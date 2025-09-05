<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $dtRightCount = 0;
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

    $dtRightCount = $value->dtCount;
    $cg_el_saved = "cg_el_saved";

}
$colId = 'dt';

    echo "<div class='cg_row_col cg_el cg_drag_drop $cg_hide_upload_field $cg_el_saved' title='Edit field' id='right$dtRightCount' data-cg-field='$colId' draggable='true'
        ondragstart='cgJsClassAdmin.createUpload.dragAndDrop.drag(event)' ondragend='cgJsClassAdmin.createUpload.dragAndDrop.dragEnd(event)' 
 >";
            echo "<div class='cg_upl_title $requiredChecked' >";
                echo $fieldTitle;
            echo "</div>";
            echo "<div class='cg_upl_content' >";
                echo "<input class='cg_upl_placeholder' placeholder='$fieldPlaceholder' type='text' >";
            echo "</div>";
            echo "<div class='cg_upl_del' title='Delete field' >";
            echo "</div>";
    echo "</div>";

if(!$isOnlyPlaceHolder){
    $dtRightCount++;
}
