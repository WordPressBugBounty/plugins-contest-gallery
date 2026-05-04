<?php

if (!function_exists('cg1l_create_last_updated_time_file')) {
    function cg1l_create_last_updated_time_file($gid, $type) {
        $wp_upload_dir = wp_upload_dir();
        $base_dir = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $gid . '/json/segments';

        // Ensure directory exists
        if (!is_dir($base_dir)) {
            wp_mkdir_p($base_dir);
        }

        $final = $base_dir . "/" . $type . ".txt";

        // Create a unique temporary filename to prevent race conditions
        // when multiple users trigger this simultaneously
        $temp = $final . '.' . uniqid('tmp_', true);

        // Write the current timestamp to the unique temporary file
        $fh = fopen($temp, 'wb');
        if ($fh) {
            fwrite($fh, (string) time());
            fclose($fh);

            // Atomically replace the final file with the unique temp file
            if (rename($temp, $final)) {
                // Clear stat cache in case the file is read again in the same request
                clearstatcache(true, $final);
            } else {
                // Clean up temp file if rename fails
                if (file_exists($temp)) {
                    unlink($temp);
                }
            }
        }
    }
}

if (!function_exists('cg1l_create_last_updated_time_file_all')) {
    function cg1l_create_last_updated_time_file_all($gid) {
        cg1l_create_last_updated_time_file($gid,'image-main-data-last-update');
        cg1l_create_last_updated_time_file($gid,'image-query-data-last-update');
        cg1l_create_last_updated_time_file($gid,'image-urls-data-last-update');
        cg1l_create_last_updated_time_file($gid,'image-stats-data-last-update');
        cg1l_create_last_updated_time_file($gid,'image-comments-data-last-update');
        cg1l_create_last_updated_time_file($gid,'image-info-data-last-update');
    }
}

if (!function_exists('cg1l_create_last_updated_time_file_image_data')) {
    function cg1l_create_last_updated_time_file_image_data($gid) {
        cg1l_create_last_updated_time_file($gid,'image-main-data-last-update');
        cg1l_create_last_updated_time_file($gid,'image-query-data-last-update');
        cg1l_create_last_updated_time_file($gid,'image-urls-data-last-update');
    }
}

if (!function_exists('cg1l_build_gzip_php_header')) {
    function cg1l_build_gzip_php_header() {
        $header = "<?php\n// Prevent direct access\nexit;\n__halt_compiler();\n";
        return $header;
    }
}

/**
 * Push an ID into a recent list stored as <id>.txt files.
 * Keeps max $max files, removes oldest ones.
 *
 * @param int    $gid   Gallery ID
 * @param int    $id    Entry ID to mark as recent
 * @param string $type  Subfolder name (e.g. 'recent-image-data')
 * @param int    $max   Maximum number of files to keep (e.g. 10 or 20)
 */

