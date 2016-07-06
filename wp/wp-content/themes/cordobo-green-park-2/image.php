<?php get_header(); ?>

	<div id="container">
		<div id="content">

  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<div class="post" id="post-<?php the_ID(); ?>">
			<h1><a href="<?php echo get_permalink($post->post_parent); ?>" rev="attachment"><?php echo get_the_title($post->post_parent); ?></a> &raquo; <?php the_title(); ?></h1>
			<div class="entry">
				<p class="attachment"><a href="<?php echo wp_get_attachment_url($post->ID); ?>"><?php echo wp_get_attachment_image( $post->ID, 'medium' ); ?></a></p>
				<div class="caption"><?php if ( !empty($post->post_excerpt) ) the_excerpt(); // this is the "caption" ?></div>

				<?php the_content((__( '&raquo; Read more: ', 'default')) . the_title('', '', false)); ?>

				<div class="navigation clearfix">
					<div class="alignleft"><?php previous_image_link() ?></div>
					<div class="alignright"><?php next_image_link() ?></div>
				</div>


			<div class="postmetadata alt clearfix">
			  <p class="categories">
			    <?php _e('Posted in ', 'default' ); the_category(', '); ?>
        </p>
  			<?php the_tags('<p class="tags">Tags: ', ' ', '</p>'); ?>
        <p class="infos">
						<?php _e('You can follow any responses to this entry through the', 'default'); ?> <a href="<?php echo get_post_comments_feed_link() ?>" rel="nofollow"><?php _e('RSS 2.0 Feed', 'default'); ?></a>. 

						<?php if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
							// Both Comments and Pings are open ?>
							<?php _e('You can', 'default'); ?> <a href="#respond"><?php _e('leave a response', 'default'); ?></a> <?php _e(', or ', 'default'); ?> <a href="<?php trackback_url(); ?>" rel="trackback nofollow"><?php _e('trackback', 'default'); ?></a> <?php _e('from your own site', 'default'); ?>.

						<?php } elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
							// Only Pings are Open ?>
							<?php _e('Responses are currently closed, but you can', 'default'); ?> <a href="<?php trackback_url(); ?> " rel="trackback nofollow"><?php _e('trackback', 'default'); ?></a> <?php _e('from your own site', 'default'); ?>.

						<?php } elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
							// Comments are open, Pings are not ?>
							<?php _e('You can skip to the end and leave a response. Pinging is currently not allowed.', 'default'); ?>

						<?php } elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
							// Neither Comments, nor Pings are open ?>
							<?php _e('Both comments and pings are currently closed.', 'default'); ?>

						<?php } edit_post_link(__( 'Edit this entry', 'default' ),'','.'); ?>
					</p>
				</div>

			</div>

		</div>
    
    <?php comments_template('', true); ?>

	<?php endwhile; else: ?>

		<?php include (TEMPLATEPATH . "/missing.php"); ?>

<?php endif; ?>

		</div><!-- #content -->
	</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
