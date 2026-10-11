<?php
if(!defined('ABSPATH')){exit;}

function cg_participants_context($context){
	if(!$context || !empty($context['isCGalleries']) || !in_array($context['shortcodeName'],array('cg_gallery','cg_gallery_no_voting'),true) || !cg_member_enabled($context['realGid'],$context['shortcodeName'])){return false;}
	$options = cg_member_options($context['realGid'],$context['shortcodeName']);
	if(!is_user_logged_in() && !empty($options['pro']['RegUserGalleryOnly'])){return false;}
	$interval = cg_shortcode_interval_check($context['realGid'],cg_member_replacement_source($context['realGid']),$context['shortcodeName']);
	return !empty($interval['shortcodeIsActive']);
}

// Only the list presentation changes. Entry opening, voting and comments keep their source settings.
function cg_participants_gallery_options($options){
	$options['general']['AllowGalleryScript'] = 1;
	$options['pro']['GalleryUpload'] = 0;
	$options['visual']['BlogLook'] = 0;
	$options['general']['ThumbLook'] = 1;
	$options['general']['ThumbLookOrder'] = 1;
	foreach(array('HeightLook','RowLook','SliderLook') as $key){$options['general'][$key] = 0;}
	return $options;
}

function cg_participants_request_context(){
	nocache_headers();
	header('Cache-Control: private, no-store, max-age=0');
	cg1l_ajax_reject_bad_origin();
	cg_check_frontend_nonce();
	$token = isset($_POST['token']) && is_string($_POST['token']) ? wp_unslash($_POST['token']) : '';
	$context = cg1l_verify_runtime_context_token($token);
	$hash = isset($_POST['galleryDataAccessHash']) && is_string($_POST['galleryDataAccessHash']) ? wp_unslash($_POST['galleryDataAccessHash']) : '';
	if(!cg_participants_context($context) || !cg1l_hash_equals(cg1l_get_gallery_data_access_hash($context['realGid'],$context['shortcodeName'],get_current_user_id(),$context['galleryDataUseAllowedRealIds']),$hash)){
		wp_send_json_error(array('message'=>cg_member_t('This gallery is not available.')));
	}
	return $context;
}

// Membership is derived from published entries, never from the WordPress user directory.
function cg_participants_rows($gid,$shortcode,$userId = 0){
	global $wpdb;
	$context = array('realGid'=>absint($gid),'shortcodeName'=>$shortcode);
	if(!cg_participants_context($context)){return array();}
	$ownerSql = $userId ? $wpdb->prepare(' AND e.WpUserId=%d',$userId) : '';
	$rows = $wpdb->get_results($wpdb->prepare('SELECT e.id,e.WpUserId,e.Active,e.Category,e.Winner,e.EcommerceEntry,e.IsProfileImage,e.Timestamp,u.display_name FROM '.$wpdb->prefix.'contest_gal1ery e INNER JOIN '.$wpdb->users.' u ON u.ID=e.WpUserId WHERE e.GalleryID=%d AND e.Active=1 AND e.IsProfileImage=0'.$ownerSql.' ORDER BY e.id DESC',$gid),ARRAY_A);
	$uploads = wp_upload_dir();
	$base = $uploads['basedir'].'/contest-gallery/gallery-id-'.absint($gid).'/json/'.absint($gid);
	$categories = is_file($base.'-categories.json') ? json_decode(file_get_contents($base.'-categories.json'),true) : array();
	$form = is_file($base.'-form-upload.json') ? json_decode(file_get_contents($base.'-form-upload.json'),true) : array();
	$keyed = array();
	foreach($rows as $row){$keyed[absint($row['id'])] = $row;}
	$ids = cg1l_frontend_get_allowed_real_ids($keyed,$shortcode,$categories,cg_member_options($gid,$shortcode),get_current_user_id(),$form);
	return cg1l_frontend_filter_id_keyed_data_by_allowed_ids($keyed,$ids);
}

