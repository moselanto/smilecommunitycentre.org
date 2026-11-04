# Plan: cut unused JavaScript and CSS (theme 2.47.0, opt-in)

Mobile PageSpeed on 4 Nov 2026, after page caching: **92** (FCP 1.2 s, LCP 3.0 s, CLS 0).
PageSpeed still reported about 99 KiB of unused JavaScript and 36 KiB of unused CSS.

## Which pages need Elementor's frontend scripts
All 22 pages in the sitemap were checked on 4 Nov 2026 by reading their Elementor widgets and element settings.

| Pages | Elementor widgets used | Needs Elementor JS? |
|---|---|---|
| Home, About Us, Our Founder, Our Impact, Our Goals, Our Programmes, Education, Feeding, Health and Wellbeing, Sports, Child Care and Shelter, Donate, Contact Us, Get Involved, Partner With Us, Volunteer, Careers, Gallery, Safeguarding, Privacy Policy, Stories | heading, text-editor, button, image, shortcode, plus the theme's own header and footer widgets | **No**. They have no entrance animations, motion effects, sticky elements, lightbox, slideshows or video backgrounds. The header menu, slider and forms run on the theme's own `main.min.js` and `slider.min.js` |
| FAQ | the same, plus the **accordion** widget | **Yes**. The accordion opens and closes through Elementor's script |

Elementor's frontend bundle (webpack runtime, frontend-modules, jQuery UI core and frontend), together with jQuery, makes up almost all of the unused JavaScript. In lean mode it is skipped on every page above except FAQ. The check runs automatically on every request, covering the page and its header and footer templates. Adding an accordion, tabs, a carousel, an animation, a lightbox or any widget that isn't on the allow-list to a page brings the scripts back for that page only.

**Update (2.47.0):** Elementor accordion widgets now render as native `<details>`/`<summary>` elements (`inc/lean-assets.php` section 3). This applies to every visitor, whether or not lean mode is on. The questions and answers are still edited in the Elementor accordion widget, and the styling matches the previous accordion. The browser opens and closes each item itself, so the FAQ page also loads without Elementor's scripts. Inside the Elementor editor, the widget keeps its normal markup. To switch it off: `add_filter( 'smile_native_accordion', '__return_false' )`.

## Critical CSS without the layout jumping
- `scripts/build-critical-css.py` keeps only the rules for what is visible before scrolling: base styles, `@font-face`, the top bar, header and menu drawer, buttons, the home slider, and the inner-page banner and breadcrumbs. It keeps the whole rule and any surrounding `@media` block, so the first screen renders exactly as it does with the full stylesheet.
- Output size: about 7.7 KB gzipped for home and 6.8 KB for inner pages. That is below the roughly 14 KB that fits in the first network round trip.
- The critical file is inlined at the top of `<head>`. The theme stylesheets (`smile-community`, `smile-home`, `smile-slider`) then load with `rel=preload` and switch over once downloaded. A `<noscript>` fallback is included.
- **Elementor's CSS stays render-blocking on purpose.** It defines the section and column layout, so loading it late would cause the layout to jump (CLS).
- Images already have width and height set, and CLS is 0 today. Test for CLS on staging (see below) before turning this on in production.
- `build-theme-zip.sh` now rebuilds the critical files on every build.

## How to test on staging (topnotchmall.co.ke/smilescommunity)
1. Upload the 2.47.0 zip and choose "Replace current". Nothing changes yet, because lean mode is off by default.
2. Add `define( 'SMILE_LEAN_ASSETS', true );` to staging's `wp-config.php`. Clear the page cache and Elementor's cache.
3. In a private window (lean mode does not apply to signed-in users), check each item:
   - View source on Home and About Us: `elementor/assets/js/frontend.min.js` and `jquery.min.js` should be gone, and `<style id="smile-critical-css">` should appear in `<head>`.
   - View source on FAQ: the Elementor scripts are gone, every question opens and closes on click and with the keyboard (Tab, then Enter or Space), and the plus and minus icons and the open-item highlight look as they did before.
   - The menu drawer, its sub-menus, the slider, the WhatsApp button, the forms and the contact map all work.
   - On phone and desktop widths, the first screen should not flash unstyled content or shift when the page finishes loading.
   - PageSpeed on staging: compare unused JS, unused CSS, render-blocking requests and CLS.
4. If anything breaks, remove the line. Specific parts can also be adjusted with the `smile_lean_static_widgets`, `smile_lean_async_styles` and `smile_lean_needs_elementor_js` filters.
5. Once staging passes, add the same line to production's `wp-config.php`, clear the caches and rerun PageSpeed.
