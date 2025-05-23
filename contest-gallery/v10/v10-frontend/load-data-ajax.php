<?php
if(!defined('ABSPATH')){exit;}

//$galleryHashToCompare = cg_hash_function('---cngl1---', $cg1lHash);
global $wpdb;

$tablename = $wpdb->prefix . "contest_gal1ery";
$table_usermeta = $wpdb->base_prefix . "usermeta";
$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
$tablenameComments = $wpdb->prefix . "contest_gal1ery_comments";
$tablename_f_input = $wpdb->prefix . "contest_gal1ery_f_input";
$tablename_f_output = $wpdb->prefix . "contest_gal1ery_f_output";
$tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
$tablename_contact_options = $wpdb->prefix . "contest_gal1ery_contact_options";
$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
$tablenameEntries = $wpdb->prefix . "contest_gal1ery_entries";
$tablenameIP = $wpdb->prefix ."contest_gal1ery_ip";
$table_posts = $wpdb->prefix ."posts";
$tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";
$contest_gal1ery_options_input = $wpdb->prefix . "contest_gal1ery_options_input";
$tablenameEcommerceOptions = $wpdb->prefix . "contest_gal1ery_ecommerce_options";
$tablenameEcommerceEntries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
$tablename_ecommerce_options = $wpdb->prefix . "contest_gal1ery_ecommerce_options";


$wp_upload_dir = wp_upload_dir();
$upload_folder_url = $wp_upload_dir['baseurl']; // Pfad zum Bilderordner angeben

$wpNickname = '';

if(is_ssl()){
    if(strpos($upload_folder_url,'http://')===0){
        $upload_folder_url = str_replace( 'http://', 'https://', $upload_folder_url );
    }
}
else{
    if(strpos($upload_folder_url,'https://')===0){
        $upload_folder_url = str_replace( 'https://', 'http://', $upload_folder_url );
    }
}

$validGalleryIdLoaded = false;
$fromCommentsWpUserIdsArray = [];
$nicknames = null;
$nicknamesArray = [];
$collectedWpUserIdsArray = [];
$profileImages = null;
$profileImagesArray = [];
$userProfileImageSrcChecked = false;
$userProfileImageSrc = '';
$isShowProfileImageForOneOfGalleries = false;
$isShowNicknameForOneOfGalleries = false;
// collect nicknames
$collectForNicknames = "";
if(!empty($WpUserId)){// so comment will be visible if done for first uploaded image from frontend
    $collectForNicknames .= "user_id = ".$WpUserId;
}
$collectForProfileImages = "";
$isAllowCommentsForOneOfTheGalleries = false;

$realGidArray = explode('-',$realGid);

$galeryID = $realGidArray[0];
$galeryID = absint($galeryID);

$is_frontend = true;// required for check-language, this file will be loaded in frontend only!

$validGalleryIdLoaded = true;

$isUserGallery = false;
$isOnlyGalleryNoVoting = false;
$isOnlyGalleryWinner = false;
$isOnlyGalleryUser = false;
$isOnlyUploadForm = false;// since 20.0 always isOnlyContactForm
$isOnlyContactForm = false;
$isOnlyGalleryEcommerce = false;
$isOnlyUploadFormEcommerce = false;

if(strpos($galeryIDuser,'-')!==false){

    $galeryIDuserArray = explode('-',$galeryIDuser);

    if($galeryIDuserArray[1]!=='w' && $galeryIDuserArray[1]!=='u' && $galeryIDuserArray[1]!=='nv' && $galeryIDuserArray[1]!=='uf'  && $galeryIDuserArray[1]!=='cf'  && $galeryIDuserArray[1]!=='ec'){
        echo "Some sort of manipulation 786543532"; die;
    }else{
        $galeryIDuser = $galeryID.'-'.$galeryIDuserArray[1];
        if($galeryIDuserArray[1]==='w'){
            $isOnlyGalleryWinner = true;
            $galeryIDuser = $galeryID.'-w';
        }
        if($galeryIDuserArray[1]==='u'){
            $isOnlyGalleryUser = true;
            $isUserGallery = true;
            $galeryIDuser = $galeryID.'-u';
        }
        if($galeryIDuserArray[1]==='nv'){
            $isOnlyGalleryNoVoting = true;
            $galeryIDuser = $galeryID.'-nv';
        }
        if($galeryIDuserArray[1]==='cf'){
            $isOnlyContactForm = true;
            $galeryIDuser = $galeryID.'-cf';
        }
        if($galeryIDuserArray[1]==='ec'){
            $isOnlyGalleryEcommerce = true;
            $galeryIDuser = $galeryID.'-ec';
        }
    }
}else{
    $galeryIDuser = intval($galeryIDuser);
}
$jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-options.json';
$fp = fopen($jsonFile, 'r');
$options = json_decode(fread($fp, filesize($jsonFile)),true);
fclose($fp);

if(!empty($isOnlyContactForm)){
    // after options were saved, options array will be extended for other gallery ids
    $options =  (!empty($options[$galeryID])) ? $options[$galeryID] : $options;
}else{
    // after options were saved, options array will be extended for other gallery ids
    $options = (!empty($options[$galeryIDuser])) ? $options[$galeryIDuser] : $options;
}

$isFbLikeOnlyShareOn = false;

if(isset($options['pro']['FbLikeOnlyShare'])){
    if($options['pro']['FbLikeOnlyShare']==1){
        $options['general']['FbLike'] = 1;
        $isFbLikeOnlyShareOn = true;
    }
}

if(!empty($options['pro']['ShowNickname'])){
    $isShowNicknameForOneOfGalleries = true;
}

if(!empty($options['pro']['ShowProfileImage'])){
    $isShowProfileImageForOneOfGalleries = true;
}


$jsonImagesCount = count($jsonImagesData);

$p_cgal1ery_db_version = get_option( "p_cgal1ery_db_version" );

$is_user_logged_in = is_user_logged_in();

$check = wp_create_nonce("check");
$wpNickname = '';

$wpUser = null;
$WpUserId = '';
$WpUserEmail = '';

if($is_user_logged_in){
    $WpUserId = get_current_user_id();
    $current_user = wp_get_current_user();
	$WpUserEmail = $current_user->user_email;
    $wpNickname = get_user_meta( $WpUserId, 'nickname');
    if(is_array($wpNickname)){
        $wpNickname = $wpNickname[0];
    }
}

