<?php
if(!defined('ABSPATH')){exit;}

// Header totals follow public display permissions, never private/viewer-only ratings.
if(!function_exists('cg_page_stats_votes_visible')){
function cg_page_stats_votes_visible($options,$shortcode,$overview = false){
	// No-voting/winner display rules apply equally to single pages and overview totals.
	if($shortcode==='cg_gallery_no_voting' || $shortcode==='cg_gallery_winner'){
		$options['general']['HideUntilVote'] = 0;
		$options['general']['ShowOnlyUsersVotes'] = 0;
	}
	return cg1l_is_rating_visible_for_shortcode($shortcode,$options) && empty($options['general']['HideUntilVote']) && empty($options['general']['ShowOnlyUsersVotes']);
}
}

if(!function_exists('cg_page_stats_count')){
function cg_page_stats_count($entries,$comments,$options,$shortcode,$participants = false,$owner = 0,$overview = false){
	$totals = array('entries'=>0);
	if($participants){$totals['participants'] = 0;}
	if(cg_page_stats_votes_visible($options,$shortcode,$overview)){$totals['votes'] = 0;}
	if(!empty($options['general']['AllowComments'])){$totals['comments'] = 0;}
	$users = array();
	foreach($entries as $id=>$entry){
		if(empty($entry['Active']) || (int)$entry['Active']!==1 || !empty($entry['IsProfileImage'])){continue;}
		$user = !empty($entry['WpUserId']) ? absint($entry['WpUserId']) : 0;
		if($owner && $user!==$owner){continue;}
		$totals['entries']++;
		if($participants && $user){$users[$user] = $user;}
		if(isset($totals['votes'])){
			// The public helpers apply manipulation offsets. Sum ratings, not star points.
			$totals['votes'] += cg1l_get_rating_type($options)==='one-star'
				? max(0,cg1l_get_one_star_rating_count($entry,$options,array(),true))
				: array_sum(cg1l_get_multi_star_rating_distribution($entry,$options,array(),true));
		}
		if(isset($totals['comments'])){$totals['comments'] += cg1l_count_comments($id,$comments);}
	}
	if($users){$totals['participants'] = count(get_users(array('include'=>array_values($users),'fields'=>'ID')));}
	return $totals;
}
}

if(!function_exists('cg_page_stats_recent')){
function cg_page_stats_recent($gid,$entries,$comments){
	$stats = cg1l_get_recent_json_by_ids($gid,'image-stats-data-last-update','image-stats','image-stats-');
	foreach($entries as $id=>$entry){
		if(!empty($stats[$id])){$entries[$id] = array_merge($entry,$stats[$id]);}
	}
	$recentComments = cg1l_get_recent_json_by_ids($gid,'image-comments-data-last-update','image-comments','image-comments-');
	foreach($recentComments as $id=>$data){$comments[$id] = $data;}
	return array($entries,$comments);
}
}

if(!function_exists('cg_page_stats_overview')){
function cg_page_stats_overview($covers,$shortcode){
	$suffixes = array('cg_gallery'=>'','cg_gallery_user'=>'-u','cg_gallery_no_voting'=>'-nv','cg_gallery_winner'=>'-w','cg_gallery_ecommerce'=>'-ec');
	$totals = array('galleries'=>0,'entries'=>0);
	if(!isset($suffixes[$shortcode])){return $totals;}
	$galleries = array();
	foreach($covers as $cover){
		$gid = !empty($cover['GalleryIdToCheck']) ? absint($cover['GalleryIdToCheck']) : (!empty($cover['gid']) ? absint($cover['gid']) : 0);
		if($gid){$galleries[$gid] = $gid;}
	}
	// Use the same current, allowed membership as the live endpoint. Stale gzip
	// totals would otherwise trigger an immediate random preview refresh on load.
	return cg_page_stats_current(array('isCGalleries'=>true,'galleriesIds'=>array_values($galleries),'shortcodeName'=>$shortcode));
}
}

