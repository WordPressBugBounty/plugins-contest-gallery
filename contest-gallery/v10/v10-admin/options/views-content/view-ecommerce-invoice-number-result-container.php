<?php

$cg_disabled_cause_timestamp_test = '';
$cg_disabled_cause_timestamp_live = '';
if($InvoiceNumberLogicResultArrayTest['selected']=='timestamp'){
	$cg_disabled_cause_timestamp_test = 'cg_disabled';
}
if($InvoiceNumberLogicResultArrayLive['selected']=='timestamp'){
	$cg_disabled_cause_timestamp_live = 'cg_disabled';
}

$cg_disabled_one = '';
if($CreateAndSetInvoiceNumber!='checked'){
	$cg_disabled_one = 'cg_disabled_one';
}

echo "<div id='cgInvoiceNumberLogicAndResultContainer' class='$cg_disabled_one $cg_invoice_disabled_one'>";

echo <<<HEREDOC
  <div class='cg_view_options_row'>
        <div  class='cg_view_option cg_view_option_50_percent cg_border_top_right_bottom_none cg_justify_content_center cg_border_radius_unset'>
            <div class='cg_view_option_title cg_flex_flow_column '>
                <p><span style="display: inline-block;margin: 10px auto 15px auto;font-size:18px;">Invoice number logic "Test Environment"</span></p>
                 <p style="margin-top:-10px;margin-bottom:20px;">Previous invoice number: $lastInvoiceNumberTest</p>
            </div>
        </div>
         <div  class='cg_view_option cg_view_option_50_percent cg_border_top_bottom_none cg_border_border_bottom_left_radius_unset cg_border_border_bottom_right_radius_unset cg_justify_content_center'>
                <div class='cg_view_option_title cg_flex_flow_column '>
                    <p><span style="display: inline-block;margin: 10px auto 15px auto;font-size:18px;">Invoice number logic "Live Environment"</span></p>
                 <p style="margin-top:-10px;margin-bottom:20px;">Previous invoice number: $lastInvoiceNumberLive</p>
                </div>
        </div>
</div>
HEREDOC;

echo <<<HEREDOC
            <div class='cg_view_options_row'>
                <div class='cg_view_option cg_view_option_flex_flow_column cg_view_option_25_percent cg_border_top_none  cg_border_top_right_none cg_border_bottom_none' >
                    <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p>Logic
                             <br><span class="cg_view_option_title_note cg_ecommerce_option_logic_select_note" >&nbsp;<br><br></span>
                         </p>
                    </div>
                    <div class='cg_view_option_select'>
                            <select id="InvoiceNumberLogicSelectTest" name="InvoiceNumberLogicSelectTest" class="cg_ecommerce_option_logic_select_new" style="width: 100%;"  >
$InvoiceNumberLogicSelectOptionsTest
                            </select>
                    </div>
                </div>
                <div class='cg_view_option cg_border_right_none cg_view_option_25_percent  cg_view_option_flex_flow_column cg_border_top_none cg_border_bottom_none cg_border_left_none cg_view_option_ecommerce_invoice_number_logic_own_prefix $cg_disabled_cause_timestamp_test'   >
                     <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p style="margin-bottom: 5px;">Own prefix<br><span class="cg_view_option_title_note cg_ecommerce_option_logic_select_note" >Select own prefix above to add own prefix</span></p>
                    </div>
                    <div class='cg_view_option_input cg_view_option_input_full_width'>
                        <input type="text" id="InvoiceNumberLogicOwnPrefixTest" name="InvoiceNumberLogicOwnPrefixTest" value="$InvoiceNumberLogicOwnPrefixTest"  class="cg_ecommerce_option_logic_own_prefix" maxlength="20" >
                    </div>
                </div>
                <div class='cg_view_option cg_view_option_flex_flow_column cg_view_option_25_percent cg_border_top_none  cg_border_top_right_none cg_border_bottom_none cg_border_right_none' >
                    <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p>Logic
                             <br><span class="cg_view_option_title_note cg_ecommerce_option_logic_select_note" >&nbsp;<br><br></span>
                         </p>
                    </div>
                    <div class='cg_view_option_select'>
                            <select id="InvoiceNumberLogicSelectLive" name="InvoiceNumberLogicSelectLive" class="cg_ecommerce_option_logic_select_new" style="width: 100%;"  >
