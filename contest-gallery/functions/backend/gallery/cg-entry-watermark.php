<?php

if(!defined('ABSPATH')){
	exit;
}

if(!function_exists('cg_entry_watermark_meta_key')){
	function cg_entry_watermark_meta_key(){
		return 'contest_gallery_watermark';
	}
}

if(!function_exists('cg_entry_watermark_url_version_meta_key')){
	function cg_entry_watermark_url_version_meta_key(){
		return 'contest_gallery_watermark_url_version';
	}
}

if(!function_exists('cg_entry_watermark_create_cache_buster')){
	function cg_entry_watermark_create_cache_buster(){
		return time();
	}
}

if(!function_exists('cg_entry_watermark_set_url_version')){
	function cg_entry_watermark_set_url_version($WpUpload){
		$WpUpload = absint($WpUpload);
		if(empty($WpUpload)){
			return 0;
		}

		$cacheBuster = cg_entry_watermark_create_cache_buster();
		$lastCacheBuster = absint(get_post_meta($WpUpload, cg_entry_watermark_url_version_meta_key(), true));
		if($lastCacheBuster >= $cacheBuster){
			$cacheBuster = $lastCacheBuster + 1;
		}
		update_post_meta($WpUpload, cg_entry_watermark_url_version_meta_key(), $cacheBuster);

		return $cacheBuster;
	}
}

if(!function_exists('cg_entry_watermark_get_url_version')){
	function cg_entry_watermark_get_url_version($WpUpload){
		$WpUpload = absint($WpUpload);
		if(empty($WpUpload)){
			return 0;
		}

		$cacheBuster = get_post_meta($WpUpload, cg_entry_watermark_url_version_meta_key(), true);
		if(empty($cacheBuster)){
			$meta = get_post_meta($WpUpload, cg_entry_watermark_meta_key(), true);
			if(!empty($meta) && is_array($meta) && !empty($meta['cache_buster'])){
				$cacheBuster = $meta['cache_buster'];
			}
		}

		return absint($cacheBuster);
	}
}

if(!function_exists('cg_entry_watermark_append_url_version')){
	function cg_entry_watermark_append_url_version($url, $WpUpload){
		if(empty($url)){
			return $url;
		}

		$cacheBuster = cg_entry_watermark_get_url_version($WpUpload);
		if(empty($cacheBuster)){
			return $url;
		}

		return add_query_arg('time', $cacheBuster, $url);
	}
}

if(!function_exists('cg_entry_watermark_append_url_version_to_multiple_files')){
	function cg_entry_watermark_append_url_version_to_multiple_files($multipleFiles){
		if(empty($multipleFiles) || !is_array($multipleFiles)){
			return $multipleFiles;
		}

		$urlKeys = array('thumbnail','medium','large','full','guid');
		foreach($multipleFiles as $key => $file){
			if(empty($file) || !is_array($file) || empty($file['WpUpload'])){
				continue;
			}
			foreach($urlKeys as $urlKey){
				if(!empty($file[$urlKey])){
					$multipleFiles[$key][$urlKey] = cg_entry_watermark_append_url_version($file[$urlKey], $file['WpUpload']);
				}
			}
		}

		return $multipleFiles;
	}
}

if(!function_exists('cg_entry_watermark_get_cached_url_version')){
	function cg_entry_watermark_get_cached_url_version($WpUpload){
		$WpUpload = absint($WpUpload);
		if(empty($WpUpload)){
			return 0;
		}

		static $cache = array();
		if(!isset($cache[$WpUpload])){
			$cache[$WpUpload] = cg_entry_watermark_get_url_version($WpUpload);
		}

		return absint($cache[$WpUpload]);
	}
}

if(!function_exists('cg_entry_watermark_append_cached_url_version')){
	function cg_entry_watermark_append_cached_url_version($url, $WpUpload){
		if(empty($url)){
			return $url;
		}

		$cacheBuster = cg_entry_watermark_get_cached_url_version($WpUpload);
		if(empty($cacheBuster)){
			return $url;
		}

		return add_query_arg('time', $cacheBuster, $url);
	}
}

if(!function_exists('cg_entry_watermark_prepare_attachment_for_js')){
	function cg_entry_watermark_prepare_attachment_for_js($response, $attachment, $meta){
		if(empty($response) || !is_array($response)){
			return $response;
		}

		if(is_object($attachment) && !empty($attachment->ID)){
			$WpUpload = absint($attachment->ID);
		}else{
			$WpUpload = !empty($response['id']) ? absint($response['id']) : 0;
		}

		if(empty($WpUpload) || empty(cg_entry_watermark_get_cached_url_version($WpUpload))){
			return $response;
		}

		if(!empty($response['url'])){
			$response['url'] = cg_entry_watermark_append_cached_url_version($response['url'], $WpUpload);
		}

		if(!empty($response['sizes']) && is_array($response['sizes'])){
			foreach($response['sizes'] as $sizeKey => $size){
				if(!empty($size) && is_array($size) && !empty($size['url'])){
					$response['sizes'][$sizeKey]['url'] = cg_entry_watermark_append_cached_url_version($size['url'], $WpUpload);
				}
			}
		}

		return $response;
	}
}

if(!function_exists('cg_entry_watermark_supported_type')){
	function cg_entry_watermark_supported_type($type){
		$type = strtolower(trim($type));
		return in_array($type, array('jpg','jpeg','png','gif'), true);
	}
}

if(!function_exists('cg_entry_watermark_supported_types_text')){
	function cg_entry_watermark_supported_types_text(){
		return 'JPG, PNG or GIF';
	}
}

if(!function_exists('cg_entry_watermark_type_not_supported_message')){
	function cg_entry_watermark_type_not_supported_message(){
		return 'Only ' . cg_entry_watermark_supported_types_text() . ' images can be watermarked.';
	}
}

if(!function_exists('cg_entry_watermark_get_type_label')){
	function cg_entry_watermark_get_type_label($type){
		$type = strtolower(trim($type));
		if($type === 'jpg' || $type === 'jpeg'){
			return 'JPG';
		}
		if($type === 'png'){
			return 'PNG';
		}
		if($type === 'gif'){
			return 'GIF';
		}
		return strtoupper($type);
	}
}

if(!function_exists('cg_entry_watermark_gd_base_supported')){
	function cg_entry_watermark_gd_base_supported(){
		$requiredFunctions = array(
			'imagesx',
			'imagesy',
			'imagecolorallocatealpha',
			'imagestring',
			'imagefontwidth',
			'imagefontheight',
			'imagedestroy'
		);

		foreach($requiredFunctions as $requiredFunction){
			if(!function_exists($requiredFunction)){
				return false;
			}
		}

		return true;
	}
}

if(!function_exists('cg_entry_watermark_type_gd_supported')){
	function cg_entry_watermark_type_gd_supported($type){
		$type = strtolower(trim($type));
		if(!cg_entry_watermark_supported_type($type) || !cg_entry_watermark_gd_base_supported()){
			return false;
		}

		if($type === 'jpg' || $type === 'jpeg'){
			return (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) ? true : false;
		}

		if($type === 'png'){
			return (function_exists('imagecreatefrompng') && function_exists('imagepng') && function_exists('imagealphablending') && function_exists('imagesavealpha')) ? true : false;
		}

		if($type === 'gif'){
			return (function_exists('imagecreatefromgif') && function_exists('imagegif')) ? true : false;
		}

		return false;
	}
}

if(!function_exists('cg_entry_watermark_gd_unsupported_message')){
	function cg_entry_watermark_gd_unsupported_message($type = ''){
		if(!cg_entry_watermark_gd_base_supported()){
			return 'PHP GD image library is not available. Real watermarking requires GD image support on this server.';
		}

		return 'PHP GD image support for ' . cg_entry_watermark_get_type_label($type) . ' is not available on this server.';
	}
}

if(!function_exists('cg_entry_watermark_gd_unsupported_status')){
	function cg_entry_watermark_gd_unsupported_status($type = ''){
		if(!cg_entry_watermark_gd_base_supported()){
			return 'PHP GD missing';
		}

		return cg_entry_watermark_get_type_label($type) . ' not supported by server';
	}
}

if(!function_exists('cg_entry_watermark_is_pro_version')){
	function cg_entry_watermark_is_pro_version(){
		return (function_exists('cg_get_version') && cg_get_version()==='contest-gallery-pro') ? true : false;
	}
}

if(!function_exists('cg_entry_watermark_default_settings')){
	function cg_entry_watermark_default_settings(){
		$size = cg_entry_watermark_is_pro_version() ? '64' : '512';
		return array(
			'WatermarkTitle' => 'Contest Gallery',
			'WatermarkPosition' => 'center',
			'WatermarkSize' => $size
		);
	}
}

if(!function_exists('cg_entry_watermark_sanitize_settings')){
	function cg_entry_watermark_sanitize_settings($settings){
		$default = cg_entry_watermark_default_settings();
		if(!is_array($settings)){
			$settings = array();
		}

		$title = isset($settings['WatermarkTitle']) ? sanitize_text_field(wp_unslash($settings['WatermarkTitle'])) : $default['WatermarkTitle'];
		$title = trim($title);
		if($title === ''){
			$title = $default['WatermarkTitle'];
		}
		if(function_exists('mb_substr')){
			$title = mb_substr($title, 0, 256);
		}else{
			$title = substr($title, 0, 256);
		}

		$positions = array('center','upperLeft','upperRight','lowerRight','lowerLeft');
		$position = isset($settings['WatermarkPosition']) ? sanitize_text_field(wp_unslash($settings['WatermarkPosition'])) : $default['WatermarkPosition'];
		if(!in_array($position, $positions, true)){
			$position = $default['WatermarkPosition'];
		}

		$sizes = array('8','16','32','64','128','256','512');
		$size = isset($settings['WatermarkSize']) ? sanitize_text_field(wp_unslash($settings['WatermarkSize'])) : $default['WatermarkSize'];
		if(!in_array($size, $sizes, true)){
			$size = $default['WatermarkSize'];
		}

		if(!cg_entry_watermark_is_pro_version()){
			$position = 'center';
			if($size !== '256'){
				$size = '512';
			}
		}

		return array(
			'WatermarkTitle' => $title,
			'WatermarkPosition' => $position,
			'WatermarkSize' => $size
		);
	}
}

if(!function_exists('cg_entry_watermark_settings_equal')){
	function cg_entry_watermark_settings_equal($settingsA, $settingsB){
		$settingsA = cg_entry_watermark_sanitize_settings($settingsA);
		$settingsB = cg_entry_watermark_sanitize_settings($settingsB);

		return (
			$settingsA['WatermarkTitle'] === $settingsB['WatermarkTitle'] &&
			$settingsA['WatermarkPosition'] === $settingsB['WatermarkPosition'] &&
			$settingsA['WatermarkSize'] === $settingsB['WatermarkSize']
		);
	}
}

if(!function_exists('cg_entry_watermark_render_version')){
	function cg_entry_watermark_render_version(){
		return 2;
	}
}

if(!function_exists('cg_entry_watermark_meta_render_version_current')){
	function cg_entry_watermark_meta_render_version_current($meta){
		return (!empty($meta) && is_array($meta) && !empty($meta['render_version']) && absint($meta['render_version']) >= cg_entry_watermark_render_version()) ? true : false;
	}
}

if(!function_exists('cg_entry_watermark_meta_needs_apply')){
	function cg_entry_watermark_meta_needs_apply($meta, $settings){
		if(empty($meta) || !is_array($meta)){
			return true;
		}

		if(empty($meta['settings']) || !cg_entry_watermark_settings_equal($meta['settings'], $settings)){
			return true;
		}

		return cg_entry_watermark_meta_render_version_current($meta) ? false : true;
	}
}

