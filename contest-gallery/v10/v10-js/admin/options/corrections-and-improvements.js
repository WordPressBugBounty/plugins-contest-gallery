jQuery(document).ready(function($){

    $(document).on('click','#cgCorrect7showExceptionsButton',function () {
        var $button = $(this);
        var isExpanded = $button.attr('aria-expanded') === 'true';
        $button.attr('aria-expanded',isExpanded ? 'false' : 'true');
        $('#cgCorrect7exceptions').toggleClass('cg_hide',isExpanded);
    });

});
