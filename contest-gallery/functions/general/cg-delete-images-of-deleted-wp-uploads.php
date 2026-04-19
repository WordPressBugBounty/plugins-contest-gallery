<?php
add_action('cg_delete_images_of_deleted_wp_uploads','cg_delete_images_of_deleted_wp_uploads');
if(!function_exists('cg_delete_images_of_deleted_wp_uploads')){
    function cg_delete_images_of_deleted_wp_uploads($deletedWpUploads){

        global $wpdb;

        $tablename = $wpdb->prefix . "contest_gal1ery";

        // After original attachments are deleted from WordPress storage, repair
        // every gallery row whose serialized MultipleFiles payload still points
        // to one of those WpUpload IDs.
        $collect = "";

        /*        var_dump('$deletedWpUploads 1');

                echo "<pre>";
                print_r($deletedWpUploads);
                echo "</pre>";*/

        // Build one SQL filter that finds any row whose MultipleFiles payload
        // still references a deleted WpUpload attachment.
        foreach ($deletedWpUploads as $value){
            if(empty($collect)){
                $collect .= "$tablename.MultipleFiles LIKE '%WpUpload\";i:$value;%'";
            }else{
                $collect .= " OR $tablename.MultipleFiles LIKE '%WpUpload\";i:$value;%'";
            }
        }

        $filesWithDeletedWpUploadsInMultipleFiles = $wpdb->get_results( "SELECT * FROM $tablename WHERE ($collect) ORDER BY GalleryID DESC, id DESC");

        $correctedRowObjects=[];

        /*var_dump($filesWithDeletedWpUploadsInMultipleFiles);
                echo "<pre>";
                print_r($filesWithDeletedWpUploadsInMultipleFiles);
                echo "</pre>";*/

        if(count($filesWithDeletedWpUploadsInMultipleFiles)){

            // Update the serialized MultipleFiles payload in place and, if the
            // removed upload used to be the real source, promote the next file
            // as the canonical row representation.
            $queryInsertNewMultipleFiles = 'INSERT INTO  '.$tablename.' (id, MultipleFiles) VALUES ';
            $queryHasRealIdDeleted = 'INSERT INTO '.$tablename.' (id, NamePic, ImgType, WpUpload, Width, Height, rThumb, Exif) VALUES ';
            $queryHasRealIdDeletedCenterStringPart = '';

            foreach ($filesWithDeletedWpUploadsInMultipleFiles as $rowObject){

                $hasRealIdDeleted = false;

                $MultipleFilesNewArray =[];
                $MultipleFilesArray = unserialize($rowObject->MultipleFiles);
                $newOrder = 1;

                // Filter the removed uploads out of the serialized attachment set
                // while preserving the original order of the remaining files.
                if(!empty($MultipleFilesArray)){// check for older rows where the column might still contain ""
                    foreach ($MultipleFilesArray as $order => $array){// rows with a realIdSource should already have been deleted earlier
                        if(!empty($array['isRealIdSource']) && in_array($array['WpUpload'],$deletedWpUploads)){
                            $hasRealIdDeleted = true;
                        }
                        if(in_array($array['WpUpload'],$deletedWpUploads)){continue;}
                        $MultipleFilesNewArray[$newOrder] = $array;
                        $newOrder++;
                    }
                }

                /*                var_dump($MultipleFilesNewArray);

                                echo "<pre>";
                                print_r($MultipleFilesNewArray);
                                echo "</pre>";*/

                if(!empty($MultipleFilesNewArray)){
                    if($hasRealIdDeleted){
                        // The main file was removed, so promote the first
                        // surviving attachment into the row's primary fields.
                        $rowObject->NamePic = $MultipleFilesNewArray[1]['NamePic'];
                        $rowObject->ImgType = $MultipleFilesNewArray[1]['ImgType'];
                        $rowObject->WpUpload = $MultipleFilesNewArray[1]['WpUpload'];
                        $rowObject->Width = $MultipleFilesNewArray[1]['Width'];
                        $rowObject->Height = $MultipleFilesNewArray[1]['Height'];
                        $rowObject->rThumb = $MultipleFilesNewArray[1]['rThumb'];
                        $rowObject->Exif = $MultipleFilesNewArray[1]['Exif'];
                        $rowObject->post_alt = $MultipleFilesNewArray[1]['post_alt'];
                        $rowObject->post_title = $MultipleFilesNewArray[1]['post_title'];
                        $rowObject->post_title = $MultipleFilesNewArray[1]['post_title'];
                        $rowObject->post_name = $MultipleFilesNewArray[1]['post_name'];
                        $rowObject->post_content = $MultipleFilesNewArray[1]['post_content'];
                        $rowObject->post_excerpt = $MultipleFilesNewArray[1]['post_excerpt'];
                        $rowObject->post_date = $MultipleFilesNewArray[1]['post_date'];

                        $correctedRowObjects[] = $rowObject;
                        $queryHasRealIdDeletedCenterStringPart .= "('$rowObject->id', '".($MultipleFilesNewArray[1]['NamePic'])."', '".($MultipleFilesNewArray[1]['ImgType'])."','".($MultipleFilesNewArray[1]['WpUpload'])."','".(intval($MultipleFilesNewArray[1]['Width']))."','".(intval($MultipleFilesNewArray[1]['Height']))."','".(intval($MultipleFilesNewArray[1]['rThumb']))."','".($MultipleFilesNewArray[1]['Exif'])."'),";
                    }

                    if(count($MultipleFilesNewArray)>1){
                        $MultipleFilesNewSerialized = serialize($MultipleFilesNewArray);
                        $queryInsertNewMultipleFiles .= "('$rowObject->id', '".($MultipleFilesNewSerialized)."'),";
                    }else{// then count($MultipleFilesNewArray) must be == 1
                        $queryInsertNewMultipleFiles .= "('$rowObject->id', ''),";
                    }
                }else{// then must be completely deleted, realIdSource and all additional files
                    // Keep the row in the follow-up cleanup set when no file
                    // references remain after the MultipleFiles repair pass.
                    $deletedWpUploads[] = $rowObject->id;
                }

            }


            $queryInsertNewMultipleFiles = substr($queryInsertNewMultipleFiles, 0, -1);
            $queryInsertNewMultipleFiles .= " ON DUPLICATE KEY UPDATE MultipleFiles = VALUES(MultipleFiles)";
            /*            echo "<br>";
                        var_dump('$queryInsertNewMultipleFiles');
                        var_dump($queryInsertNewMultipleFiles);*/
            $wpdb->query($queryInsertNewMultipleFiles);

            if(!empty($queryHasRealIdDeletedCenterStringPart)){
                $queryHasRealIdDeleted = substr($queryHasRealIdDeleted.$queryHasRealIdDeletedCenterStringPart, 0, -1);
                $queryHasRealIdDeleted .= " ON DUPLICATE KEY UPDATE NamePic = VALUES(NamePic), ImgType = VALUES(ImgType), WpUpload = VALUES(WpUpload), Width = VALUES(Width), Height = VALUES(Height),  rThumb = VALUES(rThumb), Exif = VALUES(Exif)";
                /*                echo "<br>";
                                var_dump('$queryHasRealIdDeleted');
                                var_dump($queryHasRealIdDeleted);*/
                $wpdb->query($queryHasRealIdDeleted);
            }

        }

        /*        var_dump('$deletedWpUploads 2');

                echo "<pre>";
                print_r($deletedWpUploads);
                echo "</pre>";*/

        // Run the follow-up lookup for rows whose primary WpUpload reference is
        // still one of the deleted attachment IDs.

        $collect = '';

        // Remove any gallery entries in any gallery that still use one of the
        // deleted WordPress attachments as their primary WpUpload reference.
        foreach ($deletedWpUploads as $value){
            if(empty($collect)){
                $collect .= 'WpUpload='.$value;
            }else{
                $collect .= ' OR WpUpload='.$value;
            }
        }

        // Frontend deletes can return deleted WpUpload IDs when the original
        // source file was removed from storage, so this pass removes matching
        // gallery rows across all galleries after attachment deletion.
        $deletedImages = $wpdb->get_results( "SELECT id, GalleryID FROM $tablename WHERE ($collect) ORDER BY GalleryID DESC, id DESC");

        $deletedImagesSortedByGalleryIdArrayWithObjects = array();

        /*        var_dump('$deletedImages');
                echo "<pre>";
                print_r($deletedImages);
                echo "<pre>";*/

        if(count($deletedImages)){

            foreach ($deletedImages as $rowObject){
                if(empty($deletedImagesSortedByGalleryIdArrayWithObjects[$rowObject->GalleryID])){
                    $deletedImagesSortedByGalleryIdArrayWithObjects[$rowObject->GalleryID] = array();
                }
                $deletedImagesSortedByGalleryIdArrayWithObjects[$rowObject->GalleryID][$rowObject->id] = $rowObject->id;// keep the image ID as the array key because cg_delete_images expects that shape
            }

            /*            var_dump('$deletedImagesSortedByGalleryIdArrayWithObjects');
                        echo "<pre>";
                        print_r($deletedImagesSortedByGalleryIdArrayWithObjects);
                        echo "<pre>";*/

            foreach ($deletedImagesSortedByGalleryIdArrayWithObjects as $GalleryID => $deleteValuesArray){
                // Reuse the standard delete helper, but flag the call as a
                // consecutive cleanup so attachments are not deleted twice.
                cg_delete_images($GalleryID,$deleteValuesArray,array(),false,true);
            }

        }

        if(count($correctedRowObjects)){
            // Rebuild the JSON payloads for rows that stayed alive but had their
            // primary file metadata promoted to another remaining attachment.
            /*            var_dump('$correctedRowObjects');
                        echo "<pre>";
                        print_r($correctedRowObjects);
                        echo "<pre>";*/

            $correctedRowObjectsSortedByGalleryID = [];
            $ExifDataByRealIds = [];
            foreach ($correctedRowObjects as $rowObject){
                if(empty($rowObject->Active)){continue;}// only active rows need regenerated frontend JSON data
                $ExifDataByRealIds[$rowObject->id] = $rowObject->Exif;
                if(empty($correctedRowObjectsSortedByGalleryID[$rowObject->GalleryID])){
                    $correctedRowObjectsSortedByGalleryID[$rowObject->GalleryID] = [];
                }
                $correctedRowObjectsSortedByGalleryID[$rowObject->GalleryID][] = $rowObject;
            }
            $thumbSizesWp = array();
            $thumbSizesWp['thumbnail_size_w'] = get_option("thumbnail_size_w");
            $thumbSizesWp['medium_size_w'] = get_option("medium_size_w");
            $thumbSizesWp['large_size_w'] = get_option("large_size_w");
            $wp_upload_dir = wp_upload_dir();

            /*            var_dump('$correctedRowObjectsSortedByGalleryID');
                        echo "<pre>";
                        print_r($correctedRowObjectsSortedByGalleryID);
                        echo "<pre>";*/

            foreach ($correctedRowObjectsSortedByGalleryID as $GalleryID => $rowObjectsArray){
                cg_set_json_data_of_row_objects($rowObjectsArray,$GalleryID,$wp_upload_dir,$thumbSizesWp,$ExifDataByRealIds);
            }

        }

    }
}



?>
