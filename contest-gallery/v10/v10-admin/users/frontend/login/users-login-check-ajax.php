<?php
if(!defined('ABSPATH')){exit;}

		// 1 = Mail or Username
		// 2 = Password
		// 3 = Check
		// 4 = GalleryID

		if(session_id() == '') {
			session_start();
		}

		/*if(@$_SESSION["cg_login_count"]==false){
			echo "Plz don't manipulate the registry Code:117";return false;
		}*/

/*		if(empty($_SESSION["cg_login_count"])){
			//Achtung! Mit 1 anfangen ansonsten wird als false gezählt wenn es mit 0 anfängt.
			$_SESSION["cg_login_count"]=1;
		}
		else{
			$_SESSION["cg_login_count"]++;
		}

		if($_SESSION["cg_login_count"]>15){
			echo "To many invalid atempts. Please try few minutes later again";return false;
		}*/

        $GalleryID = absint(sanitize_text_field($_REQUEST['action4']));

        $wp_upload_dir = wp_upload_dir();
        //$optionsPath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-options.json';
       // $optionsSource =json_decode(file_get_contents($optionsPath),true);
        //$intervalConf = cg_shortcode_interval_check($GalleryID,$optionsSource,'cg_users_login');
        $intervalConf = cg_shortcode_interval_check($GalleryID,[],'cg_users_login');
        if(!$intervalConf['shortcodeIsActive']){
            ?>
            <script data-cg-processing="true">
                var gid = <?php echo json_encode($GalleryID);?>;
                cgIsShortcodeIntervalOverForLogin = true;
            </script>
            <?php
            cg_shortcode_interval_check_show_ajax_message($intervalConf,$GalleryID);
            return;
        }

        $cg_check = sanitize_text_field($_REQUEST['action3']);
        $galleryHashToCompare = cg_hash_function('---cglogin---'.$GalleryID, $cg_check);

        // Hier geht die Validierung los
		if($cg_check==$galleryHashToCompare){

            global $wpdb;

            if(!empty($_REQUEST['cgLostPasswordEmail'])){
                include (__DIR__.'/ajax/users-login-check-ajax-lost-password.php');
                return;
            }

            if(!empty($_REQUEST['cgLostPasswordNew'])){
                include (__DIR__.'/ajax/users-login-check-ajax-password-reset.php');
                return;
            }

        $tablename_options = $wpdb->prefix."contest_gal1ery_options";
        $tablename_pro_options = $wpdb->prefix."contest_gal1ery_pro_options";

		$cg_login_name_mail = (isset($_REQUEST['action1']) && is_string($_REQUEST['action1'])) ? sanitize_text_field(wp_unslash($_REQUEST['action1'])) : '';
		$cg_login_password = (isset($_REQUEST['action2']) && is_string($_REQUEST['action2'])) ? wp_unslash($_REQUEST['action2']) : '';
		$cg_auth_login = $cg_login_name_mail;

		if(is_email($cg_login_name_mail)){
			$cg_user_by_email = get_user_by('email', $cg_login_name_mail);
			if(!empty($cg_user_by_email)){
				$cg_auth_login = $cg_user_by_email->user_login;
			}
		}

		if(cg1l_is_login_rate_limited($cg_login_name_mail)){
			$cg_user = new WP_Error('cg_login_rate_limited');
		}else{
			$creds = array();
			$creds['user_login'] = $cg_auth_login;
			$creds['user_password'] = $cg_login_password;
			$creds['remember'] = true;
			$cg_user = wp_signon($creds, is_ssl());

			if(is_wp_error($cg_user)){
				cg1l_record_login_failure($cg_login_name_mail);
			}else{
				cg1l_clear_login_failures($cg_login_name_mail);
			}
		}

		if(is_wp_error($cg_user)){

?>
<script data-cg-processing="true">
var cg_language_LoginAndPasswordDoNotMatch = document.getElementById("cg_language_LoginAndPasswordDoNotMatch").value;

var cg_check_mail_name_value_for_login = document.getElementById('cg_check_mail_name_value_for_login');
cg_check_mail_name_value_for_login.value = 1;

var cg_append_login_and_password_do_not_match = document.getElementById('cg_append_login_and_password_do_not_match');
cg_append_login_and_password_do_not_match.innerHTML = cg_append_login_and_password_do_not_match.innerHTML + cg_language_LoginAndPasswordDoNotMatch;
cg_append_login_and_password_do_not_match.classList.remove('cg_hide');

// Password Feld leer machen
//var cg_login_password = document.getElementById('cg_login_password');
//cg_login_password.value = '';

</script>
<?php
			return false;
		}

		$galleryDbVersion = 100;
                if(!empty($GalleryID)){
	                $galleryDbVersion = $wpdb->get_var( "SELECT Version FROM $tablename_options WHERE id='$GalleryID'");
                }

                if(intval($galleryDbVersion)>=14 || empty($GalleryID)){
                    $ForwardAfterLoginUrlCheck = intval($wpdb->get_var("SELECT ForwardAfterLoginUrlCheck FROM $tablename_pro_options WHERE GeneralID = '1'"));
	                $ForwardAfterLoginUrl = $wpdb->get_var("SELECT ForwardAfterLoginUrl FROM $tablename_pro_options WHERE GeneralID = '1'");
                    if(!empty($ForwardAfterLoginUrl)){
	                    $ForwardAfterLoginUrl = html_entity_decode(stripslashes(nl2br($ForwardAfterLoginUrl)));
                    }else{
	                    $ForwardAfterLoginUrl = '';
                    }
                }else{
                    $ForwardAfterLoginUrlCheck = intval($wpdb->get_var("SELECT ForwardAfterLoginUrlCheck FROM $tablename_pro_options WHERE GalleryID = '$GalleryID'"));
	                $ForwardAfterLoginUrl = $wpdb->get_var("SELECT ForwardAfterLoginUrl FROM $tablename_pro_options WHERE GalleryID = '$GalleryID'");
                    if(!empty($ForwardAfterLoginUrl)){
	                    $ForwardAfterLoginUrl = html_entity_decode(stripslashes(nl2br($ForwardAfterLoginUrl)));
                    }else{
	                    $ForwardAfterLoginUrl = '';
                    }
                }

                    ?>
                    <script data-cg-processing="true" data-cg-processing-successfully="true">
                        cgJsClass.gallery.vars.isSuccessFullySignedIn = true;
                        cgJsClass.gallery.vars.ForwardAfterLoginUrlCheck = <?php echo json_encode($ForwardAfterLoginUrlCheck); ?>;
                        cgJsClass.gallery.vars.ForwardAfterLoginUrl = null;
                        if(cgJsClass.gallery.vars.ForwardAfterLoginUrlCheck){
                            cgJsClass.gallery.vars.ForwardAfterLoginUrl = <?php echo json_encode($ForwardAfterLoginUrl); ?>;
                        }
                    </script>
                    <?php
                    die();

		}
		else{

            ?>
            <script data-cg-processing="true">
                console.log("Login manipulation prevention code 341. Please contact Administrator if you have questions.");
            </script>
            <?php
            die();

		}


?>
