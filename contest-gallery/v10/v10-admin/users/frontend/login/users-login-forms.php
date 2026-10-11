<?php

if(!isset($cgHideUserAjaxLoginForm)){
    $cgHideUserAjaxLoginForm = '';
}

$mainCGdivUploadFormType = 'mainCGdivUploadFormStatic';
$cgHideUserAjaxLoginFormCloseButton = '';
if(isset($isForAjax)){
    $mainCGdivUploadFormType = 'mainCGdivUploadFormAjax';
}else{
    $cgHideUserAjaxLoginFormCloseButton = 'cg_hide';
}

if(!isset($galeryIDuser)){
    $galeryIDuser = $GalleryID;
}

$isProVersion = false;
$plugin_dir_path = plugin_dir_path(__FILE__);
if(is_dir ($plugin_dir_path.'/../../../../../../contest-gallery-pro') && strpos(cg_get_version_for_scripts(),'-PRO')!==false){
    $isProVersion = true;
}

if(!$isProVersion && isset($optionsSource['interval'])){
    unset($optionsSource['interval']);
}


//$wp_upload_dir = wp_upload_dir();
//$optionsPath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-options.json';
//$optionsSource =json_decode(file_get_contents($optionsPath),true);
$intervalConf = cg_shortcode_interval_check($GalleryID,[],'cg_users_login');
$isLegacyGallerySpecificForm = intval($GalleryID) > 0 && intval($galleryDbVersion)<14;
// GalleryID 0 is the global shortcode mode and must respect the shared interval config on initial render.
if(!$isProVersion || $isLegacyGallerySpecificForm){
	$intervalConf['shortcodeIsActive'] = true;
}
if(!$intervalConf['shortcodeIsActive']){
    echo contest_gal1ery_convert_for_html_output_without_nl2br($intervalConf['TextWhenShortcodeIntervalIsOff']);
}else{
    echo contest_gal1ery_convert_for_html_output_without_nl2br($intervalConf['TextWhenShortcodeIntervalIsOn']);

    if(!is_user_logged_in()){
        if(!empty($TextBeforeLoginForm)){
            echo "<div id='cgTextBeforeLoginFormContainer' >";
            echo $TextBeforeLoginForm;
            echo "</div>";
        }
    }

    echo "<div id='cg_user_login_div' class='mainCGdivUploadForm mainCGdivLoginForm $mainCGdivUploadFormType $cgHideUserAjaxLoginForm $FeControlsStyleLogin $BorderRadiusLogin' data-cg-gid='$galeryIDuser' >";

    echo "<div class='cg_hover_effect cg-close-upload-form $FeControlsStyleLogin $cgHideUserAjaxLoginFormCloseButton' data-cg-gid='$galeryIDuser' data-cg-tooltip='".esc_attr($language_Close)."'>";
    echo "</div>";

//$check = wp_create_nonce("check");
// new check required wp_create_nonce might be different when calling ajax
    $cg_login_check =  cg_hash_function('---cglogin---'.$GalleryID);

    $cgResetPasswordLinkNotValidAnymore = false;
    $cgResetPasswordLinkHideLoginFormClass = '';

    if(isset($_GET['cgResetPassword'])){
        $cgResetPasswordLinkHideLoginFormClass = 'cg_hide';
        $cgResetPasswordWpUserID = is_string($_GET['cgResetPassword']) && ctype_digit($_GET['cgResetPassword']) ? absint($_GET['cgResetPassword']) : 0;
        $cgResetPasswordKey = isset($_GET['cgResetPasswordKey']) && is_string($_GET['cgResetPasswordKey']) ? wp_unslash($_GET['cgResetPasswordKey']) : '';
        $cgResetUser = $cgResetPasswordWpUserID ? cg1l_password_reset_user($cgResetPasswordWpUserID) : null;
        $cgResetPasswordLinkNotValidAnymore = !cg1l_password_reset_valid($cgResetUser,$cgResetPasswordKey,cg1l_password_reset_record($cgResetPasswordWpUserID));
        if(!$cgResetPasswordLinkNotValidAnymore){
            include(__DIR__.'/forms/users-login-form-reset-password.php');
        }
    }

    if($cgResetPasswordLinkNotValidAnymore){
        include(__DIR__.'/forms/users-login-form-reset-password-link-not-valid-anymore.php');
    }

    include(__DIR__.'/forms/users-login-form-login.php');

    if($LostPasswordMailActive==1){
        include(__DIR__.'/forms/users-login-form-lost-password.php');
    }

// Wichtig! Ajax Abarbeitung hier!
    echo "<div id='cg_login_message'>";

    echo "</div>";

  //echo "$language_MaximumAllowedWidthForJPGsIs";
    echo "<input type='hidden' id='cg_show_upload' value='1'>";

//echo "language_ThisFileTypeIsNotAllowed: $language_ThisFileTypeIsNotAllowed";
    echo "<input type='hidden' id='cg_file_not_allowed_1' value='".esc_attr($language_ThisFileTypeIsNotAllowed)."'>";
    echo "<input type='hidden' id='cg_file_size_to_big' value='".esc_attr($language_TheFileYouChoosedIsToBigMaxAllowedSize)."'>";
//echo "<input type='hidden' id='cg_post_size' value='$post_max_sizeMB'>";

    echo "<input type='hidden' id='cg_to_high_resolution' value='".esc_attr($language_TheResolutionOfThisPicIs)."'>";

    echo "<input type='hidden' id='cg_max_allowed_resolution_jpg' value='".esc_attr($language_MaximumAllowedResolutionForJPGsIs)."'>";
    echo "<input type='hidden' id='cg_max_allowed_width_jpg' value='".esc_attr($language_MaximumAllowedWidthForJPGsIs)."'>";
    echo "<input type='hidden' id='cg_max_allowed_height_jpg' value='".esc_attr($language_MaximumAllowedHeightForJPGsIs)."'>";

    echo "<input type='hidden' id='cg_max_allowed_resolution_png' value='".esc_attr($language_MaximumAllowedResolutionForPNGsIs)."'>";
    echo "<input type='hidden' id='cg_max_allowed_width_png' value='".esc_attr($language_MaximumAllowedWidthForPNGsIs)."'>";
    echo "<input type='hidden' id='cg_max_allowed_height_png' value='".esc_attr($language_MaximumAllowedHeightForPNGsIs)."'>";

    echo "<input type='hidden' id='cg_max_allowed_resolution_gif' value='".esc_attr($language_MaximumAllowedResolutionForGIFsIs)."'>";
    echo "<input type='hidden' id='cg_max_allowed_width_gif' value='".esc_attr($language_MaximumAllowedWidthForGIFsIs)."'>";
    echo "<input type='hidden' id='cg_max_allowed_height_gif' value='".esc_attr($language_MaximumAllowedHeightForGIFsIs)."'>";

    echo "<input type='hidden' id='cg_check_agreement' value='".esc_attr($language_YouHaveToCheckThisAgreement." ")."'>";
    echo "<input type='hidden' id='cg_check_email_upload_for_login' value='".esc_attr(" ".$language_EmailAddressHasToBeValid)."'>";
    echo "<input type='hidden' id='cg_min_characters_text' value='".esc_attr($language_MinAmountOfCharacters)."'>";
    echo "<input type='hidden' id='cg_max_characters_text' value='".esc_attr($language_MaxAmountOfCharacters)."'>";
    echo "<input type='hidden' id='cg_no_picture_is_choosed' value='".esc_attr($language_ChooseYourImage)."'>";


    echo "<input type='hidden' id='cg_language_BulkUploadQuantityIs' value='".esc_attr($language_BulkUploadQuantityIs)."'>";
    echo "<input type='hidden' id='cg_language_BulkUploadLowQuantityIs' value='".esc_attr($language_BulkUploadLowQuantityIs)."'>";

    echo "<input type='hidden' id='cg_language_BulkUploadLowQuantityIs' value='".esc_attr($language_BulkUploadLowQuantityIs)."'>";

    echo "<input type='hidden' id='cg_language_ThisMailAlreadyExists' value='".esc_attr($language_ThisMailAlreadyExists)."'>";
    echo "<input type='hidden' id='cg_language_ThisNicknameAlreadyExists' value='".esc_attr($language_ThisNicknameAlreadyExists)."'>";
    echo "<input type='hidden' id='cg_language_ThisUsernameAlreadyExists' value='".esc_attr($language_ThisUsernameAlreadyExists)."'>";

    echo "<input type='hidden' id='cg_language_PasswordRequired' value='".esc_attr($language_PasswordRequired)."'>";
    echo "<input type='hidden' id='cg_language_UsernameOrEmailRequired' value='".esc_attr($language_UsernameOrEmailRequired)."'>";
    echo "<input type='hidden' id='cg_language_EmailRequired' value='".esc_attr($language_EmailRequired)."'>";
    echo "<input type='hidden' id='cg_language_EmailAddressHasToBeValid' value='".esc_attr($language_EmailAddressHasToBeValid)."'>";

    echo "<input type='hidden' id='cg_language_EmailAndPasswordDoNotMatch' value='".esc_attr($language_EmailAndPasswordDoNotMatch)."'>";
    echo "<input type='hidden' id='cg_language_LoginAndPasswordDoNotMatch' value='".esc_attr($language_LoginAndPasswordDoNotMatch)."'>";
    echo "<input type='hidden' id='cg_language_PleaseFillOut' value='".esc_attr($language_PleaseFillOut)."'>";
    echo "<input type='hidden' id='cg_language_Required' value='".esc_attr($language_Required)."'>";
    echo "<input type='hidden' id='cg_language_PasswordsDoNotMatch' value='".esc_attr($language_PasswordsDoNotMatch)."'>";
    echo "<input type='hidden' id='cg_language_LostPasswordUrlIsNotValidAnymore' value='".esc_attr($language_LostPasswordUrlIsNotValidAnymore)."'>";
    echo "<input type='hidden' id='cg_language_ResetPasswordSuccessfully' value='".esc_attr($language_ResetPasswordSuccessfully)."'>";
    echo "<input type='hidden' id='cg_language_MinAmountOfCharacters' value='".esc_attr($language_MinAmountOfCharacters)."'>";

    echo "</div>";

}



?>
