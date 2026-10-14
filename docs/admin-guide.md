# Staff guide: editing the Smile website

Everything here is done from the WordPress dashboard (`/wp-admin`). No code is needed. Use an **Editor** account for day-to-day work.

## Quick map: where do I change...?

| I want to change | Go to |
|---|---|
| Words, photos or buttons on a page | **Pages > Edit with Elementor** |
| Any other wording on the site (cards, lists, footer, forms, Stories page, messages) | **Smile Centre > Site text & photos > Text** |
| A photo inside a section (programme cards, gallery, founder, story fallbacks) | **Smile Centre > Site text & photos > Photos** |
| Phone numbers, WhatsApp, email, address, office hours, M-Pesa, bank, volunteer notice | **Smile Centre > Centre details** |
| The home page slider (up to four slides; Slide 1 is "They eat. They dream. They smile.") | **Appearance > Customize > Smile: Home slider** |
| WhatsApp chat button: turn on or off | **Appearance > Customize > Smile: Header & Footer** |
| WhatsApp chat wording (greeting, topic buttons, note) | **Smile Centre > Site text & photos > Text** |
| Instagram, Facebook and LinkedIn links (top bar and footer) | **Smile Centre > Centre details** (empty a link to hide its icon) |
| Google reviews and the "4.7 from 111 reviews" line | **Smile Centre > Site text & photos > Text** (the large "4.7" number needs a theme update) |
| Donate button text or link, top bar, footer line | **Appearance > Customize > Smile: Header & Footer** |
| Menus | **Appearance > Menus** |
| Stories and news | **Posts** |
| Form messages from visitors | **Smile Centre > Enquiries** |
| Advertise a job, close a job | **Jobs > Add New Job** / **Jobs > All Jobs** |
| Talent community CVs | **Smile Centre > Enquiries** (type "Talent community / CV") |

## Change wording anywhere (Site text & photos)

1. Go to **Smile Centre > Site text & photos**.
2. Type a word into the search box, for example `volunteer` or `M-Pesa`. Matching sections open.
3. The left column shows the original wording. Type your new wording in the box on the right.
4. Click **Save text**.
5. To go back to the original, empty the box and save.

Tips:
- Keep the meaning and roughly the same length so the layout stays tidy.
- Numbers such as the Our Impact figures and the gift calculator amounts are in the list too (for example the line `500,1000,2500,5000,8000,15000`). Keep the same format.
- If a change does not show, clear the page cache (WP Super Cache > Delete Cache).

## Replace a photo

1. **Smile Centre > Site text & photos > Photos**.
2. Find the photo, click **Choose photo**, pick or upload an image, click **Use this photo**.
3. Click **Save photos**. "Use original" puts the theme photo back.

Use landscape photos at least 1600 px wide, and only photos the centre has permission to publish.

## Edit a page

1. **Pages**, hover over the page, click **Edit with Elementor**.
2. Click any text to change it. To change a photo, click it and choose **Choose Image**.
3. Click **Update**.

Keep the section layout; change the words and pictures inside it. A "Shortcode" box such as `[smile_mpesa]` shows live information: change its numbers in Centre details, and its wording in Site text & photos.

## Change phone numbers, M-Pesa, bank details or the volunteer notice

**Smile Centre > Centre details**, edit, **Save details**. This updates the header, footer, Donate, Contact, forms, the WhatsApp chat, the M-Pesa Paybill and Account on the home photo banner, the social icons and Google's organisation details everywhere.
- Bank transfer and online giving stay hidden until filled in.
- Tick **Volunteer places are open** when accepting volunteers.

## WhatsApp chat

A green WhatsApp button sits on every page (a round button above the bottom bar on phones, "Chat with us" on larger screens). It opens a small chat card: the centre's name and logo, "Online now" during office hours (Monday to Friday, 9am to 5pm Nairobi time) and "We will reply when the office opens" otherwise, a welcome message, topic buttons and a message box. **Send** opens WhatsApp with the message ready to send to the number in Centre details. A small "Hello there" bubble appears once per visit after 12 seconds (not on Contact or Donate).
- Turn it off: **Appearance > Customize > Smile: Header & Footer**.
- Change the wording: **Smile Centre > Site text & photos > Text** (search for "WhatsApp" or the greeting).
- No outside chat service or tracking is used; conversations happen in WhatsApp.

