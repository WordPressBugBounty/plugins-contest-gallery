<?php
if(!defined('ABSPATH')){exit;}

// Inform Admin if configured

if (!function_exists('contest_gal1ery_mail_admin'))   {
    function contest_gal1ery_mail_admin($selectSQLemailAdmin,$Msg,$galeryID) {

        $Subject = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemailAdmin->Header);
        $Admin = $selectSQLemailAdmin->Admin;
        $AdminMail = $selectSQLemailAdmin->AdminMail;
        $Reply = $selectSQLemailAdmin->Reply;
        $cc = $selectSQLemailAdmin->CC;
        $bcc = $selectSQLemailAdmin->BCC;

        $headers = array();
        $headers[] = "From: $Admin <". html_entity_decode(strip_tags($Reply)) . ">\r\n";
        $headers[] = "Reply-To: ". strip_tags($Reply) . "\r\n";

        if(strpos($cc,';')){
            $cc = explode(';',$cc);
            foreach($cc as $ccValue){
                $ccValue = trim($ccValue);
                $headers[] = "CC: $ccValue\r\n";
            }
        }
        else{
            $headers[] = "CC: $cc\r\n";
        }

        if(strpos($bcc,';')){
            $bcc = explode(';',$bcc);
            foreach($bcc as $bccValue){
                $bccValue = trim($bccValue);
                $headers[] = "BCC: $bccValue\r\n";
            }
        }
        else{
            $headers[] = "BCC: $bcc\r\n";
        }

        $headers[] = "MIME-Version: 1.0\r\n";
        $headers[] = "Content-Type: text/html; charset=utf-8\r\n";

        global $cgMailAction;
        global $cgMailGalleryId;
        $cgMailAction = "E-mail to administrator after upload";
        $cgMailGalleryId = $galeryID;
        add_action( 'wp_mail_failed', 'cg_on_wp_mail_error', 10, 1 );

        wp_mail($AdminMail, $Subject, $Msg, $headers);

    }
}

add_action('contest_gal1ery_mail_admin', 'contest_gal1ery_mail_admin',2,3);

$tablename_mail_admin = $wpdb->prefix . "contest_gal1ery_mail_admin";
$selectSQLemailAdmin = $wpdb->get_row( "SELECT * FROM $tablename_mail_admin WHERE GalleryID = '$galeryID'" );

