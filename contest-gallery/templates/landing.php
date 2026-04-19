<?php
global $post;
global $wpdb;
$tablename = $wpdb->prefix . "contest_gal1ery";
$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
$tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
$tablename_wp_pages = $wpdb->prefix . "contest_gal1ery_wp_pages";
$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
$tablenameentries = $wpdb->prefix . "contest_gal1ery_entries";
global $isCgParentPage;
global $cgWpPageParent;
global $cgId;
global $cgOptionsArray;
global $cgGalleryIDuser;
global $isCGalleries;
$blogname = get_option('blogname');
$postId = $post->ID;
$currentPermalink = get_permalink($postId);
$permalink = (function_exists('cgl_get_clean_absolute_url')) ? cgl_get_clean_absolute_url($currentPermalink) : $currentPermalink;
$permalink = (!empty($permalink)) ? $permalink : $currentPermalink;
$canonicalUrl = $permalink;
$rowObject = null;
global $cgShortCodeType;
$shortCodeType = $cgShortCodeType;

$wp_upload_dir = wp_upload_dir();

if(!function_exists('cg1l_landing_clean_text')){
	function cg1l_landing_clean_text($text){
		$text = (string)$text;
		$text = str_replace('\\', '', $text);
		$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
		$text = wp_strip_all_tags($text);
		$text = preg_replace('/\s+/u', ' ', $text);
		return trim($text);
	}
}

if(!function_exists('cg1l_landing_get_post_description_fallback')){
	function cg1l_landing_get_post_description_fallback($postObject){
		if(empty($postObject)){
			return '';
		}

		$candidates = [];
		if(!empty($postObject->post_excerpt)){
			$candidates[] = $postObject->post_excerpt;
		}
		if(!empty($postObject->post_content)){
			$candidates[] = strip_shortcodes($postObject->post_content);
		}

		foreach($candidates as $candidate){
			$candidate = cg1l_landing_clean_text($candidate);
			if($candidate !== ''){
				return $candidate;
			}
		}

		return '';
	}
}

if(!function_exists('cg1l_landing_get_shortcode_page_map')){
	function cg1l_landing_get_shortcode_page_map($shortcodeName){
		if(function_exists('cgl_get_shortcode_page_map')){
			return cgl_get_shortcode_page_map($shortcodeName);
		}

		$map = [
			'cg_gallery' => [
				'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-slug',
				'parentField' => 'WpPageParent',
				'entryField' => 'WpPage',
				'optionsKeySuffix' => '',
			],
			'cg_gallery_user' => [
				'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-user-slug',
				'parentField' => 'WpPageParentUser',
				'entryField' => 'WpPageUser',
				'optionsKeySuffix' => '-u',
			],
			'cg_gallery_no_voting' => [
				'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-no-voting-slug',
				'parentField' => 'WpPageParentNoVoting',
				'entryField' => 'WpPageNoVoting',
				'optionsKeySuffix' => '-nv',
			],
			'cg_gallery_winner' => [
				'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-winner-slug',
				'parentField' => 'WpPageParentWinner',
				'entryField' => 'WpPageWinner',
				'optionsKeySuffix' => '-w',
			],
			'cg_gallery_ecommerce' => [
				'overviewPostMimeType' => 'contest-gallery-plugin-page-galleries-ecommerce-slug',
				'parentField' => 'WpPageParentEcommerce',
				'entryField' => 'WpPageEcommerce',
				'optionsKeySuffix' => '-ec',
			],
		];

		return (!empty($map[$shortcodeName])) ? $map[$shortcodeName] : $map['cg_gallery'];
	}
}

if(!function_exists('cg1l_landing_get_gallery_options_for_shortcode')){
	function cg1l_landing_get_gallery_options_for_shortcode($galleryId, $shortcodeName){
		$wp_upload_dir = wp_upload_dir();
		$optionsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryId.'/json/'.$galleryId.'-options.json';
		if(!file_exists($optionsFile)){
			return [];
		}

		$optionsFileData = json_decode(file_get_contents($optionsFile), true);
		if(empty($optionsFileData) || !is_array($optionsFileData)){
			return [];
		}

		$map = cg1l_landing_get_shortcode_page_map($shortcodeName);
		$key = $galleryId.$map['optionsKeySuffix'];

		if(!empty($optionsFileData[$key]) && is_array($optionsFileData[$key])){
			return $optionsFileData[$key];
		}

		return $optionsFileData;
	}
}

if(!function_exists('cg1l_landing_get_entry_info_data')){
	function cg1l_landing_get_entry_info_data($galleryId, $entryId){
		$wp_upload_dir = wp_upload_dir();
		$jsonFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galleryId.'/json/image-info/image-info-'.$entryId.'.json';
		if(!file_exists($jsonFile)){
			return [];
		}

		$json = json_decode(file_get_contents($jsonFile), true);
		return (!empty($json) && is_array($json)) ? $json : [];
	}
}

if(!function_exists('cg1l_landing_get_info_value_by_field_id')){
	function cg1l_landing_get_info_value_by_field_id($infoData, $fieldId){
		$fieldId = absint($fieldId);
		if(empty($fieldId) || empty($infoData[$fieldId]['field-content'])){
			return '';
		}

		return cg1l_landing_clean_text($infoData[$fieldId]['field-content']);
	}
}

