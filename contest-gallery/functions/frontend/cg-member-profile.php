<?php
if(!defined('ABSPATH')){exit;}

function cg_member_profile_strings(){
	return array('My profile','Additional information','Display name','This name is displayed across this website.','Choose profile image','JPG, PNG or GIF. Maximum {size} MB.','Save profile','Save information','Profile saved.','Information saved.','Please enter a display name.','Please use a display name instead of an email address.','Please complete the required fields.','Please choose a valid option.','Your profile has changed. Close and reopen it before saving.','Discard unsaved profile changes?','The profile image could not be saved.','You cannot edit these profile fields.');
}

function cg_member_profile_fields(){
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}contest_gal1ery_create_user_form WHERE GeneralID=1 AND Field_Type IN ('user-text-field','user-url-field','user-comment-field','user-select-field','user-radio-field','user-check-field') ORDER BY Field_Order ASC, id ASC");
}

function cg_member_profile_image_id($userId){
	global $wpdb;
	// The existing registry stores account profile images in the base-prefix table.
	return absint($wpdb->get_var($wpdb->prepare("SELECT WpUpload FROM {$wpdb->base_prefix}contest_gal1ery WHERE WpUserId=%d AND IsProfileImage=1 ORDER BY id DESC LIMIT 1",$userId)));
}

function cg_member_profile_data($userId){
	$user = get_userdata($userId);
	$id = cg_member_profile_image_id($userId);
	$src = $id ? wp_get_attachment_image_src($id,'large') : false;
	$custom = $src ? $src[0] : '';
	$wp = cg1l_frontend_get_real_wp_avatar_images($userId,96,400,true);
	$googleImages = cg_google_sign_in_get_profile_images(array($userId));
	$google = isset($googleImages[$userId]) ? $googleImages[$userId] : '';
	$override = $custom && get_user_meta($userId,'cg_member_profile_image_override',true);
	return array('userId'=>absint($userId),'name'=>$user->display_name,'customImage'=>$custom,'image'=>$override ? $custom : ($wp['small'] ? $wp['small'] : ($custom ? $custom : $google)),'largeImage'=>$override ? $custom : ($wp['large'] ? $wp['large'] : ($custom ? $custom : $google)),'fallbackImage'=>$wp['small'] ? $wp['small'] : $google,'attachment'=>$id,'override'=>(bool)$override);
}

function cg_member_profile_revision($userId,$section){
	if($section==='basic'){
		$user = get_userdata($userId);
		$state = array($user->display_name,cg_member_profile_image_id($userId),get_user_meta($userId,'cg_member_profile_image_override',true));
	}else{
		$state = array();
		foreach(cg_member_profile_fields() as $field){$state[] = array($field,get_user_meta($userId,cg_profile_meta_key($field->id),true));}
	}
	return wp_hash(wp_json_encode(array(cg_profile_meta_prefix(),$userId,$section,$state)),'nonce');
}

