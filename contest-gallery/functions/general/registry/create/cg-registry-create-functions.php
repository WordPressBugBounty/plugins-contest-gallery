<?php
require_once __DIR__.'/../cg-public-profile-fields.php';

if(!function_exists('cg_create_contest_gallery_user_role')){
    function cg_create_contest_gallery_user_role() {
        add_role(
            'contest_gallery_user_since_v14',
            __( 'Contest Gallery User since v14' ),
            array(
                'read' => true
            )
        );
    }
}

add_action('cg_create_general_registration_form_v14','cg_create_general_registration_form_v14');
if(!function_exists('cg_create_general_registration_form_v14')){
    function cg_create_general_registration_form_v14(){

        global $wpdb;

        $tablename_create_user_form = $wpdb->prefix . "contest_gal1ery_create_user_form";

        $count_tablename_create_user_form_with_GeneralID = $wpdb->get_var( "SELECT id FROM $tablename_create_user_form WHERE GeneralID = 1 LIMIT 1");

        if(empty($count_tablename_create_user_form_with_GeneralID)){

            $wpdb->query( $wpdb->prepare(
                "
						INSERT INTO $tablename_create_user_form
						( id, GalleryID, Field_Type, Field_Order,
						Field_Name,Field_Content,Min_Char,Max_Char,
						Required,Active,GeneralID)
						VALUES ( %s,%d,%s,%s,
						%s,%s,%d,%d,
						%d,%d,%d)
					",
                '',0,'main-user-name',1,
                'Username (for login)','',3,100,
                1,1,1
            ) );

            $wpdb->query( $wpdb->prepare(
                "
						INSERT INTO $tablename_create_user_form
						( id, GalleryID, Field_Type, Field_Order,
						Field_Name,Field_Content,Min_Char,Max_Char,
						Required,Active,GeneralID)
						VALUES ( %s,%d,%s,%s,
						%s,%s,%d,%d,
						%d,%d,%d)
					",
                '',0,'main-nick-name',1,
                'Nickname (to display in frontend)','',3,100,
                1,1,1
            ) );

            $wpdb->query( $wpdb->prepare(
                "
						INSERT INTO $tablename_create_user_form
						( id, GalleryID, Field_Type, Field_Order,
						Field_Name,Field_Content,Min_Char,Max_Char,
						Required,Active,GeneralID)
						VALUES ( %s,%d,%s,%s,
						%s,%s,%d,%d,
						%d,%d,%d)
					",
                '',0,'main-mail',2,
                'E-mail (for login)','',0,0,
                1,1,1
            ) );

            $wpdb->query( $wpdb->prepare(
                "
						INSERT INTO $tablename_create_user_form
						( id, GalleryID, Field_Type, Field_Order,
						Field_Name,Field_Content,Min_Char,Max_Char,
						Required,Active,GeneralID)
						VALUES ( %s,%d,%s,%s,
						%s,%s,%d,%d,
						%d,%d,%d)
					",
                '',0,'password',3,
                'Password','',6,100,
                1,1,1
            ) );

            $wpdb->query( $wpdb->prepare(
                "
						INSERT INTO $tablename_create_user_form
						( id, GalleryID, Field_Type, Field_Order,
						Field_Name,Field_Content,Min_Char,Max_Char,
						Required,Active,GeneralID)
						VALUES ( %s,%d,%s,%s,
						%s,%s,%d,%d,
						%d,%d,%d)
					",
                '',0,'password-confirm',4,
                'Password confirm','',6,100,
                1,1,1
            ) );

            // Optional profile fields are only seeded with the first general form.
            $profileFields = array(
                array('profile-image','Profile image',0),
                array('user-text-field','City / Region',100),
                array(cg_registry_url_fields_enabled() ? 'user-url-field' : 'user-text-field','Website / Portfolio',300),
                array(cg_registry_url_fields_enabled() ? 'user-url-field' : 'user-text-field','Social profile URL',300),
                array('user-comment-field','About me',2000)
            );
            foreach($profileFields as $index=>$profileField){
                $wpdb->insert($tablename_create_user_form,array(
                    'GalleryID'=>0,'GeneralID'=>1,'Field_Type'=>$profileField[0],
                    'Field_Order'=>$index+5,'Field_Name'=>$profileField[1],'Field_Content'=>'',
                    'Min_Char'=>0,'Max_Char'=>$profileField[2],'Required'=>0,'Active'=>1,
                    'RowNumber'=>$index+6,'ColNumber'=>1,'RowCols'=>1,'PinField'=>0,
                    'PublicProfile'=>$profileField[0]==='profile-image' ? 0 : 1
                ));
            }

        }

    }
}


