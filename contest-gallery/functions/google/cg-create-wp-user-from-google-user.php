<?php

if(!function_exists('cg_google_sign_in_get_email_domain')){
    function cg_google_sign_in_get_email_domain($email){
        $email = strtolower(trim($email));
        if(strpos($email,'@') === false){
            return '';
        }
        $emailParts = explode('@',$email);
        return trim(end($emailParts));
    }
}

if(!function_exists('cg_google_sign_in_is_existing_wp_email_auto_link_allowed')){
    function cg_google_sign_in_is_existing_wp_email_auto_link_allowed($payload){
        $email = (!empty($payload['email'])) ? $payload['email'] : '';
        $emailDomain = cg_google_sign_in_get_email_domain($email);

        if($emailDomain=='gmail.com' || $emailDomain=='googlemail.com'){
            return true;
        }

        if(!empty($payload['hd'])){
            $hostedDomain = strtolower(trim($payload['hd']));
            if($hostedDomain!='' && $hostedDomain==$emailDomain){
                return true;
            }
        }

        return false;
    }
}

if(!function_exists('cg_google_sign_in_link_wp_user')){
    function cg_google_sign_in_link_wp_user($payload,$WpUserId){

        $GoogleId = (!empty($payload['sub'])) ? $payload['sub'] : '';
        $Email = (!empty($payload['email'])) ? $payload['email'] : '';
        $NickName = (!empty($payload['name'])) ? $payload['name'] : '';
        $GivenName = (!empty($payload['given_name'])) ? $payload['given_name'] : '';
        $FamilyName = (!empty($payload['family_name'])) ? $payload['family_name'] : '';
        $ImageUrl = (!empty($payload['picture'])) ? $payload['picture'] : '';
        $WpUserId = intval($WpUserId);

        if(empty($GoogleId) || empty($WpUserId)){
            return false;
        }

        global $wpdb;

        $tablename_contest_gal1ery_google_users = $wpdb->prefix."contest_gal1ery_google_users";
        $googleUserId = $wpdb->get_var($wpdb->prepare("SELECT id FROM $tablename_contest_gal1ery_google_users WHERE GoogleId = %s LIMIT 1",$GoogleId));

        if(!empty($googleUserId)){
            return true;
        }

        $wpdb->query( $wpdb->prepare(
            "
                INSERT INTO $tablename_contest_gal1ery_google_users
                ( id, GoogleId, Email, NickName, GivenName, FamilyName, ImageUrl, WpUserId)
                VALUES (%s,%s,%s,%s,%s,%s,%s,%d)
            ",
            '',$GoogleId,$Email,$NickName,$GivenName,$FamilyName,$ImageUrl,$WpUserId
        ) );

        return true;
    }
}

if(!function_exists('cg_google_sign_in_render_public_error')){
    function cg_google_sign_in_render_public_error($message){
        if(empty($message)){
            $message = 'Google sign in could not be completed. Please contact administrator.';
        }
        ?>
        <script data-cg-processing="true">
            cgJsClass.gallery.vars.isSuccessFullySignedIn = false;
            cgJsClass.gallery.function.message.close();
            cgJsClass.gallery.function.message.show('googlesignin',<?php echo json_encode($message); ?>,undefined,undefined,undefined,undefined,true,undefined,undefined,true);
        </script>
        <?php
    }
}

if(!function_exists('cg_create_wp_user_from_google_user')){
    function cg_create_wp_user_from_google_user($payload,$isSignOn = false){

        $GoogleId = (!empty($payload['sub'])) ? $payload['sub'] : '';
        $Email = (!empty($payload['email'])) ? $payload['email'] : '';
        $GivenName = (!empty($payload['given_name'])) ? $payload['given_name'] : '';
        $FamilyName = (!empty($payload['family_name'])) ? $payload['family_name'] : '';

        global $wpdb;

        $tablename_registry_and_login_options = $wpdb->prefix."contest_gal1ery_registry_and_login_options";
        $tablenameWpUsers = $wpdb->base_prefix . "users";

        $count_tablename_registry_and_login_options = $wpdb->get_var( "SELECT id FROM $tablename_registry_and_login_options WHERE GeneralID = 1 LIMIT 1");
        if(empty($count_tablename_registry_and_login_options)){
            cg_create_registry_and_login_options();
        }
        $RegistryUserRole = $wpdb->get_var( "SELECT RegistryUserRole FROM $tablename_registry_and_login_options WHERE GeneralID = 1");

        $cg_main_user_name = '';

        if(strlen($GivenName)>=5){
            $cg_main_user_name .= strtolower(substr($GivenName,0,3));
        }else{
            $cg_main_user_name .= strtolower(substr($GivenName,0,1));
        }

        if(strlen($FamilyName)>=5){
            $cg_main_user_name .= strtolower(substr($FamilyName,0,3));
        }else{
            $cg_main_user_name .= strtolower(substr($FamilyName,0,1));
        }

        if(empty($cg_main_user_name)){
            $cg_main_user_name = 'cguser';
        }

        $i = 0;
        do {
            $cg_main_user_name_to_check = $cg_main_user_name.'-'.$i;
            $checkWpIdViaName = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $tablenameWpUsers WHERE user_login = %s OR user_nicename = %s OR display_name = %s", $cg_main_user_name_to_check, $cg_main_user_name_to_check, $cg_main_user_name_to_check));
            $i++;
        } while (!empty($checkWpIdViaName));

        $WpUserId = false;

        if(!empty($Email)){
            $WpUserId = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $tablenameWpUsers WHERE user_email = %s", $Email));
        }

        if(empty($WpUserId) && !empty($Email) && !empty($GoogleId)){
            $cg_main_user_name = $cg_main_user_name_to_check;
            $user_registered = current_time( 'mysql' );
            $user_nicename=$cg_main_user_name;
            $display_name=$cg_main_user_name;
            $user_login=$cg_main_user_name;
            $user_email=$Email;
            $password = wp_hash_password(wp_generate_password( 32, true, true ));
            $activation_key = md5(time() . $password);
            $activation_key = 'cg-key---'.$activation_key.'-confirmed';

            $WpUserId = cg1l_wp_insert_user($user_login,$user_nicename,$user_email,$user_registered,$display_name);

            if(empty($WpUserId) || is_wp_error($WpUserId)){
                return;
            }

            $wpdb->update(
                $wpdb->users,
                [
                    'user_pass' => $password,
                ],
                [
                    'ID' => $WpUserId,
                ],
                [
                    '%s',
                ],
                [
                    '%d',
                ]
            );

            wp_update_user( array( 'ID' => $WpUserId, 'role' => $RegistryUserRole ) );

            $wpdb->update(
                $wpdb->users,
                [
                    'user_activation_key' => $activation_key,
                ],
                [
                    'ID' => $WpUserId,
                ],
                [
                    '%s',
                ],
                [
                    '%d',
                ]
            );

            cg_google_sign_in_link_wp_user($payload,$WpUserId);

            if($isSignOn){
                if(cg_check_headers_sent()){
                    return;
                }
                update_user_meta( $WpUserId, 'cgGoogleSignInJustSignedIn', 1);
                wp_set_auth_cookie( $WpUserId,true);
            }
        }

    }
}
