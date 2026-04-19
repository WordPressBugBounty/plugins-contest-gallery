<?php
/**
 * @ai_scan_directive
 * This file prepares and outputs the data for the frontend gallery view.
 * It initializes globals, reads shortcode parameters, pulls data from JSON and the database,
 * checks user limits/permissions, handles PRO functionality, and configures eCommerce and Rating settings.
 *
 * DATA TRANSFER ARCHITECTURE: PHP to JavaScript
 * Variables from PHP (including those in data/variables-javascript.php and data/variables-javascript-general.php)
 * are NOT output as inline JavaScript. They are encoded as JSON, then Base64, and rendered inside hidden
 * <textarea> elements (e.g., `<textarea class="cg1l-data-options">...`). The JavaScript frontend reads these,
 * decodes them, and parses the JSON. See CODE_STRUCTURE_PHP_JS_RELATION.md for details.
 */

if(!defined('ABSPATH')){exit;}

include_once(__DIR__.'/url-query-helpers.php');

global $wpdb;
global $post;
global $cgEntryId;
global $galeryIDset;
global $galeryIDuset;
global $galeryIDnvset;
global $galeryIDwset;
global $galeryIDecset;
global $galeryIDextender;
global $galeryIDuextender;
global $galeryIDnvextender;
global $galeryIDwextender;
global $galeryIDecextender;
global $isCgParentPage;
global $isGalleriesMainPage;

$CGalleriesMainPageClass = '';
if(!empty($isGalleriesMainPage)){
	$CGalleriesMainPageClass = 'cg_galleries_main_page';
}

if(!isset($isCGalleries)){
	$isCGalleries = false;
}

if(empty($isGalleriesMainPage)){
    $isGalleriesMainPage = false;// has to be set
}

if(empty($galleriesIds)){// has to be done with empty
	$galleriesIds = [];
    $isCGalleriesNoSorting = false;
}else{
    $isCGalleriesNoSorting = true;
}

if(!isset($hasGalleriesIds)){
	$hasGalleriesIds = false;
}

if(!empty($galeryIDset)){
    if(!isset($galeryIDextender)){
        $galeryIDextender = 1;
    }else{
        $galeryIDextender++;
    }
}

if(!empty($galeryIDuset)){
    if(!isset($galeryIDuextender)){
        $galeryIDuextender = 1;
    }else{
        $galeryIDuextender++;
    }
}

if(!empty($galeryIDnvset)){
    if(!isset($galeryIDnvextender)){
	    $galeryIDnvextender = 1;
    }else{
	    $galeryIDnvextender++;
    }
}

if(!empty($galeryIDwset)){
    if(!isset($galeryIDwextender)){
        $galeryIDwextender = 1;
    }else{
        $galeryIDwextender++;
    }
}

if(!empty($galeryIDecset)){
    if(!isset($galeryIDecextender)){
        $galeryIDecextender = 1;
    }else{
        $galeryIDecextender++;
    }
}

$postId = (!empty($post->ID)) ? absint($post->ID) : 0;

$countUserVotes = 0;
$votedUserPids = [];
$languageNames = [
    'gallery'   => [],
    'general'   => [],
    'ecommerce' => [],
];
$variablesGallery = [];
$variablesGeneral = [];
$allowedRealIds = [];

$tablename = $wpdb->prefix . "contest_gal1ery";
$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
$tablenameComments = $wpdb->prefix . "contest_gal1ery_comments";
$tablename_f_input = $wpdb->prefix . "contest_gal1ery_f_input";
$tablename_f_output = $wpdb->prefix . "contest_gal1ery_f_output";
$tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
$tablenameEntries = $wpdb->prefix . "contest_gal1ery_entries";
$tablenameIP = $wpdb->prefix ."contest_gal1ery_ip";
$table_posts = $wpdb->prefix ."posts";
$tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";
$contest_gal1ery_options_input = $wpdb->prefix . "contest_gal1ery_options_input";
$tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

if(!isset($entryId)){
    $entryId = 0;
}

if(empty($isFromOrderSummary)){// to go sure is initiated
	$isFromOrderSummary = false;
}

// for sure, for security
$galeryID = absint($galeryID);

$realGid = $galeryID;
$galeryIDuser = $galeryID;
$galeryIDuserForJs = $galeryIDuser;
$galeryIDshort = '';

$shortcode_name_plural = '';
if($shortcode_name == 'cg_gallery'){
	$shortcode_name_plural = 'cg_galleries';
	$galeryIDshort = '';
    if(!empty($galeryIDset)){
        $galeryIDuserForJs = $galeryIDuser.'-ext-'.$galeryIDextender;
    }
    if(!empty($isCGalleries)){
	    $galeryIDuserForJs = 'main';
    }
    $galeryIDset = true;
}
$isUserGallery = false;
$isOnlyGalleryNoVoting = false;
$isOnlyGalleryWinner = false;
$isOnlyGalleryEcommerce = false;
$isEcommerceTest = false;
$isOnlyUploadForm = false;// since 27.0.0 is always is only upload form, from 20.0.0 till 27.0.0 it was contact form
$isOnlyContactForm = false;
$WpPageShortCodeType = 'WpPage';
$WpPageParentShortCodeType = 'WpPageParent';
$cg_gallery_shortcode_type = 'cg_gallery';
$hasWpPageParent = false;
$isEcommerceTest = false;
$ecommerceFilesData = '';
if((!empty($_GET['test'])) || (!empty($atts) && !empty($atts['test']))){
	$isEcommerceTest = true;
}

if(empty($hasUploadSell)){
    $hasUploadSell = false;// so is set for sure
}

$isGalleryShortcode = false;

if($shortcode_name == 'cg_gallery'){
    $isGalleryShortcode = true;
}elseif($shortcode_name == 'cg_gallery_user'){
    $isGalleryShortcode = true;
	$shortcode_name_plural = 'cg_galleries_user';
	$isUserGallery = true; // will be used both :)
    $isOnlyGalleryUser = true;// will be used both :)
    $galeryIDshort = 'u';
    $galeryIDuser = $galeryID.'-u';
    $galeryIDuserForJs = $galeryIDuser;
    if(!empty($galeryIDuset)){
        $galeryIDuserForJs = $galeryIDuser.'-ext-'.$galeryIDuextender;
    }
	if(!empty($isCGalleries)){
		$galeryIDuserForJs = 'main';
	}
    $galeryIDuset = true;
    $WpPageShortCodeType = 'WpPageUser';
    $WpPageParentShortCodeType = 'WpPageParentUser';
    $cg_gallery_shortcode_type = 'cg_gallery_user';
} elseif($shortcode_name == 'cg_gallery_no_voting'){
    $isGalleryShortcode = true;
	$shortcode_name_plural = 'cg_galleries_no_voting';
	$isOnlyGalleryNoVoting = true;
    $galeryIDshort = 'nv';
    $galeryIDuser = $galeryID.'-nv';
    $galeryIDuserForJs = $galeryIDuser;
    if(!empty($galeryIDnvset)){
        $galeryIDuserForJs = $galeryIDuser.'-ext-'.$galeryIDnvextender;
    }
	if(!empty($isCGalleries)){
		$galeryIDuserForJs = 'main';
	}
    $galeryIDnvset = true;
    $WpPageShortCodeType = 'WpPageNoVoting';
    $WpPageParentShortCodeType = 'WpPageParentNoVoting';
    $cg_gallery_shortcode_type = 'cg_gallery_no_voting';
} elseif($shortcode_name == 'cg_gallery_winner'){
    $isGalleryShortcode = true;
	$shortcode_name_plural = 'cg_galleries_winner';
	$isOnlyGalleryWinner = true;
    $galeryIDshort = 'w';
    $galeryIDuser = $galeryID.'-w';
    $galeryIDuserForJs = $galeryIDuser;
    if(!empty($galeryIDwset)){
        $galeryIDuserForJs = $galeryIDuser.'-ext-'.$galeryIDwextender;
    }
	if(!empty($isCGalleries)){
		$galeryIDuserForJs = 'main';
	}
    $galeryIDwset = true;
    $WpPageShortCodeType = 'WpPageWinner';
    $WpPageParentShortCodeType = 'WpPageParentWinner';
    $cg_gallery_shortcode_type = 'cg_gallery_winner';
}elseif($shortcode_name == 'cg_gallery_ecommerce'){
    $isGalleryShortcode = true;
	$shortcode_name_plural = 'cg_galleries_ecommerce';
	$isOnlyGalleryEcommerce = true;
    $galeryIDshort = 'ec';
    $galeryIDuser = $galeryID.'-ec';
    $galeryIDuserForJs = $galeryIDuser;
    if(!empty($galeryIDecset)){
        $galeryIDuserForJs = $galeryIDuser.'-ext-'.$galeryIDecextender;
    }
	if(!empty($isCGalleries)){
		$galeryIDuserForJs = 'main';
	}
    $galeryIDecset = true;
    $WpPageShortCodeType = 'WpPageEcommerce';
    $WpPageParentShortCodeType = 'WpPageParentEcommerce';
    $cg_gallery_shortcode_type = 'cg_gallery_ecommerce';
    if(!empty($_GET['test']) && $_GET['test']=='true'){
        $isEcommerceTest = true;
    }
}elseif(!empty($isReallyUploadForm) || !empty($isReallyContactForm)){
    $isOnlyContactForm = true;
    $galeryIDshort = 'cf';
    $galeryIDuser = $galeryID.'-cf';
    $galeryIDuserForJs = $galeryIDuser;
    $cg_gallery_shortcode_type = 'cg_users_upload';
    if(!empty($hasUploadSell) && !empty($OrderItemID)){
        $galeryIDuserForJs = $OrderItemID.'itemId';
    }
}

$currentUrl = cgl_normalize_internal_url(get_permalink());
$cglOriginPageId = (!empty($cglOriginPageId)) ? absint($cglOriginPageId) : 0;
$cglOriginPageId = cgl_get_origin_page_id($cglOriginPageId);
$cglRequestGalleryId = cgl_get_gallery_query_gallery_id();
$cglHasExplicitFromGalleriesPage = (get_query_var('cgl_from_galleries_page') !== '');

if(
    empty($cglOriginPageId) &&
    (
        !empty($isCGalleries) ||
        !empty($isFromGalleriesSelect) ||
        !empty($is_from_single_view_for_cg_galleries) ||
        (!empty($cglHasExplicitFromGalleriesPage) && !empty($isCgParentPage))
    ) &&
    !empty($postId)
){
    $cglOriginPageId = $postId;
}

$cglOriginPageUrl = cgl_get_origin_page_url($cglOriginPageId, $currentUrl);
if(empty($cglOriginPageUrl)){
    $cglOriginPageUrl = $currentUrl;
}

$isMultiGalleryContext = false;
if(!empty($isFromGalleriesSelect) || !empty($is_from_single_view_for_cg_galleries)){
    $isMultiGalleryContext = true;
} elseif(!empty($entryId) && !empty($cglRequestGalleryId) && ($cglHasExplicitFromGalleriesPage || !empty($cglOriginPageId))){
    $isMultiGalleryContext = true;
}

$cglMultiContextGalleryId = (!empty($cglRequestGalleryId)) ? absint($cglRequestGalleryId) : 0;
if(empty($cglMultiContextGalleryId) && !empty($isMultiGalleryContext)){
    $cglMultiContextGalleryId = absint($galeryID);
}

