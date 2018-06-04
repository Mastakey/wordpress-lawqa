<?php /* Template Name: No answers */
get_header();
	$paged = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
	$args = array("paged" => $paged,"post_type" => "question","meta_query" => array(array("key" => "user_id","compare" => "NOT EXISTS")),array("orderby" => array("comment_count" => "DESC","date" => "DESC")));
	add_filter('posts_where', 'ask_filter_where');
	query_posts($args);
	get_template_part("loop-question");
	vpanel_pagination();
	remove_filter( 'posts_where', 'ask_filter_where' );
	wp_reset_query();
get_footer();?>