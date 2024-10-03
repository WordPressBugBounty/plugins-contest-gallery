<?php

global $wpdb;

$cgProVersion = contest_gal1ery_key_check();
if(!$cgProVersion){
    $cgProFalse = 'cg-pro-false';
}else{
    $cgProFalse = '';
}

$_POST = cg1l_sanitize_post($_POST);

if(empty($cg_wp_upload_ids)){
	$cg_wp_upload_ids = $_POST['action1'];
}
$cg_assign_fields = [];
if(!empty($_POST['cg_assign_fields'])){$cg_assign_fields = $_POST['cg_assign_fields'];}//21.2.1-PRO
if(!empty($_POST['cg_assign_category'])){$cg_assign_category = absint($_POST['cg_assign_category']);}//21.2.1-PRO
$galeryID = absint($_POST['action2']);
$GalleryID = $galeryID;

$table_posts = $wpdb->prefix."posts";
$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
$tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
$tablenameentries = $wpdb->prefix . "contest_gal1ery_entries";
$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";

$selectSQL1 = $wpdb->get_row( "SELECT * FROM $tablenameOptions WHERE id = '$galeryID'" );
$cgVersion = $selectSQL1->Version;
$tablename = $wpdb->prefix . "contest_gal1ery";

$proOptions = $wpdb->get_row( "SELECT * FROM $tablename_pro_options WHERE GalleryID = '$galeryID'" );
$DataShare = ($proOptions->FbLikeNoShare==1) ? 'false' : 'true';
$DataClass = ($proOptions->FbLikeOnlyShare==1) ? 'fb-share-button' : 'fb-like';
$DataLayout = ($proOptions->FbLikeOnlyShare==1) ? 'button' : 'button_count';

$addedWpUploadsArray = [];
$newEntryIdsArray = [];
$addedWpUploadsContentArray = [];

