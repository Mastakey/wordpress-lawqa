<?php $comment_id = esc_attr($comment->comment_ID);
$user_get_current_user_id = get_current_user_id();
$yes_private_answer = ask_private_answer($comment->comment_ID,$comment->user_id,get_current_user_id());
$can_delete_comment = vpanel_options("can_delete_comment");
$can_edit_comment = vpanel_options("can_edit_comment");
$can_edit_comment_after = vpanel_options("can_edit_comment_after");
$can_edit_comment_after = (int)(isset($can_edit_comment_after) && $can_edit_comment_after > 0?$can_edit_comment_after:0);
if (version_compare(phpversion(), '5.3.0', '>')) {
	$time_now = strtotime(current_time( 'mysql' ),date_create_from_format('Y-m-d H:i',current_time( 'mysql' )));
}else {
	list($year, $month, $day, $hour, $minute, $second) = sscanf(current_time( 'mysql' ), '%04d-%02d-%02d %02d:%02d:%02d');
	$datetime = new DateTime("$year-$month-$day $hour:$minute:$second");
	$time_now = strtotime($datetime->format('r'));
}
$time_edit_comment = strtotime('+'.$can_edit_comment_after.' hour',strtotime($comment->comment_date));
$time_end = ($time_now-$time_edit_comment)/60/60;
$edit_comment = get_comment_meta($comment_id,"edit_comment",true);
$the_best_answer = get_post_meta($comment->comment_post_ID,"the_best_answer",true);
$best_answer_comment = get_comment_meta($comment_id,"best_answer_comment",true);
if (isset($k) && $k == vpanel_options("between_comments_position")) {
	$between_adv_type = vpanel_options("between_comments_adv_type");
	$between_adv_code = vpanel_options("between_comments_adv_code");
	$between_adv_href = vpanel_options("between_comments_adv_href");
	$between_adv_img = vpanel_options("between_comments_adv_img");
	if (($between_adv_type == "display_code" && $between_adv_code != "") || ($between_adv_type == "custom_image" && $between_adv_img != "")) {
		echo '<li class="advertising">
			<div class="clearfix"></div>';
		if ($between_adv_type == "display_code") {
			echo stripcslashes(do_shortcode($between_adv_code));
		}else {
			if ($between_adv_href != "") {
				echo '<a target="_blank" href="'.$between_adv_href.'">';
			}
			echo '<img alt="" src="'.$between_adv_img.'">';
			if ($between_adv_href != "") {
				echo '</a>';
			}
		}
		echo '<div class="clearfix"></div>
		</li><!-- End advertising -->';
	}
}

