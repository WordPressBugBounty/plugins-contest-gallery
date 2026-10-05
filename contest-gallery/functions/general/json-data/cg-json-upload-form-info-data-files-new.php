<?php
include_once __DIR__.'/../cg-entry-page-urls.php';

add_action('cg_json_upload_form_info_data_files_new','cg_json_upload_form_info_data_files_new');
if(!function_exists('cg_json_upload_form_info_data_files_new')){
	function cg_json_upload_form_info_data_files_new($GalleryID,$pidsArray=[],$isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized = false,$IsForWpPageTitleInputDeleted = false,$IsFromCopyGalleryOrActualizeAll = false,$deferFrontendFreshness = false){

		// var_dump('$isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized123');
		// var_dump($isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized);

		global $wpdb;

		$tablename = $wpdb->prefix . "contest_gal1ery";
		$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
		$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
		$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
		$tablename_entries = $wpdb->prefix . "contest_gal1ery_entries";

			$GalleryID = absint($GalleryID);
			$wp_upload_dir = wp_upload_dir();
			$jsonUpload = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json';
			$pidsArraySanitized = array();
			if(!empty($pidsArray) && is_array($pidsArray)){
				foreach($pidsArray as $pid){
					$pid = absint($pid);
					if(!empty($pid)){
						$pidsArraySanitized[$pid] = $pid;
					}
				}
			}
			$pidsArray = array_values($pidsArraySanitized);

		if(!is_dir($jsonUpload)){
			mkdir($jsonUpload,0755,true);
		}

		// collect all input ids which can be visible somehow in frontend
		$inputIdsArray = [];
		$options = $wpdb->get_row("SELECT * FROM $tablename_options WHERE id = $GalleryID");
		$optionsVisual = $wpdb->get_row("SELECT * FROM $tablename_options_visual WHERE GalleryID = $GalleryID");
		$Field1IdGalleryView = (!empty($optionsVisual) && isset($optionsVisual->Field1IdGalleryView)) ? absint($optionsVisual->Field1IdGalleryView) : 0;
		$Field1IdFullWindowBlogView = (!empty($optionsVisual) && isset($optionsVisual->Field1IdFullWindowBlogView)) ? absint($optionsVisual->Field1IdFullWindowBlogView) : 0;
		$Field2IdGalleryView = (!empty($optionsVisual) && isset($optionsVisual->Field2IdGalleryView)) ? absint($optionsVisual->Field2IdGalleryView) : 0;
		$Field3IdGalleryView = (!empty($optionsVisual) && isset($optionsVisual->Field3IdGalleryView)) ? absint($optionsVisual->Field3IdGalleryView) : 0;
		if(!empty($Field1IdGalleryView)){$inputIdsArray[]=$Field1IdGalleryView;}
		if(!empty($Field1IdFullWindowBlogView)){$inputIdsArray[]=$Field1IdFullWindowBlogView;}
		if(!empty($Field2IdGalleryView)){$inputIdsArray[]=$Field2IdGalleryView;}
		if(!empty($Field3IdGalleryView)){$inputIdsArray[]=$Field3IdGalleryView;}

		$inputs = $wpdb->get_results("SELECT * FROM $tablename_form_input WHERE GalleryID = $GalleryID");
		$frontendInputProperties = ['Show_Slider','WatermarkPosition','SubTitle','ThirdTitle','EcommerceTitle','EcommerceDescription','IsForWpPageTitle','FieldTitleGallery'];
		$fieldTitlesArray = [];
		$dateFieldsIdsAndFormatArray = array();

		// has to be processed extra, IsForWpPageTitle is not visible in frontend, is for Custom Post Type Pages URL>s
		$IsForWpPageTitleInputId = 0;
		foreach($inputs as $input){
			if($input->IsForWpPageTitle==1){
				$IsForWpPageTitleInputId = $input->id;
			}
		}

		foreach($inputs as $input){
			foreach ($frontendInputProperties as $property){
				if(!empty($input->$property)){
					$inputIdsArray[]=$input->id;
					$Field_Content = unserialize($input->Field_Content);
					if(!empty($input->FieldTitleGallery)){
						$fieldTitlesArray[$input->id] = $input->FieldTitleGallery;
					}else{
						$fieldTitlesArray[$input->id] = $Field_Content["titel"];
					}
					if($input->Field_Type=='date-f'){
						$dateFieldsIdsAndFormatArray[$input->id] = $Field_Content["format"];
					}
				}
			}
		}

			if(!empty($pidsArray)){
				$pidsPlaceholders = implode(',', array_fill(0, count($pidsArray), '%d'));
				$entries = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename_entries WHERE GalleryID = %d AND pid IN ($pidsPlaceholders) ORDER BY pid ASC",array_merge(array($GalleryID),$pidsArray)));
			}else{
				$entries = $wpdb->get_results("SELECT * FROM $tablename_entries WHERE GalleryID = $GalleryID ORDER BY pid ASC");
			}

		$galleryEntries = array();
		if((int)$options->Version>=22 && ($isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized || $IsForWpPageTitleInputDeleted)){
			if($pidsArray){
				$pidsPlaceholders = implode(',',array_fill(0,count($pidsArray),'%d'));
				$rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename WHERE GalleryID=%d AND id IN ($pidsPlaceholders) ORDER BY id ASC",array_merge(array($GalleryID),$pidsArray)));
			}else{$rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename WHERE GalleryID=%d ORDER BY id ASC",$GalleryID));}
			$galleryEntries = cg_entry_page_update_urls($rows,$options,$entries,$IsForWpPageTitleInputDeleted ? 0 : $IsForWpPageTitleInputId,$IsFromCopyGalleryOrActualizeAll,$IsFromCopyGalleryOrActualizeAll);
			if(is_wp_error($galleryEntries)){return $galleryEntries;}
		}

		if(empty($deferFrontendFreshness) && !empty($galleryEntries)){
			// Entry permalinks changed above. The frontend also derives recent URLs from main IDs.
			foreach($galleryEntries as $galleryEntry){
				cg1l_push_recent_id_file($GalleryID,$galleryEntry->id,'image-main-data-last-update');
			}
			cg1l_create_last_updated_time_file($GalleryID,'image-main-data-last-update');
			cg1l_create_last_updated_time_file($GalleryID,'image-urls-data-last-update');
		}

		$arrayDataForImage = [];
		$deletedInfoFiles = [];

		foreach($entries as $row){

			if(in_array($row->pid,$deletedInfoFiles)!==false){
				// delete file for sure, cause might not have entries, will be created if have
				$jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-info/image-info-'.$row->pid.'.json';
				if(file_exists($jsonFile)){
					unlink($jsonFile);
				}
				$deletedInfoFiles[] = $row->pid;
			}

			if(in_array($row->f_input_id,$inputIdsArray)!==false){

				if(empty($arrayDataForImage[$row->pid])){
					$arrayDataForImage[$row->pid] = array();
				}

				$arrayDataForImage[$row->pid][$row->f_input_id] = array();

				$arrayDataForImage[$row->pid][$row->f_input_id]['field-type'] = $row->Field_Type;

				$arrayDataForImage[$row->pid][$row->f_input_id]['field-title'] = isset($fieldTitlesArray[$row->f_input_id]) ? $fieldTitlesArray[$row->f_input_id] : '';

				if(!empty($row->Field_Type == 'comment-f')){// <<< check field type here!!!
					$arrayDataForImage[$row->pid][$row->f_input_id]['field-content'] = $row->Long_Text;

					// some date time fields in some systems might start with 1999-01-01 00:00:00
					// so important to check to $row->Field_Type == 'dt'
				}elseif($row->Field_Type == 'date-f' && !empty($row->InputDate) && $row->InputDate!='0000-00-00 00:00:00'){
					$newDateTimeString = '';

					try {

						if(!empty($dateFieldsIdsAndFormatArray[$row->f_input_id])){// might be hidden or deactivated this why check here

							$dtFormat = $dateFieldsIdsAndFormatArray[$row->f_input_id];

							$dtFormat = str_replace('YYYY','Y',$dtFormat);
							$dtFormat = str_replace('MM','m',$dtFormat);
							$dtFormat = str_replace('DD','d',$dtFormat);

							$newDateTimeObject = DateTime::createFromFormat("Y-m-d H:i:s",$row->InputDate);

							if(is_object($newDateTimeObject)){
								$newDateTimeString = $newDateTimeObject->format($dtFormat);
							}

						}

					}catch (Exception $e) {

						$newDateTimeString = '';

					}

					$arrayDataForImage[$row->pid][$row->f_input_id]['field-content'] = $newDateTimeString;

				}else{
					$arrayDataForImage[$row->pid][$row->f_input_id]['field-content'] = $row->Short_Text;
				}

				if(!is_dir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-info')){
					mkdir($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-info',0755,true);
				}

				$jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-info/image-info-'.$row->pid.'.json';
				file_put_contents($jsonFile, json_encode($arrayDataForImage[$row->pid]));

	                if(!empty($deferFrontendFreshness)){
	                    continue;
	                }

	                cg1l_push_recent_id_file($GalleryID,$row->pid,'image-info-data-last-update');

                static $cg1l_last_updated_done = false;

                if (!$cg1l_last_updated_done || time() > $cg1l_last_updated_done) {
                    $cg1l_last_updated_done = time();
                    cg1l_create_last_updated_time_file($GalleryID,'image-info-data-last-update');
                }

			}
		}



	}
}
