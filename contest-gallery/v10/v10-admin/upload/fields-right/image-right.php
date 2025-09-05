<?php

$fieldContent = unserialize($value->Field_Content);
$imgTitle = '';
foreach($fieldContent as $key => $valueFieldContent){
    if($key=='titel'){
        $imgTitle = contest_gal1ery_convert_for_html_output($valueFieldContent);
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
                echo $imgTitle;
            echo "</div>";
        echo "</div>";
        echo "<div class='cg_upl_img_note' ><b>NOTE:</b> always at the top</div>";
    echo "</div>";
echo "</div>";

