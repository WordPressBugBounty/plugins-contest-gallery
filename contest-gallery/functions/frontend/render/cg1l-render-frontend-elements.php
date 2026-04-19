<?php
if(!function_exists('cg1l_render_vertical_scroll_slider')){
    function cg1l_render_vertical_scroll_slider($args = []) {
        $args = wp_parse_args($args, [
            'gid' => '',
            'show_loader' => false,
            'is_inline' => false,
            'include_nav' => false,
        ]);

        $gid = sanitize_text_field((string)$args['gid']);
        $suffix = $gid !== '' ? $gid : '';
        $show_loader = !empty($args['show_loader']);
        $is_inline = !empty($args['is_inline']);
        $include_nav = !empty($args['include_nav']);

        $loader_classes = ['mainCGdivSliderLoader'];
        if(!$show_loader){
            $loader_classes[] = 'cg_hide';
        }
        if($is_inline){
            $loader_classes[] = 'mainCGdivSliderLoaderInline';
        }

        $slider_id = $suffix !== '' ? 'cglSlider'.$suffix : 'cglSlider';
        $slider_spacer_id = $suffix !== '' ? 'cglSliderSpacer'.$suffix : 'cglSliderSpacer';
        $slider_items_id = $suffix !== '' ? 'cglSliderItems'.$suffix : 'cglSliderItems';
        $slider_nav_id = $suffix !== '' ? 'cglSliderNav'.$suffix : 'cglSliderNav';
        $gid_attr = $gid !== '' ? ' data-cg-gid="'.esc_attr($gid).'"' : '';

        echo '
        <div class="'.esc_attr(implode(' ', $loader_classes)).'"'.$gid_attr.'>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
        </div>';

        if($include_nav){
            echo '
        <div id="'.esc_attr($slider_nav_id).'" class="cgl_nav cg_hide"'.$gid_attr.'>
            <button class="cgl_nav_btn cgl_nav_up" aria-label="Previous">&#9650;</button>
            <button class="cgl_nav_btn cgl_nav_down" aria-label="Next">&#9660;</button>
        </div>';
        }

        echo '
        <div id="'.esc_attr($slider_id).'" class="cglSlider cg_hide"'.$gid_attr.'>
                <div id="'.esc_attr($slider_spacer_id).'" class="cglSliderSpacer">
                </div>
            <div id="'.esc_attr($slider_items_id).'" class="cglSliderItems">
            </div>
        </div>';

        if($is_inline){
            echo '<noscript><style>#'.esc_attr($slider_id).', #'.esc_attr($slider_nav_id).', .mainCGdivSliderLoader[data-cg-gid="'.esc_attr($gid).'"] { display:none !important; }</style></noscript>';
        }
    }

}

if(!function_exists('cg1l_render_full_main_parent')){
    function cg1l_render_full_main_parent($gid,$realGid,$shortcode_name) {
        $gid_attr = htmlspecialchars($gid, ENT_QUOTES, 'UTF-8');
        $gid_data_attr = ' data-cg-gid="'.$gid_attr.'"';
        $skeleton_height_attr = 50;
        $shortcode_name = sanitize_text_field($shortcode_name);

        echo '
    <div id="mainCGdivParent" class="mainCGdivHelperParent cg_hide mainCGwindow"'.$gid_data_attr.'>
        <div class="mainCGdiv cg_fe_controls_style_white cg_border_radius_controls_and_containers mainCGdivFullWindowBlogView cg_display_block cg_margin_0_auto" style="display: block;">
            <div style="visibility: visible; width: 100%; position: relative;"  class="mainCGallery cg_modern_hover  cg_grid cg_fade_in_back_123 cg_animation cg_full_window cg_blog cg_fade_in" >
                <div class="cgCenterDivLoaderContainer"'.$gid_data_attr.'>
                    <div  class="cgCenterImageLoader cg-center-div-skeleton-box" style="height: ' . $skeleton_height_attr . '%;"></div>
                    <div  class="cg-center-info-div cg-center-div-skeleton-box-container cgHundertPercentWidth">
                        <div class="cg-center-div-skeleton-box" style="width:90%;"></div>
                        <div class="cg-center-div-skeleton-box" style="width:100%;"></div>
                        <div class="cg-center-div-skeleton-box" style="width:80%;"></div>
                        <div class="cg-center-div-skeleton-box" style="width:60%;"></div>
                        <div class="cg-center-div-skeleton-box" style="width:40%;"></div>
                    </div>
                </div>                
            </div>
            <div id="cglSliderNav" class="cgl_nav"'.$gid_data_attr.'>
                <button class="cgl_nav_btn cgl_nav_up" aria-label="Previous">&#9650;</button>
                <button class="cgl_nav_btn cgl_nav_down" aria-label="Next">&#9660;</button>
            </div>
        </div>
        <div class="mainCGdivSliderLoader"'.$gid_data_attr.'>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
            <div class="cgl_skeleton">
            </div>
        </div>
        <div id="cglSlider" class="cglSlider"'.$gid_data_attr.'>
                <div id="cglSliderSpacer" class="cglSliderSpacer"'.$gid_data_attr.'>
                </div>
            <div id="cglSliderItems" class="cglSliderItems"'.$gid_data_attr.'>
            </div>
        </div>
    </div>';
    }

}

