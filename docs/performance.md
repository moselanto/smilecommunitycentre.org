# Performance (target: home page interactive in 1-3 seconds)

## Measured on staging (13 Oct 2026, before 1.7.0)
- Server response (TTFB): 1.3-1.6 s on every request. No page cache was active.
- HTML: 109 KB. 11 render-blocking stylesheets, 9 scripts, 3 Google font families.

## What the theme now does (1.7.0)
- Home-only CSS and JS (slider, home sections) load on the home page only.
- Minified bundles in `assets/build/` (main CSS 75 KB -> 63 KB, home CSS 30 KB -> 26 KB, JS minified). Set `SCRIPT_DEBUG` to true to load the readable sources.
- Google Fonts: 2 families, 5 files, loaded without blocking first paint. The handwriting font loads only on About, Our Founder and FAQ. Elementor's own Google Fonts loading is turned off to avoid duplicates.
- Hello Elementor's theme.css and header script, jQuery Migrate, and the block-editor CSS on Elementor pages are no longer loaded.
- The first slider photo is preloaded with high priority. New 16:9 sizes (768, 1280, 1920) let phones download a small version.
- Photo lookups are cached (one database query per day instead of one per photo).
- Head clutter removed (generator, RSD, WLW, shortlink, emoji).
- Elementor performance options switched on by "Apply latest design": inline SVG icons, optimised markup, element caching, lazy background images.

## What the host / admin must do (biggest win: TTFB)
1. Turn on page caching: install WP Super Cache from Smile Setup > Plugins (or use WP Rocket / LiteSpeed Cache if the host provides it). Expect TTFB to drop from about 1.5 s to under 0.3 s.
2. Ask the host to enable PHP OPcache, Imagick or GD, and PHP 8.2+.
3. Enable Gzip/Brotli compression and browser caching (most cPanel hosts: "Optimize Website" -> compress all content).
4. After updating, run Apply latest design once, so new image sizes are generated and Elementor options are set.
5. Test with https://pagespeed.web.dev on mobile. Targets: LCP < 2.5 s, CLS < 0.1, INP < 200 ms.

## 2.17.0 speed additions

- **Self-hosted fonts:** Newsreader, Figtree and Caveat (latin subset, woff2, about 200 KB in total) are served from `assets/fonts/` with inline `@font-face` rules. The two main files are preloaded. This removes the Google Fonts DNS lookups and connections, often 300 to 800 ms on mobile.
- **Elementor icon font only where needed:** `elementor-icons` is dropped for visitors on pages without icon, accordion, tabs or carousel widgets.
- **Instant navigation:** WordPress 6.8+ speculative loading set to `prerender` / `moderate`, so the next page is prepared when a visitor hovers or presses a link.

## 2.29.0 speed additions

- **Banner photo first on every page** (`inc/performance.php`): inner pages preload the first section's background photo with `fetchpriority="high"` and a responsive `imagesrcset`; story pages preload their featured image. This is usually the largest thing on screen (the LCP element), so it now starts downloading with the HTML instead of after the CSS.
- **Lighter uploads**: `big_image_size_threshold` 1920 px, PNG to WebP copies, quality 80.
- **Fewer visitor requests**: no dashicons, wp-embed or heartbeat for logged-out visitors; no oEmbed/REST discovery links.
- **Elementor settings re-applied per theme version**: external CSS files (cacheable), optimised image loading, optimised Gutenberg loading, element cache, lazy background images, local fonts, SVG icons, optimised markup.
- **Existing photos**: photos uploaded before 2.29.0 keep their old sizes. To shrink them too, install "Regenerate Thumbnails", run it once, then remove it.

## How to measure

Run https://pagespeed.web.dev/ on the home page and one inner page (mobile tab) with the page cache on, after clearing it once and loading each page once to warm it. Targets: Largest Contentful Paint 2.5 s or less, First Contentful Paint 1.8 s or less, Time to First Byte 0.8 s or less. If TTFB is above 0.8 s, the server is the bottleneck: see the next section.

## Reaching 1 to 3 seconds: server side (cannot be done from the theme)

1. **Page cache (biggest win):** activate WP Super Cache, Settings > WP Super Cache > Caching On, "Expert" or "Simple" mode, cache rebuild on.
2. **Browser caching:** add to `.htaccess` (above the WordPress block):

```apache
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
</IfModule>
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml
</IfModule>
```
3. **PHP 8.2 or newer** in cPanel > Select PHP Version, with OPcache on.
4. **Images:** convert large uploads to WebP (for example with the free "Converter for Media" plugin) and keep photos under 2000 px wide.
5. **Elementor > Settings > Features:** keep "Optimized DOM output", "Improved asset loading", "Improved CSS loading" and "Lazy load background images" active.
6. Measure with PageSpeed Insights (mobile) after each step.