if(!function_exists('cg_entry_watermark_get_attachment_type')){
	function cg_entry_watermark_get_attachment_type($WpUpload, $fallbackType){
		$type = strtolower(trim($fallbackType));
		if($type === '' || $type === 'image'){
			$path = get_attached_file($WpUpload);
			if($path){
				$type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			}
		}
		return $type;
	}
}

if(!function_exists('cg_entry_watermark_get_image_url')){
	function cg_entry_watermark_get_image_url($WpUpload, $size){
		$url = '';
		$image = wp_get_attachment_image_src($WpUpload, $size);
		if(!empty($image[0])){
			$url = $image[0];
		}elseif($size === 'full'){
			$url = wp_get_attachment_url($WpUpload);
		}
		return cg_entry_watermark_append_url_version($url, $WpUpload);
	}
}

if(!function_exists('cg_entry_watermark_is_ecommerce_locked')){
	function cg_entry_watermark_is_ecommerce_locked($WpUpload){
		$WpUpload = absint($WpUpload);
		if(empty($WpUpload)){
			return true;
		}

		if(get_post_meta($WpUpload, 'contest_gallery_ecommerce_sale', true)){
			return true;
		}

		if(get_post_type($WpUpload) === 'contest-gallery-ecom'){
			return true;
		}

		return false;
	}
}

if(!function_exists('cg_entry_watermark_add_file_state')){
	function cg_entry_watermark_add_file_state(&$files, $GalleryID, $realId, $order, $WpUpload, $type, $name, $pdfPreview){
		$WpUpload = absint($WpUpload);
		if(empty($WpUpload) || isset($files[$WpUpload])){
			return;
		}

		$type = cg_entry_watermark_get_attachment_type($WpUpload, $type);
		$typeSupported = cg_entry_watermark_supported_type($type) && empty($pdfPreview);
		$gdSupported = $typeSupported ? cg_entry_watermark_type_gd_supported($type) : false;
		$supported = ($typeSupported && $gdSupported) ? true : false;
		$meta = get_post_meta($WpUpload, cg_entry_watermark_meta_key(), true);
		$unsupportedReason = '';
		if(!$typeSupported){
			$unsupportedReason = cg_entry_watermark_supported_types_text() . ' only';
		}elseif(!$gdSupported){
			$unsupportedReason = cg_entry_watermark_gd_unsupported_status($type);
		}
		$settings = cg_entry_watermark_default_settings();
		if(!empty($meta) && is_array($meta) && !empty($meta['settings']) && is_array($meta['settings'])){
			$settings = cg_entry_watermark_sanitize_settings($meta['settings']);
		}

		$files[$WpUpload] = array(
			'GalleryID' => absint($GalleryID),
			'realId' => absint($realId),
			'order' => absint($order),
			'WpUpload' => $WpUpload,
			'type' => $type,
			'name' => $name ? $name : get_the_title($WpUpload),
			'preview_url' => cg_entry_watermark_get_image_url($WpUpload, 'large'),
			'full_url' => cg_entry_watermark_get_image_url($WpUpload, 'full'),
			'supported' => $supported,
			'type_supported' => $typeSupported,
			'gd_supported' => $gdSupported,
			'unsupported_reason' => $unsupportedReason,
			'watermarked' => !empty($meta),
			'can_restore' => !empty($meta),
			'ecommerce_locked' => cg_entry_watermark_is_ecommerce_locked($WpUpload),
			'settings' => $settings
		);
	}
}

if(!function_exists('cg_entry_watermark_get_entry_files')){
	function cg_entry_watermark_get_entry_files($GalleryID, $realId){
		global $wpdb;
		$tablename = $wpdb->prefix . "contest_gal1ery";

		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename WHERE id = %d AND GalleryID = %d", $realId, $GalleryID));
		if(empty($row)){
			return false;
		}

		$files = array();
		$multipleFiles = array();
		if(!empty($row->MultipleFiles) && $row->MultipleFiles !== '""'){
			$unserialized = @unserialize($row->MultipleFiles);
			if(!empty($unserialized) && is_array($unserialized)){
				$multipleFiles = $unserialized;
			}
		}

		if(!empty($multipleFiles)){
			foreach($multipleFiles as $order => $file){
				if(!empty($file['isRealIdSource'])){
					cg_entry_watermark_add_file_state($files, $GalleryID, $realId, $order, $row->WpUpload, $row->ImgType, $row->NamePic, $row->PdfPreview);
				}else{
					$fileWpUpload = !empty($file['WpUpload']) ? $file['WpUpload'] : 0;
					$fileType = !empty($file['ImgType']) ? $file['ImgType'] : '';
					$fileName = !empty($file['NamePic']) ? $file['NamePic'] : '';
					$pdfPreview = !empty($file['PdfPreview']) ? $file['PdfPreview'] : 0;
					cg_entry_watermark_add_file_state($files, $GalleryID, $realId, $order, $fileWpUpload, $fileType, $fileName, $pdfPreview);
				}
			}
		}else{
			cg_entry_watermark_add_file_state($files, $GalleryID, $realId, 1, $row->WpUpload, $row->ImgType, $row->NamePic, $row->PdfPreview);
		}

		return array(
			'row' => $row,
			'files' => $files
		);
	}
}

if(!function_exists('cg_entry_watermark_get_upload_base_dir')){
	function cg_entry_watermark_get_upload_base_dir(){
		$wp_upload_dir = wp_upload_dir();
		return trailingslashit(wp_normalize_path($wp_upload_dir['basedir']));
	}
}

if(!function_exists('cg_entry_watermark_path_has_stream_wrapper')){
	function cg_entry_watermark_path_has_stream_wrapper($path){
		return preg_match('/^[a-z][a-z0-9+.-]*:\/\//i', (string)$path) ? true : false;
	}
}

if(!function_exists('cg_entry_watermark_normalize_path_segments')){
	function cg_entry_watermark_normalize_path_segments($path, $allowAbsolute){
		if(!is_string($path) || $path === '' || strpos($path, "\0") !== false || cg_entry_watermark_path_has_stream_wrapper($path)){
			return '';
		}

		$path = wp_normalize_path($path);
		$isWindowsAbsolute = preg_match('/^[a-zA-Z]:\//', $path) ? true : false;
		$isUnixAbsolute = (isset($path[0]) && $path[0] === '/');
		if(!$allowAbsolute && ($isWindowsAbsolute || $isUnixAbsolute)){
			return '';
		}

		$prefix = '';
		if($isWindowsAbsolute){
			$prefix = substr($path, 0, 3);
			$path = substr($path, 3);
		}elseif($isUnixAbsolute){
			$prefix = '/';
			$path = ltrim($path, '/');
		}

		$parts = explode('/', $path);
		$normalizedParts = array();
		foreach($parts as $part){
			if($part === '' || $part === '.'){
				continue;
			}
			if($part === '..'){
				return '';
			}
			$normalizedParts[] = $part;
		}

		return $prefix . implode('/', $normalizedParts);
	}
}

if(!function_exists('cg_entry_watermark_validate_upload_absolute_path')){
	function cg_entry_watermark_validate_upload_absolute_path($path){
		$path = cg_entry_watermark_normalize_path_segments($path, true);
		if($path === ''){
			return '';
		}

		$basedir = cg_entry_watermark_get_upload_base_dir();
		if(strpos($path, $basedir) !== 0){
			return '';
		}

		return $path;
	}
}

if(!function_exists('cg_entry_watermark_relative_path')){
	function cg_entry_watermark_relative_path($path){
		$path = cg_entry_watermark_validate_upload_absolute_path($path);
		if($path === ''){
			return '';
		}

		$basedir = cg_entry_watermark_get_upload_base_dir();
		return ltrim(substr($path, strlen($basedir)), '/');
	}
}

if(!function_exists('cg_entry_watermark_absolute_path')){
	function cg_entry_watermark_absolute_path($relativePath){
		$relativePath = cg_entry_watermark_normalize_path_segments($relativePath, false);
		if($relativePath === ''){
			return '';
		}

		return cg_entry_watermark_validate_upload_absolute_path(cg_entry_watermark_get_upload_base_dir() . $relativePath);
	}
}

if(!function_exists('cg_entry_watermark_get_protected_preview_source')){
	function cg_entry_watermark_get_protected_preview_source($meta){
		if(empty($meta) || empty($meta['files']) || !is_array($meta['files'])){
			return new WP_Error('cg_watermark_missing_meta', 'Watermark state could not be loaded.');
		}

		$tryFile = function($file){
			if(empty($file['protected_relative'])){
				return false;
			}

			$path = cg_entry_watermark_absolute_path($file['protected_relative']);
			if(!is_file($path) || !is_readable($path)){
				return false;
			}

			$type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			if(!cg_entry_watermark_supported_type($type)){
				return false;
			}

			if($type === 'png'){
				$mime = 'image/png';
			}elseif($type === 'gif'){
				$mime = 'image/gif';
			}else{
				$mime = 'image/jpeg';
			}
			return array(
				'path' => $path,
				'type' => $type,
				'mime' => $mime
			);
		};

		$preferredKeys = array('large','full','original_image','medium_large','medium','thumbnail');
		foreach($preferredKeys as $preferredKey){
			foreach($meta['files'] as $file){
				if(empty($file['key']) || $file['key'] !== $preferredKey){
					continue;
				}
				$source = $tryFile($file);
				if(!empty($source)){
					return $source;
				}
			}
		}

		foreach($meta['files'] as $file){
			$source = $tryFile($file);
			if(!empty($source)){
				return $source;
			}
		}

		return new WP_Error('cg_watermark_missing_original', 'Protected original image source could not be found.');
	}
}

if(!function_exists('cg_entry_watermark_get_protected_folder')){
	function cg_entry_watermark_get_protected_folder($GalleryID, $realId, $WpUpload, $token){
		return trailingslashit(cg_entry_watermark_get_protected_base_folder()) . 'wp-upload-id-' . absint($WpUpload) . '-' . sanitize_key($token);
	}
}

if(!function_exists('cg_entry_watermark_get_protected_base_folder')){
	function cg_entry_watermark_get_protected_base_folder(){
		return cg_entry_watermark_get_upload_base_dir() . 'contest-gallery/watermark-originals';
	}
}

if(!function_exists('cg_entry_watermark_get_nginx_protected_uri')){
	function cg_entry_watermark_get_nginx_protected_uri(){
		if(function_exists('cg_get_upload_baseurl_path')){
			return preg_replace('#/+#', '/', cg_get_upload_baseurl_path() . 'contest-gallery/watermark-originals/');
		}

		$wp_upload_dir = wp_upload_dir();
		$baseUrl = isset($wp_upload_dir['baseurl']) ? $wp_upload_dir['baseurl'] : '';
		$path = parse_url($baseUrl, PHP_URL_PATH);
		if(empty($path)){
			$path = '/wp-content/uploads';
		}
		$path = '/' . trim($path, '/') . '/contest-gallery/watermark-originals/';
		return preg_replace('#/+#', '/', $path);
	}
}

if(!function_exists('cg_entry_watermark_get_nginx_rule')){
	function cg_entry_watermark_get_nginx_rule(){
		if(function_exists('cg_get_nginx_upload_deny_rule')){
			return cg_get_nginx_upload_deny_rule('contest-gallery/watermark-originals');
		}

		return "location ^~ " . cg_entry_watermark_get_nginx_protected_uri() . " {\n    deny all;\n}";
	}
}

