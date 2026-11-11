# AGENTS.md: start here (for the next AI agent or developer)

Read this file, then `ARCHITECTURE-ESSENTIALS.md`, `docs/workflow.md` and `docs/redirects.md` before changing anything. Append to the log at the bottom when you finish.

## The project

- **Live (old) site:** https://smilecommunitycentre.org/ (old "helpinghands" theme, SEOPress sitemaps at /sitemaps.xml).
- **Redesign (staging):** https://topnotchmall.co.ke/smilescommunity (WordPress in a sub-folder).
- **Code:** this repo. Theme `wp-content/themes/smile-community-child/` (child of Hello Elementor) with the **Smile Community Core** plugin bundled at `wp-content/themes/smile-community-child/plugins/smile-community-core/` (this bundled copy is the one that ships; `wp-content/plugins/smile-community-core/` is an older 1.0.0 copy, keep its `includes/redirects.php` in sync).
- **Production:** live at https://smilecommunitycentre.org since November 2026 (cPanel host leader.vivawebhost.com). Server configuration lives in `docs/server/`.
- **Plugin copy:** the only maintained copy of Smile Community Core is the one bundled in the theme. The old standalone copy in `wp-content/plugins/` was removed.

## Standing rules

- **No broken links.** Every URL the old site has (its sitemaps, plus category pagination/feeds) must open or 301-redirect on the new site. See `docs/redirects.md`. Never remove a built-in redirect without a replacement.
- Post permalinks must stay `/%postname%/` (same as the old site).
- Release flow: edit, bump version in `style.css` and `functions.php` (and the Core header/constant if the plugin changed), `bash scripts/build-theme-zip.sh`, commit to `main`, upload zip, add a `CHANGELOG.md` entry.
- Never publish children's names, locations or identifying details. No secrets in code.

## Current state (11 Nov 2026)

- Live: theme **2.52.0** / Core **1.6.1**, Rank Math SEO (SEOPress removed). See `docs/SEO.md` and `docs/crawler-access-matrix.md`.

## Earlier state (19 Oct 2026, 23:30 EAT)

- Theme **2.31.0** with Core **1.3.0** (commit d74c3d8) is uploaded on staging and verified: all 56 old sitemap URLs plus old sitemap files and category pagination/feeds end on a 200 page with at most one redirect. Permalinks on staging are now `/%postname%/`.

## How to re-check old links

`bash scripts/check-old-urls.sh` (staging) or `bash scripts/check-old-urls.sh https://smilecommunitycentre.org` (after go-live). It reads `docs/old-urls.txt` (every old sitemap address, saved here because the old sitemaps disappear at go-live) and fails if any address does not end on a 200 page within one redirect. Run it after every release that touches pages, permalinks, categories or redirects. The host rate-limits bursts, so the script checks one address at a time.

## Open items

1. Decide whether to import the 9 old posts that were not carried over (list in `docs/redirects.md`); they currently redirect to general pages.
2. At go-live on smilecommunitycentre.org: keep old `wp-content/uploads/` files, turn search-engine indexing on (Site setup ticks "discourage" on staging), then re-run the old-URL check against the live domain.

## Work log

