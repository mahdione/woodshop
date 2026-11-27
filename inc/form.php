<?php
/**
 * Request / quote form.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Form field definitions.
 *
 * Every form on the site uses the same three fields. Labels and placeholders
 * are editable in Wood Shop Studio → Request Form.
 *
 * @param string $mode Retained for backwards compatibility.
 * @return array<string, array<string, mixed>>
 */
function woodshop_form_fields( $mode = 'request' ) {
	$name_label    = woodshop_option( 'form.name_label', __( 'نام و نام خانوادگی', 'woodshop' ) );
	$name_holder   = woodshop_option( 'form.name_placeholder', $name_label );
	$phone_label   = woodshop_option( 'form.phone_label', __( 'شماره تماس', 'woodshop' ) );
	$phone_holder  = woodshop_option( 'form.phone_placeholder', $phone_label );
	$message_label = woodshop_option( 'form.message_label', __( 'پیام شما', 'woodshop' ) );
	$message_holder = woodshop_option( 'form.message_placeholder', $message_label );

	return array(
		'name'    => array(
			'label'        => $name_label,
			'placeholder'  => $name_holder,
			'type'         => 'text',
			'required'     => true,
			'width'        => 'half',
			'autocomplete' => 'name',
		),
		'phone'   => array(
			'label'        => $phone_label,
			'placeholder'  => $phone_holder,
			'type'         => 'tel',
			'required'     => true,
			'width'        => 'half',
			'autocomplete' => 'tel',
		),
		'message' => array(
			'label'       => $message_label,
			'placeholder' => $message_holder,
			'type'        => 'textarea',
			'required'    => true,
			'width'       => 'full',
		),
	);
}

/**
 * Render the request / contact form.
 *
 * @param array<string, string> $atts Shortcode attributes.
 * @return string
 */
function woodshop_request_form( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'title'      => '',
			'button'     => '',
			'mode'       => 'request',
			'show_title' => '1',
		),
		$atts,
		'woodshop_request_form'
	);

	$mode        = 'contact' === $atts['mode'] ? 'contact' : 'request';
	$form_title  = $atts['title'] ? $atts['title'] : woodshop_option( 'cta.form_title', __( 'درخواست خدمات شما', 'woodshop' ) );
	$button_text = $atts['button'] ? $atts['button'] : woodshop_option( 'cta.button', __( 'ثبت درخواست', 'woodshop' ) );
	$show_title  = '1' === (string) $atts['show_title'];
	$fields      = woodshop_form_fields( $mode );

	$sent  = isset( $_GET['ws_sent'] ) ? sanitize_key( wp_unslash( $_GET['ws_sent'] ) ) : '';
	$error = isset( $_GET['ws_error'] ) ? sanitize_key( wp_unslash( $_GET['ws_error'] ) ) : '';

	ob_start();
	?>
	<div class="request-form">
		<form class="request-form__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<?php if ( $show_title ) : ?>
				<h3 class="request-form__title"><?php echo esc_html( $form_title ); ?></h3>
			<?php endif; ?>

			<?php if ( '1' === $sent ) : ?>
				<p class="form-notice form-notice--success" role="status"><?php echo esc_html( woodshop_option( 'cta.success', __( 'از درخواست شما سپاسگزاریم. به‌زودی با شما تماس می‌گیریم.', 'woodshop' ) ) ); ?></p>
			<?php elseif ( $error ) : ?>
				<p class="form-notice form-notice--error" role="alert"><?php echo esc_html( woodshop_option( 'cta.error', __( 'مشکلی پیش آمد. لطفاً فیلدهای الزامی را بررسی کنید و دوباره تلاش کنید.', 'woodshop' ) ) ); ?></p>
			<?php endif; ?>

			<div class="request-form__grid">
				<?php foreach ( $fields as $key => $field ) : ?>
					<p class="field field--<?php echo esc_attr( $field['width'] ); ?>">
						<label class="screen-reader-text" for="ws-field-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
						<?php if ( 'textarea' === $field['type'] ) : ?>
							<textarea id="ws-field-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="5" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" <?php echo $field['required'] ? 'required' : ''; ?>></textarea>
						<?php else : ?>
							<input
								type="<?php echo esc_attr( $field['type'] ); ?>"
								id="ws-field-<?php echo esc_attr( $key ); ?>"
								name="<?php echo esc_attr( $key ); ?>"
								placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
								<?php echo ! empty( $field['autocomplete'] ) ? 'autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : ''; ?>
								<?php echo $field['required'] ? 'required' : ''; ?>
							>
						<?php endif; ?>
					</p>
				<?php endforeach; ?>
			</div>

			<p class="field field--hp" aria-hidden="true">
				<label for="ws-field-website">وب‌سایت</label>
				<input type="text" id="ws-field-website" name="website" tabindex="-1" autocomplete="off">
			</p>

			<?php wp_nonce_field( 'woodshop_request', 'woodshop_request_nonce' ); ?>
			<input type="hidden" name="action" value="woodshop_request">
			<input type="hidden" name="form_mode" value="<?php echo esc_attr( $mode ); ?>">
			<input type="hidden" name="started" value="<?php echo esc_attr( (string) time() ); ?>">

			<button class="btn btn--dark request-form__submit" type="submit"><?php echo esc_html( $button_text ); ?></button>
		</form>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'woodshop_request_form', 'woodshop_request_form' );

