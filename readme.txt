=== Double Opt-in for CF7 ===
Contributors: Elkurczako
Tags: contact form, contact, email, multilingual, accessibility
Requires at least: 5.3
Tested up to: 5.9
Stable tag: 1.0.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a double opt-in functionality to CF7 forms.

== Description ==

Adds double opt-in functionality to CF7 forms submissions. Double opt-in means that form submitters have to confirm their identity by their email account. The plugin is ready for translation.

**Double opt-in dependencies**

This plugin is an add-on to [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) plugin by Takayuki Miyoshi. 
Functionality of this plugin also depend on installing and activating one of the plugins, which saves Contact Form 7 submissions to WordPress database. At the time, compatible plugins are:

* [Flamingo](https://wordpress.org/plugins/flamingo/) by Takayuki Miyoshi
* [Contact Form 7 Database Addon – CFDB7](https://wordpress.org/plugins/contact-form-cfdb7/) by Arshid

Note that BOTH Contact Form 7 and one of the listed above plugins are required for double opt-in functionality to work.

**Help and Docs**

The first and most complete documentation is located on [plugin official page](https://sirta.pl/double-opt-in-for-contact-form-7-documentation/).
If you're a developer, feel free to take a look at [the plugin page on GitHub repository](https://github.com/elkurczako/Double-Opt-in-for-CF7).
You can also seek for help on WordPress support forum.

**Plugin features**

* One click conversion of existing CF7 forms into double opt-in enabled forms.
* URL parameters encryption to make confirmation links safe.
* HTML email templates with mail tags similar to used in CF7 plugin. If you know how to configure CF7 emails, double opt-in will be easy.
* Automatic creation of the "opt-in" page to process confirmed submissions.
* Adding a CSV file attachment to final submission email containing form data.
* Adding file attachment to final confirmation email sent to the submitter.
* Setting expiration time for submissions to be confirmed.
* Ability to manually confirm forms. That can be useful if your submitter enters an invalid email address, and you are sure you want to send their submission anyway.

**Additional enhancements**

Apart from its main feature, Double Opt-in for CF7 adds some functions you may find nice and useful:

* Custom, accessible file upload inputs for your forms which look better, can be CSS styled and allow easy selecting and deselecting files to upload. This works for every CF7 form, not only double opt-in enabled.
* Custom, more specific validation errors for radio buttons and checkboxes for improved accessibility.
* Option to change CSV separator when exporting submissions from Flamingo plugin. It also adds BOM to CSV exports, allowing them to be properly opened in MS Excel. 
* Additional column on Flamingo Inbound Messages screen indicating if submission is complete or still waiting for being confirmed by sender.

**What is double opt-in and why use it:**

Double opt-in is the safe way of receiving site visitor's submitted input. Used with contact forms or online questionnaires or with user registration, it helps to reduce spam submissions. With GDPR laws, double opt-in is a strongly recommended way of getting user data. Because submitters have to confirm their identity from their email address, you can reduce risk of processing personal data without permission. Double – means here that user submitting the data has to give consent by checking checkbox option and additionally confirm their email-address.
Do not forget to set acceptance field in your CF7 form if GDPR is in concern!

**How Double opt-in form works:**

When someone fills and submits a form with double opt-in functionality, the first CF7 email goes back to THEM. There is a confirmation link in that email, which the submitter has to click on or paste in their web browser. The link has two encrypted parameters: submission serial number and submitter email address. The page with "opt-in" slug validates the parameters with submission stored in WordPress database and initiates sending of final emails to recipient set in "All Opt-In Forms" settings. If validation fails or when submission has expired, no emails are sent. If there are no specified parameters in the URL, visitors of the "opt-in" page are redirected to "404" page.

**Plugin translation**

Double Opt-In can be easily translated. It is released with the translation to Polish language. 

== Frequently Asked Questions ==

= Can I use Double Opt-in for CF7 with another form plugins? =

No. This plugin works only with Contact Form 7 which is one of the best and flexible plugins.

= Do I have to install yet another plugin for storing CF7 submissions? =

For the time, yes. Submissions must be stored somehow by the time of confirmation. I'm working on a built-in solution with automatic submission data removing when form expires, but it'll take some time.

= Can I be now certain that the person submitting my form is who he/she claims to be? =

No. You can be only certain that this person has access to emails in submitted email address account. This usually means being an account owner and is considered as secure contact confirmation. Just remember that it doesn't really mean that the rest of information submitted with your form has to be true/legal or whatever.

= Is Double Opt-In plugin accessible? =

Yes. It's as accessible as the core WordPress admin area. The optional custom file upload input is also built with accessibility in mind. All the options and settings can be easily used with screen readers, and the color contrast meets Web Accessibility Initiative standards. If you find some elements of plugin UI that need improvement, please report them on support forum.

= Is set CSV delimiter working with CFDB7 plugin exports? =

No. At least now, CFDB7 doesn't offer a hook to modify CSV output. There is a solution in their support page, but it requires direct plugin file modification.  

== Screenshots ==

1. screenshot-1.png
1. screenshot-2.png
1. screenshot-3.png

== Installation ==

1. Upload the `cf7-optin` folder to the `/wp-content/plugins/` directory in your WordPress installation.
1. Install and activate the free [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) plugin by Takayuki Miyoshi from WordPress repository.
1. Activate the Double Opt-in for CF7 plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

You will find new submenus: **All Opt-In Forms** and **Double Opt-In Settings** in **Contact** menu in your WordPress admin screen.
Basic help and instructions can be found on **Double Opt-In Settings** admin page.

For detailed plugin documentation, please visit [plugin official page](https://sirta.pl/double-opt-in-for-contact-form-7-documentation/). 

== Changelog ==

= 1.0 =
* Initial plugin release.

= 1.0.1 =
* Fixed unnecessary escaping which caused wrong format of HTML email body.
* Fixed a bug with wrong CFDB7 being accepted from confirmation link.

= 1.0.2 =
* Fixed non required chechbox validation.
* Added JS validation handling for CF7 Conditional Fields.

== Upgrade Notice ==

= 1.0.1 =
This versions fixes HTML emails and a major problem when CFDB7 plugin is used.
