<?php

// 1. Delete Felder in Entries, F_Input, F_Output
// 2. Swap Field_Order in Entries, F_Input, F_Output (bei post "done-upload" wird alles mitgegeben
// 3. Neue Felder hinzuf�gen in F_Input, Entries
// 4. // Auswahl zum Anzeigen gespeicherter Felder

// Empfangen von Galerie OptiOns ID

$GalleryID = absint($_GET['option_id']);

global $wpdb;

// Tabellennamen bestimmen

$tablename = $wpdb->prefix . "contest_gal1ery";
$tablenameoptions = $wpdb->prefix . "contest_gal1ery_options";
$tablenameentries = $wpdb->prefix . "contest_gal1ery_entries";
$tablename_create_user_form = $wpdb->prefix . "contest_gal1ery_create_user_form";
$tablename_create_user_entries = $wpdb->prefix . "contest_gal1ery_create_user_entries";
$tablename_form_output = $wpdb->prefix . "contest_gal1ery_f_output";
$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
$wp_usermeta_table = $wpdb->usermeta;

$cgOptions = $wpdb->get_row($wpdb->prepare( "SELECT GalleryName,Version FROM $tablenameoptions WHERE id = %d",[$GalleryID]));

$GalleryName = $cgOptions->GalleryName;
$galleryDbVersion = $cgOptions->Version;

if(intval($galleryDbVersion)>=14){
    $GalleryIDorGeneralIDstring = 'GeneralID';
    $GalleryIDorGeneralIDnumber = 1;
    $GeneralID = 1;
    $GeneralIDtoInsert = 0;
}else{
    $GalleryIDorGeneralIDstring = 'GalleryID';
    $GalleryIDorGeneralIDnumber = $GalleryID;
    $GeneralID = 0;
    $GeneralIDtoInsert = $GalleryID;
}

// Authenticate before deleting fields or changing their public visibility.
if(!empty($_POST['submit']) || !empty($_POST['deleteFieldnumber'])){check_admin_referer('cg_admin');}

// Bind every submitted ID to this form and type before deleting or writing any rows.
if(isset($_POST['Field_Type']) && !is_array($_POST['Field_Type'])){wp_die('Invalid registration fields.');}
if(isset($_POST['Field_Id']) && !is_array($_POST['Field_Id'])){wp_die('Invalid registration field IDs.');}
if(!empty($_POST['Field_Type']) && is_array($_POST['Field_Type'])){
    $seenFieldIds = array();
    foreach($_POST['Field_Type'] as $fieldIndex=>$fieldType){
        if(!ctype_digit((string)$fieldIndex) || !$fieldIndex || !is_string($fieldType)){wp_die('Invalid registration fields.');}
        $rawFieldId = isset($_POST['Field_Id'][$fieldIndex]) ? $_POST['Field_Id'][$fieldIndex] : '';
        if(!is_scalar($rawFieldId) || ($rawFieldId!=='' && !ctype_digit((string)$rawFieldId))){wp_die('Invalid registration field ID.');}
        $fieldId = absint($rawFieldId);
        $existingField = null;
        if($fieldId){
            $existingField = $wpdb->get_row($wpdb->prepare("SELECT id,Field_Type FROM $tablename_create_user_form WHERE id=%d AND $GalleryIDorGeneralIDstring=%d",$fieldId,$GalleryIDorGeneralIDnumber));
            if(!$existingField || $existingField->Field_Type!==$fieldType || isset($seenFieldIds[$fieldId])){wp_die('Invalid registration field assignment.');}
            $seenFieldIds[$fieldId] = true;
        }else{unset($_POST['Field_Id'][$fieldIndex]);}
        if($fieldType==='user-url-field' && !$GeneralID){
            wp_die('URL fields require the general registration form.');
        }
        if($fieldType==='user-url-field'){
            foreach(array('Field_Name','Field_Content','Min_Char','Max_Char','RowNumber','ColNumber','RowCols') as $part){
                if(!isset($_POST[$part]) || !is_array($_POST[$part]) || !isset($_POST[$part][$fieldIndex]) || !is_scalar($_POST[$part][$fieldIndex])){wp_die('Invalid URL field data.');}
            }
        }
    }
}
if(!empty($_POST['deleteFieldnumber'])){
    if(!is_array($_POST['deleteFieldnumber'])){wp_die('Invalid registration field deletion.');}
    foreach($_POST['deleteFieldnumber'] as $fieldId){
        if(!is_scalar($fieldId) || !ctype_digit((string)$fieldId) || !$wpdb->get_var($wpdb->prepare("SELECT id FROM $tablename_create_user_form WHERE id=%d AND $GalleryIDorGeneralIDstring=%d",absint($fieldId),$GalleryIDorGeneralIDnumber))){wp_die('Invalid registration field deletion.');}
    }
}


