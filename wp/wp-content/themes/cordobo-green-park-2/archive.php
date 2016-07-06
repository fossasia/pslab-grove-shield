<?php get_header(); ?>

	<div id="container">
		<div id="content">

		<?php if (have_posts()) : ?>

 	  <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
 	  <?php /* If this is a category archive */ if (is_category()) { ?>
		  <h1 class="pagetitle"><?php _e('Archive for the', 'default'); ?> &#8216;<?php single_cat_title(); ?>&#8217; <?php _e('category', 'default'); ?></h1>
 	  <?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
		  <h1 class="pagetitle"><?php _e('Posts Tagged', 'default'); ?> &#8216;<?php single_tag_title(); ?>&#8217;</h1>
 	  <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
		  <h1 class="pagetitle"><?php _e('Archive for', 'default'); ?> <?php the_time(__('F jS, Y','default')); ?></h1>
 	  <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
		  <h1 class="pagetitle"><?php _e('Archive for', 'default'); ?> <?php the_time(__('F, Y','default')); ?></h1>
 	  <?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
		  <h1 class="pagetitle"><?php _e('Archive for', 'default'); ?> <?php the_time(__('Y','default')); ?></h1>
	  <?php /* If this is an author archive */ } elseif (is_author()) { ?>
		  <h1 class="pagetitle"><?php _e('Author Archive', 'default'); ?></h1>
 	  <?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
		  <h1 class="pagetitle"><?php _e('Blog Archives', 'default'); ?></h1>
 	  <?php } ?>


		<div class="navigation clearfix">
			<div class="alignleft"><?php next_posts_link(__('&laquo; Older Entries', 'default')) ?></div>
			<div class="alignright"><?php previous_posts_link(__('Newer Entries &raquo;', 'default')) ?></div>
		</div>

		<?php while (have_posts()) : the_post(); ?>
		<div <?php post_class(archives-page) ?>>
			<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			<small class="meta"><?php the_time(__('F jS, Y','default')); ?> <?php edit_post_link(__( 'Edit', 'default' ), ' | ', ''); ?></small>

			<div class="entry">
				<?php the_content((__( '&raquo; Read more: ', 'default')) . the_title('', '', false)); ?>
			</div>

			<div class="postmetadata clearfix">
        <p class="commentslink alignright">
          <?php comments_popup_link( __( 'No comments', 'default' ), __( '1 comment', 'default' ), __( '% comments', 'default' )); ?> &#187;
        </p>
			  <p class="categories">
          <?php _e('Posted in ', 'default' ); the_category(', '); ?>
        </p>
			  <?php the_tags( '<p class="tags">Tags: ', ' ', '</p>'); ?>
      </div>
		</div>

		<?php endwhile; ?>

		<div class="pagination navigation clearfix">
		  <?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } else { ?>
  		  <div class="alignleft"><?php next_posts_link(__('&laquo; Older Entries', 'default')) ?></div>
  			<div class="alignright"><?php previous_posts_link(__('Newer Entries &raquo;', 'default')) ?></div>
		  <?php } ?>
		</div>		
		
	<?php else : ?>

    <?php include (TEMPLATEPATH . "/missing.php"); ?>

	<?php endif; ?>

		</div><!-- #content -->
	</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
