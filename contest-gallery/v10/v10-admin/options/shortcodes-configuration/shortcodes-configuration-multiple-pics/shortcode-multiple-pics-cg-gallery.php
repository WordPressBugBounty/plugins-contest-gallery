<?php

echo <<<HEREDOC
<div class="cg_view cgMultiplePicsOptions cg_short_code_multiple_pics_configuration_cg_gallery_container cg_short_code_multiple_pics_configuration_container cgViewHelper1 cg_active">
<div class='cg_view_container'>

HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option'>
        <div class='cg_view_option_title'>
        <p>Number of entries per screen<br><span class="cg_view_option_title_note">Pagination</span></p>
        </div>
        <div class='cg_view_option_input'>
        <input type="text" name="PicsPerSite" class="PicsPerSite" maxlength="3" value="$PicsPerSite"><br/>
        </div>
    </div>

    <div  class='cg_view_option cg_border_left_right_none'>
        <div class='cg_view_option_title'>
        <p>Enable full window button</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="FullSizeGallery" class="FullSizeGallery" $FullSizeGallery><br/>
        </div>
    </div>

    <div  class='cg_view_option'>
        <div  class='cg_view_option_title'>
        <p>Enable full screen button<br><span class="cg_view_option_title_note">Will appear when joining full window</span></p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="FullSize" class="FullSize" $FullSize><br/>
        </div>
    </div>
</div>

HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option cg_view_option_50_percent cg_border_top_right_bottom_none'>
        <div class='cg_view_option_title'>
        <p>Allow search for files<br/><span class="cg_view_option_title_note">Search by fields content, categories, file name or EXIF data - if available</span></p>
        </div>
        <div  class='cg_view_option_checkbox'>
        <input type="checkbox" name="Search" class="Search" $Search><br/>
        </div>
    </div>

    <div  class='cg_view_option cg_view_option_50_percent cg_border_top_bottom_none AllowSortContainer'>
        <div class='cg_view_option_title'>
        <p>Allow sort<br/><span class="cg_view_option_title_note">Order by rating is not available if <br>"Show only user votes" or <br>"Hide voting until user vote" is activated</span></p>
        </div>
        <div  class='cg_view_option_checkbox'>
        <input type="checkbox" name="AllowSort" class="AllowSort"  $AllowSort><br/>
        </div>
    </div>
</div>

<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cgAllowSortOptionsContainerMain'>
        <div class='cg_view_option_title'>
        <p>Allow sort options<br><span class="cgAllowSortDependsOnMessage cg_hide" >Allow sort has to be activated</span></p>
        </div>
        <div>
