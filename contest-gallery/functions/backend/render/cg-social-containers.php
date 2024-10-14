<?php

if(!function_exists('cg_social_input_container')){
    function cg_social_input_container($GalleryID){

        echo "<div id='cgAddYoutubeContainer' class='cg_media_container cg_hide' data-cg-gid='$GalleryID'>";
// sort files hidden form
        ?>
        <form enctype="multipart/form-data" id="cgAddYoutubeForm"  action='<?php echo '?page="'.cg_get_version().'"/index.php'; ?>' method='POST'>
            <input type='hidden' name='cgGalleryHash' value='<?php echo md5(wp_salt( 'auth').'---cngl1---'.$GalleryID);?>'>
            <input type='hidden' name='GalleryID' value='<?php echo $GalleryID;?>'>
            <input type='hidden' name='action' value='post_cg_social_platform_input'>
            <span id="cgAddYoutubeLabel">Add YouTube/Twitter/Instagram/TikTok URL</span><br>
            <span class="cg_url_example">Example <b>YouTube</b>: https://www.youtube.com/watch?v=RG9TMn1FJzc</span><br>
            <span class="cg_url_example">Example <b>YouTube start time</b>: https://www.youtube.com/watch?v=RG9TMn1FJzc<b>&t=1m15s</b></span><br>
            <span class="cg_url_example">Example <b>Twitter (X)</b>: https://x.com/Interior/status/463440424141459456</span><br>
            <span class="cg_url_example">Example <b>Instagram</b>: https://www.instagram.com/p/C9fYTSPyPfp</span><br>
            <span class="cg_url_example">Example <b>TikTok</b>: https://www.tiktok.com/@scout2015/video/6718335390845095173</span><br>
            <input type='text' name='post_cg_social_platform_input_field' id='cgAddYoutubeInput'' > <input type="submit" id="cgAddYoutubeSubmit" class="cg_disabled_one" value="Add">
        </form>
        <?php
        echo "<div id='cgAddYoutubeLoader' class='cg-lds-dual-ring-gallery-hide cg_hide'></div>";
        echo "<div id='cgNoUrlMatch' class='cg_hide'>No match for given URL</div>";
        echo "<div id='cgAddYoutubePreview'></div>";
        echo "</div>";

    }
}

if(!function_exists('cg_social_library')){
    function cg_social_library($GalleryID){

        echo "<div id='cgYoutubeLibraryContainer' class='cg_media_container cg_hide' data-cg-gid='$GalleryID'>";
        echo "<div id='cgYoutubeNoEntries' class='cg_hide'>No YouTube entries added</div>";
        echo "<div id='cgYoutubeLibraryLoader' class='cg-lds-dual-ring-gallery-hide cg_hide'></div>";
        ?>
        <form enctype="multipart/form-data" id="cgAddYoutubeToGalleryForm"  action='<?php echo '?page="'.cg_get_version().'"/index.php'; ?>' method='POST'>
            <input type='hidden' name='cgGalleryHash' value='<?php echo md5(wp_salt( 'auth').'---cngl1---'.$GalleryID);?>'>
            <input type='hidden' name='GalleryID' value='<?php echo $GalleryID;?>'>
            <input type='hidden' name='action' value='post_cg_social_platforms_add_to_gallery'>
        </form>
        <?php
        echo "</div>";
        echo "<div id='cgYoutubeLibraryFooter' class='cg_media_container cg_hide' >";
        echo '<input type="button" id="cgDeleteYoutubeLibraryButton" class="cg_disabled_one" value="Delete selected from library">';
        echo '<input type="button" id="cgAddYoutubeLibraryButton" class="cg_disabled_one" value="Add selected to gallery">';
        echo "</div>";

    }
}


?>