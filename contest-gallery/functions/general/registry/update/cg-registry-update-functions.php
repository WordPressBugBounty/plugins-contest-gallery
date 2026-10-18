<?php

// Validate before WordPress saves. Persist only after a successful core profile update.
add_action('user_profile_update_errors','cg_registry_validate_wordpress_profile_urls',10,3);
add_action('profile_update','cg_update_additional_registry_form_user_fields');

if(!function_exists('cg_registry_wordpress_profile_values')){
function cg_registry_wordpress_profile_values($post){
    $posted = array();
    foreach($post as $key=>$value){
        if(strpos($key,'cg_custom_field_id_')===0){$posted[substr($key,19)] = $value;}
    }
    return cg_member_profile_validate_fields($posted,cg_member_profile_fields());
}
}

if(!function_exists('cg_registry_validate_wordpress_profile_urls')){
function cg_registry_validate_wordpress_profile_urls($errors,$update,$user){
    if(!$update || !is_admin() || empty($_POST['cg_user_data_available']) || !current_user_can('edit_user',$user->ID) || !cgHasUserGroupAllowedToEdit(wp_get_current_user())){return;}
    $values = cg_registry_wordpress_profile_values(wp_unslash($_POST));
    if(!is_wp_error($values)){return;}
    $data = $values->get_error_data();
    if(!empty($data['fieldErrors'])){
        foreach(cg_member_profile_fields() as $field){
            if(isset($data['fieldErrors'][$field->id])){
                $errors->add('cg_field_'.$field->id,esc_html(html_entity_decode($field->Field_Name,ENT_QUOTES,'UTF-8')).': '.esc_html(cg_member_t($data['fieldErrors'][$field->id])));
            }
        }
    }else{$errors->add('cg_fields',esc_html(cg_member_t($values->get_error_message())));}
}
}

if(!function_exists('cg_update_additional_registry_form_user_fields')){
    function cg_update_additional_registry_form_user_fields($user_id){
        if(empty($_POST['_wpnonce']) || !is_string($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'update-user_'.$user_id)){return;}
        if(!is_admin() || !current_user_can('edit_user',$user_id) || !cgHasUserGroupAllowedToEdit(wp_get_current_user()) || empty($_POST['cg_user_data_available'])){return;}
        $values = cg_registry_wordpress_profile_values(wp_unslash($_POST));
        if(is_wp_error($values)){return;}
        foreach($values as $key=>$value){update_user_meta($user_id,$key,wp_slash($value));}
    }
}

