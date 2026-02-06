<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $chkRightCount = 0;
    $requiredChecked = '';
    $cg_hide_upload_field = "";
    $checkArray = [];
    $cg_el_saved = "";
}else{
    $fieldContent = unserialize($value->Field_Content);
    $fieldTitle = '';
    $checkArray = [];
    foreach($fieldContent as $key => $valueFieldContent){
        $valueFieldContentProcessed = contest_gal1ery_convert_for_html_output_without_nl2br($valueFieldContent);
        if($key=='titel'){
            $fieldTitle = $valueFieldContentProcessed;
        }
        if($key=='content'){
            if(!empty(trim($valueFieldContent))){
                $checkArray = cg_get_array_from_multiple_line_textarea($valueFieldContent);
            }
        }
        if($key=='mandatory'){
            $requiredChecked = ($valueFieldContentProcessed=='on') ? "cg_upl_required" : "";
        }
    }
    if($value->Active==0){
        $cg_hide_upload_field = "cg_hide_upload_field";
    }else{
        $cg_hide_upload_field = "";
    }

    $chkRightCount = $value->chkCount;
    $cg_el_saved = "cg_el_saved";

}
$colId = 'chk';
$addOrShowCheckButtons = '<div class="cg_upl_add_check">No checkboxes added</div>';
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
            echo "<div class='cg_upl_title $requiredChecked' style='margin-bottom:3px;' >";
                echo cg1l_sanitize_method($fieldTitle);
            echo "</div>";
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
