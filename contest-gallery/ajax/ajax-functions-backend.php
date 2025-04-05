<?php

// move to another gallery get inputs
add_action('wp_ajax_post_cg_move_to_another_gallery_get_inputs', 'post_cg_move_to_another_gallery_get_inputs');
if (!function_exists('post_cg_move_to_another_gallery_get_inputs')) {
	function post_cg_move_to_another_gallery_get_inputs() {

    global $wpdb;
    $tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
    $tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
    $tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";

    $contact_forms = $wpdb->get_results("SELECT * FROM $tablename_form_input WHERE id > 0");

    $contact_forms_by_gallery_id = [];
    foreach ($contact_forms as $form){
        if(!isset($contact_forms_by_gallery_id[$form->GalleryID])){
	        $contact_forms_by_gallery_id[$form->GalleryID] = [];
        }
	    if(is_serialized($form->Field_Content)){
		    $form->Field_Content = unserialize($form->Field_Content);
        }
	    $contact_forms_by_gallery_id[$form->GalleryID][] = $form;
    }

		$galleryIDs = $wpdb->get_results("SELECT id FROM $tablenameOptions WHERE id >= 1 ORDER BY id DESC");
		$allCategoriesByGalleryID = $wpdb->get_results("SELECT id, GalleryID, Name FROM $tablename_categories WHERE id >= 1 ORDER BY id DESC");
		$allCategoriesByGalleryIDArray = [];
        foreach ($allCategoriesByGalleryID as $row){
            if(!isset($allCategoriesByGalleryIDArray[$row->GalleryID])){
	            $allCategoriesByGalleryIDArray[$row->GalleryID] = [];
            }
	        $allCategoriesByGalleryIDArray[$row->GalleryID][$row->id] = [];
	        $allCategoriesByGalleryIDArray[$row->GalleryID][$row->id]['id'] = $row->id;
	        $allCategoriesByGalleryIDArray[$row->GalleryID][$row->id]['name'] = $row->Name;
        }

		?>
    <script data-cg-processing="true">
        cgJsClassAdmin.gallery.vars.allCategoriesByGalleryID = <?php echo json_encode($allCategoriesByGalleryIDArray);?>;
        cgJsClassAdmin.gallery.vars.galleryIDs = <?php echo json_encode($galleryIDs);?>;// renew here for sure
        cgJsClassAdmin.gallery.vars.contact_forms_by_gallery_id = <?php echo json_encode($contact_forms_by_gallery_id); ?>;
    </script>
<?php

        }
}

