<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
      <article id="post-0" class="hentry post error404 not-found">
    <h1 class="entry-title"><?php _e( 'Not Found', 'hobo' ); ?></h1>
    <div class="entry-content">
      <p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'hobo' ); ?></p>
      <?php get_search_form(); ?>
    </div><!-- .entry-content -->
  </article><!-- #post-0 -->
<?php endif; ?>

<?php while ( have_posts() ) : the_post(); ?>

    <?php get_template_part('content', 'message-board'); ?>

<?php endwhile; // End the loop. Whew. ?>

