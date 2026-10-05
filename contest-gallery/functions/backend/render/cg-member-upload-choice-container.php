<?php
if(!defined('ABSPATH')){exit;}

function cg_member_upload_choice_container(){
	echo '<div id="cgMemberUploadChoice" class="cg_backend_action_container cg-options-choice-dialog cg_hide" role="dialog" aria-modal="true" aria-labelledby="cgMemberUploadChoiceTitle" aria-describedby="cgMemberUploadChoiceDescription">
		<button type="button" class="cg-options-choice-close" aria-label="Close"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button>
		<div class="cg-options-choice-kicker">MEMBER AREA</div>
		<h2 id="cgMemberUploadChoiceTitle">Require sign-in for uploads?</h2>
		<p id="cgMemberUploadChoiceDescription">Choose how visitors can upload. The member area lets them sign in and manage their own images.</p>
		<div class="cg-options-choice-list">
			<button type="button" class="cg-options-choice" data-cg-upload-login="1">
				<span class="cg-options-choice-icon dashicons dashicons-lock" aria-hidden="true"></span>
				<span class="cg-options-choice-copy"><span class="cg-options-choice-recommended">Recommended</span><strong>Require sign-in for uploads</strong><span>Enable the member area and uploads for registered users only.</span></span>
				<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
			</button>
			<button type="button" class="cg-options-choice" data-cg-upload-login="0">
				<span class="cg-options-choice-icon dashicons dashicons-groups" aria-hidden="true"></span>
				<span class="cg-options-choice-copy"><strong>Allow uploads without sign-in</strong><span>Enable the member area and keep the current upload checks. Guests can still upload.</span></span>
				<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
			</button>
		</div>
		<p class="cg-options-choice-note">The upload setting applies to all views of this gallery. Changes take effect when you save the gallery options.</p>
		<button type="button" class="cg-options-choice-cancel">Cancel</button>
	</div>';
}
