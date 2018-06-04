<?php if (is_page() && !is_page_template("template-search.php")) {
	$user_group = rwmb_meta('vbegy_user_group','type=checkbox_list',$post->ID);
	$user_sort  = rwmb_meta('vbegy_user_sort','type=select',$post->ID);
	$user_order = rwmb_meta('vbegy_user_order','type=radio',$post->ID);
	$number     = rwmb_meta('vbegy_users_per_page','type=text',$post->ID);
	$number     = (isset($number) && $number > 0?$number:apply_filters('vbegy_users_per_page',get_option('posts_per_page')));
}else {
	$user_sort  = (isset($_GET["user_filter"]) && $_GET["user_filter"] != ""?esc_html($_GET["user_filter"]):(isset($user_sort)?$user_sort:""));
	$user_order = "DESC";
	$number     = vpanel_options("users_per_page");
	$number     = (isset($number) && $number > 0?$number:apply_filters('users_per_page',get_option('posts_per_page')));
}

$active_points  = vpanel_options("active_points");
$paged          = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
$offset         = ($paged -1) * $number;

$meta_key_array = array();
$implode_array  = "";
$capabilities   = $wpdb->get_blog_prefix(1) . 'capabilities';

if (!empty($user_group)) {
	foreach ($user_group as $role => $name) {
		$all_role_array[] = $name;
		$meta_key_array[] = "( $wpdb->usermeta.meta_key = '$capabilities' AND $wpdb->usermeta.meta_value RLIKE '$name' )";
	}
	$implode_array = "AND (".implode(" OR ",$meta_key_array).")";
}

$user_sort = (isset($_GET["user_filter"]) && $_GET["user_filter"] != ""?esc_html($_GET["user_filter"]):(isset($user_sort) && $user_sort != ""?$user_sort:"user_registered"));

if (!isset($not_get_result_page)) {
	$search_value = (get_query_var('search') != ""?wp_unslash(sanitize_text_field(get_query_var('search'))):wp_unslash(sanitize_text_field(get_query_var('s'))));
}
$name_array = preg_split("/[\s,]+/", $search_value);
if (isset($search_value) && $search_value != "") {
	$search_args = " AND ( $wpdb->users.user_login RLIKE '$search_value' OR $wpdb->users.user_nicename RLIKE '$search_value') OR ( ( $wpdb->usermeta.meta_key = 'user_login' AND $wpdb->usermeta.meta_value RLIKE '$search_value' ) OR ( $wpdb->usermeta.meta_key = 'display_name' AND $wpdb->usermeta.meta_value RLIKE '$search_value' ) OR ( $wpdb->usermeta.meta_key = 'user_nicename' AND $wpdb->usermeta.meta_value RLIKE '$search_value' ) OR ( $wpdb->usermeta.meta_key = 'first_name' AND $wpdb->usermeta.meta_value RLIKE '".(isset($name_array[0]) && $name_array[0] != ""?$name_array[0]:$search_value)."' ) OR ( $wpdb->usermeta.meta_key = 'last_name' AND $wpdb->usermeta.meta_value RLIKE '".(isset($name_array[0]) && $name_array[0] != ""?$name_array[0]:$search_value)."' ) ) ";
	$implode_array = " ";
}else {
	$search_args = " ";
}

