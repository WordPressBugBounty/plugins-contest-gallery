<?php
if(!defined('ABSPATH')){exit;}
$is_admin = is_admin();
//$is_frontend = (!$is_admin) ? true : false;
if(empty($is_frontend)){
    $is_frontend = false;
}


$domainDefault = 'default';
$domain = 'contest-gallery';
$domainBackend = 'contest-gallery';

$wp_upload_dir = wp_upload_dir();

if(!is_dir($wp_upload_dir['basedir'].'/contest-gallery/ecommerce/json')){
    mkdir($wp_upload_dir['basedir'].'/contest-gallery/ecommerce/json',0755,true);
}

$translationsFile = $wp_upload_dir['basedir'].'/contest-gallery/ecommerce/json/translations.json';

// translations might already exits from check-language.php
if(empty($translations)){
    $translations = array();
}

if(file_exists($translationsFile)){
    $fp = fopen($translationsFile, 'r');
    $translationsFromFile =json_decode(fread($fp,filesize($translationsFile)),true);
    fclose($fp);
    if(count($translationsFromFile)){
        foreach($translationsFromFile as $translationKey => $translation) {
            $translations[$translationKey] = contest_gal1ery_convert_for_html_output($translation);
        }
    }else{
        $translations = $translationsFromFile;
    }
}

// PROFILE translations for since v14 contest gallery user group
__('Buy now');$l_BuyNow = "Buy now";$language_BuyNow = (!empty($translations[$l_BuyNow]) && $is_frontend) ? $translations[$l_BuyNow] : ((empty(trim(__($l_BuyNow,$domain)))) ? __($l_BuyNow,$domainDefault) : __($l_BuyNow,$domain)); if(empty($translations[$l_BuyNow])){$translations[$l_BuyNow]='';}

__('Add to cart');$l_AddToShoppingCart = "Add to cart";$language_AddToShoppingCart = (!empty($translations[$l_AddToShoppingCart]) && $is_frontend) ? $translations[$l_AddToShoppingCart] : ((empty(trim(__($l_AddToShoppingCart,$domain)))) ? __($l_AddToShoppingCart,$domainDefault) : __($l_AddToShoppingCart,$domain)); if(empty($translations[$l_AddToShoppingCart])){$translations[$l_AddToShoppingCart]='';}

__('Shopping cart');$l_ShoppingCart = "Shopping cart";$language_ShoppingCart = (!empty($translations[$l_ShoppingCart]) && $is_frontend) ? $translations[$l_ShoppingCart] : ((empty(trim(__($l_ShoppingCart,$domain)))) ? __($l_ShoppingCart,$domainDefault) : __($l_ShoppingCart,$domain)); if(empty($translations[$l_ShoppingCart])){$translations[$l_ShoppingCart]='';}

__('Close shopping cart');$l_CloseShoppingCart = "Close shopping cart";$language_CloseShoppingCart = (!empty($translations[$l_CloseShoppingCart]) && $is_frontend) ? $translations[$l_CloseShoppingCart] : ((empty(trim(__($l_CloseShoppingCart,$domain)))) ? __($l_CloseShoppingCart,$domainDefault) : __($l_CloseShoppingCart,$domain)); if(empty($translations[$l_CloseShoppingCart])){$translations[$l_CloseShoppingCart]='';}

__('Invoice');$l_Invoice = "Invoice";$language_Invoice = (!empty($translations[$l_Invoice]) && $is_frontend) ? $translations[$l_Invoice] : ((empty(trim(__($l_Invoice,$domain)))) ? __($l_Invoice,$domainDefault) : __($l_Invoice,$domain)); if(empty($translations[$l_Invoice])){$translations[$l_Invoice]='';}

__('Download invoice');$l_DownloadInvoice = "Download invoice";$language_DownloadInvoice = (!empty($translations[$l_DownloadInvoice]) && $is_frontend) ? $translations[$l_DownloadInvoice] : ((empty(trim(__($l_DownloadInvoice,$domain)))) ? __($l_DownloadInvoice,$domainDefault) : __($l_DownloadInvoice,$domain)); if(empty($translations[$l_DownloadInvoice])){$translations[$l_DownloadInvoice]='';}

