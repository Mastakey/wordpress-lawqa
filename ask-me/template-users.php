<?php /* Template Name: Users */
get_header();
	if ('' !== get_post()->post_content) {?>
		<div class="page-content">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post();?>
				<div class="boxedtitle page-title">
					<h2><?php the_title();?></h2>
				</div>
				<?php the_content();
			endwhile; endif;?>
		</div><!-- End page-content -->
	<?php }
	include( get_template_directory() . '/includes/users.php' );
get_footer();?>