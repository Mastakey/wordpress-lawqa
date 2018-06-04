<?php /* Template Name: Most Responses / answers */
get_header();
	$paged = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
	$args = array("paged" => $paged,"post_type" => "question","orderby" => "comment_count","posts_per_page" => get_option("posts_per_page"),"meta_query" => array(array("key" => "user_id","compare" => "NOT EXISTS")));
	add_filter('posts_where', 'ask_filter_where_more');
	query_posts($args);
	get_template_part("loop-question");
	vpanel_pagination();
	wp_reset_query();
	remove_filter( 'posts_where', 'ask_filter_where_more' );
get_footer();?>