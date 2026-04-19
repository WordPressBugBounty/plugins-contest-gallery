<?php

// show gallery frontend from main cg_galleries
add_action( 'wp_ajax_nopriv_post_cg_galleries_show_cg_gallery', 'post_cg_galleries_show_cg_gallery' );
add_action( 'wp_ajax_post_cg_galleries_show_cg_gallery', 'post_cg_galleries_show_cg_gallery' );
if(!function_exists('post_cg_galleries_show_cg_gallery')){
	function post_cg_galleries_show_cg_gallery() {

		global $wpdb;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$galleryRequestErrorMessage = 'Gallery data could not be requested. Please contact administrator.';

			// PLUGIN VERSION CHECK HERE
			contest_gal1ery_db_check();
			if(!isset($_POST['gidToShow'])){
				cg1l_ajax_frontend_response(false, ['message' => $galleryRequestErrorMessage, 'code' => 'cg_invalid_gallery_request']);
			}

			$requestedGalleryId = intval($_POST['gidToShow']);
			if($requestedGalleryId !== 9999999 && $requestedGalleryId <= 0){
				cg1l_ajax_frontend_response(false, ['message' => $galleryRequestErrorMessage, 'code' => 'cg_invalid_gallery_request']);
			}

			$galeryID = $requestedGalleryId;

			if($galeryID==9999999){
				global $wpdb;
				$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
				$galeryID = $wpdb->get_var( "SELECT id FROM $tablename_options ORDER BY id DESC LIMIT 0, 1" );
				$galeryID = absint($galeryID);
				if(empty($galeryID)){
					cg1l_ajax_frontend_response(false, ['message' => $galleryRequestErrorMessage, 'code' => 'cg_invalid_gallery_request']);
				}
				$isCGalleries = true;
				$isFromGalleriesSelect = false;
			}else{
				$isCGalleries = false;
				$isFromGalleriesSelect = true;
			}

			$galleriesIds = [];
			$hasGalleriesIds = false;

			if(!empty($_POST['cgIds'])){
				$galleriesIds = [];
				foreach ($_POST['cgIds'] as $idToSet){
					$galleriesIds[] = intval($idToSet);
				}
				$hasGalleriesIds = true;
			}

			$cgFromGalleriesUrl = '';
			if(!empty($_POST['cg_from_galleries_url'])){
				$cgFromGalleriesUrl = esc_url_raw(wp_unslash($_POST['cg_from_galleries_url']));
			}

			$cglOriginPageId = 0;
			if(!empty($_POST['cgl_origin_page_id'])){
				$cglOriginPageId = absint($_POST['cgl_origin_page_id']);
			}

			$cglCurrentPageNumber = 0;
			if(!empty($_POST['cgl_page'])){
				$cglCurrentPageNumber = max(1, absint($_POST['cgl_page']));
			}

			$shortcode_name = '';
			if(!empty($_POST['shortcode_name'])){
				$shortcode_name = cg1l_sanitize_method($_POST['shortcode_name']);
			}

			$allowedShortcodes = [
				'cg_gallery' => true,
				'cg_gallery_user' => true,
				'cg_gallery_no_voting' => true,
				'cg_gallery_winner' => true,
				'cg_gallery_ecommerce' => true
			];
			if(empty($allowedShortcodes[$shortcode_name])){
				cg1l_ajax_frontend_response(false, ['message' => $galleryRequestErrorMessage, 'code' => 'cg_invalid_gallery_request']);
			}

			$entryId = 0;

			$frontend_gallery = '';

			$isCGalleriesAjax = true;

			$wp_upload_dir = wp_upload_dir();
			$optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-options.json';
			if(!file_exists($optionsFile)){
				cg1l_ajax_frontend_response(false, ['message' => $galleryRequestErrorMessage, 'code' => 'cg_invalid_gallery_request']);
			}

			$options = json_decode(file_get_contents($optionsFile),true);
			if(empty($options) || !is_array($options)){
				cg1l_ajax_frontend_response(false, ['message' => $galleryRequestErrorMessage, 'code' => 'cg_invalid_gallery_request']);
			}

			include(__DIR__.'/../v10/include-scripts-v10.php');

			exit();
		}else {
			exit();
		}
	}

}
// show gallery frontend from main cg_galleries --- ENDE

