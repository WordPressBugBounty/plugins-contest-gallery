cgJsClassAdmin.gallery.pdf = cgJsClassAdmin.gallery.pdf || {};
cgJsClassAdmin.gallery.pdf = {
    createAndSetPdfPreviewPrepare: function ($, cgPdfPreviewsToCreateString, isFromReloadEntry,$response) {
        debugger
        var pdfWpUploadsToPreview = cgPdfPreviewsToCreateString.split('cg-pdf-previews-to-create###')[1];
        pdfWpUploadsToPreview = pdfWpUploadsToPreview.split('###cg-pdf-previews-to-create-end')[0];
        pdfWpUploadsToPreview = pdfWpUploadsToPreview.split(',');

        var percentage = parseFloat(parseFloat(100 / (pdfWpUploadsToPreview.length+1)).toFixed(2));

        $('#cgPdfPreviewProgress').text(percentage + '%');
        $('#cgPdfPreviewGeneration').removeClass('cg_hide');
        var cgWpUploadToReplace = $response.find('#cgWpUploadToReplaceForPdfPreview').val();
        var cgNewWpUploadWhichReplace = $response.find('#cgNewWpUploadWhichReplaceForPdfPreview').val();
        debugger
        cgJsClassAdmin.gallery.vars.pdfPreviewsCreated = 0;

        for (var index in pdfWpUploadsToPreview) {
            if (!pdfWpUploadsToPreview.hasOwnProperty(index)) {
                break;
            }
            var realId = pdfWpUploadsToPreview[index].split(';')[0];
            var WpUpload = pdfWpUploadsToPreview[index].split(';')[1];
            var url = pdfWpUploadsToPreview[index].split(';')[2];
            debugger
            if (pdfWpUploadsToPreview[index].split(';')[3]) {
                var pdfPreviewUrl = pdfWpUploadsToPreview[index].split(';')[3];
                debugger
                cgJsClassAdmin.gallery.pdf.setPdfPreview($, realId, WpUpload, pdfPreviewUrl, url, pdfWpUploadsToPreview.length,isFromReloadEntry);
            } else {
                debugger
                cgJsClassAdmin.gallery.pdf.createAndSetPdfPreview($, realId, WpUpload, url, pdfWpUploadsToPreview.length,cgWpUploadToReplace,cgNewWpUploadWhichReplace);
            }

        }

        $('#cgPdfPreviewsToCreateString').val('');// reset

    },
    setPdfPreview: function ($, realId, WpUpload, pdfPreviewUrl, pdfUrl, totalToRenderCount,isFromReloadEntry) {
        debugger
        var $sortableDiv = $('.cg_sortable_div[data-cg-real-id="' + realId + '"]');
        $sortableDiv.find('.cg_create_pdf_preview').removeClass('cg_hide');
        $sortableDiv.find('.cg_backend_image_full_size_target').addClass('cg_hide');

        if (($sortableDiv.find('.cg_backend_info_container').attr('data-cg-wp-upload') == WpUpload) || (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1] && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['WpUpload'] == WpUpload)) {
            $sortableDiv.find('.cg_backend_image_full_size_target > a').attr('href', pdfUrl).empty().append('<div class="cg0degree cg_backend_image" style="background: url(' + pdfPreviewUrl + '?time=' + new Date().getTime() + ') no-repeat center" ></div>');
        }

        debugger
        cgJsClassAdmin.gallery.vars.pdfPreviewsCreated++;
        if (cgJsClassAdmin.gallery.vars.pdfPreviewsCreated == totalToRenderCount) {
            cgJsClassAdmin.gallery.vars.pdfPreviewsCreated = 0;// reset
            $('#cgPdfPreviewGeneration').addClass('cg_hide');
            $('#cgPdfGenerationFinished').removeClass('cg_hide_slow cg_hide').addClass('cg_hide_slow');
            // use simply for all then
            $('#cgSortable .cg_create_pdf_preview').addClass('cg_hide');
            $('#cgSortable .cg_backend_image_full_size_target').removeClass('cg_hide');
            //if(isFromReloadEntry){// then easiest way to simply reload again, because MultipleFiles might be changed
            cgJsClassAdmin.gallery.reload.entry($sortableDiv.attr('data-cg-real-id'));
            // }
        }

    },
    createAndSetPdfPreview: async function ($, realId, WpUpload, pdfUrl, totalToRenderCount,cgWpUploadToReplace,cgNewWpUploadWhichReplace) {
        debugger
        var cg_admin_url = $("#cg_admin_url").val();
        var $sortableDiv = $('.cg_sortable_div[data-cg-real-id="' + realId + '"]');
        $sortableDiv.find('.cg_create_pdf_preview').removeClass('cg_hide');
        $sortableDiv.find('.cg_backend_image_full_size_target').addClass('cg_hide');

        var loadingTask = PDFJS.getDocument(pdfUrl);
        loadingTask.promise.then(function (pdf) {
            // you can now use *pdf* here
            pdf.getPage(1).then(function (page) {
                debugger
                //var canvas = document.querySelector("#canvas");
                var canvas = document.createElement("canvas");
                var viewport = page.getViewport(2.6);// seems to be best value size/quality comparison
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                canvas.style.width = "100%";
                canvas.style.height = "100%";
                console.log('viewport')
                console.log(viewport)
                console.log('canvas')
                console.log(canvas.height)

                // https://mozilla.github.io/pdf.js/examples/
                var renderTask = page.render({
                    canvasContext: canvas.getContext('2d'),
                    viewport: viewport
                });

                renderTask.promise.then(function () {
                    debugger
                    console.log('rendered');

                    const cg_base_64 = canvas.toDataURL().split(';base64,')[1];

                    console.log('cg_base_64');
                    console.log(cg_base_64);

                    $.ajax({
                        url: cg_admin_url + "admin-ajax.php",
                        type: 'post',
                        data: {
                            action: 'post_cg_create_pdf_preview_backend',
                            cg_wp_upload: WpUpload,
                            cgRealId: realId,
                            cg_base_64: cg_base_64,
                            cgWpUploadToReplace: cgWpUploadToReplace,
                            cgNewWpUploadWhichReplace: cgNewWpUploadWhichReplace,
                            cg_nonce: CG1LBackendNonce.nonce
                        },
                    }).done(function (response) {
                        debugger
                        var guid = response.split('cg_guid###')[1];
                        guid = guid.split('###cg_guid_end')[0];

                        if (($sortableDiv.find('.cg_backend_info_container').attr('data-cg-wp-upload') == WpUpload) || (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1] && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['WpUpload'] == WpUpload)) {
                            $sortableDiv.find('.cg_backend_image_full_size_target > a').attr('href', pdfUrl).empty().append('<div class="cg0degree cg_backend_image" style="background: url(' + guid + '?time=' + new Date().getTime() + ') no-repeat center" ></div>');
                        }

                        cgJsClassAdmin.gallery.vars.pdfPreviewsCreated++;
                        if (cgJsClassAdmin.gallery.vars.pdfPreviewsCreated == totalToRenderCount) {
                            cgJsClassAdmin.gallery.vars.pdfPreviewsCreated = 0;// reset
                            $('#cgPdfPreviewGeneration').addClass('cg_hide');
                            $('#cgPdfGenerationFinished').removeClass('cg_hide_slow cg_hide').addClass('cg_hide_slow');
                            // use simply for all then
                            $('#cgSortable .cg_create_pdf_preview').addClass('cg_hide');
                            $('#cgSortable .cg_backend_image_full_size_target').removeClass('cg_hide');
                            //if(isFromReloadEntry){// then easiest way to simply reload again, because MultipleFiles might be changed
                            cgJsClassAdmin.gallery.reload.entry($sortableDiv.attr('data-cg-real-id'));
                            //}
                        }

                    }).fail(function (xhr, status, error) {

                        console.log(xhr);
                        console.log(status);
                        console.log(error);

                    }).always(function () {

                    });

                });

            });
        });


    },
};