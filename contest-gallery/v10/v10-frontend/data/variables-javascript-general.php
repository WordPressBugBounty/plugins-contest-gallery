<?php
/*
 * @ai_scan_directive
 * DATA TRANSFER ARCHITECTURE: PHP to JavaScript
 * The variables populated in this file ($variablesGeneral) are NOT echoed as inline JavaScript.
 * Instead, they are json_encoded, base64_encoded, and rendered inside hidden <textarea> elements 
 * in `v10-get-data.php` (e.g., `<textarea class="cg1l-data-variables-general">...`).
 * The JS frontend (in `wp-content/plugins/contest-gallery-js-and-css/v10/v10-js-for-min/gallery/*.js`)
 * reads these textareas, decodes the base64 string, and parses the JSON.
 * See CODE_STRUCTURE_PHP_JS_RELATION.md for more details.
 */
include_once(__DIR__.'/../../../vars/general/emojis.php');

$variablesGeneral['isShowJsLoad'] = false;
$variablesGeneral['isWereSet'] = true;
$variablesGeneral['isLoggedIn'] = is_user_logged_in();
$variablesGeneral['timezoneOffset'] = date('Z');
$variablesGeneral['dateTimeUserOriginal'] = (new DateTime('now'));
$variablesGeneral['dateTimeUser'] = cg_get_date_time_object_based_on_wp_timezone_conf(time());
$variablesGeneral['gmt_offset'] = get_option('gmt_offset');
$variablesGeneral['wp_create_nonce'] = $check;
$variablesGeneral['pluginsUrl'] = plugins_url();
$variablesGeneral['localeLang'] = get_locale();
$variablesGeneral['isSsl'] = is_ssl();
$variablesGeneral['php_upload_max_filesize'] = contest_gal1ery_return_mega_byte(ini_get('upload_max_filesize'));
$variablesGeneral['php_post_max_size'] = contest_gal1ery_return_mega_byte(ini_get('post_max_size'));
$variablesGeneral['adminUrl'] = admin_url('admin-ajax.php');
$variablesGeneral['currentUrl'] = $currentUrl;
$variablesGeneral['currentPageNumber'] = cgl_get_current_page_number();

$variablesGeneral['wpNickname'] = $wpNickname;
$variablesGeneral['WpUserEmail'] = $WpUserEmail;
$variablesGeneral['wpUserId'] = $WpUserId;

$variablesGeneral['pluginVersion'] = cg_get_version_for_scripts();
$variablesGeneral['version'] = cg_get_version();

$variablesGeneral['userIP'] = $userIP;
$variablesGeneral['userIPtype'] = $userIPtype;
$variablesGeneral['userIPisPrivate'] = $userIPisPrivate;
$variablesGeneral['userIPtypesArray'] = $userIPtypesArray;

$variablesGeneral['fullWindowConfigurationAreaIsOpened'] = false;
$variablesGeneral['loadedGalleryIDs'] = [];

$variablesGeneral['cgPageUrl'] = $cgPageUrl;
$variablesGeneral['isProVersion'] = $isProVersion;

$variablesGeneral['allowed_mime_types'] = get_allowed_mime_types();
$variablesGeneral['thumbnail_size_w'] = get_option("thumbnail_size_w");
$variablesGeneral['medium_size_w'] = get_option("medium_size_w");
$variablesGeneral['large_size_w'] = get_option("large_size_w");

$variablesGeneral['isCgWpPageEntryLandingPage'] = $isCgWpPageEntryLandingPage;
$variablesGeneral['cgWpPageEntryLandingPageGid'] = $cgWpPageEntryLandingPageGid;
$variablesGeneral['cgWpPageEntryLandingPageRealGid'] = $cgWpPageEntryLandingPageRealGid;
$variablesGeneral['cgWpPageEntryLandingPageShortCodeName'] = $cgWpPageEntryLandingPageShortCodeName;

$variablesGeneral['domain'] = get_bloginfo('wpurl');
$variablesGeneral['emojis'] = cg1l_get_emojis();

// Ecommerce section
$variablesGeneral['ecommerce'] = [];
$variablesGeneral['ecommerce']['is_admin'] = is_admin();
$variablesGeneral['ecommerce']['isFromOrderSummary'] = $isFromOrderSummary;
if(!$isFromOrderSummary){
    $variablesGeneral['ecommerce']['currenciesArray'] = $currenciesArray;
    $variablesGeneral['ecommerce']['ecommerceOptions'] = $ecommerceOptions;
    $variablesGeneral['ecommerce']['ecommerceCountries'] = $ecommerceCountries;
    $variablesGeneral['ecommerce']['isEcommerceTest'] = $isEcommerceTest;
    $variablesGeneral['ecommerce']['isEcommerceDev'] = false;
    $variablesGeneral['ecommerce']['ecommerceCountriesStatesCodes'] = $ecommerceCountriesStatesCodes;
}

?>
