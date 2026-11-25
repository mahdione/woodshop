<?php
/**
 * About page.
 *
 * @package Woodshop
 */

defined( 'ABSPATH' ) || exit;

$ws_stats = woodshop_option( 'page_about.stats', array() );
$ws_values = woodshop_option( 'page_about.values', array() );
$ws_team   = woodshop_option( 'page_about.team', array() );

get_header();
?>
<div class="ws-page page-about">
	<div class="wrap">
		<?php woodshop_breadcrumb(); ?>
	</div>

	<?php
	get_template_part(
		'template-parts/page-intro',
		null,
		array(
			'eyebrow'   => woodshop_option( 'page_about.eyebrow', 'درباره ما' ),
			'title'     => woodshop_option( 'page_about.title', 'درباره ما' ),
			'intro'     => woodshop_option( 'page_about.intro', 'بیش از دو دهه است که چوب را به قطعاتی ماندگار و فضاهایی گرم تبدیل می‌کنیم.' ),
			'image'     => woodshop_option( 'page_about.image', '' ),
			'image_alt' => __( 'تیم چوب‌کاری در کارگاه', 'woodshop' ),
		)
	);
	?>

	<?php get_template_part( 'template-parts/features' ); ?>

	<section class="section about-story">
		<div class="wrap about-story__grid">
			<div class="about-story__content reveal">
				<?php woodshop_eyebrow( woodshop_option( 'page_about.story_eyebrow', 'از علاقه تا کمال' ) ); ?>
				<h2 class="section-title"><?php echo esc_html( woodshop_option( 'page_about.story_title', 'جایی که هنر دست با نوآوری دیدار می‌کند' ) ); ?></h2>
				<p class="section-text"><?php echo esc_html( woodshop_option( 'page_about.story_text', '' ) ); ?></p>

				<?php if ( $ws_stats ) : ?>
					<ul class="page-stats">
						<?php foreach ( $ws_stats as $ws_stat ) : ?>
							<li>
								<strong><?php echo esc_html( $ws_stat['value'] ?? '' ); ?><span><?php echo esc_html( $ws_stat['suffix'] ?? '' ); ?></span></strong>
								<em><?php echo esc_html( $ws_stat['label'] ?? '' ); ?></em>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="about-story__media reveal">
				<?php
				woodshop_image(
					woodshop_option( 'page_about.story_image', '' ),
					array(
						'alt'    => '',
						'width'  => 1000,
						'height' => 750,
						'class'  => 'about-story__img',
						'sizes'  => '(max-width: 900px) 100vw, 50vw',
					)
				);
				?>
			</div>
		</div>
	</section>

	<?php if ( $ws_values ) : ?>
		<section class="section section--alt values-section">
			<div class="wrap">
				<ul class="values-grid">
					<?php foreach ( $ws_values as $ws_value ) : ?>
						<li class="value-card reveal">
							<span class="value-card__icon"><?php echo woodshop_icon( $ws_value['icon'] ?? 'medal', '', 26 ); ?></span>
							<h3 class="value-card__title"><?php echo esc_html( $ws_value['title'] ?? '' ); ?></h3>
							<p class="value-card__text"><?php echo esc_html( $ws_value['text'] ?? '' ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $ws_team ) : ?>
		<section class="section team-section">
			<div class="wrap">
				<div class="section-head section-head--center reveal">
					<?php woodshop_eyebrow( woodshop_option( 'page_about.team_eyebrow', 'تیم ما' ) ); ?>
					<h2 class="section-title"><?php echo esc_html( woodshop_option( 'page_about.team_title', 'با متخصصان پشت هر قطعه آشنا شوید' ) ); ?></h2>
				</div>

				<ul class="team-grid">
					<?php foreach ( $ws_team as $ws_member ) : ?>
						<li class="team-card reveal">
							<div class="team-card__media">
								<?php
								woodshop_image(
									$ws_member['image'] ?? '',
									array(
										'alt'    => $ws_member['name'] ?? '',
										'width'  => 600,
										'height' => 700,
										'class'  => 'team-card__img',
										'sizes'  => '(max-width: 600px) 100vw, (max-width: 1024px) 50vw, 30vw',
									)
								);
								?>
							</div>
							<div class="team-card__body">
								<h3 class="team-card__name"><?php echo esc_html( $ws_member['name'] ?? '' ); ?></h3>
								<span class="team-card__role"><?php echo esc_html( $ws_member['role'] ?? '' ); ?></span>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/guarantee' ); ?>
	<?php get_template_part( 'template-parts/testimonials' ); ?>
	<?php get_template_part( 'template-parts/cta' ); ?>
</div>
<?php
get_footer();
