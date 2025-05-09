<?php

echo <<<HEREDOC
<div class="cg_view cgMultiplePicsOptions cg_short_code_multiple_pics_configuration_cg_gallery_ecommerce_container cg_short_code_multiple_pics_configuration_container cgViewHelper1 cg_hide">
<div class='cg_view_container'>
HEREDOC;

$cg_disabledAllowRatingForGalleryEcommerce = '';

if(!empty($jsonOptions[$GalleryID.'-ec']['visual']['AllowSortOptions'])){
	$AllowSortOptionsArrayCgGalleryEcommerce = explode(',',$jsonOptions[$GalleryID.'-ec']['visual']['AllowSortOptions']);
}else{
	$AllowSortOptionsArrayCgGalleryEcommerce = array();
}

if(!empty($jsonOptions[$GalleryID.'-ec']['general']['RatingVisibleForGalleryEcommerce'])){
	$RatingVisibleForGalleryEcommerce = '1';
}else{
	$cg_disabledAllowRatingForGalleryEcommerce = 'cg_disabled';
	$RatingVisibleForGalleryEcommerce = '0';
}

if(!empty($jsonOptions[$GalleryID.'-ec']['general']['AllowRatingForGalleryEcommerce'])){
	$AllowRatingForGalleryEcommerce = '1';
}else{
	$AllowRatingForGalleryEcommerce = '0';
}

echo <<<HEREDOC
<div class="cg_view_options_row RatingVisibleForGalleryEcommerce">
        <div class="cg_view_option cg_view_option_50_percent cg_border_right_none " id="RatingVisibleForGalleryEcommerceOption">
            <div class="cg_view_option_title">
                <p>Make current voting status visible<br><span class="cg_view_option_title_note">Shows current file rating (but still not possible to vote)</span></p>
            </div>
            <div class="cg_view_option_checkbox cg_view_option_checked">
                <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][RatingVisibleForGalleryEcommerce]" class="cg_shortcode_checkbox  RatingVisibleForGalleryNoVotingCheckbox" checked="$RatingVisibleForGalleryEcommerce" id="RatingVisibleForGalleryEcommerce">
            </div>
        </div>
        <div  class='cg_view_option  cg_view_option_50_percent  AllowSortContainer $cg_disabledAllowRatingForGalleryEcommerce' id="AllowRatingForGalleryEcommerceOption" >
            <div class="cg_view_option_title">
                <p>Enable voting<br><span class="cg_view_option_title_note">Enable "Make current voting status visible" first</span></p>
            </div>
            <div class="cg_view_option_checkbox cg_view_option_checked">
                <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][AllowRatingForGalleryEcommerce]" class="cg_shortcode_checkbox AllowRatingForGalleryEcommerceCheckbox" checked="$AllowRatingForGalleryEcommerce" id="AllowRatingForGalleryEcommerce">
            </div>
        </div>
</div>
<div class='cg_view_options_row'>
    <div class='cg_view_option  cg_border_top_none'>
        <div class='cg_view_option_title'>
        <p>Number of entries per screen<br><span class="cg_view_option_title_note">Pagination</span></p>
        </div>
        <div class='cg_view_option_input'>
        <input type="text" name="multiple-pics[cg_gallery_ecommerce][general][PicsPerSite]" class="PicsPerSite" maxlength="3" value="{$jsonOptions["$GalleryID-ec"]["general"]["PicsPerSite"]}">
        </div>
    </div>

    <div  class='cg_view_option  cg_border_left_right_none cg_border_top_none'>
        <div class='cg_view_option_title'>
        <p>Enable full window button</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][FullSizeGallery]" class="cg_shortcode_checkbox FullSizeGallery" checked="{$jsonOptions["$GalleryID-ec"]["general"]["FullSizeGallery"]}"><br/>
        </div>
    </div>

    <div  class='cg_view_option  cg_border_top_none'>
        <div  class='cg_view_option_title'>
        <p>Enable full screen button<br><span class="cg_view_option_title_note">Will appear when joining full window</span></p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][FullSize]" class="cg_shortcode_checkbox FullSize" checked="{$jsonOptions["$GalleryID-ec"]["general"]["FullSize"]}"><br/>
        </div>
    </div>
</div>

HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div  class='cg_view_option  cg_view_option_50_percent cg_border_top_right_bottom_none'>
        <div class='cg_view_option_title'>
        <p>Allow search for files<br/><span class="cg_view_option_title_note">Search by fields content, categories, file name or EXIF data - if available</span></p>
        </div>
        <div  class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][pro][Search]" class="cg_shortcode_checkbox Search" checked="{$jsonOptions["$GalleryID-ec"]["pro"]["Search"]}">
        </div>
    </div>

    <div  class='cg_view_option  cg_view_option_50_percent cg_border_top_bottom_none AllowSortContainer'>
        <div class='cg_view_option_title'>
        <p>Allow sort<br/><span class="cg_view_option_title_note">Order by rating is not available if <br>"Show only user votes" or <br>"Hide voting until user vote" is activated</span></p>
        </div>
        <div  class='cg_view_option_checkbox'>
                <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][AllowSort]" class="cg_shortcode_checkbox AllowSort" checked="{$jsonOptions["$GalleryID-ec"]["general"]["AllowSort"]}">
        </div>
    </div>
