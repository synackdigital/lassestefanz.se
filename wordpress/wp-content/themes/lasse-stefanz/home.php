<?php get_header(); ?>

    <aside id="gigs">
        <h1><?php _e('Upcoming shows', 'lasse-stefanz') ?></h1>
        <?php
          echo do_shortcode('[eo_events event_start_after="today" showpastevents=false]<time>%start{j F}% kl %start{G:i}%</time> &middot; <a class="venue" href="%event_url%">%event_venue%</a>[/eo_events]');
        ?>
    </aside>

    <div id="container"<?php echo hobo_class_for_element_id('container'); ?>>
        <div id="content"<?php echo hobo_class_for_element_id('content'); ?>>
            <?php get_template_part('loop', 'home'); ?>
        </div>
    </div>

    <?php get_sidebar('home-widget-area'); ?>

    <h1><?php _e('Fan photos', 'lasse-stefanz') ?></h1>
    <?php ls_instagram_feed(); ?>

<?php get_footer(); ?>
