<?php

echo <<<HEREDOC
    <div class='cg_view_options_rows_container'>
<p class="cg_view_options_rows_container_title ">
        <strong>* NOTE:</strong> Invoice options are general and valid for all galleries.<br>
</p>
HEREDOC;

echo "<div class='$cgProFalse'>";

echo <<<HEREDOC
<div id="EcommerceFurtherInvoiceOptions">
                <div class="cg_view_options_row">
                    <div class="cg_view_option cg_view_option_100_percent" id="CreateInvoiceOption">
                        <div class="cg_view_option_title">
                            <p style="margin-right: -30px;">Create invoice
                            <br><span class="cg_view_option_title_note"><b>NOTE:</b> Invoice can be downloaded at the order summary page<br>
                                <span class='cg_shortcode_parent'><span class='cg_shortcode_copy_text' style="position:relative;">[cg_order_summary]<span class='cg_shortcode_copy cg_shortcode_copy_mail_confirm cg_tooltip'></span></span>
                                </span>
                                <br><span class="cg_color_red"><b>NOTE:</b></span> If deactivated "Billing address" will be not asked during checkout
                                </span>
                            </p>
                        </div>
                        <div class="cg_view_option_checkbox cg_view_option_checked">
                            <input type="checkbox" name="CreateInvoice" id="CreateInvoice" $CreateInvoice>
                        </div>
                    </div>
            </div>
                <div class="cg_view_options_row">
                    <div class="cg_view_option cg_view_option_100_percent cg_border_top_none $cg_invoice_disabled" id="SendInvoiceOption">
                        <div class="cg_view_option_title">
                            <p style="margin-right: -30px;">Attach invoice to "Order confirmation email"<br><span class="cg_view_option_title_note"><b>NOTE:</b> Invoice will be attached at "Order confirmation email" if "Send order confirmation email after purchase" is activated</span></p>
                        </div>
                        <div class="cg_view_option_checkbox cg_view_option_checked">
                            <input type="checkbox" name="SendInvoice" id="SendInvoice" $SendInvoice>
                        </div>
                    </div>
            </div>          
HEREDOC;

/*
echo <<<HEREDOC
<div class="cg_view_options_row">
    <div class="cg_view_option cg_view_option_100_percent cg_border_top_none" >
        <div class="cg_view_option_title">
            <p style="margin-right: -30px;">UseNewInvoiceNumberLogic</p>
        </div>
        <div class="cg_view_option_checkbox cg_view_option_checked">
            <input type="checkbox" name="UseNewInvoiceNumberLogic" id="UseNewInvoiceNumberLogic" $UseNewInvoiceNumberLogic>
        </div>
    </div>
</div>
HEREDOC;
*/

echo <<<HEREDOC
<div class="cg_view_options_row">
    <div class="cg_view_option cg_view_option_100_percent cg_border_top_none $cg_invoice_disabled" id="CreateAndSetInvoiceNumberOption">
        <div class="cg_view_option_title">
            <p style="margin-right: -30px;">Create and set invoice number in invoice</p>
        </div>
        <div class="cg_view_option_checkbox cg_view_option_checked">
            <input type="checkbox" name="CreateAndSetInvoiceNumber" id="CreateAndSetInvoiceNumber" $CreateAndSetInvoiceNumber>
        </div>
    </div>
</div>
HEREDOC;

/*$InvoiceNumberLogicResult = '';
$InvoiceNumberLogicPartOne = '';
$InvoiceNumberLogicPartTwo = '';
$InvoiceNumberLogicPartThree = '';
$InvoiceNumberLogicPartOneResultPrefixDisabled = '';
$InvoiceNumberLogicPartTwoResultPrefixDisabled = '';
$InvoiceNumberLogicPartThreeResultPrefixDisabled = '';
$InvoiceNumberLogicPartOneResultPrefix = '';
$InvoiceNumberLogicPartTwoResultPrefix = '';
$InvoiceNumberLogicPartThreeResultPrefix = '';
$InvoiceNumberLogicPartOneSelectOptions = '';
$InvoiceNumberLogicPartTwoSelectOptions = '';
$InvoiceNumberLogicPartThreeSelectOptions = '';*/

$InvoiceNumberLogicResultArrayTest = cg_invoice_number_logic_result_new($InvoiceNumberLogicSelectTest,$InvoiceNumberLogicOwnPrefixTest,$InvoiceNumberLogicCustomNumberTest);