/**
 * Handle form submissions.
 */
function woodshop_handle_request() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$redirect = remove_query_arg( array( 'ws_sent', 'ws_error' ), $redirect );

	if ( ! isset( $_POST['woodshop_request_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['woodshop_request_nonce'] ) ), 'woodshop_request' ) ) {
		wp_safe_redirect( add_query_arg( 'ws_error', 'nonce', $redirect ) . '#request' );
		exit;
	}

	$honeypot = isset( $_POST['website'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['website'] ) ) ) : '';
	$started  = isset( $_POST['started'] ) ? absint( $_POST['started'] ) : 0;

	if ( '' !== $honeypot || ( $started && ( time() - $started ) < 2 ) ) {
		wp_safe_redirect( add_query_arg( 'ws_sent', '1', $redirect ) . '#request' );
		exit;
	}

	$ip_hash = md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown' );

	if ( get_transient( 'woodshop_request_' . $ip_hash ) ) {
		wp_safe_redirect( add_query_arg( 'ws_error', 'rate', $redirect ) . '#request' );
		exit;
	}

	$mode   = isset( $_POST['form_mode'] ) ? sanitize_key( wp_unslash( $_POST['form_mode'] ) ) : 'request';
	$mode   = 'contact' === $mode ? 'contact' : 'request';
	$fields = woodshop_form_fields( $mode );
	$data   = array();

	foreach ( $fields as $key => $field ) {
		$raw          = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$data[ $key ] = 'textarea' === $field['type'] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
	}

	if ( empty( $data['name'] ) || empty( $data['phone'] ) || empty( $data['message'] ) ) {
		wp_safe_redirect( add_query_arg( 'ws_error', 'fields', $redirect ) . '#request' );
		exit;
	}

	$recipient = sanitize_email( woodshop_option( 'cta.recipient', get_option( 'admin_email' ) ) );

	if ( ! is_email( $recipient ) ) {
		$recipient = get_option( 'admin_email' );
	}

	$lines = array(
		'contact' === $mode ? __( 'New contact message from the website.', 'woodshop' ) : __( 'New service request from the website.', 'woodshop' ),
		'',
	);

	foreach ( $fields as $key => $field ) {
		$value = $data[ $key ];

		if ( '' === $value ) {
			continue;
		}

		$lines[] = $field['label'] . ': ' . $value;
	}

	$lines[] = '';
	$lines[] = 'Page: ' . esc_url_raw( $redirect );

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
	);

	$mail_sent = wp_mail(
		$recipient,
		/* translators: %s: sender name. */
		sprintf( 'contact' === $mode ? __( 'New contact message from %s', 'woodshop' ) : __( 'New service request from %s', 'woodshop' ), $data['name'] ),
		implode( "\n", $lines ),
		$headers
	);

	$labels = array();
	foreach ( $fields as $key => $field ) {
		$labels[ $key ] = $field['label'];
	}

	$stored = function_exists( 'woodshop_studio_store_lead' ) ? woodshop_studio_store_lead( $data, $labels ) : 0;

	set_transient( 'woodshop_request_' . $ip_hash, 1, 30 );

	if ( ! $mail_sent && ! $stored ) {
		wp_safe_redirect( add_query_arg( 'ws_error', 'mail', $redirect ) . '#request' );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'ws_sent', '1', $redirect ) . '#request' );
	exit;
}
add_action( 'admin_post_woodshop_request', 'woodshop_handle_request' );
add_action( 'admin_post_nopriv_woodshop_request', 'woodshop_handle_request' );
