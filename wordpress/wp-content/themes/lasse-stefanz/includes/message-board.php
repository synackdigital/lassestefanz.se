<?php

function ls_mb_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case '' :
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <div id="comment-<?php comment_ID(); ?>">
        <div class="comment-author vcard">
            <?php
                $who = $depth > 1 ? __(get_bloginfo('name')) : get_comment_author_link();
                $verb = $depth > 1 ? __('answers', 'lasse-stefanz') : __('says', 'lasse-stefanz');
             ?>
            <?php printf( __( '%s <span class="says">%s:</span>', 'hobo' ), sprintf( '<cite class="fn">%s</cite>', $who ), $verb ); ?>
        </div><!-- .comment-author .vcard -->
        <?php if ( $comment->comment_approved == '0' ) : ?>
            <em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'hobo' ); ?></em>
            <br />
        <?php endif; ?>

        <?php if (false) : // quick toggling FTW! ?>
        <div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
            <?php
                if ($depth < 2) {
                    // translators: 1: date, 2: time
                    printf( __( '%1$s at %2$s', 'lasse-stefanz' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( 'Edit', 'lasse-stefanz' ), ' ' );
                }
            ?>
        </div><!-- .comment-meta .commentmetadata -->
        <?php endif;  // quick toggling FTW! ?>

        <div class="comment-body"><?php comment_text(); ?></div>

        <?php if (is_user_logged_in() && current_user_can( 'moderate_comments' )) : ?>
        <div class="reply">
            <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
        </div><!-- .reply -->
        <?php endif; ?>

    </div><!-- #comment-##  -->

    <?php
            break;
        case 'pingback'  :
        case 'trackback' :
    ?>
    <li class="post pingback">
        <p><?php _e( 'Pingback:', 'hobo' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'hobo' ), ' ' ); ?></p>
    <?php
            break;
    endswitch;
}
