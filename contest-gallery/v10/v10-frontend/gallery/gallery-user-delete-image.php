<?php
if(!defined('ABSPATH')){exit;}

// Read the gallery instance ID early so error responses can still target the
// correct frontend gallery instance before the full request is normalized.
$galeryIDuser = sanitize_text_field($_REQUEST['galeryIDuser']);

if(!is_user_logged_in()){
    ?>
    <script data-cg-processing="true">

        var message = <?php echo json_encode('Please do not manipulate image delete! (0)');?>;
        var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
        cgJsClass.gallery.function.message.show(galeryIDuser,message);

    </script>
    <?php

    return;
}
else{
    $wp_get_current_user = wp_get_current_user();
    $WpUserIdLoggedIn = $wp_get_current_user->data->ID;
}

// Normalize the incoming request before ownership and hash checks are applied.
$_REQUEST = cg1l_sanitize_post($_REQUEST);
$_POST = cg1l_sanitize_post($_POST);

// Build the request context that ties the frontend gallery instance to the
// currently authenticated WordPress user.
$galeryID = intval(sanitize_text_field($_REQUEST['gid']));
$pictureID = intval(sanitize_text_field($_REQUEST['pid']));
$userId = intval(sanitize_text_field($_REQUEST['uid']));
$galeryIDuser = sanitize_text_field($_REQUEST['galeryIDuser']);
$galleryHash = sanitize_text_field($_REQUEST['galleryHash']);
$galleryHashToCompare = cg_hash_function('---cngl1---'.$galeryIDuser, $galleryHash);

// Reject requests that try to delete an entry on behalf of another user.
if($WpUserIdLoggedIn!=$userId){
    ?>
    <script data-cg-processing="true">

        var message = <?php echo json_encode('Please do not manipulate image delete! (1)');?>;
        var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
        cgJsClass.gallery.function.message.show(galeryIDuser,message);

    </script>
    <?php

    return;
}

// Keep the gallery hash check in place so the delete request stays bound to
// the rendered gallery instance that created it.
if (!is_numeric($pictureID) or !is_numeric($galeryID) or !is_numeric($userId) or ($galleryHash != $galleryHashToCompare)){
    ?>
    <script data-cg-processing="true">

        var message = <?php echo json_encode('Please do not manipulate image delete! (2)');?>;
        var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
        cgJsClass.gallery.function.message.show(galeryIDuser,message);

    </script>
    <?php

    return;
}
else {

    $tablename = $wpdb->prefix ."contest_gal1ery";
    $tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";

    // Only active entries that belong to the requesting user may be removed
    // through the frontend delete flow.
    $isUserImage = $wpdb->get_var( $wpdb->prepare(
        "
        SELECT COUNT(*) AS UserImages
        FROM $tablename 
        WHERE id = %d and GalleryID = %d and WpUserId = %d and Active = %d
    ",
        $pictureID,$galeryID,$userId,1
    ) );

    if(empty($isUserImage)){

        ?>
        <script data-cg-processing="true">

            var message = <?php echo json_encode('Please do not manipulate image delete! (3)');?>;
            var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
            cgJsClass.gallery.function.message.show(galeryIDuser,message);

        </script>
        <?php

        return;

    }else{

        $valuesToDeleteArray = array($pictureID => $pictureID);
        $isMultipleFilesDelete = false;

        // Reload the storage option so the shared delete helper knows whether
        // the original WordPress attachment should also be removed.
        $DeleteFromStorageIfDeletedInFrontend = $wpdb->get_var($wpdb->prepare(
            "SELECT DeleteFromStorageIfDeletedInFrontend FROM $tablename_pro_options WHERE GalleryID = %d",
            $galeryID
        ));

        // Load the current entry metadata again to enforce the sales lock on
        // the server side even if the frontend state was bypassed.
        $entryRow = $wpdb->get_row($wpdb->prepare(
            "SELECT MultipleFiles, EcommerceEntry FROM $tablename WHERE id = %d",
            $pictureID
        ));

        if(!empty($entryRow->EcommerceEntry)){
            ?>
            <script data-cg-processing="true">

                var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
                cgJsClass.gallery.function.message.showPro(galeryIDuser,'Entries which are activated for selling can not be deleted.<br>Deactivate entries for sale in "Sales settings" area first.');

            </script>
            <?php
            return;
        }

        $MultipleFilesFromUserFrontendDelete = $entryRow->MultipleFiles;
        $MultipleFilesToDelete = [];

        // Forward the serialized multi-file payload into the shared delete
        // helper so every related attachment can be cleaned up consistently.
        if(!empty($MultipleFilesFromUserFrontendDelete)){
            $isMultipleFilesDelete = true;
            $MultipleFilesToDelete = [$pictureID => unserialize($MultipleFilesFromUserFrontendDelete)];
        }

        $DeleteFromStorageIfDeletedInFrontend = !empty($DeleteFromStorageIfDeletedInFrontend);

        /*        var_dump('$DeleteFromStorageIfDeletedInFrontend');
                var_dump($DeleteFromStorageIfDeletedInFrontend);

                var_dump('$valuesToDeleteArray');

                echo "<pre>";
                    print_r($valuesToDeleteArray);
                echo "</pre>";

                var_dump('$MultipleFilesToDelete');

                echo "<pre>";
                    print_r($MultipleFilesToDelete);
                echo "</pre>";*/

        $deletedWpUploads = array();
        $deletedWpUploads = cg_delete_images($galeryID,$valuesToDeleteArray,$deletedWpUploads,$DeleteFromStorageIfDeletedInFrontend,false, $MultipleFilesToDelete);

        /*        var_dump('$deletedWpUploads');

                echo "<pre>";
                print_r($deletedWpUploads);
                echo "</pre>";*/

        if(!empty($deletedWpUploads)){
            cg_delete_images_of_deleted_wp_uploads($deletedWpUploads);
        }

        ?>
        <script data-cg-processing="true">
            // The frontend still expects an executable script fragment here so
            // it can update the in-memory gallery state without a full reload.
            var gid = <?php echo json_encode($galeryIDuser);?>;
            var realIdToDelete = <?php echo json_encode($pictureID);?>;
            var isMultipleFilesDelete = <?php echo json_encode($isMultipleFilesDelete);?>;
            cgJsClass.gallery.getJson.removeImageFromImageData(gid,realIdToDelete,true);
            cgJsClass.gallery.function.message.show(gid,(isMultipleFilesDelete) ? cgJsClass.gallery.language[gid].DeleteImagesConfirm : cgJsClass.gallery.language[gid].DeleteImageConfirm);

        </script>
        <?php

    }

}

?>

