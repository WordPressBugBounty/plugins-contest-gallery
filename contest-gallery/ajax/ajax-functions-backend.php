<?php
include_once(__DIR__.'/../v10/v10-admin/gallery-transfer/gallery-transfer.php');

if (!function_exists('cg_backend_ajax_error_json')) {
    function cg_backend_ajax_error_json($message, $status = 400, $code = 'cg_backend_ajax_error') {
        wp_send_json_error(array(
            'message' => $message,
            'code' => $code
        ), $status);
    }
}

if (!function_exists('cg_backend_ajax_require_access_json')) {
    function cg_backend_ajax_require_access_json() {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            cg_backend_ajax_error_json('Invalid AJAX request.', 400, 'cg_invalid_ajax_request');
        }

        if (!is_user_logged_in() || !cg_user_has_backend_access()) {
            cg_backend_ajax_error_json('This area can be edited only as administrator, editor or author.', 403, 'cg_missing_rights');
        }

        $cg_nonce = '';
        if (isset($_POST['cg_nonce'])) {
            $cg_nonce = sanitize_text_field($_POST['cg_nonce']);
        } elseif (isset($_GET['cg_nonce'])) {
            $cg_nonce = sanitize_text_field($_GET['cg_nonce']);
        }

        if (empty($cg_nonce) || !wp_verify_nonce($cg_nonce, 'cg_nonce')) {
            wp_send_json_error(array(
                'message' => 'WP nonce security token not set or not valid anymore.',
                'code' => 'cg_nonce_invalid',
                'version' => cg_get_version()
            ), 403);
        }
    }
}

if (!function_exists('cg_backend_ajax_validate_gallery_hash_json')) {
    function cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash) {
        $GalleryID = absint($GalleryID);
        if (empty($GalleryID) || empty($galleryHash)) {
            cg_backend_ajax_error_json('Missing gallery validation data.', 403, 'cg_missing_gallery_hash');
        }

        $galleryHashToCompare = md5(wp_salt('auth') . '---cngl1---' . $GalleryID);
        if ($galleryHash !== $galleryHashToCompare) {
            cg_backend_ajax_error_json('Invalid gallery validation data.', 403, 'cg_invalid_gallery_hash');
        }
    }
}

// post_cg_get_current_permalinks
add_action('wp_ajax_post_cg_get_current_permalinks', 'post_cg_get_current_permalinks');
if (!function_exists('post_cg_get_current_permalinks')) {
    function post_cg_get_current_permalinks() {

        cg_require_backend_access();

        global $wpdb;
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $realId = absint($_POST['cgRealId']);
        $realIdRow = $wpdb->get_row( "SELECT * FROM $tablename WHERE id='$realId'" );

        $entryPermalinks = [];
        if(!empty($realIdRow->WpPage)){
            $permalink = cg_get_guid($realIdRow->WpPage);
            if(!empty($permalink)){
                $entryPermalinks['cg_gallery'] = $permalink;
            }
        }
        if(!empty($realIdRow->WpPageWinner)){
            $permalink = cg_get_guid($realIdRow->WpPageWinner);
            if(!empty($permalink)){
                $entryPermalinks['cg_gallery_winner'] = $permalink;
            }
        }
        if(!empty($realIdRow->WpPageUser)){
            $permalink = cg_get_guid($realIdRow->WpPageUser);
            if(!empty($permalink)){
                $entryPermalinks['cg_gallery_user'] = $permalink;
            }
        }
        if(!empty($realIdRow->WpPageNoVoting)){
            $permalink = cg_get_guid($realIdRow->WpPageNoVoting);
            if(!empty($permalink)){
                $entryPermalinks['cg_gallery_no_voting'] = $permalink;
            }
        }
        if(!empty($realIdRow->WpPageEcommerce)){
            $permalink = cg_get_guid($realIdRow->WpPageEcommerce);
            if(!empty($permalink)){
                $entryPermalinks['cg_gallery_ecommerce'] = $permalink.'?test=true';
            }
        }

        ?>
        <script data-cg-processing-get-current-permalinks="true">
            cgJsClassAdmin.gallery.vars.entryPermalinks = <?php echo json_encode($entryPermalinks); ?>;
        </script>
        <?php

    }
}

