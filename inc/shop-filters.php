<?php
/**
 * Product archive filters, layout and SEO safeguards.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base URL of the current product archive (without filter query args).
 *
 * @return string
 */
function woodshop_shop_archive_url() {
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return get_term_link( $term );
		}
	}

	if ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return get_term_link( $term );
		}
	}

	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/**
 * Current filter values, sanitized.
 *
 * @return array<string, mixed>
 */
function woodshop_shop_filter_values() {
	$cats = isset( $_GET['filter_cat'] ) ? array_filter( array_map( 'absint', (array) wp_unslash( $_GET['filter_cat'] ) ) ) : array();
	$tags = isset( $_GET['filter_tag'] ) ? array_filter( array_map( 'absint', (array) wp_unslash( $_GET['filter_tag'] ) ) ) : array();

	$min = isset( $_GET['min_price'] ) ? (float) wp_unslash( $_GET['min_price'] ) : '';
	$max = isset( $_GET['max_price'] ) ? (float) wp_unslash( $_GET['max_price'] ) : '';

	if ( '' !== $min && '' !== $max && $min > $max ) {
		$swap = $min;
		$min  = $max;
		$max  = $swap;
	}

	return array(
		'filter_cat' => array_values( $cats ),
		'filter_tag' => array_values( $tags ),
		'min_price'   => '' === $min ? '' : $min,
		'max_price'   => '' === $max ? '' : $max,
		'in_stock'    => ! empty( $_GET['in_stock'] ),
		'on_sale'     => ! empty( $_GET['on_sale'] ),
		's'           => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
	);
}

/**
 * Does the current request have any product filter?
 *
 * @return bool
 */
function woodshop_shop_has_filters() {
	$values = woodshop_shop_filter_values();

	return ! empty( $values['filter_cat'] ) || ! empty( $values['filter_tag'] ) || '' !== $values['min_price'] || '' !== $values['max_price'] || $values['in_stock'] || $values['on_sale'] || '' !== $values['s'];
}

/**
 * Count active filter groups.
 *
 * @return int
 */
function woodshop_shop_filter_count() {
	$values = woodshop_shop_filter_values();
	$count  = 0;

	foreach ( array( 'filter_cat', 'filter_tag' ) as $key ) {
		if ( ! empty( $values[ $key ] ) ) {
			$count++;
		}
	}

	if ( '' !== $values['min_price'] || '' !== $values['max_price'] ) {
		$count++;
	}

	if ( $values['in_stock'] ) {
		$count++;
	}

	if ( $values['on_sale'] ) {
		$count++;
	}

	if ( '' !== $values['s'] ) {
		$count++;
	}

	return $count;
}

/**
 * Apply filters to the main product query.
 *
 * @param WP_Query $query Query.
 */
function woodshop_apply_shop_filters( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
		return;
	}

	$values = woodshop_shop_filter_values();

	$meta_query = (array) $query->get( 'meta_query' );

	if ( '' !== $values['min_price'] || '' !== $values['max_price'] ) {
		$range = array();

		if ( '' !== $values['min_price'] ) {
			$range[] = $values['min_price'];
		} else {
			$range[] = 0;
		}

		if ( '' !== $values['max_price'] ) {
			$range[] = $values['max_price'];
		} else {
			$range[] = PHP_FLOAT_MAX;
		}

		$meta_query[] = array(
			'key'     => '_price',
			'value'   => $range,
			'compare' => 'BETWEEN',
			'type'    => 'NUMERIC',
		);
	}

	if ( $values['in_stock'] ) {
		$meta_query[] = array(
			'key'     => '_stock_status',
			'value'   => 'instock',
			'compare' => '=',
		);
	}

	if ( $meta_query ) {
		$query->set( 'meta_query', $meta_query );
	}

	$tax_query = (array) $query->get( 'tax_query' );

	if ( is_shop() && ! empty( $values['filter_cat'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $values['filter_cat'],
			'operator' => 'IN',
		);
	}

	if ( ! empty( $values['filter_tag'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_tag',
			'field'    => 'term_id',
			'terms'    => $values['filter_tag'],
			'operator' => 'IN',
		);
	}

	if ( count( $tax_query ) > 1 && ! isset( $tax_query['relation'] ) ) {
		$tax_query['relation'] = 'AND';
	}

	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}

	if ( $values['on_sale'] ) {
		$sale_ids = function_exists( 'wc_get_product_ids_on_sale' ) ? wc_get_product_ids_on_sale() : array();
		$sale_ids = array_map( 'absint', (array) $sale_ids );

		if ( empty( $sale_ids ) ) {
			$query->set( 'post__in', array( 0 ) );
		} else {
			$existing = (array) $query->get( 'post__in' );
			$query->set( 'post__in', $existing ? array_values( array_intersect( $existing, $sale_ids ) ) : $sale_ids );
		}
	}
}
add_action( 'pre_get_posts', 'woodshop_apply_shop_filters', 30 );

