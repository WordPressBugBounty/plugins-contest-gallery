<?php

$deletedWpUploadsFromSpace = array();

if(!empty($_POST['cg_delete'])){

    /*$deletedWpUploadsFromSpace[] = 17;
    $deletedWpUploadsFromSpace[] = 18;

    cg_delete_images_of_deleted_wp_uploads($deletedWpUploadsFromSpace);*/

    // WpUploads in Multiple Files has to be collected
    $collect = "";

    foreach ($_POST['cg_delete'] as $value){
        $value = absint($value);
        if(empty($collect)){
            $collect .= "id = '$value'";
        }else{
            $collect .= " OR id = '$value'";
        }
    }

    $filesWithDeletedWpUploadsInMultipleFiles = $wpdb->get_results( "SELECT id, GalleryID, WpUpload, MultipleFiles, EcommerceEntry FROM $tablename WHERE ($collect) AND MultipleFiles != '' ORDER BY GalleryID DESC, id DESC");


	// // can not be deleted anymore if is for sale!!!
    /*if(!isset($_POST['removeEcommerceEntryWpUploadIds'])){
        $_POST['removeEcommerceEntryWpUploadIds'] = [];
    }

    $hasEcommerceEntries = false;

    foreach ($filesWithDeletedWpUploadsInMultipleFiles as $rowObject){
        if(!empty($rowObject->EcommerceEntry)){
            $hasEcommerceEntries = true;
            if(!isset($_POST['removeEcommerceEntryWpUploadIds'][$rowObject->id])){
                $_POST['removeEcommerceEntryWpUploadIds'][$rowObject->id] = [];
                $_POST['removeEcommerceEntryWpUploadIds'][$rowObject->id][$rowObject->EcommerceEntry] = [];
            }
            if(!empty($rowObject->MultipleFiles) && $rowObject->MultipleFiles!='""'){
                $MultipleFiles = unserialize($rowObject->MultipleFiles);
                foreach ($MultipleFiles as $MultipleFile){
                    var_dump('$MultipleFile');
                    var_dump($MultipleFile);
                    echo "<pre>";
                        print_r($MultipleFile);
                    echo "</pre>";
                    if(!empty($MultipleFile['isRealIdSource'])){
                        $_POST['removeEcommerceEntryWpUploadIds'][$rowObject->id][$rowObject->EcommerceEntry][] = $rowObject->WpUpload;
                    }else{
                        $_POST['removeEcommerceEntryWpUploadIds'][$rowObject->id][$rowObject->EcommerceEntry][] = $MultipleFile['WpUpload'];
                    }
                }
            }else{
                $_POST['removeEcommerceEntryWpUploadIds'][$rowObject->id][$rowObject->EcommerceEntry][] = $rowObject->WpUpload;
            }
        }
    }
    var_dump('$hasEcommerceEntries');
    var_dump($hasEcommerceEntries);

    var_dump('removeEcommerceEntryWpUploadIds delete pics');
    echo "<pre>";
    print_r($_POST['removeEcommerceEntryWpUploadIds']);
    echo "</pre>";

    if($hasEcommerceEntries){
        cg_move_gallery_changes_file_from_ecommerce_sale_folder($GalleryID);
        $isRemoveEcommerceEntryWpUploadIdsExecuted = true;
    }

    */


    $MultipleFilesToDelete = [];
    if(count($filesWithDeletedWpUploadsInMultipleFiles)){
        foreach ($filesWithDeletedWpUploadsInMultipleFiles as $rowObject){
            if($rowObject->MultipleFiles!='""'){
                $MultipleFiles = unserialize($rowObject->MultipleFiles);
                if(!empty($MultipleFiles)){ //check for sure if really exists and unserialize went right, because might happen that "" was in database from earlier versions
                    $MultipleFilesToDelete[$rowObject->id] = $MultipleFiles;
                }
            }
        }
    }

    /*    var_dump('$MultipleFilesToDelete');
        echo "<pre>";
        print_r($MultipleFilesToDelete);
        echo "</pre>";*/

    $deletedWpUploadsFromSpace = cg_delete_images($GalleryID,$_POST['cg_delete'],$deletedWpUploadsFromSpace,false,false,$MultipleFilesToDelete);

    /*var_dump($deletedWpUploadsFromSpace);
        echo "<pre>";
        print_r($deletedWpUploadsFromSpace);
        echo "</pre>";*/

    // in case $_POST['cgDeleteOriginalImageSourceAlso'] is sent or $DeleteFromStorageIfDeletedInFrontend and frontend delete will be done, deleted wpuploads be returned,
    // so all entries in all galleries will be deleted from the image, after wp uploads were deleted from space
    if(!empty($deletedWpUploadsFromSpace)){
        cg_delete_images_of_deleted_wp_uploads($deletedWpUploadsFromSpace);
    }

}


?>