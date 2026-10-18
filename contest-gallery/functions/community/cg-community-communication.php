<?php
if(!defined('ABSPATH')){exit;}
if(!function_exists('cg_community_communication_meta_key')){
function cg_community_communication_meta_key(){global $wpdb;return $wpdb->get_blog_prefix().'cg_member_communication_disabled';}
}
if(!function_exists('cg_community_communication_disabled')){
function cg_community_communication_disabled($userId,$fresh=false){
	if(!$userId){return false;}
	$key=cg_community_communication_meta_key();
	if(!$fresh){return get_user_meta($userId,$key,true)==='1';}
	global $wpdb;
	$value=$wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id=%d AND meta_key=%s LIMIT 1",$userId,$key));
	if($wpdb->last_error){return cg_community_error('Please try again.',500);}
	return $value==='1';
}
}
if(!function_exists('cg_community_communication_notice')){
function cg_community_communication_notice(){return cg_member_t('An administrator has disabled your ability to send messages and friend requests.');}
}
// Only new outgoing messages and requests are restricted. Reads and existing relationships stay usable.
if(!function_exists('cg_community_communication_check')){
function cg_community_communication_check($userId,$mode,$operation=''){
	if($mode!=='send' && !($mode==='friend' && $operation==='request')){return true;}
	$disabled=cg_community_communication_disabled($userId,true);
	if(is_wp_error($disabled)){return $disabled;}
	return $disabled ? new WP_Error('cg_community_communication_disabled',cg_community_communication_notice(),array('status'=>403,'communicationDisabled'=>true)) : true;
}
}
