<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $raRightCount = 0;
    $requiredChecked = 'cg_upl_required';
    $cg_hide_upload_field = "";
    $radioArray = [];
    $cg_el_saved = "";
    $PinField = 0;
}else{
    $fieldTitle = contest_gal1ery_convert_for_html_output_without_nl2br($value->Field_Name);
    $radioArray = [];
    if(!empty(trim($value->Field_Content))){
        $radioArray = cg_get_array_from_multiple_line_textarea($value->Field_Content);
    }
    $PinField = $value->PinField;
    $requiredChecked = ($value->Required==1) ? "cg_upl_required" : "cg_upl_required";
    $cg_hide_upload_field = ($value->Active==0) ? "cg_hide_upload_field" : "";

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
        echo "<div class='cg_upl_title $requiredChecked' >";
            echo cg1l_sanitize_method($fieldTitle);
        echo "</div>";
if($PinField){
    echo "<div class='cg_pin_field' title='Visible in [cg_users_pin] form' >";
    echo "</div>";
}
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
