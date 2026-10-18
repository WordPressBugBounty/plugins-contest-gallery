<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_member_replacement_limits')){
function cg_member_replacement_limits($user){
	$defaults = array('hour'=>20,'day'=>50,'attachments'=>100,'bytes'=>2147483648);
	$limits = apply_filters('cg_member_replacement_limits',$defaults,absint($user));
	$limits = is_array($limits) ? array_merge($defaults,$limits) : $defaults;
	foreach($defaults as $key=>$value){$limits[$key] = max(1,is_numeric($limits[$key]) ? (float)$limits[$key] : $value);}
	return $limits;
}
}

if(!function_exists('cg_member_replacement_attempt_key')){
function cg_member_replacement_attempt_key(){
	global $wpdb;
	return '_cg_member_replace_attempts_'.$wpdb->prefix;
}
}

if(!function_exists('cg_member_replacement_storage_error')){
function cg_member_replacement_storage_error(){
	return new WP_Error('storage','Your replacement storage limit has been reached. Please contact the gallery administrator.');
}
}

if(!function_exists('cg_member_replacement_usage')){
function cg_member_replacement_usage($user){
	global $wpdb;
	// Retained attachments count even in the trash. Actual deletion releases their budget.
	$row = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) AS count, COALESCE(SUM(CAST(bytes.meta_value AS UNSIGNED)),0) AS bytes FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} owner ON owner.post_id=p.ID AND owner.meta_key='_cg_member_replacement_owner' LEFT JOIN {$wpdb->postmeta} bytes ON bytes.post_id=p.ID AND bytes.meta_key='_cg_member_replacement_bytes' WHERE p.post_type='attachment' AND owner.meta_value=%s",(string)absint($user)),ARRAY_A);
	$pending = cg_upload_job_replacement_usage($user);
	return $row ? array('count'=>(int)$row['count']+$pending['count'],'bytes'=>(float)$row['bytes']+$pending['bytes']) : false;
}
}

// Called while the per-site, per-member lock is held, before media processing.
if(!function_exists('cg_member_replacement_admit')){
function cg_member_replacement_admit($user){
	$limits = cg_member_replacement_limits($user);
	$usage = cg_member_replacement_usage($user);
	if($usage===false){return new WP_Error('busy','Please try again.');}
	if($usage['count']>=$limits['attachments'] || $usage['bytes']>=$limits['bytes']){return cg_member_replacement_storage_error();}
	wp_cache_delete($user,'user_meta');
	$attempts = get_user_meta($user,cg_member_replacement_attempt_key(),true);
	$attempts = is_array($attempts) ? $attempts : array();
	$recent = array(); $hour = 0; $now = time();
	foreach($attempts as $timestamp){
		if($timestamp>$now-DAY_IN_SECONDS){$recent[]=$timestamp;}
		if($timestamp>$now-HOUR_IN_SECONDS){$hour++;}
	}
	if($hour>=$limits['hour'] || count($recent)>=$limits['day']){return new WP_Error('rate','Too many replacement attempts. Please try again later.');}
	$recent[] = $now;
	if(!update_user_meta($user,cg_member_replacement_attempt_key(),$recent)){return new WP_Error('busy','Please try again.');}
	return array('limits'=>$limits,'usage'=>$usage);
}
}

if(!function_exists('cg_member_replacement_attachment_bytes')){
function cg_member_replacement_attachment_bytes($attachment){
	$paths = array();
	foreach(cg_entry_watermark_get_public_files($attachment) as $file){$paths[$file['public_path']]=true;}
	$watermark = get_post_meta($attachment,cg_entry_watermark_meta_key(),true);
	if(!empty($watermark['files']) && is_array($watermark['files'])){
		foreach($watermark['files'] as $file){
			if(empty($file['protected_relative'])){continue;}
			$path = cg_entry_watermark_absolute_path($file['protected_relative']);
			if($path && is_file($path)){$paths[$path]=true;}
		}
	}
	$bytes = 0;
	foreach($paths as $path=>$unused){$bytes += (float)filesize($path);}
	return $bytes;
}
}

if(!function_exists('cg_member_replacement_track_storage')){
function cg_member_replacement_track_storage($user,$attachment,$preview,$budget){
	$files = array($attachment);
	if($preview){$files[]=$preview;}
	$bytes = array();
	foreach($files as $id){$bytes[$id]=cg_member_replacement_attachment_bytes($id);}
	if($budget['usage']['count']+count($files)>$budget['limits']['attachments'] || $budget['usage']['bytes']+array_sum($bytes)>$budget['limits']['bytes']){return cg_member_replacement_storage_error();}
	foreach($bytes as $id=>$size){
		if(!$size || !add_post_meta($id,'_cg_member_replacement_owner',absint($user),true) || !add_post_meta($id,'_cg_member_replacement_bytes',$size,true)){return new WP_Error('save','Please try again.');}
	}
	return true;
}
}
