<?php
if(!defined('ABSPATH')){exit;}

function cg_community_render_nav($selected='overview'){
	$html='<nav class="cg-community-nav" role="tablist" aria-label="'.esc_attr(cg_member_t('Member area')).'">';
	foreach(array('overview'=>'Overview','messages'=>'Messages','friends'=>'Friends','profile'=>'Edit profile') as $key=>$label){
		$html.='<button type="button" role="tab" data-community-tab="'.$key.'" aria-selected="'.($selected===$key ? 'true' : 'false').'">'.esc_html(cg_member_t($label)).($key==='friends' ? '<span class="cg-community-friend-total" data-count="accepted-friends" hidden></span>' : '').($key==='messages' || $key==='friends' ? '<span class="cg-community-tab-count" data-count="'.$key.'" hidden></span>' : '').'</button>';
	}
	return $html.'</nav>';
}
function cg_community_render_hub($viewer){
	$person=cg_community_person($viewer);ob_start();
	echo '<div class="cg-member-profile-dialog cg-community-dialog cg-community-account-dialog" role="dialog" aria-modal="true" aria-label="'.esc_attr(cg_member_t('Member area')).'" tabindex="-1">';
	echo '<header class="cg-community-header cg-community-account-header"><div><span class="cg-community-kicker">'.esc_html(cg_member_t('Member area')).'</span><h2>'.esc_html($person['name']).'</h2></div><div class="cg-community-header-right"><button type="button" class="cg-community-close" aria-label="'.esc_attr(cg_member_t('Close')).'">&#215;</button></div></header>';
	echo cg_community_render_nav();
	echo '<div class="cg-community-content" role="tabpanel" tabindex="-1"></div><p class="cg-community-status" role="status" aria-live="polite"></p>';
	echo cg_community_render_view_templates();
	echo cg_community_render_entry_templates();
	echo '<template class="cg-community-compose-template"><section class="cg-community-conversation"><div class="cg-public-profile-panel-actions cg-community-contact-toolbar"><div class="cg-community-contact-heading"></div><div class="cg-public-profile-friend-management cg-community-contact-actions cg-community-contact-management"></div></div><button type="button" class="cg-community-older" hidden>'.esc_html(cg_member_t('Earlier messages')).'</button><div class="cg-community-history" role="log" aria-label="'.esc_attr(cg_member_t('Messages')).'" aria-live="polite" aria-relevant="additions"></div><form class="cg-community-compose"><label>'.esc_html(cg_member_t('Message')).'<textarea name="body" rows="3" maxlength="5000" required placeholder="'.esc_attr(cg_member_t('Write a message…')).'"></textarea></label><small class="cg-community-compose-hint">'.esc_html(cg_member_t('Up to 5,000 characters')).'</small><div class="cg-community-compose-footer"><button type="submit">'.esc_html(cg_member_t('Send message')).'</button></div></form></section></template>';
	echo '</div>';return ob_get_clean();
}
function cg_community_render_participants($count){
	ob_start();
	echo '<div class="cg-member-profile-dialog cg-community-dialog cg-galleries-participants-dialog" role="dialog" aria-modal="true" aria-labelledby="cgGalleriesParticipantsTitle" tabindex="-1"><header class="cg-community-header"><div><span class="cg-community-kicker">'.esc_html(cg_member_t('Galleries')).'</span><h2 id="cgGalleriesParticipantsTitle">'.esc_html(cg_member_t('Participants')).' (<span class="cg-directory-participant-count">'.esc_html(number_format_i18n($count)).'</span>)</h2></div><button type="button" class="cg-community-close" aria-label="'.esc_attr(cg_member_t('Close')).'">&#215;</button></header><div class="cg-directory-controls"><label>'.esc_html(cg_member_t('Search')).'<input type="search" class="cg-directory-search" maxlength="120" autocomplete="off" placeholder="'.esc_attr(cg_member_t('Search')).'"></label></div><div class="cg-community-content"></div><p class="cg-community-status" role="status" aria-live="polite"></p>';
	echo cg_community_render_view_templates(array('participants'));
	echo '</div>';return ob_get_clean();
}
function cg_community_render_view_templates($kinds=array('overview','messages','friends','conversation','profile')){
	ob_start();
	foreach($kinds as $kind){
		echo '<template class="cg-community-'.$kind.'-skeleton-template"><div class="cg-community-view-skeleton cg-community-view-skeleton-'.$kind.'" aria-hidden="true">';
		if($kind!=='friends'){echo '<span class="cg-community-skeleton-line cg-community-skeleton-heading"></span>';}
		if($kind==='participants'){
			echo '<div class="cg-public-profile-friends-list cg-community-list">';
			for($i=0;$i<4;$i++){echo '<div class="cg-community-skeleton-person"><span class="cg-community-skeleton-media cg-community-skeleton-avatar"></span><span class="cg-community-skeleton-line"></span></div>';}
			echo '</div>';
		}elseif($kind==='overview'){
			echo '<div class="cg-community-entry-grid">';
			echo cg_community_render_feed_skeleton_cards();
			echo '</div>';
		}elseif($kind==='messages'){
			for($i=0;$i<3;$i++){echo '<div class="cg-community-skeleton-thread"><div class="cg-community-skeleton-person"><span class="cg-community-skeleton-media cg-community-skeleton-avatar"></span><span class="cg-community-skeleton-line"></span></div><span class="cg-community-skeleton-line cg-community-skeleton-preview"></span><span class="cg-community-skeleton-line cg-community-skeleton-date"></span></div>';}
		}elseif($kind==='friends'){
			echo '<div class="cg-community-skeleton-buttons"><span class="cg-community-skeleton-media cg-community-skeleton-button"></span><span class="cg-community-skeleton-media cg-community-skeleton-button cg-community-skeleton-button-long"></span><span class="cg-community-skeleton-media cg-community-skeleton-button cg-community-skeleton-button-long"></span><span class="cg-community-skeleton-media cg-community-skeleton-button"></span></div>';
			for($i=0;$i<3;$i++){echo '<div class="cg-community-skeleton-friend"><div class="cg-community-skeleton-person"><span class="cg-community-skeleton-media cg-community-skeleton-avatar"></span><span class="cg-community-skeleton-line"></span></div><div class="cg-community-skeleton-buttons"><span class="cg-community-skeleton-media cg-community-skeleton-button"></span><span class="cg-community-skeleton-media cg-community-skeleton-button cg-community-skeleton-button-long"></span><span class="cg-community-skeleton-media cg-community-skeleton-button"></span></div></div>';}
		}elseif($kind==='conversation'){
			echo '<span class="cg-community-skeleton-media cg-community-skeleton-bubble"></span><span class="cg-community-skeleton-media cg-community-skeleton-bubble cg-community-skeleton-mine"></span><span class="cg-community-skeleton-media cg-community-skeleton-input"></span>';
		}else{
			echo '<div class="cg-community-skeleton-profile-photo"><span class="cg-community-skeleton-media cg-community-skeleton-avatar cg-community-skeleton-profile-avatar"></span><div class="cg-community-skeleton-profile-copy"><span class="cg-community-skeleton-line"></span><span class="cg-community-skeleton-media cg-community-skeleton-button cg-community-skeleton-button-long"></span><span class="cg-community-skeleton-line"></span><span class="cg-community-skeleton-line cg-community-skeleton-line-short"></span></div></div><div class="cg-community-skeleton-field"><span class="cg-community-skeleton-line cg-community-skeleton-line-short"></span><span class="cg-community-skeleton-media cg-community-skeleton-input"></span><span class="cg-community-skeleton-line cg-community-skeleton-line-short"></span></div><div class="cg-community-skeleton-section"><span class="cg-community-skeleton-line cg-community-skeleton-heading"></span>';
			for($i=0;$i<2;$i++){echo '<div class="cg-community-skeleton-field"><span class="cg-community-skeleton-line cg-community-skeleton-line-short"></span><span class="cg-community-skeleton-media cg-community-skeleton-input"></span></div>';}
			echo '</div>';
		}
		echo '</div></template>';
	}
	return ob_get_clean();
}
function cg_community_render_feed_skeleton_cards($galleries=false){
	ob_start();
	for($i=0;$i<6;$i++){
		echo '<article class="cg-community-entry-card cg-community-skeleton-card'.($galleries ? ' cg-community-gallery-card' : '').'" aria-hidden="true"><span class="cg-community-skeleton-media"></span><span class="cg-community-entry-caption">';
		if($galleries){
			echo '<span class="cg-community-skeleton-line cg-community-skeleton-gallery-subtitle"></span><span class="cg-community-skeleton-line cg-community-skeleton-gallery-title"></span></span><div class="cg-community-gallery-details"><span class="cg-community-skeleton-line cg-community-skeleton-gallery-count"></span><div class="cg-card-statistics cg-community-skeleton-statistics"><dl class="cg-page-stats">';
			for($metric=0;$metric<4;$metric++){
				echo '<div class="cg-page-stat"><dt><span class="cg-page-stat-icon cg-community-skeleton-media"></span><span class="cg-page-stat-label cg-community-skeleton-line"></span></dt><dd><span class="cg-community-skeleton-line"></span></dd></div>';
			}
			echo '</dl></div></div>';
		}else{
			echo '<span class="cg-community-skeleton-entry-title"><span class="cg-community-skeleton-line"></span><span class="cg-community-skeleton-line cg-community-skeleton-line-short"></span></span><span class="cg-community-skeleton-line cg-community-skeleton-entry-date"></span></span>';
		}
		echo '</article>';
	}
	return ob_get_clean();
}
function cg_community_render_entry_templates(){
	ob_start();
	echo '<template class="cg-community-skeleton-template">';
	echo cg_community_render_feed_skeleton_cards();
	echo '</template><template class="cg-community-gallery-skeleton-template">';
	echo cg_community_render_feed_skeleton_cards(true);
	echo '</template>';
	echo '<template class="cg-community-entry-template"><section class="cg-community-entry-view"><button type="button" class="cg-community-entry-back cg-community-back">'.esc_html(cg_member_t('Back to overview')).'</button><div class="cg-community-entry-detail"></div></section></template>';
	echo '<template class="cg-community-entry-paid-template"><aside class="cg-community-entry-paid-notice"><p>'.esc_html(cg_member_t('Paid actions are available on the entry page.')).'</p><a class="cg-community-entry-paid-link" target="_blank" rel="noopener" aria-label="'.esc_attr(cg_member_t('Open entry page in a new tab')).'">'.esc_html(cg_member_t('Open entry page')).' <span aria-hidden="true">&#8599;</span></a></aside></template>';
	echo '<template class="cg-community-voting-template"><section class="cg-community-entry-voting cg_messages_container_pro" aria-label="'.esc_attr(cg_member_t('Vote')).'" hidden><div class="cg-community-voting-content"></div><p class="cg-community-voting-status" role="status" aria-live="polite"></p></section></template>';
	echo '<template class="cg-community-entry-skeleton-template"><div class="cg-community-entry-skeleton" aria-hidden="true"><span class="cg-community-skeleton-media"></span><span class="cg-community-skeleton-line"></span><span class="cg-community-skeleton-line cg-community-skeleton-line-short"></span></div></template>';
	return ob_get_clean();
}
function cg_community_profile_actions($target,$gid,$shortcode,$entryId,$messageLabel='Contact',$guestFriend=false,$publicPanel=false,$contactProof=''){
	if(!cg_community_enabled() || !$target || (!$publicPanel && $target===get_current_user_id()) || !cg_member_enabled($gid,$shortcode) || !in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true)){return '';}
	$attrs=' data-community-source="'.esc_attr($contactProof!=='' ? $contactProof : cg_community_contact_proof($target,$gid,$shortcode,$entryId)).'" data-community-target="'.absint($target).'" data-community-gallery="'.absint($gid).'" data-community-entry="'.absint($entryId).'" data-community-shortcode="'.esc_attr($shortcode).'"';
	$instance=absint($gid).($shortcode==='cg_gallery_no_voting' ? '-nv' : '');
	$token=cg1l_create_runtime_context_token(array('realGid'=>absint($gid),'gid'=>$instance,'shortcodeName'=>$shortcode));
	$attrs.=' data-cg-gid="'.esc_attr($instance).'" data-cg-real-gid="'.absint($gid).'" data-cg-public-instance="'.esc_attr($instance).'" data-cg-public-shortcode="'.esc_attr($shortcode).'" data-cg-member-user="'.get_current_user_id().'" data-cg-member-token="'.esc_attr($token).'"';
	if($publicPanel){
		$own=$target===get_current_user_id();
		$relation=is_user_logged_in() && !$own ? cg_community_relationship(get_current_user_id(),$target) : array('status'=>'none','incoming'=>false,'blocked'=>false,'blockedByMe'=>false,'blockedByOther'=>false);
		$html='<div class="cg-community-profile-actions cg-public-member-profile-actions"'.$attrs.' data-cg-relationship="'.esc_attr(wp_json_encode($relation)).'">';
		if(!$own){$html.='<button type="button" data-public-panel="message" aria-pressed="false"'.(!empty($relation['blocked']) ? ' disabled' : '').'>'.esc_html(cg_member_t('Send message')).'</button>';}
		$html.='<button type="button" data-public-panel="friends" aria-pressed="false">'.esc_html(cg_member_t('Friends')).(is_user_logged_in() ? ' ('.cg_public_member_profile_friend_count(get_current_user_id(),$target).')' : '').'</button>';
		if(is_user_logged_in() && !$own){$html.='<button type="button" class="cg-public-profile-block" data-public-panel="block" aria-pressed="false">'.esc_html(cg_member_t(!empty($relation['blockedByMe']) ? 'Unblock' : 'Block')).'</button>';}
		return $html.'</div>';
	}
	$html='<div class="cg-community-profile-actions"'.$attrs.'><button type="button" class="cg-community-contact">'.esc_html(cg_member_t($messageLabel)).'</button>';
	if(is_user_logged_in()){
		$relation=cg_community_relationship(get_current_user_id(),$target);
		if(!$relation['blocked']){
			$label=$relation['status']==='accepted' ? 'Friends' : ($relation['status']==='pending' ? ($relation['incoming'] ? 'Review request' : 'Request sent') : 'Add friend');
			$html.='<button type="button" class="cg-community-friend">'.esc_html(cg_member_t($label)).'</button>';
		}
	}elseif($guestFriend){
		$html.='<button type="button" class="cg-community-friend">'.esc_html(cg_member_t('Add friend')).'</button>';
	}
	return $html.'</div>';
}

// Renew the existing profile editor's signed context after an authentication reload.
function cg_community_origin($gid,$shortcode){
	$gallery=cg_community_gallery($gid,$shortcode);
	if(!$gallery){return array();}
	$instance=absint($gid).($gallery['shortcode']==='cg_gallery_no_voting' ? '-nv' : '');
	return array('gid'=>$instance,'realGid'=>absint($gid),'shortcode'=>$gallery['shortcode'],'user'=>get_current_user_id(),
		'token'=>cg1l_create_runtime_context_token(array('realGid'=>absint($gid),'gid'=>$instance,'shortcodeName'=>$gallery['shortcode'])));
}
