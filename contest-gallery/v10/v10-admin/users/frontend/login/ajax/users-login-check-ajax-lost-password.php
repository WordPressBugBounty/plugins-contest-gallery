<?php
if(!defined('ABSPATH')){exit;}

$table = $wpdb->prefix.'contest_gal1ery_registry_and_login_options';
$options = $wpdb->get_row("SELECT * FROM $table WHERE GeneralID = 1");
$errorTarget = 'cgLostPasswordEmailValidationMessage';
if(!$options || intval($options->LostPasswordMailActive) !== 1){
    cg1l_password_flow_error($errorTarget,'Password reset is not available. Please contact the administrator.');
    return;
}
if(empty($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cgLostPasswordEmail']) || !is_string($_POST['cgLostPasswordEmail'])){
    cg1l_password_flow_error($errorTarget,'Please enter a valid email address.','cg_language_EmailAddressHasToBeValid');
    return;
}
$email = sanitize_email(wp_unslash($_POST['cgLostPasswordEmail']));
if(!is_email($email)){
    cg1l_password_flow_error($errorTarget,'Please enter a valid email address.','cg_language_EmailAddressHasToBeValid');
    return;
}
$userId = 0;
if(cg1l_password_reset_request_allowed($email)){
    $userId = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_email = %s",$email));
}
if($userId){
    $siteUrl = wp_get_referer();
    $siteUrl = $siteUrl ? strtok(strtok($siteUrl,'?'),'#') : '';
    $mailBody = contest_gal1ery_convert_for_html_output_without_nl2br($options->LostPasswordMailConfirmation);
    if(!$siteUrl || stripos($mailBody,'$resetpasswordurl$') === false){
        cg1l_password_flow_error($errorTarget,'The reset email is not configured correctly. Please contact the administrator.');
        return;
    }
    $key = cg1l_password_reset_issue(absint($userId));
    if(is_wp_error($key)){
        cg1l_password_flow_error($errorTarget,'The reset link could not be created. Please try again.');
        return;
    }
    $mailBody = str_ireplace('$resetpasswordurl$',$siteUrl.'?cgResetPassword='.absint($userId).'&cgResetPasswordKey='.$key,$mailBody);
    $addressor = contest_gal1ery_convert_for_html_output_without_nl2br($options->LostPasswordMailAddressor);
    $from = contest_gal1ery_convert_for_html_output_without_nl2br($options->LostPasswordMailFrom);
    $reply = contest_gal1ery_convert_for_html_output_without_nl2br($options->LostPasswordMailReply);
    $subject = contest_gal1ery_convert_for_html_output_without_nl2br($options->LostPasswordMailSubject);
    $mailAddresses = cg_add_mail_from_reply_headers(array(),$addressor,$from,$reply);
    $headers = $mailAddresses['headers'];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=utf-8';
    global $cgMailAction, $cgMailGalleryId;
    $cgMailAction = 'Lost password e-mail';
    $cgMailGalleryId = $GalleryID;
    add_action('wp_mail_failed','cg_on_wp_mail_error',10,1);
    if(!wp_mail($email,$subject,$mailBody,$headers)){
        cg1l_password_flow_error($errorTarget,'The reset email could not be sent. Please try again or contact the administrator.');
        return;
    }
}
// Keep the same confirmation for unknown addresses and throttled requests.
?>
<script data-cg-processing="true" data-cg-processing-success="true" data-cg-password-result="success">
    document.getElementById('mainCGdivLostPasswordContainer').classList.add('cg_hide');
    document.getElementById('mainCGdivLostPasswordEmailSentContainer').classList.remove('cg_hide');
</script>