function cg_participants_compare($a,$b){
	$order = strnatcasecmp($a['name'],$b['name']);
	return $order ? $order : ($a['id']===$b['id'] ? 0 : ($a['id']<$b['id'] ? -1 : 1));
}

function cg_participants_profile_image($profile,$isCard = false){
	$initial = '<span class="cg-participant-initial" aria-hidden="true">'.esc_html(mb_substr($profile['name'],0,1)).'</span>';
	if($profile['largeImage']){return '<img class="skip-lazy" data-skip-lazy="1" src="'.esc_url($profile['largeImage']).'" alt="" decoding="async">'.($isCard ? $initial : '');}
	return $initial;
}

// The badge counts the same visible members as the directory, including in My images.
function cg_participants_count($gid,$shortcode){
	$users = array();
	foreach(cg_participants_rows($gid,$shortcode) as $row){$users[absint($row['WpUserId'])] = true;}
	return count($users);
}

function cg_participants_collect($gid,$shortcode,$options,$userId = 0){
	$rows = cg_participants_rows($gid,$shortcode,$userId);
	$users = array();
	$ratingType = cg_page_stats_votes_visible($options,$shortcode) ? cg1l_get_rating_type($options) : 'none';
	$uploads = wp_upload_dir();
	$base = $uploads['basedir'].'/contest-gallery/gallery-id-'.absint($gid).'/json/';
	foreach($rows as $entryId=>$row){
		$id = absint($row['WpUserId']);
		if(!isset($users[$id])){
			$users[$id] = array('id'=>$id,'entry'=>absint($entryId),'name'=>$row['display_name'],'count'=>0,'date'=>0,'comments'=>0,'votes'=>0,'rating'=>0,'distribution'=>array());
		}
		$user =& $users[$id];
		$user['count']++;
		$user['date'] = max($user['date'],absint($row['Timestamp']));
		if($ratingType!=='none'){
			$stats = cg1l_get_entry_stats_data($gid,$entryId);
			$entry = isset($stats[$entryId]) ? $stats[$entryId] : array();
			if($ratingType==='one-star'){$user['votes'] += cg1l_get_one_star_rating_count($entry,$options,array(),true);}
			else{
				foreach(cg1l_get_multi_star_rating_distribution($entry,$options,array(),true) as $stars=>$count){
					if(!isset($user['distribution'][$stars])){$user['distribution'][$stars] = 0;}
					$user['distribution'][$stars] += $count;
					$user['votes'] += $count;
					$user['rating'] += $stars*$count;
				}
			}
		}
		if(!empty($options['general']['AllowComments'])){
			// Read current per-entry JSON without rebuilding a shared segment from a restricted dataset.
			$file = $base.'image-comments/image-comments-'.absint($entryId).'.json';
			$comments = is_file($file) ? json_decode(file_get_contents($file),true) : array();
			$user['comments'] += cg1l_count_comments($entryId,array($entryId=>$comments));
		}
		unset($user);
	}
	foreach($users as &$user){
		if($ratingType==='one-star'){$user['rating'] = $user['votes'];}
		elseif($ratingType==='average'){$user['rating'] = $user['votes'] ? round($user['rating']/$user['votes'],1) : 0;}
	}
	unset($user);
	return $users;
}

function cg_participants_sort_choices($options,$shortcode){
	$choices = array('date-desc'=>'Date descend','date-asc'=>'Date ascend','name-asc'=>'Name A–Z','name-desc'=>'Name Z–A');
	if(cg_page_stats_votes_visible($options,$shortcode)){
		$choices['rate-desc'] = 'Votes descend';$choices['rate-asc'] = 'Votes ascend';
	}
	if(!empty($options['general']['AllowComments'])){$choices['comment-desc'] = 'Comments descend';$choices['comment-asc'] = 'Comments ascend';}
	return $choices;
}

