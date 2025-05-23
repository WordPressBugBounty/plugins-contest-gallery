<?php

echo <<<HEREDOC
<div  class="cg_view cgSinglePicOptions cg_short_code_single_pic_configuration_cg_gallery_ecommerce_container cg_short_code_single_pic_configuration_container cg_hide cgViewHelper2">
<div class='cg_view_container'>
HEREDOC;

$AllowGalleryScript = (!empty($jsonOptions[$GalleryID.'-ec']['general']['AllowGalleryScript'])) ? 'checked' : '';
$SliderFullWindow = (!empty($jsonOptions[$GalleryID.'-ec']['pro']['SliderFullWindow'])) ? 'checked' : '';
$BlogLookFullWindow = (!empty($jsonOptions[$GalleryID.'-ec']['visual']['BlogLookFullWindow'])) ? 'checked' : '';

$gallerySlideOutDeprecated = '';

if(floatval($galleryDbVersion)<15.05){

    $gallerySlideOutDeprecated = '<br><span class="cg_view_option_title_note"><span class="cg_color_red">NOTE:</span> deprecated,<br>not available in future galleries</span>';

    echo <<<HEREDOC
<div class='cg_view_options_rows_container'>

        <p class='cg_view_options_rows_container_title'>Gallery slide out, slider view or blog view</p>
        
        <div class='cg_view_options_row'>
                <div class='cg_view_option  cg_view_option_full_width'>
                    <div class='cg_view_option_title'>
                        <p>Open entry style<br><span class="cg_view_option_title_note">Select how a file image should be opened on click in a gallery</span></p>
                    </div>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container AllowGalleryScriptContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Gallery slide out$gallerySlideOutDeprecated
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][general][AllowGalleryScript]" class="AllowGalleryScript cg_view_option_radio_multiple_input_field"  $AllowGalleryScript  />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container SliderFullWindowContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Full window slider
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][pro][SliderFullWindow]" class="SliderFullWindow cg_view_option_radio_multiple_input_field"  $SliderFullWindow   />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container BlogLookFullWindowContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Full window blog view
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][visual][BlogLookFullWindow]" class="BlogLookFullWindow cg_view_option_radio_multiple_input_field"  $BlogLookFullWindow  />
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
        $ForwardToWpPageEntry = (!empty($jsonOptions[$GalleryID.'-ec']['visual']['ForwardToWpPageEntry'])) ? 'checked' : '';
        $ForwardToWpPageEntryInNewTab = (!empty($jsonOptions[$GalleryID.'-ec']['visual']['ForwardToWpPageEntryInNewTab'])) ? '1' : '0';
        $EventuallyPadding = 'padding-bottom:20px;';
        $ForwardToWpPageEntryOptions = <<<HEREDOC
 <div class='cg_view_option_radio_multiple_container ForwardToWpPageEntryContainer'>
        <div class='cg_view_option_radio_multiple_title'>
            Forward to entry landing page
        </div>
        <div class='cg_view_option_radio_multiple_input'>
            <input type="radio" name="multiple-pics[cg_gallery_ecommerce][visual][ForwardToWpPageEntry]" class="ForwardToWpPageEntry cg_view_option_radio_multiple_input_field"  $ForwardToWpPageEntry  />
        </div>
</div>
HEREDOC;
        $ForwardToWpPageEntryInNewTabOptions = <<<HEREDOC
<div class="cg_view_options_row ForwardToWpPageEntryInNewTabContainer cg_hide">
     <div class='cg_view_option  cg_view_option_100_percent cg_border_top_none  '>
            <div class='cg_view_option_title'>
                        <p>Forward to entry landing page in new tab</p>
            </div>
            <div class='cg_view_option_checkbox'>
                <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][ForwardToWpPageEntryInNewTab]" class="ForwardToWpPageEntryInNewTab cg_shortcode_checkbox"  checked="$ForwardToWpPageEntryInNewTab"  />
            </div>
    </div>
</div>
HEREDOC;
    }
    echo <<<HEREDOC
<div class='cg_view_options_rows_container'>

        <p class='cg_view_options_rows_container_title'>Gallery slide out, slider view or blog view</p>
        
        <div class='cg_view_options_row'>
                <div class='cg_view_option  cg_view_option_full_width'>
                    <div class='cg_view_option_title'>
                        <p>Open entry style<br><span class="cg_view_option_title_note">Select how a file image should be opened on click in a gallery</span></p>
                    </div>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container BlogLookFullWindowContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Full window blog view
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][visual][BlogLookFullWindow]" class="BlogLookFullWindow cg_view_option_radio_multiple_input_field"  $BlogLookFullWindow  />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container SliderFullWindowContainer'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Full window slider
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][pro][SliderFullWindow]" class="SliderFullWindow cg_view_option_radio_multiple_input_field"  $SliderFullWindow   />
                            </div>
                        </div>
                        $ForwardToWpPageEntryOptions
                </div>
            </div>
        </div>
       $ForwardToWpPageEntryInNewTabOptions
