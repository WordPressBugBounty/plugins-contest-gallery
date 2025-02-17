// !!! Nicht löschen Basis klasse für cgJsClassAdmin.createUpload.tinymce
var cgJsClassAdmin = cgJsClassAdmin || {};
cgJsClassAdmin.mainMenu = {};

cgJsClassAdmin.mainMenu.vars = {
    formLinkObject: null,
    $cgMainMenuMainTable: null
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

        var ecomText = '';
        if(EcommerceDownloadEntries>0){
            ecomText = "\r\nOriginal downloads for selling will be moved back to WordPress media library. Sold downloads will be not available anymore on order summary page for customers.";
        }

        if(version>=7){
            var deleteText = '';}
        else{
            var deleteText = ' All uploaded pictures will be irrevocable deleted.';
        }

        if (confirm("Are you sure you want to delete this gallery (id "+del+")?"+deleteText+""+ecomText+"\r\n(files and images which are in the gallery will be NOT deleted from the media library)")) {
            cgJsClassAdmin.index.functions.cgLoadBackend(jQuery(buttonObject).closest('form'),true);
            return true;
        } else {
            //alert("Clicked Cancel");
            return false;
        }

    }
};