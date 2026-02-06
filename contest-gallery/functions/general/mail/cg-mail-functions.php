<?php

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
            if ( $existing !== $tablename_mails ) {
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
		Subject VARCHAR(1000) NOT NULL DEFAULT '',
		Body TEXT NOT NULL,
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

        $wpdb->query($wpdb->prepare(
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

        if(!empty($pid) && ($MailType == 'entry-frontend' || $MailType == 'entry-backend' || $MailType == 'entry-backend-custom' || $MailType == 'entry-backend-custom-activation' || $MailType == 'entry-backend-custom-deactivation')) {
            $count = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $tablename_mails WHERE pid = %d",
                $pid
            ) );
            $wpdb->query("UPDATE $tablename SET Mails='$count' WHERE id = $pid");
        }

    }
}

