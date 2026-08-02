<?php

if (!function_exists('cg1l_is_voting_possible_for_shortcode')) {
    function cg1l_is_voting_possible_for_shortcode($shortcode_name, $options = [])
    {
        if ($shortcode_name === 'cg_gallery') {
            return true;
        }

        if ($shortcode_name === 'cg_gallery_ecommerce' && !empty($options['general']['AllowRatingForGalleryEcommerce'])) {
            return true;
        }

        return false;
    }
}

/**
 * Shared multi-star entry markup used by cumulative and average rating controllers.
 *
 * - No schema.org AggregateRating output
 * - No bestRating/worstRating meta tags
 * - Star range comes ONLY from $options / $args (simple integer $AllowRating)
 * - No ?? operator, no @ operator, arrays with []
 * - WordPress escaping used
 *
 * check-language.php provides the localized voting tooltips.
 */

if (!function_exists('cg1l_render_rating_component_entry_multi_stars')) {
    /**
     * $args:
     * - gid, order, real_id, real_gid
     * - rating_display_value (int|float|string)
     * - rating_star_value (integer used for data attributes and on/off state)
     * - already_voted (bool)
     * - aria_label (string)
     * - rating_distribution (array) optional [star => count]
     *
     * $AllowRating:
     * - integer count of stars to show (e.g. 5, 7)
     */
    function cg1l_render_rating_component_entry_multi_stars($args = [], $fullData = [], $options = [], $shortcode_name = '', $countSuserVotes = 0, $AllowRating = 5, $votedUserPids = [],$languageNames = [])
    {
        $d = array_merge([
            'gid' => 0,
            'order' => 0,
            'real_id' => 0,
            'real_gid' => 0,
            'rating_display_value' => null,
            'rating_star_value' => null,
            'already_voted' => false,
            'aria_label' => 'Ratings',
            'rating_distribution' => [], // optional [star => count]
        ], $args);

        $gid = sanitize_text_field((string)$d['gid']);
        $order = intval($d['order']);
        $realId = intval($d['real_id']);
        $realGid = intval($d['real_gid']);

        $alreadyVoted = cg1l_has_current_user_voted_multi_star($fullData,$votedUserPids);
        $ariaLabel = $d['aria_label'];
        $isVotingPossible = cg1l_is_voting_possible_for_shortcode($shortcode_name, $options);
        $alreadyVotedUi = $alreadyVoted && $isVotingPossible;

        $AllowRating = intval($AllowRating);
        if ($AllowRating < 1) { $AllowRating = 5; }

        $ratingDivId = 'cgCenterImageRatingDiv' . $gid . '-' . $order;
        $galleryRatingId = 'cg_gallery_rating_div' . $realId;

        $ratingDisplayValue = $d['rating_display_value'];
        if($ratingDisplayValue === null){
            $ratingDisplayValue = cg1l_get_five_star_cumulative_value($fullData,$options,$votedUserPids);
        }
        $ratingStarValue = $d['rating_star_value'];
        if($ratingStarValue === null){
            $ratingStarValue = ($ratingDisplayValue > 0) ? 1 : 0;
        }

        $hideRatingUntilUserVote = cg1l_should_hide_rating_until_user_vote($options,$alreadyVoted);
        if($hideRatingUntilUserVote){
            $ratingDisplayValue = '';
            $ratingStarValue = 0;
        }

        $distribution = [];
        if (!empty($d['rating_distribution']) && is_array($d['rating_distribution'])) {
            $distribution = $d['rating_distribution'];
        }else{
            $distribution = cg1l_get_multi_star_rating_distribution($fullData,$options,$votedUserPids);
        }

        $reloadHtml = cg1l_render_center_div_reload_multi_stars(
            $realId,
            $gid,
            $realGid,
            $alreadyVotedUi,
            $ratingDisplayValue,
            $ratingStarValue,
            $AllowRating,
            $distribution,
            $shortcode_name,
            $languageNames,
            $options,
            $hideRatingUntilUserVote
        );

        $galleryRatingClass = 'cg_gallery_rating_div cg_gallery_rating_div_five_stars';
        if(!$isVotingPossible){
            $galleryRatingClass .= ' cg_gallery_rating_read_only';
        }

        return '<div id="' . esc_attr($ratingDivId) . '" class="cg-center-image-rating-div cgHundertPercentWidth" role="group" aria-label="' . esc_attr($ariaLabel) . '">
    <div class="' . esc_attr($galleryRatingClass) . '" id="' . esc_attr($galleryRatingId) . '">
        ' . $reloadHtml . '
    </div>
</div>';
    }
}

