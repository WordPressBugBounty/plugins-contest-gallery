<?php
if(!function_exists('cg_replace_ecommerce_file')){
    function cg_replace_ecommerce_file($realId, $GalleryID, $ecommerceEntry, $cgNewWpUploadWhichReplace, $fileDataForPostArray,$removedWpUploadIdsFromSale){

        global $wpdb;

// Set table names
        $tablename = $wpdb->prefix . "contest_gal1ery";

        // var_dump('$removedWpUploadIdsFromSale');
        // var_dump($removedWpUploadIdsFromSale);
        cg_move_file_from_ecommerce_sale_folder($realId, $GalleryID, $ecommerceEntry->id, $removedWpUploadIdsFromSale);
        $sqlObjectFile = $wpdb->get_row("SELECT * FROM $tablename WHERE id = '$realId'");
        $WpUploadFilesForSale = unserialize($ecommerceEntry->WpUploadFilesForSale);
        unset($WpUploadFilesForSale[array_search($_POST['cgWpUploadToReplace'], $WpUploadFilesForSale)]);
        $WpUploadFilesForSale[] = $cgNewWpUploadWhichReplace;
        if(!empty($fileDataForPostArray)){
            $sqlObjectFile->MultipleFiles = serialize($fileDataForPostArray);// has to be set here
        }
        // var_dump('$sqlObjectFile->MultipleFiles');
        // echo "<pre>";
        // 	print_r($sqlObjectFile->MultipleFiles);
        // echo "</pre>";
        // var_dump('$WpUploadFilesForSale');
        // var_dump($WpUploadFilesForSale);

        cg_move_file_ecommerce_sale_folder($realId, $GalleryID,$sqlObjectFile,$ecommerceEntry,$WpUploadFilesForSale,$removedWpUploadIdsFromSale);

    }
}
