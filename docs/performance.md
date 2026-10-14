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
