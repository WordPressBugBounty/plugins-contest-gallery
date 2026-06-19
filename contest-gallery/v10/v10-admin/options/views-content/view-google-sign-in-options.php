<?php

echo <<<HEREDOC
<div class='cg_view_container'>
        <div class='cg_view_options_rows_container' id="cgGoogleOptionsRowsContainer">

<p class="cg_view_options_rows_container_title ">
        <strong class="cg_note_label">* NOTE:</strong> <span class="cg_note_text">Login via Google options are general and valid for all galleries.</span><br>
</p>
HEREDOC;

echo <<<HEREDOC
<div class="cg_google_sign_in_info_card">
        <div class="cg_google_sign_in_info_item">
                <div class="cg_google_sign_in_info_label">NOTE</div>
                <div class="cg_google_sign_in_info_content">
                        <div class="cg_google_sign_in_info_title">Google sign in button visibility</div>
                        <p>The Google sign in button is hidden while the current browser session is already logged in. Use another browser or a private window to test the button.</p>
                </div>
        </div>
        <div class="cg_google_sign_in_info_item">
                <div class="cg_google_sign_in_info_label">DOCS</div>
                <div class="cg_google_sign_in_info_content">
                        <div class="cg_google_sign_in_info_title">Google sign in documentation</div>
                        <p>Check the documentation to connect your domain and get the required client id.</p>
                        <a href="https://www.contest-gallery.com/google-sign-in-documentation/" target="_blank" rel="noopener noreferrer">Open Google sign in documentation</a>
                </div>
        </div>
        <div class="cg_google_sign_in_info_item">
                <div class="cg_google_sign_in_info_label">USERS</div>
                <div class="cg_google_sign_in_info_content">
                        <div class="cg_google_sign_in_info_title">WordPress user handling</div>
                        <p>Users who log in with Google are handled like WordPress users and are logged in after using the Google sign in button.</p>
                        <p>If the Google signed in user is not a WordPress user yet, a WordPress user with the <a href="" class="cg_go_to_link" data-cg-go-to-link="RegOptionsUserGroupRolesContainer">configured user group role in "Registration options"</a> will be created.</p>
                        <p>The verified Google e-mail address is used for the WordPress user. <strong>@googlemail.com</strong> can be normalized to <strong>@gmail.com</strong> with the option below.</p>
                        <p>If a WordPress account already exists for the same verified Google e-mail address, no new WordPress account will be created. Existing custom-domain WordPress accounts are linked automatically only when Google confirms the same hosted domain.</p>
                        <p class="cg_google_sign_in_info_warning">Administrators with a matching Google mail address or verified Workspace domain can be logged in as that administrator account.</p>
                </div>
        </div>
</div>
HEREDOC;

$CheckMethodGoogleNameClass = 'CheckMethod';
$CheckMethodUploadGoogleClass = 'CheckMethodUpload';
$CheckMethodUploadGoogleName = 'RegUserUploadOnly';

$cgGoogleSignInTestingFilePath = '';
$cgIsGoogleSignLibraryMissingNote = '';
$cgIsGoogleSignLibraryMissingClass = '';
$cgIsGoogleSignLibraryMissingStyle = '';

if(empty($cgGoogleSignInLibStatus)){
    $cgGoogleSignInLibStatus = cg_google_sign_in_lib_checks();
}

$cgGoogleSignInOpenSslStatusText = (!empty($cgGoogleSignInLibStatus['openssl_status_text'])) ? $cgGoogleSignInLibStatus['openssl_status_text'] : cg_google_sign_in_get_openssl_status_text();
$cgGoogleSignInOpenSslStatusTextEncoded = rawurlencode($cgGoogleSignInOpenSslStatusText);
$cgGoogleSignInLibVersionClientEncoded = rawurlencode($cgGoogleSignInLibVersionClient);

