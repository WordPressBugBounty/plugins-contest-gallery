<?php

$cgHideFurtherImagesDiv = '';
if(!empty($entryId)){
    $cgHideFurtherImagesDiv = 'cg_hide';
}

echo <<<HEREDOC
<div id="cgFurtherImagesAndTopControlsContainerDiv$galeryIDuserForJs" class='$cgHideFurtherImagesDiv cg_further_images_and_top_controls_container $cgFeControlsStyle'>
<div id="cgFurtherImagesContainerDiv$galeryIDuserForJs" class='cg_further_images_container $cgFeControlsStyle'>
HEREDOC;

$pics_per_page = intval($options['general']['PicsPerSite']);
$totalPagesNumber = ceil($imagesFullDataOriginalLength/$pics_per_page);
$currentPageNumber = cgl_get_current_page_number();
if($currentPageNumber>$totalPagesNumber){
    $currentPageNumber = $totalPagesNumber;
}
$tooltip = "Select page";
$start = ($currentPageNumber-1)*$pics_per_page+1;
$end = ($currentPageNumber)*$pics_per_page;
if($end>$imagesFullDataOriginalLength){
    $end = $imagesFullDataOriginalLength;
}
$paginationGalleryId = 0;
$hasPaginationGalleriesOriginContext = (
    !empty($isFromGalleriesSelect) ||
    !empty($is_from_single_view_for_cg_galleries) ||
    !empty($isMultiGalleryContext) ||
    !empty($cglHasExplicitFromGalleriesPage) ||
    (!empty($backToGalleriesFromPageNumber) && intval($backToGalleriesFromPageNumber) > 1)
);
if(!empty($hasPaginationGalleriesOriginContext)){
    if(!empty($cglMultiContextGalleryId)){
        $paginationGalleryId = absint($cglMultiContextGalleryId);
    }elseif(!empty($realGid)){
        $paginationGalleryId = absint($realGid);
    }elseif(!empty($galeryID)){
        $paginationGalleryId = absint($galeryID);
    }
}
$previousPageNumber = $currentPageNumber-1;
$previousPageUrl = (!empty($paginationGalleryId))
    ? cgl_build_multi_gallery_page_url($currentUrl,$paginationGalleryId,$backToGalleriesFromPageNumber,$previousPageNumber)
    : cgl_build_gallery_page_url($currentUrl,$previousPageNumber);
$previousHref = "href='$previousPageUrl'";// extra for seo with href or href totally missing
if($previousPageNumber<1){
    $previousHref = 'style="pointer-events: none;"';// href totally missing for seo
}
$nextPageNumber = $currentPageNumber+1;
$nextPageUrl = (!empty($paginationGalleryId))
    ? cgl_build_multi_gallery_page_url($currentUrl,$paginationGalleryId,$backToGalleriesFromPageNumber,$nextPageNumber)
    : cgl_build_gallery_page_url($currentUrl,$nextPageNumber);
$nextPageHref = "href='$nextPageUrl'";
if($nextPageNumber>$totalPagesNumber){
    $nextPageHref = 'style="pointer-events: none;"';
}


// 1. Generate the options in advance
$optionsHtml = '';
for ($i = 1; $i <= $totalPagesNumber; $i++) {
    $selected = ($i == $currentPageNumber) ? ' selected="selected"' : '';
    $optionsHtml .= "<option class=\"cg_further_images_select_option\" data-cg-gid=\"{$galeryIDuserForJs}\" value=\"{$i}\"{$selected}>{$i}</option>";
}

echo <<<HTML
<div class="cg_further_images_select_div">
    <div class="cg_skeleton"></div>
    <select class="cg_hover_effect cg_further_images_select" data-cg-tooltip="{$tooltip}" data-cg-gid="{$galeryIDuserForJs}">
        {$optionsHtml}
    </select>
</div>
HTML;

echo <<<HEREDOC
    <span id="cgFurtherImagesContainerDivPositionHelper$galeryIDuserForJs" class='cg_further_images_container_position_helper'></span>
HEREDOC;

echo <<<HEREDOC
<div class="cg_hover_effect cg_further_images_select_nav_div">
    <div class="cg_skeleton"></div>
HEREDOC;
#toDo cg_further_images_select_nav_none CSS setzen in js auch setzen
echo <<<HEREDOC
    <a $previousHref ><div class="cg_further_images_select_nav_prev" data-cg-gid="$galeryIDuserForJs" data-cg-tooltip="$language_PreviousPage"></div></a>
HEREDOC;
echo <<<HEREDOC
    <div class="cg_further_images_select_nav_current">$start - $end</div>
    <div class="cg_further_images_select_nav_of_language">$language_of</div>
    <div class="cg_further_images_select_nav_total">$imagesFullDataOriginalLength</div>
HEREDOC;
echo <<<HEREDOC
    <a $nextPageHref ><div class="cg_further_images_select_nav_next" data-cg-gid="$galeryIDuserForJs" data-cg-tooltip="$language_NextPage"></div></a>
</div>
HEREDOC;

echo <<<HEREDOC
</div>
</div>
HEREDOC;

?>
