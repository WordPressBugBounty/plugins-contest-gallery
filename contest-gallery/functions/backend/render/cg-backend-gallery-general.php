<?php

if(!function_exists('cg_move_to_another_gallery_container')){
	function cg_move_to_another_gallery_container($GalleryID){

		echo "<div id='cgMoveToAnotherGalleryContainer' class='cg_backend_action_container cg_hide' style='min-height: unset;'>
<span class='cg_message_close'></span>";
		$urlAfterMove = '?page='.cg_get_version().'/index.php&edit_gallery=true';
		?>
        <form enctype="multipart/form-data" id="cg_move_to_another_gallery_form" action='<?php echo '?page="'.cg_get_version().'"/index.php'; ?>' method='POST'>
            <input type='hidden' name='cgGalleryHash' value='<?php echo md5(wp_salt( 'auth').'---cngl1---'.$GalleryID);?>'>
            <input type='hidden' name='cgMoveFromGalleryID' value='<?php echo $GalleryID;?>'>
            <input type='hidden' name='cgMoveRealId' value='<?php echo $GalleryID;?>'>
            <input type='hidden' name='action' value='post_cg_move_to_another_gallery'>
			<?php

		echo "<div id='cgMoveToAnotherGalleryLoader' class='cg-lds-dual-ring-gallery-hide cg_hide'></div>";
		echo "<div id='cgMoveToAnotherGalleryContent' class='cg_hide'>";
        echo '<span class="cg_hide"><a href="'.$urlAfterMove.'"  id="cgMoveUrlAfterMove" ></a></span>';

		echo "<div><b>Entry ID <span id='cgMoveEntryId'></span></b><br>Select in which gallery to move</div>";

				        echo "<select name='cg_in_gallery_id_to_move' id='cg_in_gallery_id_to_move_select' disabled class='cg_disabled_background_color_e0e0e0 cg_action_select' ><option>No another galleries available</option></select>";

					echo '<p id="cgMoveAssignFields">Assign fields to gallery <span id="cgMoveToAnotherGalleryId"></span> fields</p>';
					echo '<div  id="cgMoveToAnotherGalleryCompare"></div>';

			echo '<div id="cg_in_gallery_id_to_move_go_checkbox_container" class="cg_hover_effect   cg_disabled_background_color_e0e0e0" ><input type="checkbox" id="cg_in_gallery_id_to_move_go_checkbox" style="margin-top: unset;margin-right: 5px;"><label for="cg_in_gallery_id_to_move_go_checkbox" >Open gallery to which moved</label>
</div>';

        echo '<div  id="cgMoveToAnotherGallerySubmitContainer">
            <div  id="cgMoveToAnotherGallerySubmit" class="cg_backend_button_gallery_action cg_disabled_background_color_e0e0e0" style="margin-left: auto; margin-right: auto;">Move to selected gallery</div>
        </div>';

		echo "</div>";
        ?>
        </form>
		<?php
        echo "</div>";

	}
}


