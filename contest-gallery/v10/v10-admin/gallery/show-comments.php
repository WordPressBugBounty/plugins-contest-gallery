<?php

global $wpdb;
$tablename_comments = $wpdb->prefix . "contest_gal1ery_comments";
$tablename = $wpdb->prefix . "contest_gal1ery";
$tablenameWpUsers = $wpdb->prefix . "users";
$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
$tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";

$table_posts = $wpdb->prefix."posts";
$table_wp_users = $wpdb->base_prefix."users";

$galeryNR=absint($_GET['option_id']);
$pid=0;

if(!empty($_GET['id'])){
    $pid=absint($_GET['id']);
}

$GalleryID = $galeryNR;

// Get gallery options by ID
$cgOptions = $wpdb->get_row($wpdb->prepare(
        "SELECT GalleryName, Version 
    FROM $tablenameOptions 
    WHERE id = %d",
        $galeryNR
));

if(empty($cgOptions)){
    echo '<div class="cg_backend_info_container"><b>Gallery not found.</b></div>';
    return;
}

$imageData = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $tablename WHERE id = %d AND GalleryID = %d LIMIT 1",
    $pid,
    $galeryNR
));

if(empty($imageData)){
    echo '<div class="cg_backend_info_container"><b>Entry does not belong to this gallery.</b></div>';
    return;
}

$GalleryName = $cgOptions->GalleryName;
$Version = $cgOptions->Version;

// Fetch professional options by Gallery ID
$proOptions = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $tablename_pro_options 
    WHERE GalleryID = %d",
        $GalleryID
));

if(empty($proOptions)){
    $proOptions = (object)array();
}

$IsModernFiveStar = (!empty($proOptions->IsModernFiveStar)) ? true : false;

if(empty($GalleryName)){
    $GalleryName = 'Contest Gallery';
}
include(__DIR__."/../nav-menu.php");
include(__DIR__.'/../../../vars/general/emojis.php');

$wp_upload_dir = wp_upload_dir();
$dirImageComments = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryNR.'/json/image-comments/ids/'.$pid;

$optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryNR.'/json/'.$galeryNR.'-options.json';
if(!is_readable($optionsFile)){
    echo '<div class="cg_backend_info_container"><b>Gallery options could not be loaded.</b></div>';
    return;
}
$options = json_decode(file_get_contents($optionsFile),true);
if(!is_array($options)){
    echo '<div class="cg_backend_info_container"><b>Gallery options are invalid.</b></div>';
    return;
}
if(!empty($options[$galeryNR])){
    $options = $options[$galeryNR];
}

if(!function_exists('cg1l_admin_count_comments_to_review')){
    function cg1l_admin_count_comments_to_review($dirImageComments){
        $countCtoReview = 0;

        if(is_dir($dirImageComments)){
            $dirImageCommentsFiles = glob($dirImageComments.'/*.json');
            if(!empty($dirImageCommentsFiles)){
                foreach ($dirImageCommentsFiles as $dirImageCommentsFile){
                    $dirImageCommentsFileData = json_decode(file_get_contents($dirImageCommentsFile),true);
                    if(!empty($dirImageCommentsFileData) && is_array($dirImageCommentsFileData)){
                        $commentKey = key($dirImageCommentsFileData);
                        if(isset($dirImageCommentsFileData[$commentKey]) && is_array($dirImageCommentsFileData[$commentKey]) && !empty($dirImageCommentsFileData[$commentKey]['Active']) && $dirImageCommentsFileData[$commentKey]['Active']==2 && empty($dirImageCommentsFileData[$commentKey]['ReviewTstamp'])){
                            $countCtoReview++;
                        }
                    }
                }
            }
        }

        return $countCtoReview;
    }
}

if(!function_exists('cg1l_admin_count_hidden_comments_for_frontend')){
    function cg1l_admin_count_hidden_comments_for_frontend($dirImageComments){
        $countHiddenComments = 0;

        if(is_dir($dirImageComments)){
            $dirImageCommentsFiles = glob($dirImageComments.'/*.json');
            if(!empty($dirImageCommentsFiles)){
                foreach ($dirImageCommentsFiles as $dirImageCommentsFile){
                    $dirImageCommentsFileData = json_decode(file_get_contents($dirImageCommentsFile),true);
                    if(!empty($dirImageCommentsFileData) && is_array($dirImageCommentsFileData)){
                        $commentKey = key($dirImageCommentsFileData);
                        if(isset($dirImageCommentsFileData[$commentKey]) && is_array($dirImageCommentsFileData[$commentKey]) && !empty($dirImageCommentsFileData[$commentKey]['Active']) && $dirImageCommentsFileData[$commentKey]['Active']==2){
                            $countHiddenComments++;
                        }
                    }
                }
            }
        }

        return $countHiddenComments;
    }
}

