<?php
/**
 * Single post.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="ws-page">
		<div class="wrap">
			<article class="single-post">
				<header class="page-hero">
					<?php woodshop_breadcrumb(); ?>
					<h1 class="page-title"><?php the_title(); ?></h1>
					<div class="post-meta">
						<span><?php the_author(); ?></span>
						<span aria-hidden="true">·</span>
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( woodshop_persian_digits( get_the_date() ) ); ?></time>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="single-post__media"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>

				<div class="entry-content">
					<?php the_content(); ?>
				</div>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</article>
		</div>
	</div>
	<?php
endwhile;

get_footer();
