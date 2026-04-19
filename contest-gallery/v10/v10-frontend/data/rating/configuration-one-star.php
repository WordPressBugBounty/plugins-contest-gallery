<?php
if(empty($isOnlyGalleryNoVoting) && empty($isOnlyGalleryWinner)) {
    // registered users check
    // if(($options['general']['ShowOnlyUsersVotes']==1 or $options['general']['HideUntilVote']==1 or $options['pro']['MinusVote']==1) and $options['general']['CheckLogin']==1){ old logic before 10.07.2022
    if($options['general']['CheckLogin']==1){
        if(is_user_logged_in()){
            $countSuserId = $wpdb->get_results( $wpdb->prepare(
                "
                            SELECT pid
                            FROM $tablenameIP
                            WHERE GalleryID = %d and WpUserId = %s and RatingS = %d
                        ",
                $galeryID,$WpUserId,1
            ) );
            $countUserVotes = count($countSuserId);
            if(!empty($countSuserId) && count($countSuserId)){
                foreach($countSuserId as $object){
                    $votedUserPids[] = intval($object->pid);
                }
            }
        }

    }
    // cookie users check
    //elseif (($options['general']['ShowOnlyUsersVotes']==1 or $options['general']['HideUntilVote']==1 or $options['pro']['MinusVote']==1) and $options['general']['CheckCookie']==1 and $options['general']['CheckIp']!=1){//old logic before 10.07.2022
    elseif ($options['general']['CheckCookie']==1 and $options['general']['CheckIp']!=1){

        if(isset($_COOKIE['contest-gal1ery-'.$galeryID.'-voting'])) {

            $countSuserCookie = $wpdb->get_results( $wpdb->prepare(
                "
                        SELECT pid
                        FROM $tablenameIP
                        WHERE GalleryID = %d and CookieId = %s and RatingS = %d
                    ",
                $galeryID,$_COOKIE['contest-gal1ery-'.$galeryID.'-voting'],1
            ) );

            $countUserVotes = count($countSuserCookie);

            if(!empty($countSuserCookie) && count($countSuserCookie)){
                foreach($countSuserCookie as $object){
                    $votedUserPids[] = intval($object->pid);
                }
            }
        }
    }
     elseif ($options['general']['CheckIp']==1 and $options['general']['CheckCookie']==1){
     // CheckIpAndCookie
        $countSuserIp = [];
        if(isset($_COOKIE['contest-gal1ery-'.$galeryID.'-voting'])) {
            $countSuserIp = $wpdb->get_results( $wpdb->prepare(
                "
                    SELECT pid
                    FROM $tablenameIP
                    WHERE GalleryID = %d and IP = %s and CookieId = %s and RatingS = %d
                ",
                $galeryID,$userIP,$_COOKIE['contest-gal1ery-'.$galeryID.'-voting'],1
            ) );
        }

         $countUserVotes = count($countSuserIp);

         if(!empty($countSuserIp) && count($countSuserIp)){
             foreach($countSuserIp as $object){
                 $votedUserPids[] = intval($object->pid);
             }
         }

    }
     //elseif (($options['general']['ShowOnlyUsersVotes']==1 or $options['general']['HideUntilVote']==1 or $options['pro']['MinusVote']==1) and $options['general']['CheckIp']==1 and $options['general']['CheckCookie']!=1){//old logic before 10.07.2022
     elseif ($options['general']['CheckIp']==1 and $options['general']['CheckCookie']!=1){// IP check then

            $countSuserIp = $wpdb->get_results( $wpdb->prepare(
                "
                    SELECT pid
                    FROM $tablenameIP
                    WHERE GalleryID = %d and IP = %s and RatingS = %d
                ",
                $galeryID,$userIP,1
            ) );

         $countUserVotes = count($countSuserIp);

         if(!empty($countSuserIp) && count($countSuserIp)){
             foreach($countSuserIp as $object){
                 $votedUserPids[] = intval($object->pid);
             }
         }

    }
}
