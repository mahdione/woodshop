<?php
/**
 * Stores quote requests in the dashboard so a failed mail server never loses a
 * lead. Email delivery remains best-effort.
 *
 * @package WoodshopStudio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the request post type.
 */
function woodshop_studio_register_requests() {
	register_post_type(
		'woodshop_request',
		array(
			'labels'             => array(
				'name'          => __( 'Requests', 'woodshop-studio' ),
				'singular_name' => __( 'Request', 'woodshop-studio' ),
				'menu_name'     => __( 'Requests', 'woodshop-studio' ),
				'all_items'     => __( 'Quote Requests', 'woodshop-studio' ),
				'edit_item'     => __( 'View Request', 'woodshop-studio' ),
				'view_item'     => __( 'View Request', 'woodshop-studio' ),
				'search_items'  => __( 'Search Requests', 'woodshop-studio' ),
				'not_found'     => __( 'No requests yet.', 'woodshop-studio' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'woodshop-studio',
			'show_in_rest'       => false,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'supports'           => array( 'title', 'editor', 'custom-fields' ),
			'menu_icon'          => 'dashicons-email-alt',
			'has_archive'        => false,
			'rewrite'            => false,
		)
	);
}
add_action( 'init', 'woodshop_studio_register_requests' );

/**
 * Persist one request.
 *
 * @param array<string, string> $data   Sanitized fields.
 * @param array<string, string> $labels Field labels keyed by field name.
 * @return int Post ID, or 0 on failure.
 */
function woodshop_studio_store_lead( $data, $labels = array() ) {
	$name  = isset( $data['name'] ) ? $data['name'] : '';
	$phone = isset( $data['phone'] ) ? $data['phone'] : '';

	$lines = array();

	foreach ( $data as $key => $value ) {
		if ( '' === $value ) {
			continue;
		}

		$label   = isset( $labels[ $key ] ) ? $labels[ $key ] : ucfirst( str_replace( '_', ' ', $key ) );
		$lines[] = $label . ': ' . $value;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'woodshop_request',
			'post_status'  => 'private',
			'post_title'   => trim( $name . ' — ' . $phone, ' —' ),
			'post_content' => implode( "\n", $lines ),
			'meta_input'   => array(
				'_woodshop_request' => $data,
				'_woodshop_request_ip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			),
		),
		true
	);

	return is_wp_error( $post_id ) ? 0 : (int) $post_id;
}

/**
 * Show the submitted values on the request edit screen.
 */
function woodshop_studio_request_meta_box() {
	add_meta_box(
		'woodshop-request-details',
		__( 'Request details', 'woodshop-studio' ),
		'woodshop_studio_request_meta_box_html',
		'woodshop_request',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'woodshop_studio_request_meta_box' );

/**
 * Meta box renderer.
 *
 * @param WP_Post $post Current request.
 */
function woodshop_studio_request_meta_box_html( $post ) {
	$data = get_post_meta( $post->ID, '_woodshop_request', true );

	if ( ! is_array( $data ) ) {
		echo '<p>' . esc_html__( 'No structured data stored for this request.', 'woodshop-studio' ) . '</p>';
		return;
	}

	echo '<table class="widefat striped"><tbody>';
	foreach ( $data as $key => $value ) {
		if ( '' === $value ) {
			continue;
		}
		printf(
			'<tr><th style="width:180px">%1$s</th><td>%2$s</td></tr>',
			esc_html( ucfirst( str_replace( '_', ' ', (string) $key ) ) ),
			esc_html( (string) $value )
		);
	}
	echo '</tbody></table>';
}

/**
 * Add useful columns to the request list.
 *
 * @param array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function woodshop_studio_request_columns( $columns ) {
	$new = array(
		'cb'      => $columns['cb'],
		'title'   => __( 'Request', 'woodshop-studio' ),
		'phone'   => __( 'Phone', 'woodshop-studio' ),
		'message' => __( 'Message', 'woodshop-studio' ),
		'date'    => __( 'Received', 'woodshop-studio' ),
	);

	return $new;
}
add_filter( 'manage_woodshop_request_posts_columns', 'woodshop_studio_request_columns' );

/**
 * Render request columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function woodshop_studio_request_column_content( $column, $post_id ) {
	if ( ! in_array( $column, array( 'phone', 'message' ), true ) ) {
		return;
	}

	$data  = get_post_meta( $post_id, '_woodshop_request', true );
	$value = is_array( $data ) && isset( $data[ $column ] ) ? (string) $data[ $column ] : '—';

	if ( 'message' === $column && '—' !== $value ) {
		$value = wp_trim_words( $value, 10, '…' );
	}

	echo esc_html( $value );
}
add_action( 'manage_woodshop_request_posts_custom_column', 'woodshop_studio_request_column_content', 10, 2 );
