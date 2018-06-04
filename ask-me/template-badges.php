<?php /* Template Name: Badges & Points */
get_header();
	if ( have_posts() ) : while ( have_posts() ) : the_post();
		$badges = get_option("badges");
		if (isset($badges) && is_array($badges)) {?>
			<div class="page-content page-content-user-profile">
				<div class="user-profile-widget">
					<div class="boxedtitle page-title"><h2><?php _e("Badges","vbegy")?></h2></div>
					<div class="ul_list ul_list-icon-ok">
						<ul>
							<?php foreach ($badges as $badges_k => $badges_v) {?>
								<li style="background-color: <?php echo esc_html($badges_v["badge_color"])?>;color: #FFF;"><?php echo esc_html($badges_v["badge_name"])?><span> ( <span><?php echo esc_html($badges_v["badge_points"])?></span> ) <?php _e("Points","vbegy")?></span></li>
							<?php }?>
						</ul>
					</div>
				</div><!-- End user-profile-widget -->
			</div><!-- End page-content -->
		<?php }
		$active_points = vpanel_options("active_points");
		if ($active_points == 1) {
			$point_add_question = vpanel_options("point_add_question");
			$point_best_answer = vpanel_options("point_best_answer");
			$point_rating_question = vpanel_options("point_rating_question");
			$point_add_comment = vpanel_options("point_add_comment");
			$point_rating_answer = vpanel_options("point_rating_answer");
			$point_following_me = vpanel_options("point_following_me");
			$point_new_user = vpanel_options("point_new_user");
			$point_add_post = vpanel_options("point_add_post");?>
			<div class="page-content page-content-user-profile">
				<div class="user-profile-widget">
					<div class="boxedtitle page-title"><h2><?php _e("Points","vbegy")?></h2></div>
					<div class="ul_list ul_list-icon-ok">
						<ul>
							<?php if (isset($point_add_question) && $point_add_question > 0) {?>
								<li><?php _e("Add a new question","vbegy")?><span> ( <span><?php echo ($point_add_question)?></span> ) </span></li>
							<?php }
							if (isset($point_best_answer) && $point_best_answer > 0) {?>
								<li><?php _e("Points choose best answer","vbegy")?><span> ( <span><?php echo ($point_best_answer)?></span> ) </span></li>
							<?php }
							if (isset($point_rating_question) && $point_rating_question > 0) {?>
								<li><?php _e("Points rating question","vbegy")?><span> ( <span><?php echo ($point_rating_question)?></span> ) </span></li>
							<?php }
							if (isset($point_add_comment) && $point_add_comment > 0) {?>
								<li><?php _e("Points add answer","vbegy")?><span> ( <span><?php echo ($point_add_comment)?></span> ) </span></li>
							<?php }
							if (isset($point_rating_answer) && $point_rating_answer > 0) {?>
								<li><?php _e("Points rating answer","vbegy")?><span> ( <span><?php echo ($point_rating_answer)?></span> ) </span></li>
							<?php }
							if (isset($point_following_me) && $point_following_me > 0) {?>
								<li><?php _e("Points following user","vbegy")?><span> ( <span><?php echo ($point_following_me)?></span> ) </span></li>
							<?php }
							if (isset($point_new_user) && $point_new_user > 0) {?>
								<li><?php _e("Points for a new user","vbegy")?><span> ( <span><?php echo ($point_new_user)?></span> ) </span></li>
							<?php }
							if (isset($point_add_post) && $point_add_post > 0) {?>
								<li><?php _e("Add a new post","vbegy")?><span> ( <span><?php echo ($point_add_post)?></span> ) </span></li>
							<?php }?>
						</ul>
					</div>
				</div><!-- End user-profile-widget -->
			</div><!-- End page-content -->
		<?php }
	endwhile; endif;
get_footer();?>