<?php

if(!defined('ABSPATH')){exit;}

if(!isset($_POST['contest_gal1ery_post_create_data_csv'])){
	$_POST['contest_gal1ery_post_create_data_csv'] = false;
}

if(!isset($_POST['chooseAction1'])){
	$_POST['chooseAction1'] = false;
}

if(!isset($_POST['informId'])){
	$_POST['informId'] = false;
}

if(!isset($_POST['resetInformId'])){
	$_POST['resetInformId'] = false;
}

if(!isset($_POST['contest_gal1ery_create_zip'])){
	$_POST['contest_gal1ery_create_zip'] = false;
}

if(!empty($_GET['option_id'])){
	$GalleryID = absint($_GET['option_id']);
}else{

	if(empty($_POST['cg_id'])){
		$isNewGallery = true;
		// dann hat er reloaded und einfach die letzte gallerie anzeigen
		$GalleryID = $wpdb->get_var("SELECT MAX(id) FROM $tablenameOptions");
	}else{
		$GalleryID = absint($_POST['cg_id']);
	}

}

global $wp_version;

//  CHECK FIRST!!!!
$wp_upload_dir = wp_upload_dir();

//  CHECK FIRST  ---- END !!!!
if(!empty($_POST['cgGalleryFormSubmit']) && !empty($_POST['cgIsRealFormSubmit'])){
	include('change-gallery/0_change-gallery.php');
}

if(!isset($_POST['cg_copy'])){
	$isCopyGalleryRightNow = false;
}else{
	$isCopyGalleryRightNow = true;
}

if(empty($isNewGalleryCreated)){
	$isNewGalleryCreated = false;
}

$cg_hide_is_new_gallery = ($isNewGalleryCreated) ? 'cg_hide' : '';


if(!empty($isGalleryAjaxBackendLoad)){// when from page load then load like without without ajax call for faster processing
	$isAjaxCall = false;
}

if(empty($isAjaxCall)){
	$isAjaxCall = false;
}


include("get-data.php");
echo '<input type="hidden"  id="cgGalleryID" value="'. $GalleryID .'">';
include(dirname(__FILE__) . "/../nav-gallery.php");
include("header-1.php");

if($isCopyGalleryRightNow){

	?>

    <script>

        var gid = <?php echo json_encode($GalleryID);?>;

        var reloadUrl = window.location.href;

        if (reloadUrl.indexOf("cg_copy") >= 0){
            reloadUrl = reloadUrl.replace(/cg_copy/gi,'cg_do_nothing');
            reloadUrl = reloadUrl.replace('index.php&','index.php&option_id='+gid+'&');// <<< do not remove this!!!
        }

        history.replaceState(null,null,reloadUrl);



    </script>
	<?php
}


if($IsModernFiveStar==0 && (($AllowRating>=12 && $AllowRating<=20))){

	echo '<div style="border: thin solid black;background-color:#ffffff;    
    padding-top: 17px;
    margin-top: 20px;margin-bottom: 20px;padding-bottom: 15px;">
    <div style="text-align: center;font-size: 14px; width: 100%; font-weight: bold;margin-bottom: 10px;">You are using old 5 stars gallery frontend look. You can correct it here:</div>
    <div style="margin: 0 auto;width:230px;"><a href="?page='.cg_get_version().'/index.php&amp;corrections_and_improvements=true&amp;option_id='.$GalleryID.'" class="cg_load_backend_link"><input type="hidden" name="option_id" value="5"><input class="cg_backend_button cg_backend_button_general" type="button" value="Corrections and Improvements" style="width:230px;"></a><br></div></div>';

}

include("header-2.php");


