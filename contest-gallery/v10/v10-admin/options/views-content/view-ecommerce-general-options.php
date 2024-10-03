<?php

include(__DIR__ ."/../../../../check-language-ecommerce.php");

$cg_get_version = cg_get_version();

echo <<<HEREDOC
    <div class='cg_view_options_rows_container'>
<p class="cg_view_options_rows_container_title ">
        <strong>* NOTE:</strong> Ecommerce options are general and valid for all galleries.<br>
</p>
HEREDOC;

echo "<div class='cg_view_options_row'>";
echo <<<HEREDOC
            <div class="cg_view_option cg_border_border_top_right_radius_unset  cg_border_right_none cg_view_option_flex_flow_column " id="CurrencyShortContainer">
                <div class="cg_view_option_title cg_view_option_title_full_width">
                    <p>Select currency</p>
                </div>
                <div class="cg_view_option_select">
                    <select name='CurrencyShort' id='CurrencyShort' class=''>
HEREDOC;

$currencies_array = cg_get_ecommerce_currencies_array();

foreach ($currencies_array as $arrayElement){
    echo "<option value='".$arrayElement['short']."' data-cur-symbol='".$arrayElement['symbol']."'
    ".(($arrayElement['short']==$CurrencyShort) ? 'selected' : '')." >".$arrayElement['long']."</option>";
}

$CurrencyPositionLeft='selected';
$CurrencyPositionRight='';
if($CurrencyPosition=='right'){
    $CurrencyPositionLeft='';
    $CurrencyPositionRight='selected';
}

echo <<<HEREDOC
                    </select>
                </div>
            </div>
            <div class="cg_view_option  cg_view_option_flex_flow_column cg_border_right_none" id="CurrencyPositionContainer">
                <div class="cg_view_option_title cg_view_option_title_full_width">
                    <p>Select currency position</p>
                </div>
                <div class="cg_view_option_select">
                    <select name='CurrencyPosition' id='CurrencyPosition' class=''>
                            <option value='left' $CurrencyPositionLeft>Currency Symbol left position</option>
                            <option value='right' $CurrencyPositionRight>Currency Symbol right position</option>
                    </select>
                </div>
            </div>
HEREDOC;


$PriceDividerDot='selected';
$PriceDividerComma='';
if($PriceDivider==','){
    $PriceDividerDot='';
    $PriceDividerComma='selected';
}

echo <<<HEREDOC
            <div class="cg_view_option cg_view_option_flex_flow_column cg_border_border_top_right_radius_8_px " id="PriceDividerContainer">
                <div class="cg_view_option_title cg_view_option_title_full_width">
                    <p>Price decimal separator</p>
                </div>
                <div class="cg_view_option_select">
                    <select name='PriceDivider' id='PriceDivider' class=''>
                        <option  value='.' $PriceDividerDot>.</option>
                        <option  value=','  $PriceDividerComma>,</option>
                    </select>
                </div>
            </div>
HEREDOC;

echo "</div>";

echo <<<HEREDOC
        <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_title_full_width cg_view_option_50_percent cg_border_top_none cg_border_right_none cg_border_border_bottom_left_radius_8_px  ' id="TaxContainer" >
                    <div class='cg_view_option_title'>
                        <p>Tax default percentage</p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="TaxPercentageDefault" id="TaxPercentageDefault" value="$Tax"  maxlength="20" class="cg_currency_input cg_currency_input_tax" >
                    </div>
                </div>
                <div class='cg_view_option cg_border_border_bottom_left_radius_unset cg_border_border_bottom_left_radius_8_px cg_view_option_50_percent cg_border_top_none ' id="ShippingContainer" >
                    <div class='cg_view_option_title'>
                        <p>Default shipping</p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="ShippingGross" id="Shipping" value="$Shipping"  maxlength="20" class="cg_currency_input cg_currency_input_shipping" >
                    </div>
                </div>
        </div>
HEREDOC;
echo "</div>";// $cgProFalse close

echo <<<HEREDOC
<br>
<p class="cg_view_options_rows_container_title ">
        PayPal options
</p>
HEREDOC;

