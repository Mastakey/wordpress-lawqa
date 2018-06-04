<?php $search_attrs = vpanel_options("search_attrs");
if (isset($search_attrs) && is_array($search_attrs) && !empty($search_attrs)) {
	$i_count = -1;
	while ($i_count < count($search_attrs)) {
		$array_values = array_values($search_attrs);
		if (!empty($array_values) && isset($array_values[$i_count]) && $array_values[$i_count] == 1) {
			$first_one_search = $i_count;
			break;
		}
		$i_count++;
	}
	if (isset($first_one_search)) {
		$array_keys = array_keys($search_attrs);
		$first_one_search = $array_keys[$first_one_search];
	}
}
$user_filter   = vpanel_options('user_filter');
$active_points = vpanel_options("active_points");
$search_page   = vpanel_options('search_page');
$live_search   = vpanel_options('live_search');
if (!isset($hide_form)) {?>
	<div class="post-search">
		<form role="search" method="get" class="searchform" action="<?php echo esc_url((isset($search_page) && $search_page != ""?get_page_link($search_page):""))?>">
			<input type="hidden" name="page_id" value="<?php echo esc_attr($search_page)?>">
			<div class="row">
				<div class="main-search-div <?php echo (isset($search_type) && $search_type == "users" && $user_filter == 1 && isset($search_attrs["users"]) && $search_attrs["users"] == 1 && isset($first_one_search)?"col-md-4":(isset($search_attrs) && is_array($search_attrs) && !empty($search_attrs) && isset($first_one_search)?"col-md-6":"col-md-12"))?>">
					<div class="search-wrapper">
						<input<?php echo ($live_search == 1?" class='live-search' autocomplete='off'":"")?> type="search" name="search" value="<?php if ($search_value != "") {echo wp_unslash(sanitize_text_field($search_value));}else {esc_html_e("Hit enter to search","vbegy");}?>" onfocus="if(this.value=='<?php esc_html_e("Hit enter to search","vbegy")?>')this.value='';" onblur="if(this.value=='')this.value='<?php esc_html_e("Hit enter to search","vbegy")?>';">
						<?php if ($live_search == 1) {?>
							<div class="loader_2 search_loader"></div>
							<div class="search-results results-empty"></div>
						<?php }?>
					</div>
				</div>
				<?php if (isset($search_attrs) && is_array($search_attrs) && !empty($search_attrs) && isset($first_one_search)) {?>
					<div class="search-type-div <?php echo (isset($search_type) && $search_type == "users" && $user_filter == 1 && isset($search_attrs["users"]) && $search_attrs["users"] == 1?"col-md-4":"col-md-6")?>">
						<span class="styled-select">
							<select name="search_type" class="search_type">
								<option value="-1"><?php esc_html_e("Select kind of search","vbegy")?></option>
								<?php foreach ($search_attrs as $key => $value) {
									if ($key == "questions" && isset($search_attrs["questions"]) && $search_attrs["questions"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"questions").selected((empty($search_type) && $search_value != ""?"questions":""),"questions")?> value="questions"><?php esc_html_e("Questions","vbegy")?></option>
									<?php }else if ($key == "answers" && isset($search_attrs["answers"]) && $search_attrs["answers"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"answers")?> value="answers"><?php esc_html_e("Answers","vbegy")?></option>
									<?php }else if ($key == ask_question_category && isset($search_attrs[ask_question_category]) && $search_attrs[ask_question_category] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),ask_question_category)?> value="<?php echo ask_question_category?>"><?php esc_html_e("Question categories","vbegy")?></option>
									<?php }else if ($key == "question_tags" && isset($search_attrs["question_tags"]) && $search_attrs["question_tags"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"question_tags")?> value="question_tags"><?php esc_html_e("Question tags","vbegy")?></option>
									<?php }else if ($key == "posts" && isset($search_attrs["posts"]) && $search_attrs["posts"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"posts")?> value="posts"><?php esc_html_e("Posts","vbegy")?></option>
									<?php }else if ($key == "comments" && isset($search_attrs["comments"]) && $search_attrs["comments"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"comments")?> value="comments"><?php esc_html_e("Comments","vbegy")?></option>
									<?php }else if ($key == "category" && isset($search_attrs["category"]) && $search_attrs["category"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"category")?> value="category"><?php esc_html_e("Post categories","vbegy")?></option>
									<?php }else if ($key == "post_tag" && isset($search_attrs["post_tag"]) && $search_attrs["post_tag"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"post_tag")?> value="post_tag"><?php esc_html_e("Post tags","vbegy")?></option>
									<?php }else if ($key == "products" && isset($search_attrs["products"]) && $search_attrs["products"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"products")?> value="products"><?php esc_html_e("Products","vbegy")?></option>
									<?php }else if ($key == "product_cat" && isset($search_attrs["product_cat"]) && $search_attrs["product_cat"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"product_cat")?> value="product_cat"><?php esc_html_e("Product categories","vbegy")?></option>
									<?php }else if ($key == "product_tag" && isset($search_attrs["product_tag"]) && $search_attrs["product_tag"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"product_tag")?> value="product_tag"><?php esc_html_e("Product tags","vbegy")?></option>
									<?php }else if ($key == "users" && isset($search_attrs["users"]) && $search_attrs["users"] == 1) {?>
										<option <?php selected((isset($search_type) && $search_type != ""?$search_type:""),"users")?> value="users"><?php esc_html_e("Users","vbegy")?></option>
									<?php }
								}?>
							</select>
						</span>
					</div>
				<?php }
				if ($user_filter == 1 && isset($first_one_search) && isset($search_attrs["users"]) && $search_attrs["users"] == 1) {
					$user_sort = (isset($_GET["user_filter"]) && $_GET["user_filter"] != ""?esc_html($_GET["user_filter"]):"user_registered");
					echo '<div class="col-md-4 user-filter-div'.(isset($search_type) && $search_type == "users"?" user-filter-show":" ask-hide").'">
						<span class="styled-select user-filter">
							<select'.(isset($search_type) && $search_type == "users"?' name="user_filter"':'').'>
								<option value="user_registered" '.selected((isset($user_sort) && $user_sort != ""?$user_sort:""),"user_registered",false).'>'.esc_html__('Register','vbegy').'</option>
								<option value="display_name" '.selected((isset($user_sort) && $user_sort != ""?$user_sort:""),"display_name",false).'>'.esc_html__('Name','vbegy').'</option>
								<option value="ID" '.selected((isset($user_sort) && $user_sort != ""?$user_sort:""),"ID",false).'>'.esc_html__('ID','vbegy').'</option>
								<option value="question_count" '.selected((isset($user_sort) && $user_sort != ""?$user_sort:""),"question_count",false).'>'.esc_html__('Questions','vbegy').'</option>
								<option value="answers" '.selected((isset($user_sort) && $user_sort != ""?$user_sort:""),"answers",false).'>'.esc_html__('Answers','vbegy').'</option>
								<option value="the_best_answer" '.selected((isset($user_sort) && $user_sort != ""?$user_sort:""),"the_best_answer",false).'>'.esc_html__('Best Answers','vbegy').'</option>';
								if ($active_points == 1) {
									echo '<option value="points" '.selected((isset($user_sort) && $user_sort != ""?$user_sort:""),"points",false).'>'.esc_html__('Points','vbegy').'</option>';
								}
								echo '<option value="post_count" '.selected((isset($user_sort) && $user_sort != ""?$user_sort:""),"post_count",false).'>'.esc_html__('Posts','vbegy').'</option>
								<option value="comments" '.selected((isset($user_sort) && $user_sort != ""?$user_sort:""),"comments",false).'>'.esc_html__('Comments','vbegy').'</option>
							</select>
						</span>
					</div>';
				}?>
			</div>
			<div class="vbegy_form">
				<input type="submit" class="button-default" value="<?php esc_html_e('Search','vbegy')?>">
			</div>
		</form>
	</div>
<?php }?>