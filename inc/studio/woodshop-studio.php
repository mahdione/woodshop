<?php
/**
 * Plugin Name: Wood Shop Studio
 * Plugin URI: https://github.com/mahdione/woodshop-studio
 * Description: Homepage builder and theme customizer for the Wood Shop WordPress theme. Manage the hero slider, sections, products, menus, footer and request form from a single admin screen.
 * Version: 1.1.6
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Mahdi Karimi
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/mahdione/woodshop-studio
 * Text Domain: woodshop-studio
 *
 * @package WoodshopStudio
 */

defined( 'ABSPATH' ) || exit;

define( 'WOODSHOP_STUDIO_VERSION', '1.1.6' );
define( 'WOODSHOP_STUDIO_FILE', __FILE__ );
define( 'WOODSHOP_STUDIO_BASENAME', plugin_basename( __FILE__ ) );
define( 'WOODSHOP_STUDIO_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOODSHOP_STUDIO_URL', plugin_dir_url( __FILE__ ) );
define( 'WOODSHOP_STUDIO_OPTION', 'woodshop_studio' );

require_once WOODSHOP_STUDIO_DIR . 'includes/class-admin.php';
require_once WOODSHOP_STUDIO_DIR . 'includes/class-leads.php';
require_once WOODSHOP_STUDIO_DIR . 'includes/class-portfolio.php';

if ( ! defined( 'WOODSHOP_STUDIO_EMBEDDED' ) ) {
	require_once WOODSHOP_STUDIO_DIR . 'includes/class-github-updater.php';
}

/**
 * URL of a bundled theme image.
 *
 * @param string $file File name.
 * @return string
 */
function woodshop_studio_asset( $file ) {
	return get_template_directory_uri() . '/assets/img/' . ltrim( $file, '/' );
}

/**
 * Does an array behave like a list?
 *
 * @param array<mixed> $value Array.
 * @return bool
 */
function woodshop_studio_is_list( $value ) {
	if ( ! is_array( $value ) ) {
		return false;
	}

	if ( array() === $value ) {
		return true;
	}

	$keys = array_keys( $value );

	return $keys === range( 0, count( $value ) - 1 );
}

/**
 * Recursively merge saved settings over defaults.
 *
 * List arrays (repeaters) are replaced wholesale so an intentionally empty
 * repeater stays empty.
 *
 * @param mixed $defaults Default value.
 * @param mixed $saved    Saved value.
 * @return mixed
 */
function woodshop_studio_merge( $defaults, $saved ) {
	if ( ! is_array( $defaults ) || ! is_array( $saved ) ) {
		return $saved;
	}

	if ( woodshop_studio_is_list( $defaults ) ) {
		return $saved;
	}

	$merged = $defaults;

	foreach ( $saved as $key => $value ) {
		if ( array_key_exists( $key, $merged ) && is_array( $merged[ $key ] ) && is_array( $value ) ) {
			$merged[ $key ] = woodshop_studio_merge( $merged[ $key ], $value );
		} else {
			$merged[ $key ] = $value;
		}
	}

	return $merged;
}

/**
 * All homepage defaults.
 *
 * @return array<string, mixed>
 */
