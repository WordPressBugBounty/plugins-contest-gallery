<?php

$_POST = cg1l_sanitize_post($_POST);

global $wpdb;
$tablenameEcommerceEntries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

$wp_upload_dir = wp_upload_dir();
$viewerUserId = (is_user_logged_in()) ? get_current_user_id() : 0;
$allowedShortcodes = [
	'cg_gallery' => true,
	'cg_gallery_user' => true,
	'cg_gallery_no_voting' => true,
	'cg_gallery_winner' => true,
	'cg_gallery_ecommerce' => true
];

$RawDataEachGallery = [];
$InfoDataEachGallery = [];
$OptionsDataEachGallery = [];
$EcommerceFilesDataEachGallery = [];
$requestedEntriesByGallery = [];

if(!empty($_POST['requestedEntriesByGallery']) && is_array($_POST['requestedEntriesByGallery'])){
	foreach($_POST['requestedEntriesByGallery'] as $GalleryID => $requestedEntries){

		$GalleryID = absint($GalleryID);
		if(empty($GalleryID) || !is_array($requestedEntries)){
			continue;
		}

		foreach($requestedEntries as $requestedEntry){
			if(!is_array($requestedEntry)){
				continue;
			}

			$realId = (!empty($requestedEntry['realId'])) ? absint($requestedEntry['realId']) : 0;
			$realGid = (!empty($requestedEntry['realGid'])) ? absint($requestedEntry['realGid']) : 0;
			$sourceShortcodeName = (!empty($requestedEntry['sourceShortcodeName'])) ? sanitize_text_field($requestedEntry['sourceShortcodeName']) : '';
			$rawDataAccessHash = (!empty($requestedEntry['rawDataAccessHash'])) ? sanitize_text_field($requestedEntry['rawDataAccessHash']) : '';

			if(
				empty($realId) ||
				empty($realGid) ||
				$realGid !== $GalleryID ||
				empty($allowedShortcodes[$sourceShortcodeName]) ||
				empty($rawDataAccessHash)
			){
				continue;
			}

			$expectedRawDataAccessHash = cg1l_get_ecommerce_raw_data_access_hash($realGid,$realId,$sourceShortcodeName,$viewerUserId);
			if(!hash_equals($expectedRawDataAccessHash,$rawDataAccessHash)){
				continue;
			}

			if(empty($requestedEntriesByGallery[$GalleryID])){
				$requestedEntriesByGallery[$GalleryID] = [];
			}

			$requestedEntriesByGallery[$GalleryID][$realId] = [
				'realId' => $realId,
				'sourceShortcodeName' => $sourceShortcodeName,
				'rawDataAccessHash' => $rawDataAccessHash
			];
		}
	}
}

