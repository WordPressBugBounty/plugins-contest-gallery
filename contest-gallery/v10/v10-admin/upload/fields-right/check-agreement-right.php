<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $cbRightCount = 0;
    $requiredChecked = '';
    $cg_hide_upload_field = "";
    $fieldPlaceholder = '';
    $cg_el_saved = "";
    $valueFieldTextareaContent = "";
}else{
    $fieldContent = unserialize($value->Field_Content);
    $fieldTitle = '';
    $fieldPlaceholder = '';
    foreach($fieldContent as $key => $valueFieldContent){
        $valueFieldContent = contest_gal1ery_convert_for_html_output($valueFieldContent);
        if($key=='titel'){
            $fieldTitle = $valueFieldContent;
        }
        if($key=='content'){
            $fieldPlaceholder = $valueFieldContent;
            $valueFieldContent = contest_gal1ery_convert_for_html_output_without_nl2br($valueFieldContent);
            $valueFieldTextareaContent = $valueFieldContent;
        }
        if($key=='mandatory'){
            $requiredChecked = ($valueFieldContent=='on') ? "cg_upl_required" : "";
        }
    }
    if($value->Active==0){
        $cg_hide_upload_field = "cg_hide_upload_field";
    }else{
        $cg_hide_upload_field = "";
    }

    $cbRightCount = $value->cbCount;
    $cg_el_saved = "cg_el_saved";

}

$colId = 'cb';
$requiredChecked = 'cg_upl_required';

    echo "<div class='cg_row_col cg_el cg_drag_drop $cg_hide_upload_field $cg_el_saved' title='Edit field' id='right$cbRightCount' data-cg-field='$colId' draggable='true'
        ondragstart='cgJsClassAdmin.createUpload.dragAndDrop.drag(event)' ondragend='cgJsClassAdmin.createUpload.dragAndDrop.dragEnd(event)' 
 >";
            echo "<div class='cg_upl_title $requiredChecked' >";
                echo cg1l_sanitize_method($fieldTitle);
            echo "</div>";
            echo "<div class='cg_upl_content' >";
                echo "<input type='checkbox'  />";
                echo "<div class='cg_upl_html'>$valueFieldTextareaContent</div>";
            echo "</div>";
            echo "<div class='cg_upl_del' title='Delete field' >";
            echo "</div>";
    echo "</div>";

if(!$isOnlyPlaceHolder){
    $cbRightCount++;
}
