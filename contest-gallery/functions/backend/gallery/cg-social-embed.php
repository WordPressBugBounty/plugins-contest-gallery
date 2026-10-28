<?php
if (!defined('ABSPATH')) { exit; }

// Only provider-owned URLs enter the social library. Browser-supplied HTML is never used.
if (!function_exists('cg_social_embed_url')) {
    function cg_social_embed_url($url) {
        if (!is_string($url) || strlen($url) > 2048) {
            return new WP_Error('cg_social_url', 'Enter a link to a YouTube, Instagram, TikTok or X post.');
        }
        $url = trim($url);
        $parts = wp_parse_url($url);
        if (!$parts || empty($parts['host']) || empty($parts['scheme']) || !in_array(strtolower($parts['scheme']), array('http', 'https'), true) || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return new WP_Error('cg_social_url', 'Enter a complete public post URL beginning with https://.');
        }
        $host = strtolower($parts['host']);
        $path = isset($parts['path']) ? $parts['path'] : '/';
        $query = array();
        if (!empty($parts['query'])) { parse_str($parts['query'], $query); }
        $data = array('provider' => '', 'source_url' => '', 'embed_url' => '', 'post_name' => '', 'title' => '', 'author_name' => '', 'author_url' => '', 'html' => '');
        if (in_array($host, array('youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be'), true)) {
            $id = '';
            if (in_array($host, array('youtu.be', 'www.youtu.be'), true)) { $id = trim($path, '/'); }
            elseif ($path === '/watch' && isset($query['v']) && is_string($query['v'])) { $id = $query['v']; }
            elseif (preg_match('~^/(?:shorts|embed)/([A-Za-z0-9_-]{11})/?$~D', $path, $match)) { $id = $match[1]; }
            if (!preg_match('/^[A-Za-z0-9_-]{11}$/D', $id)) { return new WP_Error('cg_social_url', 'Use a link to one YouTube video, rather than a channel or playlist.'); }
            $start = isset($query['t']) ? $query['t'] : (isset($query['start']) ? $query['start'] : '0');
            $seconds = 0;
            if (is_string($start) && preg_match('/^\d{1,7}$/D', $start)) { $seconds = intval($start); }
            elseif (is_string($start) && preg_match('/^(?:(\d{1,4})h)?(?:(\d{1,4})m)?(?:(\d{1,4})s)?$/D', $start, $time)) {
                $seconds = (isset($time[1]) ? intval($time[1]) * 3600 : 0) + (isset($time[2]) ? intval($time[2]) * 60 : 0) + (isset($time[3]) ? intval($time[3]) : 0);
            }
            $data['provider'] = 'youtube';
            $data['post_name'] = $id;
            $data['source_url'] = 'https://www.youtube.com/watch?v=' . $id . ($seconds ? '&t=' . $seconds . 's' : '');
            $data['embed_url'] = 'https://www.youtube.com/embed/' . $id . ($seconds ? '?start=' . $seconds : '');
        } elseif (in_array($host, array('instagram.com', 'www.instagram.com'), true) && preg_match('~^/(p|reel|tv)/([A-Za-z0-9_-]+)/?$~D', $path, $match)) {
            $data['provider'] = 'instagram';
            $data['post_name'] = $match[2];
            $data['source_url'] = 'https://www.instagram.com/' . $match[1] . '/' . $match[2] . '/';
            $data['embed_url'] = $data['source_url'] . 'embed';
            $data['title'] = 'Instagram post ' . $match[2];
        } elseif (in_array($host, array('x.com', 'www.x.com', 'twitter.com', 'www.twitter.com', 'mobile.twitter.com'), true) && preg_match('~^/([A-Za-z0-9_]+)/status/([0-9]+)/?(?:photo/[0-9]+/?)?$~D', $path, $match)) {
            $data['provider'] = 'twitter';
            $data['post_name'] = $match[1] . '/status/' . $match[2];
            $data['source_url'] = 'https://x.com/' . $data['post_name'];
            $data['embed_url'] = $data['source_url'];
        } elseif (in_array($host, array('tiktok.com', 'www.tiktok.com'), true) && preg_match('~^/@([A-Za-z0-9_.]+)/video/([0-9]+)/?$~D', $path, $match)) {
            $data['provider'] = 'tiktok';
            $data['post_name'] = '@' . $match[1] . '/video/' . $match[2];
            $data['source_url'] = 'https://www.tiktok.com/' . $data['post_name'];
            $data['embed_url'] = $data['source_url'];
        } else {
            return new WP_Error('cg_social_url', 'Use the full link to an individual YouTube, Instagram, TikTok or X post. Profile and shortened redirect links are not supported.');
        }
        return $data;
    }
}