- **11 Nov 2026, 12:45 EAT (Ray / Town):** 2.52.0 live (small Elementor/Hello CSS inlined, GA4/GTM deferred to first interaction or idle, story image box reserved). PageSpeed mobile: Home 97, Donate 97, story 99; desktop 99 on all three (docs/performance.md). GA4 Realtime still records visits. Open: Home mobile CLS 0.095 (slide title font swap, unsized wp-image-1053), story h1 CLS 0.061, staging links and visible editorial labels in the sponsor-guide story.
- **11 Nov 2026, 12:40 EAT (Ray / Town):** Content drafts for the SEO plan saved in `docs/content-drafts.md` (Q&A blocks for 10 pages, full drafts of 3 Resources articles, outlines for 17 more, and 15 facts the centre must confirm). Not published: needs the centre's approval first.
- **11 Nov 2026, 12:25 EAT (Ray / Town):** 2.51.0 (critical CSS 81 KB to 55 KB on home, footer template CSS async) and 2.51.1 (fixed home desktop CLS 0.815 from missing `.smile-slider-sec` critical rules) installed live; caches cleared and preloaded. PageSpeed scores in docs/performance.md: Home mobile 70 / desktop 94, Donate mobile 73 / desktop 94, story mobile 92 / desktop 95. Next: mobile FCP/LCP (render-blocking Elementor CSS, analytics JS), story CLS (featured image), contrast. A scheduled check on 2 Dec 2026 will validate Core Web Vitals in Search Console and check the Bing sitemap.
- **11 Nov 2026, 12:00 EAT (Ray / Town):** Bing Webmaster Tools set up under peterwafulah@gmail.com: site imported from Google Search Console (verified through Search Console, no separate code), sitemap_index.xml submitted (status Processing; the previous sitemap showed 56 URLs). IndexNow comes from Rank Math Instant Indexing. Mobile Core Web Vitals: 33 URLs with poor LCP (4.2s, mostly old-site data). Released 2.50.0 (hero preload, 1200w slides, no Caveat preload); details in docs/performance.md. Validate in Search Console around 2 Dec 2026.
- **11 Nov 2026, 11:30 EAT (Ray / Town):** 2.49.0 installed live, SEOPress imported into Rank Math then deleted, Rank Math sitemap + Instant Indexing on, Analytics/Schema/Local SEO modules off, sitemap_index.xml submitted in Search Console (old sitemaps.xml removed). curl checks passed except /sitemaps.xml taking two hops; fixed in 2.49.1 / Core 1.6.1. No /shop/ or /cart/ links remain on Home or Our Goals; the Search Console referrers were historical.
- **11 Nov 2026 (Ray / Town):** Merged PR #22 into main (squash ebb594c). Released 2.49.0 / Core 1.6.0: Rank Math replaces SEOPress (theme feeds titles/descriptions, Rank Math JSON-LD off, theme graph is the only schema), GA4 G-DJQ2PBW3YN + GTM-TDZHTDQF printed by `inc/analytics.php`, robots.txt AI-crawler policy + `/llms.txt` + feed noindex (`inc/ai-discovery.php`), redirects for Search Console 404s (/shop/, /cart/) and old .html addresses, sitemap redirects to `/sitemap_index.xml`. Google: Search Console, GA4 (stream now https) and GTM owned by peterwafulah@gmail.com; NURA Beauty's account removed. Business Profile managed by sc...@gmail.com (no access yet). Open: Business Profile access, Sunday hours and Instagram handle, 33 poor mobile Core Web Vitals URLs, Bing Webmaster Tools, content/AEO work from the SEO plan document.

- **4 Nov 2026:** Go-live fixes on production: permalinks re-saved (inner pages were returning 500), Elementor URLs moved from staging to the live domain (110 rows), indexing switched on, `wp-config.php` set to 0600, and backup ZIPs moved out of `public_html`. Code 2.46.0 / Core 1.3.7: redirect target validation and trusted-proxy client IP. Open: `/wp-login.php` redirects to 127.0.0.1 because of a host-level rule (VivaWebHost ticket).
- **29 Oct 2026 (afternoon):** 2.34.0 on branch `feature/site-polish-2-34` (PR): fixes from a desktop review of all 19 staging pages (logo legibility, button contrast, dark-banner text, home Ways to help/closing layout, related-programme cards, About mission icons, Stories column, WhatsApp pop-up). After merge: build zip, upload, Apply latest design, hard-refresh.

