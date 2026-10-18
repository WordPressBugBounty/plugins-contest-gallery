<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_community_can_edit_mail_templates')){
function cg_community_can_edit_mail_templates(){
	$editable=false;
	return $editable;
}
}
if(!function_exists('cg_community_admin_render')){
function cg_community_admin_render(){
	$settings=cg_community_settings();
	$canEditTemplates=cg_community_can_edit_mail_templates();
	$proClass=$canEditTemplates ? '' : ' cg-pro-false';
	echo '<div class="cg_view_options_rows_container"><p class="cg_view_options_rows_container_title">Member notifications (general)</p><input type="hidden" name="cg_member_community_present" value="1">';
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_view_option_checkbox_container"><div class="cg_view_option_title"><p>Contact and friendships<br><span class="cg_view_option_title_note">Enable private messages, friend requests and friends’ entries in member profiles across this website. Turning this off preserves existing conversations and friendships.</span></p></div><div class="cg_view_option_checkbox"><input type="checkbox" name="cg_member_community[enabled]" value="1"'.checked(!empty($settings['enabled']),true,false).'></div></div></div></div>';
	foreach(array('message'=>'New private message','friend'=>'New friend request') as $type=>$title){
		$values=$settings[$type];$base='cg_member_community['.$type.']';
		echo '<div class="cg_view_options_rows_container"><p class="cg_view_options_rows_container_title">'.esc_html($title).' email</p>';
		echo cg_mail_delivery_card($title,array('from'=>array('[name="'.$base.'[from]"]',$values['from']),'reply'=>array('[name="'.$base.'[reply]"]',$values['reply'])),'The WordPress email address of the receiving member.');
		echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_view_option_checkbox_container cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px"><div class="cg_view_option_title"><p>Send email notifications<br><span class="cg_view_option_title_note">Disabled by default. '.($type==='message' ? 'One notification per conversation until the recipient reads their messages.' : 'One notification when a new request is sent.').' Enabling this does not send old notifications.</span></p></div><div class="cg_view_option_checkbox"><input type="checkbox" name="'.esc_attr($base.'[enabled]').'" value="1"'.checked(!empty($values['enabled']),true,false).'></div></div></div>';
		foreach(array('name'=>'Header / sender name','from'=>'From email','reply'=>'Reply-To email','subject'=>'Subject') as $key=>$label){
			$fieldProClass=$key==='subject' ? $proClass : '';
			$disabled=$key==='subject' && !$canEditTemplates ? ' disabled' : '';
			echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_top_none'.$fieldProClass.'"><div class="cg_view_option_title"><p>'.esc_html($label).'</p></div><div class="cg_view_option_input"><input type="text" name="'.esc_attr($base.'['.$key.']').'" value="'.esc_attr($values[$key]).'" maxlength="200"'.$disabled.'></div></div></div>';
		}
		$placeholders=array('$recipient$','$sender$','$site$','$url$');
		if($type==='message'){$placeholders[]='$message_preview$';}
		foreach($placeholders as $index=>$placeholder){
			$placeholders[$index]='<span style="font-weight:bold;" class="cg_tooltip cg_copy_param">'.esc_html($placeholder).'</span>';
		}
		$previewNote=$type==='message' ? ' Add '.$placeholders[4].' as normal text to include up to 200 characters in the email content. Without it, private message text is not included.' : '';
		// Both editions load TinyMCE. FREE keeps a disabled, read-only preview.
		$editorClass='cg-wp-editor-template';
		$editorAttrs=$canEditTemplates ? '' : ' disabled';
		echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_top_none'.$proClass.'"><div class="cg_view_option_title cg_copyable"><p>Email content<br><span class="cg_view_option_title_note">Placeholders: '.implode(', ',$placeholders).'. The URL opens the member area after sign-in.'.$previewNote.'</span></p></div><div class="cg_view_option_html"><textarea class="'.$editorClass.'" id="cgMemberMail'.ucfirst($type).'" name="'.esc_attr($base.'[content]').'"'.$editorAttrs.'>'.esc_textarea($values['content']).'</textarea></div></div></div></div>';
	}
}
}
if(!function_exists('cg_community_admin_save')){
function cg_community_admin_save(){
	if(empty($_POST['cg_member_community_present'])){return;}
	if(!is_user_logged_in() || !current_user_can('publish_posts') || !cg_user_has_backend_access()){return;}
	check_admin_referer('cg_admin');
	$input=isset($_POST['cg_member_community']) && is_array($_POST['cg_member_community']) ? wp_unslash($_POST['cg_member_community']) : array();
	$settings=cg_community_settings();$settings['enabled']=empty($input['enabled']) ? 0 : 1;
	$canEditTemplates=cg_community_can_edit_mail_templates();
	foreach(array('message','friend') as $type){
		$data=isset($input[$type]) && is_array($input[$type]) ? $input[$type] : array();
		$settings[$type]['enabled']=empty($data['enabled']) ? 0 : 1;
		foreach(array('name','from','reply','subject','content') as $key){
			if(!$canEditTemplates && in_array($key,array('subject','content'),true)){continue;}
			if(!isset($data[$key]) || !is_string($data[$key])){continue;}
			$value=$data[$key];
			if($key==='content'){$value=wp_kses_post($value);}
			elseif($key==='from' || $key==='reply'){$value=is_email($value) && !preg_match('/[\r\n]/',$value) ? sanitize_email($value) : '';}
			else{$value=mb_substr(sanitize_text_field($value),0,200);}
			if($key==='subject' || $key==='content'){$value=cg_community_normalize_mail_placeholders($value);}
			$settings[$type][$key]=$value;
		}
	}
	update_option('cg_member_community',$settings,false);
}
}
