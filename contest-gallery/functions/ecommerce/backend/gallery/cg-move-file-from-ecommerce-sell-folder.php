<?php
if(!function_exists('cg_move_gallery_changes_file_from_ecommerce_sale_folder')){
    function cg_move_gallery_changes_file_from_ecommerce_sale_folder($GalleryID)
    {
        // ecommerceEntryAndDownload new downloads for sale
        if (!empty($_POST['removeEcommerceEntryWpUploadIds'])) {
            /*var_dump('removeEcommerceEntryWpUploadIds');
            echo "<pre>";
            print_r($_POST['removeEcommerceEntryWpUploadIds']);
            echo "</pre>";*/
            foreach ($_POST['removeEcommerceEntryWpUploadIds'] as $entryId => $WpUploadIdsArray) {

                $ecommerceEntryId = key($WpUploadIdsArray);
                $WpUploadIdsArray = $WpUploadIdsArray[$ecommerceEntryId];

                if(!empty($WpUploadIdsArray)){
                    $removedWpUploadIdsFromSale = json_decode(stripslashes(sanitize_text_field($WpUploadIdsArray)),true);
                    /*echo "<pre>";
                    print_r($removedWpUploadIdsFromSale);
                    echo "</pre>";
                    var_dump($entryId);
                    var_dump($ecommerceEntryId);*/
                    cg_move_file_from_ecommerce_sale_folder($entryId, $GalleryID, $ecommerceEntryId, $removedWpUploadIdsFromSale);
                }

            }

        }

    }
}
if(!function_exists('cg_move_file_from_ecommerce_sale_folder')){
    function cg_move_file_from_ecommerce_sale_folder($realId, $GalleryID, $ecommerceEntryId, $removedWpUploadIdsFromSale = [],$isFromDeleteGalleryOrUninstallPlugin = false){

        $wp_upload_dir = wp_upload_dir();
        global $wpdb;
        $tablePosts = $wpdb->prefix . "posts";
        $tablePostMeta = $wpdb->prefix . "postmeta";
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
        $isAllWpUploadsSuccessfulMoved = true;

	    $ecommerceFile = $wpdb->get_row( "SELECT * FROM $tablename_ecommerce_entries WHERE id='$ecommerceEntryId'" );

	    $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId;

        $WpUploadFilesPosts = unserialize($ecommerceFile->WpUploadFilesPosts);
        $WpUploadFilesPostMeta = unserialize($ecommerceFile->WpUploadFilesPostMeta);

		/*
		echo "<pre>";
			print_r($WpUploadFilesPosts);
		echo "</pre>";

		echo "<pre>";
			print_r($WpUploadFilesPostMeta);
		echo "</pre>";
		*/

		if(empty($WpUploadFilesPostMeta)){
			$WpUploadFilesPostMeta = []; // do not remove
		}

/*        echo "<pre>";
        print_r($WpUploadFilesPostMeta);
        echo "</pre>";*/

   //     die;
      //var_dump('$WpUploadFilesPostMeta');
        //echo "<br>";
        //echo "<pre>";
        //print_r($WpUploadFilesPostMeta);
               // echo "</pre>";
        //echo "<br>";
		//var_dump('$removedWpUploadIdsFromSale');
		//var_dump($removedWpUploadIdsFromSale);

        foreach ($WpUploadFilesPostMeta as $WpUploadId => $WpUploadFilePostMeta){

            if(!empty($removedWpUploadIdsFromSale) && in_array($WpUploadId,$removedWpUploadIdsFromSale)===false){continue;}

           $ecommerceFileFolderWpUploadFolder = $ecommerceFileFolder.'/wp-upload-id-'.$WpUploadId;

            $wp_upload_dir_of_file = $wp_upload_dir['basedir'].'/'.substr($WpUploadFilePostMeta['_wp_attached_file'],0,strrpos($WpUploadFilePostMeta['_wp_attached_file'],'/'));// folder without / at the end
       //var_dump($ecommerceFileFolderWpUploadFolder);
        //echo "<br>";
        //echo "<br>";
        //var_dump($wp_upload_dir_of_file);
            if(!is_dir($wp_upload_dir_of_file)){// to go sure, folder might be deleted by the time
                mkdir($wp_upload_dir_of_file,0755,true);
            }
/*            echo "<br>";
            echo "<br>";
        var_dump($WpUploadFilePostMeta['_wp_attached_file']);*/
            $filename = substr($WpUploadFilePostMeta['_wp_attached_file'],strrpos($WpUploadFilePostMeta['_wp_attached_file'],'/')+1,strlen($WpUploadFilePostMeta['_wp_attached_file']));
/*            echo "<br>";
            echo "<br>";
            var_dump($filename);

            echo "<pre>";
            print_r($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes']);
            echo "</pre>";
            echo "<br>";
            echo "<br>";
            var_dump($ecommerceFileFolder);

            echo "<br>";
            echo "<br>";
            var_dump($wp_upload_dir_of_file);

            echo "<br>";
            echo "<br>";
            var_dump($ecommerceFileFolderWpUploadFolder);
            echo "<br>";
            echo "<br>";
            die;*/

            /*echo "<br>";
			var_dump('rename from');
            var_dump($ecommerceFileFolderWpUploadFolder.'/'.$filename);
            echo "<br>";
	        var_dump('rename to');
	        var_dump($wp_upload_dir_of_file.'/'.$filename);*/

			// maybe old folders on the WordPress instance were deleted
			if(!is_dir($wp_upload_dir_of_file)){
				mkdir($wp_upload_dir_of_file,0755,true);
			}
	        //contest-gallery-pro-replaced-3dir-replaced-31lala
	        //contest-gallery-pro-replaced-3dir-replaced-31lala-replaced
	        $isRenameSuccess = true;
	        if(file_exists($ecommerceFileFolderWpUploadFolder.'/'.$filename)){
		        // this processing (when a file will be replaced with file with same name) not for images so far!!! images always new name will be created. Because WordPress creates for images always new link (not like for other file types) despite same name uploaded.
		        if(file_exists($wp_upload_dir_of_file.'/'.$filename) && empty($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'])){
			        $result = cg_add_replace_to_url_filename($wp_upload_dir_of_file,$filename);
			        $filenameNew = $result['urlNew'];
			        $filenameCleanNew = $result['filenameCleanNew'];

			        $isRenameSuccess = rename($ecommerceFileFolderWpUploadFolder.'/'.$filename, $wp_upload_dir_of_file.'/'.$filenameNew);

			        if(!$isRenameSuccess){
				        $isAllWpUploadsSuccessfulMoved = false;
				        if($isFromDeleteGalleryOrUninstallPlugin){
					        echo "Following file could not be moved back to WordPress media library: ".$ecommerceFileFolderWpUploadFolder.'/'.$filename . ' to '.$wp_upload_dir_of_file.'/'.$filenameNew;
					        die;
				        }
			        }

			        $WpUploadFilePostMeta['_wp_attached_file'] = cg_replace_url_filename_with_replaced($WpUploadFilePostMeta['_wp_attached_file'],$filenameNew);

			        $wpdb->update(
				        "$tablePostMeta",
				        array('meta_value' => $WpUploadFilePostMeta['_wp_attached_file']),
				        array('meta_key' =>  '_wp_attached_file' , 'post_id' => $WpUploadId),
				        array('%s'),
				        array('%s','%d')
			        );

			        // if not empty then should be done after renaming sizes below
			        //if(empty($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'])){ // no need to it here, cause in that case _wp_attachment_metadata will stay like
						/*$wpdb->update(
							"$tablePostMeta",
							array('meta_value' => serialize($WpUploadFilePostMeta['_wp_attachment_metadata'])),
							array('meta_key' =>  '_wp_attachment_metadata' , 'post_id' => $WpUploadId),
							array('%s'),
							array('%s','%d')
						);*/
			      //  }

					$guidNew = cg_replace_url_filename_with_replaced($WpUploadFilesPosts[$WpUploadId]['guid'],$filenameNew);
					$postTitleNew = $filenameCleanNew;
					$postNameNew = $filenameCleanNew;

			        $wpdb->update(
				        "$tablePosts",
				        array('guid' => $guidNew,'post_title' => $postTitleNew,'post_name' => $postNameNew),
				        array('ID' => $WpUploadId),
				        array('%s','%s','%s'),
				        array('%d')
			        );

			        // $sizeArray['file] is without / at the beginning
			        ///$WpUploadFilePostMeta['_wp_attached_file'] = ... ;
			        /// $WpUploadFilesPosts[$WpUploadId]['guid'] = ....
			        ///
			        ///             $WpMetaAttachedFiles = $wpdb->get_results( "SELECT meta_value, post_id FROM $tablePostMeta WHERE meta_key = '_wp_attached_file' AND ($collectIDsWpUploadMeta)" );
			        //            $WpMetaAttachedFileMetas = $wpdb->get_results( "SELECT meta_value, post_id FROM $tablePostMeta WHERE meta_key = '_wp_attachment_metadata' AND ($collectIDsWpUploadMeta)" );
		        }else{
			        $isRenameSuccess = rename($ecommerceFileFolderWpUploadFolder.'/'.$filename, $wp_upload_dir_of_file.'/'.$filename);// $sizeArray['file] is without / at the beginning
		        }

	        }

            if(!$isRenameSuccess){
                $isAllWpUploadsSuccessfulMoved = false;
	            if($isFromDeleteGalleryOrUninstallPlugin){
					echo "Following file could not be moved back to WordPress media library: ".$ecommerceFileFolderWpUploadFolder.'/'.$filename;
					die;
	            }
            }

            /*var_dump($ecommerceFileFolderWpUploadFolder.'/'.$filename);
            echo "<br>";
            echo "<br>";
            var_dump('$isRenameSuccess');
            var_dump($isRenameSuccess);
            echo "<br>";
            echo "<br>";*/
/*            var_dump($ecommerceFileFolderWpUploadFolder.'/'.$filename);
            echo "<br>";
            echo "<br>";
            var_dump($wp_upload_dir_of_file.'/'.$filename);

            var_dump($isRenameSuccess);die;*/
            /*echo "<pre>";
            print_r($WpUploadFilePostMeta);
            echo "</pre>";*/

            if(!empty($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'])){// then must be image

				$isReplacedFile = false;
                foreach ($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'] as $sizeName => $sizeArray){

	                if(file_exists($ecommerceFileFolderWpUploadFolder.'/'.$sizeArray['file'])){

		                // this processing (when a file will be replaced with file with same name) not for images so far!!! images always new name will be created. Because WordPress creates for images always new link (not like for other file types) despite same name uploaded.
		                if(file_exists($wp_upload_dir_of_file.'/'.$sizeArray['file']) && false){
			                $result = cg_add_replace_to_url_filename($wp_upload_dir_of_file,$sizeArray['file']);
			                $filenameNew = $result['urlNew'];

			                rename($ecommerceFileFolderWpUploadFolder.'/'.$sizeArray['file'], $wp_upload_dir_of_file.'/'.$filenameNew);// $sizeArray['file] is without / at the beginning

			                $WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'][$sizeName]['file'] = cg_replace_url_filename_with_replaced($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'][$sizeName]['file'],$filenameNew);

			                $isReplacedFile = true;

		                }else{
			                rename($ecommerceFileFolderWpUploadFolder.'/'.$sizeArray['file'], $wp_upload_dir_of_file.'/'.$sizeArray['file']);// $sizeArray['file] is without / at the beginning
		                }
	                }
                }

	            // this processing not for images so far!!! images always new name will be created because always visible as attachement when watermarked
				if($isReplacedFile){
					$wpdb->update(
						"$tablePostMeta",
						array('meta_value' => serialize($WpUploadFilePostMeta['_wp_attachment_metadata'])),
						array('meta_key' =>  '_wp_attachment_metadata' , 'post_id' => $WpUploadId),
						array('%s'),
						array('%s','%d')
					);
				}
            }

            if($isRenameSuccess){
                cg_remove_folder_recursively($ecommerceFileFolderWpUploadFolder);
            }

            $wpdb->update(
                "$tablePosts",
                array('post_type' => 'attachment'),
                array('ID' => $WpUploadId),
                array('%s'),
                array('%d')
            );

        }

        if(!empty($removedWpUploadIdsFromSale)){
            //var_dump('in here');
            foreach($removedWpUploadIdsFromSale as $WpUploadId){
	            //var_dump('unset wp upload');
	            unset($WpUploadFilesPostMeta[$WpUploadId]);
                unset($WpUploadFilesPosts[$WpUploadId]);
            }

            /*var_dump('$WpUploadFilesPostMeta');
            echo "<pre>";
            print_r($WpUploadFilesPostMeta);
            echo "</pre>";

            var_dump('$WpUploadFilesPosts');
            echo "<pre>";
            print_r($WpUploadFilesPosts);
            echo "</pre>";*/

            if(empty($WpUploadFilesPostMeta)){
                $WpUploadFilesPostMeta = '';
            }else{
                $WpUploadFilesPostMeta = serialize($WpUploadFilesPostMeta);
            }
            if(empty($WpUploadFilesPosts)){
                $WpUploadFilesPosts = '';
            }else{
                $WpUploadFilesPosts = serialize($WpUploadFilesPosts);
            }

	       //var_dump('$WpUploadFilesPostMeta serialized move from');
	        //var_dump($WpUploadFilesPostMeta);

	        //var_dump('$WpUploadFilesPosts serialized move from');
	        //var_dump($WpUploadFilesPosts);

			//var_dump('$ecommerceEntryId move from');
			//var_dump($ecommerceEntryId);


            $wpdb->update(
                "$tablename_ecommerce_entries",
                array('WpUploadFilesPostMeta' => "$WpUploadFilesPostMeta",'WpUploadFilesPosts' => "$WpUploadFilesPosts"),
                array('id' => $ecommerceEntryId),
                array('%s','%s'),
                array('%d')
            );

        }else{
            // then simply WpUploadFilesPosts and WpUploadFilesPostMeta can be emptied
            $wpdb->update(
                "$tablename_ecommerce_entries",
                array('WpUploadFilesPosts' => '','WpUploadFilesPostMeta' => ''),
                array('id' => $ecommerceEntryId),
                array('%s','%s'),
                array('%d')
            );
        }

        return $isAllWpUploadsSuccessfulMoved;

    }
}
