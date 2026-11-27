# Wood Shop Theme

A fast, WooCommerce-ready WordPress theme for carpentry and joinery businesses. The homepage is a close recreation of the Nail & Knot demo with all content managed from the **Wood Shop Studio** plugin.

## Requirements

- WordPress 6.5+
- PHP 8.0+
- WooCommerce (optional, but the shop and product sections need it)
- The **Persian Calendar** plugin (`persian-calendar`) for Jalali (Shamsi) dates. The demo site uses it for all blog and archive dates.
- Wood Shop Studio plugin (bundled with the theme for one-click setup; the standalone plugin also works)

## Installation

1. Install and activate **Wood Shop** under Appearance → Themes.
2. On the first admin request the theme installs/activates the bundled Wood Shop Studio settings screen and runs the demo installer.
3. The demo installer creates the pages, blog posts, products, product categories, portfolio items, menus, images, front page and store settings. It installs WooCommerce and Persian Calendar from WordPress.org when they are missing.
4. If the automatic run is blocked by the host, open **Wood Shop → Demo Content** and press **Install / repair demo content**.
5. The installer reuses existing content with the same slug/title, so it is safe to run again.

## Homepage sections

Hero slider, features strip, why-choose-us, services (pulled from WooCommerce product categories or manual cards), featured products, showcase cards, guarantee band, project gallery, service areas, sectors, partner logos, financing, testimonials, blog and the request form.

Every section can be hidden without deleting its content. Repeaters let you add, remove and reorder content rows. Images use the native media library, so no custom field plugin is required.

## Menus

Menus are standard WordPress menus (Appearance → Menus) and are fully dynamic. The plugin lets you choose which menu is used for:

- the primary header navigation
- the footer services column
- the footer legal links

The header also shows a phone link, the site logo, an optional WooCommerce cart icon and a quote button.

## WooCommerce

- Shop, category, product, cart and checkout pages use the theme's layout.
- The homepage services grid can display product categories; six demo categories are included.
- The featured products section reads live WooCommerce products. Featured products are used by default.
- Product images use the theme's `ws-square` image size for the homepage cards.
- A slide-in cart drawer is available from the header on every page, with AJAX add/remove, live cart count, subtotal, empty state and focus management.
- Adding a product (homepage cards, shop cards or a single product) updates the header count instantly and opens the drawer.
- Prices use a custom **IRT / تومان** currency, zero decimals, the `٬` thousands separator and grouping every three digits. Demo prices are stored as natural Toman amounts (for example ۴۸٬۹۰۰٬۰۰۰ تومان).
- Product add buttons have default, loading, success, error and out-of-stock states. Homepage and shop cards also have a soft lift / image-zoom hover animation.
- The cart drawer falls back to the normal WooCommerce cart page when JavaScript is unavailable.

## Pages, shop filters and account

- Dedicated templates: `page-about.php`, `page-services.php`, `page-contact.php` and `page-portfolio.php`.
- Their copy, images, stats, team, services, FAQs and contact cards are editable in **Wood Shop Studio → About Page / Services Page / Contact Page / Portfolio Page**.
- The shop archive is a three-column layout with a filter sidebar: search, categories, price range, product tags, in-stock and on-sale. Filters use plain GET requests and `pre_get_posts`, so they work without JavaScript; on screens up to 1023px they move into a slide-in drawer.
- Filtered shop URLs are `noindex, follow` and canonicalised to the clean archive URL. Active filters are shown as removable chips.
- **Wood Shop Studio → Styles** edits the site palette (backgrounds, text, brand, accent, sand, success and error colors). Values are printed as CSS custom properties, so the whole theme follows the saved palette.
- The cart page is a two-column layout on desktop: items and coupon actions on the right, the totals card on the left in RTL. Coupon and update buttons use the theme font.
- Cart and checkout use classic WooCommerce shortcodes with local styling. The country and company fields are removed, fields are reordered, the store country is Iran, and checkout offers cash on delivery.
- My Account is limited to four tabs: داشبورد, سفارش‌ها, اطلاعات کاربری and خروج از حساب, with a custom dashboard overview and recent orders.
- `woodshop_breadcrumb()` outputs an accessible `<ol>` breadcrumb plus `BreadcrumbList` JSON-LD on pages, posts, products, product taxonomies and archives.
- Blog cards have a soft lift/zoom hover animation.
- نمونه‌کارها is a public `portfolio` post type with an archive at `/portfolio/`. Use the Order field to control archive order; single portfolio URLs redirect to the archive. The old `/projects/` page redirects there too.