- **29 Oct 2026:** 2.33.0 on branch `feature/brand-refresh-2-33` (PR): new logo (Logo Kit v1.2) and brand palette, home page rebuilt from the revised content document with native Elementor widgets, 8 bundled photos with automatic import, new slider and banner photos, new office hours. Quality gate passes (PHP lint not run in this environment: php was unavailable, so lint on staging/CI). After merge: build zip, upload, click **Apply latest design**, check home, About Us (#community), programme pages and footer logo on a phone. Copy, captions and photo use still need the Centre's approval before go-live. Optional: add Nunito woff2 to `assets/fonts/` for exact brand headings.

- **27 Oct 2026:** 2.32.0 on branch `feature/elementor-builder` (PR, not on main or staging yet): Smile Theme Options (Redux + built-in fallback), Elementor header/footer templates with per-page choice and fallback, 59 Smile Elementor widgets + 4 blocks generated from the shortcodes (`scripts/build-elementor-widgets.py`), starter templates. Quality gate passes (PHP 8.3 lint). Staging has Elementor 4.3.5, no Pro, no Redux. After merge: upload zip, install Redux from Smile Setup, create starter templates, test in Elementor.

- **20 Oct 2026, 00:55 EAT:** 2.31.5 / Core 1.3.5 uploaded and verified: all 96 addresses in `docs/old-urls.txt` (including the numbered ?p= links) end on a 200 page with at most one redirect; ?page_id=263 and /dev/about-us/ also work. /dev/ photo links redirect to /wp-content/uploads/... correctly; those files return 404 on staging only because the photos are not on the staging server (they exist on live). Go-live not yet.

- **20 Oct 2026, 00:45 EAT:** Decisions from Agnes: keep the 9 retired posts as redirects (do not import); go-live is NOT yet, more changes to the new site are coming first. Checked the 7 old /dev/ photo links: on the live site they already 301 to the home page (photos broken there too); the real files are at /wp-content/uploads/2021/... on live and missing on staging. Committed 2.31.5 / Core 1.3.5: any /dev/... address drops the /dev prefix. 2.31.4 and 2.31.5 both need upload + plugin update, then `scripts/check-old-urls.sh`. At go-live the old uploads folder must be kept so those photos load.

- **20 Oct 2026, 00:35 EAT:** Mapped the old site's 42 numbered links (?p=ID) to their addresses via the live REST API; on staging all 42 were 404 and none of those IDs is used by other staging content. Committed 2.31.4 / Core 1.3.4: `smile_core_old_id_redirect()` (template_redirect priority 0) sends each old ?p= / ?page_id= to the right page in one hop. Needs upload + plugin update, then `scripts/check-old-urls.sh`. Still open: /dev/ image links on old pages, decision on the 9 retired posts, go-live steps.

- **20 Oct 2026, 00:20 EAT:** 2.31.3 / Core 1.3.3 uploaded and verified: all 86 addresses in `docs/old-urls.txt` end on a 200 page with at most one redirect (/education/page/2/ now goes to /category/education/). Note: the staging host times out when hit with more than about 3 requests at once; check gently.

- **20 Oct 2026, 00:10 EAT:** 2.31.2 / Core 1.3.2 uploaded and verified: tag pages and /blog/page/N/ go to Stories, /charity/page/3/ goes to the category. One gap left: /education/page/2/ landed on a 404 (new archives use more posts per page than `posts_per_page`). Committed 2.31.3 / Core 1.3.3: old category page numbers always go to page 1 of the category. Needs upload + plugin update, then `scripts/check-old-urls.sh`. Still open: ?p=<id> links (needs the old ID-to-slug map; awaiting Agnes's approval to look it up on the live site), /dev/ image links, decision on the 9 retired posts.

- **19 Oct 2026, 23:55 EAT:** 2.31.1 / Core 1.3.1 were uploaded but the tag, /blog/page/N/ and category-overflow redirects did not work: the shipped redirects.php defined `smile_core_archive_redirect()` but never called it, and lacked the category max-page check (lost when several edits were made to the same file at once). Fixed in 2.31.2 / Core 1.3.2. Lesson: after editing `includes/redirects.php`, confirm every new function is called, and after upload run `scripts/check-old-urls.sh`. Remember the bundled Core plugin only updates when an admin clicks install/update in Appearance > Smile Setup > Plugins.

- **19 Oct 2026, 23:40 EAT:** Crawled all 56 old pages for links not in the sitemaps (123 found). All 51 feed addresses already work. Committed 2.31.1 / Core 1.3.1 (not yet uploaded): 18 old tag pages and /blog/page/N/ go to Stories; old category pages past the new last page go to page 1. Still open: 41 old `?p=<id>` links (IDs differ on the new site, so they may 404 or open the wrong post; needs the old ID-to-slug map from the live site, then a `?p=` redirect map), and 7 old `/dev/wp-content/uploads/...` image links on live pages (probably already broken on the live site; unverified).

- **19 Oct 2026:** URL audit of the old site vs staging. Before: 21 old URLs returned 404 and 18 posts only resolved via date-permalink redirects. Shipped 2.31.0 / Core 1.3.0: built-in redirect map, category-base fallback, old sitemap redirects, one-time switch to `/%postname%/`. Uploaded and verified the same evening. Docs: `docs/redirects.md` rewritten, this file added.
