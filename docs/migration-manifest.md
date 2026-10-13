# Content and media migration manifest

Source: the public audit of https://smilecommunitycentre.org/ on 13 Oct 2026. The full audit is in `audit.md`.

## Pages

| New page (slug) | Built from (old URL) | Notes |
|---|---|---|
| Home (`/`) | `/` | Rewritten. The "85 children" figure is omitted pending confirmation. |
| About Us (`/about-us/`) | `/about-us/` | History, milestones, mission and vision. Outdated World Bank and Oxfam statistics removed. |
| Our Founder (`/our-founder/`) | `/our-founder/` | Shortened. Personal medical and marital details reduced. **Margret to approve.** |
| Our Goals (`/our-goals/`) | `/our-goals/` | Current objectives kept separate from future aspirations. |
| Our Programmes (`/our-programmes/`) | new | Overview cards. |
| Education Programme (`/education-program/`) | `/education-program/` | Former residents' names (Abel, Laurence) removed. Outcome kept anonymously. |
| Feeding Programme (`/feeding-program/`) | `/feeding-program/` | |
| Child Care and Shelter (`/child-care-and-shelter/`) | new, from Donate, FAQ and HIV pages | Authorisation, care reform and adoption policy. |
| Health and Wellbeing (`/health-and-wellbeing/`) | `/hiv-and-the-community/` | Redirected. The 2007 statistic removed. |
| Sports and Talent (`/sports/`) | `/sports/` | |
| Our Impact (`/our-impact/`) | new | Verified facts only. |
| Gallery (`/gallery/`) | `/photos/` | Redirected. Only approved photos are used. |
| Stories & News (`/stories/`) | `/blog/` | Redirected. This is the WordPress posts page. |
| Get Involved (`/get-involved/`) | new | |
| Volunteer With Us (`/volunteer/`) | `/volunteer/` | Safeguarding conduct added (**needs centre approval**). The open/closed status is a setting. |
| Partner With Us (`/partner-with-us/`) | new | Presented as opportunities, not existing partnerships. |
| Donate (`/donate/`) | `/donate/` | The "Mary" story is removed until consent is confirmed. Giving amounts are labelled as estimates. |
| Contact Us (`/contact-us/`) | `/contact-us/` | Form and visit request added. |
| FAQ (`/faq-page/`) | `/faq-page/` | The 75 vs 85 conflict is neutralised and the ages are generalised. |
| Safeguarding (`/safeguarding/`) | new | Includes Childline Kenya 116 and the removal request form. |
| Privacy Policy (`/privacy-policy/`) | `/privacy-policy/` | Rewritten as a draft for the Kenya Data Protection Act. **Needs review.** |

## Blog posts (27)
Import all posts with the WordPress exporter, then:
- **Keep (18):** the 2024 to 2026 guides and the 2021 organisation stories listed in `audit.md`.
- **Safeguarding review before republishing (4):**
  - "A day in the life of our founder's work routine in slums" names Purity, Pamela and the twins. Remove the names and the home photos, or unpublish.
  - "Real-life stories from Smile Community Centre Children's Home"
  - "Life as it is"
  - "What vulnerability feels like"
- **Retire (5):** Titanic submarine, Hustler Fund, Comoros/Kenya, COVID announcement, and "Did you know children account for half...". Set them to Draft. Their redirects to `/stories/` are already set up.
- **Stock images:** replace the Pexels stock images used as featured images with real, approved photos, or leave the featured image empty.

## Photographs imported by Smile Setup (24)
Every photo is renamed, stripped of metadata and given alt text. Each file is tagged `_smile_source` (original URL) and `_smile_consent = pending-review` in WordPress.

