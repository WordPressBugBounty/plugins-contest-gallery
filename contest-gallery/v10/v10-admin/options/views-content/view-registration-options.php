<?php

echo <<<HEREDOC
<div class='cg_view_container'>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_rows_container' >
HEREDOC;

if($cgBeforeSinceV14ExplanationRequired){
    echo <<<HEREDOC
        <p class="cg_view_options_rows_container_title ">
                <strong>NOTE:</strong> For galleries created or copied in plugin version 14 or higher
                 "Registration options"  are general<br>and valid for all galleries created or copied in plugin version 14 or higher.<br>
        </p>
HEREDOC;
}else{
    if(intval($galleryDbVersion)>=14){// only if higher then 14 then this explanation required!
        echo <<<HEREDOC
        <p class="cg_view_options_rows_container_title ">
                <strong>NOTE:</strong> Registration options are valid for all galleries.<br>
        </p>
HEREDOC;
    }
}

$cgV14UserGroupRoleNote = '';

if(intval($galleryDbVersion)>=14){
    $cgV14UserGroupRoleNote = '<br><span class="cg_view_option_title_note">
                            <span class="cg_color_red">NOTE:</span> For galleries created or copied after version 14 new "Contest Gallery User since v14" will be used.<br>
                            <b>"Contest Gallery User since v14" allows to edit Contest Gallery registration form fields in user profile when logged in.<br>You can add further groups below which can edit Contest Gallery registration form fields in user profile.</b>
                            </span>';
}else{
    $cgV14UserGroupRoleNote = '<br><span class="cg_view_option_title_note">
                            <span class="cg_color_red">NOTE:</span> For galleries created or copied before version 14 old "Contest Gallery User" will be used.<br>
                            <b>Create a new gallery or copy the current to be able to use the new "Contest Gallery User since v14" role<br>with capability to edit user profile when logged in.</b>
                            </span>';
}

echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class="cg_view_option cg_view_option_100_percent cg_border_bottom_none" >
                    <div class="cg_view_option_title" style="width: 100%;">
                        <p style="margin-left: 0;">Two different user registration shortcode types available</p>
                    </div>
                </div>
           </div>
            <div class='cg_view_options_row'>
                <div class="cg_view_option cg_view_option_50_percent cg_border_bottom_none cg_border_right_none ">
                    <div class="cg_view_option_title" style="width: 100%;">
                        <p><code class="cg_shortcode_copy cg_tooltip"><span>[cg_users_reg]</span></code><br><span class="cg_view_option_title_note">User receives an email and <b>confirms 
        registration by clicking a link</b> (<b>page opens</b>).<br>
        <b>Typical registration form</b> requiring password, username, and nickname.</span></p>
                    </div>
                </div>
                <div class="cg_view_option cg_view_option_50_percent cg_border_bottom_none" >
                    <div class="cg_view_option_title"  style="width: 100%;">
                        <p><code class="cg_shortcode_copy cg_tooltip"><span>[cg_users_pin]</span></code><br><span class="cg_view_option_title_note">User receives an <b>email with a PIN</b> and 
        <b>confirms it on the same page</b> (<b>no page reload</b>).<br><b>Only the email field is required</b> to fill out.<br>Additional fields can be added in "Edit user form".</span></p>
                    </div>
                </div>
           </div>
        <div class='cg_view_options_row cg_go_to_target'  data-cg-go-to-target="RegOptionsUserGroupRolesContainer" >
            <div class="cg_view_option cg_view_option_full_width" id="RegistryUserRoleContainer">
                <div class="cg_view_option_title">
                    <p>Select user role group for registered users over Contest Gallery user registration form
                        <br>
                        or Contest Gallery Google sign in button
                        $cgV14UserGroupRoleNote
                    </p>
                </div>
                <div class="cg_view_option_select">
                    <select name='RegistryUserRole'>
HEREDOC;
echo "<option value=''>No role</option>";

$roles = get_editable_roles();



// show as last!!!!
if(intval($galleryDbVersion)>=14){
    $wordPressRolesAndContestGalleryRoleKeys = ["contest_gallery_user_since_v14","subscriber", "contributor", "editor", "author", "administrator"];
    //$cgRegistryUserRoleSelected = ($RegistryUserRole=='contest_gallery_user_since_v14') ? 'selected' : '';
    //echo "<option value='contest_gallery_user_since_v14' $cgRegistryUserRoleSelected>Contest Gallery User since v14</option>";
}else{
    $wordPressRolesAndContestGalleryRoleKeys = ["contest_gallery_user","subscriber", "contributor", "editor", "author", "administrator"];
    //$cgRegistryUserRoleSelected = ($RegistryUserRole=='contest_gallery_user') ? 'selected' : '';
    //echo "<option value='contest_gallery_user' $cgRegistryUserRoleSelected>Contest Gallery User</option>";
}

