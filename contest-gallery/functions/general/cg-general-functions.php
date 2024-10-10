<?php

if(!function_exists('cg_create_slug_name_galleries_posts_if_required')){
    function cg_create_slug_name_galleries_posts_if_required(){

        global $wpdb;
        $tablename_posts = $wpdb->prefix . "posts";

        $wp_upload_dir = wp_upload_dir();

        /*
var_dump(	get_page_by_path( 'contest-gallery', OBJECT, 'page' ));
var_dump(	get_page_by_path( 'contest-gallery-id-213/aaa', OBJECT, 'contest-gallery' ));
die;*/

        /*$CgEntriesOwnSlugNameGalleries = get_option('CgEntriesOwnSlugNameGalleries');
        $CgEntriesOwnSlugNameGalleries = (!empty($CgEntriesOwnSlugNameGalleries)) ? $CgEntriesOwnSlugNameGalleries : 'contest-galleries';
        $page = get_page_by_path( $CgEntriesOwnSlugNameGalleries, OBJECT, 'page');*/

        //'post_mime_type'=>'contest-gallery-plugin-page-galleries-'.$mimeType.'slug',

        $pagesArray = [];

        $WpID = $wpdb->get_var( "SELECT ID FROM $tablename_posts WHERE post_mime_type='contest-gallery-plugin-page-galleries-slug'" );// seems to be more reliable method to check as get_page_by_path
        //$page = get_page_by_path( 'contest-galleries', OBJECT, 'page');

        if(empty($WpID)){
            $array = cg_post_type_page_galleries_slug_array();
            $WpID = wp_insert_post($array);
        }

        $pagesArray['contest-galleries'] = intval($WpID);

        /*$CgEntriesOwnSlugNameGalleriesUser = get_option('CgEntriesOwnSlugNameGalleriesUser');
        $CgEntriesOwnSlugNameGalleriesUser = (!empty($CgEntriesOwnSlugNameGalleriesUser)) ? $CgEntriesOwnSlugNameGalleriesUser : 'contest-galleries-user';
        $page = get_page_by_path( $CgEntriesOwnSlugNameGalleriesUser, OBJECT, 'page');*/

        $WpID = $wpdb->get_var( "SELECT ID FROM $tablename_posts WHERE post_mime_type='contest-gallery-plugin-page-galleries-user-slug'" );
        if(empty($WpID)){
            $array = cg_post_type_page_galleries_slug_array('user');
            $WpID = wp_insert_post($array);
        }

        $pagesArray['contest-galleries-user'] = intval($WpID);

        /*$CgEntriesOwnSlugNameGalleriesNoVoting = get_option('CgEntriesOwnSlugNameGalleriesNoVoting');
        $CgEntriesOwnSlugNameGalleriesNoVoting = (!empty($CgEntriesOwnSlugNameGalleriesNoVoting)) ? $CgEntriesOwnSlugNameGalleriesNoVoting : 'contest-galleries-no-voting';
        $page = get_page_by_path( $CgEntriesOwnSlugNameGalleriesNoVoting, OBJECT, 'page');*/

        $WpID = $wpdb->get_var( "SELECT ID FROM $tablename_posts WHERE post_mime_type='contest-gallery-plugin-page-galleries-no-voting-slug'" );
        if(empty($WpID)){
            $array = cg_post_type_page_galleries_slug_array('no-voting');
            $WpID = wp_insert_post($array);
        }

        $pagesArray['contest-galleries-no-voting'] = intval($WpID);

        /*$CgEntriesOwnSlugNameGalleriesWinner = get_option('CgEntriesOwnSlugNameGalleriesWinner');
        $CgEntriesOwnSlugNameGalleriesWinner = (!empty($CgEntriesOwnSlugNameGalleriesWinner)) ? $CgEntriesOwnSlugNameGalleriesWinner : 'contest-galleries-winner';
        $page = get_page_by_path( $CgEntriesOwnSlugNameGalleriesWinner, OBJECT, 'page');*/

        $WpID = $wpdb->get_var( "SELECT ID FROM $tablename_posts WHERE post_mime_type='contest-gallery-plugin-page-galleries-winner-slug'" );
        if(empty($WpID)){
            $array = cg_post_type_page_galleries_slug_array('winner');
            $WpID = wp_insert_post($array);
        }

        $pagesArray['contest-galleries-winner'] = intval($WpID);

        /*$CgEntriesOwnSlugNameGalleriesEcommerce = get_option('CgEntriesOwnSlugNameGalleriesEcommerce');
        $CgEntriesOwnSlugNameGalleriesEcommerce = (!empty($CgEntriesOwnSlugNameGalleriesEcommerce)) ? $CgEntriesOwnSlugNameGalleriesEcommerce : 'contest-galleries-ecommerce';
        $page = get_page_by_path( $CgEntriesOwnSlugNameGalleriesEcommerce, OBJECT, 'page');*/

        $WpID = $wpdb->get_var( "SELECT ID FROM $tablename_posts WHERE post_mime_type='contest-gallery-plugin-page-galleries-ecommerce-slug'" );
        if(empty($WpID)){
            $array = cg_post_type_page_galleries_slug_array('ecommerce');
            $WpID = wp_insert_post($array);
        }

        $pagesArray['contest-galleries-ecommerce'] = intval($WpID);

        $jsonDir = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-general/json';
        if(!is_dir($jsonDir)){
            mkdir($jsonDir,0755);
        }

        file_put_contents($jsonDir.'/galleries-pages.json',json_encode($pagesArray));

    }
}