if (!function_exists('cg1l_render_center_div_reload_multi_stars')) {
    function cg1l_render_center_div_reload_multi_stars(
        $realId,
        $gid,
        $realGid,
        $alreadyVoted,
        $ratingDisplayValue,
        $ratingStarValue,
        $AllowRating,
        $distribution,
        $shortcode_name,
        $languageNames,
        $options = [],
        $hideRatingUntilUserVote = false
    ) {
        $realId = intval($realId);
        $gid = sanitize_text_field((string)$gid);
        $realGid = intval($realGid);

        $AllowRating = intval($AllowRating);
        if ($AllowRating < 1) { $AllowRating = 5; }

        $isVotingPossible = cg1l_is_voting_possible_for_shortcode($shortcode_name, $options);
        $childClass = 'cg_gallery_rating_div_child cg_gallery_rating_div_child_five_star cg_gallery_rating_compact_layout';
        if(!$isVotingPossible){
            $childClass .= ' cg_gallery_rating_read_only';
        }
        if (!empty($alreadyVoted)) {
            $childClass .= ' cg_already_voted';
        }

        $galleryRatingChildId = 'cg_gallery_rating_div_child' . $realId;
        $ratingCountId = 'rating_cg-' . $realId;
        include(__DIR__ . "/../../../../check-language.php");
        $ratingsTotalCount = 0;
        if(!empty($distribution) && is_array($distribution)){
            foreach($distribution as $ratingCount){
                $ratingsTotalCount += max(intval($ratingCount),0);
            }
        }
        $ratingCountLabel = ($ratingsTotalCount === 1) ? $language_Rating : $language_Ratings;
        if(empty($ratingCountLabel)){
            $ratingCountLabel = ($ratingsTotalCount === 1) ? 'rating' : 'ratings';
        }
        $ratingCompactCountClass = 'cg_gallery_rating_compact_vote_count';
        if($hideRatingUntilUserVote){
            $ratingCompactCountClass .= ' cg_hide';
        }
        $ratingDisplayDecimals = (cg1l_get_rating_type($options) === 'average') ? 1 : 0;
        $ratingDisplayFormatted = cg1l_format_voting_comment_number($ratingDisplayValue,$options,$ratingDisplayDecimals);
        $ratingsTotalCountFormatted = cg1l_format_voting_comment_number($ratingsTotalCount,$options,0);
        $voteTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="' . esc_attr($languageNames['gallery']['VoteNow']) . '"' : '';
        $votedTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="' . esc_attr($languageNames['gallery']['YouHaveAlreadyVotedThisPicture']) . '"' : '';
        $undoTooltipAttr = $isVotingPossible ? ' data-cg-tooltip="' . esc_attr($languageNames['gallery']['UndoYourLastVote']) . '"' : '';
        $cgRateMinus = '';
        if(!empty($options['pro']['MinusVote']) && intval($options['pro']['MinusVote']) === 1 && !empty($alreadyVoted) && $shortcode_name !== 'cg_gallery_user' && $isVotingPossible){
            $cgRateMinus = '<div data-cg-real-id="' . esc_attr($realId) . '" class="cg_rate_minus cg_rate_minus_five_star" data-cg-gid="' . esc_attr($gid) . '"' . $undoTooltipAttr . '></div>';
        }

        // Default selected star shown in compact view (JS can override on hover/click)

        $starClass = 'cg_rate_star cg_rate_star_five_star cg_gallery_rating_div_star_one_star ';

        if (!empty($alreadyVoted)) {
            $starClass .= ' cg_gallery_rating_div_one_star_on';
        }

        // Visual on/off in compact view (optional)
        $starOnOff = ($ratingStarValue > 0) ? ' cg_gallery_rating_div_one_star_on' : ' cg_gallery_rating_div_one_star_off';

        $detailsHtml = cg1l_render_multi_star_details_box($gid, $realId, $AllowRating, $distribution);

        return '<div class="' . $childClass . '" id="' . esc_attr($galleryRatingChildId) . '" data-cg-gid="' . esc_attr($gid) . '" data-cg-real-id="' . esc_attr($realId) . '" data-cg-shortcode="' . esc_attr($shortcode_name) . '">
    <div class="cg_voted_confirm"' . $votedTooltipAttr . '></div>

    <div class="cg_gallery_rating_div_star"' . $voteTooltipAttr . ' data-cg-gid="' . esc_attr($gid) . '" data-cg-shortcode="' . esc_attr($shortcode_name) . '"></div>

    <div
        data-cg_rate_star="' . esc_attr($ratingStarValue) . '"
        data-cg-gid="' . esc_attr($gid) . '"
        class="' . $starClass . $starOnOff . '"
        data-cg-real-id="' . esc_attr($realId) . '"
        data-cg-gid-real="' . esc_attr($realGid) . '"
        data-cg-shortcode="' . esc_attr($shortcode_name) . '"
    ></div>

    <div id="' . $ratingCountId . '" class="cg_gallery_rating_div_count" data-cg-number-value="' . esc_attr($ratingDisplayValue) . '">
        <div class="cg_gallery_rating_div_star_hover">^</div>
        ' . $detailsHtml . '
        ' . esc_html($ratingDisplayFormatted) . '
    </div>
    <div class="' . esc_attr($ratingCompactCountClass) . '" data-cg-number-value="' . esc_attr($ratingsTotalCount) . '" aria-label="' . esc_attr($ratingsTotalCountFormatted . ' ' . $ratingCountLabel) . '">(' . esc_html($ratingsTotalCountFormatted) . ')</div>
    ' . $cgRateMinus . '
</div>';
    }
}