if ($yes_private_answer != 1) {?>
	<li class="comment byuser comment">
		<div class="comment-body clearfix" rel="post-<?php echo $post->ID?>">
			<div class="comment-text">
				<div class="text">
					<p><?php _e("Sorry it a private answer.","vbegy");?></p>
				</div>
			</div>
		</div>
	</li>
<?php }else {?>
	<li rel="posts-<?php echo $comment->comment_post_ID?>" class="comment" id="comment-<?php echo $comment_id?>">
		<div<?php echo ($best_answer_comment == "best_answer_comment" || $the_best_answer == $comment_id?" itemprop='acceptedAnswer'":"")?> class="comment-body clearfix" rel="post-<?php echo $comment->comment_post_ID?>" itemscope itemtype="http://schema.org/Answer">
			<h3><a href="<?php echo get_permalink($comment->comment_post_ID);?>#comment-<?php echo $comment_id; ?>"><?php echo get_the_title($comment->comment_post_ID)?></a></h3>
			<div class="avatar-img">
				<?php $vpanel_get_user_url = vpanel_get_user_url($comment->user_id,get_the_author_meta('nickname', $comment->user_id));
				if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
					<a original-title="<?php echo strip_tags($comment->comment_author);?>" class="tooltip-n" href="<?php echo esc_url($vpanel_get_user_url)?>">
				<?php }
				echo askme_user_avatar(get_the_author_meta('you_avatar', $comment->user_id),65,65,$comment->user_id,$comment->comment_author,$comment->comment_author_email);
				if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
					</a>
				<?php }?>
			</div>
			<div class="comment-text">
			    <div class="author clearfix">
			    	<div class="comment-author" itemprop="author" itemscope itemtype="http://schema.org/Person">
			    		<?php if ($comment->user_id > 0){?>
	    		    		<a href="<?php echo vpanel_get_user_url($user_login_id_l->ID);?>">
	    		    	<?php }
		    		    	echo '<span itemprop="name">'.get_comment_author().'</span>';
	    		    	if ($comment->user_id > 0){?>
	    		    		</a>
	    		    		<?php $verified_user = get_the_author_meta('verified_user',$user_login_id_l->ID);
	    		    		if ($verified_user == 1) {
	    		    			echo '<img class="verified_user tooltip-n" alt="'.__("Verified","vbegy").'" original-title="'.__("Verified","vbegy").'" src="'.get_template_directory_uri().'/images/verified.png">';
	    		    		}
	    		    		echo vpanel_get_badge($user_login_id_l->ID);
	    		    	}?>
			    	</div>
			    	<?php $active_vote = vpanel_options("active_vote");
			    	$show_dislike_answers = vpanel_options("show_dislike_answers");
			    	if ($active_vote == 1 && isset($answer_type) && $answer_type == "answer") {?>
				    	<div class="comment-vote">
				        	<ul class="single-question-vote">
				        		<?php if (is_user_logged_in && $comment->user_id != get_current_user_id()){?>
				        			<li class="loader_3"></li>
			        				<li><a href="#" class="single-question-vote-up ask_vote_up comment_vote_up vote_allow<?php echo (isset($_COOKIE['comment_vote'.$comment_id])?" ".$_COOKIE['comment_vote'.$comment_id]."-".$comment_id:"")?>" title="<?php _e("Like","vbegy");?>" id="comment_vote_up-<?php echo $comment_id?>"><i class="icon-thumbs-up"></i></a></li>
				        			<?php if ($show_dislike_answers != 1) {?>
				        				<li><a href="#" class="single-question-vote-down ask_vote_down comment_vote_down vote_allow<?php echo (isset($_COOKIE['comment_vote'.$comment_id])?" ".$_COOKIE['comment_vote'.$comment_id]."-".$comment_id:"")?>" id="comment_vote_down-<?php echo $comment_id?>" title="<?php _e("Dislike","vbegy");?>"><i class="icon-thumbs-down"></i></a></li>
				        			<?php }
				        		}else { ?>
				        			<li class="loader_3"></li>
			        				<li><a href="#" class="single-question-vote-up ask_vote_up comment_vote_up <?php echo (is_user_logged_in && $comment->user_id == get_current_user_id()?"vote_not_allow":"vote_not_user")?>" title="<?php _e("Like","vbegy");?>"><i class="icon-thumbs-up"></i></a></li>
				        			<?php if ($show_dislike_answers != 1) {?>
				        				<li><a href="#" class="single-question-vote-down ask_vote_down comment_vote_down <?php echo (is_user_logged_in && $comment->user_id == get_current_user_id()?"vote_not_allow":"vote_not_user")?>" title="<?php _e("Dislike","vbegy");?>"><i class="icon-thumbs-down"></i></a></li>
				        			<?php }
				        		}?>
				        	</ul>
				    	</div>
				    	<span itemprop="upvoteCount" class="question-vote-result question_vote_result<?php echo ($comment_vote < 0?" question_vote_red":"")?>"><?php echo ($comment_vote != ""?$comment_vote:0)?></span>
			    	<?php }?>
			    	<div class="comment-meta" itemprop="dateCreated" datetime="<?php echo get_comment_date()?>">
			            <a href="<?php echo get_permalink($comment->comment_post_ID);?>#comment-<?php echo esc_attr($comment->comment_ID); ?>" class="date"><i class="fa fa-calendar"></i><?php printf(__( __('%1$s at %2$s','vbegy'), 'vbegy' ),get_comment_date(), get_comment_time()) ?></a> 
			        </div>
			        <div class="comment-reply">
			            <?php if (current_user_can('edit_comment',$comment_id) || ($can_edit_comment == 1 && $comment->user_id == get_current_user_id() && $comment->user_id != 0 && get_current_user_id() != 0 && ($can_edit_comment_after == 0 || $time_end <= $can_edit_comment_after))) {
			            	echo "<a class='comment-edit-link edit-comment' href='".esc_url(add_query_arg("comment_id", $comment_id,get_page_link(vpanel_options('edit_comment'))))."'><i class='icon-pencil'></i>".esc_html__("Edit","vbegy")."</a>";
			            }
			            if (($can_delete_comment == 1 && $comment->user_id == get_current_user_id() && $comment->user_id > 0 && get_current_user_id() > 0) || current_user_can('edit_comment',$comment_id) || is_super_admin(get_current_user_id())) {
			            	echo "<a class='comment-delete-link delete-comment".(isset($answer_type) && $answer_type == "answer"?' delete-answer':'')."' href='".esc_url(add_query_arg(array('delete_comment' => $comment_id),get_permalink($comment->comment_post_ID)))."'><i class='icon-trash'></i>".__("Delete","vbegy")."</a>";
			            }
			            $active_reports = vpanel_options("active_reports");
			            $active_logged_reports = vpanel_options("active_logged_reports");
			            if (isset($answer_type) && $answer_type == "answer" && $active_reports == 1 && (is_user_logged_in || (!is_user_logged_in && $active_logged_reports != 1))) {?>
			            	<a class="question_r_l comment_l report_c" href="#"><i class="icon-flag"></i><?php _e("Report","vbegy")?></a>
			            <?php }?>
			        </div>
			    </div>
			    <div class="text">
			    	<?php if (isset($answer_type) && $answer_type == "answer" && $active_reports == 1 && (is_user_logged_in || (!is_user_logged_in && $active_logged_reports != 1))) {?>
				    	<div class="explain-reported">
				    		<h3><?php _e("Please briefly explain why you feel this answer should be reported .","vbegy")?></h3>
				    		<textarea name="explain-reported"></textarea>
				    		<div class="clearfix"></div>
				    		<div class="loader_3"></div>
				    		<a class="color button small report"><?php _e("Report","vbegy")?></a>
				    		<a class="color button small dark_button cancel"><?php _e("Cancel","vbegy")?></a>
				    	</div><!-- End reported -->
			    	<?php }
			    	$featured_image_in_answers = vpanel_options("featured_image_in_answers");
			    	if ($featured_image_in_answers == 1) {
			    		$featured_image = get_comment_meta($comment_id,'featured_image',true);
			    		if (wp_get_attachment_image_srcset($featured_image)) {
			    			$img_url = wp_get_attachment_url($featured_image,"full");
			    			$featured_image_answers_lightbox = vpanel_options("featured_image_answers_lightbox");
			    			$featured_image_answer_width = vpanel_options("featured_image_answer_width");
			    			$featured_image_answer_height = vpanel_options("featured_image_answer_height");
			    			$featured_image_answer_width = ($featured_image_answer_width != ""?$featured_image_answer_width:260);
			    			$featured_image_answer_height = ($featured_image_answer_height != ""?$featured_image_answer_height:185);
			    			$link_url = ($featured_image_answers_lightbox == 1?$img_url:get_permalink($comment->comment_post_ID)."#comment-".$comment_id);
			    			$featured_answer_position = vpanel_options("featured_answer_position");
			    			if ($featured_answer_position != "after") {
			    				echo "<div class='featured_image_answer'><a href='".$link_url."'>".askme_resize_img($featured_image_answer_width,$featured_image_answer_height,"",$featured_image)."</a></div>
			    				<div class='clearfix'></div>";
			    			}
			    		}
			    	}?>
			    	<div itemprop="text"><a href="<?php echo get_permalink($comment->comment_post_ID);?>#comment-<?php echo $comment_id; ?>"><?php echo wp_html_excerpt($comment->comment_content,60)?></a></div>
			    	<?php if ($featured_image_in_answers == 1 && wp_get_attachment_image_srcset($featured_image) && $featured_answer_position == "after") {
			    		echo "<div class='featured_image_question featured_image_after'><a href='".$link_url."'>".askme_resize_img($featured_image_answer_width,$featured_image_answer_height,"",$featured_image)."</a></div>
			    		<div class='clearfix'></div>";
			    	}?>
			    </div>
			    <div class="clearfix"></div>
				<div class="loader_3"></div>
			    <div class="no_vote_more"></div>
			</div>
		</div>
	</li>
<?php }?>