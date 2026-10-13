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

## How a form message travels

Visitor → nonce + signed time + honeypot → rate limits → sanitise and validate → private `smile_inquiry` post → plain-text email to the centre → "Thank you" page.

## How a release happens

Edit → bump version in `style.css` and `functions.php` → `bash scripts/build-theme-zip.sh` (quality and security gate) → commit to `main` → upload zip → **Apply latest design** if layouts changed.

## Security in one breath

HTTPS + HSTS · security headers · nonces everywhere · typed sanitisation · escaped output · rate-limited forms and logins · 12-hour sessions · 12-character passwords · no user enumeration · XML-RPC off · REST CORS own-origin only · no secrets in code.

## Never do

- Put keys or passwords in code (use `wp-config.php` or environment variables).
- Echo request data without escaping, or build SQL by joining strings.
- Use `wp_remote_get` for URLs that come from outside (use `wp_safe_remote_get`).
- Publish children's names, locations or identifying details.
- Ship a zip that did not pass `check-theme.py`.