if(
    (
        !empty($isCGalleries) ||
        !empty($isFromGalleriesSelect) ||
        !empty($is_from_single_view_for_cg_galleries)
    ) &&
    !empty($cglOriginPageUrl)
){
    $currentUrl = $cglOriginPageUrl;
}

$cgFrontendPassthroughQueryArgs = cg1l_get_frontend_passthrough_query_args([
    'is_ecommerce_test' => !empty($isEcommerceTest)
]);
$is_user_logged_in = is_user_logged_in();
$logged_in_user = null;
$wpUserId = get_current_user_id();
if($is_user_logged_in){
	$logged_in_user = get_userdata($wpUserId);
}

$wp_upload_dir = wp_upload_dir();

// if users were deleted
if(file_exists($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$galeryID."/json/".$galeryID."-deleted-image-ids.json")){
	cg_actualize_all_images_data_deleted_images($galeryID);
}

$isProVersion = false;
$plugin_dir_path = plugin_dir_path(__FILE__);
if(is_dir ($plugin_dir_path.'/../../../contest-gallery-pro') && strpos(cg_get_version_for_scripts(),'-PRO')!==false){
    $isProVersion = true;
}

if(isset($options['icons'])){
    unset($options['icons']);
}

if(!$isProVersion && isset($options['interval'])){
    unset($options['interval']);
}
$optionsSource = $options;

if(!empty($isOnlyContactForm)){
    // after options were saved, options array will be extended for other gallery ids
    $options =  (!empty($options[$galeryID])) ? $options[$galeryID] : $options;
}else{
    // after options were saved, options array will be extended for other gallery ids
    $options = (!empty($options[$galeryIDuser])) ? $options[$galeryIDuser] : $options;
}

if(
    (
        !empty($isOnlyGalleryUser) ||
        !empty($isOnlyGalleryNoVoting) ||
        !empty($isOnlyGalleryEcommerce) ||
        !empty($isOnlyUploadFormEcommerce)
    ) &&
    !empty($optionsSource['pro']) &&
    is_array($optionsSource['pro'])
){
    if(!isset($options['pro']) || !is_array($options['pro'])){
        $options['pro'] = [];
    }
    if(array_key_exists('PdfPreviewFrontend',$optionsSource['pro'])){
        $options['pro']['PdfPreviewFrontend'] = $optionsSource['pro']['PdfPreviewFrontend'];
    }
    if(array_key_exists('PdfPreviewBackend',$optionsSource['pro'])){
        $options['pro']['PdfPreviewBackend'] = $optionsSource['pro']['PdfPreviewBackend'];
    }
}

$parentPermalink = '';
if(intval($options['general']['Version'])>=21){
    $parentPermalink = get_permalink($options['general'][$WpPageParentShortCodeType]);
    if(empty($parentPermalink)){
        echo "<p style='margin: 40px auto;text-align: center;font-weight: bold;'>The custom post type page of your Gallery (ID = $realGid) was deleted. <br>Please check backend and execute<br>
                    \"Edit options\" >>> \"Status, repair....\" >>>> \"Repair frontend\"</p>";
        return;
    }
}

if(intval($options['general']['Version'])<22 && (!empty($isOnlyGalleryEcommerce) || !empty($isOnlyUploadFormEcommerce))){
        echo "<p style='margin: 40px auto;text-align: center;font-weight: bold;'>Ecommerce shortcodes are only allowed to be used for galleries created or copied in plugin version 22 and later</p>";
        return;
}

if(!empty($isOnlyGalleryNoVoting) || !empty($isOnlyGalleryWinner)){
	$options['general']['HideUntilVote'] = 0;
	$options['general']['ShowOnlyUsersVotes'] = 0;
}

$isModernOptions = (!empty($options[$galeryIDuser])) ? true : false;

$RatingVisibleForGalleryNoVoting = (!empty($options['general']['RatingVisibleForGalleryNoVoting'])) ? true : false;
$hasWpPageParent = (!empty($options['general']['WpPageParent'])) ? true : false;

$IsModernFiveStar = (!empty($options['pro']['IsModernFiveStar'])) ? true : false;

$is_frontend = true;// required for check-language, this file will be loaded in frontend only!

$WpUserId = '';

if(is_user_logged_in()){
    $WpUserId = get_current_user_id();
}

###NORMAL###
cg_reset_to_normal_version_options_if_required($galeryID,$wp_upload_dir);
###NORMAL-END###
$WpUserIdsData = cg1l_frontend_get_wp_users_data($galeryID,$options);
$wpUserImageIdsArray = $WpUserIdsData['wpUserImageIdsArray'];

if(!empty($isOnlyGalleryUser)){
    $wpUserImageIdsArray = [];

    if($is_user_logged_in){
        if(!empty($isCGalleries)){
            $wpUserImageIds = $wpdb->get_results($wpdb->prepare(
                "
                    SELECT id
                    FROM $tablename
                    WHERE WpUserId = %d ORDER BY id DESC
                ",
                $WpUserId
            ));
        }else{
            $wpUserImageIds = $wpdb->get_results($wpdb->prepare(
                "
                    SELECT id
                    FROM $tablename
                    WHERE GalleryID = %d and WpUserId = %d ORDER BY id DESC
                ",
                $galeryID,
                $WpUserId
            ));
        }

        if(!empty($wpUserImageIds)){
            foreach($wpUserImageIds as $row){
                $wpUserImageIdsArray[] = intval($row->id);
            }
        }
    }

    $WpUserIdsData['wpUserImageIdsArray'] = $wpUserImageIdsArray;
}

$galleriesOptions = [];
$recentMainData = [];
$recentQueryData = [];
$recentUrlsData = [];
$recentStatsData = [];
$recentCommentsData = [];
$recentInfoData = [];
$queryDataArray = [];
$dataSlider = [];
$dataSliderSortedPids = [];
$jsonCommentsData = [];
$jsonInfoData = [];
$categoriesFullData = [];
$imagesFullData = [];
$imagesFullDataCurrentPage = [];
$imagesFullDataOriginalLength = 0;
$entryFullDataBeforeAllowedFilter = [];
$categoriesFullDataFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$realGid.'/json/'.$realGid.'-categories.json';
$currentPageNumber = (!empty($cglCurrentPageNumber)) ? max(1, absint($cglCurrentPageNumber)) : cgl_get_current_page_number();
$backToGalleriesFromPage = cgl_get_from_galleries_page();
$backToGalleriesFromPageNumber = cgl_get_from_galleries_page(true);
$backToGalleryFromPageNumber = $currentPageNumber;
$legacyBackToGalleryFromPageNumber = cgl_from_gallery_page();
if($backToGalleryFromPageNumber <= 1 && $legacyBackToGalleryFromPageNumber > 1){
    $backToGalleryFromPageNumber = $legacyBackToGalleryFromPageNumber;
}
$cgFromGalleriesUrl = (!empty($cgFromGalleriesUrl)) ? cgl_normalize_internal_url($cgFromGalleriesUrl) : '';
if(!empty($cgFromGalleriesUrl) && (!empty($isFromGalleriesSelect) || !empty($is_from_single_view_for_cg_galleries) || !empty($isMultiGalleryContext))){
    $backToGalleriesFromPageNumberFromUrl = cgl_get_query_var_from_url($cgFromGalleriesUrl,'cgl_page');
    if(empty($backToGalleriesFromPageNumberFromUrl)){
        $backToGalleriesFromPageNumberFromUrl = cgl_get_query_var_from_url($cgFromGalleriesUrl,'cgl_from_galleries_page');
    }
    if(!empty($backToGalleriesFromPageNumberFromUrl)){
        $backToGalleriesFromPageNumber = $backToGalleriesFromPageNumberFromUrl;
    }
}
if((!empty($isFromGalleriesSelect) || !empty($is_from_single_view_for_cg_galleries)) && $backToGalleriesFromPageNumber <= 1){
    $backToGalleriesFromPageNumber = max(1, $currentPageNumber);
}
if(!empty($isMultiGalleryContext) && $backToGalleriesFromPageNumber <= 0){
    $backToGalleriesFromPageNumber = 1;
}
if($backToGalleriesFromPageNumber > 1){
    $backToGalleriesFromPage = '?cgl_page='.$backToGalleriesFromPageNumber;
}else{
    $backToGalleriesFromPage = '';
}

include(__DIR__ ."/../../check-language.php");
include(__DIR__ ."/../../check-language-general.php");
include(__DIR__ ."/../../check-language-ecommerce.php");

if(!$isCGalleries && !$isOnlyUploadForm && !$isOnlyContactForm && file_exists($categoriesFullDataFile)){
    $categoriesFullData = json_decode(file_get_contents($categoriesFullDataFile),true);
    if(!is_array($categoriesFullData)){
        $categoriesFullData = [];
    }
}

