<?php
if(!defined('ABSPATH')){exit;}

// Resolve an entry visible in this signed gallery context, then derive its owner.
function cg_public_profile_owner($context,$entryId,$accessHash,$surface){
	global $wpdb;
	$error = new WP_Error('profile','This gallery is not available.');
	$suffixes = array('cg_gallery'=>'','cg_gallery_user'=>'-u','cg_gallery_no_voting'=>'-nv','cg_gallery_winner'=>'-w','cg_gallery_ecommerce'=>'-ec');
	if(!$context || !empty($context['isCGalleries']) || empty($context['realGid']) || !isset($suffixes[$context['shortcodeName']]) || !in_array($surface,array('card','entry'),true)){return $error;}
	$gid = absint($context['realGid']);
	if(!cg_member_features_available($gid)){return $error;}
	$viewer = get_current_user_id();
	$shortcode = $context['shortcodeName'];
	$expected = cg1l_get_gallery_data_access_hash($gid,$shortcode,$viewer,$context['galleryDataUseAllowedRealIds']);
	if(!cg1l_hash_equals($expected,$accessHash)){return $error;}
	$uploads = wp_upload_dir();
	$base = $uploads['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/'.$gid;
	$all = is_file($base.'-options.json') ? json_decode(file_get_contents($base.'-options.json'),true) : array();
	$key = $gid.$suffixes[$shortcode];
	$options = !empty($all[$key]) ? $all[$key] : (!empty($all[$gid]) ? $all[$gid] : $all);
	if(!$options || (!$viewer && (!empty($options['pro']['RegUserGalleryOnly']) || $shortcode==='cg_gallery_user'))){return $error;}
	$options = cg_member_profile_options($options,$gid,$shortcode);
	$interval = cg_shortcode_interval_check($gid,$all,$shortcode);
	if(empty($interval['shortcodeIsActive'])){return $error;}
	if($surface==='card' ? !cg_gallery_card_option_enabled($options,'ShowProfileGallery') : (empty($options['pro']['ShowNickname']) && empty($options['pro']['ShowProfileImage']))){return $error;}
	$entry = $wpdb->get_row($wpdb->prepare("SELECT id,WpUserId,Active,Category,Winner,EcommerceEntry,IsProfileImage FROM {$wpdb->prefix}contest_gal1ery WHERE id=%d AND GalleryID=%d",$entryId,$gid),ARRAY_A);
	if(!$entry || !empty($entry['IsProfileImage']) || empty($entry['WpUserId'])){return $error;}
	$own = $shortcode==='cg_gallery_user' && $viewer && (int)$entry['WpUserId']===$viewer && cg_member_enabled($gid);
	if((int)$entry['Active']!==1 && !$own){return $error;}
	$categories = is_file($base.'-categories.json') ? json_decode(file_get_contents($base.'-categories.json'),true) : array();
	$form = is_file($base.'-form-upload.json') ? json_decode(file_get_contents($base.'-form-upload.json'),true) : array();
	// Reuse the gallery's current category and shortcode rules, including private owner entries.
	if($own){$options['visual']['MemberArea'] = 1;}
	$allowed = cg1l_frontend_get_allowed_real_ids(array($entryId=>$entry),$shortcode,$categories,$options,$viewer,is_array($form) ? $form : array());
	if(!in_array((int)$entryId,$allowed,true) || !get_userdata($entry['WpUserId'])){return $error;}
	return absint($entry['WpUserId']);
}

function cg_public_profile_items($userId){
	$items = array();
	foreach(cg_member_profile_fields() as $field){
		$settings = cg_public_profile_field_settings($field);
		if(!$settings['public']){continue;}
		$value = cg_profile_field_text(cg_profile_field_value($field,get_user_meta($userId,cg_profile_meta_key($field->id),true)));
		if(!is_scalar($value) || trim((string)$value)===''){continue;}
		$value = (string)$value;
		$items[] = array('title'=>html_entity_decode(stripslashes($field->Field_Name),ENT_QUOTES,'UTF-8'),'value'=>$value,'link'=>$settings['display']==='link' ? cg_public_profile_link($value) : '');
	}
	return $items;
}

function cg_public_profile_render_fields($userId,$limit = 0){
	$fields = cg_public_profile_items($userId);
	if($limit){$fields = array_slice($fields,0,$limit);}
	if(!$fields){return '';}
	$html = '<dl class="cg-public-profile-fields">';
	foreach($fields as $field){
		$linkText = $field['link'] ? preg_replace('~^https?://~i','',trim($field['value'])) : '';
		$value = $field['link'] ? '<a href="'.esc_url($field['link']).'" target="_blank" rel="noopener noreferrer nofollow ugc">'.esc_html($linkText).'</a>' : nl2br(esc_html($field['value']));
		$html .= '<div class="cg-public-profile-field"><dt>'.esc_html($field['title']).'</dt><dd>'.$value.'</dd></div>';
	}
	return $html.'</dl>';
}

function cg_public_profile_render($userId){
	$profile = cg_member_profile_data($userId);
	$image = $profile['largeImage'];
	ob_start();
	echo '<div class="cg-member-profile-dialog cg-member-public-profile-dialog" role="dialog" aria-modal="true" aria-labelledby="cgMemberPublicProfileName" tabindex="-1"><div class="cg-member-profile-photo"><div class="cg-member-profile-preview">';
	if($image){echo '<img src="'.esc_url($image).'" alt="">';}
	else{echo '<span class="cg-member-profile-placeholder" aria-hidden="true">'.esc_html(mb_substr($profile['name'],0,1)).'</span>';}
	echo '</div><h2 id="cgMemberPublicProfileName">'.esc_html($profile['name']).'</h2></div>';
	echo cg_public_profile_render_fields($userId);
	echo '</div>';
	return ob_get_clean();
}

function cg_public_profile_ajax(){
	nocache_headers();
	header('Cache-Control: no-store, private, max-age=0');
	cg1l_ajax_reject_bad_origin();
	cg_check_frontend_nonce();
	$token = isset($_POST['token']) && is_string($_POST['token']) ? wp_unslash($_POST['token']) : '';
	$hash = isset($_POST['galleryDataAccessHash']) && is_string($_POST['galleryDataAccessHash']) ? wp_unslash($_POST['galleryDataAccessHash']) : '';
	$surface = isset($_POST['surface']) && is_string($_POST['surface']) ? sanitize_key($_POST['surface']) : '';
	$entryId = isset($_POST['entryId']) && is_scalar($_POST['entryId']) ? absint($_POST['entryId']) : 0;
	$owner = cg_public_profile_owner(cg1l_verify_runtime_context_token($token),$entryId,$hash,$surface);
	if(is_wp_error($owner)){wp_send_json_error(array('message'=>cg_member_t($owner->get_error_message())));}
	wp_send_json_success(array('html'=>cg_public_profile_render($owner)));
}
add_action('wp_ajax_cg_public_profile','cg_public_profile_ajax');
add_action('wp_ajax_nopriv_cg_public_profile','cg_public_profile_ajax');
