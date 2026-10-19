# URL and redirect map

Goal: when this site replaces smilecommunitycentre.org, **no old link breaks**. Every address in the old site's sitemaps (28 posts, 16 pages, 12 categories) either opens the same address or 301-redirects to the best match.

Verified on staging (topnotchmall.co.ke/smilescommunity) on 19 Oct 2026 after theme 2.31.0 / Core 1.3.0 was uploaded: all 56 sitemap addresses plus /sitemaps.xml, /post-sitemap1.xml, /charity/page/2/ and /charity/feed/ end on a 200 page (at most one redirect).

## How it works (plugin: `includes/redirects.php`)

1. **Kept unchanged:** home, `/about-us/`, `/our-goals/`, `/our-founder/`, `/education-program/`, `/feeding-program/`, `/sports/`, `/donate/`, `/volunteer/`, `/contact-us/`, `/faq-page/`, `/privacy-policy/` and the 18 old posts that were carried over.
2. **Post addresses are `/post-name/`** like the old site. The first administrator dashboard visit after a Core update sets the permalink structure to `/%postname%/` (staging had `/year/month/day/post-name/`). Off switch: `smile_core_postname_permalinks` filter.
3. **Built-in map** `smile_core_builtin_redirects()` is always active (table below). Rules typed at **Smile Centre > Redirects** take priority. Filter: `smile_core_builtin_redirects`.
4. **Old category addresses** had no `/category/` base. `smile_core_category_redirect()` sends `/charity/`, `/charity/page/2/` and `/charity/feed/` (any existing category) to `/category/<slug>/...`.
5. All redirects run only when WordPress would show a 404, so they never hide a real page.

## Built-in map

| Old URL | New URL |
|---|---|
| /blog/ | /stories/ |
| /photos/ | /gallery/ |
| /sitemap/ | / |
| /hiv-and-the-community/ | /health-and-wellbeing/ |
| /education/ (old category) | /category/education/ |
| /a-day-in-the-life-of-our-founder-margrets-work-routine-in-slums/ | /our-founder/ |
| /did-you-know-children-account-for-half-of-the-worlds-extreme-poor/ | /our-impact/ |
| /real-life-stories-from-smile-community-centre-childrens-home/ | /stories/ |
| /what-vulnerability-feels-like/ | /stories/ |
| /life-as-it-is/ | /stories/ |
| /a-journey-cut-short-reflections-on-the-titanic-submarine-experience/ | /stories/ |
| /empowering-kenyan-youth-the-significance-of-the-hustler-fund-in-informal-settlements/ | /stories/ |
| /building-bridges-the-shared-commitment-of-comoros-and-kenya-in-supporting-orphaned-and-vulnerable-children/ | /stories/ |
| /smile-community-centre-childrens-home-announcement-in-regards-to-the-fifteenth-15th-presidential-address-on-the-coronavirus-pandemic/ | /category/announcements/ |
| /sitemaps.xml, /sitemap.xml, /sitemap_index.xml | /wp-sitemap.xml |
| /post-sitemap1.xml | /wp-sitemap-posts-post-1.xml |
| /page-sitemap1.xml | /wp-sitemap-posts-page-1.xml |
| /category-sitemap1.xml | /wp-sitemap-taxonomies-category-1.xml |

New URLs: `/our-programmes/`, `/child-care-and-shelter/`, `/health-and-wellbeing/`, `/our-impact/`, `/gallery/`, `/stories/`, `/get-involved/`, `/partner-with-us/`, `/safeguarding/`, `/careers/`.

## Open items

- **Decided (Agnes, 20 Oct 2026):** the 9 old posts not carried over stay as redirects to the closest page; do not import them unless she asks.
- **Old /dev/ links (2.31.5):** old pages link to 7 photos under /dev/wp-content/uploads/2021/... (kidskusoma2-1, kusoma1_size, kusoma2, kusoma3, Infographic-724x1024, background-about, sampleheader). Any /dev/... address now redirects to the same path without /dev. The files exist on the live site at /wp-content/uploads/2021/..., not on staging.
- **Old numbered links (2.31.4):** 42 old ?p= / ?page_id= numbers map to their pages (`smile_core_old_id_map()`).
- When the new site goes live on smilecommunitycentre.org, keep the old `wp-content/uploads/` files on the server: old pages and other websites link straight to image files there.
- If SEOPress sitemaps are switched on in production, `/sitemaps.xml` will be served by SEOPress and the sitemap redirects simply stop firing (fine).
- After launch, re-run the check: every old sitemap URL must end on a 200 page.
