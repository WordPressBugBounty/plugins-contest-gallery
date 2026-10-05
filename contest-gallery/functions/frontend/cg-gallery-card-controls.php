<?php
if(!defined('ABSPATH')){exit;}

// Existing galleries keep sharing, while public profile cards are opt-in.
function cg_gallery_card_option_enabled($options,$key){
	if(isset($options['visual'][$key])){return !empty($options['visual'][$key]);}
	return $key==='ShowShareButtonGallery';
}

function cg_gallery_card_render_options($gid,$shortcode,$allOptions){
	$suffixes = array('cg_gallery'=>'','cg_gallery_user'=>'-u','cg_gallery_no_voting'=>'-nv','cg_gallery_winner'=>'-w','cg_gallery_ecommerce'=>'-ec');
	if(!isset($suffixes[$shortcode])){return;}
	$key = $gid.$suffixes[$shortcode];
	$options = !empty($allOptions[$key]) ? $allOptions[$key] : (!empty($allOptions[$gid]) ? $allOptions[$gid] : $allOptions);
	$fields = array(
		'ShowProfileGallery'=>array('Show uploader profile on images','Shows the name and profile image at the top left. Click to view the profile.'),
		'ShowShareButtonGallery'=>array('Show share button on images','Opens sharing and file actions from the top right of each image.')
	);
	echo '<div class="cg_view_options_row cg_gallery_card_options">';
	foreach($fields as $field=>$text){
		$unavailable = $field==='ShowProfileGallery' && !cg_member_features_available($gid);
		if($unavailable){$text[1] = cg_member_unavailable_note();}
		$classes = 'cg_view_option cg_view_option_50_percent cg_border_top_none'.($unavailable ? ' cg_disabled' : '');
		if($field==='ShowProfileGallery'){$classes .= ' cg_border_right_none';}
		echo '<div class="'.$classes.'"><div class="cg_view_option_title"><p>'.esc_html($text[0]).'<br><span class="cg_view_option_title_note">'.esc_html($text[1]).'</span></p></div><div class="cg_view_option_checkbox">';
		echo '<input type="checkbox" name="multiple-pics['.esc_attr($shortcode).'][visual]['.$field.']" class="cg_shortcode_checkbox '.$field.'" checked="'.(!$unavailable && cg_gallery_card_option_enabled($options,$field) ? '1' : '0').'"'.($unavailable ? ' disabled' : '').'></div></div>';
	}
	echo '</div>';
}

function cg_gallery_card_render_profile($gid,$entryId,$fullData,$wpUserIdsData){
	if(!cg_member_features_available(absint($gid))){return;}
	$userId = !empty($fullData['WpUserId']) ? absint($fullData['WpUserId']) : (!empty($wpUserIdsData['mainDataWpUserIds'][$entryId]) ? absint($wpUserIdsData['mainDataWpUserIds'][$entryId]) : 0);
	$name = $userId && isset($wpUserIdsData['nicknamesArray'][$userId]) ? $wpUserIdsData['nicknamesArray'][$userId] : '';
	if(!$userId || $name===''){return;}
	$image = cg1l_frontend_get_preferred_wp_user_avatar_url($userId,$wpUserIdsData);
	echo '<div class="cg-member-account cg-gallery-card-account"><button type="button" class="cg-member-public-profile" data-cg-gid="'.esc_attr($gid).'" data-cg-real-id="'.absint($entryId).'" data-cg-wp-user-id="'.$userId.'" aria-haspopup="dialog" aria-label="'.esc_attr($name).'" title="'.esc_attr($name).'">';
	echo '<img class="cg-member-avatar" alt=""'.($image ? ' src="'.esc_url($image).'"' : ' hidden').'><span class="cg-member-avatar-placeholder" aria-hidden="true"'.($image ? ' hidden' : '').'></span><span class="cg-member-name">'.esc_html($name).'</span></button></div>';
}