echo "<div >";

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
       <div class='cg_view_options_row_column_container' >
             <div class='cg_view_options_row_column' style="width:75%;">
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_bottom_none  cg_border_right_none ' id="ClientIdLiveContainer" >
                            <div class='cg_view_option_title'>
                                <p>PayPal Client ID Live</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="PayPalLiveClientId" id="PayPalLiveClientId" value="$PayPalLiveClientId"  maxlength="1000" >
                            </div>
                        </div>
                </div>
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_right_none ' id="SecretLiveContainer" >
                            <div class='cg_view_option_title'>
                                <p>PayPal Secret Live</p>
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
       <div class='cg_view_options_row_column_container' >
           <div class='cg_view_options_row_column' style="width:75%;">
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_right_none cg_border_bottom_none' id="ClientIdSandboxContainer" >
                            <div class='cg_view_option_title'>
                                <p>PayPal Client ID Sandbox</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="PayPalSandboxClientId" id="PayPalSandboxClientId" value="$PayPalSandboxClientId"  maxlength="1000" >
                            </div>
                        </div>
                </div>
                <div class='cg_view_options_row'>
                        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_bottom_none cg_border_right_none ' id="SecretSandboxContainer" >
                            <div class='cg_view_option_title'>
                                <p>PayPal Secret Sandbox</p>
                            </div>
                            <div class='cg_view_option_input'>
                                <input type="text" name="PayPalSandboxSecret" id="PayPalSandboxSecret" value="$PayPalSandboxSecret"  maxlength="1000" >
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
<div class="cg_view_options_row ShareButtonsHiddenRow" >
       <input type='hidden' name='PayPalDisableFunding' value='$PayPalDisableFunding' class='PayPalDisableFundingHiddenInput' />
        <div class="cg_view_option cg_view_option_full_width cg_border_bottom_none" style="margin-bottom: -15px;" >
            <div class="cg_view_option_title ">
                <p>PayPal payment methods<br><span class="cg_view_option_title_note">In general all possible types of payment methods from PayPal for a country are available.<br>But you can disable if a certain type should not be available for your customers.<br>You can check which payment methods in which country are available here:<br><a href="https://developer.paypal.com/docs/checkout/payment-methods/" target="_blank">https://developer.paypal.com/docs/checkout/payment-methods</a></span></p>
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
<div class="cg_view_options_row">
    <div class="cg_view_option   cg_view_option_20_percent cg_border_top_bottom_none cg_border_right_none cg_disable_funding_option ">
        <div class="cg_view_option_title">
            <p>Credit or debit cards<br><span class="cg_view_option_title_note">Test credit cards numbers can be found here:<br><a href="https://developer.paypal.com/tools/sandbox/card-testing/" target="_blank" >...paypal...card-testing…</a></span></p>
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
<div class="cg_view_options_row">
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
<div class="cg_view_options_row">
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


echo <<<HEREDOC
<br>
<p class="cg_view_options_rows_container_title ">
        Checkout options</p>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row cg_go_to_target RegUserPurchaseOnlyContainer' data-cg-go-to-target="RegUserPurchaseOnlyContainer">
    <div class='cg_border_radius_unset   cg_view_option cg_view_option_100_percent  $cgProFalse' id="RegUserPurchaseOnlyOption">
        <div class='cg_view_option_title'>
            <p>Allow only registered and logged in users to checkout<br><span class="cg_view_option_title_note">User have to be registered and logged in to be able to purchase</span></p>
        </div>
        <div class='cg_view_option_checkbox'>
            <input type="checkbox" name="RegUserPurchaseOnly" id="RegUserPurchaseOnly" $RegUserPurchaseOnly>
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
    <div class='cg_border_radius_unset cg_border_bottom_none    cg_view_option $cgProFalse cg_view_option_full_width cg_border_top_none  RegUserPurchaseOnlyTextContainer $RegUserPurchaseOnlyTextDisabled' id="wp-RegUserPurchaseOnlyText-wrap-Container">
        <div class='cg_view_option_title'>
            <p>Show text instead in the basket for not registered and logged in users<br><span class="cg_view_option_title_note">Put some text with explanation or a link to a login site. <br>This text will appear when basket is opened and user is not logged in.</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' id='RegUserPurchaseOnlyText'  name='RegUserPurchaseOnlyText'>$RegUserPurchaseOnlyText</textarea>
        </div>
    </div>
