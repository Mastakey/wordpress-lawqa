<?php if (is_author()) {
	$user_login = get_queried_object();
	if (isset($user_login) && is_object($user_login)) {
		$user_login = get_userdata(esc_attr($user_login->ID));
	}
	if (isset($user_login) && !is_object($user_login)) {
		$user_login = get_user_by('login',urldecode(get_query_var('author_name')));
	}
	if (isset($user_login) && !is_object($user_login)) {
		$user_login = get_user_by('slug',urldecode(get_query_var('author_name')));
	}
}else {
	$user_login = get_userdata((int)(is_user_logged_in && empty($_GET['u'])?$user_ID:$_GET['u']));
}
if (isset($user_login) && is_object($user_login)) {
	$get_query_var = $user_login->ID;
	$you_avatar = get_the_author_meta('you_avatar',$get_query_var);
	$url = get_the_author_meta('url',$get_query_var);
	$country = get_the_author_meta('country',$get_query_var);
	$city = get_the_author_meta('city',$get_query_var);
	$phone = get_the_author_meta('phone',$get_query_var);
	$sex = get_the_author_meta('sex',$get_query_var);
	$age = get_the_author_meta('age',$get_query_var);
	$twitter = get_the_author_meta('twitter',$get_query_var);
	$facebook = get_the_author_meta('facebook',$get_query_var);
	$google = get_the_author_meta('google',$get_query_var);
	$linkedin = get_the_author_meta('linkedin',$get_query_var);
	$follow_email = get_the_author_meta('follow_email',$get_query_var);
	$youtube = get_the_author_meta('youtube',$get_query_var);
	$pinterest = get_the_author_meta('pinterest',$get_query_var);
	$instagram = get_the_author_meta('instagram',$get_query_var);
	$show_point_favorite = get_the_author_meta('show_point_favorite',$get_query_var);
	$verified_user = get_the_author_meta('verified_user',$get_query_var);
}else {
	wp_redirect(home_url());
	die();
}

$owner = false;
if ($user_ID == $get_query_var) {
	$owner = true;
}

/* visit */
$visit_profile = get_user_meta($get_query_var,"visit_profile_all",true);
$visit_profile_m = get_user_meta($get_query_var,"visit_profile_m_".date_i18n('m_Y',current_time('timestamp')),true);
$visit_profile_d = get_user_meta($get_query_var,"visit_profile_d_".date_i18n('d_m_Y',current_time('timestamp')),true);

if ($visit_profile_d == "" or $visit_profile_d == 0) {
	add_user_meta($get_query_var,"visit_profile_d_".date_i18n('d_m_Y',current_time('timestamp')),1);
}else {
	update_user_meta($get_query_var,"visit_profile_d_".date_i18n('d_m_Y',current_time('timestamp')),$visit_profile_d+1);
}

if ($visit_profile_m == "" or $visit_profile_m == 0) {
	add_user_meta($get_query_var,"visit_profile_m_".date_i18n('m_Y',current_time('timestamp')),1);
}else {
	update_user_meta($get_query_var,"visit_profile_m_".date_i18n('m_Y',current_time('timestamp')),$visit_profile_m+1);
}

if ($visit_profile == "" or $visit_profile == 0) {
	add_user_meta($get_query_var,"visit_profile_all",1);
}else {
	update_user_meta($get_query_var,"visit_profile_all",$visit_profile+1);
}

/* points */
$points = get_user_meta($get_query_var,"points",true);

/* favorites */
$_favorites = get_user_meta($get_query_var,$user_login->user_login."_favorites");

/* followed */
$following_questions = get_user_meta($get_query_var,"following_questions");

/* the_best_answer */
$the_best_answer = count(get_comments(array('user_id' => $get_query_var,"status" => "approve",'post_type' => 'question',"meta_query" => array('relation' => 'AND',array("key" => "best_answer_comment","compare" => "=","value" => "best_answer_comment"),array("key" => "answer_question_user","compare" => "NOT EXISTS")))));

/* following */
$following_me = get_user_meta($get_query_var,"following_me");
$following_you = get_user_meta($get_query_var,"following_you");

