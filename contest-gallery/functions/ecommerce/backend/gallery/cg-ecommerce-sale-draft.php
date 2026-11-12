<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_ecommerce_sale_draft_key')){
    function cg_ecommerce_sale_draft_key($galleryId,$entryId){
        return 'cg_ecommerce_sale_draft_'.absint($galleryId).'_'.absint($entryId);
    }
}
if(!function_exists('cg_ecommerce_sale_draft_fields')){
    function cg_ecommerce_sale_draft_fields(){
        return array('Price','RegularPrice','IsFree','SaleType','SaleTitle','SaleDescription','HasTax','TaxPercentage',
            'IsAlternativeShipping','AlternativeShipping','SaleAmountMin','SaleAmountMax','UploadGallery','MaxUploads',
            'AllUploadsUsedText','WpUploadFilesForSale','WatermarkSettings','DownloadKeysCsvName','ServiceKeysCsvName');
    }
}
if(!function_exists('cg_ecommerce_sale_draft_save')){
    function cg_ecommerce_sale_draft_save($entry,$sale){
        $draft = array_intersect_key((array)$sale,array_flip(cg_ecommerce_sale_draft_fields()));
        $key = cg_ecommerce_sale_draft_key($entry->GalleryID,$entry->id);
        // Only configuration is retained. Protected-file snapshots and generated previews are never reused.
        update_option($key,$draft,false);
        return get_option($key,false) === $draft;
    }
}
if(!function_exists('cg_ecommerce_sale_entry_upload_ids')){
    function cg_ecommerce_sale_entry_upload_ids($entry){
        $ids = array(absint($entry->WpUpload));
        $files = maybe_unserialize($entry->MultipleFiles);
        if(is_array($files)){
            foreach($files as $file){if(!empty($file['WpUpload'])){$ids[] = absint($file['WpUpload']);}}
        }
        return array_values(array_unique(array_filter($ids)));
    }
}
if(!function_exists('cg_ecommerce_sale_draft_get')){
    function cg_ecommerce_sale_draft_get($entry){
        $draft = get_option(cg_ecommerce_sale_draft_key($entry->GalleryID,$entry->id),array());
        if(!is_array($draft) || empty($draft['SaleType'])){return array();}
        $draft = array_intersect_key($draft,array_flip(cg_ecommerce_sale_draft_fields()));
        if(isset($draft['AllUploadsUsedText'])){
            $draft['AllUploadsUsedText'] = contest_gal1ery_convert_for_html_output_without_nl2br($draft['AllUploadsUsedText']);
        }
        $selected = isset($draft['WpUploadFilesForSale']) ? maybe_unserialize($draft['WpUploadFilesForSale']) : array();
        $selected = is_array($selected) ? array_map('absint',$selected) : array();
        $selected = array_values(array_intersect($selected,cg_ecommerce_sale_entry_upload_ids($entry)));
        foreach($selected as $key=>$uploadId){
            $context = cg_ecommerce_file_context($uploadId,$entry->id);
            if($context['locked'] || !get_post($uploadId) || !is_file(get_attached_file($uploadId)) ||
                (function_exists('cg_entry_watermark_meta_key') && get_post_meta($uploadId,cg_entry_watermark_meta_key(),true))){unset($selected[$key]);}
        }
        $draft['WpUploadFilesForSale'] = array_values($selected);
        $watermarks = isset($draft['WatermarkSettings']) ? maybe_unserialize($draft['WatermarkSettings']) : array();
        $draft['WatermarkSettings'] = is_array($watermarks) ? array_intersect_key($watermarks,array_flip($selected)) : array();
        foreach(array('DownloadKeysCsvName'=>'download-keys','ServiceKeysCsvName'=>'service-keys') as $field=>$folder){
            $name = isset($draft[$field]) ? $draft[$field] : '';
            if(!cg_ecommerce_sale_key_file($entry->GalleryID,$entry->id,$folder,$name)){$draft[$field] = '';}
        }
        return $draft;
    }
}
if(!function_exists('cg_ecommerce_sale_key_file')){
    function cg_ecommerce_sale_key_file($galleryId,$entryId,$folder,$name){
        if(!is_string($name) || $name === '' || basename($name) !== $name || !in_array($folder,array('download-keys','service-keys'),true)){return false;}
        $uploads = wp_upload_dir();
        $base = $uploads['basedir'].'/contest-gallery/gallery-id-'.absint($galleryId).'/ecommerce/real-id-'.absint($entryId).'/'.$folder;
        $path = $base.'/'.$name;
        $realBase = realpath($base);$realFile = realpath($path);
        return !is_link($base) && !is_link($path) && $realBase && $realFile && strpos($realFile,$realBase.DIRECTORY_SEPARATOR) === 0 && is_file($path) ? $path : false;
    }
}
if(!function_exists('cg_ecommerce_sale_draft_delete')){
    function cg_ecommerce_sale_draft_delete($galleryId,$entryId){
        delete_option(cg_ecommerce_sale_draft_key($galleryId,$entryId));
    }
}
if(!function_exists('cg_ecommerce_sale_drafts_delete_gallery')){
    function cg_ecommerce_sale_drafts_delete_gallery($galleryId){
        global $wpdb;
        $keys = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('cg_ecommerce_sale_draft_'.absint($galleryId).'_').'%'));
        foreach($keys as $key){delete_option($key);}
    }
}
