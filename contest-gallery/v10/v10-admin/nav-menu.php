<?php

include(__DIR__.'/elements/documentation.php');

	echo "<input type='hidden' id='cgGetVersionForUrlJs' value='".cg_get_version()."' />";

    ###NORMAL###
    if(!empty($cgProVersion)){// check with no empty!
        include('normal/download-proper-pro-version-info-general-headers-area.php');
    }
    ###NORMAL-END###

cg_shortcode_interval_configuration_container($GalleryID,$cgProFalse);

	echo "<div  id='cg_shortcode_table' class='cg_shortcode_table cg_do_not_remove_when_ajax_load'>";

if(empty($GalleryName)){$GalleryName="";}
	
		$versionColor = "#444";

if(empty($cgProVersionLink)){
    $cgProVersionLink = '';
}
$galeryNR = $GalleryID;

include("nav-shortcode.php");

	echo "</div>";
	echo "</div>";
	
	echo "<div id='cg_nav_menu_row_container'  class='cg_do_not_remove_when_ajax_load cg_nav_menu_row_container' >";
	echo "<div class='cg_nav_menu_row'>";
	echo "<div ><a href='?page=".cg_get_version()."/index.php&option_id=$GalleryID&edit_gallery=true' class='cg_load_backend_link cg_load_backend_link_back_to_gallery' ><input class='cg_backend_button cg_backend_button_back'  type='submit' value='Back to gallery'  /></a><br/></div>";
	echo "<div><a id='cgNavMenuContactForm'   href='?page=".cg_get_version()."/index.php&option_id=$GalleryID&define_upload=true' class='cg_load_backend_link'><input type='submit' class='cg_backend_button cg_backend_button_general' style='padding-left: 20px;'  value='Edit upload form' /></form><br/></div>";
	echo "<div>";
		echo "<a id='cgNavMenuRegForm'   href='?page=".cg_get_version()."/index.php&create_user_form=true&option_id=$GalleryID' class='cg_load_backend_link'><input type='hidden' name='option_id' value='$GalleryID'><input class='cg_backend_button cg_backend_button_general'  type='submit'   style='padding-left: 25px;' value='Edit registration form'  /></a>";
	echo "</div>";
    echo "<div>";
		echo "<a  id='cgNavMenuUsersManagement'  href='?page=".cg_get_version()."/index.php&users_management=true&option_id=$GalleryID' class='cg_load_backend_link'><input class='cg_backend_button cg_backend_button_general'  style='padding-left: 20px;'   type='button' id='cgUsersManagement' value='Users management' /></a>";
	echo "</div>";
	echo "</div>";

    echo "<div class='cg_nav_menu_row'>";
        echo "<div><a id='cgEditOptionsButton' href='?page=".cg_get_version()."/index.php&edit_options=true&option_id=$GalleryID' class='cg_load_backend_link'><input type='submit' class='cg_backend_button cg_backend_button_general'   style='padding-left: 20px;'  value='Edit options'  /></a><br/></div>";
        echo "<div><a  id='cgNavMenuEditTranslations'  href='?page=".cg_get_version()."/index.php&edit_options=true&option_id=$GalleryID&cg_edit_translations=true' class='cg_load_backend_link cg_edit_translations'><input type='submit' class='cg_backend_button cg_backend_button_general '  style='padding-left: 20px;'  value='Edit translations'  /></a><br/></div>";
        echo "<div><a  id='cgNavMenuEditEcommerce'   href='?page=".cg_get_version()."/index.php&edit_options=true&option_id=$GalleryID&cg_edit_ecommerce=true' class='cg_load_backend_link cg_edit_ecommerce'><input type='submit' class='cg_backend_button cg_backend_button_general '   style='padding-left: 20px;'  value='Edit ecommerce'  /></a><br/></div>";
        echo "<div><a  id='cgNavMenuEcommerceOrders'   href='?page=".cg_get_version()."/index.php&option_id=$GalleryID&cg_orders=true' class='cg_load_backend_link'><input type='submit' class='cg_backend_button cg_backend_button_general '   style='padding-left: 20px;'  value='Ecommerce orders'  /></a><br/></div>";
	    echo "</div>";

	echo "</div>";



    if(!empty($isEditOptions)){
        //include('nav-users-management-with-status-and-repair.php');
    }else{
      //  include('nav-users-management.php');
    }


?>