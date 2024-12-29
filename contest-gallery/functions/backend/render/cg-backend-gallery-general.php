<?php

if(!function_exists('cg_move_to_another_gallery_container')){
	function cg_move_to_another_gallery_container($GalleryID){

		echo "<div id='cgMoveToAnotherGalleryContainer' class='cg_backend_action_container cg_hide' style='min-height: unset;'>
<span class='cg_message_close'></span>";
		$urlAfterMove = '?page='.cg_get_version().'/index.php&edit_gallery=true';
		?>
        <form enctype="multipart/form-data" id="cg_move_to_another_gallery_form" action='<?php echo '?page="'.cg_get_version().'"/index.php'; ?>' method='POST'>
            <input type='hidden' name='cgGalleryHash' value='<?php echo md5(wp_salt( 'auth').'---cngl1---'.$GalleryID);?>'>
            <input type='hidden' name='cgMoveFromGalleryID' value='<?php echo $GalleryID;?>'>
            <input type='hidden' name='cgMoveRealId' value='<?php echo $GalleryID;?>'>
            <input type='hidden' name='action' value='post_cg_move_to_another_gallery'>
			<?php

		echo "<div id='cgMoveToAnotherGalleryLoader' class='cg-lds-dual-ring-gallery-hide cg_hide'></div>";
		echo "<div id='cgMoveToAnotherGalleryContent' class='cg_hide'>";
        echo '<span class="cg_hide"><a href="'.$urlAfterMove.'"  id="cgMoveUrlAfterMove" ></a></span>';

		echo "<div><b>Entry ID <span id='cgMoveEntryId'></span></b><br>Select in which gallery to move</div>";

				        echo "<select name='cg_in_gallery_id_to_move' id='cg_in_gallery_id_to_move_select' disabled class='cg_disabled_background_color_e0e0e0 cg_action_select' ><option>No another galleries available</option></select>";

					echo '<p id="cgMoveAssignFields">Assign fields to gallery <span id="cgMoveToAnotherGalleryId"></span> fields</p>';
					echo '<div  id="cgMoveToAnotherGalleryCompare"></div>';

			echo '<div id="cg_in_gallery_id_to_move_go_checkbox_container" class="cg_hover_effect   cg_disabled_background_color_e0e0e0" ><input type="checkbox" id="cg_in_gallery_id_to_move_go_checkbox" style="margin-top: unset;margin-right: 5px;"><label for="cg_in_gallery_id_to_move_go_checkbox" >Open gallery to which moved</label>
</div>';

        echo '<div  id="cgMoveToAnotherGallerySubmitContainer">
            <div  id="cgMoveToAnotherGallerySubmit" class="cg_backend_button_gallery_action cg_disabled_background_color_e0e0e0" style="margin-left: auto; margin-right: auto;">Move to selected gallery</div>
        </div>';

		echo "</div>";
        ?>
        </form>
		<?php
        echo "</div>";

	}
}


if(!function_exists('cg_is_for_wp_page_title_unchecked')){
	function cg_is_for_wp_page_title_unchecked($GalleryID,$bloginfo_wpurl,$CgEntriesOwnSlugName,$dbGalleryVersion){

        if(floatval($dbGalleryVersion)>=24){
            echo <<<HEREDOC
<div id='cgIsForWpPageTitleUnchecked' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_main_options' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
	        <div class='cg_view_options_row ' >
	            <div class='cg_view_option cg_view_option_full_width cg_border_radius_8_px '>
	                    <p>Unchecking<br><b>Use as entry page title</b><br>will change the URLs of all current and future entry pages<br><b>from</b><br>
	                  $bloginfo_wpurl/contest-galleries/contest-gallery-$GalleryID/<b>{input_field_content}</b><br><b>to</b><br>$bloginfo_wpurl/contest-galleries/contest-gallery-$GalleryID/<b>{filename}</b>
	                    </p>
	            </div>
	        </div>
	 </div>
 </div>
HEREDOC;
        }else{
		echo <<<HEREDOC
<div id='cgIsForWpPageTitleUnchecked' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_main_options' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
	        <div class='cg_view_options_row ' >
	            <div class='cg_view_option cg_view_option_full_width cg_border_radius_8_px '>
	                    <p>Unchecking<br><b>Use as entry page title</b><br>will change the URLs of all current and future entry pages<br><b>from</b><br>
	                  $bloginfo_wpurl/$CgEntriesOwnSlugName/contest-gallery-id-$GalleryID/<b>{input_field_content}</b><br><b>to</b><br>$bloginfo_wpurl/$CgEntriesOwnSlugName/contest-gallery-id-$GalleryID/<b>{filename}</b>
	                    </p>
	            </div>
	        </div>
	 </div>
 </div>
HEREDOC;
	}

	}
}
if(!function_exists('cg_is_for_wp_page_title_checked')){
	function cg_is_for_wp_page_title_checked($GalleryID,$bloginfo_wpurl,$CgEntriesOwnSlugName,$dbGalleryVersion){

        if(floatval($dbGalleryVersion)>=24){
            echo <<<HEREDOC
<div id='cgIsForWpPageTitleChecked' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_main_options' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
	        <div class='cg_view_options_row ' >
	            <div class='cg_view_option cg_view_option_full_width cg_border_radius_8_px '>
	                    <p>Checking<br><b>Use as entry page title</b><br>will change the URLs of all current and future entry pages<br><b>from</b><br>
	                  $bloginfo_wpurl/contest-galleries/contest-gallery-$GalleryID/<b>{filename}</b><br><b>to</b><br>
$bloginfo_wpurl/contest-galleries/contest-gallery-$GalleryID/<b>{input_field_content}</b><br>
					if no <b>{input_field_content}</b> set then <b>{filename}</b> will be taken
	                    </p>
	            </div>
	        </div>
	 </div>
 </div>
HEREDOC;
        }else{
		echo <<<HEREDOC
<div id='cgIsForWpPageTitleChecked' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'>
	<span  class='cg_message_close '></span>
	<div class='cg_main_options' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
	        <div class='cg_view_options_row ' >
	            <div class='cg_view_option cg_view_option_full_width cg_border_radius_8_px '>
	                    <p>Checking<br><b>Use as entry page title</b><br>will change the URLs of all current and future entry pages<br><b>from</b><br>
	                  $bloginfo_wpurl/$CgEntriesOwnSlugName/contest-gallery-id-$GalleryID/<b>{filename}</b><br><b>to</b><br>
$bloginfo_wpurl/$CgEntriesOwnSlugName/contest-gallery-id-$GalleryID/<b>{input_field_content}</b><br>
					if no <b>{input_field_content}</b> set then <b>{filename}</b> will be taken
	                    </p>
	            </div>
	        </div>
	 </div>
 </div>
HEREDOC;
	}

	}
}

