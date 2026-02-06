<?php
if (!defined('ABSPATH')) {
    exit;
}

    $_POST = cg1l_sanitize_post($_POST);

    global $wpdb;

    $tablenameCreateUserForm = $wpdb->prefix . "contest_gal1ery_create_user_form";
    $tablenameCreateUserEntries = $wpdb->prefix . "contest_gal1ery_create_user_entries";
    $tablename_options = $wpdb->prefix . "contest_gal1ery_options";
    $tablenameProOptions = $wpdb->prefix . "contest_gal1ery_pro_options";
    $tablenameWpUsers = $wpdb->base_prefix . "users";
    $tablenameWpUserMeta = $wpdb->base_prefix . "usermeta";
    $tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
    $tablename_registry_and_login_options = $wpdb->prefix."contest_gal1ery_registry_and_login_options";


    if(intval($galleryDbVersion)>=14){
        $proOptions = $wpdb->get_row("SELECT * FROM $tablenameProOptions WHERE GeneralID = '1'");
    }else{
        $proOptions = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablenameProOptions WHERE GalleryID = %d",[$GalleryID]));
    }

    if(intval($galleryDbVersion)>=14){
        $RegistryUserRole = $wpdb->get_var( "SELECT RegistryUserRole FROM $tablename_registry_and_login_options WHERE GeneralID = 1");
    }else{
        $RegistryUserRole = $wpdb->get_var($wpdb->prepare("SELECT RegistryUserRole FROM $tablename_options WHERE id=%d",[$GalleryID]));
    }

    include(__DIR__ . "/../../../../../check-language.php");

    $cg_check = $_POST['cg_Fields'];

    /*echo "<pre>";
    print_r($cg_check);
    echo "</pre>";*/

    $activation_key = md5(time() . wp_generate_password( 32, true, true ));

    // Validierung und Erstellung von Activation Key
    foreach ($cg_check as $key => $value) {

        if ($value["Field_Type"] == "password") {
            $password = sanitize_text_field($value["Field_Content"]);
        }

        if ($value["Field_Type"] == "password-confirm") {
            $passwordConfirm = sanitize_text_field($value["Field_Content"]);
        }

    }

    if (!empty($password) && !empty($passwordConfirm) && $password != $passwordConfirm) {
        ?>
        <script  data-cg-processing="true">
            var cg_error = "Please don't manipulate the registry Code:221";
            cgJsClass.gallery.vars.$regFormContainer.find('#cg_registry_manipulation_error').text(cg_error).removeClass("cg_hide");
            console.log(cg_error);
        </script>
        <?php
        die;
    }

    $Subject = contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->RegMailSubject);

    $posUrl = '';
    $posPin = '';
    $pin = '';

    if($cg_users_pin){
        // test here
        $posPin = '$pin$';
        $TextEmailConfirmation = contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->TextPinConfirmation);
        if(empty($TextEmailConfirmation)){// because of update 28.1.0, mighty be empty
            $TextEmailConfirmation = 'Complete your registration by using the PIN below: <br/><br/> $pin$';
        }
        if (stripos($TextEmailConfirmation, $posPin) === false) {
            ?>
            <script  data-cg-processing="true">
                var cg_error = "Confirmation PIN for e-mail can't be provided. Please contact Administrator.";
                var cg_registry_manipulation_error = cgJsClass.gallery.vars.$regFormContainer.find('#cg_registry_manipulation_error').text(cg_error).removeClass("cg_hide");
                console.log(cg_error);
            </script>
            <?php
            die;
        }
    }else{
        $posUrl = '$regurl$';
        $TextEmailConfirmation = contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->TextEmailConfirmation);
        if (stripos($TextEmailConfirmation, $posUrl) === false) {
            ?>
            <script  data-cg-processing="true">
                var cg_error = "Confirmation URL for e-mail can't be provided. Please contact Administrator.";
                var cg_registry_manipulation_error = cgJsClass.gallery.vars.$regFormContainer.find('#cg_registry_manipulation_error').text(cg_error).removeClass("cg_hide");
            </script>
            <?php
            die;
        }
    }
    //var_dump('$password');
    //var_dump($password);
    if(!empty($password)){
       $password = wp_hash_password($password);
        //var_dump('$passwordHashed');
        //var_dump($password);
    }elseif(!empty($passwordConfirm)){
       $password = wp_hash_password($passwordConfirm);
    }else{
       $password = wp_hash_password(wp_generate_password( 32, true, true ));
    }

    // Validierung und Erstellung von Activation Key --- ENDE

    // Einf�gen von Werten mit Kennzeichnung durch Activation Key zur sp�teren Wiederfindung

    $Tstamp = time();
    $attach_id = 0;

    $Version = cg_get_version_for_scripts();

    $GeneralID = 0;

    if(intval($galleryDbVersion)>=14){
        $GalleryID = 0;
        $GeneralID = 1;
    }

    foreach ($cg_check as $key => $value) {

        $Form_Input_ID = sanitize_text_field($value["Form_Input_ID"]);
        $Field_Type = sanitize_text_field($value["Field_Type"]);

        $Field_Order = sanitize_text_field($value["Field_Order"]);
        $Field_Content = cg1l_sanitize_method((isset($value["Field_Content"]) ? $value["Field_Content"] : ''));

        if ($value["Field_Type"] == "password") {
            $Field_Content = $password;
        }
        if ($value["Field_Type"] == "password-confirm") {
            $Field_Content = $password;
        }
        if ($value["Field_Type"] == "profile-image") {
            if(!empty($_FILES) AND !empty($_FILES['cg_input_image_upload_file']) AND !empty($_FILES['cg_input_image_upload_file']['tmp_name']) AND !empty($_FILES['cg_input_image_upload_file']['tmp_name'][0])){
                $Field_Content = cg_registry_add_profile_image('cg_input_image_upload_file',0,true,true);
                $attach_id = $Field_Content;
            }else{
                $Field_Content = '';
            }
        }

        $Checked = 0;
        if ($Field_Type == 'user-check-agreement-field') {
            if ($Field_Content == 'checked') {
                $Checked = 1;
            } else {
                $Checked = 0;
            }
            // insert original checked field_content to show later!
            $Field_Content = $wpdb->get_row($wpdb->prepare("SELECT Field_Name, Field_Content, Required FROM $tablenameCreateUserForm WHERE id = %d",[$Form_Input_ID]));

            $RequiredString =  ($Field_Content->Required==1) ? 'yes' : 'no' ;
            $Field_Content = $Field_Content->Field_Name . ' --- required:' . $RequiredString . ' --- ' . $Field_Content->Field_Content;// get both in this case name and content for better documentation
        }

        $wpdb->query($wpdb->prepare(
            "
        INSERT INTO $tablenameCreateUserEntries
        (id, GalleryID, wp_user_id, f_input_id, Field_Type,
        Field_Content, activation_key, Checked, Version,GeneralID,Tstamp)
        VALUES (%s,%d,%d,%d,%s,
        %s,%s,%d,%s,%d,%d)
    ",
            '', $GalleryID, 0, $Form_Input_ID, $Field_Type,
            $Field_Content, $activation_key, $Checked, $Version,$GeneralID,$Tstamp
        ));

    }
    if($cg_users_pin){
        $pin = random_int(1000, 9999);
        $Subject = str_ireplace($posPin, $pin, contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->RegPinSubject));
        $pinHashed = password_hash($pin,PASSWORD_DEFAULT);
        $wpdb->query($wpdb->prepare(
            "
        INSERT INTO $tablenameCreateUserEntries
        (id, GalleryID, wp_user_id, f_input_id, Field_Type,
        Field_Content, activation_key, Checked, Version,GeneralID,Tstamp)
        VALUES (%s,%d,%d,%d,%s,
        %s,%s,%d,%s,%d,%d)
    ",
            '', 0, 0, 0, 'activation-pin',
            $pinHashed, $activation_key, 0, $Version,$GeneralID,$Tstamp
        ));
    }

    $wp_mail_result = cg1l_send_registration_mail($proOptions,$GalleryID,$cg_users_pin,$cg_main_mail,$Subject, $TextEmailConfirmation, $activation_key, $posPin, $pin, $posUrl, $currentPageUrl);

    if (!$wp_mail_result) {
        ?>
        <script  data-cg-processing="true">
            var cg_error = "Failed sending mail, please contact administrator";
            var cg_registry_manipulation_error = cgJsClass.gallery.vars.$regFormContainer.find('#cg_registry_manipulation_error').text(cg_error).removeClass("cg_hide");
            console.log(cg_error);
        </script>
        <?php
        die;
    }

    // $activation_key has definetely to be set to run it here!!!
    if($proOptions->RegMailOptional==1  && !empty($activation_key) && empty($cg_users_pin)){

        if(!empty($cg_main_nick_name)){
            $display_name=$cg_main_nick_name;
            $user_nicename=$cg_main_nick_name;
        }else{
            $display_name=$cg_main_user_name;
            $user_nicename=$cg_main_user_name;
        }

        $user_login=$cg_main_user_name;
        $user_email=$cg_main_mail;

        // this type of key not required anymore, logic improved
        //$activation_key_for_wp_users_table = $activation_key.'-unconfirmed';

        if(intval($galleryDbVersion)>=14){
            //$activation_key_for_wp_users_table = 'cg-key---'.$activation_key.'-unconfirmed';
        }

        $user_registered = current_time( 'mysql' );

        /*$wpdb->query( $wpdb->prepare(
            "
									INSERT INTO $tablenameWpUsers
									( id, user_login, user_pass, user_nicename, user_email, user_url,
									user_registered, user_activation_key, user_status, display_name)
									VALUES (%s,%s,%s,%s,%s,%s,
									%s,%s,%d,%s)
								",
            '',$user_login,$password,$user_nicename,$user_email,'',
            $user_registered,$activation_key_for_wp_users_table,'',$display_name
        ) );*/

        $newWpId = cg1l_wp_insert_user($user_login,$user_nicename,$user_email,$user_registered,$display_name);

        if (!is_wp_error($newWpId) && !empty($newWpId) && is_numeric($newWpId) && (int)$newWpId > 0) {// important to check, otherwise unpredicted handling

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
                    'user_pass' => $password,
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

            // has to be done again, because deleted during wp_update_user
            $wpdb->update(
                $wpdb->users,
                [
                    'user_activation_key' => $activation_key,
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

            if(intval($galleryDbVersion)>=14){
                cg_update_user_meta_when_register_and_delete_user_entries( $newWpId, $activation_key, $user_email ) ;
            }

            // for unconfirmed users to recognize in queries in unconfirmed-users.php
            $wpdb->query($wpdb->prepare(
                "
        INSERT INTO $tablenameCreateUserEntries
        (id, GalleryID, wp_user_id, f_input_id, Field_Type,
        Field_Content, activation_key, Checked, Version,GeneralID,Tstamp)
        VALUES (%s,%d,%d,%d,%s,
        %s,%s,%d,%s,%d,%d)
    ",
                '', $GalleryID, 0, 0, 'unconfirmed-mail',
                $user_email, $activation_key, 0, $Version, 1, $Tstamp
            ));

            if(!empty($attach_id)){
                cg_registry_add_profile_image('cg_input_image_upload_file',$newWpId,false,false,$attach_id);
            }

            //wp_set_auth_cookie( $newWpId,true );// will be done ajax

            $addOn = 'cg_gallery_id_registry='.$GalleryID.'&cg_login_user_after_registration=true&cg_activation_key='.$activation_key;

            $url = (strpos($currentPageUrl, '?')) ? $currentPageUrl . '&' .$addOn : $currentPageUrl . '?' .$addOn;
            // if RegMailOptional and direct login after registration!!!
            ?>
            <script  data-cg-processing="true" data-cg-success="true">
                var result = cgJsClass.gallery.registry.functions.loginUserByKey(jQuery,0,<?php echo json_encode($activation_key);?>);
                if(result){
                    cgJsClass.gallery.vars.$regFormContainer.find('#cg_check_mail_name_value').val(0);// then success and can be reloaded, val(1) will be set when form submit
                    var url = <?php echo json_encode($url);?>;
                    window._cgLocationUrl = url;
                }else{
                    var cg_error = "Login not possible. Please contact administrator.";
                    cgJsClass.gallery.vars.$regFormContainer.find('#cg_registry_manipulation_error').text(cg_error).removeClass("cg_hide");
                    console.log(cg_error);
                }
            </script>
            <?php
            die;
        }else{
            ?>
            <script  data-cg-processing="true">
                var cg_error = "User could not be created when login instantly after registration.";
                var cg_registry_manipulation_error = cgJsClass.gallery.vars.$regFormContainer.find('#cg_registry_manipulation_error').text(cg_error).removeClass("cg_hide");
                console.log(cg_error);
            </script>
            <?php
            die;
        }

    }else{

        if($cg_users_pin){
            ?>
            <script  data-cg-processing="true" data-cg-success="true">
                cgJsClass.gallery.vars.activationKey = <?php echo json_encode($activation_key);?>;
            </script>
            <?php
            die;
        }else{
            $addOn = 'cg_gallery_id_registry='.$GalleryID.'&cg_forward_user_after_reg=true';
            $url = (strpos($currentPageUrl, '?')) ? $currentPageUrl . '&' .$addOn : $currentPageUrl . '?' .$addOn;
            // show only ForwardAfterRegText, no login
            ?>
            <script  data-cg-processing="true" data-cg-success="true">
                cgJsClass.gallery.vars.$regFormContainer.find('#cg_check_mail_name_value').val(0);// then success and can be reloaded, val(1) will be set when form submit
                var url = <?php echo json_encode($url);?>;
                window._cgLocationUrl = url;
            </script>
            <?php
            die;
        }

    }

?>