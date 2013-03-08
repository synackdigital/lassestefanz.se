<?php if (has_post_thumbnail( )) : $thumb_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' ) ?>
    <figure>
        <a href="<?php echo $thumb_src[0]; ?>">
            <?php the_post_thumbnail( lc_album_image_size() ); ?>
        </a>
    </figure>
<?php endif; ?>

<?php

$year = lc_album_year();
$label = lc_album_label();
$formats = lc_album_formats();
$tracks = lc_album_tracklisting();

?>


<?php if ($tracks): ?>
    <?php echo $tracks; ?>
<?php endif; ?>

<?php if ($year): ?>
    <p><strong><?php _e('Year', 'lyrics-catalog'); ?></strong>: <?php echo $year; ?></p>
<?php endif; ?>

<?php if ($label): ?>
    <p><strong><?php _e('Label', 'lyrics-catalog'); ?></strong>: <?php echo $label; ?></p>
<?php endif; ?>

<?php if ($formats): ?>
    <p><strong><?php _e('Formats', 'lyrics-catalog'); ?></strong>: <?php echo $formats; ?></p>
<?php endif; ?>
