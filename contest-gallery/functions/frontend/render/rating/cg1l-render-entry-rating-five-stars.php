<?php

if(!function_exists('cg1l_render_rating_component_entry_five_stars')){
    function cg1l_render_rating_component_entry_five_stars($args = array(),$fullData = array(),$options = array(),$shortcode_name = '',$countSuserVotes = 0,$AllowRating = 5,$votedUserPids = array(),$languageNames = array()){
        $args['rating_display_value'] = cg1l_get_five_star_cumulative_value($fullData,$options,$votedUserPids);
        $args['rating_star_value'] = $args['rating_display_value'];

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
