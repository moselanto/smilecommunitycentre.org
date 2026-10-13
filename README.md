<div align="center">

# Smile Community Centre

### WordPress platform for [smilecommunitycentre.org](https://smilecommunitycentre.org/)

**A safe haven in the heart of Soweto, Kayole.** Smile Community Centre Children's Home gives orphaned and vulnerable children in Nairobi shelter, daily meals, education and care. This repository holds the complete website: a child theme, a bundled site plugin, all page layouts and a one-click installer.

![WordPress](https://img.shields.io/badge/WordPress-6.3%2B-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4%2B%20(8.1%2B%20recommended)-777BB4?logo=php&logoColor=white)
![Elementor](https://img.shields.io/badge/Built%20with-Elementor-92003B?logo=elementor&logoColor=white)
![Child theme](https://img.shields.io/badge/Child%20theme-v2.15.0-164B3F)
![Core plugin](https://img.shields.io/badge/Smile%20Community%20Core-v1.1.0-176B5B)
![Giving](https://img.shields.io/badge/Giving-M--Pesa%20Paybill-4CAF50)
![Security](https://img.shields.io/badge/Security-audited-2e7d32)
![License](https://img.shields.io/badge/License-GPLv2%2B-blue)
![Status](https://img.shields.io/badge/Status-Staging-orange)

[Staging site](https://topnotchmall.co.ke/smilescommunity/) · [Architecture](ARCHITECTURE.md) · [Architecture essentials](ARCHITECTURE-ESSENTIALS.md) · [Product requirements](PRD.md) · [Security audit](SECURITY-AUDIT.md) · [Release workflow](docs/workflow.md)

</div>

---

![Smile Community Centre home page](wp-content/themes/smile-community-child/screenshot.png)

## Contents

- [Overview](#overview)
- [What the site delivers](#what-the-site-delivers)
- [Architecture](#architecture)
- [Security](#security)
- [Project structure](#project-structure)
- [Requirements and installation](#requirements-and-installation)
- [Deploying an update](#deploying-an-update)
- [Configuration](#configuration)
- [Shortcodes](#shortcodes)
- [Quality gate](#quality-gate)
- [Release history](#release-history)
- [Documentation](#documentation)
- [Organisation](#organisation)

## Overview

| | |
|---|---|
| **Organisation** | Smile Community Centre Children's Home, a registered Community Based Organisation (2016) founded by Margret Nekesa in 2009 |
| **Location** | Mzesa, Soweto, Kayole (near Donholm Phase 8), Nairobi, Kenya |
| **Platform** | WordPress + Hello Elementor (parent) + `smile-community-child` (this theme) + Smile Community Core (bundled plugin) |
| **Pages** | 21 designed pages, Stories & News journal, search, 404 |
| **Giving** | M-Pesa Paybill 247247, Account 147516, bank transfer, in-kind gifts |
| **Install** | One zip, a three-step setup wizard, one-click content import |

## What the site delivers

### Pages

Home · About Us · Our Founder · Our Goals · Our Programmes (Education, Feeding, Child Care and Shelter, Health and Wellbeing, Sports and Talent) · Our Impact · Gallery · Stories & News · Get Involved · Donate · Volunteer · Partner With Us · Contact Us · FAQ · Safeguarding and Child Protection · Privacy Policy

### Key features

- **Hero slider** with autoplay, pause, keyboard, swipe and reduced-motion support.
- **Gift calculator** that turns an amount into meals, water and school fees, with copy-to-clipboard M-Pesa details.
- **Six secure enquiry forms** (contact, visit, volunteer, partner, donation, photo removal) feeding a private Enquiries inbox.
- **Stories & News journal**: lead story, "Also new", topic filter, 12 stories per page, support card.
- **Wall of hope gallery** with filters and an accessible lightbox.
- **Safeguarding first**: photo metadata stripping, removal-request form, no children's names or locations online.
- **Mobile-first navigation**: full-height drawer and a bottom tab bar.
- **Speed**: minified bundles, non-blocking fonts, preloaded first slide image, off-screen sections skipped until needed.
- **Security hardening** (see [Security](#security)).

## Architecture

```
Visitor ──HTTPS──> WordPress
                     ├── Hello Elementor (parent theme)
                     ├── smile-community-child (this theme)
                     │     ├── inc/security.php      headers, HTTPS, login limits, sessions, CORS
                     │     ├── inc/setup.php         assets, fonts, performance
                     │     ├── inc/*.php             components, sections, slider, helpers
                     │     ├── template-parts/       header, footer, archive, single, search, 404
                     │     └── demo/*.json           page layouts, photos, menus (imported by the wizard)
                     ├── Smile Community Core (bundled plugin)
                     │     ├── settings              centre details, M-Pesa, emails
                     │     ├── forms + inquiries     secure forms, private inbox, retention
                     │     ├── redirects             old URL redirects
                     │     └── media-privacy         strips photo metadata on upload
                     └── Elementor (page builder)
```

Full diagrams and flows: [ARCHITECTURE.md](ARCHITECTURE.md). One-page summary: [ARCHITECTURE-ESSENTIALS.md](ARCHITECTURE-ESSENTIALS.md).

## Security

Reviewed line by line for production. Full findings and fixes: [SECURITY-AUDIT.md](SECURITY-AUDIT.md).

| Control | How it is done |
|---|---|
| Rate limiting | Forms: 5 accepted / 20 attempts per hour per connection. Login: 5 failures per 15 minutes |
| Input sanitisation | Every field sanitised by type on the server; select values checked against an allow-list |
| Output escaping | `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` on all output; no `innerHTML` with data |
| CSRF | Nonces on every form and admin action, plus signed timestamps on public forms |
| SQL injection | WordPress APIs only; the one direct query uses `$wpdb->prepare` |
| Password hashing | WordPress core salted hashing (bcrypt from WordPress 6.8); 12-character minimum |
| Session expiry | 12 hours, or 7 days with "Remember me" |
| HTTPS everywhere | Plain-http requests redirected; HSTS; `upgrade-insecure-requests` |
| Security headers | CSP (safe subset), X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, COOP |
| CORS | REST API only trusts this site's own origin |
| Secrets | No keys in code; SMTP and salts live in `wp-config.php` / environment variables |
| Information leaks | No version tag, no user enumeration, XML-RPC off |
| Access control | Capability checks on every admin action; enquiries need `smile_manage_inquiries` |
| Dependencies | Monthly update routine; quality gate on every build |

## Project structure

```
.
├── README.md                     this file
├── ARCHITECTURE.md               full architecture
├── ARCHITECTURE-ESSENTIALS.md    one-page architecture
├── PRD.md                        product requirements
├── SECURITY-AUDIT.md             security review, findings and fixes
├── docs/                         install, admin guide, QA, performance, redirects, workflow
├── scripts/
│   ├── build-theme-zip.sh        minifies assets, runs the quality gate, builds the zip
│   └── check-theme.py            quality and security gate
└── wp-content/themes/smile-community-child/
    ├── style.css, functions.php, screenshot.png
    ├── inc/
    │   ├── security.php          security hardening
    │   ├── setup.php             theme setup, assets, performance
    │   ├── template-tags.php     helpers (settings, phone links, breadcrumbs)
    │   ├── components.php        icons, cards, shared components
    │   ├── home-sections.php     section shortcodes (programmes, stats, gallery, founder...)
    │   ├── slider.php            hero slider
    │   ├── images.php            photo key lookups
    │   ├── customizer.php        Donate label, top bar, footer
    │   ├── plugins.php           plugin installer (allow-listed)
    │   ├── importer.php          one-click content import
    │   └── wizard.php            Smile Setup wizard
    ├── template-parts/           header, footer, archive, single, search, 404, cards
    ├── assets/css, assets/js     source styles and scripts
    ├── assets/build/             minified bundles (generated)
    ├── assets/admin/             wizard styles and script
    ├── demo/                     pages.json, images.json, menus.json
    └── plugins/smile-community-core/
        ├── smile-community-core.php
        └── includes/             settings, forms, inquiries, shortcodes, redirects, media-privacy, dashboard
```

## Requirements and installation

- WordPress 6.3+, PHP 7.4+ (8.1+ recommended), HTTPS certificate
- The server can install plugins directly (otherwise install them from Plugins > Add New)

1. **Upload the theme**: Appearance > Themes > Add New > Upload Theme, choose `smile-community-child.zip`, then **Activate**. WordPress installs Hello Elementor automatically.
2. **Install plugins**: the **Smile Setup** wizard opens. Click **Install & activate** (Elementor and Smile Community Core required; SEOPress, WP Super Cache, WP Mail SMTP recommended).
3. **Import content**: click **Import content**. It builds the pages, photos, menus, home page and brand settings.

## Deploying an update

1. Build: `bash scripts/build-theme-zip.sh` (stops if the quality gate fails).
2. Upload the zip, then choose **Replace active with uploaded**.
3. If page layouts changed, click **Apply latest design** in the dashboard notice.
4. If Smile Community Core has a newer version, Smile Setup > Plugins shows **Update available**: tick it and click Install & activate.
5. Elementor > Tools > **Regenerate CSS & Data**, then check the site.

Full routine: [docs/workflow.md](docs/workflow.md).

## Configuration

| Where | What |
|---|---|
| Smile Centre > Centre details | Phone numbers, WhatsApp, email, address, office hours, M-Pesa, bank, notification emails, volunteer notice |
| Smile Centre > Redirects | Old URL to new URL list |
| Smile Centre > Enquiries | Private inbox for form messages |
| Appearance > Customize | Donate button label, top bar on/off |
| `wp-config.php` | Salts, SMTP credentials and hardening constants (see [SECURITY-AUDIT.md](SECURITY-AUDIT.md#environment-and-secrets)) |

## Shortcodes

| Shortcode | Purpose |
|---|---|
| `[smile_form type="contact\|visit\|volunteer\|partner\|donation\|removal"]` | Secure enquiry forms |
| `[smile_mpesa]`, `[smile_bank]`, `[smile_online_giving]`, `[smile_giving_tiers]` | Giving blocks |
| `[smile_gift_calc]` | Gift calculator |
| `[smile_contact]`, `[smile_contact_hub]`, `[smile_directions]`, `[smile_map]` | Contact and directions |
| `[smile_hero_slider]`, `[smile_latest_posts]`, `[smile_stories]` | Home and stories |
| `[smile_wall]`, `[smile_stats]`, `[smile_mv]`, `[smile_objectives]`, `[smile_dream]` | Gallery, impact, goals |
| `[smile_founder_words]`, `[smile_founder_path]` | Our Founder |
| `[smile_roles]`, `[smile_lookfor]`, `[smile_intl]`, `[smile_volunteer_notice]` | Volunteer |
| `[smile_involve]`, `[smile_involve_nav]`, `[smile_partner_types]` | Get involved |
| `[smile_setting key="..."]` | Any centre detail |

## Quality gate

`scripts/check-theme.py` runs on every build and blocks the zip if anything fails:

- PHP syntax (`php -l`), JavaScript syntax (`node --check`), CSS parse errors
- Page layouts: shortcodes exist, photos exist, internal links resolve, unique IDs, one H1 per banner
- Security patterns: no `eval`, `base64_decode`, `unserialize` of input, unprepared queries, raw output of request data, `wp_remote_get` (must be `wp_safe_remote_get`), `innerHTML` with data, stray backslash-bang

## Release history

| Version | Highlights |
|---|---|
| 2.15.0 | SEO: connected schema graph (NGO, WebSite, pages, breadcrumbs, articles), keyword-mapped titles and descriptions, indexing rules |
| 2.14.0 | Security hardening module, form hardening, SSRF-safe importer, documentation set |
| 2.13.x | Stories lead card, Founder quote band, upgrades that work without re-applying layouts |
| 2.12.x | Stories top stories, Volunteer notice restored |
| 2.11.0 | Founder journey timeline, Volunteer spacing |
| 2.10.x | Stories topic bar, theme screenshot |
| 2.9.x | Clean top of every page, quality gate, workflow |
| 2.8.x | Balanced titles, About/Founder/Goals, parse-error fix, slider fix |
| 2.4 to 2.7 | Gallery wall, Impact stats, Get Involved family, Contact hub |
| 1.7 to 2.3 | Speed, inner pages, programmes, signature details, mobile navigation |

## Documentation

| Document | For |
|---|---|
| [ARCHITECTURE.md](ARCHITECTURE.md) | Developers: layers, flows, data model |
| [ARCHITECTURE-ESSENTIALS.md](ARCHITECTURE-ESSENTIALS.md) | Anyone new: the one-page version |
| [PRD.md](PRD.md) | Goals, users, requirements, success measures |
| [SECURITY-AUDIT.md](SECURITY-AUDIT.md) | Security findings, fixes and hardening checklist |
| [docs/admin-guide.md](docs/admin-guide.md) | Centre staff: editing the site |
| [docs/install-and-deploy.md](docs/install-and-deploy.md) | Install, launch and roll back |
| [docs/qa-checklist.md](docs/qa-checklist.md) | Pre-launch checks |
| [docs/performance.md](docs/performance.md) | Speed decisions |
| [docs/SEO.md](docs/SEO.md) | Keyword map, schema and search setup |
| [docs/workflow.md](docs/workflow.md) | Release routine |

## Organisation

**Smile Community Centre Children's Home**
Mzesa, Soweto, Kayole (near Donholm Phase 8), Nairobi, Kenya · P.O. Box 156-00518, Nairobi
Phone 0721 318 869 · info@smilecommunitycentre.org · [Instagram](https://www.instagram.com/smile_kenya_/)

## License

GPL-2.0-or-later, the same as WordPress.