HEREDOC;
}

if(!isset($jsonOptions[$GalleryID.'-ec']['general']['AllowComments'])){
    $AllowComments1 = ($AllowComments==1) ? 'checked' : '';
    $AllowComments2 = ($AllowComments==2) ? 'checked' : '';
    $AllowComments0 = ($AllowComments==0) ? 'checked' : '';
}else{
    $AllowComments1 = ($jsonOptions[$GalleryID.'-ec']['general']['AllowComments']==1) ? 'checked' : '';
    $AllowComments2 = ($jsonOptions[$GalleryID.'-ec']['general']['AllowComments']==2) ? 'checked' : '';
    $AllowComments0 = ($jsonOptions[$GalleryID.'-ec']['general']['AllowComments']==0) ? 'checked' : '';
}

echo <<<HEREDOC
        <div class='cg_view_options_row_container AllowCommentsParentContainer'>
        <p class="cg_view_options_row_container_title" >Comment options
                <br><span style="font-weight: normal;">Comment notification e-mail options can be configured 
<a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="CommentNotificationArea">here</a></span>
        </p>
        <div class='cg_view_options_row cg_go_to_target' data-cg-go-to-target="AllowCommentsArea">
                <div class='cg_view_option  cg_view_option_full_width AllowCommentsContainer'>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Show comments<br>and allow to comment
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][general][AllowComments]" class="cg_view_option_radio_multiple_input_field "  $AllowComments1 value="1"  />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Show comments only
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][general][AllowComments]" class="cg_view_option_radio_multiple_input_field "  $AllowComments2 value="2"    />
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Disable comments
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="multiple-pics[cg_gallery_ecommerce][general][AllowComments]" class="cg_view_option_radio_multiple_input_field " $AllowComments0   value="0"  />
                            </div>
                        </div>
                </div>
            </div>
        </div>
HEREDOC;


echo <<<HEREDOC
<div class='cg_view_options_row AllowCommentsContainer'>
    <div  class='cg_view_option  cg_border_top_none  cg_view_option_full_width  cg_view_option_flex_flow_column AllowCommentsContainer'>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
        <p>Comments date format shown in frontend</p>
        </div>
        <div class="cg_view_option_select">
        <select name="multiple-pics[cg_gallery_ecommerce][visual][CommentsDateFormat]">
HEREDOC;

if(empty($jsonOptions[$GalleryID.'-ec']['visual']['CommentsDateFormat'])){
    $CommentsDateFormat = '';
}else{
    $CommentsDateFormat = $jsonOptions[$GalleryID.'-ec']['visual']['CommentsDateFormat'];
}

foreach($CommentsDateFormatNamePathSelectedValuesArray as  $key => $CommentsDateFormatNamePathSelectedValuesArrayValue){
    $CommentsDateFormatNamePathSelectedValuesArrayValueSelected = '';
    if($key==$CommentsDateFormat){
        $CommentsDateFormatNamePathSelectedValuesArrayValueSelected = 'selected';
    }
    echo "<option value='$key' $CommentsDateFormatNamePathSelectedValuesArrayValueSelected >$CommentsDateFormatNamePathSelectedValuesArrayValue</option>";
}

echo <<<HEREDOC
                               </select>
        </div>
    </div>
</div>
HEREDOC;

// only json option, not in database available
if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['EnableEmojis'])){
    $jsonOptions[$GalleryID.'-ec']['visual']['EnableEmojis'] = 1;
}

echo <<<HEREDOC
        <div class="cg_view_options_row">
                <div class="cg_view_option  cg_view_option_100_percent cg_border_top_none" >
                    <div class="cg_view_option_title">
                        <p>Enable emojis</p>
                    </div>
                    <div class="cg_view_option_checkbox cg_view_option_checked">
                        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][EnableEmojis]" class="cg_shortcode_checkbox" checked="{$jsonOptions[$GalleryID.'-ec']['visual']['EnableEmojis']}" >
                    </div>
                </div>
        </div>
HEREDOC;

if(!empty($jsonOptions[$GalleryID.'-ec']['general']['HideCommentNameField'])){
    $HideCommentNameField = '1';
}else{
    $HideCommentNameField = '0';
}

echo <<<HEREDOC
        <div class="cg_view_options_row">
                <div class="cg_view_option  cg_view_option_100_percent cg_border_top_none" >
                    <div class="cg_view_option_title">
                        <p>Hide enter "Name" in comments area for not logged in users<br>
                        <span class="cg_view_option_title_note">
                        Only comment textarea will be visible and required to enter<br>Comment form translations (for name, comment etc.)<br>can be found