if(!function_exists('cg_insert_into_contest_gal1ery_wp_pages')){
    function cg_insert_into_contest_gal1ery_wp_pages($WpPage){
        global $wpdb;
        $tablename_wp_pages = $wpdb->prefix . "contest_gal1ery_wp_pages";
        $wpdb->query( $wpdb->prepare(
            "
				INSERT INTO $tablename_wp_pages
					( id,WpPage
					 )
					VALUES ( %s,%d
					)
				",
            '',$WpPage
        ) );
    }
}

if(!function_exists('cg_get_filename_clean_from_url_or_url_part')){
    function cg_get_filename_clean_from_url_or_url_part($url){
        if(strpos($url,'/')!==false){
            $url = substr($url,strrpos($url,'/')+1,strlen($url));
        }
        $filenameClean = substr($url,0,strrpos($url,'.'));
        return $filenameClean;
    }
}

if(!function_exists('cg_add_replace_to_url_filename')){
    function cg_add_replace_to_url_filename($path,$url){
        // example cg_add_replace_to_url_filename('2024/05/test.jpg','/var/www/html/contest-gallery-pro/wp-content/plugins/contest-gallery-pro/functions/general')
        $filenameClean = cg_get_filename_clean_from_url_or_url_part($url);
        $fileType = substr($url,strrpos($url,'.')+1,strlen($url));
        $urlLeftPart = '';
        if(strpos($url,'/')!==false){
            $urlLeftPart = substr($url,0,(strrpos($url,'/')+1));
        }
        if(strpos($filenameClean,'-replaced')!==false){
            $exploded = explode('-replaced',$filenameClean);
            $filenameClean = $exploded[0];// then rest must be something like replaced-kasdfjaksldfj whatever, simply construct new then
            if(empty($filenameClean)){
                $filenameClean = 'xyz';// simply to have something at the begging if was -replaced... before only
            }
        }
        $add = 'replaced';
        $urlNew = $urlLeftPart.$filenameClean.'-'.$add.'.'.$fileType;
        $filenameCleanNew = $filenameClean.'-'.$add;
        if(file_exists($path.'/'.$urlNew)){
            for ($i = 2; $i <= 10000; $i++){
                $add = 'replaced-'.$i;
                $urlNew = $urlLeftPart.$filenameClean.'-'.$add.'.'.$fileType;
                $filenameCleanNew = $filenameClean.'-'.$add;
                if(!file_exists($path.'/'.$urlNew)){
                    break;
                }
            }
        }
        return [
            'add' => $add,
            'filenameCleanNew' => $filenameCleanNew,
            'urlNew' => $urlNew
        ];
    }
}

if(!function_exists('cg_replace_url_filename_with_replaced')){
    function cg_replace_url_filename_with_replaced($filename,$filenameNew){
        $filenameNewRightPart = $filenameNew;
        if(strpos($filenameNew,'/')!==false){
            $filenameNewRightPart = substr($filenameNew,strrpos($filenameNew,'/')+1,strlen($filenameNew));
        }
        if(strpos($filename,'/')!==false){
            $filenameReplacedLeftPart = substr($filename,0,(strrpos($filename,'/')+1));
            $filenameReplaced = $filenameReplacedLeftPart.$filenameNewRightPart;
        }else{
            $filenameReplaced = $filenameNewRightPart;
        }
        return $filenameReplaced;
    }
}

if(!function_exists('cg_get_guid')){
    // #!IMPORTANT: requires only to get executed if changes has to be visible right after change!!!!
    // so in gallery... when user upload in frontend or user edit input field in frontend
    // otherwise use get_permalink cause it is page by WordPress and should be faster
    // since version 24 changing permalink is not possible so or so
    function cg_get_guid($postId,$domain = '',$CgEntriesOwnSlugNameOption = 'contest-gallery', $Version = 0,$permalink_structure = ''){
        // get_permalink always most safest way to retrieve current url
        return get_permalink($postId);

        global $wpdb;
        $table_posts = $wpdb->prefix."posts";
        if(!empty($permalink_structure)){
            if(intval($Version) < 24){
                $guid = $wpdb->get_var("SELECT guid FROM $table_posts WHERE ID = '$postId'");
                $lastChar = substr($domain, -1);
                if($lastChar=='/'){
                    $domain = substr($domain, 0, -1);
                }
                if(strpos($guid,$domain."/$CgEntriesOwnSlugNameOption/")!==0){
                    $lastPart = substr($guid,strlen($domain.'/'),strlen($guid));
                    $lastPart = substr($lastPart,strpos($lastPart,'/')+1,strlen($lastPart));
                    $guid = $domain."/$CgEntriesOwnSlugNameOption/".$lastPart;
                }
                return get_permalink($postId);

                //return $guid;
            }else{
                return get_permalink($postId);
            }
        }else{
            $lastChar = substr($domain, -1);
            if($lastChar=='/'){
                $domain = substr($domain, 0, -1);
            }
            return get_permalink($postId);
            //return $domain.'?p='.$postId;// that doesn't work at all
        }
    }
}

