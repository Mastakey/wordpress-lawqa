<?php
/* Ask Question */
add_action( 'widgets_init', 'widget_ask_widget' );
function widget_ask_widget() {
	register_widget( 'Widget_Ask' );
}

class Widget_Ask extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'ask-widget'  );
		$control_ops = array( 'id_base' => 'ask-widget' );
		parent::__construct( 'ask-widget','Ask Me - Ask Question Button', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		$ask_type = esc_attr($instance['ask_type']);?>
		<div class="widget_ask">
			<a href="<?php echo esc_url(get_page_link(vpanel_options('add_question')))?>" class="color button small margin_0<?php echo ($ask_type == "popup"?" ask-question-link":"")?>"><?php _e("Ask a Question","vbegy")?></a>
		</div>
	<?php }

	function update( $new_instance, $old_instance ) {
		$instance		      = $old_instance;
		$instance['title']    = strip_tags( $new_instance['title'] );
		$instance['ask_type'] = $new_instance['ask_type'];
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title' => __('Ask question','vbegy'), 'ask_type' => 'link');
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title : </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo (isset($instance['title'])?esc_attr($instance['title']):""); ?>" class="widefat" type="text">
		</p>
		<p>
			<label>Ask a question type : </label>
			<br>
			<?php $query_type = array("link" => "Open the question at the link","popup" => "Open the question at popup");
			foreach ($query_type as $key_r => $value_r) {?>
				<input id="<?php echo self::get_field_id( 'ask_type' )."-".$key_r ?>" value="<?php echo esc_attr($key_r)?>" type="radio" name="<?php echo self::get_field_name( 'ask_type' ); ?>" <?php echo checked($key_r, $instance['ask_type'], false)?>>
				<label for="<?php echo self::get_field_id( 'ask_type' )."-".$key_r ?>"><?php echo esc_attr($value_r)?></label>
				<br>
			<?php }?>
		</p>
	<?php
	}
}
?>