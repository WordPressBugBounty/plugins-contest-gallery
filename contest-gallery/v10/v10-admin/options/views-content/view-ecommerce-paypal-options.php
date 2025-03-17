<?php


echo <<<HEREDOC
<br>
<p class="cg_view_options_rows_container_title ">
        PayPal options
</p>
HEREDOC;

echo "<div>";

echo <<<HEREDOC
 <div class='cg_view_options_row'>
                <div class='cg_view_option   cg_view_option_full_width  '  >
                    <div class='cg_view_option_title'>
                        <p>PayPal environment<br><span class="cg_font_weight_normal">Create secret and live Client IDs for sandbox and live environment documentation can be found here:<br><a href="https://www.contest-gallery.com/paypal-environment-documentation" target="_blank">PayPal environment documentation</a><br><br></span>
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
    <div class='cg_border_radius_unset  cg_border_top_none cg_view_option cg_view_option_100_percent' id="PayPalApiActiveOption">
        <div class='cg_view_option_title'>
            <p>Enable PayPal API for checkout</p>
        </div>
        <div class='cg_view_option_checkbox'>
            <input type="checkbox" name="PayPalApiActive" id="PayPalApiActive" $PayPalApiActive>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
       <div class='cg_view_options_row_column_container cg_paypal_api' >
             <div class='cg_view_options_row_column' style="width:75%;">
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_bottom_none  cg_border_right_none ' id="ClientIdLiveContainer" >
                            <div class='cg_view_option_title'>
                                <p>PayPal Client ID (Publishable Key) Live</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="PayPalLiveClientId" id="PayPalLiveClientId" value="$PayPalLiveClientId"  maxlength="1000" >
                            </div>
                        </div>
                </div>
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_right_none ' id="SecretLiveContainer" >
                            <div class='cg_view_option_title'>
                                <p>PayPal Secret Key Live</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="PayPalLiveSecret" id="PayPalLiveSecret" value="$PayPalLiveSecret"  maxlength="1000" >
                            </div>
                        </div>
                </div>
            </div>
             <div class='cg_view_options_row_column' style="width:25%;">
                <div class='cg_view_options_row'>
                            <div class='cg_view_option cg_view_option_flex_flow_column cg_view_option_full_width cg_border_top_none cg_border_left_none  ' >
                                 <a class=" cg-action cg_test_ecom_keys " href="?page=$cg_get_version/index.php&action=post_cg_test_ecom_keys"  >
                                <button type="button">Test Live Keys</button></a>
                            </div>
                    </div>
            </div>
        </div>
       <div class='cg_view_options_row_column_container cg_paypal_api' >
           <div class='cg_view_options_row_column' style="width:75%;">
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_right_none cg_border_bottom_none' id="ClientIdSandboxContainer" >
                            <div class='cg_view_option_title'>
                                <p>PayPal Client ID (Publishable Key) Sandbox</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="PayPalSandboxClientId" id="PayPalSandboxClientId" value="$PayPalSandboxClientId"  maxlength="1000" >
                            </div>
                        </div>
                </div>
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_bottom_none cg_border_right_none ' id="SecretSandboxContainer" >
                            <div class='cg_view_option_title'>
                                <p>PayPal Secret Key Sandbox</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="PayPalSandboxSecret" id="PayPalSandboxSecret" value="$PayPalSandboxSecret"  maxlength="1000" >
                            </div>
                        </div>
                </div>
                 <div class='cg_view_options_row'>
				    <div class='cg_border_radius_unset  cg_border_top_none cg_view_option cg_border_bottom_none cg_view_option_100_percent cg_border_right_none' id="PayPalTestActiveOption">
				        <div class='cg_view_option_title'>
				            <p>Enable PayPal Sandbox Testing<br>
				                <span class="cg_view_option_title_note"><b>NOTE:</b> Disable this option after testing is finished. Otherwise not desired test purchases might happen if somebody knows the logic and use official credit card numbers for testing from PayPal.</span>
				            </p>
				        </div>
				        <div class='cg_view_option_checkbox'>
				            <input type="checkbox" name="PayPalTestActive" id="PayPalTestActive" $PayPalTestActive>
				        </div>
				    </div>
				</div>
            </div>
           <div class='cg_view_options_row_column' style="width:25%;">
                <div class='cg_view_options_row'>
                            <div class='cg_view_option cg_view_option_flex_flow_column cg_view_option_full_width cg_border_top_none cg_border_left_none cg_border_bottom_none ' >
                                 <a class=" cg-action cg_test_ecom_keys cg_test_env " href="?page=$cg_get_version/index.php&action=post_cg_test_ecom_keys"  >
                            <button type="button">Test Sandbox Keys</button></a>
                            </div>
                    </div>
            </div>
        </div>
HEREDOC;

echo <<<HEREDOC
<div class="cg_view_options_row ShareButtonsHiddenRow cg_paypal_api" >
       <input type='hidden' name='PayPalDisableFunding' value='$PayPalDisableFunding' class='PayPalDisableFundingHiddenInput' />
        <div class="cg_view_option cg_view_option_full_width cg_border_bottom_none" style="margin-bottom: -15px;" >
            <div class="cg_view_option_title">
                <p>PayPal payment methods<br><span class="cg_view_option_title_note">In general all possible types of payment methods from PayPal for a country are available.<br>But you can disable if a certain type should not be available for your customers.<br><b>PayPal itself can not be disabled as payment method.</b><br>You can check which payment methods in which country are available here:<br><a href="https://developer.paypal.com/docs/checkout/payment-methods/" target="_blank">https://developer.paypal.com/docs/checkout/payment-methods</a></span></p>
            </div>                    
        </div>
</div>
HEREDOC;

