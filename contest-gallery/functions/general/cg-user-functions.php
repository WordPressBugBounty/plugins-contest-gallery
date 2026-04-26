<?php

if(!function_exists('cg1l_generate_unique_login_from_email')){
    function cg1l_generate_unique_login_from_email($user_email){

        // 1) Extract the local part of the email
        $local = strtolower( trim( strtok( $user_email, '@' ) ) );

        // 2) Remove all invalid characters for WP login
        $user_login = preg_replace( '/[^a-z0-9_\.-]/', '', $local );

        // 3) Fallback if local part becomes empty
        if ( $user_login === '' ) {
            $user_login = 'user';
        }

        // 4) Make the login unique (this is the important part you asked for)
        $original = $user_login;
        $counter  = 2;

        while ( username_exists( $user_login ) ) {
            $user_login = $original . $counter;
            $counter++;
        }

        return $user_login;

    }
}
if(!function_exists('cg1l_generate_unique_nicename')){
    function cg1l_generate_unique_nicename(){

        global $wpdb;
        $table_usermeta = $wpdb->usermeta;

        // Generate random display_name like "CG User 1234"
        $rand          = wp_rand(1000, 9999);
        $display_name  = 'CG User ' . $rand;

        // Create base nicename from display_name, e.g. "cg-user-1234"
        $nicename_base = sanitize_title($display_name);

        // Fallback if sanitize_title returns empty for some reason
        if ($nicename_base === '') {
            $rand          = wp_rand(1000, 9999);
            $nicename_base = 'cg-user-' . $rand;
        }

        // Ensure user_nicename is unique in wp_users
        $nicename = $nicename_base;
        $suffix   = 2;

        while ($wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_id FROM $table_usermeta 
                 WHERE meta_key = 'nickname' 
                 AND meta_value = %s 
                 LIMIT 1",
                $nicename
            ))) {
            $nicename = $nicename_base . '-' . $suffix;
            $suffix++;
        }

        return $nicename;
    }
}

if(!function_exists('cg1l_wp_insert_user')){
    function cg1l_wp_insert_user($user_login,$user_nicename,$user_email,$user_registered,$display_name){

        $user_data = [
            // kommt aus deinem Code
            'user_login'         => $user_login,
            'user_pass'          => wp_hash_password(wp_generate_password( 32 )),// dummy password, because wordpress hashes itself, real hashed password will be inserted later
            'user_nicename'      => $user_nicename,
            'user_email'         => $user_email,
            'user_registered'    => $user_registered,
            //'user_activation_key'=> $activation_key_for_wp_users_table, will be set later with an update because will be removed
            // through wp_update_user( array( 'ID' => $newWpId, 'role' => $RegistryUserRole ) ); which will be set later
            // and requires to be set so wp_set_auth_cookie is working
            'user_status'        => 0,
            'display_name'       => $display_name,
        ];

        $user_id = wp_insert_user( $user_data );

        return $user_id;

    }
}

if(!function_exists('cg1l_prepare_registry_fields_for_storage')){
    function cg1l_prepare_registry_fields_for_storage($cg_check,$galleryDbVersion,$GalleryID){

        if(empty($cg_check) || !is_array($cg_check)){
            return false;
        }

        global $wpdb;
        $tablenameCreateUserForm = $wpdb->prefix . "contest_gal1ery_create_user_form";

        $allowedFieldTypes = array(
            'main-user-name' => true,
            'main-nick-name' => true,
            'main-mail' => true,
            'password' => true,
            'password-confirm' => true,
            'user-comment-field' => true,
            'profile-image' => true,
            'wpfn' => true,
            'wpln' => true,
            'user-text-field' => true,
            'user-check-field' => true,
            'user-radio-field' => true,
            'user-select-field' => true,
            'user-robot-field' => true,
            'user-check-agreement-field' => true
        );

        if(intval($galleryDbVersion)>=14 || empty($GalleryID)){
            $formRows = $wpdb->get_results("SELECT id, Field_Type, Field_Order FROM $tablenameCreateUserForm WHERE GeneralID = '1' AND Active = '1'");
        }else{
            $formRows = $wpdb->get_results($wpdb->prepare("SELECT id, Field_Type, Field_Order FROM $tablenameCreateUserForm WHERE GalleryID = %d AND Active = '1'",absint($GalleryID)));
        }

        if(empty($formRows)){
            return false;
        }

        $formRowsById = array();
        foreach($formRows as $formRow){
            $formRowsById[absint($formRow->id)] = $formRow;
        }

        $prepared = array();
        $usedFormInputIds = array();

        foreach($cg_check as $key => $value){
            if(!is_array($value) || !isset($value["Form_Input_ID"]) || !isset($value["Field_Type"]) || !isset($value["Field_Order"])){
                return false;
            }

            $Form_Input_ID = absint($value["Form_Input_ID"]);

            if(empty($Form_Input_ID) || empty($formRowsById[$Form_Input_ID]) || !empty($usedFormInputIds[$Form_Input_ID])){
                return false;
            }

            $formRow = $formRowsById[$Form_Input_ID];
            $dbFieldType = sanitize_text_field($formRow->Field_Type);

            if(empty($allowedFieldTypes[$dbFieldType])){
                return false;
            }

            $postedFieldType = sanitize_text_field($value["Field_Type"]);
            $postedFieldOrder = sanitize_text_field($value["Field_Order"]);

            if($postedFieldType !== $dbFieldType || (string)$postedFieldOrder !== (string)$formRow->Field_Order){
                return false;
            }

            $usedFormInputIds[$Form_Input_ID] = true;
            $value["Form_Input_ID"] = $Form_Input_ID;
            $value["Field_Type"] = $dbFieldType;
            $value["Field_Order"] = $formRow->Field_Order;
            $prepared[$key] = $value;
        }

        return $prepared;

    }
}

