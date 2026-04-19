<?php

if (!function_exists('cg1l_get_stats_for_update')) {
    function cg1l_get_stats_for_update($gid, $id, &$lockFp) {

        $lockFp = false;

        $gid = absint($gid);
        $id  = preg_replace('/[^0-9]/', '', (string)$id);

        if (empty($gid) || empty($id)) {
            return false;
        }

        $wp_upload_dir = wp_upload_dir();

        $statsFile = $wp_upload_dir['basedir']
            . '/contest-gallery/gallery-id-' . $gid
            . '/json/image-stats/image-stats-' . $id . '.json';

        $statsDir = dirname($statsFile);
        if (!is_dir($statsDir)) {
            if (!wp_mkdir_p($statsDir)) {
                return false;
            }
        }

        if (!file_exists($statsFile)) {

            $lockFile = $statsFile . '.lock';

            $lockFp = fopen($lockFile, 'c');
            if ($lockFp === false) {
                $lockFp = false;
                return false;
            }

            if (!flock($lockFp, LOCK_EX)) {
                fclose($lockFp);
                $lockFp = false;
                return false;
            }

            // Create stats file with placeholder while lock is held
            $json = json_encode(cg1l_return_stats_placeholder_values(true));
            if ($json === false) {
                flock($lockFp, LOCK_UN);
                fclose($lockFp);
                $lockFp = false;
                return false;
            }

            // Write file only if it still doesn't exist or is empty (double-check under lock)
            if (!file_exists($statsFile) || filesize($statsFile) === 0) {
                file_put_contents($statsFile, $json, LOCK_EX);
            }

            $stats = json_decode($json, true);
            if (!is_array($stats)) {
                flock($lockFp, LOCK_UN);
                fclose($lockFp);
                $lockFp = false;
                return false;
            }

            $stats['id'] = (string) $id;

            // IMPORTANT: lock stays held; caller must unlock/close using $lockFp later
            return $stats;
        }

        $lockFile = $statsFile . '.lock';

        $lockFp = fopen($lockFile, 'c');
        if (!$lockFp) {
            return false;
        }

        // Exclusive lock for read-modify-write cycle
        if (!flock($lockFp, LOCK_EX)) {
            fclose($lockFp);
            $lockFp = false;
            return false;
        }

        $raw = file_get_contents($statsFile);
        if ($raw === false || $raw === '') {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
            $lockFp = false;
            return false;
        }

        $stats = json_decode($raw, true);
        if (!is_array($stats)) {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
            $lockFp = false;
            return false;
        }

        $stats['id'] = (string)$id;

        // IMPORTANT: lock stays held; caller must pass $lockFp into set function
        return $stats;
    }

}

if (!function_exists('cg1l_get_recent_json_by_ids')) {
    /**
     * Load recent JSON files by ID markers.
     *
     * @param int    $gid         Gallery ID
     * @param string $recentType  Folder inside json/segments (e.g. 'recent-image-data')
     * @param string $sourceDir   Source folder inside json (e.g. 'image-info' or 'image-data')
     * @param string $filePrefix  File prefix (e.g. 'image-info-' or 'image-data-')
     *
     * @return array  Array keyed by ID
     */
    function cg1l_get_recent_json_by_ids($gid, $recentType, $sourceDir, $filePrefix) {

        $gid = absint($gid);
        if ($gid === 0) {
            return [];
        }

        $wp_upload_dir = wp_upload_dir();

        $base_json_dir = $wp_upload_dir['basedir']
            . '/contest-gallery/gallery-id-' . $gid . '/json';

        $recent_dir = $base_json_dir . '/segments/' . $recentType;
        $source_dir = $base_json_dir . '/' . $sourceDir;

        if (!is_dir($recent_dir) || !is_dir($source_dir)) {
            return [];
        }

        $result = [];

        // Get recent ID marker files (*.txt)
        $idFiles = glob($recent_dir . '/*.txt');
        if (empty($idFiles)) {
            return [];
        }

        foreach ($idFiles as $idFile) {

            // Filename = ID.txt
            $id = preg_replace('/[^0-9]/', '', basename($idFile));
            if (empty($id)) {
                continue;
            }

            $jsonFile = $source_dir . '/' . $filePrefix . $id . '.json';
            if (!file_exists($jsonFile)) {
                continue;
            }

            $json = file_get_contents($jsonFile);
            if ($json === false || $json === '') {
                continue;
            }

            $data = json_decode($json, true);
            if (!is_array($data)) {
                continue;
            }

            $result[$id] = $data;
        }

        return $result;
    }
}