// create PDF preview
add_action('wp_ajax_post_cg_create_pdf_preview_backend', 'post_cg_create_pdf_preview_backend');
if (!function_exists('cg_create_pdf_preview_internal')) {
    function cg_create_pdf_preview_internal($WpUpload = 0, $realId = 0, $cg_base_64 = '', $isFromFrontendUpload = false) {

        global $wpdb;
        $tablename_posts = $wpdb->prefix . "posts";
        $tablename_wp_pdf_previews = $wpdb->prefix . "contest_gal1ery_pdf_previews";
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

        $_POST = cg1l_sanitize_post($_POST);

        $result = [
            'ok' => false,
            'attach_id' => 0,
            'preview_url' => '',
            'error' => ''
        ];

        $wp_upload_dir = wp_upload_dir();
        $cgWpUploadToReplace = 0;
        $cgNewWpUploadWhichReplace = 0;
        if (empty($WpUpload)) {
            $WpUpload = (!empty($_POST['cg_wp_upload'])) ? absint($_POST['cg_wp_upload']) : 0;
        }
        if (empty($realId)) {
            $realId = (!empty($_POST['cgRealId'])) ? absint($_POST['cgRealId']) : 0;
        }
        if (empty($cg_base_64)) {
            $cg_base_64 = (!empty($_POST['cg_base_64'])) ? $_POST['cg_base_64'] : '';
        }
        if (!empty($_POST['cgWpUploadToReplace'])) {
            $cgWpUploadToReplace = absint($_POST['cgWpUploadToReplace']);
        }
        if (!empty($_POST['cgNewWpUploadWhichReplace'])) {
            $cgNewWpUploadWhichReplace = absint($_POST['cgNewWpUploadWhichReplace']);
        }

        if (empty($WpUpload) || empty($realId)) {
            $result['error'] = 'missing_parameters';
            return $result;
        }

        $realIdRow = $wpdb->get_row("SELECT * FROM $tablename WHERE id='$realId'");
        if (empty($realIdRow)) {
            $result['error'] = 'missing_real_id_row';
            return $result;
        }

        $WpUploadRow = $wpdb->get_row("SELECT * FROM $tablename_posts WHERE ID='$WpUpload'");
        if (empty($WpUploadRow)) {
            $result['error'] = 'missing_wp_upload_row';
            return $result;
        }

        if (!empty($cgWpUploadToReplace) && !empty($cgNewWpUploadWhichReplace) && !empty($realIdRow->EcommerceEntry)) {
            $EcommerceEntry = $realIdRow->EcommerceEntry;
            $ecommerceEntry = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_entries WHERE id='$EcommerceEntry'");
            $removedWpUploadIdsFromSale = [$cgWpUploadToReplace];
            cg_replace_ecommerce_file($realIdRow->id, $realIdRow->GalleryID, $ecommerceEntry, $cgNewWpUploadWhichReplace, [], $removedWpUploadIdsFromSale);
        }

        $multipleFilesPdfPreview = 0;
        $multipleFilesTitle = '';
        if (!empty($realIdRow->MultipleFiles) && $realIdRow->MultipleFiles != '""') {
            $MultipleFiles = unserialize($realIdRow->MultipleFiles);
            foreach ($MultipleFiles as $file) {
                if (empty($file['isRealIdSource']) && $file['post_mime_type'] == 'application/pdf' && $file['WpUpload'] == $WpUpload && !empty($file['PdfPreview'])) {
                    $multipleFilesPdfPreview = $file['PdfPreview'];
                    $multipleFilesTitle = $file['post_title'];
                }
            }
        }

        if (!empty($realIdRow->PdfPreview) && !empty(get_post($realIdRow->PdfPreview)) && $WpUpload == $realIdRow->WpUpload) {
            $PdfPreviewImage = wp_get_attachment_image_src($realIdRow->PdfPreview, 'large');
            $result['ok'] = true;
            $result['attach_id'] = absint($realIdRow->PdfPreview);
            $result['preview_url'] = (!empty($PdfPreviewImage[0])) ? $PdfPreviewImage[0] : '';
            return $result;
        } elseif (!empty($multipleFilesPdfPreview) && !empty(get_post($multipleFilesPdfPreview))) {
            $multipleFilesPdfPreviewImage = wp_get_attachment_image_src($multipleFilesPdfPreview, 'large');
            $result['ok'] = true;
            $result['attach_id'] = absint($multipleFilesPdfPreview);
            $result['preview_url'] = (!empty($multipleFilesPdfPreviewImage[0])) ? $multipleFilesPdfPreviewImage[0] : '';
            return $result;
        } else {
            $content = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $cg_base_64));
            if (empty($content)) {
                $result['error'] = 'missing_preview_payload';
                return $result;
            }

            $formImage = imagecreatefromstring($content);
            if (!$formImage) {
                $result['error'] = 'invalid_preview_payload';
                return $result;
            }

            if (!empty($multipleFilesPdfPreview)) {
                $fullName = $multipleFilesTitle . '-cg-pdf-preview';
            } else {
                $fullName = $WpUploadRow->post_title . '-cg-pdf-preview';
            }
            $fullNamePath = $fullName;
            $fullNamePath = cg_pre_process_name_for_url_name($fullNamePath);
            $fullNamePath = cg_check_first_char_for_url_name_after_pre_processing($fullNamePath);
            $fullNamePath = cg_check_last_char_for_url_name_after_pre_processing($fullNamePath);
            $fullNamePath = cg_sluggify_for_url($fullNamePath);
            $fullNamePathFirst = $fullNamePath;

            $fullPath = $wp_upload_dir['basedir'] . $wp_upload_dir['subdir'] . '/' . $fullNamePathFirst . '.png';
            if (file_exists($fullPath)) {
                $i = 0;
                do {
                    if ($i == 0) {
                        $i = 1;
                    } else {
                        $i++;
                    }
                    $add = '-' . $i;
                    $fullNamePath = $fullNamePathFirst . $add;
                    $fullPath = $wp_upload_dir['basedir'] . $wp_upload_dir['subdir'] . '/' . $fullNamePath . '.png';
                } while (file_exists($fullPath));
            }

            imagesavealpha($formImage, true);
            imagepng($formImage, $fullPath);

            if (!file_exists($fullPath)) {
                imagedestroy($formImage);
                $result['error'] = 'preview_file_not_created';
                return $result;
            }

            $attachment = array(
                'guid' => $wp_upload_dir['url'] . "/" . $fullNamePath . '.png',
                'post_mime_type' => 'image/png',
                'post_title' => $fullName,
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $fullPath);
            if (empty($attach_id) || is_wp_error($attach_id)) {
                imagedestroy($formImage);
                $result['error'] = 'preview_attachment_insert_failed';
                return $result;
            }

            $imagenew = get_post($attach_id);
            $fullsizepath = get_attached_file($imagenew->ID);
            $attach_data = wp_generate_attachment_metadata($attach_id, $fullsizepath);
            wp_update_attachment_metadata($attach_id, $attach_data);
            imagedestroy($formImage);

            $wpdb->query($wpdb->prepare(
                "
						INSERT INTO $tablename_wp_pdf_previews
						(id, WpUpload, WpUploadPreview)
						VALUES ( %s,%d,%d)
					",
                '', $WpUpload, $attach_id
            ));

            $multipleFilesWpUploadForPdfPreview = 0;

            if (!empty($realIdRow->MultipleFiles) && $realIdRow->MultipleFiles != '""') {
                $MultipleFiles = unserialize($realIdRow->MultipleFiles);
                foreach ($MultipleFiles as $file) {
                    if (empty($file['isRealIdSource']) && $file['post_mime_type'] == 'application/pdf' && $file['WpUpload'] == $WpUpload) {
                        $multipleFilesWpUploadForPdfPreview = $WpUpload;
                    }
                }
            }

            if (!empty($multipleFilesWpUploadForPdfPreview)) {
                $MultipleFiles = unserialize($realIdRow->MultipleFiles);
                $MultipleFilesNew = [];
                foreach ($MultipleFiles as $order => $file) {
                    if (empty($file['isRealIdSource']) && $file['post_mime_type'] == 'application/pdf' && $file['WpUpload'] == $WpUpload && $multipleFilesWpUploadForPdfPreview == $WpUpload) {
                        $file['PdfPreview'] = $attach_id;
                        $PdfPreviewImage = wp_get_attachment_image_src($attach_id, 'full');
                        $file['PdfPreviewImage'] = $PdfPreviewImage[0];
                        $PdfPreviewImageLarge = wp_get_attachment_image_src($attach_id, 'large');
                        $file['PdfPreviewImageLarge'] = $PdfPreviewImageLarge[0];
                        $file['PdfOriginal'] = get_the_guid($file['WpUpload']);
                        $file['full'] = $PdfPreviewImage[0];
                        $file['guid'] = $PdfPreviewImage[0];
                        $file['Width'] = $PdfPreviewImage[1];
                        $file['Height'] = $PdfPreviewImage[2];
                        $file['thumbnail'] = $PdfPreviewImageLarge[0];
                        $file['medium'] = $PdfPreviewImageLarge[0];
                        $file['large'] = $PdfPreviewImageLarge[0];
                        $file['ImgType'] = 'png';
                        $file['post_mime_type'] = 'image/png';
                        $file['type'] = 'image';
                    }
                    $MultipleFilesNew[$order] = $file;
                }
                $MultipleFilesNew = serialize($MultipleFilesNew);
                $wpdb->query("UPDATE $tablename SET MultipleFiles='$MultipleFilesNew' WHERE id = $realId");
            } else {
                $wpdb->query("UPDATE $tablename SET PdfPreview=$attach_id WHERE id = $realId");
            }

            if (!$isFromFrontendUpload && !empty($realIdRow->Active)) {
                $uploadFolder = wp_upload_dir();
                $thumbSizesWp = array();
                $thumbSizesWp['thumbnail_size_w'] = get_option("thumbnail_size_w");
                $thumbSizesWp['medium_size_w'] = get_option("medium_size_w");
                $thumbSizesWp['large_size_w'] = get_option("large_size_w");
                $imageArray = array();
                $pid = $realIdRow->id;
                $GalleryID = $realIdRow->GalleryID;
                $row = $wpdb->get_row("SELECT DISTINCT $tablename_posts.*, $tablename.* FROM $tablename_posts, $tablename WHERE
                          (($tablename.id = $pid) AND $tablename.GalleryID='$GalleryID' AND $tablename.Active='1' and $tablename_posts.ID = $tablename.WpUpload)
                          OR
                          (($tablename.id = $pid) AND $tablename.GalleryID='$GalleryID' AND $tablename.Active='1' AND $tablename.WpUpload = 0)
                          GROUP BY $tablename.id  ORDER BY $tablename.id DESC LIMIT 0, 1");
                cg_create_json_files_when_activating($GalleryID, $row, $thumbSizesWp, $uploadFolder, $imageArray);
            }

            $PdfPreviewImage = wp_get_attachment_image_src($attach_id, 'large');

            $result['ok'] = true;
            $result['attach_id'] = absint($attach_id);
            $result['preview_url'] = (!empty($PdfPreviewImage[0])) ? $PdfPreviewImage[0] : '';
            return $result;
        }
    }
}
if (!function_exists('post_cg_create_pdf_preview_backend')) {
    function post_cg_create_pdf_preview_backend($WpUpload = 0, $realId = 0, $cg_base_64 = '', $isFromFrontendUpload = false) {
        cg_require_backend_access();
        $result = cg_create_pdf_preview_internal($WpUpload, $realId, $cg_base_64, $isFromFrontendUpload);
        if (!empty($result['ok']) && !$isFromFrontendUpload && !empty($result['preview_url'])) {
            echo 'cg_guid###' . $result['preview_url'] . '###cg_guid_end';
        } elseif (empty($result['ok'])) {
            echo 'cg_error###' . $result['error'] . '###cg_error_end';
        }
    }
}

