<?php

include_once(__DIR__.'/../../vars/general/emojis.php');

/**
 * Print Contest Gallery single/entry view HTML.
 * Usage:
 * cg_render_single_entry_view([
 *   'gid'            => 12,
 *   'order'          => 0,
 *   'real_id'        => 158,
 *   'cat_id'         => 91,
 *   'position_info'  => '1 of 100',
 *   'img_src_full'   => 'https://cg-pro.com/wp-content/uploads/2025/10/36contest-gallery_53949583.jpg',
 *   'img_src_683x'   => 'https://cg-pro.com/wp-content/uploads/2025/10/36contest-gallery_53949583-683x1024.jpg',
 *   'download_name'  => '36contest-gallery_53949583.jpg',
 *   'title_1'        => 'dsafsad',
 *   'category'       => 'People',
 *   'title_2'        => 'title123',
 *   'description'    => 'description123',
 *   'rating_count'   => 0,
 *   'date_badge'     => '2 Days',
 * ]);
 */
if(!function_exists('cg1l_render_single_entry_view')){
    function cg1l_render_single_entry_view($args,$fullData,$options,$shortcode_name,$jsonCommentsData,$countSuserVotes,$jsonInfoData,$languageNames,$queryDataArray,$singleViewOrderFullData,$formUploadFullData,$categoriesFullData,$WpUserIdsData,$votedUserPids,$sliderView,$imagesFullDataLength) {

        $d = array_merge([
            'gid'            => 0,
            'gid_js'         => '',
            'order'          => 0,
            'real_id'        => 0,
            'real_gid'        => 0,
            'cat_id'         => 0,
            'position_info'  => '',
            'img_src_full'   => '',
            'img_src_683x'   => '',
            'download_name'  => '',
            'title_1'        => '',
            'category'       => '',
            'title_2'        => '',
            'description'    => '',
            'rating_count'   => 0,
            'date_badge'     => '',
            'ecommerce_files_data' => [],
            'ecommerce_options' => [],
            'currencies_array' => [],
        ], $args);

        // Shorthands with WP-safe escaping
        $gid   = intval($d['gid']);
        $gidJs = (!empty($d['gid_js'])) ? sanitize_text_field($d['gid_js']) : sanitize_text_field(strval($d['gid']));
        if($sliderView){
            $ord   = '';
            $orderAttr = '';
        }else{
            $ord   = '-'.intval($d['order']);
            $orderAttr = intval($d['order']);
        }
        $rid   = intval($d['real_id']);
        $realId   = $rid;
        $realGid   = intval($d['real_gid']);
        $cat   = intval($d['cat_id']);
        $ImgType = $fullData['ImgType'];

        $imgFull   = esc_url($fullData['guid']);
        $img683x   = esc_url($d['img_src_683x']);
        $dlName    = esc_attr($fullData['NamePic'].'.'.strtolower($ImgType));
        $title1    = esc_html($d['title_1']);
        $category  = esc_html($d['category']);
        $title2    = esc_html($d['title_2']);
        $desc      = esc_html($d['description']);
        $ratingCnt = intval($d['rating_count']);
        $dateBadge = esc_html($d['date_badge']);
        $ecommerceFilesData = (is_array($d['ecommerce_files_data'])) ? $d['ecommerce_files_data'] : [];
        $ecommerceOptions = (is_array($d['ecommerce_options'])) ? $d['ecommerce_options'] : [];
        $currenciesArray = (is_array($d['currencies_array'])) ? $d['currencies_array'] : [];
        $galleryLanguageNames = (!empty($languageNames['gallery']) && is_array($languageNames['gallery'])) ? $languageNames['gallery'] : [];

        $getGalleryLanguageName = function($key, $fallback = '') use ($galleryLanguageNames) {
            if(isset($galleryLanguageNames[$key]) && $galleryLanguageNames[$key] !== ''){
                return $galleryLanguageNames[$key];
            }
            return $fallback;
        };

        $getModernDateLabel = function($timestamp) use ($getGalleryLanguageName) {
            $timestamp = absint($timestamp);
            if(empty($timestamp)){
                return '';
            }

            $time = intval(current_time('timestamp', true)) - $timestamp;
            if($time < 1){
                $time = 1;
            }

            if($time < 60){
                return $time.' '.(($time === 1) ? $getGalleryLanguageName('Second', 'Second') : $getGalleryLanguageName('Seconds', 'Seconds'));
            }elseif($time < (60 * 60)){
                $time = intval($time / 60);
                return $time.' '.(($time === 1) ? $getGalleryLanguageName('Minute', 'Minute') : $getGalleryLanguageName('Minutes', 'Minutes'));
            }elseif($time < (60 * 60 * 24)){
                $time = intval($time / (60 * 60));
                return $time.' '.(($time === 1) ? $getGalleryLanguageName('Hour', 'Hour') : $getGalleryLanguageName('Hours', 'Hours'));
            }elseif($time < (60 * 60 * 24 * 7)){
                $time = intval($time / (60 * 60 * 24));
                return $time.' '.(($time === 1) ? $getGalleryLanguageName('Day', 'Day') : $getGalleryLanguageName('Days', 'Days'));
            }elseif($time < (60 * 60 * 24 * 7 * 30)){
                $time = intval($time / (60 * 60 * 24 * 7));
                return $time.' '.(($time === 1) ? $getGalleryLanguageName('Week', 'Week') : $getGalleryLanguageName('Weeks', 'Weeks'));
            }elseif($time < (60 * 60 * 24 * 7 * 30 * 12)){
                $time = intval($time / (60 * 60 * 24 * 7 * 30));
                return $time.' '.(($time === 1) ? $getGalleryLanguageName('Month', 'Month') : $getGalleryLanguageName('Months', 'Months'));
            }else{
                $time = intval($time / (60 * 60 * 24 * 7 * 30 * 12));
                if($time < 1){
                    $time = 1;
                }
                return $time.' '.(($time === 1) ? $getGalleryLanguageName('Year', 'Year') : $getGalleryLanguageName('Years', 'Years'));
            }
        };

        $emojisDiv = cg1l_render_emojis_div();

        $formatConfiguredDate = function($timestamp, $format) use ($getModernDateLabel) {
            $timestamp = absint($timestamp);
            if(empty($timestamp)){
                return '';
            }

            $dateFormatMap = [
                'YYYY-MM-DD' => 'Y-m-d H:i',
                'DD-MM-YYYY' => 'd-m-Y H:i',
                'MM-DD-YYYY' => 'm-d-Y H:i',
                'YYYY/MM/DD' => 'Y/m/d H:i',
                'DD/MM/YYYY' => 'd/m/Y H:i',
                'MM/DD/YYYY' => 'm/d/Y H:i',
                'YYYY.MM.DD' => 'Y.m.d H:i',
                'DD.MM.YYYY' => 'd.m.Y H:i',
                'MM.DD.YYYY' => 'm.d.Y H:i',
            ];

            if($format === 'modern'){
                return $getModernDateLabel($timestamp);
            }

            $phpFormat = (!empty($dateFormatMap[$format])) ? $dateFormatMap[$format] : $format;
            return cg_get_time_based_on_wp_timezone_conf($timestamp, $phpFormat);
        };

        $formatExifDateTimeOriginal = function($dateTimeOriginal, $format) {
            if(empty($dateTimeOriginal)){
                return '';
            }

            $dateTimeOriginal = trim((string)$dateTimeOriginal);
            $dateTimeOriginalSplitted = explode(':', $dateTimeOriginal);

            if(count($dateTimeOriginalSplitted) < 3){
                return $dateTimeOriginal;
            }

            $dateTimeOriginalObject = [
                'YYYY' => trim($dateTimeOriginalSplitted[0]),
                'MM' => trim($dateTimeOriginalSplitted[1]),
                'DD' => trim(explode(' ', $dateTimeOriginalSplitted[2])[0]),
            ];

            if(empty($format)){
                $format = 'YYYY-MM-DD';
            }

            return str_replace(
                ['YYYY', 'MM', 'DD'],
                [$dateTimeOriginalObject['YYYY'], $dateTimeOriginalObject['MM'], $dateTimeOriginalObject['DD']],
                $format
            );
        };

        $renderExifInfoRow = function($classes, $imageClass, $textClass, $value) {
            return '<div class="'.esc_attr($classes).'"><span class="'.esc_attr($imageClass).'"></span><span class="'.esc_attr($textClass).'">'.contest_gal1ery_convert_for_html_output_without_nl2br($value).'</span></div>';
        };

        // IDs used many times
        $centerDivId          = "cgCenterDiv{$gidJs}{$ord}";
        $centerDivChildId     = "cgCenterDivChild{$gidJs}{$ord}";
        $centerOrientationId  = "cgCenterOrientation{$gidJs}{$ord}";
        $centerHelperId       = "cgCenterDivHelper{$gidJs}{$ord}";
        $sliderInfoId         = "cgCenterSliderPositionInfo{$gidJs}{$ord}";
        $buttonsId            = "cgCenterImageDivButtons{$gidJs}{$ord}";
        $goUpBtnId            = "cgCenterGoUpButton{$gidJs}{$ord}";
        $fullCfgId            = "cgCenterDivCenterImageFullWindowConfiguration{$gidJs}{$ord}";
        $fsBtnId              = "cgCenterImageFullScreenButton{$gidJs}{$ord}";
        $closeId              = "cgCenterImageClose{$gidJs}{$ord}";
        $fullWindowId         = "cgCenterImageFullwindow{$gidJs}{$ord}";
        $helperHelperId       = "cgCenterDivHelperHelper{$gidJs}{$ord}";
        $helper3Id            = "cgCenterDivHelperHelperHelper{$gidJs}{$ord}";
        $imgDivId             = "cgCenterImageDiv{$gidJs}{$ord}";
        $shareMobileBtnId     = "cgCenterShowSocialShareMobileButton{$gidJs}{$ord}";
        $shareParentId        = "cgCenterShowSocialShareParent{$gidJs}{$ord}";
        $imgParentId          = "cgCenterImageParent{$gidJs}{$ord}";
        $imgId                = "cgCenterImage{$gidJs}{$ord}";
        $imgRotId             = "cgCenterImageRotated{$gidJs}{$ord}";
        $exifId               = "cgCenterImageExifData{$gidJs}{$ord}";
        $exifSubId            = "cgCenterImageExifDataSub{$gidJs}{$ord}";
        $rateButtonsWrapId    = "cgCenterImageRatingAndButtonsDiv{$gidJs}{$ord}";
        $leftBtnsId           = "cgCenterImageDivButtonsLeft{$gidJs}{$ord}";
        $ratingDivId          = "cgCenterImageRatingDiv{$gidJs}{$ord}";
        $saleId               = "cgCenterSale{$gidJs}{$ord}";
        $infoDivId            = "cgCenterInfoDiv{$gidJs}{$ord}";
        $infoParentId         = "cgCenterImageInfoDivParent{$gidJs}{$ord}";
        $infoEditIconWrapId   = "cgCenterImageInfoEditIconContainer{$gidJs}{$ord}";
        $infoEditIconId       = "cgCenterImageInfoEditIcon{$gidJs}{$ord}";
        $infoEditTextId       = "cgCenterImageInfoSaveText{$gidJs}{$ord}";
        $infoTitleId          = "cgCenterImageInfoDivTitle{$gidJs}{$ord}";
        $scrollInfoWrapId     = "cgCenterImageInfoDivParentParent{$gidJs}{$ord}";
        $infoContainerId      = "cgCenterImageInfoDiv{$gidJs}{$ord}";
        $saveIconTextWrapId   = "cgCenterImageInfoSaveIconTextContainer{$gidJs}{$ord}";
        $saveIconId           = "cgCenterImageInfoSaveIcon{$gidJs}{$ord}";
        $saveTextId           = "cgCenterImageInfoSaveText{$gidJs}{$ord}";
        $commentsParentId     = "cgCenterImageCommentsDivParent{$gidJs}{$ord}";
        $commentsTitleId      = "cgCenterImageCommentsDivTitle{$gidJs}{$ord}";
        $commentsPPId         = "cgCenterImageCommentsDivParentParent{$gidJs}{$ord}";
        $commentsListId       = "cgCenterImageCommentsDiv{$gidJs}{$ord}";
        $commentsEnterId      = "cgCenterImageCommentsDivEnter{$gidJs}{$ord}";
        $commentsEnterTitleId = "cgCenterImageCommentsDivEnterTitle{$gidJs}{$ord}";
        $commentsEnterTextId  = "cgCenterImageCommentsDivEnterTextarea{$gidJs}{$ord}";
        $commentsSubmitId     = "cgCenterImageCommentsDivEnterSubmit{$gidJs}{$ord}";

        $additionalFilesCount = 0;
        if(!empty($queryDataArray[$rid]['MultipleFiles'])){
            $additionalFilesCount = count($queryDataArray[$rid]['MultipleFiles'])-1;
        }

        $additionalFilesButtons = '';
        if($additionalFilesCount){
            $additionalFilesButtons = '<div class="cg-center-image-additional-file-button cg-center-image-prev-file cg_hide" data-cg-gid="'.$gidJs.'" data-cg-tooltip="' . $languageNames['gallery']['PreviousFileInEntry'] . '"  data-cg-real-id="'.$rid.'"></div>'."\r\n";
            $additionalFilesButtons .= '<div class="cg-center-image-additional-file-button cg-center-image-next-file " data-cg-gid="'.$gidJs.'" data-cg-tooltip="' . $languageNames['gallery']['NextFileInEntry'] . '" data-cg-real-id="'.$rid.'"></div>'."\r\n";
        }
                            
        // Rating ids
        $galleryRatingId      = "cg_gallery_rating_div{$rid}";
        $galleryRatingChildId = "cg_gallery_rating_div_child{$rid}";
        $ratingCountId        = "rating_cg-{$rid}";

        $id = $fullData['id'];
        $imgSrcLarge = $fullData['large'];

        $attrs = cg1l_get_image_attributes($fullData);

        $WidthAttribute  = $attrs['WidthAttribute'];
        $HeightAttribute = $attrs['HeightAttribute'];
        $rThumb          = $attrs['rThumb'];
        $imgStyle        = $attrs['imgStyle'];

        $ratingDiv = '';
        $allowRating = (!empty($options['general']['AllowRating'])) ? absint($options['general']['AllowRating']) : 0;
        $shouldRenderRating = ($allowRating === 1 || $allowRating === 2 || $allowRating >= 12);

        if($shouldRenderRating && $shortcode_name === 'cg_gallery_no_voting' && empty($options['general']['RatingVisibleForGalleryNoVoting'])){
            $shouldRenderRating = false;
        }

        if(
            $shouldRenderRating &&
            $shortcode_name === 'cg_gallery_ecommerce' &&
            empty($options['general']['RatingVisibleForGalleryEcommerce']) &&
            empty($options['general']['AllowRatingForGalleryEcommerce'])
        ){
            $shouldRenderRating = false;
        }

        if($shouldRenderRating){
            if($allowRating === 2){
                $ratingDiv = cg1l_render_rating_component_entry_one_star(['gid'=> $gidJs, 'real_id' => $rid, 'real_gid' => $realGid],$fullData,$options,$shortcode_name,$countSuserVotes,$votedUserPids);
            }else{
                if($allowRating === 1){
                    $AllowRating = 5;
                }else{
                    $AllowRating = $allowRating - 10;
                }

                $order = 0;

                $ratingDiv = cg1l_render_rating_component_entry_multi_stars([
                    'gid' => $gidJs,
                    'order' => $order,
                    'real_id' => $rid,
                    'real_gid' => $realGid,
                    'already_voted' => true,
                    'rating_distribution' => [1=>0,2=>0,3=>0,4=>0,5=>1,6=>0,7=>0],
                ], $fullData, $options, $shortcode_name, $countSuserVotes, $AllowRating,$votedUserPids,$languageNames);
            }
        }

        $commentsDiv = '';

        if($options['general']['AllowComments'] == 1 || $options['general']['AllowComments'] == 2){
            $cg_gallery_comments_div_child = cg1l_render_center_div_reload_comment_icon($rid,$jsonCommentsData);
            $isLoggedIn = is_user_logged_in();
            $isCommentFormVisible = ($options['general']['AllowComments'] == 1);
            $hideCommentNameField = (!empty($options['general']['HideCommentNameField'])) ? intval($options['general']['HideCommentNameField']) : 0;
            $isCommentNameFieldVisible = ($isCommentFormVisible && $hideCommentNameField != 1 && !$isLoggedIn);
            $commentsDateFormat = (!empty($options['visual']['CommentsDateFormat'])) ? $options['visual']['CommentsDateFormat'] : 'modern';

            $userCommments = '';

            $commentsCount = cg1l_count_comments($realId,$jsonCommentsData);
            /*echo "<pre>";
                print_r($jsonCommentsData);
            echo "</pre>";
            die;*/
            if($commentsCount >= 1){
                foreach ($jsonCommentsData[$realId] as $comment){
                    $commentVisibleDate = $formatConfiguredDate((!empty($comment['timestamp']) ? $comment['timestamp'] : 0), $commentsDateFormat);
                    $userCommments .= cg1l_render_center_div_reload_comment_div($comment,$WpUserIdsData,$commentVisibleDate);
                }
            }

            $userCommmentsDivParent = '<div id="'.esc_attr($commentsPPId).'" class="cg-scroll-info-single-image-view cg-center-image-comments-div-parent-parent '.($userCommments ? '' : 'cg_hide').'">
            <div class="cg_comment_loader_container cg_hide">
                <div class="cg_comment_loader" style="width: 35%;margin-bottom: 10px;"></div>
                <div class="cg_comment_loader" style="width: 45%;margin-bottom: 10px;"></div>
                <div class="cg_comment_loader" style="width: 55%;margin-bottom: 10px;"></div>
                <div class="cg_comment_loader" style="width: 65%;"></div>
            </div>
            <span class="cg-top-bottom-arrow cg_hide cg_no_scroll" data-cg-gid="'.$gidJs.'"></span>
            <div id="'.esc_attr($commentsListId).'" class="cgl_comments_list cg-center-image-comments-div-parent  '.($userCommments ? '' : 'cg_hide').'">'.$userCommments.'</div>
            <span class="cg-top-bottom-arrow cg_hide" data-cg-gid="'.$gidJs.'"></span>
</div>';

            $commentsDiv = '<div id="'.esc_attr($commentsParentId).'" class="cg-center-image-comments-div-parent cgHundertPercentWidth">
                        <span id="'.esc_attr($commentsTitleId).'" class="cg-center-image-info-div-title cg-center-image-info-div-title-no-comments">
                            <div class="cg_gallery_comments_div">
                                '.$cg_gallery_comments_div_child.'
                            </div>
                            <hr>
                        </span>
                        '.$userCommmentsDivParent.'
                        <div id="'.esc_attr($commentsEnterId).'" class="cg-scroll-info-single-image-view cg-center-image-comments-div-enter'.($isCommentFormVisible ? '' : ' cg_hide').'">
                            <div class="cg-center-image-comments-user-data cg_hide">
                                <div class="cg-center-image-comments-profile-image cg-center-show-profile-image-full cg_hide"></div>
                                <div class="cg-center-image-comments-nickname-avatar cg_hide"></div>
                                <div class="cg-center-image-comments-nickname-text cg_hide"></div>
                            </div>

                            <div id="cgCenterImageCommentsDivEnterTitleDiv'.$gidJs.$ord.'" data-cg-gid="'.$gidJs.'" class="cg-center-image-comments-div-enter-container cg-center-image-comments-div-enter-title-div'.($isCommentNameFieldVisible ? '' : ' cg_hide').'">
                                <div class="cg-center-image-comments-div-enter-title-label-container">
                                    <label for="cgCenterImageCommentsDivEnterTitle'.$gidJs.$ord.'">Name</label>
                                    <span class="cg-emojis-add cg-emojis-add-title" data-cg-tooltip="Add an emoji"></span>
                                    <span class="cg-center-image-comments-div-enter-counter">99</span>
                                </div>
                                '.$emojisDiv.'
                                <input type="text" maxlength="100" id="'.esc_attr($commentsEnterTitleId).'" class="cg-center-image-comments-div-enter-title cg-center-image-comments-div-enter-contenteditable">
                                <p id="cgCenterImageCommentsDivEnterTitleError'.$gidJs.$ord.'" class="cg_hide cg-center-image-comments-div-enter-title-error"></p>
                            </div>

                            <div id="cgCenterImageCommentsDivEnterTextareaDiv'.$gidJs.$ord.'" data-cg-gid="'.$gidJs.'" class="cg-center-image-comments-div-enter-container cg-center-image-comments-div-enter-textarea-div">
                                <div class="cg-center-image-comments-div-enter-textarea-label-container">
                                    <label for="cgCenterImageCommentsDivEnterTextarea'.$gidJs.$ord.'">Comment</label>
                                    <span class="cg-emojis-add cg-emojis-add-textarea" data-cg-tooltip="Add an emoji"></span>
                                    <span class="cg-center-image-comments-div-enter-counter">999</span>
                                </div>
                                '.$emojisDiv.'
                                <textarea id="'.esc_attr($commentsEnterTextId).'" maxlength="1000" class="cg-center-image-comments-div-enter-textarea cg-center-image-comments-div-enter-contenteditable"></textarea>
                                <p id="cgCenterImageCommentsDivEnterTextareaError'.$gidJs.$ord.'" class="cg_hide cg-center-image-comments-div-enter-textarea-error"></p>
                            </div>

                            <div id="cgCenterImageCommentsDivEnterSubmitDiv'.$gidJs.$ord.'" class="cg-center-image-comments-div-enter-submit-div">
                                <button id="'.esc_attr($commentsSubmitId).'" class="cg_hover_effect cg-center-image-comments-div-enter-submit"
                                 data-cg-gid="'.$gidJs.'" data-cg-pid="'.esc_attr($realId).'" 
                                  data-cg-gid-real="'.esc_attr($realGid).'" data-cg-shortcode="'.esc_attr($shortcode_name).'">Send</button>
                            </div>
                        </div>
          </div>';
        }

        /*
        if (cgJsData[gid].options.visual.CopyOriginalFileLink == '1' && ImgType != 'con' &&  !cgJsData[gid].vars.isOnlyGalleryEcommerce) {
            cgCenterDiv.find('.cg-copy-original-file-link').attr({
                    'data-cg-gid': gid,
                    'data-cg-real-id': realId
                }).removeClass('cg_hide');
            }*/


        $copyOriginalFileDiv = '';

        if (isset($options['visual']['CopyOriginalFileLink']) && $options['visual']['CopyOriginalFileLink'] == '1' && $fullData['ImgType'] != 'con' &&  $shortcode_name != 'cg_gallery_ecommerce') {
            $copyOriginalFileDiv = '<div class="cg-copy-original-file-link" data-cg-tooltip="'.esc_attr($languageNames['gallery']['CopyOriginalFileSourceLink']).'" title="'.esc_attr($languageNames['gallery']['CopyOriginalFileSourceLink']).'" data-cg-gid="'.$gidJs.'" data-cg-real-id="'.$rid.'"></div>';
        }
        /*
            if (cgJsData[gid].options.visual.ForwardOriginalFile == '1' && ImgType != 'con'  &&  !cgJsData[gid].vars.isOnlyGalleryEcommerce) {
                var $cgForwardOriginalFile = cgCenterDiv.find('.cg-forward-original-file');*/

        $forwardOriginalFileLink = '';

        if (isset($options['visual']['ForwardOriginalFile']) && $options['visual']['ForwardOriginalFile'] == '1' && $fullData['ImgType'] != 'con' &&  $shortcode_name != 'cg_gallery_ecommerce') {
            $forwardOriginalFileLink = '<a href="'.$imgFull.'" target="_blank"><div class="cg-forward-original-file" data-cg-tooltip="'.esc_attr($languageNames['gallery']['OpenOriginalFileInNewTab']).'" title="'.esc_attr($languageNames['gallery']['OpenOriginalFileInNewTab']).'"></div></a>';
        }

        $downloadOriginalFileLink = '';

        if (isset($options['visual']['OriginalSourceLinkInSlider']) && $options['visual']['OriginalSourceLinkInSlider'] == '1' && $fullData['ImgType'] != 'con' &&  $shortcode_name != 'cg_gallery_ecommerce') {
            $downloadOriginalFileLink = '<a href="'.$imgFull.'" class="cg-center-image-download-link" download="'.$dlName.'" target="_blank"><div class="cg-center-image-download" data-cg-tooltip="'.esc_attr($languageNames['gallery']['DownloadOriginalFile']).'" title="'.esc_attr($languageNames['gallery']['DownloadOriginalFile']).'"></div></a>';
        }

        $copyImageHref = '';

        if (isset($options['visual']['CopyImageLink']) && $options['visual']['CopyImageLink'] == '1' && $fullData['ImgType'] != 'con') {
            $copyImageHref = '<div class="cg-image-image-href-to-copy cg_gallery_control_element" data-cg-gid="'.$gidJs.'" data-cg-tooltip="'.esc_attr($languageNames['gallery']['CopyGalleryEntryLink']).'" title="'.esc_attr($languageNames['gallery']['CopyGalleryEntryLink']).'" data-cg-real-id="'.$rid.'" style="display: block; margin-right: 0px;"></div>';
        }

        $ratingAndButtonsDiv = '';
        $entryActionButtons = '';

        if($copyOriginalFileDiv || $forwardOriginalFileLink || $downloadOriginalFileLink || $copyImageHref){
            $entryActionButtons = '<div class="cg_delete_user_image cg_hide" data-cg-gid="'.$gidJs.'"></div>
                                    '.$copyOriginalFileDiv.'
                                    '.$forwardOriginalFileLink.'
                                    '.$downloadOriginalFileLink.'
                                    '.$copyImageHref;
        }

        /* if (cgJsData[gid].options.visual.OriginalSourceLinkInSlider == '1'  &&  !cgJsData[gid].vars.isOnlyGalleryEcommerce) {

            if ($cgCenterImage.closest('.cg-center-image-download-link').length) {
            $cgCenterImage.unwrap();
        }

    }
            if (cgJsData[gid].options.visual.CopyImageLink == '1') {
                if (ImgType == 'con') {
                    cgCenterDiv.find('.cg-image-image-href-to-copy').attr('data-cg-real-id', realId).attr('data-cg-tooltip', cgJsClass.gallery.language[gid].CopyGalleryEntryLink).show();
                } else {
                    cgCenterDiv.find('.cg-image-image-href-to-copy').attr('data-cg-real-id', realId).show();
                }
            }
         * */

        $meta = cg1l_frontend_get_meta_html($fullData['ImgType'],$fullData);
        $metaComment = cg1l_frontend_get_meta_comment_count($fullData);
        $itemTypeObject = cg1l_get_itemtype_object($fullData['ImgType']);

        $divSocialShareParent = cg1l_render_sharebuttons($shareParentId,$shareMobileBtnId,$options,$fullData,$jsonInfoData,$languageNames);

        $singleViewContent = cg1l_frontend_get_info_data_single_view($fullData,$gid,$jsonInfoData,$singleViewOrderFullData,$categoriesFullData,$languageNames);
        $infoContent = $singleViewContent['infoContent'];
        $ariaDescribedby = $singleViewContent['ariaDescribedby'];

        $timestamp = (!empty($fullData['Timestamp'])) ? absint($fullData['Timestamp']) : 0;
        $showDateMode = (!empty($options['visual']['ShowDate'])) ? absint($options['visual']['ShowDate']) : 0;
        $showDateInEntryView = in_array($showDateMode,array(1,3),true);
        if(!$showDateInEntryView && !empty($showDateMode) && !in_array($showDateMode,array(1,2,3),true)){
            $showDateInEntryView = true;
        }
        $showDateFormat = (!empty($options['visual']['ShowDateFormat'])) ? $options['visual']['ShowDateFormat'] : 'modern';
        $dateInfoContent = '';
        if($showDateInEntryView && $timestamp){
            $dateTimeInvisibleSeo = cg_get_time_based_on_wp_timezone_conf($timestamp,'Y-m-d\TH:i:sP');
            $dateTimeVisible = $formatConfiguredDate($timestamp, $showDateFormat);
            if(!empty($dateTimeVisible)){
                $dateInfoContent = '<div class="cg-center-image-info-div cg-center-image-info-div-date"><p class="cg-center-image-info-div-content cg_date" data-cg-timestamp="'.absint($timestamp).'"><time datetime="'.esc_attr($dateTimeInvisibleSeo).'">'.esc_html($dateTimeVisible).'</time></p></div>';
            }
        }

        $exifInfoContent = '';
        if(!empty($options['pro']['ShowExif']) && intval($options['pro']['ShowExif']) === 1){
            $exifData = [];
            $exifDateTimeOriginal = '';
            $exifModel = '';
            $exifApertureFNumber = '';
            $exifExposureTime = '';
            $exifISOSpeedRatings = '';
            $exifFocalLength = '';

            if(!empty($fullData['Exif'])){
                $exifData = is_array($fullData['Exif']) ? $fullData['Exif'] : maybe_unserialize($fullData['Exif']);
            }

            if((!is_array($exifData) || empty($exifData)) && !empty($queryDataArray[$rid]['Exif'])){
                $exifData = is_array($queryDataArray[$rid]['Exif']) ? $queryDataArray[$rid]['Exif'] : maybe_unserialize($queryDataArray[$rid]['Exif']);
            }

            if(is_array($exifData) && !empty($exifData)){
                if((!isset($options['general']['ShowExifDateTimeOriginal']) || intval($options['general']['ShowExifDateTimeOriginal']) === 1) && !empty($exifData['DateTimeOriginal'])){
                    $formattedDateTimeOriginal = $formatExifDateTimeOriginal(
                        $exifData['DateTimeOriginal'],
                        (!empty($options['general']['ShowExifDateTimeOriginalFormat'])) ? $options['general']['ShowExifDateTimeOriginalFormat'] : 'YYYY-MM-DD'
                    );

                    if(!empty($formattedDateTimeOriginal)){
                        $exifDateTimeOriginal = $formattedDateTimeOriginal;
                    }
                }

                if(!isset($options['general']['ShowExifModel']) || intval($options['general']['ShowExifModel']) === 1){
                    $makeAndModel = '';
                    if(!empty($exifData['MakeAndModel'])){
                        $makeAndModel = $exifData['MakeAndModel'];
                    }elseif(!empty($exifData['Model'])){
                        $makeAndModel = $exifData['Model'];
                    }

                    if(!empty($makeAndModel)){
                        $exifModel = $makeAndModel;
                    }
                }

                if((!isset($options['general']['ShowExifApertureFNumber']) || intval($options['general']['ShowExifApertureFNumber']) === 1) && !empty($exifData['ApertureFNumber'])){
                    $exifApertureFNumber = $exifData['ApertureFNumber'];
                }

                if((!isset($options['general']['ShowExifExposureTime']) || intval($options['general']['ShowExifExposureTime']) === 1) && !empty($exifData['ExposureTime'])){
                    $exifExposureTime = $exifData['ExposureTime'];
                }

                if((!isset($options['general']['ShowExifISOSpeedRatings']) || intval($options['general']['ShowExifISOSpeedRatings']) === 1) && !empty($exifData['ISOSpeedRatings'])){
                    $exifISOSpeedRatings = $exifData['ISOSpeedRatings'];
                }

                if((!isset($options['general']['ShowExifFocalLength']) || intval($options['general']['ShowExifFocalLength']) === 1) && !empty($exifData['FocalLength'])){
                    $exifFocalLength = $exifData['FocalLength'];
                }

            }

            $hasExifRows = (
                !empty($exifDateTimeOriginal) ||
                !empty($exifModel) ||
                !empty($exifApertureFNumber) ||
                !empty($exifExposureTime) ||
                !empty($exifISOSpeedRatings) ||
                !empty($exifFocalLength)
            );

            $exifRows = '';
            $exifRows .= '<div class="cg-exif cg-exif-date-time-original'.(empty($exifDateTimeOriginal) ? ' cg_hide' : '').'"><span class="cg-exif-date-time-original-img"></span><span class="cg-exif-date-time-original-text">'.contest_gal1ery_convert_for_html_output_without_nl2br($exifDateTimeOriginal).'</span></div>';
            $exifRows .= '<div class="cg-exif cg-exif-model'.(empty($exifModel) ? ' cg_hide' : '').'"><span class="cg-exif-model-img"></span><span class="cg-exif-model-text">'.contest_gal1ery_convert_for_html_output_without_nl2br($exifModel).'</span></div>';
            $exifRows .= '<div class="cg-exif cg-exif-aperturefnumber cg-exif'.(empty($exifApertureFNumber) ? ' cg_hide' : '').'"><span class="cg-exif-aperturefnumber-img"></span><span class="cg-exif-aperturefnumber-text">'.contest_gal1ery_convert_for_html_output_without_nl2br($exifApertureFNumber).'</span></div>';
            $exifRows .= '<div class="cg-exif cg-exif-exposuretime cg-exif'.(empty($exifExposureTime) ? ' cg_hide' : '').'"><span class="cg-exif-exposuretime-img"></span><span class="cg-exif-exposuretime-text">'.contest_gal1ery_convert_for_html_output_without_nl2br($exifExposureTime).'</span></div>';
            $exifRows .= '<div class="cg-exif cg-exif-isospeedratings cg-exif'.(empty($exifISOSpeedRatings) ? ' cg_hide' : '').'"><span class="cg-exif-isospeedratings-img"></span><span class="cg-exif-isospeedratings-text">'.contest_gal1ery_convert_for_html_output_without_nl2br($exifISOSpeedRatings).'</span></div>';
            $exifRows .= '<div class="cg-exif cg-exif-focallength cg-exif'.(empty($exifFocalLength) ? ' cg_hide' : '').'"><span class="cg-exif-focallength-img"></span><span class="cg-exif-focallength-text">'.contest_gal1ery_convert_for_html_output_without_nl2br($exifFocalLength).'</span></div>';

            $exifInfoContent = '<div id="'.esc_attr($exifId).'" class="cg-center-image-exif-data'.($hasExifRows ? '' : ' cg_hide').'">
                    <div id="'.esc_attr($exifSubId).'" class="cg-center-image-exif-data-sub">
                        '.$exifRows.'
                    </div>
                </div>';
        }

        $galleryViewContent = cg1l_frontend_get_info_data_gallery($fullData,$formUploadFullData,$categoriesFullData,$languageNames,$options,$jsonInfoData);
        $altAttr = $galleryViewContent['altAttr'];
        $mediaDescription = $galleryViewContent['mediaDescription'];
        $mediaDescriptionAttribute = ($mediaDescription !== '') ? ' aria-label="'.esc_attr($mediaDescription).'"' : '';
        $conEntryPreviewContent = (!empty($galleryViewContent['conEntryPreviewContent'])) ? $galleryViewContent['conEntryPreviewContent'] : '';

        $hasEditableInfoContent = !empty($infoContent);
        $hasInitialInfoContent = ($hasEditableInfoContent || !empty($dateInfoContent));
        $infoTitleClasses = 'cg-center-image-info-div-title cg-center-image-info-info-separator';
        if(!$hasInitialInfoContent){
            $infoTitleClasses .= ' cg_hide';
        }

        $infoEditControls = '';
        if($hasEditableInfoContent){
            $infoEditControls = '<div id="'.esc_attr($infoEditIconWrapId).'" class="cg-center-image-info-edit-icon-container">
                            <div id="'.esc_attr($infoEditIconId).'" class="cg-center-image-info-edit-icon"></div>
                            <div id="'.esc_attr($infoEditTextId).'" class="cg-center-image-info-edit-text">Edit</div>
                        </div>';
        }

        $infoHeaderContent = '';
        if($shortcode_name === 'cg_gallery_user' && !empty($dateInfoContent)){
            $infoHeaderContent = '<div style="padding-left:12px;">'.$dateInfoContent.'</div>';
            $dateInfoContent = '';
        }

        $infoParent = '';
        if($hasInitialInfoContent){
            $infoParent = '<div class="cg-scroll-info-single-image-view cg-center-image-info-div-parent-parent">
            <span class="cg-top-bottom-arrow cg_hide cg_no_scroll" data-cg-gid="'.$gidJs.'"></span>
            <div id="'.esc_attr($infoContainerId).'" class="cg-center-image-info-div-container" style="clear:both;">
                                '.$dateInfoContent.$exifInfoContent.$infoContent.'
            </div>
            <span class="cg-top-bottom-arrow cg_hide" data-cg-gid="'.$gidJs.'"></span>
        </div>';
        }

        $infoSaveControls = '';
        if($hasEditableInfoContent){
            $infoSaveControls = '<div id="'.esc_attr($saveIconTextWrapId).'" class="cg-center-image-info-save-icon-text-container">
                            <div id="'.esc_attr($saveIconId).'" class="cg-center-image-info-save-icon"></div>
                            <div id="'.esc_attr($saveTextId).'" class="cg-center-image-info-save-text">Save</div>
                        </div>';
        }

        $infoSection = '';
        if($hasInitialInfoContent){
            $infoSection = '<div id="'.esc_attr($infoParentId).'" class="cg-center-image-info-div-parent cgHundertPercentWidth">
                        '.$infoHeaderContent.'
                        '.$infoEditControls.'
                        '.$infoParent.'
                        '.$infoSaveControls.'

                        <div id="cgCenterImageCommentsDivShowMoreInfo'.$gidJs.$ord.'" class="cg_hover_effect cg-center-image-comments-div-show-more cg-center-image-comments-div-show-more-info cg_hide" data-cg-tooltip="Show more info"></div>
                    </div>';
        }

        $centerImage = '';
        if(cg_is_is_image($ImgType)){
            $centerImage = '<div id="'.$imgId.'" data-cg-size-width="'.absint($fullData['Width']).'" class="cg-center-image cg_translateX cg_transition" data-cg-image-src-onload="'.esc_attr($imgSrcLarge).'" data-cg-real-id="'.$rid.'">
                    <img
                    src="'.$imgSrcLarge.'" '.$WidthAttribute.' '.$HeightAttribute.'
                    alt="'.esc_attr($altAttr).'"
                    aria-describedby="'.$ariaDescribedby.'"                 
                    loading="lazy"
                    class="'.$rThumb.'" 
                    itemprop="contentUrl" '.$imgStyle.' >
            </div>';
        }elseif(cg_is_alternative_file_type_video($ImgType)){
            $centerImage = '<div id="'.$imgId.'" class="cgl_on_load cg-center-image cg_translateX cg_transition cg-center-image-alternative-file-type-video" data-cg-real-id="'.$rid.'" >
                    <div class="cg-center-image-video">
                      <video
                        class="cg-center-image-video-entry-content"
                        controls 
                        preload="metadata"   
                        aria-describedby="'.$ariaDescribedby.'"'.$mediaDescriptionAttribute.'
                        width="'.$fullData['Width'].'"
                        height="'.$fullData['Height'].'"
                      >
                        <source src="'.$fullData['guid'].'" type="video/'.strtolower($ImgType).'">
                        <!-- Fallback text -->
                        Sorry, your browser doesn’t support embedded videos.
                      </video>
                    </div>
            </div>';
        }elseif(cg_is_alternative_file_type_audio($ImgType)){
            $centerImage = '<div id="'.$imgId.'" class="cg-center-image cg_translateX cg-center-image-alternative-file-type cg-center-image-'.strtolower($ImgType).' cg-center-image-alternative-file-type-audio cg_transition" data-cg-real-id="'.$rid.'">
                    <div class="cg-center-image-audio">
                        <audio onplay="cgJsClass.gallery.views.singleViewFunctions.audioOnplay(event)" controls=""    
                    aria-describedby="'.$ariaDescribedby.'"'.$mediaDescriptionAttribute.' >
                          <source src="'.$fullData['guid'].'" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                    <div class="cg-center-image-name">'.$fullData['NamePic'].'</div>
            </div>';
        }elseif(cg_is_alternative_file_type_file($ImgType)){
            $centerImage = '<div id="'.$imgId.'" class="cg-center-image cg_translateX cg-center-image-alternative-file-type cg-center-image-'.strtolower($ImgType).' cg_transition" data-cg-real-id="'.$rid.'" 
                    aria-describedby="'.$ariaDescribedby.'">
                    <div class="cg-center-image-name">'.$fullData['NamePic'].'</div>
            </div>';
        }elseif($ImgType === 'con' && $conEntryPreviewContent !== ''){
            $centerImage = '<div id="'.$imgId.'" class="cg-center-image cg-center-image-con-entry" data-cg-real-id="'.$rid.'"
                    aria-describedby="'.$ariaDescribedby.'">
                    <div class="cg-center-image-con-entry-content">'.$conEntryPreviewContent.'</div>
            </div>';
        }elseif(cgl_check_if_embeded($ImgType)){
            if($ImgType == 'ytb'){
                $centerImage = '<div id="'.$imgId.'" class="cg-center-image cg_translateX cg_transition cg_background_unset cg-center-image-social-entry cg-center-image-ytb-entry" data-cg-real-id="'.$rid.'" 
                    aria-describedby="'.$ariaDescribedby.'">
                    <iframe class="cg-center-image-ytb-entry-content"  src="'.$fullData['guid'].'" ></iframe>
            </div>';
            }
            if($ImgType == 'inst'){
                $centerImage = '<div id="'.$imgId.'" class="cg-center-image cg_translateX cg_transition cg_background_unset cg-center-image-social-entry cg-center-image-inst-entry" data-cg-real-id="'.$rid.'" 
                    aria-describedby="'.$ariaDescribedby.'">
                    <iframe class="cg-center-image-inst-entry-content"  src="'.$fullData['guid'].'" ></iframe>
            </div>';
            }
            if($ImgType == 'twt'){
                $blockquote = cg_get_blockquote_post_content_php_rendering($fullData['post_content']);
                $centerImage = '<div id="'.$imgId.'" class="cg-center-image cg_translateX cg_transition cg_background_unset cg-center-image-twt-entry cg-center-image-social-entry" data-cg-real-id="'.$rid.'" 
                    aria-describedby="'.$ariaDescribedby.'">
                    <div class="cg-center-image-twt-entry-content" >'.$blockquote.'</div>
            </div>';
            }
            if($ImgType == 'tkt'){
                $blockquote = cg_get_blockquote_post_content_php_rendering($fullData['post_content']);
                $centerImage = '<div id="'.$imgId.'" class="cg-center-image cg_translateX cg_transition cg_background_unset cg-center-image-tkt-entry cg-center-image-social-entry" data-cg-real-id="'.$rid.'" 
                    aria-describedby="'.$ariaDescribedby.'">
                    <div class="cg-center-image-tkt-entry-content" >'.$blockquote.'</div>
            </div>';
            }
        }

        $sliderButtons = '';
        $cgSliderViewClass = '';

        if($sliderView){
            $sliderButtons = '<div id="cgCenterImageDivButtons'.$gidJs.$ord.'" class="cg-center-image-div-buttons cgHundertPercentWidth">
                <div class="cg-center-image-div-buttons-first-controls">
                     <div class="cg-center-image-div-buttons-first-controls-arrows">
                        <div class="cg-center-arrow cg-center-arrow-left cg_hide" id="cgCenterArrowLeft'.$gidJs.$ord.'" data-cg-tooltip="Previous entry" data-cg-actual-realid="116"></div>
                        <div class="cg-center-arrow cg-center-arrow-right" id="cgCenterArrowRight'.$gidJs.$ord.'" data-cg-tooltip="Next entry" data-cg-actual-realid="116"></div>
                    </div>
                </div>
        </div>';
            $cgSliderViewClass = 'cg-slider-view';
        }

        $posInfoCgHide = 'cg_hide';
        $posInfo = '';
        if($sliderView){
            $posInfoCgHide = '';
            $posInfo = '1 '.$languageNames['gallery']['of'].' '.$imagesFullDataLength;
        }

        $saleContainerClass = 'cg-center-sale cg_hide';
        $salePriceText = '';
        $salePriceType = '';
        $saleTitleText = '';
        $saleDescriptionText = '';
        $buyNowText = (!empty($languageNames['ecommerce']['BuyNow'])) ? $languageNames['ecommerce']['BuyNow'] : 'Buy now';
        $addToCartText = (!empty($languageNames['ecommerce']['AddToShoppingCart'])) ? $languageNames['ecommerce']['AddToShoppingCart'] : 'Add to cart';

        if($shortcode_name === 'cg_gallery_ecommerce'){
            $saleData = cg1l_frontend_get_ecommerce_sale_data($fullData, $ecommerceFilesData, $ecommerceOptions, $currenciesArray, $languageNames);

            if(!empty($saleData['isActive'])){
                $saleContainerClass = 'cg-center-sale';
                $salePriceText = $saleData['salePriceText'];
                $salePriceType = $saleData['salePriceType'];
                $saleTitleText = $saleData['saleTitleText'];
                $saleDescriptionText = $saleData['saleDescriptionText'];
            }
        }

        $saleDiv = '<div id="'.esc_attr($saleId).'" class="'.esc_attr(trim($saleContainerClass.' cg-center-image-action-card cg-center-image-action-card-sale')).'" data-cg-gid="'.esc_attr($gidJs).'">
                            <div class="cg-center-sale-price-container">
                                <div class="cg-center-sale-price">
                                    <div class="cg-center-sale-price-value">'.esc_html($salePriceText).'</div>
                                    <div class="cg-center-sale-price-type">'.esc_html($salePriceType).'</div>
                                </div>
                            </div>
                            <div class="cg-center-sale-buy-now-and-add-to-basket-container">
                                <div class="cg-center-sale-buy-now-and-add-to-basket-container-sub">
                                    <div class="cg-center-sale-buy-now" data-cg-gid="'.esc_attr($gidJs).'" data-cg-real-id="'.esc_attr($rid).'">'.esc_html($buyNowText).'</div>
                                    <div class="cg-center-sale-add-to-basket" data-cg-gid="'.esc_attr($gidJs).'" data-cg-real-id="'.esc_attr($rid).'">'.esc_html($addToCartText).'</div>
                                </div>
                            </div>
                            <div class="cg-center-sale-product-info cg_hide">
                                <div class="cg-center-sale-product-info-title">'.esc_html($saleTitleText).'</div>
                                <div class="cg-center-sale-product-info-description">'.esc_html($saleDescriptionText).'</div>
                            </div>
                        </div>';

        $saleCard = '';
        if($shortcode_name === 'cg_gallery_ecommerce' && $saleContainerClass === 'cg-center-sale'){
            $saleCard = $saleDiv;
        }

        if($ratingDiv || $entryActionButtons || $saleCard){
            $ratingCard = '';
            if($ratingDiv){
                $ratingCard = '<div id="'.esc_attr($leftBtnsId).'" class="cg-center-image-div-buttons-left cg-center-image-action-card cg-center-image-action-card-rating">
                                    '.$ratingDiv.'
                                </div>';
            }

            $actionCard = '';
            if($entryActionButtons){
                $actionCard = '<div id="cgCenterImageDivButtons'.$gidJs.$ord.'" class="cg-center-image-div-buttons cg-center-image-action-card cg-center-image-action-card-actions">
                                    <div class="cg-center-image-div-buttons-second-controls">
                                        '.$entryActionButtons.'
                                    </div>
                                </div>';
            }

            $ratingAndButtonsDivClasses = 'cg-center-image-rating-and-buttons-div';
            if($saleCard && $ratingCard && $actionCard){
                $ratingAndButtonsDivClasses .= ' cg-center-image-rating-and-buttons-div-has-sale-card';
            }

            $ratingAndButtonsDiv = '<div id="'.esc_attr($rateButtonsWrapId).'" class="'.esc_attr($ratingAndButtonsDivClasses).'">
                            '.$ratingCard.'
                            '.$saleCard.'
                            '.$actionCard.'
                        </div>';
        }

        $ownerBadge = '';
        $ownerWpUserId = 0;
        $ownerNicknameRaw = '';
        $ownerProfileImageUrl = '';
        $showOwnerBadgeProfileImage = false;
        $showOwnerBadgeNickname = false;

        if(!empty($WpUserIdsData['mainDataWpUserIds'][$rid])){
            $ownerWpUserId = absint($WpUserIdsData['mainDataWpUserIds'][$rid]);
        }elseif(!empty($fullData['WpUserId'])){
            $ownerWpUserId = absint($fullData['WpUserId']);
        }

        if(!empty($ownerWpUserId) && !empty($WpUserIdsData['nicknamesArray'][$ownerWpUserId])){
            $ownerNicknameRaw = (string)$WpUserIdsData['nicknamesArray'][$ownerWpUserId];
        }

        $ownerProfileImageUrl = cg1l_frontend_get_preferred_wp_user_avatar_url($ownerWpUserId, $WpUserIdsData);
        $showOwnerBadgeProfileImage = (!empty($options['pro']['ShowProfileImage']) && !empty($ownerProfileImageUrl));
        $showOwnerBadgeNickname = (!empty($options['pro']['ShowNickname']) && $ownerNicknameRaw !== '');

        if($showOwnerBadgeProfileImage || $showOwnerBadgeNickname){
            $ownerBadgeClasses = [
                'cg-gallery-owner-badge',
                'cg-gallery-owner-badge-entry',
                'cg-gallery-owner-badge-single',
            ];

            if($ownerNicknameRaw !== ''){
                $ownerBadgeClasses[] = 'cg-gallery-owner-badge-has-nickname';
            }

            if($showOwnerBadgeProfileImage){
                $ownerBadgeClasses[] = 'cg-gallery-owner-badge-show-profile-image';
            }

            if($showOwnerBadgeNickname){
                $ownerBadgeClasses[] = 'cg-gallery-owner-badge-show-nickname';
            }

            if(!$showOwnerBadgeProfileImage){
                $ownerBadgeClasses[] = 'cg-gallery-owner-badge-static';
            }

            $ownerBadgeAriaLabel = 'Entry owner';
            if($showOwnerBadgeProfileImage && $ownerNicknameRaw !== ''){
                $ownerBadgeAriaLabel = 'Show profile image of '.$ownerNicknameRaw;
            }elseif($showOwnerBadgeProfileImage){
                $ownerBadgeAriaLabel = 'Show profile image';
            }elseif($showOwnerBadgeNickname){
                $ownerBadgeAriaLabel = $ownerNicknameRaw;
            }

            $ownerBadgeTitleAttr = ($ownerNicknameRaw !== '') ? ' title="'.esc_attr($ownerNicknameRaw).'"' : '';
            $ownerBadgeImageStyle = ($showOwnerBadgeProfileImage) ? ' style="'.esc_attr('background-image: url('.$ownerProfileImageUrl.');').'"' : '';
            $ownerBadgeText = ($showOwnerBadgeNickname) ? esc_html($ownerNicknameRaw) : '';

            $ownerBadge = '<button type="button" class="'.esc_attr(implode(' ', array_unique($ownerBadgeClasses))).'" data-cg-real-id="'.esc_attr($rid).'" aria-hidden="false" data-cg-wp-user-id="'.esc_attr($ownerWpUserId).'" aria-label="'.esc_attr($ownerBadgeAriaLabel).'"'.$ownerBadgeTitleAttr.'><span class="cg-gallery-owner-badge-image'.($showOwnerBadgeProfileImage ? '' : ' cg_hide').'"'.$ownerBadgeImageStyle.'></span><span class="cg-gallery-owner-badge-text'.($showOwnerBadgeNickname ? '' : ' cg_hide').'">'.$ownerBadgeText.'</span></button>';
        }

        $centerColorClass = (!empty($options['visual']['FeControlsStyle']) && $options['visual']['FeControlsStyle'] === 'black') ? 'cg_center_black' : 'cg_center_white';

        return '<article id="'.$centerDivId.'" class="cgCenterDiv cg_entry cgCenterDivForBlogView cg_border_top_unset_important cg_fade_in '.esc_attr($centerColorClass).' cgCenterDivPhpLoad" data-cg-gid="'.$gidJs.'" style="width: 100%; min-height: unset; height: unset; display: block;" data-cg-real-id="'.$rid.'" data-cg-gid-with-order="'.esc_attr("{$gidJs}{$ord}").'" data-cg-order="'.esc_attr($orderAttr).'" data-cg-gid-for-center-div-elements="'.esc_attr("{$gidJs}{$ord}").'" data-cg-cat-id="'.esc_attr($cat).'"
         itemscope itemtype="https://schema.org/'.$itemTypeObject.'Object" >
    '.$meta.' 
    '.$metaComment.' 
    <div id="'.esc_attr($centerDivChildId).'" class="cgCenterDivChild" data-cg-gid="'.$gidJs.'">
        <div id="'.esc_attr($centerOrientationId).'" class="cg-center-orientation cg_hide"></div>
        <div id="'.esc_attr($centerHelperId).'" class="cg-center-div-helper '.$cgSliderViewClass.'" data-cg-gid="'.$gidJs.'">
            <div id="'.esc_attr($sliderInfoId).'" class="cg-center-slider-position-info '.$posInfoCgHide.'" >'.$posInfo.'</div>
            '.$sliderButtons.'
                <div id="'.esc_attr($helperHelperId).'" class="cg-center-div-helper-helper" data-cg-gid="'.$gidJs.'">
                    <div id="'.esc_attr($helper3Id).'" class="cg-center-div-helper-helper-helper" data-cg-gid="'.$gidJs.'">
                    <div id="'.esc_attr($imgDivId).'" class="cg-center-image-div cgHundertPercentWidth">
                        '.$divSocialShareParent.'
                        <div id="'.esc_attr($imgParentId).'" class="cg-center-image-parent cg-one-image-only" style="min-height: unset; max-height: unset; height: unset; overflow: visible;">
                            '.$additionalFilesButtons.'
                            '.$centerImage.'
                            '.$ownerBadge.'
                        </div>
                        '.$exifInfoContent.'
                        '.$ratingAndButtonsDiv.'
                    </div>
                </div>

                <div id="'.esc_attr($infoDivId).'" class="cg-center-info-div cgHundertPercentWidth">
                    '.$infoSection.'
                       '.$commentsDiv.'
                    
                </div>

            </div>
        </div>
    </div>
</article>';
    }
}
