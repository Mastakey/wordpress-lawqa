<?php ob_start();?>
<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 8]> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=9">
<link rel="profile" href="https://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
<?php wp_head();?>
</head>
<?php
$site_users_only = vpanel_options("site_users_only");
if ($site_users_only != 1) {
	$site_users_only = "no";
}else {
	$site_users_only = (!is_user_logged_in?"yes":"no");
}

$vbegy_layout = "";
if (is_single() || is_page()) {
	$vbegy_layout = rwmb_meta('vbegy_layout','radio',$post->ID);
	$vbegy_layout = ($vbegy_layout != ""?$vbegy_layout:"default");
}

$cat_layout = "";
if (is_category()) {
	$category_id = get_query_var('cat');
	$categories = get_option("categories_$category_id");
	$cat_layout = (isset($categories["cat_layout"])?$categories["cat_layout"]:"default");
}else if (is_tax("product_cat")) {
	$tax_id = get_term_by('slug',get_query_var('term'),"product_cat");
	$tax_id = $tax_id->term_id;
	$categories = get_option("categories_$tax_id");
	$cat_layout = (isset($categories["cat_layout"])?$categories["cat_layout"]:"default");
	if ($cat_layout == "" || $cat_layout == "default") {
		$cat_layout = vpanel_options("products_layout");
	}
}else if (is_tax("product_tag") || is_post_type_archive("product")) {
	$products_layout = vpanel_options("products_layout");
}else if (is_tax(ask_question_category)) {
	$tax_id = get_term_by('slug',get_query_var('term'),ask_question_category);
	$tax_id = $tax_id->term_id;
	$categories = get_option("categories_$tax_id");
	$cat_layout = (isset($categories["cat_layout"])?$categories["cat_layout"]:"default");
	if ($cat_layout == "default") {
		$cat_layout = vpanel_options("questions_layout");
	}
}else if (is_tax("question_tags") || is_post_type_archive("question")) {
	$questions_layout = vpanel_options("questions_layout");
}else if (is_single() || is_page()) {
	if (is_singular("product") && ($vbegy_layout == "" || $vbegy_layout == "default")) {
		$vbegy_layout = vpanel_options("products_layout");
	}
	if (is_singular("question") && ($vbegy_layout == "" || $vbegy_layout == "default")) {
		$vbegy_layout = vpanel_options("questions_layout");
	}
	if ($vbegy_layout == "" || $vbegy_layout == "default") {
		$vbegy_layout = vpanel_options("home_layout");
	}
}
$home_layout = vpanel_options("home_layout");
$top_panel_skin = vpanel_options("top_panel_skin");
$header_skin = vpanel_options("header_skin");
$header_fixed = vpanel_options("header_fixed");
$author_layout = vpanel_options("author_layout");
if (is_author() && $author_layout != "default" && $author_layout != "") {
	$home_layout = $author_layout;
}
if (is_singular("question")) {
	$questions_layout = vpanel_options("questions_layout");
}
if (is_singular("product")) {
	$products_layout = vpanel_options("products_layout");
}

$boxed_1 = "boxed";
$boxed_2 = "boxed2";
$boxed_end = "";
if (is_category() && $cat_layout != "default" && $cat_layout != "") {
	if ($cat_layout == "fixed") {
		$boxed_end = $boxed_1;
	}else if ($cat_layout == "fixed_2") {
		$boxed_end = $boxed_2;
	}
}else if (is_tax(ask_question_category) && $cat_layout != "default" && $cat_layout != "") {
	if ($cat_layout == "fixed") {
		$boxed_end = $boxed_1." ";
	}else if ($cat_layout == "fixed_2") {
		$boxed_end = $boxed_2." ";
	}
}else if (is_tax("question_tags") && $questions_layout != "default" && $questions_layout != "") {
	if ($questions_layout == "fixed") {
		$boxed_end = $boxed_1." ";
	}else if ($questions_layout == "fixed_2") {
		$boxed_end = $boxed_2." ";
	}
}else if (is_post_type_archive("question") && $questions_layout != "default" && $questions_layout != "") {
	if ($questions_layout == "fixed") {
		$boxed_end = $boxed_1." ";
	}else if ($questions_layout == "fixed_2") {
		$boxed_end = $boxed_2." ";
	}
}else if (is_tax("product_cat") && $cat_layout != "default" && $cat_layout != "") {
	if ($cat_layout == "fixed") {
		$boxed_end = $boxed_1." ";
	}else if ($cat_layout == "fixed_2") {
		$boxed_end = $boxed_2." ";
	}
}else if (is_tax("product_tag") && $products_layout != "default" && $products_layout != "") {
	if ($products_layout == "fixed") {
		$boxed_end = $boxed_1." ";
	}else if ($products_layout == "fixed_2") {
		$boxed_end = $boxed_2." ";
	}
}else if ((is_post_type_archive("product")) && $products_layout != "default" && $products_layout != "") {
	if ($products_layout == "fixed") {
		$boxed_end = $boxed_1." ";
	}else if ($products_layout == "fixed_2") {
		$boxed_end = $boxed_2." ";
	}
}else {
	if ((is_single() || is_page()) && $vbegy_layout != "default" && $vbegy_layout != "") {
		if ($vbegy_layout == "fixed") {
			$boxed_end = $boxed_1;
		}else if ($vbegy_layout == "fixed_2") {
			$boxed_end = $boxed_2;
		}else if ($vbegy_layout == "full") {
			$boxed_end = "";
		}
	}else {
		if (is_singular("product") && $products_layout != "default" && $products_layout != "") {
			if ($products_layout == "fixed") {
				$boxed_end = $boxed_1;
			}else if ($products_layout == "fixed_2") {
				$boxed_end = $boxed_2;
			}else if ($products_layout == "full") {
				$boxed_end = "";
			}
		}else if (is_singular("question") && $questions_layout != "default" && $questions_layout != "") {
			if ($questions_layout == "fixed") {
				$boxed_end = $boxed_1;
			}else if ($questions_layout == "fixed_2") {
				$boxed_end = $boxed_2;
			}else if ($questions_layout == "full") {
				$boxed_end = "";
			}
		}else {
			if ($home_layout == "fixed") {
				$boxed_end = $boxed_1;
			}else if ($home_layout == "fixed_2") {
				$boxed_end = $boxed_2;
			}else if ($home_layout == "full") {
				$boxed_end = "";
			}
		}
	}
}

$search_type = (isset($_GET["search_type"]) && $_GET["search_type"] != ""?esc_attr($_GET["search_type"]):vpanel_options("default_search"));

