<?php


echo <<<HEREDOC
<div class="cg_view cgMultiplePicsOptions cg_short_code_multiple_pics_configuration_cg_gallery_user_container cg_short_code_multiple_pics_configuration_container cg_hide cgViewHelper1">

<div class='cg_view_container'>

HEREDOC;


if(!empty($jsonOptions[$GalleryID.'-u']['visual']['AllowSortOptions'])){
    $AllowSortOptionsArrayCgGalleryUser = explode(',',$jsonOptions[$GalleryID.'-u']['visual']['AllowSortOptions']);
}else{
    $AllowSortOptionsArrayCgGalleryUser = array();
}


// since 29.0.0 no full window and full screen
echo <<<HEREDOC
<div class='cg_view_options_row cg_hide'>
    <div  class='cg_view_option cg_border_left_right_none'>
        <div class='cg_view_option_title'>
        <p>Enable full window button</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_user][general][FullSizeGallery]" class="cg_shortcode_checkbox FullSizeGallery" checked="{$jsonOptions["$GalleryID-u"]["general"]["FullSizeGallery"]}"><br/>
        </div>
    </div>
    <div  class='cg_view_option'>
        <div  class='cg_view_option_title'>
        <p>Enable full screen button<br><span class="cg_view_option_title_note">Will appear when joining full window</span></p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_user][general][FullSize]" class="cg_shortcode_checkbox FullSize" checked="{$jsonOptions["$GalleryID-u"]["general"]["FullSize"]}"><br/>
        </div>
    </div>
</div>

HEREDOC;

if(!empty($jsonOptions[$GalleryID.'-u']['general']['ThumbLookOrder'])){
    $order = array();
    $order[$jsonOptions[$GalleryID.'-u']['general']['ThumbLookOrder']] = 'ThumbLookOrder';
    $order[$jsonOptions[$GalleryID.'-u']['general']['SliderLookOrder']] = 'SliderLookOrder';
    $order[$jsonOptions[$GalleryID.'-u']['general']['HeightLookOrder']] = 'HeightLookOrder';
    $order[$jsonOptions[$GalleryID.'-u']['general']['RowLookOrder']] = 'RowLookOrder';

    if(empty($jsonOptions[$GalleryID.'-u']['visual']['BlogLookOrder'])){
        $jsonOptions[$GalleryID.'-u']['visual']['BlogLookOrder'] = 5;
    }

    $order[$jsonOptions[$GalleryID.'-u']['visual']['BlogLookOrder']] = 'BlogLookOrder';

    ksort($order);
}

$jsonOptions[$GalleryID.'-u']['visual']['BlogLook'] = (!empty($jsonOptions[$GalleryID.'-u']['visual']['BlogLook'])) ? $jsonOptions[$GalleryID.'-u']['visual']['BlogLook'] : 0;
$jsonOptions[$GalleryID.'-u']['visual']['SliderThumbNav'] = (!isset($jsonOptions[$GalleryID.'-u']['visual']['SliderThumbNav'])) ? 1 : $jsonOptions[$GalleryID.'-u']['visual']['SliderThumbNav'];

if($jsonOptions[$GalleryID.'-u']['general']['RowLook']==1){
    $jsonOptions[$GalleryID.'-u']['general']['RowLook'] = 0;
    $jsonOptions[$GalleryID.'-u']['general']['HeightLook'] = 1;
}

cg1l_correct_view_options_and_order($order,$jsonOptions[$GalleryID.'-u']['general']['ThumbLook'],$jsonOptions[$GalleryID.'-u']['general']['SliderLook'],$jsonOptions[$GalleryID.'-u']['visual']['BlogLook'],$jsonOptions[$GalleryID.'-u']['general']['HeightLook'],$jsonOptions[$GalleryID.'-u']['general']['RowLook'], true);

