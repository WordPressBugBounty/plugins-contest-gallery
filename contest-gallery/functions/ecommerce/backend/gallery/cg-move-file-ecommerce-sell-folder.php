<?php
if(!function_exists('cg_move_file_ecommerce_sale_folder')){
    function cg_move_file_ecommerce_sale_folder($realId, $GalleryID, $sqlObjectFile, $sqlObjectFileEcommerceEntry, $WpUploadFilesForSale = [], $removedWpUploadIdsFromSale = []){

        $keyUploads = cg_ecommerce_validate_key_uploads($_FILES);

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

                $type = strtolower(sanitize_text_field($_POST['cgSellContainer']['base64WatermarkedAndAltFileTypes'][$base64WatermarkedAndAltFilesWpUploadId]));

                // handling for watermarked images
                if(in_array($type, array('jpg','jpeg','png'), true)){
                    // set watermark file as original guid now after guid has been moved
                    // overwrite original file first
                    $content = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64WatermarkedAndAltFile));
                    $formImage = imagecreatefromstring($content);

	                if($type=='png'){
		                imagesavealpha($formImage,true);// required for png images... otherwise background black
	                }

                    if($type=='jpg' || $type=='jpeg'){
	                    //     echo "<br>";
	                    //    var_dump('dasdfasdf');
	                    //   var_dump($WpUploadFilesPostBaseUrls[$base64WatermarkedAndAltFilesWpUploadId]);
	                    //   echo "<br>";
                        imagejpeg($formImage,$WpUploadFilesPostBaseUrls[$base64WatermarkedAndAltFilesWpUploadId]);
                    }
                    if($type=='png'){
                        imagepng($formImage,$WpUploadFilesPostBaseUrls[$base64WatermarkedAndAltFilesWpUploadId]);
                    }

                    foreach ($WpUploadFilesPostMeta as $WpUploadFilePostMetaWpUploadId => $WpUploadFilePostMeta){
                        if($base64WatermarkedAndAltFilesWpUploadId==$WpUploadFilePostMetaWpUploadId){
                            // now check if converted smaller filesizes exists to be replaced with watermarked
                            if(!empty($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'])){
                                foreach ($WpUploadFilePostMeta['_wp_attachment_metadata']['sizes'] as $sizeName => $sizeArray){
                                    if($type=='jpg' || $type=='jpeg'){
	                                    //   echo "<br>";
	                                    //      var_dump('5645rtretert');
	                                    //      var_dump($WpUploadFilesPostBaseDirs[$base64WatermarkedAndAltFilesWpUploadId].'/'.$sizeArray['file']);
	                                    //     echo "<br>";
                                        imagejpeg($formImage,$WpUploadFilesPostBaseDirs[$base64WatermarkedAndAltFilesWpUploadId].'/'.$sizeArray['file']);
                                    }
                                    if($type=='png'){
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

        foreach($keyUploads as $key => $upload){
            cg_ecommerce_store_key_upload($upload,$GalleryID,$sqlObjectFile->id,$key);
        }

    }
}
if(!function_exists('cg_ecommerce_key_upload_fail')){
    function cg_ecommerce_key_upload_fail($status = 400){
        $message = $status === 400 ? 'Upload a valid CSV key file of at most 64000 bytes.' : 'The key file could not be saved. Check the upload directory permissions.';
        if(function_exists('cg_backend_ajax_error_json')){
            cg_backend_ajax_error_json($message,$status,'cg_key_upload_failed');
        }
        wp_die(esc_html($message),'',array('response'=>$status));
    }
}
if(!function_exists('cg_ecommerce_validate_key_uploads')){
    function cg_ecommerce_validate_key_uploads($files){
        $uploads = array();
        if(!isset($files['cgSellContainer'])){
            return $uploads;
        }
        $files = $files['cgSellContainer'];
        $fields = array('name','tmp_name','error','size','type');
        foreach($fields as $field){
            if(!is_array($files) || !isset($files[$field]) || !is_array($files[$field])){
                cg_ecommerce_key_upload_fail();
            }
        }
        // Validate both optional uploads before any caller moves or writes files.
        foreach(array('DownloadKey','ServiceKey') as $key){
            $present = false;
            foreach($fields as $field){
                $present = $present || array_key_exists($key,$files[$field]);
            }
            if(!$present){continue;}
            foreach($fields as $field){
                if(!isset($files[$field][$key]) || !is_scalar($files[$field][$key])){
                    cg_ecommerce_key_upload_fail();
                }
            }
            $name = $files['name'][$key];
            $tmp = $files['tmp_name'][$key];
            $error = $files['error'][$key];
            $size = $files['size'][$key];
            if(!is_string($name) || !is_string($tmp) || !is_string($files['type'][$key]) || !is_int($error) || !is_int($size)){
                cg_ecommerce_key_upload_fail();
            }
            if($error === UPLOAD_ERR_NO_FILE && $name === '' && $tmp === '' && $size === 0){continue;}
            if($error !== UPLOAD_ERR_OK || $size < 0 || $size > 64000 || $name === '' ||
                preg_match('~[\\\\/\x00-\x1f\x7f]~',$name) || substr(ltrim($name),0,1) === '.' ||
                strtolower(pathinfo($name,PATHINFO_EXTENSION)) !== 'csv' || !is_uploaded_file($tmp) || !is_readable($tmp)){
                cg_ecommerce_key_upload_fail();
            }
            $name = sanitize_file_name($name);
            if(strtolower(pathinfo($name,PATHINFO_EXTENSION)) !== 'csv'){
                cg_ecommerce_key_upload_fail();
            }
            // A CSV extension alone does not neutralize intermediate handler extensions.
            $name = str_replace('.','_',substr($name,0,-4)).'.csv';
            if(!preg_match('~\A[^.\\\\/\x00-\x1f\x7f]+\.csv\z~',$name)){
                cg_ecommerce_key_upload_fail();
            }
            $content = @file_get_contents($tmp,false,null,0,64001);
            if($content === false){cg_ecommerce_key_upload_fail(500);}
            if(strlen($content) > 64000 || strlen($content) !== $size){cg_ecommerce_key_upload_fail();}
            $uploads[$key] = array('name'=>$name,'content'=>$content);
        }
        return $uploads;
    }
}
if(!function_exists('cg_ecommerce_store_key_upload')){
    function cg_ecommerce_store_key_upload($upload,$GalleryID,$realId,$key){
        $folders = array('DownloadKey'=>'download-keys','ServiceKey'=>'service-keys');
        $GalleryID = absint($GalleryID);
        $realId = absint($realId);
        if(!isset($folders[$key]) || !$GalleryID || !$realId || !isset($upload['name'],$upload['content']) ||
            !is_string($upload['name']) || !is_string($upload['content']) || strlen($upload['content']) > 64000 ||
            !preg_match('~\A[^.\\\\/\x00-\x1f\x7f]+\.csv\z~',$upload['name'])){
            cg_ecommerce_key_upload_fail();
        }
        $wp_upload_dir = wp_upload_dir();
        $dir = realpath($wp_upload_dir['basedir']);
        if($dir === false){cg_ecommerce_key_upload_fail(500);}
        // Resolve the configured upload root, then reject symlinks at every child level.
        foreach(array('contest-gallery','gallery-id-'.$GalleryID,'ecommerce','real-id-'.$realId,$folders[$key]) as $part){
            $dir .= '/'.$part;
            if(is_link($dir) || (!is_dir($dir) && !@mkdir($dir,0755) && !is_dir($dir))){
                cg_ecommerce_key_upload_fail(500);
            }
            if(wp_normalize_path(realpath($dir)) !== wp_normalize_path($dir)){
                cg_ecommerce_key_upload_fail(500);
            }
        }
        $target = $dir.'/'.$upload['name'];
        if(is_link($target) || (file_exists($target) && !is_file($target)) ||
            !cg_create_htaccess_ecommerce_sale_folder_if_not_exists($dir)){
            cg_ecommerce_key_upload_fail(500);
        }
        $keyLock = cg_ecommerce_lock_key_file($target);
        if(!$keyLock){cg_ecommerce_key_upload_fail(500);}
        try{
            if(@file_put_contents($target,$upload['content'],LOCK_EX) !== strlen($upload['content'])){
                cg_ecommerce_key_upload_fail(500);
            }
        }finally{
            cg_ecommerce_unlock_key_file($keyLock);
        }
        return $upload['name'];
    }
}
if(!function_exists('cg_create_htaccess_ecommerce_sale_folder_if_not_exists')){
    function cg_create_htaccess_ecommerce_sale_folder_if_not_exists($attached_file_dir_watermarked){
        $attached_file_dir_watermarked = rtrim($attached_file_dir_watermarked,'/\\');
        if(is_link($attached_file_dir_watermarked) || !is_dir($attached_file_dir_watermarked)){
            return false;
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
        $htaccessFile = $attached_file_dir_watermarked.'/.htaccess';
        $noteFile = $attached_file_dir_watermarked.'/do-not-remove-htaccess.txt';
        foreach(array($htaccessFile,$noteFile) as $file){
            if(is_link($file) || (file_exists($file) && !is_file($file))){return false;}
        }
        if(@file_put_contents($htaccessFile,$htaccessFileContent,LOCK_EX) !== strlen($htaccessFileContent) || !@chmod($htaccessFile,0640)){
            return false;
        }
        $note = 'Do not remove htaccess in this folder. It prevents for getting files which are selected for sale from getting from outside.';
        return @file_put_contents($noteFile,$note,LOCK_EX) === strlen($note);
    }
}