<a class=" cg_no_outline_and_shadow_on_focus" href="{$editTranslationLink}TranslationsCommentFormArea"   target="_blank">here</a>
<br><strong><span class="cg_color_red">NOTE:</span> If logged in user comment then "Nickname" (WordPress profile field) and "Profile image" (available in "Edit registration form") will be shown</strong>
</span></p>
                    </div>
                    <div class="cg_view_option_checkbox cg_view_option_checked">
                        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][HideCommentNameField]" class="cg_shortcode_checkbox" checked="$HideCommentNameField">
                    </div>
                </div>
        </div>
HEREDOC;

// only json option, not in database available
if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['CheckLoginComment'])){
    $jsonOptions[$GalleryID.'-ec']['pro']['CheckLoginComment'] = 0;
}

echo <<<HEREDOC
<div class="cg_view_options_row">
        <div class="cg_view_option $cgProFalse cg_view_option_100_percent cg_border_top_none $cgProFalse" >
            <div class="cg_view_option_title">
                <p>Allow only logged in users to comment</p>
            </div>
            <div class="cg_view_option_checkbox cg_view_option_checked">
                <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][pro][CheckLoginComment]" class="cg_shortcode_checkbox" checked="{$jsonOptions[$GalleryID.'-ec']['pro']['CheckLoginComment']}">
            </div>
        </div>
</div>
</div>
HEREDOC;


if(empty($jsonOptions[$GalleryID.'-ec'])){
    $GalleryStyleCenterWhiteChecked = '';
}else{
    if(empty($jsonOptions[$GalleryID.'-ec']['visual']['GalleryStyle'])){
        $GalleryStyleCenterWhiteChecked = '';
    }else{
        $GalleryStyleCenterWhiteChecked = ($jsonOptions[$GalleryID.'-ec']['visual']['GalleryStyle']=='center-white') ? 'checked' : '';
    }
}

if(empty($jsonOptions[$GalleryID.'-ec'])){
    $GalleryStyleCenterBlackChecked = 'checked';
}else{
    if(empty($jsonOptions[$GalleryID.'-ec']['visual']['GalleryStyle'])){
        $GalleryStyleCenterBlackChecked = 'checked';
    }else{
        $GalleryStyleCenterBlackChecked = ($jsonOptions[$GalleryID.'-ec']['visual']['GalleryStyle']=='center-black') ? 'checked' : '';
    }
}


$SlideTransitionSlideHorizontalChecked = ($jsonOptions[$GalleryID.'-ec']['pro']['SlideTransition']=='translateX') ?  'checked' :  '';

$SlideTransitionSlideDownChecked = ($jsonOptions[$GalleryID.'-ec']['pro']['SlideTransition']=='slideDown') ?  'checked' :  '';

if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['EnableSwitchStyleImageViewButton'])){
    $jsonOptions[$GalleryID.'-ec']['visual']['EnableSwitchStyleImageViewButton'] = 0;
}

if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['SwitchStyleImageViewButtonOnlyImageView'])){
    $jsonOptions[$GalleryID.'-ec']['visual']['SwitchStyleImageViewButtonOnlyImageView'] = 0;
}

$FullSizeSlideOutStartContainer = '';
if(floatval($galleryDbVersion)<15.05) {
    $FullSizeSlideOutStartContainer = <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option  cg_view_option_100_percent cg_border_top_none FullSizeSlideOutStartContainer'>
                    <div class='cg_view_option_title'>
                        <p>"Gallery slide out" has to be activated above<br>Start gallery full window view<br>
        as slide out by clicking an image<br><span class="cg_view_option_title_note">Will not start automatically full window when clicking image in slider view</span></p>
                    </div>
                    <div class='cg_view_option_checkbox'>
                        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][FullSizeSlideOutStart]" checked="{$jsonOptions[$GalleryID . '-ec']['general']['FullSizeSlideOutStart']}" class="cg_shortcode_checkbox FullSizeSlideOutStart">
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

echo <<<HEREDOC
            $FullSizeSlideOutStartContainer
HEREDOC;

if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['ShareButtons'])){
    $jsonOptions[$GalleryID.'-ec']['visual']['ShareButtons'] = $ShareButtons;
}

echo <<<HEREDOC
<div class="cg_view_options_row ShareButtonsHiddenRow" >
           <input type='hidden' name="multiple-pics[cg_gallery_ecommerce][visual][ShareButtons]" value='{$jsonOptions[$GalleryID . '-ec']['visual']['ShareButtons']}' class='ShareButtonsHiddenInput' />
            <div class="cg_view_option  cg_view_option_full_width cg_border_bottom_none " style="margin-bottom: -25px;" >
                <div class="cg_view_option_title ">
                    <p>Social share buttons</p>
                </div>                    
        </div>
</div>
HEREDOC;