if($isCGalleries){
    $galleriesOptions = cg_galleries_options($shortcode_name);
    $options = cg_set_option_from_galleries_options($options,$galleriesOptions);
    $dataArray = cg1l_get_images_full_data_frontend_for_galleries($wp_upload_dir,$shortcode_name,$is_user_logged_in,$language_NoGalleryEntries,$WpPageParentShortCodeType,[],$isGalleriesMainPage,$galleriesIds,$hasGalleriesIds,$WpUserId);
    $imagesFullData = $dataArray['imagesFullData'];

    $hasOlderVersionsOnMainCGalleriesPage = $dataArray['hasOlderVersionsOnMainCGalleriesPage'];

    $imagesFullDataOriginalLength = count($imagesFullData);
    $imagesFullDataArrays = cg1l_get_data_images_full_data_sorted($options,$imagesFullData,$isCGalleries,$isCGalleriesNoSorting);
    $imagesFullData = $imagesFullDataArrays['imagesFullDataFull'];
    $imagesFullDataSliced = $imagesFullDataArrays['imagesFullDataSliced'];
    $recentMainData = $imagesFullDataSliced;

// Upload/contact forms have their own frontend init path and do not consume gallery entry data.
}elseif(empty($isOnlyUploadForm) && empty($isOnlyContactForm)){

    cg1l_migrate_image_stats_to_folder($galeryID, true);// correct first if needs to correct
    $imagesFullData = cg1l_build_images_main_data_gzip($galeryID,true);
    $imagesStatsData = cg1l_build_images_stats_data_gzip($galeryID,$imagesFullData,true);
// merge stats in full data
    foreach ($imagesFullData as $id => $imageData) {
        if(!empty($imagesStatsData[$id])){
            $imagesFullData[$id] = array_merge($imageData, $imagesStatsData[$id]);
        }
    }
    $queryDataArray = cg1l_build_images_query_data_gzip($galeryID,true);
    $jsonCommentsData = cg1l_build_images_comments_data_gzip($galeryID,$imagesFullData,true);


    $jsonInfoData = cg1l_build_images_info_data_gzip($galeryID,$imagesFullData,true);

    $imagesUrlsData = cg1l_build_images_urls_data_gzip($galeryID, $imagesFullData, true, $shortcode_name);
// merge urls in full data
    foreach ($imagesFullData as $id => $imageData) {
        if(!empty($imagesUrlsData[$id])){
            $imagesFullData[$id] = array_merge($imageData, $imagesUrlsData[$id]);
        }
    }

    if(!empty($entryId)){
        $recentMainData = cg1l_get_entry_main_data($galeryID,$entryId);
    }else{
        $recentMainData = cg1l_get_recent_json_by_ids(
            $galeryID,
            'image-main-data-last-update',
            'image-data',
            'image-data-'
        );
    }

    if(!empty($entryId)){
        $recentQueryData = cg1l_get_entry_query_data($entryId);
    }else{
        $recentQueryData = cg1l_build_images_query_data_gzip($galeryID,true, array_keys($recentMainData));
    }

// put in recent query data for sure
    foreach ($recentMainData as $id => $recentData) {
        if(!empty($recentQueryData[$id])){
            $recentMainData[$id] = array_merge($recentData, $recentQueryData[$id]);
        }
    }

    if(!empty($entryId)){
        $recentUrlsData = cg1l_get_entry_urls_data($entryId,$recentMainData,$shortcode_name);
    }else{
        $recentUrlsData = cg1l_build_images_urls_data_gzip($galeryID, $recentMainData, true, $shortcode_name, array_keys($recentMainData));
    }

// put in recent urls data for sure
    foreach ($recentMainData as $id => $recentData) {
        if(!empty($recentUrlsData[$id])){
            $recentMainData[$id] = array_merge($recentData, $recentUrlsData[$id]);
        }
    }

    if(!empty($entryId)){
        $recentStatsData = cg1l_get_entry_stats_data($galeryID,$entryId);
    }else{
        $recentStatsData = cg1l_get_recent_json_by_ids(
            $galeryID,
            'image-stats-data-last-update',
            'image-stats',
            'image-stats-'
        );
    }

// put in recent stats data for sure
    foreach ($recentMainData as $id => $recentData) {
        if(!empty($recentStatsData[$id])){
            $recentMainData[$id] = array_merge($recentData, $recentStatsData[$id]);
        }
    }

// put in recent data for sure
    foreach ($imagesFullData as $id => $fullData) {
        if(!empty($recentMainData[$id])){
            $imagesFullData[$id] = $recentMainData[$id];
        }
    }

    if(!empty($entryId)){
        $recentCommentsData = cg1l_get_entry_comments_data($galeryID,$entryId);
    }else{
        $recentCommentsData = cg1l_get_recent_json_by_ids(
            $galeryID,
            'image-comments-data-last-update',
            'image-comments',
            'image-comments-'
        );
    }

    $recentCommentsData = cg1l_get_comments_user_ids($galeryID, $recentCommentsData);

// put in recent comments data for sure
    foreach ($recentCommentsData as $id => $recentData) {
        if(empty($jsonCommentsData[$id])){// recent data hast to be added to comments data
            $jsonCommentsData[$id] = $recentData;
        }
    }



    if(!empty($entryId)){
        $recentInfoData = cg1l_get_entry_info_data($galeryID,$entryId);
    }else{
        $recentInfoData = cg1l_get_recent_json_by_ids(
            $galeryID,
            'image-info-data-last-update',
            'image-info',
            'image-info-'
        );
    }

// put in recent info data for sure
    foreach ($recentInfoData as $id => $recentData) {
        if(empty($recentData) || !is_array($recentData)){
            continue;
        }
        if(empty($jsonInfoData[$id]) || !is_array($jsonInfoData[$id])){
            $jsonInfoData[$id] = $recentData;
        }else{
            $jsonInfoData[$id] = array_replace($jsonInfoData[$id], $recentData);
        }
    }

// get stats if not available, because recent might still not exists
    foreach($imagesFullData as $id => $fullData){
        if(!isset($fullData['CountS'])){
            $entryStatsData = cg1l_get_entry_stats_data($galeryID, $id);
            if(!empty($entryStatsData[$id]) && is_array($entryStatsData[$id])){
                $statsFileArray = $entryStatsData[$id];
            }else{
                $statsFileArray = cg1l_return_stats_placeholder_values(true);// for sure, that no error appears
            }
            $imagesFullData[$id] = array_merge($fullData,$statsFileArray);
        }
    }

    $dataSlider = [];
    $dataSliderSortedPids = [];


    foreach($imagesFullData as $id => $fullData) {
        if(!empty($WpUserIdsData['mainDataWpUserIds'][$id])){$imagesFullData[$id]['WpUserId'] = $WpUserIdsData['mainDataWpUserIds'][$id];}
    }

    foreach($recentMainData as $id => $recentData) {
        if(!empty($WpUserIdsData['mainDataWpUserIds'][$id])){$recentMainData[$id]['WpUserId'] = $WpUserIdsData['mainDataWpUserIds'][$id];}
    }

    $imagesFullData = cg1l_frontend_apply_current_multiple_files_to_dataset($imagesFullData, $queryDataArray);

    $entryFullDataBeforeAllowedFilter = [];
    if(!empty($entryId) && !empty($imagesFullData[$entryId])){
        $entryFullDataBeforeAllowedFilter = $imagesFullData[$entryId];
    }

    $shortcodesWithAllowedRealIds = [
        'cg_gallery' => true,
        'cg_gallery_no_voting' => true,
        'cg_gallery_user' => true,
        'cg_gallery_winner' => true,
        'cg_gallery_ecommerce' => true
    ];
    $shouldUseAllowedRealIds = !empty($shortcodesWithAllowedRealIds[$shortcode_name]);

    if($shortcode_name === 'cg_gallery_ecommerce' && !empty($isFromOrderSummary)){
        $shouldUseAllowedRealIds = false;
    }

    if($shouldUseAllowedRealIds){
        $allowedRealIds = cg1l_frontend_get_allowed_real_ids($imagesFullData, $shortcode_name, $categoriesFullData, $options, $WpUserId);
        $imagesFullData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($imagesFullData, $allowedRealIds);
        $queryDataArray = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($queryDataArray, $allowedRealIds);
        $jsonCommentsData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($jsonCommentsData, $allowedRealIds);
        $jsonInfoData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($jsonInfoData, $allowedRealIds);
        $WpUserIdsData['mainDataWpUserIds'] = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($WpUserIdsData['mainDataWpUserIds'], $allowedRealIds);
    }

    $imagesFullDataOriginalLength = count($imagesFullData);
    $imagesFullDataArrays = cg1l_get_data_images_full_data_sorted($options,$imagesFullData);
    if(
        !is_array($imagesFullDataArrays) ||
        !isset($imagesFullDataArrays['imagesFullDataFull']) ||
        !is_array($imagesFullDataArrays['imagesFullDataFull']) ||
        !isset($imagesFullDataArrays['imagesFullDataSliced']) ||
        !is_array($imagesFullDataArrays['imagesFullDataSliced'])
    ){
        $picsPerSiteFallback = !empty($options['general']['PicsPerSite']) ? absint($options['general']['PicsPerSite']) : count($imagesFullData);
        $imagesFullDataArrays = cg1l_frontend_get_default_sorted_images_full_data($imagesFullData, $picsPerSiteFallback);
    }
    $imagesFullData = $imagesFullDataArrays['imagesFullDataFull'];
    $imagesFullDataSliced = $imagesFullDataArrays['imagesFullDataSliced'];
    $downloadSaleEntryIdsLookup = [];
    $imageIdsForSlider = array_map('intval', array_keys($imagesFullData));
    $imageIdsForSlider = array_filter($imageIdsForSlider);

    if(!empty($imageIdsForSlider)){
        $imageIdsSql = implode(',', $imageIdsForSlider);
        $downloadSaleEntryRows = $wpdb->get_results("SELECT pid FROM $tablename_ecommerce_entries WHERE IsDownload = 1 AND pid IN ($imageIdsSql)");
        foreach($downloadSaleEntryRows as $downloadSaleEntryRow){
            $downloadSaleEntryIdsLookup[intval($downloadSaleEntryRow->pid)] = true;
        }
    }

    foreach ($imagesFullData as $fullData){
        $dataSliderSortedPids[] = intval($fullData['id']);// required for javascript indexof array check
        cg1l_set_slider_data($fullData,$dataSlider,$options,$countUserVotes,$downloadSaleEntryIdsLookup);
    }

    if(empty($entryId)){
        $imagesFullDataToProcess = $imagesFullDataSliced;
        // recent data might still not contain all required data
        foreach ($imagesFullDataToProcess as $id => $someData) {
            // all have to be checked with simple if
            if(empty($recentMainData[$id])){
                $recentMainData[$id] = cg1l_get_entry_main_data($galeryID,$id);
            }
            if(empty($recentQueryData[$id])){
                $recentQueryData = array_replace($recentQueryData, cg1l_get_entry_query_data($id));
            }
            if(empty($recentUrlsData[$id])){
                $recentUrlsData[$id] = cg1l_get_entry_urls_data($id,$recentMainData,$shortcode_name);
            }
            if(empty($recentStatsData[$id])){
                $recentStatsData[$id] = cg1l_get_entry_stats_data($galeryID,$id);
            }
            if(empty($recentCommentsData[$id])){
                $recentCommentsData[$id] = cg1l_get_entry_comments_data($galeryID,$id);
            }
            if(empty($recentInfoData[$id])){
                $entryInfoData = cg1l_get_entry_info_data($galeryID,$id);
                if(!empty($entryInfoData[$id]) && is_array($entryInfoData[$id])){
                    $recentInfoData[$id] = $entryInfoData[$id];
                }
            }
        }
    }

    $recentMainData = cg1l_frontend_apply_current_multiple_files_to_dataset($recentMainData, $recentQueryData);

    $recentMainData = cgl_flatten_to_id_array($recentMainData);
    $recentUrlsData = cgl_flatten_to_id_array($recentUrlsData);
    $recentStatsData = cgl_flatten_to_id_array($recentStatsData);
    $recentCommentsData = cgl_flatten_to_id_array($recentCommentsData);
    // recent info is already keyed by real ID => field map and has no inner "id" property.
    // Flattening would drop normal info payloads entirely.

    if($shouldUseAllowedRealIds){
        $recentMainData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($recentMainData, $allowedRealIds);
        $recentQueryData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($recentQueryData, $allowedRealIds);
        $recentUrlsData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($recentUrlsData, $allowedRealIds);
        $recentStatsData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($recentStatsData, $allowedRealIds);
        $recentCommentsData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($recentCommentsData, $allowedRealIds);
        $recentInfoData = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($recentInfoData, $allowedRealIds);
    }

    $isNotActivatedForSelling = false;
    $hasEcommerceEntry = false;
    $isEntryOnlyEcommerceRequest = (
        !empty($entryId) &&
        empty($isFromOrderSummary) &&
        (empty($cgEntryId) || $entryId == $cgEntryId)
    );

    foreach ($imagesFullData as $id => $data) {
        if(!empty($data['EcommerceEntry'])){// so data will be also displayed in other types of cg_gallery shortcodes also... not only cg_gallery_ecommerce
            $hasEcommerceEntry = true;
        }
    }

    if(
        $shortcode_name == 'cg_gallery_ecommerce' &&
        $isEntryOnlyEcommerceRequest &&
        !empty($entryFullDataBeforeAllowedFilter) &&
        empty($entryFullDataBeforeAllowedFilter['EcommerceEntry'])
    ){
        $isNotActivatedForSelling = true;
    }

    // don't change the order with deactivated check, this is so far the best order logic combination
    if($shortcode_name == 'cg_gallery_ecommerce' && $isNotActivatedForSelling){
        echo '<p style="text-align: center;margin-top: 50px;font-size: 22px;"><b>Not activated for selling</b></p>';
        return;
    }

}

