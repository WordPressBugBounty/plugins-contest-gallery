<?php

if(!function_exists('contest_gal1ery_google_sign_in')){

    function contest_gal1ery_google_sign_in($atts){

        contest_gal1ery_db_check();

	    $GalleryID = 0;// just for sure
        $entryId = 0;
        if(!empty($atts['entryId'])){
            $entryId = $atts['entryId'];
        }

        $shortcode_name = 'cg_google_sign_in';

        if(is_admin()){// no execution in admin area
            return '';
        }

        wp_enqueue_style( 'cg_v10_css_cg_gallery', plugins_url('/../v10/v10-css-min/cg_gallery.min.css', __FILE__), false, cg_get_version_for_scripts() );
        wp_enqueue_style( 'cg_v10_css_loaders_cg_gallery', plugins_url('/../v10/v10-css/frontend/style_loaders.css', __FILE__), false, cg_get_version_for_scripts() );
        wp_enqueue_script( 'cg_v10_js_cg_gallery', plugins_url( '/../v10/v10-js-min/cg_gallery.min.js', __FILE__ ), array('jquery'), cg_get_version_for_scripts());
        wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_google_sign_in_add_user_wordpress_ajax_script_function_name', array(
            'cg_google_sign_in_add_user_ajax_url' => admin_url( 'admin-ajax.php' )
        ));
        wp_localize_script('cg_v10_js_cg_gallery', 'CG1LAction', [
            'nonce'   => wp_create_nonce('cg1l_action'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);

        ob_start();

        $selectSQLgoogleOptions = cg_get_google_options();
        $ClientId = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLgoogleOptions->ClientId);
        $ButtonTextOnLoad = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLgoogleOptions->ButtonTextOnLoad);
        $ButtonStyle = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLgoogleOptions->ButtonStyle);
        $TextBeforeGoogleSignInButton = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLgoogleOptions->TextBeforeGoogleSignInButton);

        global $wpdb;
        $tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
        $tablename_options = $wpdb->prefix . "contest_gal1ery_options";
        $tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";// required for users-login-text-after-login.php
        $tablename_registry_and_login_options = $wpdb->prefix . "contest_gal1ery_registry_and_login_options";

            $options_visual = $wpdb->get_row( "SELECT BorderRadiusLogin, FeControlsStyleLogin FROM $tablename_options_visual WHERE GeneralID = '1'" );
            $PermanentTextWhenLoggedIn = contest_gal1ery_convert_for_html_output_without_nl2br($wpdb->get_var("SELECT PermanentTextWhenLoggedIn FROM $tablename_registry_and_login_options WHERE GeneralID = '1'"));

        $BorderRadiusClass = ($options_visual->BorderRadiusLogin==1) ? 'cg_border_radius_controls_and_containers ' : '';
        $cgFeControlsStyle = ($options_visual->FeControlsStyleLogin=='white' || empty($options_visual->FeControlsStyleLogin)) ?  'cg_fe_controls_style_white' : 'cg_fe_controls_style_black';

        // has to be set so loading button without moving
        $cgGoogleButtonSizeHeight = '44px';

        include (__DIR__.'/../check-language.php');

        $wp_upload_dir = wp_upload_dir();
        $intervalConf = cg_shortcode_interval_check(0,[],'cg_google_sign_in');
        if(!$intervalConf['shortcodeIsActive']){
            echo contest_gal1ery_convert_for_html_output_without_nl2br($intervalConf['TextWhenShortcodeIntervalIsOff']);
        }else{
            echo contest_gal1ery_convert_for_html_output_without_nl2br($intervalConf['TextWhenShortcodeIntervalIsOn']);
            if(!is_user_logged_in()){
                if(!empty($TextBeforeGoogleSignInButton)){
                    echo "<div id='cgTextBeforeGoogleSignInButtonContainer' >";
                    echo $TextBeforeGoogleSignInButton;
                    echo "</div>";
                }

				// no pre tag here!!!
				//echo "<pre>";
                include(__DIR__ . '/../v10/v10-frontend/google/google-sign-in-button.php');
	            //echo "</pre>";
            }else{

                $user = wp_get_current_user();
                $cgGoogleSignInJustSignedIn = get_user_meta( $user->ID, 'cgGoogleSignInJustSignedIn');

                if(!empty($cgGoogleSignInJustSignedIn)){

                    delete_user_meta( $user->ID, 'cgGoogleSignInJustSignedIn');
                    // older galleries might also used google sign in,
                    // that's why version have to be proved and not set manually to 14

                    $isFromGoogleSignIn = false;

                    $BorderRadiusLogin = $BorderRadiusClass;
                    $FeControlsStyleLogin = $cgFeControlsStyle;

	                // no pre tag here!!!
	                //echo "<pre>";
                    include (__DIR__.'/../v10/v10-admin/users/frontend/login/users-login-text-after-login.php');
                    include(__DIR__.'/../v10/v10-frontend/gallery/cg-messages.php');
	                //echo "</pre>";

                }
                echo "<input type='hidden' id='cgLoggedInTrue' class='cgLoggedInTrue'  value='1'/>";
            }
        }

        $contest_gal1ery_google_sign_in = ob_get_clean();

        return $contest_gal1ery_google_sign_in;


    }

}

?>
