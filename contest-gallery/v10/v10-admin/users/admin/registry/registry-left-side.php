<?php

// IDs of the div boxes
$nfCount = 10;
$kfCount = 20;
$efCount = 30;
$bhCount = 40;
$htCount = 50;
$cbCount = 60;
$seCount = 70;
$caRoCount = 80;
$caRoReCount = 90;
$wpfnCount = 100;
$wplnCount = 110;
$piCount = 120;
$raCount = 130;
$chkCount = 140;

// IDs of the right boxes
$nfRightCount = 10;
$kfRightCount = 20;
$efRightCount = 30;
$bhRightCount = 40;
$htRightCount = 50;
$cbRightCount = 60;
$seRightCount = 70;
$caRoRightCount = 80;
$caRoReRightCount = 90;
$wpfnRightCount = 100;
$wplnRightCount = 110;
$piRightCount = 120;
$raRightCount = 130;
$chkRightCount = 140;

// Further IDs of the div boxes
$nfHiddenCount = 100;
$kfHiddenCount = 200;
$efHiddenCount = 300;
$bhHiddenCount = 400;
$htHiddenCount = 500;
$cbHiddenCount = 600;
$seHiddenCount = 700;
$caRoHiddenCount = 800;
$caRoReHiddenCount = 900;
$wpfnHiddenCount = 1000;
$wplnHiddenCount = 1100;
$piHiddenCount = 1200;
$raHiddenCount = 1300;
$chkHiddenCount = 1400;

$id_for_editor = 0;

// FELDBENENNUNGEN

// 1 = Feldtyp
// 2 = Feldnummer
// 3 = Feldtitel
// 4 = Feldinhalt
// 5 = Feldkrieterium1
// 6 = Feldkrieterium2
// 7 = Felderfordernis

//print_r($selectFormInput);

// Zum z�hlen von Feld Reihenfolge
$i = 1;

$isOnlyPlaceHolder = false;

if(true){

    echo "<div id='cgFieldsToCloneAndAppend' class='cg_hide'>";

    $isOnlyPlaceHolder = true;

    // just as placeholder for all kind of inputs simply
    $value = new stdClass();
    $value->id = 'new-0';
    $value->Field_Order = 0;
    $value->Active = 1;
    $value->Min_Char = 3;
    $value->Max_Char = 100;
    $value->Field_Name = 0;
    $value->PinField = 0;
    $value->Field_Content = '';
    $value->ReCaLang = '';
    $value->ReCaKey = '';
    $value->Field_Type = '';
    $value->Required = 0;
    $value->GalleryID = $GalleryID;
    $value->Use_as_URL = 0;
    $value->ReCaLang = 'en';
    $value->WatermarkPosition = '';
    $id = $value->id; // Unique ID des Form Feldes

    $Field_Name = '';
    $Field_Content = '';
    $fieldOrder = $value->Field_Order;
    $Min_Char = $value->Min_Char;
    $Max_Char = $value->Max_Char;
    $Field_Order = $value->Field_Order;
    $Field_Type = $value->Field_Type;

    $id = $value->id; // Unique ID des Form Feldes
    $idKey = "$id";
    $cg_Necessary = $value->Required;
    if($cg_Necessary==1){$cg_Necessary_checked="checked";}
    else{$cg_Necessary_checked="";}
    if($value->Active==1){
        $hideChecked = "";
    }
    else{
        $hideChecked = "checked='checked'";
    }
    if($value->PinField==1){
        $pinFieldChecked = "checked='checked'";
    }
    else{
        $pinFieldChecked = "";
    }

    $enterKey = '';

    $pleaseSelectLanguage = '';
    $pleaseSelectLanguage .= '<select id="cgReCaLang" name="ReCaLang">';
    $pleaseSelectLanguage .= "<option value='' >Please select language</option>";
    foreach($langOptions as $langKey => $lang){
        $pleaseSelectLanguage .= "<option value='$langKey' >$lang</option>";
    }
    $pleaseSelectLanguage .= '</select>';

    $enterKey = '';
    $ReCaKey = '';
    $enterKey .= "<div style='display:flex;align-items:center;flex-wrap: wrap;'><input type='text' name='ReCaKey' class='cg_reca_key' placeholder='Example Key: 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI' size='30' maxlength='1000' value='$ReCaKey'/>";// Titel und Delete M�glichkeit die oben bestimmt wurde
    $enterKey .=  "<span  class='cg_recaptcha_icon' style='width: 100%;margin-left: 0;margin-top: 15px;margin-bottom: 10px;'>Insert Google reCAPTCHA test key</span>";
    $enterKey .=  "</div>";

    $captchaNote = "<span class='cg_recaptcha_test_note' ><span>NOTE:</span><br><b>Google reCAPTCHA test key</b> is provided from Google for testing purpose.
                                        <br><b>Create your own \"Site key\"</b> here <a href='https://www.google.com/recaptcha/admin' target='_blank'>www.google.com/recaptcha/admin</a><br>Register your site, create a <b>V2 \"I am not a robot\"</b>  key.</span>";

    $RowNumberCount = 1;// in case of first time processing after update to 28.0.0

    include (__DIR__.'/registry-fields-left/reg-check-agreement-left.php');

    include (__DIR__.'/registry-fields-left/reg-input-left.php');

    include (__DIR__.'/registry-fields-left/reg-profile-image-left.php');

    include (__DIR__.'/registry-fields-left/reg-select-left.php');

    include (__DIR__.'/registry-fields-left/reg-radio-left.php');

    include (__DIR__.'/registry-fields-left/reg-check-left.php');

    include (__DIR__.'/registry-fields-left/reg-textarea-left.php');

    include (__DIR__.'/registry-fields-left/wp-email-left.php');

    include (__DIR__.'/registry-fields-left/wp-first-name-left.php');

    include (__DIR__.'/registry-fields-left/wp-last-name-left.php');

    include (__DIR__.'/registry-fields-left/wp-password-left.php');

    include (__DIR__.'/registry-fields-left/wp-password-confirm-left.php');

    include (__DIR__.'/registry-fields-left/wp-username-left.php');

    include (__DIR__.'/registry-fields-left/reg-html-left.php');

    include (__DIR__.'/registry-fields-left/reg-simple-captcha-left.php');

    include (__DIR__.'/registry-fields-left/reg-google-captcha-left.php');

    $isOnlyPlaceHolder = false;

    echo "</div>";

}

