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
        animation: "slide",
        controlNav: false,
        directionNav: false,
        itemWidth: 269,
        itemMargin: 5,
        slideshowSpeed: 7000,
        animationSpeed: 600
    });

    $('#hero .flexslider').flexslider({
        controlNav: false,
        directionNav: false
    });

});
