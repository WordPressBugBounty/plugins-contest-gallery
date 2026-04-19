<?php

add_action('cg_json_single_view_order','cg_json_single_view_order');

if(!function_exists('cg_json_single_view_order')){
    function cg_json_single_view_order($GalleryID){

        global $wpdb;

        $tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";

        $wp_upload_dir = wp_upload_dir();

        // Formular Input für User wird ermittelt
        $selectFormInput = $wpdb->get_results( "SELECT id, Field_Type, Field_Order, Field_Content FROM $tablename_form_input WHERE GalleryID = '$GalleryID' AND Show_Slider = 1 ORDER BY Field_Order ASC" );

        foreach($selectFormInput as $row){
            $row->Field_Content = unserialize($row->Field_Content);
            $row->Field_Content = $row->Field_Content["titel"];
        }


        $file = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-single-view-order.json';
        $fp = fopen($file, 'w');
        fwrite($fp, json_encode($selectFormInput));
        fclose($fp);

    }
}
