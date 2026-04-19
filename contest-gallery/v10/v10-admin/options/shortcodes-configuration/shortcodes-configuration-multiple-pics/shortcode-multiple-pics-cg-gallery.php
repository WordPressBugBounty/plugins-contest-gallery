<?php

echo <<<HEREDOC
<div class="cg_view cgMultiplePicsOptions cg_short_code_multiple_pics_configuration_cg_gallery_container cg_short_code_multiple_pics_configuration_container cgViewHelper1 cg_active">
<div class='cg_view_container'>

HEREDOC;

// since 29.0.0 no full window and full screen
echo <<<HEREDOC
<div class='cg_view_options_row cg_hide'>
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

cg1l_correct_view_options_and_order($order,$ThumbLook,$SliderLook,$BlogLook,$HeightLook,$RowLook,false);

echo <<<HEREDOC
<div class='cg_view_options_row cg_hide'>
    <div class='cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px cg_border_bottom_none' style="padding-bottom:20px;">
        <div class='cg_view_option_title'>
            <p>Gallery view<br><span class="cg_view_option_title_note">Select how entries should be displayed in gallery</span></p>
        </div>
        <input type="hidden" name="order[]" value="t" />
        <input type="hidden" name="order[]" value="s" />
        <input type="hidden" name="order[]" value="b" />
        <div class='cg_view_option_radio_multiple'>
            <div class='cg_view_option_radio_multiple_container ThumbLookContainer cg_one_third_width'>
                <div class='cg_view_option_radio_multiple_title'>
                    Activate Masonry View
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="ThumbLook" class="OrderLook cg_view_option_radio_multiple_input_field" $ThumbLook>
                </div>
            </div>
            <div class='cg_view_option_radio_multiple_container SliderLookContainer cg_one_third_width'>
                <div class='cg_view_option_radio_multiple_title'>
                    Activate Slider View
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="SliderLook" class="OrderLook cg_view_option_radio_multiple_input_field" $SliderLook>
                </div>
            </div>
            <div class='cg_view_option_radio_multiple_container BlogLookContainer cg_one_third_width'>
                <div class='cg_view_option_radio_multiple_title'>
                    Activate Blog View
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="BlogLook" class="OrderLook cg_view_option_radio_multiple_input_field" $BlogLook>
                </div>
            </div>
        </div>
    </div>
