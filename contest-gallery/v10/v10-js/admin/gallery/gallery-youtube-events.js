jQuery(document).ready(function ($) {

    var $mediaFrame;
    var youtubeData;
    var tiktokdata;
    var urlPart;
    var embed;
    var instagramData;

    var $mediaFrameContentSub;

    $(document).on('click','.cg_media_menu_item',function (){
        $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        $mediaFrame.find('.media-toolbar-primary.search-form').addClass('cg_hide');
        $mediaFrame.find('.media-menu-item.active').removeClass('active').addClass('cg_active');
    });

    $(document).on('click','#cgAddYoutube',function (){
        var $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        $mediaFrame.find('#cgYoutubeLibrary').removeClass('cg_active');
        $(this).addClass('cg_active');
        var $cgAddYoutubeContainer = $('#cgAddYoutubeContainer').clone().removeClass('cg_hide');
        var $mediaFrameContent =  $mediaFrame.find('.media-frame-content');
        $mediaFrameContentSub =  $mediaFrame.find('.media-frame-content > *');
        $mediaFrameContentSub.addClass('cg_hide');
        $mediaFrameContent.append($cgAddYoutubeContainer);
    });

    $(document).on('click','#cgYoutubeLibrary,#cgYoutubeLibraryLoadMoreButton',function (e){
        debugger
        $mediaFrame = $('.media-frame.cg_backend_area');
        $mediaFrame.find('#cgAddYoutube').removeClass('cg_active');
        $(this).addClass('cg_active');
        $mediaFrame.find('#cgYoutubeLibraryNewAddedCount').addClass('cg_hide').text('0');
        var $mediaFrameContent =  $mediaFrame.find('.media-frame-content');
        $mediaFrameContentSub =  $mediaFrame.find('.media-frame-content > *');
        $mediaFrameContentSub.addClass('cg_hide');
        var $cgYoutubeLibraryContainer = $('#cgYoutubeLibraryContainer.cg_cloned');
        if($cgYoutubeLibraryContainer.length){
            $cgYoutubeLibraryContainer.removeClass('cg_hide');
            var $cgYoutubeLibraryFooter = $mediaFrame.find('#cgYoutubeLibraryFooter');
            $cgYoutubeLibraryFooter.removeClass('cg_hide');
            $cgYoutubeLibraryContainer.find('#cgYoutubeLibraryLoadMore').remove();
        }else{
            $cgYoutubeLibraryContainer = $('#cgYoutubeLibraryContainer').clone().removeClass('cg_hide').addClass('cg_cloned');
            var $cgYoutubeLibraryFooter = $('#cgYoutubeLibraryFooter').clone().removeClass('cg_hide');
            $mediaFrameContent.append($cgYoutubeLibraryContainer);
            $mediaFrameContent.append($cgYoutubeLibraryFooter);
        }

        var $cgYoutubeLibraryLoader = $mediaFrame.find('#cgYoutubeLibraryLoader');
        $cgYoutubeLibraryLoader.removeClass('cg_hide');
        if($(this).attr('id')=='cgYoutubeLibrary'){
            $cgYoutubeLibraryContainer.find('.cg_ytb_media_container').remove();
            cgJsClassAdmin.gallery.vars.youtubePostsShowMore = false;
        }
        $cgYoutubeLibraryContainer.append($cgYoutubeLibraryLoader);
        var $cgYoutubeNoEntries = $mediaFrame.find('#cgYoutubeNoEntries');
        $cgYoutubeLibraryContainer.closest('.media-frame-content').css('overflow','visible');// has to be because of cgYoutubeLibraryFooter with add to gallery button

        e.preventDefault();
        var data = {};
        data['action'] = 'post_cg_social_platforms_query';
        data['gid'] = $(this).closest('#cgYoutubeLibraryContainer').attr('data-cg-gid');
        data['cg_start'] = 0;

        if($(this).attr('id')=='cgYoutubeLibraryLoadMoreButton'){
            data['cg_start'] = $(this).attr('data-cg-start');
            $(this).remove();
        }

        var isReplace = false;
        if($(this).closest('.cg_is_replace').length){
            isReplace = true;
        }

        if($(this).closest('.cg_add_additional_files').length){
            $cgYoutubeLibraryFooter.find('#cgAddYoutubeLibraryButton').addClass('cg_add_additional_files').val(isReplace ? 'Replace with selected' : 'Add selected');
        }

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: data
        }).done(function (response) {

            var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));
            $response.find('script[data-cg-processing="true"]').each(function () {
                var script = jQuery(this).html();
                eval(script);
            });

            $cgYoutubeLibraryLoader.addClass('cg_hide');

            if(cgJsClassAdmin.gallery.vars.youtubePosts.length){
                cgJsClassAdmin.gallery.vars.youtubePosts.forEach(function (object){
                    var $frame = '';
                    var cg_ytb_only = '';
                    if(object.post_mime_type=='contest-gallery-instagram'){
                        cgJsClassAdmin.gallery.vars.embedById[object['ID']] = object.guid;
                        cg_ytb_only = 'cg_inst_only';
                        $frame = $('<iframe  class="cg_ytb_media" width="270px" height="202px" src="' + object['guid'] + '" ></iframe>');
                    }
                    if(object.post_mime_type=='contest-gallery-youtube'){
                        cgJsClassAdmin.gallery.vars.embedById[object['ID']] = object.guid;
                        cg_ytb_only = 'cg_ytb_only';
                        $frame = $('<iframe  class="cg_ytb_media" width="270px" height="165px" src="' + object['guid'] + '" ></iframe>');
                    }
                    if(object.post_mime_type=='contest-gallery-twitter'){
                        cgJsClassAdmin.gallery.vars.embedById[object['ID']] = object.blockquote;
                        $frame = $(object.blockquote);
                    }
                    if(object.post_mime_type=='contest-gallery-tiktok'){
                        cgJsClassAdmin.gallery.vars.embedById[object['ID']] = object.blockquote;
                        cg_ytb_only = 'cg_tkt_only';
                        $frame = $(object.blockquote);
                    }
                    var $cg_ytb_media_container = $('<div class="cg_ytb_media_container '+cg_ytb_only+'" data-cg-wp-upload-id="' + object['ID'] + '">' +
                        '<div class="cg_ytb_media_delete_select_container"><div class="cg_ytb_media_delete"  data-cg-wp-upload-id="' + object['ID'] + '"><span>Delete</span></div><div class="cg_ytb_media_select"  data-cg-wp-upload-id="' + object['ID'] + '" data-post-mime-type="' + object['post_mime_type'] + '"><span>'+(isReplace ? 'Replace' : 'Add')+'</span></div></div>' +
                        '</div>');
                    $cg_ytb_media_container.append($frame);
                    $cgYoutubeLibraryContainer.append($cg_ytb_media_container);
                });
                if(cgJsClassAdmin.gallery.vars.youtubePostsShowMore){
                    $cgYoutubeLibraryContainer.find('#cgYoutubeLibraryLoadMore').remove();
                    $cgYoutubeLibraryContainer.append('<div id="cgYoutubeLibraryLoadMore"><div>Showing <b>'+cgJsClassAdmin.gallery.vars.youtubePostsStep+'</b> of <b>'+cgJsClassAdmin.gallery.vars.youtubePostsCount+'</b> social media items</div><div id="cgYoutubeLibraryLoadMoreButton" data-cg-start="'+cgJsClassAdmin.gallery.vars.youtubePostsStep+'">Load more</div></div>');
                }
            }else{
                $cgYoutubeNoEntries.removeClass('cg_hide');
            }

        }).fail(function (xhr, status, error) {
            debugger
            console.log('response error');
            console.log(xhr);
            console.log('status error');
            console.log(status);
            console.log('error error');
            console.log(error);

            return;

        }).always(function () {

            var test = 1;

        });
    });

    var urlType = '';

    $(document).on('input','#cgAddYoutubeInput',function (e){
        urlType = '';
        var $cgAddYoutubeInput = $(this);
        var $cgAddYoutubePreview = $mediaFrame.find('#cgAddYoutubePreview');
        var $cgAddYoutubeLoader = $mediaFrame.find('#cgAddYoutubeLoader');
        var $cgNoUrlMatch = $mediaFrame.find('#cgNoUrlMatch');
        var $cgAddYoutubeSubmit = $mediaFrame.find('#cgAddYoutubeSubmit');
        $cgAddYoutubeSubmit.addClass('cg_disabled_one');
        var url = $(this).val();
        youtubeData = null;
        tiktokdata = null;
        instagramData = null;
        debugger
        if(url.trim()==''){
            $cgAddYoutubeLoader.addClass('cg_hide');
            $cgNoUrlMatch.addClass('cg_hide');
            $cgAddYoutubePreview.empty().addClass('cg_hide');
        }else{
            embed = '';
            urlPart = '';
            $cgAddYoutubePreview.empty().addClass('cg_hide');
            $cgAddYoutubeLoader.removeClass('cg_hide');
            $cgNoUrlMatch.addClass('cg_hide');
            if(url.indexOf('x.com/')>-1 || url.indexOf('twitter.com/')>-1){
                urlType = 'twitter';
                var cgCallTwitter = async function () {

                    e.preventDefault();
                    var data = {};
                    data['action'] = 'post_cg_twitter_get';
                    data['post_cg_twitter_url'] = url;
                    cgJsClassAdmin.gallery.vars.twitterData = null;

                    $.ajax({
                        url: 'admin-ajax.php',
                        method: 'post',
                        data: data
                    }).done(function (response) {
                        embed = url;
                        if(url.indexOf('x.com')>-1){
                            urlPart = url.split('x.com/')[1];
                        }
                        if(url.indexOf('twitter.com')>-1){
                            urlPart = url.split('twitter.com/')[1];
                        }

                        var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));
                        $response.find('script[data-cg-processing="true"]').each(function () {
                            var script = jQuery(this).html();
                            eval(script);
                        });
                        console.log('cgJsClassAdmin.gallery.vars.twitterData')
                        console.log(cgJsClassAdmin.gallery.vars.twitterData)

                        if(cgJsClassAdmin.gallery.vars.twitterData){
                            $cgAddYoutubeLoader.removeClass('cg_hide');
                            $cgNoUrlMatch.addClass('cg_hide');
                            $cgAddYoutubePreview.addClass('cg_opacity_0').removeClass('cg_hide');
                            $cgAddYoutubePreview.empty().html(cgJsClassAdmin.gallery.vars.twitterData.html);
                            $cgAddYoutubeInput.addClass('cg_disabled_one');
                            setTimeout(function (){
                                $cgAddYoutubeLoader.addClass('cg_hide');
                                $cgAddYoutubeInput.removeClass('cg_disabled_one');
                                $cgAddYoutubePreview.removeClass('cg_opacity_0');
                                $cgAddYoutubeSubmit.removeClass('cg_disabled_one');
                            },1500);
                        }else{

                            $cgAddYoutubeInput.removeClass('cg_disabled_one');
                            $cgAddYoutubeLoader.addClass('cg_hide');
                            $cgNoUrlMatch.removeClass('cg_hide');
                            $cgAddYoutubePreview.empty().addClass('cg_hide');
                            $cgAddYoutubeSubmit.addClass('cg_disabled_one');
                        }

                    }).fail(function (xhr, status, error) {
                        debugger
                        console.log('response error');
                        console.log(xhr);
                        console.log('status error');
                        console.log(status);
                        console.log('error error');
                        console.log(error);
                        $cgAddYoutubeLoader.addClass('cg_hide');
                        $cgNoUrlMatch.removeClass('cg_hide');

                        return;

                    }).always(function () {

                        var test = 1;

                    });

                }

                cgCallTwitter();

            }else if(url.indexOf('instagram.com/')>-1){
                urlType = 'instagram';
                $cgAddYoutubeLoader.addClass('cg_hide');
                $cgNoUrlMatch.addClass('cg_hide');
                $cgAddYoutubePreview.empty().removeClass('cg_hide');

                instagramData = {};
                instagramData.title = '';
                if(url.indexOf('instagram.com/p/')>-1){
                    urlPart = url.split('instagram.com/p/')[1];
                }else if(url.indexOf('instagram.com/')>-1){
                    urlPart = url.split('instagram.com/')[1];
                }
                // order important, first check question mark then last char /
                if(url.indexOf('?')>-1){
                    url = url.split('?')[0];
                }
                var lastChar = url.substring ((url.length - 1));
                if(lastChar=='/'){
                    url = url.substring (0,(url.length - 1));
                }
                url = url+'/embed';
                embed = url;
                $cgAddYoutubePreview.append('<iframe width="420" height="236"' +
                    ' src="'+url+'">' +
                    '</iframe>');
                $cgAddYoutubeSubmit.removeClass('cg_disabled_one');
            }else if(url.indexOf('youtube.com/')>-1){
                urlType = 'youtube';
                /**
                 https://stackoverflow.com/questions/28735459/how-to-validate-youtube-url-in-client-side-in-text-box
                 * Normal Url: https://www.youtube.com/watch?v=12345678901
                 * Share Url: https://youtu.be/12345678901
                 * Share Url with start time: https://youtu.be/12345678901?t=6
                 * Mobile browser url: https://m.youtube.com/watch?v=12345678901&list=RD12345678901&start_radio=1
                 * Long url: https://www.youtube.com/watch?v=12345678901&list=RD12345678901&start_radio=1&rv=smKgVuS
                 * Long url with start time: https://www.youtube.com/watch?v=12345678901&list=RD12345678901&start_radio=1&rv=12345678901&t=38
                 * YouTube Shorts: https://youtube.com/shorts/12345678901
                 */
                var cgValidateYouTubeUrl =  function (url)
                {
                    if (url != undefined || url != '') {
                        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
                        var match = url.match(regExp);
                        if (match && match[2].length == 11) {
                            // Do anything for being valid
                            // if need to change the url to embed url then use below line
                            //$('#ytplayerSide').attr('src', 'https://www.youtube.com/embed/' + match[2] + '?autoplay=0');
                            return match[2];
                        }
                        else {
                            // Do anything for not being valid
                            return '';
                        }
                    }
                }

                urlPart = cgValidateYouTubeUrl(url);

                if(urlPart){
                    var vidurl = 'https://www.youtube.com/watch?v='+urlPart;

                    var cgGetSecondsStartTime = function (url) {

                        //console.log('url to check')
                        //console.log(url)

                        //var url ="https://www.youtube.com/watch?v=RG9TMn1FJzc?lala=33&t=2h5m16s&blabla=3";
                        var timeStamp = new RegExp('t=(.)*?[&|(\s)]', 'i').exec(url);

                        //console.log('timeStamp')
                        //console.log(timeStamp)

                        var totalTimeInSeconds = 0;

                        if(timeStamp){

                            var hours = new RegExp(/(\d)+h/, 'i').exec(timeStamp[0]);

                            //console.log('hours')
                            //console.log(hours)

                            var minutes = new RegExp(/(\d)+m/, 'i').exec(timeStamp[0]);

                            //console.log('minutes')
                            //console.log(minutes)

                            var seconds = new RegExp(/(\d)+s/, 'i').exec(timeStamp[0]);

                            //console.log('seconds')
                            //console.log(seconds)

                            if (hours) {
                                totalTimeInSeconds += parseInt(hours[0]) * 60 * 60;
                            }

                            if (minutes) {
                                totalTimeInSeconds += parseInt(minutes[0]) * 60;
                            }

                            if (seconds) {
                                totalTimeInSeconds += parseInt(seconds[0]) * 1;
                            }

                        }

                        return totalTimeInSeconds;

                    }

                    var secondsStartTime = cgGetSecondsStartTime(url);

                    //console.log('secondsStartTime')
                    //console.log(secondsStartTime)

                    var cgAsyncCallYoutube = async function () {
                        try {
                            await fetch('https://noembed.com/embed?dataType=json&url='+vidurl )
                                .then(res => res.json())
                                .then(function (data){
                                    //debugger
                                    //console.log('urlPart')
                                    //console.log(urlPart)
                                    embed =  'https://www.youtube.com/embed/'+urlPart;
                                    if(secondsStartTime){
                                        embed += '?start='+secondsStartTime;
                                    }
                                    //console.log('embed done')
                                    //console.log(embed)
                                    if(data.title){
                                        //console.log('youtube data')
                                        //console.log(data)
                                        $cgAddYoutubeLoader.addClass('cg_hide');
                                        $cgNoUrlMatch.addClass('cg_hide');
                                        $cgAddYoutubePreview.empty().removeClass('cg_hide');
                                        $cgAddYoutubePreview.append('<iframe width="420" height="236"' +
                                            ' src="'+embed+'">' +
                                            '</iframe>');
                                        youtubeData = data;
                                        $cgAddYoutubeSubmit.removeClass('cg_disabled_one');
                                    }else{
                                        $cgAddYoutubeLoader.addClass('cg_hide');
                                        $cgNoUrlMatch.removeClass('cg_hide');
                                        $cgAddYoutubePreview.empty().addClass('cg_hide');
                                    }
                                });
                        } catch (error) {
                            // TypeError: Failed to fetch
                            $cgAddYoutubeLoader.addClass('cg_hide');
                            $cgNoUrlMatch.removeClass('cg_hide');
                            //cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('There was an error calling YouTube URL service noembed.com: '+error);
                        }
                    }

                    cgAsyncCallYoutube();

                }else{
                    $cgAddYoutubeLoader.addClass('cg_hide');
                    $cgNoUrlMatch.removeClass('cg_hide');
                    $cgAddYoutubePreview.empty().addClass('cg_hide');
                }

            }else if(url.indexOf('tiktok.com/')>-1){
                urlType = 'tiktok';
                var cgAsyncCallTikTok = async function () {
                    try {
                        await fetch('https://www.tiktok.com/oembed?url='+url)
                            .then(res => res.json())
                            .then(function (data){
                                console.log('data tik tok')
                                console.log(data)
                                embed =  url;
                                if(data.title){
                                    tiktokdata = data;
                                    urlPart = url.split('tiktok.com/')[1];
                                    $cgAddYoutubeLoader.removeClass('cg_hide');
                                    $cgNoUrlMatch.addClass('cg_hide');
                                    $cgAddYoutubePreview.addClass('cg_opacity_0').removeClass('cg_hide');
                                    $cgAddYoutubePreview.empty().html(data.html);
                                    $cgAddYoutubeInput.addClass('cg_disabled_one');
                                    setTimeout(function (){
                                        $cgAddYoutubeLoader.addClass('cg_hide');
                                        $cgAddYoutubeInput.removeClass('cg_disabled_one');
                                        $cgAddYoutubePreview.removeClass('cg_opacity_0');
                                        $cgAddYoutubeSubmit.removeClass('cg_disabled_one');
                                    },1500);
                                }else{
                                    $cgAddYoutubeInput.removeClass('cg_disabled_one');
                                    $cgAddYoutubeLoader.addClass('cg_hide');
                                    $cgNoUrlMatch.removeClass('cg_hide');
                                    $cgAddYoutubePreview.empty().addClass('cg_hide');
                                    $cgAddYoutubeSubmit.addClass('cg_disabled_one');
                                }
                            });
                    } catch (error) {
                        // TypeError: Failed to fetch
                        $cgAddYoutubeLoader.addClass('cg_hide');
                        $cgNoUrlMatch.removeClass('cg_hide');
                        //cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('There was an error calling TikTok URL service tiktok.com/oembed: '+error);
                    }
                }

                cgAsyncCallTikTok();

            }else{
                $cgAddYoutubeLoader.addClass('cg_hide');
                $cgNoUrlMatch.removeClass('cg_hide');
                $cgAddYoutubePreview.empty().addClass('cg_hide');
            }

        }
    });

    $(document).on('click','#cgAddYoutubeSubmit',function (e){
        e.preventDefault();
        debugger
        var $cgAddYoutubeContainer = $(this).closest('#cgAddYoutubeContainer');
        var $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        var data = {};
        data['action'] = 'post_cg_social_platform_input';
        data['urlType'] = urlType;
        if(urlType=='youtube'){
            data['socialData'] = youtubeData;
        }else if(urlType=='twitter'){
            data['socialData'] = cgJsClassAdmin.gallery.vars.twitterData;
            data['socialData']['title'] = jQuery(cgJsClassAdmin.gallery.vars.twitterData.html).text();
        }else if(urlType=='tiktok'){
            data['socialData'] = tiktokdata;
            data['socialData']['title'] = jQuery(tiktokdata.html).text();
        }else if(urlType=='instagram'){
            data['socialData'] = instagramData;
        }
        data['guid'] = embed;
        // order important, first check question mark then last char /
        if(urlPart.indexOf('?')>-1){// remove eventual parameters in link
            urlPart = urlPart.split('?')[0];
        }
        var lastChar = urlPart.substring ((urlPart.length - 1));
        if(lastChar=='/'){
            urlPart = urlPart.substring (0,(urlPart.length - 1));
        }
        data['urlPart'] = urlPart;
        data['action2'] = $cgAddYoutubeContainer.attr('data-cg-gid');

        var $cgAddYoutubeLoader = $cgAddYoutubeContainer.find('#cgAddYoutubeLoader');
        $cgAddYoutubeLoader.removeClass('cg_hide');
        $cgAddYoutubeContainer.find('#cgAddYoutubePreview').empty();
        $(this).addClass('cg_disabled_one');
        //formPostData.append('cg_position['+$(this).attr('data-cg-real-id')+']', $(this).val());

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: data
        }).done(function (response) {

            var $count = $mediaFrame.find('#cgYoutubeLibraryNewAddedCount');
            var newValue = parseInt($count.text());
            newValue++;
            $count.text('+'+newValue);
            $count.removeClass('cg_hide');
            $cgAddYoutubeLoader.addClass('cg_hide');

            $cgAddYoutubeContainer.find('#cgAddYoutubeInput').val('').focus();
            var Type = 'YouTube';
            if(urlType=='twitter'){
                Type = 'Twitter';
            }else if(urlType=='instagram'){
                Type = 'Instagram';
            }else if(urlType=='tiktok'){
                Type = 'TikTok';
            }
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Successfully added '+Type+'  to  library',true);

            return;

        }).fail(function (xhr, status, error) {
            debugger
            console.log('response error');
            console.log(xhr);
            console.log('status error');
            console.log(status);
            console.log('error error');
            console.log(error);

            return;

        }).always(function () {

            var test = 1;

        });
    });

    $(document).on('click','.cg_ytb_media_select:not(.cg_add)',function (e){

        if($(this).closest('.cg_is_replace').length){
            $(this).closest('#cgYoutubeLibraryContainer').find('.cg_ytb_media_select.cg_add').removeClass('cg_add');
        }
        $(this).addClass('cg_add');
        $(this).closest('.cg_ytb_media_container').find('.cg_ytb_media_delete').removeClass('cg_delete');
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_add').length){
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').addClass('cg_disabled_one');
        }
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_delete').length){
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').addClass('cg_disabled_one');
        }

    });

    $(document).on('click','.cg_ytb_media_select.cg_add',function (e){
        $(this).removeClass('cg_add');
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_add').length){
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').addClass('cg_disabled_one');
        }
    });

    $(document).on('click','.cg_ytb_media_delete:not(.cg_delete)',function (e){
        $(this).addClass('cg_delete');
        $(this).closest('.cg_ytb_media_container').find('.cg_ytb_media_select').removeClass('cg_add');
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_delete').length){
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').addClass('cg_disabled_one');
        }
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_add').length){
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').addClass('cg_disabled_one');
        }
    });

    $(document).on('click','.cg_ytb_media_delete.cg_delete',function (e){
        $(this).removeClass('cg_delete');
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_delete').length){
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').addClass('cg_disabled_one');
        }
    });

    $(document).on('click','#cgAddYoutubeLibraryButton',function (e){
        e.preventDefault();
        debugger
        var $mediaFrame = $(this).closest('.media-frame');
        var gid = $('#cg_gallery_id').val();
        $(this).addClass('cg_disabled_one');

        var formPostData = new FormData();
        formPostData.append('action', 'post_cg_social_platforms_add_to_gallery');
        formPostData.append('action2', parseInt($(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').attr('data-cg-gid')));

        var data = {};
        data['action'] = 'post_cg_social_platforms_add_to_gallery';
        data['action2'] = $(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').attr('data-cg-gid');
        data['cg_wp_post_ids'] = [];
        var cg_assign_category = 0;

        var cgAssignedFields = JSON.parse(localStorage.getItem('cgAssignedFields' + gid));
        if (cgAssignedFields && cgAssignedFields.category) {
            cg_assign_category = cgAssignedFields.category;
        }
        data['cg_assign_category'] = cg_assign_category;
        // reverse to get proper order same like in CG YouTube Library
        $($(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').find('.cg_ytb_media_select.cg_add').get().reverse()).each(function (){
            formPostData.append('cg_wp_post_ids[]', parseInt($(this).attr('data-cg-wp-upload-id')));
            data['cg_wp_post_ids'].push(parseInt($(this).attr('data-cg-wp-upload-id')));
        });
        $mediaFrame.find('.cg_ytb_media_container').remove();
        $mediaFrame.find('#cgYoutubeLibraryLoader').removeClass('cg_hide');
        jQuery('body').addClass('cg_pointer_events_none cg_overflow_y_hidden');

        if($mediaFrame.closest('.media-modal').hasClass('cg_add_additional_files')){
            var dataCopy = data;
            var file_frame = cgJsClassAdmin.gallery.vars.file_frame;
            var isReplace = !!file_frame.$el.hasClass('cg_is_replace');
            var WpUploadToReplace = file_frame.$el.attr('data-cg-wp-upload-to-replace') ? file_frame.$el.attr('data-cg-wp-upload-to-replace') : 0;
            var NewWpUploadWhichReplace = 0;
            var isDynamicMessageVisible = false;
            var newWpUpload = 0;
            var newImgType = '';
            var $cg_backend_info_container = cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container');
            var realId = $cg_backend_info_container.attr('data-cg-real-id');
            var requiredData = cgJsClassAdmin.gallery.functions.getDataForAdditionalFiles(realId,$cg_backend_info_container);
            var data = requiredData.data;
            var order = requiredData.order;
            var attachment = [];
            var youtubePosts = cgJsClassAdmin.gallery.vars.youtubePosts;
            for (var index in youtubePosts) {
                if (!youtubePosts.hasOwnProperty(index)) {
                    break;
                }
                dataCopy['cg_wp_post_ids'].forEach(function (value){
                    youtubePosts[index]['date'] = youtubePosts[index]['post_date'];// has to be corrected here
                    youtubePosts[index]['id'] = youtubePosts[index]['ID'];// has to be corrected here
                    if(youtubePosts[index]['id']==value){
                        attachment.push(youtubePosts[index]);
                    }
                });
            }

            if(isReplace){
                //$mediaFrame.closest('.media-modal').addClass('cg_hide');
                cgJsClassAdmin.gallery.functions.removeUsedFrames();
                $('#cgMultipleFilesForPostContainer').addClass('cg_hide');
                jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden modal-open');
            }

            attachment = cgJsClassAdmin.gallery.functions.orderDescendAttachments(attachment);
            cgJsClassAdmin.gallery.functions.selectAddAdditionalFiles(attachment,file_frame,data,$cg_backend_info_container,isDynamicMessageVisible,realId,isReplace,newWpUpload,newImgType,NewWpUploadWhichReplace,order,WpUploadToReplace,jQuery);

            //  setTimeout(function (){
            if(!isReplace){
                $mediaFrame.closest('.media-modal').find('.media-modal-close').click();
                jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');
            }
            //    },300);

        }else{

            $.ajax({
                url: 'admin-ajax.php',
                method: 'post',
                data: data
            }).done(function (response) {

                jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');
                $mediaFrame.find('#cgYoutubeLibraryLoader').addClass('cg_hide');
                $('.media-modal-close').click();// have to be clicked that way is extra element

                cgJsClassAdmin.gallery.functions.reloadAfterAddingEntries($,response);

            }).fail(function (xhr, status, error) {
                jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');

                debugger
                console.log('response error');
                console.log(xhr);
                console.log('status error');
                console.log(status);
                console.log('error error');
                console.log(error);

                return;

            }).always(function () {

                var test = 1;

            });
        }

    });

    $(document).on('click','#cgDeleteYoutubeLibraryButton',function (e){
        e.preventDefault();
        debugger
        var $mediaFrame = $(this).closest('.media-frame');
        $(this).addClass('cg_disabled_one');
        var form = $(this).closest('.media-frame-content').find('#cgAddYoutubeToGalleryForm').get(0);
        var formPostData = new FormData();
        formPostData.append('action', 'post_cg_youtube_delete_from_library');
        formPostData.append('action2', parseInt($(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').attr('data-cg-gid')));
        var data = {};
        data['action'] = 'post_cg_youtube_delete_from_library';
        data['action2'] = $(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').attr('data-cg-gid');
        data['cg_wp_post_ids'] = [];
        $mediaFrame.find('.cg_ytb_media_container').addClass('cg_hide');
        $mediaFrame.find('#cgYoutubeLibraryLoader').removeClass('cg_hide');
        $(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').find('.cg_ytb_media_delete.cg_delete').each(function (){
            formPostData.append('cg_wp_post_ids[]', parseInt($(this).attr('data-cg-wp-upload-id')));
            data['cg_wp_post_ids'].push(parseInt($(this).attr('data-cg-wp-upload-id')));
            $(this).closest('.cg_ytb_media_container').remove();
        });
        jQuery('body').addClass('cg_pointer_events_none cg_overflow_y_hidden');
        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: data
        }).done(function (response) {
            jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');
            $mediaFrame.find('#cgYoutubeLibraryLoader').addClass('cg_hide');
            if(!$mediaFrame.find('.cg_ytb_media_container').length){
                $mediaFrame.find('#cgYoutubeNoEntries').removeClass('cg_hide');
            }else{
                $mediaFrame.find('.cg_ytb_media_container').removeClass('cg_hide');
            }
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Successfully deleted from library',true);
        }).fail(function (xhr, status, error) {
            jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');

            debugger
            console.log('response error');
            console.log(xhr);
            console.log('status error');
            console.log(status);
            console.log('error error');
            console.log(error);

            return;

        }).always(function () {

            var test = 1;

        });
    });

    var isReclick = false;

    $(document).on('click','.media-frame.cg_backend_area #menu-item-browse.cg_active',function (){
        var $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        if($mediaFrame.find('.cg_media_menu_item.cg_active').length){
            $mediaFrame.find('.cg_media_menu_item.cg_active').removeClass('cg_active');
            $mediaFrameContentSub.removeClass('cg_hide');
            $(this).removeClass('cg_active').addClass('active');
        }
    });

    // has to be after 'click','.media-frame.cg_backend_area #menu-item-browse.cg_active'
    $(document).on('click','.media-frame.cg_backend_area .media-menu-item.cg_active',function (){
        $(this).removeClass('cg_active');
        var $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        $mediaFrame.find('.media-frame-content').removeAttr('style');
    });

    $(document).on('click','.media-frame.cg_backend_area .media-menu-item',function (){
        var $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        $mediaFrame.find('.media-toolbar-primary.search-form').removeClass('cg_hide');
        $mediaFrame.find('.cg_media_container').remove();
        $mediaFrame.find('.cg_media_menu_item').removeClass('cg_active');
    });

});