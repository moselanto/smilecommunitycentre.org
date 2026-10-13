# Install, deploy and roll back

## Staging: https://topnotchmall.co.ke/smilescommunity/

### 1. Before you start
- Take a full backup of the staging site (files and database) with the host's tools or BackWPup, and download a copy.
- Confirm that staging is **not** indexed: Settings > Reading > "Discourage search engines" should be ticked. The setup tool ticks it for you.

### 2. Install dependencies
1. Plugins > Add New: install and activate **Elementor**.
2. Appearance > Themes > Add New: install **Hello Elementor**. Do not activate it.
3. Recommended stack (one plugin per job):
   - **SEOPress** (free) for SEO titles, sitemap and schema. It is already used on the live site, and the setup tool writes its title and description fields.
   - **WP Rocket** (paid, already on the live site) or the host's cache for caching.
   - **WP Mail SMTP** (free) or the host's SMTP, so that form emails are actually delivered. This is required.
   - **BackWPup** (free) for backups.
   - **WP 2FA** for two-factor login for administrators.
   - Do **not** install another form, slider, SEO or animation plugin. The site does not need one.

### 3. Upload the code
Either:
- **Zip upload:** zip `wp-content/themes/smile-community-child` and upload it at Appearance > Themes > Add New > Upload. Zip `wp-content/plugins/smile-community-core` and upload it at Plugins > Add New > Upload. Or
- **SFTP/Git deploy:** copy both folders into `wp-content/themes/` and `wp-content/plugins/` on the server.

Then activate the **Smile Community Centre** theme and the **Smile Community Core** plugin.

### 4. Build the site
Go to **Smile Centre > Site setup**.
- Leave "Import the approved photographs" ticked. This downloads 24 photos from smilecommunitycentre.org, renames them neutrally, strips their metadata and adds alt text.
- If staging is a **copy of the live site**, tick "Replace the content of existing pages". The old content is kept as a revision and in the page's `_smile_previous_content` field.
- Click **Build the site**. The log lists every page, photo and menu it created.

The setup tool can be run again safely. Photos are reused rather than downloaded twice, and pages it built earlier are refreshed.

### 5. Finish
- Settings > Permalinks: click Save once.
- Elementor > Settings > Advanced: set Google Fonts to **Load locally** (better for speed and privacy). The fonts used are Fraunces for headings and Inter for text.
- Smile Centre > Centre details: check every value, especially "Send new enquiries to".
- Set up SMTP, then send a test from each form (see `qa-checklist.md`).
- Migrate blog posts: on the live site go to Tools > Export > Posts, then on staging go to Tools > Import > WordPress, ticking "Download and import file attachments". Then apply the keep/retire decisions in `migration-manifest.md`.

## Production launch (after sign-off)
1. Take a verified full backup of production. Download it and test that it restores.
2. Put production into maintenance mode briefly.
3. Choose one option:
   - **Push staging to production** with the host's staging tool or a migration plugin. Search and replace `topnotchmall.co.ke/smilescommunity` with `smilecommunitycentre.org`.
   - **Or rebuild on production:** repeat steps 2 to 5 on production with "Replace the content of existing pages" ticked.
4. Settings > Reading: **untick** "Discourage search engines".
5. Check HTTPS, the sitemap (`/sitemaps.xml` from SEOPress), canonical URLs and every redirect in `redirects.md`.
6. Test the donate, contact, volunteer and visit forms live, including email delivery.
7. Submit the sitemap in Google Search Console and watch for 404 errors for two weeks.

## Rollback
- **Code only:** deactivate the Smile Community Core plugin and switch back to the previous theme. No content is deleted.
- **A single page:** open the page, go to Revisions, and restore the old version (or copy it from `_smile_previous_content`).
- **Full rollback:** restore the backup taken in launch step 1.

## Updating the code later
Make changes on a branch, open a pull request and test on staging. Then upload the updated folders. Bump `Version` in `style.css` or the plugin header so browsers load the new CSS and JS.