// move to another gallery
add_action('wp_ajax_post_cg_move_to_another_gallery', 'post_cg_move_to_another_gallery');
if (!function_exists('post_cg_move_to_another_gallery')) {
	function post_cg_move_to_another_gallery()
	{
		contest_gal1ery_db_check();

		$_POST = cg1l_sanitize_post($_POST);

		$isBackendCall = true;
		$isAjaxCall = true;

		$isAjaxCategoriesCall = true;

		global $wp_version;
		$sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

		$cgVersion = cg_get_version_for_scripts();

		if (defined('DOING_AJAX') && DOING_AJAX) {

			$user = wp_get_current_user();

			if (
				is_super_admin($user->ID) ||
				in_array('administrator', (array)$user->roles) ||
				in_array('editor', (array)$user->roles) ||
				in_array('author', (array)$user->roles)
			) {

				$cgMoveRealId = absint($_POST['cgMoveRealId']);
				$InGalleryIDtoMove = absint($_POST['cg_in_gallery_id_to_move']);
				$MoveFromGalleryID = absint($_POST['cgMoveFromGalleryID']);
				$cgMoveCategory = absint($_POST['cgMoveCategory']);
				$MoveAssigns = $_POST['cgMoveAssigns'];

				global $wpdb;
				$table_posts = $wpdb->prefix . "posts";
				$tablename = $wpdb->prefix . "contest_gal1ery";
				$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
				$tablename_comments = $wpdb->prefix . "contest_gal1ery_comments";
				$tablename_entries = $wpdb->prefix . 'contest_gal1ery_entries';
				$tablename_ip = $wpdb->prefix . "contest_gal1ery_ip";

				$insert_id = cg_copy_table_row('contest_gal1ery',$cgMoveRealId, $valueCollect = [], $cgCopyType = '');

				$Version = cg_get_version_for_scripts();

				$wpdb->update(
					"$tablename",
					array('Version' => $Version,'GalleryID' => $InGalleryIDtoMove),
					array('id' => $insert_id),
					array('%s'),
					array('%d')
				);

				$row = $wpdb->get_row("SELECT * FROM $tablename WHERE id = $insert_id");

				// Delete previous entry because inserted as new one through cg_copy_table_row
				$wpdb->query("DELETE FROM $tablename WHERE id = $cgMoveRealId");

                // delete WpPages now
				if(!empty($row->WpPage)){
					wp_delete_post($row->WpPage,true);
				}
				if(!empty($row->WpPageUser)){
					wp_delete_post($row->WpPageUser,true);
				}
				if(!empty($row->WpPageNoVoting)){
					wp_delete_post($row->WpPageNoVoting,true);
				}
				if(!empty($row->WpPageWinner)){
					wp_delete_post($row->WpPageWinner,true);
				}
				if(!empty($row->WpPageEcommerce)){
					wp_delete_post($row->WpPageEcommerce,true);
				}

                // Update parents
				$optionsInGalleryToMove = $wpdb->get_row("SELECT * FROM $tablename_options WHERE id = $InGalleryIDtoMove ORDER BY id DESC LIMIT 0, 1");

				if(!empty($optionsInGalleryToMove->WpPageParent)) {
					$post_title = substr($row->NamePic,0,100);
					cg_create_wp_pages($InGalleryIDtoMove,$insert_id,$post_title,$optionsInGalleryToMove,$optionsInGalleryToMove->Version);
				}

				if(!empty($cgMoveCategory)){
					$wpdb->query("UPDATE $tablename SET Category=$cgMoveCategory WHERE id = $insert_id");
				}else{
					$wpdb->query("UPDATE $tablename SET Category=0 WHERE id = $insert_id");
				}

				$wpdb->query("UPDATE $tablename_ip SET pid=$insert_id, GalleryID = $InGalleryIDtoMove WHERE pid=$cgMoveRealId");
				$wpdb->query("UPDATE $tablename_comments SET pid=$insert_id, GalleryID = $InGalleryIDtoMove WHERE pid=$cgMoveRealId");
				$wpdb->query("UPDATE $tablename_entries SET pid=$insert_id, GalleryID = $InGalleryIDtoMove WHERE pid=$cgMoveRealId");

				$input_ids_entries_to_delete = $wpdb->get_results("SELECT id, f_input_id FROM $tablename_entries WHERE pid  = $insert_id");
				$input_ids_entries_to_delete_array = [];
                foreach ($input_ids_entries_to_delete as $entry){
	                $input_ids_entries_to_delete_array[$entry->f_input_id] = $entry->id;
                }

                if(!empty($MoveAssigns)){// have to be checked with not empty
                    // now change the input ids if were assigned
	                foreach ($MoveAssigns as $FromInput => $ToInput){
		                $FromInput = absint($FromInput);
		                $ToInput = absint($ToInput);
		                $wpdb->query("UPDATE $tablename_entries SET f_input_id = $ToInput WHERE pid = $insert_id && f_input_id = $FromInput");
                        if(isset($input_ids_entries_to_delete_array[$FromInput])){
	                        unset($input_ids_entries_to_delete_array[$FromInput]);
                        }
	                }
                }

				foreach ($input_ids_entries_to_delete_array as $f_input_id => $entryId) {
					$wpdb->query("DELETE FROM $tablename_entries WHERE id = $entryId");
                }

				$wp_upload_dir = wp_upload_dir();
                // unlink activated entries if exists
				if(file_exists($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-data/image-data-".$cgMoveRealId.".json")){
					unlink($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-data/image-data-".$cgMoveRealId.".json");
				}
				if(file_exists($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-info/image-info-".$cgMoveRealId.".json")){
					unlink($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-info/image-info-".$cgMoveRealId.".json");
				}
                // move file
				if(file_exists($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-comments/image-comments-".$cgMoveRealId.".json")){
                    if(!is_dir($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$InGalleryIDtoMove."/json/image-comments")){
	                    mkdir($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$InGalleryIDtoMove."/json/image-comments",0755,true);
                    }
					rename($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-comments/image-comments-".$cgMoveRealId.".json", $wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$InGalleryIDtoMove."/json/image-comments/image-comments-".$insert_id.".json");
				}
                // move folder
				if(is_dir($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-comments/ids/".$cgMoveRealId)){
					if(!is_dir($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$InGalleryIDtoMove."/json/image-comments/ids")){
						mkdir($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$InGalleryIDtoMove."/json/image-comments/ids",0755,true);
                    }
					rename($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-comments/ids/".$cgMoveRealId, $wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$InGalleryIDtoMove."/json/image-comments/ids/".$insert_id);
				}

				cg_json_upload_form_info_data_files_new($InGalleryIDtoMove,[$insert_id],true);

                if($row->Active==1){
	                $collect = "$tablename.id = ".$row->id;
	                $GalleryID = $row->GalleryID;
	                $row = $wpdb->get_row( "SELECT DISTINCT $table_posts.*, $tablename.* FROM $table_posts, $tablename WHERE 
                                              (($collect) AND $tablename.GalleryID='$GalleryID' AND $tablename.Active='1' and $table_posts.ID = $tablename.WpUpload) 
                                              OR 
                                              (($collect) AND $tablename.GalleryID='$GalleryID' AND $tablename.Active='1' AND $tablename.WpUpload = 0) 
                                          GROUP BY $tablename.id  ORDER BY $tablename.id DESC LIMIT 0, 1");
	                cg_create_json_files_when_activating($InGalleryIDtoMove,$row);
                }

				die;

			} else {
				echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>post_cg_social_platform_input can be edited only as administrator, editor or author.</h2></div>";
				exit();
			}

			exit();
		} else {
			exit();
		}
	}
}
// move to another gallery---- END

// view control backend

add_action('wp_ajax_post_cg_gallery_view_control_backend', 'post_cg_gallery_view_control_backend');

if (!function_exists('post_cg_gallery_view_control_backend')) {
    function post_cg_gallery_view_control_backend()
    {

        contest_gal1ery_db_check();

        $cgVersion = cg_get_version_for_scripts();

        if (!empty($_POST['cgVersionScripts'])) {

            if ($cgVersion != $_POST['cgVersionScripts']) {
                echo 'newversion';// has to be done this way, with echo and exit, not return!
                exit();
            }

        } else if (empty($_POST['cgVersionScripts']) && !empty($_POST['cgGalleryFormSubmit'])) { // IMPORTANT that data is not saved when wrong data is send when updateting 109900

            echo "<div id='cgStepsNavigationTop' ></div>";
            echo "<div id='cgSortable' style='width:100%;text-align:center;'><h4>New gallery version detected please reload this page manually one more time</h4></div>";
            exit();

        }

        $isBackendCall = true;
        $isAjaxCall = true;
        $isAjaxGalleryCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {

                if (!empty($isBackendCall)) {

                    if (empty($_POST['cgGalleryHash'])) {
                        echo 0;
                        die;
                    } else {

                        $galleryHash = $_POST['cgGalleryHash'];
                        $galleryHashDecoded = wp_salt('auth') . '---cngl1---' . $_POST['cg_id'];
                        $galleryHashToCompare = md5($galleryHashDecoded);

                        if ($galleryHash != $galleryHashToCompare) {
                            echo 0;
                            die;
                        }

                    }

                }

                include(__DIR__.'/../v10/v10-admin/gallery/gallery.php');

            } else {
                echo "<h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2>";
                exit();
            }

            exit();
        } else {
            exit();
        }
    }
}

// view control backend ---- END

add_action('wp_ajax_post_cg_gallery_save_categories_changes', 'post_cg_gallery_save_categories_changes');

if (!function_exists('post_cg_gallery_save_categories_changes')) {
    function post_cg_gallery_save_categories_changes()
    {
        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

	    if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {
	            include(__DIR__.'/../v10/v10-admin/gallery/save-categories-changes.php');
            } else {
                echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
                exit();
            }

            exit();
        } else {
            exit();
        }
    }
}

add_action('wp_ajax_post_cg_change_invoice', 'post_cg_change_invoice');

if (!function_exists('post_cg_change_invoice')) {
    function post_cg_change_invoice()
    {
        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';


        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {
                include(__DIR__.'/../v10/v10-admin/ecommerce/change-invoice-processing.php');
            } else {
                echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
                exit();
            }

            exit();
        } else {
            exit();
        }
    }
}

// twitter get
add_action('wp_ajax_post_cg_twitter_get', 'post_cg_twitter_get');
if (!function_exists('post_cg_twitter_get')) {
    function post_cg_twitter_get()
    {
        //contest_gal1ery_db_check();

	    $_POST = cg1l_sanitize_post($_POST);

	    $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

	    $cgVersion = cg_get_version_for_scripts();

        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {

                $post_cg_twitter_url = $_POST['post_cg_twitter_url'];

	            $ch = curl_init();
	            //curl_setopt($ch, CURLOPT_URL, "https://publish.twitter.com/oembed?theme=dark&url=".$post_cg_twitter_url);
	            curl_setopt($ch, CURLOPT_URL, "https://publish.twitter.com/oembed?url=".$post_cg_twitter_url);
	            curl_setopt($ch, CURLOPT_HEADER, false);
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	            curl_setopt($ch, CURLOPT_SSLVERSION , 6); //NEW ADDITION
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	            $result = curl_exec($ch);
	            curl_close($ch);
	            $error_msg = curl_error($ch);
	            if (curl_errno($ch)) {
		            $error_msg = curl_error($ch);
		            $result = '';
	            }else{
		            $result = str_replace('\n', '', $result);
		            $result = rtrim($result, ',');
		            $result = json_decode(trim($result),true);
	            }

	            ?>
	            <script data-cg-processing="true">
                    cgJsClassAdmin.gallery.vars.twitterData = <?php echo json_encode($result); ?>;
	            </script>
	            <?php

	            die;

            } else {
                echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>post_cg_twitter_get can be edited only as administrator, editor or author.</h2></div>";
                exit();
            }

            exit();
        } else {
            exit();
        }
    }
}
// twitter get ---- END

// youtube input
add_action('wp_ajax_post_cg_social_platform_input', 'post_cg_social_platform_input');
if (!function_exists('post_cg_social_platform_input')) {
    function post_cg_social_platform_input()
    {
        contest_gal1ery_db_check();

        $blockquote = '';
        if(!empty($_POST['socialData']['html'])){
	        $blockquote = 'blockquote: '.contest_gal1ery_htmlentities_and_preg_replace($_POST['socialData']['html']).';';
        }
	    $_POST = cg1l_sanitize_post($_POST);

	    $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

	    $cgVersion = cg_get_version_for_scripts();

        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {

                $post_mime_type = '';

	            if($_POST['urlType']=='youtube'){
		            $post_mime_type = 'contest-gallery-youtube';
	            }
	            if($_POST['urlType']=='twitter'){
		            $post_mime_type = 'contest-gallery-twitter';
	            }
	            if($_POST['urlType']=='instagram'){
		            $post_mime_type = 'contest-gallery-instagram';
	            }
	            if($_POST['urlType']=='tiktok'){
		            $post_mime_type = 'contest-gallery-tiktok';
	            }
                $post_name = $_POST['urlPart'];
	            $post_title = $_POST['socialData']['title'];
	            $post_content = '';
	            if(!empty($_POST['socialData']['author_name'])){
	                $post_content = 'author_name: '.$_POST['socialData']['author_name'].'; author_url: '.$_POST['socialData']['author_url'].'; type: '.$_POST['socialData']['type'].'; version: '.$_POST['socialData']['version'].'; '.$blockquote;
                }
	            if($_POST['urlType']=='instagram'){
		            $post_title = $post_name;
		            $post_content = '';
	            }
	            $guid = $_POST['guid'];
	            //$GalleryID = intval($_POST['gid']);

	            global $wpdb;
	            $table_posts = $wpdb->prefix . "posts";
	            //$tablename_options = $wpdb->prefix . "contest_gal1ery_options";

	            //$galleryDbVersion = $wpdb->get_var( "SELECT Version FROM $tablename_options WHERE id='$GalleryID'");

                $post_type = 'contest-gallery';
                //if(intval($galleryDbVersion)>=24){
	                //$post_type = 'contest-galleries';
                //}

                $post_title = substr(cg_pre_process_name_for_url_name($post_title),0,100);

            $array = [
                    'post_title'=> $post_title,
	            'post_name'=> $post_name,
                    'guid'=> $guid,
                    'post_type'=>$post_type,
                    'post_content'=>$post_content,
                    'post_mime_type'=>$post_mime_type,
	            'post_status'=>'publish'
            ];

	            $postId = wp_insert_post($array);


				// by default post_name will be converted to lowercase, so has to be update to original (which is with uppercases mostly) here
	            // also wordpress replace post_name by adding -1 and so on if same by defaul
	            $wpdb->update(
		            "$table_posts",
		            array('post_name' => $post_name),
		            array('ID' => $postId),
		            array('%s'),
		            array('%d')
	            );

	            echo '###SOCIAL-PLATFORM-POST-TYPE-ADDED###';

	            die;

            } else {
                echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>post_cg_social_platform_input can be edited only as administrator, editor or author.</h2></div>";
                exit();
            }

            exit();
        } else {
            exit();
        }
    }
}
// youtube input ---- END

// youtube query
add_action('wp_ajax_post_cg_social_platforms_query', 'post_cg_social_platforms_query');
if (!function_exists('post_cg_social_platforms_query')) {
    function post_cg_social_platforms_query()
    {

	    contest_gal1ery_db_check();

	    $_POST = cg1l_sanitize_post($_POST);

	    $isBackendCall = true;
	    $isAjaxCall = true;

	    $isAjaxCategoriesCall = true;

	    global $wp_version;
	    $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';


	    if (defined('DOING_AJAX') && DOING_AJAX) {

		    $user = wp_get_current_user();

		    if (
			    is_super_admin($user->ID) ||
			    in_array('administrator', (array)$user->roles) ||
			    in_array('editor', (array)$user->roles) ||
			    in_array('author', (array)$user->roles)
		    ) {
			    include(__DIR__.'/../v10/v10-admin/gallery/get-social-platforms-posts.php');
		    } else {
			    echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>post_cg_social_platforms_query can be edited only as administrator, editor or author.</h2></div>";
			    exit();
		    }

		    exit();
	    } else {
		    exit();
	    }
    }
}
// youtube query ---- END

// youtube add to gallery
add_action('wp_ajax_post_cg_social_platforms_add_to_gallery', 'post_cg_social_platforms_add_to_gallery');
if (!function_exists('post_cg_social_platforms_add_to_gallery')) {
    function post_cg_social_platforms_add_to_gallery()
    {

	    contest_gal1ery_db_check();

	    $_POST = cg1l_sanitize_post($_POST);

	    $isBackendCall = true;
	    $isAjaxCall = true;

	    $isAjaxCategoriesCall = true;

	    global $wp_version;
	    $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

	    if (defined('DOING_AJAX') && DOING_AJAX) {

		    $user = wp_get_current_user();

		    if (
			    is_super_admin($user->ID) ||
			    in_array('administrator', (array)$user->roles) ||
			    in_array('editor', (array)$user->roles) ||
			    in_array('author', (array)$user->roles)
		    ) {

			    $cg_wp_upload_ids = $_POST['cg_wp_post_ids'];
			    require_once(__DIR__.'/../v10/v10-admin/gallery/wp-uploader.php');

		    } else {
			    echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>post_cg_social_platforms_add_to_gallery can be edited only as administrator, editor or author.</h2></div>";
			    exit();
		    }

		    exit();
	    } else {
		    exit();
	    }
    }
}
// youtube add to gallery ---- END

// youtube add to gallery
add_action('wp_ajax_post_cg_youtube_delete_from_library', 'post_cg_youtube_delete_from_library');
if (!function_exists('post_cg_youtube_delete_from_library')) {
    function post_cg_youtube_delete_from_library()
    {
	    contest_gal1ery_db_check();

	    $_POST = cg1l_sanitize_post($_POST);

	    $isBackendCall = true;
	    $isAjaxCall = true;

	    $isAjaxCategoriesCall = true;

	    global $wp_version;
	    $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

	    if (defined('DOING_AJAX') && DOING_AJAX) {

		    $user = wp_get_current_user();

		    if (
			    is_super_admin($user->ID) ||
			    in_array('administrator', (array)$user->roles) ||
			    in_array('editor', (array)$user->roles) ||
			    in_array('author', (array)$user->roles)
		    ) {

			    $cg_wp_upload_ids = $_POST['cg_wp_post_ids'];

			    global $wpdb;
			    $tablename_posts = $wpdb->prefix . "posts";

			    foreach ($cg_wp_upload_ids as $WpUpload){
				    $wpdb->query($wpdb->prepare(
					    "
				DELETE FROM $tablename_posts WHERE ID = %d
			",
					    $WpUpload
				    ));
				}

		    } else {
			    echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
			    exit();
		    }

		    exit();
	    } else {
		    exit();
	    }
    }
}
// youtube add to gallery ---- END

// sort files

add_action('wp_ajax_post_cg_gallery_sort_files', 'post_cg_gallery_sort_files');

if (!function_exists('post_cg_gallery_sort_files')) {
    function post_cg_gallery_sort_files()
    {

        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';


        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {
                include(__DIR__.'/../v10/v10-admin/gallery/sort-gallery-files.php');
            } else {
                echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
                exit();
            }

            exit();
        } else {
            exit();
        }
    }
}
// sort files ---- END

// attach to another user select
add_action('wp_ajax_post_cg_attach_to_another_user_select', 'post_cg_attach_to_another_user_select');
if (!function_exists('post_cg_attach_to_another_user_select')) {
	function post_cg_attach_to_another_user_select()
	{
		if (defined('DOING_AJAX') && DOING_AJAX) {

			$user = wp_get_current_user();

			if (
				is_super_admin($user->ID) ||
				in_array('administrator', (array)$user->roles) ||
				in_array('editor', (array)$user->roles) ||
				in_array('author', (array)$user->roles)
			) {
				global $wpdb;

				$wpUsers = $wpdb->base_prefix . "users";
				$selectWPusers = $wpdb->get_results("SELECT ID, user_login, user_email FROM $wpUsers WHERE ID > 0 ORDER BY ID ASC");

                echo "<select id='cgAttachToAnotherUserSelect' name='cgAttachToAnotherUserId' class='cg_no_outline_and_shadow_on_focus'>";
                    foreach ($selectWPusers as $user){
                        echo "<option value='$user->ID' data-user_login='$user->user_login' data-user_email='$user->user_email'>$user->user_login - $user->user_email (ID: $user->ID)</option>";
                    }
                echo "</select>";

			} else {
				echo "<div ><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
				exit();
			}

			exit();
		} else {
			exit();
		}
	}
}
// attach to another user select --- END

// attach to another user
add_action('wp_ajax_post_cg_attach_to_another_user', 'post_cg_attach_to_another_user');
if (!function_exists('post_cg_attach_to_another_user')) {
	function post_cg_attach_to_another_user()
	{
		if (defined('DOING_AJAX') && DOING_AJAX) {

			$user = wp_get_current_user();

			if (
				is_super_admin($user->ID) ||
				in_array('administrator', (array)$user->roles) ||
				in_array('editor', (array)$user->roles) ||
				in_array('author', (array)$user->roles)
			) {
				global $wpdb;

				$tablename = $wpdb->prefix . "contest_gal1ery";
				$table_posts = $wpdb->prefix . "posts";
				#$wpUsers = $wpdb->prefix . "users";

				$WpUserId = absint($_POST['cgAttachToAnotherUserId']);
				$pid = absint($_POST['cgEntryId']);
				$GalleryID = absint($_POST['GalleryID']);

				$wpdb->query("UPDATE $tablename SET WpUserId=$WpUserId WHERE id = $pid");

				$Active = $wpdb->get_var( "SELECT Active FROM $tablename WHERE id = $pid");

                if($Active==1){
	                $row = $wpdb->get_row( "SELECT DISTINCT $table_posts.*, $tablename.* FROM $table_posts, $tablename WHERE 
                          (($tablename.id = $pid) AND $tablename.GalleryID='$GalleryID' AND $tablename.Active='1' and $table_posts.ID = $tablename.WpUpload) 
                          OR 
                          (($tablename.id = $pid) AND $tablename.GalleryID='$GalleryID' AND $tablename.Active='1' AND $tablename.WpUpload = 0) 
                          GROUP BY $tablename.id  ORDER BY $tablename.id DESC LIMIT 0, 1");
	                cg_create_json_files_when_activating($GalleryID,$row);
                }

				#$wpUser = $wpdb->get_row("SELECT user_login, user_email FROM $wpUsers WHERE ID = $WpUserId");
				//echo "###".$wpUser->user_login." - ".$wpUser->user_email."###";
				echo "###post_cg_attach_to_another_user successful###";

			} else {
				echo "<div ><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
				exit();
			}

			exit();
		} else {
			exit();
		}
	}
}
// attach to another user --- END

// sort files
add_action('wp_ajax_post_cg_test_ecom_keys', 'post_cg_test_ecom_keys');
if (!function_exists('post_cg_test_ecom_keys')) {
    function post_cg_test_ecom_keys()
    {
        contest_gal1ery_db_check();

        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {

                $isTest = false;
                $cg_client = sanitize_text_field($_GET['cg_client']);
                $cg_secret = sanitize_text_field($_GET['cg_secret']);
                if(intval($_GET['cg_test_env'])==1){
                    $isTest = true;
                }

                if(empty($cg_secret)){// cause without secret an access token will be at least generated, but can not be used for further requests
                    $accessToken='error' ;
                }else{
                    $accessToken = cg_paypal_get_access_token($cg_client,$cg_secret,$isTest);
                }

                if($accessToken!='error' && $accessToken!='no-internet'){
                    echo '###cgkeytrue###';
                }else{
                    echo '###cgkeyfalse###';
                }

            } else {
                echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
                exit();
            }

            exit();
        } else {
            exit();
        }
    }
}
// sort files ---- END

// sort files
add_action('wp_ajax_post_cg_test_stripe_keys', 'post_cg_test_stripe_keys');
if (!function_exists('post_cg_test_stripe_keys')) {
    function post_cg_test_stripe_keys()
    {
        contest_gal1ery_db_check();

        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {

                $cg_client = sanitize_text_field($_GET['cg_client']);
                $cg_secret = sanitize_text_field($_GET['cg_secret']);

                $tokenError = '';

				if(empty($cg_client) || empty($cg_secret)){// cause without secret an access token will be at least generated, but can not be used for further requests
					$tokenError='client or secret not provided';
				}else{
					$tokenError = cg_test_stripe_keys($cg_client,$cg_secret);
				}

                if(!empty($tokenError)){
	                echo '###cgmessage###'.$tokenError.'###cgmessage###';
                }else{
	                echo '###cgkeytrue###';
                }

            } else {
                echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
                exit();
            }

            exit();
        } else {
            exit();
        }
    }
}
// sort files ---- END

// save json

add_action('wp_ajax_post_cg_shortcode_interval_conf', 'post_cg_shortcode_interval_conf');

if (!function_exists('post_cg_shortcode_interval_conf')) {
    function post_cg_shortcode_interval_conf()
    {
        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        if (defined('DOING_AJAX') && DOING_AJAX) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {

                include(__DIR__.'/../v10/v10-admin/gallery/save-shortcode-interval-conf.php');

            } else {
                echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
                exit();
            }

            exit();
        } else {
            exit();
        }
    }
}

// save json ---- END

// AJAX Script für set comment ---- ENDE


// show paypal transaction response

add_action('wp_ajax_post_cg_show_paypal_api_response', 'post_cg_show_paypal_api_response');

if (!function_exists('post_cg_show_paypal_api_response')) {
    function post_cg_show_paypal_api_response()
    {

        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version < 4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        if (defined('DOING_AJAX') && DOING_AJAX) {
            $user = wp_get_current_user();
            if (
                is_super_admin($user->ID) ||
                in_array('administrator', (array)$user->roles) ||
                in_array('editor', (array)$user->roles) ||
                in_array('author', (array)$user->roles)
            ) {
                include(__DIR__.'/../v10/v10-admin/ecommerce/show-paypal-api-response.php');
            } else {
                echo "<div id='cgSaveCategoriesCouldNotBeChanged'><h2>MISSINGRIGHTS<br>This area can be edited only as administrator, editor or author.</h2></div>";
                exit();
            }
            exit();
        } else {
            exit();
        }
    }
}

// show paypal transaction response ---- END


// set for paypal sell
add_action( 'wp_ajax_post_cg_set_for_paypal_sell', 'post_cg_set_for_paypal_sell' );
if(!function_exists('post_cg_set_for_paypal_sell')){
    function post_cg_set_for_paypal_sell() {

	    // has to be unsanitized because of the url eventually configured by user
	    $AllUploadsUsedText = contest_gal1ery_htmlentities_and_preg_replace($_POST['cgSellContainer']['AllUploadsUsedText']);
        $_POST = cg1l_sanitize_post($_POST);
	    $_POST['cgSellContainer']['AllUploadsUsedText'] = $AllUploadsUsedText;

        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version<4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array( 'administrator', (array) $user->roles ) ||
                in_array( 'editor', (array) $user->roles ) ||
                in_array( 'author', (array) $user->roles )
            ) {

                cg_ecommerce_sale_conf();

                die;

            }else{
                echo "MISSINGRIGHTS - This area can be edited only as administrator, editor or author.";
                exit();
            }

            exit();
        }
        else {
            exit();
        }
    }
}
// set for paypal sell --- END

