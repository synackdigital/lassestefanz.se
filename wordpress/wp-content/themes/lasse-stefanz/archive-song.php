<?php get_header(); ?>

    <div id="container"<?php echo hobo_class_for_element_id('container'); ?>>
        <div id="content"<?php echo hobo_class_for_element_id('content'); ?>>


            <ul class="songs">
            <?php $initial = null; while ( have_posts() ) : the_post(); ?>
                <?php

                $title = get_the_title(get_the_ID());
                $new_section = $initial != mb_strtolower(mb_substr($title, 0, 1));
                $initial = mb_strtolower(mb_substr($title, 0, 1));

                if ($new_section) {
                    ?>
                    <li id="<?php echo urlencode($initial); ?>" class="header order-<?php echo urlencode($initial); ?>"><h2><?php echo mb_strtoupper($initial); ?></h2></li>
                    <?php
                }

                ?>
                <li class="order-<?php echo urlencode($initial) . ($new_section ? ' first' : '') ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
            <?php endwhile; // End the loop. Whew. ?>
            </ul>



        </div>
    </div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