$ShareButtonsExploded = explode(',',$jsonOptions[$GalleryID.'-ec']['visual']['ShareButtons']);
$EmailChecked = (in_array('email',$ShareButtonsExploded)!==false) ? 'checked' : '';
$SmsChecked = (in_array('sms',$ShareButtonsExploded)!==false) ? 'checked' : '';
$GmailChecked = (in_array('gmail',$ShareButtonsExploded)!==false) ? 'checked' : '';
$YahooChecked = (in_array('yahoo',$ShareButtonsExploded)!==false) ? 'checked' : '';
$EvernoteChecked = (in_array('evernote',$ShareButtonsExploded)!==false) ? 'checked' : '';

echo <<<HEREDOC
<div class="cg_view_options_row $ShareButtonsHide">
    <div class="cg_view_option   cg_view_option_20_percent cg_border_top_bottom_none cg_border_right_none cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>Email</p>
        </div>
        <div class="cg_view_option_checkbox">
            <input type="checkbox" $EmailChecked class="cg_share_button" value="email">
        </div>
    </div>
    <div class="cg_view_option   cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none cg_share_button_option">
        <div class="cg_view_option_title">
            <p>SMS</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox"  class="cg_share_button" value="sms">
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
       <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none  cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>Yahoo</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $YahooChecked class="cg_share_button" value="yahoo">
        </div>
    </div>
    <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_share_button_option  ">
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
    <div class="cg_view_option  $cgProFalse  cg-pro-false-small  cg_view_option_20_percent cg_border_top_bottom_none cg_border_right_none cg_share_button_option ">
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
       <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none  cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>Telegram</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $TelegramChecked class="cg_share_button" value="telegram">
        </div>
    </div>
    <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_share_button_option  ">
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
       <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none  cg_border_right_none  cg_share_button_option">
        <div class="cg_view_option_title">
            <p>Pinterest</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $PinterestChecked class="cg_share_button" value="pinterest">
        </div>
    </div> 
       <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none  cg_border_right_none cg_share_button_option">
        <div class="cg_view_option_title">
            <p>Reddit</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $RedditAppChecked class="cg_share_button" value="reddit">
        </div>
    </div>
       <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none  cg_border_left_none  cg_border_right_none   cg_share_button_option">
        <div class="cg_view_option_title">
            <p>XING</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $XINGChecked class="cg_share_button" value="xing">
        </div>
    </div>
       <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none  cg_border_left_none  cg_border_right_none cg_share_button_option  ">
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
       <div class="cg_view_option  $cgProFalse cg-pro-false-small cg_view_option_20_percent cg_border_top_bottom_none  cg_border_right_none cg_share_button_option ">
        <div class="cg_view_option_title">
            <p>OK</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $OkRuChecked class="cg_share_button" value="okru">
        </div>
    </div> 
 <div class="cg_view_option   $cgProFalse  cg-pro-false-small  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none  cg_border_right_none cg_share_button_option ">
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


$jsonOptions[$GalleryID.'-ec']['visual']['ImageViewFullWindow'] = (!isset($jsonOptions[$GalleryID.'-ec']['visual']['ImageViewFullWindow'])) ? 1 : $jsonOptions[$GalleryID.'-ec']['visual']['ImageViewFullWindow'];

$jsonOptions[$GalleryID.'-ec']['visual']['ImageViewFullScreen'] = (!isset($jsonOptions[$GalleryID.'-ec']['visual']['ImageViewFullScreen'])) ? 1 : $jsonOptions[$GalleryID.'-ec']['visual']['ImageViewFullScreen'];

$jsonOptions[$GalleryID.'-ec']['visual']['CopyOriginalFileLink'] = 0;

$jsonOptions[$GalleryID.'-ec']['visual']['ForwardOriginalFile'] = 0;

$jsonOptions[$GalleryID.'-ec']['visual']['OriginalSourceLinkInSlider'] = 0;

$jsonOptions[$GalleryID.'-ec']['visual']['CopyImageLink'] = (!isset($jsonOptions[$GalleryID.'-ec']['visual']['CopyImageLink'])) ? $CopyImageLink : $jsonOptions[$GalleryID.'-ec']['visual']['CopyImageLink'];

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option  cg_view_option_50_percent cg_border_right_none  cg_border_bottom_none  CopyOriginalFileLinkContainer'>
            <div class='cg_view_option_title cg_view_option_title_full_width'>
                <p>Copy original file source link button<br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE: </span>not available for cg_gallery_ecommerce shortcode</span></p>
            </div>
            <div class='cg_view_option_checkbox cg_hide'>
                <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][CopyOriginalFileLink]" checked="{$jsonOptions[$GalleryID.'-ec']['visual']['CopyOriginalFileLink']}" class="cg_shortcode_checkbox CopyOriginalFileLink"   />
            </div>
        </div>
        <div class='cg_view_option  cg_view_option_50_percent  cg_border_bottom_none ForwardOriginalFileContainer'>
            <div class='cg_view_option_title cg_view_option_title_full_width'>
                <p>Forward to original file source button<br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE: </span>not available for cg_gallery_ecommerce shortcode</span></p>
            </div>
            <div class='cg_view_option_checkbox cg_hide'>
                <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][ForwardOriginalFile]" checked="{$jsonOptions[$GalleryID.'-ec']['visual']['ForwardOriginalFile']}" class="cg_shortcode_checkbox ForwardOriginalFile"   />
            </div>
        </div>
   </div>
