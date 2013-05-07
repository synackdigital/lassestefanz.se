<?php hobo_posts_navigation(array('id' => 'nav-above')); ?>

<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
        <article id="post-0" class="hentry post error404 not-found">
        <h1 class="entry-title"><?php _e( 'Not Found', 'hobo' ); ?></h1>
        <div class="entry-content">
            <p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'hobo' ); ?></p>
            <?php get_search_form(); ?>
        </div><!-- .entry-content -->
    </article><!-- #post-0 -->
<?php else: ?>

<ul class="songs">
<?php $initial = null; while ( have_posts() ) : the_post(); ?>
    <?php

    $title = get_the_title(get_the_ID());
    $new_section = $initial != strtolower(mb_substr($title, 0, 1));
    $initial = strtolower(mb_substr($title, 0, 1));

    if ($new_section) {
        ?>
        <li id="<?php echo $initial; ?>" class="header order-<?php echo $initial; ?>"><h2><?php echo strtoupper($initial); ?></h2></li>
        <?php
    }

    ?>
    <li class="order-<?php echo $initial . ($new_section ? ' first' : '') ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
<?php endwhile; // End the loop. Whew. ?>
</ul>

<?php endif; ?>


<?php /* Display navigation to next/previous pages when applicable */ ?>

<?php hobo_posts_navigation(array('id' => 'nav-below')); ?>

