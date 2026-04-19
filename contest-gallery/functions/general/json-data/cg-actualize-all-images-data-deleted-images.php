<?php

add_action('cg_actualize_all_images_data_deleted_images','cg_actualize_all_images_data_deleted_images');

if(!function_exists('cg_actualize_all_images_data_deleted_images')){
    function cg_actualize_all_images_data_deleted_images($GalleryID){

        $wp_upload_dir = wp_upload_dir();
        $jsonFileDeleteImageIds = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-deleted-image-ids.json';

        if(file_exists($jsonFileDeleteImageIds)){

            $fp = fopen($jsonFileDeleteImageIds, 'r');
            $imageIds = json_decode(fread($fp, filesize($jsonFileDeleteImageIds)),true);
            fclose($fp);
            $hasDeletedImagesActualized = false;

            if(!empty($imageIds)){

                foreach ($imageIds as $imageId){

                    $imageId = absint($imageId);

                    if(empty($imageId)){
                        continue;
                    }

                    $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-comments/image-comments-'.$imageId.'.json';
                    if(file_exists($jsonFile)){
                        unlink($jsonFile);
                    }
                    $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-data/image-data-'.$imageId.'.json';
                    if(file_exists($jsonFile)){
                        unlink($jsonFile);
                    }
                    $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-stats/image-stats-'.$imageId.'.json';
                    if(file_exists($jsonFile)){
                        unlink($jsonFile);
                    }
                    $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-info/image-info-'.$imageId.'.json';
                    if(file_exists($jsonFile)){
                        unlink($jsonFile);
                    }

                    if(!empty($imagesInfosArray)){
                        if(!empty($imagesInfosArray[$imageId])){
                            unset($imagesInfosArray[$imageId]);
                        }
                    }

                    if(!empty($sortValuesArray)){
                        if(!empty($sortValuesArray[$imageId])){
                            unset($sortValuesArray[$imageId]);
                        }
                    }

                    cg1l_push_recent_id_file_all_types($GalleryID,$imageId,true);
                    $hasDeletedImagesActualized = true;

                }

            }

            if($hasDeletedImagesActualized){
                cg1l_create_last_updated_time_file_all($GalleryID);
            }

            unlink($jsonFileDeleteImageIds);

        }

    }
}
