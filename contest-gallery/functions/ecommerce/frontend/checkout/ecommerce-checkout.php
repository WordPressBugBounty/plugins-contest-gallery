<?php
if(!defined('ABSPATH')){exit;}

$_POST = cg1l_sanitize_post($_POST);

$isSandbox = true;

echo "<div id='cgPayPalCheckoutBody'>";
echo "<div id='cgPayPalButtonsContainer'></div>";

if($isSandbox){

    /*    $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION , 6); //NEW ADDITION
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $clientId.":".$clientSecret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $result = curl_exec($ch);
        $result = json_decode($result,true);
        $accessToken = $result['access_token'];*/

    ?>
    <!-- Replace "test" with your own sandbox Business account app client ID -->
    <script>

        // cache true has to be set so no timestamp is added to url from jquery otherwise such error:
       // throw new Error("SDK Validation error: 'Disallowed query param: _'" );
        /* Original Error:
        Disallowed query param: _ (debug id: f793441158484d9e843)
        */

        var isSandbox = <?php echo json_encode($isSandbox);?>;
        var clientId = cgJsClass.gallery.vars.PayPalOptions.ClientIdLive;
        var currencyShort = cgJsClass.gallery.vars.PayPalOptions.CurrencyShort;

        if(isSandbox){
            clientId = cgJsClass.gallery.vars.PayPalOptions.ClientIdSandbox;
        }

        var url = "https://www.paypal.com/sdk/js?client-id="+clientId+"&currency="+currencyShort+"&disable-funding=sepa,credit";
        console.log(url);

        jQuery(document).ready(function ($) {
            $.ajax({
                url: url,
                method: 'get',
                cache: false,
                dataType: "script",
            }).done(function(response) {

                var result = cgJsClass.gallery.paypal.functions.preProcessing();

                if(!result){
                    return;
                }

                cgJsClass.gallery.paypal.functions.processing(paypal,result['purchase_units'],isSandbox,result['realIdsForPayPalSale']);

            }).fail(function(xhr, status, error) {

                debugger

            }).always(function() {

            });

            return;

            });

    </script>
    <?php
}
?>