echo <<<HEREDOC
<div class='cg_view_options_row cg_hide'>
    <div class='cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px cg_border_bottom_none' style="padding-bottom:20px;">
        <div class='cg_view_option_title'>
            <p>Gallery view<br><span class="cg_view_option_title_note">Select how entries should be displayed in gallery</span></p>
        </div>
        <input type="hidden" name="multiple-pics[cg_gallery_user][general][order][]" value="t" />
        <input type="hidden" name="multiple-pics[cg_gallery_user][general][order][]" value="s" />
        <input type="hidden" name="multiple-pics[cg_gallery_user][general][order][]" value="b" />
        <div class='cg_view_option_radio_multiple'>
            <div class='cg_view_option_radio_multiple_container ThumbLookContainer cg_one_third_width'>
                <div class='cg_view_option_radio_multiple_title'>
                    Activate Masonry View
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="multiple-pics[cg_gallery_user][general][ThumbLook]" class="OrderLook cg_view_option_radio_multiple_input_field" checked="{$jsonOptions[$GalleryID.'-u']['general']['ThumbLook']}">
                </div>
            </div>
            <div class='cg_view_option_radio_multiple_container SliderLookContainer cg_one_third_width'>
                <div class='cg_view_option_radio_multiple_title'>
                    Activate Slider View
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="multiple-pics[cg_gallery_user][general][SliderLook]" class="OrderLook cg_view_option_radio_multiple_input_field" checked="{$jsonOptions[$GalleryID.'-u']['general']['SliderLook']}">
                </div>
            </div>
            <div class='cg_view_option_radio_multiple_container BlogLookContainer cg_one_third_width'>
                <div class='cg_view_option_radio_multiple_title'>
                    Activate Blog View
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="multiple-pics[cg_gallery_user][visual][BlogLook]" class="OrderLook cg_view_option_radio_multiple_input_field" checked="{$jsonOptions[$GalleryID.'-u']['visual']['BlogLook']}">
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
            <input type="checkbox" name="multiple-pics[cg_gallery_user][visual][SliderThumbNav]" class="cg_shortcode_checkbox SliderThumbNav" checked="{$jsonOptions[$GalleryID.'-u']['visual']['SliderThumbNav']}">
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
        <div class='cg_view_option_title '>
        <p>Number of entries per screen<br><span class="cg_view_option_title_note">Pagination</span></p>
        </div>
        <div class='cg_view_option_input'>
        <input type="text" name="multiple-pics[cg_gallery_user][general][PicsPerSite]" class="PicsPerSite" maxlength="3" value="{$jsonOptions["$GalleryID-u"]["general"]["PicsPerSite"]}">
        </div>
    </div>
    <div  class='cg_view_option cg_border_right_bottom_none'>
        <div class='cg_view_option_title'>
        <p>Allow search for files<br/><span class="cg_view_option_title_note">Search by fields content, categories, file name or EXIF data - if available</span></p>
        </div>
        <div  class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_user][pro][Search]" class="cg_shortcode_checkbox Search" checked="{$jsonOptions["$GalleryID-u"]["pro"]["Search"]}">
        </div>
    </div>
    <div  class='cg_view_option cg_border_bottom_none cg_border_border_top_right_radius_8_px AllowSortContainer'>
        <div class='cg_view_option_title'>
        <p>Allow sort<br/><span class="cg_view_option_title_note">Order by rating is not available if <br>"Show only user votes" or <br>"Hide voting until user vote" is activated</span></p>
        </div>
        <div  class='cg_view_option_checkbox'>
                <input type="checkbox" name="multiple-pics[cg_gallery_user][general][AllowSort]" class="cg_shortcode_checkbox AllowSort" checked="{$jsonOptions["$GalleryID-u"]["general"]["AllowSort"]}">
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

