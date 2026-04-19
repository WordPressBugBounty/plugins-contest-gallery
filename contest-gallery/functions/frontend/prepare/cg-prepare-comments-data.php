<?php
if (!function_exists('cg1l_get_entry_comments_data')) {
    function cg1l_get_entry_comments_data($gid,$entryId) {
        $wp_upload_dir = wp_upload_dir();
        $jsonFile = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $gid . '/json/image-comments/image-comments-'.$entryId.'.json';
        if(file_exists($jsonFile)) {
            $jsonFileData = json_decode(file_get_contents($jsonFile), true);

            global $wpdb;
            $tablenameComments = $wpdb->prefix . "contest_gal1ery_comments";
            $commentsWpUserIds = $wpdb->get_results($wpdb->prepare(
                "
                                SELECT id, WpUserId 
                                FROM $tablenameComments
                                WHERE pid = %d 
                            ",
                $entryId
            ));

            $commentsWpUserIdsArray = [];
            foreach ($commentsWpUserIds as $commentsWpUserId){
                $commentsWpUserIdsArray[$commentsWpUserId->id] = $commentsWpUserId->WpUserId;
            }

            $jsonCommentsData[$entryId] = $jsonFileData;
            foreach ($jsonCommentsData[$entryId] as $key => $array){
                if(!empty($jsonCommentsData[$entryId][$key]['insert_id']) && !empty($commentsWpUserIdsArray[$jsonCommentsData[$entryId][$key]['insert_id']])){
                    $jsonCommentsData[$entryId][$key]['WpUserId'] = $commentsWpUserIdsArray[$jsonCommentsData[$entryId][$key]['insert_id']];
                }
            }

            if(!empty($jsonFileData)){
                return [
                    $entryId => $jsonCommentsData[$entryId]
                ];
            }else{
                return [];
            }
        }else{
            return [];
        }
    }
}

if (!function_exists('cg1l_build_images_comments_data_gzip')) {
    function cg1l_build_images_comments_data_gzip($gid, $imagesFullData = [], $getDataOnly = false) {

        $wp_upload_dir = wp_upload_dir();

        $base_dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/segments';
        $final    = $base_dir . "/images-comments-data.json.gz.php";
        $lastUpdated    = $base_dir . "/image-comments-data-last-update.txt";

        // Fixed header length for streaming (must match output handler)
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
                $jsonCommentsData = json_decode($json, true);
                if(empty($jsonCommentsData)) {
                    $jsonCommentsData = [];
                }
                return $jsonCommentsData;
            }

            return [];
        }

        if($newBuilt){

            $commentsDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/image-comments/*.json');
            $jsonCommentsData = [];

            foreach ($commentsDataJsonFiles as $jsonFile) {
                $jsonFileData = json_decode(file_get_contents($jsonFile),true);
                if(!empty($jsonFileData)){
                    $stringArray= explode('/image-comments-',$jsonFile);
                    $imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
                    if(empty($imagesFullData[$imageId])){// then must be from some old installation and uses some old json files
                        //if(empty($jsonImagesData[$imageId])){// then must be from some old installation and uses some old json files
                        continue;
                    }else{
                        $jsonCommentsData[$imageId] = $jsonFileData;
                    }
                }
            }

            $jsonCommentsData = cg1l_get_comments_user_ids($gid, $jsonCommentsData);

            $json = json_encode($jsonCommentsData);
            $gz   = gzencode($json, 9);

            if (!is_dir($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            //$temp = $base_dir . '/images-comments-data.tmp';
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
                    cg1l_create_last_updated_time_file($gid, 'image-comments-data-last-update');
                } else {
                    if (file_exists($temp)) {
                        unlink($temp);
                    }
                }
            }

            return $jsonCommentsData;
        }else{
            if(empty($imagesFullData)){
                $imagesFullData = cg1l_build_images_main_data_gzip($gid,true);
            }
            return cg1l_build_images_comments_data_gzip($gid, $imagesFullData, true);
        }

    }
}

if (!function_exists('cg1l_get_comments_user_ids')) {
    function cg1l_get_comments_user_ids($gid, $jsonCommentsData) {

        if(!empty($jsonCommentsData)){
            global $wpdb;
            $tablenameComments = $wpdb->prefix . "contest_gal1ery_comments";

            $commentsWpUserIds = $wpdb->get_results($wpdb->prepare(
                "
                                SELECT id, WpUserId 
                                FROM $tablenameComments
                                WHERE GalleryID = %d 
                            ",
                $gid
            ));
            $commentsWpUserIdsArray = [];
            foreach ($commentsWpUserIds as $commentsWpUserId){
                $commentsWpUserIdsArray[$commentsWpUserId->id] = $commentsWpUserId->WpUserId;
            }

            foreach ($jsonCommentsData as $imageId => $commentData){
                foreach ($jsonCommentsData[$imageId] as $key => $array){
                    if(!empty($jsonCommentsData[$imageId][$key]['insert_id']) && !empty($commentsWpUserIdsArray[$jsonCommentsData[$imageId][$key]['insert_id']])){
                        $jsonCommentsData[$imageId][$key]['WpUserId'] = $commentsWpUserIdsArray[$jsonCommentsData[$imageId][$key]['insert_id']];
                    }
                }
            }

            return $jsonCommentsData;

        }else{
            return $jsonCommentsData;
        }
    }
}

