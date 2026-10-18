<?php
if(!defined('ABSPATH')){exit;}
include(__DIR__.'/cg-community-feed.php');
include(__DIR__.'/cg-community-entry.php');
include(__DIR__.'/cg-community-messages.php');
include(__DIR__.'/cg-community-friends.php');
include(__DIR__.'/cg-community-communication.php');
include(__DIR__.'/cg-community-render.php');
include(__DIR__.'/cg-community-mail.php');
include(__DIR__.'/cg-community-admin.php');

if(!function_exists('cg_community_table')){
function cg_community_table($suffix){global $wpdb; return $wpdb->prefix.'contest_gal1ery_member_'.$suffix;}
}
if(!function_exists('cg_community_normalize_mail_placeholders')){
function cg_community_normalize_mail_placeholders($text){
	return is_string($text) ? strtr($text,array('{recipient}'=>'$recipient$','{sender}'=>'$sender$','{site}'=>'$site$','{url}'=>'$url$')) : $text;
}
}
if(!function_exists('cg_community_settings')){
function cg_community_settings(){
	$defaults=array('enabled'=>1);
	foreach(array('message','friend') as $type){
		$defaults[$type]=array('enabled'=>0,'name'=>get_bloginfo('name'),'from'=>get_option('admin_email'),'reply'=>get_option('admin_email'),
			'subject'=>$type==='message' ? 'New message from $sender$' : 'Friend request from $sender$',
			'content'=>'<p>Hello $recipient$,</p><p>'.($type==='message' ? '$sender$ has sent you a private message.' : '$sender$ would like to be your friend.').'</p><p><a href="$url$">Open your member area</a></p><p>$site$</p>');
	}
	$stored=get_option('cg_member_community',array());
	if(!is_array($stored)){return $defaults;}
	foreach($defaults as $key=>$value){if(isset($stored[$key])){$defaults[$key]=is_array($value) && is_array($stored[$key]) ? array_merge($value,$stored[$key]) : $stored[$key];}}
	foreach(array('message','friend') as $type){
		if(!is_array($defaults[$type])){continue;}
		foreach(array('subject','content') as $key){
			$defaults[$type][$key]=cg_community_normalize_mail_placeholders($defaults[$type][$key]);
		}
	}
	return $defaults;
}
}
if(!function_exists('cg_community_enabled')){
function cg_community_enabled(){$settings=cg_community_settings(); return !empty($settings['enabled']);}
}
if(!function_exists('cg_community_user')){
function cg_community_user($id){
	$user=get_userdata(absint($id));
	return $user && empty($user->user_status) && empty($user->spam) && empty($user->deleted) && (!is_multisite() || is_user_member_of_blog($user->ID)) ? $user : false;
}
}
if(!function_exists('cg_community_person')){
function cg_community_person($id){
	$user=cg_community_user($id);
	if(!$user){return array('id'=>0,'name'=>cg_member_t('Account unavailable'),'image'=>'');}
	$profile=cg_member_profile_data($user->ID);
	return array('id'=>(int)$user->ID,'name'=>$profile['name'],'image'=>$profile['image']);
}
}
if(!function_exists('cg_community_error')){
function cg_community_error($message='This action is not available.',$status=403){return new WP_Error('community',cg_member_t($message),array('status'=>$status));}
}
if(!function_exists('cg_community_pair')){
function cg_community_pair($a,$b){return array(min(absint($a),absint($b)),max(absint($a),absint($b)));}
}
if(!function_exists('cg_community_pair_row')){
function cg_community_pair_row($suffix,$a,$b){
	global $wpdb; $pair=cg_community_pair($a,$b); $table=cg_community_table($suffix);
	return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE UserLow=%d AND UserHigh=%d",$pair[0],$pair[1]));
}
}
if(!function_exists('cg_community_blocked')){
function cg_community_blocked($a,$b){
	global $wpdb; $table=cg_community_table('blocks');
	return (bool)$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE (BlockerID=%d AND BlockedID=%d) OR (BlockerID=%d AND BlockedID=%d) LIMIT 1",$a,$b,$b,$a));
}
}
if(!function_exists('cg_community_blocked_by')){
function cg_community_blocked_by($blocker,$blocked){
	global $wpdb;$table=cg_community_table('blocks');
	return (bool)$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE BlockerID=%d AND BlockedID=%d LIMIT 1",$blocker,$blocked));
}
}
if(!function_exists('cg_community_relationship')){
function cg_community_relationship($viewer,$target){
	global $wpdb; $table=cg_community_table('blocks');
	$own=(bool)$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE BlockerID=%d AND BlockedID=%d",$viewer,$target));
	$friend=cg_community_pair_row('friends',$viewer,$target);
	return array('status'=>$friend ? $friend->Status : 'none','incoming'=>$friend && (int)$friend->RequesterID!==$viewer,'blocked'=>cg_community_blocked($viewer,$target),'blockedByMe'=>$own,'blockedByOther'=>cg_community_blocked_by($target,$viewer));
}
}
if(!function_exists('cg_community_summary')){
function cg_community_summary($viewer){
	global $wpdb; $messages=cg_community_table('messages'); $threads=cg_community_table('threads'); $friends=cg_community_table('friends');
	$unread=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $messages m INNER JOIN $threads t ON t.id=m.ConversationID WHERE m.RecipientID=%d AND m.id>IF(t.UserLow=%d,t.LowReadId,t.HighReadId)",$viewer,$viewer));
	$requests=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $friends WHERE (UserLow=%d OR UserHigh=%d) AND RequesterID<>%d AND Status='pending'",$viewer,$viewer,$viewer));
	return array('unread'=>$unread,'requests'=>$requests,'friends'=>cg_community_friend_count($viewer),'communicationDisabled'=>cg_community_communication_disabled($viewer));
}
}
if(!function_exists('cg_community_post')){
function cg_community_post($key,$default=''){
	return isset($_POST[$key]) && is_string($_POST[$key]) ? wp_unslash($_POST[$key]) : $default;
}
}
// Presentation only. This value never grants gallery or account access.
if(!function_exists('cg_community_display_theme')){
function cg_community_display_theme($value){
	return is_string($value) && in_array($value,array('white','black'),true) ? $value : '';
}
}
if(!function_exists('cg_community_ajax')){
function cg_community_ajax(){
	nocache_headers(); header('Cache-Control: private, no-store, max-age=0');
	cg1l_ajax_reject_bad_origin(); cg_check_frontend_nonce();
	$viewer=get_current_user_id();
	if($_SERVER['REQUEST_METHOD']!=='POST' || !$viewer || !cg_community_user($viewer) || !cg_community_enabled()){
		wp_send_json_error(array('message'=>cg_member_t('The member area is not available.')),403);
	}
	$mode=cg_community_post('mode');
	$request=array('target'=>absint(cg_community_post('target')),'conversation'=>absint(cg_community_post('conversation')),'entryId'=>absint(cg_community_post('entryId')),
		'source'=>cg_community_post('source'),'entry'=>absint(cg_community_post('entry')),'gallery'=>absint(cg_community_post('gallery')),'sourceGallery'=>absint(cg_community_post('sourceGallery')),'shortcode'=>cg_community_post('shortcode','cg_gallery'),
		'displayTheme'=>cg_community_display_theme(cg_community_post('displayTheme')),
		'cursor'=>cg_community_post('cursor'),'before'=>absint(cg_community_post('before')),'after'=>absint(cg_community_post('after')),
		'page'=>max(1,absint(cg_community_post('page','1'))),'list'=>cg_community_post('list','accepted'),'body'=>cg_community_post('body'),'key'=>cg_community_post('key'),'operation'=>cg_community_post('operation'));
	$result=cg_community_dispatch($viewer,$mode,$request);
	if(is_wp_error($result)){$data=$result->get_error_data();$error=array('message'=>$result->get_error_message());if(!empty($data['communicationDisabled'])){$error['communicationDisabled']=true;}wp_send_json_error($error,isset($data['status']) ? $data['status'] : 400);}
	$result['communicationDisabled']=cg_community_communication_disabled($viewer);
	wp_send_json_success($result);
}
}
add_action('wp_ajax_cg_community','cg_community_ajax');
if(!function_exists('cg_community_dispatch')){
function cg_community_dispatch($viewer,$mode,$request){
	if(!cg_community_enabled() || !cg_community_user($viewer)){return cg_community_error();}
	if($mode==='summary'){return cg_community_summary($viewer);}
	if($mode==='hub'){return array('html'=>cg_community_render_hub($viewer),'summary'=>cg_community_summary($viewer),'origin'=>cg_community_origin($request['gallery'],$request['shortcode']));}
	if($mode==='threads'){return cg_community_threads($viewer,$request);}
	if($mode==='messages'){return cg_community_messages($viewer,$request);}
	if($mode==='friends'){return cg_community_friends($viewer,$request);}
	if($mode==='feed' || $mode==='galleries'){return cg_community_feed($viewer,$request,$mode==='galleries');}
	if($mode==='entry'){return cg_community_entry($viewer,$request);}
	if($mode==='contact'){return cg_community_contact($viewer,$request);}
	if(!in_array($mode,array('send','read','friend','block'),true)){return cg_community_error();}
	global $wpdb;
	// A short site-scoped admission lock serializes relationships, limits and read cursors.
	$lock=cg_security_state_lock('community','write');
	if(!$lock){return cg_community_error('Please try again.',409);}
	try{
		$wpdb->query('START TRANSACTION');
		$permission=cg_community_communication_check($viewer,$mode,isset($request['operation']) ? $request['operation'] : '');
		if(is_wp_error($permission)){$result=$permission;}
		elseif($mode==='send'){$result=cg_community_send($viewer,$request);}
		elseif($mode==='read'){$result=cg_community_read($viewer,$request);}
		elseif($mode==='friend'){$result=cg_community_friend_action($viewer,$request);}
		else{$result=cg_community_block_action($viewer,$request);}
		if(is_wp_error($result)){$wpdb->query('ROLLBACK');}
		elseif($wpdb->query('COMMIT')===false){$wpdb->query('ROLLBACK');$result=cg_community_error('Please try again.',500);}
	}catch(Exception $error){$wpdb->query('ROLLBACK');$result=cg_community_error('Please try again.',500);}
	finally{cg_security_state_unlock($lock);}
	if(!is_wp_error($result)){
		if(!empty($result['_mail'])){cg_community_notify($result['_mail']);unset($result['_mail']);}
		$result['summary']=cg_community_summary($viewer);
	}
	return $result;
}
}
if(!function_exists('cg_community_remove_user')){
function cg_community_remove_user($userId){
	global $wpdb;
	$messages=cg_community_table('messages');$threads=cg_community_table('threads');$friends=cg_community_table('friends');$blocks=cg_community_table('blocks');
	$exists=$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($threads)));
	if($exists!==$threads){delete_user_meta($userId,cg_community_communication_meta_key());return;}
	$lock=cg_security_state_lock('community','write');if(!$lock){return;}
	try{
		delete_user_meta($userId,cg_community_communication_meta_key());
		$wpdb->query($wpdb->prepare("DELETE m FROM $messages m INNER JOIN $threads t ON t.id=m.ConversationID WHERE t.UserLow=%d OR t.UserHigh=%d",$userId,$userId));
		foreach(array($threads,$friends) as $table){$wpdb->query($wpdb->prepare("DELETE FROM $table WHERE UserLow=%d OR UserHigh=%d",$userId,$userId));}
		$wpdb->query($wpdb->prepare("DELETE FROM $blocks WHERE BlockerID=%d OR BlockedID=%d",$userId,$userId));
	}finally{cg_security_state_unlock($lock);}
}
}
add_action('delete_user','cg_community_remove_user');
add_action('deleted_user','cg_community_remove_user');
if(!function_exists('cg_community_remove_network_user')){
function cg_community_remove_network_user($userId){
	foreach(get_blogs_of_user($userId,true) as $site){cg_community_remove_blog_user($userId,$site->userblog_id);}
}
}
add_action('wpmu_delete_user','cg_community_remove_network_user');
if(!function_exists('cg_community_remove_blog_user')){
function cg_community_remove_blog_user($userId,$blogId){switch_to_blog($blogId);cg_community_remove_user($userId);restore_current_blog();}
}
add_action('remove_user_from_blog','cg_community_remove_blog_user',10,2);