if(isset($cgGoogleSignInLibStatus['error']) && $cgGoogleSignInLibStatus['code'] == 'openssl-missing'){
    $cgIsGoogleSignLibraryMissingStyle = 'opacity: 0.6 !important;';

    $cgIsGoogleSignLibraryMissingNote = <<<HEREDOC
<br><span class="cg_view_option_title_note"><span class="cg_color_red">NOTE:</span> <span class="cg_note_text">Enable PHP OpenSSL support to test and use Google sign in</span></span>
HEREDOC;
    $cgIsGoogleSignLibraryMissingClass = 'cg_disabled';

    echo <<<HEREDOC
<p class="cg_view_options_rows_container_title" id="GoogleSignInOpenSslRequiredContainer">
        <strong><span class="cg_color_red cg_note_label">NOTE:</span> <span class="cg_note_text">PHP OpenSSL support is required for Google sign in token verification.</span></strong><br>
        <span>$cgGoogleSignInOpenSslStatusText</span>
</p>
HEREDOC;
}

include(__DIR__.'/../google-sign-in-options-check.php');
$cgGoogleSignInTestingFolder = $uploadFolder['baseurl'].'/contest-gallery/google-sign-in';
$cgGoogleSignInTestingFilePath = $cgGoogleSignInTestingFolder.'/google-sign-in-testing.html?time='.time();

$siteUrl = get_site_url();
$siteUrlExploded = explode('//',$siteUrl);

if(!empty($siteUrlExploded[1]) && strpos($siteUrlExploded[1],'/') !== false){
    $siteUrlExplodedPartTwo = explode('/',$siteUrlExploded[1]);
    $siteUrl = $siteUrlExploded[0].'//'.$siteUrlExplodedPartTwo[0];
}

echo <<<HEREDOC
<div id='cgGoogleSignInTestClientIdForDomainMessage' style="text-align: center;" class="cg_hide">
        <div style="font-size: 20px;margin: 15px 0 10px;"><b>Test google client id in combination with your domain</b></div>
        <div style="border:thin solid black;padding: 5px;"><b>Your client id</b><br><span id="cgGoogleClientIdDomainMessage"></span></div>
        <div style="border:thin solid black;border-top: none;margin-bottom: 20px;padding: 5px;"><b>Your domain</b><br><span id="cgGoogleSignInDomainMessage"></span></div>
         <div style="font-size: 20px;color: red;margin-bottom: 10px;"><b>Possible error messages</b><br></div>
         <div style="border:thin solid black;padding: 5px;"><b>invalid_client</b><br>the client id does not exists</div>
         <div style="border:thin solid black;border-top: none;margin-bottom: 20px;padding: 5px;"><b>redirect_uri_mismatch</b><br>the domain is not configured for the client id</div>
         <div style="font-size: 20px;color: green;margin-bottom: 10px;">Success screen</div>
         <div style="border:thin solid black;margin-bottom: 20px;padding: 5px;"><b>The login will be visible and possible. Everything ok then. You can close the pop up window then and go using Google sign in option of Contest Gallery.</b></div>
          <div style="font-size: 20px;color: green;margin-bottom: 10px;cursor: pointer;text-decoration: underline;">
            <a id="cgGoogleSignInTestingFileButton" target="_blank">Test Google sign in</a>
          </div>
</div>

<div class='cg_view_options_row'>
    <div class="cg_view_option cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px cg_view_option_full_width" id="wp-TextBeforeGoogleSignInButton-wrap-Container">
        <div class="cg_view_option_title">
            <p>Text before Google sign in button before logged in<br><span class="cg_view_option_title_note">After user is logged in text is not visible anymore</span></p>
        </div>
        <div class="cg_view_option_html">
            <textarea class='cg-wp-editor-template' id='TextBeforeGoogleSignInButton' name='TextBeforeGoogleSignInButton'>$TextBeforeGoogleSignInButton</textarea>
        </div>
    </div>
</div>
<div class='cg_view_options_row '>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none'>
        <div class='cg_view_option_title'>
            <p>Google client id$cgIsGoogleSignLibraryMissingNote<br/><span class="cg_view_option_title_note"><a id="cgGoogleSignInTestClientIdForDomain" href="$cgGoogleSignInTestingFilePath#clientId=$ClientId&libVersion=$cgGoogleSignInLibVersionClientEncoded&opensslStatus=$cgGoogleSignInOpenSslStatusTextEncoded" target="_blank" class="$cgIsGoogleSignLibraryMissingClass" style="$cgIsGoogleSignLibraryMissingStyle">Test google client id in combination with your domain</a><span id="cgGoogleSignInDomain" class="cg_hide" >$siteUrl</span></span></p>
        </div>
        <div class='cg_view_option_input'>
            <input type="text" placeholder="" class="cg-long-input" id="GoogleClientId" name="GoogleClientId" maxlength="100" value="$ClientId">
        </div>
    </div>
