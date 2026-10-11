<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg1l_get_vote_comment_mode')){
    function cg1l_get_vote_comment_mode($options){
        $mode = 'disabled';
        if(!empty($options['general']['VoteCommentMode'])){
            $mode = $options['general']['VoteCommentMode'];
        }
        if(!in_array($mode, array('required','optional'), true) || empty($options['general']['AllowComments']) || intval($options['general']['AllowComments'])!==1){
            return 'disabled';
        }
        return $mode;
    }
}

if(!function_exists('cg1l_vote_comment_base64url_encode')){
    function cg1l_vote_comment_base64url_encode($value){
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

if(!function_exists('cg1l_vote_comment_base64url_decode')){
    function cg1l_vote_comment_base64url_decode($value){
        $padding = strlen($value) % 4;
        if($padding){
            $value .= str_repeat('=', 4 - $padding);
        }
        return base64_decode(strtr($value, '-_', '+/'), true);
    }
}

if(!function_exists('cg1l_create_vote_comment_follow_up_token')){
    function cg1l_create_vote_comment_follow_up_token($voteId,$galleryId,$pictureId,$voteValue){
        $payload = array(
            'vote_id' => absint($voteId),
            'gallery_id' => absint($galleryId),
            'picture_id' => absint($pictureId),
            'vote' => absint($voteValue),
            'expires' => time() + 1800,
        );
        $payloadJson = wp_json_encode($payload);
        if($payloadJson === false){
            return '';
        }
        $encodedPayload = cg1l_vote_comment_base64url_encode($payloadJson);
        $signature = hash_hmac('sha256', $encodedPayload, wp_salt('auth'));
        return $encodedPayload.'.'.$signature;
    }
}

if(!function_exists('cg1l_validate_vote_comment_follow_up_token')){
    function cg1l_validate_vote_comment_follow_up_token($token,$galleryId,$pictureId){
        if(!is_string($token) || strpos($token, '.') === false){
            return false;
        }

        $tokenParts = explode('.', $token, 2);
        $expectedSignature = hash_hmac('sha256', $tokenParts[0], wp_salt('auth'));
        $signatureMatches = function_exists('hash_equals') ? hash_equals($expectedSignature, $tokenParts[1]) : ($expectedSignature === $tokenParts[1]);
        if(!$signatureMatches){
            return false;
        }

        $payloadJson = cg1l_vote_comment_base64url_decode($tokenParts[0]);
        $payload = ($payloadJson !== false) ? json_decode($payloadJson, true) : false;
        if(
            !is_array($payload) ||
            empty($payload['vote_id']) ||
            empty($payload['gallery_id']) ||
            empty($payload['picture_id']) ||
            empty($payload['vote']) ||
            empty($payload['expires']) ||
            absint($payload['gallery_id']) !== absint($galleryId) ||
            absint($payload['picture_id']) !== absint($pictureId) ||
            absint($payload['expires']) < time()
        ){
            return false;
        }

        return array(
            'vote_id' => absint($payload['vote_id']),
            'gallery_id' => absint($payload['gallery_id']),
            'picture_id' => absint($payload['picture_id']),
            'vote' => absint($payload['vote']),
        );
    }
}

if(!function_exists('cg1l_vote_comment_error_script')){
    function cg1l_vote_comment_error_script($galeryIDuser,$status){
        ?>
        <script data-cg-processing="true">
            var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
            var status = <?php echo json_encode($status);?>;
            if(cgJsData[galeryIDuser] && cgJsData[galeryIDuser].vars){
                cgJsData[galeryIDuser].vars.voteCommentSubmitResponse = {status: status};
            }
            if(cgJsClass.gallery.rating.handleVoteCommentServerResponse){
                cgJsClass.gallery.rating.handleVoteCommentServerResponse(galeryIDuser,status);
            }
        </script>
        <?php
    }
}

if(!function_exists('cg1l_process_vote_comment_request')){
    function cg1l_process_vote_comment_request($options,$galeryID,$galeryIDuser,$pictureID,$galleryHash,$rateValue,&$ratingCommentsData,&$lockFp,&$voteCommentWasSaved,&$voteCommentInsertId,$paidCommentReservation = null){
        $voteCommentWasSaved = false;
        $voteCommentInsertId = 0;
        $mode = cg1l_get_vote_comment_mode($options);
        if($mode === 'disabled'){
            return true;
        }

        $comment = '';
        $name = '';
        if(isset($_REQUEST['voteCommentText']) && is_scalar($_REQUEST['voteCommentText'])){
            $comment = trim(wp_unslash((string)$_REQUEST['voteCommentText']));
        }
        if(isset($_REQUEST['voteCommentName']) && is_scalar($_REQUEST['voteCommentName'])){
            $name = trim(wp_unslash((string)$_REQUEST['voteCommentName']));
        }

        if($comment === ''){
            if($mode === 'required'){
                cg1l_vote_comment_error_script($galeryIDuser,'comment_required');
                return false;
            }
            return true;
        }

        $postBackup = $_POST;
        $_POST['gid'] = $galeryID;
        $_POST['galeryIDuser'] = $galeryIDuser;
        $_POST['galleryHash'] = $galleryHash;
        $_POST['cgPageUrl'] = (isset($_REQUEST['cgPageUrl']) && is_scalar($_REQUEST['cgPageUrl'])) ? wp_unslash((string)$_REQUEST['cgPageUrl']) : '';
        $_POST['pid'] = $pictureID;
        $_POST['name'] = $name;
        $_POST['comment'] = $comment;

        $cg1lCommentVoteValue = absint($rateValue);

        $cg1lCommentStatsLockAlreadyAcquired = true;
        $GLOBALS['cg1lCommentSubmitResult'] = array('status' => 'blocked');
        if(is_array($paidCommentReservation)){
            $GLOBALS['cgPaidActionCommentReservation'] = $paidCommentReservation;
        }

        ob_start();
        include(dirname(dirname(__DIR__)).'/v10/v10-frontend/data/comment/show-set-comments-v10.php');
        $commentResponse = ob_get_clean();
        unset($GLOBALS['cgPaidActionCommentReservation']);
        $_POST = $postBackup;

        $result = $GLOBALS['cg1lCommentSubmitResult'];
        unset($GLOBALS['cg1lCommentSubmitResult']);
        if(empty($result['status']) || $result['status'] !== 'success'){
            $status = (!empty($result['status'])) ? $result['status'] : 'storage_error';
            cg1l_vote_comment_error_script($galeryIDuser,$status);
            return false;
        }

        echo $commentResponse;
        $voteCommentWasSaved = true;
        $voteCommentInsertId = (!empty($result['insert_id'])) ? absint($result['insert_id']) : 0;
        return true;
    }
}

if(!function_exists('cg1l_prepare_paid_vote_reservations')){
    function cg1l_prepare_paid_vote_reservations($options,$galleryId,$pictureId,$rateValue){
        $reservations = array('vote'=>null,'comment'=>null);
        if(!function_exists('cg_paid_actions_maybe_reserve')){
            return $reservations;
        }

        $context = 'vote:'.absint($pictureId).':'.absint($rateValue);
        $reservations['vote'] = cg_paid_actions_maybe_reserve($galleryId,'vote',1,$context);
        if(is_wp_error($reservations['vote'])){
			$reservations['vote']->add_data(array('paidActionType'=>'vote'),$reservations['vote']->get_error_code());
            return $reservations['vote'];
        }

        $comment = '';
        if(isset($_REQUEST['voteCommentText']) && is_scalar($_REQUEST['voteCommentText'])){
            $comment = trim(wp_unslash((string)$_REQUEST['voteCommentText']));
        }
        if($comment!=='' && cg1l_get_vote_comment_mode($options)!=='disabled'){
            $reservations['comment'] = cg_paid_actions_maybe_reserve($galleryId,'comment',1,$context.':comment');
            if(is_wp_error($reservations['comment'])){
                cg_paid_actions_release_reservation($reservations['vote']);
				$reservations['comment']->add_data(array('paidActionType'=>'comment'),$reservations['comment']->get_error_code());
                return $reservations['comment'];
            }
        }

        return $reservations;
    }
}

if(!function_exists('cg1l_paid_action_error_type')){
	function cg1l_paid_action_error_type($error,$fallback = 'vote'){
		if(is_wp_error($error)){
			$data = $error->get_error_data();
			if(is_array($data) && !empty($data['paidActionType']) && in_array($data['paidActionType'],array('vote','comment'),true)){
				return $data['paidActionType'];
			}
		}
		return $fallback;
	}
}

if(!function_exists('cg1l_settle_paid_vote_reservations')){
    function cg1l_settle_paid_vote_reservations($reservations,$voteWasSaved,$voteInsertId,$commentWasSaved,$commentInsertId){
        if(!is_array($reservations)){return;}
        if(is_array($reservations['vote'])){
            cg_paid_actions_settle_reservation($reservations['vote'],$voteWasSaved ? 1 : 0,$voteWasSaved ? array($voteInsertId) : array());
        }
        if(is_array($reservations['comment'])){
            cg_paid_actions_settle_reservation($reservations['comment'],$commentWasSaved ? 1 : 0,$commentWasSaved ? array($commentInsertId) : array());
        }
    }
}

if(!function_exists('cg1l_prepare_comment_file_deletion')){
    function cg1l_prepare_comment_file_deletion($galleryId,$pictureId,$commentIds,&$commentsLockFp){
        $galleryId = absint($galleryId);
        $pictureId = absint($pictureId);
        $commentsLockFp = false;
        $normalizedCommentIds = array();

        if(empty($galleryId) || empty($pictureId) || !is_array($commentIds)){
            return false;
        }

        foreach($commentIds as $commentId){
            $commentId = absint($commentId);
            if(!empty($commentId)){
                $normalizedCommentIds[$commentId] = $commentId;
            }
        }

        if(empty($normalizedCommentIds)){
            return array(
                'comments_file' => '',
                'comments_raw' => null,
                'comments_data' => array(),
                'single_backups' => array(),
                'comment_keys' => array(),
            );
        }

        $commentsFile = cg1l_get_comments_lock_for_update($galleryId,$pictureId,$commentsLockFp);
        if(empty($commentsFile) || !file_exists($commentsFile)){
            cg1l_release_comment_lock($commentsLockFp);
            return false;
        }

        $commentsRaw = file_get_contents($commentsFile);
        $commentsData = ($commentsRaw !== false && $commentsRaw !== '') ? json_decode($commentsRaw,true) : false;
        if(!is_array($commentsData)){
            cg1l_release_comment_lock($commentsLockFp);
            return false;
        }

        $commentKeys = array();
        foreach($commentsData as $commentKey => $commentData){
            if(!is_array($commentData) || empty($commentData['insert_id'])){
                continue;
            }
            $insertId = absint($commentData['insert_id']);
            if(!empty($normalizedCommentIds[$insertId])){
                $commentKeys[$insertId] = (string)$commentKey;
            }
        }

        if(count($commentKeys) !== count($normalizedCommentIds)){
            cg1l_release_comment_lock($commentsLockFp);
            return false;
        }

        $wpUploadDir = wp_upload_dir();
        $singleCommentsDir = $wpUploadDir['basedir'].'/contest-gallery/gallery-id-'.$galleryId.'/json/image-comments/ids/'.$pictureId;
        $singleBackups = array();

        foreach($commentKeys as $commentKey){
            $singleCommentFile = $singleCommentsDir.'/'.$commentKey.'.json';
            $singleBackups[$singleCommentFile] = file_exists($singleCommentFile) ? file_get_contents($singleCommentFile) : null;
            if($singleBackups[$singleCommentFile] === false){
                cg1l_release_comment_lock($commentsLockFp);
                return false;
            }
            unset($commentsData[$commentKey]);
        }

        return array(
            'comments_file' => $commentsFile,
            'comments_raw' => $commentsRaw,
            'comments_data' => $commentsData,
            'single_backups' => $singleBackups,
            'comment_keys' => $commentKeys,
        );
    }
}

if(!function_exists('cg1l_restore_comment_file_deletion')){
    function cg1l_restore_comment_file_deletion($deletionPlan){
        if(!is_array($deletionPlan)){
            return;
        }

        if(!empty($deletionPlan['single_backups']) && is_array($deletionPlan['single_backups'])){
            foreach($deletionPlan['single_backups'] as $singleCommentFile => $singleCommentRaw){
                if($singleCommentRaw === null){
                    if(file_exists($singleCommentFile)){
                        unlink($singleCommentFile);
                    }
                }else{
                    cg1l_write_atomic_file_payload($singleCommentFile,$singleCommentRaw);
                }
            }
        }

        if(!empty($deletionPlan['comments_file']) && $deletionPlan['comments_raw'] !== null){
            cg1l_write_atomic_file_payload($deletionPlan['comments_file'],$deletionPlan['comments_raw']);
        }
    }
}

if(!function_exists('cg1l_apply_comment_file_deletion')){
    function cg1l_apply_comment_file_deletion($deletionPlan){
        if(!is_array($deletionPlan) || empty($deletionPlan['comments_file'])){
            return empty($deletionPlan['comment_keys']);
        }

        if(!empty($deletionPlan['single_backups']) && is_array($deletionPlan['single_backups'])){
            foreach($deletionPlan['single_backups'] as $singleCommentFile => $singleCommentRaw){
                if(file_exists($singleCommentFile) && !unlink($singleCommentFile)){
                    cg1l_restore_comment_file_deletion($deletionPlan);
                    return false;
                }
            }
        }

        $commentsPayload = json_encode($deletionPlan['comments_data']);
        if($commentsPayload === false || !cg1l_write_atomic_file_payload($deletionPlan['comments_file'],$commentsPayload)){
            cg1l_restore_comment_file_deletion($deletionPlan);
            return false;
        }

        return true;
    }
}

if(!function_exists('cg1l_get_comment_counts_from_data')){
    function cg1l_get_comment_counts_from_data($commentsData){
        $counts = array(
            'total' => 0,
            'to_review' => 0,
            'hidden' => 0,
        );

        if(!is_array($commentsData)){
            return $counts;
        }

        foreach($commentsData as $commentData){
            if(!is_array($commentData)){
                continue;
            }
            $counts['total']++;
            if(!empty($commentData['Active']) && intval($commentData['Active']) === 2){
                $counts['hidden']++;
                if(empty($commentData['ReviewTstamp'])){
                    $counts['to_review']++;
                }
            }
        }

        return $counts;
    }
}

if(!function_exists('cg1l_mark_vote_comment_data_updated')){
    function cg1l_mark_vote_comment_data_updated($galleryId,$pictureId){
        if(function_exists('cg1l_push_recent_id_file')){
            cg1l_push_recent_id_file($galleryId,$pictureId,'image-comments-data-last-update');
            cg1l_push_recent_id_file($galleryId,$pictureId,'image-stats-data-last-update');
        }
        if(function_exists('cg1l_create_last_updated_time_file')){
            cg1l_create_last_updated_time_file($galleryId,'image-comments-data-last-update');
            cg1l_create_last_updated_time_file($galleryId,'image-stats-data-last-update');
        }
    }
}

if(!function_exists('cg1l_delete_vote_with_linked_comment')){
    function cg1l_delete_vote_with_linked_comment($galleryId,$pictureId,$voteId,$commentId,&$ratingFileData){
        global $wpdb;

        $galleryId = absint($galleryId);
        $pictureId = absint($pictureId);
        $voteId = absint($voteId);
        $commentId = absint($commentId);
        $tablename = $wpdb->prefix.'contest_gal1ery';
        $tablenameIP = $wpdb->prefix.'contest_gal1ery_ip';
        $tablenameComments = $wpdb->prefix.'contest_gal1ery_comments';
        $commentsLockFp = false;
        $commentDeletionPlan = false;
        $commentCounts = false;

        if(empty($galleryId) || empty($pictureId) || empty($voteId)){
            return false;
        }

        if(!empty($commentId)){
            $linkedCommentExists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $tablenameComments WHERE id = %d AND pid = %d AND GalleryID = %d LIMIT 1",
                $commentId,$pictureId,$galleryId
            ));
            if(!empty($linkedCommentExists)){
                $commentDeletionPlan = cg1l_prepare_comment_file_deletion($galleryId,$pictureId,array($commentId),$commentsLockFp);
                if($commentDeletionPlan === false || !cg1l_apply_comment_file_deletion($commentDeletionPlan)){
                    cg1l_release_comment_lock($commentsLockFp);
                    return false;
                }
                $commentCounts = cg1l_get_comment_counts_from_data($commentDeletionPlan['comments_data']);
            }else{
                $commentId = 0;
            }
        }

        $databaseWriteFailed = false;
        $wpdb->query('START TRANSACTION');
        $deletedVote = $wpdb->delete(
            $tablenameIP,
            array('id' => $voteId, 'pid' => $pictureId, 'GalleryID' => $galleryId),
            array('%d','%d','%d')
        );
        if($deletedVote === false){
            $databaseWriteFailed = true;
        }

        if(!$databaseWriteFailed && !empty($commentId)){
            $deletedComment = $wpdb->delete(
                $tablenameComments,
                array('id' => $commentId, 'pid' => $pictureId, 'GalleryID' => $galleryId),
                array('%d','%d','%d')
            );
            if($deletedComment === false){
                $databaseWriteFailed = true;
            }
        }

        if($databaseWriteFailed){
            $wpdb->query('ROLLBACK');
            if($commentDeletionPlan !== false){
                cg1l_restore_comment_file_deletion($commentDeletionPlan);
            }
            cg1l_release_comment_lock($commentsLockFp);
            return false;
        }

        $wpdb->query('COMMIT');
        cg1l_release_comment_lock($commentsLockFp);

        if(is_array($commentCounts)){
            $wpdb->update(
                $tablename,
                array('CountC' => intval($commentCounts['total']), 'CountCtoReview' => intval($commentCounts['to_review'])),
                array('id' => $pictureId, 'GalleryID' => $galleryId),
                array('%d','%d'),
                array('%d','%d')
            );
            if(is_array($ratingFileData)){
                $ratingFileData['CountC'] = intval($commentCounts['total']);
                $ratingFileData['CountCtoReview'] = intval($commentCounts['hidden']);
            }
        }

        return array(
            'comment_keys' => ($commentDeletionPlan !== false) ? array_values($commentDeletionPlan['comment_keys']) : array(),
            'comment_ids' => !empty($commentId) ? array($commentId) : array(),
            'comment_counts' => $commentCounts,
        );
    }
}

