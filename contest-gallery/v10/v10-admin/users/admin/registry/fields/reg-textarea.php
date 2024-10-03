<?php

$Field_Name = 'Textarea';

if(!$isOnlyPlaceHolder){
    $fieldOrder = $value->Field_Order;
    $Min_Char = $value->Min_Char;
    $Max_Char = 1000;
    $Field_Name = contest_gal1ery_convert_for_html_output($value->Field_Name);
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
    $kfCount++;
    $kfHiddenCount++;

}

// Anfang des Formularteils
echo "<div id='cg$kfCount'  class='formField regTextareaField'>";
echo "<div class='cg_remove' title='Remove field' data-cg-id='$id'></div>";
echo "<div class='cg_drag_area' ><img class='cg_drag_area_icon' src='$cgDragIcon'></div>";

echo "<input type='hidden' class='Field_Type' name='Field_Type[$i]' value='user-comment-field'>";
echo "<input type='hidden' class='Field_Order' value='$Field_Order' >";
echo "<input type='hidden' class='Field_Id' name='Field_Id[$i]' value='$id' >";

echo "<div class='formFieldInnerDiv'>";

echo <<<HEREDOC
<div class="cg_view_options_row cg_view_options_row_title cg_view_options_row_collapse" title="Collapse">
        <div class="cg_view_options_row_marker cg_hide"><div class="cg_view_options_row_marker_title">Field title</div><div class="cg_view_options_row_marker_content"></div></div>
        <div class="cg_view_option cg_view_option_header cg_view_option_not_disable cg_border_bottom_none cg_view_option_100_percent">
            <div class="cg_view_option_title cg_view_option_title_header">
                <p>Textarea</p>
            </div>
        </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_33_percent   cg_border_right_none cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Field title</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input type="text" class="Field_Name cg_view_option_input_field_title" name="Field_Name[$i]" value='$Field_Name' size="30">
        </div>
    </div>
     <div class='cg_view_option cg_view_option_67_percent  cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Textarea placeholder</p>
        </div>
        <div class="cg_view_option_input_full_width" >
            <textarea class="Field_Content" name='Field_Content[$i]' maxlength='10000' style='width:100%;' placeholder='Placeholder'  rows='6' >$Field_Content</textarea>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_50_percent  cg_border_right_none cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Min char</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input type="text" class="Min_Char" name="Min_Char[$i]" value="$Min_Char" size="30">
        </div>
    </div>
     <div class='cg_view_option cg_view_option_50_percent  cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Max char</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input type="text" class="Max_Char"  name="Max_Char[$i]" value="$Max_Char" size="30">
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_50_percent cg_border_right_none '>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Required</p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" class="necessary-check" name="Necessary[$i]" $cg_Necessary_checked>
        </div>
    </div>
     <div class='cg_view_option cg_view_option_hide_upload_field cg_view_option_50_percent '>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Hide<br><span class="cg_view_option_title_note"><b>NOTE:</b> Will not be visible in registration form</span></p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" class="necessary-hide" name="Hide[$i]" $hideChecked>
        </div>
    </div>
</div>
HEREDOC;

echo "</div>";
echo "</div>";



