<?php

// has to be here
echo "<div id='cgPdfPreviewGeneration' class='cg_hide' >";
echo "<input type='hidden' id='cgPdfPreviewsToCreateString' value='$PdfPreviewsToCreateString' >";
echo "<input type='hidden' id='cgOpenAiKeyOption' value='$OpenAiKey' >";
echo "<input type='hidden' id='cgNewWpUploadWhichReplaceForPdfPreview' value='$cgNewWpUploadWhichReplaceForPdfPreview' >";
echo "<input type='hidden' id='cgWpUploadToReplaceForPdfPreview' value='$cgWpUploadToReplaceForPdfPreview' >";
echo "<span>PDF preview creation in progress: <span  id='cgPdfPreviewProgress' >50%</span> ...</span><br>";
echo '<b>Do not leave and do not reload this page</b>';
echo '</div>';

// has to be here
echo "<div id='cgPdfGenerationFinished' class='cg_hide'>";
echo '<b>PDF creation finished</b>';
echo '</div>';

if($cgVersion<7){
    echo "<div style='width:100%;text-align:center;font-size:20px;'>";
    echo "<p style='font-size:16px;'>    
        <strong>Please create a new gallery</strong><br> Galleries created before update to version 7 have old logic and will not supported anymore.<br> You can also copy an old gallery.</p></div>";
}

$admin_url = admin_url();
$plugins_url = plugins_url();

echo "<div id='cgGalleryBackendMetaCard' class='cg_gallery_backend_meta_card".(!empty($categories) ? " cg_gallery_backend_meta_card_has_categories" : " cg_gallery_backend_meta_card_upload_only")."'>";
echo "<div class='cg_gallery_backend_meta_layout'>";
echo "<div class='cg_gallery_backend_meta_upload_column'>";

echo '<input type="hidden" id="cg_gallery_id" value="'. $GalleryID .'">';
echo '<input type="hidden" id="cg_admin_url" value="'. $admin_url .'">';

?>
<div class="cg_gallery_backend_upload_area">
    <!--<input type="number" value="" class="regular-text process_custom_images" id="process_custom_images" name="" max="10" min="1" step="10">-->
    <div class="cg_gallery_backend_upload_intro">
        <div class="cg_gallery_backend_upload_intro_title">Upload files and media</div>
        <div class="cg_gallery_backend_upload_intro_text">Add files, social embeds, or create images with OpenAI.</div>
    </div>
    <div id="cgAddImagesWpUploader">
        <button data-cg-gid="<?php echo $GalleryID; ?>" class="cg_upload_wp_images_button button cg_backend_button_gallery_action">Add files / Social embed / OpenAI</button>
        <span>Social embed: YouTube, Twitter, Instagram, TikTok</span>
    </div>
<?php

echo "<img src='".$plugins_url."/".cg_get_version()."/v10/v10-css/loading.gif' width='25px' class='cg_gallery_backend_uploading_gif' id='cg_uploading_gif'/>
      <div class='cg_gallery_backend_uploading_div' id='cg_uploading_div'>
      (adding files please wait)</div>";

echo "</div>";

if($cgVersion<7){
    echo "<div class='cg_gallery_backend_upload_legacy_note'>What happens when adding images?&nbsp;<a id='cg_adding_images_info'><u>Read here...</u></a></div>";
    ?>
    <div id="cg_adding_images_answer" style="position: absolute; margin-left: 40px; margin-top: 10px;width: 510px; background-color: white; border: 1px solid; padding: 5px; display: none;z-index:500;">
        Every image will be converted to five different resolutions. From 300pixel to 1920pixel width.
        <br>Depending on screen width a suitable image will be selected by algorithm.
        <br>It brings faster loading performance for frontend users viewing your gallery.
        <br><br>Converting images can take some time, especially for images higher then 3MB.
        <br>In general it is recommended not to add more then 10 images at one go. </div>

    <?php
}

echo "<div style='display:none;' id='cg_wp_upload_ids'></div>";
echo "<div id='cg_wp_upload_div'></div>";
echo "</div>";

