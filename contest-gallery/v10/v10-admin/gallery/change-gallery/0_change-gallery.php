<?php
//$fileDataForPostArray = json_decode(stripslashes(sanitize_text_field($_POST['cg_multiple_files_for_post'][3136])),true);
// echo "<pre>";
//print_r($_POST);
// echo "</pre>";
// echo "<pre>";
//print_r($_POST['cgSellContainer']);
// echo "</pre>";

/*
echo "<pre>";
print_r($_POST['cg_multiple_files_for_post']);
echo "</pre>";
*/
//die;
/*error_reporting(E_ALL);
ini_set('display_errors', 'On');*/

/*echo "<pre>";
echo print_r($_POST);
echo "</pre>";*/

$start = 0; // Startwert setzen (0 = 1. Zeile)
$step = 10;

if (isset($_GET["start"])) {
	$muster = "/^[0-9]+$/"; // reg. Ausdruck f�r Zahlen
	if (preg_match($muster, $_GET["start"]) == 0) {
		$start = 0; // Bei Manipulation R�ckfall auf 0
	} else {
		$start = absint($_GET["start"]);
	}
}

if (isset($_GET["step"])) {
	$muster = "/^[0-9]+$/"; // reg. Ausdruck f�r Zahlen
	if (preg_match($muster, (isset($_GET["start"]) ? absint($_GET["start"]) : 1)) == 0) {
		$step = 10; // Bei Manipulation R�ckfall auf 0
	} else {
		$step = absint($_GET["step"]);
	}
}

global $wpdb;

// Set table names
$tablename = $wpdb->prefix . "contest_gal1ery";
$table_posts = $wpdb->prefix . "posts";
$table_users = $wpdb->base_prefix . "users";
$table_usermeta = $wpdb->base_prefix . "usermeta";
$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
$tablenameentries = $wpdb->prefix . "contest_gal1ery_entries";
$tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";
$tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
$tablename_comments = $wpdb->prefix . "contest_gal1ery_comments";
$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
$tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
$tablename_wp_pdf_previews = $wpdb->base_prefix . "contest_gal1ery_pdf_previews";

$GalleryID = absint($GalleryID);
$infoPidsArray = [];

// check which fileds are allowed for json save because allowed gallery or single view
$uploadFormFields = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename_form_input WHERE GalleryID = %d",[$GalleryID]));
$Field1IdGalleryView = $wpdb->get_var($wpdb->prepare("SELECT Field1IdGalleryView FROM $tablename_options_visual WHERE GalleryID = %d",[$GalleryID]));
//$watermarkPositionId = $wpdb->get_var($wpdb->prepare("SELECT id FROM $tablename_form_input WHERE GalleryID = %d AND WatermarkPosition != '' AND Active = 1",[$GalleryID]));
$IsForWpPageTitleInputId = $wpdb->get_var($wpdb->prepare("SELECT id FROM $tablename_form_input WHERE GalleryID = %d AND IsForWpPageTitle = 1",[$GalleryID]));

$fieldsForSaveContentArray = array();

foreach ($uploadFormFields as $field) {
	if (empty($fieldsForSaveContentArray[$field->id])) {
		$fieldsForSaveContentArray[$field->id] = array();
	}
	$fieldsForSaveContentArray[$field->id]['Field_Type'] = $field->Field_Type;
	$fieldsForSaveContentArray[$field->id]['Field_Order'] = $field->Field_Order;
	$fieldContent = unserialize($field->Field_Content);
	$fieldsForSaveContentArray[$field->id]['Field_Title'] = $fieldContent['titel'];
	if ($field->Field_Type == 'date-f') {
		$fieldsForSaveContentArray[$field->id]['Field_Format'] = $fieldContent['format'];
	}
}


$wpUsers = $wpdb->base_prefix . "users";

$imageInfoArray = array();

$wp_upload_dir = wp_upload_dir();

$jsonUpload = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $GalleryID . '/json';
$jsonUploadImageData = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $GalleryID . '/json/image-data';
$jsonUploadImageInfoDir = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $GalleryID . '/json/image-info';
$jsonUploadImageCommentsDir = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $GalleryID . '/json/image-comments';

$thumbSizesWp = array();
$thumbSizesWp['thumbnail_size_w'] = get_option("thumbnail_size_w");
$thumbSizesWp['medium_size_w'] = get_option("medium_size_w");
$thumbSizesWp['large_size_w'] = get_option("large_size_w");

$uploadFolder = wp_upload_dir();

// DELTE PICS FIRST
//echo "DELETE PICS!<br>";
$isRemoveEcommerceEntryWpUploadIdsExecuted = false;
include(__DIR__.'/../delete-pics.php');

// should be done after delete pics, $_POST['removeEcommerceEntryWpUploadIds'] will be collected in delete-pics
// can not be deleted anymore if is for sale!!!
//if(!$isRemoveEcommerceEntryWpUploadIdsExecuted){
//cg_move_gallery_changes_file_from_ecommerce_sale_folder($GalleryID);
//}

