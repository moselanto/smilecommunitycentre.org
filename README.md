# smilecommunitycentre.org

The Smile Community Centre Children's Home WordPress theme. It is packaged like a ThemeForest theme: **upload one zip, activate it, the wizard installs the plugins, import the content, and the site is ready.**

## Install (3 steps)

1. **Upload the theme:** go to Appearance > Themes > Add New > Upload Theme and choose `smile-community-child.zip`. WordPress installs the parent theme, Hello Elementor, automatically. Then click **Activate**.
2. **Install plugins:** the **Smile Setup** wizard opens. Click **Install & activate**. This installs:
   - Elementor (required, from WordPress.org)
   - Smile Community Core (required, included inside the theme)
   - SEOPress and WP Mail SMTP (recommended)
3. **Import content:** click **Import content**. The wizard builds 21 Elementor pages, imports 24 approved photos, builds 4 menus, sets the home page and applies the brand colours and fonts. When it finishes, the site is ready.

The wizard can be reopened at any time from **Appearance > Smile Setup**. Until the required plugins are active, an admin notice links back to it.

## Getting the zip

- Every push to `main` runs a GitHub Action (Actions > "Build installable theme zip"). It checks PHP syntax and attaches `smile-community-child.zip` as a download.
- To build it locally, run `bash scripts/build-theme-zip.sh`. The output is `dist/smile-community-child.zip`.

## Repository layout

```
wp-content/themes/smile-community-child/
  style.css, functions.php            theme (child of Hello Elementor)
  inc/setup.php, template-tags.php    theme setup, helpers
  inc/customizer.php                  Donate button, top bar, footer line
  inc/plugins.php                     required/recommended plugin installer + admin notice
  inc/wizard.php, importer.php        Smile Setup wizard and one-click content import
  template-parts/                     header, footer, single, archive, search, 404, cards
  assets/css, assets/js               design system and front-end behaviour
  assets/admin/                       wizard styles and script
  demo/pages.json, images.json, menus.json   website content
  plugins/smile-community-core/       bundled plugin: centre details, M-Pesa block,
                                      forms + enquiries inbox, redirects, photo privacy
docs/                                 install, admin guide, audit, migration, QA, limitations
scripts/build-theme-zip.sh            builds the installable zip
```

## Why the core features sit in a bundled plugin

The forms inbox, centre details and redirects store data. If they lived in the theme, they would stop working if the theme were ever changed. Bundling the plugin (the same approach ThemeForest themes take) keeps the single-zip install while protecting that data.

## Requirements

- WordPress 6.3+ and PHP 7.4+ (8.1+ recommended)
- The server must be able to install plugins directly. Most hosts can. If yours asks for FTP details, install the plugins from Plugins > Add New.
