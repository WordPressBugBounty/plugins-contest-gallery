<?php

if(!function_exists('cg1l_get_image_stats_context')){
    function cg1l_get_image_stats_context($gid) {
        $gid = absint($gid);
        $wp_upload_dir = wp_upload_dir();
        $gallery_json_dir = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $gid . '/json';

        return [
            'gid' => $gid,
            'gallery_json_dir' => $gallery_json_dir,
            'image_data_dir' => $gallery_json_dir . '/image-data',
            'image_stats_dir' => $gallery_json_dir . '/image-stats',
            'segments_dir' => $gallery_json_dir . '/segments',
            'last_updated_path' => $gallery_json_dir . '/segments/image-stats-data-last-update.txt',
            'health_marker_path' => $gallery_json_dir . '/segments/image-stats-migration-health.txt',
            'stats_keys' => cg1l_return_stats_placeholder_values(),
            'stats_keys_lookup' => array_fill_keys(cg1l_return_stats_placeholder_values(), true)
        ];
    }
}

if(!function_exists('cg1l_get_image_stats_runtime_mtime_limit')){
    function cg1l_get_image_stats_runtime_mtime_limit() {
        return 500;
    }
}

if(!function_exists('cg1l_extract_image_json_id_from_file_path')){
    function cg1l_extract_image_json_id_from_file_path($filePath, $prefix) {
        $base = basename($filePath);
        $matches = [];
        if(preg_match('/^'.preg_quote($prefix, '/').'-(\d+)\.json$/', $base, $matches)){
            return absint($matches[1]);
        }
        return 0;
    }
}

if(!function_exists('cg1l_write_atomic_file_payload')){
    function cg1l_write_atomic_file_payload($target, $payload) {
        $dir = dirname($target);
        if(!is_dir($dir)){
            wp_mkdir_p($dir);
        }

        if(!is_dir($dir)){
            return false;
        }

        $temp = $dir . '/' . uniqid('data_', true) . '.tmp';
        if(file_put_contents($temp, $payload, LOCK_EX) === false){
            if(file_exists($temp)){
                unlink($temp);
            }
            return false;
        }

        if(!rename($temp, $target)){
            if(file_exists($temp)){
                unlink($temp);
            }
            return false;
        }

        clearstatcache(true, $target);
        return true;
    }
}

if(!function_exists('cg1l_get_image_stats_directory_state')){
    function cg1l_get_image_stats_directory_state($context, $includeMtime = null) {
        $imageDataFiles = glob($context['image_data_dir'] . '/image-data-*.json');
        $imageStatsFiles = glob($context['image_stats_dir'] . '/image-stats-*.json');

        if($imageDataFiles === false || $imageStatsFiles === false){
            return [
                'ok' => false,
                'error' => 'glob_failed'
            ];
        }

        $imageDataCount = count($imageDataFiles);
        $imageStatsCount = count($imageStatsFiles);
        $shouldUseMtime = $includeMtime;
        if($shouldUseMtime === null){
            $shouldUseMtime = max($imageDataCount, $imageStatsCount) <= cg1l_get_image_stats_runtime_mtime_limit();
        }

        $imageDataIds = [];
        $imageStatsIds = [];
        $imageDataLatestMtime = 0;
        $imageStatsLatestMtime = 0;

        foreach($imageDataFiles as $filePath){
            $id = cg1l_extract_image_json_id_from_file_path($filePath, 'image-data');
            if(!$id){
                continue;
            }
            $imageDataIds[$id] = $filePath;
            if($shouldUseMtime){
                $mtime = @filemtime($filePath);
                if($mtime && $mtime > $imageDataLatestMtime){
                    $imageDataLatestMtime = $mtime;
                }
            }
        }

        foreach($imageStatsFiles as $filePath){
            $id = cg1l_extract_image_json_id_from_file_path($filePath, 'image-stats');
            if(!$id){
                continue;
            }
            $imageStatsIds[$id] = $filePath;
            if($shouldUseMtime){
                $mtime = @filemtime($filePath);
                if($mtime && $mtime > $imageStatsLatestMtime){
                    $imageStatsLatestMtime = $mtime;
                }
            }
        }

        ksort($imageDataIds);
        ksort($imageStatsIds);

        return [
            'ok' => true,
            'image_data_ids' => $imageDataIds,
            'image_stats_ids' => $imageStatsIds,
            'image_data_count' => count($imageDataIds),
            'image_stats_count' => count($imageStatsIds),
            'uses_mtime' => !empty($shouldUseMtime),
            'image_data_latest_mtime' => (int)$imageDataLatestMtime,
            'image_stats_latest_mtime' => (int)$imageStatsLatestMtime
        ];
    }
}