if(!function_exists('cg1l_resolve_unconfirmed_user_for_activation')){
    function cg1l_resolve_unconfirmed_user_for_activation($userAccountEntries,$cgkey){

        if(empty($cgkey) || empty($userAccountEntries) || !is_array($userAccountEntries)){
            return false;
        }

        $unconfirmedEntries = array();

        foreach($userAccountEntries as $entry){
            if(!empty($entry->Field_Type) && $entry->Field_Type == 'unconfirmed-mail'){
                $unconfirmedEntries[] = $entry;
            }
        }

        if(count($unconfirmedEntries)!==1){
            return false;
        }

        $unconfirmedEntry = $unconfirmedEntries[0];
        $email = sanitize_email(strtolower($unconfirmedEntry->Field_Content));

        if(empty($email) || is_email($email) == false){
            return false;
        }

        $entryWpUserId = (!empty($unconfirmedEntry->wp_user_id)) ? absint($unconfirmedEntry->wp_user_id) : 0;

        $allowedActivationKeys = array($cgkey,'cg-key---'.$cgkey);

        if(!empty($entryWpUserId)){
            $user = get_user_by('id',$entryWpUserId);
            if(!empty($user) && strtolower($user->user_email)==$email && in_array($user->user_activation_key,$allowedActivationKeys,true)){
                return array(
                    'wp_user_id' => absint($user->ID),
                    'email' => $email
                );
            }
            return false;
        }

        $user = get_user_by('email',$email);
        if(empty($user) || empty($user->ID)){
            return false;
        }

        if(in_array($user->user_activation_key,$allowedActivationKeys,true)){
            return array(
                'wp_user_id' => absint($user->ID),
                'email' => $email
            );
        }

        return false;

    }
}

