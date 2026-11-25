<?php
/**
 * Performance, head cleanup and SEO output.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove unused head output.
 */
function woodshop_clean_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
}
add_action( 'init', 'woodshop_clean_head' );

/**
 * Drop the WordPress.org resource hint.
 *
 * @param array<int, string|array<string, mixed>> $hints Existing hints.
 * @param string                                  $relation Relation type.
 * @return array<int, string|array<string, mixed>>
 */
function woodshop_resource_hints( $hints, $relation ) {
	if ( 'dns-prefetch' === $relation ) {
		$hints = array_filter(
			$hints,
			static function ( $hint ) {
				return false === strpos( (string) $hint, 's.w.org' );
			}
		);
	}

	return $hints;
}
add_filter( 'wp_resource_hints', 'woodshop_resource_hints', 10, 2 );

/**
 * Dequeue heavy default assets on the front page only.
 */
function woodshop_dequeue_front_assets() {
	if ( ! is_front_page() || is_admin() ) {
		return;
	}

	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'core-block-supports' ) as $handle ) {
		wp_dequeue_style( $handle );
	}

	foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-inline', 'wc-blocks-style', 'wc-blocks-style-all-products', 'wc-blocks-style-mini-cart', 'wc-blocks-style-cart', 'wc-blocks-style-checkout' ) as $handle ) {
		wp_dequeue_style( $handle );
	}

	foreach ( array( 'jquery', 'jquery-core', 'jquery-migrate', 'woocommerce', 'wc-add-to-cart', 'wc-cart-fragments', 'jquery-blockui', 'js-cookie', 'sourcebuster-js', 'wc-order-attribution' ) as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'woodshop_dequeue_front_assets', 100 );

/**
 * Drop WordPress global styles on the front page: the theme does not use block
 * presets and the inline payload is ~9 KB of unused CSS.
 */
function woodshop_remove_global_styles_on_front() {
	if ( ! is_front_page() ) {
		return;
	}

	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles_custom_css' );
}
add_action( 'wp_enqueue_scripts', 'woodshop_remove_global_styles_on_front', 1 );

/**
 * Inline the critical CSS and load the rest of the stylesheet without blocking
 * the first paint. Only used on the front page.
 */