if(!function_exists('cg_get_post_name')){
    function cg_get_post_name($postId){
        global $wpdb;
        $table_posts = $wpdb->prefix."posts";
        $post_name = $wpdb->get_var("SELECT post_name FROM $table_posts WHERE ID = '$postId'");
        return $post_name;
    }
}

if(!function_exists('cg_update_custom_post_type_name')){
    function cg_update_custom_post_type_name($postId,$Version,$title_original,$title_modified,$postParent,$checkIfExists = false,$IsFromCopyGalleryOrActualizeAll=false){

        //echo "<pre>";
        //print_r(debug_backtrace(6));
        //echo "</pre>";

        global $cg_post_names_for_a_title;
        global $cg_post_names_for_a_title_numbers;
        global $cg_processed_post_titles;
        global $wpdb;
        $table_posts = $wpdb->prefix."posts";

        if($checkIfExists){// check if exists is always for first check WpPage to determine $title_modified

            // only numbers like 3333, then has to be 3333-2 otherwise wordpress will always redirect to the parent site
            if(is_numeric($title_original)){
                $title_original = $title_original.'-2';
            }

            if(!isset($cg_post_names_for_a_title_numbers[$title_original])){
                $cg_post_names_for_a_title_numbers[$title_original] = [];
            }

            if($Version >= 24){
                $post_type = 'contest-g';
            }else{
                $post_type = 'contest-gallery';
            }

            $postExistsCount = $wpdb->get_var("SELECT COUNT(*) as NumberOfRows FROM $table_posts WHERE post_title = '$title_original' AND post_parent = '$postParent' AND post_type = '$post_type' AND ID != $postId  LIMIT 1 ");

            if($postExistsCount){

                if(!isset($cg_post_names_for_a_title[$title_original])){
                    $cg_post_names_for_a_title[$title_original] = [];
                    $post_names = $wpdb->get_results("SELECT post_name, id FROM $table_posts WHERE post_title = '$title_original' AND post_parent = '$postParent' AND post_type = '$post_type' AND ID != $postId");
                    foreach ($post_names as $post_name){
                        $cg_post_names_for_a_title[$title_original][$post_name->id] = $post_name->post_name;
                    }
                }

                // $cg_post_names_for_a_title_numbers result might be [0,2,3,5,6...]
                if(count($cg_post_names_for_a_title_numbers[$title_original])===0){
                    foreach ($cg_post_names_for_a_title[$title_original] as $id => $post_name){
                        if($post_name==$title_original){
                            $cg_post_names_for_a_title_numbers[$title_original][] = 0;
                        }else{
                            if(strpos($post_name,'-')!==false){// this condition just for understanding should be not needed
                                $number = substr($post_name,(strrpos($post_name,'-')+1),strlen($post_name));
                                $cg_post_names_for_a_title_numbers[$title_original][] = $number;
                            }
                        }
                    }
                }

                //['aaa','aaa-2','aaa-3']
                //['aaa-2','aaa-3']
                // $cg_post_names_for_a_title_numbers[$title_original] result might be [0,2,3,5,6...]
                $modifier = 0;
                $isTitleModifiedSet = false;
                foreach ($cg_post_names_for_a_title[$title_original]  as $id => $post_name){
                    if($id==$postId){// then already set, then must be also in array $cg_post_names_for_a_title_numbers[$title_original]
                        $title_modified = $post_name;
                        $isTitleModifiedSet = true;
                    }else{
                        if(array_search($modifier,$cg_post_names_for_a_title_numbers[$title_original])===false){
                            if($modifier==0){
                                $title_modified = $title_original;
                                $isTitleModifiedSet = true;
                                $cg_post_names_for_a_title_numbers[$title_original][] = $modifier;
                                $cg_post_names_for_a_title[$title_original][$postId] = $title_modified;
                            }else{
                                $title_modified = $title_original.'-'.$modifier;
                                $isTitleModifiedSet = true;
                                $cg_post_names_for_a_title_numbers[$title_original][] = $modifier;
                                $cg_post_names_for_a_title[$title_original][$postId] = $title_modified;
                            }
                        }
                    }
                    if($modifier==0){
                        $modifier = 2;
                    }else{
                        $modifier++;
                    }
                }

                if(!$isTitleModifiedSet){
                    $title_modified = $title_original.'-'.$modifier;
                    $cg_post_names_for_a_title_numbers[$title_original][] = $modifier;
                    $cg_post_names_for_a_title[$title_original][$postId] = $title_modified;
                }

            }else{
                $title_modified = $title_original;
            }
        }

        $guid = get_the_guid($postId);
        $guid = substr($guid,0,-1);// because of "/" at the end
        $guid = substr($guid,0,-(strlen($guid)-strrpos($guid,'/')));
        $guid = $guid.'/'.$title_modified;

        $post_update = array(
            'ID'         => $postId,
            'post_title' => $title_original, // original has to be set, so count always correct above!!!
            'post_name' => $title_modified, // has to be updated here!!! so wordpress reset the permalink immediately!
            // also post_name has to be updated below for correct count
            //'guid' => $guid // original has to be set, so count always correct above!!!
        );
        wp_update_post( $post_update );
        //wp_cache_flush();// works but reset the whole cache completely

        $wpdb->update(
            "$table_posts",
            array('post_name' => $title_modified,'guid' => $guid),// $title_modified has be set immediately here so it is possible to  count correctly if same title ... cause wp_update_post above don't update immediately...
            // updating post_name above and here combination works
            // post_title can remain as original... it is easier to add count to post title if original
            array('id' => $postId),
            array('%s','%s'),
            array('%d')
        );

        //$wpdb->show_errors();
        //$wpdb->print_error();

        return $title_modified;

    }
}

