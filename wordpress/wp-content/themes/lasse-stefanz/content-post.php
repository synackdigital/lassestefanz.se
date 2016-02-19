    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <div class="featured-content">
            <?php ls_featured_content(); ?>
        </div>

        <h2 class="entry-title"><?php if (!is_single()) : ?><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'hobo' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php endif; ?><?php the_title(); ?><?php if (!is_single()) : ?></a><?php endif; ?></h2>

<?php if ( is_page_template('tpl_archives.php') || is_archive() || is_search() ) : // Only display excerpts for archives and search. ?>
        <div class="entry-summary">
            <?php the_excerpt(); ?>
        </div><!-- .entry-summary -->
<?php else : ?>
        <div class="entry-content">
            <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'hobo' ) ); ?>
            <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'hobo' ), 'after' => '</div>' ) ); ?>
        </div><!-- .entry-content -->
<?php endif; ?>

        <div class="entry-utility">
            <?php the_category(', '); ?>
            (<?php hobo_posted_on(); ?>)
        </div><!-- .entry-utility -->
    </article><!-- #post-## -->

    <?php comments_template( '', true ); ?>
