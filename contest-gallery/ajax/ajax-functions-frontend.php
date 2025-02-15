<?php

// show gallery frontend from main cg_galleries
add_action( 'wp_ajax_nopriv_post_cg_galleries_show_cg_gallery', 'post_cg_galleries_show_cg_gallery' );
add_action( 'wp_ajax_post_cg_galleries_show_cg_gallery', 'post_cg_galleries_show_cg_gallery' );
if(!function_exists('post_cg_galleries_show_cg_gallery')){
	function post_cg_galleries_show_cg_gallery() {

		global $wpdb;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// PLUGIN VERSION CHECK HERE
			contest_gal1ery_db_check();
			$galeryID = intval($_POST['gidToShow']);

			if($galeryID==9999999){
				global $wpdb;
				$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
				$galeryID = $wpdb->get_var( "SELECT id FROM $tablename_options ORDER BY id DESC LIMIT 0, 1" );
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

			$shortcode_name = cg1l_sanitize_method($_POST['shortcode_name']);

			$entryId = 0;

			$frontend_gallery = '';

			$isCGalleriesAjax = true;

			$wp_upload_dir = wp_upload_dir();
			$optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-options.json';

			$options = json_decode(file_get_contents($optionsFile),true);

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

add_action( 'wp_ajax_nopriv_post_cg_load_v10', 'post_cg_load_v10' );
add_action( 'wp_ajax_post_cg_load_v10', 'post_cg_load_v10' );
if(!function_exists('post_cg_load_v10')){

    function post_cg_load_v10() {

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            require_once(__DIR__.'/../v10/v10-frontend/load-data-ajax.php');

            exit();
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
	        }else if(!empty($_POST['cgOrderIdHash'])){
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

add_action( 'wp_ajax_nopriv_post_cg_rate_v10_oneStar', 'post_cg_rate_v10_oneStar' );
add_action( 'wp_ajax_post_cg_rate_v10_oneStar', 'post_cg_rate_v10_oneStar' );
if(!function_exists('post_cg_rate_v10_oneStar')){

    function post_cg_rate_v10_oneStar() {

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            require_once(__DIR__.'/../v10/v10-frontend/data/rating/rate-picture-one-star.php');

            exit();
        }
        else {
            exit();
        }
    }
}

add_action( 'wp_ajax_nopriv_post_cg_rate_v10_fiveStar', 'post_cg_rate_v10_fiveStar' );
add_action( 'wp_ajax_post_cg_rate_v10_fiveStar', 'post_cg_rate_v10_fiveStar' );
if(!function_exists('post_cg_rate_v10_fiveStar')){

    function post_cg_rate_v10_fiveStar() {

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            require_once(__DIR__.'/../v10/v10-frontend/data/rating/rate-picture-five-star.php');

            exit();
        }
        else {
            exit();
        }
    }
}

// AJAX Script für rate picture ---- ENDE

// Add image gallery form upload

add_action( 'wp_ajax_nopriv_post_cg_gallery_form_upload', 'post_cg_gallery_form_upload' );
add_action( 'wp_ajax_post_cg_gallery_form_upload', 'post_cg_gallery_form_upload' );

if(!function_exists('post_cg_gallery_form_upload')){

    function post_cg_gallery_form_upload() {

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            include(__DIR__.'/../v10/v10-frontend/user_upload/users-upload-check.php');

            exit();
        }
        else {
            exit();
        }
    }
}

// Add image gallery form upload ---- END

// Remove image user gallery

add_action( 'wp_ajax_nopriv_post_cg_gallery_user_delete_image', 'post_cg_gallery_user_delete_image' );
add_action( 'wp_ajax_post_cg_gallery_user_delete_image', 'post_cg_gallery_user_delete_image' );

if(!function_exists('post_cg_gallery_user_delete_image')){
    function post_cg_gallery_user_delete_image() {

        if(!is_user_logged_in()){
            return;
        }

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            include(__DIR__.'/../v10/v10-frontend/gallery/gallery-user-delete-image.php');

            exit();
        }
        else {
            exit();
        }
    }
}

// Remove image user gallery ---- END

// Edit image user data gallery

add_action( 'wp_ajax_nopriv_post_cg_gallery_user_edit_image_data', 'post_cg_gallery_user_edit_image_data' );
add_action( 'wp_ajax_post_cg_gallery_user_edit_image_data', 'post_cg_gallery_user_edit_image_data' );

if(!function_exists('post_cg_gallery_user_edit_image_data')){
    function post_cg_gallery_user_edit_image_data() {

        if(!is_user_logged_in()){
            return;
        }

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            include(__DIR__.'/../v10/v10-frontend/gallery/gallery-user-edit-image-data.php');

            exit();
        }
        else {
            exit();
        }
    }
}

