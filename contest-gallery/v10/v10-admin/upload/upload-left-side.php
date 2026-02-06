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
$secCount = 90;
$urlCount = 100;
$caRoReCount = 110;
$fbtCount = 120;
$fbdCount = 130;
$dtCount = 140;
$htfCount = 150;
$raCount = 160;
$chkCount = 170;

// IDs of the right boxes
$nfRightCount = 10;
$kfRightCount = 20;
$efRightCount = 30;
$bhRightCount = 40;
$htRightCount = 50;
$cbRightCount = 60;
$seRightCount = 70;
$caRoRightCount = 80;
$secRightCount = 90;
$urlRightCount = 100;
$caRoReRightCount = 110;
$fbtRightCount = 120;
$fbdRightCount = 130;
$dtRightCount = 140;
$htfRightCount = 150;
$raRightCount = 160;
$chkRightCount = 170;

// Further IDs of the div boxes
$nfHiddenCount = 100;
$kfHiddenCount = 200;
$efHiddenCount = 300;
$bhHiddenCount = 400;
$htHiddenCount = 500;
$cbHiddenCount = 600;
$seHiddenCount = 700;
$caRoHiddenCount = 800;
$urlHiddenCount = 1000;
$caRoReHiddenCount = 1100;
$fbtHiddenCount = 1200;
$fbdHiddenCount = 1300;
$dtHiddenCount = 1400;
$htfHiddenCount = 1500;
$raHiddenCount = 1600;
$chkHiddenCount = 1700;

// FELDBENENNUNGEN

// 1 = Feldtyp
// 2 = Feldnummer
// 3 = Feldtitel
// 4 = Feldinhalt
// 5 = Feldkrieterium1
// 6 = Feldkrieterium2
// 7 = Felderfordernis

//print_r($selectFormInput);

$cg_info_show_slider_title = 'Show as info in single entry view';
$cg_info_show_gallery_title = 'Show as title in gallery view (only 1 allowed)';
$cg_tag_show_gallery_title = 'Show as HTML title attribute in gallery (only 1 allowed)';

$cg_disabled_sub_and_third_title = '';
$cg_disabled_sub_and_third_title_note = '';
if(floatval($dbGalleryVersion)<21){
    $cg_disabled_sub_and_third_title = 'cg_disabled_background_color_e0e0e0';
    $cg_disabled_sub_and_third_title_note = '<br><span class="cg_view_option_title_note"><span class="cg_font_weight_bold cg_color_red">NOTE:</span> Only available for galleries copied or created in version 21.0.0 and higher</span>';
}

