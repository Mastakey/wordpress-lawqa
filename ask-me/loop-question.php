<?php
global $blog_style,$vbegy_sidebar_all,$authordata,$question_bump_template,$question_vote_template,$k,$is_questions_sticky;
if (!isset($k)) {
	$k = 0;
}
if (have_posts() ) :
	do_action("askme_before_question_loop_if");
	while (have_posts() ) : the_post();
		$k++;
		do_action("askme_before_question_loop");
		include ("question.php");
		do_action("askme_after_question_loop");
	endwhile;
	do_action("askme_after_question_loop_if");
else :
	if ($is_questions_sticky != true) {
		echo "<div class='page-content page-content-user'><p class='no-item'>".__("No Questions Found.","vbegy")."</p></div>";
	}
endif;