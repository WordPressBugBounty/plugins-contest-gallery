<?php
if(!defined('ABSPATH')){exit;}

function cg_backend_render_entry_page_open($config){
	$assetBase = plugins_url('/'.cg_get_version().'/v10/');
	$version = cg_get_version_for_scripts();
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Opening entry · Contest Gallery</title>
	<link rel="stylesheet" href="<?php echo esc_url($assetBase.'v10-css/backend/cg_entry_page_open.css?ver='.$version); ?>">
</head>
<body>
	<main class="cg-entry-open-card" aria-busy="true">
		<div class="cg-entry-open-icon" aria-hidden="true"><span></span></div>
		<p class="cg-entry-open-brand">Contest Gallery</p>
		<h1 id="cgEntryOpenTitle">Checking current entry URL…</h1>
		<p id="cgEntryOpenStatus" role="status" aria-live="polite">Opening your entry.</p>
		<p class="cg-entry-open-hint">This check only appears when opening entries from the backend.</p>
		<button id="cgEntryOpenRetry" type="button" hidden>Try again</button>
		<noscript><p>Enable JavaScript to check and open the current entry URL.</p></noscript>
	</main>
	<script type="application/json" id="cgEntryOpenConfig"><?php echo wp_json_encode($config,JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
	<script src="<?php echo esc_url($assetBase.'v10-js/admin/gallery/gallery-entry-page-open.js?ver='.$version); ?>"></script>
</body>
</html>
	<?php
}