if(!function_exists('cg_create_registry_and_login_options_v14')){
    function cg_create_registry_and_login_options_v14( ) {

        global $wpdb;

        $tablename_options = $wpdb->prefix . "contest_gal1ery_options";
        $tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
        $tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
        $tablename_registry_and_login_options = $wpdb->prefix . "contest_gal1ery_registry_and_login_options";

        $count_tablename_registry_and_login_options = $wpdb->get_var( "SELECT id FROM $tablename_registry_and_login_options WHERE GeneralID = 1 LIMIT 1");
        if(empty($count_tablename_registry_and_login_options)){
            cg_create_registry_and_login_options();
        }

        $count_pro_options_table_with_GeneralID = $wpdb->get_var( "SELECT id FROM $tablename_pro_options WHERE GeneralID = 1 LIMIT 1");

        if(empty($count_pro_options_table_with_GeneralID)){

            include(__DIR__.'/../../../../v10/v10-admin/json-values.php');
            $RegMailAddressor = trim(get_option('blogname'));
            $RegMailReply = get_option('admin_email');
            $RegMailFrom = $RegMailReply;
            $RegMailSubject = 'Please complete your registration';
            $TextEmailConfirmation = 'Complete your registration by clicking on the link below: <br/><br/> $regurl$';
            $TextPinConfirmation = 'Complete your registration by using the PIN below: <br/><br/> $pin$';
            $RegPinSubject= 'Please verify your PIN';
            $ForwardAfterRegText = 'Thank you for your registration<br/>Check your email account to confirm your email and complete the registration. If you don\'t see any message then plz check also the spam folder.';

            $TextAfterEmailConfirmation = 'Email confirmed — you can now participate in the contest.';
            $TextAfterPinConfirmation = 'PIN verified — you can now participate in the contest.';

            $ForwardAfterLoginText = 'You are now logged in. Have fun with photo contest.';
            $ForwardAfterLoginTextCheck = 1;
            $ForwardAfterLoginUrlCheck = 0;
            $ForwardAfterLoginUrl = '';
            $RegMailOptional = 0;
            $HideRegFormAfterLogin = 1;
            $HideRegFormAfterLoginShowTextInstead = 0;
            $HideRegFormAfterLoginTextToShow = '';

            $wpdb->query( $wpdb->prepare(
                "
				INSERT INTO $tablename_pro_options
				(
				 id, GalleryID, GeneralID, 
				RegMailOptional, ForwardAfterRegText, TextAfterEmailConfirmation, TextAfterPinConfirmation,
				HideRegFormAfterLogin, HideRegFormAfterLoginShowTextInstead,HideRegFormAfterLoginTextToShow,
				RegMailAddressor, RegMailReply, RegMailFrom, RegMailSubject, TextEmailConfirmation, TextPinConfirmation, RegPinSubject,
				ForwardAfterLoginUrlCheck,ForwardAfterLoginUrl, ForwardAfterLoginTextCheck, ForwardAfterLoginText
				)
				VALUES (
				%s,%d,%d,
				%d,%s,%s,%s,
				%d,%d,%s,
				%s,%s,%s,%s,%s,%s,%s,
				%d,%s,%d,%s
				)
			",
                '',0,1,
                $RegMailOptional, $ForwardAfterRegText, $TextAfterEmailConfirmation, $TextAfterPinConfirmation,
                $HideRegFormAfterLogin, $HideRegFormAfterLoginShowTextInstead, $HideRegFormAfterLoginTextToShow,
                $RegMailAddressor, $RegMailReply, $RegMailFrom, $RegMailSubject, $TextEmailConfirmation, $TextPinConfirmation,$RegPinSubject,
                $ForwardAfterLoginUrlCheck,$ForwardAfterLoginUrl,$ForwardAfterLoginTextCheck,$ForwardAfterLoginText
            ) );

            $wpdb->query( $wpdb->prepare(
                "
				INSERT INTO $tablename_options_visual
				( id, GalleryID, GeneralID,BorderRadiusRegistry,BorderRadiusLogin,FeControlsStyleRegistry,FeControlsStyleLogin)
				VALUES (%s,%d,%d,%d,%d,%s,%s)
			",
                '',0,1,1,1,'white','white'
            ) );

        }


    }
}

