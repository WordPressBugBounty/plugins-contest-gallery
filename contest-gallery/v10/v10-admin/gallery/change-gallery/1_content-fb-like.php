<?php

if(!defined('ABSPATH')){
	exit;
}

$fbcontent = (isset($_POST['fbcontent']) && is_array($_POST['fbcontent'])) ? $_POST['fbcontent'] : array();

if(!empty($fbcontent)){

	if(!function_exists('cg_require_backend_access')){
		exit;
	}

	cg_require_backend_access();

}

if(!empty($fbcontent) && !empty($GalleryID) && !empty($tablename) && !empty($wpdb)){

	$uploadFolder = wp_upload_dir();
	$uploadBaseDirectoryPath = $uploadFolder['basedir'];
	$contestGalleryDirectoryPath = $uploadBaseDirectoryPath.'/contest-gallery';
	$galleryDirectoryPath = $contestGalleryDirectoryPath.'/gallery-id-'.absint($GalleryID);
	$uploadBaseDirectoryRealPath = realpath($uploadBaseDirectoryPath);
	$contestGalleryDirectoryRealPath = realpath($contestGalleryDirectoryPath);
	$galleryDirectoryRealPath = realpath($galleryDirectoryPath);

	if(
		$uploadBaseDirectoryRealPath !== false &&
		$contestGalleryDirectoryRealPath !== false &&
		$galleryDirectoryRealPath !== false &&
		!is_link($contestGalleryDirectoryPath) &&
		!is_link($galleryDirectoryPath)
	){

		$uploadBaseDirectoryRealPathNormalized = trailingslashit(wp_normalize_path($uploadBaseDirectoryRealPath));
		$contestGalleryDirectoryRealPathNormalized = trailingslashit(wp_normalize_path($contestGalleryDirectoryRealPath));
		$galleryDirectoryRealPathNormalized = trailingslashit(wp_normalize_path($galleryDirectoryRealPath));

		if(
			strpos($contestGalleryDirectoryRealPathNormalized,$uploadBaseDirectoryRealPathNormalized) === 0 &&
			strpos($galleryDirectoryRealPathNormalized,$contestGalleryDirectoryRealPathNormalized) === 0
		){

			foreach($fbcontent as $imageId => $fbContentArray){

				$imageId = absint($imageId);

				if(empty($imageId) || !is_array($fbContentArray)){
					continue;
				}

				$imageData = $wpdb->get_row($wpdb->prepare(
					"SELECT Timestamp, NamePic FROM $tablename WHERE id = %d AND GalleryID = %d",
					array($imageId,absint($GalleryID))
				));

				if(empty($imageData)){
					continue;
				}

				$namePic = (string)$imageData->NamePic;

				if(
					strpos($namePic,'/') !== false ||
					strpos($namePic,'\\') !== false ||
					strpos($namePic,"\0") !== false
				){
					continue;
				}

				$fileName = intval($imageData->Timestamp).'_'.$namePic.'413.html';
				$filePath = $galleryDirectoryPath.'/'.$fileName;
				$fileRealPath = realpath($filePath);

				if(
					$fileRealPath === false ||
					!is_file($filePath) ||
					is_link($filePath) ||
					strpos(wp_normalize_path($fileRealPath),$galleryDirectoryRealPathNormalized) !== 0
				){
					continue;
				}

				$title = (isset($fbContentArray['title']) && is_string($fbContentArray['title'])) ? sanitize_text_field(wp_unslash($fbContentArray['title'])) : '';
				$description = (isset($fbContentArray['description']) && is_string($fbContentArray['description'])) ? sanitize_textarea_field(wp_unslash($fbContentArray['description'])) : '';
				$titleForHtml = esc_html($title);
				$titleForAttribute = esc_attr($title);
				$descriptionForAttribute = esc_attr($description);
				$handle = fopen($fileRealPath,'r');

				if(!$handle){
					continue;
				}

				$newFileContent = '';
				$isTitleHeaderPassed = false;
				$isTitlePassed = false;
				$isDescriptionPassed = false;

				while(($line = fgets($handle)) !== false){

					if(!$isTitleHeaderPassed && strpos($line,'<title>') !== false){
						$isTitleHeaderPassed = true;
						$line = '<title>'.$titleForHtml.'</title>'."\n";
					}elseif(!$isTitlePassed && strpos($line,'property="og:title"') !== false){
						$isTitlePassed = true;
						$line = '<meta property="og:title"   content="'.$titleForAttribute.'" />'."\n";
					}elseif(!$isDescriptionPassed && strpos($line,'property="og:description"') !== false){
						$isDescriptionPassed = true;
						$line = '<meta property="og:description"   content="'.$descriptionForAttribute.'" />'."\n";
					}

					if(strpos($line,'version=v2.8') !== false){
						$line = str_replace('version=v2.8','version=v3.0',$line);
					}

					$newFileContent .= $line;

				}

				fclose($handle);

				if(!$isTitleHeaderPassed || !$isTitlePassed || !$isDescriptionPassed){
					continue;
				}

				$temporaryFilePath = tempnam($galleryDirectoryRealPath,'cg-fb-');

				if($temporaryFilePath === false){
					continue;
				}

				$writtenBytes = file_put_contents($temporaryFilePath,$newFileContent,LOCK_EX);

				if($writtenBytes === false || $writtenBytes !== strlen($newFileContent)){
					@unlink($temporaryFilePath);
					continue;
				}

				$filePermissions = fileperms($fileRealPath);

				if($filePermissions !== false){
					@chmod($temporaryFilePath,$filePermissions & 0777);
				}

				if(!@rename($temporaryFilePath,$fileRealPath)){
					@unlink($temporaryFilePath);
				}

			}

		}

	}

}