</div>

<div class='cg_view_options_row'>
    <div class='cg_view_option  cg_view_option_full_width cgAllowSortOptionsContainerMain'>
        <div class='cg_view_option_title'>
        <p>Allow sort options<br><span class="cg_view_option_title_note">To make rating sort options available activate "Make current voting status visible" option above</span><span class="cgAllowSortDependsOnMessage cg_hide" >Allow sort has to be activated</span></p>
        </div>
        <div>
HEREDOC;

$cgCustomSortCheck = (in_array('custom',$AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';
$cgDateDescSortCheck = (in_array('date-desc',$AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';
$cgDateAscSortCheck = (in_array('date-asc',$AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';
$cgRateDescSortCheck = (in_array('rate-desc',$AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';
$cgRateAscSortCheck = (in_array('rate-asc',$AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';
$cgRateDescSumSortCheck = (in_array('rate-sum-desc', $AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';
$cgRateAscSumSortCheck = (in_array('rate-sum-asc', $AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';
$cgCommentDescSortCheck = (in_array('comment-desc',$AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';
$cgCommentAscSortCheck = (in_array('comment-asc',$AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';
$cgRandomSortCheck = (in_array('random',$AllowSortOptionsArrayCgGalleryEcommerce)) ? '' : 'cg_unchecked';


$cgGalleryEcommerceRatingInputs = 'cgGalleryNoVotingRatingInputs';
$cgGalleryEcommerceRatingDisabled = '';
$cgGalleryEcommerceRatingHidden = '';

if(empty($RatingVisibleForGalleryEcommerce)){
	$cgGalleryEcommerceRatingDisabled = 'cg_disabled';
	$cgGalleryEcommerceRatingHidden = 'cg_hide';
}

echo <<<HEREDOC
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='custom' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='date-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='date-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='rate-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='rate-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='rate-sum-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='rate-sum-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='comment-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='comment-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_ecommerce][visual][AllowSortOptionsArray][]' value='random' class='cg-allow-sort-input' />

        <div class="cgAllowSortOptionsContainer">
        <label class="cg-allow-sort-option $cgCustomSortCheck" data-cg-target="custom"><span class="cg-allow-sort-option-cat">Custom</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgDateDescSortCheck" data-cg-target="date-desc"><span class="cg-allow-sort-option-cat">Date desc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgDateAscSortCheck" data-cg-target="date-asc"><span class="cg-allow-sort-option-cat">Date asc</span><span class="cg-allow-sort-option-icon"></span></label>
            <label class="cg-allow-sort-option $cgRateDescSortCheck $cgGalleryEcommerceRatingDisabled $cgGalleryEcommerceRatingInputs" data-cg-target="rate-desc"><span class="cg-allow-sort-option-cat">Rating desc<br><small><strong>for one star voting</strong></small></span><span class="cg-allow-sort-option-icon "></span></label>
            <label class="cg-allow-sort-option $cgRateAscSortCheck $cgGalleryEcommerceRatingDisabled $cgGalleryEcommerceRatingInputs" data-cg-target="rate-asc"><span class="cg-allow-sort-option-cat">Rating asc<br><small><strong>for one star voting</strong></small></span><span class="cg-allow-sort-option-icon "></span></label>
           <label class="cg-allow-sort-option $cgRateDescSumSortCheck $cgGalleryEcommerceRatingDisabled $cgGalleryEcommerceRatingInputs" data-cg-target="rate-sum-desc"><span class="cg-allow-sort-option-cat">Rating sum desc</span><span class="cg-allow-sort-option-icon "></span></label>
        <label class="cg-allow-sort-option $cgRateAscSumSortCheck $cgGalleryEcommerceRatingDisabled $cgGalleryEcommerceRatingInputs" data-cg-target="rate-sum-asc"><span class="cg-allow-sort-option-cat">Rating sum asc</span><span class="cg-allow-sort-option-icon "></span></label>
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

    <div class='cg_view_option  cg_view_option_flex_flow_column cg_border_top_none PreselectSortContainer'>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
            <p>Preselect order<br>on page load<br>To make rating sort options available activate "Make current voting status visible" option above</span><span class="cgPreselectSortMessage cg_view_option_title_note">Random sort has to be deactivated</span></p>
        </div>
        <div class='cg_view_option_select'>
        <select name='multiple-pics[cg_gallery_ecommerce][pro][PreselectSort]' class='PreselectSort cg_no_outline_and_shadow_on_focus'>
HEREDOC;

$PreselectSort_custom_selected = ($jsonOptions[$GalleryID.'-ec']['pro']['PreselectSort']=='custom') ? 'selected' : '';
$PreselectSort_date_descend_selected = ($jsonOptions[$GalleryID.'-ec']['pro']['PreselectSort']=='date_descend') ? 'selected' : '';
$PreselectSort_date_ascend_selected = ($jsonOptions[$GalleryID.'-ec']['pro']['PreselectSort']=='date_ascend') ? 'selected' : '';
$PreselectSort_rating_descend_selected = ($jsonOptions[$GalleryID.'-ec']['pro']['PreselectSort']=='rating_descend') ? 'selected' : '';
$PreselectSort_rating_ascend_selected = ($jsonOptions[$GalleryID.'-ec']['pro']['PreselectSort']=='rating_ascend') ? 'selected' : '';
$PreselectSort_rating_sum_descend_selected = ($jsonOptions[$GalleryID.'-ec']['pro']['PreselectSort']=='rating_sum_descend') ? 'selected' : '';
$PreselectSort_rating_sum_ascend_selected = ($jsonOptions[$GalleryID.'-ec']['pro']['PreselectSort']=='rating_sum_ascend') ? 'selected' : '';
$PreselectSort_comments_descend_selected = ($jsonOptions[$GalleryID.'-ec']['pro']['PreselectSort']=='comments_descend') ? 'selected' : '';
$PreselectSort_comments_ascend_selected = ($jsonOptions[$GalleryID.'-ec']['pro']['PreselectSort']=='comments_ascend') ? 'selected' : '';

echo <<<HEREDOC
        <option value='custom' $PreselectSort_custom_selected>Custom</option>
        <option value='date_descend' $PreselectSort_date_descend_selected>Date descending</option>
        <option value='date_ascend' $PreselectSort_date_ascend_selected>Date ascending</option>
        <option value='rating_descend' class='$cgGalleryEcommerceRatingHidden $cgGalleryEcommerceRatingInputs' $PreselectSort_rating_descend_selected >Rating descending (for one star voting)</option>
        <option value='rating_ascend' class='$cgGalleryEcommerceRatingHidden $cgGalleryEcommerceRatingInputs'  $PreselectSort_rating_ascend_selected >Rating ascending (for one star voting)</option>
        <option value='rating_sum_descend' class='$cgGalleryEcommerceRatingHidden $cgGalleryEcommerceRatingInputs' $PreselectSort_rating_sum_descend_selected>Rating sum descending (for multiple stars voting)</option>
        <option value='rating_sum_ascend' class='$cgGalleryEcommerceRatingHidden $cgGalleryEcommerceRatingInputs' $PreselectSort_rating_sum_ascend_selected>Rating sum ascending (for multiple stars voting)</option>
        <option value='comments_descend' $PreselectSort_comments_descend_selected>Comments descending</option>
        <option value='comments_ascend' $PreselectSort_comments_ascend_selected>Comments ascending</option>
        </select>
        </div>

    </div>

    <div class='cg_view_option  cg_border_top_right_left_none RandomSortContainer'>
        <div class='cg_view_option_title'>
            <p>Random sort<br><span class="cg_view_option_title_note">Each page load.<br>Random sort option<br>will be preselected<br>if allow sort is activated.</span></p>
        </div>
        <div  class='cg_view_option_checkbox'>
            <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][RandomSort]" class="cg_shortcode_checkbox RandomSort" checked="{$jsonOptions["$GalleryID-ec"]["general"]["RandomSort"]}"><br/>
        </div>
    </div>

    <div class='cg_view_option  cg_border_top_none'>
        <div class='cg_view_option_title'>
            <p>Random sort button</p>
        </div>
        <div  class='cg_view_option_checkbox'>
            <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][RandomSortButton]" class="cg_shortcode_checkbox RandomSortButton"  checked="{$jsonOptions["$GalleryID-ec"]["general"]["RandomSortButton"]}"><br/>
        </div>
    </div>

</div>

HEREDOC;

if(empty($jsonOptions[$GalleryID.'-ec'])){
	$FeControlsStyleWhiteChecked = 'checked';
}else{
	$FeControlsStyleWhiteChecked = ($jsonOptions[$GalleryID.'-ec']['visual']['FeControlsStyle']=='white') ? 'checked' : '';
}

if(empty($jsonOptions[$GalleryID.'-ec'])){
	$FeControlsStyleBlackChecked = '';
}else{
	$FeControlsStyleBlackChecked = ($jsonOptions[$GalleryID.'-ec']['visual']['FeControlsStyle']=='black') ? 'checked' : '0';
}

// add BorderRadius here
if (!isset($jsonOptions[$GalleryID.'-ec']['visual']['BorderRadius'])) {
	if(!empty($BorderRadius)){
		$jsonOptions[$GalleryID.'-ec']['visual']['BorderRadius'] = 1;
	}else{
		$jsonOptions[$GalleryID.'-ec']['visual']['BorderRadius'] = 0;
	}
}

if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['EnableSwitchStyleGalleryButton'])){
	$jsonOptions[$GalleryID.'-ec']['visual']['EnableSwitchStyleGalleryButton'] = 0;
}

if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['SwitchStyleGalleryButtonOnlyTopControls'])){
	$jsonOptions[$GalleryID.'-ec']['visual']['SwitchStyleGalleryButtonOnlyTopControls'] = 0;
}


echo <<<HEREDOC
<div class="cg_view_options_row">
        <div class="cg_view_option  cg_view_option_100_percent cg_border_top_none" id="BorderRadiusContainer">
            <div class="cg_view_option_title">
                <p>Round borders for all control elements and containers</p>
            </div>
            <div class="cg_view_option_checkbox cg_view_option_checked">
                <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][BorderRadius]" class="cg_shortcode_checkbox BorderRadius" checked="{$jsonOptions[$GalleryID.'-ec']['visual']['BorderRadius']}">
            </div>
        </div>
</div>
<div class='cg_go_to_target' data-cg-go-to-target="TopControlsStyleContainer" >
<div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_top_bottom_none'>
                    <div class='cg_view_option_title'>
                    <p>Gallery color style</p>
                    </div>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Bright style
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][visual][FeControlsStyle]" class="FeControlsStyleWhite cg_view_option_radio_multiple_input_field" $FeControlsStyleWhiteChecked value="white"/>
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Dark style
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][visual][FeControlsStyle]" class="FeControlsStyleBlack cg_view_option_radio_multiple_input_field" $FeControlsStyleBlackChecked value="black">
                            </div>
                        </div>
                </div>
            </div>
</div>
 <div class='cg_view_options_row'>
            <div class='cg_view_option   cg_view_option_100_percent cg_border_top_none EnableSwitchStyleGalleryButtonContainer'>
                <div class='cg_view_option_title'>
                    <p>Enable switch color style button<br><span class="cg_view_option_title_note">Will also switch style of opened entry view</span></p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][EnableSwitchStyleGalleryButton]" checked="{$jsonOptions[$GalleryID.'-ec']['visual']['EnableSwitchStyleGalleryButton']}" class="cg_shortcode_checkbox EnableSwitchStyleGalleryButton">
                </div>
            </div>
</div>
</div>
HEREDOC;

// only json option, not in database available
if(!empty($jsonOptions[$GalleryID.'-ec']['visual']['ShowDate'])){
	$ShowDate = '1';
}else{
	$ShowDate = '0';
}

echo <<<HEREDOC
        <div class="cg_view_options_row">
                <div class="cg_view_option cg_view_option_50_percent cg_border_top_none cg_border_right_none" >
                    <div class="cg_view_option_title">
                        <p>Show date since added/uploaded to gallery</p>
                    </div>
                    <div class="cg_view_option_checkbox cg_view_option_checked">
                        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][ShowDate]" class="cg_shortcode_checkbox" checked="$ShowDate">
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
        <select name="multiple-pics[cg_gallery_ecommerce][visual][ShowDateFormat]" class="cg_no_outline_and_shadow_on_focus">
HEREDOC;

// only json option, not in database available
if(!empty($jsonOptions[$GalleryID.'-ec']['visual']['ShowDateFormat'])){
	$ShowDateFormat = $jsonOptions[$GalleryID.'-ec']['visual']['ShowDateFormat'];
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
    <div class='cg_view_option  cg_view_option_100_percent cg_border_top_none GalleryUploadContainer'>
        <div class='cg_view_option_title'>
            <p>In gallery contact form button<br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE:</span> not available for cg_gallery_ecommerce shortcode</span></p>
        </div>
        <div class='cg_view_option_checkbox cg_hide'>
HEREDOC;

if($isModernOptionsNew){
	$jsonOptions[$GalleryID.'-ec']['pro']['GalleryUpload'] = 0;
}

if(!empty($jsonOptions[$GalleryID.'-ec']['general']['ShowTextUntilAnImageAdded'])){
	$ShowTextUntilAnImageAdded = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-ec']['general']['ShowTextUntilAnImageAdded']);
}else{
	$ShowTextUntilAnImageAdded = '';
}

if(!isset($jsonOptions[$GalleryID.'-ec']['general']['ShowAlways']) && $ShowAlways == 'checked') {
	$jsonOptions[$GalleryID.'-ec']['general']['ShowAlways'] = 1;
} else if(!isset($jsonOptions[$GalleryID.'-ec']['general']['ShowAlways']) && $ShowAlways != 'checked') {
	$jsonOptions[$GalleryID.'-ec']['general']['ShowAlways'] = 0;
}

if(!isset($jsonOptions[$GalleryID.'-ec']['general']['RegUserGalleryOnly']) && $RegUserGalleryOnly == 'checked') {
	$jsonOptions[$GalleryID.'-ec']['general']['RegUserGalleryOnly'] = 1;
} else if(!isset($jsonOptions[$GalleryID.'-ec']['general']['RegUserGalleryOnly']) && $RegUserGalleryOnly != 'checked') {
	$jsonOptions[$GalleryID.'-ec']['general']['RegUserGalleryOnly'] = 0;
}


if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['RegUserGalleryOnlyText'])){
	$jsonOptions[$GalleryID.'-ec']['pro']['RegUserGalleryOnlyText'] = $RegUserGalleryOnlyText;
}else{
	$jsonOptions[$GalleryID.'-ec']['pro']['RegUserGalleryOnlyText'] = contest_gal1ery_convert_for_html_output($jsonOptions[$GalleryID.'-ec']['pro']['RegUserGalleryOnlyText']);
}

$ShowAlwaysContainer = '';
if(floatval($galleryDbVersion)<21){
	$ShowAlwaysContainer = <<<HEREDOC
    <div class='cg_view_options_row cg_go_to_target' data-cg-go-to-target="ShowAlwaysContainer">
<div class='cg_view_option  cg_view_option_100_percent cg_border_top_none'>
    <div class='cg_view_option_title'>
        <p>Show constantly (without hovering)<br>vote, comments and file title in gallery view<br><span class="cg_view_option_title_note">You see it by hovering if not activated.<br>File title can be configured in "Edit contact form" >>> "Show as title in gallery view".</span></p>
    </div>
    <div class='cg_view_option_checkbox'>
     <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][ShowAlways]" checked="{$jsonOptions[$GalleryID.'-ec']['general']['ShowAlways']}" class="cg_shortcode_checkbox">
    </div>
</div>
</div>
HEREDOC;
}



echo <<<HEREDOC
<input class='cg_shortcode_checkbox GalleryUpload' type='checkbox' name='multiple-pics[cg_gallery_ecommerce][pro][GalleryUpload]' checked="{$jsonOptions["$GalleryID-ec"]["pro"]["GalleryUpload"]}" >
        </div>
    </div>

    <div class='cg_view_option cg_hide  cg_view_option_50_percent cg_border_top_none'>
        <div class='cg_view_option_title cg_view_option_title_flex_flow_column'>
            <p>In gallery contact form text configuration</p>
            <a class="cg_no_outline_and_shadow_on_focus" href="#cgInGalleryUploadFormConfiguration"><p>Can be configured here...</p></a>
        </div>
    </div>


</div>


$ShowAlwaysContainer

<div class='cg_view_options_row'>
    <div class='cg_view_option  cg_view_option_full_width cg_border_top_none' id="wp-ShowTextUntilAnImageAddedGalleryEcommerce-wrap-Container">
        <div class='cg_view_option_title'>
            <p>This text is visible until first entry appears in the gallery<br><span class="cg_view_option_title_note">This text is visible until first entry is added, activated and so is visible in frontend.</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' id='ShowTextUntilAnImageAddedGalleryEcommerce'  name='multiple-pics[cg_gallery_ecommerce][general][ShowTextUntilAnImageAdded]'>$ShowTextUntilAnImageAdded</textarea>
        </div>
    </div>
</div>

 <div class='cg_view_options_row cg_go_to_target RegUserGalleryOnlyContainer' data-cg-go-to-target="RegUserGalleryOnlyContainer">
        <div class='cg_view_option $cgProFalse cg_view_option_100_percent cg_border_top_none $cgProFalse' >
            <div class='cg_view_option_title'>
                <p>Allow only registered users to see the gallery<br><span class="cg_view_option_title_note">User have to be registered and logged in to be able to see the gallery</span></p>
            </div>
            <div class='cg_view_option_checkbox'>
                <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][pro][RegUserGalleryOnly]" checked="{$jsonOptions[$GalleryID.'-ec']['pro']['RegUserGalleryOnly']}" class="cg_shortcode_checkbox RegUserGalleryOnly">
            </div>
        </div>
    </div>

    <div class='cg_view_options_row'>
        <div class='cg_view_option $cgProFalse cg_view_option_full_width cg_border_top_none $cgProFalse RegUserGalleryOnlyTextContainer' id="wp-RegUserGalleryOnlyEcommerceText-wrap-Container">
            <div class='cg_view_option_title'>
                <p>Show text instead of gallery</p>
            </div>
            <div class='cg_view_option_html'>
                <textarea class='cg-wp-editor-template' id='RegUserGalleryOnlyTextEcommerce'  name='multiple-pics[cg_gallery_ecommerce][pro][RegUserGalleryOnlyText]'>{$jsonOptions[$GalleryID.'-ec']['pro']['RegUserGalleryOnlyText']}</textarea>
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
        if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['ShowBackToGalleriesButton'])){
            $jsonOptions[$GalleryID.'-ec']['visual']['ShowBackToGalleriesButton'] = 1;
        }
        echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_100_percent  cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px  '>
            <div class='cg_view_option_title '>
                <p>Show back to galleries button on gallery landing page
                <br><span class="cg_view_option_title_note"><a href="$WpPageParentEcommercePermalink" target="_blank">$WpPageParentEcommercePermalink</a></span></p>
            </div>
        <div class='cg_view_option_checkbox '>
            <input type="checkbox" name='multiple-pics[cg_gallery_ecommerce][visual][ShowBackToGalleriesButton]'  class="cg_shortcode_checkbox ShowBackToGalleriesButton"  checked="{$jsonOptions[$GalleryID.'-ec']['visual']['ShowBackToGalleriesButton']}"  >
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

        if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleriesButtonURL'])){
            $jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleriesButtonURL'] = '';
        }else{
            $jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleriesButtonURL'] = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleriesButtonURL']);
        }

        $slugName = (!empty($CgEntriesOwnSlugNameGalleriesEcommerce)) ? $CgEntriesOwnSlugNameGalleriesEcommerce : 'contest-galleries-ecommerce';

        $page = get_page_by_path( $slugName, OBJECT, 'page');
        $pageGalleries = (!empty($page)) ? get_permalink($page->ID) : '';

        echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Back to galleries button custom URL on gallery landing page
                <br><span class="cg_view_option_title_note"><a href="$WpPageParentEcommercePermalink" target="_blank">$WpPageParentEcommercePermalink</a><br><span class="cg_view_option_title_note">If not set then parent site<br><a target="_blank"  href="$pageGalleries">$pageGalleries</a><br>URL will be used<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name="multiple-pics[cg_gallery_ecommerce][pro][BackToGalleriesButtonURL]" class="BackToGalleriesButtonURL"  value="{$jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleriesButtonURL']}"   >
            </div>
        </div>
    </div>
