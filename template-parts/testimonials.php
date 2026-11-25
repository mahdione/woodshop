<?php
/**
 * Client reviews.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_items = woodshop_option(
	'testimonials.items',
	array(
		array(
			'text'  => 'It’s rare to find a team that works with such dedication and precision. The custom shelving unit they designed and installed for us completely transformed our living space, giving us the perfect blend of aesthetics and functionality. Their team was easy to communicate with, always on time, and ensured every aspect of the job was completed to perfection. I would absolutely hire them again.',
			'name'  => 'Michael Carter',
			'meta'  => 'Atlanta, Georgia',
			'image' => WOODSHOP_URI . '/assets/img/avatar-1.webp',
			'rating' => 5,
		),
		array(
			'text'  => 'The level of craftsmanship and care that Nail & Knot puts into their work is unmatched. They built a custom entertainment center for our home, and it turned out to be the perfect addition to our space. Every cut, joint, and finish was done meticulously, and their team was professional and respectful throughout the project. Their work speaks for itself, and I highly recommend them.',
			'name'  => 'Sarah Thompson',
			'meta'  => 'Tampa, Florida',
			'image' => WOODSHOP_URI . '/assets/img/avatar-2.webp',
			'rating' => 5,
		),
		array(
			'text'  => 'Hiring Nail & Knot was the best decision for our home renovation. The team was incredibly skilled, paid close attention to every detail, and worked tirelessly to deliver exactly what we wanted. The custom carpentry work they provided has completely elevated our space, and we couldn’t be more satisfied. Their expertise and reliability make them our go-to carpenters for any future projects.',
			'name'  => 'David Wilson',
			'meta'  => 'Biloxi, Mississippi',
			'image' => WOODSHOP_URI . '/assets/img/avatar-3.webp',
			'rating' => 5,
		),
	)
);

$ws_items = array_values( array_filter( (array) $ws_items ) );

if ( empty( $ws_items ) ) {
	return;
}
?>
<section class="testimonials" id="reviews">
	<?php
	woodshop_image(
		woodshop_option( 'testimonials.background', WOODSHOP_URI . '/assets/img/bg-reviews.webp' ),
		array(
			'alt'    => '',
			'width'  => 1600,
			'height' => 1066,
			'class'  => 'testimonials__bg',
			'sizes'  => '100vw',
		)
	);
	?>
	<div class="wrap testimonials__grid">
		<div class="testimonials__intro reveal">
			<?php woodshop_eyebrow( woodshop_option( 'testimonials.eyebrow', 'Client Reviews' ) ); ?>
			<h2 class="section-title"><?php echo esc_html( woodshop_option( 'testimonials.title', 'What Our Clients Say About Our Carpentry' ) ); ?></h2>
			<p class="section-text"><?php echo esc_html( woodshop_option( 'testimonials.text', 'Craftsmanship that speaks for itself, trusted by homeowners and businesses alike.' ) ); ?></p>
		</div>

		<div class="testimonials__slider reveal" data-testimonials>
			<div class="testimonials__viewport">
				<div class="testimonials__track">
					<?php foreach ( $ws_items as $ws_index => $ws_item ) : ?>
						<?php $ws_first = 0 === $ws_index; ?>
						<article class="testimonial<?php echo $ws_first ? ' is-active' : ''; ?>" data-slide="<?php echo esc_attr( (string) $ws_index ); ?>" aria-hidden="<?php echo $ws_first ? 'false' : 'true'; ?>">
							<div class="testimonial__card">
								<div class="testimonial__stars" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: star count. */ _n( '%d star', '%d stars', (int) ( $ws_item['rating'] ?? 5 ), 'woodshop' ), (int) ( $ws_item['rating'] ?? 5 ) ) ); ?>">
									<?php for ( $ws_s = 0; $ws_s < 5; $ws_s++ ) : ?>
										<?php echo woodshop_icon( 'star', 'testimonial__star', 14 ); ?>
									<?php endfor; ?>
								</div>
								<blockquote class="testimonial__text"><?php echo esc_html( $ws_item['text'] ?? '' ); ?></blockquote>
								<div class="testimonial__footer">
									<span class="testimonial__avatar">
										<?php woodshop_image( $ws_item['image'] ?? '', array( 'width' => 56, 'height' => 56, 'alt' => $ws_item['name'] ?? '', 'class' => 'testimonial__img', 'sizes' => '56px' ) ); ?>
									</span>
									<cite class="testimonial__cite">
										<strong><?php echo esc_html( $ws_item['name'] ?? '' ); ?></strong>
										<span><?php echo esc_html( $ws_item['meta'] ?? '' ); ?></span>
									</cite>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="testimonials__controls">
				<div class="testimonials__dots" role="tablist" aria-label="<?php esc_attr_e( 'Client reviews', 'woodshop' ); ?>"></div>
				<div class="testimonials__arrows">
					<button type="button" class="circle-btn" data-testimonial-prev aria-label="<?php esc_attr_e( 'Previous review', 'woodshop' ); ?>"><?php echo woodshop_icon( 'chevron-right', 'icon-flip', 15 ); ?></button>
					<button type="button" class="circle-btn" data-testimonial-next aria-label="<?php esc_attr_e( 'Next review', 'woodshop' ); ?>"><?php echo woodshop_icon( 'chevron-right', '', 15 ); ?></button>
				</div>
			</div>
		</div>
	</div>
</section>
