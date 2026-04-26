<?php
if (!function_exists('cg_echo_closed_upload_form')) {
    function cg_echo_closed_upload_form()
    {

        echo "</div>";

    }
}

if (!function_exists('cg_check_frontend_nonce')) {
    function cg_check_frontend_nonce()
    {

        if (!check_ajax_referer('cg1l_action', 'cg_nonce', false)) {
            wp_send_json_error(['message' => 'cg_invalid_nonce'], 403);
        }

    }
}

if (!function_exists('cg1l_ajax_frontend_response')) {
    function cg1l_ajax_frontend_response($ok = false, $data = []) {
        while (ob_get_level()) { ob_end_clean(); }
        if ($ok) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error($data, 403);
        }
    }
}

if (!function_exists('cg1l_normalize_positive_int_id_list')) {
    function cg1l_normalize_positive_int_id_list($ids)
    {
        if (!is_array($ids)) {
            return [];
        }

        $normalizedIds = [];

        foreach ($ids as $id) {
            $id = absint($id);
            if (empty($id) || isset($normalizedIds[$id])) {
                continue;
            }
            $normalizedIds[$id] = $id;
        }

        return array_values($normalizedIds);
    }
}

if (!function_exists('cg1l_parse_bool_value')) {
    function cg1l_parse_bool_value($value, $default = false)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (intval($value) === 1);
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));

            if ($value === 'true') {
                return true;
            }

            if ($value === 'false' || $value === '') {
                return false;
            }
        }

        return (!empty($value)) ? true : (bool)$default;
    }
}

if (!function_exists('cg1l_get_gallery_data_access_hash')) {
    function cg1l_get_gallery_data_access_hash($gid, $shortcode_name, $viewerUserId = 0, $useAllowedRealIds = true)
    {
        $gid = absint($gid);
        $shortcode_name = sanitize_text_field($shortcode_name);
        $viewerUserId = absint($viewerUserId);
        $useAllowedRealIds = (!empty($useAllowedRealIds)) ? 1 : 0;

        return cg_hash_function('---cg1lGalleryData---'.$gid.'---'.$shortcode_name.'---'.$viewerUserId.'---'.$useAllowedRealIds);
    }
}

if (!function_exists('cg1l_get_galleries_data_access_hash')) {
    function cg1l_get_galleries_data_access_hash($shortcode_name, $viewerUserId = 0, $isGalleriesMainPage = false, $galleriesIds = [], $hasGalleriesIds = false)
    {
        $shortcode_name = sanitize_text_field($shortcode_name);
        $viewerUserId = absint($viewerUserId);
        $isGalleriesMainPage = (!empty($isGalleriesMainPage)) ? 1 : 0;
        $galleriesIds = cg1l_normalize_positive_int_id_list($galleriesIds);
        $hasGalleriesIds = (!empty($hasGalleriesIds) && !empty($galleriesIds)) ? 1 : 0;

        return cg_hash_function('---cg1lGalleriesData---'.$shortcode_name.'---'.$viewerUserId.'---'.$isGalleriesMainPage.'---'.$hasGalleriesIds.'---'.wp_json_encode($galleriesIds));
    }
}

if (!function_exists('cg1l_get_ecommerce_raw_data_access_hash')) {
    function cg1l_get_ecommerce_raw_data_access_hash($realGid, $realId, $shortcode_name, $viewerUserId = 0)
    {
        $realGid = absint($realGid);
        $realId = absint($realId);
        $shortcode_name = sanitize_text_field($shortcode_name);
        $viewerUserId = absint($viewerUserId);

        return cg_hash_function('---cg1lEcommerceRawData---'.$realGid.'---'.$realId.'---'.$shortcode_name.'---'.$viewerUserId);
    }
}