$PayPalDisableFundingExploded = explode(',',$PayPalDisableFunding);

$CardChecked = (in_array('card',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$CreditChecked = (in_array('credit',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$PaylaterChecked = (in_array('paylater',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$BancontactChecked = (in_array('bancontact',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$BlikChecked = (in_array('blik',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';

echo <<<HEREDOC
<div class="cg_view_options_row cg_paypal_api">
    <div class="cg_view_option   cg_view_option_20_percent cg_border_top_bottom_none cg_border_right_none cg_disable_funding_option ">
        <div class="cg_view_option_title">
            <p>Credit or debit cards<br><span class="cg_view_option_title_note">Credit card numbers for testing can be found here:<br><a href="https://developer.paypal.com/tools/sandbox/card-testing/" target="_blank" >...paypal...card-testing…</a></span></p>
        </div>
        <div class="cg_view_option_checkbox">
            <input type="checkbox" $CardChecked class="cg_disable_funding" value="card">
        </div>
    </div>
    <div class="cg_view_option  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none cg_disable_funding_option">
        <div class="cg_view_option_title">
            <p>PayPal Credit (US, UK)</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $CreditChecked class="cg_disable_funding" value="credit">
        </div>
    </div> 
       <div class="cg_view_option    cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none cg_disable_funding_option  ">
        <div class="cg_view_option_title">
            <p>Pay Later (US, UK), Pay in 4 (AU), 4X PayPal (France), Später Bezahlen (Germany)</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $PaylaterChecked  class="cg_disable_funding" value="paylater">
        </div>
    </div>
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none  cg_disable_funding_option ">
        <div class="cg_view_option_title">
            <p>Bancontact</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $BancontactChecked class="cg_disable_funding" value="bancontact">
        </div>
    </div>
    <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_disable_funding_option  ">
        <div class="cg_view_option_title">
            <p>BLIK</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $BlikChecked class="cg_disable_funding" value="blik">
        </div>
    </div>
</div>
HEREDOC;

$EpsChecked = (in_array('eps',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$GiropayChecked = (in_array('giropay',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$IdealChecked = (in_array('ideal',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$MercadopagoChecked = (in_array('mercadopago',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$MybankChecked = (in_array('mybank',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';

echo <<<HEREDOC
<div class="cg_view_options_row cg_paypal_api">
    <div class="cg_view_option cg-pro-false-small cg_view_option_20_percent cg_border_top_bottom_none cg_border_right_none cg_disable_funding_option ">
        <div class="cg_view_option_title">
            <p>eps</p>
        </div>
        <div class="cg_view_option_checkbox">
            <input type="checkbox" $EpsChecked class="cg_disable_funding" value="eps">
        </div>
    </div>
    <div class="cg_view_option cg-pro-false-small cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none cg_disable_funding_option">
        <div class="cg_view_option_title">
            <p>giropay</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $GiropayChecked class="cg_disable_funding" value="giropay">
        </div>
    </div> 
       <div class="cg_view_option cg-pro-false-small  cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none cg_disable_funding_option  ">
        <div class="cg_view_option_title">
            <p>iDEAL</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $IdealChecked  class="cg_disable_funding" value="ideal">
        </div>
    </div>
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_border_right_none  cg_disable_funding_option ">
        <div class="cg_view_option_title">
            <p>	Mercado Pago</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $MercadopagoChecked class="cg_disable_funding" value="mercadopago">
        </div>
    </div>
    <div class="cg_view_option cg_view_option_20_percent cg_border_top_bottom_none cg_border_left_none cg_disable_funding_option  ">
        <div class="cg_view_option_title">
            <p>MyBank</p>
        </div>
        <div class="cg_view_option_checkbox ">
            <input type="checkbox" $MybankChecked class="cg_disable_funding" value="mybank">
        </div>
    </div>
</div>
HEREDOC;

$P24Checked = (in_array('p24',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$SepaChecked = (in_array('sepa',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$SofortChecked = (in_array('sofort',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';
$VenmoChecked = (in_array('venmo',$PayPalDisableFundingExploded)!==false) ? '' : 'checked';

echo <<<HEREDOC
<div class="cg_view_options_row cg_paypal_api">
       <div class="cg_view_option cg_border_border_bottom_left_radius_8_px cg_view_option_20_percent cg_border_top_none  cg_border_right_none  cg_disable_funding_option">
        <div class="cg_view_option_title">
            <p>Przelewy24</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $P24Checked class="cg_disable_funding" value="p24">
        </div>
    </div> 
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_none cg_border_left_none  cg_border_right_none cg_disable_funding_option">
        <div class="cg_view_option_title">
            <p>SEPA-Lastschrift</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $SepaChecked class="cg_disable_funding" value="sepa">
        </div>
    </div>
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_none  cg_border_left_none  cg_border_right_none   cg_disable_funding_option">
        <div class="cg_view_option_title">
            <p>Sofort</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $SofortChecked class="cg_disable_funding" value="sofort">
        </div>
    </div>
       <div class="cg_view_option cg_view_option_20_percent cg_border_top_none  cg_border_left_none  cg_border_right_none  cg_disable_funding_option  ">
        <div class="cg_view_option_title">
            <p>Venmo</p>
        </div>
        <div class="cg_view_option_checkbox ">
        <input type="checkbox" $VenmoChecked class="cg_disable_funding" value="venmo">
        </div>
    </div>
    <div class="cg_view_option  cg_border_border_bottom_right_radius_8_px  cg_view_option_20_percent cg_border_top_none  cg_border_left_none   cg_disable_funding_option  ">
        <div class="cg_view_option_title">
            <p>&nbsp;</p>
        </div>
    </div>
</div>
HEREDOC;

echo "</div>";// $cgProFalse close

