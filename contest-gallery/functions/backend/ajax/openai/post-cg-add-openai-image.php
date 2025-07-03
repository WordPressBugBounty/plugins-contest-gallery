<?php

// post_cg_add_openai_image
add_action('wp_ajax_post_cg_add_openai_image', 'post_cg_add_openai_image');
if (!function_exists('post_cg_add_openai_image')) {
    function post_cg_add_openai_image() {

        // has to be done before sanitizing! Otherwise URL can't be called after sanitizing
        // will be only used to call OpenAI URL
        $imageUrl = $_POST['cg_openai_image_url'];

        $_POST = cg1l_sanitize_post($_POST);
        $wp_upload_dir = wp_upload_dir();

        $fullName = $_POST['cg_openai_image_name'];
        $GalleryID = absint($_POST['cgGalleryID']);

        $cgAddOpenAiImageIsValid = true;
        $cgAddOpenAiImageErrorMessage = '';

        try {

            $fullNamePath = $fullName;
            $fullNamePath = cg_pre_process_name_for_url_name($fullNamePath);
            $fullNamePath = cg_check_first_char_for_url_name_after_pre_processing($fullNamePath);
            $fullNamePath = cg_check_last_char_for_url_name_after_pre_processing($fullNamePath);
            $fullNamePath = cg_sluggify_for_url($fullNamePath);// has to be tested with asia chars one time
            $fullNamePathFirst = $fullNamePath;

            //var_dump('$fullName');
            //var_dump($fullName);

            $fullPath = $wp_upload_dir['basedir'].$wp_upload_dir['subdir'].'/'.$fullNamePathFirst.'.png';
            //var_dump('$fullPath check');
            //var_dump($fullPath);
            if(file_exists($fullPath)){
                //var_dump(112233);
                $i = 0;
                do{
                    if($i==0){
                        $i = 1;
                    }else{
                        $i++;
                    }
                    $add = '-'.$i;
                    $fullNamePath = $fullNamePathFirst.$add;
                    $fullPath = $wp_upload_dir['basedir'].$wp_upload_dir['subdir'].'/'.$fullNamePath.'.png';
                }while(file_exists($fullPath));
            }

            if(!empty($_POST['cg_is_edit_image'])){
                $content = file_get_contents(__DIR__.'/openai.txt');
                $content = base64_decode($content);
                $formImage = imagecreatefromstring($content);
                imagesavealpha($formImage,true);// required for png images... otherwise background black
                imagepng($formImage,$fullPath);
            }else if(strpos($imageUrl, 'data:image/png;base64,')!==false){
                $content = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageUrl));
                $formImage = imagecreatefromstring($content);
                imagesavealpha($formImage,true);// required for png images... otherwise background black
                imagepng($formImage,$fullPath);
            }else{
                // Initialize cURL session
                $ch = curl_init($imageUrl);
                $fp = fopen($fullPath, 'wb');

                $default_socket_timeout = (int)(ini_get('default_socket_timeout'));
                $max_execution_time = (int)(ini_get('max_execution_time'));

                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, ($default_socket_timeout-1));
                curl_setopt($ch, CURLOPT_TIMEOUT, ($max_execution_time-1));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Validate SSL certificate

                $result = curl_exec($ch);
                $error = curl_error($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                curl_close($ch);
                fclose($fp);

                // Check for errors
                if (!$result || $httpCode !== 200) {
                    unlink($fullPath); // Remove the incomplete file
                    //echo json_encode([
                    // 'error' => 'Failed to download image: ' . ($error ?: 'HTTP code ' . $httpCode)
                    //]);
                    $cgAddOpenAiImageErrorMessage = 'Failed to download image: ' . ($error ?: 'HTTP code ' . $httpCode);
                    $cgAddOpenAiImageIsValid = false;
                }

            }

            if($cgAddOpenAiImageIsValid){
                $attachment = [
                    'guid' => $wp_upload_dir['url']."/".$fullNamePath.'.png',
                    'post_mime_type' => 'image/png',
                    'post_title' => (!empty($_POST['cgOpenAiTitle'])) ? contest_gal1ery_convert_for_html_output_without_nl2br($_POST['cgOpenAiTitle']) : $fullName,
                    'post_content' => contest_gal1ery_convert_for_html_output_without_nl2br($_POST['cgOpenAiDescription']),
                    'post_excerpt' => contest_gal1ery_convert_for_html_output_without_nl2br($_POST['cgOpenAiCaption']),
                    'post_status' => 'inherit'
                ];

                $attach_id = wp_insert_attachment( $attachment, $fullPath );
                $imagenew = get_post( $attach_id );
                $fullsizepath = get_attached_file( $imagenew->ID );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                add_post_meta( $attach_id, '_wp_attachment_image_alt', contest_gal1ery_convert_for_html_output_without_nl2br($_POST['cgOpenAiAltText']));
            }

            if(!empty($_POST['cg_openai_image_is_add_to_gallery'])){
                $cg_wp_upload_ids = [$attach_id];
                $_POST['action2'] = $GalleryID;
                require_once(__DIR__.'/../v10/v10-admin/gallery/wp-uploader.php');
            }

        }catch (Exception $e) {
            $cgAddOpenAiImageIsValid = false;
            $cgAddOpenAiImageErrorMessage = $e->getMessage();
        }

        ?>
        <script data-cg-processing="true">
            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = <?php echo json_encode($cgAddOpenAiImageIsValid); ?>;
            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = <?php echo json_encode($cgAddOpenAiImageErrorMessage); ?>;
        </script>
        <?php

    }
}