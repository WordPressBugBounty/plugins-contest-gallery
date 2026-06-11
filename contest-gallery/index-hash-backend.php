<?php
global $wp_version;

$cgVersion = isset($cgVersion) ? $cgVersion : cg_get_version_for_scripts();

$cgBackendHashPosted = '';

if (!empty($_POST['cgBackendHash'])) {
    $cgBackendHashPosted = cg1l_sanitize_method($_POST['cgBackendHash']);
}

echo "<input type='hidden' name='cgBackendHashPosted' id='cgBackendHashPosted' value='$cgBackendHashPosted'>";
echo "<input type='hidden' name='cgBackendHash' id='cgBackendHash' value='".md5(wp_salt( 'auth').'---cgbackend---')."'>";
echo "<input type='hidden' name='cgVersion' id='cgVersion' value='$cgVersion'>";
echo "<input type='hidden' name='cgWordPressVersion' id='cgWordPressVersion' value='$wp_version'>";
