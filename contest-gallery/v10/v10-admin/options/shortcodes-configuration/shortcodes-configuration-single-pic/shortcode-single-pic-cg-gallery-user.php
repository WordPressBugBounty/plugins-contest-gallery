<?php

echo <<<HEREDOC
<div  class="cg_view cgSinglePicOptions cg_short_code_single_pic_configuration_cg_gallery_user_container cg_short_code_single_pic_configuration_container cg_hide cgViewHelper2">
<div class='cg_view_container'>

HEREDOC;

$AllowGalleryScript = (!empty($jsonOptions[$GalleryID.'-u']['general']['AllowGalleryScript'])) ? 'checked' : '';
$SliderFullWindow = (!empty($jsonOptions[$GalleryID.'-u']['pro']['SliderFullWindow'])) ? 'checked' : '';
$BlogLookFullWindow = (!empty($jsonOptions[$GalleryID.'-u']['visual']['BlogLookFullWindow'])) ? 'checked' : '';
$ForwardToWpPageEntry = (!empty($jsonOptions[$GalleryID.'-u']['visual']['ForwardToWpPageEntry'])) ? 'checked' : '';

$gallerySlideOutDeprecated = '';

if(floatval($galleryDbVersion)<15.05){

    $gallerySlideOutDeprecated = '<br><span class="cg_view_option_title_note"><span class="cg_color_red">NOTE:</span> deprecated,<br>not available in future galleries</span>';

    echo <<<HEREDOC
<div class='cg_view_options_rows_container'>

        <p class='cg_view_options_rows_container_title'>Gallery slide out, slider view or blog view</p>
        
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width'>
                    <div class='cg_view_option_title'>
                        <p>Open single entry style<br><span class="cg_view_option_title_note">Select how an entry should be opened on click in a gallery</span></p>
                    </div>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container AllowGalleryScriptContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Gallery slide out$gallerySlideOutDeprecated
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_user][general][AllowGalleryScript]" class="AllowGalleryScript cg_view_option_radio_multiple_input_field"  $AllowGalleryScript  />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container SliderFullWindowContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Full window slider
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_user][pro][SliderFullWindow]" class="SliderFullWindow cg_view_option_radio_multiple_input_field"  $SliderFullWindow   />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container BlogLookFullWindowContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Full window blog view
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_user][visual][BlogLookFullWindow]" class="BlogLookFullWindow cg_view_option_radio_multiple_input_field"  $BlogLookFullWindow  />
                            </div>
                        </div>
                </div>
            </div>
        </div>
HEREDOC;

}else{

    $ForwardToWpPageEntryOptions = '';
    $TextBeforeWpPageEntryRow = '';
    $TextAfterWpPageEntryRow = '';
    $EventuallyPadding = '';
    $ForwardToWpPageEntryInNewTabOptions = '';
if(floatval($galleryDbVersion)>=21){
    $EventuallyPadding = 'padding-bottom:20px;';
    $ForwardToWpPageEntry = (!empty($jsonOptions[$GalleryID.'-u']['visual']['ForwardToWpPageEntry'])) ? 'checked' : '';
    $ForwardToWpPageEntryInNewTab = (!empty($jsonOptions[$GalleryID.'-u']['visual']['ForwardToWpPageEntryInNewTab'])) ? '1' : '0';
    $ForwardToWpPageEntryOptions = <<<HEREDOC
 <div class='cg_view_option_radio_multiple_container ForwardToWpPageEntryContainer'>
        <div class='cg_view_option_radio_multiple_title'>
            Forward to entry landing page
        </div>
        <div class='cg_view_option_radio_multiple_input'>
            <input type="radio" name="multiple-pics[cg_gallery_user][visual][ForwardToWpPageEntry]" class="ForwardToWpPageEntry cg_view_option_radio_multiple_input_field"  $ForwardToWpPageEntry  />
        </div>
</div>
HEREDOC;
    $ForwardToWpPageEntryInNewTabOptions = <<<HEREDOC
<div class="cg_view_options_row ForwardToWpPageEntryInNewTabContainer cg_hide">
         <div class='cg_view_option cg_view_option_100_percent cg_border_top_none  '>
                <div class='cg_view_option_title'>
                        <p>Forward to entry landing page in new tab</p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="multiple-pics[cg_gallery_user][visual][ForwardToWpPageEntryInNewTab]" class="ForwardToWpPageEntryInNewTab cg_shortcode_checkbox"  checked="$ForwardToWpPageEntryInNewTab"  />
                </div>
        </div>
</div>
HEREDOC;
}

    echo <<<HEREDOC
<div class='cg_view_options_rows_container'>
        <p class='cg_view_options_rows_container_title'>Gallery slide out, slider view or blog view</p>
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width  cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px'>
                    <div class='cg_view_option_title'>
                        <p>Open entry style<br><span class="cg_view_option_title_note">Select how a file image should be opened on click in a gallery</span></p>
                    </div>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container BlogLookFullWindowContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Full window blog view
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_user][visual][BlogLookFullWindow]" class="BlogLookFullWindow cg_view_option_radio_multiple_input_field"  $BlogLookFullWindow  />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container SliderFullWindowContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Full window slider
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_user][pro][SliderFullWindow]" class="SliderFullWindow cg_view_option_radio_multiple_input_field"  $SliderFullWindow   />
                            </div>
                        </div>
                        $ForwardToWpPageEntryOptions
                </div>
            </div>
        </div>
       $ForwardToWpPageEntryInNewTabOptions
HEREDOC;
}

if(!isset($jsonOptions[$GalleryID.'-u']['general']['AllowComments'])){
    $AllowComments1 = ($AllowComments==1) ? 'checked' : '';
    $AllowComments2 = ($AllowComments==2) ? 'checked' : '';
    $AllowComments0 = ($AllowComments==0) ? 'checked' : '';
}else{
    $AllowComments1 = ($jsonOptions[$GalleryID.'-u']['general']['AllowComments']==1) ? 'checked' : '';
    $AllowComments2 = ($jsonOptions[$GalleryID.'-u']['general']['AllowComments']==2) ? 'checked' : '';
    $AllowComments0 = ($jsonOptions[$GalleryID.'-u']['general']['AllowComments']==0) ? 'checked' : '';
}

