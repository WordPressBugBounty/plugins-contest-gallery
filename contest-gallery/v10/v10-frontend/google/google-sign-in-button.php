<?php

$cgGoogleSignInLibStatus = cg_google_sign_in_lib_checks();

include(__DIR__.'/../gallery/cg-messages.php');

$cgGoogleButtonHeight = (int)$cgGoogleButtonSizeHeight;

if(isset($cgGoogleSignInLibStatus['error']) && $cgGoogleSignInLibStatus['code'] == 'openssl-missing'){
    $cgGoogleSignInOpenSslStatusText = (!empty($cgGoogleSignInLibStatus['openssl_status_text'])) ? $cgGoogleSignInLibStatus['openssl_status_text'] : cg_google_sign_in_get_openssl_status_text();

    echo "<div style='min-height:".$cgGoogleButtonHeight."px;height:auto;' id='cgGoogleSignInContainerParent' data-cg-gid='googlesignin' class='cgGoogleSignInContainerParent ".$BorderRadiusClass." ".$cgFeControlsStyle." cgGoogleSignInPhpVersionChanged'>";
    echo "<input type='hidden' id='cgGoogleSignLibError' class='cgGoogleSignLibError' value='1'/>";
    echo "<div id='cgGoogleSignInContainer' class='cgGoogleSignInContainer'>";
    echo "<div id='cgGoogleSignIn' class='cgGoogleSignIn'>";
    echo "<div class='cgGoogleSignInMessageBox'>";
    echo "<div>Google sign in verifier unavailable.</div>";
    echo "<div>".esc_html($cgGoogleSignInOpenSslStatusText)."</div>";
    echo "<div>Please enable PHP OpenSSL support.</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}else{

    if(empty($ClientId)){
        echo "<div style='min-height:".$cgGoogleButtonHeight."px;height:auto;' id='cgGoogleSignInContainerParent' data-cg-gid='googlesignin' class='cgGoogleSignInContainerParent ".$BorderRadiusClass." ".$cgFeControlsStyle." cgGoogleSignInPhpVersionChanged'>";
        echo "<input type='hidden' id='cgGoogleSignLibError' class='cgGoogleSignLibError' value='1'/>";
        echo "<div id='cgGoogleSignInContainer' class='cgGoogleSignInContainer'>";
        echo "<div id='cgGoogleSignIn' class='cgGoogleSignIn'>";
        echo "<div class='cgGoogleSignInMessageBox'>";
        echo "<div>Missing Google sign in client id.</div>";
        echo "<div>Please add required Google client id in \"Edit options\" >>> \"Login Google\" >>> \"Google client id\".</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }else{
        $theme = ($ButtonStyle === 'dark') ? 'filled_blue' : 'outline';

        echo "<input type='hidden' id='cgGoogleSignSuccessfull' value='".esc_attr($language_GoogleSignSuccessfull)."'/>";
        echo "<input type='hidden' id='cgGoogleSignInClientId' value='".esc_attr($ClientId)."'/>";
        echo "<input type='hidden' id='cgGoogleButtonTextOnLoad' value='".esc_attr($ButtonTextOnLoad)."'/>";
        echo "<input type='hidden' id='cgGoogleButtonStyle' value='".esc_attr($ButtonStyle)."'/>";
        echo "<div id='cgGoogleSignInContainerParent' data-cg-gid='googlesignin' class='cgGoogleSignInContainerParent cg_class_ext_1 ".$BorderRadiusClass." ".$cgFeControlsStyle."' style='min-height:".$cgGoogleButtonHeight."px;height:auto;'>";
        echo "<div id='cgGoogleSignInContainer' class='cgGoogleSignInContainer'>";
        echo "<script src='https://accounts.google.com/gsi/client' async defer></script>";
        echo "<div id='g_id_onload' data-client_id='".esc_attr($ClientId)."' data-callback='cgGoogleOnSignInSuccessLogin'></div>";
        echo "<div class='g_id_signin' data-type='standard' data-shape='pill' data-theme='".esc_attr($theme)."' data-size='large' data-text='continue_with' data-logo_alignment='left' data-width='280'></div>";
        echo "</div>";
        echo "</div>";
    }
}

?>