echo "<div id='cgUplEditFields'>
    <div id='cgUplEditFieldsText'>Select fields to edit</div>
    <div  id='cgUplEditFieldsToTheRightIcon'></div>
</div>";

echo "<div id='cgCreateUploadSortableArea' class='cg_sortable_area'>";

$RowNumberCount = 1;// in case of first time processing after update to 27.0.0

foreach ($selectFormInput as $key => $value) {

    if($value->Field_Type == 'main-mail'){
        include (__DIR__.'/registry-fields-left/wp-email-left.php');
    }

    if($value->Field_Type == 'password'){
        include (__DIR__.'/registry-fields-left/wp-password-left.php');
    }

    if($value->Field_Type == 'password-confirm'){
        include (__DIR__.'/registry-fields-left/wp-password-confirm-left.php');
    }

    if($value->Field_Type == 'main-user-name'){
        include (__DIR__.'/registry-fields-left/wp-username-left.php');
    }

    if(intval($galleryDbVersion)>=14){
        if($value->Field_Type == 'main-nick-name'){
            include (__DIR__.'/registry-fields-left/wp-nickname-left.php');
        }
    }

    if($value->Field_Type == 'profile-image'){
        include (__DIR__.'/registry-fields-left/reg-profile-image-left.php');
    }

    if($value->Field_Type == 'wpfn'){
        include (__DIR__.'/registry-fields-left/wp-first-name-left.php');
    }

    if($value->Field_Type == 'wpln'){
        include (__DIR__.'/registry-fields-left/wp-last-name-left.php');
    }

    if($value->Field_Type == 'user-text-field'){
        include (__DIR__.'/registry-fields-left/reg-input-left.php');
    }

    if($value->Field_Type == 'user-comment-field'){
        include (__DIR__.'/registry-fields-left/reg-textarea-left.php');
    }

    if($value->Field_Type == 'user-select-field'){
        include (__DIR__.'/registry-fields-left/reg-select-left.php');
    }

    if($value->Field_Type == 'user-radio-field'){
        include (__DIR__.'/registry-fields-left/reg-radio-left.php');
    }

    if($value->Field_Type == 'user-check-field'){
        include (__DIR__.'/registry-fields-left/reg-check-left.php');
    }

    if($value->Field_Type == 'user-robot-field'){
        include (__DIR__.'/registry-fields-left/reg-simple-captcha-left.php');
    }

    if($value->Field_Type == 'user-robot-recaptcha-field'){
        include (__DIR__.'/registry-fields-left/reg-google-captcha-left.php');
    }

    if($value->Field_Type == 'user-html-field'){
        include (__DIR__.'/registry-fields-left/reg-html-left.php');
    }

    if($value->Field_Type == 'user-check-agreement-field'){
        include (__DIR__.'/registry-fields-left/reg-check-agreement-left.php');
    }

    // Zum z�hlen von Feld Reihenfolge
    $i++;

}
echo "</div>";