echo <<<HEREDOC
        <div class='cg_view_options_row_container AllowCommentsParentContainer' >
        <p class="cg_view_options_row_container_title" >Comment options
                <br><span style="font-weight: normal;">Comment notification e-mail options can be configured 
<a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="CommentNotificationArea">here</a></span>
        </p>
        <div class='cg_view_options_row cg_go_to_target' data-cg-go-to-target="AllowCommentsArea">
                <div class='cg_view_option cg_view_option_full_width AllowCommentsContainer'>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Show comments<br>and allow to comment
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_user][general][AllowComments]" class="cg_view_option_radio_multiple_input_field "  $AllowComments1 value="1"  />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Show comments only
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_user][general][AllowComments]" class="cg_view_option_radio_multiple_input_field "  $AllowComments2 value="2"    />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Disable comments
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_user][general][AllowComments]" class="cg_view_option_radio_multiple_input_field " $AllowComments0   value="0"  />
                            </div>
                        </div>
                </div>
            </div>
        </div>
HEREDOC;


echo <<<HEREDOC
<div class='cg_view_options_row AllowCommentsContainer'>
    <div  class='cg_view_option cg_border_top_none  cg_view_option_full_width  cg_view_option_flex_flow_column AllowCommentsContainer'>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
        <p>Comments date format shown in frontend</p>
        </div>
        <div class="cg_view_option_select">
        <select name="multiple-pics[cg_gallery_user][visual][CommentsDateFormat]">
HEREDOC;

if(empty($jsonOptions[$GalleryID.'-u']['visual']['CommentsDateFormat'])){
    $CommentsDateFormat = '';
}else{
    $CommentsDateFormat = $jsonOptions[$GalleryID.'-u']['visual']['CommentsDateFormat'];
}

foreach($CommentsDateFormatNamePathSelectedValuesArray as $CommentsDateFormatNamePathSelectedValuesArrayValue){
    $CommentsDateFormatNamePathSelectedValuesArrayValueSelected = '';
    if($CommentsDateFormatNamePathSelectedValuesArrayValue==$CommentsDateFormat){
        $CommentsDateFormatNamePathSelectedValuesArrayValueSelected = 'selected';
    }
    echo "<option value='$CommentsDateFormatNamePathSelectedValuesArrayValue' $CommentsDateFormatNamePathSelectedValuesArrayValueSelected >$CommentsDateFormatNamePathSelectedValuesArrayValue</option>";
}

echo <<<HEREDOC
                        <option value="YYYY-MM-DD">YYYY-MM-DD</option><option value="DD-MM-YYYY">DD-MM-YYYY</option><option value="MM-DD-YYYY">MM-DD-YYYY</option><option value="YYYY/MM/DD">YYYY/MM/DD</option><option value="DD/MM/YYYY">DD/MM/YYYY</option><option value="MM/DD/YYYY">MM/DD/YYYY</option><option value="YYYY.MM.DD">YYYY.MM.DD</option><option value="DD.MM.YYYY">DD.MM.YYYY</option><option value="MM.DD.YYYY">MM.DD.YYYY</option>
                               </select>
        </div>
    </div>
</div>
HEREDOC;

// only json option, not in database available
if(!isset($jsonOptions[$GalleryID.'-u']['visual']['EnableEmojis'])){
    $jsonOptions[$GalleryID.'-u']['visual']['EnableEmojis'] = 1;
}

echo <<<HEREDOC
        <div class="cg_view_options_row">
                <div class="cg_view_option cg_view_option_100_percent cg_border_top_none" >
                    <div class="cg_view_option_title">
                        <p>Enable emojis</p>
                    </div>
                    <div class="cg_view_option_checkbox cg_view_option_checked">
                        <input type="checkbox" name="multiple-pics[cg_gallery_user][visual][EnableEmojis]" class="cg_shortcode_checkbox" checked="{$jsonOptions[$GalleryID.'-u']['visual']['EnableEmojis']}" >
                    </div>
                </div>
        </div>
HEREDOC;

if(!empty($jsonOptions[$GalleryID.'-u']['general']['HideCommentNameField'])){
    $HideCommentNameField = '1';
}else{
    $HideCommentNameField = '0';
}


echo <<<HEREDOC
        <div class="cg_view_options_row">
                <div class="cg_view_option cg_view_option_100_percent cg_border_top_none" >
                    <div class="cg_view_option_title">
                        <p>Hide enter "Name" in comments area for not logged in users<br><span class="cg_font_weight_normal">and</span><br>Allow only logged in users to comment<br><span class="cg_font_weight_normal">options are not available for "cg_gallery_user" shortcode<br>because "cg_gallery_user" shortcode is designed only for logged in users already<br>"read info" in shortcodes overview above</span></p>
                    </div>
                </div>
        </div>
HEREDOC;

// logic not required for cg_gallery_user shortcode, because user already logged in
// only json option, not in database available
/*if(!isset($jsonOptions[$GalleryID.'-u']['pro']['CheckLoginComment'])){
    $jsonOptions[$GalleryID.'-u']['pro']['CheckLoginComment'] = 0;
}

echo <<<HEREDOC
        <div class="cg_view_options_row">
                <div class="cg_view_option cg_view_option_100_percent cg_border_top_none $cgProFalse" >
                    <div class="cg_view_option_title">
                        <p>Allow only logged in users to comment</p>
                    </div>
                    <div class="cg_view_option_checkbox cg_view_option_checked">
                        <input type="checkbox" name="multiple-pics[cg_gallery_user][pro][CheckLoginComment]" class="cg_shortcode_checkbox" checked="{$jsonOptions[$GalleryID.'-u']['pro']['CheckLoginComment']}">
                    </div>
                </div>
        </div>
        </div>
HEREDOC;*/

echo <<<HEREDOC
</div>
HEREDOC;

if(empty($jsonOptions[$GalleryID.'-u'])){
    $GalleryStyleCenterWhiteChecked = '';
}else{
    if(empty($jsonOptions[$GalleryID.'-u']['visual']['GalleryStyle'])){
        $GalleryStyleCenterWhiteChecked = '';
    }else{
        $GalleryStyleCenterWhiteChecked = ($jsonOptions[$GalleryID.'-u']['visual']['GalleryStyle']=='center-white') ? 'checked' : '';
    }
}

