<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $caRoRightCount = 0;
    $requiredChecked = '';
    $cg_hide_upload_field = "";
    $fieldPlaceholder = '';
    $cg_el_saved = "";
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

    $caRoRightCount = $value->caRoCount;
    $cg_el_saved = "cg_el_saved";

}

$colId = 'caRo';
$requiredChecked = 'cg_upl_required';


    echo "<div class='cg_row_col cg_el cg_drag_drop $cg_hide_upload_field $cg_el_saved' title='Edit field' id='right$caRoRightCount' data-cg-field='$colId' draggable='true'
        ondragstart='cgJsClassAdmin.createUpload.dragAndDrop.drag(event)' ondragend='cgJsClassAdmin.createUpload.dragAndDrop.dragEnd(event)' 
 >";
            echo "<div class='cg_upl_title $requiredChecked' >";
                echo $fieldTitle;
            echo "</div>";
            echo "<div class='cg_upl_content' >";
                echo "<input type='checkbox'  />";
                echo "<div class='cg_upl_html'></div>";
            echo "</div>";
            echo "<div class='cg_upl_del' title='Delete field' >";
            echo "</div>";
    echo "</div>";

if(!$isOnlyPlaceHolder){
    $caRoRightCount++;
}
