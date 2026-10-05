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


// Shared catalogue for PHP first paint and every JavaScript sharing surface.
function cg1l_share_config(){
    $providers = array(
        'whatsapp'=>array('WhatsApp','https://api.whatsapp.com/send?text={whatsapp}'),
        'telegram'=>array('Telegram','https://t.me/share/url?url={url}&text={title}'),
        'email'=>array(__('Email','contest-gallery'),'mailto:?subject={title}&body={email}'),
        'facebook'=>array('Facebook','https://www.facebook.com/sharer.php?u={url}'),
        'twitter'=>array('X / Twitter','https://twitter.com/intent/tweet?url={url}&text={title}'),
        'pinterest'=>array('Pinterest','https://pinterest.com/pin/create/link/?url={url}'),
        'linkedin'=>array('LinkedIn','https://www.linkedin.com/sharing/share-offsite/?url={url}'),
        'sms'=>array('SMS','sms:?body={sms}'),
        'reddit'=>array('Reddit','https://reddit.com/submit?url={url}&title={title}'),
        'gmail'=>array('Gmail','https://mail.google.com/mail/?view=cm&body={url}&su={title}'),
        'yahoo'=>array('Yahoo','https://compose.mail.yahoo.com/?body={url}&subject={title}'),
        'xing'=>array('XING','https://www.xing.com/spi/shares/new?url={url}'),
        'evernote'=>array('Evernote','https://www.evernote.com/clip.action?url={url}&title={title}'),
        'skype'=>array('Skype','https://web.skype.com/share?url={url}&text={title}'),
        'vk'=>array('VK','https://vk.com/share.php?url={url}&title={title}&comment={description}'),
        'okru'=>array('OK','https://connect.ok.ru/dk?st.cmd=WidgetSharePreview&st.shareUrl={url}'),
        'weibo'=>array('Weibo','https://service.weibo.com/share/share.php?url={url}&title={title}'),
        'qzone'=>array('Qzone','https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url={url}&title={title}&summary={description}'),
        'douban'=>array('Douban','https://www.douban.com/recommend/?href={url}&comment={url}&name={title}&text={description}'),
        'renren'=>array('RenRen','http://widget.renren.com/dialog/share?resourceUrl={url}&srcUrl={url}&title={title}&description={description}')
    );
    $proOnly = array('facebook','twitter','vk','okru','weibo','qzone','douban','renren');
    $platforms = array();
    foreach($providers as $id=>$provider){
        if(cg_get_version()!=='contest-gallery-pro' && in_array($id,$proOnly,true)){continue;}
        $platforms[] = array('id'=>$id,'label'=>$provider[0],'href'=>$provider[1],'icon'=>plugins_url(cg_get_version().'/assets/icons/fe/social/social-share-'.$id.'.png'));
    }
    $paths = array(
        'share'=>'<path d="M8.5 10.5l7-4m-7 7l7 4"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="5" r="3"/><circle cx="18" cy="19" r="3"/>',
        'copyOriginal'=>'<rect x="8" y="7" width="11" height="14" rx="2"/><path d="M15 7V3H4v14h4"/>',
        'openOriginal'=>'<path d="M13 3h6v6M19 3l-9 9M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5"/>',
        'download'=>'<path d="M12 3v12m-6-6 6 6 6-6M4 18v3h16v-3"/>',
        'copyLink'=>'<path d="m10 13 4-4m-6 6-2 2a4 4 0 0 1-6-6l5-5a4 4 0 0 1 6 0m2 2 2-2a4 4 0 0 1 6 6l-5 5a4 4 0 0 1-6 0" transform="translate(1 1)"/>',
        'native'=>'<path d="M12 16V3m-4 4 4-4 4 4M5 12v9h14v-9"/>'
    );
    $icons = array();
    foreach($paths as $key=>$path){$icons[$key] = '<svg class="cg-action-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$path.'</svg>';}
    return array('platforms'=>$platforms,'icons'=>$icons);
}
function cg1l_file_action_content($type,$label){
    $config = cg1l_share_config();
    return $config['icons'][$type].'<span class="cg-action-label">'.esc_html($label).'</span>';
}
function cg1l_share_strings(){
    return array('Share this entry','Take a look at this entry','Copy link','More apps…','Link copied','Copy this link:','Sharing is unavailable. You can copy the link instead.','File actions');
}

if(!function_exists('cg1l_render_sharebuttons')){
    function cg1l_render_sharebuttons($shareParentId,$shareMobileBtnId,$options,$fullData,$jsonInfoData,$languageNames) {
        if(empty($options['visual']['ShareButtons'])) {
            return '';
        }else{
            $title = cg_member_t('Take a look at this entry');
            if(isset($fullData['Active']) && intval($fullData['Active'])!==1){return '';}
            $entryGuid = isset($fullData['ShareUrl']) ? $fullData['ShareUrl'] : (!empty($fullData['entryGuid']) ? (string)$fullData['entryGuid'] : '');
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
            $title = html_entity_decode(wp_strip_all_tags(str_replace('\\', '', (string)$title)),ENT_QUOTES,'UTF-8');
            $description = html_entity_decode(wp_strip_all_tags(str_replace('\\', '', (string)$description)),ENT_QUOTES,'UTF-8');
            $entryGuid = (string)$entryGuid;
            $encodedTitle = rawurlencode($title);
            $encodedDescription = rawurlencode($description);
            $encodedEntryGuid = rawurlencode($entryGuid);
            $encodedEmailBody = rawurlencode($entryGuid."\n\n".$description);
            $encodedSmsBody = rawurlencode($entryGuid."\n".$title);
            $encodedWhatsappText = rawurlencode($title."\n".$entryGuid);
            $shareToLabel = !empty($languageNames['gallery']['ShareTo']) ? $languageNames['gallery']['ShareTo'] : 'Share to';
            $div = '<div id="'.esc_attr($shareMobileBtnId).'" class="cg_center_show_social_share_mobile_button" data-cg-tooltip="'.esc_attr($shareToLabel).'"></div>';
            $div .= '<div id="'.esc_attr($shareParentId).'" class="cg_center_show_social_share_parent cg-share-labeled">';
            $ShareButtons = array_map('trim',explode(',', $options['visual']['ShareButtons']));
            $config = cg1l_share_config();
            $values = array('{url}'=>$encodedEntryGuid,'{title}'=>$encodedTitle,'{description}'=>$encodedDescription,'{email}'=>$encodedEmailBody,'{sms}'=>$encodedSmsBody,'{whatsapp}'=>$encodedWhatsappText);
            foreach($config['platforms'] as $platform){
                if(!in_array($platform['id'],$ShareButtons,true)){continue;}
                if($platform['id']==='email' && !empty($languageNames['gallery']['Email'])){$platform['label'] = $languageNames['gallery']['Email'];}
                $div .= '<a href="'.esc_attr(strtr($platform['href'],$values)).'" target="_blank" rel="noopener noreferrer" class="cg_center_show_social_share_link cg-action-button" data-cg-platform="'.esc_attr($platform['id']).'" data-cg-tooltip="'.esc_attr($shareToLabel.' '.$platform['label']).'">';
                $div .= '<img class="cg-share-platform-icon" src="'.esc_url($platform['icon']).'" width="22" height="22" alt=""><span class="cg-action-label">'.esc_html($platform['label']).'</span></a>';
            }
            $div .= '</div>';
            return $div;
        }
    }
}
