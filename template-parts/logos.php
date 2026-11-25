<?php
/**
 * Partner logos.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_items = woodshop_option(
	'logos.items',
	array(
		array( 'image' => WOODSHOP_URI . '/assets/img/partner-1.png', 'alt' => 'Ruud Stanley' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/partner-2.png', 'alt' => 'Andreas Bike' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/partner-3.png', 'alt' => 'Blake Star' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/partner-4.png', 'alt' => 'Nick & Joan' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/partner-5.png', 'alt' => 'Skylight' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/partner-6.png', 'alt' => 'Hudston Smith' ),
	)
);

if ( empty( $ws_items ) ) {
	return;
}
?>
<section class="logos" aria-label="<?php esc_attr_e( 'Trusted partners', 'woodshop' ); ?>">
	<div class="wrap">
		<ul class="logos__grid">
			<?php foreach ( (array) $ws_items as $ws_item ) : ?>
				<li class="logos__item">
					<?php
					$ws_alt = $ws_item['alt'] ?? '';
					$ws_url = $ws_item['url'] ?? '';
					if ( $ws_url ) {
						printf( '<a href="%s">', esc_url( $ws_url ) );
					}
					woodshop_image( $ws_item['image'] ?? '', array( 'width' => 220, 'height' => 75, 'alt' => $ws_alt, 'class' => 'logos__img', 'sizes' => '220px' ) );
					if ( $ws_url ) {
						echo '</a>';
					}
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
