<?php

if(!function_exists('cg1l_get_rating_type')){
    function cg1l_get_rating_type($options){
        $allowRating = (!empty($options['general']['AllowRating'])) ? absint($options['general']['AllowRating']) : 0;

        if($allowRating === 2){
            return 'one-star';
        }

        if($allowRating === 1 || ($allowRating >= 12 && $allowRating <= 20)){
            if(!empty($options['general']['AllowRatingAverage']) && intval($options['general']['AllowRatingAverage']) === 1){
                return 'average';
            }

            return 'five-stars';
        }

        return 'none';
    }
}

if(!function_exists('cg1l_is_average_rating_enabled')){
    function cg1l_is_average_rating_enabled($options){
        return cg1l_get_rating_type($options) === 'average';
    }
}

if(!function_exists('cg1l_get_voting_mode_for_gallery')){
    function cg1l_get_voting_mode_for_gallery($options){
        $mode = (!empty($options['general']['DisableVotingForGallery'])) ? intval($options['general']['DisableVotingForGallery']) : 0;

        return in_array($mode,array(0,1,2),true) ? $mode : 0;
    }
}

if(!function_exists('cg1l_is_rating_visible_for_shortcode')){
    function cg1l_is_rating_visible_for_shortcode($shortcode_name,$options = array()){
        if(cg1l_get_rating_type($options) === 'none'){
            return false;
        }

        if($shortcode_name === 'cg_gallery' && cg1l_get_voting_mode_for_gallery($options) === 2){
            return false;
        }

        if($shortcode_name === 'cg_gallery_no_voting' && empty($options['general']['RatingVisibleForGalleryNoVoting'])){
            return false;
        }

        if(
            $shortcode_name === 'cg_gallery_ecommerce' &&
            empty($options['general']['RatingVisibleForGalleryEcommerce']) &&
            empty($options['general']['AllowRatingForGalleryEcommerce'])
        ){
            return false;
        }

        return true;
    }
}

if(!function_exists('cg1l_is_voting_possible_for_shortcode')){
    function cg1l_is_voting_possible_for_shortcode($shortcode_name,$options = array()){
        if(cg1l_get_rating_type($options) === 'none'){
            return false;
        }

        if($shortcode_name === 'cg_gallery'){
            return cg1l_get_voting_mode_for_gallery($options) === 0;
        }

        if($shortcode_name === 'cg_gallery_ecommerce' && !empty($options['general']['AllowRatingForGalleryEcommerce'])){
            return true;
        }

        return false;
    }
}

if(!function_exists('cg1l_get_shortcode_name_from_gallery_id_user')){
    function cg1l_get_shortcode_name_from_gallery_id_user($galleryIdUser,$galleryId){
        $galleryIdUser = (string)$galleryIdUser;
        $galleryId = (string)absint($galleryId);

        if($galleryIdUser === $galleryId.'-u' || strpos($galleryIdUser,$galleryId.'-u-ext-') === 0){
            return 'cg_gallery_user';
        }

        if($galleryIdUser === $galleryId.'-nv' || strpos($galleryIdUser,$galleryId.'-nv-ext-') === 0){
            return 'cg_gallery_no_voting';
        }

        if($galleryIdUser === $galleryId.'-w' || strpos($galleryIdUser,$galleryId.'-w-ext-') === 0){
            return 'cg_gallery_winner';
        }

        if($galleryIdUser === $galleryId.'-ec' || strpos($galleryIdUser,$galleryId.'-ec-ext-') === 0){
            return 'cg_gallery_ecommerce';
        }

        if($galleryIdUser === $galleryId || strpos($galleryIdUser,$galleryId.'-ext-') === 0){
            return 'cg_gallery';
        }

        return '';
    }
}

if(!function_exists('cg1l_get_multi_star_rating_limit')){
    function cg1l_get_multi_star_rating_limit($options){
        $allowRating = (!empty($options['general']['AllowRating'])) ? intval($options['general']['AllowRating']) : 0;

        if($allowRating === 1){
            return 5;
        }

        if($allowRating >= 12 && $allowRating <= 20){
            return $allowRating - 10;
        }

        return 0;
    }
}

