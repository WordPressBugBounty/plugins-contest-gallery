<?php
if(!defined('ABSPATH')){exit;}

global $wpdb;
$GLOBALS['cg1lCommentSubmitResult'] = array('status' => 'blocked');
$commentVoteValue = (isset($cg1lCommentVoteValue)) ? absint($cg1lCommentVoteValue) : 0;
$tablenameComments = $wpdb->prefix . "contest_gal1ery_comments";
$tablename = $wpdb->prefix . "contest_gal1ery";
$tablenameIP = $wpdb->prefix . "contest_gal1ery_ip";
$tablename_comments_notification_options = $wpdb->prefix . "contest_gal1ery_comments_notification_options";
$tablename_mail_user_comment = $wpdb->prefix . "contest_gal1ery_mail_user_comment";
$tablename_user_comment_mails = $wpdb->prefix . "contest_gal1ery_user_comment_mails";
$wp_users = $wpdb->prefix . "users";

if(!function_exists('cg1l_comment_submit_storage_error')){
    function cg1l_comment_submit_storage_error($galeryIDuser){
        $GLOBALS['cg1lCommentSubmitResult'] = array('status' => 'storage_error');
        ?>
        <script data-cg-processing="true">
            var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
            if(cgJsData[galeryIDuser] && cgJsData[galeryIDuser].vars){
                cgJsData[galeryIDuser].vars.commentSubmitResponse = {status: 'storage_error'};
            }
        </script>
        <?php
        echo 'Comment could not be saved. Please try again.';
    }
}

$_POST = cg1l_sanitize_post($_POST);

$galeryID = intval($_POST['gid']);
$galeryIDuser = $_POST['galeryIDuser'];
$galleryHash = $_POST['galleryHash'];
$cgPageUrl = $_POST['cgPageUrl'];
$galleryHashDecoded = wp_salt( 'auth').'---cngl1---'.$galeryIDuser;
$galleryHashToCompare = cg_hash_function('---cngl1---'.$galeryIDuser, $galleryHash);

if ($galleryHash != $galleryHashToCompare){
    return;
}

if(strpos($galeryIDuser,'-')!==false){
    $galeryIDuserArray = explode('-',$galeryIDuser);
    if($galeryIDuserArray[0]!=$galeryID){
        return;
    }
}else{
    if($galeryIDuser!=$galeryID){
        return;
    }
}

// open and write file
$wp_upload_dir = wp_upload_dir();

$options = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-options.json';
$fp = fopen($options, 'r');
$options =json_decode(fread($fp,filesize($options)),true);

$optionsSource = $options;
fclose($fp);
if(!is_array($optionsSource)){return;}
// The signed instance ID selects the same canonical variant as its renderer.
$shortcodeNameForComments = cg1l_get_shortcode_name_from_gallery_id_user($galeryIDuser,$galeryID);
if($shortcodeNameForComments===''){return;}
$options = cg_member_options_from_source($optionsSource,$galeryID,$shortcodeNameForComments);
$intervalConf = cg_shortcode_interval_check($galeryID,$optionsSource,$shortcodeNameForComments);
if(!$intervalConf['shortcodeIsActive']){
    ?>
    <script data-cg-processing="true">
        if(cgJsData[<?php echo json_encode($galeryIDuser);?>] && cgJsData[<?php echo json_encode($galeryIDuser);?>].vars){
            cgJsData[<?php echo json_encode($galeryIDuser);?>].vars.commentSubmitResponse = {status: 'blocked'};
        }
    </script>
    <?php
    cg_shortcode_interval_check_show_ajax_message($intervalConf,$galeryIDuser);
    return;
}


if($options['general']['AllowComments']!=1){
    return;
}

// set already here maybe required for blocked submit messages
$pictureID = absint($_POST['pid']);
$entryExists = $wpdb->get_var($wpdb->prepare(
    "SELECT id FROM $tablename WHERE id = %d AND GalleryID = %d LIMIT 1",
    $pictureID,
    $galeryID
));
if(empty($entryExists)){
    return;
}

