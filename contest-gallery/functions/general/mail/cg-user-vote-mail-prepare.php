<?php

if (!function_exists('contest_gal1ery_user_vote_mail_prepare')) {
    function contest_gal1ery_user_vote_mail_prepare($options, $pictureID, $galeryID, $isMultipleStars = false)
    {

        global $wpdb;

        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_mail_user_vote = $wpdb->prefix . "contest_gal1ery_mail_user_vote";
        $tablename_user_vote_mails = $wpdb->prefix . "contest_gal1ery_user_vote_mails";
        $tablename_mail_user_comment = $wpdb->prefix . "contest_gal1ery_mail_user_comment";
        $tablename_user_comment_mails = $wpdb->prefix . "contest_gal1ery_user_comment_mails";
        $wp_users = $wpdb->prefix . "users";
        $tablenameIP = $wpdb->prefix . "contest_gal1ery_ip";

        if (!empty($options['pro']['InformUserVote'])) {
            $InformUserVoteMailInterval = $options['pro']['InformUserVoteMailInterval'];

            $rowObject = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename WHERE id = %d  ORDER BY id DESC LIMIT 1", $pictureID));
            if (empty($rowObject)) {
                return;
            }
            $wpUserIdOfVotedImage = intval($rowObject->WpUserId);

            if (!empty($wpUserIdOfVotedImage)) {

                $lastTstampFor = $wpdb->get_var($wpdb->prepare("SELECT Tstamp FROM $tablename_user_vote_mails WHERE WpUserId = %d  AND GalleryID = %d ORDER BY id DESC LIMIT 1", $wpUserIdOfVotedImage, $galeryID));
                $tstampToCompare = 1 * 60 * 60;
                if ($InformUserVoteMailInterval == '1m') {
                    $tstampToCompare = 1 * 60;
                } elseif ($InformUserVoteMailInterval == '2m') {
                    $tstampToCompare = 1 * 120;
                } elseif ($InformUserVoteMailInterval == '1h') {
                    $tstampToCompare = 1 * 60 * 60;
                } elseif ($InformUserVoteMailInterval == '2h') {
                    $tstampToCompare = 1 * 60 * 60 * 2;
                } elseif ($InformUserVoteMailInterval == '4h') {
                    $tstampToCompare = 1 * 60 * 60 * 4;
                } elseif ($InformUserVoteMailInterval == '6h') {
                    $tstampToCompare = 1 * 60 * 60 * 6;
                } elseif ($InformUserVoteMailInterval == '12h') {
                    $tstampToCompare = 1 * 60 * 60 * 12;
                } elseif ($InformUserVoteMailInterval == '24h') {
                    $tstampToCompare = 1 * 60 * 60 * 24;
                } elseif ($InformUserVoteMailInterval == '48h') {
                    $tstampToCompare = 1 * 60 * 60 * 48;
                } elseif ($InformUserVoteMailInterval == '1week') {
                    $tstampToCompare = 1 * 60 * 60 * 168;
                } elseif ($InformUserVoteMailInterval == '2weeks') {
                    $tstampToCompare = 1 * 60 * 60 * 336;
                } elseif ($InformUserVoteMailInterval == '4weeks') {
                    $tstampToCompare = 1 * 60 * 60 * 672;
                }
                if (empty($lastTstampFor) or (time() - $tstampToCompare) > $lastTstampFor) {
                    if (empty($lastTstampFor)) {
                        $lastTstampFor = time() - $tstampToCompare;
                    }
                    $selectSQLemailUserVote = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_mail_user_vote WHERE GalleryID = %d", $galeryID));
                    $InformUserContent = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemailUserVote->Content);

                    // insert first to reduce chance of multiple processing
                    $wpdb->query($wpdb->prepare(
                        "
                        INSERT INTO $tablename_user_vote_mails 
                        ( id, GalleryID, Tstamp, WpUserId)
                        VALUES ( %s,%d,%d,%d)
                    ",
                        '', $galeryID, time(),$wpUserIdOfVotedImage
                    ));
                    //var_dump(123123);
                    //$wpdb->show_errors(); //setting the Show or Display errors option to true
                    //$wpdb->print_error();
                    $insert_id = $wpdb->insert_id;

                    $posUserInfo = "\$info\$";

                    if ($isMultipleStars) {
                        $multipleStarsMaxRating = 5;
                        if (!empty($options['general']['AllowRating'])) {
                            $multipleStarsAllowRating = intval($options['general']['AllowRating']);
                            if ($multipleStarsAllowRating >= 12) {
                                $multipleStarsMaxRating = $multipleStarsAllowRating - 10;
                            }
                        }
                        if ($multipleStarsMaxRating < 1) {
                            $multipleStarsMaxRating = 5;
                        } elseif ($multipleStarsMaxRating > 10) {
                            $multipleStarsMaxRating = 10;
                        }

                        $userVotes = $wpdb->get_results($wpdb->prepare(
                            "SELECT $tablename.NamePic, SUM($tablenameIP.Rating) AS CountRtotalSum, $tablename.id, $tablename.WpPage
                            FROM $tablenameIP
                            INNER JOIN $tablename ON $tablename.id = $tablenameIP.pid
                            WHERE $tablename.GalleryID = %d
                                AND $tablenameIP.GalleryID = %d
                                AND $tablename.WpUserId = %d
                                AND $tablename.Active = 1
                                AND $tablenameIP.Rating > 0
                                AND $tablenameIP.Rating <= %d
                                AND $tablenameIP.Tstamp > %d
                            GROUP BY $tablename.id, $tablename.NamePic, $tablename.WpPage
                            ORDER BY CountRtotalSum DESC
                            LIMIT 10",
                            $galeryID, $galeryID, $wpUserIdOfVotedImage, $multipleStarsMaxRating, $lastTstampFor
                        ));

                    } else {

                        $userVotes = $wpdb->get_results($wpdb->prepare(
                            "SELECT $tablename.NamePic, COUNT($tablenameIP.id) AS CountStotalCount, $tablename.id, $tablename.WpPage
                            FROM $tablenameIP
                            INNER JOIN $tablename ON $tablename.id = $tablenameIP.pid
                            WHERE $tablename.GalleryID = %d
                                AND $tablenameIP.GalleryID = %d
                                AND $tablename.WpUserId = %d
                                AND $tablename.Active = 1
                                AND $tablenameIP.RatingS = 1
                                AND $tablenameIP.Tstamp > %d
                            GROUP BY $tablename.id, $tablename.NamePic, $tablename.WpPage
                            ORDER BY CountStotalCount DESC
                            LIMIT 10",
                            $galeryID, $galeryID, $wpUserIdOfVotedImage, $lastTstampFor
                        ));

                    }

                    $to = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM $wp_users WHERE ID = %d", $wpUserIdOfVotedImage));

                    if (stripos($InformUserContent, $posUserInfo) !== false) {

                        include(__DIR__ . "/../../../check-language.php");

                        $UserEntries = '';
                        foreach ($userVotes as $userVotesData) {
                            $entryVoteCount = ($isMultipleStars) ? intval($userVotesData->CountRtotalSum) : intval($userVotesData->CountStotalCount);
                            if ($entryVoteCount <= 0) {
                                continue;
                            }

                            $UserEntries .= '(<b>+' . $entryVoteCount . '</b>) '.$userVotesData->NamePic . '<br/>';

                            $entryWpPage = (!empty($userVotesData->WpPage)) ? intval($userVotesData->WpPage) : 0;
                            $entryWpPagePermalink = (!empty($entryWpPage)) ? get_permalink($entryWpPage) : '';
                            if(!empty($entryWpPagePermalink)){
                                $UserEntries .= '<a href="' . $entryWpPagePermalink . '" target="_blank">' . $entryWpPagePermalink . '</a><br/><br/>';
                            }else{
                                if (!empty($selectSQLemailUserVote->URL)) {
                                    $UserEntries .= '<a href="' . $selectSQLemailUserVote->URL . "#!gallery/$galeryID/file/" . $userVotesData->id . "/" . $userVotesData->NamePic . '" target="_blank">' . $selectSQLemailUserVote->URL . "#!gallery/$galeryID/file/" . $userVotesData->id . "/" . $userVotesData->NamePic . '</a><br/><br/>';
                                } else {
                                    $UserEntries .= "Missing URL in options to provide full gallery link ...#!gallery/$galeryID/file/" . $userVotesData->id . "/" . $userVotesData->NamePic . '<br/><br/>';
                                }
                            }

                        }

                        $Msg = str_ireplace($posUserInfo, $UserEntries, $InformUserContent);

                        contest_gal1ery_user_vote_mail($selectSQLemailUserVote, $Msg, $galeryID, $to);
                    } else {
                        $Msg = $InformUserContent;
                        contest_gal1ery_user_vote_mail($selectSQLemailUserVote, $Msg, $galeryID, $to);
                    }

                    $Msg = contest_gal1ery_htmlentities_and_preg_replace($Msg);

                    // update main table
                    $wpdb->update(
                        "$tablename_user_vote_mails",
                        array('Content' => $Msg),
                        array('id' => $insert_id),
                        array('%s'),
                        array('%d')
                    );


                }
            }

        }


    }
}
