G-Lock Double Opt-in Manager
============================

[![Analytics](https://ga-beacon.appspot.com/UA-24619548-7/G-Lock-Opt-in-Manager--Wordpress-Plugin/readme)](https://github.com/igrigorik/ga-beacon)

Contributors: Alex Ladyga, G-Lock Software
Donate Link: http://www.glockeasymail.com/wordpress-email-newsletter-plugin-for-double-opt-in-subscription/
Tags: Double Opt-in Manager, wp mailing list management, newsletter plugin for WP, create newsletter in wordpress, WP Newsletter Plugin, sidebar widget, plugin wordpress mail newsletter, email marketing software
Requires at least: 2.5
Tested up to: 3.0
Stable tag: 2.4.9

This mailing list management plugin allows the visitors of your blog subscribe to your mailing list using a double opt-in method.

Description
-----------

**IMPORTANT: PHP 5 required!**

This mailing list management plugin allows the visitors of your blog subscribe to your mailing list using a double opt-in method. The plugin sends an email with the subscription confirmation link to the user and if the user confirms the subscription, it sends a welcome email to each new subscriber. The details of new subscribers are saved to the internal WordPress database. You can <a href="http://www.glockeasymail.com/wordpress-email-newsletter-plugin-for-double-opt-in-subscription/" title="create double opt-in mailing list">manage the mailing list inside WordPress</a> and export the list for the further use in your <a href="http://www.glockeasymail.com/" title="bulk email marketing software">email marketing software</a>. You can also use the plugin to convert your blog RSS feed into an email newsletter and send it out to your subscribers. You can design the style and content of your email newsletter yourself and you can schedule your email broadcast weekly, monthly or as soon as you add a certain number of new posts to your blog. Please see the <a href="http://www.glockeasymail.com/wordpress-email-newsletter-plugin-for-double-opt-in-subscription/history/" title="Double Opt-In sidebar widget version history">VERSION HISTORY</a> for what's new and current bugfixes.

= WP Double Opt-In Subscription Plugin Features =

* users can subscribe to your mailing list from your blog;
* users can update their subscription details and unsubscribe;
* sends a subscription confirmation email to the user;
* sends a welcome email to each new subscriber (optional);
* sends an unsubscribe notification to the user (optional);
* sends a subscribe/unsubscribe event notification to the blog admin (optional);
* saves the subscriber's details to your internal WordPress database;
* can show the subscription report on your blog dashboard either in the activity box (Right Now) or in the separate widget (optional);
* provides you with the unsubscribe link that you can copy and paste into your newsletter;
* can automatically delete users who did not confirm their subscription within 7 days since the subscription date (optional);
* can work with either your default mail settings (sendmail) or using a custom SMTP server;
* you can customize the signup form as you like (add fields, remove fields, re-order fields);
* you can merge custom fields such as blog name, blog URL, subscriber's name, subscription time and others into your confirmation and welcome messages as well as into your broadcast email newsletter. Plus, you can <a href="http://www.glockeasymail.com/ultimate-email-marketing-guide/personalizing-the-email-for-each-recipient/" title="merge personalized fields into message">merge these fields into your mass emails</a> if you connect to your WP subscribers from our <a href="http://www.glockeasymail.com/easymail/free-personal-business/" title="purchase bulk email marketing software">bulk email marketing software</a>.
* you can build own in-house permission based email list using a double opt-in subscription method;
* you can manage your opt-in mailing list inside WordPress (unsubscribe or delete users);
* you can export your mailing list from WordPress for the use in your email marketing program;
* you can connect to your subscribers stored in WordPress directly from G-Lock EasyMail address book and send an email to them without export-import;
* you can convert your blog RSS feed into an email newsletter and send it to your subscribers;
* you can schedule your email broadcast weekly, monthly or as you add new posts;
* you have a full control under your email newsletter design and content.

= Email Marketing Software related features =

Complatible with G-Lock EasyMail 6 [bulk email software](http://www.glockeasymail.com/order/ "order bulk email software") :

* [bulk email software download](http://www.glockeasymail.com/downloads/ "bulk email software download")

== Installation ==

1. Extract the files from the g-lock-double-opt-in-manager.zip archive
2. Upload the complete plugin folder /g-lock-double-opt-in-manager/, contained in this zip file, to your WP plugin directory!
   If there is no /g-lock-double-opt-in-manager/ root folder in this zip file, please create one in your WordPress plugins/ folder.
3. Go to your WordPress Admin panel -> Plugins and activate the G-Lock Double opt-in plugin
4. Go to Design -> Widgets and add the new G-Lock Double Opt-In Manager plugin to the sidebar
5. Click "Save Changes"

To put the signup form at any place on your post or page, use this shortcode:

[gsom-optin]


== Configuration ==

The plugin is pre-configured by default to collect the subscribers' email addresses and names and send confirmation and welcome emails. You can modify the default settings as you like.
 
= 1. Go to WP-Admin -> Settings -> G-Lock Double Opt-In Manager =

= 2. Email Settings: =

* From Name: - enter the name you want to send notification emails to new subscribers from. It may be either your contact name, or company name.
* From Email: - enter the email address you want to send notification emails to new subscribers from
* Send Welcome Message � check this box if you want to send a welcome message to each new subscriber
* Send Unsubscribe Notification - check this box if you want to send a notification that the user has successfully unsubscribed from your list

= 3. Submission Form: =

* Form Title: - type a title for your signup form
* Form Header: - type a text that will appear above the signup form (for example, ask the user to fill in the form to subscribe)
* Form Footer: - type a text that will appear below the signup form (for example, an email privacy statement)
* Form Fields: - fields the user must fill in. By default there are "First Name" and "Email". You can add more fields by clicking on "Add Field" button. You can re-order fields as you like. To move a field, drag it up or down and drop it.
* "Subscribe" button - button the user must click to subscribe. You can change the button label if you like.

= 4. Confirmation Email: = (will contain a subscription confirmation link and will be sent to the user after he clicks "Subscribe")

* Subject: - confirmation email subject (usually it clearly asks the user to confirm his subscription)
* Message: - confirmation email text including the subscription confirmation link
* Available variables - the variables you can use in your confirmation email, welcome message and on action pages. They will be replaced with the real data in the emails. The available variables are:

= 5. Welcome Email: = (will be sent to the user after he confirms his subscription)

* Subject: - welcome email subject (usually it thanks the user for his subscription)
* Message: - welcome email text. It's highly recommended that you welcome each new subscriber with a well written email message. 

= 6. Unsubscribe Email: = (will be sent to the user after he/she unsubscribes)

* Subject: - email subject (for example, Removal Request Confirmation)
* Message: - email text

= 7. Action Pages: = (are displayed on your blog after the user fills in the form)

* Wrong email address format: - asks the user to enter a valid email address;
* Confirmation required: - asks the user to check his mailbox and confirm the subscription;
* Confirmation successful: - thanks the user for confirming his subscription;
* Email address is already subscribed and confirmed: - tells the user that his email address is already subscribed and confirmed;
* Email address is already subscribed but not confirmed: - tells the user that his email address is already subscribed but not confirmed;
* Unsubscription successful: - tells the user that he was successfully unsubscribed from the mailing list.

After you modified the settings and made sure everything is OK, click on Update Options button to apply the changes.

== Frequently Asked Questions ==

= WordPress Double Opt-In List Management Plugin Support =

http://www.justlan.com/forum/viewtopic.php?f=24 


== Screenshots ==
1. Subscription Form Settings
2. Blog Broadcast General Settings
2. Blog Broadcast Template

== Support ==
If you find any bugs or have ideas for improvements or enhancements to functionality, please tell us. If possible, we will take your hints in consideration for future releases. If you have any questions or problems and want to get help from our support team or other users, please, visit G-Lock Software Community Forums: http://www.justlan.com/forum/viewforum.php?f=24 We will be proud to help you. We will be happy if you could suggest WordPress Double Opt-In List Management Plugin from G-Lock Software to other people who could be interested in it, e. g. your friends, colleagues or maybe even your website visitors.

Read how you can manage your mailing list in WordPress here:

http://www.glockeasymail.com/wordpress-email-newsletter-plugin-for-double-opt-in-subscription/