if(!function_exists('cg1l_get_user_vote_values_for_image')){
    function cg1l_get_user_vote_values_for_image($galleryId,$pictureId,$isOneStar,$checkLogin,$checkCookie,$checkIp,$wpUserId,$userIp,$cookieId){
        global $wpdb;

        $galleryId = absint($galleryId);
        $pictureId = absint($pictureId);
        $isOneStar = !empty($isOneStar);
        $checkLogin = !empty($checkLogin);
        $checkCookie = !empty($checkCookie);
        $checkIp = !empty($checkIp);
        $wpUserId = absint($wpUserId);
        $userIp = (string) $userIp;
        $cookieId = (string) $cookieId;
        $tablenameIP = $wpdb->prefix.'contest_gal1ery_ip';
        $ratingColumn = $isOneStar ? 'RatingS' : 'Rating';
        $comparison = $isOneStar ? '= %d' : '>= %d';
        $query = '';
        $values = array();

        if(empty($galleryId) || empty($pictureId)){
            return $values;
        }

        if($checkLogin && $wpUserId>0 && is_user_logged_in()){
            $query = $wpdb->prepare(
                "SELECT {$ratingColumn} FROM {$tablenameIP} WHERE {$ratingColumn} {$comparison} AND WpUserId = %d AND GalleryID = %d AND pid = %d ORDER BY id ASC",
                1,$wpUserId,$galleryId,$pictureId
            );
        }elseif($checkCookie && !$checkIp && $cookieId!==''){
            $query = $wpdb->prepare(
                "SELECT {$ratingColumn} FROM {$tablenameIP} WHERE {$ratingColumn} {$comparison} AND CookieId = %s AND GalleryID = %d AND pid = %d ORDER BY id ASC",
                1,$cookieId,$galleryId,$pictureId
            );
        }elseif($checkIp && !$checkCookie && $userIp!==''){
            $query = $wpdb->prepare(
                "SELECT {$ratingColumn} FROM {$tablenameIP} WHERE {$ratingColumn} {$comparison} AND IP = %s AND GalleryID = %d AND pid = %d ORDER BY id ASC",
                1,$userIp,$galleryId,$pictureId
            );
        }elseif($checkIp && $checkCookie && $userIp!=='' && $cookieId!==''){
            $query = $wpdb->prepare(
                "SELECT {$ratingColumn} FROM {$tablenameIP} WHERE {$ratingColumn} {$comparison} AND IP = %s AND CookieId = %s AND GalleryID = %d AND pid = %d ORDER BY id ASC",
                1,$userIp,$cookieId,$galleryId,$pictureId
            );
        }

        if($query===''){
            return $values;
        }

        $rows = $wpdb->get_col($query);
        if(!is_array($rows)){
            return $values;
        }

        foreach($rows as $rowValue){
            $rowValue = intval($rowValue);
            if($rowValue>0){
                $values[] = $rowValue;
            }
        }

        return $values;
    }
}

