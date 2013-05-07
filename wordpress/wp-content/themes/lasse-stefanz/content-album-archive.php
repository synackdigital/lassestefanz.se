<article id="post-<?php the_ID(); ?>" <?php post_class(has_post_thumbnail() ? 'has-thumbnail' : 'no-thumbnail'); ?>>

    <figure>
    <?php if (function_exists('lc_album_cover')) {
            lc_album_cover(null, array(
                'size' => LS_ALBUM_PREVIEW_SIZE,
            ));
    } ?>

        <figcaption>

            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>

            <span class="year"><?php echo lc_album_year(); ?></span>

        </figcaption>

    </figure>


</article><!-- #post-## -->
