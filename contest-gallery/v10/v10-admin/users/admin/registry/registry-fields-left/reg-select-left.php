<?php

$Field_Name = 'Select';

if(!$isOnlyPlaceHolder){
    $fieldOrder = $value->Field_Order;
    $Min_Char = $value->Min_Char;
    $Max_Char = $value->Max_Char;
    $Field_Name = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Name);
    $Field_Content = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content);
    $Field_Order = $value->Field_Order;
    $Field_Type = $value->Field_Type;
    $cg_Necessary = $value->Required;
    $id = $value->id; // Unique ID des Form Feldes
    $idKey = "$id";
    if($cg_Necessary==1){$cg_Necessary_checked="checked";}
    else{$cg_Necessary_checked="";}
    if($value->Active==0){
        $hideChecked = "checked='checked'";
    }
    else{
        $hideChecked = "";
    }
    if($value->PinField==1){
        $pinFieldChecked = "checked='checked'";
    }
    else{
        $pinFieldChecked = "";
    }
    $selectFormInput[$key]->seCount = $seCount;

    $RowNumber = $selectFormInput[$key]->RowNumber;
    $ColNumber = $selectFormInput[$key]->ColNumber;
    $RowCols = $selectFormInput[$key]->RowCols;
}else{
    $RowNumber = $RowNumberCount;
    $ColNumber = 1;
    $RowCols = 1;
}

// Anfang des Formularteils
echo "<div id='$seCount'  class='formField cg_hide regSelectField'>";
echo "<div class='cg_remove cg_hide' title='Remove field' data-cg-id='$id'></div>";
echo "<div class='cg_drag_area cg_hide' ><img class='cg_drag_area_icon' src='$cgDragIcon'></div>";

echo "<input type='hidden' class='Field_Type' name='Field_Type[$i]' value='user-select-field'>";
echo "<input type='hidden' class='Field_Order' value='$Field_Order' >";
echo "<input type='hidden' class='Field_Id' name='Field_Id[$i]' value='$id' >";

echo "<input type='hidden' class='RowNumber' name='RowNumber[$i]' value='$RowNumber'>";
echo "<input type='hidden' class='ColNumber' name='ColNumber[$i]' value='$ColNumber'>";
echo "<input type='hidden' class='RowCols' name='RowCols[$i]' value='$RowCols'>";

if(!$isOnlyPlaceHolder){

    $seCount++;
    $seHiddenCount++;

}

echo "<div class='formFieldInnerDiv'>";

echo <<<HEREDOC
<div id="cgEditSelectField" class="cg_view_options_row cg_view_options_row_title cg_view_options_row_collapse" title="Collapse">
        <div class="cg_view_options_row_marker cg_hide"><div class="cg_view_options_row_marker_title">Title</div><div class="cg_view_options_row_marker_content"></div></div>
        <div class="cg_view_option cg_border_left_top_none cg_view_option_header cg_view_option_not_disable cg_border_bottom_none cg_view_option_100_percent">
            <div class="cg_view_option_title cg_view_option_title_header">
                <p>Select</p>
            </div>
        </div>
</div>
HEREDOC;


echo <<<HEREDOC
<div class='cg_view_options_row'>
     <div class='cg_view_option cg_border_bottom_none cg_view_option_100_percent cg_pin_field_check'>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Show in [cg_users_pin] form<span class="cg_view_option_pin"></span><br><span class="cg_view_option_title_note"><b>NOTE:</b> will be visible in user form for PIN verification</span></p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" class="cg_pin_field" name="PinField[$i]" $pinFieldChecked>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_100_percent  cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Title</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input type="text" class="Field_Name cg_view_option_input_field_title" name="Field_Name[$i]" value='$Field_Name' size="30">
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
     <div class='cg_view_option cg_view_option_100_percent  cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Select values</p>
        </div>
        <div class="cg_view_option_input_full_width" >
            <textarea class="Field_Content cg_title_placeholder" name='Field_Content[$i]' maxlength='10000' style='width:100%;' placeholder='Each row one value - Example: &#10;value1&#10;value2&#10;value3&#10;value4&#10;value5&#10;value6'  rows='6' >$Field_Content</textarea>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_required cg_view_option_50_percent cg_border_right_none '>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Required</p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" class="necessary-check" name="Necessary[$i]" $cg_Necessary_checked>
        </div>
    </div>
     <div class='cg_view_option cg_view_option_hide_upload_field cg_view_option_50_percent '>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Hide<br><span class="cg_view_option_title_note"><b>NOTE:</b> will not be visible in registration form</span></p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" class="necessary-hide" name="Hide[$i]" $hideChecked>
        </div>
    </div>
</div>
HEREDOC;

echo "</div>";
echo "</div>";


