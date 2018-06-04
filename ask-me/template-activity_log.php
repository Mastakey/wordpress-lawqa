<?php /* Template Name: Activity log */
get_header();
	$active_activity_log = vpanel_options("active_activity_log");
	if ($active_activity_log == 1) {
		if (!is_user_logged_in) {?>
			<div class="page-content">
				<div class="boxedtitle page-title"><h2><?php the_title();?></h2></div>
		<?php }?>
			<div class="form-style form-style-4">
				<?php if (!is_user_logged_in) {
					echo '<div class="note_error"><strong>'.__("Please login to see the activity log.","vbegy").'</strong></div>
					<div class="form-style form-style-3">
						'.do_shortcode("[ask_login register_2='yes']").'
					</div>';
				}else {?>
					<div class="page-content page-content-user">
						<div class="user-questions">
							<?php $user_id = get_current_user_id();
							$rows_per_page = get_option("posts_per_page");
							$_activities = get_user_meta($user_id,$user_id."_activities",true);
							$paged = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
							
							for ($activities = 1; $activities <= $_activities; $activities++) {
								$activities_one[] = get_user_meta($user_id,$user_id."_activities_".$activities);
							}
							if (isset($activities_one) and is_array($activities_one)) {
								$activities = array_reverse($activities_one);
								
								$current = max(1,$paged);
								$pagination_args = array(
									'base' => @esc_url(add_query_arg('paged','%#%')),
									'total' => ceil(sizeof($activities)/$rows_per_page),
									'current' => $current,
									'show_all' => false,
									'prev_text' => '<i class="icon-angle-left"></i>',
									'next_text' => '<i class="icon-angle-right"></i>',
								);
								
								if( !empty($wp_query->query_vars['s']) )
									$pagination_args['add_args'] = array('s'=>get_query_var('s'));
									
								$start = ($current - 1) * $rows_per_page;
								$end = $start + $rows_per_page;
								$end = (sizeof($activities) < $end) ? sizeof($activities) : $end;
								for ($i=$start;$i < $end ;++$i ) {
									$activities_result = $activities[$i][0];?>
									<article class="question user-question user-points">
										<div class="question-content">
											<div class="question-bottom">
												<h3>
													<?php if (!empty($activities_result["another_user_id"])) {
														$vpanel_get_user_url = vpanel_get_user_url($activities_result["another_user_id"]);
														$display_name = get_the_author_meta('display_name',$activities_result["another_user_id"]);
													}
													
													if ((!empty($activities_result["another_user_id"]) || !empty($activities_result["username"])) && $activities_result["text"] != "add_message" && $activities_result["text"] != "admin_add_points" && $activities_result["text"] != "admin_remove_points") {
														if (isset($display_name) && $display_name != "") {
															if (!empty($activities_result["another_user_id"])) {?>
																<a href="<?php echo esc_url($vpanel_get_user_url)?>"><?php echo $display_name;?></a>
															<?php }
															if (!empty($activities_result["username"])) {
																echo esc_attr($activities_result["username"])." ";
															}
															echo __("has","vbegy")." ";
														}else {
															echo __("Delete user","vbegy")." - ";
														}
													}
													
													if (!empty($activities_result["post_id"])) {
														$get_the_permalink = get_the_permalink($activities_result["post_id"]);
														$get_post_status = get_post_status($activities_result["post_id"]);
													}
													if (!empty($activities_result["comment_id"])) {
														$get_comment = get_comment($activities_result["comment_id"]);
													}
													if (!empty($activities_result["post_id"]) && !empty($activities_result["comment_id"]) && $get_post_status != "trash" && isset($get_comment) && $get_comment->comment_approved != "spam" && $get_comment->comment_approved != "trash") {?>
														<a href="<?php echo esc_url($get_the_permalink.(isset($activities_result["comment_id"])?"#comment-".$activities_result["comment_id"]:""))?>">
													<?php }
													if (!empty($activities_result["post_id"]) && empty($activities_result["comment_id"]) && $get_post_status != "trash" && isset($get_the_permalink) && $get_the_permalink != "") {?>
														<a href="<?php echo esc_url($get_the_permalink)?>">
													<?php }
														if ($activities_result["text"] == "poll_question") {
															_e("Poll at question","vbegy");
														}else if ($activities_result["text"] == "question_vote_up") {
															_e("Vote up question.","vbegy");
														}else if ($activities_result["text"] == "question_vote_down") {
															_e("Vote down question.","vbegy");
														}else if ($activities_result["text"] == "answer_vote_up") {
															_e("Vote up answer.","vbegy");
														}else if ($activities_result["text"] == "answer_vote_down") {
															_e("Vote down answer.","vbegy");
														}else if ($activities_result["text"] == "user_follow") {
															_e("You follow","vbegy");
														}else if ($activities_result["text"] == "user_unfollow") {
															_e("You unfollow","vbegy");
														}else if ($activities_result["text"] == "bump_question") {
															_e("You bump your question.","vbegy");
														}else if ($activities_result["text"] == "report_question") {
															_e("You report a question.","vbegy");
														}else if ($activities_result["text"] == "report_answer") {
															_e("You report a answer.","vbegy");
														}else if ($activities_result["text"] == "select_best_answer") {
															_e("You choose the best answer.","vbegy");
														}else if ($activities_result["text"] == "cancel_best_answer") {
															_e("You cancel the best answer.","vbegy");
														}else if ($activities_result["text"] == "closed_question") {
															_e("You closed the question.","vbegy");
														}else if ($activities_result["text"] == "opend_question") {
															_e("You opend the question.","vbegy");
														}else if ($activities_result["text"] == "follow_question") {
															_e("You follow the question.","vbegy");
														}else if ($activities_result["text"] == "unfollow_question") {
															_e("You unfollow the question.","vbegy");
														}else if ($activities_result["text"] == "add_answer") {
															_e("You add a answer.","vbegy");
														}else if ($activities_result["text"] == "add_comment") {
															_e("You add a comment.","vbegy");
														}else if ($activities_result["text"] == "approved_answer") {
															_e("Your answer pending for review.","vbegy");
														}else if ($activities_result["text"] == "approved_comment") {
															_e("Your comment pending for review.","vbegy");
														}else if ($activities_result["text"] == "add_question") {
															_e("Add a new question.","vbegy");
														}else if ($activities_result["text"] == "add_post") {
															_e("add a new post.","vbegy");
														}else if ($activities_result["text"] == "approved_question") {
															_e("Your question pending for review.","vbegy");
														}else if ($activities_result["text"] == "approved_post") {
															_e("Your post pending for review.","vbegy");
														}else if ($activities_result["text"] == "question_favorites") {
															_e("You add a question at favorites.","vbegy");
														}else if ($activities_result["text"] == "question_remove_favorites") {
															_e("You remove a question from favorites.","vbegy");
														}else if ($activities_result["text"] == "delete_question") {
															_e("You have deleted a question.","vbegy");
														}else if ($activities_result["text"] == "delete_post") {
															_e("You have deleted a post.","vbegy");
														}else if ($activities_result["text"] == "delete_answer") {
															_e("You have deleted an answer.","vbegy");
														}else if ($activities_result["text"] == "delete_comment") {
															_e("You have deleted a comment.","vbegy");
														}else if ($activities_result["text"] == "add_message") {
															_e("You send a message for","vbegy");
															if (!empty($activities_result["another_user_id"])) {
																$vpanel_get_user_url = vpanel_get_user_url($activities_result["another_user_id"]);
																$display_name = get_the_author_meta('display_name',$activities_result["another_user_id"]);
															}
															
															if (!empty($activities_result["another_user_id"]) || !empty($activities_result["username"])) {
																if (isset($display_name) && $display_name != "") {
																	if (!empty($activities_result["another_user_id"])) {?>
																		<a href="<?php echo esc_url($vpanel_get_user_url)?>"><?php echo $display_name;?></a>.
																	<?php }
																	if (!empty($activities_result["username"])) {
																		echo esc_attr($activities_result["username"]).".";
																	}
																}else {
																	echo __("Delete user","vbegy").".";
																}
															}
														}else if ($activities_result["text"] == "delete_inbox_message") {
															_e("You delete your inbox message","vbegy");
														}else if ($activities_result["text"] == "delete_send_message") {
															_e("You delete your sended message","vbegy");
														}
													if ((!empty($activities_result["post_id"]) && !empty($activities_result["comment_id"]) && $get_post_status != "trash" && isset($get_comment) && $get_comment->comment_approved != "spam" && $get_comment->comment_approved != "trash") || (!empty($activities_result["post_id"]) && empty($activities_result["comment_id"]) && $get_post_status != "trash" && isset($get_the_permalink) && $get_the_permalink != "")) {?>
														</a>
													<?php }
													if (!empty($activities_result["post_id"]) && !empty($activities_result["comment_id"])) {
														if (isset($get_comment) && $get_comment->comment_approved == "spam") {
															echo " ".__('( Spam )','vbegy');
														}else if ($get_post_status == "trash" || (isset($get_comment) && $get_comment->comment_approved == "trash")) {
															echo " ".__('( Trashed )','vbegy');
														}else if (empty($get_comment)) {
															echo " ".__('( Deleted )','vbegy');
														}
													}
													if (!empty($activities_result["post_id"]) && empty($activities_result["comment_id"])) {
														if ($get_post_status == "trash") {
															echo " ".__('( Trashed )','vbegy');
														}else if (empty($get_the_permalink)) {
															echo " ".__('( Deleted )','vbegy');
														}
													}
													if (!empty($activities_result["more_text"])) {
														echo " - ".esc_attr($activities_result["more_text"]).".";
													}
													if (($activities_result["text"] == "user_follow" || $activities_result["text"] == "user_unfollow") && !empty($activities_result["another_user_id"])) {?>
														<a href="<?php echo vpanel_get_user_url($activities_result["another_user_id"])?>"><?php echo get_the_author_meta('display_name',$activities_result["another_user_id"]);?></a>.
													<?php }?>
												</h3>
												<span class="question-date"><i class="fa fa-calendar"></i><?php echo human_time_diff($activities_result["time"],current_time('timestamp'))." ".__("ago","vbegy")?></span>
											</div>
										</div>
									</article>
								<?php }
							}else {echo "<p class='no-item'>".__("There are no activities yet.","vbegy")."</p>";}?>
						</div>
					</div>
					<?php if (isset($activities_one) &&is_array($activities_one) && $pagination_args["total"] > 1) {?>
						<div class='pagination'><?php echo (paginate_links($pagination_args) != ""?paginate_links($pagination_args):"")?></div>
					<?php }
				}
			if (!is_user_logged_in) {?>
				</div><!-- End page-content -->
			<?php }?>
		</div><!-- End main -->
	<?php }else {
		echo "<div class='page-content page-content-user'><p class='no-item'>".__("This page is not active.","vbegy")."</p></div>";
	}
get_footer();?>