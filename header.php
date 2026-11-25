<?php
/**
 * Site header.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_overlay = is_front_page() && woodshop_section_enabled( 'hero' );
$ws_phone   = woodshop_option( 'brand.phone', '(123) 456 7890' );
$ws_phone_h = woodshop_option( 'brand.phone_url', 'tel:+11234567890' );
$ws_quote   = woodshop_option( 'brand.quote_label', __( 'Get Free Quote', 'woodshop' ) );
$ws_quote_u = woodshop_option( 'brand.quote_url', '/#request' );
$ws_cart    = woodshop_cart_info();
$ws_menu_id = (int) woodshop_option( 'header.menu_id', 0 );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'woodshop' ); ?></a>

<header class="site-header<?php echo $ws_overlay ? ' site-header--overlay' : ' site-header--solid'; ?>">
	<div class="wrap">
		<div class="header-top">
			<a class="header-phone" href="<?php echo esc_url( $ws_phone_h ); ?>">
				<?php echo woodshop_icon( 'phone', '', 15 ); ?>
				<span><?php echo esc_html( $ws_phone ); ?></span>
			</a>

			<div class="header-brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a class="header-brand__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<img src="<?php echo esc_url( WOODSHOP_URI . '/assets/img/logo.png' ); ?>" width="100" height="70" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					</a>
				<?php endif; ?>
			</div>

			<div class="header-actions">
				<?php if ( function_exists( 'WC' ) && woodshop_option( 'header.show_cart', 1 ) ) : ?>
					<a class="header-cart" href="<?php echo esc_url( $ws_cart['url'] ); ?>" data-cart-open aria-controls="cart-drawer" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: cart item count. */ _n( 'View cart, %s item', 'View cart, %s items', $ws_cart['count'], 'woodshop' ), woodshop_persian_digits( (string) $ws_cart['count'] ) ) ); ?>">
						<?php echo woodshop_icon( 'basket', '', 20 ); ?>
						<span class="header-cart__count<?php echo $ws_cart['count'] ? ' has-items' : ''; ?>" data-cart-count><?php echo esc_html( woodshop_persian_digits( (string) $ws_cart['count'] ) ); ?></span>
					</a>
				<?php endif; ?>

				<a class="btn header-quote" href="<?php echo esc_url( $ws_quote_u ); ?>"><?php echo esc_html( $ws_quote ); ?></a>

				<button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false">
					<span class="screen-reader-text"><?php esc_html_e( 'Toggle menu', 'woodshop' ); ?></span>
					<span class="nav-toggle__open"><?php echo woodshop_icon( 'menu', '', 24 ); ?></span>
					<span class="nav-toggle__close"><?php echo woodshop_icon( 'close', '', 22 ); ?></span>
				</button>
			</div>
		</div>

		<div class="header-nav" id="site-nav">
			<?php
			if ( $ws_menu_id ) {
				wp_nav_menu(
					array(
						'menu'        => $ws_menu_id,
						'container'   => false,
						'menu_class'  => 'ws-menu',
						'depth'       => 2,
						'fallback_cb' => false,
					)
				);
			} else {
				woodshop_menu( 'primary' );
			}
			?>

			<div class="header-nav__extra">
				<a href="<?php echo esc_url( $ws_phone_h ); ?>"><?php echo woodshop_icon( 'phone', '', 15 ); ?><?php echo esc_html( $ws_phone ); ?></a>
				<a class="btn btn--sm" href="<?php echo esc_url( $ws_quote_u ); ?>"><?php echo esc_html( $ws_quote ); ?></a>
			</div>
		</div>
	</div>
</header>

<main id="content" class="site-content">
