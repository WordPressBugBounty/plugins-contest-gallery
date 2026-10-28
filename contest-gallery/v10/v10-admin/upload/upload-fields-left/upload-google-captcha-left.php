<?php

if(!$isOnlyPlaceHolder){
    $ReCaKey = $value->ReCaKey;
    $ReCaLang = $value->ReCaLang;
    $fieldOrder = $value->Field_Order;
    $fieldOrderKey = "$fieldOrder";
    $id = $value->id; // Unique ID des Form Feldes
    $idKey = "$id";
    if($value->Active==0){
        $hideChecked = "checked='checked'";
    }
    else{
        $hideChecked = "";
    }
    $selectFormInput[$key]->caRoReCount = $caRoReCount;
    $RowNumber = $selectFormInput[$key]->RowNumber;
    $ColNumber = $selectFormInput[$key]->ColNumber;
    $RowCols = $selectFormInput[$key]->RowCols;
}else{
    $RowNumber = $RowNumberCount;
    $ColNumber = 1;
    $RowCols = 1;
}

$valueFieldTitle = 'I am not a robot';

// Anfang des Formularteils
echo "<div id='$caRoReCount'  class='formField captchaRoReField cg_hide'><input type='hidden' name='upload[$id][type]' value='caRoRe'>";
echo "<div class='cg_remove cg_hide' title='Remove field' data-cg-id='$id'></div>";
echo "<div class='cg_drag_area cg_hide' ><img class='cg_drag_area_icon' src='$cgDragIcon'></div>";

echo "<input type='hidden' class='fieldOrder' name='upload[$id][order]' value='$fieldOrder'>";
echo "<input type='hidden' class='RowNumber' name='upload[$id][RowNumber]' value='$RowNumber'>";
echo "<input type='hidden' class='ColNumber' name='upload[$id][ColNumber]' value='$ColNumber'>";
echo "<input type='hidden' class='RowCols' name='upload[$id][RowCols]' value='$RowCols'>";
echo "<div class='formFieldInnerDiv'>";

echo "<input type='hidden' value='$fieldOrder' class='fieldnumber'>";

if(!$isOnlyPlaceHolder){
    if($id==$Field1IdGalleryView){$checked='checked';}
    else{$checked='';}

    if($id==$Field2IdGalleryView){$checkedShowTag='checked';}
    else{$checkedShowTag='';}

// Formularfelder unserializen
    $fieldContent = unserialize($value->Field_Content);

    $valueFieldTitle = '';
    $valueFieldPlaceholder = '';
    $minChar = '';
    $maxChar = '';
    $requiredChecked = '';

    foreach($fieldContent as $key => $valueFieldContent){

        $valueFieldContent = cg_form_editor_value($valueFieldContent);// because of possible textarea values do not use ..._without_nl2br

        if($key=='titel'){

            $valueFieldTitle = $valueFieldContent;

            $pleaseSelectLanguage = '';

            $pleaseSelectLanguage .= '<select id="cgReCaLang" name="upload['.$id.'][ReCaLang]">';

            $pleaseSelectLanguage .= "<option value='' >Please select language</option>";

            foreach($langOptions as $langKey => $lang){

                if($ReCaLang==$langKey){
                    $pleaseSelectLanguage .= "<option value='$langKey' selected>$lang</option>";
                }else{
                    $pleaseSelectLanguage .= "<option value='$langKey' >$lang</option>";
                }
            }

            $pleaseSelectLanguage .= '</select>';

            $enterKey = '';
            $enterKey .= "<div style='display:flex;flex-direction:column;width:100%;'><input type='text' name='upload[$id][ReCaKey]' class='cg_reca_key' placeholder='Example Key: 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI' size='30' maxlength='1000' value='".cg_form_escape_value($ReCaKey)."'/>";// Titel und Delete M�glichkeit die oben bestimmt wurde

            $captchaNote = cg_captcha_setup_note();

        }

        if($key=='content'){
            $valueFieldPlaceholder = $valueFieldContent;
        }

        if($key=='mandatory'){
            $requiredChecked = ($valueFieldContent=='on') ? "checked" : "";


        }

    }
    $caRoReHiddenCount++;

}else{

    $ReCaKey = '';
    $ReCaLang = '';

    $pleaseSelectLanguage = '';

    $pleaseSelectLanguage .= '<select id="cgReCaLang" name="upload['.$id.'][ReCaLang]">';

    $pleaseSelectLanguage .= "<option value='' >Please select language</option>";

    foreach($langOptions as $langKey => $lang){

        if($ReCaLang==$langKey){
            $pleaseSelectLanguage .= "<option value='$langKey' selected>$lang</option>";
        }else{
            $pleaseSelectLanguage .= "<option value='$langKey' >$lang</option>";
        }
    }

    $pleaseSelectLanguage .= '</select>';

    $enterKey = '';
    $enterKey .= "<div style='display:flex;flex-direction:column;width:100%;'><input type='text' name='upload[$id][ReCaKey]' class='cg_reca_key' placeholder='Example Key: 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI' size='30' maxlength='1000' value='".cg_form_escape_value($ReCaKey)."'/>";// Titel und Delete M�glichkeit die oben bestimmt wurde

    $captchaNote = cg_captcha_setup_note();

}


