<?php

if(!function_exists('cg1l_is_voting_possible_for_shortcode')){
    function cg1l_is_voting_possible_for_shortcode($shortcode_name, $options = []){
        if($shortcode_name === 'cg_gallery'){
            return true;
        }

        if($shortcode_name === 'cg_gallery_ecommerce' && !empty($options['general']['AllowRatingForGalleryEcommerce'])){
            return true;
        }

        return false;
    }
}

if(!function_exists('cg1l_get_rating_gallery_one_star')){
    function cg1l_get_rating_gallery_one_star($galeryIDuserForJs,$fullData,$options,$shortcode_name,$votedUserPids,$isCGalleries = false){
        $ratingCount = cg1l_get_one_star_rating_count($fullData,$options,$votedUserPids,$isCGalleries);
        /*if($fullData['id']==1148){
            var_dump(123123);
            var_dump($ratingCount);
            var_dump($fullData);
            die;
        }*/
        //$alreadyVoted = cg1l_get_check_already_vote();
        $alreadyVoted = cg1l_has_current_user_voted_one_star($fullData,$votedUserPids);
        $isVotingPossible = cg1l_is_voting_possible_for_shortcode($shortcode_name,$options);
        $alreadyVotedUi = $alreadyVoted && $isVotingPossible;
        if(cg1l_should_hide_rating_until_user_vote($options,$alreadyVoted)){
            $ratingCount = 0;
        }
        $cg_gallery_rating_div_child = cg1l_get_rating_gallery_one_star_for_reload($fullData['id'],$galeryIDuserForJs,$fullData['GalleryID'],$alreadyVotedUi,$ratingCount,$shortcode_name,$options,$fullData);
        $galleryRatingClass = 'cg_gallery_rating_div cg_gallery_rating_div_one_star';
        if(!$isVotingPossible){
            $galleryRatingClass .= ' cg_pointer_events_none';
        }
        return '<div class="'.$galleryRatingClass.'" id="cg_gallery_rating_div'.$fullData['id'].'" aria-label="Ratings">
            '.$cg_gallery_rating_div_child.'
       </div>';
    }
}

if(!function_exists('cg1l_get_rating_gallery_five_star')){
    function cg1l_get_rating_gallery_five_star($galeryIDuserForJs,$fullData,$options,$shortcode_name,$countSuserVotes,$votedUserPids)
    {
        $ratingDisplayValue = cg1l_get_five_star_cumulative_value($fullData,$options,$votedUserPids);
        return cg1l_get_rating_gallery_multi_star($galeryIDuserForJs,$fullData,$options,$shortcode_name,$countSuserVotes,$votedUserPids,$ratingDisplayValue);
    }
}