function cg_participants_default_sort($options){
	$preselect = isset($options['pro']['PreselectSort']) ? $options['pro']['PreselectSort'] : '';
	if(strpos($preselect,'rating_')===0){return strpos($preselect,'asc')!==false ? 'rate-asc' : 'rate-desc';}
	if(strpos($preselect,'comments_')===0){return strpos($preselect,'asc')!==false ? 'comment-asc' : 'comment-desc';}
	return $preselect==='date_ascend' ? 'date-asc' : 'date-desc';
}

function cg_participants_controls($choices,$sort,$search){
	$html = '<div class="mainCGdivHelperChild cg-participants-controls"><div class="cg_header"><div class="cg_gallery_view_sort_control"><div class="cg_sort_div">';
	$html .= '<div class="cg_search_input_div cg_gallery_control_element"><input type="search" class="cg_search_input cg-participants-search" maxlength="200" autocomplete="off" value="'.esc_attr($search).'" placeholder="'.esc_attr(cg_member_t('Search')).'" aria-label="'.esc_attr(cg_member_t('Search names and profile information')).'"></div>';
	$html .= '<label class="cg_select_order_label cg_gallery_control_element cg_hover_effect"><select class="cg_select_order cg_gallery_control_element cg-participants-sort" aria-label="'.esc_attr(cg_member_t('Sort by')).'">';
	foreach($choices as $key=>$label){$html .= '<option value="'.esc_attr($key).'"'.selected($sort,$key,false).'>'.esc_html(cg_member_t($label)).'</option>';}
	return $html.'</select></label><div class="cg-participants-theme-slot"></div></div></div></div></div>';
}

// Use the same compact selector/range/arrows and page size as the entry gallery.
function cg_participants_pagination($page,$pages,$total,$perPage,$bottom = false){
	if($pages<2){return '';}
	$html = '<nav class="cg-participants-pagination cg_further_images_container'.($bottom ? ' cg_further_images_container_bottom cg_compact_bottom_pager' : '').'" aria-label="'.esc_attr(cg_member_t('Participants')).'">';
	if(!$bottom){
		$html .= '<div class="cg_further_images_select_div"><select class="cg_hover_effect cg_further_images_select cg-participants-page-select" aria-label="'.esc_attr(cg_member_t('Select page')).'">';
		for($i=1;$i<=$pages;$i++){$html .= '<option value="'.$i.'"'.selected($page,$i,false).'>'.$i.'</option>';}
		$html .= '</select></div>';
	}
	$html .= '<div class="cg_hover_effect cg_further_images_select_nav_div"><button type="button" data-cg-participants-page="'.($page-1).'" aria-label="'.esc_attr(cg_member_t('Previous page')).'"'.($page===1 ? ' disabled' : '').'><span class="cg_further_images_select_nav_prev'.($page===1 ? ' cg_further_images_select_nav_none' : '').'"></span></button>';
	$html .= '<div class="cg_further_images_select_nav_current">'.(($page-1)*$perPage+1).'-'.min($page*$perPage,$total).'</div><div class="cg_further_images_select_nav_of_language">'.esc_html(cg_member_t('of')).'</div><div class="cg_further_images_select_nav_total">'.$total.'</div>';
	return $html.'<button type="button" data-cg-participants-page="'.($page+1).'" aria-label="'.esc_attr(cg_member_t('Next page')).'"'.($page===$pages ? ' disabled' : '').'><span class="cg_further_images_select_nav_next'.($page===$pages ? ' cg_further_images_select_nav_none' : '').'"></span></button></div></nav>';
}

function cg_participants_metrics($user,$options,$shortcode,$card = false){
	$totals = array('entries'=>$user['count']);
	if(cg_page_stats_votes_visible($options,$shortcode)){$totals['votes'] = $user['votes'];}
	if(!empty($options['general']['AllowComments'])){$totals['comments'] = $user['comments'];}
	ob_start();
	cg_page_render_statistics($totals,'',$options,$card);
	return ob_get_clean();
}

function cg_participants_ajax_list(){
	$context = cg_participants_request_context();
	$options = cg_member_options($context['realGid'],$context['shortcodeName']);
	wp_send_json_success(cg_participants_list_data($context,$options,wp_unslash($_POST)));
}