$cgDateDescSortCheck = (in_array('date-desc',$AllowSortOptionsArrayCgGalleryUser)) ? '' : 'cg_unchecked';
$cgDateAscSortCheck = (in_array('date-asc',$AllowSortOptionsArrayCgGalleryUser)) ? '' : 'cg_unchecked';
$cgRateDescSortCheck = (in_array('rate-desc',$AllowSortOptionsArrayCgGalleryUser)) ? '' : 'cg_unchecked';
$cgRateAscSortCheck = (in_array('rate-asc',$AllowSortOptionsArrayCgGalleryUser)) ? '' : 'cg_unchecked';
$cgRateDescSumSortCheck = (in_array('rate-sum-desc', $AllowSortOptionsArrayCgGalleryUser)) ? '' : 'cg_unchecked';
$cgRateAscSumSortCheck = (in_array('rate-sum-asc', $AllowSortOptionsArrayCgGalleryUser)) ? '' : 'cg_unchecked';
$cgCommentDescSortCheck = (in_array('comment-desc',$AllowSortOptionsArrayCgGalleryUser)) ? '' : 'cg_unchecked';
$cgCommentAscSortCheck = (in_array('comment-asc',$AllowSortOptionsArrayCgGalleryUser)) ? '' : 'cg_unchecked';
$cgRandomSortCheck = (in_array('random',$AllowSortOptionsArrayCgGalleryUser)) ? '' : 'cg_unchecked';


echo <<<HEREDOC
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='custom' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='date-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='date-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='rate-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='rate-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='rate-sum-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='rate-sum-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='comment-desc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='comment-asc' class='cg-allow-sort-input' />
        <input type='hidden' name='multiple-pics[cg_gallery_user][visual][AllowSortOptionsArray][]' value='random' class='cg-allow-sort-input' />

        <div class="cgAllowSortOptionsContainer">
        <label class="cg-allow-sort-option $cgDateDescSortCheck" data-cg-target="date-desc"><span class="cg-allow-sort-option-cat">Date desc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgDateAscSortCheck" data-cg-target="date-asc"><span class="cg-allow-sort-option-cat">Date asc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateDescSortCheck" data-cg-target="rate-desc"><span class="cg-allow-sort-option-cat">Rating desc<br><small><strong>for one star voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateAscSortCheck" data-cg-target="rate-asc"><span class="cg-allow-sort-option-cat">Rating asc<br><small><strong>for one star voting</strong></small></span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateDescSumSortCheck" data-cg-target="rate-sum-desc"><span  style="margin-left: 3px;" class="cg-allow-sort-option-cat">Rating sum desc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRateAscSumSortCheck" data-cg-target="rate-sum-asc"><span  style="margin-left: 3px;" class="cg-allow-sort-option-cat">Rating sum asc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgCommentDescSortCheck" data-cg-target="comment-desc"><span class="cg-allow-sort-option-cat">Comments desc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgCommentAscSortCheck" data-cg-target="comment-asc"><span class="cg-allow-sort-option-cat">Comments asc</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgRandomSortCheck" data-cg-target="random"><span class="cg-allow-sort-option-cat">Random</span><span class="cg-allow-sort-option-icon"></span></label>
        <label class="cg-allow-sort-option $cgCustomSortCheck cg_disabled" data-cg-target="custom"><span class="cg-allow-sort-option-cat">Custom</span><span class="cg-allow-sort-option-icon cg_hide"></span></label>
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
        <select name='multiple-pics[cg_gallery_user][pro][PreselectSort]' class='PreselectSort cg_no_outline_and_shadow_on_focus'>
HEREDOC;


$PreselectSort_date_descend_selected = ($jsonOptions[$GalleryID.'-u']['pro']['PreselectSort']=='date_descend') ? 'selected' : '';
$PreselectSort_date_ascend_selected = ($jsonOptions[$GalleryID.'-u']['pro']['PreselectSort']=='date_ascend') ? 'selected' : '';
$PreselectSort_rating_descend_selected = ($jsonOptions[$GalleryID.'-u']['pro']['PreselectSort']=='rating_descend') ? 'selected' : '';
$PreselectSort_rating_ascend_selected = ($jsonOptions[$GalleryID.'-u']['pro']['PreselectSort']=='rating_ascend') ? 'selected' : '';
$PreselectSort_rating_sum_descend_selected = ($jsonOptions[$GalleryID.'-u']['pro']['PreselectSort']=='rating_sum_descend') ? 'selected' : '';
$PreselectSort_rating_sum_ascend_selected = ($jsonOptions[$GalleryID.'-u']['pro']['PreselectSort']=='rating_sum_ascend') ? 'selected' : '';
$PreselectSort_comments_descend_selected = ($jsonOptions[$GalleryID.'-u']['pro']['PreselectSort']=='comments_descend') ? 'selected' : '';
$PreselectSort_comments_ascend_selected = ($jsonOptions[$GalleryID.'-u']['pro']['PreselectSort']=='comments_ascend') ? 'selected' : '';


