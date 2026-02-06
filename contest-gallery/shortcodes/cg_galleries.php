<?php

if(!function_exists('cg_load_galleries_shortcode')){
    function cg_load_galleries_shortcode($atts,$shortcode_name = 'cg_gallery'){

		$isAjax = 0;
	    if (defined('DOING_AJAX') && DOING_AJAX) {
	        $isAjax = 1;
		}

	    $isCGalleries = 1;
	    $entryId = 0;
	    $galleriesIds = [];
	    $is_from_single_view_for_cg_galleries = 0;
	    $hasGalleriesIds = false;
        $search = ['”', '“', '„', '"', "'"];
	    if(!empty($atts['ids'])){
            $atts['ids'] = str_replace($search, '', $atts['ids']);
		    $galleriesIds = explode(',',$atts['ids']);
		    $hasGalleriesIds = true;
	    }elseif(!empty($atts['id'])){
            $atts['id'] = str_replace($search, '', $atts['id']);
		    $galleriesIds = explode(',',$atts['id']);
		    $hasGalleriesIds = true;
	    }
        $galleriesIds = array_map('trim', $galleriesIds);

	    if(!empty($_GET['cg_gallery_id'])){
		    $galeryID = intval($_GET['cg_gallery_id']);
		    $isCGalleries = 0;
		    $is_from_single_view_for_cg_galleries = 1;
	    }else{
		    global $wpdb;
		    $tablename_options = $wpdb->prefix . "contest_gal1ery_options";
		    $galeryID = $wpdb->get_var( "SELECT id FROM $tablename_options ORDER BY id DESC LIMIT 0, 1" );
	    }

	    $frontend_gallery = '';

	    $wp_upload_dir = wp_upload_dir();
	    $optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-options.json';

	    if(file_exists($optionsFile)){
			if($shortcode_name=='cg_gallery_ecommerce'){
				$isReallyGalleryEcommerce = true;
			}
		    $options = json_decode(file_get_contents($optionsFile),true);
		    include(__DIR__.'/../v10/include-scripts-v10.php');
	    }else{
		    $usedShortcode = 'cg_gallery';
		    include(__DIR__.'/../prev10/information.php');
	    }

		return $frontend_gallery;

    }
}

if(!function_exists('contest_gal1ery_frontend_galleries')){
    function contest_gal1ery_frontend_galleries($atts){
	    // PLUGIN VERSION CHECK HERE
	    contest_gal1ery_db_check();
	    if(is_admin()){
		    return '';
	    }
	    extract( shortcode_atts( array(
	    ), $atts ) );
        $atts = cg1l_sanitize_atts($atts);

	    return  cg_load_galleries_shortcode($atts);

    }
}

if(!function_exists('contest_gal1ery_frontend_galleries_user')){
    function contest_gal1ery_frontend_galleries_user($atts){
	    // PLUGIN VERSION CHECK HERE
	    contest_gal1ery_db_check();
	    if(is_admin()){
		    return '';
	    }
	    extract( shortcode_atts( array(
	    ), $atts ) );
        $atts = cg1l_sanitize_atts($atts);

	    return  cg_load_galleries_shortcode($atts,'cg_gallery_user');

    }
}

if(!function_exists('contest_gal1ery_frontend_galleries_no_voting')){
    function contest_gal1ery_frontend_galleries_no_voting($atts){
	    // PLUGIN VERSION CHECK HERE
	    contest_gal1ery_db_check();
	    if(is_admin()){
		    return '';
	    }
	    extract( shortcode_atts( array(
	    ), $atts ) );
        $atts = cg1l_sanitize_atts($atts);

	    return  cg_load_galleries_shortcode($atts,'cg_gallery_no_voting');

    }
}

if(!function_exists('contest_gal1ery_frontend_galleries_winner')){
    function contest_gal1ery_frontend_galleries_winner($atts){
	    // PLUGIN VERSION CHECK HERE
	    contest_gal1ery_db_check();
	    if(is_admin()){
		    return '';
	    }
	    extract( shortcode_atts( array(
	    ), $atts ) );
        $atts = cg1l_sanitize_atts($atts);

	    return  cg_load_galleries_shortcode($atts,'cg_gallery_winner');

    }
}

if(!function_exists('contest_gal1ery_frontend_galleries_ecommerce')){
    function contest_gal1ery_frontend_galleries_ecommerce($atts){
	    // PLUGIN VERSION CHECK HERE
	    contest_gal1ery_db_check();
	    if(is_admin()){
		    return '';
	    }
	    extract( shortcode_atts( array(
	    ), $atts ) );
        $atts = cg1l_sanitize_atts($atts);

	    return  cg_load_galleries_shortcode($atts,'cg_gallery_ecommerce');

    }
}


?>