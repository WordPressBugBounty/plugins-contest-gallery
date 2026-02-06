<?php

$html = '
<table cellpadding="5" cellspacing="0" style="width: 100%; ">
 <tr>
 <td colspan="2" style="text-align: left;font-size:24px;">
'.$language_Invoice.'<br>
<span style="text-align: left;font-size:11px;">'.$language_Nr_Part.' '.$InvoicePartNumber.'</span>
</td>
    <td style="text-align: left;font-size:11px;">
'.$InvoicePartInvoicer.'
 </td>
 </tr>
</table>
<br>
<table cellpadding="5" cellspacing="0" style="width: 100%; ">
 <tr>
 <td>
'.$InvoicePartRecipient.'
<br><br>
'.$InvoicePartInfo.'
</td>
 </tr>
</table>
<br><br>';

?>