echo <<<HEREDOC
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
            <input type="checkbox" name="multiple-pics[cg_gallery_user][general][RandomSort]" class="cg_shortcode_checkbox RandomSort" checked="{$jsonOptions["$GalleryID-u"]["general"]["RandomSort"]}"><br/>
        </div>
    </div>

    <div class='cg_view_option cg_border_top_none'>
        <div class='cg_view_option_title'>
            <p>Random sort button</p>
        </div>
        <div  class='cg_view_option_checkbox'>
            <input type="checkbox" name="multiple-pics[cg_gallery_user][general][RandomSortButton]" class="cg_shortcode_checkbox RandomSortButton"  checked="{$jsonOptions["$GalleryID-u"]["general"]["RandomSortButton"]}"><br/>
        </div>
    </div>

</div>

HEREDOC;

if(empty($jsonOptions[$GalleryID.'-u'])){
    $FeControlsStyleWhiteChecked = 'checked';
}else{
    $FeControlsStyleWhiteChecked = ($jsonOptions[$GalleryID.'-u']['visual']['FeControlsStyle']=='white') ? 'checked' : '';
}

if(empty($jsonOptions[$GalleryID.'-u'])){
    $FeControlsStyleBlackChecked = '';
}else{
    $FeControlsStyleBlackChecked = ($jsonOptions[$GalleryID.'-u']['visual']['FeControlsStyle']=='black') ? 'checked' : '0';
}

if(!isset($jsonOptions[$GalleryID.'-u']['visual']['EnableSwitchStyleGalleryButton'])){
    $jsonOptions[$GalleryID.'-u']['visual']['EnableSwitchStyleGalleryButton'] = 0;
}

if(!isset($jsonOptions[$GalleryID.'-u']['visual']['SwitchStyleGalleryButtonOnlyTopControls'])){
    $jsonOptions[$GalleryID.'-u']['visual']['SwitchStyleGalleryButtonOnlyTopControls'] = 0;
}


echo <<<HEREDOC
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
                                <input type="radio" name="multiple-pics[cg_gallery_user][visual][FeControlsStyle]" class="FeControlsStyleWhite cg_view_option_radio_multiple_input_field" $FeControlsStyleWhiteChecked value="white"/>
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Dark style
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_user][visual][FeControlsStyle]" class="FeControlsStyleBlack cg_view_option_radio_multiple_input_field" $FeControlsStyleBlackChecked value="black">
                            </div>
                        </div>
                </div>
            </div>
</div>

 <div class='cg_view_options_row'>
            <div class='cg_view_option  cg_view_option_100_percent cg_border_top_none EnableSwitchStyleGalleryButtonContainer'>
                <div class='cg_view_option_title'>
                    <p>Enable switch color style button<br><span class="cg_view_option_title_note">Will also switch style of opened entry view</span></p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="multiple-pics[cg_gallery_user][visual][EnableSwitchStyleGalleryButton]" checked="{$jsonOptions[$GalleryID.'-u']['visual']['EnableSwitchStyleGalleryButton']}" class="cg_shortcode_checkbox EnableSwitchStyleGalleryButton">
                </div>
            </div>
</div>
</div>
HEREDOC;