if(!function_exists('cg_update_user_meta_when_register_and_delete_user_entries')){
    function cg_update_user_meta_when_register_and_delete_user_entries( $newWpId,$activation_key,$user_email, $cg_users_pin_from_email_check = 0) {
        global $wpdb;
        $tablenameCreateUserEntries = $wpdb->prefix . "contest_gal1ery_create_user_entries";
        $tablename_create_user_form = $wpdb->prefix . "contest_gal1ery_create_user_form";

        $userAccountEntries = $wpdb->get_results("SELECT id, f_input_id, Field_Type, Field_Content, Checked FROM $tablenameCreateUserEntries WHERE
                 (Field_Type = 'user-check-agreement-field' OR Field_Type = 'user-text-field' OR Field_Type = 'user-url-field' OR Field_Type = 'user-comment-field' OR Field_Type = 'user-select-field' OR Field_Type = 'user-radio-field' OR Field_Type = 'user-check-field'
                      OR Field_Type = 'wpfn' OR Field_Type = 'wpln' OR Field_Type = 'main-nick-name') 
                AND (activation_key = '$activation_key') ");

        if(count($userAccountEntries)){
            foreach ($userAccountEntries as $entry){
                $f_input_id = $entry->f_input_id;
                if($entry->Field_Type == 'user-check-agreement-field'){
                    $value = $wpdb->get_row("SELECT * FROM $tablename_create_user_form WHERE id = '$f_input_id' ");
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
                    $value = $wpdb->get_row("SELECT * FROM $tablename_create_user_form WHERE id = '$f_input_id' ");
                    if(empty($value)){// then field must be already deleted, no user meta add, added since 28.0.6
                        continue;
                    }
                    $value = $entry->Field_Type==='user-check-field' ? cg_profile_field_value($value,cg_profile_registry_check_value($entry->Field_Content)) : $entry->Field_Content;
                }

                if($entry->Field_Type == 'wpfn'){
                    update_user_meta( $newWpId, 'first_name', $value);
                } elseif($entry->Field_Type == 'wpln'){
                    update_user_meta( $newWpId, 'last_name', $value);
                } elseif($entry->Field_Type == 'main-nick-name'){
                    update_user_meta( $newWpId, 'nickname', $value);
                } else{
                    $key = cg_profile_meta_key($entry->f_input_id);
                    update_user_meta( $newWpId, $key, wp_slash($value));
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
        $registryUserRoleGalleryDbVersion = (!empty($GalleryID)) ? 10 : 14;
        $registryUserRole = (isset($options['registry-login']['RegistryUserRole'])) ? $options['registry-login']['RegistryUserRole'] : '';
        $options['registry-login']['RegistryUserRole'] = cg_get_safe_registry_user_role($registryUserRole,$registryUserRoleGalleryDbVersion);
        $lostPasswordMailFrom = isset($options['registry-login']['LostPasswordMailFrom']) ? $options['registry-login']['LostPasswordMailFrom'] : $options['registry-login']['LostPasswordMailReply'];

        $accountNavigationRow = (array)$wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_registry_and_login_options WHERE $GalleryGeneralIDString = %d LIMIT 1",$GalleryGeneralID),ARRAY_A);
        // Older gallery-specific forms do not expose this global navigation group.
        $accountNavigationChanges = array(
            'LogoutLink'=>isset($accountNavigationRow['LogoutLink']) ? $accountNavigationRow['LogoutLink'] : '',
            'BackToGalleryLink'=>isset($accountNavigationRow['BackToGalleryLink']) ? $accountNavigationRow['BackToGalleryLink'] : ''
        );
        if(empty($GalleryID)){
            $accountNavigationChanges = cg_account_navigation_prepare_save($options['registry-login'],$accountNavigationRow);
        }
        $wpdb->update(
            "$tablename_registry_and_login_options",
            array_merge(array(
                'LogoutLink' => $options['registry-login']['LogoutLink'],
                'BackToGalleryLink' => $options['registry-login']['BackToGalleryLink'],
                'RegistryUserRole' => $options['registry-login']['RegistryUserRole'],
                'LostPasswordMailActive' => $options['registry-login']['LostPasswordMailActive'],
                'LoginAfterConfirm' => $options['registry-login']['LoginAfterConfirm'],
                'ConfirmExpiry' => $options['registry-login']['ConfirmExpiry'],
                'PinExpiry' => $options['registry-login']['PinExpiry'],
                'LostPasswordMailAddressor' => $options['registry-login']['LostPasswordMailAddressor'],
                'LostPasswordMailFrom' => $lostPasswordMailFrom,
                'LostPasswordMailReply' => $options['registry-login']['LostPasswordMailReply'],
                'LostPasswordMailSubject' => $options['registry-login']['LostPasswordMailSubject'],
                'LostPasswordMailConfirmation' => $options['registry-login']['LostPasswordMailConfirmation'],
                'TextBeforeLoginForm' => $options['registry-login']['TextBeforeLoginForm'],
                'EditProfileGroups' => $options['registry-login']['EditProfileGroups'],
                'TextBeforeRegFormBeforeLoggedIn' => $options['registry-login']['TextBeforeRegFormBeforeLoggedIn'],
                'TextBeforePinFormBeforeLoggedIn' => $options['registry-login']['TextBeforePinFormBeforeLoggedIn'],
                'PermanentTextWhenLoggedIn' => $options['registry-login']['PermanentTextWhenLoggedIn']
            ),$accountNavigationChanges),
            array($GalleryGeneralIDString => $GalleryGeneralID),
            null,
            array('%d')
        );

        global $cg_account_navigation_settings_cache;
        unset($cg_account_navigation_settings_cache[$wpdb->prefix]);

        if(!empty($GalleryID)){
            return;
        }

        $wpdb->update(
            "$tablename_options_visual",
            array(
                'BorderRadiusRegistry' => 1, 'FeControlsStyleRegistry' => $options['visual']['FeControlsStyleRegistry'],
                'BorderRadiusLogin' => 1,'FeControlsStyleLogin' => $options['visual']['FeControlsStyleLogin']
            ),
            array('GeneralID' => 1),
            array(
                '%d','%s',
                '%d','%s'
            ),
            array('%d')
        );

        $OpenAiKeyCurrent = $wpdb->get_var(
            "SELECT OpenAiKey FROM $tablename_options_pro_options WHERE GeneralID = 1"
        );
        $OpenAiKey = cg_get_global_secret_post_value(
            (string)$OpenAiKeyCurrent,
            'OpenAiKey'
        );
        $regMailFrom = isset($options['pro']['RegMailFrom']) ? $options['pro']['RegMailFrom'] : $options['pro']['RegMailReply'];

        $wpdb->update(
            "$tablename_options_pro_options",
            array(
                'ForwardAfterLoginUrlCheck' => $options['pro']['ForwardAfterLoginUrlCheck'],'ForwardAfterLoginUrl' => $options['pro']['ForwardAfterLoginUrl'],
                'ForwardAfterLoginTextCheck' => $options['pro']['ForwardAfterLoginTextCheck'],'ForwardAfterLoginText' => $options['pro']['ForwardAfterLoginText'],
                'RegMailOptional' => $options['pro']['RegMailOptional'],'ForwardAfterRegText' => $options['pro']['ForwardAfterRegText'],
                'TextAfterEmailConfirmation' => $options['pro']['TextAfterEmailConfirmation'],'TextAfterPinConfirmation' => $options['pro']['TextAfterPinConfirmation'],'HideRegFormAfterLogin' => $options['pro']['HideRegFormAfterLogin'],
                'HideRegFormAfterLoginShowTextInstead' => $options['pro']['HideRegFormAfterLoginShowTextInstead'],'HideRegFormAfterLoginTextToShow' => $options['pro']['HideRegFormAfterLoginTextToShow'],
                'RegMailAddressor' => $options['pro']['RegMailAddressor'],'RegMailFrom' => $regMailFrom,'RegMailReply' => $options['pro']['RegMailReply'],
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
                '%s','%s','%s',
                '%s','%s','%s','%s',
                '%s','%s',
                '%s'
            ),
            array('%d')
        );



    }
}



?>