if(empty($entryId)){
    $picsPerPage = (!empty($options['general']['PicsPerSite'])) ? intval($options['general']['PicsPerSite']) : 0;
    if($picsPerPage > 0 && count($imagesFullData) > $picsPerPage){
        $totalPagesNumber = (int) ceil(count($imagesFullData) / $picsPerPage);
        $currentPageNumberForRender = max(1, min((int) $currentPageNumber, $totalPagesNumber));
        $offset = ($currentPageNumberForRender - 1) * $picsPerPage;
        $imagesFullDataCurrentPage = array_slice($imagesFullData, $offset, $picsPerPage, true);
    }else{
        $imagesFullDataCurrentPage = $imagesFullData;
    }
}

cg1l_load_language_file_categorized(__DIR__.'/../../check-language.php', 'gallery', $languageNames);
cg1l_load_language_file_categorized(__DIR__.'/../../check-language-general.php', 'general', $languageNames);
cg1l_load_language_file_categorized(__DIR__.'/../../check-language-ecommerce.php', 'ecommerce', $languageNames);

if(!empty($entryId) && $shortcode_name == 'cg_gallery_user'){
	$isNotUsersEntryId = false;
	if(!is_user_logged_in()){
		$isNotUsersEntryId = true;
	}else{
		$WpUserId = get_current_user_id();
		$WpUserIdToCheck = $wpdb->get_var("SELECT WpUserId FROM $tablename WHERE id = '$entryId'");
		if($WpUserId!=$WpUserIdToCheck){
			$isNotUsersEntryId = true;
		}
	}
	if($isNotUsersEntryId){
		echo '<p style="text-align: center;margin-top: 50px;font-size: 22px;"><b>'.$language_OnlyVisibleForRegisteredAndLoggedInOwner.'</b></p>';
		return;
	}
}

$intervalConf = cg_shortcode_interval_check($galeryID,$optionsSource,$cg_gallery_shortcode_type);

if($intervalConf['shortcodeIsActive'] && empty($isFromOrderSummary)){
    if(!empty($entryId) && !empty($cgEntryId) && $entryId==$cgEntryId && $isOnlyGalleryWinner && !empty($entryFullDataBeforeAllowedFilter) && empty($entryFullDataBeforeAllowedFilter['Winner'])){
        echo "<p class='mainCGentryNotWinnerMessage' >$language_ThisEntryIsNotAWinner</p>";
        return;
    }
}

if($intervalConf['shortcodeIsActive'] && empty($isFromOrderSummary)){
    if(!empty($entryId) && !empty($cgEntryId) && $entryId==$cgEntryId && $isOnlyGalleryEcommerce && !empty($entryFullDataBeforeAllowedFilter) && empty($entryFullDataBeforeAllowedFilter['EcommerceEntry'])){
        echo "<p class='mainCGentryNotEntryMessage' >Not set for sale</p>";
        return;
    }
}

// simple check here, user might have different purposes on entry page, so other gallery shortcodes might be also inserted here
if(!empty($entryId) && !empty($cgEntryId) && $entryId==$cgEntryId && empty($imagesFullData[$entryId])){
    $entryLandingPageFilterReason = [];
    $categoriesFullDataForEntryLandingPage = (!empty($categoriesFullData) && is_array($categoriesFullData)) ? $categoriesFullData : [];
    if(!empty($entryFullDataBeforeAllowedFilter)){
        $entryLandingPageFilterReason = cg1l_frontend_get_entry_filter_block_reason($shortcode_name, $entryFullDataBeforeAllowedFilter, $categoriesFullDataForEntryLandingPage, $options, $WpUserId);
    }

    if(!empty($entryLandingPageFilterReason['type']) && $entryLandingPageFilterReason['type'] === 'category'){
        $categoryNameToShow = (!empty($entryLandingPageFilterReason['categoryName'])) ? $entryLandingPageFilterReason['categoryName'] : $language_Other;
        $categoryNameToShow = contest_gal1ery_convert_for_html_output_without_nl2br($categoryNameToShow);
        echo '<p style="font-size: 20px; text-align: center;">Category <b>'.$categoryNameToShow.'</b> is deactivated.</p>';
    }else{
        echo contest_gal1ery_convert_for_html_output_without_nl2br($options['visual']['TextDeactivatedEntry']);
    }
    return;
}

$isCgWpPageEntryLandingPage = false;
$cgWpPageEntryLandingPageGid = false;
$cgWpPageEntryLandingPageRealGid = false;
$cgWpPageEntryLandingPageShortCodeName = '';
if(!empty($entryId) && !empty($cgEntryId)  && $entryId==$cgEntryId){
    $isCgWpPageEntryLandingPage = true;
    $cgWpPageEntryLandingPageGid = $galeryIDuserForJs;
    $cgWpPageEntryLandingPageRealGid = $galeryID;
	$cgWpPageEntryLandingPageShortCodeName = $shortcode_name;
}

$isShowGallery = true;

if((isset($options['pro']['RegUserGalleryOnly']) && $options['pro']['RegUserGalleryOnly']==1 && $is_user_logged_in == false) || ($isUserGallery == true && $is_user_logged_in == false)){
    $isShowGallery = false;
}

