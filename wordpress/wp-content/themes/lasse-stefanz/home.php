<?php get_header(); ?>

    <aside id="gigs">
        <h1><?php _e('Upcoming shows', 'lasse-stefanz') ?></h1>
        <?php ls_upcoming_events(); ?>

        <?php if (function_exists('eventorganiser_get_option') && eventorganiser_get_option('feed') && function_exists('eo_get_events_feed')) : ?>
        <p><a href="<?php echo eo_get_events_feed(); ?>"><?php _e('Subscribe to events', 'lasse-stefanz') ?></a></p>
        <?php endif; ?>
    </aside>

    <div id="container"<?php echo hobo_class_for_element_id('container'); ?>>
        <div id="content"<?php echo hobo_class_for_element_id('content'); ?>>
            <?php get_template_part('loop', 'home'); ?>
        </div>
    </div>

    <?php get_sidebar('home-widget-area'); ?>

    <aside id="fanphotos">
        <header>
            <h1><?php _e('Fan photos', 'lasse-stefanz') ?></h1>
            <p><?php _e('Tag a photo with #lassestefanz on Instagram, and we might include it here.', 'lasse-stefanz') ?></p>
        </header>
        <?php ls_instagram_feed(); ?>
    </aside>

<?php get_footer(); ?>
