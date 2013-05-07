<?php
  // Feature posts are designed to look kind of like polaroids
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('feature'); ?>>

  <?php if (!is_single()) : ?><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( '%s', 'lasse-stefanz' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php endif; ?>

    <figure class="entry-image">
      <?php
      if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
        $title = esc_attr( get_the_title() );
        the_post_thumbnail(LS_SQUARE_BANNER_SIZE, array(
          'title' => $title,
          'alt' => $title,
        ));
      }
      ?>
      <figcaption class="entry-title"><?php the_title(); ?></figcaption>
    </figure>

    <div class="entry-meta">
      <?php echo get_the_date(); ?>
    </div><!-- .entry-meta -->

  <?php if (!is_single()) : ?></a><?php endif; ?>

</article><!-- #post-## -->
