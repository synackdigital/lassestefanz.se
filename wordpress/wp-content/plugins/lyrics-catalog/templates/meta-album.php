<?php if (has_post_thumbnail( )) : ?>
    <figure>
        <?php the_post_thumbnail( lc_album_image_size() ); ?>
    </figure>
<?php endif; ?>

<p><?php echo lc_album_year() ?></p>
<p><?php echo lc_album_label() ?></p>
<p><?php echo lc_album_formats() ?></p>