HEREDOC;

echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option   cg_view_option_50_percent cg_border_right_none  OriginalSourceLinkInSliderContainer'>
                    <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p>Download original file button<br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE: </span>not available for cg_gallery_ecommerce shortcode</span></p>
                    </div>
                    <div class='cg_view_option_checkbox cg_hide'>
                        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][OriginalSourceLinkInSlider]" checked="{$jsonOptions[$GalleryID.'-ec']['visual']['OriginalSourceLinkInSlider']}" class="cg_shortcode_checkbox OriginalSourceLinkInSlider">
                    </div>
                </div>
                <div class='cg_view_option  cg_view_option_50_percent CopyImageLinkContainer '>
                    <div class='cg_view_option_title cg_view_option_title_quarter'>
                        <p>Copy gallery entry link button</p>
                    </div>
                    <div class='cg_view_option_checkbox cg_view_option_title_quarter'>
                        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][visual][CopyImageLink]" checked="{$jsonOptions[$GalleryID.'-ec']['visual']['CopyImageLink']}" class="cg_shortcode_checkbox CopyImageLink">
                    </div>
                </div>
           </div>
HEREDOC;

$ShowExifModel = (!empty($jsonOptions[$GalleryID.'-ec']['general']['ShowExifModel']) OR !isset($jsonOptions[$GalleryID.'-ec']['general']['ShowExifModel'])) ? '1' : '0';
$ShowExifApertureFNumber = (!empty($jsonOptions[$GalleryID.'-ec']['general']['ShowExifApertureFNumber']) OR !isset($jsonOptions[$GalleryID.'-ec']['general']['ShowExifApertureFNumber'])) ? '1' : '0';
$ShowExifExposureTime = (!empty($jsonOptions[$GalleryID.'-ec']['general']['ShowExifExposureTime']) OR !isset($jsonOptions[$GalleryID.'-ec']['general']['ShowExifExposureTime'])) ? '1' : '0';
$ShowExifISOSpeedRatings = (!empty($jsonOptions[$GalleryID.'-ec']['general']['ShowExifISOSpeedRatings']) OR !isset($jsonOptions[$GalleryID.'-ec']['general']['ShowExifISOSpeedRatings'])) ? '1' : '0';
$ShowExifFocalLength = (!empty($jsonOptions[$GalleryID.'-ec']['general']['ShowExifFocalLength']) OR !isset($jsonOptions[$GalleryID.'-ec']['general']['ShowExifFocalLength'])) ? '1' : '0';
$ShowExifDateTimeOriginal = (!empty($jsonOptions[$GalleryID.'-ec']['general']['ShowExifDateTimeOriginal'])) ? '1' : '0';// another condition because of possible pro feature
$ShowExifDateTimeOriginalFormat = (!empty($jsonOptions[$GalleryID.'-ec']['general']['ShowExifDateTimeOriginalFormat'])) ? $jsonOptions[$GalleryID.'-ec']['general']['ShowExifDateTimeOriginalFormat'] : 'YYYY-MM-DD';

if(function_exists('exif_read_data')){
    echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option  cg_view_option_100_percent cg_border_top_none ShowExifContainer'>
                    <div class='cg_view_option_title'>
                        <p>Show image EXIF data<br><span class="cg_view_option_title_note">If an image has a EXIF data</span></p>
                    </div>
                    <div class='cg_view_option_checkbox'>
                       <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][pro][ShowExif]" checked="{$jsonOptions[$GalleryID.'-ec']['pro']['ShowExif']}" class="cg_shortcode_checkbox ShowExif">
                    </div>
                </div>
           </div>