</div>
<div class='cg_view_options_row cg_hide'>
    <div class='cg_view_option cg_view_option_33_percent cg_view_option_not_focus cg_border_top_none cg_border_bottom_none cg_border_right_none'>
        <div class='cg_view_option_title'>
            <p>&nbsp;</p>
        </div>
    </div>
    <div class='cg_view_option cg_view_option_33_percent SliderThumbNavContainer cg_border_top_none cg_border_bottom_none cg_border_left_none cg_border_right_none'>
        <div class='cg_view_option_title'>
            <p>Enable thumbnail navigation</p>
        </div>
        <div class='cg_view_option_checkbox'>
            <input type="checkbox" name="SliderThumbNav" class="SliderThumbNav" $SliderThumbNav>
        </div>
    </div>
    <div class='cg_view_option cg_view_option_33_percent cg_view_option_not_focus cg_border_top_none cg_border_bottom_none cg_border_left_none'>
        <div class='cg_view_option_title'>
            <p>&nbsp;</p>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_border_right_bottom_none cg_border_border_top_left_radius_8_px'>
        <div class='cg_view_option_title'>
        <p>Number of entries per screen<br><span class="cg_view_option_title_note">Pagination</span></p>
        </div>
        <div class='cg_view_option_input'>
        <input type="text" name="PicsPerSite" class="PicsPerSite" maxlength="3" value="$PicsPerSite"><br/>
        </div>
    </div>
    <div  class='cg_view_option cg_border_right_bottom_none'>
        <div class='cg_view_option_title'>
        <p>Allow search for files<br/><span class="cg_view_option_title_note">Search by fields content, categories, file name or EXIF data - if available</span></p>
        </div>
        <div  class='cg_view_option_checkbox'>
        <input type="checkbox" name="Search" class="Search" $Search><br/>
        </div>
    </div>
    <div class='cg_view_option cg_border_bottom_none cg_border_border_top_right_radius_8_px AllowSortContainer'>
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
        <label class="cg-allow-sort-option $cgDateDescSortCheck" data-cg-target="date-desc"><span class="cg-allow-sort-option-cat">Date desc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgDateAscSortCheck" data-cg-target="date-asc"><span class="cg-allow-sort-option-cat">Date asc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateDescSortCheck" data-cg-target="rate-desc"><span class="cg-allow-sort-option-cat">Rating desc<br><small><strong>for one star voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateAscSortCheck" data-cg-target="rate-asc"><span class="cg-allow-sort-option-cat">Rating asc<br><small><strong>for one star voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateDescSumSortCheck" data-cg-target="rate-sum-desc"><span class="cg-allow-sort-option-cat">Rating sum desc<br><small><strong>for multiple stars voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateAscSumSortCheck" data-cg-target="rate-sum-asc"><span class="cg-allow-sort-option-cat">Rating sum asc<br><small><strong>for multiple stars voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgCommentDescSortCheck" data-cg-target="comment-desc"><span class="cg-allow-sort-option-cat">Comments desc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgCommentAscSortCheck" data-cg-target="comment-asc"><span class="cg-allow-sort-option-cat">Comments asc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRandomSortCheck" data-cg-target="random"><span class="cg-allow-sort-option-cat">Random</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgCustomSortCheck" data-cg-target="custom"><span class="cg-allow-sort-option-cat">Custom</span><span class="cg-allow-sort-option-icon"></span></label>
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
        <option value='date_descend' $PreselectSort_date_descend_selected>Date descending</option>
        <option value='date_ascend' $PreselectSort_date_ascend_selected>Date ascending</option>
        <option value='rating_descend' $PreselectSort_rating_descend_selected>Rating descending (for one star voting)</option>
        <option value='rating_ascend' $PreselectSort_rating_ascend_selected>Rating ascending (for one star voting)</option>
        <option value='rating_sum_descend' $PreselectSort_rating_sum_descend_selected>Rating sum descending (for multiple stars voting)</option>
        <option value='rating_sum_ascend' $PreselectSort_rating_sum_ascend_selected>Rating sum ascending (for multiple stars voting)</option>
        <option value='comments_descend' $PreselectSort_comments_descend_selected>Comments descending</option>
        <option value='comments_ascend' $PreselectSort_comments_ascend_selected>Comments ascending</option>
        <option value='custom' $PreselectSort_custom_selected>Custom</option>
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
if(isset($jsonOptions[$GalleryID]['visual']['ShowDate'])){
	$ShowDate = absint($jsonOptions[$GalleryID]['visual']['ShowDate']);
	if(!in_array($ShowDate,array(0,1,2,3),true)){
		$ShowDate = !empty($jsonOptions[$GalleryID]['visual']['ShowDate']) ? 1 : 0;
	}
}else{
	$ShowDate = 0;
}

$ShowDateChecked = (!empty($ShowDate)) ? 'checked' : '';
$ShowDateCheckboxClass = (!empty($ShowDate)) ? 'cg_view_option_checked' : 'cg_view_option_unchecked';
$ShowDateDisabledClass = empty($ShowDate) ? 'cg_disabled' : '';
$ShowDateView = in_array($ShowDate,array(1,2,3),true) ? $ShowDate : 1;
$ShowDateView1 = ($ShowDateView===1) ? 'checked' : '';
$ShowDateView2 = ($ShowDateView===2) ? 'checked' : '';
$ShowDateView3 = ($ShowDateView===3) ? 'checked' : '';

echo <<<HEREDOC
        <div class="cg_view_options_row">
                <div class="cg_view_option cg_view_option_50_percent cg_border_top_none cg_border_right_none cg_border_bottom_none ShowDateContainer" >
                    <div class="cg_view_option_title">
                        <p>Show date entry added/uploaded to gallery</p>
                    </div>
                    <div class="cg_view_option_checkbox $ShowDateCheckboxClass">
                        <input type="checkbox" name="multiple-pics[cg_gallery][visual][ShowDate]" class="cg_shortcode_checkbox ShowDate" $ShowDateChecked>
                    </div>
                </div>
HEREDOC;

