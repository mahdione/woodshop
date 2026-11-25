<?php
/**
 * Dynamic theme palette.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Built-in palette defaults.
 *
 * @return array<string, string>
 */
function woodshop_palette_defaults() {
	return array(
		'bg'         => '#eae5e0',
		'bg_alt'     => '#f4f0eb',
		'ink'        => '#1b110e',
		'body'       => '#4a4541',
		'muted'      => '#847b73',
		'brown'      => '#75604b',
		'brown_deep' => '#6e5a45',
		'accent'     => '#996a38',
		'sand'       => '#dfd4c9',
		'sand_2'     => '#c8b5a5',
		'white'      => '#ffffff',
		'dark'       => '#140d09',
		'success'    => '#5c6d4e',
		'error'      => '#a3453b',
	);
}

/**
 * Validate a hex color and fall back to the default.
 *
 * @param mixed  $value    Raw value.
 * @param string $fallback Fallback color.
 * @return string
 */
function woodshop_sanitize_palette_color( $value, $fallback ) {
	$value = trim( (string) $value );

	if ( preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value ) ) {
		return $value;
	}

	return $fallback;
}

/**
 * Current palette, merged with the built-in defaults.
 *
 * @return array<string, string>
 */
function woodshop_palette() {
	$palette = array();

	foreach ( woodshop_palette_defaults() as $key => $default ) {
		$palette[ $key ] = woodshop_sanitize_palette_color( woodshop_option( 'styles.' . $key, $default ), $default );
	}

	return $palette;
}

/**
 * Convert a hex color to an "r, g, b" string.
 *
 * @param string $hex Hex color.
 * @return string
 */
function woodshop_hex_to_rgb( $hex ) {
	$hex = ltrim( $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) ) {
		return '0, 0, 0';
	}

	return implode(
		', ',
		array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		)
	);
}

/**
 * Print the palette as CSS custom properties.
 */
function woodshop_palette_css() {
	$palette = woodshop_palette();

	$css = ':root{';
	foreach ( $palette as $key => $value ) {
		$css .= '--' . str_replace( '_', '-', $key ) . ':' . $value . ';';
	}

	foreach ( array( 'bg', 'ink', 'white', 'dark', 'sand', 'accent' ) as $key ) {
		$css .= '--' . str_replace( '_', '-', $key ) . '-rgb:' . woodshop_hex_to_rgb( $palette[ $key ] ) . ';';
	}

	$css .= '}';

	echo '<style id="woodshop-style-vars">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'woodshop_palette_css', 99 );