if(!function_exists('cg1l_read_image_stats_health_marker')){
    function cg1l_read_image_stats_health_marker($context) {
        if(empty($context['health_marker_path']) || !file_exists($context['health_marker_path'])){
            return [];
        }

        $raw = file_get_contents($context['health_marker_path']);
        if($raw === false || $raw === ''){
            return [];
        }

        $decoded = json_decode($raw, true);
        if(empty($decoded) || !is_array($decoded)){
            return [];
        }

        return $decoded;
    }
}

if(!function_exists('cg1l_refresh_image_stats_health_marker')){
    function cg1l_refresh_image_stats_health_marker($gid, $context = []) {
        if(empty($context) || !is_array($context)){
            $context = cg1l_get_image_stats_context($gid);
        }

        $state = cg1l_get_image_stats_directory_state($context);
        if(empty($state['ok'])){
            return false;
        }

        $payload = [
            'image_data_count' => $state['image_data_count'],
            'image_stats_count' => $state['image_stats_count'],
            'uses_mtime' => !empty($state['uses_mtime']) ? 1 : 0,
            'image_data_latest_mtime' => $state['image_data_latest_mtime'],
            'image_stats_latest_mtime' => $state['image_stats_latest_mtime'],
            'updated_at' => time()
        ];

        return cg1l_write_atomic_file_payload($context['health_marker_path'], json_encode($payload));
    }
}

if(!function_exists('cg1l_is_image_stats_health_marker_fresh')){
    function cg1l_is_image_stats_health_marker_fresh($state, $healthMarker) {
        if(empty($healthMarker) || !is_array($healthMarker)){
            return false;
        }

        if(
            intval(isset($healthMarker['image_data_count']) ? $healthMarker['image_data_count'] : -1) !== intval($state['image_data_count']) ||
            intval(isset($healthMarker['image_stats_count']) ? $healthMarker['image_stats_count'] : -1) !== intval($state['image_stats_count'])
        ){
            return false;
        }

        $markerUsesMtime = !empty($healthMarker['uses_mtime']);
        $stateUsesMtime = !empty($state['uses_mtime']);

        if($markerUsesMtime !== $stateUsesMtime){
            return false;
        }

        if($stateUsesMtime){
            return (
                intval(isset($healthMarker['image_data_latest_mtime']) ? $healthMarker['image_data_latest_mtime'] : -1) === intval($state['image_data_latest_mtime']) &&
                intval(isset($healthMarker['image_stats_latest_mtime']) ? $healthMarker['image_stats_latest_mtime'] : -1) === intval($state['image_stats_latest_mtime'])
            );
        }

        return true;
    }
}

if(!function_exists('cg1l_touch_image_stats_last_updated_file')){
    function cg1l_touch_image_stats_last_updated_file($gid, $context = []) {
        if(function_exists('cg1l_create_last_updated_time_file')){
            cg1l_create_last_updated_time_file($gid, 'image-stats-data-last-update');
            return true;
        }

        if(empty($context) || !is_array($context)){
            $context = cg1l_get_image_stats_context($gid);
        }

        return cg1l_write_atomic_file_payload($context['last_updated_path'], (string)time());
    }
}

