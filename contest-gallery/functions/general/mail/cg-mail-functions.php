<?php

if(!function_exists('cg_resolve_mail_from_reply')){
    function cg_resolve_mail_from_reply($fromMail,$replyMail){
        $fromMail = sanitize_email(trim(wp_specialchars_decode((string)$fromMail,ENT_QUOTES)));
        $replyMail = sanitize_email(trim(wp_specialchars_decode((string)$replyMail,ENT_QUOTES)));

        if(!is_email($fromMail)){
            $fromMail = '';
        }
        if(!is_email($replyMail)){
            $replyMail = '';
        }

        if(empty($fromMail) && !empty($replyMail)){
            $fromMail = $replyMail;
        }elseif(empty($replyMail) && !empty($fromMail)){
            $replyMail = $fromMail;
        }

        return array(
            'from' => $fromMail,
            'reply_to' => $replyMail
        );
    }
}

if(!function_exists('cg_add_mail_from_reply_headers')){
    function cg_add_mail_from_reply_headers($headers,$fromName,$fromMail,$replyMail,$replyName = ''){
        if(!is_array($headers)){
            $headers = array();
        }

        $resolved = cg_resolve_mail_from_reply($fromMail,$replyMail);
        $fromName = wp_strip_all_tags(wp_specialchars_decode((string)$fromName,ENT_QUOTES));
        $fromName = trim(str_replace(array("\r","\n"),'',$fromName));
        $replyName = wp_strip_all_tags(wp_specialchars_decode((string)$replyName,ENT_QUOTES));
        $replyName = trim(str_replace(array("\r","\n"),'',$replyName));

        if(!empty($resolved['from'])){
            $headers[] = !empty($fromName) ? 'From: '.$fromName.' <'.$resolved['from'].'>' : 'From: '.$resolved['from'];
        }
        if(!empty($resolved['reply_to'])){
            $headers[] = !empty($replyName) ? 'Reply-To: '.$replyName.' <'.$resolved['reply_to'].'>' : 'Reply-To: '.$resolved['reply_to'];
        }

        return array(
            'headers' => $headers,
            'from' => $resolved['from'],
            'reply_to' => $resolved['reply_to']
        );
    }
}

if (!function_exists('cg_save_sent_mail'))   {
    function cg_save_sent_mail($gid,$pid,$WpUserId,$ReceiverMail,$ReplyName,$ReplyMail,$FromName,$FromMail,$cc,$bcc,$subject, $body, $MailType) {

        global $wpdb;
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_mails = $wpdb->prefix . "contest_gal1ery_mails";

        $sent = true;

        if(cg_create_tablename_mails_check()){
            $charset_collate = $wpdb->get_charset_collate();
            $isShowError = true;
            $lastError = '';
            // Safer existence check (LIKE treats '_' as a wildcard, so escape it)
            $table_like = $wpdb->esc_like( $tablename_mails );
            $existing = $wpdb->get_var(
                $wpdb->prepare('SHOW TABLES LIKE %s', $table_like)
            );
            // This optional history table is installed lazily here. Use the same schema
            // to widen existing history when a longer mail first needs it.
            $needsLongContent = false;
            if($existing === $tablename_mails && (strlen($subject)>1000 || strlen($body)>65535)){
                $columns = $wpdb->get_results("SHOW COLUMNS FROM $tablename_mails",OBJECT_K);
                $needsLongContent = empty($columns['Subject']) || empty($columns['Body']) || strtolower($columns['Subject']->Type)!=='longtext' || strtolower($columns['Body']->Type)!=='longtext';
            }
            if ( $existing !== $tablename_mails || $needsLongContent ) {
                $sql = "CREATE TABLE $tablename_mails (
		id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		pid INT(20) NOT NULL DEFAULT 0,
		GalleryID INT(11) NOT NULL DEFAULT 0,
		WpUserId INT(11) NOT NULL DEFAULT 0,
		ReceiverMail VARCHAR(256) NOT NULL DEFAULT '',
		FromName VARCHAR(256) NOT NULL DEFAULT '',
		FromMail VARCHAR(256) NOT NULL DEFAULT '',
		ReplyName VARCHAR(256) NOT NULL DEFAULT '',
		ReplyMail VARCHAR(256) NOT NULL DEFAULT '',
		Cc TEXT NOT NULL,
		Bcc TEXT NOT NULL,
		Subject LONGTEXT NOT NULL,
		Body LONGTEXT NOT NULL,
		MailType VARCHAR(100) NOT NULL DEFAULT '',
		Tstamp INT(11) NOT NULL DEFAULT 0,
        KEY pid (pid),
        KEY WpUserId (WpUserId),
        KEY ReceiverMail (ReceiverMail),
        KEY MailType (MailType),
        KEY Tstamp (Tstamp),
        KEY GalleryID (GalleryID)
		) $charset_collate;"; // WordPress $charset_collate was added in 21.0.1
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
                cg_echo_last_sql_error($isShowError,$lastError);
            }
        }

        $Tstamp = time();

        $saved = $wpdb->query($wpdb->prepare(
            "
            INSERT INTO $tablename_mails
            ( 
             pid,GalleryID,WpUserId,
             ReplyName,ReceiverMail, ReplyMail, FromName, FromMail,
             Cc, Bcc, Subject, Body, MailType,
             Tstamp
            )
            VALUES (%d,%d,%d,
                    %s,%s,%s,%s,%s,
                    %s,%s,%s,%s,%s,
            %d)
                        ",
            $pid,$gid,$WpUserId,
            $ReplyName,$ReceiverMail,$ReplyMail,$FromName,$FromMail,
            $cc,$bcc,$subject, $body, $MailType,
            $Tstamp
        ));

        $mailId = $saved ? (int)$wpdb->insert_id : false;
        if(!empty($pid) && ($MailType == 'entry-frontend' || $MailType == 'entry-backend' || $MailType == 'entry-backend-custom' || $MailType == 'entry-backend-custom-activation' || $MailType == 'entry-backend-custom-deactivation')) {
            $count = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $tablename_mails WHERE pid = %d",
                $pid
            ) );
            $wpdb->query("UPDATE $tablename SET Mails='$count' WHERE id = $pid");
        }
        return $mailId;
    }
}
