<?php if (!empty($comments_all)) {
	$k = 0;
	$pagination_args = array(
		'base'      => (add_query_arg('paged','%#%')),
		'total'     => ceil(sizeof($comments_all)/$post_number),
		'current'   => $current,
		'show_all'  => false,
		'prev_text' => '<i class="fa fa-angle-left"></i>',
		'next_text' => '<i class="fa fa-angle-right"></i>',
	);
	
	$start = ($current - 1) * $post_number;
	$end = $start + $post_number;
	?>
	<div class="page-content commentslist">
		<ol class="commentlist clearfix">
			<?php $end = (sizeof($comments_all) < $end) ? sizeof($comments_all) : $end;
			for ($k_loop = $start;$k_loop < $end ;++$k_loop ) {$k++;
				$comment = $comments_all[$k_loop];
				$yes_private = ask_private($comment->comment_post_ID,get_post($comment->comment_post_ID)->post_author,get_current_user_id());
				if ($yes_private == 1) {
						$comment_id = esc_attr($comment->comment_ID);
						if ($search_type == "answers") {
							$answer_type = "answer";
						}
						$comment_vote = get_comment_meta($comment->comment_ID,'comment_vote',true);
						$comment_vote = (!empty($comment_vote)?$comment_vote:0);
						if ($comment->user_id != 0){
							$user_login_id_l = get_user_by("id",$comment->user_id);
						}
						include( get_template_directory() . '/includes/answers.php' );?>
					</li>
				<?php }else {?>
					<li class="comment">
						<div class="comment-body clearfix">
							<?php echo '<div class="alert-message warning"><i class="icon-flag"></i><p>'.esc_html__("Sorry it a private answer.","vbegy").'</p></div>';?>
						</div>
					</li>
				<?php }
			}?>
		</ol>
	</div>
<?php }else {
	include( get_template_directory() . '/includes/search-none.php' );
}
if ($comments_all && $pagination_args["total"] > 1) {?>
	<div class="main-pagination"><div class='pagination'><?php echo (paginate_links($pagination_args) != ""?paginate_links($pagination_args):"")?></div></div>
<?php }