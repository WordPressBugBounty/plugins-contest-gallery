jQuery(document).ready(function ($) {

    $(document).on('click', '#cgChangeInvoiceButton', function (e) {

        e.preventDefault();
        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        var $cgChangeInvoiceLoader = $('#cgChangeInvoiceLoader');
        $cgChangeInvoiceLoader.removeClass('cg_hide');

        var $form = $('#cgChangeInvoiceForm');
        var form = $form.get(0);
        var formPostData = new FormData(form);

        $form.find('.cg_edit').removeClass('cg_active');
        $form.find('.cg_edit_button').removeClass('cg_hide');

        debugger

        setTimeout(function () {
            $.ajax({
                url: 'admin-ajax.php',
                method: 'post',
                data: formPostData,
                dataType: null,
                contentType: false,
                processData: false
            }).done(function (response) {
                debugger
                $cgChangeInvoiceLoader.addClass('cg_hide');

                cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Invoice changed',true);

            }).fail(function (xhr, status, error) {

                console.log('response error');
                console.log(error);

                return;

            }).always(function () {

                var test = 1;

            });

        }, 1000);

    });

    $(document).on('click','#cgChangeInvoice .cg_edit_button',function () {
        var $cg_edit = $(this).parent().find('.cg_edit');
      //  var $cg_save_button = $(this).parent().find('.cg_save_button');
        var tmp = $cg_edit.val();
        $cg_edit.addClass('cg_active').focus().val('');
        $cg_edit.val(tmp);
        $cg_edit.scrollTop($cg_edit[0].scrollHeight - $cg_edit.height());
        $(this).addClass('cg_hide');
    //    $cg_save_button.removeClass('cg_hide');
    });

    $(document).on('click','#cgWatermarkOnOption',function () {
        var $cgSellContainerForm = $(this).closest('form');
        var $element = $(this);
        setTimeout(function (){// has to be done with timeout so works correctly
            if($element.find('#cgWatermarkOnDiv').hasClass('cg_view_option_checked')){
                cgJsClassAdmin.gallery.watermarkOnChange();
                var WpUpload = $(this).closest('form').find('.cgSellPreview.active').attr('data-cg-wp-upload');
                cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on'] = true;
                cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle'] = cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgWatermarkInputTitle').val();
                cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition'] = cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgWatermarkSelectPosition').val();
                cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize'] = cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgWatermarkSelectSize').val();
                debugger
                $cgSellContainerForm.find('#cgWatermarkSettingsRow').removeClass('cg_disabled_background_color_e0e0e0');
            }else{
                cgJsClassAdmin.gallery.watermarkOnChange(true);
                $cgSellContainerForm.find('#cgWatermarkSettingsRow').addClass('cg_disabled_background_color_e0e0e0');
            }
        },100);
    });

    $(document).on('click','#cg_create_sale_orders_csv_submit',function () {

        //e.preventDefault();// no prevent default here!!!!
        var $cgSaleOrdersForm = $('#cgSaleOrdersForm');
        $cgSaleOrdersForm.removeClass('cg_load_backend_submit');
        $cgSaleOrdersForm.find('#cg_ecommerce_export_orders').removeAttr('disabled');
        $cgSaleOrdersForm.find('#cgSaleOrderSearchSubmit').click();

        setTimeout(function () {
            $cgSaleOrdersForm.find('#cg_ecommerce_export_orders').attr('disabled','disabled');
            $cgSaleOrdersForm.addClass('cg_load_backend_submit');
        },100);

    });

    $(document).on('click','#cgSaleOrderSearchReset',function () {
        $('#cgNavMenuEcommerceOrders').click();
    });

    $(document).on('click','#cgSellArrowLeft, #cgSellArrowRight',function () {

        var direction;
        if($(this).attr('id')=='cgSellArrowLeft'){
            direction = 'left';
        }

        if($(this).attr('id')=='cgSellArrowRight'){
            direction = 'right';
        }

        var $parent = $(this).parent();
        var $cgSellPreviewActive = $parent.find('.cgSellPreview.active');
        $parent.find('.cgSellPreview').addClass('cg_hide').removeClass('active');
        var order = parseInt($cgSellPreviewActive.attr('data-cg-order'));
        var newOrder;
        var realId = $(this).attr('data-cg-real-id');
        debugger
        $(this).addClass('cg_hide');
        var $cgSellContainerForm = $(this).closest('form');
        $cgSellContainerForm.find('#cgWatermarkOnDiv').addClass('cg_view_option_unchecked').removeClass('cg_view_option_checked');
        $cgSellContainerForm.find('#cgWatermarkOn').prop('checked',false);
        $cgSellContainerForm.find('#cgWatermarkSettingsRow').addClass('cg_disabled_background_color_e0e0e0');

        if(direction=='left'){
            if(order == 1){
                newOrder = Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length;
                $parent.find('.cgSellPreview[data-cg-order='+Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length+']').removeClass('cg_hide').addClass('active');
            }else{
                newOrder = order-1;
                $parent.find('.cgSellPreview[data-cg-order='+(order-1)+']').removeClass('cg_hide').addClass('active');
            }
        }

        if(direction=='right'){
            if(order == Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length){
                newOrder = 1;
                $parent.find('.cgSellPreview[data-cg-order=1]').removeClass('cg_hide').addClass('active');
            }else{
                newOrder = order+1;
                $parent.find('.cgSellPreview[data-cg-order='+(order+1)+']').removeClass('cg_hide').addClass('active');
            }
        }

        $parent.find('#cgSellArrowLeft').removeClass('cg_hide');
        $parent.find('#cgSellArrowRight').removeClass('cg_hide');

        if(newOrder==1){
            $parent.find('#cgSellArrowLeft').addClass('cg_hide');
        }

        // cgJsClassAdmin.gallery.vars.multipleFilesForPost is initiated
        cgJsClassAdmin.gallery.functions.getMultipleFileForPost(cgJsClassAdmin.gallery.vars.$sortableDiv,realId);

        for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){
                break;
            }
            if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].isRealIdSource){
                cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order] = cgJsClassAdmin.gallery.functions.setMultipleFileForPostFromBackendInfoContainer(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container'),realId);
            }
        }

        var $imagesCount=0;

        for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){
                break;
            }
            if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='image'){
                $imagesCount++;
            }
        }

        if(newOrder == $imagesCount){
            $parent.find('#cgSellArrowRight').addClass('cg_hide');
        }

        var WpUpload = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].WpUpload;
        if(cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload] && cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on']){
            $cgSellContainerForm.find('#cgWatermarkOnDiv').addClass('cg_view_option_checked').removeClass('cg_view_option_unchecked');
            $cgSellContainerForm.find('#cgWatermarkOn').prop('checked',true);
            $cgSellContainerForm.find('#cgWatermarkSettingsRow').removeClass('cg_disabled_background_color_e0e0e0');
            $cgSellContainerForm.find('#cgWatermarkSelectPosition').val(cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition']);
            $cgSellContainerForm.find('#cgWatermarkSelectSize').val(cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize']);
            $cgSellContainerForm.find('#cgWatermarkInputTitle').val(cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle']);
        }

    });

    $(document).on('click','#cgSellContainer .cg_backend_image_full_size_target a',function (e) {
        e.preventDefault();
    });

    $(document).on('click','#cgSellContainerFadeBackground.cg_active, #cgSellContainer .cg_message_close',function () {
        debugger
        $('#cgSellContainerFadeBackground').addClass('cg_hide').removeClass('cg_active');
        $('#cgSellContainer').addClass('cg_hide');
    });

    $(document).on('change','#cgCurrencySelect',function () {
        cgJsClassAdmin.gallery.currencyInputFunctions.cgCurrencySelectSet();
    });

    $(document).on('change','#cgCurrencySelectPosition',function () {
        cgJsClassAdmin.gallery.currencyInputFunctions.cgCurrencySelectSet();
    });

    $(document).on('change','#cgWatermarkSelectPosition',function () {
        debugger
        var WpUpload = $(this).closest('form').find('.cgSellPreview.active').attr('data-cg-wp-upload');
        cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on'] = true;
        cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition'] = $(this).val();
        cgJsClassAdmin.gallery.watermarkOnChange();
        localStorage.setItem('cgWatermarkPosition',$(this).val());
    });

    $(document).on('change','#cgWatermarkSelectSize',function () {
        var WpUpload = $(this).closest('form').find('.cgSellPreview.active').attr('data-cg-wp-upload');
        cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on'] = true;
        cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize'] = $(this).val();
        cgJsClassAdmin.gallery.watermarkOnChange();
        localStorage.setItem('cgWatermarkSize',$(this).val());
    });

    $(document).on('input','#cgWatermarkInputTitle',function () {
        var WpUpload = $(this).closest('form').find('.cgSellPreview.active').attr('data-cg-wp-upload');
        cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on'] = true;
        cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle'] = $(this).val();
        cgJsClassAdmin.gallery.watermarkOnChange();
        localStorage.setItem('cgWatermarkTitle',$(this).val());
    });

    /*$(document).on('focusout','#cgWatermarkInputTitle',function () {
        if($(this).val().trim()==''){$(this).val('Contest Gallery');}
        cgJsClassAdmin.gallery.watermark(cgJsClassAdmin.gallery.vars.backgroundUrlToSell,cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgWatermarkSelectPosition').val(),$(this).val(),cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgWatermarkSelectSize').val(),false,true,$('#cgSellPreviewContainer .cgSellPreview.active').attr('data-cg-order'));
    });*/

    $(document).on('click','#cgSellBackendImageContainerOption',function () {
        $('#cgActivateSaleOption').click();
    });

    $(document).on('click','#SaleType_download_option',function () {
        $('#SaleAmountContainer').addClass('cg_hide');
    });

    $(document).on('click','#SaleType_shipping_option',function () {
        $('.cg_download_sale').addClass('cg_hide');
        $('#SaleAmountContainer').removeClass('cg_hide');
    });


    $(document).on('click','.cg-ecommerce-shipping-info-copy-key',function (){

        var copyText = $(this).parent().find('.cg-ecommerce-shipping-info-key').text().trim();
        var el = document.createElement('textarea');
        el.value = copyText;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);

        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Key copied',true);

    });

    $(document).on('click', '#cgEcommerceShowApiResponseButton', function () {

        if($(this).hasClass('cg_is_stripe')){
            return;
        }

        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        var $cgEcommerceShowApiResponseContainer = $('#cgEcommerceShowApiResponseContainer');
        $cgEcommerceShowApiResponseContainer.removeClass('cg_hide').find('.cg-lds-dual-ring-gallery-hide').removeClass('cg_hide');
        $cgEcommerceShowApiResponseContainer.find('#cgEcommerceShowApiResponse').addClass('cg_hide');

        cgJsClass.gallery.vars.dom.body.addClass('cg_pointer_events_none');

        debugger
        $.ajax({
            url : cgJsClass.gallery.vars.ecommerce.admin_url+"admin-ajax.php",
            type : 'post',
            data : {
                action : 'post_cg_show_paypal_api_response',
                cg_order_id : cgJsClass.gallery.vars.ecommerce.OrderId,
            },
        }).done(function (response) {

            cgJsClass.gallery.vars.dom.body.removeClass('cg_pointer_events_none');

            debugger
            var parser = new DOMParser();
            var parsedHtml = parser.parseFromString(response, 'text/html');
            jQuery(parsedHtml).find('script[data-cg-processing="true"]').each(function () {
                var script = jQuery(this).html();
                eval(script);
            });

            $cgEcommerceShowApiResponseContainer.height($cgEcommerceShowApiResponseContainer.height());
            $cgEcommerceShowApiResponseContainer.find('.cg-lds-dual-ring-gallery-hide').addClass('cg_hide');
            $cgEcommerceShowApiResponseContainer.find('#cgEcommerceShowApiResponse').removeClass('cg_hide');
            $cgEcommerceShowApiResponseContainer.find('#cgEcommerceShowApiResponse').height($cgEcommerceShowApiResponseContainer.find('#cgEcommerceShowApiResponse').height());
            $cgEcommerceShowApiResponseContainer.find('#cgEcommerceShowApiResponseTextarea').get(0).innerHTML = JSON.stringify(cgJsClass.gallery.vars.ecommerce.payPalResponse, undefined, 4);

            // auf folgendes noch reagieren
            /*
<script data-cg-processing="true">
    cgJsClass.gallery.vars.ecommerce.payPalResponse = {
        "name": "AUTHENTICATION_FAILURE",
        "message": "Authentication failed due to invalid authentication credentials or a missing Authorization header.",
        "links": [{
            "href": "https:\/\/developer.paypal.com\/docs\/api\/overview\/#error",
            "rel": "information_link"
        }]
    };
</script>
 */
        }).fail(function (xhr, status, error) {

            cgJsClass.gallery.vars.dom.body.removeClass('cg_pointer_events_none');

            $cgEcommerceShowApiResponseContainer.addClass('cg_hide');
            cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDrop();

            if(cgJsClass.gallery.vars.ecommerce.payPalResponse['name']){
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(cgJsClass.gallery.vars.ecommerce.payPalResponse['name']);
            }else{
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Error calling PayPal data');
            }

            console.log('response error');
            console.log(response);

            return;

        }).always(function () {

            var test = 1;

        });


    });

    $(document).on('change', '.cg_view_option_select #WPLANG', function () {

        var $cgTranslationsForCountriesInput = $('#cgTranslationsForCountriesInput');
        $cgTranslationsForCountriesInput.val('');
        var value = $(this).find('option:selected').attr('lang').toUpperCase();
        var text = $(this).find('option:selected').text();
        var $cgAllowedCountriesToTranslation = $('#cgAllowedCountriesToTranslation');
        var  valueAllowedCountry = $cgAllowedCountriesToTranslation.val();
        var  textAllowedCountry = $cgAllowedCountriesToTranslation.find('option:selected').text();
        if(value && valueAllowedCountry){
            if(cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value] && cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value][valueAllowedCountry]){
                $cgTranslationsForCountriesInput.val(cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value][valueAllowedCountry]);
            }else{
                $cgTranslationsForCountriesInput.attr('placeholder',text+' translation for '+textAllowedCountry);
            }
        }
    });

    $(document).on('change', '.cg_view_option_select #cgAllowedCountriesToTranslation', function () {
        // currently not used
        return;
        var $cgTranslationsForCountriesInput = $('#cgTranslationsForCountriesInput');
        $cgTranslationsForCountriesInput.val('');
        var valueAllowedCountry = $(this).val().toUpperCase();
        var textAllowedCountry = $(this).find('option:selected').text();
        var $WPLANG = $('#WPLANG');
        var  value = $WPLANG.find('option:selected').attr('lang').toUpperCase();
        var  text = $WPLANG.find('option:selected').text();
        if(value && valueAllowedCountry){
            if(cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value] && cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value][valueAllowedCountry]){
                $cgTranslationsForCountriesInput.val(cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value][valueAllowedCountry]);
            }else{
                $cgTranslationsForCountriesInput.attr('placeholder',text+' translation for '+textAllowedCountry);
            }
        }
    });

    $(document).on('input', '.cg_view_option_input #cgTranslationsForCountriesInput', function () {
        var $cgAllowedCountriesToTranslation = $(this).closest('.cg_view_option').find('#cgAllowedCountriesToTranslation');
        var valueAllowedCountry = $cgAllowedCountriesToTranslation.val().toUpperCase();
        var textAllowedCountry = $cgAllowedCountriesToTranslation.find('option:selected').text();
        var $WPLANG = $(this).closest('.cg_view_option').find('#WPLANG');
        var  value = $WPLANG.find('option:selected').attr('lang').toUpperCase();
        var  text = $WPLANG.find('option:selected').text();
        if($(this).val().trim() && valueAllowedCountry){
            if(!cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value]){
                cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value] = {};
            }
            cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value][valueAllowedCountry] = $(this).val();
        }else{
            if(value && valueAllowedCountry){
                if(cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value] && cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value][valueAllowedCountry]){
                    delete cgJsClassAdmin.options.vars.AllowedCountriesTranslations[value][valueAllowedCountry];
                }
            }
        }

    });


});