if(empty($jsonOptions[$GalleryID.'-u'])){
    $GalleryStyleCenterBlackChecked = 'checked';
}else{
    if(empty($jsonOptions[$GalleryID.'-u']['visual']['GalleryStyle'])){
        $GalleryStyleCenterBlackChecked = 'checked';
    }else{
        $GalleryStyleCenterBlackChecked = ($jsonOptions[$GalleryID.'-u']['visual']['GalleryStyle']=='center-black') ? 'checked' : '';
    }
}


$SlideTransitionSlideHorizontalChecked = ($jsonOptions[$GalleryID.'-u']['pro']['SlideTransition']=='translateX') ?  'checked' :  '';

$SlideTransitionSlideDownChecked = ($jsonOptions[$GalleryID.'-u']['pro']['SlideTransition']=='slideDown') ?  'checked' :  '';


if(!isset($jsonOptions[$GalleryID.'-u']['visual']['EnableSwitchStyleImageViewButton'])){
    $jsonOptions[$GalleryID.'-u']['visual']['EnableSwitchStyleImageViewButton'] = 0;
}

if(!isset($jsonOptions[$GalleryID.'-u']['visual']['SwitchStyleImageViewButtonOnlyImageView'])){
    $jsonOptions[$GalleryID.'-u']['visual']['SwitchStyleImageViewButtonOnlyImageView'] = 0;
}

$FullSizeSlideOutStartContainer = '';
if(floatval($galleryDbVersion)<15.05){
    $FullSizeSlideOutStartContainer = <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_100_percent  cg_border_top_none FullSizeSlideOutStartContainer'>
                    <div class='cg_view_option_title'>
                        <p>"Gallery slide out" has to be activated above<br>Start gallery full window view<br>
        as slide out by clicking an image<br><span class="cg_view_option_title_note">Will not start automatically full window when clicking image in slider view</span></p>
                    </div>
                    <div class='cg_view_option_checkbox'>
                        <input type="checkbox" name="multiple-pics[cg_gallery_user][general][FullSizeSlideOutStart]" checked="{$jsonOptions[$GalleryID.'-u']['general']['FullSizeSlideOutStart']}" class="cg_shortcode_checkbox FullSizeSlideOutStart">
                    </div>
                </div>
            </div>
HEREDOC;
}

echo <<<HEREDOC
        <div style='padding:0;margin:0;' class="cg_image_view_other_options_then_comment_options">
             <div class='cg_go_to_target' data-cg-go-to-target="OpenedImageStyleContainer">
            </div>
HEREDOC;

/* removed completely in v18, only translateX available
echo <<<HEREDOC
            <div class='cg_view_options_row'>
                    <div class='cg_view_option cg_view_option_full_width cg_border_top_none'>
                        <div class='cg_view_option_title'>
                            <p>Slide effect</p>
                        </div>
                        <div class='cg_view_option_radio_multiple'>
                            <div class='cg_view_option_radio_multiple_container SlideTransitionTranslateXContainer'>
                                <div class='cg_view_option_radio_multiple_title'>
                                    horizontal
                                </div>
                                <div class='cg_view_option_radio_multiple_input'>
                                         <input type="radio" name="multiple-pics[cg_gallery_user][pro][SlideTransition]" class="SlideTransition cg_view_option_radio_multiple_input_field"  $SlideTransitionSlideHorizontalChecked  value="translateX" />
                                </div>
                            </div>
                            <div class='cg_view_option_radio_multiple_container SlideTransitionSlideVerticalContainer'>
                                <div class='cg_view_option_radio_multiple_title'>
                                    vertical
                                </div>
                                <div class='cg_view_option_radio_multiple_input'>
                                    <input type="radio" name="multiple-pics[cg_gallery_user][pro][SlideTransition]" class="SlideVertical cg_view_option_radio_multiple_input_field"  $SlideTransitionSlideDownChecked  value="slideDown" >
                                </div>
                            </div>
                    </div>
                </div>
            </div>
HEREDOC;*/

echo <<<HEREDOC
            $FullSizeSlideOutStartContainer
HEREDOC;

if(!isset($jsonOptions[$GalleryID.'-u']['visual']['ShareButtons'])){
    // will be set in edit-options.php!!!
    $jsonOptions[$GalleryID.'-u']['visual']['ShareButtons'] = '';// unset share buttons for gallery user, because logged in
}

echo <<<HEREDOC
<div class="cg_view_options_row ShareButtonsHiddenRow" >
           <input type='hidden' name="multiple-pics[cg_gallery_user][visual][ShareButtons]" value='{$jsonOptions[$GalleryID . '-u']['visual']['ShareButtons']}' class='ShareButtonsHiddenInput' />
            <div class="cg_view_option cg_view_option_full_width cg_border_bottom_none " style="margin-bottom: -25px;" >
                <div class="cg_view_option_title ">
                    <p>Social share buttons<br>
                    <span class="cg_view_option_title_note"><span class="cg_font_weight_bold">NOTE: </span>for cg_gallery_user shortcode unset by default, because shortcode is for registered and logged in users</span>
                    </p>
                </div>                    
        </div>
</div>
HEREDOC;

$ShareButtonsExploded = explode(',',$jsonOptions[$GalleryID.'-u']['visual']['ShareButtons']);
$EmailChecked = (in_array('email',$ShareButtonsExploded)!==false) ? 'checked' : '';
$SmsChecked = (in_array('sms',$ShareButtonsExploded)!==false) ? 'checked' : '';
$GmailChecked = (in_array('gmail',$ShareButtonsExploded)!==false) ? 'checked' : '';
$YahooChecked = (in_array('yahoo',$ShareButtonsExploded)!==false) ? 'checked' : '';
$EvernoteChecked = (in_array('evernote',$ShareButtonsExploded)!==false) ? 'checked' : '';

echo <<<HEREDOC
<div class="cg_view_options_row $ShareButtonsHide">
    <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none cg_border_right_none cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>Email</p>
        </div>
        <div class="cg_view_option_checkbox">
            <input type="checkbox" $EmailChecked class="cg_share_button" value="email">
        </div>
    </div>
    <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none cg_share_button_option">
        <div class="cg_view_option_title">
            <p>SMS</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $SmsChecked class="cg_share_button" value="sms">
        </div>
    </div> 
       <div class="cg_view_option    cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none cg_share_button_option  ">
        <div class="cg_view_option_title">
            <p>Gmail</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $GmailChecked  class="cg_share_button" value="gmail">
        </div>
    </div>
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none  cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>Yahoo</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $YahooChecked class="cg_share_button" value="yahoo">
        </div>
    </div>
    <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_share_button_option  ">
        <div class="cg_view_option_title">
            <p>Evernote</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $EvernoteChecked class="cg_share_button" value="evernote">
        </div>
    </div>