// Iterate through all image IDs that were just newly created
foreach($collectImageIDs as $key => $imageID){

    $UserEntries = '<br/>';

    if(!empty($userData)){
        $UserEntries .= "<strong>Registered user email:</strong><br/>";
        $UserEntries .= $userData->user_email."<br/>";
    }

    if(!empty($categoryId)){
        $categoryName = $wpdb->get_var("SELECT Name FROM $tablename_categories WHERE id='$categoryId' and Active='1'");
        $UserEntries .= "<br/><strong>Category:</strong><br/>";
        $UserEntries .= contest_gal1ery_convert_for_html_output_without_nl2br($categoryName)."<br/>";
    }

    $contentMail = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemailAdmin->Content);

    $posUserInfo = "\$info\$";

    if(stripos($contentMail,$posUserInfo)!==false){

        $selectFormInput = $wpdb->get_results( "SELECT id, Field_Type, Field_Order, Field_Content FROM $tablename_f_input WHERE GalleryID = '$galeryID' AND (Field_Type = 'text-f' OR Field_Type = 'comment-f' OR Field_Type ='email-f' OR Field_Type ='select-f' OR Field_Type ='radio-f' OR Field_Type = 'chk-f' OR Field_Type ='url-f') ORDER BY Field_Order ASC" );

        $selectContentFieldArray = array();

        foreach ($selectFormInput as $value) {

            // 1. Field Type
            // 2. ID of the field in F_INPUT
            // 3. Field Order
            // 4. Field Content

            $selectFieldType = 	$value->Field_Type;
            $id = $value->id;// primary unique id of the form is also saved and later inserted in entries to recognize the used form field
            $fieldOrder = $value->Field_Order;// The original Field order in f_input table. For standardization purposes.
            $selectContentFieldArray[] = $selectFieldType;
            $selectContentFieldArray[] = $id;
            $selectContentFieldArray[] = $fieldOrder;
            $selectContentField = unserialize($value->Field_Content);
            $selectContentFieldArray[] = $selectContentField["titel"];

        }

        // do not remove, might be not instantly available in first loop
        $fieldtype="";

        foreach($selectContentFieldArray as $key => $formvalue){

            //	echo "<br>$i<br>";
            // 1. Field Type
            // 2. ID of the field in F_INPUT
            // 3. Field Order
            // 4. Field Content

            if($formvalue=='text-f'){$fieldtype="nf"; $n=1; continue;}
            if($fieldtype=="nf" AND $n==1){$formFieldId=$formvalue; $n=2; continue;}
            if($fieldtype=="nf" AND $n==2){$fieldOrder=$formvalue; $n=3; continue;}
            if ($fieldtype=="nf" AND $n==3) {

                $getEntries = $wpdb->get_var( $wpdb->prepare(
                    "
															SELECT Short_Text
															FROM $tablenameentries 
															WHERE pid = %d and f_input_id = %d
														",
                    $imageID,$formFieldId
                ) );

                $UserEntries .= "<br/><strong>".contest_gal1ery_convert_for_html_output_without_nl2br($formvalue).":</strong><br/>";
                $UserEntries .= contest_gal1ery_convert_for_html_output_without_nl2br($getEntries)."<br/>";

                $fieldtype='';
                $i=0;

            }

            if($formvalue=='email-f'){$fieldtype="ef";  $n=1; continue;}
            if($fieldtype=="ef" AND $n==1){$formFieldId=$formvalue; $n=2; continue;}
            if($fieldtype=="ef" AND $n==2){$fieldOrder=$formvalue; $n=3; continue;}
            if ($fieldtype=='ef' AND $n==3) {

                $getEntries = $wpdb->get_var( $wpdb->prepare(
                    "
															SELECT Short_Text
															FROM $tablenameentries 
															WHERE pid = %d and f_input_id = %d
														",
                    $imageID,$formFieldId
                ) );

                if(empty($userData)){
                    $UserEntries .= "<br/><strong>".contest_gal1ery_convert_for_html_output_without_nl2br($formvalue).":</strong><br/>";
                    $UserEntries .= contest_gal1ery_convert_for_html_output_without_nl2br($getEntries)."<br/>";
                }

                $fieldtype='';
                $i=0;


            }

            if($formvalue=='select-f'){$fieldtype="se";  $n=1; continue;}
            if($fieldtype=="se" AND $n==1){$formFieldId=$formvalue; $n=2; continue;}
            if($fieldtype=="se" AND $n==2){$fieldOrder=$formvalue; $n=3; continue;}
            if ($fieldtype=='se' AND $n==3) {

                $getEntries = $wpdb->get_var( $wpdb->prepare(
                    "
															SELECT Short_Text
															FROM $tablenameentries 
															WHERE pid = %d and f_input_id = %d
														",
                    $imageID,$formFieldId
                ) );

                $UserEntries .= "<br/><strong>".contest_gal1ery_convert_for_html_output_without_nl2br($formvalue).":</strong><br/>";
                $UserEntries .= contest_gal1ery_convert_for_html_output_without_nl2br($getEntries)."<br/>";

                $fieldtype='';
                $i=0;

            }

            if($formvalue=='radio-f'){$fieldtype="ra";  $n=1; continue;}
            if($fieldtype=="ra" AND $n==1){$formFieldId=$formvalue; $n=2; continue;}
            if($fieldtype=="ra" AND $n==2){$fieldOrder=$formvalue; $n=3; continue;}
            if ($fieldtype=='ra' AND $n==3) {

                $getEntries = $wpdb->get_var( $wpdb->prepare(
                    "
															SELECT Short_Text
															FROM $tablenameentries 
															WHERE pid = %d and f_input_id = %d
														",
                    $imageID,$formFieldId
                ) );

                $UserEntries .= "<br/><strong>".contest_gal1ery_convert_for_html_output_without_nl2br($formvalue).":</strong><br/>";
                $UserEntries .= contest_gal1ery_convert_for_html_output_without_nl2br($getEntries)."<br/>";

                $fieldtype='';
                $i=0;

            }

            if($formvalue=='chk-f'){$fieldtype="chk";  $n=1; continue;}
            if($fieldtype=="chk" AND $n==1){$formFieldId=$formvalue; $n=2; continue;}
            if($fieldtype=="chk" AND $n==2){$fieldOrder=$formvalue; $n=3; continue;}
            if ($fieldtype=='chk' AND $n==3) {

                $getEntries = $wpdb->get_var( $wpdb->prepare(
                    "
															SELECT Short_Text
															FROM $tablenameentries 
															WHERE pid = %d and f_input_id = %d
														",
                    $imageID,$formFieldId
                ) );

                $UserEntries .= "<br/><strong>".contest_gal1ery_convert_for_html_output_without_nl2br($formvalue).":</strong><br/>";
                $UserEntries .= contest_gal1ery_convert_for_html_output_without_nl2br($getEntries)."<br/>";

                $fieldtype='';
                $i=0;

            }

            if($formvalue=='url-f'){$fieldtype="url";  $n=1; continue;}
            if($fieldtype=="url" AND $n==1){$formFieldId=$formvalue; $n=2; continue;}
            if($fieldtype=="url" AND $n==2){$fieldOrder=$formvalue; $n=3; continue;}
            if ($fieldtype=='url' AND $n==3) {

                $getEntries = $wpdb->get_var( $wpdb->prepare(
                    "
															SELECT Short_Text
															FROM $tablenameentries 
															WHERE pid = %d and f_input_id = %d
														",
                    $imageID,$formFieldId
                ) );

                $UserEntries .= "<br/><strong>".contest_gal1ery_convert_for_html_output_without_nl2br($formvalue).":</strong><br/>";
                $UserEntries .= contest_gal1ery_convert_for_html_output_without_nl2br($getEntries)."<br/>";

                $fieldtype='';
                $i=0;

            }

            if($formvalue=='comment-f'){$fieldtype="kf"; $n=1; continue;}
            if($fieldtype=="kf" AND $n==1){$formFieldId=$formvalue; $n=2; continue;}
            if($fieldtype=="kf" AND $n==2){$fieldOrder=$formvalue; $n=3; continue;}
            if ($fieldtype=='kf' AND $n==3) {

                $getEntries = $wpdb->get_var( $wpdb->prepare(
                    "
															SELECT Long_Text
															FROM $tablenameentries 
															WHERE pid = %d and f_input_id = %d
														",
                    $imageID,$formFieldId
                ));

                if(!empty($getEntries)){
                    $getEntries = nl2br($getEntries);
                    $UserEntries .= "<br/><strong>".contest_gal1ery_convert_for_html_output_without_nl2br($formvalue).":</strong><br/>";
                    $UserEntries .= contest_gal1ery_convert_for_html_output_without_nl2br($getEntries)."<br/>";
                }

                $fieldtype='';
                $i=0;

            }


        }

        // Collect data for User URL

        $wpImgId = $wpdb->get_var("SELECT WpUpload FROM $tablename1 WHERE GalleryID = '$galeryID' AND  id = '$imageID'");
        $selectImageData = $wpdb->get_var( "SELECT guid FROM $table_posts WHERE ID = '$wpImgId' ");
        $galleryName = $wpdb->get_var( "SELECT GalleryName FROM $tablenameOptions WHERE id = '$galeryID' ");

        if(!empty($AdditionalFilesArray) && count($AdditionalFilesArray)>1){
            $UserEntries .= "<br/><strong>Original Files URL:</strong><br/>";
            foreach ($AdditionalFilesArray as $keyOrder => $fileArray){
                if($keyOrder==1){
                    $UserEntries .= $selectImageData.'<br/>';
                }else{
                    $UserEntries .= $fileArray['guid'].'<br/>';
                }
            }
        }else{
            if(!empty($selectImageData)){// otherwise must be contact entry
                $UserEntries .= "<br/><strong>Original File URL:</strong><br/>";
                $UserEntries .= $selectImageData;
            }
        }

        $UserEntries .= "<br/><br/><strong>Gallery id:</strong> $galeryID<br/>";

        if(!empty($galleryName)){
            $UserEntries .= "<br/><strong>Gallery name:</strong> $galleryName<br/>";
        }

        if(stripos($contentMail,$posUserInfo)!==false){

            $Msg = str_ireplace($posUserInfo, $UserEntries, $contentMail);

        }else{
            $Msg = $contentMail;
        }

        if($proOptions->InformAdminAllowActivateDeactivate){
            if(empty(trim($proOptions->InformAdminActivationURL))){
                $Msg.= '<br>No "Page URL for activation/deactivation of an entry" configured';
            }else{
                $hash = cg_hash_function('---cngl1---'.$imageID);
                $Msg.= '<br><strong>Activate entry:</strong><br>';
                $linkOn = trim($proOptions->InformAdminActivationURL).'?cg_on_id='.$imageID.'&cg_hash='.$hash;
                $Msg.= "<a href='$linkOn' >$linkOn</a>";
                $Msg.= '<br><br><strong>Deactivate entry:</strong><br>';
                $linkOff = trim($proOptions->InformAdminActivationURL).'?cg_off_id='.$imageID.'&cg_hash='.$hash;
                $Msg.= "<a href='$linkOff' >$linkOff</a>";
            }
        }

        do_action( 'contest_gal1ery_mail_admin', $selectSQLemailAdmin,$Msg,$galeryID);

    }else{
        $Msg = $contentMail;

        if($proOptions->InformAdminAllowActivateDeactivate){
            if(empty(trim($proOptions->InformAdminActivationURL))){
                $Msg.= '<br>No "Page URL for activation/deactivation of an entry" configured';
            }else{
                $hash = cg_hash_function('---cngl1---'.$imageID);
                $Msg.= '<br><strong>Activate entry:</strong><br>';
                $linkOn = trim($proOptions->InformAdminActivationURL).'?cg_on_id='.$imageID.'&cg_hash='.$hash;
                $Msg.= "<a href='$linkOn' >$linkOn</a>";
                $Msg.= '<br><br><strong>Deactivate entry:</strong><br>';
                $linkOff = trim($proOptions->InformAdminActivationURL).'?cg_off_id='.$imageID.'&cg_hash='.$hash;
                $Msg.= "<a href='$linkOff' >$linkOff</a>";
            }
        }

        do_action( 'contest_gal1ery_mail_admin', $selectSQLemailAdmin,$Msg,$galeryID);

    }






}


?>