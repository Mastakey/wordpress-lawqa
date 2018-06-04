<?php /* Template name: User Posts */
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
	$vbegy_sidebar_all = rwmb_meta('vbegy_sidebar','radio',$post->ID);
	if ($vbegy_sidebar_all == "default") {
		$vbegy_sidebar_all = vpanel_options("sidebar_layout");
	}else {
		$vbegy_sidebar_all = $vbegy_sidebar_all;
	}
	$blog_style = vpanel_options("home_display");
	include (get_template_directory() . '/includes/author-head.php');
	$rows_per_page = get_option("posts_per_page");
	$paged = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
	$args = array('post_type' => 'post','paged' => $paged,'posts_per_page' => $rows_per_page,'author' => $user_login->ID);
	query_posts($args);
	get_template_part("loop");
	vpanel_pagination(array("base" => @esc_url(add_query_arg('page','%#%')),"format" => 'page/%#%/?u='.$get_u));
	wp_reset_query();
get_footer();?>