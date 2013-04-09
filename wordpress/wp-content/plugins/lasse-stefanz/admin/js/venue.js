function LSQueryStringParameter(key) {
    key = key.replace(/[*+?^$.\[\]{}()|\\\/]/g, "\\$&"); // escape RegEx meta chars
    var match = location.search.match(new RegExp("[?&]"+key+"=([^&]+)(&|$)"));
    return match && decodeURIComponent(match[1].replace(/\+/g, " "));
}

LSSetThumbnailHTML = function(html) {
    $('.inside', '#ls_venue_image').html(html);
};

LSSetThumbnailID = function(id) {
    var field = $('input[value="_thumbnail_id"]', '#list-table');
    if ( field.size() > 0 ) {
        $('#meta\\[' + field.attr('id').match(/[0-9]+/) + '\\]\\[value\\]').text(id);
    }
};


function LSSetAsVenueThumbnail(id, nonce){
    var $link = jQuery('a#wp-venue-thumbnail-' + id);

    venue_id = LSQueryStringParameter('venue_id');

    $link.text( setVenueThumbnailL10n.saving );
    jQuery.post(ajaxurl, {
        action: "set_venue_thumbnail",
        venue_id: venue_id,
        thumbnail_id: id,
        _ajax_nonce: nonce,
        cookie: encodeURIComponent(document.cookie)
    }, function(str) {
        var win = window.dialogArguments || opener || parent || top;
        $link.text( setVenueThumbnailL10n.setThumbnail );
        if ( str == '0' ) {
            alert( setVenueThumbnailL10n.error );
        } else {
            jQuery('a.wp-post-thumbnail').show();
            $link.text( setVenueThumbnailL10n.done );
            $link.fadeOut( 2000 );
            win.LSSetThumbnailID(id);
            win.LSSetThumbnailHTML(str);
        }
    }
    );
}
