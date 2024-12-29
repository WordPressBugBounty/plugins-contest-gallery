<?php

if(!function_exists('cg_get_24_version_values')){
    function cg_get_24_version_values(){

	    $TextBeforeGalleries = contest_gal1ery_htmlentities_and_preg_replace('<p  style="text-align: center;">This is <strong>"General text on entry landing page before an activated entry"</strong>, is only visible on an entry landing page of an entry and can be configured for every cg_gallery shortcode type in <strong>"Edit options" >>> "Entry view"</strong><br><strong>NOTE:</strong>the cg_gallery... shortcode with entry_id added to this page can be placed on any other of your pages<br><strong>NOTE:</strong> social media share icons can be configured in <strong>"Edit options" >>> "Entry view"</strong><br><strong>NOTE:</strong> "Back to gallery button custom URL" can be configured in <strong>"Edit options" >>> "Entry view"</strong></p>');

	    $TextAfterGalleries = contest_gal1ery_htmlentities_and_preg_replace('<p  style="text-align: center;">This is <strong>"General text on entry landing page after an activated entry"</strong>, is only visible on an entry landing page of an entry and can be configured for every cg_gallery shortcode type in <strong>"Edit options" >>> "Entry view"</strong><br><strong>NOTE:</strong>the cg_gallery... shortcode with entry_id added to this page can be placed on any other of your pages<br><strong>NOTE:</strong> social media share icons can be configured in <strong>"Edit options" >>> "Entry view"</strong><br><strong>NOTE:</strong> "Back to gallery button custom URL" can be configured in <strong>"Edit options" >>> "Entry view"</strong></p>');

		$options = [
			'TextBeforeGalleriesOnLandingPage' => $TextBeforeGalleries,
			'TextAfterGalleriesOnLandingPage' => $TextAfterGalleries,
			'WidthThumb' => 300,
			'HeightThumb' => 225,
			'DistancePics' => 15,
			'DistancePicsV' => 8,
			'PreviewLastAdded' => 1,
			'PreviewHighestRated' => 0,
			'PreviewMostCommented' => 0,
			'PicsPerSite' => 20,
			'ShowGalleryNameAsTitle' => 1,
			'GalleriesPageRedirectURL' => '',
			'BorderRadius' => 1,
			'FeControlsStyle' => 'white'
		];

		return [
			'g' => $options, // g = 'general
			'u' => $options,
			'nv' => $options,
			'w' => $options,
			'ec' => $options
		];

    }
}

if(!function_exists('cg_get_empty_entry_values')){
    function cg_get_empty_entry_values(){
		return [
			'Active' => 1,
			'Category' => 0,
			'CountC' => 0,
			'CountR' => 0,
			'CountR1' => 0,
			'CountR2' => 0,
			'CountR3' => 0,
			'CountR4' => 0,
			'CountR5' => 0,
			'CountR6' => 0,
			'CountR7' => 0,
			'CountR8' => 0,
			'CountR9' => 0,
			'CountR10' => 0,
			'CountS' => 0,
			'EcommerceEntry' => 0,
			'Exif' => "",
			'GalleryID' => 0,
			'GalleryName' => '',
			'Height' => 0,
			'ImgType' => '',
			'Informed' => 0,
			'MultipleFilesParsed' => 0,
			'MultipleFilesParsedRealIdSourceSet' => 0,
			'NamePic' => '',
			'PositionNumber' => 0,
			'Rating' => 0,
			'Timestamp' => 0,
			'Width' => 0,
			'Winner' => 0,
			'WpPage' => 0,
			'WpPageEcommerce' => 0,
			'WpPageNoVoting' => 0,
			'WpPageUser' => 0,
			'WpPageWinner' => 0,
			'WpUpload' => 0,
			'addCountR1' => 0,
			'addCountR2' => 0,
			'addCountR3' => 0,
			'addCountR4' => 0,
			'addCountR5' => 0,
			'addCountR6' => 0,
			'addCountR7' => 0,
			'addCountR8' => 0,
			'addCountR9' => 0,
			'addCountR10' => 0,
			'addCountS' => 0,
			'full' => '',
			'gidToShow' => 0,
			'guid' => '',
			'id' => 0,
			'imageObject' => '',
			'imgSrcOriginalHeight' => 0,
			'imgSrcOriginalWidth' => 0,
			'large' => '',
			'medium' => '',
			'post_alt' => '',
			'post_caption' => '',
			'post_content' => '',
			'post_date' => '',
			'post_excerpt' => '',
			'post_mime_type' => '',
			'post_name' => '',
			'post_title' => '',
			'rSource' => 0,
			'rThumb' => 0,
			'rowid' => 0,
			'selectedOrder' => 0,
			'thumbnail' => '',
			'type' => '',
		];

    }
}



?>