<?php
if(!defined('ABSPATH')){exit;}
require_once __DIR__.'/cg-profile-field-values.php';

function cg_public_profile_field_types(){
	return array('user-text-field','user-url-field','user-comment-field','user-select-field','user-radio-field','user-check-field');
}

function cg_registry_url_fields_enabled(){
	return true;
}

function cg_public_profile_field_settings($field){
	return array('public'=>in_array($field->Field_Type,cg_public_profile_field_types(),true) && !empty($field->PublicProfile), 'display'=>$field->Field_Type==='user-url-field' && cg_registry_url_fields_enabled() ? 'link' : 'text');
}

function cg_public_profile_field_hint($field){
	$settings = cg_public_profile_field_settings($field);
	return $settings['public'] ? '<p class="cg-member-profile-hint cg-public-profile-hint">'.esc_html(cg_member_t('Visible on your public profile')).'</p>' : '';
}

function cg_public_profile_field_controls($field,$type,$index,$general){
	if(!$general){return;}
	$field = (object)array('Field_Type'=>$type,'PublicProfile'=>is_object($field) && !empty($field->PublicProfile) ? 1 : 0);
	$settings = cg_public_profile_field_settings($field);
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_border_bottom_none"><div class="cg_view_option_title"><p>Show in public profile<br><span class="cg_view_option_title_note">Visitors can see this information in uploader profiles in galleries using the new page structure. This does not change whether the field appears in the registration form.</span></p></div><div class="cg_view_option_checkbox"><input type="checkbox" class="cg_public_profile_field" name="PublicProfile['.absint($index).']"'.checked($settings['public'],true,false).'></div></div></div>';
}

function cg_public_profile_link($value){
	$value = trim($value);
	if($value==='' || strpos($value,chr(92))!==false || preg_match('/[\s<>"\x00-\x1f]/u',$value)){return '';}
	if(!preg_match('~^https?://~i',$value)){
		// A bare hostname is convenient. Other schemes must never become links.
		if(strpos($value,':')!==false || substr($value,0,2)==='//'){return '';}
		$value = 'https://'.$value;
	}
	$parts = wp_parse_url($value);
	if(!$parts || empty($parts['host']) || !empty($parts['user']) || !empty($parts['pass']) || !in_array(strtolower($parts['scheme']),array('http','https'),true)){return '';}
	if(!preg_match('/^[^.]+(?:\.[^.]+)+$/D',$parts['host'])){return '';}
	if(!filter_var($value,FILTER_VALIDATE_URL)){return '';}
	return esc_url($value,array('http','https'));
}

// Return an existing translation key. Keep the stored value independent of HTML escaping.
function cg_registry_url_field_error($value,$field){
	if(!is_string($value)){return 'Please enter a valid URL';}
	if(trim($value)===''){return !empty($field->Required) ? 'Required' : '';}
	return cg_public_profile_link($value)==='' ? 'Please enter a valid URL' : '';
}