$userIP = cg1l_sanitize_method(cg_get_user_ip());
$userIPtype = cg1l_sanitize_method(cg_get_user_ip_type());
$userIPisPrivate = cg_check_if_ip_is_private($userIP);
$userIPtypesArray = cg_available_ip_getter_types();

if(!empty($options['general']['AllowComments'])){
    $isAllowCommentsForOneOfTheGalleries = true;
    $fromCommentsWpUserIdsQueryResults = $wpdb->get_results( "SELECT DISTINCT WpUserId FROM $tablenameComments WHERE WpUserId > 0 AND GalleryID = $galeryID");
    foreach($fromCommentsWpUserIdsQueryResults as $row){
        if(empty($fromCommentsWpUserIdsArray[$row->WpUserId])){
            $fromCommentsWpUserIdsArray[$row->WpUserId] = $row->WpUserId;
        }
    }
}

// correction of old five star
if($options['general']['AllowRating']==1){
    $options['general']['AllowRating']=15;
}

if($options['general']['CheckLogin']==1 and ($options['general']['AllowRating']==1 or $options['general']['AllowRating']==2 OR $options['general']['AllowRating']>=12)){
    if($is_user_logged_in){$UserLoginCheck = 1;} // Allow only registered users to vote (Wordpress profile) wird dadurch aktiviert
    else{$UserLoginCheck=0;}//Allow only registered users to vote (Wordpress profile): wird dadurch deaktiviert
}
else{$UserLoginCheck=0;}

$cgGalleryStyle = 'center-black';
$cgCenterWhite = false;
if(!empty($options['visual']['FeControlsStyle']) && $options['visual']['FeControlsStyle']=='white'){
    $cgGalleryStyle='center-white';
    $cgCenterWhite=true;
}

foreach($jsonImagesData as $image){
    if(!empty($image['WpUserId']) && empty($collectedWpUserIdsArray[$image['WpUserId']])){
        if($collectForNicknames==''){
            $collectForNicknames .= "user_id = ".$image['WpUserId'];
            $collectForProfileImages .= "WpUserId = ".$image['WpUserId'];
            $collectedWpUserIdsArray[$image['WpUserId']] = $image['WpUserId'];
        }else{
            $collectForNicknames .= " OR user_id = ".$image['WpUserId'];
            $collectForProfileImages .= " OR WpUserId = ".$image['WpUserId'];
            $collectedWpUserIdsArray[$image['WpUserId']] = $image['WpUserId'];
        }
    }
}

foreach($fromCommentsWpUserIdsArray as $wpUserId){
    if(empty($collectedWpUserIdsArray[$wpUserId])){
        if($collectForNicknames==''){
            $collectForNicknames .= "user_id = ".$wpUserId;
            $collectForProfileImages .= "WpUserId = ".$wpUserId;
            $collectedWpUserIdsArray[$wpUserId] = $wpUserId;
        }else{
            $collectForNicknames .= " OR user_id = ".$wpUserId;
            $collectForProfileImages .= " OR WpUserId = ".$wpUserId;
            $collectedWpUserIdsArray[$wpUserId] = $wpUserId;
        }
    }
}

$RatingVisibleForGalleryNoVoting = (!empty($options['general']['RatingVisibleForGalleryNoVoting'])) ? true : false;

include(__DIR__ ."/../../check-language.php");

$UploadedUserFilesAmount = 0;
$UploadedUserFilesAmountPerCategories = null;
$UploadedUserFilesAmountPerCategoryArray = [];
$CookieId = '';

if(!empty($options['pro']['RegUserUploadOnly'])){
    if($options['pro']['RegUserUploadOnly']==1 && !empty($options['pro']['RegUserMaxUpload']) && is_user_logged_in()==true){
        $UploadedUserFilesAmount = $wpdb->get_var("SELECT COUNT(*) FROM $tablename WHERE WpUserId = '$WpUserId' and GalleryID = '$galeryID'");
    }else if($options['pro']['RegUserUploadOnly']==2 && !empty($options['pro']['RegUserMaxUpload'])){
        if(isset($_COOKIE['contest-gal1ery-'.$galeryID.'-upload'])) {
            $CookieId = $_COOKIE['contest-gal1ery-'.$galeryID.'-upload'];
            $UploadedUserFilesAmount = $wpdb->get_var("SELECT COUNT(*) FROM $tablename WHERE CookieId = '$CookieId' and GalleryID = '$galeryID'");
        }else{
            $CookieId = "up".(md5(time().uniqid('cg',true)).time());
            $UploadedUserFilesAmount = 0;
        }
    }else if($options['pro']['RegUserUploadOnly']==3 && !empty($options['pro']['RegUserMaxUpload'])){
        $UploadedUserFilesAmount = $wpdb->get_var("SELECT COUNT(*) FROM $tablename WHERE IP = '$userIP' and GalleryID = '$galeryID'");
    }
    if($options['pro']['RegUserUploadOnly']==1 && !empty($options['pro']['RegUserMaxUploadPerCategory']) && is_user_logged_in()==true){
        $UploadedUserFilesAmountPerCategories = $wpdb->get_results("SELECT Category FROM $tablename WHERE WpUserId = '$WpUserId' and GalleryID = '$galeryID'");
    }else if($options['pro']['RegUserUploadOnly']==2 && !empty($options['pro']['RegUserMaxUploadPerCategory'])){
        if(isset($_COOKIE['contest-gal1ery-'.$galeryID.'-upload'])) {
            $CookieId = $_COOKIE['contest-gal1ery-'.$galeryID.'-upload'];
            $UploadedUserFilesAmountPerCategories = $wpdb->get_results("SELECT Category FROM $tablename WHERE CookieId = '$CookieId' and GalleryID = '$galeryID'");
        }else{
            $CookieId = "up".(md5(time().uniqid('cg',true)).time());
            $UploadedUserFilesAmountPerCategories = null;
        }
    }else if($options['pro']['RegUserUploadOnly']==3 && !empty($options['pro']['RegUserMaxUploadPerCategory'])){
        $UploadedUserFilesAmountPerCategories = $wpdb->get_results("SELECT Category FROM $tablename WHERE IP = '$userIP' and GalleryID = '$galeryID'");
}
    if(!empty($UploadedUserFilesAmountPerCategories)){
        $UploadedUserFilesAmountPerCategoryArray = [];
        foreach ($UploadedUserFilesAmountPerCategories as $rowObject){
            if(!isset($UploadedUserFilesAmountPerCategoryArray[$rowObject->Category])){
                $UploadedUserFilesAmountPerCategoryArray[$rowObject->Category] = 1;
            }else{
                $UploadedUserFilesAmountPerCategoryArray[$rowObject->Category]++;
            }
        }
    }
}


