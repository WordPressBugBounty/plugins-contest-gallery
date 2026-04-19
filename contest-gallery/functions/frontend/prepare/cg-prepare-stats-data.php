<?php
if (!function_exists('cg1l_get_entry_stats_data')) {
    function cg1l_get_entry_stats_data($gid,$entryId) {
        $wp_upload_dir = wp_upload_dir();
        $jsonFile = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $gid . '/json/image-stats/image-stats-'.$entryId.'.json';

        if(file_exists($jsonFile)) {
            $jsonFileData = json_decode(file_get_contents($jsonFile), true);
            if(!empty($jsonFileData) && is_array($jsonFileData)){
                return [
                    $entryId => $jsonFileData
                ];
            }
        }

        if(function_exists('cg1l_repair_single_image_stats_file')){
            $repairResult = cg1l_repair_single_image_stats_file($gid, $entryId, true, [], true);
            if(!empty($repairResult['ok']) && file_exists($jsonFile)){
                $jsonFileData = json_decode(file_get_contents($jsonFile), true);
                if(!empty($jsonFileData) && is_array($jsonFileData)){
                    return [
                        $entryId => $jsonFileData
                    ];
                }
            }
        }

        return [];
    }
}

if (!function_exists('cg1l_build_images_stats_data_gzip')) {
    function cg1l_build_images_stats_data_gzip($gid, $imagesFullData = [], $getDataOnly = false) {

        $wp_upload_dir = wp_upload_dir();

        $base_dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/segments';
        $final    = $base_dir . "/images-stats-data.json.gz.php";
        $lastUpdated    = $base_dir . "/image-stats-data-last-update.txt";

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
                $imagesStatsData = json_decode($json, true);
                if(empty($imagesStatsData)) {
                    $imagesStatsData = [];
                }
                return $imagesStatsData;
            }

            return [];
        }

        if($newBuilt){
            $statsDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/image-stats/*.json');
            $imagesStatsData = [];
            foreach ($statsDataJsonFiles as $jsonFile) {
                $jsonFileData = json_decode(file_get_contents($jsonFile),true);
                $imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
                if(empty($imagesFullData[$imageId])){// then must be from some old installation and uses some old json files
                    //if(empty($jsonImagesData[$imageId])){// then must be from some old installation and uses some old json files
                    continue;
                }else{
                    $imagesStatsData[$imageId] = $jsonFileData;
                    if(isset($imagesStatsData[$imageId]['id'])){
                        unset($imagesStatsData[$imageId]['id']);// id already available as key
                    }
                }
            }

            $json = json_encode($imagesStatsData);
            $gz   = gzencode($json, 9);

            if (!is_dir($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            //$temp = $base_dir . '/images-stats-data.tmp';
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
                    cg1l_create_last_updated_time_file($gid, 'image-stats-data-last-update');
                } else {
                    if (file_exists($temp)) {
                        unlink($temp);
                    }
                }
            }
            return $imagesStatsData;
        }else{
            if(empty($imagesFullData)){
                $imagesFullData = cg1l_build_images_main_data_gzip($gid,true);
            }
            return cg1l_build_images_stats_data_gzip($gid,$imagesFullData,true);
        }

    }
}
