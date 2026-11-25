<?php
/**
 * Front page.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( woodshop_section_enabled( 'hero' ) ) {
	get_template_part( 'template-parts/hero' );
}

$ws_sections = array(
	'features'     => 'features',
	'about'        => 'about',
	'services'     => 'services',
	'products'     => 'products',
	'showcase'     => 'showcase',
	'guarantee'    => 'guarantee',
	'gallery'      => 'gallery',
	'areas'        => 'areas',
	'sectors'      => 'sectors',
	'logos'        => 'logos',
	'financing'    => 'financing',
	'testimonials' => 'testimonials',
	'blog'         => 'blog',
	'cta'          => 'cta',
);

foreach ( $ws_sections as $ws_key => $ws_template ) {
	if ( woodshop_section_enabled( $ws_key ) ) {
		get_template_part( 'template-parts/' . $ws_template );
	}
}

get_footer();