HEREDOC;

        $cgCustomSortCheck = (in_array('custom', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';
        $cgDateDescSortCheck = (in_array('date-desc', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';
        $cgDateAscSortCheck = (in_array('date-asc', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';
        $cgRateDescSortCheck = (in_array('rate-desc', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';
        $cgRateAscSortCheck = (in_array('rate-asc', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';
        $cgRateDescSumSortCheck = (in_array('rate-sum-desc', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';
        $cgRateAscSumSortCheck = (in_array('rate-sum-asc', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';
        $cgCommentDescSortCheck = (in_array('comment-desc', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';
        $cgCommentAscSortCheck = (in_array('comment-asc', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';
        $cgRandomSortCheck = (in_array('random', $AllowSortOptionsArray)) ? '' : 'cg_unchecked';

echo <<<HEREDOC
        <input type='hidden' name='AllowSortOptionsArray[]' value='custom' class='cg-allow-sort-input' />
        <input type='hidden' name='AllowSortOptionsArray[]' value='date-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='AllowSortOptionsArray[]' value='date-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='AllowSortOptionsArray[]' value='rate-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='AllowSortOptionsArray[]' value='rate-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='AllowSortOptionsArray[]' value='rate-sum-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='AllowSortOptionsArray[]' value='rate-sum-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='AllowSortOptionsArray[]' value='comment-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='AllowSortOptionsArray[]' value='comment-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='AllowSortOptionsArray[]' value='random' class='cg-allow-sort-input' />

        <div class="cgAllowSortOptionsContainer">
        <label class="cg-allow-sort-option $cgCustomSortCheck" data-cg-target="custom"><span class="cg-allow-sort-option-cat">Custom</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgDateDescSortCheck" data-cg-target="date-desc"><span class="cg-allow-sort-option-cat">Date desc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgDateAscSortCheck" data-cg-target="date-asc"><span class="cg-allow-sort-option-cat">Date asc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateDescSortCheck" data-cg-target="rate-desc"><span class="cg-allow-sort-option-cat">Rating desc<br><small><strong>for one star voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateAscSortCheck" data-cg-target="rate-asc"><span class="cg-allow-sort-option-cat">Rating asc<br><small><strong>for one star voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateDescSumSortCheck" data-cg-target="rate-sum-desc"><span class="cg-allow-sort-option-cat">Rating sum desc<br><small><strong>for multiple stars voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateAscSumSortCheck" data-cg-target="rate-sum-asc"><span class="cg-allow-sort-option-cat">Rating sum asc<br><small><strong>for multiple stars voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgCommentDescSortCheck" data-cg-target="comment-desc"><span class="cg-allow-sort-option-cat">Comments desc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgCommentAscSortCheck" data-cg-target="comment-asc"><span class="cg-allow-sort-option-cat">Comments asc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRandomSortCheck" data-cg-target="random"><span class="cg-allow-sort-option-cat">Random</span><span class="cg-allow-sort-option-icon"></span></label>
        </div>
        </div>
    </div>
</div>

HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>

    <div class='cg_view_option cg_view_option_flex_flow_column cg_border_top_none PreselectSortContainer'>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
        <p>Preselect order<br>on page load<br><span class="cgPreselectSortMessage cg_view_option_title_note">Random sort has to be deactivated</span></p>
        </div>

        <div class='cg_view_option_select'>
        <select name='PreselectSort' class='PreselectSort cg_no_outline_and_shadow_on_focus'>
HEREDOC;


        $PreselectSort_custom_selected = ($PreselectSort == 'custom') ? 'selected' : '';
        $PreselectSort_date_descend_selected = ($PreselectSort == 'date_descend') ? 'selected' : '';
        $PreselectSort_date_ascend_selected = ($PreselectSort == 'date_ascend') ? 'selected' : '';
        $PreselectSort_rating_descend_selected = ($PreselectSort == 'rating_descend') ? 'selected' : '';
        $PreselectSort_rating_ascend_selected = ($PreselectSort == 'rating_ascend') ? 'selected' : '';
        $PreselectSort_rating_sum_descend_selected = ($PreselectSort == 'rating_sum_descend') ? 'selected' : '';
        $PreselectSort_rating_sum_ascend_selected = ($PreselectSort == 'rating_sum_ascend') ? 'selected' : '';
        $PreselectSort_comments_descend_selected = ($PreselectSort == 'comments_descend') ? 'selected' : '';
        $PreselectSort_comments_ascend_selected = ($PreselectSort == 'comments_ascend') ? 'selected' : '';


echo <<<HEREDOC
        <option value='custom' $PreselectSort_custom_selected>Custom</option>
        <option value='date_descend' $PreselectSort_date_descend_selected>Date descending</option>
        <option value='date_ascend' $PreselectSort_date_ascend_selected>Date ascending</option>
        <option value='rating_descend' $PreselectSort_rating_descend_selected>Rating descending (for one star voting)</option>
        <option value='rating_ascend' $PreselectSort_rating_ascend_selected>Rating ascending (for one star voting)</option>
        <option value='rating_sum_descend' $PreselectSort_rating_sum_descend_selected>Rating sum descending (for multiple stars voting)</option>
        <option value='rating_sum_ascend' $PreselectSort_rating_sum_ascend_selected>Rating sum ascending (for multiple stars voting)</option>
        <option value='comments_descend' $PreselectSort_comments_descend_selected>Comments descending</option>
        <option value='comments_ascend' $PreselectSort_comments_ascend_selected>Comments ascending</option>
        </select>
        </div>

    </div>

    <div class='cg_view_option cg_border_top_right_left_none RandomSortContainer'>
        <div class='cg_view_option_title'>
            <p>Random sort<br><span class="cg_view_option_title_note">Each page load.<br>Random sort option<br>will be preselected<br>if allow sort is activated.</span></p>
        </div>
        <div  class='cg_view_option_checkbox'>
            <input type="checkbox" name="RandomSort" class="RandomSort" $RandomSort><br/>
        </div>
    </div>

    <div class='cg_view_option cg_border_top_none RandomSortButtonContainer'>
        <div class='cg_view_option_title'>
            <p>Random sort button</p>
        </div>
        <div  class='cg_view_option_checkbox'>
            <input type="checkbox" name="RandomSortButton" class="RandomSortButton" $RandomSortButton><br/>
        </div>
    </div>

</div>

HEREDOC;

if(!isset($jsonOptions[$GalleryID]['visual']['EnableSwitchStyleGalleryButton'])){
    $jsonOptions[$GalleryID]['visual']['EnableSwitchStyleGalleryButton'] = 0;
}

if(!isset($jsonOptions[$GalleryID]['visual']['SwitchStyleGalleryButtonOnlyTopControls'])){
    $jsonOptions[$GalleryID]['visual']['SwitchStyleGalleryButtonOnlyTopControls'] = 0;
}

echo <<<HEREDOC
<div class="cg_view_options_row">
            <div class="cg_view_option cg_view_option_100_percent cg_border_top_none" id="BorderRadiusContainer">
                <div class="cg_view_option_title">
                    <p>Round borders for all control elements and containers</p>
                </div>
                <div class="cg_view_option_checkbox cg_view_option_checked">
                    <input type="checkbox" name="BorderRadius" id="BorderRadius" $BorderRadius>
                </div>
            </div>
</div>

<div class='cg_go_to_target' data-cg-go-to-target="TopControlsStyleContainer" >
<div class='cg_view_options_row '  >
                <div class='cg_view_option cg_view_option_full_width cg_border_top_bottom_none'>
                    <div class='cg_view_option_title'>
                    <p>Gallery color style</p>
                    </div>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Bright style
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="FeControlsStyle" class="FeControlsStyleWhite cg_view_option_radio_multiple_input_field" $FeControlsStyleWhite value="white"/>
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Dark style
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="FeControlsStyle" class="FeControlsStyleBlack cg_view_option_radio_multiple_input_field" $FeControlsStyleBlack value="black">
                            </div>
                        </div>
                </div>
            </div>
</div>
 <div class='cg_view_options_row'>
            <div class='cg_view_option  cg_view_option_100_percent   cg_border_top_none EnableSwitchStyleGalleryButtonContainer'>
                <div class='cg_view_option_title'>
                    <p>Enable switch color style button<br><span class="cg_view_option_title_note">Will also switch style of opened entry view</span></p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="multiple-pics[cg_gallery][visual][EnableSwitchStyleGalleryButton]" checked="{$jsonOptions[$GalleryID]['visual']['EnableSwitchStyleGalleryButton']}" class="cg_shortcode_checkbox EnableSwitchStyleGalleryButton">
                </div>
            </div>
</div>
</div>
HEREDOC;

// only json option, not in database available
if(!empty($jsonOptions[$GalleryID]['visual']['ShowDate'])){
	$ShowDate = '1';
}else{
	$ShowDate = '0';
}

echo <<<HEREDOC
        <div class="cg_view_options_row">
                <div class="cg_view_option cg_view_option_50_percent cg_border_top_none cg_border_right_none" >
                    <div class="cg_view_option_title">
                        <p>Show date entry added/uploaded to gallery</p>
                    </div>
                    <div class="cg_view_option_checkbox cg_view_option_checked">
                        <input type="checkbox" name="multiple-pics[cg_gallery][visual][ShowDate]" class="cg_shortcode_checkbox" checked="$ShowDate">
                    </div>
                </div>
HEREDOC;

echo <<<HEREDOC
    <div  class='cg_view_option cg_border_top_none cg_border_left_none  cg_view_option_50_percent  cg_view_option_flex_flow_column '>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
        <p>Show date format
                    <br><span class="cg_view_option_title_note">Translation for seconds, minutes, hours, days<br>weeks, months, years can be found <a class="cg_no_outline_and_shadow_on_focus" href="{$editTranslationLink}l_GalleryDateFormat"  target="_blank">here</a></span>
        </p>
        </div>
        <div class="cg_view_option_select">
        <select name="multiple-pics[cg_gallery][visual][ShowDateFormat]" class="cg_no_outline_and_shadow_on_focus">
HEREDOC;

// only json option, not in database available
if(!empty($jsonOptions[$GalleryID]['visual']['ShowDateFormat'])){
	$ShowDateFormat = $jsonOptions[$GalleryID]['visual']['ShowDateFormat'];
}else{
	$ShowDateFormat = 'modern';
}

foreach($CommentsDateFormatNamePathSelectedValuesArray as  $key =>  $value){
	$ShowDateFormatSelected = '';
	if($key==$ShowDateFormat){
		$ShowDateFormatSelected = 'selected';
	}
	echo "<option value='$key' $ShowDateFormatSelected >$value</option>";
}

echo <<<HEREDOC
                               </select>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_50_percent cg_border_top_right_none GalleryUploadContainer'>
        <div class='cg_view_option_title'>
            <p>In gallery contact form button<br><span class="cg_view_option_title_note">Translated as "Participation form" in frontend</span></p>
        </div>
        <div class='cg_view_option_checkbox'>
HEREDOC;

            if ($isModernOptionsNew) {
                if ($GalleryUploadOnlyUser) {
                    $GalleryUpload = '';
                }
            }

if(!empty($jsonOptions[$GalleryID]['general']['ShowTextUntilAnImageAdded'])){
    $ShowTextUntilAnImageAdded = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID]['general']['ShowTextUntilAnImageAdded']);
}else{
    $ShowTextUntilAnImageAdded = '';
}

$ShowAlwaysContainer = '';
if(floatval($galleryDbVersion)<21){
$ShowAlwaysContainer = <<<HEREDOC
<div class='cg_view_options_row cg_go_to_target' data-cg-go-to-target="ShowAlwaysContainer">
    <div class='cg_view_option cg_view_option_100_percent cg_border_top_none'>
        <div class='cg_view_option_title'>
            <p>Show constantly (without hovering)<br>vote, comments and file title in gallery view<br><span class="cg_view_option_title_note">You see it by hovering if not activated.<br>File title can be configured in "Edit contact form" >>> "Show as title in gallery view".</span></p>
        </div>
        <div class='cg_view_option_checkbox'>
         <input type="checkbox" name="ShowAlways" $ShowAlways>
        </div>
    </div>
</div>
HEREDOC;
}



echo <<<HEREDOC
<input class='GalleryUpload' type='checkbox' name='GalleryUpload' $GalleryUpload >
        </div>
    </div>

    <div class='cg_view_option cg_view_option_50_percent cg_border_top_none cg_border_left_none'>
        <div class='cg_view_option_title cg_view_option_title_flex_flow_column'>
            <p>In gallery contact form text configuration</p>
            <a class="cg_no_outline_and_shadow_on_focus" href="#cgInGalleryUploadFormConfiguration"><p>Can be configured here...</p></a>
        </div>
    </div>


</div>

            $ShowAlwaysContainer

            <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-ShowTextUntilAnImageAddedGallery-wrap-Container">
                    <div class='cg_view_option_title'>
                        <p>This text is visible until first entry appears in the gallery<br><span class="cg_view_option_title_note">This text is visible until first entry is added, activated and so is visible in frontend.</span></p>
                    </div>
                    <div class='cg_view_option_html'>
                        <textarea class='cg-wp-editor-template' id='ShowTextUntilAnImageAddedGallery'  name='multiple-pics[cg_gallery][general][ShowTextUntilAnImageAdded]'>$ShowTextUntilAnImageAdded</textarea>
                    </div>
                </div>
            </div>

            <div class='cg_view_options_row cg_go_to_target RegUserGalleryOnlyContainer' data-cg-go-to-target="RegUserGalleryOnlyContainer">
                <div class='cg_view_option cg_view_option_100_percent cg_border_top_none $cgProFalse' >
                    <div class='cg_view_option_title'>
                        <p>Allow only registered users to see the gallery<br><span class="cg_view_option_title_note">User have to be registered and logged in to be able to see the gallery</span></p>
                    </div>
                    <div class='cg_view_option_checkbox'>
                        <input type="checkbox" name="RegUserGalleryOnly" class="RegUserGalleryOnly" $RegUserGalleryOnly>
                    </div>
                </div>
            </div>
            
            <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none $cgProFalse RegUserGalleryOnlyTextContainer' id="wp-RegUserGalleryOnlyText-wrap-Container">
                    <div class='cg_view_option_title'>
                        <p>Show text instead of gallery</p>
                    </div>
                    <div class='cg_view_option_html'>
                        <textarea class='cg-wp-editor-template' id='RegUserGalleryOnlyText'  name='RegUserGalleryOnlyText'>$RegUserGalleryOnlyText</textarea>
                    </div>
                </div>
            </div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;

if(floatval($galleryDbVersion)>=21){

echo <<<HEREDOC
        <div class='cg_view_options_row_container GalleryPageOptionsParentContainer cg_border_radius_8_px' style="border: thin solid #dedede;margin-top:15px;">
            <p class="cg_view_options_row_container_title" >Gallery landing page options</p>            
HEREDOC;

    if(floatval($galleryDbVersion)>=24) {
echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_100_percent cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px'>
            <div class='cg_view_option_title '>
                <p>Show back to galleries button on gallery landing page
                <br><span class="cg_view_option_title_note"><a href="$WpPageParentPermalink" target="_blank">$WpPageParentPermalink</a></span></p>
            </div>
            <div class='cg_view_option_checkbox '>
                <input type="checkbox" name="ShowBackToGalleriesButton" class="ShowBackToGalleriesButton"  $ShowBackToGalleriesButton  >
            </div>
        </div>
    </div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
        <div class='cg_view_option_title '>
            <p>Back to galleries button text<br>
            <span class="cg_view_option_title_note">Translation can be found <a class="cg_no_outline_and_shadow_on_focus" href="{$editTranslationLink}l_BackToGalleries"  target="_blank">here</a></span>
            </p>
        </div>
    </div>
</div>
HEREDOC;

        $slugName = (!empty($CgEntriesOwnSlugNameGalleries)) ? $CgEntriesOwnSlugNameGalleries : 'contest-galleries';

        $page = get_page_by_path( $slugName, OBJECT, 'page');
        $pageGalleries = (!empty($page)) ? get_permalink($page->ID) : '';

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Back to galleries button custom URL on gallery landing page
                <br><span class="cg_view_option_title_note"><a href="$WpPageParentPermalink" target="_blank">$WpPageParentPermalink</a></span><br><span class="cg_view_option_title_note">If not set then parent site<br><a target="_blank"  href="$pageGalleries">$pageGalleries</a><br>URL will be used<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name="BackToGalleriesButtonURL" class="BackToGalleriesButtonURL"  value="$BackToGalleriesButtonURL"  >
            </div>
        </div>
    </div>
HEREDOC;

    }

    echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Redirect URL for parent site of Contest Gallery entries<br><span class="cg_view_option_title_note">The gallery page for cg_gallery id="$GalleryID" shortcode is<br><a target="_blank"  href="$WpPageParentPermalink">$WpPageParentPermalink</a><br>(But you can place the shortcode also on any other page)<br>If Redirect URL is set, then HTTP 301 redirect will be executed if the above URL gets called<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name="WpPageParentRedirectURL" class="WpPageParentRedirectURL"  value="$WpPageParentRedirectURL"  >
            </div>
        </div>
    </div>
HEREDOC;

// only json option, not in database available
    if(!isset($jsonOptions[$GalleryID]['visual']['AdditionalCssGalleryPage'])){
        $AdditionalCssGalleryPage = "body {\r\n&nbsp;&nbsp;font-family: sans-serif;\r\n&nbsp;&nbsp;font-size: 16px;\r\n&nbsp;&nbsp;background-color: white;\r\n&nbsp;&nbsp;color: black;\r\n}";
    }else{
        $AdditionalCssGalleryPage = cg_stripslashes_recursively($jsonOptions[$GalleryID]['visual']['AdditionalCssGalleryPage']);
    }

    echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Additional CSS cg_gallery page<br><span class="cg_view_option_title_note"><a target="_blank"  href="$WpPageParentPermalink">$WpPageParentPermalink</a></span></p>
            </div>
            <div class='cg_view_option_textarea' >
                <textarea type="text" name="multiple-pics[cg_gallery][visual][AdditionalCssGalleryPage]" rows="7" style="width:100%;" class="AdditionalCssGalleryPage"  >$AdditionalCssGalleryPage</textarea>
            </div>
        </div>
    </div>
HEREDOC;


echo <<<HEREDOC
        </div>
HEREDOC;

}




$showSliderViewOption = false;
$showSliderViewOptionSet = false;

if (!in_array("SliderLookOrder", $order)) {
    $showSliderViewOption = true;
}

$showBlogViewOption = false;
$showBlogViewOptionSet = false;

if (!in_array("BlogLookOrder", $order)) {
    $showBlogViewOption = true;
}

echo <<<HEREDOC
<div class='cg_options_sortable'>

<p class='cg_options_sortable_title'>View options and order</p>

HEREDOC;

$i = 0;

foreach ($order as $key => $value) {

    if ($value == "BlogLookOrder" or ($showBlogViewOption == true && $showBlogViewOptionSet == false)) {

        $i++;

        $showSliderViewOptionSet = true;

echo <<<HEREDOC

        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
             <div class="cg_options_order">$i.</div>
              <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
               <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option cg_view_options_and_order_checkbox_container cg_view_option_100_percent BlogLookContainer cg_border_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="order[]" value="b" >
                                <p>Activate <u>Blog View</u></p>
                         </div>
                         <div  class='cg_view_option_checkbox cg_view_options_and_order_checkbox'>
                            <input type="checkbox" name="BlogLook" class="BlogLook" $BlogLook>
                         </div>
                    </div>
                </div>
            </div>
        </div>
HEREDOC;

    }

    if ($value == "SliderLookOrder" or ($showSliderViewOption == true && $showSliderViewOptionSet == false)) {

        $i++;

        $showSliderViewOptionSet = true;

        echo <<<HEREDOC
        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
            <div class="cg_options_order">$i.</div>
            <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
                <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option cg_view_options_and_order_checkbox_container cg_border_right_none cg_view_option_50_percent SliderLookContainer cg_border_border_bottom_left_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="order[]" value="s" >
                                <p>Activate <u>Slider View</u></p>
                         </div>
                         <div  class='cg_view_option_checkbox cg_view_options_and_order_checkbox'>
                            <input type="checkbox" name="SliderLook" class="SliderLook" $SliderLook>
                         </div>
                    </div>
                    <div class='cg_view_option cg_view_option_50_percent SliderThumbNavContainer cg_border_border_top_right_radius_8_px cg_border_border_bottom_right_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <p>Enable thumbnail navigation</p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="SliderThumbNav" class="SliderThumbNav" $SliderThumbNav >
                         </div>
                    </div>
                </div>
        </div>
    </div>
    
HEREDOC;

    }

    if ($value == "ThumbLookOrder") {

        $i++;

        echo <<<HEREDOC
        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
            <div class="cg_options_order">$i.</div>
            <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
                <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option  cg_view_option_100_percent ThumbLookContainer cg_border_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="order[]" value="t" >
                                <p>Activate <u>Masonry View</u></p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="ThumbLook" class="ThumbLook" $ThumbLook>
                         </div>
                    </div>
                </div>
                <div class='cg_view_options_row cg_hide'>
                    <div class='cg_view_option cg_border_right_none WidthThumbContainer'>
                        <div class='cg_view_option_title'>
                                <p>Width thumbs (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="3" name="WidthThumb" class="WidthThumb" value="$WidthThumb" >
                         </div>
                    </div>
                    <div class='cg_view_option HeightThumbContainer cg_border_border_top_right_radius_8_px '>
                        <div class='cg_view_option_title'>
                                <p>Height thumbs (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="3" name="HeightThumb" class="HeightThumb" value="$HeightThumb" >
                         </div>
                    </div>
                </div>
                <div class='cg_view_options_row cg_hide'>
                    <div class='cg_view_option cg_view_option_50_percent cg_border_top_right_none DistancePicsContainer cg_border_border_bottom_left_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <p>Distance between thumbs horizontal (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="DistancePics" class="DistancePics" value="$DistancePics">
                         </div>
                    </div>
                    <div class='cg_view_option cg_view_option_50_percent cg_border_top_none DistancePicsVContainer cg_border_border_bottom_right_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <p>Distance between thumbs vertical (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="DistancePicsV" class="DistancePicsV" value="$DistancePicsV">
                         </div>
                    </div>
                </div>
        </div>
    </div>

HEREDOC;
    }

    if ($value == "HeightLookOrder") {
        // since 21.2.0 no HeightLook anymore
        continue;

echo <<<HEREDOC
        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
            <div class="cg_options_order">$i.</div>
            <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
                <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option cg_border_right_none cg_view_option_50_percent HeightLookContainer cg_border_border_top_left_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="order[]" value="h" >
                                <p>Activate <u>Height View</u></p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="HeightLook" class="HeightLook" $HeightLook>
                         </div>
                    </div>
                    <div class='cg_view_option cg_view_option_50_percent HeightLookHeightContainer cg_border_border_top_right_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <p>Height of pics in a row (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="3" name="HeightLookHeight" class="HeightLookHeight" value="$HeightLookHeight" >
                         </div>
                    </div>
                </div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option cg_view_option_50_percent cg_border_top_right_none HeightViewSpaceWidthContainer cg_border_border_bottom_left_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <p>Horizontal distance between files (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="HeightViewSpaceWidth" class="HeightViewSpaceWidth" value="$HeightViewSpaceWidth">
                         </div>
                    </div>
                    <div class='cg_view_option cg_view_option_50_percent cg_border_top_none HeightViewSpaceHeightContainer cg_border_border_bottom_right_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <p>Vertical distance between files (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="HeightViewSpaceHeight" class="HeightViewSpaceHeight" value="$HeightViewSpaceHeight">
                         </div>
                    </div>
                </div>
        </div>
    </div>
HEREDOC;

    }


    if ($value == "RowLookOrder") {

        $rowViewDeprecatedText = '';
        $rowViewDeprecatedDisabled = '';

        if(intval($galleryDbVersion)<15){
            $rowViewDeprecatedText = '<br><span class="cg_color_red">NOTE:</span> since plugin version 15.0.0<br>"Row View" is deprecated<br>can\'t fulfill mobile requirements<br>if "Row View" was activated before<br>"Height View" will be activated instead';
            $rowViewDeprecatedDisabled = 'cg_disabled';
        }else{
            if(intval($galleryDbVersion)>=15){
                continue;
            }
        }

        $i++;

        echo <<<HEREDOC
        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
                <div class="cg_options_order">$i.</div>
                <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
                <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option cg_border_right_none cg_view_option_50_percent RowLookContainer $rowViewDeprecatedDisabled'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="order[]" value="r" >
                                <p>Activate <u>Row View</u><br><span class="cg_font_weight_normal">(Same amount of files in each row)$rowViewDeprecatedText</span>
                                </p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="RowLook" class="RowLook" $RowLook>
                         </div>
                    </div>
                    <div class='cg_view_option cg_view_option_50_percent PicsInRowContainer'>
                        <div class='cg_view_option_title'>
                                <p>Number of pics in a row</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="PicsInRow" class="PicsInRow" value="$PicsInRow" >
                         </div>
                    </div>
                </div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option cg_view_option_50_percent cg_border_top_right_none RowViewSpaceWidthContainer'>
                        <div class='cg_view_option_title'>
                                <p>Horizontal distance between files (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="RowViewSpaceWidth" class="RowViewSpaceWidth" value="$RowViewSpaceWidth">
                         </div>
                    </div>
                    <div class='cg_view_option cg_view_option_50_percent cg_border_top_none RowViewSpaceHeightContainer'>
                        <div class='cg_view_option_title'>
                                <p>Vertical distance between files (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="RowViewSpaceHeight" class="RowViewSpaceHeight" value="$RowViewSpaceHeight">
                         </div>
                    </div>
                </div>
        </div>
    </div>
HEREDOC;

    }

}

echo <<<HEREDOC
</div>
HEREDOC;

if(!isset($jsonOptions[$GalleryID]['pro']['MainTitleGalleriesView'])){
	$MainTitleGalleriesView = '';
}else{
	$MainTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID]['pro']['MainTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row ' style="margin-top: -15px;"  >
        <div class='cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px  cg_border_border_top_right_radius_8_px cg_go_to_target' data-cg-go-to-target="MainTitleGalleriesViewArea">
            <div class='cg_view_option_title '>
                <p>Main title cg_galleries view</p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery][pro][MainTitleGalleriesView]' class="MainTitleGalleriesView"  value="$MainTitleGalleriesView"  >
            </div>
        </div>
    </div>
HEREDOC;

if(!isset($jsonOptions[$GalleryID]['pro']['SubTitleGalleriesView'])){
	$SubTitleGalleriesView = '';
}else{
	$SubTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID]['pro']['SubTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_go_to_target' data-cg-go-to-target="SubTitleGalleriesViewArea">
            <div class='cg_view_option_title '>
                <p>Sub title cg_galleries view</p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery][pro][SubTitleGalleriesView]' class="SubTitleGalleriesView"  value="$SubTitleGalleriesView"  >
            </div>
        </div>
    </div>
HEREDOC;

if(!isset($jsonOptions[$GalleryID]['pro']['ThirdTitleGalleriesView'])){
	$ThirdTitleGalleriesView = '';
}else{
	$ThirdTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID]['pro']['ThirdTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none  cg_border_border_bottom_left_radius_8_px  cg_border_border_bottom_right_radius_8_px cg_go_to_target $cgProFalse' data-cg-go-to-target="ThirdTitleGalleriesViewArea">
            <div class='cg_view_option_title '>
                <p>Third title cg_galleries view
                <br>
                <span class="cg_view_option_title_note"><b>NOTE:</b> perfect to use as description, 20 text rows will be displayed as preview in masonry galleries view</span>
                </p>
            </div>
            <div class='cg_view_option_input '>
                <textarea type="text" name='multiple-pics[cg_gallery][pro][ThirdTitleGalleriesView]' class="ThirdTitleGalleriesView" rows="5" style="width:100%;" >$ThirdTitleGalleriesView</textarea>
            </div>
        </div>
    </div>
HEREDOC;

echo "<br><br>";

echo <<<HEREDOC
</div>
HEREDOC;
