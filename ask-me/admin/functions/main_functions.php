<?php
if (is_user_logged_in()) {
	define("is_user_logged_in",true);
}else {
	define("is_user_logged_in",false);
}
/* excerpt */
define("excerpt_type",vpanel_options("excerpt_type"));
function excerpt ($excerpt_length,$excerpt_type = excerpt_type) {
	global $post;
	$excerpt_length = (isset($excerpt_length) && $excerpt_length != ""?$excerpt_length:5);
	$content = $post->post_content;
	//$content = apply_filters('the_content', strip_shortcodes($content));
	if ($excerpt_type == "characters") {
		$content = mb_substr($content,0,$excerpt_length,"UTF-8");
	}else {
		$words = explode(' ',$content,$excerpt_length + 1);
		if (count($words) > $excerpt_length) :
			array_pop($words);
			array_push($words,'');
			$content = implode(' ',$words).'...';
		endif;
	}
	$content = strip_tags($content);
	echo esc_attr($content);
}
/* excerpt_title */
function excerpt_title ($excerpt_length,$excerpt_type = excerpt_type) {
	global $post;
	$excerpt_length = (isset($excerpt_length) && $excerpt_length != ""?$excerpt_length:5);
	$title = $post->post_title;
	if ($excerpt_type == "characters") {
		$title = mb_substr($title,0,$excerpt_length,"UTF-8");
	}else {
		$words = explode(' ',$title,$excerpt_length + 1);
		if (count($words) > $excerpt_length) :
			array_pop($words);
			array_push($words,'');
			$title = implode(' ',$words);
		endif;
	}
	$title = strip_tags($title);
	echo esc_attr($title);
}
/* excerpt_any */
function excerpt_any($excerpt_length,$title,$excerpt_type = excerpt_type) {
	$excerpt_length = (isset($excerpt_length) && $excerpt_length != ""?$excerpt_length:5);
	if ($excerpt_type == "characters") {
		$title = mb_substr($title,0,$excerpt_length,"UTF-8");
	}else {
		$words = explode(' ',$title,$excerpt_length + 1);
		if (count($words) > $excerpt_length) :
			array_pop($words);
			array_push($words,'');
			$title = implode(' ',$words).'...';
		endif;
			$title = strip_tags($title);
	}
	return $title;
}
/* add post-thumbnails */
add_theme_support('post-thumbnails');
/* askme_resize_url */
function askme_resize_url ($img_width_f,$img_height_f,$thumbs = "",$gif = "no") {
	global $post;
	if (empty($thumbs)) {
		$thumb = get_post_thumbnail_id((isset($post->ID) && $post->ID > 0?$post->ID:""));
	}else {
		$thumb = $thumbs;
	}
	if ($thumb != "") {
		$full_image = wp_get_attachment_image_src($thumb,"full");
		$or_width = $full_image[1];
		$or_height = $full_image[2];
		$image = askme_resize( $thumb, '', $img_width_f, $img_height_f, true,$gif );
		if (isset($image['url']) && $img_width_f/$or_width <= 2) {
			$last_image = $image['url'];
		}else {
			$last_image = "https://placehold.it/".$img_width_f."x".$img_height_f;
		}
		if (isset($last_image) && $last_image != "") {
			return $last_image;
		}
	}else {
		return vpanel_image();
	}
}
/* askme_resize_img */
function askme_resize_img ($img_width_f,$img_height_f,$img_lightbox = "",$thumbs = "",$gif = "no",$title = "") {
	global $post;
	if (empty($thumbs)) {
		$thumb = get_post_thumbnail_id((isset($post->ID) && $post->ID > 0?$post->ID:""));
	}else {
		$thumb = $thumbs;
	}
	$last_image = askme_resize_url($img_width_f,$img_height_f,$thumb,$gif);
	
	if ($thumb != "") {
		if ($img_lightbox == "lightbox") {
			$img_url = wp_get_attachment_url($thumb,"full");
		}
	}else {
		$img_url = vpanel_image();
	}
	
	if (isset($last_image) && $last_image != "") {
		return ($img_lightbox == "lightbox"?"<a href='".esc_url($img_url)."'>":"")."<img alt='".(isset($title) && $title != ""?$title:get_the_title())."' width='".$img_width_f."' height='".$img_height_f."' src='".$last_image."'>".($img_lightbox == "lightbox"?"</a>":"");
	}
}
/* askme_resize_by_url */
function askme_resize_by_url ($url,$img_width_f,$img_height_f,$gif = "no") {
	$image = askme_resize( "", $url, $img_width_f, $img_height_f, true,$gif );
	if (isset($image['url'])) {
		$last_image = $image['url'];
	}else {
		$last_image = "https://placehold.it/".$img_width_f."x".$img_height_f;
	}
	return $last_image;
}
/* askme_resize_by_url_img */
function askme_resize_by_url_img ($url,$img_width_f,$img_height_f,$gif = "no",$title = "") {
	$last_image = askme_resize_by_url($url,$img_width_f,$img_height_f,$gif,$title);
	if (isset($last_image) && $last_image != "") {
		return "<img alt='".(isset($title) && $title != ""?$title:get_the_title())."' width='".$img_width_f."' height='".$img_height_f."' src='".$last_image."'>";
	}
}
/* askme_resize_img_full */
function askme_resize_img_full ($thumbnail_size,$title = "") {
	$thumb = get_post_thumbnail_id();
	if ($thumb != "") {
		$img_url = wp_get_attachment_url($thumb,$thumbnail_size);
		$image = $img_url;
		return "<img alt='".(isset($title) && $title != ""?$title:get_the_title())."' src='".$image."'>";
	}else {
		return "<img alt='".(isset($title) && $title != ""?$title:get_the_title())."' src='".askme_image()."'>";
	}
}
/* vpanel_image */
function vpanel_image() {
	global $post;
	ob_start();
	ob_end_clean();
	if (isset($post->post_content)) {
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i',$post->post_content,$matches);
		if (isset($matches[1][0])) {
			return $matches[1][0];
		}else {
			return false;
		}
	}
}
/* formatMoney */
function formatMoney($number,$fractional=false) {
    if ($fractional) {
        $number = sprintf('%.2f',$number);
    }
    while (true) {
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/','$1,$2',$number);
        if ($replaced != $number) {
            $number = $replaced;
        }else {
            break;
        }
    }
    return $number;
}
/* get_twitter_count */
function get_twitter_count ($twitter_username) {
	$count = get_transient('vpanel_twitter_followers');
	if ($count !== false) return $count;
	
	$count           = 0;
	$access_token    = get_option('vpanel_twitter_token');
	$consumer_key    = vpanel_options('twitter_consumer_key');
	$consumer_secret = vpanel_options('twitter_consumer_secret');
	if ($access_token == "") {
		$credentials = $consumer_key . ':' . $consumer_secret;
		$toSend 	 = base64_encode($credentials);
		
		$args = array(
			'method'      => 'POST',
			'httpversion' => '1.1',
			'blocking' 		=> true,
			'headers' 		=> array(
				'Authorization' => 'Basic ' . $toSend,
				'Content-Type' 	=> 'application/x-www-form-urlencoded;charset=UTF-8'
			),
			'body' 				=> array( 'grant_type' => 'client_credentials' )
		);
		
		add_filter('https_ssl_verify', '__return_false');
		$response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);
		
		$keys = json_decode(wp_remote_retrieve_body($response));
		
		if ( !empty($keys->access_token) ) {
			update_option('vpanel_twitter_token', $keys->access_token);
			$access_token = $keys->access_token;
		}
	}

	$args = array(
		'httpversion' => '1.1',
		'blocking' 		=> true,
		'timeout'     => 10,
		'headers'     => array('Authorization' => "Bearer $access_token")
	);
	
	add_filter('https_ssl_verify', '__return_false');
	$api_url  = "https://api.twitter.com/1.1/users/show.json?screen_name=$twitter_username";
	
	$get_request = wp_remote_get( $api_url , $args );
	$request = wp_remote_retrieve_body( $get_request );
	$request = @json_decode( $request , true );
	
	if ( !empty( $request['followers_count'] ) ) {
		$count = $request['followers_count'];
	}
	set_transient('vpanel_twitter_followers', $count, 60*60*24);
	return $count;
}
/* vpanel_counter_facebook */
function vpanel_counter_facebook ($page_id, $return = 'count') {
	$count = get_transient('vpanel_facebook_followers');
	$link = get_transient('vpanel_facebook_page_url');
	if ($return == 'link') {
		if ($link !== false) return $link;
	}else {
		if ($count !== false) return $count;
	}
	$count = 0;
	$link = '';
	$access_token = vpanel_options('facebook_access_token');
	$data = wp_remote_get('https://graph.facebook.com/v2.7/'.$page_id.'?fields=id,name,picture,fan_count,link&access_token='.$access_token);
	if (!is_wp_error($data)) {
		$json = json_decode( $data['body'], true );
		$count = intval($json['fan_count']);
		$link = $json['link'];
		set_transient('vpanel_facebook_followers', $count, 3600);
		set_transient('vpanel_facebook_page_url', $link, 3600);
	}
	if ($return == 'link') {
		return $link;
	}else {
		return $count;
	}
}
/* vpanel_counter_googleplus */
function vpanel_counter_googleplus ($page_id, $return = 'count') {
	$count = get_transient('vpanel_googleplus_followers');
	$link = get_transient('vpanel_googleplus_page_url');
	if ($return == 'link') {
		if ($link !== false) return $link;
	}else {
		if ($count !== false) return $count;
	}
	$count = 0;
	$link = '';
	$api_key = vpanel_options('google_api');
	$data = wp_remote_get('https://www.googleapis.com/plus/v1/people/'.$page_id.'?key='.$api_key);
	if (!is_wp_error($data)) {
		$json = json_decode( $data['body'], true );
		$count = isset($json['circledByCount']) ? intval($json['circledByCount']) : intval($json['plusOneCount']);
		$link = $json['url'];
		set_transient('vpanel_googleplus_followers', $count, 3600);
		set_transient('vpanel_googleplus_page_url', $link, 3600);
	}
	if ($return == 'link') {
		return $link;
	}else {
		return $count;
	}
}
/* vpanel_counter_youtube */
function vpanel_counter_youtube ($youtube, $return = 'count') {
	$count = get_transient('vpanel_youtube_followers');
	$api_key = vpanel_options('google_api');
	if ($count !== false) return $count;
	$count = 0;
	$data = wp_remote_get('https://www.googleapis.com/youtube/v3/channels?part=statistics&id='.$youtube.'&key='.$api_key);
	if (!is_wp_error($data)) {
		$json = json_decode( $data['body'], true );
		$count = intval($json['items'][0]['statistics']['subscriberCount']);
		set_transient('vpanel_youtube_followers', $count, 3600);
	}
	return $count;
}
/* vpanel_twitter_tweets */
if ( ! function_exists( 'vpanel_twitter_tweets' ) ) :
	function vpanel_twitter_tweets($username = '', $tweets_count = 3) {
		$twitter_data    = "";
		$access_token    = get_option('vpanel_twitter_token');
		$consumer_key    = vpanel_options('twitter_consumer_key');
		$consumer_secret = vpanel_options('twitter_consumer_secret');
		if ($access_token == "") {
			$credentials = $consumer_key . ':' . $consumer_secret;
			$toSend 	 = base64_encode($credentials);
			
			$args = array(
				'method'      => 'POST',
				'httpversion' => '1.1',
				'blocking' 		=> true,
				'headers' 		=> array(
					'Authorization' => 'Basic ' . $toSend,
					'Content-Type' 	=> 'application/x-www-form-urlencoded;charset=UTF-8'
				),
				'body' 				=> array( 'grant_type' => 'client_credentials' )
			);
			
			add_filter('https_ssl_verify', '__return_false');
			$response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);
			
			$keys = json_decode(wp_remote_retrieve_body($response));
			
			if ( !empty($keys->access_token) ) {
				update_option('vpanel_twitter_token', $keys->access_token);
				$access_token = $keys->access_token;
			}
		}
		
		$args = array(
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
			'Authorization' => "Bearer $access_token",
		));
		
		add_filter('https_ssl_verify', '__return_false');
		
		$api_url = "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=$username&count=$tweets_count";
		$response = wp_remote_get( $api_url, $args );
		
		if ( ! is_wp_error( $response )) {
			$twitter_data = json_decode(wp_remote_retrieve_body($response));
		}

		return $twitter_data;
	}
