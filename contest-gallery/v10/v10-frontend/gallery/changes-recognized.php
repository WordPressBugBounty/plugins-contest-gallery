<?php
if(!defined('ABSPATH')){exit;}

$wp_upload_dir = wp_upload_dir();
$fileName = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-general/cg-galleries-main-page-information-recognized.txt';
file_put_contents($fileName,'do-not-remove');

return;


?>