if($isShowGallery == true){

    $jsonImagesCount = count($imagesFullData);

    $jsonCategories = array();

    $jsonCategoriesFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-categories.json';
    if(file_exists($jsonCategoriesFile)){
        $fp = fopen($jsonCategoriesFile, 'r');
        $jsonCategories = json_decode(fread($fp,filesize($jsonCategoriesFile)),true);
        fclose($fp);
    }

    $userIP = cg1l_sanitize_method(cg_get_user_ip());
    $userIPtype = cg1l_sanitize_method(cg_get_user_ip_type());
    $userIPisPrivate = cg_check_if_ip_is_private($userIP);
    $userIPtypesArray = cg_available_ip_getter_types();

    if($is_user_logged_in){
        $wpUserId = get_current_user_id();
    }
    else{
        $wpUserId=0;
    }

    $wp_create_nonce = wp_create_nonce("check");

    if(empty($options['visual']['BlogLook'])){
        $options['visual']['BlogLook'] = 0;
    }

    // Legacy slider/blog gallery views are retired; render them as Masonry.
    if(!empty($options['general']['SliderLook']) || !empty($options['visual']['BlogLook'])){
        $options['general']['ThumbLook'] = 1;
        $options['general']['SliderLook'] = 0;
        $options['visual']['BlogLook'] = 0;
    }

    $LooksCount = 0;
    if($options['general']['ThumbLook'] == 1){$LooksCount++;}
    if($options['general']['HeightLook'] == 1){$LooksCount++;}
    if($options['general']['RowLook'] == 1){$LooksCount++;}
    if($options['general']['SliderLook'] == 1){$LooksCount++;}
    if($options['visual']['BlogLook'] == 1){$LooksCount++;}

    if(empty($options['pro']['SlideTransition'])){
        $options['pro']['SlideTransition']='translateX';
    }

    $ShowCatsUnchecked = 0;
    if(!empty($options['pro']['ShowCatsUnchecked'])){
        $ShowCatsUnchecked = 1;
    }
    $options['pro']['ShowCatsUnfolded'] = 1;

    $check = wp_create_nonce("check");
    $p_cgal1ery_db_version = get_option( "p_cgal1ery_db_version" );
    $upload_folder = wp_upload_dir();
    $upload_folder_url = $upload_folder['baseurl']; // Pfad zum Bilderordner angeben

    $wpNickname = '';

    if($is_user_logged_in){$current_user = wp_get_current_user();$wpNickname = $current_user->display_name;}

    if(is_ssl()){
        if(strpos($upload_folder_url,'http://')===0){
            $upload_folder_url = str_replace( 'http://', 'https://', $upload_folder_url );
        }
    }
    else{
        if(strpos($upload_folder_url,'https://')===0){
            $upload_folder_url = str_replace( 'https://', 'http://', $upload_folder_url );
        }
    }

    // correction of old five star
    if($options['general']['AllowRating']==1){
        $options['general']['AllowRating']=15;
    }

    if($options['general']['CheckLogin']==1 and ($options['general']['AllowRating']==1 or $options['general']['AllowRating']>=12 or $options['general']['AllowRating']==2)){
        if($is_user_logged_in){$UserLoginCheck = 1;$current_user = wp_get_current_user();$wpNickname = $current_user->display_name;} // Allow only registered users to vote (Wordpress profile) is activated by this
        else{$UserLoginCheck=0;}// Allow only registered users to vote (Wordpress profile): is deactivated by this
    }
    else{$UserLoginCheck=0;}

    $cgGalleryStyle = 'center-black';
    $cgCenterWhite = false;

    if(!empty($options['visual']['FeControlsStyle'])){
        if($options['visual']['FeControlsStyle']=='white'){
            $cgGalleryStyle='center-white';
            $cgCenterWhite=true;
        }
    }

    $cgl_heart = '';
    if(!empty($options['visual']['FeVotingIconType']) && $options['visual']['FeVotingIconType']=='heart'){
        $cgl_heart = 'cgl_heart';
    }

    $CheckLogin = 0;
    if(isset($options['general']['CheckLogin'])){// to go sure there is no undefined key error
        if($options['general']['CheckLogin']==1){
            $CheckLogin = 1;
        }
    }

    if(!empty($entryId) && !empty($cgEntryId) && $entryId==$cgEntryId){
        echo "<input type='hidden' id='mainCGdivEntryPageHiddenInput' value='true'>";
    }

    global $wp;
    $cgPageUrl = home_url( $wp->request );
    echo "<input type='hidden' id='cgPageUrl' value='$cgPageUrl'>";
    echo "<input type='hidden' id='cgIsUserLoggedIn' value='$is_user_logged_in'>";

    if(empty($options['general']['CheckIp']) && empty($options['general']['CheckLogin']) && empty($options['general']['CheckCookie'])){
        $options['general']['CheckIp']=1;
    }

    $cgFeControlsStyle = 'cg_fe_controls_style_white';
    $BorderRadiusClass = '';
    if(!empty($isOnlyContactForm)){

        $optionsVisual = $wpdb->get_row($wpdb->prepare( "SELECT * FROM $tablename_options_visual WHERE GalleryID = %d"  ,[$galeryID]));
        $FeControlsStyleUpload = $optionsVisual->FeControlsStyleUpload;
        $BorderRadiusUpload = $optionsVisual->BorderRadiusUpload;

        if($FeControlsStyleUpload=='black'){
            $cgFeControlsStyle='cg_fe_controls_style_black';
        }
        if($BorderRadiusUpload=='1' || empty($optionsVisual->FeControlsStyleUpload)){
            $BorderRadiusClass = 'cg_border_radius_controls_and_containers';
        }
    }else{
        if(!empty($options['visual']['FeControlsStyle'])){
            if($options['visual']['FeControlsStyle']=='black'){
                $cgFeControlsStyle='cg_fe_controls_style_black';
            }
        }
        $BorderRadiusClass = 'cg_border_radius_controls_and_containers';
    }

	if(!empty($isCGalleries)){
		$cgFeControlsStyle = 'cg_fe_controls_style_white';
		$BorderRadiusClass = 'cg_border_radius_controls_and_containers';
        if($galleriesOptions['FeControlsStyle']=='black'){
	        $cgFeControlsStyle='cg_fe_controls_style_black';
        }
    }

    $cgHideDivContainerClass = '';

    if($isUserGallery && !$is_user_logged_in){
        $cgHideDivContainerClass = 'cg_hide';
    }

    if(!empty($entryId) && !empty($options['visual']['TextBeforeWpPageEntry']) && !empty($isCgWpPageEntryLandingPage)){
        echo "<div class='mainCGlanding'>";
            echo contest_gal1ery_convert_for_html_output_without_nl2br($options['visual']['TextBeforeWpPageEntry']);
        echo "</div>";
    }elseif(!empty($isCgParentPage) && !empty($options['visual']['TextBeforeWpPageParent'])){
        echo "<div class='mainCGlanding'>";
        echo contest_gal1ery_convert_for_html_output_without_nl2br($options['visual']['TextBeforeWpPageParent']);
        echo "</div>";
    }elseif(!empty($isGalleriesMainPage) && !empty($galleriesOptions['TextBeforeWpPageGalleries'])){
        echo "<div class='mainCGlanding'>";
        echo contest_gal1ery_convert_for_html_output_without_nl2br($galleriesOptions['TextBeforeWpPageGalleries']);
        echo "</div>";
    }

    $entryLandingNeighborsHtml = '';

    if(!$intervalConf['shortcodeIsActive'] && empty($isFromOrderSummary) && empty($isCGalleries)){
            echo contest_gal1ery_convert_for_html_output_without_nl2br($intervalConf['TextWhenShortcodeIntervalIsOff']);
    }
    else{

        echo contest_gal1ery_convert_for_html_output_without_nl2br($intervalConf['TextWhenShortcodeIntervalIsOn']);

        echo "<input type='hidden' class='cg-loaded-gids' value='$galeryIDuserForJs' data-cg-short='$galeryIDshort'
 data-cg-real-gid='$realGid' data-cg-gid='$galeryIDuserForJs' >";

// --- Data and Configuration Additions ---
$table_usermeta = $wpdb->base_prefix . "usermeta";
$tablename_contact_options = $wpdb->prefix . "contest_gal1ery_contact_options";
$tablenameEcommerceOptions = $wpdb->prefix . "contest_gal1ery_ecommerce_options";
$tablenameEcommerceEntries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
$tablename_ecommerce_options = $wpdb->prefix . "contest_gal1ery_ecommerce_options";

$fromCommentsWpUserIdsArray = [];
$isAllowCommentsForOneOfTheGalleries = false;
if(!empty($options['general']['AllowComments'])){
    $isAllowCommentsForOneOfTheGalleries = true;
    $fromCommentsWpUserIdsQueryResults = $wpdb->get_results( "SELECT DISTINCT WpUserId FROM $tablenameComments WHERE WpUserId > 0 AND GalleryID = $galeryID");
    foreach($fromCommentsWpUserIdsQueryResults as $row){
        if(empty($fromCommentsWpUserIdsArray[$row->WpUserId])){
            $fromCommentsWpUserIdsArray[$row->WpUserId] = $row->WpUserId;
        }
    }
}

$isFbLikeOnlyShareOn = false;
if(isset($options['pro']['FbLikeOnlyShare'])){
    if($options['pro']['FbLikeOnlyShare']==1){
        $options['general']['FbLike'] = 1;
        $isFbLikeOnlyShareOn = true;
    }
}

$WpUserEmail = '';
if($is_user_logged_in){
    $current_user = wp_get_current_user();
    $WpUserEmail = $current_user->user_email;
    $wpNickname = get_user_meta( $WpUserId, 'nickname');
    if(is_array($wpNickname)){
        $wpNickname = $wpNickname[0];
    }
}

$UploadedUserFilesAmount = 0;
$UploadedUserFilesAmountPerCategories = null;
$UploadedUserFilesAmountPerCategoryArray = [];
$CookieId = '';

if(!empty($options['pro']['RegUserUploadOnly'])){
    if($options['pro']['RegUserUploadOnly']==1 && !empty($options['pro']['RegUserMaxUpload']) && is_user_logged_in()==true){
        $UploadedUserFilesAmount = $wpdb->get_var("SELECT COUNT(*) FROM $tablename WHERE WpUserId = '$WpUserId' and GalleryID = '$galeryID'");
    }elseif($options['pro']['RegUserUploadOnly']==2 && !empty($options['pro']['RegUserMaxUpload'])){
        $CookieId = cg_get_valid_frontend_cookie($galeryID,'upload',true);
        if(!empty($CookieId)){
            $UploadedUserFilesAmount = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $tablename WHERE CookieId = %s and GalleryID = %d",
                $CookieId,
                $galeryID
            ));
        }else{
            $UploadedUserFilesAmount = 0;
        }
    }elseif($options['pro']['RegUserUploadOnly']==3 && !empty($options['pro']['RegUserMaxUpload'])){
        $UploadedUserFilesAmount = $wpdb->get_var("SELECT COUNT(*) FROM $tablename WHERE IP = '$userIP' and GalleryID = '$galeryID'");
    }

    if($options['pro']['RegUserUploadOnly']==1 && !empty($options['pro']['RegUserMaxUploadPerCategory']) && is_user_logged_in()==true){
        $UploadedUserFilesAmountPerCategories = $wpdb->get_results("SELECT Category FROM $tablename WHERE WpUserId = '$WpUserId' and GalleryID = '$galeryID'");
    }elseif($options['pro']['RegUserUploadOnly']==2 && !empty($options['pro']['RegUserMaxUploadPerCategory'])){
        $CookieId = cg_get_valid_frontend_cookie($galeryID,'upload',true);
        if(!empty($CookieId)){
            $UploadedUserFilesAmountPerCategories = $wpdb->get_results($wpdb->prepare(
                "SELECT Category FROM $tablename WHERE CookieId = %s and GalleryID = %d",
                $CookieId,
                $galeryID
            ));
        }else{
            $UploadedUserFilesAmountPerCategories = null;
        }
    }elseif($options['pro']['RegUserUploadOnly']==3 && !empty($options['pro']['RegUserMaxUploadPerCategory'])){
        $UploadedUserFilesAmountPerCategories = $wpdb->get_results("SELECT Category FROM $tablename WHERE IP = '$userIP' and GalleryID = '$galeryID'");
    }

    if(!empty($UploadedUserFilesAmountPerCategories)){
        $UploadedUserFilesAmountPerCategoryArray = [];
        foreach ($UploadedUserFilesAmountPerCategories as $rowObject){
            if(!isset($UploadedUserFilesAmountPerCategoryArray[$rowObject->Category])){
                $UploadedUserFilesAmountPerCategoryArray[$rowObject->Category] = 1;
            }else{
                $UploadedUserFilesAmountPerCategoryArray[$rowObject->Category]++;
            }
        }
    }
}

$ShowFormAfterUploadOrContact = $wpdb->get_var( "SELECT ShowFormAfterUpload FROM $contest_gal1ery_options_input WHERE GalleryID='$galeryID'");

$ClientIdLive = '';
$ClientIdSandbox = '';
$CurrencyShort = '';
$CurrencyPosition = '';
$PriceDivider = '';
if(empty($isFromOrderSummary)){
    $currenciesArray = [];
}

$ecommerceOptions = [];
$ecommerceCountries = [];
$ecommerceCountriesStatesCodes = [];

if((!empty($isOnlyGalleryEcommerce) || !empty($isOnlyUploadFormEcommerce) || !empty($hasEcommerceEntry)) && intval($options['general']['Version'])>=22){

    $ecommerceCountries = cg_get_countries();

    $countriesWithRequiredStatesForShipping = [
            'AR' => 'argentina',
            'BR' => 'brazil',
            'CA' => 'canada',
            'C2' => 'china',
            'IN' => 'india',
            'ID' => 'indonesia',
            'IT' => 'italy',
            'JP' => 'japan',
            'MX' => 'mexico',
            'US' => 'usa'
    ];

    foreach($countriesWithRequiredStatesForShipping as $countryKey => $countryValue){
        $ecommerceCountriesStatesCodes[$countryKey] =  cg_get_country_states_codes($countryValue);
    }

    $currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();

    $ecommerceOptions = json_decode(json_encode($wpdb->get_row( "SELECT * FROM $tablename_ecommerce_options WHERE GeneralID = 1")),true);

    if(!empty($ecommerceOptions['AllowedCountries'])){
        $ecommerceOptions['AllowedCountries'] = unserialize($ecommerceOptions['AllowedCountries']);
    }
    if(!empty($ecommerceOptions['AllowedCountriesTranslations'])){
        $ecommerceOptions['AllowedCountriesTranslations'] = unserialize($ecommerceOptions['AllowedCountriesTranslations']);
    }

    $ecommerceFilesData = cg_get_ecommerce_files_data($galeryID);

    $ecommerceViewerUserId = (!empty($WpUserId)) ? absint($WpUserId) : 0;
    foreach($ecommerceFilesData as $ecommerceRealId => $ecommerceFileData){
        $ecommerceFilesData[$ecommerceRealId]['SourceShortcodeName'] = $shortcode_name;
        $ecommerceFilesData[$ecommerceRealId]['RawDataAccessHash'] = cg1l_get_ecommerce_raw_data_access_hash($galeryID,$ecommerceRealId,$shortcode_name,$ecommerceViewerUserId);
    }

    if(isset($ecommerceOptions['PayPalLiveSecret'])){ unset($ecommerceOptions['PayPalLiveSecret']); }
    if(isset($ecommerceOptions['PayPalSandboxSecret'])){ unset($ecommerceOptions['PayPalSandboxSecret']); }
    if(isset($ecommerceOptions['StripeLiveSecret'])){ unset($ecommerceOptions['StripeLiveSecret']); }
    if(isset($ecommerceOptions['StripeSandboxSecret'])){ unset($ecommerceOptions['StripeSandboxSecret']); }
}

if(($options['general']['AllowRating']==1 OR $options['general']['AllowRating']>=12)  && empty($isOnlyContactForm)) {
    if(
            empty($isOnlyGalleryNoVoting) ||
            (!empty($isOnlyGalleryNoVoting) && $RatingVisibleForGalleryNoVoting)
    )
    {
        if(!empty($isOnlyGalleryEcommerce) && empty($options['general']['RatingVisibleForGalleryEcommerce'])){
            // do nothing
        }else{
            include ('data/rating/configuration-five-star.php');
        }
    }
}elseif($options['general']['AllowRating']==2 &&  empty($isOnlyContactForm)) {
    if(
            empty($isOnlyGalleryNoVoting) ||
            (!empty($isOnlyGalleryNoVoting)  && $RatingVisibleForGalleryNoVoting)
    ){
        include('data/rating/configuration-one-star.php');
    }
}

