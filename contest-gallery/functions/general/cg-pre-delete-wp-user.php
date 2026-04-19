<?php
add_action( 'delete_user', 'cg1l_pre_delete_user',10,3);

if(!function_exists('cg1l_collect_gallery_ids_and_images_for_wp_user')){
	function cg1l_collect_gallery_ids_and_images_for_wp_user($user_id){
		global $wpdb;
		$tablename = $wpdb->prefix . "contest_gal1ery";
		$rows = $wpdb->get_results($wpdb->prepare(
			"SELECT id, GalleryID FROM $tablename WHERE WpUserId = %d ORDER BY GalleryID ASC, id ASC",
			$user_id
		));

		$collectGalleryIDsAndImagesArray = [];
		foreach ($rows as $rowObject){
			$GalleryID = absint($rowObject->GalleryID);
			$imageID = absint($rowObject->id);
			if(empty($GalleryID) || empty($imageID)){
				continue;
			}
			if(empty($collectGalleryIDsAndImagesArray[$GalleryID])){
				$collectGalleryIDsAndImagesArray[$GalleryID] = [];
			}
			$collectGalleryIDsAndImagesArray[$GalleryID][] = $imageID;
		}

		return $collectGalleryIDsAndImagesArray;
	}
}

if(!function_exists('cg1l_collect_wp_user_related_pid_gallery_rows')){
	function cg1l_collect_wp_user_related_pid_gallery_rows($table,$userColumn,$user_id){
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare(
			"SELECT DISTINCT pid, GalleryID FROM $table WHERE $userColumn = %d AND pid > 0 AND GalleryID > 0",
			$user_id
		));
	}
}

if(!function_exists('cg1l_filter_wp_user_related_rows_by_image_ids')){
	function cg1l_filter_wp_user_related_rows_by_image_ids($rows,$imageIdsLookup,$skipOwnImageIds = false){
		if(empty($skipOwnImageIds) || empty($imageIdsLookup) || empty($rows)){
			return $rows;
		}

		$filteredRows = [];
		foreach ($rows as $row){
			$pid = (!empty($row->pid)) ? absint($row->pid) : 0;
			if(empty($pid) || empty($imageIdsLookup[$pid])){
				$filteredRows[] = $row;
			}
		}

		return $filteredRows;
	}
}

if(!function_exists('cg1l_invalidate_wp_user_related_rows')){
	function cg1l_invalidate_wp_user_related_rows($rows,$types){
		if(empty($rows) || empty($types)){
			return;
		}

		$processedRows = [];
		$processedGalleries = [];

		foreach ($rows as $row){
			$pid = (!empty($row->pid)) ? absint($row->pid) : 0;
			$GalleryID = (!empty($row->GalleryID)) ? absint($row->GalleryID) : 0;

			if(empty($pid) || empty($GalleryID)){
				continue;
			}

			$rowKey = $GalleryID.'-'.$pid;
			if(!empty($processedRows[$rowKey])){
				continue;
			}
			$processedRows[$rowKey] = true;

			foreach ($types as $type){
				cg1l_push_recent_id_file($GalleryID,$pid,$type);
				$processedGalleries[$GalleryID][$type] = true;
			}
		}

		foreach ($processedGalleries as $GalleryID => $galleryTypes){
			foreach ($galleryTypes as $type => $isActive){
				if($isActive){
					cg1l_create_last_updated_time_file($GalleryID,$type);
				}
			}
		}
	}
}

if(!function_exists('cg1l_delete_rows_for_wp_user_if_table_exists')){
	function cg1l_delete_rows_for_wp_user_if_table_exists($table,$userColumn,$user_id){
		global $wpdb;
		$tableLike = $wpdb->esc_like($table);
		$existing = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$tableLike));
		if($existing !== $table){
			return;
		}
		$wpdb->query($wpdb->prepare(
			"DELETE FROM $table WHERE $userColumn = %d",
			$user_id
		));
	}
}

if(!function_exists('cg1l_append_deleted_image_ids_json_for_wp_user_delete')){
	function cg1l_append_deleted_image_ids_json_for_wp_user_delete($GalleryID,$imageIDs){
		if(empty($GalleryID) || empty($imageIDs)){
			return;
		}

		$wp_upload_dir = wp_upload_dir();
		$dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json';
		if(!is_dir($dir)){
			return;
		}

		$jsonFile = $dir.'/'.$GalleryID.'-deleted-image-ids.json';
		if(file_exists($jsonFile)){
			$json = json_decode((string) file_get_contents($jsonFile),true);
			if(!is_array($json)){
				$json = [];
			}
		}else{
			$json = [];
		}

		foreach ($imageIDs as $imageID){
			$imageID = absint($imageID);
			if(!empty($imageID) && in_array($imageID,$json,true)===false){
				$json[] = $imageID;
			}
		}

		file_put_contents($jsonFile,wp_json_encode($json));
	}
}