if (!function_exists('cg_social_embed_resolve')) {
    function cg_social_embed_resolve($url) {
        $data = cg_social_embed_url($url);
        if (is_wp_error($data) || $data['provider'] === 'instagram') { return $data; }
        $cacheKey = 'cg_social_preview_' . md5($data['source_url']);
        $cached = get_transient($cacheKey);
        if (is_array($cached) && isset($cached['source_url']) && $cached['source_url'] === $data['source_url']) { return $cached; }
        $endpoints = array('youtube' => 'https://www.youtube.com/oembed', 'twitter' => 'https://publish.x.com/oembed', 'tiktok' => 'https://www.tiktok.com/oembed');
        $endpoint = add_query_arg(array('url' => $data['source_url'], 'format' => 'json'), $endpoints[$data['provider']]);
        $response = wp_safe_remote_get($endpoint, array('timeout' => 12, 'redirection' => 2, 'limit_response_size' => 262144));
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 429) {
            return new WP_Error('cg_social_rate_limit', 'The platform is temporarily limiting requests. Please wait a little before retrying the preview.');
        }
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) < 200 || wp_remote_retrieve_response_code($response) >= 300) {
            return new WP_Error('cg_social_provider', 'The platform could not load this post. Check that it is public and allows embedding, then try again.');
        }
        $meta = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($meta) || (empty($meta['title']) && empty($meta['html']))) {
            return new WP_Error('cg_social_provider', 'The platform returned no usable preview. Check the post URL and try again.');
        }
        $data['title'] = isset($meta['title']) && is_string($meta['title']) ? sanitize_text_field($meta['title']) : '';
        $data['author_name'] = isset($meta['author_name']) && is_string($meta['author_name']) ? sanitize_text_field($meta['author_name']) : '';
        $data['author_url'] = isset($meta['author_url']) && is_string($meta['author_url']) ? esc_url_raw($meta['author_url'], array('https', 'http')) : '';
        if ($data['provider'] === 'tiktok') {
            // Current TikTok oEmbed responses contain a player iframe, often without title/author.
            // Build it from our validated video ID instead of accepting arbitrary iframe markup.
            $videoId = substr($data['post_name'], strrpos($data['post_name'], '/') + 1);
            $playerUrl = 'https://www.tiktok.com/player/v1/' . $videoId . '?music_info=1&description=1&autoplay=0';
            $data['html'] = '<iframe width="325" height="578" src="' . esc_url($playerUrl) . '" title="TikTok video" frameborder="0" allow="encrypted-media; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
            if (!$data['author_name']) { $data['author_name'] = substr($data['post_name'], 0, strpos($data['post_name'], '/')); }
            if (!$data['title']) { $data['title'] = 'TikTok video by ' . $data['author_name']; }
        }
        if ($data['provider'] === 'twitter') {
            if (empty($meta['html']) || !is_string($meta['html'])) { return new WP_Error('cg_social_provider', 'The platform returned no embeddable post.'); }
            // Keep post markup, but load scripts only from the fixed provider URLs below.
            $html = preg_replace('~<script\b[^>]*>[\s\S]*?</script\s*>~i', '', $meta['html']);
            $allowed = array(
                'blockquote' => array('class' => true, 'cite' => true, 'data-video-id' => true, 'data-embed-from' => true, 'data-dnt' => true),
                'section' => array(), 'p' => array('lang' => true, 'dir' => true), 'a' => array('href' => true, 'title' => true, 'target' => true, 'rel' => true),
                'span' => array(), 'br' => array(), 'b' => array(), 'strong' => array(), 'em' => array()
            );
            $html = wp_kses($html, $allowed, array('https', 'http'));
            if (strpos($html, '<blockquote') === false) { return new WP_Error('cg_social_provider', 'The platform returned no embeddable post.'); }
            if (!$data['title']) { $data['title'] = sanitize_text_field(wp_strip_all_tags($html)); }
            $script = 'https://platform.twitter.com/widgets.js';
            $data['html'] = $html . '<script async src="' . esc_url($script) . '"></script>';
        }
        if (!$data['title']) { $data['title'] = ucfirst($data['provider']) . ' post'; }
        set_transient($cacheKey, $data, 10 * MINUTE_IN_SECONDS);
        return $data;
    }
}

if (!function_exists('cg_social_embed_request')) {
    function cg_social_embed_request() {
        cg_backend_ajax_require_access_json();
        $url = isset($_POST['source_url']) && is_string($_POST['source_url']) ? wp_unslash($_POST['source_url']) : '';
        $data = cg_social_embed_resolve($url);
        if (is_wp_error($data)) { cg_backend_ajax_error_json($data->get_error_message(), $data->get_error_code() === 'cg_social_url' ? 400 : ($data->get_error_code() === 'cg_social_rate_limit' ? 429 : 502), $data->get_error_code()); }
        return $data;
    }
}

if (!function_exists('cg_social_embed_preview')) {
    function cg_social_embed_preview() {
        $data = cg_social_embed_request();
        wp_send_json_success($data);
    }
}
add_action('wp_ajax_post_cg_social_platform_preview', 'cg_social_embed_preview');

if (!function_exists('cg_social_embed_save')) {
    function cg_social_embed_save() {
        $data = cg_social_embed_request();
        $content = '';
        if ($data['html']) {
            // This markup was already restricted/constructed by the server. Encode it for the legacy readers
            // without the user-input helper that strips provider iframes for authors without unfiltered_html.
            // Readers locate the first literal blockquote: marker. Do not prefix it with provider text.
            $content = 'blockquote: ' . htmlentities($data['html'], ENT_QUOTES, 'UTF-8') . ';';
        }
        $postId = wp_insert_post(wp_slash(array(
            'post_title' => $data['title'], 'post_name' => $data['post_name'], 'guid' => $data['embed_url'],
            'post_type' => 'contest-gallery', 'post_content' => $content,
            'post_mime_type' => 'contest-gallery-' . $data['provider'], 'post_status' => 'publish'
        )), true);
        if (is_wp_error($postId) || !$postId) { cg_backend_ajax_error_json('The post could not be saved. Please try again.', 500, 'cg_social_save'); }
        // Existing readers use the case-sensitive provider identifier stored in post_name.
        global $wpdb;
        if ($wpdb->update($wpdb->posts, array('post_name' => $data['post_name']), array('ID' => $postId), array('%s'), array('%d')) === false) {
            wp_delete_post($postId, true);
            cg_backend_ajax_error_json('The post could not be saved. Please try again.', 500, 'cg_social_save');
        }
        clean_post_cache($postId);
        wp_send_json_success(array('post_id' => $postId, 'provider' => $data['provider']));
    }
}