// --- End Data and Configuration Additions ---

        echo "<div id='mainCGdivContainer$galeryIDuserForJs' class='mainCGdivContainer cg_overflow_hidden  $cgHideDivContainerClass' data-cg-gid='$galeryIDuserForJs'>";
        echo "<div id='mainCGdivHelperParent$galeryIDuserForJs' class='mainCGdivHelperParent cg_display_block $cgFeControlsStyle $cgl_heart' data-cg-gid='$galeryIDuserForJs'>";
        echo "<div id='cgLdsDualRingDivGalleryHide$galeryIDuserForJs' class='cg_hide cg-lds-dual-ring-div-gallery-hide cg-lds-dual-ring-div-gallery-hide-parent $BorderRadiusClass $cgFeControlsStyle'><div class='cg-lds-dual-ring-gallery-hide $cgFeControlsStyle'></div></div>";

        $isOnlyGalleryEcommerceClass = '';
        if($isOnlyGalleryEcommerce){
            $isOnlyGalleryEcommerceClass = 'isOnlyGalleryEcommerceClass';
        }

        $isCgWpPageEntryLandingPageClass = '';
        if($isCgWpPageEntryLandingPage){
            $isCgWpPageEntryLandingPageClass = 'isCgWpPageEntryLandingPageClass';
        }

	    $cgAjaxClass = '';
	    if(!empty($isAjax)){
	        $cgAjaxClass = 'cg_is_ajax';
        }

        $Version = $options['general']['Version'];

        $options['visual']['BlogLook'] = (!empty($options['visual']['BlogLook']) )? $options['visual']['BlogLook'] : 0;
        $BlogLookOrder = (!empty($options['visual']['BlogLookOrder']) )? $options['visual']['BlogLookOrder'] : 5;
        // Order of views will be determined
        $ThumbLookOrder = $options['general']['ThumbLookOrder'];
        $HeightLookOrder = $options['general']['HeightLookOrder'];
        $RowLookOrder = $options['general']['RowLookOrder'];
        $SliderLookOrder = $options['general']['SliderLookOrder'];
        $BlogLookOrder = (!empty($options['visual']['BlogLookOrder']) ) ? $options['visual']['BlogLookOrder'] : 5;
        // since 21.2.0
        // then remove heightLookOrder and also the old rowLookOrder and switch to thumbLook, also check for some strange values in thumbLook in JS
        if($HeightLookOrder==1 || $RowLookOrder==1){$ThumbLookOrder=1;}
        // since 21.2.0 $HeightLookOrder and $RowLookOrder completely
        $orderGalleries = array($SliderLookOrder =>'SliderLookOrder', $ThumbLookOrder =>'ThumbLookOrder', $BlogLookOrder => 'BlogLookOrder');
        ksort($orderGalleries);

        $currentLook = 'blog';
        foreach ($orderGalleries as $value) {
            if($value == 'BlogLookOrder' && $options['visual']['BlogLook']==1){$currentLook = 'blog';break;}
            if($value == 'ThumbLookOrder' && $options['general']['ThumbLook']==1){$currentLook = 'thumb';break;}
            if($value == 'SliderLookOrder' && $options['general']['SliderLook']==1){$currentLook = 'slider';break;}
        }

        $sliderViewMainClass = '';
        if($currentLook=='slider'){
            $sliderViewMainClass = 'cg-slider-view';
        }

        echo "<div id='mainCGdiv$galeryIDuserForJs' class='mainCGdiv $CGalleriesMainPageClass $cgAjaxClass $isOnlyGalleryEcommerceClass $isCgWpPageEntryLandingPageClass $cgFeControlsStyle $BorderRadiusClass $cgl_heart $sliderViewMainClass' data-cg-gid='$galeryIDuserForJs' data-cg-version='$Version'>";

        if($currentLook=='thumb'){
            echo '<div id="cglSliderNav'.$galeryIDuserForJs.'" class="cg_hide cgl_nav" data-cg-gid="'.$galeryIDuserForJs.'">
                    <button class="cgl_nav_btn cgl_nav_up" aria-label="Previous">&#9650;</button>
                    <button class="cgl_nav_btn cgl_nav_down" aria-label="Next">&#9660;</button>
            </div>';
        }

        include (__DIR__.'/gallery/gallery-upload-form-options.php');

        $cg_skeleton_loader_on_page_load_div_hide = 'cg_hide';
        include("gallery/gallery-loaders.php");

	        if(!empty($entryId) && !empty($options['visual']['ShowBackToGalleryButton'])){

	            $entryPermalink = '';// in case deactivated
	            $configuredBackToGalleryUrl = '';
	            $entryFallbackRootUrl = '';
	            $entryBackOriginUrl = '';

	            $options['visual']['BackToGalleryButtonText']=$language_BackToGallery;
	            if(!empty($options['pro']['BackToGalleryButtonURL'])){
	                $configuredBackToGalleryUrl = contest_gal1ery_convert_for_html_output_without_nl2br($options['pro']['BackToGalleryButtonURL']);
	            }
	            if(!empty($imagesFullData[$entryId][$WpPageShortCodeType])){// might happen if original WpUpload was deleted in WP library
		            $entryFallbackRootUrl = get_permalink(wp_get_post_parent_id($imagesFullData[$entryId][$WpPageShortCodeType]));
	            }
	            $entryBackOriginUrl = (!empty($cgFromGalleriesUrl)) ? $cgFromGalleriesUrl : $cglOriginPageUrl;
	            if(
	                !empty($isCgWpPageEntryLandingPage) &&
	                !empty($isMultiGalleryContext) &&
	                empty($cgFromGalleriesUrl) &&
	                empty($cglOriginPageId) &&
	                !empty($entryFallbackRootUrl) &&
	                cgl_strip_navigation_query_args($entryBackOriginUrl) === cgl_strip_navigation_query_args($currentUrl)
	            ){
	                $entryBackOriginUrl = $entryFallbackRootUrl;
	            }

	            $entryPermalink = cgl_build_entry_back_url([
	                'has_multi_gallery_context' => (!empty($cglMultiContextGalleryId) && !empty($isMultiGalleryContext)),
	                'multi_gallery_id' => $cglMultiContextGalleryId,
	                'multi_page_number' => max(1, $backToGalleriesFromPageNumber),
	                'origin_page_id' => $cglOriginPageId,
	                'origin_url' => $entryBackOriginUrl,
	                'single_page_number' => $backToGalleryFromPageNumber,
	                'single_back_url' => $configuredBackToGalleryUrl,
	                'fallback_root_url' => $entryFallbackRootUrl,
	            ]);
	            $entryPermalink = cg1l_append_frontend_passthrough_query_args($entryPermalink,[
	                'query_args' => $cgFrontendPassthroughQueryArgs,
	                'is_ecommerce_test' => !empty($isEcommerceTest)
	            ]);

	            if(!empty($isCgWpPageEntryLandingPage) && !empty($entryPermalink)){
		            echo "<div class='mainCGBackToGalleryButtonHrefContainer'>";
		            echo "<a href='".esc_url($entryPermalink)."' class='mainCGBackToGalleryButtonHref' data-cg-gid='$galeryIDuserForJs'>";
		            echo "<div id='mainCGBackToGalleryButton$galeryIDuserForJs' class=' mainCGBackToGalleryButton'>".contest_gal1ery_convert_for_html_output_without_nl2br($options['visual']['BackToGalleryButtonText'])."</div>";
		            echo "</a>";
		            echo "</div>";
	            }

        }

	        if(!empty($isFromGalleriesSelect) || !empty($is_from_single_view_for_cg_galleries)){
	            $backToGalleriesHref = '';

	            if(!empty($cgFromGalleriesUrl)){
	                $backToGalleriesHref = cgl_build_galleries_return_url($cgFromGalleriesUrl, max(1, $backToGalleriesFromPageNumber));
	            }

	            if(empty($backToGalleriesHref)){
	                $fallbackPageNumber = max(1, $backToGalleriesFromPageNumber);
	                $backToGalleriesHref = cgl_build_galleries_return_url($cglOriginPageUrl, $fallbackPageNumber);
	            }

	            $backToGalleriesHref = cg1l_append_frontend_passthrough_query_args($backToGalleriesHref,[
	                'query_args' => $cgFrontendPassthroughQueryArgs,
                'is_ecommerce_test' => !empty($isEcommerceTest)
            ]);

            if(!empty($options['pro']['MainTitleGalleriesView'])){
	            echo "<div class='cgGalleryName'>";
	                echo contest_gal1ery_convert_for_html_output_without_nl2br($options['pro']['MainTitleGalleriesView']);
	            echo "</div>";
            }

	        $is_from_single_view_for_cg_galleries_class = '';
            if(!empty($is_from_single_view_for_cg_galleries)){
	            $is_from_single_view_for_cg_galleries_class = 'cg_is_from_single_view_for_cg_galleries_class';
            }

	        echo "<div class='mainCGBackToGalleryButtonHrefContainer isCGalleries'>";
                echo "<a href='".esc_url($backToGalleriesHref)."' class='mainCGBackToGalleryButtonHref isCGalleries $is_from_single_view_for_cg_galleries_class' data-cg-gid='$galeryIDuserForJs'>";
                    echo "<div id='mainCGBackToGalleryButton$galeryIDuserForJs' class=' mainCGBackToGalleryButton isCGalleries'>$language_BackToGalleries</div>";
                echo "</a>";
	        echo "</div>";
        }elseif(!empty($isCgParentPage) && intval($options['general']['Version'])>=24 && (!isset($options['visual']['ShowBackToGalleriesButton']) || $options['visual']['ShowBackToGalleriesButton'] == 1)){
            if(!empty($options['pro']['BackToGalleriesButtonURL'])){
                $pageGalleries = $options['pro']['BackToGalleriesButtonURL'];
                $pageGalleries = cg1l_append_frontend_passthrough_query_args($pageGalleries,[
                    'query_args' => $cgFrontendPassthroughQueryArgs,
                    'is_ecommerce_test' => !empty($isEcommerceTest)
                ]);
                echo "<div class='mainCGBackToGalleryButtonHrefContainer'>";
                    echo "<a href='$pageGalleries' class='mainCGBackToGalleryButtonHref' data-cg-gid='$galeryIDuserForJs'>";
                    echo "<div id='mainCGBackToGalleryButton$galeryIDuserForJs' class='mainCGBackToGalleryButton'>$language_BackToGalleries</div>";
                    echo "</a>";
                echo "</div>";
            }else{
                $slugName = cg_get_galleries_slug_name($shortcode_name);
                $page = get_page_by_path( $slugName, OBJECT, 'page');
                if(!empty($page)){
                    $pageGalleries = get_permalink($page->ID);
                    if($backToGalleriesFromPageNumber > 1){
                        $pageGalleries = add_query_arg('cgl_page',$backToGalleriesFromPageNumber,$pageGalleries);
                    }
                    $pageGalleries = cg1l_append_frontend_passthrough_query_args($pageGalleries,[
                        'query_args' => $cgFrontendPassthroughQueryArgs,
                        'is_ecommerce_test' => !empty($isEcommerceTest)
                    ]);
                    echo "<div class='mainCGBackToGalleryButtonHrefContainer'>";
                    echo "<a href='$pageGalleries' class='mainCGBackToGalleryButtonHref' data-cg-gid='$galeryIDuserForJs'>";
                    echo "<div id='mainCGBackToGalleryButton$galeryIDuserForJs' class='mainCGBackToGalleryButton'>$language_BackToGalleries</div>";
                    echo "</a>";
                    echo "</div>";
                }
            }
	    }

        if(is_user_logged_in()){
            if(current_user_can('manage_options')){
                $galleryJsonCommentsDir = $wp_upload_dir['basedir'].'/contest-gallery/changes-messages-frontend';
                if (!is_dir($galleryJsonCommentsDir)) {
                    mkdir($galleryJsonCommentsDir, 0755, true);
                }
                ###NORMAL###
                $cgPro = false;

                $arrayNew = array(
                    '824f6b8e4d606614588aa97eb8860b7e',
                    'add4012c56f21126ba5a58c9d3cffcd7',
                    'bfc5247f508f427b8099d17281ecd0f6',
                    'a29de784fb7699c11bf21e901be66f4e',
                    'e5a8cb2f536861778aaa2f5064579e29',
                    '36d317c7fef770852b4ccf420855b07b'
                );

                if(file_exists($wp_upload_dir['basedir'].'/contest-gallery/changes-messages-frontend/pro-check.txt')){
                    $cgPro = file_get_contents($wp_upload_dir['basedir'].'/contest-gallery/changes-messages-frontend/pro-check.txt');
                    if($cgPro=='true'){
                        include('normal/download-proper-pro-version-info-frontend-area.php');
                    }
                }elseif(!file_exists($wp_upload_dir['basedir'].'/contest-gallery/changes-messages-frontend/pro-check.txt')){// if not exists, then one check and create file

                    // Check start from here:
                    $p_cgal1ery_reg_code = get_option("p_cgal1ery_reg_code");
                    $p_c1_k_g_r_8 = get_option("p_c1_k_g_r_9");
                    if((!empty($p_cgal1ery_reg_code) AND $p_cgal1ery_reg_code!='1') OR (!empty($p_c1_k_g_r_8) AND $p_c1_k_g_r_8!='1')){
                        $cgPro = true;
                    }

                    if (!is_dir($wp_upload_dir['basedir'].'/contest-gallery/changes-messages-frontend')) {
                        mkdir($wp_upload_dir['basedir'].'/contest-gallery/changes-messages-frontend', 0755);
                    }

                    if($cgPro){
                        file_put_contents($wp_upload_dir['basedir'].'/contest-gallery/changes-messages-frontend/pro-check.txt','true');
                        include('normal/download-proper-pro-version-info-frontend-area.php');
                    }else{
                        file_put_contents($wp_upload_dir['basedir'].'/contest-gallery/changes-messages-frontend/pro-check.txt','false');
                    }
                }
                ###NORMAL-END###
            }
        }

        echo "<div id='mainCGdivHelperChild$galeryIDuserForJs' class='mainCGdivHelperChild' data-cg-gid='$galeryIDuserForJs'>";

        echo "<div id='mainCGdivFullWindowConfigurationArea$galeryIDuserForJs' class='mainCGdivFullWindowConfigurationArea cg-header-controls-show-only-full-window cg_hide $cgFeControlsStyle' data-cg-gid='$galeryIDuserForJs'>";
        echo "<div class='mainCGdivFullWindowConfigurationAreaCloseButtonContainer' data-cg-gid='$galeryIDuserForJs'><div class='mainCGdivFullWindowConfigurationAreaCloseButton' data-cg-gid='$galeryIDuserForJs' ></div></div>";
        echo "</div>";

        echo "<span id='cgViewHelper$galeryIDuserForJs' class='cg_view_helper'></span>";

        echo "<input type='hidden' id='cg_language_i_am_not_a_robot' value='$language_IamNotArobot' >";

        echo "<div id='cg_ThePhotoContestIsOver_dialog' style='display:none;' class='cg_show_dialog'><p>$language_ThePhotoContestIsOver</p></div>";
        echo "<div id='cg_AlreadyRated_dialog' style='display:none;' class='cg_show_dialog'><p>$language_YouHaveAlreadyVotedThisPicture</p></div>";
        echo "<div id='cg_AllVotesUsed_dialog' style='display:none;' class='cg_show_dialog'><p>$language_AllVotesUsed</p></div>";

        $shouldRenderHeader = !($isGalleryShortcode && !empty($entryId)) || (!empty($entryId) && !empty($isOnlyGalleryEcommerce));
        if($shouldRenderHeader){
            echo "<div style='visibility: hidden;'  class='cg_header cg_hide'>";
                include('gallery/header.php');
            echo "</div>";
        }

        echo "</div>";// Closing mainCGdivHelperChild

        if($imagesFullDataOriginalLength > $options['general']['PicsPerSite']){
            include('gallery/further-images-steps-container.php');
        }

        include('gallery/show-text-until-an-image-added-container.php');

        echo '<div class="cg-lds-dual-ring-div '.$cgFeControlsStyle.' cg_hide"><div class="cg-lds-dual-ring"></div></div>';
        echo "<div id='cgLdsDualRingMainCGdivHide$galeryIDuserForJs' class='cg-lds-dual-ring-div-gallery-hide cg-lds-dual-ring-div-gallery-hide-mainCGallery $cgFeControlsStyle cg_hide'><div class='cg-lds-dual-ring-gallery-hide $cgFeControlsStyle cg-lds-dual-ring-gallery-hide-mainCGallery'></div></div>";

        include('gallery/cg-messages.php');



        $isCGalleriesForwardToWpPageEntry = false;
        if(!empty($isGalleriesMainPage)){
            $isCGalleriesForwardToWpPageEntry = true;
        }


