<?php

if (!function_exists('cg1l_release_stats_lock')) {
    function cg1l_release_stats_lock(&$lockFp) {
        // Release lock safely (no @)
        if (!empty($lockFp) && is_resource($lockFp)) {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
        }
        $lockFp = false;
    }
}


if (!function_exists('cg1l_set_stats_with_lock')) {
    function cg1l_set_stats_with_lock($gid, $id, $stats, $lockFp) {

        $gid = absint($gid);
        $id  = preg_replace('/[^0-9]/', '', (string)$id);

        if (empty($gid) || empty($id) || empty($lockFp) || !is_array($stats)) {
            return false;
        }

        $wp_upload_dir = wp_upload_dir();
        $folder = $wp_upload_dir['basedir']
            . '/contest-gallery/gallery-id-' . $gid
            . '/json/image-stats';

        $target = $folder.'/image-stats-' . $id . '.json';

        if (!file_exists($target)) {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
            return false;
        }

        // Use a unique temp name to prevent collisions under high traffic
        $tmp = $folder.'/'.uniqid('data_', true) . '.tmp';

        // Atomic write (tmp + rename)
        if (file_put_contents($tmp, json_encode($stats), LOCK_EX) === false) {
            if (file_exists($tmp)) {
                unlink($tmp);
            }
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
            return false;
        }

        if (!rename($tmp, $target)) {
            if (file_exists($tmp)) {
                unlink($tmp);
            }
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
            return false;
        }

        // Release lock AFTER successful write
        flock($lockFp, LOCK_UN);
        fclose($lockFp);

        return $stats;
    }


}

