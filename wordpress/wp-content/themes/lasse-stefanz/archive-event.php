<?php get_header(); ?>

    <div id="container"<?php echo hobo_class_for_element_id('container'); ?>>
        <div id="content"<?php echo hobo_class_for_element_id('content'); ?>>
            <?php ls_upcoming_events(); ?>



            <?php

                hobo_posts_navigation(array(
                    'id' => 'nav-below',
                    'next_posts_link_title' => __('Later gigs', 'lasse-stefanz'),
                    'prev_posts_link_title' => __('Earlier gigs', 'lasse-stefanz'),
                    'reverse_links' => true,
                ));

            ?>
        </div>

        <div id="map">
          <?php echo do_shortcode("[ls_events_map]"); ?>
        </div>

    </div>

<?php get_footer(); ?>
