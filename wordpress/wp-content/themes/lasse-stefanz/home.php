<?php get_header(); ?>

    <aside id="gigs">
        <h1><?php _e('Upcoming shows', 'lasse-stefanz') ?></h1>
        <div class="gigs flexslider carousel">
            <?php ls_upcoming_events(); ?>
        </div>
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
            <?php
                if (class_exists('LasseStefanz')) {
                    echo wpautop( LasseStefanz::instagram_instructions() );
                }
            ?>
        </header>
        <?php ls_instagram_feed(); ?>
    </aside>

<?php get_footer(); ?>