if (!function_exists('cg1l_push_recent_id_file')) {
    function cg1l_push_recent_id_file($gid, $id, $type, $delete = false) {

        $max = 10;
        if($type == 'image-stats-data-last-update' || $type == 'image-info-data-last-update') {
            $max = 20;
        }

        $gid = absint($gid);
        $id  = absint($id);
        $max = absint($max);

        if ($gid === 0 || $id === 0 || $max < 1) {
            return;
        }

        $wp_upload_dir = wp_upload_dir();
        $base_dir = $wp_upload_dir['basedir']
            . '/contest-gallery/gallery-id-' . $gid
            . '/json/segments/' . $type;

        // Ensure directory exists
        if (!is_dir($base_dir)) {
            wp_mkdir_p($base_dir);
        }

        $file = $base_dir . '/' . $id . '.txt';

        /* =========================
       DELETE MODE
       ========================= */
        if ($delete === true) {
            if (file_exists($file)) {
                unlink($file);
            }
            return;
        }

        /* =========================
           ADD MODE
           ========================= */

        // If file already exists, do nothing (no duplicate, no touch)
        if (!file_exists($file)) {
            file_put_contents($file, (string) time(), LOCK_EX);
        }

        // Collect all *.txt files
        $files = glob($base_dir . '/*.txt');
        if (!$files || count($files) <= $max) {
            return;
        }

        // Sort by filemtime ASC (oldest first)
        usort($files, function ($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Remove oldest files exceeding $max
        $remove_count = count($files) - $max;
        for ($i = 0; $i < $remove_count; $i++) {
            if (file_exists($files[$i])) {
                unlink($files[$i]);
            }
        }
    }
}


if (!function_exists('cg1l_push_recent_id_file_all_types')) {
    function cg1l_push_recent_id_file_all_types($gid,$id,$delete = false) {
        cg1l_push_recent_id_file($gid,$id,'image-main-data-last-update',$delete);
        cg1l_push_recent_id_file($gid,$id,'image-query-data-last-update',$delete);
        cg1l_push_recent_id_file($gid,$id,'image-urls-data-last-update',$delete);
        cg1l_push_recent_id_file($gid,$id,'image-stats-data-last-update',$delete);
        cg1l_push_recent_id_file($gid,$id,'image-comments-data-last-update',$delete);
        cg1l_push_recent_id_file($gid,$id,'image-info-data-last-update',$delete);
    }
}

if (!function_exists('cg1l_frontend_sort_by')) {
    function cg1l_frontend_sort_by($imagesFullData, $sortBy = 'id', $length = 20, $order = 'DESC',  $specialSortType = '', $CountRMax = 5, $isCGalleriesNoSorting = false)
    {
        if(!$isCGalleriesNoSorting){
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
            $CountRMax = (int) $CountRMax;
            if ($CountRMax < 1) {
                $CountRMax = 1;
            }
            if ($CountRMax > 10) {
                $CountRMax = 10;
            }

            uasort($imagesFullData, function ($a, $b) use ($sortBy, $order, $specialSortType, $CountRMax) {
                $secondaryValA = null;
                $secondaryValB = null;

                if ($specialSortType === 'CountSWithManipulation') {

                    $valA = (isset($a['CountS']) ? (int) $a['CountS'] : 0) + (isset($a['addCountS']) ? (int) $a['addCountS'] : 0);
                    $valB = (isset($b['CountS']) ? (int) $b['CountS'] : 0) + (isset($b['addCountS']) ? (int) $b['addCountS'] : 0);

                } elseif ($specialSortType === 'CountRtotal' || $specialSortType === 'CountRtotalWithManipulation') {

                    $valA = 0;
                    $valB = 0;
                    $weightedValA = 0;
                    $weightedValB = 0;

                    $i = 1;
                    while ($i <= $CountRMax) {

                        $keyBase = 'CountR' . $i;
                        $keyAdd  = 'addCountR' . $i;

                        $countA = isset($a[$keyBase]) ? (int) $a[$keyBase] : 0;
                        $countB = isset($b[$keyBase]) ? (int) $b[$keyBase] : 0;

                        if ($specialSortType === 'CountRtotalWithManipulation') {
                            $countA += isset($a[$keyAdd]) ? (int) $a[$keyAdd] : 0;
                            $countB += isset($b[$keyAdd]) ? (int) $b[$keyAdd] : 0;
                        }

                        $valA += $countA;
                        $valB += $countB;
                        $weightedValA += $countA * $i;
                        $weightedValB += $countB * $i;

                        $i++;
                    }

                    $secondaryValA = ($valA > 0) ? ($weightedValA / $valA) : 0;
                    $secondaryValB = ($valB > 0) ? ($weightedValB / $valB) : 0;

                } elseif ($specialSortType === 'CountR') {

                    $valA = 0;
                    $valB = 0;

                    $i = 1;
                    while ($i <= $CountRMax) {

                        $key = 'CountR' . $i;

                        $countA = isset($a[$key]) ? (int) $a[$key] : 0;
                        $countB = isset($b[$key]) ? (int) $b[$key] : 0;

                        $valA += $countA * $i;
                        $valB += $countB * $i;

                        $i++;
                    }

                } elseif ($specialSortType === 'CountRWithManipulation') {

                    $valA = 0;
                    $valB = 0;

                    $i = 1;
                    while ($i <= $CountRMax) {

                        $keyBase = 'CountR' . $i;
                        $keyAdd  = 'addCountR' . $i;

                        $countA = (isset($a[$keyBase]) ? (int) $a[$keyBase] : 0)
                            + (isset($a[$keyAdd])  ? (int) $a[$keyAdd]  : 0);

                        $countB = (isset($b[$keyBase]) ? (int) $b[$keyBase] : 0)
                            + (isset($b[$keyAdd])  ? (int) $b[$keyAdd]  : 0);

                        $valA += $countA * $i;
                        $valB += $countB * $i;

                        $i++;
                    }

                } else {

                    $valA = isset($a[$sortBy]) ? (int) $a[$sortBy] : 0;
                    $valB = isset($b[$sortBy]) ? (int) $b[$sortBy] : 0;
                }

                // Primary comparison
                if ($valA !== $valB) {
                    if ($order === 'ASC') {
                        return ($valA < $valB) ? -1 : 1;
                    }
                    return ($valA > $valB) ? -1 : 1;
                }

                if ($secondaryValA !== null && $secondaryValB !== null && $secondaryValA !== $secondaryValB) {
                    if ($order === 'ASC') {
                        return ($secondaryValA < $secondaryValB) ? -1 : 1;
                    }
                    return ($secondaryValA > $secondaryValB) ? -1 : 1;
                }

                // Secondary fallback: id (always DESC = latest first)
                $idA = isset($a['id']) ? (int) $a['id'] : 0;
                $idB = isset($b['id']) ? (int) $b['id'] : 0;

                if ($idA === $idB) {
                    return 0;
                }

                return ($idA > $idB) ? -1 : 1;
            });
        }

        return [
            'imagesFullDataFull' => $imagesFullData,
            'imagesFullDataSliced' => array_slice($imagesFullData, 0, (int) $length, true)
        ];
    }
}

if (!function_exists('cg1l_frontend_sort_random')) {
    function cg1l_frontend_sort_random($imagesFullData, $length = 20)
    {
        if(empty($imagesFullData) || !is_array($imagesFullData)){
            return [
                'imagesFullDataFull' => [],
                'imagesFullDataSliced' => []
            ];
        }

        $randomizedIds = array_keys($imagesFullData);
        shuffle($randomizedIds);

        $randomizedImagesFullData = [];

        foreach($randomizedIds as $randomizedId){
            if(array_key_exists($randomizedId, $imagesFullData)){
                $randomizedImagesFullData[$randomizedId] = $imagesFullData[$randomizedId];
            }
        }

        return [
            'imagesFullDataFull' => $randomizedImagesFullData,
            'imagesFullDataSliced' => array_slice($randomizedImagesFullData, 0, (int) $length, true)
        ];
    }
}

if (!function_exists('cg1l_frontend_get_meta_html')) {
    function cg1l_frontend_get_meta_html($ImgType, $fullData)
    {
        $dateCreated = cg_get_time_based_on_wp_timezone_conf($fullData['Timestamp'], 'Y-m-d\TH:i:sP');
        $title = isset($fullData['post_title']) ? $fullData['post_title'] : '';
        $url   = isset($fullData['guid']) ? $fullData['guid'] : '';
        $meta = '';
        if (cg_is_is_image($ImgType)) {
            $meta  = '<meta itemprop="name" content="' . esc_attr($title) . '">' . "\r\n";
            $meta .= '<meta itemprop="dateCreated" content="' . esc_attr($dateCreated) . '">' . "\r\n";
            $meta .= '<meta itemprop="image" content="' . esc_url($url) . '">' . "\r\n";
        } elseif (cg_is_alternative_file_type_video($ImgType)) {
            $encodingFormat = cg1l_get_encoding_formats($ImgType);

            $meta  = '<meta itemprop="name" content="' . esc_attr($title) . '">' . "\r\n";
            $meta .= '<meta itemprop="dateCreated" content="' . esc_attr($dateCreated) . '">' . "\r\n";
            $meta .= '<meta itemprop="contentUrl" content="' . esc_url($url) . '">' . "\r\n";
            $meta .= '<meta itemprop="encodingFormat" content="' . esc_attr($encodingFormat) . '">' . "\r\n";
        } elseif (cg_is_alternative_file_type_file($ImgType)) {
            $encodingFormat = cg1l_get_encoding_formats($ImgType);
            $meta  = '<meta itemprop="name" content="' . esc_attr($title) . '">' . "\r\n";
            $meta .= '<meta itemprop="dateCreated" content="' . esc_attr($dateCreated) . '">' . "\r\n";
            $meta .= '<meta itemprop="contentUrl" content="' . esc_url($url) . '">' . "\r\n";
            $meta .= '<meta itemprop="encodingFormat" content="' . esc_attr($encodingFormat) . '">' . "\r\n";
        } elseif (cgl_check_if_embeded($ImgType)){
            // Convert slug to readable title
            $title = str_replace('-', ' ', $fullData['post_title']);
            // Normalize whitespace
            $title = preg_replace('/\s+/u', ' ', trim($title));
            // Cut after X chars (UTF-8 safe) without cutting the last word
            $max = 60;
            if (mb_strlen($title, 'UTF-8') > $max) {
                $title = mb_substr($title, 0, $max, 'UTF-8');
                $title = preg_replace('/\s+\S*$/u', '', $title);
            }
            // Make it look like a title (fallback only)
            $title = mb_convert_case($title, MB_CASE_TITLE, 'UTF-8');
            $meta  = '<meta itemprop="name" content="' . esc_attr($title) . '">' . "\r\n";
            $meta .= '<meta itemprop="embedUrl" content="' . esc_attr($fullData['guid']) . '">' . "\r\n";
        } elseif (cgl_check_if_embeded($ImgType)){
            // Convert slug to readable title
            $title = str_replace('-', ' ', $fullData['post_title']);
            // Normalize whitespace
            $title = preg_replace('/\s+/u', ' ', trim($title));
            // Cut after X chars (UTF-8 safe) without cutting the last word
            $max = 60;
            if (mb_strlen($title, 'UTF-8') > $max) {
                $title = mb_substr($title, 0, $max, 'UTF-8');
                $title = preg_replace('/\s+\S*$/u', '', $title);
            }
            // Make it look like a title (fallback only)
            $title = mb_convert_case($title, MB_CASE_TITLE, 'UTF-8');
            $meta  = '<meta itemprop="name" content="' . esc_attr($title) . '">' . "\r\n";
            $meta .= '<meta itemprop="embedUrl" content="' . esc_attr($fullData['guid']) . '">' . "\r\n";
        }
        return $meta;
    }
}

if (!function_exists('cg1l_frontend_get_meta_comment_count')) {
    function cg1l_frontend_get_meta_comment_count($fullData)
    {
        $meta = '<meta itemprop="commentCount" content="' . intval($fullData['CountC']) . '">' . "\r\n";
        return $meta;
    }
}

if (!function_exists('cg1l_frontend_filter_id_keyed_data_by_allowed_ids')) {
    function cg1l_frontend_filter_id_keyed_data_by_allowed_ids($data, $allowedIds)
    {
        if (!is_array($data)) {
            return $data;
        }

        if (empty($allowedIds) || !is_array($allowedIds)) {
            return [];
        }

        $filteredData = [];

        foreach ($allowedIds as $allowedId) {
            if (array_key_exists($allowedId, $data)) {
                $filteredData[$allowedId] = $data[$allowedId];
            }
        }

        return $filteredData;
    }
}

if (!function_exists('cg1l_frontend_get_multiple_files_for_entry')) {
    function cg1l_frontend_get_multiple_files_for_entry($fullData, $queryEntryData = [])
    {
        if (!empty($fullData['MultipleFiles']) && is_array($fullData['MultipleFiles'])) {
            return $fullData['MultipleFiles'];
        }

        if (!empty($queryEntryData['MultipleFiles']) && is_array($queryEntryData['MultipleFiles'])) {
            return $queryEntryData['MultipleFiles'];
        }

        return [];
    }
}

if (!function_exists('cg1l_frontend_apply_current_multiple_file_to_entry')) {
    function cg1l_frontend_apply_current_multiple_file_to_entry($fullData, $queryEntryData = [])
    {
        if (!is_array($fullData)) {
            return $fullData;
        }

        $multipleFiles = cg1l_frontend_get_multiple_files_for_entry($fullData, $queryEntryData);
        if (empty($multipleFiles[1]) || !is_array($multipleFiles[1])) {
            return $fullData;
        }

        $fieldMap = [
            'WpUpload' => 'WpUpload',
            'post_title' => 'post_title',
            'post_name' => 'post_name',
            'post_content' => 'post_content',
            'post_excerpt' => 'post_excerpt',
            'post_mime_type' => 'post_mime_type',
            'medium' => 'medium',
            'large' => 'large',
            'full' => 'full',
            'guid' => 'guid',
            'type' => 'type',
            'NamePic' => 'NamePic',
            'ImgType' => 'ImgType',
            'Width' => 'Width',
            'Height' => 'Height',
            'rThumb' => 'rThumb',
            'Exif' => 'Exif',
            'PdfOriginal' => 'PdfOriginal'
        ];

        foreach ($multipleFiles as $multipleFilesOrder => $multipleFileData) {
            if (!is_array($multipleFileData) || empty($multipleFileData['isRealIdSource'])) {
                continue;
            }

            foreach ($fieldMap as $targetKey => $sourceKey) {
                if (array_key_exists($sourceKey, $fullData)) {
                    $multipleFiles[$multipleFilesOrder][$targetKey] = $fullData[$sourceKey];
                }
            }

            $multipleFiles[$multipleFilesOrder]['imgSrcOriginalWidth'] = isset($fullData['Width']) ? $fullData['Width'] : 0;
            $multipleFiles[$multipleFilesOrder]['imgSrcOriginalHeight'] = isset($fullData['Height']) ? $fullData['Height'] : 0;
        }

        $selectedOrder = 1;
        $selectedFileData = $multipleFiles[$selectedOrder];
        if (!is_array($selectedFileData)) {
            return $fullData;
        }

        $normalizedData = $fullData;
        $normalizedData['selectedOrder'] = $selectedOrder;

        if (empty($selectedFileData['isRealIdSource'])) {
            foreach ($fieldMap as $targetKey => $sourceKey) {
                if (array_key_exists($sourceKey, $selectedFileData)) {
                    $normalizedData[$targetKey] = $selectedFileData[$sourceKey];
                }
            }

            if (array_key_exists('imgSrcOriginalWidth', $selectedFileData)) {
                $normalizedData['imgSrcOriginalWidth'] = $selectedFileData['imgSrcOriginalWidth'];
            } elseif (array_key_exists('Width', $selectedFileData)) {
                $normalizedData['imgSrcOriginalWidth'] = $selectedFileData['Width'];
            }

            if (array_key_exists('imgSrcOriginalHeight', $selectedFileData)) {
                $normalizedData['imgSrcOriginalHeight'] = $selectedFileData['imgSrcOriginalHeight'];
            } elseif (array_key_exists('Height', $selectedFileData)) {
                $normalizedData['imgSrcOriginalHeight'] = $selectedFileData['Height'];
            }
        } else {
            $normalizedData['imgSrcOriginalWidth'] = isset($fullData['Width']) ? $fullData['Width'] : 0;
            $normalizedData['imgSrcOriginalHeight'] = isset($fullData['Height']) ? $fullData['Height'] : 0;
        }

        return $normalizedData;
    }
}

if (!function_exists('cg1l_frontend_apply_current_multiple_files_to_dataset')) {
    function cg1l_frontend_apply_current_multiple_files_to_dataset($mainDataArray, $queryDataArray = [])
    {
        if (!is_array($mainDataArray)) {
            return $mainDataArray;
        }

        foreach ($mainDataArray as $id => $fullData) {
            $queryEntryData = (!empty($queryDataArray[$id]) && is_array($queryDataArray[$id])) ? $queryDataArray[$id] : [];
            $mainDataArray[$id] = cg1l_frontend_apply_current_multiple_file_to_entry($fullData, $queryEntryData);
        }

        return $mainDataArray;
    }
}

if (!function_exists('cg1l_frontend_is_shortcode_allowed_for_entry')) {
    function cg1l_frontend_is_shortcode_allowed_for_entry($shortcode_name, $fullData, $WpUserId = 0)
    {
        if (!is_array($fullData)) {
            return false;
        }

        if ($shortcode_name === 'cg_gallery_user') {
            return !empty($WpUserId) && !empty($fullData['WpUserId']) && intval($fullData['WpUserId']) === intval($WpUserId);
        }

        if ($shortcode_name === 'cg_gallery_winner') {
            return !empty($fullData['Winner']);
        }

        if ($shortcode_name === 'cg_gallery_ecommerce') {
            return !empty($fullData['EcommerceEntry']);
        }

        return true;
    }
}

if (!function_exists('cg1l_frontend_has_active_category_upload_field')) {
    function cg1l_frontend_has_active_category_upload_field($formUploadFullData)
    {
        if (!is_array($formUploadFullData) || empty($formUploadFullData)) {
            return false;
        }

        foreach ($formUploadFullData as $field) {
            if (
                is_array($field) &&
                !empty($field['Field_Type']) &&
                $field['Field_Type'] === 'selectc-f' &&
                (!isset($field['Active']) || intval($field['Active']) === 1)
            ) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('cg1l_frontend_is_category_allowed_for_entry')) {
    function cg1l_frontend_is_category_allowed_for_entry($fullData, $categoriesFullData, $options, $formUploadFullData = null)
    {
        if (!is_array($fullData)) {
            return false;
        }

        if (is_array($formUploadFullData) && !cg1l_frontend_has_active_category_upload_field($formUploadFullData)) {
            return true;
        }

        $categoryId = (!empty($fullData['Category'])) ? absint($fullData['Category']) : 0;

        if ($categoryId === 0) {
            return !empty($options['pro']['ShowOther']);
        }

        return (
            !empty($categoriesFullData[$categoryId]) &&
            isset($categoriesFullData[$categoryId]['Active']) &&
            intval($categoriesFullData[$categoryId]['Active']) === 1
        );
    }
}

if (!function_exists('cg1l_frontend_get_entry_filter_block_reason')) {
    function cg1l_frontend_get_entry_filter_block_reason($shortcode_name, $fullData, $categoriesFullData, $options, $WpUserId = 0, $formUploadFullData = null)
    {
        if (!is_array($fullData)) {
            return [];
        }

        if (!cg1l_frontend_is_shortcode_allowed_for_entry($shortcode_name, $fullData, $WpUserId)) {
            return [
                'type' => 'shortcode'
            ];
        }

        if (cg1l_frontend_is_category_allowed_for_entry($fullData, $categoriesFullData, $options, $formUploadFullData)) {
            return [];
        }

        $categoryId = (!empty($fullData['Category'])) ? absint($fullData['Category']) : 0;

        if ($categoryId === 0) {
            return [
                'type' => 'category',
                'categoryId' => 0,
                'categoryName' => 'Other'
            ];
        }

        $categoryName = '';
        if (!empty($categoriesFullData[$categoryId]['Name'])) {
            $categoryName = $categoriesFullData[$categoryId]['Name'];
        }

        return [
            'type' => 'category',
            'categoryId' => $categoryId,
            'categoryName' => $categoryName
        ];
    }
}

if (!function_exists('cg1l_frontend_get_allowed_real_ids')) {
    function cg1l_frontend_get_allowed_real_ids($imagesFullData, $shortcode_name, $categoriesFullData, $options, $WpUserId = 0, $formUploadFullData = null)
    {
        if (!is_array($imagesFullData)) {
            return [];
        }

        $allowedRealIds = [];

        foreach ($imagesFullData as $id => $fullData) {
            if (!cg1l_frontend_is_shortcode_allowed_for_entry($shortcode_name, $fullData, $WpUserId)) {
                continue;
            }

            if (!cg1l_frontend_is_category_allowed_for_entry($fullData, $categoriesFullData, $options, $formUploadFullData)) {
                continue;
            }

            $allowedRealIds[] = intval($id);
        }

        return $allowedRealIds;
    }
}

if (!function_exists('cg1l_frontend_get_ecommerce_images_full_data_only')) {
    function cg1l_frontend_get_ecommerce_images_full_data_only($imagesFullData)
    {
        if (!is_array($imagesFullData)) {
            return [];
        }

        $filteredImagesFullData = [];

        foreach ($imagesFullData as $id => $fullData) {
            if (!empty($fullData['EcommerceEntry'])) {
                $filteredImagesFullData[$id] = $fullData;
            }
        }

        return $filteredImagesFullData;
    }
}

if (!function_exists('cg1l_frontend_get_ecommerce_sale_type_label')) {
    function cg1l_frontend_get_ecommerce_sale_type_label($entryEcommerceData, $languageNames)
    {
        if (!empty($entryEcommerceData['IsShipping'])) {
            return (!empty($languageNames['ecommerce']['Shipping'])) ? $languageNames['ecommerce']['Shipping'] : 'Shipping';
        }

        if (!empty($entryEcommerceData['IsDownload'])) {
            return (!empty($languageNames['ecommerce']['Download'])) ? $languageNames['ecommerce']['Download'] : 'Download';
        }

        if (!empty($entryEcommerceData['IsService'])) {
            if (!empty($languageNames['ecommerce']['Service'])) {
                return $languageNames['ecommerce']['Service'];
            }

            return (!empty($languageNames['gallery']['Service'])) ? $languageNames['gallery']['Service'] : 'Service';
        }

        if (!empty($entryEcommerceData['IsUpload'])) {
            if (!empty($languageNames['gallery']['sendUpload'])) {
                return $languageNames['gallery']['sendUpload'];
            }

            return (!empty($languageNames['ecommerce']['Upload'])) ? $languageNames['ecommerce']['Upload'] : 'Upload';
        }

        return '';
    }
}

if (!function_exists('cg1l_frontend_get_ecommerce_sale_data')) {
    function cg1l_frontend_get_ecommerce_sale_data($fullData, $ecommerceFilesData, $ecommerceOptions, $currenciesArray, $languageNames)
    {
        $saleData = [
            'isActive' => false,
            'salePriceText' => '',
            'salePriceLeftSymbol' => '',
            'salePriceLeft' => '',
            'salePriceRight' => '',
            'salePriceRightSymbol' => '',
            'salePriceType' => '',
            'saleTitleText' => '',
            'saleDescriptionText' => ''
        ];

        if (empty($fullData['id']) || empty($fullData['EcommerceEntry'])) {
            return $saleData;
        }

        $rid = intval($fullData['id']);

        if (empty($ecommerceFilesData[$rid]) || !is_array($ecommerceFilesData[$rid])) {
            return $saleData;
        }

        $entryEcommerceData = $ecommerceFilesData[$rid];

        $saleData['isActive'] = true;
        $saleData['saleTitleText'] = (!empty($entryEcommerceData['SaleTitle'])) ? $entryEcommerceData['SaleTitle'] : '';
        $saleData['saleDescriptionText'] = (!empty($entryEcommerceData['SaleDescription'])) ? $entryEcommerceData['SaleDescription'] : '';
        $saleData['salePriceType'] = cg1l_frontend_get_ecommerce_sale_type_label($entryEcommerceData, $languageNames);

        $salePriceValue = (!empty($entryEcommerceData['Price'])) ? floatval($entryEcommerceData['Price']) : 0;
        $priceDivider = (!empty($ecommerceOptions['PriceDivider'])) ? $ecommerceOptions['PriceDivider'] : '.';
        $currencyPosition = (!empty($ecommerceOptions['CurrencyPosition'])) ? $ecommerceOptions['CurrencyPosition'] : 'left';
        $currencyShort = (!empty($ecommerceOptions['CurrencyShort'])) ? $ecommerceOptions['CurrencyShort'] : '';
        $currencySymbol = '';

        if ($currencyShort === '%') {
            $currencySymbol = '%';
        } elseif (!empty($currenciesArray[$currencyShort])) {
            $currencySymbol = $currenciesArray[$currencyShort];
        } elseif (!empty($currencyShort)) {
            $currencySymbol = $currencyShort;
        }

        $saleData['salePriceText'] = cg_ecommerce_price_to_show(
            $currenciesArray,
            $currencyShort,
            $currencyPosition,
            $priceDivider,
            $salePriceValue
        );

        $salePriceNormalized = number_format($salePriceValue, 2, '.', '');
        $salePriceParts = explode('.', $salePriceNormalized);
        $thousandsSeparator = ($priceDivider === ',') ? '.' : ',';
        $saleData['salePriceLeft'] = number_format(floatval($salePriceParts[0]), 0, '', $thousandsSeparator);
        $saleData['salePriceRight'] = (!empty($salePriceParts[1])) ? $salePriceParts[1] : '00';

        if ($currencyPosition === 'left') {
            $saleData['salePriceLeftSymbol'] = $currencySymbol;
        } else {
            $saleData['salePriceRightSymbol'] = $currencySymbol;
        }

        return $saleData;
    }
}

if (!function_exists('cg1l_frontend_get_ecommerce_gallery_price_markup')) {
    function cg1l_frontend_get_ecommerce_gallery_price_markup($saleData)
    {
        if (empty($saleData['isActive'])) {
            return '';
        }

        return '<div class="cg-center-sale-price-container"><div class="cg-center-sale-price">' .
            '<div class="cg-center-sale-price-value">' . esc_html($saleData['salePriceText']) . '</div>' .
            '<div class="cg-center-sale-price-type">' . esc_html($saleData['salePriceType']) . '</div>' .
            '</div></div>';
    }
}


if (!function_exists('cg1l_frontend_get_form_upload_field')) {
    function cg1l_frontend_get_form_upload_field($formUploadFullData, $fieldId)
    {
        $fieldId = absint($fieldId);

        if(empty($fieldId) || !is_array($formUploadFullData)){
            return null;
        }

        if(!empty($formUploadFullData[$fieldId]) && is_array($formUploadFullData[$fieldId])){
            return $formUploadFullData[$fieldId];
        }

        $fieldId = (string)$fieldId;

        if(!empty($formUploadFullData[$fieldId]) && is_array($formUploadFullData[$fieldId])){
            return $formUploadFullData[$fieldId];
        }

        return null;
    }
}

if (!function_exists('cg1l_frontend_is_upload_field_category')) {
    function cg1l_frontend_is_upload_field_category($formUploadFullData, $fieldId)
    {
        $field = cg1l_frontend_get_form_upload_field($formUploadFullData, $fieldId);

        return (!empty($field['Field_Type']) && $field['Field_Type'] === 'selectc-f');
    }
}

if (!function_exists('cg1l_frontend_get_gallery_category_label')) {
    function cg1l_frontend_get_gallery_category_label($fullData, $categoriesFullData, $languageNames)
    {
        if(!empty($fullData['Category']) && !empty($categoriesFullData[$fullData['Category']]['Name'])){
            return contest_gal1ery_convert_for_html_output_without_nl2br($categoriesFullData[$fullData['Category']]['Name']);
        }

        return contest_gal1ery_convert_for_html_output_without_nl2br($languageNames['gallery']['Other']);
    }
}

if (!function_exists('cg1l_frontend_normalize_media_description_text')) {
    function cg1l_frontend_normalize_media_description_text($content)
    {
        $content = contest_gal1ery_convert_for_html_output_without_nl2br($content);
        $content = trim(str_replace("\xc2\xa0", ' ', wp_strip_all_tags((string)$content)));

        return $content;
    }
}

if (!function_exists('cg1l_frontend_get_gallery_media_description')) {
    function cg1l_frontend_get_gallery_media_description($fullData, $formUploadFullData, $categoriesFullData, $languageNames, $options, $jsonInfoData)
    {
        $id = (!empty($fullData['id'])) ? absint($fullData['id']) : 0;
        $Field2IdGalleryView = (!empty($options['visual']['Field2IdGalleryView'])) ? absint($options['visual']['Field2IdGalleryView']) : 0;

        if (!empty($fullData['TitleAttrGalleriesView'])) {
            return cg1l_frontend_normalize_media_description_text($fullData['TitleAttrGalleriesView']);
        }

        if (empty($Field2IdGalleryView)) {
            return '';
        }

        if (cg1l_frontend_is_upload_field_category($formUploadFullData, $Field2IdGalleryView)) {
            return cg1l_frontend_normalize_media_description_text(cg1l_frontend_get_gallery_category_label($fullData, $categoriesFullData, $languageNames));
        }

        if (
            !empty($id) &&
            !empty($jsonInfoData[$id]) &&
            is_array($jsonInfoData[$id]) &&
            isset($jsonInfoData[$id][$Field2IdGalleryView]) &&
            is_array($jsonInfoData[$id][$Field2IdGalleryView])
        ) {
            if (array_key_exists('field-content', $jsonInfoData[$id][$Field2IdGalleryView])) {
                return cg1l_frontend_normalize_media_description_text($jsonInfoData[$id][$Field2IdGalleryView]['field-content']);
            }

            return '';
        }

        return cg1l_frontend_normalize_media_description_text(cg1l_frontend_get_gallery_category_label($fullData, $categoriesFullData, $languageNames));
    }
}

if (!function_exists('cg1l_frontend_get_gallery_watermark_data')) {
    function cg1l_frontend_get_gallery_watermark_data($fullData, $formUploadFullData, $categoriesFullData, $languageNames, $options, $jsonInfoData)
    {
        $watermarkPosition = (!empty($options['visual']['WatermarkPosition'])) ? trim((string)$options['visual']['WatermarkPosition']) : '';

        if($watermarkPosition === '' || empty($fullData['id'])){
            return [
                'content' => '',
                'position' => '',
                'class' => '',
                'markup' => '',
            ];
        }

        $id = absint($fullData['id']);
        $watermarkContent = '';

        if(!empty($jsonInfoData[$id]) && is_array($jsonInfoData[$id])){
            foreach ($jsonInfoData[$id] as $uploadFieldId => $fieldData){
                if(!is_array($fieldData)){
                    continue;
                }

                $field = cg1l_frontend_get_form_upload_field($formUploadFullData, $uploadFieldId);

                if(empty($field) || empty($field['WatermarkPosition'])){
                    continue;
                }

                if(!empty($fieldData['field-content'])){
                    $watermarkContent = contest_gal1ery_convert_for_html_output_without_nl2br($fieldData['field-content']);
                    break;
                }
            }
        }

        if($watermarkContent === '' && !empty($formUploadFullData) && is_array($formUploadFullData)){
            foreach ($formUploadFullData as $field){
                if(!is_array($field)){
                    continue;
                }

                if(empty($field['WatermarkPosition']) || empty($field['Field_Type']) || $field['Field_Type'] !== 'selectc-f'){
                    continue;
                }

                $watermarkContent = cg1l_frontend_get_gallery_category_label($fullData, $categoriesFullData, $languageNames);
                break;
            }
        }

        if($watermarkContent === ''){
            return [
                'content' => '',
                'position' => '',
                'class' => '',
                'markup' => '',
            ];
        }

        $watermarkClass = 'cg_watermark cg_watermark-'.$watermarkPosition;

        return [
            'content' => $watermarkContent,
            'position' => $watermarkPosition,
            'class' => $watermarkClass,
            'markup' => '<div class="'.esc_attr($watermarkClass).'">'.$watermarkContent.'</div>',
        ];
    }
}

if (!function_exists('cg1l_frontend_normalize_single_view_order_data')) {
    function cg1l_frontend_normalize_single_view_order_data($singleViewOrderFullData, $formUploadFullData)
    {
        if(!is_array($singleViewOrderFullData)){
            return [];
        }

        if(!is_array($formUploadFullData) || empty($formUploadFullData)){
            return $singleViewOrderFullData;
        }

        $normalizedSingleViewOrderFullData = [];

        foreach($singleViewOrderFullData as $data){
            if(empty($data['id'])){
                continue;
            }

            $field = cg1l_frontend_get_form_upload_field($formUploadFullData, $data['id']);

            if(empty($field) || empty($field['Show_Slider'])){
                continue;
            }

            $normalizedSingleViewOrderFullData[] = $data;
        }

        return $normalizedSingleViewOrderFullData;
    }
}

if (!function_exists('cg1l_frontend_get_con_entry_preview_content')) {
    function cg1l_frontend_get_con_entry_preview_content($fullData,$formUploadFullData,$categoriesFullData,$languageNames,$options,$jsonInfoData)
    {
        $ImgType = (!empty($fullData['ImgType'])) ? strtolower((string)$fullData['ImgType']) : '';
        $id = (!empty($fullData['id'])) ? absint($fullData['id']) : 0;
        $fieldId = (!empty($options['visual']['Field1IdGalleryView'])) ? absint($options['visual']['Field1IdGalleryView']) : 0;
        $fieldContent = '';
        $fieldContentCheck = '';

        if($ImgType !== 'con' || empty($id) || empty($fieldId)){
            return '';
        }

        if(cg1l_frontend_is_upload_field_category($formUploadFullData, $fieldId)){
            return cg1l_frontend_get_gallery_category_label($fullData, $categoriesFullData, $languageNames);
        }

        if(isset($jsonInfoData[$id][$fieldId]['field-content']) && $jsonInfoData[$id][$fieldId]['field-content'] !== ''){
            $fieldContent = $jsonInfoData[$id][$fieldId]['field-content'];
        }elseif(isset($jsonInfoData[(string)$id][$fieldId]['field-content']) && $jsonInfoData[(string)$id][$fieldId]['field-content'] !== ''){
            $fieldContent = $jsonInfoData[(string)$id][$fieldId]['field-content'];
        }elseif(isset($fullData['MainTitleGalleriesView']) && $fullData['MainTitleGalleriesView'] !== ''){
            $fieldContent = $fullData['MainTitleGalleriesView'];
        }

        if($fieldContent === ''){
            return '';
        }

        $fieldContent = contest_gal1ery_convert_for_html_output_without_nl2br($fieldContent);
        $fieldContentCheck = trim(str_replace("\xc2\xa0", ' ', wp_strip_all_tags(html_entity_decode((string)$fieldContent, ENT_QUOTES, 'UTF-8'))));

        if($fieldContentCheck === ''){
            return '';
        }

        return $fieldContent;
    }
}

if (!function_exists('cg1l_frontend_get_con_entry_preview_markup')) {
    function cg1l_frontend_get_con_entry_preview_markup($content,$context = 'gallery')
    {
        $contentCheck = trim(str_replace("\xc2\xa0", ' ', wp_strip_all_tags(html_entity_decode((string)$content, ENT_QUOTES, 'UTF-8'))));

        if($contentCheck === ''){
            return '';
        }

        $context = sanitize_html_class($context);
        if($context === ''){
            $context = 'gallery';
        }

        return '<div class="cg-con-entry-preview cg-con-entry-preview-'.$context.'"><div class="cg-con-entry-preview-content">'.$content.'</div></div>';
    }
}

if (!function_exists('cg1l_frontend_get_info_data_gallery')) {
    function cg1l_frontend_get_info_data_gallery($fullData,$formUploadFullData,$categoriesFullData,$languageNames,$options,$jsonInfoData)
    {
        $id = $fullData['id'];
        $MainTitleId = (!empty($options['visual']['Field1IdGalleryView'])) ? absint($options['visual']['Field1IdGalleryView']) : 0;
        $SubTitleId= (!empty($options['visual']['SubTitle'])) ? absint($options['visual']['SubTitle']) : 0;
        $ThirdTitleId= (!empty($options['visual']['ThirdTitle'])) ? absint($options['visual']['ThirdTitle']) : 0;
        $Field1IdGalleryView = (!empty($options['visual']['Field1IdGalleryView'])) ? absint($options['visual']['Field1IdGalleryView']) : 0;
        $Field2IdGalleryView = (!empty($options['visual']['Field2IdGalleryView'])) ? absint($options['visual']['Field2IdGalleryView']) : 0;
        $Category = (!empty($fullData['Category'])) ? absint($fullData['Category']) : 0;

        $mainTitleIsCategory = cg1l_frontend_is_upload_field_category($formUploadFullData, $MainTitleId);
        $SubTitleIdIsCategory = cg1l_frontend_is_upload_field_category($formUploadFullData, $SubTitleId);
        $ThirdTitleIdIsCategory = cg1l_frontend_is_upload_field_category($formUploadFullData, $ThirdTitleId);

        // --- Main Title Logic ---
        if ($mainTitleIsCategory) {
            $mainTitlePure = cg1l_frontend_get_gallery_category_label($fullData, $categoriesFullData, $languageNames);
            $mainTitle = '<p class="cg_gallery_info_content cg_gallery_info_content_main">' . $mainTitlePure . '</p>';
        } else {
            if (!empty($fullData['MainTitleGalleriesView'])) {
                $mainTitlePure = contest_gal1ery_convert_for_html_output_without_nl2br($fullData['MainTitleGalleriesView']);
                $mainTitle = '<p class="cg_gallery_info_content cg_gallery_info_content_main">' . $mainTitlePure . '</p>';
            } elseif (!empty($MainTitleId) && !empty($jsonInfoData[$id][$MainTitleId]['field-content'])) {
                $mainTitlePure = contest_gal1ery_convert_for_html_output_without_nl2br($jsonInfoData[$id][$MainTitleId]['field-content']);
                $mainTitle = '<p class="cg_gallery_info_content cg_gallery_info_content_main" itemprop="name">' . $mainTitlePure . '</p>';
            } else {
                $mainTitlePure = '';
                $mainTitle = '';
            }
        }

// --- Sub Title Logic ---
        if ($SubTitleIdIsCategory) {
            $subTitlePure = cg1l_frontend_get_gallery_category_label($fullData, $categoriesFullData, $languageNames);
            $subTitle = '<p class="cg_gallery_info_content cg_gallery_info_content_sub">' . $subTitlePure . '</p>';
        } else {
            if (!empty($fullData['SubTitleGalleriesView'])) {
                $subTitlePure = contest_gal1ery_convert_for_html_output_without_nl2br($fullData['SubTitleGalleriesView']);
                $subTitle = '<p class="cg_gallery_info_content cg_gallery_info_content_sub">' . $subTitlePure . '</p>';
            } elseif (!empty($SubTitleId) && !empty($jsonInfoData[$id][$SubTitleId]['field-content'])) {
                $subTitlePure = contest_gal1ery_convert_for_html_output_without_nl2br($jsonInfoData[$id][$SubTitleId]['field-content']);
                $subTitle = '<p class="cg_gallery_info_content cg_gallery_info_content_sub"  itemprop="headline">' . $subTitlePure . '</p>';
            } else {
                $subTitlePure = '';
                $subTitle = '';
            }
        }

// --- Third Title Logic ---
        if ($ThirdTitleIdIsCategory) {
            $thirdTitlePure = cg1l_frontend_get_gallery_category_label($fullData, $categoriesFullData, $languageNames);
            $thirdTitle = '<p class="cg_gallery_info_content cg_gallery_info_content_third">' . $thirdTitlePure . '</p>';
        } else {
            if (!empty($fullData['ThirdTitleGalleriesView'])) {
                $thirdTitlePure = contest_gal1ery_convert_for_html_output_without_nl2br($fullData['ThirdTitleGalleriesView']);
                $thirdTitle = '<p class="cg_gallery_info_content cg_gallery_info_content_third">' . $thirdTitlePure . '</p>';
            } elseif (!empty($ThirdTitleId) && !empty($jsonInfoData[$id][$ThirdTitleId]['field-content'])) {
                $thirdTitlePure = contest_gal1ery_convert_for_html_output_without_nl2br($jsonInfoData[$id][$ThirdTitleId]['field-content']);
                $thirdTitle = '<p class="cg_gallery_info_content cg_gallery_info_content_third"  itemprop="description">' . $thirdTitlePure . '</p>';
            } else {
                $thirdTitlePure = '';
                $thirdTitle = '';
            }
        }

        // --- Title Attr Logic (No Category) ---
        if (!empty($fullData['TitleAttrGalleriesView'])) {
            $titleAttrPure = contest_gal1ery_convert_for_html_output_without_nl2br($fullData['TitleAttrGalleriesView']);
            $titleAttr = '<p class="cg_gallery_info_content cg_gallery_info_content_third">' . $titleAttrPure . '</p>';
        } elseif (!empty($Field2IdGalleryView) && !empty($jsonInfoData[$id][$Field2IdGalleryView]['field-content'])) {
            $titleAttrPure = contest_gal1ery_convert_for_html_output_without_nl2br($jsonInfoData[$id][$Field2IdGalleryView]['field-content']);
            $titleAttr = '<p class="cg_gallery_info_content cg_gallery_info_content_third">' . $titleAttrPure . '</p>';
        } else {
            $titleAttrPure = '';
            $titleAttr = '';
        }

        $mediaDescription = cg1l_frontend_get_gallery_media_description($fullData, $formUploadFullData, $categoriesFullData, $languageNames, $options, $jsonInfoData);
        $altAttr = '';
        if($mediaDescription !== ''){$altAttr = $mediaDescription;}
        elseif($mainTitle){$altAttr = $mainTitlePure;}
        elseif($subTitle){$altAttr = $subTitlePure;}
        elseif($thirdTitle){$altAttr = $thirdTitlePure;}
        elseif($titleAttr){$altAttr = $titleAttrPure;}
        $watermarkData = cg1l_frontend_get_gallery_watermark_data($fullData, $formUploadFullData, $categoriesFullData, $languageNames, $options, $jsonInfoData);
        $conEntryPreviewContent = cg1l_frontend_get_con_entry_preview_content($fullData,$formUploadFullData,$categoriesFullData,$languageNames,$options,$jsonInfoData);
        return ['altAttr' => $altAttr, 'mediaDescription' => $mediaDescription, 'mainTitle' => $mainTitle,'subTitle' => $subTitle, 'thirdTitle' => $thirdTitle, 'titleAttr' => $titleAttr, 'Category' => $Category, 'watermarkContent' => $watermarkData['content'], 'watermarkPosition' => $watermarkData['position'], 'watermarkClass' => $watermarkData['class'], 'watermarkMarkup' => $watermarkData['markup'], 'conEntryPreviewContent' => $conEntryPreviewContent];
    }
}

if (!function_exists('cg1l_frontend_get_info_data_single_view')) {
    function cg1l_frontend_get_info_data_single_view($fullData,$gid,$jsonInfoData,$singleViewOrderFullData,$categoriesFullData,$languageNames)
    {
        $id = $fullData['id'];
        $infoContent = '';
        $ariaDescribedby = '';
        $i = 0;
        foreach ($singleViewOrderFullData as $order => $data){
            if(!empty($jsonInfoData[$fullData['id']][$data['id']]) || $data['Field_Type'] == 'selectc-f'){
                $fieldTitle = contest_gal1ery_convert_for_html_output_without_nl2br($data['Field_Content']);
                $fieldContentRaw = '';
                if($data['Field_Type'] == 'selectc-f'){
                    $fieldContent = ($fullData['Category']=='0') ? $languageNames['gallery']['Other'] : contest_gal1ery_convert_for_html_output_without_nl2br($categoriesFullData[$fullData['Category']]['Name']);
                }else{
                    $fieldContentRaw = (string)$jsonInfoData[$fullData['id']][$data['id']]['field-content'];
                    $fieldContent = contest_gal1ery_convert_for_html_output_without_nl2br($fieldContentRaw);
                }

                $fieldTitleCheck = trim(wp_strip_all_tags(html_entity_decode((string)$fieldTitle, ENT_QUOTES, 'UTF-8')));
                $fieldContentCheck = trim(str_replace("\xc2\xa0", ' ', wp_strip_all_tags(html_entity_decode((string)$fieldContent, ENT_QUOTES, 'UTF-8'))));

                if($fieldTitleCheck === '' || $fieldContentCheck === ''){
                    continue;
                }

                if($data['Field_Type'] === 'url-f'){
                    $fieldHref = trim(wp_strip_all_tags(html_entity_decode((string)$fieldContentRaw, ENT_QUOTES, 'UTF-8')));
                    if($fieldHref !== '' && !preg_match('/^https?:\/\//i', $fieldHref)){
                        $fieldHref = 'https://'.ltrim($fieldHref, '/');
                    }
                    $fieldHref = esc_url_raw($fieldHref);
                    if($fieldHref === ''){
                        continue;
                    }
                }

                $infoId = 'cglCaption'.$gid.$id.$data['id'];
                if($i==0){
                    $ariaDescribedby = $infoId;
                }else{
                    $ariaDescribedby .= ' '.$infoId;
                }
                $infoContent .= '<div class="cg-center-image-info-div" data-cg-single-view-order="'.$order.'">';
                if($data['Field_Type'] === 'url-f'){
                    $infoContent .= '<p><a id="'.$infoId.'" class="cg-center-image-info-link-button" href="'.esc_url($fieldHref).'" target="_blank" rel="noopener noreferrer">'.esc_html($fieldTitleCheck).'</a></p>';
                }else{
                    $infoContent .= '<p>'.$fieldTitle.':</p>';
                    $infoContent .= '<p id="'.$infoId.'"  class="cg-center-image-info-div-content">'.$fieldContent.'</p>';
                }
                $infoContent .='</div>';
                $i++;
            }
        }

        return [
            'infoContent' => $infoContent,
            'ariaDescribedby' => $ariaDescribedby,
        ];
    }
}

if (!function_exists('cg1l_frontend_get_preferred_wp_user_avatar_url')) {
    function cg1l_frontend_get_preferred_wp_user_avatar_url($WpUserId, $WpUserIdsData = [])
    {
        $WpUserId = absint($WpUserId);

        if(empty($WpUserId) || empty($WpUserIdsData) || !is_array($WpUserIdsData)){
            return '';
        }

        if(!empty($WpUserIdsData['wpAvatarImagesArray'][$WpUserId])){
            return esc_url_raw($WpUserIdsData['wpAvatarImagesArray'][$WpUserId]);
        }

        if(!empty($WpUserIdsData['profileImagesArray'][$WpUserId])){
            return esc_url_raw($WpUserIdsData['profileImagesArray'][$WpUserId]);
        }

        return '';
    }
}

if (!function_exists('cg1l_frontend_is_gravatar_url')) {
    function cg1l_frontend_is_gravatar_url($avatarUrl)
    {
        $host = wp_parse_url((string) $avatarUrl, PHP_URL_HOST);

        if (empty($host)) {
            return false;
        }

        return strpos(strtolower((string) $host), 'gravatar.com') !== false;
    }
}

if (!function_exists('cg1l_frontend_get_gravatar_validation_url')) {
    function cg1l_frontend_get_gravatar_validation_url($avatarUrl, $size = 96)
    {
        $parsedUrl = wp_parse_url((string) $avatarUrl);
        $queryArgs = [];
        $baseUrl = '';

        if (empty($parsedUrl['host']) || empty($parsedUrl['path'])) {
            return '';
        }

        if (!empty($parsedUrl['query'])) {
            wp_parse_str($parsedUrl['query'], $queryArgs);
        }

        $queryArgs['d'] = '404';
        $queryArgs['s'] = absint($size) ?: 96;

        $baseUrl .= !empty($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : 'https://';
        $baseUrl .= $parsedUrl['host'];

        if (!empty($parsedUrl['port'])) {
            $baseUrl .= ':' . absint($parsedUrl['port']);
        }

        $baseUrl .= $parsedUrl['path'];

        return esc_url_raw(add_query_arg($queryArgs, $baseUrl));
    }
}

if (!function_exists('cg1l_frontend_has_real_gravatar_image')) {
    function cg1l_frontend_has_real_gravatar_image($avatarUrl)
    {
        $validationUrl = cg1l_frontend_get_gravatar_validation_url($avatarUrl, 96);
        $cacheKey = '';
        $cachedResult = false;
        $response = null;
        $statusCode = 0;

        if (empty($validationUrl)) {
            return true;
        }

        $cacheKey = 'cg1l_gravatar_exists_' . md5(strtolower(trim((string) $validationUrl)));
        $cachedResult = get_transient($cacheKey);

        if ($cachedResult !== false) {
            return $cachedResult === '1';
        }

        $response = wp_remote_head($validationUrl, [
            'timeout' => 3,
            'redirection' => 3,
        ]);

        if (is_wp_error($response)) {
            $response = wp_remote_get($validationUrl, [
                'timeout' => 3,
                'redirection' => 3,
                'limit_response_size' => 1,
            ]);
        }

        if (is_wp_error($response)) {
            return true;
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);

        if ($statusCode === 404) {
            set_transient($cacheKey, '0', DAY_IN_SECONDS);
            return false;
        }

        if ($statusCode >= 200 && $statusCode < 400) {
            set_transient($cacheKey, '1', DAY_IN_SECONDS);
            return true;
        }

        return true;
    }
}

if (!function_exists('cg1l_frontend_get_real_wp_avatar_images')) {
    function cg1l_frontend_get_real_wp_avatar_images($WpUserId, $smallSize = 96, $largeSize = 400)
    {
        $WpUserId = absint($WpUserId);
        $smallAvatarData = [];
        $largeAvatarData = [];
        $smallUrl = '';
        $largeUrl = '';
        $isRealAvatar = false;

        if (empty($WpUserId)) {
            return [
                'small' => '',
                'large' => '',
            ];
        }

        $smallAvatarData = get_avatar_data($WpUserId, [
            'size' => absint($smallSize) ?: 96,
        ]);

        if (empty($smallAvatarData['url'])) {
            return [
                'small' => '',
                'large' => '',
            ];
        }

        $smallUrl = esc_url_raw($smallAvatarData['url']);

        if (empty($smallUrl)) {
            return [
                'small' => '',
                'large' => '',
            ];
        }

        if (cg1l_frontend_is_gravatar_url($smallUrl)) {
            $isRealAvatar = cg1l_frontend_has_real_gravatar_image($smallUrl);
        } else {
            $isRealAvatar = true;
        }

        if (!$isRealAvatar) {
            return [
                'small' => '',
                'large' => '',
            ];
        }

        $largeAvatarData = get_avatar_data($WpUserId, [
            'size' => absint($largeSize) ?: 400,
        ]);

        $largeUrl = !empty($largeAvatarData['url']) ? esc_url_raw($largeAvatarData['url']) : $smallUrl;

        if (empty($largeUrl)) {
            $largeUrl = $smallUrl;
        }

        return [
            'small' => $smallUrl,
            'large' => $largeUrl,
        ];
    }
}

if (!function_exists('cg1l_frontend_get_wp_users_data')) {
    function cg1l_frontend_get_wp_users_data($gid,$options)
    {
        $gid = (int) $gid;

        global $wpdb;
        $tablename         = $wpdb->prefix . 'contest_gal1ery';
        $tablenameComments = $wpdb->prefix . 'contest_gal1ery_comments';

        $data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, WpUserId
             FROM $tablename
             WHERE WpUserId > 0 AND GalleryID = %d",
                $gid
            )
        );

        $mainDataWpUserIds = [];
        $wpUserImageIdsArray = [];
        $commentsWpUserIdsArray = [];
        $commentUserIdsArray = [];
        $profileImagesArray = [];
        $wpAvatarImagesArray = [];
        $wpAvatarImagesLargeArray = [];
        $nicknamesArray = [];
        $entryUserIds = [];
        foreach ($data as $row) {
            $wpUserImageIdsArray[] = $row->id;
            $mainDataWpUserIds[$row->id] = $row->WpUserId;
            $entryUserIds[] = $row->WpUserId;
        }

        $entryUserIds = array_values(array_unique($entryUserIds));

        if($options['general']['AllowComments'] >= 1){
            $commentUserIds = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, WpUserId 
             FROM $tablenameComments
             WHERE WpUserId > 0 AND GalleryID = %d",
                    $gid
            ));
            foreach ($commentUserIds as $commentUserId) {
                $commentsWpUserIdsArray[$commentUserId->id] = $commentUserId->WpUserId;
                $commentUserIdsArray[] = $commentUserId->WpUserId;
            }
            $commentUserIds = array_values(array_unique($commentUserIdsArray));
        }else{
            $commentUserIds = [];
        }

        $allUserIds = array_merge($entryUserIds, $commentUserIds);
        $allUserIds = array_values(
            array_unique(
                array_filter($allUserIds)
            )
        );

        if(!empty($allUserIds)){
            $profileImageUserIds = array_map('absint', $allUserIds);
            $profileImagePlaceholders = implode(',', array_fill(0, count($profileImageUserIds), '%d'));
            $profileImages = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT WpUserId, WpUpload
                 FROM $tablename
                 WHERE IsProfileImage = %d AND WpUserId IN ($profileImagePlaceholders)",
                    array_merge([1], $profileImageUserIds)
                )
            );

            foreach ($profileImages as $profileImage) {
                $imgSrcLarge = wp_get_attachment_image_src((int) $profileImage->WpUpload, 'large');
                if (!empty($imgSrcLarge[0])) {
                    $profileImagesArray[(int) $profileImage->WpUserId] = $imgSrcLarge[0];
                }
            }

            // 3) Bulk fetch WP users in one shot
            $users = get_users([
                'include' => $allUserIds,
                'fields'  => ['ID', 'display_name'],
            ]);
            foreach ($users as $user) {
                $avatarImages = [];

                $nicknamesArray[$user->ID] = $user->display_name;

                $avatarImages = cg1l_frontend_get_real_wp_avatar_images($user->ID, 96, 400);

                if (!empty($avatarImages['small'])) {
                    $wpAvatarImagesArray[$user->ID] = $avatarImages['small'];
                    $wpAvatarImagesLargeArray[$user->ID] = !empty($avatarImages['large']) ? $avatarImages['large'] : $avatarImages['small'];
                }
            }
        }

        return [
            'wpUserImageIdsArray' => $wpUserImageIdsArray,
            'mainDataWpUserIds' => $mainDataWpUserIds,
            'nicknamesArray' => $nicknamesArray,
            'profileImagesArray' => $profileImagesArray,
            'wpAvatarImagesArray' => $wpAvatarImagesArray,
            'wpAvatarImagesLargeArray' => $wpAvatarImagesLargeArray,
            'commentsWpUserIdsArray' => $commentsWpUserIdsArray,
        ];
    }

}

