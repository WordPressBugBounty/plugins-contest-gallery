<?php

if(!function_exists('cg1l_render_gallery_view_markup')){
	function cg1l_render_gallery_view_markup($args){
		$d = array_merge([
			'images_full_data_to_process' => [],
			'options' => [],
			'shortcode_name' => 'cg_gallery',
			'real_gid' => 0,
			'json_comments_data' => [],
			'count_user_votes' => [],
			'voted_user_pids' => [],
			'is_c_galleries' => false,
			'gallery_id_user_for_js' => 0,
			'query_data_array' => [],
			'form_upload_full_data' => [],
			'categories_full_data' => [],
			'language_names' => [],
			'json_info_data' => [],
			'current_page_number' => 1,
			'back_to_galleries_from_page_number' => 1,
			'galleries_options' => [],
			'ecommerce_files_data' => [],
			'ecommerce_options' => [],
			'currencies_array' => [],
			'language_delete_images' => '',
			'language_delete_image' => '',
			'force_entry_guid_links' => false,
			'force_entry_guid_links_new_tab' => false,
			'read_only_info_actions' => false,
			'rating_comments_group_class' => '',
			'article_extra_classes' => [],
			'render_skeleton' => true,
		], $args);

		$imagesFullDataToProcess = (!empty($d['images_full_data_to_process']) && is_array($d['images_full_data_to_process'])) ? $d['images_full_data_to_process'] : [];

		if(empty($imagesFullDataToProcess)){
			return '';
		}

		$options = (!empty($d['options']) && is_array($d['options'])) ? $d['options'] : [];
		$shortcode_name = (!empty($d['shortcode_name'])) ? $d['shortcode_name'] : 'cg_gallery';
		$realGid = (!empty($d['real_gid'])) ? absint($d['real_gid']) : 0;
		$jsonCommentsData = (!empty($d['json_comments_data']) && is_array($d['json_comments_data'])) ? $d['json_comments_data'] : [];
		$countUserVotes = (!empty($d['count_user_votes']) && is_array($d['count_user_votes'])) ? $d['count_user_votes'] : [];
		$votedUserPids = (!empty($d['voted_user_pids']) && is_array($d['voted_user_pids'])) ? $d['voted_user_pids'] : [];
		$isCGalleries = !empty($d['is_c_galleries']);
		$galeryIDuserForJs = (!empty($d['gallery_id_user_for_js'])) ? $d['gallery_id_user_for_js'] : 0;
		$queryDataArray = (!empty($d['query_data_array']) && is_array($d['query_data_array'])) ? $d['query_data_array'] : [];
		$formUploadFullData = (!empty($d['form_upload_full_data']) && is_array($d['form_upload_full_data'])) ? $d['form_upload_full_data'] : [];
		$categoriesFullData = (!empty($d['categories_full_data']) && is_array($d['categories_full_data'])) ? $d['categories_full_data'] : [];
		$languageNames = (!empty($d['language_names']) && is_array($d['language_names'])) ? $d['language_names'] : [];
		$jsonInfoData = (!empty($d['json_info_data']) && is_array($d['json_info_data'])) ? $d['json_info_data'] : [];
		$currentPageNumber = max(1, absint($d['current_page_number']));
		$backToGalleriesFromPageNumber = max(1, absint($d['back_to_galleries_from_page_number']));
		$galleriesOptions = (!empty($d['galleries_options']) && is_array($d['galleries_options'])) ? $d['galleries_options'] : [];
		$ecommerceFilesData = (!empty($d['ecommerce_files_data']) && is_array($d['ecommerce_files_data'])) ? $d['ecommerce_files_data'] : [];
		$ecommerceOptions = (!empty($d['ecommerce_options']) && is_array($d['ecommerce_options'])) ? $d['ecommerce_options'] : [];
		$currenciesArray = (!empty($d['currencies_array']) && is_array($d['currencies_array'])) ? $d['currencies_array'] : [];
		$language_DeleteImages = (!empty($d['language_delete_images'])) ? $d['language_delete_images'] : '';
		$language_DeleteImage = (!empty($d['language_delete_image'])) ? $d['language_delete_image'] : '';

		$cgGalleryForceEntryGuidLinks = !empty($d['force_entry_guid_links']);
		$cgGalleryForceEntryGuidLinksNewTab = !empty($d['force_entry_guid_links_new_tab']);
		$cgGalleryReadOnlyInfoActions = !empty($d['read_only_info_actions']);
		$cgGalleryRatingCommentsGroupClass = (!empty($d['rating_comments_group_class'])) ? trim((string)$d['rating_comments_group_class']) : '';
		$cgGalleryArticleExtraClasses = (!empty($d['article_extra_classes']) && is_array($d['article_extra_classes'])) ? $d['article_extra_classes'] : [];
		$cgGalleryRenderSkeleton = !empty($d['render_skeleton']);

		ob_start();
		include(__DIR__.'/gallery-view.php');
		return ob_get_clean();
	}
}
