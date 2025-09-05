<?php
if(!defined('ABSPATH')){exit;}
require_once('get-data-create-upload-v10.php');

$iconsURL = plugins_url().'/'.cg_get_version().'/v10/v10-css';

$cgRecaptchaIconUrl = $iconsURL.'/backend/re-captcha.png';
$cgDragIcon = $iconsURL.'/backend/cg-drag-icon.png';

if (is_multisite()) {
    if(floatval($dbGalleryVersion)>=24){
	    $CgEntriesOwnSlugName = cg_get_blog_option( get_current_blog_id(),'CgEntriesOwnSlugNameGalleries');
    }else{
	    $CgEntriesOwnSlugName = cg_get_blog_option( get_current_blog_id(),'CgEntriesOwnSlugName');
    }
}else{
    if(floatval($dbGalleryVersion)>=24){
	    $CgEntriesOwnSlugName = get_option('CgEntriesOwnSlugNameGalleries');
    }else{
	    $CgEntriesOwnSlugName = get_option('CgEntriesOwnSlugName');
    }
}
if(empty($CgEntriesOwnSlugName)){$CgEntriesOwnSlugName='contest-gallery';}
$bloginfo_wpurl = get_bloginfo('wpurl');

cg_backend_background_drop();

$cgIsV22OrHigher = '';
if(floatval($dbGalleryVersion)>=22){
	cg_is_for_wp_page_title_unchecked($GalleryID,$bloginfo_wpurl,$CgEntriesOwnSlugName,$dbGalleryVersion);
	cg_is_for_wp_page_title_checked($GalleryID,$bloginfo_wpurl,$CgEntriesOwnSlugName,$dbGalleryVersion);
	$cgIsV22OrHigher = '1';
}

cg_row_columns($GalleryID);
cg_add_column($GalleryID,$cgProFalse,$dbGalleryVersion);
cg_delete_existing_fields_first($GalleryID);

echo "<input type='hidden' id='cgIsV22OrHigher' value='$cgIsV22OrHigher'/>";
echo "<input type='hidden' id='cgDragIcon' value='$cgDragIcon'/>";
echo "<input type='hidden' id='cgRecaptchaIconUrl' value='$cgRecaptchaIconUrl'/>";
echo "<input type='hidden' id='cgRecaptchaKey' value='6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'/>";

require_once(dirname(__FILE__) . "/../nav-menu.php");

if(!function_exists('cg_cg_set_default_editor')){
    function cg_cg_set_default_editor() {
        $r = 'html';
        return $r;
    }
}

add_filter( 'wp_default_editor', 'cg_cg_set_default_editor' );

// recaptcha-lang-options.php
$langOptions = include(__DIR__.'/../data/recaptcha-lang-options.php');

echo '<select name="ReCaLang" id="cgReCaLangToCopy" class="cg_hide">';

echo "<option value='' >Please select language</option>";

foreach($langOptions as $langKey => $lang){

    echo "<option value='$langKey' >$lang</option>";

}

echo '</select>';


echo '<div id="cg_main_options" style="margin-top: 0;box-shadow: unset;margin-bottom: 0;" class="cg_main_options">';

echo '<div id="cgUploadFieldsSelect">';
echo '<p class="cg_edit_form_options_label" style="margin-bottom: 0;">Upload form<span class="" style="margin-top: 0;display: block;font-size:16px;"><b class="cg_color_green">NEW:</b> Multiple columns drag and drop upload form builder since version 27.0.0. </span></p>';
//echo "<form name='defineUpload' enctype='multipart/form-data' action='?page='.cg_get_version().'/index.php&optionID=$GalleryID&defineUpload=true' id='form' method='post'>";

$fbLikeTitleAndDesc = '';

if($FbLike==1){
    $fbLikeTitleAndDesc = "<option class=\"$cgProFalse\" value=\"fbt\">Facebook share button title $cgProFalseText</option>
			<option class=\"$cgProFalse\" value=\"fbd\">Facebook share button description $cgProFalseText</option>";
}

echo "<input type='hidden' id='cgProFalseCheck' value='$cgProFalse' >";


$heredoc = <<<HEREDOC
	<select name="dauswahl" id="dauswahl" class="cg_hide" >
		<optgroup label="User fields">
			<option  value="nf">Input</option>
			<option value="kf">Textarea</option>
			<option value="se">Select</option>
			<option value="sec">Select Categories</option>
			<option class="$cgProFalse" value="dt">Date $cgProFalseText</option>
			<option class="$cgProFalse" value="ef">Email $cgProFalseText</option>
			<option value="url">URL</option>
			<option class="$cgProFalse" value="cb">Check agreement $cgProFalseText</option>
			$fbLikeTitleAndDesc
		 </optgroup>
		<optgroup label="Admin fields">
			<option class="$cgProFalse" value="ht">HTML $cgProFalseText</option>
			<option  value="caRo">Simple Captcha - I am not a robot</option>
			<option  value="caRoRe">Google reCAPTCHA - I am not a robot</option>
		 </optgroup>
	</select>
	<input id="cg_create_upload_add_field" class="cg_upload_dauswahl cg_hide" type="button" name="plus" value="Add field" >
	<select id="cgPlace" style="margin-left:5px;margin-right: 5px;" class="cg_hide">
        <option  value="place-top">Place top</option>
        <option  value="place-bottom">Place bottom</option>
    </select>
    <span id="cgCollapse" class="cg_uncollapsed cg_hide" >Collapse all</span>
	<span class="cg_save_form_button_parent" >
            <span id="cgSaveContactFormNavButton"  class="cg_save_form_button cg_backend_button_gallery_action" >Save form</span>
	</span>
	<div style="flex-basis:100%;height:0;"></div>
	</div>
HEREDOC;

echo $heredoc;


if(!empty($_POST['upload'])){
 //   echo "<p id='cg_changes_saved' style='font-size:18px;'><strong>Changes saved</strong></p>";
}

echo "<form class='cg_load_backend_submit'  data-cg-submit-message='Changes saved'  name='defineUpload' enctype='multipart/form-data' action='?page=".cg_get_version()."/index.php&option_id=$GalleryID&define_upload=true' id='cgCreateUploadForm' method='post'>";
wp_nonce_field( 'cg_admin');

echo "<input type='hidden' name='option_id' value='$GalleryID'>";
echo "<input type='hidden' id='isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized' name='isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized' value='' >";

?>

<div id="cgCreateUploadContainer" ondragover="cgJsClassAdmin.createUpload.dragAndDrop.ondragoverCreateUploadContainer(event)"   >
    <?php
    echo '<div id="cgUploadFormDescription" ><b>NOTE:</b> added fields will be available as content fields for all file entries, or entries without files</div>';
    ?>
    <div id="ausgabe1" class="cg_create_upload" >
        <?php
        include ('left-side.php');
        ?>
    </div>
    <div id="cgRightSide" >
        <?php
        include ('right-side.php');
        ?>
    </div>

</div>

<div id="submitUploadRegFormContainer" >
    <input id="submitForm" type="submit" name="submit" class="cg_backend_button_gallery_action" value="Save form" style="font-weight:bold;text-align:center;width:150px;float:right;margin-right:10px;margin-bottom:10px;">
</div>
</form>
