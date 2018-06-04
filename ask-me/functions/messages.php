<?php
ob_start();
if(!session_id()) session_start();
$settings = array("textarea_name" => "comment","media_buttons" => true,"textarea_rows" => 10);
/* message post type */
function message_post_types_init() {
	$messages_slug = vpanel_options('message_slug');
	$messages_slug = (isset($messages_slug) && $messages_slug != ""?$messages_slug:"message");
    register_post_type( 'message',
        array(
        	'label' => __('Messages','vbegy'),
            'labels' => array(
				'name'               => __('Messages','vbegy'),
				'singular_name'      => __('Messages','vbegy'),
				'menu_name'          => __('Messages','vbegy'),
				'name_admin_bar'     => __('Messages','vbegy'),
				'add_new'            => __('Add New','vbegy'),
				'add_new_item'       => __('Add New Message','vbegy'),
				'new_item'           => __('New Message','vbegy'),
				'edit_item'          => __('Edit Message','vbegy'),
				'view_item'          => __('View Message','vbegy'),
				'view_items'         => __('View Messages','vbegy'),
				'all_items'          => __('All Messages','vbegy'),
				'search_items'       => __('Search Messages','vbegy'),
				'parent_item_colon'  => __('Parent Message:','vbegy'),
				'not_found'          => __('No Messages Found.','vbegy'),
				'not_found_in_trash' => __('No Messages Found in Trash.','vbegy'),
            ),
            'description'         => '',
            'public'              => false,
            'show_ui'             => true,
            'capability_type'     => 'post',
            'capabilities'        => array('create_posts' => 'do_not_allow'),
            'map_meta_cap'        => true,
            'publicly_queryable'  => false,
            'exclude_from_search' => false,
            'hierarchical'        => false,
            'query_var'           => false,
            'show_in_rest'        => false,
            'has_archive'         => false,
			'menu_position'       => 5,
			'menu_icon'           => "dashicons-email-alt",
            'supports'            => array('title','editor'),
        )
    );
}  
add_action( 'init', 'message_post_types_init', 0 );
function message_updated_messages($messages) {
  global $post_ID;
  $messages['message'] = array(
    0 => '',
    1 => '',
  );
  return $messages;
}
add_filter('post_updated_messages','message_updated_messages');
/* Admin columns for post types */
function askme_message_columns($old_columns){
	$columns = array();
	$columns["cb"]       = "<input type=\"checkbox\">";
	$columns["title"]    = __("Title","vbegy");
	$columns["content"]  = __("Content","vbegy");
	$columns["author_m"] = __("Author","vbegy");
	$columns["to_user"]  = __("To user","vbegy");
	$columns["date"]     = __("Date","vbegy");
	$columns["delete"]   = __("User delete?","vbegy");
	return $columns;
}
add_filter('manage_edit-message_columns', 'askme_message_columns');

