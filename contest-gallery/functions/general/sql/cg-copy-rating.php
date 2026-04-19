<?php

add_action('cg_copy_rating','cg_copy_rating');
if(!function_exists('cg_copy_rating')){
    function cg_copy_rating($cg_copy_start,$oldGalleryID,$nextGalleryID,$collectImageIdsArray){
        if(!empty($collectImageIdsArray)){

            global $wpdb;

            $tablename_ip = $wpdb->prefix . "contest_gal1ery_ip";

            $uploadFolder = wp_upload_dir();
            $galleryUpload = $uploadFolder['basedir'] . '/contest-gallery/gallery-id-' . $nextGalleryID . '';

            $oldImageIds = [];
            $newImageIds = [];
            $whenThenString = '';

            foreach($collectImageIdsArray as $oldImageId => $newImageId){
                if (
                    $oldImageId !== null && $oldImageId !== '' && is_numeric($oldImageId) &&
                    $newImageId !== null && $newImageId !== '' && is_numeric($newImageId)
                ) {
                    $oldImageId = (int)$oldImageId;
                    $newImageId = (int)$newImageId;

                    $oldImageIds[] = $oldImageId;
                    $newImageIds[] = $newImageId;

                    if ($oldImageId === $newImageId) {
                        continue;
                    }

                    $whenThenString .= "WHEN $oldImageId THEN $newImageId ";
                }
            }

            $oldImageIds = array_values(array_unique($oldImageIds));
            $newImageIds = array_values(array_unique($newImageIds));

            if(empty($oldImageIds) || empty($newImageIds)){
                return;
            }

            $oldImageIdsPlaceholders = implode(',', array_fill(0, count($oldImageIds), '%d'));
            $newImageIdsPlaceholders = implode(',', array_fill(0, count($newImageIds), '%d'));

            // PROCESSING EXAMPLE (EXAMPLES ARE INDEPENDENT FROM EACH OTHER)

            // First
            /*
            INSERT INTO wp_contest_gal1ery_ip
    SELECT NULL, pid, IP, 3001, Rating, RatingS, WpUserId, Tstamp, DateVote, VoteDate, OptionSet, CookieId
    FROM wp_contest_gal1ery_ip
    WHERE GalleryID IN (3000)*/

            // Then
            // UPDATE wp_contest_gal1ery_ip SET pid = CASE pid WHEN 22717 THEN 30000 WHEN 22716 THEN 30001 ELSE pid END WHERE GalleryID IN (341)


            // First
            /* Have to look like this:
            INSERT INTO wp_contest_gal1ery_ip
    SELECT NULL, pid, IP, 3001, Rating, RatingS, WpUserId, Tstamp, DateVote, VoteDate, OptionSet, CookieId
    FROM wp_contest_gal1ery_ip
    WHERE GalleryID IN (3000)*/

            //Have to look like this:INSERT INTO wp_contest_gal1ery_ip
            //SELECT NULL, pid, IP, 3001, Rating, RatingS, WpUserId, Tstamp, DateVote, VoteDate, OptionSet, CookieId
            //FROM wp_contest_gal1ery_ip
            //WHERE GalleryID IN (3000)
            $insertQuery = "INSERT INTO $tablename_ip
SELECT NULL, pid, IP, %d, Rating, RatingS, WpUserId, VoteDate, Tstamp, OptionSet, CookieId, Category, CategoriesOn
FROM $tablename_ip
WHERE GalleryID = %d AND pid IN ($oldImageIdsPlaceholders)";
            $insertParameters = array_merge([$nextGalleryID, $oldGalleryID], $oldImageIds);
            $wpdb->query($wpdb->prepare($insertQuery,$insertParameters));

            if(!empty($whenThenString)){
                $whenThenString = rtrim($whenThenString);
                //Have to look like this:UPDATE wp_contest_gal1ery_ip SET pid = CASE pid WHEN 22717 THEN 30000 WHEN 22716 THEN 30001 ELSE pid END WHERE GalleryID IN (341)
                $updatePidQuery = "UPDATE $tablename_ip SET pid = CASE pid $whenThenString ELSE pid END WHERE GalleryID = %d AND pid IN ($oldImageIdsPlaceholders)";
                $updatePidParameters = array_merge([$nextGalleryID], $oldImageIds);
                $wpdb->query($wpdb->prepare($updatePidQuery,$updatePidParameters));
            }

            // Create categories

            $oldAndNextGalleryIdsCategories = json_decode(file_get_contents($galleryUpload . '/json/' . $nextGalleryID . '-collect-cat-ids-array.json'),true);

            if(!empty($oldAndNextGalleryIdsCategories)){

                //Have to look like this:UPDATE wp_contest_gal1ery_ip SET category = CASE category WHEN 22717 THEN 30000 WHEN 22716 THEN 30001 ELSE category END WHERE GalleryID IN (341)
                $whenThenString = '';
                foreach($oldAndNextGalleryIdsCategories as $oldCategoryId => $newCategoryId){
                    if(!is_numeric($oldCategoryId) || !is_numeric($newCategoryId)){
                        continue;
                    }
                    $oldCategoryId = (int)$oldCategoryId;
                    $newCategoryId = (int)$newCategoryId;
                    $whenThenString .= "WHEN $oldCategoryId THEN $newCategoryId ";
                }

                if(!empty($whenThenString)){
                    $whenThenString = rtrim($whenThenString);

                    //Same for categories
                    // have to be done two times, CategoriesOn = 0 and CategoriesOn = 1
                    $updateCategoryQuery = "UPDATE $tablename_ip SET Category = CASE Category $whenThenString ELSE Category END WHERE CategoriesOn = 0 AND GalleryID = %d AND pid IN ($newImageIdsPlaceholders)";
                    $updateCategoryParameters = array_merge([$nextGalleryID], $newImageIds);
                    $wpdb->query($wpdb->prepare($updateCategoryQuery,$updateCategoryParameters));

                    //Same for categories
                    // have to be done two times, CategoriesOn = 0 and CategoriesOn = 1
                    $updateCategoryQuery = "UPDATE $tablename_ip SET Category = CASE Category $whenThenString ELSE Category END WHERE CategoriesOn = 1 AND GalleryID = %d AND pid IN ($newImageIdsPlaceholders)";
                    $updateCategoryParameters = array_merge([$nextGalleryID], $newImageIds);
                    $wpdb->query($wpdb->prepare($updateCategoryQuery,$updateCategoryParameters));
                }

            }


        }



    }
}