if(!function_exists('cg1l_landing_get_first_info_value')){
	function cg1l_landing_get_first_info_value($infoData, $preferredFieldIds = []){
		foreach($preferredFieldIds as $fieldId){
			$value = cg1l_landing_get_info_value_by_field_id($infoData, $fieldId);
			if($value !== ''){
				return $value;
			}
		}

		foreach($infoData as $fieldData){
			if(empty($fieldData['field-content'])){
				continue;
			}

			$fieldType = (!empty($fieldData['field-type'])) ? $fieldData['field-type'] : '';
			if($fieldType === 'comment-f' || $fieldType === 'text-f' || $fieldType === 'date-f' || $fieldType === 'url-f' || $fieldType === 'radio-f' || $fieldType === 'chk-f' || $fieldType === 'select-f'){
				$value = cg1l_landing_clean_text($fieldData['field-content']);
				if($value !== ''){
					return $value;
				}
			}
		}

		return '';
	}
}

if(!function_exists('cg1l_landing_get_galleries_page_id')){
	function cg1l_landing_get_galleries_page_id($shortcodeName){
		global $wpdb;
		$tablename_posts = $wpdb->prefix . 'posts';
		$map = cg1l_landing_get_shortcode_page_map($shortcodeName);
		return absint($wpdb->get_var($wpdb->prepare("SELECT ID FROM $tablename_posts WHERE post_mime_type = %s LIMIT 1", [$map['overviewPostMimeType']])));
	}
}

if(!function_exists('cg1l_landing_get_current_entry_page_id')){
	function cg1l_landing_get_current_entry_page_id($rowObject, $shortcodeName){
		if(empty($rowObject)){
			return 0;
		}

		$map = cg1l_landing_get_shortcode_page_map($shortcodeName);
		$entryField = $map['entryField'];
		return (!empty($rowObject->$entryField)) ? absint($rowObject->$entryField) : 0;
	}
}

if(!function_exists('cg1l_landing_get_overview_items')){
	function cg1l_landing_get_overview_items($shortcodeName, $limit = 24){
		global $wpdb;

		if($shortcodeName === 'cg_gallery_user' && !is_user_logged_in()){
			return [];
		}

		$tablename_options = $wpdb->prefix . 'contest_gal1ery_options';
		$map = cg1l_landing_get_shortcode_page_map($shortcodeName);
		$parentField = $map['parentField'];
		$rows = $wpdb->get_results("SELECT id, $parentField AS ParentPageId FROM $tablename_options ORDER BY id DESC");
		$items = [];
		$position = 1;

		if(empty($rows)){
			return [];
		}

		foreach($rows as $row){
			if(count($items) >= $limit){
				break;
			}

			$galleryId = absint($row->id);
			$parentPageId = absint($row->ParentPageId);

			if(empty($galleryId) || empty($parentPageId)){
				continue;
			}

			$options = cg1l_landing_get_gallery_options_for_shortcode($galleryId, $shortcodeName);
			if(empty($options)){
				continue;
			}

			$intervalConf = cg_shortcode_interval_check($galleryId, $options, $shortcodeName, true);
			if(empty($intervalConf['shortcodeIsActive'])){
				continue;
			}

			$itemUrl = get_permalink($parentPageId);
			if(empty($itemUrl)){
				continue;
			}

			$itemName = '';
			if(!empty($options['pro']['MainTitleGalleriesView'])){
				$itemName = cg1l_landing_clean_text($options['pro']['MainTitleGalleriesView']);
			}
			if($itemName === ''){
				$itemName = cg1l_landing_clean_text(get_the_title($parentPageId));
			}
			if($itemName === ''){
				continue;
			}

			$items[] = [
				'@type' => 'ListItem',
				'position' => $position,
				'item' => [
					'@type' => 'CollectionPage',
					'name' => $itemName,
					'url' => $itemUrl,
				],
			];
			$position++;
		}

		return $items;
	}
}

if(!function_exists('cg1l_landing_get_gallery_items')){
	function cg1l_landing_get_gallery_items($galleryId, $shortcodeName, $options, $limit = 24){
		global $wpdb;

		if(empty($galleryId)){
			return [];
		}

		if($shortcodeName === 'cg_gallery_user' && !is_user_logged_in()){
			return [];
		}

		$intervalConf = cg_shortcode_interval_check($galleryId, $options, $shortcodeName);
		if(empty($intervalConf['shortcodeIsActive'])){
			return [];
		}

		$tablename = $wpdb->prefix . 'contest_gal1ery';
		$map = cg1l_landing_get_shortcode_page_map($shortcodeName);
		$entryField = $map['entryField'];
		$whereParts = [
			$wpdb->prepare('GalleryID = %d', [$galleryId]),
			"Active = 1",
		];

		if($shortcodeName === 'cg_gallery_winner'){
			$whereParts[] = "Winner = 1";
		}elseif($shortcodeName === 'cg_gallery_ecommerce'){
			$whereParts[] = "EcommerceEntry > 0";
		}elseif($shortcodeName === 'cg_gallery_user'){
			$whereParts[] = $wpdb->prepare('WpUserId = %d', [get_current_user_id()]);
		}

		$query = "SELECT id, $entryField AS EntryPageId FROM $tablename WHERE ".implode(' AND ', $whereParts)." ORDER BY id DESC LIMIT ".absint($limit);
		$rows = $wpdb->get_results($query);
		$items = [];
		$position = 1;

		if(empty($rows)){
			return [];
		}

		foreach($rows as $row){
			$entryId = absint($row->id);
			$entryPageId = absint($row->EntryPageId);
			if(empty($entryId) || empty($entryPageId)){
				continue;
			}

			$itemUrl = get_permalink($entryPageId);
			if(empty($itemUrl)){
				continue;
			}

			$infoData = cg1l_landing_get_entry_info_data($galleryId, $entryId);
			$itemName = cg1l_landing_get_first_info_value($infoData, [
				(!empty($options['visual']['Field1IdGalleryView']) ? $options['visual']['Field1IdGalleryView'] : 0),
				(!empty($options['visual']['SubTitle']) ? $options['visual']['SubTitle'] : 0),
				(!empty($options['visual']['ThirdTitle']) ? $options['visual']['ThirdTitle'] : 0),
				(!empty($options['visual']['Field2IdGalleryView']) ? $options['visual']['Field2IdGalleryView'] : 0),
			]);

			if($itemName === ''){
				$itemName = cg1l_landing_clean_text(get_the_title($entryPageId));
			}
			if($itemName === ''){
				continue;
			}

			$items[] = [
				'@type' => 'ListItem',
				'position' => $position,
				'item' => [
					'@type' => 'CreativeWork',
					'name' => $itemName,
					'url' => $itemUrl,
				],
			];
			$position++;
		}

		return $items;
	}
}

