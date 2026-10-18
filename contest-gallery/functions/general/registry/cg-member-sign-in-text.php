<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_member_sign_in_text_should_enable')){
function cg_member_sign_in_text_should_enable($previousVersion,$isFreshInstall){
	if($isFreshInstall){return true;}
	if((!is_string($previousVersion) && !is_int($previousVersion) && !is_float($previousVersion)) || !preg_match('/^[0-9]+(?:\.[0-9]+)*$/D',(string)$previousVersion)){return false;}
	return floatval($previousVersion)<34.0;
}
}

if(!function_exists('cg_member_sign_in_text_default_html')){
function cg_member_sign_in_text_default_html(){
	$siteLocale = get_option('WPLANG');
	if(!$siteLocale){$siteLocale = 'en_US';}
	$switched = function_exists('switch_to_locale') && function_exists('restore_previous_locale') ? switch_to_locale($siteLocale) : false;
	try{
		$title = cg_pin_auth_t('Your entries. Your gallery.');
		$description = cg_pin_auth_t('Sign in with a code sent to your email. New here? Confirm your email to create your account.');
		return '<h2 style="text-align: center;">'.esc_html($title).'</h2><p style="text-align: center;">'.esc_html($description).'</p>';
	}finally{if($switched){restore_previous_locale();}}
}
}

// Record the source version before the DB updater changes it, even without a schema bump.
if(!function_exists('cg_member_sign_in_text_initialize')){
function cg_member_sign_in_text_initialize($previousVersion,$isFreshInstall){
	global $wpdb;
	$name = 'cg_member_sign_in_text';
	if($wpdb->get_var($wpdb->prepare("SELECT option_id FROM {$wpdb->options} WHERE option_name=%s",$name))!==null){return true;}
	$value = array('initialized'=>1,'enabled'=>cg_member_sign_in_text_should_enable($previousVersion,$isFreshInstall),'html'=>cg_member_sign_in_text_default_html());
	// WordPress add_option() uses an upsert. INSERT IGNORE preserves a concurrent saved choice.
	$inserted = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->options} (option_name,option_value,autoload) VALUES (%s,%s,'no')",$name,maybe_serialize($value)));
	wp_cache_delete($name,'options');
	wp_cache_delete('notoptions','options');
	return $inserted!==false;
}
}

if(!function_exists('cg_member_sign_in_text_settings')){
function cg_member_sign_in_text_settings(){
	$value = get_option('cg_member_sign_in_text');
	if(!is_array($value)){return array('initialized'=>0,'enabled'=>false,'html'=>'');}
	return array('initialized'=>!empty($value['initialized']) ? 1 : 0,'enabled'=>!empty($value['enabled']),
		'html'=>isset($value['html']) && is_string($value['html']) ? wp_kses_post($value['html']) : '');
}
}

if(!function_exists('cg_member_sign_in_text_has_content')){
function cg_member_sign_in_text_has_content($html){
	$text = trim(str_replace(array("\xc2\xa0",'&nbsp;'),'',wp_strip_all_tags($html)));
	return $text!=='' || preg_match('/<(?:img|video|audio|hr)\b/i',$html);
}
}

if(!function_exists('cg_member_sign_in_text_html')){
function cg_member_sign_in_text_html(){
	$settings = cg_member_sign_in_text_settings();
	return $settings['enabled'] && cg_member_sign_in_text_has_content($settings['html']) ? $settings['html'] : '';
}
}

if(!function_exists('cg_member_sign_in_text_save')){
function cg_member_sign_in_text_save(){
	if(!current_user_can('manage_options') || empty($_POST['cg_member_sign_in_text_present'])){return;}
	$html = isset($_POST['MemberSignInText']) && is_string($_POST['MemberSignInText']) ? wp_kses_post(wp_unslash($_POST['MemberSignInText'])) : '';
	update_option('cg_member_sign_in_text',array('initialized'=>1,'enabled'=>!empty($_POST['UseCustomMemberSignInText']),'html'=>$html),false);
}
}

if(!function_exists('cg_member_sign_in_text_render_options')){
function cg_member_sign_in_text_render_options(){
	$settings = cg_member_sign_in_text_settings();
	if(!$settings['initialized']){$settings['html'] = cg_member_sign_in_text_default_html();}
	$disabled = current_user_can('manage_options') ? '' : ' disabled';
	echo '<div class="cg_view_options_rows_container cg_margin_bottom_30 cg_go_to_target" data-cg-go-to-target="MemberSignInTextOptions"><p class="cg_view_options_rows_container_title">Sign-in dialog text</p>';
	if(!$disabled){echo '<input type="hidden" name="cg_member_sign_in_text_present" value="1">';}
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px"><div class="cg_view_option_title"><p>Use custom sign-in text<br><span class="cg_view_option_title_note">Use the text below instead of the dialog title and description from Edit translations.</span></p></div><div class="cg_view_option_checkbox"><input type="checkbox" id="UseCustomMemberSignInText" name="UseCustomMemberSignInText" value="1"'.($settings['enabled'] ? ' checked' : '').$disabled.'></div></div></div>';
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_top_none cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px" id="wp-MemberSignInText-wrap-Container"><div class="cg_view_option_title"><p>Title and description<br><span class="cg_view_option_title_note">Shown above the gallery and upload sign-in options. Leave empty to use the existing translations.</span></p></div><div class="cg_view_option_html"><textarea class="cg-wp-editor-template" id="MemberSignInText" name="MemberSignInText"'.$disabled.'>'.esc_textarea($settings['html']).'</textarea></div></div></div></div>';
}
}
