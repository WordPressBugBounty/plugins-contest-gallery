<?php

include_once(__DIR__.'/registry/cg-login-handoff.php');
include_once(__DIR__.'/registry/cg-password-reset.php');

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
            // through safe registry role handling which will be set later
            // and requires to be set so wp_set_auth_cookie is working
            'user_status'        => 0,
            'display_name'       => $display_name,
        ];

        $user_id = wp_insert_user( $user_data );

        return $user_id;

    }
}

if(!function_exists('cg1l_prepare_registry_fields_for_storage')){
    function cg1l_prepare_registry_fields_for_storage($cg_check,$galleryDbVersion,$GalleryID,$isPinForm = false){

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
            'user-url-field' => true,
            'user-check-field' => true,
            'user-radio-field' => true,
            'user-select-field' => true,
            'user-robot-field' => true,
            'user-check-agreement-field' => true
        );

        if(intval($galleryDbVersion)>=14 || empty($GalleryID)){
            $formRows = $wpdb->get_results("SELECT id, Field_Type, Field_Order, Field_Content, Required, PinField, Min_Char, Max_Char FROM $tablenameCreateUserForm WHERE GeneralID = '1' AND Active = '1'");
        }else{
            $formRows = $wpdb->get_results($wpdb->prepare("SELECT id, Field_Type, Field_Order, Field_Content, Required, PinField, Min_Char, Max_Char FROM $tablenameCreateUserForm WHERE GalleryID = %d AND Active = '1'",absint($GalleryID)));
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
        $contentsById = array();

        foreach($cg_check as $key => $value){
            if(!is_array($value) || !isset($value["Form_Input_ID"]) || !isset($value["Field_Type"]) || !isset($value["Field_Order"])){
                return false;
            }

            if(!is_scalar($value['Form_Input_ID']) || !is_scalar($value['Field_Type']) || !is_scalar($value['Field_Order'])){return false;}
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

            if($dbFieldType==='main-nick-name'){
                $raw = isset($value['Field_Content']) ? $value['Field_Content'] : '';
                if(!is_string($raw)){return false;}
                $nickname = trim(sanitize_text_field($raw));
                if(is_email($nickname)){return new WP_Error('name_email','Please use a display name instead of an email address.');}
                $value['Field_Content'] = $nickname;
            }
            if(in_array($dbFieldType,array('user-url-field','user-check-field','user-select-field','user-radio-field'),true)){
                $raw = isset($value['Field_Content']) ? $value['Field_Content'] : '';
                // Cached older registration forms still submit comma-separated checkbox values.
                if($dbFieldType!=='user-url-field' && is_string($raw)){$raw = cg_profile_field_value($formRow,$raw);}
                $validated = cg_member_profile_validate_fields(array($Form_Input_ID=>$raw),array($formRow));
                if(is_wp_error($validated)){return $validated;}
                $value['Field_Content'] = $validated[cg_profile_meta_key($Form_Input_ID)];
            }
            $contentsById[$Form_Input_ID] = isset($value['Field_Content']) ? $value['Field_Content'] : '';
            $usedFormInputIds[$Form_Input_ID] = true;
            $value["Form_Input_ID"] = $Form_Input_ID;
            $value["Field_Type"] = $dbFieldType;
            $value["Field_Order"] = $formRow->Field_Order;
            $prepared[$key] = $value;
        }

        // Required inputs come from the active server schema, including omitted POST rows.
        // Match the renderer's PIN form selection. CAPTCHA has its own signed mode/validator.
        foreach($formRows as $formRow){
            $type = $formRow->Field_Type;
            if(empty($allowedFieldTypes[$type]) || $type==='user-robot-field' || (empty($formRow->Required) && $type!=='main-mail')){continue;}
            if($isPinForm && empty($formRow->PinField) && $type!=='main-mail'){continue;}
            $id = absint($formRow->id);
            $requiredError = new WP_Error('required','Required');
            if(empty($usedFormInputIds[$id])){return $requiredError;}
            $content = $contentsById[$id];
            if($type==='profile-image'){
                $file = isset($_FILES['cg_input_image_upload_file']) ? $_FILES['cg_input_image_upload_file'] : array();
                if(!isset($file['error'][0]) || $file['error'][0]===UPLOAD_ERR_NO_FILE){return $requiredError;}
                $validImage = cg_profile_image_validate_upload(cg_profile_image_upload_file($file));
                if(is_wp_error($validImage)){return $validImage;}
            }elseif($type==='user-check-agreement-field'){
                if($content!=='checked'){return $requiredError;}
            }elseif($type==='user-check-field'){
                if(!is_array($content) || !$content){return $requiredError;}
            }elseif(!is_scalar($content) || trim((string)$content)===''){
                return $requiredError;
            }
        }
        return $prepared;

    }
}