if(!empty($categories)){

    echo "<div class='cg_gallery_backend_meta_categories_column'>";

// form start has to be done after get data!!!
    echo "<form id='cgCategoriesForm' action='?page=".cg_get_version()."/index.php' method='POST'>";

    // authentification
    echo "<input type='hidden' name='cgGalleryHash' value='".md5(wp_salt( 'auth').'---cngl1---'.$GalleryID)."'>";
    echo "<input type='hidden' name='GalleryID' value='$GalleryID'>";


    echo "<table style='$CatWidgetColor;' id='cgCatWidgetTable'>";
    echo "<tr>";
    // !IMPORTANT Has to be here for action call!!!!
    echo "<input type='hidden' name='action' value='post_cg_gallery_save_categories_changes'>";

    echo "<td class='cg_gallery_backend_categories_table_cell'>";

    echo '<div id="cgSaveCategoriesLoader" class="cg-lds-dual-ring-div-gallery-hide cg-lds-dual-ring-div-gallery-hide-mainCGallery cg_hide">
    <div class="cg-lds-dual-ring-gallery-hide cg-lds-dual-ring-gallery-hide-mainCGallery">
    </div>
</div>';

    //echo "<p id='cg_changes_saved_categories' style='font-size:18px;display:none;'><strong>Changes saved</strong></p>";
$editCategoriesLink = '<a id="cgEditGalleriesButton" href="?page='.cg_get_version().'/index.php&define_upload=true&option_id='.$GalleryID.'&cg_go_to=cgSelectCategoriesField">
<span>Edit categories</span></a>';
    echo "<div class='cg_gallery_backend_categories_header'>
<div class='cg_gallery_backend_categories_header_primary'>
$editCategoriesLink
</div>
<div class='cg_gallery_backend_categories_header_toggles'>
<label class='cg_gallery_backend_categories_toggle'><span>Show categories widget in frontend</span><input type='checkbox' name='CatWidget' id='CatWidget' value='on' $CatWidgetChecked /></label>
<label class='cg_gallery_backend_categories_toggle'><span>Show categories unchecked on page load</span><input type='checkbox' name='ShowCatsUnchecked' id='ShowCatsUnchecked' value='on' $ShowCatsUnchecked /></label>
<label class='cg_gallery_backend_categories_toggle cg_gallery_backend_show_cats_unfolded_toggle' style='display:none;'><span>Show categories unfolded</span><input type='checkbox' name='ShowCatsUnfolded' id='ShowCatsUnfolded' value='on' $ShowCatsUnfolded /></label>
</div>
</div>";
    echo "<input type='hidden' name='Category[Continue]'/>";

    echo "<div class='cg_gallery_backend_categories_note'><strong>NOTE:</strong> files of unchecked categories will be not displayed in frontend</div>";
    echo "<div id='cgCategoriesCheckContainer'>";

    $countCategories = count($categories);
    $counterCategories = 1;
    $counterCheckedCategories = 0;

    $activatedImagesCount = 0;

    foreach($categories as $category){

        $checked = '';
        $checkedClass = "class='cg_checked'";

        $imagesInCategoryCount = 0;

        if($category->Active==1){
            $counterCheckedCategories++;
            $checked = "checked='checked'";
            $checkedClass = '';
            foreach($countCategoriesObject as $categoryCountObject){

                if($categoryCountObject->Category == $category->id){
                    $categoryCountObject->Checked = true;
                }

            }
        }

        foreach($countCategoriesObject as $categoryCountObject){

            if($categoryCountObject->Category == $category->id){
                $getMethod = "Category".$categoryCountObject->Category;
                $imagesInCategoryCount = $categoryCountObject->$getMethod;
                $activatedImagesCount = $activatedImagesCount + $imagesInCategoryCount;
            }

        }

        $imagesInCategoryCountColor = 'green';

        if($imagesInCategoryCount == 0){
            $imagesInCategoryCountColor = 'red';
        }

        echo "<div class='cg_category_checkbox_images_area' >".$category->Name." (<span style='color:$imagesInCategoryCountColor;'>$imagesInCategoryCount</span>): <input class='cg-categories-check' data-cg-category-id='".$category->id."' data-cg-images-in-category-count='".$imagesInCategoryCount."' type='checkbox' name='Category[]' $checkedClass $checked value='".$category->id."' style='height:16px;width:16px;'/></div>";

        if($counterCategories==$countCategories){

            $checked = '';
            $checkedClass = "class='cg_checked'";

            if($ShowOther==1){
                $counterCheckedCategories++;
                $checked = "checked='checked'";
                $checkedClass = '';
            }

            $imagesInCategoryCount = 0;
            foreach($countCategoriesObject as $categoryCountObject){

                if($categoryCountObject->Category === '0'){
                    if($ShowOther==1){
                        $categoryCountObject->Checked = true;
                    }
                    $getMethod = "Category".$categoryCountObject->Category;
                    $imagesInCategoryCount = $categoryCountObject->$getMethod;
                    $activatedImagesCount = $activatedImagesCount + $imagesInCategoryCount;

                }

                }

            $imagesInCategoryCountColor = 'green';

            if($imagesInCategoryCount == 0){
                $imagesInCategoryCountColor = 'red';
            }

            echo "<div class='cg_category_checkbox_images_area' >Other (<span style='color:$imagesInCategoryCountColor;'>$imagesInCategoryCount</span>): <input type='checkbox' class='cg-categories-check'  data-cg-category-id='0' data-cg-images-in-category-count='".$imagesInCategoryCount."' name='Category[ShowOther]' value='1' $checkedClass $checked style='height:16px;width:16px;'/></div> ";
        }

        $counterCategories++;

    }

    $totalCountActiveImages = 0;

    foreach($countCategoriesObject as $categoryCountObject){

        if(!empty($categoryCountObject->Checked)){
            $getMethod = "Category".$categoryCountObject->Category;
            $totalCountActiveImages = $totalCountActiveImages+intval($categoryCountObject->$getMethod);
        }

    }

    $totalCountActiveImagesStyleColor = 'green';

    if($totalCountActiveImages==0){
        $totalCountActiveImagesStyleColor = 'red';
    }
                
    echo "<input type='hidden' id='cgActivatedImagesCount' value='$activatedImagesCount' />";

    echo '<div class="cg_category_checkbox_images_area cg_gallery_backend_categories_footer">
<div class="cg_gallery_backend_categories_total">
<div>Total activated files shown in frontend:</div>
<div id="cgCategoryTotalActiveImagesValue" style="color:'.$totalCountActiveImagesStyleColor.';">'.$totalCountActiveImages.'</div>
</div>
<div class="cg_gallery_backend_categories_save">
<span class="cg_save_categories_form cg_image_action_href"><span class="cg_backend_button_gallery_action">Save categories changes</span></span>
</div>
                </div>';
    echo "</div>";

cg_total_images_shown_in_frontend_zero();

    echo "</td>";

    echo "</tr>";

    echo "</table>";
    echo "</form>";
    echo "</div>";
}

