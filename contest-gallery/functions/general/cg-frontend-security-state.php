<?php
if(!defined('ABSPATH')){exit;}

// Security decisions must not reuse per-request or persistent WordPress caches.
// The fixed-width expiry prefix allows bounded cleanup without scanning live records.
function cg_security_state_key($scope,$subject){
	return '_cg_security_v1_'.sanitize_key($scope).'_'.hash_hmac('sha256',(string)$subject,wp_salt('auth'));
}
function cg_security_state_read($scope,$subject){
	global $wpdb;
	$raw=$wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s",cg_security_state_key($scope,$subject)));
	if(!is_string($raw) || strlen($raw)<12 || $raw[10]!==':' || !ctype_digit(substr($raw,0,10)) || (int)substr($raw,0,10)<=time()){return false;}
	return json_decode(substr($raw,11),true);
}
function cg_security_state_write($scope,$subject,$value,$ttl){
	global $wpdb;
	$json=wp_json_encode($value);
	if($json===false){return false;}
	$key=cg_security_state_key($scope,$subject);
	$raw=sprintf('%010d',time()+max(1,(int)$ttl)).':'.$json;
	$written=$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->options} (option_name,option_value,autoload) VALUES (%s,%s,'no') ON DUPLICATE KEY UPDATE option_value=VALUES(option_value),autoload='no'",$key,$raw));
	wp_cache_delete($key,'options');
	if($written===false){return false;}
	if(!wp_next_scheduled('cg_frontend_security_cleanup')){wp_schedule_event(time()+HOUR_IN_SECONDS,'hourly','cg_frontend_security_cleanup');}
	return true;
}
function cg_security_state_delete($scope,$subject){
	global $wpdb;
	$key=cg_security_state_key($scope,$subject);
	$result=$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name=%s",$key));
	wp_cache_delete($key,'options');
	return $result!==false;
}
function cg_security_state_lock($scope,$subject,$timeout=5){
	global $wpdb;
	$name='cg-sec-'.md5(DB_NAME.'|'.$wpdb->prefix.'|'.$scope.'|'.$subject);
	return (string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,%d)',$name,absint($timeout)))==='1' ? $name : false;
}
function cg_security_state_unlock($name){
	global $wpdb;
	if($name){$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$name));}
}
function cg_frontend_security_cleanup(){
	global $wpdb;
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %s LIMIT 250",$wpdb->esc_like('_cg_security_v1_').'%',sprintf('%010d',time()).':'));
}
add_action('cg_frontend_security_cleanup','cg_frontend_security_cleanup');

// Only call while holding the scope/subject lock or a common admission lock.
function cg_security_rate_take_locked($scope,$subject,$limit,$window,$cooldown = 0){
	$now=time(); $recent=array();
	$stored=cg_security_state_read($scope,$subject);
	foreach(is_array($stored) ? $stored : array() as $stamp){
		if(!is_numeric($stamp) || $stamp>$now){return false;}
		if($stamp>$now-$window){$recent[]=(int)$stamp;}
	}
	if(count($recent)>=$limit || ($cooldown && $recent && max($recent)>$now-$cooldown)){return false;}
	$recent[]=$now;
	return cg_security_state_write($scope,$subject,$recent,$window);
}
function cg_security_rate_take($scope,$subject,$limit,$window,$cooldown = 0){
	$lock=cg_security_state_lock($scope,$subject);
	if(!$lock){return false;}
	try{return cg_security_rate_take_locked($scope,$subject,$limit,$window,$cooldown);}
	finally{cg_security_state_unlock($lock);}
}
function cg_security_remote_address(){
	// Forwarded headers are caller-controlled unless the server normalizes them.
	return isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
}
