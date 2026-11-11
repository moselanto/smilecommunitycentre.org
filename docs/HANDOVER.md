# HANDOVER: Smile Community Centre website (state at 12 Nov 2026, 02:45 EAT)

Read this first, then `AGENTS.md` (rules + work log), `ARCHITECTURE-ESSENTIALS.md`, `docs/workflow.md`, `docs/redirects.md`, `docs/SEO.md` and `docs/performance.md`.

## 1. Where things stand

| Item | Value |
|---|---|
| Live site | https://smilecommunitycentre.org/ (live since 4 Nov 2026) |
| Staging | https://topnotchmall.co.ke/smilescommunity/ (no longer kept in sync with live; deploy straight to live) |
| Host | cPanel, leader.vivawebhost.com; WP Super Cache for page caching |
| Theme | `smile-community-child` **2.54.0** (child of Hello Elementor), commit 1d32b3f |
| Core plugin | Smile Community Core **1.6.2**, bundled at `wp-content/themes/smile-community-child/plugins/smile-community-core/` (the only maintained copy) |
| SEO plugin | Rank Math (SEOPress removed in 2.49.0). Rank Math JSON-LD, Analytics, Schema and Local SEO modules are OFF; the theme's `inc/seo.php` graph is the only schema |
| Analytics | GA4 `G-DJQ2PBW3YN` (property 270195586) + GTM `GTM-TDZHTDQF`, printed by `inc/analytics.php`, loaded on first interaction or idle |
| Google accounts | peterwafulah@gmail.com owns Search Console (domain property), GA4, GTM and Bing Webmaster Tools. The Google Business Profile is NOT in this account |
| Decisions contact | Agnes |
| Release zips | Town library: Smile Community Centre / releases / `smile-community-child-<version>.zip` |

## 2. How to ship a change (do not skip steps)

1. Edit source files only (never the `assets/build/*` output by hand).
2. Bump the version in `functions.php` (`define(... 'X')`) and `style.css` (`Version:`). If Core changed, bump its header and constant too.
3. `python3 scripts/build-css.py` then `bash scripts/build-theme-zip.sh`. The zip script runs: critical CSS (`build-critical-css.py`), front-page CSS purge (`purge-front-css.py`), Elementor widgets build, string extraction, JS/CSS minification (needs `pip install rjsmin rcssmin`, otherwise it silently keeps the old .min files) and `check-theme.py` (PHP/JS/CSS syntax + security patterns). Output: `dist/smile-community-child.zip`.
4. **If Home was edited in Elementor**, run `python3 scripts/purge-front-css.py --refresh` before building, or new classes on Home will be missing from the purged CSS.
5. Add a `CHANGELOG.md` entry, commit everything (sources + build files) to `main`, add a dated line to the AGENTS.md work log and update its "Current state" line.
6. Deploy in wp-admin: Appearance > Themes > Add New > Upload > **Replace current with uploaded**. Do NOT click "Apply latest design". If Core changed: Appearance > Smile Setup > Plugins > update Core and confirm the version.
7. Settings > WP Super Cache > Delete Cache, then start a preload. Log out (logged-in users bypass cache and tracking).
8. If pages/permalinks/redirects changed: `bash scripts/check-old-urls.sh https://smilecommunitycentre.org`.
9. Run PageSpeed (pagespeed.web.dev, new analysis each time, at least 3 runs because the test machine varies a lot) and log scores in `docs/performance.md`.

Debug helpers: `?smile_ga_debug=1` (GA4 DebugView), `?smile_full_css=1` (compare against unpurged CSS).

## 3. Work completed (10-12 Nov 2026, Ray / Town)

Full detail is in `CHANGELOG.md` (2.49.0 to 2.54.0).

- **SEO platform (2.49.0-2.49.1, Core 1.6.0-1.6.1):** Rank Math replaced SEOPress (data imported first). The theme feeds keyword-mapped titles and descriptions from `demo/pages.json`. Old sitemap URLs 301 to `/sitemap_index.xml`, feeds send `X-Robots-Tag: noindex, follow`, robots.txt has an AI-crawler policy (`docs/crawler-access-matrix.md`) and IndexNow is on for posts and pages.
- **Search engines:** sitemap_index.xml submitted in Search Console (status Success, 52 URLs, 80 pages indexed, 30 valid breadcrumbs, no manual actions). Bing Webmaster Tools set up by importing from Search Console, with the sitemap submitted.
- **Speed and layout shift (2.50.0-2.53.9):**
  - critical CSS cut from 81 KB to 55 KB on Home;
  - footer/template CSS loads async;
  - small Elementor CSS inlined;
  - GA4/GTM deferred;
  - front-page CSS purged (main 126 KB to 48 KB, home 159 KB to 87 KB);
  - fonts self-hosted with metric-matched fallbacks;
  - many CLS fixes (slider section, h1, Donate form CSS, section padding and Caveat line boxes).
