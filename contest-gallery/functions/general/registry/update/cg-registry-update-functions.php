<?php

add_action( 'personal_options_update', 'cg_update_additional_registry_form_user_fields' );
add_action( 'edit_user_profile_update', 'cg_update_additional_registry_form_user_fields' );

if(!function_exists('cg_update_additional_registry_form_user_fields')){
    function cg_update_additional_registry_form_user_fields( $user_id ) {
        if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
            return;
        }

        if(is_admin()){
            $user = wp_get_current_user();
            if(!empty($_POST['cg_user_data_available'])){
                $_POST = cg1l_sanitize_post($_POST);
                foreach ($_POST as $key => $value){
                    if(strpos($key,'cg_custom_field_id_')!==false){
                        update_user_meta( $user_id, $key, $value);
                    }
                }
            }
        }

    }
}

if(!function_exists('cg_update_user_meta_when_register_and_delete_user_entries')){
    function cg_update_user_meta_when_register_and_delete_user_entries( $newWpId,$activation_key,$user_email, $cg_users_pin_from_email_check = 0) {
        global $wpdb;
        $tablenameCreateUserEntries = $wpdb->prefix . "contest_gal1ery_create_user_entries";
        $tablename_create_user_form = $wpdb->prefix . "contest_gal1ery_create_user_form";

        $userAccountEntries = $wpdb->get_results("SELECT id, f_input_id, Field_Type, Field_Content, Checked FROM $tablenameCreateUserEntries WHERE
                 (Field_Type = 'user-check-agreement-field' OR Field_Type = 'user-text-field' OR Field_Type = 'user-comment-field' OR Field_Type = 'user-select-field' OR Field_Type = 'user-radio-field' OR Field_Type = 'user-check-field'  
                      OR Field_Type = 'wpfn' OR Field_Type = 'wpln' OR Field_Type = 'main-nick-name') 
                AND (activation_key = '$activation_key') ");

        if(count($userAccountEntries)){
            foreach ($userAccountEntries as $entry){
                $f_input_id = $entry->f_input_id;
                if($entry->Field_Type == 'user-check-agreement-field'){
                    $value = $wpdb->get_row("SELECT Field_Name, Field_Content, Required FROM $tablename_create_user_form WHERE id = '$f_input_id' ");
                    if(empty($value)){// then field must be already deleted, no user meta add
                        continue;
                    }
                    $RequiredString =  ($value->Required==1) ? 'yes' : 'no' ;
                    if($entry->Checked==1){
                        $RequiredString .='(cg-user-checked)';
                    }else{
                        $RequiredString .='(cg-user-not-checked)';
                    }
                    $value = $value->Field_Name . ' --- required:' . $RequiredString . ' --- ' . $value->Field_Content;// get both in this case name and content for better documentation
                }else{
                    $value = $wpdb->get_row("SELECT Field_Name, Field_Content, Required FROM $tablename_create_user_form WHERE id = '$f_input_id' ");
                    if(empty($value)){// then field must be already deleted, no user meta add, added since 28.0.6
                        continue;
                    }
                    $value = $entry->Field_Content;
                }

                if($entry->Field_Type == 'wpfn'){
                    update_user_meta( $newWpId, 'first_name', $value);
                } elseif($entry->Field_Type == 'wpln'){
                    update_user_meta( $newWpId, 'last_name', $value);
                } elseif($entry->Field_Type == 'main-nick-name'){
                    update_user_meta( $newWpId, 'nickname', $value);
                } else{
                    $key = 'cg_custom_field_id_'.$entry->f_input_id;
                    update_user_meta( $newWpId, $key, $value);
                }

            }
            // now after after data is added to meta_user
            // data ($userAccountEntries) can be deleted
            /*$wpdb->query($wpdb->prepare(
                "
                                    DELETE FROM $tablenameCreateUserEntries WHERE activation_key = %s
                                ",
                $activation_key
            ));*/
            cg1l_delete_unconfirmed_user($user_email);
        }else{
            if(!empty($cg_users_pin_from_email_check)){
                cg1l_delete_unconfirmed_user($user_email);
            }
        }
    }
}


if(!function_exists('cg_update_registry_and_login_options_v14')){
    function cg_update_registry_and_login_options_v14( $options, $GalleryID = 0) {

        $GalleryGeneralID = 1;
        $GalleryGeneralIDString = 'GeneralID';

        if(!empty($GalleryID)){
            $GalleryGeneralID = $GalleryID;
            $GalleryGeneralIDString = 'GalleryID';
        }

        global $wpdb;

        $tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
        $tablename_options_pro_options  = $wpdb->prefix . "contest_gal1ery_pro_options";
        $tablename_registry_and_login_options = $wpdb->prefix . "contest_gal1ery_registry_and_login_options";

        $wpdb->update(
            "$tablename_registry_and_login_options",
            array(
                'LogoutLink' => $options['registry-login']['LogoutLink'],
                'BackToGalleryLink' => $options['registry-login']['BackToGalleryLink'],
                'RegistryUserRole' => $options['registry-login']['RegistryUserRole'],
                'LostPasswordMailActive' => $options['registry-login']['LostPasswordMailActive'],
                'LoginAfterConfirm' => $options['registry-login']['LoginAfterConfirm'],
                'ConfirmExpiry' => $options['registry-login']['ConfirmExpiry'],
                'PinExpiry' => $options['registry-login']['PinExpiry'],
                'LostPasswordMailAddressor' => $options['registry-login']['LostPasswordMailAddressor'],
                'LostPasswordMailReply' => $options['registry-login']['LostPasswordMailReply'],
                'LostPasswordMailSubject' => $options['registry-login']['LostPasswordMailSubject'],
                'LostPasswordMailConfirmation' => $options['registry-login']['LostPasswordMailConfirmation'],
                'TextBeforeLoginForm' => $options['registry-login']['TextBeforeLoginForm'],
                'EditProfileGroups' => $options['registry-login']['EditProfileGroups'],
                'TextBeforeRegFormBeforeLoggedIn' => $options['registry-login']['TextBeforeRegFormBeforeLoggedIn'],
                'TextBeforePinFormBeforeLoggedIn' => $options['registry-login']['TextBeforePinFormBeforeLoggedIn'],
                'PermanentTextWhenLoggedIn' => $options['registry-login']['PermanentTextWhenLoggedIn']
            ),
            array($GalleryGeneralIDString => $GalleryGeneralID),
            array(
                '%s','%s','%s','%d','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s'
            ),
            array('%d')
        );

        if(!empty($GalleryID)){
            return;
        }

        $wpdb->update(
            "$tablename_options_visual",
            array(
                'BorderRadiusRegistry' => $options['visual']['BorderRadiusRegistry'], 'FeControlsStyleRegistry' => $options['visual']['FeControlsStyleRegistry'],
                'BorderRadiusLogin' => $options['visual']['BorderRadiusLogin'],'FeControlsStyleLogin' => $options['visual']['FeControlsStyleLogin']
            ),
            array('GeneralID' => 1),
            array(
                '%d','%s',
                '%d','%s'
            ),
            array('%d')
        );

        $OpenAiKey = (isset($_POST['OpenAiKey'])) ? sanitize_text_field($_POST['OpenAiKey']) : '';

        $wpdb->update(
            "$tablename_options_pro_options",
            array(
                'ForwardAfterLoginUrlCheck' => $options['pro']['ForwardAfterLoginUrlCheck'],'ForwardAfterLoginUrl' => $options['pro']['ForwardAfterLoginUrl'],
                'ForwardAfterLoginTextCheck' => $options['pro']['ForwardAfterLoginTextCheck'],'ForwardAfterLoginText' => $options['pro']['ForwardAfterLoginText'],
                'RegMailOptional' => $options['pro']['RegMailOptional'],'ForwardAfterRegText' => $options['pro']['ForwardAfterRegText'],
                'TextAfterEmailConfirmation' => $options['pro']['TextAfterEmailConfirmation'],'TextAfterPinConfirmation' => $options['pro']['TextAfterPinConfirmation'],'HideRegFormAfterLogin' => $options['pro']['HideRegFormAfterLogin'],
                'HideRegFormAfterLoginShowTextInstead' => $options['pro']['HideRegFormAfterLoginShowTextInstead'],'HideRegFormAfterLoginTextToShow' => $options['pro']['HideRegFormAfterLoginTextToShow'],
                'RegMailAddressor' => $options['pro']['RegMailAddressor'],'RegMailReply' => $options['pro']['RegMailReply'],
                'RegMailSubject' => $options['pro']['RegMailSubject'],'TextEmailConfirmation' => $options['pro']['TextEmailConfirmation'],'TextPinConfirmation' => $options['pro']['TextPinConfirmation'],'RegPinSubject' => $options['pro']['RegPinSubject'],
                'RegMailCC' => $options['pro']['RegMailCC'],'RegMailBCC' => $options['pro']['RegMailBCC'],
                'OpenAiKey' => $OpenAiKey,
            ),
            array('GeneralID' => 1),
            array(
                '%d','%s',
                '%d','%s',
                '%d','%s',
                '%s','%s','%d',
                '%d','%s',
                '%s','%s',
                '%s','%s','%s','%s',
                '%s','%s',
                '%s'
            ),
            array('%d')
        );



    }
}



?>