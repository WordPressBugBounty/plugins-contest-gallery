<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_gallery_export_buttons')){
    function cg_gallery_export_buttons($gallery){
        $auto=isset($_GET['cg_export_type']) ? sanitize_key($_GET['cg_export_type']) : '';
        if(!empty($_POST['contest_gal1ery_create_zip'])){$auto='files_zip';}
        if(!in_array($auto,array('files_zip','votes_csv','fields_csv'),true)){$auto='';}
        $actions=array(
            'files_zip'=>array('media-archive','Zip all files','Download original files as a ZIP archive.'),
            'votes_csv'=>array('star-filled','Export all votes','Download individual votes and voter details as CSV.'),
            'fields_csv'=>array('media-spreadsheet','Export fields & ratings','Download entry fields and rating totals as CSV.')
        );
        echo '<div class="cg_gallery_backend_actions_inner cg-gallery-export-buttons" data-gallery-id="'.absint($gallery).'" data-auto-export="'.esc_attr($auto).'">';
        foreach($actions as $type=>$action){
            $allowed=cg_gallery_export_allowed($type);
            if($type==='files_zip'){echo '<div class="cg-gallery-export-zip-card">';}
            echo '<button type="button" class="cg-gallery-export-button" data-export-type="'.esc_attr($type).'"'.($allowed ? '' : ' disabled title="Administrator rights are required for CSV exports."').'>'
                .'<span class="dashicons dashicons-'.esc_attr($action[0]).'" aria-hidden="true"></span>'
                .'<span class="cg-gallery-export-copy"><strong>'.esc_html($action[1]).'</strong><span>'.esc_html($action[2]).'</span></span></button>';
            if($type==='files_zip'){
                $target=cg_gallery_export_zip_target($gallery);
                echo '<div class="cg-gallery-export-existing'.(is_file($target['path']) ? '' : ' cg_hide').'" data-gallery-id="'.absint($gallery).'">'
                    .'<a class="cg-gallery-export-existing-download" href="'.esc_url($target['url']).'">Download ZIP</a>'
                    .'<button type="button" class="cg-gallery-export-copy-link" data-url="'.esc_attr($target['url']).'">Copy download link</button>'
                    .'<button type="button" class="cg-gallery-export-delete">Delete ZIP</button>'
                    .'<span class="cg-gallery-export-existing-message" role="status"></span></div></div>';
            }
        }
        echo '</div>';
    }
}
if(!function_exists('cg_gallery_export_container')){
    function cg_gallery_export_container(){
        echo '<div id="cgGalleryExportModalContainer" class="cg_backend_action_container cg_hide cg_do_not_remove_when_ajax_load cg_do_not_remove_when_main_empty" role="dialog" aria-modal="true" aria-labelledby="cgGalleryExportHeadline" aria-describedby="cgGalleryExportText">'
            .'<button type="button" class="cg-gallery-export-close-icon" aria-label="Close">&times;</button>'
            .'<div class="cg-gallery-export-dialog">'
            .'<div id="cgGalleryExportLabel">Gallery export</div>'
            .'<h2 id="cgGalleryExportHeadline" tabindex="-1">Preparing export</h2>'
            .'<p id="cgGalleryExportText">The export is being prepared.</p>'
            .'<div id="cgGalleryExportProgress" role="progressbar" aria-label="Export progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span></span></div>'
            .'<div class="cg-gallery-export-meta" aria-live="polite"><span id="cgGalleryExportPercent">0%</span><span id="cgGalleryExportCount">0 / 0</span></div>'
            .'<p id="cgGalleryExportError" class="cg_hide" role="alert"></p>'
            .'<p id="cgGalleryExportNote"></p>'
            .'<div class="cg-gallery-export-dialog-actions">'
            .'<button type="button" id="cgGalleryExportCancel">Cancel export</button>'
            .'<button type="button" id="cgGalleryExportRetry" class="cg_hide">Retry</button>'
            .'<a id="cgGalleryExportDownload" class="cg_hide" href="#">Download</a>'
            .'<button type="button" id="cgGalleryExportCopy" class="cg_hide">Copy download link</button>'
            .'<button type="button" id="cgGalleryExportDelete" class="cg_hide">Delete ZIP</button>'
            .'<button type="button" id="cgGalleryExportClose" class="cg_hide">Close</button>'
            .'</div></div></div>';
    }
}
