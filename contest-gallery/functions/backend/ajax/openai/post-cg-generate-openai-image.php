<?php


// post_cg_generate_openai_image
add_action('wp_ajax_post_cg_generate_openai_image', 'post_cg_generate_openai_image');
if (!function_exists('post_cg_generate_openai_image')) {
    function post_cg_generate_openai_image() {

        /*
        echo "<pre>";
            print_r($_POST);
        echo "</pre>";
        */

        $_POST = cg1l_sanitize_post($_POST);

        global $wpdb;
        $tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";

        $apiKey = $wpdb->get_var("SELECT OpenAiKey FROM $tablename_pro_options WHERE GeneralID = 1");

        $prompt = $_POST['cgOpenAiPromptInput'];
        // $prompt = 'Batman in a beautiful landscape of mountains waterfall and forrest';
        $cgOpenAiGenIsValid = false;
        $cgOpenAiGenErrorMessage = '';
        $cgOpenAiGenUrl = '';
        $cgOpenAiLastPrompt = false;

        $ch = curl_init();

        $default_socket_timeout = (int)(ini_get('default_socket_timeout'));
        $max_execution_time = (int)(ini_get('max_execution_time'));

        //$default_socket_timeout = 2;
        //$max_execution_time = 3;

        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/images/generations');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, ($default_socket_timeout-1));
        curl_setopt($ch, CURLOPT_TIMEOUT, ($max_execution_time-1));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);

        if(cg_get_version()!='contest-gallery-pro'){
            if(strpos($_POST['cg_ai_res'],'1536')!==false || strpos($_POST['cg_ai_res'],'1792')!==false){
                $_POST['cg_ai_res'] = '1024x1024';
            }
        }

        $data = [
            //  'model' => 'dall-e-3',
            'model' => $_POST['cg_ai_model'],
            'prompt' => $prompt,
            'n' => 1,
            //'quality' => 'low',// for chat gpt-image-1
            //'output_format' => 'png',// for chat gpt-image-1
            //  'size' => '1024x1024',
            'size' => $_POST['cg_ai_res'],
        ];

        if(!empty($_POST['cg_ai_quality'])){
            $data['quality'] = $_POST['cg_ai_quality'];
        }else{
            $_POST['cg_ai_quality'] = '';
        }

        /*
        var_dump('$data');
        echo "<pre>";
        print_r($_POST);
        print_r($data);
        echo "</pre>";
        die;*/

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

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
                /*var_dump('$json');
                echo "<pre>";
                    print_r($json);
                echo "</pre>";*/
                $revisedPrompt = '';
                if (isset($json['data'][0]['url'])) {
                    //echo json_encode(['imageUrl' => $json['data'][0]['url']]);
                    //$cgOpenAiGenUrl = json_encode($json['data'][0]['url']);
                    //$cgOpenAiGenUrl = str_replace('&quot;','',str_replace('"', '', stripslashes(urldecode( $cgOpenAiGenUrl ))));
                    $cgOpenAiGenUrl = $json['data'][0]['url'];
                    // var_dump($cgOpenAiGenUrl);
                    //   die;
                    $cgOpenAiGenIsValid = true;
                    $revisedPrompt = $json['data'][0]['revised_prompt'];
                } else if (isset($json['data'][0]['b64_json'])) {
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
                $cgOpenAiLastPrompt = cg_ai_insert_prompt($prompt,$cgOpenAiGenIsValid,$revisedPrompt,1);
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

    }
}