echo "</div>";
echo "</div>";

if(!$cgProFalse){
	cg_move_to_another_gallery_container($GalleryID);
}

cg_backend_gallery_render_reload_entry_loader();

cg_ask_if_should_be_removed_from_sale_if_multiple();

cg_preview_images_to_delete_container($GalleryID);

cg_social_input_container($GalleryID);

cg_openai_container($GalleryID,$cgProFalse);

cg_social_library($GalleryID);

cg_sort_gallery_files_container($GalleryID,$optionsSQL->Version);

if(!$cgProFalse){
	cg_attach_to_another_user_container($GalleryID);
}

cg_multiple_files_for_post_container();

cg_sell_ecommerce_container_warnings();

cg_service_keys_file_will_be_deleted();
cg_download_keys_file_will_be_deleted();
cg_download_and_service_keys_files_will_be_deleted();
cg_sell_ecommerce_entry_can_not_be_deleted();
cg_sell_ecommerce_entry_can_not_be_moved();

cg_sell_ecommerce_download_can_not_be_removed();

cg_sell_ecommerce_container($GalleryID, $ecommerceOptions,$cgProFalse);

cg_download_ecommerce_form($GalleryID, $ecommerceOptions);


cg1l_email_backend_template();

cg_mails_list();

cg1lCreateTemplateFromBody();

$assign_fields_png = plugins_url('/../../../v10/v10-css/assign-fields.png', __FILE__);

?>
    <script>
        cgJsClassAdmin.gallery.vars.galleryIDs = <?php echo json_encode($galleryIDs);?>;
        cgJsClassAdmin.gallery.vars.assign_fields_png = <?php echo json_encode($assign_fields_png);?>;
        cgJsClassAdmin.gallery.vars.categories = <?php echo json_encode($categories);?>;
        if(cgJsClassAdmin.gallery.vars.categories){
            var categoriesObject = {};
            cgJsClassAdmin.gallery.vars.categories.forEach(function (value,index){
                categoriesObject[value.id] = value.Name;
            });
            cgJsClassAdmin.gallery.vars.categories = categoriesObject;
        }else{
            cgJsClassAdmin.gallery.vars.categories = {};
        }

        cgJsClassAdmin.gallery.vars.upload_form_inputs = <?php echo json_encode($upload_form_inputs);?>;

        var newInputsObjectByOrder = {};

        cgJsClassAdmin.gallery.vars.upload_form_inputs.forEach(function (value,index){
            newInputsObjectByOrder[value.Field_Order] = value;
        });

        cgJsClassAdmin.gallery.vars.upload_form_inputs = newInputsObjectByOrder;

    </script>
<?php

// form start has to be done after get data!!!
echo "<form id='cgGalleryForm' action='?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$start&edit_gallery=true' method='POST'>";

echo "<input type='hidden' id='cgPdfPreviewBackend' value='$PdfPreviewBackend'>";
echo "<input type='hidden' id='cgWpUploadToReplace' name='cgWpUploadToReplace' value=''>";
echo "<input type='hidden' id='cgNewWpUploadWhichReplace' name='cgNewWpUploadWhichReplace' value=''>";

$totalCountActiveImagesForHiddenInput = 0;

if(!empty($categories)){
    $totalCountActiveImagesForHiddenInput = $totalCountActiveImages;
}

// !IMPORTANT Has to be here if form submit!!!
echo "<input type='hidden' id='cgTotalCountActiveImagesHiddenInput' value='$totalCountActiveImagesForHiddenInput' disabled>";


// !IMPORTANT Has to be here if form submit!!!
echo "<input type='hidden' id='cgGalleryFormSubmit' name='cgGalleryFormSubmit' value='1' disabled>";

// this check is needed otherwise message will be shown that no images found
if(!empty($isNewGalleryCreated)){
    echo "<input type='hidden' id='cgIsNewGalleryCreated' value='1'>";
}


// !IMPORTANT Has to be here for action call!!!!
echo "<input type='hidden' name='action' value='post_cg_gallery_view_control_backend'>";

// Check if steps were changed then reset to 0
echo "<input type='hidden' id='cgStepsChanged' name='cgStepsChanged' disabled value='1'>";