if(!function_exists('cg_is_for_wp_page_title_unchecked')){
	function cg_is_for_wp_page_title_unchecked($GalleryID,$bloginfo_wpurl,$CgEntriesOwnSlugName,$dbGalleryVersion){

        if(floatval($dbGalleryVersion)>=24){
            echo <<<HEREDOC
<div id='cgIsForWpPageTitleUnchecked' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_main_options' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
	        <div class='cg_view_options_row ' >
	            <div class='cg_view_option cg_view_option_full_width cg_border_radius_8_px '>
	                    <p>Unchecking<br><b>Use as entry page title</b><br>will change the URLs of all current and future entry pages<br><b>from</b><br>
	                  $bloginfo_wpurl/contest-galleries/contest-gallery-$GalleryID/<b>{input_field_content}</b><br><b>to</b><br>$bloginfo_wpurl/contest-galleries/contest-gallery-$GalleryID/<b>{filename}</b>
	                    </p>
	            </div>
	        </div>
	 </div>
 </div>
HEREDOC;
        }else{
		echo <<<HEREDOC
<div id='cgIsForWpPageTitleUnchecked' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_main_options' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
	        <div class='cg_view_options_row ' >
	            <div class='cg_view_option cg_view_option_full_width cg_border_radius_8_px '>
	                    <p>Unchecking<br><b>Use as entry page title</b><br>will change the URLs of all current and future entry pages<br><b>from</b><br>
	                  $bloginfo_wpurl/$CgEntriesOwnSlugName/contest-gallery-id-$GalleryID/<b>{input_field_content}</b><br><b>to</b><br>$bloginfo_wpurl/$CgEntriesOwnSlugName/contest-gallery-id-$GalleryID/<b>{filename}</b>
	                    </p>
	            </div>
	        </div>
	 </div>
 </div>
HEREDOC;
	}

	}
}
if(!function_exists('cg_is_for_wp_page_title_checked')){
	function cg_is_for_wp_page_title_checked($GalleryID,$bloginfo_wpurl,$CgEntriesOwnSlugName,$dbGalleryVersion){

        if(floatval($dbGalleryVersion)>=24){
            echo <<<HEREDOC
<div id='cgIsForWpPageTitleChecked' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_main_options' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
	        <div class='cg_view_options_row ' >
	            <div class='cg_view_option cg_view_option_full_width cg_border_radius_8_px '>
	                    <p>Checking<br><b>Use as entry page title</b><br>will change the URLs of all current and future entry pages<br><b>from</b><br>
	                  $bloginfo_wpurl/contest-galleries/contest-gallery-$GalleryID/<b>{filename}</b><br><b>to</b><br>
$bloginfo_wpurl/contest-galleries/contest-gallery-$GalleryID/<b>{input_field_content}</b><br>
					if no <b>{input_field_content}</b> set then <b>{filename}</b> will be taken
	                    </p>
	            </div>
	        </div>
	 </div>
 </div>
HEREDOC;
        }else{
		echo <<<HEREDOC
<div id='cgIsForWpPageTitleChecked' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_main_options' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
	        <div class='cg_view_options_row ' >
	            <div class='cg_view_option cg_view_option_full_width cg_border_radius_8_px '>
	                    <p>Checking<br><b>Use as entry page title</b><br>will change the URLs of all current and future entry pages<br><b>from</b><br>
	                  $bloginfo_wpurl/$CgEntriesOwnSlugName/contest-gallery-id-$GalleryID/<b>{filename}</b><br><b>to</b><br>
$bloginfo_wpurl/$CgEntriesOwnSlugName/contest-gallery-id-$GalleryID/<b>{input_field_content}</b><br>
					if no <b>{input_field_content}</b> set then <b>{filename}</b> will be taken
	                    </p>
	            </div>
	        </div>
	 </div>
 </div>
HEREDOC;
	}

	}
}