// only json option, not in database available
if(isset($jsonOptions[$GalleryID.'-u']['visual']['ShowDate'])){
	$ShowDate = absint($jsonOptions[$GalleryID.'-u']['visual']['ShowDate']);
	if(!in_array($ShowDate,array(0,1,2,3),true)){
		$ShowDate = !empty($jsonOptions[$GalleryID.'-u']['visual']['ShowDate']) ? 1 : 0;
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
                        <p>Show date since added/uploaded to gallery</p>
                    </div>
                    <div class="cg_view_option_checkbox $ShowDateCheckboxClass">
                        <input type="checkbox" name="multiple-pics[cg_gallery_user][visual][ShowDate]" class="cg_shortcode_checkbox ShowDate" $ShowDateChecked>
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
        <select name="multiple-pics[cg_gallery_user][visual][ShowDateFormat]" class="cg_no_outline_and_shadow_on_focus">
HEREDOC;

// only json option, not in database available
if(!empty($jsonOptions[$GalleryID.'-u']['visual']['ShowDateFormat'])){
	$ShowDateFormat = $jsonOptions[$GalleryID.'-u']['visual']['ShowDateFormat'];
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
                    <input type="radio" name="multiple-pics[cg_gallery_user][visual][ShowDateView]" class="cg_view_option_radio_multiple_input_field" $ShowDateView1 value="1" />
                </div>
            </div>
            <div class='cg_view_option_radio_multiple_container cg_border_top_none'>
                <div class='cg_view_option_radio_multiple_title'>
                    Show in gallery view only
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="multiple-pics[cg_gallery_user][visual][ShowDateView]" class="cg_view_option_radio_multiple_input_field" $ShowDateView2 value="2" />
                </div>
            </div>
            <div class='cg_view_option_radio_multiple_container cg_border_top_none'>
                <div class='cg_view_option_radio_multiple_title'>
                    Show in entry view only
                </div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="multiple-pics[cg_gallery_user][visual][ShowDateView]" class="cg_view_option_radio_multiple_input_field" $ShowDateView3 value="3" />
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

if($isModernOptionsNew){
    $jsonOptions[$GalleryID.'-u']['pro']['GalleryUpload'] = 1;
}


if(!empty($jsonOptions[$GalleryID.'-u']['general']['ShowTextUntilAnImageAdded'])){
    $ShowTextUntilAnImageAdded = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['general']['ShowTextUntilAnImageAdded']);
}else{
    $ShowTextUntilAnImageAdded = '';
}

if(!isset($jsonOptions[$GalleryID.'-u']['general']['ShowAlways']) && $ShowAlways == 'checked') {
    $jsonOptions[$GalleryID.'-u']['general']['ShowAlways'] = 1;
} elseif(!isset($jsonOptions[$GalleryID.'-u']['general']['ShowAlways']) && $ShowAlways != 'checked') {
    $jsonOptions[$GalleryID.'-u']['general']['ShowAlways'] = 0;
}

if(!isset($jsonOptions[$GalleryID.'-u']['general']['RegUserGalleryOnly']) && $RegUserGalleryOnly == 'checked') {
    $jsonOptions[$GalleryID.'-u']['general']['RegUserGalleryOnly'] = 1;
} elseif(!isset($jsonOptions[$GalleryID.'-u']['general']['RegUserGalleryOnly']) && $RegUserGalleryOnly != 'checked') {
    $jsonOptions[$GalleryID.'-u']['general']['RegUserGalleryOnly'] = 0;
}

if(!isset($jsonOptions[$GalleryID.'-u']['pro']['RegUserGalleryOnlyText'])){
    $jsonOptions[$GalleryID.'-u']['pro']['RegUserGalleryOnlyText'] = $RegUserGalleryOnlyText;
}else{
    $jsonOptions[$GalleryID.'-u']['pro']['RegUserGalleryOnlyText'] = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['pro']['RegUserGalleryOnlyText']);
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
         <input type="checkbox" name="multiple-pics[cg_gallery_user][general][ShowAlways]" checked="{$jsonOptions[$GalleryID.'-u']['general']['ShowAlways']}" class="cg_shortcode_checkbox">
        </div>
    </div>
</div>
HEREDOC;
}



echo <<<HEREDOC
<input class='cg_shortcode_checkbox GalleryUpload' type='checkbox' name='multiple-pics[cg_gallery_user][pro][GalleryUpload]' checked="{$jsonOptions["$GalleryID-u"]["pro"]["GalleryUpload"]}" >
        </div>
    </div>

    <div class='cg_view_option cg_view_option_50_percent cg_border_top_none'>
        <div class='cg_view_option_title cg_view_option_title_flex_flow_column'>
            <p>In gallery upload form text configuration</p>
            <a class="cg_no_outline_and_shadow_on_focus" href="#cgInGalleryUploadFormConfiguration"><p>Can be configured here...</p></a>
        </div>
    </div>


</div>


$ShowAlwaysContainer

<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-ShowTextUntilAnImageAddedGalleryUser-wrap-Container">
        <div class='cg_view_option_title'>
            <p>This text is visible until first entry appears in the gallery<br><span class="cg_view_option_title_note">This text is visible until first entry is added, activated and so is visible in frontend.</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' id='ShowTextUntilAnImageAddedGalleryUser'  name='multiple-pics[cg_gallery_user][general][ShowTextUntilAnImageAdded]'>$ShowTextUntilAnImageAdded</textarea>
        </div>
    </div>
</div>

 <div class='cg_view_options_row cg_go_to_target RegUserGalleryOnlyContainer' data-cg-go-to-target="RegUserGalleryOnlyContainer">
        <div class='cg_view_option cg_view_option_100_percent cg_border_top_none' style="cursor:auto;">
            <div class='cg_view_option_title'>
                <p>Allow only registered users to see the gallery<br><span class="cg_view_option_title_note">cg_gallery_user shortcode can be used only for logged in users. Read info at the top.</span></p>
            </div>
        </div>
    </div>

    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none $cgProFalse RegUserGalleryOnlyUserContainer' id="wp-RegUserGalleryOnlyUserText-wrap-Container">
            <div class='cg_view_option_title'>
                <p>Show text if user is not logged in</p>
            </div>
            <div class='cg_view_option_html'>
                <textarea class='cg-wp-editor-template' id='RegUserGalleryOnlyTextUser'  name='multiple-pics[cg_gallery_user][pro][RegUserGalleryOnlyText]'>{$jsonOptions[$GalleryID.'-u']['pro']['RegUserGalleryOnlyText']}</textarea>
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
        if(!isset($jsonOptions[$GalleryID.'-u']['visual']['ShowBackToGalleriesButton'])){
            $jsonOptions[$GalleryID.'-u']['visual']['ShowBackToGalleriesButton'] = 1;
        }

        echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_100_percent  cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px  '>
            <div class='cg_view_option_title '>
                <p>Show back to galleries button on gallery landing page
                <br><span class="cg_view_option_title_note"><a href="$WpPageParentUserPermalink" target="_blank">$WpPageParentUserPermalink</a></span></p>
            </div>
        <div class='cg_view_option_checkbox '>
            <input type="checkbox" name='multiple-pics[cg_gallery_user][visual][ShowBackToGalleriesButton]'  class="cg_shortcode_checkbox ShowBackToGalleriesButton"  checked="{$jsonOptions[$GalleryID.'-u']['visual']['ShowBackToGalleriesButton']}"  >
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

        if(!isset($jsonOptions[$GalleryID.'-u']['pro']['BackToGalleriesButtonURL'])){
            $jsonOptions[$GalleryID.'-u']['pro']['BackToGalleriesButtonURL'] = '';
        }else{
            $jsonOptions[$GalleryID.'-u']['pro']['BackToGalleriesButtonURL'] = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['pro']['BackToGalleriesButtonURL']);
        }

        $slugName = (!empty($CgEntriesOwnSlugNameGalleriesUser)) ? $CgEntriesOwnSlugNameGalleriesUser : 'contest-galleries-user';

        $page = get_page_by_path( $slugName, OBJECT, 'page');
        $pageGalleries = (!empty($page)) ? get_permalink($page->ID) : '';

        echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Back to galleries button custom URL on gallery landing page
                <br><span class="cg_view_option_title_note"><a href="$WpPageParentUserPermalink" target="_blank">$WpPageParentUserPermalink</a><br><span class="cg_view_option_title_note">If not set then parent site<br><a target="_blank"  href="$pageGalleries">$pageGalleries</a><br>URL will be used<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name="multiple-pics[cg_gallery_user][pro][BackToGalleriesButtonURL]" class="BackToGalleriesButtonURL"  value="{$jsonOptions[$GalleryID.'-u']['pro']['BackToGalleriesButtonURL']}"   >
            </div>
        </div>
    </div>
