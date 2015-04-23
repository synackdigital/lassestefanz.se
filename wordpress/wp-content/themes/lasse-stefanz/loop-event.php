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
<?php endif; ?>

<?php while ( have_posts() ) : the_post(); ?>

  <?php
    // Set date format
    if ( eo_is_all_day() ) {
      $date_format = 'j F Y';
    }
    else {
      $date_format = 'j F Y, G:i';
    }
  ?>

      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <?php if( eo_get_venue() ): ?>
        <div class="venue-image">
          <?php ls_venue_image(null, array(
            'class' => 'alignright',
          )); ?>
        </div>
        <?php endif; ?>

          <h2 class="entry-title"><?php if (!is_single()) : ?><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'hobo' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php endif; ?><?php the_title(); ?><br><?php eo_the_start($date_format); ?><?php if (!is_single()) : ?></a><?php endif; ?></h2>

  <?php if ( is_archive() || is_search() ) : // Only display excerpts for archives and search. ?>
          <div class="entry-summary">
              <?php the_excerpt(); ?>
          </div><!-- .entry-summary -->
  <?php else : ?>
          <div class="entry-content">
            <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'hobo' ) ); ?>

            <?php if( eo_get_venue() ): ?>
            <hr>
            <h3>Hitta rätt</h3>
            <p>
              <?php
                $url = eo_get_venue_meta(eo_get_venue(), '_url', true);
                if ($url) echo '<a href="'.$url.'" target="_blank" title="'.eo_get_venue_name().'">'
              ?>
              <?php eo_venue_name(); ?><br>
              <?php if ($url) echo '</a>'; ?>
              <?php
               $address = eo_get_venue_address(eo_get_venue());
               echo $address['address'] . ', ' . $address['city'];
               ?>
            </p>
            <?php echo eo_get_venue_map(eo_get_venue(),array('width'=>'100%','height'=>'420px')); ?>
            <?php endif; ?>

          </div><!-- .entry-content -->
  <?php endif; ?>

          <div class="entry-utility">
              <?php hobo_posted_on(); ?>
              <?php edit_post_link( __( 'Edit', 'hobo' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
          </div><!-- .entry-utility -->
      </article><!-- #post-## -->

      <?php comments_template( '', true ); ?>

<?php endwhile; // End the loop. Whew. ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>

<?php hobo_posts_navigation(array('id' => 'nav-below')); ?>

