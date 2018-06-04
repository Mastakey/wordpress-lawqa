<?php
ob_start();
if(!session_id()) session_start();
$settings = array("textarea_name" => "comment","media_buttons" => true,"textarea_rows" => 10);
/* ask_me_categories_checklist */
function ask_me_categories_checklist ($args = array()) {
	$defaults = array(
		'selected_cats' => false,
		'taxonomy' => 'category',
	);
	
	$r = wp_parse_args( $args, $defaults );
	$taxonomy = $r['taxonomy'];
	$args['name'] = $r['name'];
	$args['id'] = $r['id'];
	$args['selected_cats'] = $r['selected_cats'];
	$categories = (array) get_terms( $taxonomy, array( 'get' => 'all' ) );
	$output = '';
	foreach ($categories as $key => $value) {
		$output .= '<li id="'.$args['name'].$taxonomy.'-'.$value->term_id.'">
			<label class="selectit"><input value="'.$value->term_id.'" '.(is_array($args['selected_cats']) && in_array($value->term_id,$args['selected_cats'])?checked($value->term_id,$value->term_id,false):'').' type="checkbox" name="'.$args['name'].'[]" id="'.$args['name'].'in-'.$taxonomy.'-'.$value->term_id.'"> '.$value->name.'</label>
		</li>';
	}
	return $output;
}
if (!function_exists('ask_me_select_categories')) {
	function ask_me_select_categories ($rand,$select,$attr = array(),$post_id = '',$taxonomy = '') {
		$category_single_multi = vpanel_options("category_single_multi");
		if ($category_single_multi == "ajax" && $taxonomy != "category") {
			$attr = array(
				'name'             => 'category',
				'taxonomy'         => $taxonomy,
				'orderby'          => 'name',
				'order'            => 'ASC',
				'required'         => 'yes',
				'show_option_none' => __("Select a Category","vbegy"),
			);
			$out              = '';
			$rand             = rand(1,1000);
			$taxonomy         = $attr['taxonomy'];
			$show_option_none = $attr['show_option_none'];
			$class            = ' ask_'.$attr['name'].'_'.$rand;
			$div_class        = 'ask_'.$attr['name'].'_'.$rand;
			
			$terms = array();
			if ($post_id) {
				$terms = wp_get_post_terms($post_id,$taxonomy,array('fields' => 'ids'));
				if (!empty($terms) && is_array($terms)) {
					asort($terms);
				}
				$child_c = get_term(end($terms),$taxonomy);
				if ($child_c->parent > 0) {
					$terms[] = $child_c->parent;
				}
				
				while ($child_c->parent > 0) {
					$child_c = get_term($child_c->parent,$taxonomy);
					if (!is_wp_error($child_c)) {
						if ($child_c->parent > 0) {
							$terms[] = $child_c->parent;
							continue;
						}
					}else {
						break;
					}
				}
			}else {
				$terms = $select;
				if (!empty($terms) && is_array($terms)) {
					asort($terms);
				}
			}
			if (!empty($terms) && is_array($terms)) {
				$terms = array_unique($terms);
			}
			
			$out .= '<span class="category-wrap'.$class.'">';
				if (empty($terms) || (is_array($terms) && !count($terms))) {
					$out .= '<span id="level-0" data-level="0">'.
					ask_categories_select(null,$attr,0).'
					</span>';
				}else {
					$level = 0;
					$last_term_id = end($terms);
					foreach( $terms as $term_id) {
						$class = ($last_term_id != $term_id)?'hasChild':'';
						$out .= '<span id="ask-level-'.$level.'" data-level="'.$level.'" >'.
							ask_categories_select($term_id,$attr,$level).'
						</span>';
						$attr['parent_cat'] = $term_id;
						$level++;
					}
				}
			$out .= '</span>
			<span class="category_loader loader_2"></span>';
			return $out;
		}else if ($category_single_multi == "multi" && $taxonomy != "category") {
			$args = array(
				'selected_cats' => $select,
				'taxonomy'      => $taxonomy,
				'id'            => ($taxonomy == ask_question_category?ask_question_category:"post-category").'-'.$rand,
				'name'          => 'category'
			);
			return '<ul class="row">'.ask_me_categories_checklist($args).'</ul>';
		}else {
			$select = (!empty($select) && is_array($select) && isset($select[0])?$select[0]:$select);
			return '<span class="styled-select">'.wp_dropdown_categories(array("orderby" => "name","echo" => "0","show_option_none" => (isset($show_option_none)?$show_option_none:''),'taxonomy' => $taxonomy, 'hide_empty' => 0,'depth' => 0,'id' => ($taxonomy == ask_question_category?ask_question_category:"post-category").'-'.$rand,'name' => 'category','hierarchical' => true,'selected' => $select)).'</span>';
		}
	}
}
/* ask_me_child_cats */
if (!function_exists('ask_me_child_cats')) {
	function ask_me_child_cats () {
		$parentCat  = esc_html($_POST['catID']);
		$field_attr = stripcslashes($_POST['field_attr']);
		$field_attr = json_decode($field_attr, true);
		$taxonomy   = esc_html($field_attr['taxonomy']);
		$terms = null;
		$result = '';
		
		if ($parentCat < 1) {
			echo $result;
			die();
		}
		
		$terms = get_terms(array('taxonomy' => $taxonomy,'child_of'=> $parentCat,'hide_empty'=> 0));
		if ($terms) {
			$field_attr['parent_cat'] = $parentCat;
			if ( is_array($terms)) {
				foreach ($terms as $key => $term) {
					$terms[$key] = (array)$term;
				}
			}
			$result .= ask_categories_select(null,$field_attr,0);
		}else {
			die();
		}
		
		echo $result;
		die();
	}
}
add_action('wp_ajax_ask_me_child_cats','ask_me_child_cats');
add_action('wp_ajax_nopriv_ask_me_child_cats','ask_me_child_cats');
/* ask_categories_select */
if (!function_exists('ask_categories_select')) {
	function ask_categories_select ($terms,$attr,$level) {
		$out              = '';
		$selected         = $terms ? $terms : '';
		$required         = sprintf('data-required="%s" data-type="select"',$attr['required']);
		$taxonomy         = $attr['taxonomy'];
		$rand             = rand(1,1000);
		$class            = ' ask_'.$attr['name'].'_'.$rand.'_'.$level;
		$multi            = (isset($attr['multi'])?$attr['multi']:'[]');
		$show_option_none = (isset($attr['show_option_none'])?$attr['show_option_none']:__('Select a Category','vbegy'));
		
		$select = wp_dropdown_categories(array(
			'show_option_none' => $show_option_none,
			'hierarchical'     => 1,
			'hide_empty'       => 0,
			'orderby'          => isset($attr['orderby'])?$attr['orderby']:'name',
			'order'            => isset($attr['order'])?$attr['order']:'ASC',
			'name'             => $attr['name'].$multi,
			'taxonomy'         => $taxonomy,
			'echo'             => 0,
			'title_li'         => '',
			'class'            => 'cat-ajax '.$taxonomy.$class,
			'id'               => 'cat-ajax '.$taxonomy.$class,
			'selected'         => $selected,
			'depth'            => 1,
			'child_of'         => isset($attr['parent_cat'])?$attr['parent_cat']:''
		));
		
		$attr = array(
			'required'     => $attr['required'],
			'name'         => $attr['name'],
			'orderby'      => $attr['orderby'],
			'order'        => $attr['order'],
			'name'         => $attr['name'],
			'taxonomy'     => $attr['taxonomy'],
		);
		
		$out .= '<span class="styled-select">'.str_replace('<select','<select data-taxonomy='.json_encode($attr).' '.$required,$select).'</span>';
		
		return $out;
	}
}
/* ask_question_shortcode */
add_shortcode('ask_question', 'ask_question_shortcode');
function ask_question_shortcode($atts, $content = null) {
	global $posted,$settings;
	$a = shortcode_atts( array(
	    'type' => '',
	), $atts );
	$out = '';
	$ask_question_no_register = vpanel_options("ask_question_no_register");
	$ask_question = vpanel_options("ask_question");
	$editor_question_details = vpanel_options("editor_question_details");
	$custom_permission = vpanel_options("custom_permission");
	$pay_ask = vpanel_options("pay_ask");
	$active_coupons = vpanel_options("active_coupons");
	$coupons = get_option("coupons");
	$free_coupons = vpanel_options("free_coupons");
	$currency_code = vpanel_options("currency_code");
	$currency_code = (isset($currency_code) && $currency_code != ""?$currency_code:"USD");
	$payment_group = vpanel_options("payment_group");
	
	if (is_user_logged_in) {
		$user_get_current_user_id = get_current_user_id();
		$user_is_login = get_userdata($user_get_current_user_id);
		$user_login_group = key($user_is_login->caps);
		$roles = $user_is_login->allcaps;
	}
	
	if (($custom_permission == 1 && is_user_logged_in && empty($roles["ask_question"])) || ($custom_permission == 1 && !is_user_logged_in && $ask_question != 1)) {
		$out .= '<div class="note_error"><strong>'.__("Sorry, you do not have a permission to add a question.","vbegy").'</strong></div>';
		if (!is_user_logged_in) {
			$out .= '<div class="form-style form-style-3"><div class="note_error"><strong>'.__("You must login to ask question.","vbegy").'</strong></div>'.do_shortcode("[ask_login register_2='yes']").'</div>';
		}
	}else if (!is_user_logged_in && $ask_question_no_register != 1) {
		$out .= '<div class="form-style form-style-3"><div class="note_error"><strong>'.__("You must login to ask question.","vbegy").'</strong></div>'.do_shortcode("[ask_login register_2='yes']").'</div>';
	}else {
		if (!is_user_logged_in && $pay_ask == 1) {
			$out .= '<div class="form-style form-style-3"><div class="note_error"><strong>'.__("You must login to ask question.","vbegy").'</strong></div>'.do_shortcode("[ask_login register_2='yes']").'</div>';
		}else {
			$_allow_to_ask = get_user_meta($user_get_current_user_id,$user_get_current_user_id."_allow_to_ask",true);
			if (isset($_POST["process"]) && $_POST["process"] == "ask") {
				/* Number allow to ask question */
				if ($_allow_to_ask == "") {
					$_allow_to_ask = 0;
				}
				$_allow_to_ask++;
				update_user_meta($user_get_current_user_id,$user_get_current_user_id."_allow_to_ask",$_allow_to_ask);
				wp_safe_redirect(esc_url(get_page_link(vpanel_options('add_question'))));
				die();
			}
			
			if (isset($_allow_to_ask) && (int)$_allow_to_ask < 1 && $pay_ask == 1 && !isset($payment_group[$user_login_group])) {
				$pay_ask_payment = $last_payment = (int)vpanel_options("pay_ask_payment");
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
									}else if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "discount" && (int)$coupons_v["coupon_amount"] > $pay_ask_payment) {
										echo '<div class="alert-message error"><p>'.__("This coupon is not valid.","vbegy").'</p></div>';
									}else {
										if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "percent") {
											$the_discount = ($pay_ask_payment*$coupons_v["coupon_amount"])/100;
											$last_payment = $pay_ask_payment-$the_discount;
										}else if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "discount") {
											$last_payment = $pay_ask_payment-$coupons_v["coupon_amount"];
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
				
				echo '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Pay to ask","vbegy").'</span><br>'.__("Please make a payment to allow to be able to add a question.","vbegy").' "'.$last_payment." ".$currency_code.'"</p></div>';
				
				if (isset($coupons) && is_array($coupons) && $free_coupons == 1 && $active_coupons == 1) {
					foreach ($coupons as $coupons_k => $coupons_v) {
						$pay_ask_payments = $last_payments = (int)vpanel_options("pay_ask_payment");
						if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "percent") {
							$the_discount = ($pay_ask_payments*$coupons_v["coupon_amount"])/100;
							$last_payments = $pay_ask_payments-$the_discount;
						}else if (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "discount") {
							$last_payments = $pay_ask_payments-$coupons_v["coupon_amount"];
						}
						
						if ($last_payments <= 0) {
							if (isset($coupons_v["coupon_date"]) && $coupons_v["coupon_date"] != "") {
								$coupons_v["coupon_date"] = !is_numeric($coupons_v["coupon_date"]) ? strtotime($coupons_v["coupon_date"]):$coupons_v["coupon_date"];
							}
							
							if ((isset($coupons_v["coupon_date"]) && $coupons_v["coupon_date"] != "" && current_time( 'timestamp' ) > $coupons_v["coupon_date"]) && (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "percent" && (int)$coupons_v["coupon_amount"] > 100) && (isset($coupons_v["coupon_type"]) && $coupons_v["coupon_type"] == "discount" && (int)$coupons_v["coupon_amount"] > $pay_ask_payments)) {
								
							}else {
								echo '<div class="alert-message warning"><i class="icon-ok"></i><p><span>'.__("Free","vbegy").'</span><br>'.__("Ask a free question? Add this coupon.","vbegy").' "'.$coupons_v["coupon_name"].'"</p></div>';
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
							<input type="hidden" name="CatDescription" value="'.__("Ask a new question","vbegy").'">
							<input type="hidden" name="item_number" value="pay_ask">
							<input type="hidden" name="payment" value="'.$last_payment.'">
							<input type="hidden" name="quantity" value="1">
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
						<form method="post" action="'.esc_url(get_page_link(vpanel_options('add_question'))).'">
							<input type="submit" class="button" value="'.__("Process","vbegy").'">
							<input type="hidden" name="process" value="ask">';
							if (isset($ask_find_coupons) && $ask_find_coupons != "" && $active_coupons == 1) {
								echo '<input type="hidden" name="coupon" value="'.esc_attr($_POST["coupon_name"]).'">';
							}
						echo '</form>
					</div>';
				}
			}else {
				$title_question = vpanel_options("title_question");
				$question_points_active = vpanel_options("question_points_active");
				$question_points = vpanel_options("question_points");
				$points = get_user_meta(get_current_user_id(),"points",true);
				$points = ($points != ""?$points:0);
				$poll_question = vpanel_options("poll_question");
				$tags_question = vpanel_options("tags_question");
				$attachment_question = vpanel_options("attachment_question");
				if ($_POST) {
					$post_type = (isset($_POST["post_type"]) && $_POST["post_type"] != ""?esc_html($_POST["post_type"]):"");
				}else {
					$post_type = "";
				}
				
				if (isset($_POST["post_type"]) && $_POST["post_type"] == "add_question") {
					do_action('new_post');
				}
				
				if (($question_points_active == 0 || ($points >= $question_points && $question_points_active == 1)) && $post_type != "edit_question" && $post_type != "add_post") {
					$users_by_id = $get_user_id = 0;
					if (isset($_GET["user_id"]) && $_GET["user_id"] != "") {
						$get_user_id = (int)$_GET["user_id"];
						$get_users_by_id = get_users(array("include" => array($get_user_id)));
						if (isset($get_users_by_id) && !empty($get_users_by_id)) {
							$users_by_id = 1;
						}
					}
					
					if (is_user_logged_in && $user_get_current_user_id == $get_user_id) {
						echo '<div class="alert-message error"><p>'.__("You can't ask yourself.","vbegy").'</p></div>';
					}else {
						$out .= '<div class="form-posts"><div class="form-style form-style-3 question-submit">
							<div class="ask_question">
								<div '.(!is_user_logged_in?"class='if_no_login'":"").'>';
									$rand_q = rand(1,1000);?>
									<script type="text/javascript">
										jQuery(function () {
											jQuery("input.question_poll").each(function () {
												var poll = jQuery(this);
												if (poll.is(':checked')) {
													poll.parent().parent().find(".poll_options").slideDown(500);
												}else {
													poll.parent().parent().find(".poll_options").slideUp(500);
												}
												
												poll.click(function () {
													var poll = jQuery(this);
													if (poll.is(':checked')) {
														poll.parent().parent().find(".poll_options").slideDown(500);
													}else {
														poll.parent().parent().find(".poll_options").slideUp(500);
													}
												});
											});
										});
									</script><?php
									if ($question_points_active == 1) {
										$out .= '<div class="alert-message info"><i class="icon-ok"></i><p><span>'.__("Note","vbegy").'</span><br>'.sprintf(__("Will lose %s points when adding a new question.","vbegy"),$question_points).'</p></div>';
									}
									$out .= '
									<form class="new-question-form" method="post" enctype="multipart/form-data">
										<div class="note_error display"></div>
										<div class="form-inputs clearfix">';
											$username_email_no_register = vpanel_options("username_email_no_register");
											if (!is_user_logged_in && $ask_question_no_register == 1 && $username_email_no_register == 1) {
												$out .= '<p>
													<label for="question-username-'.$rand_q.'" class="required">'.__("Username","vbegy").'<span>*</span></label>
													<input name="username" id="question-username-'.$rand_q.'" class="the-username" type="text" value="'.(isset($posted['username'])?$posted['username']:'').'">
													<span class="form-description">'.__("Please type your username .","vbegy").'</span>
												</p>
												
												<p>
													<label for="question-email-'.$rand_q.'" class="required">'.__("E-Mail","vbegy").'<span>*</span></label>
													<input name="email" id="question-email-'.$rand_q.'" class="the-email" type="text" value="'.(isset($posted['email'])?$posted['email']:'').'">
													<span class="form-description">'.__("Please type your E-Mail .","vbegy").'</span>
												</p>';
											}
											
											$out .= apply_filters('askme_add_question_before_title',false,$posted);
											
											if ($title_question == 1) {
												$out .= '<p>
													<label for="question-title-'.$rand_q.'" class="required">'.__("Question Title","vbegy").'<span>*</span></label>
													<input name="title" id="question-title-'.$rand_q.'" class="the-title" type="text" value="'.(isset($posted['title'])?ask_kses_stip($posted['title']):(isset($_POST["title"])?ask_kses_stip($_POST["title"]):"")).'">
													<span class="form-description">'.__("Please choose an appropriate title for the question to answer it even easier .","vbegy").'</span>
												</p>';
											}
											if ($users_by_id == 0) {
												$category_question = vpanel_options("category_question");
												$category_question_required = vpanel_options("category_question_required");
												if ($category_question == 1) {
													$out .= '<div class="div_category">
														<label for="'.ask_question_category.'-'.$rand_q.'"'.($category_question_required == 1?' class="required"':'').'>'.__("Category","vbegy").($category_question_required == 1?'<span>*</span>':'').'</label>
														'.ask_me_select_categories($rand_q,$posted['category'],null,'',ask_question_category).'
														<span class="form-description">'.__("Please choose the appropriate section so easily search for your question .","vbegy").'</span>
													</div>';
												}
												
												if ($tags_question == 1) {
													$out .= '<p>
														<label for="question_tags-'.$rand_q.'">'.__("Tags","vbegy").'</label>
														<input type="text" class="input question_tags" name="question_tags" id="question_tags-'.$rand_q.'" value="'.(isset($posted['question_tags'])?$posted['question_tags']:'').'" data-seperator=",">
														<span class="form-description">'.__("Please choose  suitable Keywords Ex : ","vbegy").'<span class="color">'.__("question , poll","vbegy").'</span> .</span>
													</p>';
												}
											
												if ($poll_question == 1) {
													$out .= '<p class="question_poll_p">
														<label for="question_poll-'.$rand_q.'">'.__("Poll","vbegy").'</label>
														<input type="checkbox" id="question_poll-'.$rand_q.'" class="question_poll" value="1" name="question_poll" '.(isset($posted['question_poll']) && $posted['question_poll'] == 1?"checked='checked'":"").'>
														<span class="question_poll">'.__("This question is a poll ?","vbegy").'</span>
														<span class="poll-description">'.__("If you want to be doing a poll click here .","vbegy").'</span>
													</p>
													
													<div class="clearfix"></div>
													<div class="poll_options">
														<p class="form-submit add_poll">
															<button type="button" class="button color small submit add_poll_button add_poll_button_js"><i class="icon-plus"></i>'.__("Add Field","vbegy").'</button>
														</p>
														<ul class="question_poll_item question_polls_item">';
															if (isset($_POST['ask']) && is_array($_POST['ask'])) {
																foreach($_POST['ask'] as $ask) {
																	if (stripslashes($ask['title']) != "") {
																		$out .= '<li id="poll_li_'.(int)$ask['id'].'">
																			<div class="poll-li">
																				<p><input id="ask['.(int)$ask['id'].'][title]" class="ask" name="ask['.(int)$ask['id'].'][title]" value="'.stripslashes($ask['title']).'" type="text"></p>
																				<input id="ask['.(int)$ask['id'].'][value]" name="ask['.(int)$ask['id'].'][value]" value="" type="hidden">
																				<input id="ask['.(int)$ask['id'].'][id]" name="ask['.(int)$ask['id'].'][id]" value="'.(int)$ask['id'].'" type="hidden">
																				<div class="del-poll-li"><i class="icon-remove"></i></div>
																				<div class="move-poll-li"><i class="icon-fullscreen"></i></div>
																			</div>
																		</li>';
																	}
																}
															}else {
																$out .= '<li id="poll_li_1">
																	<div class="poll-li">
																		<p><input id="ask[1][title]" class="ask" name="ask[1][title]" value="" type="text"></p>
																		<input id="ask[1][value]" name="ask[1][value]" value="" type="hidden">
																		<input id="ask[1][id]" name="ask[1][id]" value="1" type="hidden">
																		<div class="del-poll-li"><i class="icon-remove"></i></div>
																		<div class="move-poll-li"><i class="icon-fullscreen"></i></div>
																	</div>
																</li>';
															}
														$out .= '</ul>
														<script> var nextli = '.(isset($_POST['ask']) && is_array($_POST['ask'])?count($_POST['ask'])+1:"2").';</script>
														<div class="clearfix"></div>
													</div>';
												}
												
												if ($attachment_question == 1) {
													$out .= '<label>'.__("Attachment","vbegy").'</label>
													<div class="question-multiple-upload">
														<div class="clearfix"></div>
														<p class="form-submit add_poll">
															<button type="button" class="button color small submit add_poll_button add_upload_button_js"><i class="icon-plus"></i>'.__("Add Field","vbegy").'</button>
														</p>
														<ul class="question_poll_item question_upload_item"></ul>
														<script> var next_attachment = 1;</script>
														<div class="clearfix"></div>
													</div>';
												}
												
												$featured_image_question = vpanel_options('featured_image_question');
												if ($featured_image_question == 1) {
													$out .= '<label for="featured_image-'.$rand_q.'">'.__("Featured image","vbegy").'</label>
													<div class="fileinputs">
														<input type="file" class="file" name="featured_image" id="featured_image-'.$rand_q.'">
														<div class="fakefile">
															<button type="button" class="button small margin_0">'.__("Select file","vbegy").'</button>
															<span><i class="icon-arrow-up"></i>'.__("Browse","vbegy").'</span>
														</div>
													</div>';
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
										$out .= '
										</div>
										<div class="details-area">
											<label for="question-details-'.$rand_q.'" '.($comment_question == "required"?'class="required"':'').'>'.__("Details","vbegy").($comment_question == "required"?'<span>*</span>':'').'</label>';
											
											if ($editor_question_details == 1) {
												ob_start();
												wp_editor((isset($posted['comment'])?ask_kses_stip_wpautop($posted['comment']):(isset($_POST["comment"])?wp_kses_post($_POST["comment"]):"")),"question-details-".$rand_q,$settings);
												$editor_contents = ob_get_clean();
												
												$out .= '<div class="the-details the-textarea">'.$editor_contents.'</div>';
											}else {
												$out .= '<textarea name="comment" id="question-details-'.$rand_q.'" class="the-textarea" aria-required="true" cols="58" rows="8">'.(isset($posted['comment'])?ask_kses_stip($posted['comment']):(isset($_POST["comment"])?ask_kses_stip($_POST["comment"]):"")).'</textarea>';
											}
										$out .= '<div class="clearfix"></div></div>
										
										<div class="form-inputs clearfix">';
											if ($users_by_id == 0 && vpanel_options("video_desc_active") == 1) {
												$out .= '
												<p class="question_poll_p">
													<label for="video_description-'.$rand_q.'">'.__("Video description","vbegy").'</label>
													<input type="checkbox" id="video_description-'.$rand_q.'" class="video_description_input" name="video_description" value="1" '.(isset($posted['video_description']) && $posted['video_description'] == 1?"checked='checked'":"").'>
													<span class="question_poll">'.__("Do you need a video to description the problem better ?","vbegy").'</span>
												</p>
												
												<div class="video_description" '.(isset($posted['video_description']) && $posted['video_description'] == 1?"style='display:block;'":"").'>
													<p>
														<label for="video_type-'.$rand_q.'">'.__("Video type","vbegy").'</label>
														<span class="styled-select">
															<select id="video_type-'.$rand_q.'" name="video_type">
																<option value="youtube" '.(isset($posted['video_type']) && $posted['video_type'] == "youtube"?' selected="selected"':'').'>Youtube</option>
																<option value="vimeo" '.(isset($posted['video_type']) && $posted['video_type'] == "vimeo"?' selected="selected"':'').'>Vimeo</option>
																<option value="daily" '.(isset($posted['video_type']) && $posted['video_type'] == "daily"?' selected="selected"':'').'>Dialymotion</option>
															</select>
														</span>
														<span class="form-description">'.__("Choose from here the video type .","vbegy").'</span>
													</p>
													
													<p>
														<label for="video_id-'.$rand_q.'">'.__("Video ID","vbegy").'</label>
														<input name="video_id" id="video_id-'.$rand_q.'" class="video_id" type="text" value="'.(isset($posted['video_id'])?$posted['video_id']:'').'">
														<span class="form-description">'.__("Put here the video id : https://www.youtube.com/watch?v=sdUUx5FdySs EX : 'sdUUx5FdySs'.","vbegy").'</span>
													</p>
												</div>';
											}
										
										$active_notified = vpanel_options("active_notified");
										if ($active_notified == 1) {
											$out .= '
											<p class="question_poll_p">
												<label for="remember_answer-'.$rand_q.'">'.__("Notified","vbegy").'</label>
												<input type="checkbox" id="remember_answer-'.$rand_q.'" name="remember_answer" value="1" '.(isset($posted['remember_answer']) && $posted['remember_answer'] == 1?"checked='checked'":(empty($posted)?"checked='checked'":"")).'>
												<span class="question_poll">'.__("Notified by e-mail at incoming answers.","vbegy").'</span>
											</p>';
										}
										
										$private_question = vpanel_options("private_question");
										if (is_user_logged_in && $private_question == 1) {
											$out .= '
											<p class="question_poll_p">
												<label for="private_question-'.$rand_q.'">'.__("Private question","vbegy").'</label>
												<input type="checkbox" id="private_question-'.$rand_q.'" name="private_question" value="1" '.(isset($posted['private_question']) && $posted['private_question'] == 1?"checked='checked'":"").'>
												<span class="question_poll">'.__("Active this question as a private question.","vbegy").'</span>
											</p>';
										}
										
										$anonymously_question = vpanel_options("anonymously_question");
										if ($anonymously_question == 1 && $username_email_no_register == 1) {
											$out .= '
											<p class="question_poll_p">
												<label for="anonymously_question-'.$rand_q.'">'.__("Ask Anonymously","vbegy").'</label>
												<input type="checkbox" class="ask_anonymously" id="anonymously_question-'.$rand_q.'" name="anonymously_question" value="1" '.(isset($posted['anonymously_question']) && $posted['anonymously_question'] == 1?"checked='checked'":"").'>';
												if (is_user_logged_in) {
													$you_avatar = get_the_author_meta('you_avatar',$user_get_current_user_id);
													$display_name = get_the_author_meta('display_name',$user_get_current_user_id);
													$out .= '<span class="question_poll anonymously_span ask_named'.(empty($posted['anonymously_question'])?' anonymously_span_show':'').'">';
														if ($you_avatar) {
															$out .= askme_user_avatar($you_avatar,25,25,$user_get_current_user_id,$display_name);
														}else {
															$out .= get_avatar($user_get_current_user_id,'25','');
														}
														$out .= '<span>'.$display_name.' '.esc_html__("asks","vbegy").'</span>
													</span>
													<span class="question_poll anonymously_span ask_none'.(isset($posted['anonymously_question']) && $posted['anonymously_question'] == 1?' anonymously_span_show':'').'">
														<img alt="'.esc_html__("Anonymous","vbegy").'" src="'.get_template_directory_uri().'/images/avatar.png">
														<span>'.esc_html__("Anonymous asks","vbegy").'</span>
													</span>';
												}else {
													$out .= '<span class="question_poll">'.__("Anonymous asks","vbegy").'</span>';
												}
											$out .= '</p>';
										}
										
										$the_captcha = vpanel_options("the_captcha");
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
										
										$terms_active = vpanel_options("terms_active");
										$terms_link = vpanel_options("terms_link");
										if ($terms_active == 1) {
											$terms_link_page = vpanel_options("terms_page");
											$out .= '<p class="question_poll_p">
												<label for="agree_terms-'.$rand_q.'" class="required">'.__("Terms","vbegy").'<span>*</span></label>
												<input type="checkbox" id="agree_terms-'.$rand_q.'" name="agree_terms" value="1" '.(isset($posted['agree_terms']) && $posted['agree_terms'] == 1?"checked='checked'":"").'>
												<span class="question_poll">'.sprintf(wp_kses(__("By posting your question, you agree to the <a target='%s' href='%s'>terms of service</a>.","vbegy"),array('a' => array('href' => array(),'target' => array()))),(vpanel_options("terms_active_target") == "same_page"?"_self":"_blank"),(isset($terms_link) && $terms_link != ""?$terms_link:(isset($terms_link_page) && $terms_link_page != ""?get_page_link($terms_link_page):"#"))).'</span>
											</p>';
										}
										
										$out .= '</div>
										
										<p class="form-submit">
											<input type="hidden" name="post_type" value="add_question">';
											if (isset($a["type"]) && $a["type"] == "popup") {
												$out .= '<input type="hidden" name="form_type" value="question-popup">';
											}else {
												$out .= '<input type="hidden" name="form_type" value="add_question">';
											}
											if ($users_by_id == 1) {
												$out .= '<input type="hidden" name="user_id" value="'.$get_user_id.'">';
											}
											$out .= '<input type="submit" value="'.__("Publish Your Question","vbegy").'" class="button color small submit add_qu publish-question">
										</p>
									
									</form>
								</div>
							</div>
						</div></div>';
					}
				}else {
					$out .= sprintf(__("Sorry do not have the minimum points Please do answer questions, even gaining points ( The minimum points = %s ) .","vbegy"),$question_points);
				}
			}
		}
	}
	return $out;
}
/* edit_question_shortcode */
add_shortcode('edit_question', 'edit_question_shortcode');
function edit_question_shortcode($atts, $content = null) {
	global $posted,$settings;
	$poll_question = vpanel_options("poll_question");
	$tags_question = vpanel_options("tags_question");
	$editor_question_details = vpanel_options("editor_question_details");
	$out = '';
	if (!is_user_logged_in) {
		$out .= '<div class="form-style form-style-3"><div class="note_error"><strong>'.__("You must login to edit question .","vbegy").'</strong></div>'.do_shortcode("[ask_login register_2='yes']").'</div>';
	}else {
		$get_question = (int)$_GET["q"];
		$get_post_q = get_post($get_question);
		$q_tag = "";
		if ($terms = wp_get_object_terms( $get_question, 'question_tags' )) :
			$terms_array = array();
			foreach ($terms as $term) :
				$terms_array[] = $term->name;
				$q_tag = implode(' , ', $terms_array);
			endforeach;
		endif;
		
		$question_category = wp_get_post_terms($get_question,ask_question_category,array("fields" => "ids"));
		if (isset($_POST["post_type"]) && $_POST["post_type"] == "edit_question") {
			do_action('edit_question');
		}
		$get_question_user_id = get_post_meta($get_question,"user_id",true);
		
		$out .= '<div class="form-posts"><div class="form-style form-style-3 question-submit">
			<div class="ask_question">
				<div '.(!is_user_logged_in?"class='if_no_login'":"").'>';
					$rand_e = rand(1,1000);
					$out .= '
					<form class="new-question-form" method="post" enctype="multipart/form-data">
						<div class="note_error display"></div>
						<div class="form-inputs clearfix">';
							
							$out .= apply_filters('askme_edit_question_before_title',false,$posted,$get_question);
							
							$title_question = vpanel_options("title_question");
							if ($title_question == 1) {
								$out .= '<p>
									<label for="question-title-'.$rand_e.'" class="required">'.__("Question Title","vbegy").'<span>*</span></label>
									<input name="title" id="question-title-'.$rand_e.'" class="the-title" type="text" value="'.(isset($posted['title'])?ask_kses_stip($posted['title']):ask_kses_stip($get_post_q->post_title)).'">
									<span class="form-description">'.__("Please choose an appropriate title for the question to answer it even easier .","vbegy").'</span>
								</p>';
							}
							
							if (empty($get_question_user_id)) {
								$category_question = vpanel_options("category_question");
								$category_question_required = vpanel_options("category_question_required");
								if ($category_question == 1) {
									$out .= '<div class="div_category">
										<label for="'.ask_question_category.'-'.$rand_e.'"'.($category_question_required == 1?' class="required"':'').'>'.__("Category","vbegy").($category_question_required == 1?'<span>*</span>':'').'</label>
										'.ask_me_select_categories($rand_e,(isset($posted['category'])?$posted['category']:(isset($question_category) && !empty($question_category)?$question_category:"")),null,$get_question,ask_question_category).'
										<span class="form-description">'.__("Please choose the appropriate section so easily search for your question .","vbegy").'</span>
									</div>';
								}
								
								if ($tags_question == 1) {
									$out .= '<p>
										<label for="question_tags-'.$rand_e.'">'.__("Tags","vbegy").'</label>
										<input type="text" class="input question_tags" name="question_tags" id="question_tags-'.$rand_e.'" value="'.(isset($posted['question_tags'])?$posted['question_tags']:$q_tag).'" data-seperator=",">
										<span class="form-description">'.__("Please choose  suitable Keywords Ex : ","vbegy").'<span class="color">'.__("question , poll","vbegy").'</span> .</span>
									</p>';
								}
							
								if ($poll_question == 1) {
									$out .= '<p class="question_poll_p">
										<label for="question_poll-'.$rand_e.'">'.__("Poll","vbegy").'</label>
										<input type="checkbox" id="question_poll-'.$rand_e.'" class="question_poll" value="1" name="question_poll" '.(isset($posted['question_poll']) && $posted['question_poll'] == 1 || get_post_meta($get_question,"question_poll",true) == 1?"checked='checked'":"").'>
										<span class="question_poll">'.__("This question is a poll ?","vbegy").'</span>
										<span class="poll-description">'.__("If you want to be doing a poll click here .","vbegy").'</span>
									</p>
									
									<div class="clearfix"></div>
									<div class="poll_options">
										<p class="form-submit add_poll">
											<button type="button" class="button color small submit add_poll_button add_poll_button_js"><i class="icon-plus"></i>'.__("Add Field","vbegy").'</button>
										</p>
										<ul class="question_poll_item question_polls_item">';
											if (isset($_POST['ask']) && is_array($_POST['ask'])) {
												$q_ask = $_POST['ask'];
											}else {
												$q_ask = get_post_meta($get_question,"ask",true);
											}
											if (isset($q_ask) && is_array($q_ask)) {
												foreach($q_ask as $ask) {
													if (stripslashes($ask['title']) != "") {
														$out .= '<li id="poll_li_'.(int)$ask['id'].'">
															<div class="poll-li">
																<p><input id="ask['.(int)$ask['id'].'][title]" class="ask" name="ask['.(int)$ask['id'].'][title]" value="'.stripslashes($ask['title']).'" type="text"></p>
																<input id="ask['.(int)$ask['id'].'][value]" name="ask['.(int)$ask['id'].'][value]" value="" type="hidden">
																<input id="ask['.(int)$ask['id'].'][id]" name="ask['.(int)$ask['id'].'][id]" value="'.(int)$ask['id'].'" type="hidden">
																<div class="del-poll-li"><i class="icon-remove"></i></div>
																<div class="move-poll-li"><i class="icon-fullscreen"></i></div>
															</div>
														</li>';
													}
												}
											}else {
												$out .= '<li id="poll_li_1">
													<div class="poll-li">
														<p><input id="ask[1][title]" class="ask" name="ask[1][title]" value="" type="text"></p>
														<input id="ask[1][value]" name="ask[1][value]" value="" type="hidden">
														<input id="ask[1][id]" name="ask[1][id]" value="1" type="hidden">
														<div class="del-poll-li"><i class="icon-remove"></i></div>
														<div class="move-poll-li"><i class="icon-fullscreen"></i></div>
													</div>
												</li>';
											}
										$out .= '</ul>
										<script> var nextli = '.(isset($_POST['ask']) && is_array($_POST['ask'])?count($_POST['ask'])+1:"2").';</script>
										<div class="clearfix"></div>
									</div>';
								}
								
								$featured_image_question = vpanel_options('featured_image_question');
								if ($featured_image_question == 1) {
									$out .= '<label for="featured_image-'.$rand_e.'">'.__("Featured image","vbegy").'</label>
									<div class="fileinputs">
										<input type="file" class="file" name="featured_image" id="featured_image-'.$rand_e.'">
										<div class="fakefile">
											<button type="button" class="button small margin_0">'.__("Select file","vbegy").'</button>
											<span><i class="icon-arrow-up"></i>'.__("Browse","vbegy").'</span>
										</div>
									</div>';
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
						$out .= '</div>
						<div class="details-area">
							<label for="question-details-'.$rand_e.'" '.($comment_question == "required"?'class="required"':'').'>'.__("Details","vbegy").($comment_question == "required"?'<span>*</span>':'').'</label>';
							
							if ($editor_question_details == 1) {
								ob_start();
								wp_editor((isset($posted['comment'])?ask_kses_stip_wpautop($posted['comment']):$get_post_q->post_content),"question-details-".$rand_e,$settings);
								$editor_contents = ob_get_clean();
								
								$out .= '<div class="the-details the-textarea">'.$editor_contents.'</div>';
							}else {
								$out .= '<textarea name="comment" id="question-details-'.$rand_e.'" class="the-textarea" aria-required="true" cols="58" rows="8">'.(isset($posted['comment'])?ask_kses_stip($posted['comment']):ask_kses_stip($get_post_q->post_content,"yes")).'</textarea>';
							}
						$out .= '<div class="clearfix"></div></div>
						
						<div class="form-inputs clearfix">';
							if (empty($get_question_user_id)) {
								$q_video_description = get_post_meta($get_question,"video_description",true);
								$q_video_type = get_post_meta($get_question,"video_type",true);
								$q_video_id = get_post_meta($get_question,"video_id",true);
								
								if (vpanel_options("video_desc_active") == 1) {
									$out .= '
									<p class="question_poll_p">
										<label for="video_description-'.$rand_e.'">'.__("Video description","vbegy").'</label>
										<input type="checkbox" id="video_description-'.$rand_e.'" class="video_description_input" name="video_description" value="1" '.(isset($posted['video_description']) && $posted['video_description'] == 1 || $q_video_description == 1?"checked='checked'":"").'>
										<span class="question_poll">'.__("Do you need a video to description the problem better ?","vbegy").'</span>
									</p>
									
									<div class="video_description" '.(isset($posted['video_description']) && $posted['video_description'] == 1 || $q_video_description == 1?"style='display:block;'":"").'>
										<p>
											<label for="video_type-'.$rand_e.'">'.__("Video type","vbegy").'</label>
											<span class="styled-select">
												<select id="video_type-'.$rand_e.'" class="video_type" name="video_type">
													<option value="youtube" '.(isset($posted['video_type']) && $posted['video_type'] == "youtube" || $q_video_type == "youtube"?' selected="selected"':'').'>Youtube</option>
													<option value="vimeo" '.(isset($posted['video_type']) && $posted['video_type'] == "vimeo" || $q_video_type == "vimeo"?' selected="selected"':'').'>Vimeo</option>
													<option value="daily" '.(isset($posted['video_type']) && $posted['video_type'] == "daily" || $q_video_type == "daily"?' selected="selected"':'').'>Dialymotion</option>
												</select>
											</span>
											<span class="form-description">'.__("Choose from here the video type .","vbegy").'</span>
										</p>
										
										<p>
											<label for="video_id-'.$rand_e.'">'.__("Video ID","vbegy").'</label>
											<input name="video_id" id="video_id-'.$rand_e.'" class="video_id" type="text" value="'.(isset($posted['video_id'])?$posted['video_id']:$q_video_id).'">
											<span class="form-description">'.__("Put here the video id : https://www.youtube.com/watch?v=sdUUx5FdySs EX : 'sdUUx5FdySs'.","vbegy").'</span>
										</p>
									</div>';
								}
							}
							
							$active_notified = vpanel_options("active_notified");
							if ($active_notified == 1) {
								$q_remember_answer = get_post_meta($get_question,"remember_answer",true);
								$out .= '<p class="question_poll_p">
									<label for="remember_answer-'.$rand_e.'">'.__("Notified","vbegy").'</label>
									<input type="checkbox" id="remember_answer-'.$rand_e.'" class="remember_answer" name="remember_answer" value="1" '.(isset($posted['remember_answer']) && $posted['remember_answer'] == 1 || $q_remember_answer == 1?"checked='checked'":"").'>
									<span class="question_poll">'.__("Notified by e-mail at incoming answers.","vbegy").'</span>
								</p>';
							}
							
							$private_question = vpanel_options("private_question");
							if ($private_question == 1) {
								$q_private_question = get_post_meta($get_question,"private_question",true);
								$out .= '<p class="question_poll_p">
									<label for="private_question-'.$rand_e.'">'.__("Private question","vbegy").'</label>
									<input type="checkbox" id="private_question-'.$rand_e.'" class="private_question" name="private_question" value="1" '.(isset($posted['private_question']) && $posted['private_question'] == 1 || $q_private_question == 1?"checked='checked'":"").'>
									<span class="question_poll">'.__("Active this question as a private question.","vbegy").'</span>
								</p>';
							}
						
						$out .= '</div>
						<p class="form-submit">
							<input type="hidden" name="ID" value="'.$get_question.'">
							<input type="hidden" name="post_type" value="edit_question">
							<input type="submit" value="'.__("Edit Your Question","vbegy").'" class="button color small submit add_qu publish-question">
						</p>
					
					</form>
				</div>
			</div>
		</div></div>';
	}
	return $out;
}
/* add_post_shortcode */
add_shortcode('add_post', 'add_post_shortcode');
function add_post_shortcode($atts, $content = null) {
	global $posted,$settings;
	$add_post_no_register = vpanel_options("add_post_no_register");
	$add_post = vpanel_options("add_post");
	$custom_permission = vpanel_options("custom_permission");
	$editor_post_details = vpanel_options("editor_post_details");
	if (is_user_logged_in) {
		$user_get_current_user_id = get_current_user_id();
		$user_is_login = get_userdata($user_get_current_user_id);
		$user_login_group = key($user_is_login->caps);
		$roles = $user_is_login->allcaps;
	}
	
	$out = '';
	if (($custom_permission == 1 && is_user_logged_in && empty($roles["add_post"])) || ($custom_permission == 1 && !is_user_logged_in && $add_post != 1)) {
		$out .= '<div class="note_error"><strong>'.__("Sorry, you do not have a permission to add a post .","vbegy").'</strong></div>';
	}else if (!is_user_logged_in && $add_post_no_register != 1) {
		$out .= '<div class="form-style form-style-3"><div class="note_error"><strong>'.__("You must login to add post .","vbegy").'</strong></div>'.do_shortcode("[ask_login register_2='yes']").'</div>';
	}else {
		$tags_post = vpanel_options("tags_post");
		$attachment_post = vpanel_options("attachment_post");
		if ($_POST) {
			$post_type = (isset($_POST["post_type"]) && $_POST["post_type"] != ""?esc_html($_POST["post_type"]):"");
		}else {
			$post_type = "";
		}
		
		if (isset($_POST["post_type"]) && $_POST["post_type"] == "add_post") {
			do_action('new_post');
		}
		
		if ($post_type != "edit_post" && $post_type != "add_question") {
			$out .= '<div class="form-posts"><div class="form-style form-style-3 post-submit">
				<div class="add_post">
					<div '.(!is_user_logged_in?"class='if_no_login'":"").'>';
						$rand_p = rand(1,1000);
						$out .= '
						<form class="new-post-form" method="post" enctype="multipart/form-data">
							<div class="note_error display"></div>
							<div class="form-inputs clearfix">';
								if (!is_user_logged_in && $add_post_no_register == 1) {
									$out .= '<p>
										<label for="post-username-'.$rand_p.'" class="required">'.__("Username","vbegy").'<span>*</span></label>
										<input name="username" id="post-username-'.$rand_p.'" class="the-username" type="text" value="'.(isset($posted['username'])?$posted['username']:'').'">
										<span class="form-description">'.__("Please type your username .","vbegy").'</span>
									</p>
									
									<p>
										<label for="post-email-'.$rand_p.'" class="required">'.__("E-Mail","vbegy").'<span>*</span></label>
										<input name="email" id="post-email-'.$rand_p.'" class="the-email" type="text" value="'.(isset($posted['email'])?$posted['email']:'').'">
										<span class="form-description">'.__("Please type your E-Mail .","vbegy").'</span>
									</p>';
								}
								$out .= '<p>
									<label for="post-title-'.$rand_p.'" class="required">'.__("Post Title","vbegy").'<span>*</span></label>
									<input name="title" id="post-title-'.$rand_p.'" class="the-title" type="text" value="'.(isset($posted['title'])?ask_kses_stip($posted['title']):'').'">
									<span class="form-description">'.__("Please choose an appropriate title for the post .","vbegy").'</span>
								</p>';
								
								if ($tags_post == 1) {
									$out .= '<p>
										<label for="post_tag-'.$rand_p.'">'.__("Tags","vbegy").'</label>
										<input type="text" class="input post_tag" name="post_tag" id="post_tag-'.$rand_p.'" value="'.(isset($posted['post_tag'])?$posted['post_tag']:'').'" data-seperator=",">
										<span class="form-description">'.__("Please choose  suitable Keywords Ex : ","vbegy").'<span class="color">'.__("post , video","vbegy").'</span> .</span>
									</p>';
								}
								
								$category_post = vpanel_options("category_post");
								$category_post_required = vpanel_options("category_post_required");
								$category_post = $category_post_required = 1;
								if ($category_post == 1) {
									$out .= '<div class="div_category">
										<label for="post-category-'.$rand_p.'"'.($category_post_required == 1?' class="required"':'').'>'.__("Category","vbegy").($category_post_required == 1?'<span>*</span>':'').'</label>
										'.ask_me_select_categories($rand_p,(isset($posted['category'])?$posted['category']:(isset($_POST['category'])?$_POST['category']:"")),null,'','category').'
										<span class="form-description">'.__("Please choose the appropriate section so easily search for your post .","vbegy").'</span>
									</div>';
								}
								
								if ($attachment_post == 1) {
									$out .= '<label for="attachment-'.$rand_p.'">'.__("Attachment","vbegy").'</label>
									<div class="fileinputs">
										<input type="file" class="file" name="attachment" id="attachment-'.$rand_p.'">
										<div class="fakefile">
											<button type="button" class="button small margin_0">'.__("Select file","vbegy").'</button>
											<span><i class="icon-arrow-up"></i>'.__("Browse","vbegy").'</span>
										</div>
									</div>';
								}
								
							$out .= '
							</div>
							<div class="details-area">
								<label for="post-details-'.$rand_p.'" '.(vpanel_options("content_post") == 1?'class="required"':'').'>'.__("Details","vbegy").(vpanel_options("content_post") == 1?'<span>*</span>':'').'</label>';
								
								if ($editor_post_details == 1) {
									ob_start();
									wp_editor((isset($posted['comment'])?ask_kses_stip_wpautop($posted['comment']):""),"post-details-".$rand_p,$settings);
									$editor_contents = ob_get_clean();
									
									$out .= '<div class="the-details the-textarea">'.$editor_contents.'</div>';
								}else {
									$out .= '<textarea name="comment" id="post-details-'.$rand_p.'" class="the-textarea" aria-required="true" cols="58" rows="8">'.(isset($posted['comment'])?ask_kses_stip($posted['comment']):"").'</textarea>';
								}
							$out .= '<div class="clearfix"></div></div>
							
							<div class="form-inputs clearfix">';
								
							$the_captcha_post = vpanel_options("the_captcha_post");
							$captcha_style = vpanel_options("captcha_style");
							$captcha_question = vpanel_options("captcha_question");
							$captcha_answer = vpanel_options("captcha_answer");
							$show_captcha_answer = vpanel_options("show_captcha_answer");
							if ($the_captcha_post == 1) {
								if ($captcha_style == "question_answer") {
									$out .= "
									<p class='ask_captcha_p'>
										<label for='ask_captcha-".$rand_p."' class='required'>".__("Captcha","vbegy")."<span>*</span></label>
										<input size='10' id='ask_captcha-".$rand_p."' name='ask_captcha' class='ask_captcha captcha_answer' value='' type='text'>
										<span class='ask_captcha_span'>".$captcha_question.($show_captcha_answer == 1?" ( ".$captcha_answer." )":"")."</span>
									</p>";
								}else {
									$out .= "
									<p class='ask_captcha_p'>
										<label for='ask_captcha_".$rand_p."' class='required'>".__("Captcha","vbegy")."<span>*</span></label>
										<input size='10' id='ask_captcha_".$rand_p."' name='ask_captcha' class='ask_captcha' value='' type='text'><img class='ask_captcha_img' src='".get_template_directory_uri()."/captcha/create_image.php' alt='".__("Captcha","vbegy")."' title='".__("Click here to update the captcha","vbegy")."' onclick=";$out .='"javascript:ask_get_captcha';$out .="('".get_template_directory_uri()."/captcha/create_image.php', 'ask_captcha_img_".$rand_p."');";$out .='"';$out .=" id='ask_captcha_img_".$rand_p."'>
										<span class='ask_captcha_span'>".__("Click on image to update the captcha .","vbegy")."</span>
									</p>";
								}
							}
							
							$out .= '</div>
							
							<p class="form-submit margin_0">
								<input type="hidden" name="post_type" value="add_post">
								<input type="submit" value="'.__("Publish Your Post","vbegy").'" class="button color small submit add_qu publish-post">
							</p>
						
						</form>
					</div>
				</div>
			</div></div>';
		}
	}
	return $out;
}
/* vpanel_edit_post_shortcode */
add_shortcode('vpanel_edit_post', 'vpanel_edit_post_shortcode');
function vpanel_edit_post_shortcode($atts, $content = null) {
	global $posted,$settings;
	$tags_post = vpanel_options("tags_post");
	$attachment_post = vpanel_options("attachment_post");
	$editor_post_details = vpanel_options("editor_post_details");
	
	$out = '';
	if (!is_user_logged_in) {
		$out .= '<div class="form-style form-style-3"><div class="note_error"><strong>'.__("You must login to add post .","vbegy").'</strong></div>'.do_shortcode("[ask_login register_2='yes']").'</div>';
	}else {
		$get_post = (int)$_GET["edit_post"];
		$get_post_p = get_post($get_post);
		$p_tag = "";
		if ($terms = wp_get_object_terms( $get_post, 'post_tag' )) :
			$terms_array = array();
			foreach ($terms as $term) :
				$terms_array[] = $term->name;
				$p_tag = implode(' , ', $terms_array);
			endforeach;
		endif;
		
		$category = wp_get_post_terms($get_post,'category',array("fields" => "ids"));
		if (isset($category) && is_array($category)) {
			$category = $category[0];
		}
		
		if (isset($_POST["post_type"]) && $_POST["post_type"] == "edit_post") {
			do_action('vpanel_edit_post');
		}
		
		$out .= '<div class="form-posts"><div class="form-style form-style-3 post-submit">
			<div class="add_post">
				<div '.(!is_user_logged_in?"class='if_no_login'":"").'>';
					$rand_e = rand(1,1000);
					$out .= '
					<form class="new-post-form" method="post" enctype="multipart/form-data">
						<div class="note_error display"></div>
						<div class="form-inputs clearfix">
							<p>
								<label for="post-title-'.$rand_e.'" class="required">'.__("Post Title","vbegy").'<span>*</span></label>
								<input name="title" id="post-title-'.$rand_e.'" class="the-title" type="text" value="'.(isset($posted['title'])?ask_kses_stip($posted['title']):ask_kses_stip($get_post_p->post_title)).'">
								<span class="form-description">'.__("Please choose an appropriate title for the post .","vbegy").'</span>
							</p>';
							
							if ($tags_post == 1) {
								$out .= '<p>
									<label for="post_tag-'.$rand_e.'">'.__("Tags","vbegy").'</label>
									<input type="text" class="input post_tag" name="post_tag" id="post_tag-'.$rand_e.'" value="'.(isset($posted['post_tag'])?$posted['post_tag']:$p_tag).'" data-seperator=",">
									<span class="form-description">'.__("Please choose  suitable Keywords Ex : ","vbegy").'<span class="color">'.__("post , video","vbegy").'</span> .</span>
								</p>';
							}
							
							$category_post = vpanel_options("category_post");
							$category_post_required = vpanel_options("category_post_required");
							$category_post = $category_post_required = 1;
							if ($category_post == 1) {
								$out .= '<div class="div_category">
									<label for="post-category-'.$rand_e.'"'.($category_post_required == 1?' class="required"':'').'>'.__("Category","vbegy").($category_post_required == 1?'<span>*</span>':'').'</label>
									'.ask_me_select_categories($rand_e,(isset($posted['category'])?$posted['category']:(isset($category) && !empty($category)?$category:"")),null,$get_post,'category').'
									<span class="form-description">'.__("Please choose the appropriate section so easily search for your post .","vbegy").'</span>
								</div>';
							}
							
						$out .= '</div>
						<div class="details-area">
							<label for="post-details-'.$rand_e.'" '.(vpanel_options("content_post") == 1?'class="required"':'').'>'.__("Details","vbegy").(vpanel_options("content_post") == 1?'<span>*</span>':'').'</label>';
							
							if ($editor_post_details == 1) {
								ob_start();
								wp_editor((isset($posted['comment'])?ask_kses_stip_wpautop($posted['comment']):$get_post_p->post_content),"post-details-".$rand_e,$settings);
								$editor_contents = ob_get_clean();
								
								$out .= '<div class="the-details the-textarea">'.$editor_contents.'</div>';
							}else {
								$out .= '<textarea name="comment" id="post-details-'.$rand_e.'" class="the-textarea" aria-required="true" cols="58" rows="8">'.(isset($posted['comment'])?ask_kses_stip($posted['comment']):ask_kses_stip($get_post_p->post_content,"yes")).'</textarea>';
							}
						$out .= '<div class="clearfix"></div></div>';
						
						if ($attachment_post == 1) {
							$out .= '<label for="attachment-'.$rand_e.'">'.__("Attachment","vbegy").'</label>
							<div class="fileinputs">
								<input type="file" class="file" name="attachment" id="attachment-'.$rand_e.'">
								<div class="fakefile">
									<button type="button" class="button small margin_0">'.__("Select file","vbegy").'</button>
									<span><i class="icon-arrow-up"></i>'.__("Browse","vbegy").'</span>
								</div>
							</div>';
						}
						
						$out .= '<div class="form-inputs clearfix">
						
						</div>
						<p class="form-submit margin_0">
							<input type="hidden" name="ID" value="'.$get_post.'">
							<input type="hidden" name="post_type" value="edit_post">
							<input type="submit" value="'.__("Edit Your post","vbegy").'" class="button color small submit add_qu publish-post">
						</p>
					
					</form>
				</div>
			</div>
		</div></div>';
	}
	return $out;
}
/* is_user_logged_in_data */
function is_user_logged_in_data ($user_links = array("profile" => 1,"messages" => 1,"questions" => 1,"asked_questions" => 1,"paid_questions" => 1,"answers" => 1,"favorite" => 1,"followed" => 1,"points" => 1,"i_follow" => 1,"followers" => 1,"posts" => 1,"follow_questions" => 1,"follow_answers" => 1,"follow_posts" => 1,"follow_comments" => 1,"edit_profile" => 1,"logout" => 1),$profile_widget = "") {
	$out = '';
	if (is_user_logged_in) {
		$user_login = get_userdata(get_current_user_id());
		$you_avatar = get_the_author_meta('you_avatar',$user_login->ID);
		$url = get_the_author_meta('url',$user_login->ID);
		$twitter = get_the_author_meta('twitter',$user_login->ID);
		$facebook = get_the_author_meta('facebook',$user_login->ID);
		$youtube = get_the_author_meta('youtube',$user_login->ID);
		$google = get_the_author_meta('google',$user_login->ID);
		$linkedin = get_the_author_meta('linkedin',$user_login->ID);
		$follow_email = get_the_author_meta('follow_email',$user_login->ID);
		$country = get_the_author_meta('country',$user_login->ID);
		$city = get_the_author_meta('city',$user_login->ID);
		$phone = get_the_author_meta('phone',$user_login->ID);
		$age = get_the_author_meta('age',$user_login->ID);
		$sex = get_the_author_meta('sex',$user_login->ID);
		$verified_user = get_the_author_meta('verified_user',$user_login->ID);
		$out .= '<div class="row">';
			if ($profile_widget != "on") {
				$out .= '<div class="col-md-8">
					<div class="is-login-left user-profile-img">
						<a original-title="'.$user_login->display_name.'" class="tooltip-n" href="'.vpanel_get_user_url($user_login->ID).'">
							'.askme_user_avatar($you_avatar,79,79,$user_login->ID,$user_login->display_name).'
						</a>
					</div>
					<div class="is-login-right">
						<h2>'.__("Welcome","vbegy").' '.$user_login->display_name.($verified_user == 1?'<img class="verified_user tooltip-n" alt="'.__("Verified","vbegy").'" original-title="'.__("Verified","vbegy").'" src="'.get_template_directory_uri().'/images/verified.png">':'').vpanel_get_badge($user_login->ID).'</h2>';
						if (isset($user_login->description) && $user_login->description != "") {
							$out .= '<p>'.$user_login->description.'</p>';
						}
						if ($youtube || $facebook || $twitter || $linkedin || $google || $follow_email) {
							$out .= '<div class="social_icons social_icons_display">';
								if ($facebook) {
									$out .= '<a href="'.$facebook.'" original-title="'.__("Facebook","vbegy").'" class="tooltip-n">
										<span class="icon_i">
											<span class="icon_square" icon_size="30" span_bg="#3b5997">
												<i class="social_icon-facebook"></i>
											</span>
										</span>
									</a>';
								}
								if ($twitter) {
									$out .= '<a href="'.$twitter.'" original-title="'.__("Twitter","vbegy").'" class="tooltip-n">
										<span class="icon_i">
											<span class="icon_square" icon_size="30" span_bg="#00baf0">
												<i class="social_icon-twitter"></i>
											</span>
										</span>
									</a>';
								}
								if ($youtube) {
									$out .= '<a href="'.$youtube.'" original-title="'.__("Youtube","vbegy").'" class="tooltip-n">
										<span class="icon_i">
											<span class="icon_square" icon_size="30" span_bg="#c4302b">
												<i class="social_icon-youtube"></i>
											</span>
										</span>
									</a>';
								}
								if ($linkedin) {
									$out .= '<a href="'.$linkedin.'" original-title="'.__("Linkedin","vbegy").'" class="tooltip-n">
										<span class="icon_i">
											<span class="icon_square" icon_size="30" span_bg="#006599">
												<i class="social_icon-linkedin"></i>
											</span>
										</span>
									</a>';
								}
								if ($google) {
									$out .= '<a href="'.$google.'" original-title="'.__("Google plus","vbegy").'" class="tooltip-n">
										<span class="icon_i">
											<span class="icon_square" icon_size="30" span_bg="#c43c2c">
												<i class="social_icon-gplus"></i>
											</span>
										</span>
									</a>';
								}
								if ($follow_email) {
									$out .= '<a href="mailto:'.$user_login->user_email.'" original-title="'.__("Email","vbegy").'" class="tooltip-n">
										<span class="icon_i">
											<span class="icon_square" icon_size="30" span_bg="#000">
												<i class="social_icon-email"></i>
											</span>
										</span>
									</a>';
								}
							$out .= '</div>';
						}
					$out .= '</div>
				</div>';
			}
			
			$get_lang = esc_attr(get_query_var("lang"));
			$get_lang_array = array();
			if (isset($get_lang) && $get_lang != "") {
				$get_lang_array = array("lang" => $get_lang);
			}
			
			$out .= '<div class="'.($profile_widget != "on"?"col-md-4":"col-md-12").'">';
				$active_points = vpanel_options("active_points");
				if (isset($user_links) && is_array($user_links) && ((isset($user_links["profile"]) && ($user_links["profile"] == 1 || $user_links["profile"] == "on")) || (isset($user_links["messages"]) && ($user_links["messages"] == 1 || $user_links["messages"] == "on")) || (isset($user_links["questions"]) && ($user_links["questions"] == 1 || $user_links["questions"] == "on")) || (isset($user_links["polls"]) && ($user_links["polls"] == 1 || $user_links["polls"] == "on")) || (isset($user_links["best_answers"]) && ($user_links["best_answers"] == 1 || $user_links["best_answers"] == "on")) || (isset($user_links["asked_questions"]) && ($user_links["asked_questions"] == 1 || $user_links["asked_questions"] == "on")) || (isset($user_links["paid_questions"]) && ($user_links["paid_questions"] == 1 || $user_links["paid_questions"] == "on")) || (isset($user_links["answers"]) && ($user_links["answers"] == 1 || $user_links["answers"] == "on")) || (isset($user_links["favorite"]) && ($user_links["favorite"] == 1 || $user_links["favorite"] == "on")) || (isset($user_links["followed"]) && ($user_links["followed"] == 1 || $user_links["followed"] == "on")) || (isset($user_links["points"]) && ($user_links["points"] == 1 || $user_links["points"] == "on")) || (isset($user_links["i_follow"]) && ($user_links["i_follow"] == 1 || $user_links["i_follow"] == "on")) || (isset($user_links["followers"]) && ($user_links["followers"] == 1 || $user_links["followers"] == "on")) || (isset($user_links["posts"]) && ($user_links["posts"] == 1 || $user_links["posts"] == "on")) || (isset($user_links["follow_questions"]) && ($user_links["follow_questions"] == 1 || $user_links["follow_questions"] == "on")) || (isset($user_links["follow_answers"]) && ($user_links["follow_answers"] == 1 || $user_links["follow_answers"] == "on")) || (isset($user_links["follow_posts"]) && ($user_links["follow_posts"] == 1 || $user_links["follow_posts"] == "on")) || (isset($user_links["follow_comments"]) && ($user_links["follow_comments"] == 1 || $user_links["follow_comments"] == "on")) || (isset($user_links["edit_profile"]) && ($user_links["edit_profile"] == 1 || $user_links["edit_profile"] == "on")) || (isset($user_links["logout"]) && ($user_links["logout"] == 1 || $user_links["logout"] == "on")))) {
					if ($profile_widget != "on") {
						$out .= '<h2>'.__("Quick Links","vbegy").'</h2>';
					}
					$out .= '<ul class="user_quick_links">';
						if (isset($user_links) && is_array($user_links) && ((isset($user_links["profile"]) && ($user_links["profile"] == 1 || $user_links["profile"] == "on")) || (isset($user_links["messages"]) && ($user_links["messages"] == 1 || $user_links["messages"] == "on")) || (isset($user_links["questions"]) && ($user_links["questions"] == 1 || $user_links["questions"] == "on")) || (isset($user_links["polls"]) && ($user_links["polls"] == 1 || $user_links["polls"] == "on")) || (isset($user_links["best_answers"]) && ($user_links["best_answers"] == 1 || $user_links["best_answers"] == "on")) || (isset($user_links["asked_questions"]) && ($user_links["asked_questions"] == 1 || $user_links["asked_questions"] == "on")) || (isset($user_links["answers"]) && ($user_links["answers"] == 1 || $user_links["answers"] == "on")) || (isset($user_links["favorite"]) && ($user_links["favorite"] == 1 || $user_links["favorite"] == "on")) || (isset($user_links["followed"]) && ($user_links["followed"] == 1 || $user_links["followed"] == "on")) || (isset($user_links["points"]) && ($user_links["points"] == 1 || $user_links["points"] == "on")) || (isset($user_links["i_follow"]) && ($user_links["i_follow"] == 1 || $user_links["i_follow"] == "on")) || (isset($user_links["followers"]) && ($user_links["followers"] == 1 || $user_links["followers"] == "on")))) {
							if (isset($user_links) && is_array($user_links) && (isset($user_links["profile"]) && ($user_links["profile"] == 1 || $user_links["profile"] == "on"))) {
								$out .= '<li><a href="'.vpanel_get_user_url($user_login->ID).'"><i class="icon-home"></i>'.__("Profile page","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["messages"]) && ($user_links["messages"] == 1 || $user_links["messages"] == "on"))) {
								$out .= '<li><a href="'.esc_url(get_page_link(vpanel_options('messages_page'))).'"><i class="icon-envelope-alt"></i>'.__("Messages","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["questions"]) && ($user_links["questions"] == 1 || $user_links["questions"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('question_user_page')))).'"><i class="icon-question-sign"></i>'.__("Questions","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["polls"]) && ($user_links["polls"] == 1 || $user_links["polls"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('polls_user_page')))).'"><i class="icon-question-sign"></i>'.__("Polls","vbegy").'</a></li>';
							}
							$ask_question_to_users = vpanel_options("ask_question_to_users");
							if ($ask_question_to_users == 1 && isset($user_links) && is_array($user_links) && (isset($user_links["asked_questions"]) && ($user_links["asked_questions"] == 1 || $user_links["asked_questions"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('asked_question_user_page')))).'"><i class="icon-question-sign"></i>'.__("Asked Questions","vbegy").'</a></li>';
							}
							$pay_ask = vpanel_options("pay_ask");
							if ($pay_ask == 1 && isset($user_links) && is_array($user_links) && (isset($user_links["paid_questions"]) && ($user_links["paid_questions"] == 1 || $user_links["paid_questions"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg("u", esc_attr($user_login->ID),get_page_link(vpanel_options('paid_question')))).'"><i class="icon-shopping-cart"></i>'.__("Paid question","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["answers"]) && ($user_links["answers"] == 1 || $user_links["answers"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('answer_user_page')))).'"><i class="fa fa-comments-o"></i>'.__("Answers","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["best_answers"]) && ($user_links["best_answers"] == 1 || $user_links["best_answers"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('best_answer_user_page')))).'"><i class="fa fa-comments-o"></i>'.__("Best Answers","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["favorite"]) && ($user_links["favorite"] == 1 || $user_links["favorite"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('favorite_user_page')))).'"><i class="icon-star"></i>'.__("Favorite Questions","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["followed"]) && ($user_links["followed"] == 1 || $user_links["followed"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('followed_user_page')))).'"><i class="icon-question-sign"></i>'.__("Followed Questions","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["points"]) && ($user_links["points"] == 1 || $user_links["points"] == "on")) && $active_points == 1) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('point_user_page')))).'"><i class="icon-heart"></i>'.__("Points","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["i_follow"]) && ($user_links["i_follow"] == 1 || $user_links["i_follow"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('i_follow_user_page')))).'"><i class="icon-user-md"></i>'.__("Authors I Follow","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["followers"]) && ($user_links["followers"] == 1 || $user_links["followers"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('followers_user_page')))).'"><i class="icon-user"></i>'.__("Followers","vbegy").'</a></li>';
							}
						}
						if (isset($user_links) && is_array($user_links) && ((isset($user_links["posts"]) && ($user_links["posts"] == 1 || $user_links["posts"] == "on")) || (isset($user_links["comments"]) && ($user_links["comments"] == 1 || $user_links["comments"] == "on")) || (isset($user_links["follow_questions"]) && ($user_links["follow_questions"] == 1 || $user_links["follow_questions"] == "on")) || (isset($user_links["follow_answers"]) && ($user_links["follow_answers"] == 1 || $user_links["follow_answers"] == "on")) || (isset($user_links["follow_posts"]) && ($user_links["follow_posts"] == 1 || $user_links["follow_posts"] == "on")) || (isset($user_links["follow_comments"]) && ($user_links["follow_comments"] == 1 || $user_links["follow_comments"] == "on")) || (isset($user_links["edit_profile"]) && ($user_links["edit_profile"] == 1 || $user_links["edit_profile"] == "on")) || (isset($user_links["logout"]) && ($user_links["logout"] == 1 || $user_links["logout"] == "on")))) {
							if (isset($user_links) && is_array($user_links) && (isset($user_links["posts"]) && ($user_links["posts"] == 1 || $user_links["posts"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('post_user_page')))).'"><i class="icon-file-alt"></i>'.__("Posts","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["comments"]) && ($user_links["comments"] == 1 || $user_links["comments"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('comment_user_page')))).'"><i class="fa fa-comments"></i>'.__("Comments","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["follow_questions"]) && ($user_links["follow_questions"] == 1 || $user_links["follow_questions"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('follow_question_page')))).'"><i class="icon-question-sign"></i>'.__("Follow questions","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["follow_answers"]) && ($user_links["follow_answers"] == 1 || $user_links["follow_answers"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('follow_answer_page')))).'"><i class="fa fa-comments-o"></i>'.__("Follow answers","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["follow_posts"]) && ($user_links["follow_posts"] == 1 || $user_links["follow_posts"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('follow_post_page')))).'"><i class="icon-file-alt"></i>'.__("Follow posts","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["follow_comments"]) && ($user_links["follow_comments"] == 1 || $user_links["follow_comments"] == "on"))) {
								$out .= '<li><a href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($user_login->ID),$get_lang_array)),get_page_link(vpanel_options('follow_comment_page')))).'"><i class="fa fa-comments-o"></i>'.__("Follow comments","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["activity_log"]) && ($user_links["activity_log"] == 1 || $user_links["activity_log"] == "on"))) {
								$out .= '<li><a href="'.esc_url(get_page_link(vpanel_options('activity_log_page'))).'"><i class="fa fa-thumb-tack"></i>'.__("Activity log","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["edit_profile"]) && ($user_links["edit_profile"] == 1 || $user_links["edit_profile"] == "on"))) {
								$out .= '<li><a href="'.esc_url(get_page_link(vpanel_options('user_edit_profile_page'))).'"><i class="icon-pencil"></i>'.__("Edit profile","vbegy").'</a></li>';
							}
							if (isset($user_links) && is_array($user_links) && (isset($user_links["logout"]) && ($user_links["logout"] == 1 || $user_links["logout"] == "on"))) {
								$protocol = is_ssl() ? 'https' : 'http';
								$out .= '<li><a href="'.wp_logout_url(wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])).'"><i class="icon-signout"></i>'.__("Logout","vbegy").'</a></li>';
							}
						}
					$out .= '</ul>';
				}
			$out .= '</div><!-- End col-md-4 -->
		</div><!-- End row -->';
	}else {
		$out .= '<div class="form-style form-style-3">
			'.do_shortcode("[ask_login]").'
		</div>';
	}
	return $out;
}
/* Login shortcode */
function ask_login ($atts, $content = null) {
	global $user_identity,$user_ID;
	$protocol = is_ssl() ? 'https' : 'http';
	$a = shortcode_atts( array(
	    'forget' => 'forget',
	    'register' => '',
	    'register_2' => '',
	), $atts );
	$out = '';
	if (is_user_logged_in) :
		$user_login = get_userdata(get_current_user_id());
		$out .= is_user_logged_in_data(vpanel_options("user_links"));
	else:
		$ajax_file = vpanel_options("ajax_file");
		$ajax_file = ($ajax_file == "theme"?get_template_directory_uri().'/includes/ajax.php':admin_url("admin-ajax.php"));
		$out .= do_action('askme_social_login').do_action('oa_social_login').(shortcode_exists('wordpress_social_login')?'<div class="clearfix"></div><br>'.do_shortcode("[wordpress_social_login]"):"").(shortcode_exists('apsl-login-lite')?'<div class="clearfix"></div><br>'.do_shortcode("[apsl-login-lite]"):"").'<div class="ask_form inputs">
			<form class="login-form ask_login" action="'.home_url('/').'" method="post">
				<div class="ask_error"></div>
				
				<div class="form-inputs clearfix">
					<p class="login-text">
						<input class="required-item" type="text" placeholder="'.__("Username","vbegy").'" name="log">
						<i class="icon-user"></i>
					</p>
					<p class="login-password">
						<input class="required-item" type="password" placeholder="'.__("Password","vbegy").'" name="pwd">
						<i class="icon-lock"></i>
						'.(isset($a["forget"]) && $a["forget"] == "false"?'':'<a href="#">'.__("Forget","vbegy").'</a>').'
					</p>';
					
					$the_captcha_login = vpanel_options("the_captcha_login");
					if ($the_captcha_login == 1) {
						$rand_l = rand(1,1000);
						$captcha_style = vpanel_options("captcha_style");
						$captcha_question = vpanel_options("captcha_question");
						$captcha_answer = vpanel_options("captcha_answer");
						$show_captcha_answer = vpanel_options("show_captcha_answer");
						if ($captcha_style == "question_answer") {
							$out .= "
							<p class='ask_captcha_p'>";
								$out .= '<input size="10" id="ask_captcha-'.$rand_l.'" name="ask_captcha" class="ask_captcha captcha_answer required-item" placeholder="'.__("Captcha","vbegy").'" type="text">
								<i class="icon-pencil"></i>';
								$out .= "<span class='ask_captcha_span'>".$captcha_question.($show_captcha_answer == 1?" ( ".$captcha_answer." )":"")."</span>
							</p>";
						}else {
							$out .= "
							<p class='ask_captcha_p'>";
								$out .= '<input size="10" id="ask_captcha_'.$rand_l.'" name="ask_captcha" class="ask_captcha required-item" placeholder="'.__("Captcha","vbegy").'" type="text">
								<i class="icon-pencil"></i>';
								$out .= "<img class='ask_captcha_img' src='".get_template_directory_uri()."/captcha/create_image.php' alt='".__("Captcha","vbegy")."' title='".__("Click here to update the captcha","vbegy")."' onclick=";$out .='"javascript:ask_get_captcha';$out .="('".get_template_directory_uri()."/captcha/create_image.php', 'ask_captcha_img_".$rand_l."');";$out .='"';$out .=" id='ask_captcha_img_".$rand_l."'>
								<span class='ask_captcha_span'>".__("Click on image to update the captcha .","vbegy")."</span>
							</p>";
						}
					}
				$out .= '
				</div>
				
				<p class="form-submit login-submit">
					<span class="loader_2"></span>
					<input type="submit" value="'.__("Log in","vbegy").'" class="button color small login-submit submit sidebar_submit">
					'.(isset($a["register"]) && $a["register"] == "button"?'<input type="button" class="signup button color small submit sidebar_submit" value="'.__("Register","vbegy").'">':'').'
				</p>
				
				<div class="rememberme">
					<label><input type="checkbox"input name="rememberme" checked="checked"> '.__("Remember Me","vbegy").'</label>
				</div>
				
				<input type="hidden" name="redirect_to" value="'.wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'">
				<input type="hidden" name="login_nonce" value="'.wp_create_nonce("ask-login-action").'">
				<input type="hidden" name="ajax_url" value="'.$ajax_file.'">
				<input type="hidden" name="form_type" value="ask-login">
				<div class="errorlogin"></div>
			</form>
		</div>'.(isset($a["register_2"]) && $a["register_2"] == "yes"?"<ul class='login-links login-links-r'><li><a href='#'>".__("Register","vbegy")."</a></li></ul>":"");
	endif;
	return $out;
}
function ask_login_shortcode() {
	add_shortcode("ask_login","ask_login");
}
add_action("init","ask_login_shortcode");
//add_filter("the_content","do_shortcode");
add_filter("widget_text","do_shortcode");
function ask_login_jquery() {
	if (isset($_REQUEST['redirect_to'])) {
		$redirect_to = $_REQUEST['redirect_to'];
	}
	$after_login = vpanel_options("after_login");
	$after_login_link = vpanel_options("after_login_link");
	
	if ( is_ssl() && force_ssl_admin() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )$secure_cookie = false; else $secure_cookie = '';
	$user = wp_signon('', $secure_cookie);
	
	if (isset($_REQUEST['redirect_to']) && $after_login == "same_page") {
		$redirect_to = $_REQUEST['redirect_to'];
	}else if (isset($user->ID) && $user->ID > 0 && $after_login == "profile") {
		$redirect_to = vpanel_get_user_url($user->ID);
	}else if ($after_login == "custom_link") {
		$redirect_to = esc_url($after_login_link);
	}else {
		$redirect_to = esc_url(home_url('/'));
	}
	
	// Check the username
	if ( !isset($_POST['log']) ) :
		$user = new WP_Error();
		$user->add('empty_username', __('<strong>Error :&nbsp;</strong>please insert your name .','vbegy'));
	elseif ( !isset($_POST['pwd']) ) :
		$user = new WP_Error();
		$user->add('empty_username', __('<strong>Error :&nbsp;</strong>please insert your password .','vbegy'));
	endif;
	if (ask_is_ajax()) :
		// Result
		$result = array();
		if ( !is_wp_error($user) ) :
			$result['success'] = 1;
			$result['redirect'] = $redirect_to;
		else :
			$result['success'] = 0;
			foreach ($user->errors as $error) {
				$result['error'] = $error[0];
				break;
			}
		endif;
		echo json_encode($result);
		die();
	else :
		if ( !is_wp_error($user) ) :
			wp_redirect($redirect_to);
			exit;
		endif;
	endif;
	return $user;
}
if (!function_exists('ask_is_ajax')) {
	function ask_is_ajax() {
		if (defined('DOING_AJAX')) return true;
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') return true;
		return false;
	}
}
function ask_login_process() {
	global $ask_login_errors;
	if (isset($_POST['login-form']) && $_POST['login-form']) :
		$ask_login_errors = ask_login_jquery();
	endif;
}
add_action('init','ask_login_process');
function ask_ajax_login_process() {
	check_ajax_referer( 'ask-login-action', 'security' );
	ask_login_jquery();
	die();
}
add_action('wp_ajax_ask_ajax_login_process','ask_ajax_login_process');
add_action('wp_ajax_nopriv_ask_ajax_login_process','ask_ajax_login_process');
/* Signup shortcode */
add_shortcode('ask_signup', 'ask_signup_shortcode');
function ask_signup_shortcode($atts, $content = null) {
	global $user_identity,$posted;
	$a = shortcode_atts( array(
	    'dark_button' => '',
	), $atts );
	$out = '';
	if (is_user_logged_in) {
		$user_login = get_userdata(get_current_user_id());
		$out .= is_user_logged_in_data(vpanel_options("user_links"));
	}else {
		$protocol = is_ssl() ? 'https' : 'http';
		$rand_w = rand(1,1000);
		$out .= do_action('askme_social_signup').apply_filters('askme_signup_before_form',false).'
		<form method="post" class="signup_form ask_form" enctype="multipart/form-data">
			<p>'.do_action('oa_social_login').(shortcode_exists('wordpress_social_login')?do_shortcode("[wordpress_social_login]"):"").(shortcode_exists('apsl-login-lite')?do_shortcode("[apsl-login-lite]"):"").'</p>'.
			do_action('ask_signup').'
			<div class="ask_error"></div>
				<div class="form-inputs clearfix">
					<p>
						<label for="user_name_'.$rand_w.'" class="required">'.__("Username","vbegy").'<span>*</span></label>
						<input type="text" class="required-item" name="user_name" id="user_name_'.$rand_w.'" value="'.(isset($posted["user_name"])?$posted["user_name"]:"").'">
					</p>
					<p>
						<label for="email_'.$rand_w.'" class="required">'.__("E-Mail","vbegy").'<span>*</span></label>
						<input type="email" class="required-item" name="email" id="email_'.$rand_w.'" value="'.(isset($posted["email"])?$posted["email"]:"").'">
					</p>
					<p>
						<label for="pass1_'.$rand_w.'" class="required">'.__("Password","vbegy").'<span>*</span></label>
						<input type="password" class="required-item" name="pass1" id="pass1_'.$rand_w.'" autocomplete="off">
					</p>
					<p>
						<label for="pass2_'.$rand_w.'" class="required">'.__("Confirm Password","vbegy").'<span>*</span></label>
						<input type="password" class="required-item" name="pass2" id="pass2_'.$rand_w.'" autocomplete="off">
					</p>';
					$profile_picture = vpanel_options("profile_picture");
					$profile_picture_required = vpanel_options("profile_picture_required");
					if ($profile_picture == 1) {
						$out .= '<label '.($profile_picture_required == 1?'class="required"':'').' for="attachment_'.$rand_w.'">'.__('Profile Picture','vbegy').($profile_picture_required == 1?'<span>*</span>':'').'</label>
						<div class="fileinputs">
							<input type="file" name="you_avatar" id="attachment_'.$rand_w.'">
							<div class="fakefile">
								<button type="button" class="small margin_0">'.__('Select file','vbegy').'</button>
								<span><i class="icon-arrow-up"></i>'.__('Browse','vbegy').'</span>
							</div>
						</div>';
					}
					
					$country_register = vpanel_options("country_register");
					$country_required = vpanel_options("country_required");
					$city_register = vpanel_options("city_register");
					$city_required = vpanel_options("city_required");
					$age_register = vpanel_options("age_register");
					$age_required = vpanel_options("age_required");
					$phone_register = vpanel_options("phone_register");
					$phone_required = vpanel_options("phone_required");
					$sex_register = vpanel_options("sex_register");
					$sex_required = vpanel_options("sex_required");
					$names_register = vpanel_options("names_register");
					$names_required = vpanel_options("names_required");
					
					if ($names_register == 1) {
						$out .= '
						<p>
							<label for="first_name_'.$rand_w.'" '.($names_required == 1?'class="required"':'').'>'.__("First Name","vbegy").($names_required == 1?'<span>*</span>':'').'</label>
							<input name="first_name" id="first_name_'.$rand_w.'" type="text" value="'.(isset($posted["first_name"])?$posted["first_name"]:"").'">
						</p>
						<p>
							<label for="last_name_'.$rand_w.'" '.($names_required == 1?'class="required"':'').'>'.__("Last Name","vbegy").($names_required == 1?'<span>*</span>':'').'</label>
							<input name="last_name" id="last_name_'.$rand_w.'" type="text" value="'.(isset($posted["last_name"])?$posted["last_name"]:"").'">
						</p>
						<p>
							<label for="display_name_'.$rand_w.'" '.($names_required == 1?'class="required"':'').'>'.__("Display name","vbegy").($names_required == 1?'<span>*</span>':'').'</label>
							<input name="display_name" id="display_name_'.$rand_w.'" type="text" value="'.(isset($posted["display_name"])?$posted["display_name"]:"").'">
						</p>';
					}
					if ($country_register == 1) {
						$out .= '<p>
							<label for="country_'.$rand_w.'" '.($country_required == 1?'class="required"':'').'>'.__("Country","vbegy").($country_required == 1?'<span>*</span>':'').'</label>
							<span class="styled-select">
								<select name="country" id="country_'.$rand_w.'" '.($country_required == 1?'class="required-item"':'').'>
									<option value="">'.__( 'Select a country&hellip;', 'vbegy' ).'</option>';
										foreach( vpanel_get_countries() as $key => $value )
											$out .= '<option value="' . esc_attr( $key ) . '"' . (isset($posted["country"])?selected( $posted["country"], esc_attr( $key ), false ):"") . '>' . esc_html( $value ) . '</option>';
								$out .= '</select>
							</span>
						</p>';
					}
					if ($city_register == 1) {
						$out .= '<p>
							<label for="city_'.$rand_w.'" '.($city_required == 1?'class="required"':'').'>'.__("City","vbegy").($city_required == 1?'<span>*</span>':'').'</label>
							<input type="text" '.($city_required == 1?'class="required-item"':'').' name="city" id="city_'.$rand_w.'" value="'.(isset($posted["city"])?$posted["city"]:"").'">
						</p>';
					}
					if ($age_register == 1) {
						$out .= '<p>
							<label for="age_'.$rand_w.'" '.($age_required == 1?'class="required"':'').'>'.__("Age","vbegy").($age_required == 1?'<span>*</span>':'').'</label>
							<input type="text" '.($age_required == 1?'class="required-item"':'').' name="age" id="age_'.$rand_w.'" value="'.(isset($posted["age"])?$posted["age"]:"").'">
						</p>';
					}
					if ($phone_register == 1) {
						$out .= '<p>
							<label for="phone_'.$rand_w.'" '.($phone_required == 1?'class="required"':'').'>'.__("Phone","vbegy").($phone_required == 1?'<span>*</span>':'').'</label>
							<input type="text" '.($phone_required == 1?'class="required-item"':'').' name="phone" id="phone_'.$rand_w.'" value="'.(isset($posted["phone"])?$posted["phone"]:"").'">
						</p>';
					}
					if ($sex_register == 1) {
						$out .= '<p>
							<label '.($sex_required == 1?'class="required"':'').'>'.__("Sex","vbegy").($sex_required == 1?'<span>*</span>':'').'</label>
							<input id="sex_male_'.$rand_w.'" name="sex" type="radio" value="1"'.(isset($posted["sex"]) && $posted["sex"] == "1"?' checked="checked"':' checked="checked"').'>
							<label for="sex_male_'.$rand_w.'">'.__("Male","vbegy").'</label>
						</p>
						<p>
							<input id="sex_female_'.$rand_w.'" name="sex" type="radio" value="2"'.(isset($posted["sex"]) && $posted["sex"] == "2"?' checked="checked"':'').'>
							<label for="sex_female_'.$rand_w.'">'.__("Female","vbegy").'</label>
						</p>';
					}
					
					$the_captcha_register = vpanel_options("the_captcha_register");
					$captcha_style = vpanel_options("captcha_style");
					$captcha_question = vpanel_options("captcha_question");
					$captcha_answer = vpanel_options("captcha_answer");
					$show_captcha_answer = vpanel_options("show_captcha_answer");
					if ($the_captcha_register == 1) {
						if ($captcha_style == "question_answer") {
							$out .= "
							<p class='ask_captcha_p'>
								<label for='ask_captcha-".$rand_w."' class='required'>".__("Captcha","vbegy")."<span>*</span></label>
								<input size='10' id='ask_captcha-".$rand_w."' name='ask_captcha' class='ask_captcha captcha_answer' value='' type='text'>
								<span class='question_poll ask_captcha_span'>".$captcha_question.($show_captcha_answer == 1?" ( ".$captcha_answer." )":"")."</span>
							</p>";
						}else {
							$out .= "
							<p class='ask_captcha_p'>
								<label for='ask_captcha_".$rand_w."' class='required'>".__("Captcha","vbegy")."<span>*</span></label>
								<input size='10' id='ask_captcha_".$rand_w."' name='ask_captcha' class='ask_captcha' value='' type='text'><img class='ask_captcha_img' src='".get_template_directory_uri()."/captcha/create_image.php' alt='".__("Captcha","vbegy")."' title='".__("Click here to update the captcha","vbegy")."' onclick=";$out .='"javascript:ask_get_captcha';$out .="('".get_template_directory_uri()."/captcha/create_image.php', 'ask_captcha_img_".$rand_w."');";$out .='"';$out .=" id='ask_captcha_img_".$rand_w."'>
								<span class='question_poll ask_captcha_span'>".__("Click on image to update the captcha .","vbegy")."</span>
							</p>";
						}
					}
					
					$terms_active_register = vpanel_options("terms_active_register");
					$terms_link_register = vpanel_options("terms_link_register");
					if ($terms_active_register == 1) {
						$out .= '<p class="question_poll_p">
							<label for="agree_terms-'.$rand_w.'" class="required">'.__("Terms","vbegy").'<span>*</span></label>
							<input type="checkbox" id="agree_terms-'.$rand_w.'" name="agree_terms" value="1" '.(isset($posted['agree_terms']) && $posted['agree_terms'] == 1?"checked='checked'":"").'>
							<span class="question_poll">'.sprintf(__("By registering, you agree to the <a target='%s' href='%s'>terms of service</a>.","vbegy"),(vpanel_options("terms_active_target_register") == "same_page"?"_self":"_blank"),(isset($terms_link_register) && $terms_link_register != ""?$terms_link_register:get_page_link(vpanel_options('terms_page_register')))).'</span>
						</p>';
					}
					
				$out .= '</div>
				<p class="form-submit">
					<input type="hidden" name="redirect_to" value="'.wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'">
					<input type="submit" name="register" value="'.__("Signup","vbegy").'" class="button color '.(isset($a["dark_button"]) && $a["dark_button"] == "dark_button"?"dark_button":"").' small submit">
					<input type="hidden" name="form_type" value="ask-signup">
				</p>
		</form>'.apply_filters("askme_signup_after_form",false);
	}
	return $out;
}
function ask_signup_process() {
	global $posted,$vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
	$errors = new WP_Error();
	if (isset($_POST['form_type']) && $_POST['form_type'] == "ask-signup") :
		// Process signup form
		$posted = array(
			'user_name'    => esc_html($_POST['user_name']),
			'email'        => esc_html($_POST['email']),
			'pass1'        => esc_html($_POST['pass1']),
			'pass2'        => esc_html($_POST['pass2']),
			'redirect_to'  => esc_url($_POST['redirect_to']),
			'ask_captcha'  => (isset($_POST['ask_captcha']) && $_POST['ask_captcha'] != ""?esc_html($_POST['ask_captcha']):""),
			'country'      => (isset($_POST['country']) && $_POST['country'] != ""?esc_html($_POST['country']):""),
			'city'         => (isset($_POST['city']) && $_POST['city'] != ""?esc_html($_POST['city']):""),
			'age'          => (isset($_POST['age']) && $_POST['age'] != ""?esc_html($_POST['age']):""),
			'phone'        => (isset($_POST['phone']) && $_POST['phone'] != ""?esc_html($_POST['phone']):""),
			'sex'          => (isset($_POST['sex']) && $_POST['sex'] != ""?esc_html($_POST['sex']):""),
			'first_name'   => (isset($_POST['first_name']) && $_POST['first_name'] != ""?esc_html($_POST['first_name']):""),
			'last_name'    => (isset($_POST['last_name']) && $_POST['last_name'] != ""?esc_html($_POST['last_name']):""),
			'display_name' => (isset($_POST['display_name']) && $_POST['display_name'] != ""?esc_html($_POST['display_name']):""),
			'agree_terms'  => (isset($_POST['agree_terms']) && $_POST['agree_terms'] != ""?esc_html($_POST['agree_terms']):""),
		);
		$posted = array_map('stripslashes', $posted);
		$posted['username'] = sanitize_user((isset($posted['username'])?$posted['username']:""));
		// Validation
		if ( empty($posted['user_name']) ) $errors->add('required-user_name',__("Please enter your name.","vbegy"));
		if ( empty($posted['email']) ) $errors->add('required-email',__("Please enter your email.","vbegy"));
		if ( empty($posted['pass1']) ) $errors->add('required-pass1',__("Please enter your password.","vbegy"));
		if ( empty($posted['pass2']) ) $errors->add('required-pass2',__("Please rewrite password.","vbegy"));
		if ( $posted['pass1']!==$posted['pass2'] ) $errors->add('required-pass1',__("Password does not match.","vbegy"));
		
		$the_captcha_register = vpanel_options("the_captcha_register");
		$captcha_style = vpanel_options("captcha_style");
		$captcha_question = vpanel_options("captcha_question");
		$captcha_answer = vpanel_options("captcha_answer");
		$show_captcha_answer = vpanel_options("show_captcha_answer");
		$country_register = vpanel_options("country_register");
		$country_required = vpanel_options("country_required");
		$city_register = vpanel_options("city_register");
		$city_required = vpanel_options("city_required");
		$age_register = vpanel_options("age_register");
		$age_required = vpanel_options("age_required");
		$phone_register = vpanel_options("phone_register");
		$phone_required = vpanel_options("phone_required");
		$sex_register = vpanel_options("sex_register");
		$sex_required = vpanel_options("sex_required");
		$names_register = vpanel_options("names_register");
		$names_required = vpanel_options("names_required");
		
		if ($the_captcha_register == 1) {
			if (empty($posted["ask_captcha"])) {
				$errors->add('required-captcha', __("There are required fields ( captcha ).","vbegy"));
			}
			if ($captcha_style == "question_answer") {
				if ($captcha_answer != $posted["ask_captcha"]) {
					$errors->add('required-captcha-error', __('The captcha is incorrect, please try again.','vbegy'));
				}
			}else {
				if ($_SESSION["security_code"] != $posted["ask_captcha"]) {
					$errors->add('required-captcha-error', __('The captcha is incorrect, please try again.','vbegy'));
				}
			}
		}
		$profile_picture = vpanel_options("profile_picture");
		$profile_picture_required = vpanel_options("profile_picture_required");
		
		if(isset($_FILES['you_avatar']) && !empty($_FILES['you_avatar']['name'])) :
			$mime = $_FILES["you_avatar"]["type"];
			if (($mime != 'image/jpeg') && ($mime != 'image/jpg') && ($mime != 'image/png')) {
				$errors->add('upload-error', esc_html__('Error type, Please upload: jpg,jpeg,png','vbegy'));
				if ($errors->get_error_code()) return $errors;
			}else {
				require_once(ABSPATH . "wp-admin" . '/includes/file.php');
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				$you_avatar = wp_handle_upload($_FILES['you_avatar'],array('test_form'=>false),current_time('mysql'));
				if ( isset($you_avatar['error']) ) :
					$errors->add('upload-error',  __('Error in upload the image : ','vbegy') . $you_avatar['error'] );
					return $errors;
				endif;
			}
		else:
			if ($profile_picture_required == 1) {
				$errors->add('required-profile_picture', __("There are required fields ( Profile Picture ).","vbegy"));
			}
		endif;
		if (isset($you_avatar['error']) && $you_avatar) :
			if (isset($errors->add)) {
				$errors->add('upload-error', esc_html__('Error in upload the image : ','vbegy') . $you_avatar['error']);
				if ($errors->get_error_code()) return $errors;
			}
			return $errors;
		endif;
		
		if ($country_register == 1 && $country_required == 1 && empty($posted['country'])) {
			$errors->add('required-country', __("There are required fields ( Country ).","vbegy"));
		}
		if ($city_register == 1 && $city_required == 1 && empty($posted['city'])) {
			$errors->add('required-city', __("There are required fields ( City ).","vbegy"));
		}
		if ($age_register == 1 && $age_required == 1 && empty($posted['age'])) {
			$errors->add('required-age', __("There are required fields ( Age ).","vbegy"));
		}
		if ($phone_register == 1 && $phone_required == 1 && empty($posted['phone'])) {
			$errors->add('required-phone', __("There are required fields ( Phone ).","vbegy"));
		}
		if ($sex_register == 1 && $sex_required == 1 && empty($posted['sex'])) {
			$errors->add('required-sex', __("There are required fields ( Sex ).","vbegy"));
		}
		if ($names_register == 1 && $names_required == 1 && empty($posted['first_name'])) {
			$errors->add('required-first_name', __("There are required fields ( First Name ).","vbegy"));
		}
		if ($names_register == 1 && $names_required == 1 && empty($posted['last_name'])) {
			$errors->add('required-last_name', __("There are required fields ( Last Name ).","vbegy"));
		}
		if ($names_register == 1 && $names_required == 1 && empty($posted['display_name'])) {
			$errors->add('required-display_name', __("There are required fields ( Display Name ).","vbegy"));
		}
		
		$terms_active_register = vpanel_options("terms_active_register");
		if ($terms_active_register == 1 && $posted['agree_terms'] != 1) {
			$errors->add('required-terms', __("There are required fields ( Agree of the terms ).","vbegy"));
		}
		// Check the username
		if ( username_exists( $posted['user_name'] ) ) :
			$errors->add('required-user_name',__("This account is already registered.","vbegy"));
		endif;
		// Check the e-mail address
		if ( !is_email( $posted['email'] ) ) :
			$errors->add('required-email',__("Please write correctly email.","vbegy"));
		elseif ( email_exists( $posted['email'] ) ) :
			$errors->add('required-email',__("This account is already registered.","vbegy"));
		endif;
		if ( $errors->get_error_code() ) return $errors;
		if ( !$errors->get_error_code() ) :
			do_action('register_post', $posted['user_name'], $posted['email'], $errors);
			$errors = apply_filters( 'registration_errors', $errors, $posted['user_name'], $posted['email'] );
			// if there are no errors, let's create the user account
			if ( !$errors->get_error_code() ) :
				$user_id = wp_create_user( $posted['user_name'], $posted['pass1'], $posted['email'] );
				if (is_wp_error($user_id)) {
					$errors->add('error', sprintf(__('<strong>Error</strong>: Sorry can not register please contact the webmaster ','vbegy'), get_option('admin_email')));
					if ( $errors->get_error_code() ) {return $errors;}
				}else {
					update_user_meta($user_id,"points",0);
					update_user_meta($user_id,"the_best_answer",0);
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
					if ($posted['country']) :
						update_user_meta($user_id,"country",$posted['country']);
					endif;
					if ($posted['city']) :
						update_user_meta($user_id,"city",$posted['city']);
					endif;
					if ($posted['age']) :
						update_user_meta($user_id,"age",$posted['age']);
					endif;
					if ($posted['phone']) :
						update_user_meta($user_id,"phone",$posted['phone']);
					endif;
					if ($posted['sex']) :
						update_user_meta($user_id,"sex",$posted['sex']);
					endif;
					$user_review = vpanel_options("user_review");
					if ($user_review == 1) {
						$ask_under_review = get_role("ask_under_review");
						if (!isset($ask_under_review)) {
							add_role("ask_under_review",__("Under review","vbegy"),array('read' => false));
						}
					}
					$confirm_email = vpanel_options("confirm_email");
					if ($confirm_email == 1 && $user_review != 1) {
						$activation = get_role("activation");
						if (!isset($activation)) {
							add_role("activation","activation",array('read' => false));
						}
						wp_update_user( array ('ID' => $user_id, 'role' => 'activation', 'first_name' => $posted['first_name'], 'last_name' => $posted['last_name'], 'display_name' => $posted['display_name'],'user_nicename' => $posted['user_name'],'nickname' => $posted['user_name']) ) ;
						$rand_a = rand(1,1000000000000);
						update_user_meta($user_id,"activation",$rand_a);
						$user_data = get_user_by("id",$user_id);
						$confirm_link = esc_url(add_query_arg(array("u" => $user_id,"activate" => $rand_a),esc_url(home_url('/'))));
						$send_text = ask_send_email(vpanel_options("email_confirm_link_2"),$user_id,"","","",$confirm_link);
						$logo_email_template = vpanel_options("logo_email_template");
						$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
						$email_title = vpanel_options("title_confirm_link_2");
						$email_title = ($email_title != ""?$email_title:__("Confirm account","vbegy"));
						sendEmail(vpanel_options("email_template"),get_bloginfo('name'),esc_html($_POST['email']),esc_html($_POST['user_name']),$email_title,$last_message_email);
					}else {
						$default_group = vpanel_options("default_group");
						$default_group = (isset($default_group) && $default_group != ""?$default_group:"subscriber");
						$default_group = ($user_review == 1?"ask_under_review":$default_group);
						wp_update_user( array ('ID' => $user_id, 'role' => $default_group, 'first_name' => $posted['first_name'], 'last_name' => $posted['last_name'], 'display_name' => $posted['display_name'],'user_nicename' => $posted['user_name'],'nickname' => $posted['user_name']) ) ;
					}
					$point_new_user = vpanel_options("point_new_user");
					$active_points = vpanel_options("active_points");
					if ($point_new_user > 0 && $active_points == 1 && $confirm_email != 1) {
						$current_user = get_user_by("id",$user_id);
						$_points = get_user_meta($user_id,$current_user->user_login."_points",true);
						$_points++;
						
						update_user_meta($user_id,$current_user->user_login."_points",$_points);
						add_user_meta($user_id,$current_user->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_new_user,"+","gift_site",$post_id,$id));
					
						$points_user = get_user_meta($user_id,"points",true);
						update_user_meta($user_id,"points",$points_user+$point_new_user);
						
						askme_notifications_activities($user_id,"","","","","gift_site","notifications");
					}
					$secure_cookie = is_ssl() ? true : false;
					wp_set_auth_cookie($user_id, true, $secure_cookie);
					
					$after_register = vpanel_options("after_register");
					$after_register_link = vpanel_options("after_register_link");
					
					if (isset($posted['redirect_to']) && $after_register == "same_page") {
						$redirect_to = $posted['redirect_to'];
					}else if (isset($user_id) && $user_id > 0 && $after_register == "profile") {
						$redirect_to = vpanel_get_user_url($user_id);
					}else if ($after_register == "custom_link") {
						$redirect_to = esc_url($after_register_link);
					}else {
						$redirect_to = esc_url(home_url('/'));
					}
					wp_safe_redirect($redirect_to);
					exit;
				}
			endif;
		endif;
	endif;
	return;
}
add_action('user_register','ask_registration_save',10,1);
function ask_registration_save ($user_id) {
	$point_new_user = vpanel_options("point_new_user");
	$active_points = vpanel_options("active_points");
	if (is_user_logged_in && $point_new_user > 0 && $active_points == 1) {
		$current_user = get_user_by("id",$user_id);
		$_points = get_user_meta($user_id,$current_user->user_login."_points",true);
		$_points++;
	
		update_user_meta($user_id,$current_user->user_login."_points",$_points);
		add_user_meta($user_id,$current_user->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_new_user,"+","gift_site",$post_id,$id));
	
		$points_user = get_user_meta($user_id,"points",true);
		update_user_meta($user_id,"points",$points_user+$point_new_user);
		
		askme_notifications_activities($user_id,"","","","","gift_site","notifications");
	}
}
function ask_signup() {
	if (isset($_POST['form_type']) && $_POST['form_type'] == "ask-signup") :
		$return = ask_signup_process();
		if (is_wp_error($return) ) :
			echo '<div class="ask_error"><strong><p>'.__("Error","vbegy").' :&nbsp;</strong>'.wptexturize(str_replace('<strong>'.__("Error","vbegy").'</strong>: ', '', $return->get_error_message())).'</p></div>';
   		endif;
	endif;
}
add_action('ask_signup', 'ask_signup');
/* Lostpassword shortcode */
add_shortcode('ask_lost_pass', 'ask_lost_pass');
function ask_lost_pass($atts, $content = null) {
	global $user_identity;
	$a = shortcode_atts( array(
	    'dark_button' => '',
	), $atts );
	$out = '';
	if (is_user_logged_in) :
		$user_login = get_userdata(get_current_user_id());
		$out .= is_user_logged_in_data(vpanel_options("user_links"));
	else:
		do_action('ask_lost_password');
		$rand_w = rand(1,1000);
		$out .= '
		<form method="post" class="ask-lost-password ask_form" action="">
			<div class="ask_error"></div>
			<div class="form-inputs clearfix">
				<p>
					<label for="user_mail_'.$rand_w.'" class="required">'.__("E-Mail","vbegy").'<span>*</span></label>
					<input type="email" class="required-item" name="user_mail" id="user_mail_'.$rand_w.'">
				</p>
			</div>
			<p class="form-submit">
				<input type="submit" value="'.__("Reset","vbegy").'" class="button color '.(isset($a["dark_button"]) && $a["dark_button"] == "dark_button"?"dark_button":"").' small submit">
				<input type="hidden" name="form_type" value="ask-forget">
			</p>
		</form>';
	endif;
	return $out;
}
function ask_process_lost_pass() {
	global $posted,$wpdb,$vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
	$errors = new WP_Error();
	$fields = array('user_mail','form_type');
	
	foreach ($fields as $field) :
		if (isset($_POST[$field])) $posted[$field] = $_POST[$field]; else $posted[$field] = '';
	endforeach;
	
	$posted = array_map('stripslashes', $posted);
	
	if ( is_user_logged_in ) :
		$user_id = get_current_user_id();
		$errors->add('already_logged', sprintf(wp_kses(__("You are already logged in, If you want to change your password go to <a href='%s'>edit profile</a>.","vbegy"),array('a' => array('href' => array()))),esc_url(get_page_link(vpanel_options('user_edit_profile_page')))));
	elseif ( empty($posted['user_mail']) ) :
		$errors->add('empty_email', sprintf(esc_html__('Please insert your email.','vbegy'),'<strong>','</strong>'));
	elseif ( !email_exists($posted['user_mail']) ) :
		$errors->add('invalid_email', sprintf(esc_html__('There is no user registered with that email address.','vbegy'),'<strong>','</strong>'));
	endif;
	
	$get_user_by_mail = get_user_by('email',$posted['user_mail']);
	if ( $errors->get_error_code() ) return $errors;
	if ($_POST['form_type']) {
		unset($_POST["form_type"]);
	}
	$rand_a = rand(1,1000000000000);
	$get_reset_password = get_user_meta($get_user_by_mail->ID,"reset_password",true);
	if ($get_reset_password == "") {
		update_user_meta($get_user_by_mail->ID,"reset_password",$rand_a);
		$get_reset_password = $rand_a;
	}
	$confirm_link_email = esc_url(add_query_arg(array("u" => $get_user_by_mail->ID,"reset_password" => $get_reset_password),esc_url(home_url('/'))));
	$send_text = ask_send_email(vpanel_options("email_new_password"),$get_user_by_mail->ID,"","","",$confirm_link_email);
	$logo_email_template = vpanel_options("logo_email_template");
	$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
	$email_title = vpanel_options("title_new_password");
	$email_title = ($email_title != ""?$email_title:__("Reset your password","vbegy"));
	sendEmail(vpanel_options("email_template"),get_bloginfo('name'),esc_html($posted['user_mail']),esc_html($get_user_by_mail->display_name),$email_title,$last_message_email);
	return;
}
function ask_lost_pass_word() {
	if (isset($_POST['form_type']) && $_POST['form_type'] == "ask-forget") :
		$return = ask_process_lost_pass();
		if ( is_wp_error($return) ) :
   			echo '<div class="ask_error"><strong>'.__("Error","vbegy").' :&nbsp;'.$return->get_error_message().'</strong></div>';
   		else :
   			echo '<div class="ask_done"><strong>'.__("Check your email please.","vbegy").'</strong></div>';
   		endif;
	endif;
}
add_action('ask_lost_password', 'ask_lost_pass_word');
/* Generate random code */
function ask_generate_random($length = 6, $letters = '1234567890qwertyuiopasdfghjklzxcvbnm') {
	$s = '';
	$lettersLength = strlen($letters)-1;
	for($i = 0 ; $i < $length ; $i++) {
		$s .= $letters[rand(0,$lettersLength)];
	}
	return $s;
}
/* hex2rgb */
function hex2rgb ($hex) {
   $hex = str_replace("#","",$hex);
   if (strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   }else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   return $rgb;
}
/* ask_edit_profile_shortcode */
add_shortcode('ask_edit_profile', 'ask_edit_profile_shortcode');
function ask_edit_profile_shortcode($atts, $content = null) {
	global $user_identity,$posted,$public_display;
	$out = '';
	if (!is_user_logged_in) {
		$out .= '<div class="note_error"><strong>'.__("Please login to edit profile .","vbegy").'</strong></div>
		<div class="form-style form-style-3">
			'.do_shortcode("[ask_login register_2='yes']").'
		</div>';
	}else {
		do_action('ask_edit_profile_form');
		$out .= '<form class="edit-profile-form vpanel_form" method="post" enctype="multipart/form-data">';
		
			$user_info = get_userdata(get_current_user_id());
			$you_avatar = get_the_author_meta('you_avatar',$user_info->ID);
			$url = get_the_author_meta('url',$user_info->ID);
			$twitter = get_the_author_meta('twitter',$user_info->ID);
			$facebook = get_the_author_meta('facebook',$user_info->ID);
			$youtube = get_the_author_meta('youtube',$user_info->ID);
			$google = get_the_author_meta('google',$user_info->ID);
			$linkedin = get_the_author_meta('linkedin',$user_info->ID);
			$follow_email = get_the_author_meta('follow_email',$user_info->ID);
			$follow_email = ($follow_email != ""?1:0);
			$display_name = get_the_author_meta('display_name',$user_info->ID);
			$country = get_the_author_meta('country',$user_info->ID);
			$city = get_the_author_meta('city',$user_info->ID);
			$age = get_the_author_meta('age',$user_info->ID);
			$phone = get_the_author_meta('phone',$user_info->ID);
			$sex = get_the_author_meta('sex',$user_info->ID);
			$instagram = get_the_author_meta('instagram',$user_info->ID);
			$pinterest = get_the_author_meta('pinterest',$user_info->ID);
			
			$show_point_favorite = get_the_author_meta('show_point_favorite',$user_info->ID);
			$received_email = get_the_author_meta('received_email',$user_info->ID);
			$received_message = get_the_author_meta('received_message',$user_info->ID);
			$names_profile = vpanel_options("names_profile");
			$names_required_profile = vpanel_options("names_required_profile");
			$phone_profile = vpanel_options("phone_profile");
			$phone_required_profile = vpanel_options("phone_required_profile");
			
			$country_profile = vpanel_options("country_profile");
			$country_required_profile = vpanel_options("country_required_profile");
			$city_profile = vpanel_options("city_profile");
			$city_required_profile = vpanel_options("city_required_profile");
			$age_profile = vpanel_options("age_profile");
			$age_required_profile = vpanel_options("age_required_profile");
			$sex_profile = vpanel_options("sex_profile");
			$sex_required_profile = vpanel_options("sex_required_profile");
			$url_profile = vpanel_options("url_profile");
			$url_required_profile = vpanel_options("url_required_profile");
			
			$out .= '<div class="form-inputs clearfix">
				<p>
					<label>'.__("Nickname","vbegy").'</label>
					<input name="nickname" id="nickname" type="text" value="'.$user_info->nickname.'">
				</p>';
				if ($names_profile == 1) {
					$out .= '
					<p>
						<label '.($names_required_profile == 1?'class="required"':'').'>'.__("First Name","vbegy").($names_required_profile == 1?'<span>*</span>':'').'</label>
						<input name="first_name" id="first_name" type="text" value="'.$user_info->first_name.'">
					</p>
					<p>
						<label '.($names_required_profile == 1?'class="required"':'').'>'.__("Last Name","vbegy").($names_required_profile == 1?'<span>*</span>':'').'</label>
						<input name="last_name" id="last_name" type="text" value="'.$user_info->last_name.'">
					</p>
					<p>
						<label '.($names_required_profile == 1?'class="required"':'').'>'.__("Display name","vbegy").($names_required_profile == 1?'<span>*</span>':'').'</label>
						<input name="display_name" id="display_name" type="text" value="'.$user_info->display_name.'">
					</p>';
				}
				
				$out .= '<p>
					<label for="email" class="required">'.__("E-Mail","vbegy").'<span>*</span></label>
					<input name="email" id="email" type="email" value="'.$user_info->user_email.'">
				</p>
				<p>
					<label for="newpassword" class="required">'.__("Password","vbegy").'<span>*</span></label>
					<input name="pass1" id="newpassword" type="password" value="">
				</p>
				<p>
					<label for="newpassword2" class="required">'.__("Confirm Password","vbegy").'<span>*</span></label>
					<input name="pass2" id="newpassword2" type="password" value="">
				</p>';
				
				if ($phone_profile == 1) {
					$out .= '<p>
						<label for="phone" '.($phone_required_profile == 1?'class="required"':'').'>'.__("Phone","vbegy").($phone_required_profile == 1?'<span>*</span>':'').'</label>
						<input type="text" '.($phone_required_profile == 1?'class="required-item"':'').' name="phone" id="phone" value="'.$phone.'">
					</p>';
				}
				
				if ($country_profile == 1) {
					$out .= '
					<p>
						<label for="country" '.($country_required_profile == 1?'class="required"':'').'>'.__("Country","vbegy").($country_required_profile == 1?'<span>*</span>':'').'</label>
						<span class="styled-select">
							<select name="country" id="country" '.($country_required_profile == 1?'class="required-item"':'').'>
								<option value="">'.__( 'Select a country&hellip;', 'vbegy' ).'</option>';
									foreach( vpanel_get_countries() as $key => $value )
										$out .= '<option value="' . esc_attr( $key ) . '"' . selected( $country, esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
							$out .= '</select>
						</span>
					</p>';
				}
				if ($city_profile == 1) {
					$out .= '<p>
						<label for="city" '.($city_required_profile == 1?'class="required"':'').'>'.__("City","vbegy").($city_required_profile == 1?'<span>*</span>':'').'</label>
						<input type="text" '.($city_required_profile == 1?'class="required-item"':'').' name="city" id="city" value="'.$city.'">
					</p>';
				}
				
				$out .= apply_filters('askme_edit_profile_after_city',false,(isset($_POST)?$_POST:array()),$user_info->ID);
				
				if ($age_profile == 1) {
					$out .= '<p>
						<label for="age" '.($age_required_profile == 1?'class="required"':'').'>'.__("Age","vbegy").($age_required_profile == 1?'<span>*</span>':'').'</label>
						<input type="text" '.($age_required_profile == 1?'class="required-item"':'').' name="age" id="age" value="'.$age.'">
					</p>';
				}
				if ($sex_profile == 1) {
					$out .= '<p>
						<label '.($sex_required_profile == 1?'class="required"':'').'>'.__("Sex","vbegy").($sex_required_profile == 1?'<span>*</span>':'').'</label>
						<input id="sex_male" name="sex" type="radio" value="1"'.($sex == "male" || $sex == "1"?' checked="checked"':' checked="checked"').'>
						<label for="sex_male">'.__("Male","vbegy").'</label>
						<input id="sex_female" name="sex" type="radio" value="2"'.($sex == "female" || $sex == "2"?' checked="checked"':'').'>
						<label for="sex_female">'.__("Female","vbegy").'</label>
					</p>';
				}
			$out .= '</div>
			<div class="form-style form-style-2 form-style-3">';
				$profile_picture_profile = vpanel_options("profile_picture_profile");
				$profile_picture_required_profile = vpanel_options("profile_picture_required_profile");
				if ($profile_picture_profile == 1) {
					if ($you_avatar) {
						$out .= "<div class='user-profile-img edit-profile-img'>".askme_user_avatar($you_avatar,79,79,$user_info->ID,$user_info->display_name)."</div>";
					}
					
					$out .= '
						<label '.($profile_picture_required_profile == 1?'class="required"':'').' for="you_avatar">'.__("Profile Picture","vbegy").($profile_picture_required_profile == 1?'<span>*</span>':'').'</label>
						<div class="fileinputs">
							<input type="file" name="you_avatar" id="you_avatar" value="'.$you_avatar.'">
							<div class="fakefile">
								<button type="button" class="small margin_0">'.__("Select file","vbegy").'</button>
								<span><i class="icon-arrow-up"></i>'.__("Browse","vbegy").'</span>
							</div>
						</div>
					<div class="clearfix"></div>
					<p></p>';
				}
				
				$out .= '<p>
					<label for="description">'.__("About Yourself","vbegy").'</label>
					<textarea name="description" id="description" cols="58" rows="8">'.$user_info->description.'</textarea>
				</p>
			</div>
			<div class="form-inputs clearfix">';
				if ($url_profile == 1) {
					$out .= '<p>
						<label '.($url_required_profile == 1?'class="required"':'').'>'.__("Website","vbegy").($url_required_profile == 1?'<span>*</span>':'').'</label>
						<input name="url" id="url" type="text" value="'.$url.'">
					</p>';
				}
				$out .= '<p>
					<label for="facebook">'.__("Facebook","vbegy").'</label>
					<input type="text" name="facebook" id="facebook" value="'.$facebook.'">
				</p>
				<p>
					<label for="twitter">'.__("Twitter","vbegy").'</label>
					<input type="text" name="twitter" id="twitter" value="'.$twitter.'">
				</p>
				<p>
					<label for="youtube">'.__("Youtube","vbegy").'</label>
					<input type="text" name="youtube" id="youtube" value="'.$youtube.'">
				</p>
				<p>
					<label for="linkedin">'.__("Linkedin","vbegy").'</label>
					<input type="text" name="linkedin" id="linkedin" value="'.$linkedin.'">
				</p>
				<p>
					<label for="google">'.__("Google plus","vbegy").'</label>
					<input type="text" name="google" id="google" value="'.$google.'">
				</p>
				<p>
					<label for="instagram">'.__("Instagram","vbegy").'</label>
					<input type="text" name="instagram" id="instagram" value="'.$instagram.'">
				</p>
				<p>
					<label for="pinterest">'.__("Pinterest","vbegy").'</label>
					<input type="text" name="pinterest" id="pinterest" value="'.$pinterest.'">
				</p>
			</div>
			
			<label for="show_point_favorite">
				<input type="checkbox" name="show_point_favorite" id="show_point_favorite" value="1" '.checked($show_point_favorite,1,false).'>
				'.__("Show your private pages for all the users?","vbegy").'
			</label>

			<label for="follow_email">
				<input type="checkbox" name="follow_email" id="follow_email" value="1" '.checked($follow_email,1,false).'>
				'.__("Follow-up email","vbegy").'
			</label>';
			
			$send_email_question_groups = vpanel_options("send_email_question_groups");
			if (isset($send_email_question_groups) && is_array($send_email_question_groups)) {
				foreach ($send_email_question_groups as $key => $value) {
					if ($value == 1) {
						$send_email_question_groups[$key] = $key;
					}else {
						unset($send_email_question_groups[$key]);
					}
				}
			}
			if (is_array($send_email_question_groups) && in_array($user_info->roles[0],$send_email_question_groups)) {
				$out .= '<label for="received_email">
					<input type="checkbox" name="received_email" id="received_email" value="1" '.checked($received_email,1,false).'>
					'.__("Received mail when user add a new question","vbegy").'
				</label>';
			}
			
			$active_message = vpanel_options("active_message");
			if ($active_message = 1) {
				$out .= '<label for="received_message">
					<input type="checkbox" name="received_message" id="received_message" value="1" '.checked($received_message,($received_message == ""?"":1),false).'>
					'.__("Received message from another users?","vbegy").'
				</label>';
			}
			
			$out .= '<p class="form-submit">
				<input type="hidden" name="user_action" value="edit_profile">
				<input type="hidden" name="action" value="update">
				<input type="hidden" name="admin_bar_front" value="1">
				<input type="hidden" name="user_id" id="user_id" value="'.$user_info->ID.'">
				<input type="hidden" name="user_login" id="user_login" value="'.$user_info->user_login.'">
				<input type="submit" value="'.__("Save","vbegy").'" class="button color small login-submit submit">
			</p>
		
		</form>';
	}
	return $out;
}
/* ask_sanitize_user */ 
function ask_sanitize_user ($username, $raw_username, $strict) {
	$username = wp_strip_all_tags ($raw_username);
	$username = remove_accents ($username);
	$username = preg_replace ('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
	$username = preg_replace ('/&.+?;/', '', $username);
	if ($strict) {
		$username = preg_replace ('|[^a-z\p{Arabic}\p{Cyrillic}0-9 _.\-@]|iu', '', $username);
	}
	$username = trim ($username);
	$username = preg_replace ('|\s+|', ' ', $username);
	return $username;
}
add_filter ('sanitize_user', 'ask_sanitize_user', 10, 3);?>