HEREDOC;

    }


    if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['WpPageParentRedirectURL'])){
        $jsonOptions[$GalleryID.'-ec']['pro']['WpPageParentRedirectURL'] = '';
    }else{
        $jsonOptions[$GalleryID.'-ec']['pro']['WpPageParentRedirectURL'] = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-ec']['pro']['WpPageParentRedirectURL']);
    }
    echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option  cg_view_option_full_width cg_border_top_none  '>
            <div class='cg_view_option_title '>
                <p>Redirect URL for parent site of Contest Gallery entries<br><span class="cg_view_option_title_note">The gallery page for cg_gallery_ecommerce id="$GalleryID" shortcode is<br><a target="_blank"  href="$WpPageParentEcommercePermalink">$WpPageParentEcommercePermalink</a><br>(But you can place the shortcode also on any other page)<br>If Redirect URL is set, then HTTP 301 redirect will be executed if the above URL gets called<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery_ecommerce][pro][WpPageParentRedirectURL]' class="WpPageParentRedirectURL"  value="{$jsonOptions[$GalleryID.'-ec']['pro']['WpPageParentRedirectURL']}"  >
            </div>
        </div>
    </div>
HEREDOC;

    // only json option, not in database available
    if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['AdditionalCssGalleryPage'])){
        $AdditionalCssGalleryPage = "body {\r\n&nbsp;&nbsp;font-family: sans-serif;\r\n&nbsp;&nbsp;font-size: 16px;\r\n&nbsp;&nbsp;background-color: white;\r\n&nbsp;&nbsp;color: black;\r\n}";
    }else{
        $AdditionalCssGalleryPage = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-ec']['visual']['AdditionalCssGalleryPage']);
    }

    echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option  cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Additional CSS cg_gallery page<br><span class="cg_view_option_title_note"><a target="_blank"  href="$WpPageParentEcommercePermalink">$WpPageParentEcommercePermalink</a></span></p>
            </div>
            <div class='cg_view_option_textarea' >
                <textarea type="text" name="multiple-pics[cg_gallery_ecommerce][visual][AdditionalCssGalleryPage]" rows="7" style="width:100%;" class="AdditionalCssGalleryPage"  >$AdditionalCssGalleryPage</textarea>
            </div>
        </div>
    </div>