HEREDOC;

    }

        if(!isset($jsonOptions[$GalleryID.'-u']['pro']['WpPageParentRedirectURL'])){
            $jsonOptions[$GalleryID.'-u']['pro']['WpPageParentRedirectURL'] = '';
        }else{
            $jsonOptions[$GalleryID.'-u']['pro']['WpPageParentRedirectURL'] = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['pro']['WpPageParentRedirectURL']);
        }
        echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none  '>
            <div class='cg_view_option_title '>
                <p>Redirect URL for parent site of Contest Gallery entries<br><span class="cg_view_option_title_note">The gallery page for cg_gallery_user id="$GalleryID" shortcode is<br><a target="_blank"  href="$WpPageParentUserPermalink">$WpPageParentUserPermalink</a><br>(But you can place the shortcode also on any other page)<br>If Redirect URL is set, then HTTP 301 redirect will be executed if the above URL gets called<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery_user][pro][WpPageParentRedirectURL]' class="WpPageParentRedirectURL"  value="{$jsonOptions[$GalleryID.'-u']['pro']['WpPageParentRedirectURL']}"  >
            </div>
        </div>
    </div>
HEREDOC;

// only json option, not in database available
        if(!isset($jsonOptions[$GalleryID.'-u']['visual']['AdditionalCssGalleryPage'])){
            $AdditionalCssGalleryPage = "body {\r\n&nbsp;&nbsp;font-family: sans-serif;\r\n&nbsp;&nbsp;font-size: 16px;\r\n&nbsp;&nbsp;background-color: white;\r\n&nbsp;&nbsp;color: black;\r\n}";
        }else{
            $AdditionalCssGalleryPage = cg_stripslashes_recursively($jsonOptions[$GalleryID.'-u']['visual']['AdditionalCssGalleryPage']);
        }

        echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Additional CSS cg_gallery page<br><span class="cg_view_option_title_note"><a target="_blank"  href="$WpPageParentUserPermalink">$WpPageParentUserPermalink</a></span></p>
            </div>
            <div class='cg_view_option_textarea' >
                <textarea type="text" name="multiple-pics[cg_gallery_user][visual][AdditionalCssGalleryPage]" rows="7" style="width:100%;" class="AdditionalCssGalleryPage"  >$AdditionalCssGalleryPage</textarea>
            </div>
