<?php
if(!defined('ABSPATH')){exit;}
include_once(__DIR__.'/render/cg-member-messages-page.php');

if(!function_exists('cg_member_admin_messages_user')){
function cg_member_admin_messages_user($id){
	$user=get_userdata(absint($id));
	return $user && (!is_multisite() || is_user_member_of_blog($user->ID)) ? $user : false;
}
}
if(!function_exists('cg_member_admin_messages_url')){
function cg_member_admin_messages_url($id,$gid){
	return add_query_arg(array('page'=>cg_get_version().'/index.php','users_management'=>'true','cg_member_messages'=>1,'wp_user_id'=>absint($id),'option_id'=>absint($gid),'cg_nonce'=>wp_create_nonce('cg_nonce')),admin_url('admin.php'));
}
}
if(!function_exists('cg_member_admin_messages_ready')){
function cg_member_admin_messages_ready(){
	global $wpdb;
	foreach(array('threads','messages') as $suffix){
		$table=cg_community_table($suffix);
		if($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($table)))!==$table){return false;}
	}
	return true;
}
}
if(!function_exists('cg_member_admin_threads_data')){
function cg_member_admin_threads_data($userId,$page){
	global $wpdb;$table=cg_community_table('threads');$page=max(1,absint($page));
	if(!cg_member_admin_messages_ready()){return array('items'=>array(),'more'=>false,'page'=>$page);}
	$rows=$wpdb->get_results($wpdb->prepare("SELECT id,UserLow,UserHigh,UpdatedAt FROM $table WHERE UserLow=%d OR UserHigh=%d ORDER BY UpdatedAt DESC,id DESC LIMIT 21 OFFSET %d",$userId,$userId,($page-1)*20));
	if($wpdb->last_error){return new WP_Error('cg_member_admin_read','Messages could not be loaded. Please try again.');}
	return array('items'=>array_slice($rows,0,20),'more'=>count($rows)>20,'page'=>$page);
}
}
if(!function_exists('cg_member_admin_messages_data')){
function cg_member_admin_messages_data($userId,$conversation,$before=0){
	global $wpdb;$threads=cg_community_table('threads');$messages=cg_community_table('messages');
	if(!cg_member_admin_messages_ready()){return new WP_Error('cg_member_admin_missing','This conversation is no longer available.');}
	$thread=$wpdb->get_row($wpdb->prepare("SELECT id,UserLow,UserHigh FROM $threads WHERE id=%d AND (UserLow=%d OR UserHigh=%d)",$conversation,$userId,$userId));
	if(!$thread){return new WP_Error('cg_member_admin_missing','This conversation is no longer available.');}
	$where=$wpdb->prepare('ConversationID=%d',$conversation);
	if($before){$where.=$wpdb->prepare(' AND id<%d',$before);}
	$rows=$wpdb->get_results("SELECT id,SenderID,Body,CreatedAt FROM $messages WHERE $where ORDER BY id DESC LIMIT 21");
	if($wpdb->last_error){return new WP_Error('cg_member_admin_read','Messages could not be loaded. Please try again.');}
	$items=array_reverse(array_slice($rows,0,20));
	return array('items'=>$items,'more'=>count($rows)>20,'before'=>count($items) ? (int)$items[0]->id : 0,'thread'=>$thread);
}
}
if(!function_exists('cg_member_admin_messages_name')){
function cg_member_admin_messages_name($id){
	$user=get_userdata($id);return $user ? ($user->display_name!=='' ? $user->display_name : $user->user_login) : 'Account unavailable';
}
}
if(!function_exists('cg_member_admin_messages_require_ajax')){
function cg_member_admin_messages_require_ajax(){
	nocache_headers();header('Cache-Control: private, no-store, max-age=0');
	if($_SERVER['REQUEST_METHOD']!=='POST' || !current_user_can('manage_options')){wp_send_json_error(array('message'=>'Administrator rights are required.'),403);}
	$nonce=isset($_POST['cg_nonce']) && is_string($_POST['cg_nonce']) ? wp_unslash($_POST['cg_nonce']) : '';
	if(!$nonce || !wp_verify_nonce($nonce,'cg_nonce')){wp_send_json_error(array('message'=>'Please reload the page and try again.'),403);}
	$id=isset($_POST['user_id']) && is_scalar($_POST['user_id']) ? absint($_POST['user_id']) : 0;
	if(!$id || !cg_member_admin_messages_user($id)){wp_send_json_error(array('message'=>'This user is no longer available.'),404);}
	return $id;
}
}
if(!function_exists('cg_member_admin_threads_ajax')){
function cg_member_admin_threads_ajax(){
	$id=cg_member_admin_messages_require_ajax();$page=isset($_POST['page']) && is_scalar($_POST['page']) ? absint($_POST['page']) : 1;
	if(!$page || $page>100000){wp_send_json_error(array('message'=>'Invalid page.'),400);}
	$data=cg_member_admin_threads_data($id,$page);
	if(is_wp_error($data)){wp_send_json_error(array('message'=>$data->get_error_message()),500);}
	wp_send_json_success(array('html'=>cg_member_admin_threads_html($id,$data['items']),'more'=>$data['more'],'page'=>$data['page']));
}
}
if(!function_exists('cg_member_admin_messages_ajax')){
function cg_member_admin_messages_ajax(){
	$id=cg_member_admin_messages_require_ajax();$conversation=isset($_POST['conversation_id']) && is_scalar($_POST['conversation_id']) ? absint($_POST['conversation_id']) : 0;
	$before=isset($_POST['before']) && is_scalar($_POST['before']) ? absint($_POST['before']) : 0;
	$data=cg_member_admin_messages_data($id,$conversation,$before);
	if(is_wp_error($data)){wp_send_json_error(array('message'=>$data->get_error_message()),404);}
	wp_send_json_success(array('html'=>cg_member_admin_messages_html($id,$data['items']),'heading'=>cg_member_admin_messages_name(cg_community_other($data['thread'],$id)),'more'=>$data['more'],'before'=>$data['before']));
}
}
add_action('wp_ajax_post_cg_member_admin_threads','cg_member_admin_threads_ajax');
add_action('wp_ajax_post_cg_member_admin_messages','cg_member_admin_messages_ajax');

