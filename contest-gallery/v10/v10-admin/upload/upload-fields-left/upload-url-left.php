<?php

$FieldTitleGallery = '';

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
    $selectFormInput[$key]->urlCount = $urlCount;
    $RowNumber = $selectFormInput[$key]->RowNumber;
    $ColNumber = $selectFormInput[$key]->ColNumber;
    $RowCols = $selectFormInput[$key]->RowCols;
}else{
    $RowNumber = $RowNumberCount;
    $ColNumber = 1;
    $RowCols = 1;
}
$valueFieldTitle = 'URL';

echo "<div id='$urlCount'  class='formField urlField cg_hide'><input type='hidden' name='upload[$id][type]' value='url'>";
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

    $rowUrl = $wpdb->get_row("SELECT * FROM $tablename_form_input WHERE id = '$id'");
	$FieldTitleGallery = $rowUrl->FieldTitleGallery;

    if($rowUrl->Show_Slider==1){$checkedShow_Slider='checked';}
    else{$checkedShow_Slider='';}

    if($rowUrl->ForwardToUrl==1){$checkedForwardToUrl='checked';}
    else{$checkedForwardToUrl='';}

    if($rowUrl->ForwardToUrlNewTab==1){$checkedForwardToUrlNewTab='checked';}
    else{$checkedForwardToUrlNewTab='';}

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

        $valueFieldContent = contest_gal1ery_convert_for_html_output_without_nl2br($valueFieldContent);// because of possible textarea values do not use ..._without_nl2br

        if($key=='titel'){
            $valueFieldTitle = $valueFieldContent;
        }

        if($key=='content'){
            $valueFieldPlaceholder = $valueFieldContent;
        }

        if($key=='mandatory'){

            $requiredChecked = ($valueFieldContent=='on') ? "checked" : "";


        }

    }

    $urlCount++;
    $urlHiddenCount++;

}

echo <<<HEREDOC
<div id="cgEditUrlField" class="cg_view_options_row cg_view_options_row_title cg_view_options_row_collapse" title="Collapse">
            <div class="cg_view_options_row_marker cg_hide"><div class="cg_view_options_row_marker_title">Title</div><div class="cg_view_options_row_marker_content"></div></div>
        <div class="cg_view_option cg_border_top_none cg_border_left_none cg_view_option_not_disable cg_border_bottom_none cg_view_option_100_percent">
            <div class="cg_view_option_title cg_view_option_title_header">
                <p>URL</p>
            </div>
        </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
   <div  class='cg_view_option  cg_view_option_100_percent   cg_border_bottom_none cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Title</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input  class="cg_view_option_input_field_title"  type="text" name="upload[$id][title]" value='$valueFieldTitle' size="30">
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
   <div class='cg_view_option  cg_view_option_100_percent cg_border_bottom_none  cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Placeholder</p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input class="cg_title_placeholder" type="text" name="upload[$id][content]" value='$valueFieldPlaceholder' size="30">
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_100_percent cg_border_bottom_none    cg_view_option_flex_flow_column'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Title entry view<br>
            <span class="cg_view_option_title_note">
            	<b>NOTE:</b> if set will be displayed in entry view instead of "Title"
			</span>
            </p>
        </div>
        <div class="cg_view_option_input cg_view_option_input_full_width" >
            <input type="text" name="upload[$id][FieldTitleGallery]" value='$FieldTitleGallery' size="30">
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_50_percent cg_border_right_none cg_view_option_required '>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Required</p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" name="upload[$id][required]" $requiredChecked>
        </div>
    </div>
     <div class='cg_view_option cg_view_option_hide_upload_field cg_view_option_50_percent '>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Hide<br><span class="cg_view_option_title_note"><b>NOTE:</b> will not be visible in upload form</span></p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" name="upload[$id][hide]" $hideChecked>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
     <div  class='cg_view_option cg_view_option_100_percent cg_border_top_none cg_border_bottom_none '>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Show as info in single entry view</p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" name="upload[$id][infoInSlider]" $checkedShow_Slider>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_100_percent cg_border_bottom_none  $cgProFalse'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Forward to the URL by click on an entry in masonry view</p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" name="upload[$id][ForwardToUrl]" $checkedForwardToUrl>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_100_percent cg_border_bottom_none $cgProFalse'>
        <div class='cg_view_option_title cg_view_option_title_full_width '>
            <p>Forward in new tab</p>
        </div>
        <div class="cg_view_option_checkbox">
              <input type="checkbox" name="upload[$id][ForwardToUrlNewTab]" $checkedForwardToUrlNewTab>
        </div>
    </div>
</div>
HEREDOC;


echo "</div>";
echo "</div>";
