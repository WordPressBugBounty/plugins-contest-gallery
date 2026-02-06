var cgJsClassAdmin = cgJsClassAdmin || {};
cgJsClassAdmin.gallery = cgJsClassAdmin.gallery|| {};
cgJsClassAdmin.gallery.reload = cgJsClassAdmin.gallery.reload || {};
cgJsClassAdmin.gallery.vars = cgJsClassAdmin.gallery.vars|| {};
cgJsClassAdmin.gallery.vars.ecommerce = cgJsClassAdmin.gallery.vars.ecommerce|| {};
cgJsClassAdmin.gallery.functions = cgJsClassAdmin.gallery.functions|| {};
cgJsClassAdmin.gallery.vars = {
    cgOverlayObserver: undefined,
    cgOverlayInterval: undefined,
    $cg_backend_info_container: null,
    allowedFileEndings: ['jpg','jpeg','gif','png','pdf','zip','txt','doc','docx','xls','xlsx','csv','mp3','m4a','ogg','wav','mp4','mov','webm','ppt','pptx','inst','tkt','twt','ytb'],// ico not allowed anymore since wp 6.0, status 02 Jul 2022
    multipleFilesNewAdded: {},
    multipleFilesForPost: {},
    multipleFilesForPostBeforeClone: {},
    addValue: 0,
    hasAdditionalFiles: false,
    entryPermalinks: {},
    base64andAltFileValues: {},
    base64andAltFileTypes: {},
    embedById: {},
    moveContactFieldsAssigns: {},
    openAiPrompt: '',
    openAIImagesToEdit: null,
    openAIImagesToEditById: null,
    openAiPrompts: null,
    moveSelected: '',
    pdfPreviewsCreated: 0,
    WpUsersData: {},
    WpUploadFilesForSaleArrayLoaded: [],
    $cg_backend_info_user_link_container: [],
    draggableDragStartDragEnd: "draggable='true'\n" +
        " ondragstart='cgJsClassAdmin.createUpload.dragAndDrop.drag(event)' ondragend='cgJsClassAdmin.createUpload.dragAndDrop.dragEnd(event)'  ondrop='cgJsClassAdmin.createUpload.dragAndDrop.drop(event)' ondragover='cgJsClassAdmin.createUpload.dragAndDrop.allowDrop(event)'  ondragenter='cgJsClassAdmin.createUpload.dragAndDrop.hoverOn(event)' ondragleave='cgJsClassAdmin.createUpload.dragAndDrop.hoverOff(event)' ",
    dragAndDropEvents: 'ondrop="cgJsClassAdmin.createUpload.dragAndDrop.drop(event)" ondragover="cgJsClassAdmin.createUpload.dragAndDrop.allowDrop(event)"\n' +
        'ondragenter="cgJsClassAdmin.createUpload.dragAndDrop.hoverOn(event)" ondragleave="cgJsClassAdmin.createUpload.dragAndDrop.hoverOff(event)"',
    cg_row:
        '        <div class="cg_row">\n' +
        '            <div class="cg_upl_settings cg_clicked" title="Row settings"></div>\n' +
        '            <div class="cg_row_col cg_upl_add" title="Add field"></div>\n' +
      //  '            <div class="cg_del_row" title="Delete row"></div>\n' +
        '        </div>\n',
    setStarOnStarOffSrc: function(){
        this.setStarOnSrc = jQuery('#cg_rating_star_on').val();
        this.setStarOffSrc = jQuery('#cg_rating_star_off').val();
    },
    setStarOnSrc: '',
    setStarOffSrc: '',
    setRating0:function (container) {
        container.find('.cg_rating_star_1').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_2').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_3').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_4').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_5').attr('src',this.setStarOffSrc);

    },
    setRating1:function (container) {

        container.find('.cg_rating_star_1').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_2').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_3').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_4').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_5').attr('src',this.setStarOffSrc);

    },
    setRating2:function (container) {

        container.find('.cg_rating_star_1').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_2').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_3').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_4').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_5').attr('src',this.setStarOffSrc);
        
    },
    setRating3:function (container) {

        container.find('.cg_rating_star_1').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_2').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_3').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_4').attr('src',this.setStarOffSrc);
        container.find('.cg_rating_star_5').attr('src',this.setStarOffSrc);
        
    },
    setRating4:function (container) {

        container.find('.cg_rating_star_1').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_2').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_3').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_4').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_5').attr('src',this.setStarOffSrc);
        
    },
    setRating5:function (container) {

        container.find('.cg_rating_star_1').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_2').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_3').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_4').attr('src',this.setStarOnSrc);
        container.find('.cg_rating_star_5').attr('src',this.setStarOnSrc);
        
    },
    ratingRnew: 0,
    cgChangedValueSelectorInTargetedSortableDiv: '#cgGalleryBackendContainer .cg_short_text, .cg_long_text',
    cgChangedAndSearchedValueSelector: '#cgGalleryBackendContainer #cgSortable .cg_short_text, #cgSortable .cg_long_text',
    inputsChanged: false,
    selectChanged: false,
    isHashJustChanged: false,
    cgVersion: ''
};
cgJsClassAdmin.general = cgJsClassAdmin.general || {};
cgJsClassAdmin.general.time = {
    init: function(){
        //  this.checkGalleriesEndTime();
    },
    checkGalleriesEndTime: function(){
        return;
        jQuery('.cg-contest-end-time').each(function () {

            var ActualTimeSeconds = Math.round((new Date).getTime()/1000);
            var cg_ContestEndTime = cgJsClassAdmin.general.time.correctTimezoneOffset(parseInt(jQuery(this).val()));

            if(cg_ContestEndTime<=ActualTimeSeconds){
                jQuery(this).closest('.cg-contest-ended').show();
            }
        });

    },
    correctTimezoneOffset: function (ContestEndTimeFromPhp) {

        var cg_ContestEndTime = ContestEndTimeFromPhp;

        if(cg_ContestEndTime!='' && cg_ContestEndTime>0){
            var date = new Date();
            var timezoneOffsetBrowser = date.getTimezoneOffset();// offset in MINUTES
            var timezoneServer = jQuery('#cgPhpDateOffset').val();// offset in MINUTES
            var correctTimezone = 0;// offset in MINUTES
            var correctSeconds = 0;

            if (timezoneOffsetBrowser == timezoneServer) {
                correctTimezone = 0;
            }

            if (timezoneOffsetBrowser < timezoneServer) {
                correctTimezone = (timezoneServer - timezoneOffsetBrowser)*-1;
            }

            if (timezoneOffsetBrowser > timezoneServer) {
                correctTimezone = timezoneOffsetBrowser-timezoneServer;
            }

            if(correctTimezone!=0){
                correctSeconds = correctTimezone*60; // 1 min = 60 sekunden
            }

            cg_ContestEndTime = cg_ContestEndTime + correctSeconds;
            return cg_ContestEndTime;
        }else{
            cg_ContestEndTime = 0;
            return cg_ContestEndTime;
        }
    },
    getTime: function (cg_ContestEndTime, ActualTimeSeconds) {

        // Create a new JavaScript Date object based on the timestamp
// multiplied by 1000 so that the argument is in milliseconds, not seconds.
        var date = new Date(ActualTimeSeconds*1000);
// Hours part from the timestamp
        var hours = date.getHours();
// Minutes part from the timestamp
        var minutes = "0" + date.getMinutes();
// Seconds part from the timestamp
        var seconds = "0" + date.getSeconds();

// Will display time in 10:30:23 format
        ActualTimeSeconds = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

        // Create a new JavaScript Date object based on the timestamp
// multiplied by 1000 so that the argument is in milliseconds, not seconds.
        var date = new Date(cg_ContestEndTime*1000);
// Hours part from the timestamp
        var hours = date.getHours();
// Minutes part from the timestamp
        var minutes = "0" + date.getMinutes();
// Seconds part from the timestamp
        var seconds = "0" + date.getSeconds();

// Will display time in 10:30:23 format
        cg_ContestEndTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

        alert(ActualTimeSeconds);
        alert(cg_ContestEndTime);

    }
};