if(!function_exists('cg_create_registry_and_login_options')){
    function cg_create_registry_and_login_options($GalleryID = 0) {// gallery id will be send for gallery created before 14, creating of this options in edit-options

        $GeneralID = 1;
        $RegistryUserRole = 'contest_gallery_user_since_v14';
        if(!empty($GalleryID)){
            $GeneralID = 0;
            $RegistryUserRole = 'contest_gallery_user';
        }

        global $wpdb;

        $tablename_registry_and_login_options = $wpdb->prefix . "contest_gal1ery_registry_and_login_options";

        $LostPasswordMailAddressor = trim(get_option('blogname'));
        $LostPasswordMailReply = get_option('admin_email');
        $LostPasswordMailFrom = $LostPasswordMailReply;
        $LostPasswordMailSubject = 'Reset your password';
        $LostPasswordMailConfirmation = 'Reset your password by using link below: <br><br> $resetpasswordurl$';
        $TextBeforeLoginForm = '';
        $TextBeforeRegFormBeforeLoggedIn = 'Register to participate in the contest. Enter your email to receive confirmation link.';
        $TextBeforePinFormBeforeLoggedIn = 'Register to participate in the contest. Enter your email to receive confirmation PIN.';
        $PermanentTextWhenLoggedIn = '';
        $LoginAfterConfirm = 0;
        if(cg_get_version()=='contest-gallery-pro'){
            $LoginAfterConfirm = 1;
        }
        $wpdb->query( $wpdb->prepare(
            "
						INSERT INTO $tablename_registry_and_login_options
						( id, GalleryID, GeneralID, LogoutLink, BackToGalleryLink,RegistryUserRole,
						 LostPasswordMailAddressor, LostPasswordMailReply, LostPasswordMailFrom,
						 LostPasswordMailSubject, LostPasswordMailConfirmation,
						 TextBeforeLoginForm,TextBeforeRegFormBeforeLoggedIn,TextBeforePinFormBeforeLoggedIn,PermanentTextWhenLoggedIn,
						 ConfirmExpiry, PinExpiry, LoginAfterConfirm
						 )
						VALUES ( %s,%d,%d,%s,%s,%s,
						        %s,%s,%s,
						        %s,%s,
						        %s,%s,%s,%s,
						        %d,%d,%d
						        )
					",
            '',$GalleryID,$GeneralID,'', '',$RegistryUserRole,
            $LostPasswordMailAddressor,$LostPasswordMailReply,$LostPasswordMailFrom,
            $LostPasswordMailSubject,$LostPasswordMailConfirmation,
            $TextBeforeLoginForm,$TextBeforeRegFormBeforeLoggedIn,$TextBeforePinFormBeforeLoggedIn,$PermanentTextWhenLoggedIn,
            86400,900,$LoginAfterConfirm
        ) );

    }
}

?>
