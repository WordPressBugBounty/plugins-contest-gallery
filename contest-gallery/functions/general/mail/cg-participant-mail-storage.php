<?php
if(!defined('ABSPATH')){exit;}

// Read durable state from the database, not a request-local/persistent option cache.
if(!function_exists('cg_participant_mail_read')){
function cg_participant_mail_read($key,$default=false){
	global $wpdb;
	$value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s",$key));
	return $value===null ? $default : maybe_unserialize($value);
}
}

if(!function_exists('cg_participant_mail_lock_name')){
function cg_participant_mail_lock_name(){global $wpdb;return 'cg-pmail-'.md5(DB_NAME.'|'.$wpdb->prefix);}
}
if(!function_exists('cg_participant_mail_lock')){
function cg_participant_mail_lock(){
	global $wpdb;
	return (string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',cg_participant_mail_lock_name()))==='1';
}
}
if(!function_exists('cg_participant_mail_has_lock')){
function cg_participant_mail_has_lock(){
	global $wpdb;
	return (string)$wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)=CONNECTION_ID()',cg_participant_mail_lock_name()))==='1';
}
}
if(!function_exists('cg_participant_mail_unlock')){
function cg_participant_mail_unlock(){global $wpdb;$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',cg_participant_mail_lock_name()));}
}

if(!function_exists('cg_participant_mail_store')){
function cg_participant_mail_store($key,$value){
	global $wpdb;
	// A reconnect must not let an old worker overwrite the next worker's state.
	$encoded = maybe_serialize($value);
	$lock = cg_participant_mail_lock_name();
	$insert = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->options} (option_name,option_value,autoload)
		SELECT %s,%s,'no' WHERE IS_USED_LOCK(%s)=CONNECTION_ID()",$key,$encoded,$lock));
	if($insert===false){return false;}
	$update = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->options} SET option_value=%s,autoload='no'
		WHERE option_name=%s AND IS_USED_LOCK(%s)=CONNECTION_ID()",$encoded,$key,$lock));
	wp_cache_delete($key,'options');wp_cache_delete('notoptions','options');
	return $update!==false && cg_participant_mail_has_lock();
}
}

if(!function_exists('cg_participant_mail_valid_id')){
function cg_participant_mail_valid_id($id){return is_string($id) && preg_match('/^[a-f0-9]{32}$/D',$id);}
}
if(!function_exists('cg_participant_mail_job')){
function cg_participant_mail_job($id){return cg_participant_mail_valid_id($id) ? cg_participant_mail_read('cg_pmail_job_'.$id) : false;}
}
if(!function_exists('cg_participant_mail_save_job')){
function cg_participant_mail_save_job($job){
	$job['updated_at']=time();
	return cg_participant_mail_store('cg_pmail_job_'.$job['id'],$job) && cg_participant_mail_history_index_job($job);
}
}
if(!function_exists('cg_participant_mail_chunk_key')){
function cg_participant_mail_chunk_key($id,$number){return 'cg_pmail_chunk_'.$id.'_'.absint($number);}
}
if(!function_exists('cg_participant_mail_stopped')){
function cg_participant_mail_stopped($id){return cg_participant_mail_read('cg_pmail_stop_'.$id,false)!==false;}
}

if(!function_exists('cg_participant_mail_request_stop')){
function cg_participant_mail_request_stop($id){
	global $wpdb;
	$result = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->options} (option_name,option_value,autoload) VALUES (%s,'1','no')",'cg_pmail_stop_'.$id));
	wp_cache_delete('cg_pmail_stop_'.$id,'options');wp_cache_delete('notoptions','options');
	return $result!==false;
}
}

if(!function_exists('cg_participant_mail_schedule')){
function cg_participant_mail_schedule($when){
	$when = max(time(),absint($when));
	$next = wp_next_scheduled('cg_participant_mail_worker');
	if(!$next || $next>$when+1){
		if($next){wp_unschedule_event($next,'cg_participant_mail_worker');}
		wp_schedule_single_event($when,'cg_participant_mail_worker');
	}
}
}

if(!function_exists('cg_participant_mail_counts')){
function cg_participant_mail_counts($job){
	$counts = array('pending'=>0,'sending'=>0,'accepted'=>0,'failed'=>0,'unknown'=>0,'cancelled'=>0);
	for($i=0;$i<$job['chunks'];$i++){
		$chunk = cg_participant_mail_read(cg_participant_mail_chunk_key($job['id'],$i));
		if(!is_array($chunk)){return false;}
		foreach($chunk as $recipient){if(isset($counts[$recipient['status']])){$counts[$recipient['status']]++;}}
	}
	return $counts;
}
}

if(!function_exists('cg_participant_mail_delete_job')){
function cg_participant_mail_delete_job($job){
	for($i=0;$i<$job['chunks'];$i++){delete_option(cg_participant_mail_chunk_key($job['id'],$i));}
	delete_option('cg_pmail_stop_'.$job['id']);delete_option('cg_pmail_job_'.$job['id']);
}
}

if(!function_exists('cg_participant_mail_cleanup')){
function cg_participant_mail_cleanup(){
	if(!cg_participant_mail_lock()){return;}
	try{
		$index = cg_participant_mail_read('cg_pmail_index',array());
		foreach($index as $id=>$galleryId){
			$job = cg_participant_mail_job($id);
			if(!$job){unset($index[$id]);continue;}
			if($job['status']!=='draft'){
				if(!cg_participant_mail_history_index_job($job)){return;}
				continue;
			}
			if($job['updated_at']<time()-DAY_IN_SECONDS){
				cg_participant_mail_delete_job($job);unset($index[$id]);
			}
		}
		// index_job may have removed completed jobs. Do not restore its old snapshot.
		$current = cg_participant_mail_read('cg_pmail_index',array());
		foreach($current as $id=>$galleryId){if(!isset($index[$id])){unset($current[$id]);}}
		cg_participant_mail_store('cg_pmail_index',$current);
	}finally{cg_participant_mail_unlock();}
}
}

add_action('cg_participant_mail_cleanup','cg_participant_mail_cleanup');
include_once(__DIR__.'/cg-participant-mail-history-storage.php');
