<?php

if(!function_exists('contest_gal1ery_order_summary')){
    function contest_gal1ery_order_summary($atts){

        // PLUGIN VERSION CHECK HERE

        contest_gal1ery_db_check();

        if(is_admin()){
            return '';
        }

        $shortcode_name = 'cg_order_summary';
	    $isFromOrderSummary = true;

	    // PLUGIN VERSION CHECK HERE --- END

        global $wp_version;
        $sanitize_textarea_field = ($wp_version<4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        wp_enqueue_style( 'cg_v10_css_cg_gallery', plugins_url('/../v10/v10-css-min/cg_gallery.min.css', __FILE__), false, cg_get_version_for_scripts() );
        wp_enqueue_style( 'cg_v10_css_loaders_cg_gallery', plugins_url('/../v10/v10-css/frontend/style_loaders.css', __FILE__), false, cg_get_version_for_scripts() );
        wp_enqueue_script( 'cg_v10_js_cg_gallery', plugins_url( '/../v10/v10-js-min/cg_gallery.min.js', __FILE__ ), array('jquery'), cg_get_version_for_scripts());
        wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_pro_version_info_recognized_wordpress_ajax_script_function_name', array(
            'cg_pro_version_info_recognized_ajax_url' => admin_url( 'admin-ajax.php' )
        ));
        wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_registry_wordpress_ajax_script_function_name', array(
            'cg_registry_ajax_url' => admin_url( 'admin-ajax.php' )
        ));

        wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_ecommerce_download_sale_order_wordpress_ajax_script_function_name', array(
            'cg_ecommerce_download_sale_order_ajax_url' => admin_url( 'admin-ajax.php' )
        ));

	    wp_localize_script( 'cg_v10_js_cg_gallery', 'post_cg_set_frontend_cookie_wordpress_ajax_script_function_name', array(
		    'cg_set_frontend_cookie_ajax_url' => admin_url( 'admin-ajax.php' )
	    ));

	    cg_ecommerce_include_javascript_ajax_frontend('cg_v10_js_cg_gallery','post_cg_ecommerce_payment_processing_wordpress_ajax_script_function_name',array('cg_ecommerce_payment_processing_ajax_url' => admin_url( 'admin-ajax.php' )));

	    ob_start();

	    $hasUploadSell = false;
	    $isPayPalResponseError = false;
	    $UploadGalleries = [];
	    $currenciesArray = [];// has to be setted here for sure in case no internet connection and has uploads for sale

	    echo "<pre class='cg_main_pre  cg_10 cg_20'  style='overflow:hidden;visibility: hidden;height:650px;' >";
	        include(__DIR__.'/../v10/v10-frontend/ecommerce/ecommerce-show-order-frontend.php');
	    if(!empty($hasUploadSell) && empty($isPayPalResponseError)){
				foreach ($UploadGalleries as $OrderItemID => $UploadGallery){
					if(!empty($UploadGallery)){
						$galeryID = $UploadGallery;
						$GalleryID = $UploadGallery;
						$wp_upload_dir = wp_upload_dir();
						$optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-options.json';
						$shortcode_name = 'cg_users_contact';
						$isReallyContactForm = true;
						$options = json_decode(file_get_contents($optionsFile),true);
						include(__DIR__.'/../v10/include-scripts-v10.php');
					}
				}
			}
	    echo "</pre>";

	    $frontend_gallery = ob_get_clean();
	    apply_filters( 'cg_filter_frontend_gallery', $frontend_gallery );

	    return $frontend_gallery;

    }
}

?>