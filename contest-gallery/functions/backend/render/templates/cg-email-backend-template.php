<?php
if(!function_exists('cg1l_email_backend_template')){
    function cg1l_email_backend_template($isForUnconfirmed = false){
        $hideCgMailFields = '';
        $cgMailBodyToSendId = 'cgMailBodyToSend';
        $cgUnconfirmed = '';
        $cgRegUrlUnconfirmedHide = 'cg_hide';
        if($isForUnconfirmed){
            $hideCgMailFields = 'cg_hide';
            $cgMailBodyToSendId = 'cgMailBodyToSendUnconfirmed';
            $cgUnconfirmed = 'cg_unconfirmed';
            $cgRegUrlUnconfirmedHide = '';
        }
        $domainErrorText = cgl1_domain_error_text();
        $replyNameText = cgl1_reply_name_same_as_form_name();
        $replyMailText = cgl1_reply_mail_same_as_form_mail();

echo <<<HEREDOC
<div id='cgMailTemplateContainer' class='$cgUnconfirmed cg_hide cg_height_auto cg_backend_action_container  cg_overflow_y_hidden' style='width: fit-content;min-width: 300px; overflow-y: auto !important; max-width: 80%;min-height:70%;'>
	<span  class='cg_message_close'></span>
	<div class='cg_sel' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
             <div class='cg_sel_title' style="margin-bottom: 20px;" >
                <span class="cg_title_text" >Send custom mail</span>  <span class="cg_entry_id" style="font-weight: normal;"></span> 
            </div>
</div>
HEREDOC;
if($isForUnconfirmed){
    global $wpdb;
    $tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";
    $selectSQL4 = $wpdb->get_row($wpdb->prepare( "SELECT * FROM $tablename_pro_options WHERE GeneralID = %d",[1]));
    $cgFromName = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQL4->RegMailAddressor);
    $cgReplyMail = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQL4->RegMailReply);
    $RegMailCC = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQL4->RegMailCC);
    $RegMailBCC = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQL4->RegMailBCC);
    $RegMailSubject = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQL4->RegMailSubject);
    $TextEmailConfirmation = contest_gal1ery_convert_for_html_output_without_nl2br($selectSQL4->TextEmailConfirmation);
    echo "<input type='hidden' id='cgFromName' value='$cgFromName'>";
    echo "<input type='hidden' id='cgReplyName' value='$cgFromName'>";
    echo "<input type='hidden' id='cgFromMail' value='$cgReplyMail'>";
    echo "<input type='hidden' id='cgReplyMail' value='$cgReplyMail'>";
    echo "<input type='hidden' id='cgCc' value='$RegMailCC'>";
    echo "<input type='hidden' id='cgBcc' value='$RegMailBCC'>";
    echo "<input type='hidden' id='cgSubject' value='$RegMailSubject'>";
    echo "<textarea class='cg_hide' id='cgMailBodyConfirmationText'>$TextEmailConfirmation</textarea>";
}else{

    $cgFromName = get_option('blogname');
    $cgFromMail = get_option('admin_email');
    $cgReplyMail = $cgFromMail;
    echo "<input type='hidden' id='cgFromName' value='$cgFromName'>";
    echo "<input type='hidden' id='cgReplyName' value='$cgFromName'>";
    echo "<input type='hidden' id='cgFromMail' value='$cgFromMail'>";
    echo "<input type='hidden' id='cgReplyMail' value='$cgReplyMail'>";
}


echo <<<HEREDOC
	        <div class='cg_row' >
                   <div class='cg_row_col' id="cgMailTemplate">
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row'>
                   <div class='cg_row_col '>
                        <label for="cgFromNameToSend">From name *</label>
                        <input type="text" id="cgFromNameToSend" value="" maxlength="256" />
                        <p class="cg_error cg_required cg_hide cg_color_red">Required</p>
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row'>
                   <div class='cg_row_col '>
                        <label for="cgFromMailToSend">From mail *</label>
                        <input type="text" id="cgFromMailToSend" value="" maxlength="256" />
                        <p class="cg_error cg_required cg_hide cg_color_red">Required</p>
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row'>
                   <div class='cg_row_col '>
                        <label for="cgReplyNameToSend">Reply name</label>
                        <input type="text" id="cgReplyNameToSend" value="" maxlength="256" />
                        <p class="cg_error">$replyMailText</p>
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row'>
                   <div class='cg_row_col '>
                        <label for="cgReplyMailToSend">Reply mail</label>
                        <input type="text" id="cgReplyMailToSend" value="" maxlength="256" />
                        <p class="cg_error">$replyNameText</p>
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row'>
                   <div class='cg_row_col '>
                        <label for="cgMailToSend">To *</label>
                        <input type="text" id="cgMailToSend" value="" maxlength="256" />
                        <p class="cg_error cg_domain_error cg_hide">$domainErrorText</p>
                        <p class="cg_error cg_required cg_hide cg_color_red">Required</p>
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row'>
                   <div class='cg_row_col '>
                        <label for="cgCcToSend">CC</label>
                        <input type="text" id="cgCcToSend" value="" maxlength="999" />
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row'>
                   <div class='cg_row_col '>
                        <label for="cgBccToSend">BCC</label>
                        <input type="text" id="cgBccToSend" value="" maxlength="999" />
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row'>
                   <div class='cg_row_col '>
                        <label for="cgSubjectToSend">Subject *</label>
                        <input type="text" id="cgSubjectToSend" value="" maxlength="999" />
                        <p class="cg_error cg_required cg_hide cg_color_red">Required</p>
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row $cgRegUrlUnconfirmedHide'>
                   <div class='cg_row_col'>
                        <label for="cgRegUrlUnconfirmed" style="text-align: left;">Page URL with [cg_users_reg] or [cg_users_pin] shortcode *<br><span style="font-size:13px;line-height: 18px;display: inline-block">(one of this shortcodes is reqired for processing<br>after clicking confirmation link and opening the page)</span></label>
                        <input type="text" id="cgRegUrlUnconfirmed" value="" maxlength="999" />
                        <p class="cg_error cg_required cg_hide cg_color_red">Required</p>
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row' id="cgMailBodyToSendContainer">
                   <div class='cg_row_col ' style="position: relative;">
                        <label for="$cgMailBodyToSendId">Body</label><div id="cgCreateTemplateFromBody">Create template from current body</div>
                        <textarea id="$cgMailBodyToSendId" style="width: 100%;" rows="5"></textarea>
                    </div>
            </div>
HEREDOC;
        echo <<<HEREDOC
	        <div class='cg_row' style="margin-left: auto;margin-bottom: 15px;" id="cgMailSendButtonContainer">
                   <div class='cg_row_col' style="align-items: end;">
                        <div class="cg_send cg_send_title" style="background-color: #f1f1f1;">Send</div>
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
	        <div class='cg_row' >
                   <div class='cg_row_col' id="cgMailTemplates">
                    </div>
            </div>
HEREDOC;
echo <<<HEREDOC
                    </div>                  
                   <div class='cg_row_col $hideCgMailFields' id="cgMailFields">
HEREDOC;
echo <<<HEREDOC
                    </div>
            </div>
HEREDOC;

echo <<<HEREDOC
 </div>
HEREDOC;
    }
}
