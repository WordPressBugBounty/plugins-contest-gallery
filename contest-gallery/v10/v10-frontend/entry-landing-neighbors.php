<?php

if(!function_exists('cg1l_filter_entry_landing_neighbor_id_keyed_data')){
	function cg1l_filter_entry_landing_neighbor_id_keyed_data($data, $allowedIdsLookup){
		if(empty($allowedIdsLookup) || empty($data) || !is_array($data)){
			return is_array($data) ? $data : [];
		}

		$filteredData = [];

		foreach($data as $key => $value){
			$normalizedKey = absint($key);
			if(!empty($allowedIdsLookup[$normalizedKey])){
				$filteredData[$key] = $value;
			}
		}

		return $filteredData;
	}
}

if(!function_exists('cg1l_filter_entry_landing_neighbor_value_list')){
	function cg1l_filter_entry_landing_neighbor_value_list($data, $allowedIdsLookup){
		if(empty($allowedIdsLookup) || empty($data) || !is_array($data)){
			return is_array($data) ? $data : [];
		}

		$filteredData = [];

		foreach($data as $key => $value){
			if(is_array($value)){
				$normalizedKey = absint($key);
				if(!empty($allowedIdsLookup[$normalizedKey])){
					$filteredData[$key] = $value;
				}
			}else{
				$normalizedValue = absint($value);
				if(!empty($allowedIdsLookup[$normalizedValue])){
					$filteredData[] = $value;
				}
			}
		}

		return $filteredData;
	}
}

