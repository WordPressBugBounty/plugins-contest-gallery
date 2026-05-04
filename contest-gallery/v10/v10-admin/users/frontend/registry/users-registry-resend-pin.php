<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$tablenameWpUsers = $wpdb->base_prefix . "users";
$tablenameWpUserMeta = $wpdb->base_prefix . "usermeta";
$tablenameProOptions = $wpdb->prefix . "contest_gal1ery_pro_options";
$tablenameCreateUserEntries = $wpdb->prefix . "contest_gal1ery_create_user_entries";

$cglPinRequestKeyResend = isset($_POST["cglActivationKeyResend"]) ? sanitize_text_field(wp_unslash($_POST["cglActivationKeyResend"])) : '';
$cglActivationKeyResend = get_transient('cg_pin_request_key_'.$cglPinRequestKeyResend);
if(empty($cglActivationKeyResend)){
    $cglActivationKeyResend = '';
}

$userAccountEntries = $wpdb->get_results( $wpdb->prepare("SELECT Field_Type, Field_Content, Tstamp FROM $tablenameCreateUserEntries WHERE activation_key=%s", $cglActivationKeyResend) );

include (__DIR__.'/../../../../../check-language-general.php');

// then registration was done and user should be directly logged and created account without waiting for mail
if (count($userAccountEntries)) {

    $cg_users_pin = 1;
    $cg_main_mail = '';
    $oldPinHash = '';
    $oldTstamp = !empty($userAccountEntries[0]->Tstamp) ? absint($userAccountEntries[0]->Tstamp) : 0;

    foreach ($userAccountEntries as $userAccountEntry) {
        if($userAccountEntry->Field_Type == 'main-mail') {
            $cg_main_mail = $userAccountEntry->Field_Content;
        }
        if($userAccountEntry->Field_Type == 'activation-pin') {
            $oldPinHash = $userAccountEntry->Field_Content;
        }
    }

    if(empty($cg_main_mail) || empty($oldPinHash)){
        ?>
        <script  data-cg-processing="true" >
            cgJsClass.gallery.vars.activationKey = '';
            cgJsClass.gallery.vars.pinMessage = 'mail-not-found';
        </script>
        <?php
        die;
    }

    $proOptions = $wpdb->get_row("SELECT * FROM $tablenameProOptions WHERE GeneralID = '1'");
    $posPin = '$pin$';
    $TextEmailConfirmation = contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->TextPinConfirmation);
    if(empty($TextEmailConfirmation)){// because of update 28.1.0, mighty be empty
        $TextEmailConfirmation = 'Complete your registration by using the PIN below: <br/><br/> $pin$';
    }
    if (stripos($TextEmailConfirmation, $posPin) === false) {
        ?>
        <script  data-cg-processing="true" >
            cgJsClass.gallery.vars.activationKey = '';
            cgJsClass.gallery.vars.pinMessage = 'pin-placeholder-missing';
        </script>
        <?php
        die;
    }

    $time = time();
    $activation_key_new = md5($time . wp_generate_password( 32, true, true ));

    $wpdb->update(
        $tablenameCreateUserEntries,
        ['activation_key' => $activation_key_new,'Tstamp' => $time],
        ['activation_key' => $cglActivationKeyResend],
        ['%s','%d'],
        ['%s']
    );

    $pin = wp_rand(1000, 9999);
    $pinHashed = password_hash($pin,PASSWORD_DEFAULT);

    $wpdb->update(
        $tablenameCreateUserEntries,
        ['Field_Content' => $pinHashed],
        ['activation_key' => $activation_key_new,'Field_Type' => 'activation-pin'],
        ['%s'],
        ['%s','%s']
    );

    $Subject = str_ireplace($posPin, $pin, contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->RegPinSubject));

    $wp_mail_result = cg1l_send_registration_mail($proOptions,0,$cg_users_pin,$cg_main_mail,$Subject, $TextEmailConfirmation, $activation_key_new, $posPin, $pin);
    if (!$wp_mail_result) {
        $wpdb->update(
            $tablenameCreateUserEntries,
            ['activation_key' => $cglActivationKeyResend,'Tstamp' => $oldTstamp],
            ['activation_key' => $activation_key_new],
            ['%s','%d'],
            ['%s']
        );
        $wpdb->update(
            $tablenameCreateUserEntries,
            ['Field_Content' => $oldPinHash],
            ['activation_key' => $cglActivationKeyResend,'Field_Type' => 'activation-pin'],
            ['%s'],
            ['%s','%s']
        );
        ?>
        <script  data-cg-processing="true" >
            cgJsClass.gallery.vars.activationKey = '';
            cgJsClass.gallery.vars.pinMessage = 'mail-not-sent';
        </script>
        <?php
        die;
    }

    $cgPinRequestKey = wp_generate_password(48, false, false);
    set_transient('cg_pin_request_key_'.$cgPinRequestKey, $activation_key_new, DAY_IN_SECONDS);
    if(!empty($cglPinRequestKeyResend)){
        delete_transient('cg_pin_request_key_'.$cglPinRequestKeyResend);
    }

    ?>
    <script  data-cg-processing="true" data-cg-success="true">
        cgJsClass.gallery.vars.activationKey = <?php echo json_encode($cgPinRequestKey);?>;
    </script>
    <?php
    die;

} else {

    ?>
    <script  data-cg-processing="true" >
        cgJsClass.gallery.vars.activationKey = '';
    </script>
    <?php
    die;

}



?>
