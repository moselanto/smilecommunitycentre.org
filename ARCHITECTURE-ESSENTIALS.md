# Architecture essentials

The one-page version of [ARCHITECTURE.md](ARCHITECTURE.md).

## The stack

**WordPress** + **Hello Elementor** (parent) + **smile-community-child** (this theme) + **Smile Community Core** (bundled plugin) + **Elementor** (layouts).

## The one rule

**Presentation in the theme, data in the plugin.** Enquiries, centre details and redirects live in the plugin so they survive a theme change.

## Where things are

| I want to change... | Go to |
|---|---|
| Colours, spacing, components | `assets/css/main.css`, `assets/css/home.css` |
| Front-end behaviour | `assets/js/main.js`, `assets/js/slider.js` |
| A section (programmes, stats, gallery...) | `inc/home-sections.php` |
| Header / footer | `template-parts/header.php`, `template-parts/footer.php` |
| Stories page | `template-parts/archive.php`, `template-parts/content-card.php` |
| Page layouts | `demo/pages.json` (then "Apply latest design") |
| Forms | `plugins/smile-community-core/includes/forms.php` |
| Phone, M-Pesa, emails | WordPress: Smile Centre > Centre details |
| Security behaviour | `inc/security.php` (every feature has an off-switch filter) |
| SEO and schema | `inc/seo.php`, titles in `demo/pages.json` |
| Logo and icons | `assets/images/brand/`, `inc/brand.php` |
| Home slider and bundled slide photos | `inc/slider.php`, `assets/images/slides/` |
| Two-step login | Two Factor plugin (Smile Setup), `inc/two-factor.php`, code screen styles in `assets/css/login.css` |
| Speed | `inc/setup.php` (fonts, assets, home preload, speculative loading), `inc/performance.php` (banner preload, lighter uploads, fewer requests, Elementor settings) |
| Staff sign-in page | `inc/login.php`, `assets/css/login.css` |
| Careers and jobs | `inc/careers.php`, `template-parts/job.php`, `careers` form in the core plugin |
| WhatsApp chat | `inc/whatsapp-chat.php`, `initWhatsApp()` in `assets/js/main.js` |
| Social icons (Instagram, Facebook, LinkedIn) | `smile_social_icons()` in `inc/template-tags.php` |
| Wording and photos (staff) | WordPress: Smile Centre > Site text & photos |
| Fonts | `assets/fonts/`, loaded in `inc/setup.php` |

## How a form message travels

Visitor → nonce + signed time + honeypot → rate limits → sanitise and validate → private `smile_inquiry` post → plain-text email to the centre → "Thank you" page.

## How a release happens

Edit → bump version in `style.css` and `functions.php` → `bash scripts/build-theme-zip.sh` (quality and security gate) → commit to `main` → upload zip → **Apply latest design** if layouts changed.

## Security in one breath

HTTPS + HSTS · security headers · nonces everywhere · typed sanitisation · escaped output · rate-limited forms and logins · 12-hour sessions · 12-character passwords · no user enumeration · XML-RPC off · REST CORS own-origin only · no secrets in code.

## Text you add in code

Wrap every visitor-facing string in `__( '...', 'smile-community' )` (or `esc_html__`). The build lists it in Site text & photos automatically, so staff can change it.

## Never do

- Put keys or passwords in code (use `wp-config.php` or environment variables).
- Echo request data without escaping, or build SQL by joining strings.
- Use `wp_remote_get` for URLs that come from outside (use `wp_safe_remote_get`).
- Publish children's names, locations or identifying details.
- Ship a zip that did not pass `check-theme.py`.