if (!function_exists('cg1l_frontend_get_default_sorted_images_full_data')) {
    function cg1l_frontend_get_default_sorted_images_full_data($imagesFullData, $picsPerSite = 20)
    {
        if (!is_array($imagesFullData)) {
            $imagesFullData = [];
        }

        return cg1l_frontend_sort_by($imagesFullData, 'id', $picsPerSite, 'DESC');
    }
}

if (!function_exists('cg1l_frontend_normalize_preselect_sort')) {
    function cg1l_frontend_normalize_preselect_sort($preselectSort = '', $legacyRatingSort = '')
    {
        $preselectSort = (is_string($preselectSort)) ? trim($preselectSort) : '';
        $legacyRatingSort = (is_string($legacyRatingSort)) ? trim($legacyRatingSort) : '';

        $sortAliases = [
            'custom' => 'custom',
            'date_descend' => 'date_descend',
            'date-desc' => 'date_descend',
            'date_ascend' => 'date_ascend',
            'date-asc' => 'date_ascend',
            'comments_descend' => 'comments_descend',
            'comments-desc' => 'comments_descend',
            'comment-desc' => 'comments_descend',
            'comments_ascend' => 'comments_ascend',
            'comments-asc' => 'comments_ascend',
            'comment-asc' => 'comments_ascend',
            'rating_descend' => 'rating_descend',
            'rating-desc' => 'rating_descend',
            'rate-desc' => 'rating_descend',
            'rating_ascend' => 'rating_ascend',
            'rating-asc' => 'rating_ascend',
            'rate-asc' => 'rating_ascend',
            'rating_sum_descend' => 'rating_sum_descend',
            'rating-desc-sum' => 'rating_sum_descend',
            'rate-sum-desc' => 'rating_sum_descend',
            'rating_sum_ascend' => 'rating_sum_ascend',
            'rating-asc-sum' => 'rating_sum_ascend',
            'rate-sum-asc' => 'rating_sum_ascend',
            'random' => 'random'
        ];

        if (!empty($sortAliases[$preselectSort])) {
            return $sortAliases[$preselectSort];
        }

        if (!empty($sortAliases[$legacyRatingSort])) {
            return $sortAliases[$legacyRatingSort];
        }

        return '';
    }
}

