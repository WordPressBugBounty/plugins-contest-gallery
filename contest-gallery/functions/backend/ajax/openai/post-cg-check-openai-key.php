<?php

// post_cg_check_openai_key
add_action('wp_ajax_post_cg_check_openai_key', 'post_cg_check_openai_key');
if (!function_exists('post_cg_check_openai_key')) {
    function post_cg_check_openai_key() {

        $apiKey = cg1l_sanitize_method($_GET['cgOpenAiKey']);
        $cgOpenAiKeyIsValid = false;
        $cgOpenAiKeyErrorMessage = '';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/models');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200) {
            echo '###cgkeytrue###';
            return;
            /*
           $cgOpenAiKeyIsValid = true;
           echo "✅ API key is valid.\n\n";
           $data = json_decode($response, true);
           echo "Models available:\n";
           foreach ($data['data'] as $model) {
               echo "- " . $model['id'] . "\n";
           }*/
        } elseif ($httpCode === 401) {
            return;
            //echo "❌ Invalid API key (401 Unauthorized).\n";
            $cgOpenAiKeyErrorMessage = '❌ Invalid API key (401 Unauthorized).';
        } else {
            return;
            //echo "⚠️ Request failed. HTTP code: $httpCode\n";
            //echo "cURL error: $error\n";
            $cgOpenAiKeyErrorMessage = "⚠️ Request failed. HTTP code: $httpCode<br>cURL error: $error";
        }

        ?>
        <script data-cg-processing="true">
            cgJsClassAdmin.gallery.vars.cgOpenAiKeyIsValid = <?php echo json_encode($cgOpenAiKeyIsValid); ?>;
            cgJsClassAdmin.gallery.vars.cgOpenAiKeyErrorMessage = <?php echo json_encode($cgOpenAiKeyErrorMessage); ?>;
        </script>
        <?php

    }
}
