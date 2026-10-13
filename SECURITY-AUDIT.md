# Security audit

| | |
|---|---|
| **Scope** | `smile-community-child` theme 2.14.0 (all PHP, JS, CSS, templates, page data) and the bundled Smile Community Core plugin 1.1.0 |
| **Date** | 13 October 2026 |
| **Method** | Manual line-by-line review of every request entry point (public forms, admin-post, AJAX, REST, template output, remote fetches), automated syntax and pattern checks (`scripts/check-theme.py`), OWASP Top 10 and WordPress security guidelines |
| **Result** | No critical or high findings remain. 9 issues fixed, 12 hardening controls added, residual items listed in section 6 |

## 1. Attack surface

| Entry point | Who | Protection |
|---|---|---|
| `admin-post.php?action=smile_form_submit` | Public | Nonce, signed timestamp, honeypot, size limit, rate limits, typed validation |
| `admin-post.php` settings / redirects save | Logged-in staff | `check_admin_referer` + `edit_pages` / `manage_options` |
| `wp_ajax_smile_install_plugin` | Administrators | `check_ajax_referer` + `install_plugins` + `activate_plugins` + slug allow-list |
| `wp_ajax_smile_demo_step` | Administrators | `check_ajax_referer` + `manage_options` |
| Enquiry status meta box save | Staff | Nonce + `smile_manage_inquiries` |
| Templates and shortcodes | Public output | Context escaping |
| `wp-login.php`, XML-RPC, REST | Public | Throttling, generic errors, XML-RPC off, users endpoint hidden, CORS own-origin |
| Content importer remote calls | Administrators | `wp_safe_remote_get`, `download_url` (blocks private and local addresses), `wp_kses_post` |

## 2. Findings and fixes

| # | Severity | Category | Finding | Fix |
|---|---|---|---|---|
| 1 | Medium | Broken authentication | No limit on failed logins; password guessing was unlimited | 5 failed attempts per 15 minutes per connection, then a lock message (`inc/security.php`) |
| 2 | Medium | Data exposure | Login names could be listed via `?author=1`, `/wp-json/wp/v2/users` and the users sitemap | All three blocked for visitors; staff with `list_users` unaffected |
| 3 | Medium | Missing security headers | No CSP, HSTS, X-Frame-Options, nosniff, Referrer-Policy or Permissions-Policy | Header set added for the public site, login and dashboard (CSP on the public site only) |
| 4 | Medium | Transport | No HTTP to HTTPS redirect | Front-end requests on http:// redirected to https:// when the site address is HTTPS; HSTS one year |
| 5 | Medium | SSRF | Story importer used `wp_remote_get` with URLs built from remote data | Switched to `wp_safe_remote_get`, which refuses private, loopback and non-standard-port addresses |
| 6 | Low | Email header injection | Visitor name went straight into the `Reply-To` header | Name stripped of line breaks, `<>,;"`; Reply-To only when the email validates; plain-text content type set |
| 7 | Low | Abuse / DoS | Form rate limit counted only accepted messages, so invalid posts were unlimited; no request size cap | Second limit of 20 attempts per hour; posts over 20 KB rejected |
| 8 | Low | DOM XSS (defence in depth) | Mobile menu toggle built its label with `innerHTML` from link text | Rebuilt with `createElement` and `textContent` |
| 9 | Low | SQL hygiene | One direct query ran without `$wpdb->prepare` (constant value, not exploitable) | Now prepared, so future edits stay safe |
| 10 | Info | Session hijacking window | WordPress default sessions last 2 days (14 with Remember me) | 12 hours, or 7 days with Remember me |
| 11 | Info | Password policy | No minimum length | 12-character minimum on new and changed passwords |
| 12 | Info | Exposure | WordPress version in `<meta generator>`, RSD and WLW links, X-Pingback header, XML-RPC on | All removed; XML-RPC off |
| 13 | Info | CORS | REST API echoed any `Origin` for credentialed requests | Only this site's own origin is trusted |

## 3. Controls confirmed sound (no change needed)

