<?php

if(!function_exists('cg1l_render_center_div_reload_comment_icon')){
    function cg1l_render_center_div_reload_comment_icon($realId,$jsonCommentsData,$options = array())
    {
        $comments = cg1l_count_comments($realId,$jsonCommentsData);
        $commentsFormatted = cg1l_format_voting_comment_number($comments,$options,0);
        $commentsStat = 'cg_gallery_comments_div_icon_off';
        if($comments>=1){
            $commentsStat = 'cg_gallery_comments_div_icon_on';
        }

        return '<div class="cg_gallery_comments_div_child">
            <div class="cg_gallery_comments_div_icon cgl_comment '.$commentsStat.' cg_gallery_comments_div_icon'.$realId.' cg_inside_center_div"></div>
            <div class="cg_gallery_comments_div_count'.$realId.' cg_gallery_comments_div_count" data-cg-number-value="'.esc_attr($comments).'">'.esc_html($commentsFormatted).'</div>
        </div>';

    }
}

if(!function_exists('cg1l_render_comment_vote_badge')){
    function cg1l_render_comment_vote_badge($comment,$options = array())
    {
        $voteValue = (!empty($comment['vote'])) ? absint($comment['vote']) : ((!empty($comment['Vote'])) ? absint($comment['Vote']) : 0);
        if(empty($voteValue)){
            return '';
        }

        $ratingType = (function_exists('cg1l_get_rating_type')) ? cg1l_get_rating_type($options) : '';
        $isOneStar = ($ratingType === 'one-star');
        $isHeart = (!empty($options['visual']['FeVotingIconType']) && $options['visual']['FeVotingIconType'] === 'heart');
        $icon = ($isHeart) ? '&#9829;' : '&#9733;';
        $classes = 'cg-center-image-comment-vote-badge ';
        $classes .= ($isOneStar) ? 'cg-center-image-comment-vote-badge-one-star ' : 'cg-center-image-comment-vote-badge-multiple ';
        $classes .= ($isHeart) ? 'cg-center-image-comment-vote-badge-heart' : 'cg-center-image-comment-vote-badge-star';
        $value = ($isOneStar) ? '' : '<span class="cg-center-image-comment-vote-badge-value">'.esc_html($voteValue).'</span>';

        return '<span class="'.esc_attr(trim($classes)).'" aria-label="Vote: '.esc_attr($voteValue).'" title="Vote: '.esc_attr($voteValue).'">'.
            $value.'<span class="cg-center-image-comment-vote-badge-icon" aria-hidden="true">'.$icon.'</span></span>';
    }
}

if(!function_exists('cg1l_render_center_div_reload_comment_div')){
    function cg1l_render_center_div_reload_comment_div($comment,$WpUserIdsData,$visibleDate = '',$options = array())
    {
        if(isset($comment['Active']) && $comment['Active'] == 2){
            return '';
        }

        #toDo name
        $WpUserId = 0;
        if(!empty($comment['insert_id']) && !empty($WpUserIdsData['commentsWpUserIdsArray'][$comment['insert_id']])){
            $WpUserId = $WpUserIdsData['commentsWpUserIdsArray'][$comment['insert_id']];
        }

        $preferredProfileImageUrl = cg1l_frontend_get_preferred_wp_user_avatar_url($WpUserId, $WpUserIdsData);
        $nickname = '';

        if(!empty($WpUserIdsData['nicknamesArray'][$WpUserId])){
            $nickname = $WpUserIdsData['nicknamesArray'][$WpUserId];
        }

        if($preferredProfileImageUrl || $nickname){
            $backgroundImage = '';
            $avatar = '';
            $name = esc_html(contest_gal1ery_convert_for_html_output_without_nl2br($nickname));

            if(!empty($preferredProfileImageUrl)){
                $profileImageUrl = esc_url_raw($preferredProfileImageUrl);
                $backgroundImageStyle = esc_attr('background-image: url("' . $profileImageUrl . '");');
                $backgroundImage = '<div class="cg-center-image-comments-profile-image cg-center-show-profile-image-full" data-cg-wp-user-id="'.$WpUserId.'" style="'.$backgroundImageStyle.'"></div>';
            }else{
                $avatar = '<div class="cg-center-image-comments-nickname-avatar"></div>';
            }
            $nameContainer = '<div class="cg-center-image-comments-user-data">
            '.$backgroundImage.'
            '.$avatar.'
            <div class="cg-center-image-comments-nickname-text">'.$name.'</div>
            </div>';
        }else{
            $name = esc_html(contest_gal1ery_convert_for_html_output_without_nl2br($comment['name']));
            $nameContainer = '<div class="cg-center-image-comments-name-date-container '.($name ? '' : 'cg_hide').'">
                <p class="cg-center-image-comments-name-content">'.$name.'</p>
            </div>';
        }

        $timestamp = absint( isset( $comment['timestamp'] ) ? $comment['timestamp'] : 0 );
        $commentText = esc_html(contest_gal1ery_convert_for_html_output_without_nl2br($comment['comment']));
        $visibleDate = esc_html(contest_gal1ery_convert_for_html_output_without_nl2br($visibleDate));
        $voteBadge = cg1l_render_comment_vote_badge($comment,$options);
        $commentId = (!empty($comment['insert_id'])) ? absint($comment['insert_id']) : 0;
        $commentIdAttribute = (!empty($commentId)) ? ' data-cg-comment-id="'.esc_attr($commentId).'"' : '';

        return '<div class="cg-center-image-comments-div"'.$commentIdAttribute.'>
        '.$nameContainer.'
        <p class="cg-center-image-comments-comment-content">'.$commentText.'</p>
        <div class="cg-center-image-comments-meta">
            <div class="cg-center-image-comments-date">
                <p class="cg_comment_timestamp" data-cg-timestamp="'.$timestamp.'">'.$visibleDate.'</p>
            </div>
            '.$voteBadge.'
        </div>
    </div>';
    }
}
