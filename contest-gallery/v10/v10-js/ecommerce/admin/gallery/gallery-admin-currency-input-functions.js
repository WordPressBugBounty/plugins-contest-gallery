cgJsClassAdmin.gallery.currencyInputFunctions = {
    cgCurrencySelectSet: function () {
        var dataCurSymbol = jQuery('#cgCurrencySelect option:selected').attr('data-cur-symbol');
        var curPosition = jQuery('#cgCurrencySelectPosition').val();
        if(curPosition=='left'){
            jQuery('#cgSellPriceConfCurSymbolRight').text('');
            jQuery('#cgSellPriceConfCurSymbolLeft').text(dataCurSymbol);
        }else{
            jQuery('#cgSellPriceConfCurSymbolLeft').text('');
            jQuery('#cgSellPriceConfCurSymbolRight').text(dataCurSymbol);
        }
    },
    thousandsDivider: ',',
    priceDivider: '.',
    setCurNew: function($element,isFocusOut,isAfterSubmit,e,priceDivider,thousandsDivider) {
        var cg_currency_input_decimal_disallowed_this_why_improved = false;
        if(!$element.hasClass('cg_currency_input_decimal_disallowed')){
            cg_currency_input_decimal_disallowed_this_why_improved = true;
        }
        cgJsClassAdmin.gallery.currencyInputFunctions.processCur($element,isFocusOut,cg_currency_input_decimal_disallowed_this_why_improved,e,priceDivider,thousandsDivider);
    },
    cg_currency_input_decimal_disallowed_this_why_improved: function (value,priceDivider,thousandsDivider,isFocusOut){

        var posd = value.indexOf(priceDivider);
        var left = value.substring(0, posd);
        var right = value.substring(posd);
        left = cgJsClassAdmin.gallery.currencyInputFunctions.codepenFormat(left,thousandsDivider);
        if(left==''){
            left = '0';
        }else{
            if(left.length>1 && left[0]=='0'){
                left = left.slice(1);
            }
        }
        right = cgJsClassAdmin.gallery.currencyInputFunctions.codepenFormat(right,thousandsDivider);
        right = right.substring(0, 2);
        if(right.slice('-1')==thousandsDivider){
            right = right.replace(/.$/,"");
            right = right+'0';
        }
        if (isFocusOut) {
            if(right.length==0){
                right = '00';
            }else if(right.length==1){
                right = right+'0';
            }
        }
        value = left + priceDivider + right;

        return value;

    },
    cg_currency_input_decimal_allowed_this_why_not_improved: function (value,priceDivider,thousandsDivider,isFocusOut){

        var posd = value.indexOf(priceDivider);
        var left = value.substring(0, posd);
        var right = value.substring(posd);
        left = cgJsClassAdmin.gallery.currencyInputFunctions.codepenFormat(left,thousandsDivider);
        right = cgJsClassAdmin.gallery.currencyInputFunctions.codepenFormat(right,thousandsDivider);
        if (isFocusOut) {
            right += "00";
        }
        right = right.substring(0, 2);
        value = left + priceDivider + right;

        return value;

    },
    processCur: function($element, isFocusOut,cg_currency_input_decimal_disallowed_this_why_improved,e,priceDivider,thousandsDivider) {
        priceDivider = priceDivider || cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider;
        thousandsDivider = thousandsDivider || cgJsClassAdmin.gallery.currencyInputFunctions.thousandsDivider;
        var value = $element.val();
        if (value === "") { return; }
        if (value.length>=2) {
            if(value.charAt(0)==0 && value.charAt(1)!=cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider){
                value = value.slice(1);
            }
        }
        if($element.hasClass('cg_currency_input_tax')){
            var valueToCheck = $element.val();
            var decimal_pos  = valueToCheck.indexOf(priceDivider);
            valueToCheck = cgJsClassAdmin.gallery.currencyInputFunctions.correctInputVal(decimal_pos,valueToCheck,true,priceDivider);
            if(valueToCheck>100){
                value =  String(100);
            }
        }
        var length = value.length;
        var carpos = $element.prop("selectionStart");
        debugger
        if (value.indexOf(priceDivider) === -1) {
            value = cgJsClassAdmin.gallery.currencyInputFunctions.codepenFormat(value);
            if (isFocusOut && value!='') {
                value += priceDivider+"00";
            }
        } else {
            if(cg_currency_input_decimal_disallowed_this_why_improved){
                value = this.cg_currency_input_decimal_disallowed_this_why_improved(value,priceDivider,thousandsDivider,isFocusOut);
            }else{
                value = this.cg_currency_input_decimal_allowed_this_why_not_improved(value,priceDivider,thousandsDivider,isFocusOut);
            }
        }
        $element.val(value);
        if(e && e.originalEvent.data==priceDivider && !isFocusOut && value.indexOf(priceDivider)>=0){
            carpos = (value.indexOf(priceDivider)+1);
            $element[0].setSelectionRange(carpos, carpos);
        }else{
            if(!isFocusOut){
                var updated_len = value.length;
                carpos = updated_len - length + carpos;
                $element[0].setSelectionRange(carpos, carpos);
            }
        }
    },
    codepenFormat: function (input_val,thousandsDivider){
        thousandsDivider = thousandsDivider || cgJsClassAdmin.gallery.currencyInputFunctions.thousandsDivider;
        //https://codepen.io/559wade/pen/LRzEjj
        return input_val.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, thousandsDivider);
    },
    correctInputVal: function (decimal_pos,input_val,isReturnFloatOnly,priceDivider,thousandsDivider){

        priceDivider = priceDivider || cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider;
        thousandsDivider = thousandsDivider || cgJsClassAdmin.gallery.currencyInputFunctions.thousandsDivider;

        if(decimal_pos==-1 || decimal_pos==''){
            input_val = input_val+priceDivider+'00';
            decimal_pos  = input_val.indexOf(priceDivider);
        }

        // split number by decimal point
        var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);
        if(priceDivider=='.'){
            right_side = right_side.replace(/\./g, '');
            right_side = right_side.replace(/\,/g, '');
        }else if(priceDivider==','){
            right_side = right_side.replace(/\,/g, '');
            right_side = right_side.replace(/\./g, '');
        }

        if(isReturnFloatOnly){
            if(priceDivider=='.'){
                left_side = left_side.replace(/\,/g, '');
            }else if(priceDivider==','){
                left_side = left_side.replace(/\./g, '');
            }
            return parseFloat(left_side+'.'+right_side);
        }

        // Limit decimal to only 2 digits
        right_side = right_side.substring(0, 2);

        console.log('right_side after cut');
        console.log(right_side);

        // add commas or dots to left side of number
        left_side = cgJsClassAdmin.gallery.currencyInputFunctions.codepenFormat(left_side,thousandsDivider);
        if(left_side==''){
            left_side = '0';
        }else{

            console.log('left_side[0]')
            console.log(left_side[0])

            if(left_side.length>1 && left_side[0]=='0'){
                left_side = left_side.slice(1);
            }
        }

        // validate right side
        right_side = cgJsClassAdmin.gallery.currencyInputFunctions.codepenFormat(right_side,thousandsDivider);

        if(right_side.slice('-1')==priceDivider){
            right_side = right_side.replace(/.$/,"")// .$ will match any character at the end of a string.
            right_side = right_side+'0';// there can be only the case that after first decimal is a ., that's why simply 0 can be added
        }

        if (blur) {
            if(right_side.length==0){
                right_side = '00';
            }else if(right_side.length==1){
                right_side = right_side+'0';
            }
        }

        console.log('left_side');
        console.log(left_side);
        console.log('right_side');
        console.log(right_side);

        // join number by .
        input_val = left_side + priceDivider + right_side;

        return input_val;

    }
};