if($isAjaxCall){


// Set variables:
	$heightOriginalImg = 1;
	$widthOriginalImg = 1;


// Bestimmen ob ABSTEIGEND oder AUFSTEIGEND

// -------------------------------Ausgabe der eingetragenen Felder. Hauptdiv id=sortable. Sortierbare Felder div id=cgSortableDiv

	echo '<input type="hidden" name="option_id"  value="'. $GalleryID .'">';
	//echo "<div id='sortable' style='width:935px;border: thin solid black;background-color:#fff;padding-bottom:50px;padding-left:20px;padding-right:20px;padding-top:20px;'>";
	echo "<input type='hidden' name='changeGalery' value='changeGalery'>";



	echo "<ul id='cgSortable'>";
	?>
    <script  data-cg-processing="true">
        cg_embed_post_titles = {};
        cg_embed_post_contents = {};
        cg_twitter_blockquotes = {};
        cg_tiktok_blockquotes = {};
    </script>
	<?php
	if(!empty($categories)){
		$cgUncheckedCategoriesMessageAboveGalleryHide = 'cg_hide';
		if($countCategories>=1 && $totalCountActiveImages==0 && $counterCheckedCategories != $countCategories+1 && count($selectSQL)){// +1 because of other
			$cgUncheckedCategoriesMessageAboveGalleryHide = '';
		}
		echo '<div id="cgUncheckedCategoriesMessageAboveGallery" style="" class="'.$cgUncheckedCategoriesMessageAboveGalleryHide.'"><strong>NOTE</strong><br>There are unchecked categories above. Unless there are no activated files for checked categories no files will be visible in frontend.</div>';
	}

	if(!empty($_POST['cgGalleryFormSubmit'])){
		//     echo "<p id='cg_changes_saved' style='font-size:18px;'><strong>Changes saved</strong></p>";
	}

	echo "<div id='cgNoImagesFound' class='cg_hide'>No entries found</div>";

	// Bei der ersten Abarbeitung notwendig
	//	echo "<li style='width:891px;border: thin solid black;padding-top:10px;padding-bottom:10px;display:table;' id='div' class='cgSortableDiv'>";
// Wird gebraucht um die höchste RowID am Anfang zu ermitln
	$r = 0;

	$uploadFolder = wp_upload_dir();

	$addedEcommerceImageForDownload = [];

	$domain = get_bloginfo('wpurl');
	$CgEntriesOwnSlugName = cg_get_gallery_slug_name();

	$Version = intval($optionsSQL->Version);

	foreach($selectSQL as $value){

		$id = $value->id;
		$realId = $id;
		$rowid = $value->rowid;
		$ImgType = $value->ImgType;
		$ImgTypeToShow = $ImgType;
		$Timestamp = $value->Timestamp;
		$NamePic = $value->NamePic;
		$CountC = $value->CountC;
		$comments = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryNR.'/json/image-comments/ids/'.$id.'/*.json');
		$CountC = count($comments);
		$CountCtoReview = $value->CountCtoReview;
		$rating = $value->Rating;
		$countR = $value->CountR;
		$countS = $value->CountS;
		$WpUpload = $value->WpUpload;
		$WpUploadToShow = $WpUpload;
		$WpUserId = $value->WpUserId;
		$Winner = $value->Winner;
		$widthOriginalImg = $value->Width;
		$heightOriginalImg = $value->Height;
		$Active = $value->Active;
		$rThumb = (empty($value->rThumb)) ? 0 : $value->rThumb;
		$rThumbToShow = $rThumb;
		$rSource = $value->rSource;
		$addCountS = $value->addCountS;
		$addCountR1 = $value->addCountR1;
		$addCountR2 = $value->addCountR2;
		$addCountR3 = $value->addCountR3;
		$addCountR4 = $value->addCountR4;
		$addCountR5 = $value->addCountR5;
		$addCountR6 = $value->addCountR6;
		$addCountR7 = $value->addCountR7;
		$addCountR8 = $value->addCountR8;
		$addCountR9 = $value->addCountR9;
		$addCountR10 = $value->addCountR10;
		$imageCategory = $value->Category;
		$exifData = $value->Exif;
		$exifDataStringForInput = '';
		$IP = $value->IP;
		$CookieId = $value->CookieId;
		$Informed = $value->Informed;
		$OrderItem = $value->OrderItem;
		$WpPage = $value->WpPage;
		$WpPageUser = $value->WpPageUser;
		$WpPageNoVoting = $value->WpPageNoVoting;
		$WpPageWinner = $value->WpPageWinner;
		$WpPageEcommerce = $value->WpPageEcommerce;
		if($value->MultipleFiles=='""'){
			$value->MultipleFiles = '';
		}
		$SaleType = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['SaleType']  : '';
		$EcommerceEntry = $value->EcommerceEntry;
		$EcommerceEntryAndWpUploadIds = [];
		$addedEcommerceImageForDownload[$id] = [];
		if(!empty($EcommerceEntry) && cg_is_is_image($ImgType) && $SaleType=='download'){
			$addedEcommerceImageForDownload[$id][$WpUpload] = 'base64here';
		}
		$Price = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['Price'] : 0;
		$CurrencyShort = (!empty($value->EcommerceEntry) && (float)$optionsSQL->Version>=22) ? $ecommerce_options->CurrencyShort : 'USD';
		$CurrencySymbol = (!empty($value->EcommerceEntry) && (float)$optionsSQL->Version>=22) ? $currenciesArray[$CurrencyShort] : '$';
		$CurrencyPosition = (!empty($value->EcommerceEntry)) ? $ecommerce_options->CurrencyPosition : 0;
		$PriceDivider = (!empty($value->EcommerceEntry)) ? $ecommerce_options->PriceDivider  : '';
		$WatermarkSettings = (!empty($value->EcommerceEntry) && !empty($allEcommerceFilesByIdArray[$id]['WatermarkSettings'])) ? json_encode(unserialize($allEcommerceFilesByIdArray[$id]['WatermarkSettings']))  : '';
		$WpUploadFilesForSale = (!empty($value->EcommerceEntry) && !empty($allEcommerceFilesByIdArray[$id]['WpUploadFilesForSale'])) ? json_encode(unserialize($allEcommerceFilesByIdArray[$id]['WpUploadFilesForSale']))  : '';
		$WpUploadFilesForSaleArray = (!empty($value->EcommerceEntry) && !empty($allEcommerceFilesByIdArray[$id]['WpUploadFilesForSale'])) ? unserialize($allEcommerceFilesByIdArray[$id]['WpUploadFilesForSale'])  : [];

		$UploadGallery = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['UploadGallery']  : '';
		$MaxUploads = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['MaxUploads']  : '';
		$SaleTitle = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['SaleTitle']  : '';
		$AllUploadsUsedText = (!empty($value->EcommerceEntry)) ? contest_gal1ery_convert_for_html_output_without_nl2br($allEcommerceFilesByIdArray[$id]['AllUploadsUsedText'])  : '';
		$SaleDescription = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['SaleDescription']  : '';
		$SaleAmountMin = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['SaleAmountMin']  : '';
		$SaleAmountMax = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['SaleAmountMax']  : 10;
		$HasTax = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['HasTax']  : 0;
		$IsShipping = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['IsShipping']  : 0;
		$IsAlternativeShipping = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['IsAlternativeShipping']  : 0;
		$DownloadKeysCsvName = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['DownloadKeysCsvName']  : 0;
		$ServiceKeysCsvName = (!empty($value->EcommerceEntry)) ? $allEcommerceFilesByIdArray[$id]['ServiceKeysCsvName']  : 0;
		$Tax = (!empty($value->EcommerceEntry)) ? strval($allEcommerceFilesByIdArray[$id]['TaxPercentage'])  : '';// not required to floatval value
		$AlternativeShipping = (!empty($value->EcommerceEntry)) ? strval($allEcommerceFilesByIdArray[$id]['AlternativeShipping'])  : '';// not required to floatval value
		if(empty($Tax)){
			$Tax = 0;
		}

		if(empty($AlternativeShipping)){
			$AlternativeShipping = 0;
		}

		$getEntriesMail = '';
		if(!empty($WpUserId) AND !empty($allWpUsersByIdArray[$WpUserId])){
			$getEntriesMail = $allWpUsersByIdArray[$WpUserId]['user_email'];
		}

		$anotherFirstMultipleFile = false;
		$MultipleFiles = [];
		$MultipleFilesString = '';
		$NamePicToShow = '';

		if($ImgType=='con'){
			$hideWpFileInfoToInsert = 'cg_hide';
			$image_url = '';
			$image_url_to_show = '';
			$post_title = '';
			$post_title_to_show = '';
			$post_name = '';
			$post_name_to_show = '';
			$post_description = '';
			$post_description_to_show = '';
			$post_excerpt = '';
			$post_mime_type = '';
			$post_mime_type_fist_part = '';
		}else{
			$hideWpFileInfoToInsert = '';
			if(empty($allWpPostsByWpUploadIdArray[$WpUpload])){
				$image_url = '';
				$image_url_to_show = '';
				$post_title = '';
				$post_title_to_show = '';
				$post_name = '';
				$post_name_to_show = '';
				$post_description = '';
				$post_description_to_show = '';
				$post_excerpt = '';
				$post_mime_type = '';
				$post_mime_type_fist_part = '';
			}else{
				$image_url = $allWpPostsByWpUploadIdArray[$WpUpload]['guid'];
				$image_url_to_show = $image_url;
				$post_title = $allWpPostsByWpUploadIdArray[$WpUpload]['post_title'];
				$post_title_to_show = $allWpPostsByWpUploadIdArray[$WpUpload]['post_title'];
				$post_name = $allWpPostsByWpUploadIdArray[$WpUpload]['post_name'];
				$post_name_to_show = $allWpPostsByWpUploadIdArray[$WpUpload]['post_name'];
				$post_description = $allWpPostsByWpUploadIdArray[$WpUpload]['post_content'];
				$post_description_to_show = $allWpPostsByWpUploadIdArray[$WpUpload]['post_content'];
				$post_excerpt = $allWpPostsByWpUploadIdArray[$WpUpload]['post_excerpt'];
				$post_mime_type = $allWpPostsByWpUploadIdArray[$WpUpload]['post_mime_type'];
				$post_mime_type_fist_part = substr($post_mime_type,0,strrpos($post_mime_type,'/'));
			}

		}

		if(function_exists('exif_read_data')){
			if(!empty($exifData)){
                if(strlen($exifData)>10){
	                $exifData = unserialize($exifData);
                }else{// otherwise some sort of corrupt string
	                $exifData = '';
                }
				if(!empty($exifData)){
					$exifDataStringForInput = json_encode($exifData);
				}
			}else{
				$exifData = false;
			}
		}

		$isEcommerceEntryAndDownload = 0;
		$isFirstFileIsEcommerceDownload = 0;

		if(!empty($EcommerceEntry) && $SaleType=='download'){
			$isEcommerceEntryAndDownload = 1;
		}

		if(!empty($value->MultipleFiles) && $value->MultipleFiles!='""'){
			$MultipleFilesUnserialized = unserialize($value->MultipleFiles);
			if(!empty($MultipleFilesUnserialized)){//check for sure if really exists and unserialize went right, because might happen that "" was in database from earlier versions
				$MultipleFiles = $MultipleFilesUnserialized;
				foreach ($MultipleFiles as $MultipleFileKey => $MultipleFile){
					if(empty($allWpPostsByWpUploadIdArray[$MultipleFile['WpUpload']])){
						$MultipleFiles[$MultipleFileKey]['WpUploadRemoved'] = true;
					}
				}

				foreach ($MultipleFiles as $MultipleFilesOrder => $MultipleFileArray){
					//$MultipleFiles[$MultipleFilesOrder]['isEcommerceEntryAndDownload'] = $isEcommerceEntryAndDownload;
					//$MultipleFiles[$MultipleFilesOrder]['EcommerceEntry'] = $EcommerceEntry;
					if(!empty($EcommerceEntry)){
						if(!empty($MultipleFiles[$MultipleFilesOrder]['isRealIdSource'])){
							$EcommerceEntryAndWpUploadIds[] = $WpUpload;
						}else{
							$EcommerceEntryAndWpUploadIds[] = $MultipleFiles[$MultipleFilesOrder]['WpUpload'];
						}
					}
				}
				//if(!empty($EcommerceEntryAndWpUploadIds)){
					//$MultipleFilesString = json_encode($EcommerceEntryAndWpUploadIds);
				//}

			}
			// then data of first image has to be displayed
			foreach ($MultipleFiles as $MultipleFilesOrder => $MultipleFileArray){
				if($MultipleFilesOrder == 1 && !empty($MultipleFileArray['isRealIdSource'])){
					break;
				}else if($MultipleFilesOrder == 1){
					$anotherFirstMultipleFile = true;
					break;
				}
			}

			foreach ($MultipleFiles as $MultipleFilesOrder => $MultipleFileArray){
				if(!empty($MultipleFileArray['isRealIdSource'])){
					if(!empty($EcommerceEntry) && cg_is_is_image($ImgType) && $SaleType=='download' && in_array($WpUpload,$WpUploadFilesForSaleArray)!==false){
						$addedEcommerceImageForDownload[$id][$WpUpload] = 'base64here';
					}
					if(!empty($EcommerceEntry) && $SaleType=='download' && in_array($WpUpload,$WpUploadFilesForSaleArray)!==false){
						if($MultipleFilesOrder==1){
							$isFirstFileIsEcommerceDownload = 1;
						}
					}
				}else{
					if(!empty($EcommerceEntry) && cg_is_is_image($MultipleFileArray['ImgType']) && $SaleType=='download'  && in_array($MultipleFileArray['WpUpload'],$WpUploadFilesForSaleArray)!==false){
						$addedEcommerceImageForDownload[$id][$MultipleFileArray['WpUpload']] = 'base64here';
					}
					if(!empty($EcommerceEntry) && $SaleType=='download' && in_array($WpUpload,$WpUploadFilesForSaleArray)!==false){
						if($MultipleFilesOrder==1){
							$isFirstFileIsEcommerceDownload = 1;
						}
					}
				}
			}

		}else{

			$isFirstFileIsEcommerceDownload = 0;

			if(!empty($EcommerceEntry) && $SaleType=='download'){
				$isFirstFileIsEcommerceDownload = 1;
			}

		}

		if(!empty($anotherFirstMultipleFile)){
			$exifData = false;

			//  $WpUpload = $MultipleFiles[1]['WpUpload'];

			$WpUploadToShow = $MultipleFiles[1]['WpUpload'];
			$post_name_to_show = $allWpPostsByWpUploadIdArray[$WpUploadToShow]['post_name'];
			$post_title_to_show = $allWpPostsByWpUploadIdArray[$WpUploadToShow]['post_title'];
			$post_description_to_show = $allWpPostsByWpUploadIdArray[$WpUploadToShow]['post_content'];
			/*            var_dump('$WpUploadToShow');
						var_dump($WpUploadToShow);*/
			$image_url_to_show = $MultipleFiles[1]['guid'];
			$ImgTypeToShow = $MultipleFiles[1]['ImgType'];
			$NamePicToShow = $MultipleFiles[1]['NamePic'];
			$rThumbToShow = $MultipleFiles[1]['rThumb'];
			/*            $post_title = $MultipleFiles[1]['post_title'];
						$post_name = $MultipleFiles[1]['post_name'];
						$post_description = $MultipleFiles[1]['post_content'];
						$post_excerpt = $MultipleFiles[1]['post_excerpt'];
						$post_mime_type = $MultipleFiles[1]['post_mime_type'];
						$post_mime_type_fist_part = substr($post_mime_type,0,strrpos($post_mime_type,'/'));
						$NamePic = $MultipleFiles[1]['NamePic'];
						$Width = $MultipleFiles[1]['Width'];
						$Height = $MultipleFiles[1]['Height'];
						$rThumb = $MultipleFiles[1]['rThumb'];
						$exifData = $MultipleFiles[1]['Exif'];*/

			if(function_exists('exif_read_data')){
				if(!empty($exifData)){
					$exifDataStringForInput = json_encode($exifData);
				}else{
					$exifData = false;
				}
			}else{
				$exifData = false;
			}

		}
		$emailStatus = false;

		if(!$rating){$rating=0;}

		if($Active == 1){
			$cg_sortable_div_status = 'cg_sortable_div_active';
		}else{
			$cg_sortable_div_status = 'cg_sortable_div_inactive';
		}

		if($EcommerceEntry>=1){
			$cg_is_for_sale = 'cg_is_for_sale';
			$cg_ecom_not_ecom = 'cg_ecom';
		}else{
			$cg_is_for_sale = '';
			$cg_ecom_not_ecom = 'cg_not_ecom';
		}

		echo "<li id='div$id' data-cg-real-id='$id' class='cgSortableDiv cg_sortable_div $cg_is_for_sale $cg_sortable_div_status'>";

        /*echo "<pre>";
            print_r($MultipleFiles);
        echo "</pre>";*/

		foreach($MultipleFiles as $order => $MultipleFile){
			if(!empty($MultipleFile['isRealIdSource'])){
				$MultipleFile['WpUpload'] = $WpUpload;
				$MultipleFile['ImgType'] = $ImgType;
			}
			if(!empty($MultipleFile['isRealIdSource']) && ($MultipleFile['ImgType']=='twt' || $MultipleFile['ImgType']=='ytb' || $MultipleFile['ImgType']=='tkt' ||  $MultipleFile['ImgType']=='inst')){
				$MultipleFile['post_content'] = $post_description;
			}
			if(!empty($MultipleFile['ImgType']) && ($MultipleFile['ImgType']=='twt' || $MultipleFile['ImgType']=='ytb' || $MultipleFile['ImgType']=='tkt' ||  $MultipleFile['ImgType']=='inst')){

                /*echo "<pre>";
                    print_r($MultipleFile);
                echo "</pre>";*/

				$WpUploadMultipleFile = $MultipleFile['WpUpload'];
				$post_content = '';
				$post_title = '';
				if(isset($MultipleFiles[$order]['post_title'])){// check has to be done
					$post_title = $MultipleFile['post_title'];
					unset($MultipleFiles[$order]['post_title']);
				}
                if(isset($MultipleFiles[$order]['post_content'])){// check has to be done
	                $post_content = $MultipleFile['post_content'];
	                unset($MultipleFiles[$order]['post_content']);
                }
				$MultipleFiles[$order]['type'] = 'embed';// have to be set here
				?>
                <script  data-cg-processing="true" data-cg-processing-for-reload-entry="true">
                    var WpUpload = <?php echo json_encode($WpUploadMultipleFile); ?>;
                    var id = <?php echo json_encode($id); ?>;
                    if(!cg_embed_post_contents[id]){
                        cg_embed_post_contents[id] = {};
                    }
                    if(!cg_embed_post_contents[id][WpUpload]){
                        cg_embed_post_contents[id][WpUpload] = <?php echo json_encode($post_content); ?>;
                    }
                    if(!cg_embed_post_titles[id]){
                        cg_embed_post_titles[id] = {};
                    }
                    if(!cg_embed_post_titles[id][WpUpload]){
                        cg_embed_post_titles[id][WpUpload] = <?php echo json_encode($post_title); ?>;
                    }
                </script>
				<?php
			}
			if(!empty($MultipleFile['ImgType']) && $MultipleFile['ImgType']=='twt'){
				$blockquote = cg_get_blockquote_from_post_content($post_content);
				?>
                <script  data-cg-processing="true" data-cg-processing-for-reload-entry="true">
                    var id = <?php echo json_encode($id); ?>;
                    if(!cg_twitter_blockquotes[id]){
                        cg_twitter_blockquotes[id] = {};
                    }
                    var WpUpload = <?php echo json_encode($WpUploadMultipleFile); ?>;
                    if(!cg_twitter_blockquotes[id][WpUpload]){
                        cg_twitter_blockquotes[id][WpUpload] = <?php echo json_encode($blockquote); ?>;
                    }
                </script>
				<?php
			}if(!empty($MultipleFile['ImgType']) && $MultipleFile['ImgType']=='tkt'){
				$blockquote = cg_get_blockquote_from_post_content($post_content);
				?>
                <script  data-cg-processing="true" data-cg-processing-for-reload-entry="true">
                    var id = <?php echo json_encode($id); ?>;
                    if(!cg_tiktok_blockquotes[id]){
                        cg_tiktok_blockquotes[id] = {};
                    }
                    var WpUpload = <?php echo json_encode($WpUploadMultipleFile); ?>;
                    if(!cg_tiktok_blockquotes[id][WpUpload]){
                        cg_tiktok_blockquotes[id][WpUpload] = <?php echo json_encode($blockquote); ?>;
                    }
                </script>
				<?php
			}
		}

        if(count($MultipleFiles)){
            // to be done here after unset($MultipleFiles[$order]['post_content']); is done
	        $MultipleFilesString = json_encode($MultipleFiles);
        }

		echo "<div class='cg_drag_area' ><img class='cg_drag_area_icon' src='$cgDragIcon'></div>";

		// hidden inputs zur bestimmung der Reihenfolge
		echo "<input type='hidden' name='cg_row[$id]'  class='rowId cg_input_vars_count' disabled value='$rowid'>"; // Zur Feststellung der Reihenfolge, wird vom Javascript verarbeitet
		$ytbHintInfo = 'cg_hide';
		$ytbDisabled = '';
		$ytbHint = '';
		$ytbHintText = 'contact';
		if($ImgType!='con'){
			if($ImgType=='ytb' || $ImgType=='twt' || $ImgType=='inst' || $ImgType=='tkt'){
				$ytbHintInfo = '';
				//$ytbDisabled = 'cg_disabled_one';
				$socialTypeName = 'YouTube';
				if($ImgType=='twt'){
					$socialTypeName = 'Twitter';
				}else if($ImgType=='inst'){
					$socialTypeName = 'Instagram';
				}else if($ImgType=='tkt'){
					$socialTypeName = 'TikTok';
				}
			}
		}

		echo "<div class='cg_backend_status_container'>";

		if($Active == 1){
			$Status = 'cg_status_activated';
		}else{
			$Status = 'cg_status_deactivated';
		}

		echo "<div class='informdiv $Status $cg_is_for_sale' >";

		if($Active == 1){
			echo "<div class='cg_status_activated cg_status' ><div>ACTIVE</div></div>";
		}
		else{
			echo "<div class='cg_status_deactivated cg_status' ><div>INACTIVE</div></div>";
		}

		// Check if user should be informed or is informed

		$ActiveStatus = (empty($Active)) ? '' : 'cg_hide';
		$ActiveStatusCheckbox = (!empty($Active)) ? '' : 'disabled';
		$DeactivateStatus = (empty($Active)) ? 'cg_hide' : '';
		$DeactivateStatusCheckbox = (!empty($Active)) ? 'disabled' : '';
		$WinnerStatus = (!empty($Winner)) ? 'cg_status_winner_true' : 'cg_status_winner_false';
		$WinnerText = (!empty($Winner)) ? 'Not winner' : 'Winner';
		$WinnerName = (!empty($Winner)) ? 'cg_winner_not' : 'cg_winner';

		if($Active!=1){
			echo '<div><div class="cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_activate '.$ActiveStatus.'">
<div class="cg_image_checkbox_action" >Activate</div>
<div class="cg_image_checkbox_icon"></div>
<input type="hidden" class="cg_image_checkbox_checkbox cg_input_vars_count"  disabled name="cg_activate['.$id.']" value="'.$id.'">
</div></div>';
		}else{
			echo '<div><div class="cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_deactivate '.$DeactivateStatus.'">
<div class="cg_image_checkbox_action" >Deactivate</div>
<div class="cg_image_checkbox_icon"></div>
<input type="hidden" class="cg_image_checkbox_checkbox cg_input_vars_count" disabled name="cg_deactivate['.$id.']" value="'.$id.'">
</div></div>';
		}

		echo '<div><div class="cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_delete" style="margin-bottom: 40px;">
<div class="cg_image_checkbox_action">Delete</div>
<div class="cg_image_checkbox_icon" style="margin-left: 50px;"></div>
<input type="hidden" class="cg_image_checkbox_checkbox cg_input_vars_count cg_delete"  disabled name="cg_delete['.$id.']" value="'.$id.'">
</div></div>';

		echo '<div class="cg_status_winner_container"><div class="cg_hover_effect cg_image_checkbox cg_status_winner '.$WinnerStatus.'" style="margin-bottom: 0;">
<div class="cg_image_checkbox_action">'.$WinnerText.'</div>
<div class="cg_image_checkbox_icon" ></div>
<input type="hidden" class="cg_status_winner_checkbox cg_input_vars_count" name="'.$WinnerName.'['.$id.']" disabled value="'.$id.'">
</div><div style="padding-top:2px;position: relative;margin-bottom: 10px;text-align:center;"><span class="cg-info-icon" style="margin-left: -15px;">info</span>
    <span class="cg-info-container cg-info-container-gallery-user" style="top: 34px; margin-left: -122px; display: none;">Use cg_gallery_winner shortcode to display only winners</span>
    </div></div>';

		if((float)$optionsSQL->Version>=22 && $ImgTypeToShow!='con'){
			echo "<div><div class=\"cg_sell\" >";
			echo "<div class='cg_sell_button cg_sell_activate_button cg_sale_settings $ytbDisabled' data-cg-real-id='$id' >Sell settings$ytbHint</div>";
			echo '<div style="padding-top:2px;position: relative;margin-bottom: 10px;text-align:center;" class="'.$ytbHintInfo.'"><span class="cg-info-icon" >info</span>
    <span class="cg-info-container cg-info-container-gallery-user" style="top: 34px; margin-left: -132px; display: none;">cg_gallery_ecommerce and "Sell settings"<br>are not available for '.$ytbHintText.' entries</span>
    </div>';
			echo '</div></div>';
		}


		echo '</div>';

		echo "</div>";

        $ifInformedCss = 'margin-top: -15px;';
		if($Informed==1){
			$ifInformedCss = 'margin-top: -5px;';
			echo "<div style='width: 100%;padding-left: 20px;margin: -15px 0 10px;'><b>Informed about activated image</b></div>";
        }

		if(!empty($selectContentFieldArray)){
			foreach($selectContentFieldArray as $key => $formvalue){
				// 1. Feld Typ
				// 2. ID des Feldes in F_INPUT
				// 3. Feld Reihenfolge
				// 4. Feld Content
				if(!isset($fieldtype)){
					$fieldtype = '';
				}
				if($formvalue=='email-f'){$fieldtype="ef";  $i=1; continue;}
				if($fieldtype=="ef" AND $i==1){$formFieldId=$formvalue; $i=2; continue;}
				if($fieldtype=="ef" AND $i==2){$fieldOrder=$formvalue; $i=3; continue;}
				if ($fieldtype=='ef' AND $i==3) {

					//$getEntries = $wpdb->get_var( "SELECT Short_Text FROM $tablenameentries WHERE pid='$id' AND f_input_id = '$formFieldId'");

					$emailStatus = true;
					$emailStatusText = 'Not confirmed';

					if(!empty($WpUserId) AND !empty($allWpUsersByIdArray[$WpUserId])){// check for sure both, because user might be deleted
						//$getEntries = $wpUser->user_email;

						$emailStatusText = 'Confirmed (registered user)';
						$mailReadonly = "readonly";
						$registeredUserMail = "(registered user email)";
					}
					else{

						$getEntriesMail = html_entity_decode(stripslashes($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Content']));
						$getEntriesConfMailId = html_entity_decode(stripslashes($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['ConfMailId']));

						$mailReadonly = "readonly";
						$registeredUserMail = "";

						if(!empty($getEntriesMail)){
							if($getEntriesConfMailId>0){
								$emailStatusText = 'Confirmed (not registered user)';
							}
						}
					}
				}
			}
		}

		if($emailStatus==true){
			echo "<div style='width:100%;padding-left: 20px;".$ifInformedCss."margin-bottom: 10px;'>E-Mail Status: <strong>$emailStatusText</strong></div>";
		}

		// has to be done here so cgAddedEcommerceImageForDownload img tag appears
		if(!empty($addedEcommerceImageForDownload[$realId])){
			foreach ($addedEcommerceImageForDownload[$realId] as $WpUploadToCompare  => $base64hasToBeCreated){
				$WpUploadFilesPostMeta = $wpdb->get_var("SELECT WpUploadFilesPostMeta FROM $tablename_ecommerce_entries WHERE pid = '$realId'");
				if(!empty($WpUploadFilesPostMeta)){
					$WpUploadFilesPostMeta = unserialize($WpUploadFilesPostMeta);
				}
				if(!empty($WpUploadFilesPostMeta[$WpUploadToCompare])){
					$tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

					// #2 use collected data to move files to contest gallery folder
					$ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId;
					$ecommerceFileFolderWpUploadFolder = $ecommerceFileFolder.'/wp-upload-id-'.$WpUploadToCompare;
					if(!empty($WpUploadFilesPostMeta[$WpUploadToCompare]['_wp_attachment_metadata']['sizes']['large'])){
						$filename = $WpUploadFilesPostMeta[$WpUploadToCompare]['_wp_attachment_metadata']['sizes']['large']['file'];
					}else{
						$filename = substr($WpUploadFilesPostMeta[$WpUploadToCompare]['_wp_attached_file'],strrpos($WpUploadFilesPostMeta[$WpUploadToCompare]['_wp_attached_file'],'/')+1,strlen($WpUploadFilesPostMeta[$WpUploadToCompare]['_wp_attached_file']));
					}

					$source = $ecommerceFileFolderWpUploadFolder.'/'.$filename;

					// Read image path, convert to base64 encoding
					$imageData=trim(base64_encode(file_get_contents($source)));

					//var_dump('$source');
					//var_dump($source);

					//var_dump('mime_content_type($source)');
					//var_dump(mime_content_type($source));

					// Format the image SRC: data:{mime};base64,{data};
					$src = 'data:'.mime_content_type($source).';base64,'.$imageData;
					//$src =   $imageData;
					$addedEcommerceImageForDownload[$realId][$WpUploadToCompare] = $src;

					// has to be done this way, otherwise base64 src uncorrect for the watermark library
					echo '<img id="cgAddedEcommerceImageForDownload'.$realId.'_'.$WpUploadToCompare.'" src="' . $src . '" class="cg_hide">';
				}
			}
		}


        // hidden inputs zur bestimmung der Reihenfolge ENDE

		// ------ Bild wird mittig und passend zum Div angezeigt

		// destination of the uploaded original image

		$sourceOriginalImgShow = $image_url_to_show;

		$check = explode($uploadFolder['baseurl'],$image_url);
		$sourceOriginalImg = $image_url;

		if($rThumb=='90' or $rThumb=='270'){
			$rotateRatio = $widthOriginalImg/$heightOriginalImg;
			$widthOriginalImgContainer = $widthOriginalImg;
			$widthOriginalImg = $heightOriginalImg;
			$heightOriginalImg = $widthOriginalImgContainer;
		}

		$imgSrcLarge = '';
		$imgSrcMedium = '';
		$imgSrcFullWidth = 0;
		$imgSrcFullHeight = 0;
		$WidthThumb = 160;
		$HeightThumb = 106;
		$WidthThumbImageShouldBe = 160+100;// eventually user settings very low, this why this check and + 100 because to go sure quality is always good
		$imgSrcMediumRealIdSrc = '';
		$imgSrcLargeRealIdSrc = '';
		$imgSrcLargeToShow = '';
//var_dump($EcommerceEntry);
//var_dump($image_url_to_show);

		//if($ImgTypeToShow=='ytb' || $ImgTypeToShow=='twt' || $ImgTypeToShow=='inst' || $ImgTypeToShow=='tkt'){
		if($ImgType=='ytb' || $ImgType=='twt' || $ImgType=='inst' || $ImgType=='tkt'){
			$post_mime_type_fist_part = 'embed';
		}

		if(cg_is_is_image($ImgTypeToShow)){
			if(!empty($EcommerceEntry)){
				$imgSrcMedium=$image_url_to_show;
				$imgSrcMediumRealIdSrc = $image_url_to_show;
				$imgSrcLarge = $image_url_to_show;
				$imgSrcFull = $image_url_to_show;
				$imgSrcFullWidth = $value->Width;
				$imgSrcFullHeight = $value->Height;
				$imgSrcLargeToShow=$image_url_to_show;
				$imgSrcLargeRealIdSrc = $image_url_to_show;
			}else{
				$imgSrcMedium=wp_get_attachment_image_src($WpUploadToShow, 'medium');
				$imgSrcMedium=($imgSrcMedium) ? $imgSrcMedium[0] : '';
				$imgSrcMediumRealIdSrc = $imgSrcMedium;
				$imgSrcLarge = wp_get_attachment_image_src($WpUploadToShow, 'large');
				$imgSrcLarge=($imgSrcLarge) ? $imgSrcLarge[0] : '';
				$imgSrcFull = wp_get_attachment_image_src($WpUploadToShow, 'full');
				$imgSrcFullWidth = ($imgSrcFull) ? $imgSrcFull[1] : '';
				$imgSrcFullHeight = ($imgSrcFull) ? $imgSrcFull[2] : '';
				$imgSrcLargeToShow=$imgSrcLarge;
				$imgSrcLargeRealIdSrc = $imgSrcLarge;
			}
			if(!empty($anotherFirstMultipleFile) && cg_is_is_image($ImgTypeToShow)){
				if(!empty($EcommerceEntry)){
					$imgSrcLargeToShow=$image_url_to_show;
				}else{
					$imgSrcLargeToShow = wp_get_attachment_image_src($WpUploadToShow, 'large');
					$imgSrcLargeToShow=$imgSrcLargeToShow[0];
				}
			}
		}else if(cg_is_is_image($ImgType) && !empty($anotherFirstMultipleFile)){
			if(!empty($EcommerceEntry)){
				$imgSrcLarge = $image_url_to_show;
				$imgSrcLargeToShow=$image_url_to_show;
			}else{
				$imgSrcLarge = wp_get_attachment_image_src($WpUpload, 'large');
				$imgSrcLarge=$imgSrcLarge[0];
			}
		}
		// ----------- Ermitteln der Sprache des Blogs, um das Upload Datum in richtiger schreibweise anzuzeigen

		//$uploadTime = date('d-M-Y H:i', $value->Timestamp);
		$uploadTime = cg_get_time_based_on_wp_timezone_conf($value->Timestamp,'d-M-Y H:i:s');

		// Ermitteln der Sprache des Blogs, um das Upload Datum in richtiger schreibweise anzuzeigen  ------------  ENDE

		$fileHeight = (!empty($heightOriginalImg)) ? $heightOriginalImg : '';
		$fileWidth = (!empty($widthOriginalImg)) ? $widthOriginalImg : '';
		$cg_ecom_video = '';

		if(!empty($allWpPostsByWpUploadIdArray[$WpUpload])){
			if(cg_is_alternative_file_type_video($ImgType)){
				$fileData = wp_read_video_metadata(get_attached_file($WpUpload));
				if($EcommerceEntry){
					$fileHeight = 0;
					$fileWidth = 0;
					$cg_ecom_video = 'cg_ecom_video';
				}else{
					$fileHeight = (!empty($fileData['height'])) ? $fileData['height'] : 0;
					$fileWidth = (!empty($fileData['width'])) ? $fileData['width'] : 0;
				}
			}

			if(!empty($anotherFirstMultipleFile) && cg_is_is_image($ImgType)){
				if(!empty($EcommerceEntry)){
					$imgSrcMediumRealIdSrc=$image_url;
					$imgSrcLargeRealIdSrc=$image_url;
				}else{
					$imgSrcMediumRealIdSrc=wp_get_attachment_image_src( $WpUpload, 'medium');
					$imgSrcMediumRealIdSrc=$imgSrcMediumRealIdSrc[0];
					$imgSrcLargeRealIdSrc = wp_get_attachment_image_src( $WpUpload, 'large');
					$imgSrcLargeRealIdSrc=$imgSrcLargeRealIdSrc[0];
				}
			}
		}

		echo "<div class='cg_backend_info_container $cg_ecom_not_ecom' data-cg-real-id='$id' data-cg-post_name='$post_name'  data-cg-post_title='$post_title' 
             data-cg-post_content='$post_description'  data-cg-post_excerpt='$post_excerpt'   data-cg-post_mime_type='$post_mime_type'  
             data-cg-original-source='$image_url' data-cg-type-short='$ImgType'  data-cg-type='$post_mime_type_fist_part'
             data-cg-url-image-large='$imgSrcLargeRealIdSrc'  data-cg-url-image-medium='$imgSrcMediumRealIdSrc'  
             data-cg-file-height='$fileHeight' data-cg-file-width='$fileWidth'  data-cg-exif='$exifDataStringForInput' data-cg-wp-upload='$WpUpload' 
            >";

        if(!empty($OrderItem)){
            $OrderId = $wpdb->get_var("SELECT ParentOrder FROM $tablename_ecommerce_orders_items WHERE id = $OrderItem");
            $PayerEmail = $wpdb->get_var("SELECT PayerEmail FROM $tablename_ecommerce_orders WHERE id = $OrderId");
	        echo '<a target="_blank" href="?page='.cg_get_version().'/index.php&option_id='.$GalleryID.'&cg_show_order=true&cg_order_id='.$OrderId.'">';
	            echo "<div class='cg_order_item'>Upload order by<br><span  class='cg_order_item_span'>$PayerEmail</span></div>";
            echo '</a>';
        }

		// Add additional files released in v18 and available for all galleries copied or created since v17
		if((float)$optionsSQL->Version>=17){
			echo "<div class='cg_add_multiple_files_to_post_text cg_hide_override'>Add files</div>";
			echo "<div class='cg_hover_effect cg_add_multiple_files_to_post_prev cg_hide_override cg_visibility_hidden ".(($ImgTypeToShow=='con' || (cg_get_version()=='contest-gallery' && count($MultipleFiles)>=10) || (cg_get_version()=='contest-gallery-pro' && count($MultipleFiles)>=10)) ? 'cg_hide' : '')."' title='Add additional files to this entry'></div>";
			echo "<div class='cg_hover_effect cg_add_multiple_files_to_post cg_hide_override ".(($ImgTypeToShow=='con' || (cg_get_version()=='contest-gallery' && count($MultipleFiles)>=10) || (cg_get_version()=='contest-gallery-pro' && count($MultipleFiles)>=10)) ? 'cg_hide' : '')."' title='Add additional files to this entry'></div>";
			echo "<div data-cg-rThumb='$rThumb' class='cg_hover_effect cg_rotate_image_backend ".(((cg_is_is_image($ImgTypeToShow))) ? '' : 'cg_hide')."' title='Rotate image'></div>";
		}
		echo "<input type='hidden' class='cg_disabled_send cg_multiple_files_for_post cg_input_vars_count' name='cg_multiple_files_for_post[$id]' value='$MultipleFilesString' >";
		echo "<input type='hidden' class='cg_disabled_send cg_input_vars_count cg_rThumb' name='cg_rThumb[$id]' value='$rThumb' >";

		if($ImgType!='con'){
			if($ImgType=='ytb' || $ImgType=='twt' || $ImgType=='inst' || $ImgType=='tkt'){
				$ytbHintText = $ImgType;
                //$ytbHint = '<br><span class="cg_ytb_hint">Not available for '.$socialTypeName.' type entry so far</span>';
			}
			echo "<div class='cg_manage_multiple_files $ytbDisabled' >Add, manage, replace<br>file/s$ytbHint or social embed</div>";
		}

		// Add additional files released in v18 and available for all galleries copied or created since v17
		if((float)$optionsSQL->Version>=17){
			if(!empty($MultipleFiles)){
				$MultipleFilesCount = count($MultipleFiles)-1;
				echo "<div class='cg_hover_effect cg_manage_multiple_files_for_post_prev' title='Manage additional files of this entry'></div>";
				echo "<div class='cg_hover_effect cg_manage_multiple_files_for_post' title='Manage additional files of this entry'>+$MultipleFilesCount</div>";
			}else{
				echo "<div class='cg_hover_effect cg_manage_multiple_files_for_post_prev cg_hide' title='Manage additional files of this entry'></div>";
				echo "<div class='cg_hover_effect cg_manage_multiple_files_for_post cg_hide' title='Manage additional files of this entry'></div>";
			}
		}

		$WinnerStatus = (!empty($Winner)) ? 'cg_status_winner_true' : 'cg_status_winner_false';
		$WinnerStatusCheckbox = (!empty($Winner)) ? '' : 'disabled';

		echo "<div class='$WinnerStatus cg_status_winner_visual'><div>WINNER</div></div>";

		if($WpUpload>=1){
			$checkCookieIdOrIPMarginLeft = '';
			if($pro_options->RegUserUploadOnly=='2'){
				$checkCookieIdOrIP = ", Cookie ID";
				$checkCookieIdOrIPMarginLeft = 'margin-top: -81px !important;';
			}else if($pro_options->RegUserUploadOnly=='3'){
				$checkCookieIdOrIP = ", IP";
			}

			echo '<span class="cg-info-container cg-info-container-gallery-user" style="display: none;margin-left:-31px !important;'.$checkCookieIdOrIPMarginLeft.'">Searched value is in one of the following fields: file name, exif data, entry id, user email, username'.$checkCookieIdOrIP.'<br><br><b>NOTE:</b> searching in frontend always search for nickname... not username</span>';

			echo "<input type='hidden' class='cg_wp_upload_id' value='$WpUpload'>";
			echo "<input type='hidden' class='cg_real_id' value='$id'>";
			echo "<input type='hidden' class='cg_table_id' value='$id'>";
			echo "<input type='hidden' class='cg_type' value='$post_mime_type_fist_part'>";
			echo "<input type='hidden' class='cg_image_type_to_show' value='$ImgTypeToShow'>";
			echo "<input type='hidden' class='cg_image_src_large_to_show' value='$imgSrcLargeToShow'>";

			echo "<input type='hidden' class='cg_ecommerce_entry' value='$EcommerceEntry'>";

			echo "<input type='hidden' class='IsImageType' value='".(cg_is_is_image($ImgType) ? '1' : '0')."'>";

			echo "<input type='hidden' class='cg_wp_post_title' value='$post_title_to_show'>";
			echo "<input type='hidden' class='cg_wp_post_name' value='$post_name_to_show'>";
			echo "<input type='hidden' class='cg_wp_post_content' value='$post_description_to_show'>";
			if($WpUserId>=1 AND !empty($allWpUsersByIdArray[$WpUserId])){
				echo "<input type='hidden' class='cg_wp_user_login' value='".$allWpUsersByIdArray[$WpUserId]['user_login']."'>";
				echo "<input type='hidden' class='cg_wp_user_nicename' value='".$allWpUsersByIdArray[$WpUserId]['user_nicename']."'>";
				echo "<input type='hidden' class='cg_wp_user_email' value='".$allWpUsersByIdArray[$WpUserId]['user_email']."'>";
				echo "<input type='hidden' class='cg_wp_user_display_name' value='".$allWpUsersByIdArray[$WpUserId]['display_name']."'>";
			}
			echo "<input type='hidden' class='cg_image_id' value='$id'>";
		}

		// Add additional files released in v18 and available for all galleries copied or created since v17
		if((float)$optionsSQL->Version<17){
			if($ImgType=='jpg' OR $ImgType=='jpeg' OR $ImgType=='png' OR $ImgType=='gif' OR $ImgType=='ico'){
				echo "<div class='cg_backend_rotate_image'>";
				echo "Deprecated.... copy this gallery to use modern rotate options method";
				/*echo "<a class='cg_image_action_href cg_load_backend_link' href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&cg_image_rotate=true&cg_image_id=$id&cg_image_wp_id=$WpUpload\"><span class='cg_image_action_span'>Rotate Image</span></a>";*/
				echo "</div>";
			}else{
				echo "<div style='padding:0;margin-top: 10px;'></div>";
			}
		}

		if($EcommerceEntry>0){
			$currencyChar = $currenciesArray[$CurrencyShort];

			$priceStringToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$Price);

			$SaleString = '';
			if($SaleType=='download'){
				$SaleString = 'Download';
			}
			if($SaleType=='shipping'){
				$SaleString = 'Shipping';
			}
			if($SaleType=='service'){
				$SaleString = 'Service';
			}
			if($SaleType=='upload'){
				$SaleString = 'Upload';
			}

			$ShippingCostsString = '';
			$ShippingPriceStringToShowContainer = '';
			if($IsShipping){
				$ShippingCostsString = 'Shipping';
				if($IsAlternativeShipping){
					$AlternativeShippingForShipping = $AlternativeShipping;
					if($PriceDivider==','){
						$AlternativeShippingForShipping = str_replace(strval($AlternativeShipping),'.',',');
					}
					$shippingPriceStringToShow = $currencyChar.$AlternativeShippingForShipping;
					if($CurrencyPosition=='right'){
						$shippingPriceStringToShow = $AlternativeShippingForShipping.$currencyChar;
					}
					$ShippingPriceStringToShowContainer .= "<br><span class='cg_sale_shipping' >Extra shipping ($shippingPriceStringToShow)</span>";
				}else{
					if($PriceDivider==','){
						$ShippingOptionValue = str_replace($ShippingOptionValue,'.',',');
					}
					$shippingPriceStringToShow = $currencyChar.$ShippingOptionValue;
					if($CurrencyPosition=='right'){
						$shippingPriceStringToShow = strval($ShippingOptionValue).$currencyChar;
					}
					$ShippingPriceStringToShowContainer .= "<br><span class='cg_sale_shipping' >Default shipping ($shippingPriceStringToShow)</span>";
				}
			}else{
				$ShippingPriceStringToShowContainer .= "<br><span class='cg_sale_shipping cg_hide' ></span>";
			}
			$ShippingPriceStringToShowContainer = ''; // so far do not show
			echo '<div class="cg_for_sale_price_container"><span class="cg_for_sale_price_string">'.$SaleString.'</span><br><span class="cg_for_sale_price">'.$priceStringToShow.'</span>'.$ShippingPriceStringToShowContainer.'</div>';

		}else{
			$SaleString = 'Sale download or shipping';
			echo '<div class="cg_for_sale_price_container cg_hide"><span class="cg_for_sale_price_string">'.$SaleString.'</span><br><span class="cg_for_sale_price"></span></div>';
		}

		if(isset($saleItemsCountArray[$id])){
			echo '<a target="_blank" href="?page='.cg_get_version().'/index.php&option_id='.$GalleryID.'&cg_orders=true&cg_item_ids='.$id.'&cg_go_to=cgSales">'.
			     '<div class="cg_for_sale_price_container cg_go_to_sales" style="top:125px;"><span class="cg_for_sale_price_string">Sales</span><br><span class="cg_for_sale_price">'.$saleItemsCountArray[$id].'</span></div>'.
			     '</a>';
		}

		if($EcommerceEntry){
			$EcommerceEntryAndWpUploadIdsJson = json_encode($EcommerceEntryAndWpUploadIds);
			echo "<input type='hidden' disabled class='ecommerceEntryAndDownload' name='ecommerceEntryAndDownload[$id][$EcommerceEntry]'  value='$EcommerceEntryAndWpUploadIdsJson'>";
			echo "<input type='hidden' disabled class='removeEcommerceEntryWpUploadIds' name='removeEcommerceEntryWpUploadIds[$id][$EcommerceEntry]'  value=''>";
		}

		echo "<input type='hidden' class='isEcommerceEntryAndDownload' value='$isEcommerceEntryAndDownload'>";
		echo "<input type='hidden' class='Price' value='$Price'>";
		echo "<input type='hidden' class='CurrencyShort' value='$CurrencyShort'>";
		echo "<input type='hidden' class='CurrencySymbol' value='$CurrencySymbol'>";
		echo "<input type='hidden' class='CurrencyPosition' value='$CurrencyPosition'>";
		echo "<input type='hidden' class='PriceDivider' value='$PriceDivider'>";
		echo "<input type='hidden' class='WatermarkSettings' value='$WatermarkSettings'>";
		echo "<input type='hidden' class='WpUploadFilesForSale' value='$WpUploadFilesForSale'>";
		echo "<input type='hidden' class='SaleTitle' value='$SaleTitle'>";
		echo "<input type='hidden' class='AllUploadsUsedText' value='$AllUploadsUsedText'>";
		echo "<input type='hidden' class='SaleDescription' value='$SaleDescription'>";
		echo "<input type='hidden' class='SaleType' value='$SaleType'>";
		echo "<input type='hidden' class='SaleAmountMin' value='$SaleAmountMin'>";
		echo "<input type='hidden' class='SaleAmountMax' value='$SaleAmountMax'>";
		echo "<input type='hidden' class='UploadGallery' value='$UploadGallery'>";
		echo "<input type='hidden' class='MaxUploads' value='$MaxUploads'>";
		echo "<input type='hidden' class='HasTax' value='$HasTax'>";
		echo "<input type='hidden' class='Tax' value='$Tax'>";
		echo "<input type='hidden' class='AlternativeShipping' value='$AlternativeShipping'>";
		echo "<input type='hidden' class='IsShipping' value='$IsShipping'>";
		echo "<input type='hidden' class='IsAlternativeShipping' value='$IsAlternativeShipping'>";
		echo "<input type='hidden' class='imgSrcFullWidth' value='$imgSrcFullWidth'>";
		echo "<input type='hidden' class='imgSrcFullHeight' value='$imgSrcFullHeight'>";
		echo '<div class="cg_backend_image_full_size_target '.$cg_ecom_video.'" data-file-type="'.$ImgTypeToShow.'"  data-name-pic="'.$NamePicToShow.'" data-original-src="'.$sourceOriginalImgShow.'" >';

		if(empty($allWpPostsByWpUploadIdArray[$WpUpload]) && $ImgTypeToShow!='con'){
			echo '<div class="cg_backend_image_full_size_target_empty" >';
			echo "</div>";
		}else if(cg_is_alternative_file_type_file($ImgTypeToShow)){
			echo '<a href="'.$sourceOriginalImgShow.'" target="_blank" title="Show file" alt="Show file">';
			echo '<div class="cg_backend_image_full_size_target_'.$ImgTypeToShow.' cg_backend_image_full_size_target_alternative_file_type" data-cg-file-type="'.$ImgTypeToShow.'"><span class="cg_post_title">'.$post_title_to_show.'</span></div>';
			echo '</a>';
		}else if(cg_is_alternative_file_type_video($ImgTypeToShow)){
			if($isFirstFileIsEcommerceDownload){
				echo '<div class="cg_backend_image_full_size_target_'.$ImgTypeToShow.'  cg_backend_image_full_size_target_child_video " data-cg-file-type="'.$ImgTypeToShow.'"><span class="cg_post_title">'.$post_title_to_show.'</span></div>';
			}else{
				echo '<a href="'.$sourceOriginalImgShow.'" target="_blank" title="Show file" alt="Show file">';
				echo '<video width="292.5" height="220"  >';
				echo '<source src="'.$sourceOriginalImgShow.'?time='.time().'" type="video/mp4">';
				echo '<source src="'.$sourceOriginalImgShow.'?time='.time().'" type="video/'.$ImgTypeToShow.'">';
				echo '</video>';
				echo '</a><span class="cg_post_title cg_video">'.$post_title_to_show.'</span>';
			}
		}else if($ImgTypeToShow=='con'){
			echo '<div class="cg_backend_image cg_backend_image_con_entry"><span>Contact form entry<br>Entry ID: '.$id.'</span></div>';
		}else if($ImgTypeToShow=='ytb'){
			echo '<div class="cg_backend_image"><iframe  width="294" height="221" src="'.$image_url_to_show.'"  ></iframe></div>';
		}else if($ImgTypeToShow=='inst'){
			echo '<div class="cg_backend_image"><iframe  width="294" height="221" src="'.$image_url_to_show.'"  ></iframe></div>';
		}else if($ImgTypeToShow=='twt'){
			$blockquote = cg_get_blockquote_from_post_content($post_description_to_show);
			//var_dump('$blockquote');
			//var_dump($blockquote);
			echo '<div class="cg_backend_image cg_backend_image_twt"  id="cg_backend_image_twt'.$id.'"></div>';
			?>
            <script  data-cg-processing="true" data-cg-processing-for-reload-entry="true">
                var id = <?php echo json_encode($id); ?>;
                if(!cg_twitter_blockquotes[id]){
                    cg_twitter_blockquotes[id] = {};
                }
                var WpUpload = <?php echo json_encode($WpUpload); ?>;
                if(!cg_twitter_blockquotes[id][WpUpload]){
                    cg_twitter_blockquotes[id][WpUpload] = <?php echo json_encode($blockquote); ?>;
                }
            </script>
			<?php
		}else if($ImgTypeToShow=='tkt'){
			$blockquote = cg_get_blockquote_from_post_content($post_description_to_show);
			echo '<div class="cg_backend_image cg_backend_image_tkt"  id="cg_backend_image_tkt'.$id.'"></div>';
			?>
            <script  data-cg-processing="true" data-cg-processing-for-reload-entry="true">
                var id = <?php echo json_encode($id); ?>;
                if(!cg_tiktok_blockquotes[id]){
                    cg_tiktok_blockquotes[id] = {};
                }
                var WpUpload = <?php echo json_encode($WpUpload); ?>;
                if(!cg_tiktok_blockquotes[id][WpUpload]){
                    cg_tiktok_blockquotes[id][WpUpload] = <?php echo json_encode($blockquote); ?>;
                }
            </script>
			<?php
		}else{
			echo '<a href="'.$sourceOriginalImgShow.'?time='.time().'" target="_blank" title="Show full size" alt="Show full size">
                <div class="cg'.$rThumbToShow.'degree cg_backend_image" style="background: url('.($imgSrcLargeToShow.'?time='.time()).') no-repeat center" ></div></a>';
		}
		echo "</div>";

        ### cg_display_flex open ###
		echo '<div class="cg_display_flex cg_rotate_info cg_hide" style="align-items: center;margin-bottom: 15px;">';
            echo '<div class="cg_backend_info_upload_date_container cg_backend_rotate_css_based " style="pointer-events: none;">';
            echo "<div class=\"cg_image_action_href\" style=\"/* float:right; */width: 131px;margin: 15px auto 5px;\"><div class=\"\" style=\"
            width: 121px; text-align: center;\"><b>NOTE:</b> Image rotation is CSS based. The original image source will not be rotated.</div>";
            echo '</div>';
            echo '</div>';
            echo '<div class="cg_backend_info_upload_date_container cg_backend_save_changes cg_hide">';
            echo "<div class=\"cg_image_action_href cg_go_to_save_button\" style=\"/* float:right; */width: 131px;margin: 15px auto 5px;\"><div class=\"cg_image_action_span\" style=\"
            width: 121px; text-align: center;\">Save changes</div>";
            echo '</div>';
            echo '</div>';
		echo "</div>";

		### cg_display_flex open ###
		echo '<div class="cg_display_flex cg_entry_data">';
		echo '<div class="cg_backend_info_upload_date_container">
<span class="cg_backend_info_details_small"><b>Entry ID: '.$id.'</b><br>Added on<br>'.$uploadTime.'</span>';

		$cg_ecommerce_sale_download_original_source_container_cg_hide = ($EcommerceEntry>0) ? '' : 'cg_hide';

		echo '<div class="cg_ecommerce_sale_download_original_source_container ' .$cg_ecommerce_sale_download_original_source_container_cg_hide.'" >';

		//    window.location = window.location.origin+window.location.pathname+window.location.search+'&cg_download_original_source_for_ecommerce_sale=true'+'&cgGalleryID='+GalleryID+'&cg_real_id='+cg_real_id
		if($isEcommerceEntryAndDownload){
			if(!empty($value->MultipleFiles) && $value->MultipleFiles!='""' && count($EcommerceEntryAndWpUploadIds)){
				echo '<a class="cg_image_action_href cg_download_multiple_files_zip" href="?page='.cg_get_version().'/index.php&option_id='.$GalleryID.'&cg_download_original_source_for_ecommerce_sale=true&cg_real_id='.$id.'&cg_download_cookie=cg_download_cookie_'.$id.'" data-cg-download-cookie="cg_download_cookie_'.$id.'" >';
				echo '<div class="cg_image_action_span" style="width: fit-content; padding: 5px;margin: 5px auto;font-size: 12px;">';
				echo 'Download<br>originals<br>for selling';
				echo "</div>";
				echo "</a>";
			}else{
				echo '<a class="cg_image_action_href" href="?page='.cg_get_version().'/index.php&option_id='.$GalleryID.'&cg_download_original_source_for_ecommerce_sale=true&cg_real_id='.$id.'" >';
				echo '<div class="cg_image_action_span" style="width: fit-content; padding: 5px;margin: 5px auto;font-size: 12px;">';
				echo 'Download<br>original<br>for selling';
				echo "</div>";
				echo "</a>";
			}
		}

		if(!empty($DownloadKeysCsvName)){
			echo "<input type='hidden' class='cg_download_keys_csv_name' value='".$DownloadKeysCsvName."'>";
			echo '<a class="cg_image_action_href cg_download_keys_csv_url " href="?page='.cg_get_version().'/index.php&option_id='.$GalleryID.'&cg_download_keys_for_ecommerce_sale=true&cg_real_id='.$id.'" >';
			echo '<div class="cg_image_action_span" style="width: 130px;padding: 5px;margin: 5px auto;">';
			echo 'Download Keys';
			echo "</div>";
			echo "</a>";
		}

		if(!empty($ServiceKeysCsvName)){
			echo "<input type='hidden' class='cg_service_keys_csv_name' value='".$ServiceKeysCsvName."'>";
			echo '<a class="cg_image_action_href cg_service_keys_csv_url " href="?page='.cg_get_version().'/index.php&option_id='.$GalleryID.'&cg_service_keys_for_ecommerce_sale=true&cg_real_id='.$id.'" >';
			echo '<div class="cg_image_action_span" style="width: 130px;padding: 5px;margin: 5px auto;">';
			echo 'Service Keys';
			echo "</div>";
			echo "</a>";
		}

		echo '<div class="cg_ecommerce_sale_download_original_source_helper">';
		echo "</div>";
		echo "</div>";
		echo '<div class="cg_backend_info_upload_date_container">
<span class="cg_backend_info_details_small">Added on (server-time)<br>'.$uploadTime.'</span>';

		if($RegUserUploadOnly==3){
			echo '<span class="cg_backend_info_details_small">IP '.$IP.'</span>';
			echo "<input type='hidden' class='cg_cookie_id_or_ip' value='".$IP."'>";
		}
		if($RegUserUploadOnly==2){
			if(!empty($CookieId)){
				echo '<span class="cg_backend_info_details_small">Cookie ID '.$CookieId.'</span>';
				echo "<input type='hidden' class='cg_cookie_id_or_ip' value='".$CookieId."'>";
			}
		}
		echo '</div>';

		// Link zum Wordpress User in WP Management
		$isUserShown = false;
		if($WpUserId>0){

			if(!empty($allWpUsersByIdArray[$WpUserId])){
				$isUserShown = true;

				$cgAddedByGoogleUser = '';

				if(!empty($googleUsersArray[$WpUserId])){
					$cgAddedByGoogleUser = "<div class='cg_added_by_google_user'></div>";
				}

				echo "<div class='cg_backend_info_user_link_container'>";
				echo $cgAddedByGoogleUser;
				echo "<span class='cg_added_by'>Added by (username)</span><a class=\"cg_image_action_href cg_load_backend_link\" href='?page=".cg_get_version()."/index.php&users_management=true&option_id=$GalleryID&wp_user_id=".$allWpUsersByIdArray[$WpUserId]['ID']."'>
<div class=\"cg_image_action_span cg_for_id_wp_username_by_search_sort cg_added_by_username\" >".$allWpUsersByIdArray[$WpUserId]['user_login']."</div></a>";

				if(in_array($WpUserId,$wpUsersIdsWithNotConfirmedMailArray)){
					echo "<div class='cg_mail_not_confirmed' >Mail not confirmed</div>";
				}
                    echo "<div class='cg_action_button cg_attach_to_another_user $cgProFalse' >";
                        echo "Attach to another user<br>or detach<br>from user";
                    echo '</div>';
				echo '</div>';
			}
		}
        if(!$isUserShown){
	        echo "<div class='cg_backend_info_user_link_container'>";
                echo "<div class='cg_action_button cg_attach_to_another_user $cgProFalse' >";
                echo "Attach to registered user";
                echo '</div>';
	        echo '</div>';
        }
		// Link zum Wordpress User in WP Management ---- ENDE

		echo '</div>';

        $cg_voting_open = 'cg_voting_open_one_star';

		if(($AllowRating>=12 && $AllowRating<=20)){
			$cg_voting_open = 'cg_voting_open_five_star';
		}

        ### cg_voting_open ###
		echo '<div class="cg_voting_open '.$cg_voting_open.'">';
// eventually for future versions
		if(false){
			if(function_exists('exif_read_data')){


				echo '<div class="cg-exif-container" >';
				echo '<button type="button">Exif</button>';

				echo '<div class="cg-exif-append">';

				if(empty($exifData)){
					echo "<p> Exif data is available since plugin version 10.7.0.
                          If you want to see exif data of this image then simply resave it at the bottom of this area. </p>";
				}else{
					foreach($exifData as $exifKey => $exifValue){

					}
				}

				echo '</div>';


				echo '</div>';

			}
		}

		// Berechnung und Anzeige des durchschnittlichen Ratings

		if(($AllowRating>=12 && $AllowRating<=20)){

			echo '<div class="cg_5_star_main_rating_container">';

			if($IsModernFiveStar==1){

				$AllowRatingMax = $AllowRating-10;

				$RatingOverview = $wpdb->get_results( $wpdb->prepare(
					"
                                        SELECT Rating, COUNT(*) AS NumberOfRows
                                        FROM $tablenameIP
                                        WHERE GalleryID = %d AND Rating >= %d AND Rating <= %d AND pid = %d 
                                        GROUP By Rating
                                    ",
					$GalleryID,1,$AllowRatingMax, $id
				) );

				$countR = count($RatingOverview) ;

				$RatingOverviewArray = [];

				if(count($RatingOverview)){
					foreach ($RatingOverview as $item) {
						$RatingOverviewArray[$item->Rating] = $item->NumberOfRows;
					}
				}

				for($iR=1;$iR<=$AllowRating-10;$iR++){
					if(!empty($RatingOverviewArray[$iR])){
						${'countR'.$iR} = $RatingOverviewArray[$iR];
					}else{
						${'countR'.$iR} = 0;
					}
				}

			}else{
				$AllowRatingMax = $AllowRating-10;

				$RatingOverview = $wpdb->get_results( $wpdb->prepare(
					"
                                        SELECT Rating, COUNT(*) AS NumberOfRows
                                        FROM $tablenameIP
                                        WHERE GalleryID = %d AND Rating >= %d AND Rating <= %d AND pid = %d 
                                        GROUP By Rating
                                    ",
					$GalleryID,1,$AllowRatingMax, $imageId
				) );

				$countR = count($RatingOverview) ;

				$RatingOverviewArray = [];

				if(count($RatingOverview)){
					foreach ($RatingOverview as $item) {
						$RatingOverviewArray[$item->Rating] = $item->NumberOfRows;
					}
				}

				for($iR=1;$iR<=$AllowRating-10;$iR++){
					if(!empty($RatingOverviewArray[$iR])){
						${'countR'.$iR} = $RatingOverviewArray[$iR];
					}else{
						${'countR'.$iR} = 0;
					}
				}
			}

			for($iR=1;$iR<=$AllowRating-10;$iR++){
				if(empty(${'countR'.$iR} )){
					${'countR'.$iR}  = 0;
				}
			}

			for($iR=1;$iR<=$AllowRating-10;$iR++){
				${'countR'.$iR.'origin'}  = ${'countR'.$iR};
			}

			$ratingCummulated = 0;

			if($Manipulate==1){

				for($iR=1;$iR<=$AllowRating-10;$iR++){
					${'ratingCummulated'.$iR} = ${'countR'.$iR}*$iR+(${'addCountR'.$iR}*$iR);
					$ratingCummulated = $ratingCummulated + ${'ratingCummulated'.$iR};
					${'countR'.$iR}  = ${'countR'.$iR}+${'addCountR'.$iR};
				}

			}else{
				for($iR=1;$iR<=$AllowRating-10;$iR++){
					${'ratingCummulated'.$iR} = ${'countR'.$iR}*$iR;
					$ratingCummulated = $ratingCummulated + ${'ratingCummulated'.$iR};
				}
			}

			if($Manipulate==1){
				$countRtotalCheck = $countR;
				for($iR=1;$iR<=$AllowRating-10;$iR++){
					$countRtotalCheck = $countRtotalCheck+${'addCountR'.$iR};
				}
				//$countRtotalCheck = $countR+$addCountR1+$addCountR2+$addCountR3+$addCountR4+$addCountR5;
			}
			else{
				$countRtotalCheck = $countR;
			}

			$countManipulateCummulated = 0;
			$countManipulateMultiplicated = 0;
			$countManipulateCummulatedHide = '';

			if($Manipulate==1){
				$countManipulateCummulated = 0;
				$countManipulateMultiplicated = 0;
				for($iR=1;$iR<=$AllowRating-10;$iR++){
					$countManipulateCummulated = $countManipulateCummulated+${'addCountR'.$iR};
					$countManipulateMultiplicated = $countManipulateMultiplicated+(${'addCountR'.$iR}*$iR);
				}
			}

			if ($countRtotalCheck!=0){
				$averageStars = $ratingCummulated/$countRtotalCheck;
				$averageStarsRounded = round($averageStars,1);
				//echo "<br>averageStars: $averageStars<br>";
			}else{$countRtotalCheck=0; $averageStarsRounded = 0;}

			// var_dump(intval($countR));
			//  var_dump(intval($rating));
			// var_dump(intval($value->CountRtotalSumAdd));

			for($iR=1;$iR<=$AllowRating-10;$iR++){
				${'starTest'.$iR} = 'cg_gallery_rating_div_one_star_off';
			}

			/*            $starTest1 = 'cg_gallery_rating_div_one_star_off';
						$starTest2 = 'cg_gallery_rating_div_one_star_off';
						$starTest3 = 'cg_gallery_rating_div_one_star_off';
						$starTest4 = 'cg_gallery_rating_div_one_star_off';
						$starTest5 = 'cg_gallery_rating_div_one_star_off';*/

			for($iR=1;$iR<=$AllowRating-10;$iR++){
				${'star'.$iR.'Class'} = 'cg_backend_star_off';
			}

			/*            $star1Class = 'cg_backend_star_off';
						$star2Class = 'cg_backend_star_off';
						$star3Class = 'cg_backend_star_off';
						$star4Class = 'cg_backend_star_off';
						$star5Class = 'cg_backend_star_off';*/

			for($iR=1;$iR<=$AllowRating-10;$iR++){
				if($iR==$AllowRating-10){// then must be last one!
					$iRminus1 = $iR-1;
					$iFloat75minus1 = floatval($iR.'.75')-1;
					if($averageStarsRounded>=$iFloat75minus1){${'starTest'.$iR}  = 'cg_gallery_rating_div_one_star_on';${'star'.$iR.'Class'} = 'cg_backend_star_on';}
				}else{
					if($iR==1){
						if($averageStarsRounded>=1){$starTest1 = 'cg_gallery_rating_div_one_star_on';$star1Class = 'cg_backend_star_on';}
						if($averageStarsRounded>=1.25 AND $averageStarsRounded<1.75){$starTest2 = 'cg_gallery_rating_div_one_star_half_off';${'star'.$iR.'Class'} = 'cg_backend_star_half_off';}
					}else{
						$iRminus1 = $iR-1;
						$iFloat75minus1 = floatval($iR.'.75')-1;
						$iFloat25 = floatval($iR.'.25');
						$iFloat75 = floatval($iR.'.75');
						if($averageStarsRounded>=$iFloat75minus1){${'starTest'.$iRminus1} = 'cg_gallery_rating_div_one_star_on';${'star'.$iRminus1.'Class'} = 'cg_backend_star_on';}
						if($averageStarsRounded>=$iFloat25 AND $averageStarsRounded<$iFloat75){${'starTest'.$iR} = 'cg_gallery_rating_div_one_star_half_off';${'star'.$iR.'Class'} = 'cg_backend_star_half_off';}
					}
				}
			}


			echo '<div class="cg_rating_5_star_img_div_container">';

			if($ratingCummulated>0){
				echo "<div class='cg_backend_star cg_backend_five_star cg_backend_five_stars_one_star cg_backend_star_on' ></div>";
			}else{
				echo "<div class='cg_backend_star cg_backend_five_star cg_backend_five_stars_one_star cg_backend_star_off' ></div>";
			}

			echo "<div class='cg_rating_value_countR_div_cummulated' style='font-weight: bold;' title='sum'>$ratingCummulated</div>";

			if($Manipulate==1){

				$countManipulateMultiplicatedHide = '';
				if($countManipulateCummulated<1){
					$countManipulateMultiplicatedHide = 'cg_hide';
				}

				echo "<div class='cg_rating_value_countR_additional_votes cg_rating_value_countR_additional_votes_total $countManipulateMultiplicatedHide'>".$countManipulateMultiplicated."</div>";
			}

			echo '</div>';

			for($iR=$AllowRating-10;$iR>=1;$iR--){
				// CONTINUE HERE!!!!
				echo '<div class="cg_stars_overview">';

				echo "<div class='cg_backend_star_number'>".$iR."</div>";
				echo "<div class='cg_backend_star cg_backend_five_star cg_backend_star_on'></div>";
				echo "<div class='cg_stars_overview_countR cg_rating_value_countR".$iR."' >".${'countR'.$iR}."</div>";
				echo "<div class='cg_stars_overview_equal' > = </div>";
				echo "<div class='cg_stars_overview_rating_cummulated' > ".${'ratingCummulated'.$iR}." </div>";

				if($Manipulate==1){
					$cg_hide = (${'addCountR'.$iR}>=1) ? '' : 'cg_hide';
					${'addCountR'.$iR.'multiplicated'} = ${'addCountR'.$iR} * $iR;
					echo "<div class='cg_rating_value_countR_additional_votes cg_rating_value_countR_additional_votes_".$iR." $cg_hide'>".${'addCountR'.$iR.'multiplicated'}."</div>";
				}

				echo "</div>";
			}

			echo "<div class='cg-show-votes cg-show-votes-five-stars-area'><a class='cg_image_action_href cg_load_backend_link' href='?page=".cg_get_version()."/index.php&image_id=$id&show_votes=true&option_id=$GalleryID'><span class='cg_image_action_span' style='width: 100px;text-align: center;margin-left: auto;margin-right: auto;'>Show votes</span></a></div>";

			echo '</div>';


		}else if($AllowRating==2){

			$countS = $wpdb->get_var( $wpdb->prepare(
				"
                                        SELECT COUNT(*) AS NumberOfRows
                                        FROM $tablenameIP
                                        WHERE GalleryID = %d AND RatingS = %d AND pid = %d
                                    ",
				$GalleryID,1,$id
			) );

			echo '<div class="cg_allow_rating_one_star">';

			echo "<div class='cg_rating_center' >";

			if($Manipulate==1){
				$finalCountSvalue = $countS+$addCountS;
			}else{
				$finalCountSvalue = $countS;
			}

			if ($countS>0){
				$countS = $countS;
			}
			else{$countS=0;}

			if($finalCountSvalue>=1){
				$starTest6 = $iconsURL.'/star_48_reduced.png';
				$oneStarClass = 'cg_backend_star_on';
			}
			else{
				$starTest6 = $iconsURL.'/star_off_48_reduced_with_border.png';
				$oneStarClass = 'cg_backend_star_off';
			}

			if($finalCountSvalue=='' || $finalCountSvalue==null){
				$finalCountSvalue = 0;
			}

			echo "<div><div class='cg_backend_star cg_backend_one_star $oneStarClass' style='cursor:default;'></div></div>";
			echo "";
			echo "<input type='hidden' class='cg_value_origin' value='$countS' >";
			echo "<input type='hidden' class='cg_value_add_one_star cg_input_vars_count cg_disabled_send' value='$addCountS' name='addCountS[$id]'>";


			if($finalCountSvalue<0){
				$finalCountSvalue = 0;
			}

			echo "<div class='cg_rating_value'>".$finalCountSvalue."</div>";

			if($Manipulate==1){
				$cg_hide = ($addCountS>=1) ? '' : 'cg_hide';
				echo "<div class='cg_rating_value_countR_additional_votes $cg_hide'>".$addCountS."</div>";
			}
			echo '</div>';

			echo "<div class='cg-show-votes'><a class='cg_image_action_href cg_load_backend_link' href='?page=".cg_get_version()."/index.php&image_id=$id&show_votes=true&option_id=$GalleryID'><div class='cg_image_action_span' style='width: 100px;text-align: center;margin-left: auto;margin-right: auto;'>Show votes</div></a></div>";

			echo '</div>';

		}
		else if($FbLike==1){

			echo '<div class="cg_backend_info_show_votes_fb_like">';

			echo "<div class='cg-show-votes'><a class='cg_image_action_href cg_load_backend_link' href='?page=".cg_get_version()."/index.php&image_id=$id&show_votes=true&option_id=$GalleryID'><div class='cg_image_action_span' style='width: 100px;text-align: center;margin-left: 20px;'>Show votes</div></a></div>";

			echo '</div>';

		}

		if($Manipulate==1 && $AllowRating==2){
			echo "<div style='' class='cg_manipulate_container'>";


			echo "<div class='cg_manipulate'>";
			echo "<div class='cg_manipulate_adjust'>";

			echo "<span class='cg_manipulate_adjust_one_star'>Manipulation</span>";

			//echo "<select class='cg_manipulate_plus_minus'><option value='+'>+</option><option value='-'>-</option></select>";
			if($addCountS==0){
				$addCountS=0;
			}

			echo "<input type='number' max='9999999' min='-9999999' class='cg_manipulate_countS_input cg_manipulate_plus_value' value='$addCountS'>";
			echo "</div></div>";

			echo '</div>';

		}

		if($Manipulate==1 && (($AllowRating>=12 && $AllowRating<=20))){

			//    echo "<div style='float:left;width:160px;text-align:center;left:0px;'>";


			echo "<input type='hidden' class='cg_value_origin_5_star_count' value='$countR' >";
			echo "<input type='hidden' class='cg_value_origin_5_star_rating' value='$rating' >";
			echo "<input type='hidden' class='cg_value_origin_5_star_countBeforeInput' value='$countR' >";

			for($iR=$AllowRating-10;$iR>=1;$iR--){
				echo "<input type='hidden' class='cg_value_origin_5_star_addCountR$iR cg_value_origin_5_star_to_cumulate cg_input_vars_count cg_disabled_send' value='".${'addCountR'.$iR}."' name='addCountR".$iR."[$id]' >";
				echo "<input type='hidden' class='cg_value_origin_5_only_value_$iR' value='".${'countR'.$iR.'origin'}."' >";
			}

			echo "<div class='cg_manipulate'>";
			echo "<div class='cg_manipulate_adjust'>";

			echo "<span class='cg_manipulate_adjust_five_star'>Manipulation</span>";

			if($addCountS==0){
				$addCountS='';
			}


			for($iR=$AllowRating-10;$iR>=1;$iR--){
				echo '<div class="cg_manipulate_container_5_stars cg_hide">';

				echo "<div class='cg_backend_star_number'>".$iR."</div>";
				echo "<div class='cg_backend_star cg_backend_five_star cg_backend_star_on'></div>";

				echo "<div class='cg_manipulate_5_star_input_div' ><input data-star='$iR' type='number' class='cg_manipulate_5_star_input cg_manipulate_".$iR."_star_number  cg_manipulate_plus_value' max='9999999' min='-9999999'   value='".${'addCountR'.$iR}."'  ></div>";
				echo "</div>";
			}

			echo "</div>";
			echo "</div>";

		}

		if($CountC>0 || true){ echo "<div class='cg_comments' ><a class=\"cg_image_action_href cg_load_backend_link\" href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&show_comments=true&id=$id\"><div class=\"cg_image_action_span cg_image_action_comments\" style='font-size:14px;width:100px;text-align:center;'>Comments: <b>$CountC</b><br><span style='font-size: 12px;'>To review: <b>$CountCtoReview</b></span></div></a></div>"; }
		//else{ echo ""; }

		### cg_voting_column ###
		echo '</div>';
        ### cg_display_flex close ###
		echo '</div>';

		echo "<div class='cg_move_to_another_gallery_container $GalleryID' ><a class=\"cg_move_to_another_gallery cg_image_action_href $cgProFalse\" href=\"?page=".cg_get_version()."/index.php&option_id=&show_comments=true&id=$id\"><div class=\"cg_image_action_span $cgProFalse\" >Move to another gallery</div></a></div>";

	    echo "</div>";

		echo "<div class='cg_fields_div'>";

		//print_r($selectUpload);

		// FELDBENENNUNGEN

		// 1 = Feldtyp
		// 2 = Feldnummer
		// 3 = Feldtitel
		// 4 = Feldinhalt
		// 5 = Feldkrieterium1
		// 6 = Feldkrieterium2
		// 7 = Felderfordernis

		$r = 0; // Notwendig zur Überprüfung ab wann das dritte Feld versteckt wird. ACHTUNG: Bild-Uploadfeld immer dabei, dasswegen r>=4 zum Schluss.

		// simply placeholder for sure because of bottom processing
		$formvalue = '';
		$fieldtype = '';

		if ($selectFormInput == true) {

			if(!empty($selectContentFieldArray)){
				foreach($selectContentFieldArray as $key => $formvalue){

					// 1. Feld Typ
					// 2. ID des Feldes in F_INPUT
					// 3. Feld Reihenfolge
					// 4. Feld Content

					if(!isset($fieldtype)){
						$fieldtype = '';
					}

					if($formvalue=='text-f'){$fieldtype="nf"; $i=1; continue;}
					if($fieldtype=="nf" AND $i==1){$formFieldId=$formvalue; $i=2; continue;}
					if($fieldtype=="nf" AND $i==2){$fieldOrder=$formvalue; $i=3; continue;}
					if ($fieldtype=="nf" AND $i==3) {

						$formvalue = contest_gal1ery_convert_for_html_output_without_nl2br($formvalue);
						$getEntries1 = contest_gal1ery_convert_for_html_output_without_nl2br($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Content']);

						// Prüfen ob das Feld im Slider angezeigt werden soll
						if($AllowGalleryScript==1){
							if(array_search($formFieldId, $ShowSliderInputID)){$checked='checked';}
							else{$checked='';}
						}

						echo "<div class='cg_image_title_container' >";
						echo "$formvalue:<br/>";
						echo "<input type='text' value='$getEntries1' name='content[$id][$formFieldId][short-text]'  maxlength='1000' class='cg_image_title cg_short_text cg_input_vars_count cg_disabled_send cg_input_by_search_sort_$formFieldId'>";
						echo "<img src='$titleIcon' title='Insert original WordPress title' alt='Insert original WordPress file title' class='$hideWpFileInfoToInsert cg_title_icon' />";
						echo "<input type='hidden' class='post_title' value='$post_title' >";

						echo "</div>";

						$fieldtype='';

						$i=0;

					}

					if($formvalue=='date-f'){$fieldtype="dt"; $i=1; continue;}
					if($fieldtype=="dt" AND $i==1){$formFieldId=$formvalue; $i=2; continue;}
					if($fieldtype=="dt" AND $i==2){$fieldOrder=$formvalue; $i=3; continue;}
					if ($fieldtype=="dt" AND $i==3) {


						$formvalue = html_entity_decode(stripslashes($formvalue));
						$getEntries1 = html_entity_decode(stripslashes($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Content']));

						$newDateTimeString = '';
						$dtFormatOriginal = $dateFieldFormatTypesArray[$formFieldId];

						if(!empty($getEntries1) AND $getEntries1!='0000-00-00 00:00:00'){

							try {

								$dtFormat = $dateFieldFormatTypesArray[$formFieldId];

								$dtFormat = str_replace('YYYY','Y',$dtFormat);
								$dtFormat = str_replace('MM','m',$dtFormat);
								$dtFormat = str_replace('DD','d',$dtFormat);
								$newDateTimeObject = DateTime::createFromFormat("Y-m-d H:i:s",$getEntries1);

								if(is_object($newDateTimeObject)){
									$newDateTimeString = $newDateTimeObject->format("$dtFormat");
								}

							}catch (Exception $e) {

								//echo $e->getMessage();
								$newDateTimeString = '';

							}

						}

						if(!empty($getEntries1) AND $getEntries1!='0000-00-00 00:00:00' AND empty($newDateTimeObject)){// is false if not worked
							$dtFormatOriginal = 'YYYY-MM-DD';
						}

						echo "<div class='cg_image_title_container' >";
						echo "$formvalue:<br/>";
						echo "<input type='text' value='$newDateTimeString' autocomplete='off' name='content[$id][$formFieldId][date-field]'  maxlength='1000' class='cg_image_title cg_short_text cg_input_date_class cg_input_vars_count cg_input_by_search_sort_$formFieldId'>";
						echo "<input type='hidden' value='$dtFormatOriginal' class='cg_date_format'>";

						echo "</div>";

						$fieldtype='';

						$i=0;

					}


					// 1. Feld Typ
					// 2. ID des Feldes in F_INPUT
					// 3. Feld Reihenfolge
					// 4. Feld Content


					if($formvalue=='url-f'){$fieldtype="url"; $i=1; continue;}
					if($fieldtype=="url" AND $i==1){$formFieldId=$formvalue; $i=2; continue;}
					if($fieldtype=="url" AND $i==2){$fieldOrder=$formvalue; $i=3; continue;}
					if ($fieldtype=="url" AND $i==3) {

						$formvalue = contest_gal1ery_convert_for_html_output_without_nl2br($formvalue);
						$getEntries1 = contest_gal1ery_convert_for_html_output_without_nl2br($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Content']);

						// Prüfen ob das Feld im Slider angezeigt werden soll
						if($AllowGalleryScript==1){
							if(array_search($formFieldId, $ShowSliderInputID)){$checked='checked';}
							else{$checked='';}
						}

						echo "<div class='cg_image_title_container' >";
						echo "$formvalue:<br/>";
						echo "<input type='text' value='$getEntries1' name='content[$id][$formFieldId][short-text]'  placeholder='www.example.com' maxlength='1000' class='cg_image_title cg_short_text cg_input_vars_count cg_input_by_search_sort_$formFieldId'>";

						echo "</div>";

						$fieldtype='';

						$i=0;

					}

					if($formvalue=='select-f'){$fieldtype="se"; $i=1; continue;}
					if($fieldtype=="se" AND $i==1){$formFieldId=$formvalue; $i=2; continue;}
					if($fieldtype=="se" AND $i==2){$fieldOrder=$formvalue; $i=3; continue;}
					if ($fieldtype=="se" AND $i==3) {

						$formvalue = html_entity_decode(stripslashes($formvalue));
						$getEntries1 = html_entity_decode(stripslashes($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Content']));

						// Prüfen ob das Feld im Slider angezeigt werden soll
						if($AllowGalleryScript==1){
							if(array_search($formFieldId, $ShowSliderInputID)){$checked='checked';}
							else{$checked='';}
						}

						echo "<div class='cg_image_title_container'>";
						echo "$formvalue:<br/>";
						echo "<input type='text' value='$getEntries1' name='content[$id][$formFieldId][short-text]' maxlength='1000' class='cg_image_title cg_short_text cg_input_vars_count cg_disabled_send cg_input_by_search_sort_$formFieldId'>";
						echo "<img src='$titleIcon' title='Insert original WordPress file title if exists' alt='Insert original WordPress file title if exists' class='$hideWpFileInfoToInsert cg_title_icon' />";
						echo "<input type='hidden' class='post_title' value='$post_title' >";

						if($Use_as_URL_id==$formFieldId AND $ForwardToURL==1){
							echo "&nbsp;&nbsp;&nbsp;<strong>URL</strong>";
						}
						echo "</div>";

						$fieldtype='';

						$i=0;

					}

					if($formvalue=='selectc-f'){$fieldtype="sec"; $i=1; continue;}
					if($fieldtype=="sec" AND $i==1){$formFieldId=$formvalue; $i=2; continue;}
					if($fieldtype=="sec" AND $i==2){$fieldOrder=$formvalue; $i=3; continue;}
					if ($fieldtype=="sec" AND $i==3) {

						if(!empty($categories)){

							$formvalue = html_entity_decode(stripslashes($formvalue));

							echo "<div >";
							echo "$formvalue:<br/>";

							echo "<select name='imageCategory[$id]' class='cg_category_select cg_input_vars_count cg_disabled_send cg_select_by_search_sort_$formFieldId'>";
							echo "<option value='0'>Select category</option>";

							$selectedCat = '';

							foreach($categories as $category){

								if($imageCategory==$category->id){
									$selectedCat = 'selected';
									echo "<option value='".$category->id."' $selectedCat>".$category->Name."</option>";
								}
								else{
									echo "<option value='".$category->id."' >".$category->Name."</option>";
								}
							}

							echo "</select>";

							if($Use_as_URL_id==$formFieldId AND $ForwardToURL==1){
								echo "&nbsp;&nbsp;&nbsp;<strong>URL</strong>";
							}
							echo "</div>";

							$fieldtype='';

							$i=0;

						}
					}

					if($formvalue=='email-f'){$fieldtype="ef";  $i=1; continue;}
					if($fieldtype=="ef" AND $i==1){$formFieldId=$formvalue; $i=2; continue;}
					if($fieldtype=="ef" AND $i==2){$fieldOrder=$formvalue; $i=3; continue;}
					if ($fieldtype=='ef' AND $i==3) {

						//$getEntries = $wpdb->get_var( "SELECT Short_Text FROM $tablenameentries WHERE pid='$id' AND f_input_id = '$formFieldId'");

						$emailStatus = true;
						$emailStatusText = 'Not confirmed';

						if(!empty($WpUserId) AND !empty($allWpUsersByIdArray[$WpUserId])){// check for sure both, because user might be deleted
							//$getEntries = $wpUser->user_email;

							$emailStatusText = 'Confirmed (registered user)';
							$mailReadonly = "readonly";
							$registeredUserMail = " (registered user email)";
						}
						else{

							$getEntriesMail = html_entity_decode(stripslashes($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Content']));
							$getEntriesConfMailId = html_entity_decode(stripslashes($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['ConfMailId']));

							$mailReadonly = "readonly";
							$registeredUserMail = "";

							if(!empty($getEntriesMail)){
								if($getEntriesConfMailId>0){
									$emailStatusText = 'Confirmed (not registered user)';
								}
							}

						}

						// Prüfen ob das Feld im Slider angezeigt werden soll
						if($AllowGalleryScript==1){
							if(array_search($formFieldId, $ShowSliderInputID)){$checked='checked';}
							else{$checked='';}
						}

						$formvalue = html_entity_decode(stripslashes($formvalue));

						echo "<div>";
						echo "$formvalue$registeredUserMail:<br/>";
						echo "<input type='text' value='$getEntriesMail' class='email cg_short_text cg_input_by_search_sort_$formFieldId'  maxlength='1000' $mailReadonly >";
						echo "</div>";

						$fieldtype='';

						$i=0;

					}

					if($formvalue=='check-f'){$fieldtype="cb";  $i=1; continue;}// Agreement field!
					if($fieldtype=="cb" AND $i==1){$formFieldId=$formvalue; $i=2; continue;}
					if($fieldtype=="cb" AND $i==2){$fieldOrder=$formvalue; $i=3; continue;}
					if($fieldtype=='cb' AND $i==3) {

						$getEntries1 = html_entity_decode(stripslashes($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Content']));
						$getEntriesChecked = html_entity_decode(stripslashes($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Checked']));

						if(!empty($getEntries1)){

							$formvalue = html_entity_decode(stripslashes($formvalue));

							$checked = '';
							$checkedStatus = '';
							if($getEntriesChecked==1){
								$checked = 'checked';
								$checkedStatus = 'checked';
							}else{
								$checked = '';
								$checkedStatus = 'not checked';
							}

							echo "<div >";
							echo "$formvalue:<br/>";
							echo "<input style='width: unset !important;border: thin solid #dedede;box-shadow: inset 0 1px 2px rgba(0, 0, 0, .07);' type='checkbox' $checked disabled> $checkedStatus";
							echo "</div>";

						}

						$fieldtype='';

						$i=0;

					}

					if($formvalue=='comment-f'){$fieldtype="kf"; $i=1; continue;}
					if($fieldtype=="kf" AND $i==1){$formFieldId=$formvalue; $i=2; continue;}
					if($fieldtype=="kf" AND $i==2){$fieldOrder=$formvalue; $i=3; continue;}
					if ($fieldtype=='kf' AND $i==3) {

						$formvalue = contest_gal1ery_convert_for_html_output_without_nl2br($formvalue);
						$getEntries1 = contest_gal1ery_convert_for_html_output_without_nl2br($allEntriesByImageIdArrayWithContent[$id][$formFieldId]['Content']);

						// Prüfen ob das Feld im Slider angezeigt werden soll
						if($AllowGalleryScript==1){
							if(array_search($formFieldId, $ShowSliderInputID)){$checked='checked';}
							else{$checked='';}
						}

						echo "<div  class='cg_image_description_container cg_image_excerpt_container'>";
						echo "$formvalue:<br/>";
						echo "<textarea name='content[$id][$formFieldId][long-text]' rows='4' maxlength='10000' class='cg_image_description cg_image_excerpt cg_long_text cg_input_vars_count cg_disabled_send cg_input_by_search_sort_$formFieldId'>$getEntries1</textarea>";
						echo "<div class='cg_comment_icons_div'>";
						echo "<img src='$descriptionIcon' title='Insert original WordPress file description if exists' alt='Insert original WordPress description' class='$hideWpFileInfoToInsert cg_description_icon' />";
						echo "<img src='$excerptIcon' title='Insert original WordPress file excerpt if exists' alt='Insert original WordPress excerpt if exists' class='$hideWpFileInfoToInsert cg_excerpt_icon' />";
						echo "<input type='hidden' class='post_description' value='$post_description' >";
						echo "<input type='hidden' class='post_excerpt' value='$post_excerpt' >";
						echo "</div>";

						echo "</div>";

						$fieldtype='';

						$i=0;

					}

				}
			}

			if(!empty($getEntriesMail) && $Informed!=1){// if informed then cg_email has not to be send!
				echo "<input type='hidden' value='$getEntriesMail' name='cg_email[$id]'  class='email-clone cg_input_vars_count' >";
				echo "<input type='hidden' value='$NamePic' name='cg_image_name[$id]'  class='cg_input_vars_count'  >";
			}

			if($optionsSQL->FbLike==1 || $FbLikeOnlyShare == 1){

				if(!empty($fbLikeContentArray)){

					if(!empty($selectContentFieldArray)){
						echo "<hr class='cg_fields_div_divider'>";
					}

					$valueTitle = '';
					$valueDescription = '';

					if(!empty($fbLikeContentArray[$id])){
						if(!empty($fbLikeContentArray[$id]['title'])){

							$valueTitle = $fbLikeContentArray[$id]['title'];

						}
						if(!empty($fbLikeContentArray[$id]['description'])){

							$valueDescription = $fbLikeContentArray[$id]['description'];

						}
					}

					echo "<div class='cg_image_title_container' >";

					echo "Facebook share button title:<br/>";
					echo "<input type='text' value='".$valueTitle."' name='fbcontent[$id][title]'  maxlength='1000' class='cg_image_title cg_input_vars_count'>";
					$baseUrlFacebook=$uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/'.$Timestamp."_".$NamePic."413.html";
					echo "<input type='hidden' value='".$baseUrlFacebook."' name='fbcontent[$id][baseUrlForFacebook]' class='cg_input_vars_count'>";
					echo "<img src='$titleIcon' title='Insert original WordPress file title if exists' alt='Insert original WordPress file title if exists' class='$hideWpFileInfoToInsert cg_title_icon' />";
					echo "<input type='hidden' class='post_title' value='$post_title' >";
					echo "</div>";

					echo "<div class='cg_image_description_container cg_facebook_description' >";

					if((float)$optionsSQL->Version<10.9825){
						echo "Facebook share button description: <span class=\"cg-info-icon\">info</span>
                                <div class=\"cg-info-container cg-info-container-facebook-description\" style=\"top: 21px;left: 38px;display: none;\">Can be only shown for images<br>added since plugin version 10.9.8.2.3<br>In general description appears with a delay after upload</div><br/>";
					}else{
						echo "Facebook share button description: <span class=\"cg-info-icon\">info</span>
                                <div class=\"cg-info-container cg-info-container-facebook-description\" style=\"top: 21px;left: 38px;display: none;\">In general description appears with a delay after upload</div><br/>";
					}

					echo "<textarea name='fbcontent[$id][description]'  maxlength='10000' rows='4' class='cg_image_description cg_image_excerpt cg_input_vars_count'>$valueDescription</textarea>";
					echo "<div class='cg_comment_icons_div'>";
					echo "<img src='$descriptionIcon' title='Insert original WordPress description if exists' alt='Insert original WordPress description' class='$hideWpFileInfoToInsert cg_description_icon' />";
					echo "<img src='$excerptIcon' title='Insert original WordPress excerpt if exists' alt='Insert original WordPress excerpt if exists' class='$hideWpFileInfoToInsert cg_excerpt_icon' />";
					echo "<input type='hidden' class='post_description' value='$post_description' >";
					echo "<input type='hidden' class='post_excerpt' value='$post_excerpt' >";
					echo "</div>";
					echo "</div>";

				}

			}

			if(function_exists('exif_read_data')){

				echo '<div class="cg_image_title_container cg_exif_data_container '.((($ImgType=='jpg' OR $ImgType=='jpeg' OR $ImgType=='png' OR $ImgType=='gif' OR $ImgType=='ico')) ? '' : 'cg_hide').'">Available EXIF data:';
				echo '<div class="cg-center-image-exif-data">';
				echo '<span class="'.((!$exifData) ? '' : 'cg_hide').' cg-center-image-exif-no-data">Image has no EXIF data</span>';
				echo '<span class="cg_hide cg-center-image-exif-data-not-checked">EXIF data will be checked after "Save changes"</span>';

				$DateTimeOriginal = '';
				if(!empty($exifData['DateTimeOriginal'])){
					$DateTimeOriginal = $exifData['DateTimeOriginal'];
					$DateTimeOriginal = explode(' ',$DateTimeOriginal);
					$DateTimeOriginal = $DateTimeOriginal[0];
					$DateTimeOriginal = str_replace(':','-',$DateTimeOriginal);
				}

				echo '<div class="'.((!empty($exifData['DateTimeOriginal'])) ? '' : 'cg_hide').' cg-exif cg-exif-date-time-original cg-exif '.$cgProFalse.'"><span class="cg-exif-date-time-original-img cg-exif-img"></span><span class="cg-exif-date-time-original-text cg-exif-text">'.$DateTimeOriginal.'</span></div>';

				$MakeAndModel = '';
				if(!empty($exifData['MakeAndModel'])){// Make And Model or only Model might be available
					$MakeAndModel = $exifData['MakeAndModel'];
				} else if(!empty($exifData['Model'])){// Make And Model or only Model might be available
					$MakeAndModel = $exifData['Model'];
				}

				echo '<div class="'.((!empty($exifData['MakeAndModel'])) ? '' : 'cg_hide').' cg-exif cg-exif-model"><span class="cg-exif-model-img cg-exif-img"></span><span class="cg-exif-model-text cg-exif-text">'.$MakeAndModel.'</span></div>';

				$ApertureFNumber = '';
				echo '<div class="'.((!empty($exifData['ApertureFNumber'])) ? '' : 'cg_hide').' cg-exif cg-exif-aperturefnumber cg-exif"><span class="cg-exif-aperturefnumber-img cg-exif-img"></span><span class="cg-exif-aperturefnumber-text cg-exif-text">'.((!empty($exifData['ApertureFNumber'])) ? $exifData['ApertureFNumber'] : '').'</span></div>';
				echo '<div class="'.((!empty($exifData['ExposureTime'])) ? '' : 'cg_hide').' cg-exif cg-exif-exposuretime cg-exif"><span class="cg-exif-exposuretime-img cg-exif-img"></span><span class="cg-exif-exposuretime-text cg-exif-text">'.((!empty($exifData['ExposureTime'])) ? $exifData['ExposureTime'] : '').'</span></div>';
				echo '<div class="'.((!empty($exifData['ISOSpeedRatings'])) ? '' : 'cg_hide').' cg-exif cg-exif-isospeedratings cg-exif"><span class="cg-exif-isospeedratings-img cg-exif-img"></span><span class="cg-exif-isospeedratings-text cg-exif-text">'.((!empty($exifData['ISOSpeedRatings'])) ? $exifData['ISOSpeedRatings'] : '').'</span></div>';
				echo '<div class="'.((!empty($exifData['FocalLength'])) ? '' : 'cg_hide').' cg-exif cg-exif-focallength cg-exif"><span class="cg-exif-focallength-img cg-exif-img"></span><span class="cg-exif-focallength-text cg-exif-text">'.((!empty($exifData['FocalLength'])) ? $exifData['FocalLength'] : '').'</span></div>';
				echo '</div>';

				echo '</div>';

			}


			if ($r>=4) {
				echo "</div>"; //Bild-Uploadfeld immer dabei, dasswegen r>=4 zum Schluss.
			}

			else{

				echo "";

			}

		}

		else{

			echo "";

		}

		if(!empty($WpPage)){
			echo "<div class='cg_entry_pages_container' >";
			echo "<div style='margin-bottom: 5px;'>";
			echo "Pages for this entry:";
			echo "</div>";
			echo "<div class='cg_entry_pages'>";
			if(get_post_status( $WpPage ) == 'trash'){
				echo "<a href='".get_bloginfo('wpurl') . "/wp-admin/edit.php?post_status=trash&post_type=contest-gallery' target='_blank' class='cg_entry_page_url cg_display_inline_block'>";
				echo "cg_gallery <b>moved to trash</b> - can be restored";
				echo "</a>";
			}else{
				$permalink = cg_get_guid($WpPage,$domain,$CgEntriesOwnSlugName,$Version);
				if(empty($permalink)){
					echo "<a href='#' target='_blank' class='cg_entry_page_url cg_disabled_background_color_e0e0e0 '>";
					echo "cg_gallery <b>&nbsp;deleted&nbsp;</b> - can be corrected in \"Edit options\" >>> \"Status, repair...\"";
					echo "</a>";
				}else{
					echo "<span data-cg-shortcode='[cg_gallery id=\"$GalleryID\" entry_id=\"$id\"]'><a href='".$permalink."' target='_blank' class='cg_entry_page_url''>";
					echo "cg_gallery";
					echo "</a> <span class='td_gallery_info_shortcode_edit cg_tooltip'></span></span>";
				}
			}
			if(get_post_status( $WpPageUser ) == 'trash'){
				echo "<a href='".get_bloginfo('wpurl') . "/wp-admin/edit.php?post_status=trash&post_type=contest-gallery' target='_blank' class='cg_entry_page_url cg_display_inline_block'>";
				echo "cg_gallery_user <b>moved to trash</b> - can be restored";
				echo "</a>";
			}else{
				$permalink = cg_get_guid($WpPageUser,$domain,$CgEntriesOwnSlugName,$Version);
				if(empty($permalink)){
					echo "<a href='#' target='_blank' class='cg_entry_page_url cg_disabled_background_color_e0e0e0 '>";
					echo "cg_gallery_user <b>&nbsp;deleted&nbsp;</b> - can be corrected in \"Edit options\" >>> \"Status, repair...\"";
					echo "</a>";
				}else{
					echo "<span data-cg-shortcode='[cg_gallery_user id=\"$GalleryID\" entry_id=\"$id\"]'><a href='".$permalink."' target='_blank' class='cg_entry_page_url'>";
					echo "cg_gallery_user";
					echo "</a> <span class='td_gallery_info_shortcode_edit cg_tooltip'></span></span>";
				}
			}
			if(get_post_status( $WpPageNoVoting ) == 'trash'){
				echo "<a href='".get_bloginfo('wpurl') . "/wp-admin/edit.php?post_status=trash&post_type=contest-gallery' target='_blank' class='cg_entry_page_url cg_display_inline_block'>";
				echo "cg_gallery_no_voting <b>moved to trash</b> - can be restored";
				echo "</a>";
			}else{
				$permalink = cg_get_guid($WpPageNoVoting,$domain,$CgEntriesOwnSlugName,$Version);
				if(empty($permalink)){
					echo "<a href='#' target='_blank' class='cg_entry_page_url cg_disabled_background_color_e0e0e0 '>";
					echo "cg_gallery_no_voting <b>&nbsp;deleted&nbsp;</b> - can be corrected in \"Edit options\" >>> \"Status, repair...\"";
					echo "</a>";
				}else{
					echo "<span data-cg-shortcode='[cg_gallery_no_voting id=\"$GalleryID\" entry_id=\"$id\"]'><a href='".$permalink."' target='_blank' class='cg_entry_page_url'>";
					echo "cg_gallery_no_voting";
					echo "</a> <span class='td_gallery_info_shortcode_edit cg_tooltip'></span></span>";
				}
			}
			if(get_post_status( $WpPageWinner ) == 'trash'){
				echo "<a href='".get_bloginfo('wpurl') . "/wp-admin/edit.php?post_status=trash&post_type=contest-gallery' target='_blank' class='cg_entry_page_url cg_display_inline_block'>";
				echo "cg_gallery_winner <b>moved to trash</b> - can be restored";
				echo "</a>";
			}else{
				$permalink = cg_get_guid($WpPageWinner,$domain,$CgEntriesOwnSlugName,$Version);
				if(empty($permalink)){
					echo "<a href='#' target='_blank' class='cg_entry_page_url cg_disabled_background_color_e0e0e0 '>";
					echo "cg_gallery_winner <b>&nbsp;deleted&nbsp;</b> - can be corrected in \"Edit options\" >>> \"Status, repair...\"";
					echo "</a>";
				}else{
					echo "<span data-cg-shortcode='[cg_gallery_winner id=\"$GalleryID\" entry_id=\"$id\"]'><a href='".$permalink."' target='_blank' class='cg_entry_page_url'>";
					echo "cg_gallery_winner";
					echo "</a> <span class='td_gallery_info_shortcode_edit cg_tooltip'></span></span>";
				}
			}
			if(floatval($galleryDbVersion)>=22){
				if(get_post_status( $WpPageEcommerce ) == 'trash'){
					echo "<a href='".get_bloginfo('wpurl') . "/wp-admin/edit.php?post_status=trash&post_type=contest-gallery' target='_blank' class='cg_entry_page_url cg_display_inline_block'>";
					echo "cg_gallery_ecommerce <b>moved to trash</b> - can be restored";
					echo "</a>";
				}else{
					$permalink = cg_get_guid($WpPageEcommerce,$domain,$CgEntriesOwnSlugName,$Version);
					if(empty($permalink)){
						echo "<a href='#' target='_blank' class='cg_entry_page_url cg_disabled_background_color_e0e0e0 '>";
						echo "cg_gallery_ecommerce <b>&nbsp;deleted&nbsp;</b> - can be corrected in \"Edit options\" >>> \"Status, repair...\"";
						echo "</a>";
					}else{
						if($ImgTypeToShow!='con'){
							echo "<span data-cg-shortcode='[cg_gallery_ecommerce id=\"$GalleryID\" entry_id=\"$id\"]'><a href='".$permalink."' target='_blank' class='cg_entry_page_url '>";
							echo "cg_gallery_ecommerce";
							echo "</a> <span class='td_gallery_info_shortcode_edit cg_tooltip'></span></span>";
							echo "<span data-cg-shortcode='[cg_gallery_ecommerce id=\"$GalleryID\" entry_id=\"$id\" test=\"true\"]'><a href='".$permalink."?test=true' target='_blank' class='cg_entry_page_url '>";
							echo "cg_gallery_ecommerce test";
							echo "</a> <span class='td_gallery_info_shortcode_edit cg_tooltip'></span></span>";
						}else{
							echo "<span>cg_gallery_ecommerce and \"Sell settings\" are not available for $ytbHintText entries</span>";
						}
					}
				}
			}

			echo "</div>";
			echo "</div>";
		}

		echo "</div>";


		echo "</li>";


	}

	echo "</ul>";

	echo "<div id='cgGallerySubmit'>";

	?>
    <script data-cg-processing="true">
        cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload = <?php echo json_encode($addedEcommerceImageForDownload); ?>;
    </script>
    <script data-cg-processing-for-reload-entry="true">
        cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownloadForReloadEntry = <?php echo json_encode($addedEcommerceImageForDownload); ?>;
    </script>
	<?php


	echo "<input type='hidden' name='chooseAction1' value='1'/>";

	echo "<a class=\"cg_image_action_href cg_fields_div_add_fields\" href='?page=".cg_get_version()."/index.php&define_upload=true&option_id=$GalleryID'><span class=\"cg_image_action_span\" style='margin-left: 5px;'>Add/Remove fields</span></a>";

	echo '<input type="submit" class="cg_backend_button_gallery_action cg_gallery_backend_submit" name="submit" value="Change/Save data" id="cg_gallery_backend_submit" style="margin-left:auto;">';

	echo "</div>";

	if($isAjaxCall){

		echo "<div id='cgStepsNavigationBottom' class='cg_steps_navigation' style='margin-top:2px;'>";
		for ($i = 0; $rows > $i; $i = $i + $step) {

			$anf = $i + 1;
			$end = $i + $step;

			if ($end > $rows) {
				$end = $rows;
			}

			if ($anf == $nr1 AND ($start+$step) > $rows AND $start==0) {
				continue;
				echo "<div data-cg-start='$i' class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$anf-$end</a></div>";
			}

            elseif ($anf == $nr1 AND ($start+$step) > $rows AND $anf==$end) {

				echo "<div data-cg-start='$i' class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$end</a></div>";
			}


            elseif ($anf == $nr1 AND ($start+$step) > $rows) {
				echo "<div data-cg-start='$i' class='cg_step cg_step_selected'><a href=\?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$anf-$end</a></div>";
			}

            elseif ($anf == $nr1) {
				echo "<div data-cg-start='$i' class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$anf-$end</a></div>";
			}

            elseif ($anf == $end) {
				echo "<div data-cg-start='$i' class='cg_step'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$end</a></div>";
			}

			else {
				echo "<div data-cg-start='$i' class='cg_step'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&edit_gallery=true\">$anf-$end</a></div>";
			}
		}
		echo "</div>";

		echo "<br>";

	}
	echo "<br>";


}

echo '</form>';
echo '</div>';



?>