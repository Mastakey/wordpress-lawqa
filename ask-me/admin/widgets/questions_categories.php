<?php
/* Questions Categories */
add_action( 'widgets_init', 'widget_questions_categories_widget' );
function widget_questions_categories_widget() {
	register_widget( 'Widget_Questions_Categories' );
}
class Widget_Questions_Categories extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'questions_categories-widget'  );
		$control_ops = array( 'id_base' => 'questions_categories-widget' );
		parent::__construct( 'questions_categories-widget','Ask Me - Questions Categories', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		$title			  = apply_filters('widget_title', $instance['title'] );
		$questions_counts = esc_attr($instance['questions_counts']);
		$show_child       = esc_attr($instance['show_child']);
			
		echo $before_widget;
			if ( $title )
				echo $before_title.esc_attr($title).$after_title;
			if ($show_child == "on") {?>
				<div class="widget_child_categories">
					<div class="categories-toggle-accordion">
			<?php }?>
				<ul>
					<?php $args = array(
					'parent'       => ($show_child == "on"?0:""),
					'orderby'      => 'name',
					'order'        => 'ASC',
					'hide_empty'   => 1,
					'hierarchical' => 1,
					'taxonomy'     => ask_question_category,
					'pad_counts'   => false );
					$options_categories = get_categories($args);
					foreach ($options_categories as $category) {
						if ($show_child == "on") {
							$children = get_terms(ask_question_category,array('parent' => $category->cat_ID,'hide_empty' => 1));
						}?>
						<li>
							<?php if ($show_child == "on" && isset($children) && is_array($children) && !empty($children)) {?>
								<h4 class="accordion-title">
							<?php }?>
								<a<?php echo ($show_child == "on"?' class="'.(isset($children) && is_array($children) && !empty($children)?"link-child":"link-not-child").'"':'')?> href="<?php echo get_term_link($category->slug,ask_question_category)?>"><?php echo $category->name;
									if ($questions_counts == "on") {?>
										<span> ( <span><?php echo $category->count." ";
										if ($category->count == 1) {
											_e("Question","vbegy");
										}else {
											_e("Questions","vbegy");
										}?></span> ) </span>
									<?php }?>
									<i></i>
								</a>
							<?php if ($show_child == "on" && isset($children) && is_array($children) && !empty($children)) {?>
								</h4>
							<?php }
							if ($show_child == "on" && isset($children) && is_array($children) && !empty($children)) {?>
								<div class="accordion-inner">
									<ul>
										<?php $args = array(
										'child_of'                 => $category->cat_ID,
										'orderby'                  => 'name',
										'order'                    => 'ASC',
										'hide_empty'               => 0,
										'taxonomy'                 => ask_question_category,
										'pad_counts'               => false );
										$options_categories = get_categories($args);
										foreach ($options_categories as $category) {?>
											<li>
												<a href="<?php echo get_term_link($category->slug,ask_question_category)?>"><?php echo $category->name;
													if ($questions_counts == "on") {?>
														<span> ( <span><?php echo $category->count." ";
														if ($category->count == 1) {
															_e("Question","vbegy");
														}else {
															_e("Questions","vbegy");
														}?></span> ) </span>
													<?php }?>
												</a>
											</li>
										<?php }?>
									</ul>
								</div>
							<?php }?>	
						</li>
					<?php }?>
				</ul>
			<?php if ($show_child == "on") {?>
					</div>
				</div>
			<?php }?>
				
			<?php 
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance					  = $old_instance;
		$instance['title']			  = strip_tags( $new_instance['title'] );
		$instance['questions_counts'] = $new_instance['questions_counts'];
		$instance['show_child']       = $new_instance['show_child'];
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title' => __('Questions Categories','vbegy'),'questions_counts' => 'on' );
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title : </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo (isset($instance['title'])?esc_attr($instance['title']):""); ?>" class="widefat" type="text">
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo (isset($instance['questions_counts']) && $instance['questions_counts'] == "on"?' checked="checked"':"");?> id="<?php echo $this->get_field_id( 'questions_counts' ); ?>" name="<?php echo $this->get_field_name( 'questions_counts' ); ?>">
			<label for="<?php echo $this->get_field_id( 'questions_counts' ); ?>">Show questions counts?</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo (isset($instance['show_child']) && $instance['show_child'] == "on"?' checked="checked"':"");?> id="<?php echo $this->get_field_id( 'show_child' ); ?>" name="<?php echo $this->get_field_name( 'show_child' ); ?>">
			<label for="<?php echo $this->get_field_id( 'show_child' ); ?>">Show the child categories accordion</label>
		</p>
	<?php
	}
}
?>