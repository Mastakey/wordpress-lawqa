<?php
$search_type     = (isset($_GET["search_type"]) && $_GET["search_type"] != ""?esc_attr($_GET["search_type"]):"");
$post_pagination = 'pagination';
$search_attrs    = vpanel_options("search_attrs");

if ( have_posts() ) :
	include( get_template_directory() . '/includes/search.php' );
endif;

if (is_page_template("template-search.php")) {
	$template_search = true;
}

if (($search_type == "answers" && isset($search_attrs["answers"]) && $search_attrs["answers"] == 1) || ($search_type == "comments" && isset($search_attrs["comments"]) && $search_attrs["comments"] == 1)) {
	include( get_template_directory() . '/includes/comments.php' );
}else if ($search_type == "users" && isset($search_attrs["users"]) && $search_attrs["users"] == 1) {
	include( get_template_directory() . '/includes/users.php' );
}else if (($search_type == ask_question_category && isset($search_attrs[ask_question_category]) && $search_attrs[ask_question_category] == 1) || ($search_type == "product_cat" && isset($search_attrs["product_cat"]) && $search_attrs["product_cat"] == 1) || ($search_type == "category" && isset($search_attrs["category"]) && $search_attrs["category"] == 1)) {
	include( get_template_directory() . '/includes/categories.php' );
}else if (($search_type == "question_tags" && isset($search_attrs["question_tags"]) && $search_attrs["question_tags"] == 1) || ($search_type == "product_tag" && isset($search_attrs["product_tag"]) && $search_attrs["product_tag"] == 1) || ($search_type == "post_tag" && isset($search_attrs["post_tag"]) && $search_attrs["post_tag"] == 1)) {
	include( get_template_directory() . '/includes/tags.php' );
}else {
	if ($search_value != "") {
		if ($search_type == "products" && isset($search_attrs["products"]) && $search_attrs["products"] == 1) {
			$post_type_array = array('product');
		}else if ($search_type == "posts" && isset($search_attrs["posts"]) && $search_attrs["posts"] == 1) {
			$post_type_array = array('post');
		}else {
			$post_type_array = array('question');
		}
		
		query_posts(array('s' => $search_value,'paged' => $paged,'post_type' => $post_type_array,"meta_query" => array(array("key" => "user_id","compare" => "NOT EXISTS"))));
		if ($search_type == "posts") {
			$blog_style = vpanel_options("home_display");
		}
		if ($search_type == "products") {
			do_action('woocommerce_archive_description');
			if (have_posts()) : ?>
				<div class="woocommerce-page">
				<ul class = "products woocommerce_products products_grid clearfix">
					<?php while (have_posts()) : the_post();
						woocommerce_get_template_part('content','product');
					endwhile;?>
				</ul>
				</div>
				<?php do_action('woocommerce_after_shop_loop');
			elseif (!woocommerce_product_subcategories(array('before' => woocommerce_product_loop_start(false), 'after' => woocommerce_product_loop_end(false)))) :
				woocommerce_get_template('loop/no-products-found.php');
			endif;
		}else {
			get_template_part("loop".($search_type == "posts"?"":"-question"));
			vpanel_pagination();
			wp_reset_query();
		}
	}
}?>