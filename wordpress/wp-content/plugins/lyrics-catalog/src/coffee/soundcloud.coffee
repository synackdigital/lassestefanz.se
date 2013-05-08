$ ->

  color = $('#content a:first').css('color')

  $('.soundcloud-player').each ->

    # Name              Default                     Description
    # url               -                           A Soundcloud URL for a track, set, group, user.
    # format            json          (optional)    Either xml, json or js (for jsonp).
    # callback          -             (optional)    A function name for the jsonp callback .
    # maxwidth          100%          (optional)    The maximum width in px.
    # maxheight         81 or 305     (optional)    The maximum height in px. The default is 81 for tracks and 305 for all other.
    # color             -             (optional)    The primary color of the widget as a hex triplet. (For example: ff0066).
    # auto_play         false         (optional)    Whether the widget plays on load.
    # show_comments     true          (optional)    Whether the player displays timed comments.
    # iframe            true          (optional)    Whether the new HTML5 Iframe-based Widget or the old Adobe Flash Widget will be returned.

    container = this
    data = $(this).data()
    data.format = 'json' # Force JSON, naturally. We wan't to be able to load the data later on.

    settings = {
      maxwidth: null
      maxheight: 81
      color: color
      auto_play: 'false'
      show_comments: 'true'
      iframe: 'true'
    }

    for k, v of data
      settings[k] = v

    if not settings?.url?
      return false

    oembed_url = "http://soundcloud.com/oembed"

    json_handler = (data, textStatus, jqXHR) ->
      $(container).html data.html

    $.getJSON oembed_url, settings, json_handler


