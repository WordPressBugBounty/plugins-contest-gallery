<?php
if(!defined('ABSPATH')){exit;}

// A public read grant is separate from the contact grant and never authorizes account actions.
if(!function_exists('cg_public_member_profile_source')){
function cg_public_member_profile_source($userId,$context,$entryId,$surface='participants'){
	global $wpdb;
	$data=array('target'=>absint($userId),'sourceGallery'=>absint($context['realGid']),'shortcode'=>$context['shortcodeName'],'entry'=>absint($entryId),'surface'=>$surface,
		'expires'=>time()+2*HOUR_IN_SECONDS,'site'=>$wpdb->prefix,'realIds'=>!empty($context['galleryDataUseAllowedRealIds']) ? $context['galleryDataUseAllowedRealIds'] : array());
	if(!empty($context['directorySource'])){$data['directorySource']=$context['directorySource'];}
	return cg_public_member_profile_pack_source($data);
}
}
if(!function_exists('cg_public_member_profile_pack_source')){
function cg_public_member_profile_pack_source($data){
	$payload=base64_encode(wp_json_encode($data));
	return array('target'=>$data['target'],'sourceGallery'=>$data['sourceGallery'],'shortcode'=>$data['shortcode'],'entry'=>$data['entry'],
		'source'=>$payload.'.'.hash_hmac('sha256','cg-public-member-profile|'.$payload,wp_salt('auth')));
}
}

if(!function_exists('cg_public_member_profile_attributes')){
function cg_public_member_profile_attributes($userId,$context,$entryId){
	$source=cg_public_member_profile_source($userId,$context,$entryId);
	$gid=isset($context['gid']) ? $context['gid'] : $context['realGid'].($context['shortcodeName']==='cg_gallery_no_voting' ? '-nv' : '');
	return ' data-cg-gid="'.esc_attr($gid).'" data-cg-public-profile-source="'.esc_attr(wp_json_encode($source)).'"';
}
}

if(!function_exists('cg_public_member_profile_origin')){
function cg_public_member_profile_origin($request){
	global $wpdb;
	$error=cg_community_error('This profile is no longer available in this gallery.');
	$parts=explode('.',isset($request['source']) ? $request['source'] : '');
	if(count($parts)!==2 || !cg1l_hash_equals(hash_hmac('sha256','cg-public-member-profile|'.$parts[0],wp_salt('auth')),$parts[1])){return $error;}
	$data=json_decode(base64_decode($parts[0],true),true);
	return cg_public_member_profile_validate_origin($data,$request);
}
}

