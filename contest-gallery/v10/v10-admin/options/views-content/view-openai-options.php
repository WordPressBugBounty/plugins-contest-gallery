<?php

if(cg_get_version()=='contest-gallery'){
    $PdfPreviewBackend = '';
    $PdfPreviewFrontend = '';
}

echo <<<HEREDOC

<div class='cg_view_container'>

HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_rows_container'>
<p class="cg_view_options_rows_container_title">
    <strong>NOTE:</strong> OpenAI options are valid for all galleries.<br>
</p>
</div>
HEREDOC;

echo <<<HEREDOC
       <div class='cg_view_options_row_column_container' style="margin-bottom: 30px;">
             <div class='cg_view_options_row_column' style="width:75%;"  data-cg-go-to-target="cgOpenAiKeyRowColumn">
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width  cg_border_right_none cg_border_border_top_left_radius_8_px cg_border_border_bottom_left_radius_8_px  ' id="OpenAiKeyContainer" >
                            <div class='cg_view_option_title'>
                                <p>OpenAI API key
                                <br>
                                <span class="cg_view_option_title_note"><b>NOTE:</b> get your API key from OpenAI within minutes:<br><a href="https://platform.openai.com/api-keys" target="_blank" >...openai.com/api-keys</a></span>
                                </p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="OpenAiKey" id="OpenAiKey" value="$OpenAiKey"  maxlength="1000" >
                            </div>
                        </div>
                </div>
            </div>
            <div class='cg_view_options_row_column' style="width:25%;">
                <div class='cg_view_options_row'>
                            <div class='cg_view_option cg_view_option_flex_flow_column cg_view_option_full_width  cg_border_left_none cg_border_border_top_right_radius_8_px cg_border_border_bottom_right_radius_8_px' >
                                 <a class="cg-action" id="cgOpenAiKeyTest" href="?page=$cg_get_version/index.php&action=post_cg_check_open_ai_key"  >
                                <button type="button" style="margin: unset; margin-top: 68.5px;">Test key</button></a>
                            </div>
                    </div>
            </div>
        </div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;


