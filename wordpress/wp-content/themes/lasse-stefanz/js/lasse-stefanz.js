jQuery(document).ready(function($) {

    $('#hero .wooslider li').each(function() {
        $(this).find('.slide-content').css({
            'background-image': 'url(' + $(this).data('thumb') + ')'
        });
    });

});
