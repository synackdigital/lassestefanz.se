<?php

$year = lc_album_year();
$label = lc_album_label();
$formats = lc_album_formats();
$tracks = lc_album_tracklisting();
$player = lc_soundcloud_player();

?>

<div class="album-content">

    <?php lc_album_cover(null, array('link' => 'image')); ?>

    <dl class="album-meta">
        <?php if ($year): ?>
            <dt class="album-meta album-year"><?php _e('Year', 'lyrics-catalog'); ?></dt><dd><?php echo $year; ?></dd>
        <?php endif; ?>

        <?php if ($label): ?>
            <dt class="album-meta album-label"><?php _e('Label', 'lyrics-catalog'); ?></dt><dd><?php echo $label; ?></dd>
        <?php endif; ?>

        <?php if ($formats): ?>
            <dt class="album-meta album-formats"><?php _e('Formats', 'lyrics-catalog'); ?></dt><dd><?php echo $formats; ?></dd>
        <?php endif; ?>

    </dl>
</div>


<?php if ($tracks): ?>
    <div class="album-track-list">
        <h3 class="album-track-list-heading"><? _e('Track listing', 'lyrics-catalog'); ?></h3>
        <?php echo $tracks; ?>
    </div>
<?php endif; ?>


<?php if ($player) : ?>
    <?php echo $player; ?>
<?php endif; ?>
