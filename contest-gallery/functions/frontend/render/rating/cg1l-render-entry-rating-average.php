<?php

if(!function_exists('cg1l_render_rating_component_entry_average')){
    function cg1l_render_rating_component_entry_average($args = array(),$fullData = array(),$options = array(),$shortcode_name = '',$countSuserVotes = 0,$AllowRating = 5,$votedUserPids = array(),$languageNames = array()){
        $args['rating_display_value'] = cg1l_get_average_rating_display_value($fullData,$options,$votedUserPids);
        $args['rating_star_value'] = ($args['rating_display_value'] > 0) ? 1 : 0;

        return cg1l_render_rating_component_entry_multi_stars(
            $args,
            $fullData,
            $options,
            $shortcode_name,
            $countSuserVotes,
            $AllowRating,
            $votedUserPids,
            $languageNames
        );
    }
}
