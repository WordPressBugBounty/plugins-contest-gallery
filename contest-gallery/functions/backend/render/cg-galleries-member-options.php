<?php
if(!defined('ABSPATH')){exit;}

function cg_galleries_member_options($options,$type){
	if(!in_array($type,array('g','nv'),true)){return;}
	$values=isset($options[$type]) ? $options[$type] : array();
	$enabled=cg_galleries_member_enabled($type==='nv' ? 'cg_gallery_no_voting' : 'cg_gallery',$values);
	$name='cg_galleries['.$type.'][MemberArea]';$id='cg_galleries_'.$type.'_MemberArea';
	echo '<div class="cg_member_options_group cg_margin_bottom_30"><div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_view_option_checkbox_container cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px"><div class="cg_view_option_title"><p>Member profile<br><span class="cg_view_option_title_note">Let visitors browse participants and open their public profiles from the galleries overview. Contact and friend requests require sign-in.<br>The Participants button remains available when the galleries overview header is hidden. Member profile settings of the individual galleries apply separately.</span></p></div><div class="cg_view_option_checkbox"><input type="hidden" name="'.esc_attr($name).'" value="0"><input type="checkbox" aria-label="Member profile" id="'.esc_attr($id).'" name="'.esc_attr($name).'" value="1"'.($enabled ? ' checked' : '').'></div></div></div></div>';
}
