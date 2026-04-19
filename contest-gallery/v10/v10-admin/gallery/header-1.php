<?php

//$GalleryID = @$_GET['option_id'];

$cg_backend_allowed_file_types = array(
    array(
        'label' => 'Images',
        'types' => array(
            array('label' => 'JPG', 'class' => ''),
            array('label' => 'PNG', 'class' => ''),
            array('label' => 'GIF', 'class' => '')
        )
    ),
    array(
        'label' => 'Files',
        'types' => array(
            array('label' => 'TXT', 'class' => ''),
            array('label' => 'DOC', 'class' => ''),
            array('label' => 'DOCX', 'class' => ''),
            array('label' => 'XLS', 'class' => ''),
            array('label' => 'XLSX', 'class' => ''),
            array('label' => 'PPT', 'class' => ''),
            array('label' => 'PPTX', 'class' => ''),
            array('label' => 'CSV', 'class' => ''),
            array('label' => 'PDF', 'class' => $cgProFalse),
            array('label' => 'ZIP', 'class' => $cgProFalse)
        )
    ),
    array(
        'label' => 'Audio',
        'types' => array(
            array('label' => 'M4A', 'class' => ''),
            array('label' => 'OGG', 'class' => ''),
            array('label' => 'WAV', 'class' => $cgProFalse),
            array('label' => 'MP3', 'class' => $cgProFalse)
        )
    ),
    array(
        'label' => 'Video',
        'types' => array(
            array('label' => 'WEBM', 'class' => ''),
            array('label' => 'MP4', 'class' => $cgProFalse),
            array('label' => 'MOV', 'class' => $cgProFalse)
        )
    )
);

$cg_backend_memory_limit_class = 'cg_gallery_backend_status_neutral';
$cg_backend_memory_limit_text = 'No memory limit set from server. Real memory limit unrecognizable.';
if($memory_limit !== '-1'){
    $cg_backend_memory_limit_text = $memory_limit.' MB';
    if($memory_limit >= 250){
        $cg_backend_memory_limit_class = 'cg_gallery_backend_status_good';
    }else if($memory_limit >= 120){
        $cg_backend_memory_limit_class = 'cg_gallery_backend_status_warn';
    }else{
        $cg_backend_memory_limit_class = 'cg_gallery_backend_status_bad';
    }
}

$cg_backend_max_input_vars_class = 'cg_gallery_backend_status_bad';
if($max_input_vars >= 3000){
    $cg_backend_max_input_vars_class = 'cg_gallery_backend_status_good';
} else if($max_input_vars >= 1000){
    $cg_backend_max_input_vars_class = 'cg_gallery_backend_status_warn';
}

echo "<div id='cgGalleryBackendContainer'>";
echo "<table id='cgGalleryBackendDataManagement'>";
echo "<tr class='cg_gallery_backend_data_management_row'>";
echo "<td class='cg_gallery_backend_data_cell cg_gallery_backend_info_cell' id='cgGalleryBackendAllowedFileTypes'>";
echo "<div class='cg_gallery_backend_info_text'>";
echo "<div class='cg_gallery_backend_info_section'>";
echo "<div class='cg_gallery_backend_info_section_title'>Allowed file types to add via backend</div>";
echo "<div class='cg_gallery_backend_file_types'>";
foreach($cg_backend_allowed_file_types as $cg_backend_allowed_file_types_row){
    echo "<div class='cg_gallery_backend_file_type_row'>";
    echo "<div class='cg_gallery_backend_file_type_label'>".$cg_backend_allowed_file_types_row['label']."</div>";
    echo "<div class='cg_gallery_backend_file_type_pills'>";
    foreach($cg_backend_allowed_file_types_row['types'] as $cg_backend_allowed_file_type){
        echo "<span class='cg_gallery_backend_file_type_pill ".$cg_backend_allowed_file_type['class']."'>".$cg_backend_allowed_file_type['label']."</span>";
    }
    echo "</div>";
    echo "</div>";
}
echo "</div>";
if(is_multisite()){
    echo "<div class='cg_gallery_backend_note cg_gallery_backend_note_multisite'><span class='cg_gallery_backend_note_title'>Multisite note</span> Some of the file types above which are allowed by default for a WordPress Single Site installation might not be allowed by default for a Multisite.<br><br><b>Allowed Multisite file types can be configured in:</b><br>Network Admin >>> Settings >>> Upload file types</div>";
}
echo "</div>";