$InvoiceNumberLogicSelectOptionsLive
                            </select>
                    </div>
                </div>
                <div class=' cg_border_left_none cg_view_option cg_view_option_25_percent  cg_view_option_flex_flow_column cg_border_top_none cg_border_bottom_none  cg_view_option_ecommerce_invoice_number_logic_own_prefix $cg_disabled_cause_timestamp_live'   >
                     <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p style="margin-bottom: 5px;">Own prefix<br><span class="cg_view_option_title_note cg_ecommerce_option_logic_select_note" >Select own prefix above to add own prefix</span></p>
                    </div>
                    <div class='cg_view_option_input cg_view_option_input_full_width'>
                        <input type="text" id="InvoiceNumberLogicOwnPrefixLive" name="InvoiceNumberLogicOwnPrefixLive" value="$InvoiceNumberLogicOwnPrefixLive"  class="cg_ecommerce_option_logic_own_prefix" maxlength="20" >
                    </div>
                </div>
            </div>
HEREDOC;

echo <<<HEREDOC
 			<div class='cg_view_options_row' >
                <div class='cg_view_option  cg_view_option_flex_flow_column cg_view_option_50_percent cg_border_top_none cg_border_top_none  cg_border_bottom_none cg_border_right_none  $cg_disabled_cause_timestamp_test $cg_disabled_override_custom_number_test' id="InvoiceNumberLogicCustomNumberOptionTest" >
                    <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p>Custom number to start with<br><span class="cg_view_option_title_note cg_ecommerce_option_logic_select_note" >Will be increased automatically after every sale<br>Will be reseted back to the configured number every year or month$cg_originally_configurated_custom_number_test $cg_custom_number_generated_note_test</span></p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text"  id="InvoiceNumberLogicCustomNumberTest" name="InvoiceNumberLogicCustomNumberTest"  value="$InvoiceNumberLogicCustomNumberTest" $cg_disabled_override_custom_number_input_test  maxlength="10"  >
                        <input type="hidden"  id="InvoiceNumberLogicCustomNumberPreviousTest"  value="$InvoiceNumberLogicCustomNumberPreviousIncreasedTest"  >
                        <input type="hidden"  id="InvoiceNumberLogicCustomNumberIsGeneratedTest"  value="$InvoiceNumberLogicCustomNumberIsGeneratedTest"  >
                    </div>
                </div>
                <div class='cg_view_option cg_view_option_flex_flow_column cg_view_option_50_percent cg_border_top_none cg_border_top_none cg_border_bottom_none  $cg_disabled_cause_timestamp_live $cg_disabled_override_custom_number_live'  id="InvoiceNumberLogicCustomNumberOptionLive">
                    <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p>Custom number to start with<br><span class="cg_view_option_title_note cg_ecommerce_option_logic_select_note" >Will be increased automatically after every sale<br>Will be reseted back to the configured number every year or month $cg_originally_configurated_custom_number_live $cg_custom_number_generated_note_live</span></p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text"  id="InvoiceNumberLogicCustomNumberLive" name="InvoiceNumberLogicCustomNumberLive"  value="$InvoiceNumberLogicCustomNumberLive" $cg_disabled_override_custom_number_input_live maxlength="10"    >
                        <input type="hidden"  id="InvoiceNumberLogicCustomNumberPreviousLive"  value="$InvoiceNumberLogicCustomNumberPreviousIncreasedLive"  >
                        <input type="hidden"  id="InvoiceNumberLogicCustomNumberIsGeneratedLive"  value="$InvoiceNumberLogicCustomNumberIsGeneratedLive"  >
                    </div>
                </div>
            </div>
            <div class='cg_view_options_row'>
                  <div class='cg_view_option cg_view_option_50_percent cg_view_option_flex_flow_column cg_border_top_none cg_border_bottom_none  cg_view_option_ecommerce_invoice_logic_part_prefix'  >
                     <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p>Result number next invoice<br><span class="cg_view_option_title_note cg_ecommerce_option_logic_select_note" ><b>NOTE:</b> if <b>timestamp</b> then future timestamp will be shown<br><b>NOTE:</b> if <b>timestamp</b> your server PHP timestamp will be set, it might be different as your JavaScript browser timestamp</span></p>
                    </div>
                    <div class='cg_view_option_input cg_view_option_input_full_width'>
                            <input type="text" readonly id="InvoiceNumberLogicResultTest" value="$InvoiceNumberLogicResultTest"  maxlength="1000" style="border-bottom: none;text-align:center;max-width:600px;cursor:auto;" >
                    </div>
                </div>
                <div class=' cg_view_option cg_border_left_none cg_view_option_50_percent cg_view_option_flex_flow_column cg_border_top_none cg_border_bottom_none  cg_view_option_ecommerce_invoice_logic_part_prefix'  >
                     <div class='cg_view_option_title cg_view_option_title_full_width'>
                        <p>Result number next invoice<br><span class="cg_view_option_title_note cg_ecommerce_option_logic_select_note" ><b>NOTE:</b> if <b>timestamp</b> then future timestamp will be shown<br><b>NOTE:</b> if <b>timestamp</b> your server PHP timestamp will be set, it might be different as your JavaScript browser timestamp</span></p>
                    </div>
                    <div class='cg_view_option_input cg_view_option_input_full_width'>
                            <input type="text" readonly id="InvoiceNumberLogicResultLive" value="$InvoiceNumberLogicResultLive"  maxlength="1000" style="border-bottom: none;text-align:center;max-width:600px;cursor:auto;" >
                    </div>
                </div>
            </div>
            <div class='cg_view_options_row'>
                <div  class='cg_view_option cg_view_option_50_percent cg_border_top_none $cg_disabled_override_custom_number_reset_test $cg_disabled_cause_timestamp_test' id="ResetCustomNumberNextInvoiceOptionTest">
                    <div class='cg_view_option_title'>
                        <p>Reset current custom number logic<br>and start with new custom number<br>"Test Environment"
                        <br><span class="cg_view_option_title_note">Next invoice starts with custom number<br>from configuration above if you reset.</span></p>
                    </div>
                    <div  class='cg_view_option_checkbox'>
                        <input type="checkbox" name="ResetCustomNumberNextInvoiceTest" id="ResetCustomNumberNextInvoiceTest" $ResetCustomNumberNextInvoiceTest><br/>
                    </div>
                </div>
                 <div  class='cg_view_option cg_view_option_50_percent cg_border_top_none  cg_border_left_none cg_border_border_bottom_left_radius_unset cg_border_border_bottom_right_radius_unset  $cg_disabled_override_custom_number_reset_live $cg_disabled_cause_timestamp_live' id="ResetCustomNumberNextInvoiceOptionLive">
                        <div class='cg_view_option_title'>
                        <p>Reset current custom number logic<br>and start with new custom number<br>"Live Environment"
                        <br><span class="cg_view_option_title_note">Next invoice starts with custom number<br>from configuration above if you reset.</span></p>
                        </div>
                        <div  class='cg_view_option_checkbox'>
                        <input type="checkbox" name="ResetCustomNumberNextInvoiceLive" id="ResetCustomNumberNextInvoiceLive"  $ResetCustomNumberNextInvoiceLive><br/>
                        </div>
                    </div>
            </div>
HEREDOC;
echo "</div>";
