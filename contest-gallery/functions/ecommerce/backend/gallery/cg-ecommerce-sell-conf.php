<?php
if(!function_exists('cg_ecommerce_sale_conf')){
    function cg_ecommerce_sale_conf(){

/*         echo "<pre>";
            print_r($_POST);
        echo "</pre>";

        var_dump(12312);

        die;*/


        if(!empty($_POST['cgSellContainer']['isActivateSale'])){
           // var_dump('cg_ecommerce_sale_activate');
            cg_ecommerce_sale_activate();
        }else{// then deactivate can be done if active
           // var_dump('cg_ecommerce_sale_deactivate');
            cg_ecommerce_sale_deactivate();
        }


    }
}

