<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Menu Option Backend
add_action('wp_update_nav_menu_item', 'ask_wp_update_nav_menu_item', 10, 3);
function ask_wp_update_nav_menu_item ($menu_id, $menu_item_db_id, $args) {
	$check = array('menu_link');
	foreach ($check as $key) {
		if (!isset($_POST['menu-item-'.$key][$menu_item_db_id])) {
			$_POST['menu-item-'.$key][$menu_item_db_id] = "";
		}
		$value = $_POST['menu-item-'.$key][$menu_item_db_id];
		update_post_meta($menu_item_db_id,'_menu_item_'.$key,$value);
	}
}

// Setup Menu Option
add_filter( 'wp_setup_nav_menu_item','ask_wp_setup_nav_menu_item' );
function ask_wp_setup_nav_menu_item($menu_item) {
	$menu_item->menu_link = get_post_meta( $menu_item->ID, '_menu_item_menu_link', true );
	return $menu_item;
}

// Hook Menu Walker
add_filter('wp_edit_nav_menu_walker', 'ask_wp_edit_nav_menu_walker');
function ask_wp_edit_nav_menu_walker () {
	return 'ASK_Walker_Nav_Menu_Edit';
}

// Add nav-menu.php to inject Menu Option
require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
class ASK_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_wp_nav_menu_max_depth;
		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		ob_start();
		$item_id = esc_attr( $item->ID );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = '';
		if ( 'taxonomy' == $item->type ) {
			$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
			if ( is_wp_error( $original_title ) )
				$original_title = false;
		} elseif ( 'post_type' == $item->type ) {
			$original_object = get_post( $item->object_id );
			$original_title = get_the_title( $original_object->ID );
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $item->object ),
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
		);

		$title = $item->title;

		if ( ! empty( $item->_invalid ) ) {
			$classes[] = 'menu-item-invalid';
			/* translators: %s: title of menu item which is invalid */
			$title = sprintf( __( '%s (Invalid)' ), $item->title );
		} elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: title of menu item in draft status */
			$title = sprintf( __('%s (Pending)'), $item->title );
		}

		$title = ( ! isset( $item->label ) || '' == $item->label ) ? $title : $item->label;

		$submenu_text = '';
		if ( 0 == $depth )
			$submenu_text = 'style="display: none;"';

		?>
		<li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
			<dl class="menu-item-bar">
				<dt class="menu-item-handle">
					<span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ); ?></span> <span class="is-submenu" <?php echo $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
					<span class="item-controls">
						<span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
						<span class="item-order hide-if-js">
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-up-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
							|
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-down-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">&#8595;</abbr></a>
						</span>
						<a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php esc_attr_e('Edit Menu Item'); ?>" href="<?php
							echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
						?>"></a>
					</span>
				</dt>
			</dl>

			<div class="menu-item-settings wp-clearfix" id="menu-item-settings-<?php echo $item_id; ?>">
				<?php if( 'custom' == $item->type ) : ?>
					<p class="field-url description description-wide">
						<label for="edit-menu-item-url-<?php echo $item_id; ?>">
							<?php _e( 'URL' ); ?><br />
							<input type="text" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
						</label>
					</p>
				<?php endif;
				
				echo '<p class="field-menu-link description description-wide">
					<label for="edit-menu-item-menu-link-' . $item->ID . '">Profile link</label>
					<select id="edit-menu-item-menu-link-'.esc_attr($item->ID).'" class="widefat" name="menu-item-menu_link['.esc_attr($item->ID).']">
						<option value="none">'.esc_html__('Select the link','vbegy').'</option>
						<option value="top_panel" '.selected($item->menu_link,'top_panel',false).'>'.esc_html__('Top profile panel','vbegy').'</option>
						<option value="side_panel" '.selected($item->menu_link,'side_panel',false).'>'.esc_html__('Side profile panel','vbegy').'</option>
						<option value="profile" '.selected($item->menu_link,'profile',false).'>'.esc_html__('Profile','vbegy').'</option>
						<option value="messages" '.selected($item->menu_link,'messages',false).'>'.esc_html__('Messages','vbegy').'</option>
						<option value="questions" '.selected($item->menu_link,'questions',false).'>'.esc_html__('Questions','vbegy').'</option>
						<option value="polls" '.selected($item->menu_link,'polls',false).'>'.esc_html__('Polls','vbegy').'</option>
						<option value="asked_questions" '.selected($item->menu_link,'asked_questions',false).'>'.esc_html__('Asked Questions','vbegy').'</option>
						<option value="paid_questions" '.selected($item->menu_link,'paid_questions',false).'>'.esc_html__('Paid Questions','vbegy').'</option>
						<option value="answers" '.selected($item->menu_link,'answers',false).'>'.esc_html__('Answers','vbegy').'</option>
						<option value="best_answers" '.selected($item->menu_link,'best_answers',false).'>'.esc_html__('Best Answers','vbegy').'</option>
						<option value="favorites" '.selected($item->menu_link,'favorites',false).'>'.esc_html__('Favorites','vbegy').'</option>
						<option value="followed" '.selected($item->menu_link,'followed',false).'>'.esc_html__('Followed','vbegy').'</option>
						<option value="points" '.selected($item->menu_link,'points',false).'>'.esc_html__('Points','vbegy').'</option>
						<option value="followers" '.selected($item->menu_link,'followers',false).'>'.esc_html__('Followers','vbegy').'</option>
						<option value="following" '.selected($item->menu_link,'following',false).'>'.esc_html__('Following','vbegy').'</option>
						<option value="posts" '.selected($item->menu_link,'posts',false).'>'.esc_html__('Posts','vbegy').'</option>
						<option value="comments" '.selected($item->menu_link,'comments',false).'>'.esc_html__('Comments','vbegy').'</option>
						<option value="followers_questions" '.selected($item->menu_link,'followers_questions',false).'>'.esc_html__('Followers Questions','vbegy').'</option>
						<option value="followers_answers" '.selected($item->menu_link,'followers_answers',false).'>'.esc_html__('Followers Answers','vbegy').'</option>
						<option value="followers_posts" '.selected($item->menu_link,'followers_posts',false).'>'.esc_html__('Followers Posts','vbegy').'</option>
						<option value="followers_comments" '.selected($item->menu_link,'followers_comments',false).'>'.esc_html__('Followers Comments','vbegy').'</option>
						<option value="edit_profile" '.selected($item->menu_link,'edit_profile',false).'>'.esc_html__('Edit Profile','vbegy').'</option>
						<option value="activities" '.selected($item->menu_link,'activities',false).'>'.esc_html__('Activity Log','vbegy').'</option>
						<option value="notifications" '.selected($item->menu_link,'notifications',false).'>'.esc_html__('Notifications','vbegy').'</option>
						<option value="logout" '.selected($item->menu_link,'logout',false).'>'.esc_html__('Logout','vbegy').'</option>
					</select>
					<br><span class="description">Select the link type link profile</span>
				</p>';?>
				<p class="description description-thin">
					<label for="edit-menu-item-title-<?php echo $item_id; ?>">
						<?php _e( 'Navigation Label' ); ?><br />
						<input type="text" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
					</label>
				</p>
				<p class="description description-thin">
					<label for="edit-menu-item-attr-title-<?php echo $item_id; ?>">
						<?php _e( 'Title Attribute' ); ?><br />
						<input type="text" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
					</label>
				</p>
				<p class="field-link-target description">
					<label for="edit-menu-item-target-<?php echo $item_id; ?>">
						<input type="checkbox" id="edit-menu-item-target-<?php echo $item_id; ?>" value="_blank" name="menu-item-target[<?php echo $item_id; ?>]"<?php checked( $item->target, '_blank' ); ?> />
						<?php _e( 'Open link in a new window/tab' ); ?>
					</label>
				</p>
				<p class="field-css-classes description description-thin">
					<label for="edit-menu-item-classes-<?php echo $item_id; ?>">
						<?php _e( 'CSS Classes (optional)' ); ?><br />
						<input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
					</label>
				</p>
				<p class="field-xfn description description-thin">
					<label for="edit-menu-item-xfn-<?php echo $item_id; ?>">
						<?php _e( 'Link Relationship (XFN)' ); ?><br />
						<input type="text" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
					</label>
				</p>

				<?php
				// This is the added section
				do_action( 'wp_nav_menu_item_custom_fields', $item_id, $item, $depth, $args );
				// end added section
				?>

				<p class="field-description description description-wide">
					<label for="edit-menu-item-description-<?php echo $item_id; ?>">
						<?php _e( 'Description' ); ?><br />
						<textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
						<span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
					</label>
				</p>

				<p class="field-move hide-if-no-js description description-wide">
					<label>
						<span><?php _e( 'Move' ); ?></span>
						<a href="#" class="menus-move menus-move-up" data-dir="up"><?php _e( 'Up one' ); ?></a>
						<a href="#" class="menus-move menus-move-down" data-dir="down"><?php _e( 'Down one' ); ?></a>
						<a href="#" class="menus-move menus-move-left" data-dir="left"></a>
						<a href="#" class="menus-move menus-move-right" data-dir="right"></a>
						<a href="#" class="menus-move menus-move-top" data-dir="top"><?php _e( 'To the top' ); ?></a>
					</label>
				</p>

				<div class="menu-item-actions description-wide submitbox">
					<?php if( 'custom' != $item->type && $original_title !== false ) : ?>
						<p class="link-to-original">
							<?php printf( __('Original: %s'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
						</p>
					<?php endif; ?>
					<a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
					echo wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'delete-menu-item',
								'menu-item' => $item_id,
							),
							admin_url( 'nav-menus.php' )
						),
						'delete-menu_item_' . $item_id
					); ?>"><?php _e( 'Remove' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo $item_id; ?>" href="<?php echo esc_url( add_query_arg( array( 'edit-menu-item' => $item_id, 'cancel' => time() ), admin_url( 'nav-menus.php' ) ) );
						?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel'); ?></a>
				</div>

				<input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
				<input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
				<input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
				<input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
				<input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
				<input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
			</div><!-- .menu-item-settings-->
			<ul class="menu-item-transport"></ul>
		<?php
		$output .= ob_get_clean();
	}
}