if(!function_exists('cg_remove_emoji')){
    function cg_remove_emoji($string)
    {
        // Match Enclosed Alphanumeric Supplement
        $regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
        $clear_string = preg_replace($regex_alphanumeric, '', $string);

        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);

        // Match Emoticons
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $clear_string);

        // Match Transport And Map Symbols
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);

        // Match Supplemental Symbols and Pictographs
        $regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
        $clear_string = preg_replace($regex_supplemental, '', $clear_string);

        // Match Miscellaneous Symbols
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);

        // Match Dingbats
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        return $clear_string;
    }
}

if(!function_exists('cg_pre_process_name_for_url_name')){
    function cg_pre_process_name_for_url_name($name){
        $array = ['@','.',',',';','(',')','{','}','[',']','!','?','§','$','%','&','*','/','\\',' ','<','>','|','^','"','\'','#','+','´','~','~',' ','\'','"','^','´','`','’'];
        // name example
        // $name = 'e/$%//\\\'"$%&/-.,;rr_rff.lala';
        $name = str_replace($array,'-',sanitize_text_field($name));
        $name = preg_replace('/-+/', '-', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = cg_remove_emoji($name);
        return strtolower($name);// mysql statements are caseinsensitive by default
    }
}

if(!function_exists('cg_check_first_char_for_url_name_after_pre_processing')){
    function cg_check_first_char_for_url_name_after_pre_processing($urlName){
        if(substr($urlName,0,1)==='-' || substr($urlName,0,1)==='_'){
            $urlName = substr($urlName,1,strlen($urlName));
            return cg_check_first_char_for_url_name_after_pre_processing($urlName);
        }else{
            return $urlName;
        }
    }
}

if(!function_exists('cg_check_last_char_for_url_name_after_pre_processing')){
    function cg_check_last_char_for_url_name_after_pre_processing($urlName){
        if(substr($urlName,-1)==='-' || substr($urlName,-1)==='_'){
            $urlName = substr($urlName,0,-1);
            return cg_check_last_char_for_url_name_after_pre_processing($urlName);
        }else{
            return $urlName;
        }
    }
}

if(!function_exists('cg_convert_previous_translation_to_general_translations')){
    function cg_convert_previous_translation_to_general_translations($wp_upload_dir){

        $translationsFileGeneralPrevious = $wp_upload_dir['basedir'].'/contest-gallery/gallery-general/json/translations-previous.json';
        if(!file_exists($translationsFileGeneralPrevious)){
            $folders = glob($wp_upload_dir['basedir'] . '/contest-gallery/*');
            $galleryIDwithMostTranslations = 0;
            $contentCount = 0;
            foreach ($folders as $folderPath){
                if(strpos($folderPath,'/contest-gallery/gallery-id-')!==false){
                    $id  = explode('/contest-gallery/gallery-id-',$folderPath);
                    $id = $id[1];
                    $id  = explode('/',$id);
                    $id = $id[0];
                    $translationsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$id.'/json/'.$id.'-translations.json';
                    if(file_exists($translationsFile)){
                        $content = json_decode(file_get_contents($translationsFile),true);
                        $contentCountToCompare = 0;

                        foreach ($content as $key => $value){
                            if(is_string($value)){
                                if(!empty($value)){
                                    $contentCountToCompare++;
                                }
                            }else if(is_array($value)){
                                foreach ($value as $key1 => $value1){
                                    if(is_string($value1)){
                                        if(!empty($value1)){
                                            $contentCountToCompare++;
                                        }
                                    }
                                }
                            }
                        }

                        if($contentCountToCompare >= $contentCount){// is better to take from the latest gallery id if the same
                            $contentCount = $contentCountToCompare;
                            $galleryIDwithMostTranslations = $id;
                        }
                    }
                }
            }

            if(!empty($galleryIDwithMostTranslations)){
                $translationFileWithMostTranslations = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryIDwithMostTranslations.'/json/'.$galleryIDwithMostTranslations.'-translations.json';
                file_put_contents($translationsFileGeneralPrevious,file_get_contents($translationFileWithMostTranslations));
            }else{
                file_put_contents($translationsFileGeneralPrevious,json_encode([]));
            }

        }

    }
}

if(!function_exists('cg_remove_all_line_breaks')){
    function cg_remove_all_line_breaks($string)
    {
        // $string can not be taken!!! $stringNew requires to be taken!!!
        $stringNew = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $string);
        return str_replace(PHP_EOL, '', $stringNew);
    }
}

