# smilecommunitycentre.org

WordPress theme and plugin for the redesigned Smile Community Centre Children's Home website.

GitHub stores **only the code** (theme and plugin) plus documentation. The live content, media and database live in WordPress itself.

## What's here

| Path | What it is |
|---|---|
| `wp-content/themes/smile-community-child/` | Child theme of **Hello Elementor**. It holds the design system (colours, fonts, spacing, buttons, cards), an accessible header and footer with a mobile menu, and the templates for articles, the stories archive, search results and the 404 page. |
| `wp-content/plugins/smile-community-core/` | Site functions. Centre details (phone numbers, M-Pesa, bank) are edited in one place. It also provides the M-Pesa donate block, secure forms with a private enquiries inbox, redirects from old URLs, removal of location data from photos, privacy export and erase tools, and a **one-click site builder** that creates every page in Elementor. |
| `docs/` | Install and deploy guide, administrator guide, audit, migration manifest, redirect map, QA checklist, and known limitations. |

## Quick start (staging)

1. Make a backup of the staging site.
2. Install and activate **Elementor** (the free version is enough) and the **Hello Elementor** theme.
3. Upload the two folders from `wp-content/`, then activate the **Smile Community Centre** theme and the **Smile Community Core** plugin.
4. Go to **Smile Centre > Site setup** and click **Build the site**.
5. Work through `docs/qa-checklist.md`.

The full steps are in [docs/install-and-deploy.md](docs/install-and-deploy.md).

## Requirements

- WordPress 6.3+ and PHP 7.4+ (8.1+ recommended)
- Elementor 3.18+ (free). Elementor Pro is optional (see `docs/known-limitations.md`).
- The Hello Elementor parent theme