/**
 * Product category terms for filter lists.
 *
 * @return WP_Term[]
 */
function woodshop_filter_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Product tag terms for filter lists.
 *
 * @return WP_Term[]
 */
function woodshop_filter_tags() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_tag',
			'hide_empty' => true,
			'number'     => 14,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Render the product filter form.
 */
function woodshop_shop_filters_markup() {
	$values     = woodshop_shop_filter_values();
	$action     = woodshop_shop_archive_url();
	$categories = woodshop_filter_categories();
	$tags       = woodshop_filter_tags();
	$clear_url  = remove_query_arg( array( 'filter_cat', 'filter_tag', 'min_price', 'max_price', 'in_stock', 'on_sale', 's' ), $action );
	?>
	<aside id="shop-filters" class="shop-filters" data-shop-filters aria-label="<?php esc_attr_e( 'فیلتر محصولات', 'woodshop' ); ?>">
		<div class="shop-filters__head">
			<h2 class="shop-filters__title"><?php esc_html_e( 'فیلترها', 'woodshop' ); ?></h2>
			<button type="button" class="shop-filters__close" data-shop-filters-close aria-label="<?php esc_attr_e( 'بستن فیلترها', 'woodshop' ); ?>">
				<?php echo woodshop_icon( 'close', '', 18 ); ?>
			</button>
		</div>

		<form class="shop-filter-form" method="get" action="<?php echo esc_url( $action ); ?>">
			<div class="filter-group">
				<label class="filter-label" for="shop-filter-search"><?php esc_html_e( 'جستجو در محصولات', 'woodshop' ); ?></label>
				<div class="filter-search">
					<?php echo woodshop_icon( 'search', 'filter-search__icon', 17 ); ?>
					<input id="shop-filter-search" type="search" name="s" value="<?php echo esc_attr( $values['s'] ); ?>" placeholder="<?php esc_attr_e( 'نام محصول…', 'woodshop' ); ?>">
				</div>
			</div>

			<?php if ( ! empty( $categories ) ) : ?>
				<div class="filter-group">
					<span class="filter-label"><?php esc_html_e( 'دسته‌بندی', 'woodshop' ); ?></span>
					<?php if ( is_shop() ) : ?>
						<ul class="filter-checks">
							<?php foreach ( $categories as $term ) : ?>
								<li>
									<label>
										<input type="checkbox" name="filter_cat[]" value="<?php echo esc_attr( (string) $term->term_id ); ?>" <?php checked( in_array( (int) $term->term_id, $values['filter_cat'], true ) ); ?>>
										<span><?php echo esc_html( $term->name ); ?></span>
										<em><?php echo esc_html( woodshop_persian_digits( (string) $term->count ) ); ?></em>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<ul class="filter-links">
							<?php foreach ( $categories as $term ) : ?>
								<?php $is_current = is_product_category( $term->slug ); ?>
								<li>
									<a class="<?php echo $is_current ? 'is-current' : ''; ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
										<span><?php echo esc_html( $term->name ); ?></span>
										<em><?php echo esc_html( woodshop_persian_digits( (string) $term->count ) ); ?></em>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="filter-group">
				<span class="filter-label"><?php esc_html_e( 'محدوده قیمت (تومان)', 'woodshop' ); ?></span>
				<div class="filter-price">
					<label>
						<span><?php esc_html_e( 'از', 'woodshop' ); ?></span>
						<input type="number" name="min_price" value="<?php echo esc_attr( '' === $values['min_price'] ? '' : (string) (int) $values['min_price'] ); ?>" placeholder="۰" min="0" step="100000" inputmode="numeric">
					</label>
					<label>
						<span><?php esc_html_e( 'تا', 'woodshop' ); ?></span>
						<input type="number" name="max_price" value="<?php echo esc_attr( '' === $values['max_price'] ? '' : (string) (int) $values['max_price'] ); ?>" placeholder="بدون محدودیت" min="0" step="100000" inputmode="numeric">
					</label>
				</div>
			</div>

			<?php if ( ! empty( $tags ) && ! is_product_tag() ) : ?>
				<div class="filter-group">
					<span class="filter-label"><?php esc_html_e( 'برچسب محصول', 'woodshop' ); ?></span>
					<ul class="filter-checks filter-checks--tags">
						<?php foreach ( $tags as $term ) : ?>
							<li>
								<label>
									<input type="checkbox" name="filter_tag[]" value="<?php echo esc_attr( (string) $term->term_id ); ?>" <?php checked( in_array( (int) $term->term_id, $values['filter_tag'], true ) ); ?>>
									<span><?php echo esc_html( $term->name ); ?></span>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="filter-group">
				<span class="filter-label"><?php esc_html_e( 'وضعیت', 'woodshop' ); ?></span>
				<ul class="filter-checks">
					<li>
						<label>
							<input type="checkbox" name="in_stock" value="1" <?php checked( $values['in_stock'] ); ?>>
							<span><?php esc_html_e( 'فقط کالاهای موجود', 'woodshop' ); ?></span>
						</label>
					</li>
					<li>
						<label>
							<input type="checkbox" name="on_sale" value="1" <?php checked( $values['on_sale'] ); ?>>
							<span><?php esc_html_e( 'فقط تخفیف‌دار', 'woodshop' ); ?></span>
						</label>
					</li>
				</ul>
			</div>

			<div class="shop-filter-actions">
				<button type="submit" class="btn"><?php esc_html_e( 'اعمال فیلتر', 'woodshop' ); ?></button>
				<a class="filter-clear" href="<?php echo esc_url( $clear_url ); ?>"><?php esc_html_e( 'پاک کردن فیلترها', 'woodshop' ); ?></a>
			</div>
		</form>
	</aside>
	<?php
}

