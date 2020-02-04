=== ACF On-The-Go ===
Author URI: https://www.linkedin.com/in/nadav-cohen-wd/
Donate link: https://www.paypal.me/NadavC
Plugin URI: https://github.com/ncamaa/acf-on-the-go/edit/master/README.md
Contributors: amaa, alkesh7
Tags: ACF, advanced custom fields, acf front
Requires at least: 4.8
Tested up to: 5.3.2
Requires PHP: 5.6
Stable tag: 1.0 beta
License: GPL2+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

== Description ==

ACF On The Go

- Edit your ACF text fields from the front-end of your website.
- Save time looking for the field in WP-Admin.
- See immediate results in the front-end.
- Developer & User friendly.

== IMPORTANT ==
- For fields to be editable on the front-end, YOU MUST ADD 'acfgo' to the target ACF field's 'Wrapper Attributes -> Class'
- Right now the plugin only supports non-repeater text fields! 

== Installation ==
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

== Frequently Asked Questions ==

= I have installed and activated the plugin, but I still can't see any changes on the frontend, why is that? =
Please make sure to put 'acfgo' in the target field's class. You can do so by going to the field's field-group page, then in the field attributes go to 'Wrapper Attributes -> class', type 'acfgo' and click 'update'.

= What kind of ACF fields are supported? =
Right now the plugin works only for non-repeater text fields. We're working on adding functionality to more kinds of fields. 

== Screenshots ==
1. Text field example
2.  In the field attributes go to 'Wrapper Attributes -> class', type 'acfgo' and click 'update'.
3. Insert your text field's code like you do normally, no changes here.
4. In the 'Edit Page', insert any value for your text field.
5. Click the pencil edit icon near your field's frontend content.
6. Review the existing content.
7. Insert new content and click 'Update'.
8 Woohoo! The new content now appears in the frontend and was also saved to the database. 
9. Review the new content on the 'Edit Page'.

== 1.0 ==
Release Date: 31.01.2020

== Changelog ==
Nothing here yet.

== Upgrade Notice ==
Nothing here yet.
