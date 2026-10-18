<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_mail_history_table_exists')){
function cg_mail_history_table_exists($suffix){
	global $wpdb;
	$table=$wpdb->prefix.$suffix;
	return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($table)))===$table;
}
}

if(!function_exists('cg_mail_history_user_sources')){
function cg_mail_history_user_sources($user){
	global $wpdb;
	$sources=array();
	foreach(array('mail'=>'contest_gal1ery_mails','comment'=>'contest_gal1ery_user_comment_mails','vote'=>'contest_gal1ery_user_vote_mails') as $source=>$suffix){
		$exists=cg_mail_history_table_exists($suffix);
		if($wpdb->last_error){return new WP_Error('cg_history_database','The email history could not be loaded.');}
		if(!$exists){continue;}
		$table=$wpdb->prefix.$suffix;
		if($source==='mail'){
			// Older custom-mail rows contain an unrelated WpUserId. Match their actual To address.
			$custom="('entry-backend','entry-backend-custom','entry-backend-custom-activation','entry-backend-custom-deactivation')";
			$where=$wpdb->prepare("((WpUserId=%d AND MailType NOT IN $custom) OR (ReceiverMail=%s AND (WpUserId=0 OR MailType IN $custom)))",$user->ID,$user->user_email);
			$columns="Subject,MailType";
		}else{
			$where=$wpdb->prepare("WpUserId=%d AND TRIM(Content)<>''",$user->ID);
			$columns="'' AS Subject,'user-$source-notification' AS MailType";
		}
		$sources[$source]=array('table'=>$table,'where'=>$where,'columns'=>$columns);
	}
	return $sources;
}
}

if(!function_exists('cg_mail_history_user_page')){
function cg_mail_history_user_page($user,$page){
	global $wpdb;
	$sources=cg_mail_history_user_sources($user);if(is_wp_error($sources)){return $sources;}$queries=array();
	foreach($sources as $source=>$query){$queries[]="SELECT '$source' AS Source,id,Tstamp,GalleryID,{$query['columns']} FROM {$query['table']} WHERE {$query['where']}";}
	if(!$queries){return array();}
	$rows=$wpdb->get_results(implode(' UNION ALL ',$queries).$wpdb->prepare(' ORDER BY Tstamp DESC,Source ASC,id DESC LIMIT %d,51',$page*50),ARRAY_A);
	return $wpdb->last_error ? new WP_Error('cg_history_database','The email history could not be loaded.') : $rows;
}
}

if(!function_exists('cg_mail_history_record')){
function cg_mail_history_record($source,$id,$user,$galleryId=0){
	global $wpdb;
	if($user){
		$sources=cg_mail_history_user_sources($user);if(is_wp_error($sources)){return $sources;}
		if(!isset($sources[$source])){return false;}$query=$sources[$source];
		$row=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$query['table']} WHERE ({$query['where']}) AND id=%d",$id),ARRAY_A);
	}else{
		if($source!=='mail' || !cg_mail_history_table_exists('contest_gal1ery_mails')){return false;}
		$row=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_gal1ery_mails WHERE GalleryID=%d AND id=%d AND id<=%d AND MailType='participants-campaign'",$galleryId,$id,absint(cg_participant_mail_read('cg_pmail_history_legacy_id',0))),ARRAY_A);
	}
	if($wpdb->last_error){return new WP_Error('cg_history_database','The email could not be loaded.');}
	if(!$row){return false;}
	$row['Source']=$source;
	if($source!=='mail'){$row['Subject']='';$row['Body']=contest_gal1ery_convert_for_html_output_without_nl2br($row['Content']);$row['MailType']='user-'.$source.'-notification';}
	elseif($row['MailType']!=='participants-campaign'){$row['Body']=contest_gal1ery_convert_for_html_output_without_nl2br($row['Body']);$row['Subject']=contest_gal1ery_convert_for_html_output_without_nl2br($row['Subject']);}
	return $row;
}
}

if(!function_exists('cg_mail_history_gallery_page')){
function cg_mail_history_gallery_page($galleryId,$page,$earlier){
	global $wpdb;
	if($earlier){
		$exists=cg_mail_history_table_exists('contest_gal1ery_mails');
		if($wpdb->last_error){return new WP_Error('cg_history_database','The email history could not be loaded.');}
		if(!$exists){return array();}
		$rows=$wpdb->get_results($wpdb->prepare("SELECT id,Tstamp,GalleryID,ReceiverMail,Subject,MailType,'mail' AS Source FROM {$wpdb->prefix}contest_gal1ery_mails WHERE GalleryID=%d AND MailType='participants-campaign' AND id<=%d ORDER BY Tstamp DESC,id DESC LIMIT %d,51",$galleryId,absint(cg_participant_mail_read('cg_pmail_history_legacy_id',0)),$page*50),ARRAY_A);
		return $wpdb->last_error ? new WP_Error('cg_history_database','The email history could not be loaded.') : $rows;
	}
	$history=cg_participant_mail_read(cg_participant_mail_history_key($galleryId),array());
	usort($history,'cg_mail_history_campaign_order');
	return array_slice($history,$page*50,51);
}
}

