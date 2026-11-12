<?php

if(!function_exists('cg_download_keys_ecommerce_entry')){
    function cg_download_keys_ecommerce_entry(){

	    if(!current_user_can('manage_options')){
		    echo "Only logged in user is able to download keys";die;
	    }

        $wp_upload_dir = wp_upload_dir();

        global $wpdb;
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

        $GalleryID = absint($_GET['option_id']);
        $realId = absint($_GET['cg_real_id']);
        $DownloadKeysCsvName = '';
        $ServiceKeysCsvName = '';

        $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_gal1ery WHERE id = %d AND GalleryID = %d",$realId,$GalleryID));
        if(!$entry){wp_die('Entry not found.');}
        $sale = !empty($entry->EcommerceEntry) ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_ecommerce_entries WHERE id = %d",$entry->EcommerceEntry),ARRAY_A) : cg_ecommerce_sale_draft_get($entry);
        $isService = !empty($_GET['cg_service_keys_for_ecommerce_sale']);
        $field = $isService ? 'ServiceKeysCsvName' : 'DownloadKeysCsvName';
        $folder = $isService ? 'service-keys' : 'download-keys';
        $baseName = cg_ecommerce_sale_key_file($GalleryID,$realId,$folder,isset($sale[$field]) ? $sale[$field] : '');
        if(!$baseName){wp_die('Key file not found.');}

        //Define header information
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: 0");
        header('Content-Disposition: attachment; filename="'.basename($baseName).'"');
        header('Content-Length: ' . filesize($baseName));
        header('Pragma: public');

        //Clear system output buffer
        flush();

        //Read the size of the file
        readfile($baseName);

        //Terminate from the script
        die();

    }
}
