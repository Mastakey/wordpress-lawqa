<?php $all_tag_pages = ceil(count($tags)/$number);
if (!empty($terms) && !is_wp_error($terms)) {
	$term_list = '<div class="tagcloud'.($tag_style == "advanced"?" row":"").'">';
		$k_terms = 0;
		foreach ($terms as $term) {
			$k_terms++;
			if ($tag_style == "advanced") {
				$term_list .= '<div class="col-md-3">
					<div class="tag-sections">
						<div class="tag-counter">';
			}
							$term_list .= '<a href="'.esc_url(get_term_link($term)).'" title="'.esc_attr(sprintf(($tags_tax == 'post' || $tags_tax == 'post_tag'?esc_html__('View all posts under %s','vbegy'):esc_html__('View all questions under %s','vbegy')),$term->name)).'">'.$term->name.'</a>';
			if ($tag_style == "advanced") {
							$term_list .= '<span> x '.ask_count_number($term->count).'</span>
						</div>
						<div class="tag-section">';
							$today = getdate();
							$tag = $term->term_id;
							$today_query = new WP_Query(array('post_type' => $post_type_tags,'year' => $today["year"],'monthnum' => $today["mon"],'day' => $today["mday"],'tax_query' => array(array('taxonomy' => $tag_type,'field' => 'term_id','terms' => $tag))));
							$week  = date('W');
							$year  = date('Y');
							$month = date('m');
							$week_query   = new WP_Query(array('post_type' => $post_type_tags,'year' => $year,'w' => $week,'tax_query' => array(array('taxonomy' => $tag_type,'field' => 'term_id','terms' => $tag))));
							$month_query  = new WP_Query(array('post_type' => $post_type_tags,'year' => $year,'monthnum' => $month,'tax_query' => array(array('taxonomy' => $tag_type,'field' => 'term_id','terms' => $tag))));
							$term_list .= "<span>".sprintf(esc_html__('%s asked today','vbegy'),ask_count_number($today_query->found_posts))."</span>";
							$term_list .= "<span>".sprintf(esc_html__('%s this week','vbegy'),ask_count_number($week_query->found_posts))."</span>";
							$term_list .= "<span>".sprintf(esc_html__('%s this month','vbegy'),ask_count_number($month_query->found_posts))."</span>
						</div>
					</div>
				</div>";
				if ($k_terms == 4) {
					$term_list .= '<div class="col-md-12"></div>';
					$k_terms = 0;
				}
			}
		}
	$term_list .= '</div>';
	echo ($term_list);
	if ($all_tag_pages  > 1) {
		echo '<div class="pagination">'.
		    paginate_links(array(
		    	'base'      => (is_page_template("template-search.php")?add_query_arg('paged','%#%'):add_query_arg('page','%#%')),
		    	'format'    => (is_page_template("template-search.php")?'':'page/%#%/'),
		    	'current'   => max(1, $paged),
		    	'total'     => $all_tag_pages,
		    	'prev_text' => '<i class="fa fa-angle-left"></i>',
		    	'next_text' => '<i class="fa fa-angle-right"></i>',
		    )).
	    "</div>";
	}
}else {
	$no_tags = true;
}

if (isset($search_value) && $search_value != "" && isset($no_tags) && $no_tags == true) {
	include( get_template_directory() . '/includes/search-none.php' );
}?>