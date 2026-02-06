<?php

$beforeSinceV22Disabled = '';
$beforeSinceV22Explanation = '';

if(intval($galleryDbVersion)<22){
    $beforeSinceV22Disabled = 'cg_disabled';
    $beforeSinceV22Explanation =  <<<HEREDOC
        <br><br><strong><span class="cg_color_red">NOTE:</span> available only for galleries created or copied in plugin version 22 or higher</strong>
HEREDOC;
}

echo <<<HEREDOC
<div class='cg_view_options_rows_container'>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width' >
                <div class='cg_view_option_title'>
                    <p>$language_BuyNow$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_BuyNow]" maxlength="100" value="$translations[$l_BuyNow]">
                    </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width' >
                <div class='cg_view_option_title'>
                    <p>$language_AddToShoppingCart$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_AddToShoppingCart]" maxlength="100" value="$translations[$l_AddToShoppingCart]">
                    </div>
            </div>
      </div>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width' >
                <div class='cg_view_option_title'>
                    <p>$language_ShoppingCart$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_ShoppingCart]" maxlength="100" value="$translations[$l_ShoppingCart]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width' >
                <div class='cg_view_option_title'>
                    <p>$language_CloseShoppingCart$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_CloseShoppingCart]" maxlength="100" value="$translations[$l_CloseShoppingCart]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width' >
                <div class='cg_view_option_title'>
                    <p>$language_Price$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Price]" maxlength="100" value="$translations[$l_Price]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Quantity$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Quantity]" maxlength="100" value="$translations[$l_Quantity]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Shipping$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Shipping]" maxlength="100" value="$translations[$l_Shipping]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_AlternativeShipping$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_AlternativeShipping]" maxlength="100" value="$translations[$l_AlternativeShipping]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_StandardShippingCosts$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_StandardShippingCosts]" maxlength="100" value="$translations[$l_StandardShippingCosts]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_SameAsBillingAddress$cgShortcodeCopy<br><span class="cg_view_option_title_note" >Usually "Billing" will be used as word in an english language checkout instead of "Invoice"</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_SameAsBillingAddress]" maxlength="100" value="$translations[$l_SameAsBillingAddress]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_AlternativeShipping$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_AlternativeShipping]" maxlength="100" value="$translations[$l_AlternativeShipping]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_AlternativeShippingCosts$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_AlternativeShippingCosts]" maxlength="100" value="$translations[$l_AlternativeShippingCosts]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_FreeShipping$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_FreeShipping]" maxlength="100" value="$translations[$l_FreeShipping]">
                </div>
             </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Download$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Download]" maxlength="100" value="$translations[$l_Download]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Service$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Service]" maxlength="100" value="$translations[$l_Service]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row'  id="cgTranslationShippingPossible" >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_ShippingPossibleOnlyForAvailableCountries$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_ShippingPossibleOnlyForAvailableCountries]" maxlength="100" value="$translations[$l_ShippingPossibleOnlyForAvailableCountries]">
                    </div>
                </div>
      </div>
HEREDOC;