</div>
    </div>
HEREDOC;

    // only json option, not in database available
    if(!isset($jsonOptions[$GalleryID.'-u']['visual']['HeaderWpPageParent'])){
        $HeaderWpPageParent = "";
    }else{
        $HeaderWpPageParent = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['visual']['HeaderWpPageParent']);
    }

    echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Header tracking code on gallery landing page<br><span class="cg_view_option_title_note">Paste your tracking scripts here —<br>for example Google Tag Manager, Google Analytics, or Meta Pixel.<br>The code will be added inside the &lt;head&gt; section of gallery landing page.</span></p>
            </div>
            <div class='cg_view_option_textarea' >
                <textarea type="text" name="multiple-pics[cg_gallery_user][visual][HeaderWpPageParent]" rows="7" style="width:100%;" class="HeaderWpPageParent"  >$HeaderWpPageParent</textarea>
            </div>
        </div>
    </div>
HEREDOC;

    // only json option, not in database available
    if(!isset($jsonOptions[$GalleryID.'-u']['visual']['TextBeforeWpPageParent'])){
        $TextBeforeWpPageParent = "";
    }else{
        $TextBeforeWpPageParent = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['visual']['TextBeforeWpPageParent']);
    }

    echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-TextBeforeWpPageParentUser-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on gallery landing page before gallery<br><span class="cg_view_option_title_note">Add general text or tracking code. &lt;noscript&gt; tags are also supported.<br>The code will be inserted inside the &lt;body&gt; section of gallery landing page.<br><span class="cg_font_weight_500">NOTE: </span>appears only on gallery landing page, not if cg_gallery... shortcode is used on another page.</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery_user][visual][TextBeforeWpPageParent]'  id='TextBeforeWpPageParentUser'>$TextBeforeWpPageParent</textarea>
        </div>
    </div>
