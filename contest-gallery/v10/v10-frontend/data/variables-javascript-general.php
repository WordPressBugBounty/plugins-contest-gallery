<pre>
<script data-cg-processing="true">

       if(typeof cgJsClass == 'undefined' ){ // required in JavaScript for first initialisation cgJsClass = cgJsClass || {}; would not work;
           cgJsClass = {};
       }

        cgJsClass.gallery = cgJsClass.gallery || {};
        cgJsClass.gallery.vars = cgJsClass.gallery.vars || {};
       cgJsClass.gallery.vars.ecommerce = cgJsClass.gallery.vars.ecommerce || {};

       if(typeof cgJsClass.gallery.vars.isWereSet == 'undefined' ){ // check if general vars were already set
           cgJsClass.gallery.vars.isWereSet = true;
           cgJsClass.gallery.vars.isLoggedIn = <?php echo json_encode(is_user_logged_in()); ?>;
           cgJsClass.gallery.vars.timezoneOffset = <?php echo date('Z'); ?>;
           cgJsClass.gallery.vars.dateTimeUserOriginal = <?php echo json_encode((new DateTime('now'))); ?>;
           cgJsClass.gallery.vars.dateTimeUser = <?php echo json_encode(cg_get_date_time_object_based_on_wp_timezone_conf(time())); ?>;
           cgJsClass.gallery.vars.gmt_offset = <?php echo json_encode(get_option('gmt_offset')); ?>;
           cgJsClass.gallery.vars.wp_create_nonce = <?php echo json_encode($check); ?>;
           cgJsClass.gallery.vars.pluginsUrl = <?php echo json_encode(plugins_url()); ?>;
           cgJsClass.gallery.vars.localeLang = <?php echo json_encode(get_locale()); ?>;
           cgJsClass.gallery.vars.isSsl = <?php echo json_encode(is_ssl()); ?>;
           cgJsClass.gallery.vars.php_upload_max_filesize = <?php echo json_encode(contest_gal1ery_return_mega_byte(ini_get('upload_max_filesize'))); ?>;
           cgJsClass.gallery.vars.php_post_max_size = <?php echo json_encode(contest_gal1ery_return_mega_byte(ini_get('post_max_size'))); ?>;
           cgJsClass.gallery.vars.adminUrl = <?php echo json_encode( admin_url('admin-ajax.php')); ?>;
           cgJsClass.gallery.vars.wpNickname = <?php echo json_encode($wpNickname); ?>;
           cgJsClass.gallery.vars.WpUserEmail = <?php echo json_encode($WpUserEmail); ?>;
           cgJsClass.gallery.vars.wpUserId = <?php echo json_encode($WpUserId); ?>;
           cgJsClass.gallery.vars.pluginVersion = <?php echo json_encode(cg_get_version_for_scripts()); ?>;
           cgJsClass.gallery.vars.version = <?php echo json_encode(cg_get_version()); ?>;
           cgJsClass.gallery.vars.userIP = <?php echo json_encode($userIP); ?>;
           cgJsClass.gallery.vars.userIPtype = <?php echo json_encode($userIPtype); ?>;
           cgJsClass.gallery.vars.userIPisPrivate = <?php echo json_encode($userIPisPrivate); ?>;
           cgJsClass.gallery.vars.userIPtypesArray = <?php echo json_encode($userIPtypesArray); ?>;
           cgJsClass.gallery.vars.fullWindowConfigurationAreaIsOpened = false;
           cgJsClass.gallery.vars.loadedGalleryIDs = <?php echo json_encode(array()); ?>;
           cgJsClass.gallery.vars.cgPageUrl = <?php echo json_encode($cgPageUrl); ?>;
           cgJsClass.gallery.vars.isProVersion = <?php echo json_encode($isProVersion); ?>;
           cgJsClass.gallery.vars.allowed_mime_types = <?php echo json_encode(get_allowed_mime_types()); ?>;
           cgJsClass.gallery.vars.thumbnail_size_w = <?php echo json_encode(get_option("thumbnail_size_w")); ?>;
           cgJsClass.gallery.vars.medium_size_w = <?php echo json_encode(get_option("medium_size_w")); ?>;
           cgJsClass.gallery.vars.large_size_w = <?php echo json_encode(get_option("large_size_w")); ?>;
           cgJsClass.gallery.vars.isCgWpPageEntryLandingPage = <?php echo json_encode($isCgWpPageEntryLandingPage); ?>;
           cgJsClass.gallery.vars.cgWpPageEntryLandingPageGid = <?php echo json_encode($cgWpPageEntryLandingPageGid); ?>;
           cgJsClass.gallery.vars.cgWpPageEntryLandingPageRealGid = <?php echo json_encode($cgWpPageEntryLandingPageRealGid); ?>;
           cgJsClass.gallery.vars.cgWpPageEntryLandingPageShortCodeName = <?php echo json_encode($cgWpPageEntryLandingPageShortCodeName); ?>;
           cgJsClass.gallery.vars.domain = <?php echo json_encode(get_bloginfo('wpurl')); ?>;
           //cgJsClass.gallery.vars.galleriesIds;// will be set and has to be set in init-get-json
           cgJsClass.gallery.vars.ecommerce.is_admin = <?php echo json_encode(is_admin()); ?>;
           cgJsClass.gallery.vars.ecommerce.isFromOrderSummary = <?php echo json_encode($isFromOrderSummary); ?>;
           if(!cgJsClass.gallery.vars.ecommerce.isFromOrderSummary){
               cgJsClass.gallery.vars.ecommerce.currenciesArray = <?php echo json_encode($currenciesArray); ?>;
               cgJsClass.gallery.vars.ecommerce.ecommerceOptions = <?php echo json_encode($ecommerceOptions); ?>;
               cgJsClass.gallery.vars.ecommerce.ecommerceCountries = <?php echo json_encode($ecommerceCountries); ?>;
               cgJsClass.gallery.vars.ecommerce.isEcommerceTest = <?php echo json_encode($isEcommerceTest); ?>;
               cgJsClass.gallery.vars.ecommerce.isEcommerceDev = <?php echo json_encode(false); ?>;
               cgJsClass.gallery.vars.ecommerce.ecommerceCountriesStatesCodes = <?php echo json_encode($ecommerceCountriesStatesCodes); ?>;
           }
    }

</script>
</pre>