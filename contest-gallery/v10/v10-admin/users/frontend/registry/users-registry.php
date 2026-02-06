<noscript>
    <div style="border: 1px solid purple; padding: 10px">
        <span style="color:red">Enable JavaScript to use the form</span>
    </div>
</noscript>
<?php
if (!defined('ABSPATH')) {
    exit;
}
/*
if(empty($atts['id'])){
    "<p>Please provide a gallery id to the Contest Gallery shortcode</p>";
    return;
}*/
$GalleryID = 0;// since 28.0.1 always 0

$is_frontend = true;
include(__DIR__ . "/../../../../../check-language.php");

global $wpdb;
$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
$tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
$tablename_registry_and_login_options = $wpdb->prefix . "contest_gal1ery_registry_and_login_options";

$cg_current_page_id = get_the_ID();
$currentPageUrl = get_permalink($cg_current_page_id);

$registry_and_login_options = $wpdb->get_row("SELECT * FROM $tablename_registry_and_login_options WHERE GeneralID='1'");//get row
if(empty($registry_and_login_options)){
    cg_create_registry_and_login_options();
    $registry_and_login_options = $wpdb->get_row("SELECT * FROM $tablename_registry_and_login_options WHERE GeneralID='1'");//get row
}

$RegistryUserRole = $registry_and_login_options->RegistryUserRole;
$ConfirmExpiry = $registry_and_login_options->ConfirmExpiry;
$PinExpiry = $registry_and_login_options->PinExpiry;
$LoginAfterConfirm = $registry_and_login_options->LoginAfterConfirm;
$galleryDbVersion = cg_get_db_version();

$optionsVisual = $wpdb->get_row( "SELECT * FROM $tablename_options_visual WHERE GeneralID='1'");

$isBorderRadius = 0;
$isWhite = 0;

$FeControlsStyleRegistry = ($optionsVisual->FeControlsStyleRegistry=='white' || empty($optionsVisual->FeControlsStyleRegistry)) ?  'cg_fe_controls_style_white' : 'cg_fe_controls_style_black';
if($FeControlsStyleRegistry=='cg_fe_controls_style_white'){
    $isWhite = 1;
}
$BorderRadiusRegistry = ($optionsVisual->BorderRadiusRegistry=='1' || empty($optionsVisual->FeControlsStyleRegistry)) ? 'cg_border_radius_controls_and_containers' : '';
if($BorderRadiusRegistry){
    $isBorderRadius = 1;
}
$cgFeControlsStyle = $FeControlsStyleRegistry;
$BorderRadiusClass = $BorderRadiusRegistry;

if(!isset($InGalleryRegistryHide)){
    $InGalleryRegistryHide = '';
}

$pro_options = $wpdb->get_row("SELECT * FROM $tablename_pro_options WHERE GeneralID='1'");

$TextAfterEmailConfirmation = html_entity_decode(stripslashes(nl2br($pro_options->TextAfterEmailConfirmation)));
$TextAfterPinConfirmation = html_entity_decode(stripslashes(nl2br($pro_options->TextAfterPinConfirmation)));
$HideRegFormAfterLogin = $pro_options->HideRegFormAfterLogin;
include(__DIR__.'/../../../../../v10/v10-frontend/gallery/cg-messages.php');
if (!empty($_GET["cg1l_reload_after_login"])) {
    $isAfterLogin = true;
    include (__DIR__.'/users-registry-render-confirmation-or-signin.php');
    return;
}

// has definetly to be not empty! Not isset only!
if (!empty($_GET["cgkey"]) || !empty($cg_users_pin_from_email_check)) {// joins here when email is trying to get confirmed, when forwarding from email
   include('users-registry-check-after-email-or-pin-confirmation.php');
}elseif (!empty($_GET['cg_login_user_after_registration']) OR !empty($_GET['cg_forward_user_after_reg'])) {// in both cases simply ForwardAfterRegText will be shown with login or without

        $GalleryID = sanitize_text_field($_GET['cg_gallery_id_registry']);
        echo "<div id='cg_activation' class='mainCGdivUploadForm mainCGdivUploadFormStatic $FeControlsStyleRegistry $BorderRadiusRegistry'>";
        $ForwardAfterRegText = nl2br(html_entity_decode(stripslashes($pro_options->ForwardAfterRegText)));
        echo $ForwardAfterRegText;
        echo "</div>";
        ?>
        <script>
            setTimeout(function (){
                jQuery("html, body").animate({ scrollTop: jQuery('#cg_activation').offset().top-60}, 0);
            },100);
        </script>
        <?php

} else {// show registration form then!!!!

    ob_start();
    include(__DIR__ . "/../../../../../check-language.php");
    include(__DIR__ . "/../../../../../check-language-general.php");

    global $wpdb;
    $tablenameCreateUserForm = $wpdb->prefix . "contest_gal1ery_create_user_form";

    $selectUserForm = $wpdb->get_results("SELECT * FROM $tablenameCreateUserForm WHERE GeneralID = '1' && Active = '1' ORDER BY Field_Order ASC");

    if (empty($selectUserForm)) {
        echo "Please check your shortcode. The id does not exists.<br>";
        return false;
    }

    $i = 1;

    $HideRegForm = false;

    if (($HideRegFormAfterLogin == '1' && is_user_logged_in())) {
        $HideRegForm = true;
    }

    if(!empty($isShowPinFormVotingUploading)){
        echo "<div id='cg_user_registry_div_pin_parent' class='cg_user_registry_div_pin_parent cg_hide'>";
    }

    if (!$HideRegForm) {
       include ('users-registry-form.php');
    }

    if(!empty($isShowPinFormVotingUploading)){
        echo "</div>";
    }

    if ($pro_options->HideRegFormAfterLoginShowTextInstead == 1 && $HideRegFormAfterLogin == 1 && is_user_logged_in()) {
        $HideRegFormAfterLoginTextToShow = contest_gal1ery_convert_for_html_output_without_nl2br($pro_options->HideRegFormAfterLoginTextToShow);
        echo "<div id='cg_user_registry_div_hide_after_login'>";
        echo $HideRegFormAfterLoginTextToShow;
        echo "</div>";
    }



    $formOutput = ob_get_clean();

    echo $formOutput;

}
/*if (!$HideRegForm) {
    echo "</div>";
}*/

?>