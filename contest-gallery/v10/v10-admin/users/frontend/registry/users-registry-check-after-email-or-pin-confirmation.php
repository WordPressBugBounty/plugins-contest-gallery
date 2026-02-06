<?php
if (!defined('ABSPATH')) {
    exit;
}

$tablenameWpUsers = $wpdb->base_prefix . "users";
$tablenameWpUserMeta = $wpdb->base_prefix . "usermeta";
$tablenameCreateUserEntries = $wpdb->prefix . "contest_gal1ery_create_user_entries";

if(!empty($cg_users_pin_from_email_check)){
    $cgkey = sanitize_text_field(wp_unslash($_POST["cglActivationKey"]));
}else{
    $cgkey = sanitize_text_field(wp_unslash($_GET["cgkey"]));
}

$cgkeyForWpUserTable = $cgkey;

if(strpos($cgkey,'-confirmed')!==false OR strpos($cgkey,'-unconfirmed')!==false){// then somebody must try to manipulate
    return;
}

$userAccountEntries = $wpdb->get_results( $wpdb->prepare("SELECT Field_Type, Field_Content, Tstamp FROM $tablenameCreateUserEntries WHERE activation_key=%s", $cgkey) );

include (__DIR__.'/../../../../../check-language-general.php');

// then registration was done and user should be directly logged and created account without waiting for mail
if (count($userAccountEntries)) {
    $mainMail = '';

    foreach($userAccountEntries as $entry){
        if($entry->Field_Type == 'main-mail' || $entry->Field_Type == 'unconfirmed-mail'){
            $mainMail = $entry->Field_Content;
            break;
        }
    }

    if(empty($mainMail)){
        echo "<div id='cg_activation'  class='mainCGdivUploadForm mainCGdivUploadFormStatic $FeControlsStyleRegistry $BorderRadiusRegistry'>";
        echo "<p>Mail not found</p>";
        echo "</div>";
        ?>
        <script defer>
            setTimeout(function (){
                jQuery("html, body").animate({ scrollTop: jQuery('#cg_activation').offset().top-60}, 0);
            },100);
        </script>
        <?php
        return;
    }

    $currentTime = time();
    $firstRow = $userAccountEntries[0];
    $regTime = $firstRow->Tstamp;

    $pinHash = '';

    foreach($userAccountEntries as $entry){
        if($entry->Field_Type == 'activation-pin'){
            if(!empty($_GET['cgkey'])){
                $cg_users_pin_from_email_check = 1;
            }else{
                $pinHash = $entry->Field_Content;
            }
            break;
        }
    }

    if($pinHash){
        $cgl_pin = !empty($_POST['cgl_pin']) ? substr($_POST['cgl_pin'], 0, 4) : '';
        $pinCheck = password_verify($cgl_pin, $pinHash);

        if ($currentTime>($regTime+$PinExpiry)) {
            ?>
            <script  data-cg-processing="true" >
                cgJsClass.gallery.vars.pinMessage = 'expired';
            </script>
            <?php
            die;
            return;
        }
        if (!$pinCheck) {
            ?>
            <script  data-cg-processing="true">
                cgJsClass.gallery.vars.pinMessage = 'wrong-pin';
            </script>
            <?php
            die;
            return;
        }
    }else{
        if($currentTime>($regTime+$ConfirmExpiry)){
            $page_id = get_the_ID();
            echo "<div id='cglExpired' class='cglExpired $FeControlsStyleRegistry $BorderRadiusRegistry' ><input type='hidden' class='cglEmailSent' id='cglEmailSent' value='$language_EmailSent' >$language_ConfirmationLinkExpired<button id='cglResendRegMailButton' data-cgl-mail='$mainMail' data-cgl-page-id='$page_id' data-cgl-key='$cgkey' class='cglResendRegMailButton'>$language_ResendConfirmationEmail</button></div>";

            ?>

            <script defer>

                setTimeout(function (){
                    jQuery("html, body").animate({ scrollTop: jQuery('#cglExpired').offset().top-60}, 0);
                },100);

            </script>

            <?php

            return;
        }
    }

    $user  = get_user_by( 'email', $mainMail );

    $unconfirmedMail = '';
    foreach ($userAccountEntries as $key => $value) {
        if($value->Field_Type == 'unconfirmed-mail'){
            $unconfirmedMail = $value->Field_Content;
        }
    }

    if ( $user && empty($unconfirmedMail)) {// here is better because check is within activation key expiration time
        echo "<div id='cg_activation'  class='mainCGdivUploadForm mainCGdivUploadFormStatic $FeControlsStyleRegistry $BorderRadiusRegistry'>";
        echo "<p>User is already registered</p>";
        echo "</div>";
        ?>
        <script defer>
            setTimeout(function (){
                jQuery("html, body").animate({ scrollTop: jQuery('#cg_activation').offset().top-60}, 0);
            },100);
        </script>
        <?php
        return;
    }



    if($unconfirmedMail){// if regmailoptional option was used
        $newWpId = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM $tablenameWpUsers WHERE user_email = %s",
                $unconfirmedMail
            )
        );
        if(empty($newWpId)){// theoretically should never be the case
            echo "<p style='font-weight: bold; text-align: center;'>User already deleted</p>";
            return;
        }
        if(intval($galleryDbVersion)>=14){
            $cgkeyForWpUserTable = 'cg-key---'.$cgkey;
        }
        // '-confirmed' was added in update 10.9.8.8.0
        $wpdb->query($wpdb->prepare(
            "
            UPDATE $tablenameWpUsers SET user_activation_key = %s WHERE ID = %s
        ",
            $cgkeyForWpUserTable."-confirmed",$unconfirmedMail
        ));
        cg1l_delete_unconfirmed_user($unconfirmedMail);
        include (__DIR__.'/users-registry-render-confirmation-or-signin.php');
        return;
    }

    $i = 0;
    $fieldRow = '';

    $user_nicename = '';
    $display_name = '';

    $user_login = '';
    $user_pass = '';
    $user_email = '';

   // var_dump('$userAccountEntries');
   // var_dump($userAccountEntries);die;

    foreach ($userAccountEntries as $key => $value) {

        if($value->Field_Type == 'main-mail'){
            $mainMail = $value->Field_Content;
        }

        foreach ($value as $key1 => $value1) {
            $i++;
            if ($value1 == "password") {
                $fieldRow = "password";
                continue;
            }
            if ($fieldRow == "password") {
                $user_pass = $value1;
                $fieldRow = '';
                continue;
            }
            if ($fieldRow == "password-confirm") {
                $user_pass = $value1;
                $fieldRow = '';
                continue;
            }
            if ($value1 == "main-mail") {
                $fieldRow = "main-mail";
                continue;
            }
            if ($fieldRow == "main-mail") {
                $user_email = $value1;
                $fieldRow = '';
                continue;
            }
            if ($value1 == "main-user-name") {
                $fieldRow = "main-user-name";
                continue;
            }
            if ($fieldRow == "main-user-name") {
                $user_login = $value1;
                $fieldRow = '';
                continue;
            }
            if ($value1 == "main-nick-name") {
                $fieldRow = "main-nick-name";
                continue;
            }
            if ($fieldRow == "main-nick-name") {
                $user_nicename = $value1;
                $display_name = $value1;
                $fieldRow = '';
            }
        }

    }


    $cgkeyForWpUserTable = $cgkey;

    //var_dump('$user_pass');
    //var_dump($user_pass);

    if (
        (empty($cg_users_pin_from_email_check) && !empty($user_login) && !empty($user_email) && !empty($user_pass))
        ||
        (!empty($cg_users_pin_from_email_check) && !empty($user_email))
    ) {
        if(!isset($cg_users_pin_from_email_check)){
            $cg_users_pin_from_email_check = 0;
        }

        $user_registered = current_time( 'mysql' );

        if(intval($galleryDbVersion)>=14){
            $cgkeyForWpUserTable = 'cg-key---'.$cgkey;
        }

        if(empty($user_login)){
            $user_login = cg1l_generate_unique_login_from_email($user_email);
        }

        if(empty($user_nicename)){// happens only for cg_users_pin
            $user_nicename = cg1l_generate_unique_nicename();
            $display_name = $user_nicename;
        }

        // $cgkeyForWpUserTable.'-confirmed' '-confirmed' was added update 10.9.8.8.0
        /*$wpdb->query($wpdb->prepare(
            "
                    INSERT INTO $tablenameWpUsers
                    ( id, user_login, user_pass, user_nicename, user_email, user_url,
                    user_registered, user_activation_key, user_status, display_name)
                    VALUES (%s,%s,%s,%s,%s,%s,
                    %s,%s,%d,%s)
                ",
            '', $user_login, $user_pass, $user_nicename, $user_email, '',
            $user_registered, $cgkeyForWpUserTable.'-confirmed', '', $display_name
        ));*/

        $newWpId = cg1l_wp_insert_user($user_login,$user_nicename,$user_email,$user_registered,$display_name);

        if (!is_wp_error($newWpId) && !empty($newWpId) && is_numeric($newWpId) && (int)$newWpId > 0) {// important to check, otherwise unpredicted handling

            if(!empty($cg_users_pin_from_email_check) && empty($user_pass)){
               // var_dump('generate user pass');
                $user_pass = wp_hash_password(wp_generate_password( 32, true, true ));
            }

         //   var_dump('$user_pass before set');
           // var_dump($user_pass);

            // set role and nickname here, then update with already hashed password
            wp_update_user([
                'ID' => $newWpId,
                'role' => $RegistryUserRole,
                'nickname' => $display_name
            ]);

            // has to be done here, because unhashed dummy password has to be set before, do not wp_update_user user after that
            // wp_set_password expects plain password, but already not available here anymore
            $wpdb->update(
                $wpdb->users,
                [
                    'user_pass' => $user_pass,
                ],
                [
                    'ID' => $newWpId,
                ],
                [
                    '%s',
                ],
                [
                    '%d',
                ]
            );

            // do this before updating $tablenameCreateUserEntries and deleting all fields, in cg_update_user_meta_when_register_and_delete_user_entries entries will be deleted also! !!!!
            $attach_id = $wpdb->get_var("SELECT Field_Content FROM $tablenameCreateUserEntries WHERE Field_Type = 'profile-image' AND activation_key = '".$cgkey."' LIMIT 1");

            if(intval($galleryDbVersion)>=14){
                cg_update_user_meta_when_register_and_delete_user_entries( $newWpId, $cgkey, $user_email, $cg_users_pin_from_email_check ) ;
            }

            if(intval($galleryDbVersion)<14){
                $wpdb->query($wpdb->prepare(
                    "
                            DELETE FROM $tablenameCreateUserEntries WHERE (Field_Type = %s OR Field_Type = %s OR Field_Type = %s OR Field_Type = %s) AND activation_key = %s
                        ",
                    "password", "password-confirm", "main-user-name", "main-mail", $cgkey
                ));
            }

            // has to be done again, because deleted during wp_update_user
            // in this case user_activation_key is just for orientation, not really required anymore
            $wpdb->update(
                $wpdb->users,
                [
                    'user_activation_key' => $cgkeyForWpUserTable.'-confirmed',
                ],
                [
                    'ID' => $newWpId,
                ],
                [
                    '%s',
                ],
                [
                    '%d',
                ]
            );

            if(!empty($attach_id)){
                cg_registry_add_profile_image('cg_input_image_upload_file',$newWpId,false,false,$attach_id);
            }

            if($pinHash){// login here
                //wp_set_auth_cookie( $newWpId,true );// will be done ajax
                $cgGetLoggedInFrontendUserKey = wp_hash_password(wp_generate_password( 32, true, true ));
                update_user_meta( $newWpId, 'cgGetLoggedInFrontendUserKey', $cgGetLoggedInFrontendUserKey);
                ?>
                <script  data-cg-processing="true" data-cg-success="true">
                    cgJsClass.gallery.vars.pinMessage = 'success';
                    cgJsClass.gallery.vars.pinVerified = true;
                    cgJsClass.gallery.vars.activationKeyConfirmed = <?php echo json_encode($cgkeyForWpUserTable.'-confirmed');?>;
                    cgJsClass.gallery.vars.cgGetLoggedInFrontendUserKey = <?php echo json_encode($cgGetLoggedInFrontendUserKey);?>;
                    cgJsClass.gallery.vars.cgJustLoggedInWpUserId = <?php echo json_encode($newWpId);?>;
                </script>
                <?php
                die;
            }else{
                include (__DIR__.'/users-registry-render-confirmation-or-signin.php');
            }

        }else{

            if($pinHash){// login here
                ?>
                <script  data-cg-processing="true" >
                    cgJsClass.gallery.vars.pinMessage = 'user-not-created';
                </script>
                <?php
                die;
            }else{
                echo "<div id='cg_activation'  class='mainCGdivUploadForm mainCGdivUploadFormStatic $FeControlsStyleRegistry $BorderRadiusRegistry'>";

                echo "<p>User not created. Please contact administrator.</p>";

                echo "</div>";

                ?>

                <script defer>

                    setTimeout(function (){
                        jQuery("html, body").animate({ scrollTop: jQuery('#cg_activation').offset().top-60}, 0);
                    },100);

                    window.history.replaceState({}, document.title, location.protocol + '//' + location.host + location.pathname);


                </script>

                <?php
            }

        }



    }else{
        if($pinHash){// login here
            ?>
            <script  data-cg-processing="true" >
                cgJsClass.gallery.vars.pinMessage = 'data-deleted';
            </script>
            <?php
            die;
        }else{
            echo "<div id='cg_activation'  class='mainCGdivUploadForm mainCGdivUploadFormStatic $FeControlsStyleRegistry $BorderRadiusRegistry'>";

            echo "<p>E-mail confirmation. Data not found. Please contact administrator.</p>";

            echo "</div>";

            ?>

            <script defer>

                setTimeout(function (){
                    jQuery("html, body").animate({ scrollTop: jQuery('#cg_activation').offset().top-60}, 0);
                },100);

                window.history.replaceState({}, document.title, location.protocol + '//' + location.host + location.pathname);


            </script>

            <?php
        }
    }




} else {

    if (!empty($cg_users_pin_from_email_check)) {
        ?>
        <script  data-cg-processing="true">
            cgJsClass.gallery.vars.pinMessage = 'activation-key-not-found';
        </script>
        <?php
    }else{
        echo "<div id='cg_activation'  class='mainCGdivUploadForm mainCGdivUploadFormStatic $FeControlsStyleRegistry $BorderRadiusRegistry'>";
        echo "<p>$language_ActivationKeyNotFound</p>";
        echo "</div>";
        ?>
        <script>
            setTimeout(function (){
                jQuery("html, body").animate({ scrollTop: jQuery('#cg_activation').offset().top-60}, 0);
            },100);
            window.history.replaceState({}, document.title, location.protocol + '//' + location.host + location.pathname);
        </script>
        <?php
    }

}



?>