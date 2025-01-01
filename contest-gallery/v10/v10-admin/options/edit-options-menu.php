<?php
$cg_v14_note_caret = '';
$cg_v14_general = '';
$cg_v14_email_confirmation_disabled_style = '';
$cg_v14_general_options_title_string = '';
$cg_v22_caret = '';

if($galleryDbVersion>=14){
    $cg_v14_note_caret = 'cg_v14_note_caret';
    $cg_v14_general = 'cg_v14_general';
    $cg_v14_email_confirmation_disabled_style = 'opacity: 0.5;';
    $cg_v14_general_options_title_string = ' (general)';
}

$cg_since_v22 = '';
if(intval($galleryDbVersion)>=22) {
    $cg_since_v22 = 'cg_since_v22';
    $cg_v22_caret = 'cg_v22_caret';
}

$isEditOptionsOnly = false;
$isEditTranslationsOnly = false;
$isEditEcommerceOnly = false;
if(empty($_POST['cg_edit_translations']) && empty($_POST['cg_edit_ecommerce']) && empty($_GET['cg_edit_translations']) && empty($_GET['cg_edit_ecommerce'])){
    $isEditOptionsOnly = true;
}else if(!empty($_POST['cg_edit_translations']) || !empty($_GET['cg_edit_translations'])){
    $isEditTranslationsOnly = true;
}else if(!empty($_POST['cg_edit_ecommerce']) || !empty($_GET['cg_edit_ecommerce'])){
    $isEditEcommerceOnly = true;
}
if($isEditOptionsOnly){
    if(intval($galleryDbVersion)>=14){
        $styleTabContents="style='border-radius:none !important;position:relative;'";
        echo <<<HEREDOC
    <div id="cg_main_options" class="cg_main_options cg_hidden">
        <div id="cg_main_options_tab">
             <div id="cg_tabs_container" >
            <div class="tabs" data-persist="true">
                  <div id="cg_main_options_tab_first_row" class="cg_main_options_tab_row" style="margin-bottom: 7px;border-bottom: thin solid #dedede;padding-bottom: 10px;">
                        <div class='cg_view_select cg_selected' cg-data-view="#view1" data-count="1" ><a class="cg_view_select_link" cg-data-view="#view1" cg-data-href="cgViewHelper1">Gallery view</a></div>
                        <div class='cg_view_select' cg-data-view="#view2" data-count="2"><a class="cg_view_select_link" cg-data-view="#view2" cg-data-href="cgViewHelper2">Entry view</a></div>
                        <div class='cg_view_select' cg-data-view="#view3" data-count="3"><a class="cg_view_select_link" cg-data-view="#view3" cg-data-href="cgViewHelper3">Gallery</a></div>
                        <div class='cg_view_select' cg-data-view="#view4" data-count="4"><a class="cg_view_select_link" cg-data-view="#view4" cg-data-href="cgViewHelper4">Voting</a></div>
                        <div class='cg_view_select' cg-data-view="#view5" data-count="5"><a class="cg_view_select_link" cg-data-view="#view5" cg-data-href="cgViewHelper5">Contact</a></div>
                         <div class='cg_view_select' cg-data-view="#view6" data-count="6"><a class="cg_view_select_link" cg-data-view="#view6" cg-data-href="cgViewHelper6">Admin mail</a></div>
                        <div class='cg_view_select' cg-data-view="#view7" data-count="7"><a class="cg_view_select_link" cg-data-view="#view7" cg-data-href="cgViewHelper7">Activation mail</a></div>
                          <div class='cg_view_select cg_view_select_icons' cg-data-view="#view8" data-count="8"><a class="cg_view_select_link" cg-data-view="#view8" cg-data-href="cgViewHelper8">Icons</a></div>
                </div>
HEREDOC;

    echo <<<HEREDOC
                <div id="cg_main_options_tab_second_row">
                    <div id="cg_main_options_tab_second_row_inner" class="cg_main_options_tab_row">
                      <div class='cg_view_select cg_view_select_icons' cg-data-view="#view20" data-count="20"><a class="cg_view_select_link" cg-data-view="#view20" cg-data-href="cgViewHelper20">Social embed</a></div>
                        <div class='cg_view_select cg_view_select_general cg_after_v14 $cg_v14_note_caret $cg_v22_caret' cg-data-view="#view10" data-count="10"><a class="cg_view_select_link" cg-data-view="#view10" cg-data-href="cgViewHelper10">Galleries</a></div>
                      <div class='cg_view_select cg_view_select_status_repair cg_after_v14 $cg_v14_note_caret $cg_v22_caret' cg-data-view="#statusRepairMail" data-count="16"><a class="cg_view_select_link" cg-data-view="#statusRepairMail" cg-data-href="statusRepairMail">Status, repair, mail</a></div>
                      <div class='cg_view_select cg_after_v14 $cg_v14_note_caret $cg_v22_caret' cg-data-view="#view11" data-count="11"><a class="cg_view_select_link" cg-data-view="#view11" cg-data-href="cgViewHelper11">Registration</a></div>
                      <div class='cg_view_select cg_after_v14 $cg_v14_note_caret $cg_v22_caret' cg-data-view="#view12" data-count="12"><a class="cg_view_select_link" cg-data-view="#view12" cg-data-href="cgViewHelper12">Login</a></div>
HEREDOC;
echo <<<HEREDOC
                       <div class='cg_view_select cg_after_v14 $cg_v14_note_caret $cg_v22_caret' cg-data-view="#view15" data-count="15"><a class="cg_view_select_link" cg-data-view="#view15" cg-data-href="cgViewHelper15" id="cgSignInOptionsTabLink">Login Google</a></div>

                      <div cg-data-view="#view17" data-count="17" id="cgSaveOptionsNavButton">
                        <span cg-data-view="#view17" cg-data-href="cgViewHelper17" class="cg_backend_button_gallery_action" ><strong>Save options</strong></span>
                      </div>
                    </div>
                </div>
HEREDOC;
    }else {
        $styleTabContents = "style='border-radius:none !important;position:relative;'";
echo <<<HEREDOC
    <div id="cg_main_options" class="cg_main_options cg_hidden">
        <div id="cg_main_options_tab">
             <div id="cg_tabs_container" >
            <div class="tabs" data-persist="true">
                  <div id="cg_main_options_tab_first_row" class="cg_main_options_tab_row" style="margin-bottom: 7px;border-bottom: thin solid black;padding-bottom: 10px;border-width: 0.5px;">
                        <div class='cg_view_select cg_selected' cg-data-view="#view1" data-count="1" ><a class="cg_view_select_link" cg-data-view="#view1" cg-data-href="cgViewHelper1">Gallery view</a></div>
                        <div class='cg_view_select' cg-data-view="#view2" data-count="2"><a class="cg_view_select_link" cg-data-view="#view2" cg-data-href="cgViewHelper2">Entry view</a></div>
                        <div class='cg_view_select' cg-data-view="#view3" data-count="3"><a class="cg_view_select_link" cg-data-view="#view3" cg-data-href="cgViewHelper3">Gallery</a></div>
                        <div class='cg_view_select' cg-data-view="#view4" data-count="4"><a class="cg_view_select_link" cg-data-view="#view4" cg-data-href="cgViewHelper4">Voting</a></div>
                        <div class='cg_view_select' cg-data-view="#view5" data-count="5"><a class="cg_view_select_link" cg-data-view="#view5" cg-data-href="cgViewHelper5">Contact</a></div>
                         <div class='cg_view_select' cg-data-view="#view6" data-count="6"><a class="cg_view_select_link" cg-data-view="#view6" cg-data-href="cgViewHelper6">Admin mail</a></div>
                        <div class='cg_view_select' cg-data-view="#view7" data-count="7"><a class="cg_view_select_link" cg-data-view="#view7" cg-data-href="cgViewHelper7">Activation mail</a></div>
                          <div class='cg_view_select' cg-data-view="#view8" data-count="8"><a class="cg_view_select_link" cg-data-view="#view8" cg-data-href="cgViewHelper8">Icons</a></div>
                </div>
HEREDOC;
        echo <<<HEREDOC
                <div id="cg_main_options_tab_second_row">
                    <div id="cg_main_options_tab_second_row_inner" class="cg_main_options_tab_row">
                      <div class='cg_view_select' cg-data-view="#view20" data-count="20"><a class="cg_view_select_link" cg-data-view="#view20" cg-data-href="cgViewHelper20">Social embed</a></div>
                       <div class='cg_view_select cg_view_select_status_repair cg_v14_note_caret' cg-data-view="#statusRepairMail" data-count="14"><a class="cg_view_select_link" cg-data-view="#statusRepairMail" cg-data-href="statusRepairMail" id="statusRepairMail">Status, repair, mail</a></div>
                      <div class='cg_view_select $cg_v14_note_caret' cg-data-view="#view10" data-count="10"><a class="cg_view_select_link" cg-data-view="#view10" cg-data-href="cgViewHelper10" style="$cg_v14_email_confirmation_disabled_style">E-mail confirmation e-mail</a></div>
                      <div class='cg_view_select $cg_v14_note_caret' cg-data-view="#view11" data-count="11"><a class="cg_view_select_link" cg-data-view="#view11" cg-data-href="cgViewHelper11">Registration</a></div>
                      <div class='cg_view_select $cg_v14_note_caret' cg-data-view="#view12" data-count="12"><a class="cg_view_select_link" cg-data-view="#view12" cg-data-href="cgViewHelper12">Login</a></div>
                       <div class='cg_view_select cg_v14_note_caret' cg-data-view="#view13" data-count="13"><a class="cg_view_select_link" cg-data-view="#view13" cg-data-href="cgViewHelper13" id="cgSignInOptionsTabLink">Login Google</a></div>
                      <div cg-data-view="#view15" data-count="75" id="cgSaveOptionsNavButton">
                        <span cg-data-view="#view15" cg-data-href="cgViewHelper15" class="cg_backend_button_gallery_action" >Save options</span>
                      </div>
                    </div>
                </div>
HEREDOC;
    }
}else if($isEditTranslationsOnly){

    $styleTabContents = "style='border-radius:none !important;position:relative;'";
    echo <<<HEREDOC
    <div id="cg_main_options" class="cg_main_options cg_hidden">
        <div id="cg_main_options_tab">
             <div id="cg_tabs_container" >
            <div class="tabs" data-persist="true">
HEREDOC;
    echo <<<HEREDOC
                <div id="cg_main_options_tab_second_row" class="cg_ecommerce">
                    <div id="cg_main_options_tab_second_row_inner" class="cg_main_options_tab_row">
                        <div class='cg_view_select cg_view_select_translations' cg-data-view="#translations" data-count="9"><a class="cg_view_select_link" cg-data-view="#translations" cg-data-href="cgViewHelper9">Translations</a></div>
                      <div class='cg_view_select cg_view_select_translations cg_v14_note_caret' cg-data-view="#translationsEcommerce" data-count="15"><a class="cg_view_select_link" cg-data-view="#translationsEcommerce" cg-data-href="translationsEcommerce">Translations ecommerce</a></div>
                      <div cg-data-view="#view16" data-count="75" id="cgSaveOptionsNavButton">
                        <span cg-data-view="#view16" cg-data-href="cgViewHelper16" class="cg_backend_button_gallery_action" >Save options</span>
                      </div>
                    </div>
                </div>
HEREDOC;

}else if($isEditEcommerceOnly){
    $styleTabContents = "style='border-radius:none !important;position:relative;'";
    echo <<<HEREDOC
    <div id="cg_main_options" class="cg_main_options cg_hidden">
        <div id="cg_main_options_tab">
             <div id="cg_tabs_container" >
            <div class="tabs" data-persist="true">
HEREDOC;

    echo <<<HEREDOC
                <div id="cg_main_options_tab_second_row">
                    <div id="cg_main_options_tab_second_row_inner" class="cg_main_options_tab_row">
HEREDOC;

    if(intval($galleryDbVersion)>=22) {
        echo <<<HEREDOC
                 <div class='cg_view_select cg_view_select_general cg_view_select_ecommerce cg_after_v14 $cg_v14_note_caret $cg_v22_caret' cg-data-view="#view13" data-count="13"><a class="cg_view_select_link" cg-data-view="#view13" cg-data-href="cgViewHelper13">Galleries</a></div>
                 <div class='cg_view_select cg_view_select_ecommerce cg_after_v14 $cg_v14_note_caret $cg_v22_caret' cg-data-view="#view14" data-count="14"><a class="cg_view_select_link" cg-data-view="#view14" cg-data-href="cgViewHelper14">Invoice</a></div>
                  <div cg-data-view="#view15" data-count="75" id="cgSaveOptionsNavButton">
                    <span cg-data-view="#view15" cg-data-href="cgViewHelper15" class="cg_backend_button_gallery_action" ><strong>Save options</strong></span>
                  </div>
HEREDOC;
    }

    echo <<<HEREDOC

                    </div>
                </div>
HEREDOC;

}


