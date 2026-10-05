<?php
if(!defined('ABSPATH')){exit;}

function cg_account_navigation_options_container($row,$galleryDbVersion,$proClass){
	$settings = cg_account_navigation_settings($row);
	if(!cg_account_navigation_pro_available()){$proClass = 'cg-pro-false';}
	$allowed = intval($galleryDbVersion)>=14 && cg_account_navigation_pro_available() && cg_user_can_manage_global_settings();
	$roles = get_editable_roles();
	echo '<p class="cg_view_options_rows_container_title" style="margin-top:30px;">Logout and profile navigation</p>';
	echo '<div class="cg_member_options_group cg_margin_bottom_30" id="cgAccountNavigationOptions">';
	if(intval($galleryDbVersion)<14){echo '<p class="cg-member-unavailable-note">Available for galleries created or copied in version 14 or later.</p>';}
	$disabledClass = !$allowed && !$proClass ? ' class="cg_disabled"' : '';
	echo '<fieldset'.(!$allowed ? ' disabled' : '').$disabledClass.' style="border:0;margin:0;padding:0;min-width:0;">';
	echo '<input type="hidden" name="AccountNavigationPresent" value="1">';
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px"><div class="cg_view_option_title"><p>Apply to these users<br><span class="cg_view_option_title_note">The selected roles apply to both settings below. Profile editing permissions are configured separately.<br>These settings are shared by all galleries. Only administrators can change them.</span></p></div></div></div>';
	$items = array();
	foreach($roles as $role=>$details){$items[] = array('name'=>'AccountNavigationRoles['.$role.']','label'=>translate_user_role($details['name']),'checked'=>isset($settings['AccountNavigationRoles'][$role]));}
	$items[] = array('name'=>'AccountNavigationNoRole','label'=>'Users without a role','checked'=>!empty($settings['AccountNavigationNoRole']));
	while(count($items)%4){$items[] = null;}
	foreach($items as $index=>$item){
		if($index%4===0){echo '<div class="cg_view_options_row">';}
		$border = $index%4 ? ' cg_border_left_none' : '';
		echo '<div class="cg_view_option cg_view_option_25_percent cg_border_top_none'.$border.($item ? ' cg_view_option_checkbox_container '.esc_attr($proClass) : ' cg_pointer_events_none').'">';
		if($item){
			echo '<div class="cg_view_option_title"><p>'.esc_html($item['label']).'</p></div><div class="cg_view_option_checkbox"><input type="checkbox" name="'.esc_attr($item['name']).'" aria-label="'.esc_attr($item['label']).'" value="1"'.($item['checked'] ? ' checked' : '').'></div>';
		}else{echo '<div class="cg_view_option_title"><p>&nbsp;</p></div>';}
		echo '</div>';
		if($index%4===3){echo '</div>';}
	}
	cg_account_navigation_mode_option('LogoutRedirectMode','After logout',array(1=>'Return to the previous frontend page',0=>'Use WordPress default',2=>'Redirect to a custom URL'),$settings['LogoutRedirectMode'],'Automatic return opens the previous frontend page as a signed-out visitor.',$proClass);
	cg_account_navigation_url_option('LogoutLink','Logout URL',$settings['LogoutLink'],'LogoutRedirectMode',2,$settings['LogoutRedirectMode'],'',$proClass);
	cg_account_navigation_url_option('LogoutFallbackLink','Fallback URL',$settings['LogoutFallbackLink'],'LogoutRedirectMode',1,$settings['LogoutRedirectMode'],'Optional. Used when no previous frontend page is available. If empty, WordPress decides where to go.',$proClass);
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_view_option_checkbox_container cg_border_top_none '.esc_attr($proClass).'"><div class="cg_view_option_title"><p>Also apply to other WordPress logout links<br><span class="cg_view_option_title_note">If unchecked, only Contest Gallery logout actions use the setting above.</span></p></div><div class="cg_view_option_checkbox"><input type="checkbox" name="LogoutApplyToWpLinks" aria-label="Also apply to other WordPress logout links" value="1"'.($settings['LogoutApplyToWpLinks'] ? ' checked' : '').'></div></div></div>';
	cg_account_navigation_mode_option('BackToGalleryMode','Back from WordPress profile',array(1=>'Return to the previous frontend page',2=>'Link to a custom URL',0=>'Hide the back link'),$settings['BackToGalleryMode'],'Controls the back link in the WordPress profile. The gallery profile dialog stays on its current page.',$proClass);
	cg_account_navigation_url_option('BackToGalleryLink','Back to gallery URL',$settings['BackToGalleryLink'],'BackToGalleryMode',2,$settings['BackToGalleryMode'],'',$proClass);
	cg_account_navigation_url_option('BackToGalleryFallbackLink','Fallback URL',$settings['BackToGalleryFallbackLink'],'BackToGalleryMode',1,$settings['BackToGalleryMode'],'Optional. Used when no previous frontend page is available. If empty, no back link is shown.',$proClass);
	echo '</fieldset></div>';
}

function cg_account_navigation_mode_option($name,$title,$choices,$selected,$note,$proClass = ''){
	$bottomClass = $name==='BackToGalleryMode' && $selected===0 ? ' cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px' : '';
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_top_none'.$bottomClass.' '.esc_attr($proClass).'"><div class="cg_view_option_title"><p>'.esc_html($title).'<br><span class="cg_view_option_title_note">'.esc_html($note).'</span></p></div><div class="cg_view_option_select"><select name="'.esc_attr($name).'" class="cg_account_navigation_mode" aria-label="'.esc_attr($title).'">';
	foreach($choices as $value=>$label){echo '<option value="'.intval($value).'"'.selected($selected,$value,false).'>'.esc_html($label).'</option>';}
	echo '</select></div></div></div>';
}

function cg_account_navigation_url_option($name,$title,$value,$modeName,$modeValue,$currentMode,$note = '',$proClass = ''){
	$bottomClass = $modeName==='BackToGalleryMode' ? ' cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px' : '';
	echo '<div class="cg_view_options_row'.($modeValue!==$currentMode ? ' cg_hide' : '').'" data-cg-account-mode="'.esc_attr($modeName).'" data-cg-account-value="'.intval($modeValue).'"><div class="cg_view_option cg_view_option_full_width cg_border_top_none'.$bottomClass.' '.esc_attr($proClass).'"><div class="cg_view_option_title"><p>'.esc_html($title).($note ? '<br><span class="cg_view_option_title_note">'.esc_html($note).'</span>' : '').'</p></div><div class="cg_view_option_input"><input type="text" name="'.esc_attr($name).'" aria-label="'.esc_attr($title).'" maxlength="999" value="'.esc_attr($value).'"></div></div></div>';
}