</div>
 <div class='cg_view_options_row'>
        <div class='cg_border_bottom_none cg_view_option cg_view_option_full_width  ' id="wp-CheckoutNoteTop-wrap-Container" >
            <div class='cg_view_option_title'>
                <p>Checkout note top
                    <br><span class="cg_view_option_title_note">
                    <span style="font-weight:bold;">NOTE: </span> appears above the agreements</span>
                    </span>
                </p>
            </div>
            <div class="cg_view_option_html">
                <textarea class='cg-wp-editor-template' id='CheckoutNoteTop'  name='CheckoutNoteTop'>$CheckoutNoteTop</textarea>
            </div>
        </div>
</div>
 <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width' id="wp-CheckoutAgreementOne-wrap-Container" >
            <div class='cg_view_option_title'>
                <p>First checkout agreement
                    <br><span class="cg_view_option_title_note">
                    <span style="font-weight:bold;">NOTE: </span> if empty then no checkbox for agreement will be shown</span>
                    </span>
                </p>
            </div>
            <div class="cg_view_option_html">
                <textarea class='cg-wp-editor-template' id='CheckoutAgreementOne'  name='CheckoutAgreementOne'>$CheckoutAgreementOne</textarea>
            </div>
        </div>
</div>
 <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-CheckoutAgreementTwo-wrap-Container" >
            <div class='cg_view_option_title'>
                <p>Second checkout agreement
                    <br><span class="cg_view_option_title_note">
                    <span style="font-weight:bold;">NOTE: </span> if empty then no checkbox for agreement will be shown</span>
                    </span>
                </p>
            </div>
            <div class="cg_view_option_html">
                <textarea class='cg-wp-editor-template' id='CheckoutAgreementTwo'  name='CheckoutAgreementTwo'>$CheckoutAgreementTwo</textarea>
            </div>
        </div>
</div>
 <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-CheckoutAgreementThree-wrap-Container" >
            <div class='cg_view_option_title'>
                <p>Third checkout agreement
                    <br><span class="cg_view_option_title_note">
                    <span style="font-weight:bold;">NOTE: </span> if empty then no checkbox for agreement will be shown</span>
                    </span>
                </p>
            </div>
            <div class="cg_view_option_html">
                <textarea class='cg-wp-editor-template' id='CheckoutAgreementThree'  name='CheckoutAgreementThree'>$CheckoutAgreementThree</textarea>
            </div>
        </div>
</div>
 <div class='cg_view_options_row'>
        <div class='cg_view_option   cg_border_border_bottom_right_radius_8_px cg_border_border_bottom_left_radius_8_px    cg_view_option_full_width cg_border_top_none ' id="wp-CheckoutNoteBottom-wrap-Container" >
            <div class='cg_view_option_title'>
                <p>Checkout note bottom
                    <br><span class="cg_view_option_title_note">
                    <span style="font-weight:bold;">NOTE: </span> appears under the agreements</span>
                    </span>
                </p>
            </div>
            <div class="cg_view_option_html">
                <textarea class='cg-wp-editor-template' id='CheckoutNoteBottom'  name='CheckoutNoteBottom'>$CheckoutNoteBottom</textarea>
            </div>
        </div>
</div>
HEREDOC;





echo <<<HEREDOC
<br>
<p class="cg_view_options_rows_container_title">
        Allowed shipping countries
<span class="cg_view_options_rows_container_title_note cg_hide_override">
<br>
	<span class="cg_font_weight_bold">Note:</span> generates a string of allowed countries which is visible in checkout if a shipping product is selected<br>
	<span class="cg_font_weight_bold">Example:</span> Shipping only possible for the following countries: USA, Canada<br>
	<u>Shipping only possible for the following countries</u> can be translated  <a 
                      href="?page=$cg_get_version/index.php&edit_options=true&cg_edit_translations=true&option_id=$GalleryID&cg_go_to=cgTranslationShippingPossible" target="_blank">here...</a>
