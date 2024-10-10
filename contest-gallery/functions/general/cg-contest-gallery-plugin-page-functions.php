<?php

if(!function_exists('cg_post_type_page_galleries_slug_array')){
	function cg_post_type_page_galleries_slug_array($type=''){

		$postTitle = $type;
		$shortcodeType = $type;
		$mimeType = '';
		if(!empty($type)){
			$postTitle = ' '.ucfirst($type);
			$shortcodeType = '_'.$type;
			$mimeType = $type.'-';
			if($type=='no-voting'){
				$postTitle = ' No Voting';
				$shortcodeType = '_no_voting';
				$mimeType = 'no-voting-';
			}
		}

		// new logic for new created galleries
		$array = [
			'post_title'=>'Contest Galleries'.$postTitle,
			'post_type'=>"page",
			'post_content'=>"<!-- wp:shortcode -->"."\r\n".
			                "<!--This is a comment: cg_galleries... shortcode is required to display Contest Gallery on a Contest Gallery Custom Post Type page. You can place your own content before and after this shortcode, whatever you like. You can place cg_galleries... shortcode also on any other of your pages.-->"."\r\n".
			                "[cg_galleries$shortcodeType]"."\r\n".
			                "<!-- /wp:shortcode -->",
			'post_mime_type'=>'contest-gallery-plugin-page-galleries-'.$mimeType.'slug',
			'post_status'=>'publish',
		];

		return $array;
	}
}

if(!function_exists('cg_post_type_parent_gallery_array')){
	function cg_post_type_parent_gallery_array($GalleryID, $type=''){

		$postTitle = $type;
		$shortcodeType = $type;
		if(!empty($type)){
			$postTitle = ' '.ucfirst($type);
			$shortcodeType = '_'.$type;
			if($type=='no-voting'){
				$postTitle = ' No Voting';
				$shortcodeType = '_no_voting';
			}
		}

        // old logic for old galleries with ID in title
		$array = [
			'post_title'=>'Contest Gallery ID '.$GalleryID.$postTitle,
			'post_type'=>"contest-gallery",
			'post_content'=>"<!-- wp:shortcode -->"."\r\n".
			                "<!--This is a comment: cg_gallery... shortcode is required to display Contest Gallery on a Contest Gallery Custom Post Type page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode also on any other of your pages.-->"."\r\n".
			                "[cg_gallery$shortcodeType id=\"$GalleryID\"]"."\r\n".
			                "<!-- /wp:shortcode -->",
			'post_mime_type'=>'contest-gallery-plugin-page',
			'post_status'=>'publish',
		];

		return $array;
	}
}

if(!function_exists('cg_post_type_parent_galleries_array')){
	function cg_post_type_parent_galleries_array($GalleryID,$type=''){
		$shortcodeType = $type;
		$postType = '';
		$titleType = '';
		if($type){
			$shortcodeType = '_'.$type;
			$postType = '-'.$type;
			$titleType = ucfirst($type).' ';
			if($type=='no-voting'){
				$shortcodeType = '_no_voting';
				$titleType = 'No Voting ';
			}
		}
		// new logic for new created galleries, post_title no suffix anymore
		$array = [
			//'post_title'=>'Contest Gallery ID '.$GalleryID,
			'post_title'=>"Contest Gallery $titleType".$GalleryID,// without "ID" part anymore in the URL
			'post_type'=>"contest-g".$postType,
			'post_content'=>"<!-- wp:shortcode -->"."\r\n".
			                "<!--This is a comment: cg_galley... shortcode is required to display Contest Gallery on a Contest Gallery Custom Post Type page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode also on any other of your pages.-->"."\r\n".
			                "[cg_gallery$shortcodeType id=\"$GalleryID\"]"."\r\n".
			                "<!-- /wp:shortcode -->",
			'post_mime_type'=>'contest-gallery-plugin-page',
			'post_status'=>'publish',
		];
		return $array;
	}
}

