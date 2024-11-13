<?php

$tablenameWpUsers = $wpdb->base_prefix . "users";
$tablenameCreateUserForm = $wpdb->prefix . "contest_gal1ery_create_user_form";

$cgResetPasswordWpUserID = absint(sanitize_text_field($_REQUEST['cgResetPasswordWpUserID']));

if(empty($cgResetPasswordWpUserID)){
    ?>
    <script data-cg-processing="true">
        var cg_language_LostPasswordUrlIsNotValidAnymore = document.getElementById("cg_language_LostPasswordUrlIsNotValidAnymore").value;
        var cgLostPasswordUrlIsNotValidAnymore = document.getElementById('cgLostPasswordUrlIsNotValidAnymore');
        cgLostPasswordUrlIsNotValidAnymore.innerHTML = cg_language_LostPasswordUrlIsNotValidAnymore;
        cgLostPasswordUrlIsNotValidAnymore.classList.remove('cg_hide');
    </script>
    <?php
    return;
}

$cgWpData = $wpdb->get_row("SELECT ID, user_login, user_pass FROM $tablenameWpUsers WHERE ID = '".$cgResetPasswordWpUserID."'");

if(empty($cgWpData)){
    ?>
    <script data-cg-processing="true">
        var cg_language_LostPasswordUrlIsNotValidAnymore = document.getElementById("cg_language_LostPasswordUrlIsNotValidAnymore").value;
        var cgLostPasswordUrlIsNotValidAnymore = document.getElementById('cgLostPasswordUrlIsNotValidAnymore');
        cgLostPasswordUrlIsNotValidAnymore.innerHTML = cg_language_LostPasswordUrlIsNotValidAnymore;
        cgLostPasswordUrlIsNotValidAnymore.classList.remove('cg_hide');
    </script>
    <?php
    return;
}

$wpUserID = $cgWpData->ID;

$cgResetPasswordTimestamp = intval(get_the_author_meta( 'cgResetPasswordTimestamp', $wpUserID) );
if(empty($cgResetPasswordTimestamp) || $cgResetPasswordTimestamp+(60*5)<time()){// 5 minutes valid (5 minutes for link and 5 minutes for entering new password)
	delete_user_meta($wpUserID,'cgResetPasswordTimestamp');
    ?>
    <script data-cg-processing="true">
        var cg_language_LostPasswordUrlIsNotValidAnymore = document.getElementById("cg_language_LostPasswordUrlIsNotValidAnymore").value;
        var cgLostPasswordUrlIsNotValidAnymore = document.getElementById('cgLostPasswordUrlIsNotValidAnymore');
        cgLostPasswordUrlIsNotValidAnymore.innerHTML = cg_language_LostPasswordUrlIsNotValidAnymore;
        cgLostPasswordUrlIsNotValidAnymore.classList.remove('cg_hide');
    </script>
    <?php
    return;
}

$cgLostPasswordCurrent = sanitize_text_field($_REQUEST['cgLostPasswordCurrent']);
$cgLostPasswordNew = sanitize_text_field($_REQUEST['cgLostPasswordNew']);
$cgLostPasswordNewRepeat = sanitize_text_field($_REQUEST['cgLostPasswordNewRepeat']);

$passwordField = $wpdb->get_row("SELECT Max_Char, Min_Char FROM $tablenameCreateUserForm WHERE GeneralID = '1' && Field_Type = 'password' ORDER BY Field_Order ASC LIMIT 1");

if(empty($cgLostPasswordCurrent) || empty($cgLostPasswordNew) || strlen($cgLostPasswordNew)<$passwordField->Min_Char){
	?>
    <script data-cg-processing="true">
        var language_MinAmountOfCharacters = document.getElementById("cg_min_characters_text").value;
        var cgLostPasswordPasswordMinChar = document.getElementById('cgLostPasswordPasswordMinChar');
        cgLostPasswordPasswordMinChar.innerHTML = language_MinAmountOfCharacters+' : '+<?php echo json_encode($passwordField->Min_Char); ?>;
        cgLostPasswordPasswordMinChar.classList.remove('cg_hide');
    </script>
	<?php
	return;
}

if(strlen($cgLostPasswordNew)>$passwordField->Max_Char){
	?>
    <script data-cg-processing="true">
        var language_MaxAmountOfCharacters = document.getElementById("cg_max_characters_text").value;
        var cgLostPasswordPasswordMaxChar = document.getElementById('cgLostPasswordPasswordMaxChar');
        cgLostPasswordPasswordMaxChar.innerHTML = language_MaxAmountOfCharacters+' : '+<?php echo json_encode($passwordField->Max_Char); ?>;
        cgLostPasswordPasswordMaxChar.classList.remove('cg_hide');
    </script>
	<?php
	return;
}


require_once(ABSPATH ."wp-load.php");
$cgCheckPw = (wp_check_password($cgLostPasswordCurrent, $cgWpData->user_pass));

if($cgCheckPw==false){
    ?>
    <script data-cg-processing="true">
        var cg_language_LoginAndPasswordDoNotMatch = document.getElementById("cg_language_LoginAndPasswordDoNotMatch").value;
        var cgLostPasswordCurrentValidationMessage = document.getElementById('cgLostPasswordCurrentValidationMessage');
        cgLostPasswordCurrentValidationMessage.innerHTML = cg_language_LoginAndPasswordDoNotMatch;
        cgLostPasswordCurrentValidationMessage.classList.remove('cg_hide');
    </script>
    <?php
    return;
}

if($cgLostPasswordNew!=$cgLostPasswordNewRepeat){
        ?>
        <script data-cg-processing="true">
        var cg_language_PasswordsDoNotMatch = document.getElementById("cg_language_PasswordsDoNotMatch").value;
        var cgLostPasswordPasswordsDoNotMatch = document.getElementById('cgLostPasswordPasswordsDoNotMatch');
        cgLostPasswordPasswordsDoNotMatch.innerHTML = cg_language_PasswordsDoNotMatch;
        cgLostPasswordPasswordsDoNotMatch.classList.remove('cg_hide');
        </script>
        <?php
        return;
}


        $user_pass = wp_hash_password($cgLostPasswordNew);

        // set user id here by activation key, because created!!!
        $wpdb->update(
            "$tablenameWpUsers",
            array('user_pass' => $user_pass),
            array('ID' => $wpUserID),
            array('%s'),
            array('%d')
        );

        delete_user_meta($wpUserID,'cgLostPasswordMailTimestamp');

        ?>
        <script data-cg-processing="true">
            var mainCGdivLostPasswordResetContainer = document.getElementById('mainCGdivLostPasswordResetContainer');
            mainCGdivLostPasswordResetContainer.classList.add('cg_hide');
            var mainCGdivResetPasswordSuccessfullyExplanation = document.getElementById('mainCGdivResetPasswordSuccessfullyExplanation');
            mainCGdivResetPasswordSuccessfullyExplanation.classList.remove('cg_hide');
            var mainCGdivLoginFormContainer = document.getElementById('mainCGdivLoginFormContainer');
            mainCGdivLoginFormContainer.classList.remove('cg_hide');
        </script>
        <?php
        return;



return;

?>