// authentification
echo "<input type='hidden' name='cgGalleryHash' value='".md5(wp_salt( 'auth').'---cngl1---'.$GalleryID)."'>";

// real gallery id mitschicken
echo "<input type='hidden' name='cg_id' id='cgBackendGalleryId' value='".$GalleryID."'>";

// where to start
echo "<input type='hidden' id='cgStartValue' name='cg_start' value='$start'>";

// how many to show
echo "<input type='hidden' id='cgStepValue' name='cg_step' value='$step'>";

// what order
echo "<input type='hidden' id='cgOrderValue' name='cg_order' value='$order'>";
echo "<input type='hidden' id='cgAllowRating' value='$AllowRating'>";

// image link value has to be loaded at the beginning!
echo "<input type='hidden' id='cg_rating_star_on' value='$starOn' >";
echo "<input type='hidden' id='cg_rating_star_off' value='$starOff' >";

if($isAjaxCall){
    echo "<input type='hidden' id='cg_files_count_total' value='$rows' >";
}else{
    echo "<input type='hidden' id='cg_files_count_total' value='0' >";
}

$cgVersionScripts = cg_get_version_for_scripts();

echo "<input type='hidden' name='cgVersionScripts' id='cgVersionScripts' value='$cgVersionScripts'>";

echo "<div class='cgViewControl $cg_hide_is_new_gallery' id='cgViewControl'>";

echo "<div id='cg_view_control_top'>";

echo "<div class='cg_order'>";

$orderByAverage = '';

$orderByAverageWithManip = '';

$orderByRatingOneStarWithManip = '';
$orderByRatingMultipleStarsWithManip = '';

if($Manipulate==1){
    $orderByRatingOneStarWithManip = '<option value="rating_desc_with_manip" id="cg_rating_desc_with_manip">Rating descend with manipulation</option>
            <option value="rating_asc_with_manip" id="cg_rating_asc_with_manip">Rating ascend with manipulation</option>';
    $orderByRatingMultipleStarsWithManip = '<option value="rating_desc_with_manip" id="cg_rating_desc_with_manip">Rating quantity (amount of votes) descend with manipulation</option>
            <option value="rating_asc_with_manip" id="cg_rating_asc_with_manip">Rating quantity (amount of votes) ascend with manipulation</option>';
}

// since point based system sorting by average is deprecated
/*if(($AllowRating==1 OR ($AllowRating >= 12 AND $AllowRating <=20)) && $checkTablenameIPentries>=1){
    $orderByAverage = '<option value="rating_desc_average" id="cg_rating_desc_average">Rating average descend without manipulation (longer loading possible if many images or votes)</option>
        <option value="rating_asc_average" id="cg_rating_asc_average">Rating average ascend without manipulation (longer loading possible if many images or votes)</option>';
}*/

// since point based system sorting by average is deprecated
/*if(($AllowRating==1 OR ($AllowRating >= 12 AND $AllowRating <=20)) && $Manipulate==1 && $checkTablenameIPentries>=1){
    $orderByAverageWithManip = '<option value="rating_desc_average_with_manip" id="cg_rating_desc_average_with_manip">Rating average descend with manipulation (longer loading possible if many images or votes)</option>
        <option value="rating_asc_average_with_manip" id="cg_rating_asc_average_with_manip">Rating average ascend with manipulation (longer loading possible if many images or votes)</option>';
}*/

$orderBySum = '';
$orderBySumWithManip = '';

if(($AllowRating==1 OR ($AllowRating >= 12 AND $AllowRating <=20)) && $checkTablenameIPentries>=1){
    $orderBySum = '<option value="rating_desc_sum" id="cg_rating_desc_sum">Rating sum descend without manipulation (longer loading possible if many images or votes)</option>
        <option value="rating_asc_sum" id="cg_rating_asc_sum">Rating sum ascend without manipulation (longer loading possible if many images or votes)</option>';
}
if(($AllowRating==1 OR ($AllowRating >= 12 AND $AllowRating <=20)) && $Manipulate==1 && $checkTablenameIPentries>=1){
    $orderBySumWithManip = '<option value="rating_desc_sum_with_manip" id="cg_rating_desc_sum_with_manip">Rating sum descend with manipulation (longer loading possible if many images or votes)</option>
        <option value="rating_asc_sum_with_manip" id="cg_rating_asc_sum_with_manip">Rating sum ascend with manipulation (longer loading possible if many images or votes)</option>';
}

//$selectFormInput = $wpdb->get_results( "SELECT id, Field_Type, Field_Order, Field_Content FROM $tablename_f_input WHERE GalleryID = '$GalleryID' AND (Field_Type = 'check-f' OR Field_Type = 'text-f' OR Field_Type = 'comment-f' OR Field_Type ='email-f' OR Field_Type ='select-f'  OR Field_Type ='selectc-f' OR Field_Type ='url-f' OR Field_Type ='date-f') ORDER BY Field_Order ASC" );

$selectFormInputOptGroup = '';