if(!function_exists('cg_row_columns')){
	function cg_row_columns($GalleryID, $isUpload = false){
        if($isUpload){
            echo "<div id='cgUplCols' class='cg_hide cg_height_auto cg_backend_action_container cg_backend_action_container_high_overlay cg_upload_layout_dialog'>";
            echo "<span class='cg_message_close' role='button' tabindex='0' aria-label='Close row layout'></span>";
            echo "<div class='cg_sel_title'>Row layout</div><div class='cg_upload_layout_choices'>";
            $layouts = array('1' => 'Full width', '1-1' => '1/2 + 1/2', '2-1' => '2/3 + 1/3', '1-2' => '1/3 + 2/3', '1-1-1' => '1/3 + 1/3 + 1/3');
            foreach($layouts as $layout => $label){
                echo "<button type='button' class='cg_upload_layout_choice' data-cg-upload-layout='".esc_attr($layout)."' aria-pressed='false'><span class='cg_upload_layout_diagram' aria-hidden='true'>";
                foreach(explode('-', (string)$layout) as $weight){
                    echo "<span style='flex-grow:".intval($weight)."'></span>";
                }
                echo "</span><span>".esc_html($label)."</span></button>";
            }
            echo "</div><p class='cg_upload_layout_note'>Fields that no longer fit move to new rows below.</p></div>";
            echo "<div id='cgUploadRowToolsTemplate' class='cg_hide'><div class='cg_upload_row_tools'>";
            echo "<button type='button' class='cg_upload_row_layout_button'>Row layout <span class='cg_upload_row_layout_label'></span></button>";
            echo "<button type='button' class='cg_upload_row_move' data-cg-direction='up' title='Move row up' aria-label='Move row up'>&uarr;</button>";
            echo "<button type='button' class='cg_upload_row_move' data-cg-direction='down' title='Move row down' aria-label='Move row down'>&darr;</button>";
            echo "<button type='button' class='cg_add_col'>+ Add field beside</button>";
            echo "</div></div>";
            return;
        }

            echo <<<HEREDOC
<div id='cgUplCols' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_sel' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
             <div class='cg_sel_title' >
                Select how many columns in a row
            </div>
	        <div class='cg_row' style="gap: 20px;">
                   <div class='cg_row_col' id="cgUplColsOne">
                        <div class='cg_sel_col_title' >
                            One column
                        </div>
                        <div class='cg_row_col_row' >
                            <div class='cg_row_col_row_col cg_border_radius_8_px cg_sel_item' >
                            </div>
                        </div>
                    </div>
                   <div class='cg_row_col' id="cgUplColsTwo">
                        <div class='cg_sel_col_title' >
                            Two column
                        </div>
                        <div class='cg_row_col_row' >
                            <div class='cg_row_col_row_col cg_sel_item cg_border_border_top_left_radius_8_px cg_border_border_bottom_left_radius_8_px cg_border_right_none' >
                            </div>
                            <div class='cg_row_col_row_col cg_sel_item cg_border_border_top_right_radius_8_px cg_border_border_bottom_right_radius_8_px' >
                            </div>
                        </div>
                    </div>
                   <div class='cg_row_col' id="cgUplColsThree">
                        <div class='cg_sel_col_title' >
                            Three column
                        </div>
                        <div class='cg_row_col_row' >
                            <div class='cg_row_col_row_col cg_sel_item cg_border_border_top_left_radius_8_px cg_border_border_bottom_left_radius_8_px cg_border_right_none' >
                            </div>
                            <div class='cg_row_col_row_col cg_sel_item cg_border_right_none' >
                            </div>
                            <div class='cg_row_col_row_col cg_sel_item cg_border_border_top_right_radius_8_px cg_border_border_bottom_right_radius_8_px' >
                            </div>
                        </div>
                    </div>
            </div>
	        <div class='cg_row'>
                    <div class='cg_row_col' style="width: 100%;">
                        <div class='cg_sel_col_title' >
                            Delete row
                        </div>
                        <div class='cg_row_col_row cg_row_col_row_del' style="text-align: center;"  title="Delete row">
                        </div>
                    </div>
	        </div>
	 </div>
 </div>
HEREDOC;
	}
}

if(!function_exists('cg_delete_existing_fields_first')){
	function cg_delete_existing_fields_first($GalleryID){
            echo <<<HEREDOC
<div id='cgDelExistingFieldsFirst' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_sel' style='margin-bottom: 0;margin-top: 35px; box-shadow: unset;'>
             <div class='cg_sel_title' style="margin-bottom: 20px;">
                Delete existing fields in the row first
            </div>
	 </div>
 </div>
HEREDOC;
	}
}

if(!function_exists('cg_add_upl_field')){
	function cg_add_upl_field($GalleryID,$cgProFalse,$dbGalleryVersion){
        $cgProFalseV27 = $cgProFalse && floatval($dbGalleryVersion)>=27;
        $groups = array(
            array('title' => 'Form fields', 'class' => '', 'fields' => array(
                array('cgAddInputField', 'nf', 'Input', false),
                array('cgAddTextareaField', 'kf', 'Textarea', false),
                array('cgAddSelectField', 'se', 'Select', $cgProFalseV27),
                array('cgAddSelectCategoriesField', 'sec', 'Select category', false),
                array('cgAddDateField', 'dt', 'Date', $cgProFalse),
                array('cgAddEmailField', 'ef', 'Email', $cgProFalse),
                array('cgAddUrlField', 'url', 'URL', $cgProFalseV27),
                array('cgAddRadioField', 'ra', 'Radio buttons', $cgProFalse),
                array('cgAddCheckField', 'chk', 'Checkboxes', $cgProFalse)
            )),
            array('title' => 'Content & protection', 'class' => ' cg_upload_field_grid_secondary', 'fields' => array(
                array('cgAddHtmlField', 'ht', 'HTML', $cgProFalse),
                array('cgAddCheckAgreementField', 'cb', 'Check agreement', $cgProFalse),
                array('cgAddSimpleCaptchaField', 'caRo', 'Simple captcha', false),
                array('cgAddCaptchaRoReFieldField', 'caRoRe', 'Google reCAPTCHA', $cgProFalseV27)
            ))
        );
        cg_form_field_picker('cgAddUplField', $groups);
	}
}

