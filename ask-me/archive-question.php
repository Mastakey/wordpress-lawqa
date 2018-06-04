<?php get_header();
	global $vbegy_sidebar_all;
	$paged             = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
	$sticky_questions  = get_option('sticky_questions');
	$active_sticky     = true;
	include("sticky-question.php");
	
	$custom_args = (isset($custom_args) && is_array($custom_args)?$custom_args:array());
	$user_id_query = array("key" => "user_id","compare" => "NOT EXISTS");
	$args = array_merge($custom_args,$post__not_in,array("paged" => $paged,"post_type" => "question","meta_query" => array($user_id_query)));
	query_posts($args);
	$active_sticky = false;
	get_template_part("loop-question","archive");
	vpanel_pagination();
	wp_reset_query();
get_footer();?>