foreach($roles as $keyOfRole => $roleValues){
    if(intval($galleryDbVersion)>=14){
        if($keyOfRole=='contest_gallery_user'){// not selectable if gallery created after v14
            continue;
        }
    }else{
        if($keyOfRole=='contest_gallery_user_since_v14'){// not selectable if gallery created before v14
            continue;
        }
    }
   // if(in_array($keyOfRole,$wordPressRolesAndContestGalleryRoleKeys)){
       // continue;
   // }
    $otherRegistryUserRoleSelected = ($RegistryUserRole==$keyOfRole) ? 'selected' : '';
    echo "<option value='$keyOfRole' $otherRegistryUserRoleSelected>".$roleValues['name']."</option>";
    // subscriber, contributor, editor, author, administrator
}

/*
$subscriberRegistryUserRoleSelected = ($RegistryUserRole=='subscriber') ? 'selected' : '';
echo "<option value='subscriber' $subscriberRegistryUserRoleSelected>Subscriber</option>";
$contributorRegistryUserRoleSelected = ($RegistryUserRole=='contributor') ? 'selected' : '';
echo "<option value='contributor' $contributorRegistryUserRoleSelected>Contributor</option>";
$editorRegistryUserRoleSelected = ($RegistryUserRole=='editor') ? 'selected' : '';
echo "<option value='editor' $editorRegistryUserRoleSelected>Editor</option>";
$authorRegistryUserRoleSelected = ($RegistryUserRole=='author') ? 'selected' : '';
echo "<option value='author' $authorRegistryUserRoleSelected>Author</option>";
$administratorRegistryUserRoleSelected = ($RegistryUserRole=='administrator') ? 'selected' : '';
echo "<option value='administrator' $administratorRegistryUserRoleSelected>Administrator</option>";

if(empty($cgRegistryUserRoleSelected) and
    empty($otherRegistryUserRoleSelected) and
    empty($subscriberRegistryUserRoleSelected) and
    empty($contributorRegistryUserRoleSelected) and
    empty($editorRegistryUserRoleSelected) and
    empty($authorRegistryUserRoleSelected) and
    empty($administratorRegistryUserRoleSelected)
){
    echo "<option value='' selected>No role</option>";
}*/

echo <<<HEREDOC
                    </select>
                </div>
            </div>
        </div>
HEREDOC;


$beforeSinceV14Explanation = '';
$beforeSinceV14Disabled = '';

if(intval($galleryDbVersion)<14){
    $beforeSinceV14Disabled = 'cg_disabled';
    $beforeSinceV14Explanation = '<br><span class="cg_view_option_title_note"><span class="cg_color_red">NOTE: </span> Available only for galleries created or copied in plugin version 14 or higher</span>';
}


echo <<<HEREDOC
    <div class="cg_view_options_row">
            <div class="cg_view_option cg_border_border_bottom_left_radius_8_px cg_view_option_full_width cg_border_top_bottom_none $beforeSinceV14Disabled" style="margin-bottom: -20px;">
                <div class="cg_view_option_title ">
                    <p>Role groups which can edit Contest Gallery "Registration form" fields<br>in "Edit profile" when logged in.
$beforeSinceV14Explanation
</p>
                    
                </div>                    
        </div>
    </div>
HEREDOC;

$rolesNewArray = [];
$rolesNewArray['contest_gallery_user_since_v14'] = $roles['contest_gallery_user_since_v14'];

foreach($roles as $keyOfRole => $roleValues){
    if($keyOfRole=='contest_gallery_user' OR $keyOfRole=='contest_gallery_user_since_v14'){// not selectable if gallery created after v14
        continue;
    }else{
        $rolesNewArray[$keyOfRole]  = $roleValues;
    }
}

if(count($rolesNewArray)  % 4 !== 0){
    for($iLeft = 0; count($rolesNewArray)  % 4 !== 0; $iLeft++){
        $rolesNewArray['cg-empty-'.$iLeft]  = [];
    }
}

$i = 1;
$iCount = count($rolesNewArray);