if(!function_exists('cg1l_get_registry_activation_browser_binding')){
    function cg1l_get_registry_activation_browser_binding($createSession = false){

        if($createSession){
            if(!function_exists('cg1l_get_or_create_frontend_session_id')){
                return '';
            }
            $sessionId = cg1l_get_or_create_frontend_session_id(true);
        }else{
            if(!function_exists('cg1l_get_frontend_session_id_from_cookie')){
                return '';
            }
            $sessionId = cg1l_get_frontend_session_id_from_cookie();
        }

        if(empty($sessionId)){
            return '';
        }

        return hash_hmac('sha256','cg1l-registry-activation-browser|'.$sessionId,wp_salt('auth'));

    }
}

if(!function_exists('cg1l_store_registry_activation_browser_binding')){
    function cg1l_store_registry_activation_browser_binding($activationKey,$binding,$GalleryID,$wpUserId,$Version,$GeneralID,$Tstamp){

        if(
            empty($activationKey) ||
            empty($binding) ||
            !preg_match('/^[a-f0-9]{64}$/',$binding)
        ){
            return false;
        }

        global $wpdb;
        $tablenameCreateUserEntries = $wpdb->prefix . "contest_gal1ery_create_user_entries";

        $wpdb->delete(
            $tablenameCreateUserEntries,
            array(
                'activation_key' => $activationKey,
                'Field_Type' => 'activation-browser-session'
            ),
            array('%s','%s')
        );

        $inserted = $wpdb->insert(
            $tablenameCreateUserEntries,
            array(
                'GalleryID' => absint($GalleryID),
                'wp_user_id' => absint($wpUserId),
                'f_input_id' => 0,
                'Field_Type' => 'activation-browser-session',
                'Field_Content' => $binding,
                'activation_key' => $activationKey,
                'Checked' => 0,
                'Version' => $Version,
                'GeneralID' => absint($GeneralID),
                'Tstamp' => absint($Tstamp)
            ),
            array('%d','%d','%d','%s','%s','%s','%d','%s','%d','%d')
        );

        return ($inserted !== false);

    }
}

if(!function_exists('cg1l_registry_activation_browser_matches')){
    function cg1l_registry_activation_browser_matches($userAccountEntries){

        if(empty($userAccountEntries) || !is_array($userAccountEntries)){
            return false;
        }

        $storedBindings = array();
        foreach($userAccountEntries as $entry){
            if(!empty($entry->Field_Type) && $entry->Field_Type === 'activation-browser-session'){
                $storedBindings[] = sanitize_text_field($entry->Field_Content);
            }
        }

        if(count($storedBindings)!==1 || !preg_match('/^[a-f0-9]{64}$/',$storedBindings[0])){
            return false;
        }

        $currentBinding = cg1l_get_registry_activation_browser_binding(false);
        if(empty($currentBinding)){
            return false;
        }

        return cg1l_hash_equals($storedBindings[0],$currentBinding);

    }
}

if(!function_exists('cg1l_release_registry_activation_lock')){
    function cg1l_release_registry_activation_lock($lockName){

        if(empty($lockName)){
            return;
        }

        global $wpdb;
        $wpdb->get_var($wpdb->prepare("SELECT RELEASE_LOCK(%s)",$lockName));

    }
}