echo <<<HEREDOC
    <div  class='cg_view_option cg_border_top_none cg_border_left_none cg_border_bottom_none cg_view_option_50_percent cg_view_option_flex_flow_column ShowDateFormatContainer $ShowDateDisabledClass'>
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
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none ShowDateViewContainer $ShowDateDisabledClass'>
        <div class='cg_view_option_radio_multiple'>
            <div class='cg_view_option_radio_multiple_container cg_border_top_none'>
                <div class='cg_view_option_radio_multiple_title'>
                    Show in gallery and entry view
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="multiple-pics[cg_gallery][visual][ShowDateView]" class="cg_view_option_radio_multiple_input_field" $ShowDateView1 value="1" />
                </div>
            </div>
            <div class='cg_view_option_radio_multiple_container cg_border_top_none'>
                <div class='cg_view_option_radio_multiple_title'>
                    Show in gallery view only
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="multiple-pics[cg_gallery][visual][ShowDateView]" class="cg_view_option_radio_multiple_input_field" $ShowDateView2 value="2" />
                </div>
            </div>
            <div class='cg_view_option_radio_multiple_container cg_border_top_none'>
                <div class='cg_view_option_radio_multiple_title'>
                    Show in entry view only
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="multiple-pics[cg_gallery][visual][ShowDateView]" class="cg_view_option_radio_multiple_input_field" $ShowDateView3 value="3" />
                </div>
            </div>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_50_percent cg_border_top_right_none GalleryUploadContainer'>
        <div class='cg_view_option_title'>
            <p>In gallery upload form button<br><span class="cg_view_option_title_note">Translated as "Participation form" in frontend</span></p>
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
            <p>Show constantly (without hovering)<br>vote, comments and file title in gallery view<br><span class="cg_view_option_title_note">You see it by hovering if not activated.<br>File title can be configured in "Edit upload form" >>> "Show as title in gallery view".</span></p>
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
            <p>In gallery upload form text configuration</p>
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

    // only json option, not in database available
    if(!isset($jsonOptions[$GalleryID]['visual']['HeaderWpPageParent'])){
        $HeaderWpPageParent = "";
    }else{
        $HeaderWpPageParent = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID]['visual']['HeaderWpPageParent']);
    }

    echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Header tracking code on gallery landing page<br><span class="cg_view_option_title_note">Paste your tracking scripts here —<br>for example Google Tag Manager, Google Analytics, or Meta Pixel.<br>The code will be added inside the &lt;head&gt; section of gallery landing page.</span></p>
            </div>
            <div class='cg_view_option_textarea' >
                <textarea type="text" name="multiple-pics[cg_gallery][visual][HeaderWpPageParent]" rows="7" style="width:100%;" class="HeaderWpPageParent"  >$HeaderWpPageParent</textarea>
            </div>
        </div>
    </div>
HEREDOC;

    // only json option, not in database available
    if(!isset($jsonOptions[$GalleryID]['visual']['TextBeforeWpPageParent'])){
        $TextBeforeWpPageParent = "";
    }else{
        $TextBeforeWpPageParent = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID]['visual']['TextBeforeWpPageParent']);
    }

    echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-TextBeforeWpPageParent-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on gallery landing page before gallery<br><span class="cg_view_option_title_note">Add general text or tracking code. &lt;noscript&gt; tags are also supported.<br>The code will be inserted inside the &lt;body&gt; section of gallery landing page.<br><span class="cg_font_weight_500">NOTE: </span>appears only on gallery landing page, not if cg_gallery... shortcode is used on another page.</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery][visual][TextBeforeWpPageParent]'  id='TextBeforeWpPageParent'>$TextBeforeWpPageParent</textarea>
        </div>
    </div>
</div>
HEREDOC;

    // only json option, not in database available
    if(!isset($jsonOptions[$GalleryID]['visual']['TextAfterWpPageParent'])){
        $TextAfterWpPageParent = "";
    }else{
        $TextAfterWpPageParent = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID]['visual']['TextAfterWpPageParent']);
    }

    echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-TextAfterWpPageParent-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on gallery landing page after gallery<br><span class="cg_view_option_title_note">Add general text or tracking code. &lt;noscript&gt; tags are also supported.<br>The code will be inserted inside the &lt;body&gt; section of gallery landing page.<br><span class="cg_font_weight_500">NOTE: </span>appears only on gallery landing page, not if cg_gallery... shortcode is used on another page.</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery][visual][TextAfterWpPageParent]'  id='TextAfterWpPageParent'>$TextAfterWpPageParent</textarea>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
        </div>
HEREDOC;

}

if(!isset($jsonOptions[$GalleryID]['pro']['MainTitleGalleriesView'])){
	$MainTitleGalleriesView = '';
}else{
	$MainTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID]['pro']['MainTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row ' style="margin-top: 15px;"  >
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
