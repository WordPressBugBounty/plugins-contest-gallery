<?php
if(!function_exists('cg_ecommerce_sale_activate')){
    function cg_ecommerce_sale_activate(){
        return cg_ecommerce_with_sale_files_lock('cg_ecommerce_sale_activate_locked',func_get_args());
    }
}
if(!function_exists('cg_ecommerce_sale_activate_locked')){
    function cg_ecommerce_sale_activate_locked(){

        $IsFree = !empty($_POST['cgSellContainer']['IsFree']) && $_POST['cgSellContainer']['IsFree'] === '1' ? 1 : 0;
        $isReduced = !empty($_POST['cgSellContainer']['HasReducedPrice']) && $_POST['cgSellContainer']['HasReducedPrice'] === '1';
        $reducedPriceInput = isset($_POST['cgSellContainer']['ReducedPrice']) ? $_POST['cgSellContainer']['ReducedPrice'] : '';
        // Empty or zero means no reduction, even with an enabled switch.
        if(is_scalar($reducedPriceInput) && preg_match('/^(?:0+(?:[.,]0{1,2})?)?$/D',trim((string)$reducedPriceInput))){
            $isReduced = false;
        }
        $RegularPrice = 0;
        $Price = cg_ecommerce_parse_sale_price(isset($_POST['cgSellContainer']['PriceResult']) ? $_POST['cgSellContainer']['PriceResult'] : '', $IsFree);
        if($isReduced){
            $RegularPrice = $Price;
            $Price = cg_ecommerce_parse_sale_price($reducedPriceInput,false);
            if($IsFree || $RegularPrice === false || $Price === false || $Price >= $RegularPrice){
                cg_backend_ajax_error_json('Enter a reduced price greater than 0 and lower than the regular price.',400,'cg_sale_reduced_price_invalid');
            }
        }
        if($Price === false){
            cg_backend_ajax_error_json('Enter a price greater than 0 or choose Offer for free.',400,'cg_sale_price_invalid');
        }
        $keyUploads = cg_ecommerce_validate_key_uploads($_FILES);

        $wp_upload_dir = wp_upload_dir();

        global $wpdb;
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

        $GalleryID = absint($_POST['cgSellContainer']['GalleryID']);
        $realId = absint($_POST['cgSellContainer']['realId']);
        $SaleType = sanitize_text_field($_POST['cgSellContainer']['SaleType']);
        if($isReduced && $SaleType === 'upload'){cg_backend_ajax_error_json('Reduced prices are available for downloads, shipping and services.',400,'cg_sale_type_unavailable');}
        if($IsFree && $SaleType === 'upload'){cg_backend_ajax_error_json('Free offers are available for downloads, shipping and services.',400,'cg_sale_type_unavailable');}
        $removedWpUploadIdsFromSale = (isset($_POST['cgSellContainer']['removedWpUploadIdsFromSale'])) ? $_POST['cgSellContainer']['removedWpUploadIdsFromSale'] : [];

	    $sqlObjectFile = $wpdb->get_row("SELECT * FROM $tablename WHERE id = '$realId'");
        $EcommerceEntry = intval($sqlObjectFile->EcommerceEntry);
        $saleDraft = !$EcommerceEntry ? cg_ecommerce_sale_draft_get($sqlObjectFile) : array();
        $ownedFiles = $EcommerceEntry ? maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT WpUploadFilesForSale FROM $tablename_ecommerce_entries WHERE id=%d",$EcommerceEntry))) : array();
        $ownedFiles = is_array($ownedFiles) ? array_map('absint',$ownedFiles) : array();
        // Validate before restore, insert, metadata changes or moving any original.
        cg_ecommerce_assert_file_ownership($ownedFiles,$realId);
        if(!is_array($removedWpUploadIdsFromSale) || array_diff(array_map('absint',$removedWpUploadIdsFromSale),$ownedFiles)){
            cg_backend_ajax_error_json('Select files belonging to this sale.',400,'cg_sale_files_invalid');
        }
        if($SaleType === 'download'){
            $selectedFiles = isset($_POST['cgSellContainer']['WpUploadFilesForSale']) ? $_POST['cgSellContainer']['WpUploadFilesForSale'] : array();
            $allowedFiles = cg_ecommerce_sale_entry_upload_ids($sqlObjectFile);
            if(!is_array($selectedFiles) || !is_array($removedWpUploadIdsFromSale) || array_diff(array_map('absint',$selectedFiles),$allowedFiles)){
                cg_backend_ajax_error_json('Select files belonging to this entry.',400,'cg_sale_files_invalid');
            }
            $previewFiles = isset($_POST['cgSellContainer']['base64WatermarkedAndAltFiles']) ? $_POST['cgSellContainer']['base64WatermarkedAndAltFiles'] : array();
            if(!is_array($previewFiles) || array_diff(array_map('absint',array_keys($previewFiles)),$allowedFiles)){
                cg_backend_ajax_error_json('Select files belonging to this entry.',400,'cg_sale_files_invalid');
            }
            cg_ecommerce_assert_file_ownership(array_merge($ownedFiles,array_map('absint',$selectedFiles),array_keys($previewFiles)),$realId);
            $existingFiles = $EcommerceEntry ? maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT WpUploadFilesForSale FROM $tablename_ecommerce_entries WHERE id = %d",$EcommerceEntry))) : array();
            $remainingFiles = array_diff(array_merge(is_array($existingFiles) ? $existingFiles : array(),array_map('absint',$selectedFiles)),array_map('absint',$removedWpUploadIdsFromSale));
            if(!array_intersect($allowedFiles,$remainingFiles)){
                cg_backend_ajax_error_json('Select at least one download file.',400,'cg_sale_files_missing');
            }
            if(!$EcommerceEntry){
                foreach($selectedFiles as $uploadId){
                    $path = get_attached_file(absint($uploadId));
                    if(!$path || !is_file($path) || !is_readable($path)){
                        cg_backend_ajax_error_json('A selected download file is no longer available.',400,'cg_sale_file_missing');
                    }
                }
            }
        }
	    $existingSaleType = '';
	    if(!empty($EcommerceEntry)){
		    $existingSaleType = (string)$wpdb->get_var($wpdb->prepare("SELECT SaleType FROM $tablename_ecommerce_entries WHERE id = %d",$EcommerceEntry));
	    }

        if(!in_array($SaleType,array('download','shipping','service','upload'),true) || ($SaleType === 'upload' && !cg_ecommerce_products_pro_available())){
            cg_backend_ajax_error_json('This sale type is not available.',400,'cg_sale_type_unavailable');
        }

	    $galleryVersion = cg_ecommerce_get_gallery_creation_version($GalleryID);
	    $isLegacyUploadSaleAllowed = cg_ecommerce_legacy_upload_sale_is_allowed($GalleryID,$realId);
	    if($SaleType === 'upload'){
		    if($galleryVersion >= 33 && $existingSaleType !== 'upload' && !$isLegacyUploadSaleAllowed){
			    if(function_exists('cg_backend_ajax_error_json')){
				    cg_backend_ajax_error_json('Classic upload packages can no longer be created for this gallery. Use Pay for uploads instead.', 400, 'cg_legacy_upload_sale_unavailable');
			    }
			    status_header(400);
			    echo 'LEGACYUPLOADSALEUNAVAILABLE';
			    die;
		    }
	    }

		//var_dump('$EcommerceEntry');
		//var_dump($EcommerceEntry);

	    if(!empty($EcommerceEntry)){
            $sqlObjectFileEcommerceEntry = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_entries WHERE id = '$EcommerceEntry'");

			// if previously was download and now not download anymore
            if($sqlObjectFileEcommerceEntry->IsDownload && $SaleType!='download'){

                /*var_dump('$realId');
                var_dump($realId);

                var_dump('$GalleryID');
                var_dump($GalleryID);

                var_dump('$EcommerceEntry');
                var_dump($EcommerceEntry);*/

                cg_move_file_from_ecommerce_sale_folder($realId, $GalleryID,$EcommerceEntry);
            } elseif($sqlObjectFileEcommerceEntry->IsDownload && $SaleType=='download' && !empty($removedWpUploadIdsFromSale)){
				//var_dump('move here!!!');
		        cg_move_file_from_ecommerce_sale_folder($realId, $GalleryID, $EcommerceEntry, $removedWpUploadIdsFromSale);
            }
        }else{

            $wpdb->query( $wpdb->prepare(
                "
                              INSERT INTO $tablename_ecommerce_entries
                              ( id, pid, GalleryID)
                              VALUES ( %d,%d,%d )
                           ",
                '',$sqlObjectFile->id,$sqlObjectFile->GalleryID
            ) );

            $EcommerceEntry = $wpdb->insert_id;
            if(!$EcommerceEntry){
                cg_backend_ajax_error_json('Could not save sale settings. Please try again.',500,'cg_sale_save_failed');
            }
            $sqlObjectFile->EcommerceEntry = $EcommerceEntry;

            $wpdb->update(
                "$tablename",
                array( 'EcommerceEntry' => $sqlObjectFile->EcommerceEntry),
                array('id' => $sqlObjectFile->id),
                array('%d'),
                array('%d')
            );
            $sqlObjectFileEcommerceEntry = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_entries WHERE id = '$EcommerceEntry'");

        }

        if(!empty($saleDraft)){
            foreach(array('DownloadKeysCsvName','ServiceKeysCsvName') as $keyField){
                $sqlObjectFileEcommerceEntry->$keyField = isset($saleDraft[$keyField]) ? $saleDraft[$keyField] : '';
            }
        }
        $DownloadKeysCsvName = isset($sqlObjectFileEcommerceEntry->DownloadKeysCsvName) ? $sqlObjectFileEcommerceEntry->DownloadKeysCsvName : '';
        if(!empty($_POST['cgSellContainer']['RemoveDownloadKeysFile']) && $_POST['cgSellContainer']['RemoveDownloadKeysFile']==1){

            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$sqlObjectFile->id.'/download-keys/';
            if($DownloadKeysCsvName && is_file($ecommerceFileFolder.$DownloadKeysCsvName)){unlink($ecommerceFileFolder.$DownloadKeysCsvName);}
            $DownloadKeysCsvName = '';
        }
        if(isset($keyUploads['DownloadKey'])){
            $DownloadKeysCsvName = cg_ecommerce_store_key_upload($keyUploads['DownloadKey'],$GalleryID,$sqlObjectFile->id,'DownloadKey');
        }

        $ServiceKeysCsvName = isset($sqlObjectFileEcommerceEntry->ServiceKeysCsvName) ? $sqlObjectFileEcommerceEntry->ServiceKeysCsvName : '';
        if(!empty($_POST['cgSellContainer']['RemoveServiceKeysFile']) && $_POST['cgSellContainer']['RemoveServiceKeysFile']==1){

            //var_dump('$ServiceKeysCsvName');
            //var_dump($ServiceKeysCsvName);
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$sqlObjectFile->id.'/service-keys/';
            //var_dump('$ecommerceFileFolder $ServiceKeysCsvName remove');
            //var_dump($ecommerceFileFolder);
            if($ServiceKeysCsvName && is_file($ecommerceFileFolder.$ServiceKeysCsvName)){unlink($ecommerceFileFolder.$ServiceKeysCsvName);}
            $ServiceKeysCsvName = '';
        }
        if(isset($keyUploads['ServiceKey'])){
            $ServiceKeysCsvName = cg_ecommerce_store_key_upload($keyUploads['ServiceKey'],$GalleryID,$sqlObjectFile->id,'ServiceKey');
        }

        /*var_dump('print cgSellContainer');
        echo "<pre>";
        print_r($_POST['cgSellContainer']);
        echo "</pre>";*/

        $sqlObjectFile = $wpdb->get_row("SELECT * FROM $tablename WHERE id = '$realId'");

        $IsShipping = false;
        $IsDownload = false;
        $IsService = false;
        $IsUpload = false;
        if($SaleType=='shipping'){ $IsShipping = true;  }
        if($SaleType=='download'){ $IsDownload = true;  }
        if($SaleType=='service'){ $IsService = true;  }
        if($SaleType=='upload'){ $IsUpload = true;  }

		if($IsDownload && !empty($_POST['cgSellContainer']['WpUploadFilesForSale'])){
			foreach($_POST['cgSellContainer']['WpUploadFilesForSale'] as $cgWpUploadForSale){
				$cgWpUploadForSale = absint($cgWpUploadForSale);
				if(!empty($cgWpUploadForSale) && function_exists('cg_entry_watermark_meta_key') && get_post_meta($cgWpUploadForSale, cg_entry_watermark_meta_key(), true)){
					if(function_exists('cg_backend_ajax_error_json')){
						cg_backend_ajax_error_json('Unwatermark first to watermark for selling.', 400, 'cg_entry_watermark_ecommerce_conflict');
					}
					echo 'Unwatermark first to watermark for selling.';
					die;
				}
			}
		}

		$UploadGallery  = 0;
	    $MaxUploads  = '';
        if($IsUpload){
			$UploadGallery = intval($_POST['cgSellContainer']['UploadGallery']);
			$MaxUploads = $_POST['cgSellContainer']['MaxUploads'];
        }

	    $AllUploadsUsedText = (isset($_POST['cgSellContainer']['AllUploadsUsedText']) && is_string($_POST['cgSellContainer']['AllUploadsUsedText'])) ? $_POST['cgSellContainer']['AllUploadsUsedText'] : '';
	    $AllUploadsUsedText = (!empty(trim($AllUploadsUsedText))) ? contest_gal1ery_htmlentities_and_preg_replace($AllUploadsUsedText) : '';

	    if($IsDownload){
            cg_move_file_ecommerce_sale_folder($realId, $GalleryID,$sqlObjectFile,$sqlObjectFileEcommerceEntry);
        }
        // update of set values can be done even if activated or deactivated
        // Price and free consent were validated before any writes.
        $SaleDescription = sanitize_text_field($_POST['cgSellContainer']['SaleDescription']);
        $SaleTitle = sanitize_text_field($_POST['cgSellContainer']['SaleTitle']);
	    $WatermarkSettings = (isset($_POST['cgSellContainer']['WatermarkSettings'])) ? serialize($_POST['cgSellContainer']['WatermarkSettings']) : '';
		if($SaleType!='download'){
			$WatermarkSettings = '';
		}

		if(empty($_POST['cgSellContainer']['WpUploadFilesForSale'])){
			$_POST['cgSellContainer']['WpUploadFilesForSale'] = [];
		}

		if($SaleType!='download'){
			// reset then
			$sqlObjectFileEcommerceEntry->WpUploadFilesForSale = '';
			$WpUploadFilesForSale = '';
		}else{

			if(empty($sqlObjectFileEcommerceEntry->WpUploadFilesForSale)){$sqlObjectFileEcommerceEntry->WpUploadFilesForSale=[];}else{
				$sqlObjectFileEcommerceEntry->WpUploadFilesForSale = unserialize($sqlObjectFileEcommerceEntry->WpUploadFilesForSale);
			}

			$WpUploadFilesForSaleArray = array_merge($sqlObjectFileEcommerceEntry->WpUploadFilesForSale,$_POST['cgSellContainer']['WpUploadFilesForSale']);
			//var_dump('$WpUploadFilesForSaleArray merged');
			//var_dump($WpUploadFilesForSaleArray);
			if(!empty($removedWpUploadIdsFromSale)){
				foreach ($removedWpUploadIdsFromSale as $removedWpUpload){
					if (($keyToRemove = array_search($removedWpUpload, $WpUploadFilesForSaleArray)) !== false) {
						//var_dump('unset  $keyToRemove');
						//var_dump($keyToRemove);
						unset($WpUploadFilesForSaleArray[$keyToRemove]);
					}
				}
			}
			foreach ($WpUploadFilesForSaleArray as $key => $value){
				// might be empty values after merge above
				if(empty($value)){
					//var_dump('unset  if no value');
					//var_dump($key);
					unset($WpUploadFilesForSaleArray[$key]);
				}
			}
			$WpUploadFilesForSaleArray = array_unique($WpUploadFilesForSaleArray);
			$WpUploadFilesForSale = serialize($WpUploadFilesForSaleArray);

			//var_dump('$WpUploadFilesForSale serialized');
			//var_dump($WpUploadFilesForSale);
		}


        $SaleAmountMax = (isset($_POST['cgSellContainer']['SaleAmountMax'])) ? absint($_POST['cgSellContainer']['SaleAmountMax']) : 0;
        $SaleAmountMin = (isset($_POST['cgSellContainer']['SaleAmountMin'])) ? absint($_POST['cgSellContainer']['SaleAmountMin']) : 0;
        if($IsDownload || $IsService || $IsUpload){
            $SaleAmountMax = 0;
            $SaleAmountMin = 0;
        }

	    $IsAlternativeShipping = 0;
		if($IsShipping){
			$IsAlternativeShipping = (isset($_POST['cgSellContainer']['IsAlternativeShipping'])) ? 1 : 0;
		}
        $TaxPercentage = round(floatval($_POST['cgSellContainer']['Tax']),2);
        $AlternativeShipping = 0;
        if($IsAlternativeShipping==1){
            $AlternativeShipping = round(floatval($_POST['cgSellContainer']['Shipping']),2);
        }
        $HasTax = ($_POST['cgSellContainer']['HasTax']=='1') ? 1 : 0;
        if(!$HasTax){
	        $TaxPercentage = 0;
        }

	    //var_dump('$DownloadKeysCsvName');
	    //var_dump($DownloadKeysCsvName);

	    //var_dump('$Price');
	    //var_dump($Price);

        // getting data via query in frontend
        $saleSaved = $wpdb->update(
            "$tablename_ecommerce_entries",
            array(
                'Price' => $Price, 'RegularPrice' => $RegularPrice, 'IsFree' => $IsFree,
                'WpUploadFilesForSale' => $WpUploadFilesForSale, 'WatermarkSettings' => $WatermarkSettings,
                'SaleTitle' => $SaleTitle,'SaleDescription' => $SaleDescription,'SaleType' => $SaleType,
                'SaleAmountMax' => $SaleAmountMax,'SaleAmountMin' => $SaleAmountMin,'TaxPercentage' => $TaxPercentage,'AlternativeShipping' => $AlternativeShipping,
                'IsShipping' => $IsShipping,'IsDownload' => $IsDownload,'IsService' => $IsService,'IsUpload' => $IsUpload,'IsAlternativeShipping' => $IsAlternativeShipping,'HasTax' => $HasTax,
                'UploadGallery' => $UploadGallery, 'MaxUploads' => $MaxUploads,
                'DownloadKeysCsvName' => $DownloadKeysCsvName, 'ServiceKeysCsvName' => $ServiceKeysCsvName, 'AllUploadsUsedText' => $AllUploadsUsedText
            ),
            array('id' => $sqlObjectFile->EcommerceEntry),
            array(
                '%f','%f','%d',
                '%s','%s',
                '%s','%s','%s',
                '%d','%d','%f','%f',
                '%d','%d','%d','%d','%d','%d',
                '%d','%s',
                '%s','%s','%s'
            ),
            array('%d')
        );

        if($saleSaved === false){
            cg_backend_ajax_error_json('Could not save sale settings. Please try again.',500,'cg_sale_save_failed');
        }

        cg_ecommerce_clear_legacy_sale_test_mode($EcommerceEntry);

	    if($galleryVersion >= 33){
		    if($SaleType === 'upload'){
			    cg_ecommerce_set_legacy_upload_sale_allowed($GalleryID,$realId,true);
		    }elseif($existingSaleType === 'upload' || $isLegacyUploadSaleAllowed){
			    cg_ecommerce_set_legacy_upload_sale_allowed($GalleryID,$realId,false);
		    }
	    }

        $wp_upload_dir = wp_upload_dir();

        $imageJsonPath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-data/image-data-'.$sqlObjectFile->id.'.json';
        if(file_exists($imageJsonPath)){
            $imageJson = json_decode(file_get_contents($imageJsonPath),true);
            if(!empty($imageJson)){
                $imageJson['EcommerceEntry'] = intval($sqlObjectFile->EcommerceEntry);
                file_put_contents($imageJsonPath,json_encode($imageJson));
            }
        }

        cg1l_push_recent_id_file($GalleryID,$sqlObjectFile->id,'image-main-data-last-update');
        cg1l_push_recent_id_file($GalleryID,$sqlObjectFile->id,'image-query-data-last-update');
        cg1l_push_recent_id_file($GalleryID,$sqlObjectFile->id,'image-urls-data-last-update');
        cg1l_create_last_updated_time_file_image_data($GalleryID);
        cg_ecommerce_sale_draft_delete($GalleryID,$realId);

   }
}
