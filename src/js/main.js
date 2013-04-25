jQuery(window).load(function() {
    $ = jQuery;

    /* Instagram feed */
    $('.instagram-feed.flexslider').flexslider({
        animation: "slide",
        controlNav: false,
        itemWidth: 269,
        itemMargin: 5
    });

    /* Gigs list */
    $('.gigs.flexslider').flexslider({
        direction: "vertical",
        animation: "slide",
        controlNav: false,
        directionNav: false,
        slideshowSpeed: 4000,
        animationSpeed: 300
    });

    $('#hero .flexslider').flexslider({
        controlNav: false,
        directionNav: false
    });

});