$activate = '';
if (!empty($_POST['cg_activate'])) {
	$activate = $_POST['cg_activate'];
}else{
	$_POST['cg_activate'] = array();
}

if (empty($_POST['cg_deactivate'])) {
	$_POST['cg_deactivate'] = array();
}

if (!empty($_POST['cg_row'])) {
	$rowids = $_POST['cg_row'];
} else {
	$rowids = [];
}

$content = array();

if (!empty($_POST['content'])) {
	$_POST['content'] = cg1l_sanitize_post($_POST['content']);
	$content = $_POST['content'];
}else{
	$_POST['content'] = array();
}

if (empty($_POST['imageCategory'])) {
	$_POST['imageCategory'] = array();
}

// unset rowids if Deleted!!!!
if (!empty($_POST['cg_delete'])) {
	foreach ($_POST['cg_delete'] as $key => $value) {
		unset($rowids[$key]);
		unset($content[$key]);
		unset($_POST['imageCategory'][$key]);
		// activate or deactivate can't be send if delete is send! But unset to go sure :)
		unset($_POST['cg_activate'][$key]);
		unset($_POST['cg_deactivate'][$key]);
	}
}

if (!is_dir($jsonUpload)) {
	mkdir($jsonUpload, 0755, true);
}

if (!is_dir($jsonUploadImageData)) {
	mkdir($jsonUploadImageData, 0755, true);
}

if (!is_dir($jsonUploadImageInfoDir)) {
	mkdir($jsonUploadImageInfoDir, 0755, true);
}

if (!is_dir($jsonUploadImageCommentsDir)) {
	mkdir($jsonUploadImageCommentsDir, 0755, true);
}

$imageArray = [];

if (!empty($_POST['imageCategory'])) {

	$querySETrowForCategoryIds = 'UPDATE ' . $tablename . ' SET Category = CASE id ';
	$querySETaddRowForCategoryIds = ' ELSE Category END WHERE id IN (';
	$queryArgsArray = [];
	$queryAddArgsArray = [];
	$queryArgsCounter = 0;

	foreach ($_POST['imageCategory'] as $imageId => $categoryId) {

		if ($categoryId == 'off' && is_string($categoryId)) {
			continue;
		} else {

			$imageId = absint(sanitize_text_field($imageId));
			$categoryId = absint(sanitize_text_field($categoryId));

			$querySETrowForCategoryIds .= " WHEN %d THEN %d";
			$querySETaddRowForCategoryIds .= "%d,";
			$queryArgsArray[] = $imageId;
			$queryArgsArray[] = $categoryId;
			$queryAddArgsArray[] = $imageId;
			$queryArgsCounter++;

		}
	}

	// ic = i counter
	for ($ic = 0;$ic<$queryArgsCounter;$ic++){
		$queryArgsArray[] =$queryAddArgsArray[$ic];
	}

	$querySETaddRowForCategoryIds = substr($querySETaddRowForCategoryIds,0,-1);
	$querySETaddRowForCategoryIds .= ")";

	$querySETrowForCategoryIds .= $querySETaddRowForCategoryIds;

	$wpdb->query($wpdb->prepare($querySETrowForCategoryIds,$queryArgsArray));

}

// Change Order Auswahl --- ENDE

$galeryrow = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablenameOptions WHERE id = %d",[$GalleryID]));

$informORnot = $galeryrow->Inform;

// Update Inform

// START QUERIES --- END

$tablenameemail = $wpdb->prefix . "contest_gal1ery_mail";
$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
$tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
$contest_gal1ery_f_input = $wpdb->prefix . "contest_gal1ery_f_input";
$selectSQLemail = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablenameemail WHERE GalleryID = %d",[$GalleryID]));
$proOptions = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_pro_options WHERE GalleryID = %d",[$GalleryID]));

$Manipulate = $proOptions->Manipulate;
$FbLikeNoShare = $proOptions->FbLikeNoShare;
$PdfPreviewBackend = $proOptions->PdfPreviewBackend;
$DataShare = ($FbLikeNoShare == 1) ? 'true' : 'false';
$DataClass = ($proOptions->FbLikeOnlyShare==1) ? 'fb-share-button' : 'fb-like';
$DataLayout = ($proOptions->FbLikeOnlyShare==1) ? 'button' : 'button_count';

$Subject = contest_gal1ery_convert_for_html_output($selectSQLemail->Header);
$Admin = $selectSQLemail->Admin;
$Reply = $selectSQLemail->Reply;
$cc = $selectSQLemail->CC;
$bcc = $selectSQLemail->BCC;
$contentMail = contest_gal1ery_convert_for_html_output($selectSQLemail->Content);

$url = trim(sanitize_text_field($selectSQLemail->URL));
//	$url = (strpos($url,'?')) ? $url.'&' : $url.'?';

$posUrl = "\$url\$";

// echo $posUrl;

$urlCheck = (stripos($contentMail, $posUrl)) ? 1 : 0;

/*echo "<pre>";
print_r($_POST['cg_rThumb']);
echo "</pre>";*/

