# Known limitations and open decisions

## Needs the centre's confirmation before launch
1. **Number of children in care:** the live site says both 85 and 75, so the new site gives no figure. Add the confirmed number to the Home and FAQ pages.
2. **Founder name and biography:** confirm "Margret" vs "Margaret", and that she is happy with the level of detail.
3. **M-Pesa:** confirm that Paybill 247247 and account 147516 are current, and add the account name donors will see (Centre details).
4. **Bank details and online giving:** the old page referred to bank details, but none were published. Add them in Centre details, or leave them hidden.
5. **Volunteer programme:** places are currently shown as **closed**, matching the live site. Confirm the US$165 fee and the safeguarding conduct rules.
6. **Testimonials:** three Google review quotes with names are on the home page. Confirm the reviewers are happy, or remove the names.
7. **Photo consent:** all 24 imported photos are tagged "pending-review" until the centre approves them.
8. **Privacy Policy:** this is a draft and needs review by the centre or its adviser.
9. **Giving amounts:** the KES/USD examples are kept from the live site and labelled as estimates. Confirm they are still accurate.

## Technical
- **Elementor Pro is not required.** Without it:
  - The header and footer come from the theme. Menus, logo and Donate button are editable via Appearance > Menus and Customize, but not with drag-and-drop.
  - The blog archive and single-post layouts are theme templates.
  - "Latest stories" uses a shortcode rather than the Pro Posts widget.
  - Forms are provided by the plugin, not Elementor Forms.
  With Pro, Theme Builder templates can replace any of these, and they take priority automatically.
- **No online card or M-Pesa STK payments are built.** There are no approved credentials or provider, so only the verified manual M-Pesa method is shown. A Daraja STK Push or card gateway can be added once the centre approves a provider and supplies credentials. It needs server-side callback verification and a sandbox test first.
- **Email delivery** depends on SMTP being configured on the host. The forms report honestly if email fails, and every message is also stored.
- **PHP was not run during development**, because no PHP runtime was available in the build environment. The first install on staging is the first real execution, so check the Site setup log and the PHP error log.
- **Images are imported from the live site at their existing size.** Some are small or low quality. A consented, recent photo shoot of the Mzesa centre, staff and activities is recommended.
- **Fonts** (Fraunces and Inter) load through Elementor. Set them to load locally as described in install-and-deploy.md.
- **Performance and Core Web Vitals have not been measured yet.** They must be measured on staging.
- **Cistern of Hope reference:** the brief mentions this as an internal reference. This design is deliberately its own (a warm teal and sunshine palette with an editorial serif) and copies nothing from it.