if(!function_exists('cg1l_landing_get_entry_media_data')){
	function cg1l_landing_get_entry_media_data($rowObject, $tablename_ecommerce_entries){
		global $wpdb;

		$mediaData = [
			'ogImage' => '',
			'contentUrl' => '',
			'thumbnailUrl' => '',
			'encodingFormat' => '',
			'width' => 0,
			'height' => 0,
			'schemaType' => 'CreativeWork',
		];

		if(empty($rowObject) || empty($rowObject->Active)){
			return $mediaData;
		}

		$imgType = (!empty($rowObject->ImgType)) ? $rowObject->ImgType : '';
		$mediaData['contentUrl'] = (!empty($rowObject->guid)) ? $rowObject->guid : '';

		if(cg_is_is_image($imgType)){
			$mediaData['schemaType'] = 'ImageObject';
			$mediaData['contentUrl'] = wp_get_attachment_url($rowObject->WpUpload);

			$isEcommerceDownload = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tablename_ecommerce_entries WHERE pid = %d AND IsDownload = '1'", [$rowObject->id]));

			if($rowObject->EcommerceEntry && $isEcommerceDownload){
				$wpUploadFilesPostMeta = unserialize($wpdb->get_var($wpdb->prepare("SELECT WpUploadFilesPostMeta FROM $tablename_ecommerce_entries WHERE pid = %d", [$rowObject->id])));
				if(isset($wpUploadFilesPostMeta[$rowObject->WpUpload])){
					if(!empty($rowObject->PdfPreview)){
						$largeSource = wp_get_attachment_image_src($rowObject->PdfPreview, 'large');
						$mediaData['ogImage'] = (!empty($largeSource[0])) ? $largeSource[0] : '';
					}else{
						$attachedFileDir = substr($wpUploadFilesPostMeta[$rowObject->WpUpload]['_wp_attached_file'], 0, strrpos($wpUploadFilesPostMeta[$rowObject->WpUpload]['_wp_attached_file'], '/'));
						$wp_upload_dir = wp_upload_dir();
						$fileName = $wpUploadFilesPostMeta[$rowObject->WpUpload]['_wp_attachment_metadata']['file'];
						$mediaData['ogImage'] = $wp_upload_dir['baseurl'].'/'.$attachedFileDir.((isset($wpUploadFilesPostMeta[$rowObject->WpUpload]['_wp_attachment_metadata']['sizes']['large']['file'])) ? $wpUploadFilesPostMeta[$rowObject->WpUpload]['_wp_attachment_metadata']['sizes']['large']['file'] : $fileName);
					}
				}
			}

			if($mediaData['ogImage'] === ''){
				if(!empty($rowObject->PdfPreview)){
					$imgSrcLarge = wp_get_attachment_image_src($rowObject->PdfPreview, 'large');
				}else{
					$imgSrcLarge = wp_get_attachment_image_src($rowObject->WpUpload, 'large');
				}
				$mediaData['ogImage'] = (!empty($imgSrcLarge[0])) ? $imgSrcLarge[0] : '';
				$mediaData['width'] = (!empty($imgSrcLarge[1])) ? absint($imgSrcLarge[1]) : 0;
				$mediaData['height'] = (!empty($imgSrcLarge[2])) ? absint($imgSrcLarge[2]) : 0;
			}

			$mediaData['thumbnailUrl'] = $mediaData['ogImage'];
			$attachmentMetadata = wp_get_attachment_metadata($rowObject->WpUpload);
			if(!empty($attachmentMetadata['width'])){$mediaData['width'] = absint($attachmentMetadata['width']);}
			if(!empty($attachmentMetadata['height'])){$mediaData['height'] = absint($attachmentMetadata['height']);}
			$mimeType = get_post_mime_type($rowObject->WpUpload);
			if(!empty($mimeType)){$mediaData['encodingFormat'] = $mimeType;}
		}elseif(cg_is_alternative_file_type_video($imgType)){
			$mediaData['schemaType'] = 'VideoObject';
			$fileData = wp_get_attachment_metadata($rowObject->WpUpload);
			$mediaData['encodingFormat'] = (!empty($fileData['mime_type'])) ? $fileData['mime_type'] : '';
			$mediaData['width'] = (!empty($fileData['width'])) ? absint($fileData['width']) : 0;
			$mediaData['height'] = (!empty($fileData['height'])) ? absint($fileData['height']) : 0;
			if(!empty($rowObject->PdfPreview)){
				$imgSrcLarge = wp_get_attachment_image_src($rowObject->PdfPreview, 'large');
				$mediaData['thumbnailUrl'] = (!empty($imgSrcLarge[0])) ? $imgSrcLarge[0] : '';
				$mediaData['ogImage'] = $mediaData['thumbnailUrl'];
			}
		}else{
			$audioTypes = ['mp3','m4a','ogg','wav'];
			if(in_array($imgType, $audioTypes)){
				$mediaData['schemaType'] = 'AudioObject';
			}else{
				$mediaData['schemaType'] = 'MediaObject';
			}
			$mimeType = get_post_mime_type($rowObject->WpUpload);
			if(!empty($mimeType)){$mediaData['encodingFormat'] = $mimeType;}
			$imgToShow = '';
			include (__DIR__.'/../base64-file-types-data.php');
			if($imgType=='pdf'){$imgToShow = $pdf;}
			elseif($imgType=='zip'){$imgToShow = $zip;}
			elseif($imgType=='txt'){$imgToShow = $txt;}
			elseif($imgType=='doc'){$imgToShow = $doc;}
			elseif($imgType=='docx'){$imgToShow = $docx;}
			elseif($imgType=='xls'){$imgToShow = $xls;}
			elseif($imgType=='xlsx'){$imgToShow = $xlsx;}
			elseif($imgType=='csv'){$imgToShow = $csv;}
			elseif($imgType=='mp3'){$imgToShow = $mp3;}
			elseif($imgType=='m4a'){$imgToShow = $m4a;}
			elseif($imgType=='ogg'){$imgToShow = $ogg;}
			elseif($imgType=='wav'){$imgToShow = $wav;}
			elseif($imgType=='ppt'){$imgToShow = $ppt;}
			elseif($imgType=='pptx'){$imgToShow = $pptx;}
			$mediaData['ogImage'] = $imgToShow;
			$mediaData['thumbnailUrl'] = $imgToShow;
		}

		return $mediaData;
	}
}

