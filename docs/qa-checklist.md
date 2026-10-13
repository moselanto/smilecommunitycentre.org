# QA checklist (staging, then again on production)

Tick each item and note the device or browser you used. Do not mark anything as passed unless you actually tested it.

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
- [ ] An editor (not an admin) can edit a page in Elementor, publish a post and change the M-Pesa details, but cannot see Site setup or Redirects.
- [ ] A new post appears in "Latest from Smile" on the home page and on /stories/.

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
- [ ] Only one Organization schema appears (SEOPress). Check with the Rich Results Test.

## Performance
- [ ] Run PageSpeed Insights on mobile for Home, Donate and a story. Record LCP, CLS and INP here.
- [ ] Target LCP < 2.5 s, CLS < 0.1 and INP < 200 ms. If the hero image is too heavy, replace it with a 1600 px WebP.
- [ ] WP Rocket: form pages still work while logged out. The cache lifespan is 10 hours or less.

## Safeguarding and privacy
- [ ] No child's full name, school or exact situation appears in any page, caption, filename or alt text.
- [ ] Download two imported photos and confirm with an EXIF viewer that they have no GPS or camera data.
- [ ] The centre has signed off on every photo in migration-manifest.md.
- [ ] The Privacy Policy has been reviewed and matches the plugins actually installed.

## Backup and recovery
- [ ] A backup exists, has been downloaded and has been restored once as a test.
