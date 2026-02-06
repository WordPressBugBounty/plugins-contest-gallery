<!--<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>-->
<?php

// Path to jquery Lightbox Script 

global $wpdb;

if(isset($_GET['option_id'])){
	$GalleryID = absint($_GET['option_id']);
}elseif(isset($_POST['option_id'])){
	$GalleryID = absint($_POST['option_id']);
}

$tablename = $wpdb->prefix . "contest_gal1ery";
$tablename_ip = $wpdb->prefix . "contest_gal1ery_ip";
$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";
$tablename_options_input = $wpdb->prefix . "contest_gal1ery_options_input";
$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
$tablename_mail_admin = $wpdb->prefix . "contest_gal1ery_mail_admin";
$tablenameemail = $wpdb->prefix . "contest_gal1ery_mail";
$tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
//$tablename_mail_gallery = $wpdb->prefix . "contest_gal1ery_mail_gallery";
$tablename_mail_confirmation = $wpdb->prefix . "contest_gal1ery_mail_confirmation";
$table_usermeta = $wpdb->base_prefix . "usermeta";
$table_posts = $wpdb->prefix."posts";
$table_users = $wpdb->base_prefix."users";
$tablename_wp_pages = $wpdb->base_prefix."contest_gal1ery_wp_pages";
$tablename_entries = $wpdb->base_prefix."contest_gal1ery_entries";

require_once(dirname(__FILE__) . "/../nav-menu.php");

$upload_dir = wp_upload_dir();
$uploadFolder = wp_upload_dir();

//$options = $wpdb->get_results( "SELECT * FROM $tablename WHERE GalleryID = '$GalleryID'" );
$proOptions = $wpdb->get_row( "SELECT * FROM $tablename_pro_options WHERE GalleryID = '$GalleryID'" );
$options = $wpdb->get_row( "SELECT * FROM $tablenameOptions WHERE id = '$GalleryID'" );

$DataShare = ($proOptions->FbLikeNoShare==1) ? 'false' : 'true';
$DataClass = ($proOptions->FbLikeOnlyShare==1) ? 'fb-share-button' : 'fb-like';
$DataLayout = ($proOptions->FbLikeOnlyShare==1) ? 'button' : 'button_count';

// Correct 1 from HERE

$correctStatusText1 = 'Correct';
$correctStatusClass1 = '';

$thumbSizesWp = array();
$thumbSizesWp['thumbnail_size_w'] = get_option("thumbnail_size_w");
$thumbSizesWp['medium_size_w'] = get_option("medium_size_w");
$thumbSizesWp['large_size_w'] = get_option("large_size_w");

$correctStatusText2 = 'Correct';
$correctStatusClass2 = '';

if(isset($_POST['action_correct_information_for_frontend'])){

	//do_action('cg_json_upload_form_info_data_files',$GalleryID,null);
	cg_json_upload_form_info_data_files_new($GalleryID,[],true,false,true);
	$correctStatusText2 = 'Corrected';
	$correctStatusClass2 = 'cg_corrected';

}
$correctStatusText3 = 'Correct';
$correctStatusClass3 = '';

