cgJsClassAdmin.options.functions.ecommerce = {
    setInvoiceLogicResultNew: function ($,$element){

        var resultString = '';
        var isTimestamp = false;
        var idExtenderTestOrLive = $element.attr('id').substr(-4);

        var $cgInvoiceNumberLogicAndResultContainer = $('#cgInvoiceNumberLogicAndResultContainer');

        var selectPartVal = $cgInvoiceNumberLogicAndResultContainer.find('#InvoiceNumberLogicSelect'+idExtenderTestOrLive).val();

        var dateTime = new Date();
        if(selectPartVal=='unset'){
            resultString += '';
        } else if(selectPartVal=='year'){
            resultString += dateTime.getFullYear();
        } else if(selectPartVal=='month'){
            resultString += (dateTime.getMonth()<9) ?  '0' + String((dateTime.getMonth()+1)).slice(-2) : String((dateTime.getMonth()+1));
        } else if(selectPartVal=='year-month'){
            var month = (dateTime.getMonth()<9) ?  '0' + String((dateTime.getMonth()+1)).slice(-2) : String((dateTime.getMonth()+1));
            resultString += String(dateTime.getFullYear())+'-'+month;
        }else if(selectPartVal=='timestamp'){
            isTimestamp = true;
            var timestamp = Math.floor(new Date().getTime()/1000);
            resultString = timestamp;
        }

        if(isTimestamp){
            $('#InvoiceNumberLogicOwnPrefix'+idExtenderTestOrLive+',#InvoiceNumberLogicCustomNumber'+idExtenderTestOrLive).closest('.cg_view_option').addClass('cg_disabled');
        }else{
            $('#InvoiceNumberLogicOwnPrefix'+idExtenderTestOrLive+',#InvoiceNumberLogicCustomNumber'+idExtenderTestOrLive).closest('.cg_view_option').removeClass('cg_disabled');
        }

        var ownPrefixVal = $cgInvoiceNumberLogicAndResultContainer.find('#InvoiceNumberLogicOwnPrefix'+idExtenderTestOrLive).val();

        if(!isTimestamp && ownPrefixVal){
            if(resultString){
                resultString += '-'+ownPrefixVal;
            }else{
                resultString += ownPrefixVal;
            }
        }

        var finalPartVal = $cgInvoiceNumberLogicAndResultContainer.find('#InvoiceNumberLogicCustomNumber'+idExtenderTestOrLive).val();

        if(!finalPartVal){
            if(idExtenderTestOrLive=='Test'){$(this).val($('#InvoiceNumberLogicCustomNumberPreviousTest').val());}
            if(idExtenderTestOrLive=='Live'){$(this).val($('#InvoiceNumberLogicCustomNumberPreviousLive').val());}
        }

        if(!isTimestamp){
            if(resultString){
                resultString += '-'+finalPartVal;
            }else{
                resultString += finalPartVal;
            }
        }

        $cgInvoiceNumberLogicAndResultContainer.find('#InvoiceNumberLogicResult'+idExtenderTestOrLive).val(resultString);

    },
    setInvoiceLogicResult: function ($){

        var resultString = '';

        var lastOneIsOwnAndSetPrefix = false;
        var lastSet = 0;
        var lastOwnPrefix = 0;

        $('.cg_ecommerce_option_logic_select').each(function (index){

            var dateTime = new Date();

            if($(this).val()=='unset'){
                resultString += '';
            } else if($(this).val()=='year'){
                resultString += dateTime.getFullYear()+'-';lastSet=index+1;
            } else if($(this).val()=='month'){
                resultString += (dateTime.getMonth()<9) ?  '0' + String((dateTime.getMonth()+1)).slice(-2)+'-' : String((dateTime.getMonth()+1))+'-';lastSet=index+1;
            } else if($(this).val()=='year-month'){
                var month = (dateTime.getMonth()<9) ?  '0' + String((dateTime.getMonth()+1)).slice(-2)+'-' : String((dateTime.getMonth()+1))+'-';
                resultString += String(dateTime.getFullYear())+'-'+month;lastSet=index+1;
            } else if($(this).val()=='ownprefix' && $('.cg_ecommerce_option_logic_part_prefix[data-cg-order="'+$(this).attr('data-cg-order')+'"]').val().trim()!=''){
                resultString += $('.cg_ecommerce_option_logic_part_prefix[data-cg-order="'+$(this).attr('data-cg-order')+'"]').val()+'-';lastSet=index+1;lastOwnPrefix=index+1;
            }

            $('.cg_ecommerce_option_logic_part_hidden[data-cg-order="'+$(this).attr('data-cg-order')+'"]').val($(this).val());

        });

        console.log('lastSet');
        console.log(lastSet);

        console.log('lastOwnPrefix');
        console.log(lastOwnPrefix);

        resultString = resultString.substr(0,resultString.length-1);
        if(resultString.slice(-1)=='-'){resultString = resultString.substr(0,resultString.length-1);}
        if(resultString.slice(-1)=='-'){resultString = resultString.substr(0,resultString.length-1);}
        if(resultString.slice(-1)=='-'){resultString = resultString.substr(0,resultString.length-1);}

        if(resultString.length && cgJsClassAdmin.index.functions.isNumber(resultString.slice(-1)) && lastSet===lastOwnPrefix){
            var resultStringNew;
            var collectedNumberAtTheEnd;
            var iToSlice = 1;
            var iToSliceBefore = 1;
            for(var i = resultString.length;i>0;i--){
                if(cgJsClassAdmin.index.functions.isNumber(resultString.slice(-iToSlice))){
                    iToSliceBefore = iToSlice;
                    iToSlice++;
                }else{
                    collectedNumberAtTheEnd = resultString.slice(-iToSliceBefore);
                    collectedNumberAtTheEnd++;
                    resultStringNew = resultString.slice(0,-iToSliceBefore)+collectedNumberAtTheEnd;
                    break;
                }
            }
            if(resultStringNew){
                resultString = resultStringNew;
            }
        }else{
            if(resultString == '' || resultString=='-' || resultString=='--' || resultString=='---'){
                resultString = '0001';
            }else{
                if(resultString.slice(-1)=='-'){
                    resultString.substr(0,resultString.length-1);
                }else if(resultString.slice(-2)=='--'){
                    resultString.substr(0,resultString.length-2);
                }else if(resultString.slice(-3)=='---'){
                    resultString.substr(0,resultString.length-3);
                }
                resultString = resultString + '-'+$('#InvoiceNumberLogicFinalPart').val();
            }
        }

        resultString = resultString.replaceAll('---','-');
        resultString = resultString.replaceAll('--','-');

        if(resultString.slice(0,1)=='-'){
            resultString = resultString.slice(1);
        }


        $('#InvoiceNumberLogicResult').val(resultString);

    },
    getEcommerceData: function($,$element,cg_invoicing_type,cg_ecommerce_transaction_list_year,cg_ecommerce_transaction_list_month){

        var $form = $('#cgEcommerceInvoicing');
        var $EcommerceInvoiceResultContainer = $('#EcommerceInvoiceResultContainer');
        $EcommerceInvoiceResultContainer.addClass('cg_hide');
        var $EcommerceInvoiceBackToSelectionEnvironment = $('#EcommerceInvoiceBackToSelectionEnvironment');
        var $EcommerceInvoiceOptionsSandbox = $('#EcommerceInvoiceOptionsSandbox');
        var $EcommerceInvoiceOptionsLoader = $('#EcommerceInvoiceOptionsLoader');
        var $EcommerceInvoiceBackToSelectionOptionsSandbox = $('#EcommerceInvoiceBackToSelectionOptionsSandbox');
        $EcommerceInvoiceBackToSelectionEnvironment.addClass('cg_hide');
        $EcommerceInvoiceOptionsSandbox.addClass('cg_hide');
        if($(this).attr('id')!='EcommerceInvoiceOptionsTemplatesLoader'){
            $EcommerceInvoiceOptionsLoader.removeClass('cg_hide');
        }

        if(!cg_invoicing_type){
            cg_invoicing_type = $element.attr('data-cg-invoicing-type');
        }

        $form.find('[name="cg_invoicing_type"]').val(cg_invoicing_type);
        $form.find('[name="cg_ecommerce_environment"]').val(cgJsClassAdmin.options.vars.ecommerce.environment);

        if(cg_ecommerce_transaction_list_year){
            $form.find('[name="cg_ecommerce_transaction_list_year"]').val(cg_ecommerce_transaction_list_year);
        }

        if(cg_ecommerce_transaction_list_month){
            $form.find('[name="cg_ecommerce_transaction_list_month"]').val(cg_ecommerce_transaction_list_month);
        }

        var form = $form.get(0);
        var formPostData = new FormData(form);

        //cgJsClassAdmin.gallery.vars.cgSellContainer.find('.cg_message_close').click();

        var cg_ecommerce_access_token = JSON.parse(localStorage.getItem('cgEcommerceAccessTokenData'));
        var end = Date.now();

        if(cg_ecommerce_access_token){
            $form.find('[name="cg_ecommerce_access_token"]').val(cg_ecommerce_access_token);
        }else{
            $form.find('[name="cg_ecommerce_access_token"]').val('');
        }

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: formPostData,
            dataType: null,
            contentType: false,
            processData: false
        }).done(function (response) {

            if(cgJsClassAdmin.options.vars.ecommerce.environment=='sandbox'){
                $EcommerceInvoiceBackToSelectionOptionsSandbox.removeClass('cg_hide');
            }

            var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));
            if($(this).attr('id')!='EcommerceInvoiceOptionsTemplatesLoader'){
                $EcommerceInvoiceResultContainer.height($EcommerceInvoiceResultContainer.height()).empty().append($response.find('#EcommerceInvoiceResult').html());
                $EcommerceInvoiceResultContainer.css('height','');
                $EcommerceInvoiceOptionsLoader.addClass('cg_hide');
                $EcommerceInvoiceResultContainer.removeClass('cg_hide');
            }else{
                var $EcommerceInvoiceTemplatesSelectResultContainer = $('#EcommerceInvoiceTemplatesSelectResultContainer');
                $EcommerceInvoiceTemplatesSelectResultContainer.height($EcommerceInvoiceTemplatesSelectResultContainer.height()).empty().append($response.find('#EcommerceInvoiceResult').html());
                $EcommerceInvoiceTemplatesSelectResultContainer.css('height','');
                $('#EcommerceInvoiceOptionsTemplatesLoader').addClass('cg_hide');
                $EcommerceInvoiceTemplatesSelectResultContainer.removeClass('cg_hide');
            }

            jQuery($response).find('script[data-cg-processing="true"]').each(function () {

                var script = jQuery(this).html();
                eval(script);

            });

            var test = 1;

        }).fail(function (xhr, status, error) {

            debugger

            var test = 1;

            cgJsClassAdmin.gallery.vars.newPreviewForEcommerceSale = '';

        }).always(function () {

            var test = 1;

        });

    }
};