// move to another gallery get inputs
add_action('wp_ajax_post_cg_move_to_another_gallery_get_inputs', 'post_cg_move_to_another_gallery_get_inputs');
if (!function_exists('post_cg_move_to_another_gallery_get_inputs')) {
	function post_cg_move_to_another_gallery_get_inputs() {

        cg_backend_ajax_require_access_json();
        $_POST = cg1l_sanitize_post($_POST);

        $MoveFromGalleryID = (!empty($_POST['cgMoveFromGalleryID'])) ? absint($_POST['cgMoveFromGalleryID']) : 0;
        $galleryHash = (!empty($_POST['cgGalleryHash'])) ? $_POST['cgGalleryHash'] : '';
        cg_backend_ajax_validate_gallery_hash_json($MoveFromGalleryID, $galleryHash);

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

        wp_send_json_success(array(
            'allCategoriesByGalleryID' => $allCategoriesByGalleryIDArray,
            'galleryIDs' => $galleryIDs,
            'contact_forms_by_gallery_id' => $contact_forms_by_gallery_id
        ));

        }
	}

// move to another gallery
add_action('wp_ajax_post_cg_move_to_another_gallery', 'post_cg_move_to_another_gallery');
if (!function_exists('post_cg_move_to_another_gallery')) {
	function post_cg_move_to_another_gallery()
	{
		cg_backend_ajax_require_access_json();
		contest_gal1ery_db_check();

		$_POST = cg1l_sanitize_post($_POST);

		$cgMoveRealId = (!empty($_POST['cgMoveRealId'])) ? absint($_POST['cgMoveRealId']) : 0;
		$InGalleryIDtoMove = (!empty($_POST['cg_in_gallery_id_to_move'])) ? absint($_POST['cg_in_gallery_id_to_move']) : 0;
		$MoveFromGalleryID = (!empty($_POST['cgMoveFromGalleryID'])) ? absint($_POST['cgMoveFromGalleryID']) : 0;
		$cgMoveCategory = (!empty($_POST['cgMoveCategory'])) ? absint($_POST['cgMoveCategory']) : 0;
		$MoveAssignsRaw = (!empty($_POST['cgMoveAssigns']) && is_array($_POST['cgMoveAssigns'])) ? $_POST['cgMoveAssigns'] : array();
		$galleryHash = (!empty($_POST['cgGalleryHash'])) ? $_POST['cgGalleryHash'] : '';

		cg_backend_ajax_validate_gallery_hash_json($MoveFromGalleryID, $galleryHash);

		if (empty($cgMoveRealId) || empty($InGalleryIDtoMove) || empty($MoveFromGalleryID)) {
			cg_backend_ajax_error_json('Missing move request data.', 400, 'cg_missing_move_data');
		}

		if ($InGalleryIDtoMove == $MoveFromGalleryID) {
			cg_backend_ajax_error_json('Entry can not be moved to the same gallery.', 400, 'cg_same_gallery_move');
		}

		global $wpdb;
		$table_posts = $wpdb->prefix . "posts";
		$tablename = $wpdb->prefix . "contest_gal1ery";
		$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
		$tablename_comments = $wpdb->prefix . "contest_gal1ery_comments";
		$tablename_entries = $wpdb->prefix . 'contest_gal1ery_entries';
		$tablename_ip = $wpdb->prefix . "contest_gal1ery_ip";
		$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
		$tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";

		$sourceGalleryExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tablename_options WHERE id = %d", $MoveFromGalleryID));
		if (empty($sourceGalleryExists)) {
			cg_backend_ajax_error_json('Source gallery does not exist.', 400, 'cg_source_gallery_missing');
		}

		$optionsInGalleryToMove = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_options WHERE id = %d ORDER BY id DESC LIMIT 0, 1", $InGalleryIDtoMove));
		if (empty($optionsInGalleryToMove)) {
			cg_backend_ajax_error_json('Target gallery does not exist.', 400, 'cg_target_gallery_missing');
		}

		$rowToMove = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename WHERE id = %d AND GalleryID = %d", $cgMoveRealId, $MoveFromGalleryID));
		if (empty($rowToMove)) {
			cg_backend_ajax_error_json('Entry does not belong to the selected source gallery.', 400, 'cg_entry_source_mismatch');
		}

		if (!empty($rowToMove->EcommerceEntry)) {
			cg_backend_ajax_error_json('E-commerce entries can not be moved to another gallery.', 400, 'cg_ecommerce_entry_move_blocked');
		}

		if (!empty($cgMoveCategory)) {
			$categoryExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tablename_categories WHERE id = %d AND GalleryID = %d", $cgMoveCategory, $InGalleryIDtoMove));
			if (empty($categoryExists)) {
				cg_backend_ajax_error_json('Selected target category does not belong to the target gallery.', 400, 'cg_target_category_mismatch');
			}
		}

		$MoveAssigns = array();
		if (!empty($MoveAssignsRaw)) {
			$formInputs = $wpdb->get_results($wpdb->prepare("SELECT id, GalleryID, Field_Type FROM $tablename_form_input WHERE GalleryID IN (%d,%d)", $MoveFromGalleryID, $InGalleryIDtoMove));
			$sourceFields = array();
			$targetFields = array();
			foreach ($formInputs as $formInput) {
				if (absint($formInput->GalleryID) == $MoveFromGalleryID) {
					$sourceFields[absint($formInput->id)] = $formInput->Field_Type;
				} elseif (absint($formInput->GalleryID) == $InGalleryIDtoMove) {
					$targetFields[absint($formInput->id)] = $formInput->Field_Type;
				}
			}

			$allowedMoveFieldTypes = array('date-f', 'text-f', 'url-f', 'email-f', 'comment-f', 'select-f', 'radio-f', 'chk-f');
			$usedTargetFields = array();
			foreach ($MoveAssignsRaw as $FromInput => $ToInput) {
				$FromInput = absint($FromInput);
				$ToInput = absint($ToInput);

				if (empty($FromInput) || empty($ToInput)) {
					cg_backend_ajax_error_json('Invalid field assignment data.', 400, 'cg_invalid_move_assignment');
				}
				if (empty($sourceFields[$FromInput]) || empty($targetFields[$ToInput])) {
					cg_backend_ajax_error_json('Field assignment does not belong to the selected galleries.', 400, 'cg_move_assignment_gallery_mismatch');
				}
				if (!in_array($sourceFields[$FromInput], $allowedMoveFieldTypes, true) || $sourceFields[$FromInput] != $targetFields[$ToInput]) {
					cg_backend_ajax_error_json('Field assignment types do not match.', 400, 'cg_move_assignment_type_mismatch');
				}
				if (!empty($usedTargetFields[$ToInput])) {
					cg_backend_ajax_error_json('A target field can only be assigned once.', 400, 'cg_move_assignment_duplicate_target');
				}

				$MoveAssigns[$FromInput] = $ToInput;
				$usedTargetFields[$ToInput] = true;
			}
		}

		$insert_id = cg_copy_table_row('contest_gal1ery',$cgMoveRealId, $valueCollect = [], $cgCopyType = '');
		if (empty($insert_id)) {
			cg_backend_ajax_error_json('Entry could not be copied to the target gallery.', 500, 'cg_move_copy_failed');
		}

		$Version = cg_get_version_for_scripts();

		$updated = $wpdb->update(
			"$tablename",
			array('Version' => $Version,'GalleryID' => $InGalleryIDtoMove),
			array('id' => $insert_id),
			array('%s','%d'),
			array('%d')
		);

		if ($updated === false) {
			$wpdb->delete($tablename, array('id' => $insert_id), array('%d'));
			cg_backend_ajax_error_json('Copied entry could not be assigned to the target gallery.', 500, 'cg_move_assign_gallery_failed');
		}

		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename WHERE id = %d", $insert_id));
		if (empty($row)) {
			cg_backend_ajax_error_json('Moved entry could not be loaded.', 500, 'cg_move_row_missing');
		}

		$wpdb->delete($tablename, array('id' => $cgMoveRealId, 'GalleryID' => $MoveFromGalleryID), array('%d','%d'));

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
		if(!empty($optionsInGalleryToMove->WpPageParent)) {
			$post_title = substr($row->NamePic,0,100);
			cg_create_wp_pages($InGalleryIDtoMove,$insert_id,$post_title,$optionsInGalleryToMove,$optionsInGalleryToMove->Version);
		}

		$wpdb->update($tablename, array('Category' => $cgMoveCategory), array('id' => $insert_id), array('%d'), array('%d'));

		$wpdb->update($tablename_ip, array('pid' => $insert_id, 'GalleryID' => $InGalleryIDtoMove), array('pid' => $cgMoveRealId, 'GalleryID' => $MoveFromGalleryID), array('%d','%d'), array('%d','%d'));
		$wpdb->update($tablename_comments, array('pid' => $insert_id, 'GalleryID' => $InGalleryIDtoMove), array('pid' => $cgMoveRealId, 'GalleryID' => $MoveFromGalleryID), array('%d','%d'), array('%d','%d'));
		$wpdb->update($tablename_entries, array('pid' => $insert_id, 'GalleryID' => $InGalleryIDtoMove), array('pid' => $cgMoveRealId, 'GalleryID' => $MoveFromGalleryID), array('%d','%d'), array('%d','%d'));

		$input_ids_entries_to_delete = $wpdb->get_results($wpdb->prepare("SELECT id, f_input_id FROM $tablename_entries WHERE pid = %d", $insert_id));
		$input_ids_entries_to_delete_array = [];
		foreach ($input_ids_entries_to_delete as $entry){
			$input_ids_entries_to_delete_array[$entry->f_input_id] = $entry->id;
		}

		if(!empty($MoveAssigns)){
			foreach ($MoveAssigns as $FromInput => $ToInput){
				$wpdb->query($wpdb->prepare("UPDATE $tablename_entries SET f_input_id = %d WHERE pid = %d AND f_input_id = %d", $ToInput, $insert_id, $FromInput));
				if(isset($input_ids_entries_to_delete_array[$FromInput])){
					unset($input_ids_entries_to_delete_array[$FromInput]);
				}
			}
		}

		foreach ($input_ids_entries_to_delete_array as $f_input_id => $entryId) {
			$wpdb->query($wpdb->prepare("DELETE FROM $tablename_entries WHERE id = %d", $entryId));
		}

		$wp_upload_dir = wp_upload_dir();
		// unlink activated entries if exists
		if(file_exists($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-data/image-data-".$cgMoveRealId.".json")){
			unlink($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-data/image-data-".$cgMoveRealId.".json");
		}
		if(file_exists($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-stats/image-stats-".$cgMoveRealId.".json")){
			unlink($wp_upload_dir['basedir'] . "/contest-gallery/gallery-id-".$MoveFromGalleryID."/json/image-stats/image-stats-".$cgMoveRealId.".json");
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
			$GalleryID = $row->GalleryID;
			$rowForJson = $wpdb->get_row($wpdb->prepare(
				"SELECT DISTINCT $table_posts.*, $tablename.* FROM $table_posts, $tablename WHERE
				 (($tablename.id = %d) AND $tablename.GalleryID = %d AND $tablename.Active = '1' and $table_posts.ID = $tablename.WpUpload)
				 OR
				 (($tablename.id = %d) AND $tablename.GalleryID = %d AND $tablename.Active = '1' AND $tablename.WpUpload = 0)
				 GROUP BY $tablename.id ORDER BY $tablename.id DESC LIMIT 0, 1",
				$row->id, $GalleryID, $row->id, $GalleryID
			));
			if (!empty($rowForJson)) {
				cg_create_json_files_when_activating($InGalleryIDtoMove,$rowForJson);
			}
		}

		wp_send_json_success(array(
			'entry_id' => $cgMoveRealId,
			'new_entry_id' => $insert_id,
			'target_gallery_id' => $InGalleryIDtoMove
		));
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

        } elseif (empty($_POST['cgVersionScripts']) && !empty($_POST['cgGalleryFormSubmit'])) { // IMPORTANT that data is not saved when wrong data is send when updateting 109900

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
		cg_backend_ajax_require_access_json();
		$_POST = cg1l_sanitize_post($_POST);

		$GalleryID = (!empty($_POST['GalleryID'])) ? absint($_POST['GalleryID']) : 0;
		$galleryHash = (!empty($_POST['cgGalleryHash'])) ? $_POST['cgGalleryHash'] : '';
		$cgUserSearch = (!empty($_POST['cgUserSearch'])) ? sanitize_text_field($_POST['cgUserSearch']) : '';

		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		global $wpdb;

		$wpUsers = $wpdb->base_prefix . "users";
		if (!empty($cgUserSearch)) {
			$like = '%' . $wpdb->esc_like($cgUserSearch) . '%';
			$selectWPusers = $wpdb->get_results($wpdb->prepare("SELECT ID, user_login FROM $wpUsers WHERE ID > 0 AND (user_login LIKE %s OR user_email LIKE %s) ORDER BY user_login ASC LIMIT 20", $like, $like));
		} else {
			$selectWPusers = $wpdb->get_results("SELECT ID, user_login FROM $wpUsers WHERE ID > 0 ORDER BY ID ASC LIMIT 20");
		}

		$html = "<select id='cgAttachToAnotherUserSelect' name='cgAttachToAnotherUserId' class='cg_no_outline_and_shadow_on_focus'>";
		if (empty($selectWPusers)) {
			$html .= "<option value='' disabled selected>No users found</option>";
		} else {
			$isFirst = true;
			foreach ($selectWPusers as $user){
				$selected = ($isFirst) ? ' selected' : '';
				$html .= "<option value='" . esc_attr($user->ID) . "' data-user_login='" . esc_attr($user->user_login) . "'" . $selected . ">" . esc_html($user->user_login) . " (ID: " . esc_html($user->ID) . ")</option>";
				$isFirst = false;
			}
		}
		$html .= "</select>";

		wp_send_json_success(array(
			'html' => $html,
			'has_results' => (!empty($selectWPusers))
		));
	}
}
// attach to another user select --- END

// backend gallery user filter options
add_action('wp_ajax_post_cg_backend_gallery_user_filter_options', 'post_cg_backend_gallery_user_filter_options');
if (!function_exists('post_cg_backend_gallery_user_filter_options')) {
	function post_cg_backend_gallery_user_filter_options()
	{
		cg_backend_ajax_require_access_json();
		$_POST = cg1l_sanitize_post($_POST);

		$GalleryID = (!empty($_POST['GalleryID'])) ? absint($_POST['GalleryID']) : 0;
		$galleryHash = (!empty($_POST['cgGalleryHash'])) ? $_POST['cgGalleryHash'] : '';
		$cgUserSearch = (!empty($_POST['cgUserSearch'])) ? sanitize_text_field($_POST['cgUserSearch']) : '';
		$selectedValue = (isset($_POST['selectedValue']) && !is_array($_POST['selectedValue'])) ? sanitize_text_field($_POST['selectedValue']) : '';

		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		global $wpdb;

		$tablename = $wpdb->prefix . "contest_gal1ery";
		$wpUsers = $wpdb->base_prefix . "users";

		$formatEntryCountText = function($count){
			$count = intval($count);
			return $count . ' ' . (($count === 1) ? 'entry' : 'entries');
		};

		$totalEntriesCount = intval($wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM $tablename WHERE GalleryID = %d",
			array($GalleryID)
		)));

		$withoutUserCount = intval($wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM $tablename WHERE GalleryID = %d AND WpUserId = 0",
			array($GalleryID)
		)));

		$options = array();
		$selectedOption = array(
			'value' => '',
				'label' => 'All user entries (' . $formatEntryCountText($totalEntriesCount) . ')'
		);
		$resolvedValue = '';
		$seenValues = array();

		$hasWithoutUser = ($withoutUserCount > 0);

		$selectedUserId = 0;
		if($selectedValue !== '0'){
			$selectedUserId = absint($selectedValue);
		}

		$selectedUser = null;
		if($selectedValue === '0' && $hasWithoutUser){
			$resolvedValue = '0';
			$selectedOption = array(
				'value' => '0',
					'label' => 'Without registered user (' . $formatEntryCountText($withoutUserCount) . ')'
			);
		} elseif(!empty($selectedUserId)) {
			$selectedUser = $wpdb->get_row($wpdb->prepare(
				"SELECT $wpUsers.ID, $wpUsers.user_login, COUNT($tablename.id) AS entries_count
				FROM $tablename
				INNER JOIN $wpUsers ON $wpUsers.ID = $tablename.WpUserId
				WHERE $tablename.GalleryID = %d AND $tablename.WpUserId = %d
				GROUP BY $wpUsers.ID, $wpUsers.user_login
				LIMIT 1",
				array($GalleryID, $selectedUserId)
			));
			if(!empty($selectedUser)){
				$resolvedValue = (string)$selectedUser->ID;
				$selectedOption = array(
					'value' => $resolvedValue,
					'label' => $selectedUser->user_login . ' (' . $formatEntryCountText($selectedUser->entries_count) . ')'
				);
			}
		}

		$options[] = array(
			'value' => '',
				'label' => 'All user entries (' . $formatEntryCountText($totalEntriesCount) . ')',
			'selected' => ($resolvedValue === '')
		);
		$seenValues[] = '';

		if($hasWithoutUser){
			$options[] = array(
				'value' => '0',
				'label' => 'Without registered user (' . $formatEntryCountText($withoutUserCount) . ')',
				'selected' => ($resolvedValue === '0')
			);
			$seenValues[] = '0';
		}

		$query = "SELECT $wpUsers.ID, $wpUsers.user_login, COUNT($tablename.id) AS entries_count
			FROM $tablename
			INNER JOIN $wpUsers ON $wpUsers.ID = $tablename.WpUserId
			WHERE $tablename.GalleryID = %d AND $tablename.WpUserId > 0";

		$queryArgs = array($GalleryID);

		if(!empty($cgUserSearch)){
			$like = '%' . $wpdb->esc_like($cgUserSearch) . '%';
			$query .= " AND ($wpUsers.user_login LIKE %s OR $wpUsers.user_email LIKE %s OR $wpUsers.user_nicename LIKE %s OR $wpUsers.display_name LIKE %s)";
			$queryArgs[] = $like;
			$queryArgs[] = $like;
			$queryArgs[] = $like;
			$queryArgs[] = $like;
		}

		$query .= " GROUP BY $wpUsers.ID, $wpUsers.user_login ORDER BY $wpUsers.user_login ASC LIMIT 100";

		$selectWPusers = $wpdb->get_results($wpdb->prepare($query, $queryArgs));

		if(!empty($selectWPusers)){
			foreach($selectWPusers as $user){
				$userValue = (string)$user->ID;
				$options[] = array(
					'value' => $userValue,
					'label' => $user->user_login . ' (' . $formatEntryCountText($user->entries_count) . ')',
					'selected' => ($resolvedValue === $userValue)
				);
				$seenValues[] = $userValue;
			}
		}

		if($resolvedValue !== '' && !in_array($resolvedValue, $seenValues, true) && !empty($selectedOption['label'])){
			$options[] = array(
				'value' => $selectedOption['value'],
				'label' => $selectedOption['label'],
				'selected' => true
			);
		}

		wp_send_json_success(array(
			'options' => $options,
			'resolved_value' => $resolvedValue,
			'selected_option' => $selectedOption
		));
	}
}
// backend gallery user filter options --- END

