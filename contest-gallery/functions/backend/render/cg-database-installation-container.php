<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_database_installation_container')){
    function cg_database_installation_container(){
        if(!function_exists('cg_database_install_is_pending') || !cg_database_install_is_pending() || !cg_database_install_current_user_can_complete()){
            return;
        }

        global $wpdb;

        $isFirstGallery = false;
        $i = cg_database_install_get_current_table_suffix();
        if(function_exists('cg_contest_gallery_required_tables_exist') && cg_contest_gallery_required_tables_exist($i,true)){
            $tableNameOptions = $wpdb->prefix . 'contest_gal1ery_options';
            $isFirstGallery = !(bool)$wpdb->get_var("SELECT id FROM $tableNameOptions LIMIT 1");
        }

        $redirectText = $isFirstGallery
            ? 'You will be redirected to your first gallery automatically.'
            : 'You will be redirected automatically.';

        echo '<div id="cgDatabaseInstallationContainer" class="cg_backend_action_container cg_database_installation_container cg_do_not_remove_when_ajax_load cg_do_not_remove_when_main_empty cg_hide" data-cg-pending="1" data-cg-ajax-url="'.esc_url(admin_url('admin-ajax.php')).'" data-cg-nonce="'.esc_attr(wp_create_nonce('cg_complete_database_install')).'" role="dialog" aria-modal="true" aria-labelledby="cgDatabaseInstallationHeadline" aria-describedby="cgDatabaseInstallationText">'
            .'<div class="cg_database_installation_content">'
                .'<div class="cg_database_installation_kicker">One-time setup</div>'
                .'<div class="cg_database_installation_main">'
                    .'<div class="cg_database_installation_copy">'
                        .'<h2 id="cgDatabaseInstallationHeadline">Preparing Contest Gallery</h2>'
                        .'<p id="cgDatabaseInstallationText">Contest Gallery setup is in progress and will be finished shortly.</p>'
                        .'<p class="cg_database_installation_redirect">'.esc_html($redirectText).'</p>'
                    .'</div>'
                    .'<div class="cg_database_installation_icon" aria-hidden="true"><span></span></div>'
                .'</div>'
                .'<div class="cg_database_installation_chips" aria-hidden="true"><span>Entries</span><span>Upload form</span><span>Registration</span><span>Shortcodes</span></div>'
                .'<div class="cg_database_installation_loader" role="status" aria-label="Contest Gallery setup is in progress"><span></span></div>'
                .'<p id="cgDatabaseInstallationError" class="cg_database_installation_error cg_hide">Setup could not be completed. Reload the page and try again.</p>'
                .'<button type="button" id="cgDatabaseInstallationReload" class="cg_backend_button cg_hide">Reload and retry</button>'
            .'</div>'
        .'</div>';
    }
}

?>
