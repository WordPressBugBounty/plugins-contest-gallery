<?php

if(!function_exists('cg_attach_to_another_user_container')){
    function cg_attach_to_another_user_container($GalleryID){

        echo "<div id='cgAttachToAnotherUserContainer' class='cg_backend_action_container cg_hide cg_min_height_fit_content'>
<span class='cg_message_close'></span>";

	    echo "<div id='cgAttachToAnotherUserExplanation'>Attach entry ID <span id='cgAttachToAnotherUserExplanationEntryId' ></span> to selected user</div>";

            echo "<div class='cg-lds-dual-ring-gallery-hide cg_hide'></div>";
// sort files hidden form
        ?>
        <form enctype="multipart/form-data" id="cgAttachToAnotherUserForm" class="cg_hide" action='<?php echo '?page="'.cg_get_version().'"/index.php'; ?>' method='POST'>
            <input type='hidden' name='cgGalleryHash' value='<?php echo md5(wp_salt( 'auth').'---cngl1---'.$GalleryID);?>'>
            <input type='hidden' name='GalleryID' value='<?php echo $GalleryID;?>'>
            <input type='hidden' name='cgEntryId' value=''>
            <input type='hidden' name='action' value='post_cg_attach_to_another_user_select'>
            <div  id="cgAttachToAnotherUserSubmitButtonContainer" class="cg_hide">
                <button  id="cgAttachToAnotherUserSubmitButton" type="submit" class="cg_backend_button_gallery_action">Attach</button>
                <button  id="cgAttachToAnotherUserDetachButton" type="submit" class="cg_backend_button_gallery_action cg_hide">Detach</button>
            </div>
        </form>

        <?php
        echo "</div>";

    }
}

?>