if(!function_exists('cg1l_acquire_registry_activation_lock')){
    function cg1l_acquire_registry_activation_lock($activationKey){

        if(empty($activationKey)){
            return false;
        }

        static $acquiredLocks = array();
        $lockName = 'cg1l-registry-activation-'.md5($activationKey);

        if(!empty($acquiredLocks[$lockName])){
            return true;
        }

        global $wpdb;
        $isAcquired = $wpdb->get_var($wpdb->prepare("SELECT GET_LOCK(%s,0)",$lockName));

        if((string)$isAcquired !== '1'){
            return false;
        }

        $acquiredLocks[$lockName] = true;
        register_shutdown_function('cg1l_release_registry_activation_lock',$lockName);

        return true;

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
            $requestKeys = $wpdb->get_col($wpdb->prepare(
                "SELECT Field_Content FROM {$tablenameCreateUserEntries} WHERE Field_Type = 'cg-pin-request-key' AND activation_key IN ($placeholders)",
                $activation_keys
            ));
            foreach($requestKeys as $requestKey){
                if(function_exists('cg_pin_auth_transient_key')){
                    cg_security_state_delete('pin',cg_pin_auth_transient_key($requestKey));
                }
            }
            $placeholders = implode(',', array_fill(0, count($activation_keys), '%s'));
            $sql = $wpdb->prepare("
        DELETE FROM {$tablenameCreateUserEntries}
        WHERE activation_key IN ($placeholders)
    ", $activation_keys);
            $wpdb->query($sql);
        }

    }
}

// Confirmation mail has its own limits. It must not consume a Member/Paid PIN allowance.
if(!function_exists('cg1l_reserve_confirmation_mail')){
    function cg1l_reserve_confirmation_mail($email){
        $email = strtolower(trim($email));
        $lock = cg_pin_auth_acquire_lock('confirmation-mail',$email);
        if(!$lock){return new WP_Error('cg_confirmation_busy','Please try again.');}
        try{
            $key = 'cg_confirmation_rate_'.md5($email);
            $now = time();
            $state = get_transient($key);
            if(!is_array($state) || empty($state['until']) || $state['until'] <= $now){$state = array('until'=>$now+3600,'count'=>0,'last'=>0);}
            $wait = max(0,$state['last']+60-$now);
            if($state['count'] >= 5){$wait = max($wait,$state['until']-$now);}
            if($wait){return new WP_Error('cg_confirmation_rate_limited',sprintf('Please wait %d seconds before requesting another confirmation email.',$wait),array('retryAfter'=>$wait));}
            $state['last'] = $now;
            $state['count']++;
            set_transient($key,$state,max(1,$state['until']-$now));
            if(get_transient($key)!==$state){return new WP_Error('cg_confirmation_busy','Please try again.');}
            return true;
        }finally{cg_pin_auth_release_lock($lock);}
    }
}