HEREDOC;
} else{
    echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option  cg_view_option cg_border_top_none  cg_view_option_100_percent'>
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
                <div class="cg_view_option $cgProFalse cg_view_option_full_width cg_border_top_bottom_none ShowExifOption">
                    <div class="cg_view_option_title ">
                        <p>EXIF data to show in frontend<br><span class="cg_view_option_title_note">Select which EXIF data should be shown.<br>Images might not have EXIF data which can be shown.$showExifDateTimeRepairFrontendNote</span></p>
                    </div>                    
            </div>
        </div>

<div class='cg_view_options_row'>

    <div  class='cg_view_option $cgProFalse cg_border_top_bottom_none  cg_border_right_none $cgProFalse ShowExifOption'>
        <div  class='cg_view_option_title'>
            <p>Original image date</p>
        </div>
        <div class='cg_view_option_checkbox'>
            <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][ShowExifDateTimeOriginal]" class="ShowExifDateTimeOriginal cg_shortcode_checkbox" checked="$ShowExifDateTimeOriginal"><br/>
        </div>

    </div>

    <div  class='cg_view_option $cgProFalse  cg_border_top_none  cg_border_top_bottom_none  cg_border_left_right_none cg_view_option_flex_flow_column   ShowExifOption'>
        <div class='cg_view_option_title cg_view_option_title_full_width'>
        <p>Original image date format<br>to show in frontend</p>
        </div>
        <div class="cg_view_option_select">
        <select name="multiple-pics[cg_gallery_ecommerce][general][ShowExifDateTimeOriginalFormat]" class="$cgProFalse" >
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

    <div  class='cg_view_option   cg_border_top_bottom_none cg_border_left_none ShowExifOption'>
        <div class='cg_view_option_title cg_border_top_bottom_none cg_border_right_none'>
        <p>Camera model</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][ShowExifModel]" class="ShowExifModel cg_shortcode_checkbox" checked="$ShowExifModel"><br/>
        </div>
    </div>

    
</div>
    
 <div class='cg_view_options_row'>
 
 
    <div  class='cg_view_option  cg_view_option_25_percent cg_border_top_bottom_none cg_border_right_none ShowExifOption '>
        <div class='cg_view_option_title'>
        <p>Aperture</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][ShowExifApertureFNumber]" class="ShowExifApertureFNumber cg_shortcode_checkbox" checked="$ShowExifApertureFNumber"><br/>
        </div>
    </div>

    <div  class='cg_view_option  cg_view_option_25_percent cg_border_top_bottom_none  cg_border_left_right_none  ShowExifOption'>
        <div  class='cg_view_option_title'>
        <p>Exposure time</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][ShowExifExposureTime]" class="ShowExifExposureTime cg_shortcode_checkbox" checked="$ShowExifExposureTime"><br/>
        </div>
    </div>
   
    <div  class='cg_view_option  cg_view_option_25_percent cg_border_top_bottom_none  cg_border_left_right_none ShowExifOption'>
        <div class='cg_view_option_title'>
        <p>ISO</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][ShowExifISOSpeedRatings]" class="ShowExifISOSpeedRatings cg_shortcode_checkbox" checked="$ShowExifISOSpeedRatings"><br/>
        </div>
    </div>

    <div  class='cg_view_option  cg_view_option_25_percent cg_border_top_bottom_none  cg_border_left_none ShowExifOption'>
        <div class='cg_view_option_title'>
        <p>Focal length</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][general][ShowExifFocalLength]" class="ShowExifFocalLength cg_shortcode_checkbox" checked="$ShowExifFocalLength"><br/>
        </div>
    </div>
</div>
<div class='cg_view_options_row cg_view_options_row_empty_placeholder ShowExifOption' style="padding-bottom: 15px;border-left: thin solid #dedede;border-right: thin solid #dedede;box-sizing: border-box;border-width:0.5px;">

</div>


HEREDOC;

echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option $cgProFalse cg_view_option_100_percent  ShowNicknameContainer $cgProFalse'>
                    <div class='cg_view_option_title '>
                        <p>Show "Nickname" who added entry<br><span class="cg_view_option_title_note">If a registered user uploaded a file</span></p>
                    </div>
                    <div class='cg_view_option_checkbox '>
                        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][pro][ShowNickname]" checked="{$jsonOptions[$GalleryID.'-ec']['pro']['ShowNickname']}" class="cg_shortcode_checkbox ShowNickname">
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


if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['ShowProfileImage'])){
    $jsonOptions[$GalleryID.'-ec']['pro']['ShowProfileImage'] = 0;
}

echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option $cgProFalse cg_view_option_100_percent cg_border_top_none  ShowProfileImageContainer $cgProFalse$beforeSinceV14Disabled'>
                    <div class='cg_view_option_title '>
                        <p>Show "Profile image" who added entry<br><span class="cg_view_option_title_note">If a registered user uploaded a file<br>"Profile image" form field has to be added in "Edit registration form"$beforeSinceV14Explanation</span></p>
                    </div>
                    <div class='cg_view_option_checkbox '>
                        <input type="checkbox" name="multiple-pics[cg_gallery_ecommerce][pro][ShowProfileImage]" checked="{$jsonOptions[$GalleryID.'-ec']['pro']['ShowProfileImage']}" class="cg_shortcode_checkbox ShowProfileImage">
                    </div>
                </div>
            </div>
        </div>
HEREDOC;

