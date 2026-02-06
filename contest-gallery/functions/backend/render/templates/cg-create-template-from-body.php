<?php

if(!function_exists('cg1lCreateTemplateFromBody')){
    function cg1lCreateTemplateFromBody(){

        $note = '<br><span class="cg_sel_title_note">From name, from mail, reply name, reply mail, cc, bcc and subject<br>will be also saved in the template</span>';

        echo <<<HEREDOC
<div id='cgCreateTemplateFromBodyContainer' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden cg_select_field_type' style='width: fit-content;min-width: 250px; overflow-y: auto !important; max-width: 70%;'>
	<span  class='cg_message_close cg_high_overlay'></span>
	<div class='cg_sel' style='margin-bottom: 0;margin-top: 20px; box-shadow: unset;'>
             <div class='cg_sel_title cg_new cg_hide' style="margin-bottom: 20px;" >
                Create new template$note
            </div>
             <div class='cg_sel_title cg_edit_template cg_hide' style="margin-bottom: 20px;" >
                Edit template name$note
            </div>
    </div>
HEREDOC;
        echo <<<HEREDOC
    <div class='cg_row cg_row_content' style="margin-bottom: 5px;" >
            <div class='cg_row_col' style="align-items: start;">
                <label for="cgTemplateName">Template name</label>
                <input type="text" id="cgTemplateName" value="" style="width: 100%;" />
            </div>
    </div>
    <div class='cg_row cg_row_content' style="margin-left: auto;margin-bottom: 5px;">
           <div class='cg_row_col' style="align-items: end;">
                <div class="cg_send" style="background-color: #f1f1f1;">Send</div>
            </div>
    </div>
    <div class='cg_row cg_row_loader' >
            <div class='cg_row_col' >
                <div class="cg-lds-dual-ring-gallery-hide"></div>
            </div>
    </div>
    <div class='cg_row cg_row_error' >
            <div class='cg_row_col' style="align-items: start;">
                Error response:<span class="cg_row_error_message"></span> 
            </div>
    </div>
HEREDOC;

        echo <<<HEREDOC
 </div>
HEREDOC;
    }
}