if(!function_exists('cg1l_get_rating_gallery_multi_star')){
    function cg1l_get_rating_gallery_multi_star($galeryIDuserForJs,$fullData,$options,$shortcode_name,$countSuserVotes,$votedUserPids,$ratingDisplayValue)
    {
        $realId = $fullData['id'];
        $alreadyVoted = cg1l_has_current_user_voted_multi_star($fullData,$votedUserPids);
        $isVotingPossible = cg1l_is_voting_possible_for_shortcode($shortcode_name,$options);
        $alreadyVotedUi = $alreadyVoted && $isVotingPossible;
        $outOfGalleryDisallowedClass = empty($options['general']['RatingOutGallery']) ? ' cg_rate_out_gallery_disallowed' : '';

        $ratingActivityValue = cg1l_get_five_star_cumulative_value($fullData,$options,$votedUserPids);

        $hideRatingUntilUserVote = cg1l_should_hide_rating_until_user_vote($options,$alreadyVoted);
        if($hideRatingUntilUserVote){
            $ratingActivityValue = 0;
            $ratingDisplayValue = '';
        }

        $AllowRating = cg1l_get_multi_star_rating_limit($options);
        $ratingsCount = cg1l_get_multi_star_rating_distribution($fullData,$options,$votedUserPids);
        $ratingsTotalCount = array_sum($ratingsCount);
        $ratingDisplayDecimals = (cg1l_get_rating_type($options) === 'average') ? 1 : 0;
        $ratingDisplayFormatted = cg1l_format_voting_comment_number($ratingDisplayValue,$options,$ratingDisplayDecimals);
        $ratingsTotalCountFormatted = cg1l_format_voting_comment_number($ratingsTotalCount,$options,0);

        $alreadyVotedClass = $alreadyVotedUi ? ' cg_already_voted' : '';
        include(__DIR__ . "/../../../../check-language.php");
        $voteTooltip       = $alreadyVotedUi ? $language_YouHaveAlreadyVotedThisPicture : $language_VoteNow;
        $voteTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="' . esc_attr($voteTooltip) . '"' : '';
        $votedTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="' . esc_attr($language_YouHaveAlreadyVotedThisPicture) . '"' : '';
        $undoTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="' . esc_attr($language_UndoYourLastVote) . '"' : '';
        $ratingCountLabel = ($ratingsTotalCount === 1) ? $language_Rating : $language_Ratings;
        if(empty($ratingCountLabel)){
            $ratingCountLabel = ($ratingsTotalCount === 1) ? 'rating' : 'ratings';
        }
        $ratingCompactCountClass = 'cg_gallery_rating_compact_vote_count';
        if($hideRatingUntilUserVote){
            $ratingCompactCountClass .= ' cg_hide';
        }
        $cgRateMinus = '';
        if(!empty($options['pro']['MinusVote']) && intval($options['pro']['MinusVote']) === 1 && !empty($options['general']['RatingOutGallery']) && intval($options['general']['RatingOutGallery']) === 1 && $alreadyVotedUi && $shortcode_name !== 'cg_gallery_user' && $isVotingPossible){
            $cgRateMinus = '<div data-cg-real-id="' . (int)$realId . '" class="cg_rate_minus cg_rate_minus_five_star" data-cg-gid="' . esc_attr($galeryIDuserForJs) . '"' . $undoTooltipAttr . '></div>';
        }

        $galleryRatingClass = 'cg_gallery_rating_div cg_gallery_rating_div_five_stars';
        if(!$isVotingPossible){
            $galleryRatingClass .= ' cg_gallery_rating_read_only';
        }
        $html  = '<div class="' . $galleryRatingClass . '" id="cg_gallery_rating_div' . (int)$realId . '">';
        $html .= '<div class="cg_gallery_rating_div_child' . $alreadyVotedClass . ' cg_gallery_rating_div_child_five_star"';
        $html .= ' id="cg_gallery_rating_div_child' . (int)$realId . '"';
        $html .= ' data-cg-gid="' . $galeryIDuserForJs . '"';
        $html .= ' data-cg-real-id="' . (int)$realId . '">';

        if ($alreadyVotedUi) {
            $html .= '<div class="cg_voted_confirm"' . $votedTooltipAttr . '></div>';
        }

        $html .= '<div class="cg_gallery_rating_div_star"' . $voteTooltipAttr . ' data-cg-gid="' . (int)$galeryIDuserForJs . '"></div>';

        if($ratingActivityValue){
            $cg_gallery_rating_div_one_star = 'cg_gallery_rating_div_one_star_on';
        }else{
            $cg_gallery_rating_div_one_star = 'cg_gallery_rating_div_one_star_off';
        }

        // Main clickable star (current rating preview)
        $html .= '<div data-cg_rate_star="1" data-cg-gid="' . $galeryIDuserForJs . '"';
        $html .= ' class="cg_rate_star cg_rate_star_five_star cg_gallery_rating_div_star_one_star '.$cg_gallery_rating_div_one_star.$outOfGalleryDisallowedClass.'"';
        $html .= ' data-cg-real-id="' . (int)$realId . '"></div>';

        $html .= '<div id="rating_cg-' . (int)$realId . '" class="cg_gallery_rating_div_count" data-cg-number-value="' . esc_attr($ratingDisplayValue) . '">';
        $html .= '<div class="cg_gallery_rating_div_star_hover">^</div>';

        // Details popup
        $html .= '<div class="cg_gallery_rating_div_five_star_details cg_hide"';
        $html .= ' data-cg-gid="' . $galeryIDuserForJs . '"';
        $html .= ' data-cg-real-id="' . (int)$realId . '"';
        $html .= ' id="cgDetails' . (int)$realId . '">';

        // Rating overview stars
        $html .= '<div class="cg_gallery_rating_overview">';
        for ($i = 1; $i <= $AllowRating; $i++) {
            $html .= '<div data-cg_rate_star="' . $i . '" data-cg-gid="' . $galeryIDuserForJs . '"';
            $html .= ' class="cg_rate_star cg_rate_star_five_star cg_gallery_rating_div_star_one_star cg_gallery_rating_div_one_star_off'.$outOfGalleryDisallowedClass.'"';
            $html .= ' data-cg-real-id="' . (int)$realId . '"></div>';
        }
        $html .= '</div>';

        $html .= '<div class="cg_gallery_rating_div_five_star_details_close_button"></div>';

        $html .= cg1l_render_multi_star_distribution_rows($AllowRating,$ratingsCount);

        $html .= '<span class="cg_five_star_details_to_insert_orientation"></span>';
        $html .= '<div class="cg_five_star_details_arrow_up"></div>';
        $html .= '</div>'; // details

        $html .= esc_html($ratingDisplayFormatted).'</div>'; // rating value
        $html .= '<div class="' . esc_attr($ratingCompactCountClass) . '" data-cg-number-value="' . esc_attr($ratingsTotalCount) . '" aria-label="' . esc_attr($ratingsTotalCountFormatted . ' ' . $ratingCountLabel) . '">(' . esc_html($ratingsTotalCountFormatted) . ')</div>';
        $html .= $cgRateMinus;
        $html .= '</div>'; // child
        $html .= '</div>'; // wrapper

        return $html;
    }

}

