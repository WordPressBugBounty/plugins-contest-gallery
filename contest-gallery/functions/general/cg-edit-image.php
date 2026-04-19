<?php

add_action('cg_edit_images','cg_edit_images');
if(!function_exists('cg_edit_images')){
    function cg_edit_images($GalleryID,$imageId,$imageArray,$isSetCategory){
        $upload_dir = wp_upload_dir();
        if($isSetCategory){
            // since 21.2.4 will be saved in certain image file
            $jsonFile = $upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-data/image-data-".$imageId.".json";
            $fp = fopen($jsonFile, 'w');
            fwrite($fp, json_encode($imageArray));
            fclose($fp);
            cg1l_push_recent_id_file($GalleryID,$imageId,'image-main-data-last-update');
            cg1l_create_last_updated_time_file($GalleryID,'image-main-data-last-update');
        }
    }
}

?>
