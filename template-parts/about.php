<?php
/**
 * Why choose us.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_points = woodshop_option(
	'about.points',
	array(
		array( 'icon' => 'tools', 'text' => 'Tailored solutions crafted to reflect your unique style and needs.' ),
		array( 'icon' => 'layers', 'text' => 'Seamless process from consultation to delivery and installation.' ),
		array( 'icon' => 'swatchbook', 'text' => 'Dedicated to creating durable, functional, and elegant designs.' ),
	)
);
?>
<section class="section about">
	<div class="wrap about__grid">
		<div class="about__content reveal">
			<?php woodshop_eyebrow( woodshop_option( 'about.eyebrow', 'Why Choose Us?' ) ); ?>
			<h2 class="section-title"><?php echo esc_html( woodshop_option( 'about.title', 'Building Trust Through Craftsmanship' ) ); ?></h2>
			<p class="section-text"><?php echo esc_html( woodshop_option( 'about.text', 'Our commitment to excellence is reflected in every plank, joint, and finish, ensuring that your space is not just functional but also truly unique. From custom furniture to large-scale renovations, we combine timeless carpentry techniques with modern innovations to bring your vision to life.' ) ); ?></p>

			<?php if ( $ws_points ) : ?>
				<ul class="check-list">
					<?php foreach ( $ws_points as $ws_point ) : ?>
						<li><?php echo woodshop_icon( $ws_point['icon'] ?? 'tools', 'check-list__icon', 20 ); ?><span><?php echo esc_html( $ws_point['text'] ?? '' ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<a class="btn" href="<?php echo esc_url( woodshop_option( 'about.button_url', '#' ) ); ?>"><?php echo esc_html( woodshop_option( 'about.button_label', 'More About Us' ) ); ?></a>
		</div>

		<aside class="stat-card reveal">
			<?php
			woodshop_image(
				woodshop_option( 'about.image', WOODSHOP_URI . '/assets/img/bg-why.webp' ),
				array(
					'alt'    => '',
					'width'  => 1400,
					'height' => 933,
					'class'  => 'stat-card__bg',
					'sizes'  => '50vw',
				)
			);
			?>
			<span class="stat-card__shade" aria-hidden="true"></span>
			<div class="stat-card__body">
				<span class="stat-card__number">
					<strong class="stat-card__value" data-count="<?php echo esc_attr( woodshop_option( 'about.stat_value', '۸۵۰' ) ); ?>"><?php echo esc_html( woodshop_option( 'about.stat_value', '۸۵۰' ) ); ?></strong>
					<span class="stat-card__suffix"><?php echo esc_html( woodshop_option( 'about.stat_suffix', '+' ) ); ?></span>
				</span>
				<span class="stat-card__label"><?php echo esc_html( woodshop_option( 'about.stat_label', 'مشتری راضی' ) ); ?></span>
			</div>
		</aside>
	</div>
</section>