// Add Menu Class to FrontEnd
add_filter('nav_menu_css_class' , 'ask_menu_class' , 10 , 2);
function ask_menu_class( $classes, $item ) {
	if (isset($item->menu_link) && ($item->menu_link == "top_panel" || $item->menu_link == "side_panel")) {
		if ($item->menu_link == "top_panel") {
			$classes[] = 'login-panel-link';
		}else if ($item->menu_link == "side_panel") {
			$classes[] = 'login-side-link';
		}
	}else if (isset($item->menu_link) && $item->menu_link != "" && $item->menu_link != "none" && is_user_logged_in) {
		$ask_user_id = get_current_user_id();
		if ($item->menu_link == "profile") {
			$classes[] = 'li-profile';
		}else if ($item->menu_link == "messages") {
			$classes[] = 'li-messages';
		}else if ($item->menu_link == "edit_profile") {
			$classes[] = 'li-edit-profile';
		}else if ($item->menu_link == "followers") {
			$classes[] = 'li-followers';
		}else if ($item->menu_link == "following") {
			$classes[] = 'li-following';
		}else if ($item->menu_link == "notifications") {
			$classes[] = 'li-notifications';
		}else if ($item->menu_link == "activities") {
			$classes[] = 'li-activities';
		}else if ($item->menu_link == "points") {
			$classes[] = 'li-points';
		}else if ($item->menu_link == "questions") {
			$classes[] = 'li-questions';
		}else if ($item->menu_link == "polls") {
			$classes[] = 'li-polls';
		}else if ($item->menu_link == "asked_questions") {
			$classes[] = 'li-asked-questions';
		}else if ($item->menu_link == "paid_questions") {
			$classes[] = 'li-paid-questions';
		}else if ($item->menu_link == "answers") {
			$classes[] = 'li-answers';
		}else if ($item->menu_link == "best_answers") {
			$classes[] = 'li-best-answers';
		}else if ($item->menu_link == "followed") {
			$classes[] = 'li-followed';
		}else if ($item->menu_link == "favorites") {
			$classes[] = 'li-favorites';
		}else if ($item->menu_link == "posts") {
			$classes[] = 'li-posts';
		}else if ($item->menu_link == "comments") {
			$classes[] = 'li-comments';
		}else if ($item->menu_link == "followers_questions") {
			$classes[] = 'li-followers-questions';
		}else if ($item->menu_link == "followers_answers") {
			$classes[] = 'li-followers-answers';
		}else if ($item->menu_link == "followers_posts") {
			$classes[] = 'li-followers-posts';
		}else if ($item->menu_link == "followers_comments") {
			$classes[] = 'li-followers-comments';
		}else if ($item->menu_link == "logout") {
			$classes[] = 'li-logout';
		}
		
		if (($item->menu_link == "profile" && is_author()) || ($item->menu_link == "edit_profile" && is_page_template("template-edit_profile.php")) || ($item->menu_link == "followers" && is_page_template("template-followers.php")) || ($item->menu_link == "following" && is_page_template("template-i_follow.php")) || ($item->menu_link == "notifications" && is_page_template("template-notifications.php")) || ($item->menu_link == "activities" && is_page_template("template-activity_log.php")) || ($item->menu_link == "messages" && is_page_template("template-messages.php")) || ($item->menu_link == "questions" && is_page_template("template-user_question.php")) || ($item->menu_link == "answers" && is_page_template("template-user_answer.php")) || ($item->menu_link == "best_answers" && is_page_template("template-user_best_answer.php")) || ($item->menu_link == "points" && is_page_template("template-user_points.php")) || ($item->menu_link == "polls" && is_page_template("template-user_polls.php")) || ($item->menu_link == "asked_questions" && is_page_template("template-asked_question.php")) || ($item->menu_link == "paid_questions" && is_page_template("template-user_paid_question.php")) || ($item->menu_link == "followed" && is_page_template("template-user_followed_questions.php")) || ($item->menu_link == "favorites" && is_page_template("template-user_favorite_questions.php")) || ($item->menu_link == "posts" && is_page_template("template-user_posts.php")) || ($item->menu_link == "comments" && is_page_template("template-user_comments.php")) || ($item->menu_link == "followers_questions" && is_page_template("template-question_follow.php")) || ($item->menu_link == "followers_answers" && is_page_template("template-answer_follow.php")) || ($item->menu_link == "followers_posts" && is_page_template("template-post_follow.php")) || ($item->menu_link == "followers_comments" && is_page_template("template-comment_follow.php"))) {
			$classes[] = 'current-menu-item current_page_item';
		}
	}
    return $classes;
}