endif;
/* Vpanel_Questions */
function Vpanel_Questions($questions_per_page = 5,$orderby,$display_date,$questions_excerpt,$post_or_question,$excerpt_title = 5,$display_image = "on",$display_author = "") {
	global $post;
	$date_format = (vpanel_options("date_format")?vpanel_options("date_format"):get_option("date_format"));
	$excerpt_title = ($excerpt_title != ""?$excerpt_title:5);
	$orderby_array = array();
	if ($orderby == "popular") {
		$orderby_array = array('orderby' => 'comment_count');
	}else if ($orderby == "random") {
		$orderby_array = array('orderby' => 'rand');
	}
	
	if ($orderby == "no_response") {
		add_filter('posts_where', 'ask_filter_where');
	}
	$query = new WP_Query(array_merge($orderby_array,array('post_type' => $post_or_question,'ignore_sticky_posts' => 1,'posts_per_page' => $questions_per_page,'cache_results' => false,'no_found_rows' => true,"meta_query" => array(array("key" => "user_id","compare" => "NOT EXISTS")))));
	if ( $query->have_posts() ) : 
		echo "<ul class='related-posts'>";
			while ( $query->have_posts() ) : $query->the_post();
				if ($post_or_question == "question") {
					$yes_private = ask_private($post->ID,$post->post_author,get_current_user_id());
				}else {
					$yes_private = 1;
				}
				if ($yes_private == 1) {?>
					<li class="related-item">
						<?php if (has_post_thumbnail() && $display_image == "on") {?>
							<div class="author-img">
								<a href="<?php the_permalink();?>" title="<?php printf('%s',the_title_attribute('echo=0')); ?>" rel="bookmark">
									<?php echo askme_resize_img(60,60);?>
								</a>
							</div>
						<?php }?>
						<div class="questions-div">
							<h3>
								<a href="<?php the_permalink();?>" title="<?php printf('%s',the_title_attribute('echo=0')); ?>" rel="bookmark">
									<?php if ($questions_excerpt == 0) {?>
										<i class="icon-double-angle-right"></i>
									<?php }
									excerpt_title($excerpt_title);?>
								</a>
							</h3>
							<?php if ($questions_excerpt != 0) {?>
								<p><?php excerpt($questions_excerpt);?></p>
							<?php }
							if ($display_date == "on") {?>
								<div class="clear"></div><span <?php echo ($questions_excerpt == 0?"class='margin_t_5'":"")?>><?php the_time($date_format);?></span>
							<?php }
							if ($display_author == "on") {?>
								<div class="clear"></div>
								<span class="question-meta-author<?php echo ($questions_excerpt == 0?" margin_t_5":"")?>">
									<?php if ($post->post_author == 0) {
										$anonymously_user = get_post_meta($post->ID,'anonymously_user',true);
										$anonymously_question = get_post_meta($post->ID,'anonymously_question',true);
										if ($anonymously_question == 1 && $anonymously_user != "") {
											$question_username = esc_html__("Anonymous","vbegy");
											$question_email = 0;
										}else {
											$question_username = get_post_meta($post->ID, 'question_username',true);
											$question_email = get_post_meta($post->ID, 'question_email',true);
											$question_username = ($question_username != ""?$question_username:esc_html__("Anonymous","vbegy"));
											$question_email = ($question_email != ""?$question_email:0);
										}?>
										<i class="icon-user"></i><span><?php echo $question_username?></span>
									<?php }else {?>
										<a href="<?php echo vpanel_get_user_url($post->post_author)?>"><i class="icon-user"></i><?php echo get_the_author()?></a>
										<?php do_action("askme_badge_widget_posts",$post->post_author);
									}?>
								</span>
							<?php }?>
						</div>
					</li>
				<?php }
			endwhile;
		echo "</ul>";
	endif;
	if ($orderby == "no_response") {
		remove_filter( 'posts_where', 'ask_filter_where' );
	}
	wp_reset_postdata();
}
/* Vpanel_comments */
function Vpanel_comments($post_or_question = "question",$comments_number = 5,$comment_excerpt = 30) {
	$comments = get_comments(array("post_type" => $post_or_question,"status" => "approve","number" => $comments_number,"meta_query" => array(array("key" => "answer_question_user","compare" => "NOT EXISTS"))));
	echo "<div class='widget_highest_points widget_comments'><ul>";
		foreach ($comments as $comment) {
			$you_avatar = get_the_author_meta('you_avatar',$comment->user_id);
			$user_profile_page = vpanel_get_user_url($comment->user_id);
			if ($post_or_question == "question") {
				$yes_private = ask_private($comment->comment_post_ID,get_post($comment->comment_post_ID)->post_author,get_current_user_id());
				$yes_private_answer = ask_private_answer($comment->comment_ID,$comment->user_id,get_current_user_id());
			}else {
				$yes_private = 1;
				$yes_private_answer = 1;
			}
		    if ($yes_private == 1 && $yes_private_answer == 1) {?>
			    <li>
			    	<div class="author-img">
			    		<?php if ($comment->user_id != 0) {?>
				    		<a href="<?php echo $user_profile_page?>" original-title="<?php echo strip_tags($comment->comment_author);?>" class="tooltip-n">
		    			<?php }
		    				echo askme_user_avatar($you_avatar,65,65,$comment->user_id,$comment->comment_author);
			    		if ($comment->user_id != 0) {?>
				    		</a>
			    		<?php }?>
			    	</div> 
			    	<h6><a href="<?php echo get_permalink($comment->comment_post_ID);?>#comment-<?php echo $comment->comment_ID;?>"><?php echo strip_tags($comment->comment_author);?> : <?php echo wp_html_excerpt($comment->comment_content,$comment_excerpt);?></a></h6>
			    </li>
		    <?php }
		}
	echo "</ul></div>";
}
if (!function_exists('vbegy_comment')) {
	/* vbegy_comment */
	function vbegy_comment($comment,$args,$depth) {
		global $k;
		$k++;
	    $GLOBALS['comment'] = $comment;
	    $add_below = '';
	    $comment_id = esc_attr($comment->comment_ID);
	    $user_get_current_user_id = get_current_user_id();
	    $can_delete_comment = vpanel_options("can_delete_comment");
	    $can_edit_comment = vpanel_options("can_edit_comment");
	    $can_edit_comment_after = vpanel_options("can_edit_comment_after");
	    $can_edit_comment_after = (int)(isset($can_edit_comment_after) && $can_edit_comment_after > 0?$can_edit_comment_after:0);
	    if (version_compare(phpversion(), '5.3.0', '>')) {
	    	$time_now = strtotime(current_time( 'mysql' ),date_create_from_format('Y-m-d H:i',current_time( 'mysql' )));
	    }else {
	    	list($year, $month, $day, $hour, $minute, $second) = sscanf(current_time( 'mysql' ), '%04d-%02d-%02d %02d:%02d:%02d');
	    	$datetime = new DateTime("$year-$month-$day $hour:$minute:$second");
	    	$time_now = strtotime($datetime->format('r'));
	    }
	    $time_edit_comment = strtotime('+'.$can_edit_comment_after.' hour',strtotime($comment->comment_date));
	    $time_end = ($time_now-$time_edit_comment)/60/60;
	    $edit_comment = get_comment_meta($comment_id,"edit_comment",true);
	    if (isset($k) && $k == vpanel_options("between_comments_position")) {
	    	$between_adv_type = vpanel_options("between_comments_adv_type");
	    	$between_adv_code = vpanel_options("between_comments_adv_code");
	    	$between_adv_href = vpanel_options("between_comments_adv_href");
	    	$between_adv_img = vpanel_options("between_comments_adv_img");
	    	if (($between_adv_type == "display_code" && $between_adv_code != "") || ($between_adv_type == "custom_image" && $between_adv_img != "")) {
	    		echo '<li class="advertising">
	    			<div class="clearfix"></div>';
	    		if ($between_adv_type == "display_code") {
	    			echo stripcslashes(do_shortcode($between_adv_code));
	    		}else {
	    			if ($between_adv_href != "") {
	    				echo '<a target="_blank" href="'.$between_adv_href.'">';
	    			}
	    			echo '<img alt="" src="'.$between_adv_img.'">';
	    			if ($between_adv_href != "") {
	    				echo '</a>';
	    			}
	    		}
	    		echo '<div class="clearfix"></div>
	    		</li><!-- End advertising -->';
	    	}
	    }
	    ?>
	    <li <?php comment_class('comment');?> id="li-comment-<?php comment_ID();?>">
	    	<div id="comment-<?php comment_ID();?>" class="comment-body clearfix">
	            <div class="avatar-img">
	            	<?php if ($comment->user_id != 0){
	            		$vpanel_get_user_url = vpanel_get_user_url($comment->user_id,get_the_author_meta('nickname', $comment->user_id));
	            		if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
	            			<a original-title="<?php echo strip_tags($comment->comment_author);?>" class="tooltip-n" href="<?php echo esc_url($vpanel_get_user_url)?>">
	            		<?php }
	            		echo askme_user_avatar(get_the_author_meta('you_avatar', $comment->user_id),65,65,$comment->user_id,$comment->comment_author);
	            		if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
	            			</a>
	            		<?php }
	            	}else {
	            		$vpanel_get_user_url = ($comment->comment_author_url != ""?$comment->comment_author_url:"vpanel_No_site");
	            		echo get_avatar($comment->comment_author_email,65);
	            	}?>
	            </div>
	            <div class="comment-text">
	                <div class="author clearfix">
	                	<div class="comment-meta">
	        	        	<div class="comment-author">
	        	        		<?php if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
		        	        		<a href="<?php echo esc_url($vpanel_get_user_url)?>">
		        	        	<?php }
		        	        		echo get_comment_author();
		        	        	if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
		        	        		</a>
		        	        	<?php }
		        	        	if ($comment->user_id != 0) {
			        	        	$verified_user = get_the_author_meta('verified_user',$comment->user_id);
			        	        	if ($verified_user == 1) {
			        	        		echo '<img class="verified_user tooltip-n" alt="'.__("Verified","vbegy").'" original-title="'.__("Verified","vbegy").'" src="'.get_template_directory_uri().'/images/verified.png">';
			        	        	}
		        	        		echo vpanel_get_badge($comment->user_id);
		        	        	}?>
	        	        	</div>
	                        <a href="<?php echo get_permalink($comment->comment_post_ID);?>#comment-<?php echo esc_attr($comment->comment_ID); ?>" class="date"><i class="fa fa-calendar"></i><?php printf(__('%1$s at %2$s','vbegy'),get_comment_date(), get_comment_time()) ?></a> 
	                    </div>
	                    <div class="comment-reply">
	                    <?php if (current_user_can('edit_comment',$comment_id) || ($can_edit_comment == 1 && $comment->user_id == get_current_user_id() && $comment->user_id != 0 && get_current_user_id() != 0 && ($can_edit_comment_after == 0 || $time_end <= $can_edit_comment_after))) {
	                    	echo "<a class='comment-edit-link edit-comment' href='".esc_url(add_query_arg("comment_id", $comment_id,get_page_link(vpanel_options('edit_comment'))))."'><i class='icon-pencil'></i>".__("Edit","vbegy")."</a>";
	                    }
	                    if (($can_delete_comment == 1 && $comment->user_id == get_current_user_id() && $comment->user_id > 0 && get_current_user_id() > 0) || current_user_can('edit_comment',$comment_id) || is_super_admin(get_current_user_id())) {
	                    	echo "<a class='comment-delete-link delete-comment' href='".esc_url(add_query_arg(array('delete_comment' => $comment_id),get_permalink($comment->comment_post_ID)))."'><i class='icon-trash'></i>".__("Delete","vbegy")."</a>";
	                    }
	                    comment_reply_link( array_merge( $args, array( 'reply_text' => '<i class="icon-reply"></i>'.__( 'Reply', 'vbegy' ), 'after' => '', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) );?>
	                    </div>
	                </div>
	                <div class="text">
	                	<?php if ($edit_comment == "edited") {?>
	                		<em><?php _e('This comment is edited.','vbegy')?></em><br>
	                	<?php }
	                	if ($comment->comment_approved == '0') : ?>
	                	    <em><?php _e('Your comment is awaiting moderation.','vbegy')?></em><br>
	                	<?php endif;
	                	do_shortcode(comment_text());?>
	                </div>
	            </div>
	        </div>
	    <?php
	}
}
if (!function_exists('vbegy_answer')) {
	/* vbegy_answer */
	function vbegy_answer($comment,$args,$depth) {
		global $post,$k;
		$k++;
		$GLOBALS['comment'] = $comment;
		$add_below = '';
		$comment_id = esc_attr($comment->comment_ID);
		$user_get_current_user_id = get_current_user_id();
		$yes_private_answer = ask_private_answer($comment_id,$comment->user_id,$user_get_current_user_id);
		$comment_vote = get_comment_meta($comment_id,'comment_vote',true);
		if (isset($comment_vote) && is_array($comment_vote) && isset($comment_vote["vote"])) {
			update_comment_meta($comment_id,'comment_vote',$comment_vote["vote"]);
			$comment_vote = get_comment_meta($comment_id,'comment_vote',true);
		}else if ($comment_vote == "") {
			update_comment_meta($comment_id,'comment_vote',0);
			$comment_vote = get_comment_meta($comment_id,'comment_vote',true);
		}
		$the_best_answer = get_post_meta($post->ID,"the_best_answer",true);
		$best_answer_comment = get_comment_meta($comment_id,"best_answer_comment",true);
		$comment_best_answer = ($best_answer_comment == "best_answer_comment" || $the_best_answer == $comment_id?"comment-best-answer":"");
		$active_reports = vpanel_options("active_reports");
		$active_logged_reports = vpanel_options("active_logged_reports");
		$active_vote = vpanel_options("active_vote");
		$can_delete_comment = vpanel_options("can_delete_comment");
		$can_edit_comment = vpanel_options("can_edit_comment");
		$can_edit_comment_after = vpanel_options("can_edit_comment_after");
		$can_edit_comment_after = (int)(isset($can_edit_comment_after) && $can_edit_comment_after > 0?$can_edit_comment_after:0);
		if (version_compare(phpversion(), '5.3.0', '>')) {
			$time_now = strtotime(current_time( 'mysql' ),date_create_from_format('Y-m-d H:i',current_time( 'mysql' )));
		}else {
			list($year, $month, $day, $hour, $minute, $second) = sscanf(current_time( 'mysql' ), '%04d-%02d-%02d %02d:%02d:%02d');
			$datetime = new DateTime("$year-$month-$day $hour:$minute:$second");
			$time_now = strtotime($datetime->format('r'));
		}
		$time_edit_comment = strtotime('+'.$can_edit_comment_after.' hour',strtotime($comment->comment_date));
		$time_end = ($time_now-$time_edit_comment)/60/60;
		$edit_comment = get_comment_meta($comment_id,"edit_comment",true);
		if (isset($k) && $k == vpanel_options("between_comments_position")) {
			$between_adv_type = vpanel_options("between_comments_adv_type");
			$between_adv_code = vpanel_options("between_comments_adv_code");
			$between_adv_href = vpanel_options("between_comments_adv_href");
			$between_adv_img = vpanel_options("between_comments_adv_img");
			if (($between_adv_type == "display_code" && $between_adv_code != "") || ($between_adv_type == "custom_image" && $between_adv_img != "")) {
				echo '<li class="advertising">
					<div class="clearfix"></div>';
				if ($between_adv_type == "display_code") {
					echo stripcslashes(do_shortcode($between_adv_code));
				}else {
					if ($between_adv_href != "") {
						echo '<a target="_blank" href="'.$between_adv_href.'">';
					}
					echo '<img alt="" src="'.$between_adv_img.'">';
					if ($between_adv_href != "") {
						echo '</a>';
					}
				}
				echo '<div class="clearfix"></div>
				</li><!-- End advertising -->';
			}
		}
		if ($yes_private_answer != 1) {?>
			<li class="comment byuser comment ">
				<div class="comment-body clearfix" rel="post-<?php echo $post->ID?>">
					<div class="comment-text">
						<div class="text">
							<p><?php _e("Sorry it a private answer.","vbegy");?></p>
						</div>
					</div>
				</div>
		<?php }else {?>
		    <li <?php comment_class('comment '.$comment_best_answer);?> id="li-comment-<?php comment_ID();?>">
		    	<div<?php echo ($best_answer_comment == "best_answer_comment" || $the_best_answer == $comment_id?" itemprop='acceptedAnswer'":"")?> id="comment-<?php comment_ID();?>" class="comment-body clearfix" rel="post-<?php echo $post->ID?>" itemscope itemtype="http://schema.org/Answer">
		    	    <div class="avatar-img">
		    	    	<?php if ($comment->user_id != 0) {
		    	    		$vpanel_get_user_url = vpanel_get_user_url($comment->user_id,get_the_author_meta('nickname', $comment->user_id));
		    	    		if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
		    	    			<a original-title="<?php echo strip_tags($comment->comment_author);?>" class="tooltip-n" href="<?php echo esc_url($vpanel_get_user_url)?>">
		    	    		<?php }
		    	    		echo askme_user_avatar(get_the_author_meta('you_avatar', $comment->user_id),65,65,$comment->user_id,$comment->comment_author);
		    	    		if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
		    	    			</a>
		    	    		<?php }
		    	    	}else {
		    	    		$vpanel_get_user_url = ($comment->comment_author_url != ""?$comment->comment_author_url:"vpanel_No_site");
		    	    		echo get_avatar($comment,65);
		    	    	}?>
		    	    </div>
		    	    <div class="comment-text">
		    	        <div class="author clearfix">
		    	        	<div class="comment-author" itemprop="author" itemscope itemtype="http://schema.org/Person">
		    	        		<?php if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
		    	        			<a href="<?php echo esc_url($vpanel_get_user_url)?>">
		    	        		<?php }
		    	        			echo '<span itemprop="name">'.get_comment_author().'</span>';
		    	        		if ($vpanel_get_user_url != "" && $vpanel_get_user_url != "vpanel_No_site") {?>
		    	        			</a>
		    	        		<?php }
		    	        		if ($comment->user_id != 0) {
			    	        		$verified_user = get_the_author_meta('verified_user',$comment->user_id);
			    	        		if ($verified_user == 1) {
			    	        			echo '<img class="verified_user tooltip-n" alt="'.__("Verified","vbegy").'" original-title="'.__("Verified","vbegy").'" src="'.get_template_directory_uri().'/images/verified.png">';
			    	        		}
		    	        			echo vpanel_get_badge($comment->user_id);
		    	        		}?>
		    	        	</div>
		    	        	<?php if ($active_vote == 1) {
		    	        		$show_dislike_answers = vpanel_options("show_dislike_answers");?>
			    	        	<div class="comment-vote">
			    	            	<ul class="single-question-vote">
			    	            		<?php if (is_user_logged_in && $comment->user_id != $user_get_current_user_id){?>
			    	            			<li class="loader_3"></li>
			    	            			<li><a href="#" class="single-question-vote-up ask_vote_up comment_vote_up vote_allow<?php echo (isset($_COOKIE['comment_vote'.$comment_id])?" ".$_COOKIE['comment_vote'.$comment_id]."-".$comment_id:"")?>" title="<?php _e("Like","vbegy");?>" id="comment_vote_up-<?php echo $comment_id?>"><i class="icon-thumbs-up"></i></a></li>
			    	            			<?php if ($show_dislike_answers != 1) {?>
			    	            				<li><a href="#" class="single-question-vote-down ask_vote_down comment_vote_down vote_allow<?php echo (isset($_COOKIE['comment_vote'.$comment_id])?" ".$_COOKIE['comment_vote'.$comment_id]."-".$comment_id:"")?>" id="comment_vote_down-<?php echo $comment_id?>" title="<?php _e("Dislike","vbegy");?>"><i class="icon-thumbs-down"></i></a></li>
			    	            			<?php }
			    	            		}else { ?>
			    	            			<li class="loader_3"></li>
			    	            			<li><a href="#" class="single-question-vote-up ask_vote_up comment_vote_up <?php echo (is_user_logged_in && $comment->user_id == $user_get_current_user_id?"vote_not_allow":"vote_not_user")?>" title="<?php _e("Like","vbegy");?>"><i class="icon-thumbs-up"></i></a></li>
			    	            			<?php if ($show_dislike_answers != 1) {?>
			    	            				<li><a href="#" class="single-question-vote-down ask_vote_down comment_vote_down <?php echo (is_user_logged_in && $comment->user_id == $user_get_current_user_id?"vote_not_allow":"vote_not_user")?>" title="<?php _e("Dislike","vbegy");?>"><i class="icon-thumbs-down"></i></a></li>
			    	            			<?php }
			    	            		}?>
			    	            	</ul>
			    	        	</div>
			    	        	<span itemprop="upvoteCount" class="question-vote-result question_vote_result<?php echo ($comment_vote < 0?" question_vote_red":"")?>"><?php echo ($comment_vote != ""?$comment_vote:0)?></span>
		    	        	<?php }?>
		    	        	<div class="comment-meta" itemprop="dateCreated" datetime="<?php echo get_comment_date()?>">
		    	                <a href="<?php echo get_permalink($comment->comment_post_ID);?>#comment-<?php echo esc_attr($comment->comment_ID); ?>" class="date"><i class="fa fa-calendar"></i><?php printf(__('%1$s at %2$s','vbegy'),get_comment_date(), get_comment_time()) ?></a> 
		    	            </div>
		    	            <div class="comment-reply">
			    	            <?php if (current_user_can('edit_comment',$comment_id) || ($can_edit_comment == 1 && $comment->user_id == $user_get_current_user_id && $comment->user_id != 0 && $user_get_current_user_id != 0 && ($can_edit_comment_after == 0 || $time_end <= $can_edit_comment_after))) {
			    	            	echo "<a class='comment-edit-link edit-comment' href='".esc_url(add_query_arg("comment_id", $comment_id,get_page_link(vpanel_options('edit_comment'))))."'><i class='icon-pencil'></i>".__("Edit","vbegy")."</a>";
			    	            }
			    	            if (($can_delete_comment == 1 && $comment->user_id == $user_get_current_user_id && $comment->user_id > 0 && $user_get_current_user_id > 0) || current_user_can('edit_comment',$comment_id) || is_super_admin($user_get_current_user_id)) {
			    	            	echo "<a class='comment-delete-link delete-comment delete-answer' href='".esc_url(add_query_arg(array('delete_comment' => $comment_id),get_permalink($comment->comment_post_ID)))."'><i class='icon-trash'></i>".__("Delete","vbegy")."</a>";
			    	            }
			    	            if ($active_reports == 1 && (is_user_logged_in || (!is_user_logged_in && $active_logged_reports != 1))) {?>
		    	                	<a class="question_r_l comment_l report_c" href="#"><i class="icon-flag"></i><?php _e("Report","vbegy")?></a>
		    	                <?php }
		    	                comment_reply_link( array_merge( $args, array( 'reply_text' => '<i class="icon-reply"></i>'.__( 'Reply', 'vbegy' ),'login_text' => '<i class="icon-lock"></i>'.__( 'Log in to Reply', 'vbegy' ), 'after' => '', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) );?>
		    	            </div>
		    	        </div>
		    	        <div class="text">
		    	        	<?php if ($active_reports == 1 && (is_user_logged_in || (!is_user_logged_in && $active_logged_reports != 1))) {?>
			    	        	<div class="explain-reported">
			    	        		<h3><?php _e("Please briefly explain why you feel this answer should be reported .","vbegy")?></h3>
			    	        		<textarea name="explain-reported"></textarea>
			    	        		<div class="clearfix"></div>
			    	        		<div class="loader_3"></div>
			    	        		<div class="color button small report"><?php _e("Report","vbegy")?></div>
			    	        		<div class="color button small dark_button cancel"><?php _e("Cancel","vbegy")?></div>
			    	        	</div><!-- End reported -->
		    	        	<?php }
		    	        	if ($edit_comment == "edited") {?>
		    	        		<em><?php _e('This answer is edited.','vbegy')?></em><br>
		    	        	<?php }
		    	        	if ($comment->comment_approved == '0') : ?>
		    	        	    <em><?php _e('Your answer is awaiting moderation.','vbegy')?></em><br>
		    	        	<?php endif;
		    	        	$featured_image_question_answers = vpanel_options("featured_image_question_answers");
		    	        	if ($featured_image_question_answers == 1) {
		    	        		$featured_image = get_comment_meta($comment_id,'featured_image',true);
		    	        		if (wp_get_attachment_image_srcset($featured_image)) {
		    	        			$img_url = wp_get_attachment_url($featured_image,"full");
		    	        			$featured_image_answers_lightbox = vpanel_options("featured_image_answers_lightbox");
		    	        			$featured_image_answer_width = vpanel_options("featured_image_answer_width");
		    	        			$featured_image_answer_height = vpanel_options("featured_image_answer_height");
		    	        			$featured_image_answer_width = ($featured_image_answer_width != ""?$featured_image_answer_width:260);
		    	        			$featured_image_answer_height = ($featured_image_answer_height != ""?$featured_image_answer_height:185);
		    	        			$link_url = ($featured_image_answers_lightbox == 1?$img_url:get_permalink($comment->comment_post_ID)."#comment-".$comment->comment_ID);
		    	        			$featured_answer_position = vpanel_options("featured_answer_position");
		    	        			if ($featured_answer_position != "after") {
		    	        	    		echo "<div class='featured_image_answer'><a href='".$link_url."'>".askme_resize_img($featured_image_answer_width,$featured_image_answer_height,"",$featured_image)."</a></div>
		    	        	    		<div class='clearfix'></div>";
		    	        			}
		    	        		}
		    	        	}?>
		    	        	<div itemprop="text"><?php do_shortcode(comment_text());?></div>
		    	        	<?php if ($featured_image_question_answers && wp_get_attachment_image_srcset($featured_image) && $featured_answer_position == "after") {
		    	        		echo "<div class='featured_image_question featured_image_after'><a href='".$link_url."'>".askme_resize_img($featured_image_answer_width,$featured_image_answer_height,"",$featured_image)."</a></div>
		    	        		<div class='clearfix'></div>";
		    	        	}
		    	        	
		    	        	$added_file = get_comment_meta($comment_id,'added_file', true);
		    	        	if ($added_file != "") {
		    	        		echo "<div class='clearfix'></div><br><a href='".wp_get_attachment_url($added_file)."'>".__("Attachment","vbegy")."</a>";
		    	        	}
		    	        	?>
		    	        </div>
		    	        <div class="clearfix"></div>
			        	<div class="loader_3"></div>
		    	        <?php
		    	        $user_best_answer = esc_attr(get_the_author_meta('user_best_answer',$user_get_current_user_id));
		    	        if ($best_answer_comment == "best_answer_comment" || $the_best_answer == $comment_id) {
		    	        	echo '<div class="commentform question-answered question-answered-done"><i class="icon-ok"></i>'.__("Best answer","vbegy").'</div>
		    	        	<div class="clearfix"></div>';
		    	        	if (((is_user_logged_in && $user_get_current_user_id == $post->post_author) || (isset($user_best_answer) && $user_best_answer == 1) || (is_super_admin($user_get_current_user_id))) && $the_best_answer != 0){
			    	        	echo '<a class="commentform best_answer_re question-report" title="'.__("Cancel the best answer","vbegy").'" href="#">'.__("Cancel the best answer","vbegy").'</a>';
		    	        	}
		    	        }
		    	        if (((is_user_logged_in && $user_get_current_user_id == $post->post_author) || (isset($user_best_answer) && $user_best_answer == 1) || (is_super_admin($user_get_current_user_id))) && ($the_best_answer == 0 || $the_best_answer == "")){?>
		    	        	<a class="commentform best_answer_a question-report" title="<?php _e("Select as best answer","vbegy");?>" href="#"><?php _e("Select as best answer","vbegy");?></a>
		    	        <?php
		    	        }
		    	        ?>
		    	        <div class="no_vote_more"></div>
		    	    </div>
		    	</div>
			<?php
		}
	}
}
/* vpanel_pagination */
if ( ! function_exists('vpanel_pagination')) {
	function vpanel_pagination( $args = array(),$query = '') {
		global $wp_rewrite,$wp_query;
		do_action('vpanel_pagination_start');
		if ( $query) {
			$wp_query = $query;
		} // End IF Statement
		/* If there's not more than one page,return nothing. */
		if ( 1 >= $wp_query->max_num_pages)
			return;
		/* Get the current page. */
		$current = (get_query_var("paged") != ""?(int)get_query_var("paged"):(get_query_var("page") != ""?(int)get_query_var("page"):1));
		$page_what = (get_query_var("paged") != ""?"paged":(get_query_var("page") != ""?"page":"paged"));
		/* Get the max number of pages. */
		$max_num_pages = intval( $wp_query->max_num_pages);
		/* Set up some default arguments for the paginate_links() function. */
		$defaults = array(
			'base' => esc_url(add_query_arg($page_what,'%#%')),
			'format' => '',
			'total' => $max_num_pages,
			'current' => $current,
			'prev_next' => true,
			'prev_text' => __('<i class="icon-angle-left"></i>','vbegy'),// Translate in WordPress. This is the default.
			'next_text' => __('<i class="icon-angle-right"></i>','vbegy'),// Translate in WordPress. This is the default.
			'show_all' => false,
			'end_size' => 1,
			'mid_size' => 1,
			'add_fragment' => '',
			'type' => 'plain',
			'before' => '<div class="pagination">',// Begin vpanel_pagination() arguments.
			'after' => '</div>',
			'echo' => true,
		);
		/* Add the $base argument to the array if the user is using permalinks. */
		if ( $wp_rewrite->using_permalinks())
			$defaults['base'] = user_trailingslashit( trailingslashit( get_pagenum_link()) . 'page/%#%');
		/* If we're on a search results page,we need to change this up a bit. */
		if ( is_search()) {
		/* If we're in BuddyPress,use the default "unpretty" URL structure. */
			if ( class_exists('BP_Core_User')) {
				$search_query = get_query_var('s');
				$paged = get_query_var('paged');
				$base = user_trailingslashit( home_url()) . '?s=' . $search_query . '&paged=%#%';
				$defaults['base'] = $base;
			} else {
				$search_permastruct = $wp_rewrite->get_search_permastruct();
				if ( !empty( $search_permastruct))
					$defaults['base'] = user_trailingslashit( trailingslashit( get_search_link()) . 'page/%#%');
			}
		}
		/* Merge the arguments input with the defaults. */
		$args = wp_parse_args( $args,$defaults);
		/* Allow developers to overwrite the arguments with a filter. */
		$args = apply_filters('vpanel_pagination_args',$args);
		/* Don't allow the user to set this to an array. */
		if ('array' == $args['type'])
			$args['type'] = 'plain';
		/* Make sure raw querystrings are displayed at the end of the URL,if using pretty permalinks. */
		$pattern = '/\?(.*?)\//i';
		preg_match( $pattern,$args['base'],$raw_querystring);
		if ( $wp_rewrite->using_permalinks() && $raw_querystring)
			$raw_querystring[0] = str_replace('','',$raw_querystring[0]);
			if (!empty($raw_querystring)) {
				@$args['base'] = str_replace( $raw_querystring[0],'',$args['base']);
				@$args['base'] .= substr( $raw_querystring[0],0,-1);
			}
		/* Get the paginated links. */
		$page_links = paginate_links( $args);
		/* Remove 'page/1' from the entire output since it's not needed. */
		$page_links = str_replace( array('&#038;paged=1\'','/page/1\''),'\'',$page_links);
		/* Wrap the paginated links with the $before and $after elements. */
		$page_links = $args['before'] . $page_links . $args['after'];
		/* Allow devs to completely overwrite the output. */
		$page_links = apply_filters('vpanel_pagination',$page_links);
		do_action('vpanel_pagination_end');
		/* Return the paginated links for use in themes. */
		if ( $args['echo'])
			echo $page_links;
		else
			return $page_links;
	}
}
/* askme_admin_bar_menu */
add_action('admin_bar_menu', 'askme_admin_bar_menu', 70 );
function askme_admin_bar_menu( $wp_admin_bar ) {
	if (is_super_admin()) {
		$answers_count = get_all_comments_of_post_type("question");
		if ($answers_count > 0) {
			$wp_admin_bar->add_node( array(
				'parent' => 0,
				'id' => 'answers',
				'title' => '<span class="ab-icon dashicons-before dashicons-format-chat"></span><span class=" count-'.$answers_count.'"><span class="">'.$answers_count.'</span></span>' ,
				'href' => admin_url( 'edit-comments.php?comment_status=all&answers=1')
			));
		}
	}
}
/* vpanel_admin_bar */
function vpanel_admin_bar() {
	global $wp_admin_bar;
	if (is_super_admin()) {
		$count_questions_by_type = count_posts_by_type( "question", "draft" );
		if ($count_questions_by_type > 0) {
			$wp_admin_bar->add_menu( array(
				'parent' => 0,
				'id' => 'questions_draft',
				'title' => '<span class="ab-icon dashicons-before dashicons-editor-help"></span><span class=" count-'.$count_questions_by_type.'"><span class="">'.$count_questions_by_type.'</span></span>' ,
				'href' => admin_url( 'edit.php?post_status=draft&post_type=question')
			));
		}
		$count_posts_by_type = count_posts_by_type( "post", "draft" );
		if ($count_posts_by_type > 0) {
			$wp_admin_bar->add_menu( array(
				'parent' => 0,
				'id' => 'posts_draft',
				'title' => '<span class="ab-icon dashicons-before dashicons-media-text"></span><span class=" count-'.$count_posts_by_type.'"><span class="">'.$count_posts_by_type.'</span></span>' ,
				'href' => admin_url( 'edit.php?post_status=draft&post_type=post')
			));
		}
		$pay_ask = vpanel_options("pay_ask");
		if ($pay_ask == 1) {
			$new_payments = get_option("new_payments");
			$wp_admin_bar->add_menu( array(
				'parent' => 0,
				'id' => 'new_payments',
				'title' => '<span class="ab-icon dashicons-before dashicons-cart"></span><span class=" count-'.$new_payments.'"><span class="">'.$new_payments.'</span></span>' ,
				'href' => admin_url( 'admin.php?page=ask_payments')
			));
		}
		$count_messages_by_type = count_posts_by_type( "message", "draft" );
		if ($count_messages_by_type > 0) {
			$wp_admin_bar->add_menu( array(
				'parent' => 0,
				'id' => 'messages_draft',
				'title' => '<span class="ab-icon dashicons-before dashicons-email-alt"></span><span class=" count-'.$count_messages_by_type.'"><span class="">'.$count_messages_by_type.'</span></span>' ,
				'href' => admin_url( 'edit.php?post_status=draft&post_type=message')
			));
		}
		$count_user_under_review = count(get_users('&role=ask_under_review&blog_id=1'));
		if ($count_user_under_review > 0) {
			$wp_admin_bar->add_menu( array(
				'parent' => 0,
				'id' => 'user_under_review',
				'title' => '<span class="ab-icon dashicons-before dashicons-admin-users"></span><span class=" count-'.$count_user_under_review.'"><span class="">'.$count_user_under_review.'</span></span>' ,
				'href' => admin_url( 'users.php?role=ask_under_review')
			));
		}
		$wp_admin_bar->add_menu( array(
			'parent' => 0,
			'id' => 'vpanel_page',
			'title' => 'Ask Me Settings' ,
			'href' => admin_url( 'admin.php?page=options')
		));
	}
}
add_action( 'wp_before_admin_bar_render', 'vpanel_admin_bar' );
/* breadcrumbs */
function breadcrumbs($args = array()) {
    $delimiter  = '<span class="crumbs-span">/</span>';
    $home       = __('Home','vbegy');
    $before     = '<h1>';
    $after      = '</h1>';
    if (!is_home() && !is_front_page() || is_paged()) {
    	if (is_page_template("template-users.php") || is_page_template("template-categories.php") || is_page_template("template-tags.php") || is_category() || is_tax(ask_question_category) || is_tax("product_cat") || is_tag() || is_tax("question_tags") || is_tax("product_tag") || is_archive() || is_post_type_archive("product")) {
    	    $search_page         = vpanel_options('search_page');
    	    $live_search         = vpanel_options('live_search');
    	    $user_search         = vpanel_options('user_search');
    	    $user_filter         = vpanel_options('user_filter');
    	    $category_filter     = vpanel_options('category_filter');
    	    $cat_archives_search = vpanel_options('cat_archives_search');
    	    $cat_search          = vpanel_options('cat_search');
    	    $cat_filter          = vpanel_options('cat_filter');
    	    $child_category      = vpanel_options('child_category');
    	    $tag_archives_search = vpanel_options('tag_archives_search');
    	    $tag_search          = vpanel_options('tag_search');
    	    $tag_filter          = vpanel_options('tag_filter');
    	    $g_user_filter       = (isset($_GET["user_filter"]) && $_GET["user_filter"] != ""?esc_html($_GET["user_filter"]):"user_registered");
    	    $g_cat_filter        = (isset($_GET["cat_filter"]) && $_GET["cat_filter"] != ""?esc_html($_GET["cat_filter"]):"count");
    	    $g_tag_filter        = (isset($_GET["tag_filter"]) && $_GET["tag_filter"] != ""?esc_html($_GET["tag_filter"]):"count");
    	}
    	$breadcrumbs_6 = false;
    	if ((is_page_template("template-users.php") && ($user_filter == 1 || $user_search == 1)) || (is_page_template("template-categories.php") && ($cat_filter == 1 || $cat_search == 1)) || (is_page_template("template-tags.php") && ($tag_filter == 1 || $tag_search == 1)) || ((is_tag() || is_tax("question_tags") || is_tax("product_tag")) && $tag_archives_search == 1) || ((is_category() || is_tax(ask_question_category) || is_tax("product_cat") || is_archive() || is_post_type_archive("question") || is_post_type_archive("product")) && ($cat_archives_search == 1 || $category_filter == 1))) {
    		$breadcrumbs_6 = true;
    	}
        echo '<div class="breadcrumbs"><section class="container"><div class="row"><div class="'.($breadcrumbs_6 == true?"col-md-6":"col-md-12").'">';
        global $post,$wp_query;
        $item = array();
        $homeLink = home_url();
        if (is_search()) {
        	echo $before . __("Search","vbegy") . $after;
        }else if (is_page()) {
        	echo $before . get_the_title() . $after;
        }else if (is_attachment()) {
			$parent = get_post($post->post_parent);
			$cat = get_the_category($parent->ID);
			echo $before . get_the_title() . $after;
        }elseif ( is_singular() ) {
    		$post = $wp_query->get_queried_object();
    		$post_id = (int) $wp_query->get_queried_object_id();
    		$post_type = $post->post_type;
    		$post_type_object = get_post_type_object( $post_type );
    		if ( 'post' === $wp_query->post->post_type || 'question' === $wp_query->post->post_type || 'product' === $wp_query->post->post_type ) {
    			echo $before . get_the_title() . $after;
    		}
    		if ( 'page' !== $wp_query->post->post_type ) {
    			if ( isset( $args["singular_{$wp_query->post->post_type}_taxonomy"] ) && is_taxonomy_hierarchical( $args["singular_{$wp_query->post->post_type}_taxonomy"] ) ) {
    				$terms = wp_get_object_terms( $post_id, $args["singular_{$wp_query->post->post_type}_taxonomy"] );
    				echo array_merge( $item, breadcrumbs_plus_get_term_parents( $terms[0], $args["singular_{$wp_query->post->post_type}_taxonomy"] ) );
    			}
    			elseif ( isset( $args["singular_{$wp_query->post->post_type}_taxonomy"] ) )
    				echo get_the_term_list( $post_id, $args["singular_{$wp_query->post->post_type}_taxonomy"], '', ', ', '' );
    		}
    	}else if (is_category() || is_tag() || is_tax()) {
            global $wp_query;
            $term = $wp_query->get_queried_object();
			$taxonomy = get_taxonomy( $term->taxonomy );
			if ( ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent ) && $parents = breadcrumbs_plus_get_term_parents( $term->parent, $term->taxonomy ) )
				$item = array_merge( $item, $parents );
            echo $before . '' . single_cat_title('', false) . '' . $after;
        }elseif (is_day()) {
            echo $before . __('Daily Archives : ','vbegy') . get_the_time('d') . $after;
        }elseif (is_month()) {
            echo $before . __('Monthly Archives : ','vbegy') . get_the_time('F') . $after;
        }elseif (is_year()) {
            echo $before . __('Yearly Archives : ','vbegy') . get_the_time('Y') . $after;
        }elseif (is_single() && !is_attachment()) {
            if (get_post_type() != 'post' && get_post_type() != 'question' && get_post_type() != 'product') {
                $post_type = get_post_type_object(get_post_type());
                $slug = $post_type->rewrite;
                echo $before . get_the_title() . $after;
            }else {
            	$cat = get_the_category();
            	if (isset($cat) && is_array($cat) && isset($cat[0])) {
            		$cat = $cat[0];
            	}
            	echo $before . get_the_title() . $after;
            }
        }elseif (!is_single() && !is_page() && get_post_type() != 'post' && get_post_type() != 'question' && get_post_type() != 'product') {
        	if (is_author()) {
        		$user_login = get_queried_object();
        		if (isset($user_login) && is_object($user_login)) {
        			$user_login = get_userdata(esc_attr($user_login->ID));
        		}
        		if (isset($user_login) && !is_object($user_login)) {
        			$user_login = get_user_by('login',urldecode(get_query_var('author_name')));
        		}
        		if (isset($user_login) && !is_object($user_login)) {
        			$user_login = get_user_by('slug',urldecode(get_query_var('author_name')));
        		}
				echo $before . $user_login->display_name . $after;
        	}else {
				$post_type = get_post_type_object(get_post_type());
				echo $before . (isset($post_type->labels->singular_name)?$post_type->labels->singular_name:__("Error 404","vbegy")) . $after;
        	}
        }elseif (is_attachment()) {
            $parent = get_post($post->post_parent);
            $cat = get_the_category($parent->ID);
            echo $before . get_the_title() . $after;
        }elseif (is_page() && !$post->post_parent) {
            echo $before . get_the_title() . $after;
        }elseif (is_page() && $post->post_parent) {
            $parent_id  = $post->post_parent;
            $breadcrumbs = array();
            while ($parent_id) {
                $page = get_page($parent_id);
                $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
                $parent_id  = $page->post_parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
            echo $before . get_the_title() . $after;
        }elseif (is_search()) {
            echo $before . get_search_query() . $after;
        }elseif (is_tag()) {
            echo $before . single_tag_title('', false) . $after;
        }elseif ( is_author() ) {
            $user_login = get_queried_object();
            if (isset($user_login) && is_object($user_login)) {
            	$user_login = get_userdata(esc_attr($user_login->ID));
            }
            if (isset($user_login) && !is_object($user_login)) {
            	$user_login = get_user_by('login',urldecode(get_query_var('author_name')));
            }
            if (isset($user_login) && !is_object($user_login)) {
            	$user_login = get_user_by('slug',urldecode(get_query_var('author_name')));
            }
            echo $before . $user_login->display_name . $after;
        }elseif (is_404()) {
            echo $before . __('Error 404 ', 'vbegy') . $after;
        }else if (is_archive()) {
        	if ( is_category() || is_tag() || is_tax() ) {
    			$term = $wp_query->get_queried_object();
    			$taxonomy = get_taxonomy( $term->taxonomy );
    			if ( ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent ) && $parents = breadcrumbs_plus_get_term_parents( $term->parent, $term->taxonomy ) )
    				$item = array_merge( $item, $parents );
    			echo $before . $term->name. $after;
    		}else if ( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) {
    			$post_type_object = get_post_type_object( get_query_var( 'post_type' ) );
    			echo $before . $post_type_object->labels->name. $after;
    		}else if ( is_date() ) {
    			if ( is_day() )
    				echo $before . __( 'Archives for ', 'vbegy' ) . get_the_time( 'F j, Y' ). $after;
    			elseif ( is_month() )
    				echo $before . __( 'Archives for ', 'vbegy' ) . single_month_title( ' ', false ). $after;
    			elseif ( is_year() )
    				echo $before . __( 'Archives for ', 'vbegy' ) . get_the_time( 'Y' ). $after;
    		}else if ( is_author() ) {
    			echo $before . __( 'Archives by: ', 'vbegy' ) . get_the_author_meta( 'display_name', $wp_query->post->post_author ). $after;
    		}
        }
        $before     = '<span class="current">';
        $after      = '</span>';
        echo '<div class="clearfix"></div>
        <div class="crumbs">
        <a itemprop="breadcrumb" href="' . $homeLink . '">' . $home . '</a>' . $delimiter . ' ';
        if (is_search()) {
        	echo $before . __("Search","vbegy") . $after;
        }else if (is_category() || is_tag() || is_tax()) {
            global $wp_query;
            $term = $wp_query->get_queried_object();
        	$taxonomy = get_taxonomy( $term->taxonomy );
        	if ( ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent ) && $parents = breadcrumbs_plus_get_term_parents( $term->parent, $term->taxonomy ) )
        		$item = array_merge( $item, $parents );
        	if (isset($term->term_id)) {
        		echo ask_get_taxonomy_parents($term->term_id,$taxonomy->name,true,$delimiter,$term->term_id);
        	}
            echo $before . '' . single_cat_title('', false) . '' . $after;
        }elseif (is_day()) {
            echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>' . $delimiter . '';
            echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a>' . $delimiter . '';
            echo $before . get_the_time('d') . $after;
        }elseif (is_month()) {
            echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>' . $delimiter . '';
            echo $before . get_the_time('F') . $after;
        }elseif (is_year()) {
            echo $before . get_the_time('Y') . $after;
        }elseif (is_single() && !is_attachment()) {
            if (get_post_type() != 'post') {
                $post_type = get_post_type_object(get_post_type());
                $slug = $post_type->rewrite;
                if (get_post_type() == 'question') {
                	$question_category = wp_get_post_terms($post->ID,ask_question_category,array("fields" => "all"));
                	if (isset($question_category[0])) {
                		echo ask_get_taxonomy_parents($question_category[0]->term_id,ask_question_category,TRUE,$delimiter,$question_category[0]->term_id).
                		 '<a href="'.get_term_link($question_category[0]->slug,ask_question_category).'">'.$question_category[0]->name.'</a>'.$delimiter;
                	}
                }else if (get_post_type() == 'product') {
                    global $product;
                    echo '<a href="'.get_post_type_archive_link("product").'/">'.esc_html__("Shop","vbegy") . '</a>' . $delimiter;
                    echo $product->get_categories( ', ', '' );
                    echo $delimiter;
                }else {
	                echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>' . $delimiter;
                }
                echo "".$before . get_the_title() . $after;
            }else {
                $cat = get_the_category(); $cat = $cat[0];
                echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
                echo $before . get_the_title() . $after;
            }
        }elseif (!is_single() && !is_page() && get_post_type() != 'post') {
            if (is_author()) {
				$user_login = get_queried_object();
				if (isset($user_login) && is_object($user_login)) {
					$user_login = get_userdata(esc_attr($user_login->ID));
				}
				if (isset($user_login) && !is_object($user_login)) {
					$user_login = get_user_by('login',urldecode(get_query_var('author_name')));
				}
				if (isset($user_login) && !is_object($user_login)) {
					$user_login = get_user_by('slug',urldecode(get_query_var('author_name')));
				}
				echo $before . $user_login->display_name . $after;
            }else {
	            $post_type = get_post_type_object(get_post_type());
            	echo $before . (isset($post_type->labels->singular_name)?$post_type->labels->singular_name:__("Error 404","vbegy")) . $after;
            }
        }elseif (is_attachment()) {
            $parent = get_post($post->post_parent);
            $cat = get_the_category($parent->ID);
            echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>' . $delimiter . '';
            echo $before . get_the_title() . $after;
        }elseif (is_page() && !$post->post_parent) {
            echo $before . get_the_title() . $after;
        }elseif (is_page() && $post->post_parent) {
            $parent_id  = $post->post_parent;
            $breadcrumbs = array();
            while ($parent_id) {
                $page = get_page($parent_id);
                $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
                $parent_id  = $page->post_parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
            echo $before . get_the_title() . $after;
        }elseif (is_search()) {
            echo $before . __('Search results for ', 'vbegy') . '"' . get_search_query() . '"' . $after;
        }elseif (is_tag()) {
            echo $before . __('Posts tagged ', 'vbegy') . '"' . single_tag_title('', false) . '"' . $after;
        }elseif ( is_author() ) {
            $user_login = get_queried_object();
            if (isset($user_login) && is_object($user_login)) {
            	$user_login = get_userdata(esc_attr($user_login->ID));
            }
            if (isset($user_login) && !is_object($user_login)) {
            	$user_login = get_user_by('login',urldecode(get_query_var('author_name')));
            }
            if (isset($user_login) && !is_object($user_login)) {
            	$user_login = get_user_by('slug',urldecode(get_query_var('author_name')));
            }
            echo $before . $user_login->display_name . $after;
        }elseif (is_404()) {
            echo $before . __('Error 404 ', 'vbegy') . $after;
        }
        if (get_query_var('paged')) {
            if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo '';
            echo "<span class='crumbs-span'>/</span><span class='current'>".__('Page', 'vbegy') . ' ' . get_query_var('paged')."</span>";
            if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo '';
        }
        echo '</div></div>';
        if (!is_author() && (is_page_template("template-users.php") || is_page_template("template-categories.php") || is_page_template("template-tags.php") || is_tag() || is_category() || is_archive() || is_tax(ask_question_category) || is_tax("question_tags") || is_post_type_archive("question") || is_tax("product_cat") || is_tax("product_tag") || is_post_type_archive("product"))) {
	        echo '<div class="col-md-6">
		        <div class="search-form-breadcrumbs">
		        	<div class="row">';
			        	if (is_page_template("template-users.php") && $user_filter == 1) {
			        		echo '<div class="col-md-6'.(is_page_template("template-users.php") && $user_search == 1?"":" col-md-right").'">
			        	    	<form method="get" class="search-filter-form">
			        	    		<span class="styled-select user-filter">
			        	    		<select name="user_filter" onchange="this.form.submit()">
			        	    			<option value="user_registered" '.selected($g_user_filter,"user_registered",false).'>'.__("Register","vbegy").'</option>
			        	    			<option value="display_name" '.selected($g_user_filter,"display_name",false).'>'.__("Name","vbegy").'</option>
			        	    			<option value="ID" '.selected($g_user_filter,"ID",false).'>'.__("ID","vbegy").'</option>
			        	    			<option value="question_count" '.selected($g_user_filter,"question_count",false).'>'.__("Questions","vbegy").'</option>
			        	    			<option value="answers" '.selected($g_user_filter,"answers",false).'>'.__("Answers","vbegy").'</option>
			        	    			<option value="the_best_answer" '.selected($g_user_filter,"the_best_answer",false).'>'.__("Best Answers","vbegy").'</option>
			        	    			<option value="points" '.selected($g_user_filter,"points",false).'>'.__("Points","vbegy").'</option>
			        	    			<option value="post_count" '.selected($g_user_filter,"post_count",false).'>'.__("Posts","vbegy").'</option>
			        	    			<option value="comments" '.selected($g_user_filter,"comments",false).'>'.__("Comments","vbegy").'</option>
			        	    		</select>
			        	    		</span>
			        	    	</form>
			        		</div>';
			        	}
			        	if (is_page_template("template-tags.php") && $tag_filter == 1) {
			        		echo '<div class="col-md-6'.(is_page_template("template-tags.php") && $tag_search == 1?"":" col-md-right").'">
			        	    	<form method="get" class="search-filter-form">
			        	    		<span class="styled-select tag-filter">
			        	    		<select name="tag_filter" onchange="this.form.submit()">
			        	    			<option value="count" '.selected($g_tag_filter,"count",false).'>'.__("Popular","vbegy").'</option>
			        	    			<option value="name" '.selected($g_tag_filter,"name",false).'>'.__("Name","vbegy").'</option>
			        	    		</select>
			        	    		</span>
			        	    	</form>
			        		</div>';
			        	}
			        	if (is_page_template("template-categories.php") && $cat_filter == 1) {
			        		echo '<div class="col-md-6'.(is_page_template("template-categories.php") && $cat_search == 1?"":" col-md-right").'">
			        	    	<form method="get" class="search-filter-form">
			        	    		<span class="styled-select cat-filter">
			        	    		<select name="cat_filter" onchange="this.form.submit()">
			        	    			<option value="count" '.selected($g_cat_filter,"count",false).'>'.__("Popular","vbegy").'</option>
			        	    			<option value="name" '.selected($g_cat_filter,"name",false).'>'.__("Name","vbegy").'</option>
			        	    		</select>
			        	    		</span>
			        	    	</form>
			        		</div>';
			        	}
			        	if (!is_tag() && !is_tax("question_tags") && !is_tax("product_tag") && ((is_category() || (!is_post_type_archive() && is_archive()) || is_tax(ask_question_category) || is_post_type_archive("question") || is_tax("product_cat") || is_post_type_archive("product")) && $category_filter == 1)) {
			        		$cats_search = 'category';
			        		if (is_tax(ask_question_category) || is_post_type_archive("question")) {
			        			$cats_search = ask_question_category;
			        		}
			        		if (is_tax("product_cat") || is_post_type_archive("product")) {
			        			$cats_search = 'product_cat';
			        		}
			        		$args = array(
			        		'parent'       => ($child_category == 1?0:""),
			        		'orderby'      => 'name',
			        		'order'        => 'ASC',
			        		'hide_empty'   => 1,
			        		'hierarchical' => 1,
			        		'taxonomy'     => $cats_search,
			        		'pad_counts'   => false );
			        		$options_categories = get_categories($args);
			        		if ($child_category == 1 && isset($term->term_id)) {
			        			$children = get_terms(ask_question_category,array('parent' => $term->term_id,'hide_empty' => 0));
			        			if (isset($children) && is_array($children) && !empty($children)) {
			        				$options_categories = $children;
			        			}else if (isset($term->parent) && $term->parent > 0) {
			        				$children = get_terms(ask_question_category,array('parent' => $term->parent,'hide_empty' => 0));
			        				if (isset($children) && is_array($children) && !empty($children)) {
			        					$options_categories = $children;
			        				}
			        			}
			        		}
			        		if (isset($options_categories) && is_array($options_categories)) {?>
			        			<div class="col-md-6<?php echo ($cat_archives_search == 1?"":" col-md-right")?> search-form">
			        				<div class="search-filter-form">
			        					<span class="styled-select cat-filter">
			        						<?php $option_url = (is_tax(ask_question_category) || is_tax("question_tags") || is_post_type_archive("question")?get_post_type_archive_link("question"):(is_tax("product_cat") || is_tax("product_tag") || is_post_type_archive("product")?get_post_type_archive_link("product"):""))?>
			        						<select class="home_categories" data-taxonomy="<?php echo esc_attr($cats_search)?>">
			        							<option<?php echo (is_post_type_archive("question")?' selected="selected"':'')?> value="<?php echo esc_url($option_url)?>"><?php esc_html_e('All Categories','vbegy')?></option>
			        							<?php foreach ($options_categories as $category) {
			        								$option_url = get_term_link($category->slug,is_tax(ask_question_category) || is_tax("question_tags") || is_post_type_archive("question")?ask_question_category:(is_tax("product_tag") || is_tax("product_cat") || is_post_type_archive("product")?"product_cat":"category"));?>
			        								<option <?php echo (is_category() || is_tax(ask_question_category) || is_tax("product_cat") || is_tax("question_tags") || is_tax("product_tag")?selected(esc_attr(get_query_var((is_category()?'cat':'term'))),(is_category()?$category->term_id:$category->slug),false):"")?> value="<?php echo esc_url($option_url)?>"><?php echo esc_html($category->name)?></option>
			        							<?php }?>
			        						</select>
			        					</span>
			        				</div>
			        			</div><!-- End search-form -->
			        			<?php 
			        		}
			        	}
			        	if ((is_page_template("template-users.php") && $user_search == 1) || (is_page_template("template-categories.php") && $cat_search == 1) || (is_page_template("template-tags.php") && $tag_search == 1) || ((is_tag() || is_tax("question_tags") || is_tax("product_tag")) && $tag_archives_search == 1) || ((is_category() || (!is_post_type_archive() && is_archive()) || is_tax(ask_question_category) || is_post_type_archive("question") || is_tax("product_cat") || is_post_type_archive("product")) && $cat_archives_search == 1)) {
			        		$cats_search = $tags_search = 'posts';
			        		if (is_page_template("template-categories.php")) {
			        			$cats_tax = rwmb_meta('vbegy_cats_tax','type=radio',$post->ID);
			        			if ($cats_tax == "question") {
			        				$cats_search = ask_question_category;
			        			}else if ($cats_tax == "product") {
			        				$cats_search = "product_cat";
			        			}
			        		}
			        		if (is_tax(ask_question_category) || is_post_type_archive("question")) {
			        			$cats_search = "questions";
			        		}
			        		if (is_tax("product_cat") || is_post_type_archive("product")) {
			        			$cats_search = "products";
			        		}
			        		
			        		if (is_page_template("template-tags.php")) {
			        			$tags_tax = rwmb_meta('vbegy_tags_tax','type=radio',$post->ID);
			        			if ($tags_tax == "question") {
			        				$tags_search = 'question_tags';
			        			}else if ($tags_tax == "product") {
			        				$tags_search = 'products';
			        			}
			        		}
			        		if (is_tax("question_tags")) {
			        			$tags_search = 'questions';
			        		}
			        		if (is_tax("product_tag")) {
			        			$tags_search = 'products';
			        		}
				        	echo '<div class="col-md-6'.((is_page_template("template-users.php") && $user_filter == 1) || (is_page_template("template-categories.php") && $cat_filter == 1) || (is_page_template("template-tags.php") && $tag_filter == 1) || ((is_category() || is_tax(ask_question_category) || is_tax("product_cat")) && $category_filter == 1)?"":" col-md-right").'">
					        	<form method="get" action="'.esc_url((isset($search_page) && $search_page != ""?get_page_link($search_page):"")).'" class="search-input-form">';
					        		if (isset($search_page) && $search_page != "") {
					        			echo '<input type="hidden" name="page_id" value="'.esc_attr($search_page).'">';
					        		}
					        		echo '<input'.($live_search == 1?" class='live-search breadcrumbs-live-search' autocomplete='off'":"").' type="search" name="search" placeholder="'.__("Type to find...","vbegy").'">
					        		<button class="button-search"><i class="icon-search"></i></button>';
					        		if ($live_search == 1) {
					        			echo '<div class="search-results results-empty"></div>';
					        		}
					        		echo '<input type="hidden" name="search_type" class="search_type" value="'.(is_page_template("template-users.php")?"users":(is_page_template("template-tags.php") || is_tag() || is_tax("question_tags") || is_tax("product_tag")?$tags_search:$cats_search)).'">
					        	</form>
				        	</div>';
			        	}
			        echo '</div>
		        </div>
	        </div>';
        }
        echo '</div></section></div>';
    }
}
/* breadcrumbs_plus_get_term_parents */
function breadcrumbs_plus_get_term_parents( $parent_id = '', $taxonomy = '', $separator = '/' ) {
	$html = array();
	$parents = array();
	if ( empty( $parent_id ) || empty( $taxonomy ) )
		return $parents;
	while ( $parent_id ) {
		$parent = get_term( $parent_id, $taxonomy );
		$parents[] = '<a href="' . get_term_link( $parent, $taxonomy ) . '" title="' . esc_attr( $parent->name ) . '">' . $parent->name . '</a>';
		$parent_id = $parent->parent;
	}
	if ( $parents )
		$parents = array_reverse( $parents );
	return $parents;
}
/* ask_get_taxonomy_parents */
function ask_get_taxonomy_parents($id,$taxonomy = 'category',$link = false,$separator = '/',$main_id = '',$nicename = false,$visited = array()) {
	$out = '';
	$parent = get_term($id,$taxonomy);
	
	if (is_wp_error($parent)) {
		return $parent;
	}
	if ($nicename) {
		$name = $parent->slug;
	}else {
		$name = $parent->name;
	}
	
	if ($parent->parent && ($parent->parent != $parent->term_id) && !in_array($parent->parent,$visited)) {
		$visited[] = $parent->parent;
		$out .= ask_get_taxonomy_parents($parent->parent,$taxonomy,$link,$separator,'',$nicename,$visited);
	}
	if ($link) {
		if ($parent->term_id != $main_id) {
			$out .= '<a href="'.esc_url(get_term_link($parent,$taxonomy)).'">'.$name.'</a>'.$separator;
		}
	}else {
		$out .= $name.$separator;
	}
	return $out;
}
/* askme_get_attachment_id */
function askme_get_attachment_id ($image_url) {
	global $wpdb;
	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid RLIKE '%s';", $image_url ));
	if (isset($attachment[0]) && $attachment[0] != "") {
		return $attachment[0];
	}
}
/* askme_get_user_avatar */
function askme_get_user_avatar ($you_avatar,$img_width,$img_height,$user_id) {
	$avatar_num = false;
	if (isset($you_avatar) && $you_avatar != "" && is_numeric($you_avatar)) {
		$avatar_num = true;
	}else {
		$get_attachment_id = askme_get_attachment_id($you_avatar);
		if (isset($get_attachment_id) && $get_attachment_id != "" && is_numeric($get_attachment_id)) {
			$avatar_num = true;
			$you_avatar = $get_attachment_id;
		}
	}
	
	if ($avatar_num == true) {
		$avatar = askme_resize_url($img_width,$img_height,$you_avatar);
	}else {
		$avatar = askme_resize_by_url($you_avatar,$img_width,$img_height);
	}
	return $avatar;
}
/* askme_user_avatar */
function askme_user_avatar ($you_avatar,$img_width,$img_height,$user_id,$user_name,$user = "",$itemprop = false) {
	if ($you_avatar && $user_id > 0) {
		$last_image = askme_get_user_avatar($you_avatar,$img_width,$img_height,$user_id);
		return "<img".($itemprop == true?" itemprop='image'":"")." class='avatar avatar-".$img_width." photo' alt='".(isset($user_name) && $user_name != ""?$user_name:"")."' width='".$img_width."' height='".$img_height."' src='".$last_image."'>";
	}else {
		return get_avatar((!empty($user)?$user:$user_id),$img_width,"",$user_name,null,($itemprop == true?" itemprop='image'":null));
	}
}
/* askme_avatar */
add_filter('get_avatar','askme_avatar',1,5);
function askme_avatar($avatar,$id_or_email,$size,$default,$alt) {
	$user = false;
	if (is_numeric($id_or_email)) {
		$id = (int)$id_or_email;
		$user = get_user_by('id',$id);
	}elseif (is_object($id_or_email)) {
		if (!empty($id_or_email->user_id)) {
			$id = (int)$id_or_email->user_id;
			$user = get_user_by('id',$id);
		}
	}else {
		$user = get_user_by('email',$id_or_email);	
	}
	if ($user && is_object($user)) {
		if ($user->data->ID > 0) {
			$you_avatar = get_the_author_meta('you_avatar',$user->data->ID);
			if ($you_avatar != "") {
				$avatar = askme_user_avatar($you_avatar,$size,$size,$user->data->ID,$alt);
			}
		}
	}
	return $avatar;
}
/* vpanel_show_extra_profile_fields */
add_action( 'show_user_profile', 'vpanel_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'vpanel_show_extra_profile_fields' );
function vpanel_show_extra_profile_fields( $user ) { ?>
	<table class="form-table">
		<?php $user_review = vpanel_options("user_review");
		if (is_super_admin(get_current_user_id()) && $user_review == 1) {
			$if_user_id = get_user_by("id",$user->ID);
			if (isset($if_user_id->caps["ask_under_review"]) && $if_user_id->caps["ask_under_review"] == 1) {?>
				<tr>
					<th><label for="approve_user"><?php _e("Approve this user","vbegy")?></label></th>
					<td>
						<input type="checkbox" name="approve_user" id="approve_user" value="1"><br>
					</td>
				</tr>
			<?php }
		}
		
		$you_avatar = get_the_author_meta('you_avatar',$user->ID);
		if (current_user_can('upload_files')) {?>
		<tr class="rwmb-upload-wrapper">
			<th><label for="you_avatar"><?php _e("Your avatar","vbegy")?></label></th>
			<td>
				<input type="hidden" class="image_id" value="<?php echo (isset($you_avatar) && $you_avatar != "" && is_numeric($you_avatar)?esc_attr($you_avatar):esc_url($you_avatar));?>" id="you_avatar" name="you_avatar">
				<input id="you_avatar_button" class="upload_image_button button upload-button-2" type="button" value="Upload Image">
			</td>
		</tr>
		<?php }
		
		if ($you_avatar) {?>
			<tr>
				<th><label><?php _e("Your avatar","vbegy")?></label></th>
				<td>
					<div class="you_avatar"><?php echo askme_user_avatar($you_avatar,85,85,$user->ID,get_the_author_meta('display_name',$user->ID));?></div>
				</td>
			</tr>
		<?php } ?>
		<tr>
			<th><label for="country"><?php _e("Country","vbegy")?></label></th>
			<td>
				<select name="country" id="country">
					<option value=""><?php _e( 'Select a country&hellip;', 'vbegy' )?></option>
						<?php foreach( vpanel_get_countries() as $key => $value )
							echo '<option value="' . esc_attr( $key ) . '"' . selected( esc_attr( get_the_author_meta( 'country', $user->ID ) ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="city"><?php _e("City","vbegy")?></label></th>
			<td>
				<input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'city', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
		<tr>
			<th><label for="age"><?php _e("Age","vbegy")?></label></th>
			<td>
				<input type="text" name="age" id="age" value="<?php echo esc_attr( get_the_author_meta( 'age', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
		<tr>
			<th><label for="phone"><?php _e("Phone","vbegy")?></label></th>
			<td>
				<input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'phone', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
		<tr>
			<?php
			$sex = esc_attr(get_the_author_meta( 'sex', $user->ID ) );
			?>
			<th><label><?php _e("Sex","vbegy")?></label></th>
			<td>
				<input id="sex_male" name="sex" type="radio" value="1"'<?php echo (isset($sex) && ($sex == "male" || $sex == "1")?' checked="checked"':' checked="checked"')?>'>
				<label for="sex_male"><?php _e("Male","vbegy")?></label>
				
				<input id="sex_female" name="sex" type="radio" value="2"<?php echo (isset($sex) && ($sex == "female" || $sex == "2")?' checked="checked"':'')?>>
					<label for="sex_female"><?php _e("Female","vbegy")?></label>
			</td>
		</tr>
		<tr>
			<th><label for="follow_email"><?php _e("Follow-up email","vbegy")?></label></th>
			<td>
				<?php $follow_email = get_the_author_meta( 'follow_email', $user->ID );
				$follow_email = ($follow_email != ""?1:0)?>
				<input type="checkbox" name="follow_email" id="follow_email" value="1" <?php checked($follow_email,1,true)?>><br>
			</td>
		</tr>
		<?php $active_message = vpanel_options("active_message");
		if ($active_message == 1) {?>
			<tr>
				<?php $received_message = esc_attr( get_the_author_meta( 'received_message', $user->ID ) )?>
				<th><label for="received_message"><?php _e("Received messages?","vbegy")?></label></th>
				<td>
					<input type="checkbox" name="received_message" id="received_message" value="1" <?php checked($received_message,($received_message == ""?"":1),true)?>><br>
				</td>
			</tr>
		<?php }
		if (is_super_admin(get_current_user_id()) && !is_super_admin($user->ID)) {?>
			<tr>
				<?php $block_message = esc_attr( get_the_author_meta( 'block_message', $user->ID ) )?>
				<th><label for="block_message"><?php _e("block messages?","vbegy")?></label></th>
				<td>
					<input type="checkbox" name="block_message" id="block_message" value="1" <?php checked($block_message,1,true)?>><br>
				</td>
			</tr>
		<?php }?>
	</table>
	<h3><?php _e("Show the points, favorite question, followed question, authors i follow, followers, question follow, answer follow, post follow and comment follow","vbegy")?></h3>
	<table class="form-table">
		<tr>
			<?php $show_point_favorite = esc_attr( get_the_author_meta( 'show_point_favorite', $user->ID ) )?>
			<th><label for="show_point_favorite"><?php _e("Show this pages only for me or any one?","vbegy")?></label></th>
			<td>
				<input type="checkbox" name="show_point_favorite" id="show_point_favorite" value="1" <?php checked($show_point_favorite,1,true)?>><br>
			</td>
		</tr>
	</table>
	<?php $send_email_question_groups = vpanel_options("send_email_question_groups");
	if (isset($send_email_question_groups) && is_array($send_email_question_groups)) {
		foreach ($send_email_question_groups as $key => $value) {
			if ($value == 1) {
				$send_email_question_groups[$key] = $key;
			}else {
				unset($send_email_question_groups[$key]);
			}
		}
	}
	if (is_array($send_email_question_groups) && isset($user->roles[0]) && in_array($user->roles[0],$send_email_question_groups)) {?>
		<h3><?php _e("Received email when any one add a new question","vbegy")?></h3>
		<table class="form-table">
			<tr>
				<?php $received_email = esc_attr( get_the_author_meta( 'received_email', $user->ID ) )?>
				<th><label for="received_email"><?php _e("Received email?","vbegy")?></label></th>
				<td>
					<input type="checkbox" name="received_email" id="received_email" value="1" <?php checked($received_email,1,true)?>><br>
				</td>
			</tr>
		</table>
	<?php }
	$active_points = vpanel_options("active_points");
	if (is_super_admin(get_current_user_id()) && $active_points == 1) {?>
		<h3><?php _e( 'Add or remove points for the user', 'vbegy' ) ?></h3>
		<table class="form-table">
			<tr>
				<th><label><?php _e("Add or remove points","vbegy")?></label></th>
				<td>
					<div>
						<select name="add_remove_point">
							<option value="add"><?php _e("Add","vbegy")?></option>
							<option value="remove"><?php _e("Remove","vbegy")?></option>
						</select>
					</div><br>
					<div><?php _e("The points","vbegy")?></div><br>
					<input type="text" name="the_points" class="regular-text"><br><br>
					<div><?php _e("The reason","vbegy")?></div><br>
					<input type="text" name="the_reason" class="regular-text"><br>
				</td>
			</tr>
		</table>
	<?php }
	if (is_super_admin(get_current_user_id())) {?>
		<h3><?php _e( 'Check if you need this user choose or remove the best answer', 'vbegy' ) ?></h3>
		<table class="form-table">
			<tr>
				<?php $user_best_answer = esc_attr( get_the_author_meta( 'user_best_answer', $user->ID ) )?>
				<th><label for="user_best_answer"><?php _e("Select user?","vbegy")?></label></th>
				<td>
					<input type="checkbox" name="user_best_answer" id="user_best_answer" value="1" <?php checked($user_best_answer,1,true)?>><br>
				</td>
			</tr>
		</table>
		<h3><?php _e( 'Check if you need this user is verified user', 'vbegy' ) ?></h3>
		<table class="form-table">
			<tr>
				<?php $verified_user = esc_attr( get_the_author_meta( 'verified_user', $user->ID ) )?>
				<th><label for="verified_user"><?php _e("Select user?","vbegy")?></label></th>
				<td>
					<input type="checkbox" name="verified_user" id="verified_user" value="1" <?php checked($verified_user,1,true)?>><br>
				</td>
			</tr>
		</table>
		<input type="hidden" name="admin" value="save">
	<?php }?>
	<h3><?php _e( 'Social Networking', 'vbegy' ) ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="google"><?php _e("Google +","vbegy")?></label></th>
			<td>
				<input type="text" name="google" id="google" value="<?php echo esc_attr( get_the_author_meta( 'google', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php _e("Twitter","vbegy")?></label></th>
			<td>
				<input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
		<tr>
			<th><label for="facebook"><?php _e("Facebook","vbegy")?></label></th>
			<td>
				<input type="text" name="facebook" id="facebook" value="<?php echo esc_attr( get_the_author_meta( 'facebook', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
		<tr>
			<th><label for="youtube"><?php _e("Youtube","vbegy")?></label></th>
			<td>
				<input type="text" name="youtube" id="youtube" value="<?php echo esc_attr( get_the_author_meta( 'youtube', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
		<tr>
			<th><label for="linkedin"><?php _e("linkedin","vbegy")?></label></th>
			<td>
				<input type="text" name="linkedin" id="linkedin" value="<?php echo esc_attr( get_the_author_meta( 'linkedin', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
		<tr>
			<th><label for="pinterest"><?php _e("Pinterest","vbegy")?></label></th>
			<td>
				<input type="text" name="pinterest" id="pinterest" value="<?php echo esc_attr( get_the_author_meta( 'pinterest', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
		<tr>
			<th><label for="instagram"><?php _e("Instagram","vbegy")?></label></th>
			<td>
				<input type="text" name="instagram" id="instagram" value="<?php echo esc_attr( get_the_author_meta( 'instagram', $user->ID ) ); ?>" class="regular-text"><br>
			</td>
		</tr>
	</table>
	<?php $protocol = is_ssl() ? 'https' : 'http';?>
	<input type="hidden" name="redirect_to" value="<?php echo urldecode(wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']))?>">
<?php }
/* Save user's meta */
add_action( 'personal_options_update', 'vpanel_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'vpanel_save_extra_profile_fields' );
function vpanel_save_extra_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ) return false;
	
	if (isset($_POST['google'])) {
		$google = sanitize_text_field($_POST['google']);
		update_user_meta( $user_id, 'google', $google );
	}
	
	if (isset($_POST['twitter'])) {
		$twitter = sanitize_text_field($_POST['twitter']);
		update_user_meta( $user_id, 'twitter', $twitter );
	}
	
	if (isset($_POST['facebook'])) {
		$facebook = sanitize_text_field($_POST['facebook']);
		update_user_meta( $user_id, 'facebook', $facebook );
	}
	
	if (isset($_POST['linkedin'])) {
		$linkedin = sanitize_text_field($_POST['linkedin']);
		update_user_meta( $user_id, 'linkedin', $linkedin );
	}
	
	if (isset($_POST['instagram'])) {
		$instagram = sanitize_text_field($_POST['instagram']);
		update_user_meta( $user_id, 'instagram', $instagram );
	}
	
	if (isset($_POST['pinterest'])) {
		$pinterest = sanitize_text_field($_POST['pinterest']);
		update_user_meta( $user_id, 'pinterest', $pinterest );
	}
	
	if (isset($_POST['youtube'])) {
		$youtube = sanitize_text_field($_POST['youtube']);
		update_user_meta( $user_id, 'youtube', $youtube );
	}
	
	if (isset($_POST['follow_email'])) {
		$follow_email = sanitize_text_field($_POST['follow_email']);
		update_user_meta( $user_id, 'follow_email', $follow_email );
	}else {
		delete_user_meta( $user_id, 'follow_email' );
	}
	
	if (isset($_POST['you_avatar'])) {
		$you_avatar = sanitize_text_field($_POST['you_avatar']);
		update_user_meta( $user_id, 'you_avatar', $you_avatar );
	}
	
	if (isset($_POST['country'])) {
		$country = sanitize_text_field($_POST['country']);
		update_user_meta( $user_id, 'country', $country );
	}
	
	if (isset($_POST['city'])) {
		$city = sanitize_text_field($_POST['city']);
		update_user_meta( $user_id, 'city', $city );
	}
	
	if (isset($_POST['age'])) {
		$age = sanitize_text_field($_POST['age']);
		update_user_meta( $user_id, 'age', $age );
	}
	
	if (isset($_POST['sex'])) {
		$sex = sanitize_text_field($_POST['sex']);
		update_user_meta( $user_id, 'sex', $sex );
	}
	
	if (isset($_POST['phone'])) {
		$phone = sanitize_text_field($_POST['phone']);
		update_user_meta( $user_id, 'phone', $phone );
	}
	
	do_action("askme_edit_profile_save",(isset($_POST)?$_POST:array()),$user_id);
	
	if (isset($_POST['show_point_favorite'])) {
		$show_point_favorite = sanitize_text_field($_POST['show_point_favorite']);
		update_user_meta( $user_id, 'show_point_favorite', $show_point_favorite );
	}else {
		delete_user_meta( $user_id, 'show_point_favorite' );
	}
	
	if (isset($_POST['received_message'])) {
		$received_message = sanitize_text_field($_POST['received_message']);
		update_user_meta( $user_id, 'received_message', $received_message );
	}else {
		update_user_meta( $user_id, 'received_message', 2 );
	}
	
	if (isset($_POST['block_message'])) {
		$block_message = sanitize_text_field($_POST['block_message']);
		update_user_meta( $user_id, 'block_message', $block_message );
	}else {
		delete_user_meta( $user_id, 'block_message' );
	}
	
	if (isset($_POST['received_email'])) {
		$received_email = sanitize_text_field($_POST['received_email']);
		update_user_meta( $user_id, 'received_email', $received_email );
	}else {
		delete_user_meta( $user_id, 'received_email' );
	}
	
	if (isset($_POST['admin']) && $_POST['admin'] == "save" && isset($_POST['user_best_answer'])) {
		$user_best_answer = sanitize_text_field($_POST['user_best_answer']);
		update_user_meta( $user_id, 'user_best_answer', $user_best_answer );
	}
	
	if (isset($_POST['admin']) && $_POST['admin'] == "save" && isset($_POST['verified_user'])) {
		$verified_user = sanitize_text_field($_POST['verified_user']);
		update_user_meta( $user_id, 'verified_user', $verified_user );
	}
	
	$active_points = vpanel_options("active_points");
	if (is_super_admin(get_current_user_id()) && $active_points == 1) {
		$add_remove_point = "";
		$the_points = "";
		$the_reason = "";
		if (isset($_POST['add_remove_point'])) {
			$add_remove_point = esc_attr($_POST['add_remove_point']);
		}
		if (isset($_POST['the_points'])) {
			$the_points = (int)esc_attr($_POST['the_points']);
		}
		if (isset($_POST['the_reason'])) {
			$the_reason = esc_attr($_POST['the_reason']);
		}
		if ($the_points > 0) {
			$current_user = get_user_by("id",$user_id);
			$_points = get_user_meta($user_id,$current_user->user_login."_points",true);
			$_points++;
			
			$points_user = get_user_meta($user_id,"points",true);
			if ($add_remove_point == "remove") {
				$add_remove_point_last = "-";
				$the_reason_last = "admin_remove_points";
				update_user_meta($user_id,"points",$points_user-$the_points);
			}else {
				$add_remove_point_last = "+";
				$the_reason_last = "admin_add_points";
				update_user_meta($user_id,"points",$points_user+$the_points);
			}
			
			if (get_current_user_id() > 0 && $user_id > 0) {
				askme_notifications_activities($user_id,get_current_user_id(),"","","",$the_reason_last,"notifications");
			}
			
			$the_reason = (isset($the_reason) && $the_reason != ""?$the_reason:$the_reason_last);
			update_user_meta($user_id,$current_user->user_login."_points",$_points);
			add_user_meta($user_id,$current_user->user_login."_points_".$_points,array(date_i18n('Y/m/d',current_time('timestamp')),date_i18n('g:i a',current_time('timestamp')),$the_points,$add_remove_point_last,$the_reason));
		}
	}
	$nicename_nickname = (isset($_POST['nickname']) && $_POST['nickname'] != ""?sanitize_text_field($_POST['nickname']):sanitize_text_field($_POST['user_name']));
	edit_user($user_id);
	
	$user_data = get_userdata($user_id);
	$default_group = $user_data->roles;
	if (is_array($default_group)) {
		$default_group = $default_group[0];
	}
	if (isset($_POST['role']) && $_POST['role'] != "" && $default_group != $_POST['role']) {
		$default_group = esc_attr($_POST['role']);
	}
	
	if (is_super_admin(get_current_user_id()) && isset($_POST['approve_user']) && $_POST['approve_user'] == 1) {
		$default_group = vpanel_options("default_group");
		$default_group = (isset($default_group) && $default_group != ""?$default_group:"subscriber");
		$approve_user = get_user_meta($user_id,"approve_user",true);
		if ($approve_user == "") {
			global $vpanel_emails,$vpanel_emails_2,$vpanel_emails_3;
			$logo_email_template = vpanel_options("logo_email_template");
			$send_text = ask_send_email(vpanel_options("email_approve_user"),$user_id);
			$last_message_email = $vpanel_emails.($logo_email_template != ""?'<img src="'.$logo_email_template.'" alt="'.get_bloginfo('name').'">':'').$vpanel_emails_2.$send_text.$vpanel_emails_3;
			$email_title = vpanel_options("title_approve_user");
			$email_title = ($email_title != ""?$email_title:__("Confirm account","vbegy"));
			sendEmail(vpanel_options("email_template"),get_bloginfo('name'),esc_html($user_data->user_email),esc_html($user_data->display_name),$email_title,$last_message_email);
			update_user_meta($user_id,"approve_user",1);
		}
	}
	
	wp_update_user( array ('ID' => $user_id, 'user_nicename' => $nicename_nickname, 'nickname' => $nicename_nickname, 'role' => $default_group) ) ;
	if (isset($_POST["redirect_to"]) && $_POST["redirect_to"] != "") {
		wp_redirect(esc_url($_POST["redirect_to"]));
		die();
	}
}
/* count_user_posts_by_type */
function count_user_posts_by_type( $userid, $post_type = 'post' ) {
	global $wpdb;
	$where = get_posts_by_author_sql( $post_type, true, $userid );
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );
  	return apply_filters( 'get_usernumposts', $count, $userid );
}
/* count_user_posts_by_type_date */
function count_user_posts_by_type_date( $userid, $post_type = 'post', $year = '', $month = '', $day = '' ) {
	global $wpdb;
	$where = get_posts_by_author_sql( $post_type, true, $userid );
	$date_y = date( 'Y' );
	$date_m = date( 'm' );
	$date_d = date( 'd' );
	$date = "AND (YEAR( $wpdb->posts.post_date ) = $date_y".($month == "month"?" AND MONTH( $wpdb->posts.post_date ) = $date_m":"").
	($day == "day"?" AND DAY( $wpdb->posts.post_date ) = $date_d":"") .")";
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where $date" );
  	return apply_filters( 'get_usernumposts', $count, $userid );
}
/* count_posts_by_type */
function count_posts_by_type( $post_type = 'post', $post_status = "publish" ) {
	global $wpdb;
	$where = "WHERE $wpdb->posts.post_type = '$post_type' AND $wpdb->posts.post_status = '$post_status'";
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );
  	return $count;
}
/* count_paid_question_by_type */
function count_paid_question_by_type( $user_id = "", $post_type = 'post', $post_status = "publish" ) {
	global $wpdb;
	$where = "WHERE $wpdb->posts.post_type = 'question' AND $wpdb->posts.post_status = '$post_status' AND post_author = $user_id AND ( ( $wpdb->postmeta.meta_key = '_paid_question' AND $wpdb->postmeta.meta_value = 'paid' ) )";
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) $where" );
  	return $count;
}
/* count_asked_question_by_type */
function count_asked_question_by_type( $user_id = "", $asked = "=", $post_status = "publish" ) {
	global $wpdb;
	$where = "WHERE $wpdb->posts.post_type = 'question' AND $wpdb->posts.post_status = '$post_status' AND $wpdb->posts.comment_count ".$asked." 0 AND ( ( $wpdb->postmeta.meta_key = 'user_id' AND $wpdb->postmeta.meta_value = $user_id ) )";
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) $where" );
  	return $count;
}
/* count_new_message */
function count_new_message( $user_id = "", $post_status = "publish" ) {
	global $wpdb;
	
	$count = $wpdb->get_var( "SELECT COUNT(*) 
	FROM $wpdb->posts 
	
	LEFT JOIN $wpdb->postmeta AS mt1
	ON ($wpdb->posts.ID = mt1.post_id
	AND mt1.meta_key = 'delete_inbox_message' )
	
	LEFT JOIN $wpdb->postmeta AS mt2
	ON ($wpdb->posts.ID = mt2.post_id
	AND mt2.meta_key = 'message_user_id' )
	
	LEFT JOIN $wpdb->postmeta AS mt3
	ON ($wpdb->posts.ID = mt3.post_id
	AND mt3.meta_key = 'message_new' )
	
	WHERE 1=1 
	
	AND ( mt1.post_id IS NULL )
	AND ( mt2.meta_value = $user_id )
	AND ( mt3.meta_value = 1 )
	
	AND $wpdb->posts.post_type = 'message'
	AND $wpdb->posts.post_status = '$post_status'");
	
  	return $count;
}
/* makeClickableLinks */
function makeClickableLinks($text) {
	return make_clickable($text);
}
/* vpanel_get_countries */
function vpanel_get_countries() {
	$countries = array(
		'AF' => __( 'Afghanistan', 'vbegy' ),
		'AX' => __( '&#197;land Islands', 'vbegy' ),
		'AL' => __( 'Albania', 'vbegy' ),
		'DZ' => __( 'Algeria', 'vbegy' ),
		'AD' => __( 'Andorra', 'vbegy' ),
		'AO' => __( 'Angola', 'vbegy' ),
		'AI' => __( 'Anguilla', 'vbegy' ),
		'AQ' => __( 'Antarctica', 'vbegy' ),
		'AG' => __( 'Antigua and Barbuda', 'vbegy' ),
		'AR' => __( 'Argentina', 'vbegy' ),
		'AM' => __( 'Armenia', 'vbegy' ),
		'AW' => __( 'Aruba', 'vbegy' ),
		'AU' => __( 'Australia', 'vbegy' ),
		'AT' => __( 'Austria', 'vbegy' ),
		'AZ' => __( 'Azerbaijan', 'vbegy' ),
		'BS' => __( 'Bahamas', 'vbegy' ),
		'BH' => __( 'Bahrain', 'vbegy' ),
		'BD' => __( 'Bangladesh', 'vbegy' ),
		'BB' => __( 'Barbados', 'vbegy' ),
		'BY' => __( 'Belarus', 'vbegy' ),
		'BE' => __( 'Belgium', 'vbegy' ),
		'PW' => __( 'Belau', 'vbegy' ),
		'BZ' => __( 'Belize', 'vbegy' ),
		'BJ' => __( 'Benin', 'vbegy' ),
		'BM' => __( 'Bermuda', 'vbegy' ),
		'BT' => __( 'Bhutan', 'vbegy' ),
		'BO' => __( 'Bolivia', 'vbegy' ),
		'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'vbegy' ),
		'BA' => __( 'Bosnia and Herzegovina', 'vbegy' ),
		'BW' => __( 'Botswana', 'vbegy' ),
		'BV' => __( 'Bouvet Island', 'vbegy' ),
		'BR' => __( 'Brazil', 'vbegy' ),
		'IO' => __( 'British Indian Ocean Territory', 'vbegy' ),
		'VG' => __( 'British Virgin Islands', 'vbegy' ),
		'BN' => __( 'Brunei', 'vbegy' ),
		'BG' => __( 'Bulgaria', 'vbegy' ),
		'BF' => __( 'Burkina Faso', 'vbegy' ),
		'BI' => __( 'Burundi', 'vbegy' ),
		'KH' => __( 'Cambodia', 'vbegy' ),
		'CM' => __( 'Cameroon', 'vbegy' ),
		'CA' => __( 'Canada', 'vbegy' ),
		'CV' => __( 'Cape Verde', 'vbegy' ),
		'KY' => __( 'Cayman Islands', 'vbegy' ),
		'CF' => __( 'Central African Republic', 'vbegy' ),
		'TD' => __( 'Chad', 'vbegy' ),
		'CL' => __( 'Chile', 'vbegy' ),
		'CN' => __( 'China', 'vbegy' ),
		'CX' => __( 'Christmas Island', 'vbegy' ),
		'CC' => __( 'Cocos (Keeling) Islands', 'vbegy' ),
		'CO' => __( 'Colombia', 'vbegy' ),
		'KM' => __( 'Comoros', 'vbegy' ),
		'CG' => __( 'Congo (Brazzaville)', 'vbegy' ),
		'CD' => __( 'Congo (Kinshasa)', 'vbegy' ),
		'CK' => __( 'Cook Islands', 'vbegy' ),
		'CR' => __( 'Costa Rica', 'vbegy' ),
		'HR' => __( 'Croatia', 'vbegy' ),
		'CU' => __( 'Cuba', 'vbegy' ),
		'CW' => __( 'Cura&Ccedil;ao', 'vbegy' ),
		'CY' => __( 'Cyprus', 'vbegy' ),
		'CZ' => __( 'Czech Republic', 'vbegy' ),
		'DK' => __( 'Denmark', 'vbegy' ),
		'DJ' => __( 'Djibouti', 'vbegy' ),
		'DM' => __( 'Dominica', 'vbegy' ),
		'DO' => __( 'Dominican Republic', 'vbegy' ),
		'EC' => __( 'Ecuador', 'vbegy' ),
		'EG' => __( 'Egypt', 'vbegy' ),
		'SV' => __( 'El Salvador', 'vbegy' ),
		'GQ' => __( 'Equatorial Guinea', 'vbegy' ),
		'ER' => __( 'Eritrea', 'vbegy' ),
		'EE' => __( 'Estonia', 'vbegy' ),
		'ET' => __( 'Ethiopia', 'vbegy' ),
		'FK' => __( 'Falkland Islands', 'vbegy' ),
		'FO' => __( 'Faroe Islands', 'vbegy' ),
		'FJ' => __( 'Fiji', 'vbegy' ),
		'FI' => __( 'Finland', 'vbegy' ),
		'FR' => __( 'France', 'vbegy' ),
		'GF' => __( 'French Guiana', 'vbegy' ),
		'PF' => __( 'French Polynesia', 'vbegy' ),
		'TF' => __( 'French Southern Territories', 'vbegy' ),
		'GA' => __( 'Gabon', 'vbegy' ),
		'GM' => __( 'Gambia', 'vbegy' ),
		'GE' => __( 'Georgia', 'vbegy' ),
		'DE' => __( 'Germany', 'vbegy' ),
		'GH' => __( 'Ghana', 'vbegy' ),
		'GI' => __( 'Gibraltar', 'vbegy' ),
		'GR' => __( 'Greece', 'vbegy' ),
		'GL' => __( 'Greenland', 'vbegy' ),
		'GD' => __( 'Grenada', 'vbegy' ),
		'GP' => __( 'Guadeloupe', 'vbegy' ),
		'GT' => __( 'Guatemala', 'vbegy' ),
		'GG' => __( 'Guernsey', 'vbegy' ),
		'GN' => __( 'Guinea', 'vbegy' ),
		'GW' => __( 'Guinea-Bissau', 'vbegy' ),
		'GY' => __( 'Guyana', 'vbegy' ),
		'HT' => __( 'Haiti', 'vbegy' ),
		'HM' => __( 'Heard Island and McDonald Islands', 'vbegy' ),
		'HN' => __( 'Honduras', 'vbegy' ),
		'HK' => __( 'Hong Kong', 'vbegy' ),
		'HU' => __( 'Hungary', 'vbegy' ),
		'IS' => __( 'Iceland', 'vbegy' ),
		'IN' => __( 'India', 'vbegy' ),
		'ID' => __( 'Indonesia', 'vbegy' ),
		'IR' => __( 'Iran', 'vbegy' ),
		'IQ' => __( 'Iraq', 'vbegy' ),
		'IE' => __( 'Republic of Ireland', 'vbegy' ),
		'IM' => __( 'Isle of Man', 'vbegy' ),
		'IL' => __( 'Israel', 'vbegy' ),
		'IT' => __( 'Italy', 'vbegy' ),
		'CI' => __( 'Ivory Coast', 'vbegy' ),
		'JM' => __( 'Jamaica', 'vbegy' ),
		'JP' => __( 'Japan', 'vbegy' ),
		'JE' => __( 'Jersey', 'vbegy' ),
		'JO' => __( 'Jordan', 'vbegy' ),
		'KZ' => __( 'Kazakhstan', 'vbegy' ),
		'KE' => __( 'Kenya', 'vbegy' ),
		'KI' => __( 'Kiribati', 'vbegy' ),
		'KW' => __( 'Kuwait', 'vbegy' ),
		'KG' => __( 'Kyrgyzstan', 'vbegy' ),
		'LA' => __( 'Laos', 'vbegy' ),
		'LV' => __( 'Latvia', 'vbegy' ),
		'LB' => __( 'Lebanon', 'vbegy' ),
		'LS' => __( 'Lesotho', 'vbegy' ),
		'LR' => __( 'Liberia', 'vbegy' ),
		'LY' => __( 'Libya', 'vbegy' ),
		'LI' => __( 'Liechtenstein', 'vbegy' ),
		'LT' => __( 'Lithuania', 'vbegy' ),
		'LU' => __( 'Luxembourg', 'vbegy' ),
		'MO' => __( 'Macao S.A.R., China', 'vbegy' ),
		'MK' => __( 'Macedonia', 'vbegy' ),
		'MG' => __( 'Madagascar', 'vbegy' ),
		'MW' => __( 'Malawi', 'vbegy' ),
		'MY' => __( 'Malaysia', 'vbegy' ),
		'MV' => __( 'Maldives', 'vbegy' ),
		'ML' => __( 'Mali', 'vbegy' ),
		'MT' => __( 'Malta', 'vbegy' ),
		'MH' => __( 'Marshall Islands', 'vbegy' ),
		'MQ' => __( 'Martinique', 'vbegy' ),
		'MR' => __( 'Mauritania', 'vbegy' ),
		'MU' => __( 'Mauritius', 'vbegy' ),
		'YT' => __( 'Mayotte', 'vbegy' ),
		'MX' => __( 'Mexico', 'vbegy' ),
		'FM' => __( 'Micronesia', 'vbegy' ),
		'MD' => __( 'Moldova', 'vbegy' ),
		'MC' => __( 'Monaco', 'vbegy' ),
		'MN' => __( 'Mongolia', 'vbegy' ),
		'ME' => __( 'Montenegro', 'vbegy' ),
		'MS' => __( 'Montserrat', 'vbegy' ),
		'MA' => __( 'Morocco', 'vbegy' ),
		'MZ' => __( 'Mozambique', 'vbegy' ),
		'MM' => __( 'Myanmar', 'vbegy' ),
		'NA' => __( 'Namibia', 'vbegy' ),
		'NR' => __( 'Nauru', 'vbegy' ),
		'NP' => __( 'Nepal', 'vbegy' ),
		'NL' => __( 'Netherlands', 'vbegy' ),
		'AN' => __( 'Netherlands Antilles', 'vbegy' ),
		'NC' => __( 'New Caledonia', 'vbegy' ),
		'NZ' => __( 'New Zealand', 'vbegy' ),
		'NI' => __( 'Nicaragua', 'vbegy' ),
		'NE' => __( 'Niger', 'vbegy' ),
		'NG' => __( 'Nigeria', 'vbegy' ),
		'NU' => __( 'Niue', 'vbegy' ),
		'NF' => __( 'Norfolk Island', 'vbegy' ),
		'KP' => __( 'North Korea', 'vbegy' ),
		'NO' => __( 'Norway', 'vbegy' ),
		'OM' => __( 'Oman', 'vbegy' ),
		'PK' => __( 'Pakistan', 'vbegy' ),
		'PS' => __( 'Palestinian Territory', 'vbegy' ),
		'PA' => __( 'Panama', 'vbegy' ),
		'PG' => __( 'Papua New Guinea', 'vbegy' ),
		'PY' => __( 'Paraguay', 'vbegy' ),
		'PE' => __( 'Peru', 'vbegy' ),
		'PH' => __( 'Philippines', 'vbegy' ),
		'PN' => __( 'Pitcairn', 'vbegy' ),
		'PL' => __( 'Poland', 'vbegy' ),
		'PT' => __( 'Portugal', 'vbegy' ),
		'QA' => __( 'Qatar', 'vbegy' ),
		'RE' => __( 'Reunion', 'vbegy' ),
		'RO' => __( 'Romania', 'vbegy' ),
		'RU' => __( 'Russia', 'vbegy' ),
		'RW' => __( 'Rwanda', 'vbegy' ),
		'BL' => __( 'Saint Barth&eacute;lemy', 'vbegy' ),
		'SH' => __( 'Saint Helena', 'vbegy' ),
		'KN' => __( 'Saint Kitts and Nevis', 'vbegy' ),
		'LC' => __( 'Saint Lucia', 'vbegy' ),
		'MF' => __( 'Saint Martin (French part)', 'vbegy' ),
		'SX' => __( 'Saint Martin (Dutch part)', 'vbegy' ),
		'PM' => __( 'Saint Pierre and Miquelon', 'vbegy' ),
		'VC' => __( 'Saint Vincent and the Grenadines', 'vbegy' ),
		'SM' => __( 'San Marino', 'vbegy' ),
		'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'vbegy' ),
		'SA' => __( 'Saudi Arabia', 'vbegy' ),
		'SN' => __( 'Senegal', 'vbegy' ),
		'RS' => __( 'Serbia', 'vbegy' ),
		'SC' => __( 'Seychelles', 'vbegy' ),
		'SL' => __( 'Sierra Leone', 'vbegy' ),
		'SG' => __( 'Singapore', 'vbegy' ),
		'SK' => __( 'Slovakia', 'vbegy' ),
		'SI' => __( 'Slovenia', 'vbegy' ),
		'SB' => __( 'Solomon Islands', 'vbegy' ),
		'SO' => __( 'Somalia', 'vbegy' ),
		'ZA' => __( 'South Africa', 'vbegy' ),
		'GS' => __( 'South Georgia/Sandwich Islands', 'vbegy' ),
		'KR' => __( 'South Korea', 'vbegy' ),
		'SS' => __( 'South Sudan', 'vbegy' ),
		'ES' => __( 'Spain', 'vbegy' ),
		'LK' => __( 'Sri Lanka', 'vbegy' ),
		'SD' => __( 'Sudan', 'vbegy' ),
		'SR' => __( 'Suriname', 'vbegy' ),
		'SJ' => __( 'Svalbard and Jan Mayen', 'vbegy' ),
		'SZ' => __( 'Swaziland', 'vbegy' ),
		'SE' => __( 'Sweden', 'vbegy' ),
		'CH' => __( 'Switzerland', 'vbegy' ),
		'SY' => __( 'Syria', 'vbegy' ),
		'TW' => __( 'Taiwan', 'vbegy' ),
		'TJ' => __( 'Tajikistan', 'vbegy' ),
		'TZ' => __( 'Tanzania', 'vbegy' ),
		'TH' => __( 'Thailand', 'vbegy' ),
		'TL' => __( 'Timor-Leste', 'vbegy' ),
		'TG' => __( 'Togo', 'vbegy' ),
		'TK' => __( 'Tokelau', 'vbegy' ),
		'TO' => __( 'Tonga', 'vbegy' ),
		'TT' => __( 'Trinidad and Tobago', 'vbegy' ),
		'TN' => __( 'Tunisia', 'vbegy' ),
		'TR' => __( 'Turkey', 'vbegy' ),
		'TM' => __( 'Turkmenistan', 'vbegy' ),
		'TC' => __( 'Turks and Caicos Islands', 'vbegy' ),
		'TV' => __( 'Tuvalu', 'vbegy' ),
		'UG' => __( 'Uganda', 'vbegy' ),
		'UA' => __( 'Ukraine', 'vbegy' ),
		'AE' => __( 'United Arab Emirates', 'vbegy' ),
		'GB' => __( 'United Kingdom (UK)', 'vbegy' ),
		'US' => __( 'United States (US)', 'vbegy' ),
		'UY' => __( 'Uruguay', 'vbegy' ),
		'UZ' => __( 'Uzbekistan', 'vbegy' ),
		'VU' => __( 'Vanuatu', 'vbegy' ),
		'VA' => __( 'Vatican', 'vbegy' ),
		'VE' => __( 'Venezuela', 'vbegy' ),
		'VN' => __( 'Vietnam', 'vbegy' ),
		'WF' => __( 'Wallis and Futuna', 'vbegy' ),
		'EH' => __( 'Western Sahara', 'vbegy' ),
		'WS' => __( 'Western Samoa', 'vbegy' ),
		'YE' => __( 'Yemen', 'vbegy' ),
		'ZM' => __( 'Zambia', 'vbegy' ),
		'ZW' => __( 'Zimbabwe', 'vbegy' )
	);
	asort( $countries );
	return $countries;
}
/* vpanel_update_options */
function vpanel_update_options(){
	global $themename;
	$post_re = $_POST;
	$all_save = $post_re[vpanel_options];
	//echo "<pre>";print_r($all_save);echo "</pre>";
	if(isset($all_save['import_setting']) && $all_save['import_setting'] != "") {
		$data = json_decode(wp_unslash($all_save['import_setting']),true);
		$array_options = array(vpanel_options,"badges","coupons","sidebars","roles");
		foreach($array_options as $option){
			if(isset($data[$option])){
				update_option($option,$data[$option]);
			}else{
				delete_option($option);
			}
		}
		echo 2;
		update_option("FlushRewriteRules",true);
		die();
	}else {
		foreach($all_save as $key => $value) {
			if (isset($all_save[$key]) && $all_save[$key] == "on") {
				if (isset($all_save["theme_pages"]) && $all_save["theme_pages"] == "on") {
					if ($key == "theme_pages") {
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('search','Page slug','vbegy'),
							'post_title'     => _x('Search','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$search_page = wp_insert_post($page_data);
						update_post_meta($search_page,'_wp_page_template','template-search.php');
						$all_save["search_page"] = $search_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('add_question','Page slug','vbegy'),
							'post_title'     => _x('Add question','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$add_question = wp_insert_post($page_data);
						update_post_meta($add_question,'_wp_page_template','template-ask_question.php');
						$all_save["add_question"] = $add_question;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('edit_question','Page slug','vbegy'),
							'post_title'     => _x('Edit question','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$edit_question = wp_insert_post($page_data);
						update_post_meta($edit_question,'_wp_page_template','template-edit_question.php');
						$all_save["edit_question"] = $edit_question;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('login','Page slug','vbegy'),
							'post_title'     => _x('Login','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$login_register_page = wp_insert_post($page_data);
						update_post_meta($login_register_page,'_wp_page_template','template-login.php');
						$all_save["login_register_page"] = $login_register_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('edit_profile','Page slug','vbegy'),
							'post_title'     => _x('Edit profile','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$user_edit_profile_page = wp_insert_post($page_data);
						update_post_meta($user_edit_profile_page,'_wp_page_template','template-edit_profile.php');
						$all_save["user_edit_profile_page"] = $user_edit_profile_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('user_posts','Page slug','vbegy'),
							'post_title'     => _x('User posts','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$post_user_page = wp_insert_post($page_data);
						update_post_meta($post_user_page,'_wp_page_template','template-user_posts.php');
						$all_save["post_user_page"] = $post_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('user_comments','Page slug','vbegy'),
							'post_title'     => _x('User comments','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$comment_user_page = wp_insert_post($page_data);
						update_post_meta($comment_user_page,'_wp_page_template','template-user_comments.php');
						$all_save["comment_user_page"] = $comment_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('user_questions','Page slug','vbegy'),
							'post_title'     => _x('User questions','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$question_user_page = wp_insert_post($page_data);
						update_post_meta($question_user_page,'_wp_page_template','template-user_question.php');
						$all_save["question_user_page"] = $question_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('user_polls','Page slug','vbegy'),
							'post_title'     => _x('User polls','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$polls_user_page = wp_insert_post($page_data);
						update_post_meta($polls_user_page,'_wp_page_template','template-user_polls.php');
						$all_save["polls_user_page"] = $polls_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('user_asked_questions','Page slug','vbegy'),
							'post_title'     => _x('User asked questions','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$asked_question_user_page = wp_insert_post($page_data);
						update_post_meta($asked_question_user_page,'_wp_page_template','template-asked_question.php');
						$all_save["asked_question_user_page"] = $asked_question_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('paid_questions','Page slug','vbegy'),
							'post_title'     => _x('Paid questions','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$paid_question_page = wp_insert_post($page_data);
						update_post_meta($paid_question_page,'_wp_page_template','template-user_paid_question.php');
						$all_save["paid_question"] = $paid_question_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('user_best_answers','Page slug','vbegy'),
							'post_title'     => _x('User best answers','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$best_answer_user_page = wp_insert_post($page_data);
						update_post_meta($best_answer_user_page,'_wp_page_template','template-user_best_answer.php');
						$all_save["best_answer_user_page"] = $best_answer_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('user_answers','Page slug','vbegy'),
							'post_title'     => _x('User answers','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$answer_user_page = wp_insert_post($page_data);
						update_post_meta($answer_user_page,'_wp_page_template','template-user_answer.php');
						$all_save["answer_user_page"] = $answer_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('favorite_questions','Page slug','vbegy'),
							'post_title'     => _x('Favorite questions','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$favorite_user_page = wp_insert_post($page_data);
						update_post_meta($favorite_user_page,'_wp_page_template','template-user_favorite_questions.php');
						$all_save["favorite_user_page"] = $favorite_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('followed_questions','Page slug','vbegy'),
							'post_title'     => _x('Followed questions','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$followed_user_page = wp_insert_post($page_data);
						update_post_meta($followed_user_page,'_wp_page_template','template-user_followed_questions.php');
						$all_save["followed_user_page"] = $followed_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('user_points','Page slug','vbegy'),
							'post_title'     => _x('User points','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$point_user_page = wp_insert_post($page_data);
						update_post_meta($point_user_page,'_wp_page_template','template-user_points.php');
						$all_save["point_user_page"] = $point_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('i_follow_users','Page slug','vbegy'),
							'post_title'     => _x('I follow users','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$i_follow_user_page = wp_insert_post($page_data);
						update_post_meta($i_follow_user_page,'_wp_page_template','template-i_follow.php');
						$all_save["i_follow_user_page"] = $i_follow_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('followers_users','Page slug','vbegy'),
							'post_title'     => _x('Followers users','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$followers_user_page = wp_insert_post($page_data);
						update_post_meta($followers_user_page,'_wp_page_template','template-followers.php');
						$all_save["followers_user_page"] = $followers_user_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('follow_questions','Page slug','vbegy'),
							'post_title'     => _x('Follow questions','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$follow_question_page = wp_insert_post($page_data);
						update_post_meta($follow_question_page,'_wp_page_template','template-question_follow.php');
						$all_save["follow_question_page"] = $follow_question_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('follow_answers','Page slug','vbegy'),
							'post_title'     => _x('Follow answers','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$follow_answer_page = wp_insert_post($page_data);
						update_post_meta($follow_answer_page,'_wp_page_template','template-answer_follow.php');
						$all_save["follow_answer_page"] = $follow_answer_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('follow_posts','Page slug','vbegy'),
							'post_title'     => _x('Follow posts','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$follow_post_page = wp_insert_post($page_data);
						update_post_meta($follow_post_page,'_wp_page_template','template-post_follow.php');
						$all_save["follow_post_page"] = $follow_post_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('follow comments','Page slug','vbegy'),
							'post_title'     => _x('Follow comments','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$follow_comment_page = wp_insert_post($page_data);
						update_post_meta($follow_comment_page,'_wp_page_template','template-comment_follow.php');
						$all_save["follow_comment_page"] = $follow_comment_page;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('edit_post','Page slug','vbegy'),
							'post_title'     => _x('Edit post','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$edit_post = wp_insert_post($page_data);
						update_post_meta($edit_post,'_wp_page_template','template-edit_post.php');
						$all_save["edit_post"] = $edit_post;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('edit_comment','Page slug','vbegy'),
							'post_title'     => _x('Edit comment','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$edit_comment = wp_insert_post($page_data);
						update_post_meta($edit_comment,'_wp_page_template','template-edit_comment.php');
						$all_save["edit_comment"] = $edit_comment;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('activity_log','Page slug','vbegy'),
							'post_title'     => _x('Activity log','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$activity_log = wp_insert_post($page_data);
						update_post_meta($activity_log,'_wp_page_template','template-activity_log.php');
						$all_save["activity_log_page"] = $activity_log;
						
						$page_data = array(
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'post_author'    => get_current_user_id(),
							'post_name'      => _x('notifications','Page slug','vbegy'),
							'post_title'     => _x('Notifications','Page title','vbegy'),
							'post_content'   => '',
							'post_parent'    => 0,
							'comment_status' => 'closed'
						);
						$notifications = wp_insert_post($page_data);
						update_post_meta($notifications,'_wp_page_template','template-notifications.php');
						$all_save["notifications_page"] = $notifications;
						echo 3;
					}
				}else {
					$all_save[$key] = 1;
				}
			}else {
				$all_save[$key] = $value;
			}
		}
		unset($all_save["theme_pages"]);
		update_option(vpanel_options,$all_save);
		/* Badges */
		if (isset($post_re["badges"])) {
			update_option("badges",$post_re["badges"]);
		}else {
			delete_option("badges");
		}
		/* Coupons */
		if (isset($post_re["coupons"])) {
			update_option("coupons",$post_re["coupons"]);
		}else {
			delete_option("coupons");
		}
		/* Sidebars */
		if (isset($post_re["sidebars"])) {
			update_option("sidebars",$post_re["sidebars"]);
		}else {
			delete_option("sidebars");
		}
		/* roles */
		global $wp_roles;
		if (isset($post_re["roles"])) {$k = 0;
			foreach ($post_re["roles"] as $value_roles) {$k++;
				unset($wp_roles->roles[$value_roles["id"]]);
				add_role($value_roles["id"],$value_roles["group"],array('read' => false));
				$is_group = get_role($value_roles["id"]);
				if (isset($value_roles["ask_question"]) && $value_roles["ask_question"] == "on") {
					$is_group->add_cap('ask_question');
				}else {
					$is_group->remove_cap('ask_question');
				}
				if (isset($value_roles["show_question"]) && $value_roles["show_question"] == "on") {
					$is_group->add_cap('show_question');
				}else {
					$is_group->remove_cap('show_question');
				}
				if (isset($value_roles["add_answer"]) && $value_roles["add_answer"] == "on") {
					$is_group->add_cap('add_answer');
				}else {
					$is_group->remove_cap('add_answer');
				}
				if (isset($value_roles["show_answer"]) && $value_roles["show_answer"] == "on") {
					$is_group->add_cap('show_answer');
				}else {
					$is_group->remove_cap('show_answer');
				}
				if (isset($value_roles["add_post"]) && $value_roles["add_post"] == "on") {
					$is_group->add_cap('add_post');
				}else {
					$is_group->remove_cap('add_post');
				}
				if (isset($value_roles["send_message"]) && $value_roles["send_message"] == "on") {
					$is_group->add_cap('send_message');
				}else {
					$is_group->remove_cap('send_message');
				}
				if (isset($value_roles["upload_files"]) && $value_roles["upload_files"] == "on") {
					$is_group->add_cap('upload_files');
				}else {
					$is_group->remove_cap('upload_files');
				}
			}
			update_option("roles",$post_re["roles"]);
		}else {
			delete_option("roles");
		}
		/* roles_default */
		if (isset($post_re["roles_default"])) {
			update_option("roles_default",$post_re["roles_default"]);
			$old_roles = $wp_roles->roles;
			foreach ($old_roles as $key_r => $value_r) {
				$is_group = get_role($key_r);
				if (isset($post_re["roles_default"][$key_r]) && is_array($post_re["roles_default"][$key_r])) {
					$value_d = $post_re["roles_default"][$key_r];
					if (isset($value_d["ask_question"]) && $value_d["ask_question"] == "on") {
						$is_group->add_cap('ask_question');
					}else {
						$is_group->remove_cap('ask_question');
					}
					if (isset($value_d["show_question"]) && $value_d["show_question"] == "on") {
						$is_group->add_cap('show_question');
					}else {
						$is_group->remove_cap('show_question');
					}
					if (isset($value_d["add_answer"]) && $value_d["add_answer"] == "on") {
						$is_group->add_cap('add_answer');
					}else {
						$is_group->remove_cap('add_answer');
					}
					if (isset($value_d["show_answer"]) && $value_d["show_answer"] == "on") {
						$is_group->add_cap('show_answer');
					}else {
						$is_group->remove_cap('show_answer');
					}
					if (isset($value_d["add_post"]) && $value_d["add_post"] == "on") {
						$is_group->add_cap('add_post');
					}else {
						$is_group->remove_cap('add_post');
					}
					if (isset($value_d["send_message"]) && $value_d["send_message"] == "on") {
						$is_group->add_cap('send_message');
					}else {
						$is_group->remove_cap('send_message');
					}
					if (isset($value_d["upload_files"]) && $value_d["upload_files"] == "on") {
						$is_group->add_cap('upload_files');
					}else {
						$is_group->remove_cap('upload_files');
					}
				}
			}
		}else {
			delete_option("roles_default");
		}
	}
	update_option("FlushRewriteRules",true);
	die(1);
}
add_action('wp_ajax_vpanel_update_options','vpanel_update_options');
add_action('wp_ajax_nopriv_vpanel_update_options','vpanel_update_options');
/* reset_options */
function reset_options() {
	global $themename;
	$options = & Options_Framework::_optionsframework_options();
	foreach ($options as $option) {
		if (isset($option['id'])) {
			$option_std = $option['std'];
			$option_res[$option['id']] = $option['std'];
		}
	}
	update_option(vpanel_options,$option_res);
	update_option("FlushRewriteRules",true);
	die(1);
}
add_action('wp_ajax_reset_options','reset_options');
add_action('wp_ajax_nopriv_reset_options','reset_options');
/* delete_group */
function delete_group() {
	$group_id = esc_attr($_POST["group_id"]);
	remove_role($group_id);
	die(1);
}
add_action('wp_ajax_delete_group','delete_group');
add_action('wp_ajax_nopriv_delete_group','delete_group');
/* vpanel_get_user_url */ 
add_filter('author_link','ask_author_link',10,3);
if (!function_exists('ask_author_link')) :
	function ask_author_link($link,$author_id) {
		global $wp_rewrite;
		$link = $wp_rewrite->get_author_permastruct();
		if ( empty($link) ) {
			$file = home_url( '/' );
			$link = $file . '?author=' . $author_id;
		}else {
			$user = get_userdata($author_id);
			//user_nicename - nickname
			if (isset($user->user_nicename)) {
				$link = str_replace('%author%',$user->user_nicename,$link);
				$link = home_url( user_trailingslashit( $link ) );
			}else {
				return false;
			}
		}
		return $link;
	}
endif;
function vpanel_get_user_url($author_id, $author_nicename = '') {
	$auth_ID = (int) $author_id;
	return get_author_posts_url($auth_ID);
}
/* vpanel_get_badge */
function vpanel_get_badge($author_id,$return = "") {
	$author_id = (int)$author_id;
	if ($author_id > 0) {
		$last_key = 0;
		$points = get_user_meta($author_id,"points",true);
		$badges = get_option("badges");
		if (isset($badges) && is_array($badges)) {
			foreach ($badges as $badges_k => $badges_v) {
				$badges_points[] = $badges_v["badge_points"];
			}
			if (isset($badges_points) && is_array($badges_points)) {
				foreach ($badges_points as $key => $badge_point) {
					if ($points >= $badge_point) {
						$last_key = $key;
					}
				}
			}
			$key = $last_key;
			if ($return == "color") {
				$badge_color = $badges[$key+1]["badge_color"];
				return $badge_color;
			}else if ($return == "name") {
				$badge_name = $badges[$key+1]["badge_name"];
				return $badge_name;
			}else {
				return '<span class="badge-span" style="background-color: '.$badges[$key+1]["badge_color"].'">'.$badges[$key+1]["badge_name"].'</span>';
			}
		}
	}
}
/* vpanel_sidebars */
if (!is_admin()) {
	function vpanel_sidebars($return = 'sidebar_dir') {
		global $post;
		$sidebar_layout = $sidebar_class = "";
		$sidebar_width  = vpanel_options("sidebar_width");
		$sidebar_width  = (isset($sidebar_width) && $sidebar_width != ""?$sidebar_width:"col-md-3");
		if (isset($sidebar_width) && $sidebar_width == "col-md-3") {
			$container_span = "col-md-9";
		}else {
			$container_span = "col-md-8";
		}
		$full_span       = "col-md-12";
		$page_right      = "page-right-sidebar";
		$page_left       = "page-left-sidebar";
		$page_full_width = "page-full-width";
		
		if (is_category()) {
			$category_id = get_query_var('cat');
			$categories = get_option("categories_$category_id");
		}else if (is_tax("product_cat")) {
			$tax_id = get_term_by('slug',get_query_var('term'),"product_cat");
			$tax_id = $tax_id->term_id;
			$categories = get_option("categories_$tax_id");
		}else if (is_tax(ask_question_category)) {
			$tax_id = get_term_by('slug',get_query_var('term'),ask_question_category);
			$tax_id = $tax_id->term_id;
			$categories = get_option("categories_$tax_id");
		}
		
		if (is_author()) {
			$author_sidebar_layout = vpanel_options('author_sidebar_layout');
		}else if (is_category() || is_tax("product_cat") || is_tax(ask_question_category) || is_tax("question_tags")) {
			$cat_sidebar_layout = (isset($categories["cat_sidebar_layout"])?$categories["cat_sidebar_layout"]:"default");
			if ($cat_sidebar_layout == "default") {
				if (is_tax("product_cat")) {
					$cat_sidebar_layout = vpanel_options("products_sidebar_layout");
				}else if (is_tax(ask_question_category) || is_tax("question_tags")) {
					$cat_sidebar_layout = vpanel_options("questions_sidebar_layout");
				}
			}
		}else if (is_single() || is_page()) {
			$sidebar_post = rwmb_meta('vbegy_sidebar','radio',$post->ID);
			if ($sidebar_post == "" || $sidebar_post == "default") {
				$sidebar_post = vpanel_options("sidebar_layout");
			}
		}else {
			$sidebar_layout = vpanel_options('sidebar_layout');
		}
		
		if (is_author()) {
			if ($author_sidebar_layout == "" || $author_sidebar_layout == "default") {
				$author_sidebar_layout = vpanel_options("sidebar_layout");
			}
			if ($author_sidebar_layout == 'left') {
				$sidebar_dir = $page_left;
				$homepage_content_span = $container_span;
			}elseif ($author_sidebar_layout == 'full') {
				$sidebar_dir = $page_full_width;
				$homepage_content_span = $full_span;
			}else {
				$sidebar_dir = $page_right;
				$homepage_content_span = $container_span;
			}
		}else if (is_category() || is_tax("product_cat") || is_tax(ask_question_category) || is_tax("question_tags")) {
			if ($cat_sidebar_layout == "" || $cat_sidebar_layout == "default") {
				$cat_sidebar_layout = vpanel_options("sidebar_layout");
			}
			if ($cat_sidebar_layout == 'left') {
				$sidebar_dir = $page_left;
				$homepage_content_span = $container_span;
			}elseif ($cat_sidebar_layout == 'full') {
				$sidebar_dir = $page_full_width;
				$homepage_content_span = $full_span;
			}else {
				$sidebar_dir = $page_right;
				$homepage_content_span = $container_span;
			}
		}else if (is_tax("product_tag") || is_post_type_archive("product")) {
			$products_layout = vpanel_options("products_sidebar_layout");
			if ($products_layout == 'left') {
				$sidebar_dir = $page_left;
				$homepage_content_span = $container_span;
			}elseif ($products_layout == 'full') {
				$sidebar_dir = $page_full_width;
				$homepage_content_span = $full_span;
			}else {
				$sidebar_dir = $page_right;
				$homepage_content_span = $container_span;
			}
		}else if (is_tax("question_tags") || is_post_type_archive("question")) {
			$questions_layout = vpanel_options("questions_sidebar_layout");
			if ($questions_layout == 'left') {
				$sidebar_dir = $page_left;
				$homepage_content_span = $container_span;
			}elseif ($questions_layout == 'full') {
				$sidebar_dir = $page_full_width;
				$homepage_content_span = $full_span;
			}else {
				$sidebar_dir = $page_right;
				$homepage_content_span = $container_span;
			}
		}else if (is_single() || is_page()) {
			$sidebar_post = rwmb_meta('vbegy_sidebar','radio',$post->ID);
			$sidebar_dir = '';
			if (isset($sidebar_post) && $sidebar_post != "default" && $sidebar_post != "") {
				if ($sidebar_post == 'left') {
					$sidebar_dir = 'page-left-sidebar';
					$homepage_content_span = $container_span;
				}elseif ($sidebar_post == 'full') {
					$sidebar_dir = 'page-full-width';
					$homepage_content_span = $full_span;
				}else {
					$sidebar_dir = 'page-right-sidebar';
					$homepage_content_span = $container_span;
				}
			}else {
				$sidebar_layout_q = vpanel_options('questions_sidebar_layout');
				$sidebar_layout_p = vpanel_options('products_sidebar_layout');
				if (is_singular("question") && $sidebar_layout_q != "default") {
					if ($sidebar_layout_q == 'left') {
						$sidebar_dir = 'page-left-sidebar';
						$homepage_content_span = $container_span;
					}elseif ($sidebar_layout_q == 'full') {
						$sidebar_dir = 'page-full-width';
						$homepage_content_span = $full_span;
					}else {
						$sidebar_dir = 'page-right-sidebar';
						$homepage_content_span = $container_span;
					}
				}else if (is_singular("product") && $sidebar_layout_p != "default") {
					if ($sidebar_layout_p == 'left') {
						$sidebar_dir = 'page-left-sidebar';
						$homepage_content_span = $container_span;
					}elseif ($sidebar_layout_p == 'full') {
						$sidebar_dir = 'page-full-width';
						$homepage_content_span = $full_span;
					}else {
						$sidebar_dir = 'page-right-sidebar';
						$homepage_content_span = $container_span;
					}
				}else {
					$sidebar_layout = vpanel_options('sidebar_layout');
					if ($sidebar_layout == 'left') {
						$sidebar_dir = 'page-left-sidebar';
						$homepage_content_span = $container_span;
					}elseif ($sidebar_layout == 'full') {
						$sidebar_dir = 'page-full-width';
						$homepage_content_span = $full_span;
					}else {
						$sidebar_dir = 'page-right-sidebar';
						$homepage_content_span = $container_span;
					}
				}
			}
		}else {
			if ((is_single() || is_page()) && $sidebar_post != "default" && $sidebar_post != "") {
				if ($sidebar_post == 'left') {
					$sidebar_dir = 'page-left-sidebar';
					$homepage_content_span = $container_span;
				}elseif ($sidebar_post == 'full') {
					$sidebar_dir = 'page-full-width';
					$homepage_content_span = $full_span;
				}else {
					$sidebar_dir = 'page-right-sidebar';
					$homepage_content_span = $container_span;
				}
			}else {
				if ((is_singular("product") && $sidebar_layout_p != "default" && $sidebar_layout_p != "")) {
					$sidebar_layout_p = vpanel_options('products_sidebar_layout');
					if ($sidebar_layout_p == 'left') {
						$sidebar_dir = 'page-left-sidebar';
						$homepage_content_span = $container_span;
					}elseif ($sidebar_layout_p == 'full') {
						$sidebar_dir = 'page-full-width';
						$homepage_content_span = $full_span;
					}else {
						$sidebar_dir = 'page-right-sidebar';
						$homepage_content_span = $container_span;
					}
				}else if ((is_singular("question") && $sidebar_layout_q != "default" && $sidebar_layout_q != "")) {
					$sidebar_layout_q = vpanel_options('questions_sidebar_layout');
					if ($sidebar_layout_q == 'left') {
						$sidebar_dir = 'page-left-sidebar';
						$homepage_content_span = $container_span;
					}elseif ($sidebar_layout_q == 'full') {
						$sidebar_dir = 'page-full-width';
						$homepage_content_span = $full_span;
					}else {
						$sidebar_dir = 'page-right-sidebar';
						$homepage_content_span = $container_span;
					}
				}else {
					$sidebar_layout = vpanel_options('sidebar_layout');
					if ($sidebar_layout == 'left') {
						$sidebar_dir = 'page-left-sidebar';
						$homepage_content_span = $container_span;
					}elseif ($sidebar_layout == 'full') {
						$sidebar_dir = 'page-full-width';
						$homepage_content_span = $full_span;
					}else {
						$sidebar_dir = 'page-right-sidebar';
						$homepage_content_span = $container_span;
					}
				}
			}
		}
		
		if ($return == "sidebar_dir") {
			return $sidebar_dir;
		}else if ($return == "sidebar_class") {
			return $sidebar_class;
		}else if ($return == "sidebar_where") {
			if ($sidebar_dir == $page_full_width) {
				$sidebar_where = 'full';
			}else {
				$sidebar_where = 'sidebar';
			}
			return $sidebar_where;
		}else {
			return $homepage_content_span;
		}
	}
}
/* askme_notifications */
function askme_notifications_activities($user_id = "",$another_user_id = "",$username = "",$post_id = "",$comment_id = "",$text = "",$type = "notifications",$more_text = "",$type_of_item = "") {
	$active_notifications = vpanel_options("active_notifications");
	$active_activity_log = vpanel_options("active_activity_log");
	if (($type == "notifications" && $active_notifications == 1) || ($type == "activities" && $active_activity_log == 1)) {
		/* Number of my types */
		$_types = get_user_meta($user_id,$user_id."_".$type,true);
		if ($_types == "") {
			$_types = 0;
		}
		$_types++;
		update_user_meta($user_id,$user_id."_".$type,$_types);
		
		add_user_meta($user_id,$user_id."_".$type."_".$_types,
			array(
				"date_years"      => date_i18n('Y/m/d',current_time('timestamp')),
				"date_hours"      => date_i18n('g:i a',current_time('timestamp')),
				"time"            => current_time('timestamp'),
				"user_id"         => $user_id,
				"another_user_id" => $another_user_id,
				"post_id"         => $post_id,
				"comment_id"      => $comment_id,
				"text"            => $text,
				"username"        => $username,
				"more_text"       => $more_text,
				"type_of_item"    => $type_of_item
			)
		);
		
		/* New */
		$_new_types = get_user_meta($user_id,$user_id."_new_".$type,true);
		if ($_new_types == "") {
			$_new_types = 0;
		}
		$_new_types++;
		$update = update_user_meta($user_id,$user_id.'_new_'.$type,$_new_types);
	}
}
/* delete_question_post */
function delete_question_post() {
	$data_id = (int)$_POST["data_id"];
	$data_div = esc_attr($_POST["data_div"]);
	$post_author = get_post_field('post_author',$data_id);
	$anonymously_user = get_post_meta($data_id,"anonymously_user",true);
	if ($post_author > 0 || $anonymously_user > 0) {
		askme_notifications_activities(($post_author > 0?$post_author:$anonymously_user),"","","","","delete_".(get_post_type($data_id) == "question"?"question":"post"),"notifications",$data_div,(get_post_type($data_id) == "question"?"question":""));
	}
	wp_delete_post($data_id,true);
	die(1);
}
add_action( 'wp_ajax_delete_question_post', 'delete_question_post' );
add_action('wp_ajax_nopriv_delete_question_post','delete_question_post');
/* delete_comment_answer */
function delete_comment_answer() {
	$data_id = (int)$_POST["data_id"];
	$data_div = esc_attr($_POST["data_div"]);
	$comment_type = get_comment_meta($data_id,'comment_type',"question",true);
	$get_comment = get_comment($data_id);
	$anonymously_user = get_comment_meta($data_id,'anonymously_user',true);
	if ($get_comment->user_id > 0 || $anonymously_user > 0) {
		askme_notifications_activities(($get_comment->user_id > 0?$get_comment->user_id:$anonymously_user),"","","","","delete_".($comment_type == "question"?"answer":"comment"),"notifications",$data_div,($comment_type == "question"?"answer":"comment"));
	}
	wp_delete_comment($data_id,true);
	die();
}
add_action( 'wp_ajax_delete_comment_answer', 'delete_comment_answer' );
add_action('wp_ajax_nopriv_delete_comment_answer','delete_comment_answer');
/* HTML tags */
if (!function_exists('ask_html_tags')) :
	function ask_html_tags($p_active = "") {
		global $allowedposttags,$allowedtags;
		$allowedtags['img'] = array('alt' => true, 'class' => true, 'id' => true, 'title' => true, 'src' => true);
		$allowedposttags['img'] = array('alt' => true, 'class' => true, 'id' => true, 'title' => true, 'src' => true);
		$allowedtags['a'] = array('href' => true, 'title' => true, 'target' => true);
		$allowedposttags['a'] = array('href' => true, 'title' => true, 'target' => true);
		$allowedtags['br'] = array();
		$allowedposttags['br'] = array();
		if ($p_active == "yes") {
			$allowedtags['p'] = array();
			$allowedposttags['p'] = array();
		}
	}
endif;
add_action('init','ask_html_tags',10);
/* Kses stip */
if (!function_exists('ask_kses_stip')) :
	function ask_kses_stip($value,$ireplace = "",$p_active = "") {
		return wp_kses(stripslashes(($ireplace == "yes"?str_ireplace(array("<br />","<br>","<br/>","</p>"), "\r\n",$value):$value)),ask_html_tags(($p_active == "yes"?$p_active:"")));
	}
endif;
/* Kses stip wpautop */
if (!function_exists('ask_kses_stip_wpautop')) :
	function ask_kses_stip_wpautop($value,$ireplace = "",$p_active = "") {
		return wpautop(wp_kses(stripslashes((($ireplace == "yes"?str_ireplace(array("<br />","<br>","<br/>","</p>"), "\r\n",$value):$value))),ask_html_tags(($p_active == "yes"?$p_active:""))));
	}
endif;
function ask_notifications($user_id) {
	$notifications_number = vpanel_options("notifications_number");
	$_notifications = get_user_meta($user_id,$user_id."_notifications",true);
	
	for ($notifications = 1; $notifications <= $_notifications; $notifications++) {
		$notification_one[] = get_user_meta($user_id,$user_id."_notifications_".$notifications);
	}
	if (isset($notification_one) and is_array($notification_one)) {
		$notification = array_reverse($notification_one);
		$end = (sizeof($notification) < $notifications_number) ? sizeof($notification) : $notifications_number;
		echo "<div><ul>";
		for ($i=0;$i < $end ;++$i ) {
			$notification_result = $notification[$i][0];
			echo "<li>";
			if (!empty($notification_result["another_user_id"])) {
				$vpanel_get_user_url = vpanel_get_user_url($notification_result["another_user_id"]);
				$display_name = get_the_author_meta('display_name',$notification_result["another_user_id"]);
			}
			
			if ((($notification_result["text"] == "add_question_user" || $notification_result["text"] == "add_question" || $notification_result["text"] == "poll_question") && empty($notification_result["username"]) && isset($notification_result["another_user_id"]) && $notification_result["another_user_id"] == 0) || (!empty($notification_result["another_user_id"]) || !empty($notification_result["username"])) && $notification_result["text"] != "admin_add_points" && $notification_result["text"] != "admin_remove_points") {
				
				if ((($notification_result["text"] == "add_question_user" || $notification_result["text"] == "add_question" || $notification_result["text"] == "poll_question") && isset($notification_result["another_user_id"]) && $notification_result["another_user_id"] == 0) || (isset($display_name) && $display_name != "")) {
					if (!empty($notification_result["another_user_id"])) {?>
						<a href="<?php echo esc_url($vpanel_get_user_url)?>"><?php echo esc_html($display_name);?></a> 
					<?php }
					if (!empty($notification_result["username"])) {
						echo esc_attr($notification_result["username"])." ";
					}
					if (($notification_result["text"] == "add_question_user" || $notification_result["text"] == "add_question") && empty($notification_result["username"]) && isset($notification_result["another_user_id"]) && $notification_result["another_user_id"] == 0) {
						echo esc_html__("Aanonymous","notification_result")." ";
					}
					if (($notification_result["text"] == "poll_question") && empty($notification_result["username"]) && isset($notification_result["another_user_id"]) && $notification_result["another_user_id"] == 0) {
						echo esc_html__("Not register user","notification_result")." ";
					}
					esc_html_e("has","notification_result");
				}else if (!empty($notification_result["username"])) {
					echo esc_attr($notification_result["username"])." ";
				}else {
					echo esc_html__("Deleted user","notification_result")." -";
				}
			}
			
			if (!empty($notification_result["post_id"])) {
				$get_the_permalink = get_the_permalink($notification_result["post_id"]);
				$get_post_status = get_post_status($notification_result["post_id"]);
			}
			if (!empty($notification_result["comment_id"])) {
				$get_comment = get_comment($notification_result["comment_id"]);
			}
			if (!empty($notification_result["post_id"]) && !empty($notification_result["comment_id"]) && $get_post_status != "trash" && isset($get_comment) && $get_comment->comment_approved != "spam" && $get_comment->comment_approved != "trash") {?>
				<a href="<?php echo esc_url($get_the_permalink.(isset($notification_result["comment_id"])?"#comment-".$notification_result["comment_id"]:""))?>">
			<?php }
			if (!empty($notification_result["post_id"]) && empty($notification_result["comment_id"]) && $get_post_status != "trash" && isset($get_the_permalink) && $get_the_permalink != "") {?>
				<a href="<?php echo esc_url($get_the_permalink)?>">
			<?php }
				echo " ";
				if ($notification_result["text"] == "add_question_user") {
					_e("been asked you a question.","vbegy");
				}else if ($notification_result["text"] == "poll_question") {
					_e("polled at your question","vbegy");
				}else if ($notification_result["text"] == "gift_site") {
					_e("Gift of the site.","vbegy");
				}else if ($notification_result["text"] == "admin_add_points") {
					_e("The administrator added points for you.","vbegy");
				}else if ($notification_result["text"] == "admin_remove_points") {
					_e("The administrator removed points from you.","vbegy");
				}else if ($notification_result["text"] == "question_vote_up") {
					_e("voted up your question.","vbegy");
				}else if ($notification_result["text"] == "question_vote_down") {
					_e("voted down your question.","vbegy");
				}else if ($notification_result["text"] == "answer_vote_up") {
					_e("voted up you answer.","vbegy");
				}else if ($notification_result["text"] == "answer_vote_down") {
					_e("voted down you answer.","vbegy");
				}else if ($notification_result["text"] == "user_follow") {
					_e("followed you.","vbegy");
				}else if ($notification_result["text"] == "user_unfollow") {
					_e("unfollowed you.","vbegy");
				}else if ($notification_result["text"] == "point_back") {
					_e("Your point back because the best answer selected.","vbegy");
				}else if ($notification_result["text"] == "select_best_answer") {
					_e("choosed your answer best answer.","vbegy");
				}else if ($notification_result["text"] == "point_removed") {
					_e("Your point removed because the best answer removed.","vbegy");
				}else if ($notification_result["text"] == "cancel_best_answer") {
					_e("canceled your answer best answer.","vbegy");
				}else if ($notification_result["text"] == "answer_asked_question") {
					_e("answered at your asked question.","vbegy");
				}else if ($notification_result["text"] == "answer_question") {
					_e("answered your question.","vbegy");
				}else if ($notification_result["text"] == "answer_question_follow") {
					_e("answered your question you follow.","vbegy");
				}else if ($notification_result["text"] == "add_question") {
					_e("added a new question.","vbegy");
				}else if ($notification_result["text"] == "question_favorites") {
					_e("added your question at favorites.","vbegy");
				}else if ($notification_result["text"] == "question_remove_favorites") {
					_e("removed your question from favorites.","vbegy");
				}else if ($notification_result["text"] == "follow_question") {
					_e("followed your question.","vbegy");
				}else if ($notification_result["text"] == "unfollow_question") {
					_e("unfollowed your question.","vbegy");
				}else if ($notification_result["text"] == "approved_answer") {
					_e("The administrator added your answer.","vbegy");
				}else if ($notification_result["text"] == "approved_comment") {
					_e("The administrator added your comment.","vbegy");
				}else if ($notification_result["text"] == "approved_question") {
					_e("The administrator added your question.","vbegy");
				}else if ($notification_result["text"] == "approved_post") {
					_e("The administrator added your post.","vbegy");
				}else if ($notification_result["text"] == "add_message_user") {
					echo "<a href='".esc_url(get_page_link(vpanel_options('messages_page')))."'>".__("sended a message for you.","vbegy")."</a>";
				}else if ($notification_result["text"] == "seen_message") {
					_e("seen your message.","vbegy");
				}else if ($notification_result["text"] == "action_comment") {
					echo sprintf(__("The administrator %s your %s.","vbegy"),$notification_result["more_text"],(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "answer"?__("answer","vbegy"):__("comment","vbegy")));
				}else if ($notification_result["text"] == "action_post") {
					echo sprintf(__("The administrator %s your %s.","vbegy"),$notification_result["more_text"],(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "question"?__("question","vbegy"):__("post","vbegy")));
				}else if ($notification_result["text"] == "delete_reason") {
					echo sprintf(__("The administrator reason : %s.","vbegy"),$notification_result["more_text"]);
				}else if ($notification_result["text"] == "delete_question" || $notification_result["text"] == "delete_post") {
					echo sprintf(__("The administrator deleted your %s.","vbegy"),(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "question"?__("question","vbegy"):__("post","vbegy")));
				}else if ($notification_result["text"] == "delete_answer" || $notification_result["text"] == "delete_comment") {
					echo sprintf(__("The administrator deleted your %s.","vbegy"),(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "answer"?__("answer","vbegy"):__("comment","vbegy")));
				}
			if ((!empty($notification_result["post_id"]) && !empty($notification_result["comment_id"]) && $get_post_status != "trash" && isset($get_comment) && $get_comment->comment_approved != "spam" && $get_comment->comment_approved != "trash") || (!empty($notification_result["post_id"]) && empty($notification_result["comment_id"]) && $get_post_status != "trash" && isset($get_the_permalink) && $get_the_permalink != "")) {?>
				</a>
			<?php }
			if (!empty($notification_result["post_id"]) && !empty($notification_result["comment_id"])) {
				if (isset($get_comment) && $get_comment->comment_approved == "spam") {
					echo " ".__('( Spam )','vbegy');
				}else if ($get_post_status == "trash" || (isset($get_comment) && $get_comment->comment_approved == "trash")) {
					echo " ".__('( Trashed )','vbegy');
				}else if (empty($get_comment)) {
					echo " ".__('( Deleted )','vbegy');
				}
				if ($notification_result["text"] == "delete_reason") {
					echo " - ".(isset($notification_result["type_of_item"]) && $notification_result["type_of_item"] == "answer"?__("answer","vbegy"):__("comment","vbegy"));
				}
			}
			if (!empty($notification_result["post_id"]) && empty($notification_result["comment_id"])) {
				if ($get_post_status == "trash") {
					echo " ".__('( Trashed )','vbegy');
				}else if (empty($get_the_permalink)) {
					echo " ".__('( Deleted )','vbegy');
				}
			}
			if (!empty($notification_result["more_text"]) && $notification_result["text"] != "action_post" && $notification_result["text"] != "action_comment" && $notification_result["text"] != "delete_reason") {
				echo " - ".esc_attr($notification_result["more_text"]).".";
			}
			echo "</li>";
		}
		echo "</div></ul>
		<a href='".get_page_link(vpanel_options('notifications_page'))."'>".__("Show all notifications.","vbegy")."</a>";
	}else {echo "<p class='no-notifications'>".__("There are no notifications yet.","vbegy")."</p>";}
}
/* ask_custom_widget_search */
function ask_custom_widget_search ($form) {
	$search_page = vpanel_options('search_page');
	$form = '<form role="search" method="get" class="search-form" action="'.esc_url((isset($search_page) && $search_page != ""?get_page_link($search_page):"")).'">';
		if (isset($search_page) && $search_page != "") {
			$form .= '<input type="hidden" name="page_id" value="'.esc_attr($search_page).'">';
		}
		$form .= '<label>
			<input type="search" class="search-field" placeholder="'.esc_html__("Search ...","vbegy").'" value="'.(get_query_var('search') != ""?esc_attr(get_query_var('search')):esc_attr(get_query_var('s'))).'" name="search">
		</label>
		<input type="submit" class="search-submit" value="'.esc_html__("Search","vbegy").'">
	</form>';
	return $form;
}
add_filter('get_search_form','ask_custom_widget_search',100);
function ask_author($user_id,$sort) {
	$you_avatar = get_the_author_meta('you_avatar',$user_id);
	$country = get_the_author_meta('country',$user_id);
	$url = get_the_author_meta('url',$user_id);
	$twitter = get_the_author_meta('twitter',$user_id);
	$facebook = get_the_author_meta('facebook',$user_id);
	$google = get_the_author_meta('google',$user_id);
	$linkedin = get_the_author_meta('linkedin',$user_id);
	$follow_email = get_the_author_meta('follow_email',$user_id);
	$youtube = get_the_author_meta('youtube',$user_id);
	$pinterest = get_the_author_meta('pinterest',$user_id);
	$instagram = get_the_author_meta('instagram',$user_id);
	$author_description = get_the_author_meta("description",$user_id);
	$points_u = get_the_author_meta('points',$user_id);
	$the_best_answer_u = get_the_author_meta('the_best_answer',$user_id);
	$display_name = get_the_author_meta('display_name',$user_id);
	$user_email = get_the_author_meta('user_email',$user_id);
	$verified_user = get_the_author_meta('verified_user',$user_id);
	$out = '<div class="about-author clearfix">
		<div class="author-image">
		<a href="'.vpanel_get_user_url($user_id).'" original-title="'.$display_name.'" class="tooltip-n">
			'.askme_user_avatar($you_avatar,65,65,$user_id,$display_name).'	
		</a>
		</div>
		<div class="author-bio">
			<h4><a href="'.vpanel_get_user_url($user_id).'">'.$display_name.'</a>'.($verified_user == 1?'<img class="verified_user tooltip-n" alt="'.__("Verified","vbegy").'" original-title="'.__("Verified","vbegy").'" src="'.get_template_directory_uri().'/images/verified.png">':'').vpanel_get_badge($user_id).'</h4>';
			if ($sort == "points") {
				$out .= '<span class="user_count"><i class="icon-heart"></i>'.($points_u != ""?$points_u:"0").' '.__("Points","vbegy").'</span><br>';
			}else if ($sort == "the_best_answer") {
				$out .= '<span class="user_count"><i class="icon-asterisk"></i>'.($the_best_answer_u != ""?$the_best_answer_u:"0").' '.__("Best answers","vbegy").'</span><br>';
			}else if ($sort == "question_count" || $sort == "post_count" || $sort == "answers" || $sort == "comments") {
				if ($sort == "question_count") {
					$out .= '<span class="user_count"><i class="icon-question-sign"></i>'.count_user_posts_by_type($user_id,"question").' '.__("Questions","vbegy").'</span>';
				}else if ($sort == "answers") {
					$out .= '<span class="user_count"><i class="icon-comment"></i>'.count(get_comments(array("post_type" => "question","status" => "approve","user_id" => $user_id))).' '.__("Answers","vbegy").'</span>';
				}else if ($sort == "comments") {
					$out .= '<span class="user_count"><i class="icon-comments"></i>'.count(get_comments(array("post_type" => "post","status" => "approve","user_id" => $user_id))).' '.__("Comments","vbegy").'</span>';
				}else {
					$out .= '<span class="user_count"><i class="icon-file-alt"></i>'.count_user_posts_by_type($user_id,"post").' '.__("Posts","vbegy").'</span>';
				}
			}
			$out .= '<div class="clearfix"></div>
			'.$author_description.'
			<div class="clearfix"></div>
			<br>';
			if ($facebook || $twitter || $linkedin || $google || $follow_email || $youtube || $pinterest || $instagram) {
				$out .= '<span class="user-follow-me">'.__("Follow Me","vbegy").'</span>
				<div class="social_icons social_icons_display">';
					if ($facebook) {
						$out .= '<a href="'.$facebook.'" original-title="'.__("Facebook","vbegy").'" class="tooltip-n">
							<span class="icon_i">
								<span class="icon_square" icon_size="30" span_bg="#3b5997" span_hover="#2f3239">
									<i class="social_icon-facebook"></i>
								</span>
							</span>
						</a>';
					}
					if ($twitter) {
						$out .= '<a href="'.$twitter.'" original-title="'.__("Twitter","vbegy").'" class="tooltip-n">
							<span class="icon_i">
								<span class="icon_square" icon_size="30" span_bg="#00baf0" span_hover="#2f3239">
									<i class="social_icon-twitter"></i>
								</span>
							</span>
						</a>';
					}
					if ($linkedin) {
						$out .= '<a href="'.$linkedin.'" original-title="'.__("Linkedin","vbegy").'" class="tooltip-n">
							<span class="icon_i">
								<span class="icon_square" icon_size="30" span_bg="#006599" span_hover="#2f3239">
									<i class="social_icon-linkedin"></i>
								</span>
							</span>
						</a>';
					}
					if ($google) {
						$out .= '<a href="'.$google.'" original-title="'.__("Google plus","vbegy").'" class="tooltip-n">
							<span class="icon_i">
								<span class="icon_square" icon_size="30" span_bg="#c43c2c" span_hover="#2f3239">
									<i class="social_icon-gplus"></i>
								</span>
							</span>
						</a>';
					}
					if ($youtube) {
						$out .= '<a href="'.$youtube.'" original-title="'.__("Youtube","vbegy").'" class="tooltip-n">
							<span class="icon_i">
								<span class="icon_square" icon_size="30" span_bg="#ef4e41" span_hover="#2f3239">
									<i class="social_icon-youtube"></i>
								</span>
							</span>
						</a>';
					}
					if ($pinterest) {
						$out .= '<a href="'.$pinterest.'" original-title="'.__("Pinterest","vbegy").'" class="tooltip-n">
							<span class="icon_i">
								<span class="icon_square" icon_size="30" span_bg="#e13138" span_hover="#2f3239">
									<i class="social_icon-pinterest"></i>
								</span>
							</span>
						</a>';
					}
					if ($instagram) {
						$out .= '<a href="'.$instagram.'" original-title="'.__("Instagram","vbegy").'" class="tooltip-n">
							<span class="icon_i">
								<span class="icon_square" icon_size="30" span_bg="#548bb6" span_hover="#2f3239">
									<i class="social_icon-instagram"></i>
								</span>
							</span>
						</a>';
					}
					if ($follow_email) {
						$out .= '<a href="mailto:'.$user_email.'" original-title="'.__("Email","vbegy").'" class="tooltip-n">
							<span class="icon_i">
								<span class="icon_square" icon_size="30" span_bg="#000" span_hover="#2f3239">
									<i class="social_icon-email"></i>
								</span>
							</span>
						</a>';
					}
				$out .= '</div>';
			}
		$out .= '</div>
	</div>';
	return $out;
}
/* ask_count_number */
function ask_count_number($input) {
	$input = number_format((int)$input);
	$input_count = substr_count($input,',');
	if ($input_count != '0') {
		if ($input_count == '1'){
			return (int)substr($input,0,-4).'k';
		}else if ($input_count == '2'){
			return (int)substr($input,0,-8).'mil';
		}else if ($input_count == '3'){
			return (int)substr($input,0,-12).'bil';
		}else {
			return;
		}
	}else {
		return $input;
	}
}
/* ask_live_search */
function ask_live_search() {
	global $wpdb,$post;
	$search_type          = (isset($_POST["search_type"])?esc_attr($_POST["search_type"]):"");
	$search_type          = (isset($search_type) && $search_type != ""?$search_type:vpanel_options("default_search"));
	$search_value         = wp_unslash(sanitize_text_field($_POST["search_value"]));
	$search_result_number = vpanel_options("search_result_number");
	$search_page          = vpanel_options("search_page");
	$k_search             = 0;
	if ($search_value != "") {
		echo "<div class='result-div'>
			<ul>";
				if ($search_type == "answers" || $search_type == "comments") {
					$not_get_result_page = true;
					include( get_template_directory() . '/includes/comments.php' );
				}else if ($search_type == "users") {
					$not_get_result_page = true;
					include( get_template_directory() . '/includes/users.php' );
				}else if ($search_type == ask_question_category || $search_type == "product_cat" || $search_type == "category") {
					$not_get_result_page = true;
					include( get_template_directory() . '/includes/categories.php' );
				}else if ($search_type == "question_tags" || $search_type == "product_tag" || $search_type == "post_tag") {
					$not_get_result_page = true;
					include( get_template_directory() . '/includes/tags.php' );
				}else {
					if ($search_type == "posts") {
						$post_type_array = array('post');
					}else if ($search_type == "products") {
						$post_type_array = array('product');
					}else {
						$search_type = "questions";
						$post_type_array = array('question');
					}
					
					$search_query = new wp_query(array('s' => $search_value,'post_type' => $post_type_array,"meta_query" => array(array("key" => "user_id","compare" => "NOT EXISTS"))));
					if ($search_query->have_posts()) :
						while ( $search_query->have_posts() ) : $search_query->the_post();
							$k_search++;
							if ($search_result_number >= $k_search) {
								echo "<li>";
									if ($search_type == "products") {
										echo '<a class="get-results" href="'.get_permalink($post->ID).'">
											'.askme_resize_img(20,20).'
										</a>';
									}
									echo "<a href='".get_permalink($post->ID)."'>".str_ireplace($search_value,"<strong>".$search_value."</strong>",get_the_title($post->ID))."</a>
								</li>";
							}else {
								echo "<li><a href='".esc_url(add_query_arg(array("search" => $search_value,"search_type" => $search_type),(isset($search_page) && $search_page != ""?get_page_link($search_page):"")))."'>".__("View all results.","vbegy")."</a></li>";
								exit;
							}
						endwhile;
					else :
						echo "<li class='no-search-result'>".__("No results found.","vbegy")."</li>";
					endif;
					wp_reset_postdata();
				}
			echo "</ul>
		</div>";
	}
	die();
}
add_action( 'wp_ajax_ask_live_search', 'ask_live_search' );
add_action('wp_ajax_nopriv_ask_live_search','ask_live_search');
/* ask_private */
function ask_private($post_id,$first_user,$second_user) {
	global $post;
	$get_private_question = get_post_meta($post_id,"private_question",true);
	$user_id = get_post_meta($post_id,"user_id",true);
	$user_is_comment = get_post_meta($post_id,"user_is_comment",true);
	$anonymously_user = get_post_meta($post_id,"anonymously_user",true);
	$question_category = wp_get_post_terms($post_id,'question-category',array("fields" => "all"));
	
	if (isset($question_category) && is_array($question_category) && isset($question_category[0])) {
		$get_question_category = get_option("questions_category_".$question_category[0]->term_id);
		$yes_private = 0;
		if (isset($question_category[0]) && isset($get_question_category['private']) && $get_question_category['private'] == "on") {
			if (isset($authordata->ID) && $authordata->ID > 0 && $authordata->ID == $user_get_current_user_id) {
				$yes_private = 1;
			}
		}else if (isset($question_category[0]) && !isset($get_question_category['private'])) {
			$yes_private = 1;
		}
	}else {
		$yes_private = 1;
	}
	
	if (isset($question_category) && is_array($question_category) && empty($question_category[0])) {
		$yes_private = 1;
	}
	
	if ($get_private_question == 1) {
		$yes_private = 0;
		if (isset($first_user) && $first_user > 0 && $first_user == $second_user) {
			$yes_private = 1;
		}
	}
	
	if ($get_private_question == 1 || $get_private_question == "on" || ($user_id != "" && $user_is_comment != true)) {
		$yes_private = 0;
		if ((isset($first_user) && $first_user > 0 && $first_user == $second_user) || ($user_id > 0 && $user_id == $second_user) || ($anonymously_user > 0 && $anonymously_user == $second_user)) {
			$yes_private = 1;
		}
	}
	
	if (is_super_admin($second_user)) {
		$yes_private = 1;
	}
	return $yes_private;
}
/* ask_private_answer */
function ask_private_answer($comment_id,$first_user,$second_user) {
	$yes_private_answer = 0;
	$private_answer = vpanel_options("private_answer");
	$get_private_answer = get_comment_meta($comment_id,'private_answer',true);
	
	if ($private_answer == 1) {
		if (($get_private_answer == 1 && isset($first_user) && $first_user > 0 && $first_user == $second_user) || $get_private_answer != 1) {
			$yes_private_answer = 1;
		}
	}else {
		$yes_private_answer = 1;
	}
	
	if (is_super_admin($second_user)) {
		$yes_private_answer = 1;
	}
	return $yes_private_answer;
}
/* ask_option_images */
function ask_option_images($value_id = '',$value_width = '',$value_height = '',$value_options = '',$val = '',$value_class = '',$option_name = '',$name_id = '',$data_attr = '',$add_value_id = '') {
	$output = '';
	$name = $option_name .($add_value_id != 'no'?'['. $value_id .']':'');
	$width = (isset($value_width) && $value_width != ""?" width='".$value_width."' style='box-sizing: border-box;-moz-box-sizing: border-box;-weblit-box-sizing: border-box;'":"");
	$height = (isset($value_height) && $value_height != ""?" height='".$value_height."' style='box-sizing: border-box;-moz-box-sizing: border-box;-weblit-box-sizing: border-box;'":"");
	foreach ( $value_options as $key => $option ) {
		$selected = '';
		if ( $val != '' && ($val == $key) ) {
			$selected = ' of-radio-img-selected';
		}
		$output .= '<div class="of-radio-img-label">' . esc_html( $key ) . '</div>';
		$output .= '<input type="radio" data-attr="' . esc_attr( $data_attr ) . '" class="of-radio-img-radio" value="' . esc_attr( $key ) . '" '.($name_id != "no"?' id="' . esc_attr( $value_id .'_'. $key) . '" name="' . esc_attr( $name ) . '"':'').' '. checked( $val, $key, false ) .'>';
		$output .= '<img'.$width.$height.' src="' . esc_url( $option ) . '" alt="' . $option .'" class="of-radio-img-img '.(isset($value_class)?esc_attr($value_class):'').'' . $selected .'" '.($name_id != "no"?'onclick="document.getElementById(\''. esc_attr($value_id .'_'. $key) .'\').checked=true;"':'').'>';
	}
	return $output;
}
/* ask_option_sliderui */
function ask_option_sliderui($value_min = '',$value_max = '',$value_step = '',$value_edit = '',$val = '',$value_id = '',$option_name = '',$element = '',$bracket = '',$widget = '') {
	$output = $min = $max = $step = $edit = '';
	
	if(!isset($value_min)){ $min  = '0'; }else{ $min = $value_min; }
	if(!isset($value_max)){ $max  = $min + 1; }else{ $max = $value_max; }
	if(!isset($value_step)){ $step  = '1'; }else{ $step = $value_step; }
	
	if (!isset($value_edit)) { 
		$edit  = ' readonly="readonly"'; 
	}else {
		$edit  = '';
	}
	
	if ($val == '') $val = $min;
	
	//values
	$data = 'data-id="'.(isset($element) && $element != ""?$element:$value_id).'" data-val="'.$val.'" data-min="'.$min.'" data-max="'.$max.'" data-step="'.$step.'"';
	
	//html output
	$output .= '<input type="text" name="'.esc_attr( (isset($widget) && $widget == "widget"?$option_name:$option_name . ($bracket != 'remove_it'?'[':'') . $value_id . ']') ).'" id="'.(isset($element) && $element != ""?$element:$value_id).'" value="'. $val .'" class="mini" '. $edit .' />';
	$output .= '<div id="'.(isset($element) && $element != ""?$element:$value_id).'-slider" class="v_sliderui" '. $data .'></div>';
	return $output;
}
/* sendEmail */
function askme_html_email() {
	return "text/html";
}
add_filter( 'wp_mail_content_type','askme_html_email' );
function sendEmail($fromEmail,$fromEmailName,$toEmail,$toEmailName,$subject,$message,$extra='') {
	$headers = array('From: '.$fromEmailName.' <'.$fromEmail.'>');
	if(wp_mail($toEmail,$subject,$message,$headers)) {
		
	}else {
		@mail($toEmail,$subject,$message,$headers);
	}
}
/* ask_send_email */
function ask_send_email($content,$user_id = 0,$post_id = 0,$comment_id = 0,$reset_password = "",$confirm_link_email = "",$item_price = "",$item_currency = "",$payer_email = "",$first_name = "",$last_name = "",$item_transaction = "",$date = "",$time = "") {
	$content = str_ireplace('[%blogname%]', get_bloginfo( 'name' ), $content);
	$content = str_ireplace('[%site_url%]', esc_url(home_url('/')), $content);
	$content = str_ireplace('[%messages_url%]', esc_url(get_page_link(vpanel_options('messages_page'))), $content);
	
	if ($user_id > 0) {
		$user = new WP_User($user_id);
		$content = str_ireplace('[%user_login%]'    , $user->user_login, $content);
		$content = str_ireplace('[%user_name%]'     , $user->user_login, $content);
		$content = str_ireplace('[%user_nicename%]' , ucfirst($user->user_nicename), $content);
		$content = str_ireplace('[%display_name%]'	, ucfirst($user->display_name), $content);
		$content = str_ireplace('[%user_email%]'    , $user->user_email, $content);
		$content = str_ireplace('[%user_profile%]'  , vpanel_get_user_url($user->ID), $content);
	}
	
	if (isset($reset_password) && $reset_password != "") {
		$content = str_ireplace('[%reset_password%]', $reset_password, $content);
	}
	if (isset($confirm_link_email) && $confirm_link_email != "") {
		$content = str_ireplace('[%confirm_link_email%]', $confirm_link_email, $content);
	}
	
	if ($comment_id > 0) {
		$get_comment = get_comment($comment_id);
		$content = str_ireplace('[%answer_link%]', get_permalink($post_id).'#comment_'.$comment_id, $content);
		$content = str_ireplace('[%the_name%]', $get_comment->comment_author, $content);
	}
	
	if ($post_id > 0) {
		$post = get_post($post_id);
		$content = str_ireplace('[%messages_title%]', $post->post_title, $content);
		$content = str_ireplace('[%question_title%]', $post->post_title, $content);
		$content = str_ireplace('[%post_title%]', $post->post_title, $content);
		$content = str_ireplace('[%question_link%]', get_permalink($post_id), $content);
		$content = str_ireplace('[%post_link%]', get_permalink($post_id), $content);
		if ($post->post_author != 0) {
			$get_the_author = get_user_by("id",$post->post_author);
			$the_author_post = $get_the_author->display_name;
		}else {
			$the_author_post = get_post_meta($get_comment->comment_post_ID,($post->post_type == 'question'?'question_username':'post_username'),true);
			$the_author_post = ($the_author_post != ""?$the_author_post:esc_html__("Anonymous","vbegy"));
		}
		$content = str_ireplace('[%the_author_question%]', $the_author_post, $content);
		$content = str_ireplace('[%the_author_post%]', $the_author_post, $content);
	}
	
	if (isset($item_price) && $item_price != "") {
		$content = str_ireplace('[%item_price%]', $item_price, $content);
	}
	if (isset($item_currency) && $item_currency != "") {
		$content = str_ireplace('[%item_currency%]', $item_currency, $content);
	}
	if (isset($payer_email) && $payer_email != "") {
		$content = str_ireplace('[%payer_email%]', $payer_email, $content);
	}
	if (isset($first_name) && $first_name != "") {
		$content = str_ireplace('[%first_name%]', $first_name, $content);
	}
	if (isset($last_name) && $last_name != "") {
		$content = str_ireplace('[%last_name%]', $last_name, $content);
	}
	if (isset($item_transaction) && $item_transaction != "") {
		$content = str_ireplace('[%item_transaction%]', $item_transaction, $content);
	}
	if (isset($date) && $date != "") {
		$content = str_ireplace('[%date%]', $date, $content);
	}
	if (isset($time) && $time != "") {
		$content = str_ireplace('[%time%]', $time, $content);
	}
	return stripslashes($content);
}
/* ask_filter_where */
function ask_filter_where($where = '') {
	$where .= " AND comment_count = 0";
	return $where;
}
/* ask_filter_where_more */
function ask_filter_where_more($where = '') {
	$where .= " AND comment_count > 0";
	return $where;
}
/* ask_me_not_show_questions */
add_action('wp','ask_me_not_show_questions');
function ask_me_not_show_questions() {
	global $post;
	if (is_singular('question')) {
		$user_get_current_user_id = get_current_user_id();
		$yes_private = ask_private($post->ID,$post->post_author,$user_get_current_user_id);
		if (!is_super_admin($user_get_current_user_id) && $yes_private != 1) {
			global $wp_query;
			$wp_query->set_404();
			status_header(404);
		}
	}
}
/* Get comment reply link */
add_filter("comment_reply_link","askme_comment_reply_link",1,3);
if (!function_exists('askme_comment_reply_link')) :
	function askme_comment_reply_link($link,$args,$comment) {
		$comment_editor = vpanel_options("comment_editor");
		if ($comment_editor == 1) {
			$link = '<a rel="nofollow" class="comment-reply-link askme-reply-link" href="#respond" data-id="'.$comment->comment_ID.'" aria-label="'.esc_attr( sprintf( $args['reply_to_text'], $comment->comment_author ) ).'"><i class="icon-reply"></i>'.esc_html__("Reply","vbegy").'</a>';
		}
		return $link;
	}
endif;?>