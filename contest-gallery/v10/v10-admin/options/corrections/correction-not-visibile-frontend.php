<?php

$correctStatusText6 = 'Repair';
$correctStatusClass6 = '';
if(isset($_POST['action_correct_not_visible_for_frontend'])){

	// check if parent pages exists and required before
	if(floatval($options->Version)>=21){

		if(floatval($options->Version)>=24){
			cg_create_slug_name_galleries_posts_if_required();
		}

		$jsonFile = $upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/".$GalleryID."-options.json";
		$optionsArray = json_decode(file_get_contents($jsonFile),true);

		$WpPageParent = get_post( $options->WpPageParent );
		$WpPageParentUser = get_post( $options->WpPageParentUser );
		$WpPageParentNoVoting = get_post( $options->WpPageParentNoVoting );
		$WpPageParentWinner = get_post( $options->WpPageParentWinner );
		$WpPageParentEcommerce = get_post( $options->WpPageParentEcommerce );

		$tag = get_term_by('slug', ' contest-gallery-plugin-tag','post_tag');
		if(empty($tag)){
			$tag = cg_create_contest_gallery_plugin_tag();
			$term_id = $tag['term_id'];
		}else{
			$term_id = $tag->term_id;
		}

		$hasParentPageRepaired = false;

		if(empty($WpPageParent)){
			$hasParentPageRepaired = true;

			// cg_gallery shortcode
			if(intval($options->Version)>=24){
				$array = cg_post_type_parent_galleries_array($options->id);
			}else{
				$array = cg_post_type_parent_gallery_array($options->id);
			}

			$WpPageParent = wp_insert_post($array);
			cg_insert_into_contest_gal1ery_wp_pages($WpPageParent);
			$options->WpPageParent = $WpPageParent;

			$wpdb->update(
				"$tablenameOptions",
				array('WpPageParent' => $WpPageParent),
				array('id' => $options->id),
				array('%d'),
				array('%d')
			);

			$picsSQL = $wpdb->get_results( "SELECT DISTINCT id, WpPage FROM $tablename WHERE GalleryID = '$GalleryID'");

			$querySETrowForPostIds = 'UPDATE ' . $table_posts . ' SET post_parent = CASE ID ';
			$querySETaddRowForPostIds = ' ELSE post_parent END WHERE ID IN (';
			$queryArgsArray = [];
			$queryAddArgsArray = [];
			$queryArgsCounter = 0;

			foreach ($picsSQL as $rowObject) {
				$querySETrowForPostIds .= " WHEN %d THEN %d";
				$querySETaddRowForPostIds .= "%d,";
				$queryArgsArray[] = $rowObject->WpPage;
				$queryArgsArray[] = $WpPageParent;
				$queryAddArgsArray[] = $rowObject->WpPage;
				$queryArgsCounter++;
			}

			// ic = i counter
			for ($ic = 0;$ic<$queryArgsCounter;$ic++){
				$queryArgsArray[] =$queryAddArgsArray[$ic];
			}

			$querySETaddRowForPostIds = substr($querySETaddRowForPostIds,0,-1);
			$querySETaddRowForPostIds .= ")";

			$querySETrowForPostIds .= $querySETaddRowForPostIds;
			$wpdb->query($wpdb->prepare($querySETrowForPostIds,$queryArgsArray));

			$optionsArray['general']['WpPageParent'] = $WpPageParent;
			if(!empty($optionsArray[$GalleryID])){$optionsArray[$GalleryID]['general']['WpPageParent'] = $WpPageParent;}
			if(!empty($optionsArray[$GalleryID.'-u'])){$optionsArray[$GalleryID.'-u']['general']['WpPageParent'] = $WpPageParent;}
			if(!empty($optionsArray[$GalleryID.'-nv'])){$optionsArray[$GalleryID.'-nv']['general']['WpPageParent'] = $WpPageParent;}
			if(!empty($optionsArray[$GalleryID.'-w'])){$optionsArray[$GalleryID.'-w']['general']['WpPageParent'] = $WpPageParent;}
			if(intval($options->Version)>=22){
				if(!empty($optionsArray[$GalleryID.'-ec'])){$optionsArray[$GalleryID.'-ec']['general']['WpPageParent'] = $WpPageParent;}
			}

		}

		// cg_gallery_user shortcode
		if(empty($WpPageParentUser)){
			$hasParentPageRepaired = true;
			if(intval($options->Version)>=24){
				$array = cg_post_type_parent_galleries_array($options->id,'user');
			}else{
				$array = cg_post_type_parent_gallery_array($options->id,'user');
			}
			$WpPageParentUser = wp_insert_post($array);
			cg_insert_into_contest_gal1ery_wp_pages($WpPageParentUser);
			$options->WpPageParentUser = $WpPageParentUser;

			$wpdb->update(
				"$tablenameOptions",
				array('WpPageParentUser' => $WpPageParentUser),
				array('id' => $options->id),
				array('%d'),
				array('%d')
			);

			$picsSQL = $wpdb->get_results( "SELECT DISTINCT id, WpPageUser FROM $tablename WHERE GalleryID = '$GalleryID'");

			$querySETrowForPostIds = 'UPDATE ' . $table_posts . ' SET post_parent = CASE ID ';
			$querySETaddRowForPostIds = ' ELSE post_parent END WHERE ID IN (';
			$queryArgsArray = [];
			$queryAddArgsArray = [];
			$queryArgsCounter = 0;

			foreach ($picsSQL as $rowObject) {
				$querySETrowForPostIds .= " WHEN %d THEN %d";
				$querySETaddRowForPostIds .= "%d,";
				$queryArgsArray[] = $rowObject->WpPageUser;
				$queryArgsArray[] = $WpPageParentUser;
				$queryAddArgsArray[] = $rowObject->WpPageUser;
				$queryArgsCounter++;
			}

			// ic = i counter
			for ($ic = 0;$ic<$queryArgsCounter;$ic++){
				$queryArgsArray[] =$queryAddArgsArray[$ic];
			}

			$querySETaddRowForPostIds = substr($querySETaddRowForPostIds,0,-1);
			$querySETaddRowForPostIds .= ")";

			$querySETrowForPostIds .= $querySETaddRowForPostIds;
			$wpdb->query($wpdb->prepare($querySETrowForPostIds,$queryArgsArray));

			$optionsArray['general']['WpPageParentUser'] = $WpPageParentUser;
			if(!empty($optionsArray[$GalleryID])){$optionsArray[$GalleryID]['general']['WpPageParentUser'] = $WpPageParentUser;}
			if(!empty($optionsArray[$GalleryID.'-u'])){$optionsArray[$GalleryID.'-u']['general']['WpPageParentUser'] = $WpPageParentUser;}
			if(!empty($optionsArray[$GalleryID.'-nv'])){$optionsArray[$GalleryID.'-nv']['general']['WpPageParentUser'] = $WpPageParentUser;}
			if(!empty($optionsArray[$GalleryID.'-w'])){$optionsArray[$GalleryID.'-w']['general']['WpPageParentUser'] = $WpPageParentUser;}
			if(intval($options->Version)>=22){
				if(!empty($optionsArray[$GalleryID.'-ec'])){$optionsArray[$GalleryID.'-ec']['general']['WpPageParentUser'] = $WpPageParentUser;}
			}

		}

		// cg_gallery_no_voting shortcode
		if(empty($WpPageParentNoVoting)){
			$hasParentPageRepaired = true;
			if(intval($options->Version)>=24){
				$array = cg_post_type_parent_galleries_array($options->id,'no-voting');
			}else{
				$array = cg_post_type_parent_gallery_array($options->id,'no-voting');
			}

			$WpPageParentNoVoting = wp_insert_post($array);
			cg_insert_into_contest_gal1ery_wp_pages($WpPageParentNoVoting);
			$options->WpPageParentNoVoting = $WpPageParentNoVoting;

			$wpdb->update(
				"$tablenameOptions",
				array('WpPageParentNoVoting' => $WpPageParentNoVoting),
				array('id' => $options->id),
				array('%d'),
				array('%d')
			);

			$picsSQL = $wpdb->get_results( "SELECT DISTINCT id, WpPageNoVoting FROM $tablename WHERE GalleryID = '$GalleryID'");

			$querySETrowForPostIds = 'UPDATE ' . $table_posts . ' SET post_parent = CASE ID ';
			$querySETaddRowForPostIds = ' ELSE post_parent END WHERE ID IN (';
			$queryArgsArray = [];
			$queryAddArgsArray = [];
			$queryArgsCounter = 0;

			foreach ($picsSQL as $rowObject) {
				$querySETrowForPostIds .= " WHEN %d THEN %d";
				$querySETaddRowForPostIds .= "%d,";
				$queryArgsArray[] = $rowObject->WpPageNoVoting;
				$queryArgsArray[] = $WpPageParentNoVoting;
				$queryAddArgsArray[] = $rowObject->WpPageNoVoting;
				$queryArgsCounter++;
			}

			// ic = i counter
			for ($ic = 0;$ic<$queryArgsCounter;$ic++){
				$queryArgsArray[] =$queryAddArgsArray[$ic];
			}

			$querySETaddRowForPostIds = substr($querySETaddRowForPostIds,0,-1);
			$querySETaddRowForPostIds .= ")";

			$querySETrowForPostIds .= $querySETaddRowForPostIds;
			$wpdb->query($wpdb->prepare($querySETrowForPostIds,$queryArgsArray));

			$optionsArray['general']['WpPageParentNoVoting'] = $WpPageParentNoVoting;
			if(!empty($optionsArray[$GalleryID])){$optionsArray[$GalleryID]['general']['WpPageParentNoVoting'] = $WpPageParentNoVoting;}
			if(!empty($optionsArray[$GalleryID.'-u'])){$optionsArray[$GalleryID.'-u']['general']['WpPageParentNoVoting'] = $WpPageParentNoVoting;}
			if(!empty($optionsArray[$GalleryID.'-nv'])){$optionsArray[$GalleryID.'-nv']['general']['WpPageParentNoVoting'] = $WpPageParentNoVoting;}
			if(!empty($optionsArray[$GalleryID.'-w'])){$optionsArray[$GalleryID.'-w']['general']['WpPageParentNoVoting'] = $WpPageParentNoVoting;}
			if(intval($options->Version)>=22){
				if(!empty($optionsArray[$GalleryID.'-ec'])){$optionsArray[$GalleryID.'-ec']['general']['WpPageParentNoVoting'] = $WpPageParentNoVoting;}
			}

		}

		// cg_gallery_winner shortcode
		if(empty($WpPageParentWinner)){
			$hasParentPageRepaired = true;
			if(intval($options->Version)>=24){
				$array = cg_post_type_parent_galleries_array($options->id,'winner');
			}else{
				$array = cg_post_type_parent_gallery_array($options->id,'winner');
			}

			$WpPageParentWinner = wp_insert_post($array);
			cg_insert_into_contest_gal1ery_wp_pages($WpPageParentWinner);
			$options->WpPageParentWinner = $WpPageParentWinner;

			$wpdb->update(
				"$tablenameOptions",
				array('WpPageParentWinner' => $WpPageParentWinner),
				array('id' => $options->id),
				array('%d'),
				array('%d')
			);

			$picsSQL = $wpdb->get_results( "SELECT DISTINCT id, WpPageWinner FROM $tablename WHERE GalleryID = '$GalleryID'");

			$querySETrowForPostIds = 'UPDATE ' . $table_posts . ' SET post_parent = CASE ID ';
			$querySETaddRowForPostIds = ' ELSE post_parent END WHERE ID IN (';
			$queryArgsArray = [];
			$queryAddArgsArray = [];
			$queryArgsCounter = 0;

			foreach ($picsSQL as $rowObject) {
				$querySETrowForPostIds .= " WHEN %d THEN %d";
				$querySETaddRowForPostIds .= "%d,";
				$queryArgsArray[] = $rowObject->WpPageWinner;
				$queryArgsArray[] = $WpPageParentWinner;
				$queryAddArgsArray[] = $rowObject->WpPageWinner;
				$queryArgsCounter++;
			}

			// ic = i counter
			for ($ic = 0;$ic<$queryArgsCounter;$ic++){
				$queryArgsArray[] =$queryAddArgsArray[$ic];
			}

			$querySETaddRowForPostIds = substr($querySETaddRowForPostIds,0,-1);
			$querySETaddRowForPostIds .= ")";

			$querySETrowForPostIds .= $querySETaddRowForPostIds;
			$wpdb->query($wpdb->prepare($querySETrowForPostIds,$queryArgsArray));

			$optionsArray['general']['WpPageParentWinner'] = $WpPageParentWinner;
			if(!empty($optionsArray[$GalleryID])){$optionsArray[$GalleryID]['general']['WpPageParentWinner'] = $WpPageParentWinner;}
			if(!empty($optionsArray[$GalleryID.'-u'])){$optionsArray[$GalleryID.'-u']['general']['WpPageParentWinner'] = $WpPageParentWinner;}
			if(!empty($optionsArray[$GalleryID.'-nv'])){$optionsArray[$GalleryID.'-nv']['general']['WpPageParentWinner'] = $WpPageParentWinner;}
			if(!empty($optionsArray[$GalleryID.'-w'])){$optionsArray[$GalleryID.'-w']['general']['WpPageParentWinner'] = $WpPageParentWinner;}
			if(intval($options->Version)>=22){
				if(!empty($optionsArray[$GalleryID.'-ec'])){$optionsArray[$GalleryID.'-ec']['general']['WpPageParentWinner'] = $WpPageParentWinner;}
			}
		}

		if(intval($options->Version)>=22){
			// cg_gallery_ecommerce shortcode
			if(empty($WpPageParentEcommerce)){
				$hasParentPageRepaired = true;
				if(intval($options->Version)>=24){
					$array = cg_post_type_parent_galleries_array($options->id,'ecommerce');
				}else{
					$array = cg_post_type_parent_gallery_array($options->id,'ecommerce');
				}

				$WpPageParentEcommerce = wp_insert_post($array);
				cg_insert_into_contest_gal1ery_wp_pages($WpPageParentEcommerce);
				$options->WpPageParentEcommerce = $WpPageParentEcommerce;

				$wpdb->update(
					"$tablenameOptions",
					array('WpPageParentEcommerce' => $WpPageParentEcommerce),
					array('id' => $options->id),
					array('%d'),
					array('%d')
				);

     			$picsSQL = $wpdb->get_results( "SELECT DISTINCT id, WpPageEcommerce FROM $tablename WHERE GalleryID = '$GalleryID'");

				$querySETrowForPostIds = 'UPDATE ' . $table_posts . ' SET post_parent = CASE ID ';
				$querySETaddRowForPostIds = ' ELSE post_parent END WHERE ID IN (';
				$queryArgsArray = [];
				$queryAddArgsArray = [];
				$queryArgsCounter = 0;

				foreach ($picsSQL as $rowObject) {
					$querySETrowForPostIds .= " WHEN %d THEN %d";
					$querySETaddRowForPostIds .= "%d,";
					$queryArgsArray[] = $rowObject->WpPageEcommerce;
					$queryArgsArray[] = $WpPageParentEcommerce;
					$queryAddArgsArray[] = $rowObject->WpPageEcommerce;
					$queryArgsCounter++;
				}

				// ic = i counter
				for ($ic = 0;$ic<$queryArgsCounter;$ic++){
					$queryArgsArray[] =$queryAddArgsArray[$ic];
				}

				$querySETaddRowForPostIds = substr($querySETaddRowForPostIds,0,-1);
				$querySETaddRowForPostIds .= ")";

				$querySETrowForPostIds .= $querySETaddRowForPostIds;
				$wpdb->query($wpdb->prepare($querySETrowForPostIds,$queryArgsArray));

				$optionsArray['general']['WpPageParentEcommerce'] = $WpPageParentEcommerce;
				if(!empty($optionsArray[$GalleryID])){$optionsArray[$GalleryID]['general']['WpPageParentEcommerce'] = $WpPageParentEcommerce;}
				if(!empty($optionsArray[$GalleryID.'-u'])){$optionsArray[$GalleryID.'-u']['general']['WpPageParentEcommerce'] = $WpPageParentEcommerce;}
				if(!empty($optionsArray[$GalleryID.'-nv'])){$optionsArray[$GalleryID.'-nv']['general']['WpPageParentEcommerce'] = $WpPageParentEcommerce;}
				if(!empty($optionsArray[$GalleryID.'-w'])){$optionsArray[$GalleryID.'-w']['general']['WpPageParentEcommerce'] = $WpPageParentEcommerce;}
				if(intval($options->Version)>=22){
					if(!empty($optionsArray[$GalleryID.'-ec'])){$optionsArray[$GalleryID.'-ec']['general']['WpPageParentEcommerce'] = $WpPageParentEcommerce;}
				}
			}
		}



		if($hasParentPageRepaired){
			$jsonFile = $upload_dir['basedir']."/contest-gallery/gallery-id-".$GalleryID."/json/".$GalleryID."-options.json";
			file_put_contents($jsonFile,json_encode($optionsArray));
		}

		// check if all WpPages existing, if not then recreate
		$picsSQL = $wpdb->get_results( "SELECT DISTINCT id, WpPage, WpPageUser, WpPageNoVoting, WpPageWinner, WpPageEcommerce, NamePic, WpUpload FROM $tablename WHERE GalleryID='$GalleryID' GROUP BY id ORDER BY id DESC");

		$IsForWpPageTitleInputId = $wpdb->get_var("SELECT id FROM $tablename_form_input WHERE GalleryID = '$GalleryID' AND IsForWpPageTitle=1");

		$collect = '';
		$collectForPostTitle = '';
		$WpPagesArray = [];
		$WpUploadsArray = [];
		$WpPageTitlesArrayFromInputForm = [];
		foreach ($picsSQL as $rowObject){
			$id = $rowObject->id;
			$WpPage = $rowObject->WpPage;
			$WpPageUser = $rowObject->WpPageUser;
			$WpPageNoVoting = $rowObject->WpPageNoVoting;
			$WpPageWinner = $rowObject->WpPageWinner;
			$WpPageEcommerce = $rowObject->WpPageEcommerce;
			$WpUpload = $rowObject->WpUpload;
			$WpUploadsArray[$rowObject->id] = $WpUpload;
			if($collect == ''){
				$collect .= "ID = $WpPage OR ID = $WpPageUser OR ID = $WpPageNoVoting OR ID = $WpPageWinner  OR ID = $WpPageEcommerce";
			}else{
				$collect .= " OR ID = $WpPage OR ID = $WpPageUser OR ID = $WpPageNoVoting OR ID = $WpPageWinner OR ID = $WpPageEcommerce";
			}
			if($collectForPostTitle == ''){
				$collectForPostTitle .= "ID = $WpUpload";
			}else{
				$collectForPostTitle .= " OR ID = $WpUpload";
			}
			if(!empty($WpPage)){$WpPagesArray[$WpPage] = ['WpPage' => $rowObject->id];}
			if(!empty($WpPageUser)){$WpPagesArray[$WpPageUser] = ['WpPageUser' => $rowObject->id];}
			if(!empty($WpPageNoVoting)){$WpPagesArray[$WpPageNoVoting] = ['WpPageNoVoting' => $rowObject->id];}
			if(!empty($WpPageWinner)){$WpPagesArray[$WpPageWinner] = ['WpPageWinner' => $rowObject->id];}

			if(intval($options->Version)>=22){
				if(!empty($WpPageEcommerce)){$WpPagesArray[$WpPageEcommerce] = ['WpPageEcommerce' => $rowObject->id];}
			}

			if(!empty($IsForWpPageTitleInputId)){
				$ShortText = $wpdb->get_var("SELECT Short_Text FROM $tablename_entries WHERE pid = '$id' AND f_input_id=$IsForWpPageTitleInputId");
				if(!empty($ShortText)){
					$WpPageTitlesArrayFromInputForm[$id] = $ShortText;
				}
			}
		}
		$post_titles_array = [];
		$WpPostTitles = $wpdb->get_results( "SELECT DISTINCT ID, post_title FROM $table_posts WHERE ($collectForPostTitle)");
		foreach ($WpPostTitles as $WpPostTitle){
			$post_titles_array[$WpPostTitle->ID] = $WpPostTitle->post_title;
		}

		$WpPagesIDs = $wpdb->get_results( "SELECT DISTINCT ID FROM $table_posts WHERE ($collect)");
		$WpPagesIDsArray = [];
		foreach ($WpPagesIDs as $WpPageRow){
			$WpPagesIDsArray[] = $WpPageRow->ID;
		}
		$WpPagesToRecreateArray = [];
		foreach ($WpPagesArray as $WpPage => $WpPageArray){
			if(in_array($WpPage,$WpPagesIDsArray)===false){
				$rowObjectID = $WpPageArray[key($WpPageArray)];
				if(!isset($WpPagesToRecreateArray[$rowObjectID])){
					$WpPagesToRecreateArray[$rowObjectID] = [];
				}
				$WpPagesToRecreateArray[$rowObjectID][] = key($WpPageArray);
			}
		}

		foreach ($WpPagesToRecreateArray as $rowObjectID => $WpPageTypeNamesArray){
			if(!empty($WpPageTitlesArrayFromInputForm[$rowObjectID])){
				$post_title = $WpPageTitlesArrayFromInputForm[$rowObjectID];
			}else{
				$post_title = $post_titles_array[$WpUploadsArray[$rowObjectID]];
			}
			if(empty($post_title)){$post_title='entry';}// if both variants above not available then simply the word "entry" will be taken
			// cg_gallery shortcode
			$post_title = substr($post_title,0,100);
			if(in_array('WpPage',$WpPageTypeNamesArray)!==false){
				$post_type = 'contest-gallery';
				if(intval($options->Version)>=24){
                    $post_type = 'contest-g';
				}

				// cg_gallery shortcode
				$array = [
					'post_title'=> $post_title,
					'post_type'=>$post_type,
					'post_content'=>"<!-- wp:shortcode -->"."\r\n".
					                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
					                "[cg_gallery id=\"$GalleryID\" entry_id=\"$rowObjectID\"]"."\r\n".
					                "<!-- /wp:shortcode -->",
					'post_mime_type'=>'contest-gallery-plugin-page',
					'post_status'=>'publish',
					'post_parent'=>$options->WpPageParent
				];
				$WpPage = wp_insert_post($array);
				$wpdb->update(
					"$tablename",
					array('WpPage' => $WpPage),
					array('id' => $rowObjectID),
					array('%d'),
					array('%d')
				);
			}
			// cg_gallery_user shortcode
			if(in_array('WpPageUser',$WpPageTypeNamesArray)!==false){

                $post_type = 'contest-gallery';
                if(intval($options->Version)>=24){
                    $post_type = 'contest-g-user';
                }

				// cg_gallery shortcode
				$array = [
					'post_title'=> $post_title,
					'post_type'=>$post_type,
					'post_content'=>"<!-- wp:shortcode -->"."\r\n".
					                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
					                "[cg_gallery_user id=\"$GalleryID\" entry_id=\"$rowObjectID\"]"."\r\n".
					                "<!-- /wp:shortcode -->",
					'post_mime_type'=>'contest-gallery-plugin-page',
					'post_status'=>'publish',
					'post_parent'=>$options->WpPageParentUser
				];

				$WpPageUser = wp_insert_post($array);
				$wpdb->update(
					"$tablename",
					array('WpPageUser' => $WpPageUser),
					array('id' => $rowObjectID),
					array('%d'),
					array('%d')
				);
			}
			// cg_gallery_no_voting shortcode
			if(in_array('WpPageNoVoting',$WpPageTypeNamesArray)!==false){

                $post_type = 'contest-gallery';
                if(intval($options->Version)>=24){
                    $post_type = 'contest-g-no-voting';
                }

				// cg_gallery shortcode
				$array = [
					'post_title'=> $post_title,
					'post_type'=>$post_type,
					'post_content'=>"<!-- wp:shortcode -->"."\r\n".
					                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
					                "[cg_gallery_no_voting id=\"$GalleryID\" entry_id=\"$rowObjectID\"]"."\r\n".
					                "<!-- /wp:shortcode -->",
					'post_mime_type'=>'contest-gallery-plugin-page',
					'post_status'=>'publish',
					'post_parent'=>$options->WpPageParentNoVoting
				];
				$WpPageNoVoting = wp_insert_post($array);
				$wpdb->update(
					"$tablename",
					array('WpPageNoVoting' => $WpPageNoVoting),
					array('id' => $rowObjectID),
					array('%d'),
					array('%d')
				);
			}
			// cg_gallery_winner shortcode
			if(in_array('WpPageWinner',$WpPageTypeNamesArray)!==false){

                $post_type = 'contest-gallery';
                if(intval($options->Version)>=24){
                    $post_type = 'contest-g-winner';
                }

				// cg_gallery shortcode
				$array = [
					'post_title'=> $post_title,
					'post_type'=>$post_type,
					'post_content'=>"<!-- wp:shortcode -->"."\r\n".
					                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
					                "[cg_gallery_winner id=\"$GalleryID\" entry_id=\"$rowObjectID\"]"."\r\n".
					                "<!-- /wp:shortcode -->",
					'post_mime_type'=>'contest-gallery-plugin-page',
					'post_status'=>'publish',
					'post_parent'=>$options->WpPageParentWinner
				];
				$WpPageWinner = wp_insert_post($array);
				$wpdb->update(
					"$tablename",
					array('WpPageWinner' => $WpPageWinner),
					array('id' => $rowObjectID),
					array('%d'),
					array('%d')
				);
			}

			if(intval($options->Version)>=22){
				// cg_gallery_ecommerce shortcode
				if(in_array('WpPageEcommerce',$WpPageTypeNamesArray)!==false){

                    $post_type = 'contest-gallery';

                    if(intval($options->Version)>=24){
                        $post_type = 'contest-g-ecommerce';
                    }

					// cg_gallery shortcode
					$array = [
						'post_title'=> $post_title,
						'post_type'=>$post_type,
						'post_content'=>"<!-- wp:shortcode -->"."\r\n".
						                "<!--This is a comment: cg_galley... shortcode with entry id is required to display Contest Gallery entry on a Contest Gallery Custom Post Type entry page. You can place your own content before and after this shortcode, whatever you like. You can place cg_gallery... shortcode with entry_id also on any other of your pages. -->"."\r\n".
						                "[cg_gallery_ecommerce id=\"$GalleryID\" entry_id=\"$rowObjectID\"]"."\r\n".
						                "<!-- /wp:shortcode -->",
						'post_mime_type'=>'contest-gallery-plugin-page',
						'post_status'=>'publish',
						'post_parent'=>$options->WpPageParentEcommerce
					];
					$WpPageEcommerce = wp_insert_post($array);
					$wpdb->update(
						"$tablename",
						array('WpPageEcommerce' => $WpPageEcommerce),
						array('id' => $rowObjectID),
						array('%d'),
						array('%d')
					);
				}
			}

		}
	}

	$picsSQL = $wpdb->get_results( "SELECT DISTINCT $table_posts.*, $tablename.* FROM $table_posts, $tablename WHERE 
                                              ($tablename.GalleryID='$GalleryID' AND $tablename.Active='1' and $table_posts.ID = $tablename.WpUpload) OR 
                                              ($tablename.GalleryID='$GalleryID' AND $tablename.Active='1' AND $tablename.WpUpload = 0) 
                                          GROUP BY $tablename.id ORDER BY $tablename.id DESC");

	$imageArray = [];

	$jsonUploadImageDataDir = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-data';
	$jsonUploadImageInfoDir = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-info';
	$jsonUploadImageCommentsDir = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/json/image-comments';

	// delete folders first for clean data and removed images really removed
	do_action('cg_delete_files_and_folder', $jsonUploadImageDataDir);
	do_action('cg_delete_files_and_folder', $jsonUploadImageInfoDir);

	// recreate folders then for clean data and removed images really removed
	if(!is_dir($jsonUploadImageDataDir)){
		mkdir($jsonUploadImageDataDir,0755,true);
	}

	if(!is_dir($jsonUploadImageInfoDir)){
		mkdir($jsonUploadImageInfoDir,0755,true);
	}

	if(!is_dir($jsonUploadImageCommentsDir)){
		mkdir($jsonUploadImageCommentsDir,0755,true);
	}

	$RatingOverviewArray = cg_get_correct_rating_overview($GalleryID);

	// add all json files and generate images array
	foreach($picsSQL as $object){

		$imageArray = cg_create_json_files_when_activating($GalleryID,$object,$thumbSizesWp,$uploadFolder,$imageArray,0,$RatingOverviewArray);

		$isCorrectAndImprove = true;

		$isAlternativeFile=false;

		if($object->post_mime_type=="application/pdf"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="application/zip"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="text/plain"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="application/msword"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="application/vnd.openxmlformats-officedocument.wordprocessingml.document"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="application/vnd.ms-excel"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="text/csv"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="audio/mpeg"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="audio/wav"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="audio/ogg"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="video/mp4"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="video/x-ms-wmv"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="video/quicktime"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="video/avi"){$isAlternativeFile=true;}
		else if($object->post_mime_type=="video/webm"){$isAlternativeFile=true;}

		if(!$isAlternativeFile && intval($options->Version)<17){
			include(__DIR__.'/../../../v10/v10-admin/gallery/change-gallery/4_2_fb-creation.php');
		}

	}

	if(empty($imageArray) || !is_array($imageArray)){// then data was corrected without having activated images
		$imageArray = [];
	}

	// take care of order!
	//cg_json_upload_form_info_data_files($GalleryID,null);
	cg_json_upload_form_info_data_files_new($GalleryID,[],true,false,true);


	$correctStatusText6 = 'Repaired';
	$correctStatusClass6 = 'cg_corrected';

}