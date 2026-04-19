<?php

$cgCopyBatchSize = 50;
$cg_copy_start = absint($_POST['cg_copy_start']);
$cg_processed_images = $cg_copy_start + $cgCopyBatchSize;

// otherwise is already defined
if(!empty($_POST['option_id_next_gallery'])){
    $nextIDgallery = absint($_POST['option_id_next_gallery']);
}

$galleryUpload = $uploadFolder['basedir'] . '/contest-gallery/gallery-id-' . $nextIDgallery . '';
$galleryJsonFolder = $uploadFolder['basedir'] . '/contest-gallery/gallery-id-' . $nextIDgallery . '/json';
$galleryJsonImagesFolder = $uploadFolder['basedir'] . '/contest-gallery/gallery-id-' . $nextIDgallery . '/json/image-data';
$galleryJsonInfoDir = $uploadFolder['basedir'] . '/contest-gallery/gallery-id-' . $nextIDgallery . '/json/image-info';
$galleryJsonCommentsDir = $uploadFolder['basedir'] . '/contest-gallery/gallery-id-' . $nextIDgallery . '/json/image-comments';

$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
$options = $wpdb->get_row( "SELECT * FROM $tablenameOptions WHERE id = '$nextIDgallery'" );

$imagesToProcess = $wpdb->get_var( $wpdb->prepare(
	"
						SELECT COUNT(*) AS NumberOfRows
						FROM $tablename WHERE EcommerceEntry = 0 AND GalleryID = %d",
	$idToCopy
));

// var_dump('$idToCopy');
// var_dump($idToCopy);
//  var_dump($cg_copy_start);

if($cg_processed_images<$imagesToProcess){

    $processPercent = round($cg_processed_images/$imagesToProcess*100);
    echo "<h2>In progress $processPercent%...</h2>";
    echo "<p><strong>Do not cancel</strong></p>";

}else{

    if($cg_copy_start > 0 && $cg_processed_images >= $imagesToProcess){
        echo "<h2 class='cg_in_process'>In progress 99%...</h2>";
        echo "<p class='cg_in_process'><strong>Do not cancel</strong></p>";
    }else{
        echo "<h2 class='cg_in_process'>In progress ...</h2>";
        echo "<p class='cg_in_process'><strong>Do not cancel</strong></p>";
    }

}

// Important, order by ID asc!!!! last pictures first, then ids gets descending!
$galleryToCopy = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename WHERE GalleryID = %d AND EcommerceEntry = 0 ORDER BY id ASC LIMIT %d, %d",array($idToCopy,$cg_copy_start,$cgCopyBatchSize)));
// var_dump($galleryToCopy);
//die;

$wpUploadIdsForPostTitle = array();
$oldImageIdsForCopy = array();
foreach ($galleryToCopy as $rowObject){
    $WpUpload = absint($rowObject->WpUpload);
    if(!empty($WpUpload)){
        $wpUploadIdsForPostTitle[$WpUpload] = $WpUpload;
    }
    $oldImageIdForCopy = absint($rowObject->id);
    if(!empty($oldImageIdForCopy)){
        $oldImageIdsForCopy[$oldImageIdForCopy] = $oldImageIdForCopy;
    }
}
$wpUploadIdsForPostTitle = array_values($wpUploadIdsForPostTitle);
$oldImageIdsForCopy = array_values($oldImageIdsForCopy);


$post_titles_array = [];
$WpPostTitles = [];
if(!empty($wpUploadIdsForPostTitle)){
    $wpUploadIdsPlaceholders = implode(',', array_fill(0, count($wpUploadIdsForPostTitle), '%d'));
	$WpPostTitles = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT ID, post_title FROM $table_posts WHERE ID IN ($wpUploadIdsPlaceholders)",$wpUploadIdsForPostTitle));
}

foreach ($WpPostTitles as $WpPostTitle){
    $post_titles_array[$WpPostTitle->ID] = $WpPostTitle->post_title;
}

