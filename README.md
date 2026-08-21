# ACF On-The-Go #
- Author URI: https://www.linkedin.com/in/nadav-cohen-wd/
- Donate link: https://www.paypal.me/NadavC
- Plugin URI: https://github.com/ncamaa/acf-on-the-go/edit/master/README.md
- Contributors: amaa, alkesh7
- Tags: ACF, advanced custom fields, acf front
- Requires at least: 4.8
- Tested up to: 7.1
- Requires PHP: 5.6
- Stable tag: 1.0.3
- License: GPL2+
- License URI: http://www.gnu.org/licenses/gpl-2.0.txt

## Description ##

### ACF On The Go ###

- Edit your ACF text fields from the front-end of your website.
- Save time looking for the field in WP-Admin.
- See immediate results in the front-end.
- Developer & User friendly.

## IMPORTANT ##
- For fields to be editable on the front-end, YOU MUST ADD 'acfgo' to the target ACF field's 'Wrapper Attributes -> Class'
- Right now the plugin only supports non-repeater text fields! 

## Installation ##
Developer:
- Go to the relevant ACF field group.
- Find the field you wish to turn front-end editable.
- On 'Wrapper Attributes' add the class 'acfgo'.
- Save the changes.

User:
- Visit a page that has an editable text field
- Click the small, blue pencil icon near the content you wish to update.
- Enter new content, click 'Update'.
- That's it! The new content is now set and is updated also in the DB.

## Frequently Asked Questions ##

### I have installed and activated the plugin, but I still can't see any changes on the frontend, why is that? ###
Please make sure to put 'acfgo' in the target field's class. You can do so by going to the field's field-group page, then in the field attributes go to 'Wrapper Attributes -> class', type 'acfgo' and click 'update'.

### What kind of ACF fields are supported? ###
Right now the plugin works only for non-repeater text fields. We're working on adding functionality to more kinds of fields. 

## Screenshots ##
1. Text field example
2. In the field attributes go to 'Wrapper Attributes -> class', type 'acfgo' and click 'update'.
3. Insert your text field's code like you do normally, no changes here.
4. In the 'Edit Page', insert any value for your text field.
5. Click the pencil edit icon near your field's frontend content.
6. Review the existing content.
7. Insert new content and click 'Update'.
8. Woohoo! The new content now appears in the frontend and was also saved to the database. 
9. Review the new content on the 'Edit Page'.

## Changelog ##

### 1.0.3 ###
Release date: August 21st, 2026

* Hardening: front-end save response is now inserted into the page with `.text()` instead of `.html()`, removing a redundant HTML-rendering step for values that are always plain text.
* Fix: the plugin's own front-end CSS/JS were pinned to a stale, hardcoded version string, so browsers could keep serving a cached copy across plugin updates; they now version off the plugin's own version number.
* Fix: renamed the `jquery-ui` style handle to `acfg-jquery-ui-dialog` to avoid colliding with identically-named handles from other plugins or themes.
* Tested up to WordPress 7.1.
* Code quality: renamed `includes/acfg-front-loader.php` to `includes/class-acfg-front-loader.php` and removed a redundant, unreachable admin-context check, to align with WordPress Coding Standards.
* Added a `phpcs.xml.dist` ruleset (WordPress-Extra + WordPress-Docs) so coding-standards checks are repeatable for future changes.

### 1.0.2 ###
Release date: August 5th, 2026

* Security: full plugin audit confirmed the AJAX save handler is the only endpoint that accepts input, and it enforces both nonce verification and a per-post capability check.
* Version bump for WordPress.org relisting following the security review.
* Security: added nonce verification and per-post capability checks to the front-end save request.
* Security: escaped all dynamic front-end output to prevent stored XSS.
* Tested up to WordPress 7.0.2
* Fixed a text domain mismatch that prevented translations from loading correctly.
* Removed the manual textdomain loading call in favor of WordPress.org's automatic translation loading.
* Localized the front-end dialog's "Update" and "Close" button labels, which were previously hardcoded in English.
* Fixed a front-end JavaScript error that prevented the save dialog from closing and the success/no-change notice from appearing after a save (the response was parsed twice).
* Added support for Secure Custom Fields, the actively maintained successor to Advanced Custom Fields, as a recognized dependency.
* General code cleanup and WordPress Coding Standards fixes.

### 1.0 ###
Release Date: 31.01.2020

## Upgrade Notice ##

### 1.0.2 ###
Includes important security fixes for the front-end save request. Updating is strongly recommended.
