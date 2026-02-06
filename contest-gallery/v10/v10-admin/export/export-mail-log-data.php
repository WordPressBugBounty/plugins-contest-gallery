<?php
if(!defined('ABSPATH')){exit;}

$GalleryID = absint($_POST['option_id']);
$cg_file_name_mail_log = $_POST['cg_file_name_mail_log'];//  cause wp_salt is not available in that state
$cg_file_name_mail_log_general = $_POST['cg_file_name_mail_log_general'];//  cause wp_salt is not available in that state

$uploadFolder = wp_upload_dir();
$file = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/logs/errors/mail-'.$cg_file_name_mail_log.'.log';

$fileGeneral = $uploadFolder['basedir'].'/contest-gallery/gallery-general/logs/errors/mail-'.$cg_file_name_mail_log_general.'.log';

$downloadContent = '';
if(file_exists($file)){
	$downloadContent .= file_get_contents($file);
}

if(file_exists($fileGeneral)){
	$downloadContent .= "\r\n";
	$downloadContent .= file_get_contents($fileGeneral);
}

if(!empty($downloadContent)){
	//var_dump($downloadContent);die;
	$folder = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/logs/errors';
	if(!is_dir($folder)){
		mkdir($folder,0755,true);
		$usersTableHtmlStart = <<<HEREDOC
<Files "*.log">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</Files>
HEREDOC;
		$htaccessFile = $folder.'/.htaccess';
		file_put_contents($htaccessFile,$usersTableHtmlStart);
	}
	$fileDownload = $folder.'/mail-'.$cg_file_name_mail_log.'-download.log';
	file_put_contents($fileDownload,$downloadContent);
	header('Content-Description: File Transfer');
	header('Content-Disposition: attachment; filename='.basename($fileDownload));
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($fileDownload));
	header("Content-Type: text/plain");
	readfile($fileDownload);
	die();
}
