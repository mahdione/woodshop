<?php
/**
 * WooCommerce integration.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/**
 * Open the shop content wrapper.
 */
function woodshop_wc_wrapper_start() {
	echo '<div id="primary" class="site-main ws-page ws-shop">';
	echo '<div class="wrap">';

	if ( is_shop() || is_product_taxonomy() || is_product() ) {
		woodshop_breadcrumb();
	}
}
add_action( 'woocommerce_before_main_content', 'woodshop_wc_wrapper_start', 10 );

/**
 * Close the shop content wrapper.
 */
function woodshop_wc_wrapper_end() {
	echo '</div></div>';
}
add_action( 'woocommerce_after_main_content', 'woodshop_wc_wrapper_end', 10 );

/**
 * Products per row on the shop archive.
 *
 * @return int
 */
function woodshop_loop_columns() {
	return 3;
}
add_filter( 'loop_shop_columns', 'woodshop_loop_columns', 20 );

/**
 * Products per page.
 *
 * @return int
 */
function woodshop_products_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'woodshop_products_per_page', 20 );

/**
 * Add the Iranian toman currency.
 *
 * @param array<string, string> $currencies Currencies.
 * @return array<string, string>
 */
function woodshop_add_toman_currency( $currencies ) {
	$currencies['IRT'] = __( 'تومان ایران', 'woodshop' );

	return $currencies;
}
add_filter( 'woocommerce_currencies', 'woodshop_add_toman_currency' );

/**
 * Toman symbol.
 *
 * @param string $symbol   Currency symbol.
 * @param string $currency Currency code.
 * @return string
 */
function woodshop_toman_symbol( $symbol, $currency ) {
	return 'IRT' === $currency ? 'تومان' : $symbol;
}
add_filter( 'woocommerce_currency_symbol', 'woodshop_toman_symbol', 10, 2 );

/**
 * Persian digits inside formatted WooCommerce prices.
 *
 * @param string $formatted Formatted price.
 * @return string
 */
function woodshop_persian_price_digits( $formatted ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $formatted;
	}

	return woodshop_persian_digits( $formatted );
}
add_filter( 'formatted_woocommerce_price', 'woodshop_persian_price_digits' );

/**
 * Add a body class when the cart has items.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function woodshop_cart_body_classes( $classes ) {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		$cart = function_exists( 'woodshop_get_cart' ) ? woodshop_get_cart() : ( function_exists( 'WC' ) ? WC()->cart : null );

		if ( $cart && ! $cart->is_empty() ) {
			$classes[] = 'ws-cart-filled';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'woodshop_cart_body_classes' );

/**
 * Show cross-sells under the full cart instead of inside the summary column.
 */
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10 );
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display', 15 );