if(!function_exists('cg_check_nonce')){
    function cg_check_nonce(){
        $cg_nonce = '';
        if(isset($_POST['cg_nonce'])){
            $cg_nonce = $_POST['cg_nonce'];
        }else if(isset($_GET['cg_nonce'])){
            $cg_nonce = $_GET['cg_nonce'];
        }
        if(empty($cg_nonce) || !wp_verify_nonce($cg_nonce, 'cg_nonce')){
            echo '###cg_version###'.cg_get_version().'###cg_version###';
            echo '###cg_nonce_invalid###';
            die;
        }
    }
}

if(!function_exists('cg_create_nonce')){
    function cg_create_nonce(){
        $nonce = wp_create_nonce('cg_nonce');
        echo "<input type='hidden' id='cg_nonce' value='$nonce' class='cg_do_not_remove_when_ajax_load cg_do_not_remove_when_main_empty' />";
    }
}

if(!function_exists('cg_create_version_input')){
    function cg_create_version_input(){
        echo "<input type='hidden' id='cgGetVersionForUrlJs' value='".cg_get_version()."'  class='cg_do_not_remove_when_ajax_load cg_do_not_remove_when_main_empty' />";
    }
}

/* creates a compressed zip file */
if(!function_exists('cg_action_create_zip')){
    function cg_action_create_zip($files = array(),$destination = '',$overwrite = false) {
        if(!class_exists('ZipArchive')){
            echo "The Zip extension for php is not installed on your server. Please contact your server provider in order to install you the Zip extension for php.";
            return false;
        }

        //if the zip file already exists and overwrite is false, return false
        if(file_exists($destination) && !$overwrite) {
            return false; }
        //vars
        $valid_files = array();

        //if files were passed in...
        if(is_array($files)) {
            //cycle through each file
            foreach($files as $file) {
                //make sure the file exists
                if(file_exists($file)) {
                    $valid_files[] = $file;
                }
            }
        }

        //if we have good files...
        if(count($valid_files)) {
            //create the archive
            $zip = new ZipArchive();
            if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                echo "zip could not be created";
                return false;
            }
            //add the files
            foreach($valid_files as $file) {
                if (file_exists($file) && is_file($file)){
                    $zip->addFile($file, basename($file));
                }
            }
            //debug
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
            //close the zip -- done!
            $zip->close();

            //check to make sure the file exists
            return file_exists($destination);
        }
        else
        {
            return false;
        }
    }

}

if(!function_exists('cg_copy_table_row')){
    function cg_copy_table_row($tableNameStringPart,$oldID, $valueCollect = [], $cgCopyType = '', $columns = null){

        //
        //$nextGalleryID=0,$cgCopyType='',$newPid = 0
        //$f_input_id

        global $wpdb;
        $tableName = $wpdb->prefix . $tableNameStringPart;

        // if cg_copy_table_row in a loop then does not need to get $columns again and again
        if(empty($columns)){
            $columns = $wpdb->get_results( "SHOW COLUMNS FROM $tableName" );
        }

        $columnsString = 'NULL';

        $ratingFieldsArray = ['CountC','CountR','CountS','Rating','addCountS','addCountR1','addCountR2','addCountR3','addCountR4','addCountR5','addCountR6','addCountR7','addCountR8','addCountR9','addCountR10','CountR1','CountR2','CountR3','CountR4','CountR5','CountR6','CountR7','CountR8','CountR9','CountR10'
        ];

        foreach ($columns as $rowObject){
            if($rowObject->Field=='id'){continue;}// should be always the first one
            if($cgCopyType=='cg_copy_type_options_and_images'  && $tableNameStringPart=='contest_gal1ery' && in_array($rowObject->Field,$ratingFieldsArray)){// all rating has to be set on 0
                // has to be simply in quotes then ""
                $columnsString .= ', "0"';
            } else if(!empty($valueCollect[$tableNameStringPart]) && !empty($valueCollect[$tableNameStringPart][$rowObject->Field])){
                // has to be simply in quotes then ""#
                if(is_serialized($valueCollect[$tableNameStringPart][$rowObject->Field])){
                    $columnsString .= ', \''.$valueCollect[$tableNameStringPart][$rowObject->Field].'\'';
                }else{
                    $columnsString .= ', "'.$valueCollect[$tableNameStringPart][$rowObject->Field].'"';
                }
            }else{
                $columnsString .= ', '.$rowObject->Field;
            }
        }

        if($tableNameStringPart=='contest_gal1ery_options'){
            $query = "INSERT INTO $tableName 
    SELECT $columnsString 
    FROM $tableName 
    WHERE id = $oldID";
            $wpdb->query($query);
            $nextId = $wpdb->insert_id;
            return $nextId;
        }else if(
            $tableNameStringPart=='contest_gal1ery' ||
            $tableNameStringPart=='contest_gal1ery_f_input' ||
            $tableNameStringPart=='contest_gal1ery_f_output' ||
            $tableNameStringPart=='contest_gal1ery_categories' ||
            $tableNameStringPart=='contest_gal1ery_comments_notification_options' ||
            $tableNameStringPart=='contest_gal1ery_entries'
        ){

            $query = "INSERT INTO $tableName 
    SELECT $columnsString 
    FROM $tableName
    WHERE id = $oldID";

            /*if($tableNameStringPart=='contest_gal1ery_f_input'){
                var_dump($tableName);
                echo "<br>";
                var_dump($oldID);
                echo "<br>";
                var_dump($columnsString);
                echo "<br>";
                var_dump($query);
                echo "<br>";
            }*/

            $wpdb->query($query);
            $nextId = $wpdb->insert_id;


            return $nextId;
        }else{
            $query = "INSERT INTO $tableName 
    SELECT $columnsString 
    FROM $tableName
    WHERE GalleryID = $oldID";
            $wpdb->query($query);
            $nextId = $wpdb->insert_id;
            return $nextId;
        }
    }
}

