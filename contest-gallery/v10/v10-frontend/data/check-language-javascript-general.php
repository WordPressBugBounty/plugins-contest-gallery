<script data-cg-processing="true">

       if(typeof cgJsClass == 'undefined' ){ // required in JavaScript for first initialisation cgJsClass = cgJsClass || {}; would not work
           cgJsClass = {};
       }

        cgJsClass.gallery = cgJsClass.gallery || {};
       cgJsClass.gallery.language = cgJsClass.gallery.language ||  {};
       cgJsClass.gallery.language.general = cgJsClass.gallery.language.general ||  {};

       if(typeof cgJsClass.gallery.vars.isWereSetGeneralLanguage == 'undefined' ){ // check if general vars were already set
           cgJsClass.gallery.vars.isWereSetGeneralLanguage = true;
           cgJsClass.gallery.language.general.Account = <?php echo json_encode($language_Account); ?>;
           cgJsClass.gallery.language.general.EditProfile = <?php echo json_encode($language_EditProfile); ?>;
           cgJsClass.gallery.language.general.LogOut = <?php echo json_encode($language_LogOut); ?>;
           cgJsClass.gallery.language.general.BackToGallery = <?php echo json_encode($language_BackToGallery); ?>;
           cgJsClass.gallery.language.general.BackToGalleries = <?php echo json_encode($language_BackToGalleries); ?>;
           cgJsClass.gallery.language.general.ThisNicknameAlreadyExistsGeneral = <?php echo json_encode($language_ThisNicknameAlreadyExistsGeneral); ?>;
           cgJsClass.gallery.language.general.CGProfileInformation = <?php echo json_encode($language_CGProfileInformation); ?>;
           cgJsClass.gallery.language.general.RemoveProfileImage = <?php echo json_encode($language_RemoveProfileImage); ?>;
           cgJsClass.gallery.language.general.ThisFileTypeIsNotAllowed = <?php echo json_encode($language_ThisFileTypeIsNotAllowed); ?>;
           cgJsClass.gallery.language.general.TheFileYouChoosedIsToBigMaxAllowedSize = <?php echo json_encode($language_TheFileYouChoosedIsToBigMaxAllowedSize); ?>;
           cgJsClass.gallery.language.general.Required = <?php echo json_encode($language_Required); ?>;
           cgJsClass.gallery.language.general.Remove = <?php echo json_encode($language_Remove); ?>;
           cgJsClass.gallery.language.general.contentBlocked = <?php echo json_encode($language_contentBlocked); ?>;
           cgJsClass.gallery.language.general.ToViewTheContentYouMustAgreePrivacyPolicy = <?php echo json_encode($language_ToViewTheContentYouMustAgreePrivacyPolicy); ?>;
           cgJsClass.gallery.language.general.IAgree = <?php echo json_encode($language_IAgree); ?>;
           cgJsClass.gallery.language.general.ByAgreeingAllContentUnblocked = <?php echo json_encode($language_ByAgreeingAllContentUnblocked); ?>;
           cgJsClass.gallery.language.general.content = <?php echo json_encode($language_content); ?>;
           cgJsClass.gallery.language.general.UntilCurrentTabGetsClosed = <?php echo json_encode($language_UntilCurrentTabGetsClosed); ?>;
        }

</script>