$voteCommentFollowUpVoteId = 0;
$voteCommentFollowUpToken = (!empty($_POST['voteCommentFollowUpToken'])) ? (string)$_POST['voteCommentFollowUpToken'] : '';
if($voteCommentFollowUpToken !== ''){
    $voteCommentFollowUpData = cg1l_validate_vote_comment_follow_up_token($voteCommentFollowUpToken,$galeryID,$pictureID);
    if(cg1l_get_vote_comment_mode($options) !== 'optional' || empty($voteCommentFollowUpData)){
        cg1l_comment_submit_storage_error($galeryIDuser);
        return;
    }

    $voteCommentFollowUpRow = $wpdb->get_row($wpdb->prepare(
        "SELECT id, Rating, RatingS, CommentId FROM $tablenameIP WHERE id = %d AND GalleryID = %d AND pid = %d LIMIT 1",
        $voteCommentFollowUpData['vote_id'],
        $galeryID,
        $pictureID
    ));
    if(
        empty($voteCommentFollowUpRow) ||
        !empty($voteCommentFollowUpRow->CommentId) ||
        (
            absint($voteCommentFollowUpRow->Rating) !== absint($voteCommentFollowUpData['vote']) &&
            !(absint($voteCommentFollowUpRow->RatingS) === 1 && absint($voteCommentFollowUpData['vote']) === 1)
        )
    ){
        cg1l_comment_submit_storage_error($galeryIDuser);
        return;
    }

    $voteCommentFollowUpVoteId = absint($voteCommentFollowUpRow->id);
    $commentVoteValue = absint($voteCommentFollowUpData['vote']);
}

$WpUserId = 0;
$IsWpUser = 0;

if(is_user_logged_in()){
    $WpUserId = get_current_user_id();
	$IsWpUser = 1;
}

if(isset($options['pro']['CheckLoginComment']) && $options['pro']['CheckLoginComment']==1 AND !$WpUserId){
    $GLOBALS['cg1lCommentSubmitResult'] = array('status' => 'logged_out');
    ?>
    <script data-cg-processing="true">// if this exists then everything is fine. Will check if this exits or not

        var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
        if(cgJsData[galeryIDuser] && cgJsData[galeryIDuser].vars){
            cgJsData[galeryIDuser].vars.commentSubmitResponse = {status: 'blocked'};
        }
        cgJsClass.gallery.function.message.show(galeryIDuser,cgJsClass.gallery.language[galeryIDuser].YouHaveToBeLoggedInToComment);

    </script>
    <?php
    return;
}

// check latest server-side comment submit to avoid flooding
$userIP = cg1l_sanitize_method(cg_get_user_ip());
$isFlooding = false;
$rateLimitWindowInSeconds = 10;
$timestampToCompare = 0;

if($WpUserId){
    $timestampToCompare = intval($wpdb->get_var($wpdb->prepare(
        "SELECT MAX(Timestamp) FROM $tablenameComments WHERE WpUserId = %d",
        $WpUserId
    )));
}else{
	$timestampToCompare = intval($wpdb->get_var($wpdb->prepare(
        "SELECT MAX(Timestamp) FROM $tablenameComments WHERE IP = %s",
        $userIP
    )));
}

if(!empty($timestampToCompare) && (time()-$rateLimitWindowInSeconds)<$timestampToCompare){
    $isFlooding = true;
}

if($isFlooding){
	$GLOBALS['cg1lCommentSubmitResult'] = array('status' => 'rate_limited');
	?>
    <script data-cg-processing="true">// if this exists then everything is fine. Will check if this exits or not
        var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
        if(cgJsData[galeryIDuser] && cgJsData[galeryIDuser].vars){
            cgJsData[galeryIDuser].vars.commentSubmitResponse = {status: 'rate_limited'};
        }
    </script>
	<?php
	echo "code 617 - stop flooding";
    return;
}

//$explodeHash = explode('---cngl1---',$galleryHashDecoded);
//if($explodeHash[1]==$galeryID.'-u'){
    // show message will be shown in javascript when trying to comment
   // return;
