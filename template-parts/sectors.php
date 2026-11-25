<?php
/**
 * Service sectors.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_items = woodshop_option(
	'sectors.items',
	array(
		array( 'icon' => 'home', 'title' => 'Residential Carpentry', 'text' => 'Enhancing homes with tailored, quality woodwork solutions daily.' ),
		array( 'icon' => 'building', 'title' => 'Commercial Carpentry', 'text' => 'Optimizing business spaces with precision woodwork and efficiency.' ),
		array( 'icon' => 'medkit', 'title' => 'Healthcare Carpentry', 'text' => 'Designing safe, comfortable spaces with precise woodwork installations.' ),
		array( 'icon' => 'school', 'title' => 'Institutional Carpentry', 'text' => 'Supporting institutions with dependable woodwork installations.' ),
		array( 'icon' => 'hotel', 'title' => 'Hospitality Carpentry', 'text' => 'Crafting inviting hospitality spaces with elegant, durable woodwork.' ),
		array( 'icon' => 'basket', 'title' => 'Retail Carpentry', 'text' => 'Transforming retail storefronts with innovative custom woodwork.' ),
	)
);

if ( empty( $ws_items ) ) {
	return;
}
?>
<section class="sectors" id="sectors">
	<?php
	woodshop_image(
		woodshop_option( 'sectors.background', WOODSHOP_URI . '/assets/img/bg-sectors.webp' ),
		array(
			'alt'    => '',
			'width'  => 1600,
			'height' => 1066,
			'class'  => 'sectors__bg',
			'sizes'  => '100vw',
		)
	);
	?>
	<div class="sectors__head">
		<div class="wrap">
			<div class="section-head section-head--center reveal">
				<?php woodshop_eyebrow( woodshop_option( 'sectors.eyebrow', 'Service Sectors' ) ); ?>
				<h2 class="section-title"><?php echo esc_html( woodshop_option( 'sectors.title', 'Tailored Carpentry for Every Sector' ) ); ?></h2>
				<p class="section-text"><?php echo esc_html( woodshop_option( 'sectors.text', 'Our specialized services adapt to your environment, whether residential, commercial, or industrial, with custom woodwork designed to impress.' ) ); ?></p>
			</div>
		</div>
	</div>

	<div class="wrap">
		<ul class="sectors__grid">
			<?php foreach ( $ws_items as $ws_item ) : ?>
				<li class="sector-card reveal">
					<span class="sector-card__icon"><?php echo woodshop_icon( $ws_item['icon'] ?? 'home', '', 28 ); ?></span>
					<h3 class="sector-card__title"><?php echo esc_html( $ws_item['title'] ?? '' ); ?></h3>
					<p class="sector-card__text"><?php echo esc_html( $ws_item['text'] ?? '' ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