HEREDOC;

    echo <<<HEREDOC
        </div>
HEREDOC;

}





//print_r($order);

$showSliderViewOption = false;
$showSliderViewOptionSet = false;

if(!in_array("SliderLookOrder",$order)){
	$showSliderViewOption = true;
}

$showBlogViewOption = false;
$showBlogViewOptionSet = false;

if(!in_array("BlogLookOrder",$order)){
	$showBlogViewOption = true;
}

$i = 0;

if(!empty($jsonOptions[$GalleryID.'-ec']['general']['ThumbLookOrder'])){
	$order = array();
	$order[$jsonOptions[$GalleryID.'-ec']['general']['ThumbLookOrder']] = 'ThumbLookOrder';
	$order[$jsonOptions[$GalleryID.'-ec']['general']['SliderLookOrder']] = 'SliderLookOrder';
	$order[$jsonOptions[$GalleryID.'-ec']['general']['HeightLookOrder']] = 'HeightLookOrder';
	$order[$jsonOptions[$GalleryID.'-ec']['general']['RowLookOrder']] = 'RowLookOrder';

	if(empty($jsonOptions[$GalleryID.'-ec']['visual']['BlogLookOrder'])){
		$jsonOptions[$GalleryID.'-ec']['visual']['BlogLookOrder'] = 5;
	}

	$order[$jsonOptions[$GalleryID.'-ec']['visual']['BlogLookOrder']] = 'BlogLookOrder';

	ksort($order);

}else{
	$order = $order;
}