</span>
</p>
HEREDOC;

$selectCountries = '<select name="AllowedCountries[]" multiple style="min-height: 300px;">';
$countriesArray = cg_get_countries();
foreach($countriesArray as $country_code => $country){
	$selected = '';
	if(in_array($country_code,$AllowedCountries)!==false){
		$selected = 'selected';
	}
	$selectCountries .= "<option value='$country_code' $selected >$country</option>";
}
$selectCountries .= '</select>';

echo <<<HEREDOC
<div class='cg_view_options_row'>
<div id="AllowedCountriesContainer"  class='cg_view_option   cg_border_border_top_right_radius_8_px cg_border_border_top_left_radius_8_px    cg_border_border_bottom_right_radius_8_px cg_border_border_bottom_left_radius_8_px  cg_view_option_full_width  cg_view_option_flex_flow_column '>
    <div class='cg_view_option_title cg_view_option_title_full_width'>
        <p>Allowed shipping countries</p>
    </div>
    <div class='cg_view_option_select' >
        $selectCountries
    </div>
    <div class="cg_view_option_title cg_view_option_input_full_width" style="flex-flow: column;">
            <div  style='margin-top: 6px;'>Hold <b>STRG/CMD</b> to add remove single country</div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div  class='cg_view_option cg_hide cg_view_option_not_focus cg_view_option_50_percent cg_view_option_flex_flow_column'>
    <div class='cg_view_option_title cg_view_option_title_full_width'>
    	<p>Select language you use<br>on your WordPress instance</p>
    </div>
    <div class='cg_view_option_select' >
HEREDOC;

/** WordPress Translation Installation API */
require_once ABSPATH . 'wp-admin/includes/translation-install.php';

$locale = get_locale();

$languages    = get_available_languages();
$translations = wp_get_available_translations();

wp_dropdown_languages(
	array(
		'name'                        => 'WPLANG',
		'id'                          => 'WPLANG',
		'selected'                    => $locale,
		'languages'                   => $languages,
		'translations'                => $translations,
		'show_available_translations' => current_user_can( 'install_languages' ) && wp_can_install_language_pack(),
	)
);

// name="" will be processed via javascript in index-functions formPostData append
$selectCountries = '<select  id="cgAllowedCountriesToTranslation">';
$selectCountries .= "<option value=''>Please select</option>";
$countriesArray = cg_get_countries();
foreach($countriesArray as $country_code => $country){
	$selectCountries .= "<option value='$country_code'  >$country</option>";
}
$selectCountries .= '</select>';
?>
    <script>
        cgJsClassAdmin.options.vars.AllowedCountriesTranslations = <?php echo json_encode($AllowedCountriesTranslations); ?>;
    </script>
<?php
echo <<<HEREDOC
    </div>
    <div class='cg_view_option_title cg_view_option_title_full_width'>
    <p>Create translations of the countries for the selected installed language<br><span class="cg_view_option_title_note">
    <span class="cg_font_weight_bold">NOTE:</span> otherwise the country names will be displayed in English language
</span></p>
    </div>
    <div class='cg_view_option_select' >
        $selectCountries
    </div>
     <div class='cg_view_option_title cg_view_option_title_full_width'>
    	<p>Translation for the selected country</p>
    </div>
    <div class='cg_view_option_input' >
        <input type="text" name="Countries" id="cgTranslationsForCountriesInput" style="font-size: 16px;"  /> 
    </div>
</div>
HEREDOC;


echo <<<HEREDOC
<div  class=' cg_hide cg_view_option cg_view_option_50_percent  cg_view_option_flex_flow_column cg_border_right_none'>
    
</div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;

echo <<<HEREDOC
<br>
<p class="cg_view_options_rows_container_title ">
        Order summary page options</p>
HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option  cg_view_option_full_width   cg_border_border_top_right_radius_8_px cg_border_border_top_left_radius_8_px' id="ForwardAfterPurchaseUrlContainer" >
        <div class='cg_view_option_title'>
            <p>Forward to order summary URL after purchase<br><span class="cg_view_option_title_note"><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com<br><span class="cg_font_weight_bold">Place this shortcode on the order summary page:</span><br>
                 <span class='cg_shortcode_parent'><span class='cg_shortcode_copy_text cg_font_weight_bold' style="position:relative;">[cg_order_summary]<span class='cg_shortcode_copy cg_shortcode_copy_mail_confirm cg_tooltip'></span></span><br>Order overview, with downloads if purchased, and invoice download will appear</span>
            </span></p>
        </div>
        <div class='cg_view_option_input'>
            <input id="forward_url" type="text" name="ForwardAfterPurchaseUrl" maxlength="999" value="$ForwardAfterPurchaseUrl" />
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
 <div class='cg_view_options_row cg_hide'>
        <div class='cg_view_option  cg_view_option_full_width cg_border_bottom_none'  >
            <div class='cg_view_option_title'>
                <p>
                    <span class="cg_view_option_title_note">
                        <span style="font-weight:bold;">NOTE: </span> place this shortcode on the order summary page<br>
                        <span class='cg_shortcode_parent'><span class='cg_shortcode_copy_text' style="position:relative;">[cg_order_summary]<span class='cg_shortcode_copy cg_shortcode_copy_mail_confirm cg_tooltip'></span></span>
                        </span>
                        </span>
                </p>
            </div>
        </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class="cg_view_options_row">
        <div class="cg_view_option cg_view_option_100_percent cg_border_top_none" id="BorderRadiusOrderContainer">
            <div class="cg_view_option_title">
                <p style="margin-right: -30px;">Round borders form container and field inputs</p>
            </div>
            <div class="cg_view_option_checkbox cg_view_option_checked">
                <input type="checkbox" name="BorderRadiusOrder" id="BorderRadiusOrder" $BorderRadiusOrder>
            </div>
        </div>
</div>
<div class='cg_view_options_row '  >
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none' style="padding-bottom: 10px;">
            <div class='cg_view_option_title'>
                <p>Background, fields and font color style</p>
            </div>
            <div class='cg_view_option_radio_multiple'>
                <div class='cg_view_option_radio_multiple_container'>
                    <div class='cg_view_option_radio_multiple_title'>
                        Bright style
                    </div>
                    <div class='cg_view_option_radio_multiple_input'>
                        <input type="radio" name="FeControlsStyleWhiteOrder" class="FeControlsStyleWhiteOrder cg_view_option_radio_multiple_input_field" $FeControlsStyleWhiteOrder value="white"/>
                    </div>
                </div>
                <div class='cg_view_option_radio_multiple_container'>
                    <div class='cg_view_option_radio_multiple_title'>
                        Dark style
                    </div>
                    <div class='cg_view_option_radio_multiple_input'>
                        <input type="radio" name="FeControlsStyleBlackOrder" class="FeControlsStyleBlackOrder cg_view_option_radio_multiple_input_field" $FeControlsStyleBlackOrder value="black">
                    </div>
                </div>
        </div>
    </div>
</div>
<div class='cg_view_options_row cg_go_to_target RegUserOrderSummaryOnlyContainer ' data-cg-go-to-target="RegUserOrderSummaryOnlyContainer">
    <div class='$cgProFalse cg_border_radius_unset cg_view_option cg_view_option_100_percent cg_border_top_none ' id="RegUserOrderSummaryOnlyOption" >
        <div class='cg_view_option_title'>
            <p>Allow only registered users to see the order summary<br><span class="cg_view_option_title_note">User have to be registered and logged in to be able to see the order summary on the order summary page. <br>If user is not logged in then this text will apear instead on the order summary page.</span></p>
        </div>
        <div class='cg_view_option_checkbox'>
            <input type="checkbox" name="RegUserOrderSummaryOnly" id="RegUserOrderSummaryOnly" $RegUserOrderSummaryOnly>
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
    <div class='$cgProFalse cg_border_radius_unset  cg_view_option  cg_border_border_bottom_right_radius_8_px cg_border_border_bottom_left_radius_8_px cg_view_option_full_width cg_border_top_none  RegUserOrderSummaryOnlyTextContainer $RegUserOrderSummaryOnlyTextDisabled' id="wp-RegUserOrderSummaryOnlyText-wrap-Container">
        <div class='cg_view_option_title'>
            <p>Show text instead in the basket for not registered and logged in users<br><span class="cg_view_option_title_note">Put some text with explanation or a link to a login site</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' id='RegUserOrderSummaryOnlyText'  name='RegUserOrderSummaryOnlyText'>$RegUserOrderSummaryOnlyText</textarea>
        </div>
    </div>
