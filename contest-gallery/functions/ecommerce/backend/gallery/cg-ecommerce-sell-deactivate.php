<?php
if(!function_exists('cg_ecommerce_sale_deactivate')){
    function cg_ecommerce_sale_deactivate(){

        global $wpdb;
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

        $_POST = cg1l_sanitize_post($_POST);

       $GalleryID = absint($_POST['cgSellContainer']['GalleryID']);
       $realId = absint($_POST['cgSellContainer']['realId']);

        $sqlObjectFile = $wpdb->get_row("SELECT * FROM $tablename WHERE id = '$realId'");
        $ecommerceEntryId = $sqlObjectFile->EcommerceEntry;

		if(!empty($ecommerceEntryId)){
			$ecommerceEntry = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_entries WHERE id = '$ecommerceEntryId'");

			$isAllWpUploadsSuccessfulMoved = true;

			if($ecommerceEntry->IsDownload){
				$isAllWpUploadsSuccessfulMoved = cg_move_file_from_ecommerce_sale_folder($realId, $GalleryID,$ecommerceEntryId);
			}

			$wpdb->query($wpdb->prepare(
				"
                        DELETE FROM $tablename_ecommerce_entries WHERE id = %d
                    ",
				$sqlObjectFile->EcommerceEntry
			));

			$wpdb->update(
				"$tablename",
				array( 'EcommerceEntry' => 0),
				array('id' => $sqlObjectFile->id),
				array('%d'),
				array('%d')
			);

			// if $isAllWpUploadsSuccessfulMoved then ecommerce entry folder can be finally removed, so no empty folder then
			if($isAllWpUploadsSuccessfulMoved){
				$wp_upload_dir = wp_upload_dir();
				$ecommerceEntryFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId;
				cg_remove_folder_recursively($ecommerceEntryFolder);
			}
		}


    }
}

