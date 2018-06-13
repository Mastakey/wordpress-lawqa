<?php
/* Template Name: Test Question */
get_header();
?>
<h2>Recent Questions 18</h2>
<?php
$args = array(
	"post_type" => "question",
	"meta_key" => "question_poll",
	"meta_value" => 2
);
$questions = new WP_Query( $args );
if ($questions->have_posts() ) :
	while ($questions->have_posts() ) : $questions->the_post();
?>
		<div class="recent-q">
			<div class="recent-q-container">
				<div class="recent-q-user">
					<?php 
						$author_url = get_avatar_url( get_the_author_meta('ID') );
						$author_name = get_the_author();
					?>
					<a href='<?php echo $author_url; ?>'><?php echo $author_name; ?></a>
				</div>
				<div class="recent-q-content">
					<div class="recent-q-title">
						<a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a>
					</div>
				</div>
			</div>
		</div>
<?php
	endwhile;
	wp_reset_postdata();
endif;
/*
// The Query
$args = array( 
	'meta_query' => array(
		'key' => 'question_poll',
		'value' => '2'
	)
);
$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) {
	echo '<ul>';
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		echo '<li>' . get_the_title() . '</li>';
	}
	echo '</ul>';
	
	wp_reset_postdata();
} else {
	// no posts found
}
*/
?>
<?php
get_footer();
?>