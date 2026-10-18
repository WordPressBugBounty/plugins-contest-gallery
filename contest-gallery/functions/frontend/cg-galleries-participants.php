<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_galleries_member_enabled')){
function cg_galleries_member_enabled($shortcode,$options=null){
	if(!in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true)){return false;}
	if($options===null){$options=cg_galleries_options($shortcode);}
	return (bool)cg_page_header_option_value($options,'MemberArea');
}
}
if(!function_exists('cg_member_store_galleries_options')){
function cg_member_store_galleries_options(&$options,$previous){
	foreach(array('g','nv','u','w','ec') as $type){
		if(!in_array($type,array('g','nv'),true)){unset($options[$type]['MemberArea']);continue;}
		$old=isset($previous[$type]) ? $previous[$type] : array();
		$values=isset($options[$type]) ? $options[$type] : array();
		$options[$type]['MemberArea']=cg_page_header_option_value($values,'MemberArea',cg_page_header_option_value($old,'MemberArea'));
	}
}
}

// A bounded, signed overview scope is independent of the hero and gallery pagination.
if(!function_exists('cg_galleries_participants_source')){
function cg_galleries_participants_source($context){
	global $wpdb;
	$data=array('token'=>cg1l_create_runtime_context_token($context),'site'=>$wpdb->prefix,'expires'=>time()+2*HOUR_IN_SECONDS);
	$payload=base64_encode(wp_json_encode($data));
	return $payload.'.'.hash_hmac('sha256','cg-galleries-participants|'.$payload,wp_salt('auth'));
}
}
if(!function_exists('cg_galleries_participants_scope')){
function cg_galleries_participants_scope($source){
	global $wpdb;$error=cg_community_error('This participant list is no longer available.');
	$parts=explode('.',(string)$source);
	if(count($parts)!==2 || !cg1l_hash_equals(hash_hmac('sha256','cg-galleries-participants|'.$parts[0],wp_salt('auth')),$parts[1])){return $error;}
	$data=json_decode(base64_decode($parts[0],true),true);
	if(!is_array($data) || !isset($data['token'],$data['site'],$data['expires']) || $data['site']!==$wpdb->prefix || $data['expires']<time()){return $error;}
	$context=cg1l_verify_runtime_context_token($data['token']);
	if(!$context || empty($context['isCGalleries']) || empty($context['hasGalleriesIds']) || empty($context['realGid']) || !cg_galleries_member_enabled($context['shortcodeName'])){return $error;}
	$viewer=get_current_user_id();
	return $viewer && !cg_community_user($viewer) ? $error : $context;
}
}

// Collect eligible public owners once per request. No ratings/comments or user directory scans.
if(!function_exists('cg_galleries_participant_rows')){
function cg_galleries_participant_rows($context){
	static $cache=array();
	$gids=cg1l_normalize_positive_int_id_list($context['galleriesIds']);sort($gids);
	$key=get_current_blog_id().':'.get_current_user_id().':'.$context['shortcodeName'].':'.wp_json_encode($gids);
	if(isset($cache[$key])){return $cache[$key];}
	$users=array();$viewer=get_current_user_id();
	foreach($gids as $gid){
		if(!cg_community_gallery($gid,$context['shortcodeName'])){continue;}
		foreach(cg_participants_rows($gid,$context['shortcodeName']) as $entry){
			$id=absint($entry['WpUserId']);
			if(!$id || isset($users[$id]) || !cg_community_user($id) || ($viewer && cg_community_blocked_by($id,$viewer))){continue;}
			$users[$id]=array('id'=>$id,'entry'=>absint($entry['id']),'gallery'=>absint($gid));
		}
	}
	$cache[$key]=$users;
	return $users;
}
}

