<?php $all_cat_pages = ceil(count($cats)/$number);
if (!empty($terms) && !is_wp_error($terms)) {
	$term_list = '<div class="user-profile-widget">
		<div class="ul_list ul_list-icon-ok">
			<ul>';
				foreach ($terms as $term) {
					$get_question_category = get_option("questions_category_".$term->term_id);
					$term_list .= '<li><i class="'.($cats_tax == 'post' || $cats_tax == 'category'?'icon-file-alt':'icon-question-sign').'"></i>'.(isset($get_question_category['private']) && $get_question_category['private'] == "on"?'<i class="icon-lock"></i>':'').'<a href="'.get_term_link($term->slug,($cats_tax == 'post' || $cats_tax == 'category'?'category':($cats_tax == 'product' || $cats_tax == 'product_cat'?'product_cat':ask_question_category))).'">'.$term->name.'<span> ( <span>'.$term->count." ";
					if ($term->count == 1) {
						if ($cats_tax == 'product' || $cats_tax == 'product_cat') {
							$term_list .= __("Product","vbegy");
						}else if ($cats_tax == 'post' || $cats_tax == 'category') {
							$term_list .= __("Post","vbegy");
						}else {
							$term_list .= __("Question","vbegy");
						}
					}else {
						if ($cats_tax == 'product' || $cats_tax == 'product_cat') {
							$term_list .= __("Products","vbegy");
						}else if ($cats_tax == 'post' || $cats_tax == 'category') {
							$term_list .= __("Posts","vbegy");
						}else {
							$term_list .= __("Questions","vbegy");
						}
					}
					$term_list .= '</span> ) </span></a></li>';
				}
			$term_list .= '</ul>
		</div>
	</div>';
	echo ($term_list);
	if ($all_cat_pages  > 1) {
		echo '<div class="pagination">'.
		    paginate_links(array(
		    	'base'      => (is_page_template("template-search.php")?add_query_arg('paged','%#%'):add_query_arg('page','%#%')),
		    	'format'    => (is_page_template("template-search.php")?'':'page/%#%/'),
		    	'current'   => max(1, $paged),
		    	'total'     => $all_cat_pages,
		    	'prev_text' => '<i class="fa fa-angle-left"></i>',
		    	'next_text' => '<i class="fa fa-angle-right"></i>',
		    )).
	    "</div>";
	}
}else {
	$no_cats = true;
}

if (isset($search_value) && $search_value != "" && isset($no_cats) && $no_cats == true) {
	include( get_template_directory() . '/includes/search-none.php' );
}?>