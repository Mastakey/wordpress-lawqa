<?php /* Template Name: Search */
get_header();?>
	<div class="page-content page-content-search">
		<?php 
		$search_value = (get_query_var('search') != ""?wp_unslash(sanitize_text_field(get_query_var('search'))):wp_unslash(sanitize_text_field(get_query_var('s'))));
		$search_type  = (isset($_GET["search_type"]) && $_GET["search_type"] != ""?esc_attr($_GET["search_type"]):"");
		if ( have_posts() ) : while ( have_posts() ) : the_post();?>
			<div class="boxedtitle page-title">
				<h2>
					<?php if ($search_value != "") {
						echo __("Search results for","vbegy")." ".$search_value;
					}else {
						_e("Search","vbegy");
					}?>
				</h2>
			</div>
			<?php the_content();
		endwhile; endif;
		include( get_template_directory() . '/includes/search.php' );
	if ($search_type == "-1" || $search_type == "questions" || $search_type == "products" || $search_type == "posts" || $search_type == "users" || $search_type == "comments" || $search_type == "answers") {?>
		<div class="clearfix"></div>
		</div><!-- End page-content -->
	<?php }
		$hide_form = true;
		include( get_template_directory() . '/includes/content-search.php' );
	if ($search_type != "-1" && $search_type != "questions" && $search_type != "products" && $search_type != "posts" && $search_type != "users" && $search_type != "comments" && $search_type != "answers") {?>
		<div class="clearfix"></div>
		</div><!-- End page-content -->
	<?php }
get_footer();?>