/*    var_dump('$UploadedUserFilesAmount');
    var_dump($UploadedUserFilesAmount);*/

$ShowFormAfterUploadOrContact = $wpdb->get_var( "SELECT ShowFormAfterUpload FROM $contest_gal1ery_options_input WHERE GalleryID='$galeryID'");

// get Exif or MultipleFiles data in one query
if(!empty($options['pro']['ShowExif'])){
    // default DEFAULT '' was added later at 21.08.2022 with update 18.0.0 to Exif field
    $queryData = $wpdb->get_results( "SELECT id, Exif, MultipleFiles FROM $tablename WHERE (GalleryID = '$galeryID' AND Active = '1' AND Exif != '' AND Exif != '0' AND Exif IS NOT NULL) OR (GalleryID = '$galeryID' AND Active = '1' AND MultipleFiles != '')");
}else{
    // default DEFAULT '' was added later at 21.08.2022 with update 18.0.0 to Exif field
    $queryData = $wpdb->get_results( "SELECT id, MultipleFiles FROM $tablename WHERE GalleryID = '$galeryID' AND Active = '1' AND MultipleFiles != ''");
}
$queryDataArray = [];
if(!empty($queryData)){
    foreach ($queryData as $rowObject){
        $queryDataArray[$rowObject->id] = [];
        if(!empty($rowObject->Exif)){
	        if(strlen($rowObject->Exif)>10){
		        $queryDataArray[$rowObject->id]['Exif'] = unserialize($rowObject->Exif);
	        }else{// otherwise some sort of corrupt string
		        $queryDataArray[$rowObject->id]['Exif'] = '';
	        }
        }
        if(!empty($rowObject->MultipleFiles) && $rowObject->MultipleFiles!='""'){
            $queryDataArray[$rowObject->id]['MultipleFiles'] = unserialize($rowObject->MultipleFiles);
        }
    }
}


//if((!empty($collectForNicknames) AND $isShowNicknameForOneOfGalleries) OR ($isAllowCommentsForOneOfTheGalleries && !empty($fromCommentsWpUserIdsArray))){
$nicknames = [];
if($collectForNicknames){
    $nicknames = $wpdb->get_results( "SELECT meta_value, user_id FROM $table_usermeta WHERE ($collectForNicknames) AND (meta_key = 'nickname')");
}
//}

/*if($is_user_logged_in){
    $current_user_id = $current_user->ID;
    $nicknameCurrentUser = $wpdb->get_var( "SELECT meta_value FROM $table_usermeta WHERE (user_id = $current_user_id) AND (meta_key = 'nickname')");
    if(!empty($nicknameCurrentUser)){
        $nicknamesArray[$current_user_id] = $nicknameCurrentUser;
    }
}*/

//if((!empty($nicknames) AND $isShowNicknameForOneOfGalleries) OR ($isAllowCommentsForOneOfTheGalleries && !empty($fromCommentsWpUserIdsArray))){
foreach ($nicknames as $wpUser){
    $nicknamesArray[$wpUser->user_id] = $wpUser->meta_value;
}
//}

if($isShowProfileImageForOneOfGalleries  OR ($isAllowCommentsForOneOfTheGalleries && !empty($fromCommentsWpUserIdsArray))){
    // collect not required here! simply all users with profile image can be get
    $profileImages = $wpdb->get_results( "SELECT WpUserId, WpUpload FROM $tablename WHERE IsProfileImage = '1' AND WpUserId >= '1'");
}

if((!empty($profileImages) AND $isShowProfileImageForOneOfGalleries) OR (!empty($profileImages) AND $isAllowCommentsForOneOfTheGalleries)){
    foreach ($profileImages as $image){
        $imgSrcLarge=wp_get_attachment_image_src($image->WpUpload, 'large');
        if(!empty($imgSrcLarge)){
            $imgSrcLarge=$imgSrcLarge[0];
            $profileImagesArray[$image->WpUserId] = $imgSrcLarge;
        }
    }
}

// get data for ecommerce sale

$ClientIdLive = '';
$ClientIdSandbox = '';
$CurrencyShort = '';
$CurrencyPosition = '';
$PriceDivider = '';
if(empty($isFromOrderSummary)){// otherwise would be reseted then
	$currenciesArray = [];
}
// collect ecommerce files data
$collectForEcommerce = '';

// will be converted in cgJsData[gid].vars.queryDataEcommerce in init-get-json
$ecommerceOptions = [];
$ecommerceCountries = [];
$ecommerceCountriesStatesCodes = [];

if((!empty($isOnlyGalleryEcommerce) || !empty($isOnlyUploadFormEcommerce) || $hasEcommerceEntry) && intval($options['general']['Version'])>=22){

    $ecommerceCountries = cg_get_countries();

    $countriesWithRequiredStatesForShipping = [
	    'AR' => 'argentina',
	    'BR' => 'brazil',
	    'CA' => 'canada',
	    'C2' => 'china',
	    'IN' => 'india',
	    'ID' => 'indonesia',
	    'IT' => 'italy',
	    'JP' => 'japan',
	    'MX' => 'mexico',
	    'US' => 'usa'
    ];

    foreach($countriesWithRequiredStatesForShipping as $countryKey => $countryValue){
	    $ecommerceCountriesStatesCodes[$countryKey] =  cg_get_country_states_codes($countryValue);
    }

    //$cgEcommerceUniqueDataLoaded = true;
    $currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();

    $ecommerceOptions = json_decode(json_encode($wpdb->get_row( "SELECT * FROM $tablename_ecommerce_options WHERE GeneralID = 1")),true);

    if(!empty($ecommerceOptions['AllowedCountries'])){
        $ecommerceOptions['AllowedCountries'] = unserialize($ecommerceOptions['AllowedCountries']);
    }
    if(!empty($ecommerceOptions['AllowedCountriesTranslations'])){
        $ecommerceOptions['AllowedCountriesTranslations'] = unserialize($ecommerceOptions['AllowedCountriesTranslations']);
    }

	$ecommerceFilesData = cg_get_ecommerce_files_data($galeryID);

    unset($ecommerceOptions['PayPalLiveSecret']);
    unset($ecommerceOptions['PayPalSandboxSecret']);

    unset($ecommerceOptions['StripeLiveSecret']);
    unset($ecommerceOptions['StripeSandboxSecret']);

}

include('data/variables-javascript.php');