// attach to another user
add_action('wp_ajax_post_cg_attach_to_another_user', 'post_cg_attach_to_another_user');
if (!function_exists('post_cg_attach_to_another_user')) {
	function post_cg_attach_to_another_user()
	{
		cg_backend_ajax_require_access_json();
		$_POST = cg1l_sanitize_post($_POST);

		global $wpdb;

		$tablename = $wpdb->prefix . "contest_gal1ery";
		$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
		$table_posts = $wpdb->prefix . "posts";

		$WpUserId = (isset($_POST['cgAttachToAnotherUserId'])) ? absint($_POST['cgAttachToAnotherUserId']) : 0;
		$pid = (!empty($_POST['cgEntryId'])) ? absint($_POST['cgEntryId']) : 0;
		$GalleryID = (!empty($_POST['GalleryID'])) ? absint($_POST['GalleryID']) : 0;
		$galleryHash = (!empty($_POST['cgGalleryHash'])) ? $_POST['cgGalleryHash'] : '';

		cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

		if (empty($pid) || empty($GalleryID)) {
			cg_backend_ajax_error_json('Missing user assignment data.', 400, 'cg_missing_attach_data');
		}

		$galleryExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tablename_options WHERE id = %d", $GalleryID));
		if (empty($galleryExists)) {
			cg_backend_ajax_error_json('Gallery does not exist.', 400, 'cg_attach_gallery_missing');
		}

		$rowToUpdate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename WHERE id = %d AND GalleryID = %d", $pid, $GalleryID));
		if (empty($rowToUpdate)) {
			cg_backend_ajax_error_json('Entry does not belong to the selected gallery.', 400, 'cg_attach_entry_gallery_mismatch');
		}

		$user_login = '';
		if (!empty($WpUserId)) {
			$wpUser = get_user_by('id', $WpUserId);
			if (empty($wpUser)) {
				cg_backend_ajax_error_json('Selected user does not exist.', 400, 'cg_attach_user_missing');
			}
			$user_login = $wpUser->user_login;
		}

		$updated = $wpdb->update(
			$tablename,
			array('WpUserId' => $WpUserId),
			array('id' => $pid, 'GalleryID' => $GalleryID),
			array('%d'),
			array('%d','%d')
		);

		if ($updated === false) {
			cg_backend_ajax_error_json('User assignment could not be saved.', 500, 'cg_attach_update_failed');
		}

		if($rowToUpdate->Active==1){
			$row = $wpdb->get_row($wpdb->prepare(
				"SELECT DISTINCT $table_posts.*, $tablename.* FROM $table_posts, $tablename WHERE
				  (($tablename.id = %d) AND $tablename.GalleryID = %d AND $tablename.Active = '1' and $table_posts.ID = $tablename.WpUpload)
				  OR
				  (($tablename.id = %d) AND $tablename.GalleryID = %d AND $tablename.Active = '1' AND $tablename.WpUpload = 0)
				  GROUP BY $tablename.id ORDER BY $tablename.id DESC LIMIT 0, 1",
				$pid, $GalleryID, $pid, $GalleryID
			));
			if (!empty($row)) {
				$row->WpUserId = $WpUserId;
				cg_create_json_files_when_activating($GalleryID,$row);
			}
		}

		wp_send_json_success(array(
			'entry_id' => $pid,
			'user_id' => $WpUserId,
			'user_login' => $user_login,
			'detached' => empty($WpUserId)
		));
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

        contest_gal1ery_db_check();
        cg_backend_ajax_require_access_json();

        if (empty($_POST['cgSellContainer']) || !is_array($_POST['cgSellContainer'])) {
            cg_backend_ajax_error_json('Missing sale settings data.', 400, 'cg_missing_sale_data');
        }

        $cgSellContainer = $_POST['cgSellContainer'];
        $GalleryID = (isset($cgSellContainer['GalleryID'])) ? absint($cgSellContainer['GalleryID']) : 0;
        $realId = (isset($cgSellContainer['realId'])) ? absint($cgSellContainer['realId']) : 0;
        $saleAction = (isset($cgSellContainer['saleAction'])) ? sanitize_text_field($cgSellContainer['saleAction']) : '';
        $galleryHash = (isset($_POST['cgGalleryHash'])) ? sanitize_text_field($_POST['cgGalleryHash']) : '';

        cg_backend_ajax_validate_gallery_hash_json($GalleryID, $galleryHash);

        if (empty($realId) || empty($GalleryID) || !in_array($saleAction, array('activate', 'deactivate'), true)) {
            cg_backend_ajax_error_json('Missing sale settings data.', 400, 'cg_missing_sale_data');
        }

        global $wpdb;
        $tablename = $wpdb->prefix . "contest_gal1ery";
        $entry = $wpdb->get_row($wpdb->prepare("SELECT id, GalleryID, EcommerceEntry FROM $tablename WHERE id = %d", $realId));

        if (empty($entry)) {
            cg_backend_ajax_error_json('Sale entry does not exist.', 400, 'cg_sale_entry_missing');
        }

        if (absint($entry->GalleryID) !== $GalleryID) {
            cg_backend_ajax_error_json('Entry does not belong to the selected gallery.', 400, 'cg_sale_entry_gallery_mismatch');
        }

        if ($saleAction === 'deactivate' && empty($entry->EcommerceEntry)) {
            cg_backend_ajax_error_json('Sale entry is not active.', 400, 'cg_sale_entry_not_active');
        }

        // has to be unsanitized because of the url eventually configured by user
        $AllUploadsUsedText = '';
        if (isset($_POST['cgSellContainer']['AllUploadsUsedText'])) {
            $AllUploadsUsedText = contest_gal1ery_htmlentities_and_preg_replace($_POST['cgSellContainer']['AllUploadsUsedText']);
        }
        $_POST = cg1l_sanitize_post($_POST);
        $_POST['cgSellContainer']['AllUploadsUsedText'] = $AllUploadsUsedText;

        cg_ecommerce_sale_conf();
        die;
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

add_action( 'wp_ajax_post_cg_get_current_nonce', 'post_cg_get_current_nonce' );
if(!function_exists('post_cg_get_current_nonce')){
    function post_cg_get_current_nonce() {
        cg_require_backend_access(false);
        $nonce = wp_create_nonce('cg_nonce');
        ?>
        <script data-cg-processing="true">
            cgJsClassAdmin.index.vars.cg_current_nonce = <?php echo json_encode($nonce); ?>;
            cgJsClassAdmin.index.vars.cg_current_nonce_time = new Date().getTime();
        </script>
        <?php
    }
}

add_action( 'wp_ajax_post_cg_get_wp_user_meta', 'post_cg_get_wp_user_meta' );
if(!function_exists('post_cg_get_wp_user_meta')){
    function post_cg_get_wp_user_meta() {

        cg_require_backend_access();

        $WpUserId = absint($_POST['cg_wp_user_id']);
        $firstName = get_user_meta( $WpUserId, 'first_name', true );
        if(empty($firstName)){
            $firstName = 'Not set';
        }
        $lastName = get_user_meta( $WpUserId, 'last_name', true );
        if(empty($lastName)){
            $lastName = 'Not set';
        }
        ?>
        <script data-cg-processing="true">
            var WpUserId = <?php echo json_encode($WpUserId); ?>;
            cgJsClassAdmin.gallery.vars.WpUsersData[WpUserId] = {};
            cgJsClassAdmin.gallery.vars.WpUsersData[WpUserId].firstName = <?php echo json_encode($firstName); ?>;
            cgJsClassAdmin.gallery.vars.WpUsersData[WpUserId].lastName = <?php echo json_encode($lastName); ?>;
        </script>
        <?php
    }
}


add_action( 'wp_ajax_post_cg1l_delete_unconfirmed_mail', 'post_cg1l_delete_unconfirmed_mail' );
if(!function_exists('post_cg1l_delete_unconfirmed_mail')){
    function post_cg1l_delete_unconfirmed_mail() {
        cg_require_backend_access();
        cg1l_delete_unconfirmed_user(sanitize_text_field($_POST['cg_mail']));
    }
}

add_action( 'wp_ajax_post_cg_list_unconfirmed_mails', 'post_cg_list_unconfirmed_mails' );
if(!function_exists('post_cg_list_unconfirmed_mails')){
    function post_cg_list_unconfirmed_mails() {

        cg_require_backend_access();

        global $wpdb;
        $tablename_mails = $wpdb->prefix . "contest_gal1ery_mails";

        $mail = cg1l_sanitize_method($_POST['cg_mail']);

        $mails = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
         FROM $tablename_mails
         WHERE ReceiverMail = %s AND WpUserId = 0 AND (MailType = 'registry-resend-frontend' OR MailType = 'registry-resend-backend' OR MailType = 'registry-frontend')
         ORDER BY id DESC",
                $mail
            ),
            ARRAY_A
        );

        if (!is_array($mails)) {
            $mails = [];
        }else{
            $typeMap = [
                'registry-resend-frontend' => 'Resent confirmation (Frontend)',
                'registry-resend-backend'  => 'Resent confirmation (Backend)',
                'registry-frontend'        => 'Registration confirmation (Frontend)',
            ];

            foreach ($mails as $index => $mail) {

                // Add formatted date
                $DateTime = cg_get_time_based_on_wp_timezone_conf($mail['Tstamp'], 'd-M-Y H:i:s');

                // Add formatted body
                $mails[$index]['Body'] = contest_gal1ery_convert_for_html_output_without_nl2br($mail['Body']);

                // Add human-friendly MailType
                if (isset($typeMap[$mail['MailType']])) {
                    $mails[$index]['MailTypeLabel'] = $typeMap[$mail['MailType']];
                } else {
                   if(!empty($mail['MailType'])){
                       $mails[$index]['MailTypeLabel'] = ucfirst(str_replace('-', ' ', $mail['MailType']));
                   }else{
                       $mails[$index]['MailTypeLabel'] = '';
                   }
                }

                $mails[$index]['DateTime'] = $DateTime.' — '.$mails[$index]['MailTypeLabel'];

            }

        }

        ?>
        <script data-cg-processing="true">
            cgJsClassAdmin.gallery.vars.cg_unconfirmed_mails = <?php echo json_encode($mails); ?>;
        </script>
        <?php
    }
}

