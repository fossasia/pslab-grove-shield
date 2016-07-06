<?php

// Do not delete these lines
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if ( post_password_required() ) { ?>
		<p class="nocomments"><?php _e('This post is password protected. Enter the password to view comments', 'default'); ?>.</p>
	<?php
		return;
	}
?>

<!-- You can start editing here. -->

<?php if ( have_comments() ) : ?>
<?php if ( ! empty($comments_by_type['comment']) ) : ?>

  <div class="comments-header clearfix">
  	<h3 id="comments"><?php comments_number( __( 'No comments', 'default' ), __( '1 comment', 'default' ), __( '% comments', 'default' )); ?></h3>
    <div class="comments-header-meta">
      <a href="#respond"><?php _e('Add your comment', 'default'); ?></a>
      <!--
      |
      <a href="<?php trackback_url(); ?>" rel="trackback">trackback</a>
      -->
    </div>

  </div> <!-- comments-header -->


	<div class="navigation">
		<?php // previous_comments_link('<div class="alignleft">&laquo; Older Comments</div>'); ?>
		<?php // next_comments_link('<div class="alignright">Newer Comments &raquo;</div>'); ?>
		
		<div class="alignleft"><?php previous_comments_link(__('&laquo; Older Comments', 'default')); ?></div>
		<div class="alignright"><?php next_comments_link(__('Newer Comments &raquo;', 'default')); ?></div>
	</div>

  <ol class="commentlist">
  <?php wp_list_comments('type=comment'); ?>
  </ol>


	<div class="navigation">
		<div class="alignleft"><?php previous_comments_link(__('&laquo; Older Comments', 'default')); ?></div>
		<div class="alignright"><?php next_comments_link(__('Newer Comments &raquo;', 'default')); ?></div>
	</div>

<?php endif; ?>
	
	

<?php if ( ! empty($comments_by_type['pings']) ) : ?>
  <div class="comments-header clearfix">
    <h4 id="pings">
      <?php _e('Trackbacks', 'default'); ?>
      /<br/>
      <?php _e('Pingbacks', 'default'); ?>
    </h4>
    <ol class="pinglist">
    <?php wp_list_comments('type=pings&callback=list_pings'); ?>
    </ol>
  </div>
<?php endif; ?>



<?php else : // this is displayed if there are no comments so far ?>

	<?php if ('open' == $post->comment_status) : ?>
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments"><?php _e('Comments are closed', 'default'); ?>.</p>

	<?php endif; ?>
<?php endif; ?>


<?php if ('open' == $post->comment_status) : ?>

  <div id="respond">
    <h3><?php comment_form_title(__('Leave a Reply', 'default' ), __( 'Leave a Reply to %s', 'default')); ?></h3>
    <div class="cancel-comment-reply">
    	<?php // cancel_comment_reply_link(); ?>
    	<?php cancel_comment_reply_link(__('Click here to cancel reply.', 'default')); ?>
    	
    </div>
  
  <?php if ( get_option('comment_registration') && !$user_ID ) : ?>
  <div class="you-must-be-logged-in">
    <?php _e('You must be', 'default'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>"><?php _e('logged in', 'default'); ?></a> <?php _e('to post a comment', 'default'); ?>.
  </div>
  <?php else : ?>
  
  <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
  
  <?php if ( $user_ID ) : ?>
  
  <p><?php _e('Logged in as', 'default'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="<?php _e('Log out of this account', 'default'); ?>"><?php _e('Log out', 'default'); ?> &raquo;</a></p>
  
  <?php else : ?>
  
  <div class="respond-left">
    <p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" <?php if ($req) echo "aria-required='true'"; ?> />
    <label for="author"><?php _e('Name', 'default'); ?> <?php if ($req) echo "(required)"; ?></label></p>
    
    <p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" <?php if ($req) echo "aria-required='true'"; ?> />
    <label for="email"><?php _e('Mail (will not be published)', 'default'); ?> <?php if ($req) echo "(required)"; ?></label></p>
    
    <p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" />
    <label for="url"><?php _e('Website', 'default'); ?></label></p>
  </div>
  
  <?php endif; ?>
  
  <!--<p><small><strong>XHTML:</strong> <?php _e('You can use these tags', 'default'); ?>: <code><?php echo allowed_tags(); ?></code></small></p>-->
  
  <div class="respond-right">
    <textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea>
    <p><input name="submit" type="submit" id="submit" tabindex="5" value="<?php _e('Submit Comment', 'default'); ?>" />
    <?php comment_id_fields(); ?>
    </p>
    <?php do_action('comment_form', $post->ID); ?>
  </div>
  
  </form>
  
  <?php endif; // If registration required and not logged in ?>
  </div>

<?php endif; // if you delete this the sky will fall on your head ?>