__('Billing address different from delivery address?');$l_ShipToDifferentAddress = "Billing address different from delivery address?";$language_ShipToDifferentAddress = (!empty($translations[$l_ShipToDifferentAddress]) && $is_frontend) ? $translations[$l_ShipToDifferentAddress] : ((empty(trim(__($l_ShipToDifferentAddress,$domain)))) ? __($l_ShipToDifferentAddress,$domainDefault) : __($l_ShipToDifferentAddress,$domain)); if(empty($translations[$l_ShipToDifferentAddress])){$translations[$l_ShipToDifferentAddress]='';}

__('Country');$l_Country = "Country";$language_Country = (!empty($translations[$l_Country]) && $is_frontend) ? $translations[$l_Country] : ((empty(trim(__($l_Country,$domain)))) ? __($l_Country,$domainDefault) : __($l_Country,$domain)); if(empty($translations[$l_Country])){$translations[$l_Country]='';}

__('Price');$l_Price = "Price";$language_Price = (!empty($translations[$l_Price]) && $is_frontend) ? $translations[$l_Price] : ((empty(trim(__($l_Price,$domain)))) ? __($l_Price,$domainDefault) : __($l_Price,$domain)); if(empty($translations[$l_Price])){$translations[$l_Price]='';}

__('Quantity');$l_Quantity = "Quantity";$language_Quantity = (!empty($translations[$l_Quantity]) && $is_frontend) ? $translations[$l_Quantity] : ((empty(trim(__($l_Quantity,$domain)))) ? __($l_Quantity,$domainDefault) : __($l_Quantity,$domain)); if(empty($translations[$l_Quantity])){$translations[$l_Quantity]='';}

__('Shipping');$l_Shipping = "Shipping";$language_Shipping = (!empty($translations[$l_Shipping]) && $is_frontend) ? $translations[$l_Shipping] : ((empty(trim(__($l_Shipping,$domain)))) ? __($l_Shipping,$domainDefault) : __($l_Shipping,$domain)); if(empty($translations[$l_Shipping])){$translations[$l_Shipping]='';}

__('Alternative shipping');$l_AlternativeShipping = "Alternative shipping";$language_AlternativeShipping = (!empty($translations[$l_AlternativeShipping]) && $is_frontend) ? $translations[$l_AlternativeShipping] : ((empty(trim(__($l_AlternativeShipping,$domain)))) ? __($l_AlternativeShipping,$domainDefault) : __($l_AlternativeShipping,$domain)); if(empty($translations[$l_AlternativeShipping])){$translations[$l_AlternativeShipping]='';}

__('Alternative shipping');$l_AlternativeShipping = "Alternative shipping";$language_AlternativeShipping = (!empty($translations[$l_AlternativeShipping]) && $is_frontend) ? $translations[$l_AlternativeShipping] : ((empty(trim(__($l_AlternativeShipping,$domain)))) ? __($l_AlternativeShipping,$domainDefault) : __($l_AlternativeShipping,$domain)); if(empty($translations[$l_AlternativeShipping])){$translations[$l_AlternativeShipping]='';}

__('Alternative shipping costs');$l_AlternativeShippingCosts = "Alternative shipping costs";$language_AlternativeShippingCosts = (!empty($translations[$l_AlternativeShippingCosts]) && $is_frontend) ? $translations[$l_AlternativeShippingCosts] : ((empty(trim(__($l_AlternativeShippingCosts,$domain)))) ? __($l_AlternativeShippingCosts,$domainDefault) : __($l_AlternativeShippingCosts,$domain)); if(empty($translations[$l_AlternativeShippingCosts])){$translations[$l_AlternativeShippingCosts]='';}

__('Standard shipping costs');$l_StandardShippingCosts = "Standard shipping costs";$language_StandardShippingCosts = (!empty($translations[$l_StandardShippingCosts]) && $is_frontend) ? $translations[$l_StandardShippingCosts] : ((empty(trim(__($l_StandardShippingCosts,$domain)))) ? __($l_StandardShippingCosts,$domainDefault) : __($l_StandardShippingCosts,$domain)); if(empty($translations[$l_StandardShippingCosts])){$translations[$l_StandardShippingCosts]='';}