$IsForWpPageTitleInputId = $wpdb->get_var($wpdb->prepare("SELECT id FROM $tablename_form_input WHERE GalleryID = %d AND IsForWpPageTitle=1",[$idToCopy]));
$wpPageTitlesByOldPid = array();
if(!empty($IsForWpPageTitleInputId) && !empty($oldImageIdsForCopy)){
    $oldImageIdsPlaceholders = implode(',', array_fill(0, count($oldImageIdsForCopy), '%d'));
    $wpPageTitleRows = $wpdb->get_results($wpdb->prepare("SELECT pid, Short_Text FROM $tablename_entries WHERE GalleryID = %d AND f_input_id = %d AND pid IN ($oldImageIdsPlaceholders)",array_merge(array($idToCopy,$IsForWpPageTitleInputId),$oldImageIdsForCopy)));
    foreach($wpPageTitleRows as $wpPageTitleRow){
        if(!empty($wpPageTitleRow->Short_Text)){
            $wpPageTitlesByOldPid[$wpPageTitleRow->pid] = $wpPageTitleRow->Short_Text;
        }
    }
}

// get $collectInputIdsArray
$fp = fopen($galleryUpload . '/json/' . $nextIDgallery . '-collect-cat-ids-array.json', 'r');
$collectCatIdsArray =json_decode(fread($fp,filesize($galleryUpload . '/json/' . $nextIDgallery . '-collect-cat-ids-array.json')),true);
fclose($fp);

if($cgVersion<7 && !empty($_POST['copy_v7'])){
    // gallerie bilder in offizielle wordpress library platzieren
    $galleryToCopy = cg_copy_pre7_gallery_images($galleryToCopy);
}

$valueCollect = array();
$collectImageIdsArray = array();
$collectActiveImageIdsArray = array();
$imageRatingArray = array();
$imagesDataArray = array();

$Version = cg_get_version_for_scripts();

