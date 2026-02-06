jQuery(document).ready(function ($) {

    $(document).on('input','.cg_currency_input',function (e) {
        var $element = $(this);
        cgJsClassAdmin.gallery.functions.inputCurrency($,$element,e);
    });

    $(document).on('focusout', '.cg_currency_input', function(e) {
        var $element = $(this);
        if($element.hasClass('cg_currency_input_processed')){
            $element.removeClass('cg_currency_input_processed');
        }else{
            if($element.val().trim()==''){$element.val(0);}
            cgJsClassAdmin.gallery.currencyInputFunctions.setCurNew($element,true);
            return;
        }
    });


    $(document).on('focusin', '.cg_currency_input', function(e) {

        var $element = $(this);
        var selectionStartNew = e.target.selectionStart;
        var inputVal = String($element.val());
        $element.val(inputVal.replace(cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider+'00',''));
        e.target.selectionStart = selectionStartNew;
        e.target.selectionEnd = selectionStartNew;

    });

});