// download paypal original source
add_action( 'wp_ajax_post_cg_download_original_source_for_ecommerce_sale', 'post_cg_download_original_source_for_ecommerce_sale' );
if(!function_exists('post_cg_download_original_source_for_ecommerce_sale')){
    function post_cg_download_original_source_for_ecommerce_sale() {

        $_POST = cg1l_sanitize_post($_POST);

        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version<4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array( 'administrator', (array) $user->roles ) ||
                in_array( 'editor', (array) $user->roles ) ||
                in_array( 'author', (array) $user->roles )
            ) {

                cg_download_file_ecommerce_sale();

                die;

            }else{
                echo "MISSINGRIGHTS - This area can be edited only as administrator, editor or author.";
                exit();
            }

            exit();
        }
        else {
            exit();
        }
    }
}

// deactivate paypal sale
add_action( 'wp_ajax_post_cg_deactivate_ecommerce_sale', 'post_cg_deactivate_ecommerce_sale' );
if(!function_exists('post_cg_deactivate_ecommerce_sale')){
    function post_cg_deactivate_ecommerce_sale() {

        $_POST = cg1l_sanitize_post($_POST);

        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version<4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array( 'administrator', (array) $user->roles ) ||
                in_array( 'editor', (array) $user->roles ) ||
                in_array( 'author', (array) $user->roles )
            ) {

                cg_deactivate_ecommerce_sale();

                die;

            }else{
                echo "MISSINGRIGHTS - This area can be edited only as administrator, editor or author.";
                exit();
            }

            exit();
        }
        else {
            exit();
        }
    }
}