if(!function_exists('cg_form_field_picker')){
	function cg_form_field_picker($id, $groups){
        echo "<div id='".esc_attr($id)."' class='cg_hide cg_height_auto cg_backend_action_container cg_backend_action_container_high_overlay cg_select_field_type cg_form_field_picker' role='dialog' aria-modal='true' aria-labelledby='".esc_attr($id)."Title' aria-describedby='".esc_attr($id)."Note'>";
        echo "<button type='button' class='cg_message_close' aria-label='Close'></button>";
        echo "<h2 id='".esc_attr($id)."Title'>Select field type</h2>";
        echo "<p id='".esc_attr($id)."Note'>Choose a field to add to your form.</p>";
        foreach($groups as $index => $group){
            $headingId = $id.'Group'.$index;
            echo "<section class='cg_upload_field_group' aria-labelledby='".esc_attr($headingId)."'>";
            echo "<h3 id='".esc_attr($headingId)."'>".esc_html($group['title'])."</h3>";
            echo "<div class='cg_upload_field_grid".esc_attr($group['class'])."'>";
            foreach($group['fields'] as $field){
                $proClass = $field[3] ? ' cg-pro-false' : '';
                $disabled = $field[3] ? " disabled aria-disabled='true'" : '';
                echo "<button type='button' class='cg_field cg_upload_field_choice".$proClass."' id='".esc_attr($field[0])."' data-cg-field='".esc_attr($field[1])."'".$disabled.">";
                echo "<span class='cg_row_col_row_col' aria-hidden='true'></span>";
                echo "<span class='cg_upload_field_label'>".esc_html($field[2])."</span>";
                if($field[3]){
                    echo "<span class='cg_upload_field_pro'>PRO</span>";
                }
                echo "</button>";
            }
            echo "</div></section>";
        }
        echo "</div>";
	}
}

if(!function_exists('cg_add_reg_field')){
	function cg_add_reg_field($GalleryID,$cgProFalse,$dbGalleryVersion){
        $groups = array();
        $wpFields = array();
        if(intval($dbGalleryVersion)>=14){
            $wpFields[] = array('cgWpFirstNameField', 'wpfn', 'WP First Name', false);
            $wpFields[] = array('cgWpLastNameField', 'wpln', 'WP Last Name', false);
        }
        if(intval($dbGalleryVersion)>=14 || $cgProFalse){
            $wpFields[] = array('cgProfileImageField', 'pi', 'Profile image', false);
        }
        if($wpFields){$groups[] = array('title' => 'WordPress fields', 'class' => '', 'fields' => $wpFields);}
        $fields = array(
            array('cgAddInputField', 'nf', 'Input', false),
            array('cgAddTextareaField', 'kf', 'Textarea', false),
            array('cgAddSelectField', 'se', 'Select', $cgProFalse)
        );
        if(intval($dbGalleryVersion)>=14){$fields[] = array('cgAddUrlField', 'url', 'URL', false);}
        $fields[] = array('cgAddRadioField', 'ra', 'Radio buttons', $cgProFalse);
        $fields[] = array('cgAddCheckField', 'chk', 'Checkboxes', $cgProFalse);
        $groups[] = array('title' => 'Form fields', 'class' => '', 'fields' => $fields);
        $groups[] = array('title' => 'Content & protection', 'class' => ' cg_upload_field_grid_secondary', 'fields' => array(
            array('cgAddHtmlField', 'ht', 'HTML', $cgProFalse),
            array('cgAddCheckAgreementField', 'cb', 'Check agreement', $cgProFalse),
            array('cgAddSimpleCaptchaField', 'caRo', 'Simple captcha', false),
            array('cgAddCaptchaRoReFieldField', 'caRoRe', 'Google reCAPTCHA', $cgProFalse)
        ));
        cg_form_field_picker('cgAddRegField', $groups);
	}
}