if(count($selectFormInput)){
    foreach($selectFormInput as $selectFormInputRow){

        if($selectFormInputRow->Field_Type == 'text-f'){

            if(empty($selectFormInputOptGroup)){
                $selectFormInputOptGroup .= '<optgroup label="Custom fields" id="cgOrderSelectCustomFields">';
            }

            $selectFormInputRowFieldContentUnserialized = unserialize($selectFormInputRow->Field_Content);
            $selectFormInputRowTitel = $selectFormInputRowFieldContentUnserialized["titel"];
            $selectFormInputOptGroup .= '<option value="cg_input_for_id_'.$selectFormInputRow->id.'_id_desc" id="cg_input_'.$selectFormInputRow->id.'_descend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' descend</option>';
            $selectFormInputOptGroup .= '<option value="cg_input_for_id_'.$selectFormInputRow->id.'_id_asc" id="cg_input_'.$selectFormInputRow->id.'_ascend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' ascend</option>';

        }

        if($selectFormInputRow->Field_Type == 'comment-f'){

            if(empty($selectFormInputOptGroup)){
                $selectFormInputOptGroup .= '<optgroup label="Custom fields" id="cgOrderSelectCustomFields">';
            }

            $selectFormInputRowFieldContentUnserialized = unserialize($selectFormInputRow->Field_Content);
            $selectFormInputRowTitel = $selectFormInputRowFieldContentUnserialized["titel"];
            $selectFormInputOptGroup .= '<option value="cg_textarea_for_id_'.$selectFormInputRow->id.'_id_desc" id="cg_textarea_'.$selectFormInputRow->id.'_descend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' descend</option>';
            $selectFormInputOptGroup .= '<option value="cg_textarea_for_id_'.$selectFormInputRow->id.'_id_asc" id="cg_textarea_'.$selectFormInputRow->id.'_ascend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' ascend</option>';

        }

        if($selectFormInputRow->Field_Type == 'select-f'){

            if(empty($selectFormInputOptGroup)){
                $selectFormInputOptGroup .= '<optgroup label="Custom fields" id="cgOrderSelectCustomFields">';
            }

            $selectFormInputRowFieldContentUnserialized = unserialize($selectFormInputRow->Field_Content);
            $selectFormInputRowTitel = $selectFormInputRowFieldContentUnserialized["titel"];
            $selectFormInputOptGroup .= '<option value="cg_select_for_id_'.$selectFormInputRow->id.'_id_desc" id="cg_select_'.$selectFormInputRow->id.'_descend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' descend</option>';
            $selectFormInputOptGroup .= '<option value="cg_select_for_id_'.$selectFormInputRow->id.'_id_asc" id="cg_select_'.$selectFormInputRow->id.'_ascend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' ascend</option>';

        }

        if($selectFormInputRow->Field_Type == 'selectc-f'){

            if(empty($selectFormInputOptGroup)){
                $selectFormInputOptGroup .= '<optgroup label="Custom fields" id="cgOrderSelectCustomFields">';
            }

            $selectFormInputRowFieldContentUnserialized = unserialize($selectFormInputRow->Field_Content);
            $selectFormInputRowTitel = $selectFormInputRowFieldContentUnserialized["titel"];
            $selectFormInputOptGroup .= '<option value="cg_categories_for_id_'.$selectFormInputRow->id.'_id_desc" id="cg_categories_'.$selectFormInputRow->id.'_descend" data-cg-input-fields-class="cg_select_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' descend</option>';
            $selectFormInputOptGroup .= '<option value="cg_categories_for_id_'.$selectFormInputRow->id.'_id_asc" id="cg_categories_'.$selectFormInputRow->id.'_ascend" data-cg-input-fields-class="cg_select_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' ascend</option>';

        }

        if($selectFormInputRow->Field_Type == 'email-f'){

            if(empty($selectFormInputOptGroup)){
                $selectFormInputOptGroup .= '<optgroup label="Custom fields" id="cgOrderSelectCustomFields">';
            }

            $selectFormInputRowFieldContentUnserialized = unserialize($selectFormInputRow->Field_Content);
            $selectFormInputRowTitel = $selectFormInputRowFieldContentUnserialized["titel"];
            $selectFormInputOptGroup .= '<option value="cg_email_registered_users_for_id_'.$selectFormInputRow->id.'_id_desc" id="cg_email_registered_users_'.$selectFormInputRow->id.'_descend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' descend (Registered users)</option>';
            $selectFormInputOptGroup .= '<option value="cg_email_registered_users_for_id_'.$selectFormInputRow->id.'_id_asc" id="cg_email_registered_users_'.$selectFormInputRow->id.'_ascend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' ascend (Registered users)</option>';
            $selectFormInputOptGroup .= '<option value="cg_email_unregistered_users_for_id_'.$selectFormInputRow->id.'_id_desc" id="cg_email_unregistered_users_'.$selectFormInputRow->id.'_descend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' descend (Unregistered users)</option>';
            $selectFormInputOptGroup .= '<option value="cg_email_unregistered_users_for_id_'.$selectFormInputRow->id.'_id_asc" id="cg_email_unregistered_users_'.$selectFormInputRow->id.'_ascend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' ascend (Unregistered users)</option>';

        }

        if($selectFormInputRow->Field_Type == 'date-f'){

            if(empty($selectFormInputOptGroup)){
                $selectFormInputOptGroup .= '<optgroup label="Custom fields" id="cgOrderSelectCustomFields">';
            }

            $selectFormInputRowFieldContentUnserialized = unserialize($selectFormInputRow->Field_Content);
            $selectFormInputRowTitel = $selectFormInputRowFieldContentUnserialized["titel"];
            $selectFormInputOptGroup .= '<option value="cg_date_for_id_'.$selectFormInputRow->id.'_id_desc" id="cg_date_'.$selectFormInputRow->id.'_descend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' descend</option>';
            $selectFormInputOptGroup .= '<option value="cg_date_for_id_'.$selectFormInputRow->id.'_id_asc" id="cg_date_'.$selectFormInputRow->id.'_ascend" data-cg-input-fields-class="cg_input_by_search_sort_'.$selectFormInputRow->id.'">'.$selectFormInputRowTitel.' ascend</option>';

        }

    }

}

