<?php

echo <<<HEREDOC
<br>
<p class="cg_view_options_rows_container_title ">
        Stripe options
</p>
HEREDOC;
echo "<div>";

echo <<<HEREDOC
 <div class='cg_view_options_row'>
                <div class='cg_view_option   cg_view_option_full_width  '  >
                    <div class='cg_view_option_title'>
                        <p>Stripe environment<br><span class="cg_font_weight_normal">Create secret and live Client IDs for sandbox and live environment documentation can be found here:<br><a href="https://www.contest-gallery.com/stripe-environment-documentation/" target="_blank">Stripe environment documentation</a><br><br></span>
                        <span class="cg_view_option_title_note">
                                <span style="font-weight:bold;">NOTE: </span> if you like to use <b>cg_gallery_ecommerce</b> shortcode in <b>test environment</b><br> then add <b>test="true"</b> to the shortcode<br>
                                <span class='cg_shortcode_parent'><span class='cg_shortcode_copy_text' style="position:relative;">Example: <b>[cg_gallery_ecommerce id="$GalleryID" test="true"]</b><span class='cg_shortcode_copy cg_shortcode_copy_mail_confirm cg_tooltip'></span></span>
                                <br>or add <b>?test=true</b> to as parameter to your page<br>
                                Example: <b>$get_site_url/ecommerce-gallery/?test=true</b>
                                </span>
                        </span>
                        </p>
                    </div>
                </div>
        </div>
HEREDOC;

echo <<<HEREDOC
 <div class='cg_view_options_row'>
    <div class='cg_border_radius_unset cg_border_top_none  cg_view_option cg_view_option_100_percent' id="StripeApiActiveOption">
        <div class='cg_view_option_title'>
            <p>Enable Stripe API for checkout</p>
        </div>
        <div class='cg_view_option_checkbox'>
            <input type="checkbox" name="StripeApiActive" id="StripeApiActive" $StripeApiActive>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
       <div class='cg_view_options_row_column_container cg_stripe_api' >
             <div class='cg_view_options_row_column' style="width:75%;">
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_bottom_none  cg_border_right_none ' id="ClientIdLiveContainer" >
                            <div class='cg_view_option_title'>
                                <p>Stripe Client ID (Publishable Key) Live</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="StripeLiveClientId" id="StripeLiveClientId" value="$StripeLiveClientId"  maxlength="1000" >
                            </div>
                        </div>
                </div>
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_right_none ' id="SecretLiveContainer" >
                            <div class='cg_view_option_title'>
                                <p>Stripe Secret Key Live</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="StripeLiveSecret" id="StripeLiveSecret" value="$StripeLiveSecret"  maxlength="1000" >
                            </div>
                        </div>
                </div>
            </div>
             <div class='cg_view_options_row_column' style="width:25%;">
                <div class='cg_view_options_row'>
                            <div class='cg_view_option cg_view_option_flex_flow_column cg_view_option_full_width cg_border_top_none cg_border_left_none  ' >
                                 <a class=" cg-action cg_test_stripe_keys " href="?page=$cg_get_version/index.php&action=post_cg_test_stripe_keys"  >
                                <button type="button">Test Live Keys</button></a>
                            </div>
                    </div>
            </div>
        </div>
       <div class='cg_view_options_row_column_container cg_stripe_api' >
           <div class='cg_view_options_row_column' style="width:75%;">
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_right_none cg_border_bottom_none' id="ClientIdSandboxContainer" >
                            <div class='cg_view_option_title'>
                                <p>Stripe Client ID (Publishable Key) Sandbox</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="StripeSandboxClientId" id="StripeSandboxClientId" value="$StripeSandboxClientId"  maxlength="1000" >
                            </div>
                        </div>
                </div>
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_bottom_none cg_border_right_none ' id="SecretSandboxContainer" >
                            <div class='cg_view_option_title'>
                                <p>Stripe Secret Key Sandbox</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="StripeSandboxSecret" id="StripeSandboxSecret" value="$StripeSandboxSecret"  maxlength="1000" >
                            </div>
                        </div>
                </div>
                 <div class='cg_view_options_row'>
				    <div class='cg_border_radius_unset  cg_border_top_none cg_view_option cg_border_bottom_none cg_view_option_100_percent cg_border_right_none' id="StripeTestActiveOption">
				        <div class='cg_view_option_title'>
				            <p>Enable Stripe Sandbox Testing<br>
				                <span class="cg_view_option_title_note"><b>NOTE:</b> Disable this option after testing is finished. Otherwise not desired test purchases might happen if somebody knows the logic and use official credit card numbers for testing from Stripe.</span>
				            </p>
				        </div>
				        <div class='cg_view_option_checkbox'>
				            <input type="checkbox" name="StripeTestActive" id="StripeTestActive" $StripeTestActive>
				        </div>
				    </div>
				</div>
            </div>
           <div class='cg_view_options_row_column' style="width:25%;">
                <div class='cg_view_options_row'>
                            <div class='cg_view_option cg_view_option_flex_flow_column cg_view_option_full_width cg_border_top_none cg_border_left_none cg_border_bottom_none ' >
                                 <a class=" cg-action cg_test_stripe_keys cg_test_env " href="?page=$cg_get_version/index.php&action=post_cg_test_ecom_keys"  >
                            <button type="button">Test Sandbox Keys</button></a>
                            </div>
                    </div>
            </div>
        </div>
HEREDOC;

echo <<<HEREDOC
<div class="cg_view_options_row ShareButtonsHiddenRow cg_stripe_api" >
        <div class="cg_view_option cg_view_option_full_width" style="margin-bottom: -15px;" >
            <div class="cg_view_option_title ">
                <p>Stripe payment methods<br><span class="cg_view_option_title_note">You can control which payment methods should be available in your Stripe dashboard:<br><a href="https://dashboard.stripe.com/test/settings/payment_methods/" target="_blank">https://dashboard.stripe.com/test/settings/payment_methods</a><br><br>
                Credit card numbers for testing can be found here:<br><a href="https://docs.stripe.com/testing" target="_blank">https://docs.stripe.com/testing</a>
                <br><br>
                  <b class="cg_color_red">NOTE:</b> some payment methods requires up to <b>24 hours</b> to appear as payment in <b>live mode</b> 
              		<br><br>
                  <b class="cg_color_red">NOTE:</b> only <b>instant</b> processing payment methods like "Credit Cards" are supported so far<br>
                  You can check which other payment methods has instant processing here:
                  <br><a href="https://stripe.com/guides/payment-methods-guide" target="_blank" >https://stripe.com/guides/payment-methods-guide</a>
                </span>
                </p>
            </div>                    
        </div>
</div>
HEREDOC;

echo "</div>";// $cgProFalse close