</div>
HEREDOC;

$FacebookChecked = (in_array('facebook',$ShareButtonsExploded)!==false) ? 'checked' : '';
$WhatsAppChecked = (in_array('whatsapp',$ShareButtonsExploded)!==false) ? 'checked' : '';
$TwitterChecked = (in_array('twitter',$ShareButtonsExploded)!==false) ? 'checked' : '';
$TelegramChecked = (in_array('telegram',$ShareButtonsExploded)!==false) ? 'checked' : '';
$SkypeChecked = (in_array('skype',$ShareButtonsExploded)!==false) ? 'checked' : '';

echo <<<HEREDOC
<div class="cg_view_options_row $ShareButtonsHide">
    <div class="cg_view_option $cgProFalse cg-pro-false-small   cg_view_option_20_percent cg_border_top_bottom_none cg_border_right_none cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>Facebook</p>
        </div>
        <div class="cg_view_option_checkbox">
            <input type="checkbox" $FacebookChecked class="cg_share_button" value="facebook">
        </div>
    </div>
    <div class="cg_view_option  $cgProFalse cg-pro-false-small  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none cg_share_button_option">
        <div class="cg_view_option_title">
            <p>WhatsApp</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $WhatsAppChecked class="cg_share_button" value="whatsapp">
        </div>
    </div> 
       <div class="cg_view_option  $cgProFalse cg-pro-false-small  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none cg_share_button_option  ">
        <div class="cg_view_option_title">
            <p>Twitter</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $TwitterChecked  class="cg_share_button" value="twitter">
        </div>
    </div>
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none  cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>Telegram</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $TelegramChecked class="cg_share_button" value="telegram">
        </div>
    </div>
    <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_share_button_option  ">
        <div class="cg_view_option_title">
            <p>Skype</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $SkypeChecked class="cg_share_button" value="skype">
        </div>
    </div>
</div>
HEREDOC;

$PinterestChecked = (in_array('pinterest',$ShareButtonsExploded)!==false) ? 'checked' : '';
$RedditAppChecked = (in_array('reddit',$ShareButtonsExploded)!==false) ? 'checked' : '';
$XINGChecked = (in_array('xing',$ShareButtonsExploded)!==false) ? 'checked' : '';
$LinkedInChecked = (in_array('linkedin',$ShareButtonsExploded)!==false) ? 'checked' : '';
$VKChecked = (in_array('vk',$ShareButtonsExploded)!==false) ? 'checked' : '';

echo <<<HEREDOC
<div class="cg_view_options_row $ShareButtonsHide">
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none  cg_border_right_none  cg_share_button_option">
        <div class="cg_view_option_title">
            <p>Pinterest</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $PinterestChecked class="cg_share_button" value="pinterest">
        </div>
    </div> 
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none  cg_border_right_none cg_share_button_option">
        <div class="cg_view_option_title">
            <p>Reddit</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $RedditAppChecked class="cg_share_button" value="reddit">
        </div>
    </div>
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none  cg_border_left_none  cg_border_right_none   cg_share_button_option">
        <div class="cg_view_option_title">
            <p>XING</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $XINGChecked class="cg_share_button" value="xing">
        </div>
    </div>
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none  cg_border_left_none  cg_border_right_none cg_share_button_option  ">
        <div class="cg_view_option_title">
            <p>LinkedIn</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $LinkedInChecked class="cg_share_button" value="linkedin">
        </div>
    </div>
    <div class="cg_view_option  $cgProFalse cg-pro-false-small  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none  cg_share_button_option  ">
        <div class="cg_view_option_title">
            <p>VK</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $VKChecked class="cg_share_button" value="vk">
        </div>
    </div>
</div>
HEREDOC;

$OkRuChecked = (in_array('okru',$ShareButtonsExploded)!==false) ? 'checked' : '';
$QzoneChecked = (in_array('qzone',$ShareButtonsExploded)!==false) ? 'checked' : '';
$WeiboChecked = (in_array('weibo',$ShareButtonsExploded)!==false) ? 'checked' : '';
$DoubanChecked = (in_array('douban',$ShareButtonsExploded)!==false) ? 'checked' : '';
$RenRenChecked = (in_array('renren',$ShareButtonsExploded)!==false) ? 'checked' : '';

echo <<<HEREDOC
<div class="cg_view_options_row $ShareButtonsHide">
       <div class="cg_view_option  $cgProFalse  cg-pro-false-small cg_view_option_20_percent cg_border_top_bottom_none  cg_border_right_none cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>OK</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $OkRuChecked class="cg_share_button" value="okru">
        </div>
    </div> 
 <div class="cg_view_option   $cgProFalse cg-pro-false-small   cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none  cg_border_right_none cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>Qzone</p>
        </div>
        <div class="cg_view_option_checkbox">
            <input type="checkbox" $QzoneChecked class="cg_share_button" value="qzone">
        </div>
    </div> 
    <div class="cg_view_option  $cgProFalse cg-pro-false-small  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none  cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>Weibo</p>
        </div>
        <div class="cg_view_option_checkbox">
            <input type="checkbox" $WeiboChecked class="cg_share_button" value="weibo">
        </div>
    </div>
       <div class="cg_view_option $cgProFalse cg-pro-false-small  cg_view_option_20_percent cg_border_top_bottom_none  cg_border_left_none  cg_border_right_none cg_share_button_option  ">
        <div class="cg_view_option_title">
            <p>Douban</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $DoubanChecked class="cg_share_button" value="douban">
        </div>
    </div>
    <div class="cg_view_option  $cgProFalse cg-pro-false-small   cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none  cg_share_button_option  ">
        <div class="cg_view_option_title">
            <p>RenRen</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $RenRenChecked class="cg_share_button" value="renren">
        </div>
    </div>
</div>
HEREDOC;


