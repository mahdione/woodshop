<?php
/**
 * Static page.
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
			<header class="page-hero">
				<?php woodshop_breadcrumb(); ?>
				<h1 class="page-title"><?php the_title(); ?></h1>
			</header>

			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'woodshop' ),
						'after'  => '</div>',
					)
				);
				?>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
