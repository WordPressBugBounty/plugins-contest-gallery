<?php
if(!defined('ABSPATH')){exit;}

global $wpdb;// Important! Do not remove!

$checkCGMailResult = 0;

if(!empty($checkCgMail)){
    if($checkCgMail->Confirmed==1){
        $checkCGMailResult=1;
    }
}


if(!empty($checkWpMail)){
        $checkCGMailResult=1;
}

if(empty($checkWpMail) AND $checkCGMailResult==0){


        $Subject = contest_gal1ery_convert_for_html_output_without_nl2br($mailConfSettings->Header);
        $Admin = $mailConfSettings->Admin;
        $FromMail = $mailConfSettings->FromMail;
        $Reply = $mailConfSettings->Reply;
        $cc = $mailConfSettings->CC;
        $bcc = $mailConfSettings->BCC;
        $contentMail = contest_gal1ery_convert_for_html_output_without_nl2br(@$mailConfSettings->Content);
        $Msg = $contentMail;

        $url = $mailConfSettings->URL;
        if(!empty($url)){
            $url = (strpos($url,'?')) ? $url.'&' : $url.'?';
        }

        $posUrl = strtolower('$url$');

        $codedMail =md5($userMail);

        $confirmationUrl = '';
        if(stripos($contentMail,$posUrl)!==false){

            $confirmationUrl = $url."confirmation_code=$codedMail";
            $Msg = str_ireplace($posUrl, $confirmationUrl, $contentMail);

        }


        $headers = array();
        $mailAddresses = cg_add_mail_from_reply_headers($headers,$Admin,$FromMail,$Reply);
        $headers = $mailAddresses['headers'];

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
        $cgMailAction = "E-mail confirmation e-mail";
        $cgMailGalleryId = $galeryID;
        add_action( 'wp_mail_failed', 'cg_on_wp_mail_error', 10, 1 );

        cg_upload_job_mail($userMail, $Subject, $Msg, $headers);
     //   var_dump($Subject);

        if(!empty($checkCgMail)){
            $wpdb->update(
                "$tablename_mails_collected",
                array(
                    'Timestamp' => time(),
                    'Link' => $confirmationUrl
                    ),
                array('Mail' => $userMail),
                array('%d','%s'),
                array('%s')
            );

        }
        else{

            $wpdb->query( $wpdb->prepare(
                "
									INSERT INTO $tablename_mails_collected
									( id, GalleryID, Mail, Hash, 
									Confirmed, Timestamp, Link)
									VALUES ( %s,%d,%s,%s,
									%d,%d,%s ) 
								",
                '',$galeryID,$userMail,$codedMail,
                0,time(),$confirmationUrl
            ));

        }


}


?>
