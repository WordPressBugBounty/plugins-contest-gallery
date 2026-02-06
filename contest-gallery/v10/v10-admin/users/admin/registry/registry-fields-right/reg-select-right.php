<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $seRightCount = 0;
    $requiredChecked = '';
    $cg_hide_upload_field = "";
    $fieldPlaceholder = '';
    $cg_el_saved = "";
    $PinField = 0;
}else{
    $fieldTitle = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Name);
    $fieldPlaceholder = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Content);
    $requiredChecked = ($value->Required==1) ? "cg_upl_required" : "";
    $PinField = $value->PinField;
    if($value->Active==0){
        $cg_hide_upload_field = "cg_hide_upload_field";
    }else{
        $cg_hide_upload_field = "";
    }
    $seRightCount = $value->seCount;
    $cg_el_saved = "cg_el_saved";
}

$colId = 'se';

    echo "<div class='cg_row_col cg_el cg_drag_drop $cg_hide_upload_field $cg_el_saved' title='Edit field' id='right$seRightCount' data-cg-field='$colId' draggable='true'
        ondragstart='cgJsClassAdmin.createUpload.dragAndDrop.drag(event)' ondragend='cgJsClassAdmin.createUpload.dragAndDrop.dragEnd(event)' 
 >";
            echo "<div class='cg_upl_title $requiredChecked' >";
                echo cg1l_sanitize_method($fieldTitle);
            echo "</div>";
if($PinField){
    echo "<div class='cg_pin_field' title='Visible in [cg_users_pin] form' >";
    echo "</div>";
}
            echo "<div class='cg_upl_content' >";
                //echo "<input class='cg_upl_placeholder' placeholder='$fieldPlaceholder' type='text' >";
                echo "<select><option>Please select</option></select>";
            echo "</div>";
            echo "<div class='cg_upl_content_note' ><b>NOTE:</b> Translation for \"Please select\" can be found in \"Edit translations\"</div>";
            echo "<div class='cg_upl_del' title='Delete field' >";
            echo "</div>";
    echo "</div>";

if(!$isOnlyPlaceHolder){
    $seRightCount++;
}
