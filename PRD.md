# Product requirements: Smile Community Centre website

| | |
|---|---|
| **Product** | smilecommunitycentre.org |
| **Owner** | Smile Community Centre Children's Home |
| **Status** | Staging (topnotchmall.co.ke/smilescommunity), preparing for launch |
| **Theme** | smile-community-child 2.28.0, Smile Community Core 1.2.2 |

## 1. Purpose

Give the centre a trustworthy, international-standard website that tells its story, protects the children in its care, and turns visitors into donors, volunteers and partners.

## 2. Goals

1. **Raise support**: make giving by M-Pesa, bank and in-kind obvious and quick.
2. **Build trust**: show who the centre is, what it does and its safeguarding standards.
3. **Recruit help**: clear paths for volunteers, partners and visitors.
4. **Protect children**: no identifying details, photo metadata stripped, a removal-request route.
5. **Be easy to run**: staff edit details in one place; enquiries arrive in a private inbox.

## 3. Audiences

| Audience | Needs |
|---|---|
| Kenyan donors | Paybill details in seconds, proof the money is used well |
| International donors and partners | Credibility, programmes, bank and online options |
| Volunteers (local and overseas) | Roles, requirements, fees, safeguarding, application form |
| Visitors and community | Directions, visit booking, contact |
| Children's officers, social workers | Safeguarding policy, concern reporting |
| Centre staff | Simple editing, enquiries inbox, notifications |

## 4. Functional requirements

| ID | Requirement |
|---|---|
| F1 | 21 pages covering story, programmes, impact, giving, involvement, contact, safeguarding, privacy |
| F2 | Home hero slider, programmes, impact, gift calculator, stories, testimonials, partners |
| F3 | M-Pesa Paybill 247247 / Account 147516 with copy buttons on Home, Donate and Get Involved |
| F4 | Six enquiry forms stored privately and emailed to the centre; removal requests to the safeguarding address |
| F5 | Stories & News with topics, search, pagination and reading time |
| F6 | Gallery with filters and lightbox |
| F7 | Volunteer page with roles, requirements, international details and application |
| F8 | Safeguarding page with commitments, urgent numbers and concern steps |
| F9 | Redirects from old URLs |
| F10 | One-zip install with a setup wizard and one-click import |
| F11 | Every visitor-facing text and section photo editable by staff without code |
| F12 | Search-ready: schema.org graph, keyword-mapped titles and descriptions |
| F13 | High-resolution brand logo and site icons |

## 5. Non-functional requirements

| Area | Requirement |
|---|---|
| Performance | First content in 1 to 3 seconds on mobile with page caching; minified assets; lazy images |
| Accessibility | WCAG 2.1 AA: keyboard use, focus states, labels, alt text, reduced motion |
| Security | See [SECURITY-AUDIT.md](SECURITY-AUDIT.md): headers, HTTPS, rate limits, sanitisation, escaping, short sessions |
| Privacy | Raw IPs never stored; enquiries deleted after the retention period; no children's identifying data |
| SEO | Titles and descriptions on every page, clean URLs, sitemap, one H1 per page |
| Maintainability | Quality gate on every build; documented release workflow |
| Compatibility | WordPress 6.3+, PHP 7.4+; modern browsers; mobile first |

## 6. Success measures

- Donations and donation-form messages per month
- Volunteer and partner applications per month
- Mobile load time under 3 seconds (PageSpeed)
- Zero safeguarding complaints about published content
- Zero high or critical security findings at each audit

## 7. Out of scope

- Online card payments processed on the site (giving stays on M-Pesa, bank and trusted external links)
- Public user accounts or member areas
- Storing identity documents (forms tell people not to send them)

## 8. Open items for the centre

- Confirm emergency numbers (116, 999, 112), gift calculator estimates and pull quotes
- Confirm office days for the "open now" badge
- Enable page caching and SMTP on production
