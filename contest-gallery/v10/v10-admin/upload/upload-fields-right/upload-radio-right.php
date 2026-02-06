<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $raRightCount = 0;
    $requiredChecked = 'cg_upl_required';
    $cg_hide_upload_field = "";
    $radioArray = [];
    $cg_el_saved = "";
}else{
    $fieldContent = unserialize($value->Field_Content);
    $fieldTitle = '';
    $radioArray = [];
    foreach($fieldContent as $key => $valueFieldContent){
        $valueFieldContentProcessed = contest_gal1ery_convert_for_html_output_without_nl2br($valueFieldContent);
        if($key=='titel'){
            $fieldTitle = $valueFieldContentProcessed;
        }
        if($key=='content'){
            if(!empty(trim($valueFieldContent))){
                $radioArray = cg_get_array_from_multiple_line_textarea($valueFieldContent);
            }
        }
        if($key=='mandatory'){
            $requiredChecked = ($valueFieldContentProcessed=='on') ? "cg_upl_required" : "cg_upl_required";
        }
    }
    if($value->Active==0){
        $cg_hide_upload_field = "cg_hide_upload_field";
    }else{
        $cg_hide_upload_field = "";
    }

    $raRightCount = $value->raCount;
    $cg_el_saved = "cg_el_saved";

}
$colId = 'ra';
$addOrShowRadioButtons = '<div class="cg_upl_add_radio">No radio buttons added</div>';
if(!empty($radioArray)){
    $addOrShowRadioButtons = '';
    foreach($radioArray as $key => $radioButton){
        if(empty($addOrShowRadioButtons)){
            $addOrShowRadioButtons = '<div class="cg_upl_radio_button">
<div class="cg_upl_radio_button_text">'.$radioButton.'</div>
<input type="radio" />
</div>';
        }else{
            $addOrShowRadioButtons .= '<div class="cg_upl_radio_button">
<div class="cg_upl_radio_button_text">'.$radioButton.'</div>
<input type="radio" />
</div>';
        }
    }
}

    echo "<div class='cg_row_col cg_el cg_drag_drop $cg_hide_upload_field $cg_el_saved' title='Edit field' id='right$raRightCount' data-cg-field='$colId' draggable='true'
        ondragstart='cgJsClassAdmin.createUpload.dragAndDrop.drag(event)' ondragend='cgJsClassAdmin.createUpload.dragAndDrop.dragEnd(event)' 
 >";
            echo "<div class='cg_upl_title $requiredChecked' style='margin-bottom:3px;' >";
                echo cg1l_sanitize_method($fieldTitle);
            echo "</div>";
            echo "<div class='cg_upl_content cg_upl_content_radio' >";
                echo $addOrShowRadioButtons;
            echo "</div>";
            //echo "<div class='cg_upl_content_note' ><b>NOTE:</b> Translation for \"Please select\" can be found in \"Edit translations\"</div>";
            echo "<div class='cg_upl_del' title='Delete field' >";
            echo "</div>";
    echo "</div>";

if(!$isOnlyPlaceHolder){
    $raRightCount++;
}