if (!function_exists('cg1l_render_multi_star_details_box')) {
    function cg1l_render_multi_star_details_box($gid, $realId, $AllowRating, $distribution)
    {
        $gid = sanitize_text_field((string)$gid);
        $realId = intval($realId);

        $AllowRating = intval($AllowRating);
        if ($AllowRating < 1) { $AllowRating = 5; }

        if (empty($distribution) || !is_array($distribution)) {
            $distribution = [];
        }

        $detailsId = 'cgDetails' . $realId;

        $overviewStars = cg1l_render_multi_star_overview_stars($gid, $realId, $AllowRating);
        $rows = cg1l_render_multi_star_distribution_rows($AllowRating, $distribution);

        return '<div class="cg_gallery_rating_div_five_star_details cg_hide" data-cg-gid="'.$gid.'" data-cg-real-id="' .$realId. '" id="' . $detailsId . '" style="">
    <div class="cg_gallery_rating_overview">
        ' . $overviewStars . '
    </div>
    <div class="cg_gallery_rating_div_five_star_details_close_button"></div>
    ' . $rows . '
    <span class="cg_five_star_details_to_insert_orientation"></span>
    <div class="cg_five_star_details_arrow_up"></div>
</div>';
    }
}

if (!function_exists('cg1l_render_multi_star_overview_stars')) {
    function cg1l_render_multi_star_overview_stars($gid, $realId, $AllowRating)
    {
        $gid = sanitize_text_field((string)$gid);
        $realId = intval($realId);

        $AllowRating = intval($AllowRating);
        if ($AllowRating < 1) { $AllowRating = 5; }

        $html = '';

        for ($i = 1; $i <= $AllowRating; $i++) {
            $html .= '<div data-cg_rate_star="' . esc_attr($i) . '" data-cg-gid="' . esc_attr($gid) . '" class="cg_rate_star cg_rate_star_five_star cg_gallery_rating_div_star_one_star cg_gallery_rating_div_one_star_off" data-cg-real-id="' . esc_attr($realId) . '"></div>';
        }

        return $html;
    }
}

if (!function_exists('cg1l_render_multi_star_distribution_rows')) {
    function cg1l_render_multi_star_distribution_rows($AllowRating, $distribution)
    {
        $AllowRating = intval($AllowRating);
        if ($AllowRating < 1) { $AllowRating = 5; }

        if (empty($distribution) || !is_array($distribution)) {
            $distribution = [];
        }

        $html = '';
        $totalCount = 0;

        foreach($distribution as $distributionCount){
            $totalCount += max(0,intval($distributionCount));
        }

        for ($i = $AllowRating; $i >= 1; $i--) {
            $count = 0;
            if (isset($distribution[$i])) {
                $count = max(0,intval($distribution[$i]));
            }

            $percentage = ($totalCount > 0) ? intval(round(($count / $totalCount) * 100)) : 0;

            $html .= '<div class="cg_five_star_details_row" data-cg-rating-count="' . esc_attr($count) . '">
    <div class="cg_five_star_details_row_number">' . esc_html($i) . '</div>
    <div class="cg_five_star_details_row_progress" role="progressbar" aria-label="' . esc_attr($i . ': ' . $percentage . '%') . '" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . esc_attr($percentage) . '">
        <div class="cg_five_star_details_row_progress_fill" style="width: ' . esc_attr($percentage) . '%;"></div>
    </div>
    <div class="cg_five_star_details_row_percentage">' . esc_html($percentage) . '%</div>
</div>';
        }

        return $html;
    }
}

if (!function_exists('cg1l_format_rating_value_display')) {
    function cg1l_format_rating_value_display($ratingValue,$options = array())
    {
        return cg1l_format_voting_comment_number($ratingValue,$options,1);
    }
}
