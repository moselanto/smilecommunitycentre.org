# Contributing

Thank you for helping maintain the Smile Community Centre website.

## Workflow

- Commit directly to `main`; no feature branches or pull requests.
- Every change ships complete, in one commit:
  - the version bump in **both** `style.css` (`Version:`) and `functions.php` (`SMILE_THEME_VERSION`);
  - a new entry at the top of [CHANGELOG.md](CHANGELOG.md);
  - the README badge and release table when the version changes;
  - the docs (staff guide, ARCHITECTURE, known limitations, QA checklist and so on) whenever features, files, settings or editing steps change.
- Include the generated files when sources change: `assets/build/*` for CSS or JS, `demo/strings.json` for new or changed texts.

## Build and check

```bash
bash scripts/build-theme-zip.sh
```

This minifies assets, extracts every editable text, runs the quality gate (`scripts/check-theme.py`) and writes `dist/smile-community-child.zip`. Nothing is zipped if a check fails.

## Coding standards

- Presentation lives in the theme; data (centre details, enquiries, redirects) lives in the Smile Community Core plugin.
- Escape all output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`); sanitise all input by type; use nonces and capability checks on every write.
- Every visible string is translatable with the `smile-community` text domain so staff can edit it in **Site text & photos**.
- Respect accessibility (keyboard, focus, labels, reduced motion) and safeguarding (no children's full names, schools or exact circumstances).

## Releasing

1. Build the zip.
2. On the site: Appearance > Themes > Add New > Upload, **Replace active with uploaded**.
3. If `demo/pages.json` changed: Smile Centre > **Apply latest design**. If the core plugin version changed: Smile Setup > **Update**.
4. Clear the page cache and run the relevant items in [docs/qa-checklist.md](docs/qa-checklist.md).
