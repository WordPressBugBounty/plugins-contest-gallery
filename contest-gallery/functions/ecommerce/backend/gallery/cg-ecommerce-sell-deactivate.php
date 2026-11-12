<?php
if(!function_exists('cg_ecommerce_sale_deactivate')){
    function cg_ecommerce_sale_deactivate(){
        return cg_ecommerce_with_sale_files_lock('cg_ecommerce_sale_deactivate_locked',func_get_args());
    }
}
if(!function_exists('cg_ecommerce_sale_deactivate_locked')){
    function cg_ecommerce_sale_deactivate_locked(){

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
            $ownedFiles = !empty($ecommerceEntry) ? maybe_unserialize($ecommerceEntry->WpUploadFilesForSale) : array();
            cg_ecommerce_assert_file_ownership(is_array($ownedFiles) ? $ownedFiles : array(),$realId);
			if(!empty($ecommerceEntry) && $ecommerceEntry->SaleType === 'upload' && cg_ecommerce_get_gallery_creation_version($GalleryID) >= 33){
				cg_ecommerce_set_legacy_upload_sale_allowed($GalleryID,$realId,true);
			}

            if(empty($ecommerceEntry) || !cg_ecommerce_sale_draft_save($sqlObjectFile,$ecommerceEntry)){
                cg_backend_ajax_error_json('Could not keep the sale settings. Please try again.',500,'cg_sale_draft_failed');
            }

			$isAllWpUploadsSuccessfulMoved = true;

			if($ecommerceEntry->IsDownload){
				$isAllWpUploadsSuccessfulMoved = cg_move_file_from_ecommerce_sale_folder($realId, $GalleryID,$ecommerceEntryId);
			}

            if(!$isAllWpUploadsSuccessfulMoved){
                cg_backend_ajax_error_json('Could not restore the selected files. The sale settings have been kept. Please try again.',500,'cg_sale_restore_failed');
            }

			$saleDeleted = $wpdb->query($wpdb->prepare(
				"
                        DELETE FROM $tablename_ecommerce_entries WHERE id = %d
                    ",
				$sqlObjectFile->EcommerceEntry
			));

            if($saleDeleted === false){cg_backend_ajax_error_json('Could not deactivate the sale. Please try again.',500,'cg_sale_deactivate_failed');}
            cg_ecommerce_clear_legacy_sale_test_mode($ecommerceEntryId);

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
			if($isAllWpUploadsSuccessfulMoved && empty($ecommerceEntry->DownloadKeysCsvName) && empty($ecommerceEntry->ServiceKeysCsvName)){
				$ecommerceEntryFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId;
				cg_remove_folder_recursively($ecommerceEntryFolder);
			}
		}


    }
}