$cg_short_code_multiple_pics_configuration_cg_gallery_winner = '';
$cg_short_code_multiple_pics_configuration_cg_gallery_ecommerce = '';

echo <<<HEREDOC
            </div>
        </div>
        </div>
        <div id="cg_main_options_content" class="tabcontents" $styleTabContents>
HEREDOC;
if($isEditOptionsOnly){

	$cg_short_code_multiple_pics_configuration_cg_gallery_ecommerce = '';

	if(intval($galleryDbVersion)>=22){
		$cg_short_code_multiple_pics_configuration_cg_gallery_ecommerce = '<div class="cg_short_code_multiple_pics_configuration cg_short_code_multiple_pics_configuration_cg_gallery_ecommerce '.$cg_since_v22.'">cg_gallery_ecommerce</div>';
	}

    echo <<<HEREDOC
            <h4 id="view1" class="cg_view_header">Gallery view options</h4>
<div class="cg_short_code_multiple_pics_configuration_buttons">
    <div class="cg_short_code_multiple_pics_configuration_buttons_container">
        <div class="cg_short_code_multiple_pics_configuration cg_short_code_multiple_pics_configuration_cg_gallery cg_active $cg_since_v22"> cg_gallery</div>
        <div class="cg_short_code_multiple_pics_configuration cg_short_code_multiple_pics_configuration_cg_gallery_user $cg_since_v22" >cg_gallery_user</div>
        <div class="cg_short_code_multiple_pics_configuration cg_short_code_multiple_pics_configuration_cg_gallery_no_voting $cg_since_v22">cg_gallery_no_voting</div>
        <div class="cg_short_code_multiple_pics_configuration cg_short_code_multiple_pics_configuration_cg_gallery_winner $cg_since_v22">cg_gallery_winner</div>
        $cg_short_code_multiple_pics_configuration_cg_gallery_ecommerce
    </div>
</div>

<div class="cg_short_code_multiple_pics_configuration_note" >
<div class="cg_arrow_up"></div>
<b>NOTE:</b> "Gallery view options" can be configured for every gallery shortcode</div>
HEREDOC;

    include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-multiple-pics/shortcode-multiple-pics-cg-gallery.php');
    include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-multiple-pics/shortcode-multiple-pics-cg-gallery-user.php');
    include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-multiple-pics/shortcode-multiple-pics-cg-gallery-no-voting.php');
    include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-multiple-pics/shortcode-multiple-pics-cg-gallery-winner.php');

    $cg_short_code_single_pic_configuration_cg_gallery_ecommerce = '';
    if(intval($galleryDbVersion)>=22){
        include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-multiple-pics/shortcode-multiple-pics-cg-gallery-ecommerce.php');
        $cg_short_code_single_pic_configuration_cg_gallery_ecommerce = '<div class="cg_short_code_single_pic_configuration cg_short_code_single_pic_configuration_cg_gallery_ecommerce '.$cg_since_v22.'">cg_gallery_ecommerce</div>';
    }

    echo <<<HEREDOC
                       <h4 id="view2" class="cg_view_header">Entry view options</h4>                       
<div class="cg_short_code_single_pic_configuration_buttons">
    <div class="cg_short_code_single_pic_configuration_buttons_container">
        <div class="cg_short_code_single_pic_configuration cg_short_code_single_pic_configuration_cg_gallery cg_active $cg_since_v22">cg_gallery</div>
        <div class="cg_short_code_single_pic_configuration cg_short_code_single_pic_configuration_cg_gallery_user $cg_since_v22" >cg_gallery_user</div>
        <div class="cg_short_code_single_pic_configuration cg_short_code_single_pic_configuration_cg_gallery_no_voting $cg_since_v22">cg_gallery_no_voting</div>
        <div class="cg_short_code_single_pic_configuration cg_short_code_single_pic_configuration_cg_gallery_winner $cg_since_v22">cg_gallery_winner</div>
        $cg_short_code_single_pic_configuration_cg_gallery_ecommerce
    </div>
</div>
                    
<div class="cg_short_code_single_pic_configuration_note">
<div class="cg_arrow_up"></div>
<b>NOTE:</b> "Entry view options" can be configured for every gallery shortcode</div>               

HEREDOC;

// old code
    echo '<input type="hidden" name="ScaleSizesGalery"  '.$ScaleAndCut.'  class="ScaleSizesGalery">';
    echo '<input type="hidden" name="ScaleWidthGalery"  '.$ScaleOnly.'  class="ScaleWidthGalery">';

    include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-single-pic/shortcode-single-pic-cg-gallery.php');
    include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-single-pic/shortcode-single-pic-cg-gallery-user.php');
    include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-single-pic/shortcode-single-pic-cg-gallery-no-voting.php');
    include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-single-pic/shortcode-single-pic-cg-gallery-winner.php');
    if(intval($galleryDbVersion)>=22){
        include(__DIR__.'/shortcodes-configuration/shortcodes-configuration-single-pic/shortcode-single-pic-cg-gallery-ecommerce.php');
    }

    echo <<<HEREDOC
            <h4 id="view3" class="cg_view_header">Gallery options</h4>
            <div class="cg_view cgGalleryOptions cgViewHelper3">
HEREDOC;

    $dateCurrent = date('Y-m-d H:i');
    $dateCurrentWpConf = cg_get_time_based_on_wp_timezone_conf(time(),'Y-m-d H:i');

    include(__DIR__ . '/views-content/gallery-options/view-gallery-options.php');


    echo <<<HEREDOC
</div>
HEREDOC;


    echo <<<HEREDOC
 </div>
             <h4 id="view4" class="cg_view_header">Voting options</h4>

<div class="cg_view cgVotingOptions cgViewHelper4" id="cgVotingOptions">
HEREDOC;

$userIP = cg1l_sanitize_method(cg_get_user_ip());

    $userIPunknown = '';

    if($userIP=='unknown'){
        $userIPunknown = "<br><span style='color:red;'>Users IP can not be tracked because of your server system.<br>Your server provider track the IP in very unusual way.<br>
This recognition method would not work for you.<br>Please contact support@contest-gallery.com<br> and tell the name of your server provider<br>so it can be researched.</span>";
    }

    $FbLikeGoToGalleryLinkPlaceholder = site_url().'/';

    include(__DIR__.'/views-content/view-voting-options.php');

    echo <<<HEREDOC
 </div>
             <h4 id="view5" class="cg_view_header">Contact options</h4>

			   <div class="cg_view cgUploadOptions cgViewHelper5">
HEREDOC;


// Maximal möglich eingestellter Upload wird ermittelt
    $upload_max_filesize = contest_gal1ery_return_mega_byte(ini_get('upload_max_filesize'));
    $post_max_size = contest_gal1ery_return_mega_byte(ini_get('post_max_size'));

    include(__DIR__.'/views-content/view-upload-options.php');

    echo "</div>";

    /*echo <<<HEREDOC
                <h4 id="view6" class="cg_view_header">Contact options</h4>
        <div class="cg_view  cgViewHelper6">
    HEREDOC;

    include(__DIR__.'/views-content/view-contact-options.php');

    echo "</div>";*/

    echo <<<HEREDOC
            <h4 id="view6" class="cg_view_header">Admin mail</h4>
	<div class="cg_view  cgViewHelper6">
HEREDOC;

    include(__DIR__.'/views-content/view-admin-email-options.php');

    echo "</div>";

    echo <<<HEREDOC
            <h4 id="view7" class="cg_view_header">Activation mail</h4>
	<div class="cg_view  cgViewHelper7">
HEREDOC;

    include(__DIR__.'/views-content/view-activation-email-options.php');

    echo "</div>";

    echo <<<HEREDOC
            <h4 id="view8" class="cg_view_header">Icons</h4>
            <div class="cg_view cgViewHelper8" >
HEREDOC;
    include(__DIR__.'/views-content/view-icons-options.php');
    echo "</div>";

    echo <<<HEREDOC
            <h4 id="view20" class="cg_view_header">Social embed</h4>
            <div class="cg_view cgViewHelper20" >
HEREDOC;
	include(__DIR__.'/views-content/view-youtube-options.php');

    echo "</div>";

}

