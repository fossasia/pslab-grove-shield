<?php
/*
Template Name: Links
*/
?>

<?php get_header(); ?>

<div id="container">
	<div id="content">
    
    <div class="hentry post">
      <h2><?php _e('Links', 'default'); ?></h2>
      <div class="entry">
        <?php wp_list_bookmarks('title_li=&category_before=&category_after'); ?>
      </div>
    </div>

	</div><!-- #content -->
</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