// Edit image user data gallery ---- END

// Remove image user gallery

add_action( 'wp_ajax_nopriv_post_cg_changes_recognized', 'post_cg_changes_recognized' );
add_action( 'wp_ajax_post_cg_changes_recognized', 'post_cg_changes_recognized' );

if(!function_exists('post_cg_changes_recognized')){

    function post_cg_changes_recognized() {

        if(!is_user_logged_in()){
            return;
        }

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            include(__DIR__.'/../v10/v10-frontend/gallery/changes-recognized.php');

            exit();
        }
        else {
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





add_action( 'wp_ajax_nopriv_cg_show_set_comments_v10', 'cg_show_set_comments_v10' );
add_action( 'wp_ajax_cg_show_set_comments_v10', 'cg_show_set_comments_v10' );

if(!function_exists('cg_show_set_comments_v10')){

    function cg_show_set_comments_v10(){

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            require_once(__DIR__.'/../v10/v10-frontend/data/comment/show-set-comments-v10.php');
            exit();

        }
        else {

            exit();
        }

    }

}

// AJAX Script show comment Slider or out of Gallery ---- ENDE


add_action( 'wp_ajax_nopriv_post_cg_login', 'post_cg_login' );
add_action( 'wp_ajax_post_cg_login', 'post_cg_login' );

if(!function_exists('post_cg_login')){

    function post_cg_login(){

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            require_once(__DIR__.'/../v10/v10-admin/users/frontend/login/users-login-check-ajax.php');

            die();
        }
        else {
            exit();
        }
    }

}

// PRO version info recognized
add_action( 'wp_ajax_nopriv_post_cg_pro_version_info_recognized', 'post_cg_pro_version_info_recognized' );
add_action( 'wp_ajax_post_cg_pro_version_info_recognized', 'post_cg_pro_version_info_recognized' );

if(!function_exists('post_cg_pro_version_info_recognized')){

    function post_cg_pro_version_info_recognized() {

        if(!is_user_logged_in()){
            return;
        }

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            include('v10/v10-admin/pro/pro-version-info-recognized.php');

            exit();
        }
        else {
            exit();
        }
    }

}
// PRO version info recognized ---- END

add_action( 'wp_ajax_nopriv_post_cg_ecommerce_checkout', 'post_cg_ecommerce_checkout' );
add_action( 'wp_ajax_post_cg_ecommerce_checkout', 'post_cg_ecommerce_checkout' );
if(!function_exists('post_cg_ecommerce_checkout')){

    function post_cg_ecommerce_checkout() {

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            include(__DIR__.'/../functions/ecommerce/frontend/checkout/ecommerce-checkout.php');
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
            include (__DIR__.'/../v10/v10-frontend/ecommerce/ecommerce-get-stripe-payment-intent.php');
            exit();
        }else {
            exit();
        }

    }

}

// PRO get sale id data
add_action( 'wp_ajax_post_cg_ecommerce_download_keys_file', 'post_cg_ecommerce_download_keys_file' );
if(!function_exists('post_cg_ecommerce_download_keys_file')){

    function post_cg_ecommerce_download_keys_file() {

        global $wpdb;

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            exit();

        }
        else {
            exit();
        }

    }

}
// PRO get sale id data ---- END