// Check if certain fieldnumber should be deleted

//
// Vorgehen: Zuerst Feld l�schen falls einz mitgeschickt wurde zum l�schen. Dann pr�fen welche IDs mitgeschickt wurden (beim erstellten Formular) und diese in f_output und f_entries eing�gen. Die alten
// die drin wahren durch die neuen ersetzten
// Dann pauschal existierendes f_input l�schen und die neuen mitgeschicktern werte komplett neu einf�gen "INSERT"

if(!isset($_POST['deleteFieldnumber'])){
    $_POST['deleteFieldnumber'] = false;
}

$cgCaptchaConfigs = array();
if(!empty($_POST['submit']) && !empty($_POST['Field_Type'])){
    foreach($_POST['Field_Type'] as $cgIndex=>$cgType){
        if($cgType!=='user-robot-recaptcha-field'){continue;}
        $cgId = !empty($_POST['Field_Id'][$cgIndex]) ? absint($_POST['Field_Id'][$cgIndex]) : 0;
        $cgRecord = cg_captcha_prepare_config('registry',$cgId,isset($_POST['ReCaKey']) ? $_POST['ReCaKey'] : '',isset($_POST['ReCaSecret']) ? $_POST['ReCaSecret'] : '',empty($_POST['Hide'][$cgIndex]));
        if(is_wp_error($cgRecord)){wp_die(esc_html($cgRecord->get_error_message()),'',array('back_link'=>true));}
        $cgCaptchaConfigs[$cgIndex] = $cgRecord;
    }
}

if(!isset($_POST['Necessary'])){
    $_POST['Necessary'] = [];
}

$deleteFieldnumbersArray = [];

if(!empty($_POST['deleteFieldnumber'])){

    foreach($_POST['deleteFieldnumber'] as $fieldnumber){

        $fieldnumber = intval($fieldnumber);

        $deleteFieldnumbersArray[] = $fieldnumber;

        if(intval($galleryDbVersion)>=14){

            // wpfn, wpln and profile image will be not deleted because named other way or stored in other table
            $wpdb->query($wpdb->prepare(
                "
				DELETE FROM $wp_usermeta_table WHERE meta_key = %s
			",
                cg_profile_meta_key($fieldnumber)
            ));

            $wpdb->query( $wpdb->prepare(
                "
				DELETE FROM $tablename_create_user_form WHERE GeneralID = %d AND id = %d
			",
                1, $fieldnumber
            ));

        }else{

            $wpdb->query( $wpdb->prepare(
                "
                DELETE FROM $tablename_create_user_form WHERE GalleryID = %d AND id = %d
            ",
                $GalleryID, $fieldnumber
            ));

            $wpdb->query( $wpdb->prepare(
                "
                DELETE FROM $tablename_create_user_entries WHERE GalleryID = %d AND f_input_id = %d
            ",
                $GalleryID, $fieldnumber
            ));

        }

    }
}

// Check if certain fieldnumber should be deleted --- ENDE

// Abspeichern von gesendeten Daten