// Sign the header's original scope, independently of pagination or the currently open view.
if(!function_exists('cg_page_stats_live_attributes')){
function cg_page_stats_live_attributes($args,$owner){
	$gids = array(absint($args['gallery_id']));
	if(!empty($args['is_overview'])){
		$gids = array();
		foreach($args['entries'] as $entry){
			$id = !empty($entry['GalleryIdToCheck']) ? absint($entry['GalleryIdToCheck']) : (!empty($entry['gid']) ? absint($entry['gid']) : 0);
			if($id){$gids[$id] = $id;}
		}
		$gids = array_values($gids);
	}
	if(!$gids || !$gids[0]){return '';}
	$context = array('realGid'=>$gids[0],'gid'=>!empty($args['instance']) ? $args['instance'] : $gids[0],'shortcodeName'=>$args['shortcode'],'entryId'=>$args['entry_id'],'isCGalleries'=>!empty($args['is_overview']),'hasGalleriesIds'=>!empty($args['is_overview']),'galleriesIds'=>$gids,'participantUserId'=>$owner ? $owner : (!empty($args['participant_id']) ? absint($args['participant_id']) : 0));
	$hash = cg1l_get_gallery_data_access_hash($context['realGid'],$context['shortcodeName'],get_current_user_id(),false);
	return ' data-cg-stats-token="'.esc_attr(cg1l_create_runtime_context_token($context)).'" data-cg-stats-access="'.esc_attr($hash).'" data-cg-stats-gids="'.esc_attr(wp_json_encode($gids)).'"';
}
}

// Current database membership excludes deleted/pending entries even during the gzip build delay.
if(!function_exists('cg_page_stats_current_gallery')){
function cg_page_stats_current_gallery($gid,$shortcode,$overview = false){
	// Share a read-only request snapshot between overview cards and their header.
	static $snapshots = array();
	$snapshotKey = absint($gid).':'.$shortcode.':'.(int)$overview.':'.get_current_user_id();
	if(array_key_exists($snapshotKey,$snapshots)){return $snapshots[$snapshotKey];}
	$suffixes = array('cg_gallery'=>'','cg_gallery_no_voting'=>'-nv','cg_gallery_user'=>'-u','cg_gallery_winner'=>'-w','cg_gallery_ecommerce'=>'-ec');
	if(!isset($suffixes[$shortcode])){return false;}
	$uploads = wp_upload_dir();
	$dir = $uploads['basedir'].'/contest-gallery/gallery-id-'.absint($gid).'/json/';
	$base = $dir.absint($gid);
	$source = is_file($base.'-options.json') ? json_decode(file_get_contents($base.'-options.json'),true) : array();
	$key = $gid.$suffixes[$shortcode];
	$options = !empty($source[$key]) ? $source[$key] : (!empty($source[$gid]) ? $source[$gid] : $source);
	$viewer = get_current_user_id();
	if(!$options || (!$viewer && (!empty($options['pro']['RegUserGalleryOnly']) || $shortcode==='cg_gallery_user'))){return false;}
	if(cg_get_version()==='contest-gallery'){unset($source['interval']);}
	$interval = cg_shortcode_interval_check($gid,$source,$shortcode);
	if(empty($interval['shortcodeIsActive'])){return false;}
	global $wpdb;
	$rows = $wpdb->get_results($wpdb->prepare('SELECT id,WpUserId,Active,Category,Winner,EcommerceEntry,IsProfileImage FROM '.$wpdb->prefix.'contest_gal1ery WHERE GalleryID=%d AND Active=1 AND IsProfileImage=0',$gid),ARRAY_A);
	$entries = array();
	foreach($rows as $row){$entries[absint($row['id'])] = $row;}
	$categories = is_file($base.'-categories.json') ? json_decode(file_get_contents($base.'-categories.json'),true) : array();
	$form = is_file($base.'-form-upload.json') ? json_decode(file_get_contents($base.'-form-upload.json'),true) : array();
	$ids = cg1l_frontend_get_allowed_real_ids($entries,$shortcode,$categories,$options,$viewer,is_array($form) ? $form : array());
	$entries = cg1l_frontend_filter_id_keyed_data_by_allowed_ids($entries,$ids);
	$comments = array();
	$votesVisible = cg_page_stats_votes_visible($options,$shortcode,$overview);
	foreach($entries as $id=>$entry){
		// Reuse the normal legacy stats repair if an older entry has no separate stats file yet.
		$stats = $votesVisible ? cg1l_get_entry_stats_data($gid,$id) : array();
		if(!empty($stats[$id]) && is_array($stats[$id])){$entries[$id] = array_merge($stats[$id],$entry);}
		$file = $dir.'image-comments/image-comments-'.$id.'.json';
		if(!empty($options['general']['AllowComments']) && is_file($file)){
			$data = json_decode(file_get_contents($file),true);
			if(is_array($data)){$comments[$id] = $data;}
		}
	}
	$snapshots[$snapshotKey] = array('entries'=>$entries,'comments'=>$comments,'options'=>$options);
	return $snapshots[$snapshotKey];
}
}