if(!function_exists('cg1l_landing_get_entry_ecommerce_row')){
	function cg1l_landing_get_entry_ecommerce_row($ecommerceEntryId){
		global $wpdb;
		$tablename_ecommerce_entries = $wpdb->prefix . 'contest_gal1ery_ecommerce_entries';
		if(empty($ecommerceEntryId)){
			return null;
		}
		return $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_ecommerce_entries WHERE id = %d LIMIT 1", [$ecommerceEntryId]));
	}
}

if(!function_exists('cg1l_landing_get_json_ld_for_page')){
	function cg1l_landing_get_json_ld_for_page($pageData){
		if(empty($pageData['pageSchema'])){
			return '';
		}

		$graph = [];
		$graph[] = $pageData['pageSchema'];

		if(!empty($pageData['breadcrumbs'])){
			$graph[] = [
				'@type' => 'BreadcrumbList',
				'itemListElement' => $pageData['breadcrumbs'],
			];
		}

		return '<script type="application/ld+json">'.wp_json_encode([
			'@context' => 'https://schema.org',
			'@graph' => $graph,
		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'</script>'."\r\n";
	}
}

/*
    $isParentPage = false;
    if(empty($postParent)){
        $isParentPage = true;
        $postParent = $post->ID;
        $WpPageParent = $wpdb->get_var( "SELECT id FROM $tablename_wp_pages WHERE WpPage = $postParent LIMIT 1" );
    }else{
        $WpPageParent = $wpdb->get_var( "SELECT id FROM $tablename_wp_pages WHERE WpPage = $postParent LIMIT 1" );
    }*/
if(!empty($cgWpPageParent)){
    $options = $wpdb->get_row( "SELECT id,WpPageParent,WpPageParentUser,WpPageParentNoVoting,WpPageParentWinner,WpPageParentEcommerce FROM $tablename_options WHERE WpPageParent = $cgWpPageParent OR WpPageParentUser = $cgWpPageParent OR WpPageParentNoVoting = $cgWpPageParent OR WpPageParentWinner = $cgWpPageParent OR WpPageParentEcommerce = $cgWpPageParent  LIMIT 1" );
    $cgGalleryID = $options->id;
    $shortCodeType = 'cg_gallery';
    if($options->WpPageParentUser==$cgWpPageParent){
        $shortCodeType = 'cg_gallery_user';
    }elseif($options->WpPageParentNoVoting==$cgWpPageParent){
        $shortCodeType = 'cg_gallery_no_voting';
    }elseif($options->WpPageParentWinner==$cgWpPageParent){
        $shortCodeType = 'cg_gallery_winner';
    }elseif($options->WpPageParentEcommerce==$cgWpPageParent){
        $shortCodeType = 'cg_gallery_ecommerce';
    }
    if(!empty($isCgParentPage)){
        global $cgGalleryID;
        $cgGalleryID = $options->id;
        global $cgShortCodeType;
        $cgShortCodeType = $shortCodeType;
    }else{
        if(!empty($cgGalleryID) && !empty($cgId)){
            $rowObject = $wpdb->get_row( "SELECT * FROM $tablename WHERE id = $cgId" );
            global $cgGalleryID;
            $cgGalleryID = $rowObject->GalleryID;
            global $cgEntryId;
            $cgEntryId = $cgId;
            global $cgShortCodeType;
            $cgShortCodeType = $shortCodeType;

            $post_title = $post->post_title;
            $post_excerpt = $post->post_excerpt;
            $ImgType = $rowObject->ImgType;

            $WpPageTitle = '';
            $IsForWpPageTitleInputId = $wpdb->get_var("SELECT id FROM $tablename_form_input WHERE GalleryID = '$cgGalleryID' AND IsForWpPageTitle=1");
            if(!empty($IsForWpPageTitleInputId)){
                $ShortText = $wpdb->get_var("SELECT Short_Text FROM $tablenameentries WHERE pid = '$cgId' AND f_input_id=$IsForWpPageTitleInputId");
                if(!empty($ShortText)){
                    $WpPageTitle = contest_gal1ery_convert_for_html_output_without_nl2br($ShortText);
                }
            }

            $WpPageDescription = '';
            $IsForWpPageDescriptionInputId = $wpdb->get_var("SELECT id FROM $tablename_form_input WHERE GalleryID = '$cgGalleryID' AND IsForWpPageDescription=1");
            if(!empty($IsForWpPageDescriptionInputId)){
                $entry = $wpdb->get_row("SELECT Long_Text, Short_Text, Field_Type FROM $tablenameentries WHERE pid = '$cgId' AND f_input_id=$IsForWpPageDescriptionInputId");
                if(!empty($entry) && $entry->Field_Type=='text-f'){
                    $WpPageDescription = contest_gal1ery_convert_for_html_output_without_nl2br($entry->Short_Text);
                }elseif(!empty($entry) && $entry->Field_Type=='comment-f'){
                    $WpPageDescription = contest_gal1ery_convert_for_html_output_without_nl2br($entry->Long_Text);
                }
            }

            $IsForWpPageDescription = $wpdb->get_var("SELECT IsForWpPageDescription FROM $tablename_form_input WHERE id = '$cgId'");

            if($IsForWpPageDescription==1){$checkedIsForWpPageDescription='checked';}
            else{$checkedIsForWpPageDescription='';}

        }
    }

}

$WpPageTitle = (!empty($WpPageTitle)) ? $WpPageTitle : '';
$WpPageDescription = (!empty($WpPageDescription)) ? $WpPageDescription : '';

//echo "<pre>";
//print_r($cgOptionsArray);
//echo "</pre>";
//die;

$options = [];
if(!empty($cgGalleryIDuser) && !empty($cgOptionsArray[$cgGalleryIDuser]) && is_array($cgOptionsArray[$cgGalleryIDuser])){
	$options = $cgOptionsArray[$cgGalleryIDuser];
}elseif(!empty($cgOptionsArray) && is_array($cgOptionsArray)){
	$options = $cgOptionsArray;
}

$additionalCss = '';
$HeaderWpPageEntry = '';
if(!empty($cgEntryId)){
    if(isset($options['visual']['AdditionalCssEntryLandingPage'])){// only json option, might be not set if options were never saved before
        $additionalCss = $options['visual']['AdditionalCssEntryLandingPage'];
    }
    if(!empty($options['visual']['HeaderWpPageEntry'])){// only json option, might be not set if options were never saved before
        $HeaderWpPageEntry = contest_gal1ery_convert_for_html_header_output($options['visual']['HeaderWpPageEntry']);
    }
}else{
    if(isset($options['visual']['AdditionalCssGalleryPage'])){// only json option, might be not set if options were never saved before
        $additionalCss = $options['visual']['AdditionalCssGalleryPage'];
    }
}

$galleriesOptions = cg_galleries_options($shortCodeType,$post->post_mime_type);

$metaRobots = '';

if(empty($galleriesOptions['GalleriesPagesNoIndex']) && empty($galleriesOptions['GalleriesPagesNoFollow'])){
	$metaRobots = '<meta name="robots" content="noindex, nofollow">'."\r\n";
}elseif(empty($galleriesOptions['GalleriesPagesNoIndex']) && !empty($galleriesOptions['GalleriesPagesNoFollow'])){
	$metaRobots = '<meta name="robots" content="noindex">'."\r\n";
}elseif(!empty($galleriesOptions['GalleriesPagesNoIndex']) && empty($galleriesOptions['GalleriesPagesNoFollow'])){
	$metaRobots = '<meta name="robots" content="nofollow">'."\r\n";
}

$isNavigationStateRequest = (function_exists('cgl_has_navigation_query_args')) ? cgl_has_navigation_query_args() : false;
if($isNavigationStateRequest){
	$robotsContent = 'noindex, follow';
	if(strpos($metaRobots, 'nofollow') !== false){
		$robotsContent = 'noindex, nofollow';
	}
	$metaRobots = '<meta name="robots" content="'.$robotsContent.'">'."\r\n";
}

$HeaderWpPageParent = '';
if(!empty($isCgParentPage)){
    if(!empty($options['visual']['HeaderWpPageParent'])){// only json option, might be not set if options were never saved before
        $HeaderWpPageParent = contest_gal1ery_convert_for_html_header_output($options['visual']['HeaderWpPageParent']);
    }
}


$HeaderWpPageGalleries = '';
if(!empty($isCGalleries)){
    if(!empty($galleriesOptions['HeaderWpPageGalleries'])){// only json option, might be not set if options were never saved before
        $HeaderWpPageGalleries = contest_gal1ery_convert_for_html_header_output($galleriesOptions['HeaderWpPageGalleries']);
    }
}

$isEntryLandingPage = (!empty($cgEntryId) && !empty($rowObject));
$isGalleryLandingPage = (!empty($isCgParentPage) && !empty($cgGalleryID));
$isGalleriesLandingPage = !empty($isCGalleries);
$canonicalShortCodeType = $shortCodeType;
if(!empty($isGalleriesLandingPage) && function_exists('cgl_get_galleries_shortcode_name_from_post')){
	$detectedGalleriesShortCodeType = cgl_get_galleries_shortcode_name_from_post($post);
	if(!empty($detectedGalleriesShortCodeType)){
		$canonicalShortCodeType = $detectedGalleriesShortCodeType;
	}
}
if(function_exists('cgl_get_resolved_canonical_url')){
	$resolvedCanonicalUrl = cgl_get_resolved_canonical_url($permalink, $canonicalShortCodeType, !empty($isGalleriesLandingPage), $post);
	if(!empty($resolvedCanonicalUrl)){
		$canonicalUrl = $resolvedCanonicalUrl;
	}
}

$resolvedTitle = cg1l_landing_clean_text($post->post_title);
if($resolvedTitle === ''){
	$resolvedTitle = cg1l_landing_clean_text($blogname);
}

$resolvedDescription = cg1l_landing_get_post_description_fallback($post);
if($resolvedDescription === ''){
	$resolvedDescription = cg1l_landing_clean_text(get_option('blogdescription'));
}

$mediaData = [];
$jsonLdBlocks = '';
$galleryItems = [];
$overviewItems = [];
$breadcrumbs = [];
$overviewPageId = cg1l_landing_get_galleries_page_id($shortCodeType);
$overviewPageUrl = (!empty($overviewPageId)) ? get_permalink($overviewPageId) : '';
$overviewPageTitle = (!empty($overviewPageId)) ? cg1l_landing_clean_text(get_the_title($overviewPageId)) : '';

if($isEntryLandingPage){
	$entryInfoData = cg1l_landing_get_entry_info_data($cgGalleryID, $cgId);
	$fallbackTitle = cg1l_landing_get_first_info_value($entryInfoData, [
		(!empty($options['visual']['IsForWpPageTitleID']) ? $options['visual']['IsForWpPageTitleID'] : 0),
		(!empty($options['visual']['Field1IdGalleryView']) ? $options['visual']['Field1IdGalleryView'] : 0),
		(!empty($options['visual']['SubTitle']) ? $options['visual']['SubTitle'] : 0),
	]);
	$fallbackDescription = cg1l_landing_get_first_info_value($entryInfoData, [
		(!empty($options['visual']['IsForWpPageDescriptionID']) ? $options['visual']['IsForWpPageDescriptionID'] : 0),
		(!empty($options['visual']['ThirdTitle']) ? $options['visual']['ThirdTitle'] : 0),
		(!empty($options['visual']['Field2IdGalleryView']) ? $options['visual']['Field2IdGalleryView'] : 0),
		(!empty($options['visual']['SubTitle']) ? $options['visual']['SubTitle'] : 0),
		(!empty($options['visual']['Field1IdGalleryView']) ? $options['visual']['Field1IdGalleryView'] : 0),
	]);

	if($WpPageTitle !== ''){
		$resolvedTitle = cg1l_landing_clean_text($WpPageTitle);
	}elseif($fallbackTitle !== ''){
		$resolvedTitle = $fallbackTitle;
	}

	if($WpPageDescription !== ''){
		$resolvedDescription = cg1l_landing_clean_text($WpPageDescription);
	}elseif($fallbackDescription !== ''){
		$resolvedDescription = $fallbackDescription;
	}

	if($shortCodeType === 'cg_gallery_ecommerce' && !empty($rowObject->EcommerceEntry) && $resolvedDescription === cg1l_landing_clean_text(get_option('blogdescription'))){
		$ecommerceFallbackRow = cg1l_landing_get_entry_ecommerce_row($rowObject->EcommerceEntry);
		if(!empty($ecommerceFallbackRow->SaleDescription)){
			$resolvedDescription = cg1l_landing_clean_text($ecommerceFallbackRow->SaleDescription);
		}
	}

	$mediaData = cg1l_landing_get_entry_media_data($rowObject, $tablename_ecommerce_entries);

	$isAllowedForRichSchema = true;
	if($shortCodeType === 'cg_gallery_user' && !is_user_logged_in()){
		$isAllowedForRichSchema = false;
	}
	$intervalConf = (!empty($cgGalleryID) && !empty($options)) ? cg_shortcode_interval_check($cgGalleryID, $options, $shortCodeType) : ['shortcodeIsActive' => true];
	if(empty($intervalConf['shortcodeIsActive']) || empty($rowObject->Active)){
		$isAllowedForRichSchema = false;
	}

	$breadcrumbs[] = [
		'@type' => 'ListItem',
		'position' => 1,
		'name' => cg1l_landing_clean_text($blogname),
		'item' => home_url('/'),
	];
	$breadcrumbPosition = 2;
	if(!empty($overviewPageUrl) && !empty($overviewPageTitle)){
		$breadcrumbs[] = [
			'@type' => 'ListItem',
			'position' => $breadcrumbPosition,
			'name' => $overviewPageTitle,
			'item' => $overviewPageUrl,
		];
		$breadcrumbPosition++;
	}
	if(!empty($cgWpPageParent) && empty($isCgParentPage)){
		$galleryTitle = cg1l_landing_clean_text(get_the_title($cgWpPageParent));
		if($galleryTitle !== ''){
			$breadcrumbs[] = [
				'@type' => 'ListItem',
				'position' => $breadcrumbPosition,
				'name' => $galleryTitle,
				'item' => get_permalink($cgWpPageParent),
			];
			$breadcrumbPosition++;
		}
	}
	$breadcrumbs[] = [
		'@type' => 'ListItem',
		'position' => $breadcrumbPosition,
		'name' => $resolvedTitle,
		'item' => $permalink,
	];

	$pageSchema = [
		'@type' => 'WebPage',
		'name' => $resolvedTitle,
		'description' => $resolvedDescription,
		'url' => $permalink,
	];

	if($isAllowedForRichSchema){
		$mainEntity = [
			'@type' => $mediaData['schemaType'],
			'name' => $resolvedTitle,
			'url' => $permalink,
		];

		if($resolvedDescription !== ''){
			$mainEntity['description'] = $resolvedDescription;
		}
		if(!empty($mediaData['contentUrl'])){
			$mainEntity['contentUrl'] = $mediaData['contentUrl'];
		}
		if(!empty($mediaData['thumbnailUrl'])){
			$mainEntity['thumbnailUrl'] = $mediaData['thumbnailUrl'];
		}
		if(!empty($mediaData['ogImage'])){
			$mainEntity['image'] = $mediaData['ogImage'];
		}
		if(!empty($mediaData['encodingFormat'])){
			$mainEntity['encodingFormat'] = $mediaData['encodingFormat'];
		}
		if(!empty($mediaData['width'])){
			$mainEntity['width'] = $mediaData['width'];
		}
		if(!empty($mediaData['height'])){
			$mainEntity['height'] = $mediaData['height'];
		}
		if(!empty($post->post_date_gmt)){
			$mainEntity['datePublished'] = mysql2date('c', $post->post_date_gmt, false);
		}
		if(!empty($post->post_modified_gmt)){
			$mainEntity['dateModified'] = mysql2date('c', $post->post_modified_gmt, false);
		}

		if($shortCodeType === 'cg_gallery_ecommerce' && !empty($rowObject->EcommerceEntry)){
			$ecommerceRow = cg1l_landing_get_entry_ecommerce_row($rowObject->EcommerceEntry);
			if(!empty($ecommerceRow)){
				$currencyShort = '';
				if(function_exists('cg_get_ecommerce_options')){
					$ecommerceOptions = cg_get_ecommerce_options();
					if(!empty($ecommerceOptions->CurrencyShort)){
						$currencyShort = sanitize_text_field($ecommerceOptions->CurrencyShort);
					}
				}
				if(empty($currencyShort)){
					$tablename_ecommerce_options = $wpdb->prefix . 'contest_gal1ery_ecommerce_options';
					$currencyShort = sanitize_text_field($wpdb->get_var("SELECT CurrencyShort FROM $tablename_ecommerce_options WHERE GeneralID = '1'"));
				}

				$productSchema = [
					'@type' => 'Product',
					'name' => (!empty($ecommerceRow->SaleTitle) ? cg1l_landing_clean_text($ecommerceRow->SaleTitle) : $resolvedTitle),
					'url' => $permalink,
					'sku' => 'cg-entry-'.$rowObject->id,
				];
				$productDescription = (!empty($ecommerceRow->SaleDescription)) ? cg1l_landing_clean_text($ecommerceRow->SaleDescription) : $resolvedDescription;
				if($productDescription !== ''){
					$productSchema['description'] = $productDescription;
				}
				if(!empty($mediaData['ogImage'])){
					$productSchema['image'] = $mediaData['ogImage'];
				}
				if(!empty($currencyShort)){
					$productSchema['offers'] = [
						'@type' => 'Offer',
						'price' => number_format((float)$ecommerceRow->Price, 2, '.', ''),
						'priceCurrency' => $currencyShort,
						'availability' => 'https://schema.org/InStock',
						'url' => $permalink,
					];
				}
				$mainEntity = $productSchema;
			}
		}

		$pageSchema['mainEntity'] = $mainEntity;
	}

	$jsonLdBlocks = cg1l_landing_get_json_ld_for_page([
		'pageSchema' => $pageSchema,
		'breadcrumbs' => $breadcrumbs,
	]);
}elseif($isGalleryLandingPage){
	if($resolvedTitle === ''){
		$resolvedTitle = cg1l_landing_clean_text(get_the_title($postId));
	}

	$galleryItems = cg1l_landing_get_gallery_items($cgGalleryID, $shortCodeType, $options, 24);

	$breadcrumbs[] = [
		'@type' => 'ListItem',
		'position' => 1,
		'name' => cg1l_landing_clean_text($blogname),
		'item' => home_url('/'),
	];
	$breadcrumbPosition = 2;
	if(!empty($overviewPageUrl) && !empty($overviewPageTitle)){
		$breadcrumbs[] = [
			'@type' => 'ListItem',
			'position' => $breadcrumbPosition,
			'name' => $overviewPageTitle,
			'item' => $overviewPageUrl,
		];
		$breadcrumbPosition++;
	}
	$breadcrumbs[] = [
		'@type' => 'ListItem',
		'position' => $breadcrumbPosition,
		'name' => $resolvedTitle,
		'item' => $permalink,
	];

	$pageSchema = [
		'@type' => 'CollectionPage',
		'name' => $resolvedTitle,
		'description' => $resolvedDescription,
		'url' => $permalink,
	];

	if(!empty($galleryItems)){
		$pageSchema['mainEntity'] = [
			'@type' => 'ItemList',
			'numberOfItems' => count($galleryItems),
			'itemListElement' => $galleryItems,
		];
	}

	$jsonLdBlocks = cg1l_landing_get_json_ld_for_page([
		'pageSchema' => $pageSchema,
		'breadcrumbs' => $breadcrumbs,
	]);
}elseif($isGalleriesLandingPage){
	if($resolvedTitle === ''){
		$resolvedTitle = cg1l_landing_clean_text(get_the_title($postId));
	}

	$overviewItems = cg1l_landing_get_overview_items($shortCodeType, 24);
	$breadcrumbs[] = [
		'@type' => 'ListItem',
		'position' => 1,
		'name' => cg1l_landing_clean_text($blogname),
		'item' => home_url('/'),
	];
	$breadcrumbs[] = [
		'@type' => 'ListItem',
		'position' => 2,
		'name' => $resolvedTitle,
		'item' => $permalink,
	];

	$pageSchema = [
		'@type' => 'CollectionPage',
		'name' => $resolvedTitle,
		'description' => $resolvedDescription,
		'url' => $permalink,
	];

	if(!empty($overviewItems)){
		$pageSchema['mainEntity'] = [
			'@type' => 'ItemList',
			'numberOfItems' => count($overviewItems),
			'itemListElement' => $overviewItems,
		];
	}

	$jsonLdBlocks = cg1l_landing_get_json_ld_for_page([
		'pageSchema' => $pageSchema,
		'breadcrumbs' => $breadcrumbs,
	]);
}

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html($resolvedTitle); ?></title>
    <meta name="title" content="<?php echo esc_attr($resolvedTitle); ?>">
    <meta name="description" content="<?php echo esc_attr($resolvedDescription); ?>">
    <link rel="canonical" href="<?php echo esc_url($canonicalUrl); ?>">
    <?php

        echo $metaRobots;
        echo $HeaderWpPageGalleries;
        echo $HeaderWpPageParent;
        echo $HeaderWpPageEntry;

    if(class_exists( 'QM_Plugin' )){
        ?>
        <script type='text/javascript' src='<?php echo get_bloginfo('wpurl'); ?>/wp-includes/js/jquery/jquery.min.js?ver=3.6.1' id='jquery-core-js'></script>
        <script type='text/javascript' src='<?php echo get_bloginfo('wpurl'); ?>/wp-includes/js/jquery/jquery-migrate.min.js?ver=3.3.2' id='jquery-migrate-js'></script>
        <link rel='stylesheet' id='query-monitor-css' href='<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/query-monitor/assets/query-monitor.css?ver=1673467028' type='text/css' media='all' />
        <script type='text/javascript' src='<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/query-monitor/assets/query-monitor.js?ver=1673467028' id='query-monitor-js'></script>
        <?php
    }
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    wp_enqueue_script('jquery');// will appear in footer so or so, because of wp_footer

	$cgVersionForScripts = cg_get_version_for_scripts();
	$cgPluginBaseFile = dirname(__DIR__).'/index.php';
	$cgEntryLandingPageStyleUrl = plugins_url('/v10/v10-css/cg_entry_landing_page_style.css', $cgPluginBaseFile);
	$cgGalleryStyleUrl = plugins_url('/v10/v10-css-min/cg_gallery.min.css', $cgPluginBaseFile);
	$cgGalleryLoadersStyleUrl = plugins_url('/v10/v10-css/frontend/style_loaders.css', $cgPluginBaseFile);

	echo '<link rel="stylesheet" id="cg_entry_landing_page_style-css" href="'.esc_url(add_query_arg('ver', $cgVersionForScripts, $cgEntryLandingPageStyleUrl)).'" media="all" />'."\r\n";
	echo '<link rel="stylesheet" id="cg_v10_css_cg_gallery-css" href="'.esc_url(add_query_arg('ver', $cgVersionForScripts, $cgGalleryStyleUrl)).'" media="all" />'."\r\n";
	echo '<link rel="stylesheet" id="cg_v10_css_loaders_cg_gallery-css" href="'.esc_url(add_query_arg('ver', $cgVersionForScripts, $cgGalleryLoadersStyleUrl)).'" media="all" />'."\r\n";


    echo '<meta property="og:url" content="'.$permalink.'">'."\r\n";
    echo '<meta property="og:site_name" content="'.$blogname.'">'."\r\n";
    echo '<meta property="og:title" content="'.esc_attr($resolvedTitle).'">'."\r\n";
    echo '<meta property="og:description" content="'.esc_attr($resolvedDescription).'">'."\r\n";

    if(!empty($mediaData['ogImage'])){
	    echo '<meta property="og:image" content="'.esc_url($mediaData['ogImage']).'">'."\r\n";
    }
    if($isEntryLandingPage && !empty($mediaData['schemaType']) && $mediaData['schemaType'] === 'VideoObject' && !empty($mediaData['contentUrl'])){
	    echo '<meta property="og:video" content="'.esc_url($mediaData['contentUrl']).'?t=0.001">'."\r\n";
	    if(!empty($mediaData['encodingFormat'])){
		    echo '<meta property="og:video:type" content="'.esc_attr($mediaData['encodingFormat']).'">'."\r\n";
	    }
	    if(!empty($mediaData['width'])){
		    echo '<meta property="og:width" content="'.absint($mediaData['width']).'">'."\r\n";
	    }
	    if(!empty($mediaData['height'])){
		    echo '<meta property="og:height" content="'.absint($mediaData['height']).'">'."\r\n";
	    }
    }
    echo $jsonLdBlocks;

    if(!empty($additionalCss)){
        echo "<style>";
            echo cg_stripslashes_recursively(str_replace("&nbsp;", '', htmlentities($additionalCss)));
        echo "</style>";
    }

if(!empty($cgEntryId)){
	echo "<style>";
        echo "#mainCGdivEntryPageContainer{\r\n";
        echo "max-width: 1000px;\r\n";
        echo "max-width: 1000px;\r\n";
        echo "margin-left: auto;\r\n";
        echo "margin-right: auto;\r\n";
        echo "}\r\n";
	echo "</style>";
}

    echo "<style>";
    echo "#wp-admin-bar-site-editor {display: none !important;}";
    echo "</style>";

    $is_loggedn_in = 'false';
    if(is_user_logged_in()){
	    $is_loggedn_in = 'true';
    }

    ?>
</head>
<body id="body" data-cg-is-logged-in=<?php echo json_encode($is_loggedn_in); ?> >
	<div id="mainCGdivEntryPageContainer">
	    <?php
	    //the_title(); // for later integration
	    the_content();
	    wp_dequeue_style('cg_entry_landing_page_style');
	    wp_dequeue_style('cg_v10_css_cg_gallery');
	    wp_dequeue_style('cg_v10_css_loaders_cg_gallery');
	    // https://stackoverflow.com/questions/71772319/stop-wordpress-php-javascript-function-adding-skip-link-code
	    remove_action( 'wp_footer', 'the_block_template_skip_link' );// other deprecated the_block_template_skip_link shown always
	    wp_footer();
	    ?>
</div>
</body>
</html>
