# Smile theme: release workflow

Every change goes through the same five steps.

1. **Edit** files in `wp-content/themes/smile-community-child/`.
2. **Bump the version** in two places: `style.css` (`Version:`) and `functions.php` (`SMILE_THEME_VERSION`).
3. **Build**: `bash scripts/build-theme-zip.sh`
   - Rebuilds the minified files in `assets/build/`.
   - Lists every editable text (`scripts/extract-strings.py` > `demo/strings.json`).
   - Runs the quality gate `scripts/check-theme.py`. If anything fails, no zip is made.
4. **Commit** to `main` with a message that lists what changed and why.
5. **Install** on the site:
   - Appearance > Themes > Add New > Upload > "Replace active with uploaded".
   - If `demo/pages.json` changed, click **Apply latest design** in the dashboard notice.

## What the quality gate checks

- No stray backslash before an exclamation mark (this broke PHP once and 53 CSS rules once).
- PHP syntax of every PHP file (`php -l`).
- JavaScript syntax (`node --check`).
- CSS parses with no errors (`tinycss2`).
- `pages.json`: every shortcode exists, every photo key exists, every internal link points to a real page, every element ID is unique, and every page banner has exactly one H1.

## After installing

- Open the home page and two inner pages, and check the top bar, header, banner and footer.
- Check one page on a phone (menu, bottom bar, slider).
- Confirm the live version: open `.../wp-content/themes/smile-community-child/style.css` and read the `Version:` line.

## Writing code that stays editable

- Put every visitor-facing sentence in `__()` / `esc_html__()` with the `smile-community` domain. It then appears in Site text & photos automatically.
- Use `smile_image_by_key( 'key' )` for section photos, so staff can replace them.
- Use `smile_setting()` for contact and payment details.
- Add a line to `CHANGELOG.md` with every release.