if(!function_exists('cg_page_gallery_card_statistics')){
function cg_page_gallery_card_statistics($gid,$shortcode){
	if($shortcode!=='cg_gallery' && $shortcode!=='cg_gallery_no_voting'){return '';}
	$data = cg_page_stats_current_gallery($gid,$shortcode,true);
	if(!$data){return '';}
	$totals = cg_page_stats_count($data['entries'],$data['comments'],$data['options'],$shortcode,cg_member_enabled($gid,$shortcode),0,true);
	ob_start();
	cg_page_render_statistics($totals,'',$data['options'],true);
	return ob_get_clean();
}
}

if(!function_exists('cg_page_stats_current')){
function cg_page_stats_current($context,&$previews = null,$previewLimit = 3,$previewEntryIds = null,$previewGalleryIds = null){
	$previewLimit = max(1,min(24,absint($previewLimit)));
	$overview = !empty($context['isCGalleries']);
	$gids = $overview ? $context['galleriesIds'] : array($context['realGid']);
	$totals = $overview ? array('galleries'=>0,'entries'=>0) : array();
	$countOverviewParticipants = $overview && in_array($context['shortcodeName'],array('cg_gallery','cg_gallery_no_voting'),true);
	$overviewParticipantIds = null;
	foreach($gids as $gid){
		$data = cg_page_stats_current_gallery($gid,$context['shortcodeName'],$overview);
		if(!$data){continue;}
		if($countOverviewParticipants && cg_member_enabled($gid,$context['shortcodeName'])){
			if($overviewParticipantIds===null){$overviewParticipantIds = array();}
			// Reuse visible entries in this header's scope, counting each owner only once.
			foreach($data['entries'] as $entry){
				$userId = !empty($entry['WpUserId']) ? absint($entry['WpUserId']) : 0;
				if($userId){$overviewParticipantIds[$userId] = $userId;}
			}
		}
		$member = !$overview && cg_member_enabled($gid,$context['shortcodeName']);
		$owner = !$overview && !empty($context['participantUserId']) ? absint($context['participantUserId']) : 0;
		$entryId = !$overview ? absint($context['entryId']) : 0;
		// Entry headers only describe a visible participant while membership is enabled.
		if($entryId && (!$member || !$owner || empty($data['entries'][$entryId]) || absint($data['entries'][$entryId]['WpUserId'])!==$owner)){continue;}
		if(is_array($previews) && ($previewGalleryIds===null || isset($previewGalleryIds[$gid]))){
			$previewEntries = $data['entries'];
			if(!$entryId && $owner){
				foreach($previewEntries as $id=>$entry){if(absint($entry['WpUserId'])!==$owner){unset($previewEntries[$id]);}}
			}
			if($previewEntryIds!==null){$previewEntries = array_intersect_key($previewEntries,$previewEntryIds);}
			$previews = array_merge($previews,cg_page_select_previews($previewEntries,$entryId,$member ? $owner : 0,$gid,$previewLimit,$context['shortcodeName']));
		}
		if($entryId && (!$owner || !$member)){
			$data['entries'] = isset($data['entries'][$entryId]) ? array($entryId=>$data['entries'][$entryId]) : array();
			$owner = 0;
		}
		$counts = cg_page_stats_count($data['entries'],$data['comments'],$data['options'],$context['shortcodeName'],!$entryId && $member,$owner,$overview);
		if($entryId && !$owner){unset($counts['entries']);}
		if($overview){$totals['galleries']++;}
		foreach($counts as $metric=>$count){
			if(!isset($totals[$metric])){$totals[$metric] = 0;}
			$totals[$metric] += $count;
		}
	}
	if($overviewParticipantIds!==null){
		$participants = count(cg_galleries_participant_rows($context));
		$totals = array('galleries'=>$totals['galleries'],'entries'=>$totals['entries'],'participants'=>$participants) + $totals;
	}
	if(is_array($previews)){
		$unique = array();
		foreach($previews as $preview){$unique[$preview['url']] = $preview;}
		$previews = array_values($unique);
		shuffle($previews);
		$previews = array_slice($previews,0,$previewLimit);
	}
	return $totals;
}
}