## Home slider photos

Slides 1 and 2 use photos bundled with the theme (the feeding photo with its printed text removed, and an enhanced pupils photo). Choosing a photo for a slide in **Appearance > Customize > Smile: Home slider** replaces the bundled one; removing it brings the bundled photo back. Use landscape photos at least 1600px wide without text printed on them; the headline and buttons are added by the site.

## Advertise a job (Careers)

The Careers page (Get Involved > Careers, and in the footer) lists open roles automatically. When there are none it shows a "no open positions right now" message and the talent community form.

To add a role: **Jobs > Add New Job**.
1. **Title:** the role name, for example "Child Care Officer".
2. **Main editor:** the full description. Use headings such as *About the role*, *What you will do*, *What we are looking for*, *What we offer*, with bullet lists.
3. **Excerpt** (in the side panel; turn it on under Options/Preferences if hidden): one or two sentences shown on the Careers page.
4. **Job details** box: employment type, team, location (default Soweto, Kayole, Nairobi), hours, pay (optional), start date, **closing date**, reference, and how to apply (an email address, or a link such as a Google Form). If both are empty, applications go to the centre email in Centre details.
5. **Publish.**

The role appears on the Careers page straight away and disappears on its own the day after the closing date. Its page stays online marked "Closed", is hidden from Google, and points people to the talent community. To remove a role early, set it to Draft or move it to the bin. **Jobs > All Jobs** shows each role's type, closing date and whether it is currently on the Careers page.

Open roles are described to Google in the format Google for Jobs reads, so they can appear in Google job search.

Never ask applicants for money; the Careers FAQ tells visitors that any request for payment is a scam.

## Publish a story

1. **Posts > Add New**, write a title and the story.
2. Choose a **Category** and set a **Featured image**.
3. Optional: an SEO title and description in the SEOPress box. If left empty, the site writes good ones automatically.
4. **Publish**. It appears on the home page and in Stories & News.

**Before publishing anything about a child:** only approved photos; no full names, schools, exact circumstances or locations; neutral file names (`feeding-day-2026-11.jpg`); alt text that describes the scene.

## Read enquiries

**Smile Centre > Enquiries**. Every form message is saved here even if the email fails. Open one, reply by email, tick **Handled**. Enquiries are deleted after the retention period in Centre details (365 days by default). Only Administrators and Editors can see them.

## SEO

Titles and descriptions for the main pages, every story and every topic are set automatically for searches like "children's home in Nairobi". A value typed into the SEOPress box always wins. See `docs/SEO.md`.

## The logo

The new logo is built into the theme in high resolution and shows in the header, footer, browser tab and sign-in page. To use a different logo, ask the web administrator.

### Google rating and form choices

The rating bar ("Rated 4.7 out of 5 from 111 Google reviews") is set in **Smile Centre > Centre details > Google reviews**: type the current rating and number of reviews from Google, and the link to the reviews. Leave the rating empty to hide the bar.

The choices in form drop-downs (for example the contact form's "Subject" list or the donation form's "What would you like to support?") can be reworded in **Smile Centre > Site text & photos > Text**: search for the choice and type the new wording.

### The sign-in page

Staff sign in at /wp-login.php (or /wp-admin). The page shows a photo panel with the centre's message on the left and a single white sign-in card (logo, heading, fields and links) on the right; on a phone the photo is a short banner above the box. The phone number and email under "Need access or locked out?" come from **Smile Centre > Centre details**. To change any wording on the page (for example "Welcome back" or the three points in the photo panel), go to **Smile Centre > Site text & photos > Text** and search for the words. After 5 wrong passwords, sign-in pauses for 15 minutes; this is a security feature.

## Photo or story removal requests

They arrive in Enquiries as "Safeguarding / photo removal". Unpublish first (set the post to Draft, or replace the photo in Site text & photos), then look into it.

## Administrators only

Plugins, themes, Appearance > Smile Setup, Smile Centre > Redirects, form and privacy settings, users and roles, Settings > Reading (search engine visibility). Administrators should use two-factor sign-in.
