# wp-config.php hardening

Add these lines to `public_html/wp-config.php` **above** the line `/* That's all, stop editing! */`.
Keep the file at permission **0600** (set on 4 Nov 2026). Secrets (database password, salts) live only in this
file on the server and are never committed to Git; the repository contains no keys or passwords.

```php
// HTTPS for sign-in and the dashboard.
define( 'FORCE_SSL_ADMIN', true );

// No code editing or plugin/theme installs from the dashboard (updates still work through Smile Setup if you
// set DISALLOW_FILE_MODS to false while updating).
define( 'DISALLOW_FILE_EDIT', true );

// Never show PHP errors to visitors.
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', '0' );

// Fewer stored revisions keeps the database small and fast.
define( 'WP_POST_REVISIONS', 10 );

// Optional: only if the site is put behind Cloudflare or another proxy/CDN.
// List the proxy IPs so rate limits use the real visitor IP (comma-separated).
// define( 'SMILE_TRUSTED_PROXIES', '' );
```

## Salts
If the old staging site's `wp-config.php` was copied to production, generate new salts at
https://api.wordpress.org/secret-key/1.1/salt/ and replace the eight `AUTH_KEY` ... `NONCE_SALT` lines.
Everyone will need to sign in again; this invalidates any session cookies copied from staging.
