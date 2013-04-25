<?php
/**
 * Widget template. This template can be overriden using the "sp_template_image-widget_widget.php" filter.
 * See the readme.txt file for more info.
 */

// Block direct requests
if ( !defined('ABSPATH') )
    die('-1');

echo $before_widget; ?>

<figure>
    <?php if ($link) : ?><a href="<?php echo $link; ?>"><?php endif; ?>

    <?php echo $this->get_image_html( $instance, false ); ?>
    <?php if ( !empty( $title ) ) : ?>
        <figcaption><?php echo $title; ?></figcaption>
    <?php endif; ?>

    <?php if ($link) : ?></a><?php endif; ?>
</figure>

<?php echo $after_widget;
