<?php
/* big pic */
add_action( 'widgets_init', 'widget_highest_points_widget' );
function widget_highest_points_widget() {
	register_widget( 'Widget_Highest_Points' );
}

class Widget_Highest_Points extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'highest-points-widget'  );
		$control_ops = array( 'id_base' => 'highest-points-widget' );
		parent::__construct( 'highest-points-widget','Ask Me - Highest Points', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		$title		   = apply_filters('widget_title', $instance['title'] );
		$user_per_page = (int)$instance['user_per_page'];
		
		$active_points = vpanel_options("active_points");
		if ($active_points == 1) {
			echo $before_widget;
				if ( $title )
					echo $before_title.esc_attr($title).$after_title;
				?>
				<div class="widget_highest_points">
					<?php global $wpdb;
					$query = $wpdb->prepare("SELECT DISTINCT SQL_CALC_FOUND_ROWS $wpdb->users.ID,$wpdb->users.display_name
					FROM $wpdb->users
					INNER JOIN $wpdb->usermeta
					ON ( $wpdb->users.ID = $wpdb->usermeta.user_id )
					WHERE 1=%s
					AND ( ( $wpdb->usermeta.meta_key = 'points'
					AND CAST($wpdb->usermeta.meta_value AS CHAR) >= '0' ) )
					ORDER BY $wpdb->usermeta.meta_value+0 DESC
					LIMIT 0, $user_per_page",1);
					$users = $wpdb->get_results($query);
					if (isset($users) && is_array($users) && !empty($users)) {
						echo '<ul>';
						foreach ($users as $key => $author) {
							$points_u = get_user_meta($author->ID,"points",true);
							$user_profile_page = vpanel_get_user_url($author->ID);
							$you_avatar = get_user_meta($author->ID,'you_avatar',true);?>
							<li>
								<div class="author-img">
									<a href="<?php echo $user_profile_page?>">
										<?php echo askme_user_avatar($you_avatar,65,65,$author->ID,$author->display_name);?>
									</a>
								</div>
								<div class="author-content">
									<h6><a href="<?php echo $user_profile_page?>"><?php echo $author->display_name?></a></h6>
									<?php echo vpanel_get_badge($author->ID)?>
									<span class="comment"><?php echo ($points_u != ""?$points_u:"0")?> <?php _e("Points","vbegy")?></span>
								</div>
								<div class="clearfix"></div>
							</li>
						<?php }
						echo '</ul>';
					}?>
				</div>
				<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance				   = $old_instance;
		$instance['title']		   = strip_tags( $new_instance['title'] );
		$instance['user_per_page'] = $new_instance['user_per_page'];
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title' => 'Highest points','user_per_page' => '5' );
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title : </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo (isset($instance['title'])?esc_attr($instance['title']):""); ?>" class="widefat" type="text">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'user_per_page' ); ?>">Number of users to show : </label>
			<input id="<?php echo $this->get_field_id( 'user_per_page' ); ?>" name="<?php echo $this->get_field_name( 'user_per_page' ); ?>" value="<?php echo (isset($instance['user_per_page'])?(int)$instance['user_per_page']:""); ?>" size="3" type="text">
		</p>
	<?php
	}
}
?>