<?php

if(!function_exists('cg1l_cg_users_pin')){
    function cg1l_cg_users_pin($atts){
        // PLUGIN VERSION CHECK HERE

        contest_gal1ery_db_check();

        if(is_admin()){
            return '';
        }

        $shortcode_name = 'cg_users_pin';

        // PLUGIN VERSION CHECK HERE --- END

        global $wp_version;
        $sanitize_textarea_field = ($wp_version<4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        cg1l_include_scripts_reg_pin();

        ob_start();
        echo "<pre class='cg_main_pre  cg_10 cg_20'  style='overflow:hidden;visibility: hidden;height:650px;' >";
        include(__DIR__.'/../v10/v10-admin/users/frontend/registry/users-registry.php');
        echo "</pre>";
        $contest_gal1ery_users_registry = ob_get_clean();

        return $contest_gal1ery_users_registry;

    }

}

?>