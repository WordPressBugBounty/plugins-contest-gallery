<?php
if (!function_exists('cg1l_get_shortcode_entry_url_map')) {
    function cg1l_get_shortcode_entry_url_map() {
        return [
            'cg_gallery'             => ['WpPage',           'entryGuid'],
            'cg_gallery_user'        => ['WpPageUser',       'entryGuidUser'],
            'cg_gallery_no_voting'   => ['WpPageNoVoting',   'entryGuidNoVoting'],
            'cg_gallery_winner'      => ['WpPageWinner',     'entryGuidWinner'],
            'cg_gallery_ecommerce'   => ['WpPageEcommerce',  'entryGuidEcommerce'],
        ];
    }
}
if (!function_exists('cg1l_get_entry_urls_data')) {
    function cg1l_get_entry_urls_data($entryId,$recentMainData,$shortcode_name) {
        $map = cg1l_get_shortcode_entry_url_map();
        if(empty($map[$shortcode_name])){
            return $recentMainData;
        }

        $pageKey = $map[$shortcode_name][0];

        //$url1 = $url."#!gallery/$galeryIDuser/file/$nextId/$post_title";#toDo eventually
        if(!empty($recentMainData[$entryId][$pageKey])){
            $recentMainData[$entryId]['entryGuid'] = get_permalink($recentMainData[$entryId][$pageKey]);
        }

        return $recentMainData;
    }
}
if (!function_exists('cg1l_build_images_urls_data_gzip')) {
    function cg1l_build_images_urls_data_gzip($gid, $imagesFullData = [], $getDataOnly = false, $shortcode_name = '', $getRecentIds = []) {
        $wp_upload_dir = wp_upload_dir();

        $base_dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/segments';
        $final    = $base_dir . "/images-urls-data.json.gz.php";
        $lastUpdated    = $base_dir . "/image-urls-data-last-update.txt";

        // Fixed header length for streaming (must match in output)
        $headerLen = 128;

        $newBuilt = false;
        $lastUpdatedTime = file_exists($lastUpdated) ? intval(trim(file_get_contents($lastUpdated))) : 0;
        $built = file_exists($final) ? filemtime($final) : 0;

        if(!file_exists($final) || !file_exists($lastUpdated) || ($lastUpdatedTime > $built && (time() - $built) > 3)){
            $newBuilt = true;
        }

        if($getDataOnly && file_exists($final) && !$newBuilt && empty($getRecentIds)) {
            //var_dump(1);die;
            $fh = fopen($final, 'rb');

            if($fh) {
                fseek($fh, $headerLen);
                $gzData = stream_get_contents($fh);
                fclose($fh);

                $json = gzdecode($gzData);
                $imagesUrlsData = json_decode($json, true);
                if(empty($imagesUrlsData) || !is_array($imagesUrlsData)) {
                    $imagesUrlsData = [];
                }
                if (!empty($imagesFullData) && !empty($imagesUrlsData)) {
                    $map = cg1l_get_shortcode_entry_url_map();

                    if (isset($map[$shortcode_name])) {

                        $pageKey = $map[$shortcode_name][0];
                        $guidKey = $map[$shortcode_name][1];

                        foreach ($imagesFullData as $id => $data) {

                            // Ensure both sides exist before accessing nested keys
                            if (
                                !empty($data[$pageKey]) &&
                                isset($imagesUrlsData[$id]) &&
                                !empty($imagesUrlsData[$id][$guidKey])
                            ) {
                                $imagesFullData[$id]['entryGuid'] = $imagesUrlsData[$id][$guidKey];
                            }
                        }
                    }
                }

                if(!empty($imagesFullData)){
                    return $imagesFullData;
                }

                return $imagesUrlsData;
            }
            return [];
        }

        if($newBuilt || !empty($getRecentIds)){
            //var_dump(2);
            //var_dump('$imagesFullData');
            //var_dump($imagesFullData);

            //die;
            $imagesUrlsData = [];

            if (!empty($imagesFullData)) {
                if(!empty($getRecentIds)){
                    $map = cg1l_get_shortcode_entry_url_map();
                    foreach ($getRecentIds as $id) {
                        if(empty($map[$shortcode_name]) || empty($imagesFullData[$id])){
                            continue;
                        }

                        $pageKey = $map[$shortcode_name][0];
                        if (!empty($imagesFullData[$id][$pageKey])) {
                            $imagesUrlsData[$id]['entryGuid'] = get_permalink($imagesFullData[$id][$pageKey]);
                        }
                    }
                }else{
                    reset($imagesFullData);
                    $firstImage = current($imagesFullData);

                    if (!empty($firstImage['WpPage'])) {
                        foreach ($imagesFullData as $id => $imageData) {

                            if (!empty($imageData['WpPage'])) {
                                $imagesUrlsData[$id]['entryGuid'] = get_permalink($imageData['WpPage']);
                            }

                            if (!empty($imageData['WpPageUser'])) {
                                $imagesUrlsData[$id]['entryGuidUser'] = get_permalink($imageData['WpPageUser']);
                            }

                            if (!empty($imageData['WpPageNoVoting'])) {
                                $imagesUrlsData[$id]['entryGuidNoVoting'] = get_permalink($imageData['WpPageNoVoting']);
                            }

                            if (!empty($imageData['WpPageWinner'])) {
                                $imagesUrlsData[$id]['entryGuidWinner'] = get_permalink($imageData['WpPageWinner']);
                            }

                            if (!empty($imageData['WpPageEcommerce'])) {
                                $imagesUrlsData[$id]['entryGuidEcommerce'] = get_permalink($imageData['WpPageEcommerce']);
                            }
                        }
                    }
                }
            }

            if(!empty($getRecentIds)){
                return $imagesUrlsData;
            }

            $json = json_encode($imagesUrlsData);
            $gz = gzencode($json, 9);

            if (!is_dir($base_dir)) {
                wp_mkdir_p($base_dir);
            }

            //$temp = $base_dir . '/images-urls-data.tmp';
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
                    cg1l_create_last_updated_time_file($gid, 'image-urls-data-last-update');
                } else {
                    if (file_exists($temp)) {
                        unlink($temp);
                    }
                }
            }

            return $imagesUrlsData;
        }else{
            //var_dump(3);die;
            if(empty($imagesFullData)){
                $imagesFullData = cg1l_build_images_main_data_gzip($gid,true);
            }
            return cg1l_build_images_urls_data_gzip($gid, $imagesFullData, true, $shortcode_name);
        }
    }
}