function cg_member_profile_render($userId){
	$data = cg_member_profile_data($userId);
	$fields = cgHasUserGroupAllowedToEdit(get_userdata($userId)) ? cg_member_profile_fields() : array();
	ob_start();
	echo '<div class="cg-member-profile-dialog" role="dialog" aria-modal="true" aria-label="'.esc_attr(cg_member_t('Edit profile')).'" tabindex="-1">';
	if(cg_community_enabled()){echo cg_community_render_nav('profile');}
	echo '<form class="cg-member-profile-form" novalidate data-required-message="'.esc_attr(cg_member_t('Required')).'"><section class="cg-member-profile-section" data-section="basic"><h2>'.esc_html(cg_member_t('My profile')).'</h2>';
	echo '<input type="hidden" name="revision" value="'.esc_attr(cg_member_profile_revision($userId,'basic')).'">';
	echo '<div class="cg-member-profile-photo cg-member-profile-field" data-profile-field="profile_image"><div class="cg-member-profile-preview"><img alt=""'.($data['image'] ? ' src="'.esc_url($data['image']).'"' : ' hidden').'><span class="cg-member-profile-placeholder" aria-hidden="true"'.($data['image'] ? ' hidden' : '').'>'.esc_html(mb_substr($data['name'],0,1)).'</span></div><div>';
	echo '<label class="cg-member-profile-file">'.esc_html(cg_member_t('Choose profile image')).'<input type="file" name="profile_image" '.cg_profile_image_upload_attributes().'></label>';
	echo '<p class="cg-member-profile-hint">'.cg_profile_image_upload_hint().'</p>';
	cg_member_profile_render_field_error('profile_image');
	echo '<button type="button" class="cg-member-profile-remove"'.($data['customImage'] ? '' : ' hidden').'>'.esc_html(cg_member_t('Remove profile image')).'</button><input type="hidden" name="remove_image" value="0"></div></div>';
	echo '<div class="cg-member-profile-field" data-profile-field="display_name" data-required="1"><label class="cg-member-profile-label">'.esc_html(cg_member_t('Display name')).'<input type="text" name="display_name" maxlength="250" required value="'.esc_attr($data['name']).'" autocomplete="nickname"></label>';
	cg_member_profile_render_field_error('display_name');
	echo '<p class="cg-member-profile-hint">'.esc_html(cg_member_t('This name is displayed across this website.')).'</p></div>';
	echo '</section>';
	if($fields){
		echo '<section class="cg-member-profile-section" data-section="fields"><h2>'.esc_html(cg_member_t('Additional information')).'</h2><input type="hidden" name="fields_revision" value="'.esc_attr(cg_member_profile_revision($userId,'fields')).'">';
		foreach($fields as $field){
			$value = cg_profile_field_value($field,get_user_meta($userId,cg_profile_meta_key($field->id),true));
			$name = 'fields['.absint($field->id).']';
			$title = html_entity_decode(stripslashes($field->Field_Name),ENT_QUOTES,'UTF-8');
			$required = $field->Required ? ' <span class="cg-member-profile-required">('.esc_html(cg_member_t('Required')).')</span>' : '';
			$type = $field->Field_Type;
			echo '<div class="cg-member-profile-field" data-profile-field="'.absint($field->id).'" data-required="'.($field->Required ? '1' : '0').'">';
			if($type==='user-check-field' || $type==='user-radio-field'){
				echo '<fieldset class="cg-member-profile-options"><legend>'.esc_html($title).$required.'</legend>';
				$selected = $type==='user-check-field' ? $value : array($value);
				foreach(cg_member_profile_choices($field) as $choice){
					echo '<label><input type="'.($type==='user-check-field' ? 'checkbox' : 'radio').'" name="'.esc_attr($name.($type==='user-check-field' ? '[]' : '')).'" value="'.esc_attr($choice).'"'.(in_array($choice,$selected,true) ? ' checked' : '').'> <span>'.esc_html($choice).'</span></label>';
				}
				echo '</fieldset>';
			}else{
				echo '<label class="cg-member-profile-label">'.esc_html($title).$required;
				if($type==='user-comment-field'){echo '<textarea'.cg_profile_field_length_attributes($field).' name="'.esc_attr($name).'" rows="4">'.esc_textarea($value).'</textarea>';}
				elseif($type==='user-select-field'){
					echo '<select name="'.esc_attr($name).'"><option value=""></option>';
					foreach(cg_member_profile_choices($field) as $choice){echo '<option value="'.esc_attr($choice).'"'.selected($value,$choice,false).'>'.esc_html($choice).'</option>';}
					echo '</select>';
				}else{echo '<input type="text"'.cg_profile_field_length_attributes($field).($type==='user-url-field' ? ' inputmode="url" autocomplete="url" data-url-message="'.esc_attr(cg_member_t('Please enter a valid URL')).'"' : '').' name="'.esc_attr($name).'" value="'.esc_attr($value).'">';}
				echo '</label>';
			}
			cg_member_profile_render_field_error(absint($field->id));
			echo cg_public_profile_field_hint($field).'</div>';
		}
		echo '</section>';
	}
	cg_member_profile_render_save();
	echo '</form></div>';
	return ob_get_clean();
}

function cg_member_profile_render_field_error($key){
	echo '<p class="cg_input_error" id="cg-member-profile-error-'.esc_attr($key).'"'.($key==='profile_image' ? ' role="status" aria-live="polite"' : '').' hidden></p>';
}

function cg_member_profile_render_save(){
	// Keep both labels in the layout so saving never changes the button width.
	echo '<div class="cg-member-profile-footer"><button type="button" class="cg-member-profile-close">'.esc_html(cg_member_t('Close')).'</button><button type="submit" class="cg-member-profile-save"><span class="cg-member-profile-save-label">'.esc_html(cg_member_t('Save')).'</span><span class="cg-member-profile-saved-label" aria-hidden="true">'.esc_html(cg_member_t('Saved')).'</span><span class="cg-member-profile-spinner" aria-hidden="true"></span></button>';
	echo '<p role="status" aria-live="polite" aria-atomic="true" class="cg-member-profile-status"></p></div>';
}