// set for paypal sell
add_action( 'wp_ajax_post_cg_paypal_invoicing', 'post_cg_paypal_invoicing' );
if(!function_exists('post_cg_paypal_invoicing')){
    function post_cg_paypal_invoicing() {

        $_POST = cg1l_sanitize_post($_POST);

        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

        $isAjaxCategoriesCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version<4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            $user = wp_get_current_user();

            if (
                is_super_admin($user->ID) ||
                in_array( 'administrator', (array) $user->roles ) ||
                in_array( 'editor', (array) $user->roles ) ||
                in_array( 'author', (array) $user->roles )
            ) {

                cg_get_paypal_data();

                die;

            }else{
                echo "MISSINGRIGHTS - This area can be edited only as administrator, editor or author.";
                exit();
            }

            exit();
        }
        else {
            exit();
        }
    }
}
// set for paypal sell --- END

// check nickname
add_action( 'wp_ajax_post_cg_check_nickname_edit_profile', 'post_cg_check_nickname_edit_profile' );
if(!function_exists('post_cg_check_nickname_edit_profile')){
    function post_cg_check_nickname_edit_profile() {

        $_POST = cg1l_sanitize_post($_POST);
        contest_gal1ery_db_check();

        $isBackendCall = true;
        $isAjaxCall = true;

        global $wp_version;
        $sanitize_textarea_field = ($wp_version<4.7) ? 'sanitize_text_field' : 'sanitize_textarea_field';

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

            $user = wp_get_current_user();

            $hasUserGroupAllowedToEdit = cgHasUserGroupAllowedToEdit($user);

            if($hasUserGroupAllowedToEdit){

                $nickname = sanitize_text_field($_POST['nickname']);
                $cg_user_id = absint($_POST['cg_user_id']);

                global $wpdb;

                $table_usermeta = $wpdb->prefix . "usermeta";
                $user_id_check = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $table_usermeta WHERE meta_key = 'nickname' AND meta_value = %s",[$nickname]));

                if(!empty($user_id_check) AND $cg_user_id != $user_id_check){
                    echo 'nickname-exists';
                    die;
                }else{
                    echo 'nickname-not-exists';
                    die;
                }

            }else{
                echo 'do-nothing';die;
            }

      }else{
            echo 'do-nothing';die;
      }

    }
}
// check nickname --- END

