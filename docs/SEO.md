# Smile Community Centre: SEO, AEO, GEO and structured data

## Verified organisation facts

| Field | Verified value |
|---|---|
| Public name | Smile Community Centre |
| Founder | Margret Nekesa Khaemba |
| Registration category | Community Based Organisation (CBO) |
| Children supported | 84 (management-confirmed; date and scope required before public use) |
| Volunteer availability | Periodic; subject to current needs, capacity and safeguarding |
| Physical address | Mzesa, Soweto, Kayole, Nairobi, Kenya |
| Official website | https://smilecommunitycentre.org/ |

Do not infer NGO registration from CBO status. Do not publish an unverified postal box, postcode, registration number, volunteer fee, accommodation arrangement or placement capacity. The child count is a time-sensitive impact statistic, not a permanent organisation attribute; if shown publicly, identify its "as at" date and what "supported" means.

## Search strategy and page mapping

The objective is qualified discovery for people in Kenya and international audiences seeking child welfare support, ways to give, volunteering and corporate partnerships. No ranking or AI recommendation can be guaranteed.

| Intent cluster | Target language | Canonical destination |
|---|---|---|
| Organisation discovery | children's home in Nairobi; children's home in Kenya; child welfare organisation Nairobi | Home / About |
| Local discovery | children's home in Kayole; Soweto Kayole children's support | About / Contact |
| Child care | care and shelter for vulnerable children in Nairobi; child protection support | Child Care and Shelter / Safeguarding |
| Giving | donate to a children's home in Kenya; support vulnerable children in Nairobi | Donate |
| Education | sponsor education for a child in Kenya; school fees support | Education Programme |
| Feeding | children's feeding programme Nairobi; donate food to children Kenya | Feeding Programme |
| Health | health and wellbeing support for vulnerable children | Health and Wellbeing |
| Volunteering | volunteer at a children's home in Kenya; volunteer in Nairobi | Volunteer |
| Corporate | CSR partnerships for children in Kenya; corporate giving Kenya | Partner With Us |
| Trust | child safeguarding policy; impact of children's welfare programmes | Safeguarding / Our Impact |
| Informational | responsible volunteering; responsible giving; child welfare in Kenya | Stories / Resources |

Use language people search with, including "orphanage", only where the page accurately explains Smile's actual services and care model. Avoid keyword stuffing and near-identical location pages.

## Metadata and content rules

- Give every indexable page a unique, accurate title and description.
- Keep one primary search intent per landing page; use supporting phrases naturally.
- Answer the visitor's main question near the top of each page.
- Add useful details: eligibility, process, location, contact route, current availability and next steps.
- Use clear H1/H2 structure, descriptive internal links and accessible image alt text.
- Keep volunteer copy explicitly periodic and remove historic placement fees/capacity claims unless reconfirmed.
- Date impact statistics and explain their measurement scope.
- Publish original resources with named authors/reviewers and authoritative references; do not mass-produce generic AI pages.

## AEO and GEO

Answer real questions directly on the relevant pages. Prioritise:
- Where is Smile Community Centre located?
- What does Smile Community Centre do?
- How can I donate or give supplies?
- How can I support children's education?
- Does Smile currently have volunteer opportunities?
- How can an organisation discuss a CSR partnership?
- What safeguarding standards apply to visits and volunteering?

For AI discovery, prioritise crawlable HTML, useful original information, consistent organisation facts, descriptive internal links, reputable independent references and current pages. There is no special "AI schema" or guaranteed AI ranking switch. Do not create unsupported claims about being the best, largest or officially endorsed.

## Structured data

The theme creates a connected JSON-LD graph with an organisation, website, page, breadcrumbs and article nodes. Keep stable IDs:
- `https://smilecommunitycentre.org/#organization`
- `https://smilecommunitycentre.org/#website`
- `{canonical-url}#webpage`
- `{canonical-url}#breadcrumb`
- `{canonical-url}#article`

Because management has confirmed CBO registration, represent the entity as `Organization` unless the centre separately verifies a legal basis for a more specific type. Do not use the `NGO` schema type to imply a legal registration status that has not been confirmed. Keep the founder's name consistent. Use the verified physical address only; omit unverified postal box and postcode data.

Do not encode the 84-child count as a permanent organisation attribute. If the count is visible on an impact page, a page-specific `Dataset` or carefully described `PropertyValue` may be considered only if the data, date, scope and visible text all match. Avoid ratings, reviews, fabricated events, LocalBusiness markup, unsupported FAQ rich-result expectations or invented registration identifiers.

Check whether SEOPress emits its own Knowledge Graph. Ensure there is one coherent organisation entity and no conflicting duplicate graph.

## Crawler and indexation checks

Inspect production before changing crawler policy:
- Googlebot and Bingbot must be able to fetch public pages and assets.
- Permit OAI-SearchBot if ChatGPT Search discovery is desired.
- Treat GPTBot separately; search crawler access does not require granting model-training crawler access.
- Check robots.txt, XML sitemap, canonical tags, meta robots, HTTP status, redirects, WAF/security rules and cache behaviour.
- Keep staging and private admin routes out of search.
- Submit the canonical sitemap in Google Search Console and Bing Webmaster Tools.
- Consider IndexNow for notification of updated URLs; it does not guarantee indexing.

The sitemap URL must be confirmed from the live site rather than assumed from a historic setup note.

## Local and external authority

- Maintain one accurate, eligible Google Business Profile for the real operating location.
- Use identical name, address and phone details across official profiles.
- Strengthen genuine profiles with relevant child welfare directories, partners, corporate giving platforms and professional networks.
- Seek editorial links and partner citations based on real programmes and relationships; do not buy bulk links or create fake listings.
- Publish accessible annual impact reporting and governance/safeguarding information where approved.