if (!function_exists('cg1l_get_data_images_full_data_sorted')) {
    function cg1l_get_data_images_full_data_sorted($options,$imagesFullData,$isCGalleries = false,$isCGalleriesNoSorting = false){
        if (!is_array($imagesFullData)) {
            $imagesFullData = [];
        }

        $picsPerSite = !empty($options['general']['PicsPerSite']) ? absint($options['general']['PicsPerSite']) : count($imagesFullData);
        $preselectSort = '';
        $legacyRatingSort = '';
        $imagesFullDataArrays = cg1l_frontend_get_default_sorted_images_full_data($imagesFullData, $picsPerSite);

        if(!empty($options['general']['PreselectSort'])){
            $preselectSort = $options['general']['PreselectSort'];
        }elseif(!empty($options['pro']['PreselectSort'])){
            $preselectSort = $options['pro']['PreselectSort'];
        }

        if(!empty($options['general']['rating_sum_descend'])){
            $legacyRatingSort = $options['general']['rating_sum_descend'];
        }

        $preselectSort = cg1l_frontend_normalize_preselect_sort($preselectSort, $legacyRatingSort);

        if($isCGalleries){
            $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'gid',$picsPerSite,'DESC','',5,$isCGalleriesNoSorting);
        }elseif((!empty($options['general']['RandomSort']) && intval($options['general']['RandomSort']) === 1) || $preselectSort === 'random'){
            $imagesFullDataArrays = cg1l_frontend_sort_random($imagesFullData,$picsPerSite);
        }else if(!empty($preselectSort)){
            if($preselectSort=='custom'){
                $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'PositionNumber',$picsPerSite,'ASC');
            } elseif($preselectSort=='date_descend'){
                $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'id',$picsPerSite,'DESC');
            } elseif($preselectSort=='date_ascend'){
                $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'id',$picsPerSite,'ASC');
            }elseif($preselectSort=='comments_descend'){
                $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'CountC',$picsPerSite,'DESC');
            }elseif($preselectSort=='comments_ascend'){
                $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'CountC',$picsPerSite,'ASC');
            }elseif($preselectSort=='rating_descend' || $preselectSort=='rating_ascend' || $preselectSort=='rating_sum_descend' || $preselectSort=='rating_sum_ascend'){
                $sortDirection = (strpos($preselectSort, '_ascend') !== false) ? 'ASC' : 'DESC';
                $allowRating = !empty($options['general']['AllowRating']) ? intval($options['general']['AllowRating']) : 0;
                $isManipulationActive = (!empty($options['pro']['Manipulate']) && intval($options['pro']['Manipulate']) === 1);

                if($allowRating === 2){
                    if($isManipulationActive){
                        $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'CountS',$picsPerSite,$sortDirection,'CountSWithManipulation');
                    }else{
                        $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'CountS',$picsPerSite,$sortDirection);
                    }
                }elseif($allowRating > 10){
                    $AllowRatingMax = $allowRating-10;

                    if($preselectSort=='rating_descend' || $preselectSort=='rating_ascend'){
                        $specialSortType = ($isManipulationActive) ? 'CountRtotalWithManipulation' : 'CountRtotal';
                        $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'CountRtotal',$picsPerSite,$sortDirection,$specialSortType,$AllowRatingMax);
                    }else{
                        $specialSortType = ($isManipulationActive) ? 'CountRWithManipulation' : 'CountR';
                        $imagesFullDataArrays = cg1l_frontend_sort_by($imagesFullData,'CountR',$picsPerSite,$sortDirection,$specialSortType,$AllowRatingMax);
                    }
                }
            }
        }

        if(
            !is_array($imagesFullDataArrays) ||
            !isset($imagesFullDataArrays['imagesFullDataFull']) ||
            !is_array($imagesFullDataArrays['imagesFullDataFull']) ||
            !isset($imagesFullDataArrays['imagesFullDataSliced']) ||
            !is_array($imagesFullDataArrays['imagesFullDataSliced'])
        ){
            $imagesFullDataArrays = cg1l_frontend_get_default_sorted_images_full_data($imagesFullData, $picsPerSite);
        }

        return $imagesFullDataArrays;
    }
}