if (!function_exists('cg_backend_gallery_render_reload_entry_loader')) {
    function cg_backend_gallery_render_reload_entry_loader()
    {
        echo <<<HEREDOC
 <div id="cgReloadEntryLoader"
     class="cgReloadEntryLoader cg_skeleton_loader_on_page_load_div cg_skeleton_loader_on_page_load_div_thumb_view cg_hide">
    <div class="cg_skeleton_loader_on_page_load_container" 
         style="width:162px;height:398px;margin-bottom:20px;margin-left:5px;">
        <div class="cg_skeleton_loader_on_page_load" style="width:100%;height:100%;"></div>
    </div>
    <div class="cg_skeleton_loader_on_page_load_container"
         style="margin-left:25px;margin-right:20px;display: flex; flex-flow: column; align-items: start;flex-grow:1;justify-content: start;">
        <div class="cg_skeleton_loader_on_page_load" style="width:20%;height:30px;margin-bottom:16px;"></div>
        <div class="cg_skeleton_loader_on_page_load" style="width:40%;height:30px;margin-bottom:16px;"></div>
        <div class="cg_skeleton_loader_on_page_load" style="width:50%;height:30px;margin-bottom:16px;"></div>
        <div class="cg_skeleton_loader_on_page_load" style="width:50%;height:30px;margin-bottom:16px;"></div>
        <div class="cg_skeleton_loader_on_page_load" style="width:60%;height:30px;margin-bottom:16px;"></div>
        <div class="cg_skeleton_loader_on_page_load" style="width:70%;height:30px;margin-bottom:16px;"></div>
        <div class="cg_skeleton_loader_on_page_load" style="width:80%;height:30px;margin-bottom:16px;"></div>
        <div class="cg_skeleton_loader_on_page_load" style="width:90%;height:30px;margin-bottom:16px;"></div>
        <div class="cg_skeleton_loader_on_page_load" style="width:100%;height:30px;margin-bottom:16px;"></div>
    </div>
</div>
HEREDOC;
    }
}

if(!function_exists('cg_ask_if_should_be_removed_from_sale_if_multiple')){
    function cg_ask_if_should_be_removed_from_sale_if_multiple(){
        echo "<div id='cgAskIfShouldBeRemovedFromSaleIfMultiple' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'><span id='cgAskIfShouldBeRemovedFromSaleIfMultipleClose' class='cg_message_close  cg_message_close_extra_processing '></span><p>Remove file from download sale?</p><a id='cgAskIfShouldBeRemovedFromSaleIfMultipleButton' class='cg_image_action_href' href=\"#\" ><span class='cg_image_action_span'>Yes, please remove from sale</span>
</a></div>";
    }
}

if(!function_exists('cg_ecommerce_show_api_response')){
    function cg_ecommerce_show_api_response(){

        echo "<div id='cgEcommerceShowApiResponseContainer' class='cg_hide cg_height_auto cg_backend_action_container  cg_overflow_y_hidden' style='z-index: 9999999;height:80%;'><span class='cg_message_close'></span>";
            echo "<div class='cg-lds-dual-ring-gallery-hide cg_hide'></div>";
            echo "<div id='cgEcommerceShowApiResponse' class='cg_hide' style='height:80%;'>";
                echo '<textarea id="cgEcommerceShowApiResponseTextarea" readonly style="width:100%;height:100%;margin-top:30px;"></textarea>';
            echo "</div>";
        echo "</div>";

    }
}

?>