__('Free shipping');$l_FreeShipping = "Free shipping";$language_FreeShipping = (!empty($translations[$l_FreeShipping]) && $is_frontend) ? $translations[$l_FreeShipping] : ((empty(trim(__($l_FreeShipping,$domain)))) ? __($l_FreeShipping,$domainDefault) : __($l_FreeShipping,$domain)); if(empty($translations[$l_FreeShipping])){$translations[$l_FreeShipping]='';}

__('Download');$l_Download = "Download";$language_Download = (!empty($translations[$l_Download]) && $is_frontend) ? $translations[$l_Download] : ((empty(trim(__($l_Download,$domain)))) ? __($l_Download,$domainDefault) : __($l_Download,$domain)); if(empty($translations[$l_Download])){$translations[$l_Download]='';}

__('Please check agreement');$l_PleaseCheckAgreement= "Please check agreement";$language_PleaseCheckAgreement = (!empty($translations[$l_PleaseCheckAgreement]) && $is_frontend) ? $translations[$l_PleaseCheckAgreement] : ((empty(trim(__($l_PleaseCheckAgreement,$domain)))) ? __($l_PleaseCheckAgreement,$domainDefault) : __($l_PleaseCheckAgreement,$domain)); if(empty($translations[$l_PleaseCheckAgreement])){$translations[$l_PleaseCheckAgreement]='';}

__('Shipping country not allowed');$l_ShippingCountryNotAllowed = "Shipping country not allowed";$language_ShippingCountryNotAllowed = (!empty($translations[$l_ShippingCountryNotAllowed]) && $is_frontend) ? $translations[$l_ShippingCountryNotAllowed] : ((empty(trim(__($l_ShippingCountryNotAllowed,$domain)))) ? __($l_ShippingCountryNotAllowed,$domainDefault) : __($l_ShippingCountryNotAllowed,$domain)); if(empty($translations[$l_ShippingCountryNotAllowed])){$translations[$l_ShippingCountryNotAllowed]='';}


__('Select country');$l_SelectCountry = "Select country";$language_SelectCountry = (!empty($translations[$l_SelectCountry]) && $is_frontend) ? $translations[$l_SelectCountry] : ((empty(trim(__($l_SelectCountry,$domain)))) ? __($l_SelectCountry,$domainDefault) : __($l_SelectCountry,$domain)); if(empty($translations[$l_SelectCountry])){$translations[$l_SelectCountry]='';}

__('Shipping only possible for the following countries');$l_ShippingPossibleOnlyForAvailableCountries= "Shipping only possible for the following countries";$language_ShippingPossibleOnlyForAvailableCountries = (!empty($translations[$l_ShippingPossibleOnlyForAvailableCountries]) && $is_frontend) ? $translations[$l_ShippingPossibleOnlyForAvailableCountries] : ((empty(trim(__($l_ShippingPossibleOnlyForAvailableCountries,$domain)))) ? __($l_ShippingPossibleOnlyForAvailableCountries,$domainDefault) : __($l_ShippingPossibleOnlyForAvailableCountries,$domain)); if(empty($translations[$l_ShippingPossibleOnlyForAvailableCountries])){$translations[$l_ShippingPossibleOnlyForAvailableCountries]='';}

__('Shipping address');$l_ShippingAddress = "Shipping address";$language_ShippingAddress = (!empty($translations[$l_ShippingAddress]) && $is_frontend) ? $translations[$l_ShippingAddress] : ((empty(trim(__($l_ShippingAddress,$domain)))) ? __($l_ShippingAddress,$domainDefault) : __($l_ShippingAddress,$domain)); if(empty($translations[$l_ShippingAddress])){$translations[$l_ShippingAddress]='';}

//__('Contact information');$l_ContactInformation = "Contact information";$language_ContactInformation = (!empty($translations[$l_ContactInformation]) && $is_frontend) ? $translations[$l_ContactInformation] : ((empty(trim(__($l_ContactInformation,$domain)))) ? __($l_ContactInformation,$domainDefault) : __($l_ContactInformation,$domain)); if(empty($translations[$l_ContactInformation])){$translations[$l_ContactInformation]='';}