if(!function_exists('cg1l_render_entry_landing_neighbors')){
	function cg1l_render_entry_landing_neighbors($args){
		$d = array_merge([
			'entry_id' => 0,
			'images_full_data' => [],
			'options' => [],
			'gid_js' => 0,
			'real_gid' => 0,
			'gallery_id_short' => '',
			'shortcode_name' => 'cg_gallery',
			'json_comments_data' => [],
			'count_user_votes' => [],
			'voted_user_pids' => [],
			'query_data_array' => [],
			'json_info_data' => [],
			'form_upload_full_data' => [],
			'categories_full_data' => [],
			'language_names' => [],
			'galleries_options' => [],
			'ecommerce_files_data' => [],
			'ecommerce_options' => [],
			'currencies_array' => [],
			'language_delete_images' => '',
			'language_delete_image' => '',
			'current_page_number' => 1,
			'back_to_galleries_from_page_number' => 1,
			'shortcode_color_style' => 'cg_fe_controls_style_white',
			'border_radius_class' => '',
			'cgl_heart' => '',
			'variables_gallery' => [],
			'variables_general' => [],
			'recent_main_data' => [],
			'recent_query_data' => [],
			'recent_urls_data' => [],
			'recent_stats_data' => [],
			'recent_comments_data' => [],
			'recent_info_data' => [],
			'wp_user_ids_data' => [],
			'data_slider' => [],
			'data_slider_sorted_pids' => [],
			'wp_user_id' => 0,
		], $args);

		$entryId = absint($d['entry_id']);
		$realGid = absint($d['real_gid']);
		$imagesFullData = (!empty($d['images_full_data']) && is_array($d['images_full_data'])) ? $d['images_full_data'] : [];

		if(empty($entryId) || empty($realGid) || empty($imagesFullData[$entryId])){
			return '';
		}

		$orderedIds = array_values(array_map('intval', array_keys($imagesFullData)));
		$currentIndex = array_search($entryId, $orderedIds, true);

		if($currentIndex === false){
			return '';
		}

		$previousStartIndex = max(0, $currentIndex - 3);
		$previousIds = array_slice($orderedIds, $previousStartIndex, $currentIndex - $previousStartIndex);
		$nextIds = array_slice($orderedIds, $currentIndex + 1, 3);
		$neighborIds = array_values(array_merge($previousIds, $nextIds));

		if(empty($neighborIds)){
			return '';
		}

		$allowedIdsLookup = [];
		foreach($neighborIds as $neighborId){
			$allowedIdsLookup[absint($neighborId)] = true;
		}

		$neighborImagesFullDataToProcess = cg1l_filter_entry_landing_neighbor_id_keyed_data($imagesFullData, $allowedIdsLookup);
		if(empty($neighborImagesFullDataToProcess)){
			return '';
		}

		$options = (!empty($d['options']) && is_array($d['options'])) ? $d['options'] : [];
		$options['general'] = (!empty($options['general']) && is_array($options['general'])) ? $options['general'] : [];
		$options['visual'] = (!empty($options['visual']) && is_array($options['visual'])) ? $options['visual'] : [];
		$options['pro'] = (!empty($options['pro']) && is_array($options['pro'])) ? $options['pro'] : [];
		$blogLookFullWindowActive = (!empty($options['visual']['BlogLookFullWindow']) && intval($options['visual']['BlogLookFullWindow']) === 1);

		$options['general']['ThumbLook'] = 1;
		$options['general']['SliderLook'] = 0;
		$options['general']['OnlyGalleryView'] = 0;
		$options['general']['FullSizeImageOutGallery'] = 0;
		$options['general']['FullSizeImageOutGalleryNewTab'] = 0;
		$options['general']['PicsPerSite'] = count($neighborIds);
		$options['general']['ThumbLookOrder'] = 1;
		$options['general']['SliderLookOrder'] = 2;
		$options['visual']['BlogLook'] = 0;
		$options['visual']['BlogLookOrder'] = 3;
		$options['visual']['ForwardToWpPageEntry'] = 1;
		$options['visual']['ForwardToWpPageEntryInNewTab'] = ($blogLookFullWindowActive) ? 0 : ((!empty($options['visual']['ForwardToWpPageEntryInNewTab'])) ? 1 : 0);
		$options['pro']['MinusVote'] = 0;
		$options['pro']['PreselectSort'] = 'custom';

		$variablesGallery = (!empty($d['variables_gallery']) && is_array($d['variables_gallery'])) ? $d['variables_gallery'] : [];
		$variablesGallery['gidReal'] = $realGid;
		$variablesGallery['currentLook'] = 'thumb';
		$variablesGallery['galleryLoaded'] = false;
		$variablesGallery['galleryAlreadyFullWindow'] = false;
		$variablesGallery['shortcode_name'] = (!empty($d['shortcode_name'])) ? $d['shortcode_name'] : 'cg_gallery';
		$variablesGallery['currentStep'] = 1;
		$variablesGallery['galleryShortCodeEntryId'] = 0;
		$variablesGallery['openedRealId'] = 0;
		$variablesGallery['entryId'] = 0;
		$variablesGallery['allowedRealIds'] = $neighborIds;
		$variablesGallery['galleryDataUseAllowedRealIds'] = 1;
		$variablesGallery['hasWpPageParent'] = false;
		$variablesGallery['isCgWpPageEntryLandingPage'] = false;
		$variablesGallery['imageDataLength'] = count($neighborIds);
		$variablesGallery['imagesFullDataLength'] = count($neighborIds);
		$variablesGallery['lengthData'] = count($neighborIds);
		$variablesGallery['isEntryLandingNeighborsGallery'] = true;
		$variablesGallery['orderGalleries'] = [
			1 => 'ThumbLookOrder',
			2 => 'SliderLookOrder',
			3 => 'BlogLookOrder'
		];
		$variablesGallery['galleryHash'] = cg_hash_function('---cngl1---'.$realGid.'-nb');
		$variablesGallery['galleryDataAccessHash'] = cg1l_get_gallery_data_access_hash(
			$realGid,
			$variablesGallery['shortcode_name'],
			absint($d['wp_user_id']),
			1
		);

		$queryDataArray = cg1l_filter_entry_landing_neighbor_id_keyed_data(
			(!empty($d['query_data_array']) && is_array($d['query_data_array'])) ? $d['query_data_array'] : [],
			$allowedIdsLookup
		);
		$jsonCommentsData = cg1l_filter_entry_landing_neighbor_id_keyed_data(
			(!empty($d['json_comments_data']) && is_array($d['json_comments_data'])) ? $d['json_comments_data'] : [],
			$allowedIdsLookup
		);
		$jsonInfoData = cg1l_filter_entry_landing_neighbor_id_keyed_data(
			(!empty($d['json_info_data']) && is_array($d['json_info_data'])) ? $d['json_info_data'] : [],
			$allowedIdsLookup
		);
		$recentMainData = $neighborImagesFullDataToProcess;
		$recentQueryData = $queryDataArray;
		$recentUrlsData = $neighborImagesFullDataToProcess;
		$recentStatsData = $neighborImagesFullDataToProcess;
		$recentCommentsData = $jsonCommentsData;
		$recentInfoData = $jsonInfoData;

		$WpUserIdsData = (!empty($d['wp_user_ids_data']) && is_array($d['wp_user_ids_data'])) ? $d['wp_user_ids_data'] : [];
		$WpUserIdsData['mainDataWpUserIds'] = cg1l_filter_entry_landing_neighbor_id_keyed_data(
			(!empty($WpUserIdsData['mainDataWpUserIds']) && is_array($WpUserIdsData['mainDataWpUserIds'])) ? $WpUserIdsData['mainDataWpUserIds'] : [],
			$allowedIdsLookup
		);

		foreach($recentMainData as $id => $recentData){
			if(!empty($WpUserIdsData['mainDataWpUserIds'][$id])){
				$recentMainData[$id]['WpUserId'] = $WpUserIdsData['mainDataWpUserIds'][$id];
			}
		}

		$neighborImagesFullDataToProcess = $recentMainData;

		$dataSlider = cg1l_filter_entry_landing_neighbor_id_keyed_data(
			(!empty($d['data_slider']) && is_array($d['data_slider'])) ? $d['data_slider'] : [],
			$allowedIdsLookup
		);
		$dataSliderSortedPids = cg1l_filter_entry_landing_neighbor_value_list(
			(!empty($d['data_slider_sorted_pids']) && is_array($d['data_slider_sorted_pids'])) ? $d['data_slider_sorted_pids'] : [],
			$allowedIdsLookup
		);
		$votedUserPids = cg1l_filter_entry_landing_neighbor_value_list(
			(!empty($d['voted_user_pids']) && is_array($d['voted_user_pids'])) ? $d['voted_user_pids'] : [],
			$allowedIdsLookup
		);

		include_once(__DIR__.'/gallery-instance-renderer.php');

		return cg1l_render_gallery_instance_shell([
			'gid_js' => $realGid.'-nb',
			'real_gid' => $realGid,
			'gallery_id_short' => (!empty($d['gallery_id_short'])) ? $d['gallery_id_short'] : '',
			'options' => $options,
			'shortcode_name' => $variablesGallery['shortcode_name'],
			'variables_gallery' => $variablesGallery,
			'variables_general' => (!empty($d['variables_general']) && is_array($d['variables_general'])) ? $d['variables_general'] : [],
			'galleries_options' => (!empty($d['galleries_options']) && is_array($d['galleries_options'])) ? $d['galleries_options'] : [],
			'language_names' => (!empty($d['language_names']) && is_array($d['language_names'])) ? $d['language_names'] : [],
			'recent_main_data' => $recentMainData,
			'recent_query_data' => $recentQueryData,
			'recent_urls_data' => $recentUrlsData,
			'recent_stats_data' => $recentStatsData,
			'recent_comments_data' => $recentCommentsData,
			'recent_info_data' => $recentInfoData,
			'wp_user_ids_data' => $WpUserIdsData,
			'data_slider' => $dataSlider,
			'data_slider_sorted_pids' => $dataSliderSortedPids,
			'images_full_data_to_process' => $neighborImagesFullDataToProcess,
			'json_comments_data' => $jsonCommentsData,
			'count_user_votes' => cg1l_filter_entry_landing_neighbor_id_keyed_data(
				(!empty($d['count_user_votes']) && is_array($d['count_user_votes'])) ? $d['count_user_votes'] : [],
				$allowedIdsLookup
			),
			'voted_user_pids' => $votedUserPids,
			'query_data_array' => $queryDataArray,
			'form_upload_full_data' => (!empty($d['form_upload_full_data']) && is_array($d['form_upload_full_data'])) ? $d['form_upload_full_data'] : [],
			'categories_full_data' => (!empty($d['categories_full_data']) && is_array($d['categories_full_data'])) ? $d['categories_full_data'] : [],
			'json_info_data' => $jsonInfoData,
			'current_page_number' => max(1, absint($d['current_page_number'])),
			'back_to_galleries_from_page_number' => max(1, absint($d['back_to_galleries_from_page_number'])),
			'ecommerce_files_data' => (!empty($d['ecommerce_files_data']) && is_array($d['ecommerce_files_data'])) ? $d['ecommerce_files_data'] : [],
			'ecommerce_options' => (!empty($d['ecommerce_options']) && is_array($d['ecommerce_options'])) ? $d['ecommerce_options'] : [],
			'currencies_array' => (!empty($d['currencies_array']) && is_array($d['currencies_array'])) ? $d['currencies_array'] : [],
			'language_delete_images' => (!empty($d['language_delete_images'])) ? $d['language_delete_images'] : '',
			'language_delete_image' => (!empty($d['language_delete_image'])) ? $d['language_delete_image'] : '',
			'shortcode_color_style' => (!empty($d['shortcode_color_style'])) ? $d['shortcode_color_style'] : 'cg_fe_controls_style_white',
			'border_radius_class' => (!empty($d['border_radius_class'])) ? $d['border_radius_class'] : '',
			'cgl_heart' => (!empty($d['cgl_heart'])) ? $d['cgl_heart'] : '',
			'force_entry_guid_links' => true,
			'force_entry_guid_links_new_tab' => !empty($options['visual']['ForwardToWpPageEntryInNewTab']),
			'read_only_info_actions' => true,
			'rating_comments_group_class' => 'cg_pointer_events_none cg-entry-landing-neighbor-read-only',
		]);
	}
}