//var_dump("asdfsad");die;
foreach($cg_wp_upload_ids as $key => $value){
$value = absint($value);
$wp_image_info = $wpdb->get_row("SELECT * FROM $table_posts WHERE ID = '$value'");
$image_url = $wp_image_info->guid;
$post_title = cg_pre_process_name_for_url_name($wp_image_info->post_title);
$post_name = $wp_image_info->post_name;
$post_type = $wp_image_info->post_mime_type;
$wp_image_id = $wp_image_info->ID;
$addedWpUploadsContentArray[$value] = [];
$addedWpUploadsContentArray[$value]['title'] = $wp_image_info->post_title;
$addedWpUploadsContentArray[$value]['caption'] = $wp_image_info->post_excerpt;
$addedWpUploadsContentArray[$value]['description'] = $wp_image_info->post_content;

// simply the fastest processing way, to check the string end, instead do some database request again
$fullFilePathEnd = '';
if(!empty($image_url)){
    $fullFilePathExploded = explode('.',$image_url);
    $fullFilePathEnd = end($fullFilePathExploded);
}

// Notwendig: wird in convert-several-pics so verabeitet. Darf keine Sonderzeichen enthalten! Simple example only
//$search = array(" ", "!", '"', "#", "$", "%", "&", "'", "(", ")", "*", "+", ",", "/", ":", ";", "=", "?", "@", "[","]","‘");
//$post_title = str_replace($search,"_",$post_title);
    //var_dump($post_title); die;
$NamePic = substr($post_title,0,512);

$doNotProcess=0;
$isAlternativeFile=false;
$isOnlyProVersionFile = false;

/*
    $file["type"]!='application/pdf' && $file["type"] != 'application/x-zip-compressed' &&
    $file["type"]!='text/plain' && $file["type"] != 'application/msword' &&
    $file["type"]!='application/vnd.openxmlformats-officedocument.wordprocessingml.document' && $file["type"] != 'application/vnd.ms-excel' &&
    $file["type"]!='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' && $file["type"] != 'text/csv'

    */
if($post_type=="application/pdf" && $cgProFalse){$isOnlyProVersionFile=true;}
else if($post_type=="application/zip" && $cgProFalse){$isOnlyProVersionFile=true;}
else if($post_type=="audio/mpeg" && $fullFilePathEnd=='mp3' && $cgProFalse){$isOnlyProVersionFile=true;}
else if($post_type=="audio/wav" && $cgProFalse){$isOnlyProVersionFile=true;}
else if($post_type=="video/mp4" && $cgProFalse){$isOnlyProVersionFile=true;}
else if($post_type=="video/quicktime" && $cgProFalse){$isOnlyProVersionFile=true;}

include ('post-type-check.php');

//echo "post_type $post_type<br>";
$uploads = wp_upload_dir();

//$check = explode($uploads['baseurl'],$image_url);

//echo $uploads['basedir'].$check[1].$post_title.".".$post_type;

// = $uploads['basedir'].$check[1];

    $uploads['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/image-comments';
    if(!is_dir($uploads['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/image-comments')){
        mkdir($uploads['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/image-comments',0755,true);
    }
//echo "post_type $filename<br>";

   // var_dump($filename); die;

  //  var_dump($NamePic); die;

if($doNotProcess!=1){

	$unix = time();
	$unixadd = $unix+2;

    $current_width = 0;
    $current_height = 0;

    if(cg_is_is_image($post_type)){
  //  list($current_width, $current_height) = getimagesize($filename);
        $imageInfoArray = wp_get_attachment_image_src($wp_image_id,'full');

        $current_width = $imageInfoArray[1];
        $current_height = $imageInfoArray[2];

        // in case of buggy image, can happen for some ico files, then get thumbnail sizes for width
        if($current_width=='0' || $current_width=='1' || $current_height=='0' || $current_height=='1'){
            $imageInfoArray = wp_get_attachment_image_src($wp_image_id,'thumbnail');
            $current_width = $imageInfoArray[1];
            $current_height = $imageInfoArray[2];
        }
    }


     $WpUserId = get_current_user_id();

    $userIP = cg1l_sanitize_method(cg_get_user_ip());

    $Version = cg_get_version_for_scripts();
    $uploadFolder = wp_upload_dir();

    $dirHTML = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/'.$unixadd."_".$NamePic."413.html";

    // can only happen if images with same name was added in same time!!!!
    if(file_exists($dirHTML)){
        $NamePic = $NamePic.'-copy';
    }

    // added since version 18.0.0
    if(cg_is_alternative_file_type_video($post_type)){
        $fileData = wp_get_attachment_metadata($wp_image_id);
        $current_height = (!empty($fileData['height'])) ? $fileData['height'] : 0;
        $current_width = (!empty($fileData['width'])) ? $fileData['width'] : 0;
    }

    $Category = 0;
    if(!empty($cg_assign_category)){$Category = $cg_assign_category;}

     // updating string after all the 0 at the end does not work. That is why version is not inserted there
    // default 0 to countr1-5 was added lately on 15.05.2020
    $wpdb->query( $wpdb->prepare(
		"
			INSERT INTO $tablename
			( id, rowid, Timestamp, NamePic,
			ImgType, 
			GalleryID,WpUpload,Width,Height,WpUserId,IP,
			Category)
			VALUES (%s,%d,%d,%s,
			%s,
			%d,%d,%d,%d,%d,%s,
			%d
			 )
		", 
			'',0,$unixadd,$NamePic,
			$post_type,
			$galeryID,$wp_image_id,$current_width,$current_height,$WpUserId,$userIP,
            $Category
	 ) );

	$nextId = $wpdb->insert_id;
    $addedWpUploadsArray[$nextId] = $wp_image_id;
    $newEntryIdsArray[] = $nextId;
	if($post_type!='ytb' && $post_type!='twt' && $post_type!='inst' && $post_type!='tkt'){
		cg_create_exif_data_and_add_to_database($nextId,$wp_image_id);
	}

    //echo "nextId $nextId<br>";

    // updating string after all the 0 at the end does not work at the top insert query. That is why version have to be inserted here
    $wpdb->update(
		"$tablename",
		array('Version' => $Version),
		array('id' => $nextId), 
		array('%s'),
		array('%d')
	);

	$post_title = substr($post_title,0,100);

    if(!empty($selectSQL1->WpPageParent)){
	    cg_create_wp_pages($GalleryID,$nextId,$post_title,$selectSQL1,$cgVersion);
    }

    if(!$isAlternativeFile && intval($selectSQL1->Version)<17){
        cg_create_fb_html($cgTableData,$galeryID,$DataShare,$DataClass,$DataLayout);
    }

    if(file_exists($uploadFolder['basedir'] . '/contest-gallery/cg-copying-gallery.txt')){
        unlink($uploadFolder['basedir'] . '/contest-gallery/cg-copying-gallery.txt');
    };

    if(!is_dir($uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/frontend-added-or-removed-images')){
        mkdir($uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/frontend-added-or-removed-images',0755,true);
    }

    // simply create empty file for later check
    $jsonFile = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/frontend-added-or-removed-images/'.$galeryID.'.txt';
    $fp = fopen($jsonFile, 'w');
    fwrite($fp, '');
    fclose($fp);

    $hasFieldsToAssign = false;
    if(!empty($cg_assign_fields)){
        $hasFieldsToAssign = true;
    }

    if($hasFieldsToAssign){
        //$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
        //$Use_as_URL_id = $wpdb->get_var($wpdb->prepare( "SELECT id FROM $tablename_form_input WHERE GalleryID = %d AND (Field_Type = 'text-f'  OR Field_Type = 'comment-f')",[$GalleryID]));
        $content = [];
        foreach ($newEntryIdsArray as $entryId) {
            // include('1_content here');
            //$getEntries1 = contest_gal1ery_convert_for_html_output_without_nl2br($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Content']);
            // content[$id][$formFieldId][short-text];
            //$Use_as_URL_id = $wpdb->get_var($wpdb->prepare( "SELECT id FROM $tablename_form_input WHERE GalleryID = %d AND Use_as_URL = '1'",[$GalleryID]));
            $content[$entryId] = [];
            foreach ($cg_assign_fields as $inputId => $field){
                $content[$entryId][$inputId] = [];
                $text = '';
                if($field['wp-type']=='alt'){
                    $text = get_post_meta($addedWpUploadsArray[$entryId], '_wp_attachment_image_alt', TRUE);
                }else{
                    if($field['wp-type']=='title'){
                        $text = $addedWpUploadsContentArray[$addedWpUploadsArray[$entryId]]['title'];
                    }
                    if($field['wp-type']=='caption'){
                        $text = $addedWpUploadsContentArray[$addedWpUploadsArray[$entryId]]['caption'];
                    }
                    if($field['wp-type']=='description'){
                        $text = $addedWpUploadsContentArray[$addedWpUploadsArray[$entryId]]['description'];
                    }
                }
                $inputTstamp = time();
                if($field['Field_Type']=='text-f'){
                    $wpdb->query( $wpdb->prepare(
                        "
									INSERT INTO $tablenameentries
									( id, pid, f_input_id, GalleryID, 
									Field_Type, Field_Order, Short_Text, Long_Text, Tstamp)
									VALUES ( %s,%d,%d,%d,
									%s,%d,%s,%s,%d ) 
								",
                        '',$entryId,$inputId,$GalleryID,
                        $field['Field_Type'],$field['Field_Order'],$text,'',$inputTstamp
                    ) );
                }
                if($field['Field_Type']=='comment-f'){
                    $wpdb->query( $wpdb->prepare(
                        "
									INSERT INTO $tablenameentries
									( id, pid, f_input_id, GalleryID, 
									Field_Type, Field_Order, Short_Text, Long_Text, Tstamp)
									VALUES ( %s,%d,%d,%d,
									%s,%d,%s,%s,%d ) 
								",
                        '',$entryId,$inputId,$GalleryID,
                        $field['Field_Type'],$field['Field_Order'],'',$text,$inputTstamp
                    ) );
                }
            }
        }

    }
    // do not remove!!!!, sentence will be checked to scroll and display message
    echo 'cg-images-added';

}
else{
    if($isOnlyProVersionFile){
        // do not remove!!!!, sentence will be checked to scroll and display message
        echo 'cg-pro-version-only';
    }else{
        // do not remove!!!!, sentence will be checked to scroll and display message
        echo 'cg-file-type-is-not-supported';
    }
}
	
}