if(!empty($_POST['cg_rThumb'])){
	$querySETrow = 'UPDATE ' . $tablename . ' SET rThumb = CASE';
	$querySETaddRow = ' ELSE rThumb END WHERE (id) IN (';
	$queryArgsArray = [];
	$queryAddArgsArray = [];
	$queryArgsCounter = 0;

	foreach ($_POST['cg_rThumb'] as $id => $rThumbValue){

		$id = absint (sanitize_text_field($id));
		$rThumbValue = absint(sanitize_text_field($rThumbValue));

		$querySETrow .= " WHEN (id = %d) THEN %d";
		$querySETaddRow .= "(%d), ";
		$queryArgsArray[] = $id;
		$queryArgsArray[] = $rThumbValue;
		$queryAddArgsArray[] = $id;
		$queryArgsCounter++;

	}

	// ic = i counter
	for ($ic = 0;$ic<$queryArgsCounter;$ic++){
		$queryArgsArray[] =$queryAddArgsArray[$ic];
	}

	$querySETaddRow = substr($querySETaddRow, 0, -2);
	$querySETaddRow .= ")";

	$querySETrow .= $querySETaddRow;

	$wpdb->query($wpdb->prepare($querySETrow,$queryArgsArray));
}

//var_dump('print post');

/*
echo "<pre>";
print_r($_POST);
echo "</pre>";

if(!empty($_POST['cg_multiple_files_for_post'])){
    echo "<pre>";
    print_r($_POST['cg_multiple_files_for_post']);
    echo "</pre>";
    var_dump(12312);
}*/

$PdfPreviewIfWpUploadReplace = 0;
$PdfWpUploadsToCreate = [];// do not remove, check is required

if(cg_get_version()=='contest-gallery'){
    $PdfPreviewBackend = 0;
}

// check if has PDF files to preview to create
if(!empty($_POST['cg_multiple_files_for_post']) && $PdfPreviewBackend == 1){
    foreach ($_POST['cg_multiple_files_for_post'] as $id => $fileDataForPost){
        if(empty($fileDataForPost)){// might be empty when activated for sale
            continue;
        }
        $hasPdfPreview = false;
        $fileDataForPostArray = json_decode(stripslashes(sanitize_text_field($fileDataForPost)),true);
        /*echo "<pre>";
        print_r($fileDataForPostArray);
        echo "</pre>";*/
        $fileDataForPostArrayNew = $fileDataForPostArray;
        foreach ($fileDataForPostArray as $order => $array){
            if(empty($array['PdfPreview'])){
                $array['PdfPreview'] = 0;// so is set
            }
            if(!empty($array['post_mime_type']) && $array['post_mime_type']=='application/pdf' && absint($array['PdfPreview'])==0){
                //var_dump('create pdf');
                $hasPdfPreview = true;
                $WpUpload = absint($array['WpUpload']);
                //var_dump('$WpUpload');
                //var_dump($WpUpload);
                $PdfPreview = $wpdb->get_var("SELECT WpUploadPreview FROM $tablename_wp_pdf_previews WHERE WpUpload = '$WpUpload'");
                //$PdfWpUploadsToCreate do not remove, check is required
                if(empty($PdfPreview) && in_array($WpUpload,$PdfWpUploadsToCreate)===false){
                    if(empty($PdfPreviewsToCreateString)){
                        $PdfPreviewsToCreateString =  'cg-pdf-previews-to-create###'.$id.';'.$WpUpload.';'.$array['guid'];
                    }else{
                        $PdfPreviewsToCreateString .= ','.$id.';'.$WpUpload.';'.$array['guid'];
                    }
                    $PdfWpUploadsToCreate[] = $WpUpload;
                }else{
                    $PdfPreviewImageLarge = wp_get_attachment_image_src($PdfPreview, 'large');
                    //var_dump('$PdfPreviewImage');
                    //var_dump($PdfPreviewImage);
                    if(!empty($PdfPreviewImageLarge)){
                        if(in_array($WpUpload,$PdfWpUploadsToCreate)===false){
                            if(empty($PdfPreviewsToCreateString)){
                                $PdfPreviewsToCreateString = 'cg-pdf-previews-to-create###'.$id.';'.$WpUpload.';'.$array['guid'].';'.$PdfPreviewImageLarge[0];
                            }else{
                                $PdfPreviewsToCreateString .= ','.$id.';'.$WpUpload.';'.$array['guid'].';'.$PdfPreviewImageLarge[0];
                            }
                            $PdfWpUploadsToCreate[] = $WpUpload;
                        }
                        $array['PdfPreviewImageLarge'] = $PdfPreviewImageLarge[0];
                        $PdfPreviewImage = wp_get_attachment_image_src($PdfPreview, 'full');
                        $array['PdfPreviewImage'] = $PdfPreviewImage[0];
                        $array['PdfPreview'] = absint($PdfPreview);
                        $array['PdfOriginal'] = get_the_guid($array['WpUpload']);
                        $array['full'] = $PdfPreviewImage[0];
                        $array['guid'] = $PdfPreviewImage[0];
                        $array['Width'] = $PdfPreviewImage[1];
                        $array['Height'] = $PdfPreviewImage[2];
                        $array['thumbnail'] = $PdfPreviewImageLarge[0];
                        $array['medium'] = $PdfPreviewImageLarge[0];
                        $array['large'] = $PdfPreviewImageLarge[0];
                        $array['post_mime_type'] = 'image/png';
                        $array['ImgType'] = 'png';
                        $array['type'] = 'image';
                    }else{
                        // delete row in $tablename_wp_pdf_previews
                        $wpdb->query("DELETE FROM $tablename_wp_pdf_previews WHERE WpUpload = $WpUpload");
                        if(in_array($WpUpload,$PdfWpUploadsToCreate)===false){
                            if(empty($PdfPreviewsToCreateString)){
                                $PdfPreviewsToCreateString = 'cg-pdf-previews-to-create###'.$id.';'.$WpUpload.';'.$array['guid'];
                            }else{
                                $PdfPreviewsToCreateString .= ','.$id.';'.$WpUpload.';'.$array['guid'];
                            }
                            $PdfWpUploadsToCreate[] = $WpUpload;
                        }
                        $array['PdfPreviewImage'] = '';
                        $array['PdfPreview'] = 0;
                    }
                    $fileDataForPostArrayNew[$order] = $array;
                    //$fileDataForPost[$order] = $array;
                    //$_POST['cg_multiple_files_for_post'][$id] = $fileDataForPost;
                }
            }
            /*if(!empty($array['PdfOriginal'])){// not required so far, maybe have to be constructed further in the future
                $hasPdfPreview = true;
            }*/
        }
        if($hasPdfPreview){
            $_POST['cg_multiple_files_for_post'][$id] = json_encode($fileDataForPostArrayNew);
        }
    }
}