// add BlogLook here
$jsonOptions[$GalleryID.'-ec']['visual']['BlogLook'] = (!empty($jsonOptions[$GalleryID.'-ec']['visual']['BlogLook'])) ? $jsonOptions[$GalleryID.'-ec']['visual']['BlogLook'] : 0;

echo <<<HEREDOC


<div class='cg_options_sortable'>

<p class='cg_options_sortable_title'>View options and order</p>

HEREDOC;

if($jsonOptions[$GalleryID.'-ec']['general']['RowLook']==1){
	$jsonOptions[$GalleryID.'-ec']['general']['RowLook'] = 0;
	$jsonOptions[$GalleryID.'-ec']['general']['HeightLook'] = 1;
}

// since version 15.0.0 $i = -1 so right order will be shown because row view is deprecated
$i = -1;

foreach ($order as $key => $value) {

	$i++;

	if ($value == "BlogLookOrder" or ($showBlogViewOption == true && $showBlogViewOptionSet == false)) {

		$showSliderViewOptionSet = true;

		echo <<<HEREDOC
        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
             <div class="cg_options_order">$i.</div>
              <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
               <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option  cg_view_option_100_percent BlogLookContainer cg_border_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="multiple-pics[cg_gallery_ecommerce][general][order][]" value="b" >
                                <p>Activate <u>Blog View</u></p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][BlogLook]" class="cg_shortcode_checkbox BlogLook" checked="{$jsonOptions[$GalleryID.'-ec']['visual']['BlogLook']}">
                         </div>
                    </div>
                </div>
            </div>
        </div>
HEREDOC;

	}

	if ($value == "SliderLookOrder" or ($showSliderViewOption == true && $showSliderViewOptionSet == false)) {

		$showSliderViewOptionSet = true;

		$jsonOptions[$GalleryID.'-ec']['visual']['SliderThumbNav'] = (!isset($jsonOptions[$GalleryID.'-ec']['visual']['SliderThumbNav'])) ? 1 : $jsonOptions[$GalleryID.'-ec']['visual']['SliderThumbNav'];

		echo <<<HEREDOC

        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
            <div class="cg_options_order">$i.</div>
            <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
                <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option  cg_view_options_and_order_checkbox_container cg_border_right_none cg_view_option_50_percent SliderLookContainer cg_border_border_bottom_left_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="multiple-pics[cg_gallery_ecommerce][general][order][]" value="s" >
                                <p>Activate <u>Slider View</u></p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][SliderLook]" class="cg_shortcode_checkbox SliderLook" checked="{$jsonOptions[$GalleryID.'-ec']['general']['SliderLook']}">
                         </div>
                    </div>
                    <div class='cg_view_option cg_view_option_50_percent SliderThumbNavContainer cg_border_border_top_right_radius_8_px cg_border_border_bottom_right_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <p>Enable thumbnail navigation</p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][SliderThumbNav]" class="cg_shortcode_checkbox SliderThumbNav" checked="{$jsonOptions[$GalleryID.'-ec']['visual']['SliderThumbNav']}" >
                         </div>
                    </div>
                </div>
        </div>
    </div>