if(!function_exists('cg_member_admin_communication_set')){
function cg_member_admin_communication_set($id,$disabled){
	if(!current_user_can('manage_options') || !current_user_can('edit_user',$id)){return new WP_Error('cg_member_admin_rights','You cannot change communication for this user.',array('status'=>403));}
	if(!cg_member_admin_messages_user($id)){return new WP_Error('cg_member_admin_user','This user is no longer available.',array('status'=>404));}
	$lock=cg_security_state_lock('community','write');
	if(!$lock){return new WP_Error('cg_member_admin_busy','Please try again.',array('status'=>409));}
	try{
		$current=cg_community_communication_disabled($id,true);
		if(is_wp_error($current)){return $current;}
		$key=cg_community_communication_meta_key();
		if($disabled){if(!$current && update_user_meta($id,$key,'1')===false){return new WP_Error('cg_member_admin_save','The communication setting could not be saved. Please try again.',array('status'=>500));}}
		else{delete_user_meta($id,$key);}
		$actual=cg_community_communication_disabled($id,true);
		if(is_wp_error($actual)){return $actual;}
		if($actual!==$disabled){return new WP_Error('cg_member_admin_save','The communication setting could not be saved. Please try again.',array('status'=>500));}
		wp_cache_delete($id,'user_meta');
		return $actual;
	}finally{cg_security_state_unlock($lock);}
}
}
if(!function_exists('cg_member_admin_communication_ajax')){
function cg_member_admin_communication_ajax(){
	$id=cg_member_admin_messages_require_ajax();
	$value=isset($_POST['disabled']) && is_string($_POST['disabled']) ? $_POST['disabled'] : '';
	if(!in_array($value,array('0','1'),true)){wp_send_json_error(array('message'=>'Invalid communication setting.'),400);}
	$result=cg_member_admin_communication_set($id,$value==='1');
	if(is_wp_error($result)){$data=$result->get_error_data();wp_send_json_error(array('message'=>$result->get_error_message()),isset($data['status']) ? $data['status'] : 500);}
	wp_send_json_success(array('communicationDisabled'=>$result,'html'=>cg_member_admin_communication_html($id)));
}
}
add_action('wp_ajax_post_cg_member_admin_communication','cg_member_admin_communication_ajax');
