<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
        if (function_exists('lc_album_cover')) {
            lc_album_cover();
        }
    ?>
</article><!-- #post-## -->