if (!function_exists('cg1l_include_scripts_reg_pin')) {
    function cg1l_include_scripts_reg_pin()
    {
        if (cg_check_if_development()) {
            wp_enqueue_style('cg_contest_style', plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/style.css', __FILE__), false, cg_get_version_for_scripts());
            wp_enqueue_style('cg_contest_style_pro', plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/style_pro.css', __FILE__), false, cg_get_version_for_scripts());

            wp_enqueue_script('cg_pro_version_info_recognized', plugins_url('/../../contest-gallery-js-and-css/v10/v10-js/cg-pro-version-info-recognized.js', __FILE__), array('jquery'), cg_get_version_for_scripts());

            wp_localize_script('cg_pro_version_info_recognized', 'post_cg_pro_version_info_recognized_wordpress_ajax_script_function_name', array(
                'cg_pro_version_info_recognized_ajax_url' => admin_url('admin-ajax.php')
            ));
            wp_enqueue_style('cg_general_form_style', plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/cg_general_form_style.css', __FILE__), false, cg_get_version_for_scripts());
            wp_enqueue_style('cg_v10_contest_gallery_form_style', plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/cg_gallery_form_style.css', __FILE__), false, cg_get_version_for_scripts());
            wp_enqueue_style('cg_v10_contest_gallery_registry_form_style', plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/cg_gallery_registry_form_style.css', __FILE__), false, cg_get_version_for_scripts());
            wp_enqueue_script('cg_js_general_frontend', plugins_url('/../../contest-gallery-js-and-css/v10/v10-js/general_frontend.js', __FILE__), array('jquery'), cg_get_version_for_scripts());
            wp_enqueue_script('cg_registry', plugins_url('/../../contest-gallery-js-and-css/v10/v10-js/registry/users-registry.js', __FILE__), array('jquery'), cg_get_version_for_scripts());
            // post_cg_registry is script name then!!!!!
            wp_localize_script('cg_registry', 'post_cg_registry_wordpress_ajax_script_function_name', array(
                'cg_registry_ajax_url' => admin_url('admin-ajax.php')
            ));
        } else {
            wp_enqueue_style('cg_v10_css_cg_gallery', plugins_url('/../../v10/v10-css-min/cg_gallery.min.css', __FILE__), false, cg_get_version_for_scripts());
            wp_enqueue_style('cg_v10_css_loaders_cg_gallery', plugins_url('/../../v10/v10-css/frontend/style_loaders.css', __FILE__), false, cg_get_version_for_scripts());
            wp_enqueue_script('cg_v10_js_cg_gallery', plugins_url('/../../v10/v10-js-min/cg_gallery.min.js', __FILE__), array('jquery'), cg_get_version_for_scripts());
            wp_localize_script('cg_v10_js_cg_gallery', 'post_cg_pro_version_info_recognized_wordpress_ajax_script_function_name', array(
                'cg_pro_version_info_recognized_ajax_url' => admin_url('admin-ajax.php')
            ));

            wp_localize_script('cg_v10_js_cg_gallery', 'CG1LAction', [
                'nonce' => wp_create_nonce('cg1l_action'),
                'ajax_url' => admin_url('admin-ajax.php'),
            ]);
        }
    }
}

if (!function_exists('cg1l_get_image_attributes')) {
    function cg1l_get_image_attributes($fullData)
    {
        $WidthAttribute = '';
        $HeightAttribute = '160';
        $rThumb = '';
        $imgStyle = 'style="width: 100%; height: auto;"';
        $naturalWidth = (!empty($fullData['Width'])) ? absint($fullData['Width']) : 0;
        $naturalHeight = (!empty($fullData['Height'])) ? absint($fullData['Height']) : 0;

        if (
            $naturalWidth &&
            $naturalHeight &&
            isset($fullData['rThumb']) &&
            ($fullData['rThumb'] == '90' || $fullData['rThumb'] == '270')
        ) {
            $WidthAttribute = ' height="' . $naturalHeight . '"';
            $HeightAttribute = ' width="' . $naturalWidth . '"';
            $rThumb = ' cg' . $fullData['rThumb'] . 'degree';
            $imgStyle = 'style="width: ' . round($naturalWidth / $naturalHeight * 100, 2) . '%; height: 100%; max-width: ' . $naturalHeight . 'px; max-height: ' . $naturalWidth . 'px;"';
        } elseif ($naturalWidth && $naturalHeight) {
            $WidthAttribute = ' width="' . $naturalWidth . '"';
            $HeightAttribute = ' height="' . $naturalHeight . '"';
            $imgStyle = 'style="width: 100%; height: auto; max-width: ' . $naturalWidth . 'px; max-height: ' . $naturalHeight . 'px;"';
        }

        return array(
            'WidthAttribute' => $WidthAttribute,
            'HeightAttribute' => $HeightAttribute,
            'rThumb' => $rThumb,
            'imgStyle' => $imgStyle,
        );
    }
}

/**
 * Get the current gallery page number.
 * Works universally on pages, posts, and archives.
 */
if (!function_exists('cgl_get_current_page_number')) {
    function cgl_get_current_page_number() {
        // Get our custom query var, default to 1 if not set
        $page = get_query_var('cgl_page');
        // Return the page number as an integer, at least 1
        return $page ? max(1, intval($page)) : 1;
    }
}

if (!function_exists('cgl_get_navigation_query_keys')) {
    function cgl_get_navigation_query_keys() {
        return [
            'cg_gallery_id',
            'cgl_gallery',
            'cgl_page',
            'cgl_from_gallery_page',
            'cgl_from_galleries_page',
            'cgl_origin_page_id',
        ];
    }
}

if (!function_exists('cgl_normalize_internal_url')) {
    function cgl_normalize_internal_url($url) {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }

        if (strpos($url, '//') === 0) {
            $scheme = wp_parse_url(home_url('/'), PHP_URL_SCHEME);
            $url = $scheme . ':' . $url;
        } elseif (!preg_match('#^[a-z][a-z0-9+\-.]*://#i', $url)) {
            $url = home_url('/' . ltrim($url, '/'));
        }

        return cgl_sanitize_internal_url($url);
    }
}

if (!function_exists('cgl_strip_navigation_query_args')) {
    function cgl_strip_navigation_query_args($url) {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }

        $url = preg_replace('/#.*$/', '', $url);
        $url = remove_query_arg(cgl_get_navigation_query_keys(), $url);

        return esc_url_raw($url);
    }
}

if (!function_exists('cgl_get_clean_absolute_url')) {
    function cgl_get_clean_absolute_url($url) {
        $url = cgl_normalize_internal_url($url);
        if (empty($url)) {
            return '';
        }

        return cgl_strip_navigation_query_args($url);
    }
}

if (!function_exists('cgl_has_navigation_query_args')) {
    function cgl_has_navigation_query_args($queryArgs = null) {
        if ($queryArgs === null) {
            $queryArgs = $_GET;
        }

        if (!is_array($queryArgs)) {
            return false;
        }

        foreach (cgl_get_navigation_query_keys() as $queryKey) {
            if (!isset($queryArgs[$queryKey])) {
                continue;
            }

            $value = $queryArgs[$queryKey];
            if ($value === '' || $value === null) {
                continue;
            }

            return true;
        }

        return false;
    }
}

if (!function_exists('cgl_get_from_galleries_page')) {
    function cgl_get_from_galleries_page($returnNumberOnly = false) {
        // Get our custom query var, default to 1 if not set
        $page = get_query_var('cgl_from_galleries_page');
        // Return the page number as an integer, at least 1
        $page = $page ? max(1, intval($page)) : 1;
        if($page>1){
            if($returnNumberOnly){
                return $page;
            }
            return '?cgl_page='.$page;
        }
        if($returnNumberOnly){
            return $page;
        }
        return '';
    }
}

if (!function_exists('cgl_from_gallery_page')) {
    function cgl_from_gallery_page() {
        // Get our custom query var, default to 1 if not set
        $page = get_query_var('cgl_from_gallery_page');
        // Return the page number as an integer, at least 1
        $page = $page ? max(1, intval($page)) : 1;

        return $page;
    }
}

if (!function_exists('cgl_sanitize_internal_url')) {
    function cgl_sanitize_internal_url($url) {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }

        $url = esc_url_raw($url);
        if (empty($url)) {
            return '';
        }

        $urlParts = wp_parse_url($url);
        $homeUrlParts = wp_parse_url(home_url('/'));

        if (empty($urlParts['host']) || empty($homeUrlParts['host'])) {
            return '';
        }

        if (strcasecmp($urlParts['host'], $homeUrlParts['host']) !== 0) {
            return '';
        }

        if (!empty($urlParts['port']) && !empty($homeUrlParts['port']) && intval($urlParts['port']) !== intval($homeUrlParts['port'])) {
            return '';
        }

        return preg_replace('/#.*$/', '', $url);
    }
}

if (!function_exists('cgl_get_gallery_query_gallery_id')) {
    function cgl_get_gallery_query_gallery_id($fallback = 0) {
        $galleryId = absint(get_query_var('cgl_gallery'));

        if (empty($galleryId) && isset($_GET['cgl_gallery'])) {
            $galleryId = absint(wp_unslash($_GET['cgl_gallery']));
        }

        if (empty($galleryId) && isset($_GET['cg_gallery_id'])) {
            $galleryId = absint(wp_unslash($_GET['cg_gallery_id']));
        }

        if (empty($galleryId)) {
            $galleryId = absint($fallback);
        }

        return $galleryId;
    }
}

if (!function_exists('cgl_get_origin_page_id')) {
    function cgl_get_origin_page_id($fallback = 0) {
        $originPageId = absint(get_query_var('cgl_origin_page_id'));

        if (empty($originPageId) && isset($_GET['cgl_origin_page_id'])) {
            $originPageId = absint(wp_unslash($_GET['cgl_origin_page_id']));
        }

        if (empty($originPageId)) {
            $originPageId = absint($fallback);
        }

        return $originPageId;
    }
}

if (!function_exists('cgl_get_origin_page_url')) {
    function cgl_get_origin_page_url($originPageId = 0, $fallbackUrl = '') {
        $originPageId = absint($originPageId);
        if (!empty($originPageId) && get_post_status($originPageId)) {
            $originPageUrl = cgl_normalize_internal_url(get_permalink($originPageId));
            if (!empty($originPageUrl)) {
                return cgl_strip_navigation_query_args($originPageUrl);
            }
        }

        $fallbackUrl = cgl_normalize_internal_url($fallbackUrl);
        if (!empty($fallbackUrl)) {
            return cgl_strip_navigation_query_args($fallbackUrl);
        }

        return '';
    }
}

if (!function_exists('cgl_get_query_var_from_url')) {
    function cgl_get_query_var_from_url($url, $queryVar) {
        $url = cgl_normalize_internal_url($url);
        if (empty($url) || empty($queryVar)) {
            return 0;
        }

        $query = wp_parse_url($url, PHP_URL_QUERY);
        if (empty($query)) {
            return 0;
        }

        parse_str($query, $queryVars);

        return (!empty($queryVars[$queryVar])) ? max(1, absint($queryVars[$queryVar])) : 0;
    }
}

if (!function_exists('cgl_get_shortcode_page_map')) {
    function cgl_get_shortcode_page_map($shortcodeName = 'cg_gallery') {
        $map = [
            'cg_gallery' => [
                'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-slug',
                'parentField' => 'WpPageParent',
                'entryField' => 'WpPage',
                'optionsKeySuffix' => '',
            ],
            'cg_gallery_user' => [
                'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-user-slug',
                'parentField' => 'WpPageParentUser',
                'entryField' => 'WpPageUser',
                'optionsKeySuffix' => '-u',
            ],
            'cg_gallery_no_voting' => [
                'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-no-voting-slug',
                'parentField' => 'WpPageParentNoVoting',
                'entryField' => 'WpPageNoVoting',
                'optionsKeySuffix' => '-nv',
            ],
            'cg_gallery_winner' => [
                'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-winner-slug',
                'parentField' => 'WpPageParentWinner',
                'entryField' => 'WpPageWinner',
                'optionsKeySuffix' => '-w',
            ],
            'cg_gallery_ecommerce' => [
                'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-ecommerce-slug',
                'parentField' => 'WpPageParentEcommerce',
                'entryField' => 'WpPageEcommerce',
                'optionsKeySuffix' => '-ec',
            ],
        ];

        return (!empty($map[$shortcodeName])) ? $map[$shortcodeName] : $map['cg_gallery'];
    }
}

if (!function_exists('cgl_get_shortcode_name_from_galleries_post_mime_type')) {
    function cgl_get_shortcode_name_from_galleries_post_mime_type($postMimeType = '') {
        $postMimeType = sanitize_key($postMimeType);

        $map = [
            'contest-gallery-plugin-page-galleries-slug' => 'cg_gallery',
            'contest-gallery-plugin-page-galleries-user-slug' => 'cg_gallery_user',
            'contest-gallery-plugin-page-galleries-no-voting-slug' => 'cg_gallery_no_voting',
            'contest-gallery-plugin-page-galleries-winner-slug' => 'cg_gallery_winner',
            'contest-gallery-plugin-page-galleries-ecommerce-slug' => 'cg_gallery_ecommerce',
        ];

        return (!empty($map[$postMimeType])) ? $map[$postMimeType] : '';
    }
}

if (!function_exists('cgl_get_galleries_shortcode_name_from_post')) {
    function cgl_get_galleries_shortcode_name_from_post($post = null) {
        $post = get_post($post);
        if (empty($post)) {
            return '';
        }

        $shortcodeName = cgl_get_shortcode_name_from_galleries_post_mime_type($post->post_mime_type);
        if (!empty($shortcodeName)) {
            return $shortcodeName;
        }

        $postContent = (string)$post->post_content;
        if ($postContent === '') {
            return '';
        }

        $shortcodeMap = [
            'cg_galleries_ecommerce' => 'cg_gallery_ecommerce',
            'cg_galleries_winner' => 'cg_gallery_winner',
            'cg_galleries_no_voting' => 'cg_gallery_no_voting',
            'cg_galleries_user' => 'cg_gallery_user',
            'cg_galleries' => 'cg_gallery',
        ];

        foreach ($shortcodeMap as $pluralShortcode => $singularShortcode) {
            if (
                (function_exists('has_shortcode') && has_shortcode($postContent, $pluralShortcode)) ||
                strpos($postContent, '[' . $pluralShortcode) !== false
            ) {
                return $singularShortcode;
            }
        }

        return '';
    }
}

if (!function_exists('cgl_get_gallery_parent_page_id')) {
    function cgl_get_gallery_parent_page_id($galleryId, $shortcodeName = 'cg_gallery') {
        global $wpdb;

        $galleryId = absint($galleryId);
        if (empty($galleryId)) {
            return 0;
        }

        $map = cgl_get_shortcode_page_map($shortcodeName);
        $parentField = (!empty($map['parentField'])) ? $map['parentField'] : '';
        if ($parentField === '') {
            return 0;
        }

        $tablename_options = $wpdb->prefix . 'contest_gal1ery_options';

        return absint($wpdb->get_var($wpdb->prepare("SELECT $parentField FROM $tablename_options WHERE id = %d LIMIT 1", [$galleryId])));
    }
}

if (!function_exists('cgl_get_gallery_parent_page_url')) {
    function cgl_get_gallery_parent_page_url($galleryId, $shortcodeName = 'cg_gallery', $fallbackUrl = '') {
        $parentPageId = cgl_get_gallery_parent_page_id($galleryId, $shortcodeName);
        if (!empty($parentPageId) && get_post_status($parentPageId)) {
            $parentPageUrl = cgl_normalize_internal_url(get_permalink($parentPageId));
            if (!empty($parentPageUrl)) {
                return cgl_strip_navigation_query_args($parentPageUrl);
            }
        }

        $fallbackUrl = cgl_normalize_internal_url($fallbackUrl);
        if (!empty($fallbackUrl)) {
            return cgl_strip_navigation_query_args($fallbackUrl);
        }

        return '';
    }
}

if (!function_exists('cgl_get_resolved_canonical_url')) {
    function cgl_get_resolved_canonical_url($fallbackUrl = '', $shortcodeName = '', $allowSelectedGalleryCanonical = false, $post = null) {
        $canonicalUrl = cgl_get_clean_absolute_url($fallbackUrl);

        if (empty($shortcodeName) && empty($allowSelectedGalleryCanonical)) {
            $shortcodeName = cgl_get_galleries_shortcode_name_from_post($post);
            $allowSelectedGalleryCanonical = !empty($shortcodeName);
        }

        if (empty($allowSelectedGalleryCanonical)) {
            return $canonicalUrl;
        }

        $selectedGalleryId = cgl_get_gallery_query_gallery_id();
        if (empty($selectedGalleryId)) {
            return $canonicalUrl;
        }

        if (empty($shortcodeName)) {
            $shortcodeName = cgl_get_galleries_shortcode_name_from_post($post);
        }

        if (empty($shortcodeName)) {
            return $canonicalUrl;
        }

        $galleryParentUrl = cgl_get_gallery_parent_page_url($selectedGalleryId, $shortcodeName, $canonicalUrl);
        if (!empty($galleryParentUrl)) {
            return $galleryParentUrl;
        }

        return $canonicalUrl;
    }
}

if (!function_exists('cgl_build_gallery_page_url')) {
    function cgl_build_gallery_page_url($baseUrl, $pageNumber = 1, $galleryId = 0, $forcePageParameter = false) {
        $baseUrl = cgl_normalize_internal_url($baseUrl);
        if (empty($baseUrl)) {
            return '';
        }

        $pageNumber = max(1, absint($pageNumber));
        $galleryId = absint($galleryId);

        $url = cgl_strip_navigation_query_args($baseUrl);

        if (!empty($galleryId)) {
            $url = add_query_arg('cgl_gallery', $galleryId, $url);
            if ($forcePageParameter || $pageNumber > 1) {
                $url = add_query_arg('cgl_page', $pageNumber, $url);
            }
        } elseif ($pageNumber > 1) {
            $url = add_query_arg('cgl_page', $pageNumber, $url);
        }

        return preg_replace('/#.*$/', '', $url);
    }
}

if (!function_exists('cgl_build_gallery_selection_url')) {
    function cgl_build_gallery_selection_url($baseUrl, $galleryId = 0, $fromGalleriesPageNumber = 1) {
        $baseUrl = cgl_normalize_internal_url($baseUrl);
        if (empty($baseUrl)) {
            return '';
        }

        $galleryId = absint($galleryId);
        if (empty($galleryId)) {
            return '';
        }

        $fromGalleriesPageNumber = max(1, absint($fromGalleriesPageNumber));
        $url = cgl_strip_navigation_query_args($baseUrl);
        $url = add_query_arg('cgl_gallery', $galleryId, $url);

        $url = add_query_arg('cgl_from_galleries_page', $fromGalleriesPageNumber, $url);

        return preg_replace('/#.*$/', '', $url);
    }
}

if (!function_exists('cgl_build_multi_gallery_page_url')) {
    function cgl_build_multi_gallery_page_url($baseUrl, $galleryId = 0, $fromGalleriesPageNumber = 1, $pageNumber = 1) {
        $url = cgl_build_gallery_selection_url($baseUrl, $galleryId, $fromGalleriesPageNumber);
        if (empty($url)) {
            return '';
        }

        $pageNumber = max(1, absint($pageNumber));

        if ($pageNumber > 1) {
            $url = add_query_arg('cgl_page', $pageNumber, $url);
        }

        return preg_replace('/#.*$/', '', $url);
    }
}

if (!function_exists('cgl_build_galleries_landing_gallery_url')) {
    function cgl_build_galleries_landing_gallery_url($targetUrl, $fromGalleriesPageNumber = 1) {
        $targetUrl = cgl_normalize_internal_url($targetUrl);
        if (empty($targetUrl)) {
            return '';
        }

        $targetUrl = cgl_strip_navigation_query_args($targetUrl);
        $fromGalleriesPageNumber = max(1, absint($fromGalleriesPageNumber));

        $targetUrl = add_query_arg('cgl_from_galleries_page', $fromGalleriesPageNumber, $targetUrl);

        return preg_replace('/#.*$/', '', $targetUrl);
    }
}

if (!function_exists('cgl_build_entry_context_url')) {
    function cgl_build_entry_context_url($entryUrl, $args = []) {
        $entryUrl = cgl_normalize_internal_url($entryUrl);
        if (empty($entryUrl)) {
            return '';
        }

        $entryUrl = cgl_strip_navigation_query_args($entryUrl);
        $galleryId = (!empty($args['gallery_id'])) ? absint($args['gallery_id']) : 0;
        $originPageId = (!empty($args['origin_page_id'])) ? absint($args['origin_page_id']) : 0;
        $fallbackPageNumber = (!empty($args['page_number'])) ? max(1, absint($args['page_number'])) : 1;
        $fromGalleryPageNumber = (!empty($args['from_gallery_page_number'])) ? max(1, absint($args['from_gallery_page_number'])) : $fallbackPageNumber;
        $fromGalleriesPageNumber = (!empty($args['from_galleries_page_number'])) ? max(1, absint($args['from_galleries_page_number'])) : $fallbackPageNumber;
        $isMultiGalleryContext = (!empty($args['is_multi_gallery_context']) && !empty($galleryId));

        if ($isMultiGalleryContext) {
            $entryUrl = add_query_arg('cgl_gallery', $galleryId, $entryUrl);

            $entryUrl = add_query_arg('cgl_from_galleries_page', $fromGalleriesPageNumber, $entryUrl);

            $entryUrl = add_query_arg('cgl_from_gallery_page', $fromGalleryPageNumber, $entryUrl);

            if (!empty($originPageId)) {
                $entryUrl = add_query_arg('cgl_origin_page_id', $originPageId, $entryUrl);
            }
        } else {
            $entryUrl = add_query_arg('cgl_from_gallery_page', $fromGalleryPageNumber, $entryUrl);
        }

        return preg_replace('/#.*$/', '', $entryUrl);
    }
}

if (!function_exists('cgl_build_entry_back_url')) {
    function cgl_build_entry_back_url($args = []) {
        $multiGalleryId = (!empty($args['multi_gallery_id'])) ? absint($args['multi_gallery_id']) : 0;
        $multiPageNumber = (!empty($args['multi_page_number'])) ? max(1, absint($args['multi_page_number'])) : 1;
        $singlePageNumber = (!empty($args['single_page_number'])) ? max(1, absint($args['single_page_number'])) : 1;
        $originPageId = (!empty($args['origin_page_id'])) ? absint($args['origin_page_id']) : 0;
        $originUrl = (!empty($args['origin_url'])) ? cgl_get_origin_page_url($originPageId, $args['origin_url']) : cgl_get_origin_page_url($originPageId);
        $hasMultiGalleryContext = (!empty($args['has_multi_gallery_context']) && !empty($multiGalleryId));

        if ($hasMultiGalleryContext && !empty($originUrl)) {
            $multiGalleryUrl = cgl_build_multi_gallery_page_url($originUrl, $multiGalleryId, $multiPageNumber, $singlePageNumber);
            if (!empty($multiGalleryUrl)) {
                return $multiGalleryUrl;
            }
        }

        $singleBackUrlRaw = trim((string)(isset($args['single_back_url']) ? $args['single_back_url'] : ''));
        $singleBackUrlInternal = cgl_normalize_internal_url($singleBackUrlRaw);

        if (!empty($singleBackUrlInternal)) {
            $singleBackUrl = cgl_build_gallery_page_url($singleBackUrlInternal, $singlePageNumber);
            if (!empty($singleBackUrl)) {
                return $singleBackUrl;
            }
        } elseif ($singleBackUrlRaw !== '') {
            return esc_url_raw($singleBackUrlRaw);
        }

        $fallbackRootUrl = (!empty($args['fallback_root_url'])) ? cgl_normalize_internal_url($args['fallback_root_url']) : '';
        if (!empty($fallbackRootUrl)) {
            return cgl_build_gallery_page_url($fallbackRootUrl, $singlePageNumber);
        }

        return '';
    }
}

if (!function_exists('cgl_build_galleries_return_url')) {
    function cgl_build_galleries_return_url($baseUrl, $pageNumber = 0, $galleryId = 0) {
        $pageNumber = (!empty($pageNumber) ? absint($pageNumber) : 1);
        $galleryId = absint($galleryId);

        if (!empty($galleryId)) {
            return cgl_build_gallery_selection_url($baseUrl, $galleryId, $pageNumber);
        }

        return cgl_build_gallery_page_url($baseUrl, $pageNumber);
    }
}
