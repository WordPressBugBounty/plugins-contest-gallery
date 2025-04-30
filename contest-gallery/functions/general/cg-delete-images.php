<?php

add_action('cg_delete_images','cg_delete_images');
if(!function_exists('cg_delete_images')){
    function cg_delete_images($GalleryID,$deleteValuesArray,$deletedWpUploads = array(),$DeleteFromStorageIfDeletedInFrontend = false,$isConsecutiveDeletionOfDeletedWpUploads = false, $MultipleFilesToDelete = []){

        global $wpdb;

// Set table names
	    $tablename = $wpdb->prefix . "contest_gal1ery";
	    $tablename_posts = $wpdb->prefix . "posts";
	    $tablenameEntries = $wpdb->prefix . "contest_gal1ery_entries";
        $tablenameComments = $wpdb->prefix . "contest_gal1ery_comments";
        $tablenameIp = $wpdb->prefix . "contest_gal1ery_ip";
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
        $tablename_wp_pdf_previews = $wpdb->base_prefix . "contest_gal1ery_pdf_previews";

// Set table names --- END

        /*	$imageUnlinkOrigin = @$_POST['imageUnlinkOrigin'];
            $imageUnlink300 = @$_POST['imageUnlink300'];
            $imageUnlink624 = @$_POST['imageUnlink624'];
            $imageUnlink1024 = @$_POST['imageUnlink1024'];
            $imageUnlink1600 = @$_POST['imageUnlink1600'];
            $imageUnlink1920 = @$_POST['imageUnlink1920'];
            $imageFbHTMLUnlink = @$_POST['imageFbHTMLUnlink'];*/

// Pics vom Server l�schen

// Wordpress Uploadordner bestimmen. Array wird zur�ck gegeben.
        $upload_dir = wp_upload_dir();
	    $imageArray = [];

        if(!is_dir($upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/frontend-added-or-removed-images')){
            mkdir($upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/frontend-added-or-removed-images',0755,true);
        }

        foreach($deleteValuesArray as $key => $value){
            // important!!! do not remove!!! might come from $_POST['cg_delete'] and has to be absint for sure!!!
            // found by patchstack.com
            $value = absint($value);
            /*echo '<input type="hidden" disabled name="imageUnlinkOrigin[]" value="/contest-gallery/gallery-id-'.$GalleryID.'/'.$value->Timestamp.'_'.$value->NamePic.'.'.$value->ImgType.'" class="image-delete">';
            echo '<input type="hidden" disabled name="imageUnlink300[]" value="/contest-gallery/gallery-id-'.$GalleryID.'/'.$value->Timestamp.'_'.$value->NamePic.'-300width.'.$value->ImgType.'" class="image-delete">';
            echo '<input type="hidden" disabled name="imageUnlink624[]" value="/contest-gallery/gallery-id-'.$GalleryID.'/'.$value->Timestamp.'_'.$value->NamePic.'-624width.'.$value->ImgType.'" class="image-delete">';
            echo '<input type="hidden" disabled name="imageUnlink1024[]" value="/contest-gallery/gallery-id-'.$GalleryID.'/'.$value->Timestamp.'_'.$value->NamePic.'-1024width.'.$value->ImgType.'" class="image-delete">';
            echo '<input type="hidden" disabled name="imageUnlink1600[]" value="/contest-gallery/gallery-id-'.$GalleryID.'/'.$value->Timestamp.'_'.$value->NamePic.'-1600width.'.$value->ImgType.'" class="image-delete">';
            echo '<input type="hidden" disabled name="imageUnlink1920[]" value="/contest-gallery/gallery-id-'.$GalleryID.'/'.$value->Timestamp.'_'.$value->NamePic.'-1920width.'.$value->ImgType.'" class="image-delete">';
            echo '<input type="hidden" disabled name="imageFbHTMLUnlink[]" value="/contest-gallery/gallery-id-'.$GalleryID.'/'.$value->Timestamp.'_'.$value->NamePic.'.html" class="image-delete">';*/

            // simply create empty file for later check
            $jsonFile = $upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/frontend-added-or-removed-images/'.$value.'.txt';
            $fp = fopen($jsonFile, 'w');
            fwrite($fp, '');
            fclose($fp);

            $imageData = $wpdb->get_row( "SELECT * FROM $tablename WHERE id = '$value' ");

            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic.".".$imageData->ImgType."")){
                @unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic.".".$imageData->ImgType."");
            }

            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-300width.".$imageData->ImgType."")){
                @unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-300width.".$imageData->ImgType."");
            }

            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-624width.".$imageData->ImgType."")){
                @unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-624width.".$imageData->ImgType."");
            }

            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-1024width.".$imageData->ImgType."")){
                @unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-1024width.".$imageData->ImgType."");
            }

            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-1600width.".$imageData->ImgType."")){
                @unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-1600width.".$imageData->ImgType."");
            }

            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-1920width.".$imageData->ImgType."")){
                @unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."-1920width.".$imageData->ImgType."");
            }

            /*			if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic.".html")){
                            @unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic.".html");
                        }*/

            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."413.html")){
                @unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/".$imageData->Timestamp."_".$imageData->NamePic."413.html");
            }

            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-data/image-data-".$value.".json")){
                unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-data/image-data-".$value.".json");
            }
            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-comments/image-comments-".$value.".json")){
                unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-comments/image-comments-".$value.".json");
            }

            // remove possible comment files and comment folder
            $commentsFolder = $upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-comments/ids/".$value;
            if(is_dir($commentsFolder)){
                $commentsFiles = glob($commentsFolder.'/*');//  get all files for sure, als might not json files be there
                if(count($commentsFiles)){
                    foreach ($commentsFiles as $commentsFile) {
                        unlink($commentsFile);
                    }
                }
                rmdir($commentsFolder);
            }

            if(file_exists($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-info/image-info-".$value.".json")){
                unlink($upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/image-info/image-info-".$value.".json");
            }

            if(!empty($imageArray[$value])){
                unset($imageArray[$value]);
            }

            if(!empty($imageData->WpPage)){
                wp_delete_post($imageData->WpPage,true);
            }
            if(!empty($imageData->WpPageUser)){
                wp_delete_post($imageData->WpPageUser,true);
            }
            if(!empty($imageData->WpPageNoVoting)){
                wp_delete_post($imageData->WpPageNoVoting,true);
            }
            if(!empty($imageData->WpPageWinner)){
                wp_delete_post($imageData->WpPageWinner,true);
            }
            if(!empty($imageData->WpPageEcommerce)){
                wp_delete_post($imageData->WpPageEcommerce,true);
            }

            $deleteQuery = 'DELETE FROM ' . $tablename . ' WHERE';
            $deleteQuery .= ' id = %d';

            $deleteEntries = 'DELETE FROM ' . $tablenameEntries . ' WHERE';
            $deleteEntries .= ' pid = %d';

            $deleteComments = 'DELETE FROM ' . $tablenameComments . ' WHERE';
            $deleteComments .= ' pid = %d';

            $deleteRating = 'DELETE FROM ' . $tablenameIp . ' WHERE';
            $deleteRating .= ' pid = %d';

            $deleteEcommerceEntry = 'DELETE FROM ' . $tablename_ecommerce_entries . ' WHERE';
            $deleteEcommerceEntry .= ' pid = %d';

            $deleteParameters = '';
            $deleteParameters .= $value;

            $wpdb->query( $wpdb->prepare(
                "
                    $deleteQuery
                ",
                $deleteParameters
            ));

            $wpdb->query( $wpdb->prepare(
                "
                    $deleteEntries
                ",
                $deleteParameters
            ));

            $wpdb->query( $wpdb->prepare(
                "
                    $deleteComments
                ",
                $deleteParameters
            ));

            $wpdb->query( $wpdb->prepare(
                "
                    $deleteRating
                ",
                $deleteParameters
            ));

            $wpdb->query( $wpdb->prepare(
                "
                    $deleteEcommerceEntry
                ",
                $deleteParameters
            ));

            if(((!empty($_POST['cgDeleteOriginalImageSourceAlso']) OR $DeleteFromStorageIfDeletedInFrontend) AND !$isConsecutiveDeletionOfDeletedWpUploads) && !empty($imageData->WpUpload)){
				if($imageData->ImgType==='ytb' || $imageData->ImgType==='twt' || $imageData->ImgType==='inst' || $imageData->ImgType==='tkt'){
					$wpdb->query($wpdb->prepare(
						"
				DELETE FROM $tablename_posts WHERE ID = %d
			",
						$imageData->WpUpload
					));
				}else{
					wp_delete_attachment($imageData->WpUpload);
					$deletedWpUploads[] = $imageData->WpUpload;

                    if(!empty($imageData->PdfPreview)){
                        // delete row in $tablename_wp_pdf_previews
                        $WpUploadToDelete = $imageData->WpUpload;
                        $wpdb->query("DELETE FROM $tablename_wp_pdf_previews WHERE WpUpload = $WpUploadToDelete");
                        wp_delete_attachment($imageData->PdfPreview);
                    }
                    if(!empty($imageData->MultipleFiles) && $imageData->MultipleFiles!='""'){
                        $MultipleFilesArray = unserialize($imageData->MultipleFiles);
                        foreach ($MultipleFilesArray as $MultipleFile){
                            if(!empty($MultipleFile['PdfPreview'])){
                                // the $MultipleFile['WpUpload'] will be deleted in cg_delete_images_of_deleted_wp_uploads then
                                // delete row in $tablename_wp_pdf_previews
                                $WpUploadToDelete = $MultipleFile['WpUpload'];
                                $wpdb->query("DELETE FROM $tablename_wp_pdf_previews WHERE WpUpload = $WpUploadToDelete");
                                $PdfPreviewToDelete = $MultipleFile['PdfPreview'];
                                wp_delete_attachment($PdfPreviewToDelete);
                            }
                        }
                    }
				}

            }

        }

	    if((!empty($MultipleFilesToDelete)) && ((!empty($_POST['cgDeleteOriginalImageSourceAlso']) OR $DeleteFromStorageIfDeletedInFrontend) AND !$isConsecutiveDeletionOfDeletedWpUploads)){
            foreach ($MultipleFilesToDelete as $id => $fileDataForPost){
                foreach ($fileDataForPost as $order => $fileData){
                    if(in_array($fileData['WpUpload'],$deletedWpUploads)===false){
                        wp_delete_attachment($fileData['WpUpload']);
                        $deletedWpUploads[] = $fileData['WpUpload'];
                    }
                }
            }
        }

        // korrekturskript wegen update 10.9.5.0.0 wo galery id nicht mitgechickt wurde und deswegen images nicht gelöscht worden sind

        // korrekturskript ENDE

	    // images.json was used in older versions before 22.0.0, irrelevant now, but still will checked in many paces, has to be removed in the fture
        if(empty($imageArray) || !is_array($imageArray)){// then data was corrected without having activated images
            $imageArray = [];
        }

        return $deletedWpUploads;

    }
}

?>