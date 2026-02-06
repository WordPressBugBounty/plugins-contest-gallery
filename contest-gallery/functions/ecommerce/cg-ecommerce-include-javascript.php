<?php

if(!function_exists('cg_ecommerce_include_javascript_admin')){
    function cg_ecommerce_include_javascript_admin($handle,$path){

        wp_enqueue_script( $handle, plugins_url( '/../../v10/v10-js/ecommerce/'.$path, __FILE__ ), array('jquery'), cg_get_version_for_scripts() );

    }
}

if(!function_exists('cg_ecommerce_include_javascript_frontend')){
    function cg_ecommerce_include_javascript_frontend($handle,$path){

        wp_enqueue_script( $handle, plugins_url( '/../../'.$path, __FILE__ ), array('jquery'), cg_get_version_for_scripts() );

    }
}

if(!function_exists('cg_ecommerce_include_javascript_ajax_frontend')){
    function cg_ecommerce_include_javascript_ajax_frontend($handle, $object_name, $l10n){

        wp_localize_script( $handle, $object_name, $l10n);

    }
}
