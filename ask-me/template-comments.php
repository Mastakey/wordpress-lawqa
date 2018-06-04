<?php /* Template Name: Comments  */
get_header();
	$comment_type      = rwmb_meta('vbegy_comment_type','radio',$post->ID);
	$orderby_answers_a = rwmb_meta('vbegy_orderby_answers_a','radio',$post->ID);
	$order_answers     = rwmb_meta('vbegy_order_answers','radio',$post->ID);
	$answers_number    = rwmb_meta('vbegy_answers_number','text',$post->ID);
	$rows_per_page     = ($answers_number != "" && $answers_number > 0?$answers_number:get_option("posts_per_page"));
	$post_type         = ($comment_type == "answers"?"question":"post");
	$paged             = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
	$offset		       = ($paged-1)*$rows_per_page;
	if ($orderby_answers_a == 'votes' && $post_type == 'question') {
		$comments	    = get_comments(array('order' => (isset($order_answers)?$order_answers:'DESC'),'orderby' => 'meta_value_num','meta_key' => 'comment_vote',"post_type" => $post_type,"status" => "approve","meta_query" => array(array("key" => "answer_question_user","compare" => "NOT EXISTS"))));
		$query		    = get_comments(array('order' => (isset($order_answers)?$order_answers:'DESC'),'orderby' => 'meta_value_num','meta_key' => 'comment_vote',"offset" => $offset,"post_type" => $post_type,"status" => "approve","number" => $rows_per_page,"meta_query" => array(array("key" => "answer_question_user","compare" => "NOT EXISTS"))));
	}else if ($orderby_answers_a == 'oldest') {
		$comments	    = get_comments(array('order' => 'ASC','orderby' => 'comment_date',"post_type" => $post_type,"status" => "approve","meta_query" => array(array("key" => "answer_question_user","compare" => "NOT EXISTS"))));
		$query		    = get_comments(array('order' => 'ASC','orderby' => 'comment_date',"offset" => $offset,"post_type" => $post_type,"status" => "approve","number" => $rows_per_page,"meta_query" => array(array("key" => "answer_question_user","compare" => "NOT EXISTS"))));
	}else {
		$comments	    = get_comments(array('order' => (isset($order_answers) && $orderby_answers_a == 'date'?$order_answers:'DESC'),'orderby' => 'comment_date',"post_type" => $post_type,"status" => "approve","meta_query" => array(array("key" => "answer_question_user","compare" => "NOT EXISTS"))));
		$query		    = get_comments(array('order' => (isset($order_answers) && $orderby_answers_a == 'date'?$order_answers:'DESC'),'orderby' => 'comment_date',"offset" => $offset,"post_type" => $post_type,"status" => "approve","number" => $rows_per_page,"meta_query" => array(array("key" => "answer_question_user","compare" => "NOT EXISTS"))));
	}
	
	$total_comments = count($comments);
	$total_query    = count($query);
	$total_pages    = (int)ceil($total_comments/$rows_per_page);
	if ($query) {?>
		<div id="commentlist" class="page-content">
			<ol class="commentlist clearfix">
				<?php $k = 0;
				foreach ($query as $comment) {
					$k++;
					$comment_vote = get_comment_meta($comment->comment_ID,'comment_vote',true);
					$comment_vote = (!empty($comment_vote)?$comment_vote:0);
					if ($comment->user_id != 0){
						$user_login_id_l = get_user_by("id",$comment->user_id);
					}
					$yes_private = ask_private($comment->comment_post_ID,get_post($comment->comment_post_ID)->post_author,get_current_user_id());
					if ($yes_private == 1) {
						$answer_type = ($post_type == "post"?"comment":"answer");
						include("includes/answers.php");
					}else {?>
						<li class="comment"><div class="comment-body clearfix"><?php _e("Sorry it a private answer.","vbegy");?></div></li>
					<?php }
				}?>
			</ol>
		</div>
		<?php if ($total_comments > $total_query) {
			echo '<div class="pagination">';
			$current_page = max(1,$paged);
			echo paginate_links(array(
				'base' => @esc_url(add_query_arg('page','%#%')),
				'format' => 'page/%#%/',
				'current' => $current_page,
				'show_all' => false,
				'total' => $total_pages,
				'prev_text' => '<i class="icon-angle-left"></i>',
				'next_text' => '<i class="icon-angle-right"></i>',
			));
			echo '</div><div class="clearfix"></div>';
		}
	}else {
		echo "<div class='page-content page-content-user'><p class='no-item'>".__("No Answers Found.","vbegy")."</p></div>";
	}
get_footer();?>