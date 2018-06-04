<?php get_header();?>
	<div id="question-<?php echo $post->ID;?>" itemscope itemtype="http://schema.org/Question">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
			$question_vote = get_post_meta($post->ID,"question_vote",true);
			$question_category = wp_get_post_terms($post->ID,ask_question_category,array("fields" => "all"));
			$user_get_current_user_id = get_current_user_id();
			$get_question_user_id = get_post_meta($post->ID,"user_id",true);
			$anonymously_user = get_post_meta($post->ID,'anonymously_user',true);
			$yes_private = ask_private($post->ID,$post->post_author,$user_get_current_user_id);
			$vbegy_what_post = rwmb_meta('vbegy_what_post','select',$post->ID);
			$vbegy_sidebar_all = rwmb_meta('vbegy_sidebar','select',$post->ID);
			
			$comment_count = get_post_meta($post->ID,"comment_count",true);
			if ($post->comment_count > 0) {
				$comment_count = get_post_meta($post->ID,"comment_count",true);
				if ($comment_count == "") {
					update_post_meta($post->ID,"comment_count",$post->comment_count);
				}
			}
			
			$the_best_answer = get_post_meta($post->ID,"the_best_answer",true);
			if (isset($the_best_answer) && $the_best_answer != "") {
				$get_comment = get_comment($the_best_answer);
				if (empty($get_comment)) {
					delete_post_meta($post->ID,"the_best_answer");
				}
			}
			
			$question_poll = get_post_meta($post->ID,'question_poll',true);
			$question_type = ($question_poll == 1?" question-type-poll":" question-type-normal");
			$closed_question = get_post_meta($post->ID,"closed_question",true);
			$question_favorites = get_post_meta($post->ID,'question_favorites',true);
			
			$the_author = get_user_by("login",get_the_author());
			$user_login_id_l = get_user_by("id",$post->post_author);
			if ($post->post_author != 0) {
				$user_profile_page = esc_url(add_query_arg("u", $user_login_id_l->user_login,get_page_link(vpanel_options('user_profile_page'))));
			}
			
			if (!is_super_admin($user_get_current_user_id) && $yes_private != 1) {?>
				<article class="question private-question">
					<p class="question-desc"><?php _e("Sorry it a private question.","vbegy");?></p>
				</article>
			<?php }else {
				$custom_page_setting = rwmb_meta('vbegy_custom_page_setting','checkbox',$post->ID);
				$post_share_s = rwmb_meta('vbegy_post_share_s','checkbox',$post->ID);
				$post_author_box_s = rwmb_meta('vbegy_post_author_box_s','checkbox',$post->ID);
				$related_post_s = rwmb_meta('vbegy_related_post_s','checkbox',$post->ID);
				$post_comments_s = rwmb_meta('vbegy_post_comments_s','checkbox',$post->ID);
				$post_navigation_s = rwmb_meta('vbegy_post_navigation_s','checkbox',$post->ID);
				$active_reports = vpanel_options("active_reports");
				$active_logged_reports = vpanel_options("active_logged_reports");
				$question_type = ($active_reports == 1 && (is_user_logged_in || (!is_user_logged_in && $active_logged_reports != 1))?$question_type:$question_type." no_reports");
				
				$_paid_question = get_post_meta($post->ID, '_paid_question', true);
				
				if ((is_super_admin($user_get_current_user_id) || ($anonymously_user > 0 && $user_get_current_user_id == $anonymously_user) || ($post->post_author > 0 && $user_get_current_user_id == $post->post_author)) && (isset($_paid_question) && $_paid_question == "paid")) {
					echo '<div class="alert-message info"><i class="icon-ok"></i><p><span>'.__("Paid question","vbegy").'</span><br>'.__("This is a paid question.","vbegy").'</p></div>';
				}
				
				$question_sticky = "";
				$end_sticky_time = get_post_meta($post->ID,"end_sticky_time",true);
				if (is_sticky()) {
					if ($end_sticky_time != "" && $end_sticky_time < strtotime(date("Y-m-d"))) {
						delete_post_meta($post->ID,"start_sticky_time");
						delete_post_meta($post->ID,"end_sticky_time");
						delete_post_meta($post->ID,'sticky');
						$sticky_questions = get_option('sticky_questions');
						if (is_array($sticky_questions) && in_array($post->ID,$sticky_questions)) {
							$sticky_posts = get_option('sticky_posts');
							$sticky_posts = remove_item_by_value($sticky_posts,$post->ID);
							update_option('sticky_posts',$sticky_posts);
							$sticky_questions = remove_item_by_value($sticky_questions,$post->ID);
							update_option('sticky_questions',$sticky_questions);
						}
					}
					if ((is_super_admin($user_get_current_user_id) || ($anonymously_user > 0 && $user_get_current_user_id == $anonymously_user) || ($post->post_author > 0 && $user_get_current_user_id == $post->post_author)) && ($end_sticky_time != "" && $end_sticky_time >= strtotime(date("Y-m-d")))) {
						echo '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Sticky time","vbegy").'</span><br>'.__("This question will sticky to","vbegy").': '.date("Y-m-d",$end_sticky_time).'</p></div>';
					}
					$question_sticky = " sticky";
					if ($end_sticky_time != "" && $end_sticky_time < strtotime(date("Y-m-d"))) {
						$question_sticky = "";
					}
				}else {
					$end_sticky_time = "";
				}
				
				if (is_super_admin($user_get_current_user_id) && ((isset($_paid_question) && $_paid_question == "paid") || is_sticky())) {
					if (isset($_paid_question) && $_paid_question == "paid") {
						$item_transaction = get_post_meta($post->ID, 'item_transaction', true);
						$paypal_sandbox = get_post_meta($post->ID, 'paypal_sandbox', true);
					}
					
					if (is_sticky()) {
						$item_transaction_sticky = get_post_meta($post->ID, 'item_transaction_sticky', true);
						$paypal_sandbox_sticky = get_post_meta($post->ID, 'paypal_sandbox_sticky', true);
					}
					
					if ((isset($_paid_question) && $_paid_question == "paid" && ((isset($item_transaction) && $item_transaction != "") || (isset($paypal_sandbox) && $paypal_sandbox != "" && $paypal_sandbox = "sandbox"))) || (is_sticky() && ((isset($item_transaction_sticky) && $item_transaction_sticky != "") || (isset($paypal_sandbox_sticky) && $paypal_sandbox_sticky != "" && $paypal_sandbox_sticky = "sandbox")))) {
						echo '<a href="#" class="paid-details color button small f_left">'.__("Paid details","vbegy").'</a>
						<div class="clearfix"></div>
						<div class="paid-question-area">';
							if (isset($_paid_question) && $_paid_question == "paid") {
								if (isset($item_transaction) && $item_transaction != "") {
									echo '<div class="alert-message warning"><i class="icon-ok"></i><p><span>'.__("Transaction id","vbegy").'</span><br>'.__("The transaction id","vbegy").' : '.$item_transaction.'</p></div>';
								}
								if (isset($paypal_sandbox) && $paypal_sandbox != "" && $paypal_sandbox = "sandbox") {
									echo '<div class="alert-message error"><i class="icon-ok"></i><p><span>'.__("PayPal sandbox","vbegy").'</span><br>'.__("This transaction is from PayPal sandbox.","vbegy").'</p></div>';
								}
							}
							
							if (is_sticky()) {
								if (isset($item_transaction_sticky) && $item_transaction_sticky != "") {
									echo '<div class="alert-message warning"><i class="icon-ok"></i><p><span>'.__("Transaction id","vbegy").'</span><br>'.__("The transaction id for sticky question","vbegy").' : '.$item_transaction_sticky.'</p></div>';
								}
								if (isset($paypal_sandbox_sticky) && $paypal_sandbox_sticky != "" && $paypal_sandbox_sticky = "sandbox") {
									echo '<div class="alert-message error"><i class="icon-ok"></i><p><span>'.__("PayPal sandbox","vbegy").'</span><br>'.__("This transaction is from PayPal sandbox for sticky question.","vbegy").'</p></div>';
								}
							}
						echo '</div>';
					}
				}
				
				if (($post->post_author != 0 && $post->post_author == $user_get_current_user_id) || is_super_admin($user_get_current_user_id)) {
					$question_delete = vpanel_options("question_delete");
					if ($question_delete == 1) {
						if (isset($_GET) && isset($_GET["delete"]) && $_GET["delete"] == $post->ID && isset($post->post_status) && $post->post_status == "publish") {
							if ($post->post_author > 0) {
								askme_notifications_activities($post->post_author,"","","","","delete_question","activities","","question");
							}
							$delete_trush = vpanel_options("delete_question");
							wp_delete_post($post->ID,($delete_trush == "trash"?false:true));
							$_SESSION['vbegy_session_all'] = '<div class="alert-message success"><p>'.esc_html__("Has been deleted successfully.","vbegy").'</p></div>';
							$protocol = is_ssl() ? 'https' : 'http';
							$redirect_to = wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
							$redirect_to = (isset($_GET["page"]) && esc_attr($_GET["page"]) != ""?esc_attr($_GET["page"]):$redirect_to);
							if ( is_ssl() && force_ssl_admin() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )$secure_cookie = false; else $secure_cookie = '';
							wp_redirect(((isset($_GET["page"]) && esc_attr($_GET["page"]) != "") || is_page()?$redirect_to:home_url()));
						}
					}
				}
				
				if (!is_admin() && isset($_GET["delete_comment"]) && $_GET["delete_comment"] != "") {
					$comment_id  = (int)$_GET["delete_comment"];
					$get_comment = get_comment($comment_id);
					if (isset($get_comment) && $comment_id > 0 && isset($get_comment->comment_approved) && $get_comment->comment_approved == 1) {
						$comment_user_id    = $get_comment->user_id;
						$comment_type       = get_comment_meta($comment_id,"comment_type",true);
						$can_delete_comment = vpanel_options("can_delete_comment");
						$delete_comment     = vpanel_options("delete_comment");
						if (($comment_user_id > 0 && $comment_user_id == $user_get_current_user_id && $can_delete_comment == "on") || current_user_can('edit_comment',$comment_id) || is_super_admin($user_get_current_user_id)) {
							if ($user_get_current_user_id > 0) {
								askme_notifications_activities($user_get_current_user_id,"","","","","delete_".($comment_type == "question"?"answer":"comment"),"activities","",($comment_type == "question"?"answer":"comment"));
							}
							if ($comment_user_id > 0 && $user_get_current_user_id != $comment_user_id) {
								askme_notifications_activities($comment_user_id,"","","","","delete_".($comment_type == "question"?"answer":"comment"),"notifications","",($comment_type == "question"?"answer":"comment"));
							}
							wp_delete_comment($comment_id,($delete_comment == "trash"?false:true));
							$_SESSION['vbegy_session_all'] = '<div class="alert-message success"><p>'.esc_html__("Has been deleted successfully.","vbegy").'</p></div>';
							$protocol    = is_ssl() ? 'https' : 'http';
							$redirect_to = esc_url(remove_query_arg('delete_comment',wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])));
							$redirect_to = (isset($_GET["page"]) && esc_attr($_GET["page"]) != ""?esc_attr($_GET["page"]):$redirect_to);
							wp_redirect($redirect_to);
							exit;
						}
					}
				}?>
				<article <?php post_class('question single-question'.$question_type.$question_sticky);?> id="post-<?php echo $post->ID;?>" <?php do_action("askme_question_attrs",$post->post_author)?>>
					<?php $question_follow = vpanel_options("question_follow");
					$question_control_style = vpanel_options("question_control_style");
					$following_questions = get_post_meta($post->ID,"following_questions",true);
					if ($question_control_style == "style_1" && (($post->post_author != 0 && $post->post_author == $user_get_current_user_id) || ($question_follow == 1 && is_user_logged_in) || ($post->post_author != 0 && $post->post_author == $user_get_current_user_id) || ($anonymously_user != 0 && $anonymously_user == $user_get_current_user_id) || is_super_admin($user_get_current_user_id))) {?>
						<div class="edit-delete-follow-close">
							<h2>
								<?php $question_edit = vpanel_options("question_edit");
								if (($post->post_author != 0 && $post->post_author == $user_get_current_user_id) || is_super_admin($user_get_current_user_id)) {
									if ($question_edit == 1 || is_super_admin($user_get_current_user_id)) {?>
										<span class="question-edit">
											<a href="<?php echo esc_url(add_query_arg("q", $post->ID,get_page_link(vpanel_options('edit_question'))))?>" original-title="<?php _e("Edit the question","vbegy")?>" class="tooltip-n"><i class="icon-edit"></i></a>
										</span>
									<?php }
									if ($question_delete == 1 || is_super_admin($user_get_current_user_id)) {?>
										<span class="question-delete">
											<a href="<?php echo esc_url(add_query_arg("delete", $post->ID,get_permalink($post->ID)))?>" original-title="<?php _e("Delete the question","vbegy")?>" class="tooltip-n"><i class="icon-remove"></i></a>
										</span>
									<?php }
								}
								
								if (($question_follow == 1 || is_super_admin($user_get_current_user_id)) && is_user_logged_in && $user_get_current_user_id != $get_question_user_id && (($user_get_current_user_id != $post->post_author && $post->post_author > 0) || ($anonymously_user != "" && $anonymously_user != $user_get_current_user_id))) {?>
									<span class="question-follow">
										<?php if (isset($following_questions) && is_array($following_questions) && in_array($user_get_current_user_id,$following_questions)) {?>
											<a href="#" original-title="<?php _e("Unfollow the question","vbegy")?>" class="tooltip-n unfollow-question"><i class="icon-circle-arrow-down"></i></a>
										<?php }else {?>
											<a href="#" original-title="<?php _e("Follow the question","vbegy")?>" class="tooltip-n"><i class="icon-circle-arrow-up"></i></a>
										<?php }?>
									</span>
								<?php }
								if (($post->post_author != 0 && $post->post_author == $user_get_current_user_id) || is_super_admin($user_get_current_user_id)) {
									$question_close = vpanel_options("question_close");
									if (isset($question_close) && $question_close == 1) {
										if (isset($closed_question) && $closed_question == 1) {?>
											<span class="question-open">
												<a href="#" original-title="<?php _e("Open the question","vbegy")?>" class="tooltip-n"><i class="icon-unlock"></i></a>
											</span>
										<?php }else {?>
											<span class="question-close">
												<a href="#" original-title="<?php _e("Close the question","vbegy")?>" class="tooltip-n"><i class="icon-lock"></i></a>
											</span>
										<?php }
									}
								}?>
							</h2>
						</div>
					<?php }?>
					<h2>
						<?php if ($question_sticky == " sticky") {
							echo '<i class="icon-pushpin tooltip-n question-sticky" original-title="'.__("Sticky","vbegy").'"></i>';
						}?>
						<span itemprop="name"><?php the_title();?></span>
					</h2>
					<?php if ($active_reports == 1 && (is_user_logged_in || (!is_user_logged_in && $active_logged_reports != 1))) {?>
						<a class="question-report report_q" href="#"><?php _e("Report","vbegy")?></a>
					<?php }
					if ($question_poll == 1) {?>
						<div class="question-type-main"><i class="icon-signal"></i><?php _e("Poll","vbegy")?></div>
					<?php }else {?>
						<div class="question-type-main"><i class="icon-question-sign"></i><?php _e("Question","vbegy")?></div>
					<?php }?>
					<div class="question-inner">
						<div class="clearfix"></div>
						<div class="question-desc">
							<?php
							$comments = get_comments('post_id='.$post->ID);
							$custom_permission = vpanel_options("custom_permission");
							$show_question = vpanel_options("show_question");
							if (is_user_logged_in) {
								$user_is_login = get_userdata($user_get_current_user_id);
								$user_login_group = key($user_is_login->caps);
								$roles = $user_is_login->allcaps;
							}
							if ($custom_permission != 1 || (is_user_logged_in && isset($roles["show_question"]) && $roles["show_question"] == 1) || (!is_user_logged_in && $show_question == 1) || ($user_get_current_user_id > 0 && $user_get_current_user_id == $post->post_author) || ($user_get_current_user_id > 0 && $user_get_current_user_id == $anonymously_user)) {
								if ($active_reports == 1 && (is_user_logged_in || (!is_user_logged_in && $active_logged_reports != 1))) {?>
									<div class="explain-reported">
										<h3><?php _e("Please briefly explain why you feel this question should be reported .","vbegy")?></h3>
										<textarea name="explain-reported"></textarea>
										<div class="clearfix"></div>
										<div class="loader_3"></div>
										<div class="color button small report"><?php _e("Report","vbegy")?></div>
										<div class="color button small dark_button cancel"><?php _e("Cancel","vbegy")?></div>
									</div><!-- End reported -->
								<?php }
								if ($question_poll == 1) {?>
									<div class='question_poll_end'>
										<?php
										$poll_user_only = vpanel_options("poll_user_only");
										$question_poll_num = get_post_meta($post->ID,'question_poll_num',true);
										$asks = get_post_meta($post->ID,"ask",true);
										if ($asks && is_array($asks)) {
											$i = 0;?>
											<div class="poll_1" <?php if ($poll_user_only == 1 && $user_get_current_user_id == 0) {echo ' style="display:block"';}else if (!isset($_COOKIE['question_poll'.$post->ID])) {echo ' style="display:none"';}?>>
												<div class="progressbar-warp">
													<?php foreach($asks as $ask):$i++;
														if ($question_poll_num != "" || $question_poll_num != 0) {
															$value_poll = round((($ask['value'] > 0?$ask['value']:0)/$question_poll_num)*100,2);
														}?>
														<span class="progressbar-title"><?php echo stripslashes($ask['title'])?> <?php echo ($question_poll_num == 0?0:$value_poll)?>%<span><?php echo ($ask['value'] != ""?"( ".stripslashes($ask['value'])." ".__("voter","vbegy")." )":"")?></span></span>
														<div class="progressbar">
														    <div class="progressbar-percent <?php echo ($ask['value'] == 0?"poll-result":"")?>" <?php echo ($ask['value'] == 0?"":"style='background-color: #3498db;'")?> attr-percent="<?php echo ($ask['value'] == 0?100:$value_poll)?>"></div>
														</div>
													<?php endforeach;?>
												</div><!-- End progressbar-warp -->
												<?php
												if (empty($poll_user_only) || ($poll_user_only == 1 && $user_get_current_user_id != 0)) {
													if (!isset($_COOKIE['question_poll'.$post->ID])) { ?><a href='#' class='color button small poll_polls margin_0'><?php _e("Rating","vbegy")?></a><?php }
												}?>
											</div>
											<div class="clearfix"></div>
											<?php if (empty($poll_user_only) || ($poll_user_only == 1 && $user_get_current_user_id != 0)) {?>
												<div class="poll_2"><div class="loader_3"></div>
													<div class="form-style form-style-3">
														<div class="form-inputs clearfix">
															<?php if (!isset($_COOKIE['question_poll'.$post->ID])) {
																foreach($asks as $ask):$i++;
																	?>
																	<p>
																		<input id="ask[<?php echo $i?>][title]" name="ask_radio" type="radio" value="poll_<?php echo (int)$ask['id']?>" rel="poll_<?php echo stripslashes($ask['title'])?>">
																		<label for="ask[<?php echo $i?>][title]"><?php echo stripslashes($ask['title'])?></label>
																	</p>
																<?php endforeach;
															}?>
														</div>
													</div>
													<?php if (!isset($_COOKIE['question_poll'.$post->ID])) { ?><a href='#' class='color button small poll_results margin_0'><?php _e("Results","vbegy")?></a><?php }?>
												</div>
											<?php }
										}?>
									</div><!-- End question_poll_end -->
									<div class="clearfix height_20"></div>
									<?php
								}
								$video_description = get_post_meta($post->ID,'video_description',true);
								$video_desc_active = vpanel_options("video_desc_active");
								if ($video_desc_active == 1 && $video_description == 1) {
									$video_desc = get_post_meta($post->ID,'video_desc',true);
									$video_id = get_post_meta($post->ID,'video_id',true);
									$video_type = get_post_meta($post->ID,'video_type',true);
									if ($video_id != "") {
										if ($video_type == 'youtube') {
											$type = "https://www.youtube.com/embed/".$video_id;
										}else if ($video_type == 'vimeo') {
											$type = "https://player.vimeo.com/video/".$video_id;
										}else if ($video_type == 'daily') {
											$type = "https://www.dailymotion.com/embed/video/".$video_id;
										}
										if ($vbegy_sidebar_all == "full") {
									    	$las_video = '<div class="question-video"><iframe height="600" src="'.$type.'"></iframe></div>';
										}else {
									    	$las_video = '<div class="question-video"><iframe height="450" src="'.$type.'"></iframe></div>';
										}
										if (vpanel_options("video_desc") == "before") {
											echo $las_video;
										}
									}
								}
								$featured_image_single = vpanel_options("featured_image_single");
								if ($featured_image_single == 1) {
									$thumb = get_post_thumbnail_id((isset($post->ID) && $post->ID > 0?$post->ID:""));
									if (wp_get_attachment_image_srcset($thumb)) {
										$custom_featured_image_size = rwmb_meta('vbegy_custom_featured_image_size','checkbox',$post->ID);
										if ($custom_featured_image_size == 1) {
											$featured_image_question_width = rwmb_meta('vbegy_featured_image_width','slider',$post->ID);
											$featured_image_question_height = rwmb_meta('vbegy_featured_image_height','slider',$post->ID);
										}else {
											$featured_image_question_width = vpanel_options("featured_image_question_width");
											$featured_image_question_height = vpanel_options("featured_image_question_height");
										}
										$featured_image_question_lightbox = vpanel_options("featured_image_question_lightbox");
										$featured_image_question_width = ($featured_image_question_width != ""?$featured_image_question_width:260);
										$featured_image_question_height = ($featured_image_question_height != ""?$featured_image_question_height:185);
										$img_lightbox = ($featured_image_question_lightbox == 1?"lightbox":false);
										$featured_position = vpanel_options("featured_position");
										if ($featured_position != "after") {
											echo "<div class='featured_image_question'>".askme_resize_img($featured_image_question_width,$featured_image_question_height,$img_lightbox)."</div>
											<div class='clearfix'></div>";
										}
									}
								}?>
								
								<div class="content-text" itemprop="text"><?php the_content();?></div>
								
								<?php if ($featured_image_single == 1 && wp_get_attachment_image_srcset($thumb) && $featured_position == "after") {
									echo "<div class='featured_image_question featured_image_after'>".askme_resize_img($featured_image_question_width,$featured_image_question_height,$img_lightbox)."</div>
									<div class='clearfix'></div>";
								}
								if (vpanel_options("video_desc") == "after" && $video_desc_active == 1 && isset($video_id) && $video_id != "" && $video_description == 1) {
									echo $las_video;
								}
								if (is_user_logged_in) {
									if ($user_get_current_user_id != $get_question_user_id && (($user_get_current_user_id != $post->post_author && $post->post_author > 0) || ($anonymously_user != "" && $anonymously_user != $user_get_current_user_id))) {
										$user_login_id2 = get_user_by("id",$user_get_current_user_id);
										$_favorites = get_user_meta($user_get_current_user_id,$user_login_id2->user_login."_favorites",true);
										if (isset($_favorites) && is_array($_favorites) && in_array($post->ID,$_favorites)) {?>
											<a class="remove_favorite add_favorite_in color button small" title="<?php _e("Remove the question of my favorites","vbegy")?>" href="#"><?php _e("Remove the question of my favorites","vbegy")?></a>
										<?php }else {?>
											<a class="add_favorite add_favorite_in color button small" title="<?php _e("Add a question to Favorites","vbegy")?>" href="#"><?php _e("Add a question to Favorites","vbegy")?></a>
										<?php }
									}
									$question_bump = vpanel_options("question_bump");
									$active_points = vpanel_options("active_points");
									if (empty($comments) && (($user_get_current_user_id == $post->post_author && $post->post_author != 0) || ($user_get_current_user_id == $anonymously_user && $anonymously_user != 0)) && $question_bump == 1 && $active_points == 1) {?>
										<div class="form-style form-style-2 form-add-point">
											<p class="clearfix">
												<input id="input-add-point" name="" type="text" placeholder="<?php _e("Question bump points","vbegy")?>">
												<a class="color button small margin_0 f_left" href="#"><?php _e("Bump","vbegy")?></a>
											</p>
										</div>
									<?php }
									$pay_to_sticky = vpanel_options("pay_to_sticky");
									if ($pay_to_sticky == 1) {
										if (($end_sticky_time == "" || ($end_sticky_time != "" && $end_sticky_time < strtotime(date("Y-m-d"))))) {
											$active_coupons = vpanel_options("active_coupons");
											$free_coupons   = vpanel_options("free_coupons");
											$currency_code  = vpanel_options("currency_code");
											$coupons        = get_option("coupons");
											$days_sticky    = (int)vpanel_options("days_sticky");
											$days_sticky    = ($days_sticky > 0?$days_sticky:7);
											
											$_allow_to_sticky = get_user_meta($user_get_current_user_id,$user_get_current_user_id."_allow_to_sticky",true);
											if (isset($_POST["process"]) && $_POST["process"] == "sticky") {
												update_post_meta($post->ID,"sticky",1);
												$sticky_questions = get_option('sticky_questions');
												if (is_array($sticky_questions)) {
													if (!in_array($post->ID,$sticky_questions)) {
														$array_merge = array_merge($sticky_questions,array($post->ID));
														update_option("sticky_questions",$array_merge);
													}
												}else {
													update_option("sticky_questions",array($post->ID));
												}
												update_post_meta($post->ID,"start_sticky_time",strtotime(date("Y-m-d")));
												update_post_meta($post->ID,"end_sticky_time",strtotime(date("Y-m-d",strtotime(date("Y-m-d")." +$days_sticky days"))));
												wp_safe_redirect(get_the_permalink());
												die();
											}
											
											if ((($anonymously_user > 0 && $user_get_current_user_id == $anonymously_user) || ($post->post_author > 0 && $user_get_current_user_id == $post->post_author)) && (($end_sticky_time != "" && $end_sticky_time < strtotime(date("Y-m-d"))) || (!is_sticky())) && isset($_allow_to_sticky) && (int)$_allow_to_sticky < 1 && $pay_to_sticky == 1) {
												$pay_sticky_payment = $last_payment = (int)vpanel_options("pay_sticky_payment");
												echo '<a href="#" class="pay-to-sticky color button small f_left">'.__("Pay to sticky question","vbegy").'</a>
												<div class="clearfix"></div>
												<div class="pay-to-sticky-area">';
													if ($active_coupons == 1) {
														if (isset($_POST["add_coupon"]) && $_POST["add_coupon"] == "submit") {
															$coupon_name = esc_attr($_POST["coupon_name"]);
															$coupons_not_exist = "no";
															
															if (isset($coupons) && is_array($coupons)) {
																foreach ($coupons as $coupons_k => $coupons_v) {
																	if (is_array($coupons_v) && in_array($coupon_name,$coupons_v)) {
																		$coupons_not_exist = "yes";
																		
																		if (isset($coupons_v["coupon_date"]) && $coupons_v["coupon_date"] != "") {
																			$coupons_v["coupon_date"] = !is_numeric($coupons_v["coupon_date"]) ? strtotime($coupons_v["coupon_date"]):$coupons_v["coupon_date"];
																		}
																		
																		if (isset($coupons_v["coupon_date"]) && $coupons_v["coupon_date"] != "" && current_time( 'timestamp' ) > $coupons_v["coupon_date"]) {
																			echo '<div class="alert-message error"><p>'.__("This coupon has expired.","vbegy").'</p></div>';
																		}else if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "percent" && (int)$coupons_v["coupon_amount"] > 100) {
																			echo '<div class="alert-message error"><p>'.__("This coupon is not valid.","vbegy").'</p></div>';
																		}else if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "discount" && (int)$coupons_v["coupon_amount"] > $pay_sticky_payment) {
																			echo '<div class="alert-message error"><p>'.__("This coupon is not valid.","vbegy").'</p></div>';
																		}else {
																			if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "percent") {
																				$the_discount = ($pay_sticky_payment*$coupons_v["coupon_amount"])/100;
																				$last_payment = $pay_sticky_payment-$the_discount;
																			}else if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "discount") {
																				$last_payment = $pay_sticky_payment-$coupons_v["coupon_amount"];
																			}
																			echo '<div class="alert-message success"><p>'.sprintf(__("Coupon ".'"%s"'." applied successfully.","vbegy"),$coupon_name).'</p></div>';
																			
																			update_user_meta($user_get_current_user_id,$user_get_current_user_id."_coupon",esc_attr($coupons_v["coupon_name"]));
																			update_user_meta($user_get_current_user_id,$user_get_current_user_id."_coupon_value",($last_payment <= 0?"free":$last_payment));
																		}
																	}
																}
															}
															
															if ($coupons_not_exist == "no" && $coupon_name == "") {
																echo '<div class="alert-message error"><p>'.__("Coupon does not exist!.","vbegy").'</p></div>';
															}else if ($coupons_not_exist == "no") {
																echo '<div class="alert-message error"><p>'.sprintf(__("Coupon ".'"%s"'." does not exist!.","vbegy"),$coupon_name).'</p></div>';
															}
														}else {
															delete_user_meta($user_get_current_user_id,$user_get_current_user_id."_coupon");
															delete_user_meta($user_get_current_user_id,$user_get_current_user_id."_coupon_value");
														}
													}
													
													echo '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Pay to sticky","vbegy").'</span><br>'.__("Please make a payment to allow to be able to sticky the question.","vbegy").' "'.$last_payment." ".$currency_code.'" '.sprintf(__("For %s days.","vbegy"),$days_sticky).'</p></div>';
													
													if (isset($coupons) && is_array($coupons) && $free_coupons == 1 && $active_coupons == 1) {
														foreach ($coupons as $coupons_k => $coupons_v) {
															$pay_sticky_payments = $last_payments = (int)vpanel_options("pay_sticky_payment");
															if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "percent") {
																$the_discount = ($pay_sticky_payments*$coupons_v["coupon_amount"])/100;
																$last_payments = $pay_sticky_payments-$the_discount;
															}else if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "discount") {
																$last_payments = $pay_sticky_payments-$coupons_v["coupon_amount"];
															}
															
															if ($last_payments <= 0) {
																if (isset($coupons_v["coupon_date"]) && $coupons_v["coupon_date"] != "") {
																	$coupons_v["coupon_date"] = !is_numeric($coupons_v["coupon_date"]) ? strtotime($coupons_v["coupon_date"]):$coupons_v["coupon_date"];
																}
																
																if ((isset($coupons_v["coupon_date"]) && $coupons_v["coupon_date"] != "" && current_time( 'timestamp' ) > $coupons_v["coupon_date"]) && (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "percent" && (int)$coupons_v["coupon_amount"] > 100) && (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "discount" && (int)$coupons_v["coupon_amount"] > $pay_sticky_payments)) {
																	
																}else {
																	echo '<div class="alert-message warning"><i class="icon-ok"></i><p><span>'.__("Free","vbegy").'</span><br>'.__("Sticky question free? Add this coupon.","vbegy").' "'.$coupons_v["coupon_name"].'"</p></div>';
																}
															}
														}
													}
													
													if ($active_coupons == 1) {
														echo '<div class="coupon_area">
															<form method="post" action="">
																<input type="text" name="coupon_name" id="coupon_name" value="" placeholder="Coupon code">
																<input type="submit" class="button" value="'.__("Apply Coupon","vbegy").'">
																<input type="hidden" name="add_coupon" value="submit">
															</form>
														</div>';
													}
													
													echo '<div class="clearfix"></div>';
													
													if ($last_payment > 0) {
														echo '<div class="payment_area">
															<form method="post" action="?action=process">
																<input type="hidden" name="question_sticky" value="'.$post->ID.'">
																<input type="hidden" name="CatDescription" value="'.__("Pay to make question sticky","vbegy").'">
																<input type="hidden" name="item_number" value="pay_sticky">
																<input type="hidden" name="payment" value="'.$last_payment.'">
																<input type="hidden" name="quantity" value="1" />
																<input type="hidden" name="key" value="'.md5(date("Y-m-d:").rand()).'">
																<input type="hidden" name="go" value="paypal">
																<input type="hidden" name="currency_code" value="'.$currency_code.'">
																'.(isset($coupon_name) && $coupon_name != ''?'<input type="hidden" name="coupon" value="'.$coupon_name.'">':'').'
																<input type="hidden" name="cpp_header_image" value="'.get_template_directory_uri().'/images/payment.gif">
																<input type="image" src="'.get_template_directory_uri().'/images/payment.gif" border="0" name="submit" alt="'. __("Pay now","vbegy").'">
															</form>
														</div>';
													}else {
														$ask_find_coupons = ask_find_coupons($coupons,$_POST["coupon_name"]);
														
														echo '<div class="process_area">
															<form method="post" action="'.get_the_permalink().'">
																<input type="submit" class="button" value="'.__("Process","vbegy").'">
																<input type="hidden" name="process" value="sticky">';
																if (isset($ask_find_coupons) && $ask_find_coupons != "" && $active_coupons == 1) {
																	echo '<input type="hidden" name="coupon" value="'.esc_attr($_POST["coupon_name"]).'">';
																}
															echo '</form>
														</div>';
													}
												echo '</div>';
											}
										}
									}
								}?>
								<div class="clearfix"></div>
								<div class="loader_2"></div>
								<?php
								$added_file = get_post_meta($post->ID, 'added_file', true);
								if ($added_file != "") {
									echo "<div class='clearfix'></div><br><a class='attachment-link' href='".wp_get_attachment_url($added_file)."'><i class='icon-link'></i>".__("Attachment","vbegy")."</a>";
								}
								$attachment_m = get_post_meta($post->ID, 'attachment_m',true);
								if (isset($attachment_m) && is_array($attachment_m) && !empty($attachment_m)) {
									foreach ($attachment_m as $key => $value) {
										echo "<div class='clearfix'></div><br><a class='attachment-link' href='".wp_get_attachment_url($value["added_file"])."'><i class='icon-link'></i>".__("Attachment","vbegy")."</a>";
									}
								}?>
								<div class='clearfix'></div>
								
								<?php if ($question_control_style == "style_2" && (is_super_admin($user_get_current_user_id) || ($post->post_author != 0 && $post->post_author == $user_get_current_user_id) || ($post->post_author != 0 && $post->post_author == $user_get_current_user_id) || ($question_follow == 1 && is_user_logged_in && $user_get_current_user_id != $get_question_user_id && (($user_get_current_user_id != $post->post_author && $post->post_author > 0) || ($anonymously_user != "" && $anonymously_user != $user_get_current_user_id))))) {?>
									<div class="edit-delete-follow-close-2">
										<?php $question_edit = vpanel_options("question_edit");
										if (($post->post_author != 0 && $post->post_author == $user_get_current_user_id) || is_super_admin($user_get_current_user_id)) {
											if ($question_edit == 1 || is_super_admin($user_get_current_user_id)) {?>
												<div class="question-edit">
													<a href="<?php echo esc_url(add_query_arg("q", $post->ID,get_page_link(vpanel_options('edit_question'))))?>" original-title="<?php _e("Edit the question","vbegy")?>" class="tooltip-n color button small margin_0 f_left"><?php _e("Edit","vbegy")?></a>
												</div>
											<?php }
											if ($question_delete == 1 || is_super_admin($user_get_current_user_id)) {?>
												<div class="question-delete">
													<a href="<?php echo esc_url(add_query_arg("delete", $post->ID,get_permalink($post->ID)))?>" original-title="<?php _e("Delete the question","vbegy")?>" class="tooltip-n color button small margin_0 f_left"><?php _e("Delete","vbegy")?></a>
												</div>
											<?php }
										}
										
										if (($question_follow == 1 || is_super_admin($user_get_current_user_id)) && is_user_logged_in && $user_get_current_user_id != $get_question_user_id && (($user_get_current_user_id != $post->post_author && $post->post_author > 0) || ($anonymously_user != "" && $anonymously_user != $user_get_current_user_id))) {?>
											<div class="question-follow">
												<?php if (isset($following_questions) && is_array($following_questions) && in_array($user_get_current_user_id,$following_questions)) {?>
													<a href="#" original-title="<?php _e("Unfollow the question","vbegy")?>" class="tooltip-n unfollow-question color button small margin_0 f_left"><?php _e("Unfollow","vbegy")?></a>
												<?php }else {?>
													<a href="#" original-title="<?php _e("Follow the question","vbegy")?>" class="tooltip-n color button small margin_0 f_left"><?php _e("Follow","vbegy")?></a>
												<?php }?>
											</div>
										<?php }
										if (($post->post_author != 0 && $post->post_author == $user_get_current_user_id) || is_super_admin($user_get_current_user_id)) {
											$question_close = vpanel_options("question_close");
											if (isset($question_close) && $question_close == 1) {
												if (isset($closed_question) && $closed_question == 1) {?>
													<div class="question-open">
														<a href="#" original-title="<?php _e("Open the question","vbegy")?>" class="tooltip-n color button small margin_0 f_left"><?php _e("Open","vbegy")?></a>
													</div>
												<?php }else {?>
													<div class="question-close">
														<a href="#" original-title="<?php _e("Close the question","vbegy")?>" class="tooltip-n color button small margin_0 f_left"><?php _e("Close","vbegy")?></a>
													</div>
												<?php }
											}
										}?>
										<div class="clearfix"></div>
									</div>
								<?php }?>
								<div class="no_vote_more"></div>
							<?php }else {
								echo '<div class="note_error"><strong>'.__("Sorry do not have permission to show the questions !","vbegy").'</strong></div>';
							}?>
						</div>
						<?php do_action("askme_before_end_meta");
						$questions_meta = vpanel_options("questions_meta_single");
						if (isset($questions_meta["status"]) && $questions_meta["status"] == 1) {?>
							<div class="question-details">
								<?php if (isset($the_best_answer) && $the_best_answer != "" && $comments) {?>
									<span class="question-answered question-answered-done"><i class="icon-ok"></i><?php _e("solved","vbegy")?></span>
								<?php }else if (isset($closed_question) && $closed_question == 1) {?>
									<span class="question-answered question-closed"><i class="icon-lock"></i><?php _e("closed","vbegy")?></span>
								<?php }else if ($the_best_answer == "" && $comments) {?>
									<span class="question-answered"><i class="icon-ok"></i><?php _e("in progress","vbegy")?></span>
								<?php }?>
								<span class="question-favorite"><i class="<?php echo ($question_favorites > 0?"icon-star":"icon-star-empty");?>"></i><?php echo ($question_favorites != ""?$question_favorites:0);?></span>
							</div>
						<?php }
						if (isset($questions_meta["category"]) && $questions_meta["category"] == 1) {
							echo get_the_term_list($post->ID,ask_question_category,'<span class="question-category"><i class="fa fa-folder-o"></i>',', ','</span>');
						}
						if (isset($questions_meta["user_name"]) && $questions_meta["user_name"] == 1) {?>
							<span class="question-author-meta">
								<?php if ($post->post_author == 0) {
									$anonymously_question = get_post_meta($post->ID,'anonymously_question',true);
									if ($anonymously_question == 1 && $anonymously_user != "") {
										$question_username = esc_html__("Anonymous","vbegy");
										$question_email = 0;
									}else {
										$question_username = get_post_meta($post->ID, 'question_username',true);
										$question_email = get_post_meta($post->ID, 'question_email',true);
										$question_username = ($question_username != ""?$question_username:esc_html__("Anonymous","vbegy"));
										$question_email = ($question_email != ""?$question_email:0);
									}?>
									<i class="icon-user"></i><span><?php echo $question_username?></span>
								<?php }else {?>
									<a href="<?php echo vpanel_get_user_url($post->post_author)?>"><i class="icon-user"></i><?php echo get_the_author()?></a>
								<?php }?>
							</span>
							<?php if ($get_question_user_id != "") {
								$display_name = get_the_author_meta('display_name',$get_question_user_id);
								if (isset($display_name) && $display_name != "") {?>
									<span class="question-author-meta"><a href="<?php echo vpanel_get_user_url($get_question_user_id);?>" title="<?php echo esc_attr($display_name)?>"><i class="icon-user"></i><?php echo esc_html__("Asked to","vbegy")." : ".esc_attr($display_name)?></a></span>
								<?php }
							}
						}
						if (isset($questions_meta["date"]) && $questions_meta["date"] == 1) {?>
							<span class="question-date" itemprop="dateCreated" datetime="<?php echo get_the_date(); ?>"><i class="fa fa-calendar"></i><?php echo human_time_diff(get_the_time('U'), current_time('timestamp'));?></span>
						<?php }
						if (isset($questions_meta["answer_meta"]) && $questions_meta["answer_meta"] == 1) {?>
							<span class="question-comment"><a href="<?php echo comments_link()?>"><i class="fa fa-comments-o"></i><span itemprop="answerCount"><?php echo get_comments_number()?></span> <?php comments_number(__('Answers','vbegy'),__('Answer','vbegy'), __('Answers','vbegy'))?></a></span>
						<?php }
						if (isset($questions_meta["view"]) && $questions_meta["view"] == 1) {?>
							<span class="question-view"><i class="icon-eye-open"></i><?php $post_stats = get_post_meta($post->ID, 'post_stats', true);echo ($post_stats != ""?$post_stats:0);?> <?php _e("views","vbegy");?></span>
						<?php }
						if (isset($post->post_author) && $post->post_author > 0) {
							echo vpanel_get_badge($post->post_author);
						}
						do_action("askme_after_end_meta",$post);
						$active_vote = vpanel_options("active_vote");
						$show_dislike_questions = vpanel_options("show_dislike_questions");
						if ($active_vote == 1) {?>
							<span itemprop="upvoteCount" class="single-question-vote-result question_vote_result"><?php echo ($question_vote != ""?$question_vote:0)?></span>
							<ul class="single-question-vote">
								<?php if (is_user_logged_in && $post->post_author != $user_get_current_user_id && $anonymously_user != $user_get_current_user_id) {
									if ($show_dislike_questions != 1) {?>
										<li><a href="#" id="question_vote_down-<?php echo $post->ID?>" class="single-question-vote-down ask_vote_down question_vote_down vote_allow<?php echo (isset($_COOKIE['question_vote'.$post->ID])?" ".$_COOKIE['question_vote'.$post->ID]."-".$post->ID:"")?> tooltip_s" title="<?php _e("Dislike","vbegy");?>"><i class="icon-thumbs-down"></i></a></li>
									<?php }?>
									<li><a href="#" id="question_vote_up-<?php echo $post->ID?>" class="single-question-vote-up ask_vote_up question_vote_up vote_allow<?php echo (isset($_COOKIE['question_vote'.$post->ID])?" ".$_COOKIE['question_vote'.$post->ID]."-".$post->ID:"")?> tooltip_s" title="<?php _e("Like","vbegy");?>"><i class="icon-thumbs-up"></i></a></li>
								<?php }else {
									if ($show_dislike_questions != 1) {?>
										<li><a href="#" class="single-question-vote-down ask_vote_down question_vote_down <?php echo (is_user_logged_in && (($post->post_author == $user_get_current_user_id) || ($anonymously_user == $user_get_current_user_id))?"vote_not_allow":"vote_not_user")?> tooltip_s" original-title="<?php _e("Dislike","vbegy");?>"><i class="icon-thumbs-down"></i></a></li>
									<?php }?>
									<li><a href="#" class="single-question-vote-up ask_vote_up question_vote_up <?php echo (is_user_logged_in && (($post->post_author == $user_get_current_user_id) || ($anonymously_user == $user_get_current_user_id))?"vote_not_allow":"vote_not_user")?> tooltip_s" original-title="<?php _e("Like","vbegy");?>"><i class="icon-thumbs-up"></i></a></li>
								<?php }?>
							</ul>
						<?php }?>
						<div class="clearfix"></div>
					</div>
				</article>
				
				<?php $terms = wp_get_object_terms( $post->ID, 'question_tags' );
				$post_share = vpanel_options("question_share");
				if ($terms || (($post_share == 1 && $post_share_s == "") || ($post_share == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_share == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_share_s) && $post_share_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_share_s) && $post_share_s == 1))) {?>
					<div class="share-tags page-content">
						<?php
						if ($terms) :
							echo '<div class="question-tags"><i class="icon-tags"></i>';
								$terms_array = array();
								foreach ($terms as $term) :
									$terms_array[] = '<a href="'.get_term_link($term->slug, 'question_tags').'">'.$term->name.'</a>';
								endforeach;
								echo implode(' , ', $terms_array);
							echo '</div>';
						endif;
						
						if (($post_share == 1 && $post_share_s == "") || ($post_share == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_share == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_share_s) && $post_share_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_share_s) && $post_share_s == 1)) {?>
							<div class="share-inside-warp">
								<ul>
									<li>
										<a href="https://www.facebook.com/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank">
											<span class="icon_i">
												<span class="icon_square" icon_size="20" span_bg="#3b5997" span_hover="#666">
													<i i_color="#FFF" class="social_icon-facebook"></i>
												</span>
											</span>
										</a>
										<a href="https://www.facebook.com/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank"><?php _e("Facebook","vbegy");?></a>
									</li>
									<li>
										<a href="https://twitter.com/home?status=<?php echo urlencode(get_permalink());?>" target="_blank">
											<span class="icon_i">
												<span class="icon_square" icon_size="20" span_bg="#00baf0" span_hover="#666">
													<i i_color="#FFF" class="social_icon-twitter"></i>
												</span>
											</span>
										</a>
										<a target="_blank" href="https://twitter.com/home?status=<?php echo urlencode(get_permalink());?>"><?php _e("Twitter","vbegy");?></a>
									</li>
									<li>
										<a href="https://plus.google.com/share?url=<?php echo urlencode(get_permalink());?>" target="_blank">
											<span class="icon_i">
												<span class="icon_square" icon_size="20" span_bg="#ca2c24" span_hover="#666">
													<i i_color="#FFF" class="social_icon-gplus"></i>
												</span>
											</span>
										</a>
										<a href="https://plus.google.com/share?url=<?php echo urlencode(get_permalink());?>" target="_blank"><?php _e("Google plus","vbegy");?></a>
									</li>
									<li>
										<a href="https://www.tumblr.com/share/link?url=<?php echo urlencode(get_permalink()) ?>&amp;name=<?php echo urlencode(get_the_title()) ?>" target="_blank">
											<span class="icon_i">
												<span class="icon_square" icon_size="20" span_bg="#44546b" span_hover="#666">
													<i i_color="#FFF" class="social_icon-tumblr"></i>
												</span>
											</span>
										</a>
										<a href="https://www.tumblr.com/share/link?url=<?php echo urlencode(get_permalink()) ?>&amp;name=<?php echo urlencode(get_the_title()) ?>" target="_blank"><?php _e("Tumblr","vbegy");?></a>
									</li>
									<?php $pinterestimage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );?>
									<li>
										<a target="_blank" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink($post->ID)); ?>&media=<?php echo $pinterestimage[0]; ?>&description=<?php the_title(); ?>">
											<span class="icon_i">
												<span class="icon_square" icon_size="20" span_bg="#c7151a" span_hover="#666">
													<i i_color="#FFF" class="icon-pinterest"></i>
												</span>
											</span>
										</a>
										<a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink($post->ID)); ?>&media=<?php echo $pinterestimage[0]; ?>&description=<?php the_title(); ?>" target="_blank"><?php _e("Pinterest","vbegy");?></a>
									</li>
									<li>
										<a target="_blank" onClick="popup = window.open('mailto:?subject=<?php the_title(); ?>&amp;body=<?php the_permalink(); ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#">
											<span class="icon_i">
												<span class="icon_square" icon_size="20" span_bg="#000" span_hover="#666">
													<i i_color="#FFF" class="social_icon-email"></i>
												</span>
											</span>
										</a>
										<a target="_blank" onClick="popup = window.open('mailto:?subject=<?php the_title(); ?>&amp;body=<?php the_permalink(); ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#"><?php _e("Email","vbegy");?></a>
									</li>
								</ul>
								<span class="share-inside-f-arrow"></span>
								<span class="share-inside-l-arrow"></span>
							</div><!-- End share-inside-warp -->
							<div class="share-inside"><i class="icon-share-alt"></i><?php _e("Share","vbegy");?></div>
						<?php }?>
						<div class="clearfix"></div>
					</div><!-- End share-tags -->
				<?php }
			}
		endwhile; endif;
		
		if (!is_super_admin($user_get_current_user_id) && $yes_private != 1) {
		
		}else {
			$vbegy_custom_sections = get_post_meta($post->ID,"vbegy_custom_sections",true);
			if (isset($vbegy_custom_sections) && $vbegy_custom_sections == 1) {
				$order_sections = get_post_meta($post->ID,"order_sections_li",true);
			}else {
				$order_sections = vpanel_options("order_sections_question");
			}
			if (empty($order_sections)) {
				$order_sections = array(1 => "advertising",2 => "author",3 => "related",4 => "advertising_2",5 => "comments",6 => "next_previous");
			}
			foreach ($order_sections as $key_r => $value_r) {
				if ($value_r == "") {
					unset($order_sections[$key_r]);
				}else {
					if ($value_r == "advertising") {
						$vbegy_share_adv_type = rwmb_meta('vbegy_share_adv_type','radio',$post->ID);
						$vbegy_share_adv_code = rwmb_meta('vbegy_share_adv_code','textarea',$post->ID);
						$vbegy_share_adv_href = rwmb_meta('vbegy_share_adv_href','text',$post->ID);
						$vbegy_share_adv_img = rwmb_meta('vbegy_share_adv_img','upload',$post->ID);
						
						if ((is_single() || is_page()) && (($vbegy_share_adv_type == "display_code" && $vbegy_share_adv_code != "") || ($vbegy_share_adv_type == "custom_image" && $vbegy_share_adv_img != ""))) {
							$share_adv_type = $vbegy_share_adv_type;
							$share_adv_code = $vbegy_share_adv_code;
							$share_adv_href = $vbegy_share_adv_href;
							$share_adv_img = $vbegy_share_adv_img;
						}else {
							$share_adv_type = vpanel_options("share_adv_type");
							$share_adv_code = vpanel_options("share_adv_code");
							$share_adv_href = vpanel_options("share_adv_href");
							$share_adv_img = vpanel_options("share_adv_img");
						}
						if (($share_adv_type == "display_code" && $share_adv_code != "") || ($share_adv_type == "custom_image" && $share_adv_img != "")) {
							echo '<div class="clearfix"></div>
							<div class="advertising">';
							if ($share_adv_type == "display_code") {
								echo stripcslashes(do_shortcode($share_adv_code));
							}else {
								if ($share_adv_href != "") {
									echo '<a target="_blank" href="'.$share_adv_href.'">';
								}
								echo '<img alt="" src="'.$share_adv_img.'">';
								if ($share_adv_href != "") {
									echo '</a>';
								}
							}
							echo '</div><!-- End advertising -->
							<div class="clearfix"></div>';
						}
					}else if ($value_r == "advertising_2") {
						$vbegy_related_adv_type = rwmb_meta('vbegy_related_adv_type','radio',$post->ID);
						$vbegy_related_adv_code = rwmb_meta('vbegy_related_adv_code','textarea',$post->ID);
						$vbegy_related_adv_href = rwmb_meta('vbegy_related_adv_href','text',$post->ID);
						$vbegy_related_adv_img = rwmb_meta('vbegy_related_adv_img','upload',$post->ID);
						
						if ((is_single() || is_page()) && (($vbegy_related_adv_type == "display_code" && $vbegy_related_adv_code != "") || ($vbegy_related_adv_type == "custom_image" && $vbegy_related_adv_img != ""))) {
							$related_adv_type = $vbegy_related_adv_type;
							$related_adv_code = $vbegy_related_adv_code;
							$related_adv_href = $vbegy_related_adv_href;
							$related_adv_img = $vbegy_related_adv_img;
						}else {
							$related_adv_type = vpanel_options("related_adv_type");
							$related_adv_code = vpanel_options("related_adv_code");
							$related_adv_href = vpanel_options("related_adv_href");
							$related_adv_img = vpanel_options("related_adv_img");
						}
						if (($related_adv_type == "display_code" && $related_adv_code != "") || ($related_adv_type == "custom_image" && $related_adv_img != "")) {
							echo '<div class="clearfix"></div>
							<div class="advertising">';
							if ($related_adv_type == "display_code") {
								echo stripcslashes(do_shortcode($related_adv_code));
							}else {
								if ($related_adv_href != "") {
									echo '<a target="_blank" href="'.$related_adv_href.'">';
								}
								echo '<img alt="" src="'.$related_adv_img.'">';
								if ($related_adv_href != "") {
									echo '</a>';
								}
							}
							echo '</div><!-- End advertising -->
							<div class="clearfix"></div>';
						}
					}else if ($value_r == "author") {
						$post_author_box = vpanel_options("question_author_box");
						if (($post_author_box == 1 && $post_author_box_s == "") || ($post_author_box == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_author_box == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_author_box_s) && $post_author_box_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_author_box_s) && $post_author_box_s == 1)) {
							if ($post->post_author > 0) {
								$twitter = get_the_author_meta('twitter',$post->post_author);
								$facebook = get_the_author_meta('facebook',$post->post_author);
								$google = get_the_author_meta('google',$post->post_author);
								$linkedin = get_the_author_meta('linkedin',$post->post_author);
								$follow_email = get_the_author_meta('follow_email',$post->post_author);
								$youtube = get_the_author_meta('youtube',$post->post_author);
								$pinterest = get_the_author_meta('pinterest',$post->post_author);
								$instagram = get_the_author_meta('instagram',$post->post_author);
								$verified_user = get_the_author_meta('verified_user',$post->post_author);?>
								<div itemprop="author" itemscope itemtype="http://schema.org/Person" class="about-author clearfix">
									<span itemprop="name" class="hide"><?php echo $authordata->display_name?></span>
								    <div class="author-image">
								    	<a href="<?php echo vpanel_get_user_url($post->post_author,$authordata->nickname);?>" original-title="<?php the_author();?>" class="tooltip-n">
								    		<?php echo askme_user_avatar(get_the_author_meta('you_avatar', $post->post_author),65,65,$post->post_author,$authordata->display_name,null,true);?>
								    	</a>
								    </div>
								    <div class="author-bio">
								        <h4>
								        	<?php echo __("About","vbegy")." <a href='".vpanel_get_user_url($post->post_author,$authordata->nickname)."'>".get_the_author()."</a>";
								        	if ($verified_user == 1) {
								        		echo '<img class="verified_user tooltip-n" alt="'.__("Verified","vbegy").'" original-title="'.__("Verified","vbegy").'" src="'.get_template_directory_uri().'/images/verified.png">';
								        	}
								        	echo vpanel_get_badge($post->post_author);?>
								        </h4>
								        <?php the_author_meta('description');?>
								        <div class="clearfix"></div>
								        <?php if ($facebook || $twitter || $linkedin || $google || $follow_email || $youtube || $pinterest || $instagram) { ?>
								        	<br>
								        	<span class="user-follow-me"><?php _e("Follow Me","vbegy")?></span>
								        	<div class="social_icons_display_2">
									        	<?php if ($facebook) {?>
										        	<a href="<?php echo $facebook?>" original-title="<?php _e("Facebook","vbegy")?>" class="tooltip-n">
										        		<span class="icon_i">
										        			<span class="icon_square" icon_size="30" span_bg="#3b5997" span_hover="#2f3239">
										        				<i class="social_icon-facebook"></i>
										        			</span>
										        		</span>
										        	</a>
									        	<?php }
									        	if ($twitter) {?>
										        	<a href="<?php echo $twitter?>" original-title="<?php _e("Twitter","vbegy")?>" class="tooltip-n">
										        		<span class="icon_i">
										        			<span class="icon_square" icon_size="30" span_bg="#00baf0" span_hover="#2f3239">
										        				<i class="social_icon-twitter"></i>
										        			</span>
										        		</span>
										        	</a>
									        	<?php }
									        	if ($linkedin) {?>
										        	<a href="<?php echo $linkedin?>" original-title="<?php _e("Linkedin","vbegy")?>" class="tooltip-n">
										        		<span class="icon_i">
										        			<span class="icon_square" icon_size="30" span_bg="#006599" span_hover="#2f3239">
										        				<i class="social_icon-linkedin"></i>
										        			</span>
										        		</span>
										        	</a>
									        	<?php }
									        	if ($google) {?>
										        	<a href="<?php echo $google?>" original-title="<?php _e("Google plus","vbegy")?>" class="tooltip-n">
										        		<span class="icon_i">
										        			<span class="icon_square" icon_size="30" span_bg="#c43c2c" span_hover="#2f3239">
										        				<i class="social_icon-gplus"></i>
										        			</span>
										        		</span>
										        	</a>
									        	<?php }
									        	if ($pinterest) {?>
										        	<a href="<?php echo $pinterest?>" original-title="<?php _e("Pinterest","vbegy")?>" class="tooltip-n">
										        		<span class="icon_i">
										        			<span class="icon_square" icon_size="30" span_bg="#e13138" span_hover="#2f3239">
										        				<i class="social_icon-pinterest"></i>
										        			</span>
										        		</span>
										        	</a>
									        	<?php }
									        	if ($instagram) {?>
										        	<a href="<?php echo $instagram?>" original-title="<?php _e("Instagram","vbegy")?>" class="tooltip-n">
										        		<span class="icon_i">
										        			<span class="icon_square" icon_size="30" span_bg="#548bb6" span_hover="#2f3239">
										        				<i class="social_icon-instagram"></i>
										        			</span>
										        		</span>
										        	</a>
									        	<?php }
									        	if ($follow_email) {?>
										        	<a href="mailto:<?php echo $authordata->user_email?>" original-title="<?php _e("Email","vbegy")?>" class="tooltip-n">
										        		<span class="icon_i">
										        			<span class="icon_square" icon_size="30" span_bg="#000" span_hover="#2f3239">
										        				<i class="social_icon-email"></i>
										        			</span>
										        		</span>
										        	</a>
									        	<?php }?>
								        	</div>
								        <?php }?>
								    </div>
								</div><!-- End about-author -->
							<?php }
						}
					}else if ($value_r == "related") {
						$related_post = vpanel_options("related_question");
						if (($related_post == 1 && $related_post_s == "") || ($related_post == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($related_post == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($related_post_s) && $related_post_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($related_post_s) && $related_post_s == 1)) {
							$related_no = vpanel_options('related_number_question') ? vpanel_options('related_number_question') : 5;
							global $post;
							$orig_post = $post;
							$related_query_ = array();
							$related_cat_tag = vpanel_options("related_query_question");
							
							if ($related_cat_tag == "tags") {
								$term_list = wp_get_post_terms($post->ID, 'question_tags', array("fields" => "ids"));
								$related_query_ = array('tax_query' => array(array('taxonomy' => 'question_tags','field' => 'id','terms' => $term_list,'operator' => 'IN')));
							}else {
								$categories = wp_get_post_terms($post->ID,ask_question_category,array("fields" => "ids"));
								$related_query_ = array('tax_query' => array(array('taxonomy' => ask_question_category,'field' => 'id','terms' => $categories,'operator' => 'IN')));
							}
							
							$args = array_merge($related_query_,array('post_type' => 'question','post__not_in' => array($post->ID),'posts_per_page'=> $related_no));
							$related_query = new wp_query( $args );
							if ($related_query->have_posts()) : ;?>
								<div id="related-posts">
									<h2><?php _e("Related questions","vbegy");?></h2>
									<ul class="related-posts">
										<?php while ( $related_query->have_posts() ) : $related_query->the_post()?>
											<li class="related-item"><h3><a  href="<?php the_permalink();?>" title="<?php printf('%s', the_title_attribute('echo=0')); ?>"><i class="icon-double-angle-right"></i><?php the_title();?></a></h3></li>
										<?php endwhile;?>
									</ul>
								</div><!-- End related-posts -->
							<?php endif;
							$post = $orig_post;
							wp_reset_postdata();
						}
					}else if ($value_r == "comments") {
						$post_comments = vpanel_options("question_answers");
						if (($post_comments == 1 && $post_comments_s == "") || ($post_comments == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_comments == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_comments_s) && $post_comments_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_comments_s) && $post_comments_s == 1)) {
							comments_template("/question-comments.php");
						}
					}else if ($value_r == "next_previous") {
						$post_navigation = vpanel_options("question_navigation");
						if (($post_navigation == 1 && $post_navigation_s == "") || ($post_navigation == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_navigation == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_navigation_s) && $post_navigation_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_navigation_s) && $post_navigation_s == 1)) {
							$question_nav_category = vpanel_options("question_nav_category");?>
							<div class="post-next-prev clearfix">
							    <p class="prev-post">
							        <?php if ($question_nav_category == 1) {
							        	previous_post_link('%link','<i class="icon-double-angle-left"></i>'.__('&nbsp;Previous question','vbegy'),true,'',ask_question_category);
							        }else {
							        	previous_post_link('%link','<i class="icon-double-angle-left"></i>'.__('&nbsp;Previous question','vbegy'));
							        }?>
							    </p>
							    <p class="next-post">
							    	<?php if ($question_nav_category == 1) {
							    		next_post_link('%link',__('Next question&nbsp;','vbegy').'<i class="icon-double-angle-right"></i>',true,'',ask_question_category);
							    	}else {
							    		next_post_link('%link',__('Next question&nbsp;','vbegy').'<i class="icon-double-angle-right"></i>');
							    	}?>
							    </p>
							</div><!-- End post-next-prev -->
						<?php }
					}
				}
			}
		}?>
	</div>
<?php get_footer();?>