</div>
HEREDOC;

    // only json option, not in database available
    if(!isset($jsonOptions[$GalleryID.'-u']['visual']['TextAfterWpPageParent'])){
        $TextAfterWpPageParent = "";
    }else{
        $TextAfterWpPageParent = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['visual']['TextAfterWpPageParent']);
    }

    echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-TextAfterWpPageParentUser-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on gallery landing page after gallery<br><span class="cg_view_option_title_note">Add general text or tracking code. &lt;noscript&gt; tags are also supported.<br>The code will be inserted inside the &lt;body&gt; section of gallery landing page.<br><span class="cg_font_weight_500">NOTE: </span>appears only on gallery landing page, not if cg_gallery... shortcode is used on another page.</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery_user][visual][TextAfterWpPageParent]'  id='TextAfterWpPageParentUser'>$TextAfterWpPageParent</textarea>
        </div>
    </div>
</div>
HEREDOC;


    echo <<<HEREDOC
        </div>
HEREDOC;

}



//print_r($order);


if(!isset($jsonOptions[$GalleryID.'-u']['pro']['MainTitleGalleriesView'])){
	$MainTitleGalleriesView = '';
}else{
	$MainTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['pro']['MainTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row' style="margin-top: 15px;">
        <div class='cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px  cg_border_border_top_right_radius_8_px cg_go_to_target' data-cg-go-to-target="MainTitleGalleriesViewArea">
            <div class='cg_view_option_title '>
                <p>Main title cg_galleries_user view</p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery_user][pro][MainTitleGalleriesView]' class="MainTitleGalleriesView"  value="$MainTitleGalleriesView"  >
            </div>
        </div>
    </div>
HEREDOC;

if(!isset($jsonOptions[$GalleryID.'-u']['pro']['SubTitleGalleriesView'])){
	$SubTitleGalleriesView = '';
}else{
	$SubTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['pro']['SubTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_go_to_target' data-cg-go-to-target="SubTitleGalleriesViewArea">
            <div class='cg_view_option_title '>
                <p>Sub title cg_galleries_user view</p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery_user][pro][SubTitleGalleriesView]' class="SubTitleGalleriesView"  value="$SubTitleGalleriesView"  >
            </div>
        </div>
    </div>
HEREDOC;

if(!isset($jsonOptions[$GalleryID.'-u']['pro']['ThirdTitleGalleriesView'])){
	$ThirdTitleGalleriesView = '';
}else{
	$ThirdTitleGalleriesView = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['pro']['ThirdTitleGalleriesView']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none  cg_border_border_bottom_left_radius_8_px  cg_border_border_bottom_right_radius_8_px cg_go_to_target $cgProFalse' data-cg-go-to-target="ThirdTitleGalleriesViewArea">
            <div class='cg_view_option_title '>
                <p>Third title cg_galleries_user view
                <br>
                <span class="cg_view_option_title_note"><b>NOTE:</b> perfect to use as description, 20 text rows will be displayed as preview in masonry galleries view</span></p>
            </div>
            <div class='cg_view_option_input '>
                <textarea type="text" name='multiple-pics[cg_gallery_user][pro][ThirdTitleGalleriesView]' class="ThirdTitleGalleriesView"  rows="5" style="width:100%;"  >$ThirdTitleGalleriesView</textarea>
            </div>
        </div>
    </div>
HEREDOC;

echo "<br><br>";

echo <<<HEREDOC
</div>
HEREDOC;