__('Billing address');$l_InvoiceAddress = "Billing address";$language_InvoiceAddress = (!empty($translations[$l_InvoiceAddress]) && $is_frontend) ? $translations[$l_InvoiceAddress] : ((empty(trim(__($l_InvoiceAddress,$domain)))) ? __($l_InvoiceAddress,$domainDefault) : __($l_InvoiceAddress,$domain)); if(empty($translations[$l_InvoiceAddress])){$translations[$l_InvoiceAddress]='';}

__('First Name');$l_FirstName = "First Name";$language_FirstName = (!empty($translations[$l_FirstName]) && $is_frontend) ? $translations[$l_FirstName] : ((empty(trim(__($l_FirstName,$domain)))) ? __($l_FirstName,$domainDefault) : __($l_FirstName,$domain)); if(empty($translations[$l_FirstName])){$translations[$l_FirstName]='';}

__('Last Name');$l_LastName = "Last Name";$language_LastName = (!empty($translations[$l_LastName]) && $is_frontend) ? $translations[$l_LastName] : ((empty(trim(__($l_LastName,$domain)))) ? __($l_LastName,$domainDefault) : __($l_LastName,$domain)); if(empty($translations[$l_LastName])){$translations[$l_LastName]='';}

__('Company');$l_Company = "Company";$language_Company = (!empty($translations[$l_Company]) && $is_frontend) ? $translations[$l_Company] : ((empty(trim(__($l_Company,$domain)))) ? __($l_Company,$domainDefault) : __($l_Company,$domain)); if(empty($translations[$l_Company])){$translations[$l_Company]='';}

__('Address Line 1');$l_AddressLIneOne = "Address Line 1";$language_AddressLIneOne = (!empty($translations[$l_AddressLIneOne]) && $is_frontend) ? $translations[$l_AddressLIneOne] : ((empty(trim(__($l_AddressLIneOne,$domain)))) ? __($l_AddressLIneOne,$domainDefault) : __($l_AddressLIneOne,$domain)); if(empty($translations[$l_AddressLIneOne])){$translations[$l_AddressLIneOne]='';}

__('Address Line 2');$l_AddressLIneTwo = "Address Line 2";$language_AddressLIneTwo = (!empty($translations[$l_AddressLIneTwo]) && $is_frontend) ? $translations[$l_AddressLIneTwo] : ((empty(trim(__($l_AddressLIneTwo,$domain)))) ? __($l_AddressLIneTwo,$domainDefault) : __($l_AddressLIneTwo,$domain)); if(empty($translations[$l_AddressLIneTwo])){$translations[$l_AddressLIneTwo]='';}

__('City');$l_City = "City";$language_City= (!empty($translations[$l_City]) && $is_frontend) ? $translations[$l_City] : ((empty(trim(__($l_City,$domain)))) ? __($l_City,$domainDefault) : __($l_City,$domain)); if(empty($translations[$l_City])){$translations[$l_City]='';}

__('Postal Code');$l_PostalCode = "Postal Code";$language_PostalCode = (!empty($translations[$l_PostalCode]) && $is_frontend) ? $translations[$l_PostalCode] : ((empty(trim(__($l_PostalCode,$domain)))) ? __($l_PostalCode,$domainDefault) : __($l_PostalCode,$domain)); if(empty($translations[$l_PostalCode])){$translations[$l_PostalCode]='';}

__('Select state/territory');$l_SelectState = "Select state/territory";$language_SelectState = (!empty($translations[$l_SelectState]) && $is_frontend) ? $translations[$l_SelectState] : ((empty(trim(__($l_SelectState,$domain)))) ? __($l_SelectState,$domainDefault) : __($l_SelectState,$domain)); if(empty($translations[$l_SelectState])){$translations[$l_SelectState]='';}

__('Billing address identical to delivery address');$l_SameAsBillingAddress = "Billing address identical to delivery address";$language_SameAsBillingAddress = (!empty($translations[$l_SameAsBillingAddress]) && $is_frontend) ? $translations[$l_SameAsBillingAddress] : ((empty(trim(__($l_SameAsBillingAddress,$domain)))) ? __($l_SameAsBillingAddress,$domainDefault) : __($l_SameAsBillingAddress,$domain)); if(empty($translations[$l_SameAsBillingAddress])){$translations[$l_SameAsBillingAddress]='';}