if($isEditTranslationsOnly){
    echo <<<HEREDOC
            <h4 id="translations" class="cg_view_header cg_view_header_translations">Translations</h4>
	<div class="cg_view cg_view_translations  cgViewHelper9">
HEREDOC;
    include(__DIR__.'/views-content/view-translations-options.php');
    echo "</div>";
}

$cgV14disabled = '';

if(intval($galleryDbVersion)>=14){
    $cgV14disabled = 'cg_disabled';
}

if(intval($galleryDbVersion)<14){

    if($isEditOptionsOnly){
        echo <<<HEREDOC
                <h4 id="view10" class="cg_view_header">E-mail confirmation e-mail</h4>
        <div class='cg_view cgEmailConfirmationEmail cgViewHelper10'>
    HEREDOC;
        include(__DIR__.'/views-content/view-email-confirmation-email-options.php');
        echo "</div>";

        // status, repair, mail
        echo <<<HEREDOC
    <h4 id="statusRepairMail" class="cg_view_header">Status, repair, mail</h4>
<div class="cg_view  statusRepairMail"  style="padding-bottom:5px;">
HEREDOC;
        echo <<<HEREDOC
<div class='cg_view_container '>
HEREDOC;
        echo <<<HEREDOC
<div class='cg_view_options_rows_container' style="margin-bottom:15px;">
     <p class='cg_view_options_rows_container_title' style="line-height:25px;">Check database status, repair cached json files, check if sent mails were all ok</p>
</div>
HEREDOC;
        echo "<div style='width:100%;display: flex;flex-flow: column;margin-bottom:15px;margin-top:15px;'>";
        echo "<div style='width:100%;'>";
        echo "<div style=\"width:245px;margin: 0 auto;\"><a href=\"?page=".cg_get_version()."/index.php&amp;corrections_and_improvements=true&amp;option_id=$galeryNR\" class='cg_load_backend_link'><input class=\"cg_backend_button cg_backend_button_back\" type=\"button\" value=\"Status, repair and mail exceptions\" style=\"width:245px;padding-right: 0;\"></a>
</div></div></div>";
        echo <<<HEREDOC
</div>
HEREDOC;
        echo "</div>";

        // status, repair, mail --- END
        echo <<<HEREDOC
                <h4 id="view11" class="cg_view_header">Registration$cg_v14_general_options_title_string</h4>
        <div class="cg_view cgRegistrationOptions cgViewHelper11">
    HEREDOC;
        include(__DIR__.'/views-content/view-registration-options.php');
        echo "</div>";

        echo <<<HEREDOC
                <h4 id="view12" class="cg_view_header">Login$cg_v14_general_options_title_string</h4>
    <div class="cg_view cgLoginOptions cgViewHelper12">
    HEREDOC;
        include(__DIR__.'/views-content/view-login-options.php');
        echo "</div>";

        echo <<<HEREDOC
                <h4 id="view13" class="cg_view_header">Login via Google (general)</h4>
    <div class="cg_view cgGoogleSignInOptions cgViewHelper13">
    HEREDOC;
        include(__DIR__.'/views-content/view-google-sign-in-options.php');
        echo "</div>";
    }

    if($isEditTranslationsOnly){
	    /*   echo <<<HEREDOC
				   <h4 id="translationsGeneral" class="cg_view_header cg_view_header_translations">Translations general</h4>
		   <div class="cg_view cg_view_translations  cgViewHelper14">
	   HEREDOC;
		   include(__DIR__.'/views-content/view-translations-general-options.php');
		   echo "</div>";*/
        echo <<<HEREDOC
        <h4 id="translationsEcommerce" class="cg_view_header cg_view_header_translations">Translations ecommerce (general)</h4>
<div class="cg_view cg_view_translations  cgViewHelper15">
HEREDOC;
        include(__DIR__.'/views-content/view-translations-ecommerce-options.php');
        echo "</div>";
    }

}else{
    if($isEditOptionsOnly){


	    $cg_short_code_galleries_configuration_cg_gallery_ecommerce = '';

	    if(intval($galleryDbVersion)>=22){
		    $cg_short_code_galleries_configuration_cg_gallery_ecommerce = '<div class="cg_short_code_galleries_configuration cg_short_code_galleries_configuration_cg_gallery_ecommerce '.$cg_since_v22.'">cg_galleries_ecommerce</div>';
	    }


        echo <<<HEREDOC
        <h4 id="view10" class="cg_view_header cg_view_header_general">Galleries options</h4>
<div class="cg_view cgGeneralOptions cgViewHelper10">
HEREDOC;
        include(__DIR__.'/views-content/view-general-options.php');

		echo "<p style='text-align: center; font-size: 18px; line-height: 26px;margin-bottom: 30px;'>Galleries options for <b>cg_galleries shortcodes</b></p>";

	    echo <<<HEREDOC
<div class="cg_short_code_galleries_configuration_buttons">
    <div class="cg_short_code_galleries_configuration_buttons_container">
        <div class="cg_short_code_galleries_configuration cg_short_code_galleries_configuration_cg_gallery cg_active $cg_since_v22">cg_galleries</div>
        <div class="cg_short_code_galleries_configuration cg_short_code_galleries_configuration_cg_gallery_user $cg_since_v22" >cg_galleries_user</div>
        <div class="cg_short_code_galleries_configuration cg_short_code_galleries_configuration_cg_gallery_no_voting $cg_since_v22">cg_galleries_no_voting</div>
        <div class="cg_short_code_galleries_configuration cg_short_code_galleries_configuration_cg_gallery_winner $cg_since_v22">cg_galleries_winner</div>
        $cg_short_code_galleries_configuration_cg_gallery_ecommerce
    </div>
</div>
<div class="cg_short_code_galleries_configuration_note" >
<div class="cg_arrow_up"></div>
<b>NOTE:</b> "Gallery view options" can be configured for every gallery shortcode</div>
HEREDOC;

	    include(__DIR__.'/shortcodes-configuration/shortcodes-galleries/shortcode-cg-galleries.php');
	    include(__DIR__.'/shortcodes-configuration/shortcodes-galleries/shortcode-cg-galleries-user.php');
	    include(__DIR__.'/shortcodes-configuration/shortcodes-galleries/shortcode-cg-galleries-no-voting.php');
	    include(__DIR__.'/shortcodes-configuration/shortcodes-galleries/shortcode-cg-galleries-winner.php');
	    if(intval($galleryDbVersion)>=22){
		    include(__DIR__.'/shortcodes-configuration/shortcodes-galleries/shortcode-cg-galleries-ecommerce.php');
	    }

        echo "</div>";

	    // status, repair, mail
        echo <<<HEREDOC
    <h4 id="statusRepairMail" class="cg_view_header">Status, repair, mail</h4>
<div class="cg_view  statusRepairMail" style="padding-bottom:5px;">
HEREDOC;
        echo <<<HEREDOC
<div class='cg_view_container '>
HEREDOC;
        echo <<<HEREDOC
<div class='cg_view_options_rows_container' style="margin-bottom:15px;">
     <p class='cg_view_options_rows_container_title' style="line-height:25px;">Check database status, repair cached json files, check if sent mails were all ok</p>
</div>
HEREDOC;
        echo "<div style='width:100%;display: flex;flex-flow: column;margin-bottom:15px;margin-top:15px;'>";
        echo "<div style='width:100%;'>";
        echo "<div style=\"width:245px;margin: 0 auto;\"><a href=\"?page=".cg_get_version()."/index.php&amp;corrections_and_improvements=true&amp;option_id=$galeryNR\" class='cg_load_backend_link'><input class=\"cg_backend_button cg_backend_button_back\" type=\"button\" value=\"Status, repair and mail exceptions\" style=\"width:245px;padding-right: 0;\"></a>
</div></div></div>";
        echo <<<HEREDOC
</div>
HEREDOC;
        echo "</div>";
        // status, repair, mail --- END

        echo <<<HEREDOC
        <h4 id="view11" class="cg_view_header">Registration$cg_v14_general_options_title_string</h4>
<div class="cg_view cgRegistrationOptions cgViewHelper11">
HEREDOC;
        include(__DIR__.'/views-content/view-registration-options.php');
        echo "</div>";

        echo <<<HEREDOC
        <h4 id="view12" class="cg_view_header">Login$cg_v14_general_options_title_string</h4>
<div class="cg_view cgLoginOptions cgViewHelper12">
HEREDOC;
        include(__DIR__.'/views-content/view-login-options.php');
        echo "</div>";

        echo <<<HEREDOC
        <h4 id="view15" class="cg_view_header">Login via Google (general)</h4>
<div class="cg_view cgGoogleSignInOptions cgViewHelper15">
HEREDOC;
        include(__DIR__.'/views-content/view-google-sign-in-options.php');
        echo "</div>";
    }

    if($isEditEcommerceOnly){
        if(intval($galleryDbVersion)>=22) {
            echo <<<HEREDOC
        <h4 id="view13" class="cg_view_header cg_view_header_general">Galleries</h4>
<div class="cg_view cgEcommerceGeneralOptions cgViewHelper13">
HEREDOC;
            echo <<<HEREDOC
<div class='cg_view_container'>
<div class='cg_view_options_rows_container'>
HEREDOC;
            include(__DIR__.'/views-content/view-ecommerce-general-options.php');
echo <<<HEREDOC
</div>
</div>
HEREDOC;
	        echo <<<HEREDOC
<h4 id="view14" class="cg_view_header">Invoice</h4>
<div class="cg_view cgInvoiceGeneralOptions cgViewHelper14">
HEREDOC;
	        echo <<<HEREDOC
<div class='cg_view_container'>
<div class='cg_view_options_rows_container'>
HEREDOC;
	        include(__DIR__.'/views-content/view-ecommerce-invoice-options.php');
	        echo <<<HEREDOC
</div>
</div>
HEREDOC;// cg_save_all_options must have extra div before placed

        }else{
            echo <<<HEREDOC
        <h4 id="view13" class="cg_view_header">Ecommerce $cg_v14_general_options_title_string</h4>
<div class="cg_view cgEcommerceGeneralOptions cgViewHelper13">
HEREDOC;
            echo <<<HEREDOC
<div class='cg_view_container'>
    <div class='cg_view_options_rows_container'>
        <div class='cg_view_options_rows_container_title'>
            Available for galleries created or copied in version 22.0.0 or later
        </div>
    </div>
</div>
HEREDOC;
            echo "</div>";
        }
    }
    if($isEditTranslationsOnly){
		/*
        echo <<<HEREDOC
        <h4 id="translationsGeneral" class="cg_view_header cg_view_header_translations">Translations general</h4>
<div class="cg_view cg_view_translations  cgViewHelper14">
HEREDOC;

        include(__DIR__.'/views-content/view-translations-general-options.php');
        echo "</div>";*/
        echo <<<HEREDOC
        <h4 id="translationsEcommerce" class="cg_view_header cg_view_header_translations">Translations ecommerce (general)</h4>
<div class="cg_view cg_view_translations  cgViewHelper15">
HEREDOC;
        include(__DIR__.'/views-content/view-translations-ecommerce-options.php');
        echo "</div>";
    }

}

$cgSaveOptionsButtonHideClass = '';

if($isEditEcommerceOnly && intval($galleryDbVersion)<22){
    $cgSaveOptionsButtonHideClass = 'cg_hide';
}

echo <<<HEREDOC
 </div>
<input type="hidden" name="changeSize" value="true" />
<div style="" id="cg_save_all_options" class="cg_hidden $cgSaveOptionsButtonHideClass"><input  class="cg_backend_button_gallery_action" type="submit" value="Save options" id="cgSaveOptionsButton" /></div>
            </div>
HEREDOC;



echo "</form>";



?>