// simply for bracket
if(true){

    echo "<div id='cgFieldsToCloneAndAppend' class='cg_hide'>";

    // just as placeholder for all kind of inputs simply
    $value = new stdClass();
    $value->Field_Content = serialize([
        'titel' => 'Title',
        'content' => '',
        'min-char' => 3,
        'max-char' => 100,
        'mandatory' => 'off',
        'format' => 'YYYY-MM-DD',
    ]);
    $value->id = 'new-0';
    $value->Active = 0;
    $value->Field_Order = 0;
    $value->Field_Type = '';
    $value->GalleryID = $GalleryID;
    $value->Use_as_URL = 0;
    $value->ReCaKey = '';
    $value->ReCaLang = 'en';
    $value->Version = $dbGalleryVersion;
    $value->WatermarkPosition = '';
    $value->WpAttachmentDetailsType = '';
    $id = $value->id; // Unique ID des Form Feldes

    $dateSelect = <<<HEREDOC
    <select name='upload[$id][format]'>
                            <option value='YYYY-MM-DD' >YYYY-MM-DD</option>
                            <option value='DD-MM-YYYY' >DD-MM-YYYY</option>
                            <option value='MM-DD-YYYY' >MM-DD-YYYY</option>
                            <option value='YYYY/MM/DD' >YYYY/MM/DD</option>
                            <option value='DD/MM/YYYY' >DD/MM/YYYY</option>
                            <option value='MM/DD/YYYY' >MM/DD/YYYY</option>
                            <option value='YYYY.MM.DD' >YYYY.MM.DD</option>
                            <option value='DD.MM.YYYY' >DD.MM.YYYY</option>
                            <option value='MM.DD.YYYY' >MM.DD.YYYY</option>
                            </select><br/>
    HEREDOC;

    $enterKey = '';

    $pleaseSelectLanguage = '';
    $pleaseSelectLanguage .= '<select id="cgReCaLang" name="upload['.$id.'][ReCaLang]">';
    $pleaseSelectLanguage .= "<option value='' >Please select language</option>";
    foreach($langOptions as $langKey => $lang){
        $pleaseSelectLanguage .= "<option value='$langKey' >$lang</option>";
    }
    $pleaseSelectLanguage .= '</select>';

    $captchaNote = "<span class='cg_recaptcha_test_note' ><span>NOTE:</span><br><b>Google reCAPTCHA test key</b> is provided from Google for testing purpose.
                                    <br><b>Create your own \"Site key\"</b> here <a href='https://www.google.com/recaptcha/admin' target='_blank'>www.google.com/recaptcha/admin</a><br>Register your site, create a <b>V2 \"I am not a robot\"</b>  key.</span>";

    $valueFieldTextareaContent = '';
    $editor_id = '';

    $fieldOrder = 0;

    $fieldOrder = $value->Field_Order;
    $fieldOrderKey = "$fieldOrder";
    $idKey = "$id";
    $hideChecked = "";

    if($id==$Field1IdGalleryView){$checked='checked';}
    else{$checked='';}

    $checkedSubTitle = false;
    $checkedThirdTitle = false;

    $Show_Slider = 0;

    if($Show_Slider==1){$checkedShow_Slider='checked';}
    else{$checkedShow_Slider='';}

    $IsForWpPageTitle = 0;
    if($IsForWpPageTitle==1){$checkedIsForWpPageTitle='checked';}
    else{$checkedIsForWpPageTitle='';}


    $IsForWpPageDescription = 0;
    if($IsForWpPageDescription==1){$checkedIsForWpPageDescription='checked';}
    else{$checkedIsForWpPageDescription='';}

    if($id==$Field2IdGalleryView){$checkedShowTag='checked';}
    else{$checkedShowTag='';}

    $checkedEcommerceTitle = "";
    $checkedEcommerceDescription = "";

    $checkedWatermark = "";
    $hiddenWatermarkSelect = "cg_hidden";
    $watermarkPosition = "top-left";
    $watermarkPositionTopLeftChecked = "";
    $watermarkPositionTopRightChecked = "";
    $watermarkPositionBottomLeftChecked = "";
    $watermarkPositionBottomRightChecked = "";
    $watermarkPositionCenterChecked = "";
    $watermarkPositionDisabled = "cg_disabled_watermark";
    if(!empty(trim($value->WatermarkPosition))){
        $watermarkPositionDisabled = "";
        $hiddenWatermarkSelect = "";
        $checkedWatermark = "checked='checked'";
        $watermarkPosition = $value->WatermarkPosition;
        if($watermarkPosition=='top-left'){
            $watermarkPositionTopLeftChecked = "selected";
        }elseif($watermarkPosition=='top-right'){
            $watermarkPositionTopRightChecked = "selected";
        }elseif($watermarkPosition=='bottom-left'){
            $watermarkPositionBottomLeftChecked = "selected";
        }elseif($watermarkPosition=='bottom-right'){
            $watermarkPositionBottomRightChecked = "selected";
        }elseif($watermarkPosition=='center'){
            $watermarkPositionCenterChecked = "selected";
        }
    }

    $WpAttachmentDetailsType = '';
    $WpAttachmentDetailsTypeAltChecked = '';
    $WpAttachmentDetailsTypeTitleChecked = '';
    $WpAttachmentDetailsTypeCaptionChecked = '';
    $WpAttachmentDetailsTypeDescriptionChecked = '';
    if(!empty(trim($value->WpAttachmentDetailsType))){
        $WpAttachmentDetailsType = $value->WpAttachmentDetailsType;
        if($WpAttachmentDetailsType=='alt'){
            $WpAttachmentDetailsTypeAltChecked = "selected";
        }elseif($WpAttachmentDetailsType=='title'){
            $WpAttachmentDetailsTypeTitleChecked = "selected";
        }elseif($WpAttachmentDetailsType=='caption'){
            $WpAttachmentDetailsTypeCaptionChecked = "selected";
        }elseif($WpAttachmentDetailsType=='description'){
            $WpAttachmentDetailsTypeDescriptionChecked = "selected";
        }
    }

    // Formularfelder unserializen
    $fieldContent = unserialize($value->Field_Content);

    $valueFieldTitle = '';
    $valueFieldPlaceholder = '';
    $minChar = '';
    $maxChar = '';
    $requiredChecked = '';

    foreach($fieldContent as $key => $valueFieldContent){

        $valueFieldContent = contest_gal1ery_convert_for_html_output_without_nl2br($valueFieldContent);// because of possible textarea values do not use ..._without_nl2br

        if($key=='titel'){
            $valueFieldTitle = $valueFieldContent;
        }

        if($key=='content'){
            $valueFieldPlaceholder = $valueFieldContent;
        }

        if($key=='min-char'){
            $minChar = $valueFieldContent;
        }

        if($key=='max-char'){
            $maxChar = $valueFieldContent;
        }

        if($key=='mandatory'){

            $requiredChecked = ($valueFieldContent=='on') ? "checked" : "";

            //$nfCount++;
            //$nfHiddenCount++;

        }

    }

    $isOnlyPlaceHolder = true;

    $RowNumberCount = 1;// in case of first time processing after update to 27.0.0

    include (__DIR__.'/upload-fields-left/upload-check-agreement-left.php');

    include (__DIR__.'/upload-fields-left/upload-date-left.php');

    include (__DIR__.'/upload-fields-left/upload-input-left.php');

    include (__DIR__.'/upload-fields-left/upload-url-left.php');

    include (__DIR__.'/upload-fields-left/upload-email-left.php');

    include (__DIR__.'/upload-fields-left/upload-textarea-left.php');

    include (__DIR__.'/upload-fields-left/upload-html-left.php');

    //include (__DIR__.'/fields/htmlf-left.php');// ht not works if this is inserted, no clue where it comes from

    include (__DIR__.'/upload-fields-left/upload-simple-captcha-left.php');

    include (__DIR__.'/upload-fields-left/upload-google-captcha-left.php');

    include (__DIR__.'/upload-fields-left/upload-select-left.php');

    include (__DIR__.'/upload-fields-left/upload-radio-left.php');

    include (__DIR__.'/upload-fields-left/upload-check-left.php');

    include (__DIR__.'/upload-fields-left/upload-select-categories-left.php');

    $isOnlyPlaceHolder = false;

    echo "</div>";

}



