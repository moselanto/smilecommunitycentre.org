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

- **19 Oct 2026:** URL audit of the old site vs staging. Before: 21 old URLs returned 404 and 18 posts only resolved via date-permalink redirects. Shipped 2.31.0 / Core 1.3.0: built-in redirect map, category-base fallback, old sitemap redirects, one-time switch to `/%postname%/`. Uploaded and verified the same evening. Docs: `docs/redirects.md` rewritten, this file added.
