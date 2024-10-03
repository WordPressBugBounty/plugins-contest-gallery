<?php

$OrderId = absint($_GET['cg_order']);
$GalleryID = absint($_GET['option_id']);

global $wpdb;

require_once(dirname(__FILE__) . "/../nav-menu.php");

$tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";

$Order = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id = '$OrderId' LIMIT 1");

function cg_change_invoice_br2nl($string)
{
	$return =  preg_replace('/\<br(\s*)?\/?\>/i', "", $string);
	//$return = preg_replace('/\<br\>/gi', "", $return);
	//return preg_replace('/\<br \>/gi', "", $return);
	return $return;
}

function cg_change_invoice_br2nl_alt($string)
{
	return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

$InvoiceNumberOriginal = $Order->InvoiceNumber;

if(!empty($Order->InvoiceNumberChanged)){
	$InvoicePartNumberToShow = cg_change_invoice_br2nl_alt(contest_gal1ery_convert_for_html_output_without_nl2br($Order->InvoiceNumberChanged));
}else{
	$InvoicePartNumberToShow = cg_change_invoice_br2nl_alt(contest_gal1ery_convert_for_html_output_without_nl2br($Order->InvoicePartNumber));
}

$InvoicePartInvoicer = cg_change_invoice_br2nl_alt(contest_gal1ery_convert_for_html_output_without_nl2br($Order->InvoicePartInvoicer));
$InvoicePartRecipient =cg_change_invoice_br2nl_alt( contest_gal1ery_convert_for_html_output($Order->InvoicePartRecipient));
$InvoicePartInfo = cg_change_invoice_br2nl_alt(contest_gal1ery_convert_for_html_output_without_nl2br($Order->InvoicePartInfo));
$InvoicePartMain = contest_gal1ery_convert_for_html_output_without_nl2br($Order->InvoicePartMain);
$InvoicePartNote = cg_change_invoice_br2nl_alt(contest_gal1ery_convert_for_html_output_without_nl2br($Order->InvoicePartNote));
$OrderIdHash = $Order->OrderIdHash;

include(__DIR__ ."/../../../check-language-ecommerce.php");

$cg_version = cg_get_version();
$site_url = get_site_url();

echo <<<HEREDOC
<div id="cgChangeInvoiceLoader"  class="cg_backend_action_container cg_hide cg_overflow_y_hidden" style="max-width: 300px;max-height: 300px;min-height: 268px;">
<div  class=""><div class="cg-lds-dual-ring-gallery-hide" style="margin: 0;padding-top: 80px;max-height: 180px;"></div></div></div>
HEREDOC;

/*
echo <<<HEREDOC
<div id="cgChangeInvoiceDone"  class="cg_backend_action_container cg_hide cg_overflow_y_hidden" style="max-width: 300px;max-height: 300px;min-height: 268px;">
    <span class='cg_message_close'></span>
    <div style='margin-top: 13px;font-weight: bold;'>
        Invoice changed
    </div>
</div>
HEREDOC;*/

echo <<<HEREDOC
<div class="cg_container" id="cgChangeInvoice">
<form id="cgChangeInvoiceForm" action="?page=$cg_version/index.php" method='POST'>
<input type="hidden" name="action" value="post_cg_change_invoice">
<input type="hidden" name="cg_order_id_hash" value="$OrderIdHash">
<div class="cg_row">
	<div class="cg_col_12" style="text-align: center;" >
			<span style="font-size: 22px; margin-bottom: 10px; display: inline-block;">Edit invoice</span><br>
			<span class="cg_color_red"><b>NOTE:</b></span> edit invoice modules, the changes will overwrite the current invoice
	</div>
</div>
<div class="cg_row">
	<div class="cg_col_6">
	<span style="font-size: 22px;" class="cg_hide" >$language_Invoice<br></span>
	<b>Invoice number when created:</b> $InvoiceNumberOriginal<br>
	<input type="text" class="cg_edit" name="InvoicePartNumber" value="$InvoicePartNumberToShow" />
	<div class="cg_edit_button" style="bottom: unset;top: 38px;">Edit</div>
	<span class="cg_color_red"><b>NOTE:</b></span> changing invoice number of pdf<br><span class="cg_color_red cg_visibility_hidden"><b>NOTE:</b></span> will not overwrite the configured<br><span class="cg_color_red cg_visibility_hidden"><b>NOTE:</b></span> invoice number logic
	</div>
	<div class="cg_col_6">
	<textarea class="cg_edit" name="InvoicePartInvoicer"  rows="8" >$InvoicePartInvoicer</textarea>
	<div class="cg_edit_button">Edit</div>

	</div>
</div>
<div class="cg_row">
	<div class="cg_col_6">
		<textarea class="cg_edit" name="InvoicePartRecipient"  rows="7" >$InvoicePartRecipient</textarea>
		<div class="cg_edit_button">Edit</div>
	</div>
	<div class="cg_col_6">
	</div>
</div>
<div class="cg_row">
	<div class="cg_col_6">
	<textarea class="cg_edit" name="InvoicePartInfo"  rows="5" >$InvoicePartInfo</textarea>
	<div class="cg_edit_button">Edit</div>
	</div>
	<div class="cg_col_6">
	</div>
</div>
<div class="cg_row">
	<div class="cg_col_12" style="margin-top: 15px;margin-bottom: 10px;">
		$InvoicePartMain
		<div style="position: absolute;bottom: 50px; left: 70px;"><span><b>NOTE:</b></span> this module is not changeable</div>
	</div>
</div>
<div class="cg_row">
	<div class="cg_col_12">
		<textarea class="cg_edit" name="InvoicePartNote"   rows="5" >$InvoicePartNote</textarea>
		<div class="cg_edit_button">Edit</div>
	</div>
</div>
<div class="cg_row" >
	<div class="cg_col_12" >
		<input id="cgChangeInvoiceButton"  class="cg_backend_button_gallery_action" value="Save all edit changes" style="float:right;font-weight: bold;margin-top: 15px;font-size: 18px;width: unset;"   />
	</div>
</div>
</form>
	<div class="cg_row">
		<div class="cg_col_12" style="text-align: center;" >
			<hr style="margin-top: 15px; margin-bottom: 5px;">
			<a id="cgChangeInvoiceDownloadInvoiceButton" class="cg_action_button" target="_blank" href="$site_url?cg_download_invoice_order_id_hash=$OrderIdHash" >Download invoice</a>
		</div>
	</div>
</div>
HEREDOC;