__('Date of invoice');$l_InvoiceDate = "Date of invoice";$language_InvoiceDate = (!empty($translations[$l_InvoiceDate]) && $is_frontend) ? $translations[$l_InvoiceDate] : ((empty(trim(__($l_InvoiceDate,$domain)))) ? __($l_InvoiceDate,$domainDefault) : __($l_InvoiceDate,$domain)); if(empty($translations[$l_InvoiceDate])){$translations[$l_InvoiceDate]='';}

__('Payment date');$l_PaymentDate = "Payment date";$language_PaymentDate = (!empty($translations[$l_PaymentDate]) && $is_frontend) ? $translations[$l_PaymentDate] : ((empty(trim(__($l_PaymentDate,$domain)))) ? __($l_PaymentDate,$domainDefault) : __($l_PaymentDate,$domain)); if(empty($translations[$l_PaymentDate])){$translations[$l_PaymentDate]='';}

__('Paid via');$l_PaidVia = "Paid via";$language_PaidVia = (!empty($translations[$l_PaidVia]) && $is_frontend) ? $translations[$l_PaidVia] : ((empty(trim(__($l_PaidVia,$domain)))) ? __($l_PaidVia,$domainDefault) : __($l_PaidVia,$domain)); if(empty($translations[$l_PaidVia])){$translations[$l_PaidVia]='';}

__('PayPal');$l_PayPal = "PayPal";$language_PayPal = (!empty($translations[$l_PayPal]) && $is_frontend) ? $translations[$l_PayPal] : ((empty(trim(__($l_PayPal,$domain)))) ? __($l_PayPal,$domainDefault) : __($l_PayPal,$domain)); if(empty($translations[$l_PayPal])){$translations[$l_PayPal]='';}

__('Nr.');$l_Nr = "Nr.";$language_Nr = (!empty($translations[$l_Nr]) && $is_frontend) ? $translations[$l_Nr] : ((empty(trim(__($l_Nr,$domain)))) ? __($l_Nr,$domainDefault) : __($l_Nr,$domain)); if(empty($translations[$l_Nr])){$translations[$l_Nr]='';}

__('Tax rate');$l_TaxRate = "Tax rate";$language_TaxRate = (!empty($translations[$l_TaxRate]) && $is_frontend) ? $translations[$l_TaxRate] : ((empty(trim(__($l_TaxRate,$domain)))) ? __($l_TaxRate,$domainDefault) : __($l_TaxRate,$domain)); if(empty($translations[$l_TaxRate])){$translations[$l_TaxRate]='';}

__('VAT Number');$l_VatNumber = "VAT Number";$language_VatNumber = (!empty($translations[$l_VatNumber]) && $is_frontend) ? $translations[$l_VatNumber] : ((empty(trim(__($l_VatNumber,$domain)))) ? __($l_VatNumber,$domainDefault) : __($l_VatNumber,$domain)); if(empty($translations[$l_VatNumber])){$translations[$l_VatNumber]='';}

__('optional');$l_Optional = "optional";$language_Optional = (!empty($translations[$l_Optional]) && $is_frontend) ? $translations[$l_Optional] : ((empty(trim(__($l_Optional,$domain)))) ? __($l_Optional,$domainDefault) : __($l_Optional,$domain)); if(empty($translations[$l_Optional])){$translations[$l_Optional]='';}

__('Price unit net');$l_PriceUnitNet = "Price unit net";$language_PriceUnitNet = (!empty($translations[$l_PriceUnitNet]) && $is_frontend) ? $translations[$l_PriceUnitNet] : ((empty(trim(__($l_PriceUnitNet,$domain)))) ? __($l_PriceUnitNet,$domainDefault) : __($l_PriceUnitNet,$domain)); if(empty($translations[$l_PriceUnitNet])){$translations[$l_PriceUnitNet]='';}

__('Price total net');$l_PriceTotalNet = "Price total net";$language_PriceTotalNet = (!empty($translations[$l_PriceTotalNet]) && $is_frontend) ? $translations[$l_PriceTotalNet] : ((empty(trim(__($l_PriceTotalNet,$domain)))) ? __($l_PriceTotalNet,$domainDefault) : __($l_PriceTotalNet,$domain)); if(empty($translations[$l_PriceTotalNet])){$translations[$l_PriceTotalNet]='';}

