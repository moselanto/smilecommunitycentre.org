# Security audit

## Production re-audit: 4 November 2026 (theme 2.46.0, Core 1.3.7)

**Scope:** every PHP file in the theme and the bundled plugin (16,000 lines), the theme JavaScript, the live site at https://smilecommunitycentre.org and its cPanel hosting account. **Method:** a manual review of every request entry point (one public form handler, 9 `admin-post`/AJAX handlers, REST, template output), plus pattern searches for SQL, output escaping, dangerous functions and unsanitised input, PHP 8.3 `php -l` on all files, and live HTTP checks.

**Result:** no critical or high-severity vulnerabilities in the code. Two medium-severity issues and one low-severity issue were fixed in code. Six issues on the server were fixed on the server or now have ready-made configuration.

### Required controls: how each one is covered

| Control | Status | Where / how |
|---|---|---|
| Rate limiting | In place, improved | Login: 5 failures per 15 minutes per IP (`inc/security.php`). Forms: per-IP hourly limit plus a separate limit on attempts (`forms.php`). **New:** support for trusted proxies and CDNs, so limits use the real visitor IP |
| Input sanitisation | In place | Every `$_POST`/`$_GET`/`$_SERVER` read is unslashed and sanitised (`sanitize_text_field`, `sanitize_textarea_field`, `sanitize_key`, `absint`, `is_email`). A scan found no raw superglobal reads. **New:** redirect targets are validated |
| Password hashing | WordPress core | Salted hashes (bcrypt from WordPress 6.8). The theme requires passwords of 12 characters or more, and two-step sign-in is offered. The site never stores or compares passwords itself |
| Environment variables / no hard-coded keys | In place | The repository holds no keys, tokens or passwords (scanned). Database credentials and salts exist only in `wp-config.php` on the server, now at permission 0600. See `docs/server/wp-config-hardening.md` |
| CORS | In place | The REST API allows credentialed cross-origin requests only from the site's own origin. WordPress's echo-any-origin default is removed (`inc/security.php` §6) |
| SQL / NoSQL injection | In place | There is one direct query, and it uses `$wpdb->prepare()` (`inc/slider.php`). Everything else goes through WordPress APIs (`WP_Query`, meta and option functions). There is no NoSQL store |
| Session expiry | In place | Sessions last 12 hours, or 7 days with "Remember me" (WordPress defaults are 2 and 14 days). HttpOnly secure auth cookies come from core. **Recommended:** rotate the salts after the move from staging (see `wp-config-hardening.md`) |
| HTTPS everywhere | In place, plus server rules | The theme sends http requests to https (301) and sends HSTS for one year. `upgrade-insecure-requests` is set in the CSP. Staging URLs were replaced in the database (110 rows). **New:** a one-hop http/www to https rule for static files (`docs/server/htaccess-production.conf`) |
| Security headers | In place | CSP (`object-src`, `base-uri`, `frame-ancestors`, `form-action`), HSTS, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, COOP and X-Permitted-Cross-Domain-Policies. These apply to pages, login and the dashboard. **New:** the same baseline for static files through `.htaccess` |
| Regular dependency updates | Process | There are no Composer or npm runtime dependencies. The only third-party code is WordPress, Hello Elementor, Elementor and optional plugins: update them monthly (section 7). The build tools `rcssmin`/`rjsmin` are used only at build time |

### OWASP review results