//var_dump('$PdfPreviewsToCreateString');
//var_dump($PdfPreviewsToCreateString);

// has to be done here extra because might get empty after processing
if(!empty($_POST['cg_multiple_files_for_post']) && !empty($_POST['cgDeleteOriginalImageSourceAlso'])){
	// correct if delete was sent
	if(!empty($_POST['cg_delete']) && !empty($deletedWpUploadsFromSpace)){
		// check for realIds first
		foreach ($_POST['cg_delete'] as $idToDelete){
			if(array_key_exists($idToDelete,$_POST['cg_multiple_files_for_post'])){
				unset($_POST['cg_multiple_files_for_post'][$idToDelete]);
			}
		}
		// check for WpUploads then
		foreach ($_POST['cg_multiple_files_for_post'] as $id => $fileDataForPost){
			$isWpUploadDeleted = false;
			foreach ($fileDataForPost as $order => $array){
				if(in_array($array['WpUpload'],$deletedWpUploadsFromSpace)!==false){
					$isWpUploadDeleted = true;
					unset($fileDataForPost[$order]);
				}
			}
			if($isWpUploadDeleted){
				if(empty($fileDataForPost) OR count($fileDataForPost)==1){
					$_POST['cg_multiple_files_for_post'][$id] = '';// simply empty then multiple files will get empty in field in database
				}else{
					$newOrder = 1;
					$newFileDataForPost = [];
					foreach ($fileDataForPost as $order => $array){
						$newFileDataForPost[$newOrder] = $array;
						$newOrder++;
					}
					$_POST['cg_multiple_files_for_post'][$id] = $newFileDataForPost;
				}
			}
		}
	}
}

