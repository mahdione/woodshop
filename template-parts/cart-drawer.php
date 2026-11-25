<?php
/**
 * Cart drawer.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'WC' ) ) {
	return;
}
?>
<div class="cart-drawer" id="cart-drawer" data-cart-drawer aria-hidden="true">
	<div class="cart-drawer__overlay" data-cart-close></div>

	<aside class="cart-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title" tabindex="-1">
		<header class="cart-drawer__head">
			<h2 class="cart-drawer__title" id="cart-drawer-title">
				<?php esc_html_e( 'سبد خرید', 'woodshop' ); ?>
				<span class="cart-drawer__count" data-cart-count><?php echo esc_html( woodshop_persian_digits( (string) woodshop_cart_count() ) ); ?></span>
			</h2>
			<button type="button" class="cart-drawer__close" data-cart-close aria-label="<?php esc_attr_e( 'بستن سبد خرید', 'woodshop' ); ?>">
				<?php echo woodshop_icon( 'close', '', 18 ); ?>
			</button>
		</header>

		<div class="cart-drawer__body" data-cart-body>
			<?php echo woodshop_cart_drawer_body(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<span class="cart-drawer__loader" aria-hidden="true"></span>
	</aside>
</div>

<div id="woodshop-cart-status" class="screen-reader-text" role="status" aria-live="polite"></div>
