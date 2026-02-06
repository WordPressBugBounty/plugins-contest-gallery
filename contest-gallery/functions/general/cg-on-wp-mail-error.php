<?php

// wp_mail show fail
// IMPORTANT!!!: be carefull to add_action for this!!!! Would be executed for every plugin
// and every fail for whole WordPress instance
if(!function_exists('cg_on_wp_mail_error')){
    function cg_on_wp_mail_error( $wp_error ) {

        global $cgMailAction;
        global $cgMailGalleryId;
        global $cgIsGeneral;

        $uploadFolder = wp_upload_dir();

        if(!empty($cgIsGeneral) || $cgMailGalleryId==0){
            $galleryErrorsFolder = $uploadFolder['basedir'].'/contest-gallery/gallery-general/logs/errors';
        }else{
            $galleryErrorsFolder = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$cgMailGalleryId.'/logs/errors';
        }

        if(!is_dir($galleryErrorsFolder)){
            mkdir($galleryErrorsFolder,0755,true);
        }

        $htaccessFile = $galleryErrorsFolder.'/.htaccess';

        if(!file_exists($htaccessFile)){
$usersTableHtmlStart = <<<HEREDOC
<Files "*.log">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</Files>
HEREDOC;

            $fp = fopen($htaccessFile, 'w');
            fwrite($fp, $usersTableHtmlStart);
            fclose($fp);
            chmod($htaccessFile, 0640);// no read for others!!!
        }

        $fileName = md5(wp_salt( 'auth').'---cnglog---'.$cgMailGalleryId);
        $file = $galleryErrorsFolder.'/mail-'.$fileName.'.log';

        $errorsFileContentBefore = '';

        if(file_exists($file)){
            $fp = fopen($file, 'r');
            $errorsFileContentBefore = fread($fp, filesize($file));
            fclose($fp);
        }

        $errorsFileContent = date('Y-m-d H:i:s')." (server-time)\r\n";
		if(!empty($cgMailGalleryId)){
			$errorsFileContent = $errorsFileContent.$cgMailAction." - Gallery ID $cgMailGalleryId\r\n";
		}else{
			$errorsFileContent = $errorsFileContent.$cgMailAction."\r\n";
		}
        $errorsFileContent = $errorsFileContent.'ERROR: '.$wp_error->errors['wp_mail_failed'][0]."\r\n";

        if(!empty($wp_error->errors['wp_mail_failed'][1])){
            $errorsFileContent = $errorsFileContent.'ERROR: '.$wp_error->errors['wp_mail_failed'][1]."\r\n";
        }
        if(!empty($wp_error->errors['wp_mail_failed'][2])){
            $errorsFileContent = $errorsFileContent.'ERROR: '.$wp_error->errors['wp_mail_failed'][2]."\r\n";
        }
        if(!empty($wp_error->errors['wp_mail_failed'][3])){
            $errorsFileContent = $errorsFileContent.'ERROR: '.$wp_error->errors['wp_mail_failed'][3]."\r\n";
        }

        $mimeVersion = '';
        $headers = isset($wp_error->error_data['wp_mail_failed']['headers'])
            ? $wp_error->error_data['wp_mail_failed']['headers']
            : '';

        if (is_array($headers) && !empty($headers['MIME-Version'])) {
            $mimeVersion = $headers['MIME-Version'];
        }


        $subject = isset($wp_error->error_data['wp_mail_failed']['subject'])
            ? (string) $wp_error->error_data['wp_mail_failed']['subject']
            : '';


        $toLog = '';

        if (!empty($wp_error->error_data['wp_mail_failed']['to'])) {
            $toField = $wp_error->error_data['wp_mail_failed']['to'];

            // If "to" is an array (like in your dump)
            if (is_array($toField)) {
                $parts = [];

                foreach ($toField as $address) {
                    if (is_object($address)) {
                        // Post SMTP: PostmanEmailAddress object
                        if (method_exists($address, 'getEmail')) {
                            $parts[] = $address->getEmail();
                        } elseif (method_exists($address, 'getEmailAddress')) {
                            $parts[] = $address->getEmailAddress();
                        } else {
                            // Fallback: log the class name instead of crashing
                            $parts[] = '[object ' . get_class($address) . ']';
                        }
                    } else {
                        // Normal string address
                        $parts[] = (string) $address;
                    }
                }

                $toLog = implode(', ', $parts);
            } else {
                // "to" is already a scalar/string
                $toLog = (string) $toField;
            }
        }

        $phpCode = isset($wp_error->error_data['wp_mail_failed']['phpmailer_exception_code'])
            ? $wp_error->error_data['wp_mail_failed']['phpmailer_exception_code']
            : '';


        $errorsFileContent = $errorsFileContent.'Send to: '.$toLog."\r\n";
        $errorsFileContent = $errorsFileContent.'Subject: '.$subject."\r\n";
        $errorsFileContent = $errorsFileContent.'Headers Mime-Version: '.$mimeVersion."\r\n";
        $errorsFileContent = $errorsFileContent.'phpmailer_exception_code: '.$phpCode."\r\n\r\n";
        $errorsFileContent = $errorsFileContent.$errorsFileContentBefore;

        $fp = fopen($file, 'w');
        fwrite($fp, $errorsFileContent);
        fclose($fp);
        chmod($file, 0640);// no read for others!!!
        
    }
}

// wp_mail show fail --- ENDE
