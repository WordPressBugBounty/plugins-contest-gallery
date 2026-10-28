<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $kfRightCount = 0;
    $requiredChecked = '';
    $cg_hide_upload_field = "";
    $fieldPlaceholder = '';
    $cg_el_saved = "";
    $PinField = 0;
}else{
    $fieldTitle = cg_form_decode_value($value->Field_Name);
    $fieldPlaceholder = cg_form_decode_value($value->Field_Content);
    $requiredChecked = ($value->Required==1) ? "cg_upl_required" : "";
    $PinField = $value->PinField;
    if($value->Active==0){
        $cg_hide_upload_field = "cg_hide_upload_field";
    }else{
        $cg_hide_upload_field = "";
    }
    $kfRightCount = $value->kfCount;
    $cg_el_saved = "cg_el_saved";
}

$colId = 'kf';

    echo "<div class='cg_row_col cg_el cg_drag_drop $cg_hide_upload_field $cg_el_saved' id='right$kfRightCount'  data-cg-field='$colId'>";
            echo "<div class='cg_upl_title $requiredChecked' >";
                echo cg_form_escape_value($fieldTitle);
            echo "</div>";
if($PinField){
    echo "<div class='cg_pin_field' title='Visible in [cg_users_pin] form' >";
    echo "</div>";
}
            echo "<div class='cg_upl_content' >";
                echo "<textarea class='cg_upl_placeholder' placeholder='".cg_form_escape_value($fieldPlaceholder)."'></textarea>";
            echo "</div>";
        echo "<div class='cg_upl_del' title='Delete field' >";
        echo "</div>";
    echo "</div>";