if(!function_exists('cg_galleries_participants_control')){
function cg_galleries_participants_control($gid,$instance,$shortcode,$covers){
	if(!cg_galleries_member_enabled($shortcode)){return '';}
	$gids=array();
	foreach($covers as $cover){$id=!empty($cover['GalleryIdToCheck']) ? absint($cover['GalleryIdToCheck']) : (!empty($cover['gid']) ? absint($cover['gid']) : 0);if($id){$gids[$id]=$id;}}
	$context=array('realGid'=>absint($gid),'gid'=>$instance,'shortcodeName'=>$shortcode,'isCGalleries'=>1,'hasGalleriesIds'=>1,'galleriesIds'=>array_values($gids));
	$count=count(cg_galleries_participant_rows($context));$source=cg_galleries_participants_source($context);
	return '<div class="cg-galleries-participants-control"><button type="button" class="cg-galleries-participants-open" data-cg-gid="'.esc_attr($instance).'" data-cg-participants-source="'.esc_attr($source).'" aria-haspopup="dialog">'.esc_html(cg_member_t('Participants')).' <span class="cg-member-count cg-galleries-participant-count">'.esc_html(number_format_i18n($count)).'</span></button><template class="cg-galleries-participants-template">'.cg_community_render_participants($count).'</template></div>';
}
}

if(!function_exists('cg_galleries_participants_data')){
function cg_galleries_participants_data($source,$request){
	$context=cg_galleries_participants_scope($source);if(is_wp_error($context)){return $context;}
	$users=cg_galleries_participant_rows($context);$total=count($users);$matches=array();
	if(!empty($request['member'])){
		$id=absint($request['member']);
		if(!isset($users[$id])){return cg_community_error('This profile is no longer available in this gallery.');}
		$user=$users[$id];
		return array('profile'=>cg_public_member_profile_source($id,array('realGid'=>$user['gallery'],'shortcodeName'=>$context['shortcodeName'],'directorySource'=>$source),$user['entry']));
	}
	$search=isset($request['search']) && is_string($request['search']) ? mb_substr(trim(sanitize_text_field($request['search'])),0,120) : '';
	foreach($users as $user){
		$person=cg_community_person($user['id']);
		if($search!=='' && mb_stripos($person['name'],$search)===false){continue;}
		$matches[]=array('id'=>$user['id'],'name'=>$person['name'],'person'=>$person,'gallery'=>$user['gallery'],'entry'=>$user['entry']);
	}
	usort($matches,'cg_participants_compare');
	$page=isset($request['page']) ? max(1,absint($request['page'])) : 1;
	$page=min($page,max(1,(int)ceil(count($matches)/20)));$offset=($page-1)*20;$items=array();
	foreach(array_slice($matches,$offset,20) as $user){
		$profile=cg_public_member_profile_source($user['id'],array('realGid'=>$user['gallery'],'shortcodeName'=>$context['shortcodeName'],'directorySource'=>$source),$user['entry']);
		$items[]=array('person'=>$user['person'],'profile'=>$profile);
	}
	return array('items'=>$items,'total'=>count($matches),'participantCount'=>$total,'formattedCount'=>number_format_i18n($total),'more'=>$offset+20<count($matches),'page'=>$page,'search'=>$search);
}
}
if(!function_exists('cg_galleries_participants_ajax')){
function cg_galleries_participants_ajax(){
	nocache_headers();header('Cache-Control: private, no-store, max-age=0');
	cg1l_ajax_reject_bad_origin();cg_check_frontend_nonce();
	if($_SERVER['REQUEST_METHOD']!=='POST'){wp_send_json_error(array('message'=>cg_member_t('This action is not available.')),403);}
	$result=cg_galleries_participants_data(cg_community_post('scope'),array('page'=>cg_community_post('page','1'),'search'=>cg_community_post('search'),'member'=>cg_community_post('member')));
	if(is_wp_error($result)){$data=$result->get_error_data();wp_send_json_error(array('message'=>$result->get_error_message()),isset($data['status']) ? $data['status'] : 403);}
	wp_send_json_success($result);
}
}
add_action('wp_ajax_cg_galleries_participants','cg_galleries_participants_ajax');
add_action('wp_ajax_nopriv_cg_galleries_participants','cg_galleries_participants_ajax');