function cg_member_profile_validate_name($userId,$name){
	global $wpdb;
	if($name==='' || mb_strlen($name)>250){return new WP_Error('name','Please enter a display name.',array('fieldErrors'=>array('display_name'=>$name==='' ? 'Required' : 'Please enter a display name.')));}
	$user = get_userdata($userId);
	if(!$user){return new WP_Error('save','Please try again.');}
	// Existing duplicate or email names must not block unrelated profile edits.
	if($name===trim(sanitize_text_field($user->display_name))){return true;}
	if(is_email($name)){
		$message = 'Please use a display name instead of an email address.';
		return new WP_Error('name_email',$message,array('fieldErrors'=>array('display_name'=>$message)));
	}
	$existing = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE ID<>%d AND LOWER(TRIM(display_name))=LOWER(%s) LIMIT 1",$userId,$name));
	if($wpdb->last_error){return new WP_Error('save','Please try again.');}
	if($existing){return new WP_Error('name_taken','This nickname is already taken',array('fieldErrors'=>array('display_name'=>'This nickname is already taken')));}
	return true;
}

function cg_member_profile_save_basic($userId,$values = array()){
	global $wpdb;
	$name = isset($_POST['display_name']) && is_string($_POST['display_name']) ? trim(sanitize_text_field(wp_unslash($_POST['display_name']))) : '';
	$validName = cg_member_profile_validate_name($userId,$name);
	if(is_wp_error($validName)){return $validName;}
	$remove = isset($_POST['remove_image']) && $_POST['remove_image']==='1';
	$file = isset($_FILES['profile_image']) ? $_FILES['profile_image'] : null;
	$attachment = 0;
	$hasFile = $file!==null && (!is_array($file) || !isset($file['error']) || $file['error']!==UPLOAD_ERR_NO_FILE);
	if($hasFile){
		$error = cg_profile_image_upload_error(new WP_Error('image','The profile image could not be saved.'));
		if($remove){return $error;}
		$validImage = cg_profile_image_validate_upload($file);
		if(is_wp_error($validImage)){return $validImage;}
		require_once ABSPATH.'wp-admin/includes/image.php';
		require_once ABSPATH.'wp-admin/includes/file.php';
		require_once ABSPATH.'wp-admin/includes/media.php';
		$attachment = media_handle_upload('profile_image',0,array('post_author'=>$userId,'post_content'=>'Contest Gallery user profile image'),array('test_form'=>false,'mimes'=>$validImage['mimes']));
		if(is_wp_error($attachment)){return $error;}
	}
	$table = $wpdb->base_prefix.'contest_gal1ery';
	$oldRows = $wpdb->get_results($wpdb->prepare("SELECT id, WpUpload FROM $table WHERE WpUserId=%d AND IsProfileImage=1",$userId));
	$wpdb->query('START TRANSACTION');
	try{
		$result = wp_update_user(array('ID'=>$userId,'display_name'=>wp_slash($name)));
		if(is_wp_error($result)){throw new Exception('save');}
		if($attachment){
			if(!cg_registry_add_profile_image('', $userId, false, false, $attachment)){throw new Exception('image');}
			update_user_meta($userId,'cg_member_profile_image_override',1);
			if($wpdb->last_error){throw new Exception('image');}
		}elseif($remove){delete_user_meta($userId,'cg_member_profile_image_override');}
		if($attachment || $remove){
			foreach($oldRows as $row){if($wpdb->delete($table,array('id'=>$row->id,'WpUserId'=>$userId,'IsProfileImage'=>1))===false){throw new Exception('image');}}
		}
		foreach($values as $key=>$value){update_user_meta($userId,$key,wp_slash($value)); if($wpdb->last_error){throw new Exception('save');}}
		if($wpdb->query('COMMIT')===false){throw new Exception('save');}
	}catch(Exception $error){
		$wpdb->query('ROLLBACK');
		clean_user_cache($userId);
		if($attachment){wp_delete_attachment($attachment,true);}
		return new WP_Error('save','The profile image could not be saved.');
	}
	// Only remove attachments owned by this account and no longer used by a gallery entry.
	if($attachment || $remove){
		foreach($oldRows as $row){
			$post = get_post($row->WpUpload);
			if($post && (int)$post->post_author===$userId && $post->post_content==='Contest Gallery user profile image' && !$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE WpUpload=%d OR MultipleFiles LIKE %s LIMIT 1",$row->WpUpload,'%'.(int)$row->WpUpload.'%'))){wp_delete_attachment($row->WpUpload,true);}
		}
	}
	clean_user_cache($userId);
	return true;
}

add_action('wp_ajax_cg_member_profile','cg_member_profile_ajax');
function cg_member_profile_ajax(){
	if(!is_user_logged_in() || $_SERVER['REQUEST_METHOD']!=='POST'){wp_send_json_error(array('message'=>cg_member_t('Please reload the page and try again.')),403);}
	cg_member_ajax_context();
	$userId = get_current_user_id();
	if(isset($_POST['user_id']) || isset($_POST['uid'])){wp_send_json_error(array('message'=>cg_member_t('You cannot edit these profile fields.')),403);}
	$mode = isset($_POST['mode']) && is_string($_POST['mode']) ? $_POST['mode'] : '';
	if($mode==='load'){wp_send_json_success(array('html'=>cg_member_profile_render($userId),'profile'=>cg_member_profile_data($userId)));}
	if(!in_array($mode,array('basic','fields','save'),true)){wp_send_json_error(array('message'=>cg_member_t('Please try again.')),400);}
	$saveBasic = $mode==='basic' || ($mode==='save' && isset($_POST['save_basic']) && $_POST['save_basic']==='1');
	$saveFields = $mode==='fields' || ($mode==='save' && isset($_POST['save_fields']) && $_POST['save_fields']==='1');
	if(!$saveBasic && !$saveFields){wp_send_json_error(array('message'=>cg_member_t('Please try again.')),400);}
	global $wpdb;
	// Share one lock across profile saves so two accounts cannot claim the same name.
	$lock = 'cg_profile_'.md5($wpdb->users);
	if(!$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)',$lock))){wp_send_json_error(array('message'=>cg_member_t('Please try again.')),409);}
	$result = true;
	try{
		clean_user_cache($userId);
		$revision = isset($_POST['revision']) && is_string($_POST['revision']) ? $_POST['revision'] : '';
		$fieldsRevision = $mode==='fields' ? $revision : (isset($_POST['fields_revision']) && is_string($_POST['fields_revision']) ? $_POST['fields_revision'] : '');
		$values = array();
		if($saveFields && !cgHasUserGroupAllowedToEdit(get_userdata($userId))){$result = new WP_Error('access','You cannot edit these profile fields.');}
		elseif(($saveBasic && !hash_equals(cg_member_profile_revision($userId,'basic'),$revision)) || ($saveFields && !hash_equals(cg_member_profile_revision($userId,'fields'),$fieldsRevision))){$result = new WP_Error('stale','Your profile has changed. Close and reopen it before saving.');}
		elseif($saveFields){
			$values = cg_member_profile_validate_fields(isset($_POST['fields']) ? wp_unslash($_POST['fields']) : array(),cg_member_profile_fields());
			if(is_wp_error($values)){$result = $values;}
		}
		// Validate every changed section before uploading media or writing any profile data.
		if(!is_wp_error($result)){
			if($saveBasic){$result = cg_member_profile_save_basic($userId,$values);}
			else{
				$wpdb->query('START TRANSACTION');
				foreach($values as $key=>$value){update_user_meta($userId,$key,wp_slash($value)); if($wpdb->last_error){throw new Exception('save');}}
				if($wpdb->query('COMMIT')===false){throw new Exception('save');}
			}
		}
	}catch(Exception $error){$wpdb->query('ROLLBACK');clean_user_cache($userId);$result = new WP_Error('save','Please try again.');}
	finally{$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
	if(is_wp_error($result)){
		$response = array('message'=>cg_member_t($result->get_error_message()));
		$errorData = $result->get_error_data();
		if(isset($errorData['uploadValidation'])){$response['uploadValidation']=$errorData['uploadValidation'];}
		if(isset($errorData['fieldErrors'])){
			$response['fieldErrors'] = array_map('cg_member_t',$errorData['fieldErrors']);
		}
		wp_send_json_error($response,400);
	}
	$response = array('profile'=>cg_member_profile_data($userId),'revision'=>cg_member_profile_revision($userId,$mode==='fields' ? 'fields' : 'basic'),'message'=>cg_member_t($saveBasic ? 'Profile saved.' : 'Information saved.'));
	// Reuse the public renderers so the browser never copies private editor fields.
	$response['publicProfile'] = array(
		'fieldsHtml'=>cg_public_profile_render_fields($userId),
		'cardFieldsHtml'=>cg_public_profile_render_fields($userId,2),
		'photoHtml'=>cg_participants_profile_image($response['profile']),
		'cardPhotoHtml'=>cg_participants_profile_image($response['profile'],true)
	);
	if($mode==='save' && $saveFields){$response['fieldsRevision'] = cg_member_profile_revision($userId,'fields');}
	wp_send_json_success($response);
}