$InvoiceNumberLogicResultTest = $InvoiceNumberLogicResultArrayTest['InvoiceNumberLogicResult'];
$InvoiceNumberLogicSelectOptionsTest = $InvoiceNumberLogicResultArrayTest['InvoiceNumberLogicSelectOptions'];

$InvoiceNumberLogicResultArrayLive = cg_invoice_number_logic_result_new($InvoiceNumberLogicSelectLive,$InvoiceNumberLogicOwnPrefixLive,$InvoiceNumberLogicCustomNumberLive);

$InvoiceNumberLogicResultLive = $InvoiceNumberLogicResultArrayLive['InvoiceNumberLogicResult'];
$InvoiceNumberLogicSelectOptionsLive = $InvoiceNumberLogicResultArrayLive['InvoiceNumberLogicSelectOptions'];

include ('view-ecommerce-invoice-number-result-container.php');

echo <<<HEREDOC
             <div class='cg_view_options_row'>
                    <div style="padding-top:15px;" class='cg_view_option cg_view_option_full_width cg_border_top_none ' id="InvoicerHeaderDataContainer" >
                        <div class='cg_view_option_title'>
                            <p>Invoicer Header Data<br><span class='cg_view_option_title_note'><b>NOTE:</b> appears top right on an invoice</span></p>
                        </div>
                        <div class="cg_view_option_input">
                            <textarea name="InvoicerHeaderData" class="InvoicerHeaderData" data-cg-country="default" maxlength="1000" placeholder="Your contact data"  rows="5"  style="width:600px;" >$InvoicerHeaderData</textarea>
                        </div>
                    </div>
            </div>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_row' style="margin-bottom: 30px;">
                <div class='cg_view_option cg_view_option_flex_flow_column  cg_view_option_50_percent cg_border_top_none cg_border_right_none cg_border_border_bottom_left_radius_8_px' id="InvoiceNoteCountrySelectContainer" >
                    <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p>Select for different customer country a different note</p>
                    </div>
                    <div class='cg_view_option_select' style="flex-grow: 1;">
  <select  id='InvoiceNoteCountrySelect' class='InvoiceNoteCountrySelect'>
HEREDOC;

$currencies_array = cg_get_ecommerce_currencies_array();

$country_codes_paypal_csv = __DIR__.'/../../../../functions/ecommerce/general/country_codes_paypal.csv';
$countriesArray = [];
$countriesArray['default'] = 'Default';

