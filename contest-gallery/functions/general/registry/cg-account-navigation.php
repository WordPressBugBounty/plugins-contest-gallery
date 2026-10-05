<?php
if(!defined('ABSPATH')){exit;}

// Version zero deliberately retains the old, action-specific role rules.
function cg_account_navigation_settings($row = null){
	if($row === null){
		global $wpdb, $cg_account_navigation_settings_cache;
		if(isset($cg_account_navigation_settings_cache[$wpdb->prefix])){return $cg_account_navigation_settings_cache[$wpdb->prefix];}
		if(function_exists('cg_database_install_is_pending') && cg_database_install_is_pending()){$row = array();}
		else{$row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}contest_gal1ery_registry_and_login_options WHERE GeneralID = 1 LIMIT 1",ARRAY_A);}
		$settings = cg_account_navigation_settings($row ? $row : array());
		$cg_account_navigation_settings_cache[$wpdb->prefix] = $settings;
		return $settings;
	}
	$row = (array)$row;
	$version = !empty($row['AccountNavigationVersion']) ? 1 : 0;
	$settings = array('AccountNavigationVersion'=>$version);
	foreach(array('LogoutLink','BackToGalleryLink','LogoutFallbackLink','BackToGalleryFallbackLink') as $key){
		$settings[$key] = isset($row[$key]) && is_string($row[$key]) ? html_entity_decode($row[$key],ENT_QUOTES,'UTF-8') : '';
	}
	$roles = $version && isset($row['AccountNavigationRoles']) ? maybe_unserialize($row['AccountNavigationRoles']) : array('contest_gallery_user'=>1,'contest_gallery_user_since_v14'=>1);
	$settings['AccountNavigationRoles'] = is_array($roles) ? $roles : array();
	$settings['AccountNavigationNoRole'] = $version && !empty($row['AccountNavigationNoRole']) ? 1 : 0;
	$settings['LogoutApplyToWpLinks'] = !$version || !empty($row['LogoutApplyToWpLinks']) ? 1 : 0;
	$settings['LogoutRedirectMode'] = $version && isset($row['LogoutRedirectMode']) ? intval($row['LogoutRedirectMode']) : ($settings['LogoutLink'] !== '' ? 2 : 0);
	$settings['BackToGalleryMode'] = $version && isset($row['BackToGalleryMode']) ? intval($row['BackToGalleryMode']) : ($settings['BackToGalleryLink'] !== '' ? 2 : 0);
	return $settings;
}

function cg_account_navigation_pro_available(){
	return cg_get_version()==='contest-gallery-pro' && function_exists('contest_gal1ery_key_check') && contest_gal1ery_key_check();
}

function cg_account_navigation_user_allowed($user,$settings){
	if(!$user || empty($user->ID)){return false;}
	$roles = (array)$user->roles;
	if(!$roles){return !empty($settings['AccountNavigationNoRole']);}
	return (bool)array_intersect($roles,array_keys($settings['AccountNavigationRoles']));
}

// Only a real first installation opts into automatic navigation. Repairs and upgrades never do.
function cg_account_navigation_install_defaults($table){
	global $wpdb, $cg_account_navigation_settings_cache;
	if($wpdb->get_var("SELECT id FROM $table WHERE GeneralID = 1 LIMIT 1")){return;}
	cg_create_registry_and_login_options();
	$roles = array('contest_gallery_user_since_v14'=>1);
	if(get_role('contest_gallery_user')){$roles['contest_gallery_user'] = 1;}
	$wpdb->update($table,array(
		'AccountNavigationVersion'=>1,'AccountNavigationRoles'=>serialize($roles),
		'AccountNavigationNoRole'=>0,'LogoutApplyToWpLinks'=>0,
		'LogoutRedirectMode'=>cg_get_version()==='contest-gallery-pro' ? 1 : 0,
		'BackToGalleryMode'=>cg_get_version()==='contest-gallery-pro' ? 1 : 0
	),array('GeneralID'=>1));
	unset($cg_account_navigation_settings_cache[$wpdb->prefix]);
}