if(!function_exists('cg_entry_watermark_can_chmod')){
	function cg_entry_watermark_can_chmod($path){
		if(empty($path) || !is_string($path) || !is_file($path)){
			return false;
		}

		if(!function_exists('chmod')){
			return false;
		}

		if(!is_writable($path)){
			return false;
		}

		if(function_exists('posix_geteuid')){
			$fileOwner = @fileowner($path);
			$currentOwner = @posix_geteuid();
			if($fileOwner !== false && $currentOwner !== false && intval($fileOwner) !== intval($currentOwner)){
				return false;
			}
		}

		return true;
	}
}

if(!function_exists('cg_entry_watermark_try_chmod')){
	function cg_entry_watermark_try_chmod($path, $mode){
		if(!cg_entry_watermark_can_chmod($path)){
			return false;
		}

		$chmodFailed = false;
		set_error_handler(function() use (&$chmodFailed){
			$chmodFailed = true;
			return true;
		});
		$result = chmod($path, $mode);
		restore_error_handler();

		return ($result && !$chmodFailed) ? true : false;
	}
}

if(!function_exists('cg_entry_watermark_protect_folder')){
	function cg_entry_watermark_protect_folder($folder){
		if(!wp_mkdir_p($folder)){
			return false;
		}

		$htaccessFileContent = <<<HEREDOC
#Do not remove htaccess in this folder. It prevents original watermark sources from being loaded from outside.
<Files "*">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</Files>
HEREDOC;

		$webConfigFileContent = <<<HEREDOC
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
  <system.webServer>
    <security>
      <authorization>
        <remove users="*" roles="" verbs="" />
        <add accessType="Deny" users="*" />
      </authorization>
    </security>
  </system.webServer>
</configuration>
HEREDOC;

		$nginxFileContent = "Nginx does not read protection files from upload folders automatically.\n";
		$nginxFileContent .= "Ask your server admin to add this rule inside the matching server block of the active Nginx configuration and reload Nginx:\n\n";
		$nginxFileContent .= cg_entry_watermark_get_nginx_rule() . "\n";

		$htaccessFile = trailingslashit($folder) . '.htaccess';
		if(file_put_contents($htaccessFile, $htaccessFileContent) === false){
			return false;
		}
		cg_entry_watermark_try_chmod($htaccessFile, 0640);

		$webConfigFile = trailingslashit($folder) . 'web.config';
		if(file_put_contents($webConfigFile, $webConfigFileContent) === false){
			return false;
		}
		cg_entry_watermark_try_chmod($webConfigFile, 0640);

		file_put_contents(trailingslashit($folder) . 'index.html', '');
		file_put_contents(trailingslashit($folder) . 'do-not-remove-htaccess.txt', 'Do not remove htaccess in this folder. It prevents original watermark sources from being loaded from outside.');
		file_put_contents(trailingslashit($folder) . 'nginx-watermark-originals-rule.txt', $nginxFileContent);

		return true;
	}
}

if(!function_exists('cg_entry_watermark_protect_meta_folders')){
	function cg_entry_watermark_protect_meta_folders($meta){
		if(empty($meta) || empty($meta['files']) || !is_array($meta['files'])){
			return true;
		}

		$folders = array(cg_entry_watermark_get_protected_base_folder() => true);
		foreach($meta['files'] as $file){
			if(empty($file['protected_relative'])){
				continue;
			}
			$protectedPath = cg_entry_watermark_absolute_path($file['protected_relative']);
			if($protectedPath === ''){
				return false;
			}
			$folders[dirname($protectedPath)] = true;
		}

		foreach($folders as $folder => $unused){
			if(!cg_entry_watermark_protect_folder($folder)){
				return false;
			}
		}

		return true;
	}
}

if(!function_exists('cg_entry_watermark_get_public_files')){
	function cg_entry_watermark_get_public_files($WpUpload){
		$attachedFile = cg_entry_watermark_validate_upload_absolute_path(get_attached_file($WpUpload));
		if(empty($attachedFile)){
			return array();
		}

		$files = array();
		$seen = array();
		$baseDir = dirname($attachedFile);

		$addFile = function($path, $key) use (&$files, &$seen) {
			$path = cg_entry_watermark_validate_upload_absolute_path($path);
			if(empty($path) || !is_file($path) || isset($seen[$path])){
				return;
			}
			$seen[$path] = true;
			$files[] = array(
				'key' => $key,
				'public_path' => $path
			);
		};

		$addFile($attachedFile, 'full');

		$metadata = wp_get_attachment_metadata($WpUpload);
		if(!empty($metadata) && is_array($metadata)){
			if(!empty($metadata['sizes']) && is_array($metadata['sizes'])){
				foreach($metadata['sizes'] as $sizeKey => $sizeData){
					if(!empty($sizeData['file'])){
						$addFile($baseDir . '/' . $sizeData['file'], $sizeKey);
					}
				}
			}
			if(!empty($metadata['original_image'])){
				$addFile($baseDir . '/' . $metadata['original_image'], 'original_image');
			}
		}

		return $files;
	}
}

if(!function_exists('cg_entry_watermark_get_font')){
	function cg_entry_watermark_get_font(){
		$candidates = array(
			ABSPATH . 'wp-includes/fonts/NotoSans-Regular.ttf',
			ABSPATH . 'wp-includes/fonts/NotoSerif-Regular.ttf',
			'/usr/share/fonts/truetype/noto/NotoSans-Regular.ttf',
			'/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
			'/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf'
		);

		foreach($candidates as $candidate){
			if(is_file($candidate) && is_readable($candidate)){
				return $candidate;
			}
		}

		return '';
	}
}

if(!function_exists('cg_entry_watermark_is_gd_image')){
	function cg_entry_watermark_is_gd_image($image){
		return is_resource($image) || (is_object($image) && get_class($image) === 'GdImage');
	}
}

if(!function_exists('cg_entry_watermark_create_image_resource')){
	function cg_entry_watermark_create_image_resource($path, $type){
		$type = strtolower($type);
		if($type === 'jpg' || $type === 'jpeg'){
			return @imagecreatefromjpeg($path);
		}
		if($type === 'png'){
			$image = @imagecreatefrompng($path);
			if(cg_entry_watermark_is_gd_image($image)){
				imagealphablending($image, true);
				imagesavealpha($image, true);
			}
			return $image;
		}
		if($type === 'gif'){
			return @imagecreatefromgif($path);
		}
		return false;
	}
}

if(!function_exists('cg_entry_watermark_save_image_resource')){
	function cg_entry_watermark_save_image_resource($image, $path, $type){
		$type = strtolower($type);
		if($type === 'jpg' || $type === 'jpeg'){
			return imagejpeg($image, $path, 90);
		}
		if($type === 'png'){
			imagesavealpha($image, true);
			return imagepng($image, $path, 6);
		}
		if($type === 'gif'){
			return imagegif($image, $path);
		}
		return false;
	}
}

if(!function_exists('cg_entry_watermark_get_image_dimensions')){
	function cg_entry_watermark_get_image_dimensions($path){
		if(empty($path) || !is_file($path)){
			return array(
				'width' => 0,
				'height' => 0
			);
		}

		$size = @getimagesize($path);
		if(empty($size[0]) || empty($size[1])){
			return array(
				'width' => 0,
				'height' => 0
			);
		}

		return array(
			'width' => absint($size[0]),
			'height' => absint($size[1])
		);
	}
}

if(!function_exists('cg_entry_watermark_get_public_reference_path')){
	function cg_entry_watermark_get_public_reference_path($publicFiles){
		if(empty($publicFiles) || !is_array($publicFiles)){
			return '';
		}

		$fallbackPath = '';
		foreach($publicFiles as $file){
			if(empty($file['public_path'])){
				continue;
			}
			if($fallbackPath === ''){
				$fallbackPath = $file['public_path'];
			}
			if(!empty($file['key']) && $file['key'] === 'full'){
				return $file['public_path'];
			}
		}

		return $fallbackPath;
	}
}

if(!function_exists('cg_entry_watermark_get_meta_reference_path')){
	function cg_entry_watermark_get_meta_reference_path($meta){
		if(empty($meta) || empty($meta['files']) || !is_array($meta['files'])){
			return '';
		}

		$fallbackPath = '';
		foreach($meta['files'] as $file){
			if(empty($file['protected_relative'])){
				continue;
			}
			$protectedPath = cg_entry_watermark_absolute_path($file['protected_relative']);
			if($protectedPath === '' || !is_file($protectedPath)){
				continue;
			}
			if($fallbackPath === ''){
				$fallbackPath = $protectedPath;
			}
			if(!empty($file['key']) && $file['key'] === 'full'){
				return $protectedPath;
			}
		}

		return $fallbackPath;
	}
}

if(!function_exists('cg_entry_watermark_create_render_context')){
	function cg_entry_watermark_create_render_context($referencePath){
		$dimensions = cg_entry_watermark_get_image_dimensions($referencePath);
		return array(
			'reference_width' => $dimensions['width'],
			'reference_height' => $dimensions['height']
		);
	}
}

if(!function_exists('cg_entry_watermark_get_render_font_size')){
	function cg_entry_watermark_get_render_font_size($settings, $width, $height, $renderContext = array()){
		$selectedFontSize = absint($settings['WatermarkSize']);
		$selectedFontSize = max(8, min(512, $selectedFontSize));
		$fontSize = $selectedFontSize;

		$referenceWidth = (!empty($renderContext['reference_width'])) ? absint($renderContext['reference_width']) : 0;
		$referenceHeight = (!empty($renderContext['reference_height'])) ? absint($renderContext['reference_height']) : 0;
		if($referenceWidth > 0 && $referenceHeight > 0 && $width > 0 && $height > 0){
			$ratioWidth = $width / $referenceWidth;
			$ratioHeight = $height / $referenceHeight;
			$ratio = min($ratioWidth, $ratioHeight);
			if($ratio > 0 && $ratio < 1){
				$fontSize = (int)round($selectedFontSize * $ratio);
			}
		}

		return max(8, min($selectedFontSize, $fontSize));
	}
}