// These optional lists only narrow the signed, server-authorized preview scope.
if(!function_exists('cg_page_preview_requested_ids')){
function cg_page_preview_requested_ids($key){
	if(!isset($_POST[$key])){return null;}
	$values = is_string($_POST[$key]) ? json_decode(wp_unslash($_POST[$key]),true) : null;
	if(!is_array($values)){wp_send_json_error(array(),400);}
	$ids = array();
	foreach($values as $value){
		if((!is_int($value) && !is_string($value)) || !preg_match('/^[0-9]+$/',(string)$value)){wp_send_json_error(array(),400);}
		$id = absint($value);
		if($id){$ids[$id] = true;}
	}
	return $ids;
}
}

if(!function_exists('cg_page_stats_ajax')){
function cg_page_stats_ajax(){
	nocache_headers();
	header('Cache-Control: private, no-store, max-age=0');
	cg1l_ajax_reject_bad_origin();
	cg_check_frontend_nonce();
	$token = isset($_POST['token']) && is_string($_POST['token']) ? wp_unslash($_POST['token']) : '';
	$context = cg1l_verify_runtime_context_token($token);
	$hash = isset($_POST['galleryDataAccessHash']) && is_string($_POST['galleryDataAccessHash']) ? wp_unslash($_POST['galleryDataAccessHash']) : '';
	if(!$context || !cg1l_hash_equals(cg1l_get_gallery_data_access_hash($context['realGid'],$context['shortcodeName'],get_current_user_id(),false),$hash)){
		wp_send_json_error(array(),403);
	}
	$previews = !empty($_POST['include_previews']) ? array() : null;
	$totals = cg_page_stats_current($context,$previews,24,cg_page_preview_requested_ids('preview_entry_ids'),cg_page_preview_requested_ids('preview_gallery_ids'));
	$formatted = array();
	foreach($totals as $metric=>$count){$formatted[$metric] = number_format_i18n($count);}
	$result = array('totals'=>$totals,'formatted'=>$formatted);
	if(is_array($previews)){
		$result['previews'] = array_slice($previews,0,3);
		$result['preview_pool'] = $previews;
	}
	wp_send_json_success($result);
}
}
add_action('wp_ajax_cg_page_statistics','cg_page_stats_ajax');
add_action('wp_ajax_nopriv_cg_page_statistics','cg_page_stats_ajax');

if(!function_exists('cg_page_render_statistics')){
function cg_page_render_statistics($totals,$scope = '',$options = array(),$card = false){
	if(!$totals){return;}
	$labels = array('galleries'=>'Galleries','entries'=>'Entries','participants'=>'Participants','votes'=>'Votes','comments'=>'Comments');
	$icons = array(
		'galleries'=>'<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
		'entries'=>'<rect x="3" y="3" width="15" height="15" rx="3"/><path d="m3 13 4-4 6 6 3-3 2 2M8 21h10a3 3 0 0 0 3-3V8"/><circle cx="13" cy="7" r="1"/>',
		'participants'=>'<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6m3 10v-3a6 6 0 0 0-2-4"/>',
		'votes'=>'<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9Z"/>',
		'comments'=>'<path d="M21 11a8 8 0 0 1-8 8H8l-5 3V7a4 4 0 0 1 4-4h6a8 8 0 0 1 8 8Z"/><path d="M7 8h9M7 12h6"/>'
	);
	if(!empty($options['visual']['FeVotingIconType']) && $options['visual']['FeVotingIconType']==='heart'){
		$icons['votes'] = '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21l8.84-8.61a5.5 5.5 0 0 0 0-7.78Z"/>';
	}
	echo '<div class="cg-page-statistics'.($card ? ' cg-card-statistics' : '').'">';
	if($scope!==''){echo '<span class="cg-page-stats-scope">'.esc_html($scope).'</span>';}
	echo '<dl class="cg-page-stats">';
	foreach($totals as $metric=>$count){
		if(!isset($labels[$metric])){continue;}
		echo '<div class="cg-page-stat cg-page-stat-'.esc_attr($metric).'" data-cg-stat="'.esc_attr($metric).'"><dt><span class="cg-page-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'.$icons[$metric].'</svg></span><span class="cg-page-stat-label">'.esc_html(cg_member_t($labels[$metric])).'</span></dt><dd>'.esc_html(number_format_i18n($count)).'</dd></div>';
	}
	echo '</dl></div>';
}
}
