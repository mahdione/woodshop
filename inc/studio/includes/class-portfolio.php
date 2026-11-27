<?php
/**
 * Portfolio ("نمونه‌کار") post type.
 *
 * @package WoodshopStudio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the portfolio post type.
 */
function woodshop_studio_register_portfolio() {
	register_post_type(
		'portfolio',
		array(
			'labels'             => array(
				'name'               => __( 'نمونه‌کارها', 'woodshop-studio' ),
				'singular_name'      => __( 'نمونه‌کار', 'woodshop-studio' ),
				'menu_name'          => __( 'نمونه‌کارها', 'woodshop-studio' ),
				'add_new'            => __( 'افزودن نمونه‌کار', 'woodshop-studio' ),
				'add_new_item'       => __( 'افزودن نمونه‌کار جدید', 'woodshop-studio' ),
				'edit_item'          => __( 'ویرایش نمونه‌کار', 'woodshop-studio' ),
				'new_item'           => __( 'نمونه‌کار جدید', 'woodshop-studio' ),
				'view_item'          => __( 'مشاهده نمونه‌کار', 'woodshop-studio' ),
				'search_items'       => __( 'جستجوی نمونه‌کارها', 'woodshop-studio' ),
				'not_found'          => __( 'نمونه‌کاری پیدا نشد.', 'woodshop-studio' ),
				'not_found_in_trash' => __( 'نمونه‌کاری در سطل زباله نیست.', 'woodshop-studio' ),
				'all_items'          => __( 'همه نمونه‌کارها', 'woodshop-studio' ),
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'has_archive'        => 'portfolio',
			'rewrite'            => array( 'slug' => 'portfolio', 'with_front' => false ),
			'menu_icon'          => 'dashicons-portfolio',
			'menu_position'      => 21,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'taxonomies'         => array(),
		)
	);
}
add_action( 'init', 'woodshop_studio_register_portfolio' );

/**
 * Order the portfolio archive by the menu order field, then title.
 *
 * @param WP_Query $query Query.
 */
function woodshop_studio_portfolio_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'portfolio' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$query->set( 'posts_per_page', 12 );
	}
}
add_action( 'pre_get_posts', 'woodshop_studio_portfolio_archive_order' );

/**
 * Redirect portfolio singles to the archive until a single template exists.
 */
function woodshop_studio_portfolio_redirect() {
	if ( ! is_singular( 'portfolio' ) ) {
		return;
	}

	$archive = get_post_type_archive_link( 'portfolio' );

	if ( $archive ) {
		wp_safe_redirect( $archive, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'woodshop_studio_portfolio_redirect' );

/**
 * Admin list columns.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function woodshop_studio_portfolio_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['portfolio_thumb'] = __( 'تصویر', 'woodshop-studio' );
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['menu_order'] = __( 'ترتیب', 'woodshop-studio' );
		}
	}

	return $new;
}
add_filter( 'manage_portfolio_posts_columns', 'woodshop_studio_portfolio_columns' );

/**
 * Render portfolio columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function woodshop_studio_portfolio_column_content( $column, $post_id ) {
	if ( 'portfolio_thumb' === $column ) {
		echo get_the_post_thumbnail( $post_id, array( 64, 64 ), array( 'style' => 'width:54px;height:54px;object-fit:cover;border-radius:3px' ) );
	} elseif ( 'menu_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}
add_action( 'manage_portfolio_posts_custom_column', 'woodshop_studio_portfolio_column_content', 10, 2 );

/**
 * Make the order column sortable.
 *
 * @param array<string, string> $columns Sortable columns.
 * @return array<string, string>
 */
function woodshop_studio_portfolio_sortable_columns( $columns ) {
	$columns['menu_order'] = 'menu_order';

	return $columns;
}
add_filter( 'manage_edit-portfolio_sortable_columns', 'woodshop_studio_portfolio_sortable_columns' );

/**
 * Change the title placeholder.
 *
 * @param string  $text Placeholder.
 * @param WP_Post $post Post.
 * @return string
 */
function woodshop_studio_portfolio_title_placeholder( $text, $post ) {
	if ( $post && 'portfolio' === $post->post_type ) {
		return __( 'نام نمونه‌کار را وارد کنید', 'woodshop-studio' );
	}

	return $text;
}
add_filter( 'enter_title_here', 'woodshop_studio_portfolio_title_placeholder', 10, 2 );
