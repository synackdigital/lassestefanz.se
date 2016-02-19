<?php
$posts = new WP_query('post_type=post&nopaging=1');
?>

<?php hobo_posts_navigation(array('id' => 'nav-above')); ?>

<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>

    <?php get_template_part('content', get_post_type()); ?>

<?php endwhile; wp_reset_postdata(); // End the loop. Whew. ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>

<?php hobo_posts_navigation(array('id' => 'nav-below')); ?>