if(!function_exists('cg1l_is_existing_image_stats_payload_broken')){
    function cg1l_is_existing_image_stats_payload_broken($statsFilePath, $statsKeysLookup) {
        if(empty($statsFilePath) || !file_exists($statsFilePath)){
            return true;
        }

        $jsonRaw = file_get_contents($statsFilePath);
        if($jsonRaw === false || $jsonRaw === ''){
            return true;
        }

        $data = json_decode($jsonRaw, true);
        if(empty($data) || !is_array($data)){
            return true;
        }

        foreach($statsKeysLookup as $statsKey => $unused){
            if(array_key_exists($statsKey, $data)){
                return false;
            }
        }

        return true;
    }
}

if(!function_exists('cg1l_repair_single_image_stats_file')){
    function cg1l_repair_single_image_stats_file($gid, $id, $remove_from_image_data = true, $context = [], $invalidate_stats_segment = false) {
        $gid = absint($gid);
        $id = absint($id);

        if(!$gid || !$id){
            return [
                'ok' => false,
                'error' => 'invalid_gid_or_id',
                'id' => $id
            ];
        }

        if(empty($context) || !is_array($context)){
            $context = cg1l_get_image_stats_context($gid);
        }

        if(!is_dir($context['image_stats_dir'])){
            wp_mkdir_p($context['image_stats_dir']);
        }

        if(!is_dir($context['image_stats_dir'])){
            return [
                'ok' => false,
                'error' => 'image_stats_dir_not_writable_or_not_created',
                'id' => $id
            ];
        }

        $imageDataFile = $context['image_data_dir'] . '/image-data-' . $id . '.json';
        if(!file_exists($imageDataFile)){
            return [
                'ok' => false,
                'error' => 'image_data_missing',
                'id' => $id
            ];
        }

        $jsonRaw = file_get_contents($imageDataFile);
        if($jsonRaw === false || $jsonRaw === ''){
            return [
                'ok' => false,
                'error' => 'image_data_unreadable',
                'id' => $id
            ];
        }

        $imageData = json_decode($jsonRaw, true);
        if(empty($imageData) || !is_array($imageData)){
            return [
                'ok' => false,
                'error' => 'image_data_invalid_json',
                'id' => $id
            ];
        }

        $statsData = ['id' => (string)$id] + cg1l_return_stats_placeholder_values(true);
        $hadSourceStats = false;

        foreach($context['stats_keys'] as $statsKey){
            if(array_key_exists($statsKey, $imageData)){
                $statsData[$statsKey] = $imageData[$statsKey];
                $hadSourceStats = true;
            }
        }

        $target = $context['image_stats_dir'] . '/image-stats-' . $id . '.json';
        $isNew = !file_exists($target);

        if(!cg1l_write_atomic_file_payload($target, json_encode($statsData))){
            return [
                'ok' => false,
                'error' => 'image_stats_write_failed',
                'id' => $id
            ];
        }

        $imageDataRewriteFailed = false;
        if(!empty($remove_from_image_data) && $hadSourceStats){
            foreach($context['stats_keys'] as $statsKey){
                if(array_key_exists($statsKey, $imageData)){
                    unset($imageData[$statsKey]);
                }
            }

            if(!cg1l_write_atomic_file_payload($imageDataFile, json_encode($imageData))){
                $imageDataRewriteFailed = true;
            }
        }

        if($invalidate_stats_segment){
            if(function_exists('cg1l_push_recent_id_file')){
                cg1l_push_recent_id_file($gid, $id, 'image-stats-data-last-update');
            }
            cg1l_touch_image_stats_last_updated_file($gid, $context);
            cg1l_refresh_image_stats_health_marker($gid, $context);
        }

        return [
            'ok' => true,
            'id' => $id,
            'is_new' => $isNew,
            'had_source_stats' => $hadSourceStats,
            'image_data_rewrite_failed' => $imageDataRewriteFailed
        ];
    }
}

