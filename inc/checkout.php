<?php
/**
 * Checkout field cleanup and layout support.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Simplify and reorder checkout fields.
 *
 * @param array<string, array<string, array<string, mixed>>> $fields Checkout fields.
 * @return array<string, array<string, array<string, mixed>>>
 */
function woodshop_checkout_fields( $fields ) {
	$billing = $fields['billing'] ?? array();

	unset(
		$billing['billing_country'],
		$billing['billing_company'],
		$billing['billing_address_2']
	);

	$billing['billing_first_name']['priority']    = 10;
	$billing['billing_first_name']['class']       = array( 'form-row-first' );
	$billing['billing_first_name']['placeholder'] = __( 'نام', 'woodshop' );

	$billing['billing_last_name']['priority']    = 20;
	$billing['billing_last_name']['class']       = array( 'form-row-last' );
	$billing['billing_last_name']['placeholder'] = __( 'نام خانوادگی', 'woodshop' );

	$billing['billing_phone']['priority']    = 30;
	$billing['billing_phone']['class']       = array( 'form-row-first' );
	$billing['billing_phone']['placeholder'] = __( 'شماره تماس', 'woodshop' );
	$billing['billing_phone']['required']    = true;

	$billing['billing_email']['priority']    = 40;
	$billing['billing_email']['class']       = array( 'form-row-last' );
	$billing['billing_email']['placeholder'] = __( 'ایمیل', 'woodshop' );

	$billing['billing_address_1']['priority']    = 50;
	$billing['billing_address_1']['class']       = array( 'form-row-wide' );
	$billing['billing_address_1']['placeholder'] = __( 'آدرس کامل', 'woodshop' );

	$billing['billing_state']['priority']    = 60;
	$billing['billing_state']['class']       = array( 'form-row-first' );
	$billing['billing_state']['type']        = 'text';
	$billing['billing_state']['placeholder'] = __( 'استان', 'woodshop' );

	$billing['billing_city']['priority']    = 70;
	$billing['billing_city']['class']       = array( 'form-row-last' );
	$billing['billing_city']['placeholder'] = __( 'شهر', 'woodshop' );

	$billing['billing_postcode']['priority']    = 80;
	$billing['billing_postcode']['class']       = array( 'form-row-first' );
	$billing['billing_postcode']['placeholder'] = __( 'کد پستی (اختیاری)', 'woodshop' );
	$billing['billing_postcode']['required']    = false;

	$fields['billing'] = $billing;

	// The store only ships to the billing address; keep the checkout to one address.
	$fields['shipping'] = array();

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['priority']    = 100;
		$fields['order']['order_comments']['placeholder'] = __( 'توضیحات سفارش (اختیاری)', 'woodshop' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'woodshop_checkout_fields', 20 );

/**
 * Remove the "ship to a different address" section visually.
 *
 * @param bool $show Whether to show.
 * @return bool
 */
function woodshop_ship_to_different_address( $show ) {
	return false;
}
add_filter( 'woocommerce_ship_to_different_address_checked', 'woodshop_ship_to_different_address' );

/**
 * Keep only the account tabs requested for this store.
 *
 * @param array<string, string> $items Account menu items.
 * @return array<string, string>
 */
function woodshop_account_menu_items( $items ) {
	return array(
		'dashboard'       => __( 'داشبورد', 'woodshop' ),
		'orders'          => __( 'سفارش‌ها', 'woodshop' ),
		'edit-account'    => __( 'اطلاعات کاربری', 'woodshop' ),
		'customer-logout' => __( 'خروج از حساب', 'woodshop' ),
	);
}
add_filter( 'woocommerce_account_menu_items', 'woodshop_account_menu_items', 20 );

/**
 * Persian checkout privacy copy.
 *
 * @return string
 */
function woodshop_checkout_privacy_text( $text, $type = '' ) {
	if ( 'checkout' === $type ) {
		return sprintf(
			/* translators: %s: privacy policy link placeholder. */
			__( 'اطلاعات شما فقط برای پردازش سفارش، پشتیبانی از تجربه خرید و مواردی که در %s توضیح داده شده استفاده می‌شود.', 'woodshop' ),
			'[privacy_policy]'
		);
	}

	return $text;
}
add_filter( 'woocommerce_get_privacy_policy_text', 'woodshop_checkout_privacy_text', 10, 2 );

/**
 * The checkout only serves Iran, so force the country used for validation
 * even though the country field itself is hidden.
 *
 * @param array<string, mixed> $data Posted checkout data.
 * @return array<string, mixed>
 */
function woodshop_force_checkout_country( $data ) {
	$data['billing_country'] = 'IR';

	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'woodshop_force_checkout_country' );