if(!function_exists('cg1l_admin_refresh_comment_frontend_data')){
    function cg1l_admin_refresh_comment_frontend_data($galeryNR, $pid, $countCommentsTotal, $countHiddenCommentsForFrontend){
        $galeryNR = absint($galeryNR);
        $pid = absint($pid);

        if(empty($galeryNR) || empty($pid)){
            return;
        }

        if(function_exists('cg1l_get_stats_for_update') && function_exists('cg1l_set_stats_with_lock')){
            $lockFp = false;
            $ratingFileData = cg1l_get_stats_for_update($galeryNR, $pid, $lockFp);
            if(is_array($ratingFileData)){
                $ratingFileData['CountC'] = intval($countCommentsTotal);
                $ratingFileData['CountCtoReview'] = intval($countHiddenCommentsForFrontend);
                cg1l_set_stats_with_lock($galeryNR, $pid, $ratingFileData, $lockFp);
            }else if(function_exists('cg1l_release_stats_lock')){
                cg1l_release_stats_lock($lockFp);
            }
        }

        if(function_exists('cg1l_push_recent_id_file')){
            cg1l_push_recent_id_file($galeryNR,$pid,'image-comments-data-last-update');
            cg1l_push_recent_id_file($galeryNR,$pid,'image-stats-data-last-update');
        }

        if(function_exists('cg1l_create_last_updated_time_file')){
            cg1l_create_last_updated_time_file($galeryNR,'image-comments-data-last-update');
            cg1l_create_last_updated_time_file($galeryNR,'image-stats-data-last-update');
        }
    }
}

if(!function_exists('cg1l_render_admin_comment_action_checkbox')){
    function cg1l_render_admin_comment_action_checkbox($label, $id, $inputClass, $name, $value, $disabled, $actionClass, $extraClass){
        $inputClassAttr = ($inputClass) ? ' class="'.esc_attr($inputClass).'"' : '';
        $nameAttr = ($name !== '') ? ' name="'.esc_attr($name).'"' : '';
        $valueAttr = ($value !== '') ? ' value="'.esc_attr($value).'"' : '';
        $disabledAttr = ($disabled) ? ' disabled' : '';
        $disabledClass = ($disabled) ? ' cg_comment_action_disabled' : '';
        $extraClassAttr = ($extraClass) ? ' '.esc_attr($extraClass) : '';

        return '<label class="cg_comment_action cg_comment_action_'.esc_attr($actionClass).$extraClassAttr.$disabledClass.'" for="'.esc_attr($id).'">'.
            '<input id="'.esc_attr($id).'"'.$inputClassAttr.' type="checkbox"'.$nameAttr.$valueAttr.$disabledAttr.'>'.
            '<span class="cg_comment_action_text">'.esc_html($label).'</span>'.
            '<span class="cg_comment_action_icon" aria-hidden="true"></span>'.
        '</label>';
    }
}

if(!function_exists('cg1l_admin_get_comment_action_ids')){
    function cg1l_admin_get_comment_action_ids($postKey,$commentsArray){
        $result = array(
            'valid' => true,
            'ids' => array(),
        );

        if(empty($_POST[$postKey])){
            return $result;
        }

        if(!is_array($_POST[$postKey])){
            $result['valid'] = false;
            return $result;
        }

        foreach(wp_unslash($_POST[$postKey]) as $commentId){
            if(!is_scalar($commentId)){
                $result['valid'] = false;
                return $result;
            }

            $commentId = (string)$commentId;
            if(
                $commentId === '' ||
                strlen($commentId) > 100 ||
                !preg_match('/^[A-Za-z0-9_-]+$/D',$commentId) ||
                !array_key_exists($commentId,$commentsArray) ||
                !is_array($commentsArray[$commentId])
            ){
                $result['valid'] = false;
                return $result;
            }

            $result['ids'][$commentId] = $commentId;
        }

        $result['ids'] = array_values($result['ids']);
        return $result;
    }
}

if(!function_exists('cg1l_admin_get_comment_insert_id')){
    function cg1l_admin_get_comment_insert_id($commentId,$commentData){
        if(is_array($commentData) && !empty($commentData['insert_id'])){
            return absint($commentData['insert_id']);
        }
        if(ctype_digit((string)$commentId)){
            return absint($commentId);
        }
        return 0;
    }
}

if(!function_exists('cg1l_admin_load_single_comment_file')){
    function cg1l_admin_load_single_comment_file($commentFile,$commentId,$fallbackData,&$originalRaw){
        $originalRaw = null;

        if(!file_exists($commentFile)){
            return array($commentId => $fallbackData);
        }

        $originalRaw = file_get_contents($commentFile);
        if($originalRaw === false || $originalRaw === ''){
            return false;
        }

        $commentFileData = json_decode($originalRaw,true);
        if(
            !is_array($commentFileData) ||
            !isset($commentFileData[$commentId]) ||
            !is_array($commentFileData[$commentId])
        ){
            return false;
        }

        return $commentFileData;
    }
}

if(!function_exists('cg1l_admin_restore_comment_files')){
    function cg1l_admin_restore_comment_files($fileBackups){
        foreach($fileBackups as $commentFile => $originalRaw){
            if($originalRaw === null){
                if(file_exists($commentFile)){
                    unlink($commentFile);
                }
            }else{
                cg1l_write_atomic_file_payload($commentFile,$originalRaw);
            }
        }
    }
}

if(!function_exists('cg1l_admin_comment_action_error')){
    function cg1l_admin_comment_action_error($message){
        echo '<div class="cg_backend_info_container"><b>'.esc_html($message).'</b></div>';
    }
}