HEREDOC;

	}

	if ($value == "ThumbLookOrder") {

		echo <<<HEREDOC
        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
            <div class="cg_options_order">$i.</div>
            <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
                <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option  cg_view_option_100_percent ThumbLookContainer cg_border_radius_8_px'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="multiple-pics[cg_gallery_ecommerce][general][order][]" value="t" >
                                <p>Activate <u>Masonry View</u></p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][ThumbLook]" class="cg_shortcode_checkbox ThumbLook" checked="{$jsonOptions[$GalleryID.'-ec']['general']['ThumbLook']}">
                         </div>
                    </div>
                </div>
                <div class='cg_view_options_row cg_hide'>
                    <div class='cg_view_option  cg_border_right_none WidthThumbContainer'>
                        <div class='cg_view_option_title'>
                                <p>Width thumbs (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="3" name="multiple-pics[cg_gallery_ecommerce][general][WidthThumb]" class="WidthThumb" value="{$jsonOptions[$GalleryID.'-ec']['general']['WidthThumb']}" >
                         </div>
                    </div>
                    <div class='cg_view_option  HeightThumbContainer'>
                        <div class='cg_view_option_title'>
                                <p>Height thumbs (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="3" name="multiple-pics[cg_gallery_ecommerce][general][HeightThumb]" class="HeightThumb" value="{$jsonOptions[$GalleryID.'-ec']['general']['HeightThumb']}" >
                         </div>
                    </div>
                </div>
                <div class='cg_view_options_row cg_hide'>
                    <div class='cg_view_option  cg_view_option_50_percent cg_border_top_right_none DistancePicsContainer'>
                        <div class='cg_view_option_title'>
                                <p>Distance between thumbs horizontal (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="multiple-pics[cg_gallery_ecommerce][general][DistancePics]" class="DistancePics" value="{$jsonOptions[$GalleryID.'-ec']['general']['DistancePics']}">
                         </div>
                    </div>
                    <div class='cg_view_option  cg_view_option_50_percent cg_border_top_none DistancePicsVContainer'>
                        <div class='cg_view_option_title'>
                                <p>Distance between thumbs vertical (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="multiple-pics[cg_gallery_ecommerce][general][DistancePicsV]" class="DistancePicsV" value="{$jsonOptions[$GalleryID.'-ec']['general']['DistancePicsV']}">
                         </div>
                    </div>
                </div>
        </div>
    </div>
HEREDOC;
	}

	if ($value == "HeightLookOrder") {

		echo <<<HEREDOC
        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
            <div class="cg_options_order">$i.</div>
            <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
                <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option  cg_border_right_none cg_view_option_50_percent HeightLookContainer'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="multiple-pics[cg_gallery_ecommerce][general][order][]" value="h" >
                                <p>Activate <u>Height View</u></p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][HeightLook]" class="cg_shortcode_checkbox HeightLook" checked="{$jsonOptions[$GalleryID.'-ec']['general']['HeightLook']}">
                         </div>
                    </div>
                    <div class='cg_view_option  cg_view_option_50_percent HeightLookHeightContainer'>
                        <div class='cg_view_option_title'>
                                <p>Height of pics in a row (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="3" name="multiple-pics[cg_gallery_ecommerce][general][HeightLookHeight]" class="HeightLookHeight" value="{$jsonOptions[$GalleryID.'-ec']['general']['HeightLookHeight']}" >
                         </div>
                    </div>
                </div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option  cg_view_option_50_percent cg_border_top_right_none HeightViewSpaceWidthContainer'>
                        <div class='cg_view_option_title'>
                                <p>Horizontal distance between files (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="multiple-pics[cg_gallery_ecommerce][visual][HeightViewSpaceWidth]" class="HeightViewSpaceWidth" value="{$jsonOptions[$GalleryID.'-ec']['visual']['HeightViewSpaceWidth']}">
                         </div>
                    </div>
                    <div class='cg_view_option  cg_view_option_50_percent cg_border_top_none HeightViewSpaceHeightContainer'>
                        <div class='cg_view_option_title'>
                                <p>Vertical distance between files (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="multiple-pics[cg_gallery_ecommerce][visual][HeightViewSpaceHeight]" class="HeightViewSpaceHeight" value="{$jsonOptions[$GalleryID.'-ec']['visual']['HeightViewSpaceHeight']}">
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

		echo <<<HEREDOC
        <div class='cg_options_sortableContainer'>
            <div class='cg_options_sortableDiv'>
                <div class="cg_options_order">$i.</div>
                <div class="cg_options_order_change_order cg_move_view_to_bottom"><i></i></div>
                <div class="cg_options_order_change_order cg_move_view_to_top"><i></i></div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option  cg_border_right_none cg_view_option_50_percent RowLookContainer $rowViewDeprecatedDisabled'>
                        <div class='cg_view_option_title'>
                                <input type="hidden" name="multiple-pics[cg_gallery_ecommerce][general][order][]" value="r" >
                                <p>Activate <u>Row View</u><br><span class="cg_font_weight_normal">(Same amount of files in each row)$rowViewDeprecatedText</span></p>
                         </div>
                         <div  class='cg_view_option_checkbox'>
                            <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][RowLook]" class="cg_shortcode_checkbox RowLook" checked="{$jsonOptions[$GalleryID.'-ec']['general']['RowLook']}">
                         </div>
                    </div>
                    <div class='cg_view_option  cg_view_option_50_percent PicsInRowContainer'>
                        <div class='cg_view_option_title'>
                                <p>Number of pics in a row</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="multiple-pics[cg_gallery_ecommerce][general][PicsInRow]" class="PicsInRow" value="{$jsonOptions[$GalleryID.'-ec']['general']['PicsInRow']}" >
                         </div>
                    </div>
                </div>
                <div class='cg_view_options_row'>
                    <div class='cg_view_option  cg_view_option_50_percent cg_border_top_right_none RowViewSpaceWidthContainer'>
                        <div class='cg_view_option_title'>
                                <p>Horizontal distance between files (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="multiple-pics[cg_gallery_ecommerce][visual][RowViewSpaceWidth]" class="RowViewSpaceWidth" value="{$jsonOptions[$GalleryID.'-ec']['visual']['RowViewSpaceWidth']}">
                         </div>
                    </div>
                    <div class='cg_view_option  cg_view_option_50_percent cg_border_top_none RowViewSpaceHeightContainer'>
                        <div class='cg_view_option_title'>
                                <p>Vertical distance between files (px)</p>
                         </div>
                         <div  class='cg_view_option_input'>
                            <input type="text" maxlength="2" name="multiple-pics[cg_gallery_ecommerce][visual][RowViewSpaceHeight]" class="RowViewSpaceHeight" value="{$jsonOptions[$GalleryID.'-ec']['visual']['RowViewSpaceHeight']}">
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

if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['MainTitleGalleriesView'])){
	$MainTitleGalleriesView = '';
}else{
	$MainTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-ec']['pro']['MainTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row' style="margin-top: -15px;">
        <div class='cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px  cg_border_border_top_right_radius_8_px cg_go_to_target' data-cg-go-to-target="MainTitleGalleriesViewArea">
            <div class='cg_view_option_title '>
                <p>Main title cg_galleries_ecommerce view</p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery_ecommerce][pro][MainTitleGalleriesView]' class="MainTitleGalleriesView"  value="$MainTitleGalleriesView"  >
            </div>
        </div>
    </div>
HEREDOC;

if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['SubTitleGalleriesView'])){
	$SubTitleGalleriesView = '';
}else{
	$SubTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-ec']['pro']['SubTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_go_to_target' data-cg-go-to-target="SubTitleGalleriesViewArea">
            <div class='cg_view_option_title '>
                <p>Sub title cg_galleries_ecommerce view</p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery_ecommerce][pro][SubTitleGalleriesView]' class="SubTitleGalleriesView"  value="$SubTitleGalleriesView"  >
            </div>
        </div>
    </div>
HEREDOC;

if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['ThirdTitleGalleriesView'])){
	$ThirdTitleGalleriesView = '';
}else{
	$ThirdTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-ec']['pro']['ThirdTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none  cg_border_border_bottom_left_radius_8_px  cg_border_border_bottom_right_radius_8_px cg_go_to_target $cgProFalse' data-cg-go-to-target="ThirdTitleGalleriesViewArea">
            <div class='cg_view_option_title '>
                <p>Third title cg_galleries_ecommerce view
                <br>
                <span class="cg_view_option_title_note"><b>NOTE:</b> perfect to use as description, 20 text rows will be displayed as preview in masonry galleries view</span></p>
            </div>
            <div class='cg_view_option_input '>
                <textarea type="text" name='multiple-pics[cg_gallery_ecommerce][pro][ThirdTitleGalleriesView]' class="ThirdTitleGalleriesView"  rows="5" style="width:100%;"  >$ThirdTitleGalleriesView</textarea>
            </div>
        </div>
    </div>
HEREDOC;

echo "<br><br>";

echo <<<HEREDOC
</div>
HEREDOC;
