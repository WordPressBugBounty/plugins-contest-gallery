<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_network_export_modal_container')){
	function cg_network_export_modal_container(){
		echo '<div id="cgNetworkExportModalContainer" class="cg_backend_action_container cg_hide cg_network_export_modal" data-cg-mode="" data-cg-gallery-id="" data-cg-default-url="">
<span class="cg_message_close cg_gallery_transfer_export_close"></span>
<div id="cgNetworkExportModal" class="cgnet-admin-modal">
	<section class="cgnet-admin-hero">
		<div class="cgnet-admin-hero__copy">
			<span class="cgnet-admin-kicker">Contest Gallery Network</span>
			<h2>Put your contest where people discover it.</h2>
			<p>Make your contest discoverable beyond your own website with a polished public listing on www.contest-gallery.net, preview images, live activity signals, and a verified backlink to your gallery.</p>
			<div class="cgnet-admin-pills">
				<span>Public discovery</span>
				<span class="cgnet-admin-pill--backlink">Verified backlink</span>
				<span>No personal data</span>
				<span>Domain verified</span>
				<span>Safety review</span>
			</div>
		</div>
		<div class="cgnet-admin-hero__panel">
			<strong>What gets submitted?</strong>
			<p>Website title and URL, gallery title, description, tags, contest URL, preview image URLs and public activity numbers such as entries, votes and comments.</p>
			<p><b>No IP addresses, emails, usernames, registration data or comment text.</b></p>
		</div>
	</section>

	<div class="cgnet-admin-grid cgnet-admin-single-only">
		<section class="cgnet-admin-card cgnet-admin-card--network-home">
			<div class="cgnet-admin-network-home__copy">
				<div class="cgnet-admin-card__eyebrow">Published on</div>
				<strong>www.contest-gallery.net</strong>
				<p>After domain verification and safety review, your gallery gets a public Network listing there. Visitors discover it on Contest Gallery Network and click through to your selected gallery page.</p>
			</div>
			<a class="cgnet-admin-network-link" href="https://www.contest-gallery.net/" target="_blank" rel="noopener noreferrer">View Contest Gallery Network</a>
		</section>
		<section class="cgnet-admin-card cgnet-admin-card--listing">
			<div class="cgnet-admin-card__eyebrow">Listing copy</div>
			<label class="cgnet-admin-field" for="cgNetworkExportTitle">
				<span>Network title</span>
				<input type="text" id="cgNetworkExportTitle" class="cgnet-admin-input" maxlength="180" value="">
			</label>
			<label class="cgnet-admin-field" for="cgNetworkExportDescription">
				<span>Network description</span>
				<textarea id="cgNetworkExportDescription" class="cgnet-admin-input cgnet-admin-textarea" maxlength="360" rows="5"></textarea>
			</label>
		</section>
		<section class="cgnet-admin-card cgnet-admin-card--link">
			<div class="cgnet-admin-card__eyebrow">Visitor destination</div>
			<h3>Where should visitors land after they discover your listing?</h3>
			<p>This is the page opened after someone clicks your listing on www.contest-gallery.net.</p>
			<div class="cgnet-admin-url-preview">
				<span>Default gallery URL</span>
				<strong id="cgNetworkDefaultUrl">No eligible gallery URL found</strong>
			</div>
			<label class="cgnet-admin-toggle" for="cgNetworkCustomPageToggle">
				<input id="cgNetworkCustomPageToggle" type="checkbox">
				<span class="cgnet-admin-toggle__track"><span class="cgnet-admin-toggle__knob"></span></span>
				<span><b>Use another public page</b><em>Use this if you placed the [cg_gallery] shortcode on another public page instead of using the generated gallery URL.</em></span>
			</label>
			<div id="cgNetworkCustomPagePanel" class="cgnet-admin-custom-url-panel cgnet-admin-custom-url-panel--disabled">
				<label class="cgnet-admin-field" for="cgNetworkCustomUrl">
					<span>Custom public page URL</span>
					<input type="url" id="cgNetworkCustomUrl" class="cgnet-admin-input" placeholder="https://example.com/contest/" disabled>
				</label>
				<p>Must be on the same domain and publicly show this gallery.</p>
			</div>
		</section>
		<section class="cgnet-admin-card cgnet-admin-card--automation">
			<div class="cgnet-admin-card__eyebrow">Automation</div>
			<label class="cgnet-admin-toggle cgnet-admin-toggle--fixed" for="cgNetworkAutoExportToggle">
				<input id="cgNetworkAutoExportToggle" type="checkbox" checked disabled>
				<span class="cgnet-admin-toggle__track"><span class="cgnet-admin-toggle__knob"></span></span>
				<span><b>Daily auto update <span class="cgnet-admin-live-badge">Included</span></b><em>After publishing, this listing refreshes once daily via WordPress Cron. If updates stop for 14 days, www.contest-gallery.net removes it from the public Network index.</em></span>
			</label>
		</section>
		<section id="cgNetworkIneligibleMessage" class="cgnet-admin-card cgnet-admin-card--warning cgnet-admin-is-hidden">
			<strong>Export not available for this gallery version</strong>
			<p></p>
		</section>
	</div>

	<section class="cgnet-admin-review">
		<div id="cgNetworkPublishStatus" class="cgnet-admin-publish-status cgnet-admin-is-hidden">
			<div>
				<span>Network listing</span>
				<strong>Published since <em id="cgNetworkPublishStatusTime"></em></strong>
				<small id="cgNetworkPublishAutoUpdateStatus">Last network update pending</small>
				<small id="cgNetworkPublishAutoUpdateError" class="cgnet-admin-publish-status__error cgnet-admin-is-hidden"></small>
				<a id="cgNetworkPublishStatusLink" href="#" target="_blank" rel="noopener noreferrer">View listing</a>
			</div>
			<div class="cgnet-admin-publish-status__actions">
				<button class="cgnet-admin-button cgnet-admin-button--ghost" type="button" id="cgNetworkRunAutoUpdateNow">Run update now</button>
				<button class="cgnet-admin-button cgnet-admin-button--danger" type="button" id="cgNetworkUnpublishSubmit">Unpublish from Network</button>
			</div>
		</div>
		<div class="cgnet-admin-review__meta">
			<span>Daily update after publish</span>
			<span>Removed after 14 days without updates</span>
			<span>Published after safety review</span>
		</div>
		<div id="cgNetworkUnsavedChangesNotice" class="cgnet-admin-unsaved-notice cgnet-admin-is-hidden">
			<strong>Unsaved listing changes</strong>
			<span>Click "Update network listing" to save these changes before running an update.</span>
		</div>
		<label class="cgnet-admin-check" for="cgNetworkPrivacyConfirm">
			<input id="cgNetworkPrivacyConfirm" type="checkbox">
			<span class="cgnet-admin-check__box"></span>
			<span>I confirm that I have the right to publish this gallery information on www.contest-gallery.net, accept the <a href="https://www.contest-gallery.net/privacy-policy/" target="_blank" rel="noopener noreferrer">Contest Gallery Network privacy policy</a>, and understand that this listing is refreshed once daily while the plugin remains active.</span>
		</label>
		<div id="cgNetworkExportValidation" class="cgnet-admin-result cgnet-admin-result--error cgnet-admin-is-hidden">Please confirm the privacy policy before publishing.</div>
		<div id="cgNetworkExportResult" class="cgnet-admin-result cgnet-admin-is-hidden"></div>
		<div class="cgnet-admin-actions">
			<button class="cgnet-admin-button cgnet-admin-button--ghost" type="button" id="cgNetworkExportCancel">Close</button>
			<button class="cgnet-admin-button cgnet-admin-button--primary" type="button" id="cgNetworkExportSubmit" data-cg-default-text="Publish and keep updated">Publish and keep updated</button>
		</div>
	</section>
</div>
</div>';
	}
}

?>
