<?php
/**
 * Shared inner-page introduction.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
	$args ?? array(),
	array(
		'eyebrow' => '',
		'title'   => '',
		'intro'   => '',
		'image'   => '',
		'image_alt' => '',
		'button_label' => '',
		'button_url'   => '',
		'rating'  => '',
		'rating_label' => '',
		'count'   => '',
		'count_label' => '',
	)
);

$ws_has_media = (bool) ( $args['rating'] || $args['image'] );
?>
<section class="page-intro">
	<div class="wrap page-intro__grid<?php echo $args['rating'] ? ' page-intro__grid--rating' : ''; ?><?php echo $ws_has_media ? '' : ' page-intro__grid--text'; ?>">
		<div class="page-intro__content reveal">
			<?php woodshop_eyebrow( $args['eyebrow'] ); ?>
			<h1 class="page-intro__title"><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( $args['intro'] ) : ?>
				<p class="page-intro__text"><?php echo esc_html( $args['intro'] ); ?></p>
			<?php endif; ?>
			<?php if ( $args['button_label'] && $args['button_url'] ) : ?>
				<a class="btn" href="<?php echo esc_url( $args['button_url'] ); ?>"><?php echo esc_html( $args['button_label'] ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( $ws_has_media ) : ?>
		<div class="page-intro__media reveal">
			<?php if ( $args['rating'] ) : ?>
				<div class="review-summary">
					<div class="review-summary__stars" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: rating. */ __( 'میانگین امتیاز %s از ۵', 'woodshop' ), $args['rating'] ) ); ?>">
						<?php for ( $i = 0; $i < 5; $i++ ) : ?>
							<?php echo woodshop_icon( 'star', 'review-summary__star', 20 ); ?>
						<?php endfor; ?>
					</div>
					<strong class="review-summary__value"><?php echo esc_html( $args['rating'] ); ?></strong>
					<span class="review-summary__label"><?php echo esc_html( $args['rating_label'] ); ?></span>
					<?php if ( $args['count'] ) : ?>
						<span class="review-summary__count"><?php echo esc_html( $args['count'] . '+ ' . $args['count_label'] ); ?></span>
					<?php endif; ?>
				</div>
			<?php elseif ( $args['image'] ) : ?>
				<?php
				woodshop_image(
					$args['image'],
					array(
						'alt'    => $args['image_alt'],
						'width'  => 1000,
						'height' => 700,
						'class'  => 'page-intro__img',
						'sizes'  => '(max-width: 900px) 100vw, 45vw',
					)
				);
				?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