if(!function_exists('cg_entry_watermark_apply_text')){
	function cg_entry_watermark_apply_text($image, $settings, $renderContext = array()){
		$width = imagesx($image);
		$height = imagesy($image);
		$text = $settings['WatermarkTitle'];
		$position = $settings['WatermarkPosition'];
		$font = cg_entry_watermark_get_font();
		$fontSize = cg_entry_watermark_get_render_font_size($settings, $width, $height, $renderContext);
		$margin = max(10, floor($fontSize * 0.08));
		$color = imagecolorallocatealpha($image, 255, 255, 255, 55);

		if($font && function_exists('imagettfbbox') && function_exists('imagettftext')){
			$bbox = imagettfbbox($fontSize, 0, $font, $text);

			if($bbox){
				$minX = min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
				$maxX = max($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
				$minY = min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
				$maxY = max($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
				$textWidth = $maxX - $minX;
				$textHeight = $maxY - $minY;

				if($position === 'upperLeft'){
					$left = $margin;
					$top = $margin;
				}elseif($position === 'upperRight'){
					$left = $width - $textWidth - $margin;
					$top = $margin;
				}elseif($position === 'lowerRight'){
					$left = $width - $textWidth - $margin;
					$top = $height - $textHeight - $margin;
				}elseif($position === 'lowerLeft'){
					$left = $margin;
					$top = $height - $textHeight - $margin;
				}else{
					$left = floor(($width - $textWidth) / 2);
					$top = floor(($height - $textHeight) / 2);
				}

				$x = $left - $minX;
				$y = $top - $minY;

				imagettftext($image, $fontSize, 0, $x, $y, $color, $font, $text);
				return true;
			}
		}

		$fontId = 5;
		$textWidth = imagefontwidth($fontId) * strlen($text);
		$textHeight = imagefontheight($fontId);
		$left = floor(($width - $textWidth) / 2);
		$top = floor(($height - $textHeight) / 2);
		if($position === 'upperLeft'){
			$left = $margin;
			$top = $margin;
		}elseif($position === 'upperRight'){
			$left = $width - $textWidth - $margin;
			$top = $margin;
		}elseif($position === 'lowerRight'){
			$left = $width - $textWidth - $margin;
			$top = $height - $textHeight - $margin;
		}elseif($position === 'lowerLeft'){
			$left = $margin;
			$top = $height - $textHeight - $margin;
		}
		imagestring($image, $fontId, max(0, $left), max(0, $top), $text, $color);
		return true;
	}
}

if(!function_exists('cg_entry_watermark_apply_to_file')){
	function cg_entry_watermark_apply_to_file($source, $target, $type, $settings, $renderContext = array()){
		if(!cg_entry_watermark_type_gd_supported($type)){
			return false;
		}

		$image = cg_entry_watermark_create_image_resource($source, $type);
		if(!cg_entry_watermark_is_gd_image($image)){
			return false;
		}

		cg_entry_watermark_apply_text($image, $settings, $renderContext);
		$result = cg_entry_watermark_save_image_resource($image, $target, $type);
		imagedestroy($image);
		if($result){
			cg_entry_watermark_try_chmod($target, 0644);
		}

		return $result;
	}
}

if(!function_exists('cg_entry_watermark_cleanup_tmp')){
	function cg_entry_watermark_cleanup_tmp($tmpFiles){
		foreach($tmpFiles as $tmpFile){
			if(is_file($tmpFile)){
				@unlink($tmpFile);
			}
		}
	}
}

if(!function_exists('cg_entry_watermark_restore_moved_files')){
	function cg_entry_watermark_restore_moved_files($movedFiles){
		foreach($movedFiles as $file){
			if(!empty($file['protected_path']) && is_file($file['protected_path'])){
				if(!empty($file['public_path']) && is_file($file['public_path'])){
					@unlink($file['public_path']);
				}
				@rename($file['protected_path'], $file['public_path']);
			}
		}
	}
}

if(!function_exists('cg_entry_watermark_delete_folder')){
	function cg_entry_watermark_delete_folder($folder){
		$wp_upload_dir = wp_upload_dir();
		$allowedBase = wp_normalize_path(trailingslashit($wp_upload_dir['basedir']) . 'contest-gallery/');
		$folder = wp_normalize_path($folder);
		if(empty($folder) || strpos($folder, $allowedBase) !== 0 || !is_dir($folder)){
			return;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach($iterator as $file){
			if($file->isDir()){
				@rmdir($file->getPathname());
			}else{
				@unlink($file->getPathname());
			}
		}
		@rmdir($folder);
	}
}

if(!function_exists('cg_entry_watermark_delete_meta_folder')){
	function cg_entry_watermark_delete_meta_folder($meta){
		if(!is_array($meta)){
			return;
		}

		if(!empty($meta['folder_relative'])){
			$folderPath = cg_entry_watermark_absolute_path($meta['folder_relative']);
			if($folderPath !== ''){
				cg_entry_watermark_delete_folder($folderPath);
				return;
			}
		}

		if(!empty($meta['files']) && is_array($meta['files'])){
			$folders = array();
			foreach($meta['files'] as $file){
				if(!empty($file['protected_relative'])){
					$protectedPath = cg_entry_watermark_absolute_path($file['protected_relative']);
					if($protectedPath === ''){
						continue;
					}
					$folder = dirname($protectedPath);
					if($folder !== ''){
						$folders[$folder] = $folder;
					}
				}
			}
			foreach($folders as $folder){
				cg_entry_watermark_delete_folder($folder);
			}
		}
	}
}

if(!function_exists('cg_entry_watermark_normalize_wp_upload_ids')){
	function cg_entry_watermark_normalize_wp_upload_ids($wpUploadIds){
		if(!is_array($wpUploadIds)){
			$wpUploadIds = array($wpUploadIds);
		}

		$normalized = array();
		foreach($wpUploadIds as $WpUpload){
			$WpUpload = absint($WpUpload);
			if(!empty($WpUpload)){
				$normalized[$WpUpload] = $WpUpload;
			}
		}

		return array_values($normalized);
	}
}

if(!function_exists('cg_entry_watermark_normalize_entry_ids')){
	function cg_entry_watermark_normalize_entry_ids($entryIds, $limit = 50){
		if(!is_array($entryIds)){
			$entryIds = array($entryIds);
		}

		$limit = absint($limit);
		if(empty($limit)){
			$limit = 50;
		}

		$normalized = array();
		foreach($entryIds as $entryId){
			$entryId = absint($entryId);
			if(!empty($entryId)){
				$normalized[$entryId] = $entryId;
			}
			if(count($normalized) >= $limit){
				break;
			}
		}

		return array_values($normalized);
	}
}

if(!function_exists('cg_entry_watermark_collect_wp_upload_ids_from_row')){
	function cg_entry_watermark_collect_wp_upload_ids_from_row($row){
		$wpUploadIds = array();
		if(empty($row)){
			return array();
		}

		$isArray = is_array($row);
		$WpUpload = $isArray && isset($row['WpUpload']) ? $row['WpUpload'] : (isset($row->WpUpload) ? $row->WpUpload : 0);
		$WpUpload = absint($WpUpload);
		if(!empty($WpUpload)){
			$wpUploadIds[$WpUpload] = $WpUpload;
		}

		$multipleFilesRaw = $isArray && isset($row['MultipleFiles']) ? $row['MultipleFiles'] : (isset($row->MultipleFiles) ? $row->MultipleFiles : '');
		$multipleFiles = array();
		if(!empty($multipleFilesRaw) && $multipleFilesRaw !== '""'){
			if(is_array($multipleFilesRaw)){
				$multipleFiles = $multipleFilesRaw;
			}else{
				$unserialized = @unserialize($multipleFilesRaw);
				if(!empty($unserialized) && is_array($unserialized)){
					$multipleFiles = $unserialized;
				}
			}
		}

		if(!empty($multipleFiles)){
			foreach($multipleFiles as $file){
				if(!empty($file['isRealIdSource'])){
					if(!empty($WpUpload)){
						$wpUploadIds[$WpUpload] = $WpUpload;
					}
					continue;
				}

				$fileWpUpload = !empty($file['WpUpload']) ? absint($file['WpUpload']) : 0;
				if(!empty($fileWpUpload)){
					$wpUploadIds[$fileWpUpload] = $fileWpUpload;
				}
			}
		}

		return array_values($wpUploadIds);
	}
}

if(!function_exists('cg_entry_watermark_collect_wp_upload_ids_from_rows')){
	function cg_entry_watermark_collect_wp_upload_ids_from_rows($rows){
		if(empty($rows) || !is_array($rows)){
			return array();
		}

		$wpUploadIds = array();
		foreach($rows as $row){
			$rowWpUploadIds = cg_entry_watermark_collect_wp_upload_ids_from_row($row);
			foreach($rowWpUploadIds as $WpUpload){
				$wpUploadIds[$WpUpload] = $WpUpload;
			}
		}

		return array_values($wpUploadIds);
	}
}

if(!function_exists('cg_entry_watermark_collect_wp_upload_ids_for_gallery')){
	function cg_entry_watermark_collect_wp_upload_ids_for_gallery($GalleryID, $entryIds = array()){
		global $wpdb;
		$tablename = $wpdb->prefix . "contest_gal1ery";
		$GalleryID = absint($GalleryID);
		if(empty($GalleryID)){
			return array();
		}

		$entryIds = cg_entry_watermark_normalize_wp_upload_ids($entryIds);
		$rows = array();

		if(empty($entryIds)){
			$rows = $wpdb->get_results($wpdb->prepare(
				"SELECT id, WpUpload, MultipleFiles FROM $tablename WHERE GalleryID = %d",
				$GalleryID
			));
		}else{
			$chunks = array_chunk($entryIds, 500);
			foreach($chunks as $chunk){
				$placeholders = implode(',', array_fill(0, count($chunk), '%d'));
				$args = array_merge(array($GalleryID), $chunk);
				$chunkRows = $wpdb->get_results($wpdb->prepare(
					"SELECT id, WpUpload, MultipleFiles FROM $tablename WHERE GalleryID = %d AND id IN ($placeholders)",
					$args
				));
				if(!empty($chunkRows)){
					$rows = array_merge($rows, $chunkRows);
				}
			}
		}

		return cg_entry_watermark_collect_wp_upload_ids_from_rows($rows);
	}
}

if(!function_exists('cg_entry_watermark_get_meta_for_wp_upload_ids')){
	function cg_entry_watermark_get_meta_for_wp_upload_ids($wpUploadIds, $chunkSize = 500){
		global $wpdb;
		$wpUploadIds = cg_entry_watermark_normalize_wp_upload_ids($wpUploadIds);
		if(empty($wpUploadIds)){
			return array();
		}

		$chunkSize = absint($chunkSize);
		if(empty($chunkSize)){
			$chunkSize = 500;
		}

		$metas = array();
		$chunks = array_chunk($wpUploadIds, $chunkSize);
		foreach($chunks as $chunk){
			$placeholders = implode(',', array_fill(0, count($chunk), '%d'));
			$args = array_merge(array(cg_entry_watermark_meta_key()), $chunk);
			$rows = $wpdb->get_results($wpdb->prepare(
				"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s AND post_id IN ($placeholders)",
				$args
			));
			foreach($rows as $row){
				$WpUpload = absint($row->post_id);
				if(empty($WpUpload)){
					continue;
				}
				$value = maybe_unserialize($row->meta_value);
				if(!empty($value)){
					$metas[$WpUpload] = $value;
				}
			}
		}

		return $metas;
	}
}

if(!function_exists('cg_entry_watermark_get_watermarked_wp_upload_ids')){
	function cg_entry_watermark_get_watermarked_wp_upload_ids($wpUploadIds){
		$metas = cg_entry_watermark_get_meta_for_wp_upload_ids($wpUploadIds);
		$wpUploadIdsSet = array();
		foreach($metas as $WpUpload => $meta){
			if(!empty($meta)){
				$wpUploadIdsSet[absint($WpUpload)] = true;
			}
		}
		return $wpUploadIdsSet;
	}
}

if(!function_exists('cg_entry_watermark_get_all_meta')){
	function cg_entry_watermark_get_all_meta(){
		global $wpdb;
		$metas = array();
		$rows = $wpdb->get_results($wpdb->prepare(
			"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
			cg_entry_watermark_meta_key()
		));
		foreach($rows as $row){
			$WpUpload = absint($row->post_id);
			if(empty($WpUpload)){
				continue;
			}
			$value = maybe_unserialize($row->meta_value);
			if(!empty($value)){
				$metas[$WpUpload] = $value;
			}
		}
		return $metas;
	}
}

if(!function_exists('cg_entry_watermark_apply_first_time')){
	function cg_entry_watermark_apply_first_time($GalleryID, $realId, $WpUpload, $type, $settings){
		$publicFiles = cg_entry_watermark_get_public_files($WpUpload);
		if(empty($publicFiles)){
			return new WP_Error('cg_watermark_missing_source', 'Original image source could not be found.');
		}

		$token = strtolower(wp_generate_password(20, false, false));
		$folder = cg_entry_watermark_get_protected_folder($GalleryID, $realId, $WpUpload, $token);
		if(!cg_entry_watermark_protect_folder(cg_entry_watermark_get_protected_base_folder()) || !cg_entry_watermark_protect_folder($folder)){
			return new WP_Error('cg_watermark_folder_failed', 'Watermark original folder could not be created.');
		}

		$tmpFiles = array();
		$movedFiles = array();
		$metaFiles = array();
		$counter = 1;
		$renderContext = cg_entry_watermark_create_render_context(cg_entry_watermark_get_public_reference_path($publicFiles));

		foreach($publicFiles as $file){
			$publicPath = $file['public_path'];
			$publicRelative = cg_entry_watermark_relative_path($publicPath);
			if($publicRelative === ''){
				cg_entry_watermark_cleanup_tmp($tmpFiles);
				cg_entry_watermark_delete_folder($folder);
				return new WP_Error('cg_watermark_invalid_path', 'Watermark image file path is invalid.');
			}
			$protectedPath = trailingslashit($folder) . str_pad($counter, 2, '0', STR_PAD_LEFT) . '-' . sanitize_file_name(basename($publicPath));
			$protectedRelative = cg_entry_watermark_relative_path($protectedPath);
			if($protectedRelative === ''){
				cg_entry_watermark_cleanup_tmp($tmpFiles);
				cg_entry_watermark_delete_folder($folder);
				return new WP_Error('cg_watermark_invalid_path', 'Watermark image file path is invalid.');
			}
			$tmpPath = $publicPath . '.cg-watermark-tmp-' . strtolower(wp_generate_password(8, false, false)) . '.' . $type;
			if(!cg_entry_watermark_apply_to_file($publicPath, $tmpPath, $type, $settings, $renderContext)){
				cg_entry_watermark_cleanup_tmp($tmpFiles);
				cg_entry_watermark_delete_folder($folder);
				return new WP_Error('cg_watermark_create_failed', 'Watermarked image file could not be created.');
			}
			$tmpFiles[] = $tmpPath;
			$metaFiles[] = array(
				'key' => $file['key'],
				'public_path' => $publicPath,
				'public_relative' => $publicRelative,
				'protected_path' => $protectedPath,
				'protected_relative' => $protectedRelative,
				'tmp_path' => $tmpPath
			);
			$counter++;
		}

		foreach($metaFiles as $file){
			if(!@rename($file['public_path'], $file['protected_path'])){
				cg_entry_watermark_restore_moved_files($movedFiles);
				cg_entry_watermark_cleanup_tmp($tmpFiles);
				cg_entry_watermark_delete_folder($folder);
				return new WP_Error('cg_watermark_move_failed', 'Original image source could not be protected.');
			}
			cg_entry_watermark_try_chmod($file['protected_path'], 0640);
			$movedFiles[] = $file;
		}

		foreach($metaFiles as $file){
			if(is_file($file['public_path'])){
				@unlink($file['public_path']);
			}
			if(!@rename($file['tmp_path'], $file['public_path'])){
				cg_entry_watermark_restore_moved_files($movedFiles);
				cg_entry_watermark_cleanup_tmp($tmpFiles);
				cg_entry_watermark_delete_folder($folder);
				return new WP_Error('cg_watermark_replace_failed', 'Public image file could not be replaced.');
			}
			cg_entry_watermark_try_chmod($file['public_path'], 0644);
		}

		$metaFilesForStorage = array();
		foreach($metaFiles as $file){
			$metaFilesForStorage[] = array(
				'key' => $file['key'],
				'public_relative' => $file['public_relative'],
				'protected_relative' => $file['protected_relative']
			);
		}

		$now = current_time('mysql');
		$cacheBuster = cg_entry_watermark_set_url_version($WpUpload);
		update_post_meta($WpUpload, cg_entry_watermark_meta_key(), array(
			'version' => 1,
			'render_version' => cg_entry_watermark_render_version(),
			'GalleryID' => absint($GalleryID),
			'realId' => absint($realId),
			'WpUpload' => absint($WpUpload),
			'type' => $type,
			'token' => $token,
			'folder_relative' => cg_entry_watermark_relative_path($folder),
			'files' => $metaFilesForStorage,
			'settings' => $settings,
			'cache_buster' => $cacheBuster,
			'created' => $now,
			'updated' => $now
		));

		return true;
	}
}

if(!function_exists('cg_entry_watermark_apply_from_originals')){
	function cg_entry_watermark_apply_from_originals($WpUpload, $type, $settings){
		$meta = get_post_meta($WpUpload, cg_entry_watermark_meta_key(), true);
		if(empty($meta) || empty($meta['files']) || !is_array($meta['files'])){
			return new WP_Error('cg_watermark_missing_meta', 'Watermark state could not be loaded.');
		}
		if(!cg_entry_watermark_protect_meta_folders($meta)){
			return new WP_Error('cg_watermark_folder_failed', 'Watermark original folder could not be protected.');
		}

		$tmpFiles = array();
		$metaFilePaths = array();
		$renderContext = cg_entry_watermark_create_render_context(cg_entry_watermark_get_meta_reference_path($meta));
		foreach($meta['files'] as $file){
			$publicPath = cg_entry_watermark_absolute_path($file['public_relative']);
			$protectedPath = cg_entry_watermark_absolute_path($file['protected_relative']);
			if($publicPath === '' || $protectedPath === ''){
				cg_entry_watermark_cleanup_tmp($tmpFiles);
				return new WP_Error('cg_watermark_invalid_path', 'Watermark image file path is invalid.');
			}
			if(!is_file($protectedPath)){
				cg_entry_watermark_cleanup_tmp($tmpFiles);
				return new WP_Error('cg_watermark_missing_original', 'Protected original image source could not be found.');
			}
			$tmpPath = $publicPath . '.cg-watermark-tmp-' . strtolower(wp_generate_password(8, false, false)) . '.' . $type;
			if(!cg_entry_watermark_apply_to_file($protectedPath, $tmpPath, $type, $settings, $renderContext)){
				cg_entry_watermark_cleanup_tmp($tmpFiles);
				return new WP_Error('cg_watermark_create_failed', 'Watermarked image file could not be created.');
			}
			$tmpFiles[] = $tmpPath;
			$metaFilePaths[] = array(
				'public_path' => $publicPath,
				'tmp_path' => $tmpPath
			);
		}

		foreach($metaFilePaths as $file){
			$publicPath = $file['public_path'];
			if(is_file($publicPath)){
				@unlink($publicPath);
			}
			if(!@rename($file['tmp_path'], $publicPath)){
				cg_entry_watermark_cleanup_tmp($tmpFiles);
				return new WP_Error('cg_watermark_replace_failed', 'Public image file could not be replaced.');
			}
			cg_entry_watermark_try_chmod($publicPath, 0644);
		}

		$meta['settings'] = $settings;
		$meta['render_version'] = cg_entry_watermark_render_version();
		$meta['cache_buster'] = cg_entry_watermark_set_url_version($WpUpload);
		$meta['updated'] = current_time('mysql');
		update_post_meta($WpUpload, cg_entry_watermark_meta_key(), $meta);

		return true;
	}
}

if(!function_exists('cg_entry_watermark_restore')){
	function cg_entry_watermark_restore($WpUpload){
		$meta = get_post_meta($WpUpload, cg_entry_watermark_meta_key(), true);
		return cg_entry_watermark_restore_from_meta($WpUpload, $meta);
	}
}

if(!function_exists('cg_entry_watermark_restore_from_meta')){
	function cg_entry_watermark_restore_from_meta($WpUpload, $meta){
		$WpUpload = absint($WpUpload);
		if(empty($meta) || empty($meta['files']) || !is_array($meta['files'])){
			return false;
		}

		$metaFilePaths = array();
		foreach($meta['files'] as $file){
			$publicPath = cg_entry_watermark_absolute_path($file['public_relative']);
			$protectedPath = cg_entry_watermark_absolute_path($file['protected_relative']);
			if($publicPath === '' || $protectedPath === ''){
				return new WP_Error('cg_watermark_invalid_path', 'Watermark image file path is invalid.');
			}
			if(!is_file($protectedPath)){
				return new WP_Error('cg_watermark_missing_original', 'Protected original image source could not be found.');
			}
			$metaFilePaths[] = array(
				'public_path' => $publicPath,
				'protected_path' => $protectedPath
			);
		}

		foreach($metaFilePaths as $file){
			$publicPath = $file['public_path'];
			$protectedPath = $file['protected_path'];
			$publicDir = dirname($publicPath);
			if(!is_dir($publicDir)){
				wp_mkdir_p($publicDir);
			}
			if(is_file($publicPath)){
				@unlink($publicPath);
			}
			if(!@rename($protectedPath, $publicPath)){
				return new WP_Error('cg_watermark_restore_failed', 'Original image source could not be restored.');
			}
			cg_entry_watermark_try_chmod($publicPath, 0644);
		}

		cg_entry_watermark_set_url_version($WpUpload);
		delete_post_meta($WpUpload, cg_entry_watermark_meta_key());
		cg_entry_watermark_delete_meta_folder($meta);

		return true;
	}
}

if(!function_exists('cg_entry_watermark_cleanup_from_meta')){
	function cg_entry_watermark_cleanup_from_meta($WpUpload, $meta){
		$WpUpload = absint($WpUpload);
		if(empty($WpUpload) || empty($meta)){
			return false;
		}

		delete_post_meta($WpUpload, cg_entry_watermark_meta_key());
		delete_post_meta($WpUpload, cg_entry_watermark_url_version_meta_key());
		cg_entry_watermark_delete_meta_folder($meta);
		return true;
	}
}

if(!function_exists('cg_entry_watermark_process_meta_list')){
	function cg_entry_watermark_process_meta_list($metas, $mode){
		$result = array(
			'restored' => 0,
			'cleaned' => 0,
			'skipped' => 0,
			'errors' => array()
		);

		if(empty($metas) || !is_array($metas)){
			return $result;
		}

		foreach($metas as $WpUpload => $meta){
			$WpUpload = absint($WpUpload);
			if(empty($WpUpload)){
				$result['skipped']++;
				continue;
			}

			if($mode === 'cleanup_only'){
				if(cg_entry_watermark_cleanup_from_meta($WpUpload, $meta)){
					$result['cleaned']++;
				}else{
					$result['skipped']++;
				}
				continue;
			}

			$restoreResult = cg_entry_watermark_restore_from_meta($WpUpload, $meta);
			if(is_wp_error($restoreResult)){
				$result['errors'][] = array(
					'WpUpload' => $WpUpload,
					'code' => $restoreResult->get_error_code(),
					'message' => $restoreResult->get_error_message()
				);
			}elseif($restoreResult === true){
				$result['restored']++;
			}else{
				$result['skipped']++;
			}
		}

		return $result;
	}
}

if(!function_exists('cg_entry_watermark_restore_wp_upload_ids')){
	function cg_entry_watermark_restore_wp_upload_ids($wpUploadIds, $mode){
		$metas = cg_entry_watermark_get_meta_for_wp_upload_ids($wpUploadIds);
		return cg_entry_watermark_process_meta_list($metas, $mode);
	}
}

if(!function_exists('cg_entry_watermark_restore_all')){
	function cg_entry_watermark_restore_all(){
		$metas = cg_entry_watermark_get_all_meta();
		return cg_entry_watermark_process_meta_list($metas, 'restore_public');
	}
}

if(!function_exists('cg_entry_watermark_delete_attachment_cleanup')){
	function cg_entry_watermark_delete_attachment_cleanup($WpUpload){
		$WpUpload = absint($WpUpload);
		if(empty($WpUpload)){
			return;
		}
		$meta = get_post_meta($WpUpload, cg_entry_watermark_meta_key(), true);
		if(!empty($meta)){
			cg_entry_watermark_cleanup_from_meta($WpUpload, $meta);
		}
	}
}

if(!function_exists('cg_entry_watermark_apply')){
	function cg_entry_watermark_apply($GalleryID, $realId, $file, $settings){
		$WpUpload = absint($file['WpUpload']);
		$type = strtolower($file['type']);
		if(!cg_entry_watermark_supported_type($type)){
			return new WP_Error('cg_watermark_type_not_supported', cg_entry_watermark_type_not_supported_message());
		}
		if(!cg_entry_watermark_type_gd_supported($type)){
			return new WP_Error('cg_watermark_gd_type_not_supported', cg_entry_watermark_gd_unsupported_message($type));
		}
		if(cg_entry_watermark_is_ecommerce_locked($WpUpload)){
			return new WP_Error('cg_watermark_ecommerce_locked', 'Already prepared for selling - deactivate sale download first.');
		}

		$meta = get_post_meta($WpUpload, cg_entry_watermark_meta_key(), true);
		if(!empty($meta)){
			if(!cg_entry_watermark_meta_needs_apply($meta, $settings)){
				return false;
			}
			return cg_entry_watermark_apply_from_originals($WpUpload, $type, $settings);
		}

		return cg_entry_watermark_apply_first_time($GalleryID, $realId, $WpUpload, $type, $settings);
	}
}

if(!function_exists('cg_entry_watermark_get_stored_settings')){
	function cg_entry_watermark_get_stored_settings($storedSettings){
		if(empty($storedSettings)){
			return false;
		}

		if(!is_array($storedSettings)){
			$storedSettings = maybe_unserialize($storedSettings);
		}

		if(!is_array($storedSettings)){
			return false;
		}

		if(array_key_exists('WatermarkIsActive', $storedSettings) && empty($storedSettings['WatermarkIsActive'])){
			return false;
		}

		return cg_entry_watermark_sanitize_settings($storedSettings);
	}
}

if(!function_exists('cg_entry_watermark_normalize_frontend_upload_title')){
	function cg_entry_watermark_normalize_frontend_upload_title($title, $fallbackTitle){
		$default = cg_entry_watermark_default_settings();
		$fallbackTitle = trim((string)$fallbackTitle);
		if($fallbackTitle === ''){
			$fallbackTitle = $default['WatermarkTitle'];
		}

		if(is_array($title)){
			$title = implode(', ', $title);
		}

		$title = trim((string)$title);
		if($title !== ''){
			$title = wp_unslash($title);
			$title = html_entity_decode($title, ENT_QUOTES, get_bloginfo('charset'));
			if(function_exists('cg1l_decode_nested_entities_for_plain_text')){
				$title = cg1l_decode_nested_entities_for_plain_text($title);
			}
			$title = sanitize_text_field(wp_strip_all_tags($title));
		}

		if($title === ''){
			$title = $fallbackTitle;
		}

		if(function_exists('mb_substr')){
			$title = mb_substr($title, 0, 256);
		}else{
			$title = substr($title, 0, 256);
		}

		return $title;
	}
}

if(!function_exists('cg_entry_watermark_frontend_upload_field_type_allowed')){
	function cg_entry_watermark_frontend_upload_field_type_allowed($fieldType){
		return in_array((string)$fieldType, array('text-f','select-f','selectc-f','radio-f','chk-f'), true);
	}
}

if(!function_exists('cg_entry_watermark_get_frontend_upload_field_title')){
	function cg_entry_watermark_get_frontend_upload_field_title($GalleryID, $realId, $fieldId, $fallbackTitle){
		global $wpdb;
		$GalleryID = absint($GalleryID);
		$realId = absint($realId);
		$fieldId = absint($fieldId);
		$fallbackTitle = cg_entry_watermark_normalize_frontend_upload_title($fallbackTitle, '');
		if(empty($GalleryID) || empty($realId) || empty($fieldId)){
			return $fallbackTitle;
		}

		$cacheKey = $GalleryID . '-' . $realId . '-' . $fieldId;
		static $fieldTitleCache = array();
		if(isset($fieldTitleCache[$cacheKey])){
			return $fieldTitleCache[$cacheKey];
		}

		$tablename = $wpdb->prefix . "contest_gal1ery";
		$tablename_entries = $wpdb->prefix . "contest_gal1ery_entries";
		$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
		$tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";

		$field = $wpdb->get_row($wpdb->prepare(
			"SELECT id, Field_Type FROM $tablename_form_input WHERE id = %d AND GalleryID = %d AND Active = 1",
			$fieldId,
			$GalleryID
		));
		if(empty($field)){
			$fieldTitleCache[$cacheKey] = $fallbackTitle;
			return $fallbackTitle;
		}
		if(!cg_entry_watermark_frontend_upload_field_type_allowed($field->Field_Type)){
			$fieldTitleCache[$cacheKey] = $fallbackTitle;
			return $fallbackTitle;
		}

		$title = '';
		if($field->Field_Type === 'selectc-f'){
			$categoryId = $wpdb->get_var($wpdb->prepare(
				"SELECT Category FROM $tablename WHERE id = %d AND GalleryID = %d",
				$realId,
				$GalleryID
			));
			$categoryId = absint($categoryId);
			if(!empty($categoryId)){
				$title = $wpdb->get_var($wpdb->prepare(
					"SELECT Name FROM $tablename_categories WHERE id = %d AND GalleryID = %d AND Active = 1",
					$categoryId,
					$GalleryID
				));
			}
		}else{
			$entry = $wpdb->get_row($wpdb->prepare(
				"SELECT Field_Type, Short_Text, Long_Text, InputDate FROM $tablename_entries WHERE pid = %d AND GalleryID = %d AND f_input_id = %d ORDER BY id ASC LIMIT 1",
				$realId,
				$GalleryID,
				$fieldId
			));
			if(!empty($entry)){
				if(trim((string)$entry->Short_Text) !== ''){
					$title = $entry->Short_Text;
				}elseif(trim((string)$entry->Long_Text) !== ''){
					$title = $entry->Long_Text;
				}elseif($entry->Field_Type === 'date-f' && !empty($entry->InputDate) && $entry->InputDate !== '0000-00-00 00:00:00'){
					$title = $entry->InputDate;
				}
			}
		}

		$title = cg_entry_watermark_normalize_frontend_upload_title($title, $fallbackTitle);
		$fieldTitleCache[$cacheKey] = $title;
		return $title;
	}
}

if(!function_exists('cg_entry_watermark_resolve_frontend_upload_settings')){
	function cg_entry_watermark_resolve_frontend_upload_settings($GalleryID, $realId, $settings){
		$resolvedSettings = cg_entry_watermark_sanitize_settings($settings);
		if(is_array($settings) && !empty($settings['WatermarkTitleSource']) && $settings['WatermarkTitleSource'] === 'upload_field' && !empty($settings['WatermarkFieldId'])){
			$resolvedSettings['WatermarkTitle'] = cg_entry_watermark_get_frontend_upload_field_title($GalleryID, $realId, $settings['WatermarkFieldId'], $resolvedSettings['WatermarkTitle']);
			$resolvedSettings = cg_entry_watermark_sanitize_settings($resolvedSettings);
		}
		return $resolvedSettings;
	}
}

if(!function_exists('cg_entry_watermark_get_frontend_upload_settings')){
	function cg_entry_watermark_get_frontend_upload_settings($GalleryID, $optionsVisual = null){
		global $wpdb;
		$GalleryID = absint($GalleryID);
		if(empty($GalleryID)){
			return false;
		}

		$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
		static $hasFormRealWatermarkSettingsColumn = null;
		if($hasFormRealWatermarkSettingsColumn === null){
			$hasFormRealWatermarkSettingsColumn = $wpdb->get_var("SHOW COLUMNS FROM $tablename_form_input LIKE 'RealWatermarkSettings'") ? true : false;
		}

		if($hasFormRealWatermarkSettingsColumn){
			$storedSettingsRows = $wpdb->get_results($wpdb->prepare(
				"SELECT id, Field_Type, RealWatermarkSettings FROM $tablename_form_input
				WHERE GalleryID = %d AND Active = 1 AND RealWatermarkSettings IS NOT NULL AND RealWatermarkSettings != '' AND RealWatermarkSettings != '\"\"'
				AND Field_Type IN ('text-f','select-f','selectc-f','radio-f','chk-f')
				ORDER BY Field_Order ASC, id ASC",
				$GalleryID
			));
			if(!empty($storedSettingsRows)){
				foreach($storedSettingsRows as $storedSettingsRow){
					if(!cg_entry_watermark_frontend_upload_field_type_allowed($storedSettingsRow->Field_Type)){
						continue;
					}
					$settings = cg_entry_watermark_get_stored_settings($storedSettingsRow->RealWatermarkSettings);
					if(!empty($settings)){
						$settings['WatermarkTitleSource'] = 'upload_field';
						$settings['WatermarkFieldId'] = absint($storedSettingsRow->id);
						$settings['WatermarkFieldType'] = $storedSettingsRow->Field_Type;
						return $settings;
					}
				}
			}
		}

		if(!empty($optionsVisual) && isset($optionsVisual->UploadRealWatermarkSettings)){
			$settings = cg_entry_watermark_get_stored_settings($optionsVisual->UploadRealWatermarkSettings);
			if(!empty($settings)){
				$settings['WatermarkTitleSource'] = 'upload_options';
				return $settings;
			}
		}

		return false;
	}
}

if(!function_exists('cg_entry_watermark_apply_frontend_upload')){
	function cg_entry_watermark_apply_frontend_upload($GalleryID, $realId, $WpUpload, $type, $settings){
		$GalleryID = absint($GalleryID);
		$realId = absint($realId);
		$WpUpload = absint($WpUpload);
		if(empty($GalleryID) || empty($realId) || empty($WpUpload) || empty($settings)){
			return false;
		}

		$type = cg_entry_watermark_get_attachment_type($WpUpload, $type);
		if(!cg_entry_watermark_supported_type($type)){
			return false;
		}

		$file = array(
			'WpUpload' => $WpUpload,
			'type' => $type
		);

		return cg_entry_watermark_apply($GalleryID, $realId, $file, cg_entry_watermark_sanitize_settings($settings));
	}
}

if(!function_exists('cg_entry_watermark_frontend_upload_cleanup_json_files')){
	function cg_entry_watermark_frontend_upload_cleanup_json_files($GalleryID, $realId){
		$GalleryID = absint($GalleryID);
		$realId = absint($realId);
		if(empty($GalleryID) || empty($realId)){
			return;
		}

		$wp_upload_dir = wp_upload_dir();
		$galleryJsonFolder = trailingslashit($wp_upload_dir['basedir']) . 'contest-gallery/gallery-id-' . $GalleryID . '/json/';
		$files = array(
			$galleryJsonFolder . 'image-data/image-data-' . $realId . '.json',
			$galleryJsonFolder . 'image-stats/image-stats-' . $realId . '.json',
			$galleryJsonFolder . 'image-comments/image-comments-' . $realId . '.json',
			$galleryJsonFolder . 'image-info/image-info-' . $realId . '.json',
			$galleryJsonFolder . 'frontend-added-or-removed-images/' . $realId . '.txt'
		);

		foreach($files as $file){
			if(is_file($file)){
				@unlink($file);
			}
		}

		$commentsFolder = $galleryJsonFolder . 'image-comments/ids/' . $realId;
		if(is_dir($commentsFolder)){
			$commentFiles = glob($commentsFolder . '/*');
			if(!empty($commentFiles)){
				foreach($commentFiles as $commentFile){
					if(is_file($commentFile)){
						@unlink($commentFile);
					}
				}
			}
			@rmdir($commentsFolder);
		}
	}
}

if(!function_exists('cg_entry_watermark_frontend_upload_cleanup')){
	function cg_entry_watermark_frontend_upload_cleanup($GalleryID, $entryIds, $wpUploadIds){
		global $wpdb;
		$GalleryID = absint($GalleryID);
		$entryIds = cg_entry_watermark_normalize_entry_ids($entryIds, 1000);
		$wpUploadIds = cg_entry_watermark_normalize_wp_upload_ids($wpUploadIds);

		$tablename = $wpdb->prefix . "contest_gal1ery";
		$tablenameEntries = $wpdb->prefix . "contest_gal1ery_entries";
		$tablenameComments = $wpdb->prefix . "contest_gal1ery_comments";
		$tablenameIp = $wpdb->prefix . "contest_gal1ery_ip";
		$tablenameEcommerceEntries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

		if(!empty($entryIds)){
			$chunks = array_chunk($entryIds, 100);
			foreach($chunks as $chunk){
				$placeholders = implode(',', array_fill(0, count($chunk), '%d'));
				$args = array_merge(array($GalleryID), $chunk);
				$rows = $wpdb->get_results($wpdb->prepare(
					"SELECT * FROM $tablename WHERE GalleryID = %d AND id IN ($placeholders)",
					$args
				));

				if(!empty($rows)){
					foreach($rows as $row){
						cg_entry_watermark_frontend_upload_cleanup_json_files($GalleryID, $row->id);
						$rowWpUploadIds = cg_entry_watermark_collect_wp_upload_ids_from_row($row);
						foreach($rowWpUploadIds as $rowWpUploadId){
							$wpUploadIds[$rowWpUploadId] = $rowWpUploadId;
						}
						foreach(array('WpPage','WpPageUser','WpPageNoVoting','WpPageWinner','WpPageEcommerce') as $wpPageKey){
							if(!empty($row->$wpPageKey)){
								wp_delete_post(absint($row->$wpPageKey), true);
							}
						}
					}
				}

				$args = array_merge(array($GalleryID), $chunk);
				$wpdb->query($wpdb->prepare(
					"DELETE FROM $tablename WHERE GalleryID = %d AND id IN ($placeholders)",
					$args
				));

				foreach(array($tablenameEntries, $tablenameComments, $tablenameIp, $tablenameEcommerceEntries) as $tableName){
					$wpdb->query($wpdb->prepare(
						"DELETE FROM $tableName WHERE pid IN ($placeholders)",
						$chunk
					));
				}
			}
		}

		$wpUploadIds = cg_entry_watermark_normalize_wp_upload_ids($wpUploadIds);
		foreach($wpUploadIds as $WpUpload){
			wp_delete_attachment($WpUpload, true);
		}
	}
}

if(!function_exists('cg_entry_watermark_frontend_upload_fail')){
	function cg_entry_watermark_frontend_upload_fail($GalleryID, $GalleryIDuser, $message, $entryIds, $wpUploadIds){
		$message = trim((string)$message);
		if($message === ''){
			$message = 'Watermark could not be created. Upload stopped.';
		}

		cg_entry_watermark_frontend_upload_cleanup($GalleryID, $entryIds, $wpUploadIds);

		echo '<script data-cg-processing="true">';
		echo 'var gid = ' . json_encode($GalleryIDuser) . ';';
		echo 'if(typeof cgJsData !== "undefined" && cgJsData[gid] && cgJsData[gid].vars && cgJsData[gid].vars.upload){';
		echo 'cgJsData[gid].vars.upload.doneUploadFailed = true;';
		echo 'cgJsData[gid].vars.upload.failMessage = ' . json_encode($message) . ';';
		echo '}';
		echo '</script>';
		echo esc_html($message);
		die;
	}
}

if(!function_exists('cg_entry_watermark_get_active_entry_row_for_json')){
	function cg_entry_watermark_get_active_entry_row_for_json($GalleryID, $realId){
		global $wpdb;
		$tablename = $wpdb->prefix . "contest_gal1ery";
		$table_posts = $wpdb->posts;

		$GalleryID = absint($GalleryID);
		$realId = absint($realId);
		if(empty($GalleryID) || empty($realId)){
			return false;
		}

		return $wpdb->get_row($wpdb->prepare(
			"SELECT DISTINCT $table_posts.*, $tablename.* FROM $table_posts, $tablename WHERE
			  (($tablename.id = %d) AND $tablename.GalleryID = %d AND $tablename.Active = '1' and $table_posts.ID = $tablename.WpUpload)
			  OR
			  (($tablename.id = %d) AND $tablename.GalleryID = %d AND $tablename.Active = '1' AND $tablename.WpUpload = 0)
			  GROUP BY $tablename.id ORDER BY $tablename.id DESC LIMIT 0, 1",
			$realId, $GalleryID, $realId, $GalleryID
		));
	}
}

if(!function_exists('cg_entry_watermark_refresh_entry_json')){
	function cg_entry_watermark_refresh_entry_json($GalleryID, $realId){
		if(!function_exists('cg_create_json_files_when_activating')){
			return false;
		}

		$row = cg_entry_watermark_get_active_entry_row_for_json($GalleryID, $realId);
		if(empty($row)){
			return false;
		}

		cg_create_json_files_when_activating($GalleryID, $row);
		return true;
	}
}

if(!function_exists('cg_entry_watermark_invalidate')){
	function cg_entry_watermark_invalidate($GalleryID, $realId){
		if(function_exists('cg1l_push_recent_id_file')){
			cg1l_push_recent_id_file($GalleryID, $realId, 'image-main-data-last-update');
			cg1l_push_recent_id_file($GalleryID, $realId, 'image-query-data-last-update');
			cg1l_push_recent_id_file($GalleryID, $realId, 'image-urls-data-last-update');
		}
		if(function_exists('cg1l_create_last_updated_time_file_image_data')){
			cg1l_create_last_updated_time_file_image_data($GalleryID);
		}
	}
}

if(!function_exists('cg_entry_watermark_get_state_ajax')){
	function cg_entry_watermark_get_state_ajax(){
		contest_gal1ery_db_check();
		cg_backend_ajax_require_access_json();

		$GalleryID = isset($_POST['GalleryID']) ? absint($_POST['GalleryID']) : 0;
		$realId = isset($_POST['realId']) ? absint($_POST['realId']) : 0;
		$galleryHash = isset($_POST['cgGalleryHash']) ? sanitize_text_field(wp_unslash($_POST['cgGalleryHash'])) : '';
		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		$entryData = cg_entry_watermark_get_entry_files($GalleryID, $realId);
		if(empty($entryData)){
			cg_backend_ajax_error_json('Entry could not be found.', 404, 'cg_watermark_entry_missing');
		}

		wp_send_json_success(array(
			'realId' => $realId,
			'GalleryID' => $GalleryID,
			'files' => array_values($entryData['files'])
		));
	}
}

if(!function_exists('cg_entry_watermark_preview_source_ajax')){
	function cg_entry_watermark_preview_source_ajax(){
		contest_gal1ery_db_check();
		cg_backend_ajax_require_access_json();

		$GalleryID = isset($_REQUEST['GalleryID']) ? absint($_REQUEST['GalleryID']) : 0;
		$realId = isset($_REQUEST['realId']) ? absint($_REQUEST['realId']) : 0;
		$WpUpload = isset($_REQUEST['WpUpload']) ? absint($_REQUEST['WpUpload']) : 0;
		$galleryHash = isset($_REQUEST['cgGalleryHash']) ? sanitize_text_field(wp_unslash($_REQUEST['cgGalleryHash'])) : '';
		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		$entryData = cg_entry_watermark_get_entry_files($GalleryID, $realId);
		if(empty($entryData) || empty($entryData['files'][$WpUpload])){
			cg_backend_ajax_error_json('Entry file could not be found.', 404, 'cg_watermark_entry_file_missing');
		}

		$file = $entryData['files'][$WpUpload];
		if(empty($file['supported']) && empty($file['watermarked'])){
			$message = !empty($file['unsupported_reason']) ? $file['unsupported_reason'] : cg_entry_watermark_type_not_supported_message();
			cg_backend_ajax_error_json($message, 400, 'cg_watermark_type_not_supported');
		}

		$meta = get_post_meta($WpUpload, cg_entry_watermark_meta_key(), true);
		$source = cg_entry_watermark_get_protected_preview_source($meta);
		if(is_wp_error($source)){
			cg_backend_ajax_error_json($source->get_error_message(), 404, $source->get_error_code());
		}

		nocache_headers();
		status_header(200);
		header('Content-Type: ' . $source['mime']);
		header('Content-Length: ' . filesize($source['path']));
		header('X-Content-Type-Options: nosniff');
		readfile($source['path']);
		exit;
	}
}

if(!function_exists('cg_entry_watermark_save_ajax')){
	function cg_entry_watermark_save_ajax(){
		contest_gal1ery_db_check();
		cg_backend_ajax_require_access_json();

		$GalleryID = isset($_POST['GalleryID']) ? absint($_POST['GalleryID']) : 0;
		$realId = isset($_POST['realId']) ? absint($_POST['realId']) : 0;
		$galleryHash = isset($_POST['cgGalleryHash']) ? sanitize_text_field(wp_unslash($_POST['cgGalleryHash'])) : '';
		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		$entryData = cg_entry_watermark_get_entry_files($GalleryID, $realId);
		if(empty($entryData)){
			cg_backend_ajax_error_json('Entry could not be found.', 404, 'cg_watermark_entry_missing');
		}

		$submittedFiles = isset($_POST['files']) && is_array($_POST['files']) ? $_POST['files'] : array();
		if(empty($submittedFiles)){
			cg_backend_ajax_error_json('No watermark files submitted.', 400, 'cg_watermark_no_files');
		}

		$changed = 0;
		foreach($entryData['files'] as $WpUpload => $file){
			$key = (string)$WpUpload;
			if(!isset($submittedFiles[$key]) || !is_array($submittedFiles[$key])){
				continue;
			}

			$filePost = $submittedFiles[$key];
			$enabled = !empty($filePost['enabled']) && $filePost['enabled'] == '1';
			$settingsPost = isset($filePost['settings']) && is_array($filePost['settings']) ? $filePost['settings'] : array();
			$settings = cg_entry_watermark_sanitize_settings($settingsPost);

			if($enabled){
				if(empty($file['supported'])){
					$message = !empty($file['unsupported_reason']) ? $file['unsupported_reason'] : cg_entry_watermark_type_not_supported_message();
					cg_backend_ajax_error_json($message, 400, 'cg_watermark_type_not_supported');
				}
				$result = cg_entry_watermark_apply($GalleryID, $realId, $file, $settings);
			}else{
				$result = cg_entry_watermark_restore($WpUpload);
			}

			if(is_wp_error($result)){
				cg_backend_ajax_error_json($result->get_error_message(), 400, $result->get_error_code());
			}
			if($result === true){
				$changed++;
			}
		}

		if($changed){
			cg_entry_watermark_refresh_entry_json($GalleryID, $realId);
			cg_entry_watermark_invalidate($GalleryID, $realId);
		}

		wp_send_json_success(array(
			'realId' => $realId,
			'GalleryID' => $GalleryID,
			'changed' => $changed,
			'message' => 'Watermark saved'
		));
	}
}

if(!function_exists('cg_entry_watermark_bulk_prepare_ajax')){
	function cg_entry_watermark_bulk_prepare_ajax(){
		contest_gal1ery_db_check();
		cg_backend_ajax_require_access_json();

		$GalleryID = isset($_POST['GalleryID']) ? absint($_POST['GalleryID']) : 0;
		$galleryHash = isset($_POST['cgGalleryHash']) ? sanitize_text_field(wp_unslash($_POST['cgGalleryHash'])) : '';
		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		$entryIdsPost = isset($_POST['entryIds']) && is_array($_POST['entryIds']) ? $_POST['entryIds'] : array();
		$entryIds = cg_entry_watermark_normalize_entry_ids($entryIdsPost, 50);
		if(empty($entryIds)){
			cg_backend_ajax_error_json('No visible entries submitted.', 400, 'cg_watermark_bulk_no_entries');
		}

		$settingsPost = isset($_POST['settings']) && is_array($_POST['settings']) ? $_POST['settings'] : array();
		$settings = cg_entry_watermark_sanitize_settings($settingsPost);
		$candidates = array();
		$wpUploadIds = array();
		$skippedUnsupported = 0;
		$skippedLocked = 0;
		$skippedMissing = 0;

		foreach($entryIds as $realId){
			$entryData = cg_entry_watermark_get_entry_files($GalleryID, $realId);
			if(empty($entryData) || empty($entryData['files'])){
				$skippedMissing++;
				continue;
			}

			foreach($entryData['files'] as $WpUpload => $file){
				$WpUpload = absint($WpUpload);
				if(empty($WpUpload) || empty($file['supported'])){
					$skippedUnsupported++;
					continue;
				}
				if(!empty($file['ecommerce_locked'])){
					$skippedLocked++;
					continue;
				}

				$candidates[] = array(
					'realId' => absint($realId),
					'WpUpload' => $WpUpload
				);
				$wpUploadIds[$WpUpload] = $WpUpload;
			}
		}

		$metas = cg_entry_watermark_get_meta_for_wp_upload_ids(array_values($wpUploadIds));
		$jobs = array();
		$skippedSame = 0;
		foreach($candidates as $candidate){
			$WpUpload = $candidate['WpUpload'];
			$meta = isset($metas[$WpUpload]) ? $metas[$WpUpload] : array();
			if(!empty($meta) && !cg_entry_watermark_meta_needs_apply($meta, $settings)){
				$skippedSame++;
				continue;
			}
			$jobs[] = $candidate;
		}

		wp_send_json_success(array(
			'GalleryID' => $GalleryID,
			'entryIds' => $entryIds,
			'jobs' => $jobs,
			'total' => count($jobs),
			'settings' => $settings,
			'skipped_same' => $skippedSame,
			'skipped_unsupported' => $skippedUnsupported,
			'skipped_locked' => $skippedLocked,
			'skipped_missing' => $skippedMissing
		));
	}
}

if(!function_exists('cg_entry_watermark_bulk_restore_prepare_ajax')){
	function cg_entry_watermark_bulk_restore_prepare_ajax(){
		contest_gal1ery_db_check();
		cg_backend_ajax_require_access_json();

		$GalleryID = isset($_POST['GalleryID']) ? absint($_POST['GalleryID']) : 0;
		$galleryHash = isset($_POST['cgGalleryHash']) ? sanitize_text_field(wp_unslash($_POST['cgGalleryHash'])) : '';
		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		$entryIdsPost = isset($_POST['entryIds']) && is_array($_POST['entryIds']) ? $_POST['entryIds'] : array();
		$entryIds = cg_entry_watermark_normalize_entry_ids($entryIdsPost, 50);
		if(empty($entryIds)){
			cg_backend_ajax_error_json('No visible entries submitted.', 400, 'cg_watermark_bulk_no_entries');
		}

		$candidates = array();
		$wpUploadIds = array();
		$skippedMissing = 0;

		foreach($entryIds as $realId){
			$entryData = cg_entry_watermark_get_entry_files($GalleryID, $realId);
			if(empty($entryData) || empty($entryData['files'])){
				$skippedMissing++;
				continue;
			}

			foreach($entryData['files'] as $WpUpload => $file){
				$WpUpload = absint($WpUpload);
				if(empty($WpUpload)){
					$skippedMissing++;
					continue;
				}

				$candidates[] = array(
					'realId' => absint($realId),
					'WpUpload' => $WpUpload
				);
				$wpUploadIds[$WpUpload] = $WpUpload;
			}
		}

		$metas = cg_entry_watermark_get_meta_for_wp_upload_ids(array_values($wpUploadIds));
		$jobs = array();
		$skippedNotWatermarked = 0;
		foreach($candidates as $candidate){
			$WpUpload = $candidate['WpUpload'];
			$meta = isset($metas[$WpUpload]) ? $metas[$WpUpload] : array();
			if(empty($meta) || empty($meta['files']) || !is_array($meta['files'])){
				$skippedNotWatermarked++;
				continue;
			}
			$jobs[] = $candidate;
		}

		wp_send_json_success(array(
			'GalleryID' => $GalleryID,
			'entryIds' => $entryIds,
			'jobs' => $jobs,
			'total' => count($jobs),
			'skipped_missing' => $skippedMissing,
			'skipped_not_watermarked' => $skippedNotWatermarked
		));
	}
}

if(!function_exists('cg_entry_watermark_normalize_bulk_jobs')){
	function cg_entry_watermark_normalize_bulk_jobs($jobsPost, $limit = 3){
		if(!is_array($jobsPost)){
			return array();
		}

		$limit = absint($limit);
		if(empty($limit)){
			$limit = 3;
		}

		$jobs = array();
		foreach($jobsPost as $jobPost){
			if(!is_array($jobPost)){
				continue;
			}
			$realId = isset($jobPost['realId']) ? absint($jobPost['realId']) : 0;
			$WpUpload = isset($jobPost['WpUpload']) ? absint($jobPost['WpUpload']) : 0;
			if(empty($realId) || empty($WpUpload)){
				continue;
			}
			$jobs[] = array(
				'realId' => $realId,
				'WpUpload' => $WpUpload
			);
			if(count($jobs) >= $limit){
				break;
			}
		}

		return $jobs;
	}
}

if(!function_exists('cg_entry_watermark_bulk_process_ajax')){
	function cg_entry_watermark_bulk_process_ajax(){
		contest_gal1ery_db_check();
		cg_backend_ajax_require_access_json();

		$GalleryID = isset($_POST['GalleryID']) ? absint($_POST['GalleryID']) : 0;
		$galleryHash = isset($_POST['cgGalleryHash']) ? sanitize_text_field(wp_unslash($_POST['cgGalleryHash'])) : '';
		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		$settingsPost = isset($_POST['settings']) && is_array($_POST['settings']) ? $_POST['settings'] : array();
		$settings = cg_entry_watermark_sanitize_settings($settingsPost);
		$jobsPost = isset($_POST['jobs']) && is_array($_POST['jobs']) ? $_POST['jobs'] : array();
		$jobs = cg_entry_watermark_normalize_bulk_jobs($jobsPost, 3);
		if(empty($jobs)){
			cg_backend_ajax_error_json('No watermark jobs submitted.', 400, 'cg_watermark_bulk_no_jobs');
		}

		$processed = 0;
		$changed = 0;
		$skipped = 0;
		$changedEntryIds = array();
		foreach($jobs as $job){
			$realId = $job['realId'];
			$WpUpload = $job['WpUpload'];
			$entryData = cg_entry_watermark_get_entry_files($GalleryID, $realId);
			if(empty($entryData) || empty($entryData['files'][$WpUpload])){
				$skipped++;
				$processed++;
				continue;
			}

			$file = $entryData['files'][$WpUpload];
			if(empty($file['supported']) || !empty($file['ecommerce_locked'])){
				$skipped++;
				$processed++;
				continue;
			}

			$result = cg_entry_watermark_apply($GalleryID, $realId, $file, $settings);
			if(is_wp_error($result)){
				cg_backend_ajax_error_json($result->get_error_message(), 400, $result->get_error_code());
			}
			if($result === true){
				$changed++;
				$changedEntryIds[$realId] = $realId;
			}else{
				$skipped++;
			}
			$processed++;
		}

		wp_send_json_success(array(
			'GalleryID' => $GalleryID,
			'processed' => $processed,
			'changed' => $changed,
			'skipped' => $skipped,
			'changedEntryIds' => array_values($changedEntryIds)
		));
	}
}

if(!function_exists('cg_entry_watermark_bulk_restore_process_ajax')){
	function cg_entry_watermark_bulk_restore_process_ajax(){
		contest_gal1ery_db_check();
		cg_backend_ajax_require_access_json();

		$GalleryID = isset($_POST['GalleryID']) ? absint($_POST['GalleryID']) : 0;
		$galleryHash = isset($_POST['cgGalleryHash']) ? sanitize_text_field(wp_unslash($_POST['cgGalleryHash'])) : '';
		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		$jobsPost = isset($_POST['jobs']) && is_array($_POST['jobs']) ? $_POST['jobs'] : array();
		$jobs = cg_entry_watermark_normalize_bulk_jobs($jobsPost, 3);
		if(empty($jobs)){
			cg_backend_ajax_error_json('No unwatermark jobs submitted.', 400, 'cg_watermark_bulk_no_jobs');
		}

		$processed = 0;
		$changed = 0;
		$skipped = 0;
		$changedEntryIds = array();
		foreach($jobs as $job){
			$realId = $job['realId'];
			$WpUpload = $job['WpUpload'];
			$entryData = cg_entry_watermark_get_entry_files($GalleryID, $realId);
			if(empty($entryData) || empty($entryData['files'][$WpUpload])){
				$skipped++;
				$processed++;
				continue;
			}

			$result = cg_entry_watermark_restore($WpUpload);
			if(is_wp_error($result)){
				cg_backend_ajax_error_json($result->get_error_message(), 400, $result->get_error_code());
			}
			if($result === true){
				$changed++;
				$changedEntryIds[$realId] = $realId;
			}else{
				$skipped++;
			}
			$processed++;
		}

		wp_send_json_success(array(
			'GalleryID' => $GalleryID,
			'processed' => $processed,
			'changed' => $changed,
			'skipped' => $skipped,
			'changedEntryIds' => array_values($changedEntryIds)
		));
	}
}

if(!function_exists('cg_entry_watermark_bulk_finalize_ajax')){
	function cg_entry_watermark_bulk_finalize_ajax(){
		contest_gal1ery_db_check();
		cg_backend_ajax_require_access_json();

		$GalleryID = isset($_POST['GalleryID']) ? absint($_POST['GalleryID']) : 0;
		$galleryHash = isset($_POST['cgGalleryHash']) ? sanitize_text_field(wp_unslash($_POST['cgGalleryHash'])) : '';
		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		$entryIdsPost = isset($_POST['entryIds']) && is_array($_POST['entryIds']) ? $_POST['entryIds'] : array();
		$entryIds = cg_entry_watermark_normalize_entry_ids($entryIdsPost, 50);
		$refreshed = 0;
		foreach($entryIds as $realId){
			if(cg_entry_watermark_refresh_entry_json($GalleryID, $realId)){
				$refreshed++;
			}
			cg_entry_watermark_invalidate($GalleryID, $realId);
		}

		wp_send_json_success(array(
			'GalleryID' => $GalleryID,
			'refreshed' => $refreshed
		));
	}
}

add_action('delete_attachment', 'cg_entry_watermark_delete_attachment_cleanup');
add_filter('wp_prepare_attachment_for_js', 'cg_entry_watermark_prepare_attachment_for_js', 10, 3);
add_action('wp_ajax_post_cg_get_entry_watermark_state', 'cg_entry_watermark_get_state_ajax');
add_action('wp_ajax_post_cg_get_entry_watermark_preview_source', 'cg_entry_watermark_preview_source_ajax');
add_action('wp_ajax_post_cg_save_entry_watermark', 'cg_entry_watermark_save_ajax');
add_action('wp_ajax_post_cg_prepare_bulk_entry_watermark', 'cg_entry_watermark_bulk_prepare_ajax');
add_action('wp_ajax_post_cg_process_bulk_entry_watermark', 'cg_entry_watermark_bulk_process_ajax');
add_action('wp_ajax_post_cg_prepare_bulk_entry_unwatermark', 'cg_entry_watermark_bulk_restore_prepare_ajax');
add_action('wp_ajax_post_cg_process_bulk_entry_unwatermark', 'cg_entry_watermark_bulk_restore_process_ajax');
add_action('wp_ajax_post_cg_finalize_bulk_entry_watermark', 'cg_entry_watermark_bulk_finalize_ajax');