// add contest gallery user profile image
add_action( 'wp_ajax_post_cg_backend_image_upload', 'post_cg_backend_image_upload' );
if(!function_exists('post_cg_backend_image_upload')){
    function post_cg_backend_image_upload() {

        global $wpdb;

        $tablename = $wpdb->base_prefix . "contest_gal1ery";

        $_POST = cg1l_sanitize_post($_POST);
        if(!empty($_FILES) AND !empty($_FILES['cg_input_image_upload_file']) AND !empty($_FILES['cg_input_image_upload_file']['tmp_name']) AND !empty($_FILES['cg_input_image_upload_file']['tmp_name'][0])){
            $_FILES = cg1l_sanitize_files($_FILES,'cg_input_image_upload_file',2100000);
        }

        $user = wp_get_current_user();
        $WpUserId  = absint($_POST['user_id']);

        $isAdministrator = false;

        if(is_super_admin($user->ID) || in_array( 'administrator', (array) $user->roles )){
            $isAdministrator = true;
        }

        if($user->ID != $WpUserId && $isAdministrator != true){// another user or not administrator user can't edit profile image
            return;
        }

        if(!empty($_POST['cg_input_image_upload_file_to_delete_wp_id'])){// then image must be removed!
            $WpProfileImage = $wpdb->get_row($wpdb->prepare("SELECT WpUpload, WpUserId FROM $tablename WHERE WpUserId = %d && IsProfileImage = 1",[$WpUserId]));

            if($WpProfileImage->WpUserId == $user->ID){
                $wpdb->query($wpdb->prepare(
                    "
            DELETE FROM $tablename WHERE WpUserId = %d && IsProfileImage = %d 
        ",
                    $WpUserId, 1
                ));
                // source and database _posts table entry  will be deleted
                wp_delete_attachment($WpProfileImage->WpUpload);
            }
        }

        if(!empty($_FILES) AND !empty($_FILES['cg_input_image_upload_file']) AND !empty($_FILES['cg_input_image_upload_file']['tmp_name']) AND !empty($_FILES['cg_input_image_upload_file']['tmp_name'][0])){
                    cg_registry_add_profile_image('cg_input_image_upload_file',$WpUserId);
                }

    }
}
// add contest gallery user profile image --- END