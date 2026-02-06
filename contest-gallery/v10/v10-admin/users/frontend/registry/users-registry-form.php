<?php

if(!isset($cgHideUserAjaxLoginForm)){
    $cgHideUserAjaxLoginForm = '';
}

$mainCGdivUploadFormType = 'mainCGdivUploadFormStatic';
$cgHideUserAjaxLoginFormCloseButton = 'cg_hide';

if(!isset($galeryIDuser)){
   $galeryIDuser = $GalleryID;
}

if(!isset($FeControlsStyleRegistry)){
    $FeControlsStyleRegistry = $cgFeControlsStyle;
}

if(!isset($BorderRadiusRegistry)){
    $BorderRadiusRegistry = $BorderRadiusClass;
}

$isProVersion = false;
$plugin_dir_path = plugin_dir_path(__FILE__);
if(is_dir ($plugin_dir_path.'/../../../../../../contest-gallery-pro') && strpos(cg_get_version_for_scripts(),'-PRO')!==false){
    $isProVersion = true;
}

$wp_upload_dir = wp_upload_dir();
$intervalConf = cg_shortcode_interval_check($GalleryID,[],'cg_users_reg');

if(!$isProVersion || intval($galleryDbVersion)<14){
	$intervalConf['shortcodeIsActive'] = true;
}
if(!$intervalConf['shortcodeIsActive']){
    echo contest_gal1ery_convert_for_html_output_without_nl2br($intervalConf['TextWhenShortcodeIntervalIsOff']);
}else{
    echo contest_gal1ery_convert_for_html_output_without_nl2br($intervalConf['TextWhenShortcodeIntervalIsOn']);

    echo "<div id='cg_user_registry_div' class='cg_user_registry_div mainCGdivUploadForm $InGalleryRegistryHide $mainCGdivUploadFormType $cgHideUserAjaxLoginForm $BorderRadiusRegistry $FeControlsStyleRegistry' data-cg-gid='$galeryIDuser' >";

    echo "<div class='cg_hover_effect cg-close-upload-form $FeControlsStyleRegistry $cgHideUserAjaxLoginFormCloseButton' data-cg-gid='$galeryIDuser' data-cg-tooltip='$language_Close'>";
    echo "</div>";

    echo "<div style='visibility: hidden;' class='mainCGdivUploadFormContainer' >";

    if(!is_user_logged_in()){
        echo "<div id='cg_user_registry_div_before' class='cg_user_registry_div_before'>";
        if($shortcode_name == 'cg_users_pin'){
            echo contest_gal1ery_convert_for_html_output_without_nl2br($registry_and_login_options->TextBeforePinFormBeforeLoggedIn);
        }else{
            echo contest_gal1ery_convert_for_html_output_without_nl2br($registry_and_login_options->TextBeforeRegFormBeforeLoggedIn);
        }
        echo "</div>";
    }

    echo "<input type='hidden' id='cg_check_mail_name_value' class='cg_check_mail_name_value' value='0'>";
    echo "<input type='hidden' id='cg_site_url' value='" . get_site_url() . "'/>";

    echo "<span id='cg_user_registry_anchor'/></span>";

    // 1 Array
    $selectUserFormArray = json_decode(json_encode($selectUserForm), true);

    // 2 Sorted
    $selectUserFormArraySorted = cg_sort_json_upload_form($selectUserFormArray);

    // 3 Convert associative array back to stdClass objects
    $selectUserForm = json_decode(json_encode($selectUserFormArraySorted));


/*    echo "<div>";
    echo "<pre style='display:block !important;'>";
    print_r($selectUserForm);
    echo "</pre>";
    echo "</div>";*/

    echo '<form action="?cg_register=true" method="post" id="cg_user_registry_form" class="cg_user_registry_form '.$shortcode_name.'" enctype="multipart/form-data" data-cg-gid="$GalleryID">';

// User ID �berpr�fung ob es die selbe ist
// $check = wp_create_nonce("check");
// new check required wp_create_nonce might be different when calling ajax
//$check = md5(wp_salt('auth') . '---cgreg---' . $GalleryID);
    $cg_login_check =  cg_hash_function('---cgreg---'.$GalleryID);

    echo "<input type='hidden' name='cg_current_page_id' id='cg_current_page_id' value='$cg_current_page_id'>";
    echo "<input type='hidden' name='cg_check' class='cg_check_not_robot_simple' id='cg_check' value='$cg_login_check'>";
    echo "<input type='hidden' name='action' value='post_cg_registry'>";// !important, otherwise wordpress post will not work!!!

    echo "<input type='hidden' name='cg_gallery_id_registry' id='cg_gallery_id_registry' value='$GalleryID'>";

    echo "<input type='hidden' id='cg_db_version' value='$galleryDbVersion'>";

    $previousRowNumber = -1;
    $echoRowClosed = false;
    $isEmptyCol = false;// used only for registry form, because upload form has steps and not usable here

    foreach ($selectUserForm as $key => $value) {

        $PinField = !empty($value->PinField) ? 1 : 0;

        // echo rows
        $echoRowClosed = false;
        $isEmptyCol = false;
        if($previousRowNumber!=-1 && $value->RowNumber > $previousRowNumber){
            $echoRowClosed = true;
            cg_echo_closed_upload_form();
        }

        if($value->RowNumber == 0){// if first time load in 27.0.0
            echo "<div class='cg_row'>";
            $echoRowClosed = true;
        }elseif($value->RowNumber != $previousRowNumber){
            echo "<div class='cg_row'>";
            $echoRowClosed = false;
        }

        $previousRowNumber = $value->RowNumber;

        $cgContentField = '';
        $cg_empty = '';

        if ($value->Field_Type=='empty-col'){
            $cg_empty = 'cg_empty';
            $isEmptyCol = true;
        }

        $isRender = true;
        if($shortcode_name == 'cg_users_pin' && !$PinField && $value->Field_Type!='main-mail') {
            $isRender = false;
        }

        echo "<div class='cg_row_col $cg_empty'>";
        if($isEmptyCol){
          //  cg_echo_closed_upload_form();
           // if(!$echoRowClosed){
             //   cg_echo_closed_upload_form();
            //}
            //    continue;
        }
        // echo rows --- END

        if(!$cg_empty && $isRender){
        $required = ($value->Required == 1) ? "*" : "";

        $cgCheckUsernameNicknameMail = '';

        if (($value->Field_Type == 'main-user-name' OR $value->Field_Type == 'main-nick-name' OR $value->Field_Type == 'main-mail')) {
            $placeholder = cg1l_sanitize_method(contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content));
            if ($value->Field_Type == 'main-user-name') {
                $cgContentField = "<input type='text' maxlength='" . $value->Max_Char . "' placeholder='$placeholder' class='cg_registry_form_field cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_Fields[$i][Field_Content]'>";
                $cgCheckUsernameNicknameMail = "id='cg_user_name_check_alert'";
            }
            if ($value->Field_Type == 'main-nick-name') {
                $cgContentField = "<input type='text' maxlength='" . $value->Max_Char . "' placeholder='$placeholder' class='cg_registry_form_field cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_Fields[$i][Field_Content]'>";
                $cgCheckUsernameNicknameMail = "id='cg_nick_name_check_alert'";
            }
            if ($value->Field_Type == 'main-mail') {
                $cgContentField = "<input type='text' placeholder='$placeholder' class='cg_registry_form_field cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_Fields[$i][Field_Content]'>";
                $cgCheckUsernameNicknameMail = "id='cg_mail_check_alert'";
            }
        }
        if (($value->Field_Type == 'password' OR $value->Field_Type == 'password-confirm')) {
            $placeholder = cg1l_sanitize_method(contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content));
            $cgContentField = "<input type='password' maxlength='" . $value->Max_Char . "' placeholder='$placeholder' autocomplete='off' class='cg_registry_form_field cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_Fields[$i][Field_Content]' readonly onfocus='this.removeAttribute(\"readonly\")';>";
        }
        if ($value->Field_Type == 'user-comment-field') {
            $placeholder = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content);
            $cgContentField = "<textarea maxlength='" . $value->Max_Char . "' placeholder='$placeholder' class='cg_registry_form_field cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_Fields[$i][Field_Content]' rows='6' ></textarea>";
        }

        if ($value->Field_Type == 'profile-image') {
            $placeholder = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content);
            $cgContentField = "<input type='file' class='cg_registry_form_field cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_input_image_upload_file[]'>";
        }

        if ($value->Field_Type == 'wpfn') {
            $placeholder = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content);
            $cgContentField = "<input maxlength='" . $value->Max_Char . "' type='text' placeholder='$placeholder' class='cg_registry_form_field cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_Fields[$i][Field_Content]'>";
        }

        if ($value->Field_Type == 'wpln') {
            $placeholder = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content);
            $cgContentField = "<input maxlength='" . $value->Max_Char . "' type='text' placeholder='$placeholder' class='cg_registry_form_field cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_Fields[$i][Field_Content]'>";
        }

        if ($value->Field_Type == 'user-text-field') {
            $placeholder = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content);
            $cgContentField = "<input maxlength='" . $value->Max_Char . "' type='text' placeholder='$placeholder' class='cg_registry_form_field cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_Fields[$i][Field_Content]'>";
        }

        if ($value->Field_Type == 'user-check-field') {
            $textAr = explode("\n", $value->Field_Content);// sanitazing happens after that in foreach
            $cgContentField = '<div class="cg_check">';
                foreach ($textAr as $optionKey => $optionValue) {
                    $optionValue = sanitize_text_field(contest_gal1ery_convert_for_html_output_without_nl2br($optionValue));
                    $cgContentField .= '<div class="cg_check_button">
                            <label for="cg_reg_check_button_'.$value->id.$optionKey.'">'.$optionValue.'</label>
                            <input type="checkbox" id="cg_reg_check_button_'.$value->id.$optionKey.'" value="'.$optionValue.'" class="cg_check_button_input">
                    </div>';
                }
            $cgContentField .= '</div>';
        }

        if ($value->Field_Type == 'user-radio-field') {
            $textAr = explode("\n", $value->Field_Content);// sanitazing happens after that in foreach
            $cgContentField = '<div class="cg_radio">';
                foreach ($textAr as $optionKey => $optionValue) {
                    $optionValue = sanitize_text_field(contest_gal1ery_convert_for_html_output_without_nl2br($optionValue));
                    $cgContentField .= '<div class="cg_radio_button">
                            <label for="cg_reg_radio_button_'.$value->id.$optionKey.'">'.$optionValue.'</label>
                            <input type="radio" id="cg_reg_radio_button_'.$value->id.$optionKey.'" name="cg_Fields['.$i.'][Field_Content]" value="'.$optionValue.'" class="cg_radio_button_input">
                    </div>';
                }
            $cgContentField .= '</div>';
        }

        if ($value->Field_Type == 'user-html-field') {
            $content = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content);
            $cgContentField = "<div class='cg-" . $value->Field_Type . "'>$content</div>";
        }


        if ($value->Field_Type == 'user-robot-field') {
            echo "<div class='cg_form_div cg_captcha_not_a_robot_field_class cg_captcha_not_a_robot_registry_field' id='cg_captcha_not_a_robot_registry_field'>";
            echo "<div>";
        }elseif ($value->Field_Type == 'user-check-field') {
            echo "<div id='cg-registry-" . $value->Field_Order . "' class='cg_form_div cg_form_div_check'>";
        } else {
            echo "<div id='cg-registry-" . $value->Field_Order . "' class='cg_form_div'>";
        }

        if ($value->Field_Type != 'user-html-field' && $value->Field_Type != 'user-robot-recaptcha-field') {

            if ($value->Field_Type == 'user-robot-field') {
                echo "<label for='cg_" . $cg_login_check . "_registry' >".cg1l_sanitize_method(contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Name))." *</label>";
            } else {
                echo "<label for='cg_registry_form_field" . $value->id . "' >" .cg1l_sanitize_method(contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Name)) . " $required</label>";
            }

            echo "<input type='hidden' name='cg_Fields[$i][Form_Input_ID]' value='" . $value->id . "'>";
            echo "<input type='hidden' name='cg_Fields[$i][Field_Type]' value='" . $value->Field_Type . "'>";
            echo "<input type='hidden' class='cg_field_order cg_field_number' name='cg_Fields[$i][Field_Order]' data-cg-field-number='".$i."' value='" . $value->Field_Order . "'>";

        }


        // Pr�fen ob check-agreement-feld ist ansonsten Text oder, Comment Felder anzeigen
        if ($value->Field_Type == 'user-check-agreement-field') {

            $cgCheckContent = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content);
            echo "<div class='cg-check-agreement-container'>";
            echo "<div class='cg-check-agreement-checkbox'>";
            echo "<input type='checkbox' id='cg_registry_form_field" . $value->id . "' class='cg_check_f_checkbox' value='checked' name='cg_Fields[$i][Field_Content]'>";
            echo "<input type='hidden' class='cg_form_required' value='" . $value->Required . "'>";// Pr�fen ob Pflichteingabe
            echo "</div>";
            echo "<div class='cg-check-agreement-html'>";
            echo $cgCheckContent;
            echo "</div>";
            echo "</div>";

        } else {

            if ($value->Field_Type == 'user-select-field') {

                $textAr = explode("\n", $value->Field_Content);// sanitazing happens after that in foreach

                echo "<select name='cg_Fields[$i][Field_Content]' class='cg-" . $value->Field_Type . "' id='cg_registry_form_field" . $value->id . "' name='cg_Fields[$i][Field_Content]' >";

                echo "<option value=''>$language_pleaseSelect</option>";

                foreach ($textAr as $optionKey => $optionValue) {

                    $optionValue = sanitize_text_field(contest_gal1ery_convert_for_html_output_without_nl2br($optionValue));
                    echo "<option value='$optionValue'>$optionValue</option>";

                }

                echo "</select>";
                echo "<input type='hidden' class='cg_form_required' value='" . $value->Required . "'>";// Pr�fen ob Pflichteingabe
            } elseif ($value->Field_Type == 'user-robot-field') {

                // NICHT ENTFERNEN!!!!
                // Wichtig!!! Empty if clausel muss hier bleiben beim aktullen Aufbau sonst verschieben sich Felder.

            } elseif ($value->Field_Type == 'user-robot-recaptcha-field') {

                // NICHT ENTFERNEN!!!!
                // Wichtig!!! Empty if clausel muss hier bleiben beim aktullen Aufbau sonst verschieben sich Felder.

                if(!empty($GalleryID)){
                    $GalleryIDrecaptcha = $GalleryID;
                }else{
                    $GalleryIDrecaptcha = 'v14';
                }

                echo "<div class='cg_recaptcha_reg_form' id='cgRecaptchaRegForm$GalleryIDrecaptcha'>";

                echo "</div>";
                echo "<p class='cg_input_error cg_hide cg_recaptcha_not_valid_reg_form_error' id='cgRecaptchaNotValidRegFormError$GalleryIDrecaptcha'></p>";

                ?>

                <script type="text/javascript">
                    var ReCaKey = "<?php echo $value->ReCaKey; ?>";
                    var cgRecaptchaNotValidRegFormError = "<?php echo 'cgRecaptchaNotValidRegFormError' . $GalleryIDrecaptcha . ''; ?>";
                    var cgRecaptchaRegForm = "<?php echo 'cgRecaptchaRegForm' . $GalleryIDrecaptcha . ''; ?>";

                    var cgRecaptchaCallbackRegistryFormRendered = false;

                    if(typeof cgRecaptchaCallbackRendered == 'undefined'){

                        cgRecaptchaCallbackRendered = true;
                        cgRecaptchaCallbackRegistryFormRendered = true;

                        var cgCaRoReRegCallback = function () {
                            var element = document.getElementById(cgRecaptchaNotValidRegFormError);
                            //element.parentNode.removeChild(element);
                            element.classList.remove("cg_recaptcha_not_valid_reg_form_error");
                            element.classList.add("cg_hide");
                        };

                        if(typeof cgRecaptchaFormNormalRendered == 'undefined'){
                            cgRecaptchaFormNormalRendered = true;
                            var cgOnloadRegCallback = function () {
                                grecaptcha.render(cgRecaptchaRegForm, {
                                    'sitekey': ReCaKey,
                                    'callback': 'cgCaRoReRegCallback'
                                });
                            };
                        }

                    }


                </script>
                <script src="https://www.google.com/recaptcha/api.js?onload=cgOnloadRegCallback&render=explicit&hl=<?php echo $value->ReCaLang; ?>"
                        async defer>
                </script>

                <?php

            } else {

                echo $cgContentField;
                if ($value->Field_Type != 'user-html-field') {
                    echo "<input type='hidden' class='cg_Min_Char' value='" . $value->Min_Char . "'>"; // Pr�fen minimale Anzahl zeichen
                    echo "<input type='hidden' class='cg_Max_Char' value='" . $value->Max_Char . "'>"; // Pr�fen maximale Anzahl zeichen
                    echo "<input type='hidden' class='cg_form_required' value='" . $value->Required . "'>";// Pr�fen ob Pflichteingabe
                }

            }

        }

        if ($value->Field_Type != 'user-robot-recaptcha-field' && $value->Field_Type != 'user-robot-field') {
            echo "<p class='cg_input_error cg_hide' $cgCheckUsernameNicknameMail></p>";// Fehlermeldung erscheint hier
        }

        if ($value->Field_Type == 'user-robot-field') {
            echo "</div>";
            echo "<p class='cg_input_error cg_hide' $cgCheckUsernameNicknameMail></p>";// Fehlermeldung erscheint hier
        }

            $i++;

        }

        if(!$cg_empty && $isRender){
            echo "</div>";
        }

        echo "</div>";

        if($value->RowNumber == 0){
            cg_echo_closed_upload_form();
        }


    }

    if(!$echoRowClosed){
        cg_echo_closed_upload_form();
    }

    echo "<div id='cg_registry_submit_container' class='cg_form_upload_submit_div cg_form_div'>";
    //echo '<input type="submit" name="cg_registry_submit" id="cg_users_registry_check" class="cg_form_upload_submit" value="' . $language_sendRegistry . '">';
    $submitText = $language_sendRegistry;
    if($shortcode_name == 'cg_users_pin'){
        $submitText = $language_SendPin;
    }
    echo '<button type="submit" name="cg_registry_submit" id="cg_users_registry_check" class="cg_users_registry_check cg_form_upload_submit" >' . $submitText . '</button>';
    echo "<p class='cg_input_error cg_hide' id='cg_registry_manipulation_error'></p>";
    echo '<div class="cg_form_div_image_upload_preview_loader_container cg_hide"><div class="cg_form_div_image_upload_preview_loader cg-lds-dual-ring-gallery-hide cg-lds-dual-ring-gallery-hide-mainCGallery"></div></div>';
    echo "</div>";
    echo '</form>';


    if($shortcode_name=='cg_users_pin'){
        echo '<form action="" method="post" id="cglPinForm" 
              class="cglPinForm cg_hide" enctype="multipart/form-data" data-cg-gid="'.$GalleryID.'">
           <input type="hidden" name="cglActivationKey" class="cglActivationKey" id="cglActivationKey" value="">
             <div id="cglPinSuccess" class="cglPinSuccess cg_row cg_hide">
                <div class="cg_row_col cg_100">
                    '.$TextAfterPinConfirmation.'
                </div>
            </div>
            <div class="cg_row cglPinContent" id="cglPinContent" >
                <div class="cg_row_col cg_100">
                    <div class="cg_form_div">
                        <p style="text-align: center;">'.$language_PinSentToYourEmail.'</p>
                        <label for="cglPin" style="text-align: center;">'.$language_EnterPin.'</label>
                        <div class="cg-pin-wrapper">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                   class="cg-pin-digit" data-index="0">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                   class="cg-pin-digit" data-index="1">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                   class="cg-pin-digit" data-index="2">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                                   class="cg-pin-digit" data-index="3">
                            <!-- real value that goes to PHP -->
                            <input type="hidden" name="cg_pin" id="cglPinValue" class="cglPinValue">
                        </div>
                        <p class="cg_input_error cg_hide cglPinError" id="cglPinError"  style="text-align: center; margin-left: auto; margin-right: auto;"></p>
                    </div>
                </div>
                <div class="cg_form_upload_submit_div cg_form_div" style="text-align: center;margin-top: 0;">
                    <button type="submit" id="cglPinSubmit" 
                        class="cglPinSubmit cg_form_upload_submit">'.$language_ConfirmPin.'</button>
                        <p class="cg_input_error cg_hide cglPinSubmitError" id="cglPinSubmitError" style="text-align: center; margin-left: auto; margin-right: auto;"></p>
                </div>
            </div>
        </form>
        <form action="" method="post" id="cglResendPinForm" 
              class="cglResendPinForm cg_hide" enctype="multipart/form-data" data-cg-gid="'.$GalleryID.'">
            <input type="hidden" name="cglActivationKeyResend" class="cglActivationKeyResend" id="cglActivationKeyResend" value="">
            <div class="cg_row">
                <div class="cg_row_col cg_100">
                    <div class="cg_form_div">
                        <label for="cglResendPin" style="text-align: center;">'.$language_PinExpiredRequestNewOne.'</label>
                    </div>
                </div>
            </div>
            <div class="cg_form_upload_submit_div cg_form_div" style="text-align: center;margin-top: 0;">
                <button type="submit" id="cglResendPinSubmit" 
                        class="cg_form_upload_submit cglResendPinSubmit">'.$language_SendNewPin.'</button>
                        <p class="cg_input_error cg_hide cglResendPinSubmitError" id="cglResendPinSubmitError"></p>
            </div>
        </form>';
    }

    echo "</div>";

    echo "<div id='cg_user_registry_div_messages' style='height:0;visibility: hidden;'>";
