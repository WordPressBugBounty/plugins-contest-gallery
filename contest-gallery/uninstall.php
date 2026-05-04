<?php
if(!defined('ABSPATH')){exit;}
// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
exit ();

if(include('uninstall-check.php')){return;}

// have to be included here, because index.php will be not processed before!!!!
include('functions/general/option/cg-add-blog-option.php');
include('functions/general/option/cg-update-blog-option.php');
include('functions/general/option/cg-get-blog-option.php');
include('functions/general/option/cg-delete-blog-option.php');
include('functions/general/cg-remove-folder-recursively.php');
include('functions/general/cg-general-functions.php');
include('functions/ecommerce/backend/gallery/cg-move-file-from-ecommerce-sell-folder.php');

if(include('uninstall-check.php')){return;}

if(!function_exists('contest_gal1ery_rm_uploads_content')){
    function contest_gal1ery_rm_uploads_content($dir){

        if(!is_dir($dir)){
            return;
        }

        // .htaccess requires extra glob!
        $dirContentLikeHtaccess = glob($dir.'/.*');

        if(!empty($dirContentLikeHtaccess)){
        foreach($dirContentLikeHtaccess as $item){
            if(is_file($item)){
                unlink($item);
            }
        }
        }

        $dirContent = glob($dir.'/*');

        if(!empty($dirContent)){
        foreach($dirContent as $item){
            // 1. Ebene
            if(is_dir($item)){
                contest_gal1ery_rm_uploads_content($item);
            }
            else{
                if(is_file($item)){
                    unlink($item);
                }
            }

        }

        }

        // is_dir check important here!
        if(is_dir($dir)){
            rmdir($dir);
        }

    }
}

if(!function_exists('cgUninstallTableExists')){
    function cgUninstallTableExists($tablename){

        global $wpdb;

        return ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$wpdb->esc_like($tablename))) == $tablename);

    }
}

if(!function_exists('cgRestoreEcommerceDownloadFilesBeforeUninstall')){
    function cgRestoreEcommerceDownloadFilesBeforeUninstall(){

        global $wpdb;

        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

        if(!cgUninstallTableExists($tablename_ecommerce_entries)){
            return;
        }

        $EcommerceDownloadEntries = $wpdb->get_results("SELECT * FROM $tablename_ecommerce_entries WHERE IsDownload='1' ORDER BY GalleryID DESC");

        foreach($EcommerceDownloadEntries as $EcommerceEntry){
            $isAllWpUploadsSuccessfulMoved = cg_move_file_from_ecommerce_sale_folder($EcommerceEntry->pid, $EcommerceEntry->GalleryID,$EcommerceEntry->id,false,true);
        }

    }
}

if(!function_exists('cgRemoveBlogFiles')){
    // Achtung! Löschen eines Plugins wird bei Multisite immer der Hauptinstanz 'network/admin' ausgeführt.
    function cgRemoveBlogFiles(){

        $upload_dir = wp_upload_dir();
            $dir = $upload_dir['basedir']."/contest-gallery";

        contest_gal1ery_rm_uploads_content($dir);

    }
}

if(!function_exists('cgRemoveGlobalPluginFiles')){
    function cgRemoveGlobalPluginFiles(){

		/**###SOME-PRO-CODE-HERE-IN-THE-MOMENT###**/

    }
}


