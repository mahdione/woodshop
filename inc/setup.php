<?php
/**
 * Theme setup and asset loading.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prefer a minified asset only when it is at least as new as the source.
 *
 * @param string $relative Path relative to the theme, e.g. "assets/css/main.css".
 * @return string Absolute path.
 */
function woodshop_asset_path( $relative ) {
	$source  = WOODSHOP_DIR . '/' . $relative;
	$min     = preg_replace( '/\.(css|js)$/', '.min.$1', $source );
	$min     = is_string( $min ) ? $min : $source;

	if ( file_exists( $min ) && file_exists( $source ) && filemtime( $min ) >= filemtime( $source ) ) {
		return $min;
	}

	return $source;
}

/**
 * Register theme features.
 */
function woodshop_setup() {
	load_theme_textdomain( 'woodshop', WOODSHOP_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 168,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 1000,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary'         => __( 'Primary navigation', 'woodshop' ),
			'footer_services' => __( 'Footer: services', 'woodshop' ),
			'footer_legal'    => __( 'Footer: legal links', 'woodshop' ),
		)
	);

	add_image_size( 'ws-card', 800, 533, true );
	add_image_size( 'ws-square', 700, 700, true );
	add_image_size( 'ws-thumb', 320, 220, true );
	add_image_size( 'ws-wide', 1200, 700, true );
}
add_action( 'after_setup_theme', 'woodshop_setup' );

/**
 * Content width used by embeds and legacy media.
 */
function woodshop_content_width() {
	$GLOBALS['content_width'] = 1290;
}
add_action( 'after_setup_theme', 'woodshop_content_width', 0 );

/**
 * Load front-end assets.
 */
function woodshop_assets() {
	$css_path = woodshop_asset_path( 'assets/css/main.css' );
	$js_path  = woodshop_asset_path( 'assets/js/main.js' );

	/*
	 * The front page inlines its critical CSS (see inc/performance.php) and
	 * loads main.css asynchronously to keep the render path short.
	 */
	if ( ! is_front_page() ) {
		wp_enqueue_style(
			'woodshop-main',
			str_replace( WOODSHOP_DIR, WOODSHOP_URI, $css_path ),
			array(),
			file_exists( $css_path ) ? (string) filemtime( $css_path ) : WOODSHOP_VERSION
		);

		if ( is_rtl() ) {
			$rtl_path = woodshop_asset_path( 'assets/css/rtl.css' );
			wp_enqueue_style(
				'woodshop-rtl',
				str_replace( WOODSHOP_DIR, WOODSHOP_URI, $rtl_path ),
				array( 'woodshop-main' ),
				file_exists( $rtl_path ) ? (string) filemtime( $rtl_path ) : WOODSHOP_VERSION
			);
		}
	}

	wp_enqueue_script(
		'woodshop-main',
		str_replace( WOODSHOP_DIR, WOODSHOP_URI, $js_path ),
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : WOODSHOP_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'woodshop_assets', 20 );

add_action( 'wp_body_open', 'woodshop_icon_sprite', 1 );

/**
 * Add helpful body classes.
 *
 * @param string[] $classes Existing classes.
 * @return string[]
 */
function woodshop_body_classes( $classes ) {
	$classes[] = 'ws-theme';

	if ( is_front_page() ) {
		$classes[] = 'ws-front';
	}

	if ( is_singular() ) {
		$classes[] = 'ws-singular';
	}

	return $classes;
}
add_filter( 'body_class', 'woodshop_body_classes' );

/**
 * Use a compact excerpt.
 *
 * @return int
 */
function woodshop_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'woodshop_excerpt_length' );

/**
 * Replace the default excerpt suffix.
 *
 * @return string
 */
function woodshop_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'woodshop_excerpt_more' );

/**
 * Output a menu with a graceful page-list fallback.
 *
 * @param string $location Menu location slug.
 * @param string $class    UL class name.
 */
function woodshop_menu( $location, $class = 'ws-menu' ) {
	$locations = get_nav_menu_locations();

	if ( ! empty( $locations[ $location ] ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => $class,
				'depth'          => 2,
				'fallback_cb'    => false,
			)
		);

		return;
	}

	$fallback = array(
		'primary'         => array( 'Home' => home_url( '/' ), 'Shop' => woodshop_shop_url(), 'Blog' => get_permalink( get_option( 'page_for_posts' ) ) ),
		'footer_services' => array(),
		'footer_legal'    => array(),
	);

	if ( empty( $fallback[ $location ] ) ) {
		return;
	}

	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $fallback[ $location ] as $label => $url ) {
		if ( ! $url ) {
			continue;
		}
		printf(
			'<li class="menu-item"><a href="%1$s">%2$s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Shop URL, with a safe fallback when WooCommerce is inactive.
 *
 * @return string
 */
function woodshop_shop_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}

	return home_url( '/' );
}

/**
 * Cart URL and count.
 *
 * @return array{url: string, count: int}
 */
function woodshop_cart_info() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array(
			'url'   => woodshop_shop_url(),
			'count' => 0,
		);
	}

	return array(
		'url'   => wc_get_cart_url(),
		'count' => (int) WC()->cart->get_cart_contents_count(),
	);
}

/**
 * Add a featured-image column to posts in the admin? No. Keep the theme lean.
 */

/**
 * Register a widget area for future use.
 */
function woodshop_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'woodshop' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Shown on blog archives.', 'woodshop' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'woodshop_widgets_init' );

/**
 * Pingback header on singular pages.
 */
function woodshop_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'woodshop_pingback_header' );

/**
 * Inline script that marks JS support before paint (prevents reveal FOUC).
 */
function woodshop_js_flag() {
	echo '<script>document.documentElement.className+=" ws-js";</script>' . "\n";
}
add_action( 'wp_head', 'woodshop_js_flag', 1 );