// Friends grants retain the original gallery origin and recheck every accepted edge.
if(!function_exists('cg_public_member_profile_validate_origin')){
function cg_public_member_profile_validate_origin($data,$request){
	global $wpdb;
	$error=cg_community_error('This profile is no longer available in this gallery.');
	if(!is_array($data) || !isset($data['target'],$data['sourceGallery'],$data['shortcode'],$data['entry'],$data['surface'],$data['expires'],$data['site'],$data['realIds']) || $data['site']!==$wpdb->prefix || $data['expires']<time()
		|| (int)$data['target']!==$request['target'] || (int)$data['sourceGallery']!==$request['sourceGallery'] || $data['shortcode']!==$request['shortcode'] || (int)$data['entry']!==$request['entry']
		|| !$data['target'] || !$data['sourceGallery'] || (!$data['entry'] && !in_array($data['surface'],array('member-friends','member-conversation','friends'),true)) || !cg_community_user($data['target'])){return $error;}
	$viewer=get_current_user_id();
	if(($viewer && !cg_community_user($viewer)) || ($viewer && cg_community_blocked_by($data['target'],$viewer))){return $error;}
	if($data['surface']==='member-friends'){
		if(!$viewer || !cg_community_enabled() || !isset($data['viewer'],$data['relationship']) || (int)$data['viewer']!==$viewer || cg_community_blocked($viewer,$data['target'])){return $error;}
		$row=cg_community_pair_row('friends',$viewer,$data['target']);
		if(!$row || (int)$row->id!==(int)$data['relationship'] || !in_array($row->Status,array('pending','accepted'),true) || !cg_community_gallery($data['sourceGallery'],$data['shortcode'])){return $error;}
		return $data;
	}
	if($data['surface']==='member-conversation'){
		if(!$viewer || !cg_community_enabled() || !isset($data['viewer'],$data['conversation']) || (int)$data['viewer']!==$viewer || cg_community_blocked($viewer,$data['target'])){return $error;}
		$thread=cg_community_thread($viewer,absint($data['conversation']));
		if(!$thread || cg_community_other($thread,$viewer)!==(int)$data['target'] || !cg_community_gallery($data['sourceGallery'],$data['shortcode'])){return $error;}
		return $data;
	}
	if(!empty($data['directorySource'])){
		$directory=cg_galleries_participants_scope($data['directorySource']);
		if(is_wp_error($directory) || $directory['shortcodeName']!==$data['shortcode'] || !in_array((int)$data['sourceGallery'],$directory['galleriesIds'],true)){return $error;}
	}
	if($data['surface']==='friends'){
		if(!$viewer || !cg_community_enabled() || !isset($data['viewer'],$data['root'],$data['path']) || (int)$data['viewer']!==$viewer || !is_array($data['root']) || !is_array($data['path']) || !$data['path']
			|| !isset($data['root']['surface']) || $data['root']['surface']==='friends'){return $error;}
		$root=cg_public_member_profile_validate_origin($data['root'],$data['root']);
		if(is_wp_error($root) || $data['sourceGallery']!==$root['sourceGallery'] || $data['shortcode']!==$root['shortcode'] || $data['entry']!==$root['entry'] || $data['realIds']!==$root['realIds']
			|| (int)$data['path'][0]!==(int)$root['target'] || (int)end($data['path'])!==(int)$data['target']){return $error;}
		$previous=0;
		foreach($data['path'] as $id){
			$id=absint($id);
			if(!$id || !cg_community_user($id) || cg_community_blocked_by($id,$viewer)){return $error;}
			if($previous){$edge=cg_community_pair_row('friends',$previous,$id);if(!$edge || $edge->Status!=='accepted' || cg_community_blocked($previous,$id)){return $error;}}
			$previous=$id;
		}
		return $data;
	}
	$context=array('realGid'=>(int)$data['sourceGallery'],'shortcodeName'=>$data['shortcode'],'galleryDataUseAllowedRealIds'=>$data['realIds']);
	if($data['surface']==='participants'){
		$entries=cg_participants_rows($context['realGid'],$context['shortcodeName'],$data['target']);
		if(!isset($entries[$data['entry']]) || !cg_community_gallery($context['realGid'],$context['shortcodeName'])){return $error;}
	}else{
		// The signed grant was issued only after the original nonce/token/access-hash check.
		// Re-evaluate the same surface's current visibility for this request's viewer.
		$owner=cg_public_profile_visible_owner($context,$data['entry'],$data['surface']);
		if(is_wp_error($owner) || (int)$owner!==(int)$data['target']){return $error;}
	}
	return $data;
}
}

