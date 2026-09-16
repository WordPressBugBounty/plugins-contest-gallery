<?php

if (!defined('ABSPATH')) { exit; }

if (!function_exists('cg_ai_image_models')) {
    function cg_ai_image_models() {
        $qualities = array('low' => 'Low', 'medium' => 'Standard', 'high' => 'High');
        $extended = $qualities + array('xhigh' => 'Extra high', 'max' => 'Max');
        return array(
            'gpt-image-2.5-sunburst' => array('name' => 'GPT Image 2.5 Sunburst',
                'description' => 'Precise image generation and editing.', 'badge' => 'Sunburst', 'qualities' => $extended),
            'gpt-image-2.5-flare' => array('name' => 'GPT Image 2.5 Flare',
                'description' => 'Fast, high-quality everyday image generation.', 'badge' => 'Flare', 'qualities' => $extended),
            'gpt-image-2' => array('name' => 'GPT Image 2',
                'description' => 'Image generation and editing with flexible composition.', 'badge' => 'Image 2', 'qualities' => $qualities)
        );
    }
}

if (!function_exists('cg_ai_image_sizes')) {
    function cg_ai_image_sizes() {
        return array('1024x1024' => '1024 × 1024 (square)',
            '1536x1024' => '1536 × 1024 (landscape)', '1024x1536' => '1024 × 1536 (portrait)');
    }
}

if (!function_exists('cg_ai_image_settings')) {
    function cg_ai_image_settings($input, $isEdit) {
        foreach (array('cg_ai_model', 'cg_ai_quality', 'cg_ai_res', 'cgOpenAiPromptInput') as $field) {
            if (isset($input[$field]) && !is_string($input[$field])) {
                return new WP_Error('invalid_image_settings', 'Invalid image settings. Please select a model and quality again.');
            }
        }
        $models = cg_ai_image_models();
        $model = isset($input['cg_ai_model']) ? $input['cg_ai_model'] : '';
        if (!isset($models[$model])) {
            return new WP_Error('invalid_image_model', 'This image model is no longer offered. Reload the dialog and select an available model.');
        }
        $quality = isset($input['cg_ai_quality']) ? $input['cg_ai_quality'] : 'medium';
        if (!isset($models[$model]['qualities'][$quality])) {
            return new WP_Error('invalid_image_quality', 'The selected quality is not supported by this model. Please select another quality.');
        }
        $prompt = isset($input['cgOpenAiPromptInput']) ? trim(sanitize_textarea_field(wp_unslash($input['cgOpenAiPromptInput']))) : '';
        if ($prompt === '') { return new WP_Error('missing_image_prompt', 'Please enter a prompt.'); }
        $size = isset($input['cg_ai_res']) ? $input['cg_ai_res'] : ($isEdit ? 'auto' : '1024x1024');
        $sizes = cg_ai_image_sizes();
        if (!isset($sizes[$size]) && !($isEdit && $size === 'auto')) {
            return new WP_Error('invalid_image_size', 'Please select a supported image size.');
        }
        if (cg_get_version() !== 'contest-gallery-pro') {
            if (!$isEdit && $size !== '1024x1024') {
                return new WP_Error('image_size_requires_pro', 'Landscape and portrait image generation require Contest Gallery PRO.');
            }
            if ($isEdit && !in_array($quality, array('low', 'medium'), true)) {
                return new WP_Error('image_quality_requires_pro', 'High, Extra high and Max image editing require Contest Gallery PRO.');
            }
        }
        return array('model' => $model, 'prompt' => $prompt, 'quality' => $quality,
            'size' => $isEdit ? 'auto' : $size, 'n' => 1, 'output_format' => 'png');
    }
}