echo "<div class='cg_gallery_backend_frontend_hint'><span class='cg_gallery_backend_frontend_hint_label'>Allowed file types frontend</span><span class='cg_gallery_backend_frontend_hint_value'>configurable in <b>\"Edit upload form\"</b></span></div>";

echo "<div class='cg_gallery_backend_info_section'>";
echo "<div class='cg_gallery_backend_info_section_title'>Server limits</div>";
echo "<div class='cg_gallery_backend_system_grid'>";

echo "<div class='cg_gallery_backend_system_item'>";
echo "<div class='cg_gallery_backend_system_label'>Maximum <b>upload_max_filesize</b> in your PHP configuration</div>";
echo "<div class='cg_gallery_backend_system_footer'><span class='cg_gallery_backend_system_value cg_gallery_backend_status_neutral'>$upload_max_filesize MB</span><span class='cg_gallery_backend_system_info'><span class=\"cg-info-icon\"><strong>info</strong></span><span class=\"cg-info-container\">Maximum upload size per file<br><br>To increase in .htaccess file use:<br><b>php_value upload_max_filesize 10MB</b> (example, no equal to sign!)<br>To increase in php.ini file use:<br><b>upload_max_filesize = 10MB</b> (example, equal to sign required!)<br><br><b>Some server providers does not allow manually increase in files.<br>It has to be done in providers backend or they have to be contacted.</b></span></span></div>";
echo "</div>";

echo "<div class='cg_gallery_backend_system_item'>";
echo "<div class='cg_gallery_backend_system_label'>Maximum <b>post_max_size</b> in your PHP configuration</div>";
echo "<div class='cg_gallery_backend_system_footer'><span class='cg_gallery_backend_system_value cg_gallery_backend_status_neutral'>$post_max_size MB</span><span class='cg_gallery_backend_system_info'><span class=\"cg-info-icon\"><strong>info</strong></span><span class=\"cg-info-container\">Describes the maximum size of a post which can be done when form submits.<br><br>Example: you try to upload 3 files with each 3MB and post_max_size is 6MB, then it will not work.<br><br>To increase in htaccess file use:<br><b>php_value post_max_size 10MB</b> (example, no equal to sign!)<br>To increase in php.ini file use:<br><b>post_max_size = 10MB</b> (example, equal to sign required!)<br><br><b>Some server providers does not allow manually increase in files.<br>It has to be done in providers backend or they have to be contacted.</b></span></span></div>";
echo "</div>";

echo "<div class='cg_gallery_backend_system_item'>";
echo "<div class='cg_gallery_backend_system_label'>Memory limit provided from your server provider</div>";
if($memory_limit == '-1'){
    echo "<div class='cg_gallery_backend_system_value_text'>".$cg_backend_memory_limit_text."</div>";
}else{
    echo "<div class='cg_gallery_backend_system_footer'><span class='cg_gallery_backend_system_value ".$cg_backend_memory_limit_class."'>".$cg_backend_memory_limit_text."</span></div>";
}
echo "</div>";

echo "<div class='cg_gallery_backend_system_item'>";
echo "<div class='cg_gallery_backend_system_label'>Maximum <b>max_input_vars</b> in your PHP configuration</div>";
echo "<div class='cg_gallery_backend_system_footer'><span class='cg_gallery_backend_system_value ".$cg_backend_max_input_vars_class."'>$max_input_vars</span><span class='cg_gallery_backend_system_info'><span class=\"cg-info-icon\"><strong>info</strong></span><span class=\"cg-info-container\">Important for how many information can be processed in backend<br><b>If 2000 and higher 50 files per site can be shown in backend</b><br><br>To increase in htaccess file use:<br><b>php_value max_input_vars 2000</b> (example, no equal to sign!)<br>To increase in php.ini file use:<br><b>max_input_vars = 2000</b> (example, equal to sign required!)<br><br><b>Some server providers does not allow manually increase in files.<br>It has to be done in providers backend or they have to be contacted.</b></span></span></div>";
echo "</div>";

echo "</div>";
echo "</div>";