if(!function_exists('cg_public_member_profile_render')){
function cg_public_member_profile_render($userId,$context,$entryId,$source,$origin=array()){
	$profile=cg_member_profile_data($userId);
	ob_start();
	echo '<div class="cg-member-profile-dialog cg-member-public-profile-dialog cg-public-member-profile-dialog cg-community-dialog" data-cg-public-profile-source="'.esc_attr(wp_json_encode($source)).'" role="dialog" aria-modal="true" aria-labelledby="cgMemberPublicProfileName" tabindex="-1">';
	echo '<header class="cg-community-header cg-public-member-profile-header"><div class="cg-public-member-profile-identity"><button type="button" class="cg-public-profile-home" data-public-panel="info" aria-label="'.esc_attr(cg_member_t('View profile')).'"><span class="cg-member-profile-preview">';
	if($profile['largeImage']){echo '<img src="'.esc_url($profile['largeImage']).'" alt="">';}
	else{echo '<span class="cg-member-profile-placeholder" aria-hidden="true">'.esc_html(mb_substr($profile['name'],0,1)).'</span>';}
	echo '</span></button><div><span class="cg-community-kicker">'.esc_html(cg_member_t('Public profile')).'</span><h2 id="cgMemberPublicProfileName"><button type="button" class="cg-public-profile-home cg-public-profile-name" data-public-panel="info">'.esc_html($profile['name']).'</button></h2></div></div>';
	$contactProof=isset($origin['surface']) && $origin['surface']==='friends' ? cg_public_member_profile_friend_contact_proof($origin) : '';
	echo cg_community_profile_actions($userId,$context['realGid'],$context['shortcodeName'],$entryId,'Send message',true,true,$contactProof);
	echo '<button type="button" class="cg-community-close" aria-label="'.esc_attr(cg_member_t('Close')).'">&#215;</button></header>';
	echo '<div class="cg-public-profile-scroll"><section class="cg-public-profile-panel" aria-label="'.esc_attr(cg_member_t('Public profile')).'"><div class="cg-public-profile-panel-actions" hidden><button type="button" class="cg-public-profile-back cg-community-back" data-public-panel="info" hidden>'.esc_html(cg_member_t('Back')).'</button><div class="cg-public-profile-friend-management"></div></div><div class="cg-public-profile-panel-body">'.cg_public_profile_render_fields($userId).'</div><p class="cg-public-profile-panel-status" role="status" aria-live="polite"></p></section>';
	echo '<div class="cg-public-member-profile-feed"><div class="cg-community-content"></div><p class="cg-community-status" role="status" aria-live="polite"></p></div></div>';
	echo '<template class="cg-public-profile-initial-template"><div class="cg-public-profile-initial-loader" aria-hidden="true"><section class="cg-public-profile-initial-panel"><div class="cg-public-profile-initial-panel-actions"><span class="cg-public-profile-initial-back cg-community-skeleton-line"></span><div class="cg-public-profile-initial-friend-actions"></div></div><div class="cg-public-profile-initial-fields">';
	if(cg_public_profile_items($userId)){
		echo '<div class="cg-public-profile-initial-text">';
		for($i=0;$i<5;$i++){echo '<span class="cg-public-profile-initial-line cg-community-skeleton-line"></span>';}
		echo '</div>';
	}
	echo '</div></section><div class="cg-public-profile-initial-feed"><span class="cg-public-profile-initial-heading cg-community-skeleton-line"></span><div class="cg-public-profile-initial-modes"><span class="cg-public-profile-skeleton-button cg-community-skeleton-line"></span><span class="cg-public-profile-skeleton-button cg-community-skeleton-line"></span></div><div class="cg-community-entry-grid cg-public-profile-initial-grid"></div></div></div></template>';
	echo '<template class="cg-public-profile-message-template"><section class="cg-community-conversation"><button type="button" class="cg-community-older" hidden>'.esc_html(cg_member_t('Earlier messages')).'</button><div class="cg-community-history" role="log" aria-label="'.esc_attr(cg_member_t('Messages')).'" aria-live="polite" aria-relevant="additions"></div><form class="cg-community-compose"><label>'.esc_html(cg_member_t('Message')).'<textarea name="body" rows="3" maxlength="5000" required placeholder="'.esc_attr(cg_member_t('Write a message…')).'"></textarea></label><small class="cg-community-compose-hint">'.esc_html(cg_member_t('Up to 5,000 characters')).'</small><div class="cg-community-compose-footer"><button type="submit">'.esc_html(cg_member_t('Send message')).'</button></div></form></section></template>';
	echo '<template class="cg-public-profile-friends-template"><section><div class="cg-public-profile-friends-heading"><h3>'.esc_html(cg_member_t('Friends')).'</h3></div><div class="cg-community-list cg-public-profile-friends-list"></div></section></template>';
	echo '<template class="cg-public-profile-block-template"><section><h3 class="cg-public-profile-block-title">'.esc_html(cg_member_t('Block')).'</h3><p class="cg-public-profile-block-description">'.esc_html(cg_member_t('Block this member? This stops messages and requests and ends your friendship. Public entries remain visible.')).'</p><div class="cg-public-profile-block-actions"><button type="button" class="cg-public-profile-block-cancel" data-public-panel="info">'.esc_html(cg_member_t('Cancel')).'</button><button type="button" class="cg-public-profile-block-confirm">'.esc_html(cg_member_t('Block')).'</button></div></section></template>';
	echo '<template class="cg-public-profile-message-skeleton"><div class="cg-public-profile-panel-skeleton cg-public-profile-message-skeleton-content" aria-hidden="true"><div class="cg-community-compose"><span class="cg-public-profile-skeleton-label cg-community-skeleton-line">'.esc_html(cg_member_t('Message')).'</span><span class="cg-public-profile-skeleton-input cg-community-skeleton-line"></span><small class="cg-community-compose-hint">'.esc_html(cg_member_t('Up to 5,000 characters')).'</small><div class="cg-community-compose-footer"><span class="cg-public-profile-skeleton-button cg-community-skeleton-line">'.esc_html(cg_member_t('Send message')).'</span></div></div></div></template>';
	echo '<template class="cg-public-profile-friends-skeleton"><div class="cg-public-profile-panel-skeleton cg-public-profile-friends-skeleton-content" aria-hidden="true"><div class="cg-public-profile-friends-heading"><span class="cg-public-profile-skeleton-heading cg-community-skeleton-line"></span></div><div class="cg-public-profile-friends-list cg-community-list">';
	for($i=0;$i<4;$i++){echo '<div class="cg-public-profile-friend-row"><div class="cg-community-person"><span class="cg-public-profile-skeleton-avatar cg-community-skeleton-line"></span><span class="cg-public-profile-skeleton-name cg-community-skeleton-line"></span></div></div>';}
	echo '</div></div></template>';
	echo '<template class="cg-public-profile-block-skeleton"><div class="cg-public-profile-panel-skeleton cg-public-profile-block-skeleton-content" aria-hidden="true"><span class="cg-public-profile-skeleton-heading cg-community-skeleton-line"></span><div class="cg-public-profile-skeleton-description"><span class="cg-public-profile-skeleton-copy cg-community-skeleton-line"></span><span class="cg-public-profile-skeleton-copy cg-community-skeleton-line"></span><span class="cg-public-profile-skeleton-copy cg-public-profile-skeleton-copy-mobile cg-community-skeleton-line"></span></div><div class="cg-public-profile-block-actions"><span class="cg-public-profile-skeleton-button cg-community-skeleton-line">'.esc_html(cg_member_t('Cancel')).'</span><span class="cg-public-profile-skeleton-button cg-community-skeleton-line">'.esc_html(cg_member_t('Block')).'</span></div></div></template>';
	echo cg_community_render_entry_templates();
	echo '</div>';
	return ob_get_clean();
}
}

