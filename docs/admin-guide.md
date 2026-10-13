# Administrator guide (for Smile staff)

Everything below is done from the WordPress dashboard (`/wp-admin`). No code is needed.

## Edit a page
1. Go to **Pages** and hover over the page.
2. Click **Edit with Elementor**.
3. Click any text to change it. To change a photo, click it and choose **Choose Image**, then pick it from the Media Library.
4. Click **Update** (bottom left).

Tips:
- Keep the section layout. Change the words and pictures inside it.
- Buttons have a **Link** box. Use addresses like `/donate/`.
- A "Shortcode" box such as `[smile_mpesa]` shows live information from **Smile Centre > Centre details**. Do not type numbers into it; change them in Centre details instead.

## Change phone numbers, M-Pesa, bank details or the volunteer notice
Go to **Smile Centre > Centre details**, edit the value and click **Save details**. This updates the header, footer, Donate, Contact, FAQ and forms everywhere.
- The bank transfer block and the online giving button stay hidden until you fill them in.
- Tick **Volunteer places are open** when you are accepting volunteers again.

## Publish a story or news item
1. Go to **Posts > Add New**.
2. Write a title and the story.
3. On the right, choose a **Category** (Stories of Hope, News, Guides or Community) and set a **Featured image**.
4. Fill in the SEO title and description (SEOPress box below the editor).
5. Click **Publish**. The story appears on the home page and in Stories & News automatically.

**Before you publish anything about a child:**
- Use only photos the centre has permission to publish.
- Do not include full names, schools, exact circumstances or location details.
- Name files neutrally, for example `feeding-day-2026-11.jpg`.
- Write alt text that describes the scene, for example "Children sharing lunch at the centre".

## Read enquiries
Go to **Smile Centre > Enquiries**. Every form message is saved here, even if the email notification fails.
- Open an enquiry to see the details, reply by email and tick **Handled**.
- Enquiries are deleted automatically after the number of days set in Centre details (365 by default).
- Only Administrators and Editors can see enquiries.

## Change the menu
Go to **Appearance > Menus**. Drag items to reorder them, or add pages from the left. There are four menus: Main, Footer, Footer programmes and Footer legal.

## Change the logo or the Donate button
Go to **Appearance > Customize**:
- **Site Identity** changes the logo.
- **Smile: Header & Footer** changes the Donate button text and link, the top contact bar and the footer mission line.

## SEO titles and descriptions
Each page and post has a SEOPress box below the editor. Keep titles under about 60 characters and descriptions under about 155.

## Requests to remove a photo or story
These arrive in Enquiries as "Safeguarding / photo removal". Unpublish the item first (set the post to Draft, or delete the image from the Media Library), then look into it.

## What only an administrator should change
Plugins, themes, Smile Centre > Site setup, Smile Centre > Redirects, the Forms and privacy settings, users and roles, and Settings > Reading (search engine visibility).

Give staff the **Editor** role, not Administrator. Administrators should use two-factor login (WP 2FA).
