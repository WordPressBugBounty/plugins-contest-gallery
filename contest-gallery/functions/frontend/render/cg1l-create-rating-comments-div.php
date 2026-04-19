<?php
if(!function_exists('cg1l_get_rating_count')){
    function cg1l_get_rating_count($fullData,$options,$votedUserPids = [],$isCGalleries = false){
        $rating = 0;
        if($options['general']['ShowOnlyUsersVotes']==1 && !$isCGalleries){
            /*if($fullData['id']==1148){
                var_dump(123123);
                var_dump($rating);
                die;
            }*/
            $rating = cg1l_count_votes_for_an_entry($fullData['id'],$votedUserPids,$options);
        }else{
            if(absint($options['general']['AllowRating'])==2){
                $rating = absint($fullData['CountS']);
                if(absint($options['pro']['Manipulate'])==1){
                    $rating += absint($fullData['addCountS']);
                }
            } elseif(absint($options['general']['AllowRating'])>=1){
                $AllowRating = absint($options['general']['AllowRating']);
                $rating = 0;
                $Manipulate = $options['pro']['Manipulate'];
                foreach ($fullData as $key => $value) {
                    if (strpos($key, 'CountR') === 0) {
                        $multiplikator = (int) str_replace('CountR', '', $key);
                        if($multiplikator<=$AllowRating){
                            $rating += ($value * $multiplikator);
                        }
                    }
                    if($Manipulate==1){
                        if (strpos($key, 'addCountR') === 0) {
                            $multiplikator = (int) str_replace('addCountR', '', $key);
                            if($multiplikator<=$AllowRating){
                                $rating += ($value * $multiplikator);
                            }
                        }
                    }
                }
            }
        }
        return $rating;
    }
}

if(!function_exists('cg1l_has_current_user_voted_for_entry')){
    function cg1l_has_current_user_voted_for_entry($fullData,$votedUserPids = [],$options = []){
        $realId = !empty($fullData['id']) ? absint($fullData['id']) : 0;
        $allowRating = !empty($options['general']['AllowRating']) ? absint($options['general']['AllowRating']) : 0;

        if(!$realId || empty($votedUserPids) || !is_array($votedUserPids)){
            return false;
        }

        if($allowRating === 2){
            foreach($votedUserPids as $votedPid){
                if(is_scalar($votedPid) && absint($votedPid) === $realId){
                    return true;
                }
            }

            return false;
        }

        return !empty($votedUserPids[$realId]);
    }
}