if(!function_exists('cg_public_member_profile_dispatch')){
function cg_public_member_profile_dispatch($viewer,$mode,$request){
	if(!in_array($mode,array('load','feed','galleries','entry','friends'),true)){return cg_community_error();}
	if($mode==='friends' && (!$viewer || !cg_community_user($viewer) || !cg_community_enabled())){return cg_community_error('Please sign in to view friends.');}
	$origin=cg_public_member_profile_origin($request);
	if(is_wp_error($origin)){return $origin;}
	$target=(int)$origin['target'];
	if($mode==='friends'){return cg_public_member_profile_friends($viewer,$target,$request,$origin);}
	if($mode==='load'){
		$context=array('realGid'=>(int)$origin['sourceGallery'],'shortcodeName'=>$origin['shortcode']);
		$source=array_intersect_key($request,array_flip(array('target','sourceGallery','shortcode','entry','source')));
		return array('html'=>cg_public_member_profile_render($target,$context,$origin['entry'],$source,$origin));
	}
	global $wpdb;
	$blocks=cg_community_table('blocks');
	$where=$wpdb->prepare("e.Active=1 AND e.IsProfileImage=0 AND e.WpUserId=%d AND NOT EXISTS (SELECT 1 FROM $blocks b WHERE b.BlockerID=e.WpUserId AND b.BlockedID=%d)",$target,$viewer);
	if(!empty($request['gallery'])){$where.=$wpdb->prepare(' AND e.GalleryID=%d',$request['gallery']);}
	return $mode==='entry' ? cg_community_entry($viewer,$request,$where) : cg_community_feed($viewer,$request,$mode==='galleries',$where);
}
}

// Both sides of an existing friendship/request can inspect the public profile without an entry.
if(!function_exists('cg_public_member_profile_request_source')){
function cg_public_member_profile_request_source($viewer,$target,$gid,$shortcode){
	global $wpdb;
	$row=cg_community_pair_row('friends',$viewer,$target);
	if(!$viewer || $viewer===$target || !cg_community_enabled() || !cg_community_user($target) || !$row || !in_array($row->Status,array('pending','accepted'),true) || cg_community_blocked($viewer,$target)){return false;}
	$gallery=cg_community_gallery($gid,$shortcode);
	if(!$gallery){$gid=(int)$row->GalleryID;$gallery=cg_community_gallery($gid,$shortcode);}
	if(!$gallery){return false;}
	return cg_public_member_profile_pack_source(array('target'=>(int)$target,'sourceGallery'=>(int)$gid,'shortcode'=>$gallery['shortcode'],'entry'=>0,'surface'=>'member-friends',
		'viewer'=>(int)$viewer,'relationship'=>(int)$row->id,'expires'=>time()+2*HOUR_IN_SECONDS,'site'=>$wpdb->prefix,'realIds'=>array()));
}
}

