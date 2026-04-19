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

			$wp_upload_dir = wp_upload_dir();
			$imageJsonPath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-data/image-data-'.$sqlObjectFile->id.'.json';
			if(file_exists($imageJsonPath)){
				$imageJson = json_decode(file_get_contents($imageJsonPath),true);
				if(!empty($imageJson)){
					$imageJson['EcommerceEntry'] = 0;
					file_put_contents($imageJsonPath,json_encode($imageJson));
				}
			}

			cg1l_push_recent_id_file($GalleryID,$sqlObjectFile->id,'image-main-data-last-update');
			cg1l_push_recent_id_file($GalleryID,$sqlObjectFile->id,'image-query-data-last-update');
			cg1l_push_recent_id_file($GalleryID,$sqlObjectFile->id,'image-urls-data-last-update');
			cg1l_create_last_updated_time_file_image_data($GalleryID);

			// if $isAllWpUploadsSuccessfulMoved then ecommerce entry folder can be finally removed, so no empty folder then
			if($isAllWpUploadsSuccessfulMoved){
				$ecommerceEntryFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId;
				cg_remove_folder_recursively($ecommerceEntryFolder);
			}
		}


    }
}