</div>
HEREDOC;

if(strpos($mailExceptionsGeneral,'Order confirmation e-mail') !== false OR strpos($mailExceptionsGeneral,'Order confirmation e-mail') !== false){
    $mailExceptionOrderConfirmationl = "<div style=\"width:330px;margin: -8px auto 15px;\"><a href=\"?page=".cg_get_version()."/index.php&amp;corrections_and_improvements=true&amp;option_id=$GalleryID\" class='cg_load_backend_link'><input class=\"cg_backend_button cg_backend_button_back cg_backend_button_warning\" type=\"button\" value=\"There were mail exceptions for this mailing type\" style=\"width:330px;\"></a>
</div>";
}else{
    $mailExceptionOrderConfirmationl = "<div style=\"width:280px;margin: -8px auto 15px;\"><a href=\"?page=".cg_get_version()."/index.php&amp;corrections_and_improvements=true&amp;option_id=$GalleryID\" class='cg_load_backend_link'><input class=\"cg_backend_button cg_backend_button_back cg_backend_button_success\" type=\"button\" value=\"No mail exceptions for this mailing type\" style=\"width:280px;\"></a>
</div>";
}

echo <<<HEREDOC
<br>
<p class="cg_view_options_rows_container_title ">
        Order confirmation email options<br><span class="cg_view_options_rows_container_title_note"><span class="cg_color_red">NOTE:</span> relating testing - e-mail where is send to should not contain $cgYourDomainName.<br>Many servers can not send to own domain.</span></p>
$mailExceptionOrderConfirmationl
HEREDOC;

$cg_disabled_send_order_confirmation_mail = 'cg_disabled';
if($SendOrderConfirmationMail=='checked'){
    $cg_disabled_send_order_confirmation_mail = '';
}

echo <<<HEREDOC
 <div class='cg_view_options_row'>
        <div class="$cgProFalse cg_border_radius_unset cg_view_option cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px cg_view_option_100_percent " id="SendOrderConfirmationMailContainer">
            <div class="cg_view_option_title">
                <p>Send order confirmation email after purchase</p>
            </div>
            <div class="cg_view_option_checkbox">
                <input id='SendOrderConfirmationMail' type='checkbox' name='SendOrderConfirmationMail' $SendOrderConfirmationMail >
            </div>
        </div>
