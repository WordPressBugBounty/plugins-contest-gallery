<?php

if(!defined('ABSPATH')){exit;}

$_POST = cg1l_sanitize_post($_POST);

$selectSQLgoogleOptions = cg_get_google_options();
$ClientId = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLgoogleOptions->ClientId);
$payload = cg_google_sign_in_verification($ClientId,sanitize_text_field($_POST['googleIdTokenForCurrentValidation']));

$GoogleId = (!empty($payload['sub'])) ? $payload['sub'] : '';
$GoogleEmail = (!empty($payload['email'])) ? $payload['email'] : '';

if(!empty($GoogleId) && !empty($GoogleEmail)){

    global $wpdb;

    $tablename_pro_options = $wpdb->prefix."contest_gal1ery_pro_options";
    $tablename_contest_gal1ery_google_users = $wpdb->prefix."contest_gal1ery_google_users";
    $tablenameWpUsers = $wpdb->base_prefix . "users";
    $googleUser = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_contest_gal1ery_google_users WHERE GoogleId = %s", $GoogleId));

	$googleOptions = cg_get_google_options();
	$GoogleEmailToSet = $GoogleEmail;

    if(!empty($googleOptions->GooglemailConvert) && $googleOptions->GooglemailConvert==1){
	    $GoogleEmailToSet = str_replace('@googlemail.com','@gmail.com',$GoogleEmail);
    }

	$payload['email'] = $GoogleEmailToSet;

	$GoogleEmailToCheck1 = str_replace('@googlemail.com','@gmail.com',$GoogleEmail);
	$GoogleEmailToCheck2 = str_replace('@gmail.com','@googlemail.com',$GoogleEmail);

    if(empty($googleUser)){

        $WpUserId1 = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $tablenameWpUsers WHERE user_email = %s", $GoogleEmailToCheck1));
        $WpUserId2 = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $tablenameWpUsers WHERE user_email = %s", $GoogleEmailToCheck2));

        if(!empty($WpUserId1) || !empty($WpUserId2)){
	        $WpUserId = (!empty($WpUserId1)) ? $WpUserId1 : $WpUserId2;

            if(!cg_google_sign_in_is_existing_wp_email_auto_link_allowed($payload)){
                cg_google_sign_in_render_public_error('Google sign in could not be linked automatically. Please log in with your existing WordPress account first.');
                return;
            }

            cg_google_sign_in_link_wp_user($payload,$WpUserId);

            if(cg_check_headers_sent()){
                return;
            }
            update_user_meta( $WpUserId, 'cgGoogleSignInJustSignedIn', 1);
            wp_set_auth_cookie( $WpUserId,true  );
        }else{
            cg_create_wp_user_from_google_user($payload,true);
        }

    }else{
        $WpUserId = $googleUser->WpUserId;

        $WpUserId = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $tablenameWpUsers WHERE ID = %d", $WpUserId));

        if(!empty($WpUserId)){
            if(cg_check_headers_sent()){
                return;
            }
            update_user_meta( $WpUserId, 'cgGoogleSignInJustSignedIn', 1);
            wp_set_auth_cookie( $WpUserId,true );
        }else{
	        cg_create_wp_user_from_google_user($payload,true);
        }

    }

    $ForwardAfterLoginUrl = $wpdb->get_var("SELECT ForwardAfterLoginUrl FROM $tablename_pro_options WHERE GeneralID = '1'");
    if(empty($ForwardAfterLoginUrl)){
        $ForwardAfterLoginUrl = '';
    }

    $ForwardAfterLoginUrlCheck = intval($wpdb->get_var("SELECT ForwardAfterLoginUrlCheck FROM $tablename_pro_options WHERE GeneralID = '1'"));
    $ForwardAfterLoginUrl = html_entity_decode(stripslashes(nl2br($ForwardAfterLoginUrl)));

    include (__DIR__.'/../../../check-language.php');

    ?>
    <script data-cg-processing="true">

        var language_GoogleSignSuccessfull = <?php echo json_encode($language_GoogleSignSuccessfull); ?>;
        cgJsClass.gallery.vars.isSuccessFullySignedIn = true;
        cgJsClass.gallery.vars.ForwardAfterLoginUrlCheck = <?php echo json_encode($ForwardAfterLoginUrlCheck); ?>;
        cgJsClass.gallery.vars.ForwardAfterLoginUrl = null;
        if(cgJsClass.gallery.vars.ForwardAfterLoginUrlCheck){
            cgJsClass.gallery.vars.ForwardAfterLoginUrl = <?php echo json_encode($ForwardAfterLoginUrl); ?>;
        }

        cgJsClass.gallery.function.message.show('googlesignin',language_GoogleSignSuccessfull,undefined,undefined,undefined,undefined,true,undefined,undefined,true);

    </script>
    <?php

}else{

?>
    <script data-cg-processing="true">

        cgJsClass.gallery.function.message.show('googlesignin','No google id or google email provided',undefined,undefined,undefined,undefined,undefined,undefined,undefined,true);
    </script>
<?php

}

?>
