<?php

echo <<<HEREDOC
<div class='cg_view_options_rows_container'>
<p class="cg_view_options_rows_container_title ">
        Charge users for upload<br>
</p>
HEREDOC;

echo <<<HEREDOC
    <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_full_width' >
                        <div class='cg_view_option_title'>
                            <p>Upload Sale Title</p>
                        </div>
                        <div class='cg_view_option_input'>
                            <input type="text" name="SaleTitleEcommerceUpload" id="SaleTitleEcommerceUpload" value="$SaleTitleEcommerceUpload"  maxlength="1000" >
                        </div>
                </div>
        </div>
        <div class='cg_view_options_row'>
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-SaleDescriptionEcommerceUpload-wrap-Container">
                <div class='cg_view_option_title'>
                    <p>Upload sale description</p>
                </div>
                <div class='cg_view_option_html'>
                    <textarea class='cg-wp-editor-template' id='SaleDescriptionEcommerceUpload'  name='SaleDescriptionEcommerceUpload'>$SaleDescriptionEcommerceUpload</textarea>
                </div>
            </div>
        </div>
        <div class='cg_view_options_row'>
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-ConfirmationTextAfterPayment-wrap-Container">
                <div class='cg_view_option_title'>
                    <p>Confirmation text after payment</p>
                </div>
                <div class='cg_view_option_html'>
                    <textarea class='cg-wp-editor-template' id='ConfirmationTextAfterPayment'  name='ConfirmationTextAfterPayment'>$ConfirmationTextAfterPayment</textarea>
                </div>
            </div>
        </div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;


