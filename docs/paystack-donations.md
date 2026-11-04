# Paystack donations: setup and test

## 1. Keys (wp-config.php only)
Add these lines above `/* That's all, stop editing! */`. Never commit keys to Git or paste them into WordPress settings.
```php
define( 'SMILE_PAYSTACK_SECRET_KEY', 'sk_live_...' );       // Paystack > Settings > API Keys & Webhooks
define( 'SMILE_PAYSTACK_TEST_SECRET_KEY', 'sk_test_...' );  // for staging
define( 'SMILE_PAYSTACK_MODE', 'test' );                    // staging only; remove on production
```
Only the secret key is needed, because the site redirects donors to Paystack's own checkout page.

## 2. Webhook
In Paystack > Settings > API Keys & Webhooks, set the webhook URL to `https://smilecommunitycentre.org/wp-json/smile/v1/paystack`. On staging, use `https://topnotchmall.co.ke/smilescommunity/wp-json/smile/v1/paystack` with the test webhook. The settings screen shows the exact URL and whether the key was found. The key itself is never shown.

## 3. Turn it on
- Go to Smile Centre > Centre details > Online donations (Paystack) and tick "Show the online donation form". Check the currencies (only list currencies that are enabled on the Paystack account), the amounts and the fee percentages.
- On the Donate page, add a Shortcode widget with `[smile_donate_form]` above the M-Pesa block. Add `[smile_manage_giving]` further down; it uses the `#manage` anchor that monthly receipts link to.
- Clear WP Super Cache.

## 4. Test on staging (test mode)
Paystack test card: 4084 0840 8408 4081, any future expiry date, CVV 408.
1. One-time KES 2,500: the payment is approved, the thank-you panel appears, a receipt arrives, and the donation shows as "Paid (test)" in Smile Centre > Donations.
2. One-time with "cover the fee" ticked: the amount charged is the gift divided by (1 - fee rate). For example, KES 2,500 at 2.9% becomes KES 2,574.67.
3. Monthly USD 25: the payment is approved and a subscription appears in Paystack. The webhook marks the donation "Subscription: active".
4. Cancel on Paystack's page: you are sent back with "not completed" and nothing is marked paid.
5. In the manage form, enter the donor's email: Paystack sends the manage-link email. Enter an unknown email: the same message is shown.
6. Webhook security: a request without a valid `x-paystack-signature` returns 401.
7. Export CSV: the file opens in a spreadsheet, and cells starting with `=` are prefixed with `'`.

## 5. Go live
On production, add the live key without `SMILE_PAYSTACK_MODE`, set the live webhook URL, make one real small gift and refund it from Paystack, then announce the form.

## Notes
- **Renewals:** Paystack charges monthly gifts automatically. Each renewal is recorded through the webhook as "Monthly (renewal)". If a renewal fails, Paystack retries and emails the donor, and staff receive a notice.
- **Fees:** the fee percentages are estimates for the "cover the fee" option only. Check Paystack's current Kenya pricing and update them in settings.
- **Records:** donations are private records in WordPress and are not shown publicly. Financial records are not deleted automatically.