// Called inside the existing nonce-checked options save. Missing/disabled groups preserve data.
function cg_account_navigation_prepare_save($submitted,$row){
	$current = cg_account_navigation_settings($row);
	$preserve = array('LogoutLink'=>isset($row['LogoutLink']) ? $row['LogoutLink'] : '', 'BackToGalleryLink'=>isset($row['BackToGalleryLink']) ? $row['BackToGalleryLink'] : '');
	if(!cg_account_navigation_pro_available() || !cg_user_can_manage_global_settings() || empty($submitted['AccountNavigationPresent'])){return $preserve;}
	foreach(array('LogoutRedirectMode','BackToGalleryMode') as $key){
		if(!isset($submitted[$key]) || !is_scalar($submitted[$key]) || !in_array((string)$submitted[$key],array('0','1','2'),true)){return $preserve;}
	}
	$next = $current;
	$next['AccountNavigationRoles'] = array();
	$postedRoles = isset($submitted['AccountNavigationRoles']) && is_array($submitted['AccountNavigationRoles']) ? $submitted['AccountNavigationRoles'] : array();
	foreach(wp_roles()->roles as $role=>$details){
		if(!empty($postedRoles[$role]) && is_scalar($postedRoles[$role])){$next['AccountNavigationRoles'][$role] = 1;}
	}
	foreach(array('AccountNavigationNoRole','LogoutApplyToWpLinks') as $key){$next[$key] = !empty($submitted[$key]) && is_scalar($submitted[$key]) ? 1 : 0;}
	foreach(array('LogoutRedirectMode','BackToGalleryMode') as $key){$next[$key] = intval($submitted[$key]);}
	foreach(array('LogoutLink','BackToGalleryLink','LogoutFallbackLink','BackToGalleryFallbackLink') as $key){
		if(!isset($submitted[$key]) || !is_string($submitted[$key])){return $preserve;}
		$next[$key] = esc_url_raw(html_entity_decode($submitted[$key],ENT_QUOTES,'UTF-8'),array('http','https'));
		if(strlen($next[$key]) > 999){return $preserve;}
	}
	// Do not switch legacy installations merely by saving another option on the same form.
	$comparison = $current;
	foreach(array_keys($comparison['AccountNavigationRoles']) as $role){if(!get_role($role)){unset($comparison['AccountNavigationRoles'][$role]);}}
	ksort($comparison['AccountNavigationRoles']); ksort($next['AccountNavigationRoles']);
	if(!$current['AccountNavigationVersion'] && $comparison == $next){return $preserve;}
	$next['AccountNavigationVersion'] = 1;
	$next['AccountNavigationRoles'] = serialize($next['AccountNavigationRoles']);
	foreach(array('LogoutLink','BackToGalleryLink','LogoutFallbackLink','BackToGalleryFallbackLink') as $key){
		$next[$key] = htmlspecialchars($next[$key],ENT_QUOTES,'UTF-8');
	}
	return $next;
}

// Return context is per link/tab, never a shared user-meta or cookie "last gallery".
function cg_account_navigation_frontend_url($url){
	if(!is_string($url) || strlen($url)>4096){return '';}
	$url = esc_url_raw($url,array('http','https'));
	$parts = wp_parse_url($url); $home = wp_parse_url(home_url('/'));
	if(!$parts || empty($parts['host']) || empty($parts['scheme']) || !empty($parts['user']) || !empty($parts['pass']) || strtolower($parts['host']) !== strtolower($home['host'])){return '';}
	$port = isset($parts['port']) ? $parts['port'] : ($parts['scheme']==='https' ? 443 : 80);
	$homePort = isset($home['port']) ? $home['port'] : ($home['scheme']==='https' ? 443 : 80);
	if($parts['scheme'] !== $home['scheme'] || $port !== $homePort){return '';}
	$path = isset($parts['path']) ? rawurldecode($parts['path']) : '/';
	if(preg_match('~(?:^|/)(?:wp-admin|wp-login\.php)(?:/|$)~i',$path)){return '';}
	return remove_query_arg(array('cg_account_return','cg_account_logout','cgLogoutLink','_wpnonce','cgkey'),$url);
}

function cg_account_navigation_return_url(){
	foreach(array($_POST,$_GET) as $request){
		if(isset($request['cg_account_return']) && is_string($request['cg_account_return'])){
			return cg_account_navigation_frontend_url(wp_unslash($request['cg_account_return']));
		}
	}
	if(!is_admin() && isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI'])){
		$home = wp_parse_url(home_url('/'));
		$origin = $home['scheme'].'://'.$home['host'].(isset($home['port']) ? ':'.$home['port'] : '');
		return cg_account_navigation_frontend_url($origin.wp_unslash($_SERVER['REQUEST_URI']));
	}
	return '';
}

function cg_account_navigation_logout_url($redirect = ''){
	$url = wp_logout_url($redirect);
	$settings = cg_account_navigation_settings();
	if(!$settings['AccountNavigationVersion'] || !cg_account_navigation_pro_available() || !cg_account_navigation_user_allowed(wp_get_current_user(),$settings)){return $url;}
	$url = add_query_arg('cg_account_logout','1',html_entity_decode($url,ENT_QUOTES,'UTF-8'));
	$return = cg_account_navigation_return_url();
	return $return ? add_query_arg('cg_account_return',rawurlencode($return),$url) : $url;
}

function cg_account_navigation_logout_target($user,$return,$isCgLogout,$settings = null){
	if($settings === null){$settings = cg_account_navigation_settings();}
	if(!$settings['AccountNavigationVersion'] || !cg_account_navigation_pro_available() || !cg_account_navigation_user_allowed($user,$settings) || (!$isCgLogout && !$settings['LogoutApplyToWpLinks'])){return '';}
	if($settings['LogoutRedirectMode']===2){return wp_validate_redirect($settings['LogoutLink'],'');}
	if($settings['LogoutRedirectMode']===1){
		$origin = cg_account_navigation_frontend_url($return);
		return $origin ? $origin : wp_validate_redirect($settings['LogoutFallbackLink'],'');
	}
	return '';
}