if(!function_exists('cg_check_headers_sent')){
    function cg_check_headers_sent(){
        if(headers_sent()){
            ?>
            <script data-cg-processing="true">
                cgJsClass.gallery.function.message.show(undefined,'Some other plugin have already sent headers. Login not possible.');
            </script>
            <?php
            return true;
        }else{
            return false;
        }
    }
}

if(!function_exists('cg_check_if_database_tables_ok')){
    function cg_check_if_database_tables_ok(){

        global $wpdb;
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
        $tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";

        if(
            ($wpdb->get_var("SHOW TABLES LIKE '$tablename'") != $tablename) ||
            ($wpdb->get_var("SHOW TABLES LIKE '$tablenameOptions'") != $tablenameOptions) ||
            ($wpdb->get_var("SHOW TABLES LIKE '$tablename_pro_options'") != $tablename_pro_options)
        )
        {
            if (is_multisite()) {
                $i=get_current_blog_id();
                if($i==1){
                    $i="";
                    $lastError = contest_gal1ery_create_table($i,true);
                }else {
                    $lastError = contest_gal1ery_create_table($i."_",true);
                }
            }else{
                $i='';
                $lastError = contest_gal1ery_create_table($i,true);
            }
            if(!empty($lastError)){
                // message normal and message PRO here!
                $plugin_dir_path = plugin_dir_path(__FILE__);
                echo "<b>There were errors when trying to create database tables.<br>";
                echo "If the errors are not understandable and the reason unclear<br>";
                echo "please contact following email, copy paste and send the errors information above to:</b><br>";
                if(cg_get_version()=='contest-gallery-pro'){
                    echo '<a href="mailto:support-pro@contest-gallery.com">support-pro@contest-gallery.com</a>';
                }else{
                    echo '<a href="mailto:support@contest-gallery.com">support@contest-gallery.com</a>';
                }
                echo "<br><br><br><br><br><br><br><br><br>";
                die;
            }
        }
    }
}

if(!function_exists('cg_check_if_upload_folder_permissions_ok')){
    function cg_check_if_upload_folder_permissions_ok(){

        $wp_upload_dir = wp_upload_dir();
        $isWritable = true;
        if(!is_writable($wp_upload_dir['basedir'])){
            $isWritable = false;
        }
        if($isWritable){// test file creation
            $cgTestFileName = 'contest-gal1ery-test-file.txt';
            try {
                file_put_contents($wp_upload_dir['basedir'].'/'.$cgTestFileName,'test');
            }catch (Exception $e){
                // no exception printing so far required
            }
            if(file_exists($wp_upload_dir['basedir'].'/'.$cgTestFileName)){
                unlink($wp_upload_dir['basedir'].'/'.$cgTestFileName);
            }else{
                $isWritable = false;
            }
        }
        if(!$isWritable){
            echo "<b>No files and folders can be created created in your main upload folder:</b><br>";
            echo $wp_upload_dir['basedir'];
            echo "<br><b>Recommended WordPress permissions for a wp-content/uploads folder are: 755</b><br>";
            die;
        }
    }
}

if(!function_exists('cg_echo_last_sql_error')){
    function cg_echo_last_sql_error($isShowError,& $lastError = ''){
        if($isShowError){
            global $wpdb;
            $wpdb->show_errors(true);
            $wpdb->suppress_errors(false);//<<< set for sure, but somehow always surpressed by WordPress
            if($wpdb->last_error!=$lastError){
                $lastError = $wpdb->last_error;
                echo "<div>";
                echo "<br><b>Query:</b><br> ";
                echo $wpdb->last_query;
                echo "<br><b>Error:</b><br> ";
                echo $lastError;// error somehow always surpressed by WordPress
                echo "<br><br>";
                echo "</div>";
                return $lastError;
            };
        }
        return $lastError;
    }
}

