<?php
/* twitter */
add_action( 'widgets_init', 'latest_tweet_widget' );
function latest_tweet_widget() {
	register_widget( 'Latest_Tweets' );
}
class Latest_Tweets extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'twitter-widget'  );
		$control_ops = array( 'id_base' => 'twitter_widget' );
		parent::__construct( 'twitter_widget','Ask Me - Twitter', $widget_ops, $control_ops );
	}
	
	private function hyperlinks($text){
		$text = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" target=\"_blank\">$1</a>", $text);
		$text = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" target=\"_blank\">$1</a>", $text);
		$text = preg_replace("/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i","<a href=\"mailto://$1\" target=\"_blank\">$1</a>", $text);
		$text = preg_replace('/([\.|\,|\:|\?|\?|\>|\{|\(]?)#{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"https://twitter.com/#search?q=$2\" target=\"_blank\">#$2</a>$3 ", $text);
		return $text;
	}
	
	private function twitter_users($text){
		$text = preg_replace('/([\.|\,|\:|\?|\?|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"https://twitter.com/$2\" target=\"_blank\">@$2</a>$3 ", $text);
		return $text;
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		$title		  = apply_filters('widget_title', $instance['title'] );
		$no_of_tweets = (int)$instance['no_of_tweets'];
		$accounts	  = esc_attr($instance['accounts']);

		echo $before_widget;
			if ( $title )
				echo $before_title.$title.$after_title;?>
	
			<div class="widget_twitter">
				<?php $tweets = get_transient('vpanel_twitter_widget_'.$args["widget_id"].$accounts);
				if ($tweets == false) {
					$tweets = vpanel_twitter_tweets($accounts,$no_of_tweets);
					set_transient('vpanel_twitter_widget_'.$args["widget_id"].$accounts, $tweets, HOUR_IN_SECONDS);
				}
				if (isset($tweets) && is_array($tweets)) {
					$i = 0;?>
					<ul>
						<?php foreach ( $tweets as $item ) {
							$tweet     = $item->text;
							$tweet     = make_clickable( $tweet );
							$tweet     = $this->twitter_users( $tweet );
							$permalink = 'https://twitter.com/#!/'. $accounts .'/status/'. $item->id_str;
							
							$time = strtotime( $item->created_at );
							$h_time = sprintf( __( 'about %s ago','vbegy' ), human_time_diff( $time ) );
							
							echo '<li class="tweet-item">
								<div class="tweet-text">
									<a target="_blank" href="'.esc_url($permalink).'" class="tweet-icon"></a>
									<a target="_blank" class="tweet-name" href="'.esc_url($permalink).'">'.$accounts.'</a>
									'.$tweet.'
									<br>
									<span class="tweet-time">'.$h_time.'</span>
								</div>
							</li>';
							$i++;
							if ( $i >= $no_of_tweets ) {
								break;
							}
						}?>
					</ul>
				<?php }?>
				<div class="clearfix"></div>
			</div>
		<?php echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$widget_id                = explode("-",$this->get_field_id("widget_id"));
		$widget_id                = $widget_id[1]."-".$widget_id[2];
		$instance				  = $old_instance;
		$instance['title']		  = strip_tags( $new_instance['title'] );
		$instance['no_of_tweets'] = strip_tags( $new_instance['no_of_tweets'] );
		$instance['accounts']	  = strip_tags( $new_instance['accounts'] );
		delete_transient('vpanel_twitter_widget_'.$widget_id.$instance['accounts']);
		delete_option('vpanel_twitter_token');
		return $instance;
	}

	function form( $instance ) {
		$defaults = array('title' => '@Follow Me','accounts' => '2codeThemes','no_of_tweets' => '5');
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title : </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr($instance['title']); ?>" class="widefat" type="text">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'no_of_tweets' ); ?>">No of Tweets to show : </label>
			<input id="<?php echo $this->get_field_id( 'no_of_tweets' ); ?>" name="<?php echo $this->get_field_name( 'no_of_tweets' ); ?>" value="<?php echo (int)$instance['no_of_tweets']; ?>" type="text" size="3">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'accounts' ); ?>">Twitter username : </label>
			<input id="<?php echo $this->get_field_id( 'accounts' ); ?>" name="<?php echo $this->get_field_name( 'accounts' ); ?>" value="<?php if (isset($instance['accounts']) && !empty($instance['accounts'])) {echo esc_attr($instance['accounts']);} ?>" class="widefat" type="text">
		</p>
	<?php
	}
}
?>