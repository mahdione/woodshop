<?php
/**
 * Admin settings screen.
 *
 * @package WoodshopStudio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds and saves the Wood Shop Studio settings screen.
 */
class Woodshop_Studio_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var Woodshop_Studio_Admin|null
	 */
	private static $instance = null;

	/**
	 * Cached schema.
	 *
	 * @var array<string, mixed>|null
	 */
	private $schema = null;

	/**
	 * Get the instance.
	 *
	 * @return Woodshop_Studio_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Hook up.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_post_woodshop_studio_save', array( $this, 'save' ) );
		add_action( 'admin_post_woodshop_studio_reset', array( $this, 'reset' ) );
	}

	/**
	 * Register the admin page.
	 */
	public function menu() {
		add_menu_page(
			__( 'Wood Shop Studio', 'woodshop-studio' ),
			__( 'Wood Shop', 'woodshop-studio' ),
			'manage_options',
			'woodshop-studio',
			array( $this, 'render_page' ),
			'dashicons-hammer',
			58
		);
	}

	/**
	 * Load admin assets only on our screen.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function assets( $hook ) {
		if ( 'toplevel_page_woodshop-studio' !== $hook ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'woodshop-studio-admin',
			WOODSHOP_STUDIO_URL . 'assets/admin.css',
			array(),
			WOODSHOP_STUDIO_VERSION
		);

		wp_enqueue_script(
			'woodshop-studio-admin',
			WOODSHOP_STUDIO_URL . 'assets/admin.js',
			array( 'jquery' ),
			WOODSHOP_STUDIO_VERSION,
			true
		);

		wp_localize_script(
			'woodshop-studio-admin',
			'wssAdmin',
			array(
				'selectImage' => __( 'Select image', 'woodshop-studio' ),
				'useImage'    => __( 'Use this image', 'woodshop-studio' ),
				'remove'      => __( 'Remove', 'woodshop-studio' ),
				'confirmReset'=> __( 'Reset every homepage setting to the demo content? This cannot be undone.', 'woodshop-studio' ),
			)
		);
	}

	/**
	 * Settings schema.
	 *
	 * @return array<string, array{label: string, sections: array<int, array<string, mixed>>}>
	 */
	public function schema() {
		if ( null !== $this->schema ) {
			return $this->schema;
		}

		$menus = array( 0 => __( '— Theme default —', 'woodshop-studio' ) );
		foreach ( wp_get_nav_menus() as $menu ) {
			$menus[ $menu->term_id ] = $menu->name;
		}

		$product_cats = array( 0 => __( '— All categories —', 'woodshop-studio' ) );
		if ( taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
				)
			);

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$product_cats[ $term->term_id ] = $term->name;
				}
			}
		}

		$icons = array(
			'award'      => __( 'Award', 'woodshop-studio' ),
			'tools'      => __( 'Tools', 'woodshop-studio' ),
			'medal'      => __( 'Medal', 'woodshop-studio' ),
			'recycle'    => __( 'Recycle', 'woodshop-studio' ),
			'check'      => __( 'Check circle', 'woodshop-studio' ),
			'layers'     => __( 'Layers', 'woodshop-studio' ),
			'swatchbook' => __( 'Swatch book', 'woodshop-studio' ),
			'home'       => __( 'Home', 'woodshop-studio' ),
			'building'   => __( 'Building', 'woodshop-studio' ),
			'medkit'     => __( 'Medical kit', 'woodshop-studio' ),
			'school'     => __( 'School', 'woodshop-studio' ),
			'hotel'      => __( 'Hotel', 'woodshop-studio' ),
			'basket'     => __( 'Basket', 'woodshop-studio' ),
			'cart'       => __( 'Cart', 'woodshop-studio' ),
			'star'       => __( 'Star', 'woodshop-studio' ),
			'phone'      => __( 'Phone', 'woodshop-studio' ),
			'email'      => __( 'Envelope', 'woodshop-studio' ),
			'map'        => __( 'Map', 'woodshop-studio' ),
			'pin'        => __( 'Map pin', 'woodshop-studio' ),
		);

		$socials = array(
			'facebook'  => 'Facebook',
			'x'         => 'X (Twitter)',
			'linkedin'  => 'LinkedIn',
			'instagram' => 'Instagram',
			'youtube'   => 'YouTube',
		);

		$enabled = array(
			'key'   => 'enabled',
			'type'  => 'toggle',
			'label' => __( 'Show this section on the homepage', 'woodshop-studio' ),
			'desc'  => __( 'Turn this off to hide the section without deleting its content.', 'woodshop-studio' ),
		);

		$this->schema = array(
			'brand'        => array(
				'label'    => __( 'Brand & Header', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Contact details', 'woodshop-studio' ),
						'desc'   => __( 'Used in the header, footer, contact blocks and structured data.', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'brand.phone', 'type' => 'text', 'label' => __( 'Phone number', 'woodshop-studio' ) ),
							array( 'key' => 'brand.phone_url', 'type' => 'url', 'label' => __( 'Phone link', 'woodshop-studio' ), 'desc' => 'tel:+11234567890' ),
							array( 'key' => 'brand.email', 'type' => 'email', 'label' => __( 'Email address', 'woodshop-studio' ) ),
							array( 'key' => 'brand.address', 'type' => 'text', 'label' => __( 'Address', 'woodshop-studio' ) ),
							array( 'key' => 'brand.hours', 'type' => 'text', 'label' => __( 'Opening hours', 'woodshop-studio' ) ),
							array( 'key' => 'brand.quote_label', 'type' => 'text', 'label' => __( 'Quote button label', 'woodshop-studio' ) ),
							array( 'key' => 'brand.quote_url', 'type' => 'text', 'label' => __( 'Quote button link', 'woodshop-studio' ) ),
						),
					),
					array(
						'title'  => __( 'Header navigation', 'woodshop-studio' ),
						'desc'   => __( 'Menus are built in Appearance → Menus. Choose which menu the header uses here.', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'header.menu_id', 'type' => 'select', 'label' => __( 'Header menu', 'woodshop-studio' ), 'options' => $menus ),
							array( 'key' => 'header.show_cart', 'type' => 'toggle', 'label' => __( 'Show WooCommerce cart icon', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'styles'       => array(
				'label'    => __( 'Styles', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Site colors', 'woodshop-studio' ),
						'desc'   => __( 'Pick a color or type a hex value such as #996a38. These colors feed the theme CSS variables and apply across the site.', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'styles.bg', 'type' => 'color', 'label' => __( 'Page background', 'woodshop-studio' ), 'default' => '#eae5e0' ),
							array( 'key' => 'styles.bg_alt', 'type' => 'color', 'label' => __( 'Soft background', 'woodshop-studio' ), 'default' => '#f4f0eb' ),
							array( 'key' => 'styles.white', 'type' => 'color', 'label' => __( 'Cards and surfaces', 'woodshop-studio' ), 'default' => '#ffffff' ),
							array( 'key' => 'styles.dark', 'type' => 'color', 'label' => __( 'Dark sections and footer', 'woodshop-studio' ), 'default' => '#140d09' ),
							array( 'key' => 'styles.ink', 'type' => 'color', 'label' => __( 'Headings and strong text', 'woodshop-studio' ), 'default' => '#1b110e' ),
							array( 'key' => 'styles.body', 'type' => 'color', 'label' => __( 'Body text', 'woodshop-studio' ), 'default' => '#4a4541' ),
							array( 'key' => 'styles.muted', 'type' => 'color', 'label' => __( 'Muted text', 'woodshop-studio' ), 'default' => '#847b73' ),
							array( 'key' => 'styles.brown', 'type' => 'color', 'label' => __( 'Primary brand color', 'woodshop-studio' ), 'default' => '#75604b' ),
							array( 'key' => 'styles.brown_deep', 'type' => 'color', 'label' => __( 'Primary dark tone', 'woodshop-studio' ), 'default' => '#6e5a45' ),
							array( 'key' => 'styles.accent', 'type' => 'color', 'label' => __( 'Accent and hover color', 'woodshop-studio' ), 'default' => '#996a38' ),
							array( 'key' => 'styles.sand', 'type' => 'color', 'label' => __( 'Light accent / image placeholder', 'woodshop-studio' ), 'default' => '#dfd4c9' ),
							array( 'key' => 'styles.sand_2', 'type' => 'color', 'label' => __( 'Muted accent / borders', 'woodshop-studio' ), 'default' => '#c8b5a5' ),
						),
					),
					array(
						'title'  => __( 'Status colors', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'styles.success', 'type' => 'color', 'label' => __( 'Success', 'woodshop-studio' ), 'default' => '#5c6d4e' ),
							array( 'key' => 'styles.error', 'type' => 'color', 'label' => __( 'Error and required', 'woodshop-studio' ), 'default' => '#a3453b' ),
						),
					),
				),
			),
			'hero'         => array(
				'label'    => __( 'Hero', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Slides', 'woodshop-studio' ),
						'desc'   => __( 'The first slide is preloaded for a fast Largest Contentful Paint. Use 1600–2000px wide images.', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array(
								'key'       => 'hero.slides',
								'type'      => 'repeater',
								'label'     => __( 'Hero slides', 'woodshop-studio' ),
								'add_label' => __( 'Add slide', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'image', 'type' => 'media', 'label' => __( 'Image', 'woodshop-studio' ) ),
									array( 'key' => 'alt', 'type' => 'text', 'label' => __( 'Alt text', 'woodshop-studio' ) ),
								),
							),
						),
					),
					array(
						'title'  => __( 'Headline and call to action', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'hero.title_before', 'type' => 'text', 'label' => __( 'Headline — before highlight', 'woodshop-studio' ) ),
							array( 'key' => 'hero.title_highlight', 'type' => 'text', 'label' => __( 'Highlighted word', 'woodshop-studio' ) ),
							array( 'key' => 'hero.title_after', 'type' => 'text', 'label' => __( 'Headline — after highlight', 'woodshop-studio' ) ),
							array( 'key' => 'hero.subtitle', 'type' => 'textarea', 'label' => __( 'Subtitle', 'woodshop-studio' ) ),
							array( 'key' => 'hero.button_label', 'type' => 'text', 'label' => __( 'Button label', 'woodshop-studio' ) ),
							array( 'key' => 'hero.button_url', 'type' => 'text', 'label' => __( 'Button link', 'woodshop-studio' ) ),
						),
					),
					array(
						'title'  => __( 'Reviews badge', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'hero.reviews_value', 'type' => 'text', 'label' => __( 'Review count', 'woodshop-studio' ) ),
							array( 'key' => 'hero.reviews_suffix', 'type' => 'text', 'label' => __( 'Suffix', 'woodshop-studio' ), 'desc' => '+' ),
							array( 'key' => 'hero.reviews_label', 'type' => 'text', 'label' => __( 'Review label', 'woodshop-studio' ) ),
							array(
								'key'       => 'hero.avatars',
								'type'      => 'repeater',
								'label'     => __( 'Avatar images', 'woodshop-studio' ),
								'add_label' => __( 'Add avatar', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'image', 'type' => 'media', 'label' => __( 'Image', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
			'features'     => array(
				'label'    => __( 'Features', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Feature strip', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array(
								'key'       => 'features.items',
								'type'      => 'repeater',
								'label'     => __( 'Features', 'woodshop-studio' ),
								'add_label' => __( 'Add feature', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'icon', 'type' => 'select', 'label' => __( 'Icon', 'woodshop-studio' ), 'options' => $icons ),
									array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
									array( 'key' => 'text', 'type' => 'text', 'label' => __( 'Description', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
			'about'        => array(
				'label'    => __( 'About', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Why choose us', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'about.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'about.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'about.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
							array(
								'key'       => 'about.points',
								'type'      => 'repeater',
								'label'     => __( 'Bullet points', 'woodshop-studio' ),
								'add_label' => __( 'Add point', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'icon', 'type' => 'select', 'label' => __( 'Icon', 'woodshop-studio' ), 'options' => $icons ),
									array( 'key' => 'text', 'type' => 'text', 'label' => __( 'Text', 'woodshop-studio' ) ),
								),
							),
							array( 'key' => 'about.image', 'type' => 'media', 'label' => __( 'Stat card background', 'woodshop-studio' ) ),
							array( 'key' => 'about.stat_value', 'type' => 'text', 'label' => __( 'Stat number', 'woodshop-studio' ) ),
							array( 'key' => 'about.stat_suffix', 'type' => 'text', 'label' => __( 'Stat suffix', 'woodshop-studio' ) ),
							array( 'key' => 'about.stat_label', 'type' => 'text', 'label' => __( 'Stat label', 'woodshop-studio' ) ),
							array( 'key' => 'about.button_label', 'type' => 'text', 'label' => __( 'Button label', 'woodshop-studio' ) ),
							array( 'key' => 'about.button_url', 'type' => 'text', 'label' => __( 'Button link', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'services'     => array(
				'label'    => __( 'Services', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Services heading', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'services.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'services.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'services.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
						),
					),
					array(
						'title'  => __( 'Cards source', 'woodshop-studio' ),
						'desc'   => __( 'Pull the cards from WooCommerce product categories, or write them manually. Categories link straight to their shop archive.', 'woodshop-studio' ),
						'fields' => array(
							array(
								'key'     => 'services.source',
								'type'    => 'select',
								'label'   => __( 'Source', 'woodshop-studio' ),
								'options' => array(
									'categories' => __( 'WooCommerce product categories', 'woodshop-studio' ),
									'manual'     => __( 'Manual cards', 'woodshop-studio' ),
								),
							),
							array(
								'key'     => 'services.category_ids',
								'type'    => 'categories',
								'label'   => __( 'Categories to show', 'woodshop-studio' ),
								'options' => $product_cats,
								'desc'    => __( 'Leave empty to show the first six top-level product categories.', 'woodshop-studio' ),
							),
						),
					),
					array(
						'title'  => __( 'Manual cards', 'woodshop-studio' ),
						'desc'   => __( 'Only used when the source is set to manual cards.', 'woodshop-studio' ),
						'fields' => array(
							array(
								'key'       => 'services.items',
								'type'      => 'repeater',
								'label'     => __( 'Cards', 'woodshop-studio' ),
								'add_label' => __( 'Add card', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'image', 'type' => 'media', 'label' => __( 'Image', 'woodshop-studio' ) ),
									array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
									array( 'key' => 'text', 'type' => 'text', 'label' => __( 'Description', 'woodshop-studio' ) ),
									array( 'key' => 'url', 'type' => 'text', 'label' => __( 'Link', 'woodshop-studio' ) ),
								),
							),
							array( 'key' => 'services.button_label', 'type' => 'text', 'label' => __( 'Button label', 'woodshop-studio' ) ),
							array( 'key' => 'services.button_url', 'type' => 'text', 'label' => __( 'Button link', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'products'     => array(
				'label'    => __( 'Products', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'WooCommerce product grid', 'woodshop-studio' ),
						'desc'   => __( 'Products are managed in WooCommerce → Products. This section only controls what appears on the homepage.', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'products.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'products.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'products.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
							array( 'key' => 'products.limit', 'type' => 'number', 'label' => __( 'Number of products', 'woodshop-studio' ), 'min' => 1, 'max' => 12 ),
							array( 'key' => 'products.featured_only', 'type' => 'toggle', 'label' => __( 'Only featured products', 'woodshop-studio' ) ),
							array( 'key' => 'products.category_id', 'type' => 'select', 'label' => __( 'Product category', 'woodshop-studio' ), 'options' => $product_cats ),
							array( 'key' => 'products.button_label', 'type' => 'text', 'label' => __( 'Button label', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'showcase'     => array(
				'label'    => __( 'Showcase', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Bento cards', 'woodshop-studio' ),
						'desc'   => __( 'Large cards span two thirds; small cards span one third. Split cards show text beside the image.', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array(
								'key'       => 'showcase.items',
								'type'      => 'repeater',
								'label'     => __( 'Cards', 'woodshop-studio' ),
								'add_label' => __( 'Add card', 'woodshop-studio' ),
								'subfields' => array(
									array(
										'key'     => 'layout',
										'type'    => 'select',
										'label'   => __( 'Layout', 'woodshop-studio' ),
										'options' => array(
											'split' => __( 'Text + image', 'woodshop-studio' ),
											'cover' => __( 'Full image', 'woodshop-studio' ),
										),
									),
									array(
										'key'     => 'size',
										'type'    => 'select',
										'label'   => __( 'Width', 'woodshop-studio' ),
										'options' => array(
											'large' => __( 'Large', 'woodshop-studio' ),
											'small' => __( 'Small', 'woodshop-studio' ),
										),
									),
									array( 'key' => 'image', 'type' => 'media', 'label' => __( 'Image', 'woodshop-studio' ) ),
									array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
									array( 'key' => 'text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
									array( 'key' => 'url', 'type' => 'text', 'label' => __( 'Link', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
			'guarantee'    => array(
				'label'    => __( 'Guarantee', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Guarantee band', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'guarantee.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'guarantee.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'guarantee.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
							array(
								'key'       => 'guarantee.points',
								'type'      => 'repeater',
								'label'     => __( 'Checklist', 'woodshop-studio' ),
								'add_label' => __( 'Add item', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'text', 'type' => 'text', 'label' => __( 'Text', 'woodshop-studio' ) ),
								),
							),
							array( 'key' => 'guarantee.image', 'type' => 'media', 'label' => __( 'Badge image', 'woodshop-studio' ) ),
							array( 'key' => 'guarantee.background', 'type' => 'media', 'label' => __( 'Background image', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'gallery'      => array(
				'label'    => __( 'Gallery', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Project gallery', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'gallery.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'gallery.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'gallery.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
							array(
								'key'       => 'gallery.items',
								'type'      => 'repeater',
								'label'     => __( 'Projects', 'woodshop-studio' ),
								'add_label' => __( 'Add project', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'image', 'type' => 'media', 'label' => __( 'Image', 'woodshop-studio' ) ),
									array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
									array( 'key' => 'caption', 'type' => 'text', 'label' => __( 'Caption', 'woodshop-studio' ) ),
									array( 'key' => 'url', 'type' => 'text', 'label' => __( 'Link', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
			'areas'        => array(
				'label'    => __( 'Areas', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Service areas', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'areas.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'areas.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'areas.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
							array(
								'key'       => 'areas.items',
								'type'      => 'repeater',
								'label'     => __( 'Areas', 'woodshop-studio' ),
								'add_label' => __( 'Add area', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'name', 'type' => 'text', 'label' => __( 'Name', 'woodshop-studio' ) ),
									array( 'key' => 'url', 'type' => 'text', 'label' => __( 'Link', 'woodshop-studio' ) ),
								),
							),
							array( 'key' => 'areas.image', 'type' => 'media', 'label' => __( 'Image', 'woodshop-studio' ) ),
							array( 'key' => 'areas.button_label', 'type' => 'text', 'label' => __( 'Button label', 'woodshop-studio' ) ),
							array( 'key' => 'areas.button_url', 'type' => 'text', 'label' => __( 'Button link', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'sectors'      => array(
				'label'    => __( 'Sectors', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Service sectors', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'sectors.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'sectors.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'sectors.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
							array( 'key' => 'sectors.background', 'type' => 'media', 'label' => __( 'Background image', 'woodshop-studio' ) ),
							array(
								'key'       => 'sectors.items',
								'type'      => 'repeater',
								'label'     => __( 'Sectors', 'woodshop-studio' ),
								'add_label' => __( 'Add sector', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'icon', 'type' => 'select', 'label' => __( 'Icon', 'woodshop-studio' ), 'options' => $icons ),
									array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
									array( 'key' => 'text', 'type' => 'text', 'label' => __( 'Description', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
			'logos'        => array(
				'label'    => __( 'Logos', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Partner logos', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array(
								'key'       => 'logos.items',
								'type'      => 'repeater',
								'label'     => __( 'Logos', 'woodshop-studio' ),
								'add_label' => __( 'Add logo', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'image', 'type' => 'media', 'label' => __( 'Image', 'woodshop-studio' ) ),
									array( 'key' => 'alt', 'type' => 'text', 'label' => __( 'Brand name', 'woodshop-studio' ) ),
									array( 'key' => 'url', 'type' => 'text', 'label' => __( 'Link (optional)', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
			'financing'    => array(
				'label'    => __( 'Financing', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Financing block', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'financing.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'financing.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'financing.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
							array(
								'key'       => 'financing.points',
								'type'      => 'repeater',
								'label'     => __( 'Checklist', 'woodshop-studio' ),
								'add_label' => __( 'Add item', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'text', 'type' => 'text', 'label' => __( 'Text', 'woodshop-studio' ) ),
								),
							),
							array( 'key' => 'financing.image', 'type' => 'media', 'label' => __( 'Image', 'woodshop-studio' ) ),
							array( 'key' => 'financing.button_label', 'type' => 'text', 'label' => __( 'Button label', 'woodshop-studio' ) ),
							array( 'key' => 'financing.button_url', 'type' => 'text', 'label' => __( 'Button link', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'testimonials' => array(
				'label'    => __( 'Testimonials', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Client reviews', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'testimonials.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'testimonials.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'testimonials.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
							array( 'key' => 'testimonials.background', 'type' => 'media', 'label' => __( 'Background image', 'woodshop-studio' ) ),
							array(
								'key'       => 'testimonials.items',
								'type'      => 'repeater',
								'label'     => __( 'Reviews', 'woodshop-studio' ),
								'add_label' => __( 'Add review', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'text', 'type' => 'textarea', 'label' => __( 'Quote', 'woodshop-studio' ) ),
									array( 'key' => 'name', 'type' => 'text', 'label' => __( 'Name', 'woodshop-studio' ) ),
									array( 'key' => 'meta', 'type' => 'text', 'label' => __( 'Location / role', 'woodshop-studio' ) ),
									array( 'key' => 'image', 'type' => 'media', 'label' => __( 'Avatar', 'woodshop-studio' ) ),
									array( 'key' => 'rating', 'type' => 'number', 'label' => __( 'Stars (1–5)', 'woodshop-studio' ), 'min' => 1, 'max' => 5 ),
								),
							),
						),
					),
				),
			),
			'blog'         => array(
				'label'    => __( 'Blog', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Latest posts', 'woodshop-studio' ),
						'desc'   => __( 'Posts are pulled automatically from the WordPress blog.', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'blog.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'blog.title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
							array( 'key' => 'blog.text', 'type' => 'textarea', 'label' => __( 'Description', 'woodshop-studio' ) ),
							array( 'key' => 'blog.count', 'type' => 'number', 'label' => __( 'Number of posts', 'woodshop-studio' ), 'min' => 2, 'max' => 10 ),
							array( 'key' => 'blog.button_label', 'type' => 'text', 'label' => __( 'Button label', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'cta'          => array(
				'label'    => __( 'Request Form', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Request section', 'woodshop-studio' ),
						'fields' => array(
							$enabled,
							array( 'key' => 'cta.title_before', 'type' => 'text', 'label' => __( 'Title — before highlight', 'woodshop-studio' ) ),
							array( 'key' => 'cta.title_highlight', 'type' => 'text', 'label' => __( 'Highlighted phrase', 'woodshop-studio' ) ),
							array( 'key' => 'cta.background', 'type' => 'media', 'label' => __( 'Background image', 'woodshop-studio' ) ),
							array(
								'key'       => 'cta.points',
								'type'      => 'repeater',
								'label'     => __( 'Checklist', 'woodshop-studio' ),
								'add_label' => __( 'Add item', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'text', 'type' => 'text', 'label' => __( 'Text', 'woodshop-studio' ) ),
								),
							),
						),
					),
					array(
						'title'  => __( 'Form behaviour', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'cta.form_title', 'type' => 'text', 'label' => __( 'Form title', 'woodshop-studio' ) ),
							array( 'key' => 'cta.button', 'type' => 'text', 'label' => __( 'Submit button label', 'woodshop-studio' ) ),
							array( 'key' => 'cta.recipient', 'type' => 'email', 'label' => __( 'Send requests to', 'woodshop-studio' ), 'desc' => __( 'Leave empty to use the site administrator email.', 'woodshop-studio' ) ),
							array( 'key' => 'cta.success', 'type' => 'textarea', 'label' => __( 'Success message', 'woodshop-studio' ) ),
							array( 'key' => 'cta.error', 'type' => 'textarea', 'label' => __( 'Error message', 'woodshop-studio' ) ),
						),
					),
					array(
						'title'  => __( 'Form fields', 'woodshop-studio' ),
						'desc'   => __( 'Every form on the site uses the same three fields. Edit their labels and placeholders here.', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'form.name_label', 'type' => 'text', 'label' => __( 'Name label', 'woodshop-studio' ) ),
							array( 'key' => 'form.name_placeholder', 'type' => 'text', 'label' => __( 'Name placeholder', 'woodshop-studio' ) ),
							array( 'key' => 'form.phone_label', 'type' => 'text', 'label' => __( 'Phone label', 'woodshop-studio' ) ),
							array( 'key' => 'form.phone_placeholder', 'type' => 'text', 'label' => __( 'Phone placeholder', 'woodshop-studio' ) ),
							array( 'key' => 'form.message_label', 'type' => 'text', 'label' => __( 'Message label', 'woodshop-studio' ) ),
							array( 'key' => 'form.message_placeholder', 'type' => 'text', 'label' => __( 'Message placeholder', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'page_about'   => array(
				'label'    => __( 'About Page', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'About introduction', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'page_about.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'page_about.title', 'type' => 'text', 'label' => __( 'Page title', 'woodshop-studio' ) ),
							array( 'key' => 'page_about.intro', 'type' => 'textarea', 'label' => __( 'Introduction', 'woodshop-studio' ) ),
							array( 'key' => 'page_about.image', 'type' => 'media', 'label' => __( 'Hero image', 'woodshop-studio' ) ),
						),
					),
					array(
						'title'  => __( 'Story, mission and values', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'page_about.story_eyebrow', 'type' => 'text', 'label' => __( 'Story eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'page_about.story_title', 'type' => 'text', 'label' => __( 'Story title', 'woodshop-studio' ) ),
							array( 'key' => 'page_about.story_text', 'type' => 'textarea', 'label' => __( 'Story text', 'woodshop-studio' ) ),
							array( 'key' => 'page_about.story_image', 'type' => 'media', 'label' => __( 'Story image', 'woodshop-studio' ) ),
							array(
								'key'       => 'page_about.stats',
								'type'      => 'repeater',
								'label'     => __( 'Stats', 'woodshop-studio' ),
								'add_label' => __( 'Add stat', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'value', 'type' => 'text', 'label' => __( 'Value', 'woodshop-studio' ) ),
									array( 'key' => 'suffix', 'type' => 'text', 'label' => __( 'Suffix', 'woodshop-studio' ) ),
									array( 'key' => 'label', 'type' => 'text', 'label' => __( 'Label', 'woodshop-studio' ) ),
								),
							),
							array(
								'key'       => 'page_about.values',
								'type'      => 'repeater',
								'label'     => __( 'Mission / vision / values', 'woodshop-studio' ),
								'add_label' => __( 'Add item', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'icon', 'type' => 'select', 'label' => __( 'Icon', 'woodshop-studio' ), 'options' => $icons ),
									array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
									array( 'key' => 'text', 'type' => 'textarea', 'label' => __( 'Text', 'woodshop-studio' ) ),
								),
							),
						),
					),
					array(
						'title'  => __( 'Team', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'page_about.team_eyebrow', 'type' => 'text', 'label' => __( 'Team eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'page_about.team_title', 'type' => 'text', 'label' => __( 'Team title', 'woodshop-studio' ) ),
							array(
								'key'       => 'page_about.team',
								'type'      => 'repeater',
								'label'     => __( 'Team members', 'woodshop-studio' ),
								'add_label' => __( 'Add member', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'image', 'type' => 'media', 'label' => __( 'Photo', 'woodshop-studio' ) ),
									array( 'key' => 'name', 'type' => 'text', 'label' => __( 'Name', 'woodshop-studio' ) ),
									array( 'key' => 'role', 'type' => 'text', 'label' => __( 'Role', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
			'page_services' => array(
				'label'    => __( 'Services Page', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Services introduction', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'page_services.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'page_services.title', 'type' => 'text', 'label' => __( 'Page title', 'woodshop-studio' ) ),
							array( 'key' => 'page_services.intro', 'type' => 'textarea', 'label' => __( 'Introduction', 'woodshop-studio' ) ),
							array( 'key' => 'page_services.image', 'type' => 'media', 'label' => __( 'Hero image', 'woodshop-studio' ) ),
							array(
								'key'       => 'page_services.stats',
								'type'      => 'repeater',
								'label'     => __( 'Stats', 'woodshop-studio' ),
								'add_label' => __( 'Add stat', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'value', 'type' => 'text', 'label' => __( 'Value', 'woodshop-studio' ) ),
									array( 'key' => 'suffix', 'type' => 'text', 'label' => __( 'Suffix', 'woodshop-studio' ) ),
									array( 'key' => 'label', 'type' => 'text', 'label' => __( 'Label', 'woodshop-studio' ) ),
								),
							),
						),
					),
					array(
						'title'  => __( 'Process', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'page_services.process_eyebrow', 'type' => 'text', 'label' => __( 'Process eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'page_services.process_title', 'type' => 'text', 'label' => __( 'Process title', 'woodshop-studio' ) ),
							array(
								'key'       => 'page_services.process',
								'type'      => 'repeater',
								'label'     => __( 'Steps', 'woodshop-studio' ),
								'add_label' => __( 'Add step', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'woodshop-studio' ) ),
									array( 'key' => 'text', 'type' => 'textarea', 'label' => __( 'Text', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
			'page_contact' => array(
				'label'    => __( 'Contact Page', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Contact introduction', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'page_contact.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'page_contact.title', 'type' => 'text', 'label' => __( 'Page title', 'woodshop-studio' ) ),
							array( 'key' => 'page_contact.intro', 'type' => 'textarea', 'label' => __( 'Introduction', 'woodshop-studio' ) ),
							array( 'key' => 'page_contact.image', 'type' => 'media', 'label' => __( 'Hero image', 'woodshop-studio' ) ),
							array( 'key' => 'page_contact.info_image', 'type' => 'media', 'label' => __( 'Workshop image', 'woodshop-studio' ) ),
						),
					),
					array(
						'title'  => __( 'Contact details', 'woodshop-studio' ),
						'fields' => array(
							array(
								'key'       => 'page_contact.cards',
								'type'      => 'repeater',
								'label'     => __( 'Contact cards', 'woodshop-studio' ),
								'add_label' => __( 'Add card', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'icon', 'type' => 'select', 'label' => __( 'Icon', 'woodshop-studio' ), 'options' => $icons ),
									array( 'key' => 'label', 'type' => 'text', 'label' => __( 'Label', 'woodshop-studio' ) ),
									array( 'key' => 'value', 'type' => 'text', 'label' => __( 'Value', 'woodshop-studio' ) ),
									array( 'key' => 'url', 'type' => 'text', 'label' => __( 'Link', 'woodshop-studio' ) ),
								),
							),
							array( 'key' => 'page_contact.form_eyebrow', 'type' => 'text', 'label' => __( 'Form eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'page_contact.form_title', 'type' => 'text', 'label' => __( 'Form title', 'woodshop-studio' ) ),
						),
					),
					array(
						'title'  => __( 'FAQ', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'page_contact.faq_eyebrow', 'type' => 'text', 'label' => __( 'FAQ eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'page_contact.faq_title', 'type' => 'text', 'label' => __( 'FAQ title', 'woodshop-studio' ) ),
							array(
								'key'       => 'page_contact.faq',
								'type'      => 'repeater',
								'label'     => __( 'FAQ items', 'woodshop-studio' ),
								'add_label' => __( 'Add question', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'question', 'type' => 'text', 'label' => __( 'Question', 'woodshop-studio' ) ),
									array( 'key' => 'answer', 'type' => 'textarea', 'label' => __( 'Answer', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
			'page_portfolio' => array(
				'label'    => __( 'Portfolio Page', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Portfolio archive', 'woodshop-studio' ),
						'desc'   => __( 'Items are managed in the نمونه‌کارها post type. Use the Order field to control their archive order.', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'page_portfolio.eyebrow', 'type' => 'text', 'label' => __( 'Eyebrow', 'woodshop-studio' ) ),
							array( 'key' => 'page_portfolio.title', 'type' => 'text', 'label' => __( 'Page title', 'woodshop-studio' ) ),
							array( 'key' => 'page_portfolio.intro', 'type' => 'textarea', 'label' => __( 'Introduction', 'woodshop-studio' ) ),
							array( 'key' => 'page_portfolio.button_label', 'type' => 'text', 'label' => __( 'Button label', 'woodshop-studio' ) ),
							array( 'key' => 'page_portfolio.button_url', 'type' => 'text', 'label' => __( 'Button link', 'woodshop-studio' ) ),
						),
					),
				),
			),
			'footer'       => array(
				'label'    => __( 'Footer', 'woodshop-studio' ),
				'sections' => array(
					array(
						'title'  => __( 'Footer content', 'woodshop-studio' ),
						'fields' => array(
							array( 'key' => 'footer.about', 'type' => 'textarea', 'label' => __( 'Brand description', 'woodshop-studio' ) ),
							array( 'key' => 'footer.logo', 'type' => 'media', 'label' => __( 'Footer logo', 'woodshop-studio' ), 'desc' => __( 'Optional light logo for the dark footer. Defaults to the site logo.', 'woodshop-studio' ) ),
							array( 'key' => 'footer.copyright', 'type' => 'text', 'label' => __( 'Copyright', 'woodshop-studio' ), 'desc' => __( 'Use {year} and {site} as placeholders.', 'woodshop-studio' ) ),
							array( 'key' => 'footer.services_menu', 'type' => 'select', 'label' => __( 'Services menu', 'woodshop-studio' ), 'options' => $menus ),
							array( 'key' => 'footer.legal_menu', 'type' => 'select', 'label' => __( 'Legal menu', 'woodshop-studio' ), 'options' => $menus ),
							array(
								'key'       => 'footer.socials',
								'type'      => 'repeater',
								'label'     => __( 'Social profiles', 'woodshop-studio' ),
								'add_label' => __( 'Add profile', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'network', 'type' => 'select', 'label' => __( 'Network', 'woodshop-studio' ), 'options' => $socials ),
									array( 'key' => 'url', 'type' => 'url', 'label' => __( 'URL', 'woodshop-studio' ) ),
								),
							),
							array(
								'key'       => 'footer.instagram',
								'type'      => 'repeater',
								'label'     => __( 'Instagram grid', 'woodshop-studio' ),
								'add_label' => __( 'Add image', 'woodshop-studio' ),
								'subfields' => array(
									array( 'key' => 'image', 'type' => 'media', 'label' => __( 'Image', 'woodshop-studio' ) ),
									array( 'key' => 'url', 'type' => 'url', 'label' => __( 'Link', 'woodshop-studio' ) ),
								),
							),
						),
					),
				),
			),
		);

		return $this->schema;
	}

	/**
	 * Current tab key.
	 *
	 * @return string
	 */
	private function current_tab() {
		$tabs = $this->schema();
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'brand';

		return isset( $tabs[ $tab ] ) ? $tab : 'brand';
	}

	/**
	 * Render the settings page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'woodshop-studio' ) );
		}

		$tabs     = $this->schema();
		$current  = $this->current_tab();
		$tab_data = $tabs[ $current ];
		$reset_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'woodshop_studio_reset',
				),
				admin_url( 'admin-post.php' )
			),
			'woodshop_studio_reset'
		);

		$updated = isset( $_GET['updated'] ) && '1' === $_GET['updated'];
		$reset   = isset( $_GET['reset'] ) && '1' === $_GET['reset'];
		?>
		<div class="wrap wss-wrap">
			<header class="wss-header">
				<div class="wss-header__title">
					<span class="wss-logo" aria-hidden="true"><?php echo esc_html( 'W' ); ?></span>
					<div>
						<h1><?php esc_html_e( 'Wood Shop Studio', 'woodshop-studio' ); ?></h1>
						<p><?php esc_html_e( 'Everything on the homepage, in one place.', 'woodshop-studio' ); ?></p>
					</div>
				</div>
				<div class="wss-header__actions">
					<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View site', 'woodshop-studio' ); ?></a>
					<a class="button button-link-delete" href="<?php echo esc_url( $reset_url ); ?>" data-wss-reset><?php esc_html_e( 'Reset demo', 'woodshop-studio' ); ?></a>
				</div>
			</header>

			<?php if ( $updated ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'woodshop-studio' ); ?></p></div>
			<?php endif; ?>

			<?php if ( $reset ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Demo content restored.', 'woodshop-studio' ); ?></p></div>
			<?php endif; ?>

			<nav class="wss-tabs" aria-label="<?php esc_attr_e( 'Settings sections', 'woodshop-studio' ); ?>">
				<?php foreach ( $tabs as $key => $tab ) : ?>
					<a class="wss-tab<?php echo $key === $current ? ' is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=woodshop-studio&tab=' . $key ) ); ?>">
						<?php echo esc_html( $tab['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form class="wss-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="woodshop_studio_save">
				<input type="hidden" name="wss_tab" value="<?php echo esc_attr( $current ); ?>">
				<?php wp_nonce_field( 'woodshop_studio_save', 'woodshop_studio_nonce' ); ?>

				<div class="wss-panel">
					<h2 class="wss-panel__title"><?php echo esc_html( $tab_data['label'] ); ?></h2>

					<?php foreach ( $tab_data['sections'] as $section ) : ?>
						<section class="wss-card">
							<header class="wss-card__head">
								<h3><?php echo esc_html( $section['title'] ); ?></h3>
								<?php if ( ! empty( $section['desc'] ) ) : ?>
									<p><?php echo esc_html( $section['desc'] ); ?></p>
								<?php endif; ?>
							</header>
							<div class="wss-fields">
								<?php
								foreach ( $section['fields'] as $field ) {
									if ( 'enabled' === $field['key'] ) {
										$field['key'] = $current . '.enabled';
									}

									$path = explode( '.', $field['key'] );
									$this->render_field( $field, $path );
								}
								?>
							</div>
						</section>
					<?php endforeach; ?>
				</div>

				<div class="wss-savebar">
					<span class="wss-savebar__hint"><?php esc_html_e( 'Changes are applied to the live homepage immediately.', 'woodshop-studio' ); ?></span>
					<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save changes', 'woodshop-studio' ); ?></button>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Field name attribute.
	 *
	 * @param array<int, string|int> $path Path segments.
	 * @return string
	 */
	private function name( $path ) {
		return WOODSHOP_STUDIO_OPTION . '[' . implode( '][', $path ) . ']';
	}

	/**
	 * Current value for a path.
	 *
	 * @param array<int, string|int> $path Path segments.
	 * @param mixed                  $default Fallback.
	 * @return mixed
	 */
	private function value( $path, $default = '' ) {
		$value = woodshop_studio_get( implode( '.', $path ), $default );

		return null === $value ? $default : $value;
	}

	/**
	 * Render one field.
	 *
	 * @param array<string, mixed>   $field Field definition.
	 * @param array<int, string|int> $path  Path segments.
	 */
	private function render_field( $field, $path ) {
		$type  = $field['type'];
		$value = $this->value( $path );
		$id    = 'wss-' . substr( md5( $this->name( $path ) ), 0, 10 );

		if ( 'repeater' === $type ) {
			$this->render_repeater( $field, $path );
			return;
		}

		$classes = 'wss-field wss-field--' . $type;

		if ( in_array( $type, array( 'textarea', 'media', 'categories' ), true ) ) {
			$classes .= ' wss-field--wide';
		}

		echo '<div class="' . esc_attr( $classes ) . '">';

		if ( 'toggle' === $type ) {
			printf(
				'<label class="wss-switch" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s><span class="wss-switch__track" aria-hidden="true"></span><span class="wss-switch__label">%4$s</span></label>',
				esc_attr( $id ),
				esc_attr( $this->name( $path ) ),
				checked( (bool) $value, true, false ),
				esc_html( $field['label'] )
			);
		} else {
			printf( '<label class="wss-label" for="%s">%s</label>', esc_attr( $id ), esc_html( $field['label'] ) );
		}

		switch ( $type ) {
			case 'text':
			case 'url':
			case 'email':
				printf(
					'<input type="%1$s" class="wss-input" id="%2$s" name="%3$s" value="%4$s">',
					'email' === $type ? 'email' : ( 'url' === $type ? 'url' : 'text' ),
					esc_attr( $id ),
					esc_attr( $this->name( $path ) ),
					esc_attr( (string) $value )
				);
				break;

			case 'number':
				printf(
					'<input type="number" class="wss-input wss-input--small" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" step="1">',
					esc_attr( $id ),
					esc_attr( $this->name( $path ) ),
					esc_attr( (string) $value ),
					esc_attr( (string) ( $field['min'] ?? 0 ) ),
					esc_attr( (string) ( $field['max'] ?? 100 ) )
				);
				break;

			case 'textarea':
				printf(
					'<textarea class="wss-textarea" id="%1$s" name="%2$s" rows="4">%3$s</textarea>',
					esc_attr( $id ),
					esc_attr( $this->name( $path ) ),
					esc_textarea( (string) $value )
				);
				break;

			case 'select':
				printf( '<select class="wss-select" id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $this->name( $path ) ) );
				foreach ( (array) $field['options'] as $option_value => $option_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( (string) $option_value ),
						selected( (string) $value, (string) $option_value, false ),
						esc_html( $option_label )
					);
				}
				echo '</select>';
				break;

			case 'color':
				$color       = (string) $value;
				$field_reset = isset( $field['default'] ) ? (string) $field['default'] : '#000000';

				if ( preg_match( '/^#([a-f0-9])([a-f0-9])([a-f0-9])$/i', $color, $matches ) ) {
					$color = '#' . $matches[1] . $matches[1] . $matches[2] . $matches[2] . $matches[3] . $matches[3];
				}

				if ( ! preg_match( '/^#[a-f0-9]{6}$/i', $color ) ) {
					$color = $field_reset;
				}

				printf(
					'<div class="wss-color"><input type="color" value="%1$s" data-color-picker aria-label="%2$s"><input type="text" class="wss-input" id="%3$s" name="%4$s" value="%5$s" data-color-text placeholder="%6$s" spellcheck="false"></div>',
					esc_attr( $color ),
					esc_attr( $field['label'] ),
					esc_attr( $id ),
					esc_attr( $this->name( $path ) ),
					esc_attr( $color ),
					esc_attr( $field_reset )
				);
				break;

			case 'media':
				$this->render_media( $path, $value );
				break;

			case 'categories':
				$selected = is_array( $value ) ? array_map( 'absint', $value ) : array();
				echo '<div class="wss-catlist">';
				foreach ( (array) $field['options'] as $option_value => $option_label ) {
					if ( 0 === (int) $option_value ) {
						continue;
					}
					printf(
						'<label class="wss-catlist__item"><input type="checkbox" name="%1$s[]" value="%2$s" %3$s> %4$s</label>',
						esc_attr( $this->name( $path ) ),
						esc_attr( (string) $option_value ),
						checked( in_array( (int) $option_value, $selected, true ), true, false ),
						esc_html( $option_label )
					);
				}
				echo '</div>';
				break;
		}

		if ( ! empty( $field['desc'] ) ) {
			printf( '<p class="wss-desc">%s</p>', esc_html( $field['desc'] ) );
		}

		echo '</div>';
	}

	/**
	 * Media picker control.
	 *
	 * @param array<int, string|int> $path  Field path.
	 * @param mixed                  $value Stored value.
	 */
	private function render_media( $path, $value ) {
		$url = woodshop_studio_media_url( $value, 'medium' );
		?>
		<div class="wss-media" data-media>
			<input type="hidden" name="<?php echo esc_attr( $this->name( $path ) ); ?>" value="<?php echo esc_attr( is_scalar( $value ) ? (string) $value : '' ); ?>" data-media-input>
			<div class="wss-media__preview" data-media-preview>
				<?php if ( $url ) : ?>
					<img src="<?php echo esc_url( $url ); ?>" alt="">
				<?php else : ?>
					<span class="wss-media__empty"><?php esc_html_e( 'No image selected', 'woodshop-studio' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="wss-media__side">
				<div class="wss-media__actions">
					<button type="button" class="button" data-media-select><?php esc_html_e( 'Select image', 'woodshop-studio' ); ?></button>
					<button type="button" class="button-link" data-media-remove><?php esc_html_e( 'Remove', 'woodshop-studio' ); ?></button>
				</div>
				<code class="wss-media__url" data-media-url><?php echo esc_html( $url ? $url : '' ); ?></code>
			</div>
		</div>
		<?php
	}

	/**
	 * Repeater control.
	 *
	 * @param array<string, mixed>   $field Field definition.
	 * @param array<int, string|int> $path  Field path.
	 */
	private function render_repeater( $field, $path ) {
		$rows = $this->value( $path, array() );
		$rows = is_array( $rows ) ? array_values( $rows ) : array();
		?>
		<div class="wss-repeater" data-repeater>
			<div class="wss-repeater__top">
				<strong class="wss-repeater__label"><?php echo esc_html( $field['label'] ); ?></strong>
				<span class="wss-repeater__count" data-repeater-count><?php echo esc_html( (string) count( $rows ) ); ?></span>
			</div>

			<div class="wss-rows" data-rows>
				<?php foreach ( $rows as $index => $row ) : ?>
					<?php $this->render_repeater_row( $field, $path, $index, (array) $row, false ); ?>
				<?php endforeach; ?>
			</div>

			<button type="button" class="button wss-add" data-repeater-add><?php echo esc_html( $field['add_label'] ?? __( 'Add item', 'woodshop-studio' ) ); ?></button>

			<template data-repeater-template>
				<?php $this->render_repeater_row( $field, $path, '__i__', array(), true ); ?>
			</template>
		</div>
		<?php
	}

	/**
	 * One repeater row.
	 *
	 * @param array<string, mixed>   $field     Field definition.
	 * @param array<int, string|int> $path      Field path.
	 * @param int|string             $index     Row index or placeholder.
	 * @param array<string, mixed>   $row       Row values.
	 * @param bool                   $is_template Whether this is the JS template.
	 */
	private function render_repeater_row( $field, $path, $index, $row, $is_template ) {
		?>
		<div class="wss-row" data-row>
			<div class="wss-row__head">
				<span class="wss-row__handle" aria-hidden="true">⋮⋮</span>
				<strong><?php esc_html_e( 'Item', 'woodshop-studio' ); ?> <span data-row-number><?php echo is_int( $index ) ? esc_html( (string) ( $index + 1 ) ) : '—'; ?></span></strong>
				<button type="button" class="button-link wss-row__remove" data-row-remove><?php esc_html_e( 'Remove', 'woodshop-studio' ); ?></button>
			</div>
			<div class="wss-row__fields">
				<?php foreach ( $field['subfields'] as $sub ) : ?>
					<?php
					$sub_path  = array_merge( $path, array( $index, $sub['key'] ) );
					$sub_value = $row[ $sub['key'] ] ?? '';
					$sub_id    = 'wss-' . substr( md5( $this->name( $sub_path ) ), 0, 10 );

					if ( 'media' === $sub['type'] ) {
						echo '<div class="wss-field wss-field--media wss-field--wide">';
						printf( '<label class="wss-label">%s</label>', esc_html( $sub['label'] ) );
						$this->render_media( $sub_path, $sub_value );
						echo '</div>';
						continue;
					}

					echo '<div class="wss-field wss-field--' . esc_attr( $sub['type'] ) . '">';
					printf( '<label class="wss-label" for="%s">%s</label>', esc_attr( $sub_id ), esc_html( $sub['label'] ) );

					if ( 'select' === $sub['type'] ) {
						printf( '<select class="wss-select" id="%1$s" name="%2$s">', esc_attr( $sub_id ), esc_attr( $this->name( $sub_path ) ) );
						foreach ( (array) $sub['options'] as $option_value => $option_label ) {
							printf(
								'<option value="%1$s" %2$s>%3$s</option>',
								esc_attr( (string) $option_value ),
								selected( (string) $sub_value, (string) $option_value, false ),
								esc_html( $option_label )
							);
						}
						echo '</select>';
					} elseif ( 'textarea' === $sub['type'] ) {
						printf(
							'<textarea class="wss-textarea" id="%1$s" name="%2$s" rows="3">%3$s</textarea>',
							esc_attr( $sub_id ),
							esc_attr( $this->name( $sub_path ) ),
							esc_textarea( (string) $sub_value )
						);
					} elseif ( 'number' === $sub['type'] ) {
						printf(
							'<input type="number" class="wss-input wss-input--small" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s">',
							esc_attr( $sub_id ),
							esc_attr( $this->name( $sub_path ) ),
							esc_attr( (string) $sub_value ),
							esc_attr( (string) ( $sub['min'] ?? 0 ) ),
							esc_attr( (string) ( $sub['max'] ?? 100 ) )
						);
					} else {
						printf(
							'<input type="%1$s" class="wss-input" id="%2$s" name="%3$s" value="%4$s">',
							'url' === $sub['type'] ? 'url' : ( 'email' === $sub['type'] ? 'email' : 'text' ),
							esc_attr( $sub_id ),
							esc_attr( $this->name( $sub_path ) ),
							esc_attr( (string) $sub_value )
						);
					}

					echo '</div>';
				endforeach;
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Sanitize and save the submitted form.
	 */
	public function save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'woodshop-studio' ) );
		}

		check_admin_referer( 'woodshop_studio_save', 'woodshop_studio_nonce' );

		$raw  = isset( $_POST[ WOODSHOP_STUDIO_OPTION ] ) ? wp_unslash( $_POST[ WOODSHOP_STUDIO_OPTION ] ) : array();
		$raw  = is_array( $raw ) ? $raw : array();
		$tab  = isset( $_POST['wss_tab'] ) ? sanitize_key( wp_unslash( $_POST['wss_tab'] ) ) : 'brand';
		$tabs = $this->schema();

		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'brand';
		}

		$saved = get_option( WOODSHOP_STUDIO_OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		$data  = $this->sanitize_tab( $raw, $tab, $saved );

		update_option( WOODSHOP_STUDIO_OPTION, $data );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'woodshop-studio',
					'tab'     => $tab,
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Delete the saved settings.
	 */
	public function reset() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'woodshop-studio' ) );
		}

		check_admin_referer( 'woodshop_studio_reset' );

		delete_option( WOODSHOP_STUDIO_OPTION );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'  => 'woodshop-studio',
					'reset' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Sanitize one tab's payload and merge it over the stored settings.
	 *
	 * @param array<string, mixed> $raw    Raw payload.
	 * @param string               $tab    Active tab key.
	 * @param array<string, mixed> $saved  Stored settings.
	 * @return array<string, mixed>
	 */
	private function sanitize_tab( $raw, $tab, $saved ) {
		$tabs  = $this->schema();
		$clean = $saved;

		if ( ! isset( $tabs[ $tab ] ) ) {
			return $clean;
		}

		foreach ( $tabs[ $tab ]['sections'] as $section ) {
			foreach ( $section['fields'] as $field ) {
				if ( 'enabled' === $field['key'] ) {
					$field['key'] = $tab . '.enabled';
				}

				$path = explode( '.', $field['key'] );

				if ( 'repeater' === $field['type'] ) {
					$rows = $this->get_raw( $raw, $path );
					$rows = is_array( $rows ) ? $rows : array();
					$list = array();

					foreach ( $rows as $row ) {
						if ( ! is_array( $row ) ) {
							continue;
						}

						$clean_row = array();
						$has_value = false;

						foreach ( $field['subfields'] as $sub ) {
							$value = $row[ $sub['key'] ] ?? '';
							$value = $this->sanitize_value( $sub['type'], $value, $sub );

							if ( '' !== $value && array() !== $value ) {
								$has_value = true;
							}

							$clean_row[ $sub['key'] ] = $value;
						}

						if ( $has_value ) {
							$list[] = $clean_row;
						}
					}

					$this->set_path( $clean, $path, $list );
					continue;
				}

				$value = $this->get_raw( $raw, $path );
				$value = $this->sanitize_value( $field['type'], $value, $field );
				$this->set_path( $clean, $path, $value );
			}
		}

		return $clean;
	}

	/**
	 * Read a nested raw value.
	 *
	 * @param array<string, mixed>   $raw  Raw payload.
	 * @param array<int, string|int> $path Path.
	 * @return mixed
	 */
	private function get_raw( $raw, $path ) {
		$value = $raw;

		foreach ( $path as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return null;
			}
			$value = $value[ $segment ];
		}

		return $value;
	}

	/**
	 * Set a nested value.
	 *
	 * @param array<string, mixed>   $target Target array, by reference.
	 * @param array<int, string|int> $path   Path.
	 * @param mixed                  $value  Value.
	 */
	private function set_path( &$target, $path, $value ) {
		$reference = &$target;

		foreach ( $path as $segment ) {
			if ( ! isset( $reference[ $segment ] ) || ! is_array( $reference[ $segment ] ) ) {
				$reference[ $segment ] = array();
			}
			$reference = &$reference[ $segment ];
		}

		$reference = $value;
	}

	/**
	 * Sanitize a single value by type.
	 *
	 * @param string               $type  Field type.
	 * @param mixed                $value Raw value.
	 * @param array<string, mixed> $field Field definition.
	 * @return mixed
	 */
	private function sanitize_value( $type, $value, $field = array() ) {
		switch ( $type ) {
			case 'text':
				return sanitize_text_field( (string) $value );

			case 'textarea':
				return sanitize_textarea_field( (string) $value );

			case 'url':
				return esc_url_raw( (string) $value );

			case 'email':
				return sanitize_email( (string) $value );

			case 'number':
				return absint( $value );

			case 'toggle':
				return empty( $value ) ? 0 : 1;

			case 'select':
				$value   = (string) $value;
				$options = isset( $field['options'] ) && is_array( $field['options'] ) ? array_map( 'strval', array_keys( $field['options'] ) ) : array();

				return in_array( $value, $options, true ) ? $value : '';

			case 'color':
				$value = sanitize_hex_color( (string) $value );

				return $value ? $value : (string) ( $field['default'] ?? '' );

			case 'media':
				if ( is_numeric( $value ) ) {
					return absint( $value );
				}

				return esc_url_raw( (string) $value );

			case 'categories':
				$value = is_array( $value ) ? $value : array();
				return array_values( array_unique( array_filter( array_map( 'absint', $value ) ) ) );

			default:
				return sanitize_text_field( (string) $value );
		}
	}
}
