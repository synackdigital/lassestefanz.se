<?php
  if ( is_home() && is_active_sidebar( 'home-widget-area' ) ) :

    do_action('hobo_current_widget_area', 'home-widget-area'); ?>

  <aside id="home-sidebar"<?php hobo_class_for_element_id('home-sidebar', 'widget-area'); ?>>
    <ul class="xoxo">
      <?php dynamic_sidebar( 'home-widget-area' ); ?>
    </ul>
  </aside><!-- #home-sidebar .widget-area -->

<?php
  do_action('hobo_current_widget_area', null);

  endif;
?>