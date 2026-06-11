<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_delete_gallery_confirm_container')){
	function cg_delete_gallery_confirm_container(){
		echo '<div id="cgDeleteGalleryConfirmContainer" class="cg_backend_action_container cg_hide cg_delete_gallery_confirm_modal cg_height_auto">
<span class="cg_message_close"></span>
<div class="cg-delete-gallery-confirm">
	<div class="cg-delete-gallery-confirm-header">
		<div class="cg-delete-gallery-confirm-header-icon" aria-hidden="true"></div>
		<div>
			<div class="cg-delete-gallery-confirm-kicker">Delete gallery</div>
			<h2>Delete gallery <span id="cgDeleteGalleryConfirmGalleryId">0</span>?</h2>
			<p>This action deletes the gallery and its entries.</p>
		</div>
	</div>
	<div class="cg-delete-gallery-confirm-list">
		<div id="cgDeleteGalleryConfirmMediaNote" class="cg-delete-gallery-confirm-item">
			<span class="cg-delete-gallery-confirm-item-icon" aria-hidden="true"></span>
			<span>Files and images used by this gallery <strong>will not be deleted</strong> from the media library.</span>
		</div>
		<div id="cgDeleteGalleryConfirmLegacyNote" class="cg-delete-gallery-confirm-item cg-delete-gallery-confirm-item-danger cg_hide">
			<span class="cg-delete-gallery-confirm-item-icon" aria-hidden="true"></span>
			<span>All uploaded pictures will be irrevocably deleted.</span>
		</div>
		<div class="cg-delete-gallery-confirm-item cg-delete-gallery-confirm-item-warning">
			<span class="cg-delete-gallery-confirm-item-icon" aria-hidden="true"></span>
			<span>Real watermarks will be restored before this gallery is deleted. If the same WordPress media file is used in another gallery, it can become unwatermarked there too.</span>
		</div>
		<div id="cgDeleteGalleryConfirmEcommerceNote" class="cg-delete-gallery-confirm-item cg-delete-gallery-confirm-item-warning cg_hide">
			<span class="cg-delete-gallery-confirm-item-icon" aria-hidden="true"></span>
			<span>Original downloads for selling will be moved back to WordPress media library. Sold downloads will be not available anymore on order summary page for customers.</span>
		</div>
	</div>
	<div class="cg-delete-gallery-confirm-actions">
		<button type="button" id="cgDeleteGalleryConfirmCancel" class="cg_backend_button cg-delete-gallery-confirm-button cg-delete-gallery-confirm-button-cancel">Cancel</button>
		<button type="button" id="cgDeleteGalleryConfirmSubmit" class="cg_backend_button cg-delete-gallery-confirm-button cg-delete-gallery-confirm-button-delete">Delete gallery</button>
	</div>
</div>
</div>';
	}
}

?>
