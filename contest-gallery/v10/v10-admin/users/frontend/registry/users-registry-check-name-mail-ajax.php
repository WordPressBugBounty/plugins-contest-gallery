<?php
if(!defined('ABSPATH')){exit;}

if(!empty($_FILES) AND !empty($_FILES['cg_input_image_upload_file']) AND !empty($_FILES['cg_input_image_upload_file']['tmp_name']) AND !empty($_FILES['cg_input_image_upload_file']['tmp_name'][0])){
    $_FILES = cg1l_sanitize_files($_FILES,'cg_input_image_upload_file',2100000);
}

$GalleryID = absint(sanitize_text_field($_POST['cg_gallery_id_registry']));

$wp_upload_dir = wp_upload_dir();
//$optionsPath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-options.json';
//$optionsSource =json_decode(file_get_contents($optionsPath),true);
$intervalConf = cg_shortcode_interval_check($GalleryID,[],'cg_users_reg');
if(!$intervalConf['shortcodeIsActive']){
    ?>
    <script data-cg-processing="true">
        var gid = <?php echo json_encode($GalleryID);?>;
        cgIsShortcodeIntervalOverForReg = true;
    </script>
    <?php
    cg_shortcode_interval_check_show_ajax_message($intervalConf,$GalleryID);
    return;
}

$_POST = cg1l_sanitize_post($_POST);

$cg_current_page_id = intval($_POST['cg_current_page_id']);
$currentPageUrl = get_permalink($cg_current_page_id);

if(empty($currentPageUrl)){
    ?>
    <script  data-cg-processing="true"  data-cg-processing-error="true">

        var cg_error = "Please do not manipulate page id code 332. Please contact Administrator if you have questions.";
        var cg_registry_manipulation_error = cgJsClass.gallery.vars.$regFormContainer.find('#cg_registry_manipulation_error').text(cg_error).removeClass('cg_hide');
        console.log(cg_error);

    </script>
    <?php
    die;
}

		// 1 = Mail
		// 2 = Name
		// 3 = Check

        $GalleryID = absint(sanitize_text_field($_POST['cg_gallery_id_registry']));
        $cg_check = sanitize_text_field($_POST['cg_check']);
        $galleryHashToCompare = cg_hash_function('---cgreg---'.$GalleryID, $cg_check);


/*		var cg_check = $("#cg_user_registry_form #cg_check").val();
    var cg_main_mail = $( ".cg-main-mail" ).val();
    var cg_main_user_name = $( ".cg-main-user-name" ).val();
    var cg_gallery_id_registry = $( "#cg_gallery_id_registry" ).val();*/