$jsonOptions[$GalleryID.'-u']['visual']['ImageViewFullWindow'] = (!isset($jsonOptions[$GalleryID.'-u']['visual']['ImageViewFullWindow'])) ? 1 : $jsonOptions[$GalleryID.'-u']['visual']['ImageViewFullWindow'];

$jsonOptions[$GalleryID.'-u']['visual']['ImageViewFullScreen'] = (!isset($jsonOptions[$GalleryID.'-u']['visual']['ImageViewFullScreen'])) ? 1 : $jsonOptions[$GalleryID.'-u']['visual']['ImageViewFullScreen'];

$jsonOptions[$GalleryID.'-u']['visual']['CopyOriginalFileLink'] = (!isset($jsonOptions[$GalleryID.'-u']['visual']['CopyOriginalFileLink'])) ? $CopyOriginalFileLink : $jsonOptions[$GalleryID.'-u']['visual']['CopyOriginalFileLink'];

$jsonOptions[$GalleryID.'-u']['visual']['ForwardOriginalFile'] = (!isset($jsonOptions[$GalleryID.'-u']['visual']['ForwardOriginalFile'])) ? $CopyOriginalFileLink : $jsonOptions[$GalleryID.'-u']['visual']['ForwardOriginalFile'];

$jsonOptions[$GalleryID.'-u']['visual']['OriginalSourceLinkInSlider'] = (!isset($jsonOptions[$GalleryID.'-u']['visual']['OriginalSourceLinkInSlider'])) ? $CopyImageLink : $jsonOptions[$GalleryID.'-u']['visual']['OriginalSourceLinkInSlider'];

$jsonOptions[$GalleryID.'-u']['visual']['CopyImageLink'] = (!isset($jsonOptions[$GalleryID.'-u']['visual']['CopyImageLink'])) ? $CopyImageLink : $jsonOptions[$GalleryID.'-u']['visual']['CopyImageLink'];

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_50_percent cg_border_right_none   cg_border_bottom_none CopyOriginalFileLinkContainer'>
            <div class='cg_view_option_title cg_view_option_title_quarter'>
                <p>Copy original file source link button</p>
            </div>
            <div class='cg_view_option_checkbox cg_view_option_title_quarter'>
                <input type="checkbox" name="multiple-pics[cg_gallery_user][visual][CopyOriginalFileLink]" checked="{$jsonOptions[$GalleryID.'-u']['visual']['CopyOriginalFileLink']}" class="cg_shortcode_checkbox CopyOriginalFileLink"   />
            </div>
        </div>
        <div class='cg_view_option cg_view_option_50_percent  cg_border_bottom_none  ForwardOriginalFileContainer'>
            <div class='cg_view_option_title cg_view_option_title_quarter'>
                <p>Forward to original file source button</p>
            </div>
            <div class='cg_view_option_checkbox cg_view_option_title_quarter'>
                <input type="checkbox" name="multiple-pics[cg_gallery_user][visual][ForwardOriginalFile]" checked="{$jsonOptions[$GalleryID.'-u']['visual']['ForwardOriginalFile']}" class="cg_shortcode_checkbox ForwardOriginalFile"   />
            </div>
        </div>
   </div>
HEREDOC;


echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option  cg_view_option_50_percent cg_border_right_none   OriginalSourceLinkInSliderContainer'>
                    <div class='cg_view_option_title cg_view_option_title_quarter'>
                        <p>Download original file button</p>
                    </div>
                    <div class='cg_view_option_checkbox cg_view_option_title_quarter'>
                        <input type="checkbox" name="multiple-pics[cg_gallery_user][visual][OriginalSourceLinkInSlider]" checked="{$jsonOptions[$GalleryID.'-u']['visual']['OriginalSourceLinkInSlider']}" class="cg_shortcode_checkbox OriginalSourceLinkInSlider">
                    </div>
                </div>
                <div class='cg_view_option cg_view_option_50_percent   CopyImageLinkContainer '>
                    <div class='cg_view_option_title cg_view_option_title_quarter'>
                        <p>Copy gallery entry link button</p>
                    </div>
                    <div class='cg_view_option_checkbox cg_view_option_title_quarter'>
                        <input type="checkbox" name="multiple-pics[cg_gallery_user][visual][CopyImageLink]" checked="{$jsonOptions[$GalleryID.'-u']['visual']['CopyImageLink']}" class="cg_shortcode_checkbox CopyImageLink">
                    </div>
                </div>
           </div>
HEREDOC;

$ShowExifModel = (!empty($jsonOptions[$GalleryID.'-u']['general']['ShowExifModel']) OR !isset($jsonOptions[$GalleryID.'-u']['general']['ShowExifModel'])) ? '1' : '0';
$ShowExifApertureFNumber = (!empty($jsonOptions[$GalleryID.'-u']['general']['ShowExifApertureFNumber']) OR !isset($jsonOptions[$GalleryID.'-u']['general']['ShowExifApertureFNumber'])) ? '1' : '0';
$ShowExifExposureTime = (!empty($jsonOptions[$GalleryID.'-u']['general']['ShowExifExposureTime']) OR !isset($jsonOptions[$GalleryID.'-u']['general']['ShowExifExposureTime'])) ? '1' : '0';
$ShowExifISOSpeedRatings = (!empty($jsonOptions[$GalleryID.'-u']['general']['ShowExifISOSpeedRatings']) OR !isset($jsonOptions[$GalleryID.'-u']['general']['ShowExifISOSpeedRatings'])) ? '1' : '0';
$ShowExifFocalLength = (!empty($jsonOptions[$GalleryID.'-u']['general']['ShowExifFocalLength']) OR !isset($jsonOptions[$GalleryID.'-u']['general']['ShowExifFocalLength'])) ? '1' : '0';
$ShowExifDateTimeOriginal = (!empty($jsonOptions[$GalleryID.'-u']['general']['ShowExifDateTimeOriginal'])) ? '1' : '0';// another condition because of possible pro feature
$ShowExifDateTimeOriginalFormat = (!empty($jsonOptions[$GalleryID.'-u']['general']['ShowExifDateTimeOriginalFormat'])) ? $jsonOptions[$GalleryID.'-u']['general']['ShowExifDateTimeOriginalFormat'] : 'YYYY-MM-DD';

