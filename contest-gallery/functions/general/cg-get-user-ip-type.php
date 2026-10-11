<?php
add_action('cg_get_user_ip_type','cg_get_user_ip_type');
if(!function_exists('cg_get_user_ip_type')){
    function cg_get_user_ip_type(){
        $result = cg_resolve_user_ip();
        return $result['type'];
    }
}
