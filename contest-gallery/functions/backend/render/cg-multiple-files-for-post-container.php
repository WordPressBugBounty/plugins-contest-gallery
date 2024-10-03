<?php

if(!function_exists('cg_multiple_files_for_post_container')){
    function cg_multiple_files_for_post_container(){

        echo "<div id='cgMultipleFilesForPostContainer' class='cg_backend_action_container cg_hide'>
<span class='cg_message_close'></span>";
        echo "<div class='cg_preview_files_container'>";
        echo "</div>";
        echo '<div  id="cg_multiple_files_file_for_post_submit_button_container" >
            <div id="cg_multiple_files_file_for_post_ecommerce_download_files_removed_from_sale" class="cg_hide" style="margin-bottom: 20px;text-align: right;font-weight: bold;">Removed files from sale will be moved to back from inaccessible folder to official WordPress folder</div>
            <div id="cg_multiple_files_file_for_post_ecommerce_processing" class="cg_hide cg_hide_override" style="margin-bottom: 20px;text-align: right;font-weight: bold;">Original files will be moved to an inaccessible folder. If image then visible files will be watermarked.</div>
            <div  id="cg_multiple_files_file_for_post_submit_button" class="cg_backend_button_gallery_action">Save changes</div>
        </div>';
        echo "</div>";

    }
}

?>