if(function_exists('exif_read_data')){
    echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_100_percent cg_border_top_none ShowExifContainer'>
                    <div class='cg_view_option_title'>
                        <p>Show image EXIF data<br><span class="cg_view_option_title_note">If an image has a EXIF data</span></p>
                    </div>
                    <div class='cg_view_option_checkbox'>
                       <input type="checkbox" name="multiple-pics[cg_gallery_user][pro][ShowExif]" checked="{$jsonOptions[$GalleryID.'-u']['pro']['ShowExif']}" class="cg_shortcode_checkbox ShowExif">
                    </div>
                </div>
           </div>
HEREDOC;
} else{
    echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option cg_border_top_none  cg_view_option_100_percent'>
                    <div class='cg_view_option_title'>
                        <p>Show EXIF data an image file type can not be activated.<br>Please contact your provider<br>to enable exif_read_data function.</p>
                    </div>
                </div>
           </div>
HEREDOC;
}

$showExifDateTimeRepairFrontendNote = '';

if(floatval($galleryDbVersion)<floatval(cg_get_db_version())){
    $showExifDateTimeRepairFrontendNote = '<br><strong>NOTE:</strong> to display original date of images added before 12.2.3 version<br>please use "Status, repair ...." button at the top and click "Repair frontend".<br>Original image date will be added to activated images if EXIF data contains some.';
}


echo <<<HEREDOC
<div class="cg_view_options_row">
                <div class="cg_view_option cg_view_option_full_width cg_border_top_bottom_none ShowExifOption">
                    <div class="cg_view_option_title ">
                        <p>EXIF data to show in frontend<br><span class="cg_view_option_title_note">Select which EXIF data should be shown.<br>Images might not have EXIF data which can be shown.$showExifDateTimeRepairFrontendNote</span></p>
                    </div>                    
            </div>
        </div>

<div class='cg_view_options_row'>

    <div  class='cg_view_option cg_border_top_bottom_none  cg_border_right_none $cgProFalse ShowExifOption'>
        <div  class='cg_view_option_title'>
            <p>Original image date</p>
        </div>
        <div class='cg_view_option_checkbox'>
            <input type="checkbox" name="multiple-pics[cg_gallery_user][general][ShowExifDateTimeOriginal]" class="ShowExifDateTimeOriginal cg_shortcode_checkbox" checked="$ShowExifDateTimeOriginal"><br/>
        </div>

    </div>

    <div  class='cg_view_option cg_border_top_none  cg_border_top_bottom_none  cg_border_left_right_none cg_view_option_flex_flow_column $cgProFalse  ShowExifOption'>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
        <p>Original image date format<br>to show in frontend</p>
        </div>
        <div class="cg_view_option_select">
        <select name="multiple-pics[cg_gallery_user][general][ShowExifDateTimeOriginalFormat]" class="$cgProFalse" >
HEREDOC;


foreach($ShowExifDateTimeOriginalFormatNamePathSelectedValuesArray as $ShowExifDateTimeOriginalFormatNamePathArrayValue){
    $ShowExifDateTimeOriginalFormatNamePathArrayValueSelected = '';
    if($ShowExifDateTimeOriginalFormatNamePathArrayValue==$ShowExifDateTimeOriginalFormat){
        $ShowExifDateTimeOriginalFormatNamePathArrayValueSelected = 'selected';
    }
    echo "<option value='$ShowExifDateTimeOriginalFormatNamePathArrayValue' $ShowExifDateTimeOriginalFormatNamePathArrayValueSelected >$ShowExifDateTimeOriginalFormatNamePathArrayValue</option>";
}

echo <<<HEREDOC
                        <option value="YYYY-MM-DD">YYYY-MM-DD</option><option value="DD-MM-YYYY">DD-MM-YYYY</option><option value="MM-DD-YYYY">MM-DD-YYYY</option><option value="YYYY/MM/DD">YYYY/MM/DD</option><option value="DD/MM/YYYY">DD/MM/YYYY</option><option value="MM/DD/YYYY">MM/DD/YYYY</option><option value="YYYY.MM.DD">YYYY.MM.DD</option><option value="DD.MM.YYYY">DD.MM.YYYY</option><option value="MM.DD.YYYY">MM.DD.YYYY</option>
                               </select>
        </div>
    </div>

    <div  class='cg_view_option cg_border_top_bottom_none cg_border_left_none ShowExifOption'>
        <div class='cg_view_option_title cg_border_top_bottom_none cg_border_right_none'>
        <p>Camera model</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_user][general][ShowExifModel]" class="ShowExifModel cg_shortcode_checkbox" checked="$ShowExifModel"><br/>
        </div>
    </div>

    
</div>
    
 <div class='cg_view_options_row'>
 
 
    <div  class='cg_view_option cg_view_option_25_percent cg_border_top_bottom_none cg_border_right_none ShowExifOption '>
        <div class='cg_view_option_title'>
        <p>Aperture</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_user][general][ShowExifApertureFNumber]" class="ShowExifApertureFNumber cg_shortcode_checkbox" checked="$ShowExifApertureFNumber"><br/>
        </div>
    </div>

    <div  class='cg_view_option cg_view_option_25_percent cg_border_top_bottom_none  cg_border_left_right_none  ShowExifOption'>
        <div  class='cg_view_option_title'>
        <p>Exposure time</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_user][general][ShowExifExposureTime]" class="ShowExifExposureTime cg_shortcode_checkbox" checked="$ShowExifExposureTime"><br/>
        </div>
    </div>
   
    <div  class='cg_view_option cg_view_option_25_percent cg_border_top_bottom_none  cg_border_left_right_none ShowExifOption'>
        <div class='cg_view_option_title'>
        <p>ISO</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_user][general][ShowExifISOSpeedRatings]" class="ShowExifISOSpeedRatings cg_shortcode_checkbox" checked="$ShowExifISOSpeedRatings"><br/>
        </div>
    </div>

    <div  class='cg_view_option cg_view_option_25_percent cg_border_top_bottom_none  cg_border_left_none ShowExifOption'>
        <div class='cg_view_option_title'>
        <p>Focal length</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_user][general][ShowExifFocalLength]" class="ShowExifFocalLength cg_shortcode_checkbox" checked="$ShowExifFocalLength"><br/>
        </div>
    </div>
