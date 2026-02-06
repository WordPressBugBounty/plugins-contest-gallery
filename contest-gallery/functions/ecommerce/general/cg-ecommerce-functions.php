<?php

if(!function_exists('cg_increase_number')){
	function cg_increase_number($InvoiceNumberLogicCustomNumberSent,$InvoiceNumber = '') {
		//var_dump('new prccessingInvoiceNumberLogicCustomNumber ');
		// #toDo continue from here with $InvoiceNumberLogicCustomNumber
		$InvoiceNumberLogicCustomNumber = intval($InvoiceNumberLogicCustomNumberSent);
		$InvoiceNumberLogicCustomNumber++;
		//var_dump($InvoiceNumberLogicCustomNumber);
		if(
			strlen($InvoiceNumberLogicCustomNumberSent)>strlen($InvoiceNumberLogicCustomNumber) &&  substr($InvoiceNumberLogicCustomNumberSent,0,1)=='0'
		){
			$zerosToAdd = strlen($InvoiceNumberLogicCustomNumberSent)-strlen($InvoiceNumberLogicCustomNumber);
			//var_dump('$zerosToAdd');
			//var_dump($zerosToAdd);
			for($i=1;$i<=$zerosToAdd;$i++){
				$InvoiceNumberLogicCustomNumber = '0'.$InvoiceNumberLogicCustomNumber;
			}
		}
		$InvoiceNumber .= $InvoiceNumberLogicCustomNumber;
		return [
			'InvoiceNumber' => $InvoiceNumber,
			'InvoiceNumberLogicCustomNumber' => $InvoiceNumberLogicCustomNumber
		];
	}
}

if(!function_exists('cg_get_set_key')){
	function cg_get_set_key($GalleryID,$realId,$DownloadKeysCsvName='',$ServiceKeysCsvName='',$email='',$captureId='',$tstamp='',$OrderNumber=''){
		$wp_upload_dir = wp_upload_dir();

		if($DownloadKeysCsvName){
			$csvPath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId.'/download-keys/'.$DownloadKeysCsvName;
		}elseif($ServiceKeysCsvName){
			$csvPath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId.'/service-keys/'.$ServiceKeysCsvName;
		}
//var_dump('$csvPath');
//var_dump($csvPath);
		$isGotNewKey = false;
		$keyFromCsv = '';
		$newExplode = [];

		//$handle = fopen($csvPath, "w");
		if (($handle = fopen($csvPath, "r+")) !== FALSE) {
			//var_dump('opencsv');
			//var_dump($DownloadKeysCsvName);
			//var_dump($csvPath);
			$fp = file($csvPath);
			//$linesLength = 100;
			$linesLength = 0;// 0 is unlimited

			$i = 0;

			while ($line = fgetcsv($handle,$linesLength,';')){
				$newExplode[$i] = [];
				$newExplode[$i][0] = trim($line[0]);
				if(!empty($line[1])){
					$newExplode[$i][1] = trim($line[1]);
				}else{
					$newExplode[$i][1] = '';
				}
				if(!empty($line[2])){
					$newExplode[$i][2] = trim($line[2]);
				}else{
					$newExplode[$i][2] = '';
				}
				if(!empty($line[3])){
					$newExplode[$i][3] = trim($line[3]);
				}else{
					$newExplode[$i][3] = '';
				}

				if(empty($line[1]) && !$isGotNewKey){
					//var_dump('$line[0]');
					//var_dump($line[0]);
					//var_dump('$line[1]');
					//var_dump($line[1]);
					//var_dump('insert $i');
					//var_dump($i);
					$isGotNewKey = true;
					$keyFromCsv = $line[0];
					if(!empty($tstamp)){
						$purchaseTime = cg_get_time_based_on_wp_timezone_conf($tstamp,'Y-M-d H:i:s');
						$newExplode[$i][1] = trim($purchaseTime);
					}
					if(!empty($email)){
						$newExplode[$i][2] = trim($email);
					}
					if(!empty($OrderNumber)){
						$newExplode[$i][3] = trim($OrderNumber);
					}
				}
				$i++;
			}
		}
		fclose($handle);

        $newExplode = cg_neutralize_csv_array($newExplode);

		chmod($csvPath,0644);
		$fpnew = fopen($csvPath, 'w');
		// no need to use here otherwise puts "zwnbsp" at the beginning of every line 0
		//fputs($fpnew, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		foreach ($newExplode as $fields) {
			fputcsv($fpnew, $fields, ";");
		}
		fclose($fpnew);

		return $keyFromCsv;
	}
}

