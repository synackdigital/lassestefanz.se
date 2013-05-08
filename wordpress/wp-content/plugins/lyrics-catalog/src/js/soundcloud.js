(function() {
  $(function() {
    var color;

    color = $('#content a:first').css('color');
    return $('.soundcloud-player').each(function() {
      var container, data, json_handler, k, oembed_url, settings, v;

      container = this;
      data = $(this).data();
      data.format = 'json';
      settings = {
        maxwidth: null,
        maxheight: 81,
        color: color,
        auto_play: 'false',
        show_comments: 'true',
        iframe: 'true'
      };
      for (k in data) {
        v = data[k];
        settings[k] = v;
      }
      if ((settings != null ? settings.url : void 0) == null) {
        return false;
      }
      oembed_url = "http://soundcloud.com/oembed";
      json_handler = function(data, textStatus, jqXHR) {
        return $(container).html(data.html);
      };
      return $.getJSON(oembed_url, settings, json_handler);
    });
  });

}).call(this);