// SQL zum Ermitteln von allen Komments mit gesendeter picture id
// DATEN Löschen und exit

	if (!empty($_POST['delete-comment']) || !empty($_POST['activate-comment']) || !empty($_POST['deactivate-comment'])) {

        cg_require_backend_access();

        $commentsLockFp = false;
        $jsonFile = cg1l_get_comments_lock_for_update($galeryNR,$pid,$commentsLockFp);
        if(empty($jsonFile) || !file_exists($jsonFile)){
            cg1l_release_comment_lock($commentsLockFp);
            cg1l_admin_comment_action_error('Comment data could not be loaded.');
            return;
        }

        $commentsArrayRaw = file_get_contents($jsonFile);
        $commentsArray = ($commentsArrayRaw !== false && $commentsArrayRaw !== '') ? json_decode($commentsArrayRaw,true) : false;
        if(!is_array($commentsArray)){
            cg1l_release_comment_lock($commentsLockFp);
            cg1l_admin_comment_action_error('Comment data is invalid. No changes were saved.');
            return;
        }

        $deleteAction = cg1l_admin_get_comment_action_ids('delete-comment',$commentsArray);
        $activateAction = cg1l_admin_get_comment_action_ids('activate-comment',$commentsArray);
        $deactivateAction = cg1l_admin_get_comment_action_ids('deactivate-comment',$commentsArray);

        if(!$deleteAction['valid'] || !$activateAction['valid'] || !$deactivateAction['valid']){
            cg1l_release_comment_lock($commentsLockFp);
            cg1l_admin_comment_action_error('Invalid comment action. No changes were saved.');
            return;
        }

        if(
            count(array_intersect($deleteAction['ids'],$activateAction['ids'])) ||
            count(array_intersect($deleteAction['ids'],$deactivateAction['ids'])) ||
            count(array_intersect($activateAction['ids'],$deactivateAction['ids']))
        ){
            cg1l_release_comment_lock($commentsLockFp);
            cg1l_admin_comment_action_error('A comment can have only one action. No changes were saved.');
            return;
        }

        $fileImageCommentsDir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryNR.'/json/image-comments/ids/'.$pid;
        if(!is_dir($fileImageCommentsDir) && !wp_mkdir_p($fileImageCommentsDir)){
            cg1l_release_comment_lock($commentsLockFp);
            cg1l_admin_comment_action_error('Comment directory could not be prepared.');
            return;
        }

        $fileBackups = array();
        $filesToDelete = array();
        $databaseActions = array();
        $hasNewReviewed = false;
        $storageFailed = false;
        $unix = time();

        foreach($deleteAction['ids'] as $commentId){
            $commentFile = $fileImageCommentsDir.'/'.$commentId.'.json';
            $originalRaw = null;
            if(cg1l_admin_load_single_comment_file($commentFile,$commentId,$commentsArray[$commentId],$originalRaw) === false){
                $storageFailed = true;
                break;
            }

            $fileBackups[$commentFile] = $originalRaw;
            $filesToDelete[] = $commentFile;
            $databaseActions[] = array(
                'action' => 'delete',
                'insert_id' => cg1l_admin_get_comment_insert_id($commentId,$commentsArray[$commentId]),
            );
            unset($commentsArray[$commentId]);
        }

        if(!$storageFailed){
            foreach($activateAction['ids'] as $commentId){
                $commentFile = $fileImageCommentsDir.'/'.$commentId.'.json';
                $originalRaw = null;
                $commentFileData = cg1l_admin_load_single_comment_file($commentFile,$commentId,$commentsArray[$commentId],$originalRaw);
                if($commentFileData === false){
                    $storageFailed = true;
                    break;
                }

                $fileBackups[$commentFile] = $originalRaw;
                if(
                    empty($commentFileData[$commentId]['ReviewTstamp']) &&
                    isset($commentFileData[$commentId]['Active']) &&
                    intval($commentFileData[$commentId]['Active']) === 2
                ){
                    $hasNewReviewed = true;
                }

                $commentFileData[$commentId]['Active'] = 1;
                $commentsArray[$commentId]['Active'] = 1;
                if(empty($commentFileData[$commentId]['ReviewTstamp'])){
                    $commentFileData[$commentId]['ReviewTstamp'] = $unix;
                }
                if(empty($commentsArray[$commentId]['ReviewTstamp'])){
                    $commentsArray[$commentId]['ReviewTstamp'] = $unix;
                }

                $commentFilePayload = json_encode($commentFileData);
                if($commentFilePayload === false || !cg1l_write_atomic_file_payload($commentFile,$commentFilePayload)){
                    $storageFailed = true;
                    break;
                }

                $databaseActions[] = array(
                    'action' => 'activate',
                    'insert_id' => cg1l_admin_get_comment_insert_id($commentId,$commentsArray[$commentId]),
                );
            }
        }

        if(!$storageFailed){
            foreach($deactivateAction['ids'] as $commentId){
                $commentFile = $fileImageCommentsDir.'/'.$commentId.'.json';
                $originalRaw = null;
                $commentFileData = cg1l_admin_load_single_comment_file($commentFile,$commentId,$commentsArray[$commentId],$originalRaw);
                if($commentFileData === false){
                    $storageFailed = true;
                    break;
                }

                $fileBackups[$commentFile] = $originalRaw;
                $commentFileData[$commentId]['Active'] = 2;
                $commentsArray[$commentId]['Active'] = 2;
                if(empty($commentFileData[$commentId]['ReviewTstamp'])){
                    $commentFileData[$commentId]['ReviewTstamp'] = $unix;
                }
                if(empty($commentsArray[$commentId]['ReviewTstamp'])){
                    $commentsArray[$commentId]['ReviewTstamp'] = $unix;
                }

                $commentFilePayload = json_encode($commentFileData);
                if($commentFilePayload === false || !cg1l_write_atomic_file_payload($commentFile,$commentFilePayload)){
                    $storageFailed = true;
                    break;
                }

                $databaseActions[] = array(
                    'action' => 'deactivate',
                    'insert_id' => cg1l_admin_get_comment_insert_id($commentId,$commentsArray[$commentId]),
                );
            }
        }

        if(!$storageFailed){
            foreach($filesToDelete as $commentFile){
                if(file_exists($commentFile) && !unlink($commentFile)){
                    $storageFailed = true;
                    break;
                }
            }
        }

        $commentsArrayPayload = json_encode($commentsArray);
        if(
            !$storageFailed &&
            ($commentsArrayPayload === false || !cg1l_write_atomic_file_payload($jsonFile,$commentsArrayPayload))
        ){
            $storageFailed = true;
        }

        if($storageFailed){
            cg1l_admin_restore_comment_files($fileBackups);
            cg1l_write_atomic_file_payload($jsonFile,$commentsArrayRaw);
            cg1l_release_comment_lock($commentsLockFp);
            cg1l_admin_comment_action_error('Comment files could not be updated. No changes were saved.');
            return;
        }

        $databaseWriteFailed = false;
        $wpdb->query('START TRANSACTION');
        foreach($databaseActions as $databaseAction){
            if(empty($databaseAction['insert_id'])){
                continue;
            }

            if($databaseAction['action'] === 'delete'){
                $databaseResult = $wpdb->delete(
                    $tablename_comments,
                    array(
                        'id' => $databaseAction['insert_id'],
                        'pid' => $pid,
                        'GalleryID' => $galeryNR,
                    ),
                    array('%d','%d','%d')
                );
            }else{
                $databaseResult = $wpdb->update(
                    $tablename_comments,
                    array('Active' => ($databaseAction['action'] === 'activate') ? 1 : 2),
                    array(
                        'id' => $databaseAction['insert_id'],
                        'pid' => $pid,
                        'GalleryID' => $galeryNR,
                    ),
                    array('%d'),
                    array('%d','%d','%d')
                );
            }

            if($databaseResult === false){
                $databaseWriteFailed = true;
                break;
            }
        }

        if($databaseWriteFailed){
            $wpdb->query('ROLLBACK');
            cg1l_admin_restore_comment_files($fileBackups);
            cg1l_write_atomic_file_payload($jsonFile,$commentsArrayRaw);
            cg1l_release_comment_lock($commentsLockFp);
            cg1l_admin_comment_action_error('Comment database data could not be updated. No changes were saved.');
            return;
        }
        $wpdb->query('COMMIT');

        $countCommentsSQL = 0;
        if(floatval($options['general']['Version'])<16){
            $countCommentsSQL = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) AS NumberOfRows
                FROM $tablename_comments
                WHERE pid = %d AND GalleryID = %d",
                $pid,
                $galeryNR
            ));
        }

        $fileImageCommentsDirFiles = glob($fileImageCommentsDir.'/*.json');
        if(!is_array($fileImageCommentsDirFiles)){
            $fileImageCommentsDirFiles = array();
        }
        $fileImageCommentsDirCount = count($fileImageCommentsDirFiles);
        $countCommentsTotal = intval($countCommentsSQL)+$fileImageCommentsDirCount;
        $countCtoReview = cg1l_admin_count_comments_to_review($dirImageComments);
        $countHiddenCommentsForFrontend = cg1l_admin_count_hidden_comments_for_frontend($dirImageComments);

        $wpdb->update(
            $tablename,
            array('CountC' => $countCommentsTotal, 'CountCtoReview' => $countCtoReview),
            array('id' => $pid, 'GalleryID' => $galeryNR),
            array('%d','%d'),
            array('%d','%d')
        );

        cg1l_admin_refresh_comment_frontend_data($galeryNR, $pid, $countCommentsTotal, $countHiddenCommentsForFrontend);
        cg1l_release_comment_lock($commentsLockFp);

        if($hasNewReviewed){
            contest_gal1ery_user_comment_mail_prepare($options,$pid,$galeryNR,$wp_upload_dir,time());
        }

    }

    $countCtoReviewArray = [];
    if(is_dir($dirImageComments)){
        $dirImageCommentsFiles = glob($dirImageComments.'/*.json');
        if(!is_array($dirImageCommentsFiles)){
            $dirImageCommentsFiles = array();
        }
        foreach ($dirImageCommentsFiles as $dirImageCommentsFile){
            $dirImageCommentsFileData = json_decode(file_get_contents($dirImageCommentsFile),true);
            if(!empty($dirImageCommentsFileData) && is_array($dirImageCommentsFileData)){
                $commentKey = key($dirImageCommentsFileData);
                if(
                    isset($dirImageCommentsFileData[$commentKey]) &&
                    is_array($dirImageCommentsFileData[$commentKey]) &&
                    !empty($dirImageCommentsFileData[$commentKey]['Active']) &&
                    $dirImageCommentsFileData[$commentKey]['Active']==2 &&
                    empty($dirImageCommentsFileData[$commentKey]['ReviewTstamp'])
                ){
                    $countCtoReviewArray[$commentKey] = true;
                }
            }
        }
    }


