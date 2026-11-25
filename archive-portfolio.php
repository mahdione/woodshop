<?php
/**
 * Portfolio archive.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="ws-page page-portfolio">
	<div class="wrap">
		<?php woodshop_breadcrumb(); ?>
	</div>

	<?php
	get_template_part(
		'template-parts/page-intro',
		null,
		array(
			'eyebrow'      => woodshop_option( 'page_portfolio.eyebrow', 'نمونه‌کارها' ),
			'title'        => woodshop_option( 'page_portfolio.title', 'نمونه‌کارهای ما' ),
			'intro'        => woodshop_option( 'page_portfolio.intro', '' ),
			'button_label' => woodshop_option( 'page_portfolio.button_label', 'درخواست پروژه مشابه' ),
			'button_url'   => woodshop_option( 'page_portfolio.button_url', '#request' ),
		)
	);
	?>

	<section class="section portfolio-section">
		<div class="wrap">
			<?php if ( have_posts() ) : ?>
				<div class="portfolio-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article <?php post_class( 'portfolio-card reveal' ); ?>>
							<div class="portfolio-card__media">
								<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail(
										'ws-card',
										array(
											'class'   => 'portfolio-card__img',
											'loading' => 'lazy',
										)
									);
								}
								?>
								<span class="portfolio-card__shade" aria-hidden="true"></span>
							</div>
							<div class="portfolio-card__body">
								<span class="eyebrow"><?php esc_html_e( 'نمونه‌کار', 'woodshop' ); ?></span>
								<h2 class="portfolio-card__title"><?php the_title(); ?></h2>
								<?php if ( has_excerpt() ) : ?>
									<p class="portfolio-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
								<?php endif; ?>
							</div>
						</article>
					<?php endwhile; ?>
				</div>

				<?php woodshop_pagination(); ?>
			<?php else : ?>
				<p class="section-text"><?php esc_html_e( 'هنوز نمونه‌کاری منتشر نشده است.', 'woodshop' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta' ); ?>
</div>
<?php
get_footer();
