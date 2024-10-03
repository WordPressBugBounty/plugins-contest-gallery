<?php

$realId = absint($_GET['real_id']);
$WpUpload = absint($_GET['cg_wp_upload']);
$EcommerceEntryID = absint($_GET['cg_ecommerce_entry_id']);
$cgOrderIdHash = sanitize_text_field($_GET['cg_order_id_hash']);
$cgOrderId = absint($_GET['cg_order_id']);
$cgOrderIdHashDecoded = wp_salt( 'auth').'---cg_ecommerce_sale---'.$cgOrderId;
$cgOrderIdHashToCompare = cg_hash_function('---cg_ecommerce_sale---'.$cgOrderId, $cgOrderIdHash);

if ($cgOrderIdHash == $cgOrderIdHashToCompare){

    global $wpdb;
    $tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
    $tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";
    $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

    $SaleOrder = $wpdb->get_var("SELECT * FROM $tablename_ecommerce_orders WHERE id = '$cgOrderId' LIMIT 1");
    $SaleItems = $wpdb->get_var("SELECT * FROM $tablename_ecommerce_orders_items WHERE OrderId = '$cgOrderId' LIMIT 1");

    $wp_upload_dir = wp_upload_dir();
    $downloadFiles = [];

    $entry = $wpdb->get_row("SELECT WpUploadFilesPostMeta FROM $tablename_ecommerce_entries WHERE id = '$EcommerceEntryID' LIMIT 1");
    $WpUploadFilesPostMeta = $entry->WpUploadFilesPostMeta;
    $GalleryID = $entry->GalleryID;

    foreach($WpUploadFilesPostMeta as $WpUploadId => $metaData){
        if($WpUpload==$WpUploadId){
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/ecommerce/gallery-id-'.$GalleryID.'/real-id-'.$realId;
            $filename = substr($metaData['_wp_attached_file'],strrpos($metaData['_wp_attached_file'],'/')+1,strlen($metaData['_wp_attached_file']));
            $fullpath = $ecommerceFileFolder.'/wp-upload-id-'.$WpUploadId.'/'.$filename;

            //Define header information
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
            header('Content-Disposition: attachment; filename="'.basename($fullpath).'"');
            header('Content-Length: ' . filesize($fullpath));
            header('Pragma: public');

//Clear system output buffer
            flush();

//Read the size of the file
            readfile($fullpath);

//Terminate from the script
            die();


        }
    }




}

?>