if(file_exists($country_codes_paypal_csv)){
    $row = 1;
    if (($handle = fopen($country_codes_paypal_csv, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            //   echo "<p> $num Felder in Zeile $row: <br /></p>\n";
            $row++;
            for ($c=0; $c < $num; $c++) {
                $explode = explode(';',$data[$c]);
                $country= $explode[0];
                $country_code = $explode[1];
                $countriesArray[$country_code] =  $country;
            }
        }
        fclose($handle);
    }
}

$countriesArrayNew = [];
$countriesArrayNew['default'] = $countriesArray['default'];
$countriesArrayNew['US'] = $countriesArray['US'];
$countriesArrayNew['GB'] = $countriesArray['GB'];
$countriesArrayNew['DE'] = $countriesArray['DE'];
$countriesArrayNew['FR'] = $countriesArray['FR'];
$countriesArrayNew['IT'] = $countriesArray['IT'];
$countriesArrayNew['ES'] = $countriesArray['ES'];
$countriesArrayNew['PL'] = $countriesArray['PL'];
$countriesArrayNew['NL'] = $countriesArray['NL'];
$countriesArrayNew['PT'] = $countriesArray['PT'];

foreach ($countriesArray as $country_code => $country){
    if($country_code=='default' || $country_code=='US' || $country_code=='GB' || $country_code=='DE' || $country_code=='FR' || $country_code=='IT' || $country_code=='ES' || $country_code=='PL' || $country_code=='NL' || $country_code=='PT'){
        continue;
    }
    $countriesArrayNew[$country_code] = $countriesArray[$country_code];
}

foreach ($countriesArrayNew as $country_code => $country){
    $countryName = ucfirst(strtolower($country));
    if(strpos($countryName,'bania')!==false){// have to be corrected because of the start of the csv file
        $countryName = 'Albania';
    }
    if(strpos($countryName,' ')!==false){
        $countryNameExploded = explode(' ',$countryName);
        $countryName = '';
        foreach ($countryNameExploded as $countryNameExplodedName){
            if(!$countryName){
                $countryName .= ucfirst(strtolower($countryNameExplodedName)).' ';
            }else{
                $countryName .= ' '.ucfirst(strtolower($countryNameExplodedName));
            }
        }
    }
    $countriesArrayNew[$country_code] = $countryName;
	if($countryName=='Usa'){
		$countryName = 'USA';
	}
    echo "<option value='".$country_code."' >".$countryName."</option>";
}

$InvoiceNoteDefault = (!empty($InvoiceNote['default']) ? $InvoiceNote['default'] : '' );
echo <<<HEREDOC
                    </select>
                    </div>
                </div>
                <div class='cg_view_option cg_view_option_flex_flow_column cg_view_option_50_percent cg_border_top_none cg_border_border_bottom_left_radius_unset cg_border_left_none cg_border_border_bottom_right_radius_8_px' id="InvoiceNoteContainer" >
                    <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p>InvoiceNote<br><span class="cg_view_option_title_note"><b>NOTE:</b> if empty for a country then default will be taken<br>No tags allowed<br>Appears at the bottom of an invoice</span></p>
                    </div>
                    <div class='cg_view_option_input'>
<textarea name="InvoiceNote[default]" class="InvoiceNote" data-cg-country="default" maxlength="1000" rows="5" placeholder="Place your default note" >$InvoiceNoteDefault</textarea>
HEREDOC;
foreach ($countriesArrayNew as $country_code => $countryName){
    if($country_code=='default'){
        continue;
    }
    echo '<textarea name="InvoiceNote['.$country_code.']" class="InvoiceNote cg_hide" data-cg-country="'.$country_code.'" 
    maxlength="1000" placeholder="Place your note for country '.$countryName.'" >'.(!empty($InvoiceNote[$country_code]) ? cg_stripslashes_recursively($InvoiceNote[$country_code]) : '' ).'</textarea>';
}
echo <<<HEREDOC
                    </div>
                </div>
         </div>
    </div>
HEREDOC;

echo "</div>";// $cgProFalse close


echo <<<HEREDOC
        <div class='cg_view_options_row cg_hide' id="EcommerceInvoiceOptionsStartButton" >
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none ' >
                    <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 10px;">
                        <p>Invoice options, templates and examples<br><span class="cg_view_option_title_note">Click here for more</span></p>
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row cg_hide' id="EcommerceInvoiceOptionsEnvironment" >
                <div class='cg_view_option cg_view_option_50_percent cg_border_right_none  cg_border_top_none'  id="EcommerceInvoiceOptionsLiveStartButton" data-cg-ecommerce-environment="live" >
                    <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: -5px;">
                        <p>Live</p>
                    </div>
                </div>
                <div class='cg_view_option cg_view_option_50_percent cg_border_top_none' id="EcommerceInvoiceOptionsSandboxStartButton" data-cg-ecommerce-environment="sandbox">
                    <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: -5px;">
                        <p>Sandbox</p>
                    </div>
                </div>
        </div>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_row cg_hide'  id="EcommerceInvoiceBackToSelectionEnvironment">
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                    <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 10px;">
                        <p class="cg_back_to_selection" ><span class="cg_back_to_selection_icon" ></span><span class="cg_back_to_selection_text" >Back to environment selection</span></p>
                    </div>
                </div>
        </div>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_row cg_hide'  id="EcommerceInvoiceBackToSelectionOptionsLive">
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                    <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 10px;">
                        <p class="cg_back_to_selection" ><span class="cg_back_to_selection_icon" ></span><span class="cg_back_to_selection_text" >Back to live options selection</span></p>
                    </div>
                </div>
        </div>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_row cg_hide'  id="EcommerceInvoiceBackToSelectionOptionsSandbox">
                <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                    <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 10px;">
                        <p class="cg_back_to_selection" ><span class="cg_back_to_selection_icon" ></span><span class="cg_back_to_selection_text" >Back to sandbox options selection</span></p>
                    </div>
                </div>
        </div>
HEREDOC;

echo <<<HEREDOC
        <div class="cg_hide" id="EcommerceInvoiceOptionsSandbox" style="margin:0;padding:0;" >
            <div class='cg_view_options_row' >
                    <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_invoicing_type'  data-cg-invoicing-type="invoice-new-test" >
                        <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 5px;">
                            <p>Create a test invoice based on data</p><br>
                            <span class="cg_view_option_title_note">Always your default template will be used for invoicing</span>
                        </div>
                    </div>
            </div>
<!--            <div class='cg_view_options_row' >
                    <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_invoicing_type'  data-cg-invoicing-type="invoice-generate-number" >
                        <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 5px;">
                            <p>Test new invoice number based on current select</p>
                        </div>
                    </div>
            </div>-->
            <div class='cg_view_options_row' >
                    <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_invoicing_type'  data-cg-invoicing-type="invoice-list" >
                        <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 5px;">
                            <p>Invoices List</p>
                        </div>
                    </div>
            </div>
            <div class='cg_view_options_row' >
                    <div class='cg_view_option  cg_view_option_full_width  cg_border_top_none' id="EcommerceInvoiceTransactionsListButton">
                        <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 5px;">
                            <p>Transactions List</p>
                        </div>
                    </div>
            </div>
            <div class='cg_view_options_row' >
                    <div class='cg_view_option  cg_view_option_full_width  cg_border_top_none cg_invoicing_type' data-cg-invoicing-type="template-list" >
                        <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 5px;">
                            <p>Invoices Templates List<br><span class="cg_view_option_title_note">Always your default template will be used for invoicing</span></p>
                        </div>
                    </div>
            </div>
        </div>
HEREDOC;


echo <<<HEREDOC
    <div id="EcommerceInvoiceTransactionsListSelectContainer" class="cg_hide">
        <div class='cg_view_options_row' >
                <div class='cg_view_option  cg_view_option_full_width  cg_border_top_none cg_border_bottom_none' >
                    <div class='cg_view_option_title cg_view_option_title_full_width' style="margin-top: 5px;">
                        <p>Transactions List - select year and month<br><span class="cg_view_option_title_note">Only max 31 days can displayed by Ecommerce at once</span></p>
                    </div>
                </div>
        </div>
        <div class='cg_view_options_row' style="margin-top: -20px;">
            <div class="cg_view_option cg_border_top_none cg_view_option_50_percent cg_border_right_none cg_view_option_flex_flow_column ">
                        <div style="margin-right: -290px;">
                <div class="cg_view_option_title cg_view_option_title_full_width">
                    <p>Select year</p>
                </div>
                <div class="cg_view_option_select">
                    <select id='EcommerceInvoiceTransactionsListYear'>
                            <option value='2010' >2010</option>
                            <option value='2011' >2011</option>
                            <option value='2012' >2012</option>
                            <option value='2013' >2013</option>
                            <option value='2014' >2014</option>
                            <option value='2015' >2015</option>
                            <option value='2016' >2016</option>
                            <option value='2017' >2017</option>
                            <option value='2018' >2018</option>
                            <option value='2019' >2019</option>
                            <option value='2020' >2020</option>
                            <option value='2021' >2021</option>
HEREDOC;
if(date('Y')>=2022){$selected='';if(date('Y')==2022){$selected='selected';} echo "<option value='2022' $selected >2022</option>";}
if(date('Y')>=2023){$selected='';if(date('Y')==2023){$selected='selected';} echo "<option value='2023' $selected>2023</option>";}
if(date('Y')>=2024){$selected='';if(date('Y')==2024){$selected='selected';} echo "<option value='2024' $selected>2024</option>";}
if(date('Y')>=2025){$selected='';if(date('Y')==2025){$selected='selected';} echo "<option value='2025' $selected>2025</option>";}
if(date('Y')>=2026){$selected='';if(date('Y')==2026){$selected='selected';} echo "<option value='2026' $selected>2026</option>";}
if(date('Y')>=2027){$selected='';if(date('Y')==2027){$selected='selected';} echo "<option value='2027' $selected>2027</option>";}
if(date('Y')>=2028){$selected='';if(date('Y')==2028){$selected='selected';} echo "<option value='2028' $selected>2028</option>";}
if(date('Y')>=2029){$selected='';if(date('Y')==2029){$selected='selected';} echo "<option value='2029' $selected>2029</option>";}
if(date('Y')>=2030){$selected='';if(date('Y')==2030){$selected='selected';} echo "<option value='2030' $selected>2030</option>";}
if(date('Y')>=2031){$selected='';if(date('Y')==2031){$selected='selected';} echo "<option value='2031' $selected>2031</option>";}
if(date('Y')>=2032){$selected='';if(date('Y')==2032){$selected='selected';} echo "<option value='2032' $selected>2032</option>";}
if(date('Y')>=2033){$selected='';if(date('Y')==2033){$selected='selected';} echo "<option value='2033' $selected>2033</option>";}
if(date('Y')>=2034){$selected='';if(date('Y')==2034){$selected='selected';} echo "<option value='2034' $selected>2034</option>";}
if(date('Y')>=2035){$selected='';if(date('Y')==2035){$selected='selected';} echo "<option value='2035' $selected>2035</option>";}
if(date('Y')>=2036){$selected='';if(date('Y')==2036){$selected='selected';} echo "<option value='2036' $selected>2036</option>";}
if(date('Y')>=2037){$selected='';if(date('Y')==2037){$selected='selected';} echo "<option value='2037' $selected>2037</option>";}
if(date('Y')>=2038){$selected='';if(date('Y')==2038){$selected='selected';} echo "<option value='2038' $selected>2038</option>";}
if(date('Y')>=2039){$selected='';if(date('Y')==2039){$selected='selected';} echo "<option value='2039' $selected>2039</option>";}
if(date('Y')>=2040){$selected='';if(date('Y')==2040){$selected='selected';} echo "<option value='2040' $selected>2040</option>";}
echo <<<HEREDOC
</select>
                </div>
                </div>
            </div>
            <div class="cg_view_option cg_border_top_none cg_border_left_none cg_view_option_50_percent cg_view_option_flex_flow_column ">
            <div style="margin-left: -50px;width: 200px;">
                <div class="cg_view_option_title cg_view_option_title_full_width">
                    <p>Select month</p>
                </div>
                <div class="cg_view_option_select">
                    <select id='EcommerceInvoiceTransactionsListMonth' >
HEREDOC;
$selected='';if(date('n')==1){$selected='selected';} echo "<option value='1' $selected>01 (January)</option>";
$selected='';if(date('n')==2){$selected='selected';} echo "<option value='2' $selected>02 (February)</option>";
$selected='';if(date('n')==3){$selected='selected';} echo "<option value='3' $selected>03 (March)</option>";
$selected='';if(date('n')==4){$selected='selected';} echo "<option value='4' $selected>04 (April)</option>";
$selected='';if(date('n')==5){$selected='selected';} echo "<option value='5' $selected>05 (May)</option>";
$selected='';if(date('n')==6){$selected='selected';} echo "<option value='6' $selected>06 (June)</option>";
$selected='';if(date('n')==7){$selected='selected';} echo "<option value='7' $selected>07 (July)</option>";
$selected='';if(date('n')==8){$selected='selected';} echo "<option value='8' $selected>08 (August)</option>";
$selected='';if(date('n')==9){$selected='selected';} echo "<option value='9' $selected>09 (September)</option>";
$selected='';if(date('n')==10){$selected='selected';} echo "<option value='10' $selected>10 (October)</option>";
$selected='';if(date('n')==11){$selected='selected';} echo "<option value='11' $selected>11 (November)</option>";
$selected='';if(date('n')==12){$selected='selected';} echo "<option value='12' $selected>12 (December)</option>";
echo <<<HEREDOC
                    </select>
                </div>
            </div>
            </div>
        </div>
   </div>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_row cg_hide' id="EcommerceInvoiceOptionsLoader">
                <div class='cg_view_option  cg_view_option_full_width  cg_border_top_none'  >
                    <div class='cg_view_option_title cg_view_option_title_full_width' >
                        <p>
                             <div class="cg-lds-dual-ring-div-gallery-hide">
                                <div class="cg-lds-dual-ring-gallery-hide">
                                </div>
                            </div>
                        </p>
                    </div>
                </div>
        </div>
HEREDOC;

echo <<<HEREDOC
        <div id="EcommerceInvoiceResultContainer" class="cg_hide">
        </div>
HEREDOC;

echo <<<HEREDOC
</div>
</div>
HEREDOC;
