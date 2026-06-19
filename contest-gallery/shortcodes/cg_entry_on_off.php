<?php

if(!function_exists('cg_entry_on_off_render_container_start')){
    function cg_entry_on_off_render_container_start($GalleryID,$cgFeControlsStyle,$BorderRadiusClass){
        echo '<div class="cg_border_radius_controls_and_containers '.$cgFeControlsStyle.' '.$BorderRadiusClass.' mainCGdivUploadForm mainCGdivUploadFormStatic" data-cg-gid="'.$GalleryID.'">';
    }
}

if(!function_exists('cg_entry_on_off_render_message')){
    function cg_entry_on_off_render_message($GalleryID,$cgFeControlsStyle,$BorderRadiusClass,$message){
        cg_entry_on_off_render_container_start($GalleryID,$cgFeControlsStyle,$BorderRadiusClass);
            echo '<div class="cg_entry_on_off_link_header">';
                echo '<p>'.$message.'</p>';
            echo '</div>';
        echo '</div>';
    }
}

if(!function_exists('cg_entry_on_off_get_form_action_url')){
    function cg_entry_on_off_get_form_action_url(){
        return remove_query_arg(array(
            'cg_entry_action',
            'cg_entry_id',
            'cg_gallery_id',
            'cg_hash',
            'cg_on_id',
            'cg_off_id'
        ));
    }
}

if(!function_exists('cg_entry_on_off_render_confirm_form')){
    function cg_entry_on_off_render_confirm_form($GalleryID,$entryID,$action,$hash,$cgFeControlsStyle,$BorderRadiusClass,$isLegacyLink){
        $actionLabel = ($action === 'activate') ? 'activation' : 'deactivation';
        $buttonLabel = ($action === 'activate') ? 'Confirm activation' : 'Confirm deactivation';
        $legacyNote = (!empty($isLegacyLink)) ? '<br><span>This is an older link format. Please confirm to continue.</span>' : '';

        cg_entry_on_off_render_container_start($GalleryID,$cgFeControlsStyle,$BorderRadiusClass);
            echo '<div class="cg_entry_on_off_link_header">';
                echo '<p>Please confirm '.$actionLabel.' for Entry ID '.absint($entryID).'.'.$legacyNote.'</p>';
            echo '</div>';
            echo '<form method="post" action="'.esc_url(cg_entry_on_off_get_form_action_url()).'" class="cg_entry_on_off_confirm_form">';
                echo '<input type="hidden" name="cg_entry_on_off_confirm" value="1">';
                echo '<input type="hidden" name="cg_entry_action" value="'.esc_attr($action).'">';
                echo '<input type="hidden" name="cg_entry_id" value="'.absint($entryID).'">';
                echo '<input type="hidden" name="cg_gallery_id" value="'.absint($GalleryID).'">';
                echo '<input type="hidden" name="cg_hash" value="'.esc_attr($hash).'">';
                echo '<button type="submit" class="cg_users_upload_submit cg_entry_on_off_confirm_submit">'.$buttonLabel.'</button>';
            echo '</form>';
        echo '</div>';
    }
}

if(!function_exists('cg_entry_on_off_render_entry_links')){
    function cg_entry_on_off_render_entry_links($objectRow,$galleryDBversion){

        $pageKeys = array(
            'WpPage' => 'cg_gallery',
            'WpPageUser' => 'cg_gallery_user',
            'WpPageNoVoting' => 'cg_gallery_no_voting',
            'WpPageWinner' => 'cg_gallery_winner'
        );

        if(floatval($galleryDBversion)>=22){
            $pageKeys['WpPageEcommerce'] = 'cg_gallery_ecommerce';
        }

        echo "<div class='cg_entry_on_off_link_container'>";

        foreach($pageKeys as $pageKey => $label){
            $pageId = (!empty($objectRow->$pageKey)) ? $objectRow->$pageKey : 0;

            if(get_post_status($pageId) == 'trash'){
                echo "<div class='cg_entry_on_off_link'>";
                    echo "<a href='".esc_url(get_bloginfo('wpurl') . "/wp-admin/edit.php?post_status=trash&post_type=contest-gallery")."' target='_blank' class='cg_entry_page_url cg_display_inline_block'>";
                        echo esc_html($label)." <b>moved to trash</b> - can be restored";
                    echo "</a>";
                echo "</div>";
            }else{
                $permalink = get_permalink($pageId);
                if($permalink===false){
                    echo "<div class='cg_entry_on_off_link'>";
                        echo "<a href='#' target='_blank' class='cg_entry_page_url cg_disabled_background_color_e0e0e0 cg_display_inline_block'>";
                            echo esc_html($label)." <b>deleted</b> - can be corrected in \"Edit options\" >>> \"Status, repair...\"";
                        echo "</a>";
                    echo "</div>";
                }else{
                    echo "<div class='cg_entry_on_off_link'>";
                        echo "<a href='".esc_url($permalink)."' target='_blank' class='cg_entry_page_url'>";
                            echo esc_html($label);
                        echo "</a>";
                    echo "</div>";
                }
            }
        }

        echo '</div>';

    }
}

