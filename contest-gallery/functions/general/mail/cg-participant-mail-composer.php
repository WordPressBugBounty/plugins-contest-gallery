<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_participant_mail_composer_key')){
function cg_participant_mail_composer_key($galleryId,$userId){return 'cg_pmail_composer_'.absint($galleryId).'_'.absint($userId);}
}

if(!function_exists('cg_participant_mail_composer_empty')){
function cg_participant_mail_composer_empty(){return array('revision'=>0,'message'=>false,'updated_at'=>0,'write_id'=>'');}
}

if(!function_exists('cg_participant_mail_composer_read')){
function cg_participant_mail_composer_read($galleryId,$userId){
	global $wpdb;
	$draft=cg_participant_mail_read(cg_participant_mail_composer_key($galleryId,$userId),false);
	if($wpdb->last_error){return new WP_Error('cg_pmail_draft_storage','The saved draft could not be read. Please try again.');}
	if($draft===false){return cg_participant_mail_composer_empty();}
	if(!is_array($draft) || !isset($draft['revision'],$draft['updated_at'],$draft['write_id']) || !array_key_exists('message',$draft) || !is_int($draft['revision']) || $draft['revision']<1 || ($draft['message']!==false && !is_array($draft['message']))){
		return new WP_Error('cg_pmail_draft_storage','The saved draft could not be read. Please try again.');
	}
	return $draft;
}
}

// Incomplete addresses, subjects and placeholder fragments are valid while composing.
if(!function_exists('cg_participant_mail_composer_message')){
function cg_participant_mail_composer_message($values){
	$message=array();
	foreach(array('subject','body','from_name','from_email','reply_name','reply_email') as $field){
		if(isset($values[$field]) && !is_string($values[$field])){return new WP_Error('cg_pmail_draft_input','Invalid draft field.');}
		$value=isset($values[$field]) ? wp_unslash($values[$field]) : '';
		$message[$field]=$field==='body' ? wp_kses_post($value) : sanitize_text_field($value);
	}
	return $message;
}
}

// Compare the exact stored value. Autosaves must not wait for a transport's queue lock.
if(!function_exists('cg_participant_mail_composer_write')){
function cg_participant_mail_composer_write($galleryId,$userId,$revision,$message,$writeId){
	global $wpdb;
	if(!is_int($revision) || $revision<0 || !is_string($writeId) || !preg_match('/^[a-f0-9]{32}$/D',$writeId)){
		return new WP_Error('cg_pmail_draft_input','Invalid draft revision.');
	}
	$key=cg_participant_mail_composer_key($galleryId,$userId);
	$raw=$wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s",$key));
	if($wpdb->last_error){return new WP_Error('cg_pmail_draft_storage','The draft could not be saved. Please try again.');}
	$current=$raw===null ? cg_participant_mail_composer_empty() : maybe_unserialize($raw);
	if(!is_array($current) || !isset($current['revision'],$current['write_id']) || !array_key_exists('message',$current)){
		return new WP_Error('cg_pmail_draft_storage','The saved draft could not be read. Please try again.');
	}
	// A repeated request after a lost response acknowledges only the same exact write.
	if($current['write_id']===$writeId && $current['message']===$message){return $current;}
	if($current['revision']!==$revision){return new WP_Error('cg_pmail_draft_conflict','This draft changed in another tab or device.',array('draft'=>$current));}
	$next=array('revision'=>$revision+1,'message'=>$message,'updated_at'=>time(),'write_id'=>$writeId);
	$encoded=maybe_serialize($next);
	$owners=$wpdb->prepare("EXISTS (SELECT id FROM {$wpdb->prefix}contest_gal1ery_options WHERE id=%d) AND EXISTS (SELECT ID FROM {$wpdb->users} WHERE ID=%d)",$galleryId,$userId);
	if($raw===null){
		$changed=$wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->options} (option_name,option_value,autoload) SELECT %s,%s,'no' WHERE $owners",$key,$encoded));
	}else{
		$changed=$wpdb->query($wpdb->prepare("UPDATE {$wpdb->options} SET option_value=%s,autoload='no' WHERE option_name=%s AND BINARY option_value=%s AND $owners",$encoded,$key,$raw));
	}
	wp_cache_delete($key,'options');wp_cache_delete('notoptions','options');
	if($changed===false){return new WP_Error('cg_pmail_draft_storage','The draft could not be saved. Please try again.');}
	if(!$changed){
		$current=cg_participant_mail_composer_read($galleryId,$userId);
		return is_wp_error($current) ? $current : new WP_Error('cg_pmail_draft_conflict','This draft changed in another tab or device.',array('draft'=>$current));
	}
	return $next;
}
}

if(!function_exists('cg_participant_mail_composer_open')){
function cg_participant_mail_composer_open($galleryId,$userId){
	$draft=cg_participant_mail_composer_read($galleryId,$userId);
	if(is_wp_error($draft) || $draft['revision']){return $draft;}
	// Migrate only this editor's most recent, non-expired legacy preview. A tombstone prevents resurrection.
	$latest=false;
	foreach(cg_participant_mail_read('cg_pmail_index',array()) as $id=>$gid){
		if((int)$gid!==(int)$galleryId){continue;}$job=cg_participant_mail_job($id);
		if(!$job || $job['status']!=='draft' || (int)$job['created_by']!==(int)$userId || $job['updated_at']<time()-DAY_IN_SECONDS){continue;}
		if(!$latest || $job['created_at']>$latest['created_at']){$latest=$job;}
	}
	if(!$latest){return $draft;}
	$saved=cg_participant_mail_composer_write($galleryId,$userId,0,$latest['message'],md5(wp_generate_password(64,false,false)));
	return is_wp_error($saved) && $saved->get_error_code()==='cg_pmail_draft_conflict' ? cg_participant_mail_composer_read($galleryId,$userId) : $saved;
}
}

if(!function_exists('cg_participant_mail_composer_clear_started')){
function cg_participant_mail_composer_clear_started($job){
	// Retrying delivery never clears a new composition. Only the owner and matching preview revision qualify.
	if(empty($job['composer_revision']) || (int)$job['created_by']!==get_current_user_id()){return false;}
	$draft=cg_participant_mail_composer_read($job['gallery_id'],$job['created_by']);
	if(is_wp_error($draft) || $draft['revision']!==$job['composer_revision'] || $draft['message']!==$job['message']){return false;}
	$cleared=cg_participant_mail_composer_write($job['gallery_id'],$job['created_by'],$draft['revision'],false,md5('started|'.$job['id']));
	return is_wp_error($cleared) ? false : $cleared;
}
}

if(!function_exists('cg_participant_mail_composer_delete')){
function cg_participant_mail_composer_delete($galleryId,$userId){
	global $wpdb;
	$pattern=$galleryId ? $wpdb->esc_like('cg_pmail_composer_'.absint($galleryId).'_').'%' : $wpdb->esc_like('cg_pmail_composer_').'%';
	$keys=$wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",$pattern));
	if($wpdb->last_error){return false;}
	foreach($keys as $key){
		if(preg_match('/^cg_pmail_composer_([1-9][0-9]*)_([1-9][0-9]*)$/D',$key,$match) && (!$userId || (int)$match[2]===(int)$userId)){delete_option($key);}
	}
	return !$wpdb->last_error;
}
}

if(!function_exists('cg_participant_mail_composer_deleted_user')){
function cg_participant_mail_composer_deleted_user($userId){cg_participant_mail_composer_delete(0,$userId);}
}
add_action('deleted_user','cg_participant_mail_composer_deleted_user');