- **CSRF**: every form and admin action has a nonce; the public form also has an HMAC-signed timestamp (3 seconds to 24 hours).
- **XSS**: all template and shortcode output uses `esc_html`, `esc_attr`, `esc_url` or `esc_textarea`; imported story HTML passes `wp_kses_post`; search terms are escaped.
- **SQL injection**: data access goes through WordPress APIs (`WP_Query`, `get_posts`, `update_post_meta`, options); no string-built SQL.
- **Input sanitisation**: form fields sanitised by type (email, tel, url, date, textarea, select allow-list, checkbox); settings sanitised by declared type; redirects saved with `sanitize_textarea_field` and executed with `wp_safe_redirect` (external hosts refused).
- **Open redirect**: form return URL passes `wp_validate_redirect`.
- **Access control**: enquiries are a private post type (`public`, `publicly_queryable`, `show_in_rest` all false, own capability); form-email settings need `manage_options`.
- **Insecure file uploads**: the site has no public upload; staff uploads use WordPress core checks; photo EXIF/GPS metadata is stripped on upload; SVG is not enabled.
- **Privacy**: raw IP addresses are never stored (HMAC-hashed with the site salt); enquiries are deleted by a daily retention job.
- **Secrets**: no API keys, tokens or passwords in the code. Payment details shown on the site (Paybill, account) are public by design and editable in Centre details.
- **Plugin installer**: only slugs in the theme's allow-list; bundled plugin copied from the theme folder; WordPress.org plugins via the official API.

## 4. Requested controls checklist

| Control | Status | Where |
|---|---|---|
| Rate limiting | Done | Forms (`forms.php`), login (`security.php`) |
| Input sanitisation | Done | `forms.php`, `settings.php`, `redirects.php`, `inquiries.php` |
| Password hashing | Done (core) | WordPress salted hashing, bcrypt from 6.8; 12-character minimum added |
| Environment variables, no hard-coded keys | Done | No secrets in code; see section 5 |
| CORS configuration | Done | `security.php` |
| SQL / NoSQL injection protection | Done | WordPress APIs and `$wpdb->prepare`; no NoSQL store |
| Session expiry | Done | `security.php` |
| HTTPS everywhere | Done | Redirect, HSTS, `upgrade-insecure-requests` |
| Security headers | Done | `security.php` |
| Regular dependency updates | Process | Section 7 |

## 5. Environment and secrets

Keep these out of the repository. Put them in `wp-config.php` (outside version control) or in server environment variables read by `wp-config.php`:

```php
// Unique salts: generate at https://api.wordpress.org/secret-key/1.1/salt/
define( 'AUTH_KEY', getenv( 'WP_AUTH_KEY' ) ); // ...and the other seven keys and salts

// SMTP (used by WP Mail SMTP), never typed into the code
define( 'WPMS_ON', true );
define( 'WPMS_SMTP_PASS', getenv( 'SMTP_PASSWORD' ) );

// Hardening
define( 'DISALLOW_FILE_EDIT', true );   // turns off the dashboard code editor
define( 'FORCE_SSL_ADMIN', true );      // dashboard and login over HTTPS only
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );    // never show PHP errors to visitors
```

Server: file permissions 644 (files) / 755 (folders), `wp-config.php` 600; disable directory listing.

## 6. Residual risks and recommendations

| Item | Why it remains | Recommendation |
|---|---|---|
| CSP has no `script-src` | Elementor and WordPress rely on inline scripts; a strict script policy would break the editor and pages | Revisit if Elementor is replaced; meanwhile output escaping is the XSS control |
| IP-based limits behind a proxy | If the host uses a reverse proxy, all visitors may share one address | Configure the host to pass the real client IP to `REMOTE_ADDR` |
| No two-factor login | Not a theme feature | Add a 2FA plugin (for example Two Factor) for administrator accounts |
| No web application firewall | Hosting-level control | Use the host's WAF or Cloudflare |
| Map and fonts from Google | Third-party requests | Elementor "load fonts locally" option; map loads lazily |
| Staging is public | `noindex` only | Password-protect staging at the host |

Every hardening feature has an off-switch filter (see the top of `inc/security.php`) so a host problem can be fixed without editing code.

## 7. Dependency and maintenance routine

| When | Action |
|---|---|
| Weekly | Apply WordPress core minor updates (automatic) and security releases for Elementor, SEOPress, WP Super Cache, WP Mail SMTP |
| Monthly | Update all plugins and the Hello Elementor parent theme on staging, check the QA list, then production; remove unused plugins and themes |
| Each release | `scripts/check-theme.py` must pass (syntax plus security patterns); bump theme and plugin versions |
| Quarterly | Review administrator accounts, reset shared passwords, check backups restore, re-run this audit |

## 8. Automated security checks in the quality gate

`scripts/check-theme.py` fails the build if it finds:

- `eval(`, `base64_decode(`, `unserialize(` on request data, `create_function(`
- `$wpdb->query/get_*` with a variable and no `prepare`
- `echo $_GET/$_POST/$_REQUEST/$_COOKIE` (unescaped request data)
- `wp_remote_get(` (must be `wp_safe_remote_get(`)
- `innerHTML` assigned from anything other than a fixed string
- A stray backslash before `!` (once crashed the site)
