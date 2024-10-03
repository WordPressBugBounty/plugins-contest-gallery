<?php

if(!function_exists('cg_deactivate_ecommerce_sale_question')){
    function cg_deactivate_ecommerce_sale_question(){
        echo "<div id='cgDeactivatePayPalSaleQuestion' class='cg_hide cg_backend_action_container cg_overflow_y_hidden'>";

        echo '<div id="cgDeactivatePayPalSaleQuestionLoaderContainer" >';
            echo '<div id="cgDeactivatePayPalSaleQuestionLoader" class="cg-lds-dual-ring-gallery-hide" style="height: 300px;margin: 0;padding-top: 80px;max-height: 220px;"></div>';
        echo '</div>';


echo "<span class='cg_message_close'></span><p>&nbsp;</p><a class='cg_image_action_href' >
<span class='cg_image_action_span' id='cgDeactivatePayPalSaleConfirm' >Deactivate selling?</span>
</a></div>";
    }
}

?>