add_action('wp_ajax_nopriv_post_cg_check_if_online', 'post_cg_check_if_online');
add_action('wp_ajax_post_cg_check_if_online', 'post_cg_check_if_online');
if (!function_exists('post_cg_check_if_online')) {

    function post_cg_check_if_online()
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return '###IS_ONLINE###';
        } else {
            exit();
        }
    }
}

add_action('wp_ajax_nopriv_post_cg_set_frontend_cookie', 'post_cg_set_frontend_cookie');
add_action('wp_ajax_post_cg_set_frontend_cookie', 'post_cg_set_frontend_cookie');
if (!function_exists('post_cg_set_frontend_cookie')) {

    function post_cg_set_frontend_cookie()
    {

        global $wpdb;

        if (defined('DOING_AJAX') && DOING_AJAX) {

			if(!empty($_REQUEST['gid'])){
				$galeryID = intval(sanitize_text_field($_REQUEST['gid']));// is gidReal
			}

	        if(!empty($_POST['cgIsUpload'])){
		        if(!isset($_COOKIE['contest-gal1ery-'.$galeryID.'-upload'])) {
			        cg_set_cookie($galeryID,'upload');
			        // thats it cookie is set... after that cookie is available in browser
		        }
	        }elseif(!empty($_POST['cgOrderIdHash'])){
		        setcookie('cg_order',  cg_hash_function('---cg_order---'.sanitize_text_field($_POST['cgOrderIdHash'])), time() + ( 7 * 24 * 60 * 60), "/");
	        }else{
	            if(!isset($_COOKIE['contest-gal1ery-'.$galeryID.'-voting'])) {
	                cg_set_cookie($galeryID,'voting');
	                // thats it cookie is set... after that cookie is available in browser
	            }
	        }
            exit();
        } else {
            exit();
        }
    }
}



add_action('wp_ajax_nopriv_post_cg_rate_v10_oneStar', 'post_cg_rate_v10_oneStar');
add_action('wp_ajax_post_cg_rate_v10_oneStar', 'post_cg_rate_v10_oneStar');
if (!function_exists('post_cg_rate_v10_oneStar')) {

    function post_cg_rate_v10_oneStar()
    {

        if (defined('DOING_AJAX') && DOING_AJAX) {

            cg_check_frontend_nonce();

            require_once(__DIR__.'/../v10/v10-frontend/data/rating/rate-picture-one-star.php');

            exit();
        } else {
            exit();
        }
    }
}

add_action('wp_ajax_nopriv_post_cg_rate_v10_fiveStar', 'post_cg_rate_v10_fiveStar');
add_action('wp_ajax_post_cg_rate_v10_fiveStar', 'post_cg_rate_v10_fiveStar');
if (!function_exists('post_cg_rate_v10_fiveStar')) {

    function post_cg_rate_v10_fiveStar()
    {

        if (defined('DOING_AJAX') && DOING_AJAX) {

            cg_check_frontend_nonce();

            require_once(__DIR__.'/../v10/v10-frontend/data/rating/rate-picture-five-star.php');

            exit();
        } else {
            exit();
        }
    }
}

add_action('wp_ajax_nopriv_post_cg1l_current_frontend_nonce', 'post_cg1l_current_frontend_nonce');
add_action('wp_ajax_post_cg1l_current_frontend_nonce', 'post_cg1l_current_frontend_nonce');// has to run also for logged in users
if (!function_exists('post_cg1l_current_frontend_nonce')) {

    function post_cg1l_current_frontend_nonce()
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $WpUserId = absint($_POST['cgJustLoggedInWpUserId']);
            $cgGetLoggedInFrontendUserKey = sanitize_text_field($_POST['cgGetLoggedInFrontendUserKey']);
            $cgGetLoggedInFrontendUserKeyToCompare = get_user_meta( $WpUserId,'cgGetLoggedInFrontendUserKey',true);
            if(!empty($cgGetLoggedInFrontendUserKeyToCompare) && hash_equals((string)$cgGetLoggedInFrontendUserKeyToCompare, (string)$cgGetLoggedInFrontendUserKey)){
                ?>
                <script data-cg-processing-current-nonce="true">
                    cgJsClass.gallery.vars.currentCgNonce = <?php echo json_encode(wp_create_nonce('cg1l_action')); ?>;
                    cgJsClass.gallery.vars.cgGetLoggedInFrontendUserKey = '';
                    cgJsClass.gallery.vars.cgJustLoggedInWpUserId = '';
                </script>
                <?php
                delete_user_meta( $WpUserId,'cgGetLoggedInFrontendUserKey');
            }
            exit();
        } else {
            exit();
        }
    }
}

