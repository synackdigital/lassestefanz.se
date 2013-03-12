jQuery(document).ready(function($) {

    var preview_img = new Image();
    preview_img.width = 300;
    preview_img.height = 300;

    $(preview_img).addClass('instagram-preview').hide();
    $('body').append(preview_img);


    $(document).mousemove(function(e){

        var offset = 10;

        $(preview_img).filter(':visible').css({
            top: e.pageY + offset,
            left: e.pageX + offset
        });
    });

    $('#the-list .column-instagram_image img.instagram-image').hover(
        function (e) {
            var url = $(this).data('url');
            preview_img.src = url;

            $(preview_img).show();

            console.log("Show");
        },
        function (e) {
            $(preview_img).hide();

            console.log("Hide");
        }
    );

});
