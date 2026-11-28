<?php
/**
 * Template helpers.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Convert Latin digits to Persian digits for display.
 *
 * @param string $text Text.
 * @return string
 */
function woodshop_persian_digits( $text ) {
	return str_replace(
		array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
		(string) $text
	);
}

/**
 * Resolve a media option (attachment ID, URL or array) to a URL.
 *
 * @param mixed  $value Attachment ID, URL or ACF-like array.
 * @param string $size  Image size for attachment IDs.
 * @return string
 */
function woodshop_media_url( $value, $size = 'full' ) {
	if ( is_array( $value ) ) {
		$value = $value['url'] ?? ( $value['id'] ?? '' );
	}

	if ( is_numeric( $value ) ) {
		$url = wp_get_attachment_image_url( (int) $value, $size );
		return $url ? $url : '';
	}

	return is_string( $value ) ? $value : '';
}

/**
 * Output an image from a media option or URL.
 *
 * @param mixed                $value Media value.
 * @param array<string, mixed> $args  Image arguments.
 */
function woodshop_image( $value, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'alt'           => '',
			'class'         => '',
			'width'         => 800,
			'height'        => 533,
			'size'          => 'full',
			'loading'       => 'lazy',
			'fetchpriority' => '',
			'decoding'      => 'async',
			'sizes'         => '(max-width: 767px) 100vw, 50vw',
			'srcset'        => '',
		)
	);

	if ( empty( $value ) ) {
		return;
	}

	if ( is_numeric( $value ) ) {
		$attrs = array(
			'alt'     => $args['alt'],
			'class'   => $args['class'],
			'loading' => $args['loading'],
			'sizes'   => $args['sizes'],
		);

		if ( $args['fetchpriority'] ) {
			$attrs['fetchpriority'] = $args['fetchpriority'];
		}

		echo wp_get_attachment_image( (int) $value, $args['size'], false, $attrs );
		return;
	}

	$src = is_string( $value ) ? $value : '';

	if ( ! $src ) {
		return;
	}

	$srcset = $args['srcset'];

	printf(
		'<img src="%1$s" alt="%2$s" class="%3$s" width="%4$d" height="%5$d" loading="%6$s" decoding="%7$s"%8$s%9$s>',
		esc_url( $src ),
		esc_attr( $args['alt'] ),
		esc_attr( $args['class'] ),
		(int) $args['width'],
		(int) $args['height'],
		esc_attr( $args['loading'] ),
		esc_attr( $args['decoding'] ),
		$srcset ? ' srcset="' . esc_attr( $srcset ) . '"' : '',
		$args['fetchpriority'] ? ' fetchpriority="' . esc_attr( $args['fetchpriority'] ) . '"' : ''
	);
}

/**
 * Get an option image URL, with a theme asset fallback.
 *
 * @param string $path     Dot path.
 * @param string $fallback Relative asset path or absolute URL.
 * @param string $size     Image size for attachment IDs.
 * @return string
 */
function woodshop_option_image( $path, $fallback = '', $size = 'full' ) {
	$value = woodshop_has_studio() ? woodshop_studio_get( $path, null ) : null;
	$url   = woodshop_media_url( $value, $size );

	if ( $url ) {
		return $url;
	}

	if ( ! $fallback ) {
		return '';
	}

	return preg_match( '#^https?://#', $fallback ) ? $fallback : WOODSHOP_URI . '/assets/img/' . ltrim( $fallback, '/' );
}

/**
 * Render the section eyebrow.
 *
 * @param string $text Eyebrow text.
 * @param string $icon Icon key.
 */
function woodshop_eyebrow( $text, $icon = 'expand' ) {
	if ( ! $text ) {
		return;
	}

	echo '<span class="eyebrow">' . woodshop_icon( $icon, 'eyebrow__icon', 18 ) . '<span>' . esc_html( $text ) . '</span></span>';
}

/**
 * Social profile definitions.
 *
 * @return array<string, array{label: string, icon: string}>
 */
