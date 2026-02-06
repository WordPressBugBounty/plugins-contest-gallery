<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$tablenameWpUsers = $wpdb->base_prefix . "users";
$tablenameWpUserMeta = $wpdb->base_prefix . "usermeta";
$tablenameProOptions = $wpdb->prefix . "contest_gal1ery_pro_options";
$tablenameCreateUserEntries = $wpdb->prefix . "contest_gal1ery_create_user_entries";

$cglActivationKeyResend = sanitize_text_field(wp_unslash($_POST["cglActivationKeyResend"]));

$userAccountEntries = $wpdb->get_results( $wpdb->prepare("SELECT Field_Type, Field_Content, Tstamp FROM $tablenameCreateUserEntries WHERE activation_key=%s", $cglActivationKeyResend) );

include (__DIR__.'/../../../../../check-language-general.php');

// then registration was done and user should be directly logged and created account without waiting for mail
if (count($userAccountEntries)) {

    $cg_users_pin = 1;
    $cg_main_mail = '';

    foreach ($userAccountEntries as $userAccountEntry) {
        if($userAccountEntry->Field_Type == 'main-mail') {
            $cg_main_mail = $userAccountEntry->Field_Content;
        }
    }

    $activation_key_new = md5(time() . wp_generate_password( 32, true, true ));

    $wpdb->update(
        $tablenameCreateUserEntries,
        ['activation_key' => $activation_key_new],
        ['activation_key' => $cglActivationKeyResend],
        ['%s'],
        ['%s']
    );

    $pin = random_int(1000, 9999);
    $pinHashed = password_hash($pin,PASSWORD_DEFAULT);

    $wpdb->update(
        $tablenameCreateUserEntries,
        ['Field_Content' => $pinHashed],
        ['activation_key' => $activation_key_new,'Field_Type' => 'activation-pin'],
        ['%s'],
        ['%s','%s']
    );

    $proOptions = $wpdb->get_row("SELECT * FROM $tablenameProOptions WHERE GeneralID = '1'");
    $Subject = $proOptions->RegPinSubject;
    $TextEmailConfirmation = contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->TextPinConfirmation);
    if(empty($TextEmailConfirmation)){// because of update 28.1.0, mighty be empty
        $TextEmailConfirmation = 'Complete your registration by using the PIN below: <br/><br/> $pin$';
    }
    $posPin = '$pin$';

    $wp_mail_result = cg1l_send_registration_mail($proOptions,0,$cg_users_pin,$cg_main_mail,$Subject, $TextEmailConfirmation, $activation_key_new, $posPin, $pin);

    ?>
    <script  data-cg-processing="true" data-cg-success="true">
        cgJsClass.gallery.vars.activationKey = <?php echo json_encode($activation_key_new);?>;
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