if (!function_exists('cg_ai_image_files')) {
    function cg_ai_image_files($images) {
        if (!is_array($images) || !$images) {
            return new WP_Error('missing_edit_image', 'Please select a JPG or PNG image to edit.');
        }
        $files = array();
        foreach ($images as $image) {
            $id = is_array($image) && isset($image['id']) && is_scalar($image['id']) ? absint($image['id']) : 0;
            if (!$id || get_post_type($id) !== 'attachment' || !current_user_can('read_post', $id)) {
                return new WP_Error('edit_image_access', 'You do not have permission to read the selected image.');
            }
            $path = get_attached_file($id);
            if (!$path || !is_file($path) || !is_readable($path)) {
                return new WP_Error('edit_image_missing', 'The selected image file could not be read. Please select another image.');
            }
            $mime = wp_get_image_mime($path);
            if (!in_array($mime, array('image/jpeg', 'image/png'), true)) {
                return new WP_Error('edit_image_type', 'Only JPG and PNG images can be edited here.');
            }
            $files[] = array('path' => $path, 'mime' => $mime, 'name' => wp_basename($path));
        }
        return $files;
    }
}

if (!function_exists('cg_ai_request_image')) {
    function cg_ai_request_image($apiKey, $settings, $files = array()) {
        if (!is_string($apiKey) || trim($apiKey) === '') {
            return new WP_Error('missing_openai_key', 'Ask an administrator to configure the OpenAI API key in the plugin settings.');
        }
        if (!function_exists('curl_init') || ($files && !class_exists('CURLFile'))) {
            return new WP_Error('missing_curl', 'The PHP cURL extension is required to connect to OpenAI.');
        }
        $headers = array('Authorization: Bearer ' . trim($apiKey));
        $body = $settings;
        if ($files) {
            foreach ($files as $index => $file) {
                $body['image[' . $index . ']'] = new CURLFile($file['path'], $file['mime'], $file['name']);
            }
        } else {
            $headers[] = 'Content-Type: application/json';
            $body = wp_json_encode($body);
        }
        $maxExecutionTime = (int) ini_get('max_execution_time');
        $timeout = $maxExecutionTime > 0 ? max(1, min(240, $maxExecutionTime - 1)) : 240;
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://api.openai.com/v1/images/' . ($files ? 'edits' : 'generations'),
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeout), CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $headers, CURLOPT_POSTFIELDS => $body
        ));
        $response = curl_exec($ch);
        $errorNo = curl_errno($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($errorNo) {
            return new WP_Error('openai_connection', $errorNo === CURLE_OPERATION_TIMEDOUT
                ? 'The OpenAI request timed out. Image processing can take several minutes. Ask your host to check PHP and web server time limits before trying again.'
                : 'The connection to OpenAI failed. Check your server connection before trying again.');
        }
        $json = json_decode($response, true);
        if ($httpCode < 200 || $httpCode >= 300 || !empty($json['error'])) {
            $code = isset($json['error']['code']) && is_string($json['error']['code']) ? $json['error']['code'] : '';
            $message = isset($json['error']['message']) && is_string($json['error']['message']) ? sanitize_text_field($json['error']['message']) : '';
            if ($httpCode === 401) {
                $message = 'OpenAI did not accept the API key. Ask an administrator to check the plugin settings.';
            } elseif ($code === 'model_not_found' || $httpCode === 403) {
                $message = 'This model is not available to the configured OpenAI project. Check model access and organization verification, or choose another model.';
            } elseif ($code === 'insufficient_quota') {
                $message = 'The OpenAI project has insufficient quota. Check its billing balance and spending limits.';
            } elseif ($httpCode === 429) {
                $message = 'The OpenAI rate limit was reached. Wait before trying again.';
            } elseif ($code === 'moderation_blocked') {
                $message = 'OpenAI could not process this prompt or input image under its content rules. Adjust your request before trying again.';
            } elseif ($httpCode >= 500) {
                $message = 'OpenAI is temporarily unavailable. Please try again later.';
            }
            return new WP_Error('openai_request_failed', $message !== '' ? $message : 'OpenAI did not return a valid response. Please try again later.');
        }
        $encoded = isset($json['data'][0]['b64_json']) && is_string($json['data'][0]['b64_json']) ? $json['data'][0]['b64_json'] : '';
        $bytes = base64_decode($encoded, true);
        $image = $bytes ? @getimagesizefromstring($bytes) : false;
        if (!$image || $image['mime'] !== 'image/png') {
            return new WP_Error('invalid_openai_image', 'OpenAI did not return a valid PNG image. No image was saved.');
        }
        return array('url' => 'data:image/png;base64,' . $encoded,
            'size' => $image[0] . 'x' . $image[1], 'model' => $settings['model'], 'quality' => $settings['quality']);
    }
}