function woodshop_social_networks() {
	return array(
		'facebook'  => array( 'label' => 'فیسبوک', 'icon' => 'facebook' ),
		'x'         => array( 'label' => 'ایکس', 'icon' => 'x' ),
		'linkedin'  => array( 'label' => 'لینکدین', 'icon' => 'linkedin' ),
		'instagram' => array( 'label' => 'اینستاگرام', 'icon' => 'instagram' ),
		'youtube'   => array( 'label' => 'یوتیوب', 'icon' => 'youtube' ),
	);
}

/**
 * Output social links.
 *
 * @param array<int, array{network?: string, url?: string}> $links Social rows.
 * @param string                                            $class Wrapper class.
 */
function woodshop_socials( $links, $class = 'socials' ) {
	if ( empty( $links ) || ! is_array( $links ) ) {
		return;
	}

	$networks = woodshop_social_networks();

	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $links as $link ) {
		$network = $link['network'] ?? '';
		$url     = $link['url'] ?? '';

		if ( ! $url || ! isset( $networks[ $network ] ) ) {
			continue;
		}

		printf(
			'<li><a href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s">%3$s</a></li>',
			esc_url( $url ),
			esc_attr( $networks[ $network ]['label'] ),
			woodshop_icon( $networks[ $network ]['icon'], '', 17 )
		);
	}
	echo '</ul>';
}

/**
 * Pagination used on archives.
 */
function woodshop_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => esc_html__( 'Previous', 'woodshop' ),
			'next_text' => esc_html__( 'Next', 'woodshop' ),
			'class'     => 'ws-pagination',
		)
	);
}

/**
 * Build breadcrumb items for the current request.
 *
 * @return array<int, array{name: string, url: string}>
 */
function woodshop_breadcrumb_items() {
	if ( is_front_page() ) {
		return array();
	}

	$items    = array(
		array(
			'name' => __( 'Home', 'woodshop' ),
			'url'  => home_url( '/' ),
		),
	);
	$blog_url = get_permalink( (int) get_option( 'page_for_posts' ) );
	$blog_url = $blog_url ? $blog_url : home_url( '/' );
	$blog_name = get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : __( 'وبلاگ', 'woodshop' );

	$append_term = static function ( $term, $taxonomy ) use ( &$items ) {
		if ( ! $term instanceof WP_Term ) {
			return;
		}

		$ancestors = array_reverse( get_ancestors( $term->term_id, $taxonomy, 'taxonomy' ) );

		foreach ( $ancestors as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, $taxonomy );
			if ( $ancestor && ! is_wp_error( $ancestor ) ) {
				$items[] = array( 'name' => $ancestor->name, 'url' => get_term_link( $ancestor ) );
			}
		}

		$items[] = array( 'name' => $term->name, 'url' => get_term_link( $term ) );
	};

	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || is_product() ) ) {
		$items[] = array( 'name' => __( 'فروشگاه', 'woodshop' ), 'url' => wc_get_page_permalink( 'shop' ) );

		if ( is_product_category() ) {
			$append_term( get_queried_object(), 'product_cat' );
		} elseif ( is_product_tag() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$items[] = array( 'name' => $term->name, 'url' => get_term_link( $term ) );
			}
		} elseif ( is_product() ) {
			$terms = wp_get_post_terms( get_queried_object_id(), 'product_cat' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$append_term( $terms[0], 'product_cat' );
			}
			$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		}

		return $items;
	}

	if ( is_singular( 'page' ) ) {
		$ancestors = array_reverse( get_post_ancestors( get_queried_object_id() ) );
		foreach ( $ancestors as $ancestor_id ) {
			$items[] = array( 'name' => get_the_title( $ancestor_id ), 'url' => get_permalink( $ancestor_id ) );
		}
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		return $items;
	}

	if ( is_singular( 'post' ) ) {
		$items[] = array( 'name' => $blog_name, 'url' => $blog_url );

		$categories = get_the_category( get_queried_object_id() );
		if ( ! empty( $categories ) ) {
			$append_term( $categories[0], 'category' );
		}

		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		return $items;
	}

	if ( is_post_type_archive( 'portfolio' ) ) {
		$items[] = array( 'name' => __( 'نمونه‌کارها', 'woodshop' ), 'url' => get_post_type_archive_link( 'portfolio' ) );
		return $items;
	}

	if ( is_singular( 'portfolio' ) ) {
		$items[] = array( 'name' => __( 'نمونه‌کارها', 'woodshop' ), 'url' => get_post_type_archive_link( 'portfolio' ) );
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		return $items;
	}

	if ( is_home() ) {
		$items[] = array( 'name' => $blog_name, 'url' => $blog_url );
		return $items;
	}

	if ( is_category() || is_tag() ) {
		$items[] = array( 'name' => $blog_name, 'url' => $blog_url );
		$term    = get_queried_object();

		if ( $term instanceof WP_Term ) {
			if ( 'category' === $term->taxonomy ) {
				$append_term( $term, 'category' );
			} else {
				$items[] = array( 'name' => $term->name, 'url' => get_term_link( $term ) );
			}
		}

		return $items;
	}

	if ( is_archive() ) {
		$items[] = array( 'name' => wp_strip_all_tags( get_the_archive_title() ), 'url' => '' );
		return $items;
	}

	if ( is_search() ) {
		$items[] = array(
			'name' => sprintf( /* translators: %s: search query. */ __( 'Search results for “%s”', 'woodshop' ), get_search_query() ),
			'url'  => '',
		);
		return $items;
	}

	if ( is_404() ) {
		$items[] = array( 'name' => __( 'صفحه پیدا نشد', 'woodshop' ), 'url' => '' );
		return $items;
	}

	return $items;
}