/*echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_ContactInformation$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_ContactInformation]" maxlength="100" value="$translations[$l_ContactInformation]">
                    </div>
                </div>
      </div>
HEREDOC;*/

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_ShippingAddress$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_ShippingAddress]" maxlength="100" value="$translations[$l_ShippingAddress]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_InvoiceAddress$cgShortcodeCopy<br><span class="cg_view_option_title_note" >Usually "Billing" will be used as word in an english language checkout instead of "Invoice"</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_InvoiceAddress]" maxlength="100" value="$translations[$l_InvoiceAddress]">
                    </div>
                </div>
      </div>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_FirstName$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_FirstName]" maxlength="100" value="$translations[$l_FirstName]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_LastName$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_LastName]" maxlength="100" value="$translations[$l_LastName]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Company$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Company]" maxlength="100" value="$translations[$l_Company]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_AddressLIneOne$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_AddressLIneOne]" maxlength="100" value="$translations[$l_AddressLIneOne]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_AddressLIneTwo$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_AddressLIneTwo]" maxlength="100" value="$translations[$l_AddressLIneTwo]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_City$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_City]" maxlength="100" value="$translations[$l_City]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_PostalCode$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_PostalCode]" maxlength="100" value="$translations[$l_PostalCode]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_SelectState$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_SelectState]" maxlength="100" value="$translations[$l_SelectState]">
                    </div>
                </div>
      </div>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Country$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Country]" maxlength="100" value="$translations[$l_Country]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Nr$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Nr]" maxlength="100" value="$translations[$l_Nr]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_VatNumber$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_VatNumber]" maxlength="100" value="$translations[$l_VatNumber]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Optional$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Optional]" maxlength="100" value="$translations[$l_Optional]">
                    </div>
                </div>
      </div>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Invoice$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Invoice]" maxlength="100" value="$translations[$l_Invoice]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_DownloadInvoice$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_DownloadInvoice]" maxlength="100" value="$translations[$l_DownloadInvoice]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_ShipToDifferentAddress$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_ShipToDifferentAddress]" maxlength="100" value="$translations[$l_ShipToDifferentAddress]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_PaymentDate$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_PaymentDate]" maxlength="100" value="$translations[$l_PaymentDate]">
                    </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_PaidVia$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_PaidVia]" maxlength="100" value="$translations[$l_PaidVia]">
                    </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_PayPal$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_PayPal]" maxlength="100" value="$translations[$l_PayPal]">
                    </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Stripe$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Stripe]" maxlength="100" value="$translations[$l_Stripe]">
                    </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_InvoiceDate$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_InvoiceDate]" maxlength="100" value="$translations[$l_InvoiceDate]" >
                    </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Title$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Title]" maxlength="100" value="$translations[$l_Title]">
                    </div>
                </div>
      </div>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_TaxRate$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_TaxRate]" maxlength="100" value="$translations[$l_TaxRate]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_PriceUnitNet$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_PriceUnitNet]" maxlength="100" value="$translations[$l_PriceUnitNet]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_PriceTotalNet$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_PriceTotalNet]" maxlength="100" value="$translations[$l_PriceTotalNet]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_TotalNet$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_TotalNet]" maxlength="100" value="$translations[$l_TotalNet]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_TotalGross$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_TotalGross]" maxlength="100" value="$translations[$l_TotalGross]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_TotalPrice$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_TotalPrice]" maxlength="100" value="$translations[$l_TotalPrice]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_AddedToBasket$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_AddedToBasket]" maxlength="100" value="$translations[$l_AddedToBasket]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_GoToBasket$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_GoToBasket]" maxlength="100" value="$translations[$l_GoToBasket]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_PleaseCheckAgreement$cgShortcodeCopy</p>
                </div>
            <div class='cg_view_option_input'>
                <input type="text" name="translations[ecommerce][$l_PleaseCheckAgreement]" maxlength="100" value="$translations[$l_PleaseCheckAgreement]">
                </div>
            </div>
      </div>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_SelectCountry$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_SelectCountry]" maxlength="100" value="$translations[$l_SelectCountry]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_ShippingCountryNotAllowed$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_ShippingCountryNotAllowed]" maxlength="100" value="$translations[$l_ShippingCountryNotAllowed]">
                    </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_NoProductsSelected$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_NoProductsSelected]" maxlength="100" value="$translations[$l_NoProductsSelected]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_FullOrderDetails$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_FullOrderDetails]" maxlength="100" value="$translations[$l_FullOrderDetails]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_OrderSuccessfulYouWillBeRedirected$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_OrderSuccessfulYouWillBeRedirected]" maxlength="100" value="$translations[$l_OrderSuccessfulYouWillBeRedirected]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_OrderFailedContactAdministrator$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_OrderFailedContactAdministrator]" maxlength="100" value="$translations[$l_OrderFailedContactAdministrator]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_CheckoutImpossibleNoInternet$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_CheckoutImpossibleNoInternet]" maxlength="100" value="$translations[$l_CheckoutImpossibleNoInternet]">
                </div>
            </div>
      </div>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_MaximumUploads$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_MaximumUploads]" maxlength="100" value="$translations[$l_MaximumUploads]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_Uploaded$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_Uploaded]" maxlength="100" value="$translations[$l_Uploaded]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_AllUploadsUsed$cgShortcodeCopy</p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[ecommerce][$l_AllUploadsUsed]" maxlength="100" value="$translations[$l_AllUploadsUsed]">
                </div>
            </div>
      </div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;
// take care next row has to be after HEREDOC in file end
