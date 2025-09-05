<?php

if($isOnlyPlaceHolder){
    $fieldTitle = 'Title';
    $kfRightCount = 0;
    $requiredChecked = '';
    $fieldPlaceholder = '';
    $cg_hide_upload_field = "";
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

    $kfRightCount = $value->kfCount;
    $cg_el_saved = "cg_el_saved";
}

$colId = 'kf';

    echo "<div class='cg_row_col cg_el cg_drag_drop $cg_hide_upload_field $cg_el_saved' id='right$kfRightCount'  data-cg-field='$colId'>";
            echo "<div class='cg_upl_title $requiredChecked' >";
                echo $fieldTitle;
            echo "</div>";
            echo "<div class='cg_upl_content' >";
                echo "<textarea class='cg_upl_placeholder'>$fieldPlaceholder</textarea>";
            echo "</div>";
        echo "<div class='cg_upl_del' title='Delete field' >";
        echo "</div>";
    echo "</div>";

if(!$isOnlyPlaceHolder){
    $kfRightCount++;
}