if (!function_exists('cg_ai_handle_image_request')) {
    function cg_ai_handle_image_request($isEdit) {
        cg_require_backend_access();
        if (!defined('DOING_AJAX') || !DOING_AJAX) { exit; }
        $user = wp_get_current_user();
        $result = new WP_Error('image_access', 'Only administrators, editors and authors can use image generation.');
        $lastPrompt = false;
        if (is_super_admin($user->ID) || array_intersect(array('administrator', 'editor', 'author'), (array) $user->roles)) {
            $settings = cg_ai_image_settings($_POST, $isEdit);
            $result = $settings;
            if (!is_wp_error($settings)) {
                $files = $isEdit ? cg_ai_image_files(isset($_POST['cg_images']) ? $_POST['cg_images'] : array()) : array();
                $result = $files;
                if (!is_wp_error($files)) {
                    global $wpdb;
                    $apiKey = $wpdb->get_var('SELECT OpenAiKey FROM ' . $wpdb->prefix . 'contest_gal1ery_pro_options WHERE GeneralID = 1');
                    $result = cg_ai_request_image($apiKey, $settings, $files);
                    if (!is_wp_error($result)) {
                        $savedSettings = $settings;
                        $savedSettings['size'] = $result['size'];
                        $lastPrompt = cg_ai_insert_prompt($settings['prompt'], true, '', $isEdit ? 2 : 1, $savedSettings);
                    }
                }
            }
        }
        $valid = !is_wp_error($result);
        $values = array('cgOpenAiGenIsValid' => $valid,
            'cgOpenAiGenErrorMessage' => $valid ? '' : $result->get_error_message(),
            'cgOpenAiGenUrl' => $valid ? $result['url'] : '', 'cgOpenAiLastPrompt' => $lastPrompt);
        echo '<script data-cg-processing="true">';
        foreach ($values as $name => $value) {
            echo 'cgJsClassAdmin.gallery.vars.' . $name . '=' . wp_json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';';
        }
        echo '</script>';
        exit;
    }
}

if (!function_exists('cg_ai_insert_prompt')) {
    function cg_ai_insert_prompt($prompt, $cgOpenAiGenIsValid = false, $revisedPrompt = '', $type = 0, $settings = array()) {

        global $wpdb;

        $tablename_ai_prompts = $wpdb->prefix . "contest_gal1ery_ai_prompts";
        $cgOpenAiLastPrompt = false;

        //var_dump('$revisedPrompt');
        //var_dump($revisedPrompt);

        if($cgOpenAiGenIsValid){
            $model = isset($settings['model']) ? $settings['model'] : $_POST['cg_ai_model'];
            $quality = isset($settings['quality']) ? $settings['quality'] : $_POST['cg_ai_quality'];
            $resolution = isset($settings['size']) ? $settings['size'] : $_POST['cg_ai_res'];
            $wpdb->query( $wpdb->prepare(
                "
					INSERT INTO $tablename_ai_prompts 
					( id, Prompt, RevisedPrompt, Company, Model, Quality, Resolution, Type, Tstamp)
					VALUES ( %s,%s,%s,%s,%s,%s,%s,%d,%d)
				",
                '',$prompt,$revisedPrompt,'openai',$model,$quality,$resolution,$type,time()
            ) );
            $insert_id = $wpdb->insert_id;
            $cgOpenAiLastPrompt = $wpdb->get_row("SELECT * FROM $tablename_ai_prompts WHERE id = $insert_id");
            $gmt_offset = get_option('gmt_offset');
            $cgOpenAiLastPrompt->date = cg_get_time_based_on_wp_timezone_conf($cgOpenAiLastPrompt->Tstamp,'d-M-Y H:i:s');
            $cgOpenAiLastPrompt->gmt_offset = $gmt_offset;
            return $cgOpenAiLastPrompt;
        }
        return $cgOpenAiLastPrompt;

    }
}
