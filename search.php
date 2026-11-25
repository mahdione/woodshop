<?php
/**
 * Search results.
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
			<h1 class="page-title">
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Search results for “%s”', 'woodshop' ), esc_html( get_search_query() ) );
				?>
			</h1>
			<?php get_search_form(); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'post-card' ); ?>>
						<div class="post-card__body">
							<div class="post-meta"><?php echo esc_html( woodshop_persian_digits( get_the_date() ) ); ?></div>
							<h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="post-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php woodshop_pagination(); ?>
		<?php else : ?>
			<p class="section-text"><?php esc_html_e( 'Nothing matched your search. Try a different keyword.', 'woodshop' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