if(!function_exists('cg_mails_list')){
	function cg_mails_list(){
            echo <<<HEREDOC
<div id='cgMailsList' class='cg_hide cg_height_auto cg_backend_action_container  cg_overflow_y_hidden' style='width: fit-content;min-width: 600px; overflow-y: auto !important; max-width: 80%;min-height:70%;'>
	<span  class='cg_message_close'></span>
	<div class='cg_sel' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
             <div class='cg_sel_title' style="margin-bottom: 20px;" >
                Mails <span class="cg_mails_count" style="font-weight: normal;"></span>   <span class="cg_entry_id" style="font-weight: normal;"></span> 
            </div>
    </div>
HEREDOC;
    echo <<<HEREDOC
    <div class='cg_row cg_row_loader' >
            <div class='cg_row_col' >
                <div class="cg-lds-dual-ring-gallery-hide"></div>
            </div>
    </div>
    <div class='cg_row cg_row_error' >
            <div class='cg_row_col' style="align-items: start;">
                Error response:<span class="cg_row_error_message"></span> 
            </div>
    </div>
HEREDOC;
        echo <<<HEREDOC
        <div class='cg_row'>
               <div class='cg_row_col '>
                    <label for="cgMail">To</label>
                    <input type="text" id="cgMail" value="" />
                </div>
        </div>
HEREDOC;
        echo <<<HEREDOC
        <div class='cg_row'>
               <div class='cg_row_col '>
                    <label for="cgCcToSend">Cc</label>
                    <input type="text" id="cgCcToSend" value="" />
                </div>
        </div>
HEREDOC;
        echo <<<HEREDOC
        <div class='cg_row'>
               <div class='cg_row_col '>
                    <label for="cgBcc">Bcc</label>
                    <input type="text" id="cgBcc" value="" />
                </div>
        </div>
HEREDOC;
        echo <<<HEREDOC
        <div class='cg_row'>
               <div class='cg_row_col'>
                    <label for="cgMailBody">Body</label>
                    <div id="cgMailBody" ></div>
                </div>
        </div>
HEREDOC;
echo <<<HEREDOC
 </div>
HEREDOC;
	}
}

if (!function_exists('cg_backend_render_backend_loader')) {
    function cg_backend_render_backend_loader()
    {
        echo <<<HEREDOC
<div id="cgBackendLoader" class="cg-skeleton-box-container cg-backend-loader cg_do_not_remove_when_ajax_load" role="status" aria-live="polite" aria-label="Loading">
    <div class="cg-backend-loader-head">
        <div class="cg-skeleton-box cg-backend-loader-title"></div>
        <div class="cg-backend-loader-actions">
            <div class="cg-skeleton-box cg-backend-loader-action"></div>
            <div class="cg-skeleton-box cg-backend-loader-action"></div>
            <div class="cg-skeleton-box cg-backend-loader-action cg-backend-loader-action-short"></div>
        </div>
    </div>
    <div class="cg-backend-loader-toolbar">
        <div class="cg-skeleton-box cg-backend-loader-filter cg-backend-loader-filter-wide"></div>
        <div class="cg-skeleton-box cg-backend-loader-filter"></div>
        <div class="cg-skeleton-box cg-backend-loader-filter cg-backend-loader-filter-short"></div>
    </div>
    <div class="cg-backend-loader-list">
        <div class="cg-backend-loader-row">
            <div class="cg-skeleton-box cg-backend-loader-thumb"></div>
            <div class="cg-backend-loader-lines">
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-main"></div>
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-small"></div>
            </div>
            <div class="cg-skeleton-box cg-backend-loader-row-action"></div>
        </div>
        <div class="cg-backend-loader-row">
            <div class="cg-skeleton-box cg-backend-loader-thumb"></div>
            <div class="cg-backend-loader-lines">
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-main cg-backend-loader-line-medium"></div>
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-small cg-backend-loader-line-short"></div>
            </div>
            <div class="cg-skeleton-box cg-backend-loader-row-action"></div>
        </div>
        <div class="cg-backend-loader-row">
            <div class="cg-skeleton-box cg-backend-loader-thumb"></div>
            <div class="cg-backend-loader-lines">
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-main cg-backend-loader-line-wide"></div>
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-small"></div>
            </div>
            <div class="cg-skeleton-box cg-backend-loader-row-action"></div>
        </div>
        <div class="cg-backend-loader-row">
            <div class="cg-skeleton-box cg-backend-loader-thumb"></div>
            <div class="cg-backend-loader-lines">
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-main"></div>
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-small cg-backend-loader-line-short"></div>
            </div>
            <div class="cg-skeleton-box cg-backend-loader-row-action"></div>
        </div>
        <div class="cg-backend-loader-row cg-backend-loader-row-last">
            <div class="cg-skeleton-box cg-backend-loader-thumb"></div>
            <div class="cg-backend-loader-lines">
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-main cg-backend-loader-line-medium"></div>
                <div class="cg-skeleton-box cg-backend-loader-line cg-backend-loader-line-small"></div>
            </div>
            <div class="cg-skeleton-box cg-backend-loader-row-action"></div>
        </div>
    </div>
</div>
HEREDOC;
    }
}