if (!empty($_POST['submit'])) {

/*  echo "<pre>";
    print_r($_POST);
    echo "</pre>";*/

    check_admin_referer( 'cg_admin');


// Neue Formularfelder werden eingef�gt
$get_Field_Id = (!empty($_POST['Field_Id'])) ? $_POST['Field_Id'] : [];
$get_Field_Type = (!empty($_POST['Field_Type'])) ? $_POST['Field_Type'] : [];
$get_Field_Name = (!empty($_POST['Field_Name'])) ? $_POST['Field_Name'] : [];
$get_ReCaKey = (!empty($_POST['ReCaKey'])) ? cg1l_sanitize_method($_POST['ReCaKey']) : '';
$get_ReCaLang = (!empty($_POST['ReCaLang'])) ? cg1l_sanitize_method($_POST['ReCaLang']) : '';
$get_Field_Content = (!empty($_POST['Field_Content'])) ? $_POST['Field_Content'] : [];
$get_Min_Char = (!empty($_POST['Min_Char'])) ? $_POST['Min_Char'] : [];
$get_Max_Char = (!empty($_POST['Max_Char'])) ? $_POST['Max_Char'] : [];
$get_RowNumber = (!empty($_POST['RowNumber'])) ? $_POST['RowNumber'] : [];
$get_ColNumber = (!empty($_POST['ColNumber'])) ? $_POST['ColNumber'] : [];
$get_RowCols = (!empty($_POST['RowCols'])) ? $_POST['RowCols'] : [];

foreach ($get_Min_Char as $charKey => $char){
    $get_Min_Char[$charKey] = absint($char);
}

foreach ($get_Max_Char as $charKey => $char){
    $get_Max_Char[$charKey] = absint($char);
}

foreach ($get_RowNumber as $charKey => $char){
    $get_RowNumber[$charKey] = absint($char);
}

foreach ($get_ColNumber as $charKey => $char){
    $get_ColNumber[$charKey] = absint($char);
}

foreach ($get_RowCols as $charKey => $char){
    $get_RowCols[$charKey] = absint($char);
}

$get_Necessary = (!empty($_POST['Necessary'])) ? $_POST['Necessary'] : [];
$get_Hide = (!empty($_POST['Hide'])) ? $_POST['Hide'] : [];
$get_PinField = (!empty($_POST['PinField'])) ? $_POST['PinField'] : [];


    $fieldOrder = 0;



foreach($get_Field_Type as $key => $value){
        // Use the same submitted index as the validation above, including sparse arrays.
        $i = $key;
        $wpdb->insert_id = 0;
        $PublicProfile = $GeneralID && in_array($value,cg_public_profile_field_types(),true) && !empty($_POST['PublicProfile'][$i]) ? 1 : 0;

		// Das gel�schte Feld soll nicht nochmal kreiert werden. Unbedingt auf true pr�fen! Ansonsten bei zwei leeren Werten ist die Bedingung auch erf�llt.
		if(!empty($deleteFieldnumbersArray) && !empty($get_Field_Id[$i]) && in_array($get_Field_Id[$i],$deleteFieldnumbersArray)!==false){continue;}

        if($value=="profile-image"){

            $fieldOrder++;

            $Active = 1;
            if(!empty($get_Hide[$i])){
                $Active = 0;
            }

            $PinField = 0;
            if(!empty($get_PinField[$i])){
                $PinField = 1;
            }

            if(!isset($get_Necessary[$i])){
                $get_Necessary[$i] = false;
            }

            if($get_Necessary[$i]=='on'){$update_Necessary=1;}
            else{$update_Necessary=0;}

            $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);

            if(isset($get_Field_Id[$i])){
                $wpdb->update(
                    "$tablename_create_user_form",
                    array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
                        'Field_Content' => '','Min_Char' => '','Max_Char' => '', 'Required' => $update_Necessary, 'Active' => $Active,
                        'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile
                    ),
                    array('id' => $get_Field_Id[$i]),
                    array('%d','%s',
                        '%s','%s','%s','%d','%d','%d','%d','%d','%d','%d'),
                    array('%d')
                );
            }
            else{

                $wpdb->query( $wpdb->prepare(
                    "
                                INSERT INTO $tablename_create_user_form
                                ( id, GalleryID, Field_Type, Field_Order,
                                Field_Name,Field_Content,Min_Char,Max_Char,
                                Required,Active,GeneralID,
                                 RowNumber,ColNumber,RowCols,PinField,PublicProfile
                                 )
                                VALUES ( %s,%d,%s,%d,
                                %s,%s,%d,%d,
                                %d,%d,%d,
                                %d,%d,%d,%d,%d)
                            ",
                    '',$GeneralIDtoInsert,'profile-image',$i,
                    $get_Field_Name[$i],'','','',
                    $update_Necessary,$Active,$GeneralID,
                    $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
                ) );

            }

        }

        if($value=="wpfn"){

            $fieldOrder++;
            $Active = 1;
            if(!empty($get_Hide[$i])){
                $Active = 0;
            }

            $PinField = 0;
            if(!empty($get_PinField[$i])){
                $PinField = 1;
            }

            if(!isset($get_Necessary[$i])){
                $get_Necessary[$i] = false;
            }

            if($get_Necessary[$i]=='on'){$update_Necessary=1;}
            else{$update_Necessary=0;}

            $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
            $get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);

            if(isset($get_Field_Id[$i])){
                $wpdb->update(
                    "$tablename_create_user_form",
                    array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
                        'Field_Content' => $get_Field_Content[$i],'Min_Char' => $get_Min_Char[$i],'Max_Char' => $get_Max_Char[$i], 'Required' => $update_Necessary, 'Active' => $Active,
                        'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
                    array('id' => $get_Field_Id[$i]),
                    array('%d','%s',
                        '%s','%d','%d','%d','%d','%d','%d','%d','%d','%d'),
                    array('%d')
                );
            }
            else{

                $wpdb->query( $wpdb->prepare(
                    "
                                INSERT INTO $tablename_create_user_form
                                ( id, GalleryID, Field_Type, Field_Order,
                                Field_Name,Field_Content,Min_Char,Max_Char,
                                Required,Active,GeneralID,
                                 RowNumber,ColNumber,RowCols,PinField,PublicProfile)
                                VALUES ( %s,%d,%s,%d,
                                %s,%s,%d,%d,
                                %d,%d,%d,
                                %d,%d,%d,%d,%d)
                            ",
                    '',$GeneralIDtoInsert,'wpfn',$i,
                    $get_Field_Name[$i],$get_Field_Content[$i],$get_Min_Char[$i],$get_Max_Char[$i],
                    $update_Necessary,$Active,$GeneralID,
                    $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
                ) );

            }

        }

        if($value=="wpln"){

            $fieldOrder++;
            $Active = 1;
            if(!empty($get_Hide[$i])){
                $Active = 0;
            }

            $PinField = 0;
            if(!empty($get_PinField[$i])){
                $PinField = 1;
            }

            if(!isset($get_Necessary[$i])){
                $get_Necessary[$i] = false;
            }

            if($get_Necessary[$i]=='on'){$update_Necessary=1;}
            else{$update_Necessary=0;}

            $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
            $get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);

            if(isset($get_Field_Id[$i])){
                $wpdb->update(
                    "$tablename_create_user_form",
                    array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
                        'Field_Content' => $get_Field_Content[$i],'Min_Char' => $get_Min_Char[$i],'Max_Char' => $get_Max_Char[$i], 'Required' => $update_Necessary, 'Active' => $Active,
                        'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
                    array('id' => $get_Field_Id[$i]),
                    array('%d','%s',
                        '%s','%d','%d','%d','%d','%d','%d','%d','%d','%d'),
                    array('%d')
                );
            }
            else{

                $wpdb->query( $wpdb->prepare(
                    "
                                INSERT INTO $tablename_create_user_form
                                ( id,GalleryID , Field_Type, Field_Order,
                                Field_Name,Field_Content,Min_Char,Max_Char,
                                Required,Active,GeneralID,
                                 RowNumber,ColNumber,RowCols,PinField,PublicProfile)
                                VALUES ( %s,%d,%s,%d,
                                %s,%s,%d,%d,
                                %d,%d,%d,
                                %d,%d,%d,%d,%d)
                            ",
                    '',$GeneralIDtoInsert,'wpln',$i,
                    $get_Field_Name[$i],$get_Field_Content[$i],$get_Min_Char[$i],$get_Max_Char[$i],
                    $update_Necessary,$Active,$GeneralID,
                    $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
                ) );

            }

        }

        if($value=="user-text-field" || $value=="user-url-field"){

            $fieldOrder++;
            $Active = 1;
            if(!empty($get_Hide[$i])){
                $Active = 0;
            }

            $PinField = 0;
            if(!empty($get_PinField[$i])){
                $PinField = 1;
            }

            if(!isset($get_Necessary[$i])){
                $get_Necessary[$i] = false;
            }

            if($get_Necessary[$i]=='on'){$update_Necessary=1;}
            else{$update_Necessary=0;}

            $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
            $get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);

            if(isset($get_Field_Id[$i])){
                $wpdb->update(
                    "$tablename_create_user_form",
                    array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
                        'Field_Content' => $get_Field_Content[$i],'Min_Char' => $get_Min_Char[$i],'Max_Char' => $get_Max_Char[$i], 'Required' => $update_Necessary, 'Active' => $Active,
                        'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
                    array('id' => $get_Field_Id[$i]),
                    array('%d','%s',
                        '%s','%d','%d','%d','%d','%d','%d','%d','%d','%d'),
                    array('%d')
                );
            }
            else{

                $wpdb->query( $wpdb->prepare(
                    "
                                INSERT INTO $tablename_create_user_form
                                ( id, GalleryID, Field_Type, Field_Order,
                                Field_Name,Field_Content,Min_Char,Max_Char,
                                Required,Active,GeneralID,
                                 RowNumber,ColNumber,RowCols,PinField,PublicProfile)
                                VALUES ( %s,%d,%s,%d,
                                %s,%s,%d,%d,
                                %d,%d,%d,
                                %d,%d,%d,%d,%d)
                            ",
                    '',$GeneralIDtoInsert,$value,$i,
                    $get_Field_Name[$i],$get_Field_Content[$i],$get_Min_Char[$i],$get_Max_Char[$i],
                    $update_Necessary,$Active,$GeneralID,
                    $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
                ) );

            }

        }
		
		if($value=="user-check-agreement-field"){

            $fieldOrder++;
            $Active = 1;
            if(!empty($get_Hide[$i])){
                $Active = 0;
            }

            $PinField = 0;
            if(!empty($get_PinField[$i])){
                $PinField = 1;
            }

            $update_Necessary=1;// so far check agreement is always required

            $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
            $get_Field_Content[$i] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);

            if(!isset($get_Field_Content[$i])){
                $get_Field_Content[$i] = '';
            }

            if(isset($get_Field_Id[$i])){
						$wpdb->update(
						"$tablename_create_user_form",
						array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
						'Field_Content' => $get_Field_Content[$i],'Min_Char' => 0,'Max_Char' => 0, 'Required' => $update_Necessary, 'Active' => $Active,
                            'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
						array('id' => $get_Field_Id[$i]),
						array('%d','%s',
						'%s','%s','%s','%d','%d','%d','%d','%d','%d','%d'),
						array('%d')
						);					
					}
					else{						
						$wpdb->query( $wpdb->prepare(
						"
							INSERT INTO $tablename_create_user_form
							( id, GalleryID, Field_Type, Field_Order,
							Field_Name,Field_Content,Min_Char,Max_Char,
							Required,Active,GeneralID,
                             RowNumber,ColNumber,RowCols,PinField,PublicProfile)
							VALUES ( %s,%d,%s,%d,
							%s,%s,%d,%d,
							%d,%d,%d,
                            %d,%d,%d,%d,%d)
						",
							'',$GeneralIDtoInsert,'user-check-agreement-field',$i,
							$get_Field_Name[$i],$get_Field_Content[$i],'','',
                            $update_Necessary,$Active,$GeneralID,
                            $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
						) );						
					}			

		}


		if($value=="user-robot-field"){

                    $fieldOrder++;
            $Active = 1;
            if(!empty($get_Hide[$i])){
                $Active = 0;
            }

            $PinField = 0;
            if(!empty($get_PinField[$i])){
                $PinField = 1;
            }


					$get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);

					if(isset($get_Field_Id[$i])){


						$wpdb->update(
                            "$tablename_create_user_form",
                            array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
                            'Field_Content' => '','Min_Char' => '','Max_Char' => '', 'Active' => $Active,
                                'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
                            array('id' => $get_Field_Id[$i]),
                            array('%d','%s',
                            '%s','%s','%s','%d','%d','%d','%d','%d','%d'),
                            array('%d')
						);
					}
					else{

						$wpdb->query( $wpdb->prepare(
						"
							INSERT INTO $tablename_create_user_form
							( id, GalleryID, Field_Type, Field_Order,
							Field_Name,Field_Content,Min_Char,Max_Char,
							Required,Active,GeneralID,
                             RowNumber,ColNumber,RowCols,PinField,PublicProfile)
							VALUES ( %s,%d,%s,%d,
							%s,%s,%d,%d,
							%d,%d,%d,
                            %d,%d,%d,%d,%d)
						",
							'',$GeneralIDtoInsert,'user-robot-field',$i,
							$get_Field_Name[$i],'','','',
							1,$Active,$GeneralID,
                            $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
						) );
					}

		}

		if($value=="user-robot-recaptcha-field"){

                    $fieldOrder++;
            $Active = 1;
            if(!empty($get_Hide[$i])){
                $Active = 0;
            }

            $PinField = 0;
            if(!empty($get_PinField[$i])){
                $PinField = 1;
            }

					$get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method((isset($get_Field_Name[$i])) ? $get_Field_Name[$i] : '');

					if(isset($get_Field_Id[$i])){
						$wpdb->update(
                            "$tablename_create_user_form",
                            array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
                            'Field_Content' => '','Min_Char' => '','Max_Char' => '', 'Active' => $Active, 'ReCaKey' => $get_ReCaKey, 'ReCaLang' => $get_ReCaLang,
                                'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
                            array('id' => $get_Field_Id[$i]),
                            array('%d','%s',
                            '%s','%s','%s','%d','%s','%s','%d','%d','%d','%d','%d'),
                            array('%d')
						);
					}
					else{

						$wpdb->query( $wpdb->prepare(
						"
							INSERT INTO $tablename_create_user_form
							( id, GalleryID, Field_Type, Field_Order,
							Field_Name,Field_Content,Min_Char,Max_Char,
							Required,Active,ReCaKey,ReCaLang,GeneralID,
                                 RowNumber,ColNumber,RowCols,PinField,PublicProfile)
							VALUES ( %s,%d,%s,%d,
							%s,%s,%d,%d,
							%d,%d,%s,%s,%d,
                                %d,%d,%d,%d,%d)
						",
							'',$GeneralIDtoInsert,'user-robot-recaptcha-field',$i,
							$get_Field_Name[$i],'','','',
							1,$Active,$get_ReCaKey,$get_ReCaLang,$GeneralID,
                            $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
						) );
					}
            $cgCaptchaId = !empty($get_Field_Id[$i]) ? absint($get_Field_Id[$i]) : absint($wpdb->insert_id);
            cg_captcha_store_config('registry',$cgCaptchaId,isset($cgCaptchaConfigs[$i]) ? $cgCaptchaConfigs[$i] : null);

		}
	
			if($value=="user-comment-field"){

                $fieldOrder++;
                $Active = 1;
                if(!empty($get_Hide[$i])){
                    $Active = 0;
                }

                $PinField = 0;
                if(!empty($get_PinField[$i])){
                    $PinField = 1;
                }
				
                $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
                $get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);


                if(!isset($get_Necessary[$i])){
                    $get_Necessary[$i] = false;
                }

				if($get_Necessary[$i]=='on'){$update_Necessary=1;}
				else{$update_Necessary=0;}
			
					if(isset($get_Field_Id[$i])){

						$wpdb->update(
						"$tablename_create_user_form",
						array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
						'Field_Content' => $get_Field_Content[$i],'Min_Char' => $get_Min_Char[$i],'Max_Char' => $get_Max_Char[$i], 'Required' => $update_Necessary, 'Active' => $Active,
                            'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
						array('id' => $get_Field_Id[$i]),
						array('%d','%s',
						'%s','%d','%d','%d','%d','%d','%d','%d','%d','%d'),
						array('%d')
						);							
					}
					else{					
						$wpdb->query( $wpdb->prepare(
						"
							INSERT INTO $tablename_create_user_form
							( id, GalleryID, Field_Type, Field_Order,
							Field_Name,Field_Content,Min_Char,Max_Char,
							Required,Active,GeneralID,
                                 RowNumber,ColNumber,RowCols,PinField,PublicProfile)
							VALUES ( %s,%d,%s,%d,
							%s,%s,%d,%d,
							%d,%d,%d,
                                %d,%d,%d,%d,%d)
						",
							'',$GeneralIDtoInsert,'user-comment-field',$i,
							$get_Field_Name[$i],$get_Field_Content[$i],$get_Min_Char[$i],$get_Max_Char[$i],
							$update_Necessary,$Active,$GeneralID,
                            $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
						) );						
					}
		
		}
	


	if($value=="user-select-field"){

        $fieldOrder++;
        $Active = 1;
        if(!empty($get_Hide[$i])){
            $Active = 0;
        }

        $PinField = 0;
        if(!empty($get_PinField[$i])){
            $PinField = 1;
        }

        if(!isset($get_Necessary[$i])){
            $get_Necessary[$i] = false;
        }

        if($get_Necessary[$i]=='on'){$update_Necessary=1;}
        else{$update_Necessary=0;}

            // to go sure, to avoid eventually error 05 December 2021
            $get_Field_Name[$i] = (!empty($get_Field_Name[$i])) ? $get_Field_Name[$i] : '';

            $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
            $get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);

            if(isset($get_Field_Id[$i])){
                $wpdb->update(
                "$tablename_create_user_form",
                array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
                'Field_Content' => $get_Field_Content[$i],'Min_Char' => '','Max_Char' => '', 'Required' => $update_Necessary, 'Active' => $Active,
                    'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
                array('id' => $get_Field_Id[$i]),
                array('%d','%s',
                '%s','%s','%s','%d','%d','%d','%d','%d','%d','%d'),
                array('%d')
                );
            }
            else{
                $wpdb->query( $wpdb->prepare(
                "
                    INSERT INTO $tablename_create_user_form
                    ( id, GalleryID, Field_Type, Field_Order,
                    Field_Name,Field_Content,Min_Char,Max_Char,
                    Required,Active,GeneralID,
                         RowNumber,ColNumber,RowCols,PinField,PublicProfile)
                    VALUES ( %s,%d,%s,%d,
                    %s,%s,%s,%s,
                    %d,%d,%d,
                        %d,%d,%d,%d,%d)
                ",
                    '',$GeneralIDtoInsert,'user-select-field',$i,
                    $get_Field_Name[$i],$get_Field_Content[$i],'','',
                    $update_Necessary,$Active,$GeneralID,
                    $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
                ) );

            }


	}


	if($value=="user-check-field"){

        $fieldOrder++;
        $Active = 1;
        if(!empty($get_Hide[$i])){
            $Active = 0;
        }

        $PinField = 0;
        if(!empty($get_PinField[$i])){
            $PinField = 1;
        }

        if(!isset($get_Necessary[$i])){
            $get_Necessary[$i] = false;
        }

				if($get_Necessary[$i]=='on'){$update_Necessary=1;}
				else{$update_Necessary=0;}

				    // to go sure, to avoid eventually error 05 December 2021
                    $get_Field_Name[$i] = (!empty($get_Field_Name[$i])) ? $get_Field_Name[$i] : '';

					$get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
					$get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);

					if(isset($get_Field_Id[$i])){
						$wpdb->update(
						"$tablename_create_user_form",
						array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
						'Field_Content' => $get_Field_Content[$i],'Min_Char' => '','Max_Char' => '', 'Required' => $update_Necessary, 'Active' => $Active,
                            'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
						array('id' => $get_Field_Id[$i]),
						array('%d','%s',
						'%s','%s','%s','%d','%d','%d','%d','%d','%d','%d'),
						array('%d')
						);
					}
					else{
						$wpdb->query( $wpdb->prepare(
						"
							INSERT INTO $tablename_create_user_form
							( id, GalleryID, Field_Type, Field_Order,
							Field_Name,Field_Content,Min_Char,Max_Char,
							Required,Active,GeneralID,
                                 RowNumber,ColNumber,RowCols,PinField,PublicProfile)
							VALUES ( %s,%d,%s,%d,
							%s,%s,%s,%s,
							%d,%d,%d,
                                %d,%d,%d,%d,%d)
						",
                    '',$GeneralIDtoInsert,'user-check-field',$i,
                    $get_Field_Name[$i],$get_Field_Content[$i],'','',
                    $update_Necessary,$Active,$GeneralID,
                    $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
                ) );

            }


	}

	if($value=="user-radio-field"){

        $fieldOrder++;
        $Active = 1;
        if(!empty($get_Hide[$i])){
            $Active = 0;
        }

        $PinField = 0;
        if(!empty($get_PinField[$i])){
            $PinField = 1;
        }

        $update_Necessary=1;// radio is always required

        // to go sure, to avoid eventually error 05 December 2021
        $get_Field_Name[$i] = (!empty($get_Field_Name[$i])) ? $get_Field_Name[$i] : '';

        $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
        $get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);

        if(isset($get_Field_Id[$i])){
            $wpdb->update(
            "$tablename_create_user_form",
            array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
            'Field_Content' => $get_Field_Content[$i],'Min_Char' => '','Max_Char' => '', 'Required' => $update_Necessary, 'Active' => $Active,
                'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
            array('id' => $get_Field_Id[$i]),
            array('%d','%s',
            '%s','%s','%s','%d','%d','%d','%d','%d','%d','%d'),
            array('%d')
            );
        }
        else{
            $wpdb->query( $wpdb->prepare(
            "
                INSERT INTO $tablename_create_user_form
                ( id, GalleryID, Field_Type, Field_Order,
                Field_Name,Field_Content,Min_Char,Max_Char,
                Required,Active,GeneralID,
                     RowNumber,ColNumber,RowCols,PinField,PublicProfile)
                VALUES ( %s,%d,%s,%d,
                %s,%s,%s,%s,
                %d,%d,%d,
                    %d,%d,%d,%d,%d)
            ",
                '',$GeneralIDtoInsert,'user-radio-field',$i,
							$get_Field_Name[$i],$get_Field_Content[$i],'','',
							$update_Necessary,$Active,$GeneralID,
                            $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
						) );

					}


	}

	if($value=="main-user-name"){


        $PinField = 0;
        if(!empty($get_PinField[$i])){
            $PinField = 1;
        }

					$get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
					$get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);

						if($get_Field_Id[$i]){
							$wpdb->update(
							"$tablename_create_user_form",
							array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
							'Field_Content' => $get_Field_Content[$i],'Min_Char' => $get_Min_Char[$i],'Max_Char' => $get_Max_Char[$i],
                                'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
							array('id' => $get_Field_Id[$i]),
							array('%d','%s',
							'%s','%s','%s',
                            '%d','%d','%d','%d','%d'),
							array('%d')
							);
						}
						
		}

	if($value=="main-nick-name"){



        $PinField = 0;
        if(!empty($get_PinField[$i])){
            $PinField = 1;
        }

					$get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
					$get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);

						if($get_Field_Id[$i]){
							$wpdb->update(
							"$tablename_create_user_form",
							array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
							'Field_Content' => $get_Field_Content[$i],'Min_Char' => $get_Min_Char[$i],'Max_Char' => $get_Max_Char[$i],
                                'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
							array('id' => $get_Field_Id[$i]),
							array('%d','%s',
							'%s','%s','%s',
                            '%d','%d','%d','%d','%d'),
							array('%d')
							);
						}

	}
	
	if($value=="main-mail"){

        $PinField = 0;
        if(!empty($get_PinField[$i])){
            $PinField = 1;
        }
		
            $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
            $get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);


        if(!isset($get_Field_Content[$i])){
            $get_Field_Content[$i] = '';
        }

        if($get_Field_Id[$i]){
                $wpdb->update(
                "$tablename_create_user_form",
                array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
                'Field_Content' => $get_Field_Content[$i],'Min_Char' => 0,'Max_Char' => 0,
                    'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
                array('id' => $get_Field_Id[$i]),
                array('%d','%s',
                '%s','%s','%s',
                    '%d','%d','%d','%d','%d'),
                array('%d')
                );
        }
		
						
	}
	
	if($value=="password"){

        $PinField = 0;
        if(!empty($get_PinField[$i])){
            $PinField = 1;
        }
		
					$get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
					$get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);
		
					if($get_Field_Id[$i]){
						
						$wpdb->update(
							"$tablename_create_user_form",
							array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
							'Field_Content' => $get_Field_Content[$i],'Min_Char' => $get_Min_Char[$i],'Max_Char' => $get_Max_Char[$i],
                            'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
							array('id' => $get_Field_Id[$i]),
							array('%d','%s',
							'%s','%s','%s',
                            '%d','%d','%d','%d','%d'),
							array('%d')
						);

					}
					
		}
	    if($value=="password-confirm"){

            $PinField = 0;
            if(!empty($get_PinField[$i])){
                $PinField = 1;
            }

					$get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
					$get_Field_Content[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);
		
					if($get_Field_Id[$i]){
						$wpdb->update(
						"$tablename_create_user_form",
						array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
						'Field_Content' => $get_Field_Content[$i],'Min_Char' => $get_Min_Char[$i],'Max_Char' => $get_Max_Char[$i],
                        'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
						array('id' => $get_Field_Id[$i]),
						array('%d','%s',
						'%s','%s','%s',
                        '%d','%d','%d','%d','%d'),
						array('%d')
						);							
					}
		}

	if($value=="user-html-field" && $cgProVersion){

        $fieldOrder++;
        $Active = 1;
        if(!empty($get_Hide[$i])){
            $Active = 0;
        }

        $PinField = 0;
        if(!empty($get_PinField[$i])){
            $PinField = 1;
        }

        $get_Field_Name[$i]=contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Name[$i]);
                $get_Field_Content[$i] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($get_Field_Content[$i]);


        if(!empty($get_Field_Id[$i])){

						$wpdb->update(
						"$tablename_create_user_form",
						array('Field_Order' => $i, 'Field_Name' => $get_Field_Name[$i],
						'Field_Content' => $get_Field_Content[$i],'Min_Char' => '','Max_Char' => '', 'Required' => '', 'Active' => $Active,
                        'RowNumber' => $get_RowNumber[$i],'ColNumber' => $get_ColNumber[$i],'RowCols' => $get_RowCols[$i],'PinField' => $PinField, 'PublicProfile' => $PublicProfile),
						array('id' => $get_Field_Id[$i]),
						array('%d','%s',
						'%s','%s','%s','%d','%d',
                        '%d','%d','%d','%d','%d'),
						array('%d')
						);
						
					}
					else{			
						$wpdb->query( $wpdb->prepare(
						"
							INSERT INTO $tablename_create_user_form
							( id, GalleryID, Field_Type, Field_Order,
							Field_Name,Field_Content,Min_Char,Max_Char,
							Required,Active,GeneralID,
                                 RowNumber,ColNumber,RowCols,PinField,PublicProfile)
							VALUES ( %s,%d,%s,%d,
							%s,%s,%d,%d,
							%d,%d,%d,
                                %d,%d,%d,%d,%d)
						",
							'',$GeneralIDtoInsert,'user-html-field',$i,
							$get_Field_Name[$i],$get_Field_Content[$i],'','',
							'',$Active,$GeneralID,
                            $get_RowNumber[$i],$get_ColNumber[$i],$get_RowCols[$i],$PinField,$PublicProfile
						) );						
					}

		}

}

}