function woodshop_front_styles() {
	if ( ! is_front_page() ) {
		return;
	}

	$critical_path = woodshop_asset_path( 'assets/css/critical.css' );

	if ( file_exists( $critical_path ) ) {
		$critical = (string) file_get_contents( $critical_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$critical = str_replace( '../fonts/', WOODSHOP_URI . '/assets/fonts/', $critical );

		echo '<style id="woodshop-critical-css">' . $critical . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( is_rtl() ) {
		$critical_rtl_path = woodshop_asset_path( 'assets/css/critical-rtl.css' );

		if ( file_exists( $critical_rtl_path ) ) {
			$critical_rtl = (string) file_get_contents( $critical_rtl_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$critical_rtl = str_replace( '../fonts/', WOODSHOP_URI . '/assets/fonts/', $critical_rtl );

			echo '<style id="woodshop-critical-rtl-css">' . $critical_rtl . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	$main_path = woodshop_asset_path( 'assets/css/main.css' );
	$main_uri  = str_replace( WOODSHOP_DIR, WOODSHOP_URI, $main_path );
	$version   = file_exists( $main_path ) ? (string) filemtime( $main_path ) : WOODSHOP_VERSION;
	$main_uri .= '?ver=' . $version;

	printf( '<link rel="preload" as="style" href="%s">' . "\n", esc_url( $main_uri ) );
	printf(
		'<link rel="stylesheet" id="woodshop-main-css" href="%1$s" media="print" onload="this.media=\'all\'">' . "\n",
		esc_url( $main_uri )
	);
	printf( '<noscript><link rel="stylesheet" href="%s"></noscript>' . "\n", esc_url( $main_uri ) );

	if ( is_rtl() ) {
		$rtl_path = woodshop_asset_path( 'assets/css/rtl.css' );
		$rtl_uri  = str_replace( WOODSHOP_DIR, WOODSHOP_URI, $rtl_path );
		$rtl_uri .= '?ver=' . ( file_exists( $rtl_path ) ? (string) filemtime( $rtl_path ) : WOODSHOP_VERSION );

		printf( '<link rel="preload" as="style" href="%s">' . "\n", esc_url( $rtl_uri ) );
		printf(
			'<link rel="stylesheet" id="woodshop-rtl-css" href="%1$s" media="print" onload="this.media=\'all\'">' . "\n",
			esc_url( $rtl_uri )
		);
		printf( '<noscript><link rel="stylesheet" href="%s"></noscript>' . "\n", esc_url( $rtl_uri ) );
	}
}
add_action( 'wp_head', 'woodshop_front_styles', 3 );

/**
 * Preload fonts and the hero image.
 */
function woodshop_preload_assets() {
	$fonts = is_rtl()
		? array( 'vazirmatn-arabic.woff2', 'vazirmatn-latin.woff2' )
		: array( 'saira-condensed-700.woff2', 'roboto-var.woff2' );

	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( WOODSHOP_URI . '/assets/fonts/' . $font )
		);
	}

	if ( ! is_front_page() ) {
		return;
	}

	$first = woodshop_has_studio() ? woodshop_studio_get( 'hero.slides.0.image', '' ) : '';
	$src   = woodshop_media_url( $first, 'full' );

	if ( ! $src ) {
		$src = WOODSHOP_URI . '/assets/img/hero-1.webp';
	}

	if ( is_numeric( $first ) ) {
		$srcset = wp_get_attachment_image_srcset( (int) $first, 'full' );
		printf(
			'<link rel="preload" as="image" href="%1$s"%2$s imagesizes="100vw" fetchpriority="high">' . "\n",
			esc_url( $src ),
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : ''
		);
	} else {
		$srcset = '';

		foreach ( array( 'hero-1', 'hero-2', 'hero-3' ) as $base ) {
			if ( false !== strpos( $src, '/' . $base . '.webp' ) ) {
				$srcset = WOODSHOP_URI . '/assets/img/' . $base . '.webp 960w, ' . WOODSHOP_URI . '/assets/img/' . $base . '@2x.webp 1600w';
				break;
			}
		}

		printf(
			'<link rel="preload" as="image" href="%1$s"%2$s imagesizes="100vw" fetchpriority="high">' . "\n",
			esc_url( $src ),
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : ''
		);
	}
}
add_action( 'wp_head', 'woodshop_preload_assets', 2 );

/**
 * Meta description, Open Graph and theme color.
 */
function woodshop_meta_tags() {
	$description = '';

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$description = __( 'فروشگاه چوب‌کاری: مبلمان سفارشی، کابینت، کف‌پوش چوبی و دکوراسیون چوبی با کیفیت ساخت بالا.', 'woodshop' );
	} elseif ( is_front_page() ) {
		$description = woodshop_option( 'hero.subtitle', get_bloginfo( 'description' ) );
	} elseif ( function_exists( 'is_account_page' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		if ( is_cart() ) {
			$description = __( 'سبد خرید فروشگاه چوب‌کاری؛ محصولات انتخابی خود را بررسی و سفارش را نهایی کنید.', 'woodshop' );
		} elseif ( is_checkout() ) {
			$description = __( 'تسویه‌حساب امن و ساده سفارش‌های چوب‌کاری با پرداخت در محل.', 'woodshop' );
		} else {
			$description = __( 'حساب کاربری فروشگاه چوب‌کاری؛ مشاهده سفارش‌ها و ویرایش اطلاعات شخصی.', 'woodshop' );
		}
	} elseif ( is_home() ) {
		$description = __( 'مقالات و راهنمای تخصصی چوب‌کاری، طراحی داخلی و نگهداری از مبلمان چوبی از کارگاه ما.', 'woodshop' );
	} elseif ( is_singular() ) {
		$description = get_the_excerpt();

		if ( is_page() ) {
			$ws_page_keys = array(
				'about'     => 'page_about.intro',
				'services'  => 'page_services.intro',
				'contact'   => 'page_contact.intro',
			);
			$ws_slug      = get_post_field( 'post_name', get_queried_object_id() );

			if ( isset( $ws_page_keys[ $ws_slug ] ) ) {
				$description = woodshop_option( $ws_page_keys[ $ws_slug ], $description );
			}
		}
	} elseif ( is_post_type_archive( 'portfolio' ) ) {
		$description = woodshop_option( 'page_portfolio.intro', wp_strip_all_tags( get_the_archive_description() ) );
	} elseif ( is_archive() ) {
		$description = wp_strip_all_tags( get_the_archive_description() );

		if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) && ! $description ) {
			$description = __( 'فروشگاه چوب‌کاری: مبلمان سفارشی، کابینت، کف‌پوش چوبی و دکوراسیون چوبی با کیفیت ساخت بالا.', 'woodshop' );
		}
	}

	$description = wp_trim_words( wp_strip_all_tags( (string) $description ), 32, '' );

	$ws_palette = woodshop_palette();
	printf( '<meta name="theme-color" content="%s">' . "\n", esc_attr( $ws_palette['bg'] ) );

	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}

	if ( is_singular() ) {
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( get_the_title() ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( get_permalink() ) );
		printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'post' ) ? 'article' : 'website' );

		if ( has_post_thumbnail() ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( get_the_post_thumbnail_url( null, 'large' ) ) );
		}
	} else {
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	}
}
add_action( 'wp_head', 'woodshop_meta_tags', 5 );

/**
 * LocalBusiness structured data.
 */
function woodshop_schema() {
	if ( ! is_front_page() ) {
		return;
	}

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'HomeAndConstructionBusiness',
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'telephone'   => woodshop_option( 'brand.phone', '' ),
		'email'       => woodshop_option( 'brand.email', '' ),
		'description' => woodshop_option( 'hero.subtitle', get_bloginfo( 'description' ) ),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => woodshop_option( 'brand.address', '' ),
		),
	);

	$logo = get_theme_mod( 'custom_logo' );

	if ( $logo ) {
		$schema['logo'] = wp_get_attachment_image_url( $logo, 'full' );
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( array_filter( $schema ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_footer', 'woodshop_schema', 5 );

/**
 * BlogPosting structured data for single posts.
 */
function woodshop_article_schema() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$post_id = get_queried_object_id();
	$schema  = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'BlogPosting',
		'headline'         => get_the_title( $post_id ),
		'description'      => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
		'datePublished'    => get_the_date( 'c', $post_id ),
		'dateModified'     => get_the_modified_date( 'c', $post_id ),
		'author'           => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ),
		),
		'publisher'        => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
		),
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post_id ),
		),
	);

	$image = get_the_post_thumbnail_url( $post_id, 'large' );

	if ( $image ) {
		$schema['image'] = $image;
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "
",
		wp_json_encode( array_filter( $schema ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'woodshop_article_schema', 6 );
