<?php /* Template name: User Polls */
global $user_ID;
if (!is_user_logged_in && empty($_GET['u'])) {
	wp_redirect(home_url());
}
$get_u = (int)(is_user_logged_in && empty($_GET['u'])?$user_ID:$_GET['u']);
$user_login = get_userdata($get_u);
if (empty($user_login)) {
	wp_redirect(home_url());
}
$owner = false;
if ($user_ID == $user_login->ID) {
	$owner = true;
}
get_header();
	include (get_template_directory() . '/includes/author-head.php');?>
	<div class="page-content page-content-user">
		<div class="user-questions">
			<?php
			$user_get_current_user_id = get_current_user_id();
			$rows_per_page  = get_option("posts_per_page");
			$paged = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
			$args = array('post_type' => 'question','posts_per_page' => $rows_per_page,'paged' => $paged,'author' => $user_login->ID,"meta_query" => array('relation' => 'AND',array("key" => "question_poll","compare" => "=","value" => 1),array("key" => "user_id","compare" => "NOT EXISTS")));
			query_posts($args);
			if (have_posts()) : while ( have_posts() ) : the_post();
				$question_poll = get_post_meta($post->ID,'question_poll',true);
				$the_best_answer = get_post_meta($post->ID,"the_best_answer",true);
				$closed_question = get_post_meta($post->ID,"closed_question",true);
				$question_favorites = get_post_meta($post->ID,'question_favorites',true);
				$yes_private = ask_private($post->ID,$post->post_author,$user_get_current_user_id);
				$question_category = wp_get_post_terms($post->ID,ask_question_category,array("fields" => "all"));
				$comments = get_comments('post_id='.$post->ID);
				if ($yes_private == 1) {?>
					<article <?php post_class('question user-question');?> id="post-<?php echo $post->ID;?>">
						<h3>
							<?php $question_edit = vpanel_options("question_edit");
							if ($user_ID == $user_login->ID) {
								if ($question_edit == 1) {?>
									<span class="question-edit">
										<a href="<?php echo esc_url(add_query_arg("q", $post->ID,get_page_link(vpanel_options('edit_question'))))?>" original-title="<?php _e("Edit the question","vbegy")?>" class="tooltip-n"><i class="icon-edit"></i></a>
									</span>
								<?php }
								$question_delete = vpanel_options("question_delete");
								if ($question_delete == 1) {
									$protocol = is_ssl() ? 'https' : 'http';
									$redirect_to = wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
									if ( is_ssl() && force_ssl_admin() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )$secure_cookie = false; else $secure_cookie = '';
									$redirect_to = str_replace(site_url("/"),"",$redirect_to);?>
									<span class="question-delete">
										<a href="<?php echo esc_url(add_query_arg(array("delete" => $post->ID,"page" => esc_attr($redirect_to)),get_permalink($post->ID)))?>" original-title="<?php _e("Delete the question","vbegy")?>" class="tooltip-n"><i class="icon-remove"></i></a>
									</span>
								<?php }
							}?>
							<a href="<?php the_permalink();?>" title="<?php printf('%s', the_title_attribute('echo=0')); ?>" rel="bookmark"><?php the_title()?></a>
						</h3>
						<?php if ($question_poll == 1) {?>
							<div class="question-type-main"><i class="icon-signal"></i><?php _e("Poll","vbegy")?></div>
						<?php }else {?>
							<div class="question-type-main"><i class="icon-question-sign"></i><?php _e("Question","vbegy")?></div>
						<?php }?>
						<div class="question-content">
							<div class="question-bottom">
								<?php if (isset($the_best_answer) && $the_best_answer != "" && $comments) {?>
									<span class="question-answered question-answered-done"><i class="icon-ok"></i><?php _e("solved","vbegy")?></span>
								<?php }else if (isset($closed_question) && $closed_question == 1) {?>
									<span class="question-answered question-closed"><i class="icon-lock"></i><?php _e("closed","vbegy")?></span>
								<?php }else if ($the_best_answer == "" && $comments) {?>
									<span class="question-answered"><i class="icon-ok"></i><?php _e("in progress","vbegy")?></span>
								<?php }?>
								<span class="question-favorite"><i class="<?php echo ($question_favorites > 0?"icon-star":"icon-star-empty");?>"></i><?php echo ($question_favorites != ""?$question_favorites:0);?></span>
								<?php echo get_the_term_list($post->ID,ask_question_category,'<span class="question-category"><i class="fa fa-folder-o"></i>',', ','</span>');?>
								<span class="question-date"><i class="fa fa-calendar"></i><?php echo human_time_diff(get_the_time('U'), current_time('timestamp'));?></span>
								<span class="question-comment"><a href="<?php echo comments_link()?>"><i class="fa fa-comments-o"></i><?php echo get_comments_number()?> <?php _e("Answer","vbegy");?></a></span>
								<a class="question-reply" href="<?php the_permalink();?>#commentform"><i class="icon-reply"></i><?php _e("Reply","vbegy")?></a>
								<span class="question-view"><i class="icon-eye-open"></i><?php $post_stats = get_post_meta($post->ID, 'post_stats', true);echo ($post_stats != ""?$post_stats:0);?> <?php _e("views","vbegy");?></span>
							</div>
						</div>
					</article>
				<?php }else {?>
					<article class="question private-question user-question">
						<p class="question-desc"><?php _e("Sorry it a private question.","vbegy");?></p>
					</article>
				<?php }
			endwhile;else:echo "<p class='no-item'>".__("No questions yet .","vbegy")."</p>";endif;?>
		</div>
	</div>
	
	<?php if ($wp_query->max_num_pages > 1 ) :
		vpanel_pagination(array("base" => @esc_url(add_query_arg('page','%#%')),"format" => 'page/%#%/?u='.$get_u));
	endif;
	wp_reset_query();
get_footer();?>