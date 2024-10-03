<?php
if(!function_exists('cg_ecommerce_sale_activate')){
    function cg_ecommerce_sale_activate(){

		/*
        echo "<pre>";
        print_r($_FILES);
        echo "</pre>";

        echo "<pre>";
        print_r($_POST);
        echo "</pre>";*/

        if(!empty($_FILES['cgSellContainer']) && !empty($_FILES['cgSellContainer']['name']) && (!empty($_FILES['cgSellContainer']['name']['ServiceKey']) || !empty($_FILES['cgSellContainer']['name']['DownloadKey']))){

            if(!empty($_FILES['cgSellContainer']['name']['ServiceKey']['type']) &&  $_FILES['cgSellContainer']['name']['ServiceKey']['type']!='text/csv'){
                echo 'not allowed download key file type';
                die;
            }

            if(!empty($_FILES['cgSellContainer']['name']['DownloadKey']['type']) &&  $_FILES['cgSellContainer']['name']['DownloadKey']['type']!='text/csv'){
                echo 'not allowed download key file type';
                die;
            }

            $_FILES = cg1l_sanitize_post($_FILES);// since 21.0.1 can be also done
            $_FILES = cg1l_sanitize_files($_FILES,'cgSellContainer');
        }

        $wp_upload_dir = wp_upload_dir();

        global $wpdb;
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

        $GalleryID = absint($_POST['cgSellContainer']['GalleryID']);
        $realId = absint($_POST['cgSellContainer']['realId']);
        $SaleType = sanitize_text_field($_POST['cgSellContainer']['SaleType']);
        $removedWpUploadIdsFromSale = (isset($_POST['cgSellContainer']['removedWpUploadIdsFromSale'])) ? $_POST['cgSellContainer']['removedWpUploadIdsFromSale'] : [];

	    $sqlObjectFile = $wpdb->get_row("SELECT * FROM $tablename WHERE id = '$realId'");
        $EcommerceEntry = intval($sqlObjectFile->EcommerceEntry);

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
            } else if($sqlObjectFileEcommerceEntry->IsDownload && $SaleType=='download' && !empty($removedWpUploadIdsFromSale)){
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

        $htaccessFileContent = <<<HEREDOC
#Do not remove htaccess in this folder. It prevents for getting files which are selected for sale from getting from outside.
<Files "*">
   order deny,allow
   deny from all
</Files>
HEREDOC;

        $DownloadKeysCsvName = ($sqlObjectFileEcommerceEntry->DownloadKeysCsvName) ?? '';
        if(!empty($_POST['cgSellContainer']['RemoveDownloadKeysFile']) && $_POST['cgSellContainer']['RemoveDownloadKeysFile']==1){
            $DownloadKeysCsvName = $wpdb->get_var("SELECT DownloadKeysCsvName FROM $tablename_ecommerce_entries WHERE id = '$EcommerceEntry'");
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$sqlObjectFile->id.'/download-keys/';
            unlink($ecommerceFileFolder.$DownloadKeysCsvName);
            $DownloadKeysCsvName = '';
        }
        if(!empty($_FILES['cgSellContainer']['name']) && !empty($_FILES['cgSellContainer']['name']['DownloadKey'])){
            $DownloadKeysCsvName = sanitize_text_field($_FILES['cgSellContainer']['name']['DownloadKey']);
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$sqlObjectFile->id.'/download-keys';
            if(!is_dir($ecommerceFileFolder)){
                mkdir($ecommerceFileFolder,0755,true);
                $htaccessFile = $ecommerceFileFolder.'/.htaccess';
                file_put_contents($htaccessFile,$htaccessFileContent);
                chmod($htaccessFile, 0640);// no read for others!!!
                file_put_contents($ecommerceFileFolder.'/do-not-remove-htaccess.txt','Do not remove htaccess in this folder. It prevents for getting files which are selected for sale from getting from outside.');
            }
            $downloadKeyFile = $ecommerceFileFolder.'/'.$DownloadKeysCsvName;
            file_put_contents($downloadKeyFile,file_get_contents($_FILES['cgSellContainer']['tmp_name']['DownloadKey']));
        }

        $ServiceKeysCsvName = ($sqlObjectFileEcommerceEntry->ServiceKeysCsvName) ?? '';
        if(!empty($_POST['cgSellContainer']['RemoveServiceKeysFile']) && $_POST['cgSellContainer']['RemoveServiceKeysFile']==1){
            $ServiceKeysCsvName = $wpdb->get_var("SELECT ServiceKeysCsvName FROM $tablename_ecommerce_entries WHERE id = '$EcommerceEntry'");
            //var_dump('$ServiceKeysCsvName');
            //var_dump($ServiceKeysCsvName);
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$sqlObjectFile->id.'/service-keys/';
            //var_dump('$ecommerceFileFolder $ServiceKeysCsvName remove');
            //var_dump($ecommerceFileFolder);
            unlink($ecommerceFileFolder.$ServiceKeysCsvName);
            $ServiceKeysCsvName = '';
        }
        if(!empty($_FILES['cgSellContainer']['name']) && !empty($_FILES['cgSellContainer']['name']['ServiceKey'])){
            $ServiceKeysCsvName = sanitize_text_field($_FILES['cgSellContainer']['name']['ServiceKey']);
            $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$sqlObjectFile->id.'/service-keys';
            if(!is_dir($ecommerceFileFolder)){
                mkdir($ecommerceFileFolder,0755,true);
                $htaccessFile = $ecommerceFileFolder.'/.htaccess';
                file_put_contents($htaccessFile,$htaccessFileContent);
                chmod($htaccessFile, 0640);// no read for others!!!
                file_put_contents($ecommerceFileFolder.'/do-not-remove-htaccess.txt','Do not remove htaccess in this folder. It prevents for getting files which are selected for sale from getting from outside.');
            }
            $serviceKeyFile = $ecommerceFileFolder.'/'.$ServiceKeysCsvName;
            file_put_contents($serviceKeyFile,file_get_contents($_FILES['cgSellContainer']['tmp_name']['ServiceKey']));
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
        if($SaleType=='upload'){ $IsUpload = true;  }

		$UploadGallery  = 0;
	    $MaxUploads  = '';
        if($IsUpload){
			$UploadGallery = intval($_POST['cgSellContainer']['UploadGallery']);
			$MaxUploads = $_POST['cgSellContainer']['MaxUploads'];
        }

	    $AllUploadsUsedText = (!empty(trim($_POST['cgSellContainer']['AllUploadsUsedText']))) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['cgSellContainer']['AllUploadsUsedText']) : '';

        if($IsDownload){
            cg_move_file_ecommerce_sale_folder($realId, $GalleryID,$sqlObjectFile,$sqlObjectFileEcommerceEntry);
        }
        // update of set values can be done even if activated or deactivated
        $Price = round(floatval($_POST['cgSellContainer']['PriceResult']),2);
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
        $wpdb->update(
            "$tablename_ecommerce_entries",
            array(
                'Price' => $Price,
                'WpUploadFilesForSale' => $WpUploadFilesForSale, 'WatermarkSettings' => $WatermarkSettings,
                'SaleTitle' => $SaleTitle,'SaleDescription' => $SaleDescription,'SaleType' => $SaleType,
                'SaleAmountMax' => $SaleAmountMax,'SaleAmountMin' => $SaleAmountMin,'TaxPercentage' => $TaxPercentage,'AlternativeShipping' => $AlternativeShipping,
                'IsShipping' => $IsShipping,'IsDownload' => $IsDownload,'IsService' => $IsService,'IsUpload' => $IsUpload,'IsAlternativeShipping' => $IsAlternativeShipping,'HasTax' => $HasTax,
                'UploadGallery' => $UploadGallery, 'MaxUploads' => $MaxUploads,
                'DownloadKeysCsvName' => $DownloadKeysCsvName, 'ServiceKeysCsvName' => $ServiceKeysCsvName, 'AllUploadsUsedText' => $AllUploadsUsedText
            ),
            array('id' => $sqlObjectFile->EcommerceEntry),
            array(
                '%f',
                '%s','%s',
                '%s','%s','%s',
                '%d','%d','%f','%f',
                '%d','%d','%d','%d','%d','%d',
                '%d','%s',
                '%s','%s','%s'
            ),
            array('%d')
        );

        $wp_upload_dir = wp_upload_dir();

        $imageJsonPath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-data/image-data-'.$sqlObjectFile->id.'.json';
        if(file_exists($imageJsonPath)){
            $imageJson = json_decode(file_get_contents($imageJsonPath),true);
            if(!empty($imageJson)){
                $imageJson['EcommerceEntry'] = $sqlObjectFile->id;
                file_put_contents($imageJsonPath,json_encode($imageJson));
            }
        }

   }
}

