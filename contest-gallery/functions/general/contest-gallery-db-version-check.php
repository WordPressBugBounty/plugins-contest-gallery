<?php
if(!function_exists('cg_is_fresh_install_before_create_table')){
    function cg_is_fresh_install_before_create_table($i,$p_cgal1ery_db_installed_ver){

        if($p_cgal1ery_db_installed_ver){
            return false;
        }

        global $wpdb;

        $tablename_prefix = $wpdb->base_prefix . "$i"."contest_gal1ery";
        $table_like = $wpdb->esc_like($tablename_prefix).'%';
        $existing = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_like));

        return empty($existing);

    }
}

if(!function_exists('cg_run_update_check_after_create_table')){
    function cg_run_update_check_after_create_table($i,$p_cgal1ery_db_installed_ver){

        $isFreshInstall = cg_is_fresh_install_before_create_table($i,$p_cgal1ery_db_installed_ver);

        contest_gal1ery_create_table($i);

        if(!$isFreshInstall){
            include(__DIR__."/../../update/update-check-new.php");
        }

    }
}

if(!function_exists('contest_gal1ery_db_check')){
    function contest_gal1ery_db_check(){

        global $p_cgal1ery_db_new_version;
        $p_cgal1ery_db_new_version = cg_get_db_version();

        if(!get_option("p_cgal1ery_install_date")){
            add_option("p_cgal1ery_install_date",date('Y-m-d'));
        }

        $p_cgal1ery_db_installed_ver = get_option( "p_cgal1ery_db_version" );

        if ( $p_cgal1ery_db_installed_ver != $p_cgal1ery_db_new_version ) {
            if(function_exists('contest_gal1ery_create_table')){

                // Important! If multisite, then updating might happen from network/admin or from a subsite!
                if (is_multisite()) {

                    if(!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'],"wp-admin/network")){

                        global $wpdb;

                        $wpBlogs = $wpdb->base_prefix . "blogs";

                        $getBlogIDs = $wpdb->get_results( "SELECT blog_id FROM $wpBlogs ORDER BY blog_id ASC" );

                        foreach($getBlogIDs as $key => $value){
                            foreach($value as $key1 => $value1){
                                if($value1==1){
                                    $i='';
                                }
                                else{
                                    $i=$value1."_";
                                }
                                // create tables and run update check then for each site when required
                                cg_run_update_check_after_create_table($i,$p_cgal1ery_db_installed_ver);
                            }
                        }
                    }
                    else{
                        $i=get_current_blog_id();
                        if($i==1){
                            $i="";
                        }else {
                            $i=$i."_";
                        }

                        // create tables and run update check when required
                        cg_run_update_check_after_create_table($i,$p_cgal1ery_db_installed_ver);
                    }

                }
                else{
                    $i='';
                    // create tables and run update check when required
                    cg_run_update_check_after_create_table($i,$p_cgal1ery_db_installed_ver);
                }


            }

            if($p_cgal1ery_db_installed_ver){update_option( "p_cgal1ery_db_version", $p_cgal1ery_db_new_version );}

            else{add_option( "p_cgal1ery_db_version", $p_cgal1ery_db_new_version );}

            $wp_upload_dir = wp_upload_dir();
            $rewriteRulesChangedFilePath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-general/rewrite-rules-changed-do-not-edit-or-remove.txt';
            file_put_contents($rewriteRulesChangedFilePath,'changed');// register_post_type has to be executed in register_post_type.php, which will be executed on init, after register_post_type()

        }

    }
}

/**###NORMAL###**/
if(!function_exists('contest_gal1ery_key_check')){
    function contest_gal1ery_key_check(){

        return false;

    }
}
/**###NORMAL---END###**/
