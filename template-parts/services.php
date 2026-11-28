<?php
/**
 * Services (WooCommerce product categories or manual cards).
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_source = woodshop_option( 'services.source', 'categories' );
$ws_terms  = array();
$ws_cards  = array();

$ws_fallbacks = array(
	'service-furniture.webp',
	'service-cabinetry.webp',
	'service-flooring.webp',
	'service-builtin.webp',
	'service-outdoor.webp',
	'service-renovation.webp',
);

if ( 'categories' === $ws_source && taxonomy_exists( 'product_cat' ) ) {
	$ws_ids  = woodshop_option( 'services.category_ids', array() );
	$ws_terms = woodshop_product_categories( is_array( $ws_ids ) ? $ws_ids : array(), 6 );

	foreach ( $ws_terms as $ws_i => $ws_term ) {
		$ws_image_id = (int) get_term_meta( $ws_term->term_id, 'thumbnail_id', true );
		$ws_image    = $ws_image_id ? wp_get_attachment_image_url( $ws_image_id, 'ws-card' ) : woodshop_term_image( $ws_term, 'ws-card' );
		$ws_cards[]  = array(
			'title'  => $ws_term->name,
			'text'   => wp_trim_words( wp_strip_all_tags( term_description( $ws_term ) ), 16 ),
			'image'  => $ws_image ? $ws_image : WOODSHOP_URI . '/assets/img/' . $ws_fallbacks[ $ws_i % 6 ],
			'srcset' => $ws_image_id ? woodshop_attachment_srcset( $ws_image_id ) : '',
			'url'    => get_term_link( $ws_term ),
		);
	}
}

if ( empty( $ws_cards ) ) {
	$ws_items = woodshop_option(
		'services.items',
		array(
			array( 'title' => 'Custom Furniture Design', 'text' => 'Creating bespoke furniture pieces tailored to your unique style and space needs.', 'image' => WOODSHOP_URI . '/assets/img/service-furniture.webp', 'url' => '#' ),
			array( 'title' => 'Cabinetry and Storage', 'text' => 'Designing custom cabinets and storage units for functional and elegant spaces.', 'image' => WOODSHOP_URI . '/assets/img/service-cabinetry.webp', 'url' => '#' ),
			array( 'title' => 'Wooden Flooring', 'text' => 'Installing premium hardwood floors with expert precision for unmatched lasting durability.', 'image' => WOODSHOP_URI . '/assets/img/service-flooring.webp', 'url' => '#' ),
			array( 'title' => 'Built-In Furniture Solutions', 'text' => 'Designing built-in shelving and units that blend seamlessly with your interiors.', 'image' => WOODSHOP_URI . '/assets/img/service-builtin.webp', 'url' => '#' ),
			array( 'title' => 'Outdoor Carpentry', 'text' => 'Building durable decks, pergolas, fences, and outdoor furniture for your garden.', 'image' => WOODSHOP_URI . '/assets/img/service-outdoor.webp', 'url' => '#' ),
			array( 'title' => 'Home Renovation Carpentry', 'text' => 'Enhancing homes with expert carpentry for remodeling and architectural woodwork.', 'image' => WOODSHOP_URI . '/assets/img/service-renovation.webp', 'url' => '#' ),
		)
	);

	foreach ( (array) $ws_items as $ws_i => $ws_item ) {
		$ws_cards[] = array(
			'title'  => $ws_item['title'] ?? '',
			'text'   => $ws_item['text'] ?? '',
			'image'  => $ws_item['image'] ?? ( WOODSHOP_URI . '/assets/img/' . $ws_fallbacks[ $ws_i % 6 ] ),
			'srcset' => is_numeric( $ws_item['image'] ?? '' ) ? woodshop_attachment_srcset( (int) $ws_item['image'] ) : '',
			'url'    => $ws_item['url'] ?? '#',
		);
	}
}

if ( empty( $ws_cards ) ) {
	return;
}

$ws_phone   = woodshop_option( 'brand.phone', '(123) 456 7890' );
$ws_phone_h = woodshop_option( 'brand.phone_url', 'tel:+11234567890' );
?>
<section class="section services" id="services">
	<div class="wrap">
		<div class="section-head reveal">
			<?php woodshop_eyebrow( woodshop_option( 'services.eyebrow', 'Our Services' ) ); ?>
			<h2 class="section-title"><?php echo esc_html( woodshop_option( 'services.title', 'Crafting Perfection in Every Corner' ) ); ?></h2>
			<p class="section-text"><?php echo esc_html( woodshop_option( 'services.text', 'Transforming your spaces with expert carpentry and custom woodwork solutions tailored to your lifestyle.' ) ); ?></p>
		</div>

		<ul class="services__grid">
			<?php foreach ( $ws_cards as $ws_card ) : ?>
				<li class="service-card reveal">
					<a class="service-card__media" href="<?php echo esc_url( $ws_card['url'] ); ?>" tabindex="-1" aria-hidden="true">
						<?php
						woodshop_image(
							$ws_card['image'],
							array(
								'alt'    => '',
								'width'  => 800,
								'height' => 533,
								'class'  => 'service-card__img',
								'sizes'  => '(max-width: 767px) 100vw, (max-width: 1024px) 50vw, 430px',
								'srcset' => $ws_card['srcset'] ?? '',
							)
						);
						?>
					</a>
					<div class="service-card__body">
						<h3 class="service-card__title"><a href="<?php echo esc_url( $ws_card['url'] ); ?>"><?php echo esc_html( $ws_card['title'] ); ?></a></h3>
						<p class="service-card__text"><?php echo esc_html( $ws_card['text'] ); ?></p>
						<a class="btn btn--sm service-card__btn" href="<?php echo esc_url( $ws_card['url'] ); ?>"><?php esc_html_e( 'Learn More', 'woodshop' ); ?><span class="screen-reader-text"> <?php echo esc_html( sprintf( /* translators: %s: service name. */ __( 'about %s', 'woodshop' ), $ws_card['title'] ) ); ?></span></a>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="services__cta reveal">
			<a class="btn" href="<?php echo esc_url( woodshop_option( 'services.button_url', '#request' ) ); ?>"><?php echo esc_html( woodshop_option( 'services.button_label', 'Get Free Quote' ) ); ?></a>
			<a class="phone-link" href="<?php echo esc_url( $ws_phone_h ); ?>"><?php echo woodshop_icon( 'phone', '', 17 ); ?><?php echo esc_html( $ws_phone ); ?></a>
		</div>
	</div>
</section>