if(!function_exists('cg1l_migrate_image_stats_to_folder')){
    
    /*
    Example usage:
    
    // Just create image-stats files (do not modify image-data)
    $result = cg1l_migrate_image_stats_to_folder(32, false);
    
    // Or: also remove stats fields from image-data json (be careful!)
    $result = cg1l_migrate_image_stats_to_folder(32, true);
    
    error_log(print_r($result, true));
    */

    function cg1l_migrate_image_stats_to_folder($gid, $remove_from_image_data, $force_deep_repair = false) {

        $context = cg1l_get_image_stats_context($gid);

        if (!is_dir($context['image_stats_dir'])) {
            wp_mkdir_p($context['image_stats_dir']);
        }

        if (!is_dir($context['image_stats_dir'])) {
            return [
                'ok' => false,
                'error' => 'image_stats_dir_not_writable_or_not_created',
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0
            ];
        }

        $state = cg1l_get_image_stats_directory_state($context, !empty($force_deep_repair) ? true : null);
        if (empty($state['ok'])) {
            return [
                'ok' => false,
                'error' => 'glob_failed',
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0
            ];
        }

        if (empty($state['image_data_ids'])) {
            cg1l_refresh_image_stats_health_marker($context['gid'], $context);
            return [
                'ok' => true,
                'mode' => 'noop',
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0
            ];
        }

        $healthMarker = cg1l_read_image_stats_health_marker($context);
        $missingStatsIds = array_diff_key($state['image_data_ids'], $state['image_stats_ids']);
        $hasFreshLastUpdated = file_exists($context['last_updated_path']);
        $hasFreshHealthMarker = cg1l_is_image_stats_health_marker_fresh($state, $healthMarker);

        if (empty($force_deep_repair) && $hasFreshLastUpdated && $hasFreshHealthMarker && empty($missingStatsIds)) {
            return [
                'ok' => true,
                'mode' => 'noop',
                'processed' => $state['image_data_count'],
                'created' => 0,
                'updated' => 0,
                'skipped' => 0
            ];
        }

        $processed = $state['image_data_count'];
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $repairedIds = [];
        $idsToRepair = [];

        foreach ($missingStatsIds as $id => $unusedFilePath) {
            $idsToRepair[$id] = true;
        }

        if(!empty($force_deep_repair) || !$hasFreshHealthMarker || !$hasFreshLastUpdated || !empty($missingStatsIds)){
            foreach ($state['image_data_ids'] as $id => $imageDataFilePath) {
                if (!empty($idsToRepair[$id])) {
                    continue;
                }
                if (empty($state['image_stats_ids'][$id])) {
                    continue;
                }
                if (cg1l_is_existing_image_stats_payload_broken($state['image_stats_ids'][$id], $context['stats_keys_lookup'])) {
                    $idsToRepair[$id] = true;
                }
            }
        }

        foreach (array_keys($idsToRepair) as $id) {
            $repairResult = cg1l_repair_single_image_stats_file($context['gid'], $id, $remove_from_image_data, $context, false);
            if (empty($repairResult['ok'])) {
                $skipped++;
                continue;
            }

            $repairedIds[] = $id;

            if (!empty($repairResult['is_new'])) {
                $created++;
            } else {
                $updated++;
            }
        }

        if (!empty($repairedIds) && function_exists('cg1l_push_recent_id_file')) {
            foreach ($repairedIds as $id) {
                cg1l_push_recent_id_file($context['gid'], $id, 'image-stats-data-last-update');
            }
        }

        if ($hasFreshLastUpdated === false || !empty($repairedIds)) {
            cg1l_touch_image_stats_last_updated_file($context['gid'], $context);
        }

        $postRepairState = cg1l_get_image_stats_directory_state($context);
        $canRefreshHealthMarker = !empty($postRepairState['ok']) && empty(array_diff_key($postRepairState['image_data_ids'], $postRepairState['image_stats_ids'])) && $skipped === 0;

        if ($canRefreshHealthMarker) {
            foreach ($postRepairState['image_data_ids'] as $id => $unusedFilePath) {
                if (empty($postRepairState['image_stats_ids'][$id])) {
                    $canRefreshHealthMarker = false;
                    break;
                }
                if (cg1l_is_existing_image_stats_payload_broken($postRepairState['image_stats_ids'][$id], $context['stats_keys_lookup'])) {
                    $canRefreshHealthMarker = false;
                    break;
                }
            }
        }

        if ($canRefreshHealthMarker) {
            cg1l_refresh_image_stats_health_marker($context['gid'], $context);
        }

        return [
            'ok' => true,
            'mode' => !empty($idsToRepair) ? 'repair' : 'check',
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped
        ];
    }

}