//}
$Name = cg1l_sanitize_method($_POST['name']);
//$Name = trim(stripslashes($Name));
//$Name = htmlentities($Name, ENT_QUOTES);
$Name = substr($Name,0,300);// 100 is max as message in frontend but because of smylies it can be some more

$Comment = cg1l_sanitize_method($_POST['comment']);
//$Comment = trim(stripslashes($Comment));
//$Comment = nl2br(htmlspecialchars($Comment, ENT_QUOTES));
$Comment = substr($Comment,0,3000);// 1000 is max as message in frontend but because of smilies it can be some more

if(!$WpUserId && empty($options['general']['HideCommentNameField']) && strlen(trim($Name))<2){
    $GLOBALS['cg1lCommentSubmitResult'] = array('status' => 'invalid_name');
    return;
}

if(strlen(trim($Comment))<3){
    $GLOBALS['cg1lCommentSubmitResult'] = array('status' => 'invalid_comment');
    return;
}

$cgPaidActionCommentReservation = null;
$cgPaidActionCommentReservationOwnedHere = true;
if(isset($GLOBALS['cgPaidActionCommentReservation']) && is_array($GLOBALS['cgPaidActionCommentReservation'])){
    $cgPaidActionCommentReservation = $GLOBALS['cgPaidActionCommentReservation'];
    $cgPaidActionCommentReservationOwnedHere = false;
}elseif(function_exists('cg_paid_actions_maybe_reserve')){
    $cgPaidActionCommentReservation = cg_paid_actions_maybe_reserve(
        $galeryID,
        'comment',
        1,
        'comment:'.$pictureID.':'.md5($Comment)
    );
    if(is_wp_error($cgPaidActionCommentReservation)){
        $GLOBALS['cg1lCommentSubmitResult'] = array('status' => $cgPaidActionCommentReservation->get_error_code());
        cg_paid_actions_output_frontend_error($galeryIDuser,'comment',$cgPaidActionCommentReservation);
        return;
    }
}

$unix = time();
$date = date("Y-m-d H:i",$unix);

$Active = 0;
if(!empty($options['pro']['ReviewComm'])){
	$Active = 2;
}

$cg1lCommentOwnsStatsLock = empty($cg1lCommentStatsLockAlreadyAcquired);
if($cg1lCommentOwnsStatsLock){
    cg1l_migrate_image_stats_to_folder($galeryID, true);
    $lockFp = false;
    $ratingCommentsData = cg1l_get_stats_for_update($galeryID, $pictureID, $lockFp);
}
$hasRatingCommentsData = is_array($ratingCommentsData);

$commentsLockFp = false;
$commentsFile = cg1l_get_comments_lock_for_update($galeryID, $pictureID, $commentsLockFp);
if(empty($commentsFile)){
    if($cg1lCommentOwnsStatsLock){cg1l_release_stats_lock($lockFp);}
    cg1l_comment_submit_storage_error($galeryIDuser);
    return;
}

$commentsFileData = array();
if(file_exists($commentsFile)){
    $commentsFileRaw = file_get_contents($commentsFile);
    if($commentsFileRaw === false || $commentsFileRaw === ''){
        cg1l_release_comment_lock($commentsLockFp);
        if($cg1lCommentOwnsStatsLock){cg1l_release_stats_lock($lockFp);}
        cg1l_comment_submit_storage_error($galeryIDuser);
        return;
    }
    $commentsFileData = json_decode($commentsFileRaw,true);
    if(!is_array($commentsFileData)){
        cg1l_release_comment_lock($commentsLockFp);
        if($cg1lCommentOwnsStatsLock){cg1l_release_stats_lock($lockFp);}
        cg1l_comment_submit_storage_error($galeryIDuser);
        return;
    }
}