if(!function_exists('cg_create_wp_pages')){
	function cg_create_wp_pages($GalleryID,$nextId,$post_title,$options,$cgVersion){

		global $wpdb;
		$tablename = $wpdb->prefix . "contest_gal1ery";

		// cg_gallery shortcode
		$shortcodeType = '';
		$postType = 'contest-gallery';// before 24 always contest-gallery
		if(intval($cgVersion)>=24){
			$postType = 'contest-g';
		}

		$shortcodeType = '';

		$array = [
			'post_title'=> $post_title,
			'post_type'=>$postType,
			'post_content'=>"<!-- wp:shortcode -->"."\r\n".
			                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
			                "[cg_gallery$shortcodeType id=\"$GalleryID\" entry_id=\"$nextId\"]"."\r\n".
			                "<!-- /wp:shortcode -->",
			'post_mime_type'=>'contest-gallery-plugin-page',
			'post_status'=>'publish',
			'post_parent'=>$options->WpPageParent
		];
		$WpPage = wp_insert_post($array);

		// cg_gallery_user shortcode
		$shortcodeType = '_user';
		$postType = 'contest-gallery';// before 24 always contest-gallery
		if(intval($cgVersion)>=24){
			$postType = 'contest-g-user';
		}

		$array = [
			'post_title'=> $post_title,
			'post_type'=>$postType,
			'post_content'=>"<!-- wp:shortcode -->"."\r\n".
			                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
			                "[cg_gallery$shortcodeType id=\"$GalleryID\" entry_id=\"$nextId\"]"."\r\n".
			                "<!-- /wp:shortcode -->",
			'post_mime_type'=>'contest-gallery-plugin-page',
			'post_status'=>'publish',
			'post_parent'=>$options->WpPageParentUser
		];
		$WpPageUser = wp_insert_post($array);

		// cg_gallery_no_voting shortcode
		$shortcodeType = '_no_voting';
		$postType = 'contest-gallery';// before 24 always contest-gallery
		if(intval($cgVersion)>=24){
			$postType = 'contest-g-no-voting';
		}

		$array = [
			'post_title'=> $post_title,
			'post_type'=>$postType,
			'post_content'=>"<!-- wp:shortcode -->"."\r\n".
			                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
			                "[cg_gallery$shortcodeType id=\"$GalleryID\" entry_id=\"$nextId\"]"."\r\n".
			                "<!-- /wp:shortcode -->",
			'post_mime_type'=>'contest-gallery-plugin-page',
			'post_status'=>'publish',
			'post_parent'=>$options->WpPageParentNoVoting
		];
		$WpPageNoVoting = wp_insert_post($array);

		// cg_gallery_winner shortcode
		$shortcodeType = '_winner';
		$postType = 'contest-gallery';// before 24 always contest-gallery
		if(intval($cgVersion)>=24){
			$postType = 'contest-g-winner';
		}

		$array = [
			'post_title'=> $post_title,
			'post_type'=>$postType,
			'post_content'=>"<!-- wp:shortcode -->"."\r\n".
			                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
			                "[cg_gallery$shortcodeType id=\"$GalleryID\" entry_id=\"$nextId\"]"."\r\n".
			                "<!-- /wp:shortcode -->",
			'post_mime_type'=>'contest-gallery-plugin-page',
			'post_status'=>'publish',
			'post_parent'=>$options->WpPageParentWinner
		];
		$WpPageWinner = wp_insert_post($array);

		// cg_gallery_ecommerce shortcode
		$WpPageEcommerce = 0;

		if(intval($cgVersion)>=22){
			$shortcodeType = '_ecommerce';
			$postType = 'contest-gallery';// before 24 always contest-gallery
			if(intval($cgVersion)>=24){
				$postType = 'contest-g-ecommerce';
			}
			$array = [
				'post_title'=> $post_title,
				'post_type'=>$postType,
				'post_content'=>"<!-- wp:shortcode -->"."\r\n".
				                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
				                "[cg_gallery$shortcodeType id=\"$GalleryID\" entry_id=\"$nextId\"]"."\r\n".
				                "<!-- /wp:shortcode -->",
				'post_mime_type'=>'contest-gallery-plugin-page',
				'post_status'=>'publish',
				'post_parent'=>$options->WpPageParentEcommerce
			];
			$WpPageEcommerce = wp_insert_post($array);
		}

		$wpdb->update(
			"$tablename",
			array('WpPage' => $WpPage,'WpPageUser' => $WpPageUser,'WpPageNoVoting' => $WpPageNoVoting,'WpPageWinner' => $WpPageWinner,'WpPageEcommerce' => $WpPageEcommerce),
			array('id' => $nextId),
			array('%d','%d','%d','%d','%d'),
			array('%d')
		);

		return [
			'WpPage' => $WpPage,
			'WpPageUser' => $WpPageUser,
			'WpPageNoVoting' => $WpPageNoVoting,
			'WpPageWinner' => $WpPageWinner,
			'WpPageEcommerce' => $WpPageEcommerce,
		];

	}
}

if(!function_exists('cg_create_cg_galleries_insert_array')){
	function cg_create_cg_galleries_insert_array($url){
		$array = [
			'post_title'=>'Contest Gallery Main ecommerce',
			'post_type'=>'contest-gallery',
			'post_content'=>"<!-- wp:shortcode -->"."\r\n".
			                "<!--This is a comment: cg_galley... shortcode is required to display Contest Gallery on a Contest Gallery Custom Post Type page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode also on any other of your pages.-->"."\r\n".
			                "[cg_gallery_ecommerce id=\"$nextIDgallery\"]"."\r\n".
			                "<!-- /wp:shortcode -->",
			'post_status'=>'publish',
		];
		return $array;
	}
}
