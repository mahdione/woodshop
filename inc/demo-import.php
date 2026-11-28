<?php
/**
 * One-click demo content installer.
 *
 * Bundled with the theme so a fresh installation can be populated without a
 * separate import step. Existing pages, posts, products and menus are reused.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the demo content screen under Wood Shop.
 */
function woodshop_demo_admin_menu() {
	add_submenu_page(
		'woodshop-studio',
		__( 'Demo Content', 'woodshop' ),
		__( 'Demo Content', 'woodshop' ),
		'manage_options',
		'woodshop-demo',
		'woodshop_demo_admin_screen'
	);
}
add_action( 'admin_menu', 'woodshop_demo_admin_menu', 20 );

/**
 * Render the demo content screen.
 */
function woodshop_demo_admin_screen() {
	$imported = (int) get_option( 'woodshop_demo_imported', 0 );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Wood Shop Demo Content', 'woodshop' ); ?></h1>
		<p>
			<?php esc_html_e( 'This installer creates the demo pages, posts, products, product categories, portfolio items, menus, images and the main settings. Existing content with the same slug or title is reused, so it is safe to run more than once.', 'woodshop' ); ?>
		</p>

		<?php if ( $imported ) : ?>
			<div class="notice notice-success inline">
				<p>
					<?php
					printf(
						/* translators: %s: date. */
						esc_html__( 'Demo content was last installed on %s.', 'woodshop' ),
						esc_html( wp_date( 'Y-m-d H:i', $imported ) )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<?php wp_nonce_field( 'woodshop_demo_import', 'woodshop_demo_nonce' ); ?>
			<input type="hidden" name="action" value="woodshop_demo_import">
			<?php submit_button( __( 'Install / repair demo content', 'woodshop' ), 'primary', 'submit', false ); ?>
		</form>
	</div>
	<?php
}

/**
 * Handle the manual import button.
 */
function woodshop_demo_handle_import() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'woodshop' ) );
	}

	check_admin_referer( 'woodshop_demo_import', 'woodshop_demo_nonce' );

	$log = woodshop_demo_import();

	set_transient( 'woodshop_demo_notice', $log, MINUTE_IN_SECONDS );

	if ( empty( $GLOBALS['woodshop_demo_import_complete'] ) ) {
		update_option( 'woodshop_demo_pending', time() );
		delete_option( 'woodshop_demo_imported' );
		wp_safe_redirect( admin_url( 'admin.php?page=woodshop-demo&demo_continue=1' ) );
		exit;
	}

	update_option( 'woodshop_demo_imported', time() );
	delete_option( 'woodshop_demo_pending' );

	wp_safe_redirect( admin_url( 'admin.php?page=woodshop-demo&done=1' ) );
	exit;
}
add_action( 'admin_post_woodshop_demo_import', 'woodshop_demo_handle_import' );

/**
 * Queue the demo import on the first activation of the theme.
 */
function woodshop_demo_queue_after_switch() {
	if ( apply_filters( 'woodshop_demo_auto_import', true ) ) {
		update_option( 'woodshop_demo_pending', time() );
	}
}
add_action( 'after_switch_theme', 'woodshop_demo_queue_after_switch' );

/**
 * Run a queued import on the next admin request.
 */