add_action('wp_ajax_nopriv_post_cg1l_get_current_galleries_data', 'post_cg1l_get_current_galleries_data');
add_action('wp_ajax_post_cg1l_get_current_galleries_data', 'post_cg1l_get_current_galleries_data');
if (!function_exists('post_cg1l_get_current_galleries_data')) {
    function post_cg1l_get_current_galleries_data()
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            cg_check_frontend_nonce();
            $_POST = cg1l_sanitize_post($_POST);
            $galleriesDataAccessErrorMessage = 'A new gallery version is available. Please reload the page.';
            $allowedShortcodes = [
                'cg_gallery' => true,
                'cg_gallery_user' => true,
                'cg_gallery_no_voting' => true,
                'cg_gallery_winner' => true,
                'cg_gallery_ecommerce' => true,
            ];
            $shortcode_name = (!empty($_POST['shortcode_name'])) ? sanitize_text_field($_POST['shortcode_name']) : '';
            if(empty($allowedShortcodes[$shortcode_name])){
                cg1l_ajax_frontend_response(false, ['message' => $galleriesDataAccessErrorMessage, 'code' => 'cg_invalid_galleries_data_access']);
            }
            $loadedRealGalleriesIds = [];
            if(!empty($_POST['loadedRealGalleriesIds']) && is_array($_POST['loadedRealGalleriesIds'])){
                $loadedRealGalleriesIds = cg1l_normalize_positive_int_id_list($_POST['loadedRealGalleriesIds']);
            }
            $galleriesIds = [];
            if(!empty($_POST['galleriesIds']) && is_array($_POST['galleriesIds'])){
                $galleriesIds = cg1l_normalize_positive_int_id_list($_POST['galleriesIds']);
            }
            $hasGalleriesIds = (
                array_key_exists('hasGalleriesIds', $_POST) &&
                cg1l_parse_bool_value($_POST['hasGalleriesIds']) &&
                !empty($galleriesIds)
            );
            $isGalleriesMainPage = (
                array_key_exists('isGalleriesMainPage', $_POST) &&
                cg1l_parse_bool_value($_POST['isGalleriesMainPage'])
            );
            $galleriesDataAccessHash = (!empty($_POST['galleriesDataAccessHash'])) ? sanitize_text_field($_POST['galleriesDataAccessHash']) : '';
            $viewerUserId = (is_user_logged_in()) ? get_current_user_id() : 0;
            if(!$hasGalleriesIds){
                $galleriesIds = [];
            }
            $expectedGalleriesDataAccessHash = cg1l_get_galleries_data_access_hash($shortcode_name,$viewerUserId,$isGalleriesMainPage,$galleriesIds,$hasGalleriesIds);
            if(
                empty($galleriesDataAccessHash) ||
                empty($expectedGalleriesDataAccessHash) ||
                !hash_equals((string)$expectedGalleriesDataAccessHash, (string)$galleriesDataAccessHash)
            ){
                cg1l_ajax_frontend_response(false, ['message' => $galleriesDataAccessErrorMessage, 'code' => 'cg_invalid_galleries_data_access']);
            }
            $wp_upload_dir = wp_upload_dir();
            $is_user_logged_in = is_user_logged_in();

            include(__DIR__ ."/../check-language-general.php");

            $WpPageParentShortCodeType = 'WpPageParent';

            if($shortcode_name == 'cg_gallery_user'){
                $WpPageParentShortCodeType = 'WpPageParentUser';
            } elseif($shortcode_name == 'cg_gallery_no_voting'){
                $WpPageParentShortCodeType = 'WpPageParentNoVoting';
            } elseif($shortcode_name == 'cg_gallery_winner'){
                $WpPageParentShortCodeType = 'WpPageParentWinner';
            }elseif($shortcode_name == 'cg_gallery_ecommerce'){
                $WpPageParentShortCodeType = 'WpPageParentEcommerce';
            }

            $galleriesOptions = cg_galleries_options($shortcode_name);
            $options = cg_set_option_from_galleries_options([],$galleriesOptions);
            $isCGalleriesNoSorting = $hasGalleriesIds;
            $dataArray = cg1l_get_images_full_data_frontend_for_galleries($wp_upload_dir,$shortcode_name,$is_user_logged_in,$language_NoGalleryEntries,$WpPageParentShortCodeType,$loadedRealGalleriesIds,$isGalleriesMainPage,$galleriesIds,$hasGalleriesIds,$viewerUserId);
            $imagesFullData = $dataArray['imagesFullData'];
            $imagesFullDataArrays = cg1l_get_data_images_full_data_sorted($options,$imagesFullData,true,$isCGalleriesNoSorting);
            $imagesFullData = $imagesFullDataArrays['imagesFullDataFull'];
            cg1l_ajax_frontend_response(true, ['imagesFullData' => $imagesFullData]);
            exit();
        } else {
            exit();
        }
    }
}