if($cgVersion<7){
    echo "&nbsp;&nbsp;<a id='cg_server_power_info'><b><u>INFO</u></b></a></b>";
    ?>
    <div id="cg_answerPNG" style="position: absolute; margin-left: 135px; margin-top: 10px;width: 460px; background-color: white; border: 1px solid; padding: 5px; display: none;">
        Higher memory allows you to upload bigger images with higher resolution.<br>
        If you receive an error during upload like "Allowed memory size of ... exhausted",
        then try to upload same image in minor resolution.<br>
        ≈256 MB: good <br>
        ≈128 MB: average <br>
        ≈64 MB: poor <br></div>

    <?php
}

echo "</div>";


echo "</td>";

echo "<td align='center' class='cg_gallery_backend_data_cell cg_gallery_backend_actions_cell'><div class='cg_gallery_backend_actions'><div class='cg_gallery_backend_actions_panel'>";

if ($_POST['contest_gal1ery_create_zip']==true or ($_POST['chooseAction1'] == 4 and ($_POST['informId']==true or $_POST['resetInformId']==true))) {


    $allPics=array();
    //$pfad = $_SERVER['DOCUMENT_ROOT'];
    $uploadFolder = wp_upload_dir();

    $pfad = $uploadFolder['basedir'];
    $baseurl = $uploadFolder['baseurl'];

    $is_ssl = false;
    if(is_ssl()){
        $is_ssl = true;
    }

    if($is_ssl){
        if(strpos($baseurl,'http://')===0){
            $baseurl = str_replace( 'http://', 'https://', $baseurl );
        }
    }else{
        if(strpos($baseurl,'https://')===0){
            $baseurl = str_replace( 'https://', 'http://', $baseurl );
        }
    }

    $baseurlParsedPath = wp_parse_url($baseurl, PHP_URL_PATH);

    if(!empty($_POST['contest_gal1ery_create_zip'])){

        $selectSQLall = $wpdb->get_results( "SELECT * FROM $tablename WHERE GalleryID = '$GalleryID' AND WpUpload>0 AND NOT (ImgType = 'ytb' OR ImgType = 'inst' OR ImgType = 'tkt' OR ImgType = 'twt')");

	    foreach($selectSQLall as $value){
                if(!empty($value->MultipleFiles) && $value->MultipleFiles!='""'){
                    $MultipleFilesUnserialized = unserialize($value->MultipleFiles);
                    if(!empty($MultipleFilesUnserialized)){//check for sure if really exists and unserialize went right, because might happen that "" was in database from earlier versions
                        foreach($MultipleFilesUnserialized as $order => $MultipleFile){
                            if($order==1 && empty($MultipleFile['isRealIdSource'])){
                                $image_url = $MultipleFile['guid'];
                            }else{
                                if(!empty($MultipleFile['isRealIdSource'])){
                                    $image_url = $wpdb->get_var("SELECT guid FROM $table_posts WHERE ID = '".$value->WpUpload."'");
                                }else{
                                    $image_url = $MultipleFile['guid'];
                                }
                            }
                            if($is_ssl){
                                if(strpos($image_url,'http://')===0){
                                    $image_url = str_replace( 'http://', 'https://', $image_url );
                                }
                            }else{
                                if(strpos($image_url,'https://')===0){
                                    $image_url = str_replace( 'https://', 'http://', $image_url );
                                }
                            }

                            $image_url_path = wp_parse_url($image_url, PHP_URL_PATH);
                            $relative_image_path = '';
                            if(!empty($image_url_path) && !empty($baseurlParsedPath) && strpos($image_url_path, $baseurlParsedPath) === 0){
                                $relative_image_path = substr($image_url_path, strlen($baseurlParsedPath));
                            }

                            if($relative_image_path !== '' && $relative_image_path !== false){
                                $dl_image_original = $pfad.$relative_image_path;
                                $allPics[] = $dl_image_original;
                            }
                        }
                    }
                }else{

                    $image_url = $wpdb->get_var("SELECT guid FROM $table_posts WHERE ID = '".$value->WpUpload."'");

                    if($is_ssl){
                        if(strpos($image_url,'http://')===0){
                            $image_url = str_replace( 'http://', 'https://', $image_url );
                        }
                    }else{
                        if(strpos($image_url,'https://')===0){
                            $image_url = str_replace( 'https://', 'http://', $image_url );
                        }
                    }

                    $image_url_path = wp_parse_url($image_url, PHP_URL_PATH);
                    $relative_image_path = '';
                    if(!empty($image_url_path) && !empty($baseurlParsedPath) && strpos($image_url_path, $baseurlParsedPath) === 0){
                        $relative_image_path = substr($image_url_path, strlen($baseurlParsedPath));
                    }

                    if($relative_image_path !== '' && $relative_image_path !== false){
                        $dl_image_original = $pfad.$relative_image_path;
                        $allPics[] = $dl_image_original;
                    }
                }

        }
    }


/*    if(@$_POST['chooseAction1'] == 4 and (@$_POST['informId']==true or @$_POST['resetInformId'])){

        //echo "2131242131243";

        $informId = @$_POST['informId'];
        $resetInformId = @$_POST['resetInformId'];

        $selectPICS = "SELECT * FROM $tablename WHERE ";

        //$wpdb->get_results( );

        foreach(@$informId as $key => $value){

            $selectPICS .= "id=$value or ";

        }

        foreach(@$resetInformId as $key => $value){

            $selectPICS .= "id=$value or ";

        }

        $selectPICS = substr($selectPICS,0,-4);

        //print_r($selectPICS);

        $selectPICSzip = $wpdb->get_results("$selectPICS");

    }*/


    $admin_email = get_option('admin_email');
    $adminHashedPass = $wpdb->get_var("SELECT user_pass FROM $wpUsers WHERE user_email = '$admin_email'");

    $code = $wpdb->base_prefix; // database prefix
    $code = md5($code.$adminHashedPass);


    if (file_exists(''.$pfad.'/contest-gallery/gallery-id-'.$GalleryID.'/'.$code.'_images_download.zip')) {
        unlink(''.$pfad.'/contest-gallery/gallery-id-'.$GalleryID.'/'.$code.'_images_download.zip');
    }
    if(cg_action_create_zip($allPics,''.$pfad.'/contest-gallery/gallery-id-'.$GalleryID.'/'.$code.'_images_download.zip')==false){
        die;
    }
    else{
        cg_action_create_zip($allPics,''.$pfad.'/contest-gallery/gallery-id-'.$GalleryID.'/'.$code.'_images_download.zip');
    }

    $downloadZipFileLink = $baseurl.'/contest-gallery/gallery-id-'.$GalleryID.'/'.$code.'_images_download.zip';
    echo '<div class="cg_gallery_backend_actions_inner cg_gallery_backend_actions_inner_zip">';
    echo '<div class="cg_shortcode_parent cg_gallery_backend_zip_hint" id="cgDeleteZipFileHintContainer">';
    echo '<a href="'.$downloadZipFileLink.'" class="cg_gallery_backend_action_link cg_backend_button_gallery_action">Download zip file</a>';
    echo '<div class="cg_gallery_backend_zip_top">';
    echo '<div class="cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip"></div>';
    echo '<input type="hidden" class="cg_shortcode_copy_text" value="'.$downloadZipFileLink.'">';
    echo '<div class="cg_gallery_backend_zip_info"><span class="cg-info-icon">Read info before download</span>
    <span class="cg-info-container cg-info-container-gallery-user" style="display: none;"><strong>Info Windows users!!!</strong><br>A <strong>ZIP file</strong> can not be opened by standard Windows Software.<br>You have to download for example WinRAR (which is free)<br>to be able to open a <strong>ZIP file</strong> in Windows.<br><br><strong>The generated zip file link is unique coded for your page</strong><br>You can use the zip file link for sharing<br>or<br>You can delete the zip file from server space</span></div>';
    echo '</div>';
    echo '<form action="?page='.cg_get_version().'/index.php&option_id='.$GalleryID.'&edit_gallery=true" method="POST" class="cg_load_backend_submit cg_gallery_backend_action_form cg_gallery_backend_action_form_delete_zip">';
    echo '<input type="hidden" name="cg_delete_zip" value="true">';
    echo '<input class="cg_backend_button_gallery_action" type="submit" value="Delete zip file">';
    echo '</form>';
    echo '</div>';
    echo '</div>';

}
else {

    if(!empty($_POST['cg_delete_zip'])){
        $admin_email = get_option('admin_email');
        $adminHashedPass = $wpdb->get_var("SELECT user_pass FROM $wpUsers WHERE user_email = '$admin_email'");

        $code = $wpdb->base_prefix; // database prefix
        $code = md5($code.$adminHashedPass);
        $uploadFolder = wp_upload_dir();
        $pfad = $uploadFolder['basedir'];
        if(file_exists(''.$pfad.'/contest-gallery/gallery-id-'.$GalleryID.'/'.$code.'_images_download.zip')){
            unlink(''.$pfad.'/contest-gallery/gallery-id-'.$GalleryID.'/'.$code.'_images_download.zip');
        ?><script>alert('Zip file deleted');</script><?php
        }
    }

    if(!empty(['delete_data_csv'])){
        $admin_email = get_option('admin_email');
        $adminHashedPass = $wpdb->get_var("SELECT user_pass FROM $wpUsers WHERE user_email = '$admin_email'");
        $code = $wpdb->base_prefix; // database prefix
        $code = md5($code.$adminHashedPass);
        $dir = plugin_dir_path( __FILE__ );
        $dir = $dir.$code."_userdata.csv";
        if(file_exists($dir)){
            unlink($dir);
            ?><script>alert('CSV data file deleted.');</script><?php
        }
    }

    echo "<div class='cg_gallery_backend_actions_inner'>";
    echo "<div class='cg_gallery_backend_action_block cg_gallery_backend_action_block_zip'><form method='POST' action='?page=".cg_get_version()."/index.php&option_id=$GalleryID&edit_gallery=true' class='cg_load_backend_submit cg_gallery_backend_action_form'><input type='hidden' name='contest_gal1ery_create_zip' value='true' /><input class='cg_backend_button_gallery_action' type='submit' value='Zip all files' /></form></div>";

    echo "<div class='cg_gallery_backend_action_block cg_gallery_backend_action_block_votes'><form method='POST' action='?page=".cg_get_version()."/index.php&option_id=$GalleryID&cg_export_votes=true' class='cg_gallery_backend_action_form'><input type='hidden' name='cg_export_votes' value='true' /><input type='hidden' name='cg_export_votes_all' value='true' /><input type='hidden' name='cg_option_id' value='$GalleryID' /><input class='cg_backend_button_gallery_action' type='submit' value='Export all votes' /></form>";
    echo "<div class='cg_gallery_backend_action_info'><span class=\"cg-info-icon\">info</span>
    <span class=\"cg-info-container cg-info-container-gallery-user\">CSV file will be exported separated by semicolon ( ; )<br>For every image votes are also visible under \"Show votes\" in this area.<br>Votes can be also removed in \"Show votes\".</span>
    </div></div>";

    echo "<div class='cg_gallery_backend_action_block cg_gallery_backend_action_block_csv'><form method='POST' action='?page=".cg_get_version()."/index.php&option_id=$GalleryID&edit_gallery=true' class='cg_gallery_backend_action_form'><input type='hidden' name='contest_gal1ery_post_create_data_csv' value='true' /><input class='cg_backend_button_gallery_action' type='submit' value='Export all fields and total rating' /></form>";
    echo "<div class='cg_gallery_backend_action_info'><span class=\"cg-info-icon\">info</span>
    <span class=\"cg-info-container cg-info-container-gallery-user\">CSV file will be exported separated by semicolon ( ; )<br></span>
    </div></div>";

echo "</div>";

}
echo "</div></div></td>";

// since 28.0.2 not available anymore, because not required, a mail for an entry can be sent anytime
echo "<td align='center' class='cg_gallery_backend_data_cell cg_gallery_backend_reset_cell cg_hidden cg_pointer_events_none'>
<div id='cgResetAllInformed'>";

echo "<form method='POST' action='?page=".cg_get_version()."/index.php&option_id=$GalleryID&edit_gallery=true'  class='cg_load_backend_submit cg_load_backend_submit_form_submit cg_reset_all_informed'>
<input type='submit' class='cg_backend_button_gallery_action' value='Reset all informed' />";
echo "<input type='hidden'  name='reset_all' value='true'>";
echo "</form></a>";
echo "<div style='padding-top:2px;'><span class=\"cg-info-icon\">info</span>
    <span class=\"cg-info-container cg-info-container-gallery-user\" style=\"top: 60px; margin-left: -235px; display: none;\">If \"Send this activation e-mail when activating users files\" is activated<br>Then users will be informed<br>All informed users can be reseted here<br>They will be informed again if entry will be activated again<br>Entry has to be deactivated before</span>
    </div>";
echo "</div></td>";


echo "</tr>";

echo "</table>";

///////////// SHOW Pictures of certain galery





?>
