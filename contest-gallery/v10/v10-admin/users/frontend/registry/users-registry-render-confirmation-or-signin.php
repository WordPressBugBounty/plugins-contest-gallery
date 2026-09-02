<?php

$isAfterLoginRequest = !empty($isAfterLogin);
$targetWpUserId = !empty($newWpId) ? absint($newWpId) : 0;
$currentWpUserId = is_user_logged_in() ? get_current_user_id() : 0;
$isCurrentTargetUser = ($targetWpUserId && $currentWpUserId === $targetWpUserId);
$isCurrentDifferentUser = ($targetWpUserId && $currentWpUserId && $currentWpUserId !== $targetWpUserId);
$isActivationBrowserMatch = !empty($isActivationBrowserMatch);
$shouldReloadAfterAutomaticLogin = (
    $LoginAfterConfirm &&
    !$isAfterLoginRequest &&
    $targetWpUserId &&
    $isActivationBrowserMatch &&
    !$isCurrentDifferentUser
);

if($shouldReloadAfterAutomaticLogin){
    if(!$isCurrentTargetUser){
        wp_set_auth_cookie( $targetWpUserId,true );
    }
    ?>
    <script>
        location.replace(location.origin + location.pathname + '?cg1l_reload_after_login=' + Date.now());
    </script>
    <?php
}else{
    echo "<div id='cg_activation'  class='mainCGdivUploadForm mainCGdivUploadFormStatic $FeControlsStyleRegistry $BorderRadiusRegistry'>";

    if(!empty($pro_options)){
        echo "<p>";
        echo html_entity_decode(stripslashes(nl2br($pro_options->TextAfterEmailConfirmation)));
        echo "</p>";

    }else{ // Fallback text if gallery was deleted

        echo "<p>Thank you for your registration. <br>You are now able to log in.</p>";

    }

    echo "</div>";

    if($LoginAfterConfirm && !$isAfterLoginRequest && $isCurrentDifferentUser){
        echo "<p class='cg_registry_security_notice'>";
        echo esc_html__('Your account was confirmed. The account currently signed in to this browser was not changed.',cg_get_version());
        echo "</p>";
    }

    if($LoginAfterConfirm && !$isAfterLoginRequest && !$currentWpUserId && !$isActivationBrowserMatch){
        echo "<p class='cg_registry_security_notice'>";
        echo esc_html__('Your account was confirmed. For security reasons, automatic login is available only in the browser used for registration. Please log in.',cg_get_version());
        echo "</p>";

        cg1l_include_scripts_login_form();
        $cgRegistryAtts = isset($atts) ? $atts : array();
        $atts = array();
        include (__DIR__.'/../login/users-login.php');
        $atts = $cgRegistryAtts;
    }

    ?>

    <script defer>

        <?php if(!$isAfterLoginRequest){ ?>
        window.history.replaceState({}, document.title, location.origin + location.pathname);
        <?php } ?>

        setTimeout(function (){
            jQuery("html, body").animate({ scrollTop: jQuery('#cg_activation').offset().top-60}, 0);
        },100);

    </script>

    <?php

}
