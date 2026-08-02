<?php
if(!defined('ABSPATH')){exit;}

cg_require_backend_access();

$GalleryID = (!empty($_POST['option_id']) && !is_array($_POST['option_id'])) ? absint($_POST['option_id']) : 0;
$cg_file_name_mail_log = (isset($_POST['cg_file_name_mail_log']) && !is_array($_POST['cg_file_name_mail_log'])) ? sanitize_text_field(wp_unslash($_POST['cg_file_name_mail_log'])) : '';
$cg_file_name_mail_log_general = (isset($_POST['cg_file_name_mail_log_general']) && !is_array($_POST['cg_file_name_mail_log_general'])) ? sanitize_text_field(wp_unslash($_POST['cg_file_name_mail_log_general'])) : '';

if (
	empty($GalleryID) ||
	!preg_match('/^[a-f0-9]{32}$/D', $cg_file_name_mail_log) ||
	!preg_match('/^[a-f0-9]{32}$/D', $cg_file_name_mail_log_general)
) {
	status_header(400);
	die('Mail log request is invalid.');
}

$expectedGalleryLogHash = md5(wp_salt('auth').'---cnglog---'.$GalleryID);
$expectedGeneralLogHash = md5(wp_salt('auth').'---cnglog---'.'0');

if (
	!cg_hash_equals($expectedGalleryLogHash, $cg_file_name_mail_log) ||
	!cg_hash_equals($expectedGeneralLogHash, $cg_file_name_mail_log_general)
) {
	status_header(403);
	die('Mail log request is not authorized.');
}

$uploadFolder = wp_upload_dir();
$galleryLogDirectory = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/logs/errors';
$generalLogDirectory = $uploadFolder['basedir'].'/contest-gallery/gallery-general/logs/errors';
$files = array(
	array($galleryLogDirectory, $galleryLogDirectory.'/mail-'.$cg_file_name_mail_log.'.log'),
	array($generalLogDirectory, $generalLogDirectory.'/mail-'.$cg_file_name_mail_log_general.'.log')
);

$downloadContent = '';
foreach($files as $fileData){
	$directoryRealPath = realpath($fileData[0]);
	$fileRealPath = realpath($fileData[1]);

	if (
		$directoryRealPath === false ||
		$fileRealPath === false ||
		dirname($fileRealPath) !== $directoryRealPath ||
		!is_file($fileRealPath) ||
		!is_readable($fileRealPath)
	) {
		continue;
	}

	$fileContent = file_get_contents($fileRealPath);
	if($fileContent === false || $fileContent === ''){
		continue;
	}

	if($downloadContent !== ''){
		$downloadContent .= "\r\n";
	}
	$downloadContent .= $fileContent;
}

if($downloadContent === ''){
	status_header(404);
	die('Mail log not found.');
}

$downloadFileName = 'mail-'.$cg_file_name_mail_log.'-download.log';
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="'.$downloadFileName.'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($downloadContent));
header('Content-Type: text/plain; charset=UTF-8');
echo $downloadContent;
die();
