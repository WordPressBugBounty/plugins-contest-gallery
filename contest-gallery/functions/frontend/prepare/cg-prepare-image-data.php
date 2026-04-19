<?php
if (!function_exists('cg1l_get_entry_main_data')) {
    function cg1l_get_entry_main_data($gid,$entryId)
    {
        $wp_upload_dir = wp_upload_dir();

        $jsonFile = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $gid . '/json/image-data/image-data-'.$entryId.'.json';

        if(file_exists($jsonFile)) {
            $jsonFileData = json_decode(file_get_contents($jsonFile), true);
            if(!empty($jsonFileData)){
                return [
                    $entryId => $jsonFileData
                ];
            }else{
                return [];
            }
        }else{
            return [];
        }
    }
}

if (!function_exists('cg1l_build_images_main_data_gzip')) {
    function cg1l_build_images_main_data_gzip($gid, $getDataOnly = false) {

        $wp_upload_dir = wp_upload_dir();

        $base_dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/segments';
        $final    = $base_dir . "/images-main-data.json.gz.php";
        $lastUpdated    = $base_dir . "/image-main-data-last-update.txt";

        // Fixed header length for streaming (must match in output)
        $headerLen = 128;

        $newBuilt = false;
        $lastUpdatedTime = file_exists($lastUpdated) ? intval(trim(file_get_contents($lastUpdated))) : 0;
        $built = file_exists($final) ? filemtime($final) : 0;

        if(!file_exists($final) || !file_exists($lastUpdated) || ($lastUpdatedTime > $built && (time() - $built) > 3)){
            $newBuilt = true;
        }

        if($getDataOnly && file_exists($final) && !$newBuilt) {
            $fh = fopen($final, 'rb');
            if($fh) {
                fseek($fh, $headerLen);
                $gzData = stream_get_contents($fh);
                fclose($fh);

                $json = gzdecode($gzData);
                if ($json === false) { return []; }
                $imagesFullData = json_decode($json, true);
                if (empty($imagesFullData) || !is_array($imagesFullData)) { return []; }

                return $imagesFullData;
            }
            return [];
        }

        if($newBuilt){
            $optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/'.$gid.'-options.json';
            $options = json_decode(file_get_contents($optionsFile), true);
            $options = (!empty($options[$gid])) ? $options[$gid] : $options;

            global $wpdb;
            $tablename = $wpdb->prefix . "contest_gal1ery";

            $WpUserIds = $wpdb->get_results($wpdb->prepare(
                "SELECT id, WpUserId FROM $tablename WHERE GalleryID = %d",
                $gid
            ));

            $WpUserIdsArray = [];
            foreach ($WpUserIds as $WpUserIdRow){
                $WpUserIdsArray[$WpUserIdRow->id] = $WpUserIdRow->WpUserId;
            }

            $imageDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/image-data/*.json');

            $jsonImagesData = [];
            foreach ($imageDataJsonFiles as $jsonFile) {
                $jsonFileData = json_decode(file_get_contents($jsonFile), true);
                $imageId = substr(substr($jsonFile, strrpos($jsonFile, '-') + 1, 30), 0, -5);
                if(empty($jsonFileData['Category'])){
                    $jsonFileData['Category'] = 0;
                }
                $jsonImagesData[$imageId] = $jsonFileData;
                if(!empty($WpUserIdsArray[$imageId])){
                    $jsonImagesData[$imageId]['WpUserId'] = $WpUserIdsArray[$imageId];
                }
            }

            $imagesFullData = $jsonImagesData;

            /*if(!empty($options['general']['WpPageParent'])){
                foreach ($imagesFullData as $imageID => $imageData){
                    if(!empty($imageData['WpPage'])){
                        $imagesFullData[$imageID]['entryGuid'] = get_permalink($imageData['WpPage']);
                    }
                    if(!empty($imageData['WpPageUser'])){
                        $imagesFullData[$imageID]['entryGuidUser'] = get_permalink($imageData['WpPageUser']);
                    }
                    if(!empty($imageData['WpPageNoVoting'])){
                        $imagesFullData[$imageID]['entryGuidNoVoting'] = get_permalink($imageData['WpPageNoVoting']);
                    }
                    if(!empty($imageData['WpPageWinner'])){
                        $imagesFullData[$imageID]['entryGuidWinner'] = get_permalink($imageData['WpPageWinner']);
                    }
                    if(!empty($imageData['WpPageEcommerce'])){
                        $imagesFullData[$imageID]['entryGuidEcommerce'] = get_permalink($imageData['WpPageEcommerce']);
                    }
                }
            }*/

            $json = json_encode($imagesFullData);
            $gz = gzencode($json, 9);

            if (!is_dir($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            //$temp = $base_dir . '/images-main-data.tmp';
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
                    cg1l_create_last_updated_time_file($gid, 'image-main-data-last-update');
                } else {
                    if (file_exists($temp)) {
                        unlink($temp);
                    }
                }
            }
            return $imagesFullData;
        }else{
            return cg1l_build_images_main_data_gzip($gid,true);
        }
    }
}