if(!function_exists('cg1l_delete_unconfirmed_user')){
    function cg1l_delete_unconfirmed_user($mainMail){

        global $wpdb;

        $tablenameCreateUserEntries = $wpdb->prefix . "contest_gal1ery_create_user_entries";

        $activation_keys = $wpdb->get_col(
            $wpdb->prepare("
        SELECT DISTINCT activation_key
        FROM {$tablenameCreateUserEntries}
        WHERE (Field_Type = 'main-mail' || Field_Type = 'unconfirmed-mail') 
          AND Field_Content = %s
    ", $mainMail)
        );

        if (!empty($activation_keys)) {
            $placeholders = implode(',', array_fill(0, count($activation_keys), '%s'));
            $sql = $wpdb->prepare("
        DELETE FROM {$tablenameCreateUserEntries}
        WHERE activation_key IN ($placeholders)
    ", $activation_keys);
            $wpdb->query($sql);
        }

    }
}

if(!function_exists('cg1l_resend_unconfirmed_mail')){
    function  cg1l_resend_unconfirmed_mail($gid,$pid,$ReceiverMail,$pageUrl,$ReplyMail,$FromName,$ReplyName,$FromMail,$cc,$bcc,$body,$subject,$old_activation_key = '', $isFrontend = false){

        $suffixSource = 'backend';
        if($isFrontend){
            $suffixSource = 'frontend';
        }

        global $wpdb;
        $tablenameCreateUserEntries = $wpdb->prefix . "contest_gal1ery_create_user_entries";

        $sent = false;

        $resolved_body = contest_gal1ery_convert_for_html_output_without_nl2br($body);

        $pageUrlForEmail = (strpos($pageUrl, '?')) ? $pageUrl . '&' : $pageUrl . '?';
        $posUrl = '$regurl$';

        if(empty($old_activation_key)){
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT Field_Content, activation_key 
	FROM $tablenameCreateUserEntries
	WHERE activation_key = (
    SELECT activation_key 
    FROM $tablenameCreateUserEntries 
    WHERE (Field_Type = 'main-mail' OR Field_Type = 'unconfirmed-mail') 
          AND Field_Content = %s 
    LIMIT 1
  )
    LIMIT 1;",
	                $ReceiverMail
	            ));
            if(empty($row) || empty($row->activation_key)){
                return false;
            }
            $old_activation_key = $row->activation_key;
        }

        $oldUserAccountEntries = $wpdb->get_results(
            $wpdb->prepare("SELECT Field_Type, Field_Content, wp_user_id, Tstamp FROM $tablenameCreateUserEntries WHERE activation_key=%s",$old_activation_key)
        );

        if(empty($oldUserAccountEntries)){
            return false;
        }

        $resolvedUnconfirmedUser = false;
        foreach($oldUserAccountEntries as $oldUserAccountEntry){
            if($oldUserAccountEntry->Field_Type == 'unconfirmed-mail'){
                $resolvedUnconfirmedUser = cg1l_resolve_unconfirmed_user_for_activation($oldUserAccountEntries,$old_activation_key);
                if(empty($resolvedUnconfirmedUser)){
                    return false;
                }
                break;
            }
        }

        $password = wp_generate_password( 32, true, true );

        $time = time();

        $new_activation_key = md5($time . $password);

        $insertedRows = $wpdb->query(
            $wpdb->prepare("
        INSERT INTO {$tablenameCreateUserEntries}
            (GalleryID, wp_user_id, f_input_id, Field_Type, Field_Content, 
             activation_key, Checked, Version, GeneralID, Tstamp) 
        SELECT
            GalleryID, wp_user_id, f_input_id, Field_Type, Field_Content,
            %s AS activation_key, Checked, Version, GeneralID, $time 
        FROM {$tablenameCreateUserEntries}
        WHERE activation_key = %s 
    ", $new_activation_key, $old_activation_key)
        );

        if(empty($insertedRows)){
            return false;
        }

        if(!empty($resolvedUnconfirmedUser)){
            $newWpId = absint($resolvedUnconfirmedUser['wp_user_id']);
            $ReceiverMail = $resolvedUnconfirmedUser['email'];

            $userKeyUpdated = $wpdb->update(
                $wpdb->users,
                array(
                    'user_activation_key' => $new_activation_key,
                ),
                array(
                    'ID' => $newWpId,
                ),
                array(
                    '%s',
                ),
                array(
                    '%d',
                )
            );

            if($userKeyUpdated === false){
                return false;
            }

            $wpdb->update(
                $tablenameCreateUserEntries,
                array(
                    'wp_user_id' => $newWpId,
                    'Field_Content' => $ReceiverMail,
                ),
                array(
                    'activation_key' => $new_activation_key,
                    'Field_Type' => 'unconfirmed-mail',
                ),
                array(
                    '%d',
                    '%s',
                ),
                array(
                    '%s',
                    '%s',
                )
            );
        }

        $TextEmailConfirmation = str_ireplace($posUrl, $pageUrlForEmail . "cgkey=$new_activation_key#cg_activation", $resolved_body);

        // Build headers (From / Reply-To / CC / BCC)
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        // From
        if (!empty($FromMail)) {
            if (!empty($FromName)) {
                $headers[] = 'From: ' . sprintf('%s <%s>', $FromName, $FromMail);
            } else {
                $headers[] = 'From: ' . $FromMail;
            }
        }

        // Reply-To
        if (!empty($ReplyMail)) {
            if (!empty($ReplyName)) {
                $headers[] = 'Reply-To: ' . sprintf('%s <%s>', $ReplyName, $ReplyMail);
            } else {
                $headers[] = 'Reply-To: ' . $ReplyMail;
            }
        }

        // CC / BCC
        if (!empty($cc)) {
            $ccItems = preg_split('/[;,]+/', $cc); // split by ; or ,
            foreach ($ccItems as $item) {
                $item = trim($item);
                if ($item !== '') {
                    $headers[] = 'Cc: ' . $item;
                }
            }
        }
        if (!empty($bcc)) {
            $bccItems = preg_split('/[;,]+/', $bcc); // split by ; or ,
            foreach ($bccItems as $item) {
                $item = trim($item);
                if ($item !== '') {
                    $headers[] = 'Bcc: ' . $item;
                }
            }
        }

        global $cgMailAction;
        global $cgMailGalleryId;
        $cgMailAction = "Registry resend $suffixSource";
        $cgMailGalleryId = $gid;
        add_action( 'wp_mail_failed', 'cg_on_wp_mail_error', 10, 1 );

        // Send
        if(wp_mail( $ReceiverMail, $subject, $TextEmailConfirmation, $headers )){
            $sent = true;
            $WpUserId = 0;
            cg_save_sent_mail($gid,$pid,$WpUserId,$ReceiverMail,$ReplyName,$ReplyMail,$FromName,$FromMail,$cc,$bcc,$subject,$TextEmailConfirmation, 'registry-resend-'.$suffixSource);
        }else{
            $wpdb->delete(
                $tablenameCreateUserEntries,
                ['activation_key' => $new_activation_key],
                ['%s']
            );
        }

        return $sent;

    }
}

if(!function_exists('cg1l_send_registration_mail')){
    function  cg1l_send_registration_mail($proOptions,$GalleryID,$cg_users_pin,$cg_main_mail,$Subject, $TextEmailConfirmation, $activation_key, $posPin = '', $pin = '', $posUrl = '', $currentPageUrl = '' ){
        // Check if valid mail. Take admin mail if not.
        if (is_email($proOptions->RegMailReply)) {
            $cgReply = $proOptions->RegMailReply;
        } else {
            $cgReply = get_option('admin_email');
        }

        $RegMailCC = (empty($proOptions->RegMailCC)) ? '' : $proOptions->RegMailCC;
        $ccPlain = $RegMailCC;
        $RegMailBCC = (empty($proOptions->RegMailBCC)) ? '' : $proOptions->RegMailBCC;
        $bccPlain = $RegMailBCC;
        $FromName = html_entity_decode(strip_tags($proOptions->RegMailAddressor));
        $ReplyName = $FromName;
        $WpUserId = 0;
        $ReplyMail = $cgReply;
        $FromMail = $cgReply;

        $headers = array();
        $headers[] = "From: " . $FromName . " <" . strip_tags($cgReply) . ">";
        $headers[] = "Reply-To: " . strip_tags($cgReply) . "";

        if(!empty($RegMailCC)){
            if(strpos($RegMailCC,';')){
                $RegMailCC = explode(';',$RegMailCC);
                foreach($RegMailCC as $ccValue){
                    $ccValue = trim($ccValue);
                    $headers[] = "CC: $ccValue\r\n";
                }
            }
            else{
                $headers[] = "CC: $RegMailCC\r\n";
            }
        }

        if(strpos($RegMailBCC,';')){
            $RegMailBCC = explode(';',$RegMailBCC);
            foreach($RegMailBCC as $bccValue){
                $bccValue = trim($bccValue);
                $headers[] = "BCC: $bccValue\r\n";
            }
        }
        else{
            $headers[] = "BCC: $RegMailBCC\r\n";
        }

        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=utf-8";

        $ForwardAfterRegText = nl2br(html_entity_decode(stripslashes($proOptions->ForwardAfterRegText)));

        if($cg_users_pin){
            $TextEmailConfirmation = str_ireplace($posPin, $pin, $TextEmailConfirmation);
        }else{
            $currentPageUrlForEmail = (strpos($currentPageUrl, '?')) ? $currentPageUrl . '&' : $currentPageUrl . '?';
            $TextEmailConfirmation = str_ireplace($posUrl, $currentPageUrlForEmail . "cgkey=$activation_key#cg_activation", $TextEmailConfirmation);
        }

        global $cgMailAction;
        global $cgMailGalleryId;
        $cgMailAction = "User registration e-mail";
        $cgMailGalleryId = $GalleryID;
        add_action('wp_mail_failed', 'cg_on_wp_mail_error', 10, 1);

        $result = wp_mail($cg_main_mail, $Subject, $TextEmailConfirmation, $headers);

        if($result){
            cg_save_sent_mail(0,0,$WpUserId,$cg_main_mail,$ReplyName,$ReplyMail,$FromName,$FromMail,$ccPlain,$bccPlain,$Subject, $TextEmailConfirmation, 'registry-frontend');
        }

        return $result;


    }
}
