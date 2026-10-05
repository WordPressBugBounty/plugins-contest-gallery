<?php
if(!defined('ABSPATH')){exit;}

function cg_gallery_header_options($gid,$shortcode){
	if(!in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true)){return;}
	$options = cg_member_options($gid,$shortcode);
	if(empty($options['general']['Version']) || (int)$options['general']['Version']<24){return;}
	$texts = cg_page_gallery_header_texts($options,$gid);
	$labels = array('GalleryHeaderLabel'=>'Small heading','GalleryHeaderTitle'=>'Main title','GalleryHeaderSubtitle'=>'Subtitle');
	$enabled = cg_page_gallery_header_enabled($gid,$shortcode);
	echo '<div class="cg_gallery_header_options cg_page_header_options cg_margin_bottom_30">';
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px"><div class="cg_view_option_title"><p>Gallery header<br><span class="cg_view_option_title_note">Leave a text field empty to hide that line.</span></p></div></div></div>';
	cg_page_header_toggle_option($shortcode.'ShowGalleryHeader','multiple-pics['.$shortcode.'][visual][ShowGalleryHeader]',$enabled,'Show gallery header','Show texts, statistics and preview images above this gallery. Applies to the gallery page and embedded galleries using this shortcode.');
	echo '<div class="cg_page_header_texts"'.(!$enabled ? ' inert aria-disabled="true"' : '').'>';
	foreach($labels as $key=>$label){
		$id = $shortcode.$key;
		$bottom = ($key==='GalleryHeaderSubtitle' ? ' cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px' : '').(!$enabled ? ' cg_disabled' : '');
		echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_top_none'.$bottom.'"><div class="cg_view_option_title"><p><label for="'.esc_attr($id).'">'.esc_html($label).'</label></p></div><div class="cg_view_option_input"><input type="text" id="'.esc_attr($id).'" name="'.esc_attr('multiple-pics['.$shortcode.'][visual]['.$key.']').'" value="'.esc_attr($texts[$key]).'"></div></div></div>';
	}
	echo '</div></div>';
}

function cg_galleries_header_options($options,$type){
	if(!in_array($type,array('g','nv'),true)){return;}
	$options = isset($options[$type]) ? $options[$type] : array();
	$texts = cg_page_galleries_header_texts($options);
	$enabled = cg_page_header_option_value($options,'ShowGalleriesHeader');
	$labels = array('GalleriesHeaderLabel'=>'Small heading','GalleriesHeaderTitle'=>'Main title','GalleriesHeaderSubtitle'=>'Subtitle');
	echo '<div class="cg_galleries_header_options cg_page_header_options cg_margin_top_15 cg_margin_bottom_30">';
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px"><div class="cg_view_option_title"><p>Galleries overview header<br><span class="cg_view_option_title_note">Leave a text field empty to hide that line.</span></p></div></div></div>';
	cg_page_header_toggle_option('cg_galleries_'.$type.'_ShowGalleriesHeader','cg_galleries['.$type.'][ShowGalleriesHeader]',$enabled,'Show galleries overview header','Show texts, statistics and preview images above the gallery cards. Applies to all overviews using this shortcode type.');
	echo '<div class="cg_page_header_texts"'.(!$enabled ? ' inert aria-disabled="true"' : '').'>';
	foreach($labels as $key=>$label){
		$id = 'cg_galleries_'.$type.'_'.$key;
		$bottom = ($key==='GalleriesHeaderSubtitle' ? ' cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px' : '').(!$enabled ? ' cg_disabled' : '');
		echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_top_none'.$bottom.'"><div class="cg_view_option_title"><p><label for="'.esc_attr($id).'">'.esc_html($label).'</label></p></div><div class="cg_view_option_input"><input type="text" id="'.esc_attr($id).'" name="'.esc_attr('cg_galleries['.$type.']['.$key.']').'" value="'.esc_attr($texts[$key]).'"></div></div></div>';
	}
	echo '</div></div>';
}

function cg_page_header_toggle_option($id,$name,$enabled,$label,$description){
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_view_option_checkbox_container cg_border_top_none cg_page_header_toggle_container"><div class="cg_view_option_title"><p>'.esc_html($label).'<br><span class="cg_view_option_title_note">'.esc_html($description).'</span></p></div><div class="cg_view_option_checkbox"><input type="hidden" name="'.esc_attr($name).'" value="0"><input type="checkbox" class="cg_page_header_toggle" aria-label="'.esc_attr($label).'" id="'.esc_attr($id).'" name="'.esc_attr($name).'" value="1"'.($enabled ? ' checked' : '').'></div></div></div>';
}
