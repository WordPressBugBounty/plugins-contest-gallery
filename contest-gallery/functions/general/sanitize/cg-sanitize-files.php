<?php

if(!function_exists('cg1l_sanitize_files')){

    function cg1l_sanitize_files($FILES,$keyFiles = 'data',$allowedImageSize = 0, $galeryIDuser = '0', $galeryID = 0, $isFrontendUpload = false) {

        if(empty($FILES[$keyFiles])){

            ?>

            <script data-cg-processing="true">

                var gid = <?php echo json_encode($galeryIDuser);?>;
                cgJsData[gid].vars.upload.doneUploadFailed = true;
                cgJsData[gid].vars.upload.failMessage = <?php echo json_encode("Upload manipulation, no files sent");?>;

            </script>

            <?php

            echo "Upload manipulation, no files sent";
            die;

        }

        if(!function_exists('getimagesize')){

            ?>
            <script data-cg-processing="true">
                var gid = <?php echo json_encode($galeryIDuser);?>;
                cgJsData[gid].vars.upload.doneUploadFailed = true;
                cgJsData[gid].vars.upload.failMessage = <?php echo json_encode("You require getimagesize function to be available for your server to be able to upload");?>;
            </script>
            <?php

            echo "You require getimagesize function to be available for your server to be able to upload";
            die;

        }

        $frontendUploadFieldContent = [];
        $frontendUploadImageUploadAllowed = true;
        $frontendUploadAllowUploadJPG = 1;
        $frontendUploadAllowUploadPNG = 1;
        $frontendUploadAllowUploadGIF = 1;
        $frontendUploadAllowUploadICO = 1;

        if($isFrontendUpload){

            global $wpdb;
            $tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
            $tablenameProOptions = $wpdb->prefix . "contest_gal1ery_pro_options";
            $imageInputFieldContent = $wpdb->get_var($wpdb->prepare("SELECT Field_Content FROM $tablename_form_input WHERE GalleryID = %d AND Field_Type = 'image-f'", $galeryID));
            $frontendUploadFieldContent = unserialize($imageInputFieldContent);
            if(!is_array($frontendUploadFieldContent)){
                $frontendUploadFieldContent = [];
            }
            $proOptions = $wpdb->get_row($wpdb->prepare("SELECT AllowUploadJPG, AllowUploadPNG, AllowUploadGIF, AllowUploadICO FROM $tablenameProOptions WHERE GalleryID = %d", $galeryID));
            $frontendUploadAllowUploadJPG = (!empty($proOptions) && isset($proOptions->AllowUploadJPG)) ? intval($proOptions->AllowUploadJPG) : 1;
            $frontendUploadAllowUploadPNG = (!empty($proOptions) && isset($proOptions->AllowUploadPNG)) ? intval($proOptions->AllowUploadPNG) : 1;
            $frontendUploadAllowUploadGIF = (!empty($proOptions) && isset($proOptions->AllowUploadGIF)) ? intval($proOptions->AllowUploadGIF) : 1;
            $frontendUploadAllowUploadICO = (!empty($proOptions) && isset($proOptions->AllowUploadICO)) ? intval($proOptions->AllowUploadICO) : 1;
            $frontendUploadImageUploadAllowed = (!empty($frontendUploadFieldContent['file-type-img']) && $frontendUploadFieldContent['file-type-img'] == 'img') ? true : false;

        }

        foreach($FILES[$keyFiles]['tmp_name'] as $key => $value){

            if(is_array($FILES[$keyFiles]['type'][$key])){// new processing
                $type = $FILES[$keyFiles]['type'][$key][0];
            }else{
                $type = $FILES[$keyFiles]['type'][$key];
            }

            if(empty($FILES[$keyFiles]['tmp_name'][$key])){
                continue;
            }

            if($isFrontendUpload){

                if(is_array($FILES[$keyFiles]['name'][$key])){// new processing
                    $fileExtensionForTypeCheck = strtolower(pathinfo($FILES[$keyFiles]['name'][$key][0], PATHINFO_EXTENSION));
                }else{
                    $fileExtensionForTypeCheck = strtolower(pathinfo($FILES[$keyFiles]['name'][$key], PATHINFO_EXTENSION));
                }

                $notAllowedFileType = '';
                if(($type == 'image/jpeg' || $type == 'image/jpg' || $type == 'image/x-citrix-jpeg' || $fileExtensionForTypeCheck == 'jpg' || $fileExtensionForTypeCheck == 'jpeg' || $fileExtensionForTypeCheck == 'jpe') && (!$frontendUploadImageUploadAllowed || empty($frontendUploadAllowUploadJPG))){
                    $notAllowedFileType = 'jpg';
                }elseif(($type == 'image/png' || $type == 'image/x-citrix-png' || $fileExtensionForTypeCheck == 'png') && (!$frontendUploadImageUploadAllowed || empty($frontendUploadAllowUploadPNG))){
                    $notAllowedFileType = 'png';
                }elseif(($type == 'image/gif' || $type == 'image/x-citrix-gif' || $fileExtensionForTypeCheck == 'gif') && (!$frontendUploadImageUploadAllowed || empty($frontendUploadAllowUploadGIF))){
                    $notAllowedFileType = 'gif';
                }elseif(($type == 'image/x-icon' || $type == 'image/vnd.microsoft.icon' || $fileExtensionForTypeCheck == 'ico') && (!$frontendUploadImageUploadAllowed || empty($frontendUploadAllowUploadICO))){
                    $notAllowedFileType = 'ico';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-pdf']) &&  $type == 'application/pdf'){
                    $notAllowedFileType = 'pdf';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-zip']) &&  ($type == 'application/x-zip-compressed' || $type == 'application/zip')){
                    $notAllowedFileType = 'zip';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-txt']) && $type == 'text/plain'){
                    $notAllowedFileType = 'txt';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-doc']) && $type == 'application/msword'){
                    $notAllowedFileType = 'doc';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-docx']) && $type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'){
                    $notAllowedFileType = 'docx';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-xls']) && $type == 'application/vnd.ms-excel'){
                    $notAllowedFileType = 'xls';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-xlsx']) && $type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
                    $notAllowedFileType = 'xlsx';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-csv']) && $type == 'text/csv'){
                    $notAllowedFileType = 'csv';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-mp3']) && $type == 'audio/mpeg'){
                    $notAllowedFileType = 'mp3';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-m4a']) && $type == 'audio/x-m4a'){
                    $notAllowedFileType = 'm4a';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-ogg']) && $type == 'audio/ogg'){
                    $notAllowedFileType = 'ogg';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-wav']) && $type == 'audio/wav'){
                    $notAllowedFileType = 'wav';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-mp4']) && $type == 'video/mp4'){
                    $notAllowedFileType = 'mp4';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-webm']) && $type == 'video/webm'){
                    $notAllowedFileType = 'webm';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-mov']) && $type == 'video/quicktime'){
                    $notAllowedFileType = 'mov';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-ppt']) && $type == 'application/vnd.ms-powerpoint'){
                    $notAllowedFileType = 'ppt';
                }elseif(empty($frontendUploadFieldContent['alternative-file-type-pptx']) && $type == 'application/vnd.openxmlformats-officedocument.presentationml.presentation'){
                    $notAllowedFileType = 'pptx';
               }

                if(!empty($notAllowedFileType)){
                    // check first of one of file types is not allowed
                    ?>
                    <script data-cg-processing="true">
                        var gid = <?php echo json_encode($galeryIDuser);?>;
                        cgJsData[gid].vars.upload.doneUploadFailed = true;
                        cgJsData[gid].vars.upload.failMessage = <?php echo json_encode("This filetype is not allowed: $notAllowedFileType");?>;
                    </script>
                    <?php
                    echo "This filetype is not allowed: $notAllowedFileType";
                    die;
                }

            }

            $getimagesize = false;

            if(
                    is_array($FILES[$keyFiles]['tmp_name'][$key]) && $type != 'application/pdf' && $type != 'application/x-zip-compressed' &&
                    $type != 'text/plain' && $type != 'application/msword' &&
                    $type != 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' && $type != 'application/vnd.ms-excel' &&
                    $type != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' && $type != 'text/csv' &&
                    $type != 'audio/mpeg' && $type != 'audio/x-m4a' &&
                    $type != 'audio/ogg' && $type != 'audio/wav' && $type != 'video/mp4' && $type != 'video/quicktime' && $type != 'video/webm' && $type != 'video/x-ms-wmv' && $type != 'video/avi' &&
                    $type != 'application/vnd.ms-powerpoint' && $type != 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ){// new processing
                $getimagesize = getimagesize($FILES[$keyFiles]['tmp_name'][$key][0]);
            }else{// old processing
                if(
                        $type != 'application/pdf' && $type != 'application/x-zip-compressed' && $type != 'application/zip' &&
                        $type != 'text/plain' && $type != 'application/msword' &&
                        $type != 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' && $type != 'application/vnd.ms-excel' &&
                        $type != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' && $type != 'text/csv' &&
                        $type != 'audio/mpeg' && $type != 'audio/x-m4a' &&
                        $type != 'audio/ogg' && $type != 'audio/wav' && $type != 'video/mp4' && $type != 'video/quicktime' && $type != 'video/webm' && $type != 'video/x-ms-wmv' && $type != 'video/avi' &&
                        $type != 'application/vnd.ms-powerpoint' && $type != 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                ){
                    $getimagesize = getimagesize($FILES[$keyFiles]['tmp_name'][$key]);
                }
            }

            if(empty($getimagesize) &&
                $type != 'application/pdf' && $type != 'application/x-zip-compressed' && $type != 'application/zip' &&
                $type != 'text/plain' && $type != 'application/msword' &&
                $type != 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' && $type != 'application/vnd.ms-excel' &&
                $type != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' && $type != 'text/csv' &&
                $type != 'audio/mpeg' && $type != 'audio/x-m4a' &&
                $type != 'audio/ogg' && $type != 'audio/wav' && $type != 'video/mp4' && $type != 'video/quicktime' && $type != 'video/webm' && $type != 'video/x-ms-wmv' && $type != 'video/avi' &&
                $type != 'application/vnd.ms-powerpoint' && $type != 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ){
                ?>
                <script data-cg-processing="true">
                    var gid = <?php echo json_encode($galeryIDuser);?>;
                    cgJsData[gid].vars.upload.doneUploadFailed = true;
                    cgJsData[gid].vars.upload.failMessage = <?php echo json_encode("This filetype is not allowed, please do not manipulate");?>;
                </script>
                <?php
                echo "This filetype is not allowed, please do not manipulate";
                die;
            }elseif(!empty($getimagesize['mime'])){
/*                if($getimagesize['mime']=='image/vnd.microsoft.icon'){
                    */?><!--
                    <script data-cg-processing="true">
                        var gid = <?php /*echo json_encode($galeryIDuser);*/?>;
                        cgJsData[gid].vars.upload.doneUploadFailed = true;
                        cgJsData[gid].vars.upload.failMessage = <?php /*echo json_encode("favicons are not allowed");*/?>;
                    </script>
                    --><?php
/*                    echo "favicons are not allowed";
                    die;
                }*/
            }

        }

        // only images allowed
        $allowedMimes = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            //'ico' => 'image/vnd.microsoft.icon', // image/x-icon works also for wordpress
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'mp3' => 'audio/mpeg',
            'm4a' => 'audio/x-m4a',
            'ogg' => 'audio/ogg',
            'wav' => 'audio/wav',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
          //  'wmv' => 'video/x-ms-wmv',
           // 'avi' => 'video/avi'
        );

        foreach($FILES[$keyFiles]['name'] as $key => $value){

            if(empty($FILES[$keyFiles]['tmp_name'][$key])){
                continue;
            }

            if(is_array($FILES[$keyFiles]['tmp_name'][$key])){// new processing
                $FILES[$keyFiles]['name'][$key][0] = sanitize_file_name($FILES[$keyFiles]['name'][$key][0]);
                $fileInfo = wp_check_filetype(basename($FILES[$keyFiles]['name'][$key][0]), $allowedMimes);
            }else{// old processing
                $FILES[$keyFiles]['name'][$key] = sanitize_file_name($FILES[$keyFiles]['name'][$key]);
                $fileInfo = wp_check_filetype(basename($FILES[$keyFiles]['name'][$key]), $allowedMimes);
            }

            if(empty($fileInfo['ext']) OR empty($fileInfo['type'])){
                ?>
                <script data-cg-processing="true">
                    var gid = <?php echo json_encode($galeryIDuser);?>;
                    cgJsData[gid].vars.upload.doneUploadFailed = true;
                    cgJsData[gid].vars.upload.failMessage = <?php echo json_encode("This file type is not allowed, please do not manipulate");?>;
                </script>
                <?php
                echo "This file type is not allowed, please do not manipulate";die;
            }

        }

        if(!empty($allowedImageSize)){

            foreach($FILES[$keyFiles]['size'] as $key => $value){

                if(is_array($value)){
                    if($value[0]>$allowedImageSize){// new processing
                        ?>
                        <script data-cg-processing="true">
                            var gid = <?php echo json_encode($galeryIDuser);?>;
                            cgJsData[gid].vars.upload.doneUploadFailed = true;
                            cgJsData[gid].vars.upload.failMessage = <?php echo json_encode("File size higher then allowed file size");?>;
                        </script>
                        <?php
                        echo "File size higher then allowed file size";die;
                    }
                }else{
                    if($value>$allowedImageSize){// old processing
                        ?>
                        <script data-cg-processing="true">
                            var gid = <?php echo json_encode($galeryIDuser);?>;
                            cgJsData[gid].vars.upload.doneUploadFailed = true;
                            cgJsData[gid].vars.upload.failMessage = <?php echo json_encode("File size higher then allowed file size");?>;
                        </script>
                        <?php
                        echo "File size higher then allowed file size";die;
                    }
                }
            }
        }

        return $FILES;
    }
}