//echo "$language_MaximumAllowedWidthForJPGsIs";
    echo "<input type='hidden' id='cg_show_upload' value='1'>";

//echo "language_ThisFileTypeIsNotAllowed: $language_ThisFileTypeIsNotAllowed";
    echo "<input type='hidden' id='cg_file_not_allowed_1' value='$language_ThisFileTypeIsNotAllowed'>";
    echo "<input type='hidden' id='cg_file_size_to_big' value='$language_TheFileYouChoosedIsToBigMaxAllowedSize'>";
//echo "<input type='hidden' id='cg_post_size' value='$post_max_sizeMB'>";

    echo "<input type='hidden' id='cg_to_high_resolution' value='$language_TheResolutionOfThisPicIs'>";

    echo "<input type='hidden' id='cg_max_allowed_resolution_jpg' value='$language_MaximumAllowedResolutionForJPGsIs'>";
    echo "<input type='hidden' id='cg_max_allowed_width_jpg' value='$language_MaximumAllowedWidthForJPGsIs'>";
    echo "<input type='hidden' id='cg_max_allowed_height_jpg' value='$language_MaximumAllowedHeightForJPGsIs'>";

    echo "<input type='hidden' id='cg_max_allowed_resolution_png' value='$language_MaximumAllowedResolutionForPNGsIs'>";
    echo "<input type='hidden' id='cg_max_allowed_width_png' value='$language_MaximumAllowedWidthForPNGsIs'>";
    echo "<input type='hidden' id='cg_max_allowed_height_png' value='$language_MaximumAllowedHeightForPNGsIs'>";

    echo "<input type='hidden' id='cg_max_allowed_resolution_gif' value='$language_MaximumAllowedResolutionForGIFsIs'>";
    echo "<input type='hidden' id='cg_max_allowed_width_gif' value='$language_MaximumAllowedWidthForGIFsIs'>";
    echo "<input type='hidden' id='cg_max_allowed_height_gif' value='$language_MaximumAllowedHeightForGIFsIs'>";

    echo "<input type='hidden' id='cg_check_agreement' value='$language_YouHaveToCheckThisAgreement '>";
    echo "<input type='hidden' id='cg_check_email_upload' value='$language_EmailAddressHasToBeValid'>";
    echo "<input type='hidden' id='cg_min_characters_text' value='$language_MinAmountOfCharacters'>";
    echo "<input type='hidden' id='cg_max_characters_text' value='$language_MaxAmountOfCharacters'>";
    echo "<input type='hidden' id='cg_no_picture_is_choosed' value='$language_ChooseYourImage'>";

    echo "<input type='hidden' id='cg_language_BulkUploadQuantityIs' value='$language_BulkUploadQuantityIs'>";
    echo "<input type='hidden' id='cg_language_BulkUploadLowQuantityIs' value='$language_BulkUploadLowQuantityIs'>";

    echo "<input type='hidden' id='cg_language_BulkUploadLowQuantityIs' value='$language_BulkUploadLowQuantityIs'>";
    echo "<input type='hidden' id='cg_language_ThisMailAlreadyExists' value='$language_ThisMailAlreadyExists'>";
    echo "<input type='hidden' id='cg_language_ThisNicknameAlreadyExists' value='$language_ThisNicknameAlreadyExists'>";
    echo "<input type='hidden' id='cg_language_ThisUsernameAlreadyExists' value='$language_ThisUsernameAlreadyExists'>";

    echo "<input type='hidden' id='cg_language_PleaseFillOut' value='$language_PleaseFillOut'>";
    echo "<input type='hidden' id='cg_language_Required' value='$language_Required'>";
    echo "<input type='hidden' id='cg_language_youHaveNotSelected' value='$language_youHaveNotSelected'>";

    echo "<input type='hidden' id='cg_language_PasswordsDoNotMatch' value='$language_PasswordsDoNotMatch'>";
    echo "<input type='hidden' id='cg_language_ChooseYourImage' value='$language_ChooseYourImage'>";

    echo "<input type='hidden' id='cg_language_pleaseConfirm' value='$language_pleaseConfirm'>";
    echo "<input type='hidden' id='cg_language_ThisFileTypeIsNotAllowed' value='$language_ThisFileTypeIsNotAllowed'>";
    echo "<input type='hidden' id='cg_language_TheFileYouChoosedIsToBigMaxAllowedSize' value='$language_TheFileYouChoosedIsToBigMaxAllowedSize'>";

    echo "<input type='hidden' id='cg_language_ActivationKeyNotFound' value='$language_ActivationKeyNotFound'>";

    echo "<input type='hidden' id='cg_users_registry_check_submit_language' value='$language_sendRegistry'>";
    echo "<input type='hidden' id='cg_language_IncorrectPinPleaseTryAgain' value='$language_IncorrectPinPleaseTryAgain'>";

    echo "</div>";
    echo "</div>";

}

/*
if($shortcode_name=='cg_users_pin'){
    echo '
<div id="cglPinFormBox" class="mainCGdivUploadForm mainCGdivUploadFormStatic '.$BorderRadiusRegistry.' '.$FeControlsStyleRegistry.' cg_hide" data-cg-gid="0">
    <div class="cg_hover_effect cg-close-upload-form '.$FeControlsStyleRegistry.' cg_hide"
         data-cg-gid="0" data-cg-tooltip="Close"></div>
    <div class="mainCGdivUploadFormContainer">

    </div>
</div>
';
}*/


?>