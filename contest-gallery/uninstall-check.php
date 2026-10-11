<?php
if(!defined('ABSPATH')){exit;}
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// two checks to go absolutely sure, that there is no other version installed!
// checks if there is normal version left or PRO version left if one of that left
// uninstall does not continues

// A preceding FREE/PRO deletion in the same request can leave this cache stale.
wp_cache_delete('plugins','plugins');
clearstatcache();
$plugins_list = get_plugins();
/**###NORMAL###**/
if(!empty($plugins_list['contest-gallery-pro/index.php'])){
    return true;
}
$plugin_dir_path = plugin_dir_path(__FILE__);
if(is_dir ($plugin_dir_path.'/../contest-gallery-pro')){
    return true;
}
/**###NORMAL-END###**/

return false;

?>