if(floatval($galleryDbVersion)>=21){
    if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['BackToGalleryButtonText'])){
        $jsonOptions[$GalleryID.'-ec']['visual']['BackToGalleryButtonText'] = $BackToGalleryButtonText;
    }
    if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleryButtonURL'])){
        $jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleryButtonURL'] = '';
    }else{
        $jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleryButtonURL'] = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleryButtonURL']);
    }
    if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['TextBeforeWpPageEntry'])){
        $jsonOptions[$GalleryID.'-ec']['visual']['TextBeforeWpPageEntry'] = $TextBeforeWpPageEntry;
    }else{
        $jsonOptions[$GalleryID.'-ec']['visual']['TextBeforeWpPageEntry'] = contest_gal1ery_convert_for_html_output($jsonOptions[$GalleryID.'-ec']['visual']['TextBeforeWpPageEntry']);
    }
    if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['TextAfterWpPageEntry'])){
        $jsonOptions[$GalleryID.'-ec']['visual']['TextAfterWpPageEntry'] = $TextAfterWpPageEntry;
    }else{
        $jsonOptions[$GalleryID.'-ec']['visual']['TextAfterWpPageEntry'] = contest_gal1ery_convert_for_html_output($jsonOptions[$GalleryID.'-ec']['visual']['TextAfterWpPageEntry']);
    }
    if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['ShowBackToGalleryButton'])){
        $jsonOptions[$GalleryID.'-ec']['visual']['ShowBackToGalleryButton'] = 0;
    }
    if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['TextDeactivatedEntry'])){
        $jsonOptions[$GalleryID.'-ec']['visual']['TextDeactivatedEntry'] = $TextDeactivatedEntry;
    }else{
        $jsonOptions[$GalleryID.'-ec']['visual']['TextDeactivatedEntry'] = contest_gal1ery_convert_for_html_output($jsonOptions[$GalleryID.'-ec']['visual']['TextDeactivatedEntry']);
    }
    if(!isset($jsonOptions[$GalleryID.'-ec']['pro']['RedirectURLdeletedEntry'])){
        $jsonOptions[$GalleryID.'-ec']['pro']['RedirectURLdeletedEntry'] = $RedirectURLdeletedEntry;
    }else{
        $jsonOptions[$GalleryID.'-ec']['pro']['RedirectURLdeletedEntry'] = contest_gal1ery_convert_for_html_output_without_nl2br($jsonOptions[$GalleryID.'-ec']['pro']['RedirectURLdeletedEntry']);
    }

    $ShowBackToGalleryButtonRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option  cg_view_option_100_percent  '>
        <div class='cg_view_option_title '>
            <p>Show back to gallery button on entry landing page</p>
        </div>
        <div class='cg_view_option_checkbox '>
            <input type="checkbox" name='multiple-pics[cg_gallery_ecommerce][visual][ShowBackToGalleryButton]'  class="cg_shortcode_checkbox ShowBackToGalleryButton"  checked="{$jsonOptions[$GalleryID.'-ec']['visual']['ShowBackToGalleryButton']}"  >
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
    <div class='cg_view_option  cg_view_option_full_width cg_border_top_none   '>
        <div class='cg_view_option_title '>
            <p>Back to gallery button custom URL<br><span class="cg_view_option_title_note">If not set then parent site URL will be used<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
        </div>
        <div class='cg_view_option_input '>
            <input type="text" name="multiple-pics[cg_gallery_ecommerce][pro][BackToGalleryButtonURL]" class="BackToGalleryButtonURL"  value="{$jsonOptions[$GalleryID.'-ec']['pro']['BackToGalleryButtonURL']}"   >
        </div>
    </div>
</div>
HEREDOC;

    $TextBeforeWpPageEntryRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option  cg_view_option_full_width cg_border_top_none' id="wp-TextBeforeWpPageEntryEcommerce-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on entry landing page before an activated entry
            <br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE: </span>appears only on entry landing page, not if cg_gallery shortcode with entry_id is copied on some other page</span>
            </p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery_ecommerce][visual][TextBeforeWpPageEntry]'  id='TextBeforeWpPageEntryEcommerce'>{$jsonOptions[$GalleryID.'-ec']['visual']['TextBeforeWpPageEntry']}</textarea>
        </div>
    </div>
</div>
HEREDOC;

    $TextAfterWpPageEntryRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option  cg_view_option_full_width cg_border_top_none' id="wp-TextAfterWpPageEntryEcommerce-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on entry landing page after an activated entry
            <br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE: </span>appears only on entry landing page, not if cg_gallery shortcode with entry_id is copied on some other page</span>
            </p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery_ecommerce][visual][TextAfterWpPageEntry]'  id='TextAfterWpPageEntryEcommerce'>{$jsonOptions[$GalleryID.'-ec']['visual']['TextAfterWpPageEntry']}</textarea>
        </div>
    </div>