if(!function_exists('cg1l_sync_rating_file_data_from_database')){
    function cg1l_sync_rating_file_data_from_database($galleryId,$pictureId,$isOneStar,$includeRatingDistribution,&$ratingFileData){
        global $wpdb;

        $galleryId = absint($galleryId);
        $pictureId = absint($pictureId);
        $tablename = $wpdb->prefix.'contest_gal1ery';

        if(empty($galleryId) || empty($pictureId) || !is_array($ratingFileData)){
            return false;
        }

        if(!empty($isOneStar)){
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT CountS FROM {$tablename} WHERE id = %d AND GalleryID = %d LIMIT 1",
                $pictureId,$galleryId
            ),ARRAY_A);
            if(!is_array($row)){
                return false;
            }
            $ratingFileData['CountS'] = max(0,intval($row['CountS']));
            return true;
        }

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT CountR, Rating, CountR1, CountR2, CountR3, CountR4, CountR5, CountR6, CountR7, CountR8, CountR9, CountR10 FROM {$tablename} WHERE id = %d AND GalleryID = %d LIMIT 1",
            $pictureId,$galleryId
        ),ARRAY_A);
        if(!is_array($row)){
            return false;
        }

        $ratingFileData['CountR'] = max(0,intval($row['CountR']));
        $ratingFileData['Rating'] = max(0,intval($row['Rating']));

        if(!empty($includeRatingDistribution)){
            for($ratingValue=1;$ratingValue<=10;$ratingValue++){
                $ratingFileData['CountR'.$ratingValue] = max(0,intval($row['CountR'.$ratingValue]));
            }
        }

        return true;
    }
}