include('data/check-language-javascript.php');

include('data/check-language-javascript-ecommerce.php');

include('data/check-language-javascript-general.php');

$options = (!empty($options[$galeryIDuser])) ? $options[$galeryIDuser] : $options;
$isModernOptions = (!empty($options[$galeryIDuser])) ? true : false;

if($options['general']['AllowRating']>=12  && empty($isOnlyContactForm)) {
    if(
        empty($isOnlyGalleryNoVoting) ||
        !empty($isOnlyGalleryNoVoting) && $RatingVisibleForGalleryNoVoting
    )
    {
        if(!empty($isOnlyGalleryEcommerce) && empty($options['general']['RatingVisibleForGalleryEcommerce'])){
            // do nothing
        }else{
	        include ('data/rating/configuration-five-star.php');
        }
    }
}

if(($options['general']['AllowRating']==1 OR $options['general']['AllowRating']>=12)  && empty($isOnlyContactForm)) {
    if(
        empty($isOnlyGalleryNoVoting) ||
        !empty($isOnlyGalleryNoVoting) && $RatingVisibleForGalleryNoVoting
    )
    {
	    if(!empty($isOnlyGalleryEcommerce) && empty($options['general']['RatingVisibleForGalleryEcommerce'])){
		    // do nothing
	    }else{
		    include ('data/rating/configuration-five-star.php');
	    }
    }
}

if($options['general']['AllowRating']==2 &&  empty($isOnlyContactForm)) {
    if(
        empty($isOnlyGalleryNoVoting) ||
        !empty($isOnlyGalleryNoVoting)  && $RatingVisibleForGalleryNoVoting
    ){
        include('data/rating/configuration-one-star.php');
    }
}

if(!empty($isOnlyGalleryUser)  && empty($isOnlyContactForm)) {
    include('data/user-image-ids.php');
}

// has to be checked if interval is active and off
if(!empty($orderGalleries)){
    ?>
<pre>
    <script data-cg-processing="true">
        var index = <?php echo json_encode($galeryIDuserForJs) ?>;
        cgJsData[index].vars.orderGalleries = <?php echo json_encode($orderGalleries) ?>;
    </script>
</pre>

    <?php
}

include('data/variables-javascript-general.php');

$optionsFullData = json_decode(file_get_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$realGid.'/json/'.$realGid.'-options.json'),true);
if(!empty($isUserGallery) && empty($optionsFullData[$galeryIDuser])){
    $optionsFullData['visual']['ShareButtons'] = '';// unset share buttons for gallery user, because logged in
}

?>
<pre>
<script data-cg-processing="true">
    var gid = <?php echo json_encode($galeryIDuserForJs); ?>;
    var realGid = <?php echo json_encode($realGid); ?>;
    var entryID = <?php echo json_encode($entryId); ?>;
    if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work
        cgJsData = {};
        cgJsData[gid] = {};
    }
    cgJsData[gid].optionsFullData = <?php echo json_encode($optionsFullData); ?>;

    //21.1.0.1-correction
    cgJsData[gid].optionsFullData.general.ContestEnd = 0;
    cgJsData[gid].optionsFullData.general.ContestStart = 0;

    // might not here if options were never saved
    if(cgJsData[gid].optionsFullData[realGid]){
        //21.1.0.1-correction
        cgJsData[gid].optionsFullData[realGid].general.ContestEnd = 0;
        cgJsData[gid].optionsFullData[realGid].general.ContestStart = 0;
    }

    // is here if options were saved
    if(cgJsData[gid].optionsFullData[gid]){
        //21.1.0.1-correction
        cgJsData[gid].optionsFullData[gid].general.ContestEnd = 0;
        cgJsData[gid].optionsFullData[gid].general.ContestStart = 0;
    }

    if(entryID){
        cgJsData[gid].vars.isEntryOnly = true;

        cgJsData[gid].optionsFullData.pro.CatWidget=0;
        cgJsData[gid].optionsFullData.pro.Search=0;
        cgJsData[gid].optionsFullData.general.RandomSortButton=0;
        cgJsData[gid].optionsFullData.general.AllowSort=0;
        cgJsData[gid].optionsFullData.general.FullSizeGallery=0;
        cgJsData[gid].optionsFullData.pro.GalleryUpload = 0;
        cgJsData[gid].optionsFullData.general.SliderLook = 0;
        cgJsData[gid].optionsFullData.general.ThumbLook = 0;
        cgJsData[gid].optionsFullData.general.HeightLook = 0;
        cgJsData[gid].optionsFullData.general.BlogLook = 1;

        // might not here if options were never saved
        if(cgJsData[gid].optionsFullData[realGid]){
            cgJsData[gid].optionsFullData[realGid].pro.CatWidget=0;
            cgJsData[gid].optionsFullData[realGid].pro.Search=0;
            cgJsData[gid].optionsFullData[realGid].general.RandomSortButton=0;
            cgJsData[gid].optionsFullData[realGid].general.AllowSort=0;
            cgJsData[gid].optionsFullData[realGid].general.FullSizeGallery=0;
            cgJsData[gid].optionsFullData[realGid].pro.GalleryUpload = 0;
            cgJsData[gid].optionsFullData[realGid].general.SliderLook = 0;
            cgJsData[gid].optionsFullData[realGid].general.ThumbLook = 0;
            cgJsData[gid].optionsFullData[realGid].general.HeightLook = 0;
            cgJsData[gid].optionsFullData[realGid].general.BlogLook = 1;
        }

        // might not here if options were never saved
        if(cgJsData[gid].optionsFullData[gid]){
            cgJsData[gid].optionsFullData[gid].pro.CatWidget=0;
            cgJsData[gid].optionsFullData[gid].pro.Search=0;
            cgJsData[gid].optionsFullData[gid].general.RandomSortButton=0;
            cgJsData[gid].optionsFullData[gid].general.AllowSort=0;
            cgJsData[gid].optionsFullData[gid].general.FullSizeGallery=0;
            cgJsData[gid].optionsFullData[gid].pro.GalleryUpload = 0;
            cgJsData[gid].optionsFullData[gid].general.SliderLook = 0;
            cgJsData[gid].optionsFullData[gid].general.ThumbLook = 0;
            cgJsData[gid].optionsFullData[gid].general.HeightLook = 0;
            cgJsData[gid].optionsFullData[gid].general.BlogLook = 1;
        }

        cgJsData[gid].vars.orderGalleries = {};
        if(cgJsData[gid].optionsFullData[gid]){
            cgJsData[gid].optionsFullData[gid].visual.BlogLook = 1;
        }else{
            cgJsData[gid].optionsFullData.visual.BlogLook = 1;
        }
        cgJsData[gid].vars.orderGalleries[1] = "BlogLookOrder";

    }

