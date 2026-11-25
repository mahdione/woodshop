<?php
/**
 * Featured WooCommerce products.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$ws_limit = (int) woodshop_option( 'products.limit', 4 );
$ws_cat   = (int) woodshop_option( 'products.category_id', 0 );

$ws_args = array(
	'status'  => 'publish',
	'limit'   => $ws_limit > 0 ? $ws_limit : 4,
	'orderby' => 'date',
	'order'   => 'DESC',
	'return'  => 'objects',
);

if ( $ws_cat ) {
	$ws_args['category'] = array( get_term_field( 'slug', $ws_cat, 'product_cat' ) );
}

if ( woodshop_option( 'products.featured_only', 0 ) ) {
	$ws_args['featured'] = true;
}

$ws_products = wc_get_products( $ws_args );

if ( empty( $ws_products ) && ! empty( $ws_args['featured'] ) ) {
	unset( $ws_args['featured'] );
	$ws_products = wc_get_products( $ws_args );
}

if ( empty( $ws_products ) ) {
	return;
}
?>
<section class="section section--alt products-section" id="shop">
	<div class="wrap">
		<div class="section-head section-head--center reveal">
			<?php woodshop_eyebrow( woodshop_option( 'products.eyebrow', 'From Our Workshop' ) ); ?>
			<h2 class="section-title"><?php echo esc_html( woodshop_option( 'products.title', 'Crafted Pieces Ready to Order' ) ); ?></h2>
			<p class="section-text"><?php echo esc_html( woodshop_option( 'products.text', 'Solid timber furniture and made-to-order joinery, built in our workshop and delivered to your door.' ) ); ?></p>
		</div>

		<ul class="ws-products">
			<?php foreach ( $ws_products as $ws_product ) : ?>
				<?php woodshop_product_card( $ws_product ); ?>
			<?php endforeach; ?>
		</ul>

		<div class="section-actions reveal">
			<a class="btn" href="<?php echo esc_url( woodshop_shop_url() ); ?>"><?php echo esc_html( woodshop_option( 'products.button_label', 'Shop All Products' ) ); ?></a>
		</div>
	</div>
</section>