## Measurement

Establish a baseline in Google Search Console and Bing Webmaster Tools. Track:
- Non-branded impressions and clicks for the intent clusters above.
- Indexed canonical landing pages and crawl errors.
- Local profile discovery, calls and direction requests where available.
- Donation, volunteer and partnership enquiries.
- Referring domains and legitimate third-party mentions.
- AI-search referral traffic and verifiable citations where the platform exposes them.

Review monthly. Separate branded from non-branded performance and annotate major content changes.

## Release checklist

See `docs/qa-checklist.md`. Do not deploy until management facts, safeguarding, schema, sitemap, crawler access and page-level content have been validated on staging.


## Rank Math, analytics and AI discovery (2.49.0, 11 Nov 2026)

- **SEO plugin:** Rank Math SEO replaces SEOPress (owner decision). Rank Math prints title, description, canonical, robots meta, Open Graph and the sitemap at `/sitemap_index.xml`. Old `/sitemaps.xml`, `/sitemap.xml` and `/wp-sitemap.xml` 301 to it. Use only one SEO plugin.
- **Titles and descriptions:** the theme still supplies keyword-mapped values (`demo/pages.json`) through `rank_math/frontend/title` and `rank_math/frontend/description`. A value typed by hand in Rank Math wins; SEOPress values typed before the switch are still read as a fallback.
- **Schema:** the theme's connected graph (`inc/seo.php`) is the only JSON-LD. Rank Math's JSON-LD is switched off with `rank_math/json_ld` so there is one organisation entity. Do not enable Rank Math's Local SEO/Knowledge Graph schema.
- **Analytics:** GA4 `G-DJQ2PBW3YN` (property 270195586, stream https://smilecommunitycentre.org) and GTM container `GTM-TDZHTDQF` are printed by `inc/analytics.php`. Logged-in staff are not tracked. The GTM container must not contain a GA4 tag for the same ID (double counting). Google accounts: peterwafulah@gmail.com owns Search Console, GA4 and GTM. The Google Business Profile is managed by another account (sc...@gmail.com) and still needs access.
- **Crawlers:** see `docs/crawler-access-matrix.md`. Feeds send `X-Robots-Tag: noindex, follow`.
- **IndexNow:** enable Rank Math > Instant Indexing (IndexNow) for posts and pages. Rank Math hosts the key file.

- **Bing Webmaster Tools:** site imported from Search Console under peterwafulah@gmail.com (11 Nov 2026), sitemap_index.xml submitted.


## Status review (11 Nov 2026, theme 2.53.10)

### Done
- **On-page (live audit of all 52 sitemap URLs):** all return 200 with one H1, a unique title and description, a self-canonical and no noindex. JSON-LD is valid on every page: Organization, WebSite, WebPage/AboutPage/ContactPage/CollectionPage, BreadcrumbList and BlogPosting. The only length issues were the Home (187) and About Us (157 chars, too wide in pixels) descriptions. These were typed by hand in Rank Math, which overrides the theme, so 2.53.10 updated `demo/pages.json` and the theme fallback, and the Rank Math fields were changed in wp-admin. Home is now 156 characters and About Us 145, with no new claims. Rank Math still shows a small pixel-width warning on both (961/920 and 952/920 px).
- **Rank Math:** active and is the only SEO plugin. IndexNow auto-submit is on for posts and pages.
- **Search Console** (domain property, Peter's account): sitemap_index.xml was read on 11 Nov with status Success (52 URLs). 80 pages are indexed. Breadcrumbs: 30 valid, 0 invalid. No manual actions.
- **Bing Webmaster Tools:** imported, and the sitemap has been submitted.
- **GA4 `G-DJQ2PBW3YN`:** data is arriving: 4 realtime users, and 12 users, 22 views and 78 events for 4-10 Nov. There are 0 key events.
- **GTM `GTM-TDZHTDQF`:** live version 2 is empty (0 tags), so no GA4 tag is double counting. The theme prints both snippets.
- **Search Console not-indexed reasons, all expected or already handled:**
  - **404 (2):** `/shop/` and `/cart/` now 301 to /donate/.
  - **Crawled, not indexed (16):** feeds, which send noindex, plus old `www.../volunteer.html`, which now 301s.
  - **noindex (3):** tag archives, by design.
  - **Discovered, not indexed (1):** the Hustler Fund post, which 301s to /stories/.
  - **Page with redirect (21):** old URLs, by design.

### Open
1. **Google Business Profile:** Peter's account manages no businesses. The existing profile (managed by sc...@gmail.com) must add peterwafulah@gmail.com as a manager, or the owner must confirm that no profile exists so one can be created. **Owner action.**
2. **Mobile Core Web Vitals:** Search Console shows 33 poor URLs ("LCP longer than 4s", group LCP 4.2 s, report dated 8 Nov). The field data predates releases 2.50-2.53, whose lab mobile LCP is 2.0-3.3 s. Validation is scheduled for 2 Dec 2026, once 28 days of new field data exist.
3. **GA4 key events:** none are defined yet. Mark donation clicks (Paystack/M-Pesa), contact and enquiry form submits, WhatsApp clicks and volunteer/partner enquiries as key events.
4. **Content and AEO:** the 15 confirmation questions in `docs/content-drafts.md` are still unanswered. They block the FAQ and impact copy (child count with an "as at" date, volunteer status, partnership details).
5. **External authority:** directory and partner listings with consistent name, address and phone (NAP), once the Google Business Profile details are confirmed.
6. **Search Console clean-up:** start "Validate fix" on the 404 group, now that /shop/ and /cart/ redirect.