// DATEN Löschen und exit ENDE	

        $ImgType = $imageData->ImgType;
        $WpUserId = $imageData->WpUserId;
        $WpUpload = $imageData->WpUpload;
        $widthOriginalImg = $imageData->Width;
        $heightOriginalImg = $imageData->Height;
        $rThumb = $imageData->rThumb;

        if(!empty($imageData->MultipleFiles) && $imageData->MultipleFiles!='""'){
            $MultipleFilesUnserialized = unserialize($imageData->MultipleFiles);
            if(!empty($MultipleFilesUnserialized)){//check for sure if really exists and unserialize went right, because might happen that "" was in database from earlier versions
                foreach($MultipleFilesUnserialized as $order => $MultipleFile){
                    if($order==1 && empty($MultipleFile['isRealIdSource'])){
                        $ImgType = (!empty($MultipleFile['ImgType'])) ? $MultipleFile['ImgType'] : 0;
                        $widthOriginalImg = (!empty($MultipleFile['Width'])) ? $MultipleFile['Width'] : 0;
                        $heightOriginalImg = (!empty($MultipleFile['Height'])) ? $MultipleFile['Height'] : 0;
                        $rThumb = (!empty($MultipleFile['rThumb'])) ? $MultipleFile['rThumb'] : '';
                        $WpUpload = (!empty($MultipleFile['WpUpload'])) ? $MultipleFile['WpUpload'] : 0;
                        break;
                    }
                }
            }
       }

        $user_login = '';
        if(!empty($WpUserId)){
            $user_login = $wpdb->get_var($wpdb->prepare(
                "SELECT user_login FROM $table_wp_users WHERE ID = %d LIMIT 1",
                $WpUserId
            ));
        }

        if(!empty($imageData->IP)){
            $userIP = $imageData->IP;
        }else{
            $userIP = 'User IP when uploading will be tracked since plugin version 10.9.3.7';
        }

        if(!empty($imageData->CookieId)){
            $CookieId = $imageData->CookieId;
        }else{
            $CookieId = '';
        }


        $image_url = '';
        $post_title = '';
        $post_description = '';
        $post_excerpt = '';
        $post_type = '';
        $wp_image_id = '';
        $sourceOriginalImgShow = '';
        $imageThumb = '';

        if($ImgType!='con' && !empty($WpUpload)){
            $wp_image_info = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_posts WHERE ID = %d LIMIT 1",
                $WpUpload
            ));
            if(!empty($wp_image_info)){
                $image_url = $wp_image_info->guid;
                $post_title = $wp_image_info->post_title;
                $post_description = $wp_image_info->post_content;
                $post_excerpt = $wp_image_info->post_excerpt;
                $post_type = $wp_image_info->post_mime_type;
                $wp_image_id = $wp_image_info->ID;
                $sourceOriginalImgShow = $image_url;
            }
        }

        if(cg_is_is_image($ImgType) && !empty($WpUpload) && $widthOriginalImg>0 && $heightOriginalImg>0){
            $imageThumbData = wp_get_attachment_image_src($WpUpload, 'large');
            if(!empty($imageThumbData[0])){
                $imageThumb = $imageThumbData[0];

                $WidthThumb = 300;
                $HeightThumb = 200;

                // Ermittlung der Höhe nach Skalierung. Falls unter der eingestellten Höhe, dann nächstgrößeres Bild nehmen.
                $heightScaledThumb = $WidthThumb*$heightOriginalImg/$widthOriginalImg;

                // Falls unter der eingestellten Höhe, dann größeres Bild nehmen (normales Bild oder panorama Bild, kein Vertikalbild)
                if ($heightScaledThumb <= $HeightThumb) {

                    // Bestimmung von Breite des Bildes
                    $WidthThumbPic = $HeightThumb*$widthOriginalImg/$heightOriginalImg;

                    // Bestimmung wie viel links und rechts abgeschnitten werden soll
                    $paddingLeftRight = ($WidthThumbPic-$WidthThumb)/2;
                    $paddingLeftRight = $paddingLeftRight.'px';

                    $padding = "left: -$paddingLeftRight;right: -$paddingLeftRight";

                    $WidthThumbPic = $WidthThumbPic.'px';

                }

                // Falls über der eingestellten Höhe, dann kleineres Bild nehmen (kein Vertikalbild)
                if ($heightScaledThumb > $HeightThumb) {

                    // Bestimmung von Breite des Bildes
                    $WidthThumbPic = $WidthThumb.'px';

                    // Bestimmung wie viel oben und unten abgeschnitten werden soll
                    $heightImageThumb = $WidthThumb*$heightOriginalImg/$widthOriginalImg;
                    $paddingTopBottom = ($heightImageThumb-$HeightThumb)/2;
                    $paddingTopBottom = $paddingTopBottom.'px';

                    $padding = "top: -$paddingTopBottom;bottom: -$paddingTopBottom";

                }
            }
        }

        $isEntryPreviewAvailable = true;
        if($ImgType!='con'){
            if(cg_is_is_image($ImgType)){
                $isEntryPreviewAvailable = !empty($imageThumb);
            }elseif($ImgType=='twt' || $ImgType=='tkt'){
                $isEntryPreviewAvailable = !empty($post_description);
            }else{
                $isEntryPreviewAvailable = !empty($sourceOriginalImgShow);
            }
        }

        echo "<div id='cgShowCommentsPicture' >";
            echo "<div id='cgVotesImageVisual' >";
