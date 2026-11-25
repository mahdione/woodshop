<?php
/**
 * Services page.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_stats   = woodshop_option( 'page_services.stats', array() );
$ws_process = woodshop_option( 'page_services.process', array() );
$ws_ids     = array_filter( array_map( 'absint', (array) woodshop_option( 'services.category_ids', array() ) ) );
$ws_terms   = function_exists( 'woodshop_product_categories' ) ? woodshop_product_categories( $ws_ids, 6 ) : array();
$ws_manual  = woodshop_option( 'services.items', array() );
$ws_cards   = array();

if ( $ws_terms ) {
	foreach ( $ws_terms as $ws_index => $ws_term ) {
		$ws_image = woodshop_term_image( $ws_term, 'ws-card' );
		$ws_cards[] = array(
			'title' => $ws_term->name,
			'text'  => wp_trim_words( wp_strip_all_tags( term_description( $ws_term ) ), 18 ),
			'image' => $ws_image ? $ws_image : ( $ws_manual[ $ws_index ]['image'] ?? '' ),
			'url'   => get_term_link( $ws_term ),
		);
	}
} else {
	foreach ( $ws_manual as $ws_item ) {
		$ws_cards[] = array(
			'title' => $ws_item['title'] ?? '',
			'text'  => $ws_item['text'] ?? '',
			'image' => $ws_item['image'] ?? '',
			'url'   => $ws_item['url'] ?? '#',
		);
	}
}

get_header();
?>
<div class="ws-page page-services">
	<div class="wrap">
		<?php woodshop_breadcrumb(); ?>
	</div>

	<?php
	get_template_part(
		'template-parts/page-intro',
		null,
		array(
			'eyebrow'   => woodshop_option( 'page_services.eyebrow', 'خدمات ما' ),
			'title'     => woodshop_option( 'page_services.title', 'خدمات چوب‌کاری متناسب با فضای شما' ),
			'intro'     => woodshop_option( 'page_services.intro', '' ),
			'image'     => woodshop_option( 'page_services.image', '' ),
			'image_alt' => __( 'نمونه‌ای از اجرای چوب‌کاری', 'woodshop' ),
		)
	);
	?>

	<?php if ( $ws_stats ) : ?>
		<section class="page-stats-strip" aria-label="<?php esc_attr_e( 'آمار ما', 'woodshop' ); ?>">
			<div class="wrap">
				<ul class="page-stats page-stats--inline">
					<?php foreach ( $ws_stats as $ws_stat ) : ?>
						<li>
							<strong><?php echo esc_html( $ws_stat['value'] ?? '' ); ?><span><?php echo esc_html( $ws_stat['suffix'] ?? '' ); ?></span></strong>
							<em><?php echo esc_html( $ws_stat['label'] ?? '' ); ?></em>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<section class="section services-page-grid">
		<div class="wrap">
			<div class="section-head reveal">
				<?php woodshop_eyebrow( 'تخصص‌های ما' ); ?>
				<h2 class="section-title"><?php esc_html_e( 'خدمات تخصصی چوب‌کاری', 'woodshop' ); ?></h2>
				<p class="section-text"><?php esc_html_e( 'هر خدمت با تیمی از متخصصان، متریال مرغوب و استاندارد اجرایی یکسان ارائه می‌شود. برای دیدن محصولات هر دسته، روی آن کلیک کنید.', 'woodshop' ); ?></p>
			</div>

			<ul class="services__grid">
				<?php foreach ( $ws_cards as $ws_card ) : ?>
					<li class="service-card reveal">
						<a class="service-card__media" href="<?php echo esc_url( $ws_card['url'] ); ?>" tabindex="-1" aria-hidden="true">
							<?php
							woodshop_image(
								$ws_card['image'],
								array(
									'alt'    => '',
									'width'  => 800,
									'height' => 533,
									'class'  => 'service-card__img',
									'sizes'  => '(max-width: 600px) 100vw, (max-width: 1024px) 50vw, 430px',
								)
							);
							?>
						</a>
						<div class="service-card__body">
							<h3 class="service-card__title"><a href="<?php echo esc_url( $ws_card['url'] ); ?>"><?php echo esc_html( $ws_card['title'] ); ?></a></h3>
							<p class="service-card__text"><?php echo esc_html( $ws_card['text'] ); ?></p>
							<a class="btn btn--sm service-card__btn" href="<?php echo esc_url( $ws_card['url'] ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'woodshop' ); ?></a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php get_template_part( 'template-parts/guarantee' ); ?>

	<?php if ( $ws_process ) : ?>
		<section class="section process-section">
			<div class="wrap">
				<div class="section-head section-head--center reveal">
					<?php woodshop_eyebrow( woodshop_option( 'page_services.process_eyebrow', 'فرآیند کار' ) ); ?>
					<h2 class="section-title"><?php echo esc_html( woodshop_option( 'page_services.process_title', 'از ایده تا نصب، در سه قدم روشن' ) ); ?></h2>
				</div>

				<ol class="process-grid">
					<?php foreach ( $ws_process as $ws_index => $ws_step ) : ?>
						<li class="process-step reveal">
							<span class="process-step__number"><?php echo esc_html( woodshop_persian_digits( str_pad( (string) ( $ws_index + 1 ), 2, '0', STR_PAD_LEFT ) ) ); ?></span>
							<h3 class="process-step__title"><?php echo esc_html( $ws_step['title'] ?? '' ); ?></h3>
							<p class="process-step__text"><?php echo esc_html( $ws_step['text'] ?? '' ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/testimonials' ); ?>
	<?php get_template_part( 'template-parts/areas' ); ?>
	<?php get_template_part( 'template-parts/cta' ); ?>
</div>
<?php
get_footer();
