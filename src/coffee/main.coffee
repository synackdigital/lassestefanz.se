# CSS3 Columns polyfill - CAN I PLEASE ADD THE SCRIPTS TO THE BOTTOM OF THE PAGE?!?
# Modernizr.load({
#     test: Modernizr.csscolumns,
#     yep: 'http://www.branning.se/wordpress/wp-content/themes/branning/js/libs/css3-multi-column.js',
#     nope: 'http://www.branning.se/wordpress/wp-content/themes/branning/js/debug.js' //OBtheme.CSS3ColumnPolyfill
# });

# CSS3 Columns polyfill
if !Modernizr?.csscolumns
  script = document.createElement 'script'
  script.type = "text/javascript"
  script.src = LS?.css3_multi_column_js_src ? '/themes/lasse-stefanz/js/css3-multi-column.min.js'
  $('body').append script


$(window).load ->
  $ = jQuery

  # Instagram flexslider
  $('.instagram-feed.flexslider').flexslider
    itemWidth: 240,
    itemMargin: 5,
    animation: "slide",
    slideshowSpeed: 3800,
    animationSpeed: 310


  # Gigs flexslider
  $('.gigs.flexslider').flexslider
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

  # Hero flexslider
  $('#hero .flexslider').flexslider
    controlNav: false,
    directionNav: false

#     # Fancybox
  $('a[href*=".jpg"], a[href*=".jpeg"], a[href*=".png"], a[href*=".gif"]').fancybox
    transitionIn    :   'elastic',
    transitionOut   :   'elastic',
    speedIn         :   200,
    speedOut        :   200

  # Enable hover tint on linked images in posts
  $('.entry-content a img').each ->
    $(this).wrap '<div class="image-tint"></div>'

