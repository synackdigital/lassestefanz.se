<?php
$album = lc_song_album();
$authors = lc_song_authors();
$publisher = lc_song_publisher();
?>

<?php if ($authors) : ?>
    <p class="song-meta song-author"><strong><?php _e('Author', 'lyrics-catalog'); ?></strong>: <?php echo $authors; ?></p>
<?php endif; ?>

<?php if ($publisher) : ?>
    <p class="song-meta song-publisher"><strong><?php _e('Publisher', 'lyrics-catalog'); ?></strong>: <?php echo $publisher; ?></p>
<?php endif; ?>

<?php if ($album) : ?>
    <p class="song-meta song-album"><?php _e('From the album', 'lyrics-catalog'); ?> <?php echo $album; ?></p>
<?php endif; ?>
