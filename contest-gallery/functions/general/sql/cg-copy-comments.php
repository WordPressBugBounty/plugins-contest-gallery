<?php
add_action('cg_copy_comments','cg_copy_comments');
if(!function_exists('cg_copy_comments')){
    function cg_copy_comments($cg_copy_start,$oldGalleryID,$nextGalleryID,$collectImageIdsArray){
        $commentIdMap = array();
        if(!empty($collectImageIdsArray)){

            global $wpdb;

            $tablename_comments = $wpdb->prefix . "contest_gal1ery_comments";

            $oldImageIds = [];
            $newImageIds = [];

            foreach($collectImageIdsArray as $oldImageId => $newImageId){
                if (
                    $oldImageId !== null && $oldImageId !== '' && is_numeric($oldImageId) &&
                    $newImageId !== null && $newImageId !== '' && is_numeric($newImageId)
                ) {
                    $oldImageId = (int)$oldImageId;
                    $newImageId = (int)$newImageId;

                    $oldImageIds[] = $oldImageId;
                    $newImageIds[] = $newImageId;

                }
            }

            $oldImageIds = array_values(array_unique($oldImageIds));
            $newImageIds = array_values(array_unique($newImageIds));

            if(empty($oldImageIds) || empty($newImageIds)){
                return $commentIdMap;
            }

            $oldImageIdsPlaceholders = implode(',', array_fill(0, count($oldImageIds), '%d'));
            $sourceComments = $wpdb->get_results($wpdb->prepare(
                "SELECT *
                FROM $tablename_comments
                WHERE GalleryID = %d AND pid IN ($oldImageIdsPlaceholders)
                ORDER BY id ASC",
                array_merge(array($oldGalleryID),$oldImageIds)
            ),ARRAY_A);

            foreach($sourceComments as $sourceComment){
                $oldCommentId = (!empty($sourceComment['id'])) ? absint($sourceComment['id']) : 0;
                $oldCommentPid = (!empty($sourceComment['pid'])) ? absint($sourceComment['pid']) : 0;
                $newCommentPid = (!empty($collectImageIdsArray[$oldCommentPid])) ? absint($collectImageIdsArray[$oldCommentPid]) : 0;
                if(empty($oldCommentId) || empty($newCommentPid)){
                    continue;
                }

                unset($sourceComment['id']);
                $sourceComment['pid'] = $newCommentPid;
                $sourceComment['GalleryID'] = absint($nextGalleryID);
                if(function_exists('cg_paid_actions_normalize_copied_values')){
                    $sourceComment = cg_paid_actions_normalize_copied_values($sourceComment);
                }
                $inserted = $wpdb->insert($tablename_comments,$sourceComment);
                if($inserted !== false && !empty($wpdb->insert_id)){
                    $commentIdMap[$oldCommentId] = absint($wpdb->insert_id);
                }
            }

            $wp_upload_dir = wp_upload_dir();
            $time = time();

	        // image files first, because must be newer, released in 16.0.0
            $oldImageCommentIdsDir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$oldGalleryID.'/json/image-comments/ids';
            $newImageCommentIdsDir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$nextGalleryID.'/json/image-comments/ids';
	            if(is_dir($oldImageCommentIdsDir)){
	                if(!is_dir($newImageCommentIdsDir)){
	                    mkdir($newImageCommentIdsDir,0755,true);
	                }
	                foreach ($collectImageIdsArray as $oldImageId => $newImageId){
                        $oldImageId = absint($oldImageId);
                        $newImageId = absint($newImageId);
                        if(empty($oldImageId) || empty($newImageId)){
                            continue;
                        }
                        $fileImageCommentDir = $oldImageCommentIdsDir.'/'.$oldImageId;
                        if(!is_dir($fileImageCommentDir)){
                            continue;
                        }
                        if(!is_dir($newImageCommentIdsDir.'/'.$newImageId)){
                            mkdir($newImageCommentIdsDir.'/'.$newImageId,0755,true);
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
                                    $imageCommentNewArray[$newCommentId]['vote'] = (!empty($fileImageCommentDirFileValue->vote)) ? absint($fileImageCommentDirFileValue->vote) : 0;
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
                                        $oldCommentInsertId = absint($fileImageCommentDirFileValue->insert_id);
                                        if(!empty($commentIdMap[$oldCommentInsertId])){
											$imageCommentNewArray[$newCommentId]['insert_id'] = $commentIdMap[$oldCommentInsertId];
										}
	                                }
                                }
                                file_put_contents($newImageCommentIdsDir.'/'.$newImageId.'/'.$newCommentId.'.json',json_encode($imageCommentNewArray));
                            }
                         }
                    }
	            }

            $wp_upload_dir = wp_upload_dir();

            foreach ($collectImageIdsArray as $oldImageId => $newImageId){
                // now can be done via common way, like when activating or repairing
                cg_create_comments_json_file_when_activating_image($wp_upload_dir,$nextGalleryID,$newImageId,true);
            }

        }

        return $commentIdMap;

    }
}