if(!function_exists('cg1l_get_rating_data_value')){
    function cg1l_get_rating_data_value($fullData,$key){
        if(is_array($fullData) && isset($fullData[$key])){
            return intval($fullData[$key]);
        }

        if(is_object($fullData) && isset($fullData->{$key})){
            return intval($fullData->{$key});
        }

        return 0;
    }
}

if(!function_exists('cg1l_get_rating_entry_id')){
    function cg1l_get_rating_entry_id($fullData){
        if(is_array($fullData) && !empty($fullData['id'])){
            return absint($fullData['id']);
        }

        if(is_object($fullData) && !empty($fullData->id)){
            return absint($fullData->id);
        }

        return 0;
    }
}

if(!function_exists('cg1l_has_current_user_voted_one_star')){
    function cg1l_has_current_user_voted_one_star($fullData,$votedUserPids = array()){
        $realId = cg1l_get_rating_entry_id($fullData);

        if(!$realId || empty($votedUserPids) || !is_array($votedUserPids)){
            return false;
        }

        foreach($votedUserPids as $votedPid){
            if(is_scalar($votedPid) && absint($votedPid) === $realId){
                return true;
            }
        }

        return false;
    }
}

if(!function_exists('cg1l_has_current_user_voted_multi_star')){
    function cg1l_has_current_user_voted_multi_star($fullData,$votedUserPids = array()){
        $realId = cg1l_get_rating_entry_id($fullData);

        if(!$realId || empty($votedUserPids) || !is_array($votedUserPids)){
            return false;
        }

        return !empty($votedUserPids[$realId]);
    }
}

if(!function_exists('cg1l_has_current_user_voted_for_entry')){
    function cg1l_has_current_user_voted_for_entry($fullData,$votedUserPids = array(),$options = array()){
        if(cg1l_get_rating_type($options) === 'one-star'){
            return cg1l_has_current_user_voted_one_star($fullData,$votedUserPids);
        }

        return cg1l_has_current_user_voted_multi_star($fullData,$votedUserPids);
    }
}

if(!function_exists('cg1l_should_hide_rating_until_user_vote')){
    function cg1l_should_hide_rating_until_user_vote($options,$alreadyVoted){
        return !empty($options['general']['HideUntilVote']) && !$alreadyVoted;
    }
}

if(!function_exists('cg1l_get_one_star_rating_count')){
    function cg1l_get_one_star_rating_count($fullData,$options,$votedUserPids = array(),$isCGalleries = false){
        if(!empty($options['general']['ShowOnlyUsersVotes']) && !$isCGalleries){
            $realId = cg1l_get_rating_entry_id($fullData);
            $rating = 0;

            foreach($votedUserPids as $votedPid){
                if(is_scalar($votedPid) && absint($votedPid) === $realId){
                    $rating++;
                }
            }

            return $rating;
        }

        $rating = absint(cg1l_get_rating_data_value($fullData,'CountS'));
        if(!empty($options['pro']['Manipulate'])){
            $rating += absint(cg1l_get_rating_data_value($fullData,'addCountS'));
        }

        return $rating;
    }
}

if(!function_exists('cg1l_get_multi_star_user_rating_total')){
    function cg1l_get_multi_star_user_rating_total($fullData,$options,$votedUserPids = array()){
        $realId = cg1l_get_rating_entry_id($fullData);
        $ratingLimit = cg1l_get_multi_star_rating_limit($options);
        $rating = 0;

        if(!$realId || !$ratingLimit || empty($votedUserPids[$realId]) || !is_array($votedUserPids[$realId])){
            return 0;
        }

        foreach($votedUserPids[$realId] as $value){
            $value = intval($value);
            if($value >= 1 && $value <= $ratingLimit){
                $rating += $value;
            }
        }

        return $rating;
    }
}

