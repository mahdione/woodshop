<?php
/**
 * Wood Shop theme bootstrap.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

define( 'WOODSHOP_VERSION', '1.0.0' );
define( 'WOODSHOP_DIR', get_template_directory() );
define( 'WOODSHOP_URI', get_template_directory_uri() );

require_once WOODSHOP_DIR . '/inc/icons.php';
require_once WOODSHOP_DIR . '/inc/setup.php';
require_once WOODSHOP_DIR . '/inc/template-tags.php';
require_once WOODSHOP_DIR . '/inc/styles.php';
require_once WOODSHOP_DIR . '/inc/performance.php';
require_once WOODSHOP_DIR . '/inc/form.php';
require_once WOODSHOP_DIR . '/inc/cart.php';
require_once WOODSHOP_DIR . '/inc/shop-filters.php';
require_once WOODSHOP_DIR . '/inc/checkout.php';
require_once WOODSHOP_DIR . '/inc/github-updater.php';

/*
 * The companion settings plugin is bundled so the theme can be installed on
 * its own. If the standalone plugin is active, it loads first and wins.
 */
if ( ! defined( 'WOODSHOP_STUDIO_VERSION' ) ) {
	define( 'WOODSHOP_STUDIO_EMBEDDED', true );
	require_once WOODSHOP_DIR . '/inc/studio/woodshop-studio.php';
}

require_once WOODSHOP_DIR . '/inc/demo-import.php';
require_once WOODSHOP_DIR . '/inc/manual-update.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once WOODSHOP_DIR . '/inc/woocommerce.php';
}

/**
 * Is the companion customization plugin available?
 *
 * @return bool
 */
function woodshop_has_studio() {
	return function_exists( 'woodshop_studio_get' );
}

/**
 * Read a homepage option with a safe fallback.
 *
 * @param string $path    Dot separated option path.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function woodshop_option( $path, $default = '' ) {
	if ( woodshop_has_studio() ) {
		$value = woodshop_studio_get( $path, null );
		if ( null !== $value && '' !== $value ) {
			return $value;
		}
	}

	return $default;
}

/**
 * Is a homepage section enabled?
 *
 * @param string $section Section key.
 * @return bool
 */
function woodshop_section_enabled( $section ) {
	if ( ! woodshop_has_studio() ) {
		return true;
	}

	return (bool) woodshop_studio_get( $section . '.enabled', true );
}

/**
 * Redirect the old Projects page to the portfolio archive.
 */
function woodshop_legacy_projects_redirect() {
	if ( is_page( 'projects' ) ) {
		$archive = get_post_type_archive_link( 'portfolio' );
		if ( $archive ) {
			wp_safe_redirect( $archive, 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'woodshop_legacy_projects_redirect' );

/**
 * Highlight the portfolio archive menu item.
 *
 * @param string[] $classes Menu item classes.
 * @param WP_Post  $item    Menu item.
 * @return string[]
 */
function woodshop_portfolio_menu_current( $classes, $item ) {
	if ( ! is_post_type_archive( 'portfolio' ) || empty( $item->url ) ) {
		return $classes;
	}

	$archive = get_post_type_archive_link( 'portfolio' );

	if ( $archive && untrailingslashit( $archive ) === untrailingslashit( $item->url ) ) {
		$classes[] = 'current-menu-item';
	}

	return $classes;
}
add_filter( 'nav_menu_css_class', 'woodshop_portfolio_menu_current', 10, 2 );
