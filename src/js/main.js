jQuery(window).load(function() {
    $ = jQuery;

    /* Instagram flexslider */
    $('.instagram-feed.flexslider').flexslider({
        animation: "slide",
        controlNav: false,
        itemWidth: 269,
        itemMargin: 5
    });

    /* Gigs flexslider */
    $('.gigs.flexslider').flexslider({
        direction: "vertical",
        animation: "slide",
        controlNav: false,
        directionNav: false,
        slideshowSpeed: 4000,
        animationSpeed: 300
    });

    /* Hero flexslider */
    $('#hero .flexslider').flexslider({
        controlNav: false,
        directionNav: false
    });

    /* Fancybox */
    $('a[href*=".jpg"], a[href*=".jpeg"], a[href*=".png"], a[href*=".gif"]').fancybox({
        'transitionIn'  :   'elastic',
        'transitionOut' :   'elastic',
        'speedIn'       :   200,
        'speedOut'      :   200
    });

});