/**
 * Active filter chips.
 */
function woodshop_shop_active_filters() {
	$values = woodshop_shop_filter_values();
	$base   = woodshop_shop_archive_url();
	$chips  = array();
	$query  = array();

	if ( ! empty( $values['filter_cat'] ) ) {
		$query['filter_cat'] = array_map( 'strval', $values['filter_cat'] );
	}
	if ( ! empty( $values['filter_tag'] ) ) {
		$query['filter_tag'] = array_map( 'strval', $values['filter_tag'] );
	}
	if ( '' !== $values['min_price'] ) {
		$query['min_price'] = (string) (int) $values['min_price'];
	}
	if ( '' !== $values['max_price'] ) {
		$query['max_price'] = (string) (int) $values['max_price'];
	}
	if ( $values['in_stock'] ) {
		$query['in_stock'] = '1';
	}
	if ( $values['on_sale'] ) {
		$query['on_sale'] = '1';
	}
	if ( '' !== $values['s'] ) {
		$query['s'] = $values['s'];
	}

	$current = $query ? add_query_arg( $query, $base ) : $base;

	if ( ! empty( $values['filter_cat'] ) && is_shop() ) {
		$names = array();
		foreach ( $values['filter_cat'] as $term_id ) {
			$term = get_term( $term_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$names[] = $term->name;
			}
		}
		if ( $names ) {
			$chips[] = array(
				'label' => __( 'دسته‌بندی:', 'woodshop' ) . ' ' . implode( '، ', $names ),
				'url'   => remove_query_arg( 'filter_cat', $current ),
			);
		}
	}

	if ( ! empty( $values['filter_tag'] ) ) {
		$names = array();
		foreach ( $values['filter_tag'] as $term_id ) {
			$term = get_term( $term_id, 'product_tag' );
			if ( $term && ! is_wp_error( $term ) ) {
				$names[] = $term->name;
			}
		}
		if ( $names ) {
			$chips[] = array(
				'label' => __( 'برچسب:', 'woodshop' ) . ' ' . implode( '، ', $names ),
				'url'   => remove_query_arg( 'filter_tag', $current ),
			);
		}
	}

	if ( '' !== $values['min_price'] || '' !== $values['max_price'] ) {
		$label = __( 'قیمت:', 'woodshop' ) . ' ';
		$label .= '' !== $values['min_price'] ? woodshop_persian_digits( number_format_i18n( (float) $values['min_price'] ) ) : '۰';
		$label .= ' — ';
		$label .= '' !== $values['max_price'] ? woodshop_persian_digits( number_format_i18n( (float) $values['max_price'] ) ) : 'بدون محدودیت';
		$label .= ' ' . __( 'تومان', 'woodshop' );

		$chips[] = array(
			'label' => $label,
			'url'   => remove_query_arg( array( 'min_price', 'max_price' ), $current ),
		);
	}

	if ( $values['in_stock'] ) {
		$chips[] = array(
			'label' => __( 'فقط کالاهای موجود', 'woodshop' ),
			'url'   => remove_query_arg( 'in_stock', $current ),
		);
	}

	if ( $values['on_sale'] ) {
		$chips[] = array(
			'label' => __( 'فقط تخفیف‌دار', 'woodshop' ),
			'url'   => remove_query_arg( 'on_sale', $current ),
		);
	}

	if ( '' !== $values['s'] ) {
		$chips[] = array(
			'label' => __( 'جستجو:', 'woodshop' ) . ' ' . $values['s'],
			'url'   => remove_query_arg( 's', $current ),
		);
	}

	if ( empty( $chips ) ) {
		return;
	}
	?>
	<div class="shop-active-filters" aria-label="<?php esc_attr_e( 'فیلترهای فعال', 'woodshop' ); ?>">
		<?php foreach ( $chips as $chip ) : ?>
			<a class="filter-chip" href="<?php echo esc_url( $chip['url'] ); ?>">
				<span><?php echo esc_html( $chip['label'] ); ?></span>
				<?php echo woodshop_icon( 'close', '', 12 ); ?>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Toolbar above the product loop.
 */
function woodshop_shop_toolbar() {
	$count = woodshop_shop_filter_count();
	?>
	<div class="shop-toolbar">
		<button type="button" class="shop-filter-toggle" data-shop-filters-open aria-controls="shop-filters" aria-expanded="false">
			<?php echo woodshop_icon( 'filter', '', 18 ); ?>
			<span><?php esc_html_e( 'فیلترها', 'woodshop' ); ?></span>
			<?php if ( $count ) : ?>
				<em><?php echo esc_html( woodshop_persian_digits( (string) $count ) ); ?></em>
			<?php endif; ?>
		</button>
	</div>
	<?php
	woodshop_shop_active_filters();
}

/**
 * Open the shop layout after the archive description.
 */
function woodshop_shop_layout_open() {
	if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
		return;
	}

	echo '<div class="shop-layout">';
	woodshop_shop_filters_markup();
	echo '<div class="shop-filters-overlay" data-shop-filters-close></div>';
	echo '<div class="shop-results">';
	woodshop_shop_toolbar();
}
add_action( 'woocommerce_shop_loop_header', 'woodshop_shop_layout_open', 20 );