if(!function_exists('contest_gal1ery_entry_on_off')){

    function contest_gal1ery_entry_on_off($atts){

        contest_gal1ery_db_check();

        if(is_admin()){
            return '';
        }

        $shortcode_name = 'cg_entry_on_off';

        extract(shortcode_atts(array(
            'id' => ''
        ), $atts));

        $atts = cg1l_sanitize_atts($atts);
        if(empty($atts['id'])){
            echo "<p style='text-align: center;'><br><br><br>Please provide gallery id<br>example: [cg_entry_on_off id=\"1\"]</p>";
            return;
        }

        $GalleryID = absint($atts['id']);

        wp_enqueue_style('cg_v10_css_cg_gallery', plugins_url('/../v10/v10-css-min/cg_gallery.min.css', __FILE__), false, cg_get_version_for_scripts());
        wp_enqueue_style('cg_v10_css_loaders_cg_gallery', plugins_url('/../v10/v10-css/frontend/style_loaders.css', __FILE__), false, cg_get_version_for_scripts());
        wp_enqueue_script('cg_v10_js_cg_gallery', plugins_url('/../v10/v10-js-min/cg_gallery.min.js', __FILE__), array('jquery'), cg_get_version_for_scripts());
        wp_localize_script('cg_v10_js_cg_gallery', 'post_cg_pro_version_info_recognized_wordpress_ajax_script_function_name', array(
            'cg_pro_version_info_recognized_ajax_url' => admin_url('admin-ajax.php')
        ));
        wp_localize_script('cg_v10_js_cg_gallery', 'post_cg_login_wordpress_ajax_script_function_name', array(
            'cg_login_ajax_url' => admin_url('admin-ajax.php')
        ));
        wp_localize_script('cg_v10_js_cg_gallery', 'CG1LAction', array(
            'nonce'   => wp_create_nonce('cg1l_action'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ));

        global $wpdb;

        $tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
        $tablename_options = $wpdb->prefix . "contest_gal1ery_options";
        $tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
        $tablename = $wpdb->prefix . "contest_gal1ery";

        $optionsVisual = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_options_visual WHERE GalleryID = %d",array($GalleryID)));
        $FeControlsStyleUpload = (!empty($optionsVisual->FeControlsStyleUpload)) ? $optionsVisual->FeControlsStyleUpload : 'white';
        $BorderRadiusUpload = (!empty($optionsVisual->BorderRadiusUpload)) ? $optionsVisual->BorderRadiusUpload : '';

        $optionsRow = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_options WHERE id = %d",array($GalleryID)));
        $galleryDBversion = (!empty($optionsRow->Version)) ? $optionsRow->Version : 0;

        $BorderRadiusClass = '';
        $cgFeControlsStyle = 'cg_fe_controls_style_white';
        if($FeControlsStyleUpload=='black'){
            $cgFeControlsStyle = 'cg_fe_controls_style_black';
        }
        if($BorderRadiusUpload=='1'){
            $BorderRadiusClass = 'cg_border_radius_controls_and_containers';
        }

        $isProVersion = false;
        $plugin_dir_path = plugin_dir_path(__FILE__);
        if(is_dir($plugin_dir_path.'/../../contest-gallery-pro') && strpos(cg_get_version_for_scripts(),'-PRO')!==false){
            $isProVersion = true;
        }

        ob_start();

        if(!$isProVersion){
            echo "<p><strong>Only available in PRO version</strong></p>";
            $contest_gal1ery_cg_entry_on_off = ob_get_clean();
            return $contest_gal1ery_cg_entry_on_off;
        }

        if(empty($GalleryID) || empty($optionsRow)){
            cg_entry_on_off_render_message($GalleryID,$cgFeControlsStyle,$BorderRadiusClass,'Gallery not found.');
            $contest_gal1ery_cg_entry_on_off = ob_get_clean();
            return $contest_gal1ery_cg_entry_on_off;
        }

        $isConfirmPost = (!empty($_POST['cg_entry_on_off_confirm']));
        $isLegacyLink = false;
        $action = '';
        $entryID = 0;
        $hash = '';
        $postedGalleryID = 0;

        if($isConfirmPost){
            $action = (!empty($_POST['cg_entry_action'])) ? sanitize_key($_POST['cg_entry_action']) : '';
            $entryID = (!empty($_POST['cg_entry_id'])) ? absint($_POST['cg_entry_id']) : 0;
            $postedGalleryID = (!empty($_POST['cg_gallery_id'])) ? absint($_POST['cg_gallery_id']) : 0;
            $hash = (!empty($_POST['cg_hash'])) ? sanitize_text_field($_POST['cg_hash']) : '';
        }elseif(!empty($_GET['cg_entry_action']) || !empty($_GET['cg_entry_id']) || !empty($_GET['cg_gallery_id'])){
            $action = (!empty($_GET['cg_entry_action'])) ? sanitize_key($_GET['cg_entry_action']) : '';
            $entryID = (!empty($_GET['cg_entry_id'])) ? absint($_GET['cg_entry_id']) : 0;
            $postedGalleryID = (!empty($_GET['cg_gallery_id'])) ? absint($_GET['cg_gallery_id']) : 0;
            $hash = (!empty($_GET['cg_hash'])) ? sanitize_text_field($_GET['cg_hash']) : '';
        }elseif((!empty($_GET['cg_on_id']) || !empty($_GET['cg_off_id'])) && !empty($_GET['cg_hash'])){
            $isLegacyLink = true;
            if(!empty($_GET['cg_on_id'])){
                $action = 'activate';
                $entryID = absint($_GET['cg_on_id']);
            }else{
                $action = 'deactivate';
                $entryID = absint($_GET['cg_off_id']);
            }
            $postedGalleryID = $GalleryID;
            $hash = sanitize_text_field($_GET['cg_hash']);
        }

        if(empty($action) && empty($entryID) && empty($hash)){
            cg_entry_on_off_render_message($GalleryID,$cgFeControlsStyle,$BorderRadiusClass,'No parameters from email forwarded for cg_entry_on_off id="'.$GalleryID.'" shortcode.');
            $contest_gal1ery_cg_entry_on_off = ob_get_clean();
            return $contest_gal1ery_cg_entry_on_off;
        }

        if(($action !== 'activate' && $action !== 'deactivate') || empty($entryID) || empty($hash)){
            cg_entry_on_off_render_message($GalleryID,$cgFeControlsStyle,$BorderRadiusClass,'Invalid activation/deactivation link.');
            $contest_gal1ery_cg_entry_on_off = ob_get_clean();
            return $contest_gal1ery_cg_entry_on_off;
        }

        if(empty($postedGalleryID) || intval($postedGalleryID) !== intval($GalleryID)){
            cg_entry_on_off_render_message($GalleryID,$cgFeControlsStyle,$BorderRadiusClass,'Invalid gallery for this activation/deactivation link.');
            $contest_gal1ery_cg_entry_on_off = ob_get_clean();
            return $contest_gal1ery_cg_entry_on_off;
        }

        $proOptions = $wpdb->get_row($wpdb->prepare("SELECT InformAdminAllowActivateDeactivate FROM $tablename_pro_options WHERE GalleryID = %d",array($GalleryID)));
        if(empty($proOptions) || intval($proOptions->InformAdminAllowActivateDeactivate) !== 1){
            cg_entry_on_off_render_message($GalleryID,$cgFeControlsStyle,$BorderRadiusClass,'Activation/deactivation by admin e-mail link is currently disabled for this gallery.');
            $contest_gal1ery_cg_entry_on_off = ob_get_clean();
            return $contest_gal1ery_cg_entry_on_off;
        }

        $objectRow = cg_entry_on_off_get_entry_row($GalleryID,$entryID);
        if(empty($objectRow)){
            cg_entry_on_off_render_message($GalleryID,$cgFeControlsStyle,$BorderRadiusClass,'Entry not found for this gallery.');
            $contest_gal1ery_cg_entry_on_off = ob_get_clean();
            return $contest_gal1ery_cg_entry_on_off;
        }

        if($isLegacyLink){
            // Legacy links only prove knowledge of the old entry-id hash. They may open the confirm page, but the POST receives a new gallery/action scoped hash.
            $hashToCompare = cg_entry_on_off_get_legacy_hash($entryID,$hash);
            if(!cg_hash_equals($hashToCompare,$hash)){
                cg_entry_on_off_render_message($GalleryID,$cgFeControlsStyle,$BorderRadiusClass,'Invalid activation/deactivation link hash.');
                $contest_gal1ery_cg_entry_on_off = ob_get_clean();
                return $contest_gal1ery_cg_entry_on_off;
            }
            $hash = cg_entry_on_off_get_action_hash($GalleryID,$entryID,$action);
        }else{
            $hashToCompare = cg_entry_on_off_get_action_hash($GalleryID,$entryID,$action,$hash);
            if(!cg_hash_equals($hashToCompare,$hash)){
                cg_entry_on_off_render_message($GalleryID,$cgFeControlsStyle,$BorderRadiusClass,'Invalid activation/deactivation link hash.');
                $contest_gal1ery_cg_entry_on_off = ob_get_clean();
                return $contest_gal1ery_cg_entry_on_off;
            }
        }

        if(!$isConfirmPost){
            cg_entry_on_off_render_confirm_form($GalleryID,$entryID,$action,$hash,$cgFeControlsStyle,$BorderRadiusClass,$isLegacyLink);
            $contest_gal1ery_cg_entry_on_off = ob_get_clean();
            return $contest_gal1ery_cg_entry_on_off;
        }

        cg_entry_on_off_render_container_start($GalleryID,$cgFeControlsStyle,$BorderRadiusClass);

        if($action === 'activate'){

            if(intval($objectRow->Active) === 1){
                echo "<div class='cg_entry_on_off_link_header'>";
                    echo "<p>Entry ID $entryID is already activated.</p>";
                echo '</div>';
                cg_entry_on_off_render_entry_links($objectRow,$galleryDBversion);
            }else{

                $updated = $wpdb->query($wpdb->prepare(
                    "UPDATE $tablename SET Active = 1 WHERE id = %d AND GalleryID = %d AND Active != 1",
                    $entryID,$GalleryID
                ));

                if(!empty($updated)){
                    $objectRow = cg_entry_on_off_get_entry_row($GalleryID,$entryID);

                    $uploadFolder = wp_upload_dir();
                    $thumbSizesWp = array();
                    $thumbSizesWp['thumbnail_size_w'] = get_option("thumbnail_size_w");
                    $thumbSizesWp['medium_size_w'] = get_option("medium_size_w");
                    $thumbSizesWp['large_size_w'] = get_option("large_size_w");
                    $imageArray = array();

                    cg_create_json_files_when_activating($GalleryID,$objectRow,$thumbSizesWp,$uploadFolder,$imageArray,$galleryDBversion);
                    cg_json_upload_form_info_data_files_new($GalleryID,array($entryID),true);

                    $mailResult = cg_entry_on_off_send_activation_mail_if_needed($GalleryID,$entryID,$objectRow,'admin-mail-link');

                    echo "<div class='cg_entry_on_off_link_header'>";
                        echo "<p>Entry ID $entryID successfully activated.</p>";
                        if(!empty($mailResult['message'])){
                            echo '<p>'.esc_html($mailResult['message']).'</p>';
                        }
                    echo '</div>';
                    cg_entry_on_off_render_entry_links($objectRow,$galleryDBversion);
                }else{
                    echo "<div class='cg_entry_on_off_link_header'>";
                        echo "<p>Entry ID $entryID is already activated.</p>";
                    echo '</div>';
                }

            }

        }else{

            $wp_upload_dir = wp_upload_dir();

            if(intval($objectRow->Active) !== 1){
                echo "<div class='cg_entry_on_off_link_header'>";
                    echo "<p>Entry ID $entryID is already deactivated.</p>";
                echo '</div>';
            }else{
                cg_deactivate_images($GalleryID,$wp_upload_dir,array($entryID => $entryID));

                echo "<div class='cg_entry_on_off_link_header'>";
                    echo "<p>Entry ID $entryID successfully deactivated.</p>";
                echo '</div>';
            }

        }

        echo '</div>';

        $contest_gal1ery_cg_entry_on_off = ob_get_clean();
        return $contest_gal1ery_cg_entry_on_off;

    }

}

?>
