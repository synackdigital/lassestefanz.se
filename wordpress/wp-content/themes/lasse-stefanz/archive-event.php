<?php get_header(); ?>

    <div id="container"<?php echo hobo_class_for_element_id('container'); ?>>
        <div id="content"<?php echo hobo_class_for_element_id('content'); ?>>
            <?php ls_upcoming_events(); ?>
        </div>

        <div id="map">
          <?php echo do_shortcode("[ls_events_map]"); ?>
        </div>

    </div>

<?php get_footer(); ?>