<?php

$passwordFields = cg1l_password_reset_fields();
if(is_wp_error($passwordFields)){
    echo "<div id='mainCGdivLostPasswordResetContainer' class='mainCGdivUploadFormContainer mainCGdivLostPasswordContainer'><p class='cg_input_error' role='alert'>The password form is not configured correctly. Please contact the administrator.</p></div>";
    return;
}
$passwordField = $passwordFields['password'];
$passwordFieldConfirm = $passwordFields['password-confirm'];

echo "<div id='mainCGdivLostPasswordResetContainer' class='mainCGdivUploadFormContainer mainCGdivLostPasswordContainer'>";
/*
echo "<div class='cg_form_div' id='cgLostPasswordCurrentContainer'>";
	echo "<label for='cgLostPasswordCurrent'>$language_CurrentPassword</label>";
	echo "<input type='password'  id='cgLostPasswordCurrent' name='cgLostPasswordCurrent'>";
	echo "<p id='cgLostPasswordCurrentValidationMessage' class='cg_input_error cg_hide' ></p>";
echo "</div>";*/
echo "<div class='cg_form_div' id='cgResetPassword'>";
	echo "<label for='cgLostPasswordNew'>".esc_html($language_NewPassword)."</label>";
	echo "<input type='password'  id='cgLostPasswordNew' name='cgLostPasswordNew' maxlength='".$passwordField->Max_Char."'>";
	echo "<input type='hidden'  id='cgResetPasswordWpUserID' value='".esc_attr($cgResetPasswordWpUserID)."'>";
	echo "<input type='hidden'  id='cgResetPasswordKey' value='".esc_attr($cgResetPasswordKey)."'>";
	echo "<p id='cgLostPasswordNewValidationMessage' class='cg_input_error cg_hide' ></p>";
echo "</div>";
echo "<div class='cg_form_div' id='cgLostPasswordNewRepeatContainer'>";
	echo "<label for='cgLostPasswordNewRepeat'>".esc_html($language_NewPasswordRepeat)."</label>";
	echo "<input type='password'  id='cgLostPasswordNewRepeat' name='cgLostPasswordNewRepeat' maxlength='".$passwordFieldConfirm->Max_Char."'>";
	echo "<p id='cgLostPasswordNewRepeatValidationMessage' class='cg_input_error cg_hide' ></p>";
echo "</div>";
echo "<div class='cg_form_upload_submit_div cg_form_div' >";
echo '<button type="submit" id="cgLostPasswordNewSend" >';
    echo esc_html($language_Send);
echo '</button>';
echo "<p id='cgLostPasswordPasswordMinChar' class='cg_input_error cg_hide' ></p>";
echo "<p id='cgLostPasswordPasswordMaxChar' class='cg_input_error cg_hide' ></p>";
echo "<p id='cgLostPasswordPasswordsDoNotMatch' class='cg_input_error cg_hide' ></p>";
echo "<p id='cgLostPasswordUrlIsNotValidAnymore' class='cg_input_error cg_hide' ></p>";
echo "<p id='cgLostPasswordResetError' class='cg_input_error cg_hide' role='alert' ></p>";
echo '<div class="cg_form_div_image_upload_preview_loader_container cg_hide"><div class="cg_form_div_image_upload_preview_loader cg-lds-dual-ring-gallery-hide cg-lds-dual-ring-gallery-hide-mainCGallery"></div></div>';
echo "</div>";
echo "<div><a href='' class='cgLostPasswordBackToLoginFormButton'>".esc_html($language_BackToLoginForm)."</a></div>";
echo "<input type='hidden' id='cgResetPasswordMinChar' value='".$passwordField->Min_Char."'>";
echo "<input type='hidden' id='cgResetPasswordConfirmMinChar' value='$passwordFieldConfirm->Min_Char'>";
echo "</div>";// mainCGdivUploadFormContainer close

?>
