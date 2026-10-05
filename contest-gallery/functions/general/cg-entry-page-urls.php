<?php
if(!defined('ABSPATH')){exit;}

function cg_entry_page_fields(){
	return array('cg_gallery'=>'WpPage','cg_gallery_user'=>'WpPageUser','cg_gallery_no_voting'=>'WpPageNoVoting','cg_gallery_winner'=>'WpPageWinner','cg_gallery_ecommerce'=>'WpPageEcommerce');
}

function cg_entry_page_clear_cache($row){
	foreach(cg_entry_page_fields() as $field){if(!empty($row->$field)){clean_post_cache($row->$field);}}
}

// Shared by form saves and media replacement. Only update pages, never publish
// an owner's private entry through the public info/JSON regeneration path.
function cg_entry_page_update_urls($rows,$options,$entries,$urlFieldId,$allowMissingField = false,$isBulk = false){
	if(!$options || (int)$options->Version<22){return array();}
	global $cg_post_names_for_a_title,$cg_post_names_for_a_title_numbers,$cg_processed_post_titles;
	$cg_post_names_for_a_title = $cg_post_names_for_a_title_numbers = $cg_processed_post_titles = array();
	$values = array(); $updated = array();
	foreach($entries as $entry){if($urlFieldId && (int)$entry->f_input_id===(int)$urlFieldId){$values[$entry->pid]=$entry->Short_Text;}}
	foreach($rows as $row){
		if($urlFieldId && !array_key_exists($row->id,$values) && !$allowMissingField){continue;}
		$title = !empty($values[$row->id]) ? $values[$row->id] : ($row->ImgType==='con' ? 'entry' : $row->NamePic);
		$title = cg_check_last_char_for_url_name_after_pre_processing(cg_check_first_char_for_url_name_after_pre_processing(cg_pre_process_name_for_url_name($title)));
		$slug = $title;
		foreach(cg_entry_page_fields() as $field){
			if(empty($row->$field) || !get_post($row->$field)){continue;}
			$parentField = str_replace('WpPage','WpPageParent',$field);
			$parent = isset($options->$parentField) ? $options->$parentField : 0;
			$slug = cg_update_custom_post_type_name($row->$field,(int)$options->Version,$title,$slug,$parent,$field==='WpPage',$isBulk);
			if(is_wp_error($slug)){return $slug;}
		}
		$updated[] = $row;
	}
	return $updated;
}

function cg_entry_page_update_replacement_urls($row){
	global $wpdb;
	$options = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'contest_gal1ery_options WHERE id=%d',$row->GalleryID));
	$field = $wpdb->get_var($wpdb->prepare('SELECT id FROM '.$wpdb->prefix.'contest_gal1ery_f_input WHERE GalleryID=%d AND IsForWpPageTitle=1 ORDER BY id DESC LIMIT 1',$row->GalleryID));
	$entries = $field ? $wpdb->get_results($wpdb->prepare('SELECT pid,f_input_id,Short_Text FROM '.$wpdb->prefix.'contest_gal1ery_entries WHERE GalleryID=%d AND pid=%d AND f_input_id=%d',$row->GalleryID,$row->id,$field)) : array();
	return cg_entry_page_update_urls(array($row),$options,$entries,$field,true);
}
