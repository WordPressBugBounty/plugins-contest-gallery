<?php

if(!empty($isReallyGalleryEcommerce)){
	wp_enqueue_script( 'cg_v10_js_cg_stripe_url', 'https://js.stripe.com/v3/', [], false);
}

wp_enqueue_script( 'jquery-touch-punch' );
wp_enqueue_script( 'jquery-ui-slider' );
wp_enqueue_script( 'jquery-ui-datepicker' );
wp_enqueue_script( 'jquery-ui-sortable' );

    wp_enqueue_style( 'cg_v10_css_cg_gallery', plugins_url('/v10-css-min/cg_gallery.min.css', __FILE__), false, cg_get_version_for_scripts() );
    
    wp_enqueue_script( 'cg_v10_js_masonry', plugins_url( '/v10-js/libs/masonry.pkgd.min.js', __FILE__ ), array('jquery'), cg_get_version_for_scripts());

    wp_enqueue_script( 'cg_v10_js_cg_gallery', plugins_url( '/v10-js-min/cg_gallery.min.js', __FILE__ ), array('jquery'), cg_get_version_for_scripts());


	// Achtung! Nicht von hier verschieben und die Reihenfolge beachten. Wp_enque kommt for wp_localize
    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_rate_v10_oneStar_wordpress_ajax_script_function_name', array(
        'cg_rate_v10_oneStar_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

// Reihenfolge beachten
    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_rate_v10_fiveStar_wordpress_ajax_script_function_name', array(
        'cg_rate_v10_fiveStar_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_gallery_form_upload_wordpress_ajax_script_function_name', array(
        'cg_gallery_form_upload_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    wp_localize_script( 'cg_v10_js_cg_gallery', 'cg_show_set_comments_v10_wordpress_ajax_script_function_name', array(
        'cg_show_set_comments_v10_ajax_url' => admin_url( 'admin-ajax.php' )
    ));
    // ecommerce REIHENFOLGE BEACHTEN!
    if(!empty($isReallyGalleryEcommerce)){
        /*cg_ecommerce_include_javascript_ajax_frontend('cg_ecommerce_checkout','post_cg_ecommerce_checkout_wordpress_ajax_script_function_name',array('cg_ecommerce_checkout_ajax_url' => admin_url( 'admin-ajax.php' )));*/
        cg_ecommerce_include_javascript_ajax_frontend('cg_v10_js_cg_gallery','post_cg_ecommerce_payment_processing_wordpress_ajax_script_function_name',array('cg_ecommerce_payment_processing_ajax_url' => admin_url( 'admin-ajax.php' )));
    }

    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_gallery_user_delete_image_wordpress_ajax_script_function_name', array(
        'cg_gallery_user_delete_image_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_gallery_user_edit_image_data_wordpress_ajax_script_function_name', array(
        'cg_gallery_user_edit_image_data_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_pro_version_info_recognized_wordpress_ajax_script_function_name', array(
        'cg_pro_version_info_recognized_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_changes_recognized_wordpress_ajax_script_function_name', array(
        'cg_changes_recognized_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_set_frontend_cookie_wordpress_ajax_script_function_name', array(
        'cg_set_frontend_cookie_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_get_raw_data_from_galleries_wordpress_ajax_script_function_name', array(
        'cg_get_raw_data_from_galleries_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_get_stripe_payment_intent_wordpress_ajax_script_function_name', array(
        'cg_get_stripe_payment_intent_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_check_if_online_wordpress_ajax_script_function_name', array(
        'cg_check_if_online_ajax_url' => admin_url( 'admin-ajax.php' )
    ));

	wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_galleries_show_cg_gallery_wordpress_ajax_script_function_name', array(
		'cg_galleries_show_cg_gallery_ajax_url' => admin_url( 'admin-ajax.php' )
	));

if(empty($isFromOrderSummary)){
	if(empty($isCGalleriesAjax)){
		ob_start();
	}
    $cgPreMinHeight = 650;
    if(!empty($shortcode_name) && $shortcode_name == 'cg_users_contact'){
        $cgPreMinHeight = 250;
}
	echo "<pre class='cg_main_pre  cg_10 cg_20' style='overflow:hidden;visibility: hidden;height:".$cgPreMinHeight."px;' >";
}

include("v10-frontend/v10-get-data.php");

if(empty($isFromOrderSummary)){
	echo "</pre>";
	if(empty($isCGalleriesAjax)){
		$frontend_gallery = ob_get_clean();
		apply_filters( 'cg_filter_frontend_gallery', $frontend_gallery );
	}
}