function woodshop_studio_defaults() {
	$img = 'woodshop_studio_asset';

	$defaults = array(
		'brand'        => array(
			'phone'       => '(۱۲۳) ۴۵۶ ۷۸۹۰',
			'phone_url'   => 'tel:+11234567890',
			'email'       => 'info@example.com',
			'address'     => 'خیابان تیمبرلاین، پلاک ۱۲۳۴، اورلاندو، فلوریدا',
			'hours'       => 'دوشنبه تا جمعه: ۹ صبح تا ۸ شب',
			'quote_label' => 'دریافت استعلام رایگان',
			'quote_url'   => '/#request',
		),
		'styles'       => array(
			'bg'         => '#eae5e0',
			'bg_alt'     => '#f4f0eb',
			'ink'        => '#1b110e',
			'body'       => '#4a4541',
			'muted'      => '#6b655e',
			'brown'      => '#75604b',
			'brown_deep' => '#6e5a45',
			'accent'     => '#996a38',
			'sand'       => '#dfd4c9',
			'sand_2'     => '#c8b5a5',
			'white'      => '#ffffff',
			'dark'       => '#140d09',
			'success'    => '#5c6d4e',
			'error'      => '#a3453b',
		),
		'header'       => array(
			'menu_id'   => 0,
			'show_cart' => 1,
		),
		'hero'         => array(
			'enabled'         => 1,
			'title_before'    => 'چوب‌کاری',
			'title_highlight' => 'حرفه‌ای',
			'title_after'     => 'برای ماندگاری',
			'subtitle'        => 'خدمات نجاری و چوب‌کاری بی‌نظیر برای پروژه‌هایی که زیبایی و دوام را در کنار هم می‌آورند.',
			'button_label'    => 'درخواست خدمات',
			'button_url'      => '#request',
			'reviews_value'   => '۵۲۰',
			'reviews_suffix'  => '+',
			'reviews_label'   => 'بازخورد پنج‌ستاره',
			'slides'          => array(
				array( 'image' => $img( 'hero-1.webp' ), 'alt' => 'آشپزخانه روشن و مدرن با جزئیات چوبی' ),
				array( 'image' => $img( 'hero-2.webp' ), 'alt' => 'نشیمن مینیمال اسکاندیناوی با ترکیب چوب و سفید' ),
				array( 'image' => $img( 'hero-3.webp' ), 'alt' => 'مبلمان چوبی در آشپزخانه‌ای گرم' ),
			),
			'avatars'         => array(
				array( 'image' => $img( 'avatar-1.webp' ) ),
				array( 'image' => $img( 'avatar-2.webp' ) ),
				array( 'image' => $img( 'avatar-3.webp' ) ),
			),
		),
		'features'     => array(
			'enabled' => 1,
			'items'   => array(
				array( 'icon' => 'award', 'title' => 'کیفیت ممتاز', 'text' => 'چوب و پرداخت‌های درجه‌یک برای دوام و زیبایی ماندگار.' ),
				array( 'icon' => 'tools', 'title' => 'مهارت حرفه‌ای', 'text' => 'متخصصان مجرب با اجرای دقیق و بی‌نقص چوب‌کاری.' ),
				array( 'icon' => 'medal', 'title' => 'ضمانت و پشتیبانی', 'text' => 'پشتیبانی اختصاصی و ضمانت، برای آرامش خاطر همیشگی.' ),
				array( 'icon' => 'recycle', 'title' => 'رویکرد دوستدار محیط زیست', 'text' => 'مواد با منبع پایدار و فرآیندهای سازگار با محیط زیست.' ),
			),
		),
		'about'        => array(
			'enabled'      => 1,
			'eyebrow'      => 'چرا ما؟',
			'title'        => 'اعتماد را با هنر چوب‌کاری می‌سازیم',
			'text'         => 'تعهد ما به کیفیت در هر تخته، اتصال و پرداخت دیده می‌شود؛ تا فضای شما فقط کاربردی نباشد، بلکه هویت و زیبایی خودش را داشته باشد. از مبلمان سفارشی تا بازسازی‌های بزرگ، تکنیک‌های اصیل نجاری را با نوآوری‌های امروز ترکیب می‌کنیم تا ایده شما به واقعیت برسد.',
			'image'        => $img( 'bg-why.webp' ),
			'points'       => array(
				array( 'icon' => 'tools', 'text' => 'راهکارهای اختصاصی، هماهنگ با سبک و نیاز شما.' ),
				array( 'icon' => 'layers', 'text' => 'فرآیندی یکپارچه از مشاوره تا تحویل و نصب.' ),
				array( 'icon' => 'swatchbook', 'text' => 'طراحی‌های بادوام، کاربردی و ظریف.' ),
			),
			'button_label' => 'بیشتر درباره ما',
			'button_url'   => '#',
			'stat_value'   => '۸۵۰',
			'stat_suffix'  => '+',
			'stat_label'   => 'مشتری راضی',
		),
		'services'     => array(
			'enabled'      => 1,
			'eyebrow'      => 'خدمات ما',
			'title'        => 'کمال را در هر گوشه می‌سازیم',
			'text'         => 'فضاهای شما را با نجاری حرفه‌ای و راهکارهای چوبی سفارشی، متناسب با سبک زندگی شما دگرگون می‌کنیم.',
			'source'       => 'categories',
			'category_ids' => array(),
			'items'        => array(
				array( 'title' => 'طراحی مبلمان سفارشی', 'text' => 'ساخت قطعات چوبی اختصاصی، متناسب با سبک و فضای شما.', 'image' => $img( 'service-furniture.webp' ), 'url' => '#' ),
				array( 'title' => 'کابینت و فضای ذخیره‌سازی', 'text' => 'کابینت، کمد، قفسه و فضای ذخیره‌سازی سفارشی برای خانه‌ای منظم و زیبا.', 'image' => $img( 'service-cabinetry.webp' ), 'url' => '#' ),
				array( 'title' => 'کف‌پوش چوبی', 'text' => 'تأمین و نصب کف‌پوش چوبی جامد و مهندسی با دقت و دوام بالا.', 'image' => $img( 'service-flooring.webp' ), 'url' => '#' ),
				array( 'title' => 'مبلمان توکار', 'text' => 'قفسه‌ها و واحدهای توکار که هماهنگ با معماری داخلی شما ساخته می‌شوند.', 'image' => $img( 'service-builtin.webp' ), 'url' => '#' ),
				array( 'title' => 'نجاری فضای باز', 'text' => 'ساخت تراس، آلاچیق، حفاظ و مبلمان باغی مقاوم در برابر شرایط جوی.', 'image' => $img( 'service-outdoor.webp' ), 'url' => '#' ),
				array( 'title' => 'نجاری بازسازی منزل', 'text' => 'پله، پنل دیواری و چوب‌کاری معماری برای بازسازی خانه شما.', 'image' => $img( 'service-renovation.webp' ), 'url' => '#' ),
			),
			'button_label' => 'دریافت استعلام رایگان',
			'button_url'   => '#request',
		),
		'products'     => array(
			'enabled'       => 1,
			'eyebrow'       => 'از کارگاه ما',
			'title'         => 'قطعه‌هایی که آماده سفارشاند',
			'text'          => 'مبلمان چوب جامد و کارهای سفارشی، ساخته‌شده در کارگاه ما و تحویل به درِ خانه شما.',
			'limit'         => 4,
			'featured_only' => 0,
			'category_id'   => 0,
			'button_label'  => 'مشاهده همه محصولات',
		),
		'showcase'     => array(
			'enabled' => 1,
			'items'   => array(
				array(
					'layout' => 'split',
					'size'   => 'large',
					'title'  => 'بازگرداندن زیبایی، حفظ تاریخ',
					'text'   => 'جان دوباره به مبلمان عتیقه و چوب‌کاری معماری، با مرمت‌های دقیقی که اصالت ساخت اولیه را حفظ می‌کند.',
					'image'  => $img( 'collage-a.webp' ),
					'url'    => '#',
				),
				array(
					'layout' => 'cover',
					'size'   => 'small',
					'title'  => 'نجاری ماندگار برای شما',
					'text'   => '',
					'image'  => $img( 'collage-b.webp' ),
					'url'    => '#',
				),
				array(
					'layout' => 'cover',
					'size'   => 'small',
					'title'  => 'تعالی در هنر چوب‌کاری',
					'text'   => '',
					'image'  => $img( 'collage-c.webp' ),
					'url'    => '#',
				),
				array(
					'layout' => 'split',
					'size'   => 'large',
					'title'  => 'کف‌هایی برای ماندگاری',
					'text'   => 'کف‌پوش چوبی با تخصص نجاری نصب، سنباده و پرداخت شده است؛ برای زیبایی ماندگار و مقاومت روزمره.',
					'image'  => $img( 'collage-d.webp' ),
					'url'    => '#',
				),
			),
		),
		'guarantee'    => array(
			'enabled'    => 1,
			'eyebrow'    => 'تضمین خدمات',
			'title'      => 'کیفیت تضمین‌شده و پشتیبانی اختصاصی',
			'text'       => 'ما پشت کیفیت کارمان می‌ایستیم. هر پروژه با مواد اولیه مرغوب و دقت بالا ساخته می‌شود. ما کار را تحویل نمی‌دهیم و رها نمی‌کنیم؛ پشتیبانی پس از نصب را هم ادامه می‌دهیم.',
			'points'     => array(
				array( 'text' => 'ضمانت ۱۲ ماهه اجرای کار.' ),
				array( 'text' => 'بازدید رایگان پس از نصب.' ),
				array( 'text' => 'تضمین ۱۰۰٪ کیفیت مواد.' ),
				array( 'text' => 'پشتیبانی پاسخگو پس از تحویل.' ),
				array( 'text' => 'رضایت شما، اولویت اول ما.' ),
			),
			'image'      => $img( 'badge.png' ),
			'background' => $img( 'bg-guarantee.webp' ),
		),
		'gallery'      => array(
			'enabled' => 1,
			'eyebrow' => 'گالری پروژه‌های منتخب',
			'title'   => 'ساخته‌های چوبی ما در عمل',
			'text'    => 'نگاهی به پروژه‌های اخیر نجاری ما؛ جایی که دقت و مهارت به فضاها جان می‌دهد.',
			'items'   => array(
				array( 'image' => $img( 'gallery-1.webp' ), 'title' => 'صندلی‌های چوبی رستوران', 'caption' => 'مهمان‌نوازی', 'url' => '#' ),
				array( 'image' => $img( 'gallery-2.webp' ), 'title' => 'قفسه‌های کلاسیک', 'caption' => 'مسکونی', 'url' => '#' ),
				array( 'image' => $img( 'gallery-3.webp' ), 'title' => 'فضای جلسات', 'caption' => 'تجاری', 'url' => '#' ),
				array( 'image' => $img( 'gallery-4.webp' ), 'title' => 'کابینت پیشخوان درمانی', 'caption' => 'درمانی', 'url' => '#' ),
			),
		),
		'areas'        => array(
			'enabled'      => 1,
			'eyebrow'      => 'مناطق خدمات',
			'title'        => 'خدمات نجاری تخصصی نزدیک شما',
			'text'         => 'ما به مشتریان مسکونی و تجاری در چند منطقه خدمات ارائه می‌دهیم؛ با چوب‌کاری سفارشی و اجرای دقیق.',
			'items'        => array(
				array( 'name' => 'فلوریدا', 'url' => '#' ),
				array( 'name' => 'جورجیا', 'url' => '#' ),
				array( 'name' => 'میسیسیپی', 'url' => '#' ),
				array( 'name' => 'کارولینای شمالی', 'url' => '#' ),
				array( 'name' => 'آلاباما', 'url' => '#' ),
				array( 'name' => 'لوئیزیانا', 'url' => '#' ),
				array( 'name' => 'کارولینای جنوبی', 'url' => '#' ),
				array( 'name' => 'تنسی', 'url' => '#' ),
			),
			'button_label' => 'مشاهده همه مناطق',
			'button_url'   => '#',
			'image'        => $img( 'areas-team.webp' ),
		),
		'sectors'      => array(
			'enabled'    => 1,
			'eyebrow'    => 'بخش‌های خدمات',
			'title'      => 'نجاری متناسب با هر بخش',
			'text'       => 'خدمات ما با محیط شما سازگار می‌شود؛ مسکونی، تجاری یا صنعتی، با چوب‌کاری سفارشی که تأثیر می‌گذارد.',
			'background' => $img( 'bg-sectors.webp' ),
			'items'      => array(
				array( 'icon' => 'home', 'title' => 'نجاری مسکونی', 'text' => 'ارتقای خانه‌ها با چوب‌کاری سفارشی و روزمره.' ),
				array( 'icon' => 'building', 'title' => 'نجاری تجاری', 'text' => 'بهینه‌سازی فضاهای کاری با اجرای دقیق و کارآمد.' ),
				array( 'icon' => 'medkit', 'title' => 'نجاری درمانی', 'text' => 'طراحی فضاهای ایمن و آرام با نصب چوبی دقیق.' ),
				array( 'icon' => 'school', 'title' => 'نجاری سازمانی', 'text' => 'پشتیبانی از سازمان‌ها با نصب چوبی قابل اعتماد.' ),
				array( 'icon' => 'hotel', 'title' => 'نجاری هتلداری', 'text' => 'خلق فضاهای پذیرایی دلنشین با چوب‌کاری ظریف و بادوام.' ),
				array( 'icon' => 'basket', 'title' => 'نجاری خرده‌فروشی', 'text' => 'دگرگونی فروشگاه‌ها با چوب‌کاری سفارشی نوآورانه.' ),
			),
		),
		'logos'        => array(
			'enabled' => 1,
			'items'   => array(
				array( 'image' => $img( 'partner-1.png' ), 'alt' => 'Ruud Stanley', 'url' => '' ),
				array( 'image' => $img( 'partner-2.png' ), 'alt' => 'Andreas Bike', 'url' => '' ),
				array( 'image' => $img( 'partner-3.png' ), 'alt' => 'Blake Star', 'url' => '' ),
				array( 'image' => $img( 'partner-4.png' ), 'alt' => 'Nick & Joan', 'url' => '' ),
				array( 'image' => $img( 'partner-5.png' ), 'alt' => 'Skylight', 'url' => '' ),
				array( 'image' => $img( 'partner-6.png' ), 'alt' => 'Hudston Smith', 'url' => '' ),
			),
		),
		'financing'    => array(
			'enabled'      => 1,
			'eyebrow'      => 'تأمین مالی',
			'title'        => 'تأمین مالی انعطاف‌پذیر برای پروژه‌های چوب‌کاری',
			'text'         => 'خانه‌تان را بدون فشار مالی به‌روز کنید. گزینه‌های تأمین مالی انعطاف‌پذیر، پروژه رویایی‌تان را در دسترس‌پذیرتر می‌کند.',
			'points'       => array(
				array( 'text' => 'فرآیند درخواست آسان و تأیید سریع.' ),
				array( 'text' => 'نرخ سود کم و طرح‌های پرداخت انعطاف‌پذیر.' ),
				array( 'text' => 'امکان تأمین مالی بازسازی، مبلمان سفارشی و تعمیرات.' ),
				array( 'text' => 'بدون هزینه پنهان یا هزینه غیرمنتظره.' ),
			),
			'image'        => $img( 'financing.webp' ),
			'button_label' => 'بیشتر درباره تأمین مالی',
			'button_url'   => '#',
		),
		'testimonials' => array(
			'enabled'    => 1,
			'eyebrow'    => 'نظرات مشتریان',
			'title'      => 'مشتریان درباره چوب‌کاری ما چه می‌گویند',
			'text'       => 'کیفیتی که خودش حرف می‌زند؛ مورد اعتماد خانه‌ها و کسب‌وکارها.',
			'background' => $img( 'bg-reviews.webp' ),
			'items'      => array(
				array(
					'text'   => 'کمتر تیمی را می‌بینید که با این حد از دقت و تعهد کار کند. قفسه سفارشی که طراحی و نصب کردند، فضای نشیمن ما را کامل دگرگون کرد و ترکیب بی‌نظیری از زیبایی و کاربردی بودن ساخت. ارتباط با تیم آسان بود، همیشه سر وقت حاضر شدند و همه جزئیات را بی‌نقص انجام دادند. قطعاً دوباره با آن‌ها کار می‌کنم.',
					'name'   => 'مایکل کارتر',
					'meta'   => 'آتلانتا، جورجیا',
					'image'  => $img( 'avatar-1.webp' ),
					'rating' => 5,
				),
				array(
					'text'   => 'سطح مهارت و توجهی که نیل اند نات در کارش دارد، بی‌نظیر است. یک واحد سرگرمی سفارشی برای خانه ما ساختند که دقیقاً همان قطعه گمشده فضا بود. هر برش، اتصال و پرداخت با وسواس انجام شد و تیم در تمام پروژه حرفه‌ای و محترم بود. کارشان خودش گویاست؛ با اطمینان توصیه‌شان می‌کنم.',
					'name'   => 'سارا تامپسون',
					'meta'   => 'تامپا، فلوریدا',
					'image'  => $img( 'avatar-2.webp' ),
					'rating' => 5,
				),
				array(
					'text'   => 'انتخاب نیل اند نات بهترین تصمیم برای بازسازی خانه ما بود. تیم بسیار ماهر بود، به همه جزئیات توجه داشت و بی‌توقف کار کرد تا دقیقاً همان چیزی را که می‌خواستیم تحویل دهد. چوب‌کاری سفارشی آن‌ها فضای ما را کاملاً ارتقا داد و ما بیش از پیش راضی هستیم. تخصص و قابل‌اعتماد بودنشان باعث شده برای پروژه‌های بعدی هم سراغشان برویم.',
					'name'   => 'دیوید ویلسون',
					'meta'   => 'بیلوکسی، میسیسیپی',
					'image'  => $img( 'avatar-3.webp' ),
					'rating' => 5,
				),
			),
		),
		'blog'         => array(
			'enabled'      => 1,
			'eyebrow'      => 'وبلاگ ما',
			'title'        => 'تازه‌ترین اخبار و منابع',
			'text'         => 'برای دانش تخصصی چوب‌کاری، نوآوری‌های صنعت چوب و راهکارهای خلاقانه، وبلاگ ما را بخوانید.',
			'count'        => 5,
			'button_label' => 'مشاهده همه مقالات',
		),
		'cta'          => array(
			'enabled'         => 1,
			'title_before'    => 'فضای خود را متحول کنید با',
			'title_highlight' => 'چوب‌کاری باکیفیت.',
			'points'          => array(
				array( 'text' => 'اجرای حرفه‌ای با دقت در جزئیات.' ),
				array( 'text' => 'تحویل به‌موقع پروژه، بدون تأخیر.' ),
			),
			'background'      => $img( 'bg-cta.webp' ),
			'form_title'      => 'درخواست خدمات شما',
			'button'          => 'ثبت درخواست',
			'recipient'       => '',
			'success'         => 'از درخواست شما سپاسگزاریم. به‌زودی با شما تماس می‌گیریم.',
			'error'           => 'مشکلی پیش آمد. لطفاً فیلدهای الزامی را بررسی کنید و دوباره تلاش کنید.',
		),
		'form'         => array(
			'name_label'          => 'نام و نام خانوادگی',
			'name_placeholder'    => 'نام و نام خانوادگی',
			'phone_label'         => 'شماره تماس',
			'phone_placeholder'   => 'شماره تماس',
			'message_label'       => 'پیام شما',
			'message_placeholder' => 'پیام شما',
		),
		'page_about'   => array(
			'eyebrow'      => 'درباره ما',
			'title'        => 'درباره ما',
			'intro'        => 'بیش از دو دهه است که چوب را به قطعاتی ماندگار و فضاهایی گرم تبدیل می‌کنیم؛ با تیمی از نجاران، طراحان و نصابان که به جزئیات اهمیت می‌دهند.',
			'image'        => $img( 'areas-team.webp' ),
			'story_eyebrow'=> 'از علاقه تا کمال',
			'story_title'  => 'جایی که هنر دست با نوآوری دیدار می‌کند',
			'story_text'   => 'کار ما با عشق به چوب و احترام به مهارت دست شروع شد. امروز همان وسواس اولیه را با ابزارهای دقیق و طراحی امروزی ترکیب می‌کنیم تا هر پروژه، هم زیبا باشد و هم سال‌ها بماند. از یک میز سفارشی تا بازسازی کامل یک فضا، هر کار با گفت‌وگو، نقشه و اجرای دقیق پیش می‌رود.',
			'story_image'  => $img( 'bg-why.webp' ),
			'stats'        => array(
				array( 'value' => '۲۰', 'suffix' => '+', 'label' => 'سال تجربه' ),
				array( 'value' => '۸۵۰', 'suffix' => '+', 'label' => 'مشتری راضی' ),
				array( 'value' => '۵۲۰', 'suffix' => '+', 'label' => 'بازخورد پنج‌ستاره' ),
			),
			'values'       => array(
				array( 'icon' => 'medal', 'title' => 'مأموریت ما', 'text' => 'تبدیل چوب به راهکارهایی زیبا، کاربردی و ماندگار برای زندگی و کار شما.' ),
				array( 'icon' => 'star', 'title' => 'چشم‌انداز ما', 'text' => 'تبدیل‌شدن به نامی مورد اعتماد در نجاری سفارشی، با دقت، نوآوری و پایداری.' ),
				array( 'icon' => 'swatchbook', 'title' => 'ارزش‌های ما', 'text' => 'صداقت، مهارت و رضایت مشتری؛ با احترام به چوب و محیط زیست.' ),
			),
			'team'         => array(
				array( 'image' => $img( 'craft-1.webp' ), 'name' => 'ایتن کالینز', 'role' => 'مدیر کارگاه' ),
				array( 'image' => $img( 'craft-2.webp' ), 'name' => 'سوفیا رینولدز', 'role' => 'طراح داخلی' ),
				array( 'image' => $img( 'craft-3.webp' ), 'name' => 'دانیل فاستر', 'role' => 'نجار ارشد' ),
				array( 'image' => $img( 'avatar-1.webp' ), 'name' => 'مایکل هیز', 'role' => 'متخصص نصب' ),
			),
			'team_eyebrow' => 'تیم ما',
			'team_title'   => 'با متخصصان پشت هر قطعه آشنا شوید',
		),
		'page_services'=> array(
			'eyebrow'      => 'خدمات ما',
			'title'        => 'خدمات چوب‌کاری متناسب با فضای شما',
			'intro'        => 'از طراحی و ساخت مبلمان سفارشی تا کابینت، کف‌پوش و بازسازی؛ همه خدمات نجاری را با یک تیم و یک استاندارد کیفیت دریافت می‌کنید.',
			'image'        => $img( 'service-builtin.webp' ),
			'stats'        => array(
				array( 'value' => '۶', 'suffix' => '', 'label' => 'تخصص اصلی' ),
				array( 'value' => '۸۵۰', 'suffix' => '+', 'label' => 'پروژه اجراشده' ),
				array( 'value' => '۱۲', 'suffix' => 'ماه', 'label' => 'ضمانت اجرا' ),
			),
			'process_eyebrow' => 'فرآیند کار',
			'process_title'   => 'از ایده تا نصب، در سه قدم روشن',
			'process'      => array(
				array( 'title' => 'درخواست و مشاوره', 'text' => 'نیاز و ایده‌تان را با ما در میان بگذارید. مشاوره اولیه و بازدید، رایگان است.' ),
				array( 'title' => 'طراحی و برآورد', 'text' => 'طرح، جنس چوب و برآورد شفاف هزینه را دریافت می‌کنید و جزئیات را نهایی می‌کنیم.' ),
				array( 'title' => 'ساخت و نصب', 'text' => 'قطعه در کارگاه ساخته می‌شود و تیم نصب، آن را با دقت و تمیزی در محل اجرا می‌کند.' ),
			),
		),
		'page_contact' => array(
			'eyebrow'      => 'تماس با ما',
			'title'        => 'دوست داریم از شما بشنویم',
			'intro'        => 'برای مشاوره، استعلام قیمت یا هماهنگی بازدید، از راه‌های زیر با ما در تماس باشید. تیم ما در اولین فرصت پاسخ می‌دهد.',
			'image'        => $img( 'areas-team.webp' ),
			'info_image'   => $img( 'bg-why.webp' ),
			'cards'        => array(
				array( 'icon' => 'pin', 'label' => 'آدرس کارگاه', 'value' => 'اصفهان، خیابان اول، کوچه دوم', 'url' => '' ),
				array( 'icon' => 'phone', 'label' => 'تماس مستقیم', 'value' => '۰۹۱۳۴۵۶۷۸۹۰', 'url' => 'tel:09134567890' ),
				array( 'icon' => 'email', 'label' => 'ایمیل', 'value' => 'info@example.com', 'url' => 'mailto:info@example.com' ),
				array( 'icon' => 'clock', 'label' => 'ساعت کاری', 'value' => 'دوشنبه تا جمعه، ۹ صبح تا ۸ شب', 'url' => '' ),
			),
			'form_eyebrow' => 'درخواست مشاوره',
			'form_title'   => 'پیام خود را برای ما بفرستید',
			'faq_eyebrow'  => 'سوالات متداول',
			'faq_title'    => 'پاسخ سوال‌های رایج',
			'faq'          => array(
				array( 'question' => 'آماده‌شدن پروژه چقدر زمان می‌برد؟', 'answer' => 'بستگی به ابعاد کار دارد؛ مبلمان سفارشی معمولاً چهار تا شش هفته و بازسازی کامل هشت تا دوازده هفته زمان می‌برد.' ),
				array( 'question' => 'مشاوره و بازدید اولیه رایگان است؟', 'answer' => 'بله. برای شناخت فضا و برآورد دقیق، یک جلسه مشاوره و بازدید اولیه رایگان انجام می‌دهیم.' ),
				array( 'question' => 'در کدام مناطق خدمات می‌دهید؟', 'answer' => 'در اصفهان و شهرهای اطراف خدمات ارائه می‌دهیم؛ برای پروژه‌های بزرگ‌تر هماهنگی لازم را انجام می‌دهیم.' ),
				array( 'question' => 'امکان سفارشی‌سازی جنس و ابعاد وجود دارد؟', 'answer' => 'بله، همه قطعات بر اساس ابعاد فضا و سلیقه شما طراحی و با جنس انتخابی ساخته می‌شوند.' ),
			),
		),
		'page_portfolio' => array(
			'eyebrow'      => 'نمونه‌کارها',
			'title'        => 'نمونه‌کارهای ما',
			'intro'        => 'گزیده‌ای از پروژه‌هایی که با دقت، مواد مرغوب و وسواس در جزئیات اجرا کرده‌ایم. هر نمونه‌کار، بخشی از تجربه ما در ساخت فضاهای ماندگار است.',
			'button_label' => 'درخواست پروژه مشابه',
			'button_url'   => '#request',
		),
		'footer'       => array(
			'about'         => 'چوب‌کاری حرفه‌ای با دقت، کیفیت و زیبایی ماندگار برای فضای شما.',
			'logo'          => 0,
			'copyright'     => '© ۲۰۲۶ نیل اند نات. تمامی حقوق محفوظ است.',
			'services_menu' => 0,
			'legal_menu'    => 0,
			'socials'       => array(
				array( 'network' => 'facebook', 'url' => 'https://facebook.com/' ),
				array( 'network' => 'x', 'url' => 'https://x.com/' ),
				array( 'network' => 'linkedin', 'url' => 'https://linkedin.com/' ),
				array( 'network' => 'instagram', 'url' => 'https://instagram.com/' ),
				array( 'network' => 'youtube', 'url' => 'https://youtube.com/' ),
			),
			'instagram'     => array(
				array( 'image' => $img( 'insta-1.webp' ), 'url' => '#' ),
				array( 'image' => $img( 'insta-2.webp' ), 'url' => '#' ),
				array( 'image' => $img( 'insta-3.webp' ), 'url' => '#' ),
				array( 'image' => $img( 'insta-4.webp' ), 'url' => '#' ),
				array( 'image' => $img( 'insta-5.webp' ), 'url' => '#' ),
				array( 'image' => $img( 'insta-6.webp' ), 'url' => '#' ),
			),
		),
	);

	return apply_filters( 'woodshop_studio_defaults', $defaults );
}

