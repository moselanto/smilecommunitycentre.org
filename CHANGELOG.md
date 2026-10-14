# Changelog

All notable changes to the Smile Community Centre theme (`smile-community-child`) and the bundled Smile Community Core plugin. Newest first.

## 2.26.1
- **Sign-in page polish after the first live check.** The heading and logo were being cut off at the top on shorter screens; the card now centres safely and never goes above the top edge.
- Logo, heading, messages, fields, button and the "Lost your password?" / "Back to" links now sit together in one white card, with a divider above the links.
- The photo panel now fills the full height of the page, with a lighter tint on the right so more of the photo shows.
- The language drop-down under the card is hidden (the site is English only).
- The secure-connection note is aligned and narrower; the privacy link is quieter. On phones the card overlaps the photo banner slightly.

## 2.26.0
- **New staff sign-in page.** wp-login.php now uses a split layout: a photo panel on the left (deep green over the feeding photo, "Staff and volunteers" label, headline, three things staff can do, and a "Need access or locked out?" line with the centre's phone and email from Centre details), and a clean sign-in card on the right.
- The card has a "Welcome back" heading (or "Reset your password" / "Choose a new password" on those screens), large rounded fields with a green focus ring, a visible show-password button, a full-width "Sign in" button, softer success and error messages, and a secure-connection note under the card.
- On phones and tablets the photo becomes a short banner above the card.
- The site's own fonts now load on the sign-in page too. Keyboard focus, reduced motion and the core WordPress features (password reset, language switcher, privacy link) are kept.
- Every wording is editable in Smile Centre > Site text & photos. The whole design can be turned off with the `smile_login_design` filter.
- New files inc/login.php and assets/css/login.css (minified to assets/build/login.min.css); docs updated.

## 2.25.0
- **Careers.** New Careers page (/careers/, under Get Involved and in the footer) created automatically on first admin visit: hero, "Why work at Smile" values, open roles, "How we hire" four-step process, talent community form and a Careers FAQ with a recruitment-fraud warning.
- **Jobs** admin (Jobs > Add New Job): title, description, excerpt and a Job details box (type, team, location, hours, pay, start, closing date, reference, apply email or link). Roles list on Careers while published and before their closing date (Nairobi time), then drop off automatically; admin list shows type, closing date and whether the role is live.
- Open roles list with type filter chips, "Closes in N days" flag, and a polished "no open positions" state. Job pages with hero, at-a-glance card, Apply button (email with prefilled subject or external link), share links, safeguarding commitment, closed-role notice.
- Google for Jobs JobPosting structured data on open roles; closed roles are noindex.
- Smile Community Core 1.2.0: new "careers" talent community form (area of work, CV/LinkedIn link, consent to keep details 12 months), stored in Enquiries. Email fallback until the plugin is updated.
- New files inc/careers.php and template-parts/job.php; docs updated.

## 2.24.0
- Social profiles: Instagram, Facebook (facebook.com/smilecommunitycentre) and LinkedIn (linkedin.com/company/smile-community-centre) as matching round icon buttons in the top bar and in a new "Follow us" row in the footer Contact column. Shared helper smile_social_profiles()/smile_social_icons() in inc/template-tags.php; links open in a new tab with accessible labels; also used for the organisation sameAs in structured data. New LinkedIn URL field and Facebook default in Smile Centre settings.

## 2.23.1
- "Read all reviews on Google" now opens the reviews panel of the centre's Google listing directly.

## 2.23.0
- "What people say / Voices from our community" (Our Impact and Get Involved pages) now uses real Google reviews: Melissa Nz, Eugene Emmanuel and Maureen Ngobili (all 5 stars). Added a rating summary bar: 4.7 out of 5 from 111 Google reviews, with a "Read all reviews on Google" link (https://share.google/xseS4IIjXcfL36FcG). Rating checked 13 Oct 2026.

## 2.22.7
- Desktop menu: only one dropdown shows at a time. Hovering a menu item hides any other open dropdown (e.g. Impact on the Our Impact page no longer stays open beside Get Involved).

## 2.22.6
- Fix: on desktop, the dropdown of the section you are in (e.g. About on the About pages) no longer stays stuck open. The current section now auto-opens only in the phone/tablet menu. Desktop dropdowns opened with the arrow close on a click elsewhere or Escape, and resizing to desktop closes any open sub-menus.

## 2.22.5
- "Keep every child in school" slide: enhanced photo of the pupils (noise removed, upscaled from 770px to 1600px, local contrast, colour and sharpening), bundled as assets/images/slides/pupils-1600.webp and -960.webp and used instead of the original imported photo. A photo chosen in the Customizer still wins. Slide photos now have their own alt text.

## 2.22.4
- Home page photo banner ("Give a child a reason to smile today"): the M-Pesa line now shows both Paybill 247247 and Account 147516 in a glass pill, each with a Copy button.

## 2.22.3
- First slide: the smile arc now sits directly under the word "smile." instead of under the start of the line.

## 2.22.2
- Home page: "See your impact" and "Get involved" are now hidden by the theme itself, so they disappear on upload even if "Apply latest design" is not run or the home page was edited in Elementor.

## 2.22.1
- Home page shortened: removed the "See your impact" gift calculator and the "Get involved" section (both remain on the Donate and Get Involved pages). Community section now on cream and Latest stories on white to keep the alternating rhythm.

## 2.22.0
- New first home slide "They eat. They dream. They smile.": the feeding photo with its baked-in text cropped away (bundled in assets/images/slides, 1600 and 960 px WebP), and the message rebuilt as real, editable text on a deep green panel (script "Because of you,", bold headline, italic "They smile." with the smile curve, Donate Now and Feeding Programme buttons, "Change a life today." note). Photo can be swapped in Customizer > Smile: Home slider > Slide 1. Slider now supports up to four slides.

## 2.21.0
- WhatsApp chat: floating green button on every page (round above the tab bar on phones, "Chat with us" pill on larger screens) opening a branded chat card with the centre's avatar, online/offline status from Nairobi office hours, a welcome message, quick topic chips and a message box that opens WhatsApp with the message ready to send. One gentle nudge per visit after 12 seconds (not on Contact or Donate). Keyboard and screen-reader friendly, no third-party scripts. New file inc/whatsapp-chat.php; Customizer toggle under Smile: Header & Footer; all text editable in Site text & photos.

## 2.20.2
- Mobile menu polish: no dot on the parent section (only on the exact page), "Go to <section> →" text link instead of "Overview:", flatter evenly inset sub-menu card, tidier spacing.

## 2.20.1
- Updated to the centre's current logo (four children, one carrying a baby): header, footer, sign-in page, schema logo, theme preview picture, and the umbrella-and-children favicon and phone icons.

## 2.20.0
- **Mobile sub-menus:** tapping a section name (e.g. Get Involved) now opens its sub-menu instead of leaving the menu; an "Overview" link at the top still reaches the section page. One section open at a time; the section you are in opens automatically. Sub-menus are white cards with arrows and a yellow dot for the current page.
- Call and WhatsApp buttons in the menu: label above the number, no wrapping.
- Header menu button: three even bars in a soft round button.

## 2.19.1
- **Smile curve toned down:** now used only under words an editor highlights in a heading (slimmer) and on the founder's signature. Removed from small labels, page banner titles, centred titles, the menu, the phone bar, story cards, impact figures and the footer; those return to clean straight lines or a small dot.

## 2.19.0
- **Mobile menu redesign:** full-width panel with the logo at the top and a round close button; large airy links without boxes or dividers; rotating chevrons instead of +/-; sub-menus indented with a fine guide line; current page marked with the smile curve; staggered slide-in; Donate pill plus Call and WhatsApp quick buttons with icons. Phone bottom bar lighter and glassy, Donate button ringed in white.

## 2.18.1
- Favicon and phone icons: the umbrella with the three children only (no yellow box), transparent for browser tabs, on white for the iPhone home screen.

## 2.18.0
- **The Smile curve:** the yellow crescent from the new logo becomes the site's signature mark: under highlighted heading words (drawn in), beside every small label, under page titles and centred section titles, active menu link, phone tab bar, story card hover, founder quote, impact figures and the top of the footer. Accent yellow warmed towards the logo yellow.

## 2.17.0 (13 Oct 2026)
- **Editable content:** new Smile Centre > Site text & photos screen. All 589 visitor-facing texts are listed by section with search; an empty box restores the original. Photos tab replaces any section photo from the Media Library.
- Testimonial names, partner names, founder signature, impact figures and gift amounts are now editable.
- **Speed:** self-hosted fonts with preload; Elementor icon font only where used; speculative prerender for instant page changes.

## 2.16.0
- New logo bundled in 1x/2x/3x PNG and WebP; site icons (32, 180, 192, 512); login page logo; schema logo; theme preview picture updated.

## 2.15.1
- Every page, story and topic gets a title and description; SEOPress filters fed from one resolver; hand-typed values always win.

## 2.15.0
- Connected schema.org graph (NGO, WebSite, pages, breadcrumbs, BlogPosting); keyword-mapped titles and descriptions; indexing rules; `docs/SEO.md`.

## 2.14.1
- Compact gift calculator: one card, two bands.

## 2.14.0
- Security hardening module (headers, HTTPS, login limits, sessions, passwords, enumeration, XML-RPC, CORS); form, importer, JS and SQL fixes; Smile Community Core 1.1.0; README, ARCHITECTURE, ARCHITECTURE-ESSENTIALS, PRD, SECURITY-AUDIT; security checks in the quality gate.

## 2.13.x
- Stories lead story as a clean card, one-line topic bar; Founder quote full-width band; Goals and Safeguarding upgrades that work without re-applying layouts.

## 2.12.x
- Stories "Top stories", topic labels, "Stay close to Smile" band; Volunteer places notice restored.

## 2.11.0
- Founder "Her journey" timeline and portrait quote; Volunteer international facts beside the form.

## 2.10.x
- Stories topics without sideways scroll, 12 stories per page; theme preview picture.

## 2.9.x
- Clean top bar, header and banners; 53 broken CSS rules fixed; full-site scan; quality gate in the build; closing band on one line.

## 2.8.x
- Balanced titles; About, Founder and Goals components; PHP parse error fix; slider disappearing fix; speed tweaks.

## 2.4 to 2.7
- Gallery wall of hope, Impact stats, Stories journal, Get Involved family, Contact hub, Donate.

## 1.7 to 2.3
- Speed (minified bundles, fonts, preload), inner pages, programme pages, signature details, mobile navigation, balance layer.