$tableNameForColumns = $wpdb->prefix . 'contest_gal1ery';
$columns = $wpdb->get_results( "SHOW COLUMNS FROM $tableNameForColumns" );
foreach($galleryToCopy as $key => $rowObject){

    $imageRatingArray = array();

    $WpUpload = $rowObject->WpUpload;
    $Active = $rowObject->Active;
    $lastImageIdOld = $rowObject->id;

    $prevId = 0;
    $WpUpload = 0;
    foreach($rowObject as $key1 => $value1){

        if ($key1 == 'id') {
            $prevId = $value1;
            $value1 = '';
        }
        if ($key1 == 'rowid') {
            $value1 = 0;
        }
        if ($key1 == 'GalleryID') {
            $value1 = $nextIDgallery;
        }
        if ($key1 == 'WpUpload') {
            $WpUpload = $value1;
        }

        // if only options and images then set to 0
        if($cgCopyType=='cg_copy_type_options_and_images'){
            if ($key1 == 'CountC') {
                $value1 = 0;
            }
            if ($key1 == 'CountR') {
                $value1 = 0;
            }
            if ($key1 == 'CountS') {
                $value1 = 0;
            }
            if ($key1 == 'Rating') {
                $value1 = 0;
            }
        }


        if ($key1 == 'Category') {
            if(empty($collectCatIdsArray[$value1])){
                $value1 = 0;
            }else{
                $value1 = $collectCatIdsArray[$value1];
            }
        }

        if ($key1 == 'Version') {
            if(empty($value1)){// put in the current version then
                $value1 = $Version;
            }
        }
        $valueCollect[$key1] = $value1;
    }

    $nextId = cg_copy_table_row('contest_gal1ery',$rowObject->id,['contest_gal1ery' => $valueCollect],$cgCopyType,$columns);

    $WpPageTitle = '';
    if(!empty($IsForWpPageTitleInputId) && isset($wpPageTitlesByOldPid[$prevId])){
        $WpPageTitle = $wpPageTitlesByOldPid[$prevId];
    }

    if(!empty($WpPageTitle)){
        $post_title = $WpPageTitle;
    }else{
        $post_title = '';
        if(isset($post_titles_array[$WpUpload])){
            $post_title = $post_titles_array[$WpUpload];
        }
    }

    // cg_gallery shortcode
    $array = [
        'post_title'=> $post_title,
        'post_type'=>'contest-g',
        'post_content'=>"<!-- wp:shortcode -->"."\r\n".
            "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
            "[cg_gallery id=\"$nextIDgallery\" entry_id=\"$nextId\"]"."\r\n".
            "<!-- /wp:shortcode -->",
        'post_mime_type'=>'contest-gallery-plugin-page',
        'post_status'=>'publish',
        'post_parent'=>$options->WpPageParent
    ];

    $WpPage = wp_insert_post($array);

    // cg_gallery_user shortcode
    $array = [
        'post_title'=> $post_title,
        'post_type'=>'contest-g-user',
        'post_content'=>"<!-- wp:shortcode -->"."\r\n".
            "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
            "[cg_gallery_user id=\"$nextIDgallery\" entry_id=\"$nextId\"]"."\r\n".
            "<!-- /wp:shortcode -->",
        'post_mime_type'=>'contest-gallery-plugin-page',
        'post_status'=>'publish',
        'post_parent'=>$options->WpPageParentUser
    ];

    $WpPageUser = wp_insert_post($array);

    // cg_gallery_no_voting shortcode
    $array = [
        'post_title'=> $post_title,
        'post_type'=>'contest-g-no-voting',
        'post_content'=>"<!-- wp:shortcode -->"."\r\n".
            "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
            "[cg_gallery_no_voting id=\"$nextIDgallery\" entry_id=\"$nextId\"]"."\r\n".
            "<!-- /wp:shortcode -->",
        'post_mime_type'=>'contest-gallery-plugin-page',
        'post_status'=>'publish',
        'post_parent'=>$options->WpPageParentNoVoting
    ];

    $WpPageNoVoting = wp_insert_post($array);

    // cg_gallery_winner shortcode
    $array = [
        'post_title'=> $post_title,
        'post_type'=>'contest-g-winner',
        'post_content'=>"<!-- wp:shortcode -->"."\r\n".
            "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
            "[cg_gallery_winner id=\"$nextIDgallery\" entry_id=\"$nextId\"]"."\r\n".
            "<!-- /wp:shortcode -->",
        'post_mime_type'=>'contest-gallery-plugin-page',
        'post_status'=>'publish',
        'post_parent'=>$options->WpPageParentWinner
    ];

	$WpPageWinner = wp_insert_post($array);

	// cg_gallery_ecommerce shortcode
    $array = [
        'post_title'=> $post_title,
        'post_type'=>'contest-g-ecommerce',
        'post_content'=>"<!-- wp:shortcode -->"."\r\n".
            "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
            "[cg_gallery_ecommerce id=\"$nextIDgallery\" entry_id=\"$nextId\"]"."\r\n".
            "<!-- /wp:shortcode -->",
        'post_mime_type'=>'contest-gallery-plugin-page',
        'post_status'=>'publish',
        'post_parent'=>$options->WpPageParentEcommerce
    ];

	$WpPageEcommerce = wp_insert_post($array);

    $wpdb->update(
        "$tablename",
        array('WpPage' => $WpPage,'WpPageUser' => $WpPageUser,'WpPageNoVoting' => $WpPageNoVoting,'WpPageWinner' => $WpPageWinner,'WpPageEcommerce' => $WpPageEcommerce),
        array('id' => $nextId),
        array('%d','%d','%d','%d','%d'),
        array('%d')
    );

    $copiedRowObject = $wpdb->get_row($wpdb->prepare(
        "
            SELECT $table_posts.*, $tablename.*
            FROM $tablename
            LEFT JOIN $table_posts ON $tablename.WpUpload = $table_posts.ID
            WHERE $tablename.id = %d
            LIMIT 1
        ",
        [$nextId]
    ));

    if(empty($copiedRowObject)){
        $copiedRowObject = $wpdb->get_row($wpdb->prepare(
            "
                SELECT *
                FROM $tablename
                WHERE id = %d
                LIMIT 1
            ",
            [$nextId]
        ));
    }

    if(!empty($copiedRowObject)){
        if(!isset($copiedRowObject->post_date)){$copiedRowObject->post_date = '';}
        if(!isset($copiedRowObject->post_content)){$copiedRowObject->post_content = '';}
        if(!isset($copiedRowObject->post_title)){$copiedRowObject->post_title = '';}
        if(!isset($copiedRowObject->post_name)){$copiedRowObject->post_name = '';}
        if(!isset($copiedRowObject->post_excerpt)){$copiedRowObject->post_excerpt = '';}
    }

    if($Active==1){
        $imagesDataArray = cg_create_json_files_when_activating($nextIDgallery,$copiedRowObject,$thumbSizesWp,$uploadFolder,$imagesDataArray,0,array(),array(),true);

        $collectImageIdsArray[$lastImageIdOld] = $nextId;
        $collectActiveImageIdsArray[$lastImageIdOld] = $nextId;

    }else{
        $collectImageIdsArray[$lastImageIdOld] = $nextId;

    }

    $valueCollect = array();

}