__('Total net');$l_TotalNet = "Total net";$language_TotalNet = (!empty($translations[$l_TotalNet]) && $is_frontend) ? $translations[$l_TotalNet] : ((empty(trim(__($l_TotalNet,$domain)))) ? __($l_TotalNet,$domainDefault) : __($l_TotalNet,$domain)); if(empty($translations[$l_TotalNet])){$translations[$l_TotalNet]='';}

__('Total gross');$l_TotalGross = "Total gross";$language_TotalGross = (!empty($translations[$l_TotalGross]) && $is_frontend) ? $translations[$l_TotalGross] : ((empty(trim(__($l_TotalGross,$domain)))) ? __($l_TotalGross,$domainDefault) : __($l_TotalGross,$domain)); if(empty($translations[$l_TotalGross])){$translations[$l_TotalGross]='';}

__('Total price');$l_TotalPrice = "Total price";$language_TotalPrice = (!empty($translations[$l_TotalPrice]) && $is_frontend) ? $translations[$l_TotalPrice] : ((empty(trim(__($l_TotalPrice,$domain)))) ? __($l_TotalPrice,$domainDefault) : __($l_TotalPrice,$domain)); if(empty($translations[$l_TotalPrice])){$translations[$l_TotalPrice]='';}

__('Added to shopping cart');$l_AddedToBasket = "Added to shopping cart";$language_AddedToBasket = (!empty($translations[$l_AddedToBasket]) && $is_frontend) ? $translations[$l_AddedToBasket] : ((empty(trim(__($l_AddedToBasket,$domain)))) ? __($l_AddedToBasket,$domainDefault) : __($l_AddedToBasket,$domain)); if(empty($translations[$l_AddedToBasket])){$translations[$l_AddedToBasket]='';}

__('To shopping cart');$l_GoToBasket = "To shopping cart";$language_GoToBasket = (!empty($translations[$l_GoToBasket]) && $is_frontend) ? $translations[$l_GoToBasket] : ((empty(trim(__($l_GoToBasket,$domain)))) ? __($l_GoToBasket,$domainDefault) : __($l_GoToBasket,$domain)); if(empty($translations[$l_GoToBasket])){$translations[$l_GoToBasket]='';}

__('Title');$l_Title = "Title";$language_Title = (!empty($translations[$l_Title]) && $is_frontend) ? $translations[$l_Title] : ((empty(trim(__($l_Title,$domain)))) ? __($l_Title,$domainDefault) : __($l_Title,$domain)); if(empty($translations[$l_Title])){$translations[$l_Title]='';}

__('Key');$l_Key = "Key";$language_Key = (!empty($translations[$l_Key]) && $is_frontend) ? $translations[$l_Key] : ((empty(trim(__($l_Key,$domain)))) ? __($l_Key,$domainDefault) : __($l_Key,$domain)); if(empty($translations[$l_Key])){$translations[$l_Key]='';}

__('Copy Key');$l_CopyKey = "Copy Key";$language_CopyKey = (!empty($translations[$l_CopyKey]) && $is_frontend) ? $translations[$l_CopyKey] : ((empty(trim(__($l_CopyKey,$domain)))) ? __($l_CopyKey,$domainDefault) : __($l_CopyKey,$domain)); if(empty($translations[$l_CopyKey])){$translations[$l_CopyKey]='';}

__('No products selected');$l_NoProductsSelected = "No products selected";$language_NoProductsSelected = (!empty($translations[$l_NoProductsSelected]) && $is_frontend) ? $translations[$l_NoProductsSelected] : ((empty(trim(__($l_NoProductsSelected,$domain)))) ? __($l_NoProductsSelected,$domainDefault) : __($l_NoProductsSelected,$domain)); if(empty($translations[$l_NoProductsSelected])){$translations[$l_NoProductsSelected]='';}

__('Full order details');$l_FullOrderDetails = "Full order details";$language_FullOrderDetails = (!empty($translations[$l_FullOrderDetails]) && $is_frontend) ? $translations[$l_FullOrderDetails] : ((empty(trim(__($l_FullOrderDetails,$domain)))) ? __($l_FullOrderDetails,$domainDefault) : __($l_FullOrderDetails,$domain)); if(empty($translations[$l_FullOrderDetails])){$translations[$l_FullOrderDetails]='';}

