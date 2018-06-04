<?php if (is_page() && !is_page_template("template-search.php")) {
	$tag_sort  = rwmb_meta('vbegy_tag_sort','type=radio',$post->ID);
	$tag_style = rwmb_meta('vbegy_tag_style','type=radio',$post->ID);
	$tag_order = rwmb_meta('vbegy_tag_order','type=radio',$post->ID);
	$tags_tax  = rwmb_meta('vbegy_tags_tax','type=radio',$post->ID);
	$number    = rwmb_meta('vbegy_tags_per_page','type=text',$post->ID);
	if (isset($_GET["tag_filter"]) && $_GET["tag_filter"] != "") {
		$g_tag_sort = (isset($_GET["tag_filter"]) && $_GET["tag_filter"] != ""?esc_html($_GET["tag_filter"]):$tag_sort);
		$tag_order = "DESC";
		if ($g_tag_sort == "name") {
			$tag_order = "ASC";
		}
	}
}else {
	$tag_style = vpanel_options("tag_style_pages");
	$tag_sort  = (isset($_GET["tag_filter"]) && $_GET["tag_filter"] != ""?esc_html($_GET["tag_filter"]):"count");
	$tag_order = "DESC";
	if ($tag_sort == "name") {
		$tag_order = "ASC";
	}
	if (isset($not_get_result_page) && $not_get_result_page == true) {
		$tags_tax = $search_type;
	}else {
		$tags_tax = (isset($_GET["search_type"]) && $_GET["search_type"] != ""?esc_html($_GET["search_type"]):"question_tags");
	}
}

if ($tags_tax == 'post' || $tags_tax == 'post_tag') {
	$tag_type = 'post_tag';
	$post_type_tags = 'post';
}else if ($tags_tax == 'product' || $tags_tax == 'product_tag') {
	$tag_type = 'product_tag';
	$post_type_tags = 'product';
}else {
	$tag_type = 'question_tags';
	$post_type_tags = 'question';
}

$tag_sort = (isset($_GET["tag_filter"]) && $_GET["tag_filter"] != ""?esc_html($_GET["tag_filter"]):(isset($tag_sort) && $tag_sort != ""?$tag_sort:"count"));

if (!isset($not_get_result_page)) {
	$search_value = (get_query_var('search') != ""?wp_unslash(sanitize_text_field(get_query_var('search'))):wp_unslash(sanitize_text_field(get_query_var('s'))));
}
if (isset($search_value) && $search_value != "") {
	$search_args = array('search' => $search_value);
}else {
	$search_args = array();
}

$number = (isset($number) && $number > 0?$number:apply_filters('vbegy_tags_per_page',4*get_option('posts_per_page',10)));
$paged  = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
$offset = ($paged-1)*$number;

$tags  = get_terms($tag_type,array_merge($search_args,array('hide_empty' => 0)));
$terms = get_terms($tag_type,array_merge($search_args,array(
	'orderby'    => $tag_sort,
	'order'      => $tag_order,
	'number'     => $number,
	'offset'     => $offset,
	'hide_empty' => 0
)));

if (isset($not_get_result_page) && $not_get_result_page == true) {
	if (!empty($terms) && !is_wp_error($terms)) {
		foreach ($terms as $term) {
			$k_search++;
			if ($search_result_number >= $k_search) {
				echo '<li><a href="'.esc_url(get_term_link($term)).'" title="'.esc_attr(sprintf(($tags_tax == 'post' || $tags_tax == 'post_tag'?esc_html__('View all posts under %s','vbegy'):($tags_tax == 'product' || $tags_tax == 'product_tag'?esc_html__('View all products under %s','vbegy'):esc_html__('View all questions under %s','vbegy'))),$term->name)).'">'.str_ireplace($search_value,"<strong>".$search_value."</strong>",$term->name).'</a></li>';
			}else {
				echo "<li><a href='".esc_url(add_query_arg(array("search" => $search_value,"search_type" => $search_type),(isset($search_page) && $search_page != ""?get_page_link($search_page):"")))."'>".__("View all results.","vbegy")."</a></li>";
				exit;
			}
		}
	}else {
		echo "<li class='no-search-result'>".__("No results found.","vbegy")."</li>";
	}
}else {
	include( get_template_directory() . '/includes/tags-results.php' );
}?>