if(!function_exists('cg1l_get_multi_star_rating_distribution')){
    function cg1l_get_multi_star_rating_distribution($fullData,$options,$votedUserPids = array(),$isCGalleries = false){
        $realId = cg1l_get_rating_entry_id($fullData);
        $ratingLimit = cg1l_get_multi_star_rating_limit($options);
        $distribution = array();
        $ratingValue = 0;

        for($ratingValue = 1;$ratingValue <= $ratingLimit;$ratingValue++){
            $distribution[$ratingValue] = 0;
        }

        if(!empty($options['general']['ShowOnlyUsersVotes']) && !$isCGalleries){
            if(!$realId || empty($votedUserPids[$realId]) || !is_array($votedUserPids[$realId])){
                return $distribution;
            }

            foreach($votedUserPids[$realId] as $ratingValue){
                $ratingValue = intval($ratingValue);
                if($ratingValue >= 1 && $ratingValue <= $ratingLimit){
                    $distribution[$ratingValue]++;
                }
            }

            return $distribution;
        }

        $includeManipulation = !empty($options['pro']['Manipulate']) && intval($options['pro']['Manipulate']) === 1;

        for($ratingValue = 1;$ratingValue <= $ratingLimit;$ratingValue++){
            $ratingCount = cg1l_get_rating_data_value($fullData,'CountR'.$ratingValue);
            if($includeManipulation){
                $ratingCount += cg1l_get_rating_data_value($fullData,'addCountR'.$ratingValue);
            }
            $distribution[$ratingValue] = max(0,intval($ratingCount));
        }

        return $distribution;
    }
}

if(!function_exists('cg1l_get_five_star_cumulative_value')){
    function cg1l_get_five_star_cumulative_value($fullData,$options,$votedUserPids = array(),$isCGalleries = false){
        if(!empty($options['general']['ShowOnlyUsersVotes']) && !$isCGalleries){
            return cg1l_get_multi_star_user_rating_total($fullData,$options,$votedUserPids);
        }

        $ratingLimit = cg1l_get_multi_star_rating_limit($options);
        $includeManipulation = !empty($options['pro']['Manipulate']) && intval($options['pro']['Manipulate']) === 1;
        $rating = 0;

        for($ratingValue = 1;$ratingValue <= $ratingLimit;$ratingValue++){
            $ratingCount = cg1l_get_rating_data_value($fullData,'CountR'.$ratingValue);
            if($includeManipulation){
                $ratingCount += cg1l_get_rating_data_value($fullData,'addCountR'.$ratingValue);
            }
            $rating += ($ratingCount * $ratingValue);
        }

        return $rating;
    }
}

if(!function_exists('cg1l_get_rating_display_value')){
    function cg1l_get_rating_display_value($fullData,$options,$votedUserPids = array(),$isCGalleries = false){
        $ratingType = cg1l_get_rating_type($options);

        if($ratingType === 'one-star'){
            return cg1l_get_one_star_rating_count($fullData,$options,$votedUserPids,$isCGalleries);
        }

        if($ratingType === 'average'){
            return cg1l_get_average_rating_display_value($fullData,$options,$votedUserPids,$isCGalleries);
        }

        if($ratingType === 'five-stars'){
            return cg1l_get_five_star_cumulative_value($fullData,$options,$votedUserPids,$isCGalleries);
        }

        return 0;
    }
}

if(!function_exists('cg1l_get_rating_count')){
    function cg1l_get_rating_count($fullData,$options,$votedUserPids = array(),$isCGalleries = false){
        return cg1l_get_rating_display_value($fullData,$options,$votedUserPids,$isCGalleries);
    }
}

if(!function_exists('cg1l_count_votes_for_an_entry')){
    function cg1l_count_votes_for_an_entry($pid,$votedUserPids,$options){
        $fullData = array('id' => $pid);

        if(cg1l_get_rating_type($options) === 'one-star'){
            return cg1l_get_one_star_rating_count($fullData,$options,$votedUserPids);
        }

        return cg1l_get_multi_star_user_rating_total($fullData,$options,$votedUserPids);
    }
}