if ($user_sort == "post_count" || $user_sort == "question_count" || $user_sort == "answers" || $user_sort == "comments") {
	if ($user_sort == "post_count" || $user_sort == "question_count") {
		$query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.ID FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ( $wpdb->users.ID = $wpdb->usermeta.user_id ) LEFT OUTER JOIN ( SELECT post_author, COUNT(*) as post_count FROM $wpdb->posts WHERE ( ( post_type = '".($user_sort == "question_count"?"question":"post")."' AND ( post_status = 'publish' OR post_status = 'private' ) ) ) GROUP BY post_author ) p ON ($wpdb->users.ID = p.post_author) WHERE %s=1".$search_args.$implode_array,1);
	}else {
		$query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.ID FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ( $wpdb->users.ID = $wpdb->usermeta.user_id ) LEFT OUTER JOIN ( SELECT user_id, COUNT(*) as total FROM $wpdb->comments INNER JOIN $wpdb->posts ON ( $wpdb->comments.comment_post_ID = $wpdb->posts.ID ) WHERE $wpdb->posts.post_type = '".($user_sort == "answers"?"question":"post")."' AND ( $wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private' ) GROUP BY user_id ) c ON ($wpdb->users.ID = c.user_id) WHERE %s=1".$search_args.$implode_array,1);
	}
	
	$users = $wpdb->get_results($query);
	$total_users = $wpdb->num_rows;
	$total_pages = ceil($total_users/$number);
	
	if ($user_sort == "post_count" || $user_sort == "question_count") {
		$query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.ID FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ( $wpdb->users.ID = $wpdb->usermeta.user_id ) LEFT OUTER JOIN ( SELECT post_author, COUNT(*) as post_count FROM $wpdb->posts WHERE ( ( post_type = '".($user_sort == "question_count"?"question":"post")."' AND ( post_status = 'publish' OR post_status = 'private' ) ) ) GROUP BY post_author ) p ON ($wpdb->users.ID = p.post_author) WHERE %s=1".$search_args.$implode_array." ORDER BY post_count $user_order limit $offset,$number",1);
	}else {
		$query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.ID FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ( $wpdb->users.ID = $wpdb->usermeta.user_id ) LEFT OUTER JOIN ( SELECT user_id, COUNT(*) as total FROM $wpdb->comments INNER JOIN $wpdb->posts ON ( $wpdb->comments.comment_post_ID = $wpdb->posts.ID ) WHERE $wpdb->posts.post_type = '".($user_sort == "answers"?"question":"post")."' AND ( $wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'private' ) GROUP BY user_id ) c ON ($wpdb->users.ID = c.user_id) WHERE %s=1".$search_args.$implode_array." ORDER BY total $user_order limit $offset,$number",1);
	}
	$query = $wpdb->get_results($query);
}else if (($user_sort == "points" && $active_points == 1) || $user_sort == "the_best_answer") {
	function ask_cmps($a, $b) {
		global $user_order;
		if ($a->meta_value == $b->meta_value) {
			return 0;
		}
		if ($user_order == "ASC") {
			return ($a->meta_value < $b->meta_value) ? -1 : 1;
		}else {
			return ($a->meta_value > $b->meta_value) ? -1 : 1;
		}
	}

	$query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.ID,$wpdb->usermeta.meta_key,$wpdb->usermeta.meta_value FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) WHERE %s=1 AND ( ( $wpdb->usermeta.meta_key = '$user_sort' AND CAST($wpdb->usermeta.meta_value AS CHAR) >= '0' ) )".$search_args,1);
	$users = $wpdb->get_results($query);
	if (isset($users) && is_array($users) && !empty($users)) {
		usort($users, 'ask_cmps');
		foreach ($users as $key => $value) {
			$get_capabilities = get_user_meta($value->ID,$capabilities,true);
			if (empty($user_group) || (is_array($user_group) && is_array($get_capabilities) && in_array(key($get_capabilities),$user_group))) {
				$users_ids[] = $value->ID;
			}else {
				if (!empty($user_group)) {
					unset($users[$key]);
				}
			}
		}
	}else {
		$no_user = true;
	}
}else {
	if ($user_sort != "user_registered" && $user_sort != "display_name" && $user_sort != "ID") {
		$user_sort = "user_registered";
	}
	$query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.ID FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) WHERE %s=1".$search_args.$implode_array,1);
	$users = $wpdb->get_results($query);
	
	$total_users = $wpdb->num_rows;
	$total_pages = ceil($total_users/$number);
	$query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.ID FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) WHERE %s=1".$search_args.$implode_array." ORDER BY ".$user_sort." $user_order limit $offset,$number",1);
	$query = $wpdb->get_results($query);
}

if (isset($not_get_result_page) && $not_get_result_page == true) {
	if (($user_sort == "points" && $active_points == 1) || $user_sort == "the_best_answer") {
		if (isset($query) && !empty($query) && isset($users_ids) && is_array($users_ids)) {
			
		}else {
			echo "<li class='no-search-result'>".__("No results found.","vbegy")."</li>";
		}
	}else {
		$total_query = $wpdb->num_rows;
		if (isset($query) && !empty($query)) {
			foreach ($query as $user) {
				$k_search++;
				if ($search_result_number >= $k_search) {
					$you_avatar = get_the_author_meta('you_avatar',$user->ID);
					$display_name = get_the_author_meta('display_name',$user->ID);
					echo '<li>
						<a class="get-results" href="'.vpanel_get_user_url($user->ID).'" title="'.$display_name.'">
							'.askme_user_avatar($you_avatar,20,20,$user->ID,$display_name).'
						</a>
						<a href="'.vpanel_get_user_url($user->ID).'" title="'.$display_name.'">'.str_ireplace($search_value,"<strong>".$search_value."</strong>",$display_name).'</a>
					</li>';
				}else {
					echo "<li><a href='".esc_url(add_query_arg(array("search" => $search_value,"search_type" => $search_type),(isset($search_page) && $search_page != ""?get_page_link($search_page):"")))."'>".__("View all results.","vbegy")."</a></li>";
					exit;
				}
			}
		}else {
			echo "<li class='no-search-result'>".__("No results found.","vbegy")."</li>";
		}
	}
}else {
	include( get_template_directory() . '/includes/users-results.php' );
}?>