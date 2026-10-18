<?php
if(!defined('ABSPATH')){exit;}

// Shared by the gallery list and participant mail selection. Never accept SQL from a request.
if(!function_exists('cg_backend_gallery_filters')){
function cg_backend_gallery_filters($values){
	$filters = array('search'=>'','user'=>'','winners'=>false,'active'=>false,'inactive'=>false);
	if(isset($values['cg_search']) && is_scalar($values['cg_search'])){$filters['search'] = trim(sanitize_text_field($values['cg_search']));}
	if(isset($values['cg_wp_user_id_filter']) && is_scalar($values['cg_wp_user_id_filter'])){
		$user = (string)$values['cg_wp_user_id_filter'];
		if($user==='0'){$filters['user']='0';}
		elseif(absint($user)){$filters['user']=(string)absint($user);}
	}
	foreach(array('winners'=>'cg_show_only_winners','active'=>'cg_show_only_active','inactive'=>'cg_show_only_inactive') as $name=>$field){
		$filters[$name] = isset($values[$field]) && is_scalar($values[$field]) && !empty($values[$field]);
	}
	return $filters;
}
}

if(!function_exists('cg_backend_gallery_filter_query')){
function cg_backend_gallery_filter_query($galleryId,$filters){
	global $wpdb;
	$sql = '';
	foreach(array('winners'=>'Winner = 1','active'=>'Active = 1','inactive'=>'Active = 0') as $name=>$condition){
		if(!empty($filters[$name])){$sql .= ' AND cgGallery.'.$condition.' ';}
	}
	if(isset($filters['user']) && $filters['user']!==''){$sql .= ' AND cgGallery.WpUserId = '.absint($filters['user']).' ';}
	$searchSql = '';
	$params = array();
	if(isset($filters['search']) && $filters['search']!==''){
		$like = '%'.$wpdb->esc_like($filters['search']).'%';
		$searchSql = " AND (
			cgGallery.id = %d OR cgGallery.Exif LIKE %s
			OR EXISTS (SELECT 1 FROM {$wpdb->prefix}contest_gal1ery_entries AS cgSearchEntry
				WHERE cgSearchEntry.GalleryID = %d AND cgSearchEntry.pid = cgGallery.id
				AND (cgSearchEntry.Short_Text LIKE %s OR cgSearchEntry.Long_Text LIKE %s))
			OR EXISTS (SELECT 1 FROM {$wpdb->prefix}contest_gal1ery_categories AS cgSearchCategory
				WHERE cgSearchCategory.GalleryID = %d AND cgSearchCategory.id = cgGallery.Category AND cgSearchCategory.Name LIKE %s)
			OR EXISTS (SELECT 1 FROM {$wpdb->posts} AS cgSearchPost WHERE cgSearchPost.ID = cgGallery.WpUpload
				AND (cgSearchPost.post_content LIKE %s OR cgSearchPost.post_title LIKE %s OR cgSearchPost.post_name LIKE %s))
			OR EXISTS (SELECT 1 FROM {$wpdb->users} AS cgSearchUser WHERE cgSearchUser.ID = cgGallery.WpUserId
				AND (cgSearchUser.user_login LIKE %s OR cgSearchUser.user_nicename LIKE %s OR cgSearchUser.user_email LIKE %s OR cgSearchUser.display_name LIKE %s))
		) ";
		$params = array(absint($filters['search']),$like,absint($galleryId),$like,$like,absint($galleryId),$like,$like,$like,$like,$like,$like,$like,$like);
	}
	return array('filter_sql'=>$sql,'search_sql'=>$searchSql,'search_parameters'=>$params);
}
}

