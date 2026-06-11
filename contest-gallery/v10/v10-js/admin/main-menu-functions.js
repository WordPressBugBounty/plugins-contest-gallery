// !!! Nicht löschen Basis klasse für cgJsClassAdmin.createUpload.tinymce
var cgJsClassAdmin = cgJsClassAdmin || {};
cgJsClassAdmin.mainMenu = {};

cgJsClassAdmin.mainMenu.vars = {
    formLinkObject: null,
    $cgMainMenuMainTable: null,
    $deleteGalleryForm: null
};

cgJsClassAdmin.mainMenu.functions = {
    load: function ($,$formLinkObject,$response) {
        cgJsClassAdmin.mainMenu.vars.$cgMainMenuMainTable = $('#cgMainMenuTable');
        cgJsClassAdmin.mainMenu.vars.$cgGoTopOptions = $('#cgGoTopOptions');
        cgJsClassAdmin.options.vars.$cgGoTopOptions = null;
        cgJsClassAdmin.options.vars.windowHeight = null;
    },
    cgCheckCopy: function (optionId) {

        if (confirm("Are you sure you want to copy this gallery (id "+optionId+")? Everything will be copied except of voting results and comments.")) {
            return true;
        } else {
            return false;
        }

    },
    cgCheckCopyPrevV7: function (optionId) {

        if (confirm("Are you sure you want to copy this gallery (id "+optionId+")?")) {
            return true;
        } else {
            return false;
        }

    },
    cgCheckDelete: function (arg,version,buttonObject,EcommerceDownloadEntries) {

        var del = arg;
        var $buttonObject = jQuery(buttonObject);
        var $form = $buttonObject.closest('form');
        var $modal = jQuery('#cgDeleteGalleryConfirmContainer');
        var parsedVersion = parseInt(version,10) || 0;
        var ecommerceDownloadEntries = parseInt(EcommerceDownloadEntries,10) || 0;

        if($modal.length){
            cgJsClassAdmin.mainMenu.vars.$deleteGalleryForm = $form;
            $modal.find('#cgDeleteGalleryConfirmGalleryId').text(del);
            $modal.find('#cgDeleteGalleryConfirmEcommerceNote').toggleClass('cg_hide', ecommerceDownloadEntries <= 0);
            $modal.find('#cgDeleteGalleryConfirmMediaNote').toggleClass('cg_hide', parsedVersion < 7);
            $modal.find('#cgDeleteGalleryConfirmLegacyNote').toggleClass('cg_hide', parsedVersion >= 7);
            $modal.find('#cgDeleteGalleryConfirmSubmit').prop('disabled',false).text('Delete gallery');
            cgJsClassAdmin.gallery.functions.showModal('#cgDeleteGalleryConfirmContainer');
            return false;
        }

        var confirmText = "Are you sure you want to delete this gallery (id "+del+")?\r\n\r\n";

        confirmText += "This action deletes the gallery and its entries.";
        if(parsedVersion>=7){
            confirmText += "\r\nFiles and images used by this gallery will not be deleted from the media library.";
        }else{
            confirmText += "\r\nAll uploaded pictures will be irrevocably deleted.";
        }
        confirmText += "\r\n\r\nReal watermarks will be restored before this gallery is deleted.";
        confirmText += "\r\nIf the same WordPress media file is used in another gallery, it can become unwatermarked there too.";
        if(ecommerceDownloadEntries>0){
            confirmText += "\r\n\r\nOriginal downloads for selling will be moved back to WordPress media library.";
            confirmText += "\r\nSold downloads will be not available anymore on order summary page for customers.";
        }

        if (confirm(confirmText)) {
            cgJsClassAdmin.index.functions.cgLoadBackend(jQuery(buttonObject).closest('form'),true);
            return true;
        } else {
            //alert("Clicked Cancel");
            return false;
        }

    }
};

jQuery(document).on('click','#cgDeleteGalleryConfirmCancel',function () {
    cgJsClassAdmin.mainMenu.vars.$deleteGalleryForm = null;
    cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
    jQuery('body,html').removeClass('cg_no_scroll cg_overflow_hidden cg_pointer_events_none cg_overflow_y_hidden');
});

jQuery(document).on('click','#cgDeleteGalleryConfirmSubmit',function () {
    var $form = cgJsClassAdmin.mainMenu.vars.$deleteGalleryForm;
    if(!$form || !$form.length){
        cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
        jQuery('body,html').removeClass('cg_no_scroll cg_overflow_hidden cg_pointer_events_none cg_overflow_y_hidden');
        return;
    }

    jQuery(this).prop('disabled',true).text('Deleting ...');
    jQuery('#cgDeleteGalleryConfirmContainer').addClass('cg_hide').removeClass('cg_active');
    jQuery('#cgBackendBackgroundDrop').addClass('cg_hide').removeClass('cg_active cg_high_overlay cg_pointer_events_none');
    jQuery('body,html').removeClass('cg_no_scroll cg_overflow_hidden cg_pointer_events_none cg_overflow_y_hidden');
    cgJsClassAdmin.mainMenu.vars.$deleteGalleryForm = null;
    cgJsClassAdmin.index.functions.cgLoadBackend($form,true);
});
