<?php
if(!function_exists('cg_create_watermarked_image_and_return_attach_id')){
    function cg_create_watermarked_image_and_return_attach_id($currentUploadDir,$wp_upload_dir){

        $type = $_POST['base64WatermarkedImageType'];
        $content = file_get_contents($_POST['base64WatermarkedImageNew']);

        /*        $watermarkedUploadDir = $currentUploadDir."/watermarked";
                if(!is_dir($watermarkedUploadDir)){
                    mkdir($watermarkedUploadDir,0755);
                }*/

        $newFilename = $_POST['WpUploadName'].'-watermarked';
        $fullNewPath = $currentUploadDir."/$newFilename.$type";
        file_put_contents($fullNewPath,$content);

        $attachment = array(
            'guid' => $wp_upload_dir['url']."/".$newFilename.'.'.$type,
            'post_mime_type' => $_POST['WpUploadType'],
            'post_title' => $_POST['WpUploadTitle'],
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment( $attachment, $fullNewPath );
        $imagenew = get_post( $attach_id );
        $fullsizepath = get_attached_file( $imagenew->ID );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        return $attach_id;

    }
}