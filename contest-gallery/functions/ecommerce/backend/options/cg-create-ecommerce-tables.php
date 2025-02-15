<?php
if(!function_exists('cg_create_ecommerce_tables')){
    function cg_create_ecommerce_tables($i,$isShowError = false, & $lastError = '', $charset_collate = ''){

        global $wpdb;

        $tablename_ecommerce_entries = $wpdb->base_prefix . "$i"."contest_gal1ery_ecommerce_entries";
        $tablename_ecommerce_options = $wpdb->base_prefix . "$i"."contest_gal1ery_ecommerce_options";
        $tablename_ecommerce_invoice_options = $wpdb->base_prefix . "$i"."contest_gal1ery_ecommerce_invoice_options";
        $tablename_ecommerce_orders = $wpdb->base_prefix . "$i"."contest_gal1ery_ecommerce_orders";
        $tablename_ecommerce_orders_items = $wpdb->base_prefix . "$i"."contest_gal1ery_ecommerce_orders_items";

        // ecommerce entries
        if($wpdb->get_var("SHOW TABLES LIKE '$tablename_ecommerce_entries'") != $tablename_ecommerce_entries){
            $sql = "CREATE TABLE $tablename_ecommerce_entries (
    		id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            pid INT(11) DEFAULT 0,
            GalleryID INT(11) DEFAULT 0,
            WpUploadFilesPosts TEXT DEFAULT '',
            WpUploadFilesPostMeta TEXT DEFAULT '',
            WpUploadFilesForSale TEXT DEFAULT '',
            Price DECIMAL(65,2) DEFAULT 0,
            HasTax TINYINT DEFAULT 0,
            TaxPercentage DECIMAL(65,2) DEFAULT 0,
            AlternativeShipping DECIMAL(65,2) DEFAULT 0,
            IsDownload TINYINT DEFAULT 0,
            IsShipping TINYINT DEFAULT 0,
            IsService TINYINT DEFAULT 0,
            IsUpload TINYINT DEFAULT 0,
            IsAlternativeShipping TINYINT DEFAULT 0,
            WatermarkSettings TEXT DEFAULT '',
            SaleTitle TEXT DEFAULT '',
            SaleDescription TEXT DEFAULT '',
            SaleType TEXT DEFAULT '',
            SaleAmountMax INT(11) DEFAULT 0,
            SaleAmountMin INT(11) DEFAULT 0,
            DownloadKeysCsvName TEXT DEFAULT '', 
            ServiceKeysCsvName TEXT DEFAULT '',
            UploadGallery INT(11) DEFAULT 0,
            MaxUploads TEXT DEFAULT '',
            AllUploadsUsedText TEXT DEFAULT ''
            ) $charset_collate";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            cg_echo_last_sql_error($isShowError,$lastError);
        }

        // ecommerce options
        if($wpdb->get_var("SHOW TABLES LIKE '$tablename_ecommerce_options'") != $tablename_ecommerce_options){
            $sql = "CREATE TABLE $tablename_ecommerce_options (
    		id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    		GeneralID TINYINT DEFAULT 0,
    		PayPalApiActive TINYINT DEFAULT 0,
    		PayPalLiveClientId TEXT DEFAULT '',
            PayPalLiveSecret TEXT DEFAULT '',
            PayPalSandboxClientId TEXT DEFAULT '',
            PayPalSandboxSecret TEXT DEFAULT '',
            PayPalDisableFunding TEXT DEFAULT '',
            StripeApiActive TINYINT DEFAULT 0,
    		StripeLiveClientId TEXT DEFAULT '',
            StripeLiveSecret TEXT DEFAULT '',
            StripeSandboxClientId TEXT DEFAULT '',
            StripeSandboxSecret TEXT DEFAULT '',
            BorderRadiusOrder TINYINT DEFAULT 0,
            FeControlsStyleOrder TEXT DEFAULT '',
            Environment TEXT DEFAULT '',
            CurrencyShort TEXT DEFAULT '',
            CurrencyPosition TEXT DEFAULT '',
            PriceDivider TEXT DEFAULT '',
            TaxPercentageDefault DECIMAL(65,2) DEFAULT 0,
            ShippingGross DECIMAL(65,2) DEFAULT 0,
            CreateInvoice TINYINT DEFAULT 0,
            SendInvoice TINYINT DEFAULT 0,
            RegUserPurchaseOnly TINYINT DEFAULT 0,
            RegUserPurchaseOnlyText TEXT DEFAULT '',
            RegUserOrderSummaryOnly TINYINT DEFAULT 0,
            RegUserOrderSummaryOnlyText TEXT DEFAULT '',
            CheckoutAgreementOne TEXT DEFAULT '',
            CheckoutAgreementTwo TEXT DEFAULT '',
            CheckoutAgreementThree TEXT DEFAULT '',
            CheckoutNoteTop TEXT DEFAULT '',
            CheckoutNoteBottom TEXT DEFAULT '',
            SendOrderConfirmationMail TINYINT DEFAULT 0,
            OrderConfirmationMailHeader TEXT DEFAULT '',
            OrderConfirmationMailReply TEXT DEFAULT '',
            OrderConfirmationMailBcc TEXT DEFAULT '',
            OrderConfirmationMailCc TEXT DEFAULT '',
            OrderConfirmationMailSubject TEXT DEFAULT '',
            OrderConfirmationMail TEXT DEFAULT '',
            OCMailOrderSummaryURL TEXT DEFAULT '',
            ForwardAfterPurchaseUrl TEXT DEFAULT '',
            AllowedCountries TEXT DEFAULT '',
            AllowedCountriesTranslations TEXT DEFAULT ''
            ) $charset_collate";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            cg_echo_last_sql_error($isShowError,$lastError);
        }

        // ecommerce invoice options
        if($wpdb->get_var("SHOW TABLES LIKE '$tablename_ecommerce_invoice_options'") != $tablename_ecommerce_invoice_options){
            $sql = "CREATE TABLE $tablename_ecommerce_invoice_options (
    		id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            GeneralID TINYINT DEFAULT 0,
            ResetCustomNumberNextInvoiceLive TINYINT DEFAULT 0,
            ResetCustomNumberNextInvoiceTest TINYINT DEFAULT 0,
            CreateAndSetInvoiceNumber TINYINT DEFAULT 0,
            UseNewInvoiceNumberLogic TINYINT DEFAULT 0,
            InvoiceNumberLogicSelectLive TEXT DEFAULT '',
            InvoiceNumberLogicOwnPrefixLive TEXT DEFAULT '',
            InvoiceNumberLogicCustomNumberLive TEXT DEFAULT '',
            InvoiceNumberLogicSelectTest TEXT DEFAULT '',
            InvoiceNumberLogicOwnPrefixTest TEXT DEFAULT '',
            InvoiceNumberLogicCustomNumberTest TEXT DEFAULT '',
            InvoiceNote TEXT DEFAULT '',
            InvoicerHeaderData TEXT DEFAULT ''
            ) $charset_collate";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            cg_echo_last_sql_error($isShowError,$lastError);
        }

        // ecommerce sales
        if($wpdb->get_var("SHOW TABLES LIKE '$tablename_ecommerce_orders'") != $tablename_ecommerce_orders){
            $sql = "CREATE TABLE $tablename_ecommerce_orders (
    		id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            GalleryID INT(11) DEFAULT 0,
            PriceTotalNetItems DECIMAL(65,2) DEFAULT 0,
            PriceTotalGrossItems DECIMAL(65,2) DEFAULT 0,
            PriceTotalNetItemsWithShipping DECIMAL(65,2) DEFAULT 0,
            PriceTotalGrossItemsWithShipping DECIMAL(65,2) DEFAULT 0,
            TaxPercentageDefault DECIMAL(65,2) DEFAULT 0,
            ShippingTotal DECIMAL(65,2) DEFAULT 0,
            ShippingNet DECIMAL(65,2) DEFAULT 0,
            ShippingGross DECIMAL(65,2) DEFAULT 0,
            ShippingTaxValue DECIMAL(65,2) DEFAULT 0,
            CurrencyShort TEXT DEFAULT '',
            CurrencyPosition TEXT DEFAULT '',
		    WpUserId INT (11) DEFAULT 0,
		    IP TEXT DEFAULT '',
		    IsTest TINYINT DEFAULT 0,
		    PaymentType TEXT DEFAULT '',
		    PaymentStatus TEXT DEFAULT '',
		    PaymentOrderType TEXT DEFAULT '',
		    IsFullPaid TINYINT DEFAULT 0,
        	VersionDb DECIMAL(65,2) DEFAULT 0,
            VersionScripts TEXT DEFAULT '',
            InvoiceNumberLogicSelect TEXT DEFAULT '',
            InvoiceNumberLogicOwnPrefix TEXT DEFAULT '',
            InvoiceNumberLogicCustomNumber TEXT DEFAULT '',
            InvoiceNumber TEXT DEFAULT '',
            InvoiceNumberChanged TEXT DEFAULT '',
		    PayPalTransactionId TEXT DEFAULT '',
		    StripePiClientSecret TEXT DEFAULT '',
		    StripePiId TEXT DEFAULT '',
		    StripePiPaymentMethodId TEXT DEFAULT '',
		    StripePiPaymentMethodConfDetailsId TEXT DEFAULT '',
            StripePiPaymentMethodName TEXT DEFAULT '',
		    LogFilePath TEXT DEFAULT '',
		    LogForDatabase TEXT DEFAULT '',
		    HasInvoice TINYINT DEFAULT 0,
		    InvoiceFilePath TEXT DEFAULT '',
		    ShippingAddressFirstName TEXT DEFAULT '',
		    ShippingAddressLastName TEXT DEFAULT '',
		    ShippingAddressCompany TEXT DEFAULT '',
		    ShippingAddressLine1 TEXT DEFAULT '',
		    ShippingAddressLine2 TEXT DEFAULT '',
		    ShippingAddressCity TEXT DEFAULT '',
		    ShippingAddressPostalCode TEXT DEFAULT '',
		    ShippingAddressStateShort TEXT DEFAULT '',
		    ShippingAddressStateTranslation TEXT DEFAULT '',
		    ShippingAddressCountryShort TEXT DEFAULT '',
		    ShippingAddressCountryTranslation TEXT DEFAULT '',
		    InvoiceAddressFirstName TEXT DEFAULT '',
		    InvoiceAddressLastName TEXT DEFAULT '',
		    InvoiceAddressCompany TEXT DEFAULT '',
		    InvoiceAddressLine1 TEXT DEFAULT '',
		    InvoiceAddressLine2 TEXT DEFAULT '',
		    InvoiceAddressCity TEXT DEFAULT '',
		    InvoiceAddressPostalCode TEXT DEFAULT '',
		    InvoiceAddressStateShort TEXT DEFAULT '',
		    InvoiceAddressStateTranslation TEXT DEFAULT '',
		    InvoiceAddressCountryShort TEXT DEFAULT '',
		    InvoiceAddressCountryTranslation TEXT DEFAULT '',
		    InvoicePartNumber TEXT DEFAULT '',
		    InvoicePartInvoicer TEXT DEFAULT '',
		    InvoicePartRecipient TEXT DEFAULT '',
		    InvoicePartMain TEXT DEFAULT '',
		    InvoicePartInfo TEXT DEFAULT '',
		    InvoicePartNote TEXT DEFAULT '',
            InvoiceEdited DateTime DEFAULT '0000-00-00 00:00:00',
		    TaxNr TEXT DEFAULT '',
		    Version TEXT DEFAULT '',
		    Tstamp INT (11) DEFAULT 0,
		    CreatedMonth INT (11) DEFAULT 0,
		    CreatedYear INT (11) DEFAULT 0,
			CreatedDateWP DateTime DEFAULT '0000-00-00 00:00:00',
		    OrderNumber TEXT DEFAULT '',
		    OrderIdHash TEXT DEFAULT '',
		    PayerEmail TEXT DEFAULT ''
            ) $charset_collate";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            cg_echo_last_sql_error($isShowError,$lastError);
        }

        // ecommerce sales subs
        if($wpdb->get_var("SHOW TABLES LIKE '$tablename_ecommerce_orders_items'") != $tablename_ecommerce_orders_items){
            $sql = "CREATE TABLE $tablename_ecommerce_orders_items (
    		id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            pid INT(11) DEFAULT 0,
            GalleryID INT(11) DEFAULT 0,
    		ParentOrder INT(11) DEFAULT 0,
            PriceUnitNet DECIMAL(65,2) DEFAULT 0,
            PriceTotalNet DECIMAL(65,2) DEFAULT 0,
            PriceUnitGross DECIMAL(65,2) DEFAULT 0,
            PriceTotalGross DECIMAL(65,2) DEFAULT 0,
            TaxValueUnit DECIMAL(65,2) DEFAULT 0,
            TaxValueTotal DECIMAL(65,2) DEFAULT 0,
            IsDownload TINYINT DEFAULT 0,
            IsUpload TINYINT DEFAULT 0,
            IsShipping TINYINT DEFAULT 0,
            IsService TINYINT DEFAULT 0,
            IsAlternativeShipping TINYINT DEFAULT 0,
            AlternativeShippingNet DECIMAL(65,2) DEFAULT 0,
            AlternativeShippingGross DECIMAL(65,2) DEFAULT 0,
            AlternateShippingTaxValue DECIMAL(65,2) DEFAULT 0,
            TaxPercentage DECIMAL(65,2) DEFAULT 0,
		    Units INT(11) DEFAULT 0,
            SaleTitle TEXT DEFAULT '',
            SaleDescription TEXT DEFAULT '',
            DownloadKey TEXT DEFAULT '',
            ServiceKey TEXT DEFAULT '',
            WpUploads TEXT DEFAULT '',
            RawData TEXT DEFAULT '',
            WpUploadFilesForSale TEXT DEFAULT '',
            Uploaded INT(11) DEFAULT 0
            ) $charset_collate";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            cg_echo_last_sql_error($isShowError,$lastError);
        }

	    cg_create_ecommerce_options($i);

    }
}