if (!function_exists('cg1l_get_consent_ytb')) {
    function cg1l_get_consent_ytb($galeryIDuserForJs,$id,$fullData,$languageNames){
        return '<div class="cg_append_ytb_overlay_consent cg_append_social_overlay_consent cg_append cg_append_social" data-ytb-src="'.$fullData['guid'].'" data-cg-gid="'.$galeryIDuserForJs.'" data-cg-real-id="'.$id.'" id="cg_append'.$id.'" ><span class="cg_ytb_logo cg_social_logo"></span><span>YouTube '.$languageNames['general']['contentBlocked'].'.</span><span><a href="https://policies.google.com/privacy" target="_blank" class="cg_extern_privacy_policy">'.$languageNames['general']['ToViewTheContentYouMustAgreePrivacyPolicy'].'</a>.</span><span class="cg_ytb_consent_agree cg_social_consent_agree" data-cg-gid="'.$galeryIDuserForJs.'" data-cg-real-id="'.$id.'">'.$languageNames['general']['IAgree'].'</span><span>'.$languageNames['general']['ByAgreeingAllContentUnblocked'].' (YouTube '.$languageNames['general']['content'].'). </span></div>';
    }
}

if (!function_exists('cg1l_get_consent_inst')) {
    function cg1l_get_consent_inst($galeryIDuserForJs,$id,$fullData,$languageNames){
        return '<div class="cg_append_inst_overlay_consent cg_append_social_overlay_consent cg_append cg_append_social cg_append_inst" data-inst-src="'.$fullData['guid'].'" data-cg-gid="'.$galeryIDuserForJs.'" data-cg-real-id="'.$id.'" id="cg_append'.$id.'"><span class="cg_inst_logo cg_social_logo"></span><span>Instagram '.$languageNames['general']['contentBlocked'].'.</span><span><a href="https://privacycenter.instagram.com/policy" target="_blank" class="cg_extern_privacy_policy">'.$languageNames['general']['ToViewTheContentYouMustAgreePrivacyPolicy'].' (Instagram)</a>.</span><span class="cg_inst_consent_agree cg_social_consent_agree" data-cg-gid="'.$galeryIDuserForJs.'" data-cg-real-id="'.$id.'">'.$languageNames['general']['IAgree'].'</span><span>'.$languageNames['general']['ByAgreeingAllContentUnblocked'].' (Instagram '.$languageNames['general']['content'].'). </span></div>';
    }
}