__('Order number');$l_OrderNumber = "Order number";$language_OrderNumber = (!empty($translations[$l_OrderNumber]) && $is_frontend) ? $translations[$l_OrderNumber] : ((empty(trim(__($l_OrderNumber,$domain)))) ? __($l_OrderNumber,$domainDefault) : __($l_OrderNumber,$domain)); if(empty($translations[$l_OrderNumber])){$translations[$l_OrderNumber]='';}

__('Your order was successful. You will be redirected.');$l_OrderSuccessfulYouWillBeRedirected = "Your order was successful. You will be redirected.";$language_OrderSuccessfulYouWillBeRedirected = (!empty($translations[$l_OrderSuccessfulYouWillBeRedirected]) && $is_frontend) ? $translations[$l_OrderSuccessfulYouWillBeRedirected] : ((empty(trim(__($l_OrderSuccessfulYouWillBeRedirected,$domain)))) ? __($l_OrderSuccessfulYouWillBeRedirected,$domainDefault) : __($l_OrderSuccessfulYouWillBeRedirected,$domain)); if(empty($translations[$l_OrderSuccessfulYouWillBeRedirected])){$translations[$l_OrderSuccessfulYouWillBeRedirected]='';}

__('Order failed. Please contact administrator.');$l_OrderFailedContactAdministrator = "Order failed. Please contact administrator.";$language_OrderFailedContactAdministrator = (!empty($translations[$l_OrderFailedContactAdministrator]) && $is_frontend) ? $translations[$l_OrderFailedContactAdministrator] : ((empty(trim(__($l_OrderFailedContactAdministrator,$domain)))) ? __($l_OrderFailedContactAdministrator,$domainDefault) : __($l_OrderFailedContactAdministrator,$domain)); if(empty($translations[$l_OrderFailedContactAdministrator])){$translations[$l_OrderFailedContactAdministrator]='';}

__('Checkout processing impossible. No internet connection.');$l_CheckoutImpossibleNoInternet = "Checkout processing impossible. No internet connection.";$language_CheckoutImpossibleNoInternet = (!empty($translations[$l_CheckoutImpossibleNoInternet]) && $is_frontend) ? $translations[$l_CheckoutImpossibleNoInternet] : ((empty(trim(__($l_CheckoutImpossibleNoInternet,$domain)))) ? __($l_CheckoutImpossibleNoInternet,$domainDefault) : __($l_CheckoutImpossibleNoInternet,$domain)); if(empty($translations[$l_CheckoutImpossibleNoInternet])){$translations[$l_CheckoutImpossibleNoInternet]='';}

__('Maximum uploads');$l_MaximumUploads = "Maximum uploads";$language_MaximumUploads = (!empty($translations[$l_MaximumUploads]) && $is_frontend) ? $translations[$l_MaximumUploads] : ((empty(trim(__($l_MaximumUploads,$domain)))) ? __($l_MaximumUploads,$domainDefault) : __($l_MaximumUploads,$domain)); if(empty($translations[$l_MaximumUploads])){$translations[$l_MaximumUploads]='';}

__('Uploaded');$l_Uploaded = "Uploaded";$language_Uploaded = (!empty($translations[$l_Uploaded]) && $is_frontend) ? $translations[$l_Uploaded] : ((empty(trim(__($l_Uploaded,$domain)))) ? __($l_Uploaded,$domainDefault) : __($l_Uploaded,$domain)); if(empty($translations[$l_Uploaded])){$translations[$l_Uploaded]='';}

__('All uploads used');$l_AllUploadsUsed = "All uploads used";$language_AllUploadsUsed = (!empty($translations[$l_AllUploadsUsed]) && $is_frontend) ? $translations[$l_AllUploadsUsed] : ((empty(trim(__($l_AllUploadsUsed,$domain)))) ? __($l_AllUploadsUsed,$domainDefault) : __($l_AllUploadsUsed,$domain)); if(empty($translations[$l_AllUploadsUsed])){$translations[$l_AllUploadsUsed]='';}

?>