<?php
/**
 * My Account dashboard.
 *
 * @package Woodshop
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$ws_user_id      = get_current_user_id();
$ws_order_count  = function_exists( 'wc_get_customer_order_count' ) ? (int) wc_get_customer_order_count( $ws_user_id ) : 0;
$ws_total_spent  = function_exists( 'wc_get_customer_total_spent' ) ? (float) wc_get_customer_total_spent( $ws_user_id ) : 0;
$ws_recent       = function_exists( 'wc_get_orders' ) ? wc_get_orders(
	array(
		'customer_id' => $ws_user_id,
		'limit'       => 4,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
) : array();
$ws_account_url  = wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ) );
$ws_orders_url   = wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) );
$ws_name         = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
?>

<div class="account-overview">
	<div class="account-greeting">
		<div>
			<span class="eyebrow"><?php echo woodshop_icon( 'home', 'eyebrow__icon', 17 ); ?><span><?php esc_html_e( 'داشبورد', 'woodshop' ); ?></span></span>
			<h2 class="account-greeting__title"><?php echo esc_html( sprintf( /* translators: %s: customer name. */ __( 'سلام %s، خوش آمدید', 'woodshop' ), $ws_name ) ); ?></h2>
			<p class="account-greeting__text"><?php esc_html_e( 'خلاصه‌ای از سفارش‌ها و اطلاعات حساب شما در یک نگاه.', 'woodshop' ); ?></p>
		</div>
		<a class="btn btn--sm" href="<?php echo esc_url( wc_logout_url() ); ?>"><?php echo woodshop_icon( 'logout', '', 15 ); ?><span><?php esc_html_e( 'خروج از حساب', 'woodshop' ); ?></span></a>
	</div>

	<ul class="account-stats">
		<li>
			<span class="account-stats__icon"><?php echo woodshop_icon( 'receipt', '', 20 ); ?></span>
			<strong><?php echo esc_html( woodshop_persian_digits( (string) $ws_order_count ) ); ?></strong>
			<em><?php esc_html_e( 'سفارش ثبت‌شده', 'woodshop' ); ?></em>
		</li>
		<li>
			<span class="account-stats__icon"><?php echo woodshop_icon( 'basket', '', 20 ); ?></span>
			<strong><?php echo wp_kses_post( wc_price( $ws_total_spent ) ); ?></strong>
			<em><?php esc_html_e( 'مجموع خرید', 'woodshop' ); ?></em>
		</li>
		<li>
			<span class="account-stats__icon"><?php echo woodshop_icon( 'user', '', 20 ); ?></span>
			<strong class="account-stats__email"><?php echo esc_html( $current_user->user_email ); ?></strong>
			<em><?php esc_html_e( 'ایمیل حساب', 'woodshop' ); ?></em>
		</li>
	</ul>

	<div class="account-recent">
		<div class="account-recent__head">
			<h3><?php esc_html_e( 'آخرین سفارش‌ها', 'woodshop' ); ?></h3>
			<a href="<?php echo esc_url( $ws_orders_url ); ?>"><?php esc_html_e( 'مشاهده همه سفارش‌ها', 'woodshop' ); ?><?php echo woodshop_icon( 'chevron-right', '', 13 ); ?></a>
		</div>

		<?php if ( $ws_recent ) : ?>
			<ul class="account-orders">
				<?php foreach ( $ws_recent as $ws_order ) : ?>
					<li class="account-order">
						<a class="account-order__number" href="<?php echo esc_url( $ws_order->get_view_order_url() ); ?>">#<?php echo esc_html( $ws_order->get_order_number() ); ?></a>
						<span class="account-order__date"><?php echo esc_html( wc_format_datetime( $ws_order->get_date_created(), 'j F Y' ) ); ?></span>
						<span class="account-order__status"><?php echo esc_html( wc_get_order_status_name( $ws_order->get_status() ) ); ?></span>
						<span class="account-order__total"><?php echo wp_kses_post( $ws_order->get_formatted_order_total() ); ?></span>
						<a class="account-order__link" href="<?php echo esc_url( $ws_order->get_view_order_url() ); ?>"><?php esc_html_e( 'جزئیات', 'woodshop' ); ?><?php echo woodshop_icon( 'chevron-right', '', 12 ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<div class="account-empty">
				<p><?php esc_html_e( 'هنوز سفارشی ثبت نکرده‌اید. از فروشگاه ما دیدن کنید و اولین سفارش خود را ثبت کنید.', 'woodshop' ); ?></p>
				<a class="btn btn--sm" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'رفتن به فروشگاه', 'woodshop' ); ?></a>
			</div>
		<?php endif; ?>
	</div>

	<div class="account-links">
		<a class="account-link-card" href="<?php echo esc_url( $ws_account_url ); ?>">
			<span class="account-link-card__icon"><?php echo woodshop_icon( 'user', '', 20 ); ?></span>
			<span>
				<strong><?php esc_html_e( 'اطلاعات کاربری', 'woodshop' ); ?></strong>
				<em><?php esc_html_e( 'نام، ایمیل و رمز عبور', 'woodshop' ); ?></em>
			</span>
			<?php echo woodshop_icon( 'chevron-right', '', 14 ); ?>
		</a>
		<a class="account-link-card" href="<?php echo esc_url( $ws_orders_url ); ?>">
			<span class="account-link-card__icon"><?php echo woodshop_icon( 'receipt', '', 20 ); ?></span>
			<span>
				<strong><?php esc_html_e( 'پیگیری سفارش‌ها', 'woodshop' ); ?></strong>
				<em><?php esc_html_e( 'وضعیت و جزئیات خریدها', 'woodshop' ); ?></em>
			</span>
			<?php echo woodshop_icon( 'chevron-right', '', 14 ); ?>
		</a>
	</div>
</div>

<?php
/**
 * My Account dashboard hook for compatibility with plugins.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );
