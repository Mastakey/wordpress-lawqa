<?php /* Template Name: Most visit */
get_header();
	$paged = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
	$args = array("paged" => $paged,"posts_per_page" => get_option("posts_per_page"),"post_type" => "question",'orderby' => array('post_stats_order' => "DESC"),"meta_query" => array('relation' => 'AND','user_question_order' => array("key" => "user_id","compare" => "NOT EXISTS"),'post_stats_order' => array('type' => 'numeric',"key" => "post_stats","value" => 0,"compare" => ">")));
	query_posts($args);
	get_template_part("loop-question");
	vpanel_pagination();
	wp_reset_query();
get_footer();?>