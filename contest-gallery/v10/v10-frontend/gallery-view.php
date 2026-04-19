<?php

	include_once(__DIR__.'/url-query-helpers.php');

    $cgGalleryForceEntryGuidLinks = !empty($cgGalleryForceEntryGuidLinks);
    $cgGalleryForceEntryGuidLinksNewTab = !empty($cgGalleryForceEntryGuidLinksNewTab);
    $cgGalleryReadOnlyInfoActions = !empty($cgGalleryReadOnlyInfoActions);
    $cgGalleryRatingCommentsGroupClass = (!empty($cgGalleryRatingCommentsGroupClass)) ? trim((string)$cgGalleryRatingCommentsGroupClass) : '';
    $cgGalleryArticleExtraClasses = (!empty($cgGalleryArticleExtraClasses) && is_array($cgGalleryArticleExtraClasses)) ? $cgGalleryArticleExtraClasses : [];
    $cgGalleryRenderSkeleton = !isset($cgGalleryRenderSkeleton) || !empty($cgGalleryRenderSkeleton);
    $cglOriginPageId = (!empty($cglOriginPageId)) ? absint($cglOriginPageId) : 0;
    $isMultiGalleryContext = !empty($isMultiGalleryContext);
    $isFromGalleriesSelect = !empty($isFromGalleriesSelect);
    $is_from_single_view_for_cg_galleries = !empty($is_from_single_view_for_cg_galleries);
    $isGalleriesMainPage = !empty($isGalleriesMainPage);

    $cg_render_gallery_media_link = function($content, $href, $targetAttr = '', $linkClass = ''){
        if(empty($href)){
            return $content;
        }

        $linkClass = trim($linkClass);
        $classAttr = '';
        if($linkClass !== ''){
            $classAttr = ' class="'.esc_attr($linkClass).'"';
        }

        return '<a href="'.esc_url($href).'"'.$classAttr.$targetAttr.'>'.$content.'</a>';
    };

    $cg_render_gallery_embed_overlay = function($href, $targetAttr, $linkClass, $useOverlayLink, $isOnlyGalleryView, $showDefaultOverlay){
        if($useOverlayLink && !empty($href)){
            $overlayClasses = ['cg_append_overlay'];
            if(!empty($linkClass)){
                $overlayClasses[] = $linkClass;
            }
            if(strpos($targetAttr,'target=') !== false){
                $overlayClasses[] = 'cg_show_href_target_blank';
            }
            return '<a href="'.esc_url($href).'" class="'.esc_attr(implode(' ',$overlayClasses)).'"'.$targetAttr.'></a>';
        }

        if($isOnlyGalleryView || $showDefaultOverlay){
            return '<div class="cg_append_overlay"></div>';
        }

        return '';
    };

    $cg_render_gallery_embed_hidden_link = function($href, $targetAttr = '', $linkClass = ''){
        if(empty($href)){
            return '';
        }

        $hiddenLinkClasses = ['cg_append_seo_conform_hidden_link'];
        if(!empty($linkClass)){
            $hiddenLinkClasses[] = $linkClass;
        }

        return '<a href="'.esc_url($href).'" class="'.esc_attr(implode(' ',$hiddenLinkClasses)).'"'.$targetAttr.'>Details</a>';
    };

    $cg_wrap_gallery_media_shell = function($content){
        return '<div class="cg_masonry_media_shell">'.$content.'</div>';
    };

    $cg_resolve_cgalleries_target_gallery_id = function($fullData){
        if(empty($fullData) || !is_array($fullData)){
            return 0;
        }

        if(!empty($fullData['isCGalleriesNoGalleryGid'])){
            return absint($fullData['isCGalleriesNoGalleryGid']);
        }

        if(!empty($fullData['GalleryIdToCheck'])){
            return absint($fullData['GalleryIdToCheck']);
        }

        if(!empty($fullData['gidToShow'])){
            return absint($fullData['gidToShow']);
        }

        return 0;
    };
    $cg_normalize_forward_to_url = function($urlValue){
        $urlValue = trim(wp_strip_all_tags(html_entity_decode((string)$urlValue, ENT_QUOTES, 'UTF-8')));

        if($urlValue === ''){
            return '';
        }

        if(!preg_match('/^https?:\/\//i', $urlValue)){
            $urlValue = 'https://'.ltrim($urlValue, '/');
        }

        return esc_url_raw($urlValue);
    };
    $cg_resolve_forward_to_url_href = function($fullData, $jsonInfoData, $options) use ($cg_normalize_forward_to_url){
        $forwardFieldId = (!empty($options['visual']['ForwardToUrl'])) ? absint($options['visual']['ForwardToUrl']) : 0;
        $entryId = (!empty($fullData['id'])) ? absint($fullData['id']) : 0;
        $fieldContent = '';

        if(!$forwardFieldId || !$entryId){
            return '';
        }

        if(!empty($jsonInfoData[$entryId][$forwardFieldId]['field-content'])){
            $fieldContent = $jsonInfoData[$entryId][$forwardFieldId]['field-content'];
        }elseif(!empty($jsonInfoData[(string)$entryId][$forwardFieldId]['field-content'])){
            $fieldContent = $jsonInfoData[(string)$entryId][$forwardFieldId]['field-content'];
        }

        return $cg_normalize_forward_to_url($fieldContent);
    };

    $isEmbeddedCGalleriesHostPage = (!empty($isCGalleries) && empty($isGalleriesMainPage));
    $isMainCGalleriesLandingPage = (!empty($isCGalleries) && !empty($isGalleriesMainPage));

    $order = 0;
    foreach ($imagesFullDataToProcess as $index => $fullData) {
        $id = $fullData['id'];
        $imgSrcLarge = $fullData['large'];
        $ImgType = strtolower($fullData['ImgType']);
        $isOnlyGalleryView = !empty($options['general']['OnlyGalleryView']) && intval($options['general']['OnlyGalleryView']) === 1;
        $isFullSizeImageOutGallery = !empty($options['general']['FullSizeImageOutGallery']) && intval($options['general']['FullSizeImageOutGallery']) === 1;
        $isFullSizeImageOutGalleryNewTab = !empty($options['general']['FullSizeImageOutGalleryNewTab']) && intval($options['general']['FullSizeImageOutGalleryNewTab']) === 1;
        if($isCGalleries){
            $isOnlyGalleryView = false;
            $isFullSizeImageOutGallery = false;
            $isFullSizeImageOutGalleryNewTab = false;
        }
        $commentsWrapperClass = ($isOnlyGalleryView || $isFullSizeImageOutGallery) ? 'cg_pointer_events_none' : '';
        if($cgGalleryReadOnlyInfoActions){
            $commentsWrapperClass = trim($commentsWrapperClass.' cg_pointer_events_none');
        }

        $additionalFilesCount = 0;
        if(!empty($queryDataArray[$id]['MultipleFiles'])){
            $additionalFilesCount = count($queryDataArray[$id]['MultipleFiles'])-1;
        }

        $cg_multiple_files = '';
        if($additionalFilesCount){
            $cg_multiple_files = '<div class="cg_multiple_files_prev"></div>'."\r\n";
            $cg_multiple_files .= '<div class="cg_multiple_files">+'.$additionalFilesCount.'</div>'."\r\n";
        }

        $array = cg1l_frontend_get_info_data_gallery($fullData,$formUploadFullData,$categoriesFullData,$languageNames,$options,$jsonInfoData);
        $mainTitle = $array['mainTitle'];
        $subTitle = $array['subTitle'];
        $thirdTitle = $array['thirdTitle'];
        $titleAttr = $array['titleAttr'];
        $Category = $array['Category'];
        $altAttr = $array['altAttr'];
        $mediaDescription = $array['mediaDescription'];
        $watermarkMarkup = $array['watermarkMarkup'];
        $mediaDescriptionAttribute = ($mediaDescription !== '') ? ' aria-label="'.esc_attr($mediaDescription).'"' : '';
        $conEntryPreviewContent = (!empty($array['conEntryPreviewContent'])) ? $array['conEntryPreviewContent'] : '';
        $conEntryPreviewMarkup = '';
        if($ImgType === 'con'){
            $conEntryPreviewMarkup = cg1l_frontend_get_con_entry_preview_markup($conEntryPreviewContent,'gallery');
        }

        $attrs = cg1l_get_image_attributes($fullData);

        $WidthAttribute  = $attrs['WidthAttribute'];
        $HeightAttribute = $attrs['HeightAttribute'];
        $rThumb          = $attrs['rThumb'];
        $imgStyle        = $attrs['imgStyle'];

        // later for multiple stars voting
        //<meta itemprop="ratingValue" content="4.8">
        //cg_gallery_info_content_sub
     //   var_dump('$galeryIDuserForJs');
     //   var_dump($galeryIDuserForJs);
        $hasNoGalleryEntriesText = !empty($fullData['isCGalleriesNoGalleryEntriesText']);
        $contestOver = !empty($fullData['isContestOver']);
        if($contestOver || $hasNoGalleryEntriesText){
            $ratingComments = '';
        }else{
            if($isCGalleries){
                $ratingComments = '';
            }else{
                $ratingComments = cg1l_create_rating_comments_gallery($galeryIDuserForJs,$fullData,$options,$shortcode_name,$jsonCommentsData,$countUserVotes,$votedUserPids,$isCGalleries,$commentsWrapperClass,$cgGalleryRatingCommentsGroupClass);
            }
        }

        $figCaption = '';
        $deleteUserImage = '';
        $isUserGalleryDeleteButton = ($shortcode_name === 'cg_gallery_user');

        if($isUserGalleryDeleteButton){
            $deleteTooltipText = ($additionalFilesCount > 0) ? $language_DeleteImages : $language_DeleteImage;
            $deleteUserImage = '<div class="cg_delete_user_image_container" data-cg-tooltip="'.esc_attr($deleteTooltipText).'"><div class="cg_delete_user_image" data-cg-gid="'.esc_attr($galeryIDuserForJs).'" data-cg-real-id="'.(int)$id.'"></div></div>';
        }

        if(!empty($subTitle) || !empty($mainTitle) || !empty($thirdTitle)){
            $figCaptionClass = 'cg_gallery_info_title cg_gallery_info_main_title';
            if($isUserGalleryDeleteButton){
                $figCaptionClass .= ' cg_is_user_gallery';
            }
            $figCaption = '
                        <figcaption class="'.$figCaptionClass.'">
                                '.$subTitle.'
                                '.$mainTitle.'
                                '.$thirdTitle.'
                                '.$deleteUserImage.'
                        </figcaption>
                    ';
        }elseif($deleteUserImage){
            $hideTillHover = (!empty($options['general']['ShowAlways']) && intval($options['general']['ShowAlways']) === 1) ? '' : ' cg_hide_till_hover';
            $figCaption = '
                        <figcaption class="cg_gallery_info_title cg_gallery_info_main_title cg_gallery_info_title_no_title cg_is_user_gallery'.$hideTillHover.'">
                                <div class="cg_gallery_info_content"></div>
                                '.$deleteUserImage.'
                        </figcaption>
                    ';
        }

	        $hasMainTitleContent = trim(wp_strip_all_tags($mainTitle)) !== '';
	        $hasSubTitleContent = trim(wp_strip_all_tags($subTitle)) !== '';
	        $hasThirdTitleContent = trim(wp_strip_all_tags($thirdTitle)) !== '';
	        $hasCaptionContent = $hasMainTitleContent || $hasSubTitleContent || $hasThirdTitleContent || !empty($deleteUserImage);
	        $cgGidToShow = (!empty($isCGalleries)) ? $cg_resolve_cgalleries_target_gallery_id($fullData) : 0;
	        $hasExplicitGalleriesOriginContext = (!empty($cglHasExplicitFromGalleriesPage) || (!empty($backToGalleriesFromPageNumber) && intval($backToGalleriesFromPageNumber) > 1));
	        $isMultiGalleryEntryContext = (!empty($isFromGalleriesSelect) || !empty($is_from_single_view_for_cg_galleries) || !empty($isMultiGalleryContext) || !empty($hasExplicitGalleriesOriginContext));
	        $entryContextFallbackGalleryId = (!empty($realGid)) ? absint($realGid) : 0;
	        if(empty($entryContextFallbackGalleryId) && !empty($galeryID)){
	            $entryContextFallbackGalleryId = absint($galeryID);
	        }
	        $entryContextGalleryId = 0;
	        if(!empty($isCGalleries) && $cgGidToShow > 0){
	            $entryContextGalleryId = $cgGidToShow;
	        }elseif(!empty($isMultiGalleryEntryContext)){
	            $entryContextGalleryId = (!empty($cglMultiContextGalleryId)) ? absint($cglMultiContextGalleryId) : $entryContextFallbackGalleryId;
	        }

	        $entryGuid = '';
	        if(!empty($fullData['entryGuid'])){
	            $entryGuid = $fullData['entryGuid'];
	        }

	        if(!empty($entryGuid)){
	            $entryGuid = cg1l_append_frontend_passthrough_query_args($entryGuid,[
	                'is_ecommerce_test' => !empty($isEcommerceTest)
	            ]);
	            if($isMainCGalleriesLandingPage){
	                $entryGuid = cgl_build_galleries_landing_gallery_url($entryGuid,$currentPageNumber);
	            }else{
	                $entryGuid = cgl_build_entry_context_url($entryGuid,[
	                    'is_multi_gallery_context' => ((!empty($isCGalleries) || !empty($isMultiGalleryEntryContext)) && !empty($entryContextGalleryId)),
	                    'gallery_id' => $entryContextGalleryId,
	                    'from_gallery_page_number' => $currentPageNumber,
	                    'from_galleries_page_number' => $backToGalleriesFromPageNumber,
	                    'origin_page_id' => $cglOriginPageId,
	                ]);
	            }
	        }

        $embeddedCGalleriesHostHref = '';
        if($isEmbeddedCGalleriesHostPage && $cgGidToShow > 0 && !empty($currentUrl)){
            $embeddedCGalleriesHostHref = cgl_build_galleries_return_url($currentUrl,$currentPageNumber,$cgGidToShow);
            $embeddedCGalleriesHostHref = cg1l_append_frontend_passthrough_query_args($embeddedCGalleriesHostHref,[
                'is_ecommerce_test' => !empty($isEcommerceTest)
            ]);
        }

        $originalSourceHref = '';
        if(!empty($fullData['PdfPreview']) && !empty($fullData['PdfOriginal'])){
            $originalSourceHref = $fullData['PdfOriginal'];
        }elseif(!empty($fullData['full'])){
            $originalSourceHref = $fullData['full'];
        }elseif(!empty($fullData['guid'])){
            $originalSourceHref = $fullData['guid'];
        }

        $forwardToUrlHref = '';
        $isForwardToUrl = false;
        $isForwardToUrlNewTab = false;
        if(empty($isCGalleries) && empty($isGalleriesMainPage)){
            $forwardToUrlHref = $cg_resolve_forward_to_url_href($fullData, $jsonInfoData, $options);
            $isForwardToUrl = ($forwardToUrlHref !== '');
            $isForwardToUrlNewTab = $isForwardToUrl && !empty($options['visual']['ForwardToUrlNewTab']) && intval($options['visual']['ForwardToUrlNewTab']) === 1;
        }

        $useOriginalSourceTarget = (!$cgGalleryForceEntryGuidLinks && !$isForwardToUrl && !$isOnlyGalleryView && $isFullSizeImageOutGallery && !empty($originalSourceHref));
        $galleryEntryTargetHref = (!empty($embeddedCGalleriesHostHref)) ? $embeddedCGalleriesHostHref : $entryGuid;
        $galleryMediaHref = '';
        if($isForwardToUrl){
            $galleryMediaHref = $forwardToUrlHref;
        }elseif($cgGalleryForceEntryGuidLinks && !empty($galleryEntryTargetHref)){
            $galleryMediaHref = $galleryEntryTargetHref;
        }elseif(!$isOnlyGalleryView){
            if($useOriginalSourceTarget){
                $galleryMediaHref = $originalSourceHref;
            }else{
                $galleryMediaHref = $galleryEntryTargetHref;
            }
        }

        $galleryMediaTargetAttr = '';
        if($isForwardToUrl && $isForwardToUrlNewTab){
            $galleryMediaTargetAttr = ' target="_blank" rel="noopener noreferrer"';
        }elseif($cgGalleryForceEntryGuidLinks && $cgGalleryForceEntryGuidLinksNewTab){
            $galleryMediaTargetAttr = ' target="_blank" rel="noopener noreferrer"';
        }elseif($useOriginalSourceTarget && $isFullSizeImageOutGalleryNewTab){
            $galleryMediaTargetAttr = ' target="_blank"';
        }

        $galleryMediaLinkClass = '';
        if($isForwardToUrl){
            $galleryMediaLinkClass = 'cg_forward_to_url';
        }elseif($cgGalleryForceEntryGuidLinks){
            $galleryMediaLinkClass = 'is_forward_to_wp_page_entry';
        }elseif($useOriginalSourceTarget){
            $galleryMediaLinkClass = 'cg_show_href_target_blank';
        }

        $cg_date = '';
        $showDateMode = (!empty($options['visual']['ShowDate'])) ? absint($options['visual']['ShowDate']) : 0;
        $showDateInGalleryView = in_array($showDateMode,array(1,2),true);
        if(!$showDateInGalleryView && !empty($showDateMode) && !in_array($showDateMode,array(1,2,3),true)){
            $showDateInGalleryView = true;
        }

        if(!empty($fullData['Timestamp']) && $showDateInGalleryView && empty($isCGalleries)){
            $dateTimeInvisibleSeo = cg_get_time_based_on_wp_timezone_conf($fullData['Timestamp'],'Y-m-d\TH:i:sP');
            $dateTimeVisibleSeo = cg_get_time_based_on_wp_timezone_conf($fullData['Timestamp'],'j. F Y');
            $cg_date = '<div class="cg_date cg_opacity_0" data-cg-timestamp="'.absint($fullData['Timestamp']).'"><time datetime="'.$dateTimeInvisibleSeo.'">'.$dateTimeVisibleSeo.'</time></div>';
        }

        $cg_sale_price_markup = '';
        if($shortcode_name === 'cg_gallery_ecommerce'){
            $saleData = cg1l_frontend_get_ecommerce_sale_data($fullData, $ecommerceFilesData, $ecommerceOptions, $currenciesArray, $languageNames);
            $cg_sale_price_markup = cg1l_frontend_get_ecommerce_gallery_price_markup($saleData);
        }

        $cg_gallery_info = '';
        $hasGalleryInfoContent = !empty($cg_date) || !empty($cg_sale_price_markup) || !empty($ratingComments) || $hasCaptionContent;
        if($hasGalleryInfoContent){
            $cg_gallery_info = '<div class="cg_gallery_info">
                '.$cg_sale_price_markup.'
                '.$cg_date.'
                '.$ratingComments.'
                '.$figCaption.'
            </div>';
        }

        $entry = '';
        $figure = '';
        $meta = cg1l_frontend_get_meta_html($ImgType,$fullData);
        $metaComment = cg1l_frontend_get_meta_comment_count($fullData);
        $itemTypeObject = cg1l_get_itemtype_object($ImgType);

        if($hasNoGalleryEntriesText){
            $contestOverContent = '<div class="cg_no_entries_text">
                        '.contest_gal1ery_convert_for_html_output_without_nl2br($fullData['isCGalleriesNoGalleryEntriesText']).'
                    </div>';
            $figure = '<figure class="cg_figure" itemscope itemtype="https://schema.org/CreativeWork">
                '.$cg_wrap_gallery_media_shell($cg_render_gallery_media_link($contestOverContent,$galleryMediaHref,$galleryMediaTargetAttr,$galleryMediaLinkClass)).'
                '.$cg_gallery_info.'
              </figure>';
        }elseif(cg_is_is_image($ImgType)){
            $imageContent = '<img
                    src="'.$imgSrcLarge.'" '.$WidthAttribute.' '.$HeightAttribute.'
                    alt="'.esc_attr($altAttr).'"
                    loading="lazy"
                    class="'.$rThumb.'" 
                    itemprop="contentUrl" '.$imgStyle.' >';
            $figure = '<figure class="cg_figure" itemscope itemtype="https://schema.org/'.$itemTypeObject.'Object">
                '.$meta.'
                '.$metaComment.'
                '.$cg_wrap_gallery_media_shell($cg_render_gallery_media_link($imageContent,$galleryMediaHref,$galleryMediaTargetAttr,$galleryMediaLinkClass)).'
                '.$watermarkMarkup.'
                '.$cg_gallery_info.'
              </figure>';
        }elseif(cg_is_alternative_file_type_video($ImgType)){
            $controls = '';
            if($isOnlyGalleryView){
                $controls = 'controls';
            }
            $videoContent = '<video id="cg_append'.$id.'" 
                    class="cg_append cg_append_alternative_file_type_video"
                    '.$controls.'
                    preload="metadata"
                    width="'.$fullData['Width'].'"
                    height="'.$fullData['Height'].'"
                    '.$mediaDescriptionAttribute.'
                  >
                    <source src="'.$fullData['guid'].'" type="video/'.strtolower($ImgType).'">
                    <!-- Fallback text -->
                    Sorry, your browser doesn’t support embedded videos.
                  </video>';
            $figure = '<figure class="cg_figure" itemscope itemtype="https://schema.org/'.$itemTypeObject.'Object">
                '.$meta.'
                '.$metaComment.'
                '.$cg_wrap_gallery_media_shell($cg_render_gallery_media_link($videoContent,$galleryMediaHref,$galleryMediaTargetAttr,$galleryMediaLinkClass)).'
                '.$cg_gallery_info.'
              </figure>';
        }elseif(cg_is_alternative_file_type_file($ImgType)){
            $fileContent = '<div id="cg_append'.$id.'" 
                    class="cg_append cg_append_alternative_file_type cg_append_'.$ImgType.'"
                  >
                  </div>';
            $figure = '<figure class="cg_figure" itemscope itemtype="https://schema.org/'.$itemTypeObject.'Object">
                '.$meta.'
                '.$metaComment.'
                '.$cg_wrap_gallery_media_shell($cg_render_gallery_media_link($fileContent,$galleryMediaHref,$galleryMediaTargetAttr,$galleryMediaLinkClass)).'
                '.$cg_gallery_info.'
              </figure>';
        }elseif(cgl_check_if_embeded($ImgType)){
            $blockquote = '';
            $cg_append_social = '';
            $cg_append = '';
            $useGalleryMediaOverlayLink = ($isForwardToUrl || $cgGalleryForceEntryGuidLinks || $useOriginalSourceTarget);
            $showDefaultEmbedOverlay = (!$isOnlyGalleryView && !$useGalleryMediaOverlayLink && $options['visual']['ForwardToWpPageEntry']!=1);
            $cg_append_overlay = $cg_render_gallery_embed_overlay($galleryMediaHref,$galleryMediaTargetAttr,$galleryMediaLinkClass,$useGalleryMediaOverlayLink,$isOnlyGalleryView,$showDefaultEmbedOverlay);
            $embedHiddenLink = $cg_render_gallery_embed_hidden_link($galleryMediaHref,$galleryMediaTargetAttr,$galleryMediaLinkClass);
            $embedHrefAttribute = '';
            if(!empty($galleryMediaHref)){
                $embedHrefAttribute = ' data-cgl-href="'.esc_url($galleryMediaHref).'"';
            }

            if($ImgType=='tkt' || $ImgType=='twt'){
                if($ImgType=='tkt' && $options['pro']['ConsentTikTok']=='1'){
                    $cg_append = cg1l_get_consent_tkt($galeryIDuserForJs,$id,$fullData,$languageNames);
                } elseif($ImgType=='twt' && $options['pro']['ConsentTwitter']=='1'){
                    $cg_append = cg1l_get_consent_twt($galeryIDuserForJs,$id,$fullData,$languageNames);
                }else{
                    $blockquote = cg_get_blockquote_post_content_php_rendering($fullData['post_content']);
                    $cg_append = '<div class="cg_append_container">'.$cg_append_overlay.'<div id="cg_append'.$id.'" 
                    class="cg_append '.$cg_append_social.' cg_append_'.$ImgType.'"'.$embedHrefAttribute.'
                    >
                    '.$blockquote.'
                  </div></div>'.$embedHiddenLink;
                }
            }else if($ImgType=='ytb' || $ImgType=='inst'){
                if($ImgType=='ytb' && $options['pro']['ConsentYoutube']=='1'){
                    $cg_append = cg1l_get_consent_ytb($galeryIDuserForJs,$id,$fullData,$languageNames);
                }else if($ImgType=='inst' && $options['pro']['ConsentInstagram']=='1'){
                    $cg_append = cg1l_get_consent_inst($galeryIDuserForJs,$id,$fullData,$languageNames);
                }else{
                    $cg_append = '<div class="cg_append_container">'.$cg_append_overlay.'<iframe class="cg_append cg_append_social cg_append_'.$ImgType.'"  src="'.$fullData['guid'].'"  ></iframe></div>'.$embedHiddenLink;
                }
            }
            //$blockquote = '';
            $figure = '<figure class="cg_figure" itemscope itemtype="https://schema.org/'.$itemTypeObject.'Object">
                '.$meta.' 
                '.$metaComment.'
                '.$cg_wrap_gallery_media_shell($cg_append).'
                '.$cg_gallery_info.'
              </figure>';
        }elseif($ImgType=='con'){
            $cg_append = ($conEntryPreviewMarkup !== '') ? $cg_render_gallery_media_link($conEntryPreviewMarkup,$galleryMediaHref,$galleryMediaTargetAttr,$galleryMediaLinkClass) : '';
            $figure = '<figure class="cg_figure">
                '.$meta.' 
                '.$metaComment.'
                '.$cg_wrap_gallery_media_shell($cg_append).'
                '.$cg_gallery_info.'
              </figure>';
        }

        $articleClasses = array(
            'cg_show',
            'cg_grid_item',
            'cg-cat-'.$Category
        );

        if($hasSubTitleContent && $hasThirdTitleContent){
            $articleClasses[] = 'has_sub_and_third_title';
        }elseif($hasSubTitleContent || $hasThirdTitleContent){
            $articleClasses[] = 'has_sub_or_third_title';
        }

        if($ImgType === 'con' && empty($galleriesOptions['infoToShowIfCon'])){
            $articleClasses[] = 'cg_show_con_entry';
        }

        if($ImgType === 'con' && $conEntryPreviewMarkup !== ''){
            $articleClasses[] = 'cg_show_con_entry_has_preview';
        }

        if($hasGalleryInfoContent){
            if(!empty($options['general']['ShowAlways']) && intval($options['general']['ShowAlways']) === 3){
                $articleClasses[] = 'cg_show_info_bottom';
                if($ImgType === 'con' && empty($galleriesOptions['infoToShowIfCon'])){
                    $articleClasses[] = 'cg_show_info_bottom_con';
                }
            }
        }else{
            $articleClasses[] = 'cg_empty_info';
        }

        if(!empty($cgGalleryArticleExtraClasses)){
            foreach($cgGalleryArticleExtraClasses as $cgGalleryArticleExtraClass){
                if(!$cgGalleryArticleExtraClass){
                    continue;
                }
                $articleClasses[] = sanitize_html_class($cgGalleryArticleExtraClass);
            }
        }

        $cgGidToShowAttribute = '';
        if($isCGalleries && $cgGidToShow > 0){
            $cgGidToShowAttribute = " data-cg-gid-to-show='".esc_attr($cgGidToShow)."'";
        }

        echo "<article id='cg_show$id' class='".esc_attr(implode(' ',$articleClasses))."' data-cg-gid='$galeryIDuserForJs' data-cg-id='$id' style='margin-bottom: 15px;margin-right: 15px;' data-cg-cat-gid='$Category' data-cg-order='$order'".$cgGidToShowAttribute."  >";
        if($cgGalleryRenderSkeleton){
            echo "<div class='cg_skeleton'></div>";
        }
        echo $cg_multiple_files;
        echo $figure;
        echo "</article>";
        $order++;

    }
    //   echo '</div>';
//echo '</div>';
