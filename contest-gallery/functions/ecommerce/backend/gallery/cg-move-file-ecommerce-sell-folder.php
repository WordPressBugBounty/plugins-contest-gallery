<?php
if(!function_exists('cg_move_file_ecommerce_sale_folder')){
    function cg_move_file_ecommerce_sale_folder($realId, $GalleryID, $sqlObjectFile, $sqlObjectFileEcommerceEntry, $WpUploadFilesForSale = [], $removedWpUploadIdsFromSale = []){

        $wp_upload_dir = wp_upload_dir();

        global $wpdb;
        $tablePosts = $wpdb->prefix . "posts";
        $tablePostMeta = $wpdb->prefix . "postmeta";
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

	    $WpUploadFilesForSale = (isset($_POST['cgSellContainer']['WpUploadFilesForSale'])) ? $_POST['cgSellContainer']['WpUploadFilesForSale'] : $WpUploadFilesForSale;

	    $removedWpUploadIdsFromSale = (isset($_POST['cgSellContainer']['removedWpUploadIdsFromSale'])) ? $_POST['cgSellContainer']['removedWpUploadIdsFromSale'] : $removedWpUploadIdsFromSale;

	    // three steps
        // #1 collect files related data and save in contest gallery table
        // #2 use collected data to move files to contest gallery folder
        // #3 replace original links with watermarked files or fake file links

        // #1 collect files related data and save in contest gallery table
	//    var_dump('$sqlObjectFileEcommerceEntry->WpUploadFilesPostMeta 111');
	   // var_dump($sqlObjectFileEcommerceEntry->WpUploadFilesPostMeta);
	   // var_dump('$sqlObjectFileEcommerceEntry->WpUploadFilesPosts 111');
	  //  var_dump($sqlObjectFileEcommerceEntry->WpUploadFilesPosts);
        if(!empty($sqlObjectFileEcommerceEntry->WpUploadFilesPosts)){
            $WpUploadFilesPosts = unserialize($sqlObjectFileEcommerceEntry->WpUploadFilesPosts);
            foreach ($WpUploadFilesPosts as $key => $WpUploadFilesPost){
	            $WpUploadFilesPosts[$key]['isAlreadyMoved'] = true;
	            if(in_array($key,$removedWpUploadIdsFromSale)){
		            unset($WpUploadFilesPosts[$key]);
	            }
            }
        }else{
            $WpUploadFilesPosts = [];
        }

        if(!empty($sqlObjectFileEcommerceEntry->WpUploadFilesPosts)){
            $WpUploadFilesPostMeta = unserialize($sqlObjectFileEcommerceEntry->WpUploadFilesPostMeta);
            foreach ($WpUploadFilesPostMeta as $key => $WpUploadFilesPostMetaPart){
	            $WpUploadFilesPostMeta[$key]['isAlreadyMoved'] = true;
				if(in_array($key,$removedWpUploadIdsFromSale)){
					unset($WpUploadFilesPostMeta[$key]);
				}
            }
        }else{
            $WpUploadFilesPostMeta = [];
        }

        $WpUploadFilesPostBaseUrls = [];
        $WpUploadFilesPostBaseDirs = [];
        $WpUploadFilesAlreadyMoved = [];

        //var_dump('$sqlObjectFile->WpUploadFilesPosts');
	    //echo "<pre>";
	    //print_r($sqlObjectFileEcommerceEntry->WpUploadFilesPosts);
	    //echo "</pre>";

        if(!empty($sqlObjectFileEcommerceEntry->WpUploadFilesPosts)){// then must be simply update base64 values or new files were added
            foreach (unserialize($sqlObjectFileEcommerceEntry->WpUploadFilesPosts) as $WpUploadFilesPostId => $WpUploadFilesPost){
                $WpUploadFilesAlreadyMoved[] = $WpUploadFilesPostId;
            }
        }

        $entryId = $sqlObjectFile->EcommerceEntry;

        /*$WpUploadFilesPostsFromEntryArray = [];
        $WpUploadFilesPostsFromEntry = $wpdb->get_var("SELECT WpUploadFilesPosts FROM $tablename_ecommerce_entries WHERE id = '$entryId'");
        if(!empty($WpUploadFilesPostsFromEntry)){
            $WpUploadFilesPostsFromEntryArray = unserialize($WpUploadFilesPostsFromEntry);
        }*/

	    //var_dump('$WpUploadFilesAlreadyMoved');
	    //echo "<pre>";
	    //print_r($WpUploadFilesAlreadyMoved);
	    //echo "</pre>";

        if(!empty($sqlObjectFile->MultipleFiles)){

            $MultipleFiles = unserialize($sqlObjectFile->MultipleFiles);

	        // var_dump('$MultipleFiles');
	        // echo "<pre>";
	        //    print_r($MultipleFiles);
	        //echo "</pre>";

            $collectIDsWpUploadPost = '';
            $collectIDsWpUploadMeta = '';

		//	var_dump('$WpUploadFilesForSale');
	        //	var_dump($WpUploadFilesForSale);

	        //var_dump('$MultipleFiles');
	        //	var_dump($MultipleFiles);

	        //		var_dump('watermarked files');
		//	var_dump($_POST['cgSellContainer']['base64WatermarkedAndAltFiles']);

            foreach ($MultipleFiles as $MultipleFilesOrder => $MultipleFileArray){
                if(!empty($MultipleFileArray['isRealIdSource'])){
                    $WpUploadID = $sqlObjectFile->WpUpload;
                }else{
                    $WpUploadID = $MultipleFileArray['WpUpload'];
                }

                if(!in_array($WpUploadID,$WpUploadFilesAlreadyMoved) && in_array($WpUploadID,$WpUploadFilesForSale)){
                    if(!$collectIDsWpUploadPost){
                        $collectIDsWpUploadPost .= 'ID='.$WpUploadID;
                        $collectIDsWpUploadMeta .= 'post_id='.$WpUploadID;
                    }else{
                        $collectIDsWpUploadPost .= ' OR ID='.$WpUploadID;
                        $collectIDsWpUploadMeta .= ' OR post_id='.$WpUploadID;
                    }
                }

            }

	        //    print_r('$WpUploadFilesAlreadyMoved');
	        //    echo "<br>";
	        // var_dump($WpUploadFilesAlreadyMoved);
	        //   echo "<br>";

	        //      print_r('$WpUploadFilesForSale');
	        //  echo "<br>";
	        //    var_dump($WpUploadFilesForSale);
	        //    echo "<br>";

	        //    print_r('$collectIDsWpUploadPost');
	        //  echo "<br>";
	        // var_dump($collectIDsWpUploadPost);
	        //  echo "<br>";
	        //   print_r('$collectIDsWpUploadMeta');
	        //  echo "<br>";
	        // var_dump($collectIDsWpUploadMeta);


            $WpUploadPosts = $wpdb->get_results( "SELECT * FROM $tablePosts WHERE ($collectIDsWpUploadPost)" );

	        //var_dump('$collectIDsWpUploadPost');
	        //   echo "<pre>";
	        //   print_r($WpUploadPosts);
	        //   echo "</pre>";

            $WpMetaAttachedFiles = $wpdb->get_results( "SELECT meta_value, post_id FROM $tablePostMeta WHERE meta_key = '_wp_attached_file' AND ($collectIDsWpUploadMeta)" );
            $WpMetaAttachedFileMetas = $wpdb->get_results( "SELECT meta_value, post_id FROM $tablePostMeta WHERE meta_key = '_wp_attachment_metadata' AND ($collectIDsWpUploadMeta)" );

            foreach ($WpMetaAttachedFiles as $WpMetaAttachedFile){
                $WpMetaAttachedFiles[$WpMetaAttachedFile->post_id] = $WpMetaAttachedFile->meta_value;
            }
            $WpMetaAttachedFileMetasArray = [];
            foreach ($WpMetaAttachedFileMetas as $WpMetaAttachedFileMeta){
                $WpMetaAttachedFileMetasArray[$WpMetaAttachedFileMeta->post_id] = unserialize($WpMetaAttachedFileMeta->meta_value);
            }

            foreach ($WpUploadPosts as $WpUploadPost){
                $WpUploadFilesPosts[$WpUploadPost->ID] = json_decode(json_encode($WpUploadPost),true);
                $WpUploadFilesPostMeta[$WpUploadPost->ID] = [];
                $WpUploadFilesPostMeta[$WpUploadPost->ID]['_wp_attached_file'] = $WpMetaAttachedFiles[$WpUploadPost->ID];
                $WpUploadFilesPostMeta[$WpUploadPost->ID]['_wp_attachment_metadata'] = $WpMetaAttachedFileMetasArray[$WpUploadPost->ID];

                $wpdb->update(
                    "$tablePosts",
                    array('post_type' => 'contest-gallery-ecom'),
                    array('ID' => $WpUploadPost->ID),
                    array('%s'),
                    array('%d')
                );

            }

        }else{

            $WpUploadId = $sqlObjectFile->WpUpload;
            $WpUploadPost = $wpdb->get_row( "SELECT * FROM $tablePosts WHERE ID = '$WpUploadId'" );

            $WpMetaAttachedFile = $wpdb->get_var( "SELECT meta_value FROM $tablePostMeta WHERE meta_key = '_wp_attached_file' AND post_id='$WpUploadId'" );
            $WpMetaAttachedFileMeta = $wpdb->get_var( "SELECT meta_value FROM $tablePostMeta WHERE meta_key = '_wp_attachment_metadata' AND post_id='$WpUploadId'" );

            $WpUploadFilesPosts[$WpUploadId] = json_decode(json_encode($WpUploadPost),true);
            $WpUploadFilesPostMeta[$WpUploadId]['_wp_attached_file'] = $WpMetaAttachedFile;
            $WpUploadFilesPostMeta[$WpUploadId]['_wp_attachment_metadata'] = unserialize($WpMetaAttachedFileMeta);

            $wpdb->update(
                "$tablePosts",
                array('post_type' => 'contest-gallery-ecom'),
                array('ID' => $WpUploadId),
                array('%s'),
                array('%d')
            );

        }

	    // var_dump('$WpUploadFilesPosts');
	    // echo "<pre>";
	    // print_r($WpUploadFilesPosts);
	    // echo "</pre>";

	    //   var_dump('WpUploadFilesPostMeta');
	    // echo "<pre>";
	    // print_r($WpUploadFilesPostMeta);
	    // echo "</pre>";

        // collected posts and meta can be update here then
	    // WpUploadFilesForSale has to be updated here definetly also here in case it is a replace
	    // WatermarkSettings has also to be set here in case it is a replace
        $wpdb->update(
            "$tablename_ecommerce_entries",
            array('WpUploadFilesPosts' => serialize($WpUploadFilesPosts), 'WpUploadFilesPostMeta' => serialize($WpUploadFilesPostMeta), 'WpUploadFilesForSale' => serialize($WpUploadFilesForSale), 'WatermarkSettings' => (isset($_POST['cgSellContainer']['WatermarkSettings'])) ? serialize($_POST['cgSellContainer']['WatermarkSettings']) : ''),
            array('id' => $sqlObjectFile->EcommerceEntry),
            array('%s','%s','%s'),
            array('%d')
        );

        // #2 use collected data to move files to contest gallery folder
        $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$sqlObjectFile->id;

        if(!is_dir($ecommerceFileFolder)){
            mkdir($ecommerceFileFolder,0755,true);
        }

        foreach ($WpUploadFilesPostMeta as $WpUploadFilePostMetaWpUploadId => $WpUploadFilePostMeta){

            if(!empty($WpUploadFilePostMeta['isAlreadyMoved'])){
                continue;
            }

            $attached_file = get_attached_file($WpUploadFilePostMetaWpUploadId);

	        //  var_dump('$attached_file');
	        //  echo "<pre>";
	        //  print_r($attached_file);
	        // echo "</pre>";

      //      $WpUploadFilesPostBaseUrls[$WpUploadFilePostMetaWpUploadId] = $attached_file;

            $attached_file_dir = substr($attached_file,0,strrpos($attached_file,'/'));// folder without / at the end
            //$WpUploadFilesPostBaseDirs[$WpUploadFilePostMetaWpUploadId] = $attached_file_dir;
            $attached_file_name = substr($attached_file,(strrpos($attached_file,'/')+1),strlen($attached_file)); // filename without / at the end beginning because of +1 at start

            $ecommerceFileFolderWpUploadFolder = $ecommerceFileFolder.'/wp-upload-id-'.$WpUploadFilePostMetaWpUploadId;

            if(!is_dir($ecommerceFileFolderWpUploadFolder)){
                mkdir($ecommerceFileFolderWpUploadFolder,0755,true);
            }

            $htaccessFileContent = <<<HEREDOC
#Do not remove htaccess in this folder. It prevents for getting files which are selected for sale from getting from outside.
<Files "*">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</Files>
HEREDOC;
            $htaccessFile = $ecommerceFileFolderWpUploadFolder.'/.htaccess';
            file_put_contents($htaccessFile,$htaccessFileContent);
            chmod($htaccessFile, 0640);// no read for others!!!
            file_put_contents($ecommerceFileFolderWpUploadFolder.'/do-not-remove-htaccess.txt','Do not remove htaccess in this folder. It prevents for getting files which are selected for sale from getting from outside.');

	        //   var_dump('rename');
	        // var_dump($ecommerceFileFolderWpUploadFolder.'/'.$attached_file_name);
	        //  echo "<br>";

            rename($attached_file, $ecommerceFileFolderWpUploadFolder.'/'.$attached_file_name);

            // now check if converted smaller filesizes exists to move
            if(!empty($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'])){
                foreach ($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'] as $sizeName => $sizeArray){
					if(file_exists($attached_file_dir.'/'.$sizeArray['file'])){// do not remove this check
						rename($attached_file_dir.'/'.$sizeArray['file'], $ecommerceFileFolderWpUploadFolder.'/'.$sizeArray['file']);// $sizeArray['file] is without / at the beginning
					}
                }
            }

            // add own post meta for javascript media.frame attachment, to avoid that user add a ecommerce sale file to a gallery as add images or multiple file
            add_post_meta( $WpUploadFilePostMetaWpUploadId, 'contest_gallery_ecommerce_sale', serialize(['GalleryID'=>$GalleryID]) );

        }

        // #3 replace original links with watermarked files or fake file links, no changes to original wp posts database and link names!!!

        /*var_dump('$WpUploadFilesPostMeta');
        echo "<pre>";
        print_r($WpUploadFilesPostMeta);
        echo "</pre>";*/


        // base URLs and BaseDIRs should be determined here, after everything is settled, it should be get again from database

        /*var_dump('$entryId');
        echo "<pre>";
        print_r($entryId);
        echo "</pre>";

        var_dump('$WpUploadFilesPostMeta');
        echo "<pre>";
        print_r($WpUploadFilesPostMeta);
        echo "</pre>";*/

        foreach ($WpUploadFilesPostMeta as $WpUploadFilePostMetaWpUploadId => $WpUploadFilePostMeta){
            $attached_file = get_attached_file($WpUploadFilePostMetaWpUploadId);
            $WpUploadFilesPostBaseUrls[$WpUploadFilePostMetaWpUploadId] = $attached_file;
            $attached_file_dir = substr($attached_file,0,strrpos($attached_file,'/'));// folder without / at the end
            $WpUploadFilesPostBaseDirs[$WpUploadFilePostMetaWpUploadId] = $attached_file_dir;
        }

	    //  var_dump('$WpUploadFilesPostBaseUrls');
	    //  echo "<pre>";
	    //  print_r($WpUploadFilesPostBaseUrls);
	    // echo "</pre>";

	    //   var_dump('$WpUploadFilesPostBaseDirs');
	    //   echo "<pre>";
	    //   print_r($WpUploadFilesPostBaseDirs);
	    // echo "</pre>";

        // watermarkedFiles contains also simple full guid of not images, they are of course no kind of watermarked
        if(!empty($_POST['cgSellContainer']['base64WatermarkedAndAltFiles'])){
            foreach ($_POST['cgSellContainer']['base64WatermarkedAndAltFiles'] as $base64WatermarkedAndAltFilesWpUploadId => $base64WatermarkedAndAltFile){

                $type = $_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId];

                // handling for watermarked images
                if(cg_is_is_image($type)){
                    // set watermark file as original guid now after guid has been moved
                    // overwrite original file first
                    $content = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64WatermarkedAndAltFile));
                    $formImage = imagecreatefromstring($content);

	                if($_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]=='png'){
		                imagesavealpha($formImage,true);// required for png images... otherwise background black
	                }

                    if($_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]=='jpg' || $_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]=='jpeg'){
	                    //     echo "<br>";
	                    //    var_dump('dasdfasdf');
	                    //   var_dump($WpUploadFilesPostBaseUrls[$base64WatermarkedAndAltFilesWpUploadId]);
	                    //   echo "<br>";
                        imagejpeg($formImage,$WpUploadFilesPostBaseUrls[$base64WatermarkedAndAltFilesWpUploadId]);
                    }
                    if($_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]=='gif'){
                        imagegif($formImage,$WpUploadFilesPostBaseUrls[$base64WatermarkedAndAltFilesWpUploadId]);
                    }
                    if($_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]=='png'){
                        imagepng($formImage,$WpUploadFilesPostBaseUrls[$base64WatermarkedAndAltFilesWpUploadId]);
                    }

                    foreach ($WpUploadFilesPostMeta as $WpUploadFilePostMetaWpUploadId => $WpUploadFilePostMeta){
                        if($base64WatermarkedAndAltFilesWpUploadId==$WpUploadFilePostMetaWpUploadId){
                            // now check if converted smaller filesizes exists to be replaced with watermarked
                            if(!empty($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'])){
                                foreach ($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'] as $sizeName => $sizeArray){
                                    if($_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]=='jpg' || $_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]=='jpeg'){
	                                    //   echo "<br>";
	                                    //      var_dump('5645rtretert');
	                                    //      var_dump($WpUploadFilesPostBaseDirs[$base64WatermarkedAndAltFilesWpUploadId].'/'.$sizeArray['file']);
	                                    //     echo "<br>";
                                        imagejpeg($formImage,$WpUploadFilesPostBaseDirs[$base64WatermarkedAndAltFilesWpUploadId].'/'.$sizeArray['file']);
                                    }
                                    if($_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]=='gif'){
                                        imagegif($formImage,$WpUploadFilesPostBaseDirs[$base64WatermarkedAndAltFilesWpUploadId].'/'.$sizeArray['file']);
                                    }
                                    if($_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]=='png'){
                                        imagepng($formImage,$WpUploadFilesPostBaseDirs[$base64WatermarkedAndAltFilesWpUploadId].'/'.$sizeArray['file']);
                                    }
                                }
                            }
                        }
                    }

                } else {// handling for normal files
					// Do nothing norma files like pdf, video, audio, will be not forwarded as base64WatermarkedAndAltFileTypes. Will be simply renamed by through WpUploadFilesPostMeta.
                }

            }
        }

        if(!empty($_FILES['cgSellContainer']['name']) && !empty($_FILES['cgSellContainer']['name']['DownloadKey'])){
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$sqlObjectFile->id.'/download-keys/';
            if(!is_dir($ecommerceFileFolder)){
                mkdir($ecommerceFileFolder,0755,true);
            }
            cg_create_htaccess_ecommerce_sale_folder_if_not_exists($ecommerceFileFolder);
            $fileContent = file_get_contents($_FILES['cgSellContainer']['tmp_name']['DownloadKey']);
            file_put_contents($ecommerceFileFolder.$_FILES['cgSellContainer']['name']['DownloadKey'],$fileContent);
        }

        if(!empty($_FILES['cgSellContainer']['name']) && !empty($_FILES['cgSellContainer']['name']['ServiceKey'])){
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$sqlObjectFile->id.'/service-keys/';
            if(!is_dir($ecommerceFileFolder)){
                mkdir($ecommerceFileFolder,0755,true);
            }
            cg_create_htaccess_ecommerce_sale_folder_if_not_exists($ecommerceFileFolder);
            $fileContent = file_get_contents($_FILES['cgSellContainer']['tmp_name']['ServiceKey']);
            file_put_contents($ecommerceFileFolder.$_FILES['cgSellContainer']['name']['ServiceKey'],$fileContent);
        }

    }
}
if(!function_exists('cg_create_htaccess_ecommerce_sale_folder_if_not_exists')){
    function cg_create_htaccess_ecommerce_sale_folder_if_not_exists($attached_file_dir_watermarked){
        $htaccessFileContent = <<<HEREDOC
#Do not remove htaccess in this folder. It prevents for getting files which are selected for sale from getting from outside.
<Files "*">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</Files>
HEREDOC;
        $htaccessFile = $attached_file_dir_watermarked.'/.htaccess';
        file_put_contents($htaccessFile,$htaccessFileContent);
        chmod($htaccessFile, 0640);// no read for others!!!

        file_put_contents($attached_file_dir_watermarked.'/do-not-remove-htaccess.txt','Do not remove htaccess in this folder. It prevents for getting files which are selected for sale from getting from outside.');
        file_put_contents($attached_file_dir_watermarked.'/.htaccess','Do not remove htaccess in this folder. It prevents for getting files which are selected for sale from getting from outside.');

    }
}
