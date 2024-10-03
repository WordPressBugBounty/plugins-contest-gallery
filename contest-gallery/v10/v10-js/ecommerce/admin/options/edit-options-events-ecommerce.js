jQuery(document).ready(function($){

    // is better to set here
    cgJsClassAdmin.options.vars.ecommerce = cgJsClassAdmin.options.vars.ecommerce || {};
    cgJsClassAdmin.options.vars.ecommerce.transactions = cgJsClassAdmin.options.vars.ecommerce.transactions || {};
    cgJsClassAdmin.options.vars.ecommerce.invoices = cgJsClassAdmin.options.vars.ecommerce.invoices || {};

    $(document).on('click', '#RegUserPurchaseOnlyOption', function () {
        debugger
        if($(this).find('#RegUserPurchaseOnly').prop('checked')){
            $('#wp-RegUserPurchaseOnlyText-wrap-Container').removeClass('cg_disabled');
        }else{
            $('#wp-RegUserPurchaseOnlyText-wrap-Container').addClass('cg_disabled');
        }
    });

    $(document).on('click', '#RegUserOrderSummaryOnlyOption', function () {
        debugger
        if($(this).find('#RegUserOrderSummaryOnly').prop('checked')){
            $('#wp-RegUserOrderSummaryOnlyText-wrap-Container').removeClass('cg_disabled');
        }else{
            $('#wp-RegUserOrderSummaryOnlyText-wrap-Container').addClass('cg_disabled');
        }
    });

    $(document).on('click', '#CreateAndSetInvoiceNumberOption', function () {
        if($(this).find('#CreateAndSetInvoiceNumber').prop('checked')){
            $('#cgInvoiceNumberLogicAndResultContainer').removeClass('cg_disabled_one');
        }else{
            $('#cgInvoiceNumberLogicAndResultContainer').addClass('cg_disabled_one');
        }
    });

    $(document).on('click', '#CreateInvoiceOption', function () {

        if($(this).find('#CreateInvoice').prop('checked')){
            $('#SendInvoiceOption').removeClass('cg_disabled');
            $('#CreateAndSetInvoiceNumberOption').removeClass('cg_disabled');
            if($('#CreateAndSetInvoiceNumber').prop('checked')){
                $('#cgInvoiceNumberLogicAndResultContainer').removeClass('cg_disabled_one');
            }else{
                $('#cgInvoiceNumberLogicAndResultContainer').addClass('cg_disabled_one');
            }
        }else{
            $('#SendInvoiceOption').addClass('cg_disabled');
            $('#CreateAndSetInvoiceNumberOption').addClass('cg_disabled');
            $('#cgInvoiceNumberLogicAndResultContainer').addClass('cg_disabled_one');
        }

    });

    $(document).on('click', '#EcommerceInvoiceOptionsStartButton', function () {
        var height = $(this).height();
        $(this).addClass('cg_hide');
        var $EcommerceInvoiceOptionsEnvironment = $('#EcommerceInvoiceOptionsEnvironment');
        $EcommerceInvoiceOptionsEnvironment.height(height).removeClass('cg_hide');
    });

/*    $(document).on('click', '#EcommerceInvoiceSelectTemplate', function () {// could be usefull in future
        $(this).addClass('hide');
        $('#EcommerceInvoiceOptionsTemplatesLoader').removeClass('cg_hide');
        cgJsClassAdmin.options.functions.ecommerce.getEcommerceData($,$(this),'templates-list-live-and-sandbox');
    });*/

    $(document).on('click', '#ResetCustomNumberNextInvoiceOptionTest,#ResetCustomNumberNextInvoiceOptionLive', function () {
        var addOn = 'Test';
        if($(this).attr('id')=='ResetCustomNumberNextInvoiceOptionLive'){
            addOn = 'Live';
        }
        if($(this).find('#ResetCustomNumberNextInvoice'+addOn).prop('checked')){
            $('#InvoiceNumberLogicCustomNumberOption'+addOn).removeClass('cg_disabled_override').find('#InvoiceNumberLogicCustomNumber'+addOn).prop('disabled',false);
        }else{
            if($('#InvoiceNumberLogicCustomNumberIsGenerated'+addOn).val()=='1'){
                $('#InvoiceNumberLogicCustomNumber'+addOn).val($('#InvoiceNumberLogicCustomNumberPrevious'+addOn).val());
                cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResultNew($,$('#InvoiceNumberLogicCustomNumber'+addOn));
            }
            $('#InvoiceNumberLogicCustomNumberOption'+addOn).addClass('cg_disabled_override').find('#InvoiceNumberLogicCustomNumber'+addOn).prop('disabled',true);
        }
    });

    $(document).on('change', '#InvoiceNumberLogicSelectTest,#InvoiceNumberLogicSelectLive', function () {
        cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResultNew($,$(this));
        if($(this).val()==='timestamp'){
            if($(this).attr('id')=='InvoiceNumberLogicSelectTest'){
                $('#ResetCustomNumberNextInvoiceOptionTest').addClass('cg_disabled');
            }
            if($(this).attr('id')=='InvoiceNumberLogicSelectLive'){
                $('#ResetCustomNumberNextInvoiceOptionLive').addClass('cg_disabled');
            }
        }else{
            if($(this).attr('id')=='InvoiceNumberLogicSelectTest'){
                $('#ResetCustomNumberNextInvoiceOptionTest').removeClass('cg_disabled');
            }
            if($(this).attr('id')=='InvoiceNumberLogicSelectLive'){
                $('#ResetCustomNumberNextInvoiceOptionLive').removeClass('cg_disabled');
            }
        }
    });

    $(document).on('input', '#InvoiceNumberLogicOwnPrefixTest,#InvoiceNumberLogicOwnPrefixLive', function () {
        cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResultNew($,$(this));
    });

    $(document).on('input', '#InvoiceNumberLogicCustomNumberTest,#InvoiceNumberLogicCustomNumberLive', function () {
        cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResultNew($,$(this));
    });

    $(document).on('focusout', '#InvoiceNumberLogicCustomNumberTest,#InvoiceNumberLogicCustomNumberLive', function () {
        if($(this).val()=='' && $(this).attr('id')=='InvoiceNumberLogicCustomNumberTest'){
            $(this).val($('#InvoiceNumberLogicCustomNumberPreviousTest').val());
        }
        if($(this).val()=='' && $(this).attr('id')=='InvoiceNumberLogicCustomNumberLive'){
            $(this).val($('#InvoiceNumberLogicCustomNumberPreviousLive').val());
        }
        cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResultNew($,$(this));
    });

    /*
    $(document).on('change', '.cg_ecommerce_option_logic_select_new', function () {
        cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResultNew($);
    });

    $(document).on('change', '.cg_ecommerce_option_logic_select', function () {

        if($(this).val()=='ownprefix'){
            $('.cg_view_option_ecommerce_invoice_logic_part_prefix[data-cg-order="'+$(this).attr('data-cg-order')+'"]').removeClass('cg_disabled');
        }else{
            $('.cg_view_option_ecommerce_invoice_logic_part_prefix[data-cg-order="'+$(this).attr('data-cg-order')+'"]').addClass('cg_disabled');
        }

        cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResult($);

    });

    $(document).on('input', '.cg_ecommerce_option_logic_part_prefix', function () {

        cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResult($);

    });

    $(document).on('input', '#InvoiceNumberLogicFinalPart', function () {
        cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResult($);
    });

    $(document).on('focusout', '#InvoiceNumberLogicFinalPart', function () {
        if($(this).val()==''){
            $(this).val('0001');
        }
        cgJsClassAdmin.options.functions.ecommerce.setInvoiceLogicResult($);
    });*/

    $(document).on('click', '#EcommerceInvoiceOptionsSandboxStartButton', function () {
        $('#EcommerceInvoiceOptionsEnvironment').addClass('cg_hide');
        $('#EcommerceInvoiceOptionsSandbox').removeClass('cg_hide');
        $('#EcommerceInvoiceBackToSelectionEnvironment').removeClass('cg_hide');
        cgJsClassAdmin.options.vars.ecommerce.environment = $(this).attr('data-cg-ecommerce-environment');
    });

    $(document).on('click', '#EcommerceInvoiceBackToSelectionEnvironment', function () {
        $(this).addClass('cg_hide');
        $('#EcommerceInvoiceOptionsSandbox').addClass('cg_hide');
        $('#EcommerceInvoiceResultContainer').addClass('cg_hide');
        $('#EcommerceInvoiceOptionsEnvironment').removeClass('cg_hide');
        $('#EcommerceInvoiceTransactionsListSelectContainer').addClass('cg_hide');
    });

    $(document).on('click', '#EcommerceInvoiceBackToSelectionOptionsSandbox', function () {
        $(this).addClass('cg_hide');
        $('#EcommerceInvoiceOptionsSandbox').removeClass('cg_hide');
        $('#EcommerceInvoiceResultContainer').addClass('cg_hide');
        $('#EcommerceInvoiceOptionsSandboxStartButton').removeClass('cg_hide');
        $('#EcommerceInvoiceBackToSelectionEnvironment').removeClass('cg_hide');
        $('#EcommerceInvoiceTransactionsListSelectContainer').addClass('cg_hide');
    });

    $(document).on('click', '#EcommerceInvoiceTransactionsListButton', function () {
        $('#EcommerceInvoiceOptionsSandbox').addClass('cg_hide');
        $('#EcommerceInvoiceBackToSelectionEnvironment').addClass('cg_hide');
        if(cgJsClassAdmin.options.vars.ecommerce.environment=='sandbox'){
            $('#EcommerceInvoiceBackToSelectionOptionsSandbox').removeClass('cg_hide');
        }else{
            $('#EcommerceInvoiceBackToSelectionOptionsLive').removeClass('cg_hide');
        }
        $('#EcommerceInvoiceTransactionsListSelectContainer').removeClass('cg_hide');

        var $form = $('#cgEcommerceInvoicing');
        $form.find('[name="cg_ecommerce_transaction_list_year"]').val($('#EcommerceInvoiceTransactionsListYear').val());
        $form.find('[name="cg_ecommerce_transaction_list_month"]').val($('#EcommerceInvoiceTransactionsListMonth').val());

        cgJsClassAdmin.options.functions.ecommerce.getEcommerceData($,undefined,'transaction-list');
    });

    $(document).on('click', '.cg_ecommerce_invoice_search,.cg_ecommerce_invoice_delete', function () {
        $('#EcommerceInvoiceOptionsSandbox').addClass('cg_hide');
        $('#EcommerceInvoiceTransactionsListSelectContainer').addClass('cg_hide');
        $('#EcommerceInvoiceBackToSelectionEnvironment').addClass('cg_hide');
        if(cgJsClassAdmin.options.vars.ecommerce.environment=='sandbox'){
            $('#EcommerceInvoiceBackToSelectionOptionsSandbox').removeClass('cg_hide');
        }else{
            $('#EcommerceInvoiceBackToSelectionOptionsLive').removeClass('cg_hide');
        }
        var $form = $('#cgEcommerceInvoicing');
        if($(this).hasClass('cg_ecommerce_invoice_search')){
            $form.find('[name="cg_ecommerce_recipient_email"]').val($(this).attr('data-cg-ecommerce-recipient-email'));
            cgJsClassAdmin.options.functions.ecommerce.getEcommerceData($,undefined,'invoice-search');
        }
        if($(this).hasClass('cg_ecommerce_invoice_delete')){
            $form.find('[name="cg_ecommerce_invoice_id"]').val($(this).attr('data-cg-ecommerce-invoice-id'));
            cgJsClassAdmin.options.functions.ecommerce.getEcommerceData($,undefined,'invoice-delete');
        }
    });

    $(document).on('click','.cg_ecommerce_show_details',function () {
        var $cg_view_options_row = $(this).closest('.cg_view_options_row');
        $(this).addClass('cg_hide');
        $cg_view_options_row.find('.cg_ecommerce_hide_details').removeClass('cg_hide');
        var $textarea = $cg_view_options_row.find('.cg-ecommerce-textarea').removeClass('cg_hide');

        if($cg_view_options_row.hasClass('ecommerce-transaction')){
            $textarea.get(0).innerHTML = JSON.stringify(cgJsClassAdmin.options.vars.ecommerce.transactions.transaction_details[$(this).attr('data-cg-ecommerce-transaction')], undefined, 4);
        }
        if($cg_view_options_row.hasClass('ecommerce-invoice')){
            $textarea.get(0).innerHTML = JSON.stringify(cgJsClassAdmin.options.vars.ecommerce.invoices.items[$(this).attr('data-cg-ecommerce-invoice')], undefined, 4);
        }
        $textarea.height($textarea.get(0).scrollHeight)
    });

    $(document).on('click','.cg_ecommerce_hide_details',function () {
        var $cg_view_options_row = $(this).closest('.cg_view_options_row');
        $(this).addClass('cg_hide');
        $cg_view_options_row.find('.cg-ecommerce-textarea').addClass('cg_hide');
        $cg_view_options_row.find('.cg_ecommerce_transaction_show_details').removeClass('cg_hide');
        $cg_view_options_row.get(0).scrollIntoView();
    });

    $(document).on('change','#EcommerceInvoiceTransactionsListYear,#EcommerceInvoiceTransactionsListMonth',function () {
        $('#EcommerceInvoiceResultContainer').addClass('cg_hide');
        cgJsClassAdmin.options.functions.ecommerce.getEcommerceData($,undefined,'transaction-list',$('#EcommerceInvoiceTransactionsListYear').val(),$('#EcommerceInvoiceTransactionsListMonth').val());
    });

    $(document).on('click','.cg_invoicing_type',function () {
        var $form = $('#cgEcommerceInvoicing');
        if($(this).attr('template-list-set-default-before')=='template-list-set-default-before'){
            $form.find('[name="cg_ecommerce_default_invoice_template_id"]').attr($(this).attr('data-cg-ecommerce-new-default-invoice-template-id'));
        }
        cgJsClassAdmin.options.functions.ecommerce.getEcommerceData($,$(this));
    });

    $(document).on('click', '#SendOrderConfirmationMailContainer', function () {
        if($(this).find('#SendOrderConfirmationMail').prop('checked')){
            $(this).closest('.cg_view').find('.cg_send_order_confirmation_mail').removeClass('cg_disabled');
        }else{
            $(this).closest('.cg_view').find('.cg_send_order_confirmation_mail').addClass('cg_disabled');
        }
    });

});