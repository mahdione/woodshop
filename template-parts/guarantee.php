<?php
/**
 * Guarantee band.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_points = woodshop_option(
	'guarantee.points',
	array(
		'12-month workmanship guarantee.',
		'Free post-installation check.',
		'100% quality assurance on materials.',
		'Responsive after-service support.',
		'Satisfaction first approach.',
	)
);
?>
<section class="guarantee">
	<div class="guarantee__main">
		<?php
		woodshop_image(
			woodshop_option( 'guarantee.background', WOODSHOP_URI . '/assets/img/bg-guarantee.webp' ),
			array(
				'alt'    => '',
				'width'  => 1600,
				'height' => 1093,
				'class'  => 'guarantee__bg',
				'sizes'  => '100vw',
			)
		);
		?>
		<div class="guarantee__text reveal">
			<?php woodshop_eyebrow( woodshop_option( 'guarantee.eyebrow', 'Service Guarantee' ) ); ?>
			<h2><?php echo esc_html( woodshop_option( 'guarantee.title', 'Guaranteed Quality & Dedicated Support' ) ); ?></h2>
			<p><?php echo esc_html( woodshop_option( 'guarantee.text', 'We stand by our craftsmanship with a solid guarantee for your satisfaction. Every project is built with high-quality materials and precision. We don’t just finish a project and leave—we provide dedicated after-service support for lasting quality.' ) ); ?></p>
		</div>

		<ul class="guarantee__list reveal">
			<?php foreach ( (array) $ws_points as $ws_point ) : ?>
				<li><?php echo woodshop_icon( 'check-simple', 'guarantee__check', 14 ); ?><span><?php echo esc_html( is_array( $ws_point ) ? ( $ws_point['text'] ?? '' ) : $ws_point ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="guarantee__badge">
		<?php
		woodshop_image(
			woodshop_option( 'guarantee.image', WOODSHOP_URI . '/assets/img/badge.png' ),
			array(
				'width'  => 268,
				'height' => 250,
				'alt'    => esc_attr__( 'Quality guaranteed badge', 'woodshop' ),
				'class'  => 'guarantee__badge-img',
				'sizes'  => '268px',
			)
		);
		?>
	</div>
</section>
