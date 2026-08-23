<?php

extract( shortcode_atts( array(
	'id' => ''
), $atts ) );
$atts = cg1l_sanitize_atts($atts);

wp_enqueue_style('cg_v10_css_cg_gallery', plugins_url('/../v10/v10-css-min/cg_gallery.min.css', __FILE__), false, cg_get_version_for_scripts());
$cgLegacyStatusCardTheme = 'cg_fe_controls_style_white';

if(!empty($atts['id'])){

	$galeryID = absint(trim($atts['id']));

	global $wpdb;

	$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";

	$optionsSQL = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablenameOptions WHERE id= %d ",[$galeryID]));

	if(empty($optionsSQL)){
		@ob_start();// Wichtig!!! Anonsten kommt es zu Fehler im Backend beim speichern!
		$checkIfGalleryExists = false;
		echo cg1l_get_gallery_status_card('Contest Gallery: Please check your gallery shortcode. The ID does not exist.', $cgLegacyStatusCardTheme, 'cg-error-shortcode cg-error-shortcode-id-not-found');

		?>

        <script>

            alert('Contest Gallery message:\r\nYou have a shortcode of a gallery inserted with id which does not exits. Please remove the shortcode. Otherwise functionality is broken.');

        </script>

		<?php

		return;

	}else{

		if(empty($optionsSQL->Version) OR intval($optionsSQL->Version)<10){
			echo cg1l_get_gallery_status_card('<b>You are using a gallery with an old engine.</b><br>Please copy this gallery to use the new engine and all Contest Gallery abilities.', $cgLegacyStatusCardTheme, 'cg-error-shortcode');
		}else{
			$message = "<b>$usedShortcode shortcode information:</b>
<br>wp-content/uploads/contest-gallery... folder and files can not be found or read.<br>They might be deleted by someone manually or wrong files/folders permissions are set.
";
			echo cg1l_get_gallery_status_card($message, $cgLegacyStatusCardTheme, 'cg-error-shortcode');
		}
	}

}else{
	?>

    <script>

        alert('Contest Gallery message:\r\nYou have a shortcode inserted which will not display any content.\r\nMaybe there are no galleries created or no id or ids of galleries provided for the shortcode.');

    </script>

	<?php

	return;
}


?>
