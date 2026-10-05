<?php
if(!defined('ABSPATH')){exit;}

function cg_view_choice_container(){
	$choices = array(
		'BlogLookFullWindow'=>array('Full window blog view','Open the entry in a full window overlay.','editor-expand'),
		'ForwardToWpPageEntry'=>array('Entry landing page','Open the entry on its own page.','admin-page'),
		'FullSizeImageOutGallery'=>array('Original source link only','Open the original file directly. Voting on gallery cards remains available.','external'),
		'OnlyGalleryView'=>array('Make entries unclickable','Display entries without opening them. Voting on gallery cards remains available.','screenoptions')
	);
	echo '<div id="cgViewChoice" class="cg_backend_action_container cg-options-choice-dialog cg_hide" role="dialog" aria-modal="true" aria-labelledby="cgViewChoiceTitle" aria-describedby="cgViewChoiceDescription">
		<button type="button" class="cg-options-choice-close" aria-label="Close"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button>
		<div class="cg-options-choice-kicker">ENTRY VIEW</div>
		<h2 id="cgViewChoiceTitle">Select another view</h2>
		<p id="cgViewChoiceDescription">Choose what happens when visitors click an entry in this gallery view.</p>
		<div class="cg-options-choice-list">';
	foreach($choices as $value=>$choice){
		echo '<button type="button" class="cg-options-choice" data-cg-entry-view="'.esc_attr($value).'">
			<span class="cg-options-choice-icon dashicons dashicons-'.esc_attr($choice[2]).'" aria-hidden="true"></span>
			<span class="cg-options-choice-copy"><strong>'.esc_html($choice[0]).'</strong><span>'.esc_html($choice[1]).'</span></span>
			<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
		</button>';
	}
	echo '</div>
		<p class="cg-options-choice-note">Slider and blog views keep their current behavior. Changes take effect when you save the gallery options.</p>
		<button type="button" class="cg-options-choice-cancel">Cancel</button>
	</div>';
}
