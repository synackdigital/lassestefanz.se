jQuery(window).load(function() {
    $ = jQuery;

    /* Instagram flexslider */
    $('.instagram-feed.flexslider').flexslider({
        itemWidth: 240,
        itemMargin: 5,
        animation: "slide",
        slideshowSpeed: 3800,
        animationSpeed: 310
    });

    /* Gigs flexslider */
    $('.gigs.flexslider').flexslider({
        itemWidth: 260,
        itemMargin: 0,
        minItems: 2,
        maxItems: 4,
        direction: "horizontal",
        animation: "slide",
        controlNav: false,
        directionNav: false,
        slideshowSpeed: 4200,
        animationSpeed: 280,
        initDelay: 900
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
