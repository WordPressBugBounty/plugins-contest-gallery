<?php

$Field_Name = 'Password';
$Field_Content = '';

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
    $RowNumber = $selectFormInput[$key]->RowNumber;
    $ColNumber = $selectFormInput[$key]->ColNumber;
    $RowCols = $selectFormInput[$key]->RowCols;
}else{
    $RowNumber = $RowNumberCount;
    $ColNumber = 1;
    $RowCols = 1;
}

// Anfang des Formularteils
echo "<div  id='cg_password'   class='formField cg_hide'>";
echo "<div class='cg_remove cg_hide title='Remove field' data-cg-id='$id'></div>";
echo "<div class='cg_drag_area' ><img class='cg_drag_area_icon' src='$cgDragIcon'></div>";

echo "<input type='hidden' class='Field_Type' name='Field_Type[$i]' value='password'>";
echo "<input type='hidden' class='Field_Order' value='$Field_Order' >";
echo "<input type='hidden' class='Field_Id' name='Field_Id[$i]' value='$id' >";

echo "<input type='hidden' class='RowNumber' name='RowNumber[$i]' value='$RowNumber'>";
echo "<input type='hidden' class='ColNumber' name='ColNumber[$i]' value='$ColNumber'>";
echo "<input type='hidden' class='RowCols' name='RowCols[$i]' value='$RowCols'>";

echo "<div class='formFieldInnerDiv'>";

$loginTranslationUrl = '?page='.cg_get_version().'/index.php&edit_options=true&cg_edit_translations=true&option_id='.$GalleryID.'&cg_go_to=cgTranslationLanguagePassword';

echo <<<HEREDOC
<div id="cgEditWpPasswordField" class="cg_view_options_row cg_view_options_row_title cg_view_options_row_collapse" title="Collapse">
        <div class="cg_view_options_row_marker cg_hide"><div class="cg_view_options_row_marker_title">Title</div><div class="cg_view_options_row_marker_content"></div></div>
        <div class="cg_view_option cg_border_left_top_none cg_view_option_header  cg_view_option_not_disable cg_border_bottom_none cg_view_option_full_width cg_view_option_flex_flow_column">
            <div class="cg_view_option_title cg_view_option_title_header">
                <p>WP-Password</p>
            </div>
            <div class="cg_view_option_title" style="justify-content: center;padding-bottom: 0;">
                  <span class="cg_view_option_title_note"><b>NOTE:</b>  (Login user password: Wordpress-Profile-Field)<br/>Login Form translation can be found <a href="$loginTranslationUrl" target="_blank">here...</a></span>
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
    <div  class='cg_view_option cg_view_option_100_percent cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Title</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input type="text" class="Field_Name cg_view_option_input_field_title" name="Field_Name[$i]" size="30" value='$Field_Name'>
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
     <div class='cg_view_option cg_view_option_100_percent  cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Placeholder</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input type="text"  class="Field_Content cg_title_placeholder" name="Field_Content[$i]" value='$Field_Content' size="30" placeholder=''  >
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
            <input type="text"  class="Min_Char"  name="Min_Char[$i]" value="$Min_Char" maxlength="4">
        </div>
    </div>
     <div class='cg_view_option cg_view_option_50_percent  cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Max char</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input type="text" class="Max_Char"  name="Max_Char[$i]" value="$Max_Char" maxlength="4">
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_50_percent cg_border_right_none'>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Required<br>
                  <span class="cg_view_option_title_note"><b>NOTE:</b> WP-Password is always required</span>
            </p>
        </div>
    </div>
    <div class='cg_view_option cg_view_option_hide_upload_field cg_view_option_50_percent '>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Hide<br><span class="cg_view_option_title_note"><b>NOTE:</b> WP-Password will be always displayed</span></p>
        </div>
    </div>
</div>
HEREDOC;

echo "</div>";
echo "</div>";



