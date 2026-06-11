<?php

if(!defined('ABSPATH')){
	exit;
}

if(!function_exists('cg_entry_watermark_container')){
	function cg_entry_watermark_container($GalleryID){
		$isProVersion = (function_exists('cg_get_version') && cg_get_version()==='contest-gallery-pro') ? true : false;
		$isProVersionData = $isProVersion ? '1' : '0';
		$galleryHash = md5(wp_salt('auth').'---cngl1---'.$GalleryID);
		echo "<div id='cgWatermarkContainer' class='cg_backend_action_container cg_hide' data-cg-is-pro='".esc_attr($isProVersionData)."'>";
		echo "<span class='cg_message_close'></span>";
		echo "<form id='cgEntryWatermarkForm' method='POST'>";
		echo "<input type='hidden' id='cgEntryWatermarkGalleryID' value='".esc_attr($GalleryID)."'>";
		echo "<input type='hidden' id='cgEntryWatermarkRealId' value=''>";
		echo "<input type='hidden' id='cgEntryWatermarkGalleryHash' value='".esc_attr($galleryHash)."'>";

		echo "<div class='cg_shortcode_conf_title_container' style='margin-top: 25px;margin-bottom: 15px;'><div class='cg_shortcode_conf_title_main'>Watermark</div><div class='cg_shortcode_conf_title_sub'><span id='cgEntryWatermarkTitleLabel'>Entry ID</span> <span id='cgEntryWatermarkEntryIdTitle'></span></div></div>";
		echo "<div id='cgEntryWatermarkFormLoaderContainer' class='cg_hide'><div class='cg-lds-dual-ring-gallery-hide' style='height: 260px;margin: 0;padding-top: 80px;max-height: 200px;'></div></div>";

		echo "<div id='cgEntryWatermarkFormContent'>";
		echo "<div class='cg_entry_watermark_real_notice'><b>Real watermarking:</b> Original image files will be stored in the protected Contest Gallery folder <span>.../wp-content/uploads/contest-gallery/watermark-originals/...</span>, and public image files will be replaced with watermarked versions. Unwatermark to restore originals.</div>";
		if(function_exists('cg_entry_watermark_gd_base_supported') && !cg_entry_watermark_gd_base_supported()){
			$gdNotice = function_exists('cg_entry_watermark_gd_unsupported_message') ? cg_entry_watermark_gd_unsupported_message() : 'PHP GD image library is not available. Real watermarking requires GD image support on this server.';
			echo "<div class='cg_entry_watermark_gd_notice'>".esc_html($gdNotice)."</div>";
		}
		if(function_exists('cg_render_nginx_upload_protection_notice')){
			$nginxRule = function_exists('cg_entry_watermark_get_nginx_rule') ? cg_entry_watermark_get_nginx_rule() : "location ^~ /wp-content/uploads/contest-gallery/watermark-originals/ {\n    deny all;\n}";
			cg_render_nginx_upload_protection_notice(
				'Using Nginx? Add this server rule to protect watermark originals.',
				'Watermarking works without this rule, but on Nginx the original files are only protected when the server configuration blocks this path. Ask your server admin to add this rule inside the matching server block and reload Nginx.',
				$nginxRule,
				'cg_entry_watermark_nginx_notice'
			);
		}
		echo "<div class='cg_main_options cg_entry_watermark_main_options'>";
		echo "<div class='cg_view_options_row'>";
		echo "<div class='cg_view_option cg_view_option_full_width cg_entry_page_description cg_border_bottom_none cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px cg_entry_watermark_files_panel'>";
		echo "<div class='cg_view_option_title cg_view_option_title_full_width'><p>Select image files</p></div>";
		echo "<div id='cgEntryWatermarkFiles'></div>";
		echo "<p id='cgEntryWatermarkNoFiles' class='cg_error cg_hide'>No JPG, PNG or GIF image files available for watermarking</p>";
		echo "</div>";
		echo "</div>";

		echo "<div class='cg_view_options_row'>";
		echo "<div class='cg_view_option cg_view_option_full_width cg_entry_page_description cg_border_bottom_none cg_entry_watermark_editor'>";
		echo "<div class='cg_entry_watermark_editor_layout'>";
		echo "<div class='cg_entry_watermark_preview_panel'>";
		echo "<div id='cgEntryWatermarkPreviewImageContainer'>";
		echo "<div id='cgEntryWatermarkPreviewLoader' class='cg_entry_watermark_preview_loader cg_hide'>";
		echo "<div class='cg_entry_watermark_preview_loader_panel'>";
		echo "<div class='cg_entry_watermark_preview_loader_bar'></div>";
		echo "<div class='cg_entry_watermark_preview_loader_text'>Updating preview</div>";
		echo "</div>";
		echo "</div>";
		echo "<div id='cgEntryWatermarkPreviewImage'></div>";
		echo "</div>";
		echo "</div>";
		echo "<div class='cg_entry_watermark_settings_panel'>";
		echo "<div class='cg_entry_watermark_settings'>";
		echo "<label for='cgEntryWatermarkInputTitle'>Text</label>";
		echo "<input type='text' id='cgEntryWatermarkInputTitle' maxlength='256' value='Contest Gallery'>";
		echo "<label for='cgEntryWatermarkSelectPosition'>Position</label>";
		echo "<select id='cgEntryWatermarkSelectPosition'>";
		$positions = array(
			'center' => 'Center',
			'upperLeft' => 'Upper left',
			'upperRight' => 'Upper right',
			'lowerRight' => 'Lower right',
			'lowerLeft' => 'Lower left'
		);
		foreach($positions as $positionValue => $positionText){
			$isProOnlyOption = (!$isProVersion && $positionValue !== 'center') ? true : false;
			$class = $isProOnlyOption ? " class='cg-pro-false'" : '';
			$proText = $isProOnlyOption ? " <span class='cg-pro-false-text'>(PRO)</span>" : '';
			echo "<option value='".esc_attr($positionValue)."'$class>".esc_html($positionText)."$proText</option>";
		}
		echo "</select>";
		echo "<label for='cgEntryWatermarkSelectSize'>Size</label>";
		echo "<select id='cgEntryWatermarkSelectSize'>";
			foreach(array(512,256,128,64,32,16,8) as $size){
				$selected = (($isProVersion && $size==64) || (!$isProVersion && $size==512)) ? ' selected' : '';
				$isProOnlyOption = (!$isProVersion && !in_array($size, array(512,256), true)) ? true : false;
				$class = $isProOnlyOption ? " class='cg-pro-false'" : '';
				$proText = $isProOnlyOption ? " <span class='cg-pro-false-text'>(PRO)</span>" : '';
				$sizeLabel = function_exists('cg_get_watermark_size_label') ? cg_get_watermark_size_label($size) : $size;
				echo "<option value='".esc_attr($size)."'$selected$class>".esc_html($sizeLabel)."$proText</option>";
			}
		echo "</select>";
		echo "</div>";
		echo "</div>";
		echo "</div>";
		echo "</div>";
		echo "</div>";

		echo "<div class='cg_view_options_row cg_entry_watermark_restore_summary_row cg_hide'>";
		echo "<div id='cgEntryWatermarkRestoreSummary' class='cg_view_option cg_view_option_full_width cg_entry_page_description cg_border_radius_8_px cg_entry_watermark_restore_summary'>";
		echo "<div id='cgEntryWatermarkRestoreSummaryTitle' class='cg_entry_watermark_restore_summary_title'>Checking watermarked image files...</div>";
		echo "<div id='cgEntryWatermarkRestoreSummaryMeta' class='cg_entry_watermark_restore_summary_meta'></div>";
		echo "</div>";
		echo "</div>";

		echo "<div class='cg_view_options_row cg_entry_watermark_bulk_progress_row'>";
		echo "<div id='cgEntryWatermarkBulkProgress' class='cg_view_option cg_view_option_full_width cg_entry_page_description cg_border_bottom_none cg_entry_watermark_bulk_progress cg_hide'>";
		echo "<div class='cg_entry_watermark_bulk_progress_header'>";
		echo "<span id='cgEntryWatermarkBulkProgressStatus'>Preparing watermark files</span>";
		echo "<strong id='cgEntryWatermarkBulkProgressPercent'>0%</strong>";
		echo "</div>";
		echo "<div class='cg_entry_watermark_bulk_progress_bar'><span id='cgEntryWatermarkBulkProgressBar'></span></div>";
		echo "<div id='cgEntryWatermarkBulkProgressMeta' class='cg_entry_watermark_bulk_progress_meta'>0 / 0 files processed</div>";
		echo "</div>";
		echo "</div>";

		echo "<div class='cg_view_options_row cg_entry_watermark_save_row'>";
		echo "<div class='cg_view_option cg_view_option_full_width cg_entry_page_description cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px cg_entry_watermark_save_panel'>";
		echo "<input type='submit' id='cgEntryWatermarkSave' class='cg_backend_button_gallery_action' value='Save'>";
		echo "</div>";
		echo "</div>";
		echo "</div>";
		echo "</div>";
		echo "</form>";
		echo "</div>";
	}
}
