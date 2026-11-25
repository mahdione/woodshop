<?php
/**
 * Latest posts.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_count = max( 2, (int) woodshop_option( 'blog.count', 5 ) );

$ws_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => $ws_count,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $ws_query->have_posts() ) {
	wp_reset_postdata();
	return;
}

$ws_posts = $ws_query->posts;
$ws_first = array_shift( $ws_posts );
$ws_blog_url = get_permalink( get_option( 'page_for_posts' ) );
?>
<section class="section blog-section" id="blog">
	<div class="wrap">
		<div class="section-head section-head--center reveal">
			<?php woodshop_eyebrow( woodshop_option( 'blog.eyebrow', 'Our Blog' ) ); ?>
			<h2 class="section-title"><?php echo esc_html( woodshop_option( 'blog.title', 'Latest News & Resources' ) ); ?></h2>
			<p class="section-text"><?php echo esc_html( woodshop_option( 'blog.text', 'Read our blog for expert carpentry knowledge, woodworking innovations, and creative solutions.' ) ); ?></p>
		</div>

		<div class="blog-grid">
			<article class="post-feature reveal">
				<a class="post-feature__media" href="<?php echo esc_url( get_permalink( $ws_first ) ); ?>" tabindex="-1" aria-hidden="true">
					<?php
					$ws_thumb = get_the_post_thumbnail_url( $ws_first, 'ws-wide' );
					woodshop_image(
						$ws_thumb,
						array(
							'alt'    => '',
							'width'  => 1000,
							'height' => 640,
							'class'  => 'post-feature__img',
							'sizes'  => '(max-width: 900px) 100vw, 58vw',
						)
					);
					?>
				</a>
				<div class="post-feature__body">
					<?php $ws_cats = get_the_category( $ws_first->ID ); ?>
					<?php if ( $ws_cats ) : ?>
						<span class="post-meta__cat"><?php echo esc_html( $ws_cats[0]->name ); ?></span>
					<?php endif; ?>
					<h3 class="post-feature__title"><a href="<?php echo esc_url( get_permalink( $ws_first ) ); ?>"><?php echo esc_html( get_the_title( $ws_first ) ); ?></a></h3>
					<div class="post-meta">
						<span><?php echo esc_html( get_the_author_meta( 'display_name', $ws_first->post_author ) ); ?></span>
						<span aria-hidden="true">·</span>
						<time datetime="<?php echo esc_attr( get_the_date( 'c', $ws_first ) ); ?>"><?php echo esc_html( woodshop_persian_digits( get_the_date( '', $ws_first ) ) ); ?></time>
					</div>
				</div>
			</article>

			<div class="post-list">
				<?php foreach ( $ws_posts as $ws_post ) : ?>
					<article class="post-mini reveal">
						<a class="post-mini__media" href="<?php echo esc_url( get_permalink( $ws_post ) ); ?>" tabindex="-1" aria-hidden="true">
							<?php
							$ws_mini_thumb = get_the_post_thumbnail_url( $ws_post, 'ws-thumb' );
							woodshop_image(
								$ws_mini_thumb,
								array(
									'alt'    => '',
									'width'  => 160,
									'height' => 110,
									'class'  => 'post-mini__img',
									'sizes'  => '160px',
								)
							);
							?>
						</a>
						<div class="post-mini__body">
							<h4 class="post-mini__title"><a href="<?php echo esc_url( get_permalink( $ws_post ) ); ?>"><?php echo esc_html( get_the_title( $ws_post ) ); ?></a></h4>
							<div class="post-meta">
								<time datetime="<?php echo esc_attr( get_the_date( 'c', $ws_post ) ); ?>"><?php echo esc_html( woodshop_persian_digits( get_the_date( '', $ws_post ) ) ); ?></time>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ( $ws_blog_url ) : ?>
			<div class="section-actions reveal">
				<a class="btn" href="<?php echo esc_url( $ws_blog_url ); ?>"><?php echo esc_html( woodshop_option( 'blog.button_label', 'View All Articles' ) ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
wp_reset_postdata();