## Mobile LCP review (11 Nov 2026, 2.50.0)

- Search Console (CrUX, updated 8 Nov 2026): one group of 33 mobile URLs, "LCP longer than 4s", group LCP 4.2s; no CLS or INP issue. The 28-day window is mostly the old site (new site live 4 Nov), so the figure will move as new data replaces it.
- Measured with curl from outside Kenya: cached HTML TTFB about 0.7s; uncached 1.5-2.1s (PHP generation about 1.0-1.2s). HTML 197 KB (34 KB gzipped), of which about 89 KB is inline critical CSS. Theme images and fonts are served with a 1-year immutable cache.
- Fixed in code: hero preload for bundled slides, 1200w slide files, Caveat preload removed.
- Fixed in admin: WP Super Cache preload mode on, so visitors rarely hit an uncached page.
- Next candidates if field LCP stays above 2.5s: a smaller inline critical CSS (it currently holds almost the whole stylesheet), loading Elementor's per-page CSS asynchronously where the critical CSS covers it, and a server-level cache or CDN (for example Cloudflare) in front of VivaWebHost.
- PageSpeed Insights/Lighthouse could not be run from the build environment (API daily quota exceeded; headless Chrome missing libraries). Run PSI on Home, Donate and a story after each release and note the scores here.
- Validate the fix in Search Console (Core Web Vitals > Mobile > Validate fix) about 28 days after this release.


## PageSpeed Insights scores (11 Nov 2026, Lighthouse 13.6.0, single lab runs)

Mobile = emulated Moto G Power, Slow 4G. Field (CrUX) data: "No Data" on all three pages.

### After 2.51.0 (smaller critical CSS, footer CSS in background)

| Page | Device | Perf | LCP | FCP | TBT | CLS | SI | LCP element |
|---|---|---:|---:|---:|---:|---:|---:|---|
| Home | Mobile | 69 | 6.4 s | 3.2 s | 10 ms | 0 | 4.3 s | first slide photo |
| Home | Desktop | 70 | 1.4 s | 0.8 s | 0 ms | 0.815 | 1.5 s | first slide photo |
| Donate | Mobile | 73 | 5.5 s | 3.1 s | 10 ms | 0 | 3.5 s | hero section "We make a life by what we give" |
| Donate | Desktop | 94 | 1.3 s | 0.8 s | 0 ms | 0.084 | 1.2 s | hero section |
| Story* | Mobile | 92 | 1.1 s | 1.1 s | 180 ms | 0.140 | 2.6 s | article h1 |
| Story* | Desktop | 95 | 1.2 s | 0.8 s | 0 ms | 0 | 1.4 s | featured image |

*Story: /how-to-sponsor-a-child-in-kenya-a-donors-guide-to-real-lasting-impact/

Home desktop CLS 0.815 was blamed on `.smile-slide__content`: the `.smile-slider-sec` rules were never in the critical CSS (existing bug). Fixed in 2.51.1.

### After 2.51.1 (home re-test)

| Page | Device | Perf | LCP | FCP | TBT | CLS | SI |
|---|---|---:|---:|---:|---:|---:|---:|
| Home | Mobile | 70 | 6.3 s | 3.2 s | 20 ms | 0 | 4.1 s |
| Home | Desktop | **94** | 1.4 s | 0.8 s | 0 ms | **0** | 1.5 s |

### Remaining opportunities (from these reports)

- Mobile LCP on Home (6.3 s lab) and Donate (5.5 s): FCP is 3.1-3.2 s, so the delay is mostly before the first paint. Render-blocking requests about 300 ms (Elementor frontend/kit/page CSS), plus the server time.
- Unused JavaScript about 260 KiB on Home and Donate (Google tag/GTM and gtag.js account for most of it; consider loading analytics after the first interaction or idle).
- Image delivery: 41 KiB (Home), 16 KiB (Donate), 55 KiB (story).
- Story: CLS 0.140 from `.smile-container--narrow`; Lighthouse flags the featured image as unsized. Render-blocking 690 ms.
- Accessibility: contrast warnings on Home and the story (scores 96).
- Phone-size visual check of menu/footer could not be done with the available browser tools; mobile Lighthouse CLS was 0 on Home and Donate.


### After 2.52.0 (inline small Elementor/Hello CSS, deferred GA4/GTM, story image box)