/**
 * Close the shop layout before the page wrapper closes.
 */
function woodshop_shop_layout_close() {
	if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
		return;
	}

	echo '</div></div>';
}
add_action( 'woocommerce_after_main_content', 'woodshop_shop_layout_close', 5 );

/**
 * Noindex filtered archives and private Woo pages; canonicalise filtered archives.
 *
 * @param array<string, bool> $robots Robots directives.
 * @return array<string, bool>
 */
function woodshop_shop_robots( $robots ) {
	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) && woodshop_shop_has_filters() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}

	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'woodshop_shop_robots' );

/**
 * Canonical URL for filtered product archives.
 *
 * @param string  $canonical Canonical URL.
 * @param WP_Post $post      Post.
 * @return string
 */
function woodshop_shop_canonical( $canonical, $post ) {
	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) && woodshop_shop_has_filters() ) {
		return woodshop_shop_archive_url();
	}

	return $canonical;
}
add_filter( 'get_canonical_url', 'woodshop_shop_canonical', 10, 2 );

/**
 * Canonical URL for archives (core only outputs it for singular content).
 */
function woodshop_archive_canonical() {
	if ( is_front_page() || is_singular() ) {
		return;
	}

	$url = '';

	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
		$url = woodshop_shop_archive_url();
	} elseif ( is_post_type_archive( 'portfolio' ) ) {
		$url = get_post_type_archive_link( 'portfolio' );
	} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
		$url = get_permalink( (int) get_option( 'page_for_posts' ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$url = get_term_link( $term );
		}
	}

	if ( $url && ! is_wp_error( $url ) ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	}
}
add_action( 'wp_head', 'woodshop_archive_canonical', 2 );

/**
 * Wrap the result count and catalog ordering in one toolbar row.
 */
function woodshop_shop_loop_bar_open() {
	echo '<div class="shop-loop-bar">';
}
add_action( 'woocommerce_before_shop_loop', 'woodshop_shop_loop_bar_open', 19 );

/**
 * Close the result/ordering toolbar.
 */
function woodshop_shop_loop_bar_close() {
	echo '</div>';
}
add_action( 'woocommerce_before_shop_loop', 'woodshop_shop_loop_bar_close', 31 );
