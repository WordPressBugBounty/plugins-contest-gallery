<?php

if(!function_exists('cg_correct_entry_count')){
    function cg_correct_entry_count($pid){

        global $wpdb;
        $tablename = $wpdb->prefix ."contest_gal1ery";
        $tablenameIP = $wpdb->prefix ."contest_gal1ery_ip";

        $RatingOverview = $wpdb->get_results( $wpdb->prepare(
            "
                                    SELECT * 
                                    FROM $tablenameIP
                                    WHERE pid = %d AND (RatingS >=1 OR (Rating >= %d AND Rating <= %d))
                                ",
            $pid,1,10
        ) );

        $CountR = 0;
        $CountR1 = 0;
        $CountR2 = 0;
        $CountR3 = 0;
        $CountR4 = 0;
        $CountR5 = 0;
        $CountR6 = 0;
        $CountR7 = 0;
        $CountR8 = 0;
        $CountR9 = 0;
        $CountR10 = 0;
        $Rating = 0;
        $CountS = 0;
        foreach($RatingOverview as $row){
            if($row->RatingS == 1){
                $CountS++;
            }else{
                if($row->Rating == 1){$CountR1++;}
                if($row->Rating == 2){$CountR2++;}
                if($row->Rating == 3){$CountR3++;}
                if($row->Rating == 4){$CountR4++;}
                if($row->Rating == 5){$CountR5++;}
                if($row->Rating == 6){$CountR6++;}
                if($row->Rating == 7){$CountR7++;}
                if($row->Rating == 8){$CountR8++;}
                if($row->Rating == 9){$CountR9++;}
                if($row->Rating == 10){$CountR10++;}
                $CountR++;
                $Rating = $Rating + $row->Rating;
            }
        }

        $wpdb->query("UPDATE $tablename SET CountR=$CountR, CountS = $CountS, Rating = $Rating, 
         CountR1 = $CountR1, CountR2 = $CountR2, CountR3 = $CountR3, CountR4=$CountR4, CountR5 = $CountR5,
         CountR6 = $CountR6, CountR7=$CountR7, CountR8 = $CountR8, CountR9 = $CountR9, CountR10 = $CountR10 
         WHERE id=$pid");

    }
}

if(!function_exists('cg_get_correct_rating_overview')){
    function cg_get_correct_rating_overview($GalleryID){

        $wp_upload_dir = wp_upload_dir();

        global $wpdb;
        $tablenameIP = $wpdb->prefix ."contest_gal1ery_ip";
        $tablenameComments = $wpdb->prefix . "contest_gal1ery_comments";

        $wp_upload_dir = wp_upload_dir();

        $options = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-options.json';
        $options = json_decode(file_get_contents($options),true);
        if(!empty($options[$GalleryID])){
            $options = $options[$GalleryID];
        }

        $RatingOverview = $wpdb->get_results( $wpdb->prepare(
            "
                                        SELECT pid, RatingS, Rating
                                        FROM $tablenameIP
                                        WHERE GalleryID = %d AND (RatingS >=1 OR (Rating >= %d AND Rating <= %d))
                                        ORDER By pid DESC
                                    ",
            $GalleryID,1,10
        ) );

        $RatingOverviewArray = [];

        foreach($RatingOverview as $row){

            if(!isset($RatingOverviewArray[$row->pid])){
                $CountR = 0;
                $CountR1 = 0;
                $CountR2 = 0;
                $CountR3 = 0;
                $CountR4 = 0;
                $CountR5 = 0;
                $CountR6 = 0;
                $CountR7 = 0;
                $CountR8 = 0;
                $CountR9 = 0;
                $CountR10 = 0;
                $Rating = 0;
                $CountS = 0;
                $RatingOverviewArray[$row->pid] = [];
                if(!empty($row->RatingS)){
                    $CountS++;
                    $RatingOverviewArray[$row->pid]['CountS'] = $CountS;
                }

                if(!empty($row->Rating)){
                    $CountR++;
                    $RatingOverviewArray[$row->pid]['CountR'] = $CountR;
                    $Rating = $Rating + $row->Rating;
                    $RatingOverviewArray[$row->pid]['Rating'] = $Rating;
                    if($row->Rating==1){$CountR1++;$RatingOverviewArray[$row->pid]['CountR1']=$CountR1;}
                    if($row->Rating==2){$CountR2++;$RatingOverviewArray[$row->pid]['CountR2']=$CountR2;}
                    if($row->Rating==3){$CountR3++;$RatingOverviewArray[$row->pid]['CountR3']=$CountR3;}
                    if($row->Rating==4){$CountR4++;$RatingOverviewArray[$row->pid]['CountR4']=$CountR4;}
                    if($row->Rating==5){$CountR5++;$RatingOverviewArray[$row->pid]['CountR5']=$CountR5;}
                    if($row->Rating==6){$CountR6++;$RatingOverviewArray[$row->pid]['CountR6']=$CountR6;}
                    if($row->Rating==7){$CountR7++;$RatingOverviewArray[$row->pid]['CountR7']=$CountR7;}
                    if($row->Rating==8){$CountR8++;$RatingOverviewArray[$row->pid]['CountR8']=$CountR8;}
                    if($row->Rating==9){$CountR9++;$RatingOverviewArray[$row->pid]['CountR9']=$CountR9;}
                    if($row->Rating==10){$CountR10++;$RatingOverviewArray[$row->pid]['CountR10']=$CountR10;}
                }
            }else{
                if(!empty($row->RatingS)){
                    $CountS++;
                    $RatingOverviewArray[$row->pid]['CountS'] = $CountS;
                }
                if(!empty($row->Rating)){
                    $CountR++;
                    $RatingOverviewArray[$row->pid]['CountR'] = $CountR;
                    $Rating = $Rating + $row->Rating;
                    $RatingOverviewArray[$row->pid]['Rating'] = $Rating;
                    if($row->Rating==1){$CountR1++;$RatingOverviewArray[$row->pid]['CountR1']=$CountR1;}
                    if($row->Rating==2){$CountR2++;$RatingOverviewArray[$row->pid]['CountR2']=$CountR2;}
                    if($row->Rating==3){$CountR3++;$RatingOverviewArray[$row->pid]['CountR3']=$CountR3;}
                    if($row->Rating==4){$CountR4++;$RatingOverviewArray[$row->pid]['CountR4']=$CountR4;}
                    if($row->Rating==5){$CountR5++;$RatingOverviewArray[$row->pid]['CountR5']=$CountR5;}
                    if($row->Rating==6){$CountR6++;$RatingOverviewArray[$row->pid]['CountR6']=$CountR6;}
                    if($row->Rating==7){$CountR7++;$RatingOverviewArray[$row->pid]['CountR7']=$CountR7;}
                    if($row->Rating==8){$CountR8++;$RatingOverviewArray[$row->pid]['CountR8']=$CountR8;}
                    if($row->Rating==9){$CountR9++;$RatingOverviewArray[$row->pid]['CountR9']=$CountR9;}
                    if($row->Rating==10){$CountR10++;$RatingOverviewArray[$row->pid]['CountR10']=$CountR10;}
                }
            }

        }

        $CommentsOverview = $wpdb->get_results( $wpdb->prepare(
            "
                                        SELECT pid, COUNT(*) as NumberOfRows
                                        FROM $tablenameComments
                                        WHERE GalleryID = %d
                                        GROUP By pid 
                                        ORDER By pid DESC
                                    ",
            $GalleryID
        ) );

        // before 16.0.0 data was saved in database
        $processedDatabaseComments = [];

        foreach($CommentsOverview as $row){
            if(!empty($RatingOverviewArray[$row->pid])){
                $processedDatabaseComments[$row->pid] = $row->NumberOfRows;
                $RatingOverviewArray[$row->pid]['CountC'] = $row->NumberOfRows;
            }else{
                $RatingOverviewArray[$row->pid] = [];
                $RatingOverviewArray[$row->pid]['CountC'] = $row->NumberOfRows;
                $processedDatabaseComments[$row->pid] = $row->NumberOfRows;
            }
        }

        // this condition added later in version 28.1.2.2
        // then $CommentsOverview has to be added. Because before version 16 comments might be inserted
        // But with and after 16 no comments inserted in database till version 23.1.2. In 23.1.3 inserted again.
        // Between 16 and 23.1.2 $CommentsOverview will be always 0, but also good for case if before 16.
        // comments will be inserted since 23.1.3, because of allocation correction, but also in dir, so what in dir counts in generally
        $toAdd = false;
        if(cg_format_options_version($options['general']['Version'])>16 && cg_format_options_version($options['general']['Version'])<23.13){
            $toAdd = true;
        }

        // process now comments data since 16.0.0
        //$fileImageComment = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-comments/ids/'.$row->pid.'/'.$commentId.'.json';
        if(is_dir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-comments/ids')){
            $fileImageCommentDirs = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-comments/ids/*');
            if(count($fileImageCommentDirs)){
                foreach ($fileImageCommentDirs as $fileImageCommentDir){
                    $fileImageCommentDirFiles = glob($fileImageCommentDir.'/*.json');
                    $fileImageCommentDirCount = count($fileImageCommentDirFiles);

                    $fileImageCommentDirExploded = explode('/',$fileImageCommentDir);
                    $imageId = end($fileImageCommentDirExploded);

                    if(!empty($RatingOverviewArray[$imageId])){
                        $RatingOverviewArray[$imageId]['CountC'] = $fileImageCommentDirCount;
                    }else{
                        $RatingOverviewArray[$imageId] = [];
                        $RatingOverviewArray[$imageId]['CountC'] = $fileImageCommentDirCount;
                    }

                    // add database entries if existed
                    if(!empty($processedDatabaseComments[$imageId]) && $toAdd){
                        $RatingOverviewArray[$imageId]['CountC'] = $fileImageCommentDirCount+$processedDatabaseComments[$imageId];
                    }

                    $countCtoReview = 0;

                    // added since 18.0.1 CommReview
                    foreach ($fileImageCommentDirFiles as $fileImageCommentDirFile){
                        $fileImageCommentDirFileArray = json_decode(file_get_contents($fileImageCommentDirFile),true);
                        if(!empty($fileImageCommentDirFileArray[key($fileImageCommentDirFileArray)]['Active']) && $fileImageCommentDirFileArray[key($fileImageCommentDirFileArray)]['Active']==2){
                            $countCtoReview++;
                        }
                    }

                    $RatingOverviewArray[$imageId]['CountCtoReview'] = $countCtoReview;

               }
            }
        }

        return $RatingOverviewArray;

    }
}