| Page | Device | Perf | LCP | FCP | TBT | CLS | SI | LCP element |
|---|---|---:|---:|---:|---:|---:|---:|---|
| Home | Mobile | **97** | 1.9 s | 1.0 s | 50 ms | 0.095 | 2.9 s | first slide photo |
| Home | Desktop | **99** | 0.7 s | 0.3 s | 10 ms | 0 | 1.3 s | first slide photo |
| Donate | Mobile | **97** | 2.4 s | 0.9 s | 10 ms | 0.017 | 2.8 s | hero section |
| Donate | Desktop | **99** | 0.7 s | 0.3 s | 20 ms | 0.005 | 1.2 s | hero section |
| Story | Mobile | **99** | 1.1 s | 1.1 s | 50 ms | 0.061 | 2.6 s | article h1 |
| Story | Desktop | **99** | 0.7 s | 0.3 s | 10 ms | 0 | 1.3 s | featured image |

Home mobile went from 70 to 97 (LCP 6.3 s to 1.9 s); Donate mobile from 73 to 97 (LCP 5.5 s to 2.4 s). GA4 Realtime still records visits (scripts load on first scroll/tap or when idle). Field data: still "No Data".

Remaining:
- Home mobile CLS 0.095: `h1.smile-slide__title` (0.090), probably the heading font swapping in; plus the Soweto-Kayole image (`wp-image-1053`) has no width/height.
- Story mobile CLS 0.061: `h1.smile-article__title` (font swap). Donate mobile CLS 0.017: hero widget wrap.
- Render-blocking requests about 850 ms mobile (mainly Elementor `frontend.min.css` and the async theme CSS preloads); unused JS about 260 KiB is still reported (Lighthouse counts the deferred Google scripts when they load at idle).
- Content (not performance): the sponsor-guide story links to the old staging domain topnotchmall.co.ke/smilescommunity/ and shows editorial labels ("Quick Answer (Featured Snippet Target)", "Donation Call to Action").
- Contrast warnings on Home and the story (Accessibility 96).


### After 2.53.0 (Nunito font-display optional, footer contrast, sized community photo)

| Page | Device | Perf | A11y | LCP | FCP | TBT | CLS | SI |
|---|---|---:|---:|---:|---:|---:|---:|---:|
| Home | Mobile | 92 | 96 | 3.0 s | 1.0 s | 80 ms | **0** (was 0.095) | 4.0 s |
| Home | Desktop | 88 | 100 | 0.6 s | 0.3 s | 0 ms | **0.198** (was 0) | 1.6 s |
| Donate | Mobile | 95 | 97 | 2.6 s | 1.1 s | 80 ms | **0** | 3.9 s |
| Donate | Desktop | 95 | 97 | 0.7 s | 0.3 s | 150 ms | 0.005 | 1.5 s |
| Story | Mobile | 93 | **100** | 2.1 s | 1.1 s | 240 ms | **0** (was 0.061) | 3.7 s |
| Story | Desktop | 96 | 100 | 0.7 s | 0.3 s | 10 ms | 0 | 2.0 s |

Single runs; mobile performance scores and LCP vary from run to run (Home mobile LCP 1.9 s on the previous run, 3.0 s on this one).
- Fixed: mobile CLS is 0 on all three pages; footer contrast items are gone; no unsized images reported.
- New: Home desktop CLS 0.198 from the "A safe home / Daily meals" text widget (data-id 7a9bc3d: 0.174 + 0.023); the nav is also listed with caveat-latin.woff2. Needs a follow-up.
- Remaining contrast on Home mobile: slider eyebrow text and slide containers over photos (Lighthouse cannot read text over images) and one `em.smile-em`. Donate: Privacy Policy link in `.smile-form__privacy` relies on colour only (needs an underline).
- Home SEO 92: four links without descriptive text.


### After 2.53.1 (pillars in Home critical CSS, dark backgrounds under slider/closing text, underlined privacy link, descriptive Learn more text)

| Page | Device | Perf | A11y | SEO | LCP | FCP | TBT | CLS | SI |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Home | Mobile | 88 | 96 | **100** | 1.5 s | 1.0 s | 340 ms | 0.109 | 3.2 s |
| Home | Desktop | **99** | 100 | **100** | 0.7 s | 0.3 s | 50 ms | **0.058** (was 0.198) | 0.7 s |
| Donate | Mobile | n/a (NO_LCP, twice) | **100** | 100 | NO_LCP | 1.2 s | n/a | 0 | 4.6 s |
| Donate | Desktop | 98 | **100** | 100 | 0.7 s | 0.4 s | 110 ms | 0 | 1.2 s |
| Story | Mobile | 98 | 100 | 100 | 1.7 s | 1.1 s | 120 ms | 0.061 | 2.5 s |
| Story | Desktop | 98 | 100 | 100 | 0.7 s | 0.3 s | 50 ms | 0 | 1.5 s |