</script>
</pre>

<?php

$singleViewOrderFullData = json_decode(file_get_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$realGid.'/json/'.$realGid.'-single-view-order.json'),true);
?>
<pre>
<script data-cg-processing="true">
    var gid = <?php echo json_encode($galeryIDuserForJs); ?>;
    if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work
        cgJsData = {};
        cgJsData[gid] = {};
    }
    cgJsData[gid].singleViewOrderFullData = <?php echo json_encode($singleViewOrderFullData); ?>;
</script>
</pre>
<?php

$formUploadFullData = json_decode(file_get_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$realGid.'/json/'.$realGid.'-form-upload.json'),true);

?>
<pre>
<script data-cg-processing="true">
    var gid = <?php echo json_encode($galeryIDuserForJs); ?>;
    if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work;
        cgJsData = {};
        cgJsData[gid] = {};
    }
    cgJsData[gid].formUploadFullData = <?php echo json_encode($formUploadFullData); ?>;
</script>
</pre>
<?php

if(!$isOnlyUploadForm && !$isOnlyContactForm){

    $categoriesFullData = json_decode(file_get_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$realGid.'/json/'.$realGid.'-categories.json'),true);

    ?>
<pre>
    <script data-cg-processing="true">
        var gid = <?php echo json_encode($galeryIDuserForJs); ?>;
        if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work;
            cgJsData = {};
            cgJsData[gid] = {};
        }
        cgJsData[gid].categoriesFullData = <?php echo json_encode($categoriesFullData); ?>;
    </script>
    </pre>
    <?php

    $imagesFullData = $jsonImagesData;

    if(!empty($entryId)){
        if(!empty($imagesFullData[$entryId])){
            $imagesFullDataEntryID = $imagesFullData[$entryId];
            $imagesFullData = [];
            $imagesFullData[$entryId] = $imagesFullDataEntryID;
        }else{
            $imagesFullData = [];
        }
    }

    if(!empty($options['general']['WpPageParent'])){
        $structure = get_option( 'permalink_structure' );
        if(!empty($structure)){
            foreach ($imagesFullData as $imageID => $imageData){
                if(!empty($imageData[$WpPageShortCodeType])){
                    // theretically if only id is passed and not the $post object, no query will be executed
                    // https://wordpress.stackexchange.com/questions/195361/get-the-post-permalink-within-the-loop-but-without-additional-db-query
                    //https://wordpress.stackexchange.com/questions/28933/why-does-get-permalink-produces-an-add-db-request-without-post-filter
                    $imagesFullData[$imageID]['entryGuid'] = get_permalink($imageData[$WpPageShortCodeType]);
                }
            }
        }else{
            //$pageLink = get_page_link();
            foreach ($imagesFullData as $imageID => $imageData){
                if(!empty($imageData[$WpPageShortCodeType])){
                    // get_permalink always most safest way to retrieve current url
	                $imagesFullData[$imageID]['entryGuid'] = get_permalink($imageData[$WpPageShortCodeType]);
	                //$imagesFullData[$imageID]['entryGuid'] = $pageLink.'?p='.$imageData[$WpPageShortCodeType];// not possible to get this way correctly, that doesn't work at all
                }
            }
        }
    }