- **Content clean-up (2.53.0):** staging links (topnotchmall.co.ke/smilescommunity) replaced in 8 posts by a one-off upgrade (`inc/content-fixes.php`, option `smile_content_fix_2530`) and editorial labels removed. Contrast failures were cleared across the site (2.53.3).
- **Meta descriptions (2.53.10):** Home (156 characters) and About Us (145) shortened in the repo AND in the hand-typed Rank Math fields. A hand-typed Rank Math field always overrides the theme, so edit both.
- **SEO audit:** all 52 sitemap URLs return 200, with one H1, a unique title and description, a self-canonical and valid JSON-LD (Organization, WebSite, WebPage types, BreadcrumbList, BlogPosting). Not-indexed reasons in Search Console are all expected or redirected (`/shop/` and `/cart/` 301 to /donate/).
- **GA4 key events (2.54.0):** `donation_start`, `mpesa_details_copy`, `donation_complete`, `contact_submit`, `volunteer_enquiry`, `partner_enquiry`, `enquiry_submit` and `whatsapp_click`, sent by the theme's gtag (`assets/js/main.js`). All 8 are marked as key events in GA4. Four were verified in DebugView and Realtime. GTM live version 2 has **0 tags and must stay that way** (a GA4 tag there would double count). Details are in `docs/SEO.md`.
- **Care-strip shift (2.54.0):** a "Figtree Fallback" font face (Arial with size and ascent overrides) means a late Figtree no longer moves the Home care strip. Home PageSpeed after 2.54.0 is in the table below.

  | Device | Performance (3 runs) | CLS |
  |---|---|---|
  | Desktop | 98 / 100 / 100 | 0 / 0 / 0 |
  | Mobile | 94 / 96 / 93 | 0.001 max (slider pause button) |

## 4. Pending, in priority order

### Needs the owner or centre (blocked on people, not code)
1. **Google Business Profile.** peterwafulah@gmail.com manages no businesses. The current profile owner (a sc...@gmail.com account) must add Peter as manager, or the centre must confirm no profile exists so one can be created. Then align name/address/phone with the site and add the website URL.
2. **15 content questions** in `docs/content-drafts.md` Part 3 (founding year 2009 vs 2016, registration wording, Sunday hours, official Instagram, sponsorship, food list, feeding figure, child count with "as at" date, card giving/bank details, receipts, international platforms, safeguarding sentence, Resources location, reviewer, approval of Q&A blocks and Articles 1-3). Nothing in that file may be published until approved (no new site wording without approval).
3. **Launch confirmations** in `docs/known-limitations.md` (child count, founder name spelling, M-Pesa Paybill 247247 / account 147516 and account name, volunteer fee and status, testimonials, WhatsApp hours, photo consent for 24 photos tagged "pending-review", Privacy Policy review, giving amounts).
4. **Paystack:** confirm live keys/approval for KES and USD card giving (`docs/paystack-donations.md`).

### Technical / SEO (can be done by the next agent)
5. **2 Dec 2026 – Core Web Vitals validation.** A scheduled Town task fires 2 Dec 06:55 UTC: open Search Console > Core Web Vitals (mobile), and if field LCP is now under 4 s click "Validate fix" on the 33 URLs flagged "LCP longer than 4 s" (report dated 8 Nov predates releases 2.50-2.54; lab mobile LCP is now 2.7-3.0 s). Also check the Bing sitemap status. Log the result in `docs/SEO.md` and the AGENTS.md work log.
6. **Search Console 404 group:** click "Validate fix" (the URLs now 301).
7. **Untested GA4 events:** `donation_start` / `donation_complete` (need a real or test-mode Paystack payment) and `partner_enquiry` / `enquiry_submit` (same code path as verified forms; confirm when a real enquiry arrives). Check GA4 > Reports > Engagement > Events after a few days for key-event counts.
8. **Accessibility:** one mobile PageSpeed run showed Accessibility 96 from a contrast warning that did not repeat. Re-check the Home closing band if it appears again.
9. **Unsized images** `wp-image-1048` / `wp-image-1053` (Home, below the first screen): they cause no measured shift, but adding explicit width/height in the Elementor image widgets would remove the PageSpeed hint.
10. **Directory listings / external authority** (consistent NAP) once the Business Profile details are confirmed.
11. **Content and AEO publishing** once item 2 is answered: Q&A blocks on 10 pages (with FAQPage schema only where the Q&A is visible), Resources articles 1-3, then outlines 4-20.
12. **Old posts:** Agnes decided the 9 retired posts stay as redirects (20 Oct). No action unless she changes her mind.

## 5. Gotchas

- Never write a file with `open(f,'w').write(open(f).read()...)`: the file is emptied before it is read. Read into a variable first.
- PageSpeed scores swing between runs, so always take 3 runs. Mobile perf has ranged from 57 to 97 on identical code because of TBT on the test machine.
- The PageSpeed API quota on the shared key is exhausted, so use the web UI.
- Rank Math values typed by hand override `demo/pages.json`.
- Logged-in staff are not tracked in GA4 and bypass WP Super Cache, so always test logged out.
- Never publish children's names, locations or identifying details. Put no secrets in code and no invented facts in schema.
