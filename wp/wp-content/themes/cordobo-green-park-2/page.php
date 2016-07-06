<?php get_header(); ?>

	<div id="container">
		<div id="content">

  		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    		<div class="hentry post" id="post-<?php the_ID(); ?>">
    		  <h1><?php the_title(); ?></h1>
    		  <?php edit_post_link(__( 'Edit this entry', 'default' ), '<small class="meta">', '</small>'); ?>
    			<div class="entry">
            <?php the_content(''); ?>
    			  <?php wp_link_pages(array('before' => '<div class="page-link clearfix"><strong>Pages:</strong>', 'after' => '</div>', 'next_or_number' => 'number', 'pagelink' => '<span>%</span>')); ?>
          </div>
    		</div>
    		
    		<?php comments_template('', true); ?>
  		
  		<?php endwhile; endif; ?>

		</div><!-- #content -->
	</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>