function woodshop_demo_maybe_run_auto_import() {
	if ( ! get_option( 'woodshop_demo_pending' ) || get_option( 'woodshop_demo_imported' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) || get_transient( 'woodshop_demo_import_lock' ) ) {
		return;
	}

	set_transient( 'woodshop_demo_import_lock', 1, 5 * MINUTE_IN_SECONDS );
	$log = woodshop_demo_import();

	delete_transient( 'woodshop_demo_import_lock' );
	set_transient( 'woodshop_demo_notice', $log, MINUTE_IN_SECONDS );

	if ( empty( $GLOBALS['woodshop_demo_import_complete'] ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=woodshop-demo&demo_continue=1' ) );
		exit;
	}

	update_option( 'woodshop_demo_imported', time() );
	delete_option( 'woodshop_demo_pending' );
}
add_action( 'admin_init', 'woodshop_demo_maybe_run_auto_import', 20 );

/**
 * Show the result of the last import.
 */
function woodshop_demo_admin_notice() {
	$log = get_transient( 'woodshop_demo_notice' );

	if ( ! is_array( $log ) ) {
		return;
	}

	delete_transient( 'woodshop_demo_notice' );

	echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Wood Shop demo content installed.', 'woodshop' ) . '</strong></p><ul style="margin-inline-start:1.2em;list-style:disc">';
	foreach ( $log as $line ) {
		echo '<li>' . esc_html( $line ) . '</li>';
	}
	echo '</ul></div>';
}
add_action( 'admin_notices', 'woodshop_demo_admin_notice' );

/**
 * Reuse an existing upload or copy a bundled theme image into the media library.
 *
 * @param string $file  File name inside theme assets/img.
 * @param string $title Attachment title and alt text.
 * @return int Attachment ID.
 */
function woodshop_demo_image( $file, $title ) {
	$file = basename( $file );

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_woodshop_demo_image',
			'meta_value'     => $file,
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	global $wpdb;

	$attached = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id ASC LIMIT 1",
			'%' . $wpdb->esc_like( $file )
		)
	);

	if ( $attached ) {
		update_post_meta( (int) $attached, '_woodshop_demo_image', $file );
		return (int) $attached;
	}

	$source = get_template_directory() . '/assets/img/' . $file;

	if ( ! file_exists( $source ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( $file );

	if ( ! $tmp || ! copy( $source, $tmp ) ) {
		return 0;
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0,
		$title
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}

	update_post_meta( $attachment_id, '_woodshop_demo_image', $file );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );

	return (int) $attachment_id;
}

/**
 * Ensure a page exists.
 *
 * @param string $slug    Page slug.
 * @param string $title   Page title.
 * @param string $content Optional content.
 * @return int Page ID.
 */
function woodshop_demo_page( $slug, $title, $content = '' ) {
	$page = get_page_by_path( $slug );

	if ( $page ) {
		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
		)
	);

	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Ensure a shortcode page has the right content without clobbering unrelated copy.
 *
 * @param int    $id        Page ID.
 * @param string $shortcode Shortcode.
 */
function woodshop_demo_shortcode_page( $id, $shortcode ) {
	if ( ! $id ) {
		return;
	}

	$content = (string) get_post_field( 'post_content', $id );

	if ( '' === trim( $content ) || false !== strpos( $content, 'wp:woocommerce' ) ) {
		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => $shortcode,
			)
		);
	}
}

/**
 * Activate or install a WordPress.org plugin.
 *
 * @param string $slug Plugin slug.
 * @param string $file Plugin basename.
 * @param array  $log  Log lines.
 * @return bool
 */
function woodshop_demo_ensure_plugin( $slug, $file, &$log ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( is_plugin_active( $file ) ) {
		return true;
	}

	if ( file_exists( WP_PLUGIN_DIR . '/' . $file ) ) {
		$result = activate_plugin( $file );

		if ( is_wp_error( $result ) ) {
			$log[] = sprintf( '%s: %s', $slug, $result->get_error_message() );
			return false;
		}

		if ( is_plugin_active( $file ) ) {
			$GLOBALS['woodshop_demo_import_complete'] = false;
		}

		return is_plugin_active( $file );
	}

	if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
		$log[] = sprintf( __( '%s needs to be installed manually because file modifications are disabled.', 'woodshop' ), $slug );
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$api = plugins_api(
		'plugin_information',
		array(
			'slug'   => $slug,
			'fields' => array( 'sections' => false ),
		)
	);

	if ( is_wp_error( $api ) || empty( $api->download_link ) ) {
		$log[] = sprintf( __( '%s could not be downloaded from WordPress.org.', 'woodshop' ), $slug );
		return false;
	}

	$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
	$result   = $upgrader->install( $api->download_link );

	if ( is_wp_error( $result ) || ! $result ) {
		$log[] = sprintf( __( '%s could not be installed automatically.', 'woodshop' ), $slug );
		return false;
	}

	$activated = activate_plugin( $file );

	if ( is_wp_error( $activated ) ) {
		$log[] = sprintf( '%s: %s', $slug, $activated->get_error_message() );
		return false;
	}

	if ( is_plugin_active( $file ) ) {
		$GLOBALS['woodshop_demo_import_complete'] = false;
	}

	return is_plugin_active( $file );
}

