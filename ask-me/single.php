<?php get_header();
	$date_format = (vpanel_options("date_format")?vpanel_options("date_format"):get_option("date_format"));
	$vbegy_what_post = rwmb_meta('vbegy_what_post','select',$post->ID);
    $vbegy_sidebar_all = rwmb_meta('vbegy_sidebar','select',$post->ID);
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
	if ( have_posts() ) : while ( have_posts() ) : the_post();
		$custom_page_setting = rwmb_meta('vbegy_custom_page_setting','checkbox',$post->ID);
		$post_meta_s = rwmb_meta('vbegy_post_meta_s','checkbox',$post->ID);
		$post_share_s = rwmb_meta('vbegy_post_share_s','checkbox',$post->ID);
		$post_author_box_s = rwmb_meta('vbegy_post_author_box_s','checkbox',$post->ID);
		$related_post_s = rwmb_meta('vbegy_related_post_s','checkbox',$post->ID);
		$post_comments_s = rwmb_meta('vbegy_post_comments_s','checkbox',$post->ID);
		$post_navigation_s = rwmb_meta('vbegy_post_navigation_s','checkbox',$post->ID);
		$featured_image = vpanel_options("featured_image")?>
		<article <?php post_class('post single-post');?> id="post-<?php echo $post->ID;?>" role="article" itemtype="https://schema.org/Article">
			<div class="post-inner">
				<?php $post_image_filter = apply_filters('askme_filter_feature_image',true);
				if ($post_image_filter == true) {?>
					<div class="post-img<?php if (($vbegy_what_post == "image" && !has_post_thumbnail()) || ($vbegy_what_post == "" && !has_post_thumbnail())) {echo " post-img-0";}else if ($vbegy_what_post == "lightbox") {echo " post-img-lightbox";}else if ($vbegy_what_post == "video") {echo " video_embed";}else if ($vbegy_what_post == "google") {echo " map_embed";}if ($vbegy_sidebar_all == "full") {echo " post-img-12";}else {echo " post-img-9";}?>">
						<?php include (get_template_directory() . '/includes/head.php');?>
					</div>
				<?php }?>
	        	<h2 itemprop="name" class="post-title">
	        		<?php if ($vbegy_what_post == "lightbox") {?>
	        			<span class="post-type"><i class="icon-zoom-in"></i></span>
	        		<?php }else if ($vbegy_what_post == "google") {?>
	        			<span class="post-type"><i class="icon-map-marker"></i></span>
	        		<?php }else if ($vbegy_what_post == "video") {?>
	        			<span class="post-type"><i class="icon-play-circle"></i></span>
	        		<?php }else if ($vbegy_what_post == "slideshow") {?>
	        			<span class="post-type"><i class="icon-film"></i></span>
	        		<?php }else {
	        			if (has_post_thumbnail()) {?>
	        		    	<span class="post-type"><i class="icon-picture"></i></span>
	        			<?php }else {?>
	        		    	<span class="post-type"><i class="icon-file-alt"></i></span>
	        			<?php }
	        		}
	        		$can_edit_post = vpanel_options("can_edit_post");
	        		$post_delete = vpanel_options("post_delete");
	        		$user_login_id_l = get_user_by("id",$post->post_author);
	        		if ($post->post_author != 0 && $user_login_id_l->ID == get_current_user_id()) {
	        			if ($can_edit_post == 1) {?>
	        				<span class="post-edit">
	        					<a href="<?php echo esc_url(add_query_arg("edit_post", $post->ID,get_page_link(vpanel_options('edit_post'))))?>" original-title="<?php _e("Edit the post","vbegy")?>" class="tooltip-n"><i class="icon-edit"></i></a>
	        				</span>
	        			<?php }
	        			if ($post_delete == 1) {
	        				if (isset($_GET) && isset($_GET["delete"]) && $_GET["delete"] == $post->ID && isset($post->post_status) && $post->post_status == "publish") {
	        					if ($post->post_author > 0) {
	        						askme_notifications_activities($post->post_author,"","","","","delete_post","activities");
	        					}
	        					$delete_trush = vpanel_options("delete_post");
	        					wp_delete_post($post->ID,($delete_trush == "trash"?false:true));
	        					$protocol = is_ssl() ? 'https' : 'http';
	        					$redirect_to = wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	        					if ( is_ssl() && force_ssl_admin() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )$secure_cookie = false; else $secure_cookie = '';
	        					wp_redirect((is_page()?$redirect_to:home_url()));
	        				}?>
	        				<span class="post-delete">
	        					<a href="<?php echo esc_url(add_query_arg("delete", $post->ID,get_permalink($post->ID)))?>" original-title="<?php _e("Delete the post","vbegy")?>" class="tooltip-n"><i class="icon-remove"></i></a>
	        				</span>
	        			<?php }
	        		}
	        		
	        		if (!is_admin() && isset($_GET["delete_comment"]) && $_GET["delete_comment"] != "") {
	        			$comment_id  = (int)$_GET["delete_comment"];
	        			$get_comment = get_comment($comment_id);
	        			if (isset($get_comment) && $comment_id > 0 && isset($get_comment->comment_approved) && $get_comment->comment_approved == 1) {
	        				$comment_user_id    = $get_comment->user_id;
	        				$comment_type       = get_comment_meta($comment_id,"comment_type",true);
	        				$can_delete_comment = vpanel_options("can_delete_comment");
	        				$delete_comment     = vpanel_options("delete_comment");
	        				if (($comment_user_id > 0 && $comment_user_id == $user_get_current_user_id && $can_delete_comment == "on") || current_user_can('edit_comment',$comment_id) || is_super_admin($user_get_current_user_id)) {
	        					if ($user_get_current_user_id > 0) {
	        						askme_notifications_activities($user_get_current_user_id,"","","","","delete_".($comment_type == "question"?"answer":"comment"),"activities","",($comment_type == "question"?"answer":"comment"));
	        					}
	        					if ($comment_user_id > 0 && $user_get_current_user_id != $comment_user_id) {
	        						askme_notifications_activities($comment_user_id,"","","","","delete_".($comment_type == "question"?"answer":"comment"),"notifications","",($comment_type == "question"?"answer":"comment"));
	        					}
	        					wp_delete_comment($comment_id,($delete_comment == "trash"?false:true));
	        					$_SESSION['vbegy_session_all'] = '<div class="alert-message success"><p>'.esc_html__("Has been deleted successfully.","vbegy").'</p></div>';
	        					$protocol    = is_ssl() ? 'https' : 'http';
	        					$redirect_to = esc_url(remove_query_arg('delete_comment',wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])));
	        					$redirect_to = (isset($_GET["page"]) && esc_attr($_GET["page"]) != ""?esc_attr($_GET["page"]):$redirect_to);
	        					wp_redirect($redirect_to);
	        					exit;
	        				}
	        			}
	        		}
	        		the_title();?>
	        	</h2>
				<?php $posts_meta = vpanel_options("post_meta");
				if (($posts_meta == 1 && $post_meta_s == "") || ($posts_meta == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($posts_meta == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_meta_s) && $post_meta_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_meta_s) && $post_meta_s == 1)) {
					$post_username = get_post_meta($post->ID, 'post_username',true);
					$post_email = get_post_meta($post->ID, 'post_email',true);?>
					<div class="post-meta">
					    <span class="meta-author" itemprop="author" rel="author"><i class="icon-user"></i>
						    <?php 
						    if ($post->post_author > 0) {
						    	the_author_posts_link();
						    }else {
						    	echo ($post_username);
						    }
						    ?>
					    </span>
					    <?php if (isset($post->post_author) && $post->post_author > 0) {
					    	echo vpanel_get_badge($post->post_author);
					    }?>
					    <span class="meta-date" datetime="<?php echo get_the_date(); ?>"><i class="fa fa-calendar"></i><?php the_time($date_format);?></span>
					    <span class="meta-categories"><i class="icon-suitcase"></i><?php the_category(' , ');?></span>
					    <span class="meta-comment"><i class="fa fa-comments-o"></i><?php comments_popup_link(__('0 Comments', 'vbegy'), __('1 Comment', 'vbegy'), '% '.__('Comments', 'vbegy'));?></span>
					    <span class="post-view"><i class="icon-eye-open"></i><?php $post_stats = get_post_meta($post->ID, 'post_stats', true);echo ($post_stats != ""?$post_stats:0);?> <?php _e("views","vbegy");?></span>
					</div>
				<?php }?>
				<div class="post-content" itemprop="mainContentOfPage">
					<?php the_content();
					
					$added_file = get_post_meta($post->ID, 'added_file', true);
					if ($added_file != "") {
						echo "<div class='clear'></div><br><a class='attachment-link' href='".wp_get_attachment_url($added_file)."'><i class='icon-link'></i>".__("Attachment","vbegy")."</a>";
					}
					?>
				</div>
				
				<?php  wp_link_pages(array('before' => '<div class="pagination post-pagination">','after' => '</div>','link_before' => '<span>','link_after' => '</span>'));?>
				<div class="clearfix"></div>
				
			</div><!-- End post-inner -->
		</article><!-- End article.post -->
		
		<?php $post_share = vpanel_options("post_share");
		if (has_tag() || (($post_share == 1 && $post_share_s == "") || ($post_share == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_share == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_share_s) && $post_share_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_share_s) && $post_share_s == 1))) {?>
			<div class="share-tags page-content">
				<?php if (has_tag()) {?>
					<div class="post-tags"><i class="icon-tags"></i>
						<?php the_tags('',' , ','');?>
					</div>
				<?php }
				if (($post_share == 1 && $post_share_s == "") || ($post_share == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_share == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_share_s) && $post_share_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_share_s) && $post_share_s == 1)) {?>
					<div class="share-inside-warp">
						<ul>
							<li>
								<a href="https://www.facebook.com/sharer.php?u=<?php echo urlencode(get_permalink());?>" target="_blank">
									<span class="icon_i">
										<span class="icon_square" icon_size="20" span_bg="#3b5997" span_hover="#666">
											<i i_color="#FFF" class="social_icon-facebook"></i>
										</span>
									</span>
								</a>
								<a href="https://www.facebook.com/sharer.php?u=<?php echo urlencode(get_permalink());?>" target="_blank"><?php _e("Facebook","vbegy");?></a>
							</li>
							<li>
								<a href="https://twitter.com/home?status=<?php echo urlencode(get_permalink());?>" target="_blank">
									<span class="icon_i">
										<span class="icon_square" icon_size="20" span_bg="#00baf0" span_hover="#666">
											<i i_color="#FFF" class="social_icon-twitter"></i>
										</span>
									</span>
								</a>
								<a target="_blank" href="https://twitter.com/home?status=<?php echo urlencode(get_permalink());?>"><?php _e("Twitter","vbegy");?></a>
							</li>
							<li>
								<a href="https://plus.google.com/share?url=<?php echo urlencode(get_permalink());?>" target="_blank">
									<span class="icon_i">
										<span class="icon_square" icon_size="20" span_bg="#ca2c24" span_hover="#666">
											<i i_color="#FFF" class="social_icon-gplus"></i>
										</span>
									</span>
								</a>
								<a href="https://plus.google.com/share?url=<?php echo urlencode(get_permalink());?>" target="_blank"><?php _e("Google plus","vbegy");?></a>
							</li>
							<li>
								<a href="https://www.tumblr.com/share/link?url=<?php echo urlencode(get_permalink()) ?>&amp;name=<?php echo urlencode(get_the_title()) ?>" target="_blank">
									<span class="icon_i">
										<span class="icon_square" icon_size="20" span_bg="#44546b" span_hover="#666">
											<i i_color="#FFF" class="social_icon-tumblr"></i>
										</span>
									</span>
								</a>
								<a href="https://www.tumblr.com/share/link?url=<?php echo urlencode(get_permalink()) ?>&amp;name=<?php echo urlencode(get_the_title()) ?>" target="_blank"><?php _e("Tumblr","vbegy");?></a>
							</li>
							<?php $pinterestimage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );?>
							<li>
								<a target="_blank" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink($post->ID)); ?>&media=<?php echo $pinterestimage[0]; ?>&description=<?php the_title(); ?>">
									<span class="icon_i">
										<span class="icon_square" icon_size="20" span_bg="#c7151a" span_hover="#666">
											<i i_color="#FFF" class="icon-pinterest"></i>
										</span>
									</span>
								</a>
								<a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink($post->ID)); ?>&media=<?php echo $pinterestimage[0]; ?>&description=<?php the_title(); ?>" target="_blank"><?php _e("Pinterest","vbegy");?></a>
							</li>
							<li>
								<a target="_blank" onClick="popup = window.open('mailto:?subject=<?php the_title(); ?>&amp;body=<?php the_permalink(); ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#">
									<span class="icon_i">
										<span class="icon_square" icon_size="20" span_bg="#000" span_hover="#666">
											<i i_color="#FFF" class="social_icon-email"></i>
										</span>
									</span>
								</a>
								<a target="_blank" onClick="popup = window.open('mailto:?subject=<?php the_title(); ?>&amp;body=<?php the_permalink(); ?>', 'PopupPage', 'height=450,width=500,scrollbars=yes,resizable=yes'); return false" href="#"><?php _e("Email","vbegy");?></a>
							</li>
						</ul>
						<span class="share-inside-f-arrow"></span>
						<span class="share-inside-l-arrow"></span>
					</div><!-- End share-inside-warp -->
					<div class="share-inside"><i class="icon-share-alt"></i><?php _e("Share","vbegy");?></div>
				<?php }?>
				<div class="clearfix"></div>
			</div><!-- End share-tags -->
		<?php }else {
			echo "<br>";
		}
		
	endwhile; endif;
	
	$vbegy_custom_sections = get_post_meta($post->ID,"vbegy_custom_sections",true);
	if (isset($vbegy_custom_sections) && $vbegy_custom_sections == 1) {
		$order_sections = get_post_meta($post->ID,"order_sections_li",true);
	}else {
		$order_sections = vpanel_options("order_sections_li");
	}
	if (empty($order_sections)) {
		$order_sections = array(1 => "advertising",2 => "author",3 => "related",4 => "advertising_2",5 => "comments",6 => "next_previous");
	}
	foreach ($order_sections as $key_r => $value_r) {
		if ($value_r == "") {
			unset($order_sections[$key_r]);
		}else {
			if ($value_r == "advertising") {
				$vbegy_share_adv_type = rwmb_meta('vbegy_share_adv_type','radio',$post->ID);
				$vbegy_share_adv_code = rwmb_meta('vbegy_share_adv_code','textarea',$post->ID);
				$vbegy_share_adv_href = rwmb_meta('vbegy_share_adv_href','text',$post->ID);
				$vbegy_share_adv_img = rwmb_meta('vbegy_share_adv_img','upload',$post->ID);
				
				if ((is_single() || is_page()) && (($vbegy_share_adv_type == "display_code" && $vbegy_share_adv_code != "") || ($vbegy_share_adv_type == "custom_image" && $vbegy_share_adv_img != ""))) {
					$share_adv_type = $vbegy_share_adv_type;
					$share_adv_code = $vbegy_share_adv_code;
					$share_adv_href = $vbegy_share_adv_href;
					$share_adv_img = $vbegy_share_adv_img;
				}else {
					$share_adv_type = vpanel_options("share_adv_type");
					$share_adv_code = vpanel_options("share_adv_code");
					$share_adv_href = vpanel_options("share_adv_href");
					$share_adv_img = vpanel_options("share_adv_img");
				}
				if (($share_adv_type == "display_code" && $share_adv_code != "") || ($share_adv_type == "custom_image" && $share_adv_img != "")) {
					echo '<div class="clearfix"></div>
					<div class="advertising">';
					if ($share_adv_type == "display_code") {
						echo stripcslashes(do_shortcode($share_adv_code));
					}else {
						if ($share_adv_href != "") {
							echo '<a target="_blank" href="'.$share_adv_href.'">';
						}
						echo '<img alt="" src="'.$share_adv_img.'">';
						if ($share_adv_href != "") {
							echo '</a>';
						}
					}
					echo '</div><!-- End advertising -->
					<div class="clearfix"></div>';
				}
			}else if ($value_r == "advertising_2") {
				$vbegy_related_adv_type = rwmb_meta('vbegy_related_adv_type','radio',$post->ID);
				$vbegy_related_adv_code = rwmb_meta('vbegy_related_adv_code','textarea',$post->ID);
				$vbegy_related_adv_href = rwmb_meta('vbegy_related_adv_href','text',$post->ID);
				$vbegy_related_adv_img = rwmb_meta('vbegy_related_adv_img','upload',$post->ID);
				
				if ((is_single() || is_page()) && (($vbegy_related_adv_type == "display_code" && $vbegy_related_adv_code != "") || ($vbegy_related_adv_type == "custom_image" && $vbegy_related_adv_img != ""))) {
					$related_adv_type = $vbegy_related_adv_type;
					$related_adv_code = $vbegy_related_adv_code;
					$related_adv_href = $vbegy_related_adv_href;
					$related_adv_img = $vbegy_related_adv_img;
				}else {
					$related_adv_type = vpanel_options("related_adv_type");
					$related_adv_code = vpanel_options("related_adv_code");
					$related_adv_href = vpanel_options("related_adv_href");
					$related_adv_img = vpanel_options("related_adv_img");
				}
				if (($related_adv_type == "display_code" && $related_adv_code != "") || ($related_adv_type == "custom_image" && $related_adv_img != "")) {
					echo '<div class="clearfix"></div>
					<div class="advertising">';
					if ($related_adv_type == "display_code") {
						echo stripcslashes(do_shortcode($related_adv_code));
					}else {
						if ($related_adv_href != "") {
							echo '<a target="_blank" href="'.$related_adv_href.'">';
						}
						echo '<img alt="" src="'.$related_adv_img.'">';
						if ($related_adv_href != "") {
							echo '</a>';
						}
					}
					echo '</div><!-- End advertising -->
					<div class="clearfix"></div>';
				}
			}else if ($value_r == "author") {
				$post_author_box = vpanel_options("post_author_box");
				if (($post_author_box == 1 && $post_author_box_s == "") || ($post_author_box == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_author_box == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_author_box_s) && $post_author_box_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_author_box_s) && $post_author_box_s == 1)) {
					if ($post->post_author > 0) {
						$twitter = get_the_author_meta('twitter',$post->post_author);
						$facebook = get_the_author_meta('facebook',$post->post_author);
						$google = get_the_author_meta('google',$post->post_author);
						$linkedin = get_the_author_meta('linkedin',$post->post_author);
						$follow_email = get_the_author_meta('follow_email',$post->post_author);
						$youtube = get_the_author_meta('youtube',$post->post_author);
						$pinterest = get_the_author_meta('pinterest',$post->post_author);
						$instagram = get_the_author_meta('instagram',$post->post_author);
						$verified_user = get_the_author_meta('verified_user',$post->post_author);?>
						<div class="about-author clearfix">
							<div class="author-image">
								<a href="<?php echo vpanel_get_user_url($post->post_author,$authordata->nickname);?>" original-title="<?php the_author();?>" class="tooltip-n">
									<?php echo askme_user_avatar(get_the_author_meta('you_avatar', $post->post_author),65,65,$post->post_author,$authordata->display_name);?>
								</a>
							</div>
						    <div class="author-bio">
						        <h4>
							        <?php echo __("About","vbegy")." <a href='".vpanel_get_user_url($post->post_author,$authordata->nickname)."'>".get_the_author()."</a>";
							        if ($verified_user == 1) {
							        	echo '<img class="verified_user tooltip-n" alt="'.__("Verified","vbegy").'" original-title="'.__("Verified","vbegy").'" src="'.get_template_directory_uri().'/images/verified.png">';
							        }
							        echo vpanel_get_badge($post->post_author);?>
						        </h4>
						        <?php the_author_meta('description');?>
						        <div class="clearfix"></div>
						        <?php if ($facebook || $twitter || $linkedin || $google || $follow_email || $youtube || $pinterest || $instagram) { ?>
						        	<br>
						        	<span class="user-follow-me"><?php _e("Follow Me","vbegy")?></span>
						        	<div class="social_icons_display_2">
							        	<?php if ($facebook) {?>
								        	<a href="<?php echo $facebook?>" original-title="<?php _e("Facebook","vbegy")?>" class="tooltip-n">
								        		<span class="icon_i">
								        			<span class="icon_square" icon_size="30" span_bg="#3b5997" span_hover="#2f3239">
								        				<i class="social_icon-facebook"></i>
								        			</span>
								        		</span>
								        	</a>
							        	<?php }
							        	if ($twitter) {?>
								        	<a href="<?php echo $twitter?>" original-title="<?php _e("Twitter","vbegy")?>" class="tooltip-n">
								        		<span class="icon_i">
								        			<span class="icon_square" icon_size="30" span_bg="#00baf0" span_hover="#2f3239">
								        				<i class="social_icon-twitter"></i>
								        			</span>
								        		</span>
								        	</a>
							        	<?php }
							        	if ($linkedin) {?>
								        	<a href="<?php echo $linkedin?>" original-title="<?php _e("Linkedin","vbegy")?>" class="tooltip-n">
								        		<span class="icon_i">
								        			<span class="icon_square" icon_size="30" span_bg="#006599" span_hover="#2f3239">
								        				<i class="social_icon-linkedin"></i>
								        			</span>
								        		</span>
								        	</a>
							        	<?php }
							        	if ($google) {?>
								        	<a href="<?php echo $google?>" original-title="<?php _e("Google plus","vbegy")?>" class="tooltip-n">
								        		<span class="icon_i">
								        			<span class="icon_square" icon_size="30" span_bg="#c43c2c" span_hover="#2f3239">
								        				<i class="social_icon-gplus"></i>
								        			</span>
								        		</span>
								        	</a>
							        	<?php }
							        	if ($pinterest) {?>
								        	<a href="<?php echo $pinterest?>" original-title="<?php _e("Pinterest","vbegy")?>" class="tooltip-n">
								        		<span class="icon_i">
								        			<span class="icon_square" icon_size="30" span_bg="#e13138" span_hover="#2f3239">
								        				<i class="social_icon-pinterest"></i>
								        			</span>
								        		</span>
								        	</a>
							        	<?php }
							        	if ($instagram) {?>
								        	<a href="<?php echo $instagram?>" original-title="<?php _e("Instagram","vbegy")?>" class="tooltip-n">
								        		<span class="icon_i">
								        			<span class="icon_square" icon_size="30" span_bg="#548bb6" span_hover="#2f3239">
								        				<i class="social_icon-instagram"></i>
								        			</span>
								        		</span>
								        	</a>
							        	<?php }
							        	if ($follow_email) {?>
								        	<a href="mailto:<?php echo $authordata->user_email?>" original-title="<?php _e("Email","vbegy")?>" class="tooltip-n">
								        		<span class="icon_i">
								        			<span class="icon_square" icon_size="30" span_bg="#000" span_hover="#2f3239">
								        				<i class="social_icon-email"></i>
								        			</span>
								        		</span>
								        	</a>
							        	<?php }?>
						        	</div>
						        <?php }?>
						    </div>
						</div><!-- End about-author -->
					<?php }
				}
			}else if ($value_r == "related") {
				$related_post = vpanel_options("related_post");
				if (($related_post == 1 && $related_post_s == "") || ($related_post == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($related_post == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($related_post_s) && $related_post_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($related_post_s) && $related_post_s == 1)) {
					$related_no = vpanel_options('related_number') ? vpanel_options('related_number') : 5;
					global $post;
					$orig_post = $post;
					
					$related_query_ = array();
					$related_cat_tag = vpanel_options("related_query");
					
					if ($related_cat_tag == "tags") {
						$tags = wp_get_post_tags($post->ID);
						$tags_ids = array();
						foreach($tags as $q_tag) $tags_ids[] = $q_tag->term_id;
						$related_query_ = array('tag__in' => $tags_ids);
					}else {
						$categories = get_the_category($post->ID);
						$category_ids = array();
						foreach($categories as $q_category) $category_ids[] = $q_category->term_id;
						$related_query_ = array('category__in' => $category_ids);
					}
					
					$args = array_merge($related_query_,array('post__not_in' => array($post->ID),'posts_per_page'=> $related_no));
					$related_query = new wp_query( $args );
					if ($related_query->have_posts()) : ;?>
						<div id="related-posts">
							<h2><?php _e("Related Posts","vbegy");?></h2>
							<ul class="related-posts">
								<?php while ( $related_query->have_posts() ) : $related_query->the_post()?>
									<li class="related-item"><h3><a  href="<?php the_permalink();?>" title="<?php printf('%s', the_title_attribute('echo=0')); ?>"><i class="icon-double-angle-right"></i><?php the_title();?></a></h3></li>
								<?php endwhile;?>
							</ul>
						</div><!-- End related-posts -->
					<?php endif;
					$post = $orig_post;
					wp_reset_postdata();
				}
			}else if ($value_r == "comments") {
				$post_comments = vpanel_options("post_comments");
				if (($post_comments == 1 && $post_comments_s == "") || ($post_comments == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_comments == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_comments_s) && $post_comments_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_comments_s) && $post_comments_s == 1)) {
					comments_template();
				}
			}else if ($value_r == "next_previous") {
				$post_navigation = vpanel_options("post_navigation");
				if (($post_navigation == 1 && $post_navigation_s == "") || ($post_navigation == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($post_navigation == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_navigation_s) && $post_navigation_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_navigation_s) && $post_navigation_s == 1)) {
					$post_nav_category = vpanel_options("post_nav_category");?>
					<div class="post-next-prev clearfix">
					    <p class="prev-post">
					        <?php if ($post_nav_category == 1) {
					        	previous_post_link('%link','<i class="icon-double-angle-left"></i>'.__('&nbsp;Previous post','vbegy'),true,'','category');
					        }else {
					        	previous_post_link('%link','<i class="icon-double-angle-left"></i>'.__('&nbsp;Previous post','vbegy'));
					        }?>
					    </p>
					    <p class="next-post">
					    	<?php if ($post_nav_category == 1) {
					    		next_post_link('%link',__('Next post&nbsp;','vbegy').'<i class="icon-double-angle-right"></i>',true,'','category');
					    	}else {
					    		next_post_link('%link',__('Next post&nbsp;','vbegy').'<i class="icon-double-angle-right"></i>');
					    	}?>
					    </p>
					</div><!-- End post-next-prev -->
				<?php }
			}
		}
	}
get_footer();?>