if(!empty($selectFormInputOptGroup)){
    $selectFormInputOptGroup .= '</optgroup>';
}

$selectFurtherFieldsOptGroup = '<optgroup label="Further fields" id="cgOrderSelectFurtherFields">';
$selectFurtherFieldsOptGroup .= '<option value="cg_for_id_wp_username_desc" id="cg_for_id_wp_username_descend" data-cg-input-fields-class="cg_for_id_wp_username_by_search_sort">WP username descend</option>';
$selectFurtherFieldsOptGroup .= '<option value="cg_for_id_wp_username_asc" id="cg_for_id_wp_username_ascend" data-cg-input-fields-class="cg_for_id_wp_username_by_search_sort">WP username ascend</option>';
$selectFurtherFieldsOptGroup .= '</optgroup>';


$orderByRatingOneStarWithoutManip = '';
$orderByRatingMultipleStarsWithoutManip = '';

if($AllowRating==2){
    echo <<<HEREDOC
	<select id="cgOrderSelect">
		<optgroup label="General" id="cgOrderSelectGeneral">
            <option value="date_desc" id="cg_date_desc">Date descend</option>
            <option value="date_asc" id="cg_date_asc">Date ascend</option>
            <option value="rating_desc" id="cg_rating_desc">Rating descend without manipulation</option>
            <option value="rating_asc" id="cg_rating_asc">Rating ascend without manipulation</option>
            $orderByRatingOneStarWithManip
            <option value="comments_desc" id="cg_comments_desc">Comments descend</option>
            <option value="comments_asc" id="cg_comments_asc">Comments ascend</option>
		    <option value="custom" id="cg_custom">Custom</option>
        </optgroup>
            $selectFormInputOptGroup
            $selectFurtherFieldsOptGroup
	</select>
HEREDOC;
}elseif($AllowRating>=12 && $AllowRating<=20){
    echo <<<HEREDOC
	<select id="cgOrderSelect">
		<optgroup label="General" id="cgOrderSelectGeneral">
            <option value="date_desc" id="cg_date_desc">Date descend</option>
            <option value="date_asc" id="cg_date_asc">Date ascend</option>
            $orderBySum
            $orderBySumWithManip
            <option value="rating_desc" id="cg_rating_desc">Rating quantity (amount of votes) descend without manipulation</option>
            <option value="rating_asc" id="cg_rating_asc">Rating quantity (amount of votes) ascend without manipulation</option>
            $orderByRatingMultipleStarsWithManip
            <option value="comments_desc" id="cg_comments_desc">Comments descend</option>
            <option value="comments_asc" id="cg_comments_asc">Comments ascend</option>
            <option value="custom" id="cg_custom">Custom</option>
        </optgroup>
            $selectFormInputOptGroup
            $selectFurtherFieldsOptGroup
	</select>
HEREDOC;
}else{
    if($AllowRating==0){
        $orderByRatingMultipleStarsWithManip = '';
    }
    echo <<<HEREDOC
	<select id="cgOrderSelect">
		<optgroup label="General" id="cgOrderSelectGeneral">
            <option value="date_desc" id="cg_date_desc">Date descend</option>
            <option value="date_asc" id="cg_date_asc">Date ascend</option>
            $orderByRatingMultipleStarsWithManip
            <option value="comments_desc" id="cg_comments_desc">Comments descend</option>
            <option value="comments_asc" id="cg_comments_asc">Comments ascend</option>
            <option value="custom" id="cg_custom">Custom</option>
        </optgroup>
            $selectFormInputOptGroup
            $selectFurtherFieldsOptGroup
	</select>
HEREDOC;
}

$heredoc = <<<HEREDOC
	<span class="cg-info-icon"><strong>info</strong></span>
<span class="cg-info-container cg-info-container-gallery-order" style="display: none;">Custom fields can be added in "Edit upload form".
<br>Supported custom fields for sorting are:<br><b>Input, Textarea, Select, Select categories, Date, Email</b></span>
HEREDOC;
echo $heredoc;

