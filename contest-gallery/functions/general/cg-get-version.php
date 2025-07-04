<?php

if(!function_exists('cg_get_version')){
    function cg_get_version () {
        // gets contest-gallery or contest-gallery-pro
        $plugin_basename = explode('/',plugin_basename(__FILE__));
        return reset($plugin_basename);
    }
}

if(!function_exists('cg_get_db_version')){
    function cg_get_db_version () {
        return '26.09';// has to be floatval, especially after 21.0 update!
    }
}

if(!function_exists('cg_get_version_for_scripts')){
    function cg_get_version_for_scripts () {
        /**###NORMAL###**/
        return '26.0.9';
        /**###NORMAL-END###**/
    }
}