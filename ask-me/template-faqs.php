<?php /* Template Name: FAQs */
get_header();?>
		<div class="page-content">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post();?>
			<div class="boxedtitle page-title">
				<h2><?php the_title();?></h2>
			</div>
			<?php the_content();
		endwhile; endif;
		$vbegy_faqs = rwmb_meta('vbegy_faqs','type=elements',$post->ID);
		if (isset($vbegy_faqs) && is_array($vbegy_faqs)) {
			$faqs_i = 0;
			echo "<div class='accordion toggle-accordion'>";
			foreach ($vbegy_faqs as $faqs_key => $faqs) {
				$faqs_title = $faqs["text"];
				$faqs_content = $faqs["textarea"];
				echo "<div class='accordion-warp'>
					<h4 class='accordion-title'><a href='#'>".$faqs_title."</a></h4>
					<div class='accordion-inner'>".ask_kses_stip($faqs_content)."</div>
				</div>";
			}
			echo "</div>";
		}?>
	</div><!-- End page-content -->
<?php get_footer();?>