| Key | Original file (uploads/) | New filename | Alt text | Consent |
|---|---|---|---|---|
| logo | 2021/03/smile_community_centre_logo.jpg | smile-community-centre-logo.jpg | Smile Community Centre logo | Pending centre sign-off |
| centre | 2021/06/smilecommunitycentre.jpg | smile-centre-soweto-kayole.jpg | Smile Community Centre Children's Home in Soweto, Kayole, Nairobi | Pending centre sign-off |
| centre-new | 2024/07/centresmile.png | smile-centre-mzesa-building.png | The centre's current rented building at Mzesa, Soweto | Pending centre sign-off |
| soweto | 2021/06/soweto_sitep.jpg | soweto-kayole-neighbourhood.jpg | The Soweto and Kayole neighbourhood in Nairobi's Eastlands, where the centre is located | Pending centre sign-off |
| meal | 2026/08/donate_smile.png | children-sharing-a-meal.png | Children sharing a meal at the centre | Pending centre sign-off |
| pupils | 2026/07/486729868_1087360600100012_8455447626077969328_n.jpg | young-pupils-outside-the-centre.jpg | Young pupils in school uniform outside the centre | Pending centre sign-off |
| founder-serving | 2026/07/609173910_18066322541550137_7263296275536633456_n.jpg | founder-serving-children.jpg | Founder Margret Nekesa serving a meal to children at the centre | Pending centre sign-off |
| founder-school | 2024/12/325528061_709180737315255_7858314926099917907_n.jpg | founder-with-school-children.jpg | Founder Margret Nekesa with school-going children from the centre | Pending centre sign-off |
| founder-soweto | 2021/02/MargretinSoweto.jpg | founder-in-soweto.jpg | Margret Nekesa walking in Soweto | Pending centre sign-off |
| founder-serving-2021 | 2021/02/Maggy_serving_children.jpg | founder-serving-children-2021.jpg | Margret Nekesa serving children | Pending centre sign-off |
| education | 2021/02/Education.jpg | education-programme-overview.jpg | Children from the centre studying | Pending centre sign-off |
| highschool | 2024/12/452515955_18009711326550137_2779251292477401680_n.jpg | secondary-school-students.jpg | Secondary school students supported by the centre | Pending centre sign-off |
| schoolkids | 2021/04/schoolkids2.jpg | primary-pupils-at-the-centre.jpg | Primary school pupils at the centre | Pending centre sign-off |
| feeding1 | 2021/02/Serving-Kids.jpg | feeding-programme-soweto.jpg | Children being served food during a community feeding programme in Soweto | Pending centre sign-off |
| feeding2 | 2021/04/feeding.jpg | feeding-programme-meal.jpg | Children eating during a feeding programme | Pending centre sign-off |
| feeding3 | 2024/12/362298880_677995781036498_8911277294567252744_n.jpg | children-eating-at-the-centre.jpg | Children eating together at the centre | Pending centre sign-off |
| staff-serving | 2024/12/449081928_890360169800057_1906894275461205900_n.jpg | staff-serving-children.jpg | A staff member serving children at the centre | Pending centre sign-off |
| mealtime | 2024/12/449618445_890360196466721_2360178954375711370_n.jpg | children-at-mealtime.jpg | Children at mealtime at the centre | Pending centre sign-off |
| football | 2021/02/Soccer.jpg | football-team-jacaranda-grounds.jpg | A football team that children from the centre play in at Jacaranda Grounds | Pending centre sign-off |
| skipping | 2021/03/IMG-20210313-WA0036.jpg | rope-skipping-jacaranda-grounds.jpg | Children rope skipping at Jacaranda Grounds | Pending centre sign-off |
| doctors | 2024/12/447963250_874996954669712_37164957290925775_n-e1734236902509.jpg | volunteer-doctors-visit.jpg | Volunteer doctors with children from the centre | Pending centre sign-off |
| iebc | 2024/12/356118476_658586159644127_3934087309319921065_n.jpg | iebc-visit.jpg | A visit to the centre by staff of the Independent Electoral and Boundaries Commission (IEBC) | Pending centre sign-off |
| volunteer | 2021/02/volunteer_at_smile.jpg | volunteering-at-smile.jpg | A volunteer spending time with children at the centre | Pending centre sign-off |
| gift | 2024/12/402293787_742414474594628_2211120368292308434_n.jpg | child-with-visitor-gifts.jpg | A child holding gifts brought by a visitor | Pending centre sign-off |

## Photographs NOT imported
- **Identifiable people:** the 2021 "day in the life" set (house_closed, thats_where_purity_stays, purityfriends, puritypamela, pamela_purity, two_twins, maggyholdingtwins, donations_maggy), poor_mother and poor_mother-1, and abel_laurence (named children).
- **Stock images (Pexels and others):** never presented as Smile's own work.
- **Other:** partner logos (Lift the Children, GivingWay, Benevity) until permission and current status are confirmed. The infographic (figures out of date) and the M-Pesa paybill image (replaced by the live text block).

## Video
The flooding reel is hosted on Facebook (https://www.facebook.com/reel/366105888367538). Please send the original video file so it can be hosted locally if it is wanted on the About page.
