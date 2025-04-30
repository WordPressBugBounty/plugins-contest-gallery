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
    <p class='cg_view_options_rows_container_title'><span class="cg_font_weight_bold">Imagick library</span> is <span class="cg_font_weight_bold">NOT required</span> to be installed on your server</p>
</div>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_row cg_margin_bottom_30'>
            <div class='cg_view_option cg_view_option_100_percent cg_border_radius_8_px $cgProFalse cg_border_border_bottom_left_radius_unset cg_border_border_bottom_right_radius_unset ' id="PdfPreviewBackendContainer">
                <div class='cg_view_option_title' >
                    <p>Create real PDF preview image when adding a PDF file in backend<br><span class="cg_view_option_title_note">First site of a PDF will be used for preview image</span></p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="PdfPreviewBackend" id="PdfPreviewBackend" $PdfPreviewBackend>
                </div>
            </div>
            <div class='cg_view_option cg_border_top_none cg_view_option_100_percent cg_border_border_bottom_left_radius_8_px  cg_border_border_bottom_right_radius_8_px $cgProFalse   ' id="PdfPreviewFrontendContainer">
                <div class='cg_view_option_title' >
                    <p>Create real PDF preview image when adding a PDF file in frontend via upload form<br><span class="cg_view_option_title_note">First site of a PDF will be used for preview image</span></p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="PdfPreviewFrontend" id="PdfPreviewBackend" $PdfPreviewFrontend>
                </div>
            </div>
        </div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;


