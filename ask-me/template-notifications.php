<?php /* Template Name: Notifications */
get_header();
	$active_notifications = vpanel_options("active_notifications");
	if ($active_notifications == 1) {
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
							$_notifications = get_user_meta($user_id,$user_id."_notifications",true);
							update_user_meta($user_id,$user_id.'_new_notifications',0);
							$paged = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
							
							for ($notifications = 1; $notifications <= $_notifications; $notifications++) {
								$notification_one[] = get_user_meta($user_id,$user_id."_notifications_".$notifications);
							}
							if (isset($notification_one) and is_array($notification_one)) {
								$notification = array_reverse($notification_one);
								
								$current = max(1,$paged);
								$pagination_args = array(
									'base' => @esc_url(add_query_arg('paged','%#%')),
									'total' => ceil(sizeof($notification)/$rows_per_page),
									'current' => $current,
									'show_all' => false,
									'prev_text' => '<i class="icon-angle-left"></i>',
									'next_text' => '<i class="icon-angle-right"></i>',
								);
								
								if( !empty($wp_query->query_vars['s']) )
									$pagination_args['add_args'] = array('s'=>get_query_var('s'));
									
								$start = ($current - 1) * $rows_per_page;
								$end = $start + $rows_per_page;
								$end = (sizeof($notification) < $end) ? sizeof($notification) : $end;
								for ($i=$start;$i < $end ;++$i ) {
									$notification_result = $notification[$i][0];?>
									<article class="question user-question user-points">
										<div class="question-content">
											<div class="question-bottom">
												<h3>
													<?php if (!empty($notification_result["another_user_id"])) {
														$vpanel_get_user_url = vpanel_get_user_url($notification_result["another_user_id"]);
														$display_name = get_the_author_meta('display_name',$notification_result["another_user_id"]);
													}
													
													if ((($notification_result["text"] == "add_question_user" || $notification_result["text"] == "add_question" || $notification_result["text"] == "poll_question") && empty($notification_result["username"]) && isset($notification_result["another_user_id"]) && $notification_result["another_user_id"] == 0) || (!empty($notification_result["another_user_id"]) || !empty($notification_result["username"])) && $notification_result["text"] != "admin_add_points" && $notification_result["text"] != "admin_remove_points") {
														
														if ((($notification_result["text"] == "add_question_user" || $notification_result["text"] == "add_question" || $notification_result["text"] == "poll_question") && isset($notification_result["another_user_id"]) && $notification_result["another_user_id"] == 0) || (isset($display_name) && $display_name != "")) {
															if (!empty($notification_result["another_user_id"])) {?>
																<a href="<?php echo esc_url($vpanel_get_user_url)?>"><?php echo esc_html($display_name);?></a> 
															<?php }
															if (!empty($notification_result["username"])) {
																echo esc_attr($notification_result["username"])." ";
															}
															if (($notification_result["text"] == "add_question_user" || $notification_result["text"] == "add_question") && empty($notification_result["username"]) && isset($notification_result["another_user_id"]) && $notification_result["another_user_id"] == 0) {
																echo esc_html__("Aanonymous","vbegy")." ";
															}
															if (($notification_result["text"] == "poll_question") && empty($notification_result["username"]) && isset($notification_result["another_user_id"]) && $notification_result["another_user_id"] == 0) {
																echo esc_html__("Not register user","vbegy")." ";
															}
															esc_html_e("has","vbegy");
														}else if (!empty($notification_result["username"])) {
															echo esc_attr($notification_result["username"])." ";
														}else {
															echo esc_html__("Deleted user","vbegy")." -";
														}
													}
													if (!empty($notification_result["post_id"])) {
														$get_the_permalink = get_the_permalink($notification_result["post_id"]);
														$get_post_status = get_post_status($notification_result["post_id"]);
													}
													if (!empty($notification_result["comment_id"])) {
														$get_comment = get_comment($notification_result["comment_id"]);
													}
													if (!empty($notification_result["post_id"]) && !empty($notification_result["comment_id"]) && $get_post_status != "trash" && isset($get_comment) && $get_comment->comment_approved != "spam" && $get_comment->comment_approved != "trash") {?>
														<a href="<?php echo esc_url($get_the_permalink.(isset($notification_result["comment_id"])?"#comment-".$notification_result["comment_id"]:""))?>">
													<?php }
													if (!empty($notification_result["post_id"]) && empty($notification_result["comment_id"]) && $get_post_status != "trash" && isset($get_the_permalink) && $get_the_permalink != "") {?>
														<a href="<?php echo esc_url($get_the_permalink)?>">
													<?php }
														echo " ";
														if ($notification_result["text"] == "add_question_user") {
															_e("been asked you a question.","vbegy");
														}else if ($notification_result["text"] == "poll_question") {
															_e("polled at your question","vbegy");
														}else if ($notification_result["text"] == "gift_site") {
															_e("Gift of the site.","vbegy");
														}else if ($notification_result["text"] == "admin_add_points") {
															_e("The administrator added points for you.","vbegy");
														}else if ($notification_result["text"] == "admin_remove_points") {
															_e("The administrator removed points from you.","vbegy");
														}else if ($notification_result["text"] == "question_vote_up") {
															_e("voted up your question.","vbegy");
														}else if ($notification_result["text"] == "question_vote_down") {
															_e("voted down your question.","vbegy");
														}else if ($notification_result["text"] == "answer_vote_up") {
															_e("voted up you answer.","vbegy");
														}else if ($notification_result["text"] == "answer_vote_down") {
															_e("voted down you answer.","vbegy");
														}else if ($notification_result["text"] == "user_follow") {
															_e("followed you.","vbegy");
														}else if ($notification_result["text"] == "user_unfollow") {
															_e("unfollowed you.","vbegy");
														}else if ($notification_result["text"] == "point_back") {
															_e("Your point back because the best answer selected.","vbegy");
														}else if ($notification_result["text"] == "select_best_answer") {
															_e("choosed your answer best answer.","vbegy");
														}else if ($notification_result["text"] == "point_removed") {
															_e("Your point removed because the best answer removed.","vbegy");
														}else if ($notification_result["text"] == "cancel_best_answer") {
															_e("canceled your answer best answer.","vbegy");
														}else if ($notification_result["text"] == "answer_asked_question") {
															_e("answered at your asked question.","vbegy");
														}else if ($notification_result["text"] == "answer_question") {
															_e("answered your question.","vbegy");
														}else if ($notification_result["text"] == "answer_question_follow") {
															_e("answered your question you follow.","vbegy");
														}else if ($notification_result["text"] == "add_question") {
															_e("added a new question.","vbegy");
														}else if ($notification_result["text"] == "question_favorites") {
															_e("added your question at favorites.","vbegy");
														}else if ($notification_result["text"] == "question_remove_favorites") {
															_e("removed your question from favorites.","vbegy");
														}else if ($notification_result["text"] == "follow_question") {
															_e("followed your question.","vbegy");
														}else if ($notification_result["text"] == "unfollow_question") {
															_e("unfollowed your question.","vbegy");
														}else if ($notification_result["text"] == "approved_answer") {
															_e("The administrator added your answer.","vbegy");
														}else if ($notification_result["text"] == "approved_comment") {
															_e("The administrator added your comment.","vbegy");
														}else if ($notification_result["text"] == "approved_question") {
															_e("The administrator added your question.","vbegy");
														}else if ($notification_result["text"] == "approved_post") {
															_e("The administrator added your post.","vbegy");
														}else if ($notification_result["text"] == "add_message_user") {
															echo "<a href='".esc_url(get_page_link(vpanel_options('messages_page')))."'>".__("sended a message for you.","vbegy")."</a>";
														}else if ($notification_result["text"] == "seen_message") {
															_e("seen your message.","vbegy");
														}else if ($notification_result["text"] == "action_comment") {
															echo sprintf(__("The administrator %s your %s.","vbegy"),$notification_result["more_text"],(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "answer"?__("answer","vbegy"):__("comment","vbegy")));
														}else if ($notification_result["text"] == "action_post") {
															echo sprintf(__("The administrator %s your %s.","vbegy"),$notification_result["more_text"],(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "question"?__("question","vbegy"):__("post","vbegy")));
														}else if ($notification_result["text"] == "delete_reason") {
															echo sprintf(__("The administrator reason : %s.","vbegy"),$notification_result["more_text"]);
														}else if ($notification_result["text"] == "delete_question" || $notification_result["text"] == "delete_post") {
															echo sprintf(__("The administrator deleted your %s.","vbegy"),(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "question"?__("question","vbegy"):__("post","vbegy")));
														}else if ($notification_result["text"] == "delete_answer" || $notification_result["text"] == "delete_comment") {
															echo sprintf(__("The administrator deleted your %s.","vbegy"),(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "answer"?__("answer","vbegy"):__("comment","vbegy")));
														}
													if ((!empty($notification_result["post_id"]) && !empty($notification_result["comment_id"]) && $get_post_status != "trash" && isset($get_comment) && $get_comment->comment_approved != "spam" && $get_comment->comment_approved != "trash") || (!empty($notification_result["post_id"]) && empty($notification_result["comment_id"]) && $get_post_status != "trash" && isset($get_the_permalink) && $get_the_permalink != "")) {?>
														</a>
													<?php }
													if (!empty($notification_result["post_id"]) && !empty($notification_result["comment_id"])) {
														if (isset($get_comment) && $get_comment->comment_approved == "spam") {
															echo " ".__('( Spam )','vbegy');
														}else if ($get_post_status == "trash" || (isset($get_comment) && $get_comment->comment_approved == "trash")) {
															echo " ".__('( Trashed )','vbegy');
														}else if (empty($get_comment)) {
															echo " ".__('( Deleted )','vbegy');
														}
														if ($notification_result["text"] == "delete_reason") {
															echo " - ".(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "answer"?__("answer","vbegy"):__("comment","vbegy"));
														}
													}
													if (!empty($notification_result["post_id"]) && empty($notification_result["comment_id"])) {
														if ($get_post_status == "trash") {
															echo " ".__('( Trashed )','vbegy');
														}else if (empty($get_the_permalink)) {
															echo " ".__('( Deleted )','vbegy');
														}
													}
													if (!empty($notification_result["more_text"]) && $notification_result["text"] != "action_post" && $notification_result["text"] != "action_comment" && $notification_result["text"] != "delete_reason") {
														echo " - ".esc_attr($notification_result["more_text"]).".";
													}?>
												</h3>
												<span class="question-date"><i class="fa fa-calendar"></i><?php echo human_time_diff($notification_result["time"],current_time('timestamp'))." ".__("ago","vbegy")?></span>
											</div>
										</div>
									</article>
								<?php }
							}else {echo "<p class='no-item'>".__("There are no notifications yet.","vbegy")."</p>";}?>
						</div>
					</div>
					<?php if (isset($notification_one) &&is_array($notification_one) && $pagination_args["total"] > 1) {?>
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