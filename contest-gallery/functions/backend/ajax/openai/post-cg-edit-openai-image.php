<?php

// post_cg_edit_openai_image
add_action('wp_ajax_post_cg_edit_openai_image', 'post_cg_edit_openai_image');
if (!function_exists('post_cg_edit_openai_image')) {
    function post_cg_edit_openai_image() {

        cg_check_nonce();

        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {
                $missingRights = false;
                if (empty($_POST['cg_images']) || !is_array($_POST['cg_images'])) { $missingRights = true; }
                else{
                    foreach ($_POST['cg_images'] as $key => $value) {
                        $id = !empty($value['id']) ? intval($value['id']) : 0;
                        if ($id < 1) { $missingRights = true; break; }
                        if (get_post_type($id) !== 'attachment') { $missingRights = true; break;}
                        if (!current_user_can('read_post', $id)) { $missingRights = true; break; }
                    }
                }

                if($missingRights){
                    ?>
                    <script data-cg-processing="true">
                        cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = false;
                        cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = <?php echo json_encode("<h2>MISSINGRIGHTS<br>post_cg_edit_openai_image error. Logged in user not allowed to read file.</h2>"); ?>;
                    </script>
                    <?php
                    exit();
                }

                $_POST = cg1l_sanitize_post($_POST);

                /*echo "<pre>";
                print_r($_POST);
                echo "</pre>";*/

                global $wpdb;
                $tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
                $apiKey = $wpdb->get_var("SELECT OpenAiKey FROM $tablename_pro_options WHERE GeneralID = 1");

                $prompt = $_POST['cgOpenAiPromptInput'];
                // $prompt = 'Batman in a beautiful landscape of mountains waterfall and forrest';
                $cgOpenAiGenIsValid = false;
                $cgOpenAiGenErrorMessage = '';
                $cgOpenAiGenUrl = '';
                $cgOpenAiLastPrompt = false;

                $default_socket_timeout = (int)(ini_get('default_socket_timeout'));
                $max_execution_time = (int)(ini_get('max_execution_time'));

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/images/edits');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, ($default_socket_timeout-1));
                curl_setopt($ch, CURLOPT_TIMEOUT, ($max_execution_time-1));

                $_POST['cg_ai_model'] =  'gpt-image-1';

                if(cg_get_version()!='contest-gallery-pro'){
                    if(strpos($_POST['cg_ai_res'],'1536')!==false || strpos($_POST['cg_ai_res'],'1792')!==false){
                        $_POST['cg_ai_res'] = '1024x1024';
                    }
                    if(strpos($_POST['cg_ai_quality'],'high')!==false){
                        $_POST['cg_ai_quality'] = 'medium';
                    }
                }

                $postFields = [
                    'model' => $_POST['cg_ai_model'],
                    /*'prompt' => 'Generate a photorealistic image of a gift basket on a white background labeled "Relax & Unwind" with a ribbon and handwriting-like font, containing all the items in the reference pictures',*/
                    // 'prompt' => 'Generate a photorealistic image of a gift basket labeled "Relax & Unwind" with a ribbon and handwriting-like font, containing all the items in the reference pictures. Add nature background with waterfall at night and moon.',
                    'prompt' =>$prompt,
                    //'image[]' => new CURLFile(__DIR__.'/body-lotion.png', 'image/png', 'body-lotion.png'),
                    //'image[]' => new CURLFile(__DIR__.'/bath-bomb.png', 'image/png', 'bath-bomb.png'),
                    //'image[]' => new CURLFile(__DIR__.'/incense-kit.png', 'image/png', 'incense-kit.png'),
                    //'image[]' => new CURLFile(__DIR__.'/soap.png', 'image/png', 'soap.png'),
                    //'size' => '1024x1536',
                    //  'size' => $_POST['cg_ai_res'],
                    //'quality' => 'medium',
                    'quality' => $_POST['cg_ai_quality'],
                ];

                //$postFields['image'] = [];

                foreach ($_POST['cg_images'] as $key => $value) {
                    $fullsizepath = get_attached_file( $value['id'] );
                    //$postFields['image'][] = new CURLFile($fullsizepath, $value['mime'] , $value['filename']);
                    $postFields['image['.$key.']'] = new CURLFile($fullsizepath, $value['mime'] , $value['filename']);
                }

                /*echo "<pre>";
                print_r($postFields);
                echo "</pre>";*/

                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiKey
                ]);


                $response = curl_exec($ch);
                $curlErrorNo = curl_errno($ch);
                $curlErrorMsg = curl_error($ch);

                curl_close($ch);

                if($curlErrorMsg){
                    $cgOpenAiGenErrorMessage = $curlErrorMsg;
                    if(strpos($cgOpenAiGenErrorMessage, 'time')!==false){
                        $cgOpenAiGenErrorMessage .= '<br>if it is a timeout reason so increase that values in your .htaccess file<br>'.
                            'php_value max_execution_time 240<br>php_value default_socket_timeout 240<br>
                    240 seconds and higher is the recommend value to get the response from OpenAI.';
                    }
                }else{
                    //var_dump('$response');
                    //var_dump($response);
                    if (!$response) {
                        // echo json_encode(['error' => 'API request failed']);
                        $cgOpenAiGenErrorMessage = 'API request failed';
                    }else{
                        $json = json_decode($response, true);
                        /*var_dump('$json123');
                        var_dump($json);
                        echo "<pre>";
                            print_r($json);
                        echo "</pre>";*/
                        if (isset($json['data'][0]['b64_json'])) {
                            $cgOpenAiGenUrl = 'data:image/png;base64,'.$json['data'][0]['b64_json'];
                            $cgOpenAiGenIsValid = true;
                        } else {
                            //$json = json_decode($response, true);
                            //var_dump($json);
                            //echo json_encode(['error' => 'Image not generated.']);
                            if (!empty($json['error']['message'])) {
                                $cgOpenAiGenErrorMessage = $json['error']['message'];
                            }else {
                                $cgOpenAiGenErrorMessage = 'Error: image not generated.<br>The prompt might not comply with OpenAI policies.<br><a href="https://openai.com/policies/creating-images-and-videos-in-line-with-our-policies/" target="_blank" >https://openai.com/policies/creating-images...</a>';
                            }
                        }
                        $cgOpenAiLastPrompt = cg_ai_insert_prompt($prompt,$cgOpenAiGenIsValid,'',2);
                    }
                }

                ?>
                <script data-cg-processing="true">
                    cgJsClassAdmin.gallery.vars.cgOpenAiGenIsValid = <?php echo json_encode($cgOpenAiGenIsValid); ?>;
                    cgJsClassAdmin.gallery.vars.cgOpenAiGenErrorMessage = <?php echo json_encode($cgOpenAiGenErrorMessage); ?>;
                    cgJsClassAdmin.gallery.vars.cgOpenAiGenUrl = <?php echo json_encode($cgOpenAiGenUrl); ?>;
                    cgJsClassAdmin.gallery.vars.cgOpenAiLastPrompt = <?php echo json_encode($cgOpenAiLastPrompt); ?>;
                </script>
                <?php

            } else {
                ?>
                <script data-cg-processing="true">
                    cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = false;
                    cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = <?php echo json_encode("<h2>MISSINGRIGHTS<br>post_cg_edit_openai_image can be edited only as administrator, editor or author.</h2>"); ?>;
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