foreach($rolesNewArray as $keyOfRole => $roleValues){

    if(!empty($roleValues['name'])){
        $roleName = $roleValues['name'];
    }

    $cg_border_right_none = 'cg_border_right_none';
    $cg_border_left_none = 'cg_border_left_none';
    $cg_border_top_bottom_none = 'cg_border_top_bottom_none';

    if($i==1 || (($i-1) % 4 === 0)){
        $cg_border_left_none = '';
        echo "<div class='cg_view_options_row'>";
    }

    if($i==$iCount || ($i  % 4 === 0)){
        $cg_border_right_none = '';
    }
    if($i+4>$iCount){// then must be last row
        $cg_border_top_bottom_none = 'cg_border_top_none';
    }

    if($i==1){
        echo <<<HEREDOC
    <div  class='cg_view_option cg_view_option_25_percent $cg_border_top_bottom_none $cg_border_left_none $cg_border_right_none cg_pointer_events_none $beforeSinceV14Disabled'>
        <div class='cg_view_option_title'>
            <p>$roleName<br><span class="cg_view_option_title_note">Always possible<br>to edit for this group</span></p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="EditProfileGroups[contest_gallery_user_since_v14]" checked><br/>
        </div>
    </div>
HEREDOC;
    }else{

        $checked = (array_key_exists($keyOfRole,$EditProfileGroups) !== false) ? 'checked' : '';

        if(!empty($roleValues)){
            echo <<<HEREDOC
    <div  class='cg_view_option cg_view_option_25_percent $cg_border_top_bottom_none $cg_border_left_none $cg_border_right_none $beforeSinceV14Disabled'>
        <div class='cg_view_option_title'>
            <p>$roleName</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <input type="checkbox" name="EditProfileGroups[$keyOfRole]" $checked ><br/>
        </div>
    </div>
HEREDOC;
        }else{// then must be placeholder
            echo <<<HEREDOC
    <div  class='cg_view_option cg_view_option_25_percent $cg_border_top_bottom_none $cg_border_left_none $cg_border_right_none cg_pointer_events_none $beforeSinceV14Disabled'>
        <div class='cg_view_option_title'>
            <p>&nbsp;</p>
        </div>
        <div class='cg_view_option_checkbox'>
        <br/>
        </div>
    </div>
HEREDOC;
        }

    }

    if($i==$iCount || ($i  % 4 === 0)){
        echo "</div>";
    }

    $i++;

}
echo <<<HEREDOC
        </div>
HEREDOC;

echo <<<HEREDOC
    <div class='cg_view_options_rows_container'>
        <p class='cg_view_options_rows_container_title'>Registry form shortcode <b>visual</b> configuration</p>
        <div class="cg_view_options_row">
            <div class="cg_view_option cg_view_option_100_percent" id="BorderRadiusContainer">
                <div class="cg_view_option_title">
                    <p style="margin-right: -30px;">Round borders form container and field inputs</p>
                </div>
                <div class="cg_view_option_checkbox cg_view_option_checked">
                    <input type="checkbox" name="BorderRadiusRegistry" id="BorderRadiusRegistry" $BorderRadiusRegistry>
                </div>
            </div>
    </div>
     <div class='cg_view_options_row '  >
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none'>
                    <div class='cg_view_option_title'>
                        <p>Background, fields and font color style</p>
                    </div>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Bright style
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="FeControlsStyleWhiteRegistry" class="FeControlsStyleWhiteRegistry cg_view_option_radio_multiple_input_field" $FeControlsStyleWhiteRegistry value="white"/>
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container'>
                            <div class='cg_view_option_radio_multiple_title'>
                                Dark style
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="FeControlsStyleBlackRegistry" class="FeControlsStyleBlackRegistry cg_view_option_radio_multiple_input_field" $FeControlsStyleBlackRegistry value="black">
                            </div>
                        </div>
                </div>
            </div>
</div>
    </div>
HEREDOC;

// Calculate days, hours, seconds
$confirmExpiryDays    = (int) floor( $ConfirmExpiry / 86400);
$rest    = $ConfirmExpiry % 86400;
$confirmExpiryHours   = (int) floor($rest / 3600);
$rest2   = $rest % 3600;
$confirmExpiryMinutes = (int) floor($rest2 / 60);
// Calculate hours, seconds
$pinExpiryHours   = (int) floor($PinExpiry / 3600);
$rest2   = $PinExpiry % 3600;
$pinExpiryMinutes = (int) floor($rest2 / 60);

