<?php
/* Template Name: Nyhetsarkiv */

get_header(); ?>

    <div id="container"<?php echo hobo_class_for_element_id('container'); ?>>
        <div id="content"<?php echo hobo_class_for_element_id('content'); ?>>

        	<?php get_template_part('loop', 'archives'); ?>

        </div>
    </div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
