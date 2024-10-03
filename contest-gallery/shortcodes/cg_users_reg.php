<?php

if(!function_exists('contest_gal1ery_users_registry')){
    function contest_gal1ery_users_registry($atts){
        // PLUGIN VERSION CHECK HERE

        contest_gal1ery_db_check();

        if(is_admin()){
            return '';
        }

        $shortcode_name = 'cg_users_reg';

        // PLUGIN VERSION CHECK HERE --- END

        global $wp_version;
        $sanitize_textarea_field = ($wp_version<4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        if(cg_check_if_development()){
            wp_enqueue_style( 'cg_contest_style',  plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/style.css', __FILE__), false, cg_get_version_for_scripts() );
            wp_enqueue_style( 'cg_contest_style_pro',  plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/style_pro.css', __FILE__), false, cg_get_version_for_scripts() );

            wp_enqueue_script( 'cg_pro_version_info_recognized', plugins_url( '/../../contest-gallery-js-and-css/v10/v10-js/cg-pro-version-info-recognized.js', __FILE__ ), array('jquery'), cg_get_version_for_scripts() );

            wp_localize_script( 'cg_pro_version_info_recognized', 'post_cg_pro_version_info_recognized_wordpress_ajax_script_function_name', array(
                'cg_pro_version_info_recognized_ajax_url' => admin_url( 'admin-ajax.php' )
            ));
            wp_enqueue_style( 'cg_general_form_style', plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/cg_general_form_style.css', __FILE__), false , cg_get_version_for_scripts() );
            wp_enqueue_style( 'cg_v10_contest_gallery_form_style',  plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/cg_gallery_form_style.css', __FILE__), false, cg_get_version_for_scripts() );
            wp_enqueue_style( 'cg_v10_contest_gallery_registry_form_style',  plugins_url('/../../contest-gallery-js-and-css/v10/v10-css/cg_gallery_registry_form_style.css', __FILE__), false, cg_get_version_for_scripts() );
            wp_enqueue_script( 'cg_js_general_frontend', plugins_url( '/../../contest-gallery-js-and-css/v10/v10-js/general_frontend.js', __FILE__ ), array('jquery'), cg_get_version_for_scripts() );
            wp_enqueue_script( 'cg_registry', plugins_url( '/../../contest-gallery-js-and-css/v10/v10-js/registry/users-registry.js', __FILE__ ), array('jquery'), cg_get_version_for_scripts() );
            // post_cg_registry is script name then!!!!!
            wp_localize_script( 'cg_registry', 'post_cg_registry_wordpress_ajax_script_function_name', array(
                'cg_registry_ajax_url' => admin_url( 'admin-ajax.php' )
            ));
        }else{
            wp_enqueue_style( 'cg_v10_css_cg_gallery', plugins_url('/../v10/v10-css-min/cg_gallery.min.css', __FILE__), false, cg_get_version_for_scripts() );
            wp_enqueue_script( 'cg_v10_js_cg_gallery', plugins_url( '/../v10/v10-js-min/cg_gallery.min.js', __FILE__ ), array('jquery'), cg_get_version_for_scripts());
            wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_pro_version_info_recognized_wordpress_ajax_script_function_name', array(
                'cg_pro_version_info_recognized_ajax_url' => admin_url( 'admin-ajax.php' )
            ));
            wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_registry_wordpress_ajax_script_function_name', array(
                'cg_registry_ajax_url' => admin_url( 'admin-ajax.php' )
            ));
        }
        ob_start();
        echo "<pre class='cg_main_pre  cg_10 cg_20' >";
        include(__DIR__.'/../v10/v10-admin/users/frontend/registry/users-registry.php');
        echo "</pre>";
        $contest_gal1ery_users_registry = ob_get_clean();

        return $contest_gal1ery_users_registry;

    }

}

?>