<?php if (($user_sort == "points" && $active_points == 1) || $user_sort == "the_best_answer") {
	if (isset($query) && !empty($query) && isset($users_ids) && is_array($users_ids)) {
		$current = max(1,$paged);
		$pagination_args = array(
			'format'    => 'page/%#%/',
			'total'     => ceil(sizeof($users_ids)/$number),
			'current'   => $current,
			'show_all'  => false,
			'prev_text' => '<i class="fa fa-angle-left"></i>',
			'next_text' => '<i class="fa fa-angle-right"></i>',
		);
			
		$start = ($current - 1) * $number;
		$end = $start + $number;
		$end = (sizeof($users_ids) < $end) ? sizeof($users_ids) : $end;
		for ($i=$start;$i < $end ;++$i ) {
			$user = $users_ids[$i];
			echo ask_author($user,$user_sort);
		}
	}
}else {
	$total_query = $wpdb->num_rows;
	if (isset($query) && !empty($query)) {
		foreach ($query as $user) {
			echo ask_author($user->ID,$user_sort);
		}
	}else {
		$no_user = true;
	}
}

if (($user_sort == "points" && $active_points == 1) || $user_sort == "the_best_answer") {
	if (isset($users_ids) &&is_array($users_ids) && $pagination_args["total"] > 1) {?>
		<div class='pagination'><?php echo (paginate_links($pagination_args) != ""?paginate_links($pagination_args):"")?></div>
	<?php }
}else {
	if (isset($total_users) && $total_users > $total_query) {
		$current_page = max(1,$paged);
		echo '<div class="pagination">'
			.paginate_links(array(
				'base' => (is_page_template("template-search.php")?add_query_arg('paged','%#%'):add_query_arg('page','%#%')),
				'format'    => (is_page_template("template-search.php")?'':'page/%#%/'),
				'current' => $current_page,
				'total' => $total_pages,
				'prev_text' => '<i class="fa fa-angle-left"></i>',
				'next_text' => '<i class="fa fa-angle-right"></i>',
			)).
		'</div><div class="clearfix"></div>';
	}
}

if (isset($no_user) && $no_user == true) {
	include( get_template_directory() . '/includes/search-none.php' );
}