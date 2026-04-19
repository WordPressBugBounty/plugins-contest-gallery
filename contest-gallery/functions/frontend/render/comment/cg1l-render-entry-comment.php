<?php

if(!function_exists('cg1l_render_center_div_reload_comment_icon')){
    function cg1l_render_center_div_reload_comment_icon($realId,$jsonCommentsData)
    {
        $comments = cg1l_count_comments($realId,$jsonCommentsData);
        $commentsStat = 'cg_gallery_comments_div_icon_off';
        if($comments>=1){
            $commentsStat = 'cg_gallery_comments_div_icon_on';
        }

        return '<div class="cg_gallery_comments_div_child">
            <div class="cg_gallery_comments_div_icon cgl_comment '.$commentsStat.' cg_gallery_comments_div_icon'.$realId.' cg_inside_center_div"></div>
            <div class="cg_gallery_comments_div_count'.$realId.' cg_gallery_comments_div_count">'.$comments.'</div>
        </div>';

    }
}

if(!function_exists('cg1l_render_center_div_reload_comment_div')){
    function cg1l_render_center_div_reload_comment_div($comment,$WpUserIdsData,$visibleDate = '')
    {
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
            $name = contest_gal1ery_convert_for_html_output_without_nl2br($nickname);

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
            $name = contest_gal1ery_convert_for_html_output_without_nl2br($comment['name']);
            $nameContainer = '<div class="cg-center-image-comments-name-date-container '.($name ? '' : 'cg_hide').'">
                <p class="cg-center-image-comments-name-content">'.$name.'</p>
            </div>';
        }

        $timestamp = absint( isset( $comment['timestamp'] ) ? $comment['timestamp'] : 0 );
        $commentText = contest_gal1ery_convert_for_html_output_without_nl2br($comment['comment']);
        $visibleDate = contest_gal1ery_convert_for_html_output_without_nl2br($visibleDate);

        return '<div class="cg-center-image-comments-div">
        '.$nameContainer.'
        <p class="cg-center-image-comments-comment-content">'.$commentText.'</p>
        <div class="cg-center-image-comments-date">
            <p class="cg_comment_timestamp" data-cg-timestamp="'.$timestamp.'">'.$visibleDate.'</p>
        </div>
    </div>';
    }
}