if(!function_exists('cg_get_countries')){
	function cg_get_countries(){
		$array = [];
		$row = 1;
		if (($handle = fopen(__DIR__."/country_codes_paypal.csv", "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$num = count($data);
				//   echo "<p> $num Felder in Zeile $row: <br /></p>\n";
				for ($c=0; $c < $num; $c++) {
					$explode = explode(';',$data[$c]);
					//$country = str_replace('&#xFEFF;','', $explode[0]);
					if($row==1){
						$country = 'Albania';
					}else{
						$country = ucwords(strtolower($explode[0]));
					}
					$country_code = $explode[1];
					if(strtolower($country)=='usa'){
						$country = 'USA';
					}
					$array[$country_code] = $country;
				}
				$row++;
			}
			fclose($handle);
		}
		/*$arrayNew = [];
		$arrayNew['US'] = 'USA';
		$arrayNew['DE'] = 'Germany';
		$arrayNewMerged = array_merge($arrayNew,$array);*/

		return $array;
	}
}

if(!function_exists('cg_get_country_states_codes')){
	function cg_get_country_states_codes($country){
		$array = [];
		$row = 1;
		if (($handle = fopen(__DIR__."/country_codes_states_".strtolower($country).".csv", "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$num = count($data);
				//   echo "<p> $num Felder in Zeile $row: <br /></p>\n";
				for ($c=0; $c < $num; $c++) {
					$explode = explode(';',$data[$c]);
					//$country = str_replace('&#xFEFF;','', $explode[0]);
					$country = $explode[0];
					$country_code = $explode[1];
					$array[$country_code] = $country;
				}
				$row++;
			}
			fclose($handle);
		}
		return $array;
	}
}

if(!function_exists('cg_get_eu_countries_shortcodes')){
	function cg_get_eu_countries_shortcodes(){
		return ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE'];
	}
}

if(!function_exists('cg_get_ecommerce_currencies_array_formatted_by_short_key')){
	function cg_get_ecommerce_currencies_array_formatted_by_short_key(){
		$newArray = [];
		foreach (cg_get_ecommerce_currencies_array() as $value){
			$newArray[$value['short']] = $value['symbol'];
		}
		return $newArray;
	}
}


if(!function_exists('cg_ecommerce_price_to_show')){
	function cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$floatValue){
//var_dump('$CurrencyShort');
//var_dump($CurrencyShort);
//var_dump('$currenciesArray');
//var_dump($currenciesArray);
		if($CurrencyShort=='%'){
			$currencyChar = '%';
		}else{
			$currencyChar = $currenciesArray[$CurrencyShort];
		}
		//  var_dump('$currencyChar');
		//   var_dump($currencyChar);
		/*    $Price = strval($floatValue);
			if($PriceDivider==','){
				$Price = str_replace('.',',',$floatValue);
			}
			$exploded = explode($PriceDivider,$Price);*/
		/*var_dump('$floatValue');
		var_dump($floatValue);
		var_dump('$exploded 123');
		echo "<pre>";
		print_r($exploded);
		echo "</pre>";*/
		/*     $leftSide = $exploded[0];
			 if(empty($exploded[1])){
				 $rightSide = '00';
			 }else{
				 $rightSide = $exploded[1];
			 }
			 if(strlen($rightSide)==1){
				 $rightSide .= '0';
			 }
			 $Price = $leftSide.$PriceDivider.$rightSide;*/

		if($PriceDivider==','){
			$Price = number_format($floatValue,2,$PriceDivider, '.');
		}else{
			$Price = number_format($floatValue,2,$PriceDivider, ',');
		}

		$priceStringToShow = $currencyChar.$Price;
		if($CurrencyPosition=='right'){
			$priceStringToShow = $Price.$currencyChar;
		}

		return $priceStringToShow;
	}
}

if(!function_exists('cg_get_ecommerce_files_data')){
	function cg_get_ecommerce_files_data($galeryID,$pid = 0){

		global $wpdb;
		$tablenameEcommerceEntries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

		$ecommerceFilesData = [];

		if($pid){
			$ecommerceFilesSQL = $wpdb->get_results( "SELECT * FROM $tablenameEcommerceEntries WHERE pid = $pid");
		}else{
			$ecommerceFilesSQL = $wpdb->get_results( "SELECT * FROM $tablenameEcommerceEntries WHERE GalleryID = $galeryID");
		}

		foreach ($ecommerceFilesSQL as $ecommerceFilesRow){
			$ecommerceFilesData[$ecommerceFilesRow->pid] = json_decode(json_encode($ecommerceFilesRow),true);

			// has to be unserialized because is unserialized otherwise jsond_decode processing error later
			$ecommerceFilesData[$ecommerceFilesRow->pid]['WpUploadFilesPosts'] = !empty($ecommerceFilesData[$ecommerceFilesRow->pid]['WpUploadFilesPosts']) ? unserialize($ecommerceFilesData[$ecommerceFilesRow->pid]['WpUploadFilesPosts']) : '';
			$ecommerceFilesData[$ecommerceFilesRow->pid]['WpUploadFilesPostMeta'] = !empty($ecommerceFilesData[$ecommerceFilesRow->pid]['WpUploadFilesPostMeta']) ? unserialize($ecommerceFilesData[$ecommerceFilesRow->pid]['WpUploadFilesPostMeta']) : '';
			$ecommerceFilesData[$ecommerceFilesRow->pid]['WpUploadFilesForSale'] = !empty($ecommerceFilesData[$ecommerceFilesRow->pid]['WpUploadFilesForSale']) ? unserialize($ecommerceFilesData[$ecommerceFilesRow->pid]['WpUploadFilesForSale']) : '';
			$ecommerceFilesData[$ecommerceFilesRow->pid]['WatermarkSettings'] = !empty($ecommerceFilesData[$ecommerceFilesRow->pid]['WatermarkSettings']) ? unserialize($ecommerceFilesData[$ecommerceFilesRow->pid]['WatermarkSettings']) : '';
			$ecommerceFilesData[$ecommerceFilesRow->pid]['AllUploadsUsedText'] = contest_gal1ery_convert_for_html_output_without_nl2br($ecommerceFilesData[$ecommerceFilesRow->pid]['AllUploadsUsedText']);
		}

		return $ecommerceFilesData;
	}
}

if(!function_exists('cg_get_query_data_and_options')){
	function cg_get_query_data_and_options($wp_upload_dir,$GalleryID){

		global $wpdb;
		$tablename = $wpdb->prefix . "contest_gal1ery";

		$optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-options.json';

		// then gallery must be deleted
		if(!file_exists($optionsFile)){
			return [
				'options' => [],
				'queryDataArray' => [],
			];
		}

		// get Exif or MultipleFiles data in one query
		if(!empty($options['pro']['ShowExif'])){
			// default DEFAULT '' was added later at 21.08.2022 with update 18.0.0 to Exif field
			$queryData = $wpdb->get_results( "SELECT id, Exif, MultipleFiles FROM $tablename WHERE (GalleryID = '$GalleryID' AND Active = '1' AND Exif != '' AND Exif != '0' AND Exif IS NOT NULL) OR (GalleryID = '$GalleryID' AND Active = '1' AND MultipleFiles != '')");
		}else{
			// default DEFAULT '' was added later at 21.08.2022 with update 18.0.0 to Exif field
			$queryData = $wpdb->get_results( "SELECT id, MultipleFiles FROM $tablename WHERE GalleryID = '$GalleryID' AND Active = '1' AND MultipleFiles != ''");
		}

		$queryDataArray = [];
		if(!empty($queryData)){
			foreach ($queryData as $rowObject){
				$queryDataArray[$rowObject->id] = [];
				if(!empty($rowObject->Exif)){$queryDataArray[$rowObject->id]['Exif'] = unserialize($rowObject->Exif);}
				if(!empty($rowObject->MultipleFiles) && $rowObject->MultipleFiles!='""'){$queryDataArray[$rowObject->id]['MultipleFiles'] = unserialize($rowObject->MultipleFiles);}
			}
		}

		$options = json_decode(file_get_contents($optionsFile),true);

		if(!empty($options[$GalleryID.'-ec'])){
			$options = $options[$GalleryID.'-ec'];
		}

		return [
			'options' => $options,
			'queryDataArray' => $queryDataArray,
		];

	}
}

if(!function_exists('cg_get_data_for_order_items')){
	function cg_get_data_for_order_items($uploadedEntries){

		global $wpdb;
		$tablename = $wpdb->prefix . "contest_gal1ery";
		$uploadedEntriesData = [];

		foreach($uploadedEntries as $OrderItemID => $entry){
			foreach ($entry as $entryID){
				// has to be done via database because might be deactivated
				$entry = $wpdb->get_row("SELECT * FROM $tablename WHERE id = '$entryID' LIMIT 1");
				$entryData = json_decode(json_encode($entry),true);
				$entryData['gid'] = $entry->GalleryID;
				$entryData['realGid'] = $entry->GalleryID;

				if(cg_is_is_image($entry->ImgType)){
					$imgSrcThumb=wp_get_attachment_image_src($entry->WpUpload, 'thumbnail');
					$imgSrcThumb= (!empty($imgSrcThumb[0])) ? $imgSrcThumb[0] : '';
					$imgSrcMedium=wp_get_attachment_image_src($entry->WpUpload, 'medium');
					$imgSrcMedium= (!empty($imgSrcMedium[0])) ? $imgSrcMedium[0] : '';
					$imgSrcLarge=wp_get_attachment_image_src($entry->WpUpload, 'large');
					$imgSrcLarge= (!empty($imgSrcLarge[0])) ? $imgSrcLarge[0] : '';

					$entryData['thumbnail'] = $imgSrcThumb;
					$entryData['medium'] = $imgSrcMedium;
					$entryData['large'] = $imgSrcLarge;
				}

				if(!empty($entryData['MultipleFiles']) && $entryData['MultipleFiles']!='""'){
					$entryData['MultipleFiles'] = unserialize($entryData['MultipleFiles']);
				}
				if(!isset($uploadedEntriesData[$OrderItemID])){
					$uploadedEntriesData[$OrderItemID] = [];
				}
				$uploadedEntriesData[$OrderItemID][$entryID] = $entryData;
			}
		}
		return $uploadedEntriesData;
	}
}

if(!function_exists('cg_get_data_for_order')){
	function cg_get_data_for_order($wp_upload_dir,$OrderItem){

		$GalleryID = $OrderItem->GalleryID;
		$RawData = [];

		$optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-options.json';

		// then gallery must be deleted
		if(!file_exists($optionsFile)){
			return [
				'jsonInfoData' => [],
				'RawData' => [],
			];
		}

		$pid = $OrderItem->pid;
		$imageDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-data/*.json');
		foreach ($imageDataJsonFiles as $jsonFile) {
			$imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
			if($imageId==$pid){
				$jsonFileData = json_decode(file_get_contents($jsonFile),true);
				if(empty($jsonFileData['Category'])){// repair here for sure
					$jsonFileData['Category'] = 0;
				}
				$jsonFileData['realGid'] = $OrderItem->GalleryID;
				$RawData[$pid] = $jsonFileData;
				if(!empty($RawData[$pid]['MultipleFiles']) && $RawData[$pid]['MultipleFiles']!='""'){
					$RawData[$pid]['MultipleFiles'] = unserialize($RawData[$pid]['MultipleFiles']);
				}
			}
		}

		$infoDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$OrderItem->GalleryID.'/json/image-info/*.json');
		$jsonInfoData = [];
		foreach ($infoDataJsonFiles as $jsonFile) {
			$imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
			if($imageId==$pid){
				$jsonFileData = json_decode(file_get_contents($jsonFile),true);
				$jsonInfoData[$imageId] = $jsonFileData;
			}
		}
		return [
			'jsonInfoData' => $jsonInfoData,
			'RawData' => $RawData,
		];
	}
}