if(!function_exists('cg_public_member_profile_contact_source')){
function cg_public_member_profile_contact_source($viewer,$target,$gid,$shortcode,$conversation=0){
	if(!$conversation){return cg_public_member_profile_request_source($viewer,$target,$gid,$shortcode);}
	if(!$viewer || $viewer===$target || !cg_community_enabled() || !cg_community_user($target) || cg_community_blocked($viewer,$target)){return false;}
	$thread=cg_community_thread($viewer,absint($conversation));
	if(!$thread || cg_community_other($thread,$viewer)!==(int)$target){return false;}
	$gallery=cg_community_gallery($gid,$shortcode);
	if(!$gallery){$gid=(int)$thread->GalleryID;$gallery=cg_community_gallery($gid,$shortcode);}
	if(!$gallery){return false;}
	global $wpdb;
	return cg_public_member_profile_pack_source(array('target'=>(int)$target,'sourceGallery'=>(int)$gid,'shortcode'=>$gallery['shortcode'],'entry'=>0,'surface'=>'member-conversation',
		'viewer'=>(int)$viewer,'conversation'=>(int)$thread->id,'expires'=>time()+2*HOUR_IN_SECONDS,'site'=>$wpdb->prefix,'realIds'=>array()));
}
}

// Friends navigation does not require the member to own a public entry.
// Keep the original entry-based fallback for callers without a friends origin.
if(!function_exists('cg_public_member_profile_friend_source')){
function cg_public_member_profile_friend_source($userId,$origin=null,$viewer=0){
	if($origin && $viewer){
		$data=$origin;
		$data['root']=$origin['surface']==='friends' ? $origin['root'] : $origin;
		$data['path']=$origin['surface']==='friends' ? $origin['path'] : array((int)$origin['target']);
		$position=array_search((int)$userId,$data['path'],true);
		$data['path']=$position===false ? array_merge($data['path'],array((int)$userId)) : array_slice($data['path'],0,$position+1);
		$data['target']=(int)$userId;$data['surface']='friends';$data['viewer']=(int)$viewer;
		return cg_public_member_profile_pack_source($data);
	}
	global $wpdb;
	$rows=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_gal1ery WHERE WpUserId=%d AND Active=1 AND IsProfileImage=0 ORDER BY Timestamp DESC,id DESC LIMIT 100",$userId));
	foreach($rows as $entry){
		$gallery=cg_community_visible_entry($entry);
		if($gallery){return cg_public_member_profile_source($userId,array('realGid'=>(int)$entry->GalleryID,'shortcodeName'=>$gallery['shortcode']),(int)$entry->id);}
	}
	return false;
}
}

