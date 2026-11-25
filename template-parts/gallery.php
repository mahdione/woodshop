<?php
/**
 * Project gallery.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_items = woodshop_option(
	'gallery.items',
	array(
		array( 'image' => WOODSHOP_URI . '/assets/img/gallery-1.webp', 'title' => 'Restaurant Wooden Seating', 'caption' => 'Hospitality', 'url' => '#' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/gallery-2.webp', 'title' => 'Classic Racks', 'caption' => 'Residential', 'url' => '#' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/gallery-3.webp', 'title' => 'Meeting Room Interior', 'caption' => 'Commercial', 'url' => '#' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/gallery-4.webp', 'title' => 'Medical Counter Cabinetry', 'caption' => 'Healthcare', 'url' => '#' ),
	)
);

if ( empty( $ws_items ) ) {
	return;
}
?>
<section class="section gallery-section" id="projects">
	<div class="wrap">
		<div class="section-head section-head--center reveal">
			<?php woodshop_eyebrow( woodshop_option( 'gallery.eyebrow', 'Featured Project Gallery' ) ); ?>
			<h2 class="section-title"><?php echo esc_html( woodshop_option( 'gallery.title', 'Our Carpentry Creations in Action' ) ); ?></h2>
			<p class="section-text"><?php echo esc_html( woodshop_option( 'gallery.text', 'Browse our latest carpentry projects, where precision and craftsmanship bring spaces to life.' ) ); ?></p>
		</div>
	</div>

	<div class="wrap">
		<ul class="gallery-grid">
			<?php foreach ( $ws_items as $ws_item ) : ?>
				<li class="gallery-item reveal">
					<a href="<?php echo esc_url( $ws_item['url'] ?? '#' ); ?>">
						<?php woodshop_image( $ws_item['image'] ?? '', array( 'width' => 700, 'height' => 700, 'alt' => '', 'class' => 'gallery-item__img', 'sizes' => '(max-width: 640px) 100vw, 25vw' ) ); ?>
						<span class="gallery-item__shade" aria-hidden="true"></span>
						<span class="gallery-item__content">
							<strong class="gallery-item__title"><?php echo esc_html( $ws_item['title'] ?? '' ); ?></strong>
							<?php if ( ! empty( $ws_item['caption'] ) ) : ?>
								<span class="gallery-item__caption"><?php echo esc_html( $ws_item['caption'] ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
