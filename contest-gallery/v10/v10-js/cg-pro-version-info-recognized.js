jQuery(document).ready(function ($) {

    $(document).on('click','.cg_pro_version_info_close_button',function (e) {

        var $element = $(this);

        var $cg_pro_version_info_container_header = $element.closest('.cg_pro_version_info_container_header');// header is over parent, is main element which contains infos
        var $cg_pro_version_info_container_parent = $element.closest('.cg_pro_version_info_container_parent');

        var cgProVersionInfoId = $(this).closest('.cg_pro_version_info_container').attr('data-cg-pro-version-info-id');

        var cgProVersionInfoHash = $('#cgProVersionInfoHash').val();

        if(!cgProVersionInfoHash){
            cgProVersionInfoHash = '';
        }

        var removeInfos = function (){

            $cg_pro_version_info_container_parent.remove();

            if($cg_pro_version_info_container_header.length){

                if(!$cg_pro_version_info_container_header.find('.cg_pro_version_info_container_parent').length){

                    $cg_pro_version_info_container_header.remove();

                }

            }

        }

        jQuery.ajax({
            url: post_cg_pro_version_info_recognized_wordpress_ajax_script_function_name.cg_pro_version_info_recognized_ajax_url,
            method: 'post',
            data : {
                action : 'post_cg_pro_version_info_recognized',
                cgProVersionInfoId : cgProVersionInfoId,
                cgProVersionInfoHash : cgProVersionInfoHash,
            }
        }).done(function(response) {

            var parser = new DOMParser();
            var parsedHtml = parser.parseFromString(response, 'text/html');
            jQuery(parsedHtml).find('script[data-cg-processing="true"]').each(function () {

                var script = jQuery(this).html();
                eval(script);

            });

            removeInfos();

        }).fail(function(xhr, status, error) {

            console.log('Something went wrong removing pro version info.');

            removeInfos();

        }).always(function() {

        });

    });


});