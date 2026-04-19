<?php

$AllowRatingMax = $options['general']['AllowRating']-10;

if(empty($isOnlyGalleryNoVoting) && empty($isOnlyGalleryWinner)) {
    // registered users check
    //if(($options['general']['ShowOnlyUsersVotes']==1 or $options['general']['HideUntilVote']==1 or $options['pro']['MinusVote']==1) and $options['general']['CheckLogin']==1){//old logic before 10.07.2022
        if($options['general']['CheckLogin']==1){
            if (is_user_logged_in()) {

                $countRuserId = $wpdb->get_results($wpdb->prepare(
                    "
                                    SELECT pid, Rating
                                    FROM $tablenameIP
                                    WHERE GalleryID = %d and WpUserId = %s and Rating >= %d and Rating <= %d
                                ",
                    $galeryID, $WpUserId, 1, $AllowRatingMax
                ));

                $countUserVotes = count($countRuserId);

                if (!empty($countRuserId)) {
                    foreach ($countRuserId as $object) {
                        if(!isset($votedUserPids[$object->pid])){$votedUserPids[$object->pid] = [];}
                        $votedUserPids[$object->pid][] = $object->Rating;
                    }
                }
            }
    } elseif ($options['general']['CheckCookie']==1 and $options['general']['CheckIp']!=1){
        if(isset($_COOKIE['contest-gal1ery-'.$galeryID.'-voting'])) {
            $countSuserCookie = $wpdb->get_results( $wpdb->prepare(
                "
                            SELECT pid, Rating
                            FROM $tablenameIP
                            WHERE GalleryID = %d and CookieId = %s and Rating >= %d and Rating <= %d
                        ",
                $galeryID,$_COOKIE['contest-gal1ery-'.$galeryID.'-voting'],1, $AllowRatingMax
            ) );

            $countUserVotes = count($countSuserCookie);

            if(!empty($countSuserCookie)){
                foreach($countSuserCookie as $object){
                    if(!isset($votedUserPids[$object->pid])){$votedUserPids[$object->pid] = [];}
                    $votedUserPids[$object->pid][] = $object->Rating;
                }
            }
        }
    }elseif($options['general']['CheckIp']==1 and $options['general']['CheckCookie']==1){
    // CheckIpAndCookie
        $countSuserCookie = [];
        if(isset($_COOKIE['contest-gal1ery-'.$galeryID.'-voting'])) {

            $countSuserCookie = $wpdb->get_results( $wpdb->prepare(
                "
                            SELECT pid, Rating
                            FROM $tablenameIP
                            WHERE GalleryID = %d and CookieId = %s and IP = %s and Rating >= %d and Rating <= %d
                        ",
                $galeryID,$_COOKIE['contest-gal1ery-'.$galeryID.'-voting'],$userIP,1, $AllowRatingMax
            ) );
        }

        $countUserVotes = count($countSuserCookie);

        if(!empty($countSuserCookie)){
                foreach($countSuserCookie as $object){
                    if(!isset($votedUserPids[$object->pid])){$votedUserPids[$object->pid] = [];}
                    $votedUserPids[$object->pid][] = $object->Rating;
                }
        }
    } elseif ($options['general']['CheckIp']==1 and $options['general']['CheckCookie']!=1){// IP check then

     //   if ($options['general']['ShowOnlyUsersVotes']==1 or $options['general']['HideUntilVote']==1 or $options['pro']['MinusVote']==1){//old logic before 10.07.2022

            $countRuserIp = $wpdb->get_results($wpdb->prepare(
                "
                            SELECT pid, Rating
                            FROM $tablenameIP
                            WHERE GalleryID = %d and IP = %s and Rating >= %d and Rating <= %d
                        ",
                $galeryID, $userIP, 1, $AllowRatingMax
            ));

            $countUserVotes = count($countRuserIp);

            if (!empty($countRuserIp)) {
                foreach ($countRuserIp as $object) {
                    if(!isset($votedUserPids[$object->pid])){$votedUserPids[$object->pid] = [];}
                    $votedUserPids[$object->pid][] = $object->Rating;
                }
            }
            //   }
    }

}
