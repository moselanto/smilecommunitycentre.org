# Changelog

All notable changes to the Smile Community Centre theme (`smile-community-child`) and the bundled Smile Community Core plugin. Newest first.

## 2.53.5 - 11 Nov 2026

- **Lighter Home styles** (`scripts/purge-front-css.py`, new; `inc/setup.php`): the front page now loads purged copies `main-front.min.css` (126 KB to 48 KB) and `home-front.min.css` (159 KB to 87 KB), holding only rules whose classes/ids appear in the live Home markup (mobile and desktop), in the theme/Core JavaScript, or in a safelist of runtime state classes. Every other page keeps the full files; critical CSS is still built from the full files. The used-token list is saved in `scripts/front-used-tokens.txt`; **after editing the Home page in Elementor, run `python3 scripts/purge-front-css.py --refresh` before building**, or new classes on Home will have no styles. `build-theme-zip.sh` runs the purge (offline) on every build.
- **Home DOM:** an empty browser-extension element pasted into the Home text (`#jw-selection-toolbar-host`) is stripped on render (`inc/content-fixes.php`). The Home body has about 740 elements, below Lighthouse's DOM-size warning, so no sections were restructured.
- **Phone slider heading shift (0.020 in 2 of 4 runs):** the handwritten eyebrow line has a fixed 1.2em height and the three-line heading's reserved height is raised to 3.45em, which covers its accent line, so neither can push text below.
- **Programme card photos** (wp-image-1048 and the others): the image widget itself is now a fixed 16:11 box and the photo fills it (object-fit: cover), so the image size never decides the layout. Same look.

## 2.53.4 / Core 1.6.2 - 11 Nov 2026

- **Donate form styles in the head** (Core `includes/donations.php`): `smile-give-css` was printed inside the Donate hero card, so the hero intro could paint before the form/hero rules were parsed and then move (Donate CLS 0.017 mobile, 0.029 desktop). It is now printed in `wp_head` on pages using `[smile_donate_form]`, `[smile_give_hero]` or `[smile_manage_giving]`; the shortcodes then skip it (shared `$GLOBALS['smile_pay_styles_done']` flag).
- **Photo widgets** (`.smile-photo img`, e.g. wp-image-13 "Young pupils in school uniform"): `height:auto`, so the existing width/height attributes (800x416) and aspect-ratio rules reserve the box.

## 2.53.3 - 11 Nov 2026

- **Home closing band:** the "Help make everyday care possible." heading now sits on a solid dark green panel, so Lighthouse can measure its white and yellow text (mobile contrast warning). Photo, overlay, eyebrow, text and buttons are unchanged.
- **Donate mobile layout shift (0.018):** the menu button (`.smile-nav-toggle`) had no rules in the critical CSS (the KEEP pattern matched `.smile-nav` but not `.smile-nav-toggle`), so it painted as a plain button and resized when the full stylesheet arrived, moving the header and the hero below it. `nav-toggle` is now kept in the critical CSS (open-menu states excluded via `.smile-nav-open`).

## 2.53.2 - 11 Nov 2026

- **Heading and body fonts never swap late** (`inc/setup.php`, `assets/css/main.css`): Figtree (body) now uses `font-display: optional` like Nunito, and the heading font stack no longer falls back to Figtree. Lighthouse's test machine has no Arial, so headings fell through to Figtree, which then swapped in and re-wrapped the slide h1 (Home mobile CLS 0.109), the article h1 (0.061) and the pillars strip text (Home desktop 0.058).
- **Slide h1 room reserved on phones:** the three-line split-slide title keeps `min-height: 3.3em`.
- **Closing band contrast:** the yellow bar under "everyday care possible." now sits under the words instead of behind them.
- **Programme card photos** (`.smile-pcard__img img`, e.g. wp-image-1048) and photo widgets get `height:auto`, so the box keeps its ratio and counts as sized.

## 2.53.1 - 11 Nov 2026

- **Home desktop layout shift (0.198) fixed:** the "A safe home / Daily meals / Schooling" pillars strip (`.smile-pillars`, widget 7a9bc3d) overlaps the bottom of the slider, so it is in the first screen. Its rules are now in the Home critical CSS (`scripts/build-critical-css.py` KEEP list; skipped on inner pages), so it no longer jumps from an unstyled list into the card grid when the async stylesheet arrives.
- **Contrast checks on Home:** the slider, its track and slides, and the closing "Help make everyday care possible" band now have a dark green background colour under their photos, so the white and yellow text is measured against dark green. The closing band's highlighted words keep a solid yellow.
- **Donate privacy note:** the Privacy Policy link is underlined, so it does not rely on colour alone.
- **Descriptive link text** (`inc/seo.php`, `smile_describe_generic_buttons`): Elementor "Learn more" buttons get hidden screen-reader text naming the linked page ("Learn more about Feeding Program"). The visible label is unchanged. This covers the four Home buttons (Education, Feeding, Health and Wellbeing, Sports).

## 2.53.0 - 11 Nov 2026