// reinserted in 23.1.3, after removed in 16 and higher
$paidCommentFlags = cg_paid_actions_reservation_flags($cgPaidActionCommentReservation);
$wpdb->query( $wpdb->prepare(
    "
				INSERT INTO $tablenameComments
				( id, pid, GalleryID, Name, Date, Comment, Timestamp,IP,WpUserId,Vote,Active,IsPaidAction,IsPaidActionPreview)
				VALUES ( %s,%d,%d,%s,%s,%s,%d,%s,%d,%d,%d,%d,%d)
			",
    '',$pictureID,$galeryID,$Name,$date,$Comment,$unix,$userIP,$WpUserId,$commentVoteValue,$Active,$paidCommentFlags['isPaidAction'],$paidCommentFlags['isPaidActionPreview']
) );

$insert_id = $wpdb->insert_id;
if(empty($insert_id)){
    cg1l_release_comment_lock($commentsLockFp);
    if($cg1lCommentOwnsStatsLock){cg1l_release_stats_lock($lockFp);}
    cg1l_comment_submit_storage_error($galeryIDuser);
    return;
}

//$lastCommentId = $wpdb->get_var("SELECT id FROM $tablenameComments WHERE pid = '$pictureID' ORDER BY id DESC LIMIT 0, 1");

$randomAdder = md5(uniqid('cg-comment'));
$lastCommentId = $unix.'-'.substr($randomAdder,0,6);

$commentsFileData[$lastCommentId] = array();
$commentsFileData[$lastCommentId]['date'] = $date;
$commentsFileData[$lastCommentId]['timestamp'] = $unix;
$commentsFileData[$lastCommentId]['name'] = $Name;
$commentsFileData[$lastCommentId]['comment'] = $Comment;
$commentsFileData[$lastCommentId]['vote'] = $commentVoteValue;
$commentsFileData[$lastCommentId]['IsWpUser'] = $IsWpUser;
$commentsFileData[$lastCommentId]['insert_id'] = $insert_id;
//$commentsFileData[$lastCommentId]['WpUserId'] = $WpUserId;
$commentsFileData[$lastCommentId]['ReviewTstamp'] = '';
$commentsFileData[$lastCommentId]['Active'] = $Active;
$commentsFileData[$lastCommentId]['IsPaidAction'] = $paidCommentFlags['isPaidAction'];
$commentsFileData[$lastCommentId]['IsPaidActionPreview'] = $paidCommentFlags['isPaidActionPreview'];
//$commentsFileData[$lastCommentId]['userIP'] = $userIP;

$commentsFileDataTheOnlyOneComment = array();;
$commentsFileDataTheOnlyOneComment[$lastCommentId] = array();
$commentsFileDataTheOnlyOneComment[$lastCommentId]['date'] = $date;
$commentsFileDataTheOnlyOneComment[$lastCommentId]['timestamp'] = $unix;
$commentsFileDataTheOnlyOneComment[$lastCommentId]['name'] = $Name;
$commentsFileDataTheOnlyOneComment[$lastCommentId]['comment'] = $Comment;
$commentsFileDataTheOnlyOneComment[$lastCommentId]['vote'] = $commentVoteValue;
$commentsFileDataTheOnlyOneComment[$lastCommentId]['IsWpUser'] = $IsWpUser;
$commentsFileDataTheOnlyOneComment[$lastCommentId]['insert_id'] = $insert_id;
//$commentsFileDataTheOnlyOneComment[$lastCommentId]['WpUserId'] = $WpUserId;
$commentsFileDataTheOnlyOneComment[$lastCommentId]['ReviewTstamp'] = '';
$commentsFileDataTheOnlyOneComment[$lastCommentId]['Active'] = $Active;
$commentsFileDataTheOnlyOneComment[$lastCommentId]['IsPaidAction'] = $paidCommentFlags['isPaidAction'];
$commentsFileDataTheOnlyOneComment[$lastCommentId]['IsPaidActionPreview'] = $paidCommentFlags['isPaidActionPreview'];
//$commentsFileDataTheOnlyOneComment[$lastCommentId]['userIP'] = $userIP;

