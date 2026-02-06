<?php

if($LoginAfterConfirm && empty($isAfterLogin)){
    wp_set_auth_cookie( $newWpId,true );
    ?>
    <script defer>
        var isWhite = <?php echo json_encode($isWhite);?>;
        var isBorderRadius = <?php echo json_encode($isBorderRadius);?>;
        debugger

        var cg1lCheckInterval = setInterval(function () {
            if (cgJsClass &&
                cgJsClass.gallery &&
                cgJsClass.gallery.vars &&
                cgJsClass.gallery.vars.cgMessagesContainer) {
                debugger
                clearInterval(cg1lCheckInterval);
                cgJsClass.gallery.function.message.show(undefined, undefined,undefined,undefined,undefined,undefined,
                    true,undefined,undefined,undefined,isBorderRadius,isWhite);
                setTimeout(function (){
                    debugger
                    location.href = location.origin + location.pathname + '?cg1l_reload_after_login=' + Date.now();
                },1000);
            }
        }, 50); // check every 50ms (fast but safe)

    </script>
    <?php
}else{
    echo "<div id='cg_activation'  class='mainCGdivUploadForm mainCGdivUploadFormStatic $FeControlsStyleRegistry $BorderRadiusRegistry'>";

    if(!empty($pro_options)){
        echo "<p>";
        echo html_entity_decode(stripslashes(nl2br($pro_options->TextAfterEmailConfirmation)));
        echo "</p>";

    }else{ // Fallback text if gallery was deleted

        echo "<p>Thank you for your registration. <br>You are now able to log in.</p>";

    }

    echo "</div>";

    ?>

    <script defer>

        setTimeout(function (){
            jQuery("html, body").animate({ scrollTop: jQuery('#cg_activation').offset().top-60}, 0);
        },100);

    </script>

    <?php

}