if(!function_exists('cgDropTables')){
    function cgDropTables(){

        global $wpdb;

        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_ip = $wpdb->prefix . "contest_gal1ery_ip";
        $tablename_comments = $wpdb->prefix . "contest_gal1ery_comments";
        $tablename_options = $wpdb->prefix . "contest_gal1ery_options";
        $tablename_options_input = $wpdb->prefix . "contest_gal1ery_options_input";
        $tablename_email = $wpdb->prefix . "contest_gal1ery_mail";
        $tablename_entries = $wpdb->prefix . "contest_gal1ery_entries";
        $tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
        $tablename_form_output = $wpdb->prefix . "contest_gal1ery_f_output";
        $tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
        $tablename_email_admin = $wpdb->prefix . "contest_gal1ery_mail_admin";
        $wpOptions = $wpdb->options;
        $tablename_contest_gal1ery_create_user_entries = $wpdb->prefix . "contest_gal1ery_create_user_entries";
        $tablename_contest_gal1ery_create_user_form = $wpdb->prefix . "contest_gal1ery_create_user_form";
        $tablename_contest_gal1ery_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
        $tablename_mail_gallery = $wpdb->prefix . "contest_gal1ery_mail_gallery";
        $tablename_mail_confirmation = $wpdb->prefix . "contest_gal1ery_mail_confirmation";
        $tablename_mails_collected = $wpdb->prefix . "contest_gal1ery_mails_collected";
        $tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";
        $tablename_comments_notification_options = $wpdb->prefix . "contest_gal1ery_comments_notification_options";
        $tablename_registry_and_login_options = $wpdb->prefix . "contest_gal1ery_registry_and_login_options";
        $tablename_google_options = $wpdb->prefix . "contest_gal1ery_google_options";
        $tablename_google_users = $wpdb->prefix . "contest_gal1ery_google_users";
        $tablename_wp_pages = $wpdb->prefix . "contest_gal1ery_wp_pages";
        $posts = $wpdb->posts;
        $tablename_mail_user_comment = $wpdb->prefix . "contest_gal1ery_mail_user_comment";
        $tablename_mail_user_upload = $wpdb->prefix . "contest_gal1ery_mail_user_upload";
        $tablename_mail_user_vote = $wpdb->prefix . "contest_gal1ery_mail_user_vote";
        $tablename_user_comment_mails = $wpdb->prefix . "contest_gal1ery_user_comment_mails";
        $tablename_user_vote_mails = $wpdb->prefix . "contest_gal1ery_user_vote_mails";

	    $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
	    $tablename_ecommerce_options = $wpdb->prefix . "contest_gal1ery_ecommerce_options";
	    $tablename_ecommerce_invoice_options = $wpdb->prefix . "contest_gal1ery_ecommerce_invoice_options";
	    $tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
	    $tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";

	    $tablename_contest_gal1ery_pdf_previews = $wpdb->prefix . "contest_gal1ery_pdf_previews";

	    $tablename_contest_gal1ery_ai_prompts = $wpdb->prefix . "contest_gal1ery_ai_prompts";

	    $tablename_contest_gal1ery_mails = $wpdb->prefix . "contest_gal1ery_mails";

	    $tablename_contest_gal1ery_mail_templates = $wpdb->prefix . "contest_gal1ery_mail_templates";

        $sqlWpOptionsDelete = 'DELETE FROM ' . $wpOptions . ' WHERE';
        $sqlWpOptionsDelete .= ' option_name = %s';

        $wpdb->query( $wpdb->prepare(
            "
			$sqlWpOptionsDelete
		",
            "p_cgal1ery_db_version"
        ));

        $sql = "DROP TABLE IF EXISTS $tablename";
        $sql1 = "DROP TABLE IF EXISTS $tablename_ip";
        $sql2 = "DROP TABLE IF EXISTS $tablename_comments";
        $sql4 = "DROP TABLE IF EXISTS $tablename_options";
        $sql5 = "DROP TABLE IF EXISTS $tablename_options_input";
        $sql6 = "DROP TABLE IF EXISTS $tablename_email";
        $sql7 = "DROP TABLE IF EXISTS $tablename_entries";
        $sql8 = "DROP TABLE IF EXISTS $tablename_form_input";
        $sql9 = "DROP TABLE IF EXISTS $tablename_form_output";
        $sql10 = "DROP TABLE IF EXISTS $tablename_options_visual";
        $sql11 = "DROP TABLE IF EXISTS $tablename_email_admin";
        $sql12 = "DROP TABLE IF EXISTS $tablename_contest_gal1ery_create_user_entries";
        $sql13 = "DROP TABLE IF EXISTS $tablename_contest_gal1ery_create_user_form";
        $sql14 = "DROP TABLE IF EXISTS $tablename_contest_gal1ery_pro_options";
        $sql15 = "DROP TABLE IF EXISTS $tablename_mail_gallery";
        $sql16 = "DROP TABLE IF EXISTS $tablename_mail_confirmation";
        $sql17 = "DROP TABLE IF EXISTS $tablename_mails_collected";
        $sql18 = "DROP TABLE IF EXISTS $tablename_categories";
        $sql19 = "DROP TABLE IF EXISTS $tablename_comments_notification_options";
        $sql20 = "DROP TABLE IF EXISTS $tablename_registry_and_login_options";
        $sql21 = "DROP TABLE IF EXISTS $tablename_google_options";
        $sql22 = "DROP TABLE IF EXISTS $tablename_google_users";
        $sql23 = "DROP TABLE IF EXISTS $tablename_wp_pages";
        $sql24 = "DROP TABLE IF EXISTS $tablename_mail_user_comment";
        $sql25 = "DROP TABLE IF EXISTS $tablename_mail_user_upload";
        $sql26 = "DROP TABLE IF EXISTS $tablename_mail_user_vote";
        $sql27 = "DROP TABLE IF EXISTS $tablename_user_comment_mails";
        $sql28 = "DROP TABLE IF EXISTS $tablename_user_vote_mails";
        $sql29 = "DROP TABLE IF EXISTS $tablename_ecommerce_entries";
        $sql30 = "DROP TABLE IF EXISTS $tablename_ecommerce_options";
        $sql31 = "DROP TABLE IF EXISTS $tablename_ecommerce_invoice_options";
        $sql32 = "DROP TABLE IF EXISTS $tablename_ecommerce_orders";
        $sql33 = "DROP TABLE IF EXISTS $tablename_ecommerce_orders_items";
        $sql34 = "DROP TABLE IF EXISTS $tablename_contest_gal1ery_pdf_previews";
        $sql35 = "DROP TABLE IF EXISTS $tablename_contest_gal1ery_ai_prompts";
        $sql36 = "DROP TABLE IF EXISTS $tablename_contest_gal1ery_mails";
        $sql37 = "DROP TABLE IF EXISTS $tablename_contest_gal1ery_mail_templates";


        $wpdb->query($sql);
        $wpdb->query($sql1);
        $wpdb->query($sql2);
        $wpdb->query($sql4);
        $wpdb->query($sql5);
        $wpdb->query($sql6);
        $wpdb->query($sql7);
        $wpdb->query($sql8);
        $wpdb->query($sql9);
        $wpdb->query($sql10);
        $wpdb->query($sql11);
        $wpdb->query($sql12);
        $wpdb->query($sql13);
        $wpdb->query($sql14);
        if(cgUninstallTableExists($tablename_mail_gallery)){// extra condition for this old table otherwise an error might be shown when deleting this
            $wpdb->query($sql15);
        }
        $wpdb->query($sql16);
        $wpdb->query($sql17);
        $wpdb->query($sql18);
        $wpdb->query($sql19);
        $wpdb->query($sql20);
        $wpdb->query($sql21);
        $wpdb->query($sql22);

        // delete all sub pages of contest-gallery parent custom types before
        $WpPages = array();
        if(cgUninstallTableExists($tablename_wp_pages)){
        $WpPages = $wpdb->get_results( "SELECT WpPage FROM $tablename_wp_pages" );
        }
        $collect = '';
        $hasCgWpCustomPostTypesToDelete = false;
        foreach ($WpPages as $WpPageRow){
            $WpPage = absint($WpPageRow->WpPage);
            if(empty($WpPage)){
                continue;
            }
            if(!$collect){
                $hasCgWpCustomPostTypesToDelete = true;
                $collect .= '((ID='.$WpPage.' AND post_mime_type="contest-gallery-plugin-page" && post_type="contest-gallery") OR (post_parent='.$WpPage.' AND post_mime_type="contest-gallery-plugin-page" && post_type="contest-gallery"))';
            }else{
                $hasCgWpCustomPostTypesToDelete = true;
                $collect .= ' OR ((ID='.$WpPage.' AND post_mime_type="contest-gallery-plugin-page" && post_type="contest-gallery") OR (post_parent='.$WpPage.' AND post_mime_type="contest-gallery-plugin-page" && post_type="contest-gallery"))';
            }
        }

        if($collect && $hasCgWpCustomPostTypesToDelete && count($WpPages)){
            $postsResult = $wpdb->get_results( "SELECT DISTINCT ID FROM $posts WHERE ($collect)" );
            foreach ($postsResult as $post){
                wp_delete_post($post->ID,true);
            }
        }

	    $wpdb->query(
		    "
				DELETE FROM $posts WHERE post_mime_type = 'contest-gallery-youtube' || post_mime_type = 'contest-gallery-twitter' || post_mime_type = 'contest-gallery-instagram' || post_mime_type = 'contest-gallery-tiktok' || post_mime_type = 'contest-gallery-plugin-page' || post_mime_type = 'contest-gallery-plugin-page-galleries-slug' || post_mime_type = 'contest-gallery-plugin-page-galleries-user-slug' || post_mime_type = 'contest-gallery-plugin-page-galleries-no-voting-slug' || post_mime_type = 'contest-gallery-plugin-page-galleries-winner-slug' || post_mime_type = 'contest-gallery-plugin-page-galleries-ecommerce-slug'
			");

        $wpdb->query($sql23);

        $wpdb->query($sql24);
        $wpdb->query($sql25);
        $wpdb->query($sql26);
        $wpdb->query($sql27);
        $wpdb->query($sql28);
        $wpdb->query($sql29);
        $wpdb->query($sql30);
        $wpdb->query($sql31);
        $wpdb->query($sql32);
        $wpdb->query($sql33);
        $wpdb->query($sql34);
        $wpdb->query($sql35);
        $wpdb->query($sql36);
        $wpdb->query($sql37);


        delete_option("p_cgal1ery_reg_code");
        delete_option("p_c1_k_g_r_9");
        delete_option("p_cgal1ery_db_version");
        delete_option("p_cgal1ery_install_date");
        delete_option("p_cgal1ery_count_users");
        delete_option("p_cgal1ery_uploadscounter_reminder");
        delete_option("p_cgal1ery_count_uploads");
        delete_option("p_cgal1ery_uploadscounter_reminder");
        delete_option("p_cgal1ery_count_users");
        delete_option("p_cgal1ery_reminder_time");
        delete_option("p_cgal1ery_count_users");
        delete_option("p_cgal1ery_reg_code");

        // just to go sure all this old database entries are getting deleted
        delete_option("p_cgal1ery_pro_version_fail_status");
        delete_option("p_cgal1ery_pro_version_main_key");
        delete_option("p_cgal1ery_pro_version_main_key_is_old");
        delete_option("p_cgal1ery_pro_version_success_status");
        delete_option("p_cgal1ery_pro_version_key_new_version_string");
        delete_option("p_cgal1ery_pro_version_key_information");
        delete_option("p_cgal1ery_pro_version_fail_content_plugins_area");
        delete_option("p_cgal1ery_pro_version_fail_content_main_menu_area");
        delete_option("p_cgal1ery_pro_version_fail_registered_sites_limit_key");
        delete_option("p_cgal1ery_pro_version_fail_registered_sites_limit_reached_already_registered_websites");
        delete_option("p_cgal1ery_pro_version_fail_registered_sites_limit_reached");
        delete_option("p_cgal1ery_pro_version_fail_domain_switched");
        delete_option("p_cgal1ery_pro_version_key_activation_time");
        delete_option("p_cgal1ery_pro_version_key_expiration_time");
        delete_option("CgEntriesOwnSlugName");
        delete_option("cg_network_publish_state");
        delete_option("cg_network_keypair_v1");
        delete_option("cg_network_submit_url");
        delete_option("cg_network_unpublish_url");

    }
}


// Löschen aller Dateien von Contest Gallery --- ENDE
// Löschen von Tabellen	und Files von Contest Gallery
if (is_multisite()) {

    if(include('uninstall-check.php')){return;}

    global $wpdb;

	    $wpBlogs = $wpdb->base_prefix . "blogs";

		$getBlogIDs = $wpdb->get_col( "SELECT blog_id FROM $wpBlogs ORDER BY blog_id ASC" );
	    foreach($getBlogIDs as $blogId){
	        $blogId = absint($blogId);
	        if(empty($blogId)){
	            continue;
            }

            switch_to_blog($blogId);

            cgRestoreEcommerceDownloadFilesBeforeUninstall();
            cgDropTables();
            cgRemoveBlogFiles();

            restore_current_blog();
    }

        cgRemoveGlobalPluginFiles();
}
else{

    if(include('uninstall-check.php')){return;}

	// move entry files to original folder
    cgRestoreEcommerceDownloadFilesBeforeUninstall();
    cgDropTables();
    cgRemoveBlogFiles();
    cgRemoveGlobalPluginFiles();

}
// Löschen von Tabellen und Files von Contest Gallery --- ENDE


	  
?>
