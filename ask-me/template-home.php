<?php /* Template Name: Home page */
get_header();
	$the_page_id      = $post->ID;
	$wp_page_template = get_post_meta($the_page_id,'_wp_page_template',true);
	include( get_template_directory() . '/includes/home.php' );
get_footer();?>