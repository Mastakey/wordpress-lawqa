<?php /* Template Name: Most vote */
get_header();
	$paged = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
	$args = array("paged" => $paged,"post_type" => "question",'orderby' => array('question_vote_order' => "DESC"),"meta_query" => array('relation' => 'AND','user_question_order' => array("key" => "user_id","compare" => "NOT EXISTS"),'question_vote_order' => array('type' => 'numeric',"key" => "question_vote","value" => 0,"compare" => ">")));
	query_posts($args);
	$question_vote_template = true;
	get_template_part("loop-question");
	vpanel_pagination();
	wp_reset_query();
get_footer();?>