function cg_participants_list_data($context,$options,$request){
	$users = cg_participants_collect($context['realGid'],$context['shortcodeName'],$options);
	$participantCount = count($users);
	$search = isset($request['search']) && is_string($request['search']) ? mb_substr(sanitize_text_field($request['search']),0,200) : '';
	if($search!==''){
		$needle = mb_strtolower(remove_accents($search));
		foreach($users as $id=>$user){
			$text = $user['name'];
			foreach(cg_public_profile_items($id) as $field){$text .= ' '.$field['title'].' '.$field['value'];}
			if(mb_strpos(mb_strtolower(remove_accents($text)),$needle)===false){unset($users[$id]);}
		}
	}
	$choices = cg_participants_sort_choices($options,$context['shortcodeName']);
	$sort = isset($request['sort']) && is_string($request['sort']) ? sanitize_key($request['sort']) : cg_participants_default_sort($options);
	if(!isset($choices[$sort])){$sort = cg_participants_default_sort($options);}
	if(!isset($choices[$sort])){$sort = 'date-desc';}
	$users = array_values($users);
	usort($users,function($a,$b) use ($sort){
		$parts = explode('-',$sort);$keys = array('date'=>'date','rate'=>'votes','comment'=>'comments','name'=>'name');
		$key = $keys[$parts[0]];
		$result = $key==='name' ? strnatcasecmp($a['name'],$b['name']) : ($a[$key]==$b[$key] ? 0 : ($a[$key]<$b[$key] ? -1 : 1));
		return $result ? ($parts[1]==='desc' ? -$result : $result) : cg_participants_compare($a,$b);
	});
	$perPage = !empty($options['general']['PicsPerSite']) ? max(1,absint($options['general']['PicsPerSite'])) : 20;
	$total = count($users);$pages = max(1,(int)ceil($total/$perPage));
	$page = min($pages,max(1,isset($request['page']) && is_scalar($request['page']) ? absint($request['page']) : 1));
	ob_start();
	echo cg_participants_pagination($page,$pages,$total,$perPage);
	if(!$total){echo '<div class="cg-participants-empty">'.esc_html(cg_member_t($search!=='' ? 'No participants found.' : 'No participants with a profile yet. Published entries with a profile will appear here.')).'</div>';}
	else{
		echo '<div class="cg-participants-grid cg_grid"><div class="cg_grid_sizer"></div><div class="cg_gutter_sizer"></div>';
		foreach(array_slice($users,($page-1)*$perPage,$perPage) as $user){
			$profile = cg_member_profile_data($user['id']);
			echo '<article class="cg-participant-card cg_grid_item" data-cg-wp-user-id="'.absint($user['id']).'"'.cg_public_member_profile_attributes($user['id'],$context,$user['entry']).'><button type="button" class="cg-participant-open" data-cg-profile="'.$user['id'].'" aria-haspopup="dialog"><span class="cg-participant-photo">'.cg_participants_profile_image($profile,true).'</span><span class="cg-participant-name">'.esc_html($profile['name']).'</span></button>'.cg_participants_metrics($user,$options,$context['shortcodeName'],true).cg_public_profile_render_fields($user['id'],2).'<div class="cg-participant-actions"><button type="button" class="cg-participant-open" aria-haspopup="dialog">'.esc_html(cg_member_t('View profile')).'</button><button type="button" class="cg-participant-gallery-open" data-cg-profile="'.$user['id'].'">'.esc_html(cg_member_t('View gallery entries')).'</button></div></article>';
		}
		echo '</div>';
	}
	echo cg_participants_pagination($page,$pages,$total,$perPage,true);
	return array('html'=>ob_get_clean(),'controlsHtml'=>cg_participants_controls($choices,$sort,$search),'page'=>$page,'pages'=>$pages,'total'=>$total,'participantCount'=>$participantCount,'perPage'=>$perPage,'sort'=>$sort,'search'=>$search);
}
add_action('wp_ajax_cg_participants','cg_participants_ajax_list');
add_action('wp_ajax_nopriv_cg_participants','cg_participants_ajax_list');

