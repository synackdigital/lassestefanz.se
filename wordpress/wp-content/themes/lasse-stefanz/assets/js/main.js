jQuery(document).ready(function($) {

    $('#hero .wooslider li').each(function() {
        console.log('url(' + $(this).data('thumb') + ')');
        $(this).css({
            'background-image': 'url(' + $(this).data('thumb') + ')'
        });
    });

});