if (!function_exists('cg1l_return_stats_placeholder_values')) {

    /*
    Example usage:

    // Just create image-stats files (do not modify image-data)
    $result = cg1l_migrate_image_stats_to_folder(32, false);

    // Or: also remove stats fields from image-data json (be careful!)
    $result = cg1l_migrate_image_stats_to_folder(32, true);

    error_log(print_r($result, true));
    */

    function cg1l_return_stats_placeholder_values($withValues = false)
    {
        $data = [
            'CountC',
            'CountR',
            'CountS',
            'addCountS',
            'addCountR1',
            'addCountR2',
            'addCountR3',
            'addCountR4',
            'addCountR5',
            'addCountR6',
            'addCountR7',
            'addCountR8',
            'addCountR9',
            'addCountR10',
            'CountR1',
            'CountR2',
            'CountR3',
            'CountR4',
            'CountR5',
            'CountR6',
            'CountR7',
            'CountR8',
            'CountR9',
            'CountR10'
        ];
        if($withValues){
            $data = array_fill_keys($data, 0);
        }
        return $data;
    }

}



if (!function_exists('cg1l_correct_view_options_and_order')) {
    function cg1l_correct_view_options_and_order(&$order, &$thumbLook = '', &$sliderLook = '', &$blogLook = '', &$heightLook = '', &$rowLook = '', $isJsonOptions = false)
    {
        // 1. HeightLook and RowLook must always be ''
        $heightLook = '';
        $rowLook = '';
        $currentLook = 'thumb';

        // 2. Process the array to find the very first valid "Look" option
        // has to be done first, because is correction
        $foundFirstValid = false;
        foreach ($order as $lookType) {
            if ($lookType === 'ThumbLookOrder' && $thumbLook) {
                // Keep thumbLook as is, but disable others
                $sliderLook = '';
                $blogLook = '';
                $currentLook = 'thumb';
                $foundFirstValid = true;break;
            } elseif ($lookType === 'SliderLookOrder' && $sliderLook) {
                // Keep sliderLook as is, but disable others
                $thumbLook = '';
                $blogLook = '';
                $currentLook = 'slider';
                $foundFirstValid = true;break;
            } elseif ($lookType === 'BlogLookOrder' && $blogLook) {
                // Keep blogLook as is, but disable others
                $thumbLook = '';
                $sliderLook = '';
                $currentLook = 'blog';
                $foundFirstValid = true;break;
            }
        }
        // If none of the preferred types were found in the array, reset
        if (!$foundFirstValid) {
            $thumbLook = 'checked';
            $sliderLook = '';
            $blogLook = '';
        }
        // 3. Finally, sort the array into the fixed required order
        usort($order, function($a, $b) {
            $orderToBe = [
                'ThumbLookOrder'  => 0,
                'SliderLookOrder' => 1,
                'BlogLookOrder'   => 2,
                'RowLookOrder'    => 3
            ];
            $posA = isset($orderToBe[$a]) ? $orderToBe[$a] : 99;
            $posB = isset($orderToBe[$b]) ? $orderToBe[$b] : 99;
            if($posA == $posB){
                return 0;
            }
            return ($posA < $posB) ? -1 : 1;
        });

        if($isJsonOptions){
            if($thumbLook){$thumbLook='1';}else{$thumbLook='0';}
            if($sliderLook){$sliderLook='1';}else{$sliderLook='0';}
            if($blogLook){$blogLook='1';}else{$blogLook='0';}
            if($heightLook){$heightLook='1';}else{$heightLook='0';}
            if($rowLook){$rowLook='1';}else{$rowLook='0';}
        }

        return $currentLook;


    }
}


?>
