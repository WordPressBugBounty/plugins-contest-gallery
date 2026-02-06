<?php
add_action('cg_delete_images_of_deleted_wp_uploads','cg_delete_images_of_deleted_wp_uploads');
if(!function_exists('cg_delete_images_of_deleted_wp_uploads')){
    function cg_delete_images_of_deleted_wp_uploads($deletedWpUploads){

        global $wpdb;

        $tablename = $wpdb->prefix . "contest_gal1ery";

        // first delete multiple files and there if exists!!!!
        // if is realIdSource then will be not completly deleted!
        // replace query will be done additionally
        // since v18.0.0 has to be done
        $collect = "";

        /*        var_dump('$deletedWpUploads 1');

                echo "<pre>";
                print_r($deletedWpUploads);
                echo "</pre>";*/

        // realIdSource can not be anymore, because must be deleted before
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

            $queryInsertNewMultipleFiles = 'INSERT INTO  '.$tablename.' (id, MultipleFiles) VALUES ';
            $queryHasRealIdDeleted = 'INSERT INTO '.$tablename.' (id, NamePic, ImgType, WpUpload, Width, Height, rThumb, Exif) VALUES ';
            $queryHasRealIdDeletedCenterStringPart = '';

            foreach ($filesWithDeletedWpUploadsInMultipleFiles as $rowObject){

                $hasRealIdDeleted = false;

                $MultipleFilesNewArray =[];
                $MultipleFilesArray = unserialize($rowObject->MultipleFiles);
                $newOrder = 1;

                if(!empty($MultipleFilesArray)){//check for sure if really exists and unserialize went right, because might happen that "" was in database from earlier versions
                    foreach ($MultipleFilesArray as $order => $array){// als with realIdSource must be already deleted before
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

        // then delete rest wp uploads

        $collect = '';

        foreach ($deletedWpUploads as $value){
            if(empty($collect)){
                $collect .= 'WpUpload='.$value;
            }else{
                $collect .= ' OR WpUpload='.$value;
            }
        }

        // in case $_POST['cgDeleteOriginalImageSourceAlso'] is sent or $DeleteFromStorageIfDeletedInFrontend and frontend delete will be done, deleted wpuploads be returned,
        // so all entries in all galleries will be deleted from the image, after wp uploads were deleted from space
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
                $deletedImagesSortedByGalleryIdArrayWithObjects[$rowObject->GalleryID][$rowObject->id] = $rowObject->id;// $rowObject->id as key because same system as always
            }

            /*            var_dump('$deletedImagesSortedByGalleryIdArrayWithObjects');
                        echo "<pre>";
                        print_r($deletedImagesSortedByGalleryIdArrayWithObjects);
                        echo "<pre>";*/

            foreach ($deletedImagesSortedByGalleryIdArrayWithObjects as $GalleryID => $deleteValuesArray){
                cg_delete_images($GalleryID,$deleteValuesArray,array(),false,true);
            }

        }

        if(count($correctedRowObjects)){
            /*            var_dump('$correctedRowObjects');
                        echo "<pre>";
                        print_r($correctedRowObjects);
                        echo "<pre>";*/

            $correctedRowObjectsSortedByGalleryID = [];
            $ExifDataByRealIds = [];
            foreach ($correctedRowObjects as $rowObject){
                if(empty($rowObject->Active)){continue;}// get only active to set array in json files
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