add_action('wp_ajax_nopriv_post_cg1l_login_user_by_key', 'post_cg1l_login_user_by_key');
if (!function_exists('post_cg1l_login_user_by_key')) {
    function post_cg1l_login_user_by_key()
    {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            cg_check_frontend_nonce();
            global $wpdb;
            $tablename = $wpdb->prefix . "contest_gal1ery";
            $gid = absint($_POST['cgl_gid']);
            $WpUserId = absint($_POST['cgJustLoggedInWpUserId']);
            $cgGetLoggedInFrontendUserKey = sanitize_text_field($_POST['cglKey']);
            if(empty($WpUserId) || empty($cgGetLoggedInFrontendUserKey)){
                exit();
            }
            $cgGetLoggedInFrontendUserKeyToCompare = get_user_meta( $WpUserId,'cgGetLoggedInFrontendUserKey',true);
            if(!empty($cgGetLoggedInFrontendUserKeyToCompare) && hash_equals((string)$cgGetLoggedInFrontendUserKeyToCompare, (string)$cgGetLoggedInFrontendUserKey)){
                $userRow = get_userdata($WpUserId);
                if(empty($userRow)){
                    exit();
                }
                $wpNickname = $userRow->display_name;
                $WpUserEmail = $userRow->user_email;
                wp_set_current_user($WpUserId);
                wp_set_auth_cookie( $WpUserId,true );
                $profileImage = '';
                $wpAvatarImage = '';
                $wpAvatarImageLarge = '';
                $wpUploadProfileImage = $wpdb->get_var( $wpdb->prepare(
                    "SELECT WpUpload FROM $tablename WHERE IsProfileImage = %d AND WpUserId = %d",
                    1,
                    $WpUserId
                ));
                if(!empty($wpUploadProfileImage)){
                    $profileImage=wp_get_attachment_image_src($wpUploadProfileImage, 'large');
                    $profileImage=$profileImage[0];
                }
                $avatarImages = cg1l_frontend_get_real_wp_avatar_images($WpUserId, 96, 400);
                if(!empty($avatarImages['small'])){
                    $wpAvatarImage = $avatarImages['small'];
                    $wpAvatarImageLarge = !empty($avatarImages['large']) ? $avatarImages['large'] : $avatarImages['small'];
                }
                ?>
                <script data-cg-processing-current-nonce="true">
                    var gid = <?php echo json_encode($gid); ?>;
                    cgJsClass.gallery.vars.wpNickname = <?php echo json_encode($wpNickname); ?>;
                    cgJsClass.gallery.vars.WpUserEmail = <?php echo json_encode($WpUserEmail); ?>;
                    cgJsClass.gallery.vars.wpUserId = <?php echo json_encode($WpUserId); ?>;
                    cgJsClass.gallery.vars.cglCurrentUserId = <?php echo json_encode(absint($WpUserId)); ?>;
                    if(typeof cgJsData != 'undefined' && cgJsData[gid] && cgJsData[gid].vars && cgJsData[gid].vars.nicknames) {
                        cgJsData[gid].vars.nicknames[cgJsClass.gallery.vars.wpUserId] = <?php echo json_encode($wpNickname); ?>;
                    }
                    if(typeof cgJsData != 'undefined' && cgJsData[gid] && cgJsData[gid].vars && cgJsData[gid].vars.profileImages) {
                        cgJsData[gid].vars.profileImages[cgJsClass.gallery.vars.wpUserId] = <?php echo json_encode($profileImage); ?>;
                    }
                    if(typeof cgJsData != 'undefined' && cgJsData[gid] && cgJsData[gid].vars) {
                        cgJsData[gid].vars.wpAvatarImages = cgJsData[gid].vars.wpAvatarImages || {};
                        cgJsData[gid].vars.wpAvatarImagesLarge = cgJsData[gid].vars.wpAvatarImagesLarge || {};
                        if (<?php echo json_encode(!empty($wpAvatarImage)); ?>) {
                            cgJsData[gid].vars.wpAvatarImages[cgJsClass.gallery.vars.wpUserId] = <?php echo json_encode($wpAvatarImage); ?>;
                            cgJsData[gid].vars.wpAvatarImagesLarge[cgJsClass.gallery.vars.wpUserId] = <?php echo json_encode($wpAvatarImageLarge); ?>;
                        } else if (cgJsData[gid].vars.wpAvatarImages[cgJsClass.gallery.vars.wpUserId]) {
                            delete cgJsData[gid].vars.wpAvatarImages[cgJsClass.gallery.vars.wpUserId];
                            if (cgJsData[gid].vars.wpAvatarImagesLarge[cgJsClass.gallery.vars.wpUserId]) {
                                delete cgJsData[gid].vars.wpAvatarImagesLarge[cgJsClass.gallery.vars.wpUserId];
                            }
                        }
                    }
                </script>
                <?php
                exit();
            } else {
                exit();
            }
        }
    }
}

