<?php
/**
 * My Account navigation.
 *
 * @package Woodshop
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$ws_items = wc_get_account_menu_items();
$ws_icons = array(
	'dashboard'       => 'home',
	'orders'          => 'receipt',
	'edit-account'    => 'user',
	'customer-logout' => 'logout',
);

do_action( 'woocommerce_before_account_navigation' );
?>
<nav class="woocommerce-MyAccount-navigation account-nav" aria-label="<?php esc_attr_e( 'مسیرهای حساب کاربری', 'woodshop' ); ?>">
	<ul>
		<?php foreach ( $ws_items as $ws_endpoint => $ws_label ) : ?>
			<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $ws_endpoint ) ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $ws_endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $ws_endpoint ) ? 'aria-current="page"' : ''; ?>>
					<span class="account-nav__icon"><?php echo woodshop_icon( $ws_icons[ $ws_endpoint ] ?? 'chevron-right', '', 18 ); ?></span>
					<span><?php echo esc_html( $ws_label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php do_action( 'woocommerce_after_account_navigation' ); ?>
