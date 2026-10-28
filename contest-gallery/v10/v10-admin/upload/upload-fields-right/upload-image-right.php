<?php

$fieldContent = unserialize($value->Field_Content);
$imgTitle = '';
foreach($fieldContent as $key => $valueFieldContent){
    if($key=='titel'){
        $imgTitle = cg_form_decode_value($valueFieldContent);
    }
}


$requiredChecked = "";

// because of older versions before 20.0, before always required
if(!isset($fieldContent['mandatory'])){
    $requiredChecked = "cg_upl_required";
}

$bhRightCount = $value->bhCount;

echo "<div class='cg_row cg_image' >";
    echo "<div class='cg_row_col cg_el' id='right$bhRightCount'>";
        echo "<div class='cg_upl_img' >";
            echo "<div class='cg_upl_img_title $requiredChecked' >";
                echo cg_form_escape_value($imgTitle);
            echo "</div>";
        echo "</div>";
        echo "<div class='cg_upl_img_note' ><b>NOTE:</b> always at the top</div>";
    echo "</div>";
echo "</div>";