/**
 * Render an accessible breadcrumb with BreadcrumbList structured data.
 */
function woodshop_breadcrumb() {
	static $rendered = false;

	if ( $rendered || is_front_page() ) {
		return;
	}

	$items = woodshop_breadcrumb_items();

	if ( count( $items ) < 2 ) {
		return;
	}

	$rendered = true;

	echo '<nav class="breadcrumb" aria-label="' . esc_attr__( 'مسیر صفحه', 'woodshop' ) . '"><ol>';
	$last = count( $items ) - 1;

	foreach ( $items as $index => $item ) {
		echo '<li>';
		if ( $index < $last && ! empty( $item['url'] ) ) {
			printf( '<a href="%1$s">%2$s</a>', esc_url( $item['url'] ), esc_html( $item['name'] ) );
		} else {
			printf( '<span aria-current="page">%s</span>', esc_html( $item['name'] ) );
		}
		echo '</li>';
	}

	echo '</ol></nav>';

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => array(),
	);

	foreach ( $items as $index => $item ) {
		$list_item = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => wp_strip_all_tags( $item['name'] ),
		);

		if ( ! empty( $item['url'] ) ) {
			$list_item['item'] = $item['url'];
		}

		$schema['itemListElement'][] = $list_item;
	}

	printf(
		'<script type="application/ld+json">%s</script>',
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}

/**
 * Inline squiggle used under highlighted words.
 *
 * @return string
 */
function woodshop_squiggle() {
	return '<svg class="squiggle" viewBox="0 0 300 14" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="M2 9c14-7 28-7 42 0s28 7 42 0 28-7 42 0 28 7 42 0 28-7 42 0 28 7 42 0 28-7 42 0" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>';
}

/**
 * Build a list of product categories for the services section.
 *
 * @param int[] $ids Category IDs.
 * @param int   $limit Max terms.
 * @return WP_Term[]
 */
function woodshop_product_categories( $ids = array(), $limit = 6 ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$args = array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'number'     => $limit,
		'orderby'    => empty( $ids ) ? 'term_id' : 'include',
		'order'      => 'ASC',
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
	);

	if ( ! empty( $ids ) ) {
		$args['include'] = array_map( 'absint', $ids );
	} else {
		$args['parent'] = 0;
	}

	$terms = get_terms( $args );

	return is_wp_error( $terms ) ? array() : array_slice( $terms, 0, $limit );
}

/**
 * Build a srcset for an attachment from its generated sizes.
 *
 * @param int $attachment_id Attachment ID.
 * @return string
 */