if (!function_exists('cg_backend_gallery_render_reload_entry_loader')) {
    function cg_backend_gallery_render_reload_entry_loader()
    {
        echo <<<HEREDOC
<div id="cgReloadEntryLoader" class="cgReloadEntryLoader cg_reload_entry_loader cg_hide" role="status" aria-live="polite" aria-label="Reloading entry">
    <div class="cg_reload_entry_status_column">
        <div class="cg_skeleton_loader_on_page_load cg_reload_entry_status_badge"></div>
        <div class="cg_skeleton_loader_on_page_load cg_reload_entry_status_action"></div>
        <div class="cg_skeleton_loader_on_page_load cg_reload_entry_status_action cg_reload_entry_status_action_short"></div>
        <div class="cg_skeleton_loader_on_page_load cg_reload_entry_status_action"></div>
    </div>
    <div class="cg_reload_entry_card">
        <div class="cg_reload_entry_top_actions">
            <div class="cg_skeleton_loader_on_page_load cg_reload_entry_action_button"></div>
            <div class="cg_skeleton_loader_on_page_load cg_reload_entry_action_button cg_reload_entry_action_button_short"></div>
        </div>
        <div class="cg_reload_entry_main">
            <div class="cg_reload_entry_meta_panel">
                <div class="cg_skeleton_loader_on_page_load cg_reload_entry_meta_line cg_reload_entry_meta_line_main"></div>
                <div class="cg_skeleton_loader_on_page_load cg_reload_entry_meta_line"></div>
                <div class="cg_skeleton_loader_on_page_load cg_reload_entry_meta_textarea"></div>
                <div class="cg_reload_entry_meta_split">
                    <div class="cg_skeleton_loader_on_page_load cg_reload_entry_meta_pill"></div>
                    <div class="cg_skeleton_loader_on_page_load cg_reload_entry_meta_pill cg_reload_entry_meta_pill_short"></div>
                </div>
                <div class="cg_skeleton_loader_on_page_load cg_reload_entry_meta_textarea cg_reload_entry_meta_textarea_short"></div>
                <div class="cg_skeleton_loader_on_page_load cg_reload_entry_meta_line cg_reload_entry_meta_line_wide"></div>
            </div>
        </div>
        <div class="cg_reload_entry_fields">
            <div class="cg_skeleton_loader_on_page_load cg_reload_entry_field_line cg_reload_entry_field_line_wide"></div>
            <div class="cg_skeleton_loader_on_page_load cg_reload_entry_field_line"></div>
            <div class="cg_skeleton_loader_on_page_load cg_reload_entry_field_line cg_reload_entry_field_line_short"></div>
        </div>
    </div>
</div>
HEREDOC;
    }
}

if(!function_exists('cg_ask_if_should_be_removed_from_sale_if_multiple')){
    function cg_ask_if_should_be_removed_from_sale_if_multiple(){
        echo "<div id='cgAskIfShouldBeRemovedFromSaleIfMultiple' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'><span id='cgAskIfShouldBeRemovedFromSaleIfMultipleClose' class='cg_message_close  cg_message_close_extra_processing '></span><p>Remove file from download sale?</p><a id='cgAskIfShouldBeRemovedFromSaleIfMultipleButton' class='cg_image_action_href' href=\"#\" ><span class='cg_image_action_span'>Yes, please remove from sale</span>
</a></div>";
    }
}

if(!function_exists('cg_ecommerce_show_api_response')){
    function cg_ecommerce_show_api_response(){

        echo "<div id='cgEcommerceShowApiResponseContainer' class='cg_hide cg_height_auto cg_backend_action_container  cg_overflow_y_hidden' style='z-index: 9999999;height:80%;'><span class='cg_message_close'></span>";
            echo "<div class='cg-lds-dual-ring-gallery-hide cg_hide'></div>";
            echo "<div id='cgEcommerceShowApiResponse' class='cg_hide' style='height:80%;'>";
                echo '<textarea id="cgEcommerceShowApiResponseTextarea" readonly style="width:100%;height:100%;margin-top:30px;"></textarea>';
            echo "</div>";
        echo "</div>";

    }
}


?>
