<?php

// post_cg_add_openai_image
add_action('wp_ajax_post_cg_add_openai_image', 'post_cg_add_openai_image');
if (!function_exists('post_cg_add_openai_image')) {
    function post_cg_add_openai_image() {

        cg_check_nonce();

        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {

                $urlRaw = '';
                // has to be done before sanitizing! Otherwise URL can't be called after sanitizing
                // will be only used to call OpenAI URL
                if (!empty($_POST['cg_openai_image_url'])) {
                    $urlRaw = $_POST['cg_openai_image_url'];
                }

                $imageUrl = esc_url_raw(trim($urlRaw));

                $isDataImage = (strpos($urlRaw, 'data:image/') === 0);

                if ($isDataImage) {

                    $imageUrl = $urlRaw;

                    // English comment: Accept only data:image/<type>;base64,<data>
                    if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/i', $imageUrl)) {
                        ?>
                        <script data-cg-processing="true">
                            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = false;
                            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = <?php echo json_encode("<h2>MISSINGRIGHTS<br>Invalid base64 image format.</h2>"); ?>;
                        </script>
                        <?php
                        exit();
                    }

                    $parts = explode(',', $imageUrl, 2);
                    if (count($parts) !== 2) {
                        ?>
                        <script data-cg-processing="true">
                            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = false;
                            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = <?php echo json_encode("<h2>MISSINGRIGHTS<br>Invalid base64 image data.</h2>"); ?>;
                        </script>
                        <?php
                        exit();
                    }

                    $decoded = base64_decode($parts[1], true);
                    if ($decoded === false) {
                        ?>
                        <script data-cg-processing="true">
                            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = false;
                            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = <?php echo json_encode("<h2>MISSINGRIGHTS<br>Invalid base64 image data.</h2>"); ?>;
                        </script>
                        <?php
                        exit();
                    }

                    // English comment: Size limit (example 10 MB) to prevent disk/memory abuse
                    if (strlen($decoded) > (10 * 1024 * 1024)) {
                        ?>
                        <script data-cg-processing="true">
                            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = false;
                            cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = <?php echo json_encode("<h2>MISSINGRIGHTS<br>Base64 image too large.</h2>"); ?>;
                        </script>
                        <?php
                        exit();
                    }

                } elseif(empty($imageUrl) || !cg1l_is_safe_remote_url($imageUrl)) {
                    ?>
                    <script data-cg-processing="true">
                        cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = false;
                        cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = <?php echo json_encode("<h2>MISSINGRIGHTS<br>Blocked URL (SSRF protection).</h2>"); ?>;
                    </script>
                    <?php
                    exit();
                }

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

                    // always png if nothing not other told, but jpeg and webp are possible if told via output_format
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
                    }elseif(strpos($imageUrl, 'data:image/png;base64,')!==false){
                        $content = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageUrl));
                        $formImage = imagecreatefromstring($content);
                        imagesavealpha($formImage,true);// required for png images... otherwise background black
                        imagepng($formImage,$fullPath);
                    }else{
                        // Initialize cURL session
                        /*$ch = curl_init($imageUrl);

                        $fp = fopen($fullPath, 'wb');

                        $default_socket_timeout = (int)(ini_get('default_socket_timeout'));
                        $max_execution_time = (int)(ini_get('max_execution_time'));

                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);

                        // SSRF hardening: do NOT follow redirects (prevents redirect-to-localhost bypass)
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

                        // SSRF hardening: allow only http/https
                        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

                        // Keep your timeouts (but avoid negatives)
                        $ct = $default_socket_timeout > 2 ? ($default_socket_timeout - 1) : 10;
                        $to = $max_execution_time > 2 ? ($max_execution_time - 1) : 20;

                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $ct);
                        curl_setopt($ch, CURLOPT_TIMEOUT, $to);

                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Validate SSL certificate
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    // Also verify host name

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
                        }*/

                        // Timeouts berechnen (wie in deinem Original)
                        // Calculate timeouts based on server settings
                        $default_socket_timeout = (int)(ini_get('default_socket_timeout'));
                        $max_execution_time = (int)(ini_get('max_execution_time'));
                        $ct = $default_socket_timeout > 2 ? ($default_socket_timeout - 1) : 10;
                        $to = $max_execution_time > 2 ? ($max_execution_time - 1) : 20;

                        // Path security: Prevent Directory Traversal attacks
                        if (empty($fullPath) || strpos($fullPath, '..') !== false) {
                            $cgAddOpenAiImageErrorMessage = 'Security Error: Invalid file path.';
                            $cgAddOpenAiImageIsValid = false;
                        } else {
                            // wp_safe_remote_get replaces cURL and provides native SSRF protection as requested by Patchstack
                            // if dynamic url from unknown source, then better to use wp_safe_remote_get
                            $response = wp_safe_remote_get($imageUrl, [
                                'timeout'     => $to, // WordPress uses a single combined timeout
                                'redirection' => 0,   // Equivalent to CURLOPT_FOLLOWLOCATION => false (SSRF Hardening)
                                'sslverify'   => true // Equivalent to CURLOPT_SSL_VERIFYPEER
                            ]);

                            if (is_wp_error($response)) {
                                $cgAddOpenAiImageErrorMessage = 'Failed to download image: ' . $response->get_error_message();
                                $cgAddOpenAiImageIsValid = false;
                            } else {
                                $httpCode   = wp_remote_retrieve_response_code($response);
                                $image_data = wp_remote_retrieve_body($response);

                                if ($httpCode !== 200 || empty($image_data)) {
                                    $cgAddOpenAiImageErrorMessage = 'Failed to download image: HTTP code ' . $httpCode;
                                    $cgAddOpenAiImageIsValid = false;
                                } else {
                                    // Content Validation: Verify the actual file content (MIME-Type)
                                    $is_valid_image = false;

                                    if (class_exists('finfo')) {
                                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                                        $mime_type = $finfo->buffer($image_data);
                                        $is_valid_image = in_array($mime_type, ['image/png', 'image/jpeg', 'image/jpg', 'image/webp']);
                                    } else {
                                        // Fallback: Use getimagesizefromstring if finfo extension is missing
                                        $image_info = getimagesizefromstring($image_data);
                                        $is_valid_image = ($image_info !== false);
                        }

                                    if (!$is_valid_image) {
                                        $cgAddOpenAiImageErrorMessage = 'Invalid file type. Only PNG, JPG, and WEBP are allowed.';
                                        $cgAddOpenAiImageIsValid = false;
                                    } else {
                                        // Securely save the validated data to the disk
                                        file_put_contents($fullPath, $image_data);
                                    }
                                }
                            }
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

            } else {
                ?>
                <script data-cg-processing="true">
                    cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = false;
                    cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = <?php echo json_encode("<h2>MISSINGRIGHTS<br>post_cg_add_openai_image can be edited only as administrator, editor or author.</h2>"); ?>;
                </script>
                <?php
                exit();
            }

            exit();
        } else {
            exit();
        }

    }
}