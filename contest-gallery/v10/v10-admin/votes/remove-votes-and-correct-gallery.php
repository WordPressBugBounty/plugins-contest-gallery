<?php

$cgVotesCorrectionSucceeded = false;
$cgVotesCorrectionError = '';
$lockFp = false;
$ratingFileData = false;
$commentsLockFp = false;
$commentDeletionPlan = false;
$linkedCommentIds = array();
$commentCounts = false;
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
    $selectedVoteValues = array_merge($collectIds,array($imageId,$GalleryID));
    $selectedVotes = $wpdb->get_results($wpdb->prepare(
        "SELECT id, CommentId
        FROM $tablename_ip
        WHERE id IN ($placeholders) AND pid = %d AND GalleryID = %d",
        $selectedVoteValues
    ));

    $collectIds = array();
    foreach($selectedVotes as $selectedVote){
        $selectedVoteId = absint($selectedVote->id);
        if(!empty($selectedVoteId)){
            $collectIds[$selectedVoteId] = $selectedVoteId;
        }
        $selectedCommentId = absint($selectedVote->CommentId);
        if(!empty($selectedCommentId)){
            $linkedCommentIds[$selectedCommentId] = $selectedCommentId;
        }
    }

    if(!empty($linkedCommentIds)){
        $commentPlaceholders = implode(',',array_fill(0,count($linkedCommentIds),'%d'));
        $linkedCommentValues = array_merge(array_values($linkedCommentIds),array($imageId,$GalleryID));
        $existingLinkedCommentIds = $wpdb->get_col($wpdb->prepare(
            "SELECT id
            FROM $tablename_comments
            WHERE id IN ($commentPlaceholders) AND pid = %d AND GalleryID = %d",
            $linkedCommentValues
        ));
        $linkedCommentIds = array();
        foreach($existingLinkedCommentIds as $existingLinkedCommentId){
            $existingLinkedCommentId = absint($existingLinkedCommentId);
            if(!empty($existingLinkedCommentId)){
                $linkedCommentIds[$existingLinkedCommentId] = $existingLinkedCommentId;
            }
        }

        if(!empty($linkedCommentIds)){
            $commentDeletionPlan = cg1l_prepare_comment_file_deletion($GalleryID,$imageId,array_values($linkedCommentIds),$commentsLockFp);
            if($commentDeletionPlan === false || !cg1l_apply_comment_file_deletion($commentDeletionPlan)){
                $cgVotesCorrectionError = 'Votes and their linked comments could not be removed.';
                cg1l_release_comment_lock($commentsLockFp);
                cg1l_release_stats_lock($lockFp);
                return;
            }
            $commentCounts = cg1l_get_comment_counts_from_data($commentDeletionPlan['comments_data']);
        }
    }

    if(!empty($collectIds)){
        $collectIds = array_values($collectIds);
        $placeholders = implode(',',array_fill(0,count($collectIds),'%d'));
        $deleteValues = array_merge($collectIds,array($imageId,$GalleryID));
        $databaseWriteFailed = false;
        $wpdb->query('START TRANSACTION');

        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $tablename_ip WHERE id IN ($placeholders) AND pid = %d AND GalleryID = %d",
            $deleteValues
        ));

        if($deleted === false){
            $databaseWriteFailed = true;
        }

        if(!$databaseWriteFailed && !empty($linkedCommentIds)){
            $commentPlaceholders = implode(',',array_fill(0,count($linkedCommentIds),'%d'));
            $deleteCommentValues = array_merge(array_values($linkedCommentIds),array($imageId,$GalleryID));
            $deletedComments = $wpdb->query($wpdb->prepare(
                "DELETE FROM $tablename_comments WHERE id IN ($commentPlaceholders) AND pid = %d AND GalleryID = %d",
                $deleteCommentValues
            ));
            if($deletedComments === false){
                $databaseWriteFailed = true;
            }
        }

        if($databaseWriteFailed){
            $wpdb->query('ROLLBACK');
            if($commentDeletionPlan !== false){
                cg1l_restore_comment_file_deletion($commentDeletionPlan);
            }
            cg1l_release_comment_lock($commentsLockFp);
            $cgVotesCorrectionError = 'Votes and their linked comments could not be removed.';
            cg1l_release_stats_lock($lockFp);
            return;
        }

        $wpdb->query('COMMIT');
    }
}

cg1l_release_comment_lock($commentsLockFp);

$correctedRatingCounts = cg_correct_entry_count($imageId,$GalleryID);

if($correctedRatingCounts === false){
    $cgVotesCorrectionError = 'Votes were removed, but the entry counters could not be corrected.';
    cg1l_release_stats_lock($lockFp);
    return;
}

if(is_array($commentCounts)){
    $wpdb->update(
        $tablename,
        array('CountC' => intval($commentCounts['total']), 'CountCtoReview' => intval($commentCounts['to_review'])),
        array('id' => $imageId, 'GalleryID' => $GalleryID),
        array('%d','%d'),
        array('%d','%d')
    );
}

if($isActiveEntry){
    foreach($correctedRatingCounts as $ratingKey => $ratingValue){
        $ratingFileData[$ratingKey] = intval($ratingValue);
    }

    if(is_array($commentCounts)){
        $ratingFileData['CountC'] = intval($commentCounts['total']);
        $ratingFileData['CountCtoReview'] = intval($commentCounts['hidden']);
    }

    $ratingFileData = cg1l_set_stats_with_lock($GalleryID, $imageId, $ratingFileData, $lockFp);
    cg1l_release_stats_lock($lockFp);

    if($ratingFileData === false){
        $cgVotesCorrectionError = 'The entry counters were corrected, but the frontend statistics could not be updated.';
        return;
    }

    if(is_array($commentCounts)){
        cg1l_mark_vote_comment_data_updated($GalleryID,$imageId);
    }else{
        cg1l_push_recent_id_file($GalleryID,$imageId,'image-stats-data-last-update');
        cg1l_create_last_updated_time_file($GalleryID,'image-stats-data-last-update');
    }
}elseif(is_array($commentCounts)){
    cg1l_mark_vote_comment_data_updated($GalleryID,$imageId);
}

$cgVotesCorrectionSucceeded = true;