function cg_participants_ajax_gallery(){
	$context = cg_participants_request_context();
	$data = cg_participants_profile_data($context,wp_unslash($_POST));
	if(!empty($data['unavailable'])){wp_send_json_error($data);}
	wp_send_json_success($data);
}

// Shared by the initial page response and subsequent participant navigation.
function cg_participants_profile_data($context,$request){
	$userId = isset($request['userId']) && is_scalar($request['userId']) ? absint($request['userId']) : 0;
	$entries = $userId ? cg_participants_rows($context['realGid'],$context['shortcodeName'],$userId) : array();
	if(!$entries){return array('unavailable'=>true,'message'=>cg_member_t('This profile is no longer available in this gallery.'));}
	$profile = cg_member_profile_data($userId);
	$profileOptions = cg_member_options($context['realGid'],$context['shortcodeName']);
	$profileStats = cg_participants_collect($context['realGid'],$context['shortcodeName'],$profileOptions,$userId);
	$profileStatsHtml = cg_participants_metrics($profileStats[$userId],$profileOptions,$context['shortcodeName']);
	$profileViewLabel = cg_member_t('Gallery entries');
	$profileHtml = '<nav class="cg-participant-breadcrumb" aria-label="'.esc_attr(cg_member_t('Gallery view')).'"><button type="button" class="cg-participant-list-link">'.esc_html(cg_member_t('Participants')).'</button><span aria-hidden="true">&#8250;</span><span aria-current="page">'.esc_html(cg_member_t($profileViewLabel)).'</span></nav>';
	$profileHtml .= '<section class="cg-participant-profile" data-cg-wp-user-id="'.absint($userId).'"'.cg_public_member_profile_attributes($userId,$context,key($entries)).'><div class="cg-participant-profile-title"><div class="cg-participant-photo">'.cg_participants_profile_image($profile).'</div><h2 tabindex="-1">'.esc_html($profile['name']).'</h2></div>'.$profileStatsHtml.cg_public_profile_render_fields($userId).'<div class="cg-participant-actions"><button type="button" class="cg-participant-open" aria-haspopup="dialog">'.esc_html(cg_member_t('View profile')).'</button></div></section>';
	$galeryID = absint($context['realGid']);
	$cgParticipantUserId = $userId;
	$cgParticipantListPage = max(1,isset($request['listPage']) && is_scalar($request['listPage']) ? absint($request['listPage']) : 1);
	$cgMemberPublicInstance = $context['gid'];
	$cgMemberPublicShortcode = $context['shortcodeName'];
	$cgMemberInstanceId = $galeryID.($context['shortcodeName']==='cg_gallery_no_voting' ? '-nv' : '').'-ext-profile-'.$userId;
	$shortcode_name = $context['shortcodeName'];
	$entryId = 0;$isCgRuntimeFresh = true;$isCGalleries = false;
	$page = max(1,isset($request['page']) && is_scalar($request['page']) ? absint($request['page']) : 1);
	$galleryOptions = cg_member_options($galeryID,$shortcode_name);
	$perPage = !empty($galleryOptions['general']['PicsPerSite']) ? max(1,absint($galleryOptions['general']['PicsPerSite'])) : 20;
	$page = min($page,max(1,(int)ceil(count($entries)/$perPage)));
	$previousPage = get_query_var('cgl_page');
	set_query_var('cgl_page',$page);
	$wp_upload_dir = wp_upload_dir();
	$options = cg_member_replacement_source($galeryID);
	$frontend_gallery = '';
	$previousRendering = !empty($GLOBALS['cgParticipantRendering']);
	$GLOBALS['cgParticipantRendering'] = true;
	include(__DIR__.'/../../v10/include-scripts-v10.php');
	$GLOBALS['cgParticipantRendering'] = $previousRendering;
	set_query_var('cgl_page',$previousPage);
	return array('html'=>$frontend_gallery,'profileHtml'=>$profileHtml,'gid'=>$cgMemberInstanceId,'userId'=>$userId,'page'=>$page);
}
add_action('wp_ajax_cg_participant_gallery','cg_participants_ajax_gallery');
add_action('wp_ajax_nopriv_cg_participant_gallery','cg_participants_ajax_gallery');

