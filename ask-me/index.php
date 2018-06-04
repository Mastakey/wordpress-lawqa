<?php get_header();
	if (isset($_POST)) {
		$show_on_front = get_option("show_on_front");
		if ($show_on_front == "page") {
			$page_on_front = get_option("page_on_front");
			if (is_numeric($page_on_front)) {
				$wp_page_template = get_post_meta($page_on_front,'_wp_page_template',true);
				$the_page_id = $page_on_front;
				if ($wp_page_template == "template-home.php") {
					$is_home_template = true;
				}
			}
		}
	}
	if (isset($is_home_template)) {
		include( get_template_directory() . '/includes/home.php' );
	}else {
		$vbegy_sidebar_all = vpanel_options("sidebar_layout");
		$blog_style = vpanel_options("home_display");
		get_template_part("loop","index");
		vpanel_pagination();
	}
get_footer();?>