Single Lighthouse 13.6 runs; field data "No Data".
- Fixed: "Links do not have descriptive text" (Home SEO 92 to 100); "Links rely on color" on Donate (A11y 97 to 100); slider eyebrow/slide contrast items gone; Home desktop CLS down from 0.198 to 0.058.
- Still open:
  - Home mobile CLS 0.109 and story mobile 0.061 again come from the h1 (`h1.smile-slide__title`, `h1.smile-article__title`). Runs vary (both were 0 on the 2.53.0 run), so font-display: optional alone is not enough; next step is to reserve the h1 height or check whether the critical CSS gives the mobile h1 its final size.
  - Home desktop 0.058 still from the pillars strip (7a9bc3d); caveat-latin.woff2 listed.
  - Home mobile contrast: `em.smile-em` "everyday care possible." in the closing band.
  - Unsized image on Home: `img.wp-image-1048` (education photo).
  - Donate mobile returned NO_LCP twice (Lighthouse could not pick a largest element); desktop is fine.


### After 2.53.2 (Figtree font-display optional, no Figtree in heading stack, slide h1 reserve, closing highlight under text, sized card photos)

| Page | Device | Perf | A11y | SEO | LCP | FCP | TBT | CLS | SI | LCP element |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---|
| Home | Mobile | 94 | 96 | 100 | 3.0 s | 1.0 s | 0 ms | **0.007** (was 0.109) | 3.3 s | first slide photo |
| Home | Desktop | **99** | 100 | 100 | 0.7 s | 0.4 s | 20 ms | **0** (was 0.058) | 1.2 s | first slide photo |
| Donate | Mobile | **98** | 100 | 100 | 2.3 s | 0.9 s | 80 ms | 0.018 | 2.8 s | hero section 2603e9d |
| Donate | Desktop | 98 | 100 | 100 | 0.7 s | 0.3 s | 10 ms | 0.005 | 1.5 s | hero section 2603e9d |
| Story | Mobile | 98 | 100 | 100 | 2.1 s | 1.1 s | 10 ms | **0** (was 0.061) | 3.5 s | featured image |
| Story | Desktop | 97 | 100 | 100 | 0.7 s | 0.3 s | 120 ms | 0 | 1.2 s | featured image |

Single Lighthouse 13.6 runs; field data "No Data". Headings still render in the rounded brand font (checked visually).
- Fixed: h1 shifts on Home mobile and the story; pillars-strip shift on Home desktop; unsized images (none reported on any page).
- Donate mobile NO_LCP did not recur (first run normal). Cause of the two earlier failures not identified; checked: no Elementor entrance animations or lazy-background rules on the hero section, hero image preloaded per breakpoint. Treat as a Lighthouse flake unless it returns.
- Still open: Home mobile contrast on `em.smile-em` "everyday care possible." in the closing band (text over a photo with a 78% green overlay; Lighthouse cannot measure text over an image, so it may need a solid band behind the heading). Donate mobile CLS 0.018 (hero widget wrap 0.017, menu button 0.002).


### After 2.53.3 (green panel behind Home closing heading; .smile-nav-toggle in critical CSS)

| Page | Device | Perf | A11y | SEO | LCP | FCP | TBT | CLS | SI |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Home | Mobile | 75 | **100** | 100 | 4.1 s | 1.0 s | 360 ms | 0 | 4.9 s |
| Home | Desktop | 95 | 100 | 100 | 0.8 s | 0.3 s | 70 ms | 0 | 1.9 s |
| Donate | Mobile | 97 | 100 | 100 | 2.4 s | 1.1 s | 10 ms | 0.017 | 2.6 s |
| Donate | Desktop | 94 | 100 | 100 | 0.7 s | 0.3 s | 170 ms | 0.029 | 1.3 s |

Single Lighthouse 13.6 runs; field data "No Data".
- Fixed: no contrast failures on any page (Home mobile Accessibility 96 to 100). Menu button no longer listed as a shift culprit on Donate.
- Not fixed: Donate mobile CLS 0.017 (hero `div.elementor-widget-wrap.elementor-element-populated`); Donate desktop 0.029 (shortcode widget 3bb899c) plus unsized `img.wp-image-13` ("Young pupils in school uniform outside the centre"). Likely cause to check next: the give form's CSS (`<style id="smile-give-css">`) is printed inside the hero card in the body, so the intro can paint before those rules are parsed.
- Home mobile 75 / LCP 4.1 s / TBT 360 ms is lower than the 2.53.2 run (94 / 3.0 s / 0 ms); 2.53.3 changed only CSS, so this needs a repeat run before treating it as a regression.
