<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $chkRightCount = 0;
    $requiredChecked = '';
    $cg_hide_upload_field = "";
    $checkArray = [];
    $cg_el_saved = "";
    $PinField = 0;
}else{
    $fieldTitle = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Name);
    $checkArray = [];
    if(!empty(trim($value->Field_Content))){
        $checkArray = cg_get_array_from_multiple_line_textarea($value->Field_Content);
    }
    $PinField = $value->PinField;
    $requiredChecked = ($value->Required==1) ? "cg_upl_required" : "";
    $cg_hide_upload_field = ($value->Active==0) ? "cg_hide_upload_field" : "";

    $chkRightCount = $value->chkCount;
    $cg_el_saved = "cg_el_saved";

}

$colId = 'chk';
$addOrShowCheckButtons = '<div class="cg_upl_add_check">No checkboxes buttons added</div>';
if(!empty($checkArray)){
    $addOrShowCheckButtons = '';
    foreach($checkArray as $key => $checkButton){
        if(empty($addOrShowCheckButtons)){
            $addOrShowCheckButtons = '<div class="cg_upl_check_button">
<div class="cg_upl_check_button_text">'.$checkButton.'</div>
<input type="checkbox" />
</div>';
        }else{
            $addOrShowCheckButtons .= '<div class="cg_upl_check_button">
<div class="cg_upl_check_button_text">'.$checkButton.'</div>
<input type="checkbox" />
</div>';
        }
    }
}

    echo "<div class='cg_row_col cg_el cg_drag_drop $cg_hide_upload_field $cg_el_saved' title='Edit field' id='right$chkRightCount' data-cg-field='$colId' draggable='true'
        ondragstart='cgJsClassAdmin.createUpload.dragAndDrop.drag(event)' ondragend='cgJsClassAdmin.createUpload.dragAndDrop.dragEnd(event)' 
 >";
        echo "<div class='cg_upl_title $requiredChecked' >";
            echo cg1l_sanitize_method($fieldTitle);
        echo "</div>";
if($PinField){
    echo "<div class='cg_pin_field' title='Visible in [cg_users_pin] form' >";
    echo "</div>";
}
        echo "<div class='cg_upl_content cg_upl_content_check' >";
        echo $addOrShowCheckButtons;
        echo "</div>";
        //echo "<div class='cg_upl_content_note' ><b>NOTE:</b> Translation for \"Please select\" can be found in \"Edit translations\"</div>";
        echo "<div class='cg_upl_del' title='Delete field' >";
        echo "</div>";
    echo "</div>";

if(!$isOnlyPlaceHolder){
    $chkRightCount++;
}