$dirImageComments = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/image-comments/ids/'.$pictureID;
if(!is_dir($dirImageComments) && !wp_mkdir_p($dirImageComments)){
    $wpdb->delete($tablenameComments,array('id' => $insert_id,'pid' => $pictureID,'GalleryID' => $galeryID),array('%d','%d','%d'));
    cg1l_release_comment_lock($commentsLockFp);
    if($cg1lCommentOwnsStatsLock){cg1l_release_stats_lock($lockFp);}
    cg1l_comment_submit_storage_error($galeryIDuser);
    return;
}

$singleCommentFile = $dirImageComments.'/'.$lastCommentId.'.json';
$singleCommentPayload = json_encode($commentsFileDataTheOnlyOneComment);
$commentsPayload = json_encode($commentsFileData);
if(
    $singleCommentPayload === false ||
    $commentsPayload === false ||
    !cg1l_write_atomic_file_payload($singleCommentFile,$singleCommentPayload) ||
    !cg1l_write_atomic_file_payload($commentsFile,$commentsPayload)
){
    if(file_exists($singleCommentFile)){
        unlink($singleCommentFile);
    }
    $wpdb->delete($tablenameComments,array('id' => $insert_id,'pid' => $pictureID,'GalleryID' => $galeryID),array('%d','%d','%d'));
    cg1l_release_comment_lock($commentsLockFp);
    if($cg1lCommentOwnsStatsLock){cg1l_release_stats_lock($lockFp);}
    cg1l_comment_submit_storage_error($galeryIDuser);
    return;
}

if(!empty($voteCommentFollowUpVoteId)){
    $voteCommentLinkUpdated = $wpdb->query($wpdb->prepare(
        "UPDATE $tablenameIP SET CommentId = %d WHERE id = %d AND GalleryID = %d AND pid = %d AND CommentId = 0",
        $insert_id,
        $voteCommentFollowUpVoteId,
        $galeryID,
        $pictureID
    ));
    if($voteCommentLinkUpdated !== 1){
        if(file_exists($singleCommentFile)){
            unlink($singleCommentFile);
        }
        unset($commentsFileData[$lastCommentId]);
        cg1l_write_atomic_file_payload($commentsFile,json_encode($commentsFileData));
        $wpdb->delete($tablenameComments,array('id' => $insert_id,'pid' => $pictureID,'GalleryID' => $galeryID),array('%d','%d','%d'));
        cg1l_release_comment_lock($commentsLockFp);
        if($cg1lCommentOwnsStatsLock){cg1l_release_stats_lock($lockFp);}
        cg1l_comment_submit_storage_error($galeryIDuser);
        return;
    }
}

// has to be set here after saving file
$commentsFileData[$lastCommentId]['WpUserId'] = $WpUserId;

// process comments File --- ENDE

// count active comments correctly
$countActiveComments = 0;
$countCountCtoReview = 0;
$countHiddenCommentsForFrontend = 0;

// process rating comments data file --- ENDE

// check if there were some database entries of before version 16
$countCommentsSQL = 0;
if(floatval($options['general']['Version'])<16){// this condition added later in version 28.1.2.2,
    // the only way it will be repaired in database, 'CountC' => $countCommentsTotal below and since  28.1.2.2
    // comments will be inserted since 23.1.3, because of allocation correction, but also in dir, so what in dir counts in generally
    $countCommentsSQL = $wpdb->get_var( $wpdb->prepare(
        "
                SELECT COUNT(1)
                FROM $tablenameComments 
                WHERE pid = %d AND GalleryID = %d
            ",
        $pictureID,
        $galeryID
    ) );
}


$dirImageCommentsFiles = glob($dirImageComments.'/*.json');
if(!is_array($dirImageCommentsFiles)){
    $dirImageCommentsFiles = array();
}

$fileImageCommentsDirCount = count($dirImageCommentsFiles);

