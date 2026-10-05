<?php
if(!defined('ABSPATH')){exit;}
echo "<div class='cg_form_div cg_recaptcha_not_a_robot_field'>";
cg_captcha_render_widget(isset($fieldGoogleRecaptcha['ReCaKey']) ? $fieldGoogleRecaptcha['ReCaKey'] : '',isset($fieldGoogleRecaptcha['ReCaLang']) ? $fieldGoogleRecaptcha['ReCaLang'] : 'en');
echo '</div>';
