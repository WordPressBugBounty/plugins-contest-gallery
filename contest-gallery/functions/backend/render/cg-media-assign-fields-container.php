<?php

if(!function_exists('cg_media_assign_fields_container')){
	function cg_media_assign_fields_container($GalleryID){
		$assignFieldsImageUrl = plugins_url('/../../../v10/v10-css/assign-fields.png', __FILE__);
		?>
		<div id="cgMediaAssignFieldsContainer"
			 class="cg_media_container cg_media_assign_fields_tab_panel cg_hide"
			 data-cg-gid="<?php echo absint($GalleryID); ?>">
			<div class="cg_media_assign_fields_tab_inner">
				<div class="cg_media_assign_fields_tab_header">
					<h2 class="cg_media_assign_fields_tab_title" tabindex="-1">Assign fields to added files</h2>
					<p>Choose a category and map WordPress media fields to your gallery fields. These assignments are applied when you add selected files.</p>
				</div>
				<div class="cg_media_assign_fields_tab_layout">
					<div class="cg_media_assign_fields_tab_settings">
						<div class="cg_media_assign_category cg_hide">
							<label class="cg_media_assign_category_title">Assign to category</label>
							<div class="cg_media_assign_category_select">
								<select><option value="">No category</option></select>
							</div>
						</div>
						<div class="cg_media_assign_fields_overview cg_hide">
							<h3>Map WordPress media fields</h3>
							<div class="cg_media_assign_fields"></div>
						</div>
					</div>
					<div class="cg_media_assign_fields_image">
						<div class="cg_media_assign_fields_image_title">Example</div>
						<img src="<?php echo esc_url($assignFieldsImageUrl); ?>" alt="Example of WordPress media fields">
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