// Pure lookup shared by the gallery filter and the participant-mail dialog.
if(!function_exists('cg_backend_gallery_user_filter_options')){
function cg_backend_gallery_user_filter_options($GalleryID,$cgUserSearch,$selectedValue,$participantsOnly=false){
	global $wpdb;

	$tablename = $wpdb->prefix . "contest_gal1ery";
	$wpUsers = $wpdb->users;
	$participantWhere = $participantsOnly ? " AND COALESCE($tablename.IsProfileImage,0)=0" : "";

	$formatEntryCountText = function($count){
		$count = intval($count);
		return $count . ' ' . (($count === 1) ? 'entry' : 'entries');
	};

	$totalEntriesCount = intval($wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM $tablename WHERE GalleryID = %d $participantWhere",
		array($GalleryID)
	)));

	$withoutUserCount = intval($wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM $tablename WHERE GalleryID = %d $participantWhere AND WpUserId = 0",
		array($GalleryID)
	)));

	$options = array();
	$selectedOption = array(
		'value' => '',
		'label' => 'All user entries (' . $formatEntryCountText($totalEntriesCount) . ')'
	);
	$resolvedValue = '';
	$seenValues = array();

	$hasWithoutUser = ($withoutUserCount > 0);

	$selectedUserId = 0;
	if($selectedValue !== '0'){
		$selectedUserId = absint($selectedValue);
	}

	$selectedUser = null;
	if($selectedValue === '0' && $hasWithoutUser){
		$resolvedValue = '0';
		$selectedOption = array(
			'value' => '0',
			'label' => 'Without registered user (' . $formatEntryCountText($withoutUserCount) . ')'
		);
	} elseif(!empty($selectedUserId)) {
		$selectedUser = $wpdb->get_row($wpdb->prepare(
			"SELECT $wpUsers.ID, $wpUsers.user_login, COUNT($tablename.id) AS entries_count
			FROM $tablename
			INNER JOIN $wpUsers ON $wpUsers.ID = $tablename.WpUserId
			WHERE $tablename.GalleryID = %d $participantWhere AND $tablename.WpUserId = %d
			GROUP BY $wpUsers.ID, $wpUsers.user_login
			LIMIT 1",
			array($GalleryID, $selectedUserId)
		));
		if(!empty($selectedUser)){
			$resolvedValue = (string)$selectedUser->ID;
			$selectedOption = array(
				'value' => $resolvedValue,
				'label' => $selectedUser->user_login . ' (' . $formatEntryCountText($selectedUser->entries_count) . ')'
			);
		}
	}

	$options[] = array(
		'value' => '',
		'label' => 'All user entries (' . $formatEntryCountText($totalEntriesCount) . ')',
		'selected' => ($resolvedValue === '')
	);
	$seenValues[] = '';

	if($hasWithoutUser){
		$options[] = array(
			'value' => '0',
			'label' => 'Without registered user (' . $formatEntryCountText($withoutUserCount) . ')',
			'selected' => ($resolvedValue === '0')
		);
		$seenValues[] = '0';
	}

	$query = "SELECT $wpUsers.ID, $wpUsers.user_login, COUNT($tablename.id) AS entries_count
		FROM $tablename
		INNER JOIN $wpUsers ON $wpUsers.ID = $tablename.WpUserId
		WHERE $tablename.GalleryID = %d $participantWhere AND $tablename.WpUserId > 0";

	$queryArgs = array($GalleryID);

	if($cgUserSearch!==''){
		$like = '%' . $wpdb->esc_like($cgUserSearch) . '%';
		$query .= " AND ($wpUsers.user_login LIKE %s OR $wpUsers.user_email LIKE %s OR $wpUsers.user_nicename LIKE %s OR $wpUsers.display_name LIKE %s)";
		$queryArgs[] = $like;
		$queryArgs[] = $like;
		$queryArgs[] = $like;
		$queryArgs[] = $like;
	}

	$query .= " GROUP BY $wpUsers.ID, $wpUsers.user_login ORDER BY $wpUsers.user_login ASC LIMIT 100";

	$selectWPusers = $wpdb->get_results($wpdb->prepare($query, $queryArgs));

	if(!empty($selectWPusers)){
		foreach($selectWPusers as $user){
			$userValue = (string)$user->ID;
			$options[] = array(
				'value' => $userValue,
				'label' => $user->user_login . ' (' . $formatEntryCountText($user->entries_count) . ')',
				'selected' => ($resolvedValue === $userValue)
			);
			$seenValues[] = $userValue;
		}
	}

	if($resolvedValue !== '' && !in_array($resolvedValue, $seenValues, true) && !empty($selectedOption['label'])){
		$options[] = array(
			'value' => $selectedOption['value'],
			'label' => $selectedOption['label'],
			'selected' => true
		);
	}

	if($wpdb->last_error){return new WP_Error('cg_pmail_users','The participants could not be loaded.');}
	return array(
		'options' => $options,
		'resolved_value' => $resolvedValue,
		'selected_option' => $selectedOption
	);
}
}