/**
 * Read a setting using dot notation.
 *
 * @param string $path    Dot path, e.g. "hero.title_before".
 * @param mixed  $default Fallback.
 * @return mixed
 */
function woodshop_studio_get( $path, $default = null ) {
	static $data = null;

	if ( null === $data ) {
		$saved = get_option( WOODSHOP_STUDIO_OPTION, array() );
		$data  = woodshop_studio_merge( woodshop_studio_defaults(), is_array( $saved ) ? $saved : array() );
	}

	$value = $data;

	foreach ( explode( '.', (string) $path ) as $segment ) {
		if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
			return $default;
		}
		$value = $value[ $segment ];
	}

	return $value;
}

/**
 * Resolve a media setting to a URL.
 *
 * @param mixed  $value Attachment ID or URL.
 * @param string $size  Image size.
 * @return string
 */
function woodshop_studio_media_url( $value, $size = 'full' ) {
	if ( is_array( $value ) ) {
		$value = $value['url'] ?? ( $value['id'] ?? '' );
	}

	if ( is_numeric( $value ) ) {
		return (string) wp_get_attachment_image_url( (int) $value, $size );
	}

	return is_string( $value ) ? $value : '';
}

/**
 * Boot the plugin.
 */
function woodshop_studio_boot() {
	Woodshop_Studio_Admin::instance();
}
if ( defined( 'WOODSHOP_STUDIO_EMBEDDED' ) ) {
	add_action( 'after_setup_theme', 'woodshop_studio_boot' );
} else {
	add_action( 'plugins_loaded', 'woodshop_studio_boot' );
}

/**
 * Add a settings shortcut on the plugins screen.
 *
 * @param string[] $links Action links.
 * @return string[]
 */
function woodshop_studio_action_links( $links ) {
	$url = admin_url( 'admin.php?page=woodshop-studio' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'woodshop-studio' ) . '</a>' );

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woodshop_studio_action_links' );
