<?php

if (!defined('ABSPATH')) {
    exit;
}

if(!empty($options['visual']['ShowPinFormVoting']) || !empty($options['visual']['ShowPinFormUploading'])){
    $shortcode_name_temp = $shortcode_name;
    $isShowPinFormVotingUploading = 1;
    $shortcode_name = 'cg_users_pin';
    $InGalleryRegistryHide = ' cg_hide ';
    include(__DIR__.'/../../../v10/v10-admin/users/frontend/registry/users-registry.php');
    $shortcode_name = $shortcode_name_temp;
}
