  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <h2 class="entry-title"><?php if (!is_single()) : ?><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'hobo' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php endif; ?><?php the_title(); ?><?php if (!is_single()) : ?></a><?php endif; ?></h2>

    <div class="entry-meta">
      <?php hobo_posted_on(); ?>
    </div><!-- .entry-meta -->

<?php if ( is_archive() || is_search() ) : // Only display excerpts for archives and search. ?>
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
      <?php if ( count( get_the_category() ) ) : ?>
        <span class="cat-links">
          <?php printf( __( '<span class="%1$s">Posted in</span> %2$s', 'hobo' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?>
        </span>
      <?php endif; ?>
      <?php
        $tags_list = get_the_tag_list( '', ', ' );
        if ( $tags_list ):
      ?>
        <span class="meta-sep">|</span>
        <span class="tag-links">
          <?php printf( __( '<span class="%1$s">Tagged</span> %2$s', 'hobo' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
        </span>
        <span class="meta-sep">|</span>
      <?php endif; ?>
      <span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'hobo' ), __( '1 Comment', 'hobo' ), __( '% Comments', 'hobo' ) ); ?></span>
      <?php edit_post_link( __( 'Edit', 'hobo' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
    </div><!-- .entry-utility -->
  </article><!-- #post-## -->

  <?php comments_template( '', true ); ?>