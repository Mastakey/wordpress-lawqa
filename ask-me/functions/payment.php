<?php require_once get_template_directory() . '/includes/paypal.class.php';
$p = new paypal_class;
$paypal_sandbox = vpanel_options('paypal_sandbox');
if ($paypal_sandbox == 1) {
	$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
}else {
	$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
}
$protocol    = is_ssl() ? 'https' : 'http';
$this_script = $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$user_id     = get_current_user_id();
global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;

switch ((isset($_GET['action'])?$_GET['action']:"")) {
	case 'process':
		if (isset($_POST["go"]) && $_POST["go"] == "paypal") {
			$question_sticky = (isset($_REQUEST['question_sticky']) && $_REQUEST['question_sticky'] != ""?(int)$_REQUEST['question_sticky']:"");
			$CatDescription  = (isset($_REQUEST['CatDescription']) && $_REQUEST['CatDescription'] != ""?esc_attr($_REQUEST['CatDescription']):"");
			$item_no         = (isset($_REQUEST['item_number']) && $_REQUEST['item_number'] != ""?esc_attr($_REQUEST['item_number']):"");
			$payment         = (isset($_REQUEST['payment']) && $_REQUEST['payment'] != ""?esc_attr($_REQUEST['payment']):"");
			$key             = (isset($_REQUEST['key']) && $_REQUEST['key'] != ""?esc_attr($_REQUEST['key']):"");
			$quantity        = (isset($_REQUEST['quantity']) && $_REQUEST['quantity'] != ""?esc_attr($_REQUEST['quantity']):"");
			$coupon          = (isset($_REQUEST['coupon']) && $_REQUEST['coupon'] != ""?esc_attr($_REQUEST['coupon']):"");
			$currency_code   = (isset($_REQUEST['currency_code']) && $_REQUEST['currency_code'] != ""?esc_attr($_REQUEST['currency_code']):"");
			
			echo '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.sprintf(__("Go to PayPal now","vbegy").'</span><br>'.__("Please wait will go to the PayPal now to pay a new payment.","vbegy"),esc_url(add_query_arg(array("get_activate" => "do"),esc_url(home_url('/'))))).'</p></div>';
			
			if ($question_sticky != "") {
				update_user_meta($user_id,$user_id."_question_sticky",$question_sticky);
			}
			
			$p->add_field('business', vpanel_options('paypal_email'));
			$p->add_field('return', $this_script.'?action=success');
			$p->add_field('cancel_return', $this_script.'?action=cancel');
			$p->add_field('notify_url', $this_script.'?action=ipn');
			$p->add_field('item_name', $CatDescription);
			$p->add_field('item_number', $item_no);
			$p->add_field('amount', $payment);
			$p->add_field('key', $key);
			$p->add_field('quantity', $quantity);
			$p->add_field('currency_code', $currency_code);
			
			$p->submit_paypal_post();
			//$p->dump_fields();
		}else {
			wp_safe_redirect(esc_url(home_url('/')));
		}
		get_footer();
		die();
	break;
	case 'success':
		if ((isset($_REQUEST['txn_id']) && $_REQUEST['txn_id'] != "") || isset($_REQUEST['tx']) && $_REQUEST['tx'] != "") {
			$data = wp_remote_post($p->paypal_url.'?cmd=_notify-synch&tx='.(isset($_REQUEST['tx'])?$_REQUEST['tx']:(isset($_REQUEST['txn_id'])?$_REQUEST['txn_id']:'')).'&at='.vpanel_options("identity_token"));
			if (!is_wp_error($data)) {
				$data = $data['body'];
				$response = substr($data, 7);
				$response = urldecode($response);
				
				preg_match_all('/^([^=\s]++)=(.*+)/m', $response, $m, PREG_PATTERN_ORDER);
				$response = array_combine($m[1], $m[2]);
				
				if (isset($response['charset']) && strtoupper($response['charset']) !== 'UTF-8') {
					foreach ($response as $key => &$value) {
						$value = mb_convert_encoding($value, 'UTF-8', $response['charset']);
					}
					$response['charset_original'] = $response['charset'];
					$response['charset'] = 'UTF-8';
				}
				
				ksort($response);
			}else {
				wp_safe_redirect(esc_url(home_url('/')));
				die();
			}
			
			$item_transaction = (isset($response['txn_id'])?esc_attr($response['txn_id']):"");
			$last_payments    = get_user_meta($user_id,$user_id."_last_payments",true);
			
			if (isset($item_transaction)) {
				if (isset($last_payments) && $last_payments == $item_transaction) {
					wp_safe_redirect(esc_url(home_url('/')));
					die();
				}else {
					$item_no       = (isset($response['item_number'])?esc_attr($response['item_number']):"");
					$item_price    = (isset($response['mc_gross'])?esc_attr($response['mc_gross']):"");
					$item_currency = (isset($response['mc_currency'])?esc_attr($response['mc_currency']):"");
					$payer_email   = (isset($response['payer_email'])?esc_attr($response['payer_email']):"");
					$first_name    = (isset($response['first_name'])?esc_attr($response['first_name']):"");
					$last_name     = (isset($response['last_name'])?esc_attr($response['last_name']):"");
					$item_name     = (isset($response['item_name'])?esc_attr($response['item_name']):"");
					
					/* Coupon */
					$_coupon = get_user_meta($user_id,$user_id."_coupon",true);
					$_coupon_value = get_user_meta($user_id,$user_id."_coupon_value",true);
					
					/* Number of my payments */
					$_payments = get_user_meta($user_id,$user_id."_payments",true);
					if ($_payments == "") {
						$_payments = 0;
					}
					$_payments++;
					update_user_meta($user_id,$user_id."_payments",$_payments);
					
					add_user_meta($user_id,$user_id."_payments_".$_payments,
						array(
							"date_years" => date_i18n('Y/m/d',current_time('timestamp')),
							"date_hours" => date_i18n('g:i a',current_time('timestamp')),
							"item_number" => $item_no,
							"item_name" => $item_name,
							"item_price" => $item_price,
							"item_currency" => $item_currency,
							"item_transaction" => $item_transaction,
							"payer_email" => $payer_email,
							"first_name" => $first_name,
							"last_name" => $last_name,
							"user_id" => $user_id,
							"sandbox" => ($paypal_sandbox == 1?"sandbox":""),
							"time" => current_time('timestamp'),
							"coupon" => $_coupon,
							"coupon_value" => $_coupon_value
						)
					);
					
					/* New */
					$new_payments = get_option("new_payments");
					if ($new_payments == "") {
						$new_payments = 0;
					}
					$new_payments++;
					$update = update_option('new_payments',$new_payments);
					
					/* Money i'm paid */
					$_all_my_payment = get_user_meta($user_id,$user_id."_all_my_payment_".$item_currency,true);
					if($_all_my_payment == "" || $_all_my_payment == 0) {
						$_all_my_payment = 0;
					}
					update_user_meta($user_id,$user_id."_all_my_payment_".$item_currency,$_all_my_payment+$item_price);
					
					update_user_meta($user_id,$user_id."_last_payments",$item_transaction);
					
					/* All the payments */
					$payments_option = get_option("payments_option");
					if ($payments_option == "") {
						$payments_option = 0;
					}
					$payments_option++;
					update_option("payments_option",$payments_option);
					
					add_option("payments_".$payments_option,
						array(
							"date_years" => date_i18n('Y/m/d',current_time('timestamp')),
							"date_hours" => date_i18n('g:i a',current_time('timestamp')),
							"item_number" => $item_no,
							"item_name" => $item_name,
							"item_price" => $item_price,
							"item_currency" => $item_currency,
							"item_transaction" => $item_transaction,
							"payer_email" => $payer_email,
							"first_name" => $first_name,
							"last_name" => $last_name,
							"user_id" => $user_id,
							"sandbox" => ($paypal_sandbox == 1?"sandbox":""),
							"time" => current_time('timestamp'),
							"coupon" => $_coupon,
							"coupon_value" => $_coupon_value
						)
					);
					
					delete_user_meta($user_id,$user_id."_coupon",true);
					delete_user_meta($user_id,$user_id."_coupon_value",true);
					
					/* All money */
					$all_money = get_option("all_money_".$item_currency);
					if($all_money == "" || $all_money == 0) {
						$all_money = 0;
					}
					update_option("all_money_".$item_currency,$all_money+$item_price);
					
					/* The currency */
					$the_currency = get_option("the_currency");
					if((isset($the_currency) || !isset($the_currency)) && !is_array($the_currency)) {
						delete_option("the_currency");
						add_option("the_currency",array("USD"));
					}
					if (!in_array($item_currency,$the_currency)) {
						array_push($the_currency,$item_currency);
					}
					update_option("the_currency",$the_currency);
					
					if ($item_no == "pay_sticky") {
						$_question_sticky = get_user_meta($user_id,$user_id."_question_sticky",true);
						update_post_meta($_question_sticky,"sticky",1);
						$sticky_questions = get_option('sticky_questions');
						if (is_array($sticky_questions)) {
							if (!in_array($_question_sticky,$sticky_questions)) {
								$array_merge = array_merge($sticky_questions,array($_question_sticky));
								update_option("sticky_questions",$array_merge);
							}
						}else {
							update_option("sticky_questions",array($_question_sticky));
						}
						$sticky_posts = get_option('sticky_posts');
						if (is_array($sticky_posts)) {
							if (!in_array($_question_sticky,$sticky_posts)) {
								$array_merge = array_merge($sticky_posts,array($_question_sticky));
								update_option("sticky_posts",$array_merge);
							}
						}else {
							update_option("sticky_posts",array($_question_sticky));
						}
						$days_sticky = (int)vpanel_options("days_sticky");
						$days_sticky = ($days_sticky > 0?$days_sticky:7);
						update_post_meta($_question_sticky,"start_sticky_time",strtotime(date("Y-m-d")));
						update_post_meta($_question_sticky,"end_sticky_time",strtotime(date("Y-m-d",strtotime(date("Y-m-d")." +$days_sticky days"))));
						delete_user_meta($user_id,$user_id."_question_sticky");
					}else {
						/* Number allow to ask question */
						$_allow_to_ask = get_user_meta($user_id,$user_id."_allow_to_ask",true);
						if ($_allow_to_ask == "") {
							$_allow_to_ask = 0;
						}
						$_allow_to_ask++;
						update_user_meta($user_id,$user_id."_allow_to_ask",$_allow_to_ask);
						
						/* Paid question */
						update_user_meta($user_id,"_paid_question","paid");
					}
					
					update_user_meta($user_id,"item_transaction",$item_transaction);
					if ($paypal_sandbox == 1) {
						update_user_meta($user_id,"paypal_sandbox","sandbox");
					}
					
					if ($item_no == "pay_sticky") {
						update_post_meta($_question_sticky, 'item_transaction_sticky', $item_transaction);
						if ($paypal_sandbox == 1) {
							update_post_meta($_question_sticky, 'paypal_sandbox_sticky', 'sandbox');
						}
					}
					
					echo '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Successfully payment","vbegy").'</span><br>'.($item_no == "pay_sticky"?__("Thank you for your payment, Your question now is sticky.","vbegy"):__("Thank you for your payment you now can make a new question.","vbegy")).'</p></div>';
					$send_text = ask_send_email(vpanel_options("email_new_payment"),"","","","","",$item_price,$item_currency,$payer_email,$first_name,$last_name,$item_transaction,date('m/d/Y'),date('g:i A'));
					$logo_email_template = vpanel_options("logo_email_template");
					$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
					$email_title = vpanel_options("title_new_payment");
					$email_title = ($email_title != ""?$email_title:__("Instant Payment Notification - Received Payment","vbegy"));
					$email_template = vpanel_options("email_template");
					sendEmail($email_template,$first_name,$email_template,get_bloginfo('name'),$email_title,$last_message_email);
					if ($payer_email != $email_template) {
						sendEmail($email_template,$first_name,$payer_email,get_bloginfo('name'),$email_title,$last_message_email);
					}
					$_SESSION['vbegy_session_p'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Successfully payment","vbegy").'</span><br>'.($item_no == "pay_sticky"?__("Thank you for your payment, Your question now is sticky,","vbegy"):__("Thank you for your payment you now can make a new question.","vbegy")).' '.__("Your transaction id ".$item_transaction.", Please copy it.","vbegy").'</p></div>';
					if ($item_no == "" || $item_no == "pay_ask") {
						wp_safe_redirect(esc_url(get_page_link(vpanel_options('add_question'))));
					}else if (isset($_question_sticky) && $_question_sticky != "") {
						wp_safe_redirect(esc_url(get_the_permalink($_question_sticky)));
					}else {
						wp_safe_redirect(esc_url(home_url('/')));
					}
					die();
				}
			}else {
				echo '<div class="alert-message error"><i class="icon-ok"></i><p><span>'.__("Payment Failed","vbegy").'</span><br>'.__("The payment was failed!","vbegy").'</p></div>';
			}
		}else {
			wp_safe_redirect(esc_url(home_url('/')));
			die();
		}
	break;
	case 'cancel':
		echo '<div class="alert-message error"><i class="icon-ok"></i><p><span>'.__("Payment Canceled","vbegy").'</span><br>'.__("The payment was canceled!","vbegy").'</p></div>';
	break;
	case 'ipn':
		if ($p->validate_ipn()) { 
			$dated = date("D, d M Y H:i:s", time()); 
			
			$subject  = 'Instant Payment Notification - Received Payment';
			$body     =  "An instant payment notification was successfully recieved\n";
			$body    .= "from ".esc_attr($p->ipn_data['payer_email'])." on ".date('m/d/Y');
			$body    .= " at ".date('g:i A')."\n\nDetails:\n";
			$headers  = "";
			$headers .= "From: Paypal \r\n";
			$headers .= "Date: $dated \r\n";
			
			$PaymentStatus =  esc_attr($p->ipn_data['payment_status']);
			$Email         =  esc_attr($p->ipn_data['payer_email']);
			$id            =  esc_attr($p->ipn_data['item_number']);
			
			if($PaymentStatus == 'Completed' or $PaymentStatus == 'Pending') {
				$PaymentStatus = '2';
			}else {
				$PaymentStatus = '1';
			}
			mail(get_bloginfo("admin_email"), $subject, $body, $headers);
		}
	break;
}?>