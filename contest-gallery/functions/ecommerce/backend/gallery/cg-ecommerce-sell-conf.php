<?php
if(!function_exists('cg_ecommerce_sale_conf')){
    function cg_ecommerce_sale_conf(){

/*         echo "<pre>";
            print_r($_POST);
        echo "</pre>";

        var_dump(12312);

        die;*/


        $saleAction = '';
        if(isset($_POST['cgSellContainer']['saleAction'])){
            $saleAction = sanitize_text_field($_POST['cgSellContainer']['saleAction']);
        }

        if($saleAction === 'activate'){
           cg_ecommerce_sale_activate();
        }else if($saleAction === 'deactivate'){
            cg_ecommerce_sale_deactivate();
        }else{
            if(function_exists('cg_backend_ajax_error_json')){
                cg_backend_ajax_error_json('Missing or invalid sale action.', 400, 'cg_invalid_sale_action');
            }
            status_header(400);
            echo 'INVALIDSALEACTION';
            die;
        }


    }
}
