<?php
/**
 * Widget template. This template can be overriden using the "sp_template_image-widget_widget.php" filter.
 * See the readme.txt file for more info.
 */

// Block direct requests
if ( !defined('ABSPATH') )
    die('-1');

echo $before_widget;

echo $this->get_image_html( $instance, true );

echo $after_widget;