// for replace always cg_multiple_files_for_post will be sent with 1 item
if(!empty($_POST['cg_multiple_files_for_post'])){

	// NamePic
	// ImgType
	// WpUpload
	// Width
	// Height
	// Exif

	$querySETrowMultipleFiles = 'UPDATE ' . $tablename . ' SET MultipleFiles = CASE';
	$querySETaddRowMultipleFiles = ' ELSE MultipleFiles END WHERE (id) IN (';

	$hasRealIdDeleted = false;
	$realIdWhereSourceChanged = 0;
	$newWpUploadWhenSourceChanged = 0;
	$newPostTitleWhenSourceChanged = '';
	$queryHasRealIdDeleted = 'INSERT INTO '.$tablename.' (id, NamePic, ImgType, WpUpload, Width, Height, rThumb, Exif, PdfPreview) VALUES ';
	$queryArgsArray = [];
	$queryAddArgsArray = [];
	$queryArgsCounter = 0;
	$queryArgsArray1 = [];

	foreach ($_POST['cg_multiple_files_for_post'] as $id => $fileDataForPost){

		$id = absint($id);

		if(!empty($fileDataForPost)){

			$fileDataForPostArray = json_decode(stripslashes(sanitize_text_field($fileDataForPost)),true);

			/*var_dump('$fileDataForPostArray 1234');
			echo "<pre>";
			print_r($fileDataForPostArray);
			echo "</pre>";*/

			$hasRealId = false;
			$realIdWpUpload = 0;
			$hasRealIdToReplace = false;

			$fileDataForPostArrayLength = count($fileDataForPostArray);

			foreach ($fileDataForPostArray as $order => $array){
				if(!empty($array['isRealIdSource'])){
					$hasRealId = true;
				}
			}

			if(!empty($_POST['cgWpUploadToReplace'])){

                $WpUpload = absint($_POST['cgNewWpUploadWhichReplace']);
                $cgWpUploadToReplace = absint($_POST['cgWpUploadToReplace']);
                $mime_type = get_post_mime_type($WpUpload);
                $rowObject = $wpdb->get_row("SELECT * FROM $tablename WHERE id = '$id'");
                if($mime_type=='application/pdf' && $PdfPreviewBackend == 1){
                    $PdfPreview = $wpdb->get_var("SELECT WpUploadPreview FROM $tablename_wp_pdf_previews WHERE WpUpload = '$WpUpload'");
                    $guid = get_the_guid($WpUpload);
                    //$PdfWpUploadsToCreate do not remove, check is required
                    if(empty($PdfPreview) && in_array($WpUpload,$PdfWpUploadsToCreate)===false){
                        if(empty($PdfPreviewsToCreateString)){
                            $PdfPreviewsToCreateString =  'cg-pdf-previews-to-create###'.$id.';'.$WpUpload.';'.$guid;
                        }else{
                            $PdfPreviewsToCreateString .= ','.$id.';'.$WpUpload.';'.$guid;
                        }
                        $PdfWpUploadsToCreate[] = $WpUpload;
                    }else{
                        $PdfPreviewImageLarge = wp_get_attachment_image_src($PdfPreview, 'large');
                        if(!empty($PdfPreviewImageLarge)){
                            if(in_array($WpUpload,$PdfWpUploadsToCreate)===false){
                                if(empty($PdfPreviewsToCreateString)){
                                    $PdfPreviewsToCreateString = 'cg-pdf-previews-to-create###'.$id.';'.$WpUpload.';'.$guid.';'.$PdfPreviewImageLarge[0];
                                }else{
                                    $PdfPreviewsToCreateString .= ','.$id.';'.$WpUpload.';'.$guid.';'.$PdfPreviewImageLarge[0];
                                }
                                $PdfWpUploadsToCreate[] = $WpUpload;
                            }
                            //         var_dump('$PdfPreview replace');
                            //        var_dump($PdfPreview);
                            $PdfPreviewIfWpUploadReplace = $PdfPreview;
                        }else{
                            // then update back to 0!!!
                            if($cgWpUploadToReplace == $rowObject->WpUpload){
                                $wpdb->query("UPDATE $tablename SET PdfPreview=0 WHERE id = $id");
                            }
                            // delete row in $tablename_wp_pdf_previews
                            $wpdb->query("DELETE FROM $tablename_wp_pdf_previews WHERE WpUpload = $WpUpload");
                            if(in_array($WpUpload,$PdfWpUploadsToCreate)===false){
                                if(empty($PdfPreviewsToCreateString)){
                                    $PdfPreviewsToCreateString = 'cg-pdf-previews-to-create###'.$id.';'.$WpUpload.';'.$guid;
                                }else{
                                    $PdfPreviewsToCreateString .= ','.$id.';'.$WpUpload.';'.$guid;
                                }
                                $PdfWpUploadsToCreate[] = $WpUpload;
                            }
                        }
                    }
                }

				// var_dump('will be replaced');
				$cgNewWpUploadWhichReplace = absint($_POST['cgNewWpUploadWhichReplace']);
				$ecommerceEntry = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_entries WHERE pid = '$id'");
				$hasFileToReplaceAndMove = false;
                //var_dump('$cgNewWpUploadWhichReplace');
                //var_dump($cgNewWpUploadWhichReplace);
                if(!empty($ecommerceEntry->WpUploadFilesForSale)){
                    //var_dump('unserialize $ecommerceEntry->WpUploadFilesForSale');
                    //var_dump(unserialize($ecommerceEntry->WpUploadFilesForSale));
                }
                //var_dump('cgWpUploadToReplace');
                //var_dump($_POST['cgWpUploadToReplace']);

                $NewWpUploadOrder = 0;

                foreach ($fileDataForPostArray as $order1 => $array1){
                    if($array1['WpUpload'] == $cgNewWpUploadWhichReplace){
                        $NewWpUploadOrder = $order1;
                    }
                }

                // for replace always cg_multiple_files_for_post will be sent with 1 item
				if(!empty($ecommerceEntry) && !empty($ecommerceEntry->WpUploadFilesForSale) && in_array(absint($_POST['cgWpUploadToReplace']),unserialize($ecommerceEntry->WpUploadFilesForSale))!==false){
                    //('before move file');
					$removedWpUploadIdsFromSale = [absint($_POST['cgWpUploadToReplace'])];
                    // for replace always cg_multiple_files_for_post will be sent with 1 item
                    //var_dump('post_mime_type test');
                    //var_dump($fileDataForPostArray[$NewWpUploadOrder]['post_mime_type']);

                /*    echo "<pre>";
                    print_r($fileDataForPostArray);
                    echo "</pre>";*/

               //     var_dump('$cgNewWpUploadWhichReplace');
               //     var_dump($cgNewWpUploadWhichReplace);

        //            die;
                    if(!(!empty($fileDataForPostArray[$NewWpUploadOrder]['post_mime_type']) && $fileDataForPostArray[$NewWpUploadOrder]['post_mime_type'] == 'application/pdf' && $PdfPreviewBackend == 1)){
                        //var_dump('move file');
                        cg_replace_ecommerce_file($id, $GalleryID, $ecommerceEntry, $cgNewWpUploadWhichReplace, $fileDataForPostArray,$removedWpUploadIdsFromSale);
                    }else{
                        $cgNewWpUploadWhichReplaceForPdfPreview = $cgNewWpUploadWhichReplace;
                        $cgWpUploadToReplaceForPdfPreview = $cgWpUploadToReplace;
                    }

					// var_dump('$removedWpUploadIdsFromSale');
					// var_dump($removedWpUploadIdsFromSale);
					/*cg_move_file_from_ecommerce_sale_folder($id, $GalleryID, $ecommerceEntry->id, $removedWpUploadIdsFromSale);
					$sqlObjectFile = $wpdb->get_row("SELECT * FROM $tablename WHERE id = '$id'");
					$WpUploadFilesForSale = unserialize($ecommerceEntry->WpUploadFilesForSale);
					unset($WpUploadFilesForSale[array_search($_POST['cgWpUploadToReplace'], $WpUploadFilesForSale)]);
					$WpUploadFilesForSale[] = $cgNewWpUploadWhichReplace;
					$sqlObjectFile->MultipleFiles = serialize($fileDataForPostArray);// has to be set here
					// var_dump('$sqlObjectFile->MultipleFiles');
					// echo "<pre>";
					// 	print_r($sqlObjectFile->MultipleFiles);
					// echo "</pre>";
					// var_dump('$WpUploadFilesForSale');
					// var_dump($WpUploadFilesForSale);

					cg_move_file_ecommerce_sale_folder($id, $GalleryID,$sqlObjectFile,$ecommerceEntry,$WpUploadFilesForSale,$removedWpUploadIdsFromSale);*/


				}
			}

            //var_dump('$cgWpUploadToReplace123');
            //var_dump($cgWpUploadToReplace);

            //var_dump('$cgNewWpUploadWhichReplace123');
            //var_dump($cgNewWpUploadWhichReplace);

			// var_dump('$hasRealId');
			// var_dump($hasRealId);

            // $fileDataForPostArray[1] becomes new real id then!!!
			if(!$hasRealId){// then realId must be deleted
                //         var_dump('!$hasRealId');
                //      var_dump(true);
				$hasRealIdDeleted = true;
                //      var_dump('$hasRealIdDeleted123');
                //     var_dump($hasRealIdDeleted);
				$realIdWhereSourceChanged = $id;
                // for replace always cg_multiple_files_for_post will be sent with 1 item
				$newPostTitleWhenSourceChanged = $fileDataForPostArray[1]['NamePic'];
				$newWpUploadWhenSourceChanged = $fileDataForPostArray[1]['WpUpload'];
				// var_dump('$fileDataForPostArray');

				// echo "<pre>";
				// print_r($fileDataForPostArray);
				// echo "</pre>";

				$queryHasRealIdDeleted .= "(%d,%s,%s,%d,%d,%d,%d,%s,%d),";
				$queryArgsArray1[] = $id;
                // $fileDataForPostArray[1] becomes new real id then!!!
				$queryArgsArray1[] = $fileDataForPostArray[1]['NamePic'];
				$queryArgsArray1[] = $fileDataForPostArray[1]['ImgType'];
				$queryArgsArray1[] = $fileDataForPostArray[1]['WpUpload'];
				$queryArgsArray1[] = absint($fileDataForPostArray[1]['Width']);
				$queryArgsArray1[] = absint($fileDataForPostArray[1]['Height']);
				$queryArgsArray1[] = absint($fileDataForPostArray[1]['rThumb']);
				$queryArgsArray1[] = absint($fileDataForPostArray[1]['Exif']);
                if(!empty($PdfPreviewIfWpUploadReplace)){
                    $queryArgsArray1[] = $PdfPreviewIfWpUploadReplace;
                }else if(!empty($fileDataForPostArray[1]['PdfPreview'])){
                    $queryArgsArray1[] = absint($fileDataForPostArray[1]['PdfPreview']);
                }else{
                    $queryArgsArray1[] = 0;
                }

				$fileDataForPostArrayNew = [];
				$fileDataForPostArrayNew['WpUpload'] = $fileDataForPostArray[1]['WpUpload'];
				$fileDataForPostArrayNew['isRealIdSource'] = true;
				$fileDataForPostArray[1] = $fileDataForPostArrayNew;

				// var_dump('$fileDataForPostArray new');

				// echo "<pre>";
				// print_r($fileDataForPostArray);
				// echo "</pre>";

			}

			if(count($fileDataForPostArray)>1){
				$fileDataForPostCorrected = [];
				$iCorrect = 1;
				ksort($fileDataForPostArray);

				// order might not be 1,2,3... anymore... has to be corrected
				// but has to be sorted by key! so from 1 to 10 and so on
				foreach ($fileDataForPostArray as $order => $fileDataForPostArrayPart){
					$fileDataForPostCorrected[$iCorrect] = $fileDataForPostArrayPart;
					$iCorrect++;
				}
				$fileDataForPost = serialize($fileDataForPostCorrected);
			}else{
				//$fileDataForPost = '""';
				$fileDataForPost = '';// now with prepare can be really empty
			}

		}else{
			//$fileDataForPost = '""';
			$fileDataForPost = '';// now with prepare can be really empty
		}

		$querySETrowMultipleFiles .= " WHEN (id = %d) THEN %s";
		$querySETaddRowMultipleFiles .= "(%d), ";
		$queryArgsArray[] = $id;
		$queryArgsArray[] = $fileDataForPost;
		$queryAddArgsArray[] = $id;
		$queryArgsCounter++;

	}

    //   var_dump('$PdfPreviewsToCreateString 2');
    // var_dump($PdfPreviewsToCreateString);

	// ic = i counter
	for ($ic = 0;$ic<$queryArgsCounter;$ic++){
		$queryArgsArray[] =$queryAddArgsArray[$ic];
	}

	$querySETaddRowMultipleFiles = substr($querySETaddRowMultipleFiles, 0, -2);
	$querySETaddRowMultipleFiles .= ")";

	$querySETrowMultipleFiles .= $querySETaddRowMultipleFiles;

	$wpdb->query($wpdb->prepare($querySETrowMultipleFiles,$queryArgsArray));

	if(!empty($hasRealIdDeleted)){
        //    var_dump('$hasRealIdDeleted execute');
		$queryHasRealIdDeleted = substr($queryHasRealIdDeleted, 0, -1);
		$queryHasRealIdDeleted .= " ON DUPLICATE KEY UPDATE NamePic = VALUES(NamePic), ImgType = VALUES(ImgType), WpUpload = VALUES(WpUpload), Width = VALUES(Width), Height = VALUES(Height),  rThumb = VALUES(rThumb), Exif = VALUES(Exif), PdfPreview = VALUES(PdfPreview)";
		$wpdb->query($wpdb->prepare($queryHasRealIdDeleted,$queryArgsArray1));
		if(intval($galeryrow->Version)>=22){
			$IsForWpPageTitleInputId = $wpdb->get_var("SELECT id FROM $tablename_form_input WHERE GalleryID = '$GalleryID' AND IsForWpPageTitle = '1'");
			$hasWpPageTitleInputIfSourceChanged = false;
			// var_dump('$IsForWpPageTitleInputId');
			// var_dump($IsForWpPageTitleInputId);
			if(!empty($IsForWpPageTitleInputId)){
				$Short_Text_if_realIdSourceChanged = $wpdb->get_var("SELECT Short_Text FROM $tablenameentries WHERE f_input_id = '$IsForWpPageTitleInputId' AND pid = '$realIdWhereSourceChanged' AND GalleryID = '$GalleryID'");
				// var_dump('$Short_Text_if_realIdSourceChanged');
				// var_dump($Short_Text_if_realIdSourceChanged);
				if(!empty($Short_Text_if_realIdSourceChanged)){
					$hasWpPageTitleInputIfSourceChanged = true;
				}
			}

		}

	}

}