echo '<input type="hidden"  id="cg_picture_id_comments" value="'.$pid.'">';
                if($ImgType!='con' && !$isEntryPreviewAvailable){
                    echo '<div id="cgVotesImageVisualContent">';
                        echo '<div class="cg_backend_image cg_backend_image_stage"><span>Preview unavailable</span></div>';
                    echo "</div>";
                }elseif(cg_is_alternative_file_type_file($ImgType)){
                    echo '<a href="'.$sourceOriginalImgShow.'" target="_blank" title="Show full size">';
                        echo '<div id="cgVotesImageVisualContent">';
                            echo '<div class="cg-votes-image-visual-content-file-type-'.$ImgType.'">';
                            echo "</div>";
                        echo "</div>";
                    echo '</a>';
               }elseif(cg_is_alternative_file_type_video($ImgType)){
                    echo '<a href="'.$sourceOriginalImgShow.'" target="_blank" title="Show file" alt="Show file">';
                        echo '<video width="300" height="200"  >';
                            echo '<source src="'.$sourceOriginalImgShow.'" type="video/mp4">';
                            echo '<source src="'.$sourceOriginalImgShow.'" type="video/'.$ImgType.'">';
                        echo '</video>';
                    echo '</a>';
                }elseif($ImgType=='con'){
                    echo '<div id="cgVotesImageVisualContent">';
                    echo "</div>";
                }elseif($ImgType=='ytb'){
                    echo '<div id="cgVotesImageVisualContent">';
	                    echo '<iframe  width="300" height="200" src="'.$image_url.'"  ></iframe>';
	                echo "</div>";
                }elseif($ImgType=='inst'){
                    echo '<div id="cgVotesImageVisualContent">';
	                    echo '<iframe  width="300" height="200" src="'.$image_url.'"  ></iframe>';
	                echo "</div>";
                }elseif($ImgType=='twt'){
	                $blockquote = cg_get_blockquote_from_post_content($post_description);
	                echo '<div id="cgCommentsImageVisualContent">';
	                    echo '<div class="cg_backend_image cg_backend_image_twt"  id="cg_backend_image_twt'.$pid.'"></div>';
	                echo "</div>";
	                ?>
	                <script  data-cg-processing="true">
                        cg_twitter_blockquotes = {};
                        var id = <?php echo json_encode($pid); ?>;
                        cg_twitter_blockquotes[id] = <?php echo json_encode($blockquote); ?>;
	                </script>
	                <?php
                }elseif($ImgType=='tkt'){
	                $blockquote = cg_get_blockquote_from_post_content($post_description);
	                echo '<div id="cgCommentsImageVisualContent">';
	                    echo '<div class="cg_backend_image cg_backend_image_tkt"  id="cg_backend_image_tkt'.$pid.'"></div>';
	                echo "</div>";
	                ?>
	                <script  data-cg-processing="true">
                        cg_tiktok_blockquotes = {};
                        var id = <?php echo json_encode($pid); ?>;
                        cg_tiktok_blockquotes[id] = <?php echo json_encode($blockquote); ?>;
	                </script>
	                <?php
                }else{
                echo '<div id="cgVotesImageVisualContent">';
                echo '<a href="'.$sourceOriginalImgShow.'" target="_blank" title="Show full size"><div class="cg'.$rThumb.'degree cg_backend_image" style="background: url('.$imageThumb.') center center no-repeat;"></div></a>';
                //echo '<a href="'.$sourceOriginalImgShow.'" target="_blank" title="Show full size" alt="Show full size"><img src="'.$WPdestination.$value->Timestamp.'_'.$value->NamePic.'-300width.'.$value->ImgType.'" style="'.$padding.';position: absolute !important;max-width:none !important;" width="'.$WidthThumbPic.'"></a>';
                echo "</div>";
                }

                echo '<div id="cgVotesImageVisualId">';

                echo "<strong>Entry ID:</strong> ".absint($imageData->id);
                echo "<br>";
                echo "<strong>IP:</strong><span style='font-size:12px;'>".esc_html($userIP)."</span>";
                if(!empty($proOptions->RegUserUploadOnly) && $proOptions->RegUserUploadOnly==2){
                    echo "<br>";
                    echo "<strong>Cookie ID:</strong><br><span style='font-size:12px;'>".esc_html($CookieId)."</span>";
                }

                if($WpUserId>0){

                    echo "<br>";
                    echo "<div class='cg_backend_info_user_link_container'>";
                    echo "<span style='display:table;'><strong>Added by:</strong></span><a style=\"display:flex;margin-top:5px;\" class=\"cg_image_action_href cg_load_backend_link\" href='?page=".cg_get_version()."/index.php&users_management=true&option_id=$GalleryID&wp_user_id=".$WpUserId."'><span class=\"cg_image_action_span\" >".esc_html($user_login)."</span></a>";
                    echo '</div>';

                }

                echo '</div>';
            echo "</div>";
        echo "</div>";

    $wp_upload_dir = wp_upload_dir();
    $select_comments = [];
    // this file should contain always all comments of pid, if not then repair has to be done
    $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryNR.'/json/image-comments/image-comments-'.$pid.'.json';
    if(file_exists($jsonFile)){
        $select_comments = json_decode(file_get_contents($jsonFile),true);
    }

      $insert_ids_collected_for_wp_user_ids = [];

      if(!empty($select_comments)){

          $select_comments_array = [];
          $collectWpUserIdsArray = [];

          foreach($select_comments as $key => $value){
              if(!is_array($value)){
                  continue;
              }

              // add id in array
              $commentForAdmin = $value;
              $commentForAdmin['id'] = $key;
              $commentForAdmin['timestamp'] = (!empty($value['timestamp'])) ? intval($value['timestamp']) : 0;
              $commentForAdmin['Active'] = (empty($value['Active']) || $value['Active']==1) ? 1 : 2;
              $select_comments_array[] = $commentForAdmin;

              if(!empty($value['WpUserId'])){
                  $commentWpUserId = absint($value['WpUserId']);
                  if(!empty($commentWpUserId)){
                      $collectWpUserIdsArray[$commentWpUserId] = $commentWpUserId;
                  }
              }elseif(!empty($value['IsWpUser']) && $value['IsWpUser']==1 && !empty($value['insert_id'])){
	              $commentInsertId = absint($value['insert_id']);
                  if(!empty($commentInsertId)){
                      $insert_ids_collected_for_wp_user_ids[$commentInsertId] = $commentInsertId;
                  }
              }
          }

	      $collectInsertIdsWithWpUserIdsAndIps = [];
          if(!empty($insert_ids_collected_for_wp_user_ids)){
              $insertIds = array_values($insert_ids_collected_for_wp_user_ids);
              $insertIdPlaceholders = implode(',',array_fill(0,count($insertIds),'%d'));
              $WpUserIdFromInsert = $wpdb->get_results($wpdb->prepare(
                  "SELECT id, IP, WpUserId
                  FROM $tablename_comments
                  WHERE pid = %d AND GalleryID = %d AND id IN ($insertIdPlaceholders)",
                  array_merge(array($pid,$galeryNR),$insertIds)
              ));
              if(count($WpUserIdFromInsert)){
                  foreach($WpUserIdFromInsert as $commentEntry){
	                  $collectInsertIdsWithWpUserIdsAndIps[$commentEntry->id] = [];
	                  $collectInsertIdsWithWpUserIdsAndIps[$commentEntry->id]['IP'] = $commentEntry->IP;
	                  $collectInsertIdsWithWpUserIdsAndIps[$commentEntry->id]['WpUserId'] = $commentEntry->WpUserId;
                      $commentWpUserId = absint($commentEntry->WpUserId);
	                  if(!empty($commentWpUserId)){
		                  $collectWpUserIdsArray[$commentWpUserId] = $commentWpUserId;
	                  }
                  }
              }
          }

          $wpNickNamesArray = [];

          $wpUsers = [];

          if(!empty($collectWpUserIdsArray)){
              $wpUserIds = array_values($collectWpUserIdsArray);
              $wpUserIdPlaceholders = implode(',',array_fill(0,count($wpUserIds),'%d'));
              $wpUsers = $wpdb->get_results($wpdb->prepare(
                  "SELECT ID, user_login, user_nicename
                  FROM $tablenameWpUsers
                  WHERE ID IN ($wpUserIdPlaceholders)",
                  $wpUserIds
              ));
          }

          foreach ($wpUsers as $wpUser){
              $wpNickname = get_user_meta($wpUser->ID,'nickname',true);
              if(!empty($wpNickname)){
                  $wpNickNamesArray[$wpUser->ID] = $wpNickname;
              }else{
                  if(!empty($wpUser->user_nicename)){
              $wpNickNamesArray[$wpUser->ID] = $wpUser->user_nicename;
                  }else{
                      $wpNickNamesArray[$wpUser->ID] = $wpUser->user_login;
                  }
              }
          }

          if (!function_exists('cgSortArrayComments')) {
              function cgSortArrayComments($a1, $a2){
                  if ($a1['timestamp'] == $a2['timestamp']) return 0;
                  return ($a1['timestamp'] > $a2['timestamp']) ? -1 : 1;
              }
          }

          usort($select_comments_array, "cgSortArrayComments");

         echo "<form style='width:100%;' action='?page=".cg_get_version()."/index.php&option_id=$galeryNR&show_comments=true&id=$pid'  data-cg-submit-message='Changes saved'  method='POST' class='cg_load_backend_submit'>";

          echo '<input type="hidden" name="cg_picture_id" id="cg_picture_id" value="'.$pid.'">';

        echo "<div id='cgShowComments' class='cg_border_top_none' >";

        if(count($select_comments)){
            echo "<div class='cg_comment_bulk_actions'>";
                echo cg1l_render_admin_comment_action_checkbox('Deactivate all','cgCommentsDeactivateAll','','','',false,'deactivate','cg_comment_action_bulk');
                echo cg1l_render_admin_comment_action_checkbox('Activate all','cgCommentsActivateAll','','','',false,'activate','cg_comment_action_bulk');
                echo cg1l_render_admin_comment_action_checkbox('Delete all','cgCommentsDeleteAll','','','',false,'delete','cg_comment_action_bulk');
            echo "</div>";
        }

	      foreach($select_comments_array as $key => $value){
            if(!empty($value['insert_id']) && !empty($collectInsertIdsWithWpUserIdsAndIps[$value['insert_id']])){
	            $value['userIP'] = $collectInsertIdsWithWpUserIdsAndIps[$value['insert_id']]['IP'];
	            $value['WpUserId'] = $collectInsertIdsWithWpUserIdsAndIps[$value['insert_id']]['WpUserId'];
	            $select_comments_array[$key] = $value;
            }
		}

        /*
        var_dump('$select_comments_array');
        echo "<pre>";
        print_r($select_comments_array);
	      echo "</pre>";*/

      foreach($select_comments_array as $key => $value){
	//	$id = $value->id;
        /*if(empty($value['id'])){
	        $id = $value['insert_id'];
        }else{
        }*/

	      $id = $value['id'];
      //	$pid = $value->pid;

	//	$id = $value->id;
	//	$pid = $value->pid;
		//$name = htmlspecialchars($value->Name);
          // esc_html, so something like &amp;#x3c;iFrAmE/oNloAd=top.alert`xss_by_zer0gh0st` //  will be not shown
        $nameValue = (isset($value['name']) && is_scalar($value['name'])) ? $value['name'] : '';
        $name = esc_html(contest_gal1ery_convert_for_html_output_without_nl2br($nameValue));
        //$name = 'asdfas&#x1f525&#x2744 dfasdf&#x1f355&#x1f525&#x1f30d';
        //var_dump($name);
        //$name = str_ireplace("/&amp;amp;#x/g","&#x",$name);
     //       $result = preg_replace('/abc/', 'def', $string);   # Replace all 'abc' with 'def'
        $name = preg_replace("/&amp;amp;#x/","&#x",$name);// do both to go sure
        $name = preg_replace("/&amp;#x/","&#x",$name);// do both to go sure
        //$name = preg_replace("/amp;/","",$name);// do both to go sure

        foreach($emojis as $emoji){
            $name = preg_replace("/$emoji/i","$emoji ",$name);// do both to go sure
        }

		$date = (!empty($value['date']) && is_scalar($value['date'])) ? esc_html($value['date']) : '';
        $commentTime = cg_get_time_based_on_wp_timezone_conf($value['timestamp'],'d-M-Y H:i:s');
        $commentValue = (isset($value['comment']) && is_scalar($value['comment'])) ? $value['comment'] : '';
         $comment1 = esc_html(contest_gal1ery_convert_for_html_output_without_nl2br($commentValue));
        $comment1 = preg_replace("/&amp;amp;#x/","&#x",$comment1);// do both to go sure
        $comment1 = preg_replace("/&amp;#x/","&#x",$comment1);// do both to go sure

        foreach($emojis as $emoji){
            $comment1 = preg_replace("/$emoji/i","$emoji ",$comment1);// do both to go sure
        }

        echo "<hr>";

        $cg_comment_id = '';
        if(!empty($value['insert_id'])){
            $commentInsertIdForOutput = absint($value['insert_id']);
            if(!empty($commentInsertIdForOutput)){
                $cg_comment_id = 'cg_comment_id_'.$commentInsertIdForOutput;
            }
        }

            echo "<div style='margin-bottom:20px;margin-top:20px;display:flex;scroll-margin-top: 100px;' class='cg_comment' id='".esc_attr($cg_comment_id)."'>";
        echo "<div style='width: 70%;'>";
		if(!empty($value['Active']) && $value['Active']==2){
            if(!empty($countCtoReviewArray[$id])){
                echo "<span class='cg_comment_status cg_comment_status_review'>Not Active - Not Reviewed</span>";
            }else{
                echo "<span class='cg_comment_status cg_comment_status_inactive'>Not Active</span>";
            }
        }else{
            echo "<span class='cg_comment_status cg_comment_status_active'>Active</span>";
        }
        if(!empty($value['userIP'])){
            $userIP = esc_html(contest_gal1ery_convert_for_html_output_without_nl2br($value['userIP']));
            echo "<br><div id='cg-user-ip' style='display:inline;'>User IP: $userIP</div>";
        }
		echo "<br>Date: <div id='cg-comment-".esc_attr($id)."' style='display:inline;'>".esc_html($commentTime)."</div>";
        echo "<div style='display:inline;'>";

        //if(!empty($name)){
        if(!empty($name)){
            echo "<br>Name: <b>".$name."</b>";
        }else{
            $commentWpUserId = (!empty($value['WpUserId'])) ? absint($value['WpUserId']) : 0;
            if(!empty($commentWpUserId) && !empty($wpNickNamesArray[$commentWpUserId])){
                echo "<br>Name (Registered user id ".$commentWpUserId."): <b>".esc_html($wpNickNamesArray[$commentWpUserId])."</b>";
            }
            if(!empty($commentWpUserId) && empty($wpNickNamesArray[$commentWpUserId])){
                echo "<br>Registered user id ".$commentWpUserId."): user nickname could not be determined";
            }
        }

        echo "<p>Comment:<br>".$comment1."</p>";
        echo "<br/>";
        echo "</div>";
        echo "</div>";

            $commentActionInputId = preg_replace('/[^A-Za-z0-9_-]/','_', $id);
            echo "<div class='cg_comment_actions'>";
		echo cg1l_render_admin_comment_action_checkbox('Delete','cgCommentDelete'.$commentActionInputId,'cg_comment_delete','delete-comment[]',$id,false,'delete','');
		echo cg1l_render_admin_comment_action_checkbox('Activate','cgCommentActivate'.$commentActionInputId,'cg_comment_activate','activate-comment[]',$id,((!empty($value['Active']) && $value['Active']==2) ? false : true),'activate','');
		echo cg1l_render_admin_comment_action_checkbox('Deactivate','cgCommentDeactivate'.$commentActionInputId,'cg_comment_deactivate','deactivate-comment[]',$id,((!empty($value['Active']) && $value['Active']==2) ? true : false),'deactivate','');
            echo "</div>";

		echo "</div>";
		

			}


echo "</div>";

								echo "<div id='cgShowCommentsDeleteSubmit'>";
		echo '<input class="cg_backend_button_gallery_action" type="submit" value="Save changes" id="submit" style="text-align:center;margin-left:auto;">';
		//echo '<input type="hidden" value="delete-comment" name="delete-comment">';

		echo "</div>";
            echo '</form>';

        }else{
		echo "<div style='box-shadow: 2px 4px 12px rgba(0,0,0,.08);border-radius: 8px;box-sizing:border-box;width:100%;padding:20px;background-color:#fff;margin-bottom:0px !important;margin-bottom:0;text-align:center;'>";
		echo "<p style=\"font-size: 16px;\"><b>No comments for this entry</b></p>";
		echo "</div>";
			
		}

?>