if(!function_exists('cg1l_pre_delete_user')){
	function cg1l_pre_delete_user( $user_id,$reassign = null,$user = null) {

		$user_id = absint($user_id);
		if(empty($user_id)){
			return;
		}

		global $wpdb;

		$tablename = $wpdb->prefix . "contest_gal1ery";
		$tablenameIp = $wpdb->prefix . "contest_gal1ery_ip";
		$contest_gal1ery_create_user_entries = $wpdb->prefix . "contest_gal1ery_create_user_entries";
		$tablename_contest_gal1ery_google_users = $wpdb->prefix."contest_gal1ery_google_users";
		$tablenameComments = $wpdb->prefix . "contest_gal1ery_comments";
		$tablename_mails = $wpdb->prefix . "contest_gal1ery_mails";
		$tablename_user_comment_mails = $wpdb->prefix . "contest_gal1ery_user_comment_mails";
		$tablename_user_vote_mails = $wpdb->prefix . "contest_gal1ery_user_vote_mails";
		$tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
		$wp_usermeta_table = $wpdb->prefix . "usermeta";

		$reassign_user_id = (!empty($reassign) && absint($reassign) !== $user_id) ? absint($reassign) : 0;
		$isDeleteMode = empty($reassign_user_id);

		$collectGalleryIDsAndImagesArray = cg1l_collect_gallery_ids_and_images_for_wp_user($user_id);
		$imageIdsLookup = [];
		foreach ($collectGalleryIDsAndImagesArray as $GalleryID => $imageIDs){
			foreach ($imageIDs as $imageID){
				$imageIdsLookup[absint($imageID)] = true;
			}
		}

		$commentRowsByUser = cg1l_collect_wp_user_related_pid_gallery_rows($tablenameComments,'WpUserId',$user_id);
		$voteRowsByUser = cg1l_collect_wp_user_related_pid_gallery_rows($tablenameIp,'WpUserId',$user_id);

		$commentRowsToInvalidate = cg1l_filter_wp_user_related_rows_by_image_ids($commentRowsByUser,$imageIdsLookup,$isDeleteMode);
		$voteRowsToInvalidate = cg1l_filter_wp_user_related_rows_by_image_ids($voteRowsByUser,$imageIdsLookup,$isDeleteMode);

		if($isDeleteMode){
			foreach ($collectGalleryIDsAndImagesArray as $GalleryID => $imageIDs){
				if(empty($GalleryID) || empty($imageIDs)){
					continue;
				}

				cg1l_append_deleted_image_ids_json_for_wp_user_delete($GalleryID,$imageIDs);
				cg_delete_images($GalleryID,$imageIDs);
				cg1l_create_last_updated_time_file_all($GalleryID);
			}
		}else{
			$wpdb->query($wpdb->prepare(
				"UPDATE $tablename SET WpUserId = %d WHERE WpUserId = %d",
				$reassign_user_id,$user_id
			));

			foreach ($collectGalleryIDsAndImagesArray as $GalleryID => $imageIDs){
				if(empty($GalleryID) || empty($imageIDs)){
					continue;
				}

				foreach ($imageIDs as $imageID){
					cg1l_push_recent_id_file($GalleryID,$imageID,'image-main-data-last-update');
				}

				cg1l_create_last_updated_time_file($GalleryID,'image-main-data-last-update');
			}
		}

		$wpdb->query($wpdb->prepare(
			"DELETE FROM $tablenameComments WHERE WpUserId = %d",
			$user_id
		));
		$wpdb->query($wpdb->prepare(
			"DELETE FROM $tablenameIp WHERE WpUserId = %d",
			$user_id
		));

		cg1l_invalidate_wp_user_related_rows($commentRowsToInvalidate,['image-comments-data-last-update','image-main-data-last-update']);
		cg1l_invalidate_wp_user_related_rows($voteRowsToInvalidate,['image-stats-data-last-update','image-main-data-last-update']);

		$wpdb->query($wpdb->prepare(
			"DELETE FROM $contest_gal1ery_create_user_entries WHERE wp_user_id = %d",
			$user_id
		));

		$wpdb->query($wpdb->prepare(
			"DELETE FROM $wp_usermeta_table WHERE meta_key LIKE %s AND user_id = %d",
			'%cg_custom_field_id_%',$user_id
		));

		cg1l_delete_rows_for_wp_user_if_table_exists($tablename_contest_gal1ery_google_users,'WpUserId',$user_id);
		cg1l_delete_rows_for_wp_user_if_table_exists($tablename_mails,'WpUserId',$user_id);
		cg1l_delete_rows_for_wp_user_if_table_exists($tablename_user_comment_mails,'WpUserId',$user_id);
		cg1l_delete_rows_for_wp_user_if_table_exists($tablename_user_vote_mails,'WpUserId',$user_id);

		$wpdb->query($wpdb->prepare(
			"UPDATE $tablename_ecommerce_orders SET WpUserId = 0 WHERE WpUserId = %d",
			$user_id
		));

	}
}
