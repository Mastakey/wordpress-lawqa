<?php /* Template Name: Recent question  */
get_header();
	$paged            = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
	$sticky_questions = get_option('sticky_questions');
	$active_sticky    = true;
	include("sticky-question.php");
	
	$user_id_query = array("key" => "user_id","compare" => "NOT EXISTS");
	$custom_args = (isset($custom_args) && is_array($custom_args)?$custom_args:array());
	$args = array_merge($custom_args,$post__not_in,array("paged" => $paged,"post_type" => "question","posts_per_page" => get_option("posts_per_page"),"meta_query" => array($user_id_query)));
	query_posts($args);
	$active_sticky = false;
	get_template_part("loop-question");
	vpanel_pagination();
	wp_reset_query();
get_footer();?>