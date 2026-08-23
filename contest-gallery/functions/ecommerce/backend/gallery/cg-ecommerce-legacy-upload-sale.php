<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_ecommerce_legacy_upload_sale_option_name')){
	function cg_ecommerce_legacy_upload_sale_option_name($galleryId){
		return 'cg_legacy_upload_sale_entries_'.absint($galleryId);
	}
}

if(!function_exists('cg_ecommerce_get_legacy_upload_sale_entries')){
	function cg_ecommerce_get_legacy_upload_sale_entries($galleryId){
		$entries = get_option(cg_ecommerce_legacy_upload_sale_option_name($galleryId),array());
		if(!is_array($entries)){return array();}

		$normalizedEntries = array();
		foreach($entries as $entryId){
			$entryId = absint($entryId);
			if($entryId){$normalizedEntries[$entryId] = $entryId;}
		}
		return array_values($normalizedEntries);
	}
}

if(!function_exists('cg_ecommerce_legacy_upload_sale_is_allowed')){
	function cg_ecommerce_legacy_upload_sale_is_allowed($galleryId,$entryId){
		return in_array(absint($entryId),cg_ecommerce_get_legacy_upload_sale_entries($galleryId),true);
	}
}

if(!function_exists('cg_ecommerce_set_legacy_upload_sale_allowed')){
	function cg_ecommerce_set_legacy_upload_sale_allowed($galleryId,$entryId,$isAllowed){
		$galleryId = absint($galleryId);
		$entryId = absint($entryId);
		if(!$galleryId || !$entryId){return;}

		$entries = cg_ecommerce_get_legacy_upload_sale_entries($galleryId);
		$entriesById = array();
		foreach($entries as $existingEntryId){$entriesById[$existingEntryId] = $existingEntryId;}
		if($isAllowed){
			$entriesById[$entryId] = $entryId;
		}else{
			unset($entriesById[$entryId]);
		}

		$optionName = cg_ecommerce_legacy_upload_sale_option_name($galleryId);
		if(empty($entriesById)){
			delete_option($optionName);
		}else{
			update_option($optionName,array_values($entriesById),false);
		}
	}
}

if(!function_exists('cg_ecommerce_get_gallery_creation_version')){
	function cg_ecommerce_get_gallery_creation_version($galleryId){
		global $wpdb;
		$tablenameOptions = $wpdb->prefix.'contest_gal1ery_options';
		$galleryVersionRow = $wpdb->get_row($wpdb->prepare("SELECT Version, VersionDecimal FROM $tablenameOptions WHERE id = %d",absint($galleryId)));
		if(empty($galleryVersionRow)){return 0;}
		if(!empty($galleryVersionRow->Version)){return (float)$galleryVersionRow->Version;}
		if(!empty($galleryVersionRow->VersionDecimal)){return (float)$galleryVersionRow->VersionDecimal;}
		return 0;
	}
}