$user_id = 0;
if (is_user_logged_in) {
	$user_id = get_current_user_id();
}?>
<body <?php echo (isset($boxed_end) && $boxed_end != ""?"id='body_".$boxed_end."'":"")?> <?php body_class();?>>
	<div class="background-cover"></div>
	<?php
	$get_u = (isset($_GET['u']) && $_GET['u']?(int)$_GET['u']:"");
	$loader_option = vpanel_options("loader");
	if ($loader_option == 1) {?>
		<div class="loader"><div class="loader_html"></div></div>
	<?php }
	
	if ((!is_user_logged_in && (isset($_POST["form_type"]) && ($_POST["form_type"] == "ask-signup" || $_POST["form_type"] == "ask-login" || $_POST["form_type"] == "ask-forget"))) || (is_user_logged_in && isset($_POST["form_type"]) && ($_POST["form_type"] == "question-popup" || $_POST["form_type"] == "message-popup"))) {?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				function wrap_pop() {
					jQuery(".wrap-pop").click(function () {
						jQuery(".panel-pop").animate({"top":"-100%"},500).fadeOut(function () {
							jQuery(this).animate({"top":"-100%"},500);
						});
						jQuery(this).remove();
					});
				}
				jQuery(".panel-pop").animate({"top":"-100%"},10).hide();
				<?php if ($_POST["form_type"] == "ask-signup") {?>
					jQuery("#signup").show().animate({"top":"2%"},500);
				<?php }else if ($_POST["form_type"] == "ask-login") {?>
					jQuery("#login-comments").show().animate({"top":"2%"},500);
				<?php }else if ($_POST["form_type"] == "ask-forget") {?>
					jQuery("#lost-password").show().animate({"top":"2%"},500);
				<?php }else if ($_POST["form_type"] == "question-popup") {?>
					jQuery("#ask-question").show().animate({"top":"2%"},500);
				<?php }else if ($_POST["form_type"] == "message-popup") {?>
					jQuery("#send-message").show().animate({"top":"2%"},500);
				<?php }?>
				jQuery("html,body").animate({scrollTop:0},500);
				jQuery("body").prepend("<div class='wrap-pop'></div>");
				wrap_pop();
			});
		</script>
	<?php }
	
	if (!is_user_logged_in) {
		if ((isset($_POST["form_type"]) && ($_POST["form_type"] == "ask-signup" || $_POST["form_type"] == "empty-post")) || empty($_POST)) {?>
			<div class="panel-pop" id="signup">
				<h2><?php _e("Register Now","vbegy");?><i class="icon-remove"></i></h2>
				<div class="form-style form-style-3">
					<?php echo do_shortcode("[ask_signup]");?>
				</div>
			</div><!-- End signup -->
		<?php }
		
		if ((isset($_POST["form_type"]) && ($_POST["form_type"] == "ask-login" || $_POST["form_type"] == "empty-post")) || empty($_POST)) {?>
			<div class="panel-pop" id="login-comments">
				<h2><?php _e("Login","vbegy");?><i class="icon-remove"></i></h2>
				<div class="form-style form-style-3">
					<?php echo do_shortcode("[ask_login]");?>
				</div>
			</div><!-- End login-comments -->
		<?php }
		
		if ((isset($_POST["form_type"]) && ($_POST["form_type"] == "ask-forget" || $_POST["form_type"] == "empty-post")) || empty($_POST)) {?>
			<div class="panel-pop" id="lost-password">
				<h2><?php _e("Lost Password","vbegy");?><i class="icon-remove"></i></h2>
				<div class="form-style form-style-3">
					<p><?php _e("Lost your password? Please enter your email address. You will receive a link and will create a new password via email.","vbegy");?></p>
					<?php echo do_shortcode("[ask_lost_pass]");?>
					<div class="clearfix"></div>
				</div>
			</div><!-- End lost-password -->
		<?php }
	}

	if (isset($_POST["form_type"]) && ($_POST["form_type"] == "message-popup" || $_POST["form_type"] == "empty-post") || empty($_POST)) {
		$received_message = "";
		$user_block_message = array();
		$active_message = vpanel_options("active_message");
		$send_message_no_register = vpanel_options("send_message_no_register");
		if ($active_message == 1) {
			if (is_author()) {
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
				if ($get_u != "") {
					$user_login = get_userdata((int)(is_user_logged_in?$user_id:$get_u));
				}
			}
			if (isset($user_login) && is_object($user_login)) {
				$received_message = esc_attr( get_the_author_meta( 'received_message', $user_login->ID ) );
				$user_block_message = get_user_meta($user_login->ID,"user_block_message",true);
			}
			$block_message = esc_attr( get_the_author_meta( 'block_message', $user_id ) );
		}
		if ($active_message == 1 && ((!is_user_logged_in && $send_message_no_register == 1) || (is_user_logged_in && (empty($user_block_message) || (isset($user_block_message) && is_array($user_block_message) && !in_array(get_current_user_id(),$user_block_message))) && ($block_message != 1 || is_super_admin($user_id)) && ($received_message == "" || $received_message == 1)))) {?>
			<div class="panel-pop panel-pop-message" id="send-message">
				<h2><?php _e("Send Message","vbegy");?><i class="icon-remove"></i></h2>
				<div class="form-style form-style-3">
					<?php echo do_shortcode("[send_message type='popup']");?>
				</div>
			</div><!-- End ask-question -->
		<?php }
	}
	
	$ask_question_popup = vpanel_options("ask_question_popup");
	if ($ask_question_popup == 1 && (isset($_POST["form_type"]) && ($_POST["form_type"] == "add_question" || $_POST["form_type"] == "question-popup" || $_POST["form_type"] == "empty-post") || empty($_POST))) {?>
		<div class="panel-pop panel-pop-ask" id="ask-question">
			<h2><?php _e("Add question","vbegy");?><i class="icon-remove"></i></h2>
			<div class="form-style form-style-3">
				<?php echo do_shortcode("[ask_question type='popup']");?>
			</div>
		</div><!-- End ask-question -->
	<?php }
	
	if (is_tax("question_tags")) {
		$grid_template_q = vpanel_options("questions_template");
		$grid_template_q = ($grid_template_q != ""?$grid_template_q:"default");
	}
	if (is_author()) {
		$grid_template_a = vpanel_options("author_template");
		$grid_template = $grid_template_a;
	}else if (is_category()) {
		$grid_template_c = (isset($categories["cat_template"])?$categories["cat_template"]:"default");
		$grid_template = $grid_template_c;
	}else if (is_tax(ask_question_category)) {
		$grid_template_c = (isset($categories["cat_template"])?$categories["cat_template"]:"default");
		$grid_template = $grid_template_c;
		if ($grid_template == "default") {
			$grid_template_c = vpanel_options("questions_template");
			$grid_template = $grid_template_c;
		}
	}else if (is_tax("question_tags") || is_post_type_archive("question")) {
		$grid_template = vpanel_options("questions_template");
		$grid_template = ($grid_template != ""?$grid_template:"default");
	}else if (is_tax("product_cat")) {
		$grid_template_c = (isset($categories["cat_template"])?$categories["cat_template"]:"default");
		$grid_template = $grid_template_c;
		if ($grid_template == "" || $grid_template == "default") {
			$grid_template_c = vpanel_options("products_template");
			$grid_template = $grid_template_c;
		}
	}else if (is_tax("product_tag") || is_post_type_archive("product")) {
		$grid_template = vpanel_options("products_template");
		$grid_template = ($grid_template != ""?$grid_template:"default");
	}else {
		if (is_single() || is_page()) {
			$grid_template_s = rwmb_meta('vbegy_home_template','radio',$post->ID);
			if (is_singular("question")) {
				$grid_template_q = vpanel_options("questions_template");
				$grid_template_q = ($grid_template_q != ""?$grid_template_q:"default");
			}
			if (is_singular("product")) {
				$grid_template_p = vpanel_options("products_template");
				$grid_template_p = ($grid_template_p != ""?$grid_template_p:"default");
			}
		}
		if ((is_single() || is_page()) && ($grid_template_s != "default" && $grid_template_s != "")) {
			$grid_template = $grid_template_s;
		}else {
			if ((is_singular("question") && $grid_template_q != "default" && $grid_template_q != "")) {
				$grid_template = $grid_template_q;
			}else if ((is_singular("product") && $grid_template_p != "default" && $grid_template_p != "")) {
				$grid_template = $grid_template_p;
			}else {
				$grid_template = vpanel_options("home_template");
			}
		}
	}
	
	if ((is_author() && $grid_template_a == "default") || ((is_single() || is_page()) && $grid_template == "default") || (is_category() && $grid_template_c == "default") || (is_tax("product_cat") && ($grid_template_c == "" || $grid_template_c == "default")) || (is_tax("product_tag") && ($grid_template == "" || $grid_template == "default")) || ((is_post_type_archive("product")) && ($grid_template == "" || $grid_template == "default")) || (is_tax(ask_question_category) && ($grid_template_c == "" || $grid_template_c == "default")) || (is_tax("question_tags") && ($grid_template == "" || $grid_template == "default")) || ((is_post_type_archive("question")) && ($grid_template == "" || $grid_template == "default"))) {
		$grid_template = vpanel_options("home_template");
	}
	
	$nicescroll = vpanel_options("nicescroll");?>
	
	<?php $side_panel_skin = vpanel_options("side_panel_skin");?>
	<aside class="mobile-aside mobile-menu-wrap mobile-login-wrap<?php echo ($side_panel_skin == "light"?" light-mobile-menu panel_light":($side_panel_skin == "dark"?" dark-mobile-menu panel_dark":" gray-mobile-menu panel_light"))?>">
		<div class="mobile-aside-inner">
			<div class="mobile-aside-inner-inner">
				<a href="#" class="mobile-aside-close">x</a>
				<div class="row">
					<?php if (is_user_logged_in) {?>
						<div class="col-md-12">
							<div class="page-content">
								<?php echo is_user_logged_in_data(vpanel_options("user_links"))?>
							</div><!-- End page-content -->
						</div><!-- End col-md-12 -->
					<?php }else {?>
						<div class="col-md-6">
							<div class="page-content">
								<h2><?php _e("Login","vbegy")?></h2>
								<div class="form-style form-style-3">
									<?php echo do_shortcode("[ask_login]");?>
								</div>
							</div><!-- End page-content -->
						</div><!-- End col-md-6 -->
						<div class="col-md-6">
							<div class="page-content Register">
								<h2><?php _e("Register Now","vbegy")?></h2>
								<p><?php echo stripslashes(vpanel_options("register_content"))?></p>
								<div class="button color small signup"><?php _e("Create an account","vbegy")?></div>
							</div><!-- End page-content -->
						</div><!-- End col-md-6 -->
					<?php }?>
				</div>
			</div><!-- End mobile-aside-inner-inner -->
		</div><!-- End mobile-aside-inner -->
	</aside><!-- End mobile-aside -->
	
	<?php $search_page = vpanel_options('search_page');
	$live_search = vpanel_options("live_search");
	$search_var = (get_query_var('search') != ""?wp_unslash(esc_attr(get_query_var('search'))):wp_unslash(esc_attr(get_query_var('s'))));
	$header_notifications = vpanel_options("header_notifications");
	$active_notifications = vpanel_options("active_notifications");
	$mobile_menu = vpanel_options("mobile_menu");
	$mobile_cart = vpanel_options("mobile_cart");?>
	
	<aside class="mobile-aside mobile-menu-wrap<?php echo (class_exists('woocommerce') && $mobile_cart == 1?" aside-no-cart":" aside-no-cart").($mobile_menu == "light"?" light-mobile-menu":($mobile_menu == "dark"?" dark-mobile-menu":" gray-mobile-menu"))?>">
		<div class="mobile-aside-inner">
			<div class="mobile-aside-inner-inner">
				<a href="#" class="mobile-aside-close">x</a>
				<?php $top_menu_mobile = vpanel_options("top_menu_mobile");
				if ($top_menu_mobile == 1) {?>
					<div class="mobile-menu-top mobile-aside-menu">
						<?php if (is_user_logged_in) {
							wp_nav_menu(array('container_class' => 'header-top','menu_class' => 'menu_aside','theme_location' => 'top_bar_login','fallback_cb' => 'vpanel_nav_fallback'));
						}else {
							wp_nav_menu(array('container_class' => 'header-top','menu_class' => 'menu_aside','theme_location' => 'top_bar','fallback_cb' => 'vpanel_nav_fallback'));
						}?>
					</div>
				<?php }
				
				$ask_question_mobile = vpanel_options("ask_question_mobile");
				if ($ask_question_mobile == 1) {?>
					<div class="ask-question-menu">
						<a href="<?php echo esc_url(get_page_link(vpanel_options('add_question')))?>" class="color button small margin_0"><?php _e("Ask a Question","vbegy")?></a>
					</div><!-- End ask-question-menu -->
				<?php }
				
				
				if (class_exists('woocommerce') && $mobile_cart == 1) {
					echo "<div class='cart-wrapper'>";
						global $woocommerce;
						$num = $woocommerce->cart->cart_contents_count;
						echo '<a href="'.$woocommerce->cart->get_cart_url().'" class="cart_control nav-button nav-cart"><i class="enotype-icon-cart"></i>
							<span class="numofitems" data-num="'.$num.'">'.$num.'</span>
						</a>
						<div class="cart_wrapper'.(sizeof($woocommerce->cart->get_cart()) < 1?" cart_wrapper_empty":"").'"><div class="widget_shopping_cart_content"></div></div>
					</div>';
				}
				
				if (is_user_logged_in) {
					$mobile_notifications = vpanel_options("mobile_notifications");
					if ($mobile_notifications == 1 && $active_notifications == 1) {
						include("includes/notification.php");
					}
				}
				
				$search_mobile = vpanel_options("search_mobile");
				if ($search_mobile == 1) {?>
					<div class="post-search">
						<form role="search" method="get" class="searchform" action="<?php echo esc_url((isset($search_page) && $search_page != ""?get_page_link($search_page):""))?>">
							<div class="row">
								<div class="col-md-8">
									<div class="mobile-search-result">
										<input<?php echo ($live_search == 1?" class='live-search' autocomplete='off'":"")?> type="search" name="search" value="<?php echo ($search_var != ""?$search_var:esc_html__("Hit enter to search","vbegy"))?>" onfocus="if(this.value=='<?php esc_html_e("Hit enter to search","vbegy")?>')this.value='';" onblur="if(this.value=='')this.value='<?php esc_html_e("Hit enter to search","vbegy")?>';">
										<input type="hidden" name="page_id" value="<?php echo esc_attr($search_page)?>">
										<input type="hidden" name="search_type" value="<?php echo esc_attr($search_type)?>">
										<?php if ($live_search == 1) {?>
											<div class="loader_2 search_loader"></div>
											<div class="search-results results-empty"></div>
										<?php }?>
									</div>
								</div><!-- End col-md-8 -->
								<div class="col-md-4">
									<input type="submit" class="button-default" value="<?php esc_html_e('Search','vbegy')?>">
								</div><!-- End col-md-4 -->
							</div><!-- End row -->
						</form>
					</div>
				<?php }
				
				$main_menu_mobile = vpanel_options("main_menu_mobile");
				if ($main_menu_mobile == 1) {?>
					<div class="mobile-menu-left mobile-aside-menu">
						<?php wp_nav_menu(array('container_class' => 'header-menu','menu_class' => 'menu_aside','theme_location' => 'header_menu','fallback_cb' => 'vpanel_nav_fallback'));?>
					</div><!-- End mobile-menu-left -->
				<?php }
				
				$social_mobile = vpanel_options("social_mobile");
				if ($social_mobile == 1) {?>
					<div class="social_icons f_right">
						<?php include("includes/social.php");?>
					</div><!-- End social_icons -->
				<?php }?>
			</div><!-- End mobile-aside-inner-inner -->
		</div><!-- End mobile-aside-inner -->
	</aside><!-- End mobile-aside -->
	
	<div id="wrap" class="<?php echo $grid_template." ";if ($header_fixed == 1) {echo "fixed-enabled ";}echo $boxed_end;if ($nicescroll == 1) {echo " wrap-nicescroll";}?>">
		
		<?php $login_panel = vpanel_options("login_panel");
		$top_menu = vpanel_options("top_menu");
		if ($login_panel == 1 && $top_menu == 1) {?>
			<div class="login-panel <?php if ($top_panel_skin == "panel_light") {echo "panel_light";}else {echo "panel_dark";}?>">
				<section class="container">
					<div class="row">
						<?php if (is_user_logged_in) {?>
							<div class="col-md-12">
								<div class="page-content">
									<?php echo is_user_logged_in_data(vpanel_options("user_links"))?>
								</div><!-- End page-content -->
							</div><!-- End col-md-12 -->
						<?php }else {?>
							<div class="col-md-6">
								<div class="page-content">
									<h2><?php _e("Login","vbegy")?></h2>
									<div class="form-style form-style-3">
										<?php echo do_shortcode("[ask_login]");?>
									</div>
								</div><!-- End page-content -->
							</div><!-- End col-md-6 -->
							<div class="col-md-6">
								<div class="page-content Register">
									<h2><?php _e("Register Now","vbegy")?></h2>
									<p><?php echo stripslashes(vpanel_options("register_content"))?></p>
									<div class="button color small signup"><?php _e("Create an account","vbegy")?></div>
								</div><!-- End page-content -->
							</div><!-- End col-md-6 -->
						<?php }?>
					</div>
				</section>
			</div><!-- End login-panel -->
		<?php }
		
		if ($top_menu) {
			$top_header_layout = vpanel_options("top_header_layout");
			if ($top_header_layout == "header_2c_2") {
				$top_header_menu = "col-md-5";
				$top_header_left = "col-md-7";
			}else if ($top_header_layout == "header_2c_3") {
				$top_header_menu = "col-md-7";
				$top_header_left = "col-md-5";
			}else if ($top_header_layout == "menu") {
				$top_header_menu = "col-md-12";
				$top_header_left = "";
			}else if ($top_header_layout == "left_ontent") {
				$top_header_menu = "";
				$top_header_left = "col-md-12";
			}else {
				$top_header_menu = "col-md-6";
				$top_header_left = "col-md-6";
			}?>
			<div id="header-top">
				<section class="container clearfix">
					<div class="row">
						<?php if ($top_header_menu != "") {?>
							<div class="<?php echo esc_attr($top_header_menu)?>">
								<nav class="header-top-nav">
									<?php 
									if (is_user_logged_in) {
										wp_nav_menu(array('container_class' => 'header-top','menu_class' => '','theme_location' => 'top_bar_login','fallback_cb' => 'vpanel_nav_fallback'));
									}else {
										wp_nav_menu(array('container_class' => 'header-top','menu_class' => '','theme_location' => 'top_bar','fallback_cb' => 'vpanel_nav_fallback'));
									}?>
								</nav>
								<div class="f_left language_selector">
									<?php do_action('icl_language_selector'); ?>
								</div>
								<div class="clearfix"></div>
							</div><!-- End col-md-* -->
						<?php }
						
						if ($top_header_left != "") {?>
							<div class="<?php echo esc_attr($top_header_left).($top_header_menu == ""?" top-header-left":"")?>">
								<?php $social_icon_h = vpanel_options("social_icon_h");
								if ($social_icon_h == 1) {?>
									<div class="social_icons f_right<?php echo (!is_search() && !is_page_template("template-search.php")?"":" not-show-search")?>">
										<?php include("includes/social.php");?>
									</div><!-- End social_icons -->
								<?php }
								
								$header_search = vpanel_options("header_search");
								if ($header_search == 1 && !is_search() && !is_page_template("template-search.php")) {?>
									<div class="header-search">
										<form method="get" action="<?php echo esc_url((isset($search_page) && $search_page != ""?get_page_link($search_page):""))?>">
											<input<?php echo ($live_search == 1?" class='live-search header-live-search' autocomplete='off'":"")?> type="text" value="<?php echo ($search_var != ""?$search_var:esc_html__("Search here ...","vbegy"))?>" onfocus="if(this.value=='<?php _e("Search here ...","vbegy");?>')this.value='';" onblur="if(this.value=='')this.value='<?php _e("Search here ...","vbegy");?>';" name="search">
										    <input type="hidden" name="page_id" value="<?php echo esc_attr($search_page)?>">
										    <input type="hidden" name="search_type" value="<?php echo esc_attr($search_type)?>">
										    <?php if ($live_search == 1) {?>
										    	<div class="search-results results-empty"></div>
										    <?php }?>
										    <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
										</form>
									</div>
								<?php }
								
								$header_cart = vpanel_options("header_cart");
								if (class_exists('woocommerce') && $header_cart == 1) {
									echo "<div class='cart-wrapper'>";
										global $woocommerce;
										$num = $woocommerce->cart->cart_contents_count;
										echo '<a href="'.$woocommerce->cart->get_cart_url().'" class="cart_control nav-button nav-cart"><i class="enotype-icon-cart"></i>
											<span class="numofitems" data-num="'.$num.'">'.$num.'</span>
										</a>
										<div class="cart_wrapper'.(sizeof($woocommerce->cart->get_cart()) < 1?" cart_wrapper_empty":"").'"><div class="widget_shopping_cart_content"></div></div>
									</div>';
								}
								
								if (is_user_logged_in) {
									if ($header_notifications == 1 && $active_notifications == 1) {
										include("includes/notification.php");
									}
								}?>
								<div class="clearfix"></div>
							</div><!-- End col-md-* -->
						<?php }?>
					</div><!-- End row -->
				</section><!-- End container -->
			</div><!-- End header-top -->
		<?php }
		
		$index_top_box = "";
		if ((is_home() || is_front_page()) && !is_page_template("template-home.php")) {
			$index_top_box = vpanel_options('index_top_box');
			$index_top_box_layout = vpanel_options('index_top_box_layout');
			$index_title_comment = vpanel_options('index_title_comment');
			$index_about = vpanel_options('index_about');
			$index_about_h = vpanel_options('index_about_h');
			$index_join = vpanel_options('index_join');
			$index_join_h = vpanel_options('index_join_h');
			$index_about_login = vpanel_options('index_about_login');
			$index_about_h_login = vpanel_options('index_about_h_login');
			$index_join_login = vpanel_options('index_join_login');
			$index_join_h_login = vpanel_options('index_join_h_login');
			$index_title = vpanel_options("index_title");
			$index_content = vpanel_options("index_content");
			$index_top_box_background = vpanel_options("index_top_box_background");
			$background_home = vpanel_options("background_home");
			$background_full_home = vpanel_options("background_full_home");
			$background_full_home = ($background_full_home == 1?"-webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;":"");
			$background_color_home = (isset($background_home["color"]) && $background_home["color"] != ""?"background-color:".$background_home["color"].";":"");
			$background_img_home = (isset($background_home["image"]) && $background_home["image"] != ""?"background-image:url(".$background_home["image"].");":"");
			$background_repeat_home = (isset($background_home["repeat"]) && $background_home["repeat"] != ""?"background-repeat:".$background_home["repeat"].";":"");
			$background_fixed_home = (isset($background_home["attachment"]) && $background_home["attachment"] != ""?"background-attachment:".$background_home["attachment"].";":"");
			$background_position_home = (isset($background_home["position"]) && $background_home["position"] != ""?"background-position:".$background_home["position"].";":"");
			if ($index_top_box_background == "background") {
				$index_top_box_style = "style='".$background_color_home.$background_img_home.$background_repeat_home.$background_fixed_home.$background_position_home.$background_full_home."'";
			}else {
				$index_top_box_style = "";
			}
			$remove_index_content = vpanel_options("remove_index_content");
		}else {
			if (is_page_template("template-home.php")) {
				$index_top_box = rwmb_meta('vbegy_index_top_box','checkbox',$post->ID);
				$index_top_box_layout = rwmb_meta('vbegy_index_top_box_layout','radio',$post->ID);
				$index_title_comment = rwmb_meta('vbegy_index_title_comment','radio',$post->ID);
				$index_about = rwmb_meta('vbegy_index_about','text',$post->ID);
				$index_about_h = rwmb_meta('vbegy_index_about_h','text',$post->ID);
				$index_join = rwmb_meta('vbegy_index_join','text',$post->ID);
				$index_join_h = rwmb_meta('vbegy_index_join_h','text',$post->ID);
				$index_about_login = rwmb_meta('vbegy_index_about_login','text',$post->ID);
				$index_about_h_login = rwmb_meta('vbegy_index_about_h_login','text',$post->ID);
				$index_join_login = rwmb_meta('vbegy_index_join_login','text',$post->ID);
				$index_join_h_login = rwmb_meta('vbegy_index_join_h_login','text',$post->ID);
				$index_title = rwmb_meta('vbegy_index_title','text',$post->ID);
				$index_content = rwmb_meta('vbegy_index_content','textarea',$post->ID);
				$index_top_box_background = rwmb_meta('vbegy_index_top_box_background','radio',$post->ID);
				$upload_images_home = rwmb_meta('vbegy_upload_images_home','image_advanced',$post->ID);
				$background_color_home = rwmb_meta('vbegy_background_color_home','color',$post->ID);
				$background_img_home = rwmb_meta('vbegy_background_img_home','upload',$post->ID);
				$background_repeat_home = rwmb_meta('vbegy_background_repeat_home','select',$post->ID);
				$background_fixed_home = rwmb_meta('vbegy_background_fixed_home','select',$post->ID);
				$background_position_x_home = rwmb_meta('vbegy_background_position_x_home','select',$post->ID);
				$background_position_y_home = rwmb_meta('vbegy_background_position_y_home','select',$post->ID);
				$background_full_home = rwmb_meta('vbegy_background_full_home','checkbox',$post->ID);
				$background_color_home = (isset($background_color_home) && $background_color_home != ""?"background-color:".$background_color_home.";":"");
				$background_img_home = (isset($background_img_home) && $background_img_home != ""?"background-image:url(".$background_img_home.");":"");
				$background_repeat_home = (isset($background_repeat_home) && $background_repeat_home != ""?"background-repeat:".$background_repeat_home.";":"");
				$background_fixed_home = (isset($background_fixed_home) && $background_fixed_home != ""?"background-attachment:".$background_fixed_home.";":"");
				$background_position_x_home = (isset($background_position_x_home) && $background_position_x_home != ""?"background-position-x:".$background_position_x_home.";":"");
				$background_position_y_home = (isset($background_position_y_home) && $background_position_y_home != ""?"background-position-y:".$background_position_y_home.";":"");
				$background_full_home = ($background_full_home == 1?"-webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;":"");
				if ($index_top_box_background == "background") {
					$index_top_box_style = "style='".$background_color_home.$background_img_home.$background_repeat_home.$background_fixed_home.$background_position_x_home.$background_position_y_home.$background_full_home."'";
				}else {
					$index_top_box_style = "";
				}
				$remove_index_content = rwmb_meta('vbegy_remove_index_content','checkbox',$post->ID);
			}
		}
		
		$breadcrumbs = vpanel_options("breadcrumbs");
		$logo_position = vpanel_options("logo_position");
		$logo_display = vpanel_options("logo_display");
		$logo_img = vpanel_options("logo_img");
		$retina_logo = vpanel_options("retina_logo");
		$logo_height = vpanel_options("logo_height");
		$logo_width = vpanel_options("logo_width");
		
		if (is_tax("product_cat") || is_tax("product_tag") || is_post_type_archive("product") || is_singular("product")) {
			$products_custom_header = vpanel_options("products_custom_header");
			if ($products_custom_header == 1) {
				$logo_position = vpanel_options("products_logo_position");
				$header_skin = vpanel_options("products_header_skin");
				$logo_display = vpanel_options("products_logo_display");
				$logo_img = vpanel_options("products_logo_img");
				$retina_logo = vpanel_options("products_retina_logo");
				$logo_height = vpanel_options("products_logo_height");
				$logo_width = vpanel_options("products_logo_width");
			}
		}else if (is_tax(ask_question_category) || is_tax("question_tags") || is_post_type_archive("question") || is_singular("question")) {
			$questions_custom_header = vpanel_options("questions_custom_header");
			if ($questions_custom_header == 1) {
				$logo_position = vpanel_options("questions_logo_position");
				$header_skin = vpanel_options("questions_header_skin");
				$logo_display = vpanel_options("questions_logo_display");
				$logo_img = vpanel_options("questions_logo_img");
				$retina_logo = vpanel_options("questions_retina_logo");
				$logo_height = vpanel_options("questions_logo_height");
				$logo_width = vpanel_options("questions_logo_width");
			}
		}?>
		<header id="header" class='<?php if ($header_skin == "header_light") {echo "header_light ";}
		if (is_front_page() || is_home()) {
			if ($index_top_box != 1) {
				echo "index-no-box ";
			}
		}else {
			if (is_page_template("template-home.php")) {
				if (($breadcrumbs != 1 && $index_top_box != 1) || ($breadcrumbs == 1 && $index_top_box != 1)) {
					echo "index-no-box ";
				}
			}else {
				if ($breadcrumbs != 1 && $index_top_box != 1) {
					echo "index-no-box ";
				}
			}
		}
		if ($logo_position == "right_logo") {echo "header_2 ";}else if ($logo_position == "center_logo") {echo "header_3 ";}?>'>
			<section class="container clearfix">
				<div class="logo">
					<?php if ($logo_display == "custom_image") {?>
					    <a class="logo-img" href="<?php echo esc_url(home_url('/'));?>" itemprop="url" title="<?php echo esc_attr(get_bloginfo('name','display'))?>">
					    	<?php if (isset($logo_img) && $logo_img != "") {?>
					    		<img width="<?php echo (int)$logo_width?>" height="<?php echo (int)$logo_height?>" class="default_logo" itemprop="logo" alt="<?php echo esc_attr(get_bloginfo('name','display'))?>" src="<?php echo $logo_img?>">
					    	<?php }
					    	if (isset($retina_logo) && $retina_logo != "") {?>
					    		<img width="<?php echo (int)$logo_width?>" height="<?php echo (int)$logo_height?>" class="retina_logo" itemprop="logo" alt="<?php echo esc_attr(get_bloginfo('name','display'))?>" src="<?php echo esc_attr($retina_logo)?>">
					    	<?php }
					    	if ($retina_logo == "" && isset($logo_img) && $logo_img != "") {?>
					    		<img width="<?php echo (int)$logo_width?>" height="<?php echo (int)$logo_height?>" class="retina_logo" itemprop="logo" alt="<?php echo esc_attr(get_bloginfo('name','display'))?>" src="<?php echo esc_attr($logo_img)?>">
					    	<?php }?>
					    </a>
					<?php }else {?>
						<h2><a href="<?php echo esc_url(home_url('/'));?>" itemprop="url" title="<?php echo esc_attr(get_bloginfo('name','display'))?>"><?php bloginfo('name');?></a></h2>
					<?php }?>
					<meta itemprop="name" content="<?php bloginfo('name'); ?>">
				</div>
				<nav class="navigation">
					<?php wp_nav_menu(array('container_class' => 'header-menu','menu_class' => '','theme_location' => 'header_menu','fallback_cb' => 'vpanel_nav_fallback'));?>
				</nav>
				<div class="mobile-menu">
					<div class="mobile-menu-click navigation_mobile"></div>
				</div>
			</section><!-- End container -->
		</header><!-- End header -->
		
		<?php $top_after_header = false;
		if ($site_users_only != "yes") {
			if (is_page_template("template-home.php") || is_front_page()) {
				$ask_a_new_question = __("Ask a question and you will be sure to find an answer!","vbegy");
				if ($index_top_box == 1) {?>
					<div class="section-warp top-after-header<?php echo (isset($remove_index_content) && $remove_index_content == 1?" remove-index-content":"")?>"<?php echo $index_top_box_style?>>
						<?php
						if ($index_top_box_background == "slideshow") {
							$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'vbegy_upload_images_home' AND post_id = {$post->ID}");?>
							<div class="flexslider blog_silder margin_b_20 post-img">
							    <ul class="slides">
							    	<?php
							    	foreach ($result as $results) {
							    	    $slideshow_imgs = $results->meta_value.',';
							    	    $slideshow_imgs = explode(",",$slideshow_imgs);
							    	    $images = $wpdb->get_col("
							    	    SELECT ID FROM $wpdb->posts
							    	    WHERE post_type = 'attachment'
							    	    AND ID IN ('".implode("','",$slideshow_imgs)."')
							    	    ORDER BY menu_order ASC");
							    	    foreach ($images as $att) {
								    	    $src = wp_get_attachment_image_src($att,'full');
								    	    $src = $src[0];?>
								    	    <li><img alt="" src="<?php echo $src;?>"></li>
								    	<?php }
							    	}?>
							    </ul>
							</div><!-- End flexslider -->
							<?php
						}?>
						<div class="container clearfix">
							<div class="box_icon box_warp box_no_border box_no_background">
								<div class="row">
									<?php $remove_index_content = apply_filters("askme_filter_remove_content",$remove_index_content);
									if ($remove_index_content != 1) {
										if ($index_top_box_layout == 2) {?>
											<div class="col-md-12">
												<h2><?php echo stripslashes($index_title);?></h2>
												<p><?php echo stripslashes($index_content);?></p>
												<div class="clearfix"></div>
												<?php if (is_user_logged_in) {
													if ($index_about_login != "") {?>
														<a class="color button dark_button medium" href="<?php echo $index_about_h_login?>"><?php echo $index_about_login?></a>
													<?php }
													if ($index_join_login != "") {?>
														<a class="color button dark_button medium" href="<?php echo $index_join_h_login?>"><?php echo $index_join_login?></a>
													<?php }
												}else {
													if ($index_about != "") {?>
														<a class="color button dark_button medium" href="<?php echo $index_about_h?>"><?php echo $index_about?></a>
													<?php }
													if ($index_join != "") {?>
														<a class="color button dark_button medium" href="<?php echo $index_join_h?>"><?php echo $index_join?></a>
													<?php }
												}?>
												
												<div class="clearfix"></div>
												<form class="form-style form-style-2" method="post" action="<?php echo esc_url(get_page_link(vpanel_options('add_question')))?>">
													<p>
														<input name="<?php echo ($index_title_comment == "comment"?"comment":"title")?>" type="text" id="question_title" value="<?php echo $ask_a_new_question;?>" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;">
														<i class="icon-pencil"></i>
														<button class="ask-question"><span class="color button small publish-question<?php echo (is_user_logged_in?"":" ask-not-login")?>"><?php _e("Ask Now","vbegy");?></span></button>
													</p>
													<input type="hidden" name="form_type" value="empty-post">
												</form>
											</div>
										<?php }else {?>
											<div class="col-md-3">
												<h2><?php echo stripslashes($index_title);?></h2>
												<p><?php echo stripslashes($index_content);?></p>
												<div class="clearfix"></div>
												<?php if (is_user_logged_in) {
													if ($index_about_login != "") {?>
														<a class="color button dark_button medium" href="<?php echo $index_about_h_login?>"><?php echo $index_about_login?></a>
													<?php }
													if ($index_join_login != "") {?>
														<a class="color button dark_button medium" href="<?php echo $index_join_h_login?>"><?php echo $index_join_login?></a>
													<?php }
												}else {
													if ($index_about != "") {?>
														<a class="color button dark_button medium" href="<?php echo $index_about_h?>"><?php echo $index_about?></a>
													<?php }
													if ($index_join != "") {?>
														<a class="color button dark_button medium" href="<?php echo $index_join_h?>"><?php echo $index_join?></a>
													<?php }
												}?>
											</div>
											<div class="col-md-9">
												<form class="form-style form-style-2" method="post" action="<?php echo esc_url(get_page_link(vpanel_options('add_question')))?>">
													<p>
														<textarea name="<?php echo ($index_title_comment == "comment"?"comment":"title")?>" rows="4" id="question_title" onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;"><?php echo $ask_a_new_question;?></textarea>
														<i class="icon-pencil"></i>
														<button class="ask-question"><span class="color button small publish-question<?php echo (is_user_logged_in?"":" ask-not-login")?>"><?php _e("Ask Now","vbegy");?></span></button>
													</p>
													<input type="hidden" name="form_type" value="empty-post">
												</form>
											</div>
										<?php }
									}else {
										do_action("askme_action_without_content");
									}?>
								</div><!-- End row -->
							</div><!-- End box_icon -->
						</div><!-- End container -->
					</div><!-- End section-warp -->
				<?php
				}
			}else {
				if (is_singular("question")) {
					$yes_private = ask_private($post->ID,$post->post_author,$user_id);
					if ($yes_private != 1 && is_singular("question")) {
						$breadcrumbs = 0;
						$yes_put_it = 1;
					}
				}
				if ($breadcrumbs == 1) {
					breadcrumbs();
				}
				if (isset($yes_put_it) && $yes_put_it == 1) {
					echo "<div class='index-no-box'></div>";
				}
			}
		}// End if site_users_only
		
		if ($top_after_header == false) {
			echo "<div class='index-no-box'></div>";
		}
		
		$big_video = vpanel_options("big_video");
		$big_video_work = vpanel_options("big_video_work");
		if (($big_video_work == "all_pages" || ((is_front_page() || is_home()) && $big_video_work == "home_page") || (!is_front_page() && !is_home() && $big_video_work == "pages_no_home")) && $big_video == 1) {?>
			<div class="section-warp top-after-header big-video">
				<div class="container clearfix">
					<div class="box_icon box_warp box_no_border box_no_background">
						<div class="row">
							<div class="col-md-12">
								<?php $video_height = vpanel_options('video_height');
								$video_id = vpanel_options('video_id');
								$video_type = vpanel_options('video_type');
								if ($video_type == 'youtube') {
									$type = "https://www.youtube.com/embed/".$video_id;
								}else if ($video_type == 'vimeo') {
									$type = "https://player.vimeo.com/video/".$video_id;
								}else if ($video_type == 'daily') {
									$type = "https://www.dailymotion.com/embed/video/".$video_id;
								}else if ($video_type == 'facebook') {
									$type = "https://www.facebook.com/video/embed?video_id=".$video_id;
								}
								$video_mp4 = vpanel_options('video_mp4');
								$video_m4v = vpanel_options('video_m4v');
								$video_webm = vpanel_options('video_webm');
								$video_ogv = vpanel_options('video_ogv');
								$video_wmv = vpanel_options('video_wmv');
								$video_flv = vpanel_options('video_flv');
								$video_image = vpanel_options('video_image');
								$video_mp4 = (isset($video_mp4) && $video_mp4 != ""?" mp4='".$video_mp4."'":"");
								$video_m4v = (isset($video_m4v) && $video_m4v != ""?" m4v='".$video_m4v."'":"");
								$video_webm = (isset($video_webm) && $video_webm != ""?" webm='".$video_webm."'":"");
								$video_ogv = (isset($video_ogv) && $video_ogv != ""?" ogv='".$video_ogv."'":"");
								$video_wmv = (isset($video_wmv) && $video_wmv != ""?" wmv='".$video_wmv."'":"");
								$video_flv = (isset($video_flv) && $video_flv != ""?" flv='".$video_flv."'":"");
								$video_image = (isset($video_image) && $video_image != ""?" poster='".$video_image."'":"");
								if ($video_type == "html5") {
									echo do_shortcode('[video'.$video_mp4.$video_m4v.$video_webm.$video_ogv.$video_wmv.$video_flv.$video_image.']');
								}else if ($video_type == "embed") {
									echo vpanel_options('custom_embed');
								}else if (isset($type) && $type != "") {
									echo '<iframe frameborder="0" allowfullscreen height="'.$video_height.'" src="'.$type.'"></iframe>';
								}?>
							</div>
						</div><!-- End row -->
					</div><!-- End box_icon -->
				</div><!-- End container -->
			</div><!-- End section-warp -->
		<?php }
		
		$big_search = vpanel_options("big_search");
		$big_search_work = vpanel_options("big_search_work");
		if (($big_search_work == "all_pages" || ((is_front_page() || is_home()) && $big_search_work == "home_page") || (!is_front_page() && !is_home() && $big_search_work == "pages_no_home")) && $big_search == 1 && !is_search() && !is_page_template("template-search.php")) {?>
			<div class="section-warp top-after-header big-search">
				<div class="container clearfix">
					<div class="box_icon box_warp box_no_border box_no_background">
						<div class="row">
							<div class="col-md-12">
								<form class="form-style form-style-2" method="get" action="<?php echo esc_url((isset($search_page) && $search_page != ""?get_page_link($search_page):""))?>">
									<div class="search-p">
										<input<?php echo ($live_search == 1?" class='live-search live-search-big' autocomplete='off'":"")?> type="text" value="<?php echo ($search_var != ""?$search_var:esc_html__("Search here ...","vbegy"))?>" onfocus="if(this.value=='<?php _e("Search here ...","vbegy");?>')this.value='';" onblur="if(this.value=='')this.value='<?php _e("Search here ...","vbegy");?>';" name="search">
										<input type="hidden" name="page_id" value="<?php echo esc_attr($search_page)?>">
										<input type="hidden" name="search_type" value="<?php echo esc_attr($search_type)?>">
										<i class="fa fa-search"></i>
										<button class="ask-search"><span class="color button small publish-question"><?php _e("Search","vbegy");?></span></button>
										<?php if ($live_search == 1) {?>
											<div class="search-results results-empty"></div>
										<?php }?>
									</div>
								</form>
							</div>
						</div><!-- End row -->
					</div><!-- End box_icon -->
				</div><!-- End container -->
			</div><!-- End section-warp -->
		<?php }
		
		do_action("askme_after_header_search");
		
		$sidebar_width = vpanel_options("sidebar_width");
		$sidebar_width = (isset($sidebar_width) && $sidebar_width != ""?$sidebar_width:"col-md-3");
		$sidebar_layout = "";
		if (isset($sidebar_width) && $sidebar_width == "col-md-3") {
			$container_span = "col-md-9";
		}else {
			$container_span = "col-md-8";
		}
		$full_span = "col-md-12";
		$page_right = "page-right-sidebar";
		$page_left = "page-left-sidebar";
		$page_full_width = "page-full-width";
		
		$sidebar_dir = vpanel_sidebars("sidebar_dir");
		$homepage_content_span = vpanel_sidebars("homepage_content_span");
		$sidebar_class = vpanel_sidebars("sidebar_class");
		
		$confirm_email = vpanel_options("confirm_email");
		$user_review = vpanel_options("user_review");
		
		if (is_user_logged_in && ($confirm_email == 1 || $user_review == 1)) {
			$if_user_id = get_user_by("id",$user_id);
		}
		
		if (is_user_logged_in && $confirm_email == 1) {
			if (isset($if_user_id->caps["activation"]) && $if_user_id->caps["activation"] == 1) {
				$site_users_only = "yes";
			}
		}
		
		if (is_user_logged_in && $user_review == 1) {
			if (isset($if_user_id->caps["ask_under_review"]) && $if_user_id->caps["ask_under_review"] == 1) {
				$site_users_only = "yes";
			}
		}
		
		$adv_404 = vpanel_options("adv_404");
		if (is_404() && $adv_404 == 1) {
			$adv_404 = "on";
		}else {
			$adv_404 = "";
		}
		
		if ($site_users_only != "yes") {
			if (($adv_404 != "on" && is_404()) || !is_404()) {
				if (is_single() || is_page()) {
					$vbegy_header_adv_type = rwmb_meta('vbegy_header_adv_type','radio',$post->ID);
					$vbegy_header_adv_code = rwmb_meta('vbegy_header_adv_code','textarea',$post->ID);
					$vbegy_header_adv_href = rwmb_meta('vbegy_header_adv_href','text',$post->ID);
					$vbegy_header_adv_img = rwmb_meta('vbegy_header_adv_img','upload',$post->ID);
				}
				
				if ((is_single() || is_page()) && (($vbegy_header_adv_type == "display_code" && $vbegy_header_adv_code != "") || ($vbegy_header_adv_type == "custom_image" && $vbegy_header_adv_img != ""))) {
					$header_adv_type = $vbegy_header_adv_type;
					$header_adv_code = $vbegy_header_adv_code;
					$header_adv_href = $vbegy_header_adv_href;
					$header_adv_img = $vbegy_header_adv_img;
				}else {
					$header_adv_type = vpanel_options("header_adv_type");
					$header_adv_code = vpanel_options("header_adv_code");
					$header_adv_href = vpanel_options("header_adv_href");
					$header_adv_img = vpanel_options("header_adv_img");
				}
				if (($header_adv_type == "display_code" && $header_adv_code != "") || ($header_adv_type == "custom_image" && $header_adv_img != "")) {
					echo '<div class="clearfix"></div>
					<div class="advertising">';
					if ($header_adv_type == "display_code") {
						echo stripcslashes(do_shortcode($header_adv_code));
					}else {
						if ($header_adv_href != "") {
							echo '<a target="_blank" href="'.$header_adv_href.'">';
						}
						echo '<img alt="" src="'.$header_adv_img.'">';
						if ($header_adv_href != "") {
							echo '</a>';
						}
					}
					echo '</div><!-- End advertising -->
					<div class="clearfix"></div>';
				}
			}
		}?>
		<section class="container main-content <?php echo (!is_404() && $site_users_only != "yes"?$sidebar_dir:"page-full-width");?>">
			<?php
			if (is_user_logged_in) {
				$question_publish = vpanel_options("question_publish");
				$post_publish = vpanel_options("post_publish");
			}else {
				$question_publish = vpanel_options("question_publish_unlogged");
				$post_publish = vpanel_options("post_publish_unlogged");
			}
			if ($question_publish == "draft" && !is_super_admin($user_id)) {
				vpanel_session('','vbegy_session');
			}
			
			if ($post_publish == "draft" && !is_super_admin($user_id)) {
				vpanel_session('','vbegy_session_post');
			}
			vpanel_session('','vbegy_session_e');
			vpanel_session('','vbegy_session_comment');
			vpanel_session('','vbegy_session_answer');
			vpanel_session('','vbegy_session_a');
			vpanel_session('','vbegy_session_p');
			vpanel_session('','vbegy_session_message');
			vpanel_session('','vbegy_session_user');
			vpanel_session('','vbegy_session_all');?>
			
			<div class="row">
				<div class="with-sidebar-container">
					<div class="main-sidebar-container <?php echo (!is_404() && $site_users_only != "yes"?$homepage_content_span:$full_span);?>">
					<?php if (isset($_GET['reset_password']) && $get_u != "") {
						if (!is_user_logged_in) {
							$reset_password = get_user_meta($get_u,"reset_password",true);
							if ($reset_password == esc_attr((int)$_GET['reset_password'])) {
								$reset_password 	= ask_generate_random();
								$pwdb	= md5(trim($reset_password));
								$wpdb->query("UPDATE ".$wpdb->users." SET user_pass = '".$pwdb."' WHERE ID = ".$get_u);
								$get_user_by_id = new WP_User($get_u);
								delete_user_meta($get_u,"reset_password");
								$send_text = ask_send_email(vpanel_options("email_new_password_2"),$get_u,"","",$reset_password);
								global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
								$logo_email_template = vpanel_options("logo_email_template");
								$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
								$email_title = vpanel_options("title_new_password_2");
								$email_title = ($email_title != ""?$email_title:__("Reset your password","vbegy"));
								sendEmail(vpanel_options("email_template"),get_bloginfo('name'),esc_html($get_user_by_id->user_email),esc_html($get_user_by_id->display_name),$email_title,$last_message_email);
								$_SESSION['vbegy_session_a'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Your password has been reset","vbegy").'</span><br>'.__("Check your email.","vbegy").'</p></div>';
								wp_safe_redirect(esc_url(home_url('/')));
								die();
							}else {
								wp_safe_redirect(esc_url(home_url('/')));
								die();
							}
						}else {
							$_SESSION['vbegy_session_a'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("You are already logged in","vbegy").'</span><br>'.sprintf(__("If you want to change your password go to <a href='%s'>edit profile</a>.","vbegy"),esc_url(get_page_link(vpanel_options('user_edit_profile_page')))).'</p></div>';
							wp_safe_redirect(esc_url(home_url('/')));
							die();
						}
					}
					
					if (is_user_logged_in && ($confirm_email == 1 || $user_review == 1)) {
						if ($user_review == 1 && isset($if_user_id->caps["ask_under_review"]) && $if_user_id->caps["ask_under_review"] == 1) {
							echo '<div class="alert-message error"><i class="icon-ok"></i><p><span>'.__("Your membership under review","vbegy").'</span><br>'.__("You membership us under review, When the admin approved it will send email for you.","vbegy").'</p></div>';
							get_footer();
							die();
						}
						
						if ($confirm_email == 1 && isset($if_user_id->caps["activation"]) && $if_user_id->caps["activation"] == 1) {
							$get_activate = (isset($_GET['activate'])?esc_attr($_GET['activate']):"");
							if ($get_u != "" && isset($_GET['activate'])) {
								$activation = get_user_meta($user_id,"activation",true);
								if ($activation == $get_activate) {
									$default_group = vpanel_options("default_group");
									$default_group = (isset($default_group) && $default_group != ""?$default_group:"subscriber");
									$default_group = ($user_review == 1?"ask_under_review":$default_group);
									wp_update_user( array ('ID' => $user_id, 'role' => $default_group) ) ;
									delete_user_meta($user_id,"activation");
									
									$point_new_user = vpanel_options("point_new_user");
									$active_points = vpanel_options("active_points");
									if ($point_new_user > 0 && $active_points == 1) {
										$current_user = get_user_by("id",$user_id);
										$_points = get_user_meta($user_id,$current_user->user_login."_points",true);
										$_points++;
									
										update_user_meta($user_id,$current_user->user_login."_points",$_points);
										add_user_meta($user_id,$current_user->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$point_new_user,"+","gift_site",$post_id,$id));
									
										$points_user = get_user_meta($user_id,"points",true);
										update_user_meta($user_id,"points",$points_user+$point_new_user);
										
										askme_notifications_activities($user_id,"","","","","gift_site","notifications");
									}
									
									if(!session_id()) session_start();
									if ($user_review == 1) {
										$_SESSION['vbegy_session_a'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Membership Activated","vbegy").'</span><br>'.__("Your membership is now activated, But it need a review first, When the admin approved it will send email for you.","vbegy").'</p></div>';
									}else {
										$_SESSION['vbegy_session_a'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Membership Activated","vbegy").'</span><br>'.__("Your membership is now activated.","vbegy").'</p></div>';
									}
									wp_safe_redirect(esc_url(home_url('/')));
									die();
								}else {
									echo '<div class="alert-message error"><i class="icon-ok"></i><p><span>'.sprintf(__("Kindly activate your membership","vbegy").'</span><br>'.__("A confirmation email has been sent to your registered email account. If you have not received the confirmation email, kindly <a href='%s'>Click here</a> to re-send another confirmation email.","vbegy"),esc_url(add_query_arg(array("get_activate" => "do"),esc_url(home_url('/'))))).'</p></div>';
								}
							}else if (!isset($_GET['activate']) && !isset($_SESSION['vbegy_session_a'])) {
								if (isset($_GET['get_activate']) && $_GET['get_activate'] == "do") {
									$user_data = new WP_User($user_id);
									$rand_a = rand(1,1000000000000);
									update_user_meta($user_id,"activation",$rand_a);
									$confirm_link = esc_url(add_query_arg(array("u" => $user_id,"activate" => $rand_a),esc_url(home_url('/'))));
									$send_text = ask_send_email(vpanel_options("email_confirm_link"),$user_id,"","","",$confirm_link);
									global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
									$logo_email_template = vpanel_options("logo_email_template");
									$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
									$email_title = vpanel_options("title_confirm_link");
									$email_title = ($email_title != ""?$email_title:__("Confirm account","vbegy"));
									sendEmail(vpanel_options("email_template"),get_bloginfo('name'),esc_html($user_data->user_email),esc_html($user_data->display_name),$email_title,$last_message_email);
									$_SESSION['vbegy_session_a'] = '<div class="alert-message success"><i class="icon-ok"></i><p><span>'.__("Activate the membership","vbegy").'</span><br>'.__("Check your email again.","vbegy").'</p></div>';
									wp_safe_redirect(esc_url(home_url('/')));
									die();
								}else {
									echo '<div class="alert-message error"><i class="icon-ok"></i><p><span>'.sprintf(__("Kindly activate your membership","vbegy").'</span><br>'.__("A confirmation email has been sent to your registered email account. If you have not received the confirmation email, kindly <a href='%s'>Click here</a> to re-send another confirmation email.","vbegy"),esc_url(add_query_arg(array("get_activate" => "do"),esc_url(home_url('/'))))).'</p></div>';
								}
							}
							get_footer();
							die();
						}
					}
					
					if ($site_users_only == "yes") {?>
						<div class='index-no-box index-no-box-30'></div>
						<div class="login<?php if (is_front_page() || is_home()) {
							if ($index_top_box != 1) {
								echo " index-no-box-login";
							}
						}else {
							if ($breadcrumbs != 1) {
								echo " index-no-box-login";
							}
						}?>">
							<div class="row">
								<div class="col-md-6">
									<div class="page-content">
										<h2><?php _e("Login","vbegy")?></h2>
										<div class="form-style form-style-3">
											<?php echo do_shortcode("[ask_login]");?>
										</div>
									</div><!-- End page-content -->
								</div><!-- End col-md-6 -->
								<?php if (!is_user_logged_in) {?>
									<div class="col-md-6">
										<div class="page-content">
											<h2><?php _e("Register Now","vbegy")?></h2>
											<p><?php echo stripslashes(vpanel_options("register_content"))?></p>
											<div class="button small color signup"><?php _e("Create an account","vbegy")?></div>
										</div><!-- End page-content -->
									</div><!-- End col-md-6 -->
								<?php }?>
							</div><!-- End row -->
						</div><!-- End login -->
						<?php get_footer();
						die();
					}
					
					$best_answer_done = get_option("best_answer_done");
					if ($best_answer_done != "yes") {
						$get_posts_count = array();
						$best_answer_option = get_option("best_answer_option");
						if ($best_answer_option == "") {
							$the_query = new WP_Query(array("post_type" => "question","meta_key" => "the_best_answer","nopaging" => true));
							foreach ($the_query->posts as $key => $value) {
								$get_posts_count[] = get_post_meta($value->ID,"the_best_answer",true)."<br>";
							}
							update_option("best_answer_option",count($get_posts_count));
							update_option("best_answer_done","yes");
							wp_reset_postdata();
						}
					}
					
					require_once get_template_directory() . '/functions/payment.php';