function cg_account_navigation_logout_redirect($redirect,$requested,$user){
	$target = cg_account_navigation_logout_target($user,cg_account_navigation_return_url(),!empty($_GET['cg_account_logout']));
	return $target !== '' ? $target : $redirect;
}
add_filter('logout_redirect','cg_account_navigation_logout_redirect',10,3);

function cg_account_navigation_back_url($user){
	$settings = cg_account_navigation_settings();
	if(!$settings['AccountNavigationVersion']){return $settings['BackToGalleryLink'];}
	if(!cg_account_navigation_pro_available() || !cg_account_navigation_user_allowed($user,$settings)){return '';}
	if($settings['BackToGalleryMode']===2){return $settings['BackToGalleryLink'];}
	if($settings['BackToGalleryMode']===1){
		$return = cg_account_navigation_return_url();
		return $return ? $return : $settings['BackToGalleryFallbackLink'];
	}
	return '';
}

function cg_account_navigation_profile_url($url,$userId){
	$settings = cg_account_navigation_settings();
	if(!$settings['AccountNavigationVersion'] || !cg_account_navigation_pro_available() || intval($userId)!==get_current_user_id() || !cg_account_navigation_user_allowed(wp_get_current_user(),$settings)){return $url;}
	$return = cg_account_navigation_return_url();
	return $return ? add_query_arg('cg_account_return',rawurlencode($return),$url) : $url;
}
add_filter('edit_profile_url','cg_account_navigation_profile_url',10,2);

function cg_account_navigation_profile_field($user){
	global $pagenow;
	if($pagenow !== 'profile.php' || intval($user->ID)!==get_current_user_id()){return;}
	$settings = cg_account_navigation_settings();
	if(!$settings['AccountNavigationVersion'] || !cg_account_navigation_pro_available() || !cg_account_navigation_user_allowed($user,$settings)){return;}
	$return = cg_account_navigation_return_url();
	if($return){echo '<input type="hidden" name="cg_account_return" value="'.esc_attr($return).'">';}
}
add_action('personal_options','cg_account_navigation_profile_field');

function cg_account_navigation_profile_redirect($url){
	global $pagenow;
	if($pagenow==='profile.php' && wp_parse_url($url,PHP_URL_PATH)===wp_parse_url(self_admin_url('profile.php'),PHP_URL_PATH)){
		return cg_account_navigation_profile_url($url,get_current_user_id());
	}
	return $url;
}
add_filter('wp_redirect','cg_account_navigation_profile_redirect');

function cg_account_navigation_admin_bar(){
	global $pagenow, $wp_admin_bar;
	$settings = cg_account_navigation_settings(); $user = wp_get_current_user();
	if($pagenow!=='profile.php' || !$settings['AccountNavigationVersion'] || !cg_account_navigation_pro_available() || !cg_account_navigation_user_allowed($user,$settings)){return;}
	$back = cg_account_navigation_back_url($user);
	if(!$back){return;}
	$is_frontend = true;
	include(__DIR__.'/../../../check-language-general.php');
	$account = $wp_admin_bar->get_node('contest_gallery_user_bar');
	// The legacy role uses this ID for its account menu, not for a back link.
	$isLegacyAccountMenu = $account && empty($account->parent);
	$parent = $wp_admin_bar->get_node('cg_account') ? 'cg_account' : ($isLegacyAccountMenu ? 'contest_gallery_user_bar' : 'user-actions');
	$wp_admin_bar->add_node(array('id'=>$isLegacyAccountMenu ? 'cg_account_back_to_gallery' : 'contest_gallery_user_bar','parent'=>$parent,'title'=>$language_BackToGallery,'href'=>esc_url($back)));
}
add_action('wp_before_admin_bar_render','cg_account_navigation_admin_bar',20);

function cg_account_navigation_enqueue(){
	global $pagenow;
	if(!is_user_logged_in() || !cg_account_navigation_pro_available()){return;}
	$settings = cg_account_navigation_settings();
	if(!$settings['AccountNavigationVersion'] || !cg_account_navigation_user_allowed(wp_get_current_user(),$settings) || (is_admin() && $pagenow!=='profile.php')){return;}
	wp_enqueue_script('cg-account-navigation',plugins_url('../../../v10/v10-js/frontend/account-navigation.js',__FILE__),array(),cg_get_version_for_scripts(),true);
	wp_localize_script('cg-account-navigation','cgAccountNavigation',array('profileUrl'=>self_admin_url('profile.php'),'frontend'=>!is_admin(),'returnUrl'=>cg_account_navigation_return_url(),'allLogoutLinks'=>(bool)$settings['LogoutApplyToWpLinks']));
}
add_action('wp_enqueue_scripts','cg_account_navigation_enqueue');
add_action('admin_enqueue_scripts','cg_account_navigation_enqueue');
