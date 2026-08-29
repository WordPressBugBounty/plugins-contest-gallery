<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_gallery_has_legacy_hash_urls')){
	function cg_gallery_has_legacy_hash_urls($galleryDbVersion){
		return floatval($galleryDbVersion) < 21;
	}
}

if(!function_exists('cg_legacy_gallery_url_notice')){
	function cg_legacy_gallery_url_notice($galleryId,$galleryDbVersion,$context = 'editor'){
		if(!cg_gallery_has_legacy_hash_urls($galleryDbVersion)){
			return;
		}

		$galleryId = absint($galleryId);
		$context = ($context === 'overview') ? 'overview' : 'editor';
		$titleId = 'cgLegacyGalleryUrlNoticeTitle'.$galleryId.$context;
		$galleryOverviewUrl = add_query_arg('page',cg_get_version().'/index.php',admin_url('admin.php'));

		echo "<section class='cg_legacy_gallery_url_notice cg_legacy_gallery_url_notice_".esc_attr($context)."' aria-labelledby='".esc_attr($titleId)."'>";
		echo "<span class='dashicons dashicons-warning cg_legacy_gallery_url_notice_icon' aria-hidden='true'></span>";
		echo "<div class='cg_legacy_gallery_url_notice_content'>";
		echo "<div class='cg_legacy_gallery_url_notice_eyebrow'>LEGACY GALLERY ".esc_html('#'.$galleryId)."</div>";
		echo "<h2 id='".esc_attr($titleId)."'>Legacy gallery URLs are no longer supported</h2>";
		echo "<p>This gallery still uses outdated hash URLs. The existing gallery remains unchanged and continues to work, but support for galleries using this legacy URL format has ended.</p>";
		echo "<p><strong>Recommended migration:</strong> Create a complete copy. Copying leaves the original gallery untouched, and the copied gallery receives real WordPress URLs for its entries. This is the safest migration path because you can test the copy before replacing the gallery ID in your shortcode.</p>";
		echo "<p class='cg_legacy_gallery_url_notice_instruction'>In the gallery overview, click <strong>Copy gallery</strong> and select <strong>Copy everything</strong>.</p>";
		if($context !== 'overview'){
			echo "<a class='button button-primary cg_legacy_gallery_url_notice_button' href='".esc_url($galleryOverviewUrl)."'><span class='dashicons dashicons-admin-page' aria-hidden='true'></span> Open gallery overview</a>";
		}
		echo "</div>";
		echo "</section>";
	}
}
