<?php lc_album_cover(null, array('link' => 'image')); ?>

<?php

$year = lc_album_year();
$label = lc_album_label();
$formats = lc_album_formats();
$tracks = lc_album_tracklisting();
$player = lc_soundcloud_player();

?>


<?php if ($tracks): ?>
    <h3 class="album-track-list-heading"><? _e('Track listing', 'lyrics-catalog'); ?></h3>
    <?php echo $tracks; ?>
<?php endif; ?>

<?php if ($year): ?>
    <p class="album-meta album-year"><strong><?php _e('Year', 'lyrics-catalog'); ?></strong>: <?php echo $year; ?></p>
<?php endif; ?>

<?php if ($label): ?>
    <p class="album-meta album-label"><strong><?php _e('Label', 'lyrics-catalog'); ?></strong>: <?php echo $label; ?></p>
<?php endif; ?>

<?php if ($formats): ?>
    <p class="album-meta album-formats"><strong><?php _e('Formats', 'lyrics-catalog'); ?></strong>: <?php echo $formats; ?></p>
<?php endif; ?>

<?php if ($player) : ?>
    <?php echo $player; ?>
<?php endif; ?>
