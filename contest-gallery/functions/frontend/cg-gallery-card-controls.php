<?php
if(!defined('ABSPATH')){exit;}

// Existing galleries keep sharing, while public profile cards are opt-in.
if(!function_exists('cg_gallery_card_option_enabled')){
function cg_gallery_card_option_enabled($options,$key){
	if(isset($options['visual'][$key])){return !empty($options['visual'][$key]);}
	return $key==='ShowShareButtonGallery';
}
}

if(!function_exists('cg_gallery_card_profile_supported')){
function cg_gallery_card_profile_supported($shortcode){
	return in_array($shortcode,array('cg_gallery','cg_gallery_no_voting','cg_gallery_winner'),true);
}
}

// A saved entry preference takes precedence over the legacy name/image pair.
if(!function_exists('cg_uploader_profile_entry_enabled')){
function cg_uploader_profile_entry_enabled($options,$shortcode){
	if(!cg_gallery_card_profile_supported($shortcode)){return false;}
	if(isset($options['visual']['ShowProfileEntry'])){return !empty($options['visual']['ShowProfileEntry']);}
	return !empty($options['pro']['ShowNickname']) || !empty($options['pro']['ShowProfileImage']);
}
}

if(!function_exists('cg_uploader_profile_entry_posted')){
function cg_uploader_profile_entry_posted($options,$shortcode,$posted){
	if(isset($posted[$shortcode]['visual']['ShowProfileEntry']) && is_scalar($posted[$shortcode]['visual']['ShowProfileEntry'])){
		return !empty($posted[$shortcode]['visual']['ShowProfileEntry']);
	}
	return cg_uploader_profile_entry_enabled($options,$shortcode);
}
}

// Preserve missing controls on partial saves. Keep legacy JSON/DB readers compatible.
if(!function_exists('cg_uploader_profile_store_entry_options')){
function cg_uploader_profile_store_entry_options(&$options,$gid,$posted = array(),$source = null){
	$source = is_array($source) ? $source : $options;
	foreach(array(''=>'cg_gallery','-nv'=>'cg_gallery_no_voting','-w'=>'cg_gallery_winner') as $suffix=>$shortcode){
		$previous = cg_member_options_from_source($source,$gid,$shortcode);
		$value = cg_uploader_profile_entry_posted($previous,$shortcode,$posted) ? 1 : 0;
		$keys = $suffix==='' ? array('flat',$gid) : array($gid.$suffix);
		foreach($keys as $key){
			if($key!=='flat' && empty($options[$key])){continue;}
			if($key==='flat'){$target =& $options;}else{$target =& $options[$key];}
			$target['visual']['ShowProfileEntry'] = $value;
			$target['pro']['ShowNickname'] = $value;
			$target['pro']['ShowProfileImage'] = $value;
			unset($target);
		}
	}
	// Removed controls on other shortcodes must not overwrite dormant legacy preferences.
	foreach(array('-u','-ec') as $suffix){
		$key = $gid.$suffix;
		if(empty($options[$key])){continue;}
		$previous = !empty($source[$key]) ? $source[$key] : cg_member_options_from_source($source,$gid,'cg_gallery');
		foreach(array('ShowNickname','ShowProfileImage') as $field){
			if(isset($previous['pro'][$field])){$options[$key]['pro'][$field] = $previous['pro'][$field];}
		}
		if(isset($previous['visual']['ShowProfileEntry'])){$options[$key]['visual']['ShowProfileEntry'] = $previous['visual']['ShowProfileEntry'];}
		else{unset($options[$key]['visual']['ShowProfileEntry']);}
	}
}
}

if(!function_exists('cg_uploader_profile_render_entry_option')){
function cg_uploader_profile_render_entry_option($gid,$shortcode,$allOptions){
	if(!cg_gallery_card_profile_supported($shortcode)){return;}
	$options = cg_member_options_from_source($allOptions,$gid,$shortcode);
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px"><div class="cg_view_option_title"><p>Show uploader profile in entry view<br><span class="cg_view_option_title_note">Shows the uploader name and available profile image in entry view and full window view. Also shows the profile header on entry landing pages.</span></p></div><div class="cg_view_option_checkbox">';
	echo '<input type="checkbox" name="multiple-pics['.esc_attr($shortcode).'][visual][ShowProfileEntry]" class="cg_shortcode_checkbox ShowProfileEntry" checked="'.(cg_uploader_profile_entry_enabled($options,$shortcode) ? '1' : '0').'" aria-label="Show uploader profile in entry view"></div></div></div>';
}
}