</div>
<div class='cg_view_options_row cg_view_options_row_empty_placeholder ShowExifOption' style="padding-bottom: 15px;border-left: thin solid #dedede;border-right: thin solid #dedede;box-sizing: border-box;">

</div>
HEREDOC;



echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_100_percent  ShowNicknameContainer $cgProFalse'>
                    <div class='cg_view_option_title '>
                        <p>Show "Nickname" who added entry<br><span class="cg_view_option_title_note">If a registered user uploaded a file</span></p>
                    </div>
                    <div class='cg_view_option_checkbox '>
                        <input type="checkbox" name="multiple-pics[cg_gallery_user][pro][ShowNickname]" checked="{$jsonOptions[$GalleryID.'-u']['pro']['ShowNickname']}" class="cg_shortcode_checkbox ShowNickname">
                    </div>
                </div>
            </div>
HEREDOC;

$beforeSinceV14Explanation = '';
$beforeSinceV14Disabled = '';

// is ok if it works for old galleries also, only registered user has to have new since v14 role  or role configured in reg options since v14
if(intval($galleryDbVersion)<14 AND empty($cgProFalse)){
    //  $beforeSinceV14Disabled = 'cg_disabled';
    //  $beforeSinceV14Explanation = '<br><span class="cg_color_red">NOTE: </span> Available only for galleries created or copied in plugin version 14 or higher';
}

if(!isset($jsonOptions[$GalleryID.'-u']['pro']['ShowProfileImage'])){
    $jsonOptions[$GalleryID.'-u']['pro']['ShowProfileImage'] = 0;
}

echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option cg_border_border_bottom_left_radius_unset cg_border_border_bottom_right_radius_unset cg_view_option_100_percent cg_border_top_none  ShowProfileImageContainer $cgProFalse$beforeSinceV14Disabled'>
                    <div class='cg_view_option_title '>
                        <p>Show "Profile image" who added entry<br><span class="cg_view_option_title_note">If a registered user uploaded a file<br>"Profile image" form field has to be added in "Edit registration form"$beforeSinceV14Explanation</span></p>
                    </div>
                    <div class='cg_view_option_checkbox '>
                        <input type="checkbox" name="multiple-pics[cg_gallery_user][pro][ShowProfileImage]" checked="{$jsonOptions[$GalleryID.'-u']['pro']['ShowProfileImage']}" class="cg_shortcode_checkbox ShowProfileImage">
                    </div>
                </div>
            </div>
        </div>
HEREDOC;


if(floatval($galleryDbVersion)>=21){
    if(!isset($jsonOptions[$GalleryID.'-u']['visual']['BackToGalleryButtonText'])){
        $jsonOptions[$GalleryID.'-u']['visual']['BackToGalleryButtonText'] = $BackToGalleryButtonText;
    }
    if(!isset($jsonOptions[$GalleryID.'-u']['pro']['BackToGalleryButtonURL'])){
        $jsonOptions[$GalleryID.'-u']['pro']['BackToGalleryButtonURL'] = $BackToGalleryButtonURL;
    }else{
        $jsonOptions[$GalleryID.'-u']['pro']['BackToGalleryButtonURL'] = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['pro']['BackToGalleryButtonURL']);
    }
    if(!isset($jsonOptions[$GalleryID.'-u']['visual']['TextBeforeWpPageEntry'])){
        $jsonOptions[$GalleryID.'-u']['visual']['TextBeforeWpPageEntry'] = $TextBeforeWpPageEntry;
    }else{
        $jsonOptions[$GalleryID.'-u']['visual']['TextBeforeWpPageEntry'] = contest_gal1ery_convert_for_html_output($jsonOptions[$GalleryID.'-u']['visual']['TextBeforeWpPageEntry']);
    }
    if(!isset($jsonOptions[$GalleryID.'-u']['visual']['TextAfterWpPageEntry'])){
        $jsonOptions[$GalleryID.'-u']['visual']['TextAfterWpPageEntry'] = $TextAfterWpPageEntry;
    }else{
        $jsonOptions[$GalleryID.'-u']['visual']['TextAfterWpPageEntry'] = contest_gal1ery_convert_for_html_output($jsonOptions[$GalleryID.'-u']['visual']['TextAfterWpPageEntry']);
    }
    if(!isset($jsonOptions[$GalleryID.'-u']['visual']['ShowBackToGalleryButton'])){
        $jsonOptions[$GalleryID.'-u']['visual']['ShowBackToGalleryButton'] = 0;
    }
    if(!isset($jsonOptions[$GalleryID.'-u']['visual']['TextDeactivatedEntry'])){
        $jsonOptions[$GalleryID.'-u']['visual']['TextDeactivatedEntry'] = $TextDeactivatedEntry;
    }else{
        $jsonOptions[$GalleryID.'-u']['visual']['TextDeactivatedEntry'] = contest_gal1ery_convert_for_html_output($jsonOptions[$GalleryID.'-u']['visual']['TextDeactivatedEntry']);
    }
    if(!isset($jsonOptions[$GalleryID.'-u']['pro']['RedirectURLdeletedEntry'])){
        $jsonOptions[$GalleryID.'-u']['pro']['RedirectURLdeletedEntry'] = $RedirectURLdeletedEntry;
    }else{
        $jsonOptions[$GalleryID.'-u']['pro']['RedirectURLdeletedEntry'] = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-u']['pro']['RedirectURLdeletedEntry']);
    }

    $ShowBackToGalleryButtonRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_100_percent  '>
        <div class='cg_view_option_title '>
            <p>Show back to gallery button on entry landing page</p>
        </div>
        <div class='cg_view_option_checkbox '>
            <input type="checkbox" name='multiple-pics[cg_gallery_user][visual][ShowBackToGalleryButton]'  class="cg_shortcode_checkbox ShowBackToGalleryButton"  checked="{$jsonOptions[$GalleryID.'-u']['visual']['ShowBackToGalleryButton']}"  >
        </div>
    </div>
</div>
HEREDOC;

$BackToGalleryButtonTextRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
        <div class='cg_view_option_title '>
            <p>Back to gallery button text<br>
            <span class="cg_view_option_title_note">Translation can be found <a class="cg_no_outline_and_shadow_on_focus" href="{$editTranslationLink}l_BackToGallery"  target="_blank">here</a></span>
            </p>
        </div>
    </div>
