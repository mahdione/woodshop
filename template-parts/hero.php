<?php
/**
 * Hero.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_slides = woodshop_option(
	'hero.slides',
	array(
		array( 'image' => WOODSHOP_URI . '/assets/img/hero-1.webp', 'alt' => 'Modern light kitchen interior with wooden details' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/hero-2.webp', 'alt' => 'Minimalist Scandinavian living room with wood and white interior' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/hero-3.webp', 'alt' => 'Wooden furniture in a warm kitchen' ),
	)
);

$ws_slides = array_values( array_filter( (array) $ws_slides ) );

$ws_before    = woodshop_option( 'hero.title_before', 'Expert' );
$ws_highlight = woodshop_option( 'hero.title_highlight', 'Carpentry' );
$ws_after     = woodshop_option( 'hero.title_after', 'Built to Last' );
$ws_text      = woodshop_option( 'hero.subtitle', 'Delivering exceptional carpentry and woodwork for projects that combine beauty and durability' );
$ws_btn       = woodshop_option( 'hero.button_label', 'Request Your Service' );
$ws_btn_url   = woodshop_option( 'hero.button_url', '#request' );
$ws_reviews   = woodshop_option( 'hero.reviews_value', '520' );
$ws_suffix    = woodshop_option( 'hero.reviews_suffix', '+' );
$ws_label     = woodshop_option( 'hero.reviews_label', 'Five-Star Reviews' );
$ws_avatars   = woodshop_option(
	'hero.avatars',
	array(
		array( 'image' => WOODSHOP_URI . '/assets/img/avatar-1.webp' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/avatar-2.webp' ),
		array( 'image' => WOODSHOP_URI . '/assets/img/avatar-3.webp' ),
	)
);
$ws_avatars = array_slice( array_values( array_filter( (array) $ws_avatars ) ), 0, 3 );

$ws_slide_count = count( $ws_slides );
?>
<section class="hero" aria-label="<?php esc_attr_e( 'Featured craftsmanship', 'woodshop' ); ?>" data-hero>
	<div class="hero__slides" aria-hidden="true">
		<?php foreach ( $ws_slides as $ws_index => $ws_slide ) : ?>
			<?php
			$ws_img = is_array( $ws_slide ) ? ( $ws_slide['image'] ?? '' ) : $ws_slide;
			$ws_alt = is_array( $ws_slide ) ? ( $ws_slide['alt'] ?? '' ) : '';
			$ws_src = woodshop_media_url( $ws_img, 'full' );
			$ws_first = 0 === $ws_index;

			if ( ! $ws_src ) {
				continue;
			}
			?>
			<div class="hero__slide<?php echo $ws_first ? ' is-active' : ''; ?>">
				<?php if ( is_numeric( $ws_img ) ) : ?>
					<?php
					woodshop_image(
						$ws_img,
						array(
							'alt'           => $ws_alt,
							'width'         => 1600,
							'height'        => 1066,
							'size'          => 'full',
							'loading'       => $ws_first ? 'eager' : 'lazy',
							'fetchpriority' => $ws_first ? 'high' : 'low',
							'decoding'      => $ws_first ? 'sync' : 'async',
							'sizes'         => '100vw',
							'class'         => 'hero__img',
						)
					);
					?>
				<?php else : ?>
					<?php
					$ws_srcset = WOODSHOP_URI . '/assets/img/hero-1.webp 960w, ' . WOODSHOP_URI . '/assets/img/hero-1@2x.webp 1600w';
					if ( false !== strpos( $ws_src, '/hero-2' ) ) {
						$ws_srcset = WOODSHOP_URI . '/assets/img/hero-2.webp 960w, ' . WOODSHOP_URI . '/assets/img/hero-2@2x.webp 1600w';
					} elseif ( false !== strpos( $ws_src, '/hero-3' ) ) {
						$ws_srcset = WOODSHOP_URI . '/assets/img/hero-3.webp 960w, ' . WOODSHOP_URI . '/assets/img/hero-3@2x.webp 1600w';
					}
					?>
					<img
						class="hero__img"
						src="<?php echo esc_url( $ws_src ); ?>"
						srcset="<?php echo esc_attr( $ws_srcset ); ?>"
						sizes="100vw"
						width="1600"
						height="1066"
						alt="<?php echo esc_attr( $ws_alt ); ?>"
						loading="<?php echo $ws_first ? 'eager' : 'lazy'; ?>"
						decoding="<?php echo $ws_first ? 'sync' : 'async'; ?>"
						fetchpriority="<?php echo $ws_first ? 'high' : 'low'; ?>"
					>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<span class="hero__overlay" aria-hidden="true"></span>

	<div class="wrap hero__wrap">
		<div class="hero__content">
			<h1 class="hero__title">
				<span><?php echo esc_html( $ws_before ); ?></span>
				<span class="highlight"><?php echo esc_html( $ws_highlight ); ?><?php echo woodshop_squiggle(); ?></span>
				<span><?php echo esc_html( $ws_after ); ?></span>
			</h1>

			<p class="hero__text"><?php echo esc_html( $ws_text ); ?></p>

			<span class="hero__divider" aria-hidden="true"></span>

			<div class="hero__actions">
				<a class="btn hero__btn" href="<?php echo esc_url( $ws_btn_url ); ?>"><?php echo esc_html( $ws_btn ); ?></a>

				<div class="hero__reviews">
					<div class="hero__avatars" aria-hidden="true">
						<?php foreach ( $ws_avatars as $ws_avatar ) : ?>
							<?php
							$ws_avatar_img = is_array( $ws_avatar ) ? ( $ws_avatar['image'] ?? '' ) : $ws_avatar;
							woodshop_image(
								$ws_avatar_img,
								array(
									'alt'    => '',
									'width'  => 56,
									'height' => 56,
									'class'  => 'hero__avatar',
									'sizes'  => '56px',
								)
							);
							?>
						<?php endforeach; ?>
					</div>
					<p class="hero__reviews-text">
						<strong><?php echo esc_html( $ws_reviews . $ws_suffix ); ?></strong>
						<span><?php echo esc_html( $ws_label ); ?></span>
					</p>
				</div>
			</div>
		</div>
	</div>

	<?php if ( $ws_slide_count > 1 ) : ?>
		<div class="hero__controls">
			<div class="hero__dots" role="tablist" aria-label="<?php esc_attr_e( 'Hero slides', 'woodshop' ); ?>">
				<?php for ( $ws_i = 0; $ws_i < $ws_slide_count; $ws_i++ ) : ?>
					<button type="button" class="hero__dot<?php echo 0 === $ws_i ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo 0 === $ws_i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number. */ __( 'Show slide %d', 'woodshop' ), $ws_i + 1 ) ); ?>" data-slide="<?php echo esc_attr( (string) $ws_i ); ?>"></button>
				<?php endfor; ?>
			</div>
			<button type="button" class="hero__pause" aria-label="<?php esc_attr_e( 'Pause slideshow', 'woodshop' ); ?>" aria-pressed="false">
				<svg class="hero__pause-icon" width="12" height="14" viewBox="0 0 12 14" aria-hidden="true"><rect width="4" height="14" rx="1"></rect><rect x="8" width="4" height="14" rx="1"></rect></svg>
				<svg class="hero__play-icon" width="12" height="14" viewBox="0 0 12 14" aria-hidden="true"><path d="M1 1.5v11a1 1 0 0 0 1.5.86l9-5.5a1 1 0 0 0 0-1.72l-9-5.5A1 1 0 0 0 1 1.5z"></path></svg>
			</button>
		</div>
	<?php endif; ?>
</section>