// The profile action uses the signed entry context, independently of Member profile.
if(!function_exists('cg_uploader_profile_entry_links')){
function cg_uploader_profile_entry_links($args,$owner,$profile){
	$gid = absint($args['gallery_id']);
	$shortcode = $args['shortcode'];
	$entryId = absint($args['entry_id']);
	if(!$owner || !$entryId || !cg_uploader_profile_entry_enabled($args['options'],$shortcode) || !cg_member_features_available($gid)){return '';}
	$name = isset($profile['name']) ? trim($profile['name']) : '';
	$label = cg_member_t('View public profile').($name!=='' ? ' · '.$name : '');
	$avatar = '<img class="cg-member-avatar skip-lazy" data-skip-lazy="1" loading="eager"'.(!empty($profile['image']) ? ' src="'.esc_url($profile['image']).'"' : ' hidden').' alt="">';
	$html = '<nav class="cg-page-hero-links"><button type="button" class="cg-page-profile-link cg-entry-public-profile" data-cg-gid="'.esc_attr($args['instance']).'" data-cg-real-id="'.$entryId.'" aria-haspopup="dialog" title="'.esc_attr($label).'">'.$avatar.'<span class="cg-member-name">'.esc_html($label).'</span></button>';
	if(!empty($args['back_url']) && cg_member_enabled($gid,$shortcode)){
		$url = remove_query_arg(array('cgl_view','cgl_profile','cgl_profile_page'),$args['back_url']);
		$url = preg_replace('/#.*$/','',add_query_arg('cgl_view','participants',$url));
		$html .= '<a class="cg-page-participants-link" data-cg-participant-view="participants" href="'.esc_url($url).'">'.esc_html(cg_member_t('Participants')).' <span class="cg-member-count">'.absint(cg_participants_count($gid,$shortcode)).'</span></a>';
	}
	return $html.'</nav>';
}
}

if(!function_exists('cg_gallery_card_render_options')){
function cg_gallery_card_render_options($gid,$shortcode,$allOptions){
	$suffixes = array('cg_gallery'=>'','cg_gallery_user'=>'-u','cg_gallery_no_voting'=>'-nv','cg_gallery_winner'=>'-w','cg_gallery_ecommerce'=>'-ec');
	if(!isset($suffixes[$shortcode])){return;}
	$key = $gid.$suffixes[$shortcode];
	$options = !empty($allOptions[$key]) ? $allOptions[$key] : (!empty($allOptions[$gid]) ? $allOptions[$gid] : $allOptions);
	$fields = array(
		'ShowProfileGallery'=>array('Show uploader profile on entries','Shows the name and profile image at the top left. Click to view the profile.'),
		'ShowShareButtonGallery'=>array('Show share button on images','Opens sharing and file actions from the top right of each image.')
	);
	if(!cg_gallery_card_profile_supported($shortcode)){unset($fields['ShowProfileGallery']);}
	$widthClass = count($fields)===1 ? 'cg_view_option_100_percent' : 'cg_view_option_50_percent';
	echo '<div class="cg_view_options_row cg_gallery_card_options">';
	foreach($fields as $field=>$text){
		$unavailable = $field==='ShowProfileGallery' && !cg_member_features_available($gid);
		if($unavailable){$text[1] = cg_member_unavailable_note();}
		$classes = 'cg_view_option '.$widthClass.' cg_border_top_none'.($unavailable ? ' cg_disabled' : '');
		if($field==='ShowProfileGallery'){$classes .= ' cg_border_right_none';}
		echo '<div class="'.$classes.'"><div class="cg_view_option_title"><p>'.esc_html($text[0]).'<br><span class="cg_view_option_title_note">'.esc_html($text[1]).'</span></p></div><div class="cg_view_option_checkbox">';
		echo '<input type="checkbox" name="multiple-pics['.esc_attr($shortcode).'][visual]['.$field.']" class="cg_shortcode_checkbox '.$field.'" checked="'.(!$unavailable && cg_gallery_card_option_enabled($options,$field) ? '1' : '0').'"'.($unavailable ? ' disabled' : '').'></div></div>';
	}
	echo '</div>';
}
}

if(!function_exists('cg_gallery_card_render_profile')){
function cg_gallery_card_render_profile($gid,$entryId,$fullData,$wpUserIdsData){
	if(!cg_member_features_available(absint($gid))){return;}
	$userId = !empty($fullData['WpUserId']) ? absint($fullData['WpUserId']) : (!empty($wpUserIdsData['mainDataWpUserIds'][$entryId]) ? absint($wpUserIdsData['mainDataWpUserIds'][$entryId]) : 0);
	$name = $userId && isset($wpUserIdsData['nicknamesArray'][$userId]) ? $wpUserIdsData['nicknamesArray'][$userId] : '';
	if(!$userId || $name===''){return;}
	$image = cg1l_frontend_get_preferred_wp_user_avatar_url($userId,$wpUserIdsData);
	echo '<div class="cg-member-account cg-gallery-card-account"><button type="button" class="cg-member-public-profile" data-cg-gid="'.esc_attr($gid).'" data-cg-real-id="'.absint($entryId).'" data-cg-wp-user-id="'.$userId.'" aria-haspopup="dialog" aria-label="'.esc_attr($name).'" title="'.esc_attr($name).'">';
	echo '<img class="cg-member-avatar" alt=""'.($image ? ' src="'.esc_url($image).'"' : ' hidden').'><span class="cg-member-avatar-placeholder" aria-hidden="true"'.($image ? ' hidden' : '').'></span><span class="cg-member-name">'.esc_html($name).'</span></button></div>';
}
}