$optionsFullData = json_decode(file_get_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$realGid.'/json/'.$realGid.'-options.json'),true);
if(!empty($isUserGallery) && empty($optionsFullData[$galeryIDuser])){
    $optionsFullData['visual']['ShareButtons'] = '';// unset share buttons for gallery user, because logged in
}

$singleViewOrderFullData = json_decode(file_get_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$realGid.'/json/'.$realGid.'-single-view-order.json'),true);

$formUploadFullData = json_decode(file_get_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$realGid.'/json/'.$realGid.'-form-upload.json'),true);

$singleViewOrderFullData = cg1l_frontend_normalize_single_view_order_data($singleViewOrderFullData,$formUploadFullData);

if(!$isOnlyUploadForm && !$isOnlyContactForm){

    if(empty($categoriesFullData) && file_exists($categoriesFullDataFile)){
        $categoriesFullData = json_decode(file_get_contents($categoriesFullDataFile),true);
        if(!is_array($categoriesFullData)){
            $categoriesFullData = [];
        }
    }

}

include('data/variables-javascript.php');
include('data/variables-javascript-general.php');

if(!empty($cgFrontendPassthroughQueryArgs)){
    if(isset($imagesFullData) && is_array($imagesFullData)){
        $imagesFullData = cg1l_append_frontend_passthrough_query_args_to_entry_guid_data($imagesFullData,[
            'query_args' => $cgFrontendPassthroughQueryArgs
        ]);
    }
    if(isset($imagesFullDataCurrentPage) && is_array($imagesFullDataCurrentPage)){
        $imagesFullDataCurrentPage = cg1l_append_frontend_passthrough_query_args_to_entry_guid_data($imagesFullDataCurrentPage,[
            'query_args' => $cgFrontendPassthroughQueryArgs
        ]);
    }
    if(isset($recentMainData) && is_array($recentMainData)){
        $recentMainData = cg1l_append_frontend_passthrough_query_args_to_entry_guid_data($recentMainData,[
            'query_args' => $cgFrontendPassthroughQueryArgs
        ]);
    }
    if(isset($recentUrlsData) && is_array($recentUrlsData)){
        $recentUrlsData = cg1l_append_frontend_passthrough_query_args_to_entry_guid_data($recentUrlsData,[
            'query_args' => $cgFrontendPassthroughQueryArgs
        ]);
    }
}

        $galleryType = 'cg_grid';
        $galleryInitialMasonryLoadClass = '';
        if($currentLook=='blog'){
            $galleryType = 'cg_blog';
        }elseif($currentLook=='slider'){
            $galleryType = 'cg_slider';
        }

        if($currentLook !== 'blog' && $currentLook !== 'slider'){
            $galleryInitialMasonryLoadClass = 'cg_masonry_flex_loading';
        }

        if($currentLook=='thumb'){
            cg1l_render_vertical_scroll_slider([
                'gid' => $galeryIDuserForJs,
            ]);
        }elseif($currentLook=='slider'){
            cg1l_render_vertical_scroll_slider([
                'gid' => $galeryIDuserForJs,
                'show_loader' => true,
                'is_inline' => true,
                'include_nav' => true,
            ]);
        }
        echo "<div id='mainCGallery$galeryIDuserForJs' data-cg-gid='$galeryIDuserForJs' class='mainCGallery  cg_modern_hover $cgFeControlsStyle  $galleryType $galleryInitialMasonryLoadClass " . ($cgFeControlsStyle == 'cg_fe_controls_style_white' ? 'cg_center_white' : 'cg_center_black') . " $BorderRadiusClass' data-cg-entry-id='$entryId'  data-cg-real-gid='$realGid'>";

        $b64 = base64_encode(rawurlencode(wp_json_encode($options)));

        echo '<textarea class="cg1l-data-options" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($votedUserPids)));

        echo '<textarea class="cg1l-data-voted-user-pids" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($languageNames)));

        echo '<textarea class="cg1l-data-language-names" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($variablesGallery)));

        echo '<textarea class="cg1l-data-variables-gallery" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';
        $b64 = base64_encode(rawurlencode(wp_json_encode($variablesGeneral)));
        echo '<textarea class="cg1l-data-variables-general" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($galleriesOptions)));

        echo '<textarea class="cg1l-data-galleries-options" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($recentMainData)));
        echo '<textarea class="cg1l-data-recent-main" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($recentQueryData)));
        echo '<textarea class="cg1l-data-recent-query" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($recentUrlsData)));
        echo '<textarea class="cg1l-data-recent-urls" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($recentStatsData)));
        echo '<textarea class="cg1l-data-recent-stats" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($recentCommentsData)));
        echo '<textarea class="cg1l-data-recent-comments" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($recentInfoData)));
        echo '<textarea class="cg1l-data-recent-info" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
            '.esc_textarea($b64).'
        </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($WpUserIdsData['mainDataWpUserIds'])));
        echo '<textarea class="cg1l-data-main-data-wp-user-ids" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($WpUserIdsData['nicknamesArray'])));
        echo '<textarea class="cg1l-data-nicknames" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($WpUserIdsData['commentsWpUserIdsArray'])));
        echo '<textarea class="cg1l-data-comments-wp-user-ids" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($WpUserIdsData['profileImagesArray'])));

        echo '<textarea class="cg1l-data-main-data-profile-images" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $wpAvatarImagesArray = (!empty($WpUserIdsData['wpAvatarImagesArray']) && is_array($WpUserIdsData['wpAvatarImagesArray'])) ? $WpUserIdsData['wpAvatarImagesArray'] : [];
        $b64 = base64_encode(rawurlencode(wp_json_encode($wpAvatarImagesArray)));
        echo '<textarea class="cg1l-data-main-data-wp-avatar-images" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $wpAvatarImagesLargeArray = (!empty($WpUserIdsData['wpAvatarImagesLargeArray']) && is_array($WpUserIdsData['wpAvatarImagesLargeArray'])) ? $WpUserIdsData['wpAvatarImagesLargeArray'] : [];
        $b64 = base64_encode(rawurlencode(wp_json_encode($wpAvatarImagesLargeArray)));
        echo '<textarea class="cg1l-data-main-data-wp-avatar-images-large" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        if($options['general']['ShowOnlyUsersVotes']==1 && $options['general']['AllowRating'] >= 1){
            if($options['general']['AllowRating']==2){
                foreach ($dataSlider as $realId => $value){
                    if(!empty($votedUserPids[$realId])){
                        $dataSlider[$realId][1] = cg1l_count_votes_for_an_entry($realId,$votedUserPids,$options);
                    }
                }
            }else{
                foreach ($dataSlider as $realId => $value){
                    if(!in_array($realId,$votedUserPids)!==false){
                        $dataSlider[$realId][1] = cg1l_count_votes_for_an_entry($realId,$votedUserPids,$options);
                    }
                }
            }
        }

        $b64 = base64_encode(rawurlencode(wp_json_encode($dataSlider)));

        echo '<textarea class="cg1l-data-slider" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        $b64 = base64_encode(rawurlencode(wp_json_encode($dataSliderSortedPids)));
        echo '<textarea class="cg1l-data-slider-sorted-pids" data-cg1l-gid="'.$galeryIDuserForJs.'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';

        if($isGalleryShortcode && empty($entryId)){

            // #toDo $imagesFullData pics per site
            if($currentLook=='thumb'){
                $imagesFullDataToProcess = $imagesFullDataCurrentPage;
                $cgGalleryForceEntryGuidLinks = !empty($options['visual']['ForwardToWpPageEntry']) && intval($options['visual']['ForwardToWpPageEntry']) === 1;
                $cgGalleryForceEntryGuidLinksNewTab = $cgGalleryForceEntryGuidLinks && !empty($options['visual']['ForwardToWpPageEntryInNewTab']) && intval($options['visual']['ForwardToWpPageEntryInNewTab']) === 1;

                include ('gallery-view.php');

            }elseif($currentLook=='slider'){
                reset($imagesFullData);
                $entryIdSlider = key($imagesFullData);
                $fullData = $imagesFullData[$entryIdSlider];
                include ('entry-view.php');
                $entryView = cg1l_render_single_entry_view([
                    'gid'=> $galeryIDuserForJs,
                    'gid_js' => $galeryIDuserForJs,
                    'real_id' => $entryIdSlider,
                    'real_gid' => $realGid,
                    'ecommerce_files_data' => $ecommerceFilesData,
                    'ecommerce_options' => $ecommerceOptions,
                    'currencies_array' => $currenciesArray
                ],$fullData,$options,$shortcode_name,$jsonCommentsData,$countUserVotes,$jsonInfoData,$languageNames,$queryDataArray,$singleViewOrderFullData,$formUploadFullData,$categoriesFullData,$WpUserIdsData,$votedUserPids,true,count($imagesFullData));
                echo $entryView;
            }elseif($currentLook=='blog'){
                $order = 0;
                include ('entry-view.php');
                foreach($imagesFullDataCurrentPage as $id => $fullData){
                    $entryView = cg1l_render_single_entry_view([
                        'gid'=> $galeryIDuserForJs,
                        'gid_js' => $galeryIDuserForJs,
                        'order' => $order,
                        'real_id' => $fullData['id'],
                        'real_gid' => $realGid,
                        'ecommerce_files_data' => $ecommerceFilesData,
                        'ecommerce_options' => $ecommerceOptions,
                        'currencies_array' => $currenciesArray
                    ],$fullData,$options,$shortcode_name,$jsonCommentsData,$countUserVotes,$jsonInfoData,$languageNames,$queryDataArray,$singleViewOrderFullData,$formUploadFullData,$categoriesFullData,$WpUserIdsData,$votedUserPids,false,count($imagesFullData));
                    echo $entryView;
                    $order++;
                }
            }
        }elseif($isGalleryShortcode && !empty($entryId)){
            $fullData = $imagesFullData[$entryId];
            include ('entry-view.php');
            $entryView = cg1l_render_single_entry_view([
                'gid'=> $galeryIDuserForJs,
                'gid_js' => $galeryIDuserForJs,
                'real_id' => $entryId,
                'real_gid' => $realGid,
                'ecommerce_files_data' => $ecommerceFilesData,
                'ecommerce_options' => $ecommerceOptions,
                'currencies_array' => $currenciesArray
            ],$fullData,$options,$shortcode_name,$jsonCommentsData,$countUserVotes,$jsonInfoData,$languageNames,$queryDataArray,$singleViewOrderFullData,$formUploadFullData,$categoriesFullData,$WpUserIdsData,$votedUserPids,false,count($imagesFullData));
            echo $entryView;
        }

        echo "<div id='mainCGslider$galeryIDuserForJs' data-cg-gid='$galeryIDuserForJs' class='mainCGslider cg_hide cgCenterDivBackgroundColor' >";
        echo "</div>";

        if(!($isGalleryShortcode && !empty($entryId))){
            include('gallery/inside-gallery-single-image-view.php');
        }

        echo "<div id='cgLdsDualRingCGcenterDivHide$galeryIDuserForJs' class='cg-lds-dual-ring-div-gallery-hide $cgFeControlsStyle cg-lds-dual-ring-div-gallery-hide-cgCenterDiv cg_hide'><div class='cg-lds-dual-ring-gallery-hide $cgFeControlsStyle cg-lds-dual-ring-gallery-hide-cgCenterDiv'></div></div>";
        echo "</div>";
        echo "<div id='cgLdsDualRingCGcenterDivLazyLoader$galeryIDuserForJs' class='cg-lds-dual-ring-div-gallery-hide cg-lds-dual-ring-div-gallery-hide-mainCGallery $cgFeControlsStyle cg_hide'><div class='cg-lds-dual-ring-gallery-hide $cgFeControlsStyle cg-lds-dual-ring-gallery-hide-mainCGallery'></div></div>";

        if(!empty($entryId) && !empty($isCgWpPageEntryLandingPage)){
            include_once(__DIR__.'/entry-landing-neighbors.php');
            $entryLandingNeighborsHtml = cg1l_render_entry_landing_neighbors([
                'entry_id' => $entryId,
                'images_full_data' => $imagesFullData,
                'options' => $options,
                'gid_js' => $galeryIDuserForJs,
                'real_gid' => $realGid,
                'gallery_id_short' => $galeryIDshort,
                'shortcode_name' => $shortcode_name,
                'json_comments_data' => $jsonCommentsData,
                'count_user_votes' => $countUserVotes,
                'voted_user_pids' => $votedUserPids,
                'query_data_array' => $queryDataArray,
                'json_info_data' => $jsonInfoData,
                'form_upload_full_data' => $formUploadFullData,
                'categories_full_data' => $categoriesFullData,
                'language_names' => $languageNames,
                'galleries_options' => $galleriesOptions,
                'ecommerce_files_data' => $ecommerceFilesData,
                'ecommerce_options' => $ecommerceOptions,
                'currencies_array' => $currenciesArray,
                'language_delete_images' => $language_DeleteImages,
                'language_delete_image' => $language_DeleteImage,
                'current_page_number' => $currentPageNumber,
                'back_to_galleries_from_page_number' => $backToGalleriesFromPageNumber,
                'shortcode_color_style' => $cgFeControlsStyle,
                'border_radius_class' => $BorderRadiusClass,
                'cgl_heart' => $cgl_heart,
                'variables_gallery' => $variablesGallery,
                'variables_general' => $variablesGeneral,
                'recent_main_data' => $recentMainData,
                'recent_query_data' => $recentQueryData,
                'recent_urls_data' => $recentUrlsData,
                'recent_stats_data' => $recentStatsData,
                'recent_comments_data' => $recentCommentsData,
                'recent_info_data' => $recentInfoData,
                'wp_user_ids_data' => $WpUserIdsData,
                'data_slider' => $dataSlider,
                'data_slider_sorted_pids' => $dataSliderSortedPids,
                'wp_user_id' => $WpUserId,
            ]);
        }

        echo "</div>";
        echo "<div id='cgCenterDivAppearenceHelper$galeryIDuserForJs' class='cgCenterDivAppearenceHelper'>
    </div>";

        echo "</div>";

        echo "<noscript>";

        echo "<div id='mainCGdivNoScriptContainer$galeryIDuserForJs' class='mainCGdivNoScriptContainer' data-cg-gid='$galeryIDuserForJs'>";

        if(file_exists($upload_folder["basedir"].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-noscript.html')){
        echo file_get_contents($upload_folder["basedir"].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-noscript.html');
        }

        echo "</div>";

        echo "</noscript>";

        echo "</div>";

    }

    if(!empty($entryLandingNeighborsHtml)){
        echo $entryLandingNeighborsHtml;
    }

    if(!empty($entryId) && !empty($options['visual']['TextAfterWpPageEntry']) && !empty($isCgWpPageEntryLandingPage)){
        echo contest_gal1ery_convert_for_html_output_without_nl2br($options['visual']['TextAfterWpPageEntry']);
    }elseif(!empty($isCgParentPage) && !empty($options['visual']['TextAfterWpPageParent'])){
        echo "<div class='mainCGlanding'>";
        echo contest_gal1ery_convert_for_html_output_without_nl2br($options['visual']['TextAfterWpPageParent']);
        echo "</div>";
    }elseif(!empty($isGalleriesMainPage) && !empty($galleriesOptions['TextAfterWpPageGalleries'])){
        echo "<div class='mainCGlanding'>";
        echo contest_gal1ery_convert_for_html_output_without_nl2br($galleriesOptions['TextAfterWpPageGalleries']);
        echo "</div>";
    }

if(!empty($isGalleriesMainPage) && !empty($hasOlderVersionsOnMainCGalleriesPage)){
    if($is_user_logged_in && !empty($logged_in_user) && (
       is_super_admin($logged_in_user->ID) ||
       in_array( 'administrator', (array) $logged_in_user->roles ) ||
       in_array( 'editor', (array) $logged_in_user->roles ) ||
       in_array( 'author', (array) $logged_in_user->roles))
    ){
        $recognizedFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-general/cg-galleries-main-page-information-recognized.txt';
        if(!file_exists($recognizedFile)){
            echo <<<HEREDOC
<p style="text-align: center;background-color: white;max-width: 800px;border-radius: 8px;margin-left: auto; margin-right: auto;" id="cgGalleriesMainPageRecognizedContainer"><b>Information - only visible for administrators</b><br><br>
Galleries copied or created before version 24 are not visible here on auto generated Contest Gallery Galleries page<br><br>
If you place [$shortcode_name_plural] on of your custom pages<br>
you will see all your galleries<br><br>
You can also add ids to display certain galleries<br>
Example: [$shortcode_name_plural ids="1,2,3"]<br><br>
<span id="cgGalleriesMainPageRecognized">Got it</span>
</p>
HEREDOC;
        }
    }
}

}
else{

    if(!$intervalConf['shortcodeIsActive'] && empty($isFromOrderSummary)){
        echo contest_gal1ery_convert_for_html_output_without_nl2br($intervalConf['TextWhenShortcodeIntervalIsOff']);
    }else{
        echo "<div id='cgRegUserGalleryOnly$galeryIDuserForJs' class='cgRegUserGalleryOnly' data-cg-gid='$galeryIDuserForJs'>";
        echo contest_gal1ery_convert_for_html_output_without_nl2br($options['pro']['RegUserGalleryOnlyText']);
        echo "</div>";
    }

    ?>
    <pre>
    <script>
        // will be set in gallery entry page or single entry page
        var mainCGdivEntryPageContainer = document.getElementById('mainCGdivEntryPageContainer');
        if(mainCGdivEntryPageContainer){
            mainCGdivEntryPageContainer.classList.add("cg_visibility_visible");// better with add class CSS
        }
    </script>
        </pre>
    <?php

}

?>