| Area | Result |
|---|---|
| SQL injection | None found. The only direct query is prepared |
| XSS | None found. Output is escaped at render (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`). The two unescaped `echo $html` calls print markup the theme builds and escapes itself. The one `insertAdjacentHTML` call in `main.js` inserts a constant SVG string |
| CSRF | Every state-changing handler checks a nonce (`check_admin_referer`, `check_ajax_referer`, `wp_verify_nonce`) |
| Broken authentication | Login throttling, generic login errors, author/user enumeration blocked, XML-RPC off, application passwords off, two-step sign-in available |
| Unauthorised access | Every admin handler checks a capability (`manage_options`, `edit_pages`, `install_plugins`, `smile_manage_inquiries`). The only public handler is the enquiry form. The 2FA "snooze" handler changes only the signed-in user's own reminder |
| Insecure file uploads | The site accepts no public uploads. CVs are links, not files. Media uploads are for staff only, with EXIF/GPS stripped (`media-privacy.php`). **New:** a rule that blocks PHP execution in `wp-content/uploads` (`docs/server/uploads-htaccess.conf`) |
| Session hijacking | Secure HttpOnly cookies, HSTS, shorter sessions, and salt rotation recommended |
| API vulnerabilities | Users endpoint hidden from visitors, CORS limited to the site's own origin, enquiries not exposed in REST (`show_in_rest => false`, not public) |
| Data exposure | Enquiries are private posts with a retention cron. IPs are stored only as keyed hashes. Email headers are protected against injection. **Fixed on the server:** three backup ZIPs (9.1 GB in total, including a full site backup) could be downloaded from the web root. They were moved to `~/private-backups/`. `wp-config.php` was world-writable (0666) and is now 0600 |

### Findings fixed in this round

| # | Severity | Finding | Fix |
|---|---|---|---|
| P1 | High (server) | Full-site backup ZIPs were downloadable from `public_html` | Moved outside the web root on 4 Nov 2026. `.htaccess` now denies `.zip/.sql/.bak/.log` and other backup or log files |
| P2 | High (server) | `wp-config.php` had permission 0666 (writable by any process on the server) | Set to 0600 on 4 Nov 2026 |
| P3 | Medium | Redirect list targets were not validated, so an editor could store a `javascript:`, `data:` or protocol-relative `//host` target. Absolute targets were also sent to wp-admin by `wp_safe_redirect` | `smile_core_valid_redirect_target()` accepts only `/path` or `http(s)://host` targets. `allowed_redirect_hosts` lists only hosts that an administrator typed in |
| P4 | Medium | Behind a CDN or proxy, every visitor shares the proxy IP, so one abuser could trip the form and login limits for everyone | `smile_core_client_ip()` trusts `CF-Connecting-IP`/`X-Forwarded-For` only from proxies listed in `SMILE_TRUSTED_PROXIES` or the `smile_trusted_proxies` filter. By default nothing changes, so the headers cannot be spoofed |
| P5 | Low | No server rules for static files: no compression or caching headers, directory listing allowed, readme/license files exposed | `docs/server/htaccess-production.conf` |
| P6 | Open (host) | `/wp-login.php` returns a 302 to `http://127.0.0.1/` for visitors without a cookie. Nothing in WordPress, `.htaccess` or cPanel Redirects causes it | A ticket is needed with VivaWebHost about a server, vhost or WAF login-protection rule. `/wp-admin/` still works for signing in |



| | |
|---|---|
| **Scope** | `smile-community-child` theme 2.14.0, re-checked for 2.17.0 and again in full for 2.28.0 (all PHP, JS, CSS, templates, page data) and the bundled Smile Community Core plugin 1.1.0, re-checked for 1.2.2 |
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
| `admin-post.php?action=smile_save_text` / `smile_save_photos` (2.17.0) | Editors | `check_admin_referer` + `edit_pages`; keys restricted to hex hashes / `sanitize_key`; text `sanitize_textarea_field` (2,000 chars); photo IDs must be image attachments; output still escaped at render |
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

### Re-check for 2.28.0 / Core 1.2.2 (14 October 2026)

- New entry points since the first audit reviewed: Careers (Jobs meta box: nonce, capability, typed sanitising; JobPosting data escaped), talent community form (same nonce, signed timestamp, honeypot, rate limit and allow-list as the other forms), WhatsApp chat (no data sent to the site; GET to wa.me only, allowed by the CSP form rule), branded sign-in page (display only; core login, throttling and generic errors unchanged), Google reviews and office-hours settings (rating validated 0-5, hours validated 0-24).
- Fixed: new-tab links now use rel="noopener noreferrer"; the WhatsApp send no longer relies on window.open "noopener" (which returned null and opened a second copy in the same tab) and instead clears the opener on the new tab.
- No new critical, high or medium findings.
- 2.30.0: two-step login added through the official Two Factor plugin (authenticator app and recovery codes), installed from Smile Setup, with a per-user setup reminder. This closes the earlier recommendation that administrators use two-factor sign-in.

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
