jQuery(document).ready(function($) {

    $('#hero .wooslider li').each(function() {
        $(this).find('.slide-content').css({
            'background-image': 'url(' + $(this).data('thumb') + ')'
        });
    });

    // $('.instagram-feed').flexslider2();

    // return;

    // $('.instagram-feed').flexslider2({
    //     animation: "li",
    //     animationLoop: false,
    //     itemWidth: 269,
    //     itemMargin: 5
    // });

});


jQuery(window).load(function() {
    $ = jQuery;

    $('.instagram-feed').flexslider({
        animation: "slide",
        animationLoop: true,
        itemWidth: 269,
        itemMargin: 5
    });
});
