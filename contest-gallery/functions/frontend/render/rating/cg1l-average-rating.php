<?php

if(!function_exists('cg1l_get_average_rating_metrics')){
    function cg1l_get_average_rating_metrics($fullData,$options,$votedUserPids = array(),$isCGalleries = false){
        $metrics = array(
            'ratingCount' => 0,
            'ratingTotal' => 0,
            'ratingValue' => 0,
            'bestRating' => 0,
            'worstRating' => 1,
        );
        $ratingLimit = cg1l_get_multi_star_rating_limit($options);

        if($ratingLimit < 2){
            return $metrics;
        }

        $metrics['bestRating'] = $ratingLimit;
        $showOnlyUsersVotes = !empty($options['general']['ShowOnlyUsersVotes']) && intval($options['general']['ShowOnlyUsersVotes']) === 1;

        if($showOnlyUsersVotes && !$isCGalleries){
            $realId = cg1l_get_rating_data_value($fullData,'id');
            $userVotes = ($realId && !empty($votedUserPids[$realId]) && is_array($votedUserPids[$realId])) ? $votedUserPids[$realId] : array();

            foreach($userVotes as $userVote){
                $ratingValue = intval($userVote);
                if($ratingValue >= 1 && $ratingValue <= $ratingLimit){
                    $metrics['ratingCount']++;
                    $metrics['ratingTotal'] += $ratingValue;
                }
            }
        }else{
            $includeManipulation = !empty($options['pro']['Manipulate']) && intval($options['pro']['Manipulate']) === 1;

            for($ratingValue = 1;$ratingValue <= $ratingLimit;$ratingValue++){
                $ratingCount = cg1l_get_rating_data_value($fullData,'CountR'.$ratingValue);
                if($includeManipulation){
                    $ratingCount += cg1l_get_rating_data_value($fullData,'addCountR'.$ratingValue);
                }
                $metrics['ratingCount'] += $ratingCount;
                $metrics['ratingTotal'] += ($ratingCount * $ratingValue);
            }
        }

        if($metrics['ratingCount'] > 0){
            $metrics['ratingValue'] = round($metrics['ratingTotal'] / $metrics['ratingCount'],1);
        }

        return $metrics;
    }
}

if(!function_exists('cg1l_get_average_rating_display_value')){
    function cg1l_get_average_rating_display_value($fullData,$options,$votedUserPids = array(),$isCGalleries = false){
        $metrics = cg1l_get_average_rating_metrics($fullData,$options,$votedUserPids,$isCGalleries);
        return $metrics['ratingValue'];
    }
}

if(!function_exists('cg1l_get_entry_aggregate_rating_schema')){
    function cg1l_get_entry_aggregate_rating_schema($fullData,$options,$shortcodeName){
        if(!cg1l_is_average_rating_enabled($options)){
            return array();
        }

        if(!empty($options['general']['HideUntilVote']) || !empty($options['general']['ShowOnlyUsersVotes'])){
            return array();
        }

        if($shortcodeName === 'cg_gallery_user'){
            return array();
        }

        if($shortcodeName === 'cg_gallery_no_voting' && empty($options['general']['RatingVisibleForGalleryNoVoting'])){
            return array();
        }

        if(
            $shortcodeName === 'cg_gallery_ecommerce' &&
            empty($options['general']['RatingVisibleForGalleryEcommerce']) &&
            empty($options['general']['AllowRatingForGalleryEcommerce'])
        ){
            return array();
        }

        $metrics = cg1l_get_average_rating_metrics($fullData,$options);
        if(
            $metrics['ratingCount'] < 1 ||
            $metrics['ratingValue'] < $metrics['worstRating'] ||
            $metrics['ratingValue'] > $metrics['bestRating']
        ){
            return array();
        }

        return array(
            '@type' => 'AggregateRating',
            'ratingValue' => $metrics['ratingValue'],
            'ratingCount' => $metrics['ratingCount'],
            'bestRating' => $metrics['bestRating'],
            'worstRating' => $metrics['worstRating'],
        );
    }
}
