<?php

$_POST = cg1l_sanitize_post($_POST);

global $wpdb;
$tablenameEcommerceEntries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
$tablenameEcommerceOptions = $wpdb->prefix . "contest_gal1ery_ecommerce_options";

$wp_upload_dir = wp_upload_dir();

$RawDataEachGallery = [];
$InfoDataEachGallery = [];
$OptionsDataEachGallery = [];
$EcommerceFilesDataEachGallery = [];

if(isset($_POST['realGidsAndIdsDeletedOrFromOtherGalleries'])){
	foreach($_POST['realGidsAndIdsDeletedOrFromOtherGalleries'] as $GalleryID => $realIds){

		$GalleryID = absint($GalleryID);

		$RawDataEachGallery[$GalleryID] = [];
		$InfoDataEachGallery[$GalleryID] = [];
		$OptionsDataEachGallery[$GalleryID] = [];
		$EcommerceFilesDataEachGallery[$GalleryID] = [];

		$imageDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-data/*.json');
		$jsonImagesData = [];
		foreach ($imageDataJsonFiles as $jsonFile) {
			$stringArray= explode('/image-data-',$jsonFile);
			$imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
			// can only happen if database was cleared but json files were not deleted and old files from old "installation" are still there
            if(in_array($imageId,$realIds)){
	            $jsonFileData = json_decode(file_get_contents($jsonFile),true);
	            if(empty($jsonFileData['Category'])){// repair here for sure
		            $jsonFileData['Category'] = 0;
	            }
	            $jsonImagesData[$imageId] = $jsonFileData;
	            $RawDataEachGallery[$GalleryID][$imageId] = $jsonFileData;
            }
		}

		$infoDataJsonFiles = glob($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-info/*.json');
		foreach ($infoDataJsonFiles as $jsonFile) {
			$stringArray= explode('/image-info-',$jsonFile);
			$imageId = substr(substr($jsonFile,strrpos($jsonFile,'-')+1, 30),0,-5);
			if(in_array($imageId,$realIds)!==false){
				$jsonFileData = json_decode(file_get_contents($jsonFile),true);
				$InfoDataEachGallery[$GalleryID][$imageId] = $jsonFileData;
			}
		}

		$OptionsDataEachGallery[$GalleryID] = json_decode(file_get_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/'.$GalleryID.'-options.json'),true);

		$OptionsDataEachGallery[$GalleryID] = (!empty($OptionsDataEachGallery[$GalleryID][$GalleryID])) ? $OptionsDataEachGallery[$GalleryID][$GalleryID] : $OptionsDataEachGallery[$GalleryID];

		$ecommerceFilesData = [];
		$collectedIds = '';
		foreach ($realIds as $realId){
			$realId = absint($realId);
			if(empty($collectedIds)){
				$collectedIds = "pid = $realId";
			}else{
				$collectedIds .= " OR pid = $realId";
			}
		}

		$ecommerceFilesSQL = $wpdb->get_results( "SELECT * FROM $tablenameEcommerceEntries WHERE GalleryID = $GalleryID AND ($collectedIds) ");

		$EcommerceFilesDataEachGallery[$GalleryID] = [];

		foreach($ecommerceFilesSQL as $ecommerceFileSQL){
			$EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid] = json_decode(json_encode($ecommerceFileSQL),true);
			// has to be unserialized because is unserialized otherwise jsond_decode processing error later
			$EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['WpUploadFilesPosts'] = unserialize($EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['WpUploadFilesPosts']);
			$EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['WpUploadFilesPostMeta'] = unserialize($EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['WpUploadFilesPostMeta']);
			$EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['WpUploadFilesForSale'] = unserialize($EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['WpUploadFilesForSale']);
			$EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['WatermarkSettings'] = unserialize($EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['WatermarkSettings']);
			$EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['AllUploadsUsedText'] = contest_gal1ery_convert_for_html_output_without_nl2br($EcommerceFilesDataEachGallery[$GalleryID][$ecommerceFileSQL->pid]['AllUploadsUsedText']);
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

}