<?php
if(!function_exists('cg_ecommerce_create_invoice_address_for_html_output')) {
    function cg_ecommerce_create_invoice_address_for_html_output($InvoiceAddressFirstName,$InvoiceAddressLastName,$InvoiceAddressCompany,$InvoiceAddressLine1,$InvoiceAddressLine2,$InvoiceAddressStateShort,$InvoiceAddressCountryShort,$InvoiceAddressCity,$InvoiceAddressPostalCode,$InvoiceAddressStateTranslation,$EUshortcodes,$InvoiceAddressCountryTranslation,$EcommerceTaxNr,$language_VatNumber) {

        $InvoiceAddressForHtmlOutput = $InvoiceAddressFirstName.' '.$InvoiceAddressLastName;
        $InvoiceAddressForHtmlOutput .= ($InvoiceAddressCompany) ? '<br>'.$InvoiceAddressCompany : '';
        $InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressLine1;
        $InvoiceAddressForHtmlOutput .= ($InvoiceAddressLine2) ? '<br>'.$InvoiceAddressLine2 : '';
        if($InvoiceAddressStateShort){
            if($InvoiceAddressCountryShort=='US'  || $InvoiceAddressCountryShort=='CA'){
                $InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressCity.' '.$InvoiceAddressStateShort.' '.$InvoiceAddressPostalCode;
            }else{
                $InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressCity.' '.$InvoiceAddressStateTranslation.' '.$InvoiceAddressPostalCode;
            }
        }else{
            if(in_array($InvoiceAddressCountryShort,$EUshortcodes)!==false){
                $InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressPostalCode.' '.$InvoiceAddressCity;
            }else{
                $InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressCity.' '.$InvoiceAddressPostalCode;
            }
        }

        $InvoiceAddressForHtmlOutput .= ($InvoiceAddressCountryTranslation) ? '<br>'.$InvoiceAddressCountryTranslation : '';
        if($EcommerceTaxNr){
            $InvoiceAddressForHtmlOutput .=  '<br>'.$language_VatNumber.': '.$EcommerceTaxNr;
        }

        return $InvoiceAddressForHtmlOutput;

    }
}


?>