## Persian / RTL

The included demo is Persian and the theme is fully RTL-aware:

- Site locale `fa_IR` (front end), admin user locale `en_US` for the English settings screen.
- `assets/css/rtl.css` mirrors the layout, aligns text right and loads the self-hosted **Vazirmatn** variable font.
- `assets/css/critical-rtl.css` is inlined on the front page so the hero renders correctly before the async stylesheets arrive.
- `languages/fa_IR.po` / `fa_IR.mo` translate the theme interface strings.
- Persian digits are used for dates, prices, cart counts and static numbers.
- The Wood Shop Studio content defaults are Persian, so **Reset demo** restores Persian content.

To switch back to another language, change Settings → General → Site Language; the LTR styles and Latin fonts are still included.

## GitHub updates

The theme is published at [github.com/mahdione/woodshop](https://github.com/mahdione/woodshop).

WordPress checks the latest GitHub release through `inc/github-updater.php` and shows it in **Dashboard → Updates**. The theme is also opted into WordPress automatic updates. Releases must include a `woodshop.zip` asset containing a top-level `woodshop/` folder.

To publish a release:

1. Update `Version:` in `style.css`.
2. Commit and push the change.
3. Run `./scripts/release.sh`.

The release script builds `woodshop.zip`, creates a matching tag such as `v1.1.0`, pushes it and publishes the GitHub release with the zip asset. It uses `GITHUB_TOKEN` when set, or the configured git credential helper for github.com.

For a private repository, define `WOODSHOP_GITHUB_TOKEN` in `wp-config.php` or return a token from the `woodshop_github_token` filter.

## Performance

The theme ships with performance work already in place:

- Self-hosted WOFF2 fonts with `font-display: swap` and preloads.
- One small CSS file and one vanilla JS file (no jQuery on the front page).
- Critical CSS (plus RTL critical CSS) inlined on the front page; the full stylesheets load asynchronously.
- Hero image preloaded with `fetchpriority="high"`; other images are lazy-loaded.
- Background images are real lazy-loaded `<img>` elements, not CSS backgrounds.
- SVG icon sprite instead of repeated inline paths.
- WordPress emoji, embed, block-library and global-style payloads removed on the front page.
- WooCommerce sourcebuster/order-attribution scripts removed on the front page.
- Output compression mu-plugin included as a fallback for hosts without server-level gzip.
- Scroll reveal animations and the hero zoom are disabled on mobile to keep INP/TBT low.

Measured on the included Persian demo (Lighthouse mobile, simulated slow 4G): Performance 95–98, Accessibility 100, Best Practices 78 (HTTP-only local site), SEO 100, LCP 2.1–2.5 s, CLS 0, TBT 0–190 ms. Serve the site over HTTPS for a 100 Best Practices score.

## Structure

```
woodshop/
├── assets/css/          main.css + critical.css (and .min versions)
├── assets/js/           main.js (and .min)
├── assets/img/          optimised demo images
├── assets/fonts/        self-hosted Saira Condensed + Roboto
├── inc/                 setup, helpers, icons, performance, form, WooCommerce
├── template-parts/      one file per homepage section
├── front-page.php
├── header.php / footer.php
└── style.css
```

## Notes

- The demo uses the Nail & Knot name and copy. Change the site title, logo and section text in Wood Shop Studio and Settings → General.
- About, Services, Reviews and Contact are complete dedicated templates; edit their content in the Wood Shop Studio settings.
- Request form submissions are stored under **Wood Shop → Requests** even if the mail server is not configured.
