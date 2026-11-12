<?php
if(!function_exists('cg_replace_ecommerce_file')){
    function cg_replace_ecommerce_file($realId, $GalleryID, $ecommerceEntry, $cgNewWpUploadWhichReplace, $fileDataForPostArray,$removedWpUploadIdsFromSale){
        return cg_ecommerce_with_sale_files_lock('cg_replace_ecommerce_file_locked',func_get_args());
    }
}
if(!function_exists('cg_replace_ecommerce_file_locked')){
    function cg_replace_ecommerce_file_locked($realId, $GalleryID, $ecommerceEntry, $cgNewWpUploadWhichReplace, $fileDataForPostArray,$removedWpUploadIdsFromSale){

        cg_ecommerce_validate_key_uploads($_FILES);
        global $wpdb;
        // The caller may have read the sale before waiting for the shared mutation lock.
        $ecommerceEntry = $wpdb->get_row($wpdb->prepare("SELECT s.* FROM {$wpdb->prefix}contest_gal1ery_ecommerce_entries s
            INNER JOIN {$wpdb->prefix}contest_gal1ery e ON e.EcommerceEntry=s.id AND e.id=s.pid AND e.GalleryID=s.GalleryID
            WHERE e.id=%d AND e.GalleryID=%d",$realId,$GalleryID));
        if(!$ecommerceEntry){cg_backend_ajax_error_json('The sale has changed. Reload the entry and try again.',409,'cg_sale_changed');}
        $ownedFiles = maybe_unserialize($ecommerceEntry->WpUploadFilesForSale);
        $ownedFiles = is_array($ownedFiles) ? array_map('absint',$ownedFiles) : array();
        if(!is_array($removedWpUploadIdsFromSale) || array_diff(array_map('absint',$removedWpUploadIdsFromSale),$ownedFiles)){
            cg_backend_ajax_error_json('Select files belonging to this sale.',400,'cg_sale_files_invalid');
        }
        $expectedFiles = array_merge($ownedFiles,array(absint($cgNewWpUploadWhichReplace)));
        foreach(array('WpUploadFilesForSale','base64WatermarkedAndAltFiles') as $field){
            if(!isset($_POST['cgSellContainer'][$field])){continue;}
            $values = $_POST['cgSellContainer'][$field];
            if(!is_array($values) || array_diff(array_map('absint',$field === 'WpUploadFilesForSale' ? $values : array_keys($values)),$expectedFiles)){
                cg_backend_ajax_error_json('Select files belonging to this sale.',400,'cg_sale_files_invalid');
            }
        }
        cg_ecommerce_assert_file_ownership($expectedFiles,$realId);

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