$enterKey .= cg_captcha_secret_input('upload',!$isOnlyPlaceHolder ? $id : 0,$ReCaKey,'upload['.$id.'][ReCaSecret]');
$enterKey .= "<span class='cg_recaptcha_icon' style='margin:16px 0 10px;text-align:center;'>Insert Google reCAPTCHA test keys</span></div>";

echo <<<HEREDOC
<div id="cgEditCaptchaRoReFieldField"  class="cg_view_options_row cg_view_options_row_title cg_view_options_row_collapse" title="Collapse">
        <div class="cg_view_option cg_view_option_not_disable cg_border_top_none cg_border_left_none cg_border_bottom_none cg_view_option_full_width cg_view_option_flex_flow_column">
            <div class="cg_view_option_title cg_view_option_title_header">
                <p>Google reCAPTCHA<br>I am not a robot</p>
            </div>
            <div class="cg_view_option_title" style="justify-content: center;padding-bottom: 0;">
                  <span class="cg_view_option_title_note"><b>NOTE:</b> <span class="cg_note_text">(each form is verified separately)</span></span>
            </div>
        </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
      <div class='cg_view_option cg_view_option_100_percent   cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Select CAPTCHA language<br><span class="cg_view_option_title_note">Google provides the widget text. All reCAPTCHA widgets on the same page share the language of the first loaded widget.</span></p>
        </div>
        <div class="cg_view_option_select cg_view_option_input_full_width" >
            $pleaseSelectLanguage
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
     <div class='cg_view_option cg_view_option_100_percent  cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Your site key</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            $enterKey
        </div>
         <div class='cg_view_option_title cg_view_option_title_full_width' style="justify-content: center; margin-bottom: 5px;">
            $captchaNote
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_pointer_events_none cg_view_option_50_percent cg_border_right_none'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Required<br>
                  <span class="cg_view_option_title_note"><b>NOTE:</b> <span class="cg_note_text">Confirmation is required while this field is enabled</span></span>
            </p>
        </div>
    </div>
     <div class='cg_view_option cg_view_option_hide_upload_field cg_view_option_50_percent '>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Hide<br><span class="cg_view_option_title_note"><b>NOTE:</b> <span class="cg_note_text">Hides this field and disables reCAPTCHA verification for this upload form</span></span></p>
        </div>
        <div class="cg_view_option_checkbox" >
              <input type="checkbox" name="upload[$id][hide]" $hideChecked>
        </div>
    </div>
</div>
HEREDOC;

echo "</div>";
echo "</div>";


            