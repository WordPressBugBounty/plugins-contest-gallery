<?php
echo <<<HEREDOC
<div class='cg_view_container'>
HEREDOC;

if(strpos($mailExceptions,'Image activation e-mail frontend') !== false OR strpos($mailExceptions,'File activation e-mail frontend') !== false){
    $mailExceptionActivationMail = "<div style=\"width:330px;margin: 0 auto 18px;\"><a href=\"?page=".cg_get_version()."/index.php&amp;corrections_and_improvements=true&amp;option_id=$galeryNR\" class='cg_load_backend_link'><input class=\"cg_backend_button cg_backend_button_back cg_backend_button_warning\" type=\"button\" value=\"There were mail exceptions for this mailing type\" style=\"width:330px;\"></a>
</div>";
}else{
    $mailExceptionActivationMail = "<div style=\"width:280px;margin: 0 auto 18px;\"><a href=\"?page=".cg_get_version()."/index.php&amp;corrections_and_improvements=true&amp;option_id=$galeryNR\" class='cg_load_backend_link'><input class=\"cg_backend_button cg_backend_button_back cg_backend_button_success\" type=\"button\" value=\"No mail exceptions for this mailing type\" style=\"width:280px;\"></a>
</div>";
}

$InformUsersAdmin = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemail->Admin);
$InformUsersReply = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemail->Reply);
$InformUsersFrom = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemail->FromMail);
$InformUsersCC = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemail->CC);
$InformUsersBCC = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemail->BCC);
$InformUsersHeader = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemail->Header);
$InformUsersURL = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemail->URL);
$InformUsersContent = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQLemail->Content);


$cgMailDeliveryCard = cg_mail_delivery_card('Entry activation',array(
    'from'=>array('[name="from_user_mail_address"]',$InformUsersFrom),
    'reply'=>array('[name="reply_user_mail"]',$InformUsersReply)
),'The email address of the entry or its assigned user.');

echo <<<HEREDOC
    <div class='cg_view_options_rows_container'>
        <p class='cg_view_options_rows_container_title'>Activation email options</p>
        $cgMailDeliveryCard
        $mailExceptionActivationMail
        <div class='cg_view_options_row'>
            <div class="cg_view_option cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px cg_view_option_100_percent $cgProFalse" id="InformUsersContainer">
                <div class="cg_view_option_title">
                    <p>Send this activation e-mail when activating user entries
                        <br><span class="cg_view_option_title_note">Sends an e-mail when an entry is activated, including entries uploaded in the backend.
                        <br>Requires an e-mail address for the entry or its assigned user.
                        <br>Also applies to automatic activation after a frontend upload.</span></p>
                </div>
                <div class="cg_view_option_checkbox">
                    <input type="checkbox" name="InformUsers" id="InformUsers" value="1" $checkInform >
                </div>
            </div>
        </div>
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_file_activation_email_option $cgProFalse' id="InformUsersFromContainer" >
                    <div class='cg_view_option_title'>
                        <p>From e-mail<br><span class="cg_view_option_title_note">Falls back to "Reply-To e-mail" when empty.</span></p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="from_user_mail_address" id="InformUsersFrom" value="$InformUsersFrom" maxlength="200" >
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_file_activation_email_option $cgProFalse' id="InformUsersAdminContainer" >
                    <div class='cg_view_option_title'>
                        <p>Header<br><span class="cg_view_option_title_note">Like your company name or something like that, not an e-mail</span></p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="from_user_mail" id="InformUsersAdmin" value="$InformUsersAdmin"  maxlength="200" >
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_file_activation_email_option $cgProFalse' id="InformUsersReplyContainer" >
                    <div class='cg_view_option_title'>
                        <p>Reply-To e-mail<span class="cg_view_option_title_note"><br>Falls back to "From e-mail" when empty.</span></p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="reply_user_mail" id="InformUsersReply" value="$InformUsersReply"  maxlength="200" >
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_file_activation_email_option $cgProFalse' id="InformUsersCCContainer" >
                    <div class='cg_view_option_title'>
                        <p>CC e-mail<br><span class="cg_view_option_title_note">Should not be the same as "Reply e-mail"<br>Sending to multiple recipients example (mail1@example.com; mail2@example.com; mail3@example.com)</span>
                        </p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="cc_user_mail" id="InformUsersCC" value="$InformUsersCC"  maxlength="200">
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_file_activation_email_option $cgProFalse' id="InformUsersBCCContainer" >
                    <div class='cg_view_option_title'>
                        <p>BCC e-mail<br><span class="cg_view_option_title_note">Should not be the same as "Reply e-mail"<br>Sending to multiple recipients example (mail1@example.com; mail2@example.com; mail3@example.com)</span>
                        </p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="bcc_user_mail" id="InformUsersBCC" value="$InformUsersBCC"  maxlength="200">
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_file_activation_email_option $cgProFalse' id="InformUsersHeaderContainer" >
                    <div class='cg_view_option_title'>
                        <p>Subject</p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="header_user_mail" id="InformUsersHeader" value="$InformUsersHeader"  maxlength="200">
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row $cgHideUrlEntryFieldsForMails'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_file_activation_email_option $cgProFalse' id="InformUsersURLContainer" >
                    <div class='cg_view_option_title'>
                        <p>URL<br><span class="cg_view_option_title_note">URL of same page where a cg_gallery... shortcode of this gallery is inserted</span></p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="url_user_mail" id="InformUsersURL" value="$InformUsersURL"  placeholder="Example: $get_site_url/contest-gallery" maxlength="1000">
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_file_activation_email_option $cgProFalse'  id="wp-InformUsersContent-wrap-Container" >
                    <div class='cg_view_option_title cg_copyable'>
                        <p>Email content<br><span class="cg_view_option_title_note">Put this variable in the email content editor: <span style="font-weight:bold;user-select: all;" class="cg_tooltip cg_copy_param">\$url$</span><br>Link to users file in confirmation mail will appear when the entry is activated<br><a href="https://www.contest-gallery.com/documentation/#cgDisplayConfirmationURL" target="_blank" class="cg-documentation-link">Documentation: How to make the link clickable in e-mail</a></span></p>
                    </div>
                    <div class='cg_view_option_html'>
                        <textarea class='cg-wp-editor-template' id='InformUsersContent'  name='cgEmailImageActivating'>$InformUsersContent</textarea>
                    </div>
                </div>
        </div>
</div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;