// Add image gallery form upload

add_action('wp_ajax_nopriv_post_cg_gallery_form_upload', 'post_cg_gallery_form_upload');
add_action('wp_ajax_post_cg_gallery_form_upload', 'post_cg_gallery_form_upload');

if (!function_exists('post_cg_gallery_form_upload')) {

    function post_cg_gallery_form_upload()
    {

        global $wpdb;

        if (defined('DOING_AJAX') && DOING_AJAX) {

            cg_check_frontend_nonce();

            include(__DIR__.'/../v10/v10-frontend/user_upload/users-upload-check.php');

            exit();
        } else {
            exit();
        }
    }
}

// Add image gallery form upload ---- END


// Remove image user gallery
add_action('wp_ajax_post_cg_gallery_user_delete_image', 'post_cg_gallery_user_delete_image');

if (!function_exists('post_cg_gallery_user_delete_image')) {
    function post_cg_gallery_user_delete_image()
    {
        if (!is_user_logged_in()) {
            return;
        }

        global $wpdb;

        if (defined('DOING_AJAX') && DOING_AJAX) {

            // Keep the Ajax entrypoint thin: validate the shared frontend nonce
            // and hand over the request to the dedicated delete flow file.
            cg_check_frontend_nonce();

            include(__DIR__.'/../v10/v10-frontend/gallery/gallery-user-delete-image.php');

            exit();
        } else {
            exit();
        }
    }
}

// Remove image user gallery ---- END



// Edit image user data gallery

add_action('wp_ajax_nopriv_post_cg_gallery_user_edit_image_data', 'post_cg_gallery_user_edit_image_data');
add_action('wp_ajax_post_cg_gallery_user_edit_image_data', 'post_cg_gallery_user_edit_image_data');

if (!function_exists('post_cg_gallery_user_edit_image_data')) {
    function post_cg_gallery_user_edit_image_data()
    {

        if (!is_user_logged_in()) {
            return;
        }

        global $wpdb;

        if (defined('DOING_AJAX') && DOING_AJAX) {

            cg_check_frontend_nonce();

            include(__DIR__.'/../v10/v10-frontend/gallery/gallery-user-edit-image-data.php');

            exit();
        } else {
            exit();
        }
    }
}

// Edit image user data gallery ---- END


// Remove image user gallery

add_action('wp_ajax_nopriv_post_cg_changes_recognized', 'post_cg_changes_recognized');
add_action('wp_ajax_post_cg_changes_recognized', 'post_cg_changes_recognized');
if (!function_exists('post_cg_changes_recognized')) {

    function post_cg_changes_recognized()
    {

        if (!is_user_logged_in()) {
            return;
        }

        if (defined('DOING_AJAX') && DOING_AJAX) {
            cg_check_frontend_nonce();
            include(__DIR__.'/../v10/v10-frontend/gallery/changes-recognized.php');
            exit();
        } else {
            exit();
        }
    }
}

// Remove image user gallery ---- END