if(!function_exists('cg_mail_history_campaign_order')){
function cg_mail_history_campaign_order($a,$b){
	if($a['created_at']===$b['created_at']){return strcmp($b['id'],$a['id']);}
	return $a['created_at']>$b['created_at'] ? -1 : 1;
}
}

if(!function_exists('cg_mail_history_import_gallery')){
function cg_mail_history_import_gallery($galleryId){
	if(!cg_participant_mail_lock()){return new WP_Error('cg_history_busy','Email processing is busy. Please try again.');}
	try{$ok=cg_participant_mail_history_import($galleryId);}finally{cg_participant_mail_unlock();}
	return $ok ? true : new WP_Error('cg_history_database','The email history could not be loaded.');
}
}

if(!function_exists('cg_mail_history_ajax')){
function cg_mail_history_ajax(){
	cg_backend_ajax_require_access_json();
	$context=isset($_POST['context']) && is_string($_POST['context']) ? $_POST['context'] : '';
	$view=isset($_POST['view']) && is_string($_POST['view']) ? $_POST['view'] : 'list';
	$galleryId=isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
	$userId=isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
	$page=isset($_POST['page']) ? min(1000000,absint($_POST['page'])) : 0;
	$user=false;
	if($context==='gallery'){
		if(!$galleryId || !cg_participant_mail_gallery_exists($galleryId)){cg_backend_ajax_error_json('This gallery no longer exists.',404,'cg_history_gallery');}
		$import=cg_mail_history_import_gallery($galleryId);if(is_wp_error($import)){cg_backend_ajax_error_json($import->get_error_message(),400,$import->get_error_code());}
	}elseif($context==='user'){
		if(!current_user_can('manage_options')){cg_backend_ajax_error_json('Only administrators can view a user email history.',403,'cg_history_access');}
		$user=$userId ? get_userdata($userId) : false;
		if(!$user){cg_backend_ajax_error_json('This user no longer exists.',404,'cg_history_user');}
	}else{cg_backend_ajax_error_json('Invalid email history context.',400,'cg_history_context');}
	$result=false;
	if($view==='list' || ($context==='gallery' && $view==='earlier')){
		$rows=$user ? cg_mail_history_user_page($user,$page) : cg_mail_history_gallery_page($galleryId,$page,$view==='earlier');
		$result=is_wp_error($rows) ? $rows : array('html'=>cg_mail_history_list_html($rows,$context,$view,$page),'title'=>$user ? 'Email history for '.$user->user_email : 'Participant email history');
	}elseif($context==='gallery' && ($view==='campaign' || $view==='recipient' || $view==='reuse')){
		$id=isset($_POST['campaign']) && is_string($_POST['campaign']) ? $_POST['campaign'] : '';$job=cg_participant_mail_job($id);
		if(!$job || (int)$job['gallery_id']!==$galleryId || $job['status']==='draft'){cg_backend_ajax_error_json('This message does not belong to this gallery.',404,'cg_history_missing');}
		if($view==='reuse'){
			$result=array('message'=>$job['message'],'gallery_id'=>$galleryId);
		}elseif($view==='campaign'){
			$chunk=$page<$job['chunks'] ? cg_participant_mail_read(cg_participant_mail_chunk_key($id,$page)) : array();
			$result=is_array($chunk) ? array('html'=>cg_mail_history_campaign_html($job,$chunk,$page)) : new WP_Error('cg_history_missing','The saved recipient list is incomplete.');
		}else{
			$offset=isset($_POST['recipient']) ? absint($_POST['recipient']) : 0;$number=(int)floor($offset/50);
			$chunk=$number<$job['chunks'] ? cg_participant_mail_read(cg_participant_mail_chunk_key($id,$number)) : false;
			$result=is_array($chunk) && isset($chunk[$offset%50]) ? array('html'=>cg_mail_history_recipient_html($job,$chunk[$offset%50],$number)) : new WP_Error('cg_history_missing','This recipient is no longer available.');
		}
	}elseif($view==='message'){
		$source=isset($_POST['source']) && is_string($_POST['source']) ? $_POST['source'] : '';$id=isset($_POST['mail_id']) ? absint($_POST['mail_id']) : 0;
		$row=cg_mail_history_record($source,$id,$user,$galleryId);
		$result=is_wp_error($row) ? $row : ($row ? array('html'=>cg_mail_history_message_html($row,$user ? 'list' : 'earlier',$page)) : new WP_Error('cg_history_missing','This email does not belong to this history.'));
	}else{$result=new WP_Error('cg_history_view','Invalid email history view.');}
	if(is_wp_error($result)){cg_backend_ajax_error_json($result->get_error_message(),400,$result->get_error_code());}
	wp_send_json_success($result);
}
}

add_action('wp_ajax_cg_mail_history','cg_mail_history_ajax');
