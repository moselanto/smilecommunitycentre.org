# SEO and structured data

Goal: rank for searches like **children's home in Nairobi**, **children's home in Kenya**, **orphanage in Nairobi / Kayole**, **donate to a children's home in Kenya**, **sponsor a child's education in Kenya** and **volunteer at a children's home in Kenya**, using only accurate, verifiable information.

No one can guarantee first position. What this work does is give Google the clearest possible picture of who Smile is, where it is and what each page is for.

## 1. Keyword to page map

| Search intent | Primary phrase | Page |
|---|---|---|
| Find a children's home | children's home in Nairobi / Kenya | Home |
| Local | children's home in Kayole, Soweto Nairobi | About Us, Contact |
| Give | donate to a children's home in Kenya | Donate |
| Education | sponsor a child's education in Kenya | Education Programme |
| Food | feeding programme for children in Nairobi | Feeding Programme |
| Care | child care and shelter Nairobi | Child Care and Shelter |
| Volunteer | volunteer at a children's home in Kenya | Volunteer |
| Business | CSR for children in Kenya | Partner With Us |
| Trust | charity supporting children in Kenya | Our Impact, About, Safeguarding |
| Research | children's homes in Nairobi (guides) | Stories & News |

Each page has one primary phrase so pages do not compete with each other. Titles are 60 characters or less and descriptions 160 or less (`demo/pages.json`).

## 2. Structured data (JSON-LD)

`inc/seo.php` prints one connected graph on every page:

| Node | @id | Content |
|---|---|---|
| NGO / Organization | `/#organization` | Name, alternate names, founder Margret Nekesa Khaemba, founded 2009, address (Mzesa, Soweto, Kayole, Nairobi, P.O. Box 156-00518), phone, email, Instagram, area served, logo, DonateAction |
| WebSite | `/#website` | Name, language en-KE, publisher, site search |
| WebPage / AboutPage / ContactPage / CollectionPage | `<url>#webpage` | Title, description, dates, main image, breadcrumb |
| BreadcrumbList | `<url>#breadcrumb` | Matches the visible breadcrumbs |
| BlogPosting | `<url>#article` | Stories: headline, dates, image, section, publisher |

Deliberately **not** added: ratings, reviews, LocalBusiness, Event, Product, FAQ rich results, registration numbers or statistics. They would be inaccurate or unsupported, and Google penalises misleading markup.

If SEOPress's own "Knowledge Graph" is switched on, the theme skips its Organization node to avoid a duplicate (switching it off and letting the theme handle it is recommended, because the theme's version is richer).

## 3. Indexing rules

- Search results, tag archives, author and date archives: `noindex, follow`.
- Attachment pages redirect to their parent page.
- `max-image-preview:large` and full snippets allowed on indexable pages.
- The users sitemap is removed (also a security measure).

## 4. Titles and descriptions stay in sync

On the first dashboard visit after a theme update, the new titles and descriptions are copied into SEOPress for the theme's pages. A value is only replaced if it is empty or still an older theme value (`demo/seo-history.json`), so anything typed by hand is kept.

## 5. After launch (centre or web admin)

1. **Google Search Console**: verify the domain, submit `https://smilecommunitycentre.org/sitemaps.xml`, inspect the home, Donate and Volunteer pages.
2. **Google Business Profile**: claim or create "Smile Community Centre Children's Home", category "Non-profit organisation" (or "Orphanage" if offered), same address, phone and website as the site, add real photos (no identifiable children without consent).
3. **Rich Results Test** and **Schema Markup Validator**: test the home page, a programme page and a story.
4. **Turn off "Discourage search engines"** under Settings > Reading on production.
5. **Links from trusted sites**: partners (Lift the Children, Thrive Kenya, Rotary, AFC Leopards Kayole) linking to the site helps more than anything on-page.
6. **Content**: one genuine, helpful story a month (for example "How to support a children's home in Nairobi responsibly").
7. **Measure** monthly in Search Console: impressions and clicks for the phrases above.
