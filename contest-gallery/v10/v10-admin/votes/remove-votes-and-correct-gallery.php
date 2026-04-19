<?php
if (!empty($_POST['ipId'])) {

    $collect = '';
    $collectIds = array();

    $collectCountS = 0;
    $collectCountR = 0;
    $collectRating = 0; // sum of multiple stars ratingsecho

    $isRemoveFiveStar = false;
    $isRemoveOneStar = false;

    foreach ($_POST['ipId'] as $ipId => $ratingHeight) {

        if ($collect == '') {
            $collect .= "id = %d";
            $collectIds[] = $ipId;
        } else {
            $collect .= " OR id = %d";
            $collectIds[] = $ipId;
        }

    }

    $wpdb->query($wpdb->prepare(
        "DELETE FROM $tablename_ip WHERE $collect", $collectIds
    ));

}

if($imageData->Active==1){
    cg_correct_entry_count($imageId);
    $wp_upload_dir = wp_upload_dir();
    $RatingOverviewArray = cg_get_correct_rating_overview($GalleryID);
    cg1l_migrate_image_stats_to_folder($GalleryID, true, true);// correct first if needs to correct
    $lockFp = false;
    $ratingFileData = cg1l_get_stats_for_update($GalleryID, $imageId, $lockFp);
    if(empty($RatingOverviewArray[$imageId])){
        $RatingOverviewArray[$imageId] = [];
    }
    $ratingFileData = array_merge($ratingFileData, $RatingOverviewArray[$imageId]);
    $ratingFileData = cg1l_set_stats_with_lock($GalleryID, $imageId, $ratingFileData, $lockFp);
    cg1l_release_stats_lock($lockFp);
    cg1l_push_recent_id_file($GalleryID,$imageId,'image-stats-data-last-update');
    cg1l_create_last_updated_time_file($GalleryID,'image-stats-data-last-update');
}
