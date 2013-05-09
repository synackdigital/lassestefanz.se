(function() {
  var script, _ref;

  if (!(typeof Modernizr !== "undefined" && Modernizr !== null ? Modernizr.csscolumns : void 0)) {
    script = document.createElement('script');
    script.type = "text/javascript";
    script.src = (_ref = typeof LS !== "undefined" && LS !== null ? LS.css3_multi_column_js_src : void 0) != null ? _ref : '/themes/lasse-stefanz/js/css3-multi-column.min.js';
    $('body').append(script);
  }

  $(window).load(function() {
    var $;

    $ = jQuery;
    $('.instagram-feed.flexslider').flexslider({
      itemWidth: 240,
      itemMargin: 5,
      animation: "slide",
      slideshowSpeed: 3800,
      animationSpeed: 310
    });
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
    $('#hero .flexslider').flexslider({
      controlNav: false,
      directionNav: false
    });
    $('a[href*=".jpg"], a[href*=".jpeg"], a[href*=".png"], a[href*=".gif"]').fancybox({
      transitionIn: 'elastic',
      transitionOut: 'elastic',
      speedIn: 200,
      speedOut: 200
    });
    return $('.entry-content a img').each(function() {
      return $(this).wrap('<div class="image-tint"></div>');
    });
  });

}).call(this);