if(isset($_POST['action_correct_not_shown_for_frontend'])){

	$picsSQL = $wpdb->get_results( "SELECT DISTINCT $table_posts.*, $tablename.* FROM $table_posts, $tablename WHERE 
                                              ($tablename.GalleryID='$GalleryID' AND $tablename.Active='1' and $table_posts.ID = $tablename.WpUpload) OR 
                                              ($tablename.GalleryID='$GalleryID' AND $tablename.Active='1' AND $tablename.WpUpload = 0) 
                                          GROUP BY $tablename.id ORDER BY $tablename.id DESC");

	$imageArray = array();
	$RatingOverviewArray = cg_get_correct_rating_overview($GalleryID);

	// add all json files and generate images array
	foreach($picsSQL as $object){

		$imageArray = cg_create_json_files_when_activating($GalleryID,$object,$thumbSizesWp,$upload_dir,$imageArray,$options->Version,$RatingOverviewArray);

	}

	cg_json_upload_form_info_data_files_new($GalleryID,[],true,false,true);

	$correctStatusText3 = 'Corrected';
	$correctStatusClass3 = 'cg_corrected';

}//>>> SEEE DIV FOR IT HERE!!!!!
// $correctStatusText3 DIV FOR IT HERE
// $correctStatusClass3 DIV FOR IT HERE
/*    <div class="cg_corrections_container">
        <div class="cg_corrections_explanation">
            <div class="cg_corrections_title">Seeing not all images in frontend?</div>
            <div class="cg_corrections_description">If you missing images in frontend you can correct it here.</div>
        </div>
        <div class="cg_corrections_action $correctStatusClass3">
            <form method="POST" action="?page=".cg_get_version()."/index.php&amp;corrections_and_improvements=true&amp;option_id=$GalleryID">
                <input type="hidden" name="action_correct_not_shown_for_frontend" value="true">
                <input type="hidden" name="option_id" value="$GalleryID">
                <span class="cg_corrections_action_submit">$correctStatusText3</span>
            </form>
        </div>
    </div>    */

// Correct 4 from HERE


$correctStatusText4 = 'Nothing to repair';
$correctStatusClass4 = '';
$correctStatusTextFull4 = 'All required columns available!';


try {
	if(isset($_POST['action_check_and_correct_database'])){
		$i="";

		// try all updates here!
		include(__DIR__."/../../../update/update-check-new.php");
		$isJustCheck = true;
		include(__DIR__."/../../../update/update-check-new.php");

		if(!empty($columnsToRepairArray['hasColumnsToImprove'])){

			// unset here so processing does not stop
			unset($columnsToRepairArray['hasColumnsToImprove']);

			$correctStatusTextFull4 = '<span class="cg_database_improve_title">Please contact <a href="mailto:support@contest-gallery.com">support@contest-gallery.com</a><br>Copy and send following data with MySQL version:</span>';

			if ( function_exists( 'mysqli_connect' ) ) {
				$server_info = mysqli_get_server_info( $wpdb->dbh );
			}else{
				$server_info = mysql_get_server_info( $wpdb->dbh );
			}

			$correctStatusTextFull4 .= '<span class="cg_database_improve_mysql_version">MySQL version '.$wpdb->db_version().' - '.$server_info.'</span>';

			foreach($columnsToRepairArray as $tableName => $tableData){
				$correctStatusTextFull4 .= "<span class=\"cg_database_improve_table_name\">Table: $tableName</span><br>";
				$correctStatusTextFull4 .= "<table><tbody>";
				$correctStatusTextFull4 .= "<tr><th>Column</th><th>Status</th></tr>";
				foreach($tableData as $columnData){
					$statusText = '';
					if(isset($columnData['IsNoColumn'])){
						$statusText = $errorsArray[$columnData['ColumnName']];
					}
					if(isset($columnData['IsColumnCouldNotBeModified'])){
						$statusText = $errorsArray[$columnData['ColumnName']];
					}
					$correctStatusTextFull4 .= "<tr><td>".$columnData['ColumnName']."</td><td>$statusText</td></tr>";
				}
				$correctStatusTextFull4 .= "</table></tbody>";
			}

			$correctStatusText4 = 'Repair';
			$correctStatusClass4 = '';

		}else{
			$correctStatusText4 = 'Repaired';
			$correctStatusClass4 = 'cg_corrected';
		}

	}else{
		$i="";

		$isJustCheck = true;
		include(__DIR__."/../../../update/update-check-new.php");

		if(!empty($columnsToRepairArray['hasColumnsToImprove'])){

			// unset here so processing does not stop
			unset($columnsToRepairArray['hasColumnsToImprove']);

			$correctStatusTextFull4 = '<span class="cg_database_improve_title">Table data needs to be repaired</span>';

			foreach($columnsToRepairArray as $tableName => $tableData){
				$correctStatusTextFull4 .= "<span class=\"cg_database_improve_table_name\">Table: $tableName</span><br>";
				$correctStatusTextFull4 .= "<table><tbody>";
				$correctStatusTextFull4 .= "<tr><th>Column</th><th>Status</th></tr>";
				foreach($tableData as $columnData){
					$statusText = '';
					if(isset($columnData['IsNoColumn'])){
						$statusText = 'Not created';
					}
					if(isset($columnData['IsColumnCouldNotBeModified'])){
						$statusText = 'Modify: from '.$columnData['ColumnTypeCurrent'].' to '.$columnData['ColumnTypeRequired'].'';
					}
					$correctStatusTextFull4 .= "<tr><td>".$columnData['ColumnName']."</td><td>$statusText</td></tr>";
				}
				$correctStatusTextFull4 .= "</table></tbody>";
			}

			$correctStatusText4 = 'Repair';
			$correctStatusClass4 = '';

		}else{

			$correctStatusText4 = 'Nothing to repair';
			$correctStatusClass4 = 'cg_corrected';

		}

	}

}catch (Exception $e) {
	$correctStatusTextFull4 = '<span class="cg_database_improve_title">Please contact <a href="mailto:support@contest-gallery.com">support@contest-gallery.com</a><br>Copy and send following data:</span>';
	$correctStatusTextFull4 .= '<span class="cg_database_error_message">'.$e->getMessage().'</span>';

	$correctStatusText4 = 'Repair';
	$correctStatusClass4 = '';
}

// Correct 5 from HERE
$correctStatusText5 = 'Correct';
$correctStatusClass5 = '';

include ('corrections/correction-not-visibile-frontend.php');


$correctStatusTextFull7 = 'No mail exceptions so far';
$correctStatusText7 = 'Everything correct';
$correctStatusClass7 = 'cg_corrected';
$correctStatusDownloadClass7 = 'cg_corrected';
$correctStatusExceptions7 = '';
$correctStatusExceptionsReturn7 = '';
$cg_file_name_mail_log = '';

$fileName = md5(wp_salt( 'auth').'---cnglog---'.$GalleryID);
$cg_file_name_mail_log = $fileName;

$file = $uploadFolder['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/logs/errors/mail-'.$fileName.'.log';

if(file_exists($file)){
	if(!empty($_POST['cg_remove_mail_exceptions_log'])){
		unlink($file);
	}else{
		$correctStatusTextFull7 = 'Some mail exceptions happend';
		$correctStatusText7 = 'Show exceptions';
		$correctStatusClass7 = 'cg_corrections_container_exception';
		$correctStatusDownloadClass7 = '';
		$fp = fopen($file, 'r');
		$correctStatusExceptions7 = fread($fp, 30000);
		fclose($fp);

		ob_start();
		echo "<pre>";
		print_r($correctStatusExceptions7);
		echo "</pre>";
		$correctStatusExceptionsReturn7 = ob_get_clean();
	}

}

$cg_file_name_mail_log_general = '';
$fileNameGeneral = md5(wp_salt( 'auth').'---cnglog---'.'0');
$fileGeneral = $uploadFolder['basedir'].'/contest-gallery/gallery-general/logs/errors/mail-'.$fileNameGeneral.'.log';

if(file_exists($fileGeneral)){
	if(!empty($_POST['cg_remove_mail_exceptions_log'])){
		unlink($fileGeneral);
	}else{
		if(!empty($correctStatusExceptions7)){
			$cg_file_name_mail_log_general = $fileNameGeneral;
			$fp = fopen($fileGeneral, 'r');
			$exceptionGeneral = fread($fp, 30000);
			$correctStatusExceptionsReturn7 .= "\r\n";
			$correctStatusExceptionsReturn7 .= $exceptionGeneral;
		}else{
			$cg_file_name_mail_log_general = $fileNameGeneral;
			$correctStatusTextFull7 = 'Some mail exceptions happend';
			$correctStatusText7 = 'Show exceptions';
			$correctStatusClass7 = 'cg_corrections_container_exception';
			$correctStatusDownloadClass7 = '';
			$fp = fopen($fileGeneral, 'r');
			$correctStatusExceptions7 = fread($fp, 30000);
			fclose($fp);
			ob_start();
			echo "<pre>";
			print_r($correctStatusExceptions7);
			echo "</pre>";
			$correctStatusExceptionsReturn7 = ob_get_clean();
		}
	}

}

$cgGetVersion = cg_get_version();
$pagesRepairText = '';
if(intval($options->Version)>=21){
	$pagesRepairText = '<br><b>If Contest Gallery Custom Type Pages were deleted then will be recreated.</b>';
}

echo <<<HEREDOC
<div id="cgCorrections">
    <div class="cg_corrections_container">
        <div class="cg_corrections_explanation">
            <div class="cg_corrections_title">Repair frontend?</div>
            <div class="cg_corrections_description">Many of the data is cached in json files.<br>If there were some changes done in database manually or json files were deleted then json files can be renewed here again.$pagesRepairText</div>
        </div>
        <div class="cg_corrections_action $correctStatusClass6">                
            <form method="POST" action="?page=$cgGetVersion/index.php&amp;corrections_and_improvements=true&amp;option_id=$GalleryID" data-cg-submit-message="Frontend repaired"  class="cg_load_backend_submit">
                <input type="hidden" name="action_correct_not_visible_for_frontend" value="true">
                <input type="hidden" name="option_id" value="$GalleryID">
                <span class="cg_corrections_action_submit">$correctStatusText6</span>
            </form>
        </div>
    </div>
    <div class="cg_corrections_container">
        <div class="cg_corrections_explanation">
            <div class="cg_corrections_title">Database status</div>
            <div class="cg_corrections_description">$correctStatusTextFull4</div>
        </div>
        <div class="cg_corrections_action $correctStatusClass4">                
            <form method="POST" action="?page=$cgGetVersion/index.php&amp;corrections_and_improvements=true&amp;option_id=$GalleryID" class="cg_load_backend_submit">
                <input type="hidden" name="action_check_and_correct_database" value="true">
                <input type="hidden" name="option_id" value="$GalleryID">
                <span class="cg_corrections_action_submit">$correctStatusText4</span>
            </form>
        </div>
    </div>
    <div class="cg_corrections_container">
        <div class="cg_corrections_explanation">
            <div class="cg_corrections_title">Mailing status</div>
            <div class="cg_corrections_description">$correctStatusTextFull7</div>
        </div>
        <div class="cg_corrections_action $correctStatusClass7" id="cgCorrect7showExceptionsButton" style="width:40%;text-align:center;">
            $correctStatusText7         
        </div>
        <div id="cgCorrect7exceptions" class="cg_hide" style="width:100%;">
            <div>$correctStatusExceptionsReturn7</div>
            <div class="cg_corrections_action $correctStatusDownloadClass7" style="width:40%;text-align:center;">
                 <form method="POST" action="?page=$cgGetVersion/index.php&amp;corrections_and_improvements=true&amp;option_id=$GalleryID">
                    <input type="hidden" name="cg_action_check_and_download_mail_log_for_gallery" value="true">
                    <input type="hidden" name="cg_file_name_mail_log" value="$cg_file_name_mail_log">
                    <input type="hidden" name="cg_file_name_mail_log_general" value="$cg_file_name_mail_log_general">
                    <input type="hidden" name="option_id" value="$GalleryID">
                    <span class="cg_corrections_action_submit">Download full log</span>
                </form>
            </div>
            <div class="cg_corrections_action $correctStatusClass7" style="width:40%;text-align:center;margin-top:30px;">
                <form method="POST" action="?page=$cgGetVersion/index.php&amp;corrections_and_improvements=true&amp;option_id=$GalleryID" class="cg_load_backend_submit">
                    <input type="hidden" name="cg_remove_mail_exceptions_log" value="true">
                    <input type="hidden" name="option_id" value="$GalleryID">
                    <span class="cg_corrections_action_submit">Remove exceptions log</span>
                </form>
            </div>
        </div>
    </div>
</div>
HEREDOC;




?>