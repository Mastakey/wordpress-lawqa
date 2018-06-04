<?php
$prefix = 'vbegy_';
add_action( 'admin_init', 'vbegy_register_meta_boxes' );
function vbegy_register_meta_boxes() {
	global $prefix;
	if ( ! class_exists( 'RW_Meta_Box' ) )
		return;
	
	$options_categories = array();
	$options_categories_obj = get_categories();
	foreach ($options_categories_obj as $category) {
		$options_categories[$category->cat_ID] = $category->cat_name;
	}
	
	$options_question_categories = array();
	$options_question_categories_obj = get_categories();
	foreach ($options_question_categories_obj as $category) {
		$options_question_categories[$category->cat_ID] = $category->cat_name;
	}
	
	$sidebars = get_option('sidebars');
	$new_sidebars = array('default'=> 'Default');
	foreach ($GLOBALS['wp_registered_sidebars'] as $sidebar) {
		$new_sidebars[$sidebar['id']] = $sidebar['name'];
	}
	
	// Menus
    $menus = array();
    $all_menus = get_terms('nav_menu',array('hide_empty' => true));
	foreach ($all_menus as $menu) {
	    $menus[$menu->term_id] = $menu->name;
	}
	
	// Pull all the groups into an array
	$options_groups = array();
	global $wp_roles;
	$options_groups_obj = $wp_roles->roles;
	foreach ($options_groups_obj as $key_r => $value_r) {
		$options_groups[$key_r] = $value_r['name'];
	}
	
	$meta_boxes = array();
	$post_types = get_post_types();

	$meta_boxes[] = array(
		'id' => 'template_comments',
		'title' => 'Comments Options',
		'pages' => array('page'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name' => esc_html__("Comment type",'vbegy'),
				'desc' => esc_html__("Select the comment type.",'vbegy'),
				'id' => $prefix."comment_type",
				'std' => "comments",
				'type' => "radio",
				'options' => array(
					'comments' => 'Comments',
					'answers' => 'Answers',
				)
			),
			array(
				'name'    => esc_html__('Order by','vbegy'),
				'desc'    => esc_html__('Select the comments order by.','vbegy'),
				'id'      => $prefix."orderby_answers_a",
				'std'     => "date",
				'type'    => "radio",
				'options' => array(
					'date'  => 'Date',
					'votes' => 'Voted - Work at answers only.',
				)
			),
			array(
				'name'    => esc_html__('Order','vbegy'),
				'id'      => $prefix.'order_answers',
				'std'     => "DESC",
				'type'    => 'radio',
				'options' => array(
					'DESC'  => 'Descending',
					'ASC'   => 'Ascending',
				),
			),
			array(
				'name' => esc_html__('Comments per page','vbegy'),
				'desc' => esc_html__('put the comments per page','vbegy'),
				'id'   => $prefix.'answers_number',
				'type' => 'text',
				'std'  => "10"
			),
		),
	);
	
	$meta_boxes[] = array(
		'id' => 'blog',
		'title' => 'Blog Options',
		'pages' => array('page'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name' => esc_html__("Post number",'vbegy'),
				'desc' => esc_html__("put the post number",'vbegy'),
				'id' => $prefix.'post_number_b',
				'type' => 'text',
				'std' => "5"
			),
			array(
				'name' => esc_html__("Excerpt post",'vbegy'),
				'desc' => esc_html__("Put here the excerpt post",'vbegy'),
				'id' => $prefix.'post_excerpt_b',
				'type' => 'text',
				'std' => "5"
			),
			array(
				'name' => esc_html__("Order by",'vbegy'),
				'desc' => esc_html__("Select the post order by.",'vbegy'),
				'id' => $prefix."orderby_post_b",
				'std' => array("recent"),
				'type' => "select",
				'options' => array(
					'recent' => 'Recent',
					'popular' => 'Popular',
					'random' => 'Random',
				)
			),
			array(
				'name'		=> esc_html__("Display by",'vbegy'),
				'id'		=> $prefix."post_display_b",
				'type'		=> 'select',
				'options'	=> array(
					'lasts'	=> 'Lasts',
					'single_category' => 'Single category',
					'multiple_categories' => 'Multiple categories',
					'posts'	=> 'Custom posts',
				),
				'std'		=> array('lasts'),
			),
			array(
				'name'		=> esc_html__('Single category','vbegy'),
				'id'		=> $prefix.'post_single_category_b',
				'type'		=> 'select',
				'options'	=> $options_categories,
			),
			array(
				'name' => esc_html__("Post categories",'vbegy'),
				'desc' => esc_html__("Select the post categories.",'vbegy'),
				'id' => $prefix."post_categories_b",
				'options' => $options_categories,
				'type' => 'checkbox_list'
			),
			array(
				'name'     => esc_html__("Post ids",'vbegy'),
				'desc'     => esc_html__("Type the post ids.",'vbegy'),
				'id'       => $prefix."post_posts_b",
				'std'      => '',
				'type'     => 'text',
			),
		),
	);
	
	$meta_boxes[] = array(
		'id' => 'contact_us',
		'title' => 'Contact us Options',
		'pages' => array('page'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name' => esc_html__('Map','vbegy'),
				'desc' => esc_html__('Put the code iframe map.','vbegy'),
				'id'   => $prefix.'contact_map',
				'std'  => '<iframe height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=egypt&amp;hl=en&amp;sll=26.820553,30.802498&amp;sspn=16.874794,19.753418&amp;hnear=Egypt&amp;t=m&amp;z=6&amp;output=embed"></iframe>',
				'type' => 'textarea'
			),
			array(
				'name' => esc_html__('Form shortcode','vbegy'),
				'desc' => esc_html__('Put the form shortcode.','vbegy'),
				'id'   => $prefix.'contact_form',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('About widget enable or disable','vbegy'),
				'desc' => esc_html__('About widget enable or disable.','vbegy'),
				'id'   => $prefix.'about_widget',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name' => esc_html__('About content','vbegy'),
				'desc' => esc_html__('Put the about content.','vbegy'),
				'id'   => $prefix.'about_content',
				'type' => 'textarea'
			),
			array(
				'name' => esc_html__('Address','vbegy'),
				'desc' => esc_html__('Put the address.','vbegy'),
				'id'   => $prefix.'address',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Phone','vbegy'),
				'desc' => esc_html__('Put the phone.','vbegy'),
				'id'   => $prefix.'phone',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Email','vbegy'),
				'desc' => esc_html__('Put the email.','vbegy'),
				'id'   => $prefix.'email',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Social enable or disable','vbegy'),
				'desc' => esc_html__('Social widget enable or disable.','vbegy'),
				'id'   => $prefix.'social',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name' => esc_html__('Facebook','vbegy'),
				'desc' => esc_html__('Put the facebook.','vbegy'),
				'id'   => $prefix.'facebook',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Twitter','vbegy'),
				'desc' => esc_html__('Put the twitter.','vbegy'),
				'id'   => $prefix.'twitter',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Youtube','vbegy'),
				'desc' => esc_html__('Put the youtube.','vbegy'),
				'id'   => $prefix.'youtube',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Linkedin','vbegy'),
				'desc' => esc_html__('Put the linkedin.','vbegy'),
				'id'   => $prefix.'linkedin',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Google plus','vbegy'),
				'desc' => esc_html__('Put the google plus.','vbegy'),
				'id'   => $prefix.'google_plus',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Instagram','vbegy'),
				'desc' => esc_html__('Put the instagram.','vbegy'),
				'id'   => $prefix.'instagram',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Dribbble','vbegy'),
				'desc' => esc_html__('Put the dribbble.','vbegy'),
				'id'   => $prefix.'dribbble',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Pinterest','vbegy'),
				'desc' => esc_html__('Put the pinterest.','vbegy'),
				'id'   => $prefix.'pinterest',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Rss enable or disable','vbegy'),
				'desc' => esc_html__('Rss widget enable or disable.','vbegy'),
				'id'   => $prefix.'rss',
				'std'  => 1,
				'type' => 'checkbox'
			),
		),
	);
	
	$meta_boxes[] = array(
		'id'       => 'faqs_template',
		'title'    => 'FAQs Setting',
		'pages'    => array('page'),
		'context'  => 'normal',
		'priority' => 'high',
		'fields'   => array(
			array(
				'id'      => $prefix."faqs",
				'type'    => "elements",
				'button'  => "Add new faq",
				'hide'    => "yes",
				'options' => array(
					array(
						"type" => "text",
						"id"   => "text",
						"name" => esc_html__("Title",'vbegy'),
					),
					array(
						"type" => "textarea",
						"id"   => "textarea",
						"name" => esc_html__("Content",'vbegy'),
					),
				),
			),
		),
	);
	
	$meta_boxes[] = array(
		'id' => 'users_template',
		'title' => 'User groups Options',
		'pages' => array('page'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name' => esc_html__('Users per page','vbegy'),
				'desc' => esc_html__('Put the users per page.','vbegy'),
				'id'   => $prefix.'users_per_page',
				'std'  => '10',
				'type' => 'text'
			),
			array(
				'name'    => esc_html__('Choose the user groups show','vbegy'),
				'id'      => $prefix.'user_group',
				'type'    => 'checkbox_list',
				'std'     => array("editor","administrator","author","contributor","subscriber"),
				'options' => $options_groups,
			),
			array(
				'name'    => esc_html__('Order by','vbegy'),
				'id'      => $prefix.'user_sort',
				'type'    => 'select',
				'std' => array("registered"),
				'options'	=> array(
					'user_registered' => 'Register',
					'display_name'    => 'Name',
					'ID'              => 'ID',
					'question_count'  => 'Questions',
					'answers'         => 'Answers',
					'the_best_answer' => 'Best Answers',
					'points'          => 'Points',
					'post_count'      => 'Posts',
					'comments'        => 'Comments',
				),
			),
			array(
				'name'    => esc_html__('Order','vbegy'),
				'id'      => $prefix.'user_order',
				'std'     => array("DESC"),
				'type'    => 'radio',
				'options' => array(
					'DESC'  => 'Descending',
					'ASC'   => 'Ascending',
				),
			),
		),
	);
	
	$meta_boxes[] = array(
		'id'       => 'categories_template',
		'title'    => 'Categories Options',
		'pages'    => array('page'),
		'context'  => 'normal',
		'priority' => 'high',
		'fields'   => array(
			array(
				'name' => esc_html__('Categories per page','vbegy'),
				'desc' => esc_html__('Put the categories per page.','vbegy'),
				'id'   => $prefix.'cats_per_page',
				'std'  => '50',
				'type' => 'text'
			),
			array(
				'name'    => esc_html__('Categories type','vbegy'),
				'id'      => $prefix.'cats_tax',
				'std'     => array("question"),
				'type'    => 'radio',
				'options' => array(
					'question' => 'Question categories',
					'product'  => 'Product categories',
					'post'     => 'Post categories',
				),
			),
			array(
				'name'    => esc_html__('Order by','vbegy'),
				'id'      => $prefix.'cat_sort',
				'std'     => array("count"),
				'type'    => 'radio',
				'options' => array(
					'count' => 'Popular',
					'name'  => 'Name',
				),
			),
			array(
				'name'    => esc_html__('Order','vbegy'),
				'id'      => $prefix.'cat_order',
				'std'     => array("DESC"),
				'type'    => 'radio',
				'options' => array(
					'DESC'  => 'Descending',
					'ASC'   => 'Ascending',
				),
			),
		),
	);
	
	$meta_boxes[] = array(
		'id'       => 'tags_template',
		'title'    => 'Tags Options',
		'pages'    => array('page'),
		'context'  => 'normal',
		'priority' => 'high',
		'fields'   => array(
			array(
				'name' => esc_html__('Tags per page','vbegy'),
				'desc' => esc_html__('Put the tags per page.','vbegy'),
				'id'   => $prefix.'tags_per_page',
				'std'  => '50',
				'type' => 'text'
			),
			array(
				'name'    => esc_html__('Tags type','vbegy'),
				'id'      => $prefix.'tags_tax',
				'std'     => array("question"),
				'type'    => 'radio',
				'options' => array(
					'question' => 'Question tags',
					'product'  => 'Product tags',
					'post'     => 'Post tags',
				),
			),
			array(
				'name'    => esc_html__('Order by','vbegy'),
				'id'      => $prefix.'tag_sort',
				'std'     => array("count"),
				'type'    => 'radio',
				'options' => array(
					'count' => 'Popular',
					'name'  => 'Name',
				),
			),
			array(
				'name'    => esc_html__('Tags style','vbegy'),
				'desc'    => esc_html__('Choose the tags style.','vbegy'),
				'id'      => $prefix.'tag_style',
				'options' => array(
					'advanced' => 'Advanced',
					'simple'   => 'Simple',
				),
				'std'     => array('advanced'),
				'type'    => 'radio'
			),
			array(
				'name'    => esc_html__('Order','vbegy'),
				'id'      => $prefix.'tag_order',
				'std'     => array("DESC"),
				'type'    => 'radio',
				'options' => array(
					'DESC'  => 'Descending',
					'ASC'   => 'Ascending',
				),
			),
		),
	);
	/*
	$meta_boxes[] = array(
		'id' => 'forum_option',
		'title' => 'Forum Options',
		'pages' => array('page'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name' => esc_html__('Choose the categories show','vbegy'),
				'desc' => esc_html__('Choose the categories show.','vbegy'),
				'id'   => $prefix.'forum_categories',
				'type' => 'questions_categories',
				'show_all' => 'no'
			),
		),
	);
	*/
	$meta_boxes[] = array(
		'id' => 'ask_me',
		'title' => 'Home Options',
		'pages' => array('page'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name' => esc_html__('Home top box enable or disable','vbegy'),
				'desc' => esc_html__('Home top box enable or disable.','vbegy'),
				'id'   => $prefix.'index_top_box',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name'    => esc_html__('Home top box layout','vbegy'),
				'desc'    => esc_html__('Home top box layout.','vbegy'),
				'id'      => $prefix.'index_top_box_layout',
				'std'     => '1',
				'class'   => 'index_top_box_layout',
				'type'    => 'radio',
				'options' => array("1" => "Style 1","2" => "Style 2")
			),
			array(
				'name'    => esc_html__('Question title or comment','vbegy'),
				'desc'    => esc_html__('Question title or comment.','vbegy'),
				'id'      => $prefix.'index_title_comment',
				'std'     => 'title',
				'class'   => 'index_title_comment',
				'type'    => 'radio',
				'options' => array("title" => "Title","comment" => "Comment")
			),
			array(
				'name' => esc_html__('Remove the content ?','vbegy'),
				'desc' => esc_html__('Remove the content ( Title, content, buttons and ask question ) ?','vbegy'),
				'id'   => $prefix.'remove_index_content',
				'type' => 'checkbox'
			),
			array(
				'name'    => esc_html__('Home top box background','vbegy'),
				'desc'    => esc_html__('Home top box background.','vbegy'),
				'id'      => $prefix.'index_top_box_background',
				'std'     => 'background',
				'class'   => 'index_top_box_background',
				'type'    => 'radio',
				'options' => array("background" => "Background","slideshow" => "Slideshow")
			),
			array(
				'name'	=> esc_html__('Upload your images','vbegy'),
				'id'	=> $prefix."upload_images_home",
				'type'	=> 'image_advanced',
			),
			array(
				'name'		=> esc_html__("Background color",'vbegy'),
				'id'		=> $prefix."background_color_home",
				'type'		=> 'color',
			),
			array(
				'name'		=> esc_html__('Background','vbegy'),
				'id'		=> $prefix."background_img_home",
				'type'		=> 'upload',
			),
			array(
				'name'		=> esc_html__("Background repeat",'vbegy'),
				'id'		=> $prefix."background_repeat_home",
				'type'		=> 'select',
				'options'	=> array(
					'repeat'	=> 'repeat',
					'no-repeat'	=> 'no-repeat',
					'repeat-x'	=> 'repeat-x',
					'repeat-y'	=> 'repeat-y',
				),
			),
			array(
				'name'		=> esc_html__("Background fixed",'vbegy'),
				'id'		=> $prefix."background_fixed_home",
				'type'		=> 'select',
				'options'	=> array(
					'fixed'  => 'fixed',
					'scroll' => 'scroll',
				),
			),
			array(
				'name'		=> esc_html__("Background position x",'vbegy'),
				'id'		=> $prefix."background_position_x_home",
				'type'		=> 'select',
				'options'	=> array(
					'left'	 => 'left',
					'center' => 'center',
					'right'	 => 'right',
				),
			),
			array(
				'name'		=> esc_html__("Background position y",'vbegy'),
				'id'		=> $prefix."background_position_y_home",
				'type'		=> 'select',
				'options'	=> array(
					'top'	 => 'top',
					'center' => 'center',
					'bottom' => 'bottom',
				),
			),
			array(
				'name' => esc_html__("Full Screen Background",'vbegy'),
				'id'   => $prefix."background_full_home",
				'type' => 'checkbox',
				'std'  => 0,
			),
			array(
				'name' => esc_html__('Home top box title','vbegy'),
				'desc' => esc_html__('Put the Home top box title.','vbegy'),
				'id'   => $prefix.'index_title',
				'std'  => 'Welcome to Ask me',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Home top box content','vbegy'),
				'desc' => esc_html__('Put the Home top box content.','vbegy'),
				'id'   => $prefix.'index_content',
				'std'  => 'Duis dapibus aliquam mi, eget euismod sem scelerisque ut. Vivamus at elit quis urna adipiscing iaculis. Curabitur vitae velit in neque dictum blandit. Proin in iaculis neque.',
				'type' => 'textarea'
			),
			array(
				'name' => esc_html__('About Us title','vbegy'),
				'desc' => esc_html__('Put the About Us title.','vbegy'),
				'id'   => $prefix.'index_about',
				'std'  => 'About Us',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('About Us link','vbegy'),
				'desc' => esc_html__('Put the About Us link.','vbegy'),
				'id'   => $prefix.'index_about_h',
				'std'  => '#',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Join Now title','vbegy'),
				'desc' => esc_html__('Put the Join Now title.','vbegy'),
				'id'   => $prefix.'index_join',
				'std'  => 'Join Now',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Join Now link','vbegy'),
				'desc' => esc_html__('Put the Join Now link.','vbegy'),
				'id'   => $prefix.'index_join_h',
				'std'  => '#',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('About Us title if login','vbegy'),
				'desc' => esc_html__('Put the About Us title if login.','vbegy'),
				'id'   => $prefix.'index_about_login',
				'std'  => 'About Us',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('About Us link if login','vbegy'),
				'desc' => esc_html__('Put the About Us link if login.','vbegy'),
				'id'   => $prefix.'index_about_h_login',
				'std'  => '#',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Ask question title if login','vbegy'),
				'desc' => esc_html__('Put the Ask question title if login.','vbegy'),
				'id'   => $prefix.'index_join_login',
				'std'  => 'Ask question',
				'type' => 'text'
			),
			array(
				'name' => esc_html__('Ask question link if login','vbegy'),
				'desc' => esc_html__('Put the Ask question link if login.','vbegy'),
				'id'   => $prefix.'index_join_h_login',
				'std'  => '#',
				'type' => 'text'
			),
			array(
				'name'		=> esc_html__("Page style",'vbegy'),
				'id'		=> $prefix."index_tabs",
				'type'		=> 'select',
				'options'	=> array(
					1	=> "Tabs",
					2	=> 'Recent questions',
					3	=> 'Page content',
				),
			),
			array(
				'name' => esc_html__('Tabs pagination enable or disable','vbegy'),
				'desc' => esc_html__('Tabs pagination enable or disable.','vbegy'),
				'id'   => $prefix.'pagination_tabs',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name'	  => esc_html__('Choose your tabs','vbegy'),
				'id'	  => $prefix.'what_tab',
				'options' => array("recent_questions" => "Recent Questions","most_responses" => "Most Responses / answers","recently_answered" => "Recently Answered","no_answers" => "No answers","most_visit" => "Most Visit","most_vote" => "Most Vote","question_bump" => "Questions bump","recent_posts" => "Recent Posts"),
				'std'  => array("recent_questions","most_responses","recently_answered","no_answers"),
				'type'	  => 'checkbox_list'
			),
			array(
				'name'  => esc_html__('Choose the categories show','vbegy'),
				'desc'  => esc_html__('Choose the categories show.','vbegy'),
				'id'    => $prefix.'categories_show',
				'type'  => 'questions_categories',
				'addto' => 'vbegy_sort_home_elements'
			),
			array(
				'name'     => esc_html__('Sort the home elements','vbegy'),
				'id'       => $prefix."sort_home_elements",
				'std'      => array(array("value" => "recent_questions","name" => "Recent Questions"),array("value" => "most_responses","name" => "Most Responses / answers"),array("value" => "recently_answered","name" => "Recently Answered"),array("value" => "no_answers","name" => "No answers"),array("value" => "most_visit","name" => "Most Visit"),array("value" => "most_vote","name" => "Most Vote"),array("value" => "question_bump","name" => "Questions bump"),array("value" => "recent_posts","name" => "Recent Posts")),
				'type'     => "sort",
				'options'  => array(
					array("value" => "recent_questions"  ,"name" => "Recent Questions"),
					array("value" => "most_responses"    ,"name" => "Most Responses / answers"),
					array("value" => "recently_answered" ,"name" => "Recently Answered"),
					array("value" => "no_answers"        ,"name" => "No answers"),
					array("value" => "most_visit"        ,"name" => "Most Visit"),
					array("value" => "most_vote"         ,"name" => "Most Vote"),
					array("value" => "question_bump"     ,"name" => "Questions bump"),
					array("value" => "recent_posts"      ,"name" => "Recent Posts")
				)
			),
			array(
				'name' => esc_html__('Posts per page','vbegy'),
				'desc' => esc_html__('Put the Posts per page.','vbegy'),
				'id'   => $prefix.'posts_per_page',
				'std'  => '10',
				'type' => 'text'
			),
			array(
			    'name'    => esc_html__('Content before tabs','vbegy'),
			    'id'      => $prefix.'content_before_tabs',
			    'type'    => 'wysiwyg',
			    'raw'     => false,
			    'options' => array(
			        'textarea_rows' => 5,
			        'teeny'         => true,
			    ),
			),
			array(
			    'name'    => esc_html__('Content after tabs','vbegy'),
			    'id'      => $prefix.'content_after_tabs',
			    'type'    => 'wysiwyg',
			    'raw'     => false,
			    'options' => array(
			        'textarea_rows' => 5,
			        'teeny'         => true,
			    ),
			),
		),
	);
	
	$featured_image_question = vpanel_options('featured_image_question');
	if ($featured_image_question == 1) {
		$meta_boxes[] = array(
			'id' => 'question_setting',
			'title' => 'Featured Image Setting',
			'pages' => array('question'),
			'context' => 'normal',
			'fields' => array(
				array(
					'name' => esc_html__('Custom featured image size','vbegy'),
					'desc' => esc_html__('Click ON to set the custom featured image size.','vbegy'),
					'id'   =>  $prefix.'custom_featured_image_size',
					'type' => 'checkbox'
				),
				array(
					"name"       => esc_html__("Featured image width",'vbegy'),
					"id"         => $prefix."featured_image_width",
					"type"       => "slider",
					"std"        => "260",
					"js_options" => array(
						"step" => "1",
						"min"  => 50,
						"max"  => 600,
					),
				),
				array(
					"name"       => esc_html__("Featured image height",'vbegy'),
					"id"         => $prefix."featured_image_height",
					"type"       => "slider",
					"std"        => "185",
					"js_options" => array(
						"step" => "1",
						"min"  => 50,
						"max"  => 600,
					),
				),
			),
		);
	}
	
	$meta_boxes[] = array(
		'id' => 'post_page',
		'title' => 'Post and Page Options',
		'pages' => array('post','page','question','product'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name'		=> esc_html__('Layout','vbegy'),
				'id'		=> $prefix."layout",
				'class'     => 'radio_no_margin',
				'type'		=> 'radio',
				'options'	=> array(
					'default'	=> '',
					'full'		=> '',
					'fixed'		=> '',
					'fixed_2'	=> '',
				),
				'std'		=> 'default',
			),
			array(
				'name'		=> esc_html__('Choose page / post template','vbegy'),
				'id'		=> $prefix."home_template",
				'class'     => 'radio_no_margin',
				'type'		=> 'radio',
				'options'	=> array(
					'default'   => '',
					'grid_1300' => '',
					'grid_1200' => '',
					'grid_970'  => ''
				),
				'std'		=> 'default',
			),
			array(
				'name'		=> esc_html__('Choose page / post skin','vbegy'),
				'id'		=> $prefix."site_skin_l",
				'class'     => 'radio_no_margin',
				'type'		=> 'radio',
				'options'	=> array(
					'default'    => '',
					'site_light' => '',
					'site_dark'  => ''
				),
				'std'		=> 'default',
			),
			array(
				'name'		=> esc_html__('Choose Your Skin','vbegy'),
				'id'		=> $prefix."skin",
				'class'		=> 'radio_no_margin',
				'type'		=> 'radio',
				'options'	=> array(
					'default'		=> '',
					'skin'	    	=> '',
					'blue'			=> '',
					'gray'			=> '',
					'green'			=> '',
					'moderate_cyan' => '',
					'orange'		=> '',
					'purple'	    => '',
					'red'			=> '',
					'strong_cyan'	=> '',
					'yellow'		=> '',
				),
				'std'		=> 'default',
			),
			array(
				'name'		=> esc_html__('Primary Color','vbegy'),
				'id'		=> $prefix."primary_color",
				'type'		=> 'color',
			),
			array(
				'name'		=> esc_html__('Background','vbegy'),
				'id'		=> $prefix."background_img",
				'type'		=> 'upload',
			),
			array(
				'name'		=> esc_html__("Background color",'vbegy'),
				'id'		=> $prefix."background_color",
				'type'		=> 'color',
			),
			array(
				'name'		=> esc_html__("Background repeat",'vbegy'),
				'id'		=> $prefix."background_repeat",
				'type'		=> 'select',
				'options'	=> array(
					'repeat'	=> 'repeat',
					'no-repeat'	=> 'no-repeat',
					'repeat-x'	=> 'repeat-x',
					'repeat-y'	=> 'repeat-y',
				),
			),
			array(
				'name'		=> esc_html__("Background fixed",'vbegy'),
				'id'		=> $prefix."background_fixed",
				'type'		=> 'select',
				'options'	=> array(
					'fixed'  => 'fixed',
					'scroll' => 'scroll',
				),
			),
			array(
				'name'		=> esc_html__("Background position x",'vbegy'),
				'id'		=> $prefix."background_position_x",
				'type'		=> 'select',
				'options'	=> array(
					'left'	 => 'left',
					'center' => 'center',
					'right'	 => 'right',
				),
			),
			array(
				'name'		=> esc_html__("Background position y",'vbegy'),
				'id'		=> $prefix."background_position_y",
				'type'		=> 'select',
				'options'	=> array(
					'top'	 => 'top',
					'center' => 'center',
					'bottom' => 'bottom',
				),
			),
			array(
				'name' => esc_html__("Full Screen Background",'vbegy'),
				'id'   => $prefix."background_full",
				'type' => 'checkbox',
				'std'  => 0,
			),
			array(
				'name'		=> esc_html__('Sidebar','vbegy'),
				'id'		=> $prefix."sidebar",
				'class'   => 'radio_no_margin',
				'type'		=> 'radio',
				'options'	=> array(
					'default'		=> '',
					'right'			=> '',
					'full'			=> '',
					'left'			=> '',
				),
				'std'		=> 'default',
			),
			array(
				'name'		=> esc_html__('Select your sidebar','vbegy'),
				'id'		=> $prefix.'what_sidebar',
				'type'		=> 'select',
				'options'	=> $new_sidebars,
			),
			array(
				'name'		=> esc_html__('Head post','vbegy'),
				'id'		=> $prefix.'what_post',
				'type'		=> 'select',
				'options'	=> array(
					'image' => "Featured Image",
					'lightbox' => "Lightbox",
					'google' => "Google Map",
					'slideshow' => "Slideshow",
					'video' => "Video",
				),
				'std'		=> array('image'),
				'desc'		=> esc_html__('Choose from here the post type.','vbegy'),
			),
			array(
				'name'		=> esc_html__('Google map','vbegy'),
				'desc'		=> esc_html__("Put your google map html",'vbegy'),
				'id'		=> $prefix."google",
				'type'		=> 'textarea',
				'cols'		=> "40",
				'rows'		=> "8"
			),
			array(
				'name'		=> esc_html__('Slideshow ?','vbegy'),
				'id'		=> $prefix.'slideshow_type',
				'type'		=> 'select',
				'options'	=> array(
					'custom_slide' => "Custom Slideshow",
					'upload_images' => "Upload your images",
				),
				'std'		=> array('custom_slide'),
			),
			array(
				'id'		=> $prefix.'slideshow_post',
				'type'		=> 'note',
			),
			array(
				'name'	=> esc_html__('Upload your images','vbegy'),
				'id'	=> $prefix."upload_images",
				'type'	=> 'image_advanced',
			),
			array(
				'name'		=> esc_html__('Video type','vbegy'),
				'id'		=> $prefix.'video_post_type',
				'type'		=> 'select',
				'options'	=> array(
					'youtube' => "Youtube",
					'vimeo' => "Vimeo",
					'daily' => "Dialymotion",
					'html5' => "HTML 5",
				),
				'std'		=> array('youtube'),
				'desc'		=> esc_html__('Choose from here the video type','vbegy'),
			),
			array(
				'name'		=> esc_html__('Video ID','vbegy'),
				'id'		=> $prefix.'video_post_id',
				'desc'		=> esc_html__('Put here the video id : https://www.youtube.com/watch?v=sdUUx5FdySs EX : "sdUUx5FdySs".','vbegy'),
				'type'		=> 'text',
				'std'		=> ''
			),
			array(
				'name' => esc_html__('Video Image','vbegy'),
				'desc' => esc_html__('Upload a image, or enter URL to an image if it is already uploaded.','vbegy'),
				'id'   => $prefix.'video_image',
				'std'  => '',
				'type' => 'upload'
			),
			array(
				'name'		=> esc_html__('Mp4 video','vbegy'),
				'id'		=> $prefix.'video_mp4',
				'desc'		=> esc_html__('Put here the mp4 video','vbegy'),
				'type'		=> 'text',
				'std'		=> ''
			),
			array(
				'name'		=> esc_html__('M4v video','vbegy'),
				'id'		=> $prefix.'video_m4v',
				'desc'		=> esc_html__('Put here the m4v video','vbegy'),
				'type'		=> 'text',
				'std'		=> ''
			),
			array(
				'name'		=> esc_html__('Webm video','vbegy'),
				'id'		=> $prefix.'video_webm',
				'desc'		=> esc_html__('Put here the webm video','vbegy'),
				'type'		=> 'text',
				'std'		=> ''
			),
			array(
				'name'		=> esc_html__('Ogv video','vbegy'),
				'id'		=> $prefix.'video_ogv',
				'desc'		=> esc_html__('Put here the ogv video','vbegy'),
				'type'		=> 'text',
				'std'		=> ''
			),
			array(
				'name'		=> esc_html__('Wmv video','vbegy'),
				'id'		=> $prefix.'video_wmv',
				'desc'		=> esc_html__('Put here the wmv video','vbegy'),
				'type'		=> 'text',
				'std'		=> ''
			),
			array(
				'name'		=> esc_html__('Flv video','vbegy'),
				'id'		=> $prefix.'video_flv',
				'desc'		=> esc_html__('Put here the flv video','vbegy'),
				'type'		=> 'text',
				'std'		=> ''
			),
		),
	);
	
	$meta_boxes[] = array(
		'id' => 'single_page',
		'title' => 'Single Pages Options',
		'pages' => array('post','page','question','product'),
		'context' => 'side',
		'priority' => 'default',
		'fields' => array(
			array(
				'name' => esc_html__('Choose a custom page setting','vbegy'),
				'desc' => esc_html__('Choose a custom page setting.','vbegy'),
				'id'   => $prefix.'custom_page_setting',
				'type' => 'checkbox'
			),
			array(
				'name' => esc_html__('Sticky sidebar enable or disable','vbegy'),
				'desc' => esc_html__('Sticky sidebar enable or disable.','vbegy'),
				'id'   => $prefix.'sticky_sidebar_s',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name' => esc_html__('Post meta enable or disable','vbegy'),
				'desc' => esc_html__('Post meta enable or disable.','vbegy'),
				'id'   => $prefix.'post_meta_s',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name' => esc_html__('Share enable or disable','vbegy'),
				'desc' => esc_html__('Share enable or disable.','vbegy'),
				'id'   => $prefix.'post_share_s',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name' => esc_html__('Author info box enable or disable','vbegy'),
				'desc' => esc_html__('Author info box enable or disable.','vbegy'),
				'id'   =>  $prefix.'post_author_box_s',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name' => esc_html__('Related post enable or disable','vbegy'),
				'desc' => esc_html__('Related post enable or disable.','vbegy'),
				'id'   =>  $prefix.'related_post_s',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name' => esc_html__('Comments enable or disable','vbegy'),
				'desc' => esc_html__('Comments enable or disable.','vbegy'),
				'id'   =>  $prefix.'post_comments_s',
				'std'  => 1,
				'type' => 'checkbox'
			),
			array(
				'name' => esc_html__('Navigation post enable or disable','vbegy'),
				'desc' => esc_html__('Navigation post ( next and previous posts) enable or disable.','vbegy'),
				'id'   =>  $prefix.'post_navigation_s',
				'std'  => 1,
				'type' => 'checkbox'
			),
		),
	);
	
	$meta_boxes[] = array(
		'id' => 'advertising_meta',
		'title' => 'Advertising Options',
		'pages' => array('post','page','question','product'),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'name'  => esc_html__("Advertising after header",'vbegy'),
				'id'    => $prefix.'header_adv_n',
				'type'  => 'heading'
			),
			array(
				'name'    => esc_html__('Advertising type','vbegy'),
				'desc'    => esc_html__('Advertising type.','vbegy'),
				'id'      => $prefix.'header_adv_type',
				'std'     => 'custom_image',
				'type'    => 'radio',
				'class'   => 'radio',
				'options' => array("display_code" => "Display code","custom_image" => "Custom Image")
			),
			array(
				'name' => esc_html__('Image URL','vbegy'),
				'desc' => esc_html__('Upload a image, or enter URL to an image if it is already uploaded. ','vbegy'),
				'id'   => $prefix.'header_adv_img',
				'std'  => '',
				'type' => 'upload'
			),
			array(
				'name' => esc_html__('Advertising url','vbegy'),
				'desc' => esc_html__('Advertising url. ','vbegy'),
				'id'   => $prefix.'header_adv_href',
				'std'  => '#',
				'type' => 'text'
			),
			array(
				'name' => esc_html__("Advertising Code html ( Ex: Google ads)",'vbegy'),
				'desc' => esc_html__("Advertising Code html ( Ex: Google ads)",'vbegy'),
				'id'   => $prefix.'header_adv_code',
				'std'  => '',
				'type' => 'textarea'
			),
			array(
				'name'  => esc_html__("Advertising 1 in post and question",'vbegy'),
				'id'    => $prefix.'share_adv_n',
				'type'  => 'heading'
			),
			array(
				'name' => esc_html__('Advertising type','vbegy'),
				'desc' => esc_html__('Advertising type.','vbegy'),
				'id'   => $prefix.'share_adv_type',
				'std'  => 'custom_image',
				'type' => 'radio',
				'class'   => 'radio',
				'options' => array("display_code" => "Display code","custom_image" => "Custom Image")
			),
			array(
				'name' => esc_html__('Image URL','vbegy'),
				'desc' => esc_html__('Upload a image, or enter URL to an image if it is already uploaded. ','vbegy'),
				'id'   => $prefix.'share_adv_img',
				'std'  => '',
				'type' => 'upload'
			),
			array(
				'name' => esc_html__('Advertising url','vbegy'),
				'desc' => esc_html__('Advertising url. ','vbegy'),
				'id'   => $prefix.'share_adv_href',
				'std'  => '#',
				'type' => 'text'
			),
			array(
				'name' => esc_html__("Advertising Code html ( Ex: Google ads)",'vbegy'),
				'desc' => esc_html__("Advertising Code html ( Ex: Google ads)",'vbegy'),
				'id'   => $prefix.'share_adv_code',
				'std'  => '',
				'type' => 'textarea'
			),
			array(
				'name'  => esc_html__("Advertising 2 in post and question",'vbegy'),
				'id'    => $prefix.'related_adv_n',
				'type'  => 'heading'
			),
			array(
				'name' => esc_html__('Advertising type','vbegy'),
				'desc' => esc_html__('Advertising type.','vbegy'),
				'id'   => $prefix.'related_adv_type',
				'std'  => 'custom_image',
				'type' => 'radio',
				'class'   => 'radio',
				'options' => array("display_code" => "Display code","custom_image" => "Custom Image")
			),
			array(
				'name' => esc_html__('Image URL','vbegy'),
				'desc' => esc_html__('Upload a image, or enter URL to an image if it is already uploaded. ','vbegy'),
				'id'   => $prefix.'related_adv_img',
				'std'  => '',
				'type' => 'upload'
			),
			array(
				'name' => esc_html__('Advertising url','vbegy'),
				'desc' => esc_html__('Advertising url. ','vbegy'),
				'id'   => $prefix.'related_adv_href',
				'std'  => '#',
				'type' => 'text'
			),
			array(
				'name' => esc_html__("Advertising Code html ( Ex: Google ads)",'vbegy'),
				'desc' => esc_html__("Advertising Code html ( Ex: Google ads)",'vbegy'),
				'id'   => $prefix.'related_adv_code',
				'std'  => '',
				'type' => 'textarea'
			),
			array(
				'name'  => esc_html__("Advertising after content",'vbegy'),
				'id'    => $prefix.'content_adv_n',
				'type'  => 'heading'
			),
			array(
				'name'    => esc_html__('Advertising type','vbegy'),
				'desc'    => esc_html__('Advertising type.','vbegy'),
				'id'      => $prefix.'content_adv_type',
				'std'     => 'custom_image',
				'type'    => 'radio',
				'class'   => 'radio',
				'options' => array("display_code" => "Display code","custom_image" => "Custom Image")
			),
			array(
				'name' => esc_html__('Image URL','vbegy'),
				'desc' => esc_html__('Upload a image, or enter URL to an image if it is already uploaded. ','vbegy'),
				'id'   => $prefix.'content_adv_img',
				'std'  => '',
				'type' => 'upload'
			),
			array(
				'name' => esc_html__('Advertising url','vbegy'),
				'desc' => esc_html__('Advertising url. ','vbegy'),
				'id'   => $prefix.'content_adv_href',
				'std'  => '#',
				'type' => 'text'
			),
			array(
				'name' => esc_html__("Advertising Code html ( Ex: Google ads)",'vbegy'),
				'desc' => esc_html__("Advertising Code html ( Ex: Google ads)",'vbegy'),
				'id'   => $prefix.'content_adv_code',
				'std'  => '',
				'type' => 'textarea'
			)
		),
	);
	
	foreach ( $meta_boxes as $meta_box ) {
		new RW_Meta_Box( $meta_box );
	}
}?>