echo <<<HEREDOC
    <div class='cg_view_options_rows_container'>
        <p class='cg_view_options_rows_container_title'>Registration options</p>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width $cgProFalse" id="wp-TextBeforeRegFormBeforeLoggedIn-wrap-Container">
                <div class="cg_view_option_title">
                    <p>Text before email confirmation form before logged in<br><span class="cg_view_option_title_note">For [cg_users_reg] shortcodes.</span></p>
                </div>
                <div class="cg_view_option_html">
                    <textarea class='cg-wp-editor-template' id='TextBeforeRegFormBeforeLoggedIn'  name='TextBeforeRegFormBeforeLoggedIn'>$TextBeforeRegFormBeforeLoggedIn</textarea>
                </div>
             </div>
        </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width $cgProFalse" id="wp-TextBeforePinFormBeforeLoggedIn-wrap-Container">
                <div class="cg_view_option_title">
                    <p>Text before PIN confirmation form before logged in<br><span class="cg_view_option_title_note">For [cg_users_pin] shortcodes.</span></p>
                </div>
                <div class="cg_view_option_html">
                    <textarea class='cg-wp-editor-template' id='TextBeforePinFormBeforeLoggedIn'  name='TextBeforePinFormBeforeLoggedIn'>$TextBeforePinFormBeforeLoggedIn</textarea>
                </div>
             </div>
        </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_100_percent cg_border_top_none $cgProFalse" id="RegMailOptionalContainer">
                <div class="cg_view_option_title">
                    <p>Log in user instantly after registration<br><span class="cg_view_option_title_note">For [cg_users_reg] shortcode.<br>The account is created immediately after registration and the user is logged in automatically.<br>
E-mail confirmation is not required to access the account, but a confirmation email will still be sent.</span></p>
                </div>
                <div class="cg_view_option_checkbox">
                    <input id='RegMailOptional' type='checkbox' name='RegMailOptional' $RegMailOptional >
                </div>
            </div>
       </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width cg_border_top_none $cgProFalse" id="wp-ForwardAfterRegText-wrap-Container">
                <div class="cg_view_option_title">
                    <p>Confirmation text after registration<br><span class="cg_view_option_title_note">For [cg_users_reg] shortcode.</span></p>
                </div>
                <div class="cg_view_option_html">
                    <textarea class='cg-wp-editor-template' id='ForwardAfterRegText'  name='ForwardAfterRegText'>$ForwardAfterRegText</textarea>
                </div>
             </div>
        </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width cg_border_top_none $cgProFalse" id="wp-TextAfterEmailConfirmation-wrap-Container">
                <div class="cg_view_option_title">
                    <p>Confirmation text after email confirmation<br><span class="cg_view_option_title_note">For [cg_users_reg] shortcode.</span></p>
                </div>
                <div class="cg_view_option_html">
                    <textarea class='cg-wp-editor-template' id='TextAfterEmailConfirmation'  name='TextAfterEmailConfirmation'>$TextAfterEmailConfirmation</textarea>
                </div>
             </div>
        </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_100_percent cg_border_top_none $cgProFalse" id="LoginAfterConfirmContainer">
                <div class="cg_view_option_title">
                    <p>Login user immediately after email confirmation<br><span class="cg_view_option_title_note">For [cg_users_reg] shortcode.<br>The user will be logged in automatically after clicking the confirmation link in the e-mail.</span></p>
                </div>
                <div class="cg_view_option_checkbox">
                    <input id='LoginAfterConfirm' type='checkbox' name='LoginAfterConfirm' $LoginAfterConfirm >
                </div>
            </div>
       </div>
       <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="ConfirmDaysHoursMinutesContainer" >
            <div class='cg_view_option_title'>
                <p>Confirmation link lifetime<br><span class="cg_view_option_title_note">For [cg_users_reg] shortcode.<br>(Days : Hours : Minutes)</span></p>
            </div>
            <div class='cg_view_option_input'>
                <input type="number" id="confirmExpiryDays" name="confirm_expiry_days" min="0" max="100" step="1" value="$confirmExpiryDays" style="width:60px;margin-right: 15px;" >
                <input type="number" id="confirmExpiryHours" name="confirm_expiry_hours" 
min="0" max="24" step="1" value="$confirmExpiryHours" style="width:60px;margin-right: 15px;" >
                <input type="number" id="confirmExpiryMinutes" name="confirm_expiry_minutes" 
