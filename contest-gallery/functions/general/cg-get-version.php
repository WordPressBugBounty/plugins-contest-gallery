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
        return '34.02';// Decimal schema version. Increase only when a database update is required.
    }
}

if(!function_exists('cg_get_version_for_scripts')){
    function cg_get_version_for_scripts () {
        /**###NORMAL###**/
        return '34.0.2';
        /**###NORMAL-END###**/
    }
}