if (!function_exists('cg1l_get_consent_tkt')) {
    function cg1l_get_consent_tkt($galeryIDuserForJs,$id,$fullData,$languageNames){
        return '<div class="cg_append_tkt_overlay_consent cg_append_social_overlay_consent cg_append cg_append_tkt cg_append_social" data-tkt-src="'.$fullData['guid'].'" data-cg-gid="'.$galeryIDuserForJs.'" data-cg-real-id="'.$id.'" id="cg_append'.$id.'" ><span class="cg_tkt_logo cg_social_logo"></span><span>TikTok '.$languageNames['general']['contentBlocked'].'.</span><span><a href="https://www.tiktok.com/legal/privacy-policy-row" target="_blank" class="cg_extern_privacy_policy">'.$languageNames['general']['ToViewTheContentYouMustAgreePrivacyPolicy'].' (TikTok)</a>.</span><span class="cg_tkt_consent_agree cg_social_consent_agree" data-cg-gid="'.$galeryIDuserForJs.'" data-cg-real-id="'.$id.'">I Agree</span><span>'.$languageNames['general']['ByAgreeingAllContentUnblocked'].' (TikTok '.$languageNames['general']['content'].'). </span></div>';
    }
}

if (!function_exists('cg1l_get_consent_twt')) {
    function cg1l_get_consent_twt($galeryIDuserForJs,$id,$fullData,$languageNames){
        return '<div class="cg_append_twt_overlay_consent cg_append_social_overlay_consent cg_append cg_append_twt cg_append_social" data-twt-src="'.$fullData['guid'].'" data-cg-gid="'.$galeryIDuserForJs.'" data-cg-real-id="'.$id.'" id="cg_append'.$id.'" ><span class="cg_twt_logo cg_social_logo"></span><span>X '.$languageNames['general']['contentBlocked'].'.</span><span><a href="https://x.com/privacy" target="_blank" class="cg_extern_privacy_policy">'.$languageNames['general']['ToViewTheContentYouMustAgreePrivacyPolicy'].' (X)</a>.</span><span class="cg_twt_consent_agree cg_social_consent_agree" data-cg-gid="'.$galeryIDuserForJs.'" data-cg-real-id="'.$id.'">I Agree</span><span>'.$languageNames['general']['ByAgreeingAllContentUnblocked'].' (X '.$languageNames['general']['content'].').</span></div>';
    }
}

if (!function_exists('cg1l_get_slider_popover_fallback_basename')) {
    function cg1l_get_slider_popover_fallback_basename($fullData){
        static $mediumSizeW = null;
        static $mediumSizeH = null;
        static $popoverThumbWidth = 250;

        if(empty($fullData['ImgType']) || !cg_is_is_image($fullData['ImgType'])){
            return '';
        }

        if(empty($fullData['large']) || empty($fullData['Width']) || empty($fullData['Height'])){
            return '';
        }

        $originalWidth = absint($fullData['Width']);
        $originalHeight = absint($fullData['Height']);

        if(empty($originalWidth) || empty($originalHeight)){
            return '';
        }

        if($mediumSizeW === null){
            $mediumSizeW = absint(get_option('medium_size_w'));
            $mediumSizeH = absint(get_option('medium_size_h'));
        }

        if(empty($mediumSizeW) && empty($mediumSizeH)){
            return '';
        }

        $scale = 1;

        if(!empty($mediumSizeW) && $originalWidth > $mediumSizeW){
            $scale = min($scale, ($mediumSizeW / $originalWidth));
        }

        if(!empty($mediumSizeH) && $originalHeight > $mediumSizeH){
            $scale = min($scale, ($mediumSizeH / $originalHeight));
        }

        $constrainedWidth = (int) floor($originalWidth * $scale);

        if($constrainedWidth >= $popoverThumbWidth){
            return '';
        }

        $largePath = wp_parse_url($fullData['large'], PHP_URL_PATH);

        if(empty($largePath)){
            return '';
        }

        return wp_basename($largePath);
    }
}

if (!function_exists('cg1l_set_slider_data')) {
    function cg1l_set_slider_data($fullData,&$dataSlider,$options,$countUserVotes,$downloadSaleEntryIdsLookup = []){
        $Width = '';
        $Height = '';
        $id = (!empty($fullData['id'])) ? intval($fullData['id']) : 0;
        if(empty($id)){
            return $dataSlider;
        }
        $imgType = (isset($fullData['ImgType'])) ? $fullData['ImgType'] : '';
        $guid = (isset($fullData['guid'])) ? $fullData['guid'] : '';
        $medium = (isset($fullData['medium'])) ? $fullData['medium'] : '';
        $full = (isset($fullData['full'])) ? $fullData['full'] : '';
        $postContent = (isset($fullData['post_content'])) ? $fullData['post_content'] : '';
        $countC = (isset($fullData['CountC'])) ? $fullData['CountC'] : 0;
        $namePic = (isset($fullData['NamePic'])) ? $fullData['NamePic'] : '';
        $thumbSource = '';
        $sliderPopoverFallbackBasename = '';
        if(!isset($fullData['CountS'])){
            $fullData['CountS'] = 0;
        }
        if(!isset($fullData['addCountS'])){
            $fullData['addCountS'] = 0;
        }
        if(cg_is_is_image($imgType)){
            $isDownloadSaleEntry = !empty($downloadSaleEntryIdsLookup[$id]);
            if($isDownloadSaleEntry){
                if(!empty($full)){
                    $thumbSource = $full;
                }elseif(!empty($guid)){
                    $thumbSource = $guid;
                }else{
                    $thumbSource = $medium;
                }
            }else{
                $thumbSource = (!empty($medium)) ? $medium : $guid;
            }
            $sliderPopoverFallbackBasename = cg1l_get_slider_popover_fallback_basename($fullData);
        }elseif($imgType == 'twt' || $imgType == 'tkt'){
            $thumbSource = $postContent;
        }elseif(cg_is_alternative_file_type_video($imgType)){
            $Width = (isset($fullData['Width'])) ? $fullData['Width'] : '';
            $Height = (isset($fullData['Height'])) ? $fullData['Height'] : '';
            $thumbSource = $guid;
        }else{
            $thumbSource = $guid;
        }
        $dataSlider[$id] = [$thumbSource,cg1l_get_rating_count($fullData,$options),$countUserVotes,$countC,$Width,$Height,$imgType,$namePic,$sliderPopoverFallbackBasename];
        return $dataSlider;
    }
}