foreach ($dirImageCommentsFiles as $dirImageCommentsFile){
    $dirImageCommentsFileData = json_decode(file_get_contents($dirImageCommentsFile),true);
    if(!empty($dirImageCommentsFileData) && is_array($dirImageCommentsFileData)){
        $commentKey = key($dirImageCommentsFileData);
        if(isset($dirImageCommentsFileData[$commentKey]) && is_array($dirImageCommentsFileData[$commentKey])){
            if(!empty($dirImageCommentsFileData[$commentKey]['Active']) && $dirImageCommentsFileData[$commentKey]['Active']==2){
                $countHiddenCommentsForFrontend++;
                if(empty($dirImageCommentsFileData[$commentKey]['ReviewTstamp'])){
        $countCountCtoReview++;
    }
            }else{
                $countActiveComments++;
            }
        }
    }
}

// $countCommentsSQL check if there were some database entries of before version 16
$countCommentsTotal = $countCommentsSQL + $fileImageCommentsDirCount;

if($hasRatingCommentsData){
$ratingCommentsData['CountC'] = $countCommentsTotal;
$ratingCommentsData['CountCtoReview'] = $countHiddenCommentsForFrontend;
}

// the rest will be done in cg_actualize_all_images_data_sort_values_file
// $countCommentsSQL condition above if(floatval($options['general']['Version'])<16){ since  28.1.2.2
$wpdb->update(
    "$tablename",
    array('CountC' => $countCommentsTotal, 'CountCtoReview' => $countCountCtoReview),
    array('id' => $pictureID, 'GalleryID' => $galeryID),
    array('%d','%d'),
    array('%d','%d')
);

//$ratingCommentsData = cg_check_and_repair_image_file_data($galeryID,$pictureID,$ratingCommentsData,false);

/*echo "<pre>";
    print_r($ratingCommentsData);
echo "</pre>";*/

if($hasRatingCommentsData && $cg1lCommentOwnsStatsLock){
    cg1l_set_stats_with_lock($galeryID, $pictureID, $ratingCommentsData, $lockFp);
}elseif($cg1lCommentOwnsStatsLock){
    cg1l_release_stats_lock($lockFp);
}

cg1l_push_recent_id_file($galeryID,$pictureID,'image-comments-data-last-update');
cg1l_create_last_updated_time_file($galeryID,'image-comments-data-last-update');
cg1l_push_recent_id_file($galeryID,$pictureID,'image-stats-data-last-update');
cg1l_create_last_updated_time_file($galeryID,'image-stats-data-last-update');

cg1l_release_comment_lock($commentsLockFp);

$commentsDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/image-comments/*.json');
if(!is_array($commentsDataJsonFiles)){
    $commentsDataJsonFiles = array();
}
$jsonCommentsData = [];
foreach ($commentsDataJsonFiles as $jsonFile) {
    $jsonFileData = json_decode(file_get_contents($jsonFile),true);
    if(!empty($jsonFileData)){
        $stringArray= explode('/image-comments-',$jsonFile);
        $imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
     //   if(empty($jsonImagesData[$imageId])){// then must be from some old installation and uses some old json files, logic will be only used in v10-get-data.php
    //        continue;
       // }else{
            $jsonCommentsData[$imageId] = $jsonFileData;
     //   }
    }
}

$GLOBALS['cg1lCommentSubmitResult'] = array(
    'status' => 'success',
    'Active' => $Active,
    'lastCommentId' => $lastCommentId,
    'unix' => $unix,
    'vote' => $commentVoteValue,
    'insert_id' => $insert_id
);

if($cgPaidActionCommentReservationOwnedHere && is_array($cgPaidActionCommentReservation)){
    cg_paid_actions_settle_reservation($cgPaidActionCommentReservation,1,array($insert_id));
}

?>

    <script data-cg-processing="true">// if this exists then everything is fine. Will check if this exits or not

        var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
        var pictureID = <?php echo json_encode($pictureID);?>;
        var lastCommentId = <?php echo json_encode($lastCommentId);?>;
        var Active = <?php echo json_encode($Active);?>;
        var commentVoteValue = <?php echo json_encode($commentVoteValue);?>;
        var ratingCommentsDataFromJustCommented = <?php echo json_encode($ratingCommentsData);?>;

        if(cgJsData[galeryIDuser] && cgJsData[galeryIDuser].vars){
            cgJsData[galeryIDuser].vars.commentSubmitResponse = {
                status: 'success',
                ratingCommentsDataFromJustCommented: ratingCommentsDataFromJustCommented,
                Active: Active,
                lastCommentId: lastCommentId,
                insertId: <?php echo json_encode($insert_id); ?>,
                unix: <?php echo json_encode($unix); ?>,
                vote: commentVoteValue
            };
        }

        if(cgJsData[galeryIDuser].jsonCommentsData[pictureID]){
            cgJsData[galeryIDuser].jsonCommentsData[pictureID][lastCommentId] = <?php echo json_encode($commentsFileData[$lastCommentId]); ?>;
        }else{
            cgJsData[galeryIDuser].jsonCommentsData[pictureID] = <?php echo json_encode($commentsFileData); ?>;
        }

        cgJsData[galeryIDuser].vars.commentUnix = <?php echo json_encode($unix); ?>;
        cgJsClass.gallery.comment.setComments(galeryIDuser);
        if(cgJsClass.gallery.paidActions && cgJsClass.gallery.paidActions.consumeLocalBalance){
            cgJsClass.gallery.paidActions.consumeLocalBalance(galeryIDuser,'comment',1);
        }

    </script>

<?php

if(!cg_paid_actions_is_current_request_preview($galeryID) && !empty($options['pro']['CommNoteActive'])){

    include(__DIR__ ."/../../../../check-language.php");

    $checkCommentsNotificationOptions = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_comments_notification_options WHERE GalleryID = %d", $galeryID));

    global $cgMailAction;
    global $cgMailGalleryId;
    $cgMailAction = "User comment notification e-mail";
    $cgMailGalleryId = $galeryID;

    if (empty($checkCommentsNotificationOptions)) {
        if (function_exists('cg_on_wp_mail_error') && class_exists('WP_Error')) {
            cg_on_wp_mail_error(new WP_Error('wp_mail_failed', 'Missing comment notification settings row', array(
                'to' => '',
                'subject' => '',
                'headers' => array()
            )));
        }
        return;
    }

    $CommNoteAddressor = contest_gal1ery_convert_for_html_output_without_nl2br($checkCommentsNotificationOptions->CommNoteAddressor);
    $CommNoteAdminMail = contest_gal1ery_convert_for_html_output_without_nl2br($checkCommentsNotificationOptions->CommNoteAdminMail);
    $CommNoteCC = contest_gal1ery_convert_for_html_output_without_nl2br($checkCommentsNotificationOptions->CommNoteCC);
    $CommNoteBCC = contest_gal1ery_convert_for_html_output_without_nl2br($checkCommentsNotificationOptions->CommNoteBCC);
    $CommNoteFrom = contest_gal1ery_convert_for_html_output_without_nl2br($checkCommentsNotificationOptions->CommNoteFrom);
    $CommNoteReply = contest_gal1ery_convert_for_html_output_without_nl2br($checkCommentsNotificationOptions->CommNoteReply);
    $CommNoteSubject = contest_gal1ery_convert_for_html_output_without_nl2br($checkCommentsNotificationOptions->CommNoteSubject);
    $CommNoteContent = contest_gal1ery_convert_for_html_output_without_nl2br($checkCommentsNotificationOptions->CommNoteContent);

    $headers = array();
    $mailAddresses = cg_add_mail_from_reply_headers($headers,$CommNoteAddressor,$CommNoteFrom,$CommNoteReply);
    $headers = $mailAddresses['headers'];

    if(strpos($CommNoteCC,';')){
        $CommNoteCC = explode(';',$CommNoteCC);
        foreach($CommNoteCC as $CommNoteCCValue){
            $CommNoteCCValue = trim($CommNoteCCValue);
            if(!empty($CommNoteCCValue)){
                $headers[] = "CC: $CommNoteCCValue\r\n";
            }
        }
    }elseif(!empty($CommNoteCC)){
        $headers[] = "CC: $CommNoteCC\r\n";
    }

    if(strpos($CommNoteBCC,';')){
        $CommNoteBCC = explode(';',$CommNoteBCC);
        foreach($CommNoteBCC as $CommNoteBCCValue){
            $CommNoteBCCValue = trim($CommNoteBCCValue);
            if(!empty($CommNoteBCCValue)){
                $headers[] = "BCC: $CommNoteBCCValue\r\n";
            }
        }
    }elseif(!empty($CommNoteBCC)){
        $headers[] = "BCC: $CommNoteBCC\r\n";
    }

    $headers[] = "MIME-Version: 1.0\r\n";
    $headers[] = "Content-Type: text/html; charset=utf-8\r\n";

    $NameForMail = contest_gal1ery_convert_for_html_output_without_nl2br($Name);
    $NameForMail = preg_replace("/&amp;amp;#x/","&#x",$NameForMail);// do both to go sure
    $NameForMail = preg_replace("/&amp;#x/","&#x",$NameForMail);// do both to go sure

    $CommentForMail = contest_gal1ery_convert_for_html_output_without_nl2br($Comment);
    $CommentForMail = preg_replace("/&amp;amp;#x/","&#x",$CommentForMail);// do both to go sure
    $CommentForMail = preg_replace("/&amp;#x/","&#x",$CommentForMail);// do both to go sure

    if(empty($galeryIDuser)){
        $galeryIDuser = $galeryID;// because might have be send from cg_gallery_user or cg_gallery_no_voting shortcode
    }

    // open again because might be reqpired
    $dataFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/image-data/image-data-'.$pictureID.'.json';
    $fp = fopen($dataFile, 'r');
    $imageData =json_decode(fread($fp,filesize($dataFile)),true);
    fclose($fp);

    // #toDo check if multisite working
    $adminUrl = get_site_url()."/wp-admin/admin.php";
    $post_title = $imageData['post_title'];

    $WpPage = $wpdb->get_var($wpdb->prepare("SELECT WpPage FROM $tablename WHERE id = %d  ORDER BY id DESC LIMIT 1", $pictureID));

    if(!empty($WpPage)){
        $WpPagePermalink = get_permalink($WpPage);
        $urlFrontend = '<a href="'.$WpPagePermalink.'" >'.$WpPagePermalink.'</a>';
    }else{
        $urlFrontend = $cgPageUrl."#!gallery/$galeryIDuser/image/$pictureID/$post_title";
        $urlFrontend = '<a href="'.$urlFrontend.'" >'.$urlFrontend.'</a>';
    }

    $cg_comm_note_check =  cg_hash_function('---cgCommNoteActive---'.$galeryID);

    $urlBackend = $adminUrl."?page=".cg_get_version()."/index.php#option_id=$galeryID&show_comments=true&id=$pictureID&cg_comm_note_check=$cg_comm_note_check&cg_comment_id=$insert_id";
    $urlBackend = '<a href="'.$urlBackend.'" >'.$urlBackend.'</a>';

    $posComment = '$comment$';
    $commentComplete = '<br><br>'.$language_Name.':<br>'.$NameForMail.'<br>'.$language_Comment.':<br>'.$CommentForMail.'<br><br><br>URL backend: '.$urlBackend.'<br><br>URL frontend: '.$urlFrontend.' 
    <br><br><br><b>NOTE:</b> if you see question marks or cryptic code in this e-mail then this are smileys (emoticons) which can not be displayed by e-mail provider';

    if (stripos($CommNoteContent, $posComment) !== false) {
        $CommNoteContent = str_ireplace($posComment, $commentComplete, $CommNoteContent);
    }

    add_action('wp_mail_failed', 'cg_on_wp_mail_error', 10, 1);

    if (empty($CommNoteAdminMail)) {
        if (function_exists('cg_on_wp_mail_error') && class_exists('WP_Error')) {
            cg_on_wp_mail_error(new WP_Error('wp_mail_failed', 'Missing admin recipient for user comment notification e-mail', array(
                'to' => $CommNoteAdminMail,
                'subject' => $CommNoteSubject,
                'headers' => $headers
            )));
    }
        return;
    }

    wp_mail($CommNoteAdminMail, $CommNoteSubject, $CommNoteContent, $headers);


}


?>