min="0" max="59" step="1" value="$confirmExpiryMinutes" style="width:60px;" >
            </div>
        </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width cg_border_top_none $cgProFalse" id="wp-TextAfterPinConfirmation-wrap-Container">
                <div class="cg_view_option_title">
                    <p>Confirmation text after PIN verification<br><span class="cg_view_option_title_note">For [cg_users_pin] shortcode.</span></p>
                </div>
                <div class="cg_view_option_html">
                    <textarea class='cg-wp-editor-template' id='TextAfterPinConfirmation'  name='TextAfterPinConfirmation'>$TextAfterPinConfirmation</textarea>
                </div>
             </div>
        </div>
       <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="PinHoursMinutesContainer" >
            <div class='cg_view_option_title'>
                <p>PIN lifetime<br><span class="cg_view_option_title_note">For [cg_users_pin] shortcode.<br>(Hours : Minutes)</span></p>
            </div>
            <div class='cg_view_option_input'>
                <input type="number" id="pinExpiryHours" name="pin_expiry_hours" 
min="0" max="24" step="1" value="$pinExpiryHours" style="width:60px;margin-right: 15px;" >
                <input type="number" id="pinExpiryMinutes" name="pin_expiry_minutes" 
min="0" max="59" step="1" value="$pinExpiryMinutes" style="width:60px;" >
            </div>
        </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_100_percent cg_border_top_none" id="HideRegFormAfterLoginContainer">
                    <div class="cg_view_option_title">
                        <p>Hide registration form if user is logged in<br><span class="cg_view_option_title_note">For [cg_users_reg] and [cg_users_pin] shortcodes.</span></p>
                    </div>
                    <div class="cg_view_option_checkbox">
                        <input id='HideRegFormAfterLogin' type='checkbox' name='HideRegFormAfterLogin' $HideRegFormAfterLogin >
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_100_percent cg_border_top_none" id="HideRegFormAfterLoginShowTextInsteadContainer">
                    <div class="cg_view_option_title">
                        <p>Show text instead<br><span class="cg_view_option_title_note">For [cg_users_reg] and [cg_users_pin] shortcodes.</span></p>
                    </div>
                    <div class="cg_view_option_checkbox">
                        <input id='HideRegFormAfterLoginShowTextInstead' type='checkbox' name='HideRegFormAfterLoginShowTextInstead' $HideRegFormAfterLoginShowTextInstead >
                    </div>
                </div>
            </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width cg_border_top_none" id="wp-HideRegFormAfterLoginTextToShow-wrap-Container">
                    <div class="cg_view_option_title">
                        <p  style="padding-right: 20px;">Text to show<br><span class="cg_view_option_title_note">For [cg_users_reg] and [cg_users_pin] shortcodes.</span></p>
                    </div>
                    <div class="cg_view_option_html">
                        <textarea class='cg-wp-editor-template' id='HideRegFormAfterLoginTextToShow'  name='HideRegFormAfterLoginTextToShow'>$HideRegFormAfterLoginTextToShow</textarea>
                    </div>
                </div>
            </div>
    </div>
HEREDOC;

echo <<<HEREDOC
    <div class='cg_view_options_rows_container'>
        <p class='cg_view_options_rows_container_title'>Confirmation e-mail options
            <br><span class='cg_view_options_rows_container_title_note'>For [cg_users_reg] and [cg_users_pin] shortcodes.<br>$cgDomainErrorText</span>
        </p>
HEREDOC;


if(strpos($mailExceptions,'User registration e-mail') !== false){
    echo "<div style=\"width:330px;margin: -8px auto 15px;\"><a href=\"?page=".cg_get_version()."/index.php&amp;corrections_and_improvements=true&amp;option_id=$galeryNR\" class='cg_load_backend_link'><input class=\"cg_backend_button cg_backend_button_back cg_backend_button_warning\" type=\"button\" value=\"There were mail exceptions for this mailing type\" style=\"width:330px;\"></a>
</div>";
}else{
    echo "<div style=\"width:280px;margin: -8px auto 15px;\"><a href=\"?page=".cg_get_version()."/index.php&amp;corrections_and_improvements=true&amp;option_id=$galeryNR\" class='cg_load_backend_link'><input class=\"cg_backend_button cg_backend_button_back cg_backend_button_success\" type=\"button\" value=\"No mail exceptions for this mailing type\" style=\"width:280px;\"></a>
</div>";
}

