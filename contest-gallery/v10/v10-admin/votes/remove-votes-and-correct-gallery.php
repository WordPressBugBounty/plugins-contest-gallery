<?php

$cgVotesCorrectionSucceeded = false;
$cgVotesCorrectionError = '';
$lockFp = false;
$ratingFileData = false;
$isActiveEntry = (!empty($imageData->Active) && intval($imageData->Active) === 1);

if($isActiveEntry){
    cg1l_migrate_image_stats_to_folder($GalleryID, true, true);// correct first if needs to correct
    $ratingFileData = cg1l_get_stats_for_update($GalleryID, $imageId, $lockFp);

    if($ratingFileData === false){
        $cgVotesCorrectionError = 'Votes could not be corrected because the entry statistics are currently unavailable.';
        cg1l_release_stats_lock($lockFp);
        return;
    }
}

$collectIds = array();

if(!empty($_POST['ipId']) && is_array($_POST['ipId'])){
    foreach($_POST['ipId'] as $ipId => $ratingHeight){
        $ipId = absint($ipId);
        if(!empty($ipId)){
            $collectIds[$ipId] = $ipId;
        }
    }
}

if(!empty($collectIds)){
    $collectIds = array_values($collectIds);
    $placeholders = implode(',',array_fill(0,count($collectIds),'%d'));
    $deleteValues = $collectIds;
    $deleteValues[] = $imageId;
    $deleteValues[] = $GalleryID;

    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM $tablename_ip WHERE id IN ($placeholders) AND pid = %d AND GalleryID = %d",
        $deleteValues
    ));

    if($deleted === false){
        $cgVotesCorrectionError = 'Votes could not be removed.';
        cg1l_release_stats_lock($lockFp);
        return;
    }
}

$correctedRatingCounts = cg_correct_entry_count($imageId,$GalleryID);

if($correctedRatingCounts === false){
    $cgVotesCorrectionError = 'Votes were removed, but the entry counters could not be corrected.';
    cg1l_release_stats_lock($lockFp);
    return;
}

if($isActiveEntry){
    foreach($correctedRatingCounts as $ratingKey => $ratingValue){
        $ratingFileData[$ratingKey] = intval($ratingValue);
    }

    $ratingFileData = cg1l_set_stats_with_lock($GalleryID, $imageId, $ratingFileData, $lockFp);
    cg1l_release_stats_lock($lockFp);

    if($ratingFileData === false){
        $cgVotesCorrectionError = 'The entry counters were corrected, but the frontend statistics could not be updated.';
        return;
    }

    cg1l_push_recent_id_file($GalleryID,$imageId,'image-stats-data-last-update');
    cg1l_create_last_updated_time_file($GalleryID,'image-stats-data-last-update');
}

$cgVotesCorrectionSucceeded = true;