- **Staging links and editorial labels removed from posts** (`inc/content-fixes.php`, new): a one-off clean-up that runs once in wp-admin and then records `smile_content_fix_2530`. It rewrites `topnotchmall.co.ke/smilescommunity` links to the live domain in every post, page and Elementor template, and removes the writer's labels "Quick Answer (Featured Snippet Target)" and "Donation Call to Action". Before the fix the REST scan found 8 posts: 151 (sponsor guide: Donate, About, Feeding, Contact + both labels), 152 (Donate link + label), 159, 161, 163 (Donate links and image URLs), 166, 182 and 183 (Donate links). Posts are saved with wp_update_post, so revisions are kept.
- **Heading font no longer shifts layout** (`inc/setup.php`): Nunito now uses `font-display: optional` (still preloaded). Fixes the mobile CLS on the Home slide h1 (0.090) and the article h1 (0.061). On a very slow first visit, headings may stay in the metric-matched fallback for that page only.
- **Footer contrast audit** (`assets/css/main.css`): removed `content-visibility: auto` from the footer. The colours already pass (cream #DCE7E2 and yellow on #0B4021), but the skipped render made Lighthouse check them against white.
- **Highlighted heading words** (`.smile-em`): solid text colour per section, with the yellow smile mark placed under the text.
- **Soweto-Kayole photo** (`.smile-community-photo img`, wp-image-1053): fixed 1024/497 shape with `height:auto`, so its space is reserved before it loads.

## 2.52.0 - 11 Nov 2026

- **Fewer render-blocking stylesheets** (`inc/lean-assets.php`): small local CSS files (Hello reset, Elementor kit, page, header/footer template, widget-heading, widget-image, base desktop/mobile; each under 8 KB) are printed inline in the head in the same order and media, instead of about nine separate blocking requests. Elementor's 54 KB `frontend.min.css` stays external.
- **GA4 and Tag Manager deferred** (`inc/analytics.php`): dataLayer, gtag() and the GA4 config run at once, but the Google scripts (about 260 KB) load on the first scroll/tap/key/mouse move, or when the browser is idle after load (within about 4 s). Off switch: `smile_analytics_defer` filter.
- **Story featured image layout shift (CLS 0.140) fixed:** the article image rules are now in the inner-page critical CSS, and the image box has `height:auto` and a fixed `aspect-ratio`, so its space is reserved before it loads.

## 2.51.1 - 11 Nov 2026

- **Fixed desktop layout shift on the home slider (CLS 0.815 in PageSpeed).** The slider section's own layout rules (`.smile-slider-sec ...`, including where the slide text sits at 768 px and wider) were never in the critical CSS, so the slide text jumped when the full stylesheet arrived. `slider-sec` is now in the critical CSS selector list. Existing bug, not caused by 2.51.0.

## 2.51.0 - 11 Nov 2026

- **Smaller inline critical CSS.** `scripts/build-critical-css.py` now keeps only the matching selectors of each rule (not the whole comma list), leaves interaction states (:hover, :focus, open menu) to the full stylesheet, skips print media, and skips components that are never in a template's first screen (home: page banner, breadcrumbs, FAQ, stories lead; inner pages: slider, FAQ). Home: 81.0 KB to 55.2 KB (10.6 KB gzipped); inner: 66.1 KB to 53.5 KB. The remaining bulk is the header menu (desktop menu and mobile drawer markup are in the first screen).
- **Elementor footer CSS loads in the background** (`inc/lean-assets.php`): the footer template is always below the fold. The page's own Elementor CSS, the kit and Elementor's frontend layout CSS stay render-blocking because they lay out the first section (moving them would cause layout shift).

## 2.50.0 - 11 Nov 2026

Mobile LCP work (Search Console: "LCP issue: longer than 4s (mobile)", 33 URLs in one group, group LCP 4.2s, CrUX data to 8 Nov 2026).
- **Home hero photo preloaded.** The bundled first slide had no preload (the preload only handled Media Library images), so phones discovered it after about 90 KB of inline CSS. Now preloaded in the head with the same srcset/sizes as the `<img>`.
- **1200 px slide photos added.** Phones at about 2.6x density were downloading the 1600 px file (feeding-tables: 95 KB); they now get 1200 px (63 KB). All six slides have a 1200 px version (25-30% smaller).
- **Caveat font no longer preloaded** on the home page (51 KB, decorative, `font-display: optional`); it was competing with the hero photo.
- Findings recorded in `docs/performance.md`: the field data is a 28-day window, and the new site went live on 4 Nov 2026, so most of the 4.2s comes from the old site. Cached page TTFB is about 0.7s, but uncached it is 1.5-2.1s (VivaWebHost), so WP Super Cache preload mode was enabled to keep pages warm. Validate the fix in Search Console from about 2 Dec 2026.

## 2.49.1 / Core 1.6.1 - 11 Nov 2026

- Old sitemap addresses (`/sitemaps.xml`, `/sitemap.xml`, `/wp-sitemap.xml`) now reach Rank Math's `/sitemap_index.xml` in one 301. The saved redirect list from the original activation still pointed to `/wp-sitemap.xml`, causing two hops; built-in sitemap rules now always win.
- Verified live after 2.49.0: one JSON-LD block (theme graph), GA4 and GTM each loaded once (2 ID occurrences each: script + config/noscript), robots.txt and llms.txt live, `/shop/`, `/cart/`, `/volunteer.html` 301 in one hop, feeds send `X-Robots-Tag: noindex, follow`, GA4 Realtime receiving data.

## 2.49.0 / Core 1.6.0 - 11 Nov 2026

- **Rank Math SEO replaces SEOPress.** Theme titles and descriptions now feed Rank Math (`rank_math/frontend/title`, `rank_math/frontend/description`); hand-typed Rank Math values win, older SEOPress values are read as a fallback. Rank Math's JSON-LD is switched off so the theme's connected graph is the only schema. Setup wizard recommends Rank Math instead of SEOPress; importer and careers write both meta keys.
- **Analytics in the theme** (`inc/analytics.php`): GA4 `G-DJQ2PBW3YN` and GTM `GTM-TDZHTDQF`, not for logged-in staff, silent while SEOPress is still active (no double counting).
- **AI and crawler readiness** (`inc/ai-discovery.php`): robots.txt with explicit rules for Google, Bing, Apple, OpenAI, Perplexity, Anthropic and Common Crawl (all allowed; admin and search closed) and the active sitemap address; `/llms.txt`; feeds `noindex, follow` (15 feed URLs were "Crawled - currently not indexed").
- **Search Console 404s and old URLs (Core):** `/shop/`, `/cart/`, `/checkout/` to Donate, `/my-account/` to Home; old static-HTML addresses (`/volunteer.html`, `/smile-children-s-home-kenya.html` and others) to their pages; old sitemap addresses go to Rank Math's `/sitemap_index.xml` when Rank Math is active.
- Merged PR #22 first (verified facts: founder Margret Nekesa Khaemba, CBO, Organization type, no unverified postal box, periodic volunteering).
- Docs: `docs/SEO.md` (Rank Math, analytics, AI section), new `docs/crawler-access-matrix.md`.

## 2.48.2 / Core 1.5.2 (Re-check stuck donations with Paystack)
- **Smile Centre > Donations** has a **Re-check with Paystack** link on every donation that is not marked Paid, plus a bulk action of the same name (up to 50 at a time). It asks Paystack's verify API for the real status: a successful payment whose amount and currency match is marked Paid (and the receipt/notice steps run as usual); abandoned, failed or reversed payments get that status; anything else stays Pending. A notice lists each reference and its result.
- Useful when a donor paid but closed the window before returning to the site and the webhook did not arrive.

## 2.48.1 / Core 1.5.1 (Donate hero: compact form card)
- The "Give a smile" card in the Donate hero is tighter, so the amounts, name, email and **Donate** button fit the first screen on a normal laptop: "How often?" and "Currency" sit side by side, amounts are a compact 3-column grid (two-line descriptions), and "Other amount" is a normal tile beside KES 10,000 instead of a full-width row.
- The headline now starts at the top of the hero, level with the card, instead of sitting low with empty photo space above it.
- Phones keep a 2-column amount grid with "Other amount" full width.

## 2.48.0 / Core 1.5.0 (Donate page: form in the first screen)
- **New `[smile_give_hero]`** for the Donate page hero: headline, short lead and trust points on the left, the "Give a smile" Paystack form as a white card on the right. On phones the form comes straight after the headline, with the trust points below it. Attributes `eyebrow`, `title` and `lead` keep the wording editable; the defaults are the existing Donate page text.
- Use it in the hero section with the classes `smile-sec smile-pagehero smile-pagehero--give` (the demo Donate page is updated). If online giving is off, the hero shows the headline only.
- `[smile_donate_form]` now renders once per page, so a second copy further down the page is skipped automatically.

## 2.47.5 / Core 1.4.2 (Paystack payment window on the Smile page)
- **Donors stay on smilecommunitycentre.org.** The donation form now opens Paystack's secure payment window (Paystack Inline v2, `resumeTransaction`) over the Donate page instead of sending donors to checkout.paystack.com. The server still starts the transaction (same reference, amount, plan and metadata), and the payment is still verified on the server and by the webhook.
- After paying, donors land on the usual thank-you message; closing the window shows the "not completed" message.
- **Fallback:** if Paystack's script can't load or the window can't open, the form falls back to the previous full-page redirect, so giving never breaks.
- `Permissions-Policy` now allows `payment` for checkout.paystack.com, so wallet payments can work inside the window.

## 2.47.4 / Core 1.4.1 (Paystack key status on the settings page)
- **Smile Centre > Centre details > Online donations (Paystack)** now shows a status line under the heading: the Paystack mode (test or live), whether the secret key for that mode was found in wp-config.php, and the webhook URL to enter in Paystack. The key itself is never shown. The status function existed in 1.4.0 but was not displayed.

## 2.47.3 (mobile hero heading shift)
- **A metric-matched fallback font for Nunito.** Fresh mobile PageSpeed on 5 Nov showed CLS 0.108 on `h1.smile-slide__title` while Nunito swapped in. A new `Nunito Fallback` font-face (local Arial, Liberation Sans or Arimo) is sized to Nunito's measured widths and line metrics:
  - weights 200-500: `size-adjust` 100.19%
  - weights 600 and up (headings): matched against Arial Bold, `size-adjust` 95.35%
  - ascent, descent and line-gap overrides from Nunito's own file

  Text no longer re-wraps or moves when Nunito arrives. `--smile-font-display` is now `"Nunito", "Nunito Fallback", "Figtree", ...`.
- **Hero slide image** has `aspect-ratio: 1600 / 871` as well as its width and height attributes. Lighthouse had flagged it as "unsized".
- The minified CSS and critical CSS have been rebuilt.

## 2.47.2 (desktop layout shift from fonts)
- **Caveat no longer swaps in late.** One desktop PageSpeed run on 4 Nov showed CLS 0.747 on the hero slide content, with the Caveat web font listed as a contributor. Caveat is decorative (captions and signatures), so it now uses `font-display: optional`: if it isn't ready for the first paint, the fallback stays and nothing moves. On the home page it is also preloaded, so it is usually ready in time. Nunito and Figtree are unchanged (preloaded, `swap`).

## Core 1.4.0 (Paystack donations: one-time and monthly)
- **New `[smile_donate_form]`:** a Paystack donation form with:
  - one-time or monthly giving
  - KES and USD amounts (standard presets: KES 1,000/2,500/5,000/10,000 and USD 10/25/50/100, each with an impact line), plus a custom amount
  - an optional "cover the processing fee" tick box, off by default
  - an accessible, mobile-first layout matching the site palette. It works without JavaScript; the script only updates the button label and shows the fee amount.
- **Payments:** donors pay on Paystack's own checkout page, so card data never touches the site.
  - Monthly gifts use Paystack Plans and Subscriptions. One plan is created per currency and amount, then reused.
  - Every payment is verified on the server against the stored amount and currency before it is marked paid.
- **Webhook** `POST /wp-json/smile/v1/paystack`:
  - checks the HMAC-SHA512 signature on every request and processes each reference only once
  - records monthly renewals and stores subscriptions
  - tracks cancellations and failed renewals, and tells staff when a renewal fails
- **New `[smile_manage_giving]`:** Paystack emails donors a secure link to update their card or cancel. The page never reveals whether an email address has a gift on file.
- **Records and emails:**
  - a private Smile Centre > Donations list with amount, type, status and reference, plus a CSV export that is safe from formula injection
  - branded HTML receipts for donors
  - staff notices
- **Security:**
  - keys are read only from `wp-config.php` (`SMILE_PAYSTACK_SECRET_KEY`, optional `SMILE_PAYSTACK_TEST_SECRET_KEY` and `SMILE_PAYSTACK_MODE`)
  - the form has a nonce, a honeypot and a limit of 10 payment starts per hour per visitor; the manage form allows 5 requests per hour
  - the return URL is validated, and the redirect only goes to `checkout.paystack.com`
  - donation result pages are never cached
  - CSP `form-action` now allows `https://checkout.paystack.com`
- **Settings:** Smile Centre > Centre details > Online donations (Paystack), for administrators only. Covers on/off, currencies, amounts, fee percentages (KES 2.9%, USD 3.8%), the notification email and the manage page URL.
- Setup and test steps: `docs/paystack-donations.md`.

## 2.47.1 (fix: layout shift with lean mode)
- **Critical CSS was missing the slider and header element rules.** The selector filter in `scripts/build-critical-css.py` used `\b` after the block name, and `_` counts as a word character, so BEM elements like `.smile-slide__content`, `.smile-slider__controls` and `.smile-header__inner` were left out. On slow loads, the hero text and controls moved when the full stylesheet arrived. This caused CLS 0.481 in one of three mobile PageSpeed runs on 4 Nov. The filter now matches BEM elements (`__`) and modifiers (`--`), and the critical files have been rebuilt: 14.8 KB gzipped for home and 12.2 KB for inner pages.

## 2.47.0 (lean assets, opt-in)
- New `inc/lean-assets.php`, turned on with `define( 'SMILE_LEAN_ASSETS', true );`. It is off by default; test it on staging first.
  - Elementor's frontend scripts (and jQuery when unused) are skipped on pages that use only static widgets. All pages except FAQ qualify today. Each page and its header and footer templates are checked automatically.
  - Critical CSS is inlined in `<head>`, and the theme stylesheets load without blocking rendering (preload plus a noscript fallback). Elementor's CSS stays blocking to avoid layout shift.
- **FAQ without Elementor JS:** Elementor accordion widgets render as native `<details>` elements for all visitors. The questions and answers are still edited in Elementor and look the same as before (new `.smile-faq__*` rules in `main.css`). The FAQ page no longer needs Elementor's scripts. To switch it off: `smile_native_accordion` filter.
- **Mobile image delivery:** the bundled hero slides now include 720 px versions (`*-720.webp`) in their srcset, so phones no longer download the 960 px file.
- **Accessibility:** a `<main id="content">` landmark is added around page content on Elementor full-width pages, so both skip links now land on the content. The parent theme's second skip link is visually hidden but still focusable; it was `display:none`, which Lighthouse reports as not focusable.
- New `scripts/build-critical-css.py`, which builds `assets/build/critical-home.min.css` and `critical-inner.min.css`. It runs from `build-theme-zip.sh`.
- Plan and staging test steps: `docs/lean-assets-plan.md`.

## 2.46.1 (mobile speed)
- **jQuery no longer blocks rendering.** It was the only blocking script in `<head>`. It now loads in the footer on the public site, after the page has painted. Everything that uses it (Elementor) was already in the footer. This is skipped for signed-in users and in the Elementor editor. To switch it off: `add_filter( 'smile_jquery_in_footer', '__return_false' )`.
- **Lighter slides on phones.** Every hero slide after the first now uses the 1280 px image size instead of 1920 px. The first slide is unchanged.
- **Server config:** JavaScript is served as `text/javascript`, which the compression and cache rules in `docs/server/htaccess-production.conf` did not cover. Live JS files were going out uncompressed with only one week of caching. Both rules now include `text/javascript`.

## 2.46.0 / Core 1.3.7 (security, production)
- **Redirect targets validated:** only `/path` or `http(s)://host` targets are accepted. `javascript:`, `data:` and `//host` targets are ignored. Absolute targets that an administrator adds now redirect correctly instead of falling back to wp-admin.
- **Rate limits behind a CDN:** new `smile_core_client_ip()`. Forms and the login limiter use the forwarded visitor IP only when the request comes from a trusted proxy (`SMILE_TRUSTED_PROXIES` or the `smile_trusted_proxies` filter). The default behaviour is unchanged.
- **Server configuration** in `docs/server/`: production `.htaccess` (security deny rules, HTTPS, Brotli/Deflate compression, one-year caching for versioned assets), an uploads `.htaccess` that blocks script execution, and a `wp-config.php` hardening guide.
- Docs: SECURITY-AUDIT production re-audit, and status updated to live in the PRD, README, AGENTS and ARCHITECTURE docs.

## Unreleased (repository clean-up after launch)
- Removed the outdated standalone copy of Smile Community Core (1.0.1) from `wp-content/plugins/`. The only maintained copy is the one bundled in the theme (`plugins/smile-community-core`, 1.3.6), which the Smile Setup wizard installs. The old copy contained a separate page builder that could overwrite pages with older content and change search-engine visibility.
- Added a GitHub Actions workflow (`.github/workflows/build.yml`) that runs PHP 8.3 syntax checks and builds `smile-community-child.zip` as a downloadable artifact.
- Docs: corrected the bundled plugin version (1.3.6), and added sub-folder-to-root migration steps (serialized-safe search and replace, `.htaccess` RewriteBase, site URLs).

## 2.45.1 (sync with edits made on the site)
- `demo/pages.json` now matches the page content edited directly in WordPress on the staging site (export of 31 Oct 2026), so "Apply latest design" no longer overwrites those edits:
  - **Home:** new "who we are" intro aligned with Kenya's Care Reform Strategy; badge now "Since 2016"; HIV/AIDS card renamed "Health & Wellbeing" with a "Learn more" button; new featured Google review quote.
  - **About Us:** "Since 2016" intro; banner photo is now the founder with children.
  - **Our Founder:** founder photo is now the founder with children.
  - **Health and Wellbeing:** updated HIV/AIDS introduction text.
- Stray browser-extension markup (`jw-selection-toolbar-host`) that the editor had saved into text blocks is removed. Theme and plugin files on the site were already identical to 2.45.0.

## 2.45.0 (speed)
- **Lighter CSS on inner pages.** `scripts/build-css.py` now also writes `main-inner.min.css` and `home-inner.min.css` without the home-page-only rules (slider, pillars, intro, programme cards, appeal, founder, ways-to-help head, voices, closing). Inner pages load home styles of 118 KB instead of 159 KB (about 20 KB gzipped instead of 26). The home page, the Elementor editor, Customizer previews and any page that uses one of those sections still get the full files (`smile_needs_full_css()`), so editing is unchanged.
- **Banner photos load first and right-sized (About, Donate and every page with a photo banner).** The banner photo was preloaded at the wrong size while Elementor painted the full original, so browsers found it late. The page now preloads the exact phone (768 px) and desktop (1536 px) files with high priority and points the banner at them. It follows whatever photo is chosen in Elementor; nothing changes inside the editor.
- **Donate banner compressed.** A theme copy of "children eating at the centre" replaces the 867 KB original on the page: 203 KB on desktop, 74 KB on phones (`assets/images/heroes/`). Choose a different banner in Elementor and that photo is used instead.
- **Contact map loads on scroll.** Google Maps (about 500 KB) now loads only when a visitor scrolls near it, with a light map-pin placeholder; without JavaScript it loads as before. Smile Community Core 1.0.1 / bundled 1.3.6.

## 2.44.1
- **Home copy matches the founders' approved content document (29 Oct 2026).** Cross-checked every heading, paragraph, caption and button in sections 2.2-2.11: all match except the HIV/AIDS card button, which 2.36 had changed to "Support Health" plus "Learn more". Restored the approved single button "Explore This Program" (to Health and Wellbeing). Apply latest design to update.

## 2.44.0
- **Bottom bar docked to the screen edge.** The 2.43 floating rounded dock left a gap under it with page content showing through. It is now a full-width bar fixed flush to the bottom of the screen (white, thin top border, soft shadow), with the phone's gesture area included inside it. Active-page highlight and the raised yellow Donate button stay; page content and the WhatsApp button are kept clear of it.

## 2.43.0 (mobile menu)
- **Sub-menus are never hidden.** The menu drawer's contact block (Donate, two contact cards and the hours line) was fixed at the bottom and took about a third of the screen, so opened sub-menus slid behind it with no sign the list could scroll. Now only the menu list scrolls; the footer is one compact row (Donate, round Call and WhatsApp buttons); a soft fade and a small "More" tag appear while there is more below; and an opened sub-menu scrolls itself into full view.
- **Calmer menu rows:** bolder labels, softer dividers, and the opened section gets a light background.
- **Bottom bar redesigned** as a floating rounded dock: even spacing, a green pill with a yellow tick on the current page, the raised yellow Donate button, and page content and the WhatsApp button kept clear of it.

## 2.42.0
- **Menu chevrons stay still.** The sub-menu arrows no longer flip; they sit beside the label as a fixed 18 px item (turning yellow on hover or when open), so they can never cover letters. The current-page underline stops before the arrow. Phones: the round arrow button no longer flips either.
- **Headings never run under photos.** Emphasised words in headings (for example "A chance to move forward.") used to stay on one unbreakable line and slid under the photo beside them. They now wrap with the rest of the heading on every screen; columns can no longer be pushed wider than their space, and the intro heading is sized to its column, with the text layer above the photo.

## 2.41.0 (mobile)
- **Menu opens clearly on phones.** The sticky header formed its own layer, so the dimmed backdrop sat on top of the open menu and blurred it. The menu panel now always sits above the page; the backdrop is a plain dim layer behind it.
- **One menu button.** The bottom bar's "Menu" button is gone (the header button is the menu); Home, Programmes, Donate and Contact share the bar evenly.
- **Content stacks into one clean column on phones.** Text and photo, and headings with their "All programmes"/"All stories" buttons, were squeezed side by side; every section now stacks full width with readable heading sizes, consistent spacing and 4:3 photos.
- **Buttons the same size.** On phones every call-to-action button is full width and 52 px tall, stacked with even gaps (slider, appeal, closing, page bands).
- **Hero slider on phones.** Photo on top, then the message on solid green straight after it (no empty band or mismatched green strip); full text visible, buttons stacked and equal; dots sit on the photo.

## 2.40.0
- **Sub-menus are obvious again.** About, Programmes, Impact and Get Involved show a clear round chevron next to the label that turns yellow and flips when the sub-menu opens (2.38's underline had taken over the arrow's slot and shrunk it to a tiny yellow mark). The current-page underline no longer clashes with it, dropdowns have a small pointer and a hover bridge so they don't close while moving the mouse down. Phones and tablets get a 44 px round chevron button beside each parent item and a yellow guide line beside the opened sub-menu.
- **Accessibility:** one "Skip to content" link instead of two; the WhatsApp button has a spoken label.
- **Gallery:** opening a photo shows only the Smile photo viewer (Elementor's own lightbox no longer opens on top of it).
- **Volunteer page wording:** "there is a place for your skills" replaced with register-your-interest wording that matches the "placements full" notice (Apply latest design).

## 2.39.0
- **Hero slider text is never cut off.** On the first slide ("Because of you, / They eat. They dream. They smile.") the top line was hidden under the header because the slide had more text than the fixed-height slider could hold. On tablets and desktops the slider now grows to fit its tallest slide (all slides stay the same height and still cross-fade), the text has proper room above and below, and the split slide uses a slightly calmer type size. On short laptop screens the small handwritten "Change a life today." line is hidden to keep everything visible. Phones are unchanged.
- **Go-live link check (29 Oct 2026).** All 55 addresses in the live smilecommunitycentre.org sitemaps (28 posts, 16 pages, 12 categories) are in `docs/old-urls.txt`; `scripts/check-old-urls.sh` on staging: 96 of 96 old addresses (sitemaps, categories, pagination, feeds, tags, old ?p= links) open a working page with at most one 301 redirect.

## 2.38.0
- **Top menu balanced.** Logo on the left, menu centred in the space between, Donate on the right; even spacing between items, larger tap targets, a soft hover highlight and a yellow underline for the current page; dropdowns as tidy cards. Spacing tightens slightly on smaller laptops so nothing crowds.
- **"A home, care and opportunity" redesigned.** Balanced 52/48 split with more breathing room; larger heading; the lead paragraph now uses the full column (a clash with the Stories layout was squeezing it into a narrow strip; this also fixes the same squeeze on the feeding appeal, page banners and closing text across the site); three even tick rows; tall photo with a sunshine block behind it, a dot pattern and a floating "Since 2009" badge.
- **Hero:** text, buttons and slider dots now sit clear of the pillars strip.
- **Ways to help and Supporter voices:** headings centred; tighter, even gaps (the big gap above the cards and the line before the volunteering note are gone).
- **Closing appeal:** "Every day matters" back to a small label; heading, text and buttons stacked and centred.
- Re-run **Apply latest design** for the "Since 2009" badge.

## 2.37.0
- **Lighter photos.** All bundled photos and slider images recompressed (same sizes and sharpness): 3.6 MB down to 2.9 MB in total; the largest slider image went from 299 KB to 182 KB, and the first slide that every visitor downloads is smaller.
- **Smaller CSS.** New `scripts/build-css.py` rebuilds all minified CSS in one step and strips more whitespace safely: main 115 KB (20 KB gzipped), home 141 KB (23 KB gzipped).
- **Security, section 7 of `inc/security.php`:** theme/plugin code editor disabled in wp-admin, pingbacks and trackbacks off, comments closed on pages and media, application passwords off, and "Lost your password?" limited to 5 requests per hour per connection. Each can be switched off with a filter.
- **Code guide.** New `docs/CODE-GUIDE.md`: where everything lives, how the CSS is organised, how to rebuild and check, and a security summary including the server settings a theme cannot control.

## 2.36.0
- **Home page redesign (senior UI/UX pass).**
  - New signature "pillars" strip that overlaps the bottom of the hero: A safe home, Daily meals, Schooling, Healthcare, each with an icon (2 x 2 on tablets and phones).
  - Intro: balanced 54/46 split, a readable lead (it was squeezed into a narrow column), checklist as tidy tick rows, and a different photo (children with hygiene kits) so the founder photo is no longer used twice.
  - Founder section mirrors the intro (photo on the left) for a better zig-zag rhythm.
  - Programme cards: equal heights, images the same shape, titles aligned, buttons lined up at the bottom, and every card now has "Support" plus "Learn more" (Health card included).
  - Ways to help: the three stacked sections now read as one seamless forest band (the visible seams and lighter strip are gone), centred heading, even cards, pill-style volunteering note.
  - Supporter voice: featured quote card with a yellow quote badge.
  - Closing appeal: centred, larger heading, yellow emphasis (the green-on-green words were unreadable), eyebrow on one line, buttons centred.
  - Phones: full-width buttons, smaller photo accents, no forced title heights.
- **Inner pages, one consistent system.** Page banners are taller with a left-to-right forest overlay, larger headings, readable lead and a three-colour brand stripe; even section spacing; text + photo sections split and centred evenly (stacked on tablets); consistent photo shape and shadow; shared card radius, shadow and hover lift across all card grids; the "Help a child stay safe, fed and in school" band is centred with soft yellow and coral light, readable text and full-width buttons on phones; roomier, accessible forms with a clear focus ring; tidier FAQs and breadcrumbs.
- Re-run **Apply latest design** to get the new home pillars strip, the new intro photo and the Health card buttons.

## 2.35.0
- **Brand heading font bundled.** Nunito (the Smile Logo Kit heading font, SIL Open Font License, licence in `assets/fonts/OFL-Nunito.txt`) is now served from the theme as a single latin variable WOFF2 (`assets/fonts/nunito-latin.woff2`, about 40 KB, weights 200-1000) and preloaded. Headings now match the logo kit exactly on every device; Figtree stays as the fallback. The block editor uses it too.
- **One logo size on every page.** The header logo is locked to 60 px tall (50 px when scrolled, 48/44 px on tablets and phones, 40 px on very small screens) and the footer logo to 72 px, with rules no page or Elementor setting can override. Fixes the oversized logo on Get Involved.
- **Navigation stays visible while scrolling.** The menu used to scroll away because it was only sticky inside the Elementor header wrapper. The wrapper is now pinned: the green top bar scrolls off, and the logo, menu and Donate bar stay at the top and compact slightly with a soft shadow. Works on large screens, tablets and phones, with or without the WordPress admin bar (also when Elementor is off). Anchor links land below the header; motion is reduced for visitors who prefer less animation.

## 2.34.0
- **Page-by-page polish after a full review of all 19 pages on staging.**
- **Logo:** header now uses the kit's no-tagline lockup (full colour) so "SMiLE Community Centre" reads clearly; the footer uses the official Reversed lockup (yellow and coral on Forest Green) with no white panel. New favicon and phone icons from the supplied symbol artwork.
- **Readable buttons everywhere:** white text on green buttons (Where help is needed now, See our gallery), Forest Green text on yellow buttons (footer Donate Now), white outline buttons on dark bands and the slider.
- **Dark green banners** ("Help a child stay safe, fed and in school" and others): white headings and text, yellow accents, yellow primary button, white outline secondary buttons.
- **Home:** Ways to help rebuilt as four even cards with readable titles (its class names clashed with an older component); centred headings in Ways to help, Supporter voices and the closing appeal; closing text readable on the photo; side-by-side buttons; one clean accent behind each photo instead of stacked boxes; comfortable reading width for the intro.
- **Programme pages:** "Our other programmes" cards now show the photo across the whole card (new authentic photos for Education, Feeding and Sports); spacing fixed after emphasised words ("further here").
- **About Us:** mission and vision icons sit above their labels.
- **Stories:** balanced side column and full story titles.
- **WhatsApp chat:** no longer pops a welcome bubble over the page; compact round button on desktop.

## 2.33.0
- **New logo applied (Smile Logo Kit v1.2).** Primary full-colour lockup in the header, the approved reversed colourway on the Forest Green footer, the sign-in screen, and new browser and phone icons (symbol on Sunshine Yellow). 1x/2x/3x PNG and WebP, the master SVG and a 512 px symbol are in `assets/images/brand/`. Sizes respect the kit minimum (full lockup at least 150 px wide).
- **Brand colours and type.** Theme tokens, Elementor global colours and hard-coded colours moved to the kit palette: Forest Green #0B4021, Sunshine Yellow #FDDC05, Warm Coral #ED5534 (eyebrows/accents, darkened to #B8391C for text contrast). Headings use Nunito ExtraBold when installed, otherwise Figtree heavy (rounded, closest match); Newsreader is no longer preloaded. Pill buttons, yellow header rule and footer band.
- **Home page rebuilt to the revised content structure (29 Oct 2026)**, entirely from native Elementor sections, columns, headings, text, images and buttons so every word, photo and link is editable in Elementor: hero slider (new copy "Help us give orphaned and vulnerable children..."), "More than shelter. A chance to move forward.", new Soweto community section (wide photo with caption, photo above text on mobile, links to About Us #community), four programme cards (Education, Feeding Program, HIV/AIDS Counselling & Support, Sports & Talent), feeding appeal, founder introduction, four ways to help with the volunteer availability notice, supporter voice (Ruth Kamau), stories and updates, and a closing appeal.
- **Eight new authentic photos** bundled in `assets/images/photos/` (JPEG for the Media Library, 1200/640 WebP) and added to `demo/images.json` with alt text from the photo plan. New `theme:` sources are copied from the theme instead of downloaded; **Apply latest design** imports any missing bundled photo automatically, and sections fall back to the bundled WebP if a photo is not in the library yet. `medical.jpg` was not added (restricted in the photo plan).
- **Slider**: new slide photos (feeding, founder with children, school uniforms, sports court) and a Sports and Talent slide.
- **Inner pages**: new banner photos on About Us, Our Founder, Education, Feeding and Sports; About Us community section uses the Soweto view and has the anchor `#community`.
- **Office hours**: Mon-Sat 9am-5pm, Sun from 2pm (top bar, contact cards, help box, Smile widgets); Saturday counts as an office day by default.

## 2.32.0
- **The whole site is now editable in Elementor (no Elementor Pro needed)**, following the same pattern as ThemeForest themes such as Ryse.
- **Smile Theme Options (Redux Framework).** Appearance > Smile Theme Options: site header and footer templates, top bar, Donate button, WhatsApp chat, footer line, brand colours, content width and custom CSS. Redux Framework is listed in Smile Setup > Plugins (one click). Without Redux, a built-in screen at the same place keeps header/footer, colours and custom CSS working. Top bar, Donate, WhatsApp and footer line stay in step with Appearance > Customize both ways. All values live in the `smile_options` option.
- **Elementor header and footer.** Build a header or footer as an Elementor template (Templates > Saved Templates, type Section) and choose it site-wide in Smile Theme Options. Any page can choose its own header/footer, the built-in one, or hide them: Elementor > Page settings > Smile header & footer, or the "Smile header & footer" box in the WordPress editor (both save to the same place). Safe fallback: if nothing is chosen, Elementor is off, or the template is missing, unpublished or empty, the built-in header/footer shows exactly as before. Elementor Pro Theme Builder headers still win when present.
- **Smile widgets.** A "Smile Community Centre" panel in Elementor with every section as a widget: all 57 sections plus Site header and Site footer (59), and four building blocks (Logo, Menu links, Social icons, Donate button). Each section widget has Settings (form type, programme, number of stories ...), Texts (every sentence it prints, 791 in total, editable per widget; an empty box keeps the Site text & photos wording), Photos (61 photo slots, per widget) and Style (alignment, heading/text colours and typography, link and button colours) plus Elementor's Advanced tab. Shortcodes keep working, so existing pages are unchanged.
- **Starter templates.** "Create starter header and footer templates" in Smile Theme Options makes two Elementor templates that reproduce the current header and footer, ready to edit. The same templates ship as `elementor-templates/*.json` for Templates > Import.
- Built-in header/footer markup moved to `template-parts/site-header.php` and `site-footer.php`; `header.php`/`footer.php` now choose between the Elementor template and the built-in design.
- New `scripts/build-elementor-widgets.py` generates `inc/elementor/registry.php` and `widget-classes.php` from the shortcodes; it runs in `build-theme-zip.sh`. Re-run after changing any shortcode.
- Elementor's icon font is kept when an Elementor header/footer is in use.

## 2.31.5
- **Old /dev/ links.** Four old pages (Blog, Our goals, Our impact infographic, From a children's home in slums to university) link to 7 photos under /dev/wp-content/uploads/. On the live site those already redirect to the home page, so the photos do not show; the real files are at /wp-content/uploads/... Any old /dev/... address now drops the /dev prefix in one hop (photos go to the real file, /dev/about-us/ goes to /about-us/). The photos themselves will only load after go-live if the old uploads folder is kept.
- Decision recorded (Agnes, 20 Oct 2026): the 9 retired posts stay as redirects; they are not being imported.
- Smile Community Core 1.3.5.

## 2.31.4
- **Old numbered links (?p=2299, ?page_id=263).** The old site's pages link to posts and pages by number. Those numbers are different on the new site, so all 42 of them were 404 on staging. Each old number now redirects in one hop to the right page (27 posts, 15 pages, mapped from the live site on 20 Oct 2026; none of these numbers is used by other content on staging). Previews are not affected. Filter: smile_core_old_id_map. Smile Community Core 1.3.4.
- docs/old-urls.txt now includes the numbered links.

## 2.31.3
- Old category page numbers (/charity/page/2/, /education/page/2/) now always go to page 1 of the matching category. The new archives show a different number of posts per page, so old page numbers could land past the end (/education/page/2/ was still a 404 in 2.31.2). Smile Community Core 1.3.3.
- Verified on staging after 2.31.2: all 18 old tag pages and /blog/page/2/ and /blog/page/3/ now go to Stories; /charity/page/3/ goes to the Charity category.

## 2.31.2
- **Fix: the 2.31.1 tag, blog-page and category-page redirects never ran.** The new tag/blog redirect function was added but not called, and the category "past the last page" check was missing from the shipped file. Both are now in place. Smile Community Core 1.3.2.

## 2.31.1
- **Links found inside old pages, not just the sitemaps.** Crawled all 56 old pages: 123 more linked addresses (feeds, tag pages, archive pages, ?p= links, old /dev/ image paths). All 51 feeds already work. Fixed:
  - 18 old tag pages (/tag/impact/ and others, with /page/N/ or /feed/) were 404 because staging has no tags. They now go to Stories (only when the tag does not exist).
  - /blog/page/2/ and /blog/page/3/ were 404. They now go to Stories.
  - Old category pages past the new last page (/charity/page/3/, /education/page/2/) were 404. They now go to page 1 of that category.
- docs/old-urls.txt now includes these addresses. Smile Community Core 1.3.1.

## 2.31.0
- **No broken links after the switch.** Audited every address in the old site's sitemaps (smilecommunitycentre.org: 28 posts, 16 pages, 12 categories, 19 Oct 2026) against the staging site. All 56 now open a page or 301-redirect to the best match.
- Redirects are now built in (always on), not only saved at activation, so clearing the Redirects screen can no longer break old links. Rules typed at Smile Centre > Redirects still take priority; the screen lists the built-in ones.
- Old category addresses without "/category/" (/charity/, /children/, /orphanages-in-nairobi/, including /page/2/ and /feed/) redirect to the matching category archive.
- Old posts not carried over redirect to the closest page (founder post to Our founder, "half of the world's extreme poor" to Our impact, the COVID announcement to Announcements, the rest to Stories). /education/ goes to the Education category, as on the old site.
- Old SEOPress sitemap files (sitemaps.xml, post-sitemap1.xml and the rest) redirect to the WordPress sitemaps.
- Post addresses: the first administrator visit after the update sets permalinks to /post-name/ (staging used /year/month/day/post-name/), so every old post keeps its exact address. Off switch: smile_core_postname_permalinks filter.
- Smile Community Core 1.3.0.
- Uploaded to staging and verified on 19 Oct 2026: all 59 old addresses (docs/old-urls.txt) end on a 200 page within one redirect. New `scripts/check-old-urls.sh` re-runs the check; AGENTS.md added as the handover file for developers and AI agents.

## 2.30.0
- **Two-step login (two-factor authentication).** The official WordPress.org "Two Factor" plugin is now listed in Smile Setup > Plugins (recommended), so it installs in one click.
- Until it is installed, administrators see a dashboard reminder with an "Install Two Factor" button.
- Once it is active, every staff member who has not set it up sees a dashboard reminder with the six setup steps (authenticator app, QR code, recovery codes, main method, update profile, sign out and in) and a "Set up two-step login" button that opens the right part of their profile. "Remind me in a week" hides it for 7 days (nonce protected).
- Users > All Users shows a "Two-step login: On / Not set up" column when the plugin does not add its own.
- The code screen after the password uses the branded sign-in page: "Two-step verification" heading, large spaced 6-digit code box, tidy links to recovery codes.
- New file inc/two-factor.php; reminders can be switched off with the smile_2fa_reminder filter. Editable texts now 811. Docs updated.

## 2.29.0
- **Speed: banner photo first on every page.** Inner pages (About, Programmes, Donate and the rest) now tell the browser to fetch their banner photo straight away, at high priority and in the right size for the screen, as the home page already did for its first slide. Story pages do the same for their main photo.
- **Lighter photos for new uploads.** Uploads are scaled to at most 1920 px (was 2560 px), PNG photos get WebP copies like JPEGs, and WebP/JPEG quality is 80 (was 84), about 15 to 25% smaller with no visible change.
- **Fewer files for visitors.** The dashboard icon font, the embed helper and the heartbeat script no longer load for visitors (still there for signed-in staff); oEmbed and REST discovery links removed from the page head.
- **Elementor speed settings kept on.** After each theme update, the first administrator visit to the dashboard switches on Elementor's cacheable CSS files, optimised image loading, element caching, lazy background images and local fonts, then clears Elementor's CSS cache once.
- New file inc/performance.php; each part has an off switch (smile_preload_page_hero, smile_light_uploads, smile_trim_frontend, smile_elementor_speed). Docs updated.

## 2.28.0
- **WhatsApp chat fix.** Sending a message could open WhatsApp twice (a new tab and the current page), because the browser returns nothing when a tab is opened in "noopener" mode. It now opens once in a new tab, still safely cut off from the site, and falls back to the same tab only if pop-ups are blocked.
- **Office hours are now settings, not code.** New fields in Smile Centre > Centre details: office opens at (hour), closes at (hour), and "open on Saturdays". They drive both the WhatsApp chat status ("Online now" / "We will reply when the office opens") and the "Office open now" badge on Contact, in Nairobi time. Previously both were fixed at Monday to Friday, 9am to 5pm.
- The "Office open now" badge now also respects weekends (it showed "open" on Saturdays and Sundays before).
- Last fixed English words in scripts made editable in Site text & photos: "Office open now", "Office closed now", "KES", "about US$" (gift calculator), "Play slideshow" and "Pause slideshow" (slider button).
- Full recheck: every PHP file linted, both scripts syntax-checked, quality gate passed; WhatsApp chat reviewed for keyboard use, Escape to close, focus return, the no-JavaScript fallback, the child-privacy note and the CSP form rule.
- Smile Community Core 1.2.2. Editable texts now 792. PRD and SECURITY-AUDIT updated to the current theme and core versions, with a re-check section for 2.28.0 / 1.2.2.

## 2.27.0
- **Nothing hard-coded on the site any more.** Site-wide check of every template and section for fixed wording, numbers, contacts and links.
- Google rating bar: the score (4.7), the number of reviews (111) and the "Read all reviews" link are now settings in Smile Centre > Centre details > Google reviews. Leave the rating empty to hide the bar; the sentence adapts when the count is empty.
- Form drop-down choices (contact subject, partner interest, donation area and method, photo-removal relationship, careers area) are now editable in Site text & photos. Submissions are still checked against the same list.
- Sign-in page logo text uses the organisation name from Centre details; organisation and founder names in the search-engine data are editable.
- Fixed a wrong fallback for the other phone numbers (0720 805 865 corrected to 0720 809 885 to match Centre details).
- Security: links and the WhatsApp chat form that open a new tab now use rel="noopener noreferrer". Full re-check of form handling, nonces, permission checks, AJAX and admin actions, output escaping, redirects, headers and JavaScript found no other issues.
- Smile Community Core 1.2.1 (new Google reviews settings, translatable drop-down choices). Editable texts now 787.

## 2.26.1
- **Sign-in page polish after the first live check.** The heading and logo were being cut off at the top on shorter screens; the card now centres safely and never goes above the top edge.
- Logo, heading, messages, fields, button and the "Lost your password?" / "Back to" links now sit together in one white card, with a divider above the links.
- The photo panel now fills the full height of the page, with a lighter tint on the right so more of the photo shows.
- The language drop-down under the card is hidden (the site is English only).
- The secure-connection note is aligned and narrower; the privacy link is quieter. On phones the card overlaps the photo banner slightly.

## 2.26.0
- **New staff sign-in page.** wp-login.php now uses a split layout: a photo panel on the left (deep green over the feeding photo, "Staff and volunteers" label, headline, three things staff can do, and a "Need access or locked out?" line with the centre's phone and email from Centre details), and a clean sign-in card on the right.
- The card has a "Welcome back" heading (or "Reset your password" / "Choose a new password" on those screens), large rounded fields with a green focus ring, a visible show-password button, a full-width "Sign in" button, softer success and error messages, and a secure-connection note under the card.
- On phones and tablets the photo becomes a short banner above the card.
- The site's own fonts now load on the sign-in page too. Keyboard focus, reduced motion and the core WordPress features (password reset, language switcher, privacy link) are kept.
- Every wording is editable in Smile Centre > Site text & photos. The whole design can be turned off with the `smile_login_design` filter.
- New files inc/login.php and assets/css/login.css (minified to assets/build/login.min.css); docs updated.

## 2.25.0
- **Careers.** New Careers page (/careers/, under Get Involved and in the footer) created automatically on first admin visit: hero, "Why work at Smile" values, open roles, "How we hire" four-step process, talent community form and a Careers FAQ with a recruitment-fraud warning.
- **Jobs** admin (Jobs > Add New Job): title, description, excerpt and a Job details box (type, team, location, hours, pay, start, closing date, reference, apply email or link). Roles list on Careers while published and before their closing date (Nairobi time), then drop off automatically; admin list shows type, closing date and whether the role is live.
- Open roles list with type filter chips, "Closes in N days" flag, and a polished "no open positions" state. Job pages with hero, at-a-glance card, Apply button (email with prefilled subject or external link), share links, safeguarding commitment, closed-role notice.
- Google for Jobs JobPosting structured data on open roles; closed roles are noindex.
- Smile Community Core 1.2.0: new "careers" talent community form (area of work, CV/LinkedIn link, consent to keep details 12 months), stored in Enquiries. Email fallback until the plugin is updated.
- New files inc/careers.php and template-parts/job.php; docs updated.

## 2.24.0
- Social profiles: Instagram, Facebook (facebook.com/smilecommunitycentre) and LinkedIn (linkedin.com/company/smile-community-centre) as matching round icon buttons in the top bar and in a new "Follow us" row in the footer Contact column. Shared helper smile_social_profiles()/smile_social_icons() in inc/template-tags.php; links open in a new tab with accessible labels; also used for the organisation sameAs in structured data. New LinkedIn URL field and Facebook default in Smile Centre settings.

## 2.23.1
- "Read all reviews on Google" now opens the reviews panel of the centre's Google listing directly.

## 2.23.0
- "What people say / Voices from our community" (Our Impact and Get Involved pages) now uses real Google reviews: Melissa Nz, Eugene Emmanuel and Maureen Ngobili (all 5 stars). Added a rating summary bar: 4.7 out of 5 from 111 Google reviews, with a "Read all reviews on Google" link (https://share.google/xseS4IIjXcfL36FcG). Rating checked 13 Oct 2026.

## 2.22.7
- Desktop menu: only one dropdown shows at a time. Hovering a menu item hides any other open dropdown (e.g. Impact on the Our Impact page no longer stays open beside Get Involved).

## 2.22.6
- Fix: on desktop, the dropdown of the section you are in (e.g. About on the About pages) no longer stays stuck open. The current section now auto-opens only in the phone/tablet menu. Desktop dropdowns opened with the arrow close on a click elsewhere or Escape, and resizing to desktop closes any open sub-menus.

## 2.22.5
- "Keep every child in school" slide: enhanced photo of the pupils (noise removed, upscaled from 770px to 1600px, local contrast, colour and sharpening), bundled as assets/images/slides/pupils-1600.webp and -960.webp and used instead of the original imported photo. A photo chosen in the Customizer still wins. Slide photos now have their own alt text.

## 2.22.4
- Home page photo banner ("Give a child a reason to smile today"): the M-Pesa line now shows both Paybill 247247 and Account 147516 in a glass pill, each with a Copy button.

## 2.22.3
- First slide: the smile arc now sits directly under the word "smile." instead of under the start of the line.

## 2.22.2
- Home page: "See your impact" and "Get involved" are now hidden by the theme itself, so they disappear on upload even if "Apply latest design" is not run or the home page was edited in Elementor.

## 2.22.1
- Home page shortened: removed the "See your impact" gift calculator and the "Get involved" section (both remain on the Donate and Get Involved pages). Community section now on cream and Latest stories on white to keep the alternating rhythm.

## 2.22.0
- New first home slide "They eat. They dream. They smile.": the feeding photo with its baked-in text cropped away (bundled in assets/images/slides, 1600 and 960 px WebP), and the message rebuilt as real, editable text on a deep green panel (script "Because of you,", bold headline, italic "They smile." with the smile curve, Donate Now and Feeding Programme buttons, "Change a life today." note). Photo can be swapped in Customizer > Smile: Home slider > Slide 1. Slider now supports up to four slides.

## 2.21.0
- WhatsApp chat: floating green button on every page (round above the tab bar on phones, "Chat with us" pill on larger screens) opening a branded chat card with the centre's avatar, online/offline status from Nairobi office hours, a welcome message, quick topic chips and a message box that opens WhatsApp with the message ready to send. One gentle nudge per visit after 12 seconds (not on Contact or Donate). Keyboard and screen-reader friendly, no third-party scripts. New file inc/whatsapp-chat.php; Customizer toggle under Smile: Header & Footer; all text editable in Site text & photos.

## 2.20.2
- Mobile menu polish: no dot on the parent section (only on the exact page), "Go to <section> →" text link instead of "Overview:", flatter evenly inset sub-menu card, tidier spacing.

## 2.20.1
- Updated to the centre's current logo (four children, one carrying a baby): header, footer, sign-in page, schema logo, theme preview picture, and the umbrella-and-children favicon and phone icons.

## 2.20.0
- **Mobile sub-menus:** tapping a section name (e.g. Get Involved) now opens its sub-menu instead of leaving the menu; an "Overview" link at the top still reaches the section page. One section open at a time; the section you are in opens automatically. Sub-menus are white cards with arrows and a yellow dot for the current page.
- Call and WhatsApp buttons in the menu: label above the number, no wrapping.
- Header menu button: three even bars in a soft round button.

## 2.19.1
- **Smile curve toned down:** now used only under words an editor highlights in a heading (slimmer) and on the founder's signature. Removed from small labels, page banner titles, centred titles, the menu, the phone bar, story cards, impact figures and the footer; those return to clean straight lines or a small dot.

## 2.19.0
- **Mobile menu redesign:** full-width panel with the logo at the top and a round close button; large airy links without boxes or dividers; rotating chevrons instead of +/-; sub-menus indented with a fine guide line; current page marked with the smile curve; staggered slide-in; Donate pill plus Call and WhatsApp quick buttons with icons. Phone bottom bar lighter and glassy, Donate button ringed in white.

## 2.18.1
- Favicon and phone icons: the umbrella with the three children only (no yellow box), transparent for browser tabs, on white for the iPhone home screen.

## 2.18.0
- **The Smile curve:** the yellow crescent from the new logo becomes the site's signature mark: under highlighted heading words (drawn in), beside every small label, under page titles and centred section titles, active menu link, phone tab bar, story card hover, founder quote, impact figures and the top of the footer. Accent yellow warmed towards the logo yellow.

## 2.17.0 (13 Oct 2026)
- **Editable content:** new Smile Centre > Site text & photos screen. All 589 visitor-facing texts are listed by section with search; an empty box restores the original. Photos tab replaces any section photo from the Media Library.
- Testimonial names, partner names, founder signature, impact figures and gift amounts are now editable.
- **Speed:** self-hosted fonts with preload; Elementor icon font only where used; speculative prerender for instant page changes.

## 2.16.0
- New logo bundled in 1x/2x/3x PNG and WebP; site icons (32, 180, 192, 512); login page logo; schema logo; theme preview picture updated.

## 2.15.1
- Every page, story and topic gets a title and description; SEOPress filters fed from one resolver; hand-typed values always win.

## 2.15.0
- Connected schema.org graph (NGO, WebSite, pages, breadcrumbs, BlogPosting); keyword-mapped titles and descriptions; indexing rules; `docs/SEO.md`.

## 2.14.1
- Compact gift calculator: one card, two bands.

## 2.14.0
- Security hardening module (headers, HTTPS, login limits, sessions, passwords, enumeration, XML-RPC, CORS); form, importer, JS and SQL fixes; Smile Community Core 1.1.0; README, ARCHITECTURE, ARCHITECTURE-ESSENTIALS, PRD, SECURITY-AUDIT; security checks in the quality gate.

## 2.13.x
- Stories lead story as a clean card, one-line topic bar; Founder quote full-width band; Goals and Safeguarding upgrades that work without re-applying layouts.

## 2.12.x
- Stories "Top stories", topic labels, "Stay close to Smile" band; Volunteer places notice restored.

## 2.11.0
- Founder "Her journey" timeline and portrait quote; Volunteer international facts beside the form.

## 2.10.x
- Stories topics without sideways scroll, 12 stories per page; theme preview picture.

## 2.9.x
- Clean top bar, header and banners; 53 broken CSS rules fixed; full-site scan; quality gate in the build; closing band on one line.

## 2.8.x
- Balanced titles; About, Founder and Goals components; PHP parse error fix; slider disappearing fix; speed tweaks.

## 2.4 to 2.7
- Gallery wall of hope, Impact stats, Stories journal, Get Involved family, Contact hub, Donate.

## 1.7 to 2.3
- Speed (minified bundles, fonts, preload), inner pages, programme pages, signature details, mobile navigation, balance layer.
