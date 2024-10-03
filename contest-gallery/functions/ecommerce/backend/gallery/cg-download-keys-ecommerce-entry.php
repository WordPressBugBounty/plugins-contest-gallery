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

/*var_dump($GalleryID);
var_dump($realId);*/
        if(!empty($_GET['cg_download_keys_for_ecommerce_sale'])){
            $DownloadKeysCsvName = $wpdb->get_var("SELECT DownloadKeysCsvName FROM $tablename_ecommerce_entries WHERE pid = '$realId' ");
        }
        if(!empty($_GET['cg_service_keys_for_ecommerce_sale'])){
            $ServiceKeysCsvName = $wpdb->get_var("SELECT ServiceKeysCsvName FROM $tablename_ecommerce_entries WHERE pid = '$realId' ");
        }

        if(!empty($DownloadKeysCsvName)){
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId;
            $baseName = $ecommerceFileFolder.'/download-keys/'.$DownloadKeysCsvName;
        }

        if(!empty($ServiceKeysCsvName)){
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId;
            $baseName = $ecommerceFileFolder.'/service-keys/'.$ServiceKeysCsvName;
        }

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