if(!function_exists('cg_public_member_profile_friends_where')){
function cg_public_member_profile_friends_where($viewer,$target){
	global $wpdb;$blocks=cg_community_table('blocks');
	$where=$wpdb->prepare("(f.UserLow=%d OR f.UserHigh=%d) AND f.Status='accepted' AND u.user_status=0 AND NOT EXISTS (SELECT 1 FROM $blocks b WHERE (b.BlockerID=u.ID AND b.BlockedID=%d) OR (b.BlockerID=u.ID AND b.BlockedID=%d) OR (b.BlockerID=%d AND b.BlockedID=u.ID))",$target,$target,$viewer,$target,$target);
	if(is_multisite()){
		$where.=$wpdb->prepare(" AND u.spam=0 AND u.deleted=0 AND EXISTS (SELECT 1 FROM {$wpdb->usermeta} um WHERE um.user_id=u.ID AND um.meta_key=%s)",$wpdb->get_blog_prefix().'capabilities');
	}
	return $where;
}
}
if(!function_exists('cg_public_member_profile_friend_count')){
function cg_public_member_profile_friend_count($viewer,$target){
	global $wpdb;$table=cg_community_table('friends');$where=cg_public_member_profile_friends_where($viewer,$target);
	return (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table f INNER JOIN {$wpdb->users} u ON u.ID=IF(f.UserLow=%d,f.UserHigh,f.UserLow) WHERE $where",$target));
}
}
if(!function_exists('cg_public_member_profile_friends')){
function cg_public_member_profile_friends($viewer,$target,$request,$origin){
	global $wpdb;
	$table=cg_community_table('friends');$blocks=cg_community_table('blocks');
	$page=isset($request['page']) ? max(1,absint($request['page'])) : 1;$offset=($page-1)*20;
	$where=cg_public_member_profile_friends_where($viewer,$target);
	$rows=$wpdb->get_results($wpdb->prepare("SELECT u.ID AS Target FROM $table f INNER JOIN {$wpdb->users} u ON u.ID=IF(f.UserLow=%d,f.UserHigh,f.UserLow) WHERE $where ORDER BY f.id DESC LIMIT 21 OFFSET %d",$target,$offset));
	$items=array();
	foreach(array_slice($rows,0,20) as $row){
		$id=(int)$row->Target;
		if(!cg_community_user($id)){continue;}
		$items[]=array('person'=>cg_community_person($id),'profile'=>cg_public_member_profile_friend_source($id,$origin,$viewer));
	}
	return array('items'=>$items,'more'=>count($rows)>20,'total'=>cg_public_member_profile_friend_count($viewer,$target));
}
}

// Separate contact capability. Public read tokens still cannot authorize writes.
if(!function_exists('cg_public_member_profile_friend_contact_proof')){
function cg_public_member_profile_friend_contact_proof($origin){
	$payload=base64_encode(wp_json_encode($origin));
	return $payload.'.'.hash_hmac('sha256','cg-community-friend-contact|'.$payload,wp_salt('auth'));
}
}
if(!function_exists('cg_public_member_profile_friend_contact_allowed')){
function cg_public_member_profile_friend_contact_allowed($viewer,$target,$request){
	$parts=explode('.',isset($request['source']) ? $request['source'] : '');
	if(count($parts)!==2 || !cg1l_hash_equals(hash_hmac('sha256','cg-community-friend-contact|'.$parts[0],wp_salt('auth')),$parts[1])){return false;}
	$data=json_decode(base64_decode($parts[0],true),true);
	if(!is_array($data) || !isset($data['surface'],$data['viewer']) || $data['surface']!=='friends' || (int)$data['viewer']!==$viewer){return false;}
	$origin=cg_public_member_profile_validate_origin($data,array('target'=>$target,'sourceGallery'=>$request['gallery'],'shortcode'=>$request['shortcode'],'entry'=>$request['entry']));
	return !is_wp_error($origin);
}
}

if(!function_exists('cg_public_member_profile_ajax')){
function cg_public_member_profile_ajax(){
	nocache_headers();header('Cache-Control: private, no-store, max-age=0');
	cg1l_ajax_reject_bad_origin();cg_check_frontend_nonce();
	if($_SERVER['REQUEST_METHOD']!=='POST'){wp_send_json_error(array('message'=>cg_member_t('This action is not available.')),403);}
	$request=array('target'=>absint(cg_community_post('target')),'sourceGallery'=>absint(cg_community_post('sourceGallery')),'shortcode'=>cg_community_post('shortcode'),
		'entry'=>absint(cg_community_post('entry')),'source'=>cg_community_post('source'),'gallery'=>absint(cg_community_post('gallery')),
		'displayTheme'=>cg_community_display_theme(cg_community_post('displayTheme')),
		'cursor'=>cg_community_post('cursor'),'entryId'=>absint(cg_community_post('entryId')),'page'=>max(1,absint(cg_community_post('page','1'))));
	$result=cg_public_member_profile_dispatch(get_current_user_id(),cg_community_post('mode','load'),$request);
	if(is_wp_error($result)){$data=$result->get_error_data();wp_send_json_error(array('message'=>$result->get_error_message()),isset($data['status']) ? $data['status'] : 400);}
	$result['communicationDisabled']=cg_community_communication_disabled(get_current_user_id());
	wp_send_json_success($result);
}
}
add_action('wp_ajax_cg_public_member_profile','cg_public_member_profile_ajax');
add_action('wp_ajax_nopriv_cg_public_member_profile','cg_public_member_profile_ajax');