//cg_set_data_in_images_files_with_all_data($nextIDgallery,$imagesDataArray);

// since 17.0.0 not used anymore, no share button anymore
//cg_create_fb_sites($idToCopy,$nextIDgallery);// IMAGE ID Will be considered in this case. Thats why it is done so!

if($cgVersion<10){

    $backToGalleryFile = $uploadFolder["basedir"]."/contest-gallery/gallery-id-$nextIDgallery/backtogalleryurl.js";
    $FbLikeGoToGalleryLink = 'backToGalleryUrl="";';
    $fp = fopen($backToGalleryFile, 'w');
    fwrite($fp, $FbLikeGoToGalleryLink);
    fclose($fp);

}else{

    $backToGalleryFile = $uploadFolder["basedir"]."/contest-gallery/gallery-id-$nextIDgallery/backtogalleryurl.js";
    $FbLikeGoToGalleryLink = 'backToGalleryUrl="'.$FbLikeGoToGalleryLink.'";';
    $fp = fopen($backToGalleryFile, 'w');
    fwrite($fp, $FbLikeGoToGalleryLink);
    fclose($fp);

}


// create user entries

// get $collectInputIdsArray
$fp = fopen($galleryUpload . '/json/' . $nextIDgallery . '-collect-input-ids-array.json', 'r');
$collectInputIdsArray =json_decode(fread($fp,filesize($galleryUpload . '/json/' . $nextIDgallery . '-collect-input-ids-array.json')),true);
fclose($fp);

// check which fileds are allowed for json save because allowed gallery or single view
$uploadFormFields = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename_form_input WHERE GalleryID = %d",[$nextIDgallery]));

$optionsVisual = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_options_visual WHERE GalleryID = %d",[$nextIDgallery]));
$Field1IdGalleryView = (!empty($optionsVisual) && isset($optionsVisual->Field1IdGalleryView)) ? absint($optionsVisual->Field1IdGalleryView) : 0;
$Field1IdFullWindowBlogView = (!empty($optionsVisual) && isset($optionsVisual->Field1IdFullWindowBlogView)) ? absint($optionsVisual->Field1IdFullWindowBlogView) : 0;

$fieldsForFrontendArray = array();
$inputTitles = array();

foreach ($uploadFormFields as $field) {
    $Field_Content = unserialize($field->Field_Content);

    $inputTitles[$field->id] = $Field_Content['titel'];

    if ($field->id == $Field1IdGalleryView or $field->id == $Field1IdFullWindowBlogView or $field->Show_Slider == 1) {
        $fieldsForFrontendArray[] = $field->id;
    }
}

if(!empty($oldImageIdsForCopy)){
    $oldImageIdsPlaceholders = implode(',', array_fill(0, count($oldImageIdsForCopy), '%d'));
    $galleryToCopy = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename_entries WHERE GalleryID = %d AND pid IN ($oldImageIdsPlaceholders) ORDER BY pid DESC",array_merge(array($idToCopy),$oldImageIdsForCopy)));
}else{
    $galleryToCopy = array();
}

$valueCollect = array();

$pidBefore = '';

/*echo "<pre>";
print_r($galleryToCopy);
echo "</pre>";*/

/*
echo "<pre>";
print_r($collectImageIdsArray);
echo "</pre>";*/

