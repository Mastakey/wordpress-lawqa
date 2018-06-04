<?php
/**
 * Single Product Rating
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/rating.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

if ( 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
	return;
}

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

if ( $rating_count > 0 ) : ?>
	
	<div class="woocommerce-product-rating" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
		<?php global $post,$product;$cat_count = sizeof(get_the_terms($post->ID,'product_cat'));
		$posts_meta = vpanel_options("post_meta");
		$post_meta_s = rwmb_meta('vbegy_post_meta_s','checkbox',$post->ID);
		$custom_page_setting = rwmb_meta('vbegy_custom_page_setting','checkbox',$post->ID);
		if (($posts_meta == 1 && $post_meta_s == "") || ($posts_meta == 1 && isset($custom_page_setting) && $custom_page_setting == 0) || ($posts_meta == 1 && isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_meta_s) && $post_meta_s != 0) || (isset($custom_page_setting) && $custom_page_setting == 1 && isset($post_meta_s) && $post_meta_s == 1)) {
			echo $product->get_categories( ', ', '<h4 class="posted_in">' . _n( 'Category :', 'Categories :', $cat_count, 'woocommerce' ) . ' ', '.</h4>' );?>
			<h4><?php _e('Reviews :', 'vbegy'); ?>	</h4>
			<?php echo wc_get_rating_html( $average, $rating_count );?>
			<!--
			<?php if ( comments_open() ) : ?><a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $rating_count, 'woocommerce' ), '<span itemprop="reviewCount" class="count">' . $rating_count . '</span>' ); ?>)</a><?php endif ?>
			-->
		<?php }?>
	</div>

<?php endif; ?>
