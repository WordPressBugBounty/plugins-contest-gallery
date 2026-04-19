<?php

if(!function_exists('cg_is_alternative_file_type')){
    function cg_is_alternative_file_type($ImgType){

        if($ImgType=='pdf' || $ImgType=='zip' || $ImgType=='txt' || $ImgType=='doc' || $ImgType=='docx' || $ImgType=='xls' || $ImgType=='xlsx' ||
            $ImgType=='csv' || $ImgType=='mp3' || $ImgType=='m4a' || $ImgType=='ogg' || $ImgType=='wav' || $ImgType=='mp4' ||
            $ImgType=='mov' || $ImgType=='avi' || $ImgType=='wmv' || $ImgType=='webm' || $ImgType=='ppt' || $ImgType=='pptx' ){
                return true;
        }

        return false;

    }
}

if(!function_exists('cg_is_alternative_file_type_file')){
    function cg_is_alternative_file_type_file($ImgType){

        if($ImgType=='pdf' || $ImgType=='zip' || $ImgType=='txt' || $ImgType=='doc' || $ImgType=='docx' || $ImgType=='xls' || $ImgType=='xlsx' ||
            $ImgType=='csv' || $ImgType=='mp3' || $ImgType=='m4a' || $ImgType=='ogg' || $ImgType=='wav' ||
            $ImgType=='ppt' || $ImgType=='pptx' ){
                return true;
        }

        return false;

    }
}

if(!function_exists('cg_is_alternative_file_type_video')){
    function cg_is_alternative_file_type_video($ImgType){

        if($ImgType=='mp4' || $ImgType=='mov' || $ImgType=='avi' || $ImgType=='wmv' || $ImgType=='webm'){
            return true;
        }
        return false;

    }
}
if(!function_exists('cg_is_alternative_file_type_audio')){
    function cg_is_alternative_file_type_audio($ImgType){

        if($ImgType=='mp3' || $ImgType=='wav' || $ImgType=='m4a' || $ImgType=='ogg'){
            return true;
        }

        return false;

    }
}

if(!function_exists('cg_is_is_image')){
    function cg_is_is_image($ImgType){
        // !important, do not remove jpeg, is for additional files type!!!
        if($ImgType=='jpeg' || $ImgType=='jpg' || $ImgType=='png' || $ImgType=='gif' || $ImgType=='ico'){
            return true;
        }
        return false;
    }
}

if(!function_exists('cgl_is_img_type_embed')){
    function cgl_is_img_type_embed($ImgType){
        if($ImgType=='tkt' || $ImgType=='ytb' || $ImgType=='inst' || $ImgType=='twt'){
            return true;
        }
        return false;
    }
}

if(!function_exists('cg1l_get_encoding_formats')){
    function cg1l_get_encoding_formats($ImgType){
        $cg1l_encoding_formats = [
            'txt'  => 'text/plain',
            'csv'  => 'text/csv',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pdf'  => 'application/pdf',
            'zip'  => 'application/zip',
            'mp3'  => 'audio/mpeg',
            'm4a'  => 'audio/mp4',
            'ogg'  => 'audio/ogg',
            'wav'  => 'audio/wav',
            'mp4'  => 'video/mp4',
            'mov'  => 'video/quicktime',
            'avi'  => 'video/x-msvideo',
            'wmv'  => 'video/x-ms-wmv',
            'webm' => 'video/webm'
        ];
        if(!empty($cg1l_encoding_formats[strtolower($ImgType)])){
            return $cg1l_encoding_formats[strtolower($ImgType)];
        }else{
            return 'format not exists';
        }
    }
}

if(!function_exists('cgl_check_if_embeded')){
    function cgl_check_if_embeded($ImgType){
        // !important, do not remove jpeg, is for additional files type!!!
        if($ImgType=='ytb' || $ImgType=='twt' || $ImgType=='inst' || $ImgType=='tkt'){
            return true;
        }
        return false;
    }
}

if(!function_exists('cg1l_get_itemtype_object')){
    function cg1l_get_itemtype_object($ImgType){
        $itemTypeObject = 'Creative';
        if(cg_is_is_image($ImgType)){
            $itemTypeObject = 'Image';
        } elseif (cg_is_alternative_file_type_video($ImgType)){
            $itemTypeObject = 'Video';
        } elseif(cg_is_alternative_file_type_file($ImgType)){
            $itemTypeObject = 'Digital';
            if(in_array($ImgType,['mp3','m4a','ogg','wav'])!==false){
                $itemTypeObject = 'Audio';
            }elseif($ImgType=='zip'){
                $itemTypeObject = 'Media';
            }
        } elseif(cgl_check_if_embeded($ImgType)){
            if($ImgType=='ytb'){
                $itemTypeObject = 'Video';
            }else{
                $itemTypeObject = 'SocialMediaPosting';
            }
        }
        return $itemTypeObject;
    }
}



?>