function woodshop_attachment_srcset( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	$meta          = wp_get_attachment_metadata( $attachment_id );
	$url           = wp_get_attachment_url( $attachment_id );

	if ( ! $meta || ! $url ) {
		return '';
	}

	$base   = trailingslashit( dirname( $url ) );
	$srcset = array();

	foreach ( (array) ( $meta['sizes'] ?? array() ) as $size ) {
		if ( empty( $size['file'] ) || empty( $size['width'] ) ) {
			continue;
		}

		$srcset[ (int) $size['width'] ] = $base . $size['file'] . ' ' . (int) $size['width'] . 'w';
	}

	if ( ! empty( $meta['width'] ) ) {
		$srcset[ (int) $meta['width'] ] = $url . ' ' . (int) $meta['width'] . 'w';
	}

	ksort( $srcset );

	return implode( ', ', array_values( $srcset ) );
}

/**
 * Category image URL.
 *
 * @param WP_Term $term Category term.
 * @param string  $size Image size.
 * @return string
 */
function woodshop_term_image( $term, $size = 'ws-card' ) {
	$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

	if ( $thumb_id ) {
		$url = wp_get_attachment_image_url( $thumb_id, $size );
		if ( $url ) {
			return $url;
		}
	}

	return '';
}

/**
 * Render one custom WooCommerce product card.
 *
 * @param WC_Product $product Product object.
 */
function woodshop_product_card( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$link     = get_permalink( $product->get_id() );
	$image_id = $product->get_image_id();
	$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'ws-square' ) : wc_placeholder_img_src( 'ws-square' );
	$srcset   = $image_id ? woodshop_attachment_srcset( $image_id ) : '';
	$cats     = wc_get_product_category_list( $product->get_id(), ', ' );

	echo '<li class="product-card">';
	echo '<a class="product-card__media" href="' . esc_url( $link ) . '" tabindex="-1" aria-hidden="true">';
	printf(
		'<img src="%1$s" alt="" width="700" height="700" loading="lazy" decoding="async" srcset="%2$s" sizes="(max-width: 767px) 100vw, (max-width: 1024px) 50vw, 300px">',
		esc_url( $image ),
		esc_attr( $srcset )
	);
	if ( $product->is_on_sale() ) {
		echo '<span class="product-card__badge">' . esc_html__( 'Sale', 'woodshop' ) . '</span>';
	}
	echo '</a>';
	echo '<div class="product-card__body">';
	if ( $cats ) {
		echo '<div class="product-card__cat">' . wp_kses_post( $cats ) . '</div>';
	}
	echo '<h3 class="product-card__title"><a href="' . esc_url( $link ) . '">' . esc_html( $product->get_name() ) . '</a></h3>';
	echo '<div class="product-card__price">' . wp_kses_post( $product->get_price_html() ) . '</div>';

	if ( $product->is_type( 'variable' ) ) {
		echo '<a class="btn btn--sm product-card__btn" href="' . esc_url( $link ) . '">' . esc_html__( 'View Product', 'woodshop' ) . '</a>';
	} elseif ( $product->is_purchasable() && $product->is_in_stock() ) {
		echo '<a class="btn btn--sm product-card__btn" href="' . esc_url( $product->add_to_cart_url() ) . '" data-add-to-cart="' . esc_attr( (string) $product->get_id() ) . '" data-quantity="1" rel="nofollow">';
		echo '<span class="product-card__spinner" aria-hidden="true"></span>';
		echo '<span class="product-card__btn-icon product-card__btn-icon--cart">' . woodshop_icon( 'basket', '', 16 ) . '</span>';
		echo '<span class="product-card__btn-icon product-card__btn-icon--check">' . woodshop_icon( 'check-simple', '', 16 ) . '</span>';
		echo '<span class="product-card__btn-label">' . esc_html__( 'افزودن به سبد خرید', 'woodshop' ) . '</span>';
		echo '<span class="screen-reader-text"> ' . esc_html( $product->get_name() ) . '</span>';
		echo '</a>';
	} else {
		echo '<button type="button" class="btn btn--sm product-card__btn is-disabled" disabled>' . esc_html__( 'ناموجود', 'woodshop' ) . '</button>';
	}

	echo '</div></li>';
}