?>

    <script>

        var gid = <?php echo json_encode($GalleryID);?>;
        var cgOrder_BG = localStorage.getItem('cgOrder_BG_'+gid);
        var cgOrderSelect = document.querySelector('#cgGalleryBackendContainer #cgOrderSelect');
        var cgOrderValue = document.querySelector('#cgGalleryForm #cgOrderValue');
        var isNewGalleryCreated = !!document.getElementById('cgIsNewGalleryCreated');

        if(isNewGalleryCreated){
            cgOrder_BG = 'date_desc';
            localStorage.setItem('cgOrder_BG_'+gid, cgOrder_BG);
        }

        if(cgOrder_BG){
            // fallback to go sure if old order options are activated
            if(cgOrder_BG=='rating_desc_average' || cgOrder_BG=='rating_asc_average' || cgOrder_BG=='rating_desc_average_with_manip' || cgOrder_BG=='rating_asc_average_with_manip'){
                cgOrder_BG = 'date_desc';
            }
            if(cgOrderSelect){
                cgOrderSelect.value = cgOrder_BG;
            }
            if(cgOrderValue){
                cgOrderValue.value = cgOrder_BG;
            }
        }

    </script>

<?php


echo "</div>";

echo "<div id='cg_sort_files_div'>";

?>
        <div style="margin-left: 20px; display: flex; align-items: center; justify-content: center;font-weight:bold;" id="cg_sort_files_form_button" class="cg_backend_button_gallery_action cg_hide">Sort entries</div>
<?php
echo "</div>";


echo "<div id='cgPicsPerSite' class='cg_pics_per_site'>";
echo"<div class='cg_show_all_pics_text'>&nbsp;&nbsp;Show pics per Site:</div>";
echo "<div data-cg-step-value='10' class='cg_hover_effect cg_step' id='cg_step_10'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=10&start=$i&edit_gallery=true\">10</a></div>";
echo "<div data-cg-step-value='20' class='cg_hover_effect cg_step' id='cg_step_20'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=20&start=$i&edit_gallery=true\">20</a></div>";
echo "<div data-cg-step-value='30' class='cg_hover_effect cg_step' id='cg_step_30'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=30&start=$i&edit_gallery=true\">30</a></div>";
if($max_input_vars>=2000){
    echo "<div data-cg-step-value='50' class='cg_hover_effect cg_step' id='cg_step_50'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=50&start=$i&edit_gallery=true\">50</a></div>";
}
?>

    <script>

        var gid = <?php echo json_encode($GalleryID);?>;
        var cgStep_BG = localStorage.getItem('cgStep_BG_'+gid);

        if(!cgStep_BG){
            cgStep_BG = 10;
        }

        var id = 'cg_step_'+cgStep_BG;
        var el = document.getElementById(id);
        el.classList.add('cg_step_selected');

    </script>

<?php

echo "</div>";

echo "</div>";


echo "<div style='margin-top: 18px;' class='cg_search'>";

echo "<span id='cgSearchInputSpan'><input id='cgSearchInput' placeholder='search' name='cg_search' value='$search'>
<span id='cgSearchInputButton' class='cg_hide'>Search</span>
<span id='cgSearchInputClose' class='cg_hide'>X</span>
</span>";

$checkCookieIdOrIP = '';

/*if($pro_options->RegUserUploadOnly=='2'){
    $checkCookieIdOrIP = ", Cookie ID";
}elseif($pro_options->RegUserUploadOnly=='3'){
    $checkCookieIdOrIP = ", IP";
}*/

echo '<span class="cg-info-icon"><strong>info</strong></span>
    <span class="cg-info-container cg-info-container-gallery-user" style="display: none;margin-top: 55px !important;margin-left: 180px !important;">Search by fields content, categories,
file name, entry id, EXIF data (if available), user email, user nickname'.$checkCookieIdOrIP.'<br><br><strong>Pay attention!<br>Database queries with searched value takes longer then without searched value</strong></span>';
?>

    <script>

        var gid = <?php echo json_encode($GalleryID);?>;
        var cgSearch_BG = localStorage.getItem('cgSearch_BG_'+gid);

        if(cgSearch_BG){
            var id = 'cgSearchInput';
            var el = document.getElementById(id);
            if(el){
                el.value  = cgSearch_BG;
                el.classList.add('cg_searched_value');
            }
            var id = 'cgSearchInputClose';
            var el = document.getElementById(id);

            if(el){
                el.classList.remove('cg_hide');
            }

            var id = 'cgSearchInputButton';
            var el = document.getElementById(id);
            if(el){el.classList.remove('cg_hide');}

        }else{
            var id = 'cgSearchInput';
            var el = document.getElementById(id);
            if(el){el.classList.remove('cg_searched_value');}

            var id = 'cgSearchInputClose';
            var el = document.getElementById(id);
            if(el){el.classList.add('cg_hide');}

            var id = 'cgSearchInputButton';
            var el = document.getElementById(id);
            if(el){el.classList.add('cg_hide');}

        }

    </script>

<?php


echo "</div>";


echo "<div id='cgShowOnlyWinners' class='cg_show_only'>";
echo "<label class='cg_show_only_label' for='cgShowOnlyWinnersCheckbox'><span class='cg_show_only_text'>Show winners only:</span><input type='checkbox' id='cgShowOnlyWinnersCheckbox' name='cg_show_only_winners' value='true' /></label>";
echo "</div>";

