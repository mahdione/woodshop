<?php
/**
 * Request call to action.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_points = woodshop_option(
	'cta.points',
	array(
		'Expert craftsmanship with precision detailing.',
		'Timely project completion with no delays.',
	)
);

$ws_phone   = woodshop_option( 'brand.phone', '(123) 456 7890' );
$ws_phone_h = woodshop_option( 'brand.phone_url', 'tel:+11234567890' );
?>
<section class="cta-section" id="request">
	<?php
	woodshop_image(
		woodshop_option( 'cta.background', WOODSHOP_URI . '/assets/img/bg-cta.webp' ),
		array(
			'alt'    => '',
			'width'  => 1600,
			'height' => 736,
			'class'  => 'cta-section__bg',
			'sizes'  => '100vw',
		)
	);
	?>
	<span class="cta-section__overlay" aria-hidden="true"></span>

	<div class="wrap cta-section__grid">
		<div class="cta-section__content reveal">
			<h2 class="cta-section__title">
				<?php echo esc_html( woodshop_option( 'cta.title_before', 'Transform Your Space with' ) ); ?>
				<span class="highlight"><?php echo esc_html( woodshop_option( 'cta.title_highlight', 'Quality Carpentry.' ) ); ?><?php echo woodshop_squiggle(); ?></span>
			</h2>

			<ul class="cta-checks">
				<?php foreach ( (array) $ws_points as $ws_point ) : ?>
					<li><?php echo woodshop_icon( 'check', 'cta-checks__icon', 17 ); ?><span><?php echo esc_html( is_array( $ws_point ) ? ( $ws_point['text'] ?? '' ) : $ws_point ); ?></span></li>
				<?php endforeach; ?>
			</ul>

			<a class="cta-phone" href="<?php echo esc_url( $ws_phone_h ); ?>">
				<?php echo woodshop_icon( 'phone', '', 18 ); ?>
				<span><?php echo esc_html( $ws_phone ); ?></span>
			</a>
		</div>

		<div class="cta-section__form reveal">
			<?php echo do_shortcode( '[woodshop_request_form]' ); ?>
		</div>
	</div>
</section>
