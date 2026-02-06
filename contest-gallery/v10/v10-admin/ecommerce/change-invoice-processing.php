<?php

global $wpdb;
$tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
$OrderId = sanitize_text_field($_POST['cg_order_id_hash']);
$Order = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE OrderIdHash = '$OrderId' LIMIT 1");

$wp_upload_dir = wp_upload_dir();
$InvoiceFilePath = $Order->InvoiceFilePath;
$InvoicePartMain = contest_gal1ery_convert_for_html_output_without_nl2br($Order->InvoicePartMain);
$invoiceFilePath = str_replace('WP_UPLOAD_DIR',$wp_upload_dir['basedir'],$InvoiceFilePath);

$InvoicePartNumber = contest_gal1ery_convert_for_html_output_without_nl2br(sanitize_text_field($_POST['InvoicePartNumber']));
$InvoiceNumber = $InvoicePartNumber;
$InvoicePartInvoicer = contest_gal1ery_convert_for_html_output_without_nl2br($_POST['InvoicePartInvoicer']);
$InvoicePartRecipient = contest_gal1ery_convert_for_html_output_without_nl2br($_POST['InvoicePartRecipient']);
$InvoicePartInfo = contest_gal1ery_convert_for_html_output_without_nl2br($_POST['InvoicePartInfo']);
$InvoicePartNote = contest_gal1ery_convert_for_html_output_without_nl2br($_POST['InvoicePartNote']);

include(__DIR__ ."/../../../check-language-ecommerce.php");

$language_Nr_Part = $language_Nr.':';
if(empty($InvoiceNumber)){
	$language_Nr_Part = '';
}

$html = '';
include(__DIR__.'/../../../v10/v10-frontend/ecommerce/invoice-parts/top.php');

$html .= $InvoicePartMain;

if(!empty($InvoicePartNote)){
	include(__DIR__.'/../../../v10/v10-frontend/ecommerce/invoice-parts/bottom.php');
}

require_once(__DIR__.'/../../../v10/v10-admin/ecommerce/libs/tcpdf/tcpdf.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdfAuthor = get_option('blogname');

// Dokumenteninformationen
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($pdfAuthor);
$pdf->SetTitle($InvoicePartNumber);
$pdf->SetSubject($InvoicePartNumber);

// Header und Footer Informationen
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Auswahl des Font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Auswahl der MArgins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Automatisches Autobreak der Seiten
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Image Scale
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Schriftart
$pdf->SetFont('dejavusans', '', 10);

// Neue Seite
$pdf->AddPage();

// Fügt den HTML Code in das PDF Dokument ein
$pdf->writeHTML($html, true, false, true, false, '');

//Ausgabe der PDF
//Variante 1: PDF direkt an den Benutzer senden:
$pdf->Output($invoiceFilePath, 'F');// Mit I nur output!!!!

$InvoiceEdited = cg_get_time_based_on_wp_timezone_conf(time(),'Y-m-d H:i:s');

// update main table
$wpdb->update(
	"$tablename_ecommerce_orders",
	array('InvoiceNumberChanged' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoiceNumber)),
	      'InvoicePartNumber' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartNumber)),'InvoicePartInvoicer' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartInvoicer)),'InvoicePartRecipient' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartRecipient)),'InvoicePartInfo' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartInfo)),'InvoicePartNote' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartNote)),
	'InvoiceEdited' => $InvoiceEdited
	),
	array('id' => $Order->id),
	array(
		'%s',
		'%s','%s','%s','%s','%s',
		'%s'
	),
	array('%d')
);

$wpdb->show_errors(); //setting the Show or Display errors option to true
var_dump('$wpdb-> ORDER print_error();');
var_dump($wpdb->print_error());