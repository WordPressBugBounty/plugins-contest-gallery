<?php

if(!function_exists('contest_gal1ery_frontend_gallery_no_voting')){

    function contest_gal1ery_frontend_gallery_no_voting($atts){

        // PLUGIN VERSION CHECK HERE

        contest_gal1ery_db_check();

        if(is_admin()){
            return '';
        }

        extract( shortcode_atts( array(
            'id' => ''
        ), $atts ) );
        $atts = cg1l_sanitize_atts($atts);

	    $galeryID = 0;
	    if(!empty($atts['id'])){
		    $galeryID = trim($atts['id']);
	    }

        $entryId = 0;
        if(!empty($atts['entry_id'])){
            $entryId = $atts['entry_id'];
        }

        $frontend_gallery = '';

        $shortcode_name = 'cg_gallery_no_voting';

        $wp_upload_dir = wp_upload_dir();
        $optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-options.json';

        if(file_exists($optionsFile)){
            $options = json_decode(file_get_contents($optionsFile),true);
            include(__DIR__.'/../v10/include-scripts-v10.php');
        }
        else{

            $usedShortcode = 'cg_gallery_no_voting';
            include(__DIR__.'/../prev10/information.php');

        }

        return $frontend_gallery;

    }
}

?>