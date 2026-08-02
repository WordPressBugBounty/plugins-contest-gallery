<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_user_data_export_container')){
	function cg_user_data_export_container(){
		echo '<div id="cgUserDataExportModalContainer" class="cg_backend_action_container cg_hide cg_user_data_export_modal cg_do_not_remove_when_ajax_load cg_do_not_remove_when_main_empty" data-cg-job-id="" role="dialog" aria-modal="true" aria-labelledby="cgUserDataExportHeadline" aria-describedby="cgUserDataExportText">'
			.'<span class="cg_message_close cg_user_data_export_close"></span>'
			.'<div class="cg-user-data-export-modal">'
				.'<div class="cg-user-data-export-kicker">Registered users data export</div>'
				.'<h2 id="cgUserDataExportHeadline">Preparing export</h2>'
				.'<p id="cgUserDataExportText">The export job is being prepared.</p>'
				.'<div class="cg-user-data-export-progressbar"><span id="cgUserDataExportProgressBar"></span></div>'
				.'<div class="cg-user-data-export-meta">'
					.'<span>Progress: <strong id="cgUserDataExportPercent">0%</strong></span>'
					.'<span>Users: <strong id="cgUserDataExportUsers">0/0</strong></span>'
				.'</div>'
				.'<div id="cgUserDataExportError" class="cg-user-data-export-result cg-user-data-export-result-error cg_hide"></div>'
				.'<div id="cgUserDataExportDownloadInfo" class="cg-user-data-export-download-info cg_hide">The temporary CSV parts are removed automatically after 24 hours.</div>'
				.'<div class="cg-user-data-export-actions">'
					.'<button type="button" id="cgUserDataExportCancel" class="cg_backend_button cg-user-data-export-button cg-user-data-export-button-ghost">Cancel export</button>'
					.'<button type="button" id="cgUserDataExportRetry" class="cg_backend_button cg-user-data-export-button cg-user-data-export-button-primary cg_hide">Retry</button>'
					.'<a id="cgUserDataExportDownload" class="cg_backend_button cg-user-data-export-button cg-user-data-export-button-primary cg_hide" href="#">Download CSV</a>'
					.'<button type="button" id="cgUserDataExportClose" class="cg_backend_button cg-user-data-export-button cg-user-data-export-button-ghost cg_hide">Close</button>'
				.'</div>'
			.'</div>'
			.'</div>';
	}
}

?>
