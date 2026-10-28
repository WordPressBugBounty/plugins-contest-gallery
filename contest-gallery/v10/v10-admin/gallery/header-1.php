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

echo "<td align='center' class='cg_gallery_backend_data_cell cg_gallery_backend_actions_cell'><div class='cg_gallery_backend_actions'><div class='cg_gallery_backend_actions_panel cg-gallery-export-panel'>";

cg_gallery_export_buttons($GalleryID);

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