</div>
<div class='cg_view_options_row cg_hide '>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none'>
        <div class='cg_view_option_title'>
            <p>Google button text on page load<br/><span class="cg_view_option_title_note">Take care of <a href="https://developers.google.com/identity/branding-guidelines" target="_blank" class="">Google branding guidlines</a><br><strong>It should contains the word "Google"<br>Keep it short, line breaks are not allowed</strong></span></p>
        </div>
        <div class='cg_view_option_input'>
            <input type="text" class="cg-long-input" id="GoogleButtonTextOnLoad" name="GoogleButtonTextOnLoad" maxlength="100" value="$ButtonTextOnLoad">
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class="cg_view_option cg_view_option_100_percent cg_border_top_none">
        <div class="cg_view_option_title">
            <p>Create @googlemail Google Sign In users as @gmail WordPress users in the database<br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE:</span> <span class="cg_note_text"> </span><span class="cg_note_text">older Google users from certain<br> countries might have @googlemail as official address,<br>but using @gmail everywhere for registration and login. This users can be created as @gmail users,<br>so they can login with @gmail as WordPress user without using Google Sign In.<br>Google handles @googlemail and @gmail as same e-mail address.</span></span></p>
        </div>
        <div class="cg_view_option_checkbox">
            <input type="checkbox" name="GooglemailConvert" id="GooglemailConvert" value="1" $GooglemailConvert >
        </div>
    </div>
</div>
HEREDOC;

$btn_google_signin_light_normal_web = plugins_url('/../../../../v10/v10-css/btn_google_signin_light_normal_web.png', __FILE__);
$btn_google_signin_dark_normal_web = plugins_url('/../../../../v10/v10-css/btn_google_signin_dark_normal_web.png', __FILE__);

echo <<<HEREDOC
<div class='cg_view_options_row '>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none'>
        <div class='cg_view_option_title'>
            <p>Google button theme style<br/><span class="cg_view_option_title_note">Relating <a href="https://developers.google.com/identity/branding-guidelines" target="_blank" class="">Google branding guidlines</a> it can be only this two styles</span></p>
        </div>
        <div class='cg_view_option_radio_multiple'>
            <div class='cg_view_option_radio_multiple_container GoogleButtonStyleBrightContainer'>
                <div class='cg_view_option_radio_multiple_title'>Bright style</div>
                <div class='cg_view_option_radio_multiple_input'>
                     <input type="radio" name="GoogleButtonStyle" class="GoogleButtonStyle cg_view_option_radio_multiple_input_field" $ButtonStyleBrightChecked value="bright" />
                </div>
                <div style="flex-basis: 100%;margin-top: 5px;">
                     <img src="$btn_google_signin_light_normal_web" style="margin: 0 auto;"/>
                </div>
            </div>
            <div class='cg_view_option_radio_multiple_container GoogleButtonStyleDarkContainer'>
                <div class='cg_view_option_radio_multiple_title'>Dark style</div>
                <div class='cg_view_option_radio_multiple_input'>
                    <input type="radio" name="GoogleButtonStyle" class="GoogleButtonStyle cg_view_option_radio_multiple_input_field" $ButtonStyleDarkChecked value="dark" >
                </div>
                <div style="flex-basis: 100%;margin-top: 5px;">
                     <img src="$btn_google_signin_dark_normal_web" style="margin: 0 auto;"/>
                </div>
            </div>
        </div>
    </div>
</div>

<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none'>
        <div class='cg_view_option_title'>
            <p>Messages background color depends on configurations in<br>
                <a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="LoginFormShortcodeVisualConfiguration">Login options visual</a>
            </p>
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none'>
        <div class='cg_view_option_title'>
            <p>Forwarding after sign or text after sign in can be configured in<br>
                <a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="LoginFormShortcodeForwardingConfirmationConfiguration">Login options</a>
            </p>
        </div>
    </div>
</div>
</div>
</div>
HEREDOC;