if(!function_exists('cg1l_create_rating_comments_gallery')){
    function cg1l_create_rating_comments_gallery($galeryIDuserForJs,$fullData,$options,$shortcode_name,$jsonCommentsData,$countSuserVotes,$votedUserPids,$isCGalleries,$commentsWrapperClass = '',$ratingCommentsGroupClass = ''){
        /*if(!isset($fullData['AllowRatingToCheck'])){
            var_dump('12312321');
            echo "<pre>";
            print_r($fullData);
            echo "</pre>";
            var_dump($fullData['GalleryIdToCheck']);
        }*/
        $allowRating = (!empty($options['general']['AllowRating'])) ? absint($options['general']['AllowRating']) : 0;
        $allowComments = (!empty($options['general']['AllowComments'])) ? absint($options['general']['AllowComments']) : 0;
        $shouldRenderRating = ($allowRating === 1 || $allowRating === 2 || $allowRating >= 12);

        if($shouldRenderRating && $shortcode_name === 'cg_gallery_no_voting' && empty($options['general']['RatingVisibleForGalleryNoVoting'])){
            $shouldRenderRating = false;
        }

        if(
            $shouldRenderRating &&
            $shortcode_name === 'cg_gallery_ecommerce' &&
            empty($options['general']['RatingVisibleForGalleryEcommerce']) &&
            empty($options['general']['AllowRatingForGalleryEcommerce'])
        ){
            $shouldRenderRating = false;
        }

        $shouldRenderComments = ($allowComments >= 1);

        if($shouldRenderRating || $shouldRenderComments){
            $ratingDiv = '';
            if($shouldRenderRating && (($isCGalleries && $fullData['AllowRatingToCheck'] == 1) || ($allowRating === 2))){
                $ratingDiv = cg1l_get_rating_gallery_one_star($galeryIDuserForJs,$fullData,$options,$shortcode_name,$votedUserPids,$isCGalleries);
            }elseif($shouldRenderRating && (($isCGalleries && $fullData['AllowRatingToCheck'] >= 12) || ($allowRating === 1 || $allowRating >= 12))){
                $ratingDiv = cg1l_get_rating_gallery_five_star($galeryIDuserForJs,$fullData,$options,$shortcode_name,$countSuserVotes,$votedUserPids);
            }
            $commentsDiv = '';
            if($shouldRenderComments){
                $commentsDiv = cg1l_get_comments_gallery_reload($fullData['id'],$options,$jsonCommentsData,$commentsWrapperClass);
            }
            $groupLabel = 'Ratings and comments';
            if($ratingDiv === ''){
                $groupLabel = 'Comments';
            }elseif($commentsDiv === ''){
                $groupLabel = 'Ratings';
            }
            $ratingCommentsGroupClass = trim((string)$ratingCommentsGroupClass);
            if($ratingCommentsGroupClass){
                $ratingCommentsGroupClass = ' '.$ratingCommentsGroupClass;
            }
            return '<div class="cg_gallery_info_rating_comments'.$ratingCommentsGroupClass.'" role="group" aria-label="'.esc_attr($groupLabel).'">
                        '.$ratingDiv.'
                        '.$commentsDiv.'
                    </div>';
        }else{
            return '';
        }
    }
}

if(!function_exists('cg1l_get_comments_gallery_reload')){
    function cg1l_get_comments_gallery_reload($realId,$options,$jsonCommentsData,$commentsWrapperClass = ''){
        if(absint($options['general']['AllowComments'])==0){
            return '';
        }else{
            $commentsCount = cg1l_count_comments($realId,$jsonCommentsData);
            $commentsStat = 'cg_gallery_comments_div_icon_off';
            if($commentsCount>=1){
                $commentsStat = 'cg_gallery_comments_div_icon_on';
            }
            $commentsWrapperClass = trim($commentsWrapperClass);
            if($commentsWrapperClass){
                $commentsWrapperClass = ' '.$commentsWrapperClass;
            }
            /*if($realId==1067){
                var_dump('$commentsCount123 ');
                echo "<pre>";
                print_r($commentsCount);
                echo "</pre>";
            }*/

            //<div class="stat comments" aria-label="Comments">💬 <span>34</span></div>
            return '<div class="cg_gallery_comments_div'.$commentsWrapperClass.'"><div class="cg_gallery_comments_div_child "><div class="cg_gallery_comments_div_icon '.$commentsStat.' cg_gallery_comments_div_icon'.$realId.'"></div><div class="cg_gallery_comments_div_count'.$realId.' cg_gallery_comments_div_count" aria-label="Comments">'.$commentsCount.'</div></div></div>';
        }
    }
}

if(!function_exists('cg1l_count_comments')){
    function cg1l_count_comments($realId,$jsonCommentsData){
        /*if($realId==1067){
            var_dump('$jsonCommentsData1234');
            echo "<pre>";
            print_r($jsonCommentsData[$realId]);
            echo "</pre>";
        }*/
        if(empty($jsonCommentsData[$realId])){
            return 0;
        }else{
            $count = 0;
            /*var_dump('21123');
            echo "<pre>";
                print_r($jsonCommentsData[$realId]);
            echo "</pre>";*/
            foreach($jsonCommentsData[$realId] as $comment){
                // has to be checked with isset!
                if(isset($comment['Active']) && $comment['Active']!=2){
                    $count++;
                }
            }
            return $count;
        }
    }
}