if(!function_exists('cg1l_get_rating_gallery_one_star_for_reload')){
    function cg1l_get_rating_gallery_one_star_for_reload($pid,$galeryIDuserForJs,$gidReal,$alreadyVoted,$ratingCount,$shortcode_name,$options,$fullData){
        $ratingStat = 'cg_gallery_rating_div_one_star_off';
        $isVotingPossible = cg1l_is_voting_possible_for_shortcode($shortcode_name,$options);
        $alreadyVotedUi = $alreadyVoted && $isVotingPossible;
        if($ratingCount>=1){
            $ratingStat = 'cg_gallery_rating_div_one_star_on';
        }
        $position = '';
        if(!empty($options['visual']['RatingPositionGallery']) && intval($options['visual']['RatingPositionGallery']) === 2){
            $position = ' cg_center';
        }elseif(!empty($options['visual']['RatingPositionGallery']) && intval($options['visual']['RatingPositionGallery']) === 3){
            $position = ' cg_right';
        }
        $alreadyVoted_class = '';
        $cg_voted_confirm_hide = ' cg_hide';
        if($alreadyVotedUi){
            $alreadyVoted_class = ' cg_already_voted';
            $cg_voted_confirm_hide = '';
        }
        $disallowed = '';
        if(empty($options['general']['RatingOutGallery'])){
            $disallowed = 'cg_rate_out_gallery_disallowed';
        }
        include(__DIR__ . "/../../../../check-language.php");
        $voteTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="'.$language_VoteNow.'"' : '';
        $votedTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="'.$language_YouHaveAlreadyVotedThisPicture.'"' : '';
        $undoTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="'.$language_UndoYourLastVote.'"' : '';
        $cg_rate_minus = '';
        if(!empty($options['pro']['MinusVote']) && intval($options['pro']['MinusVote']) === 1 && !empty($options['general']['RatingOutGallery']) && intval($options['general']['RatingOutGallery']) === 1 && $alreadyVotedUi && $shortcode_name !== 'cg_gallery_user' && $isVotingPossible){
            $cg_rate_minus = '<div data-cg_rate_star_id="'.$pid.'" class="cg_rate_minus" data-cg-gid="'.$galeryIDuserForJs.'"'.$undoTooltipAttr.'></div>';
        }
        if(cg1l_should_hide_rating_until_user_vote($options,$alreadyVoted)){
            $ratingCount = '';
        }
        $ratingCountFormatted = cg1l_format_voting_comment_number($ratingCount,$options,0);
        return '<div class="cg_gallery_rating_div_child'.$position.$alreadyVoted_class.'" id="cg_gallery_rating_div_child'.$pid.'">
              <div class="cg_voted_confirm '.$cg_voted_confirm_hide.'"'.$votedTooltipAttr.'></div>
              <div data-cg-pid="'.$pid.'" data-cg-gid="'.$galeryIDuserForJs.'" data-cg-gid-real="'.$gidReal.'" data-cg_rate_star_id="'.$pid.'" class="cg_rate_star cg_rate_star_gallery cg_gallery_rating_div_star cg_gallery_rating_div_star_one_star '.$ratingStat.' '.$shortcode_name.' '.$disallowed.'" data-cg-shortcode="'.$shortcode_name.'"'.$voteTooltipAttr.'>
              </div>
              <div id="rating_cg-'.$pid.'" class="cg_gallery_rating_div_count" data-cg-number-value="'.esc_attr($ratingCount).'">'.esc_html($ratingCountFormatted).'</div>
              '.$cg_rate_minus.'
            </div>';
    }
}
