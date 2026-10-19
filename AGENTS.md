# AGENTS.md: start here (for the next AI agent or developer)

Read this file, then `ARCHITECTURE-ESSENTIALS.md`, `docs/workflow.md` and `docs/redirects.md` before changing anything. Append to the log at the bottom when you finish.

## The project

- **Live (old) site:** https://smilecommunitycentre.org/ (old "helpinghands" theme, SEOPress sitemaps at /sitemaps.xml).
- **Redesign (staging):** https://topnotchmall.co.ke/smilescommunity (WordPress in a sub-folder).
- **Code:** this repo. Theme `wp-content/themes/smile-community-child/` (child of Hello Elementor) with the **Smile Community Core** plugin bundled at `wp-content/themes/smile-community-child/plugins/smile-community-core/` (this bundled copy is the one that ships; `wp-content/plugins/smile-community-core/` is an older 1.0.0 copy, keep its `includes/redirects.php` in sync).
- **Plan:** when finished, the staging site replaces the live site on smilecommunitycentre.org.

## Standing rules

- **No broken links.** Every URL the old site has (its sitemaps, plus category pagination/feeds) must open or 301-redirect on the new site. See `docs/redirects.md`. Never remove a built-in redirect without a replacement.
- Post permalinks must stay `/%postname%/` (same as the old site).
- Release flow: edit, bump version in `style.css` and `functions.php` (and the Core header/constant if the plugin changed), `bash scripts/build-theme-zip.sh`, commit to `main`, upload zip, add a `CHANGELOG.md` entry.
- Never publish children's names, locations or identifying details. No secrets in code.

## Current state (19 Oct 2026, 23:30 EAT)

- Theme **2.31.0** with Core **1.3.0** (commit d74c3d8) is uploaded on staging and verified: all 56 old sitemap URLs plus old sitemap files and category pagination/feeds end on a 200 page with at most one redirect. Permalinks on staging are now `/%postname%/`.

## How to re-check old links

`bash scripts/check-old-urls.sh` (staging) or `bash scripts/check-old-urls.sh https://smilecommunitycentre.org` (after go-live). It reads `docs/old-urls.txt` (every old sitemap address, saved here because the old sitemaps disappear at go-live) and fails if any address does not end on a 200 page within one redirect. Run it after every release that touches pages, permalinks, categories or redirects. The host rate-limits bursts, so the script checks one address at a time.

## Open items

1. Decide whether to import the 9 old posts that were not carried over (list in `docs/redirects.md`); they currently redirect to general pages.
2. At go-live on smilecommunitycentre.org: keep old `wp-content/uploads/` files, turn search-engine indexing on (Site setup ticks "discourage" on staging), then re-run the old-URL check against the live domain.

## Work log

- **20 Oct 2026, 00:35 EAT:** Mapped the old site's 42 numbered links (?p=ID) to their addresses via the live REST API; on staging all 42 were 404 and none of those IDs is used by other staging content. Committed 2.31.4 / Core 1.3.4: `smile_core_old_id_redirect()` (template_redirect priority 0) sends each old ?p= / ?page_id= to the right page in one hop. Needs upload + plugin update, then `scripts/check-old-urls.sh`. Still open: /dev/ image links on old pages, decision on the 9 retired posts, go-live steps.

- **20 Oct 2026, 00:20 EAT:** 2.31.3 / Core 1.3.3 uploaded and verified: all 86 addresses in `docs/old-urls.txt` end on a 200 page with at most one redirect (/education/page/2/ now goes to /category/education/). Note: the staging host times out when hit with more than about 3 requests at once; check gently.

- **20 Oct 2026, 00:10 EAT:** 2.31.2 / Core 1.3.2 uploaded and verified: tag pages and /blog/page/N/ go to Stories, /charity/page/3/ goes to the category. One gap left: /education/page/2/ landed on a 404 (new archives use more posts per page than `posts_per_page`). Committed 2.31.3 / Core 1.3.3: old category page numbers always go to page 1 of the category. Needs upload + plugin update, then `scripts/check-old-urls.sh`. Still open: ?p=<id> links (needs the old ID-to-slug map; awaiting Agnes's approval to look it up on the live site), /dev/ image links, decision on the 9 retired posts.

- **19 Oct 2026, 23:55 EAT:** 2.31.1 / Core 1.3.1 were uploaded but the tag, /blog/page/N/ and category-overflow redirects did not work: the shipped redirects.php defined `smile_core_archive_redirect()` but never called it, and lacked the category max-page check (lost when several edits were made to the same file at once). Fixed in 2.31.2 / Core 1.3.2. Lesson: after editing `includes/redirects.php`, confirm every new function is called, and after upload run `scripts/check-old-urls.sh`. Remember the bundled Core plugin only updates when an admin clicks install/update in Appearance > Smile Setup > Plugins.

- **19 Oct 2026, 23:40 EAT:** Crawled all 56 old pages for links not in the sitemaps (123 found). All 51 feed addresses already work. Committed 2.31.1 / Core 1.3.1 (not yet uploaded): 18 old tag pages and /blog/page/N/ go to Stories; old category pages past the new last page go to page 1. Still open: 41 old `?p=<id>` links (IDs differ on the new site, so they may 404 or open the wrong post; needs the old ID-to-slug map from the live site, then a `?p=` redirect map), and 7 old `/dev/wp-content/uploads/...` image links on live pages (probably already broken on the live site; unverified).

- **19 Oct 2026:** URL audit of the old site vs staging. Before: 21 old URLs returned 404 and 18 posts only resolved via date-permalink redirects. Shipped 2.31.0 / Core 1.3.0: built-in redirect map, category-base fallback, old sitemap redirects, one-time switch to `/%postname%/`. Uploaded and verified the same evening. Docs: `docs/redirects.md` rewritten, this file added.
