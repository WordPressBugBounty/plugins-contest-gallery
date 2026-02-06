<?php

if(!function_exists('cg_download_ecommerce_form')){
    function cg_download_ecommerce_form($GalleryID){

        // Download PayPal sale container
        echo "<form id='cgDownloadSaleForEcommerceForm' action='?page=".cg_get_version()."/index.php' method='POST' class='cg_hide'>";
            echo "<input type='hidden' name='GalleryID' value='$GalleryID'>";
            // !IMPORTANT Has to be here for action call!!!!
            echo "<input type='hidden' name='action' value='post_cg_download_original_source_for_ecommerce_sale'>";
            echo "<input type='hidden' class='cg_real_id' name='cg_real_id' value=''>";
        echo "</form>";
        // Download PayPal sale container END

    }
}

?>