<?php

if (!defined('ABSPATH')) { exit; }

add_action('wp_ajax_post_cg_generate_openai_image', 'post_cg_generate_openai_image');
if (!function_exists('post_cg_generate_openai_image')) {
    function post_cg_generate_openai_image() {
        cg_ai_handle_image_request(false);
    }
}
