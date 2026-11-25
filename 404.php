<?php
/**
 * 404 template.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="ws-page error-page">
	<div class="wrap">
		<p class="error-code" aria-hidden="true">404</p>
		<h1 class="page-title"><?php esc_html_e( 'This page has been sanded away', 'woodshop' ); ?></h1>
		<p class="page-subtitle"><?php esc_html_e( 'The page you are looking for does not exist. Try searching, or head back home.', 'woodshop' ); ?></p>
		<div class="error-actions">
			<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'woodshop' ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( woodshop_shop_url() ); ?>"><?php esc_html_e( 'Browse the shop', 'woodshop' ); ?></a>
		</div>
		<div class="error-search"><?php get_search_form(); ?></div>
	</div>
</div>
<?php
get_footer();
