<?php
if(!function_exists('cg1l_create_rating_comments_gallery')){
    function cg1l_create_rating_comments_gallery($galeryIDuserForJs,$fullData,$options,$shortcode_name,$jsonCommentsData,$countSuserVotes,$votedUserPids,$isCGalleries,$commentsWrapperClass = '',$ratingCommentsGroupClass = ''){
        /*if(!isset($fullData['AllowRatingToCheck'])){
            var_dump('12312321');
            echo "<pre>";
            print_r($fullData);
            echo "</pre>";
            var_dump($fullData['GalleryIdToCheck']);
        }*/
        $ratingOptions = $options;
        if($isCGalleries && isset($fullData['AllowRatingToCheck'])){
            $ratingOptions['general']['AllowRating'] = absint($fullData['AllowRatingToCheck']);
            $ratingOptions['general']['AllowRatingAverage'] = (!empty($fullData['AllowRatingAverageToCheck'])) ? 1 : 0;
        }
        $allowRating = (!empty($ratingOptions['general']['AllowRating'])) ? absint($ratingOptions['general']['AllowRating']) : 0;
        $ratingType = cg1l_get_rating_type($ratingOptions);
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
            if($shouldRenderRating && $ratingType === 'one-star'){
                $ratingDiv = cg1l_get_rating_gallery_one_star($galeryIDuserForJs,$fullData,$ratingOptions,$shortcode_name,$votedUserPids,$isCGalleries);
            }elseif($shouldRenderRating && $ratingType === 'average'){
                $ratingDiv = cg1l_get_rating_gallery_average($galeryIDuserForJs,$fullData,$ratingOptions,$shortcode_name,$countSuserVotes,$votedUserPids);
            }elseif($shouldRenderRating && $ratingType === 'five-stars'){
                $ratingDiv = cg1l_get_rating_gallery_five_star($galeryIDuserForJs,$fullData,$ratingOptions,$shortcode_name,$countSuserVotes,$votedUserPids);
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
            $commentsCountFormatted = cg1l_format_voting_comment_number($commentsCount,$options,0);
            /*if($realId==1067){
                var_dump('$commentsCount123 ');
                echo "<pre>";
                print_r($commentsCount);
                echo "</pre>";
            }*/

            //<div class="stat comments" aria-label="Comments">💬 <span>34</span></div>
            return '<div class="cg_gallery_comments_div'.$commentsWrapperClass.'"><div class="cg_gallery_comments_div_child "><div class="cg_gallery_comments_div_icon '.$commentsStat.' cg_gallery_comments_div_icon'.$realId.'"></div><div class="cg_gallery_comments_div_count'.$realId.' cg_gallery_comments_div_count" data-cg-number-value="'.esc_attr($commentsCount).'" aria-label="Comments">'.esc_html($commentsCountFormatted).'</div></div></div>';
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
