<?php
add_action('cg_copy_comments','cg_copy_comments');
if(!function_exists('cg_copy_comments')){
    function cg_copy_comments($cg_copy_start,$oldGalleryID,$nextGalleryID,$collectImageIdsArray){
        if(!empty($collectImageIdsArray)){

            global $wpdb;

            $tablename = $wpdb->prefix . "contest_gal1ery";
            $tablename_comments = $wpdb->prefix . "contest_gal1ery_comments";

            if($cg_copy_start==0){
                $query = "INSERT INTO $tablename_comments
    SELECT NULL, pid, $nextGalleryID, Name, Date, Comment, Timestamp, IP, WpUserId, ReviewTstamp, Active
    FROM $tablename_comments
    WHERE GalleryID IN ($oldGalleryID)";
                $wpdb->query($query);
            }

            $whenThenString = '';
            foreach($collectImageIdsArray as $oldImageId => $newImageId){
                $whenThenString .= "WHEN $oldImageId THEN $newImageId ";
            }

            $whenThenString = substr_replace($whenThenString ,"", -1);

            $wpdb->query($wpdb->prepare("UPDATE $tablename_comments SET pid = CASE pid $whenThenString ELSE pid END WHERE GalleryID IN (%d)",[$nextGalleryID]));

            $wp_upload_dir = wp_upload_dir();
            $time = time();

	        $all_insert_ids = $wpdb->get_results("SELECT id, Timestamp FROM $tablename_comments WHERE GalleryID = '$nextGalleryID'");

            // image files first, because must be newer, released in 16.0.0
            if(is_dir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$oldGalleryID.'/json/image-comments/ids')){
                if(!is_dir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$nextGalleryID.'/json/image-comments/ids')){
                    mkdir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$nextGalleryID.'/json/image-comments/ids',0755,true);
                }
                $fileImageCommentDirs = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$oldGalleryID.'/json/image-comments/ids/*');
                if(count($fileImageCommentDirs)){
                    foreach ($fileImageCommentDirs as $fileImageCommentDir){
		                $oldImageId = substr($fileImageCommentDir,strrpos($fileImageCommentDir,'/')+1,strlen($fileImageCommentDir));
                        if(!is_dir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$nextGalleryID.'/json/image-comments/ids/'.$collectImageIdsArray[$oldImageId])){
                            mkdir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$nextGalleryID.'/json/image-comments/ids/'.$collectImageIdsArray[$oldImageId],0755,true);
                        }
                        $fileImageCommentDirFiles = glob($fileImageCommentDir.'/*.json');
                        if(!empty($fileImageCommentDirFiles)){
                            foreach ($fileImageCommentDirFiles as $fileImageCommentDirFile){
                                $fileImageCommentDirFileContent = json_decode(file_get_contents($fileImageCommentDirFile));
                                $randomAdder = md5(uniqid('cg-comment'));
                                $newCommentId = $time.'-'.substr($randomAdder,0,6);
                                    $imageCommentNewArray = array();
	                            foreach ($fileImageCommentDirFileContent as $fileImageCommentDirFileKey => $fileImageCommentDirFileValue){
                                    $imageCommentNewArray[$newCommentId] = array();
                                    $imageCommentNewArray[$newCommentId]['date'] = date("Y/m/d, G:i",$fileImageCommentDirFileValue->timestamp);
                                    $imageCommentNewArray[$newCommentId]['timestamp'] = $fileImageCommentDirFileValue->timestamp;
                                    $imageCommentNewArray[$newCommentId]['name'] = $fileImageCommentDirFileValue->name;
                                    $imageCommentNewArray[$newCommentId]['comment'] = $fileImageCommentDirFileValue->comment;
                                    $imageCommentNewArray[$newCommentId]['Active'] = (!empty($fileImageCommentDirFileValue->Active)) ? $fileImageCommentDirFileValue->Active : 1;
                                    $imageCommentNewArray[$newCommentId]['ReviewTstamp'] = (!empty($fileImageCommentDirFileValue->ReviewTstamp)) ? $fileImageCommentDirFileValue->ReviewTstamp : '';
                                    if(!empty($fileImageCommentDirFileValue->WpUserId)){// better to check here for sure
										// WpUserId and IP will be not set new comments since 23.1.3
                                        $imageCommentNewArray[$newCommentId]['WpUserId'] = $fileImageCommentDirFileValue->WpUserId;
                                    }
                                    if(!empty($fileImageCommentDirFileValue->userIP)){// better to check here for sure
										// WpUserId and IP will be not set in new comments since 23.1.3
                                        $imageCommentNewArray[$newCommentId]['userIP'] = $fileImageCommentDirFileValue->userIP;
                                    }
									// since 23.1.3 IsWpUser will be set
	                                $imageCommentNewArray[$newCommentId]['IsWpUser'] = (isset($fileImageCommentDirFileValue->IsWpUser)) ? $fileImageCommentDirFileValue->IsWpUser : 0;
	                                $imageCommentNewArray[$newCommentId]['insert_id'] = '';
									if(!empty($fileImageCommentDirFileValue->insert_id)){
										foreach ($all_insert_ids as $insert_id_to_check){
											if($insert_id_to_check->Timestamp == $fileImageCommentDirFileValue->timestamp){
												$imageCommentNewArray[$newCommentId]['insert_id'] = $insert_id_to_check->id;
											}
										}
	                                }
                                }
                                file_put_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$nextGalleryID.'/json/image-comments/ids/'.$collectImageIdsArray[$oldImageId].'/'.$newCommentId.'.json',json_encode($imageCommentNewArray));
                            }
                         }
                    }
                }
            }

            $wp_upload_dir = wp_upload_dir();

            foreach ($collectImageIdsArray as $oldImageId => $newImageId){
                // now can be done via common way, like when activating or repairing
                cg_create_comments_json_file_when_activating_image($wp_upload_dir,$nextGalleryID,$newImageId);
            }

        }

    }
}
