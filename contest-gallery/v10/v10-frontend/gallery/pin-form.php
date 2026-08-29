<?php

if (!defined('ABSPATH')) {
    exit;
}

if(!empty($options['visual']['ShowPinFormVoting']) || !empty($options['visual']['ShowPinFormUploading'])){
    $shortcode_name_temp = $shortcode_name;
    $cgUsersPinModeWasSet = isset($cgUsersPinMode);
    $cgUsersPinModeTemp = $cgUsersPinModeWasSet ? $cgUsersPinMode : '';
    $isShowPinFormVotingUploading = 1;
    $cgUsersPinUploadIntro = '';
    if(!empty($options['visual']['ShowPinFormUploading']) && !empty($options['pro']['RegUserUploadOnlyText'])){
        $cgUsersPinUploadIntro = $options['pro']['RegUserUploadOnlyText'];
    }
    $shortcode_name = 'cg_users_pin';
    $cgUsersPinMode = 'access';
    $InGalleryRegistryHide = ' cg_hide ';
    include(__DIR__.'/../../../v10/v10-admin/users/frontend/registry/users-registry.php');
    $shortcode_name = $shortcode_name_temp;
    if($cgUsersPinModeWasSet){
        $cgUsersPinMode = $cgUsersPinModeTemp;
    }else{
        unset($cgUsersPinMode);
    }
    unset($cgUsersPinModeWasSet, $cgUsersPinModeTemp, $cgUsersPinUploadIntro);
}
