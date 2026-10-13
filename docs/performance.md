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
