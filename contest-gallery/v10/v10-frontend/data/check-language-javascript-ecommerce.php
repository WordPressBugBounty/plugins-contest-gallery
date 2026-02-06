<script data-cg-processing="true">

       if(typeof cgJsClass == 'undefined' ){ // required in JavaScript for first initialisation cgJsClass = cgJsClass || {}; would not work
           cgJsClass = {};
       }

        cgJsClass.gallery = cgJsClass.gallery || {};
       cgJsClass.gallery.language = cgJsClass.gallery.language ||  {};
       cgJsClass.gallery.language.ecommerce = cgJsClass.gallery.language.ecommerce ||  {};

       if(typeof cgJsClass.gallery.vars.isWereSetEcommerceLanguage == 'undefined' ){ // check if general vars were already set
           cgJsClass.gallery.vars.isWereSetEcommerceLanguage = true;
           cgJsClass.gallery.language.ecommerce.Price = <?php echo json_encode($language_Price); ?>;
           cgJsClass.gallery.language.ecommerce.Quantity = <?php echo json_encode($language_Quantity); ?>;
           cgJsClass.gallery.language.ecommerce.Shipping = <?php echo json_encode($language_Shipping); ?>;
           cgJsClass.gallery.language.ecommerce.AlternativeShipping = <?php echo json_encode($language_AlternativeShipping); ?>;
           cgJsClass.gallery.language.ecommerce.StandardShippingCosts = <?php echo json_encode($language_StandardShippingCosts); ?>;
           cgJsClass.gallery.language.ecommerce.AlternativeShippingCosts = <?php echo json_encode($language_AlternativeShippingCosts); ?>;
           cgJsClass.gallery.language.ecommerce.FreeShipping = <?php echo json_encode($language_FreeShipping); ?>;
           cgJsClass.gallery.language.ecommerce.Download = <?php echo json_encode($language_Download); ?>;
           cgJsClass.gallery.language.ecommerce.Service = <?php echo json_encode($language_Service); ?>;
           cgJsClass.gallery.language.ecommerce.PleaseCheckAgreement = <?php echo json_encode($language_PleaseCheckAgreement); ?>;
           cgJsClass.gallery.language.ecommerce.ShippingCountryNotAllowed = <?php echo json_encode($language_ShippingCountryNotAllowed); ?>;
           //cgJsClass.gallery.language.ecommerce.ContactInformation = <?php //echo json_encode($language_ContactInformation); ?>;
           cgJsClass.gallery.language.ecommerce.ShippingAddress = <?php echo json_encode($language_ShippingAddress); ?>;
           cgJsClass.gallery.language.ecommerce.InvoiceAddress = <?php echo json_encode($language_InvoiceAddress); ?>;
           cgJsClass.gallery.language.ecommerce.FirstName = <?php echo json_encode($language_FirstName); ?>;
           cgJsClass.gallery.language.ecommerce.LastName = <?php echo json_encode($language_LastName); ?>;
           cgJsClass.gallery.language.ecommerce.Company = <?php echo json_encode($language_Company); ?>;
           cgJsClass.gallery.language.ecommerce.AddressLineOne = <?php echo json_encode($language_AddressLIneOne); ?>;
           cgJsClass.gallery.language.ecommerce.AddressLineTwo = <?php echo json_encode($language_AddressLIneTwo); ?>;
           cgJsClass.gallery.language.ecommerce.City = <?php echo json_encode($language_City); ?>;
           cgJsClass.gallery.language.ecommerce.PostalCode = <?php echo json_encode($language_PostalCode); ?>;
           cgJsClass.gallery.language.ecommerce.Nr = <?php echo json_encode($language_Nr); ?>;
           cgJsClass.gallery.language.ecommerce.VatNumber = <?php echo json_encode($language_VatNumber); ?>;
           cgJsClass.gallery.language.ecommerce.Optional = <?php echo json_encode($language_Optional); ?>;
           cgJsClass.gallery.language.ecommerce.Invoice = <?php echo json_encode($language_Invoice); ?>;
           cgJsClass.gallery.language.ecommerce.Title = <?php echo json_encode($language_Title); ?>;
           cgJsClass.gallery.language.ecommerce.Tax = <?php echo json_encode($language_TaxRate); ?>;
           cgJsClass.gallery.language.ecommerce.PriceUnitNet = <?php echo json_encode($language_PriceUnitNet); ?>;
           cgJsClass.gallery.language.ecommerce.PriceTotalNet = <?php echo json_encode($language_PriceTotalNet); ?>;
           cgJsClass.gallery.language.ecommerce.TotalNet = <?php echo json_encode($language_TotalNet); ?>;
           cgJsClass.gallery.language.ecommerce.TotalGross = <?php echo json_encode($language_TotalGross); ?>;
           cgJsClass.gallery.language.ecommerce.TotalPrice = <?php echo json_encode($language_TotalPrice); ?>;
           cgJsClass.gallery.language.ecommerce.Key = <?php echo json_encode($language_Key); ?>;
           cgJsClass.gallery.language.ecommerce.CopyKey = <?php echo json_encode($language_CopyKey); ?>;
           cgJsClass.gallery.language.ecommerce.DownloadInvoice = <?php echo json_encode($language_DownloadInvoice); ?>;
           cgJsClass.gallery.language.ecommerce.NoProductsSelected = <?php echo json_encode($language_NoProductsSelected); ?>;
           cgJsClass.gallery.language.ecommerce.ShipToDifferentAddress = <?php echo json_encode($language_ShipToDifferentAddress); ?>;
           cgJsClass.gallery.language.ecommerce.Country = <?php echo json_encode($language_Country); ?>;
           cgJsClass.gallery.language.ecommerce.OrderSuccessfulYouWillBeRedirected = <?php echo json_encode($language_OrderSuccessfulYouWillBeRedirected); ?>;
           cgJsClass.gallery.language.ecommerce.OrderFailedContactAdministrator = <?php echo json_encode($language_OrderFailedContactAdministrator); ?>;
           cgJsClass.gallery.language.ecommerce.ShippingPossibleOnlyForAvailableCountries = <?php echo json_encode($language_ShippingPossibleOnlyForAvailableCountries); ?>;
           cgJsClass.gallery.language.ecommerce.SameAsBillingAddress = <?php echo json_encode($language_SameAsBillingAddress); ?>;
           cgJsClass.gallery.language.ecommerce.SelectState = <?php echo json_encode($language_SelectState); ?>;
           cgJsClass.gallery.language.ecommerce.SelectCountry = <?php echo json_encode($language_SelectCountry); ?>;
           cgJsClass.gallery.language.ecommerce.AddedToBasket = <?php echo json_encode($language_AddedToBasket); ?>;
           cgJsClass.gallery.language.ecommerce.GoToBasket = <?php echo json_encode($language_GoToBasket); ?>;
           cgJsClass.gallery.language.ecommerce.ShoppingCart = <?php echo json_encode($language_ShoppingCart); ?>;
           cgJsClass.gallery.language.ecommerce.CloseShoppingCart = <?php echo json_encode($language_CloseShoppingCart); ?>;
           cgJsClass.gallery.language.ecommerce.PaymentDate = <?php echo json_encode($language_PaymentDate); ?>;
           cgJsClass.gallery.language.ecommerce.InvoiceDate = <?php echo json_encode($language_InvoiceDate); ?>;
           cgJsClass.gallery.language.ecommerce.CheckoutImpossibleNoInternet = <?php echo json_encode($language_CheckoutImpossibleNoInternet); ?>;
           cgJsClass.gallery.language.ecommerce.OrderNumber = <?php echo json_encode($language_OrderNumber); ?>;
           cgJsClass.gallery.language.ecommerce.MaximumUploads = <?php echo json_encode($language_MaximumUploads); ?>;
           cgJsClass.gallery.language.ecommerce.Uploaded = <?php echo json_encode($language_Uploaded); ?>;
           cgJsClass.gallery.language.ecommerce.AllUploadsUsed = <?php echo json_encode($language_AllUploadsUsed); ?>;
       }

</script>