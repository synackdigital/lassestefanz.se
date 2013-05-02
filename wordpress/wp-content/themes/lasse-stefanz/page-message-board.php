<?php
/*
 * Template Name: Klotterplank
 */

get_header(); ?>

    <div id="container"<?php echo hobo_class_for_element_id('container'); ?>>
        <div id="content"<?php echo hobo_class_for_element_id('content'); ?>>

            <?php get_template_part('loop', 'message-board'); ?>
        </div>
    </div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
