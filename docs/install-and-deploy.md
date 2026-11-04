# Install, deploy and roll back

## Staging: https://topnotchmall.co.ke/smilescommunity/

1. **Back up** the staging site (files and database).
2. **Upload the theme:** go to Appearance > Themes > Add New > Upload Theme and choose `smile-community-child.zip`. Download it from the latest run of the "Build theme" GitHub Actions workflow (Actions tab > Build theme > Artifacts), or build it locally with `scripts/build-theme-zip.sh`. WordPress installs Hello Elementor (the parent theme) automatically. Then click **Activate**.
3. **Smile Setup, step 1 (Plugins):** click **Install & activate**.
4. **Smile Setup, step 2 (Content):**
   - If staging is a **copy of the old website**, tick "Replace existing pages". The old content is kept as a revision.
   - Leave "Hide from search engines" ticked on staging.
   - Click **Import content** and watch the progress bar. Click "Details" to see each step.
5. **Smile Setup, step 3 (Ready):** follow the finish-off list:
   - check Centre details
   - set up SMTP
   - import the old blog posts
   - set Elementor fonts to load locally
6. Work through `qa-checklist.md`.

The wizard can be run again safely. Photos are reused rather than downloaded twice, and pages it created are refreshed.

### Updating the theme later
Upload the new zip and choose "Replace current with uploaded". If the bundled Smile Community Core plugin has a newer version, Smile Setup > Plugins shows "Update available". Tick it and click Install & activate.

## Production: https://smilecommunitycentre.org/ (live since Nov 2026)

## Production launch (after sign-off)
1. Take a verified full backup of production. Download it and test that it restores.
2. Choose one option:
   - **Push staging to production** with the host's staging tool or a migration plugin. Search and replace `topnotchmall.co.ke/smilescommunity` with `smilecommunitycentre.org` using a tool that handles serialized data (WP-CLI `wp search-replace --all-tables --precise`, or Better Search Replace). Also replace the JSON-escaped form `topnotchmall.co.ke\/smilescommunity` used inside Elementor data, then run Elementor > Tools > Replace URL and Regenerate CSS & Data.
   - **Sub-folder to root:** the staging `.htaccess` has `RewriteBase /smilescommunity/`. On production it must be the standard WordPress block (`RewriteBase /` and `RewriteRule . /index.php [L]`). Re-save Settings > Permalinks to regenerate it. If inner pages, `/robots.txt` or `/sitemaps.xml` return 500, this is the cause.
   - Check `siteurl` and `home` in Settings > General (or `wp_options`) are both `https://smilecommunitycentre.org`.
   - **Or install on production:** repeat steps 2 to 5 with "Replace existing pages" ticked and "Hide from search engines" unticked.
3. Settings > Reading: make sure "Discourage search engines" is **unticked**.
4. Check HTTPS, `/sitemaps.xml`, canonical URLs and every redirect in `redirects.md`.
5. Test the donate, contact, volunteer and visit forms live, including email delivery.
6. Submit the sitemap in Google Search Console and watch for 404 errors for two weeks.

## Rollback
- **Theme:** switch back to the previous theme. The bundled plugin stays installed and no content is deleted.
- **A single page:** restore it from Revisions.
- **Full rollback:** restore the backup from launch step 1.
