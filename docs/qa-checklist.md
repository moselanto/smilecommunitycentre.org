# QA checklist (staging, then again on production)

Tick each item and note the device or browser you used. Do not mark anything as passed unless you actually tested it.

## Install flow
- [ ] Upload the zip on a site without Hello Elementor. WordPress installs the parent automatically, and activation works.
- [ ] After activation you are taken straight to Appearance > Smile Setup.
- [ ] Step 1 installs and activates Elementor and Smile Community Core. No Elementor or WP Mail SMTP welcome screen interrupts the wizard.
- [ ] Until the required plugins are active, the admin notice shows on other screens.
- [ ] Step 2 imports with no FAILED lines in Details. Running it a second time creates no duplicate photos or pages.

## Journeys
- [ ] Home > About > Founder reads correctly. No placeholder text and no empty sections.
- [ ] Home > Our Programmes > each programme page > Donate works.
- [ ] Donate: the Paybill and account numbers match Centre details. **Copy** works on a phone. The steps match the real M-Pesa menu.
- [ ] Donate form: submit with an M-Pesa reference. The thank-you message shows, the enquiry appears in Smile Centre > Enquiries, and the email arrives.
- [ ] Volunteer form: submit an empty form. The errors are listed, each field is marked, and focus moves to the error summary. Then submit a valid form and confirm the email arrives.
- [ ] Partner, contact, visit and removal forms: each submits, is stored, and its email arrives. The removal request goes to the safeguarding address.
- [ ] With email deliberately broken (for example a wrong SMTP password), the form shows "saved, but our email notification did not go through", not a false success.
- [ ] Send six messages within an hour. The sixth is rate-limited.
- [ ] Contact: the map loads, and the phone, WhatsApp and email links open the right apps.
- [ ] An editor (not an admin) can edit a page in Elementor, publish a post and change the M-Pesa details, but cannot see Smile Setup or Redirects.
- [ ] A new post appears in "Latest from Smile" on the home page and on /stories/.
- [ ] Home page: slide 1 reads "Because of you, / They eat. / They dream. / They smile." with the arc under "smile."; "See your impact" and "Get involved" do not appear on the home page (they remain on Donate and Get Involved).
- [ ] Home photo banner shows Paybill 247247 and Account 147516; both Copy buttons work on a phone.
- [ ] WhatsApp chat: the button sits above the bottom bar on phones and does not cover it; the card opens and closes (button, X, Escape, click outside); a topic button fills the message; Send opens WhatsApp to the right number with the text.
- [ ] Desktop menu: on an About or Impact page no dropdown is stuck open; only one dropdown shows at a time.
- [ ] Top bar and footer: Instagram, Facebook and LinkedIn icons open the right pages in a new tab.
- [ ] Careers: the page exists at /careers/ and is linked under Get Involved and in the footer. With no jobs it shows "There are no open positions right now"; "Join our talent community" scrolls to the form; the form submits and appears in Enquiries as "Talent community / CV".
- [ ] Add a test job with a closing date tomorrow: it shows on Careers with "Closes in 1 day"; its page shows the at-a-glance card, Apply button (email with subject, or link), share links and safeguarding note. Google's Rich Results Test finds a JobPosting. Set the closing date to yesterday: it leaves the list, its page says "Closed" and is noindex. Delete the test job.
- [ ] With two job types, the filter chips show and filter the list.
- [ ] "What people say" shows the three Google reviews and the rating bar; "Read all reviews on Google" opens the reviews.

## Mobile and accessibility (WCAG 2.2 AA)
- [ ] Test at 360 px, 768 px and 1280 px wide. Nothing scrolls sideways and the buttons are at least 44 px.
- [ ] Mobile menu: opens and closes with Enter and Space, Escape closes it, focus stays inside it, and submenus expand.
- [ ] Tab through Home and Donate. The focus outline is always visible, and the skip link works.
- [ ] Check with WAVE or axe. No contrast errors, all images have alt text, and headings run in order (one H1 per page).
- [ ] Turn on "reduce motion" in the operating system. No movement on hover.

## SEO
- [ ] Each page has a unique title and description (SEOPress).
- [ ] `/sitemaps.xml` is reachable. Staging is noindex, and production is indexable at launch.
- [ ] Every row in redirects.md returns a 301 to the right page.
- [ ] Only one Organization appears (the theme's; SEOPress Knowledge Graph off). Check the home page with the Rich Results Test and the Schema Markup Validator.
- [ ] View source on Home, Donate and a story: the title and meta description match docs/SEO.md.

## Performance
- [ ] Run PageSpeed Insights on mobile for Home, Donate and a story. Record LCP, CLS and INP here.
- [ ] Target LCP < 2.5 s, CLS < 0.1 and INP < 200 ms. If the hero image is too heavy, replace it with a 1600 px WebP.
- [ ] Page cache (WP Super Cache): forms still work while logged out. The cache lifespan is 10 hours or less.
- [ ] No requests to fonts.googleapis.com or fonts.gstatic.com (browser DevTools > Network).

## Editing
- [ ] Change one sentence in Smile Centre > Site text & photos, check it on the site, then empty the box and check the original returns.
- [ ] Replace one photo in the Photos tab, check it, then click "Use original".
- [ ] The new logo shows in the header, footer, browser tab and sign-in page.
- [ ] WhatsApp chat: open it, pick a topic, send; WhatsApp opens once in a new tab with the message filled in. Escape closes it and focus returns to the button. Status matches the office hours in Centre details (check once inside and once outside hours, and on a Saturday).
- [ ] Change the Google rating and count in Centre details: the rating bar on Our Impact and Get Involved updates; empty rating hides it.
- [ ] Reword a form drop-down choice in Site text & photos: the form shows it and still sends.
- [ ] Sign-in page (/wp-login.php): photo panel fills the full height on the left and one white card (logo, heading, fields, links) on the right on desktop; nothing is cut off at the top on a short laptop screen; short banner above the card on a phone; "Welcome back" heading; show-password button works; wrong password shows a red message; "Lost your password?" shows "Reset your password"; the reset email link opens "Choose a new password"; Tab moves through every field with a visible focus ring.

## Safeguarding and privacy
- [ ] No child's full name, school or exact situation appears in any page, caption, filename or alt text.
- [ ] Download two imported photos and confirm with an EXIF viewer that they have no GPS or camera data.
- [ ] The centre has signed off on every photo in migration-manifest.md.
- [ ] The Privacy Policy has been reviewed and matches the plugins actually installed.

## Backup and recovery
- [ ] A backup exists, has been downloaded and has been restored once as a test.