</div>
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_border_radius_unset $cgProFalse $cg_disabled_send_order_confirmation_mail cg_view_option_full_width cg_border_top_none cg_send_order_confirmation_mail' id="OrderConfirmationMailHeaderContainer" >
        <div class='cg_view_option_title'>
            <p>Header</p>
        </div>
        <div class='cg_view_option_input'>
            <input type="text" name="OrderConfirmationMailHeader" id="OrderConfirmationMailHeader" value="$OrderConfirmationMailHeader"  maxlength="1000" >
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_border_radius_unset $cgProFalse $cg_disabled_send_order_confirmation_mail cg_view_option_full_width cg_border_top_none cg_send_order_confirmation_mail' id="OrderConfirmationMailReplyContainer" >
        <div class='cg_view_option_title'>
            <p>Reply</p>
        </div>
        <div class='cg_view_option_input'>
            <input type="text" name="OrderConfirmationMailReply" id="OrderConfirmationMailReply" value="$OrderConfirmationMailReply"  maxlength="1000" >
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_border_radius_unset $cgProFalse $cg_disabled_send_order_confirmation_mail cg_view_option_full_width cg_border_top_none cg_send_order_confirmation_mail' id="OrderConfirmationMailCcContainer" >
        <div class='cg_view_option_title'>
            <p>Cc<br><span class="cg_view_option_title_note">Should not be the same as "Reply e-mail"<br>Sending to multiple recipients example (mail1@example.com; mail2@example.com; mail3@example.com)</span></p>
        </div>
        <div class='cg_view_option_input'>
            <input type="text" name="OrderConfirmationMailCc" id="OrderConfirmationMailCc" value="$OrderConfirmationMailCc"  maxlength="1000" >
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_border_radius_unset $cgProFalse $cg_disabled_send_order_confirmation_mail cg_view_option_full_width cg_border_top_none cg_send_order_confirmation_mail' id="OrderConfirmationMailBccContainer" >
        <div class='cg_view_option_title'>
            <p>Bcc<br><span class="cg_view_option_title_note">Should not be the same as "Reply e-mail"<br>Sending to multiple recipients example (mail1@example.com; mail2@example.com; mail3@example.com)</span></p>
        </div>
        <div class='cg_view_option_input'>
            <input type="text" name="OrderConfirmationMailBcc" id="OrderConfirmationMailBcc" value="$OrderConfirmationMailBcc"  maxlength="1000" >
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_border_radius_unset $cgProFalse $cg_disabled_send_order_confirmation_mail cg_view_option_full_width cg_border_top_none cg_send_order_confirmation_mail' id="OrderConfirmationMailSubjectContainer" >
        <div class='cg_view_option_title'>
            <p>Subject</p>
        </div>
        <div class='cg_view_option_input'>
            <input type="text" name="OrderConfirmationMailSubject" id="OrderConfirmationMailSubject" value="$OrderConfirmationMailSubject"  maxlength="1000" >
        </div>
    </div>
</div>
<div class='cg_view_options_row'>
        <div class='cg_view_option cg_border_radius_unset $cgProFalse $cg_disabled_send_order_confirmation_mail  cg_view_option_full_width cg_border_top_none cg_send_order_confirmation_mail ' id="cgOCMailOrderSummaryURLContainer" >
            <div class='cg_view_option_title'>
                <p>Order summary page URL
                    <br><span class="cg_view_option_title_note">
<span style="font-weight:bold;">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com<br>
                    <span style="font-weight:bold;">NOTE: </span>  the page where cg_order_summary shortcode is placed<br>
                <span style="font-weight:bold;">NOTE: </span>  place this shortcode on the order summary page<br>
                <span class='cg_shortcode_parent'><span class='cg_shortcode_copy cg_shortcode_copy_mail_confirm cg_tooltip'></span><span class='cg_shortcode_copy_text'>[cg_order_summary]</span></span>
                </span>
                </p>
            </div>
            <div class='cg_view_option_input'>
                <input type="text" name="OCMailOrderSummaryURL" id="OCMailOrderSummaryURL" value="$OCMailOrderSummaryURL"  maxlength="200" placeholder="Example: $get_site_url/order" >
            </div>
        </div>
</div>
 <div class='cg_view_options_row'>
        <div class='cg_view_option cg_border_border_bottom_right_radius_8_px cg_border_border_bottom_left_radius_8_px  cg_border_radius_unset $cgProFalse $cg_disabled_send_order_confirmation_mail cg_view_option_full_width cg_border_top_none cg_send_order_confirmation_mail ' id="wp-OrderConfirmationMail-wrap-Container  cg_border_border_bottom_right_radius_8_px cg_border_border_bottom_left_radius_8_px" >
            <div class='cg_view_option_title'>
                <p>Mail<br><span class="cg_view_option_title_note">Use <span style="font-weight:bold;">\$order$</span> so order summary data and "Order summary page URL
" (where downloads are also available if purchased) will be inserted in the mail</span>         
</p>
            </div>
            <div class="cg_view_option_html">
                <textarea class='cg-wp-editor-template' id='OrderConfirmationMail'  name='OrderConfirmationMail'>$OrderConfirmationMail</textarea>
            </div>
        </div>
</div>
HEREDOC;



echo "</div>";