echo "<div id='cgShowOnlyActive' class='cg_show_only'>";
echo "<label class='cg_show_only_label' for='cgShowOnlyActiveCheckbox'><span class='cg_show_only_text'>Show active only:</span><input type='checkbox' id='cgShowOnlyActiveCheckbox' name='cg_show_only_active' value='true' /></label>";
echo "</div>";

echo "<div id='cgShowOnlyInactive' class='cg_show_only'>";
echo "<label class='cg_show_only_label' for='cgShowOnlyInactiveCheckbox'><span class='cg_show_only_text'>Show inactive only:</span><input type='checkbox' id='cgShowOnlyInactiveCheckbox' name='cg_show_only_inactive' value='true' /></label>";
echo "</div>";

echo "<div class='cg_image_checkbox_container_view_control '>
<div class=\"cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_activate_all\">
<div class=\"cg_image_checkbox_action\">Activate all</div>
<div class=\"cg_image_checkbox_icon\"></div>
</div>

<div class=\"cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_deactivate_all\">
<div class=\"cg_image_checkbox_action\">Deactivate all</div>
<div class=\"cg_image_checkbox_icon\"></div>
</div>

<div class=\"cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_delete_all\" >
<div class=\"cg_image_checkbox_action\">Delete all</div>
<div class=\"cg_image_checkbox_icon\" style=\"margin-left: 50px;\"></div>
</div>

<div class=\"cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_winner_all\">
<div class=\"cg_image_checkbox_action\">Winner all</div>
<div class=\"cg_image_checkbox_icon\"></div>
</div>

<div class=\"cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_not_winner_all\">
<div class=\"cg_image_checkbox_action\">Not winner all</div>
<div class=\"cg_image_checkbox_icon\"></div>
</div>

    </div>";

/*
<div class=\"cg_image_action_href cg_image_checkbox cg_image_checkbox_move_all\" >
<div class=\"cg_image_checkbox_action\">Move all</div>
<div class=\"cg_image_checkbox_icon\" style=\"margin-left: 50px;\"></div>
</div>*/

echo "</div>";




// if cgBackendHash then backend reload must be done
if(!empty($isAjaxGalleryCall) && empty($_POST['cgBackendHash'])){// if ajax send without this name, then must be under version 10.9.9.null.null

    echo "<div id='cgStepsNavigationTop'></div>";

    echo "<ul id='cgSortable' style='width:100%;padding:20px;background-color:#fff;margin-bottom:0px !important;margin-bottom:0px;border: thin solid black;margin-top:0px;'>";

    echo "<p style='text-align: center;'><b>New Contest Gallery Version detected. Please refresh this page one time manually.</b></p>";

    echo "</ul>";

    echo "<div id='cgStepsNavigationBottom'></div>";

    die;

}


if($isAjaxCall){


    echo "<div id='cgStepsNavigationTop' class='cg_steps_navigation'>";
    for ($i = 0; $rows > $i; $i = $i + $step) {

        $anf = $i + 1;
        $end = $i + $step;

        if ($end > $rows) {
            $end = $rows;
        }

        if ($anf == $nr1 AND ($start+$step) > $rows AND $start==0) {
            continue;
            echo "<div data-cg-start='$i' class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$anf-$end</a></div>";
        }
        elseif ($anf == $nr1 AND ($start+$step) > $rows AND $anf==$end) {

            echo "<div data-cg-start='$i' class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$end</a></div>";
        }
        elseif ($anf == $nr1 AND ($start+$step) > $rows) {

            echo "<div data-cg-start='$i' class='cg_step cg_step_selected'><a href=\?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$anf-$end</a></div>";
        }

        elseif ($anf == $nr1) {
            echo "<div data-cg-start='$i' class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$anf-$end</a></div>";
        }

        elseif ($anf == $end) {
            echo "<div data-cg-start='$i' class='cg_step'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$end</a></div>";
        }

        else {
            echo "<div data-cg-start='$i' class='cg_step'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$anf-$end</a></div>";
        }
    }
    echo "</div>";

}

// has to be before cgGalleryLoader
echo "<div id='cgGallerySubmitTop' >";

echo "<a class=\"cg_image_action_href cg_fields_div_add_fields\" href='?page=".cg_get_version()."/index.php&define_upload=true&option_id=$GalleryID'><span class=\"cg_image_action_span\">Add/Remove fields</span></a>";

echo '<input type="submit" class="cg_backend_button_gallery_action cg_gallery_backend_submit" name="submit" value="Change/Save data" id="cg_gallery_backend_submit" style="margin-left:auto;margin-right: 25px;">';

echo "</div>";

echo '<div id="cgGalleryLoader" class="cg-lds-dual-ring-div-gallery-hide cg-lds-dual-ring-div-gallery-hide-mainCGallery '.$cg_hide_is_new_gallery.'">
    <div class="cg-lds-dual-ring-gallery-hide cg-lds-dual-ring-gallery-hide-mainCGallery">
    </div>
</div>';

?>
