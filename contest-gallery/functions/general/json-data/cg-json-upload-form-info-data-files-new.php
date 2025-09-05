<?php

add_action('cg_json_upload_form_info_data_files_new','cg_json_upload_form_info_data_files_new');
if(!function_exists('cg_json_upload_form_info_data_files_new')){
	function cg_json_upload_form_info_data_files_new($GalleryID,$pidsArray=[],$isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized = false,$IsForWpPageTitleInputDeleted = false,$IsFromCopyGalleryOrActualizeAll = false){

		// var_dump('$isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized123');
		// var_dump($isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized);

		global $wpdb;

		$tablename = $wpdb->prefix . "contest_gal1ery";
		$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
		$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
		$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
		$tablename_entries = $wpdb->prefix . "contest_gal1ery_entries";

		$wp_upload_dir = wp_upload_dir();
		$jsonUpload = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json';

		if(!is_dir($jsonUpload)){
			mkdir($jsonUpload,0755,true);
		}

		// collect all input ids which can be visible somehow in frontend
		$inputIdsArray = [];
		$options = $wpdb->get_row("SELECT * FROM $tablename_options WHERE id = $GalleryID");
		$optionsVisual = $wpdb->get_row("SELECT * FROM $tablename_options_visual WHERE GalleryID = $GalleryID");
		if(!empty($optionsVisual->Field1IdGalleryView)){$inputIdsArray[]=$optionsVisual->Field1IdGalleryView;}
		if(!empty($optionsVisual->Field2IdGalleryView)){$inputIdsArray[]=$optionsVisual->Field2IdGalleryView;}
		if(!empty($optionsVisual->Field3IdGalleryView)){$inputIdsArray[]=$optionsVisual->Field3IdGalleryView;}

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
			$collect = '';
			foreach ($pidsArray as $pid){
				if(!$collect){
					$collect .= "pid = $pid";
				}else{
					$collect .= " or pid = $pid";
				}
			}
			$entries = $wpdb->get_results("SELECT * FROM $tablename_entries WHERE GalleryID = $GalleryID && ($collect) ORDER BY pid ASC");
		}else{
			$entries = $wpdb->get_results("SELECT * FROM $tablename_entries WHERE GalleryID = $GalleryID ORDER BY pid ASC");
		}

        $Version = intval($options->Version);

	    if($Version>=22 && $IsForWpPageTitleInputId && !empty($isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized)  && empty($IsForWpPageTitleInputDeleted)){

			// var_dump('change 1');
			if(!empty($pidsArray)){
				$collect = '';
				foreach ($pidsArray as $pid){
					if(!$collect){
						$collect .= "id = $pid";
					}else{
						$collect .= " or id = $pid";
					}
				}
				$galleryEntries = $wpdb->get_results("SELECT * FROM $tablename WHERE GalleryID = $GalleryID && ($collect) ORDER BY id ASC");
			}else{
				$galleryEntries = $wpdb->get_results("SELECT * FROM $tablename WHERE GalleryID = $GalleryID ORDER BY id ASC");
			}

			global $cg_post_names_for_a_title;
			$cg_post_names_for_a_title = [];

			global $cg_post_names_for_a_title_numbers;
			$cg_post_names_for_a_title_numbers = [];

			global $cg_processed_post_titles;
			$cg_processed_post_titles = [];

			$galleryEntriesProcessed = [];

			// creation order explained
			// if added via backend then post_title set so or so
			// if input field added that slug with content then content will be set
			// if content deleted then post_title will be set again
			// if field deleted that slug then content will be set everywhere again
			foreach ($entries as $entry){
				// var_dump('$entry');
				// var_dump($entry);
				// echo "<br>";
				if($entry->f_input_id==$IsForWpPageTitleInputId && !empty($entry->Short_Text)){
					$field_content = $entry->Short_Text;
					// example: $field_content = '____------e/$%//\\\'"$%&/-.,;rr_r_____f----f.l-----a___la___----';

					$field_content = cg_pre_process_name_for_url_name($field_content);
					$field_content = cg_check_first_char_for_url_name_after_pre_processing($field_content);
					$field_content = cg_check_last_char_for_url_name_after_pre_processing($field_content);

					$field_content_original = $field_content;
					$field_content_modified = $field_content;

					// var_dump('$field_content_original');
					// echo "<br>";
					// var_dump($field_content_original);
					// echo "<br>";
					// var_dump('$field_content_modified');
					// echo "<br>";
					// var_dump($field_content_modified);
					// echo "<br>";
					// echo "<br>";

					foreach ($galleryEntries as $galleryEntry){

						if($galleryEntry->id==$entry->pid){
							$galleryEntriesProcessed[] = $galleryEntry->id;
							// var_dump('same pid');
							if(!empty($galleryEntry->WpPage)){
								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPage,$Version,$field_content_original,$field_content_modified,$options->WpPageParent,true,$IsFromCopyGalleryOrActualizeAll);
							}
							if(!empty($galleryEntry->WpPageUser)){
								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageUser,$Version,$field_content_original,$field_content_modified,$options->WpPageParentUser);
							}
							if(!empty($galleryEntry->WpPageNoVoting)){

								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageNoVoting,$Version,$field_content_original,$field_content_modified,$options->WpPageParentNoVoting);
							}
							if(!empty($galleryEntry->WpPageWinner)){

								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageWinner,$Version,$field_content_original,$field_content_modified,$options->WpPageParentWinner);
							}
							if(!empty($galleryEntry->WpPageEcommerce)){

								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageEcommerce,$Version,$field_content_original,$field_content_modified,$options->WpPageParentEcommerce);
							}
						}
					}
				}else if($entry->f_input_id==$IsForWpPageTitleInputId && empty($entry->Short_Text)){

					foreach ($galleryEntries as $galleryEntry){
						if($galleryEntry->id==$entry->pid){
							$galleryEntriesProcessed[] = $galleryEntry->id;
							// var_dump('$galleryEntry->id');
							// echo "<br>";
							// var_dump($galleryEntry->id);

							if($galleryEntry->ImgType=='con'){
								$field_content = 'entry';
							}else{
								// example: $field_content = '____------e/$%//\\\'"$%&/-.,;rr_r_____f----f.l-----a___la___----';
								$field_content = cg_pre_process_name_for_url_name($galleryEntry->NamePic);
							}
							$field_content = cg_check_first_char_for_url_name_after_pre_processing($field_content);
							$field_content = cg_check_last_char_for_url_name_after_pre_processing($field_content);// then like post_name

							$field_content_original = $field_content;
							$field_content_modified = $field_content;

							// var_dump('empty $entry->Short_Text');
							// var_dump('$field_content_original');
							// echo "<br>";
							// var_dump($field_content_original);

							// var_dump('$galleryEntry->NamePic');
							// echo "<br>";
							// var_dump($galleryEntry->NamePic);
							// echo "<br>";
							// var_dump('$field_content_modified');
							// echo "<br>";
							// var_dump($field_content_modified);
							// echo "<br>";
							// echo "<br>";

							if(!empty($galleryEntry->WpPage)){
								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPage,$Version,$field_content_original,$field_content_modified,$options->WpPageParent,true,$IsFromCopyGalleryOrActualizeAll);
							}
							if(!empty($galleryEntry->WpPageUser)){
								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageUser,$Version,$field_content_original,$field_content_modified,$options->WpPageParentUser);
							}
							if(!empty($galleryEntry->WpPageNoVoting)){
								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageNoVoting,$Version,$field_content_original,$field_content_modified,$options->WpPageParentNoVoting);
							}
							if(!empty($galleryEntry->WpPageWinner)){
								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageWinner,$Version,$field_content_original,$field_content_modified,$options->WpPageParentWinner);
							}
							if(!empty($galleryEntry->WpPageEcommerce)){
								$field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageEcommerce,$Version,$field_content_original,$field_content_modified,$options->WpPageParentEcommerce);
							}
						}
					}

				}
			}

			//    var_dump('$galleryEntriesProcessed');
			//     var_dump($galleryEntriesProcessed);
			//     var_dump($IsFromCopyGalleryOrActualizeAll);

			// now process all unprocessed
			if(!empty($IsFromCopyGalleryOrActualizeAll)){
				foreach ($galleryEntries as $galleryEntry){
					// then was not processed because no entry was done
					if(in_array($galleryEntry->id,$galleryEntriesProcessed)===false){
						//var_dump('check not processed');
						if($galleryEntry->ImgType=='con'){
							$field_content = 'entry';
						}else{
							// example: $field_content = '____------e/$%//\\\'"$%&/-.,;rr_r_____f----f.l-----a___la___----';
							$field_content = cg_pre_process_name_for_url_name($galleryEntry->NamePic);
						}
						$field_content = cg_check_first_char_for_url_name_after_pre_processing($field_content);
						$field_content = cg_check_last_char_for_url_name_after_pre_processing($field_content);// then like post_name

						$field_content_original = $field_content;
						$field_content_modified = $field_content;

						if(!empty($galleryEntry->WpPage)){
                            $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPage,$Version,$field_content_original,$field_content_modified,$options->WpPageParent,true,$IsFromCopyGalleryOrActualizeAll);
						}
						if(!empty($galleryEntry->WpPageUser)){
                            $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageUser,$Version,$field_content_original,$field_content_modified,$options->WpPageParentUser);
						}
						if(!empty($galleryEntry->WpPageNoVoting)){
                            $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageNoVoting,$Version,$field_content_original,$field_content_modified,$options->WpPageParentNoVoting);
						}
						if(!empty($galleryEntry->WpPageWinner)){
                            $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageWinner,$Version,$field_content_original,$field_content_modified,$options->WpPageParentWinner);
						}
						if(!empty($galleryEntry->WpPageEcommerce)){
                            $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageEcommerce,$Version,$field_content_original,$field_content_modified,$options->WpPageParentEcommerce);
						}
					}
				}
			}


		}
		else if (($Version>=22 && $IsForWpPageTitleInputDeleted) || ($Version>=22 && empty($IsForWpPageTitleInputId) && !empty($isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized) && empty($IsForWpPageTitleInputDeleted))){//  $IsForWpPageTitleInputDeleted can only be from edit upload form
			// IsForWpPageTitle might be unchecked then this condition here will be used

			// var_dump('change 2');

			$galleryEntries = $wpdb->get_results("SELECT * FROM $tablename WHERE GalleryID = $GalleryID ORDER BY id ASC");
			foreach ($galleryEntries as $galleryEntry){

				if($galleryEntry->ImgType=='con'){
					$field_content = 'entry';
				}else{
					// example: $field_content = '____------e/$%//\\\'"$%&/-.,;rr_r_____f----f.l-----a___la___----';
					$field_content = cg_pre_process_name_for_url_name($galleryEntry->NamePic);
				}
				$field_content = cg_check_first_char_for_url_name_after_pre_processing($field_content);
				$field_content = cg_check_last_char_for_url_name_after_pre_processing($field_content);// then like post_name

				$field_content_original = $field_content;
				$field_content_modified = $field_content;

				if(!empty($galleryEntry->WpPage)){
				    $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPage,$Version,$field_content_original,$field_content_modified,$options->WpPageParent,true,$IsFromCopyGalleryOrActualizeAll);
				}
				if(!empty($galleryEntry->WpPageUser)){
				    $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageUser,$Version,$field_content_original,$field_content_modified,$options->WpPageParentUser);
				}
				if(!empty($galleryEntry->WpPageNoVoting)){
				    $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageNoVoting,$Version,$field_content_original,$field_content_modified,$options->WpPageParentNoVoting);
				}
				if(!empty($galleryEntry->WpPageWinner)){
				    $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageWinner,$Version,$field_content_original,$field_content_modified,$options->WpPageParentWinner);
				}
				if(!empty($galleryEntry->WpPageEcommerce)){
				    $field_content_modified = cg_update_custom_post_type_name($galleryEntry->WpPageEcommerce,$Version,$field_content_original,$field_content_modified,$options->WpPageParentEcommerce);
				}

			}
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
				}else if($row->Field_Type == 'date-f' && !empty($row->InputDate) && $row->InputDate!='0000-00-00 00:00:00'){
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

			}
		}

	}
}

