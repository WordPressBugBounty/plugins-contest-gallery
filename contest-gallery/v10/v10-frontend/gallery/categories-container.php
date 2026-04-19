<?php

$cg_cat_checkbox_checked = 'cg_cat_checkbox_checked';

if($ShowCatsUnchecked==1){
    $cg_cat_checkbox_checked = 'cg_cat_checkbox_unchecked';
}

$heredoc = <<<HEREDOC
<div id="cgCatSelectAreaContainer$galeryIDuserForJs" class="cg-cat-select-area-container $cgFeControlsStyle cg-cat-select-area-show-less-available">
<div id="cgCatSelectArea$galeryIDuserForJs" class="cg-cat-select-area $cgFeControlsStyle cg-cat-select-area-show-less-available cg-cat-select-area-show-unfolded" style="padding-left:0;">
       <label class="$cg_cat_checkbox_checked cg_select_cat_label cg_hover_effect">
            <div class="cg_skeleton"></div>
            <span class="cg_select_cat" ></span>
            <span class="cg_select_cat_check_icon"></span>
       </label>
</div>
    <div class="cg-cat-select-area-show-more cg_hide" data-cg-tooltip="$language_ShowAllCategories"></div>
    <div class="cg-cat-select-area-show-less cg_hide"  data-cg-tooltip="$language_ShowLessCategories"></div>
</div>

HEREDOC;

echo $heredoc;

?>
