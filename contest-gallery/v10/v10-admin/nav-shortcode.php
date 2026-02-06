<?php

if(empty($galleryDbVersion)){
	global $wpdb;
	$tablename_options = $wpdb->prefix . "contest_gal1ery_options";
	$galleryDbVersion = $wpdb->get_var( "SELECT Version FROM $tablename_options WHERE id='$GalleryID'");
}


$hideIntervalForGeneralForBefore14 = '';
if(intval($galleryDbVersion)<14){
	$hideIntervalForGeneralForBefore14 = 'cg_hide';
}


echo "<div class='td_gallery_info_content_row'>";

echo "<div class='td_gallery_info_content' style='width: 71.5%;box-sizing: border-box;display:flex;flex-flow:row;align-items:center;'>";

echo "<div class='td_gallery_info_shortcode td_gallery_info_shortcode_pro' style='border: unset; padding: 0;height: 100%;width: 32%;border-right: thin solid #dedede;border-width:0.5px;' >
$cgProVersionLink
</div>";

echo "<div class='td_gallery_info_shortcode' style='flex-grow:1;border-right: unset;'>
<div class='td_gallery_info_name' style='border-bottom: none;'><div style='margin-right: 5px;'>Gallery name<br><div class='td_gallery_info_name_span'><span class='td_gallery_info_name_span_bold'>$GalleryName</span></div></div><a class='td_gallery_info_name_edit_link cg_load_backend_link' href=\"?page=".cg_get_version()."/index.php&edit_options=true&option_id=".$galeryNR."&cg_go_to=cgEditGalleryNameRow\" ><div class='td_gallery_info_name_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div></a></div>
</div>";

echo "</div>";

echo "<div class='td_gallery_info_content' style='width: 23.5%;
    border-left: thin solid #dedede;
    border-right: unset;
    box-sizing: border-box;
    border-bottom: thin solid #dedede;
    margin-left: 0.5px;'>";

echo "<div class='td_gallery_info_shortcode $cgProFalse' >
    <div>
      <div class='td_gallery_info_name_title'>Google sign in button</div>
      <div class='td_gallery_info_name_span cg_shortcode_copy cg_tooltip'>[cg_google_sign_in]</div>
        <div class='cg-info-icon-parent'>
            <div class=\"cg-info-icon\">read info
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_on   cg_tooltip cg_hide'   data-cg-shortcode='cg_google_sign_in'  ></div>
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_off   cg_tooltip cg_hide'   data-cg-shortcode='cg_google_sign_in'  ></div></div>
            <span class=\"cg-info-container cg-info-container-gallery-user\" style=\"display: none;\">
            Displays the Google Sign-In button.<br>
<b>Can be used only once per page</b>.<br><br>

Google Sign-In button <b>options have to be configured</b>.<br><br>

The <b>[cg_google_sign_in]</b> shortcode does not require an id,<br>
because it's options are general and apply to all galleries.</span>
        </div>
    </div>
    <div class='td_gallery_info_shortcode_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div>
    <div class='td_gallery_info_shortcode_conf cg_tooltip $hideIntervalForGeneralForBefore14' data-cg-shortcode='cg_google_sign_in' data-cg-title-main='Google sign in button' data-cg-title-sub='[cg_google_sign_in]'></div>
</div>";

echo "</div>";
echo "</div>";

echo "<div class='td_gallery_info_content_row'>";

echo "<div class='td_gallery_info_content'>";

