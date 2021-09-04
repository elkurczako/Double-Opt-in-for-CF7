=== Double opt-in for CF7 ===
Contributors: Elkurczako
Tags: contact form, contact, email, multilingual
Requires at least: 5.5
Tested up to: 5.8
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin adds double opt-in functionality to CF7 forms.

== Description ==

This plugin is an addon to Contact Form 7 and Flamingo plugins by Takayuki Miyoshi. 
Adds double opt-in functionality to CF7 forms. Note that BOTH mentioned plugins are required for this plugin to work.

**What is double opt in and why use it:**

Double opt-in is the safe way of receiving site visitor's submitted input. 
Used with contact forms or online questionnaires or with user registration, it helps to highly reduce spam submissions.
With GDPR laws, double opt-in is a strongly recommended way of getting user data. Because submitters has to confirm 
their identity from their email address, you can reduce risk of processing personal data without permission. 
Double - means here that user submitting the data has to give consent by checking checkbox option and additionally 
confirm their email-address. Do not forget to set acceptance field in your CF7 form if GDPR is in concern!

**How Double opt in form works:**

When someone fills and submits a form with double opt in functionality the first CF7 email is end back to THEM. 
There is confirmation link in that email wchich submitter has to click on or paste it in their web browser. 
The link has two encrypted parameters: submission serial number and submitter email address. The page with 
"opt-in" slug validates the parameters with submission stored in flamingo plugin and initiates sending of final 
emails to recipient set in "Doube Opt In Forms" settings.
If validation fails or when submission has expired, no emails are sent. If there are no specified parameters in url, 
wisitors of the "opt-in" page are redirected to "404" page.


== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 1.0 =
* Initial plugin release.