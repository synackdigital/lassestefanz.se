<article id="post-<?php the_ID(); ?>" <?php post_class('feature'); ?>>

  <?php if (!is_single()) : ?><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'hobo' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php endif; ?>

    <h2 class="entry-title"><?php the_title(); ?></h2>

    <figure class="entry-image">
      <?php
      if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
        the_post_thumbnail();
      }
      ?>
    </figure>

    <div class="entry-meta">
      <?php the_date(); ?>
    </div><!-- .entry-meta -->

  <?php if (!is_single()) : ?></a><?php endif; ?>

</article><!-- #post-## -->