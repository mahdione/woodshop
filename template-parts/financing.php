<?php
/**
 * Financing.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_points = woodshop_option(
	'financing.points',
	array(
		'Easy application process with fast approval.',
		'Low-interest rates and flexible payment plans.',
		'Financing available for renovations, custom furniture, and repairs.',
		'No hidden fees or unexpected costs.',
	)
);
?>
<section class="section financing" id="financing">
	<div class="wrap financing__grid">
		<div class="financing__media reveal">
			<?php
			woodshop_image(
				woodshop_option( 'financing.image', WOODSHOP_URI . '/assets/img/financing.webp' ),
				array(
					'alt'    => esc_attr__( 'Carpenter reviewing project costs', 'woodshop' ),
					'width'  => 1000,
					'height' => 666,
					'class'  => 'financing__img',
					'sizes'  => '(max-width: 900px) 100vw, 50vw',
				)
			);
			?>
		</div>

		<div class="financing__content reveal">
			<?php woodshop_eyebrow( woodshop_option( 'financing.eyebrow', 'Financing' ) ); ?>
			<h2 class="section-title"><?php echo esc_html( woodshop_option( 'financing.title', 'Flexible Financing for Your Carpentry Projects' ) ); ?></h2>
			<p class="section-text"><?php echo esc_html( woodshop_option( 'financing.text', 'Upgrade your home with expert carpentry without the financial stress. We offer flexible financing options to make your dream woodwork project more affordable and accessible.' ) ); ?></p>

			<ul class="check-list">
				<?php foreach ( (array) $ws_points as $ws_point ) : ?>
					<li><?php echo woodshop_icon( 'check', 'check-list__icon', 18 ); ?><span><?php echo esc_html( is_array( $ws_point ) ? ( $ws_point['text'] ?? '' ) : $ws_point ); ?></span></li>
				<?php endforeach; ?>
			</ul>

			<a class="btn" href="<?php echo esc_url( woodshop_option( 'financing.button_url', '#' ) ); ?>"><?php echo esc_html( woodshop_option( 'financing.button_label', 'More About Financing' ) ); ?></a>
		</div>
	</div>
</section>