// AJAX Script für set comment Slider ---- ENDE
/*
add_action( 'wp_ajax_nopriv_post_cg_set_comment_v10', 'post_cg_set_comment_v10' );
add_action( 'wp_ajax_post_cg_set_comment_v10', 'post_cg_set_comment_v10' );
function post_cg_set_comment_v10() {

	global $wpdb;

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

		require_once('v10/v10-frontend/data/comment/set-comment-v10.php');
		die();

	}
	else {

		exit();
	}
}*/


// AJAX Script für set comment Slider ---- ENDE


// AJAX Script show comment Slider or out of Gallery


add_action('wp_ajax_nopriv_cg_show_set_comments_v10', 'cg_show_set_comments_v10');
add_action('wp_ajax_cg_show_set_comments_v10', 'cg_show_set_comments_v10');
if (!function_exists('cg_show_set_comments_v10')) {
    function cg_show_set_comments_v10()
    {

        global $wpdb;

        if (defined('DOING_AJAX') && DOING_AJAX) {

            cg_check_frontend_nonce();

            require_once(__DIR__.'/../v10/v10-frontend/data/comment/show-set-comments-v10.php');
            exit();

        } else {

            exit();
        }

    }

}

// AJAX Script show comment Slider or out of Gallery ---- ENDE

add_action( 'wp_ajax_nopriv_post_cg_login', 'post_cg_login' );

if(!function_exists('post_cg_login')){

    function post_cg_login(){

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            cg_check_frontend_nonce();

            require_once(__DIR__.'/../v10/v10-admin/users/frontend/login/users-login-check-ajax.php');

            die();
        }
        else {
            exit();
        }
    }

}

add_action( 'wp_ajax_nopriv_post_cg1l_resend_unconfirmed_mail_frontend', 'post_cg1l_resend_unconfirmed_mail_frontend' );

if(!function_exists('post_cg1l_resend_unconfirmed_mail_frontend')){
    function post_cg1l_resend_unconfirmed_mail_frontend(){
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            cg_check_frontend_nonce();

            global $wpdb;
            $tablenameProOptions = $wpdb->prefix . "contest_gal1ery_pro_options";

            $proOptions = $wpdb->get_row("SELECT * FROM $tablenameProOptions WHERE GeneralID = '1'");
            $activationKey = sanitize_text_field(wp_unslash($_POST["cgl_activation_key"]));

            $gid = 0;
            $pid = 0;
            $ReceiverMail = sanitize_email($_POST['cgl_mail']);
            $page_id = intval($_POST['cgl_page_id']);
            $pageUrl = get_permalink($page_id);
            $FromName = html_entity_decode(strip_tags($proOptions->RegMailAddressor));
            $ReplyName = $FromName;
            if (is_email($proOptions->RegMailReply)) {
                $ReplyMail = $proOptions->RegMailReply;
            } else {
                $ReplyMail = get_option('admin_email');
            }
            $FromMail = $ReplyMail;
            $RegMailCC = (empty($proOptions->RegMailCC)) ? '' : $proOptions->RegMailCC;
            $RegMailBCC = (empty($proOptions->RegMailBCC)) ? '' : $proOptions->RegMailBCC;
            $body = contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->TextEmailConfirmation);
            $Subject = contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->RegMailSubject);

            $sent = cg1l_resend_unconfirmed_mail($gid,$pid,$ReceiverMail,$pageUrl,$ReplyMail,$FromName,$ReplyName,$FromMail,$RegMailCC,$RegMailBCC,$body,$Subject,$activationKey,true);

            ?>
            <script data-cg-processing="true">
                cgJsClass.gallery.vars.resendConfirmationMailSent = <?php echo json_encode($sent);?>;
            </script>
            <?php
            die();
        }else {
            exit();
        }
    }
}

add_action( 'wp_ajax_nopriv_post_cg1l_verify_pin', 'post_cg1l_verify_pin' );
if(!function_exists('post_cg1l_verify_pin')){

    function post_cg1l_verify_pin() {

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            global $wpdb;

            cg_check_frontend_nonce();

            // PLUGIN VERSION CHECK HERE
            contest_gal1ery_db_check();

            $cg_users_pin_from_email_check = 1;

            include(__DIR__.'/../v10/v10-admin/users/frontend/registry/users-registry.php');

            exit();

        }
        else {
            exit();
        }
    }

}