</div>
HEREDOC;

    $TextDeactivatedEntryRow = <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option  cg_view_option_full_width cg_border_top_none' id="wp-TextDeactivatedEntryEcommerce-wrap-Container">
        <div class='cg_view_option_title'>
            <p>Text on entry landing page if entry is deactivated</p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='multiple-pics[cg_gallery_ecommerce][visual][TextDeactivatedEntry]'  id='TextDeactivatedEntryEcommerce'>{$jsonOptions[$GalleryID.'-ec']['visual']['TextDeactivatedEntry']}</textarea>
        </div>
    </div>
</div>
HEREDOC;

    // currently will be not used, deleted ids needs to be saved some for all galleries or htaccess redirect rule needs to be written
    $RedirectURLdeletedEntryRow = <<<HEREDOC
    <div class='cg_view_options_row cg_hide'>
        <div class='cg_view_option  cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Redirect (HTTP 301) URL if gallery entry was deleted<br><span class="cg_view_option_title_note">If not set then parent site URL will be used<br>If Redirect URL is set, then HTTP 301 redirect will be done</span></span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='multiple-pics[cg_gallery_ecommerce][pro][RedirectURLdeletedEntry]' class="RedirectURLdeletedEntry"  value="{$jsonOptions[$GalleryID.'-ec']['pro']['RedirectURLdeletedEntry']}"  >
            </div>
        </div>
    </div>
HEREDOC;

// only json option, not in database available
    if(!isset($jsonOptions[$GalleryID.'-ec']['visual']['AdditionalCssEntryLandingPage'])){
        $AdditionalCssEntryLandingPage = "body {\r\n&nbsp;&nbsp;font-family:sans-serif;\r\n&nbsp;&nbsp;font-size:16px;\r\n&nbsp;&nbsp;background-color:white;\r\n&nbsp;&nbsp;color:black;\r\n}";
    }else{
        $AdditionalCssEntryLandingPage = ($jsonOptions[$GalleryID.'-ec']['visual']['AdditionalCssEntryLandingPage']);
    }

    $AdditionalCssEntryLandingPageRow = <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option  cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Additional CSS on entry landing page</p>
            </div>
            <div class='cg_view_option_textarea' >
                <textarea type="text" name="multiple-pics[cg_gallery_ecommerce][visual][AdditionalCssEntryLandingPage]" rows="7" style="width:100%;" class="AdditionalCssEntryLandingPage"  >$AdditionalCssEntryLandingPage</textarea>
            </div>
        </div>
    </div>
HEREDOC;

    echo <<<HEREDOC
        <div class='cg_view_options_row_container EntryLadingPageOptionsParentContainer cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px' style="border-bottom: thin solid #dedede;border-bottom-width: 0.5px;">
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

$jsonOptions[$GalleryID.'-ec']['general']['OnlyGalleryView'] = 0;
$jsonOptions[$GalleryID.'-ec']['general']['FullSizeImageOutGallery'] = 0;

echo <<<HEREDOC

   <div class='cg_view_options_rows_container cg_pointer_events_none'>

        <p class='cg_view_options_rows_container_title'>Original source link only</p>

        <div class='cg_view_options_row'>
            <div class='cg_view_option  cg_view_option_100_percent FullSizeImageOutGalleryContainer'>
                <div class='cg_view_option_title'>
                    <p>Forward directly to original source after clicking an entry from in a gallery<br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE: </span>not available for cg_gallery_ecommerce shortcode</span></p>
                </div>
                <div class='cg_view_option_radio cg_margin_top_5 cg_hide'>
                    <input type="radio" name="multiple-pics[cg_gallery_ecommerce][general][FullSizeImageOutGallery]" checked="{$jsonOptions[$GalleryID.'-ec']['general']['FullSizeImageOutGallery']}" class="FullSizeImageOutGallery">
                </div>
            </div>
        </div>

    </div>

    <div class='cg_view_options_rows_container cg_pointer_events_none'>

        <p class='cg_view_options_rows_container_title'>Only gallery view</p>

        <div class='cg_view_options_row'>
            <div class='cg_view_option  cg_view_option_100_percent OnlyGalleryViewContainer'>
                <div class='cg_view_option_title'>
                    <p>Make entries unclickable<br>Good for displaying entries only<br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE: </span>not available for cg_gallery_ecommerce shortcode</span></p>
                </div>
                <div class='cg_view_option_radio cg_margin_top_5 cg_hide'>
                       <input type="radio" name="multiple-pics[cg_gallery_ecommerce][general][OnlyGalleryView]" checked="{$jsonOptions[$GalleryID.'-ec']['general']['OnlyGalleryView']}" class="OnlyGalleryView">
                </div>
            </div>
        </div>

    </div>
HEREDOC;

echo <<<HEREDOC

    </div>
</div>
HEREDOC;