if(intval($galleryDbVersion)>=14){
    $selectFormInput = $wpdb->get_results("SELECT * FROM $tablename_create_user_form WHERE GeneralID = 1 ORDER BY Field_Order ASC");
}else{// then old form
    $selectFormInput = $wpdb->get_results($wpdb->prepare( "SELECT * FROM $tablename_create_user_form WHERE GalleryID = %d ORDER BY Field_Order ASC",[$GalleryID]));
}

$checkDataFormOutput = $wpdb->get_results($wpdb->prepare( "SELECT * FROM $tablename_create_user_form WHERE GalleryID = %d and (Field_Type = 'comment-f' or Field_Type = 'text-f' or Field_Type = 'email-f')",[$GalleryID]));

//print_r($checkDataFormOutput);

$rowVisualOptions = $wpdb->get_row($wpdb->prepare( "SELECT * FROM $tablename_options_visual WHERE GalleryID = %d",[$GalleryID]));

$Field1IdGalleryView = $rowVisualOptions->Field1IdGalleryView;


// Pr�fen ob es ein Feld gibt welches als Images URL genutzt werden soll
//$Use_as_URL = $wpdb->get_var("SELECT Use_as_URL FROM $tablename_create_user_form WHERE GalleryID = '$GalleryID' AND Use_as_URL = '1'");
//$Use_as_URL_id = $wpdb->get_var("SELECT id FROM $tablename_create_user_form WHERE GalleryID = '$GalleryID' AND Use_as_URL = '1'");




?>
