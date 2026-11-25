<?php
/**
 * Blog index / fallback template.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="ws-page">
	<div class="wrap">
		<header class="page-hero">
			<?php woodshop_breadcrumb(); ?>
			<h1 class="page-title"><?php echo esc_html( is_home() && ! is_front_page() ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Latest News & Resources', 'woodshop' ) ); ?></h1>
			<p class="page-subtitle"><?php esc_html_e( 'Expert carpentry knowledge, woodworking innovations, and creative solutions.', 'woodshop' ); ?></p>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'post-card' ); ?>>
						<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'ws-card', array( 'class' => 'post-card__img', 'loading' => 'lazy' ) );
							}
							?>
						</a>
						<div class="post-card__body">
							<div class="post-meta"><?php echo esc_html( woodshop_persian_digits( get_the_date() ) ); ?></div>
							<h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="post-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<a class="link-arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'woodshop' ); ?><?php echo woodshop_icon( 'arrow-right', '', 16 ); ?></a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<?php woodshop_pagination(); ?>
		<?php else : ?>
			<p class="section-text"><?php esc_html_e( 'No posts found.', 'woodshop' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