function askme_message_custom_columns($column) {
	global $post;
	$to_user = get_post_meta($post->ID,'message_user_id',true);
	$display_name_user = get_the_author_meta('display_name',$to_user);
	switch ( $column ) {
		case 'author_m' :
			$display_name = get_the_author_meta('display_name',$post->post_author);
			if ($post->post_author > 0) {
				echo '<a href="edit.php?post_type=message&author='.$post->post_author.'">'.$display_name.'</a>';
			}else {
				echo get_post_meta($post->ID,'message_username',true);
			}
		break;
		case 'content' :
			echo $post->post_content;
		break;
		case 'to_user' :
			echo '<a href="'.get_author_posts_url($to_user).'">'.$display_name_user.'</a>';
		break;
		case 'delete' :
			$delete_send_message = get_post_meta($post->ID,"delete_send_message",true);
			$delete_inbox_message = get_post_meta($post->ID,"delete_inbox_message",true);
			if ($delete_inbox_message == 1) {
				echo '<a href="'.get_author_posts_url($to_user).'">'.$display_name_user.'</a> '.__("delete his inbox message.","vbegy");
			}
			if ($delete_send_message == 1 || $delete_inbox_message == 1) {
				if ($delete_send_message == 1 && $delete_inbox_message == 1) {
					echo '<br>';
				}
				if ($delete_send_message == 1) {
					$display_name = get_the_author_meta('display_name',$post->post_author);
					echo '<a href="'.get_author_posts_url($post->post_author).'">'.$display_name.'</a> '.__("delete his sended message.","vbegy");
				}
			}
			if ($delete_inbox_message != 1 && $delete_send_message != 1) {
				echo '<span aria-hidden="true">—</span><span class="screen-reader-text">'.__("No one delete it","vbegy").'</span>';
			}
		break;
	}
}
add_action('manage_message_posts_custom_column', 'askme_message_custom_columns', 2);
/* send_message_shortcode */
add_shortcode('send_message', 'send_message_shortcode');
function send_message_shortcode($atts, $content = null) {
	global $posted,$settings;
	$a = shortcode_atts( array(
	    'type' => '',
	), $atts );
	$out = '';
	$send_message = vpanel_options("send_message");
	$send_message_no_register = vpanel_options("send_message_no_register");
	$custom_permission = vpanel_options("custom_permission");
	
	if (is_user_logged_in) {
		$user_get_current_user_id = get_current_user_id();
		$user_is_login = get_userdata($user_get_current_user_id);
		$user_login_group = key($user_is_login->caps);
		$roles = $user_is_login->allcaps;
	}
	
	if (($custom_permission == 1 && is_user_logged_in && empty($roles["send_message"])) || ($custom_permission == 1 && !is_user_logged_in && $send_message != 1)) {
		$out .= '<div class="note_error"><strong>'.__("Sorry, you do not have a permission to send message.","vbegy").'</strong></div>';
		if (!is_user_logged_in) {
			$out .= '<div class="form-style form-style-3"><div class="note_error"><strong>'.__("You must login to send a message.","vbegy").'</strong></div>'.do_shortcode("[ask_login register_2='yes']").'</div>';
		}
	}else if (!is_user_logged_in && $send_message_no_register != 1) {
		$out .= '<div class="form-style form-style-3"><div class="note_error"><strong>'.__("You must login to send a message.","vbegy").'</strong></div>'.do_shortcode("[ask_login register_2='yes']").'</div>';
	}else {
		if ($_POST) {
			$post_type = (isset($_POST["post_type"]) && $_POST["post_type"] != ""?esc_html($_POST["post_type"]):"");
		}else {
			$post_type = "";
		}
		
		if (isset($_POST["post_type"]) && $_POST["post_type"] == "send_message") {
			do_action('new_message');
		}
		
		if ($post_type != "add_question" && $post_type != "edit_question" && $post_type != "add_post" && $post_type != "edit_post") {
			$users_by_id = $get_user_id = 0;
			if (isset($_GET["user_id"]) && $_GET["user_id"] != "") {
				$get_user_id = (int)$_GET["user_id"];
				$get_users_by_id = get_users(array("include" => array($get_user_id)));
				if (isset($get_users_by_id) && !empty($get_users_by_id)) {
					$users_by_id = 1;
				}
			}else if (is_author()) {
				$users_by_id = $get_user_id = 0;
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
				if (isset($user_login) && is_object($user_login)) {
					$users_by_id = 1;
					$get_user_id = $user_login->ID;
				}
			}
			
			if (is_user_logged_in && $user_get_current_user_id == $get_user_id) {
				echo '<div class="alert-message error"><p>'.__("You can't send message for yourself.","vbegy").'</p></div>';
			}else {
				$comment_message = vpanel_options("comment_message");
				$out .= '<div class="form-posts"><div class="form-style form-style-3 message-submit">
					<div class="send_message">
						<div '.(!is_user_logged_in?"class='if_no_login'":"").'>';
							$rand_q = rand(1,1000);?>
							<?php
							$out .= '
							<form class="new-message-form" method="post" enctype="multipart/form-data">
								<div class="note_error display"></div>
								<div class="form-inputs clearfix">';
									if (!is_user_logged_in && $send_message_no_register == 1) {
										$out .= '<p>
											<label for="message-username-'.$rand_q.'" class="required">'.__("Username","vbegy").'<span>*</span></label>
											<input name="username" id="message-username-'.$rand_q.'" class="the-username" type="text" value="'.(isset($posted['username'])?$posted['username']:'').'">
											<span class="form-description">'.__("Please type your username .","vbegy").'</span>
										</p>
										
										<p>
											<label for="message-email-'.$rand_q.'" class="required">'.__("E-Mail","vbegy").'<span>*</span></label>
											<input name="email" id="message-email-'.$rand_q.'" class="the-email" type="text" value="'.(isset($posted['email'])?$posted['email']:'').'">
											<span class="form-description">'.__("Please type your E-Mail .","vbegy").'</span>
										</p>';
									}
									$out .= '<p>
										<label for="message-title-'.$rand_q.'" class="required">'.__("Message Title","vbegy").'<span>*</span></label>
										<input name="title" id="message-title-'.$rand_q.'" class="the-title" type="text" value="'.(isset($posted['title'])?ask_kses_stip($posted['title']):(isset($_POST["title"])?ask_kses_stip($_POST["title"]):"")).'">
									</p>';
								$out .= '</div>
								<div class="details-area">
									<label for="message-details-'.$rand_q.'" '.($comment_message == 1?'class="required"':'').'>'.__("Details","vbegy").($comment_message == 1?'<span>*</span>':'').'</label>';
									
									$editor_message_details = vpanel_options("editor_message_details");
									if ($editor_message_details == 1) {
										ob_start();
										wp_editor((isset($posted['comment'])?ask_kses_stip_wpautop($posted['comment']):(isset($_POST["comment"])?wp_kses_post($_POST["comment"]):"")),"message-details-".$rand_q,$settings);
										$editor_contents = ob_get_clean();
										
										$out .= '<div class="the-details the-textarea">'.$editor_contents.'</div>';
									}else {
										$out .= '<textarea name="comment" id="message-details-'.$rand_q.'" class="the-textarea" aria-required="true" cols="58" rows="8">'.(isset($posted['comment'])?ask_kses_stip($posted['comment']):(isset($_POST["comment"])?ask_kses_stip($_POST["comment"]):"")).'</textarea>';
									}
								$out .= '<div class="clearfix"></div></div>
								
								<div class="form-inputs clearfix">';
								
								$the_captcha = vpanel_options("the_captcha_message");
								$captcha_style = vpanel_options("captcha_style");
								$captcha_question = vpanel_options("captcha_question");
								$captcha_answer = vpanel_options("captcha_answer");
								$show_captcha_answer = vpanel_options("show_captcha_answer");
								if ($the_captcha == 1) {
									if ($captcha_style == "question_answer") {
										$out .= "
										<p class='ask_captcha_p'>
											<label for='ask_captcha-'.$rand_q.'' class='required'>".__("Captcha","vbegy")."<span>*</span></label>
											<input size='10' id='ask_captcha-'.$rand_q.'' name='ask_captcha' class='ask_captcha captcha_answer' value='' type='text'>
											<span class='question_poll ask_captcha_span'>".$captcha_question.($show_captcha_answer == 1?" ( ".$captcha_answer." )":"")."</span>
										</p>";
									}else {
										$out .= "
										<p class='ask_captcha_p'>
											<label for='ask_captcha_".$rand_q."' class='required'>".__("Captcha","vbegy")."<span>*</span></label>
											<input size='10' id='ask_captcha_".$rand_q."' name='ask_captcha' class='ask_captcha' value='' type='text'><img class='ask_captcha_img' src='".get_template_directory_uri()."/captcha/create_image.php' alt='".__("Captcha","vbegy")."' title='".__("Click here to update the captcha","vbegy")."' onclick=";$out .='"javascript:ask_get_captcha';$out .="('".get_template_directory_uri()."/captcha/create_image.php', 'ask_captcha_img_".$rand_q."');";$out .='"';$out .=" id='ask_captcha_img_".$rand_q."'>
											<span class='question_poll ask_captcha_span'>".__("Click on image to update the captcha .","vbegy")."</span>
										</p>";
									}
								}
								
								$out .= '</div>
								
								<p class="form-submit">
									<input type="hidden" name="post_type" value="send_message">';
									if (isset($a["type"]) && $a["type"] == "popup") {
										$out .= '<input type="hidden" name="form_type" value="message-popup">';
									}else {
										$out .= '<input type="hidden" name="form_type" value="send_message">';
									}
									if ($users_by_id == 1) {
										$out .= '<input type="hidden" name="user_id" value="'.$get_user_id.'">';
									}
									$out .= '<input type="submit" value="'.__("Send Your Message","vbegy").'" class="button color small submit send-message">
								</p>
							
							</form>
						</div>
					</div>
				</div></div>';
			}
		}
	}
	return $out;
}
/* new_message */
function new_message() {
	global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
	if ($_POST) :
		$return = process_new_messages();
		if (is_wp_error($return)) :
   			echo '<div class="ask_error"><span><p>'.$return->get_error_message().'</p></span></div>';
   		else :
   			if (get_post_type($return) == "message") {
				$get_post = get_post($return);
	   			$user_id = get_current_user_id();
	   			$get_message_user = get_post_meta($get_post->ID,"message_user_id",true);
	   			$message_publish = vpanel_options("message_publish");
	   			$send_email_message = vpanel_options("send_email_message");
				if ($user_id != $get_message_user && $message_publish == "publish") {
					$message_username = get_post_meta($get_post->ID,'message_username',true);
					if ($get_post->post_author != $get_message_user && $get_message_user > 0) {
						askme_notifications_activities($get_message_user,$get_post->post_author,($get_post->post_author == 0?$message_username:""),"","","add_message_user","notifications","","message");
					}
					if ($user_id > 0) {
						askme_notifications_activities($user_id,$get_message_user,"","","","add_message","activities","","message");
					}
					
					if ($send_email_message == 1) {
						$send_text = ask_send_email(vpanel_options("email_new_message"),"",$get_post->ID);
						$logo_email_template = vpanel_options("logo_email_template");
						$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
						$user = get_userdata($get_message_user);
						$email_title = vpanel_options("title_new_message");
						$email_title = ($email_title != ""?$email_title:__("New message","vbegy"));
						sendEmail(vpanel_options("email_template"),get_bloginfo('name'),esc_attr($user->user_email),esc_attr($user->display_name),$email_title,$last_message_email);
					}
				}
				
				$_SESSION['vbegy_session_message'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Message been successfully","vbegy").'</span><br>'.__("The message has been sended successfully.","vbegy").'</p></div>';
				wp_redirect((is_author()?esc_url(vpanel_get_user_url($get_message_user)):esc_url(get_page_link(vpanel_options('messages_page')))));
				exit;
			}
			exit;
   		endif;
	endif;
}
add_action('new_message','new_message');
/* process_new_messages */
function process_new_messages() {
	global $posted;
	set_time_limit(0);
	$errors = new WP_Error();
	$posted = array();
	
	$post_type = (isset($_POST["post_type"]) && $_POST["post_type"] != ""?$_POST["post_type"]:"");
	if ($post_type == "send_message") {
		
		$fields = array(
			'title','comment','ask_captcha','username','email','user_id'
		);
		
		foreach ($fields as $field) :
			if (isset($_POST[$field])) $posted[$field] = $_POST[$field]; else $posted[$field] = '';
		endforeach;
		
		$custom_permission = vpanel_options("custom_permission");
		$send_message_no_register = vpanel_options("send_message_no_register");
		$send_message = vpanel_options("send_message");
		if (is_user_logged_in) {
			$user_get_current_user_id = get_current_user_id();
			$user_is_login = get_userdata($user_get_current_user_id);
			$user_login_group = key($user_is_login->caps);
			$roles = $user_is_login->allcaps;
		}
		
		if (($custom_permission == 1 && is_user_logged_in && empty($roles["send_message"])) || ($custom_permission == 1 && !is_user_logged_in && $send_message != 1)) {
			$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Sorry, you do not have a permission to send message.","vbegy"));
			if (!is_user_logged_in) {
				$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("You must login to send a message.","vbegy"));
			}
		}else if (!is_user_logged_in && $send_message_no_register != 1) {
			$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("You must login to send a message.","vbegy"));
		}else if ($posted['user_id'] == $user_get_current_user_id) {
			$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("You can't send message for yourself.","vbegy"));
		}else if ($posted['user_id'] == "") {
			$errors->add('required','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are a error.","vbegy"));
		}
		
		if (!is_user_logged_in && $send_message_no_register == 1 && get_current_user_id() == 0) {
			if (empty($posted['username'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (username).","vbegy"));
			if (empty($posted['email'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (email).","vbegy"));
			if (!is_email($posted['email'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("Please write correctly email.","vbegy"));
		}
		
		/* Validate Required Fields */
		if (empty($posted['title'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (title).","vbegy"));
		
		$comment_message = vpanel_options("comment_message");
		if ($comment_message == 1) {
			if (empty($posted['comment'])) $errors->add('required-field','<strong>'.__("Error","vbegy").' :&nbsp;</strong> '.__("There are required fields (content).","vbegy"));
		}
		
		$the_captcha = vpanel_options("the_captcha_message");
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
		
		if (sizeof($errors->errors)>0) return $errors;
		$message_publish = vpanel_options("message_publish");
		
		/* Create message */
		$data = array(
			'post_content' => ($posted['comment']),
			'post_title'   => sanitize_text_field($posted['title']),
			'post_status'  => ($message_publish == "publish" || is_super_admin(get_current_user_id())?"publish":"draft"),
			'post_author'  => (!is_user_logged_in && $send_message_no_register == 1?0:get_current_user_id()),
			'post_type'	=> 'message',
		);
			
		$post_id = wp_insert_post($data);
			
		if ($post_id==0 || is_wp_error($post_id)) wp_die(__("Error in message.","vbegy"));
		
		if (!is_user_logged_in && $send_message_no_register == 1 && get_current_user_id() == 0) {
			$message_username = sanitize_text_field($posted['username']);
			$message_email = sanitize_text_field($posted['email']);
			update_post_meta($post_id,'message_username',$message_username);
			update_post_meta($post_id,'message_email',$message_email);
		}
		
		update_post_meta($post_id,'message_user_id',(int)$posted['user_id']);
		update_post_meta($post_id,'message_new',1);
		
		do_action('new_messages',$post_id);
	}
	if ($post_type == "send_message") {
		/* Successful */
		return $post_id;
	}
}
/* ask_message_view */
function ask_message_view() {
	global $post;
	$seen_message = vpanel_options("seen_message");
	$message_id = (int)$_POST["message_id"];
	$the_query = new WP_Query(array("p" => $message_id,"post_type" => "message"));
	if ( $the_query->have_posts() ) {
	    while ( $the_query->have_posts() ) {
	        $the_query->the_post();
	        $post_author = $post->post_author;
	        $message_user_id = get_post_meta($message_id,'message_user_id',true);
	        $message_new = get_post_meta($message_id,'message_new',true);
	        if ($message_new == 1) {
	        	delete_post_meta($message_id,'message_new');
	        	$message_new = get_post_meta($post->ID,'message_new',true);
	        }
	        if ($seen_message == 1 && $message_new == 1 && get_current_user_id() == $message_user_id) {
	        	askme_notifications_activities($post_author,$message_user_id,"","","","seen_message","notifications","","message");
	        }
	        echo "<div>".the_content()."</div>";
	    }
	}
	wp_reset_postdata();
	die(1);
}
add_action( 'wp_ajax_ask_message_view', 'ask_message_view' );
add_action('wp_ajax_nopriv_ask_message_view','ask_message_view');
/* ask_message_reply */
function ask_message_reply() {
	$message_id = (int)$_POST["message_id"];
	echo __("RE:","vbegy")." ".get_the_title($message_id);
	die(1);
}
add_action( 'wp_ajax_ask_message_reply', 'ask_message_reply' );
add_action('wp_ajax_nopriv_ask_message_reply','ask_message_reply');
/* ask_block_message */
function ask_block_message() {
	$user_id      = (int)$_POST["user_id"];
	$current_user = get_current_user_id();
	
	$user_block_message = get_user_meta($current_user,"user_block_message",true);
	if (empty($user_block_message)) {
		update_user_meta(get_current_user_id(),"user_block_message",array($user_id));
	}else {
		update_user_meta(get_current_user_id(),"user_block_message",array_merge($user_block_message,array($user_id)));
	}
	die();
}
add_action( 'wp_ajax_ask_block_message', 'ask_block_message' );
add_action('wp_ajax_nopriv_ask_block_message','ask_block_message');
/* ask_unblock_message */
function ask_unblock_message() {
	$user_id      = (int)$_POST["user_id"];
	$current_user = get_current_user_id();
	
	$user_block_message = get_user_meta(get_current_user_id(),"user_block_message",true);
	$remove_user_block_message = remove_item_by_value($user_block_message,$user_id);
	update_user_meta(get_current_user_id(),"user_block_message",$remove_user_block_message);
	die();
}
add_action( 'wp_ajax_ask_unblock_message', 'ask_unblock_message' );
add_action('wp_ajax_nopriv_ask_unblock_message','ask_unblock_message');?>