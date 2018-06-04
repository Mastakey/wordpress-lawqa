<?php
/* Profile */
add_action( 'widgets_init', 'widget_profile_widget' );
function widget_profile_widget() {
	register_widget( 'Widget_Profile' );
}

class Widget_Profile extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'profile-widget'  );
		$control_ops = array( 'id_base' => 'profile-widget' );
		parent::__construct( 'profile-widget','Ask Me - Profile', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		$title     = apply_filters('widget_title', $instance['title'] );
		if (is_user_logged_in) {
			echo $before_widget;
				if ( $title )
					echo $before_title.esc_attr($title).$after_title;?>
				<div class="widget_profile">
					<?php $active_points = vpanel_options("active_points");
					$user_links = vpanel_options("user_links");
					$out = is_user_logged_in_data($user_links,"on")?>
				</div>
				<?php
				echo ($out);
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance		   = $old_instance;
		$instance['title']     = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title' => __('Profile','vbegy') );
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title : </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo (isset($instance['title'])?esc_attr($instance['title']):""); ?>" class="widefat" type="text">
		</p>
	<?php
	}
}
?>