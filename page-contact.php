<?php
/**
 * Contact page.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_cards = woodshop_option( 'page_contact.cards', array() );
$ws_faq   = woodshop_option( 'page_contact.faq', array() );

get_header();
?>
<div class="ws-page page-contact">
	<div class="wrap">
		<?php woodshop_breadcrumb(); ?>
	</div>

	<?php
	get_template_part(
		'template-parts/page-intro',
		null,
		array(
			'eyebrow'   => woodshop_option( 'page_contact.eyebrow', 'تماس با ما' ),
			'title'     => woodshop_option( 'page_contact.title', 'دوست داریم از شما بشنویم' ),
			'intro'     => woodshop_option( 'page_contact.intro', '' ),
			'image'     => woodshop_option( 'page_contact.image', '' ),
			'image_alt' => __( 'کارگاه چوب‌کاری', 'woodshop' ),
		)
	);
	?>

	<?php if ( $ws_cards ) : ?>
		<section class="contact-cards-section">
			<div class="wrap">
				<ul class="contact-cards">
					<?php foreach ( $ws_cards as $ws_card ) : ?>
						<li class="contact-card reveal">
							<span class="contact-card__icon"><?php echo woodshop_icon( $ws_card['icon'] ?? 'pin', '', 22 ); ?></span>
							<span class="contact-card__label"><?php echo esc_html( $ws_card['label'] ?? '' ); ?></span>
							<?php if ( ! empty( $ws_card['url'] ) ) : ?>
								<a class="contact-card__value" href="<?php echo esc_url( $ws_card['url'] ); ?>"><?php echo esc_html( $ws_card['value'] ?? '' ); ?></a>
							<?php else : ?>
								<span class="contact-card__value"><?php echo esc_html( $ws_card['value'] ?? '' ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<section class="section contact-main">
		<div class="wrap contact-main__grid">
			<div class="contact-form-panel reveal" id="request">
				<?php woodshop_eyebrow( woodshop_option( 'page_contact.form_eyebrow', 'درخواست مشاوره' ) ); ?>
				<h2 class="section-title"><?php echo esc_html( woodshop_option( 'page_contact.form_title', 'پیام خود را برای ما بفرستید' ) ); ?></h2>
				<p class="section-text"><?php esc_html_e( 'فرم زیر را پر کنید؛ برای هماهنگی و پاسخ به سؤال‌های شما با شما تماس می‌گیریم.', 'woodshop' ); ?></p>
				<?php echo do_shortcode( '[woodshop_request_form mode="contact" show_title="0" button="ارسال پیام"]' ); ?>
			</div>

			<div class="contact-info-panel reveal">
				<div class="contact-info-panel__image">
					<?php
					woodshop_image(
						woodshop_option( 'page_contact.info_image', woodshop_option( 'page_contact.image', '' ) ),
						array(
							'alt'    => '',
							'width'  => 1000,
							'height' => 750,
							'class'  => 'contact-info-panel__img',
							'sizes'  => '(max-width: 900px) 100vw, 45vw',
						)
					);
					?>
				</div>
				<div class="contact-info-panel__body">
					<h3><?php esc_html_e( 'کارگاه ما', 'woodshop' ); ?></h3>
					<p><?php esc_html_e( 'برای دیدن نمونه‌کارها و گفت‌وگو درباره پروژه، می‌توانید با هماهنگی قبلی به کارگاه بیایید. قهوه مهمان ما هستید.', 'woodshop' ); ?></p>
					<ul class="contact-info-panel__list">
						<li><?php echo woodshop_icon( 'clock', '', 17 ); ?><span><?php echo esc_html( woodshop_option( 'brand.hours', '' ) ); ?></span></li>
						<li><?php echo woodshop_icon( 'phone', '', 17 ); ?><a href="<?php echo esc_url( woodshop_option( 'brand.phone_url', 'tel:' ) ); ?>"><?php echo esc_html( woodshop_option( 'brand.phone', '' ) ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $ws_faq ) : ?>
		<section class="section section--alt faq-section">
			<div class="wrap">
				<div class="section-head section-head--center reveal">
					<?php woodshop_eyebrow( woodshop_option( 'page_contact.faq_eyebrow', 'سوالات متداول' ) ); ?>
					<h2 class="section-title"><?php echo esc_html( woodshop_option( 'page_contact.faq_title', 'پاسخ سوال‌های رایج' ) ); ?></h2>
				</div>

				<div class="faq-list">
					<?php foreach ( $ws_faq as $ws_item ) : ?>
						<details class="faq-item reveal">
							<summary class="faq-item__question">
								<span><?php echo esc_html( $ws_item['question'] ?? '' ); ?></span>
								<?php echo woodshop_icon( 'chevron-down', 'faq-item__icon', 15 ); ?>
							</summary>
							<div class="faq-item__answer"><?php echo esc_html( $ws_item['answer'] ?? '' ); ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/guarantee' ); ?>
</div>
<?php
get_footer();