echo <<<HEREDOC
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px" id="RegMailAddressorContainer">
                <div class="cg_view_option_title">
                    <p>Header (like your company name or something like that, not an e-mail)</p>
                </div>
                <div class="cg_view_option_input">
                    <input type="text" name="RegMailAddressor" value="$RegMailAddressor"  maxlength="200" >
                </div>
            </div>
       </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width cg_border_top_none" id="RegMailReplyContainer">
                <div class="cg_view_option_title">
                    <p>Reply e-mail (address From)<br><span class="cg_view_option_title_note">Should not be empty</span></p>
                </div>
                <div class="cg_view_option_input">
                    <input type="text" name="RegMailReply" value="$RegMailReply"  maxlength="200" >
                </div>
            </div>
       </div>
       <div class='cg_view_options_row'>
                <div class='cg_view_option   cg_view_option_full_width cg_border_top_none $cgProFalse'  >
                    <div class='cg_view_option_title'>
                        <p>CC e-mail<br><span class="cg_view_option_title_note">Should not be the same as "Reply e-mail"<br>Sending to multiple recipients example (mail1@example.com; mail2@example.com; mail3@example.com)</span>
                        </p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="RegMailCC" id="RegMailCC" value="$RegMailCC"  maxlength="200" >
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
                <div class='cg_view_option   cg_view_option_full_width cg_border_top_none $cgProFalse'  >
                    <div class='cg_view_option_title'>
                        <p>BCC e-mail<br><span class="cg_view_option_title_note">Should not be the same as "Reply e-mail"<br>Sending to multiple recipients example (mail1@example.com; mail2@example.com; mail3@example.com)</span>
                        </p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="RegMailBCC" id="RegMailBCC" value="$RegMailBCC"  maxlength="200" >
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width cg_border_top_none" id="RegMailSubjectContainer">
                <div class="cg_view_option_title">
                    <p>Subject<br><span class="cg_view_option_title_note">For [cg_users_reg] shortcode.</span></p>
                </div>
                <div class="cg_view_option_input">
                    <input type="text" name="RegMailSubject" class="cg-long-input" value="$RegMailSubject"  maxlength="200">
                </div>
            </div>
       </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width cg_border_top_none $cgProFalse" id="wp-TextEmailConfirmation-wrap-Container">
                <div class="cg_view_option_title cg_copyable">
                    <p>Email content shortcode<br><span class="cg_view_option_title_note">For [cg_users_reg] shortcode.<br>Put this variable in the email content editor: <span style="font-weight:bold;" class="cg_tooltip cg_copy_param">\$regurl$</span><br>(Link to confirmation page will appear in the e-mail<br>It will be the same page where your registration shortcode is inserted)
<br><a href="https://www.contest-gallery.com/documentation/#cgDisplayConfirmationURL" target="_blank" class="cg-documentation-link">Documentation: How to make the link clickable in e-mail</a></span></p>
                </div>
                <div class="cg_view_option_html">
                    <textarea class='cg-wp-editor-template' id='TextEmailConfirmation'  name='TextEmailConfirmation'>$TextEmailConfirmation</textarea
                </div>
            </div>
       </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_border_border_bottom_left_radius_unset cg_border_border_bottom_right_radius_unset cg_view_option_full_width cg_border_top_none" id="RegPinSubjectContainer">
                <div class="cg_view_option_title">
                    <p>Subject<br><span class="cg_view_option_title_note">For [cg_users_pin] shortcode.<br>Put this variable in the subject field: <span style="font-weight:bold;" class="cg_tooltip cg_copy_param">\$pin$</span><br>(PIN for confirmation will appear in the subject)</span></p>
                </div>
                <div class="cg_view_option_input">
                    <input type="text" name="RegPinSubject" class="cg-long-input" value="$RegPinSubject"  maxlength="200">
                </div>
            </div>
       </div>
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_view_option_full_width cg_border_top_none $cgProFalse" id="wp-TextPinConfirmation-wrap-Container">
                <div class="cg_view_option_title cg_copyable">
                    <p>Email content<br><span class="cg_view_option_title_note">For [cg_users_pin] shortcode.<br>Put this variable in the email content editor: <span style="font-weight:bold;" class="cg_tooltip cg_copy_param">\$pin$</span><br>(PIN for confirmation will appear in the e-mail)</span></p>
                </div>
                <div class="cg_view_option_html">
                    <textarea class='cg-wp-editor-template' id='TextPinConfirmation'  name='TextPinConfirmation'>$TextPinConfirmation</textarea
                </div>
            </div>
       </div>
    </div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;