add_action( 'wp_ajax_nopriv_post_cg1l_resend_pin', 'post_cg1l_resend_pin' );
if(!function_exists('post_cg1l_resend_pin')){

    function post_cg1l_resend_pin() {

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            global $wpdb;

            cg_check_frontend_nonce();

            // PLUGIN VERSION CHECK HERE
            contest_gal1ery_db_check();

            include(__DIR__.'/../v10/v10-admin/users/frontend/registry/users-registry-resend-pin.php');
            exit();

        }
        else {
            exit();
        }
    }

}

add_action( 'wp_ajax_nopriv_post_cg_ecommerce_payment_processing', 'post_cg_ecommerce_payment_processing' );
add_action( 'wp_ajax_post_cg_ecommerce_payment_processing', 'post_cg_ecommerce_payment_processing' );
if(!function_exists('post_cg_ecommerce_payment_processing')){

    function post_cg_ecommerce_payment_processing() {
        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            cg_check_frontend_nonce();

            include (__DIR__.'/../v10/v10-frontend/ecommerce/ecommerce-payment-processing.php');
            exit();

        }
        else {
            exit();
        }

    }

}

add_action( 'wp_ajax_nopriv_post_cg_get_raw_data_from_galleries', 'post_cg_get_raw_data_from_galleries' );
add_action( 'wp_ajax_post_cg_get_raw_data_from_galleries', 'post_cg_get_raw_data_from_galleries' );
if(!function_exists('post_cg_get_raw_data_from_galleries')){
    function post_cg_get_raw_data_from_galleries() {
        global $wpdb;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            cg_check_frontend_nonce();
            include (__DIR__.'/../v10/v10-frontend/ecommerce/ecommerce-get-raw-data-from-galleries.php');
            exit();
        }else {
            exit();
        }

    }

}

add_action( 'wp_ajax_nopriv_post_cg_get_stripe_payment_intent', 'post_cg_get_stripe_payment_intent' );
add_action( 'wp_ajax_post_cg_get_stripe_payment_intent', 'post_cg_get_stripe_payment_intent' );
if(!function_exists('post_cg_get_stripe_payment_intent')){
    function post_cg_get_stripe_payment_intent() {
        global $wpdb;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            cg_check_frontend_nonce();
            include (__DIR__.'/../v10/v10-frontend/ecommerce/ecommerce-get-stripe-payment-intent.php');
            exit();
        }else {
            exit();
        }
    }
}