if(!function_exists('cg1l_resend_unconfirmed_mail')){
    function  cg1l_resend_unconfirmed_mail($gid,$pid,$ReceiverMail,$pageUrl,$ReplyMail,$FromName,$ReplyName,$FromMail,$cc,$bcc,$body,$subject,$old_activation_key = '', $isFrontend = false, &$details = null){

        $details = array('message'=>'Resend confirmation mail could not be sent.');
        if($isFrontend && empty($old_activation_key)){return false;}
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
    ORDER BY id DESC
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

        if(!cg1l_acquire_registry_activation_lock($old_activation_key)){return false;}

        $oldUserAccountEntries = $wpdb->get_results(
            $wpdb->prepare("SELECT Field_Type, Field_Content, wp_user_id, Tstamp FROM $tablenameCreateUserEntries WHERE activation_key=%s",$old_activation_key)
        );

        if(empty($oldUserAccountEntries)){
            return false;
        }

        $storedEmails = array();
        foreach($oldUserAccountEntries as $entry){
            if($entry->Field_Type==='main-mail' || $entry->Field_Type==='unconfirmed-mail'){
                $storedEmails[] = sanitize_email(strtolower($entry->Field_Content));
            }
        }
        $storedEmails = array_values(array_unique($storedEmails));
        if(count($storedEmails)!==1 || !is_email($storedEmails[0])){return false;}
        // Never let the frontend select the recipient, even with a valid activation key.
        $ReceiverMail = $storedEmails[0];

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

        $reserved = cg1l_reserve_confirmation_mail($ReceiverMail);
        if(is_wp_error($reserved)){
            $details = array('code'=>$reserved->get_error_code(),'message'=>$reserved->get_error_message());
            $rateData = $reserved->get_error_data();
            if(is_array($rateData)){$details = array_merge($details,$rateData);}
            return false;
        }

        $password = wp_generate_password( 32, true, true );

        $time = time();

        $new_activation_key = md5($time . $password);

        if(!cg1l_acquire_registry_activation_lock($new_activation_key)){return false;}

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

        $newWpId = 0;
        $oldWpKey = '';
        if(!empty($resolvedUnconfirmedUser)){
            $newWpId = absint($resolvedUnconfirmedUser['wp_user_id']);
            $oldWpKey = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE ID = %d",$newWpId));
            $bound = $wpdb->update($tablenameCreateUserEntries,
                array('wp_user_id'=>$newWpId,'Field_Content'=>$ReceiverMail),
                array('activation_key'=>$new_activation_key,'Field_Type'=>'unconfirmed-mail'),array('%d','%s'),array('%s','%s'));
            if($bound===false || !in_array($oldWpKey,array($old_activation_key,'cg-key---'.$old_activation_key),true)){
                $wpdb->delete($tablenameCreateUserEntries,array('activation_key'=>$new_activation_key),array('%s'));
                return false;
            }
            $updated = $wpdb->update($wpdb->users,array('user_activation_key'=>$new_activation_key),
                array('ID'=>$newWpId,'user_activation_key'=>$oldWpKey),array('%s'),array('%d','%s'));
            clean_user_cache($newWpId);
            if($updated!==1){
                $wpdb->delete($tablenameCreateUserEntries,array('activation_key'=>$new_activation_key),array('%s'));
                return false;
            }
        }

        $TextEmailConfirmation = str_ireplace($posUrl, $pageUrlForEmail . "cgkey=$new_activation_key#cg_activation", $resolved_body);

        // Build headers (From / Reply-To / CC / BCC)
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $mailAddresses = cg_add_mail_from_reply_headers($headers,$FromName,$FromMail,$ReplyMail,$ReplyName);
        $headers = $mailAddresses['headers'];
        $FromMail = $mailAddresses['from'];
        $ReplyMail = $mailAddresses['reply_to'];

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
            $wpdb->delete($tablenameCreateUserEntries,array('activation_key'=>$old_activation_key),array('%s'));
            $details = array('activationKey'=>$new_activation_key);
            $sent = true;
            $WpUserId = 0;
            cg_save_sent_mail($gid,$pid,$WpUserId,$ReceiverMail,$ReplyName,$ReplyMail,$FromName,$FromMail,$cc,$bcc,$subject,$TextEmailConfirmation, 'registry-resend-'.$suffixSource);
        }else{
            if($newWpId){
                // Restore only our own replacement, never a later password reset/confirmation.
                $restored = $wpdb->update($wpdb->users,array('user_activation_key'=>$oldWpKey),
                    array('ID'=>$newWpId,'user_activation_key'=>$new_activation_key),array('%s'),array('%d','%s'));
                clean_user_cache($newWpId);
                if($restored===false){return false;}
            }
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
        $RegMailCC = (empty($proOptions->RegMailCC)) ? '' : $proOptions->RegMailCC;
        $ccPlain = $RegMailCC;
        $RegMailBCC = (empty($proOptions->RegMailBCC)) ? '' : $proOptions->RegMailBCC;
        $bccPlain = $RegMailBCC;
        $FromName = html_entity_decode(strip_tags($proOptions->RegMailAddressor));
        $ReplyName = $FromName;
        $WpUserId = 0;
        $ReplyMail = isset($proOptions->RegMailReply) ? $proOptions->RegMailReply : '';
        $FromMail = isset($proOptions->RegMailFrom) ? $proOptions->RegMailFrom : $ReplyMail;

        $headers = array();
        $mailAddresses = cg_add_mail_from_reply_headers($headers,$FromName,$FromMail,$ReplyMail);
        $headers = $mailAddresses['headers'];
        $FromMail = $mailAddresses['from'];
        $ReplyMail = $mailAddresses['reply_to'];

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
