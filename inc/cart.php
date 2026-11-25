<?php
/**
 * Cart drawer, live cart count and AJAX add/remove.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the WooCommerce cart, loading it when needed (AJAX requests).
 *
 * @return WC_Cart|null
 */
function woodshop_get_cart() {
	if ( ! function_exists( 'WC' ) ) {
		return null;
	}

	if ( ! WC()->cart && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}

	return WC()->cart;
}

/**
 * Cart item count.
 *
 * @return int
 */
function woodshop_cart_count() {
	$cart = woodshop_get_cart();

	return $cart ? (int) $cart->get_cart_contents_count() : 0;
}

/**
 * Drawer body markup. Used on page load and returned by AJAX.
 *
 * @return string
 */
function woodshop_cart_drawer_body() {
	$cart = woodshop_get_cart();

	if ( ! $cart ) {
		return '';
	}

	ob_start();

	if ( $cart->is_empty() ) :
		?>
		<div class="cart-drawer__empty">
			<span class="cart-drawer__empty-icon"><?php echo woodshop_icon( 'basket', '', 34 ); ?></span>
			<p><?php esc_html_e( 'سبد خرید شما خالی است.', 'woodshop' ); ?></p>
			<a class="btn btn--sm" href="<?php echo esc_url( woodshop_shop_url() ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'woodshop' ); ?></a>
		</div>
		<?php
	else :
		?>
		<ul class="cart-drawer__items">
			<?php foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) : ?>
				<?php
				$product = $cart_item['data'];

				if ( ! $product instanceof WC_Product ) {
					continue;
				}

				$thumbnail = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
				$thumbnail = $thumbnail ? $thumbnail : wc_placeholder_img_src( 'woocommerce_thumbnail' );
				?>
				<li class="cart-drawer__item">
					<a class="cart-drawer__thumb" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
						<img src="<?php echo esc_url( $thumbnail ); ?>" alt="" width="72" height="72" loading="lazy" decoding="async">
					</a>
					<div class="cart-drawer__item-body">
						<a class="cart-drawer__item-name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
						<div class="cart-drawer__item-meta">
							<span class="cart-drawer__qty"><?php echo esc_html( sprintf( /* translators: %s: quantity. */ __( 'تعداد: %s', 'woodshop' ), woodshop_persian_digits( (string) $cart_item['quantity'] ) ) ); ?></span>
							<span class="cart-drawer__price"><?php echo wp_kses_post( $cart->get_product_subtotal( $product, $cart_item['quantity'] ) ); ?></span>
						</div>
						<button type="button" class="cart-drawer__remove" data-cart-remove="<?php echo esc_attr( $cart_item_key ); ?>"><?php esc_html_e( 'حذف', 'woodshop' ); ?></button>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="cart-drawer__footer">
			<div class="cart-drawer__subtotal">
				<span><?php esc_html_e( 'جمع سبد خرید', 'woodshop' ); ?></span>
				<strong><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></strong>
			</div>
			<a class="btn cart-drawer__checkout" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'تسویه‌حساب', 'woodshop' ); ?></a>
			<a class="cart-drawer__view-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'مشاهده سبد خرید', 'woodshop' ); ?></a>
		</div>
		<?php
	endif;

	return (string) ob_get_clean();
}

/**
 * Cart payload for JS.
 *
 * @return array<string, mixed>
 */
function woodshop_cart_payload() {
	$cart  = woodshop_get_cart();
	$count = $cart ? (int) $cart->get_cart_contents_count() : 0;

	return array(
		'count'    => $count,
		'html'     => woodshop_cart_drawer_body(),
		'subtotal' => $cart ? wp_strip_all_tags( $cart->get_cart_subtotal() ) : '',
		'cartUrl'  => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
		'checkoutUrl' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '',
	);
}

/**
 * Verify the cart nonce for AJAX requests.
 */
function woodshop_cart_verify_nonce() {
	check_ajax_referer( 'woodshop_cart', 'nonce' );
}

/**
 * AJAX: return the current cart.
 */
