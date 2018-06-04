<?php if (is_page() && !is_page_template("template-search.php")) {
	$cat_sort  = rwmb_meta('vbegy_cat_sort','type=radio',$post->ID);
	$cat_order = rwmb_meta('vbegy_cat_order','type=radio',$post->ID);
	$cats_tax  = rwmb_meta('vbegy_cats_tax','type=radio',$post->ID);
	$number    = rwmb_meta('vbegy_cats_per_page','type=text',$post->ID);
	if (isset($_GET["cat_filter"]) && $_GET["cat_filter"] != "") {
		$g_cat_sort = (isset($_GET["cat_filter"]) && $_GET["cat_filter"] != ""?esc_html($_GET["cat_filter"]):$cat_sort);
		$cat_order = "DESC";
		if ($g_cat_sort == "name") {
			$cat_order = "ASC";
		}
	}
}else {
	$cat_sort  = (isset($_GET["cat_filter"]) && $_GET["cat_filter"] != ""?esc_html($_GET["cat_filter"]):"count");
	$cat_order = "DESC";
	if ($cat_sort == "name") {
		$cat_order = "ASC";
	}
	if (isset($not_get_result_page) && $not_get_result_page == true) {
		$cats_tax = $search_type;
	}else {
		$cats_tax = (isset($_GET["search_type"]) && $_GET["search_type"] != ""?esc_html($_GET["search_type"]):ask_question_category);
	}
}

if ($cats_tax == 'post' || $cats_tax == 'category') {
	$cat_type = 'category';
	$post_type_cats = 'post';
}else if ($cats_tax == 'product' || $cats_tax == 'product_cat') {
	$cat_type = 'product_cat';
	$post_type_cats = 'product';
}else {
	$cat_type = ask_question_category;
	$post_type_cats = 'question';
}

$cat_sort = (isset($_GET["cat_filter"]) && $_GET["cat_filter"] != ""?esc_html($_GET["cat_filter"]):(isset($cat_sort) && $cat_sort != ""?$cat_sort:"count"));

if (!isset($not_get_result_page)) {
	$search_value = (get_query_var('search') != ""?wp_unslash(sanitize_text_field(get_query_var('search'))):wp_unslash(sanitize_text_field(get_query_var('s'))));
}
if (isset($search_value) && $search_value != "") {
	$search_args = array('search' => $search_value);
}else {
	$search_args = array();
}

$number = (isset($number) && $number > 0?$number:apply_filters('vbegy_cats_per_page',4*get_option('posts_per_page',10)));
$paged  = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
$offset = ($paged-1)*$number;

$cats  = get_terms($cat_type,array_merge($search_args,array('hide_empty' => 0)));
$terms = get_terms($cat_type,array_merge($search_args,array(
	'orderby'    => $cat_sort,
	'order'      => $cat_order,
	'number'     => $number,
	'offset'     => $offset,
	'hide_empty' => 0
)));

if (isset($not_get_result_page) && $not_get_result_page == true) {
	if (!empty($terms) && !is_wp_error($terms)) {
		foreach ($terms as $term) {
			$k_search++;
			if ($search_result_number >= $k_search) {
				$get_question_category = get_option("questions_category_".$term->term_id);
				echo '<li><a href="'.get_term_link($term->slug,($cats_tax == 'post' || $cats_tax == 'category'?'category':($cats_tax == 'product' || $cats_tax == 'product_cat'?'product_cat':ask_question_category))).'">'.str_ireplace($search_value,"<strong>".$search_value."</strong>",$term->name).'</a></li>';
			}else {
				echo "<li><a href='".esc_url(add_query_arg(array("search" => $search_value,"search_type" => $search_type),(isset($search_page) && $search_page != ""?get_page_link($search_page):"")))."'>".__("View all results.","vbegy")."</a></li>";
				exit;
			}
		}
	}else {
		echo "<li class='no-search-result'>".__("No results found.","vbegy")."</li>";
	}
}else {
	include( get_template_directory() . '/includes/categories-results.php' );
}?>