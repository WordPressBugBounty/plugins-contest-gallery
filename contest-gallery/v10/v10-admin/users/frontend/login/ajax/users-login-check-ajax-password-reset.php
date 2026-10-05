<?php
if(!defined('ABSPATH')){exit;}

$invalidMessage = 'This password reset link is no longer valid. Please request a new link.';
if(empty($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['cgResetPasswordWpUserID'],$_POST['cgResetPasswordKey']) ||
    !is_string($_POST['cgResetPasswordWpUserID']) || !ctype_digit($_POST['cgResetPasswordWpUserID']) ||
    !is_string($_POST['cgResetPasswordKey']) || !absint($_POST['cgResetPasswordWpUserID'])){
    cg1l_password_flow_error('cgLostPasswordUrlIsNotValidAnymore',$invalidMessage,'cg_language_LostPasswordUrlIsNotValidAnymore');
    return;
}

$wpUserID = absint($_POST['cgResetPasswordWpUserID']);
$key = wp_unslash($_POST['cgResetPasswordKey']);
if(!isset($_POST['cgLostPasswordNew'],$_POST['cgLostPasswordNewRepeat']) ||
    !is_string($_POST['cgLostPasswordNew']) || !is_string($_POST['cgLostPasswordNewRepeat'])){
    cg1l_password_flow_error('cgLostPasswordResetError','Please enter and confirm your new password.');
    return;
}
$password = wp_unslash($_POST['cgLostPasswordNew']);
$confirmation = wp_unslash($_POST['cgLostPasswordNewRepeat']);
$fields = cg1l_password_reset_fields();
if(is_wp_error($fields)){
    cg1l_password_flow_error('cgLostPasswordResetError','The password form is not configured correctly. Please contact the administrator.');
    return;
}
if($password !== $confirmation){
    cg1l_password_flow_error('cgLostPasswordPasswordsDoNotMatch','Passwords do not match.','cg_language_PasswordsDoNotMatch');
    return;
}
$length = cg1l_password_reset_length($password);
$min = max(1,intval($fields['password']->Min_Char),intval($fields['password-confirm']->Min_Char));
$max = min(intval($fields['password']->Max_Char),intval($fields['password-confirm']->Max_Char));
if($length === false || strpos($password,chr(0)) !== false){
    cg1l_password_flow_error('cgLostPasswordResetError','The password contains unsupported characters.');
    return;
}
if($length < $min || trim($password) === ''){
    cg1l_password_flow_error('cgLostPasswordPasswordMinChar','Minimum number of characters','cg_min_characters_text',' : '.$min);
    return;
}
if($length > $max){
    cg1l_password_flow_error('cgLostPasswordPasswordMaxChar','Maximum number of characters','cg_max_characters_text',' : '.$max);
    return;
}

$result = cg1l_password_reset_apply($wpUserID,$key,$password);
if(is_wp_error($result)){
    if($result->get_error_code() === 'cg_reset_invalid'){
        cg1l_password_flow_error('cgLostPasswordUrlIsNotValidAnymore',$invalidMessage,'cg_language_LostPasswordUrlIsNotValidAnymore');
    }elseif($result->get_error_code() === 'cg_reset_uncertain'){
        cg1l_password_flow_error('cgLostPasswordResetError','The password reset could not be fully confirmed. Please try signing in with your new password before requesting another reset link.');
    }else{
        cg1l_password_flow_error('cgLostPasswordResetError','Your password could not be saved. Please try again.');
    }
    return;
}
?>
<script data-cg-processing="true" data-cg-password-result="success">
    document.getElementById('mainCGdivLostPasswordResetContainer').classList.add('cg_hide');
    document.getElementById('mainCGdivResetPasswordSuccessfullyExplanation').classList.remove('cg_hide');
    document.getElementById('mainCGdivLoginFormContainer').classList.remove('cg_hide');
</script>
