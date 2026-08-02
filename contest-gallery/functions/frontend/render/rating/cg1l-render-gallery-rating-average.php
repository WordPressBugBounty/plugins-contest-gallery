<?php

if(!function_exists('cg1l_get_rating_gallery_average')){
    function cg1l_get_rating_gallery_average($galeryIDuserForJs,$fullData,$options,$shortcode_name,$countSuserVotes,$votedUserPids){
        $ratingDisplayValue = cg1l_get_average_rating_display_value($fullData,$options,$votedUserPids);
        return cg1l_get_rating_gallery_multi_star($galeryIDuserForJs,$fullData,$options,$shortcode_name,$countSuserVotes,$votedUserPids,$ratingDisplayValue);
    }
}