</div>
HEREDOC;

    $BackToGalleryButtonURLRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none  '>
        <div class='cg_view_option_title '>
            <p>Back to gallery button custom URL<br><span class="cg_view_option_title_note">If not set then parent site URL will be used<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
        </div>
        <div class='cg_view_option_input '>
            <input type="text" name="multiple-pics[cg_gallery_user][pro][BackToGalleryButtonURL]" class="BackToGalleryButtonURL"  value="{$jsonOptions[$GalleryID.'-u']['pro']['BackToGalleryButtonURL']}"   >
        </div>
    </div>
</div>
HEREDOC;

    $TextBeforeWpPageEntryRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-TextBeforeWpPageEntryUser-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on entry landing page before an activated entry</p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery_user][visual][TextBeforeWpPageEntry]'  id='TextBeforeWpPageEntryUser'>{$jsonOptions[$GalleryID.'-u']['visual']['TextBeforeWpPageEntry']}</textarea>
        </div>
    </div>
</div>
HEREDOC;

    $TextAfterWpPageEntryRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-TextAfterWpPageEntryUser-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on entry landing page after an activated entry</p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery_user][visual][TextAfterWpPageEntry]'  id='TextAfterWpPageEntryUser'>{$jsonOptions[$GalleryID.'-u']['visual']['TextAfterWpPageEntry']}</textarea>
        </div>
    </div>
</div>
HEREDOC;

    $TextDeactivatedEntryRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-TextDeactivatedEntryUser-wrap-Container">
        <div class='cg_view_option_title'>
            <p>Text on entry landing page if entry is deactivated</p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery_user][visual][TextDeactivatedEntry]'  id='TextDeactivatedEntryUser'>{$jsonOptions[$GalleryID.'-u']['visual']['TextDeactivatedEntry']}</textarea>
        </div>
    </div>
</div>
HEREDOC;

    // currently will be not used, deleted ids needs to be saved some for all galleries or htaccess redirect rule needs to be written
    $RedirectURLdeletedEntryRow = <<<HEREDOC
    <div class='cg_view_options_row cg_hide'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Redirect (HTTP 301) URL if gallery entry was deleted<br><span class="cg_view_option_title_note">If not set then parent site URL will be used</span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery_user][pro][RedirectURLdeletedEntry]' class="RedirectURLdeletedEntry"  value="{$jsonOptions[$GalleryID.'-u']['pro']['RedirectURLdeletedEntry']}"  >
            </div>
        </div>
    </div>
HEREDOC;

// only json option, not in database available
if(!isset($jsonOptions[$GalleryID.'-u']['visual']['AdditionalCssEntryLandingPage'])){
    $AdditionalCssEntryLandingPage = "body {\r\n&nbsp;&nbsp;font-family:sans-serif;\r\n&nbsp;&nbsp;font-size:16px;\r\n&nbsp;&nbsp;background-color:white;\r\n&nbsp;&nbsp;color:black;\r\n}";
}else{
    $AdditionalCssEntryLandingPage = cg_stripslashes_recursively($jsonOptions[$GalleryID.'-u']['visual']['AdditionalCssEntryLandingPage']);
}

$AdditionalCssEntryLandingPageRow = <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Additional CSS on entry landing page</p>
            </div>
            <div class='cg_view_option_textarea' >
                <textarea type="text" name="multiple-pics[cg_gallery_user][visual][AdditionalCssEntryLandingPage]" rows="7" style="width:100%;" class="AdditionalCssEntryLandingPage"  >$AdditionalCssEntryLandingPage</textarea>
            </div>
        </div>
    </div>
HEREDOC;

    echo <<<HEREDOC
        <div class='cg_view_options_row_container EntryLadingPageOptionsParentContainer cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px' style="border-bottom: thin solid #dedede;">
            <p class="cg_view_options_row_container_title" >Entry landing page options</p>
            $ShowBackToGalleryButtonRow
            $BackToGalleryButtonTextRow
            $BackToGalleryButtonURLRow
            $TextBeforeWpPageEntryRow
            $TextAfterWpPageEntryRow
            $TextDeactivatedEntryRow
            $RedirectURLdeletedEntryRow
            $AdditionalCssEntryLandingPageRow
        </div>
HEREDOC;
}


// also will be closed here
echo <<<HEREDOC
    </div>
HEREDOC;

echo <<<HEREDOC

   <div class='cg_view_options_rows_container'>

        <p class='cg_view_options_rows_container_title'>Original source link only</p>

        <div class='cg_view_options_row'>
            <div class='cg_view_option cg_view_option_100_percent FullSizeImageOutGalleryContainer cg_border_radius_8_px'>
                <div class='cg_view_option_title'>
                    <p>Forward directly to original source after clicking an entry from in a gallery<br><span class="cg_view_option_title_note">Configuration of voting out of gallery is possible. Only for gallery views. Slider and blog view will work as usual.</span></p>
                </div>
                <div class='cg_view_option_radio cg_margin_top_5'>
                    <input type="radio" name="multiple-pics[cg_gallery_user][general][FullSizeImageOutGallery]" checked="{$jsonOptions[$GalleryID.'-u']['general']['FullSizeImageOutGallery']}" class="FullSizeImageOutGallery">
                </div>
            </div>
        </div>

    </div>

    <div class='cg_view_options_rows_container'>

        <p class='cg_view_options_rows_container_title'>Only gallery view</p>

        <div class='cg_view_options_row'>
            <div class='cg_view_option cg_view_option_100_percent OnlyGalleryViewContainer cg_border_radius_8_px'>
                <div class='cg_view_option_title'>
                    <p>Make entries unclickable<br>Good for displaying entries only<br><span class="cg_view_option_title_note">Files images can not be clicked. Configuration of voting out of gallery is possible. Only for gallery views. Slider and blog view will work as usual.</span></p>
                </div>
                <div class='cg_view_option_radio cg_margin_top_5'>
                       <input type="radio" name="multiple-pics[cg_gallery_user][general][OnlyGalleryView]" checked="{$jsonOptions[$GalleryID.'-u']['general']['OnlyGalleryView']}" class="OnlyGalleryView">
                </div>
            </div>
        </div>

    </div>
HEREDOC;

echo <<<HEREDOC

    </div>
</div>
HEREDOC;