function woodshop_ajax_get_cart() {
	woodshop_cart_verify_nonce();

	if ( ! function_exists( 'WC' ) ) {
		wp_send_json_error( array( 'message' => __( 'فروشگاه در دسترس نیست.', 'woodshop' ) ), 400 );
	}

	wp_send_json_success( woodshop_cart_payload() );
}
add_action( 'wp_ajax_woodshop_get_cart', 'woodshop_ajax_get_cart' );
add_action( 'wp_ajax_nopriv_woodshop_get_cart', 'woodshop_ajax_get_cart' );

/**
 * AJAX: add a product.
 */
function woodshop_ajax_add_to_cart() {
	woodshop_cart_verify_nonce();

	if ( ! function_exists( 'WC' ) ) {
		wp_send_json_error( array( 'message' => __( 'فروشگاه در دسترس نیست.', 'woodshop' ) ), 400 );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	$quantity   = isset( $_POST['quantity'] ) ? max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) : 1;
	$product    = $product_id ? wc_get_product( $product_id ) : false;

	if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() || $product->is_type( 'variable' ) ) {
		wp_send_json_error( array( 'message' => __( 'این محصول در حال حاضر قابل افزودن به سبد نیست.', 'woodshop' ) ), 400 );
	}

	$cart = woodshop_get_cart();

	if ( ! $cart ) {
		wp_send_json_error( array( 'message' => __( 'سبد خرید در دسترس نیست.', 'woodshop' ) ), 400 );
	}

	wc_clear_notices();
	$added = $cart->add_to_cart( $product_id, $quantity );

	if ( ! $added ) {
		$notices = wc_get_notices( 'error' );
		$message = ! empty( $notices ) ? wp_strip_all_tags( $notices[0]['notice'] ) : __( 'افزودن محصول انجام نشد. دوباره تلاش کنید.', 'woodshop' );
		wc_clear_notices();
		wp_send_json_error( array( 'message' => $message ), 400 );
	}

	$cart->calculate_totals();

	wp_send_json_success(
		array(
			'payload' => woodshop_cart_payload(),
			'message' => __( 'محصول به سبد خرید اضافه شد.', 'woodshop' ),
		)
	);
}
add_action( 'wp_ajax_woodshop_add_to_cart', 'woodshop_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_woodshop_add_to_cart', 'woodshop_ajax_add_to_cart' );

/**
 * AJAX: remove a line item.
 */
function woodshop_ajax_remove_cart_item() {
	woodshop_cart_verify_nonce();

	$cart = woodshop_get_cart();
	$key  = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';

	if ( ! $cart || ! $key ) {
		wp_send_json_error( array( 'message' => __( 'حذف محصول انجام نشد.', 'woodshop' ) ), 400 );
	}

	$removed = $cart->remove_cart_item( $key );
	$cart->calculate_totals();

	if ( ! $removed ) {
		wp_send_json_error( array( 'message' => __( 'این محصول در سبد خرید پیدا نشد.', 'woodshop' ) ), 400 );
	}

	wp_send_json_success(
		array(
			'payload' => woodshop_cart_payload(),
			'message' => __( 'محصول از سبد خرید حذف شد.', 'woodshop' ),
		)
	);
}
add_action( 'wp_ajax_woodshop_remove_cart_item', 'woodshop_ajax_remove_cart_item' );
add_action( 'wp_ajax_nopriv_woodshop_remove_cart_item', 'woodshop_ajax_remove_cart_item' );

/**
 * Localize the cart controller.
 */
function woodshop_cart_script_data() {
	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	wp_localize_script(
		'woodshop-main',
		'woodshopCart',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'woodshop_cart' ),
			'strings' => array(
				'adding'         => __( 'در حال افزودن…', 'woodshop' ),
				'added'          => __( 'به سبد اضافه شد', 'woodshop' ),
				'error'          => __( 'خطا، دوباره تلاش کنید', 'woodshop' ),
				'addedMessage'   => __( 'محصول به سبد خرید اضافه شد.', 'woodshop' ),
				'removedMessage' => __( 'محصول از سبد خرید حذف شد.', 'woodshop' ),
				'errorMessage'   => __( 'مشکلی پیش آمد. دوباره تلاش کنید.', 'woodshop' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'woodshop_cart_script_data', 30 );
