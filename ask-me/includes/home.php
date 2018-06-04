<?php $content_before_tabs   = rwmb_meta('vbegy_content_before_tabs','type=wysiwyg',$the_page_id);
$content_after_tabs    = rwmb_meta('vbegy_content_after_tabs','type=wysiwyg',$the_page_id);
$posts_per_page        = rwmb_meta('vbegy_posts_per_page','text',$the_page_id);
$vbegy_index_tabs      = rwmb_meta('vbegy_index_tabs','checkbox',$the_page_id);
$vbegy_pagination_tabs = rwmb_meta('vbegy_pagination_tabs','checkbox',$the_page_id);
$vbegy_what_tab        = rwmb_meta('vbegy_what_tab','type=checkbox_list',$the_page_id);
$sort_home_elements    = get_post_meta($the_page_id,'vbegy_sort_home_elements',true);
$vbegy_categories_show = rwmb_meta('vbegy_categories_show','type=questions_categories',$the_page_id);
$posts_meta            = vpanel_options("post_meta");
$posts_per_page        = ($posts_per_page != "")?$posts_per_page:get_option("posts_per_page");
$paged                 = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
$sticky_questions      = get_option('sticky_questions');

if ($vbegy_index_tabs == 1) {
	if ($content_before_tabs != "") {
		echo "<div class='clearfix'></div>".$content_before_tabs."<div class='clearfix'></div>";
	}?>
	<div class="tabs-warp question-tab">
		<?php do_action("askme_bofore_tabs");
		$pagination_tabs = array();
		if ($vbegy_pagination_tabs == 1) {
			$pagination_tabs = array("paged" => $paged);
		}
		
		if (empty($sort_home_elements) || (isset($sort_home_elements) && isset($sort_home_elements["value"]) && $sort_home_elements["value"] != "recent_questions" && $sort_home_elements["value"] != "most_responses" && $sort_home_elements["value"] != "recently_answered" && $sort_home_elements["value"] != "no_answers" && $sort_home_elements["value"] != "most_visit" && $sort_home_elements["value"] != "most_vote" && $sort_home_elements["value"] != "question_bump" && $sort_home_elements["value"] != "recent_posts")) {
			$sort_home_elements = array(array("value" => "recent_questions","name" => "Recent Questions"),array("value" => "most_responses","name" => "Most Responses / answers"),array("value" => "recently_answered","name" => "Recently Answered"),array("value" => "no_answers","name" => "No answers"),array("value" => "most_visit","name" => "Most Visit"),array("value" => "most_vote","name" => "Most Vote"),array("value" => "question_bump","name" => "Questions bump"),array("value" => "recent_posts","name" => "Recent Posts"));
		}
		
		$i_count = -1;
		while ($i_count < count($sort_home_elements)) {
			$home_array_values = array_values($sort_home_elements);
			$home_array_keys = array_keys($sort_home_elements);
			if ((isset($home_array_values[$i_count]["value"]) && isset($vbegy_what_tab) && is_array($vbegy_what_tab) && in_array($home_array_values[$i_count]["value"],$vbegy_what_tab)) || (isset($home_array_values[$i_count]["cat"]) && $home_array_values[$i_count]["cat"] != "")) {
				$get_i = $i_count;
				if (isset($home_array_values[$i_count]["cat"]) && $home_array_values[$i_count]["cat"] != "") {
					$first_one = $sort_home_elements[$home_array_keys[$i_count]]["cat"];
					if ($first_one == 0) {
						$first_one = "all";
					}
					$get_i = "none";
				}
				break;
			}
			$i_count++;
		}
		
		if (isset($get_i) && $get_i !== "none") {
			$first_one = $sort_home_elements[$home_array_keys[$i_count]]["value"];
		}
		
		if (isset($_GET["tabs"]) && $_GET["tabs"] != "") {
			$first_one = $_GET["tabs"];
		}
		
		$value_last = esc_html($first_one);
		
		if (isset($sort_home_elements) && is_array($sort_home_elements)) {?>
	    	<ul class="tabs not-tabs">
		    	<?php foreach ($sort_home_elements as $key_r => $value_r) {
	    			$value_tab = (isset($value_r["value"])?$value_r["value"]:"");
	    			if (isset($value_r["cat"]) && $value_r["cat"] > 0) {
	    				$term_cat = get_term_by('id',$value_r["cat"],ask_question_category);
	    			}
					if ($value_tab == "recent_questions" && is_array($vbegy_what_tab) && in_array("recent_questions",$vbegy_what_tab)) {?>
						<li class="tab"><a<?php echo ($value_last == "recent_questions"?" class='current'":"")?> href="<?php echo esc_url(add_query_arg(array("tabs" => "recent_questions"),get_the_permalink($the_page_id)))?>" data-js="recent_questions"><?php _e("Recent Questions","vbegy")?></a></li>
					<?php }
					if ($value_tab == "most_responses" && is_array($vbegy_what_tab) && in_array("most_responses",$vbegy_what_tab)) {?>
						<li class="tab"><a<?php echo ($value_last == "most_responses"?" class='current'":"")?> href="<?php echo esc_url(add_query_arg(array("tabs" => "most_responses"),get_the_permalink($the_page_id)))?>" data-js="most_responses"><?php _e("Most Responses","vbegy")?></a></li>
					<?php }
					if ($value_tab == "recently_answered" && is_array($vbegy_what_tab) && in_array("recently_answered",$vbegy_what_tab)) {?>
						<li class="tab"><a<?php echo ($value_last == "recently_answered"?" class='current'":"")?> href="<?php echo esc_url(add_query_arg(array("tabs" => "recently_answered"),get_the_permalink($the_page_id)))?>" data-js="recently_answered"><?php _e("Recently Answered","vbegy")?></a></li>
					<?php }
					if ($value_tab == "no_answers" && is_array($vbegy_what_tab) && in_array("no_answers",$vbegy_what_tab)) {?>
						<li class="tab"><a<?php echo ($value_last == "no_answers"?" class='current'":"")?> href="<?php echo esc_url(add_query_arg(array("tabs" => "no_answers"),get_the_permalink($the_page_id)))?>" data-js="no_answers"><?php _e("No answers","vbegy")?></a></li>
					<?php }
					if ($value_tab == "most_visit" && is_array($vbegy_what_tab) && in_array("most_visit",$vbegy_what_tab)) {?>
						<li class="tab"><a<?php echo ($value_last == "most_visit"?" class='current'":"")?> href="<?php echo esc_url(add_query_arg(array("tabs" => "most_visit"),get_the_permalink($the_page_id)))?>" data-js="most_visit"><?php _e("Most Visit","vbegy")?></a></li>
					<?php }
					if ($value_tab == "most_vote" && is_array($vbegy_what_tab) && in_array("most_vote",$vbegy_what_tab)) {?>
						<li class="tab"><a<?php echo ($value_last == "most_vote"?" class='current'":"")?> href="<?php echo esc_url(add_query_arg(array("tabs" => "most_vote"),get_the_permalink($the_page_id)))?>" data-js="most_vote"><?php _e("Most Vote","vbegy")?></a></li>
					<?php }
					if ($value_tab == "question_bump" && is_array($vbegy_what_tab) && in_array("question_bump",$vbegy_what_tab)) {?>
						<li class="tab"><a<?php echo ($value_last == "question_bump"?" class='current'":"")?> href="<?php echo esc_url(add_query_arg(array("tabs" => "question_bump"),get_the_permalink($the_page_id)))?>" data-js="question_bump"><?php _e("Questions Bump","vbegy")?></a></li>
					<?php }
					if ($value_tab == "recent_posts" && is_array($vbegy_what_tab) && in_array("recent_posts",$vbegy_what_tab)) {?>
						<li class="tab"><a<?php echo ($value_last == "recent_posts"?" class='current'":"")?> href="<?php echo esc_url(add_query_arg(array("tabs" => "recent_posts"),get_the_permalink($the_page_id)))?>" data-js="recent_posts"><?php _e("Recent Posts","vbegy")?></a></li>
					<?php }else if (isset($value_r["cat"]) && $value_r["cat"] != "") {
						$current_value = "";
						if ($value_r["cat"] == 0) {
							if ($value_last == "all") {
								$current_value = $value_r["cat"];
							}
						}else {
							if ((isset($term_cat->slug) && $value_last == $term_cat->slug) || ($value_r["cat"] == $value_last)) {
								$current_value = $value_r["cat"];
							}
						}?>
						<li class="tab"><a<?php echo (($current_value == $value_r["cat"]) || ($value_r["cat"] == $value_last)?" class='current'":"")?> href="<?php echo esc_url(add_query_arg(array("tabs" => ($value_r["cat"] == 0?"all":(isset($term_cat->slug)?$term_cat->slug:""))),get_the_permalink($the_page_id)))?>" data-js="question_bump"><?php echo ($value_r["cat"] == 0?__("All Categories","vbegy"):(isset($term_cat->name)?$term_cat->name:""))?></a></li>
					<?php }
				}?>
		    </ul>
	    
	    	<?php do_action("askme_after_tabs");
	    	
	    	foreach ($sort_home_elements as $key_r => $value_r) {
	    		$value_tab = (isset($value_r["value"])?$value_r["value"]:"");
	    		
	    		if (isset($value_r["cat"]) && $value_r["cat"] != "") {
	    			$current_value = "";
	    			if ($value_r["cat"] == 0) {
	    				if ($value_last == "all") {
	    					$current_value = $value_r["cat"];
	    				}
	    			}else {
	    				$term_cat = get_term_by('id',$value_r["cat"],ask_question_category);
	    				if ((isset($term_cat->slug) && $value_last == $term_cat->slug) || ($value_r["cat"] == $value_last)) {
	    					$current_value = $value_r["cat"];
	    				}
	    			}
	    		}
	    		
	    		if ($value_tab == "recent_questions" && $value_last == "recent_questions" && is_array($vbegy_what_tab) && in_array("recent_questions",$vbegy_what_tab)) {?>
				    <div class="tab-inner-warp">
						<div class="tab-inner">
							<?php $question_bump_template = $question_vote_template = false;
							$active_sticky = true;
							$k = 0;
							include(get_template_directory() ."/sticky-question.php");
							
							$is_questions_sticky = false;
							$args = array_merge($pagination_tabs,$post__not_in,array("post_type" => "question","posts_per_page" => $posts_per_page,"meta_query" => array($user_id_query)));
							query_posts($args);
							get_template_part("loop-question");
							if ($vbegy_pagination_tabs == 1) {
								vpanel_pagination();
							}
							wp_reset_query();?>
					    </div>
					</div>
				<?php }else if ($value_tab == "most_responses" && $value_last == "most_responses" && is_array($vbegy_what_tab) && in_array("most_responses",$vbegy_what_tab)) {?>
					<div class="tab-inner-warp">
						<div class="tab-inner">
							<?php $args = array_merge($pagination_tabs,array("post_type" => "question","posts_per_page" => $posts_per_page,'orderby' => 'comment_count','order' => "DESC","meta_query" => array(array("key" => "user_id","compare" => "NOT EXISTS"))));
							query_posts($args);
							$question_bump_template = $active_sticky = $question_vote_template = false;
							$k = 0;
							get_template_part("loop-question");
							if ($vbegy_pagination_tabs == 1) {
								vpanel_pagination();
							}
							wp_reset_query();?>
					    </div>
					</div>
				<?php }else if ($value_tab == "no_answers" && $value_last == "no_answers" && is_array($vbegy_what_tab) && in_array("no_answers",$vbegy_what_tab)) {?>
					<div class="tab-inner-warp">
						<div class="tab-inner">
							<?php
							$question_bump_template = $active_sticky = $question_vote_template = false;
							$k = 0;
							$args = array("paged" => $paged,"post_type" => "question","posts_per_page" => $posts_per_page,"orderby" => array("comment_count" => "DESC","date" => "DESC"),"meta_query" => array('user_question_order' => array("key" => "user_id","compare" => "NOT EXISTS")));
							add_filter('posts_where', 'ask_filter_where');
							query_posts($args);
							get_template_part("loop-question");
							vpanel_pagination();
							remove_filter( 'posts_where', 'ask_filter_where' );
							wp_reset_query();?>
					    </div>
					</div>
				<?php }else if ($value_tab == "recently_answered" && $value_last == "recently_answered" && is_array($vbegy_what_tab) && in_array("recently_answered",$vbegy_what_tab)) {?>
					<div class="tab-inner-warp">
						<div class="tab-inner">
							<?php $k = 0;
							$offset   = ($paged-1)*$posts_per_page;
							$comments	    = get_comments(array("post_type" => "question","status" => "approve","meta_query" => array(array("key" => "answer_question_user","compare" => "NOT EXISTS"))));
							$query		    = get_comments(array("offset" => $offset,"post_type" => "question","status" => "approve","number" => $posts_per_page,"meta_query" => array(array("key" => "answer_question_user","compare" => "NOT EXISTS"))));
							$total_comments = count($comments);
							$total_query    = count($query);
							$total_pages    = (int)ceil($total_comments/$posts_per_page);
							if ($query) {?>
								<div id="commentlist" class="page-content">
									<ol class="commentlist clearfix">
										<?php
										foreach ($query as $comment) {
											$k++;
											$comment_vote = get_comment_meta($comment->comment_ID,'comment_vote',true);
											$comment_vote = (!empty($comment_vote)?$comment_vote:0);
											if ($comment->user_id != 0){
												$user_login_id_l = get_user_by("id",$comment->user_id);
											}
											$yes_private = ask_private($comment->comment_post_ID,get_post($comment->comment_post_ID)->post_author,get_current_user_id());
											if ($yes_private == 1) {
												$answer_type = "answer";
												include(get_template_directory() ."/includes/answers.php");
											}
										}?>
									</ol>
								</div>
								<?php if ($total_comments > $total_query && $vbegy_pagination_tabs == 1) {
									echo '<div class="pagination">';
									$current_page = max(1,$paged);
									echo paginate_links(array(
										'base' => @esc_url(add_query_arg('page','%#%')),
										'format' => 'page/%#%/',
										'show_all' => false,
										'current' => $current_page,
										'total' => $total_pages,
										'prev_text' => '<i class="icon-angle-left"></i>',
										'next_text' => '<i class="icon-angle-right"></i>',
									));
									echo '</div><div class="clearfix"></div>';
								}
							}else {
								echo "<div class='page-content page-content-user'><p class='no-item'>".__("No answers Found.","vbegy")."</p></div>";
							}?>
					    </div>
					</div>
				<?php }else if ($value_tab == "most_visit" && $value_last == "most_visit" && is_array($vbegy_what_tab) && in_array("most_visit",$vbegy_what_tab)) {?>
					<div class="tab-inner-warp">
						<div class="tab-inner">
							<?php $args = array_merge($pagination_tabs,array("posts_per_page" => $posts_per_page,"post_type" => "question",'orderby' => array('post_stats_order' => "DESC"),"meta_query" => array('relation' => 'AND','user_question_order' => array("key" => "user_id","compare" => "NOT EXISTS"),'post_stats_order' => array('type' => 'numeric',"key" => "post_stats","value" => 0,"compare" => ">"))));
							query_posts($args);
							$question_bump_template = $active_sticky = $question_vote_template = false;
							$k = 0;
							get_template_part("loop-question");
							if ($vbegy_pagination_tabs == 1) {
								vpanel_pagination();
							}
							wp_reset_query();?>
					    </div>
					</div>
				<?php }else if ($value_tab == "most_vote" && $value_last == "most_vote" && is_array($vbegy_what_tab) && in_array("most_vote",$vbegy_what_tab)) {?>
					<div class="tab-inner-warp">
						<div class="tab-inner">
							<?php $args = array_merge($pagination_tabs,array("posts_per_page" => $posts_per_page,"post_type" => "question",'orderby' => array('question_vote_order' => "DESC"),"meta_query" => array('relation' => 'AND','user_question_order' => array("key" => "user_id","compare" => "NOT EXISTS"),'question_vote_order' => array('type' => 'numeric',"key" => "question_vote","value" => 0,"compare" => ">"))));
							query_posts($args);
							$question_bump_template = $active_sticky = false;
							$question_vote_template = true;
							$k = 0;
							get_template_part("loop-question");
							if ($vbegy_pagination_tabs == 1) {
								vpanel_pagination();
							}
							wp_reset_query();?>
					    </div>
					</div>
				<?php }else if ($value_tab == "question_bump" && $value_last == "question_bump" && is_array($vbegy_what_tab) && in_array("question_bump",$vbegy_what_tab)) {?>
					<div class="tab-inner-warp">
						<div class="tab-inner">
							<?php $question_bump = vpanel_options("question_bump");
							$active_points = vpanel_options("active_points");
							if ($active_points == 1 && $question_bump == 1) {
								$args = array("paged" => $paged,"post_type" => "question","posts_per_page" => $posts_per_page,'orderby' => array('question_points_order' => "DESC"),"meta_query" => array('relation' => 'AND','user_question_order' => array("key" => "user_id","compare" => "NOT EXISTS"),'question_points_order' => array('type' => 'numeric',"key" => "question_points","value" => 0,"compare" => ">=")));
								add_filter('posts_where', 'ask_filter_where');
								query_posts($args);
								$question_bump_template = true;
								$question_vote_template = $active_sticky = false;
								$k = 0;
								get_template_part("loop-question");
								if ($vbegy_pagination_tabs == 1) {
									vpanel_pagination();
								}
								remove_filter( 'posts_where', 'ask_filter_where' );
								wp_reset_query();
							}else {
								echo "<div class='page-content page-content-user'><p class='no-item'>".__("This page is not active .","vbegy")."</p></div>";
							}?>
					    </div>
					</div>
				<?php }else if ($value_tab == "recent_posts" && $value_last == "recent_posts" && is_array($vbegy_what_tab) && in_array("recent_posts",$vbegy_what_tab)) {?>
					<div class="tab-inner-warp">
						<div class="tab-inner">
							<?php $vbegy_sidebar_all = vpanel_sidebars("sidebar_where");
							$args = array_merge($pagination_tabs,array("post_type" => "post","posts_per_page" => $posts_per_page));
							$blog_style = vpanel_options("home_display");
							query_posts($args);
							$question_bump_template = $active_sticky = $question_vote_template = false;
							$k = 0;
							get_template_part("loop");
							if ($vbegy_pagination_tabs == 1) {
								vpanel_pagination();
							}
							wp_reset_query();?>
					    </div>
					</div>
				<?php }else if (isset($value_r["cat"]) && (($current_value == $value_r["cat"]) || ($value_r["cat"] == $value_last))) {?>
					<div class="tab-inner-warp">
						<div class="tab-inner">
							<?php $k = 0;
							$question_bump_template = $question_vote_template = false;
							$active_sticky = true;
							if (is_numeric($value_last) && $value_last == 0) {
								$value_last = "all";
							}else if (is_numeric($value_last) && $value_last > 0) {
								$term_cat = get_term_by('id',$value_last,ask_question_category);
								$value_last = $term_cat->slug;
							}
							
							if ($value_last == "all") {
								$cat_array = array();
							}else {
								$cat_array = $custom_args = array('tax_query' => array(array('taxonomy' => ask_question_category,'field' => 'slug','terms' => $value_last)));
							}
							include(get_template_directory() ."/sticky-question.php");
							
							$is_questions_sticky = false;
							$user_id_query = array("key" => "user_id","compare" => "NOT EXISTS");
							$args = array_merge($pagination_tabs,$cat_array,$post__not_in,array("post_type" => "question","posts_per_page" => $posts_per_page,"meta_query" => array($user_id_query)));
							query_posts($args);
							get_template_part("loop-question");
							if ($vbegy_pagination_tabs == 1) {
								vpanel_pagination();
							}
							wp_reset_query();?>
					    </div>
					</div>
				<?php }
			}
		}
		vpanel_pagination();?>
	</div><!-- End tabs-warp -->
	<?php if ($content_after_tabs != "") {
		echo "<div class='clearfix'></div>".$content_after_tabs."<div class='clearfix'></div>";
	}
}else if ($vbegy_index_tabs == 2) {
	$args = array("paged" => $paged,"post_type" => "question","posts_per_page" => $posts_per_page);
	query_posts($args);
	get_template_part("loop-question");
	vpanel_pagination();
	wp_reset_query();
}else {
	if ( have_posts() ) : while ( have_posts() ) : the_post();
		$date_format = (vpanel_options("date_format")?vpanel_options("date_format"):get_option("date_format"));
		$vbegy_what_post = rwmb_meta('vbegy_what_post','select',$post->ID);
		$vbegy_google = rwmb_meta('vbegy_google',"textarea",$post->ID);
		$video_id = rwmb_meta('vbegy_video_post_id',"select",$post->ID);
		$video_type = rwmb_meta('vbegy_video_post_type',"text",$post->ID);
		$vbegy_slideshow_type = rwmb_meta('vbegy_slideshow_type','select',$post->ID);
		if ($video_type == 'youtube') {
			$type = "https://www.youtube.com/embed/".$video_id;
		}else if ($video_type == 'vimeo') {
			$type = "https://player.vimeo.com/video/".$video_id;
		}else if ($video_type == 'daily') {
			$type = "https://www.dailymotion.com/embed/video/".$video_id;
		}
		$custom_page_setting = rwmb_meta('vbegy_custom_page_setting','checkbox',$post->ID);
		$post_meta_s = rwmb_meta('vbegy_post_meta_s','checkbox',$post->ID);
		$post_comments_s = rwmb_meta('vbegy_post_comments_s','checkbox',$post->ID);
		$vbegy_sidebar_all = vpanel_sidebars("sidebar_where");?>
		<article <?php post_class('post single-post');?> id="post-<?php echo $post->ID;?>">
			<div class="post-inner">
				<div class="post-img<?php if ($vbegy_what_post == "image" && !has_post_thumbnail()) {echo " post-img-0";}else if ($vbegy_what_post == "video") {echo " video_embed";}if ($vbegy_sidebar_all == "full") {echo " post-img-12";}else {echo " post-img-9";}?>">
					<?php include (get_template_directory() . '/includes/head.php');?>
				</div>
	        	<h2 class="post-title"><?php the_title()?></h2>
				<?php $posts_meta = vpanel_options("post_meta");
				if (($posts_meta == 1 && $post_meta_s == "") || ($posts_meta == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($posts_meta == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_meta_s) && $post_meta_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_meta_s) && $post_meta_s == 1)) {?>
					<div class="post-meta">
					    <span class="meta-author"><i class="icon-user"></i><?php the_author_posts_link();?></span>
					    <span class="meta-date"><i class="fa fa-calendar"></i><?php the_time($date_format);?></span>
					    <span class="meta-comment"><i class="fa fa-comments-o"></i><?php comments_popup_link(__('0 Comments', 'vbegy'), __('1 Comment', 'vbegy'), '% '.__('Comments', 'vbegy'));?></span>
					    <span class="post-view"><i class="icon-eye-open"></i><?php $post_stats = get_post_meta($post->ID, 'post_stats', true);echo ($post_stats != ""?$post_stats:0);?> <?php _e("views","vbegy");?></span>
					</div>
				<?php }?>
				<div class="post-content">
					<?php the_content();?>
					<div class="clearfix"></div>
				</div>
			</div><!-- End post-inner -->
		</article><!-- End article.post -->
		<?php $post_comments = vpanel_options("post_comments");
		if (($post_comments == 1 && $post_comments_s == "") || ($post_comments == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_comments == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_comments_s) && $post_comments_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_comments_s) && $post_comments_s == 1)) {
			comments_template();
		}
	endwhile; endif;
}