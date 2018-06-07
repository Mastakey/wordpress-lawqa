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
	</div>
	<div class="nearby-lawyers">
		<h2>Nearby Lawyers</h2>
	</div>
</div>
<?php get_footer();?>