if(!empty($PdfPreviewsToCreateString)){
    $PdfPreviewsToCreateString .= '###cg-pdf-previews-to-create-end';
}

if (!empty($_POST['cg_winner'])) {

	$querySETrowWinner = 'UPDATE ' . $tablename . ' SET Winner = CASE';
	$querySETaddRowWinner = ' ELSE Winner END WHERE (id) IN (';
	$queryArgsArray = [];
	$queryAddArgsArray = [];
	$queryArgsCounter = 0;

	foreach ($_POST['cg_winner'] as $key => $value) {

		$key = absint($key);

		$querySETrowWinner .= " WHEN (id = %d) THEN 1";
		$querySETaddRowWinner .= "(%d), ";
		$queryArgsArray[] = $key;
		$queryAddArgsArray[] = $key;
		$queryArgsCounter++;

	}

	// ic = i counter
	for ($ic = 0;$ic<$queryArgsCounter;$ic++){
		$queryArgsArray[] =$queryAddArgsArray[$ic];
	}

	$querySETaddRowWinner = substr($querySETaddRowWinner, 0, -2);
	$querySETaddRowWinner .= ")";

	$querySETrowWinner .= $querySETaddRowWinner;

	$wpdb->query($wpdb->prepare($querySETrowWinner,$queryArgsArray));

}