if (!function_exists('cg1l_get_winners_data_only')) {
    function cg1l_get_winners_data_only($imagesFullData){
        $imagesFullDataNew = [];
        foreach ($imagesFullData as $key => $fullData){
            if(isset($fullData['Winner']) && $fullData['Winner'] == 1){
                $imagesFullDataNew = $imagesFullData[$key];
            }
        }
        return $imagesFullDataNew;
    }
}

if (!function_exists('cg1l_get_stats_for_galleries_shortcode')) {
    function cg1l_get_stats_for_galleries_shortcode($wp_upload_dir,$galleryIdToCheck,$imageId,$jsonFileData){
        if(is_dir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-stats')){
            $statsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-stats/image-stats-'.$imageId.'.json';
            if(file_exists($statsFile)){
                $statsFileData = json_decode(file_get_contents($statsFile),true);
                $jsonFileData = array_merge($jsonFileData, $statsFileData);
            }
        }
        return $jsonFileData;
    }
}

if (!function_exists('cg1l_get_images_full_data_frontend_for_galleries')) {
    function cg1l_get_images_full_data_frontend_for_galleries($wp_upload_dir,$shortcode_name,$is_user_logged_in,$language_NoGalleryEntries,$WpPageParentShortCodeType,$loadedRealGalleriesIds = [],$isGalleriesMainPage = false,$galleriesIds = [],$hasGalleriesIds = false,$WpUserId = 0){

        global $wpdb;

        $tablename = $wpdb->prefix . "contest_gal1ery";

        $imagesFullData = [];
        $galleryNumbers = [];
        $hasOlderVersionsOnMainCGalleriesPage = false;
        $loadedRealGalleriesIds = cg1l_normalize_positive_int_id_list($loadedRealGalleriesIds);
        $galleriesIds = cg1l_normalize_positive_int_id_list($galleriesIds);
        $hasGalleriesIds = (!empty($hasGalleriesIds) && !empty($galleriesIds));
        $WpUserId = absint($WpUserId);
        $isOnlyGalleryUser = ($shortcode_name == 'cg_gallery_user');

        if($isOnlyGalleryUser && (!$is_user_logged_in || empty($WpUserId))){
            return [
                'imagesFullData' => [],
                'hasOlderVersionsOnMainCGalleriesPage' => false
            ];
        }

        $loadedRealGalleriesIdsLookup = [];
        $useCanonicalGalleryNumbers = !$hasGalleriesIds;
        $shouldFilterLoadedRealGalleriesIds = (!$useCanonicalGalleryNumbers && empty($isGalleriesMainPage));
        foreach($loadedRealGalleriesIds as $loadedRealGalleryId){
            $loadedRealGalleriesIdsLookup[$loadedRealGalleryId] = true;
        }

        $galleriesIdsLookup = [];
        foreach($galleriesIds as $galleriesId){
            $galleriesIdsLookup[$galleriesId] = true;
        }

        // have to be checked already here... if no galleries then message
        $galleriesOptions = cg_galleries_options($shortcode_name);
        $dirs = array_filter(glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-*'), 'is_dir');
        foreach ($dirs as $dir) {
            $galleryIdToCheck = absint(substr($dir,strrpos($dir,'-')+1, strlen($dir)));
            if(
                empty($galleryIdToCheck) ||
                (
                    $shouldFilterLoadedRealGalleriesIds &&
                    !empty($loadedRealGalleriesIdsLookup[$galleryIdToCheck])
                )
            ){
                continue;
            }
            if($hasGalleriesIds && empty($galleriesIdsLookup[$galleryIdToCheck])){
                continue;
            }
            $optionsFileToCheck = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/'.$galleryIdToCheck.'-options.json';
            $isOlderVersion = false;
            $hasNoVersion = false;
            if($galleryIdToCheck!='0' && file_exists($optionsFileToCheck)){
                if($useCanonicalGalleryNumbers){
                    $optionsFullDataToCheck = json_decode(file_get_contents($optionsFileToCheck),true);
                    if(!empty($optionsFullDataToCheck['general']['Version']) &&  intval($optionsFullDataToCheck['general']['Version'])<24){
                        $hasOlderVersionsOnMainCGalleriesPage = true;
                        $isOlderVersion = true;
                    }elseif(empty($optionsFullDataToCheck['general']['Version'])){
                        $hasNoVersion = true;
                    }
                }
                if($useCanonicalGalleryNumbers && $isOlderVersion){
                    continue;
                }elseif($hasNoVersion){
                    continue;
                }else{
                    $galleryNumbers[] = $galleryIdToCheck;
                }
            }
        }

        $WinnerIdsArray = [];
        $WinnerIdsLookup = [];

        if($shortcode_name == 'cg_gallery_winner'){
            $WinnerIds = $wpdb->get_results("
							SELECT id 
							FROM $tablename 
							WHERE Winner = 1 ORDER BY id DESC 
						");
            foreach($WinnerIds as $WinnerObject){
                $WinnerIdsArray[] = $WinnerObject->id;
                $WinnerIdsLookup[intval($WinnerObject->id)] = true;
            }
        }

        $EcommerceIdsArray = [];
        $EcommerceIdsLookup = [];

        if($shortcode_name == 'cg_gallery_ecommerce'){
            $EcommerceIds = $wpdb->get_results("SELECT id 
							FROM $tablename 
							WHERE EcommerceEntry > 0 ORDER BY id DESC" );
            foreach($EcommerceIds as $EcommerceObject){
                $EcommerceIdsArray[] = $EcommerceObject->id;
                $EcommerceIdsLookup[intval($EcommerceObject->id)] = true;
            }
        }

        $userGalleryImageIdsLookup = [];

        if($isOnlyGalleryUser){
            $wpUserImageIds = $wpdb->get_results($wpdb->prepare(
                "
                    SELECT id
                    FROM $tablename
                    WHERE WpUserId = %d ORDER BY id DESC
                ",
                $WpUserId
            ));

            if(!empty($wpUserImageIds)){
                foreach($wpUserImageIds as $row){
                    $userGalleryImageIdsLookup[intval($row->id)] = true;
                }
            }
        }


        if (!function_exists('cgSortArray')) {
            function cgSortArray($a1, $a2){
                if ($a1 == $a2) return 0;
                return ($a1 > $a2) ? -1 : 1;
            }
        }

        if(!$hasGalleriesIds){
            usort($galleryNumbers, "cgSortArray");
        }else{// else sort by user given galleryIds
            $galleryNumbersNew = [];
            foreach ($galleriesIds as $galleriesIdToCheck) {
                if(in_array($galleriesIdToCheck,$galleryNumbers,true)!==false){
                    $galleryNumbersNew[] = $galleriesIdToCheck;
                }
            }
            $galleryNumbers = $galleryNumbersNew;
        }

        $dirs = [];


        foreach ($galleryNumbers as $galleryIdForArray) {
            $dirs[] = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdForArray;
        }

        if (!function_exists('cgGetNotEmptyJsonFileData')) {
            function cgGetNotEmptyJsonFileData($index,$imageIDs,$galleryIdToCheck, $imageId, $wp_upload_dir){
                $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/image-data-'.$imageId.'.json';
                $jsonFileData = json_decode(file_get_contents($jsonFile),true);
                if(!empty($jsonFileData)){
                    $jsonFileData['id'] = $imageId;// set it for sure because of previous versions
                    return $jsonFileData;
                }else{
                    $index++;
                    if(!empty($imageIDs[$index])){
                        return cgGetNotEmptyJsonFileData($index,$imageIDs,$galleryIdToCheck, $imageIDs[$index], $wp_upload_dir);
                    }else{
                        return [];
                    }
                }
            }
        }

        if (!function_exists('cgGetHighestRating')) {
            function cgGetHighestRating($a1, $a2, $AllowRating){
                // $a1 == previous file
                // $a2 == current file
                if($AllowRating=='2'){
                    if(empty($a1['addCountS'])){
                        $a1['addCountS'] = 0;
                    }
                    if(empty($a2['addCountS'])){
                        $a2['addCountS'] = 0;
                    }
                    if (intval($a1['CountS'])+intval($a1['addCountS']) == intval($a2['CountS'])+intval($a2['addCountS'])) return $a1;// return previous always, which means higher id
                    return (intval($a1['CountS'])+intval($a1['addCountS']) > intval($a2['CountS'])+intval($a2['addCountS'])) ? $a1 : $a2;
                }elseif($AllowRating>='12'){
                    $array = [12,13,14,15,16,17,18,19,20];
                    $sumA1 = 0;
                    $sumA2 = 0;
                    foreach ($array as $option){
                        if($AllowRating==$option){
                            for($i=1;$i<=($option-10);$i++){
                                if(!empty($a1['CountR'.$i])){
                                    $sumA1 += intval($a1['CountR'.$i])*$i;// *$i because count and not sum is saved
                                }
                                if(!empty($a1['addCountR'.$i])){
                                    $sumA1 += intval($a1['addCountR'.$i])*$i;// *$i because count and not sum is saved
                                }
                                if(!empty($a2['CountR'.$i])){
                                    $sumA2 += intval($a2['CountR'.$i]*$i);//  *$i because count and not sum is saved
                                }
                                if(!empty($a2['addCountR'.$i])){
                                    $sumA2 += intval($a2['addCountR'.$i]*$i);//  *$i because count and not sum is saved
                                }
                            }
                        }
                    }
                    if ($sumA1 == $sumA2) return $a1;// return previous always, which means higher id
                    return ($sumA1 > $sumA2) ? $a1 : $a2;
                }
            }
        }

        if (!function_exists('cgGetHighestComments')) {
            function cgGetHighestComments($a1, $a2){
                // $a1 == previous file
                // $a2 == current file
                if ($a1['CountC'] == $a2['CountC']) return $a1;// return previous always, which means higher id
                return ($a1['CountC'] > $a2['CountC']) ? $a1 : $a2;
            }
        }

        $time = time();
        $structure = get_option( 'permalink_structure' );

        $PositionNumber = 1;

        foreach ($dirs as $dir) {
            $galleryIdToCheck = absint(substr($dir,strrpos($dir,'-')+1, strlen($dir)));
            if(empty($galleryIdToCheck)){
                continue;
            }
            $optionsFileToCheck = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/'.$galleryIdToCheck.'-options.json';
            if(file_exists($optionsFileToCheck)){// only v10 and later

                cg1l_migrate_image_stats_to_folder($galleryIdToCheck, true);// correct first if needs to correct

                $optionsToCheckSource = json_decode(file_get_contents($optionsFileToCheck),true);

                if(!empty($optionsToCheckSource[$galleryIdToCheck])){
                    $optionsToCheck = $optionsToCheckSource[$galleryIdToCheck];
                }else{
                    $optionsToCheck = $optionsToCheckSource;
                }

                $imageDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/*.json');

                $imageIDs = [];
                foreach ($imageDataJsonFiles as $imageDataJsonFile) {
                    $imageId = absint(substr(substr($imageDataJsonFile,strrpos($imageDataJsonFile,'-')+1, 30),0,-5));// do it for sure for eventually very old galleries, which might have only rowid or so
                    if(empty($imageId)){
                        continue;
                    }
                    $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/image-data-'.$imageId.'.json';
                    if(file_exists($jsonFile) && filesize($jsonFile)>10){// size is in bytes... empty array is two chars => 2 byte
                        if($isOnlyGalleryUser){
                            if(!empty($userGalleryImageIdsLookup[$imageId])){
                                $imageIDs[] = $imageId;
                            }
                        }elseif($shortcode_name == 'cg_gallery_winner'){
                            if(!empty($WinnerIdsLookup[$imageId])){
                                $imageIDs[] = $imageId;
                            }
                        }elseif($shortcode_name == 'cg_gallery_ecommerce'){
                            if(!empty($EcommerceIdsLookup[$imageId])){
                                $imageIDs[] = $imageId;
                            }
                        }else{
                            $imageIDs[] = $imageId;
                        }
                    }
                }

                $intervalConf = cg_shortcode_interval_check($galleryIdToCheck,$optionsToCheckSource,$shortcode_name,true);

                if(!$intervalConf['shortcodeIsActive'] || !count($imageIDs)){
                    //var_dump('set no entries');
                    $time = $time+1;// does not work with ++ no clue why
                    $imagesFullData[$time] = cg_get_empty_entry_values();
                    $imagesFullData[$time]['PositionNumber'] = $PositionNumber;
                    if(!empty($optionsToCheck['pro']['MainTitleGalleriesView'])){
                        $imagesFullData[$time]['MainTitleGalleriesView'] = $optionsToCheck['pro']['MainTitleGalleriesView'];
                    }
                    if(!empty($optionsToCheck['pro']['SubTitleGalleriesView'])){
                        $imagesFullData[$time]['SubTitleGalleriesView'] = $optionsToCheck['pro']['SubTitleGalleriesView'];
                    }
                    if(!empty($optionsToCheck['pro']['ThirdTitleGalleriesView'])){
                        $imagesFullData[$time]['ThirdTitleGalleriesView'] = $optionsToCheck['pro']['ThirdTitleGalleriesView'];
                    }

                    $imagesFullData[$time]['gidToShow'] = $galleryIdToCheck;
                    $imagesFullData[$time]['gid'] = $galleryIdToCheck;
                    $imagesFullData[$time]['GalleryIdToCheck'] = $galleryIdToCheck;
                    if(!empty($optionsToCheck['general'][$WpPageParentShortCodeType])){
                        $imagesFullData[$time]['entryGuid'] = get_permalink($optionsToCheck['general'][$WpPageParentShortCodeType]);
                    }
                    $GalleryName = 'Gallery ID '.$galleryIdToCheck;
                    if(!empty($optionsToCheck['general']['GalleryName'])){
                        $GalleryName = $optionsToCheck['general']['GalleryName'];
                    }
                    $imagesFullData[$time]['GalleryName'] = $GalleryName;
                    $imagesFullData[$time]['isCGalleriesNoGalleryGid']  = $galleryIdToCheck;
                    $imagesFullData[$time]['isCGalleriesNoGalleryEntries']  = true;
                    $imagesFullData[$time]['isContestOver']  = false;

                    if(!$intervalConf['shortcodeIsActive']){
                        $imagesFullData[$time]['isContestOver']  = true;
                        $imagesFullData[$time]['isCGalleriesNoGalleryEntriesText']  = $intervalConf['TextWhenShortcodeIntervalIsOff'];
                    }else{
                        $imagesFullData[$time]['isCGalleriesNoGalleryEntriesText']  = $language_NoGalleryEntries;
                    }

                }else{
                    usort($imageIDs, "cgSortArray");
                    $AllowRatingToCheck = $optionsToCheck['general']['AllowRating'];
                    if(!($AllowRatingToCheck==2 || $AllowRatingToCheck>=12)){
                        if($galleriesOptions['PreviewHighestRated']==1){
                            $galleriesOptions['PreviewHighestRated']=0;
                            $galleriesOptions['PreviewLastAdded']=1;
                        }
                    }
                    if($galleriesOptions['PreviewLastAdded']==1){
                        $imageId = $imageIDs[0];
                        $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/image-data-'.$imageId.'.json';
                        $jsonFileData = json_decode(file_get_contents($jsonFile),true);
                        if(empty($jsonFileData)){
                            // 1 because previously 0 is used
                            $jsonFileData = cgGetNotEmptyJsonFileData(1,$imageIDs,$galleryIdToCheck, $imageIDs[1], $wp_upload_dir);
                            if(empty($jsonFileData)){// then image-data files with content in that gallery
                                continue;
                            }
                            $imageId = $jsonFileData['id'];
                            $jsonFileData = cg1l_get_stats_for_galleries_shortcode($wp_upload_dir,$galleryIdToCheck,$imageId,$jsonFileData);
                        }else{
                            $jsonFileData = cg1l_get_stats_for_galleries_shortcode($wp_upload_dir,$galleryIdToCheck,$imageId,$jsonFileData);
                        }
                        /*if($galleryIdToCheck == 40){
                            var_dump('$jsonFileData12312');
                            echo "<br>";
                            echo "<br>";
                            var_dump($jsonFileData);
                            echo "<br>";
                            echo "<br>";
                        }*/
                    }elseif($galleriesOptions['PreviewHighestRated']==1 || $galleriesOptions['PreviewMostCommented']==1){
                        $previousHighestFileData = [];
                        foreach ($imageIDs as $imageID){
                            $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/image-data-'.$imageID.'.json';
                            if(!file_exists($jsonFile)){
                                continue;
                            }else{
                                $jsonFileData = json_decode(file_get_contents($jsonFile),true);
                                if(empty($jsonFileData)){
                                    continue;
                                }
                                $jsonFileData = cg1l_get_stats_for_galleries_shortcode($wp_upload_dir,$galleryIdToCheck,$imageID,$jsonFileData);
                                if(!empty($previousHighestFileData)){
                                    $jsonFileData['id'] = $imageID;// for sure because of previous versions
                                    if($galleriesOptions['PreviewHighestRated']==1 ){
                                        $jsonFileTemp = cgGetHighestRating($previousHighestFileData, $jsonFileData, $AllowRatingToCheck);
                                    }elseif($galleriesOptions['PreviewMostCommented']==1){
                                        $jsonFileTemp = cgGetHighestComments($previousHighestFileData, $jsonFileData);
                                    }
                                    $previousHighestFileData = $jsonFileTemp;
                                    $jsonFileData = $jsonFileTemp;
                                }else{
                                    $jsonFileData = json_decode(file_get_contents($jsonFile),true);
                                    $jsonFileData['id'] = $imageID;// for sure because of previous versions
                                    $previousHighestFileData = $jsonFileData;
                                }
                            }
                        }
                    }

                    $jsonFileData['GalleryIdToCheck'] = $galleryIdToCheck;
                    $jsonFileData['AllowRatingToCheck'] = $AllowRatingToCheck;
                    $jsonFileData['PositionNumber'] = $PositionNumber;

                    if(!empty($optionsToCheck['pro']['MainTitleGalleriesView'])){
                        $jsonFileData['MainTitleGalleriesView'] = $optionsToCheck['pro']['MainTitleGalleriesView'];
                    }
                    if(!empty($optionsToCheck['pro']['SubTitleGalleriesView'])){
                        $jsonFileData['SubTitleGalleriesView'] = $optionsToCheck['pro']['SubTitleGalleriesView'];
                    }
                    if(!empty($optionsToCheck['pro']['ThirdTitleGalleriesView'])){
                        $jsonFileData['ThirdTitleGalleriesView'] = $optionsToCheck['pro']['ThirdTitleGalleriesView'];
                    }

                    if(!empty($optionsToCheck['pro']['ConsentYoutube'])){
                        $jsonFileData['isCGalleriesConsentYoutube'] = $optionsToCheck['pro']['ConsentYoutube'];
                    }
                    if(!empty($optionsToCheck['pro']['ConsentInstagram'])){
                        $jsonFileData['isCGalleriesConsentInstagram'] = $optionsToCheck['pro']['ConsentInstagram'];
                    }
                    if(!empty($optionsToCheck['pro']['ConsentTwitter'])){
                        $jsonFileData['isCGalleriesConsentTwitter'] = $optionsToCheck['pro']['ConsentTwitter'];
                    }
                    if(!empty($optionsToCheck['pro']['ConsentTikTok'])){
                        $jsonFileData['isCGalleriesConsentTikTok'] = $optionsToCheck['pro']['ConsentTikTok'];
                    }

                    /*
                     * $rowObject = $wpdb->get_row( "SELECT id, MultipleFiles FROM $tablename WHERE id = $imageId LIMIT 0, 1" );
                    if(!empty($rowObject->MultipleFiles) && $rowObject->MultipleFiles!='""') {
                        $queryDataArray[$rowObject->id] = [];
                        $queryDataArray[$rowObject->id]['MultipleFiles'] = unserialize($rowObject->MultipleFiles);
                    }
                    */
                    $jsonFileData['gidToShow'] = $galleryIdToCheck;
                    if(!empty($jsonFileData['ImgType']) && $jsonFileData['ImgType'] == 'con'){
                        // continue here with $optionsToCheck
                        $entryIdToDisplay = 0;
                        if(!empty($optionsToCheck['visual']['Field1IdGalleryView'])){
                            $entryIdToDisplay = $optionsToCheck['visual']['Field1IdGalleryView'];
                        }elseif(!empty($optionsToCheck['visual']['SubTitle'])){
                            $entryIdToDisplay = $optionsToCheck['visual']['SubTitle'];
                        }elseif(!empty($optionsToCheck['visual']['ThirdTitle'])){
                            $entryIdToDisplay = $optionsToCheck['visual']['ThirdTitle'];
                        }
                        if($entryIdToDisplay){
                            $InfoDataFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-info/image-info-'.$imageId.'.json';
                            if(file_exists($InfoDataFile)){
                                $InfoData = json_decode(file_get_contents($InfoDataFile),true);
                            }
                            if(!empty($InfoData[$entryIdToDisplay])){
                                $jsonFileData['infoToShowIfGalleriesCon'] = $InfoData[$entryIdToDisplay]['field-content'];
                            }
                        }
                    }
                    $jsonFileData['gid'] = $galleryIdToCheck;
                    $imagesFullData[$imageId] = $jsonFileData;

                    if(!empty($isGalleriesMainPage)){
                        if(!empty($optionsToCheck['general'][$WpPageParentShortCodeType])){
                            if(!empty($structure)){
                                //$imagesFullData[$imageId]['entryGuid'] = get_permalink($optionsToCheck['general'][$WpPageParentShortCodeType]);
                                $imagesFullData[$imageId]['entryGuid'] = get_permalink($optionsToCheck['general'][$WpPageParentShortCodeType]);
                            }else{
                                //$imagesFullData[$imageId]['entryGuid'] = get_permalink($optionsToCheck['general'][$WpPageParentShortCodeType]);
                                $imagesFullData[$imageId]['entryGuid'] = get_permalink($optionsToCheck['general'][$WpPageParentShortCodeType]);
                            }
                        }
                    }
                }
                $PositionNumber++;
            }
        }

        return [
            'imagesFullData' => $imagesFullData,
            'hasOlderVersionsOnMainCGalleriesPage' => $hasOlderVersionsOnMainCGalleriesPage
       ];

    }
}

if (!function_exists('cgl_flatten_to_id_array')) {
    /**
     * Flattens a deeply nested array structure.
     * * It traverses the array until it finds an element containing an 'id' key.
     * The final structure will be a flat associative array where the key is
     * the item's ID and the value is the data array itself.
     *
     * @param array $input The nested input array.
     * @return array The flattened array indexed by ID.
     */
    function cgl_flatten_to_id_array(array $input) {
        $result = [];

        foreach ($input as $key => $value) {
            // Only process if the current value is an array
            if (is_array($value)) {

                // Check if this level contains the actual data (marked by the 'id' key)
                if (isset($value['id'])) {
                    // Use the 'id' value as the new array key to ensure a clean structure
                    $result[$value['id']] = $value;
                } else {
                    // If 'id' is not found, dive deeper into the next level (recursion)
                    // Use array_replace or the + operator to merge found items into the result
                    $result = $result + cgl_flatten_to_id_array($value);
                }
            }
        }

        return $result;
    }
}
