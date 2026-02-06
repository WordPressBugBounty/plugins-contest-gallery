<?php

if(!function_exists('cg_deactivate_ecommerce_form')){
    function cg_deactivate_ecommerce_form($GalleryID){

        // Deactivate sale PayPal sale
        echo "<form id='cgDeactivateSalePayPalForm' action='?page=".cg_get_version()."/index.php' method='POST' class='cg_hide'>";
            echo "<input type='hidden' name='GalleryID' value='$GalleryID'>";
            // !IMPORTANT Has to be here for action call!!!!
            echo "<input type='hidden' name='action' value='post_cg_deactivate_ecommerce_sale'>";
            echo "<input type='hidden' class='cg_real_id' name='realId' value=''>";
        echo "</form>";
        // Deactivate PayPal sale container END

    }
}

?>