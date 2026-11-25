<?php
/**
 * Showcase / bento cards.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_items = woodshop_option(
	'showcase.items',
	array(
		array(
			'layout' => 'split',
			'size'   => 'large',
			'title'  => 'Restoring Beauty, Preserving History',
			'text'   => 'Breathing new life into antique furniture and architectural woodwork with meticulous repairs that honor their original craftsmanship.',
			'image'  => WOODSHOP_URI . '/assets/img/collage-a.webp',
			'url'    => '#',
		),
		array(
			'layout' => 'cover',
			'size'   => 'small',
			'title'  => 'Timeless Carpentry Built for You',
			'text'   => '',
			'image'  => WOODSHOP_URI . '/assets/img/collage-b.webp',
			'url'    => '#',
		),
		array(
			'layout' => 'cover',
			'size'   => 'small',
			'title'  => 'Crafting Excellence in Carpentry',
			'text'   => '',
			'image'  => WOODSHOP_URI . '/assets/img/collage-c.webp',
			'url'    => '#',
		),
		array(
			'layout' => 'split',
			'size'   => 'large',
			'title'  => 'Crafted Floors, Built to Endure',
			'text'   => 'Hardwood flooring installed with carpentry expertise—sanded, sealed, and finished for timeless beauty and everyday resilience.',
			'image'  => WOODSHOP_URI . '/assets/img/collage-d.webp',
			'url'    => '#',
		),
	)
);

if ( empty( $ws_items ) ) {
	return;
}
?>
<section class="section showcase-section" aria-label="<?php esc_attr_e( 'Selected work', 'woodshop' ); ?>">
	<h2 class="screen-reader-text"><?php esc_html_e( 'Selected work', 'woodshop' ); ?></h2>
	<div class="wrap">
		<div class="showcase">
			<?php foreach ( $ws_items as $ws_item ) : ?>
				<?php
				$ws_layout = 'cover' === ( $ws_item['layout'] ?? 'split' ) ? 'cover' : 'split';
				$ws_size   = 'small' === ( $ws_item['size'] ?? 'large' ) ? 'small' : 'large';
				$ws_url    = $ws_item['url'] ?? '#';
				?>
				<article class="showcase__card showcase__card--<?php echo esc_attr( $ws_layout ); ?> showcase__card--<?php echo esc_attr( $ws_size ); ?> reveal">
					<?php if ( 'cover' === $ws_layout ) : ?>
						<a class="showcase__cover" href="<?php echo esc_url( $ws_url ); ?>">
							<?php woodshop_image( $ws_item['image'] ?? '', array( 'width' => 900, 'height' => 700, 'alt' => '', 'class' => 'showcase__img', 'sizes' => '(max-width: 900px) 100vw, 33vw' ) ); ?>
							<span class="showcase__shade" aria-hidden="true"></span>
							<h3 class="showcase__cover-title"><?php echo esc_html( $ws_item['title'] ?? '' ); ?></h3>
						</a>
					<?php else : ?>
						<div class="showcase__body">
							<h3 class="showcase__title"><a href="<?php echo esc_url( $ws_url ); ?>"><?php echo esc_html( $ws_item['title'] ?? '' ); ?></a></h3>
							<?php if ( ! empty( $ws_item['text'] ) ) : ?>
								<p class="showcase__text"><?php echo esc_html( $ws_item['text'] ); ?></p>
							<?php endif; ?>
						</div>
						<div class="showcase__media">
							<?php woodshop_image( $ws_item['image'] ?? '', array( 'width' => 1200, 'height' => 800, 'alt' => '', 'class' => 'showcase__img', 'sizes' => '(max-width: 900px) 100vw, 55vw' ) ); ?>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
