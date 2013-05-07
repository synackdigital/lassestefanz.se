<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.  The actual display of comments is
 * handled by a callback to hobo_comment which is
 * located in the functions.php file.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>

			<div id="message-board">

				<?php comment_form(array(
					'comment_field' => '<p class="comment-form-comment"><label for="comment">' . __( 'Message', 'lasse-stefanz' ) . '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
					'comment_notes_after' => null,
					'title_reply' => __('Leave a message', 'lasse-stefanz'),
					'title_reply_to' => __("Reply to the message from %s", 'lasse-stefanz'),
					'label_submit' => __('Post message', 'lasse-stefanz'),
					'cancel_reply_link' => __('Cancel', 'lasse-stefanz'),
				)); ?>

<?php if ( post_password_required() ) : ?>
				<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'hobo' ); ?></p>
			</div><!-- #comments -->
<?php
		/* Stop the rest of comments.php from being processed,
		 * but don't kill the script entirely -- we still have
		 * to fully load the template.
		 */
		return;
	endif;
?>

<?php
	// You can start editing here -- including this comment!
?>

<?php if ( have_comments() ) : ?>

			<h3 id="comments-title"><?php _e('Messages', 'lasse-stefanz'); ?></h3>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older messages', 'lasse-stefanz' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer messages <span class="meta-nav">&rarr;</span>', 'lasse-stefanz' ) ); ?></div>
			</div> <!-- .navigation -->
<?php endif; // check for comment navigation ?>



			<ol class="commentlist">
				<?php
					/* Loop through and list the comments. Tell wp_list_comments()
					 * to use hobo_comment() to format the comments.
					 * If you want to overload this in a child theme then you can
					 * define hobo_comment() and that will be used instead.
					 * See hobo_comment() in hobo/functions.php for more.
					 */
					wp_list_comments( array(
						'callback' => 'ls_mb_comment',
						'type' => 'comment',
						'reverse_top_level' => true,
						'max_depth' => 2,
					) );
				?>
			</ol>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older messages', 'lasse-stefanz' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer messages <span class="meta-nav">&rarr;</span>', 'lasse-stefanz' ) ); ?></div>
			</div><!-- .navigation -->
<?php endif; // check for comment navigation ?>

<?php else : // or, if we don't have comments:

	/* If there are no comments and comments are closed,
	 * let's leave a little note, shall we?
	 */
	if ( ! comments_open() ) :
?>
	<p class="nocomments"><?php _e( 'Comments are closed.', 'hobo' ); ?></p>
<?php endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>


</div><!-- #message-board -->
