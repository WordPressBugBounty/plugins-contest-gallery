<?php

if(!$isOnlyPlaceHolder){
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
    $selectFormInput[$key]->htCount = $htCount;
    $RowNumber = $selectFormInput[$key]->RowNumber;
    $ColNumber = $selectFormInput[$key]->ColNumber;
    $RowCols = $selectFormInput[$key]->RowCols;
}else{
    $RowNumber = $RowNumberCount;
    $ColNumber = 1;
    $RowCols = 1;
}

// Anfang des Formularteils
echo "<div id='$htCount'  class='formField htmlField cg_hide'><input type='hidden' name='upload[$id][type]' value='ht'>";
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

        if($key=='titel'){
            $valueFieldContent = contest_gal1ery_convert_for_html_output_without_nl2br($valueFieldContent);// because of possible textarea values do not use ..._without_nl2br
            $valueFieldTitle = $valueFieldContent;
        }

        if($key=='content'){
            $editor_id = "htmlFieldTemplate$htCount";
            $valueFieldContent = contest_gal1ery_convert_for_html_output_without_nl2br($valueFieldContent);
            $valueFieldPlaceholder = $valueFieldContent;
        }
    }

    $htCount++;
    $htHiddenCount++;
}

echo <<<HEREDOC
<div class="cg_view_options_row cg_view_options_row_title cg_view_options_row_collapse" title="Collapse" style="background-image:unset;" >
            <div class="cg_view_options_row_marker cg_hide"><div class="cg_view_options_row_marker_title">Title</div><div class="cg_view_options_row_marker_content"></div></div>
        <div class="cg_view_option cg_border_top_none cg_border_left_none cg_view_option_not_disable cg_border_bottom_none cg_view_option_100_percent">
            <div class="cg_view_option_title cg_view_option_title_header">
                                <p>HTML<br><span class="cg_view_option_title_note">This HTML will be visible in upload form</span></p>
            </div>
        </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_100_percent  cg_border_bottom_none cg_view_option_flex_flow_column cg_hide'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Title</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input class="cg_view_option_input_field_title"  type="text" name="upload[$id][title]" value='$valueFieldTitle' size="30">
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
     <div class='cg_view_option cg_view_option_100_percent  cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>HTML part visibile in frontend</p>
        </div>
        <div class="cg_view_option_html cg_view_option_input_full_width" >
               <div class='cgCheckAgreementHtml cg-wp-editor-container' data-wp-editor-id='$editor_id'>
                    <textarea class='cg-wp-editor-template' id='$editor_id' name='upload[$id][content]'>$valueFieldContent</textarea>
                </div>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_pointer_events_none cg_view_option_50_percent cg_border_right_none'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>&nbsp;</p>
        </div>
    </div>
     <div class='cg_view_option cg_view_option_hide_upload_field cg_view_option_50_percent '>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Hide<br><span class="cg_view_option_title_note"><b>NOTE:</b> will not be visible in upload form</span></p>
        </div>
        <div class="cg_view_option_checkbox" >
              <input type="checkbox" name="upload[$id][hide]" $hideChecked>
        </div>
    </div>
</div>
HEREDOC;

echo "</div>";
echo "</div>";


            