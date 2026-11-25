<?php
/**
 * Feature strip.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_items = woodshop_option(
	'features.items',
	array(
		array( 'icon' => 'award', 'title' => 'Premium Quality', 'text' => 'High-grade wood and finishes for durability and long-lasting beauty.' ),
		array( 'icon' => 'tools', 'title' => 'Expert Craftsmanship', 'text' => 'Skilled professionals delivering exceptional and precise woodwork.' ),
		array( 'icon' => 'medal', 'title' => 'Warranty and Support', 'text' => 'Backed by warranty and dedicated support for lasting satisfaction.' ),
		array( 'icon' => 'recycle', 'title' => 'Eco-Friendly Practices', 'text' => 'Sustainably sourced materials and eco-friendly processes.' ),
	)
);

if ( empty( $ws_items ) ) {
	return;
}
?>
<section class="features" aria-label="<?php esc_attr_e( 'Why work with us', 'woodshop' ); ?>">
	<h2 class="screen-reader-text"><?php esc_html_e( 'Why work with us', 'woodshop' ); ?></h2>
	<div class="wrap">
		<ul class="features__grid">
			<?php foreach ( $ws_items as $ws_item ) : ?>
				<li class="feature">
					<span class="feature__icon"><?php echo woodshop_icon( $ws_item['icon'] ?? 'award', '', 26 ); ?></span>
					<h3 class="feature__title"><?php echo esc_html( $ws_item['title'] ?? '' ); ?></h3>
					<p class="feature__text"><?php echo esc_html( $ws_item['text'] ?? '' ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
