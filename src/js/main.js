(function() {
  var script, _ref;

  if (!(typeof Modernizr !== "undefined" && Modernizr !== null ? Modernizr.csscolumns : void 0)) {
    script = document.createElement('script');
    script.type = "text/javascript";
    script.src = (_ref = typeof LS !== "undefined" && LS !== null ? LS.css3_multi_column_js_src : void 0) != null ? _ref : '/themes/lasse-stefanz/js/css3-multi-column.min.js';
    $('body').append(script);
  }

  $(function() {
    var $, hamburger, _ref1;

    $ = jQuery;
    $('#access nav > .menu > li.current-menu-item, #access nav > .menu > li.current-menu-ancestor').each(function() {
      return $(this).find('ul.sub-menu').clone().prependTo('#content').wrap('<nav class="page-menu" />');
    });
    $('body').prepend('<nav id="hamburger"><button class="toggle">' + ((_ref1 = typeof LS !== "undefined" && LS !== null ? LS.menu_title : void 0) != null ? _ref1 : 'Content') + '</button><ul class="menu"/></nav>');
    hamburger = $('#hamburger ul');
    $('#access nav .menu').children().each(function() {
      return $(hamburger).append($(this).clone());
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

  $(window).load(function() {
    var $, client_width, narrow_treshold;

    $ = jQuery;
    narrow_treshold = 480;
    client_width = document.body.clientWidth;
    $('.instagram-feed.flexslider').flexslider({
      itemWidth: 240,
      itemMargin: 5,
      animation: "slide",
      slideshowSpeed: 3800,
      animationSpeed: 310
    });
    if (client_width > narrow_treshold) {
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
    }
    return $('#hero .flexslider').flexslider({
      controlNav: false,
      directionNav: false
    });
  });

}).call(this);