if(!function_exists('cg1l_render_sharebuttons')){
    function cg1l_render_sharebuttons($shareParentId,$shareMobileBtnId,$options,$fullData,$jsonInfoData,$languageNames) {
        if(empty($options['visual']['ShareButtons'])) {
            return '';
        }else{
            $isProVersion = cg_get_version()=='contest-gallery-pro' ? true : false;
            $title = !empty($fullData['post_title']) ? (string)$fullData['post_title'] : '';
            $entryGuid = !empty($fullData['entryGuid']) ? (string)$fullData['entryGuid'] : '';
            if(empty($entryGuid)){
                return '';
            }
            $description = '';
            $IsForWpPageTitleID = (!empty($options['visual']['IsForWpPageTitleID'])) ? absint($options['visual']['IsForWpPageTitleID']) : 0;
            $IsForWpPageDescriptionID = (!empty($options['visual']['IsForWpPageDescriptionID'])) ? absint($options['visual']['IsForWpPageDescriptionID']) : 0;
            $infoData = $jsonInfoData;
            if (!empty($IsForWpPageTitleID)) {
                if (!empty($infoData) && !empty($infoData[$IsForWpPageTitleID]) && !empty($infoData[$IsForWpPageTitleID]['field-content'])) {
                    $title = str_replace('\\', '', $infoData[$IsForWpPageTitleID]['field-content']);
                }
            }
            if (!empty($IsForWpPageDescriptionID)) {
                if (!empty($infoData) && !empty($infoData[$IsForWpPageDescriptionID]) && !empty($infoData[$IsForWpPageDescriptionID]['field-content'])) {
                    $description = str_replace('\\', '', $infoData[$IsForWpPageDescriptionID]['field-content']);
                }
            }
            $title = str_replace('\\', '', (string)$title);
            $description = str_replace('\\', '', (string)$description);
            $entryGuid = (string)$entryGuid;
            $encodedTitle = rawurlencode($title);
            $encodedDescription = rawurlencode($description);
            $encodedEntryGuid = rawurlencode($entryGuid);
            $encodedEmailBody = rawurlencode($entryGuid."\n\n".$description);
            $encodedSmsBody = rawurlencode($entryGuid."\n".$title);
            $encodedWhatsappText = rawurlencode($title."\n".$entryGuid);
            $shareToLabel = !empty($languageNames['gallery']['ShareTo']) ? $languageNames['gallery']['ShareTo'] : 'Share to';
            $div = '<div id="'.esc_attr($shareMobileBtnId).'" class="cg_center_show_social_share_mobile_button" data-cg-tooltip="Share to..."></div>';
            $div .= '<div id="'.esc_attr($shareParentId).'" class="cg_center_show_social_share_parent">';
            $ShareButtons = explode(',', $options['visual']['ShareButtons']);
            foreach($ShareButtons as $name) {
                $name = trim($name);
                if(empty($name)){
                    continue;
                }
                $platform = '';
                $href = '';
                $classes = '';
                if ($name == 'email') {
                    $platform = 'Email';
                    $href = 'mailto:?subject='.$encodedTitle.'&body='.$encodedEmailBody;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_email';
                } elseif ($name == 'sms') {
                    $platform = 'SMS';
                    $href = 'sms:?body='.$encodedSmsBody;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_sms';
                } elseif ($name == 'gmail') {
                    $platform = 'Gmail';
                    $href = 'https://mail.google.com/mail/?view=cm&body='.$encodedEntryGuid.'&su='.$encodedTitle;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_gmail';
                } elseif ($name == 'yahoo') {
                    $platform = 'Yahoo';
                    $href = 'https://compose.mail.yahoo.com/?body=' . $encodedEntryGuid . '&subject=' . $encodedTitle;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_yahoo';
                } elseif ($name == 'evernote') {
                    $platform = 'Evernote';
                    $href = 'https://www.evernote.com/clip.action?url=' . $encodedEntryGuid . '&title=' . $encodedTitle;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_evernote';
                } elseif ($name == 'facebook' && $isProVersion) {
                    $platform = 'Facebook';
                    $href = 'https://www.facebook.com/sharer.php?u=' . $encodedEntryGuid;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_facebook';
                } elseif ($name == 'whatsapp' && $isProVersion) {
                    $platform = 'WhatsApp';
                    $href = 'https://api.whatsapp.com/send?text=' . $encodedWhatsappText;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_whatsapp';
                } elseif ($name == 'twitter' && $isProVersion) {
                    $platform = 'Twitter';
                    $href = 'https://twitter.com/intent/tweet?url=' . $encodedEntryGuid . '&text=' . $encodedTitle;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_twitter';
                } elseif ($name == 'skype') {
                    $platform = 'Skype';
                    $href = 'https://web.skype.com/share?url=' . $encodedEntryGuid . '&text=' . $encodedTitle;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_skype';
                } elseif ($name == 'telegram') {
                    $platform = 'Telegram';
                    $href = 'https://t.me/share/url?url=' . $encodedEntryGuid . '&text=' . $encodedTitle;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_telegram';
                } elseif ($name == 'pinterest') {
                    $platform = 'Pinterest';
                    $href = 'https://pinterest.com/pin/create/link/?url=' . $encodedEntryGuid;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_pinterest';
                } elseif ($name == 'reddit') {
                    $platform = 'Reddit';
                    $href = 'https://reddit.com/submit?url=' . $encodedEntryGuid . '&title=' . $encodedTitle;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_reddit';
                } elseif ($name == 'xing') {
                    $platform = 'XING';
                    $href = 'https://www.xing.com/spi/shares/new?url=' . $encodedEntryGuid;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_xing';
                } elseif ($name == 'linkedin') {
                    $platform = 'LinkedIn';
                    $href = 'https://www.linkedin.com/sharing/share-offsite/?url=' . $encodedEntryGuid;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_linkedin';
                } elseif ($name == 'vk' && $isProVersion) {
                    $platform = 'VK';
                    $href = 'https://vk.com/share.php?url=' . $encodedEntryGuid . '&title=' . $encodedTitle . '&comment=' . $encodedDescription;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_vk';
                } elseif ($name == 'okru' && $isProVersion) {
                    $platform = 'OK';
                    $href = 'https://connect.ok.ru/dk?st.cmd=WidgetSharePreview&st.shareUrl=' . $encodedEntryGuid;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_okru';
                } elseif ($name == 'qzone' && $isProVersion) {
                    $platform = 'Qzone';
                    $href = 'https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' . $encodedEntryGuid . '&title=' . $encodedTitle . '&summary=' . $encodedDescription;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_qzone';
                } elseif ($name == 'weibo' && $isProVersion) {
                    $platform = 'Weibo';
                    $href = 'https://service.weibo.com/share/share.php?url=' . $encodedEntryGuid . '&title=' . $encodedTitle;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_weibo';
                } elseif ($name == 'douban' && $isProVersion) {
                    $platform = 'Douban';
                    $href = 'https://www.douban.com/recommend/?href=' . $encodedEntryGuid . '&comment=' . $encodedEntryGuid . '&name=' . $encodedTitle . '&text=' . $encodedDescription;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_douban';
                } elseif ($name == 'renren' && $isProVersion) {
                    $platform = 'RenRen';
                    $href = 'http://widget.renren.com/dialog/share?resourceUrl=' . $encodedEntryGuid . '&srcUrl=' . $encodedEntryGuid . '&title=' . $encodedTitle . '&description=' . $encodedDescription;
                    $classes = 'cg_center_show_social_share cg_center_show_social_share_renren';
                }
                if(empty($href) || empty($classes)){
                    continue;
                }
                $div  .= '<a href="' . esc_attr($href) . '" target="_blank" rel="noopener noreferrer" class="cg_center_show_social_share_link" ';
                $div .= 'data-cg-tooltip="' . esc_attr($shareToLabel . ' ' . $platform) . '">';
                $div .= '<div class="' . esc_attr($classes) . '"></div></a>';
            }
            $div .= '</div>';
            return $div;
        }
    }
}