if(!function_exists('cg_get_time_based_on_wp_timezone_conf')){
    function cg_get_time_based_on_wp_timezone_conf($unixtstmp,$format){// unixtime always gmt+0/utc+0

        $dt = DateTime::createFromFormat('U', $unixtstmp);// this always creates gmt+0/utc+0
        $minutes = get_option('gmt_offset')*60;
        $dt->modify("$minutes minutes");
        return $dt->format($format);

    }
}

if(!function_exists('cg_get_date_time_object_based_on_wp_timezone_conf')){
    function cg_get_date_time_object_based_on_wp_timezone_conf($unixtstmp){// unixtime always gmt+0/utc+0
        $dt = DateTime::createFromFormat('U', $unixtstmp);// this always creates gmt+0/utc+0
        $minutes = get_option('gmt_offset')*60;
        $dt->modify("$minutes minutes");
        return $dt;
    }
}

if(!function_exists('cg_set_json_data_of_row_objects')){
    function cg_set_json_data_of_row_objects($picsSQL,$galeryID,$wp_upload_dir,$thumbSizesWp,$ExifDataByRealIds = []){

        $imagesArray = [];

        foreach($picsSQL as $object){

            if(empty($imagesArray)){
                $imagesArray = cg_create_json_files_when_activating($galeryID,$object,$thumbSizesWp,$wp_upload_dir,null,0,[],$ExifDataByRealIds);
                /*                        echo "<pre>";
                                        print_r($imagesArray);
                                        echo "</pre>";*/
            }else{
                /*                        echo "<pre>";
                                        print_r($imagesArray);
                                        echo "</pre>";*/
                $imagesArray = cg_create_json_files_when_activating($galeryID,$object,$thumbSizesWp,$wp_upload_dir,$imagesArray,0,[],$ExifDataByRealIds);
                /*                        echo "<pre>";
                                        print_r($imagesArray);
                                        echo "</pre>";*/
            }

            if(!is_dir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/frontend-added-or-removed-images')){
                mkdir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/frontend-added-or-removed-images',0755);
            }

            // simply create empty file for later check
            $jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/frontend-added-or-removed-images/'.$object->id.'.txt';
            $fp = fopen($jsonFile, 'w');
            fwrite($fp, '');
            fclose($fp);

        }

        cg_create_no_script_html($imagesArray,$galeryID);

        //   die;

        return $imagesArray;

    }
}

if(!function_exists('cg_set_cookie')){
    function cg_set_cookie($galeryID,$type){
        $cookieValue = md5(uniqid('cg',true)).time();
        setcookie('contest-gal1ery-'.$galeryID.'-'.$type, $cookieValue, time() + (20 * 365 * 24 * 60 * 60), "/");
        return $cookieValue;
    }
}

if(!function_exists('cg_create_contest_gallery_plugin_tag')){
    function cg_create_contest_gallery_plugin_tag(){

        $tag = wp_insert_term(
            'Contest Gallery Plugin Tag', // the term
            'post_tag', // the taxonomy
            array(
                'description'=> 'Do not remove. Will be recreated if required as long Contest Gallery plugin is activated. Tag for categorizing Contest Gallery Plugin entry pages. Helps to sort out entry pages in backend "Pages" area.  Will be removed when Contest Gallery plugin will be deleted.',
                'slug' => 'contest-gallery-plugin-tag'
            )
        );

        //   wp_set_post_tags()

        return $tag;
    }
}

if(!function_exists('cg_check_if_development')){
    function cg_check_if_development(){
        return false;
        if(is_dir(plugin_dir_path( __FILE__  ).'../../../contest-gallery-js-and-css')){
            return true;
        }else{
            return false;
        }

    }
}

if(!function_exists('cg_get_blockquote_from_post_content')){
    function cg_get_blockquote_from_post_content($post_content){
        $blockquote = substr($post_content,(strpos($post_content,'blockquote:')+strlen('blockquote:')),strlen($post_content));
        $blockquote = substr($blockquote,0, -1);
        return contest_gal1ery_convert_for_html_output_without_nl2br($blockquote);
    }
}

if(!function_exists('cg_get_gallery_slug_name')){
    function cg_get_gallery_slug_name(){
        if (is_multisite()) {
            $CgEntriesOwnSlugName = cg_get_blog_option( get_current_blog_id(),'CgEntriesOwnSlugName');
        }else{
            $CgEntriesOwnSlugName = get_option('CgEntriesOwnSlugName');
        }
        if(!empty($CgEntriesOwnSlugName)){
            return $CgEntriesOwnSlugName;
        }else{
            return 'contest-gallery';
        }
    }
}

