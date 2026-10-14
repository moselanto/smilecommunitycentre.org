# Known limitations and open decisions

## Needs the centre's confirmation before launch
1. **Number of children in care:** the live site says both 85 and 75, so the new site gives no figure. Add the confirmed number to the Home and FAQ pages.
2. **Founder name and biography:** confirm "Margret" vs "Margaret", and that she is happy with the level of detail.
3. **M-Pesa:** confirm that Paybill 247247 and account 147516 are current, and add the account name donors will see (Centre details).
4. **Bank details and online giving:** the old page referred to bank details, but none were published. Add them in Centre details, or leave them hidden.
5. **Volunteer programme:** places are currently shown as **closed**, matching the live site. Confirm the US$165 fee and the safeguarding conduct rules.
6. **Testimonials:** "What people say" (Our Impact and Get Involved) shows three real Google reviews by Melissa Nz, Eugene Emmanuel and Maureen Ngobili, plus "4.7 out of 5 from 111 Google reviews" (checked 13 Oct 2026). Eugene's review has a small typo fix and is shortened. Confirm the centre is happy to show the names. The rating and count do not update automatically.
11. **Careers:** confirm the hiring steps (two references, Certificate of Good Conduct), the 12-month retention for talent community details, and that the Careers FAQ wording suits the centre.
10. **WhatsApp office hours:** the chat shows "Online now" Monday to Friday, 9am to 5pm Nairobi time. Confirm these hours and whether anyone replies at weekends.
7. **Photo consent:** all 24 imported photos are tagged "pending-review" until the centre approves them.
8. **Privacy Policy:** this is a draft and needs review by the centre or its adviser.
9. **Giving amounts:** the KES/USD examples are kept from the live site and labelled as estimates. Confirm they are still accurate.

## Technical

- The sign-in page restyles WordPress's own markup. A future WordPress update could shift some spacing; check the sign-in page after major WordPress updates. The language drop-down is hidden because the site is English only; remove the `login_display_language_dropdown` filter in inc/login.php if another language is added. The two-factor plugin, if added, will show its own screen inside the same card.
- **Elementor Pro is not required.** Without it:
  - The header and footer come from the theme. Menus, logo and Donate button are editable via Appearance > Menus and Customize, but not with drag-and-drop.
  - The blog archive and single-post layouts are theme templates.
  - "Latest stories" uses a shortcode rather than the Pro Posts widget.
  - Forms are provided by the plugin, not Elementor Forms.
  With Pro, Theme Builder templates can replace any of these, and they take priority automatically.
- **No online card or M-Pesa STK payments are built.** There are no approved credentials or provider, so only the verified manual M-Pesa method is shown. A Daraja STK Push or card gateway can be added once the centre approves a provider and supplies credentials. It needs server-side callback verification and a sandbox test first.
- **Job applications** are by email or an external link (for example a Google Form). The site does not accept CV file uploads, to avoid storing personal documents on the web server; the talent community form takes a CV or LinkedIn link instead.
- **Careers form needs Smile Community Core 1.2.0.** Until the plugin is updated (Smile Setup shows "Update"), the talent community section shows an "Email your CV" button instead of the form.
- **Email delivery** depends on SMTP being configured on the host. The forms report honestly if email fails, and every message is also stored.
- **Automated checks:** every build runs PHP 8.3 syntax checks, JavaScript and CSS checks and security patterns. Visual checks still need a person on staging.
- **Images are imported from the live site at their existing size.** Some are small or low quality. The two bundled slide photos were enlarged from small sources (the feeding photo is about 950px after its printed text was cropped away; the pupils photo was 770px) and may look slightly soft on large screens. Originals from the camera or designer would fix this. A consented, recent photo shoot of the Mzesa centre, staff and activities is recommended.
- **Fonts** (Newsreader, Figtree, Caveat) are self-hosted by the theme since 2.17.0.
- **Performance:** the theme work is done; reaching 1 to 3 seconds also needs page caching, `.htaccess` caching and PHP 8.2 on the host (docs/performance.md). Measure with PageSpeed Insights.
- **Logo source:** the new logo was supplied as a flat image; an SVG from the designer would make it razor sharp at any size.
- **Cistern of Hope reference:** the brief mentions this as an internal reference. This design is deliberately its own (a warm teal and sunshine palette with an editorial serif) and copies nothing from it.
