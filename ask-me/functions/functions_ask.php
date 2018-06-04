<?php
ob_start();
function ask_members_only() {
	if (!is_user_logged_in) ask_redirect_login();
}
/* vpanel_media_library */
add_action('pre_get_posts','vpanel_media_library');
function vpanel_media_library($wp_query_obj) {
	global $current_user,$pagenow;
	if (!is_a($current_user,'WP_User') || is_super_admin($current_user->ID))
		return;
	if ('admin-ajax.php' != $pagenow || $_REQUEST['action'] != 'query-attachments')
		return;
	if (!current_user_can('manage_media_library'))
		$wp_query_obj->set('author',$current_user->ID);
	return;
}
/* question_poll */
function question_poll() {
	$poll_id = (int)$_POST['poll_id'];
	$poll_title = stripslashes($_POST['poll_title']);
	$post_id = (int)$_POST['post_id'];
	$user_id = get_current_user_id();
	
	$asks = get_post_meta($post_id,"ask",true);
	if (empty($asks) && !is_array($asks)) {
		$asks = array();
	}
	
	$question_poll_num = get_post_meta($post_id,'question_poll_num',true);
	$question_poll_num++;
	update_post_meta($post_id,'question_poll_num',$question_poll_num);
	
	$needle = $asks[$poll_id];
	$value = $needle["value"];
	$user_ids = (isset($needle["user_ids"])?$needle["user_ids"]:array());
	
	if ($value == "") {
		$value_end = 1;
	}else {
		$value_end = $value+1;
	}
	
	if (!is_array($user_ids)) {
		$user_ids_end = array(($user_id != 0?$user_id:0));
	}else {
		$user_ids_end = array_merge($user_ids,array(($user_id != 0?$user_id:0)));
	}
	
	foreach ($asks as $key => $value) {
		if($value == $needle) {
			$asks[$key] = array("title" => $poll_title,"value" => $value_end,"id" => $poll_id,"user_ids" => $user_ids_end);
		}
	}
	
	$update = update_post_meta($post_id,'ask',$asks);

	if ($update) {
		setcookie('question_poll'.$post_id,"ask_yes_poll",time()+3600*24*365,'/');
	}
	
	$get_post = get_post($post_id);
	$anonymously_user = get_post_meta($post_id,"anonymously_user",true);
	if (($get_post->post_author > 0 && $get_post->post_author != $user_id) || ($anonymously_user > 0 && $anonymously_user != $user_id)) {
		askme_notifications_activities(($get_post->post_author > 0?$get_post->post_author:$anonymously_user),($user_id > 0?$user_id:0),"",$post_id,"","poll_question","notifications",$poll_title,"question");
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,"","poll_question","activities",$poll_title,"question");
	}
	die();
}
add_action('wp_ajax_question_poll','question_poll');
add_action('wp_ajax_nopriv_question_poll','question_poll');
/* question_vote_up */
function question_vote_up() {
	$id = (int)$_POST['id'];
	$get_post = get_post($id);
	$user_id = $get_post->post_author;
	$point_rating_question = vpanel_options("point_rating_question");
	$active_points = vpanel_options("active_points");
	
	$count = get_post_meta($id,'question_vote',true);
	if ($count == "") {
		$count = 0;
	}
	
	if ($user_id != get_current_user_id()) {
		if ($user_id > 0 && $point_rating_question > 0 && $active_points == 1){
			$add_votes = get_user_meta($user_id,"add_votes_all",true);
			if ($add_votes == "" or $add_votes == 0) {
				update_user_meta($user_id,"add_votes_all",1);
			}else {
				update_user_meta($user_id,"add_votes_all",$add_votes+1);
			}
		
			$user_vote = get_user_by("id",$user_id);
			$_points = get_user_meta($user_id,$user_vote->user_login."_points",true);
			$_points++;
		
			update_user_meta($user_id,$user_vote->user_login."_points",$_points);
			add_user_meta($user_id,$user_vote->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),($point_rating_question != ""?$point_rating_question:1),"+","rating_question",$id));
		
			$points_user = get_user_meta($user_id,"points",true);
			update_user_meta($user_id,"points",$points_user+($point_rating_question != ""?$point_rating_question:1));
		}
		
		$count++;
		$update = update_post_meta($id,'question_vote',$count);
		if($update) {
			setcookie('question_vote'.$id,"ask_yes",time()+3600*24*365,'/');
		}
		
		$another_user_id = $user_id;
		$user_id = get_current_user_id();
		$anonymously_user = get_post_meta($id,"anonymously_user",true);
		if (($user_id > 0 && $another_user_id > 0) || ($user_id > 0 && $anonymously_user > 0)) {
			askme_notifications_activities(($another_user_id > 0?$another_user_id:$anonymously_user),$user_id,"",$id,"","question_vote_up","notifications","","question");
		}
		if ($user_id > 0) {
			askme_notifications_activities($user_id,"","",$id,"","question_vote_up","activities","","question");
		}
	}
	
	echo $count;
	die();
}
add_action('wp_ajax_question_vote_up','question_vote_up');
add_action('wp_ajax_nopriv_question_vote_up','question_vote_up');
/* question_vote_down */
function question_vote_down() {
	$id = (int)$_POST['id'];
	
	$get_post = get_post($id);
	$user_id = $get_post->post_author;
	$point_rating_question = vpanel_options("point_rating_question");
	$active_points = vpanel_options("active_points");
	
	$count = get_post_meta($id,'question_vote',true);
	if ($count == "") {
		$count = 0;
	}
	
	if ($user_id != get_current_user_id()) {
		if ($user_id > 0 && $point_rating_question > 0 && $active_points == 1){
			$add_votes = get_user_meta($user_id,"add_votes_all",true);
			if ($add_votes == "" or $add_votes == 0) {
				update_user_meta($user_id,"add_votes_all",1);
			}else {
				update_user_meta($user_id,"add_votes_all",$add_votes+1);
			}
			
			$user_vote = get_user_by("id",$user_id);
			$_points = get_user_meta($user_id,$user_vote->user_login."_points",true);
			$_points++;
		
			update_user_meta($user_id,$user_vote->user_login."_points",$_points);
			add_user_meta($user_id,$user_vote->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),($point_rating_question != ""?$point_rating_question:1),"-","rating_question",$id));
		
			$points_user = get_user_meta($user_id,"points",true);
			update_user_meta($user_id,"points",$points_user-($point_rating_question != ""?$point_rating_question:1));
		}
		
		$count--;
		$update = update_post_meta($id,'question_vote',$count);
		if($update) {
			setcookie('question_vote'.$id,"ask_yes",time()+3600*24*365,'/');
		}
		
		$another_user_id = $user_id;
		$user_id = get_current_user_id();
		$anonymously_user = get_post_meta($id,"anonymously_user",true);
		if (($user_id > 0 && $another_user_id > 0) || ($user_id > 0 && $anonymously_user > 0)) {
			askme_notifications_activities(($another_user_id > 0?$another_user_id:$anonymously_user),$user_id,"",$id,"","question_vote_down","notifications","","question");
		}
		if ($user_id > 0) {
			askme_notifications_activities($user_id,"","",$id,"","question_vote_down","activities","","question");
		}
	}
	echo $count;
	die();
}
add_action('wp_ajax_question_vote_down','question_vote_down');
add_action('wp_ajax_nopriv_question_vote_down','question_vote_down');
/* comment_vote_up */
function comment_vote_up() {
	$id = (int)$_POST['id'];
	$get_comment = get_comment($id);
	$post_id = $get_comment->comment_post_ID;
	$active_points = vpanel_options("active_points");
	$user_id = get_current_user_id();
	
	if ($get_comment->user_id != 0) {
		$user_votes_id = $get_comment->user_id;
		if ($active_points == 1) {
			$add_votes = get_user_meta($user_votes_id,"add_votes_all",true);
			if ($add_votes == "" or $add_votes == 0) {
				update_user_meta($user_votes_id,"add_votes_all",1);
			}else {
				update_user_meta($user_votes_id,"add_votes_all",$add_votes+1);
			}
		
			$current_user = $get_comment->user_id;
			$user_vote = get_user_by("id",$get_comment->user_id);
			$_points = get_user_meta($get_comment->user_id,$user_vote->user_login."_points",true);
			$_points++;
		
			update_user_meta($get_comment->user_id,$user_vote->user_login."_points",$_points);
			add_user_meta($get_comment->user_id,$user_vote->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),(vpanel_options("point_rating_answer") != ""?vpanel_options("point_rating_answer"):1),"+","rating_answer",$post_id,$id));
		
			$points_user = get_user_meta($get_comment->user_id,"points",true);
			update_user_meta($get_comment->user_id,"points",$points_user+(vpanel_options("point_rating_answer") != ""?vpanel_options("point_rating_answer"):1));
		}
		
		$anonymously_user = get_comment_meta($id,"anonymously_user",true);
		if (($user_id > 0 && $user_votes_id > 0) || ($user_id > 0 && $anonymously_user > 0)) {
			askme_notifications_activities(($user_votes_id > 0?$user_votes_id:$anonymously_user),$user_id,"",$post_id,$id,"answer_vote_up","notifications","","answer");
		}
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,$id,"answer_vote_up","activities","","answer");
	}
	
	$count = get_comment_meta($id,'comment_vote',true);
	
	if (isset($count) && is_array($count) && isset($count["vote"])) {
		update_comment_meta($id,'comment_vote',$count["vote"]);
		$count = get_comment_meta($id,'comment_vote',true);
	}
	
	if ($count == "") {
		$count = 0;
	}
	$count++;
	$update = update_comment_meta($id,'comment_vote',$count);
	if($update) {
		setcookie('comment_vote'.$id,"ask_yes_comment",time()+3600*24*365,'/');
	}
	echo $count;
	die();
}
add_action('wp_ajax_comment_vote_up','comment_vote_up');
add_action('wp_ajax_nopriv_comment_vote_up','comment_vote_up');
/* comment_vote_down */
function comment_vote_down() {
	$id = (int)$_POST['id'];
	$get_comment = get_comment($id);
	$post_id = $get_comment->comment_post_ID;
	$active_points = vpanel_options("active_points");
	$user_id = get_current_user_id();
	
	if ($get_comment->user_id != 0) {
		$user_votes_id = $get_comment->user_id;
		if ($active_points == 1) {
			$add_votes = get_user_meta($user_votes_id,"add_votes_all",true);
			if ($add_votes == "" or $add_votes == 0) {
				update_user_meta($user_votes_id,"add_votes_all",1);
			}else {
				update_user_meta($user_votes_id,"add_votes_all",$add_votes+1);
			}
			
			$current_user = $get_comment->user_id;
			$user_vote = get_user_by("id",$get_comment->user_id);
			$_points = get_user_meta($get_comment->user_id,$user_vote->user_login."_points",true);
			$_points++;
			
			update_user_meta($get_comment->user_id,$user_vote->user_login."_points",$_points);
			add_user_meta($get_comment->user_id,$user_vote->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),(vpanel_options("point_rating_answer") != ""?vpanel_options("point_rating_answer"):1),"-","rating_answer",$post_id,$id));
		
			$points_user = get_user_meta($get_comment->user_id,"points",true);
			update_user_meta($get_comment->user_id,"points",$points_user-(vpanel_options("point_rating_answer") != ""?vpanel_options("point_rating_answer"):1));
		}
		
		$anonymously_user = get_comment_meta($id,"anonymously_user",true);
		if (($user_id > 0 && $user_votes_id > 0) || ($user_id > 0 && $anonymously_user > 0)) {
			askme_notifications_activities(($user_votes_id > 0?$user_votes_id:$anonymously_user),$user_id,"",$post_id,$id,"answer_vote_down","notifications","","answer");
		}
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,$id,"answer_vote_down","activities","","answer");
	}
	
	$count = get_comment_meta($id,'comment_vote',true);
	
	if (isset($count) && is_array($count) && isset($count["vote"])) {
		update_comment_meta($id,'comment_vote',$count["vote"]);
		$count = get_comment_meta($id,'comment_vote',true);
	}
	
	if ($count == "") {
		$count = 0;
	}
	$count--;
	$update = update_comment_meta($id,'comment_vote',$count);
	if($update) {
		setcookie('comment_vote'.$id,"ask_yes_comment",time()+3600*24*365,'/');
	}
	echo $count;
	die();
}
add_action('wp_ajax_comment_vote_down','comment_vote_down');
add_action('wp_ajax_nopriv_comment_vote_down','comment_vote_down');
/* following_me */
function following_me () {
	$following_you_id = (int)$_POST["following_you_id"];
	$get_user_by_following_id = get_user_by("id",$following_you_id);
	$active_points = vpanel_options("active_points");
	$point_following_me = vpanel_options("point_following_me");
	$point_following_me = ($point_following_me != ""?$point_following_me:1);

	$following_me_get = get_user_meta(get_current_user_id(),"following_me",true);
	if (empty($following_me_get)) {
		update_user_meta(get_current_user_id(),"following_me",array($following_you_id));
	}else {
		update_user_meta(get_current_user_id(),"following_me",array_merge($following_me_get,array($following_you_id)));
	}
	if ($active_points == 1) {
		$points_get = get_user_meta($following_you_id,"points",true);
		if ($points_get == "" or $points_get == 0) {
			update_user_meta($following_you_id,"points",$point_following_me);
		}else {
			$new_points = $points_get+$point_following_me;
			update_user_meta($following_you_id,"points",$new_points);
		}
		
		$_points = get_user_meta($following_you_id,$get_user_by_following_id->user_login."_points",true);
		$_points++;
		
		update_user_meta($following_you_id,$get_user_by_following_id->user_login."_points",$_points);
		add_user_meta($following_you_id,$get_user_by_following_id->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_following_me,"+","user_follow","",""));
	}
	
	$another_user_id = $following_you_id;
	$user_id = get_current_user_id();
	if ($user_id > 0 && $another_user_id > 0) {
		askme_notifications_activities($another_user_id,$user_id,"","","","user_follow","notifications");
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,$another_user_id,"","","","user_follow","activities");
	}

	$following_you_get = get_user_meta($following_you_id,"following_you",true);
	if (empty($following_you_get)) {
		update_user_meta($following_you_id,"following_you",array(get_current_user_id()));
	}else {
		update_user_meta($following_you_id,"following_you",array_merge($following_you_get,array(get_current_user_id())));
	}
	
	$echo_following_you = get_user_meta($following_you_id,"following_you",true);
	echo (isset($echo_following_you) && is_array($echo_following_you)?count($echo_following_you):0);
	die();
}
add_action('wp_ajax_following_me','following_me');
add_action('wp_ajax_nopriv_following_me','following_me');
/* following_not */
function following_not () {
	$following_not_id = (int)$_POST["following_not_id"];
	$get_user_by_following_not_id = get_user_by("id",$following_not_id);
	$active_points = vpanel_options("active_points");
	$point_following_me = vpanel_options("point_following_me");
	$point_following_me = ($point_following_me != ""?$point_following_me:1);
	
	$following_me = get_user_meta(get_current_user_id(),"following_me",true);
	$remove_following_me = remove_item_by_value($following_me,$following_not_id);
	update_user_meta(get_current_user_id(),"following_me",$remove_following_me);
	if ($active_points == 1) {
		$points = get_user_meta($following_not_id,"points",true);
		$new_points = $points-$point_following_me;
		if ($new_points < 0) {
			$new_points = 0;
		}
		update_user_meta($following_not_id,"points",$new_points);
		
		$_points = get_user_meta($following_not_id,$get_user_by_following_not_id->user_login."_points",true);
		$_points++;
		
		update_user_meta($following_not_id,$get_user_by_following_not_id->user_login."_points",$_points);
		add_user_meta($following_not_id,$get_user_by_following_not_id->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_following_me,"-","user_unfollow","",""));
	}
	
	$another_user_id = $following_not_id;
	$user_id = get_current_user_id();
	if ($user_id > 0 && $another_user_id > 0) {
		askme_notifications_activities($another_user_id,$user_id,"","","","user_unfollow","notifications");
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,$another_user_id,"","","","user_unfollow","activities");
	}
	
	$following_you = get_user_meta($following_not_id,"following_you",true);
	$get_user_by_following_not_id2 = get_user_by("id",get_current_user_id());
	$remove_following_you = remove_item_by_value($following_you,$get_user_by_following_not_id2->ID);
	update_user_meta($following_not_id,"following_you",$remove_following_you);
	
	$echo_following_you = get_user_meta($following_not_id,"following_you",true);
	echo (isset($echo_following_you) && is_array($echo_following_you)?count($echo_following_you):0);
	die();
}
add_action('wp_ajax_following_not','following_not');
add_action('wp_ajax_nopriv_following_not','following_not');
/* add_point */
function add_point () {
	$input_add_point = (int)$_POST["input_add_point"];
	$post_id = (int)$_POST["post_id"];
	$user_id = get_current_user_id();
	$user_name = get_user_by("id",$user_id);
	$points_user = get_user_meta($user_id,"points",true);
	$get_post = get_post($post_id);
	if (get_current_user_id() != $get_post->post_author) {
		_e("Sorry no mistake, this is not a question asked.","vbegy");
	}else if ($points_user >= $input_add_point) {
		if ($input_add_point == "") {
			_e("You must enter a numeric value and a value greater than zero.","vbegy");
		}else if ($input_add_point <= 0) {
			_e("You must enter a numeric value and a value greater than zero.","vbegy");
		}else {
			$question_points = get_post_meta($post_id,"question_points",true);
			if ($question_points == 0) {
				$question_points = $input_add_point;
			}else {
				$question_points = $input_add_point+$question_points;
			}
			update_post_meta($post_id,"question_points",$question_points);
			
			$_points = get_user_meta($user_id,$user_name->user_login."_points",true);
			$_points++;
			update_user_meta($user_id,$user_name->user_login."_points",$_points);
			add_user_meta($user_id,$user_name->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$input_add_point,"-","bump_question",$post_id));
			$points_user = get_user_meta($user_id,"points",true);
			update_user_meta($user_id,"points",$points_user-$input_add_point);
			_e("You bump your question now.","vbegy");
			if ($user_id > 0) {
				askme_notifications_activities($user_id,"","",$post_id,"","bump_question","activities","","question");
			}
		}
	}else {
		_e("Your points are insufficient.","vbegy");
	}
	die();
}
add_action('wp_ajax_add_point','add_point');
add_action('wp_ajax_nopriv_add_point','add_point');
/* ask_redirect_login */
function ask_redirect_login() {
	if (vpanel_options("login_page") != "") {
		wp_redirect(get_permalink(vpanel_options("login_page")));
	}else {
		wp_redirect(wp_login_url(home_url()));
	}
	exit;
}
/* ask_get_filesize */
if (!function_exists('ask_get_filesize')) {
	function ask_get_filesize($file) { 
		$bytes = filesize($file);
		$s = array('b','Kb','Mb','Gb');
		$e = floor(log($bytes)/log(1024));
		return sprintf('%.2f '.$s[$e],($bytes/pow(1024,floor($e))));
	}
}
/* report_q */
function report_q () {
	global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
	$post_id = (int)$_POST['post_id'];
	$explain = esc_attr($_POST['explain']);
	$user_id = get_current_user_id();
	
	/* option */
	$ask_option = get_option("ask_option");
	$ask_option_array = get_option("ask_option_array");
	if ($ask_option_array == "") {
		$ask_option_array = array();
	}
	if ($ask_option != "") {
		$ask_option++;
		update_option("ask_option",$ask_option);
		array_push($ask_option_array,$ask_option);
		update_option("ask_option_array",$ask_option_array);
	}else {
		$ask_option = 1;
		add_option("ask_option",$ask_option);
		add_option("ask_option_array",array($ask_option));
	}
	$ask_time = current_time('timestamp');
	/* option */
	if ($user_id > 0 && is_user_logged_in) {
		$name_last = "";
		$id_last = $user_id;
	}else {
		$name_last = 1;
		$id_last = "";
	}
	/* add option */
	add_option("ask_option_".$ask_option,array("post_id" => $post_id,"the_date" => $ask_time,"report_new" => 1,"user_id" => $id_last,"the_author" => $name_last,"item_id_option" => $ask_option,"value" => $explain));
	$send_text = ask_send_email(vpanel_options("email_report_question"),"",$post_id);
	$logo_email_template = vpanel_options("logo_email_template");
	$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
	$email_title = vpanel_options("title_report_question");
	$email_title = ($email_title != ""?$email_title:__("Question report","vbegy"));
	sendEmail(vpanel_options("email_template"),get_bloginfo('name'),vpanel_options("email_template"),get_bloginfo('name'),$email_title,$last_message_email);
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,"","report_question","activities","","question");
	}
	die();
}
add_action('wp_ajax_report_q','report_q');
add_action('wp_ajax_nopriv_report_q','report_q');
/* report_c */
function report_c () {
	global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
	$comment_id = (int)$_POST['comment_id'];
	$explain = esc_attr($_POST['explain']);
	$get_comment = get_comment($comment_id);
	$user_id = get_current_user_id();
	
	/* option */
	$ask_option_answer = get_option("ask_option_answer");
	$ask_option_answer_array = get_option("ask_option_answer_array");
	if ($ask_option_answer_array == "") {
		$ask_option_answer_array = array();
	}
	if ($ask_option_answer != "") {
		$ask_option_answer++;
		update_option("ask_option_answer",$ask_option_answer);
		array_push($ask_option_answer_array,$ask_option_answer);
		update_option("ask_option_answer_array",$ask_option_answer_array);
	}else {
		$ask_option_answer = 1;
		add_option("ask_option_answer",$ask_option_answer);
		add_option("ask_option_answer_array",array($ask_option_answer));
	}
	$ask_time = current_time('timestamp');
	/* option */
	if ($user_id > 0 && is_user_logged_in) {
		$name_last = "";
		$id_last = $user_id;
	}else {
		$name_last = 1;
		$id_last = "";
	}
	/* add option */
	add_option("ask_option_answer_".$ask_option_answer,array("post_id" => $get_comment->comment_post_ID,"comment_id" => $comment_id,"the_date" => $ask_time,"report_new" => 1,"user_id" => $id_last,"the_author" => $name_last,"item_id_option" => $ask_option_answer,"value" => $explain));
	$send_text = ask_send_email(vpanel_options("email_report_answer"),"",$get_comment->comment_post_ID,$comment_id);
	$logo_email_template = vpanel_options("logo_email_template");
	$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
	$email_title = vpanel_options("title_report_answer");
	$email_title = ($email_title != ""?$email_title:__("Answer report","vbegy"));
	sendEmail(vpanel_options("email_template"),get_bloginfo('name'),vpanel_options("email_template"),get_bloginfo('name'),$email_title,$last_message_email);
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$get_comment->comment_post_ID,$comment_id,"report_answer","activities","","answer");
	}
	die();
}
add_action('wp_ajax_report_c','report_c');
add_action('wp_ajax_nopriv_report_c','report_c');
/* best_answer */
function best_answer() {
	$comment_id = (int)$_POST['comment_id'];
	$get_comment = get_comment($comment_id);
	$user_id = $get_comment->user_id;
	$post_id = $get_comment->comment_post_ID;
	$post_author = get_post($post_id);
	$user_author = $post_author->post_author;
	update_post_meta($post_id,"the_best_answer",$comment_id);
	$active_points = vpanel_options("active_points");
	$get_current_user_id = get_current_user_id();
	if ($user_id != 0) {
		$user_name = get_user_by("id",$user_id);
		if ($user_id != $user_author && $active_points == 1) {
			$_points = get_user_meta($user_id,$user_name->user_login."_points",true);
			$_points++;
			update_user_meta($user_id,$user_name->user_login."_points",$_points);
			add_user_meta($user_id,$user_name->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),(vpanel_options("point_best_answer") != ""?vpanel_options("point_best_answer"):5),"+","select_best_answer",$post_id,$comment_id));
			$points_user = get_user_meta($user_id,"points",true);
			update_user_meta($user_id,"points",$points_user+(vpanel_options("point_best_answer") != ""?vpanel_options("point_best_answer"):5));
		}
		$the_best_answer_u = get_user_meta($user_id,"the_best_answer",true);
		if ($the_best_answer_u == "" || $the_best_answer_u < 0) {
			$the_best_answer_u = 0;
		}
		$the_best_answer_u++;
		update_user_meta($user_id,"the_best_answer",$the_best_answer_u);
	}
	update_comment_meta($comment_id,"best_answer_comment","best_answer_comment");
	$option_name = "best_answer_option";
	$best_answer_option = get_option($option_name);
	if ($best_answer_option == "" || $best_answer_option < 0) {
		$best_answer_option = 0;
	}
	$best_answer_option++;
	update_option($option_name,$best_answer_option);
	update_option("best_answer_done","yes");
	
	$point_back_option = vpanel_options("point_back");
	$anonymously_user = get_post_meta($post_id,"anonymously_user",true);
	if ($point_back_option == 1 && $active_points == 1 && (is_super_admin($get_current_user_id) || ($user_id != $user_author && $user_author > 0) || ($user_id != $anonymously_user && $anonymously_user > 0))) {
		$point_back_number = vpanel_options("point_back_number");
		$point_back = get_post_meta($post_id,"point_back",true);
		$what_point = get_post_meta($post_id,"what_point",true);
		
		if ($point_back_number > 0) {
			$what_point = $point_back_number;
		}
		
		if ($point_back == "yes" && ($user_author > 0 || $anonymously_user > 0)) {
			$author_points = ($anonymously_user > 0?$anonymously_user:$user_author);
			$user_name2 = get_user_by("id",$author_points);
			$_points = get_user_meta($author_points,$user_name2->user_login."_points",true);
			$_points++;
			update_user_meta($author_points,$user_name2->user_login."_points",$_points);
			add_user_meta($author_points,$user_name2->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),($what_point != ""?$what_point:vpanel_options("question_points")),"+","point_back",$post_id,$comment_id));
			$points_user = get_user_meta($author_points,"points",true);
			update_user_meta($author_points,"points",$points_user+($what_point != ""?$what_point:vpanel_options("question_points")));
			
			if ($user_author > 0 || $anonymously_user > 0) {
				askme_notifications_activities(($user_author > 0?$user_author:$anonymously_user),"","",$post_id,$comment_id,"point_back","notifications");
			}
		}
	}
	
	$anonymously_user = get_comment_meta($comment_id,"anonymously_user",true);
	if (($user_id > 0 && $get_current_user_id > 0 && $user_id != $get_current_user_id) || ($anonymously_user > 0 && $get_current_user_id > 0 && $anonymously_user != $get_current_user_id)) {
		askme_notifications_activities(($user_id > 0?$user_id:$anonymously_user),$get_current_user_id,"",$post_id,$comment_id,"select_best_answer","notifications","","answer");
	}
	if ($get_current_user_id > 0) {
		askme_notifications_activities($get_current_user_id,"","",$post_id,$comment_id,"select_best_answer","activities","","answer");
	}
	
	die();
}
add_action('wp_ajax_best_answer','best_answer');
add_action('wp_ajax_nopriv_best_answer','best_answer');
/* best_answer_remove */
function best_answer_re() {
	$comment_id = (int)$_POST['comment_id'];
	$get_comment = get_comment($comment_id);
	$user_id = $get_comment->user_id;
	$post_id = $get_comment->comment_post_ID;
	$post_author = get_post($post_id);
	$user_author = $post_author->post_author;
	delete_post_meta($post_id,"the_best_answer",$comment_id);
	$active_points = vpanel_options("active_points");
	$get_current_user_id = get_current_user_id();
	if ($user_id != 0) {
		$user_name = get_user_by("id",$user_id);
		if ($user_id != $user_author && $active_points == 1) {
			$_points = get_user_meta($user_id,$user_name->user_login."_points",true);
			$_points++;
			update_user_meta($user_id,$user_name->user_login."_points",$_points);
			add_user_meta($user_id,$user_name->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),(vpanel_options("point_best_answer") != ""?vpanel_options("point_best_answer"):5),"-","cancel_best_answer",$post_id,$comment_id));
			$points_user = get_user_meta($user_id,"points",true);
			update_user_meta($user_id,"points",$points_user-(vpanel_options("point_best_answer") != ""?vpanel_options("point_best_answer"):5));
		}
		$the_best_answer_u = get_user_meta($user_id,"the_best_answer",true);
		$the_best_answer_u--;
		if ($the_best_answer_u < 0) {
			$the_best_answer_u = 0;
		}
		update_user_meta($user_id,"the_best_answer",$the_best_answer_u);
	}
	delete_comment_meta($comment_id,"best_answer_comment");
	$option_name = "best_answer_option";
	$best_answer_option = get_option($option_name);
	if ($best_answer_option == "") {
		$best_answer_option = 0;
	}
	$best_answer_option--;
	if ($best_answer_option < 0) {
		$best_answer_option = 0;
	}
	update_option($option_name,$best_answer_option);
	update_option("best_answer_done","yes");
	
	$point_back_option = vpanel_options("point_back");
	$anonymously_user = get_post_meta($post_id,"anonymously_user",true);
	if ($point_back_option == 1 && $active_points == 1 && (is_super_admin($get_current_user_id) || ($user_id != $user_author && $user_author > 0) || ($user_id != $anonymously_user && $anonymously_user > 0))) {
		$point_back_number = vpanel_options("point_back_number");
		$point_back = get_post_meta($post_id,"point_back",true);
		$what_point = get_post_meta($post_id,"what_point",true);
		
		if ($point_back_number > 0) {
			$what_point = $point_back_number;
		}
		
		if ($point_back == "yes" && ($user_author > 0 || $anonymously_user > 0)) {
			$user_name2 = get_user_by("id",$user_author);
			$_points = get_user_meta($user_author,$user_name2->user_login."_points",true);
			$_points++;
			update_user_meta($user_author,$user_name2->user_login."_points",$_points);
			add_user_meta($user_author,$user_name2->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),($what_point != ""?$what_point:vpanel_options("question_points")),"-","point_removed",$post_id,$comment_id));
			$points_user = get_user_meta($user_author,"points",true);
			update_user_meta($user_author,"points",$points_user-($what_point != ""?$what_point:vpanel_options("question_points")));
		}
		
		if ($user_author > 0 || $anonymously_user > 0) {
			askme_notifications_activities(($user_author > 0?$user_author:$anonymously_user),"","",$post_id,$comment_id,"point_removed","notifications");
		}
	}
	
	$anonymously_user = get_comment_meta($comment_id,"anonymously_user",true);
	if (($user_id > 0 && $get_current_user_id > 0 && $user_id != $get_current_user_id) || ($anonymously_user > 0 && $get_current_user_id > 0 && $anonymously_user != $get_current_user_id)) {
		askme_notifications_activities(($user_id > 0?$user_id:$anonymously_user),$get_current_user_id,"",$post_id,$comment_id,"cancel_best_answer","notifications","","answer");
	}
	if ($get_current_user_id > 0) {
		askme_notifications_activities($get_current_user_id,"","",$post_id,$comment_id,"cancel_best_answer","activities","","answer");
	}
	
	die();
}
add_action('wp_ajax_best_answer_re','best_answer_re');
add_action('wp_ajax_nopriv_best_answer_re','best_answer_re');
/* question_close */
function question_close() {
	$post_id = (int)$_POST['post_id'];
	$post_author = get_post($post_id);
	$user_author = $post_author->post_author;
	$user_id = get_current_user_id();
	if (($user_author != 0 && $user_author == $user_id) || is_super_admin($user_id)) {
		update_post_meta($post_id,'closed_question',1);
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,"","closed_question","activities","","question");
	}
	die();
}
add_action('wp_ajax_question_close','question_close');
add_action('wp_ajax_nopriv_question_close','question_close');
/* question_open */
function question_open() {
	$post_id = (int)$_POST['post_id'];
	$post_author = get_post($post_id);
	$user_author = $post_author->post_author;
	$user_id = get_current_user_id();
	if (($user_author != 0 && $user_author == $user_id) || is_super_admin($user_id)) {
		delete_post_meta($post_id,'closed_question');
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,"","opend_question","activities","","question");
	}
	die();
}
add_action('wp_ajax_question_open','question_open');
add_action('wp_ajax_nopriv_question_open','question_open');
/* question_follow */
function question_follow() {
	$post_id = (int)$_POST['post_id'];
	$user_id = get_current_user_id();
	
	$following_questions_user = get_user_meta($user_id,"following_questions",true);
	if (empty($following_questions_user)) {
		update_user_meta($user_id,"following_questions",array($post_id));
	}else {
		if (is_array($following_questions_user) && !in_array($post_id,$following_questions_user)) {
			update_user_meta($user_id,"following_questions",array_merge($following_questions_user,array($post_id)));
		}
	}
	
	$following_questions = get_post_meta($post_id,"following_questions",true);
	if (empty($following_questions)) {
		update_post_meta($post_id,"following_questions",array($user_id));
	}else {
		if (is_array($following_questions) && !in_array($user_id,$following_questions)) {
			update_post_meta($post_id,"following_questions",array_merge($following_questions,array($user_id)));
		}
	}
	
	$get_post = get_post($post_id);
	$anonymously_user = get_post_meta($post_id,"anonymously_user",true);
	if (($user_id > 0 && $get_post->post_author > 0) || ($user_id > 0 && $anonymously_user > 0)) {
		askme_notifications_activities(($get_post->post_author > 0?$get_post->post_author:$anonymously_user),$user_id,"",$post_id,"","follow_question","notifications","","question");
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,"","follow_question","activities","","question");
	}
	die();
}
add_action('wp_ajax_question_follow','question_follow');
add_action('wp_ajax_nopriv_question_follow','question_follow');
/* question_unfollow */
function question_unfollow() {
	$post_id = (int)$_POST['post_id'];
	$user_id = get_current_user_id();
	
	$following_questions_user = get_user_meta($user_id,"following_questions",true);
	$remove_following_questions_user = remove_item_by_value($following_questions_user,$post_id);
	update_user_meta($user_id,"following_questions",$remove_following_questions_user);
	
	$following_questions = get_post_meta($post_id,"following_questions",true);
	$remove_following_questions = remove_item_by_value($following_questions,$user_id);
	update_post_meta($post_id,"following_questions",$remove_following_questions);
	
	$get_post = get_post($post_id);
	$anonymously_user = get_post_meta($post_id,"anonymously_user",true);
	if (($user_id > 0 && $get_post->post_author > 0) || ($user_id > 0 && $anonymously_user > 0)) {
		askme_notifications_activities(($get_post->post_author > 0?$get_post->post_author:$anonymously_user),$user_id,"",$post_id,"","unfollow_question","notifications","","question");
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,"","unfollow_question","activities","","question");
	}
	die();
}
add_action('wp_ajax_question_unfollow','question_unfollow');
add_action('wp_ajax_nopriv_question_unfollow','question_unfollow');
/* comment_question_before */
add_filter ('preprocess_comment','comment_question_before');
function comment_question_before($commentdata) {
	$get_post_type_comment = "";
	if (!is_admin() && get_post_type($commentdata["comment_post_ID"]) != "product") {
		$the_captcha = 0;
		if (get_post_type($commentdata["comment_post_ID"]) == "question") {
			$the_captcha = vpanel_options("the_captcha_answer");
		}else {
			$the_captcha = vpanel_options("the_captcha_comment");
		}
		$captcha_style = vpanel_options("captcha_style");
		$captcha_question = vpanel_options("captcha_question");
		$captcha_answer = vpanel_options("captcha_answer");
		$show_captcha_answer = vpanel_options("show_captcha_answer");
		if ($the_captcha == 1) {
			if (empty($_POST["ask_captcha"])) {
				if (defined('DOING_AJAX') && DOING_AJAX)
					wp_die(__("<strong>ERROR</strong>: please type a captcha.","vbegy"));
				else
					die(__("<strong>ERROR</strong>: please type a captcha.","vbegy"));
				exit;
			}
			if ($captcha_style == "question_answer") {
				if ($captcha_answer != $_POST["ask_captcha"]) {
					if (defined('DOING_AJAX') && DOING_AJAX)
						wp_die(__('The captcha is incorrect, please try again.','vbegy'));
					else
						die(__('The captcha is incorrect, please try again.','vbegy'));
					exit;
				}
			}else {
				if (isset($_SESSION["security_code"]) && $_SESSION["security_code"] != $_POST["ask_captcha"]) {
					if (defined('DOING_AJAX') && DOING_AJAX)
						wp_die(__('The captcha is incorrect, please try again.','vbegy'));
					else
						die(__('The captcha is incorrect, please try again.','vbegy'));
					exit;
				}
			}
		}
		
		if (isset($_FILES['featured_image']) && !empty($_FILES['featured_image']['name'])) :
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');					
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			$types = array("image/jpeg","image/bmp","image/jpg","image/png","image/gif","image/tiff","image/ico");
			if (!in_array($_FILES['featured_image']['type'],$types)) :
				wp_die(__("Attachment Error, Please upload image only.","vbegy"));
				exit;
			endif;
		endif;
	}
	return $commentdata;
}
/* comment_question */
add_action ('comment_post','comment_question');
function comment_question($comment_id) {
	$get_comment = get_comment($comment_id);
	$get_post = get_post($get_comment->comment_post_ID);
	if ($get_post->post_type == "question") {
		$user_id = get_current_user_id();
		add_comment_meta($comment_id,'comment_type',"question");
		add_comment_meta($comment_id,'comment_vote',0);
		$question_user_id = get_post_meta($get_comment->comment_post_ID,"user_id",true);
		if ($question_user_id != "" && $question_user_id > 0) {
			add_comment_meta($comment_id,"answer_question_user","answer_question_user");
		}
		
		if (isset($_POST["private_answer"]) && $_POST["private_answer"] == 1) {
			add_comment_meta($comment_id,"private_answer",1);
		}
		
		if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) :
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');					
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			$comment_attachment = wp_handle_upload($_FILES['attachment'],array('test_form'=>false),current_time('mysql'));
			if (isset($comment_attachment['error'])) :
				wp_die('Attachment Error: ' . $comment_attachment['error']);
				exit;
			endif;
			$comment_attachment_data = array(
				'post_mime_type' => $comment_attachment['type'],
				'post_title'	 => preg_replace('/\.[^.]+$/','',basename($comment_attachment['file'])),
				'post_content'   => '',
				'post_status'	=> 'inherit',
				'post_author'	=> (get_current_user_id() != "" or get_current_user_id() != 0?get_current_user_id():"")
			);
			$comment_attachment_id = wp_insert_attachment($comment_attachment_data,$comment_attachment['file'],$get_comment->comment_post_ID);
			$comment_attachment_metadata = wp_generate_attachment_metadata($comment_attachment_id,$comment_attachment['file']);
			wp_update_attachment_metadata($comment_attachment_id, $comment_attachment_metadata);
			add_comment_meta($comment_id,'added_file',$comment_attachment_id);
		endif;
		
		if (isset($_FILES['featured_image']) && !empty($_FILES['featured_image']['name'])) :
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');					
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			$comment_featured_image = wp_handle_upload($_FILES['featured_image'],array('test_form'=>false),current_time('mysql'));
			if (isset($comment_featured_image['error'])) :
				wp_die('Attachment Error: ' . $comment_featured_image['error']);
				exit;
			endif;
			$comment_featured_image_data = array(
				'post_mime_type' => $comment_featured_image['type'],
				'post_title'	 => preg_replace('/\.[^.]+$/','',basename($comment_featured_image['file'])),
				'post_content'   => '',
				'post_status'	=> 'inherit',
				'post_author'	=> (get_current_user_id() != "" || get_current_user_id() != 0?get_current_user_id():"")
			);
			$comment_featured_image_id = wp_insert_attachment($comment_featured_image_data,$comment_featured_image['file'],$get_comment->comment_post_ID);
			$comment_featured_image_metadata = wp_generate_attachment_metadata($comment_featured_image_id,$comment_featured_image['file']);
			wp_update_attachment_metadata($comment_featured_image_id, $comment_featured_image_metadata);
			add_comment_meta($comment_id,'featured_image',$comment_featured_image_id);
		endif;
		
		if(!session_id()) session_start();
		if ($get_comment->comment_approved == 1) {
			askme_notifications_add_answer($get_comment,$get_post);
			$_SESSION['vbegy_session_answer'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Added been successfully","vbegy").'</span><br>'.__("Has been Added successfully.","vbegy").'</p></div>';	
			$another_user_id = $get_post->post_author;
			if ($user_id > 0) {
				askme_notifications_activities($user_id,"","",$get_comment->comment_post_ID,$get_comment->comment_ID,"add_answer","activities","","answer","","answer");
			}
			update_comment_meta($get_comment->comment_ID,'comment_approved_before',"yes");
			update_post_meta($get_comment->comment_post_ID,"comment_count",$get_post->comment_count);
		}else {
			$_SESSION['vbegy_session_answer'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Added been successfully","vbegy").'</span><br>'.__("Has been Added successfully, The answer under review.","vbegy").'</p></div>';
			if (get_current_user_id() > 0) {
				askme_notifications_activities(get_current_user_id(),"","","","","approved_answer","activities","","answer","","answer");
			}
		}
	}else {
		if ($get_comment->comment_approved == 1) {
			if (get_current_user_id() > 0) {
				askme_notifications_activities(get_current_user_id(),"","",$get_comment->comment_post_ID,$get_comment->comment_ID,"add_comment","activities");
			}
		}else {
			if (get_current_user_id() > 0) {
				askme_notifications_activities(get_current_user_id(),"","","","","approved_comment","activities");
			}
		}
	}
}
/* Notifications add answer */
function askme_notifications_add_answer($comment,$get_post) {
	global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
	$logo_email_template = vpanel_options("logo_email_template");
	$comment_id = $comment->comment_ID;
	$post_id = $comment->comment_post_ID;
	$comment_user_id = $comment->user_id;
	$post_author = $get_post->post_author;
	$post_title = $get_post->post_title;
	$user_id_question = get_post_meta($post_id,"user_id",true);
	$private_answer = get_comment_meta($comment_id,"private_answer",true);

	$active_notified = vpanel_options("active_notified");
	if ($active_notified == 1) {
		$remember_answer = get_post_meta($post_id,"remember_answer",true);
		if ($remember_answer == 1 && $post_author != $comment_user_id) {
			$the_name = $comment->comment_author;
			if ($post_author != 0) {
				$get_the_author = get_user_by("id",$post_author);
				$the_mail = $get_the_author->user_email;
				$the_author = $get_the_author->display_name;
			}else {
				$the_mail = get_post_meta($post_id,'question_email',true);
				$the_author = get_post_meta($post_id,'question_username',true);
				$the_author = ($the_author != ""?$the_author:esc_html__("Anonymous","vbegy"));
			}
			if ($the_mail != "") {
				$send_text = ask_send_email(vpanel_options("email_notified_answer"),"",$post_id,$comment_id);
				$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
				$email_title = vpanel_options("title_notified_answer");
				$email_title = ($email_title != ""?$email_title:__("Answer to your question","vbegy"));
				sendEmail(vpanel_options("email_template"),get_bloginfo('name'),$the_mail,$the_author,$email_title,$last_message_email);
			}
		}
	}
	
	$question_follow = vpanel_options("question_follow");
	$following_questions = get_post_meta($post_id,"following_questions",true);
	if ($question_follow == 1 && isset($following_questions) && is_array($following_questions)) {
		$send_text = ask_send_email(vpanel_options("email_follow_question"),"",$post_id,$comment_id);
		$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
		$email_title = vpanel_options("title_follow_question");
		$email_title = ($email_title != ""?$email_title:__("Hi there","vbegy"));
		foreach ($following_questions as $user) {
			if ($user_id_question != $user) {
				$all_meta_for_user = get_user_by('id',$user);
				$yes_private_answer = ask_private_answer($comment_id,$comment_user_id,$user);
				$another_user_id = $user;
				if ($another_user_id > 0 && $comment_user_id != $another_user_id && $yes_private_answer == 1) {
					askme_notifications_activities($another_user_id,$comment_user_id,($comment_user_id == 0?$comment->comment_author:0),$post_id,$comment_id,"answer_question_follow","notifications","","answer");
				}
				
				sendEmail(vpanel_options("email_template"),get_bloginfo('name'),esc_attr($all_meta_for_user->user_email),esc_attr($all_meta_for_user->display_name),$email_title,$last_message_email);
			}
		}
	}
	
	$active_points = vpanel_options("active_points");
	if ($comment_user_id != 0) {
		$user_data = get_user_by("id",$comment_user_id);
		if ($comment_user_id != $post_author && $active_points == 1) {
			$_points = get_user_meta($comment_user_id,$user_data->user_login."_points",true);
			$_points++;
			
			update_user_meta($comment_user_id,$user_data->user_login."_points",$_points);
			add_user_meta($comment_user_id,$user_data->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),(vpanel_options("point_add_comment") != ""?vpanel_options("point_add_comment"):2),"+","answer_question",$post_id,$comment->comment_ID));
		
			$points_user = get_user_meta($comment_user_id,"points",true);
			update_user_meta($comment_user_id,"points",$points_user+(vpanel_options("point_add_comment") != ""?vpanel_options("point_add_comment"):2));
		}
		
		$add_answer = get_user_meta($comment_user_id,"add_answer_all",true);
		$add_answer_m = get_user_meta($comment_user_id,"add_answer_m_".date_i18n('m_Y',current_time('timestamp')),true);
		$add_answer_d = get_user_meta($comment_user_id,"add_answer_d_".date_i18n('d_m_Y',current_time('timestamp')),true);
		if ($add_answer_d == "" or $add_answer_d == 0) {
			update_user_meta($comment_user_id,"add_answer_d_".date_i18n('d_m_Y',current_time('timestamp')),1);
		}else {
			update_user_meta($comment_user_id,"add_answer_d_".date_i18n('d_m_Y',current_time('timestamp')),$add_answer_d+1);
		}
		
		if ($add_answer_m == "" or $add_answer_m == 0) {
			update_user_meta($comment_user_id,"add_answer_m_".date_i18n('m_Y',current_time('timestamp')),1);
		}else {
			update_user_meta($comment_user_id,"add_answer_m_".date_i18n('m_Y',current_time('timestamp')),$add_answer_m+1);
		}
		
		if ($add_answer == "" or $add_answer == 0) {
			update_user_meta($comment_user_id,"add_answer_all",1);
		}else {
			update_user_meta($comment_user_id,"add_answer_all",$add_answer+1);
		}

	}	
	
	$user_is_comment = get_post_meta($post_id,"user_is_comment",true);
	$anonymously_user = get_post_meta($post_id,"anonymously_user",true);
	$yes_private_answer_1 = ask_private_answer($comment_id,$comment_user_id,$post_author);
	$yes_private_answer_2 = ask_private_answer($comment_id,$comment_user_id,$anonymously_user);
	if (($yes_private_answer_2 == 1 && $post_author > 0 && $comment_user_id != $post_author) || ($yes_private_answer_2 == 1 && $anonymously_user > 0 && $comment_user_id != $anonymously_user)) {
		askme_notifications_activities(($post_author > 0?$post_author:$anonymously_user),$comment_user_id,($comment_user_id == 0?$comment->comment_author:0),$post_id,$comment_id,"answer_question","notifications","","answer");
	}
	$yes_private_answer = ask_private_answer($comment_id,$comment_user_id,$user_id_question);
	if ($yes_private_answer == 1 && $user_id_question != "") {
		if ($user_id_question != $comment_user_id) {
			askme_notifications_activities($user_id_question,$comment_user_id,($comment_user_id == 0?$comment->comment_author:0),$post_id,$comment_id,"answer_asked_question","notifications","","answer");
		}
		if ($user_is_comment != true && $user_id_question == $comment_user_id) {
			update_post_meta($post_id,"user_is_comment",true);
		}
	}
}
/* askme_pre_comment_approved */
function askme_pre_comment_approved($approved,$commentdata) {
	if (!is_user_logged_in && $approved != "spam") {
		$comment_unlogged = vpanel_options("comment_unlogged");
		$approved = ($comment_unlogged == "draft"?0:1);
	}
	return $approved;
}
add_filter('pre_comment_approved','askme_pre_comment_approved','99',2);
/* askme_approve_comment_callback */
add_action('transition_comment_status','askme_approve_comment_callback',10,3);
function askme_approve_comment_callback($new_status,$old_status,$comment) {
	global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
	$logo_email_template = vpanel_options("logo_email_template");
	if ($old_status != $new_status) {
		$get_post = get_post($comment->comment_post_ID);
		if ($new_status == 'approved') {
			$comment_approved_before = get_comment_meta($comment->comment_ID,'comment_approved_before',true);
			if ($comment_approved_before != "yes") {
				if ($get_post->post_type == "question") {
					$another_user_id = $get_post->post_author;
					$user_id = $comment->user_id;
					if ($user_id > 0) {
						askme_notifications_activities($user_id,"","",$comment->comment_post_ID,$comment->comment_ID,"approved_answer","notifications","","answer");
					}
					askme_notifications_add_answer($comment,$get_post);
				}else {
					if ($comment->user_id > 0) {
						askme_notifications_activities($comment->user_id,"","",$comment->comment_post_ID,$comment->comment_ID,"approved_comment","notifications");
					}
				}
			}
			update_comment_meta($comment->comment_ID,'comment_approved_before',"yes");
		}
	}
}
/* Notifications add question */
function askme_notifications_add_question($post_data,$question_username,$user_id,$not_user,$anonymously_user,$get_current_user_id) {
	global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3,$wpdb;
	$post_id = $post_data->ID;
	$send_email_new_question = vpanel_options("send_email_new_question");
	$the_author = 0;
	if ($not_user == 0) {
		$the_author = $question_username;
	}
	if ($user_id == "") {
		$private_question = get_post_meta($post_id,"private_question",true);
		if ($send_email_new_question == 1) {
			$logo_email_template = vpanel_options("logo_email_template");
			$user_group   = vpanel_options("send_email_question_groups");
			$capabilities = $wpdb->get_blog_prefix(1) . 'capabilities';
			$query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.ID,$wpdb->users.user_email,$wpdb->users.display_name,$wpdb->usermeta.meta_key,$wpdb->usermeta.meta_value FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) WHERE %s=1 AND $wpdb->usermeta.meta_key = 'received_email' AND ($wpdb->usermeta.meta_value = '1' OR $wpdb->usermeta.meta_value = 'on')",1);
			$users = $wpdb->get_results($query);
			if (isset($users) && is_array($users) && !empty($users)) {
				$send_text = ask_send_email(vpanel_options("email_new_questions"),"",$post_id);
				$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
				$email_title = vpanel_options("title_new_questions");
				$email_title = ($email_title != ""?$email_title:__("New question","vbegy"));
				foreach ($users as $key => $value) {
					$another_user_id = $value->ID;
					if (is_super_admin($another_user_id) && ($private_question == "on" || $private_question == 1) && (($another_user_id != $anonymously_user && $anonymously_user > 0) || ($another_user_id != $not_user && $not_user > 0))) {
						askme_notifications_activities($another_user_id,$not_user,$the_author,$post_id,"","add_question","notifications","","question");
					}else {
						$get_capabilities = get_user_meta($another_user_id,$capabilities,true);
						if ($not_user != $another_user_id && (empty($user_group) || (is_array($user_group) && is_array($get_capabilities) && isset($user_group[key($get_capabilities)]) && $user_group[key($get_capabilities)] == 1))) {
							$yes_private = ask_private($post_id,$not_user,$another_user_id);
							if ($yes_private == 1) {
								if ($another_user_id > 0 && $not_user != $another_user_id) {
									askme_notifications_activities($another_user_id,$not_user,$the_author,$post_id,"","add_question","notifications","","question");
								}
								
								sendEmail(vpanel_options("email_template"),get_bloginfo('name'),esc_attr($value->user_email),esc_attr($value->display_name),$email_title,$last_message_email);
							}
						}
					}
				}
			}
		}
	}
}
/* new_post */
function new_post() {
	global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3,$wpdb;
	$logo_email_template = vpanel_options("logo_email_template");
	if ($_POST) :
		$return = process_new_posts();
		if (is_wp_error($return)) :
   			echo '<div class="ask_error"><span><p>'.$return->get_error_message().'</p></span></div>';
   		else :
   			if (get_post_type($return) == "question") {
   				$get_post = get_post($return);
   				if (is_user_logged_in) {
   					$question_publish = vpanel_options("question_publish");
   				}else {
   					$question_publish = vpanel_options("question_publish_unlogged");
   				}
	   			$user_id = get_current_user_id();
	   			$get_question_user = get_post_meta($get_post->ID,"user_id",true);
	   			if ($question_publish == "draft" && !is_super_admin($user_id)) {
					if(!session_id()) session_start();
					$_SESSION['vbegy_session'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Added been successfully","vbegy").'</span><br>'.__("Has been added successfully, The question under review.","vbegy").'</p></div>';
					
					if ($user_id > 0) {
						askme_notifications_activities($user_id,"","","","","approved_question","activities","","question");
					}
					
					$send_email_draft_questions = vpanel_options("send_email_draft_questions");
					if ($send_email_draft_questions == 1) {
						$send_text = ask_send_email(vpanel_options("email_draft_questions"),"",$get_post->ID);
						$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
						$email_title = vpanel_options("title_new_draft_questions");
						$email_title = ($email_title != ""?$email_title:__("New question for review","vbegy"));
						sendEmail(vpanel_options("email_template"),get_bloginfo('name'),get_bloginfo("admin_email"),get_bloginfo('name'),$email_title,$last_message_email);
					}
					
					wp_redirect(esc_url(($get_question_user != ""?vpanel_get_user_url($get_question_user):home_url('/'))));
				}else {
					$anonymously_user = get_post_meta($get_post->ID,"anonymously_user",true);
					$question_username = get_post_meta($get_post->ID,"question_username",true);
					$not_user = ($get_post->post_author > 0?$get_post->post_author:0);
					askme_notifications_add_question($get_post,$question_username,$get_question_user,$not_user,$anonymously_user,$user_id);
					update_post_meta($return,'post_approved_before',"yes");
					if ($get_post->post_author != $get_question_user && $get_question_user > 0) {
						askme_notifications_activities($get_question_user,$get_post->post_author,"",$get_post->ID,"","add_question_user","notifications","","question");
					}
					if ($user_id > 0) {
						askme_notifications_activities($user_id,"","",$return,"","add_question","activities","","question");
					}
					if ($get_question_user != "") {
						$_SESSION['vbegy_session_user'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Added been successfully","vbegy").'</span><br>'.__("Has been added successfully, When the user answered will show the question.","vbegy").'</p></div>';
					}
					wp_redirect(($get_question_user != ""?vpanel_get_user_url($get_question_user):get_permalink($return)));
				}
			}else if (get_post_type($return) == "post") {
				if (is_user_logged_in) {
					$post_publish = vpanel_options("post_publish");
				}else {
					$post_publish = vpanel_options("post_publish_unlogged");
				}
				$user_id = get_current_user_id();
				if ($post_publish == "draft" && !is_super_admin($user_id)) {
					if(!session_id()) session_start();
					$_SESSION['vbegy_session_post'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Added been successfully","vbegy").'</span><br>'.__("Has been added successfully, The post under review.","vbegy").'</p></div>';
					
					if ($user_id > 0) {
						askme_notifications_activities($user_id,"","","","","approved_post","activities");
					}
					
					$send_email_draft_posts = vpanel_options("send_email_draft_posts");
					if ($send_email_draft_posts == 1) {
						$send_text = ask_send_email(vpanel_options("email_draft_posts"),"",$return);
						$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
						$email_title = vpanel_options("title_new_draft_posts");
						$email_title = ($email_title != ""?$email_title:__("New post for review","vbegy"));
						sendEmail(vpanel_options("email_template"),get_bloginfo('name'),get_bloginfo("admin_email"),get_bloginfo('name'),$email_title,$last_message_email);
					}
					
					wp_redirect(esc_url(home_url('/')));
				}else {
					if ($user_id > 0) {
						askme_notifications_activities($user_id,"","",$return,"","add_post","activities");
					}
					update_post_meta($return,'post_approved_before',"yes");
					wp_redirect(get_permalink($return));
				}
			}
			exit;
   		endif;
	endif;
}
add_action('new_post','new_post');
/* process_new_posts */
function process_new_posts() {
	global $posted;
	set_time_limit(0);
	$errors = new WP_Error();
	$posted = array();
	
	$post_type = (isset($_POST["post_type"]) && $_POST["post_type"] != ""?$_POST["post_type"]:"");
	$user_get_current_user_id = get_current_user_id();
	if ($post_type == "add_question") {
		$video_desc_active = vpanel_options("video_desc_active");
		$ask_question_no_register = vpanel_options("ask_question_no_register");
		$username_email_no_register = vpanel_options("username_email_no_register");
		$question_points_active = vpanel_options("question_points_active");
		$question_points = vpanel_options("question_points");
		$category_question = vpanel_options("category_question");
		$category_question_required = vpanel_options("category_question_required");
		$points = get_user_meta($user_get_current_user_id,"points",true);
		$points = ($points != ""?$points:0);
		
		$fields = array(
			'title','category','comment','question_poll','remember_answer','private_question','anonymously_question','question_tags','video_type','video_id','video_description','attachment','attachment_m','featured_image','ask_captcha','username','email','agree_terms','user_id'
		);
		
		$fields = apply_filters('askme_add_question_fields',$fields);
		
		foreach ($fields as $field) :
			if (isset($_POST[$field])) $posted[$field] = $_POST[$field]; else $posted[$field] = '';
		endforeach;
		
		$payment_group = vpanel_options("payment_group");
		$pay_ask = vpanel_options("pay_ask");
		$custom_permission = vpanel_options("custom_permission");
		$ask_question = vpanel_options("ask_question");
		if (is_user_logged_in) {
			$user_is_login = get_userdata($user_get_current_user_id);
			$user_login_group = key($user_is_login->caps);
			$roles = $user_is_login->allcaps;
			$_allow_to_ask = get_user_meta($user_get_current_user_id,$user_get_current_user_id."_allow_to_ask",true);
		}
		
		if (($custom_permission == 1 && is_user_logged_in && empty($roles["ask_question"])) || ($custom_permission == 1 && !is_user_logged_in && $ask_question != 1)) {
			$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Sorry, you do not have a permission to add a question.","vbegy"));
			if (!is_user_logged_in) {
				$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("You must login to ask question.","vbegy"));
			}
		}else if (!is_user_logged_in && $ask_question_no_register != 1) {
			$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("You must login to ask question.","vbegy"));
		}else {
			if (!is_user_logged_in && $pay_ask == 1) {
				$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("You must login to ask question.","vbegy"));
			}else {
				if (isset($_allow_to_ask) && (int)$_allow_to_ask < 1 && $pay_ask == 1 && $payment_group[$user_login_group] != 1) {
					$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("You need to pay first.","vbegy"));
				}
			}
		}
		
		if ($points < $question_points && $question_points_active == 1) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.sprintf(__("Sorry do not have the minimum points Please do answer questions,even gaining points (The minimum points = %s).","vbegy"),$question_points));
		
		if (!is_user_logged_in && $ask_question_no_register == 1 && $username_email_no_register == 1 && $user_get_current_user_id == 0) {
			if (empty($posted['username'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (username).","vbegy"));
			if (empty($posted['email'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (email).","vbegy"));
			if (!is_email($posted['email'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Please write correctly email.","vbegy"));
		}
		
		/* Validate Required Fields */
		$title_question = vpanel_options("title_question");
		if ($title_question == 1 && empty($posted['title'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (title).","vbegy"));
		if (empty($posted['user_id']) && ($category_question == 1 && $category_question_required == 1 && (empty($posted['category']) || $posted['category'] == '-1' || (is_array($posted['category']) && end($posted['category']) == '-1')))) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (category).","vbegy"));
		if (isset($posted['question_poll']) && $posted['question_poll'] == 1) {
			foreach($_POST['ask'] as $ask) {
				if (empty($ask['ask']) && count($_POST['ask']) < 2) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Please enter at least two values in poll.","vbegy"));
			}
		}
		
		$comment_question = "";
		if ($title_question != 1) {
			$comment_question = "required";
		}else {
			$comment_question = vpanel_options("comment_question");
			if ($comment_question == 1) {
				$comment_question = "required";
			}
		}
		
		if ($comment_question == "required" && empty($posted['comment'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (content).","vbegy"));
		if ($video_desc_active == 1 && $posted['video_description'] == 1 && empty($posted['video_id'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (Video ID).","vbegy"));
		
		$the_captcha = vpanel_options("the_captcha");
		$captcha_style = vpanel_options("captcha_style");
		$captcha_question = vpanel_options("captcha_question");
		$captcha_answer = vpanel_options("captcha_answer");
		$show_captcha_answer = vpanel_options("show_captcha_answer");
		if ($the_captcha == 1) {
			if (empty($posted["ask_captcha"])) {
				$errors->add('required-captcha',__("There are required fields (captcha).","vbegy"));
			}
			if ($captcha_style == "question_answer") {
				if ($captcha_answer != $posted["ask_captcha"]) {
					$errors->add('required-captcha-error',__('The captcha is incorrect, please try again.','vbegy'));
				}
			}else {
				if (isset($_SESSION["security_code"]) && $_SESSION["security_code"] != $posted["ask_captcha"]) {
					$errors->add('required-captcha-error',__('The captcha is incorrect, please try again.','vbegy'));
				}
			}
		}
		
		$terms_active = vpanel_options("terms_active");
		if ($terms_active == 1 && $posted['agree_terms'] != 1) {
			$errors->add('required-terms',__("There are required fields (Agree of the terms).","vbegy"));
		}
		
		do_action('askme_add_question_errors',$errors,$posted);
		
		if (sizeof($errors->errors)>0) return $errors;
		
		/* Create question */
		if (is_user_logged_in) {
			$question_publish = vpanel_options("question_publish");
		}else {
			$question_publish = vpanel_options("question_publish_unlogged");
		}
		
		$title_excerpt_type = vpanel_options("title_excerpt_type");
		$title_excerpt = vpanel_options("title_excerpt");
		if ($title_question != 1) {
			$question_title = excerpt_any($title_excerpt,$posted['comment'],$title_excerpt_type);
		}else {
			$question_title = $posted['title'];
		}
		
		$data = array(
			'post_content' => ask_kses_stip_wpautop($posted['comment']),
			'post_title'   => ask_kses_stip($question_title),
			'post_status'  => ($question_publish == "draft" && !is_super_admin($user_get_current_user_id)?"draft":"publish"),
			'post_author'  => ((!is_user_logged_in && $ask_question_no_register == 1) || $posted['anonymously_question']?0:$user_get_current_user_id),
			'post_type'	   => 'question',
		);
			
		$post_id = wp_insert_post($data);
			
		if ($post_id==0 || is_wp_error($post_id)) wp_die(__("Error in question.","vbegy"));
		
		if (empty($posted['user_id']) && $category_question == 1 && isset($posted['category']) && $posted['category']) {
			if (is_array($posted['category'])) {
				$cat_ids = array_map( 'intval', $posted['category'] );
				$cat_ids = array_unique( $cat_ids );
			}else {
				$cat_ids = array();
				$cat_ids[] = get_term_by('id',(is_array($posted['category'])?end($posted['category']):$posted['category']),ask_question_category)->slug;
			}
			if (sizeof($cat_ids)>0) :
				wp_set_object_terms($post_id,$cat_ids,ask_question_category);
			endif;
		}
		
		if ($posted['question_poll'])  {
			update_post_meta($post_id,'question_poll',$posted['question_poll']);
		}else {
			update_post_meta($post_id,'question_poll',2);
		}
	
		if (isset($_POST['ask'])) 
			update_post_meta($post_id,'ask',$_POST['ask']);
		
		if ($posted['remember_answer']) 
			update_post_meta($post_id,'remember_answer',$posted['remember_answer']);
		
		if ($posted['private_question']) {
			update_post_meta($post_id,'private_question',$posted['private_question']);
			update_post_meta($question_id,'private_question_author',((!is_user_logged_in && $ask_question_no_register == 1) || $posted['anonymously_question']?0:$user_get_current_user_id));
		}
		
		if ($posted['anonymously_question']) {
			update_post_meta($post_id,'anonymously_question',$posted['anonymously_question']);
			update_post_meta($post_id,'anonymously_user',(is_user_logged_in?$user_get_current_user_id:0));
		}
		
		if ($video_desc_active == 1) {
			if ($posted['video_description']) 
				update_post_meta($post_id,'video_description',$posted['video_description']);
			
			if ($posted['video_type']) 
				update_post_meta($post_id,'video_type',$posted['video_type']);
				
			if ($posted['video_id']) 
				update_post_meta($post_id,'video_id',$posted['video_id']);	
		}
		
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		
		if (isset($_FILES['attachment_m'])) {
			$files = $_FILES['attachment_m'];
			if (isset($files) && $files) {
				foreach ($files['name'] as $key => $value) {
					if ($files['name'][$key]) {
						$file = array(
							'name'	 => $files['name'][$key]["file_url"],
							'type'	 => $files['type'][$key]["file_url"],
							'tmp_name' => $files['tmp_name'][$key]["file_url"],
							'error'	=> $files['error'][$key]["file_url"],
							'size'	 => $files['size'][$key]["file_url"]
						);
						if ($files['error'][$key]["file_url"] != 0) {
							unset($files['name'][$key]);
							unset($files['type'][$key]);
							unset($files['tmp_name'][$key]);
							unset($files['error'][$key]);
							unset($files['size'][$key]);
						}
					}
				}
			}
			
			if (isset($files) && $files) {
				foreach ($files['name'] as $key => $value) {
					if ($files['name'][$key]) {
						$file = array(
							'name'	 => $files['name'][$key]["file_url"],
							'type'	 => $files['type'][$key]["file_url"],
							'tmp_name' => $files['tmp_name'][$key]["file_url"],
							'error'	=> $files['error'][$key]["file_url"],
							'size'	 => $files['size'][$key]["file_url"]
						);
						$attachment = wp_handle_upload($file,array('test_form'=>false),current_time('mysql'));
						if (!isset($attachment['error']) && $attachment) :
							//$errors->add('upload-error',__("Attachment Error: ","vbegy") . $attachment['error']);
							$attachment_data = array(
								'post_mime_type' => $attachment['type'],
								'post_title'	 => preg_replace('/\.[^.]+$/','',basename($attachment['file'])),
								'post_content'   => '',
								'post_status'	 => 'inherit',
								'post_author'    => ((!is_user_logged_in && $ask_question_no_register == 1) || $posted['anonymously_question']?0:$user_get_current_user_id),
							);
							$attachment_id = wp_insert_attachment($attachment_data,$attachment['file'],$post_id);
							$attachment_metadata = wp_generate_attachment_metadata($attachment_id,$attachment['file']);
							wp_update_attachment_metadata($attachment_id,$attachment_metadata);
							$attachment_m_array[] = array("added_file" => $attachment_id);
						endif;
					}
					if (get_post_meta($post_id,'attachment_m')) {
						delete_post_meta($post_id,'attachment_m');
					}
					if (isset($attachment_m_array)) {
						add_post_meta($post_id,'attachment_m',$attachment_m_array);
					}
				}
			}
		}
		
		/* Featured image */
		
		$featured_image_question = vpanel_options('featured_image_question');
		if ($featured_image_question == 1) {
			$featured_image = '';
			
			if (isset($_FILES['featured_image']) && !empty($_FILES['featured_image']['name'])) :
				$types = array("image/jpeg","image/bmp","image/jpg","image/png","image/gif","image/tiff","image/ico");
				if (!in_array($_FILES['featured_image']['type'],$types)) :
					$errors->add('upload-error',__("Attachment Error, Please upload image only.","vbegy"));
					return $errors;
				endif;
				
				$featured_image = wp_handle_upload($_FILES['featured_image'],array('test_form'=>false),current_time('mysql'));
				
				if (isset($featured_image['error'])) :
					$errors->add('upload-error',__("Attachment Error: ","vbegy") . $featured_image['error']);
					return $errors;
				endif;
			endif;
			
			if ($featured_image) :
				$featured_image_data = array(
					'post_mime_type' => $featured_image['type'],
					'post_title'	 => preg_replace('/\.[^.]+$/','',basename($featured_image['file'])),
					'post_content'   => '',
					'post_status'	 => 'inherit',
					'post_author'    => ((!is_user_logged_in && $ask_question_no_register == 1) || $posted['anonymously_question']?0:$user_get_current_user_id),
				);
				$featured_image_id = wp_insert_attachment($featured_image_data,$featured_image['file'],$post_id);
				$featured_image_metadata = wp_generate_attachment_metadata($featured_image_id,$featured_image['file']);
				wp_update_attachment_metadata($featured_image_id, $featured_image_metadata);
				$set_post_thumbnail = set_post_thumbnail($post_id,$featured_image_id);
			endif;
		}
		
		/* Tags */
		
		if (empty($posted['user_id']) && isset($posted['question_tags']) && $posted['question_tags']) :
			$tags = explode(',',trim(stripslashes($posted['question_tags'])));
			$tags = array_map('strtolower',$tags);
			$tags = array_map('trim',$tags);
	
			if (sizeof($tags)>0) :
				wp_set_object_terms($post_id,$tags,'question_tags');
			endif;
		endif;
		
		if (!is_user_logged_in && $ask_question_no_register == 1 && $user_get_current_user_id == 0) {
			if ($username_email_no_register == 1) {
				$question_username = sanitize_text_field($posted['username']);
				$question_email = sanitize_text_field($posted['email']);
				update_post_meta($post_id,'question_username',$question_username);
				update_post_meta($post_id,'question_email',$question_email);
			}else {
				update_post_meta($post_id,'question_no_username',"no_user");
			}
		}else {
			$user_id = $user_get_current_user_id;
			
			$pay_ask = vpanel_options("pay_ask");
			if ($pay_ask == 1) {
				$_allow_to_ask = get_user_meta($user_id,$user_id."_allow_to_ask",true);
				if ($_allow_to_ask == "") {
					$_allow_to_ask = 0;
				}
				$_allow_to_ask--;
				update_user_meta($user_id,$user_id."_allow_to_ask",$_allow_to_ask);
				
				$_coupon = get_user_meta($user_id,$user_id."_coupon",true);
				$_coupon_value = get_user_meta($user_id,$user_id."_coupon_value",true);
				if (isset($_coupon) && $_coupon != "") {
					update_post_meta($post_id,'_coupon',$_coupon);
					delete_user_meta($user_id,$user_id."_coupon");
				}
				if (isset($_coupon_value) && $_coupon_value != "") {
					update_post_meta($post_id,'_coupon_value',$_coupon_value);
					delete_user_meta($user_id,$user_id."_coupon_value");
				}
				
				$_paid_question = get_user_meta($user_id,'_paid_question',true);
				if (isset($_paid_question) && $_paid_question != "") {
					update_post_meta($post_id,'_paid_question',$_paid_question);
					delete_user_meta($user_id,'_paid_question');
				}
				
				$item_transaction = get_user_meta($user_id,'item_transaction',true);
				if (isset($item_transaction) && $item_transaction != "") {
					update_post_meta($post_id,'item_transaction',$item_transaction);
					delete_user_meta($user_id,'item_transaction');
				}
				
				$paypal_sandbox = get_user_meta($user_id,'paypal_sandbox',true);
				if (isset($paypal_sandbox) && $paypal_sandbox != "") {
					update_post_meta($post_id,'paypal_sandbox',$paypal_sandbox);
					delete_user_meta($user_id,'paypal_sandbox');
				}
				
			}
			
			$point_add_question = vpanel_options("point_add_question");
			$active_points = vpanel_options("active_points");
			if ($point_add_question > 0 && $active_points == 1) {
				$current_user = get_user_by("id",$user_id);
				$_points = get_user_meta($user_id,$current_user->user_login."_points",true);
				$_points++;
			
				update_user_meta($user_id,$current_user->user_login."_points",$_points);
				add_user_meta($user_id,$current_user->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_add_question,"+","add_question",$post_id));
			
				$points_user = get_user_meta($user_id,"points",true);
				$last_points = $points_user+$point_add_question;
				update_user_meta($user_id,"points",$last_points);
			}
			
			if ($points && $question_points_active == 1) {
				$current_user = get_user_by("id",$user_id);
				$_points = get_user_meta($user_id,$current_user->user_login."_points",true);
				$_points++;
			
				update_user_meta($user_id,$current_user->user_login."_points",$_points);
				add_user_meta($user_id,$current_user->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$question_points,"-","question_point",$post_id));
			
				$points_user = get_user_meta($user_id,"points",true);
				$last_points = $points_user-$question_points;
				update_user_meta($user_id,"points",$last_points);
				
				update_post_meta($post_id,"point_back","yes");
				update_post_meta($post_id,"what_point",$question_points);
			}			
		}
		
		if ($posted['user_id'] && $posted['user_id'] != "") {
			update_post_meta($post_id,'user_id',(int)$posted['user_id']);
		}
		
		/* The default meta */
		update_post_meta($post_id,"vbegy_layout","default");
		update_post_meta($post_id,"vbegy_home_template","default");
		update_post_meta($post_id,"vbegy_site_skin_l","default");
		update_post_meta($post_id,"vbegy_skin","default");
		update_post_meta($post_id,"vbegy_sidebar","default");
		update_post_meta($post_id,"post_from_front","from_front");
		do_action('new_questions',$post_id,$posted);
	}else if ($post_type == "add_post") {
		$add_post_no_register = vpanel_options("add_post_no_register");
		$category_post = vpanel_options("category_post");
		$category_post_required = vpanel_options("category_post_required");
		$category_post = $category_post_required = 1;
		
		$fields = array(
			'title','comment','category','post_tag','attachment','ask_captcha','username','email'
		);
		
		foreach ($fields as $field) :
			if (isset($_POST[$field])) $posted[$field] = $_POST[$field]; else $posted[$field] = '';
		endforeach;
		
		if (!is_user_logged_in && $add_post_no_register == 1 && $user_get_current_user_id == 0) {
			if (empty($posted['username'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (username).","vbegy"));
			if (empty($posted['email'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (email).","vbegy"));
			if (!is_email($posted['email'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Please write correctly email.","vbegy"));
		}
		
		/* Validate Required Fields */
		if (empty($posted['title'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (title).","vbegy"));
		if ($category_post == 1 && $category_post_required == 1 && (empty($posted['category']) || $posted['category'] == '-1')) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (category).","vbegy"));
		if (vpanel_options("content_post") == 1) {
			if (empty($posted['comment'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (details).","vbegy"));
		}
		
		$the_captcha_post = vpanel_options("the_captcha_post");
		$captcha_style = vpanel_options("captcha_style");
		$captcha_question = vpanel_options("captcha_question");
		$captcha_answer = vpanel_options("captcha_answer");
		$show_captcha_answer = vpanel_options("show_captcha_answer");
		if ($the_captcha_post == 1) {
			if (empty($posted["ask_captcha"])) {
				$errors->add('required-captcha',__("There are required fields (captcha).","vbegy"));
			}
			if ($captcha_style == "question_answer") {
				if ($captcha_answer != $posted["ask_captcha"]) {
					$errors->add('required-captcha-error',__('The captcha is incorrect, please try again.','vbegy'));
				}
			}else {
				if (isset($_SESSION["security_code"]) && $_SESSION["security_code"] != $posted["ask_captcha"]) {
					$errors->add('required-captcha-error',__('The captcha is incorrect, please try again.','vbegy'));
				}
			}
		}
		
		if (sizeof($errors->errors)>0) return $errors;
		
		/* Create post */
		if (is_user_logged_in) {
			$post_publish = vpanel_options("post_publish");
		}else {
			$post_publish = vpanel_options("post_publish_unlogged");
		}
		$data = array(
			'post_content' => ask_kses_stip_wpautop($posted['comment']),
			'post_title'   => ask_kses_stip($posted['title']),
			'post_status'  => ($post_publish == "draft" && !is_super_admin($user_get_current_user_id)?"draft":"publish"),
			'post_author'  => (!is_user_logged_in && $add_post_no_register == 1?0:$user_get_current_user_id),
			'post_type'	   => 'post',
		);
			
		$post_id = wp_insert_post($data);
			
		if ($post_id==0 || is_wp_error($post_id)) wp_die(__("Error in post.","vbegy"));
		
		if ($category_post == 1) {
			$terms = array();
			if ($posted['category']) $terms[] = get_term_by('id',(is_array($posted['category'])?end($posted['category']):$posted['category']),'category')->slug;
			if (sizeof($terms)>0) wp_set_object_terms($post_id,$terms,'category');
		}
	
		$attachment = '';
	
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
			
		if(isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) :
				
			$attachment = wp_handle_upload($_FILES['attachment'],array('test_form'=>false),current_time('mysql'));
						
			if (isset($attachment['error'])) :
				$errors->add('upload-error',__("Attachment Error: ","vbegy") . $attachment['error']);
				
				return $errors;
			endif;
			
		endif;
		if ($attachment) :
			$attachment_data = array(
				'post_mime_type' => $attachment['type'],
				'post_title'	 => preg_replace('/\.[^.]+$/','',basename($attachment['file'])),
				'post_content'   => '',
				'post_status'	 => 'inherit',
				'post_author'	 => (!is_user_logged_in && $add_post_no_register == 1?0:$user_get_current_user_id)
			);
			$attachment_id = wp_insert_attachment($attachment_data,$attachment['file'],$post_id);
			$attachment_metadata = wp_generate_attachment_metadata($attachment_id,$attachment['file']);
			wp_update_attachment_metadata($attachment_id, $attachment_metadata);
			$set_post_thumbnail = set_post_thumbnail($post_id,$attachment_id);
			if (!$set_post_thumbnail) {
				add_post_meta($post_id,'added_file',$attachment_id,true);
			}
		endif;
		
		/* Tags */
		
		if (isset($posted['post_tag']) && $posted['post_tag']) :
			$tags = explode(',',trim(stripslashes($posted['post_tag'])));
			$tags = array_map('strtolower',$tags);
			$tags = array_map('trim',$tags);
	
			if (sizeof($tags)>0) :
				wp_set_object_terms($post_id,$tags,'post_tag');
			endif;
		endif;
		
		if (!is_user_logged_in && $add_post_no_register == 1 && $user_get_current_user_id == 0) {
			$post_username = sanitize_text_field($posted['username']);
			$post_email = sanitize_text_field($posted['email']);
			update_post_meta($post_id,'post_username',$post_username);
			update_post_meta($post_id,'post_email',$post_email);
		}else {
			$user_id = $user_get_current_user_id;
			$point_add_post = vpanel_options("point_add_post");
			$active_points = vpanel_options("active_points");
			if ($point_add_post > 0 && $active_points == 1) {
				$current_user = get_user_by("id",$user_id);
				$_points = get_user_meta($user_id,$current_user->user_login."_points",true);
				$_points++;
			
				update_user_meta($user_id,$current_user->user_login."_points",$_points);
				add_user_meta($user_id,$current_user->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_add_post,"+","add_post",$post_id));
			
				$points_user = get_user_meta($user_id,"points",true);
				$last_points = $points_user+$point_add_post;
				update_user_meta($user_id,"points",$last_points);
			}
		}
		
		/* The default meta */
		update_post_meta($post_id,"vbegy_layout","default");
		update_post_meta($post_id,"vbegy_home_template","default");
		update_post_meta($post_id,"vbegy_site_skin_l","default");
		update_post_meta($post_id,"vbegy_skin","default");
		update_post_meta($post_id,"vbegy_sidebar","default");
		update_post_meta($post_id,"post_from_front","from_front");
		do_action('new_posts',$post_id);
	}
	if ($post_type == "add_question" || $post_type == "add_post") {
		/* Successful */
		return $post_id;
	}
}
/* askme_before_delete_post */
add_action('before_delete_post','askme_before_delete_post');
function askme_before_delete_post($postid) {
	$post_type = get_post_type($postid);
	if (isset($postid) && $postid != "" && ($post_type == "post" || $post_type == "question")) { 
		$favorites_questions = get_post_meta($postid,"favorites_questions",true);
		if (isset($favorites_questions) && is_array($favorites_questions) && count($favorites_questions) >= 1) {
			foreach ($favorites_questions as $user_id) {
				$user_login_id2 = get_user_by("id",$user_id);
				$favorites_questions_user = get_user_meta($user_id,$user_login_id2->user_login."_favorites",true);
				$remove_favorites_questions = remove_item_by_value($favorites_questions_user,$postid);
				update_user_meta($user_id,$user_login_id2->user_login."_favorites",$remove_favorites_questions);
			}
		}
		
		$following_questions = get_post_meta($postid,"following_questions",true);
		if (isset($following_questions) && is_array($following_questions) && count($following_questions) >= 1) {
			foreach ($following_questions as $user_id) {
				$following_questions_user = get_user_meta($user_id,"following_questions",true);
				$remove_following_questions = remove_item_by_value($following_questions_user,$postid);
				update_user_meta($user_id,"following_questions",$remove_following_questions);
			}
		}
	}
}
/* askme_before_delete_comment */
add_action('delete_comment','askme_before_delete_comment');
function askme_before_delete_comment($comment_id) {
	$remove_best_answer_stats = vpanel_options("remove_best_answer_stats");
	$active_points = vpanel_options("active_points");
	if ($remove_best_answer_stats == 1) {
		$best_answer_comment = get_comment_meta($comment_id,"best_answer_comment",true);
		$get_comment = get_comment($comment_id);
		$user_id = $get_comment->user_id;
		if ($user_id > 0 && $active_points == 1) {
			$point_best_answer = vpanel_options("point_best_answer");
			$point_best_answer = ($point_best_answer != ""?$point_best_answer:5);
			$point_add_comment = vpanel_options("point_add_comment");
			$point_add_comment = ($point_add_comment != ""?$point_add_comment:2);
			
			$user_name = get_user_by("id",$user_id);
			$_points = get_user_meta($user_id,$user_name->user_login."_points",true);
			$_points++;
			update_user_meta($user_id,$user_name->user_login."_points",$_points);
			add_user_meta($user_id,$user_name->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_add_comment,"-","delete_answer"));
			$points_user = get_user_meta($user_id,"points",true);
			update_user_meta($user_id,"points",$points_user-$point_add_comment);
		}
		
		if (isset($best_answer_comment) && isset($comment_id) && $best_answer_comment == "best_answer_comment") {
			$best_answer_option = get_option("best_answer_option");
			$best_answer_option--;
			if ($best_answer_option < 0) {
				$best_answer_option = 0;
			}
			update_option("best_answer_option",$best_answer_option);
			
			$the_best_answer_user = get_user_meta($user_id,"the_best_answer",true);
			$the_best_answer_user--;
			if ($the_best_answer_user < 0) {
				$the_best_answer_user = 0;
			}
			update_user_meta($user_id,"the_best_answer",$the_best_answer_user);
			
			if ($user_id > 0 && $active_points == 1) {
				$_points++;
				update_user_meta($user_id,$user_name->user_login."_points",$_points);
				add_user_meta($user_id,$user_name->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_best_answer,"-","delete_best_answer"));
				$points_user = get_user_meta($user_id,"points",true);
				update_user_meta($user_id,"points",$points_user-$point_best_answer);
			}
			
			$point_back_option = vpanel_options("point_back");
			$user_author = get_post_field('post_author',$get_comment->comment_post_ID);
			if ($point_back_option == 1 && $active_points == 1 && $user_id != $user_author) {
				$point_back_number = vpanel_options("point_back_number");
				$point_back = get_post_meta($post_id,"point_back",true);
				$what_point = get_post_meta($post_id,"what_point",true);
				
				if ($point_back_number > 0) {
					$what_point = $point_back_number;
				}
				
				if ($point_back == "yes" && $user_author > 0) {
					$user_name2 = get_user_by("id",$user_author);
					$_points = get_user_meta($user_author,$user_name2->user_login."_points",true);
					$_points++;
					update_user_meta($user_author,$user_name2->user_login."_points",$_points);
					add_user_meta($user_author,$user_name2->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),($what_point != ""?$what_point:vpanel_options("question_points")),"-","point_removed"));
					$points_user = get_user_meta($user_author,"points",true);
					update_user_meta($user_author,"points",$points_user-($what_point != ""?$what_point:vpanel_options("question_points")));
				}
				
				if ($user_author > 0) {
					askme_notifications_activities($user_author,"","","","","point_removed","notifications");
				}
			}
		}
	}
}
/* askme_save_post */
add_action('save_post','askme_save_post',10,3);
function askme_save_post($post_id) {
	if (is_admin()) {
		$post_data = get_post($post_id);
		if ($post_data->post_type == "question" || $post_data->post_type == "post" || $post_data->post_type == "message") {
			if ($post_data->post_type == "question") {
				$question_username = get_post_meta($post_id,'question_username',true);
				$question_email = get_post_meta($post_id,'question_email',true);
				$anonymously_user = get_post_meta($post_id,'anonymously_user',true);
				if ($question_username == "") {
					$question_no_username = get_post_meta($post_id,'question_no_username',true);
				}
			}
			if ($post_data->post_type == "post") {
				$post_username = get_post_meta($post_id,'post_username',true);
				$post_email = get_post_meta($post_id,'post_email',true);
			}
			if ($post_data->post_type == "message") {
				$message_username = get_post_meta($post_id,'message_username',true);
				$message_email = get_post_meta($post_id,'message_email',true);
			}
			
			if ((isset($anonymously_user) && $anonymously_user != "") || (isset($question_no_username) && $question_no_username == "no_user") || (isset($question_username) && $question_username != "" && isset($question_email) && $question_email != "") || (isset($post_username) && $post_username != "" && isset($post_email) && $post_email != "") || (isset($message_username) && $message_username != "" && isset($message_email) && $message_email != "")) {
				$data = array(
					'ID' => $post_id,
					'post_author' => 0,
				);
				
				if ($old_status == $new_status) {
					remove_action('save_post', 'askme_save_post');
					$post_id = wp_update_post($data);
					add_action('save_post', 'askme_save_post');
				}
			}
		}
	}
}
/* run_on_update_post */
add_action('transition_post_status','run_on_update_post',10,3);
function run_on_update_post($new_status,$old_status,$post) {
	if (is_admin()) {
		if ($post->post_type == "question" || $post->post_type == "post" || $post->post_type == "message") {
			if ($post->post_type == "question") {
				$user_id = get_post_meta($post->ID,"user_id",true);
				$anonymously_user = get_post_meta($post->ID,"anonymously_user",true);
				$question_username = get_post_meta($post->ID,'question_username',true);
				$question_email = get_post_meta($post->ID,'question_email',true);
				if ($question_username == "") {
					$question_no_username = get_post_meta($post->ID,'question_no_username',true);
				}
			}
			if ($post->post_type == "post") {
				$post_username = get_post_meta($post->ID,'post_username',true);
				$post_email = get_post_meta($post->ID,'post_email',true);
			}
			if ($post->post_type == "message") {
				$message_username = get_post_meta($post->ID,'message_username',true);
				$message_email = get_post_meta($post->ID,'message_email',true);
			}
			
			if ((isset($anonymously_user) && $anonymously_user > 0) || (isset($question_no_username) && $question_no_username == "no_user") || (isset($question_username) && $question_username != "" && isset($question_email) && $question_email != "") || (isset($post_username) && $post_username != "" && isset($post_email) && $post_email != "") || (isset($message_username) && $message_username != "" && isset($message_email) && $message_email != "")) {
				$not_user = 0;
			}else {
				$not_user = $post->post_author;
			}
		}
		
		if ($old_status != $new_status) {
			if ($post->post_type == "question" || $post->post_type == "post" || $post->post_type == "message") {
				$post_from_front = get_post_meta($post->ID,'post_from_front',true);
				$post_approved_before = get_post_meta($post->ID,'post_approved_before',true);
			}
			if ('publish' == $new_status && $post->post_type == "message") {
				global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
				if ($post_approved_before != "yes") {
					update_post_meta($post->ID,'post_approved_before',"yes");
					$get_message_user = get_post_meta($post->ID,'message_user_id',true);
					$send_email_message = vpanel_options("send_email_message");
					if ($post->post_author != $get_message_user && $get_message_user > 0) {
						askme_notifications_activities($get_message_user,$post->post_author,($post->post_author == 0?$get_message_user:""),"","","add_message_user","notifications","","message");
					}
					if ($not_user > 0) {
						askme_notifications_activities($not_user,$get_message_user,"","","","add_message","activities","","message");
					}
					
					if ($send_email_message == 1) {
						$send_text = ask_send_email(vpanel_options("email_new_message"),"",$post->ID);
						$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
						$user = get_userdata($get_message_user);
						$email_title = vpanel_options("title_new_message");
						$email_title = ($email_title != ""?$email_title:__("New message","vbegy"));
						sendEmail(vpanel_options("email_template"),get_bloginfo('name'),esc_attr($user->user_email),esc_attr($user->display_name),$email_title,$last_message_email);
					}
				}
			}
			if ('publish' == $new_status && isset($post_from_front) && $post_from_front == "from_front" && ($post->post_type == "question" || $post->post_type == "post")) {
				global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3,$wpdb;
				$logo_email_template = vpanel_options("logo_email_template");
				if ($post_approved_before != "yes") {
					update_post_meta($post->ID,'post_approved_before',"yes");
					
					if ($not_user > 0 || $anonymously_user > 0) {
						if ($post->post_type == "question") {
							askme_notifications_activities(($anonymously_user > 0?$anonymously_user:$not_user),"","",$post->ID,"","approved_question","notifications","","question");
							if ($post->post_author != $user_id && $user_id > 0) {
								askme_notifications_activities($user_id,($anonymously_user > 0?0:$not_user),"",$post->ID,"","add_question_user","notifications","","question");
							}
						}else if ($not_user > 0) {
							askme_notifications_activities($not_user,"","",$post->ID,"","approved_post","notifications");
						}
					}
					
					$send_email_new_question = vpanel_options("send_email_new_question");
					if ($old_status == "draft" && $post->post_type == "question") {
						$user_get_current_user_id = get_current_user_id();
						askme_notifications_add_question($post,$question_username,$user_id,$not_user,$anonymously_user,$user_get_current_user_id);
					}
				}
				update_post_meta($post->ID,'post_approved_before',"yes");
			}
		}
	}
}
/* edit_question */
function edit_question() {
	if ($_POST) :
		$return = process_edit_questions();
		if (is_wp_error($return)) :
   			echo '<div class="ask_error"><span><p>'.$return->get_error_message().'</p></span></div>';
   		else :
   			if(!session_id()) session_start();
   			$question_approved = vpanel_options("question_approved");
			if ($question_approved == 1 || is_super_admin(get_current_user_id())) {
				$_SESSION['vbegy_session_e'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Edited been successfully","vbegy").'</span><br>'.__("Has been edited successfully.","vbegy").'</p></div>';
				wp_redirect(get_permalink($return));
			}else {
				$_SESSION['vbegy_session_e'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Edited been successfully","vbegy").'</span><br>'.__("Has been edited successfully, The question under review.","vbegy").'</p></div>';
				wp_redirect(esc_url(home_url('/')));
			}
			exit;
   		endif;
	endif;
}
add_action('edit_question','edit_question');
/* process_edit_questions */
function process_edit_questions() {
	global $posted;
	set_time_limit(0);
	$errors = new WP_Error();
	$posted = array();
	$video_desc_active = vpanel_options("video_desc_active");
	$category_question = vpanel_options("category_question");
	$category_question_required = vpanel_options("category_question_required");
	
	$fields = array(
		'ID','title','comment','category','question_poll','remember_answer','private_question','anonymously_question','question_tags','video_type','video_id','video_description','featured_image'
	);
	
	$fields = apply_filters('askme_edit_question_fields',$fields);
	
	foreach ($fields as $field) :
		if (isset($_POST[$field])) $posted[$field] = $_POST[$field]; else $posted[$field] = '';
	endforeach;
	/* Validate Required Fields */
	
	$get_question = (isset($posted['ID'])?(int)$posted['ID']:0);
	$get_question_user_id = get_post_meta($get_question,"user_id",true);
	$get_post_q = get_post($get_question);
	if (isset($get_question) && $get_question != 0 && $get_post_q && get_post_type($get_question) == "question") {
		$user_login_id_l = get_user_by("id",$get_post_q->post_author);
		if ($user_login_id_l->ID != get_current_user_id() && !is_super_admin(get_current_user_id())) {
			$errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Sorry you can't edit this question.","vbegy"));
		}
	}else {
		$errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Sorry no question select or not found.","vbegy"));
	}
	
	$title_question = vpanel_options("title_question");
	if ($title_question == 1 && empty($posted['title'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (title).","vbegy"));
	if (empty($get_question_user_id) && ($category_question == 1 && $category_question_required == 1 && (empty($posted['category']) || $posted['category'] == '-1' || (is_array($posted['category']) && end($posted['category']) == '-1')))) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (category).","vbegy"));
	if (isset($posted['question_poll']) && $posted['question_poll'] == 1) {
		foreach($_POST['ask'] as $ask) {
			if (empty($ask['ask']) && count($_POST['ask']) < 2) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Please enter at least two values in poll.","vbegy"));
		}
	}
	
	$comment_question = "";
	if ($title_question != 1) {
		$comment_question = "required";
	}else {
		$comment_question = vpanel_options("comment_question");
		if ($comment_question == 1) {
			$comment_question = "required";
		}
	}
	
	if ($comment_question == "required" && empty($posted['comment'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (content).","vbegy"));
	if ($video_desc_active == 1 && $posted['video_description'] == 1 && empty($posted['video_id'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (Video ID).","vbegy"));
	
	do_action('askme_edit_question_errors',$errors,$posted);
	
	if (sizeof($errors->errors)>0) return $errors;
	
	$question_id = $get_question;
	
	$question_approved = vpanel_options("question_approved");
	
	/* Edit question */
	$post_name = array();
	$change_question_url = vpanel_options("change_question_url");
	if ($change_question_url == 1) {
		$post_name = array('post_name' => sanitize_text_field($posted['title']));
	}
	
	$title_excerpt_type = vpanel_options("title_excerpt_type");
	$title_excerpt = vpanel_options("title_excerpt");
	if ($title_question != 1) {
		$question_title = excerpt_any($title_excerpt,$posted['comment'],$title_excerpt_type);
	}else {
		$question_title = $posted['title'];
	}
	
	$data = array(
		'ID'		   => (int)sanitize_text_field($question_id),
		'post_content' => ask_kses_stip_wpautop($posted['comment']),
		'post_title'   => ask_kses_stip($question_title),
		'post_status'  => ($question_approved == 1 || is_super_admin(get_current_user_id())?"publish":"draft"),
		'post_author'  => ($posted['anonymously_question']?0:$get_post_q->post_author),
	);
	
	wp_update_post(array_merge($post_name,$data));
	
	if (empty($get_question_user_id) && $category_question == 1 && isset($posted['category']) && $posted['category']) {
		if (is_array($posted['category'])) {
			$cat_ids = array_map( 'intval', $posted['category'] );
			$cat_ids = array_unique( $cat_ids );
		}else {
			$cat_ids = array();
			$cat_ids[] = get_term_by('id',(is_array($posted['category'])?end($posted['category']):$posted['category']),ask_question_category)->slug;
		}
		if (sizeof($cat_ids)>0) :
			wp_set_object_terms($question_id,$cat_ids,ask_question_category);
		endif;
	}

	if ($posted['question_poll'] && $posted['question_poll'] != "")  {
		update_post_meta($question_id,'question_poll',$posted['question_poll']);
	}else {
		update_post_meta($question_id,'question_poll',2);
	}

	if (isset($_POST['ask'])) 
		update_post_meta($question_id,'ask',$_POST['ask']);
	
	if ($posted['remember_answer'] && $posted['remember_answer'] != "") {
		update_post_meta($question_id,'remember_answer',$posted['remember_answer']);
	}else {
		delete_post_meta($question_id,'remember_answer');
	}
	
	if ($posted['private_question'] && $posted['private_question'] != "") {
		update_post_meta($question_id,'private_question',$posted['private_question']);
		$anonymously_user = get_post_meta($question_id,'anonymously_user',true);
		update_post_meta($question_id,'private_question_author',($anonymously_user > 0?$anonymously_user:$get_post_q->post_author));
	}else {
		delete_post_meta($question_id,'private_question');
		delete_post_meta($question_id,'private_question_author');
	}
	
	if ($video_desc_active == 1) {
		if ($posted['video_description'] && $posted['video_description'] != "") {
			update_post_meta($question_id,'video_description',$posted['video_description']);
		}else {
			delete_post_meta($question_id,'video_description');
		}
		
		if ($posted['video_type']) 
			update_post_meta($question_id,'video_type',$posted['video_type']);
			
		if ($posted['video_id']) 
			update_post_meta($question_id,'video_id',$posted['video_id']);	
	}
	
	/* Featured image */
	
	$featured_image_question = vpanel_options('featured_image_question');
	if ($featured_image_question == 1) {
		$featured_image = '';
		
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		
		if(isset($_FILES['featured_image']) && !empty($_FILES['featured_image']['name'])) :
			$types = array("image/jpeg","image/bmp","image/jpg","image/png","image/gif","image/tiff","image/ico");
			if (!in_array($_FILES['featured_image']['type'],$types)) :
				$errors->add('upload-error',__("Attachment Error, Please upload image only.","vbegy"));
				return $errors;
			endif;
			
			$featured_image = wp_handle_upload($_FILES['featured_image'],array('test_form'=>false),current_time('mysql'));
			
			if (isset($featured_image['error'])) :
				$errors->add('upload-error',__("Attachment Error: ","vbegy") . $featured_image['error']);
				return $errors;
			endif;
			
		endif;
		if ($featured_image) :
			$ask_question_no_register = vpanel_options("ask_question_no_register");
			$featured_image_data = array(
				'post_mime_type' => $featured_image['type'],
				'post_title'     => preg_replace('/\.[^.]+$/','',basename($featured_image['file'])),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'post_author'    => (!is_user_logged_in && $ask_question_no_register == 1?0:get_current_user_id())
			);
			$featured_image_id = wp_insert_attachment($featured_image_data,$featured_image['file'],$question_id);
			$featured_image_metadata = wp_generate_attachment_metadata($featured_image_id,$featured_image['file']);
			wp_update_attachment_metadata($featured_image_id, $featured_image_metadata);
			set_post_thumbnail($question_id,$featured_image_id);
		endif;
	}
	
	/* Tags */
	
	if (empty($get_question_user_id)) :
		if (isset($posted['question_tags']) && $posted['question_tags']) {
			$tags = explode(',',trim(stripslashes($posted['question_tags'])));
			$tags = array_map('strtolower',$tags);
			$tags = array_map('trim',$tags);
	
			if (sizeof($tags)>0) :
				wp_set_object_terms($question_id,$tags,'question_tags');
			endif;
		}else {
			wp_set_object_terms($question_id,array(),'question_tags');
		}
	endif;
	
	$post_id = $question_id;
	
	do_action('edit_questions',$question_id,$posted);
	
	/* Successful */
	return $question_id;
}
/* vpanel_edit_comment */
function vpanel_edit_comment() {
	if ($_POST) :
		$return = process_edit_comments();
		if (is_wp_error($return)) :
   			echo '<div class="ask_error"><span><p>'.$return->get_error_message().'</p></span></div>';
   		else :
   			if(!session_id()) session_start();
   			$comment_approved = vpanel_options("comment_approved");
   			if ($comment_approved == 1 || is_super_admin(get_current_user_id())) {
   				$_SESSION['vbegy_session_comment'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Edited been successfully","vbegy").'</span><br>'.__("Has been edited successfully.","vbegy").'</p></div>';
   			}else {
   				$_SESSION['vbegy_session_comment'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Edited been successfully","vbegy").'</span><br>'.__("Has been added successfully, The comment under review.","vbegy").'</p></div>';
   			}
			wp_redirect(get_comment_link($return));
			exit;
   		endif;
	endif;
}
add_action('vpanel_edit_comment','vpanel_edit_comment');
/* process_edit_comments */
function process_edit_comments() {
	global $posted;
	set_time_limit(0);
	$errors = new WP_Error();
	$posted = array();
	
	$fields = array(
		'comment_id','comment_content'
	);
	
	foreach ($fields as $field) :
		if (isset($_POST[$field])) $posted[$field] = $_POST[$field]; else $posted[$field] = '';
	endforeach;
	/* Validate Required Fields */
	
	$comment_id = (isset($posted['comment_id'])?(int)$posted['comment_id']:0);
	$comment_content = (isset($posted["comment_content"])?wp_kses_post($posted["comment_content"]):"");
	
	$get_comment = get_comment($comment_id);
	$get_post = array();
	if (isset($comment_id) && $comment_id != 0 && is_object($get_comment)) {
		$get_post = get_post($get_comment->comment_post_ID);
	}
	
	if (isset($comment_id) && $comment_id != 0 && $get_post) {
		$can_edit_comment = vpanel_options("can_edit_comment");
		$comment_approved = vpanel_options("comment_approved");
		if ($can_edit_comment != 1 || $get_comment->user_id != get_current_user_id()) {
			$errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("You are not allowed to edit this comment.","vbegy"));
		}
	}else {
		$errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Sorry no comment has you select or not found.","vbegy"));
	}
	
	if (empty($comment_content)) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (comment).","vbegy"));
	if (isset($comment_content) && $comment_content == $get_comment->comment_content) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("You don't modify anything this is the same comment!.","vbegy"));
	if (sizeof($errors->errors)>0) return $errors;
	
	/* Edit comment */
	$data['comment_ID'] = $comment_id;
	if (!isset($comment_approved) || $comment_approved == 0) {
		$data['comment_approved'] = 0;
	}
	$data['comment_content']  = $comment_content;
	
	wp_update_comment($data);
	
	update_comment_meta($comment_id,"edit_comment","edited");

	do_action('vpanel_edit_comments',$comment_id);
	
	/* Successful */
	return $comment_id;
}
/* vpanel_session */
function vpanel_session ($message = "",$session = "") {
	if(!session_id())
		session_start();
	if ($message) {
		$_SESSION[$session] = $message;
	}else {
		if (isset($_SESSION[$session])) {
			$last_message = $_SESSION[$session];
			unset($_SESSION[$session]);
			echo $last_message;
		}
	}
}
/* vpanel_edit_post */
function vpanel_edit_post() {
	if ($_POST) :
		$return = process_vpanel_edit_posts();
		if (is_wp_error($return)) :
   			echo '<div class="ask_error"><span><p>'.$return->get_error_message().'</p></span></div>';
   		else :
   			if(!session_id()) session_start();
			$post_approved = vpanel_options("post_approved");
   			if ($post_approved == 1 || is_super_admin(get_current_user_id())) {
   				$_SESSION['vbegy_session_e'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Edited been successfully","vbegy").'</span><br>'.__("Has been edited successfully.","vbegy").'</p></div>';
   				wp_redirect(get_permalink($return));
   			}else {
   				$_SESSION['vbegy_session_e'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Edited been successfully","vbegy").'</span><br>'.__("Has been added successfully, The post under review.","vbegy").'</p></div>';
   				wp_redirect(esc_url(home_url('/')));
   			}
			exit;
   		endif;
	endif;
}
add_action('vpanel_edit_post','vpanel_edit_post');
/* process_vpanel_edit_posts */
function process_vpanel_edit_posts() {
	global $posted;
	set_time_limit(0);
	$errors = new WP_Error();
	$posted = array();
	
	$fields = array(
		'ID','title','comment','category','attachment','post_tag'
	);
	
	foreach ($fields as $field) :
		if (isset($_POST[$field])) $posted[$field] = $_POST[$field]; else $posted[$field] = '';
	endforeach;
	/* Validate Required Fields */
	
	$get_post = (isset($posted['ID'])?(int)$posted['ID']:0);
	$get_post_q = get_post($get_post);
	if (isset($get_post) && $get_post != 0 && $get_post_q && get_post_type($get_post) == "post") {
		$user_login_id_l = get_user_by("id",$get_post_q->post_author);
		if ($user_login_id_l->ID != get_current_user_id() && !is_super_admin(get_current_user_id())) {
			$errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Sorry you can't edit this post.","vbegy"));
		}
	}else {
		$errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Sorry no post select or not found.","vbegy"));
	}
	if (empty($posted['title'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (title).","vbegy"));
	$category_post = vpanel_options("category_post");
	$category_post_required = vpanel_options("category_post_required");
	if ($category_post == 1 && $category_post_required == 1 && (empty($posted['category']) || $posted['category'] == '-1')) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (category).","vbegy"));
	
	if (vpanel_options("content_post") == 1) {
		if (empty($posted['comment'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (content).","vbegy"));
	}
	if (sizeof($errors->errors)>0) return $errors;
	
	$post_id = $get_post;
	
	$post_approved = vpanel_options("post_approved");
	$post_name = array();
	$change_post_url = vpanel_options("change_post_url");
	if ($change_post_url == 1) {
		$post_name = array('post_name' => sanitize_text_field($posted['title']));
	}
	
	/* Edit post */
	$data = array(
		'ID'		   => sanitize_text_field($post_id),
		'post_content' => ask_kses_stip_wpautop($posted['comment']),
		'post_title'   => ask_kses_stip($posted['title']),
		'post_status'  => ($post_approved == 1 || is_super_admin(get_current_user_id())?"publish":"draft"),
	);
	
	wp_update_post(array_merge($post_name,$data));
	
	if ($category_post == 1) {
		$terms = array();
		if ($posted['category']) $terms[] = get_term_by('id',$posted['category'],'category')->slug;
		if (sizeof($terms)>0) wp_set_object_terms($post_id,$terms,'category');
	}
	
	$attachment = '';

	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		
	if(isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) :
			
		$attachment = wp_handle_upload($_FILES['attachment'],array('test_form'=>false),current_time('mysql'));
					
		if (isset($attachment['error'])) :
			$errors->add('upload-error',__("Attachment Error: ","vbegy") . $attachment['error']);
			
			return $errors;
		endif;
		
	endif;
	if ($attachment) :
		$add_post_no_register = vpanel_options("add_post_no_register");
		$attachment_data = array(
			'post_mime_type' => $attachment['type'],
			'post_title'     => preg_replace('/\.[^.]+$/','',basename($attachment['file'])),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_author'    => (!is_user_logged_in && $add_post_no_register == 1?0:get_current_user_id())
		);
		$attachment_id = wp_insert_attachment($attachment_data,$attachment['file'],$post_id);
		$attachment_metadata = wp_generate_attachment_metadata($attachment_id,$attachment['file']);
		wp_update_attachment_metadata($attachment_id, $attachment_metadata);
		set_post_thumbnail($post_id,$attachment_id);
	endif;
	
	/* Tags */
	
	if (isset($posted['post_tag']) && $posted['post_tag']) :
				
		$tags = explode(',',trim(stripslashes($posted['post_tag'])));
		$tags = array_map('strtolower',$tags);
		$tags = array_map('trim',$tags);

		if (sizeof($tags)>0) :
			wp_set_object_terms($post_id,$tags,'post_tag');
		endif;
		
	endif;

	do_action('vpanel_edit_posts',$post_id);
	
	/* Successful */
	return $post_id;
}
/* ask_process_edit_profile_form */
function ask_process_edit_profile_form() {
	global $posted;
	require_once(ABSPATH . 'wp-admin/includes/user.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	$errors = "";
	$errors_2 = new WP_Error();
	$posted = array(
		'email'        => esc_html($_POST['email']),
		'pass1'        => esc_html($_POST['pass1']),
		'pass2'        => esc_html($_POST['pass2']),
		'country'      => (isset($_POST['country'])?esc_html($_POST['country']):''),
		'city'         => (isset($_POST['city'])?esc_html($_POST['city']):''),
		'age'          => (isset($_POST['age'])?esc_html($_POST['age']):''),
		'phone'        => (isset($_POST['phone'])?esc_html($_POST['phone']):''),
		'sex'          => (isset($_POST['sex'])?esc_html($_POST['sex']):''),
		'first_name'   => (isset($_POST['first_name'])?esc_html($_POST['first_name']):''),
		'last_name'    => (isset($_POST['last_name'])?esc_html($_POST['last_name']):''),
		'display_name' => (isset($_POST['display_name'])?esc_html($_POST['display_name']):''),
		'url'          => (isset($_POST['url'])?esc_html($_POST['url']):''),
	);
	
	$posted = apply_filters('askme_edit_profile_fields',$posted);
	
	$profile_picture_required_profile = vpanel_options("profile_picture_required_profile");
	$country_profile = vpanel_options("country_profile");
	$country_required_profile = vpanel_options("country_required_profile");
	$city_profile = vpanel_options("city_profile");
	$city_required_profile = vpanel_options("city_required_profile");
	$age_profile = vpanel_options("age_profile");
	$age_required_profile = vpanel_options("age_required_profile");
	$phone_profile = vpanel_options("phone_profile");
	$phone_required_profile = vpanel_options("phone_required_profile");
	$sex_profile = vpanel_options("sex_profile");
	$sex_required_profile = vpanel_options("sex_required_profile");
	$names_profile = vpanel_options("names_profile");
	$names_required_profile = vpanel_options("names_required_profile");
	$url_profile = vpanel_options("url_profile");
	$url_required_profile = vpanel_options("url_required_profile");
	
	if (empty($posted['email'])) $errors_2->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields.","vbegy"));
	if ($posted['pass1'] !== $posted['pass2']) $errors_2->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Password does not match.","vbegy"));
	
	if ($country_profile == 1 && $country_required_profile == 1 && empty($posted['country'])) {
		$errors_2->add('required-country', __("There are required fields ( Country ).","vbegy"));
	}
	if ($city_profile == 1 && $city_required_profile == 1 && empty($posted['city'])) {
		$errors_2->add('required-city', __("There are required fields ( City ).","vbegy"));
	}
	if ($age_profile == 1 && $age_required_profile == 1 && empty($posted['age'])) {
		$errors_2->add('required-age', __("There are required fields ( Age ).","vbegy"));
	}
	if ($phone_profile == 1 && $phone_required_profile == 1 && empty($posted['phone'])) {
		$errors_2->add('required-phone', __("There are required fields ( Phone ).","vbegy"));
	}
	if ($sex_profile == 1 && $sex_required_profile == 1 && empty($posted['sex'])) {
		$errors_2->add('required-sex', __("There are required fields ( Sex ).","vbegy"));
	}
	if ($names_profile == 1 && $names_required_profile == 1 && empty($posted['first_name'])) {
		$errors_2->add('required-first_name', __("There are required fields ( First Name ).","vbegy"));
	}
	if ($names_profile == 1 && $names_required_profile == 1 && empty($posted['last_name'])) {
		$errors_2->add('required-last_name', __("There are required fields ( Last Name ).","vbegy"));
	}
	if ($names_profile == 1 && $names_required_profile == 1 && empty($posted['display_name'])) {
		$errors_2->add('required-display_name', __("There are required fields ( Display Name ).","vbegy"));
	}
	if ($url_profile == 1 && $url_required_profile == 1 && empty($posted['url'])) {
		$errors_2->add('required-url', __("There are required fields ( URL ).","vbegy"));
	}
	
	do_action('askme_edit_profile_errors',$errors_2,$posted);
	
	$user_id = get_current_user_id();
	isset($_POST['admin_bar_front']) ? 'true' : 'false';
	$get_you_avatar = get_user_meta($user_id,"you_avatar",true);
	$errors_user = edit_user($user_id);
	if (is_wp_error($errors_user)) return $errors_user;
	do_action('personal_options_update',$user_id);
	
	if (isset($_FILES['you_avatar']) && !empty($_FILES['you_avatar']['name'])) :
		$mime = $_FILES["you_avatar"]["type"];
		if (($mime != 'image/jpeg') && ($mime != 'image/jpg') && ($mime != 'image/png')) {
			$errors_2->add('upload-error', esc_html__('Error type, Please upload: jpg,jpeg,png','vbegy'));
			if ($errors_2->get_error_code()) return $errors_2;
		}else {
			$you_avatar = wp_handle_upload($_FILES['you_avatar'],array('test_form'=>false),current_time('mysql'));
			if ($you_avatar && isset($you_avatar["url"])) :
				$filename = $you_avatar["file"];
				$filetype = wp_check_filetype( basename( $filename ), null );
				$wp_upload_dir = wp_upload_dir();
				
				$attachment = array(
					'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);
				$attach_id = wp_insert_attachment( $attachment, $filename );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				update_user_meta($user_id,"you_avatar",$attach_id);
			endif;
			if (isset($you_avatar['error']) && $you_avatar) :
				if (isset($errors_2->add)) {
					$errors_2->add('upload-error', esc_html__('Error in upload the image : ','vbegy') . $you_avatar['error']);
					if ($errors_2->get_error_code()) return $errors_2;
				}
				return $errors_2;
			endif;
		}
	else:
		if ($profile_picture_required_profile == 1 && $get_you_avatar == "") {
			$errors_2->add('required-profile_picture', __("There are required fields ( Profile Picture ).","vbegy"));
			return $errors_2;
		}
		update_user_meta($user_id,"you_avatar",$get_you_avatar);
	endif;
	
	if (sizeof($errors_2->errors)>0) return $errors_2;
	return;
}
/* ask_edit_profile_form */
function ask_edit_profile_form() {
	if (isset($_POST["user_action"]) && $_POST["user_action"] == "edit_profile") :
		$return = ask_process_edit_profile_form();
		if (is_wp_error($return)) :
			$error_string = $return->get_error_message();
   			echo '<div class="ask_error"><span><p>'.$return->get_error_message().'</p></span></div>';
   		else :
   			echo '<div class="ask_done"><span><p>'.__("Profile has been updated","vbegy").'</p></span></div>';
   		endif;
	endif;
}
add_action('ask_edit_profile_form','ask_edit_profile_form');
/* add_favorite */
function add_favorite() {
	$post_id = (int)$_POST['post_id'];
	$user_id = get_current_user_id();
	$user_login_id2 = get_user_by("id",$user_id);
	
	$favorites_questions = get_post_meta($post_id,"favorites_questions",true);
	if (empty($favorites_questions)) {
		$update = update_post_meta($post_id,"favorites_questions",array($user_id));
	}else if (is_array($favorites_questions) && !in_array($user_id,$favorites_questions)) {
		$update = update_post_meta($post_id,"favorites_questions",array_merge($favorites_questions,array($user_id)));
	}
	
	$_favorites = get_user_meta($user_id,$user_login_id2->user_login."_favorites",true);
	if (is_array($_favorites)) {
		if (!in_array($post_id,$_favorites)) {
			$array_merge = array_merge($_favorites,array($post_id));
			update_user_meta($user_id,$user_login_id2->user_login."_favorites",$array_merge);
		}
	}else {
		update_user_meta($user_id,$user_login_id2->user_login."_favorites",array($post_id));
	}
	
	$count = get_post_meta($post_id,'question_favorites',true);
	if ($count == "") {
		$count = 0;
	}
	$count++;
	$update = update_post_meta($post_id,'question_favorites',$count);
	
	$get_post = get_post($post_id);
	$post_author = $get_post->post_author;
	$anonymously_user = get_post_meta($post_id,"anonymously_user",true);
	if (($user_id > 0 && $post_author > 0) || ($user_id > 0 && $anonymously_user > 0)) {
		askme_notifications_activities(($post_author > 0?$post_author:$anonymously_user),$user_id,"",$post_id,"","question_favorites","notifications","","question");
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,"","question_favorites","activities","","question");
	}
	die();
}
add_action('wp_ajax_add_favorite','add_favorite');
add_action('wp_ajax_nopriv_add_favorite','add_favorite');
/* remove_favorite */
function remove_favorite() {
	$post_id = (int)$_POST['post_id'];
	$user_id = get_current_user_id();
	$user_login_id2 = get_user_by("id",$user_id);
	
	$favorites_questions = get_post_meta($post_id,"favorites_questions",true);
	if (isset($favorites_questions) && !empty($favorites_questions)) {
		$remove_favorites_questions = remove_item_by_value($favorites_questions,$user_id);
		update_post_meta($post_id,"favorites_questions",$remove_favorites_questions);
	}
	
	$_favorites = get_user_meta($user_id,$user_login_id2->user_login."_favorites",true);
	if (isset($_favorites) && is_array($_favorites) && in_array($post_id,$_favorites)) {
		$remove_item = remove_item_by_value($_favorites,$post_id);
		update_user_meta($user_id,$user_login_id2->user_login."_favorites",$remove_item);
	}
	
	$count = get_post_meta($post_id,'question_favorites',true);
	if ($count == "") {
		$count = 0;
	}
	$count--;
	if ($count < 0) {
		$count = 0;
	}
	$update = update_post_meta($post_id,'question_favorites',$count);
	
	$get_post = get_post($post_id);
	$post_author = $get_post->post_author;
	$anonymously_user = get_post_meta($post_id,"anonymously_user",true);
	if (($user_id > 0 && $post_author > 0) || ($user_id > 0 && $anonymously_user > 0)) {
		askme_notifications_activities(($post_author > 0?$post_author:$anonymously_user),$user_id,"",$post_id,"","question_remove_favorites","notifications","","question");
	}
	if ($user_id > 0) {
		askme_notifications_activities($user_id,"","",$post_id,"","question_remove_favorites","activities","","question");
	}
	die();
}
add_action('wp_ajax_remove_favorite','remove_favorite');
add_action('wp_ajax_nopriv_remove_favorite','remove_favorite');
/* remove_item_by_value */
function remove_item_by_value($array,$val = '',$preserve_keys = true) {
	if (empty($array) || !is_array($array)) return false;
	if (!in_array($val,$array)) return $array;
	
	foreach($array as $key => $value) {
		if ($value == $val) unset($array[$key]);
	}
	
	return ($preserve_keys === true) ? $array : array_values($array);
}
/* excerpt_row */
function excerpt_row($excerpt_length,$content) {
	global $post;
	$words = explode(' ',$content,$excerpt_length + 1);
	if(count($words) > $excerpt_length) :
		array_pop($words);
		array_push($words,'...');
		$content = implode(' ',$words);
	endif;
		$content = strip_tags($content);
	echo $content;
}
/* excerpt_title_row */
function excerpt_title_row($excerpt_length,$title) {
	global $post;
	$words = explode(' ',$title,$excerpt_length + 1);
	if(count($words) > $excerpt_length) :
		array_pop($words);
		array_push($words,'');
		$title = implode(' ',$words);
	endif;
		$title = strip_tags($title);
	echo $title;
}
$vpanel_emails = '
<div style="word-wrap:break-word">
<div>
<div>
<div style="margin:0px;background-color:#f4f3f4;font-family:Helvetica,Arial,sans-serif;font-size:12px" text="#444444" bgcolor="#F4F3F4" link="#21759B" alink="#21759B" vlink="#21759B" marginheight="0" marginwidth="0">
	<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F4F3F4">
		<tbody>
		<tr>
		<td style="padding:15px">
			<center>
				
				<table width="550" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
				<tbody>
				<tr>
				<td align="left">
				<div style="border:solid 1px #d9d9d9">
				<table style="line-height:1.6;font-size:12px;font-family:Helvetica,Arial,sans-serif;border:solid 1px #ffffff;color:#444" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
				<tbody>
				<tr>
				<td style="color:#ffffff" colspan="2" valign="bottom" height="30"></td>
				</tr>
				<tr>
				<td style="line-height:32px;padding-left:30px" valign="baseline"><a href="'.esc_url(home_url('/')).'" target="_blank">';
				$vpanel_emails_2 = '</a></td>';
				$description_email_template = vpanel_options("description_email_template");
				if ($description_email_template == 1) {
					$vpanel_emails_2 .= '<td style="padding-right:30px" align="right" valign="baseline">
					<span style="font-size:14px;color:#444">'.get_bloginfo ('description').'</span>
					</td>';
				}
				$vpanel_emails_2 .= '</tr>
				</tbody>
				</table>
				
				<table style="margin-top:15px;margin-right:30px;margin-left:30px;color:#444;line-height:1.6;font-size:12px;font-family:Arial,sans-serif" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
				<tbody>
				<tr>
				<td style="border-top:solid 1px #d9d9d9" colspan="2">
				<div style="padding:15px 0">';
				$vpanel_emails_3 = '</div>
				</td>
				</tr>
				</tbody>
				</table>
				</div>
				</td>
				</tr>
				</tbody>
				</table>
			</center>
		</td>
		</tr>
		</tbody>
	</table>
</div>
</div>
</div>
</div>';
$vpanel_emails = apply_filters("vpanel_emails",$vpanel_emails);
$vpanel_emails_2 = apply_filters("vpanel_emails_2",$vpanel_emails_2);
$vpanel_emails_3 = apply_filters("vpanel_emails_3",$vpanel_emails_3);
/* ask_coupon_valid */
function ask_coupon_valid ($coupons,$coupon_name,$coupons_not_exist,$pay_ask_payment,$what_return = '') {
	if (isset($coupons) && is_array($coupons)) {
		foreach ($coupons as $coupons_k => $coupons_v) {
			if (is_array($coupons_v) && in_array($coupon_name,$coupons_v)) {
				if ($what_return == "coupons_not_exist") {
					return "yes";
				}
				if (isset($coupons_v["coupon_date"]) && $coupons_v["coupon_date"] !="" && $coupons_v["coupon_date"] < date_i18n('m/d/Y',current_time('timestamp'))) {
					return '<div class="alert-message error"><p>'.__("This coupon has expired.","vbegy").'</p></div>';
				}else if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "percent") {
					if ((int)$coupons_v["coupon_amount"] > 100) {
						return '<div class="alert-message error"><p>'.__("This coupon is not valid.","vbegy").'</p></div>';
					}else {
						$the_discount = ($pay_ask_payment*$coupons_v["coupon_amount"])/100;
						$last_payment = $pay_ask_payment-$the_discount;
						if ($what_return == "last_payment") {
							return $last_payment;
						}
					}
				}else if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "discount") {
					if ((int)$coupons_v["coupon_amount"] > $pay_ask_payment) {
						return '<div class="alert-message error"><p>'.__("This coupon is not valid.","vbegy").'</p></div>';
					}else {
						$last_payment = $pay_ask_payment-$coupons_v["coupon_amount"];
						if ($what_return == "last_payment") {
							return $last_payment;
						}
					}
				}else {
					return '<div class="alert-message success"><p>'.__("Coupon code applied successfully.","vbegy").'</p></div>';
				}
			}
		}
	}
}
/* ask_find_coupons */
function ask_find_coupons($coupons,$coupon_name) {
	foreach ($coupons as $coupons_k => $coupons_v) {
		if (is_array($coupons_v) && in_array($coupon_name,$coupons_v)) {
			return $coupons_k;
		}
	}
	return false;
}
/* send_admin_notification */
function send_admin_notification($post_id,$post_title) {
	$blogname = get_option('blogname');
	$email = get_option('admin_email');
	$headers = "MIME-Version: 1.0\r\n" . "From: ".$blogname." "."<".$email.">\n" . "Content-Type: text/HTML; charset=\"" . get_option('blog_charset') . "\"\r\n";
	$message = __('Hello there,','vbegy').'<br/><br/>'. 
	__('A new post has been submitted in ','vbegy').$blogname.' site.'.__(' Please find details below:','vbegy').'<br/><br/>'.
	
	'Post title: '.$post_title.'<br/><br/>';
	$post_author_name = get_post_meta($post_id,'ap_author_name',true);
	$post_author_email = get_post_meta($post_id,'ap_author_email',true);
	$post_author_url = get_post_meta($post_id,'ap_author_url',true);
	if($post_author_name!=''){
		$message .= 'Post Author Name: '.$post_author_name.'<br/><br/>';
	}
	if($post_author_email!=''){
		$message .= 'Post Author Email: '.$post_author_email.'<br/><br/>';
	}
	if($post_author_url!=''){
		$message .= 'Post Author URL: '.$post_author_url.'<br/><br/>';
	}
	
	$message .= '____<br/><br/>
	'.__('To take action (approve/reject)- please go here:','vbegy').'<br/>'
	.admin_url().'post.php?post='.$post_id.'&action=edit <br/><br/>
	
	'.__('Thank You','vbegy');
	$subject = __('New Post Submission','vbegy');
	wp_mail($email,$subject,$message,$headers);
}
/* askme_edit_comment */
add_action ('edit_comment','askme_edit_comment');
function askme_edit_comment($comment_id) {
	update_comment_meta($comment_id,"delete_reason",esc_attr($_POST["delete_reason"]));
}
/* askme_meta_boxes_comment */
add_action('add_meta_boxes_comment','askme_meta_boxes_comment');
function askme_meta_boxes_comment($comment) {
	$answer_question = get_post_type($comment->comment_post_ID);
	if ($answer_question == "question" || $answer_question == "post") {?>
		<div class="stuffbox">
			<div class="inside">
				<fieldset>
					<legend class="edit-comment-author">Reason if you need to remove it.</legend>
					<table class="form-table editcomment">
						<tbody>
							<tr>
								<td class="first" style="width: 10px;"><label for="delete_reason">Reason:</label></td>
								<td>
									<input id="delete_reason" name="delete_reason" class="code" type="text" value="<?php echo esc_attr(get_comment_meta($comment->comment_ID,"delete_reason",true));?>" style="width: 98%;">
								</td>
							</tr>
						</tbody>
					</table>
					<br>
					<div class="submitbox"><a href="#" class="submitdelete delete-comment-answer" data-div-id="delete_reason" data-id="<?php echo esc_attr($comment->comment_ID);?>" data-action="delete_comment_answer" data-location="<?php echo esc_url(($answer_question == "question"?admin_url( 'edit-comments.php?comment_status=all&answers=1'):admin_url( 'edit-comments.php?comment_status=all&comments=1')))?>">Delete?</a></div>
				</fieldset>
			</div>
		</div>
	<?php }?>
<?php }
/* askme_comments_exclude */
add_action('current_screen','askme_comments_exclude',10,2);
function askme_comments_exclude($screen) {
	if ($screen->id != 'edit-comments')
		return;
	if (isset($_GET['answers'])) {
		add_action('pre_get_comments','askme_list_answers',10,1);
	}else if (isset($_GET['comments'])) {
		add_action('pre_get_comments','askme_list_comments',10,1);
	}
	add_filter('comment_status_links','askme_new_answers_page_link');
}
function askme_list_comments($clauses) {
	$clauses->query_vars['post_type'] = "post";
}
function askme_list_answers($clauses) {
	$clauses->query_vars['post_type'] = "question";
}
function askme_new_answers_page_link($status_links) {
	$count = get_all_comments_of_post_type("question");
	$count_posts = get_all_comments_of_post_type("post");
	$status_links['comments'] = '<a href="edit-comments.php?comment_status=all&comments=1"'.(isset($_GET['comments'])?' class="current"':'').'>'.__('Comments','vbegy').' ('.$count_posts.')</a>';
	$status_links['answers'] = '<a href="edit-comments.php?comment_status=all&answers=1"'.(isset($_GET['answers'])?' class="current"':'').'>'.__('Answers','vbegy').' ('.$count.')</a>';
	return $status_links;
}
/* askme_before_delete_user */
add_action('delete_user','askme_before_delete_user');
function askme_before_delete_user($user_id) {
	$active_points = vpanel_options("active_points");
	$point_following_me = vpanel_options("point_following_me");
	$point_following_me = ($point_following_me != ""?$point_following_me:1);
	
	$following_me = get_user_meta($user_id,"following_me",true);
	if (isset($following_me) && is_array($following_me)) {
		foreach ($following_me as $key => $value) {
			$following_me = get_user_meta($value,"following_me",true);
			$get_user_by_following_not_id = get_user_by("id",$value);
			$remove_following_me = remove_item_by_value($following_me,$user_id);
			update_user_meta($value,"following_me",$remove_following_me);
			if ($active_points == 1) {
				$points = get_user_meta($value,"points",true);
				$new_points = $points-$point_following_me;
				if ($new_points < 0) {
					$new_points = 0;
				}
				update_user_meta($value,"points",$new_points);
				
				$_points = get_user_meta($value,$get_user_by_following_not_id->user_login."_points",true);
				$_points++;
				
				update_user_meta($value,$get_user_by_following_not_id->user_login."_points",$_points);
				add_user_meta($value,$get_user_by_following_not_id->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_following_me,"-","delete_follow_user","",""));
			}
			
			$following_you = get_user_meta($value,"following_you",true);
			$remove_following_you = remove_item_by_value($following_you,$user_id);
			update_user_meta($value,"following_you",$remove_following_you);
		}
	}
}
/* update_notifications */
function update_notifications() {
	$user_id = get_current_user_id();
	update_user_meta($user_id,$user_id.'_new_notifications',0);
	die(1);
}
add_action( 'wp_ajax_update_notifications', 'update_notifications' );
add_action('wp_ajax_nopriv_update_notifications','update_notifications');?>