echo "<div class='td_gallery_info_shortcode' style='min-height: 62px; '>";
echo "<div>
      <div class='td_gallery_info_name_title' style='font-size: 15px;'>Voting gallery</div>
      <div class='td_gallery_info_name_span cg_shortcode_copy cg_tooltip'>[cg_gallery id=\"".$galeryNR."\"]</div>
        <div class='cg-info-icon-parent'>
            <div class=\"cg-info-icon\">read info
                <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_on   cg_tooltip cg_hide'   data-cg-shortcode='cg_gallery'  ></div>
                <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_off   cg_tooltip cg_hide'   data-cg-shortcode='cg_gallery'  ></div>
            </div>
            <span class=\"cg-info-container cg-info-container-gallery-user\" style=\"display: none;\">Shows <b>all activated entries</b> in the gallery.<br>
Visitors <b>can vote</b> on entries.<br>
Place the gallery shortcode <b>as often as you like</b><br>
by assigning different <b>id’s</b>.
</span>
        </div>
    </div>
    <div class='td_gallery_info_shortcode_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div>
    <div class='td_gallery_info_shortcode_conf   cg_tooltip'  data-cg-shortcode='cg_gallery'  data-cg-title-main='Voting gallery' data-cg-title-sub='[cg_gallery id=\"".$galeryNR."\"]'></div>
</div>";
echo "<div class='td_gallery_info_shortcode'>";

echo "<div>
     <div class='td_gallery_info_name_title'>Logged in user files only</div>
    <div class='td_gallery_info_name_span cg_shortcode_copy cg_tooltip'>[cg_gallery_user id=\"".$galeryNR."\"]</div>
        <div class='cg-info-icon-parent'>
            <div class=\"cg-info-icon\">read info
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_on   cg_tooltip cg_hide '   data-cg-shortcode='cg_gallery_user'  ></div>
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_off   cg_tooltip cg_hide'   data-cg-shortcode='cg_gallery_user'  ></div></div>
            <span class=\"cg-info-container cg-info-container-gallery-user\" style=\"display: none;\">Shows only the entries uploaded by the logged-in user.<br>
Voting is <b>not available</b>.<br>
All votes are always displayed.<br>
The options <b>\"Hide until vote\"</b> and <b>\"Show only user votes\"</b> are disabled.<br>
Deleting votes is <b>not possible</b>.<br>
<b>Users can edit</b> their entry information if the fields are activated as
<b>\"Show as info in single entry view\" or \"Show as title in gallery view\".</b>
<br>in <b>\"Edit upload form\"</b> is activated.

<br><br>
Can be used multiple times on a page with different <b>id’s</b>.<br><br>
The option <b>\"Delete by frontend user deleted files from storage also\"</b><br>
can be configured in the <b>\"Upload options\"</b>.
            </span>
        </div>
    </div>
    <div class='td_gallery_info_shortcode_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div>
    <div class='td_gallery_info_shortcode_conf   cg_tooltip'  data-cg-shortcode='cg_gallery_user'  data-cg-title-main='Logged in user files only' data-cg-title-sub='[cg_gallery_user id=\"".$galeryNR."\"]'></div>";

echo "</div>";

echo "</div>";


echo "<div class='td_gallery_info_content'>";

echo "<div class='td_gallery_info_shortcode' >
    <div>
     <div class='td_gallery_info_name_title'>Upload form</div>
    <div class='td_gallery_info_name_span cg_shortcode_copy cg_tooltip'>[cg_users_upload id=\"".$galeryNR."\"]</div>
        <div class='cg-info-icon-parent'>
            <div class=\"cg-info-icon\">read info
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_on   cg_tooltip cg_hide ' data-cg-shortcode='cg_users_upload' ></div>
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_off   cg_tooltip cg_hide' data-cg-shortcode='cg_users_upload' ></div></div>
    <span class=\"cg-info-container cg-info-container-gallery-user\" style=\"display: none;\">Displays the <b>upload form</b>.<br>
Can be used multiple times on a page with different <b>id’s</b>.</div>
    </div>
    <div class='td_gallery_info_shortcode_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div>
    <div class='td_gallery_info_shortcode_conf   cg_tooltip'  data-cg-shortcode='cg_users_upload'  data-cg-title-main='Upload form' data-cg-title-sub='[cg_users_upload id=\"".$galeryNR."\"]'></div>
</div>";

echo "<div class='td_gallery_info_shortcode'>";
echo "<div>
     <div class='td_gallery_info_name_title'>Gallery without voting</div>
    <div class='td_gallery_info_name_span cg_shortcode_copy cg_tooltip'>[cg_gallery_no_voting id=\"".$galeryNR."\"]</div>
        <div class='cg-info-icon-parent'>
            <div class=\"cg-info-icon\">read info
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_on   cg_tooltip cg_hide '   data-cg-shortcode='cg_gallery_no_voting'  ></div>
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_off   cg_tooltip cg_hide'   data-cg-shortcode='cg_gallery_no_voting'  ></div></div>
            <span class=\"cg-info-container cg-info-container-gallery-user\" style=\"display: none;\">All <b>activated entries</b> are <b>visible</b>.<br>
<b>Voting, sorting by votes</b>, and <b>preselecting by votes</b> options are <b>not available</b> and not displayed.<br>
Can be used as a <b>normal gallery without voting</b>.<br>
Can be placed multiple times on a page using different <b>id’s</b>.
</span>
        </div>
    </div>
    <div class='td_gallery_info_shortcode_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div>
    <div class='td_gallery_info_shortcode_conf   cg_tooltip'  data-cg-shortcode='cg_gallery_no_voting'  data-cg-title-main='Gallery without voting' data-cg-title-sub='[cg_gallery_no_voting id=\"".$galeryNR."\"]'></div>
";
echo "</div>";


echo "</div>";

echo "<div class='td_gallery_info_content'>";


$cgGalleryIdToShowForGeneralShortcodes  = ' id="'.$galeryNR.'"';

$cg_v14_note_caret = '';
$cg_v14_note_caret_text = '';

$cgBeforeSinceV14ExplanationRequired = false;

if(cg_check_if_new_registry_logic_explanation_note_required($galleryDbVersion)){
    $cgBeforeSinceV14ExplanationRequired = true;
}

if($cgBeforeSinceV14ExplanationRequired){
	$cgGalleryIdToShowForGeneralShortcodes  = '';
    $cg_v14_note_caret = 'cg_v14_note_caret';
    $cg_v14_note_caret_text = <<<HEREDOC
    <br><br><b><span class="cg_color_red cg_v14_note_caret">NOTE:</span> For galleries created or copied in plugin version 14 or higher
                 "Registration form" and "Registration options"  are general and valid for all galleries created or copied in plugin version 14 or higher.<br><br>[cg_users_reg] does not require id</b> because it's options are general and valid for all galleries<br>
HEREDOC;
}else{// then automatically must be higher then version 14 if this is the case
    if(intval($galleryDbVersion)>=14){
	    $cgGalleryIdToShowForGeneralShortcodes  = '';
	    $cg_v14_note_caret = 'cg_v14_note_caret';
        $cg_v14_note_caret_text = <<<HEREDOC
    <br><br><b><span class="cg_color_red cg_v14_note_caret">NOTE:</span> "Registration form" and "Registration options" are general and valid for all galleries.<br><br>[cg_users_reg] or [cg_users_pin] does not require id because is general and valid for all galleries</b><br>
HEREDOC;
    }
}

echo "<div class='td_gallery_info_shortcode'>
    <div>
         <div class='td_gallery_info_name_title'><span class='td_gallery_info_name_title_span $cg_v14_note_caret'>User form</span></div>
    <div class='td_gallery_info_name_span' style='max-width: 230px;'><span class='td_gallery_info_name_span_box cg_shortcode_copy cg_tooltip' style='margin-right: 30px;'>[cg_users_reg]</span><span style='margin-right: 10px;display: none;'>or</span><span class='td_gallery_info_name_span_box cg_shortcode_copy cg_tooltip'>[cg_users_pin]</span></div>
        <div class='cg-info-icon-parent'>
            <span class=\"cg-info-icon $cg_v14_note_caret\">read info
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_on   cg_tooltip cg_hide'    data-cg-shortcode='cg_users_reg'  ></div>
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_off   cg_tooltip cg_hide'   data-cg-shortcode='cg_users_reg'  ></div></span>
<span class=\"cg-info-container cg-info-container-gallery-user\" style=\"display: none;min-width: 350px;\">
Displays the <b>user registration form</b>.<br><b>Hidden</b> by default if <b>logged in</b><br>
<br>
    Use either <code>[cg_users_reg]</code> or <code>[cg_users_pin]</code><br>
    <b>not both on the same page.</b>
<br>
<ul>
    <li style='text-align: left;'><code>[cg_users_reg]</code> – user receives an email and confirms 
        registration by clicking a link (page opens).</li>
    <li style='text-align: left;'><code>[cg_users_pin]</code> – user receives an email with a PIN and 
        confirms it on the same page (no page reload).</li>
</ul>
    Configure which fields are also shown in the PIN form under<br>
    <b>\"Edit user form\"</b>.
$cg_v14_note_caret_text</span>
        </div>
    </div>
    <div class='td_gallery_info_shortcode_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div>
    <div class='td_gallery_info_shortcode_conf   cg_tooltip $hideIntervalForGeneralForBefore14'  data-cg-shortcode='cg_users_reg'  data-cg-title-main='User registration form' data-cg-title-sub='[cg_users_reg] and [cg_users_pin]'></div>
</div>";


echo "<div class='td_gallery_info_shortcode'>";
echo "<div>
         <div class='td_gallery_info_name_title'>Gallery of selected winners</div>
    <div class='td_gallery_info_name_span cg_shortcode_copy cg_tooltip'>[cg_gallery_winner id=\"".$galeryNR."\"]</div>
        <div class='cg-info-icon-parent'>
            <div class=\"cg-info-icon\">read info
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_on   cg_tooltip cg_hide'  data-cg-shortcode='cg_gallery_winner' ></div>
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_off   cg_tooltip cg_hide'   data-cg-shortcode='cg_gallery_winner' ></div></div>
            <span class=\"cg-info-container cg-info-container-gallery-user\" style=\"display: none;\">Only <b>activated entries marked as winners</b> are displayed.<br>
Total <b>voting is visible</b> but <b>can’t be voted</b>.<br>
The options <b>\"Hide until vote\"</b> and <b>\"Show only user votes\"</b> are disabled.<br>
Deleting votes is <b>not possible</b>.<br>
The <b>\"In gallery upload form\" button</b> is not available.<br>
Can be added multiple times on a page with different <b>id’s</b>.
</span>
        </div>
    </div>
    <div class='td_gallery_info_shortcode_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div>
    <div class='td_gallery_info_shortcode_conf   cg_tooltip'  data-cg-shortcode='cg_gallery_winner' data-cg-title-main='Gallery of selected winners' data-cg-title-sub='[cg_gallery_winner id=\"".$galeryNR."\"]'></div>";
echo "</div></div>";

echo "<div class='td_gallery_info_content' style='border-right: none;'>";

$cg_v14_note_caret = '';
$cg_v14_note_caret_text = '';

if($cgBeforeSinceV14ExplanationRequired){
    $cg_v14_note_caret = 'cg_v14_note_caret';
    $cg_v14_note_caret_text = <<<HEREDOC
    <br><br><b><span class="cg_color_red cg_v14_note_caret">NOTE:</span> For galleries created or copied in plugin version 14 or higher "Login form" and "Login options" are general and valid for all galleries created or copied in plugin version 14 or higher.<br><br>[cg_users_login] does not require id because is general and valid for all galleries</b><br>
HEREDOC;
}else{// then automatically must be higher then version 14 if this is the case
    if(intval($galleryDbVersion)>=14){
        $cg_v14_note_caret_text = <<<HEREDOC
    <br><br><span class="cg_color_red cg_v14_note_caret">NOTE:</span> "Login form" and "Login options" are <b>valid for all galleries</b>.<br><br><b>[cg_users_login] does not require id</b> because it's options are general and valid for all galleries</b><br>
HEREDOC;
    }
}

echo "<div class='td_gallery_info_shortcode'  >
    <div>
        <div class='td_gallery_info_name_title'><span class='td_gallery_info_name_title_span $cg_v14_note_caret'>User login</span></div>
    <div class='td_gallery_info_name_span cg_shortcode_copy cg_tooltip'>[cg_users_login$cgGalleryIdToShowForGeneralShortcodes]</div>
        <div class='cg-info-icon-parent'>
            <span class=\"cg-info-icon $cg_v14_note_caret\">read info
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_on   cg_tooltip cg_hide'  data-cg-shortcode='cg_users_login'  ></div>
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_off   cg_tooltip cg_hide'  data-cg-shortcode='cg_users_login'   ></div></span>
<span class=\"cg-info-container cg-info-container-gallery-user\" style=\"display: none;\">Displays login form.<br><br>Login form is <b>invisible if logged in.</b><br><br><b>Can be used only once per page.</b>$cg_v14_note_caret_text</span>
        </div>
    </div>
    <div class='td_gallery_info_shortcode_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div>
    <div class='td_gallery_info_shortcode_conf   cg_tooltip $hideIntervalForGeneralForBefore14'  data-cg-shortcode='cg_users_login'  data-cg-title-main='User login' data-cg-title-sub='[cg_users_login]'></div>
</div>";

$cg_hide_before_22 = '';
if(intval($galleryDbVersion)<22){
	$cg_hide_before_22 = 'cg_hide';
}

echo "<div class='td_gallery_info_shortcode $cg_hide_before_22' style='border-bottom-right-radius: 8px;' >";

echo "<div>
         <div class='td_gallery_info_name_title'>Sell products gallery</div>
    <div class='td_gallery_info_name_span cg_shortcode_copy cg_tooltip' style='padding-left: 0;white-space: pre;'>[cg_gallery_ecommerce id=\"".$galeryNR."\"]</div>
        <div class='cg-info-icon-parent'>
            <div class=\"cg-info-icon\">read info
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_on   cg_tooltip cg_hide'  data-cg-shortcode='cg_gallery_ecommerce' ></div>
    <div class='td_gallery_info_shortcode_conf_status td_gallery_info_shortcode_conf_status_off   cg_tooltip cg_hide'   data-cg-shortcode='cg_gallery_ecommerce' ></div></div>
            <span class=\"cg-info-container cg-info-container-gallery-user\" style=\"display: none;left:5%;\">Displays only entries that are activated for selling.<br>
Every entry can be activated for selling.<br>
Use the <b>\"Sales settings\"</b> button to activate an entry for selling.<br>
The <b>\"In gallery upload form\" button</b> is not available.<br>
Voting can be enabled or disabled.<br>
Can be added multiple times on a page with different <b>id’s</b>.<br><br>
Add <b>test=\"true\"</b> to the shortcode to activate the test environment.<br>
Example: <b>[cg_gallery_ecommerce id=\"$galeryNR\" test=\"true\"]</b>
</span>
        </div>
    </div>
    <div class='td_gallery_info_shortcode_edit cg_shortcode_copy cg_shortcode_copy_gallery cg_tooltip'></div>
    <div class='td_gallery_info_shortcode_conf   cg_tooltip'  data-cg-shortcode='cg_gallery_ecommerce' data-cg-title-main='Sell products gallery' data-cg-title-sub='[cg_gallery_ecommerce id=\"".$galeryNR."\"]'></div>";
echo "</div></div>";
echo "</div>";

?>