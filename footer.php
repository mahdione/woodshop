<?php
/**
 * Site footer.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_phone    = woodshop_option( 'brand.phone', '(123) 456 7890' );
$ws_phone_h  = woodshop_option( 'brand.phone_url', 'tel:+11234567890' );
$ws_email    = woodshop_option( 'brand.email', 'info@example.com' );
$ws_address  = woodshop_option( 'brand.address', '1234 Timberline Ave, Orlando, FL' );
$ws_hours    = woodshop_option( 'brand.hours', 'Mon - Fri: 9am – 8pm' );
$ws_quote    = woodshop_option( 'brand.quote_label', __( 'Get Free Quote', 'woodshop' ) );
$ws_quote_u  = woodshop_option( 'brand.quote_url', '/#request' );
$ws_socials  = woodshop_option( 'footer.socials', array() );
$ws_insta    = woodshop_option( 'footer.instagram', array() );
$ws_services = (int) woodshop_option( 'footer.services_menu', 0 );
$ws_legal    = (int) woodshop_option( 'footer.legal_menu', 0 );
$ws_copy     = woodshop_option( 'footer.copyright', '© 2026 Nail & Knot. All rights reserved.' );
$ws_footer_logo = woodshop_option( 'footer.logo', 0 );

$ws_copy = str_replace(
	array( '{year}', '{site}' ),
	array( gmdate( 'Y' ), get_bloginfo( 'name' ) ),
	$ws_copy
);
?>
	</main><!-- #content -->

	<footer class="site-footer" id="site-footer">
		<div class="wrap">
			<div class="footer-grid">
				<div class="footer-brand">
					<a class="footer-brand__logo<?php echo $ws_footer_logo ? '' : ' footer-brand__logo--invert'; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<?php
						if ( $ws_footer_logo ) {
							woodshop_image( $ws_footer_logo, array( 'width' => 140, 'height' => 98, 'alt' => get_bloginfo( 'name' ), 'class' => 'footer-logo' ) );
						} elseif ( has_custom_logo() ) {
							$ws_logo_id = get_theme_mod( 'custom_logo' );
							woodshop_image( $ws_logo_id, array( 'width' => 120, 'height' => 84, 'alt' => get_bloginfo( 'name' ), 'class' => 'footer-logo' ) );
						} else {
							printf(
								'<img src="%s" width="120" height="84" alt="%s" loading="lazy" decoding="async" class="footer-logo">',
								esc_url( WOODSHOP_URI . '/assets/img/logo.png' ),
								esc_attr( get_bloginfo( 'name' ) )
							);
						}
						?>
					</a>
					<p class="footer-brand__text"><?php echo esc_html( woodshop_option( 'footer.about', __( 'Expert carpentry crafted with precision, quality, and lasting beauty for your space.', 'woodshop' ) ) ); ?></p>
					<?php woodshop_socials( $ws_socials, 'socials' ); ?>
				</div>

				<div class="footer-col">
					<h3 class="footer-title"><?php esc_html_e( 'Services', 'woodshop' ); ?></h3>
					<?php
					if ( $ws_services ) {
						wp_nav_menu(
							array(
								'menu'        => $ws_services,
								'container'   => false,
								'menu_class'  => 'footer-links',
								'depth'       => 1,
								'fallback_cb' => false,
							)
						);
					} elseif ( has_nav_menu( 'footer_services' ) ) {
						woodshop_menu( 'footer_services', 'footer-links' );
					} else {
						$ws_terms = function_exists( 'woodshop_product_categories' ) ? woodshop_product_categories( array(), 6 ) : array();
						if ( $ws_terms ) {
							echo '<ul class="footer-links">';
							foreach ( $ws_terms as $ws_term ) {
								printf(
									'<li><a href="%1$s">%2$s</a></li>',
									esc_url( get_term_link( $ws_term ) ),
									esc_html( $ws_term->name )
								);
							}
							echo '</ul>';
						}
					}
					?>
				</div>

				<div class="footer-col">
					<h3 class="footer-title"><?php esc_html_e( 'Contact Info', 'woodshop' ); ?></h3>
					<ul class="footer-contact">
						<li><a href="<?php echo esc_url( $ws_phone_h ); ?>"><?php echo woodshop_icon( 'phone', '', 16 ); ?><span><?php echo esc_html( $ws_phone ); ?></span></a></li>
						<li><a href="mailto:<?php echo esc_attr( antispambot( $ws_email ) ); ?>"><?php echo woodshop_icon( 'email', '', 16 ); ?><span><?php echo esc_html( antispambot( $ws_email ) ); ?></span></a></li>
						<li><?php echo woodshop_icon( 'pin', '', 16 ); ?><span><?php echo esc_html( $ws_address ); ?></span></li>
						<li><?php echo woodshop_icon( 'clock', '', 16 ); ?><span><?php echo esc_html( $ws_hours ); ?></span></li>
					</ul>
					<a class="btn footer-quote" href="<?php echo esc_url( $ws_quote_u ); ?>"><?php echo esc_html( $ws_quote ); ?></a>
				</div>

				<div class="footer-col footer-insta">
					<h3 class="footer-title"><?php esc_html_e( 'Instagram', 'woodshop' ); ?></h3>
					<?php if ( ! empty( $ws_insta ) ) : ?>
						<div class="insta-grid">
							<?php $ws_insta_items = array_slice( $ws_insta, 0, 6 ); ?>
							<?php foreach ( $ws_insta_items as $ws_ii => $item ) : ?>
								<?php
								$item_url = is_array( $item ) ? ( $item['url'] ?? '' ) : '';
								$item_img = is_array( $item ) ? ( $item['image'] ?? '' ) : $item;
								$external = $item_url && '#' !== $item_url;
								?>
								<a href="<?php echo esc_url( $item_url ? $item_url : '#' ); ?>" class="insta-grid__item" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: image number. */ __( 'View Instagram photo %d', 'woodshop' ), $ws_ii + 1 ) ); ?>"<?php echo $external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
									<?php woodshop_image( $item_img, array( 'width' => 160, 'height' => 160, 'alt' => '', 'class' => 'insta-img', 'sizes' => '160px' ) ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="footer-bottom">
				<div class="footer-legal">
					<?php
					if ( $ws_legal ) {
						wp_nav_menu(
							array(
								'menu'        => $ws_legal,
								'container'   => false,
								'menu_class'  => 'legal-links',
								'depth'       => 1,
								'fallback_cb' => false,
							)
						);
					} elseif ( has_nav_menu( 'footer_legal' ) ) {
						woodshop_menu( 'footer_legal', 'legal-links' );
					} else {
						$ws_privacy = get_privacy_policy_url();
						echo '<ul class="legal-links">';
						echo '<li><a href="' . esc_url( $ws_privacy ? $ws_privacy : home_url( '/' ) ) . '">' . esc_html__( 'Privacy Policy', 'woodshop' ) . '</a></li>';
						echo '</ul>';
					}
					?>
				</div>
				<p class="footer-copy"><?php echo esc_html( $ws_copy ); ?></p>
			</div>
		</div>
	</footer>

<?php get_template_part( 'template-parts/cart-drawer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
