<?php

if(!function_exists('cg_download_file_ecommerce_sale')){
    function cg_download_file_ecommerce_sale(){

	    if(!current_user_can('manage_options')){
		    echo "Only logged in user is able to download original files for selling";die;
	    }

        $wp_upload_dir = wp_upload_dir();

        global $wpdb;
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
        $tablename = $wpdb->prefix . "contest_gal1ery";

        $GalleryID = absint($_GET['option_id']);
        $realId = absint($_GET['cg_real_id']);
/*var_dump($GalleryID);
var_dump($realId);*/
        $WpUploadFilesPostMeta = unserialize($wpdb->get_var("SELECT WpUploadFilesPostMeta FROM $tablename_ecommerce_entries WHERE pid = '$realId'"));
        $WpUploadFilesPosts = unserialize($wpdb->get_var("SELECT WpUploadFilesPosts FROM $tablename_ecommerce_entries WHERE pid = '$realId'"));

        $zipMultipleFiles = false;
        if(count($WpUploadFilesPostMeta)>1){
	        $zipMultipleFiles = true;
        }

        // #2 use collected data to move files to contest gallery folder
        $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId;
        // then zip download
        if($zipMultipleFiles){
            $zipFile = "$ecommerceFileFolder/download.zip";
            $files = [];
            if(count($WpUploadFilesPostMeta)==1){

                $WpUpload = $WpUploadFilesPosts[array_key_first($WpUploadFilesPosts)];
                $ecommerceFileFolderWpUploadFolder = $ecommerceFileFolder.'/wp-upload-id-'.$WpUpload;
                $filename = substr($WpUploadFilesPostMeta[$WpUpload]['_wp_attached_file'],strrpos($WpUploadFilesPostMeta[$WpUpload]['_wp_attached_file'],'/')+1,strlen($WpUploadFilesPostMeta[$WpUpload]['_wp_attached_file']));
                $path = $ecommerceFileFolderWpUploadFolder.'/'.$filename;
                $files[] = $path;
                cg_action_create_zip($files,$zipFile);

            }else {// then bigger then 1
                foreach ($WpUploadFilesPostMeta as $WpUpload => $array){
                    $ecommerceFileFolderWpUploadFolder = $ecommerceFileFolder.'/wp-upload-id-'.$WpUpload;
                    $filename = substr($WpUploadFilesPostMeta[$WpUpload]['_wp_attached_file'],strrpos($WpUploadFilesPostMeta[$WpUpload]['_wp_attached_file'],'/')+1,strlen($WpUploadFilesPostMeta[$WpUpload]['_wp_attached_file']));
                    $path = $ecommerceFileFolderWpUploadFolder.'/'.$filename;
                    $files[] = $path;
                }
                cg_action_create_zip($files,$zipFile);
            }
            $strFile = file_get_contents($zipFile);
            ob_start();
            //Define header information
            if(!empty($_GET['cg_download_cookie'])){
                $cg_download_cookie = sanitize_text_field($_GET['cg_download_cookie']);
                header("Set-Cookie: $cg_download_cookie=true; EXPIRES".(time() + (20 * 365 * 24 * 60 * 60)).";path=/;");
            }
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            //header('Content-Type: application/zip'); //
     //       header("Content-type: application/force-download"); // some images broken in that case
            header('Content-Disposition: attachment; filename="contest-gallery-files-for-selling-entry-id-'.$realId.'.zip"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($zipFile));
            header('Pragma: public');

            echo $strFile;
            while (ob_get_level()) {
                ob_end_clean();
            }
            readfile($zipFile);
            unlink($zipFile);
            exit();

        }else{

            $WpUpload = array_key_first($WpUploadFilesPosts);

            $ecommerceFileFolderWpUploadFolder = $ecommerceFileFolder.'/wp-upload-id-'.$WpUpload;
            $filename = substr($WpUploadFilesPostMeta[$WpUpload]['_wp_attached_file'],strrpos($WpUploadFilesPostMeta[$WpUpload]['_wp_attached_file'],'/')+1,strlen($WpUploadFilesPostMeta[$WpUpload]['_wp_attached_file']));
            $path = $ecommerceFileFolderWpUploadFolder.'/'.$filename;

	        //Define header information
	        if(!empty($_GET['cg_download_cookie'])){
		        $cg_download_cookie = sanitize_text_field($_GET['cg_download_cookie']);
		        header("Set-Cookie: $cg_download_cookie=true; EXPIRES".(time() + (20 * 365 * 24 * 60 * 60)).";path=/;");
	        }

	        if(!file_exists($path)){
		        echo "Entry file not found";die;
	        }
	        //Define header information
	        header('Content-Description: File Transfer');
	        header('Content-Type: application/octet-stream');
	        header("Cache-Control: no-cache, must-revalidate");
	        header("Expires: 0");
	        header('Content-Disposition: attachment; filename="'.basename($path).'"');
	        header('Content-Length: ' . filesize($path));
	        header('Pragma: public');
//Clear system output buffer
	        flush();
//Read the size of the file
	        readfile($path);
	        exit();

        }


    }
}