// post_cg_get_current_permalinks
add_action('wp_ajax_post_cg1l_get_management_show_users', 'post_cg1l_get_management_show_users');
if (!function_exists('post_cg1l_get_management_show_users')) {
    function post_cg1l_get_management_show_users() {

        cg_require_backend_access();
        /*echo "<pre>";
            print_r($_POST);
        echo "</pre>";die;*/
      //  if(empty($_POST['cg_form_submit'])){
            $_GET = array_merge($_GET, $_POST);
        //}
        $cgProVersion = contest_gal1ery_key_check();
        if(!$cgProVersion){
            $cgProFalse = 'cg-pro-false';
        }else{
            $cgProFalse = '';
        }
        include(__DIR__.'/../v10/v10-admin/users/admin/users/management.php');

    }
}

// post_cg1l_get_unconfirmed_users
add_action('wp_ajax_post_cg1l_get_unconfirmed_users', 'post_cg1l_get_unconfirmed_users');
if (!function_exists('post_cg1l_get_unconfirmed_users')) {
    function post_cg1l_get_unconfirmed_users() {

        cg_require_backend_access();
        /*echo "<pre>";
            print_r($_POST);
        echo "</pre>";die;*/
        //  if(empty($_POST['cg_form_submit'])){
        $_GET = array_merge($_GET, $_POST);
        //}
        $cgProVersion = contest_gal1ery_key_check();
        if(!$cgProVersion){
            $cgProFalse = 'cg-pro-false';
        }else{
            $cgProFalse = '';
        }
        include(__DIR__.'/../v10/v10-admin/users/admin/users/unconfirmed-users.php');

    }
}
