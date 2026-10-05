<?php
if(!defined('ABSPATH')){exit;}
require_once __DIR__.'/cg-profile-image-upload.php';

add_action('cg_registry_add_profile_image','cg_registry_add_profile_image');
if(!function_exists('cg_registry_add_profile_image')){
    function cg_registry_add_profile_image($filesKey, $WpUserId = 0, $isMediaHandleUploadOnly = false, $isReturnAttachId = false, $attach_id = 0){

        global $wpdb;
        $table_posts = $wpdb->prefix."posts";
        $tablename = $wpdb->base_prefix . "contest_gal1ery";

        $created = empty($attach_id);
        if($created){
            $file = cg_profile_image_upload_file(isset($_FILES[$filesKey]) ? $_FILES[$filesKey] : null);
            $valid = cg_profile_image_validate_upload($file);
            if(is_wp_error($valid)){return $valid;}
            $_FILES[$filesKey] = $file;
            require_once ABSPATH.'wp-admin/includes/image.php';
            require_once ABSPATH.'wp-admin/includes/file.php';
            require_once ABSPATH.'wp-admin/includes/media.php';
            $post_data = array('post_author'=>$WpUserId,'post_content'=>'Contest Gallery user profile image');
            $attach_id = media_handle_upload($filesKey,0,$post_data,array('test_form'=>false,'mimes'=>$valid['mimes']));
            if(is_wp_error($attach_id)){return cg_profile_image_upload_error($attach_id);}

        }

        if($isMediaHandleUploadOnly){
            if($isReturnAttachId){
                return $attach_id;
            }else{
                return true;
            }
        }

        $Version = cg_get_version_for_scripts();

        $wp_image_info = $wpdb->get_row("SELECT * FROM $table_posts WHERE ID = '$attach_id'");
        $post_type = $wp_image_info->post_mime_type;
        $post_title = $wp_image_info->post_title;
        $wp_image_id = $wp_image_info->ID;

        $imageInfoArray = wp_get_attachment_image_src($wp_image_id,'full');
        $current_width = $imageInfoArray[1];
        $current_height = $imageInfoArray[2];

        $unix = time();
        $unixadd = $unix+2;

        $dateityp = '';

        if(strpos($post_type,'jpg')!==false OR strpos($post_type,'jpeg')!==false){
            $dateityp = 'jpg';
        }elseif(strpos($post_type,'png')!==false){
            $dateityp = 'png';
        }elseif(strpos($post_type,'gif')!==false){
            $dateityp = 'gif';
        }

        $dateiname = $post_title;

        $userIP = cg1l_sanitize_method(cg_get_user_ip());

        // updating string after all the 0 at the end does not work. That is why Version is not inserted there
        // default 0 to countr1-5 was added lately on 15.05.2020
        $inserted = $wpdb->query( $wpdb->prepare(
            "
                INSERT INTO $tablename
                ( id, rowid, Timestamp, NamePic,
                ImgType, CountC, CountR, Rating,
                GalleryID, Active, Informed, WpUpload, Width, Height, WpUserId, IP,
                CountR1,CountR2,CountR3,CountR4,CountR5,IsProfileImage,Version)
                VALUES ( %s,%s,%d,%s,
                %s,%d,%s,%s,
                %d,%s,%s,%s,%s,%s,%s,%s,
                %d,%d,%d,%d,%d,%d,%s)
            ",
            '','',$unixadd,$dateiname,
            $dateityp,0,'','',
            0,'','',$wp_image_id,$current_width,$current_height,$WpUserId,$userIP,
            0,0,0,0,0,1,$Version
        ) );

        if($inserted===false && $created){wp_delete_attachment($attach_id,true);}
        return $inserted !== false;

        // not required to use this logic in the moment
        //$imgSrcLarge = wp_get_attachment_image_src($wp_image_id, 'large');

    }
}


?>
