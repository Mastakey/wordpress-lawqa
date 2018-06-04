<?php echo "<div class='header-notifications'>";
	$num = get_user_meta($user_id,$user_id.'_new_notifications',true);
	$num = ($num == ""?0:$num);
	echo '<a href="'.get_page_link(vpanel_options('notifications_page')).'" class="notifications_control"><i class="fa fa-bell"></i>
		<span class="numofitems">'.$num.'</span>
	</a>
	<div class="notifications-wrapper">';
		ask_notifications($user_id);
	echo '</div>
</div>';