<?php
if (!function_exists('cg1l_get_entry_query_data')) {
    function cg1l_get_entry_query_data($entryId)
    {
        $entryId = absint($entryId);
        if (empty($entryId)) {
            return [];
        }

        global $wpdb;
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $queryData = $wpdb->get_results($wpdb->prepare(
            "SELECT id, Exif, MultipleFiles
         FROM $tablename
         WHERE id = %d AND Active = '1' AND ((Exif != '' AND Exif != '0' AND Exif IS NOT NULL)
            OR MultipleFiles != '')",
            $entryId
        ));

        $queryDataArray = [];
        if (!empty($queryData)) {
            foreach ($queryData as $rowObject) {
                $queryDataArray[$rowObject->id] = [];
                if (!empty($rowObject->Exif)) {
                    if (strlen($rowObject->Exif) > 10) {
                        $queryDataArray[$rowObject->id]['Exif'] = unserialize($rowObject->Exif);
                    } else {
                        $queryDataArray[$rowObject->id]['Exif'] = '';
                    }
                }
                if (!empty($rowObject->MultipleFiles) && $rowObject->MultipleFiles != '""') {
                    $queryDataArray[$rowObject->id]['MultipleFiles'] = unserialize($rowObject->MultipleFiles);
                }
            }
        }
        return $queryDataArray;
    }
}

if (!function_exists('cg1l_build_images_query_data_gzip')) {
    function cg1l_build_images_query_data_gzip($gid, $getDataOnly = false, $getRecentIds = []) {

        $gid = absint($gid);
        if(!empty($getRecentIds) && is_array($getRecentIds)){
            $getRecentIds = array_values(array_unique(array_filter(array_map('absint', $getRecentIds))));
        }else{
            $getRecentIds = [];
        }

        $wp_upload_dir = wp_upload_dir();

        $base_dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/segments';
        $final    = $base_dir . "/images-query-data.json.gz.php";
        $lastUpdated    = $base_dir . "/image-query-data-last-update.txt";

        // Fixed header length for streaming (must match output handler)
        $headerLen = 128;

        $newBuilt = false;
        $lastUpdatedTime = file_exists($lastUpdated) ? intval(trim(file_get_contents($lastUpdated))) : 0;
        $built = file_exists($final) ? filemtime($final) : 0;

        if(!file_exists($final) || !file_exists($lastUpdated) || ($lastUpdatedTime > $built && (time() - $built) > 3)){
            $newBuilt = true;
        }

        if($getDataOnly && file_exists($final) && !$newBuilt && empty($getRecentIds)) {

            $fh = fopen($final, 'rb');
            if($fh) {
                fseek($fh, $headerLen);
                $gzData = stream_get_contents($fh);
                fclose($fh);

                $json = gzdecode($gzData);
                $queryDataArray = json_decode($json, true);
                if(empty($queryDataArray)) {
                    $queryDataArray = [];
                }
                return $queryDataArray;
            }

            return [];
        }

        if($newBuilt || !empty($getRecentIds)) {
            global $wpdb;
            $tablename = $wpdb->prefix . "contest_gal1ery";

            if(!empty($getRecentIds)){
                $placeholders = implode(',', array_fill(0, count($getRecentIds), '%d'));
                $queryData = $wpdb->get_results(
                    $wpdb->prepare(
                    "SELECT id, Exif, MultipleFiles
         FROM $tablename
         WHERE id IN ($placeholders) AND Active = '1' AND ((Exif != '' AND Exif != '0' AND Exif IS NOT NULL)
            OR MultipleFiles != '')",
                        $getRecentIds
                    )
                );
            }else{
                $queryData = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, Exif, MultipleFiles
         FROM $tablename
         WHERE GalleryID = %d AND Active = '1' AND ((Exif != '' AND Exif != '0' AND Exif IS NOT NULL)
            OR MultipleFiles != '')",
                    $gid
                ));
            }

            $queryDataArray = [];
            if(!empty($queryData)){
                foreach ($queryData as $rowObject){
                    $queryDataArray[$rowObject->id] = [];
                    if(!empty($rowObject->Exif)){
                        if(strlen($rowObject->Exif)>10){
                            $queryDataArray[$rowObject->id]['Exif'] = unserialize($rowObject->Exif);
                        }else{
                            $queryDataArray[$rowObject->id]['Exif'] = '';
                        }
                    }
                    if(!empty($rowObject->MultipleFiles) && $rowObject->MultipleFiles!='""'){
                        $queryDataArray[$rowObject->id]['MultipleFiles'] = unserialize($rowObject->MultipleFiles);
                    }
                }
            }

            if( !empty($getRecentIds)){
                return $queryDataArray;
            }

            $json = json_encode($queryDataArray);
            $gz   = gzencode($json, 9);

            if (!is_dir($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            //$temp = $base_dir . '/images-query-data.tmp';
            // Use a unique temp name to prevent collisions under high traffic
            $temp = $base_dir . '/' . uniqid('data_', true) . '.tmp';

            // Header padded to fixed length
            $header = cg1l_build_gzip_php_header();
            if (strlen($header) > $headerLen) {
                $header = substr($header, 0, $headerLen);
            } else {
                $header = str_pad($header, $headerLen, " ");
            }

            // Write header + gz payload
            $fh = fopen($temp, 'wb');
            if ($fh) {
                fwrite($fh, $header);
                fwrite($fh, $gz);
                fclose($fh);
                if (rename($temp, $final)) {
                    // Clear PHP file stat cache for this file
                    clearstatcache(true, $final);
                    // Update the timestamp marker using the atomic function
                    cg1l_create_last_updated_time_file($gid, 'image-query-data-last-update');
                } else {
                    if (file_exists($temp)) {
                        unlink($temp);
                    }
                }
            }
            return $queryDataArray;
        }else{
            return cg1l_build_images_query_data_gzip($gid, true);
        }
    }
}