foreach($requestedEntriesByGallery as $GalleryID => $authorizedEntries){

		$RawDataEachGallery[$GalleryID] = [];
		$InfoDataEachGallery[$GalleryID] = [];
		$OptionsDataEachGallery[$GalleryID] = [];
		$EcommerceFilesDataEachGallery[$GalleryID] = [];

		$authorizedRealIds = array_map('absint',array_keys($authorizedEntries));
		$authorizedRealIdsLookup = array_fill_keys($authorizedRealIds,true);

		$imageDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-data/*.json');
		foreach ($imageDataJsonFiles as $jsonFile) {
			$stringArray= explode('/image-data-',$jsonFile);
			$imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
			if(empty($authorizedRealIdsLookup[$imageId])){
				continue;
			}
			$jsonFileData = json_decode(file_get_contents($jsonFile),true);
			if(empty($jsonFileData['Category'])){// repair here for sure
				$jsonFileData['Category'] = 0;
			}
			$RawDataEachGallery[$GalleryID][$imageId] = $jsonFileData;
		}

		$infoDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-info/*.json');
		foreach ($infoDataJsonFiles as $jsonFile) {
			$stringArray= explode('/image-info-',$jsonFile);
			$imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
			if(empty($authorizedRealIdsLookup[$imageId])){
				continue;
			}
			$jsonFileData = json_decode(file_get_contents($jsonFile),true);
			$InfoDataEachGallery[$GalleryID][$imageId] = $jsonFileData;
		}

		$optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-options.json';
		if(file_exists($optionsFile)){
			$OptionsDataEachGallery[$GalleryID] = json_decode(file_get_contents($optionsFile),true);
		}

		$collectedIdsArray = [];
		foreach ($authorizedRealIds as $realId){
			$collectedIdsArray[] = 'pid = '.absint($realId);
		}

		$ecommerceFilesSQL = [];
		if(!empty($collectedIdsArray)){
			$ecommerceFilesSQL = $wpdb->get_results( "SELECT * FROM $tablenameEcommerceEntries WHERE GalleryID = $GalleryID AND (".implode(' OR ',$collectedIdsArray).") " );
		}

		$EcommerceFilesDataEachGallery[$GalleryID] = [];

		foreach($ecommerceFilesSQL as $ecommerceFileSQL){
			$pid = absint($ecommerceFileSQL->pid);
			if(empty($authorizedEntries[$pid])){
				continue;
			}

			$EcommerceFilesDataEachGallery[$GalleryID][$pid] = json_decode(json_encode($ecommerceFileSQL),true);
			// has to be unserialized because is unserialized otherwise jsond_decode processing error later
			$EcommerceFilesDataEachGallery[$GalleryID][$pid]['WpUploadFilesPosts'] = !empty($EcommerceFilesDataEachGallery[$GalleryID][$pid]['WpUploadFilesPosts']) ? unserialize($EcommerceFilesDataEachGallery[$GalleryID][$pid]['WpUploadFilesPosts']) : '';
			$EcommerceFilesDataEachGallery[$GalleryID][$pid]['WpUploadFilesPostMeta'] = !empty($EcommerceFilesDataEachGallery[$GalleryID][$pid]['WpUploadFilesPostMeta']) ? unserialize($EcommerceFilesDataEachGallery[$GalleryID][$pid]['WpUploadFilesPostMeta']) : '';
			$EcommerceFilesDataEachGallery[$GalleryID][$pid]['WpUploadFilesForSale'] = !empty($EcommerceFilesDataEachGallery[$GalleryID][$pid]['WpUploadFilesForSale']) ? unserialize($EcommerceFilesDataEachGallery[$GalleryID][$pid]['WpUploadFilesForSale']) : '';
			$EcommerceFilesDataEachGallery[$GalleryID][$pid]['WatermarkSettings'] = !empty($EcommerceFilesDataEachGallery[$GalleryID][$pid]['WatermarkSettings']) ? unserialize($EcommerceFilesDataEachGallery[$GalleryID][$pid]['WatermarkSettings']) : '';
			$EcommerceFilesDataEachGallery[$GalleryID][$pid]['AllUploadsUsedText'] = contest_gal1ery_convert_for_html_output_without_nl2br($EcommerceFilesDataEachGallery[$GalleryID][$pid]['AllUploadsUsedText']);
			$EcommerceFilesDataEachGallery[$GalleryID][$pid]['SourceShortcodeName'] = $authorizedEntries[$pid]['sourceShortcodeName'];
			$EcommerceFilesDataEachGallery[$GalleryID][$pid]['RawDataAccessHash'] = $authorizedEntries[$pid]['rawDataAccessHash'];
		}

}

?>
<script data-cg-processing="true">
    cgJsClass.gallery.vars.ecommerce.RawDataEachGallery = <?php echo json_encode($RawDataEachGallery);?>;
    cgJsClass.gallery.vars.ecommerce.InfoDataEachGallery = <?php echo json_encode($InfoDataEachGallery);?>;
    cgJsClass.gallery.vars.ecommerce.OptionsDataEachGallery = <?php echo json_encode($OptionsDataEachGallery);?>;
    cgJsClass.gallery.vars.ecommerce.EcommerceFilesDataEachGallery = <?php echo json_encode($EcommerceFilesDataEachGallery);?>;
</script>
<?php
return;