/**
 * Create or reuse a menu.
 *
 * @param string $name Menu name.
 * @return int Menu term ID.
 */
function woodshop_demo_menu( $name ) {
	$menu = wp_get_nav_menu_object( $name );

	if ( $menu ) {
		return (int) $menu->term_id;
	}

	$id = wp_create_nav_menu( $name );

	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Add a menu item when an equivalent item is not already present.
 *
 * @param int   $menu_id Menu ID.
 * @param array $args    Menu item args.
 * @return int Menu item ID.
 */
function woodshop_demo_menu_item( $menu_id, $args ) {
	if ( ! $menu_id ) {
		return 0;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	$expected_url = '';

	if ( ! empty( $args['menu-item-url'] ) ) {
		$expected_url = (string) $args['menu-item-url'];
	} elseif ( ! empty( $args['menu-item-object-id'] ) ) {
		$object_id = (int) $args['menu-item-object-id'];

		if ( 'product_cat' === ( $args['menu-item-object'] ?? '' ) ) {
			$term_link = get_term_link( $object_id );
			$expected_url = is_wp_error( $term_link ) ? '' : $term_link;
		} else {
			$expected_url = (string) get_permalink( $object_id );
		}
	}

	if ( $items ) {
		foreach ( $items as $item ) {
			$same_object = ! empty( $args['menu-item-object-id'] ) && (int) $item->object_id === (int) $args['menu-item-object-id'];
			$same_url    = $expected_url && untrailingslashit( $item->url ) === untrailingslashit( $expected_url );

			if ( $same_object || $same_url ) {
				return (int) $item->ID;
			}
		}
	}

	return (int) wp_update_nav_menu_item( $menu_id, 0, $args );
}

/**
 * Add a page to a menu.
 *
 * @param int    $menu_id Menu ID.
 * @param int    $page_id Page ID.
 * @param string $title   Menu title.
 */
function woodshop_demo_menu_page( $menu_id, $page_id, $title = '' ) {
	if ( ! $page_id ) {
		return;
	}

	woodshop_demo_menu_item(
		$menu_id,
		array(
			'menu-item-title'     => $title ? $title : get_the_title( $page_id ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
}

/**
 * Add a product category to a menu.
 *
 * @param int $menu_id Menu ID.
 * @param int $term_id Category term ID.
 */
function woodshop_demo_menu_term( $menu_id, $term_id ) {
	if ( ! $term_id ) {
		return;
	}

	woodshop_demo_menu_item(
		$menu_id,
		array(
			'menu-item-title'     => get_term_field( 'name', $term_id, 'product_cat' ),
			'menu-item-object'    => 'product_cat',
			'menu-item-object-id' => $term_id,
			'menu-item-type'      => 'taxonomy',
			'menu-item-status'    => 'publish',
		)
	);
}

/**
 * Add a custom link to a menu.
 *
 * @param int    $menu_id Menu ID.
 * @param string $title   Title.
 * @param string $url     URL.
 */
function woodshop_demo_menu_link( $menu_id, $title, $url ) {
	woodshop_demo_menu_item(
		$menu_id,
		array(
			'menu-item-title'  => $title,
			'menu-item-url'    => $url,
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		)
	);
}

/**
 * Create the full demo site.
 *
 * @return string[] Log lines.
 */
function woodshop_demo_import() {
	$log = array();
	$GLOBALS['woodshop_demo_import_complete'] = true;

	$woo_ready = woodshop_demo_ensure_plugin( 'woocommerce', 'woocommerce/woocommerce.php', $log );
	woodshop_demo_ensure_plugin( 'persian-calendar', 'persian-calendar/persian-calendar.php', $log );

	if ( empty( $GLOBALS['woodshop_demo_import_complete'] ) ) {
		$log[] = __( 'The required plugins were activated. Demo content will continue automatically on the next admin request.', 'woodshop' );
		return $log;
	}

	$images = array(
		'areas-team.webp',
		'bg-why.webp',
		'bg-cta.webp',
		'service-furniture.webp',
		'service-cabinetry.webp',
		'service-flooring.webp',
		'service-builtin.webp',
		'service-outdoor.webp',
		'service-renovation.webp',
		'blog-1.webp',
		'blog-2.webp',
		'blog-3.webp',
		'blog-4.webp',
		'blog-5.webp',
		'gallery-1.webp',
		'gallery-2.webp',
		'gallery-3.webp',
		'gallery-4.webp',
		'logo-dark.png',
		'logo.png',
	);

	foreach ( $images as $image ) {
		woodshop_demo_image( $image, pathinfo( $image, PATHINFO_FILENAME ) );
	}

	$home    = woodshop_demo_page( 'home', __( 'خانه', 'woodshop' ) );
	$about   = woodshop_demo_page( 'about', __( 'درباره ما', 'woodshop' ) );
	$services = woodshop_demo_page( 'services', __( 'خدمات', 'woodshop' ) );
	$contact = woodshop_demo_page( 'contact', __( 'تماس با ما', 'woodshop' ) );
	$blog    = woodshop_demo_page( 'blog', __( 'وبلاگ', 'woodshop' ) );
	$terms   = woodshop_demo_page( 'terms', __( 'قوانین و مقررات', 'woodshop' ) );
	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );

	if ( ! $privacy ) {
		$privacy = woodshop_demo_page( 'privacy-policy', __( 'حریم خصوصی', 'woodshop' ) );
		update_option( 'wp_page_for_privacy_policy', $privacy );
	}

	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
	}

	if ( $blog ) {
		update_option( 'page_for_posts', $blog );
	}

	$log[] = __( 'Pages and the front page were created.', 'woodshop' );

	$shop = 0;
	$cart = 0;
	$checkout = 0;
	$account = 0;

	if ( $woo_ready && class_exists( 'WooCommerce' ) ) {
		$shop = (int) wc_get_page_id( 'shop' );
		$cart = (int) wc_get_page_id( 'cart' );
		$checkout = (int) wc_get_page_id( 'checkout' );
		$account = (int) wc_get_page_id( 'myaccount' );

		if ( $shop <= 0 ) {
			$shop = woodshop_demo_page( 'shop', __( 'فروشگاه', 'woodshop' ) );
			update_option( 'woocommerce_shop_page_id', $shop );
		}

		if ( $cart <= 0 ) {
			$cart = woodshop_demo_page( 'cart', __( 'سبد خرید', 'woodshop' ), '[woocommerce_cart]' );
			update_option( 'woocommerce_cart_page_id', $cart );
		}

		if ( $checkout <= 0 ) {
			$checkout = woodshop_demo_page( 'checkout', __( 'تسویه‌حساب', 'woodshop' ), '[woocommerce_checkout]' );
			update_option( 'woocommerce_checkout_page_id', $checkout );
		}

		if ( $account <= 0 ) {
			$account = woodshop_demo_page( 'my-account', __( 'حساب کاربری', 'woodshop' ), '[woocommerce_my_account]' );
			update_option( 'woocommerce_myaccount_page_id', $account );
		}

		woodshop_demo_shortcode_page( $cart, '[woocommerce_cart]' );
		woodshop_demo_shortcode_page( $checkout, '[woocommerce_checkout]' );
		woodshop_demo_shortcode_page( $account, '[woocommerce_my_account]' );
	}

	$cat_ids = array();
	$category_data = array(
		'custom-furniture'   => array( 'طراحی مبلمان سفارشی', 'service-furniture.webp' ),
		'cabinetry-storage'  => array( 'کابینت و فضای ذخیره‌سازی', 'service-cabinetry.webp' ),
		'wooden-flooring'    => array( 'کف‌پوش چوبی', 'service-flooring.webp' ),
		'built-in-furniture' => array( 'مبلمان توکار', 'service-builtin.webp' ),
		'outdoor-carpentry'  => array( 'نجاری فضای باز', 'service-outdoor.webp' ),
		'home-renovation'    => array( 'نجاری بازسازی منزل', 'service-renovation.webp' ),
	);

	if ( $woo_ready && taxonomy_exists( 'product_cat' ) ) {
		foreach ( $category_data as $slug => $data ) {
			$term = term_exists( $slug, 'product_cat' );

			if ( ! $term ) {
				$term = wp_insert_term(
					$data[0],
					'product_cat',
					array(
						'slug'        => $slug,
						'description' => sprintf( 'خدمات %s با اجرای حرفه‌ای و مواد مرغوب.', $data[0] ),
					)
				);
			}

			if ( ! is_wp_error( $term ) ) {
				$term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
				$cat_ids[ $slug ] = $term_id;
				$image_id = woodshop_demo_image( $data[1], $data[0] );
				if ( $image_id ) {
					update_term_meta( $term_id, 'thumbnail_id', $image_id );
				}
			}
		}
	}

	$tag_ids = array();
	$tag_data = array( 'چوب بلوط', 'چوب گردو', 'سفارشی‌سازی', 'دست‌ساز', 'مدرن', 'فضای باز' );

	if ( $woo_ready && taxonomy_exists( 'product_tag' ) ) {
		foreach ( $tag_data as $tag_name ) {
			$term = term_exists( $tag_name, 'product_tag' );
			if ( ! $term ) {
				$term = wp_insert_term( $tag_name, 'product_tag' );
			}
			if ( ! is_wp_error( $term ) ) {
				$tag_ids[ $tag_name ] = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
			}
		}
	}

	$product_data = array(
		array( 'میز غذاخوری گردویی', 'walnut-dining-table', '48900000', 'custom-furniture', array( 'چوب گردو', 'سفارشی‌سازی' ), 'service-furniture.webp', true ),
		array( 'میز جلومبلی بلوط', 'oak-coffee-table', '12400000', 'custom-furniture', array( 'چوب بلوط', 'دست‌ساز' ), 'gallery-2.webp', true ),
		array( 'قفسه کتاب بلوط توپر', 'solid-oak-bookshelf', '18900000', 'built-in-furniture', array( 'چوب بلوط', 'سفارشی‌سازی' ), 'gallery-3.webp', true ),
		array( 'کابینت آشپزخانه سفارشی', 'custom-kitchen-cabinets', '185000000', 'cabinetry-storage', array( 'سفارشی‌سازی', 'مدرن' ), 'service-cabinetry.webp', true ),
		array( 'کف‌پوش تخته‌پهن بلوط', 'wide-plank-oak-flooring', '1850000', 'wooden-flooring', array( 'چوب بلوط', 'مدرن' ), 'service-flooring.webp', false ),
		array( 'کمد دیواری توکار', 'built-in-wardrobe', '92000000', 'built-in-furniture', array( 'سفارشی‌سازی', 'مدرن' ), 'service-builtin.webp', false ),
		array( 'نیمکت باغی چوب تیک', 'teak-garden-bench', '24500000', 'outdoor-carpentry', array( 'دست‌ساز', 'فضای باز' ), 'service-outdoor.webp', false ),
		array( 'پنل دیواری چوبی', 'timber-wall-panelling', '1250000', 'home-renovation', array( 'چوب بلوط', 'مدرن' ), 'service-renovation.webp', false ),
	);

	if ( $woo_ready && class_exists( 'WC_Product_Simple' ) ) {
		foreach ( $product_data as $index => $product_row ) {
			list( $name, $slug, $price, $cat_slug, $tags, $image_file, $featured ) = $product_row;

			if ( get_page_by_path( $slug, OBJECT, 'product' ) ) {
				continue;
			}

			$product = new WC_Product_Simple();
			$product->set_name( $name );
			$product->set_slug( $slug );
			$product->set_status( 'publish' );
			$product->set_catalog_visibility( 'visible' );
			$product->set_description( sprintf( '%s با طراحی اختصاصی، چوب مرغوب و پرداخت دقیق در کارگاه ما ساخته می‌شود. برای سفارش و انتخاب متریال با ما تماس بگیرید.', $name ) );
			$product->set_short_description( sprintf( '%s؛ ساخت سفارشی با جزئیات دقیق.', $name ) );
			$product->set_regular_price( $price );
			$product->set_price( $price );
			$product->set_manage_stock( false );
			$product->set_stock_status( 'instock' );
			$product->set_featured( (bool) $featured );
			$product->set_sku( 'WS-' . ( 100 + $index ) );

			$image_id = woodshop_demo_image( $image_file, $name );
			if ( $image_id ) {
				$product->set_image_id( $image_id );
			}

			if ( isset( $cat_ids[ $cat_slug ] ) ) {
				$product->set_category_ids( array( $cat_ids[ $cat_slug ] ) );
			}

			$product_tag_ids = array();
			foreach ( $tags as $tag_name ) {
				if ( isset( $tag_ids[ $tag_name ] ) ) {
					$product_tag_ids[] = $tag_ids[ $tag_name ];
				}
			}

			if ( $product_tag_ids ) {
				$product->set_tag_ids( $product_tag_ids );
			}

			$product->save();
		}

		$log[] = __( 'WooCommerce products and categories were created.', 'woodshop' );
	} else {
		$log[] = __( 'WooCommerce is not active, so shop products were skipped.', 'woodshop' );
	}

	$product_category = term_exists( 'woodworking', 'category' );
	if ( ! $product_category ) {
		$product_category = wp_insert_term( 'چوب‌کاری', 'category', array( 'slug' => 'woodworking' ) );
	}

	$post_category_id = is_wp_error( $product_category ) ? 0 : (int) ( is_array( $product_category ) ? $product_category['term_id'] : $product_category );

	$post_data = array(
		array( 'تازه‌ترین ترندهای نجاری برای خانه‌های مدرن', 'top-carpentry-trends-for-stunning-modern-homes', 'blog-1.webp', 'نجاری مدرن به سمت متریال‌های گرم، جزئیات آرام و فضای ذخیره‌سازی توکار حرکت می‌کند.' ),
		array( 'اشتباهات رایج در چوب‌کاری و راه‌های جلوگیری از آن‌ها', 'common-woodworking-mistakes-and-how-to-avoid-them', 'blog-2.webp', 'بیشتر شکست‌ها در چوب‌کاری سفارشی از انتخاب نامناسب چوب و رطوبت شروع می‌شود.' ),
		array( 'چگونه لمس‌های چوبی را در طراحی داخلی به کار ببریم', 'how-to-incorporate-wooden-accents-in-interior-design', 'blog-3.webp', 'ترکیب درست چوب با نور، پارچه و رنگ، فضای داخلی را گرم و ماندگار می‌کند.' ),
		array( 'اهمیت دقت در نجاری سفارشی', 'the-importance-of-precision-in-custom-carpentry', 'blog-4.webp', 'کیفیت نهایی هر قطعه چوبی، نتیجه اندازه‌گیری دقیق و پرداخت صبورانه است.' ),
		array( 'چگونه چوب سخت باکیفیت را تشخیص دهیم', 'how-to-identify-high-quality-hardwood-materials', 'blog-5.webp', 'رگه‌های طبیعی، وزن و بوی چوب سخت، نشانه‌هایی برای تشخیص کیفیت آن هستند.' ),
	);

	foreach ( $post_data as $post_row ) {
		list( $title, $slug, $image_file, $excerpt ) = $post_row;

		if ( get_page_by_path( $slug, OBJECT, 'post' ) ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_excerpt' => $excerpt,
				'post_content' => $excerpt . "\n\n" . 'در کارگاه ما هر پروژه با گفت‌وگو، اندازه‌گیری دقیق، انتخاب متریال مناسب و اجرای مرحله‌به‌مرحله پیش می‌رود. اگر درباره این موضوع سؤالی دارید یا برای پروژه خود به مشاوره نیاز دارید، با تیم ما در تماس باشید.',
				'post_category' => $post_category_id ? array( $post_category_id ) : array(),
			)
		);

		if ( ! is_wp_error( $post_id ) ) {
			$image_id = woodshop_demo_image( $image_file, $title );
			if ( $image_id ) {
				set_post_thumbnail( $post_id, $image_id );
			}
		}
	}

	if ( post_type_exists( 'portfolio' ) ) {
		$portfolio_data = array(
			array( 'میز ناهارخوری بلوط سفارشی', 'service-furniture.webp', 'میز یکپارچه بلوط با رگه‌های طبیعی و پرداخت مات؛ ساخته‌شده برای یک خانه خانوادگی در اصفهان.', 0 ),
			array( 'کابینت آشپزخانه مدرن', 'service-cabinetry.webp', 'کابینت‌های بدون دستگیره با چوب گردو و نورپردازی مخفی، در ترکیب با فضای باز آشپزخانه.', 1 ),
			array( 'کف‌پوش چوبی پذیرایی', 'service-flooring.webp', 'اجرای کف‌پوش چوب سخت با الگوی ماهی‌چوبی و پوشش مقاوم در برابر رفت‌وآمد روزانه.', 2 ),
			array( 'دیوار تلویزیون توکار', 'service-renovation.webp', 'دیوار توکار با قفسه‌های باز و کشوهای مخفی؛ ترکیبی از چوب و فلز مات.', 3 ),
			array( 'تراس و آلاچیق چوبی', 'service-outdoor.webp', 'بازسازی تراس با چوب مقاوم به رطوبت و ساخت آلاچیق برای دورهمی‌های خانوادگی.', 4 ),
			array( 'بازسازی اتاق کار خانگی', 'gallery-2.webp', 'میز کار، کتابخانه و پنل آکوستیک چوبی برای یک فضای کار آرام و منظم.', 5 ),
		);

		foreach ( $portfolio_data as $portfolio_row ) {
			list( $title, $image_file, $excerpt, $order ) = $portfolio_row;

			$existing = get_posts(
				array(
					'post_type'      => 'portfolio',
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'title'          => $title,
					'fields'         => 'ids',
				)
			);

			if ( $existing ) {
				continue;
			}

			$portfolio_id = wp_insert_post(
				array(
					'post_type'    => 'portfolio',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_excerpt' => $excerpt,
					'post_content' => $excerpt,
					'menu_order'   => $order,
				)
			);

			if ( ! is_wp_error( $portfolio_id ) ) {
				$image_id = woodshop_demo_image( $image_file, $title );
				if ( $image_id ) {
					set_post_thumbnail( $portfolio_id, $image_id );
				}
			}
		}
	}

	$log[] = __( 'Blog posts and portfolio items were created.', 'woodshop' );

	$primary        = woodshop_demo_menu( __( 'منوی اصلی', 'woodshop' ) );
	$footer_services = woodshop_demo_menu( __( 'خدمات فوتر', 'woodshop' ) );
	$footer_legal    = woodshop_demo_menu( __( 'پیوندهای حقوقی', 'woodshop' ) );

	woodshop_demo_menu_page( $primary, $home, __( 'خانه', 'woodshop' ) );
	woodshop_demo_menu_page( $primary, $shop, __( 'فروشگاه', 'woodshop' ) );
	woodshop_demo_menu_page( $primary, $services, __( 'خدمات', 'woodshop' ) );

	$portfolio_url = get_post_type_archive_link( 'portfolio' );
	if ( $portfolio_url ) {
		woodshop_demo_menu_link( $primary, __( 'نمونه‌کارها', 'woodshop' ), $portfolio_url );
	}

	woodshop_demo_menu_page( $primary, $about, __( 'درباره ما', 'woodshop' ) );
	woodshop_demo_menu_page( $primary, $blog, __( 'وبلاگ', 'woodshop' ) );
	woodshop_demo_menu_page( $primary, $contact, __( 'تماس با ما', 'woodshop' ) );

	foreach ( $cat_ids as $cat_id ) {
		woodshop_demo_menu_term( $footer_services, $cat_id );
	}

	woodshop_demo_menu_page( $footer_legal, $terms );
	woodshop_demo_menu_page( $footer_legal, $privacy );

	$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $primary;
	$locations['footer_services'] = $footer_services;
	$locations['footer_legal'] = $footer_legal;
	set_theme_mod( 'nav_menu_locations', $locations );

	$logo_id = woodshop_demo_image( 'logo.png', get_bloginfo( 'name' ) );
	if ( $logo_id ) {
		set_theme_mod( 'custom_logo', $logo_id );
	}

	$studio = (array) get_option( 'woodshop_studio', array() );
	$studio['header']['menu_id'] = $primary;
	$studio['footer']['services_menu'] = $footer_services;
	$studio['footer']['legal_menu'] = $footer_legal;
	$studio['footer']['logo'] = woodshop_demo_image( 'logo-dark.png', get_bloginfo( 'name' ) );
	$studio['services']['source'] = 'categories';
	$studio['services']['category_ids'] = array_values( $cat_ids );

	if ( empty( $studio['brand'] ) ) {
		$studio['brand'] = array(
			'phone'       => '09134567890',
			'phone_url'   => 'tel:09134567890',
			'email'       => 'info@example.com',
			'address'     => 'اصفهان، خیابان اول، کوچه دوم',
			'hours'       => 'دوشنبه تا جمعه: ۹ صبح تا ۸ شب',
			'quote_label' => 'دریافت استعلام رایگان',
			'quote_url'   => '/#request',
		);
	}

	update_option( 'woodshop_studio', $studio );

	$log[] = __( 'Menus, logo and Wood Shop Studio settings were connected.', 'woodshop' );

	if ( $woo_ready && class_exists( 'WooCommerce' ) ) {
		update_option( 'woocommerce_currency', 'IRT' );
		update_option( 'woocommerce_currency_pos', 'right_space' );
		update_option( 'woocommerce_price_num_decimals', 0 );
		update_option( 'woocommerce_price_thousand_sep', '٬' );
		update_option( 'woocommerce_price_decimal_sep', '.' );
		update_option( 'woocommerce_default_country', 'IR' );
		update_option( 'woocommerce_ship_to_countries', 'billing' );
		update_option( 'woocommerce_calc_taxes', 'no' );
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );

		$cod = array_merge(
			array(
				'enabled'      => 'yes',
				'title'        => 'پرداخت در محل',
				'description'  => 'پرداخت هزینه سفارش هنگام تحویل یا هماهنگی نهایی با کارگاه.',
				'instructions' => 'پرداخت هنگام تحویل انجام می‌شود.',
			),
			(array) get_option( 'woocommerce_cod_settings', array() )
		);
		$cod['enabled'] = 'yes';
		update_option( 'woocommerce_cod_settings', $cod );
	}

	$site_name = get_option( 'blogname' );
	if ( in_array( $site_name, array( 'My WordPress', 'WordPress', 'My Blog' ), true ) ) {
		update_option( 'blogname', 'Nail & Knot' );
	}

	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	flush_rewrite_rules( false );

	$log[] = __( 'Demo content installation finished.', 'woodshop' );

	return $log;
}