/* add_answer */
$add_answer = get_user_meta($get_query_var,"add_answer_all",true);
$add_answer_m = count(get_comments(array("post_type" => "question","status" => "approve","user_id" => $get_query_var,'date_query' => array(array('year'  => date( 'Y' ),'month' => date( 'm' ),)))));
$add_answer_d = count(get_comments(array("post_type" => "question","status" => "approve","user_id" => $get_query_var,'date_query' => array(array('year'  => date( 'Y' ),'month' => date( 'm' ),'day' => date( 'd' ))))));
$add_answer = count(get_comments(array("post_type" => "question","status" => "approve","user_id" => $get_query_var)));

/* add_questions */
$add_questions = count_user_posts_by_type($get_query_var,"question");
$add_questions_m = count_user_posts_by_type_date($get_query_var,"question","year","month");
$add_questions_d = count_user_posts_by_type_date($get_query_var,"question","year","month","day");

/* paid_questions */
$paid_questions = count_paid_question_by_type($get_query_var,"question");

/* asked_questions */
$asked_questions = count_asked_question_by_type($get_query_var,($owner == true?">=":">"));

/* add_comment */
$add_comment = count(get_comments(array("post_type" => "post","status" => "approve","user_id" => $get_query_var)));

/* follow questions - answers - posts - comments */
$follow_questions = 0;
$follow_answers = 0;
$follow_posts = 0;
$follow_comments = 0;
if (isset($following_me) && is_array($following_me) && isset($following_me[0]) && is_array($following_me[0])) {
	if (isset($following_me[0]) && is_array($following_me[0])) {
		$following_me_array = $following_me[0];
	}
}
if (isset($following_me_array) && is_array($following_me_array) && !empty($following_me_array)) {
	foreach ($following_me_array as $key => $value) {
		$follow_questions += count_user_posts_by_type($value,"question");
		$follow_posts += count_user_posts_by_type($value,"post");
		$follow_answers += count(get_comments(array("post_type" => "question","status" => "approve","user_id" => $value)));
		$follow_comments += count(get_comments(array("post_type" => "post","status" => "approve","user_id" => $value)));
	}
}
?>
<div class="row">
	<div class="user-profile">
		<div class="col-md-12">
			<div class="page-content">
				<h2>
					<?php _e("About","vbegy")?> <a href="<?php echo vpanel_get_user_url($get_query_var);?>"><?php echo $user_login->display_name?></a>
					<?php if ($verified_user == 1) {
						echo '<img class="verified_user tooltip-n" alt="'.__("Verified","vbegy").'" original-title="'.__("Verified","vbegy").'" src="'.get_template_directory_uri().'/images/verified.png">';
					}
					echo vpanel_get_badge($get_query_var)?>
				</h2>
				<div class="user-profile-img">
					<a original-title="<?php echo $user_login->display_name?>" class="tooltip-n" href="<?php echo vpanel_get_user_url($get_query_var);?>">
						<?php echo askme_user_avatar($you_avatar,79,79,$get_query_var,$user_login->display_name);?>
					</a>
				</div>
				<div class="ul_list ul_list-icon-ok about-user">
					<?php
					$user_registered = vpanel_options("user_registered");
					$user_country = vpanel_options("user_country");
					$user_city = vpanel_options("user_city");
					$user_phone = vpanel_options("user_phone");
					$user_age = vpanel_options("user_age");
					$user_sex = vpanel_options("user_sex");
					$user_url = vpanel_options("user_url");
					if ($user_registered != 1 || $user_country != 1 || $user_city != 1 || $user_phone != 1 || $user_age != 1 || $user_sex != 1 || $user_url != 1) {?>
						<ul>
							<?php if ($user_registered != 1) {
								$date_format = vpanel_options("date_format");
								$register_date = explode(" ",$user_login->user_registered);
								if (isset($register_date[0]) && isset($register_date[1])) {
									$register_date_1 = explode("-",$register_date[0]);
									$register_date_2 = explode(":",$register_date[1]);
								}?>
								<li><i class="icon-plus"></i><strong><?php _e("Registered","vbegy")?>: </strong><span><?php echo (isset($register_date_1[0]) && isset($register_date_1[1]) && isset($register_date_1[2]) && isset($register_date_2[0]) && isset($register_date_2[1]) && isset($register_date_2[2])?date($date_format,mktime($register_date_2[0], $register_date_2[1], $register_date_2[2], $register_date_1[1], $register_date_1[2], $register_date_1[0])):substr($user_login->user_registered, 0, 10));?></span></li>
							<?php }
							if ($phone && $user_phone != 1) {?>
								<li><i class="icon-phone"></i><strong><?php _e("Phone","vbegy")?>: </strong><span><?php echo $phone?></span></li>
							<?php }
							$get_countries = vpanel_get_countries();
							if ($country && $user_country != 1 && isset($get_countries[$country])) {?>
								<li><i class="icon-map-marker"></i><strong><?php _e("Country","vbegy")?>: </strong><span><?php echo $get_countries[$country]?></span></li>
							<?php }
							if ($city && $user_city != 1) {?>
								<li><i class="icon-map-marker"></i><strong><?php _e("City","vbegy")?>: </strong><span><?php echo $city?></span></li>
							<?php }
							if ($age && $user_age != 1) {?>
								<li><i class="icon-heart"></i><strong><?php _e("Age","vbegy")?>: </strong><span><?php echo $age?></span></li>
							<?php }
							if (isset($sex) && !empty($sex) && $user_sex != 1) {?>
								<li><i class="icon-user"></i><strong><?php _e("Sex","vbegy")?>: </strong><span><?php echo ($sex == "male" || $sex == 1?__("Male","vbegy"):__("Female","vbegy"))?></span></li>
							<?php }
							if ($url && $user_url != 1) {?>
								<li><i class="icon-globe"></i><strong><?php _e("Website","vbegy")?>: </strong><a target="_blank" href="<?php echo $url?>"><?php _e("view","vbegy")?></a></li>
							<?php }
							do_action("askme_author_page_li",$get_query_var);?>
						</ul>
					<?php }?>
				</div>
				<div class="clearfix"></div>
				<p><?php echo nl2br($user_login->description)?></p>
				<div class="clearfix"></div>
				<?php if ($owner == true) {
					$get_lang = esc_attr(get_query_var("lang"));
					$get_lang_array = array();
					if (isset($get_lang) && $get_lang != "") {
						$get_lang_array = array("lang" => $get_lang);
					}?>
					<a class="button color small margin_0" href="<?php echo esc_url(get_page_link(vpanel_options('user_edit_profile_page')))?>"><?php _e("Edit profile","vbegy")?></a>
				<?php }else {
					$ask_question_to_users = vpanel_options("ask_question_to_users");
					if ($ask_question_to_users == 1) {?>
						<form class="form_ask_user" method="get" action="<?php echo esc_url(get_page_link(vpanel_options('add_question')))?>">
							<button class="button color small"><?php echo esc_html__("Ask","vbegy")." ".get_the_author_meta("display_name",$get_query_var)?></button>
							<input type="hidden" name="user_id" value="<?php echo $get_query_var?>">
						</form>
					<?php }
					
					$active_message = vpanel_options("active_message");
					$send_message_no_register = vpanel_options("send_message_no_register");
					$received_message = esc_attr( get_the_author_meta( 'received_message', $get_query_var ) );
					$block_message = esc_attr( get_the_author_meta( 'block_message', get_current_user_id() ) );
					if ($active_message == 1) {
						$user_block_message = array();
						if (is_user_logged_in) {
							$user_block_message = get_user_meta($get_query_var,"user_block_message",true);
						}
						if (((!is_user_logged_in && $send_message_no_register == 1) || (is_user_logged_in && (empty($user_block_message) || (isset($user_block_message) && is_array($user_block_message) && !in_array(get_current_user_id(),$user_block_message))) && ($block_message != 1 || is_super_admin(get_current_user_id())) && ($received_message == "" || $received_message == 1)))) {?>
							<a href="#" class="button color small form_message"><?php echo esc_html__("Message","vbegy")?></a>
						<?php }
						if (is_user_logged_in && !is_super_admin($get_query_var)) {
							$user_block_message = get_user_meta(get_current_user_id(),"user_block_message",true);
							if (isset($user_block_message) && is_array($user_block_message) && in_array($get_query_var,$user_block_message)) {?>
								<a href="#" class="button color small block_message unblock_message" data-id="<?php echo (int)$get_query_var?>"><?php echo esc_html__("Unblock Message","vbegy")?></a>
							<?php }else {?>
								<a href="#" class="button color small block_message" data-id="<?php echo (int)$get_query_var?>"><?php echo esc_html__("Block Message","vbegy")?></a>
							<?php }
						}
					}
					
					if (is_user_logged_in) {
						$following_me2 = get_user_meta(get_current_user_id(),"following_me");
						$following_me2 = (isset($following_me2[0])?$following_me2[0]:array());
						if (isset($following_me2) && is_array($following_me2) && !empty($following_me2) and in_array($get_query_var,$following_me2)) {?>
							<a href="#" class="link_follow following_not button color small margin_0" rel="<?php echo $get_query_var?>"><?php _e("Unfollow","vbegy")?></a>
						<?php }else {?>
							<a href="#" class="link_follow following_you button color small margin_0" rel="<?php echo $get_query_var?>"><?php _e("Follow","vbegy")?></a>
						<?php }
						
					}
				}?>
				<div class="clearfix"></div>
				<?php if ($facebook || $twitter || $linkedin || $google || $follow_email || $youtube || $pinterest || $instagram) { ?>
					<br>
					<span class="user-follow-me"><?php _e("Follow Me","vbegy")?></span>
					<div class="social_icons social_icons_display">
						<?php if ($facebook) {?>
							<a href="<?php echo $facebook?>" original-title="<?php _e("Facebook","vbegy")?>" target="_blank" class="tooltip-n">
								<span class="icon_i">
									<span class="icon_square" icon_size="30" span_bg="#3b5997" span_hover="#2f3239">
										<i class="social_icon-facebook"></i>
									</span>
								</span>
							</a>
						<?php }
						if ($twitter) {?>
							<a href="<?php echo $twitter?>" original-title="<?php _e("Twitter","vbegy")?>" target="_blank" class="tooltip-n">
								<span class="icon_i">
									<span class="icon_square" icon_size="30" span_bg="#00baf0" span_hover="#2f3239">
										<i class="social_icon-twitter"></i>
									</span>
								</span>
							</a>
						<?php }
						if ($linkedin) {?>
							<a href="<?php echo $linkedin?>" original-title="<?php _e("Linkedin","vbegy")?>" target="_blank" class="tooltip-n">
								<span class="icon_i">
									<span class="icon_square" icon_size="30" span_bg="#006599" span_hover="#2f3239">
										<i class="social_icon-linkedin"></i>
									</span>
								</span>
							</a>
						<?php }
						if ($google) {?>
							<a href="<?php echo $google?>" original-title="<?php _e("Google plus","vbegy")?>" target="_blank" class="tooltip-n">
								<span class="icon_i">
									<span class="icon_square" icon_size="30" span_bg="#c43c2c" span_hover="#2f3239">
										<i class="social_icon-gplus"></i>
									</span>
								</span>
							</a>
						<?php }
						if ($pinterest) {?>
							<a href="<?php echo $pinterest?>" original-title="<?php _e("Pinterest","vbegy")?>" target="_blank" class="tooltip-n">
								<span class="icon_i">
									<span class="icon_square" icon_size="30" span_bg="#e13138" span_hover="#2f3239">
										<i class="social_icon-pinterest"></i>
									</span>
								</span>
							</a>
						<?php }
						if ($instagram) {?>
							<a href="<?php echo $instagram?>" original-title="<?php _e("Instagram","vbegy")?>" target="_blank" class="tooltip-n">
								<span class="icon_i">
									<span class="icon_square" icon_size="30" span_bg="#548bb6" span_hover="#2f3239">
										<i class="social_icon-instagram"></i>
									</span>
								</span>
							</a>
						<?php }
						if ($follow_email) {?>
							<a href="mailto:<?php echo $user_login->user_email?>" original-title="<?php _e("Email","vbegy")?>" class="tooltip-n">
								<span class="icon_i">
									<span class="icon_square" icon_size="30" span_bg="#000" span_hover="#2f3239">
										<i class="social_icon-email"></i>
									</span>
								</span>
							</a>
						<?php }?>
					</div>
				<?php }?>
			</div><!-- End page-content -->
		</div><!-- End col-md-12 -->
		<div class="col-md-12">
			<div class="page-content page-content-user-profile">
				<div class="user-profile-widget">
					<h2><?php _e("User Stats","vbegy")?></h2>
					<div class="ul_list ul_list-icon-ok">
						<ul>
							<li><i class="icon-question-sign"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('question_user_page'))))?>"><?php _e("Questions","vbegy")?><span> ( <span><?php echo ($add_questions == ""?0:$add_questions)?></span> ) </span></a></li>
							<li><i class="icon-comment"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('answer_user_page'))))?>"><?php _e("Answers","vbegy")?><span> ( <span><?php echo ($add_answer == ""?0:$add_answer)?></span> ) </span></a></li>
							<?php $ask_question_to_users = vpanel_options("ask_question_to_users");
							if ($ask_question_to_users == 1) {?>
								<li><i class="icon-question-sign"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('asked_question_user_page'))))?>"><?php _e("Asked Questions","vbegy")?><span> ( <span><?php echo ($asked_questions == ""?0:$asked_questions)?></span> ) </span></a></li>
							<?php }
							if ($show_point_favorite == 1 || $owner == true) {?>
								<li><i class="icon-star"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('favorite_user_page'))))?>"><?php _e("Favorite Questions","vbegy")?><span> ( <span><?php echo (isset($_favorites[0])?count($_favorites[0]):0)?></span> ) </span></a></li>
								<li><i class="icon-question-sign"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('followed_user_page'))))?>"><?php _e("Followed Questions","vbegy")?><span> ( <span><?php echo (isset($following_questions[0])?count($following_questions[0]):0)?></span> ) </span></a></li>
								<?php $active_points = vpanel_options("active_points");
								if ($active_points == 1) {?>
								<li><i class="icon-heart"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('point_user_page'))))?>"><?php _e("Points","vbegy")?><span> ( <span><?php echo ($points == "" ?0:$points)?></span> ) </span></a></li>
								<?php }
							}?>
							<li><i class="icon-file-alt"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('post_user_page'))))?>"><?php _e("Posts","vbegy")?><span> ( <span><?php echo count_user_posts_by_type($get_query_var,"post")?></span> ) </span></a></li>
							<li><i class="icon-comment"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('comment_user_page'))))?>"><?php _e("Comments","vbegy")?><span> ( <span><?php echo ($add_comment == ""?0:$add_comment)?></span> ) </span></a></li>
							<li><i class="icon-asterisk"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('best_answer_user_page'))))?>"><?php _e("Best Answers","vbegy")?><span> ( <span><?php echo ($the_best_answer == ""?0:$the_best_answer)?></span> ) </span></a></li>
							<?php if ($show_point_favorite == 1 || $owner == true) {?>
								<li class="authors_follow"><i class="icon-user-md"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('i_follow_user_page'))))?>"><?php _e("Authors I Follow","vbegy")?><span> ( <span><?php echo (isset($following_me[0]) && is_array($following_me[0])?count($following_me[0]):0)?></span> ) </span></a></li>
								<li class="followers"><i class="icon-user"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('followers_user_page'))))?>"><?php _e("Followers","vbegy")?><span> ( <span><?php echo (isset($following_you[0]) && is_array($following_you[0])?count($following_you[0]):0)?></span> ) </span></a></li>
								<li class="follow_questions"><i class="icon-question-sign"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('follow_question_page'))))?>"><?php _e("Follow questions","vbegy")?><span> ( <span><?php echo esc_attr($follow_questions)?></span> ) </span></a></li>
								<li class="follow_answers"><i class="icon-comment"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('follow_answer_page'))))?>"><?php _e("Follow answers","vbegy")?><span> ( <span><?php echo esc_attr($follow_answers)?></span> ) </span></a></li>
								<li class="follow_posts"><i class="icon-file-alt"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('follow_post_page'))))?>"><?php _e("Follow posts","vbegy")?><span> ( <span><?php echo esc_attr($follow_posts)?></span> ) </span></a></li>
								<li class="follow_comments"><i class="icon-comments"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('follow_comment_page'))))?>"><?php _e("Follow comments","vbegy")?><span> ( <span><?php echo esc_attr($follow_comments)?></span> ) </span></a></li>
								<?php $pay_ask = vpanel_options("pay_ask");
								if ($pay_ask == 1) {?>
									<li class="paid_question"><i class="icon-shopping-cart"></i><a href="<?php echo esc_url(add_query_arg("u", esc_attr($get_query_var),get_page_link(vpanel_options('paid_question'))))?>"><?php _e("Paid question","vbegy")?><span> ( <span><?php echo esc_attr($paid_questions)?></span> ) </span></a></li>
								<?php }
							}?>
						</ul>
					</div>
				</div><!-- End user-profile-widget -->
			</div><!-- End page-content -->
		</div><!-- End col-md-12 -->
	</div><!-- End user-profile -->
</div><!-- End row -->
<div class="clearfix"></div>