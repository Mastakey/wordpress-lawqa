<?php if (isset($sticky_questions) && is_array($sticky_questions) && !empty($sticky_questions) && $paged == 1) {
	if (isset($custom_args) && is_array($custom_args) && !empty($custom_args)) {
		$custom_args = $custom_args;
	}else {
		$custom_args = array();
	}
	$args = array_merge($custom_args,array("nopaging" => true,"post_type" => "question","post__in" => $sticky_questions));
	query_posts($args);
	global $blog_style,$authordata,$question_bump_template,$question_vote_template,$k;
	if (!isset($k)) {
		$k = 0;
	}
	if (have_posts() ) :
		//do_action("askme_before_question_loop_if");
		while (have_posts() ) : the_post();
			$k++;
			//do_action("askme_before_question_loop");
			include ("question.php");
			//do_action("askme_after_question_loop");
		endwhile;
		$is_questions_sticky = true;
		//do_action("askme_after_question_loop_if");
	endif;
	wp_reset_query();
}
$post__not_in = array();
$sticky_questions = get_option("sticky_questions");
if (isset($sticky_questions) && is_array($sticky_questions) && !empty($sticky_questions)) {
	$post__not_in = array("post__not_in" => $sticky_questions);
}?>