<?php /* Template Name: Categories */
get_header();?>
	<div class="page-content">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post();?>
			<div class="boxedtitle page-title">
				<h2><?php the_title();?></h2>
			</div>
			<?php the_content();
		endwhile; endif;
		include( get_template_directory() . '/includes/categories.php' );?>
	</div><!-- End page-content -->
<?php get_footer();?>