// get Exif or MultipleFiles data in one query
    if(!empty($options['pro']['ShowExif'])){
        // default DEFAULT '' was added later at 21.08.2022 with update 18.0.0 to Exif field
        $queryData = $wpdb->get_results( "SELECT id, Exif, MultipleFiles FROM $tablename WHERE (GalleryID = '$galeryID' AND Active = '1' AND Exif != '' AND Exif != '0' AND Exif IS NOT NULL) OR (GalleryID = '$galeryID' AND Active = '1' AND MultipleFiles != '')");
    }else{
        // default DEFAULT '' was added later at 21.08.2022 with update 18.0.0 to Exif field
        $queryData = $wpdb->get_results( "SELECT id, MultipleFiles FROM $tablename WHERE GalleryID = '$galeryID' AND Active = '1' AND MultipleFiles != ''");
    }
    $queryDataArray = [];
    if(!empty($queryData)){
        foreach ($queryData as $rowObject){
            $queryDataArray[$rowObject->id] = [];
            if(!empty($rowObject->Exif)){
	            if(strlen($rowObject->Exif)>10){
		            $queryDataArray[$rowObject->id]['Exif'] = unserialize($rowObject->Exif);
	            }else{// otherwise some sort of corrupt string
		            $queryDataArray[$rowObject->id]['Exif'] = '';
	            }
            }
            if(!empty($rowObject->MultipleFiles) && $rowObject->MultipleFiles!='""'){$queryDataArray[$rowObject->id]['MultipleFiles'] = unserialize($rowObject->MultipleFiles);}
        }
    }

	$isCGalleriesForwardToWpPageEntry = false;
    if(!empty($isGalleriesMainPage)){
	    $isCGalleriesForwardToWpPageEntry = true;
    }

    if(!empty($isCGalleries)){

	    $WinnerIdsArray = [];

	    if($shortcode_name == 'cg_gallery_winner'){
		    $WinnerIds = $wpdb->get_results("
							SELECT id 
							FROM $tablename 
							WHERE Winner = 1 ORDER BY id DESC 
						");
            foreach($WinnerIds as $WinnerObject){
	            $WinnerIdsArray[] = $WinnerObject->id;
            }
	    }

	    $EcommerceIdsArray = [];

	    if($shortcode_name == 'cg_gallery_ecommerce'){
		    $EcommerceIds = $wpdb->get_results("SELECT id 
							FROM $tablename 
							WHERE EcommerceEntry > 0 ORDER BY id DESC" );
            foreach($EcommerceIds as $EcommerceObject){
	            $EcommerceIdsArray[] = $EcommerceObject->id;
            }
	    }

        $imagesFullData = [];

        if (!function_exists('cgSortArray')) {
            function cgSortArray($a1, $a2){
                if ($a1 == $a2) return 0;
                return ($a1 > $a2) ? -1 : 1;
            }
        }

	    if(empty($galleriesIds)){
		    usort($galleryNumbers, "cgSortArray");
	    }else{// else sort by user given galleryIds
		    $galleryNumbersNew = [];
		    foreach ($galleriesIds as $galleriesIdToCheck) {
			    if(in_array($galleriesIdToCheck,$galleryNumbers)!==false){
				    $galleryNumbersNew[] = $galleriesIdToCheck;
			    }
		    }
		    $galleryNumbers = $galleryNumbersNew;
	    }

	    $dirs = [];

        //var_dump('$galleryNumbers');
        //var_dump($galleryNumbers);

	    foreach ($galleryNumbers as $galleryIdForArray) {
		    $dirs[] = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdForArray;
	    }


        if (!function_exists('cgGetNotEmptyJsonFileData')) {
            function cgGetNotEmptyJsonFileData($index,$imageIDs,$galleryIdToCheck, $imageId, $wp_upload_dir){
                $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/image-data-'.$imageId.'.json';
                $jsonFileData = json_decode(file_get_contents($jsonFile),true);
                if(!empty($jsonFileData)){
                    $jsonFileData['id'] = $imageId;// set it for sure because of previous versions
                    return $jsonFileData;
                }else{
                    $index++;
                    if(!empty($imageIDs[$index])){
                        return cgGetNotEmptyJsonFileData($index,$imageIDs,$galleryIdToCheck, $imageIDs[$index], $wp_upload_dir);
                    }else{
                        return [];
                    }
                }
            }
        }

        if (!function_exists('cgGetHighestRating')) {
            function cgGetHighestRating($a1, $a2, $AllowRating){
                // $a1 == previous file
                // $a2 == current file
                if($AllowRating=='2'){
                    if(empty($a1['addCountS'])){
                        $a1['addCountS'] = 0;
                    }
                    if(empty($a2['addCountS'])){
                        $a2['addCountS'] = 0;
                    }
                    if (intval($a1['CountS'])+intval($a1['addCountS']) == intval($a2['CountS'])+intval($a2['addCountS'])) return $a1;// return previous always, which means higher id
                    return (intval($a1['CountS'])+intval($a1['addCountS']) > intval($a2['CountS'])+intval($a2['addCountS'])) ? $a1 : $a2;
                }else if($AllowRating>='12'){
                    $array = [12,13,14,15,16,17,18,19,20];
                    $sumA1 = 0;
                    $sumA2 = 0;
                    foreach ($array as $option){
                        if($AllowRating==$option){
                            for($i=1;$i<=($option-10);$i++){
                                if(!empty($a1['CountR'.$i])){
                                    $sumA1 += intval($a1['CountR'.$i])*$i;// *$i because count and not sum is saved
                                }
                                if(!empty($a1['addCountR'.$i])){
                                    $sumA1 += intval($a1['addCountR'.$i])*$i;// *$i because count and not sum is saved
                                }
                                if(!empty($a2['CountR'.$i])){
                                    $sumA2 += intval($a2['CountR'.$i]*$i);//  *$i because count and not sum is saved
                                }
                                if(!empty($a2['addCountR'.$i])){
                                    $sumA2 += intval($a2['addCountR'.$i]*$i);//  *$i because count and not sum is saved
                                }
                            }
                        }
                    }
                    if ($sumA1 == $sumA2) return $a1;// return previous always, which means higher id
                    return ($sumA1 > $sumA2) ? $a1 : $a2;
                }
            }
        }

        if (!function_exists('cgGetHighestComments')) {
            function cgGetHighestComments($a1, $a2){
                // $a1 == previous file
                // $a2 == current file
                if ($a1['CountC'] == $a2['CountC']) return $a1;// return previous always, which means higher id
                return ($a1['CountC'] > $a2['CountC']) ? $a1 : $a2;
            }
        }

        $time = time();
	    $structure = get_option( 'permalink_structure' );

	    $PositionNumber = 1;

	    foreach ($dirs as $dir) {
		    $galleryIdToCheck = substr($dir,strrpos($dir,'-')+1, strlen($dir));
		    $optionsFileToCheck = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/'.$galleryIdToCheck.'-options.json';
		    if(file_exists($optionsFileToCheck)){// only v10 and later
			    $optionsToCheckSource = json_decode(file_get_contents($optionsFileToCheck),true);

			    if(!empty($optionsToCheckSource[$galleryIdToCheck])){
				    $optionsToCheck = $optionsToCheckSource[$galleryIdToCheck];
			    }else{
				    $optionsToCheck = $optionsToCheckSource;
			    }

                $imageDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/*.json');

			    $imageIDs = [];
			    foreach ($imageDataJsonFiles as $imageDataJsonFile) {
				    $imageId = substr(substr($imageDataJsonFile,strrpos($imageDataJsonFile,'-')+1, 30),0,-5);// do it for sure for eventually very old galleries, which might have only rowid or so
				    $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/image-data-'.$imageId.'.json';
				    if(file_exists($jsonFile) && filesize($jsonFile)>10){// size is in bytes... empty array is two chars => 2 byte
					    if(!empty($isOnlyGalleryUser) && $is_user_logged_in){
                            if(in_array($imageId,$wpUserImageIdsArray)!==false){
	                            $imageIDs[] = $imageId;
                            }
					    }else if($shortcode_name == 'cg_gallery_winner'){
                            if(in_array($imageId,$WinnerIdsArray)!==false){
	                            $imageIDs[] = $imageId;
                            }
					    }else if($shortcode_name == 'cg_gallery_ecommerce'){
                            if(in_array($imageId,$EcommerceIdsArray)!==false){
	                            $imageIDs[] = $imageId;
                            }
					    }else{
						    $imageIDs[] = $imageId;
					    }
                    }
			    }

			    $intervalConf = cg_shortcode_interval_check($galleryIdToCheck,$optionsToCheckSource,$cg_gallery_shortcode_type,true);

                if(!$intervalConf['shortcodeIsActive'] || !count($imageIDs)){
	                //var_dump('set no entries');
	                $time = $time+1;// does not work with ++ no clue why
	                $imagesFullData[$time] = cg_get_empty_entry_values();
	                $imagesFullData[$time]['PositionNumber'] = $PositionNumber;
	                if(!empty($optionsToCheck['pro']['MainTitleGalleriesView'])){
		                $imagesFullData[$time]['MainTitleGalleriesView'] = $optionsToCheck['pro']['MainTitleGalleriesView'];
	                }
	                if(!empty($optionsToCheck['pro']['SubTitleGalleriesView'])){
		                $imagesFullData[$time]['SubTitleGalleriesView'] = $optionsToCheck['pro']['SubTitleGalleriesView'];
	                }
	                if(!empty($optionsToCheck['pro']['ThirdTitleGalleriesView'])){
		                $imagesFullData[$time]['ThirdTitleGalleriesView'] = $optionsToCheck['pro']['ThirdTitleGalleriesView'];
	                }

	                $imagesFullData[$time]['gidToShow'] = $time;
	                if(!empty($optionsToCheck['general'][$WpPageParentShortCodeType])){
		                $imagesFullData[$time]['entryGuid'] = get_permalink($optionsToCheck['general'][$WpPageParentShortCodeType]);
	                }
	                $GalleryName = 'Gallery ID '.$galleryIdToCheck;
	                if(!empty($optionsToCheck['general']['GalleryName'])){
		                $GalleryName = $optionsToCheck['general']['GalleryName'];
	                }
	                $imagesFullData[$time]['GalleryName'] = $GalleryName;
	                $imagesFullData[$time]['isCGalleriesNoGalleryGid']  = $galleryIdToCheck;
	                $imagesFullData[$time]['isCGalleriesNoGalleryEntries']  = true;
	                $imagesFullData[$time]['isContestOver']  = false;

	                if(!$intervalConf['shortcodeIsActive']){
		                $imagesFullData[$time]['isContestOver']  = true;
		                $imagesFullData[$time]['isCGalleriesNoGalleryEntriesText']  = $intervalConf['TextWhenShortcodeIntervalIsOff'];
	                }else{
		                $imagesFullData[$time]['isCGalleriesNoGalleryEntriesText']  = $language_NoGalleryEntries;
	                }

                }else{
				    usort($imageIDs, "cgSortArray");
				    $AllowRatingToCheck = $optionsToCheck['general']['AllowRating'];
				    if(!($AllowRatingToCheck==2 || $AllowRatingToCheck>=12)){
					    if($galleriesOptions['PreviewHighestRated']==1){
						    $galleriesOptions['PreviewHighestRated']=0;
						    $galleriesOptions['PreviewLastAdded']=1;
					    }
                    }
				    if($galleriesOptions['PreviewLastAdded']==1){
	                    $imageId = $imageIDs[0];
	                    $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/image-data-'.$imageId.'.json';
	                    $jsonFileData = json_decode(file_get_contents($jsonFile),true);
	                    if(empty($jsonFileData)){
		                    // 1 because previously 0 is used
		                    $jsonFileData = cgGetNotEmptyJsonFileData(1,$imageIDs,$galleryIdToCheck, $imageIDs[1], $wp_upload_dir);
		                    if(empty($jsonFileData)){// then image-data files with content in that gallery
			                    continue;
		                    }
		                    $imageId = $jsonFileData['id'];
	                    }
                    }else if($galleriesOptions['PreviewHighestRated']==1 || $galleriesOptions['PreviewMostCommented']==1){
                        $previousHighestFileData = [];
                        foreach ($imageIDs as $imageID){
	                        $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-data/image-data-'.$imageID.'.json';
                            if(!file_exists($jsonFile)){
                                continue;
                            }else{
	                            $jsonFileData = json_decode(file_get_contents($jsonFile),true);
	                            if(empty($jsonFileData)){
		                            continue;
	                            }
	                            if(!empty($previousHighestFileData)){
	                                $jsonFileData['id'] = $imageID;// for sure because of previous versions
                                    if($galleriesOptions['PreviewHighestRated']==1 ){
	                                    $jsonFileTemp = cgGetHighestRating($previousHighestFileData, $jsonFileData, $AllowRatingToCheck);
                                    }else if($galleriesOptions['PreviewMostCommented']==1){
	                                    $jsonFileTemp = cgGetHighestComments($previousHighestFileData, $jsonFileData);
                                    }
		                            $previousHighestFileData = $jsonFileTemp;
	                                $jsonFileData = $jsonFileTemp;
                                }else{
	                                $jsonFileData = json_decode(file_get_contents($jsonFile),true);
	                                $jsonFileData['id'] = $imageID;// for sure because of previous versions
		                            $previousHighestFileData = $jsonFileData;
                                }
                            }
                        }
                    }

                    $jsonFileData['PositionNumber'] = $PositionNumber;

                    if(!empty($optionsToCheck['pro']['MainTitleGalleriesView'])){
	                    $jsonFileData['MainTitleGalleriesView'] = $optionsToCheck['pro']['MainTitleGalleriesView'];
                    }
                    if(!empty($optionsToCheck['pro']['SubTitleGalleriesView'])){
	                    $jsonFileData['SubTitleGalleriesView'] = $optionsToCheck['pro']['SubTitleGalleriesView'];
                    }
                    if(!empty($optionsToCheck['pro']['ThirdTitleGalleriesView'])){
	                    $jsonFileData['ThirdTitleGalleriesView'] = $optionsToCheck['pro']['ThirdTitleGalleriesView'];
                    }

                    if(!empty($optionsToCheck['pro']['ConsentYoutube'])){
	                    $jsonFileData['isCGalleriesConsentYoutube'] = $optionsToCheck['pro']['ConsentYoutube'];
                    }
                    if(!empty($optionsToCheck['pro']['ConsentInstagram'])){
	                    $jsonFileData['isCGalleriesConsentInstagram'] = $optionsToCheck['pro']['ConsentInstagram'];
                    }
                    if(!empty($optionsToCheck['pro']['ConsentTwitter'])){
	                    $jsonFileData['isCGalleriesConsentTwitter'] = $optionsToCheck['pro']['ConsentTwitter'];
                    }
                    if(!empty($optionsToCheck['pro']['ConsentTikTok'])){
	                    $jsonFileData['isCGalleriesConsentTikTok'] = $optionsToCheck['pro']['ConsentTikTok'];
                    }

				    $rowObject = $wpdb->get_row( "SELECT id, MultipleFiles FROM $tablename WHERE id = $imageId LIMIT 0, 1" );

                    if(!empty($rowObject->MultipleFiles) && $rowObject->MultipleFiles!='""') {
	                    $queryDataArray[$rowObject->id] = [];
	                    $queryDataArray[$rowObject->id]['MultipleFiles'] = unserialize($rowObject->MultipleFiles);
                    }
				    $jsonFileData['gidToShow'] = $galleryIdToCheck;
				    if(!empty($jsonFileData['ImgType']) && $jsonFileData['ImgType'] == 'con'){
                        // continue here with $optionsToCheck
                        $entryIdToDisplay = 0;
                        if(!empty($optionsToCheck['visual']['Field1IdGalleryView'])){
	                        $entryIdToDisplay = $optionsToCheck['visual']['Field1IdGalleryView'];
                        }else if(!empty($optionsToCheck['visual']['SubTitle'])){
	                        $entryIdToDisplay = $optionsToCheck['visual']['SubTitle'];
                        }else if(!empty($optionsToCheck['visual']['ThirdTitle'])){
	                        $entryIdToDisplay = $optionsToCheck['visual']['ThirdTitle'];
                        }
                        if($entryIdToDisplay){
	                        $InfoDataFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIdToCheck.'/json/image-info/image-info-'.$imageId.'.json';
                            if(file_exists($InfoDataFile)){
	                            $InfoData = json_decode(file_get_contents($InfoDataFile),true);
                            }
                            if(!empty($InfoData[$entryIdToDisplay])){
	                            $jsonFileData['infoToShowIfGalleriesCon'] = $InfoData[$entryIdToDisplay]['field-content'];
                            }
                        }
				    }

				    $imagesFullData[$imageId] = $jsonFileData;

				    if(!empty($isGalleriesMainPage)){
					    if(!empty($optionsToCheck['general'][$WpPageParentShortCodeType])){
						    if(!empty($structure)){
							    $imagesFullData[$imageId]['entryGuid'] = get_permalink($optionsToCheck['general'][$WpPageParentShortCodeType]);
						    }else{
							    $imagesFullData[$imageId]['entryGuid'] = get_permalink($optionsToCheck['general'][$WpPageParentShortCodeType]);
						    }
					    }
				    }
                }
			    $PositionNumber++;
		    }
	    }
    }

    /*
    var_dump('$imagesFullData123');
    echo "<pre>";
	    print_r($imagesFullData);
	echo "</pre>";*/

    ?>
<pre>
    <script data-cg-processing="true">

        var gid = <?php echo json_encode($galeryIDuserForJs); ?>;
        if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work;
            cgJsData = {};
            cgJsData[gid] = {};
        }
        cgJsData[gid].galleriesOptions = <?php echo json_encode($galleriesOptions); ?>;
        cgJsData[gid].isCGalleriesForwardToWpPageEntry = <?php echo json_encode($isCGalleriesForwardToWpPageEntry); ?>;
        cgJsData[gid].imagesFullData = <?php echo json_encode($imagesFullData); ?>;
        debugger
        cgJsData[gid].vars.queryDataArray = <?php echo json_encode($queryDataArray); ?>;// has to be set here after cg_galleries processing

    </script>
    </pre>
    <?php

    $imagesSortValuesFullData = $jsonImagesData;
    if(!empty($entryId)){
        if(!empty($imagesSortValuesFullData[$entryId])){
            $imagesSortValuesFullDataBefore = $imagesSortValuesFullData;
            $imagesSortValuesFullData = [];
            $imagesSortValuesFullData[$entryId] = $imagesSortValuesFullDataBefore[$entryId];
	        if(!empty($WpUserIdsArray[$entryId])){
		        $imagesSortValuesFullData[$entryId]['WpUserId'] = $WpUserIdsArray[$entryId];
	        }
        }else{
            $imagesSortValuesFullData = [];
        }
    }

    ?>
<pre>
    <script data-cg-processing="true">

        var gid = <?php echo json_encode($galeryIDuserForJs); ?>;
        if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work;
            cgJsData = {};
            cgJsData[gid] = {};
        }
        cgJsData[gid].imagesSortValuesFullData = <?php echo json_encode($imagesSortValuesFullData); ?>;

    </script>
    </pre>
    <?php

    $commentsDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/image-comments/*.json');
    $jsonCommentsData = [];

	$commentsWpUserIds = $wpdb->get_results($wpdb->prepare(
		"
                                SELECT id, WpUserId 
                                FROM $tablenameComments
                                WHERE GalleryID = %d 
                            ",
		$galeryID
	));
	$commentsWpUserIdsArray = [];
    foreach ($commentsWpUserIds as $commentsWpUserId){
	    $commentsWpUserIdsArray[$commentsWpUserId->id] = $commentsWpUserId->WpUserId;
    }

    foreach ($commentsDataJsonFiles as $jsonFile) {
        $jsonFileData = json_decode(file_get_contents($jsonFile),true);
        if(!empty($jsonFileData)){
            $stringArray= explode('/image-comments-',$jsonFile);
            $imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
            if(empty($jsonImagesData[$imageId])){// then must be from some old installation and uses some old json files
                continue;
            }else{
                $jsonCommentsData[$imageId] = $jsonFileData;
	            foreach ($jsonCommentsData[$imageId] as $key => $array){
		            if(!empty($jsonCommentsData[$imageId][$key]['insert_id']) && !empty($commentsWpUserIdsArray[$jsonCommentsData[$imageId][$key]['insert_id']])){
			            $jsonCommentsData[$imageId][$key]['WpUserId'] = $commentsWpUserIdsArray[$jsonCommentsData[$imageId][$key]['insert_id']];
		            }
	            }
            }
        }
    }

    ?>
    <pre>
        <script data-cg-processing="true">

            var gid = <?php echo json_encode($galeryIDuserForJs); ?>;
            if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work;
                cgJsData = {};
                cgJsData[gid] = {};
            }
            cgJsData[gid].jsonCommentsData = <?php echo json_encode($jsonCommentsData); ?>;

        </script>
    </pre>
    <?php

    $infoDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/image-info/*.json');
    $jsonInfoData = [];
    foreach ($infoDataJsonFiles as $jsonFile) {
        $jsonFileData = json_decode(file_get_contents($jsonFile),true);
        $stringArray= explode('/image-info-',$jsonFile);
        $imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
        if(empty($jsonImagesData[$imageId])){// then must be from some old installation and uses some old json files
            continue;
        }else{
            $jsonInfoData[$imageId] = $jsonFileData;
	        if(!empty($WpUserIdsArray[$imageId])){
		        $jsonInfoData[$imageId]['WpUserId'] = $WpUserIdsArray[$imageId];
	        }
        }
    }

    ?>
<pre>
    <script data-cg-processing="true" >
        var gid = <?php echo json_encode($galeryIDuserForJs); ?>;
        if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work;
            cgJsData = {};
            cgJsData[gid] = {};
        }
        cgJsData[gid].jsonInfoData = <?php echo json_encode($jsonInfoData); ?>;
    </script>
    </pre>
    <?php

}


if(!$validGalleryIdLoaded){
    echo "no valid gallery ID could be loaded";
}

?>


