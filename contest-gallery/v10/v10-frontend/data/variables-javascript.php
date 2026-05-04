<?php
    /*
     * @ai_scan_directive
     * DATA TRANSFER ARCHITECTURE: PHP to JavaScript
     * The variables populated in this file ($variablesGallery) are NOT echoed as inline JavaScript.
     * Instead, they are json_encoded, base64_encoded, and rendered inside hidden <textarea> elements 
     * in `v10-get-data.php` (e.g., `<textarea class="cg1l-data-variables-gallery">...`).
     * The JS frontend (in `wp-content/plugins/contest-gallery-js-and-css/v10/v10-js-for-min/gallery/*.js`)
     * reads these textareas, decodes the base64 string, and parses the JSON.
     * See CODE_STRUCTURE_PHP_JS_RELATION.md for more details.
     */
    $variablesGallery['gidReal'] = $galeryID;
    $variablesGallery['currentLook'] = $currentLook;
    $variablesGallery['versionDatabaseGallery'] = $options['general']['Version'];
    $variablesGallery['versionDatabaseGeneral'] = $p_cgal1ery_db_version;
    $variablesGallery['uploadFolderUrl'] = $upload_folder_url;
    $variablesGallery['cg_check_login'] = $options['general']['CheckLogin'];
    $variablesGallery['cg_user_login_check'] = $UserLoginCheck;
    $variablesGallery['cg_ContestEndTime'] = $options['general']['ContestEndTime'];
    $variablesGallery['cg_ContestEnd'] = $options['general']['ContestEnd'];
    $variablesGallery['formHasUrlField'] = 0;
    $variablesGallery['cg_hide_hide_width'] = 0;
    $variablesGallery['openedGalleryImageOrder'] = null;
    $variablesGallery['categories'] = [];
    $variablesGallery['categoriesUploadFormId'] = null;
    $variablesGallery['categoriesUploadFormTitle'] = null;
    $variablesGallery['showCategories'] = false;
    $variablesGallery['info']  = [];
    $variablesGallery['thumbViewWidth'] = null;
    $variablesGallery['galleryLoaded'] = false;
    $variablesGallery['getJson'] = [];
    $variablesGallery['jsonGetComment'] = [];
    $variablesGallery['jsonGetImageCheck'] = [];
    $variablesGallery['searchInput'] = null;
    $variablesGallery['categoriesLength'] = 0;
    $variablesGallery['galleryAlreadyFullWindow'] = false;
    $variablesGallery['lastRealIdInFullImageDataObject'] = 0;
    $variablesGallery['thumbViewWidthFromLastImageInRow'] = false;
    $variablesGallery['allVotesUsed'] = 0;
    $variablesGallery['sorting'] = 0;
    $variablesGallery['widthmain'] = 0;
    $variablesGallery['translateX'] = $options['pro']['SlideTransition'];
    $variablesGallery['AllowRating'] = $options['general']['AllowRating'];
    $variablesGallery['maximumVisibleImagesInSlider'] = 0;
    $variablesGallery['currentStep'] = $currentPageNumber;
    $variablesGallery['backToGalleriesFromPageNumber'] = $backToGalleriesFromPageNumber;
    $variablesGallery['hasExplicitFromGalleriesPage'] = (!empty($cglHasExplicitFromGalleriesPage)) ? true : false;
    $variablesGallery['currentUrl'] = $currentUrl;
    $variablesGallery['isMultiGalleryContext'] = (!empty($isMultiGalleryContext)) ? true : false;
    $variablesGallery['multiGalleryOriginPageId'] = (!empty($cglOriginPageId)) ? absint($cglOriginPageId) : 0;
    $variablesGallery['multiGalleryOriginUrl'] = (!empty($cglOriginPageUrl)) ? $cglOriginPageUrl : '';
    $variablesGallery['isFromGalleriesSelect'] = (!empty($isFromGalleriesSelect)) ? true : false;
    $variablesGallery['isFromSingleViewForCGalleries'] = (!empty($is_from_single_view_for_cg_galleries)) ? true : false;
    $variablesGallery['sortedRandomFullData'] = null;
    $variablesGallery['rowLogicCount'] = 0;
    $variablesGallery['sortedDateDescFullData'] = null;
    $variablesGallery['sortedDateAscFullData'] = null;
    $variablesGallery['sortedRatingDescFullData'] = null;
    $variablesGallery['sortedRatingAscFullData'] = null;
    $variablesGallery['sortedCommentsDescFullData'] = null;
    $variablesGallery['sortedCommentsAscFullData'] = null;
    $variablesGallery['sortedSearchFullData'] = null;
    $variablesGallery['isProVersion'] = $isProVersion;
    $variablesGallery['ShowFormAfterUploadOrContact'] = $ShowFormAfterUploadOrContact;
    $variablesGallery['imageDataLength'] = $jsonImagesCount;
    $variablesGallery['shortcode_name'] = $shortcode_name;
    $variablesGallery['isUserGallery'] = $isUserGallery;
    $variablesGallery['isOnlyGalleryEcommerce'] = $isOnlyGalleryEcommerce;
    $variablesGallery['isEcommerceTest'] = $isEcommerceTest;
    $variablesGallery['isOnlyGalleryNoVoting'] = $isOnlyGalleryNoVoting;
    $variablesGallery['isOnlyGalleryWinner'] = $isOnlyGalleryWinner;
    $variablesGallery['isOnlyUploadForm'] = $isOnlyUploadForm;
    $variablesGallery['isOnlyContactForm'] = $isOnlyContactForm;
    if(!empty($isOnlyGalleryUser)){
        $variablesGallery['onlyLoggedInUserImages'] = true;
        $variablesGallery['wpUserImageIds'] = $wpUserImageIdsArray;
    }
    $variablesGallery['galleryHash'] = cg_hash_function('---cngl1---'.$galeryIDuserForJs);
    $variablesGallery['runtimeContextToken'] = cg1l_create_runtime_context_token([
        'realGid' => $galeryID,
        'gid' => $galeryIDuserForJs,
        'shortcodeName' => $shortcode_name,
        'entryId' => $entryId,
        'isCGalleries' => $isCGalleries,
        'hasGalleriesIds' => $hasGalleriesIds,
        'isGalleriesMainPage' => $isGalleriesMainPage,
        'galleryDataUseAllowedRealIds' => (!empty($shouldUseAllowedRealIds)) ? 1 : 0,
        'galleriesIds' => $galleriesIds,
    ]);
    $variablesGallery['isCgRuntimeFresh'] = (!empty($isCgRuntimeFresh)) ? true : false;
    $variablesGallery['galleryDataUseAllowedRealIds'] = (!empty($shouldUseAllowedRealIds)) ? 1 : 0;
    $variablesGallery['galleryDataAccessHash'] = cg1l_get_gallery_data_access_hash(
        $galeryID,
        $shortcode_name,
        (!empty($WpUserId)) ? intval($WpUserId) : 0,
        $variablesGallery['galleryDataUseAllowedRealIds']
    );
    $variablesGallery['RatingVisibleForGalleryNoVoting'] = $RatingVisibleForGalleryNoVoting;
    $variablesGallery['isFbLikeOnlyShareOn'] = $isFbLikeOnlyShareOn;
    $variablesGallery['upload'] = [];
    $variablesGallery['upload']['cg_upload_form_e_prevent_default'] = '';
    $variablesGallery['upload']['cg_upload_form_e_prevent_default_file_resolution'] = 0;
    $variablesGallery['upload']['cg_upload_form_e_prevent_default_file_not_loaded'] = 0;
    $variablesGallery['upload']['UploadedUserFilesAmount'] = $UploadedUserFilesAmount;
    $variablesGallery['upload']['UploadedUserFilesAmountPerCategoryArray'] = $UploadedUserFilesAmountPerCategoryArray;
    $variablesGallery['upload']['CookieId'] = $CookieId;
    $variablesGallery['centerWhite'] = $cgCenterWhite;
    $variablesGallery['blogViewImagesLoadedCount'] = 0;
    $variablesGallery['fullImageInfoData'] = [];
    $variablesGallery['language'] = [];
    $variablesGallery['language']['pro'] = [];
    $variablesGallery['language']['pro']['VotesPerUserAllVotesUsedHtmlMessage'] = $language_VotesPerUserAllVotesUsedHtmlMessage;
    //$variablesGallery['queryDataArray'] = $queryDataArray;
    $variablesGallery['hasWpPageParent'] = $hasWpPageParent;
    $variablesGallery['isCgWpPageEntryLandingPage'] = $isCgWpPageEntryLandingPage;
    $variablesGallery['galleryShortCodeEntryId'] = $entryId;
    $variablesGallery['openedRealId'] = $entryId;
    $variablesGallery['entryId'] = $entryId;
    $variablesGallery['allowedRealIds'] = $allowedRealIds;
    $variablesGallery['ecommerceFilesData'] = $ecommerceFilesData;
    $variablesGallery['isCGalleries'] = $isCGalleries;
    $variablesGallery['galleriesIds'] = $galleriesIds;
    $variablesGallery['hasGalleriesIds'] = $hasGalleriesIds;
    $variablesGallery['isGalleriesMainPage'] = $isGalleriesMainPage;
    $variablesGallery['galleriesDataAccessHash'] = cg1l_get_galleries_data_access_hash(
        $shortcode_name,
        (!empty($WpUserId)) ? intval($WpUserId) : 0,
        $isGalleriesMainPage,
        $galleriesIds,
        $hasGalleriesIds
    );
    $variablesGallery['hasUploadSell'] = $hasUploadSell;
    $variablesGallery['isCGalleriesForwardToWpPageEntry'] = $isCGalleriesForwardToWpPageEntry;
    $variablesGallery['timestampBasePath'] = $wp_upload_dir['baseurl'].'/contest-gallery/gallery-id-'.$galeryID.'/json/segments/';
    $variablesGallery['imagesFullDataLength'] = count($imagesFullData);
    $variablesGallery['orderGalleries'] = $orderGalleries;
    $variablesGallery['singleViewOrderFullData'] = $singleViewOrderFullData;
    $variablesGallery['formUploadFullData'] = $formUploadFullData;
    $variablesGallery['categoriesFullData'] = $categoriesFullData;
    $variablesGallery['lengthData'] = count($imagesFullData);
?>