echo "<div id='cgUplEditFields'>
<div id='cgUplEditFieldsText'>Select fields to edit</div>
<div  id='cgUplEditFieldsToTheRightIcon'></div>
</div>";

echo "<div id='cgCreateUploadSortableArea' class='cg_sortable_area'>";

foreach ($selectFormInput as $key => $value) {
    if($value->Field_Type == 'image-f'){
        include (__DIR__.'/upload-fields-left/upload-image-left.php');
    }
}

$RowNumberCount = 1;// in case of first time processing after update to 27.0.0

foreach ($selectFormInput as $key => $value) {

    if($value->Field_Type == 'check-f'){// AGREEMENT FIELD
        include (__DIR__.'/upload-fields-left/upload-check-agreement-left.php');
    }

    if($value->Field_Type == 'date-f'){
        include (__DIR__.'/upload-fields-left/upload-date-left.php');
    }

    if($value->Field_Type == 'text-f'){
        include (__DIR__.'/upload-fields-left/upload-input-left.php');
    }

    if($value->Field_Type == 'url-f'){
        include (__DIR__.'/upload-fields-left/upload-url-left.php');
    }

    if($value->Field_Type == 'email-f'){
        include (__DIR__.'/upload-fields-left/upload-email-left.php');
    }

    if($value->Field_Type == 'comment-f'){
        include (__DIR__.'/upload-fields-left/upload-textarea-left.php');
    }

    if($value->Field_Type == 'html-f'){
        include (__DIR__.'/upload-fields-left/upload-html-left.php');
    }

    if($value->Field_Type == 'htmlf-f'){
        include (__DIR__.'/upload-fields-left/upload-htmlf-left.php');
    }

    if($value->Field_Type == 'caRo-f'){
        include (__DIR__.'/upload-fields-left/upload-simple-captcha-left.php');
    }

    if($value->Field_Type == 'caRoRe-f'){
        include (__DIR__.'/upload-fields-left/upload-google-captcha-left.php');
    }

    if($value->Field_Type == 'select-f'){
        include (__DIR__.'/upload-fields-left/upload-select-left.php');
    }

    if($value->Field_Type == 'radio-f'){
        include (__DIR__.'/upload-fields-left/upload-radio-left.php');
    }

    if($value->Field_Type == 'chk-f'){
        include (__DIR__.'/upload-fields-left/upload-check-left.php');
    }

    if($value->Field_Type == 'selectc-f'){
        include (__DIR__.'/upload-fields-left/upload-select-categories-left.php');
    }

    $RowNumberCount++;

}

echo "</div>";