if($cg_check==$galleryHashToCompare){
	global $wpdb;

	$tablenameWpUsers = $wpdb->base_prefix . "users";
    $tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
    $tablename_options = $wpdb->prefix . "contest_gal1ery_options";
    $table_usermeta = $wpdb->prefix . "usermeta";

    // because not id give to cg_users_reg shortcode
    if($GalleryID==0){
	    $galleryDbVersion = 100;
    }else{
	    $galleryDbVersion = $wpdb->get_var("SELECT Version FROM $tablename_options WHERE id='$GalleryID'");
    }

    $cg_users_pin = !empty($_POST['cg_users_pin']) ? 1 : 0;

    $cg_main_mail = sanitize_email(strtolower($_POST['cg-main-mail']));

    if (empty($cg_main_mail) || is_email($cg_main_mail) == false) {// email has to be always
        ?>
        <script  data-cg-processing="true"  data-cg-processing-error="true">
            var cg_error = cgJsClass.gallery.vars.$regFormContainer.find('#cg_check_email_upload').val();
            var cg_registry_manipulation_error = cgJsClass.gallery.vars.$regFormContainer.find('#cg_registry_manipulation_error').text(cg_error).removeClass('cg_hide');
            console.log(cg_error);
        </script>
        <?php
        die;
    }

	$cg_main_user_name = sanitize_text_field($_POST['cg-main-user-name']);

    $cg_main_nick_name = '';

    if(intval($galleryDbVersion)>=14){
        $cg_main_nick_name = sanitize_text_field($_POST['cg-main-nick-name']);
    }

    $checkWpIdViaMail = false;

    if(!empty($cg_main_mail)){
        $checkWpIdViaMail = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM $tablenameWpUsers WHERE user_email = %s",
                $cg_main_mail
            )
        );
    }

    $checkWpIdViaUserName = false;

    if(!empty($cg_main_user_name)){
        $checkWpIdViaUserName = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM $tablenameWpUsers WHERE user_login = %s",
                $cg_main_user_name
            )
        );
    }

    $checkWpIdViaNickNameUsermeta = false;
    if(intval($galleryDbVersion)>=14 && !empty($cg_main_nick_name)){
        $checkWpIdViaNickNameUsermeta = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_id FROM $table_usermeta 
         WHERE meta_key = 'nickname' 
         AND meta_value = %s",
                $cg_main_nick_name
            )
        );
    }

	if($checkWpIdViaMail==true){
?>
<script  data-cg-processing="true">
var cg_language_ThisMailAlreadyExists = cgJsClass.gallery.vars.$regFormContainer.find("#cg_language_ThisMailAlreadyExists").val();

cgJsClass.gallery.vars.$regFormContainer.find('#cg_check_mail_name_value').val(1);// blocks form from beeing submitted

var cg_mail_check_alert = cgJsClass.gallery.vars.$regFormContainer.find('#cg_mail_check_alert').text(cg_language_ThisMailAlreadyExists).removeClass("cg_hide");

//alert(cg_language_ThisMailAlreadyExists);
</script>
<?php
		}
		if($checkWpIdViaUserName==true){
?>
<script data-cg-processing="true">
var cg_language_ThisUsernameAlreadyExists = cgJsClass.gallery.vars.$regFormContainer.find("#cg_language_ThisUsernameAlreadyExists").val();

var cg_check_mail_name_value = cgJsClass.gallery.vars.$regFormContainer.find('#cg_check_mail_name_value').val(1);

cgJsClass.gallery.vars.$regFormContainer.find('#cg_user_name_check_alert').text(cg_language_ThisUsernameAlreadyExists).removeClass("cg_hide");

</script>
<?php
		}
if((intval($galleryDbVersion)>=14 && !empty($checkWpIdViaNickNameUsermeta))){
?>
<script data-cg-processing="true">
var cg_language_ThisNicknameAlreadyExists = cgJsClass.gallery.vars.$regFormContainer.find("#cg_language_ThisNicknameAlreadyExists").val();

var cg_check_mail_name_value = cgJsClass.gallery.vars.$regFormContainer.find('#cg_check_mail_name_value').val(1);

var cg_nick_name_check_alert = cgJsClass.gallery.vars.$regFormContainer.find('#cg_nick_name_check_alert').text(cg_language_ThisNicknameAlreadyExists).removeClass("cg_hide");

</script>
<?php
		}
        if($checkWpIdViaUserName!=true && $checkWpIdViaMail!=true && $checkWpIdViaNickNameUsermeta!=true){
            if(intval($galleryDbVersion)>=14){
             //   $RegMailOptional = $wpdb->get_var( "SELECT RegMailOptional FROM $tablename_pro_options WHERE GeneralID = '1'" );
            }else{
                $GalleryID = absint($GalleryID);
              //  $RegMailOptional = $wpdb->get_var( "SELECT RegMailOptional FROM $tablename_pro_options WHERE GalleryID = '$GalleryID'" );
            }
            include('users-registry-check-registering-and-login.php');
            // <<< registration and forwarding processing will be done here
            return;
        }

}
else{

    ?>
    <script  data-cg-processing="true">

        var cg_error = "Registration manipulation prevention code 331. Please contact Administrator if you have questions.";
        var cg_registry_manipulation_error = cgJsClass.gallery.vars.$regFormContainer.find('#cg_registry_manipulation_error').text(cg_error).removeClass("cg_hide");
        console.log(cg_error);

    </script>
    <?php
    die;

}


?>