if(!empty($galleryToCopy)){

    $tableNameForColumns = $wpdb->prefix . 'contest_gal1ery_entries';
    $columns = $wpdb->get_results( "SHOW COLUMNS FROM $tableNameForColumns" );

    foreach ($galleryToCopy as $key => $rowObject) {

        if(!empty($rowObject->InputDate) AND $rowObject->InputDate!='0000-00-00 00:00:00'){
            // simply continue processing then
        } elseif ($rowObject->Short_Text == '' && $rowObject->Long_Text == '') {// to reduce amount of copy
            continue;
        }

        foreach ($rowObject as $key1 => $value1) {

            if ($key1 == 'id') {
                $value1 = '';
            }
            if ($key1 == 'GalleryID') {
                $value1 = $nextIDgallery;
            }
            if ($key1 == 'pid') {
                $value1 = $collectImageIdsArray[$value1];
            }
            if ($key1 == 'f_input_id') {
                $lastInputIdOld = $value1;
                $value1 = $collectInputIdsArray[$lastInputIdOld];
                $fInputId = $value1;
            }

            $valueCollect[$key1] = $value1;

        }

        /*$wpdb->insert(
            $tablename_entries,
            $valueCollect,
            array(
                '%s', '%d', '%d', '%d',
                '%s', '%d', '%s', '%s', '%d', '%d', '%s', '%d'// InputDate was last
            )
        ); // the last two are*/

     /*   echo "<pre>";
        print_r($collectInputIdsArray);
        echo "</pre>";

        var_dump($rowObject->pid);*/

        $nextPid = (!empty($collectImageIdsArray[$rowObject->pid])) ? $collectImageIdsArray[$rowObject->pid] : 0;
       // var_dump('$nextPid');
        //var_dump($nextPid);
        //$nextId = cg_copy_table_row('contest_gal1ery_entries',$rowObject->id,$nextIDgallery,$cgCopyType,$nextPid,$columns,$collectInputIdsArray[$lastInputIdOld]);

        $nextId = cg_copy_table_row('contest_gal1ery_entries',$rowObject->id,['contest_gal1ery_entries' => $valueCollect],'',$columns);

        if ($rowObject->pid != $pidBefore) {

            if ($pidBefore == '') {
                $pidBefore = $rowObject->pid;
                continue;
            }

        }

        $pidBefore = $rowObject->pid;

        $valueCollect = array();

    }

}


// insert entries json
//do_action('cg_json_upload_form_info_data_files',$nextIDgallery,$newIdsToCopyStringCollect);
//cg_json_upload_form_info_data_files_new($nextIDgallery,[],true);// not required since 24.0.0 ... will be inserted in correct way so or so
// has to be done twice for correct url order
//cg_json_upload_form_info_data_files_new($nextIDgallery,[],true);// not required since 24.0.0 ... will be inserted in correct way so or so
$copyImageInfoPids = array_values($collectImageIdsArray);
if(!empty($copyImageInfoPids)){
    cg_json_upload_form_info_data_files_new($nextIDgallery,$copyImageInfoPids,false,false,true,true);// simply actualize only current contest gallery image info files since 24.0.0
}

if($cgCopyType=='cg_copy_type_all'){

    // copy rating here
    cg_copy_rating($cg_copy_start,$idToCopy,$nextIDgallery,$collectImageIdsArray);

    // copy comments here
    cg_copy_comments($cg_copy_start,$idToCopy,$nextIDgallery,$collectImageIdsArray);
}

if($cg_processed_images >= $imagesToProcess){
    cg1l_migrate_image_stats_to_folder($nextIDgallery,true,false);
    cg1l_create_last_updated_time_file_all($nextIDgallery);
}

// forward
if($cg_processed_images<$imagesToProcess){

    //   ?page=".cg_get_version()."/index.php&option_id=137
    //    &edit_gallery=true&copy=true
    $cg_copy_start = $cg_processed_images;

    echo "<input type='hidden' id='cgProcessedImages' value='$cg_copy_start' />";
    echo "<input type='hidden' id='cgNextIdGallery' value='$nextIDgallery' />";


    die;


    //require("forward-url.php");

    //exit;
    //echo $Forward_URL;

}

?>
