<?php
/**
 * Service areas.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_areas = woodshop_option(
	'areas.items',
	array(
		array( 'name' => 'Florida', 'url' => '#' ),
		array( 'name' => 'Georgia', 'url' => '#' ),
		array( 'name' => 'Mississippi', 'url' => '#' ),
		array( 'name' => 'North Carolina', 'url' => '#' ),
		array( 'name' => 'Alabama', 'url' => '#' ),
		array( 'name' => 'Louisiana', 'url' => '#' ),
		array( 'name' => 'South Carolina', 'url' => '#' ),
		array( 'name' => 'Tennessee', 'url' => '#' ),
	)
);

$ws_areas = array_values( array_filter( (array) $ws_areas ) );
$ws_half  = (int) ceil( count( $ws_areas ) / 2 );
$ws_left  = array_slice( $ws_areas, 0, $ws_half );
$ws_right = array_slice( $ws_areas, $ws_half );
?>
<section class="section areas" id="areas">
	<div class="wrap areas__grid">
		<div class="areas__content reveal">
			<?php woodshop_eyebrow( woodshop_option( 'areas.eyebrow', 'Service Areas' ) ); ?>
			<h2 class="section-title"><?php echo esc_html( woodshop_option( 'areas.title', 'Expert Carpentry Services Near You' ) ); ?></h2>
			<p class="section-text"><?php echo esc_html( woodshop_option( 'areas.text', 'We proudly serve homeowners and businesses in multiple locations, delivering high-quality custom carpentry solutions with precision and craftsmanship.' ) ); ?></p>

			<div class="areas__lists">
				<ul class="map-list">
					<?php foreach ( $ws_left as $ws_area ) : ?>
						<li><a href="<?php echo esc_url( $ws_area['url'] ?? '#' ); ?>"><?php echo woodshop_icon( 'map', 'map-list__icon', 18 ); ?><span><?php echo esc_html( $ws_area['name'] ?? '' ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
				<ul class="map-list">
					<?php foreach ( $ws_right as $ws_area ) : ?>
						<li><a href="<?php echo esc_url( $ws_area['url'] ?? '#' ); ?>"><?php echo woodshop_icon( 'map', 'map-list__icon', 18 ); ?><span><?php echo esc_html( $ws_area['name'] ?? '' ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<a class="btn" href="<?php echo esc_url( woodshop_option( 'areas.button_url', '#' ) ); ?>"><?php echo esc_html( woodshop_option( 'areas.button_label', 'View All Service Areas' ) ); ?></a>
		</div>

		<div class="areas__media reveal">
			<?php
			woodshop_image(
				woodshop_option( 'areas.image', WOODSHOP_URI . '/assets/img/areas-team.webp' ),
				array(
					'alt'    => esc_attr__( 'Carpenters working together in a workshop', 'woodshop' ),
					'width'  => 1000,
					'height' => 666,
					'class'  => 'areas__img',
					'sizes'  => '(max-width: 900px) 100vw, 50vw',
				)
			);
			?>
		</div>
	</div>
</section>