if (!empty($_POST['cg_winner_not'])) {

	$querySETrowWinnerNot = 'UPDATE ' . $tablename . ' SET Winner = CASE';
	$querySETaddRowWinnerNot = ' ELSE Winner END WHERE (id) IN (';
	$queryArgsArray = [];
	$queryAddArgsArray = [];
	$queryArgsCounter = 0;

	foreach ($_POST['cg_winner_not'] as $key => $value) {

		$key = absint($key);

		$querySETrowWinnerNot .= " WHEN (id = %d) THEN 0";
		$querySETaddRowWinnerNot .= "(%d), ";
		$queryArgsArray[] = $key;
		$queryAddArgsArray[] = $key;
		$queryArgsCounter++;

	}

	// ic = i counter
	for ($ic = 0;$ic<$queryArgsCounter;$ic++){
		$queryArgsArray[] =$queryAddArgsArray[$ic];
	}

	$querySETaddRowWinnerNot = substr($querySETaddRowWinnerNot, 0, -2);
	$querySETaddRowWinnerNot .= ")";

	$querySETrowWinnerNot .= $querySETaddRowWinnerNot;

	$wpdb->query($wpdb->prepare($querySETrowWinnerNot,$queryArgsArray));

}

$_POST['addCountChange'] = array();


// Rating manipulieren hier