add_action( 'wp_ajax_post_cg1l_get_gallery_data', 'post_cg1l_get_gallery_data' );
add_action( 'wp_ajax_nopriv_post_cg1l_get_gallery_data', 'post_cg1l_get_gallery_data' );
if(!function_exists('post_cg1l_get_gallery_data')){
    add_action('wp_ajax_post_cg1l_get_gallery_data', 'post_cg1l_get_gallery_data');

    if(!function_exists('post_cg1l_get_gallery_data')){
        function post_cg1l_get_gallery_data() {
            if ( ! ( defined('DOING_AJAX') && DOING_AJAX ) ) { exit; }

            while (ob_get_level() > 0) { ob_end_clean(); }

            cg_check_frontend_nonce();

            $gid = absint($_POST['gid']);
            if (empty($gid)) { status_header(400); exit('Missing gid'); }

            $wp_upload_dir = wp_upload_dir();
            $headerLen = 128;
            $type = cg1l_sanitize_method($_POST['cg_type']);
            $shortcode_name = sanitize_text_field($_POST['shortcode_name']);
            $galleryDataAccessHash = sanitize_text_field($_POST['galleryDataAccessHash']);
            $galleryDataUseAllowedRealIds = absint($_POST['galleryDataUseAllowedRealIds']);

            $base_dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/segments';
            $optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/'.$gid.'-options.json';
            $categoriesFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/'.$gid.'-categories.json';

            $allowedShortcodes = [
                'cg_gallery' => true,
                'cg_gallery_user' => true,
                'cg_gallery_no_voting' => true,
                'cg_gallery_winner' => true,
                'cg_gallery_ecommerce' => true
            ];

            if(empty($allowedShortcodes[$shortcode_name])){
                cg1l_ajax_frontend_response(false, ['message' => 'cg_invalid_gallery_data_access']);
            }

            $viewerUserId = (is_user_logged_in()) ? get_current_user_id() : 0;
            $expectedGalleryDataAccessHash = cg1l_get_gallery_data_access_hash($gid, $shortcode_name, $viewerUserId, $galleryDataUseAllowedRealIds);
            if($galleryDataAccessHash !== $expectedGalleryDataAccessHash){
                cg1l_ajax_frontend_response(false, ['message' => 'cg_invalid_gallery_data_access']);
            }

            $options = [];
            if(file_exists($optionsFile)){
                $options = json_decode(file_get_contents($optionsFile), true);
                $options = (!empty($options[$gid])) ? $options[$gid] : $options;
            }

            if(
                (!empty($options['pro']['RegUserGalleryOnly']) && intval($options['pro']['RegUserGalleryOnly']) === 1 && !is_user_logged_in()) ||
                ($shortcode_name === 'cg_gallery_user' && !is_user_logged_in())
            ){
                cg1l_ajax_frontend_response(false, ['message' => 'cg_invalid_gallery_data_access']);
            }

            $categoriesFullData = [];
            if(file_exists($categoriesFile)){
                $categoriesFullData = json_decode(file_get_contents($categoriesFile), true);
                if(empty($categoriesFullData) || !is_array($categoriesFullData)){
                    $categoriesFullData = [];
                }
            }

            cg1l_migrate_image_stats_to_folder($gid, true);// correct first if needs to correct

            $imagesFullData = cg1l_build_images_main_data_gzip($gid, true);
            if(!is_array($imagesFullData)){
                $imagesFullData = [];
            }

            $allowedRealIds = [];
            if(!empty($galleryDataUseAllowedRealIds)){
                $allowedRealIds = cg1l_frontend_get_allowed_real_ids($imagesFullData, $shortcode_name, $categoriesFullData, $options, $viewerUserId);
            }

            $typeMap = [
                'images-main-data' => [
                    'final' => $base_dir . "/images-main-data.json.gz.php",
                    'builder' => 'cg1l_build_images_main_data_gzip'
                ],
                'images-query-data' => [
                    'final' => $base_dir . "/images-query-data.json.gz.php",
                    'builder' => 'cg1l_build_images_query_data_gzip'
                ],
                'images-comments-data' => [
                    'final' => $base_dir . "/images-comments-data.json.gz.php",
                    'builder' => 'cg1l_build_images_comments_data_gzip'
                ],
                'images-info-data' => [
                    'final' => $base_dir . "/images-info-data.json.gz.php",
                    'builder' => 'cg1l_build_images_info_data_gzip'
                ],
                'images-urls-data' => [
                    'final' => $base_dir . "/images-urls-data.json.gz.php",
                    'builder' => 'cg1l_build_images_urls_data_gzip'
                ],
                'images-stats-data' => [
                    'final' => $base_dir . "/images-stats-data.json.gz.php",
                    'builder' => 'cg1l_build_images_stats_data_gzip'
                ]
            ];

            if(empty($typeMap[$type])){
                status_header(404);
                exit('Type not found');
            }

            $final = $typeMap[$type]['final'];
            $builder = $typeMap[$type]['builder'];

            if($type === 'images-main-data' || $type === 'images-query-data'){
                call_user_func($builder, $gid);
            }elseif($type === 'images-urls-data'){
                call_user_func($builder, $gid, $imagesFullData, false, $shortcode_name);
            }else{
                call_user_func($builder, $gid, $imagesFullData);
            }

            clearstatcache(true, $final);

            if (!file_exists($final)) { status_header(404); exit('File not found'); }

            // Read gz payload from file
            $fh = fopen($final, 'rb');
            if (!$fh) { status_header(500); exit('Open failed'); }

            fseek($fh, $headerLen);
            $gzData = stream_get_contents($fh);
            fclose($fh);

            if (empty($gzData)) { status_header(500); exit('Empty payload'); }

            // Decompress to plain JSON
            $json = gzdecode($gzData);
            if ($json === false) { status_header(500); exit('Decode failed'); }

            if(!empty($galleryDataUseAllowedRealIds)){
                $payloadData = json_decode($json, true);
                if(!is_array($payloadData)){
                    $payloadData = [];
                }
                $payloadData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($payloadData, $allowedRealIds);
                $json = wp_json_encode($payloadData);
                if($json === false){
                    status_header(500);
                    exit('Encode failed');
                }
            }

            nocache_headers();
            header('Content-Type: application/json; charset=utf-8');
            header('Vary: Accept-Encoding');
            header('Cache-Control: no-transform');

            echo $json;
            exit;
        }
    }

}