// Menu FrontEnd
add_filter( 'walker_nav_menu_start_el', 'ask_walker_nav_menu_start_el', 10, 4 );
function ask_walker_nav_menu_start_el( $item_output, $item, $depth, $args ) {
	// link attributes
	$attributes  = ! empty($item->attr_title)? ' title="'.esc_attr($item->attr_title).'"' : '';
	$attributes .= ! empty($item->target)    ? ' target="'.esc_attr($item->target  ).'"' : '';
	$attributes .= ! empty($item->xfn)       ? ' rel="'  .esc_attr($item->xfn     ).'"' : '';
	if (isset($item->menu_link) && $item->menu_link != "" && $item->menu_link != "none" && is_user_logged_in) {
		$ask_user_id = get_current_user_id();
		
		$get_lang = esc_attr(get_query_var("lang"));
		$get_lang_array = array();
		if (isset($get_lang) && $get_lang != "") {
			$get_lang_array = array("lang" => $get_lang);
		}
		
		if ($item->menu_link == "profile") {
			$attributes .= ' href="'.esc_url(vpanel_get_user_url($ask_user_id)).'"';
		}else if ($item->menu_link == "messages") {
			$attributes .= ' href="'.esc_url(get_page_link(vpanel_options('messages_page'))).'"';
		}else if ($item->menu_link == "edit_profile") {
			$attributes .= ' href="'.esc_url(get_page_link(vpanel_options('user_edit_profile_page'))).'"';
		}else if ($item->menu_link == "followers") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('followers_user_page')))).'"';
		}else if ($item->menu_link == "following") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('i_follow_user_page')))).'"';
		}else if ($item->menu_link == "notifications") {
			$attributes .= ' href="'.esc_url(get_page_link(vpanel_options('notifications_page'))).'"';
		}else if ($item->menu_link == "activities") {
			$attributes .= ' href="'.esc_url(get_page_link(vpanel_options('activity_log_page'))).'"';
		}else if ($item->menu_link == "points") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('point_user_page')))).'"';
		}else if ($item->menu_link == "questions") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('question_user_page')))).'"';
		}else if ($item->menu_link == "polls") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('polls_user_page')))).'"';
		}else if ($item->menu_link == "asked_questions") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('asked_question_user_page')))).'"';
		}else if ($item->menu_link == "paid_questions") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('paid_question')))).'"';
		}else if ($item->menu_link == "answers") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('answer_user_page')))).'"';
		}else if ($item->menu_link == "best_answers") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('best_answer_user_page')))).'"';
		}else if ($item->menu_link == "followed") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('followed_user_page')))).'"';
		}else if ($item->menu_link == "favorites") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('favorite_user_page')))).'"';
		}else if ($item->menu_link == "posts") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('post_user_page')))).'"';
		}else if ($item->menu_link == "comments") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('comment_user_page')))).'"';
		}else if ($item->menu_link == "followers_questions") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('follow_question_page')))).'"';
		}else if ($item->menu_link == "followers_answers") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('follow_answer_page')))).'"';
		}else if ($item->menu_link == "followers_posts") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('follow_post_page')))).'"';
		}else if ($item->menu_link == "followers_comments") {
			$attributes .= ' href="'.esc_url(add_query_arg(array_merge(array("u" => esc_attr($ask_user_id),$get_lang_array)),get_page_link(vpanel_options('follow_comment_page')))).'"';
		}else if ($item->menu_link == "logout") {
			$protocol = is_ssl() ? 'https' : 'http';
			$attributes .= ' href="'.wp_logout_url(wp_unslash( $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])).'"';
		}else {
			$attributes .= ' href="'.esc_url($item->url).'"';
		}
	}else {
		$attributes .= ' href="'.esc_url($item->url).'"';
	}
	$item_output = '';
	if (isset($args->before)) {
		$item_output = $args->before;
	}
	
	if ((isset($item->menu_link) && $item->menu_link != "" && $item->menu_link != "none" && $item->menu_link != "top_panel" && $item->menu_link != "side_panel" && !is_user_logged_in)) {
		
	}else {
		$item_output .= '<a class="" '.$attributes.'>';
		$item_output .= apply_filters('the_title',$item->title,$item->ID);
		$item_output .= '</a>';
	}
	
	if (isset($args->after)) {
		$item_output .= $args->after;
	}
	return $item_output;
}