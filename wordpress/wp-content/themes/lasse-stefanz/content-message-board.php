<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <h2 class="entry-title"><?php if (!is_single()) : ?><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'hobo' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php endif; ?><?php the_title(); ?><?php if (!is_single()) : ?></a><?php endif; ?></h2>


  <div class="entry-content">
    <?php the_content(); ?>
    <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'hobo' ), 'after' => '</div>' ) ); ?>
    <?php edit_post_link( __( 'Edit', 'hobo' ), '<span class="edit-link">', '</span>' ); ?>
  </div><!-- .entry-content -->

  <?php comments_template( '/comments-message-board.php', true ); ?>
</article><!-- #post-## -->