if ($Manipulate == 1) {

	if ($galeryrow->AllowRating == 2) {

		if (!empty($_POST['addCountS'])) {

			$querySETrowAddCount = 'UPDATE ' . $tablename . ' SET addCountS = CASE';
			$querySETaddRowAddCount = ' ELSE addCountS END WHERE (id) IN (';
			$queryArgsArray = [];
			$queryAddArgsArray = [];
			$queryArgsCounter = 0;

			foreach ($_POST['addCountS'] as $key => $value) {

				$_POST['addCountChange'][$key] = $key;

				$key = absint($key);
				$value = absint($value);

				$querySETrowAddCount .= " WHEN (id = %d) THEN %d";
				$querySETaddRowAddCount .= "(%d), ";
				$queryArgsArray[] = $key;
				$queryArgsArray[] = $value;
				$queryAddArgsArray[] = $key;
				$queryArgsCounter++;

			}

			// ic = i counter
			for ($ic = 0;$ic<$queryArgsCounter;$ic++){
				$queryArgsArray[] =$queryAddArgsArray[$ic];
			}

			$querySETaddRowAddCount = substr($querySETaddRowAddCount, 0, -2);
			$querySETaddRowAddCount .= ")";

			$querySETrowAddCount .= $querySETaddRowAddCount;

			$wpdb->query($wpdb->prepare($querySETrowAddCount,$queryArgsArray));

		}
	}

	if ($galeryrow->AllowRating == 1 OR ($galeryrow->AllowRating >= 12 AND $galeryrow->AllowRating <=20)) {

		for ($forCounter = 1;$forCounter<=10;$forCounter++){
			if (!empty($_POST['addCountR'.$forCounter])) {

				$querySETrowAddCount = 'UPDATE ' . $tablename . ' SET addCountR'.$forCounter.' = CASE';
				$querySETaddRowAddCount = ' ELSE addCountR'.$forCounter.' END WHERE (id) IN (';
				$queryArgsArray = [];
				$queryAddArgsArray = [];
				$queryArgsCounter = 0;

				foreach ($_POST['addCountR'.$forCounter] as $key => $value) {

					$_POST['addCountChange'][$key] = $key;

					$key = absint($key);
					$value = absint($value);

					$querySETrowAddCount .= " WHEN (id = %d) THEN %d";
					$querySETaddRowAddCount .= "(%d), ";
					$queryArgsArray[] = $key;
					$queryArgsArray[] = $value;
					$queryAddArgsArray[] = $key;
					$queryArgsCounter++;

				}

				// ic = i counter
				for ($ic = 0;$ic<$queryArgsCounter;$ic++){
					$queryArgsArray[] =$queryAddArgsArray[$ic];
				}

				$querySETaddRowAddCount = substr($querySETaddRowAddCount, 0, -2);
				$querySETaddRowAddCount .= ")";

				$querySETrowAddCount .= $querySETaddRowAddCount;

				$wpdb->query($wpdb->prepare($querySETrowAddCount,$queryArgsArray));

			}
		}

	}
}

// Insert fields content
include('1_content.php');

// Insert fields content fb like
include('1_content-fb-like.php');

// Insert fields content --- END

// 	Bilder daktivieren
include('2_deactivate.php');

// Reinfolge Bilder ändern (old file 3_row-order.php', not used anymore and deleted)
//include('3_row-order.php');

// 	Bilder aktivieren
include('4_activate.php');

// !IMPORTANT: have to be done before 5_create-no-script-html
//include('5_set-image-array.php');

//do_action('cg_json_upload_form_info_data_files',$GalleryID,null);

include('5_create-no-script-html.php');

// Reset informierte Felder

// Reset informierte Felder ---- END

// Inform Users if picture is activated per Mail
include('7_inform.php');

// Move to another gallery selected images to move
//include('8_move-to-another-gallery.php');

// Inform Users if picture is activated per Mail --- END

//echo "<p id='cg_changes_saved' style='font-size:18px;'><strong>Changes saved</strong></p>";



?>