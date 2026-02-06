<?php
require_once('get-data-create-registry.php');
require_once(dirname(__FILE__) . "/../../../nav-menu.php");

$iconsURL = plugins_url().'/'.cg_get_version().'/v10/v10-css';

$cgRecaptchaIconUrl = $iconsURL.'/backend/re-captcha.png';
$cgDragIcon = $iconsURL.'/backend/cg-drag-icon.png';

cg_row_columns($GalleryID);

cg_add_reg_field($GalleryID,$cgProFalse,$galleryDbVersion);

echo "<input type='hidden' id='cgDragIcon' value='$cgDragIcon'/>";
echo "<input type='hidden' id='cgRecaptchaIconUrl' value='$cgRecaptchaIconUrl'/>";
echo "<input type='hidden' id='cgRecaptchaKey' value='6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'/>";

if(!function_exists('cg_cg_set_default_editor')){
    function cg_cg_set_default_editor() {
        $r = 'html';
        return $r;
    }
}

$cgBeforeSinceV14ExplanationRequired = false;

if(cg_check_if_new_registry_logic_explanation_note_required($galleryDbVersion)){
    $cgBeforeSinceV14ExplanationRequired = true;
}

$cgRegistrationGeneralForm = '';

if($cgBeforeSinceV14ExplanationRequired){
    $cgRegistrationGeneralForm = ' (general) ';
    echo "<div id='cgNewRegistryLogicNote'><b class='cg_color_red'>NOTE:</b> Since plugin version 14 the \"User registration form\" is general<br>and valid for all new created or copied galleries since plugin version 14.</div>";
}else{
    if(intval($galleryDbVersion)>=14){// only if higher then 14 then this explanation required!
        $cgRegistrationGeneralForm = ' (general) ';
        echo "<div id='cgNewRegistryLogicNote'><b class='cg_color_green'>NOTE:</b> \"User registration form\" is general and valid for all galleries.</div>";
    }
}

add_filter( 'wp_default_editor', 'cg_cg_set_default_editor' );

// recaptcha-lang-options.php
$langOptions = include(__DIR__.'/../../../data/recaptcha-lang-options.php');

echo '<select name="ReCaLang" id="cgReCaLangToCopy" class="cg_hide">';

echo "<option value='' >Please select language</option>";

foreach($langOptions as $langKey => $lang){

    echo "<option value='$langKey' >$lang</option>";

}

echo '</select>';

echo '<div id="cgRegFormSelect">';
echo '<p class="cg_edit_form_options_label">User registration '.$cgRegistrationGeneralForm.' form<span class="cg_hide" style="margin-top: 0;display: block;font-size:16px;"><b class="cg_color_green">NOTE:</b> Multiple columns drag and drop upload form builder like for "Upload form" will be available in future for "Registration form" also. </span></p>';

$optGroupWpFields = '';

if(intval($galleryDbVersion)>=14){
    $optGroupWpFields = '<optgroup label="WP fields">
			<option value="wpfn">WP First Name</option>
			<option value="wpln">WP Last Name</option>
		</optgroup>';
}

if(intval($galleryDbVersion)>=14){
    $optGroupWpFields = '<optgroup label="WP fields">
			<option value="wpfn">WP First Name</option>
			<option value="wpln">WP Last Name</option>
		</optgroup>';
}


$beforeSinceV14Explanation = '';
$beforeSinceV14Disabled = '';

if(intval($galleryDbVersion)<14 AND empty($cgProFalse)){
    $beforeSinceV14Disabled = 'disabled';
    $beforeSinceV14Explanation = '- available only for galleries created or copied in plugin version 14 or higher';
}


$heredoc = <<<HEREDOC
	<select name="dauswahl" id="dauswahl" class="cg_hide" >
		$optGroupWpFields
		<optgroup label="User fields">
			<option value="nf">Input</option>
			<option value="kf">Textarea</option>
			<option value="se" class="$cgProFalse">Select</option>
            <option value="ra" class="$cgProFalse">Radio</option>
			<option value="chk" class="$cgProFalse">Checkbox</option>
			<option value="cb" class="$cgProFalse">Check agreement $cgProFalseText</option>
			<option value="pi" class="$cgProFalse" $beforeSinceV14Disabled>Profile image $cgProFalseText$beforeSinceV14Explanation</option>
		</optgroup>
		<optgroup label="Admin fields">
			<option class="$cgProFalse" value="ht">HTML $cgProFalseText</option>
			<option  value="caRo">Simple Captcha - I am not a robot</option>
			<option  value="caRoRe" class="$cgProFalse">Google reCAPTCHA - I am not a robot</option>
		 </optgroup>
	</select>
	<input id="cg_create_upload_add_field" class="cg_registry_dauswahl cg_hide" type="button" name="plus" value="Add field" >
	<select id="cgPlace" style="margin-left:5px;margin-right: 5px;" class="cg_hide">
        <option  value="place-top">Place top</option>
        <option  value="place-bottom">Place bottom</option>
    </select>
    <span id="cgCollapse" class="cg_uncollapsed cg_hide" >Collapse all</span>
    	<span class="cg_save_form_button_parent" >
            <span id="cgSaveRegistryFormNavButton" class="cg_save_form_button cg_backend_button_gallery_action" >Save form</span>
	    </span>
	</div>
HEREDOC;

echo $heredoc;

if (!empty($_POST['submit'])) {

//    echo "<p id='cg_changes_saved' style='font-size:18px;'><strong>Changes saved</strong></p>";

}

echo '<div id="cg_main_options" class="cg_main_options" style="box-shadow: unset;border-radius: unset;">';

echo "<form name='create_user_form' enctype='multipart/form-data'  data-cg-submit-message='Changes saved'  class='cg_load_backend_submit' action='?page=".cg_get_version()."/index.php&option_id=$GalleryID&create_user_form=true' id='cg_create_user_form' method='post'>";
wp_nonce_field( 'cg_admin');
echo "<input type='hidden' name='option_id' value='$GalleryID'>";



echo '<div id="cg_registry_form_container_parent" class="cgCreateUploadContainer">';
echo '<div id="ausgabe1" class="cg_registry_form_container" >';
include ('registry-left-side.php');
echo '</div>';

?>
<div id="cgRightSide" >
    <?php
    include ('registry-right-side.php');
    ?>
</div>
</div>

<div id="submitUploadRegFormContainer"  >
    <input type="hidden" name="submit" value="true"/>
<input id="submitForm" class="cg_backend_button_gallery_action" type="submit" value="Save form" style="font-weight:bold;text-align:center;width:150px;float:right;margin-right:10px;margin-bottom:10px;">
</div>
<br/>



<?php


// ---------------- AUSGABE des gespeicherten Formulares  --------------------------- ENDE

echo "<br/>";
?>
</form>
</div>