function cg_participants_entry_link($gid,$shortcode,$owner,$backUrl){
	if(!$backUrl || !$owner || !in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true) || !cg_member_enabled($gid,$shortcode)){return '';}
	// Reuse the resolved Back to gallery URL, including origin, pagination and passthrough args.
	$url = remove_query_arg(array('cgl_view','cgl_profile','cgl_profile_page'),$backUrl);
	$profileUrl = add_query_arg(array('cgl_view'=>'profile','cgl_profile'=>absint($owner)),$backUrl);
	$participantsUrl = add_query_arg('cgl_view','participants',$url);
	// Direct page navigation starts at the top of the server-rendered destination.
	$profileUrl = preg_replace('/#.*$/','',$profileUrl);
	$participantsUrl = preg_replace('/#.*$/','',$participantsUrl);
	$profile = cg_member_profile_data($owner);
	$profileName = isset($profile['name']) ? trim($profile['name']) : '';
	$profileLabel = cg_member_t('View public profile').($profileName!=='' ? ' · '.$profileName : '');
	$avatar = '<img class="cg-member-avatar skip-lazy" data-skip-lazy="1" loading="eager"'.(!empty($profile['image']) ? ' src="'.esc_url($profile['image']).'"' : ' hidden').' alt="">';
	$count = cg_participants_count($gid,$shortcode);
	return '<nav class="cg-page-hero-links"><a class="cg-page-profile-link" data-cg-participant-view="profile" data-cg-participant-id="'.absint($owner).'" href="'.esc_url($profileUrl).'" title="'.esc_attr($profileLabel).'">'.$avatar.'<span class="cg-member-name">'.esc_html($profileLabel).'</span></a><a class="cg-page-participants-link" data-cg-participant-view="participants" href="'.esc_url($participantsUrl).'">'.esc_html(cg_member_t('Participants')).' <span class="cg-member-count">'.absint($count).'</span></a></nav>';
}

function cg_participants_navigation_args($source = null){
	$source = is_array($source) ? $source : $_GET;
	if(empty($source['cgl_view']) || !is_string($source['cgl_view']) || !in_array($source['cgl_view'],array('participants','profile'),true)){return array();}
	$args = array('cgl_view'=>$source['cgl_view']);
	foreach(array('cgl_profile','cgl_participants_page','cgl_profile_page') as $key){if(isset($source[$key]) && is_scalar($source[$key]) && absint($source[$key])){$args[$key] = absint($source[$key]);}}
	foreach(array('cgl_participants_search','cgl_participants_sort') as $key){if(isset($source[$key]) && is_string($source[$key])){$args[$key] = mb_substr(sanitize_text_field(wp_unslash($source[$key])),0,200);}}
	return $args;
}

// Only generated gallery landing pages adopt a participant view on the server.
function cg_participants_initial_navigation($gid,$shortcode,$entryId,$isOverview){
	if(empty($GLOBALS['cgPagePresentation']) || !empty($GLOBALS['cgParticipantRendering']) || (defined('DOING_AJAX') && DOING_AJAX) || $entryId || $isOverview){return array();}
	$args = cg_participants_navigation_args();
	$target = isset($_GET['cgl_gallery']) && is_scalar($_GET['cgl_gallery']) ? absint($_GET['cgl_gallery']) : 0;
	if(!$args || ($target && $target!==absint($gid)) || !cg_participants_context(array('realGid'=>$gid,'shortcodeName'=>$shortcode))){return array();}
	return $args;
}
