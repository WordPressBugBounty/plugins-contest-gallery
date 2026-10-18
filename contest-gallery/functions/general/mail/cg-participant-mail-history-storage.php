<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_participant_mail_history_key')){
function cg_participant_mail_history_key($galleryId){return 'cg_pmail_history_'.absint($galleryId);}
}

// Called with the queue lock held, before this version writes any recipient logs.
if(!function_exists('cg_participant_mail_history_bootstrap')){
function cg_participant_mail_history_bootstrap(){
	if(cg_participant_mail_read('cg_pmail_history_legacy_id',false)!==false){return true;}
	global $wpdb;
	$table = $wpdb->prefix.'contest_gal1ery_mails';
	$exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($table)));
	if($wpdb->last_error){return false;}
	$lastId = $exists===$table ? $wpdb->get_var("SELECT MAX(id) FROM $table WHERE MailType='participants-campaign'") : 0;
	if($wpdb->last_error){return false;}
	return cg_participant_mail_store('cg_pmail_history_legacy_id',absint($lastId));
}
}

if(!function_exists('cg_participant_mail_history_index_job')){
function cg_participant_mail_history_index_job($job){
	if($job['status']==='draft'){return true;}
	if(!cg_participant_mail_history_bootstrap()){return false;}
	$key = cg_participant_mail_history_key($job['gallery_id']);
	$history = cg_participant_mail_read($key,array());
	$history[$job['id']] = array('id'=>$job['id'],'created_at'=>$job['created_at'],
		'updated_at'=>$job['updated_at'],'subject'=>$job['message']['subject'],'status'=>$job['status'],'recipient_count'=>$job['recipient_count']);
	if(!cg_participant_mail_store($key,$history)){return false;}
	$index = cg_participant_mail_read('cg_pmail_index',array());$before=$index;
	if($job['status']==='running'){$index[$job['id']]=$job['gallery_id'];}
	else{unset($index[$job['id']]);}
	return $before===$index || cg_participant_mail_store('cg_pmail_index',$index);
}
}

if(!function_exists('cg_participant_mail_history_import')){
function cg_participant_mail_history_import($galleryId){
	if(!cg_participant_mail_history_bootstrap()){return false;}
	foreach(cg_participant_mail_read('cg_pmail_index',array()) as $id=>$gid){
		if((int)$gid!==$galleryId){continue;}
		$job=cg_participant_mail_job($id);
		if($job && $job['status']!=='draft' && !cg_participant_mail_history_index_job($job)){return false;}
	}
	return true;
}
}

if(!function_exists('cg_participant_mail_history_delete_gallery')){
function cg_participant_mail_history_delete_gallery($galleryId){
	if(!cg_participant_mail_lock()){return false;}
	try{
		$history=cg_participant_mail_read(cg_participant_mail_history_key($galleryId),array());
		$index=cg_participant_mail_read('cg_pmail_index',array());
		foreach($index as $id=>$gid){if((int)$gid===$galleryId){$history[$id]=array();unset($index[$id]);}}
		if(!cg_participant_mail_store('cg_pmail_index',$index)){return false;}
		foreach($history as $id=>$summary){$job=cg_participant_mail_job($id);if($job){cg_participant_mail_delete_job($job);}}
		delete_option(cg_participant_mail_history_key($galleryId));
		return cg_participant_mail_composer_delete($galleryId,0);
	}finally{cg_participant_mail_unlock();}
}
}

// Retry narrowly if deletion overlaps an in-flight transport holding the queue lock.
if(!function_exists('cg_participant_mail_history_erase_user')){
function cg_participant_mail_history_erase_user($userId,$email){
	$userId=absint($userId);if(!$userId){return;}
	cg_participant_mail_composer_delete(0,$userId);
	if(!cg_participant_mail_lock()){
		if(!wp_next_scheduled('cg_participant_mail_erase_user',array($userId,$email))){wp_schedule_single_event(time()+60,'cg_participant_mail_erase_user',array($userId,$email));}
		return;
	}
	try{$ok=cg_participant_mail_history_erase_user_locked($userId,$email);}finally{cg_participant_mail_unlock();}
	if(!$ok && !wp_next_scheduled('cg_participant_mail_erase_user',array($userId,$email))){wp_schedule_single_event(time()+60,'cg_participant_mail_erase_user',array($userId,$email));}
}
}

if(!function_exists('cg_participant_mail_history_erase_user_locked')){
function cg_participant_mail_history_erase_user_locked($userId,$email){
	global $wpdb;
	$success=true;
	$keys=$wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('cg_pmail_job_').'%'));
	if($wpdb->last_error){return false;}
	foreach($keys as $key){
		if(!preg_match('/^cg_pmail_job_([a-f0-9]{32})$/D',$key,$match)){continue;}
		$job=cg_participant_mail_job($match[1]);if(!$job){continue;}$changed=false;
		for($i=0;$i<$job['chunks'];$i++){
			$chunkKey=cg_participant_mail_chunk_key($job['id'],$i);$chunk=cg_participant_mail_read($chunkKey);if(!is_array($chunk)){$success=false;continue;}$chunkChanged=false;
			foreach($chunk as &$recipient){
				if((int)$recipient['user_id']!==$userId && !(!$recipient['user_id'] && $email && strcasecmp($recipient['email'],$email)===0)){continue;}
				cg_participant_mail_history_erase_recipient($recipient);
				$chunkChanged=true;
			}
			unset($recipient);
			if($chunkChanged && !cg_participant_mail_store($chunkKey,$chunk)){return false;}
			$changed=$changed || $chunkChanged;
		}
		if((int)$job['created_by']===$userId){$job['created_by']=0;$changed=true;}
		if($changed && !cg_participant_mail_save_job($job)){return false;}
	}
	// Also remove a transport log that may have been written while deletion waited.
	$table=$wpdb->prefix.'contest_gal1ery_mails';
	if($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($table)))===$table){
		$where=$wpdb->prepare('WpUserId=%d',$userId);
		if($email!==''){$where.=$wpdb->prepare(' OR (WpUserId=0 AND ReceiverMail=%s)',$email);}
		$wpdb->query("DELETE FROM $table WHERE $where");
	}
	return $success && !$wpdb->last_error;
}
}

if(!function_exists('cg_participant_mail_history_erase_recipient')){
function cg_participant_mail_history_erase_recipient(&$recipient){
	$recipient['user_id']=0;$recipient['email']='';$recipient['first_name']='';$recipient['last_name']='';$recipient['entries']=array();$recipient['erased']=true;
	unset($recipient['mail_id']);
	if($recipient['status']==='pending'){$recipient['status']='cancelled';}
	elseif($recipient['status']==='sending'){$recipient['status']='unknown';}
}
}

add_action('cg_participant_mail_erase_user','cg_participant_mail_history_erase_user',10,2);
