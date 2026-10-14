# Architecture

This document explains how the Smile Community Centre website is built, how a request moves through it, where data lives and how it is protected. For a one-page version see [ARCHITECTURE-ESSENTIALS.md](ARCHITECTURE-ESSENTIALS.md).

## 1. System context

```mermaid
flowchart LR
  V[Visitor / donor / volunteer] -->|HTTPS| WP[WordPress site]
  S[Centre staff] -->|HTTPS + login| WP
  WP --> DB[(MySQL database)]
  WP -->|wp_mail via SMTP| M[Centre inbox]
  WP -->|Google Fonts| GF[fonts.googleapis.com]
  WP -->|Map embed| GM[Google Maps]
  V -->|M-Pesa Paybill 247247| MP[Safaricom M-Pesa]
  V -->|wa.me links| WA[WhatsApp]
  ADM[Administrator] -->|One-time import, admin only| LIVE[smilecommunitycentre.org REST API]
```

Payments never pass through the website: donors pay by M-Pesa or bank and may tell the centre through the donation form.

## 2. Layers

| Layer | Component | Responsibility |
|---|---|---|
| Parent theme | Hello Elementor | Minimal base markup |
| Child theme | `smile-community-child` | Design system, templates, section shortcodes, setup wizard, security hardening |
| Site plugin | Smile Community Core | Data that must survive a theme change: centre details, forms and enquiries, redirects, photo privacy |
| Page builder | Elementor | Page layouts (imported from `demo/pages.json`) |
| Optional plugins | SEOPress, WP Super Cache, WP Mail SMTP | SEO, page cache, email delivery |

Rule: **presentation in the theme, data in the plugin.** If the theme is ever switched, enquiries, settings and redirects are kept.

## 3. Theme modules (`inc/`)

| File | Purpose |
|---|---|
| `security.php` | Security headers, HTTPS redirect, login throttling, generic login errors, session length, password minimum, user-enumeration blocks, XML-RPC off, REST CORS |
| `template-tags.php` (social) | `smile_social_profiles()` / `smile_social_icons()`: Instagram, Facebook, LinkedIn from Centre details, used in the top bar, footer and schema `sameAs` |
| `brand.php` | Bundled logo (1x/2x/3x, WebP + PNG), site icons, login logo, logo URL for schema |
| `seo.php` | Connected schema.org graph, title and description resolver (feeds SEOPress), indexing rules, fallback meta and Open Graph |
| `content-editor.php` | Smile Centre > Site text & photos: text overrides through `gettext`, photo overrides by key |
| `setup.php` | Theme supports, image sizes, asset loading (minified bundles), self-hosted fonts with preload, first-slide preload, Elementor trimming, speculative prerender |
| `template-tags.php` | Settings helper, phone links, breadcrumbs, reading time, stories per page |
| `components.php` | Icon set, story images, category labels, shared shortcodes |
| `home-sections.php` | Section shortcodes used across pages |
| `slider.php` | Hero slider and its data (up to four slides; bundled slide photos in `assets/images/slides/`; "split" layout for slide 1) |
| `performance.php` | `wp_head` preload of the inner-page banner (first Elementor section `background_image`, cached per page) or story featured image; `big_image_size_threshold` 1920; PNG to WebP output; dequeues dashicons, wp-embed, heartbeat for visitors; removes oEmbed/REST head links; re-applies Elementor speed options once per theme version on `admin_init` (option `smile_speed_applied`) |
| `login.php` | Branded wp-login.php: `login_body_class` adds `smile-login`; `login_header` prints the photo story panel and opens `<main>`; `login_message` adds the per-screen heading (log in, lost password, reset); `login_footer` prints the secure-connection note and closes `<main>`; "Log In" button relabelled "Sign in"; language drop-down hidden via `login_display_language_dropdown`; `#login` itself is styled as the card. Styles in `assets/css/login.css` (built to `assets/build/login.min.css`), self-hosted fonts also loaded on `login_head`. Off switch: `smile_login_design` filter |
| `smile_office_hours()` (template-tags.php) | Reads `office_open_hour`, `office_close_hour`, `office_saturday` (core settings, validated) and feeds `data-*` attributes on the WhatsApp chat and the Contact hub badge; main.js computes open/closed in Africa/Nairobi time |
| Settings `google_rating`, `google_review_count`, `google_reviews_url` | Core plugin "Google reviews" group (rating validated 0-5, one decimal); read by `[smile_community]` for the rating bar, which hides itself when the rating is empty |
| `careers.php` | Jobs post type `smile_job` (details in `_smile_job_*` meta; open = published and closing date not passed, Nairobi time), admin box and columns, `[smile_careers]`, `[smile_careers_values]`, `[smile_careers_process]`, `[smile_careers_talent]`, `[smile_careers_faq]`, JobPosting JSON-LD, noindex for closed roles, one-time creation of the Careers page and menu links. Job pages render `template-parts/job.php` (routed from `template-parts/single.php`); URLs `/careers/jobs/<role>/` |
| `whatsapp-chat.php` | Floating WhatsApp button and chat card (no third-party scripts); toggle `smile_wa_chat` in the Customizer; behaviour in `initWhatsApp()` in `main.js` |
| `images.php` | Photo key to attachment lookups (cached) |
| `customizer.php` | Donate label, top bar, footer |
| `plugins.php` | Allow-listed plugin installer (admin, nonce, capability checked) |
| `importer.php` | One-click content import and "Apply latest design" |
| `wizard.php` | Smile Setup screens |

