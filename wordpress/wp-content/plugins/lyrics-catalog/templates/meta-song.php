<?php
$album = lc_song_album();
$authors = lc_song_authors();
$publisher = lc_song_publisher();
?>

<?php if ($authors) : ?>
    <p><strong><?php _e('Author', 'lyrics-catalog'); ?></strong>: <?php echo $authors; ?></p>
<?php endif; ?>

<?php if ($publisher) : ?>
    <p><strong><?php _e('Publisher', 'lyrics-catalog'); ?></strong>: <?php echo $publisher; ?></p>
<?php endif; ?>

<?php if ($album) : ?>
    <p><?php _e('From the album', 'lyrics-catalog'); ?> <?php echo $album; ?></p>
<?php endif; ?>
