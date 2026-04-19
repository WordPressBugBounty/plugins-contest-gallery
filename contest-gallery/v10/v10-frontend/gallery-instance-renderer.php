<?php

if(!function_exists('cg1l_render_gallery_instance_shell')){
	function cg1l_render_gallery_instance_shell($args){
		$d = array_merge([
			'gid_js' => '',
			'real_gid' => 0,
			'gallery_id_short' => '',
			'options' => [],
			'shortcode_name' => 'cg_gallery',
			'variables_gallery' => [],
			'variables_general' => [],
			'galleries_options' => [],
			'language_names' => [],
			'recent_main_data' => [],
			'recent_query_data' => [],
			'recent_urls_data' => [],
			'recent_stats_data' => [],
			'recent_comments_data' => [],
			'recent_info_data' => [],
			'wp_user_ids_data' => [],
			'data_slider' => [],
			'data_slider_sorted_pids' => [],
			'images_full_data_to_process' => [],
			'json_comments_data' => [],
			'count_user_votes' => [],
			'voted_user_pids' => [],
			'query_data_array' => [],
			'form_upload_full_data' => [],
			'categories_full_data' => [],
			'json_info_data' => [],
			'current_page_number' => 1,
			'back_to_galleries_from_page_number' => 1,
			'ecommerce_files_data' => [],
			'ecommerce_options' => [],
			'currencies_array' => [],
			'language_delete_images' => '',
			'language_delete_image' => '',
			'shortcode_color_style' => 'cg_fe_controls_style_white',
			'border_radius_class' => '',
			'cgl_heart' => '',
			'force_entry_guid_links' => false,
			'force_entry_guid_links_new_tab' => false,
			'read_only_info_actions' => false,
			'rating_comments_group_class' => '',
		], $args);

		$gidJs = sanitize_text_field((string)$d['gid_js']);
		$realGid = absint($d['real_gid']);
		$imagesFullDataToProcess = (!empty($d['images_full_data_to_process']) && is_array($d['images_full_data_to_process'])) ? $d['images_full_data_to_process'] : [];

		if($gidJs === '' || empty($realGid) || empty($imagesFullDataToProcess)){
			return '';
		}

		$options = (!empty($d['options']) && is_array($d['options'])) ? $d['options'] : [];
		$variablesGallery = (!empty($d['variables_gallery']) && is_array($d['variables_gallery'])) ? $d['variables_gallery'] : [];
		$variablesGeneral = (!empty($d['variables_general']) && is_array($d['variables_general'])) ? $d['variables_general'] : [];
		$galleriesOptions = (!empty($d['galleries_options']) && is_array($d['galleries_options'])) ? $d['galleries_options'] : [];
		$languageNames = (!empty($d['language_names']) && is_array($d['language_names'])) ? $d['language_names'] : [];
		$recentMainData = (!empty($d['recent_main_data']) && is_array($d['recent_main_data'])) ? $d['recent_main_data'] : [];
		$recentQueryData = (!empty($d['recent_query_data']) && is_array($d['recent_query_data'])) ? $d['recent_query_data'] : [];
		$recentUrlsData = (!empty($d['recent_urls_data']) && is_array($d['recent_urls_data'])) ? $d['recent_urls_data'] : [];
		$recentStatsData = (!empty($d['recent_stats_data']) && is_array($d['recent_stats_data'])) ? $d['recent_stats_data'] : [];
		$recentCommentsData = (!empty($d['recent_comments_data']) && is_array($d['recent_comments_data'])) ? $d['recent_comments_data'] : [];
		$recentInfoData = (!empty($d['recent_info_data']) && is_array($d['recent_info_data'])) ? $d['recent_info_data'] : [];
		$WpUserIdsData = (!empty($d['wp_user_ids_data']) && is_array($d['wp_user_ids_data'])) ? $d['wp_user_ids_data'] : [];
		$dataSlider = (!empty($d['data_slider']) && is_array($d['data_slider'])) ? $d['data_slider'] : [];
		$dataSliderSortedPids = (!empty($d['data_slider_sorted_pids']) && is_array($d['data_slider_sorted_pids'])) ? $d['data_slider_sorted_pids'] : [];
		$jsonCommentsData = (!empty($d['json_comments_data']) && is_array($d['json_comments_data'])) ? $d['json_comments_data'] : [];
		$countUserVotes = (!empty($d['count_user_votes']) && is_array($d['count_user_votes'])) ? $d['count_user_votes'] : [];
		$votedUserPids = (!empty($d['voted_user_pids']) && is_array($d['voted_user_pids'])) ? $d['voted_user_pids'] : [];
		$queryDataArray = (!empty($d['query_data_array']) && is_array($d['query_data_array'])) ? $d['query_data_array'] : [];
		$formUploadFullData = (!empty($d['form_upload_full_data']) && is_array($d['form_upload_full_data'])) ? $d['form_upload_full_data'] : [];
		$categoriesFullData = (!empty($d['categories_full_data']) && is_array($d['categories_full_data'])) ? $d['categories_full_data'] : [];
		$jsonInfoData = (!empty($d['json_info_data']) && is_array($d['json_info_data'])) ? $d['json_info_data'] : [];
		$ecommerceFilesData = (!empty($d['ecommerce_files_data']) && is_array($d['ecommerce_files_data'])) ? $d['ecommerce_files_data'] : [];
		$ecommerceOptions = (!empty($d['ecommerce_options']) && is_array($d['ecommerce_options'])) ? $d['ecommerce_options'] : [];
		$currenciesArray = (!empty($d['currencies_array']) && is_array($d['currencies_array'])) ? $d['currencies_array'] : [];
		$language_DeleteImages = (!empty($d['language_delete_images'])) ? $d['language_delete_images'] : '';
		$language_DeleteImage = (!empty($d['language_delete_image'])) ? $d['language_delete_image'] : '';
		$currentPageNumber = max(1, absint($d['current_page_number']));
		$backToGalleriesFromPageNumber = max(1, absint($d['back_to_galleries_from_page_number']));
		$shortcode_name = (!empty($d['shortcode_name'])) ? $d['shortcode_name'] : 'cg_gallery';
		$cgFeControlsStyle = trim((string)$d['shortcode_color_style']);
		$BorderRadiusClass = trim((string)$d['border_radius_class']);
		$cgl_heart = trim((string)$d['cgl_heart']);
		$galeryIDshort = sanitize_text_field((string)$d['gallery_id_short']);
		$galleryType = 'cg_grid';
		$galleryInitialMasonryLoadClass = 'cg_masonry_flex_loading';
		$Version = (!empty($options['general']['Version'])) ? $options['general']['Version'] : '';
		$sliderViewMainClass = '';
		$galeryIDuserForJs = $gidJs;
		$isCGalleries = false;

		$cgGalleryForceEntryGuidLinks = !empty($d['force_entry_guid_links']);
		$cgGalleryForceEntryGuidLinksNewTab = !empty($d['force_entry_guid_links_new_tab']);
		$cgGalleryReadOnlyInfoActions = !empty($d['read_only_info_actions']);
		$cgGalleryRatingCommentsGroupClass = (!empty($d['rating_comments_group_class'])) ? trim((string)$d['rating_comments_group_class']) : '';
		$cgGalleryArticleExtraClasses = [];
		$cgGalleryRenderSkeleton = true;
		$isEntryLandingNeighborsGallery = !empty($variablesGallery['isEntryLandingNeighborsGallery']);

		if($isEntryLandingNeighborsGallery){
			$galleryInitialMasonryLoadClass = '';
			$cgGalleryRenderSkeleton = false;
		}

		$textareaData = [
			'cg1l-data-options' => $options,
			'cg1l-data-voted-user-pids' => $votedUserPids,
			'cg1l-data-language-names' => $languageNames,
			'cg1l-data-variables-gallery' => $variablesGallery,
			'cg1l-data-variables-general' => $variablesGeneral,
			'cg1l-data-galleries-options' => $galleriesOptions,
			'cg1l-data-recent-main' => $recentMainData,
			'cg1l-data-recent-query' => $recentQueryData,
			'cg1l-data-recent-urls' => $recentUrlsData,
			'cg1l-data-recent-stats' => $recentStatsData,
			'cg1l-data-recent-comments' => $recentCommentsData,
			'cg1l-data-recent-info' => $recentInfoData,
			'cg1l-data-main-data-wp-user-ids' => (!empty($WpUserIdsData['mainDataWpUserIds']) && is_array($WpUserIdsData['mainDataWpUserIds'])) ? $WpUserIdsData['mainDataWpUserIds'] : [],
			'cg1l-data-nicknames' => (!empty($WpUserIdsData['nicknamesArray']) && is_array($WpUserIdsData['nicknamesArray'])) ? $WpUserIdsData['nicknamesArray'] : [],
			'cg1l-data-comments-wp-user-ids' => (!empty($WpUserIdsData['commentsWpUserIdsArray']) && is_array($WpUserIdsData['commentsWpUserIdsArray'])) ? $WpUserIdsData['commentsWpUserIdsArray'] : [],
			'cg1l-data-main-data-profile-images' => (!empty($WpUserIdsData['profileImagesArray']) && is_array($WpUserIdsData['profileImagesArray'])) ? $WpUserIdsData['profileImagesArray'] : [],
			'cg1l-data-main-data-wp-avatar-images' => (!empty($WpUserIdsData['wpAvatarImagesArray']) && is_array($WpUserIdsData['wpAvatarImagesArray'])) ? $WpUserIdsData['wpAvatarImagesArray'] : [],
			'cg1l-data-main-data-wp-avatar-images-large' => (!empty($WpUserIdsData['wpAvatarImagesLargeArray']) && is_array($WpUserIdsData['wpAvatarImagesLargeArray'])) ? $WpUserIdsData['wpAvatarImagesLargeArray'] : [],
			'cg1l-data-slider' => $dataSlider,
			'cg1l-data-slider-sorted-pids' => $dataSliderSortedPids,
		];

		include_once(__DIR__.'/gallery-view-renderer.php');

		ob_start();

		echo '<div class="cg-entry-landing-neighbors-container">';
		echo "<input type='hidden' class='cg-loaded-gids' value='".esc_attr($galeryIDuserForJs)."' data-cg-short='".esc_attr($galeryIDshort)."' data-cg-real-gid='".esc_attr($realGid)."' data-cg-gid='".esc_attr($galeryIDuserForJs)."' >";
		echo "<div id='mainCGdivContainer".esc_attr($galeryIDuserForJs)."' class='mainCGdivContainer cg-entry-landing-neighbors-gallery-instance' data-cg-gid='".esc_attr($galeryIDuserForJs)."'>";
		echo "<div id='mainCGdivHelperParent".esc_attr($galeryIDuserForJs)."' class='mainCGdivHelperParent cg_display_block ".esc_attr(trim($cgFeControlsStyle.' '.$BorderRadiusClass.' '.$cgl_heart))."' data-cg-gid='".esc_attr($galeryIDuserForJs)."'>";
		echo "<div id='cgLdsDualRingDivGalleryHide".esc_attr($galeryIDuserForJs)."' class='cg_hide cg-lds-dual-ring-div-gallery-hide cg-lds-dual-ring-div-gallery-hide-parent ".esc_attr(trim($BorderRadiusClass.' '.$cgFeControlsStyle))."'><div class='cg-lds-dual-ring-gallery-hide ".esc_attr($cgFeControlsStyle)."'></div></div>";
		echo "<div id='mainCGdiv".esc_attr($galeryIDuserForJs)."' class='mainCGdiv cg-entry-landing-neighbors-mainCGdiv ".esc_attr(trim($cgFeControlsStyle.' '.$BorderRadiusClass.' '.$cgl_heart.' '.$sliderViewMainClass))."' data-cg-gid='".esc_attr($galeryIDuserForJs)."' data-cg-version='".esc_attr($Version)."'>";
		echo "<span id='cgViewHelper".esc_attr($galeryIDuserForJs)."' class='cg_view_helper'></span>";
		echo '<div class="cg-lds-dual-ring-div '.esc_attr($cgFeControlsStyle).' cg_hide"><div class="cg-lds-dual-ring"></div></div>';
		echo "<div id='cgLdsDualRingMainCGdivHide".esc_attr($galeryIDuserForJs)."' class='cg-lds-dual-ring-div-gallery-hide cg-lds-dual-ring-div-gallery-hide-mainCGallery ".esc_attr($cgFeControlsStyle)." cg_hide'><div class='cg-lds-dual-ring-gallery-hide ".esc_attr($cgFeControlsStyle)." cg-lds-dual-ring-gallery-hide-mainCGallery'></div></div>";

		cg1l_render_vertical_scroll_slider([
			'gid' => $galeryIDuserForJs,
		]);

		echo "<div id='mainCGallery".esc_attr($galeryIDuserForJs)."' data-cg-gid='".esc_attr($galeryIDuserForJs)."' class='mainCGallery cg_modern_hover ".esc_attr(trim($cgFeControlsStyle.' '.$galleryType.' '.$galleryInitialMasonryLoadClass.' '.($cgFeControlsStyle == 'cg_fe_controls_style_white' ? 'cg_center_white' : 'cg_center_black').' '.$BorderRadiusClass))."' data-cg-entry-id='0' data-cg-real-gid='".esc_attr($realGid)."'>";

		foreach($textareaData as $className => $dataValue){
			$b64 = base64_encode(rawurlencode(wp_json_encode($dataValue)));
			echo '<textarea class="'.esc_attr($className).'" data-cg1l-gid="'.esc_attr($galeryIDuserForJs).'" style="display:none" aria-hidden="true">
        '.esc_textarea($b64).'
      </textarea>';
		}

		echo cg1l_render_gallery_view_markup([
			'images_full_data_to_process' => $imagesFullDataToProcess,
			'options' => $options,
			'shortcode_name' => $shortcode_name,
			'real_gid' => $realGid,
			'json_comments_data' => $jsonCommentsData,
			'count_user_votes' => $countUserVotes,
			'voted_user_pids' => $votedUserPids,
			'is_c_galleries' => false,
			'gallery_id_user_for_js' => $galeryIDuserForJs,
			'query_data_array' => $queryDataArray,
			'form_upload_full_data' => $formUploadFullData,
			'categories_full_data' => $categoriesFullData,
			'language_names' => $languageNames,
			'json_info_data' => $jsonInfoData,
			'current_page_number' => $currentPageNumber,
			'back_to_galleries_from_page_number' => $backToGalleriesFromPageNumber,
			'galleries_options' => $galleriesOptions,
			'ecommerce_files_data' => $ecommerceFilesData,
			'ecommerce_options' => $ecommerceOptions,
			'currencies_array' => $currenciesArray,
			'language_delete_images' => $language_DeleteImages,
			'language_delete_image' => $language_DeleteImage,
			'force_entry_guid_links' => $cgGalleryForceEntryGuidLinks,
			'force_entry_guid_links_new_tab' => $cgGalleryForceEntryGuidLinksNewTab,
			'read_only_info_actions' => $cgGalleryReadOnlyInfoActions,
			'rating_comments_group_class' => $cgGalleryRatingCommentsGroupClass,
			'render_skeleton' => $cgGalleryRenderSkeleton,
		]);

		echo "<div id='mainCGslider".esc_attr($galeryIDuserForJs)."' data-cg-gid='".esc_attr($galeryIDuserForJs)."' class='mainCGslider cg_hide cgCenterDivBackgroundColor'></div>";
		echo "<div id='cgLdsDualRingCGcenterDivHide".esc_attr($galeryIDuserForJs)."' class='cg-lds-dual-ring-div-gallery-hide ".esc_attr($cgFeControlsStyle)." cg-lds-dual-ring-div-gallery-hide-cgCenterDiv cg_hide'><div class='cg-lds-dual-ring-gallery-hide ".esc_attr($cgFeControlsStyle)." cg-lds-dual-ring-gallery-hide-cgCenterDiv'></div></div>";
		echo "</div>";
		echo "<div id='cgLdsDualRingCGcenterDivLazyLoader".esc_attr($galeryIDuserForJs)."' class='cg-lds-dual-ring-div-gallery-hide cg-lds-dual-ring-div-gallery-hide-mainCGallery ".esc_attr($cgFeControlsStyle)." cg_hide'><div class='cg-lds-dual-ring-gallery-hide ".esc_attr($cgFeControlsStyle)." cg-lds-dual-ring-gallery-hide-mainCGallery'></div></div>";
		echo "</div>";
		echo "<div id='cgCenterDivAppearenceHelper".esc_attr($galeryIDuserForJs)."' class='cgCenterDivAppearenceHelper'></div>";
		echo "</div>";
		echo "</div>";

		return ob_get_clean();
	}
}
