<?php

if(!function_exists('cg_create_json_files_when_activating')){
	function cg_create_json_files_when_activating($GalleryID,$rowObject,$thumbSizesWp = [],$uploadFolder = [],$imagesDataArray=null,$galleryDBversion = 0, $RatingOverviewArray = [], $ExifDataAlreadySet = []){

		if($imagesDataArray!=null){
			$imagesDataArray[$rowObject->id] = array();
		}else{
			$imagesDataArray = array();
			$imagesDataArray[$rowObject->id] = array();
		}

		if(empty($uploadFolder)){
			$uploadFolder = wp_upload_dir();
		}

		$dirImageComments = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-comments/ids/'.$rowObject->id;

		if(cg_is_alternative_file_type($rowObject->ImgType)){
			$guid = $rowObject->guid;
			$imageWidth = 0;
			$imageHeight = 0;
			$imgSrcThumb = '';
			$imgSrcMedium = '';
			$imgSrcLarge = '';
			$imgSrcFull = $guid;
			$post_alt = get_post_meta($rowObject->WpUpload,'_wp_attachment_image_alt',true);
			// added since version 20.0.0
			if(cg_is_alternative_file_type_video($rowObject->ImgType)){
				$fileData = wp_get_attachment_metadata($rowObject->WpUpload);
				$imageHeight = (!empty($fileData['height'])) ? $fileData['height'] : 0;
				$imageWidth = (!empty($fileData['width'])) ? $fileData['width'] : 0;
			}
		}elseif($rowObject->ImgType=='con'){// then must be upload form entry
			$guid = '';
			$imageWidth = 300;
			$imageHeight = 200;
			$imgSrcThumb = '';
			$imgSrcMedium = '';
			$imgSrcLarge = '';
			$imgSrcFull = '';
			$rowObject->post_date = '';
			$rowObject->post_content = '';
			$rowObject->post_title = '';
			$rowObject->post_name = '';
			$rowObject->post_excerpt = '';
			$post_alt = '';
		}elseif($rowObject->ImgType=='ytb' || $rowObject->ImgType=='twt' || $rowObject->ImgType=='inst' || $rowObject->ImgType=='tkt'){// then must be upload form entry
			$guid = $rowObject->guid;
			$imgSrcFull =  $rowObject->guid;
			$imageWidth = 300;
			$imageHeight = 200;
			$imgSrcThumb =  $rowObject->guid;
			$imgSrcMedium =  $rowObject->guid;
			$imgSrcLarge =  $rowObject->guid;
			//$rowObject->post_date = '';
			//$rowObject->post_content = '';
			//$rowObject->post_title = '';
			//$rowObject->post_name = '';
			//$rowObject->post_excerpt = '';
			$post_alt = '';
		}else{

			$isEcommerceImageSale = false;
			if(!empty($rowObject->EcommerceEntry)){
				global $wpdb;
				$tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
				$EcommerceEntry = $rowObject->EcommerceEntry;
				$IsDownload = $wpdb->get_var( "SELECT IsDownload FROM $tablename_ecommerce_entries WHERE id = $EcommerceEntry LIMIT 1" );
				if(!empty($IsDownload)){
					$isEcommerceImageSale = true;
				}
			}

			$post_alt = get_post_meta($rowObject->WpUpload,'_wp_attachment_image_alt',true);
			if(!empty($isEcommerceImageSale)){
                if(!empty($rowObject->PdfPreview)){
                    $meta = wp_get_attachment_metadata( $rowObject->PdfPreview );
                }else{
                    $meta = wp_get_attachment_metadata( $rowObject->WpUpload );
                }
				$folderPath = substr($meta['file'],0, strrpos($meta['file'],'/'));
				$fileName = substr($meta['file'],strrpos($meta['file'],'/')+1,strlen($meta['file']) );
				$imgSrcFull=$uploadFolder['baseurl'].'/'.$folderPath.'/'.$fileName;
				$imgSrcThumb=$uploadFolder['baseurl'].'/'.$folderPath.'/'.(!empty($meta['sizes']['thumbnail']['file'])) ? $meta['sizes']['thumbnail']['file'] : $fileName;
				$imgSrcMedium=$uploadFolder['baseurl'].'/'.$folderPath.'/'.(!empty($meta['sizes']['medium']['file'])) ? $meta['sizes']['medium']['file'] : $fileName;
				$imgSrcLarge=$uploadFolder['baseurl'].'/'.$folderPath.'/'.((isset($meta['sizes']['large']['file'])) ? $meta['sizes']['large']['file'] : $fileName);
				$imageWidth = $meta['width'];
				$imageHeight = $meta['height'];
			}else{
				// posts fields
				$imgSrcThumb=wp_get_attachment_image_src($rowObject->WpUpload, 'thumbnail');
				$imgSrcThumb=(isset($imgSrcThumb[0])) ? $imgSrcThumb[0] : '';
				$imgSrcMedium=wp_get_attachment_image_src($rowObject->WpUpload, 'medium');
				$imgSrcMedium=(isset($imgSrcMedium[0])) ? $imgSrcMedium[0] : '';
				$imgSrcLarge=wp_get_attachment_image_src($rowObject->WpUpload, 'large');
				$imgSrcLarge=(isset($imgSrcLarge[0])) ? $imgSrcLarge[0] : '';
				$imgSrcFull=wp_get_attachment_image_src($rowObject->WpUpload, 'full');
				$imgSrcFull=(isset($imgSrcFull[0])) ? $imgSrcFull[0] : '';
				if(!empty($rowObject->Width)){
					$imageWidth = $rowObject->Width;
					$imageHeight = $rowObject->Height;
				}else{
					$imageWidth = $imgSrcFull[1];
					$imageHeight = $imgSrcFull[2];
				}
			}
			$guid = $imgSrcFull;

		}

		/*        $imagesDataArray[$rowObject->id]['thumbnail_size_w'] = $thumbSizesWp['thumbnail_size_w'];
				$imagesDataArray[$rowObject->id]['medium_size_w'] = $thumbSizesWp['medium_size_w'];
				$imagesDataArray[$rowObject->id]['large_size_w'] = $thumbSizesWp['large_size_w'];*/

		$imagesDataArray[$rowObject->id]['id'] = $rowObject->id;
		$imagesDataArray[$rowObject->id]['thumbnail'] = $imgSrcThumb;
		$imagesDataArray[$rowObject->id]['medium'] = $imgSrcMedium;
		$imagesDataArray[$rowObject->id]['large'] = $imgSrcLarge;
		$imagesDataArray[$rowObject->id]['full'] = $imgSrcFull;

		$imagesDataArray[$rowObject->id]['post_date'] = $rowObject->post_date;
		$imagesDataArray[$rowObject->id]['post_content'] = $rowObject->post_content;
		$imagesDataArray[$rowObject->id]['post_title'] = $rowObject->post_title;
		$imagesDataArray[$rowObject->id]['post_name'] = $rowObject->post_name;
		$imagesDataArray[$rowObject->id]['post_caption'] = $rowObject->post_excerpt;
		$imagesDataArray[$rowObject->id]['post_alt'] = $post_alt;
		$imagesDataArray[$rowObject->id]['guid'] = $guid;

		// hier pauschal setzen
		$imagesDataArray[$rowObject->id]['display_name'] = '';

		$imageRatingArray = array();

		/*        $imageRatingArray['thumbnail_size_w'] = $thumbSizesWp['thumbnail_size_w'];
				$imageRatingArray['medium_size_w'] = $thumbSizesWp['medium_size_w'];
				$imageRatingArray['large_size_w'] = $thumbSizesWp['large_size_w'];*/

		$imageRatingArray['id'] = $rowObject->id;
		$imageRatingArray['thumbnail'] = $imgSrcThumb;
		$imageRatingArray['medium'] = $imgSrcMedium;
		$imageRatingArray['large'] = $imgSrcLarge;
		$imageRatingArray['full'] = $imgSrcFull;

		$imageRatingArray['post_date'] = $rowObject->post_date;
		$imageRatingArray['post_content'] = $rowObject->post_content;
		$imageRatingArray['post_title'] = $rowObject->post_title;
		$imageRatingArray['post_name'] = $rowObject->post_name;
		$imageRatingArray['post_caption'] = $rowObject->post_excerpt;
		$imageRatingArray['post_alt'] = $post_alt;
		$imageRatingArray['guid'] = $guid;

		// tablename fields
		$imagesDataArray[$rowObject->id]['rowid'] = intval($rowObject->rowid);
		$imagesDataArray[$rowObject->id]['PositionNumber'] = intval($rowObject->PositionNumber);
		$imagesDataArray[$rowObject->id]['Timestamp'] = intval($rowObject->Timestamp);
		$imagesDataArray[$rowObject->id]['NamePic'] = $rowObject->NamePic;
		$imagesDataArray[$rowObject->id]['ImgType'] = $rowObject->ImgType;
		$imagesDataArray[$rowObject->id]['GalleryID'] = intval($rowObject->GalleryID);
		$imagesDataArray[$rowObject->id]['Active'] = intval($rowObject->Active);
		$imagesDataArray[$rowObject->id]['Winner'] = intval($rowObject->Winner);
		$imagesDataArray[$rowObject->id]['Informed'] = intval($rowObject->Informed);
		$imagesDataArray[$rowObject->id]['WpUpload'] = intval($rowObject->WpUpload);
		$imagesDataArray[$rowObject->id]['Width'] = intval($imageWidth);
		$imagesDataArray[$rowObject->id]['Height'] = intval($imageHeight);
		$imagesDataArray[$rowObject->id]['rSource'] = intval($rowObject->rSource);
		$imagesDataArray[$rowObject->id]['rThumb'] = intval($rowObject->rThumb);
		$imagesDataArray[$rowObject->id]['Category'] = intval($rowObject->Category);
		//$imagesDataArray[$rowObject->id]['WpUserId'] = intval($rowObject->WpUserId);
		$imagesDataArray[$rowObject->id]['WpPage'] = intval($rowObject->WpPage);
		$imagesDataArray[$rowObject->id]['WpPageUser'] = intval($rowObject->WpPageUser);
		$imagesDataArray[$rowObject->id]['WpPageNoVoting'] = intval($rowObject->WpPageNoVoting);
		$imagesDataArray[$rowObject->id]['WpPageWinner'] = intval($rowObject->WpPageWinner);
		$imagesDataArray[$rowObject->id]['WpPageEcommerce'] = intval($rowObject->WpPageEcommerce);
		$imagesDataArray[$rowObject->id]['EcommerceEntry'] = intval($rowObject->EcommerceEntry);
		$imagesDataArray[$rowObject->id]['PdfPreview'] = intval($rowObject->PdfPreview);
        if(!empty($rowObject->PdfPreview)){
            $PdfPreviewImage = wp_get_attachment_image_src($rowObject->PdfPreview, 'full');
            $PdfPreviewImageLarge = wp_get_attachment_image_src($rowObject->PdfPreview, 'large');
            // important to set PdfOriginal
            $imagesDataArray[$rowObject->id]['PdfOriginal'] = $guid;
            $imagesDataArray[$rowObject->id]['full'] = $PdfPreviewImage[0];
            $imagesDataArray[$rowObject->id]['guid'] = $PdfPreviewImage[0];
            $imagesDataArray[$rowObject->id]['Width'] = $PdfPreviewImage[1];
            $imagesDataArray[$rowObject->id]['Height'] = $PdfPreviewImage[2];
            $imagesDataArray[$rowObject->id]['thumbnail'] = $PdfPreviewImageLarge[0];
            $imagesDataArray[$rowObject->id]['medium'] = $PdfPreviewImageLarge[0];
            $imagesDataArray[$rowObject->id]['large'] = $PdfPreviewImageLarge[0];
            $imagesDataArray[$rowObject->id]['ImgType'] = 'png';
            $imagesDataArray[$rowObject->id]['post_mime_type'] = 'image/png';
        }
		$imageRatingArray['rowid'] = intval($rowObject->rowid);
		$imageRatingArray['PositionNumber'] = intval($rowObject->PositionNumber);
		$imageRatingArray['Timestamp'] = intval($rowObject->Timestamp);
		$imageRatingArray['NamePic'] = $rowObject->NamePic;
		$imageRatingArray['ImgType'] = $rowObject->ImgType;
		$imageRatingArray['Rating'] = intval($rowObject->Rating);
		$imageRatingArray['GalleryID'] = intval($rowObject->GalleryID);
		$imageRatingArray['Active'] = intval($rowObject->Active);
		$imageRatingArray['Winner'] = intval($rowObject->Winner);
		$imageRatingArray['Informed'] = intval($rowObject->Informed);
		$imageRatingArray['WpUpload'] = intval($rowObject->WpUpload);
		$imageRatingArray['Width'] = intval($imageWidth);
		$imageRatingArray['Height'] = intval($imageHeight);
		$imageRatingArray['rSource'] = intval($rowObject->rSource);
		$imageRatingArray['rThumb'] = intval($rowObject->rThumb);
		$imageRatingArray['Category'] = intval($rowObject->Category);
		//$imageRatingArray['WpUserId']= intval($rowObject->WpUserId);
		$imageRatingArray['WpPage']= intval($rowObject->WpPage);
		$imageRatingArray['WpPageUser']= intval($rowObject->WpPageUser);
		$imageRatingArray['WpPageNoVoting']= intval($rowObject->WpPageNoVoting);
		$imageRatingArray['WpPageWinner']= intval($rowObject->WpPageWinner);
		$imageRatingArray['WpPageEcommerce']= intval($rowObject->WpPageEcommerce);
		$imageRatingArray['EcommerceEntry']= intval($rowObject->EcommerceEntry);
        $imageRatingArray['PdfPreview'] = intval($rowObject->PdfPreview);
        if(!empty($rowObject->PdfPreview)){
            $imageRatingArray['PdfOriginal'] = $guid;
            $imageRatingArray['full'] = $PdfPreviewImage[0];
            $imageRatingArray['guid'] = $PdfPreviewImage[0];
            $imageRatingArray['Width'] = $PdfPreviewImage[1];
            $imageRatingArray['Height'] = $PdfPreviewImage[2];
            $imageRatingArray['thumbnail'] = $PdfPreviewImageLarge[0];
            $imageRatingArray['medium'] = $PdfPreviewImageLarge[0];
            $imageRatingArray['large'] = $PdfPreviewImageLarge[0];
            $imageRatingArray['ImgType'] = 'png';
            $imageRatingArray['post_mime_type'] = 'image/png';
        }

		// rating comment save here
		$imageRatingArray['CountC'] = intval($rowObject->CountC);
		// since 21.0.0
		$countCtoReview = 0;
		if(is_dir($dirImageComments)){
			$dirImageCommentsFiles = glob($dirImageComments.'/*.json');
			$countCtotal = count($dirImageCommentsFiles);
			foreach ($dirImageCommentsFiles as $dirImageCommentsFile){
				$dirImageCommentsFileData = json_decode(file_get_contents($dirImageCommentsFile),true);
				if(!empty($dirImageCommentsFileData[key($dirImageCommentsFileData)]['Active']) && $dirImageCommentsFileData[key($dirImageCommentsFileData)]['Active']==2 && empty($dirImageCommentsFileData[key($dirImageCommentsFileData)]['ReviewTstamp'])){
					$countCtoReview++;
				}
			}
			if($countCtotal){
				$imageRatingArray['CountC'] = $countCtotal - $countCtoReview;
			}else{
				$imageRatingArray['CountC'] = 0;
			}
		}

		//  $imageRatingArray['CountC'] =intval($rowObject->CountCtoReview);
		$imageRatingArray['CountR'] = intval($rowObject->CountR);
		$imageRatingArray['CountS'] = intval($rowObject->CountS);
		$imageRatingArray['Rating'] = intval($rowObject->Rating);
		$imageRatingArray['addCountS'] = intval($rowObject->addCountS);
		$imageRatingArray['addCountR1'] = intval($rowObject->addCountR1);
		$imageRatingArray['addCountR2'] = intval($rowObject->addCountR2);
		$imageRatingArray['addCountR3'] = intval($rowObject->addCountR3);
		$imageRatingArray['addCountR4'] = intval($rowObject->addCountR4);
		$imageRatingArray['addCountR5'] = intval($rowObject->addCountR5);
		$imageRatingArray['addCountR6'] = intval($rowObject->addCountR6);
		$imageRatingArray['addCountR7'] = intval($rowObject->addCountR7);
		$imageRatingArray['addCountR8'] = intval($rowObject->addCountR8);
		$imageRatingArray['addCountR9'] = intval($rowObject->addCountR9);
		$imageRatingArray['addCountR10'] = intval($rowObject->addCountR10);
		$imageRatingArray['CountR1'] = intval($rowObject->CountR1);
		$imageRatingArray['CountR2'] = intval($rowObject->CountR2);
		$imageRatingArray['CountR3'] = intval($rowObject->CountR3);
		$imageRatingArray['CountR4'] = intval($rowObject->CountR4);
		$imageRatingArray['CountR5'] = intval($rowObject->CountR5);
		$imageRatingArray['CountR6'] = intval($rowObject->CountR6);
		$imageRatingArray['CountR7'] = intval($rowObject->CountR7);
		$imageRatingArray['CountR8'] = intval($rowObject->CountR8);
		$imageRatingArray['CountR9'] = intval($rowObject->CountR9);
		$imageRatingArray['CountR10'] = intval($rowObject->CountR10);
		$imageRatingArray['Exif'] = '';

		if(!empty($RatingOverviewArray)){
			if(!empty($RatingOverviewArray[$rowObject->id])){
				$imageRatingArray['CountC'] = (!empty($RatingOverviewArray[$rowObject->id]['CountC']) ? $RatingOverviewArray[$rowObject->id]['CountC'] : 0);
				//$imageRatingArray['CountCtoReview'] = (!empty($RatingOverviewArray[$rowObject->id]['CountCtoReview']) ? $RatingOverviewArray[$rowObject->id]['CountCtoReview'] : 0);// added since 21.0.0
				$imageRatingArray['CountS'] = (!empty($RatingOverviewArray[$rowObject->id]['CountS']) ? $RatingOverviewArray[$rowObject->id]['CountS'] : 0);
				$imageRatingArray['CountR'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR']) ? $RatingOverviewArray[$rowObject->id]['CountR'] : 0);
				$imageRatingArray['Rating'] = (!empty($RatingOverviewArray[$rowObject->id]['Rating']) ? $RatingOverviewArray[$rowObject->id]['Rating'] : 0);
				$imageRatingArray['CountR1'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR1']) ? $RatingOverviewArray[$rowObject->id]['CountR1'] : 0);
				$imageRatingArray['CountR2'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR2']) ? $RatingOverviewArray[$rowObject->id]['CountR2'] : 0);
				$imageRatingArray['CountR3'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR3']) ? $RatingOverviewArray[$rowObject->id]['CountR3'] : 0);
				$imageRatingArray['CountR4'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR4']) ? $RatingOverviewArray[$rowObject->id]['CountR4'] : 0);
				$imageRatingArray['CountR5'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR5']) ? $RatingOverviewArray[$rowObject->id]['CountR5'] : 0);
				$imageRatingArray['CountR6'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR6']) ? $RatingOverviewArray[$rowObject->id]['CountR6'] : 0);
				$imageRatingArray['CountR7'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR7']) ? $RatingOverviewArray[$rowObject->id]['CountR7'] : 0);
				$imageRatingArray['CountR8'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR8']) ? $RatingOverviewArray[$rowObject->id]['CountR8'] : 0);
				$imageRatingArray['CountR9'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR9']) ? $RatingOverviewArray[$rowObject->id]['CountR9'] : 0);
				$imageRatingArray['CountR10'] = (!empty($RatingOverviewArray[$rowObject->id]['CountR10']) ? $RatingOverviewArray[$rowObject->id]['CountR10'] : 0);
			}
		}

		// var_dump($imageRatingArray['addCountR5']);

		$correctDateTimeOriginal = false;
		$possibleCorrectDateTimeOriginal = false;
		if(!empty($galleryDBversion) && floatval($galleryDBversion)<12.23){
			$possibleCorrectDateTimeOriginal = true;
		}
		$hasExif = true;
		$exifDataArray = array();
		if($rowObject->Exif == '' or $rowObject->Exif == NULL){
			$hasExif = false;
		}elseif($rowObject->Exif != '' && $rowObject->Exif != '0'){
			$exifDataArray = unserialize($rowObject->Exif);
			if(empty($exifDataArray['DateTimeOriginal']) && $possibleCorrectDateTimeOriginal){// this EXIF value DateTimeOriginal comes later, is for possible correction required
				$correctDateTimeOriginal = true;
			}
		}

		if(!empty($ExifDataAlreadySet) && isset($ExifDataAlreadySet[$rowObject->id])){
			// since 18.0.0 exif data in image-data... files will be not really used, can be removed later completely
			$imageRatingArray['Exif'] = $ExifDataAlreadySet[$rowObject->id];
		}else{
			// set exif data
			if((($hasExif==false && empty($exifDataArray)) OR $correctDateTimeOriginal) && !empty($rowObject->WpUpload)){
				// Exif data will be set in image-data-.json only in version before 18
				if(!empty($galleryDBversion) && floatval($galleryDBversion)<18){
					// since 18.0.0 exif data in image-data... files will be not really used, can be removed later completely
					if($rowObject->ImgType!='ytb' && $rowObject->ImgType!='twt' && $rowObject->ImgType!='inst' && $rowObject->ImgType!='tkt'){
						$imageRatingArray['Exif'] = cg_create_exif_data_and_add_to_database($rowObject->id,$rowObject->WpUpload);
					}
				}else{// but here will still be created and added to database
					// since 18.0.0 exif data in image-data... files will be not really used, can be removed later completely
					if($rowObject->ImgType!='ytb' && $rowObject->ImgType!='twt' && $rowObject->ImgType!='inst' && $rowObject->ImgType!='tkt'){
						cg_create_exif_data_and_add_to_database($rowObject->id,$rowObject->WpUpload);
					}
				}
			}else{
				$imageRatingArray['Exif'] = $exifDataArray;
			}
		}

		// set rating data
		$jsonFile = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-data/image-data-'.$rowObject->id.'.json';
		$fp = fopen($jsonFile, 'w');
		fwrite($fp, json_encode($imageRatingArray));
		fclose($fp);

		cg_create_comments_json_file_when_activating_image($uploadFolder,$GalleryID,$rowObject->id);

		// leeres Info file wird kreiert falls noch nicht existiert
		if(!is_file($uploadFolder['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-info/image-info-".$rowObject->id.".json")){

			$jsonFile = $uploadFolder['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-info/image-info-".$rowObject->id.".json";
			$fp = fopen($jsonFile, 'w');
			fwrite($fp, json_encode(array()));
			fclose($fp);

		}

		return $imagesDataArray;


	}
}

if(!function_exists('cg_create_comments_json_file_when_activating_image')){
	function cg_create_comments_json_file_when_activating_image($uploadFolder,$GalleryID,$imageId){

		$imageCommentsArray = array();
		$dirImageComments = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-comments/ids/'.$imageId;
		$already_copied_insert_ids = [];
		if(is_dir($dirImageComments)){
			// file can be used for future repair
			$dirImageCommentsFiles = glob($dirImageComments.'/*.json');
			if(count($dirImageCommentsFiles)){
				// important, so top down in json
				$dirImageCommentsFiles = array_reverse($dirImageCommentsFiles, true);
				foreach ($dirImageCommentsFiles as $dirImageCommentsFile){
					$comment = json_decode(file_get_contents($dirImageCommentsFile));
					// is json file, this why this foreach
					foreach ($comment as $commentId => $commentData){
						$imageCommentsArray[$commentId] = array();
						$imageCommentsArray[$commentId]['date'] = date("Y/m/d, G:i",$commentData->timestamp);
						$imageCommentsArray[$commentId]['timestamp'] = $commentData->timestamp;
						$imageCommentsArray[$commentId]['name'] = $commentData->name;
						$imageCommentsArray[$commentId]['comment'] = $commentData->comment;
						$imageCommentsArray[$commentId]['Active'] = (!empty($commentData->Active)) ? $commentData->Active : '';
						$imageCommentsArray[$commentId]['ReviewTstamp'] = (!empty($commentData->ReviewTstamp)) ? $commentData->ReviewTstamp : '';
						if(!empty($commentData->WpUserId)){// better to check here for sure
							// WpUserId and IP will be not set in new comments since 23.1.3
							$imageCommentsArray[$commentId]['WpUserId'] = $commentData->WpUserId;
						}
						if(!empty($commentData->userIP)){// better to check here for sure
							// WpUserId and IP will be not set in new comments since 23.1.3
							$imageCommentsArray[$commentId]['userIP'] = $commentData->userIP;
						}
						if(isset($commentData->IsWpUser)){// will be set since version 21.3.1
							$imageCommentsArray[$commentId]['IsWpUser'] = $commentData->IsWpUser;
						}
						if(isset($commentData->insert_id)){// will be set since version 21.3.1
							$imageCommentsArray[$commentId]['insert_id'] = $commentData->insert_id;
							$already_copied_insert_ids[] = $commentData->insert_id;
						}
					}
				}
			}
		}

		global $wpdb;
		$tablename_comments = $wpdb->prefix . "contest_gal1ery_comments";

		// desc important so top down in json
		$imageComments = $wpdb->get_results("SELECT * FROM $tablename_comments WHERE pid = $imageId ORDER BY id DESC");

		if(count($imageComments)){// is for old comments, before insert_id and no WpUserId in the comments logic
			foreach($imageComments as $comment){
				if(in_array($comment->id,$already_copied_insert_ids)===false){
					$imageCommentsArray[$comment->id] = array();
					$imageCommentsArray[$comment->id]['date'] = date("Y/m/d, G:i",$comment->Timestamp);
					$imageCommentsArray[$comment->id]['timestamp'] = $comment->Timestamp;
					$imageCommentsArray[$comment->id]['name'] = $comment->Name;
					$imageCommentsArray[$comment->id]['comment'] = $comment->Comment;
					$imageCommentsArray[$comment->id]['Active'] = $comment->Active;
					$imageCommentsArray[$comment->id]['ReviewTstamp'] = $comment->ReviewTstamp;
					$imageCommentsArray[$comment->id]['IsWpUser'] = 0;
					if(!empty($comment->WpUserId)){
						$imageCommentsArray[$comment->id]['IsWpUser'] = 1;
					}
					$imageCommentsArray[$comment->id]['insert_id'] = $comment->id;
				}
			}
		}

		$jsonFile = $uploadFolder['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-comments/image-comments-".$imageId.".json";
		$fp = fopen($jsonFile, 'w');
		fwrite($fp, json_encode($imageCommentsArray));
		fclose($fp);

	}
}