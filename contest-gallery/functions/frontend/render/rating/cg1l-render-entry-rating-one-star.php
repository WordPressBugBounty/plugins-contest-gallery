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

/**
 * Print the rating UI (single-star style) with accessibility markup only.
 *
 * Works for both normal 1–5 ratings or simple cumulative +1 votes.
 *
 * Usage:
 * cg_render_rating_component(array(
 *   'gid'           => 16,
 *   'order'         => 0,
 *   'real_id'       => 40,
 *   'rating_count'  => 10000,
 *   'rating_value'  => 1,   // average or 1 if cumulative
 *   'best_rating'   => 1,   // for cumulative: 1
 *   'worst_rating'  => 1,   // for cumulative: 1
 *   'already_voted' => true,
 *   'star_on'       => true,
 * ));
 */
if(!function_exists('cg1l_render_rating_component_entry_one_star')){
    function cg1l_render_rating_component_entry_one_star($args = array(),$fullData = [], $options = [],$shortcode_name = '',$countSuserVotes = 0,$votedUserPids = [])
    {
        $d = array_merge(array(
            'gid' => 0,
            'order' => 0,
            'real_id' => 0,
            'real_gid' => 0,
            'rating_count' => 0,
            'rating_value' => 0,
            'best_rating' => 5,
            'worst_rating' => 1,
            'already_voted' => false,
            'star_on' => false,
            'aria_label' => 'Ratings',
            'tooltip_voted' => 'You have already voted for this entry',
            'tooltip_vote' => 'Vote',
        ), $args);

        // Manual assignments (explicit & safe)
        $gid = sanitize_text_field((string)$d['gid']);
        $order = intval($d['order']);
        $realId = intval($d['real_id']);
        $realGid = intval($d['real_gid']);
        $ratingCount = intval($d['rating_count']);
        $ratingValue = floatval($d['rating_value']);
        $bestRating = floatval($d['best_rating']);
        $worstRating = floatval($d['worst_rating']);
        $alreadyVoted = cg1l_has_current_user_voted_for_entry($fullData,$votedUserPids,$options);
        $starOn = !empty($d['star_on']);
        $ariaLabel = $d['aria_label'];
        $tooltipVoted = $d['tooltip_voted'];
        $tooltipVote = $d['tooltip_vote'];
        $isVotingPossible = cg1l_is_voting_possible_for_shortcode($shortcode_name,$options);
        $alreadyVotedUi = $alreadyVoted && $isVotingPossible;

        // IDs
        $ratingDivId = 'cgCenterImageRatingDiv' . $gid . '-' . $order;
        $galleryRatingId = 'cg_gallery_rating_div' . $realId;

        $ratingCount = cg1l_get_rating_count($fullData,$options,$votedUserPids);
        if(!$alreadyVoted && $options['general']['HideUntilVote']==1){
            $ratingCount = 0;
        }

        $cg1l_render_center_div_reload = cg1l_render_center_div_reload_one_star($realId,$gid,$realGid,$alreadyVotedUi,$ratingCount,$shortcode_name,$options);

        $galleryRatingClass = 'cg_gallery_rating_div cg_gallery_rating_div_one_star';

        return '<div id="' . esc_attr($ratingDivId) . '" class="cg-center-image-rating-div cgHundertPercentWidth" role="group" aria-label="' . esc_attr($ariaLabel) . '">
    <div class="' . esc_attr($galleryRatingClass) . '" id="' . esc_attr($galleryRatingId) . '">
        '.$cg1l_render_center_div_reload.' 
    </div>
</div>';
    }
}

if(!function_exists('cg1l_render_center_div_reload_one_star')){
    function cg1l_render_center_div_reload_one_star($realId,$gid,$realGid,$alreadyVoted,$ratingCount,$shortcode_name,$options)
    {
        $ratingCountId = 'rating_cg-' . $realId;
        $isVotingPossible = cg1l_is_voting_possible_for_shortcode($shortcode_name,$options);
        $starClass = 'cg_rate_star cg_rate_star_entry cg_gallery_rating_div_star cg_gallery_rating_div_star_one_star ';
        $starClass .= $ratingCount ? ' cg_gallery_rating_div_one_star_on' : 'cg_gallery_rating_div_one_star_off';
        $starOnOff = $ratingCount ? ' cgl_on' : 'cgl_off';

        // Classes
        $childClass = 'cg_gallery_rating_div_child cg_entry_rating_div_child';
        if ($alreadyVoted) {
            $childClass .= ' cg_already_voted';
        }

        $galleryRatingChildId = 'cg_entry_rating_div_child' . $realId;

        include(__DIR__ . "/../../../../check-language.php");
        $voteTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="' . esc_attr($language_VoteNow) . '"' : '';
        $votedTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="' . esc_attr($language_YouHaveAlreadyVotedThisPicture) . '"' : '';
        $undoTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="' . esc_attr($language_UndoYourLastVote) . '"' : '';
        $cgRateMinus = '';
        if(!empty($options['pro']['MinusVote']) && intval($options['pro']['MinusVote']) === 1 && $alreadyVoted && $shortcode_name !== 'cg_gallery_user' && $isVotingPossible){
            $cgRateMinus = '<div data-cg_rate_star_id="' . esc_attr($realId) . '" class="cg_rate_minus" data-cg-gid="' . esc_attr($gid) . '"' . $undoTooltipAttr . '></div>';
        }

        if(!$alreadyVoted && $options['general']['HideUntilVote']==1){
            $ratingCount = '';
        }

        return '<div class="' . esc_attr($childClass) . '" id="' . esc_attr($galleryRatingChildId) . '" data-cg-gid="' . esc_attr($gid) . '" data-cg-real-id="' . esc_attr($realId) . '" data-cg-shortcode="' . esc_attr($shortcode_name) . '">
            <div class="cg_voted_confirm"' . $votedTooltipAttr . '></div>
            <div
                data-cg-pid="' . esc_attr($realId) . '"
                data-cg-gid="' . esc_attr($gid) . '"
                data-cg-gid-real="' . esc_attr($realGid) . '"
                data-cg_rate_star_id="' . esc_attr($realId) . '"
                class="' . esc_attr($starClass) . ' cgl_rating '.$starOnOff.'" data-cg-shortcode="' . esc_attr($shortcode_name) . '"
                ' . $voteTooltipAttr . '>
            </div>
            <div id="' . esc_attr($ratingCountId) . '" class="cg_gallery_rating_div_count" >' . $ratingCount . '</div>
            ' . $cgRateMinus . '
        </div>';

    }
}
