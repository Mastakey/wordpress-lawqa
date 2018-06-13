<?php get_header();
	$date_format = (vpanel_options("date_format")?vpanel_options("date_format"):get_option("date_format"));
	$vbegy_what_post = rwmb_meta('vbegy_what_post','select',$post->ID);
	$vbegy_sidebar = rwmb_meta('vbegy_sidebar','select',$post->ID);
	if ($vbegy_sidebar == "default") {
		$vbegy_sidebar_all = vpanel_options("sidebar_layout");
	}else {
		$vbegy_sidebar_all = $vbegy_sidebar;
	}
	$vbegy_google = rwmb_meta('vbegy_google',"textarea",$post->ID);
	$video_id = rwmb_meta('vbegy_video_post_id',"select",$post->ID);
	$video_type = rwmb_meta('vbegy_video_post_type',"text",$post->ID);
	$vbegy_slideshow_type = rwmb_meta('vbegy_slideshow_type','select',$post->ID);
	if ($video_type == 'youtube') {
		$type = "https://www.youtube.com/embed/".$video_id;
	}else if ($video_type == 'vimeo') {
		$type = "https://player.vimeo.com/video/".$video_id;
	}else if ($video_type == 'daily') {
		$type = "https://www.dailymotion.com/embed/video/".$video_id;
	}
?>
<div class="front-page-content">
	<div class="recent-questions">
		<h2>Recent Questions</h2>
		<?php
		$paged            = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
		$sticky_questions = get_option('sticky_questions');
		$active_sticky    = true;
		include("sticky-question.php");
		
		$user_id_query = array("key" => "user_id","compare" => "NOT EXISTS");
		$custom_args = (isset($custom_args) && is_array($custom_args)?$custom_args:array());
		$args = array_merge($custom_args,$post__not_in,array("paged" => $paged,"post_type" => "question","posts_per_page" => get_option("posts_per_page"),"meta_query" => array($user_id_query)));
		query_posts($args);
		$active_sticky = false;
		get_template_part("loop-question");
		vpanel_pagination();
		wp_reset_query();
		?>
	</div>
	<div class="nearby-lawyers">
		<h2>Nearby Lawyers</h2>
	</div>
</div>
<?php get_footer();?>