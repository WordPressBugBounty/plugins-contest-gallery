<?php
/**###NORMAL###**/
if(!function_exists('cg_update_to_pro_one_star')){
    function cg_update_to_pro_one_star($galeryIDuser,$pictureID,$ratingFileData,$message){

        ?>
        <script data-cg-processing="true">

            var message = <?php echo json_encode($message);?>;
            var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
            var pictureID = <?php echo json_encode($pictureID);?>;
            var ratingFileData = <?php echo json_encode($ratingFileData);?>;

            cgJsClass.gallery.rating.setRatingOneStar(pictureID,0,false,galeryIDuser,false,false,ratingFileData);
            cgJsClass.gallery.function.message.show(galeryIDuser,message);

        </script>
        <?php


    }
}
if(!function_exists('cg_update_to_pro_multiple_stars')){
    function cg_update_to_pro_multiple_stars($galeryIDuser,$pictureID,$ratingFileData,$isFromSingleView,$message){

        ?>
        <script data-cg-processing="true">

            var message = <?php echo json_encode($message);?>;
            var galeryIDuser = <?php echo json_encode($galeryIDuser);?>;
            var pictureID = <?php echo json_encode($pictureID);?>;
            var ratingFileData = <?php echo json_encode($ratingFileData);?>;
            var isFromSingleView = <?php echo json_encode($isFromSingleView);?>;

            cgJsClass.gallery.rating.setRatingMultiStarByMode(pictureID,0,0,false,galeryIDuser,false,false,ratingFileData,isFromSingleView);
            cgJsClass.gallery.function.message.show(galeryIDuser,message);

        </script>
        <?php

    }
}
/**###NORMAL---END###**/