// was created for v24 but will be not used because changing of slug name is not possible since v24
if(!function_exists('cg_get_galleries_slug_name')){
    function cg_get_galleries_slug_name($shortcode_name = '',$post_mime_type = ''){

        $gType = '';
        $gTypeLowerCase = '';
        if($shortcode_name=='cg_gallery_user' || $post_mime_type == 'contest-gallery-plugin-page-galleries-user-slug'){
            $gType = 'User';
            $gTypeLowerCase = '-user';
        }else if($shortcode_name=='cg_gallery_winner' || $post_mime_type == 'contest-gallery-plugin-page-galleries-winner-slug'){
            $gType = 'Winner';
            $gTypeLowerCase = '-winner';
        }else if($shortcode_name=='cg_gallery_no_voting' || $post_mime_type == 'contest-gallery-plugin-page-galleries-no-voting-slug'){
            $gType = 'NoVoting';
            $gTypeLowerCase = '-no-voting';
        }else if($shortcode_name=='cg_gallery_ecommerce' || $post_mime_type == 'contest-gallery-plugin-page-galleries-ecommerce-slug'){
            $gType = 'Ecommerce';
            $gTypeLowerCase = '-ecommerce';
        }

        if (is_multisite()) {
            $CgEntriesOwnSlugNameGalleries = cg_get_blog_option( get_current_blog_id(),'CgEntriesOwnSlugNameGalleries'.$gType);
        }else{
            $CgEntriesOwnSlugNameGalleries = get_option('CgEntriesOwnSlugNameGalleries'.$gType);
        }

        $CgEntriesOwnSlugNameGalleries = (!empty($CgEntriesOwnSlugNameGalleries)) ? $CgEntriesOwnSlugNameGalleries : 'contest-galleries'.$gTypeLowerCase;

        return $CgEntriesOwnSlugNameGalleries;
    }
}

if(!function_exists('cg_galleries_options')){
    function cg_galleries_options($wp_upload_dir,$shortcode_name = '',$post_mime_type = ''){
        $gType = 'g';
        if($shortcode_name=='cg_gallery_user' || $post_mime_type == 'contest-gallery-plugin-page-galleries-user-slug'){
            $gType = 'u';
        }else if($shortcode_name=='cg_gallery_winner' || $post_mime_type == 'contest-gallery-plugin-page-galleries-winner-slug'){
            $gType = 'w';
        }else if($shortcode_name=='cg_gallery_no_voting' || $post_mime_type == 'contest-gallery-plugin-page-galleries-no-voting-slug'){
            $gType = 'nv';
        }else if($shortcode_name=='cg_gallery_ecommerce' || $post_mime_type == 'contest-gallery-plugin-page-galleries-ecommerce-slug'){
            $gType = 'ec';
        }

        $galleriesOptions = cg_get_galleries_options_all();

        $galleriesOptions = $galleriesOptions[$gType];

        return $galleriesOptions;
    }
}

if(!function_exists('cg_get_galleries_options_all')){
    function cg_get_galleries_options_all(){

        $uploadFolder = wp_upload_dir();

        // get cg_galleries options here
        $galleriesOptionsPath = $uploadFolder['basedir'].'/contest-gallery/gallery-general/json/galleries-options.json';
        if(file_exists($galleriesOptionsPath)){
            $galleriesOptions = json_decode(file_get_contents($galleriesOptionsPath),true);
            $galleriesOptionsForEcommerce = cg_get_24_version_values();
            // because might be saved in older gallery created in version 21 before cg_ecommerce shortcode
            if(!isset($galleriesOptions['ec']['PicsPerSite'])){
                $galleriesOptions['ec']['PicsPerSite'] = $galleriesOptionsForEcommerce['ec']['PicsPerSite'];
            }
            if(!isset($galleriesOptions['ec']['WidthThumb'])){
                $galleriesOptions['ec']['WidthThumb'] = $galleriesOptionsForEcommerce['ec']['WidthThumb'];
            }
            if(!isset($galleriesOptions['ec']['HeightThumb'])){
                $galleriesOptions['ec']['HeightThumb'] = $galleriesOptionsForEcommerce['ec']['HeightThumb'];
            }
            if(!isset($galleriesOptions['ec']['DistancePics'])){
                $galleriesOptions['ec']['DistancePics'] = $galleriesOptionsForEcommerce['ec']['DistancePics'];
            }
            if(!isset($galleriesOptions['ec']['DistancePicsV'])){
                $galleriesOptions['ec']['DistancePicsV'] = $galleriesOptionsForEcommerce['ec']['DistancePicsV'];
            }
			if(!isset($galleriesOptions['ec']['PreviewLastAdded'])){
				$galleriesOptions['ec']['PreviewLastAdded'] = $galleriesOptionsForEcommerce['ec']['PreviewLastAdded'];
			}
			if(!isset($galleriesOptions['ec']['BorderRadius'])){
				$galleriesOptions['ec']['BorderRadius'] = $galleriesOptionsForEcommerce['ec']['BorderRadius'];
			}
			if(!isset($galleriesOptions['ec']['FeControlsStyle'])){
				$galleriesOptions['ec']['FeControlsStyle'] = $galleriesOptionsForEcommerce['ec']['FeControlsStyle'];
			}
            if($galleriesOptions['ec']['PreviewLastAdded']=='0' && $galleriesOptions['ec']['PreviewHighestRated']=='0' && $galleriesOptions['ec']['PreviewMostCommented']=='0'){// do not remove this condition, this case might happen using saving in galleries created before 22 (cg_galleries_ecommerce) shortcode
	            $galleriesOptions['ec']['PreviewLastAdded']=1;
            }
        }else{
            $galleriesOptions = cg_get_24_version_values();
        }

        return $galleriesOptions;

    }
}

?>