## 4. Plugin modules (`plugins/smile-community-core/includes/`)

| File | Purpose |
|---|---|
| `settings.php` | Centre details screen, typed sanitisation, `smile_core_get()` |
| `forms.php` | `[smile_form]` render and submit handler |
| `inquiries.php` | Private `smile_inquiry` post type, inbox columns, status and notes, retention |
| `shortcodes.php` | Giving, contact, map, directions, latest posts, settings shortcodes |
| `redirects.php` | 301 redirects for old URLs (404 only) |
| `media-privacy.php` | Strips EXIF/GPS metadata from uploaded photos |
| `dashboard.php` | Dashboard widget with enquiry counts |

## 5. Request flows

### 5.1 Page view

```mermaid
sequenceDiagram
  participant B as Browser
  participant W as WordPress
  B->>W: GET /page/
  W->>W: security.php: HTTPS check, author-scan block
  W->>W: Elementor renders layout, shortcodes render sections
  W-->>B: HTML + security headers (CSP, HSTS, nosniff...)
  B->>W: main.min.css, home.min.css, main.min.js (deferred)
```

### 5.2 Enquiry form

```mermaid
sequenceDiagram
  participant B as Visitor
  participant F as forms.php
  participant D as Database
  participant M as Mail
  B->>F: POST admin-post.php (nonce, signed time, honeypot)
  F->>F: Size limit, honeypot, nonce, signed timestamp (3s to 24h)
  F->>F: Rate limits: 20 attempts/h, 5 accepted/h per hashed IP
  F->>F: Sanitise and validate every field by type
  alt errors
    F-->>B: 303 back to form with token (values kept 15 min)
  else valid
    F->>D: Private smile_inquiry post + meta
    F->>M: Plain-text notification (header-safe Reply-To)
    F-->>B: 303 to "Thank you" status
  end
```

### 5.3 Editing text and photos

```mermaid
sequenceDiagram
  participant E as Editor
  participant A as Site text & photos
  participant O as Options
  participant P as Public page
  E->>A: Change wording / choose photo (nonce, edit_pages)
  A->>O: smile_text_overrides / smile_image_overrides (sanitised)
  P->>O: gettext filter swaps edited text; smile_image_by_key() returns chosen photo
```

`scripts/extract-strings.py` lists every visitor-facing string into `demo/strings.json` at build time, so the screen always matches the code.

### 5.4 Content import (administrators only)

`wp_ajax_smile_demo_step` → nonce + `manage_options` → builds pages from `demo/pages.json`, sideloads photos with `download_url()` (SSRF-safe), imports stories from the live site with `wp_safe_remote_get()`, cleans HTML with `wp_kses_post()`.

## 6. Data model

| Data | Storage | Who can see it |
|---|---|---|
| Pages, stories, photos | Posts, attachments | Public |
| Centre details | Option `smile_core_settings` | Public where shown; edited by editors (form emails by admins only) |
| Enquiries | Private post type `smile_inquiry` + meta | Users with `smile_manage_inquiries` (administrators, editors) |
| Redirects | Option `smile_core_redirects` | Administrators |
| Rate-limit counters | Transients keyed by hashed IP | Nobody (raw IPs are never stored) |
| Design version | Option `smile_design_version` | Administrators |
| Text and photo changes | Options `smile_text_overrides`, `smile_image_overrides` | Editors and administrators |
| SEO sync marker | Option `smile_seo_synced` | Administrators |

Enquiries are excluded from search, REST, feeds and front-end queries, and are deleted by a daily retention job.

## 7. Front-end assets

- `assets/css/main.css` (all pages), `home.css` + `slider.css` (sections), `assets/js/main.js`, `slider.js` (home only)
- Built into `assets/build/*.min.*` by `scripts/build-theme-zip.sh`
- Fonts: Newsreader, Figtree and Caveat self-hosted in `assets/fonts/` (latin woff2), inline `@font-face`, two preloaded
- Brand: `assets/images/brand/` (logo sizes, icons)

## 8. Build and release

```mermaid
flowchart LR
  E[Edit] --> V[Bump version] --> B[build-theme-zip.sh]
  B --> Q{check-theme.py}
  Q -- fail --> E
  Q -- pass --> Z[dist/smile-community-child.zip]
  Z --> C[Commit to main] --> U[Upload in WordPress] --> A[Apply latest design if layouts changed]
```

## 9. Security architecture

Defence in depth, summarised here and detailed in [SECURITY-AUDIT.md](SECURITY-AUDIT.md):

1. **Transport**: HTTPS redirect, HSTS, `upgrade-insecure-requests`.
2. **Browser**: CSP (`object-src`, `base-uri`, `frame-ancestors`, `form-action`), X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, COOP.
3. **Input**: nonces, signed timestamps, honeypot, rate limits, typed sanitisation, allow-lists.
4. **Output**: context escaping everywhere, `wp_kses_post` for imported HTML.
5. **Access**: capability checks on every admin action; private enquiry post type.
6. **Identity**: login throttling, generic errors, short sessions, 12-character passwords, core password hashing.
7. **Exposure**: no version tag, no user enumeration, XML-RPC off, REST CORS limited to own origin.
