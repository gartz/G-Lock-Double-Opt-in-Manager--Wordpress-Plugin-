<?php
/*
Plugin Name: G-Lock Double Opt-in Manager
Plugin URI: http://www.glockeasymail.com/wordpress-email-newsletter-plugin-for-double-opt-in-subscription/
Description: This mailing list management plugin allows the visitors of your blog subscribe to your mailing list using a double opt-in method. The plugin sends an email with the subscription confirmation link to the user and if the user confirms the subscription, it sends a welcome email to each new subscriber. The details of new subscribers are saved to the internal WordPress database. You can <a target="_blank" href="http://www.glockeasymail.com/wordpress-email-newsletter-plugin-for-double-opt-in-subscription/">manage the mailing list inside WordPress</a> and export the list for the further use in your <a target="_blank" href="http://www.glockeasymail.com/downloads/?ref=1878">email newsletter sending program</a>. G-Lock Double Opt-In Manager helps you reach the largest possible audience by automatically converting your blog RSS feed into an email newsletter. You have a full control under your newsletter's design and content and you can schedule your blog newsletters to be sent weekly, daily or as you publish new posts. Please see the <a target="_blank" href="http://www.glockeasymail.com/wordpress-email-newsletter-plugin-for-double-opt-in-subscription/history/?ref=1878">VERSION HISTORY</a> for what's new and current bugfixes.
Version: 2.4.9
Author: Alex Ladyga - G-Lock Software
Author URI: http://www.glocksoft.com
*/
/*  Copyright 2008  Alex Ladyga (email : alexladyga@glocksoft.com)
    Copyright 2008  G-Lock Software

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once('json.php');
//date_default_timezone_set('Europe/Minsk');

global $gsom_db_version,
       $gsom_def_form,
       $gsom_form,
       $gsom_form_vars,
       $gsom_eml,
       $gsom_frname,
       $gsom_plugindir,
       $gsom_table_subs,
       $wpdb,
       $gsom_plugin_name;
       
$gsom_plugin_name = 'G-lock Soft Opt-in Manger';       

$gsom_db_version = '2.4.9';
define('GSOM_VERSION',$gsom_db_version);
$gsom_def_form = '';           
$gsom_form = '';

$gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
$gsom_table_log = $wpdb->prefix . "gsom_log";

define('GSOM_SS_SUBSCRIBED',0);
define('GSOM_SS_CONFIRMED',1);
define('GSOM_SS_UNSUBSCRIBED',2);

define('GSOM_VER_EQUAL',0);
define('GSOM_VER_LOWER',-1);
define('GSOM_VER_GREATER',1);
define('GSOM_VER_ERROR',2);


if (!defined('GSOM_ADMIN_READ')) {
	define('GSOM_ADMIN_READ', 'edit_posts');
}	

if (!defined('GSOM_ADMIN_READ_WRITE')) {
	define('GSOM_ADMIN_READ_WRITE', 'publish_pages');
}
	

function gsom_addtrslash($url) {
	if (substr($url,strlen($url)-1,1) != '/') {
    	$url .= '/';
    }		
	return $url;
}

global $gsom_xd;

$gsom_xd = 'YToyOTp7aTowO3M6Mjc6ImVtYWlsIGJyb2FkY2FzdGluZyBzb2Z0d2FyZSI7aToxO3M6MjA6ImVuZXdzbGV0dGVyIHNvZnR3YXJlIjtpOjI7czoyMToiZGlyZWN0IGVtYWlsIHNvZnR3YXJlIjtpOjM7czoyMjoiaG93IHRvIGJyb2FkY2FzdCBlbWFpbCI7aTo0O3M6MjI6InNlbmQgZW1haWwgbmV3c2xldHRlcnMiO2k6NTtzOjIyOiJlbWFpbCBzZW5kaW5nIHNvZnR3YXJlIjtpOjY7czoyNjoiZW1haWwgbmV3c2xldHRlcnMgc29mdHdhcmUiO2k6NztzOjI2OiJtYXNzIGVtYWlsIHNlbmRlciBzb2Z0d2FyZSI7aTo4O3M6MjE6Im1haWxpbmcgbGlzdCBzb2Z0d2FyZSI7aTo5O3M6MjQ6ImVtYWlsIG1hcmtldGluZyBzb2Z0d2FyZSI7aToxMDtzOjMwOiJiZXN0IGVtYWlsIG5ld3NsZXR0ZXIgc29mdHdhcmUiO2k6MTE7czoyODoiYmVzdCBlbWFpbCBjYW1wYWlnbiBzb2Z0d2FyZSI7aToxMjtzOjM0OiJob3cgdG8gc2VuZCBtYXNzIGVtYWlsIG5ld3NsZXR0ZXJzIjtpOjEzO3M6MjA6Im1haWwgc2VuZGVyIHNvZnR3YXJlIjtpOjE0O3M6Mjk6Im1hc3MgZW1haWwgbWFya2V0aW5nIHNvZnR3YXJlIjtpOjE1O3M6MjQ6ImVtYWlsIG1hcmtldGluZyBzb2x1dGlvbiI7aToxNjtzOjI5OiJuZXdzbGV0dGVyIG1hcmtldGluZyBzb2Z0d2FyZSI7aToxNztzOjI5OiJodG1sIGVtYWlsIG1hcmtldGluZyBzb2Z0d2FyZSI7aToxODtzOjI3OiJuZXdzbGV0dGVyIG1haWxpbmcgc29mdHdhcmUiO2k6MTk7czoyMDoic2VuZCBlbWFpbCBtYXJrZXRpbmciO2k6MjA7czoxOToiYnVsayBlbWFpbCBzb2Z0d2FyZSI7aToyMTtzOjIzOiJlbWFpbCBjYW1wYWlnbiBzb2Z0d2FyZSI7aToyMjtzOjIxOiJlbWFpbCBtYXJrZXRpbmcgdG9vbHMiO2k6MjM7czozMToiZGlyZWN0IGVtYWlsIG1hcmtldGluZyBzb2Z0d2FyZSI7aToyNDtzOjI5OiJtYXNzIGVtYWlsIG1hcmtldGluZyBzb2Z0d2FyZSI7aToyNTtzOjIxOiJkaXJlY3QgZW1haWwgc29mdHdhcmUiO2k6MjY7czoyMDoibWFzcyBlbWFpbCBtYXJrZXRpbmciO2k6Mjc7czoyNToiZW1haWwgbmV3c2xldHRlciBzb2Z0d2FyZSI7aToyODtzOjI0OiJlbWFpbCBtYXJrZXRpbmcgc29mdHdhcmUiO30=';



$gsom_form_vars = array();
$gsom_form_vars['blog_name'] = html_entity_decode(get_option('blogname'), ENT_QUOTES, 'UTF-8');

$gsom_form_vars['blog_url'] = gsom_addtrslash(get_bloginfo('url'));
$gsom_form_vars['wp_url'] = gsom_addtrslash(get_bloginfo('wpurl'));
$gsom_eml = get_option('gsom_email_from');
$gsom_frname = get_option('gsom_name_from');
$gsom_form_vars['from_email'] = (trim($gsom_eml) != '') ? $gsom_eml : get_option('admin_email');
$gsom_form_vars['from_name'] = (trim($gsom_frname) != '') ? $gsom_frname : $gsom_form_vars['blog_name'];

$gsom_form_vars['gsom_unsubscribe_reason_form'] = '
		<label for="u_reason">Please tell us, why do you wish to unsubscribe?</label>
		<textarea name="u_reason" cols="60" rows="7"></textarea>
';

define('gsom_plugin_url', $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)));

function gsom_redirect($link){
    wp_redirect($link);      
    echo " ";
    exit();
}

function gsom_activation_error() {	
	
	if ((substr(phpversion(),0,1) < 5)) {		
		echo '<div class="error"><p><b>Error:</b> G-Lock Double Opt-in Manager >= 2.3 requires PHP version > 5 and cannot be activated. You have PHP '.phpversion().' installed.</p></div>';				
		deactivate_plugins('g-lock-double-opt-in-manager/glsft-optin.php');
		unset($_GET['activate']); // to disable "Plugin activated" message
	}
}

function gsom_install() {
    global $wpdb;
    global $gsom_db_version;  
	global $gsom_form_vars; 	
    
    $gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
    
    
    // filling variables with defaults ----------------------------------------------------
    
    $blogname = $gsom_form_vars['blog_name'];
        $gsom_email_confirmation_msg = '
Hello $gsom_fname_field,

We received your request for information from $blog_name

Before we begin sending you the information you requested, we want to be certain we have your permission.

Click the link below to give us permission to send you information. It\'s fast and easy!  If you cannot click the full URL below, please copy the URL and paste it into your web browser.

CONFIRM BY CLICKING THIS URL:

$confirmation_link

If you do not want to subscribe, simply ignore this message.

Best Regards,
$blog_name
$blog_url


Request generated by:
IP: $subscriber_ip
Date: $subscription_time';     

        $gsom_email_welcome_msg = 'Hi $gsom_fname_field,

Thank you for your subscription. You are now signed up to my newsletter, and will get great tips, tricks, and strategies a few times a month.  Hope you get some value out of it!  It\'s where ALL of my best posts go!

We will keep your personal information private and secure. We will not share your email address with any third parties. 

To ensure you receive future subscribers-only great tips, tricks, and offers, please add $from_email to your e-mail address book or safe senders list.

Sincerely,
$from_name
$blog_name
$blog_url


--
You are receiving this email because on $subscription_time at $blog_url you subscribed to receive our e-newsletters.
                         
You can modify your subscription via the link below:
$manage_subscription_link';

$gsom_email_unsubscribe_subj = 'Removal Request Confirmation';
        $gsom_email_unsubscribe_msg = 'Dear $gsom_fname_field,

This email is being sent to notify you that we have received a request to remove the following record from our mailing list:

Name: $gsom_fname_field
Email: $gsom_email_field

This record was removed from our online database and will also be removed from any backup files shortly.

If you do receive further unwanted email from our system, please let us know using the contact information below and we\'d be happy to address the issue immediately.

Sincerely,
$from_name
$from_email
$blog_url';

$gsom_send_welcome_message = '1';
$gsom_send_unsubscibed_message = '1';

$gsom_msg_confirmation_required = '<h2>Please Check Your Email to Confirm Your Subscription!</h2>
<h3 style="color:#FF0000;">We sent you an email with a link to confirm your subscription. If you do not receive it within a reasonable amount of time, please check your spam filters to ensure you are allowing emails from $from_email.</h3>
<p>To ensure that our email reaches your Inbox, and is not deleted or moved to your Junk Mail folder, add our email address $from_email to your safe sender list by following the directions below.</p>
<p>If your email provider is not listed here, contact your email host and ask them how you can whitelist email addresses so you will get our special announcement and tip emails.</p>
<p style="margin-top: 10px;">
<h3>Yahoo Mail</h3>
</p>
<p>To create a filter that sends our emails to your Inbox:</p>
<ol>
<li>Login to Yahoo Mail.</li>
<li>Click on "Mail Options" (on the right hand side of the screen).</li>
<li>Click on "Filters" (on left hand side of the screen).</li>
<li>Click "Add" button.</li>
<li>Assign a name for this filter: "Newsletter Name".</li>
<li>Underneath the heading "if all of the following rules are true�" go to the top row labeled 
"FROM header." Next to this you will see a drop down menu. Make sure to select "contains", 
then simply type in our email address $from_email into the box provided.</li>
<li>At the bottom, where it says "Move the message to:", select "Inbox" from the drop-down menu.</li>
<li>Click the "Add Filter" button at the bottom to add this filter.</li>
</ol>
<p style="margin-top: 10px;">
<h3>MSN Hotmail</h3>
</p>
<p>To add our email address to your "Safe List":</p>
<ol>
<li>Login to your Hotmail or MSN Mail account.</li>
<li>Click on "Options."</li>
<li>Click "Junk Mail Protection".</li>
<li>Click on "Safe List."</li>
<li>In the box provided, type in our domain.</li>
<li>Click the "Add" button.</li>
<li>When you see the address you entered in the Safe List box, click on the "OK" button.</li>
</ol>
<p>To add our email address to your "Contacts List":</p>
<ol>
<li>Login to your Hotmail of MSN Mail account.</li>
<li>Click on "Contacts" folder.</li>
<li>In the new screen which opens, click "New" (upper left corner).</li>
<li>Assign a Quickname in the left hand box.</li>
<li>Enter our email address $from_email into the "Online Addresses" area.</li>
</ol>
<p style="margin-top: 10px;">
<h3>AOL Mail</h3>
</p>
<p>To add our email address to your Address Book or Custom Sender List:</p>
<p>AOL 9.0:</p>
<ol>
<li>Click the "Spam Controls" link (lower right area of your inbox).</li>
<li>When the "Mail &amp; Spam Controls" box appears, click "Custom sender list".</li>
<li>Choose "allow email from" option</li>
<li>Add the domain/email address you would like to receive mail from.</li>
<li>Click "Add", then click "Save".</li>
</ol>
<p style="margin-top: 10px;">AOL 7 &amp 8:</p>
<ol>
<li>Go to Keyword Mail Controls.</li>
<li>Select the screen name you are using to receive email.</li>
<li>Click "Customize Mail Controls For This Screen Name."</li>
<li>Allow/Included</li>
<ol style="padding-left: 18px;">
<li style="list-style-type: lower-alpha;">For AOL version 7.0: In the section for 
"exclusion and inclusion parameters", include the following domain: * @mysite.com</li>
<li style="list-style-type: lower-alpha;">For AOL version 8.0: Select "Allow 
email from all AOL members, email addresses and domains."</li>
</ol>
<li>Click on "Next" until the "Save" button shows up at the bottom.</li>
<li>Click "Save."</li>
</ol>
<p style="margin-top: 10px;">
<h3>Outlook 2003 Mail</h3>
</p>
<p>To make sure you can see emails as they were intended to be seen (including 
images), add our email address $from_email to your address book and safe sender list.</p>
<p>To add our email address to your address book:</p>
<ol>
<li>Right click on the email subject line.</li>
<li>Choose "Add Sender To Address Book".</li>
</ol>
<p>To add our domain to your safe sender list:</p>
<ol>
<li>Right click on a non-displaying image in an HTML email.</li>
<li>Choose "Add the domain to the safe sender list" option.</li>
</ol>';

        $gsom_msg_confirmation_succeed = '<h2>Thank You For Confirming Your Subscription To $blog_name!</h2>
        
<h3>A welcome e-mail is already on its way to you!</h3>

To make sure you get emails from $from_email, follow these simple guidelines:

Open your email program or log-in to your web email service.

<strong>Create a filter that will place our emails in your Inbox:</strong>

 <strong>From:</strong> field will always be $from_name
 <strong>Email address:</strong> $from_email

<strong>If our email is delivered to your Spam or Junk folder:</strong>

Click on the box in front of our email to insert a checkmark
Click the "This is not spam" button 

<strong>Add our email address to your Address Book or Friends List:</strong>

Open your Address Book or Friends List
Add our contact details as listed above
Click "Add" or "Save"';
        add_option('gsom_msg_confirmation_succeed',$gsom_msg_confirmation_succeed); //confirmation successful.
        
$gsom_msg_unsubscribe_succeed = '
The following record was removed from our online database and will also be removed from any backup files shortly.

Name: $gsom_fname_field
Email: $gsom_email_field

If you do receive further unwanted email from our system, please let us know using the contact information below and we\'d be happy to address the issue immediately.

$from_name
$from_email
$blog_url';

$gsom_msg_bad_email_address = '<h2>Please, enter a valid email address.</h2>';

$gsom_msg_email_subscribed_and_confirmed = 'This email is already subscribed and verified. If you are not receiving our newsletter, please check your spam filter settings as the email may have been incorrectly flagged as spam and moved to junk mail folder by your email client.

To make sure you get emails from $from_email, follow these simple guidelines:

Open your email program or log-in to your web email service.

<strong>Create a filter that will place our emails in your Inbox:</strong>

 <strong>From:</strong> field will always be $from_name
 <strong>Email address:</strong> $from_email

<strong>If our email is delivered to your Spam or Junk folder:</strong>

Click on the box in front of our email to insert a checkmark
Click the "This is not spam" button 

<strong>Add our email address to your Address Book or Friends List:</strong>

Open your Address Book or Friends List
Add our contact details as listed above
Click "Add" or "Save"';

$gsom_msg_email_subscribed_but_not_confirmed = 'This email address is already in our mailing list, however it has not been confirmed. If you would like your verification email resent, please <a href="$resend_confirmation_link">click here</a>.

To make sure you get emails from $from_email, follow these simple guidelines:

Open your email program or log-in to your web email service.

<strong>Create a filter that will place our emails in your Inbox:</strong>

 <strong>From:</strong> field will always be $from_name
 <strong>Email address:</strong> $from_email

<strong>If our email is delivered to your Spam or Junk folder:</strong>

Click on the box in front of our email to insert a checkmark
Click the "This is not spam" button 

<strong>Add our email address to your Address Book or Friends List:</strong>

Open your Address Book or Friends List
Add our contact details as listed above
Click "Add" or "Save"';

$gsom_email_welcome_subj = 'Thank you for confirming your subscription at $blog_url';
$gsom_email_confirmation_subj = 'Please, confirm your request for information from $blog_url';


$gsom_email_address_changed_subj = '$blog_url Subscription Email Address Updated';

$gsom_email_address_changed_msg = '
Thank you! Your email address was changed to $gsom_email_field. 

You can modify your subscription via the link below:
$manage_subscription_link';

$gsom_msg_change_subscription = '
You can change your subscription email <b>$decoded_email</b>
<p>
	<form style="text-align: left;" method="POST" action="$blog_url">
		<input type="hidden" name="u" value="$ucode">
		<label for="gsom-chsub-newemail">New Email:</label><input type="text" name="gsom-chsub-newemail" />
		<input type="submit" value="Change my email" name="gsom-chsub" />
	</form>
</p>
<p>
You can unsubscribe from our mailing list:
	<form style="text-align: left;" action="$blog_url" method="post">
		<input type="hidden" name="u" value="$ucode">
		$gsom_unsubscribe_reason_form
		<p style="margin-top:5px;">
		<input type="submit" value="Unsubscribe" name="gsom-unsubscribe" /></p>
	</form>
</p>';


$gsom_sform_header = '<p>Fill out the form below to signup to our blog newsletter and we\'ll drop you a line when new articles come up.</p>';
$gsom_sform_footer = '<p style="font-size:x-small; line-height:1.5em;">Our strict privacy policy keeps your email address 100% safe &amp; secure.</p>';

$gsom_predefined_form = '[{"label": "First Name:", "name": "gsom_fname_field", "value": "", "type": "text"}, {"label": "Last Name:", "name": "Last_Name1", "value": "", "type": "text"}, {"label": "Email:", "name": "gsom_email_field", "value": "", "type": "text"}, {"label": "", "name": "gsomsubscribe", "value": "Subscribe", "type": "submit"}]';


$gsom_email_subscribtion_event_subj = 'A contact has joined your mailing list at $blog_name';
$gsom_email_subscribtion_event_msg = '
A contact has joined your mailing list. The contact\'s details are listed below:

$gsom_form_data
--
$blog_urlwp-admin/ -> Settings -> G-Lock Double Opt-In Manager';

$gsom_email_unsubscribtion_event_subj = 'A contact has unsubscribed from your mailing list at $blog_name';

$gsom_email_unsubscribtion_event_msg = '
A contact has unsubscribed from your mailing list. The contact\'s details are listed below:

$gsom_form_data

Reason: 
$u_reason

--
$blog_urlwp-admin/ -> Settings -> G-Lock Double Opt-In Manager';

$gsom_send_event_notif = '0';

$gsom_cron_clean_unsubscribed_every_week = '1';
$gsom_mail_delivery_option = 'sendmail';

    $gsom_smtp_hostname = '';
    $gsom_smtp_username = '';
    $gsom_smtp_password = '';
    $gsom_smtp_port = '';
    
$gsom_dashboard_stats = 'abox';

$gsom_smtp_secure_conn = 'off';

$gsom_bcst_email_subj = '$last_rss_item_date - $last_rss_item_title';
$gsom_def_bcst_email_subj = $gsom_bcst_email_subj;

$gsom_bcst_email_msg = 'Hi $gsom_fname_field,<br><br>
Here\'s what\'s new at the <a target="" title="" href="$blog_url">$blog_url</a> over the past few days. Your comments and feedback are always welcome.<br>
<ol>$rss_itemblock
<li><a href="$rss_item_link">$rss_item_title</a> - $rss_item_date<br>
$rss_item_description<br><br>
</li>
/$rss_itemblock</ol>Sincerely,<br>
$from_name<br>
<a href="$blog_url">$blog_name</a><br>
<br>
<br>
--<br>
You are receiving this email because on $subscription_time at <a href="$blog_url">$blog_url</a> you subscribed to receive our e-newsletters.<br>
<br>
You can modify your subscription via the link below:<br>
<a href="$manage_subscription_link">$manage_subscription_link</a><br>';

$gsom_def_bcst_email_msg = $gsom_bcst_email_msg;
$gsom_bcst_email_msg_plain = 'Welcome to the $blog_name newsletter. Here\'s what\'s new at the $blog_url over the past few days. Your comments and feedback are always welcome.

$rss_channel_title
$rss_channel_link

$rss_channel_description
$rss_itemblock
   $rss_item_number. $rss_item_link - $rss_item_date
      $rss_item_title. $rss_item_description
/$rss_itemblock

Sincerely,
$from_name
$blog_name
$blog_url


--
You are receiving this email because on $subscription_time at $blog_url you subscribed to receive our e-newsletters.
                         
You can modify your subscription via the link below:
$manage_subscription_link';
$gsom_def_bcst_email_msg_plain = $gsom_bcst_email_msg_plain;
$gsom_bcst_number_of_posts = '5';
$gsom_bcst_day_number = '15';
$gsom_bcst_when = 'thu';
$gsom_bcst_send = 'manually';
$gsom_bcst_dom_post_limit = '10';

$gsom_rss_excerpt_length = 350;

$gsom_write_debug_log = '0';

$gsom_feed_limit = 10;

$gsom_filter_images = '1';


    // if first time activation ---------------------------------------------------------
    if($wpdb->get_var("show tables like '".$gsom_table_subs."'") != $gsom_table_subs) {    
        
        // Table did not exist; create new
        $sql = "CREATE TABLE ".$gsom_table_subs." (
                  `intId` int(10) unsigned NOT NULL auto_increment,
                  `dtTime` datetime NOT NULL,
                  `varIP` varchar(50) NOT NULL,
                  `varEmail` varchar(255) NOT NULL,
                  `textCustomFields` text NOT NULL,
                  `intStatus` tinyint(3) unsigned NOT NULL default '0',
				  `varUCode` VARCHAR(255) NOT NULL,
				  `gsom_fname_field` TEXT NOT NULL,
				  `Last_Name1` TEXT NOT NULL,
                  PRIMARY KEY  (`intId`)
                ) CHARSET=utf8";
        $result = $wpdb->query($sql);
        //require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        //dbDelta($sql);

        add_option("gsom_db_version", $gsom_db_version);

        // Initialise options with default values
        
        add_option('gsom_form_title', 'Subscription');
        
        
        add_option('gsom_email_from', get_option('admin_email') );
        add_option('gsom_name_from', $blogname );
        add_option('gsom_email_confirmation_subj',$gsom_email_confirmation_subj);        
        add_option('gsom_email_confirmation_msg',$gsom_email_confirmation_msg);                         
        add_option('gsom_email_welcome_subj', $gsom_email_welcome_subj);
        add_option('gsom_email_welcome_msg', $gsom_email_welcome_msg);
        add_option('gsom_email_unsubscribe_subj',$gsom_email_unsubscribe_subj);
        add_option('gsom_email_unsubscribe_msg',$gsom_email_unsubscribe_msg);
        add_option('gsom_send_welcome_message', $gsom_send_welcome_message);
        add_option('gsom_send_unsubscibed_message', $gsom_send_unsubscibed_message);
        add_option('gsom_msg_confirmation_required',$gsom_msg_confirmation_required); //confirmation-required
        add_option('gsom_msg_unsubscribe_succeed',$gsom_msg_unsubscribe_succeed); //unsubscribe successful.
        add_option('gsom_msg_bad_email_address',$gsom_msg_bad_email_address); //Bad email address
        add_option('gsom_msg_email_subscribed_and_confirmed',$gsom_msg_email_subscribed_and_confirmed); //Email address already subscribed and confirmed.                
        add_option('gsom_msg_email_subscribed_but_not_confirmed',$gsom_msg_email_subscribed_but_not_confirmed); //Email address already subscribed but not confirmed. Click here to resend confirmation email.
        add_option('gsom_msg_change_subscription',$gsom_msg_change_subscription); //Bad email address                
        add_option('gsom_sform_header', $gsom_sform_header);
        add_option('gsom_sform_footer', $gsom_sform_footer);        
        add_option('gsom_def_form',$gsom_predefined_form);        
        add_option('gsom_form',$gsom_predefined_form);
        
        add_option('gsom_email_address_changed_msg',$gsom_email_address_changed_msg);        
        add_option('gsom_email_address_changed_subj',$gsom_email_address_changed_subj);

        add_option('gsom_email_subscribtion_event_subj',$gsom_email_subscribtion_event_subj);
        add_option('gsom_email_subscribtion_event_msg',$gsom_email_subscribtion_event_msg);
        
        add_option('gsom_email_unsubscribtion_event_subj',$gsom_email_unsubscribtion_event_subj);
        add_option('gsom_email_unsubscribtion_event_msg',$gsom_email_unsubscribtion_event_msg);         
        
        add_option('gsom_send_event_notif',$gsom_send_event_notif);        
        add_option('gsom_mail_delivery_option',$gsom_mail_delivery_option);

        add_option('gsom_smtp_hostname',$gsom_smtp_hostname);
        add_option('gsom_smtp_username',$gsom_smtp_username);
        add_option('gsom_smtp_password',$gsom_smtp_password);
        add_option('gsom_smtp_port',$gsom_smtp_port);    
        
        add_option('gsom_dashboard_stats',$gsom_dashboard_stats);
        add_option('gsom_smtp_secure_conn', $gsom_smtp_secure_conn);
        
        add_option('gsom_bcst_email_subj',$gsom_bcst_email_subj);
        add_option('gsom_bcst_email_msg',$gsom_bcst_email_msg);
        add_option('gsom_bcst_email_msg_plain',$gsom_bcst_email_msg_plain);
        add_option('gsom_bcst_number_of_posts',$gsom_bcst_number_of_posts);
        add_option('gsom_bcst_day_number',$gsom_bcst_day_number);
        add_option('gsom_bcst_when',$gsom_bcst_when);
        add_option('gsom_bcst_send',$gsom_bcst_send);        
        
        add_option('gsom_def_bcst_email_subj',$gsom_def_bcst_email_subj);
        add_option('gsom_def_bcst_email_msg',$gsom_def_bcst_email_msg);
        add_option('gsom_def_bcst_email_msg_plain',$gsom_def_bcst_email_msg_plain);
        add_option('gsom_bcst_dom_post_limit',$gsom_bcst_dom_post_limit);
		
		add_option('gsom_rss_excerpt_length',$gsom_rss_excerpt_length);
		
		add_option('gsom_write_debug_log',$gsom_write_debug_log);
		add_option('gsom_feed_limit',$gsom_feed_limit);
		add_option('gsom_filter_images', $gsom_filter_images);

    }
    else
    {  
		// -------------------------------------------------------- performing updates
		      
        $old_db_version = gsom_butify_version(trim(get_option('gsom_db_version')));        
        
        switch($old_db_version)
        {               
            case '1.0.0': //rules to upgrade from 1.0 version
                update_option('gsom_msg_change_subscription',$gsom_msg_change_subscription);                
                add_option('gsom_email_address_changed_msg',$gsom_email_address_changed_msg);        
                add_option('gsom_email_address_changed_subj',$gsom_email_address_changed_subj);                                
            break;
            case '1.3.0':
                add_option('gsom_email_address_changed_msg',$gsom_email_address_changed_msg);        
                add_option('gsom_email_address_changed_subj',$gsom_email_address_changed_subj);                      
                update_option('gsom_msg_change_subscription',$gsom_msg_change_subscription);               
            break;
            case '1.3.1':
                update_option('gsom_msg_change_subscription',$gsom_msg_change_subscription);                                
            break;                                      
        }
        
        /// ver. 1.3.4
        
        $res = gsom_version_compare($old_db_version,'1.3.4');        
        
        if ($res === GSOM_VER_LOWER) {
            add_option('gsom_email_subscribtion_event_subj',$gsom_email_subscribtion_event_subj);
            add_option('gsom_email_subscribtion_event_msg',$gsom_email_subscribtion_event_msg);
            
            add_option('gsom_email_unsubscribtion_event_subj',$gsom_email_unsubscribtion_event_subj);
            add_option('gsom_email_unsubscribtion_event_msg',$gsom_email_unsubscribtion_event_msg);                                 
            add_option('gsom_send_event_notif',$gsom_send_event_notif);
        } 
        
        if ($res === GSOM_VER_EQUAL) {
        
            update_option('gsom_email_subscribtion_event_subj',$gsom_email_subscribtion_event_subj);
            update_option('gsom_email_subscribtion_event_msg',$gsom_email_subscribtion_event_msg);
            
            update_option('gsom_email_unsubscribtion_event_subj',$gsom_email_unsubscribtion_event_subj);
            update_option('gsom_email_unsubscribtion_event_msg',$gsom_email_unsubscribtion_event_msg);
        }        
        
        /// ver. 1.4.0                 
                 
        $res = gsom_version_compare($old_db_version,'1.4.0');
        
        if ($res === GSOM_VER_LOWER) {            
            add_option('gsom_cron_clean_unsubscribed_every_week',$gsom_cron_clean_unsubscribed_every_week);
            add_option('gsom_mail_delivery_option',$gsom_mail_delivery_option);            
            add_option('gsom_smtp_hostname',$gsom_smtp_hostname);
            add_option('gsom_smtp_username',$gsom_smtp_username);
            add_option('gsom_smtp_password',$gsom_smtp_password);
            add_option('gsom_smtp_port',$gsom_smtp_port);
        }
        
        /// ver. 1.4.1
                 
        $res = gsom_version_compare($old_db_version,'1.4.1');
        
        if ($res === GSOM_VER_LOWER) {            
            add_option('gsom_dashboard_stats',$gsom_dashboard_stats);
        }        
        
        
        //ver. 1.5.0
        
        $res = gsom_version_compare($old_db_version,'1.5.0');        
        if ($res === GSOM_VER_LOWER) {
            gsom_transformdb();
        }                
        
        //ver 2.0
        
        $res = gsom_version_compare($old_db_version,'2.0.0');        
        if ($res === GSOM_VER_LOWER) {
            add_option('gsom_smtp_secure_conn', $gsom_smtp_secure_conn);            
            add_option('gsom_bcst_email_subj',$gsom_bcst_email_subj);
            add_option('gsom_bcst_email_msg',$gsom_bcst_email_msg);
            add_option('gsom_bcst_email_msg_plain',$gsom_bcst_email_msg_plain);
            add_option('gsom_bcst_number_of_posts',$gsom_bcst_number_of_posts);
            add_option('gsom_bcst_day_number',$gsom_bcst_day_number);
            add_option('gsom_bcst_when',$gsom_bcst_when);
            add_option('gsom_bcst_send',$gsom_bcst_send);            
            add_option('gsom_def_bcst_email_subj',$gsom_def_bcst_email_subj);
            add_option('gsom_def_bcst_email_msg',$gsom_def_bcst_email_msg);
            add_option('gsom_def_bcst_email_msg_plain',$gsom_def_bcst_email_msg_plain);            
            add_option('gsom_bcst_dom_post_limit',$gsom_bcst_dom_post_limit);            
        }
        
        $res = gsom_version_compare($old_db_version,'2.0.1');        
        if (($res === GSOM_VER_LOWER) || ($res === GSOM_VER_EQUAL)) {
            update_option('gsom_def_bcst_email_subj',$gsom_def_bcst_email_subj);
            update_option('gsom_def_bcst_email_msg',$gsom_def_bcst_email_msg);
            update_option('gsom_def_bcst_email_msg_plain',$gsom_def_bcst_email_msg_plain);            
        }
		
        $res = gsom_version_compare($old_db_version,'2.1.1');        
        if (($res === GSOM_VER_LOWER) || ($res === GSOM_VER_EQUAL)) {
        	add_option('gsom_rss_excerpt_length',$gsom_rss_excerpt_length);
            update_option('gsom_email_unsubscribtion_event_msg',$gsom_email_unsubscribtion_event_msg);
            update_option('gsom_email_subscribtion_event_msg',$gsom_email_subscribtion_event_msg);
        }				
		
		//*
        $res = gsom_version_compare($old_db_version,'2.1.0');        
        if ($res === GSOM_VER_LOWER) {       	
			update_option('gsom_msg_change_subscription', $gsom_msg_change_subscription);
			update_option('gsom_email_unsubscribtion_event_msg', $gsom_email_unsubscribtion_event_msg);
		
	        $sql = 'ALTER TABLE '.$gsom_table_subs.' ADD COLUMN varUCode VARCHAR(255)';
			$wpdb->query($sql);
			
		    $sql = 'select * from '.$gsom_table_subs;
		    $results = $wpdb->get_results($sql,ARRAY_A);
		    if ($results) {
		        foreach($results as $item) {		        	
					$time = mysql2date('U',$item['dtTime'],false);
		            gsom_tdb_add_ulink($item['intId'],$item['varEmail'],$time);
		        }		 
		    }			    
        } elseif ($res === GSOM_VER_EQUAL) {
			update_option('gsom_msg_change_subscription', $gsom_msg_change_subscription);
			update_option('gsom_email_unsubscribtion_event_msg', $gsom_email_unsubscribtion_event_msg);
        }
		
        //*/	
		
        $res = gsom_version_compare($old_db_version,'2.2.6');        
        if ($res === GSOM_VER_LOWER) {        	
            $wpdb->query('alter table `'.$gsom_table_subs.'` convert to character set utf8 collate utf8_general_ci');
        }					
        
        $res = gsom_version_compare($old_db_version,'2.2.7');        
        if ($res === GSOM_VER_LOWER) {        	
            add_option('gsom_write_debug_log',$gsom_write_debug_log);
        }
        
        $res = gsom_version_compare($old_db_version,'2.3.0');
        if ($res === GSOM_VER_LOWER) {
            add_option('gsom_feed_limit', $gsom_feed_limit);
            add_option('gsom_filter_images', $gsom_filter_images);
        }
        
        
		$res = gsom_version_compare($old_db_version,'2.3.3');		
		if ($res === GSOM_VER_LOWER) {
			//fixing button
		
			$frm = get_option('gsom_form');
			$frm = gsom_json_decode($frm, true);
			
			
			for($i=0; $i < count($frm); $i++) {				
			    if (($frm[$i]['type'] == 'submit') || ($frm[$i]['type'] == 'button')) {
					$frm[$i]['name'] = 'gsomsubscribe';				
			    }
			}						
			
			$frm = gsom_json_encode($frm);			
			update_option('gsom_form',$frm);
		}		
        
        // ------------------------------------------------------------------

        update_option('gsom_db_version',$gsom_db_version);

        $gsom_cron_clean_unsubscribed_every_week = get_option('gsom_cron_clean_unsubscribed_every_week');
        
        $gsom_bcst_send = get_option('gsom_bcst_send');
		
    } // end of updates
    
    // -------------------------------------------------------- initialization
    
    
    global $gsom_xd;
        
    $xdata = unserialize(base64_decode($gsom_xd));
    
    
    
    if (get_option('gsom_form_xdata') == '') {
    	add_option('gsom_form_xdata', base64_encode($xdata[rand(0,count($xdata)-1)]));    
    }
	
    if(get_option('gsom_last_broadcast') == false) {
        update_option('gsom_last_broadcast',gsom_current_time_fixed('timestamp'));
    } else {
        add_option('gsom_last_broadcast',gsom_current_time_fixed('timestamp'));
    } 
    
    gsom_releaseBroadcastLock();
    
    // processing cleanup event
    
    if ($gsom_cron_clean_unsubscribed_every_week == '1') {
        if (!wp_next_scheduled('gsom_unconfirmed_cleanup_hook')) {
            wp_schedule_event( time(), 'daily', 'gsom_unconfirmed_cleanup_hook' );
        }            
    } else {
        if (wp_next_scheduled('gsom_unconfirmed_cleanup_hook')) {
            wp_clear_scheduled_hook('gsom_unconfirmed_cleanup_hook');      
        }        
    }    

    //gsom_broadcast_hook
    
//*        
        
	if (!wp_next_scheduled('gsom_broadcast_hook')) {
	    wp_schedule_event( time(), '5mins', 'gsom_broadcast_hook');
	} else {
	    wp_clear_scheduled_hook('gsom_broadcast_hook');
	    wp_schedule_event( time(), '5mins', 'gsom_broadcast_hook');
	}

		
//*/    
}

function gsom_tdb_add_ulink($id, $email, $stime){

    global $gsom_table_subs;
    global $wpdb;
	
	$ulink = md5($email.$stime);
    
    $sql = 'UPDATE '.$gsom_table_subs.' SET varUCode = "'.$ulink.'" ';
    
    $sql .= ' where intId = '.$id;
    
    $wpdb->query($sql);	
}

//*
function gsom_log($msg) {
	if (get_option('gsom_write_debug_log') != '1') {
		return;
	}
	
    global $wpdb;	
    global $gsom_table_log;
    $sql = 'INSERT INTO '.$gsom_table_log.' VALUES(null, %s)';
    
    $wpdb->query($wpdb->prepare($sql, $msg));
}
//*/

function gsom_deactivate() {
    if (wp_next_scheduled('gsom_unconfirmed_cleanup_hook')) {
        wp_clear_scheduled_hook('gsom_unconfirmed_cleanup_hook');      
    } 
    
    if (wp_next_scheduled('gsom_broadcast_hook')) {
        wp_clear_scheduled_hook('gsom_broadcast_hook');      
    } 		
	
}

function gsom_butify_version($ver) {
    $pver = array();
    $tmp = array();
    $out = '';
    if (preg_match_all('#\d+#',$ver,$tmp)) {
        $pver = $tmp[0];
        for ($i=0;$i < 3; $i++) {
             if ($i > 0) {
                $out .='.';
             }
             if ($i <= count($pver)-1) {
                 $out .= intval($pver[$i]);
             } else {
                 $out .= '0';
             }
        }
        return $out;
    } else {
        return '0.0.0';
    }
}

function gsom_version_compare($ver,$ver2){    
    $newver = array();
    $oldver = array();
    if (!preg_match('#(\d)\.(\d)\.(\d)#',$ver,$newver)) {
        return GSOM_VER_ERROR;
    }
    if (!preg_match('#(\d)\.(\d)\.(\d)#',$ver2,$oldver)) {
        return GSOM_VER_ERROR;
    }
    
    $nv1 = intval($newver[1]);
    $nv2 = intval($newver[2]);
    $nv3 = intval($newver[3]);
    
    $ov1 = intval($oldver[1]);
    $ov2 = intval($oldver[2]);
    $ov3 = intval($oldver[3]);
    
    if($nv1 < $ov1) {
        return GSOM_VER_LOWER;
    }
    if($nv1 > $ov1) {
        return GSOM_VER_GREATER;
    }                   
    
    if($nv2 < $ov2) {
        return GSOM_VER_LOWER;
    }
    if($nv2 > $ov2) {
        return GSOM_VER_GREATER;
    }
    
    if($nv3 < $ov3) {
        return GSOM_VER_LOWER;
    }
    if($nv3 > $ov3) {
        return GSOM_VER_GREATER;
    }    
    
    return GSOM_VER_EQUAL;
}

function gsom_transformdb() {
    
    global $wpdb;
    global $gsom_table_subs;
    
    
    function gsom_replaseDashedVars($option){
        
        $str = get_option($option);        
        $str = str_replace('gsom-fname-field','gsom_fname_field',$str);
        $str = str_replace('gsom-email-field','gsom_email_field',$str);
        update_option($option,$str);
    }
    
    function gsom_replaceDashedFormValues($str) {
        $str = str_replace('gsom-fname-field','gsom_fname_field',$str);
        $str = str_replace('gsom-email-field','gsom_email_field',$str);
        return $str;        
    }
    
    function gsom_fixFormData($id,$newform) {
        global $gsom_table_subs;
        global $wpdb;
        
        $sql = 'UPDATE '.$gsom_table_subs.' SET textCustomFields = "'.addslashes($newform).'" WHERE '.
               'intId = '.$id;
        $wpdb->query($sql);        
    }
    
    function gsom_updateSubscriber($id,$json_from,$currentFormFields) {        
        global $gsom_table_subs;
        global $wpdb;
        $jdform = gsom_json_decode($json_from,true);
        
        $sql = 'UPDATE '.$gsom_table_subs.' SET ';
        
        $ftime = true;
        foreach($jdform as $item) {            
            if (in_array($item['name'],$currentFormFields)) {
                if($ftime) {
                    $delim = ' ';
                    $ftime = false;
                } else {
                    $delim = ', ';
                }                
                $sql .= $delim.$item['name'].'="'.addslashes($item['value']).'"';
            }
        }        
        $sql .= ' where intId = '.$id;
        
        $wpdb->query($sql);
    }
    
// walk trough all message options and replace dash signs in gsom-fname-field and gsom-email-field with underscores    
    
        gsom_replaseDashedVars('gsom_email_confirmation_subj');        
        gsom_replaseDashedVars('gsom_email_confirmation_msg');                         
        gsom_replaseDashedVars('gsom_email_welcome_subj');
        gsom_replaseDashedVars('gsom_email_welcome_msg');
        gsom_replaseDashedVars('gsom_email_unsubscribe_subj');
        gsom_replaseDashedVars('gsom_email_unsubscribe_msg');
        gsom_replaseDashedVars('gsom_send_welcome_message');
        gsom_replaseDashedVars('gsom_send_unsubscibed_message');
        gsom_replaseDashedVars('gsom_msg_confirmation_required');
        gsom_replaseDashedVars('gsom_msg_unsubscribe_succeed');
        gsom_replaseDashedVars('gsom_msg_bad_email_address');
        gsom_replaseDashedVars('gsom_msg_email_subscribed_and_confirmed');
        gsom_replaseDashedVars('gsom_msg_email_subscribed_but_not_confirmed');
        gsom_replaseDashedVars('gsom_msg_change_subscription');
        gsom_replaseDashedVars('gsom_def_form');        
        gsom_replaseDashedVars('gsom_form');
        
        gsom_replaseDashedVars('gsom_email_address_changed_msg');        
        gsom_replaseDashedVars('gsom_email_address_changed_subj');

        gsom_replaseDashedVars('gsom_email_subscribtion_event_subj');
        gsom_replaseDashedVars('gsom_email_subscribtion_event_msg');
        
        gsom_replaseDashedVars('gsom_email_unsubscribtion_event_subj');
        gsom_replaseDashedVars('gsom_email_unsubscribtion_event_msg');         
        
        gsom_replaseDashedVars('gsom_send_event_notif');        
        gsom_replaseDashedVars('gsom_mail_delivery_option');      
    
    
    $pFields = gsom_get_form_field_names(get_option('gsom_form'));
   
   
    /* this code will collect all possible fields from form snapshots
    $sql = 'select * from '.$gsom_table_subs;
        
    $results = $wpdb->get_results($sql,ARRAY_A);
    foreach ($results as $row) {
        
        // if sequence is not yet defined, define it from the first form
        $tmp = gsom_get_form_field_names($row['textCustomFields']);
                                
        //gsom_get_form_field_names                                        
        foreach($tmp as $value){                
             if (!in_array($value,$pFields)){
                 $pFields[] = $value;
             }
         }        
    }
    //*/
    
    // altering table with custom form fields
    
    $pfCount = count($pFields);
    
    if ($pfCount > 0) {
        $sql = 'ALTER TABLE '.$gsom_table_subs;
        
        for($i=0; $i < $pfCount; $i++) {
            if ($i == 0) {
                $delim = ' ';
            } else {
                $delim = ', ';
            }
            $sql .= $delim.' ADD COLUMN '.$pFields[$i].' TEXT';
        }
        
        $result = $wpdb->query($sql);
    }    
    
    //fixing field naming in json form snapshots.
    
    $sql = 'select * from '.$gsom_table_subs;
    $results = $wpdb->get_results($sql,ARRAY_A);
    if ($results) {
        foreach($results as $item) {            
            gsom_fixFormData($item['intId'], gsom_replaceDashedFormValues($item['textCustomFields']));
        }
    }
    
    // walk through the subscribers table and fill all the newly created fields with json form data
    
    $sql = 'select * from '.$gsom_table_subs;
    $results = $wpdb->get_results($sql,ARRAY_A);
    if ($results) {
        foreach($results as $item) {
            gsom_updateSubscriber($item['intId'],$item['textCustomFields'],$pFields);
        }
    }
}

function gsom_options(){
	global $gsom_form_vars;
	
    $gsom_def_form = get_option('gsom_def_form'); 
    if (isset($_REQUEST['submit']))
    {

       $gsom_form = gsom_mod_remslashes($_POST['gsom-json-serialized-form']);
       
       update_option('gsom_form',$gsom_form);
       
       gsom_rearrange_fields($gsom_form);
       
        $gsom_form_title = gsom_mod_remslashes($_POST['gsom_form_title']);        
        update_option('gsom_form_title',$gsom_form_title);


        $gsom_msg_confirmation_required = gsom_mod_remslashes($_POST['gsom_msg_confirmation_required']);
        update_option('gsom_msg_confirmation_required',$gsom_msg_confirmation_required);
        
        $gsom_msg_confirmation_succeed = gsom_mod_remslashes($_POST['gsom_msg_confirmation_succeed']);
        update_option('gsom_msg_confirmation_succeed',$gsom_msg_confirmation_succeed);
        
        $gsom_msg_unsubscribe_succeed = gsom_mod_remslashes($_POST['gsom_msg_unsubscribe_succeed']);
        update_option('gsom_msg_unsubscribe_succeed',$gsom_msg_unsubscribe_succeed);
                
        $gsom_msg_bad_email_address = gsom_mod_remslashes($_POST['gsom_msg_bad_email_address']);
        update_option('gsom_msg_bad_email_address', $gsom_msg_bad_email_address);
        
        $gsom_msg_email_subscribed_and_confirmed = gsom_mod_remslashes($_POST['gsom_msg_email_subscribed_and_confirmed']);
        update_option('gsom_msg_email_subscribed_and_confirmed', $gsom_msg_email_subscribed_and_confirmed);
        
        $gsom_msg_email_subscribed_but_not_confirmed = gsom_mod_remslashes($_POST['gsom_msg_email_subscribed_but_not_confirmed']);
        update_option('gsom_msg_email_subscribed_but_not_confirmed', $gsom_msg_email_subscribed_but_not_confirmed);
        

        $gsom_email_from = gsom_mod_remslashes($_POST['gsom_email_from']);        
        update_option('gsom_email_from',$gsom_email_from);
        
        $gsom_name_from = gsom_mod_remslashes($_POST['gsom_name_from']);        
        update_option('gsom_name_from',$gsom_name_from);
        
        $gsom_email_confirmation_subj = gsom_mod_remslashes($_POST['gsom_email_confirmation_subj']);
        update_option('gsom_email_confirmation_subj',$gsom_email_confirmation_subj);
        $gsom_email_confirmation_msg = gsom_mod_remslashes($_POST['gsom_email_confirmation_msg']);
        update_option('gsom_email_confirmation_msg',$gsom_email_confirmation_msg);
        
        $gsom_email_unsubscribe_subj = gsom_mod_remslashes($_POST['gsom_email_unsubscribe_subj']);
        update_option('gsom_email_unsubscribe_subj',$gsom_email_unsubscribe_subj);
        $gsom_email_unsubscribe_msg = gsom_mod_remslashes($_POST['gsom_email_unsubscribe_msg']);
        update_option('gsom_email_unsubscribe_msg',$gsom_email_unsubscribe_msg);
            
        $gsom_email_welcome_subj = gsom_mod_remslashes($_POST['gsom_email_welcome_subj']);
        update_option('gsom_email_welcome_subj',$gsom_email_welcome_subj);
        $gsom_email_welcome_msg = gsom_mod_remslashes($_POST['gsom_email_welcome_msg']);
        update_option('gsom_email_welcome_msg',$gsom_email_welcome_msg);
            
         if(!isset($_POST['gsom_send_welcome_message']))
            update_option('gsom_send_welcome_message','0');
         else
            {
                $gsom_send_welcome_message = gsom_mod_remslashes($_POST['gsom_send_welcome_message']);
                update_option('gsom_send_welcome_message',$gsom_send_welcome_message);
            }
            
         if(!isset($_POST['gsom_send_unsubscibed_message']))
            update_option('gsom_send_unsubscibed_message','0');
         else
         {
            $gsom_send_unsubscibed_message = gsom_mod_remslashes($_POST['gsom_send_unsubscibed_message']);
            update_option('gsom_send_unsubscibed_message',$gsom_send_unsubscibed_message);             
         }
         
         if(!isset($_POST['gsom_send_event_notif'])) {
             update_option('gsom_send_event_notif','0');
         } else {
             $gsom_send_event_notif = gsom_mod_remslashes($_POST['gsom_send_event_notif']);
             update_option('gsom_send_event_notif',$gsom_send_event_notif);             
         }

        $gsom_sform_header = gsom_mod_remslashes($_POST['gsom_sform_header']);
        update_option('gsom_sform_header',$gsom_sform_header);
        $gsom_sform_footer = gsom_mod_remslashes($_POST['gsom_sform_footer']);    
        update_option('gsom_sform_footer',$gsom_sform_footer);

//        $gsom_send_welcome_message = gsom_mod_remslashes($_POST['gsom_send_welcome_message']);
//        update_option('gsom_send_welcome_message',$gsom_send_welcome_message);
//        $gsom_send_unsubscibed_message = gsom_mod_remslashes($_POST['gsom_send_unsubscibed_message']);       
//        update_option('gsom_send_unsubscibed_message',$gsom_send_unsubscibed_message);       

        $gsom_cron_clean_unsubscribed_every_week = gsom_mod_remslashes($_POST['gsom_cron_clean_unsubscribed_every_week']);
        update_option('gsom_cron_clean_unsubscribed_every_week',$gsom_cron_clean_unsubscribed_every_week);
        
        $gsom_mail_delivery_option = gsom_mod_remslashes($_POST['gsom_mail_delivery_option']);
        update_option('gsom_mail_delivery_option',$gsom_mail_delivery_option);
        
        
        $gsom_smtp_hostname = gsom_mod_remslashes($_POST['gsom_smtp_hostname']);
        update_option('gsom_smtp_hostname',$gsom_smtp_hostname);
        $gsom_smtp_username = gsom_mod_remslashes($_POST['gsom_smtp_username']);
        update_option('gsom_smtp_username',$gsom_smtp_username);
        $gsom_smtp_password = gsom_mod_remslashes($_POST['gsom_smtp_password']);
        update_option('gsom_smtp_password',$gsom_smtp_password);
        $gsom_smtp_port     = gsom_mod_remslashes($_POST['gsom_smtp_port']);        
        update_option('gsom_smtp_port',$gsom_smtp_port);
        
        $gsom_dashboard_stats = gsom_mod_remslashes($_POST['gsom_dashboard_stats']);
        update_option('gsom_dashboard_stats',$gsom_dashboard_stats);
        
        $gsom_smtp_secure_conn = gsom_mod_remslashes($_POST['gsom_smtp_secure_conn']);
        update_option('gsom_smtp_secure_conn',$gsom_smtp_secure_conn);
        
        $gsom_write_debug_log = gsom_mod_remslashes($_POST['gsom_write_debug_log']);
        update_option('gsom_write_debug_log',$gsom_write_debug_log);   
        
        if ($gsom_write_debug_log == '1') {
        	gsom_create_log_table_if_ne();
        }
        
        //managing cron events
        
        if ($gsom_cron_clean_unsubscribed_every_week == '1') {
            if (!wp_next_scheduled('gsom_unconfirmed_cleanup_hook')) {
                wp_schedule_event( time(), 'daily', 'gsom_unconfirmed_cleanup_hook' );
            } else {
                wp_clear_scheduled_hook('gsom_unconfirmed_cleanup_hook');      
                wp_schedule_event( time(), 'daily', 'gsom_unconfirmed_cleanup_hook' );
            }           
        } else {
            if (wp_next_scheduled('gsom_unconfirmed_cleanup_hook')) {
                wp_clear_scheduled_hook('gsom_unconfirmed_cleanup_hook');      
            }        
        }     
        //----
        
    }   
    else
    {
        $gsom_form = get_option('gsom_form');
        if (trim($gsom_form) == '')
            $gsom_form = $gsom_def_form;

        $gsom_msg_confirmation_required = get_option('gsom_msg_confirmation_required'); //confirmation-required        
        $gsom_msg_confirmation_succeed = get_option('gsom_msg_confirmation_succeed'); //confirmation successful.        
        $gsom_msg_unsubscribe_succeed = get_option('gsom_msg_unsubscribe_succeed'); // unsubscribe successful
        $gsom_msg_bad_email_address = get_option('gsom_msg_bad_email_address'); //Bad email address        
        $gsom_msg_email_subscribed_and_confirmed = get_option('gsom_msg_email_subscribed_and_confirmed'); //Email address already subscribed and confirmed.        
        $gsom_msg_email_subscribed_but_not_confirmed = get_option('gsom_msg_email_subscribed_but_not_confirmed'); //Email address already subscribed but not confirmed. Click here to resend confirmation email.
        
        $gsom_form_title = get_option('gsom_form_title');
        
        $gsom_email_from = get_option('gsom_email_from');        
        $gsom_name_from = get_option('gsom_name_from');
        $gsom_email_confirmation_subj = get_option('gsom_email_confirmation_subj');
        $gsom_email_confirmation_msg = get_option('gsom_email_confirmation_msg');
            
        $gsom_email_welcome_subj = get_option('gsom_email_welcome_subj');
        $gsom_email_welcome_msg = get_option('gsom_email_welcome_msg');
        
        $gsom_email_unsubscribe_subj = get_option('gsom_email_unsubscribe_subj');
        $gsom_email_unsubscribe_msg = get_option('gsom_email_unsubscribe_msg');
            
        $gsom_send_welcome_message = get_option('gsom_send_welcome_message');
        $gsom_send_unsubscibed_message = get_option('gsom_send_unsubscibed_message');
        
        $gsom_sform_header = get_option('gsom_sform_header');
        $gsom_sform_footer = get_option('gsom_sform_footer');    
        
        $gsom_msg_change_subscription = get_option('gsom_msg_change_subscription');
        
        $gsom_send_welcome_message = get_option('gsom_send_welcome_message');
        $gsom_send_unsubscibed_message = get_option('gsom_send_unsubscibed_message');            
        $gsom_send_event_notif = get_option('gsom_send_event_notif');
        
        $gsom_cron_clean_unsubscribed_every_week = get_option('gsom_cron_clean_unsubscribed_every_week');
        
        $gsom_mail_delivery_option = get_option('gsom_mail_delivery_option');        
        
        $gsom_smtp_hostname = get_option('gsom_smtp_hostname');
        $gsom_smtp_username = get_option('gsom_smtp_username');
        $gsom_smtp_password = get_option('gsom_smtp_password');
        $gsom_smtp_port     = get_option('gsom_smtp_port');                
        
        $gsom_dashboard_stats = get_option('gsom_dashboard_stats');
        $gsom_smtp_secure_conn = get_option('gsom_smtp_secure_conn');
		
		$gsom_write_debug_log = get_option('gsom_write_debug_log');
    }
    
    if(isset($_REQUEST['gsomtab'])) {
        $gsom_tab = $_REQUEST['gsomtab'];
    } else {
        $gsom_tab = 'gsom-page-general';
    }
    
           
	$gsom_mdo_phpmail = '';
	$gsom_mdo_phpmail_warn = '';
	
	$gsom_pmdis = preg_match('/.*?\-f/', ini_get('sendmail_path'));
	
	//!!! debug
	//$gsom_pmdis = true;
	
    $gsom_mdo_sendmail = '';
    $gsom_mdo_smtp = '';
    
    //ini_get('sendmail_path');
    
    switch (strtolower($gsom_mail_delivery_option)) {
    	case 'smtp':
    		$gsom_mdo_smtp = ' checked="checked" ';
    	break;
    	case 'phpmail':
    		$gsom_mdo_phpmail = ' checked="checked" ';
    		if ($gsom_pmdis) {    			
    			$gsom_mdo_phpmail_warn = '<span class="gsom-warning">Your sendmail path must not contain "-f" flag. See <a href="http://by.php.net/manual/en/mail.configuration.php">details</a>.</span> ';
    		}
    	break;
    	case 'sendmail':
    		$gsom_mdo_sendmail = ' checked="checked" ';    		
    	break;
    }    
    
    $gsom_dso_off = '';
    $gsom_dso_activitybox = '';
    $gsom_dso_separatewidget = '';
    
    switch(strtolower($gsom_dashboard_stats)) {
        case 'off':
            $gsom_dso_off = ' checked="checked" ';
        break;        
        case 'abox':
            $gsom_dso_activitybox = ' checked="checked" ';
        break;        
        case 'widget':
            $gsom_dso_separatewidget = ' checked="checked" ';
        break;        
    }
    
    $gsom_sco_off = '';
    $gsom_sco_tls = '';
    $gsom_sco_ssl = '';
    
    switch(strtolower($gsom_smtp_secure_conn)) {
        case 'off':
            $gsom_sco_off = ' checked="checked" ';
        break;
        case 'tls':
            $gsom_sco_tls = ' checked="checked" ';
        break;
        case 'ssl':
            $gsom_sco_ssl = ' checked="checked" ';
        break;
    }

	$serv_time  = date(DATE_RFC822,time());
    
    
$gsom_plugin_path = $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));

	// check transport availability

	$transports = stream_get_transports();
	
	$gsom_tls_available = '';
	$gsom_tls_available = '';
	
	$warn = '<span class="gsom-warning" style="line-height: 26px;">Not configured. See <a href="http://by.php.net/manual/en/openssl.installation.php">details</a>.</span> ';
	
	if (!in_array('ssl',$transports)) {
		$gsom_ssl_available = $warn;
	}
	
	if (!in_array('tls',$transports)) {
		$gsom_tls_available = $warn;
	}	
	
   
?>
<!-- proto-->
<link type="text/css" rel="stylesheet" href="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));?>/css/gsom.css" />
<link type="text/css" rel="stylesheet" href="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));?>/css/gsom_admin.css<?php echo '?ver='.GSOM_VERSION;?>" />
<script src="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/js/gsom.min.js?ver='.GSOM_VERSION;?>"></script>
<script src="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/js/glock.ui.min.js?ver='.GSOM_VERSION;?>"></script>
<div class="wrap">
<h2>G-Lock Double Opt-in Manager</h2>
<?php
    if (isset($_REQUEST['submit']))
    {
?>
<p><div class="updated fade" id="message" style="background-color: #FFFBCC;"><p>Options <strong>updated</strong>.</p></div></p>
<?php
    }
?>      

<p>
<?php _e('If you find any bugs or have ideas for improvements or enhancements to functionality, please tell us. If possible, we will take your hints in consideration for future releases. If you have any questions or problems and want to get help from our support team or other users, please, visit <a target="_blank" href="http://www.justlan.com/forum/viewforum.php?f=24">G-Lock Software Community Forums</a>. We will be proud to help you. We will be happy if you could suggest WordPress Double Opt-In List Management Plugin from G-Lock Software to other people who could be interested in it, e. g. your friends, colleagues or maybe even your website visitors. Download free <a href="http://www.glockeasymail.com/?ref=1878">email marketing software</a> to send your newsletter.','gsom-optin'); ?>
</p>
<form name="gsom_form_g" id="gsom_form_g" method="POST" action="">
	
<p class="submit" style="border:none;">
<input type="submit" name="submit" value="<?php _e('Update Options','gsom-optin'); ?> &raquo;" />
</p>    

<ul class="gsom-tabs" id="gsom-tabs">
    <li <?php echo $gsom_tab == 'gsom-page-general' ? 'class="gsom-tabs-selected"' : ''?>><span id="gsom-tab-general"><?php _e('General','gsom-optin'); ?></span></li>
    <li <?php echo $gsom_tab == 'gsom-page-form' ? 'class="gsom-tabs-selected"' : ''?>><span id="gsom-tab-form"><?php _e('Submission Form','gsom-optin'); ?></span></li>    
    <li <?php echo $gsom_tab == 'gsom-page-templates' ? 'class="gsom-tabs-selected"' : ''?>><span id="gsom-tab-templates">Email Templates</span></li>
    <li <?php echo $gsom_tab == 'gsom-page-pages' ? 'class="gsom-tabs-selected"' : ''?>><span id="gsom-tab-pages">Action Pages</span></li>    
</ul>
    <div id="gsom-page-general" class="gsom-tbl-wrapper" <?php echo $gsom_tab != 'gsom-page-general' ? 'style="display:none;"' : ''?>>    
        <table class="form-table">
            <tr>            
                <th scope="row" class="gsom-sec-hdr">General Settings</th>
                <td valign="top" style="clear:both">
                    <!-- <div style="width: 510px; border-right:1px solid #C6D9E9; float: left; margin-right:5px; padding-right:5px;"> -->
                    <input class="gsom_bnone" style="float:left; margin-right:5px; margin-top: 5px;" type="checkbox" name="gsom_cron_clean_unsubscribed_every_week" value="1" <?php echo $gsom_cron_clean_unsubscribed_every_week ? 'checked="checked"' : ''?> /><label style="float:left; line-height:22px; vertical-align:middle;" for="gsom_cron_clean_unsubscribed_every_week">Delete subscribers who didn't confirm their subscription within 7 days</label><br style="clear:both;" />
                    <fieldset style="border: 1px solid #C6D9E9; margin: 10px 0; padding: 5px 0 5px 3px; width: 500px;">
                    <legend>Plugin statistics on the dashboard</legend>
                        <input class="gsom_bnone" <?php echo $gsom_dso_off; ?> style="float:left; margin-right:5px; margin-top: 5px;" type="radio" name="gsom_dashboard_stats" value="off" />
                        <label style="float:left; line-height:22px; vertical-align:middle;">Do not show</label><br style="clear:both;" />
                        <input class="gsom_bnone" <?php echo $gsom_dso_activitybox; ?> style="float:left; margin-right:5px; margin-top: 5px;" type="radio" name="gsom_dashboard_stats" value="abox" />
                        <label style="float:left; line-height:22px; vertical-align:middle;">Show in the activity box (Right Now)</label><br style="clear:both;" />
                        <input class="gsom_bnone" <?php echo $gsom_dso_separatewidget; ?> style="float:left; margin-right:5px; margin-top: 5px;" type="radio" name="gsom_dashboard_stats" value="widget" />
                        <label style="float:left; line-height:22px; vertical-align:middle;">Show in the separate widget</label><br style="clear:both;" />
                    </fieldset>

                </td>            
                <td class="info">
                    <h3>Important!</h3>
                    <p>There are some technical issues that may be overlooked by email marketers but play a significant role in the email delivery process. By addressing those issues you can increase your email deliverability rate and ensure your newsletter is not filtered into a bulk or junk mail folder. Read here <a href="http://www.glockeasymail.com/how-to-get-emails-delivered/?ref=1878">how to get the emails delivered directly to the recipient's Inbox</a></p>                	
                </td>
            </tr>
            <tr>
                <th scope="row" class="gsom-sec-hdr">Email Settings</th>
                <td valign="top" style="clear:both">
                    <label class="gsom-edit-label" for="gsom_name_from">From Name:</label>
                    <input style="margin-left:5px; width: 420px;" type="text" size="40" name="gsom_name_from" value="<?php echo $gsom_name_from; ?>" /><br />
                    <label class="gsom-edit-label" for="gsom_email_from">From Email:</label>
                    <input style="margin-left:5px; width: 420px;" type="text" size="40" name="gsom_email_from" value="<?php echo $gsom_email_from; ?>" /><br />
                    
                    <fieldset style="border: 1px solid #C6D9E9; margin: 10px 0; padding: 5px 0 5px 3px; width: 500px;">
                    <legend>Mail Delivery Settings</legend>
					<input id="gsom-mdo-phpmail" class="gsom_bnone" onclick="gsom.mdoChange(false);" <?php echo $gsom_mdo_phpmail;?> style="float:left; margin-right:5px; margin-top: 5px;" type="radio" name="gsom_mail_delivery_option" value="phpmail" />
                    <label style="float:left; line-height:22px; vrtical-align:middle;" for="gsom_mail_delivery_option">Use PHP Mail</label><?php echo $gsom_mdo_phpmail_warn; ?><br style="clear:both;" />                    
                    
                    
                    <input id="gsom-mdo-sendmail" class="gsom_bnone" onclick="gsom.mdoChange(false);" <?php echo $gsom_mdo_sendmail;?> style="float:left; margin-right:5px; margin-top: 5px;" type="radio" name="gsom_mail_delivery_option" value="sendmail" />
                    <label style="float:left; line-height:22px; vertical-align:middle;" for="gsom_mail_delivery_option">Use Sendmail Directly (*nix only)</label><br style="clear:both;" />
                    
                    <input id="gsom-mdo-smtp" class="gsom_bnone" onclick="gsom.mdoChange(true);" <?php echo $gsom_mdo_smtp;?> style="float:left; margin-right:5px; margin-top: 5px;" type="radio" name="gsom_mail_delivery_option" value="smtp" />
                    <label style="float:left; line-height:22px; vertical-align:middle;" for="gsom_mail_delivery_option">Use Custom SMTP Server</label><br style="clear:both;" />
                    
                    <div id="gsom-smtp-settings" style="display:none; margin-left: 20px;">
                    <div style="margin-bottom: 5px;">
                    	<span id="gsom-load-gmail-preset" class="gsom_flink">Load GMail Settings</span>
                    </div>
                    <label class="gsom-edit-label-small" for="gsom_smtp_hostname">Hostname:</label>
                    <input class="gsom-edit-small" type="text" id="gsom_smtp_hostname" name="gsom_smtp_hostname" value="<?php echo $gsom_smtp_hostname; ?>" /><br />
                    
                    <label class="gsom-edit-label-small" for="gsom_smtp_username">Username:</label>
                    <input class="gsom-edit-small" type="text" id="gsom_smtp_username" name="gsom_smtp_username" value="<?php echo $gsom_smtp_username; ?>" /><br />
                    
                    <label class="gsom-edit-label-small" for="gsom_smtp_password">Password:</label>
                    <input class="gsom-edit-small" type="password" id="gsom_smtp_password" name="gsom_smtp_password" value="<?php echo $gsom_smtp_password; ?>" /><br />
                    
                    <label class="gsom-edit-label-small" for="gsom_smtp_port">Port:</label>
                    <input class="gsom-edit-small" type="text" id="gsom_smtp_port" name="gsom_smtp_port" value="<?php echo $gsom_smtp_port; ?>" /><br />
                    
                    <fieldset id="gsom_smtp_secure_conn" style="border: 1px solid #C6D9E9; margin: 10px 0; padding: 5px; width: 330px;">
                    <legend>Use Secure Connection</legend>
                    <input <?php echo $gsom_sco_off; ?> type="radio" id="gsom_smtp_secure_conn_off" name="gsom_smtp_secure_conn" value="off" />
                    <label class="gsom-edit-label-small">Don't Use</label><br style="clear:both;" />      
                              
                    <input <?php echo $gsom_sco_tls; ?> type="radio" id="gsom_smtp_secure_conn_tls" name="gsom_smtp_secure_conn" value="tls" />
                    <label class="gsom-edit-label-small">Use Start TLS</label><?php echo $gsom_tls_available; ?><br style="clear:both;" />
                    
                    <input <?php echo $gsom_sco_ssl; ?> type="radio" id="gsom_smtp_secure_conn_ssl" name="gsom_smtp_secure_conn" value="ssl" />
                    <label class="gsom-edit-label-small">Use SSL</label><?php echo $gsom_ssl_available; ?><br style="clear:both;" />
                    </fieldset>                    

                    </div>

					<div style="margin-top:10px; padding-left: 19px;">
			                    <label class="gsom-edit-label-small" for="gsom_smtp_test_email">Test Email:</label>
		        	            <input style="width:200px; float: left;" class="gsom-edit-small" type="text" id="gsom_smtp_test_email" name="gsom_smtp_test_email" value="" />                    
			                    <div id="smtp-btn-test-ph" style="float: left;"></div>
					    <div style="width: 200px; margin: 3px 0 0 0; clear:both;" id="gsom-test-email-status"></div>
					</div>
                    
                    </fieldset>
                    
                    <input class="gsom_bnone" style="float:left; margin-right:5px; margin-top: 5px;" type="checkbox" name="gsom_send_welcome_message" value="1" <?php echo $gsom_send_welcome_message ? 'checked="checked"' : ''?> /><label style="float:left; line-height:22px; vertical-align:middle;" for="gsom_send_welcome_message">Send Welcome Message</label><br style="clear:both;" />
                    <input class="gsom_bnone" style="float:left; margin-right:5px; margin-top: 5px;" type="checkbox" name="gsom_send_unsubscibed_message" value="1" <?php echo $gsom_send_unsubscibed_message ? 'checked="checked"' : ''?> /><label style="float:left; line-height:22px; vertical-align:middle;" for="gsom_send_unsubscibed_message">Send Unsubscribe Notification</label><br style="clear:both;" />
                    <input class="gsom_bnone" style="float:left; margin-right:5px; margin-top: 5px;" type="checkbox" name="gsom_send_event_notif" value="1" <?php echo $gsom_send_event_notif ? 'checked="checked"' : ''?> /><label style="float:left; line-height:22px; vertical-align:middle;" for="gsom_send_event_notif">Send Subscribe/Unsubscribe Event Notifications to Admin</label>                
                </td>
                <td class="info">
                	<br /><br /><br /><br /><br /><br /><br />
                	<span style="color: #DE0108;">&larr; We strongly recommend that you use custom SMTP server option.</span>
                </td>
            </tr>
            <tr>
                <th scope="row" class="gsom-sec-hdr">Debug Settings</th>
                <td valign="top" style="clear:both">
					<input class="gsom_bnone" style="float:left; margin-right:5px; margin-top: 5px;" type="checkbox" name="gsom_write_debug_log" value="1" <?php echo $gsom_write_debug_log ? 'checked="checked"' : ''?> /><label style="float:left; line-height:22px; vertical-align:middle;" for="gsom_write_debug_log">Write Debug Log</label><br style="clear:both;" />                	
                </td>
                <td class="info">
                </td>                
            </tr>	            						
        </table>
    </div>
    <div id="gsom-page-form" class="gsom-tbl-wrapper" <?php echo $gsom_tab != 'gsom-page-form' ? 'style="display:none;"' : ''?>>
    <table class="form-table">    
        <tr>
            <th scope="row" class="gsom-sec-hdr">Submission Form</th>
            <td>
                <p style="margin-top: 0;">Form Title (Headline):</p><textarea type="text" rows="5" cols="60" name="gsom_form_title"><?php echo $gsom_form_title; ?></textarea>
                <p>Form Header:</p><textarea type="text" rows="5" cols="60" name="gsom_sform_header"><?php echo $gsom_sform_header; ?></textarea>
                <p>Form Footer:</p><textarea type="text" rows="5" cols="60" name="gsom_sform_footer"><?php echo $gsom_sform_footer; ?></textarea><br />                
                <h3>Form Fields</h3>
                <p style="font-weight: normal;"><b>Note:</b><br />If you add custom fields to the signup form, they will become available as variables for the use in your email notifications. If you rename the default form field First Name, be sure to replace the default variable <b>$gsom_fname_field</b> used in the confirmation email, welcome message and on action pages with the new variable associated with the renamed field. The new variable will be listed under Available Variables.                 
                </p>
                <div style="overflow:hidden;">
                    <div style="float:left; width:130px;">
                        <div style="display:block; float:none; overflow: hidden;"  id="gsom-ddbutton-placeholder"></div>
                        <div style="display:block; float:none; overflow: hidden;"  id="gsom-ddbutton-restore"></div>                        
                    </div>                
                    <div style="margin-left:130px;">
                        <ul id="gsom-fields-list">
                        </ul>
                    To re-order fields, drag a field up or down and drop it.                        
                    </div>
                    
                </div>
                <input type="hidden" id="gsom-json-serialized-form" name="gsom-json-serialized-form" value="" />
            </td>
            <td class="info">
	            <h4>Define Why People Should Subscribe</h4>
	            <p>If you don't give people a good reason (better yet, several good reasons) to subscribe, well... they won't subscribe. Even if they love your blog.</p>
	            <p>To get more subscribers faster, write a good headline for your signup form that sells visitors on subscribing. Give people a good reason to subscribe. In most cases it is a simple convenience of being notified about new articles on your blog. It may be also a promise of giving something of value that non-subscribers won't get. Whatever your incentive is, clearly define it either in the form header or footer.</p>            	
            </td>
        </tr>     
    </table>
    </div>
    <div id="gsom-page-templates" class="gsom-tbl-wrapper" <?php echo $gsom_tab != 'gsom-page-templates' ? 'style="display:none;"' : ''?>>
    <table class="form-table">
        <tr>
            <th scope="row" class="gsom-sec-hdr">Confirmation Email</th>
            <td>
				<p>Subject:</p><input type="text" size="60" name="gsom_email_confirmation_subj" value="<?php echo $gsom_email_confirmation_subj; ?>" />
				<p>Message:</p>                    
				<textarea type="text" rows="15" cols="60" name="gsom_email_confirmation_msg" ><?php echo $gsom_email_confirmation_msg; ?></textarea>
            </td>
            <td class="info">
				<h3>Available variables:</h3>
				<div class="gsom-varlist">
				</div>            	
            </td>
        </tr>
        <tr>
            <th scope="row" class="gsom-sec-hdr">Welcome Email</th>
            <td>
                <p>Subject:</p><input type="text" size="60" name="gsom_email_welcome_subj" value="<?php echo $gsom_email_welcome_subj; ?>" />
                <p>Message:</p><textarea type="text" rows="10" cols="60" name="gsom_email_welcome_msg"><?php echo $gsom_email_welcome_msg; ?></textarea>
            </td>
            <td class="info">
                <h3>Important!</h3>
                <p>The way you welcome people lays the foundation of your future relationships. You won't have another chance to make the first impression on the subscriber so you have to get the most out of your welcome message. A skilful message creates a good reputation and defines you as serious email marketer.<br /><br /> In this article you'll find <nobr><a target="_blank" href="http://www.glockeasymail.com/9-tips-for-writing-a-welcome-message/?ref=1878">9 Tips for Writing Your Welcome Message</a></nobr></p>            	
            </td>
        </tr> 
        <tr>
            <th scope="row" class="gsom-sec-hdr">Unsubscribe Email</th>
            <td>
                    <p>Subject:</p><input type="text" size="60" name="gsom_email_unsubscribe_subj" value="<?php echo $gsom_email_unsubscribe_subj; ?>" />
                    <p>Message:</p><textarea type="text" rows="10" cols="60" name="gsom_email_unsubscribe_msg"><?php echo $gsom_email_unsubscribe_msg; ?></textarea>
            </td>
            <td class="info">
                <h3>Important!</h3>
                <p>Contrary to popular belief among marketers, an unsubscribe request is not necessarily the end of a customer relationship. By executing a well-thought out, positive unsubscribe experience, you can extend your brand equity and keep the customer for years to come, even if that customer is no longer an email subscriber. And a well-designed unsubscribe process can provide highly valuable, actionable information that will make you a better marketer.</p>
                <p>In this article you'll find <a target="_blank" href="http://www.glockeasymail.com/3-tips-for-generating-profits-from-the-signup-process/?ref=1878">3 Tips for Generating Profits from the Signup Process</a></p>            	
            </td>
        </tr>    
    </table>
    </div>
    <div id="gsom-page-pages" class="gsom-tbl-wrapper" <?php echo $gsom_tab != 'gsom-page-pages' ? 'style="display:none;"' : ''?>>
    <table class="form-table">
        <tr>
            <th scope="row" class="gsom-sec-hdr">Action Pages</th>    
            <td>
                <div class="gsom-ap-hdr">Wrong email address format:</div><textarea type="text" rows="10" cols="60" name="gsom_msg_bad_email_address"><?php echo $gsom_msg_bad_email_address;?></textarea>
                <div class="gsom-ap-hdr">Confirmation required:</div><textarea type="text" rows="10" cols="60" name="gsom_msg_confirmation_required"><?php echo $gsom_msg_confirmation_required;?></textarea>                
                <div class="gsom-ap-hdr">Confirmation successful (Thank you page):</div><textarea type="text" rows="10" cols="60" name="gsom_msg_confirmation_succeed"><?php echo $gsom_msg_confirmation_succeed;?></textarea>                                                
                <div class="gsom-ap-hdr">Email address is already subscribed and confirmed:</div><textarea type="text" rows="10" cols="60" name="gsom_msg_email_subscribed_and_confirmed"><?php echo $gsom_msg_email_subscribed_and_confirmed;?></textarea>
                <div class="gsom-ap-hdr">Email address is already subscribed but not confirmed:</div><textarea type="text" rows="10" cols="60" name="gsom_msg_email_subscribed_but_not_confirmed"><?php echo $gsom_msg_email_subscribed_but_not_confirmed;?></textarea>
                <div class="gsom-ap-hdr">Unsubscription successful:</div><textarea type="text" rows="10" cols="60" name="gsom_msg_unsubscribe_succeed"><?php echo $gsom_msg_unsubscribe_succeed;?></textarea>
            </td>     
            <td class="info">
                <h3>Available variables:</h3>
                <div class="gsom-varlist">
                </div>            	       
            </td>
        </tr>
    </table>    
    </div>
<p class="submit">
<input id="gsom_tab_inp" type="hidden" name="gsomtab" value="gsom-page-general" />
<input type="submit" name="submit" value="<?php _e('Update Options','gsom-optin'); ?> &raquo;" />
</p>    
</form>
</div>
<script type="text/javascript">        
	   glock.event.observe(window,'load',function(){
	
	    window.gsom_bcst_number_of_posts = '<?php echo $gsom_bcst_number_of_posts;?>';
	    window.gsom_bcst_day_number = '<?php echo $gsom_bcst_day_number;?>';
	    window.gsom_bcst_when = '<?php echo $gsom_bcst_when;?>';
	    window.gsom_bcst_dom_post_limit = '<?php echo $gsom_bcst_dom_post_limit; ?>';
	    
	   	window.gsompathToScripts = '<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));?>/';    
	   	window.gsomAdminMenu = true;
	   	window.gsomPluginPath = '<?php echo $gsom_form_vars["wp_url"].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));?>/';
	window.gsomForm = <?php $s = (trim($gsom_form) != '') ? $gsom_form : $gsom_def_form; echo $s; ?>;   
	window.gsomdef_form = <?php  $s = (trim($gsom_def_form) != '') ? $gsom_def_form : '[]'; echo $s;?>;		
	       gsom.gsomInit();
	       gsom.adminBuildForm({arr:gsomForm});
	       glock.ui.makeSortable('gsom-fields-list',{handle: 'gsom-list-item-wrapper',onUpdate:gsom.SerializeForm, markDropZone:true, dropOnEmpty: true});
	});

   
</script>
<?php    
}

function gsom_rearrange_fields($json_form) {
    global $wpdb;
    global $gsom_table_subs;
    $jdform = gsom_json_decode($json_form,true);
    
    $const_fields = array('intId', 'dtTime', 'varIP', 'varEmail', 'intStatus', 'textCustomFields', 'varUCode');
    $current_fields_full = array();
    $current_fields = array();
    
    $new_form_fields = array();
    
    // get fields to rename     
    $rename_fields = array();    
	
    if (!is_array($jdform) || empty($jdform)) {
    	return;
    }
	
    foreach ($jdform as $item)
    {
       if (($item['name'] != 'gsomsubscribe') && ($item['name'] != 'gsom_email_field'))
       {
           $new_form_fields[] = $item['name'];
           $oldName = trim($item['oldName']); 
           if($oldName != '') {
            $rename_fields[] = array('oldName' => $oldName,
                                     'newName' => $item['name']);
           }
       }        
    } 
    
    // make table backup
    
    $sql = 'CREATE TABLE '.$gsom_table_subs.'_copy LIKE '.$gsom_table_subs;
    $wpdb->query($sql);  
    
    $sql_1 = $sql;

    $sql = 'INSERT INTO '.$gsom_table_subs.'_copy SELECT * FROM '.$gsom_table_subs;
    $wpdb->query($sql);
    
    $sql_1 .= $sql;
    
    $success = true;
    
    //rename query
    $ren_q_count = count($rename_fields);
    if ($ren_q_count > 0) {
        $sql = 'ALTER TABLE '.$gsom_table_subs;
        
        for($i=0; $i < count($rename_fields); $i++) {
            if($i == 0) {
                $delim = ' ';            
            } else {
                $delim = ', ';
            }
            $sql .= $delim.'CHANGE COLUMN '.$rename_fields[$i]['oldName'].' '.$rename_fields[$i]['newName'].' TEXT';                
        }       
        
        $sql_2 = $sql;
        
        if($wpdb->query($sql) === false) {
            $success = false;
        }
    }
    
    // get current fields
    $sql = 'describe '.$gsom_table_subs;
    $result = $wpdb->get_results($sql,ARRAY_A);
    
    if ((!is_array($result)) || (empty($result))) {
        return false;
    }
    
    foreach($result as $row) {        
        $current_fields_full[] = $row['Field'];
    }     
    
    $current_fields = array_diff($current_fields_full, $const_fields);    
       
    $fields_to_delete = array_diff($current_fields, $new_form_fields);
    $fields_to_add = array_diff($new_form_fields, $current_fields);
    
    $ftd_count = count($fields_to_delete);
    if ($ftd_count > 0) {
        $sql = 'ALTER TABLE '.$gsom_table_subs;
        
        $ftime = true;
        foreach($fields_to_delete as $item) {
            if ($ftime) {
                $delim = ' ';
                $ftime = false;
            } else {
                $delim = ', ';
            }
            $sql .= $delim.'DROP COLUMN '.$item;            
        }        
        
        $sql_3 = $sql;
        
        if($wpdb->query($sql) === false) {
            $success = false;
        }
    }    
    
    $fta_count = count($fields_to_add);
    if ($fta_count > 0) {
        $sql = 'ALTER TABLE '.$gsom_table_subs;
        
        $ftime = true;
        foreach($fields_to_add as $item) {
            if ($ftime) {
                $delim = ' ';
                $ftime = false;
            } else {
                $delim = ', ';
            }
            $sql .= $delim.'ADD COLUMN '.$item.' TEXT';
        }        
                
        $sql_4 = $sql;
          
        if($wpdb->query($sql) === false) {
            $success = false;
        }
    }     
    
    if ($success) {
        $sql = 'DROP TABLE IF EXISTS '.$gsom_table_subs.'_copy';
        $wpdb->query($sql);
    }
    
    
}

function gsom_get_form_field_names($customForm) {
    $res = array(); 
    $jdform = gsom_json_decode($customForm,true);        
    foreach($jdform as $item) {   
        if ((trim($item['name']) != 'gsomsubscribe') && 
            (trim($item['name']) != 'gsom_email_field')) {
            $res[] = $item['name'];
        }
    }    
    return $res;
}

function gsom_mod_remslashes ( $string ){       
// all wordress $_POST ,$_GET and $_COOKIE variables
// are compulsory backslashed!
    return stripslashes( $string );
}

function gsom_manager()
{
	global $gsom_form_vars;
    ?>
<link type="text/css" rel="stylesheet" href="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/css/gsom.css?ver='.GSOM_VERSION;?>" />        
<script src="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/js/gsom.min.js?ver='.GSOM_VERSION;?>"></script>
<!-- proto-->
<div class="wrap">
<div style="border-bottom: 1px solid #DADADA; overflow: hidden;">
    <h2 style="border:none; float: left; padding:0; margin: 10px;">Manage Subscribers</h2>
    
    <div style="float:right; margin: 10px; line-height: 28px;">
        <label style="padding: 0px 5px; line-height: 28px; vertical-align:middle; float:left;" for="post-search-input">Email:</label>
            <input id="gsom-search-input" name="s" value="" type="text">
            <button class="button" style="margin:3px 3px 0 3px;" id="gsom-search-btn">Search Subscribers</button>            
        <button id="gsom-btn-export" style="margin:3px 3px 0 3px;" class="button">Export to <img src="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));?>/img/darrow.gif"></button>            
    </div>    
</div>

<div style="overflow:hidden;">

<ul class="subsubsub" style="float:left; margin-right: 50px;">
<li><a id="gsom-mgr-stype-all" class="gsom-flink current">All Subscribers</a> |</li>
<li><a id="gsom-mgr-stype-confirmed" class="gsom-flink">Confirmed</a> |</li>
<li><a id="gsom-mgr-stype-unconfirmed" class="gsom-flink">Unconfirmed</a> |</li>
<li><a id="gsom-mgr-stype-unsubscribed" class="gsom-flink">Unsubscribed</a></li>
</ul>
<button style="float:right; display:none;" class="button subsubsub" id="gsom-search-cancel-btn">Remove Search Filter</button>
</div>

<div class="tablenav" style="height: 23px; line-height:23px;">
    <div class="tablenav-pages" style="height: 23px; line-height:23px; padding: 0;">
    </div>
    <div class="alignleft">
        <button id="gsom-btn-unsubscribe" style="margin: 0 3px;" type="button" class="button-secondary">Unsubscribe</button>
        <button id="gsom-btn-delete" style="margin: 0 3px;" type="button" class="button-secondary">Delete</button>
    </div>

<br class="clear">
</div>

<br class="clear">


<table id="gsom-mgr-subscribers" class="widefat">
  <thead>
  <tr>
    <th scope="col" class="check-column"><input id="gsom-cbx-main" type="checkbox"></th>
    <th scope="col">Email</th>
    <th scope="col">Date</th>
    <th scope="col">IP Address</th>
    <th scope="col">Status</th>
    <th scope="col">Form Data</th>
  </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<div class="tablenav" style="height: 23px; line-height:23px;">
    <div class="tablenav-pages" style="height: 23px; line-height:23px; padding: 0;">
    </div>
</div>

<br class="clear" />

<div style="background: #EAF3FA; padding: 5px 20px 10px 20px; margin: 5px 0;">
<h4>Send email to your WP subscribers without exporting them to a list</h4>
You can <a href="http://www.glockeasymail.com/tutorials/import-subscribers-wordpress-database-glock-easymail/?ref=1878">connect directly to your WordPress subscribers from G-Lock EasyMail 6 address book</a> and send an personalized email to them without the need to export-import the list
</div>

<div style="background: #EAF3FA; padding: 5px 20px 10px 20px;">
<h4>Unsubscribe Link for Your Newsletter</h4>

To let the users unsubscribe from your list, you can use the unsubscribe link below. Just copy the link and paste it at the bottom of your email:

<br />
<br />
<?php echo gsom_GetUnsubscribeLink(''); ?><br />
<br />

<b>%%varUCode%%</b> - variable that stores a unique unsubscribe code for each user. You can find this code in the <b>varUCode</b> field after you export your list.

You can merge the varUCode field into the unsubscribe link and use it in your <a href="http://www.glockeasymail.com/?ref=1878">email marketing program</a> as shown above.

</div>


</div>


<script type="text/javascript">
	glock.event.observe(window,'load',function(){
		window.gsompathToScripts = '<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));?>/';
	    gsom.initManager();
	});
</script>
    <?php    
}

function gsom_debugger()
{
	global $gsom_form_vars;
	global $wpdb;
	global $gsom_table_log;
	
	$sql = 'select * from '.$gsom_table_log;
	
	$log = '';
	
    $results = $wpdb->get_results($sql,ARRAY_A);
    if ($results) {
        foreach($results as $item) {		        	
        	$log .= $item['varMessage']."\n";
        }		 
    } else {
    	 $log = $wpdb->last_error;
    }
    ?>
<link type="text/css" rel="stylesheet" href="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));?>/css/gsom.css" />        
<!-- proto-->
<div class="wrap">
<div style="border-bottom: 1px solid #DADADA; overflow: hidden;">
    <h2 style="border:none; float: left; padding:0; margin: 10px;">Debug Log</h2>
</div>
<textarea style="width: 100%; height: 500px;">
<?php echo $log; ?>
</textarea>


<br class="clear">
    <?php    
}

function gsom_get_authors($selected = '') {
	global $wpdb;
	$sql = "SELECT ID, user_nicename from $wpdb->users ORDER BY display_name";	
	$authors = $wpdb->get_results($sql,ARRAY_A);
	return $authors;
}

function gsom_broadcast_settings() {
	global $gsom_form_vars;
	global $wpdb;	
	
	
	$gsom_bcst_when = get_option('gsom_bcst_when');
	$gsom_bcst_dom_post_limit = get_option('gsom_bcst_dom_post_limit');
	$gsom_bcst_number_of_posts = get_option('gsom_bcst_number_of_posts');
	$gsom_bcst_day_number = get_option('gsom_bcst_day_number');
		
	$gsom_bcst_send = get_option('gsom_bcst_send');
		
	$gsom_bcst_send_date = '';
	$gsom_bcst_send_number = '';
	$gsom_bcst_send_manually = '';   	
	
	$gsom_feed_limit = get_option('gsom_feed_limit');
	
	$bcsttime = get_option('gsom_last_broadcast');
	
	$gsom_show_full_posts = get_option('gsom_show_full_posts');
	
	$gsom_bcst_sel_cat = get_option('gsom_bcst_sel_cat');
	$gsom_bcst_sel_auth = get_option('gsom_bcst_sel_auth');
	$gsom_rss_excerpt_length = get_option('gsom_rss_excerpt_length');
	
	$gsom_bcst_email_subj = get_option('gsom_bcst_email_subj');
	$gsom_bcst_email_msg = get_option('gsom_bcst_email_msg');
	$gsom_bcst_email_msg_plain = get_option('gsom_bcst_email_msg_plain');
	
	$gsom_filter_images = get_option('gsom_filter_images');
	
	
	// prepare interface output
	
	$sel_cat_arr = preg_split('/[\s,]+/',$gsom_bcst_sel_cat,-1,PREG_SPLIT_NO_EMPTY);	
	$sel_auth_arr = preg_split('/[\s,]+/',$gsom_bcst_sel_auth,-1,PREG_SPLIT_NO_EMPTY);
	
	$categories =  get_categories();
	
	$authors = gsom_get_authors();
	

	$cats = array();
	
	foreach ($categories as $item) {
		
		$a = array('text' =>$item->name, 'value' => $item->cat_ID);
		
		if (in_array($item->cat_ID, $sel_cat_arr)) {
			$a['checked'] = true;
		}
		
		$cats[] = $a;
	}
	
	$jcats = gsom_json_encode($cats);
	
	$auths = array();
	
	foreach ($authors as $item) {
		$a = array('text' => $item['user_nicename'], 'value' => $item['ID']);
		
		if (in_array($item['ID'], $sel_auth_arr)) {
			$a['checked'] = true;
		}
		
		$auths[] = $a;
	}
	
	$jauths = gsom_json_encode($auths);	
   	
    switch (strtolower($gsom_bcst_send)) {
        case 'date':
            $gsom_bcst_send_date = ' checked="checked" ';
        break;
        case 'number':
            $gsom_bcst_send_number = ' checked="checked" ';
        break;
        case 'manually':
        	$gsom_bcst_send_manually = ' checked="checked"';
        break;
    }      	   	
    

	$bcst_locked = '';
	if (gsom_broadcastLocked()) {
		$bcst_locked = 'disabled="disabled"';		
		$bstatus = '<span id="bcst_status" class="bcst_status_running">broadcasting</span>';
	} else {		
		$bstatus = '<span id="bcst_status" class="bcst_status_idle">stand by</span>';				
	}   
	
    $lastbcst = '<p style="font-size:14px;">Broadcast status: '.$bstatus.'. <button id="gsom_run_broadcast_now" '.$bcst_locked.' class="button-primary">Send Broadcast Now</button></p>';
    
    
    $gsom_show_full_posts = $gsom_show_full_posts == '1' ? ' checked="checked" ' : '';
    
	//unf. dynamic variables
	
	 $pFields = gsom_get_form_field_names(get_option('gsom_form'));
	 
	 for($i=0; $i < count($pFields); $i++) {
	 	$pFields[$i] = '$'.$pFields[$i];
	 }
	 
	 $pFields = array_merge(array('$blog_name',
							'$blog_url',
							'$from_email',
							'$from_name',
							'$manage_subscription_link',
							'$confirmation_link',
							'$resend_confirmation_link',
							'$encoded_email',
							'$decoded_email',
							'$subscription_time',
							'$subscriber_ip'), $pFields);
    
   	
    ?>
<link type="text/css" rel="stylesheet" href="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/css/gsom.css?ver='.GSOM_VERSION; ?>" />        
<link type="text/css" rel="stylesheet" href="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/css/gsom_admin.css?ver='.GSOM_VERSION;?>" />
<link type="text/css" rel="stylesheet" href="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/js/css/glock.ui.calendar.css?ver='.GSOM_VERSION;?>" />
<link type="text/css" rel="stylesheet" href="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/js/css/glock.ui.ddcalendar.css?ver='.GSOM_VERSION;?>" />
<link type="text/css" rel="stylesheet" href="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/js/css/glock.ui.CheckboxDropdownList.css?ver='.GSOM_VERSION;?>" />
<script src="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/js/glock.ui.min.js?ver='.GSOM_VERSION;?>"></script>
<script src="<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/js/gsom.min.js?ver='.GSOM_VERSION;?>"></script>
<!-- proto-->
<div class="wrap">
<div style="border-bottom: 1px solid #DADADA; overflow: hidden;">
    <h2 style="border:none; float: left; padding:0; margin: 10px;">Blog Broadcast Settings</h2>
</div>

<div id="gsom_updo_notification" class="gsom-upd-message" style="clear: both; display: none;">
	<p>Options <strong>updated</strong>.</p>		
</div>	

<p>If your blog is already up and running and you add new posts frequently, it's important to think about how often you will send your email newsletter and how much content you will send in each email. You don't want to overload subscribers, but you want to send enough information to make them open and read your email.</p>
<?php echo $lastbcst; ?>
<form name="gsom_form" id="gsom_form" method="post" action="">
<ul class="gsom-tabs" id="gsom-tabs">
    <li><a href="#general"><?php _e('General','gsom-optin'); ?></a></li>
    <li><a href="#template">Broadcast Template</a></li>
</ul>
<div id="poststuff">
<div id="gsom-pages">
    <div id="gsom-page-general" class="gsom-tbl-wrapper">
    <table class="form-table">
        <tr>
        	<th scope="row" class="gsom-sec-hdr">Sending Options</th>
        	<td>
        		<div>
                	<input name="gsom_bcst_send" <?php echo $gsom_bcst_send_manually;?> value="manually" type="radio"  style="clear:both;" class="gsom_bnone gsom-radio-small">
                	<span style="margin:0 5px;float:left;">I'll send broadcast manually</span>
                </div>
                	
            	<div>
	                <input name="gsom_bcst_send" <?php echo $gsom_bcst_send_number;?> value="number" type="radio" style="clear:both;" class="gsom_bnone gsom-radio-small"><span style="margin:0 5px;float:left;">Send broadcast email when the number of new posts is at least</span>
	                <select id="gsom_bcst_number_of_posts" name="gsom_bcst_number_of_posts" class="gsom-select-small">
	                </select>            		
            	</div>                        		

                <div style="overflow:hidden; clear:both;">
	                <input name="gsom_bcst_send" <?php echo $gsom_bcst_send_date;?> value="date" type="radio"  style="clear:both;" class="gsom_bnone gsom-radio-small">
	                
	                <span style="margin:0 5px;float:left;">Send broadcast email each</span>
	                <select id="gsom_bcst_day_number" name="gsom_bcst_day_number" class="gsom-select-small">
	                </select>                            
					<select id="gsom_bcst_when" name="gsom_bcst_when" class="gsom-select-small">
	                </select>
                </div>
        	</td>
        	<td class="info">
        	</td>        
        </tr>    	
        <tr>
            <th scope="row" class="gsom-sec-hdr">Feed Options<br />
            <td>                
				<input class="gsom_bnone gsom-opt-cbox" type="checkbox" id="gsom_show_full_posts" name="gsom_show_full_posts" value="1" <?php echo $gsom_show_full_posts ? 'checked="checked"' : ''?> /><label style="float:left; line-height:22px; vertical-align:middle;">Include full posts into broadcast email</label><br style="clear:both;" />
				<input class="gsom_bnone gsom-opt-cbox" type="checkbox" id="gsom_filter_images" name="gsom_filter_images" value="1" <?php echo $gsom_filter_images ? 'checked="checked"' : ''?> /><label style="float:left; line-height:22px; vertical-align:middle;">Don't show images in broadcast emails</label><br style="clear:both;" />
				
				
				<div id="gsom_excerpt_block" style="display:none;"><span>Length of post excerpt </span><input type="text" style="width:40px;" value="<?php echo $gsom_rss_excerpt_length; ?>" name="gsom_rss_excerpt_length" id="gsom_rss_excerpt_length" /><span> characters.</span></div>
				<label>Limit feed output to </label><input id="gsom_feed_limit" style="width: 30px;" type="text" name="gsom_feed_limit" value="<?php echo $gsom_feed_limit; ?>" /> items<br style="clear:both;" />						
				<label style="float:left; margin: 0 5px 5px 5px;">Next broadcast will send posts from</label><div id="gsom-bc-start-cal" name="gsom_last_broadcast"></div>
				
				<div id="gsom-bcst-posts-pv" style="width: 530px;">
					<div class="gsom-bcst-topbar">
						<div style="clear:both;"><label style="float: left; width: 70px;">Categories:&nbsp;</label><div id="gsom-bcst-sel-cat" name="gsom_bcst_sel_cat"></div></div>
						<div style="clear:both;"><label style="float: left; width: 70px;">Authors:&nbsp;</label><div id="gsom-bcst-sel-auth" name="gsom_bcst_sel_auth"></div></div>
					</div>
					<div id="gsom-bcst-posts">
				    </div>
				</div>
            </td>  
            <td class="info">
            	<br /><br /><br /><br /><br /><br /><br />
            	&larr; These are the posts that will be included in your next broadcast.
            </td>
        </tr>
    </table>
    </div>
    <div id="gsom-page-template" class="gsom-tbl-wrapper" style="display:none;">
    <table class="form-table">
        <tr>
            <th scope="row" class="gsom-sec-hdr">Message Template<br /><br /><button id="gsom_bcst_loadtemplates_btn" class="button" type="button">Load Default Template</button>
            <td>                
				<p style="margin-top: 0px;">Subject:</p><input type="text" id="gsom_bcst_email_subj" name="gsom_bcst_email_subj" value="<?php echo $gsom_bcst_email_subj; ?>" />
				<p>HTML Message:</p>   
				<textarea type="text" rows="15" id="gsom_bcst_email_msg" name="gsom_bcst_email_msg" ><?php echo stripslashes($gsom_bcst_email_msg); ?></textarea>				

				<div style="background: #FFFFFF;">                    
				    <p>Plain Text Message:</p>                    
				    <textarea type="text" rows="15" id="gsom_bcst_email_msg_plain" name="gsom_bcst_email_msg_plain" ><?php echo $gsom_bcst_email_msg_plain; ?></textarea>                    
				</div>  
				<fieldset style="border: 1px solid #C6D9E9; float:left; margin: 10px 0 0 0; padding: 3px 0 3px 3px;">
				    <legend>Test broadcast</legend>
				    <p style="font-size:x-small; margin: 0 5px 10px 5px;">Note: a test  broadcast email will include 10 latest posts from your blog.</p>
				    <label style="margin: 0px 5px;" for="gsom_bcst_test_email">Test Email:</label>
				    <input style="width:410px;" id="gsom_bcst_test_email" name="gsom_bcst_test_email" type="text" /><br />
				    <button style="margin: 5px 0 0 72px; float:left;" type="button" id="gsom_bcst_test_btn" name="gsom_bcst_test_btn" class="button">Send Test Email</button>
				    <div id="bcst_test_status" style="width:300px; margin: 0 5px; float:left;"></div>
				</fieldset>
            </td>    
            <td class="info">
                    <p style="font-weight:bold">2 tips for your email newsletter:</p>
                    <p>#1: Do not make your newsletter look like heaps of links. Add some value.</p>
                    <p>#2: Send some additional exclusive content in your newsletter regularly - at least once a month.</p>            	
				<div class="gsom_variables">
				    <h3>Available variables:</h3>
				    <div id="gsom_varlist" class="gsom-varlist">
				    </div>
				    <h3>RSS variables:</h3>
				    <div>
				        <p>$rss_channel_title</p>
				        <p>$rss_channel_link</p>
				        <p>$rss_channel_description</p>
				        <p>$rss_itemblock - rss item open tag</p>
				        <p>/$rss_itemblock - rss item close tag</p>
				        <p>$last_rss_item_number</p>
				        <p>$last_rss_item_link</p>
				        <p>$last_rss_item_title</p>
				        <p>$last_rss_item_date</p>
				        <p>$last_rss_item_description</p>                                                        
				        <h4>Variables available only between itemblock open/close tags:</h4>                        
				            <p>$rss_item_number</p>				            
				            <p>$rss_item_link</p>
				            <p>$rss_item_title</p>
				            <p>$rss_item_date</p>
				            <p>$rss_item_description</p>                        
				            <p>$rss_item_author</p>
				    </div>
				</div>            	
            </td>                    
        </tr>
    </table>
    </div>
</div>
</div>
<p class="submit">
<input id="gsom_tab_inp" type="hidden" name="gsomtab" value="gsom-page-general" />
<input id="gsom_form_submit" type="button" name="submit" value="<?php _e('Update Options','gsom-optin'); ?> &raquo;" />
</p>    
</form>
</div>
<script>        
   glock.event.observe(document,'dom:load',function(){
   	window.gsompathToScripts = '<?php echo $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));?>/';    
   	window.gsomAdminMenu = true;
   	window.gsomPluginPath = '<?php echo $gsom_form_vars["wp_url"].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__));?>/';
   	
   	var gsom_bcst_when = '<?php echo $gsom_bcst_when; ?>',
   		gsom_bcst_dom_post_limit = '<?php echo $gsom_bcst_dom_post_limit; ?>',
   		gsom_bcst_number_of_posts = '<?php echo $gsom_bcst_number_of_posts; ?>',
   		gsom_bcst_day_number = '<?php echo $gsom_bcst_day_number; ?>',
   		
   		gsom_jcats = '<?php echo addslashes($jcats); ?>';   		
   		gsom_jcats = glock.json.parse(gsom_jcats);
   		
   		gsom_auths = '<?php echo addslashes($jauths); ?>';   		
   		gsom_auths = glock.json.parse(gsom_auths);   		
   		
   		window.gsom_fields =  glock.json.parse('<?php echo gsom_json_encode($pFields); ?>');
   		
   		gsom_last_bcst = "<?php echo date('Y-m-d H:i:s',$bcsttime); ?>";
   		
   	
//   	glock.xA(glock.x('gsom-tabs').select('li')).each(function(el){
//   		console.log(el.down('a').href);
//   	});	
    function gsomhidetabs(leavetab){
    	var rx = new RegExp("#"+leavetab); 
    	var ahref = '';
    	glock.xA(glock.x('gsom-tabs').select('li')).each(function(li){
    		ahref = glock.x(li).down('a').href;
    		if (rx.test(ahref)) {
    			glock.x(li).addClassName('gsom-tabs-selected');
    		} else {
    			glock.x(li).removeClassName('gsom-tabs-selected');
    		}
    	});
    	
    	glock.xA(glock.x('gsom-pages').select('div.gsom-tbl-wrapper')).each(function(pg){
    		if (pg.id == 'gsom-page-'+leavetab) {
    			glock.x(pg).show();
    		} else {    			
    			glock.x(pg).hide();
			}
    	});     	    	
    }    
//*   	
   	glock.history.registerScreen('general',function(){   		   		
   		gsomhidetabs('general');   		
   	},true);
   	
   	glock.history.registerScreen('template',function(){   		
   		gsomhidetabs('template');
   	}); 
   	glock.history.init();  	
//*/   	
	
	gsom.InitBroadcast(gsom_bcst_when, gsom_bcst_dom_post_limit, gsom_bcst_number_of_posts, gsom_bcst_day_number, gsom_jcats);
   });

   
</script>
    <?php    	
}


function gsom_add_menu_item() {
/*	
	if (function_exists('add_menu_page')) {
		add_menu_page(__('G-Lock Soft', 'gsom'), __('G-Lock Soft', 'gsom'), 7, __FILE__);
	}
	elseif (function_exists('add_management_page')) {
		add_management_page(__('G-Lock Soft', 'gsom'), __('G-Lock Soft', 'gsom'), 7, __FILE__);
	}	
    add_submenu_page(__FILE__, 'Double Opt-in Manager',' Settings', 7, __FILE__, 'gsom_options' );
    add_submenu_page(__FILE__, 'Manage Subscribers','Subscribers', 7, __FILE__, 'gsom_manager'); 
//*/	


	add_menu_page(
		__('Newsletter', 'gsom-optin'), 
		__('Newsletter', 'gsom-optin'), 
		GSOM_ADMIN_READ_WRITE, 
		'gsom-optin', 
		'gsom_options',
		gsom_plugin_url.'/img/glock.png'		
	);
	
	add_submenu_page(
		'gsom-optin',
		__('Settings', 'gsom-optin'),
		__('Settings', 'gsom-optin'),
		GSOM_ADMIN_READ_WRITE,
		'gsom-optin',
		'gsom_options'
	);
	
	add_submenu_page(
		'gsom-optin',
		__('Subscribers', 'gsom-optin'),
		__('Subscribers', 'gsom-optin'), 
		GSOM_ADMIN_READ_WRITE, 
		'gsom-subscribers', 
		'gsom_manager'
	);
	
	if (get_option('gsom_write_debug_log')== '1') {
		add_submenu_page(
			'gsom-optin',
			__('Debugger', 'gsom-optin'),
			__('Debugger', 'gsom-optin'), 
			GSOM_ADMIN_READ_WRITE, 
			'gsom-debugger', 
			'gsom_debugger'
		);		
	}
	
	add_submenu_page(
		'gsom-optin',
		__('Blog Broadcast', 'gsom-optin'),
		__('Blog Broadcast', 'gsom-optin'),
		GSOM_ADMIN_READ_WRITE,
		'gsom-broadcast',
		'gsom_broadcast_settings'
	);
	

/*	
    add_options_page('G-Lock Soft WP Opt-in Manager Options', 'G-Lock Double Opt-in Manager', 7, __FILE__, 'gsom_options' );
    add_submenu_page('users.php', 'Manage Subscribers','Subscribers', 7, __FILE__, 'gsom_manager'); 
//*/
}

function gsom_init() {    
    global $wp;
    
    require_once('fakepage.php');    
    
    global $gsom_def_form;
    global $gsom_form;
    global $gsom_form_vars;
    
    global $gsom_db_version;
    
    global $gsom_plugindir;
	
/*    
    echo '<pre>';
    echo print_r($_POST,true);
    echo '</pre>';   
    
//*/    
	if (get_option('gsom_transform') == '1') {
		gsom_transformdb();
	}


    $gsom_plugindir = dirname(plugin_basename(__FILE__));
    
    load_plugin_textdomain( 'gsom-optin', null, $gsom_plugindir.'/languages' );
    
    if (trim($gsom_def_form)=='')
      $gsom_def_form = get_option('gsom_def_form');           
      
    if (trim($gsom_form) == '')
      $gsom_form = get_option('gsom_form');
      
    if (trim($gsom_form) == '')
        $gsom_form = $gsom_def_form;
      
    $jdform = gsom_json_decode($gsom_form,true);
    
    // if subscription form submitted
    $form_custom = '';
    
    $gsom_send_event_notif = get_option('gsom_send_event_notif');
	
    
    if (isset($_REQUEST['gsom-chsub']))
    {   
	    extract(gsom_RequestParamsPresent('u'));
		$email = gsom_get_email_by_ucode($u);    	
		gsom_init_gvars_with_email($email,$u);

		gsom_EmailValid($email, true);
        $newemail = strtolower(gsom_mod_remslashes($_POST['gsom-chsub-newemail']));
		gsom_EmailValid($newemail, true);
				
        if (gsom_changeEmail($email,$newemail))
        {            
                $gsom_email_address_changed_msg = get_option('gsom_email_address_changed_msg');
                $gsom_email_address_changed_subj = get_option('gsom_email_address_changed_subj');
                gsom_FillVarsWithCustomFormData($newemail);
                gsom_Mail($gsom_email_address_changed_subj, $gsom_email_address_changed_msg);
                
                gsom_redirect( gsom_BuildLink('subscription-changed') );                            
        }
    }    
    
    if (isset($_REQUEST['gsom-unsubscribe']))
    {
	    extract(gsom_RequestParamsPresent('u'));
		$email = gsom_get_email_by_ucode($u);    	
		gsom_init_gvars_with_email($email,$u);		
		
		$gsom_form_vars['gsom_email_field'] = $email;
		
        if(gsom_unsubscribe($email))
        {
            gsom_FillVarsWithCustomFormData($email);
            
            $sendUnsubscribe = get_option('gsom_send_unsubscibed_message');
            if ($sendUnsubscribe == '1')
            {                        
                $gsom_email_unsubscribe_subj = get_option('gsom_email_unsubscribe_subj');
                $gsom_email_unsubscribe_msg = get_option('gsom_email_unsubscribe_msg');
                gsom_Mail($gsom_email_unsubscribe_subj,$gsom_email_unsubscribe_msg);            
            }
            
            if ($gsom_send_event_notif == '1'){
                //mailing to admin
				$gsom_form_vars['u_reason'] = gsom_mod_remslashes($_REQUEST['u_reason']);
								
                $gsom_email_unsubscribtion_event_msg = get_option('gsom_email_unsubscribtion_event_msg');
                $gsom_email_unsubscribtion_event_subj = get_option('gsom_email_unsubscribtion_event_subj');                
                gsom_Mail($gsom_email_unsubscribtion_event_subj, $gsom_email_unsubscribtion_event_msg, true);
            }
            
            gsom_redirect( gsom_BuildLink('unsubscribe-successful',array('u'=> $u)) );        
        }
    }
    
    if (isset($_POST['gsomsubscribe']) || isset($_POST['gsom-subscribe']))
    {
        $formSnapshot = array();
        $form_email = strtolower(gsom_mod_remslashes($_POST['gsom_email_field']));
        
        gsom_EmailValid($form_email,true);
        
        gsom_log('SUBSCRIPTION TRAP');
        
        
        foreach ($jdform as $item)
        {
	        if (($item['name'] != 'gsomsubscribe') && ($item['name'] != 'gsom-subscribe') && ($item['name'] != 'gsom_email_field')) {
        	
	            if (isset($_POST[$item['name']])) {
	                    $formSnapshot[] = array(
	                        'name' => $item['name'],
	                        'value' => $_POST[$item['name']],
	                        'label' => $item['label']
	                    );                                    
	                
	                $gsom_form_vars[$item['name']] = $_POST[$item['name']];
	            } else {
	                $formSnapshot[] = array(
	                    'name' => $item['name'],
	                    'value' => '0',
	                    'label' => $item['label']
	                );
	                $gsom_form_vars[$item['name']] = '0';
	            }
	        }
            
        }      
        
        
        
        $res = gsom_SubscriberPresent($form_email);
        
        gsom_log('Subscriber present : '.print_r($res,true));
        
        if ($res === false) {
        	$asres = gsom_AddSubscription($form_email,$formSnapshot);
        	gsom_log('Add subscription : '.print_r($asres,true));
        	gsom_log('$form_email: '.$form_email);
            if ($asres){ 
                // mailing to subscriber
                $gsom_email_confirmation_subj = get_option('gsom_email_confirmation_subj');
                $gsom_email_confirmation_msg = get_option('gsom_email_confirmation_msg');                
                gsom_FillVarsWithCustomFormData($form_email);
                gsom_Mail($gsom_email_confirmation_subj, $gsom_email_confirmation_msg);                                
														
				$u = gsom_get_ucode_by_email($form_email);                
				//gsom_init_gvars_with_email($form_email,$u, false);
                
                gsom_redirect( gsom_BuildLink('confirmation-required',array('u' => $u)) );        
            } 
        } 
        else
        {
            switch ($res)
            {
                case GSOM_SS_SUBSCRIBED:
                    // subscribed but not confirmed
					
                    $u = gsom_get_ucode_by_email($form_email);                    
					gsom_init_gvars_with_email($form_email,$u, false);					
										
                    gsom_redirect( gsom_BuildLink('already-subscribed',array( 'u' => $u)));                    
                break;                
                case GSOM_SS_CONFIRMED:
                    // subscribed and confirmed
                    gsom_log('GSOM_SS_CONFIRMED');
                    $u = gsom_get_ucode_by_email($form_email);
                    gsom_log('eml: '.$form_email.' u='.print_r($u,true));
                    gsom_redirect( gsom_BuildLink('already-confirmed', array('u' => $u)) );                    
                break;
                case GSOM_SS_UNSUBSCRIBED:
                    // unsubscribed
                    gsom_setUnconfirmed($form_email);
                    $gsom_email_confirmation_subj = get_option('gsom_email_confirmation_subj');
                    $gsom_email_confirmation_msg = get_option('gsom_email_confirmation_msg');                
                    gsom_FillVarsWithCustomFormData($form_email);
                    gsom_Mail($gsom_email_confirmation_subj,$gsom_email_confirmation_msg);
                    $u = gsom_get_ucode_by_email($form_email);
                    gsom_redirect( gsom_BuildLink('confirmation-required',array('u' => $u)) );        
                break;
            }
        }              
    }              
    
		
    if (function_exists('wp_enqueue_script')) {
        //wp_register_script('my-prototype', $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) . '/js/prototype.js', array(), '1.6.0.1');
        //wp_enqueue_script('my-prototype');
        //wp_register_script('my-scriptaculous', $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) . '/js/scriptaculous.js', array(), '1.8.1');
        //wp_enqueue_script('my-scriptaculous');    
        
        if (defined('WP_ADMIN')) {
	        wp_register_script('glock', $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) . '/js/glock2.min.js', array(), $gsom_db_version);
	        wp_enqueue_script('glock');        
	        
	        wp_register_script('gsom_s', $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) . '/js/gsom_s.min.js', array(), $gsom_db_version);
	        wp_enqueue_script('gsom_s');  	        
	        
    	}
		
		if ((stripos($_SERVER['REQUEST_URI'],'gsoms-ubscribers')!== false) ||
		   (stripos($_SERVER['REQUEST_URI'],'glsft-optin.php')!== false)) {			            
						
			if (defined('WP_ADMIN')) {
				wp_register_script('glock.ui', $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) . '/js/glock.ui.min.js', array(), $gsom_db_version);
	    	    wp_enqueue_script('glock.ui');      			
			}		
			
			if (defined('WP_ADMIN')) {
		        wp_register_script('gsom', $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) . '/js/gsom.min.js', array(), $gsom_db_version);
	    	    wp_enqueue_script('gsom');      			
			}			
			
			
			// if (defined('WP_ADMIN')) {
			// 	    	    wp_enqueue_script('prototype');      			
			// 	wp_enqueue_script('scriptaculous');      			
			// 	wp_enqueue_script('jquery');      			
			// }						
		}
		

}               

    gsom_plugin_loaded();
    
    if (stripos($_SERVER['REQUEST_URI'],'manage-subscription')!== false)
    {
	    extract(gsom_RequestParamsPresent('u'));
		$email = gsom_get_email_by_ucode($u);    	
		gsom_init_gvars_with_email($email,$u);
		// ---
		if (get_option('gsom_send_event_notif') == '0') {
			$gsom_form_vars['gsom_unsubscribe_reason_form'] = '';
		}
		
        $msg = get_option('gsom_msg_change_subscription');
        $msg = gsom_replaceVars($msg);
        new FakePage('manage-subscription','Manage Subscription',$msg);
		
    }
    elseif (stripos($_SERVER['REQUEST_URI'],'confirmation-required')!== false)
    {        
	    extract(gsom_RequestParamsPresent('u'));
		$email = gsom_get_email_by_ucode($u);		    	
		gsom_init_gvars_with_email($email,$u);
    	
        $msg = get_option('gsom_msg_confirmation_required');
        $msg = gsom_replaceVars($msg);
        new FakePage('confirmation-required','Confirmation Required',$msg);
    }
	elseif (stripos($_SERVER['REQUEST_URI'],'resend-confirmation')!== false) {
	    extract(gsom_RequestParamsPresent('u'));
		$email = gsom_get_email_by_ucode($u);		    	
		gsom_init_gvars_with_email($email,$u);		    	
        
        gsom_FillVarsWithCustomFormData($email);        
        $gsom_email_confirmation_subj = get_option('gsom_email_confirmation_subj');
        $gsom_email_confirmation_msg = get_option('gsom_email_confirmation_msg');
        gsom_Mail($gsom_email_confirmation_subj,$gsom_email_confirmation_msg);        
        gsom_redirect( gsom_BuildLink('confirmation-required', array('u' => $u)) );        

	}
    elseif (stripos($_SERVER['REQUEST_URI'],'confirm-subscription')!== false)
    {
	    extract(gsom_RequestParamsPresent('u'));
		$email = gsom_get_email_by_ucode($u);    	
		gsom_init_gvars_with_email($email,$u);
		
		$gsom_form_vars['gsom_email_field'] = $email;
        
        $present = gsom_SubscriberPresent($email);
        
        if($present === GSOM_SS_SUBSCRIBED)
        {
            if (gsom_ConfirmSubscription($email))                
            {   
                gsom_FillVarsWithCustomFormData($email);
                
                $sendWelcome = get_option('gsom_send_welcome_message');
                if ($sendWelcome == '1')
                {                                        
                    $gsom_email_welcome_subj = get_option('gsom_email_welcome_subj');            
                    $gsom_email_welcome_msg = get_option('gsom_email_welcome_msg');            
                    gsom_Mail($gsom_email_welcome_subj,$gsom_email_welcome_msg);                                    
                }
                
                if ($gsom_send_event_notif == '1'){
                    //mailing to admin
                    $gsom_email_subscribtion_event_subj = get_option('gsom_email_subscribtion_event_subj');
                    $gsom_email_subscribtion_event_msg = get_option('gsom_email_subscribtion_event_msg');
                    gsom_Mail($gsom_email_subscribtion_event_subj, $gsom_email_subscribtion_event_msg, true);
                }                
                
                $msg = get_option('gsom_msg_confirmation_succeed');
                $msg = gsom_replaceVars($msg);            
            }
            else        
            {
                gsom_redirect( gsom_BuildLink('confirmation-failed') );
            }                        
        } elseif($present === GSOM_SS_CONFIRMED){
        	$u = gsom_get_ucode_by_email($email);
        	gsom_redirect( gsom_BuildLink('already-confirmed', array('u' => $u)) );
        } else {            
            gsom_redirect( gsom_BuildLink('confirmation-failed') );
        }
            
        new FakePage('confirm-subscription',__('Confirmation Successful','gsom-optin'),$msg);        
    }
    elseif (stripos($_SERVER['REQUEST_URI'],'already-confirmed')!== false)
    {
	    extract(gsom_RequestParamsPresent('u'));
	    gsom_log('already-confirmed. u='.$u);	    
	    
		$email = gsom_get_email_by_ucode($u);    	
		gsom_log('already-confirmed email = '.$email);
		gsom_init_gvars_with_email($email,$u);
		    	
        $msg = get_option('gsom_msg_email_subscribed_and_confirmed');        
        $msg = gsom_replaceVars($msg);
        new FakePage('already-confirmed',__('Already Confirmed','gsom-optin'),$msg);
    }
    elseif (stripos($_SERVER['REQUEST_URI'],'already-subscribed')!== false)
    {      		
	    extract(gsom_RequestParamsPresent('u'));
		$email = gsom_get_email_by_ucode($u);    	
		gsom_init_gvars_with_email($email,$u);
				
        $msg = get_option('gsom_msg_email_subscribed_but_not_confirmed');
        $msg = gsom_replaceVars($msg);
        new FakePage('already-subscribed',__('Already Subscribed','gsom-optin'),$msg);
    }
    elseif (stripos($_SERVER['REQUEST_URI'],'subscription-changed')!== false)
    {        
        new FakePage('subscription-changed',__('Subscription Changed','gsom-optin'),__('<h2>You subscription email was successfully changed.</h2>','gsom-optin') );
    }
    elseif (stripos($_SERVER['REQUEST_URI'],'unsubscribe-successful')!== false)
    {
        //unsubscribe-successful        
	    extract(gsom_RequestParamsPresent('u'));
		$email = gsom_get_email_by_ucode($u);    	
		gsom_init_gvars_with_email($email,$u);

        $msg = get_option('gsom_msg_unsubscribe_succeed');
        $msg = gsom_replaceVars($msg);
        new FakePage('unsubscribe-successful',__('Successfully Unsubscribed','gsom-optin'),$msg);        
    }
    elseif (stripos($_SERVER['REQUEST_URI'],'bad-email')!== false)
    {
        $msg = get_option('gsom_msg_bad_email_address');
        $msg = gsom_replaceVars($msg);
        new FakePage('bad-email',__('Bad Email Address','gsom-optin'),$msg);                        
    }
    elseif (stripos($_SERVER['REQUEST_URI'],'confirmation-failed')!== false)
    {        
        $msg = __('<h3>Your email address was not found in our database.</h3>','gsom-optin');
        new FakePage('confirmation-failed',__('Confirmation Failed','gsom-optin'),$msg);                        
    }
	elseif (stripos($_SERVER['REQUEST_URI'],'not-found')!== false)
	{
        $msg = __('<h3>Your email address was not found in our database.</h3>','gsom-optin');
        new FakePage('not-found',__('Email not found','gsom-optin'),$msg);    
	}
}

function gsom_get_email_by_ucode($ucode){
    global $wpdb;
    global $gsom_table_subs;      
      
    $sql = 'SELECT varEmail FROM '.$gsom_table_subs.' WHERE varUCode = %s';
   	$email = $wpdb->get_var($wpdb->prepare($sql, $ucode));		
	return $email;
}

function gsom_get_ucode_by_email($email) {
    global $wpdb;
    global $gsom_table_subs;      
      
    $sql = 'SELECT varUCode FROM '.$gsom_table_subs.' WHERE varEmail = %s';
   	$ucode = $wpdb->get_var($wpdb->prepare($sql, $email));		
	return $ucode;
	
}

function gsom_init_gvars_with_email($email, $u, $fillVars = true){
	global $gsom_form_vars;
	if(trim($email) == '') {
		gsom_redirect(gsom_BuildLink('not-found'));
	} else {
        $gsom_form_vars['manage_subscription_link'] = gsom_GetUnsubscribeLink('',$u);
        $gsom_form_vars['confirmation_link'] = gsom_GetConfirmationLink($email, $u);
        $gsom_form_vars['resend_confirmation_link'] = gsom_GetResendConfirmationLink($email, $u);    
        $gsom_form_vars['encoded_email'] = gsom_ModBase64Encode($email);
        $gsom_form_vars['decoded_email'] = $email;
		$gsom_form_vars['ucode'] = $u;
		if ($fillVars) {
			gsom_FillVarsWithCustomFormData($email);
		}
	}	
}

function gsom_RequestParamsPresent()
{    
    $array = array();
    $ret = array();
    
    for($i=0;$i < func_num_args(); $i++)    
        $array[] = func_get_arg($i);
    
    foreach ($array as $param)
    {
        if (!isset($_REQUEST[$param]))        
            $ret[$param] = '';        
        else        
            $ret[$param] = $_REQUEST[$param];        
    }
    return $ret;
}


function gsom_RequestPresent($param)
{
    if (!isset($_REQUEST[$param]))
    {
        if(func_num_args() == 2)
        {
            $def = func_get_arg(1);
            global ${$param};
            ${$param} = $def;
        }
        else
            ${$param} = '';
    }
    else
    {
        global ${$param};
        ${$param} = $_REQUEST[$param];
    }
}

function gsom_widget($args){
    
    if(is_array($args)){
          extract($args);
    }
          
/*    global $gsom_def_form;
    global $gsom_form;
    
    if (trim($gsom_def_form)=='')
      $gsom_def_form = get_option('gsom_def_form');           
      
    if (trim($gsom_form) == '')
      $gsom_form = get_option('gsom_form');
      
    $gsom_sform_header = get_option('gsom_sform_header');
    $gsom_sform_footer = get_option('gsom_sform_footer');          
  
  
  $gsom_form_title = get_option('gsom_form_title');
  if (trim($gsom_form) == '')
    $gsom_form = $gsom_def_form;     */         
    
    gsom_put_form(true,$before_title,$after_title,$before_widget,$after_widget);                    
}

function gsom_put_form($print = true)
{
	global $gsom_form_vars;
    $gsom_def_form = get_option('gsom_def_form');                 
    $gsom_form = get_option('gsom_form');      
    $gsom_sform_header = get_option('gsom_sform_header');
    $gsom_sform_footer = get_option('gsom_sform_footer');          
    $gsom_form_title = get_option('gsom_form_title');
    
    $rnd = ceil(microtime(true)*10)+rand(10,100);
    
    if (trim($gsom_form) == '')
      $gsom_form = $gsom_def_form;          
        
    if (func_num_args() >= 4)   
    {
        $before_title = func_get_arg(1);
        $after_title  = func_get_arg(2);
        $before_widget = func_get_arg(3);
        $after_widget = func_get_arg(4);          
    }
    else
    {
        $before_title = '<h2>';
        $after_title  = '</h2>';
        $before_widget = '';
        $after_widget = '';                
    }
    
    $data = '';
    $s = (trim($gsom_form) != '') ? $gsom_form : $gsom_def_form;
    
    $data .= $before_widget;
    $data .= $before_title
         . $gsom_form_title
         . $after_title . $gsom_sform_header;
                                                                   
    if(!$print)
    {
        $clsa_form = 'class="gsom-sa-from"';
        $clsa_placehldr = ' gsom-sa-placeholder';
    }
    else
    {
        $clsa_form = '';
        $clsa_placehldr = '';
    }
    
    $data .= '<script src="'.$gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) . '/js/glock2.min.js'.'"></script>';
    $data .= '<script src="'.$gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) . '/js/gsom_s.min.js'.'"></script>';
    
    $data .= '<form '.$clsa_form.' name="gsom-optin" action="'.$gsom_form_vars['blog_url'].'" method="post">
        <div class="gsom-optin-form'.$clsa_placehldr.'" id="gsom-optin-form-'.$rnd.'">                  
        </div>                
        </form>';
    
    $data .= $gsom_sform_footer;
    /*
     *  WP Double Opt-in Plugin has required a great deal of time and effort to develop. 
     *  If it's been useful to you then you can support this development by retaining the 
     *  link to www.glockeasymail.com. This will act as an incentive for us to carry on developing it,
     *  providing countless hours of support, and including any enhancements that are suggested.
     *  
     *  Since "Link love" is the only form of appreciation we ask from our plugin users, the best 
     *  if you simply leave the links in place.
     *  
     *  Alternatively, you can place links in some other place on your site (e.g. credits page,
     *  write a review post for this plugin on your blog, etc.). 
     *  If you don't give any credits to the developers, then support on our forum
     *  and via email may be refused.
     *
     *  G-Lock Software : 2008                             
     */
     
    $anc = base64_decode(get_option('gsom_form_xdata'));
     
    $data .= '<noscript><a href="http://www.glockeasymail.com/wordpress-email-newsletter-plugin-for-double-opt-in-subscription/">G-Lock opt-in manager</a> for <a href="http://www.glockeasymail.com">'.$anc.'</a></noscript>';        
    $data .= '<script type="text/javascript">  
		(function(){			
		function rungsom() {
       var gsomForm = '.$s.';';
    $data .= '
       gsomBuildForm({makeDivs: true, arr:gsomForm, place:"gsom-optin-form-'.$rnd.'"});    			
		}
      if (window.addEventListener) {
        window.addEventListener("load", rungsom, false);
      } else {
        window.attachEvent("onload", rungsom);
      }
	})();
    </script>';
    $data .= $after_widget;    
    
    if ($print) {
        echo $data;
    } else {
        return '<div class="gsom-sa-wrapper">'.$data.'</div>';
    }
}

function gsom_plugin_loaded(){    
    register_sidebar_widget('G-Lock Double Opt-in Manager', 'gsom_widget');        
}

function gsom_headMod(){
	
	global $gsom_form_vars;
    echo '<link type="text/css" rel="stylesheet" href="' . $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) . '/css/gsom.css" />' . "\n";
	echo '<!--[if IE]>
		  <link type="text/css" rel="stylesheet" href="' . $gsom_form_vars['wp_url'].PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/css/gsom-ie.css" />
		  <![endif]-->';
}

function gsom_peerip() {
    if (isset($_SERVER)) 
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } 
        elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } 
        else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
    } 
    else {
        if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
            $ip = getenv( 'HTTP_X_FORWARDED_FOR' );
        } 
        elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
            $ip = getenv( 'HTTP_CLIENT_IP' );
        } 
        else {
            $ip = getenv( 'REMOTE_ADDR' );
        }
    }
    return $ip;
}

function gsom_unsubscribe($email)
{
    global $wpdb;
    //global $gsom_table_subs;
    
    $gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
    
    $sql = 'UPDATE '.$gsom_table_subs.' SET intStatus = '.GSOM_SS_UNSUBSCRIBED.' WHERE varEmail = %s';
    $result = $wpdb->query($wpdb->prepare($sql,$email));
    
    if ($result !== false)
        return true;
    else
        return false;  
}

function gsom_changeEmail($email,$newemail)
{
    global $wpdb;
    //global $gsom_table_subs;
    
    $gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
    
    $sql = 'UPDATE '.$gsom_table_subs.' SET varEmail = %s WHERE varEmail = %s';
    $result = $wpdb->query($wpdb->prepare($sql,$newemail,$email));
    
    if ($result !== false)
        return true;
    else
        return false;  
}

function gsom_AddSubscription($email, $customData)
{
    global $wpdb;
    global $gsom_form_vars;
    
    
    $json_form = gsom_json_encode($customData); 
    
    $insertFields = '';
    $insertValues = '';
    
    foreach($customData as $item) {
        $insertFields .= ', '.$item['name'];
        $insertValues .= ', "'.addslashes($item['value']).'"';
    }    

    $gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
	
		$ulink = md5($email.time());
		
		$mysqldate = gsom_current_time_fixed('mysql');
    
        $sql = 'INSERT INTO '.$gsom_table_subs.' (dtTime, varIP, varEmail, textCustomFields, varUCode'.$insertFields.') VALUES("'.$mysqldate.'", %s, %s, %s, %s'.$insertValues.')';
        
        $psql = $wpdb->prepare($sql,gsom_peerip(),$email,$json_form, $ulink);
        
        $result = $wpdb->query($psql);
        if ($result !== false)
        {            
            $df = get_option('date_format');
            $tf = get_option('time_format');                
            $gsom_form_vars['subscription_time'] = date($df.' '.$tf);
            $gsom_form_vars['subscriber_ip'] = gsom_peerip();
            
            return true;
        } else {
        	gsom_log('DB Error: '.$wpdb->last_error);
        	gsom_log('SQL: '.$psql);
        	gsom_log('Cdata: '.$customData);
        	return false;
        }
            
}

function gsom_SubscriberPresent($email)
{
    global $wpdb;
    //global $gsom_table_subs;
    $gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
    
    //$sql = 'SELECT intStatus FROM '.$gsom_table_subs.' WHERE varEmail = %s';
    //$result = $wpdb->get_row($wpdb->prepare($sql,$email));
    $sql = 'SELECT intStatus FROM '.$gsom_table_subs.' WHERE varEmail = %s';    
    $result = $wpdb->get_var($wpdb->prepare($sql,$email));
    
    if (($result !== false) && ($result !== null))
    {
        return intval($result,10);        
    }
    else
        return false;
}

function gsom_ConfirmSubscription($email)
{
    global $wpdb;
    //global $gsom_table_subs;
    $gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
    
    $sql = 'UPDATE '.$gsom_table_subs.' SET intStatus = '.GSOM_SS_CONFIRMED.' WHERE varEmail = %s';
    $result = $wpdb->query($wpdb->prepare($sql,$email));
    
    if ($result !== false)
        return true;
    else
        return false;    
}

function gsom_setUnconfirmed($email)
{
    global $wpdb;
    //global $gsom_table_subs;
    $gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
    
    $sql = 'UPDATE '.$gsom_table_subs.' SET intStatus = '.GSOM_SS_SUBSCRIBED.' WHERE varEmail = %s';
    $result = $wpdb->query($wpdb->prepare($sql,$email));
    
    if ($result !== false)
        return true;
    else
        return false;    
}

// further functions work only inside of The Loop

function gsom_postTime() {
    global $post;
    $time = $post->post_date; 
    $time = mysql2date('U', $time); 
    return $time;
}

function gsom_postTitle(){
    global $post;
    return $post->post_title;
}

function gsom_postExcerpt() {
    global $post;
    $output = '';
    $output = $post->post_excerpt;
    if ( !empty($post->post_password) ) { // if there's a password
           $output = 'There is no excerpt because this is a protected post.';     
    }
    return $output;
}

function gsom_postContent($more_link_text = '(more...)', $stripteaser = 0, $more_file = '', $cut = 0) {
    global $id, $post, $more, $page, $pages, $multipage, $preview, $pagenow;

    $output = '';

    if ( !empty($post->post_password) ) { // if there's a password
            $output = 'This post is password protected.';
            return $output;
    }

    if ( $more_file != '' )
        $file = $more_file;
    else
        $file = $pagenow; //$_SERVER['PHP_SELF'];

    if ( $page > count($pages) ) // if the requested page doesn't exist
        $page = count($pages); // give them the highest numbered page that DOES exist

    $content = $pages[$page-1];
    if ( preg_match('/<!--more(.*?)?-->/', $content, $matches) ) {
        $content = explode($matches[0], $content, 2);
        if ( !empty($matches[1]) && !empty($more_link_text) )
            $more_link_text = strip_tags(wp_kses_no_null(trim($matches[1])));
    } else {
        $content = array($content);
    }
    if ( (false !== strpos($post->post_content, '<!--noteaser-->') && ((!$multipage) || ($page==1))) )
        $stripteaser = 1;
    $teaser = $content[0];
    if ( ($more) && ($stripteaser) )
        $teaser = '';
    $output .= $teaser;
    if ( count($content) > 1 ) {
        if ( $more ) {
            $output .= '<span id="more-'.$id.'"></span>'.$content[1];
        } else {
            $output = balanceTags($output);
            if ( ! empty($more_link_text) )
                $output .= ' <a href="'. get_permalink() . "#more-$id\" class=\"more-link\">$more_link_text</a>";
        }

    }
    if ( $preview ) // preview fix for javascript bug with foreign languages
        $output =    preg_replace('/\%u([0-9A-F]{4,4})/e',    "'&#'.base_convert('\\1',16,10).';'", $output);
        
   if($cut > 0 ) {
       $output = substr($output,0,$cut);
   } 

    return gsom_clear_tags($output);
} 

function gsom_postPermalink($id = 0, $leavename=false) {
    $rewritecode = array(
        '%year%',
        '%monthnum%',
        '%day%',
        '%hour%',
        '%minute%',
        '%second%',
        $leavename? '' : '%postname%',
        '%post_id%',
        '%category%',
        '%author%',
        $leavename? '' : '%pagename%',
    );

    global $post;

    if ( empty($post->ID) ) return FALSE;

    if ( $post->post_type == 'page' )
        return get_page_link($post->ID, $leavename);
    elseif ($post->post_type == 'attachment')
        return get_attachment_link($post->ID);

    $permalink = get_option('permalink_structure');

    if ( '' != $permalink && !in_array($post->post_status, array('draft', 'pending')) ) {
        $unixtime = strtotime($post->post_date);

        $category = '';
        if ( strpos($permalink, '%category%') !== false ) {
            $cats = get_the_category($post->ID);
            if ( $cats )
                usort($cats, '_usort_terms_by_ID'); // order by ID
            $category = $cats[0]->slug;
            if ( $parent=$cats[0]->parent )
                $category = get_category_parents($parent, FALSE, '/', TRUE) . $category;

            // show default category in permalinks, without
            // having to assign it explicitly
            if ( empty($category) ) {
                $default_category = get_category( get_option( 'default_category' ) );
                $category = is_wp_error( $default_category ) ? '' : $default_category->slug; 
            }
        }

        $author = '';
        if ( strpos($permalink, '%author%') !== false ) {
            $authordata = get_userdata($post->post_author);
            $author = $authordata->user_nicename;
        }

        $date = explode(" ",date('Y m d H i s', $unixtime));
        $rewritereplace =
        array(
            $date[0],
            $date[1],
            $date[2],
            $date[3],
            $date[4],
            $date[5],
            $post->post_name,
            $post->ID,
            $category,
            $author,
            $post->post_name,
        );
        $permalink = get_option('home') . str_replace($rewritecode, $rewritereplace, $permalink);
        $permalink = user_trailingslashit($permalink, 'single');
        return apply_filters('post_link', $permalink, $post);
    } else { // if they're not using the fancy permalink option
        $permalink = get_option('home') . '/?p=' . $post->ID;
        return apply_filters('post_link', $permalink, $post);
    }
} 

// ----

function gsom_get_fancy_excerpt($max_length = 350){
    
    $content = gsom_postContent('', 0, '');

    $excerpt = apply_filters('the_content', $content);
		
	//$excerpt = wp_specialchars($excerpt);
	
	
    $excerpt = trim(strip_tags($excerpt));
    str_replace("&#8212;", "-", $excerpt);

    $words = preg_split("/(?<=(\.|!|\?)+)\s/", $excerpt, -1, PREG_SPLIT_NO_EMPTY);

    foreach ( $words as $word )
    {
        $new_text = $text . substr($excerpt, 0, strpos($excerpt, $word) + strlen($word));
        $excerpt = substr($excerpt, strpos($excerpt, $word) + strlen($word), strlen($excerpt));

        if ( ( strlen($text) != 0 ) && ( strlen($new_text) > $max_length ) )
        {
            $text .= " (...)";
            break;
        }

        $text = $new_text;
    }    
    return $text;
}

function gsom_cut_images($content) {
	return preg_replace('/\<img[^>]+>/','',$content);
}

function gsom_getNewPostsfrom($timestamp = false) {    
    global $gsom_form_vars;
    global $post;
    
    $i = 1;                                     
    $newposts = array();
    
	//gsom_log('gsom_getNewPostsfrom: '.date(DATE_RFC822,$timestamp).', postlimit: '.$postsLimit);
    
    $df = get_option('date_format');
    $tf = get_option('time_format');       
    
    $showFullPosts = get_option('gsom_show_full_posts');    
    
    $postsLimit = get_option('gsom_feed_limit');  
    
    /*
    	category_name=
    	cat=22
    	year=$current_year
    	monthnum=$current_month
    	order=ASC
    	tag=bread,baking
    	author=3
    	caller_get_posts=1
    	author=1
    	post_type=page
    	post_status=publish
    	orderby=title
    	order=ASC
    	
    *  hour= - hour (from 0 to 23)
    * minute= - minute (from 0 to 60)
    * second= - second (0 to 60)
    * day= - day of the month (from 1 to 31)
    * monthnum= - month number (from 1 to 12)
    * year= - 4 digit year (e.g. 2009)
    * w= - week of the year (from 0 to 53) and uses the MySQL WEEK command Mode=1.     	
    
    
    */
    
    
    
    $q = 'showposts='.$postsLimit.'&post_status=publish';
    
    $qcat = get_option('gsom_bcst_sel_cat');
    if ($qcat != '') {
    	$q .= '&cat='.$qcat;
    }
    
    
    $qauth = get_option('gsom_bcst_sel_auth');
    
    if ($qauth != '') {
    	$q .= '&author='.$qauth;
    }    
    

    query_posts($q);
    //gsom_log('gsom_getNewPostsfrom: entering posts loop');
    
    //TODO: check post time incl. minutes and remove post before last post time
    
    while (have_posts()) {
        the_post();            
        $pt = gsom_postTime();      
        
        if ($showFullPosts != '1') {
            $content = gsom_postExcerpt();			
            if(trim($content) == '') {            	
                $content = gsom_get_fancy_excerpt(get_option('gsom_rss_excerpt_length'));
				
            }
        } else { // use content            
            $content = gsom_postContent('', 0, '');						
		    $content = apply_filters('the_content', $content);											
        }
        
        if(get_option('gsom_filter_images')) {
        	$content = gsom_cut_images($content);
        }
        
        if(!$timestamp) {
            $timestamp = 0;
        }
		
		//gsom_log('gsom_getNewPostsfrom: post time: '.date(DATE_RFC822,$pt).', selector time: '.date(DATE_RFC822,$timestamp));
        
        if(($pt > $timestamp) && ('publish' == $post->post_status)){
            $newposts[] = array(
            	'rss_item_date' => date($df.' '.$tf, $pt),
            	'rss_item_timestamp' => $pt,
				'rss_item_title' => gsom_postTitle(),
				'rss_item_description' => $content,
				'rss_item_link' => gsom_postPermalink(),
				'rss_item_number' => $i,
				'rss_item_author' => get_userdata($post->post_author)->display_name);
            $i++;
        }

    }
    //gsom_log('gsom_getNewPostsfrom: exiting posts loop');	
	//gsom_log('gsom_getNewPostsfrom: returning '.count($newposts).' posts');
    return $newposts;
}

function gsom_clear_tags($src) {
	return preg_replace('@<script>.*?</script>@i', '', $src);
}

function gsom_replaceVars($string)
{
    global $gsom_form_vars;    
    
    $tmpstr = $string;    
    
    foreach($gsom_form_vars as $key => $value)
    {                
       // replace singe variables 
       $tmpstr = preg_replace('/\$'.preg_quote($key).'/si', $value,$tmpstr);
    }   
    
    //$tmpstr = preg_replace('/\$[a-zA-Z0-9_-]+/si', '',$tmpstr);
    
    return $tmpstr;
}

function gsom_replaceVar($string,$tpl,$replacement)
{
    return preg_replace('/\$'.$tpl.'/i', $replacement,$string);
}

function gsom_ModBase64Decode($str)
{
  $str = preg_replace('#\-#','/',$str);
  $str = preg_replace('#\_#','+',$str);
  return base64_decode($str.'==');
}

function gsom_ModBase64Encode($str)
{
  $str = Rtrim(base64_encode($str),'=');
  $str = preg_replace('#\/#','-',$str);
  $str = preg_replace('#\+#','_',$str);      
  return $str;
}

function gsom_GetUnsubscribeLink($email, $code = '')
{
	if(($email == '') && ($code == '')) {
		$ucode = '%%varUCode%%';
	} elseif ($code != '') {
		$ucode = $code;
	} elseif ($code == '') {
		$ucode = gsom_get_ucode_by_email($email);
	}	

    return gsom_BuildLink('manage-subscription',array('u' => $ucode));
}

function gsom_GetConfirmationLink($email, $code = '')
{
	if ($code != '') {
		$ucode = $code;
	} elseif ($code == '') {
		$ucode = gsom_get_ucode_by_email($email);
	}	
    
    return gsom_BuildLink('confirm-subscription',array('u'=>$ucode));
}

function gsom_GetResendConfirmationLink($email, $code = '')
{    
	if ($code != '') {
		$ucode = $code;
	} elseif ($code == '') {
		$ucode = gsom_get_ucode_by_email($email);
	}	

	return gsom_BuildLink('resend-confirmation',array('u' => $ucode));
}

function gsom_FillVarsWithCustomFormData($email)
{
    global $wpdb;
    global $gsom_form_vars; 
    $snapShot = '';
    
    $gsom_table_subs = $wpdb->prefix . "gsom_subscribers";
    
    $sql = $wpdb->prepare('SELECT varEmail, textCustomFields, dtTime, varIP FROM '.$gsom_table_subs.' WHERE varEmail = %s',$email);    
    $result = $wpdb->get_row($sql);
    
    if ($result)
    {
        
        $gsom_form_vars['gsom_email_field'] = $email;                   
        
        $df = get_option('date_format');
        $tf = get_option('time_format');                
        $gsom_form_vars['subscription_time'] = mysql2date($result->dtTime,$df.' '.$tf);
        $gsom_form_vars['subscriber_ip'] = $result->varIP;
        
        $gsom_form_vars['manage_subscription_link'] = gsom_GetUnsubscribeLink($email);
        $gsom_form_vars['confirmation_link'] = gsom_GetConfirmationLink($email);
        $gsom_form_vars['resend_confirmation_link'] = gsom_GetResendConfirmationLink($email);    
        //$gsom_form_vars['encoded_email'] = $email;
        //$gsom_form_vars['decoded_email'] = gsom_ModBase64Encode($email);
        
        $customFields = $result->textCustomFields;
        
        $jdform = gsom_json_decode($customFields,true);       
       
        foreach($jdform as $item)
        {
            $gsom_form_vars[$item['name']] = $item['value'];
            $snapShot .= $item['label']." : ".$item['value']."\n";
        }                
        $snapShot .= "Email : ".$email."\n";
        
        $gsom_form_vars['gsom_form_data'] = $snapShot;
    }
    else
        return false;    
}

function gsom_BuildLink($page)
{
	
	//(strstr(PHP_OS, 'WIN') ? "\\" : "\/")
	
	global $gsom_form_vars;
    $page = func_get_arg(0);
    
    if(func_num_args() == 2)    
        $params = func_get_arg(1);    
    else
        $params = array();
    
    $url = $gsom_form_vars['blog_url'];
         
    $ps = get_option('permalink_structure');
    
    if (trim($ps) == '')
    {
        // permalinks disabeld add params
        $url .= '?page_id='.$page;
        foreach($params as $key => $value)
        {
            $url .= '&'.$key.'='.$value;
        }
    }
    else
    {
        // add permalink
        $lp = strrpos($ps,'%');
        $delim = '/';
        if($lp !== false){
            if($lp+1 <= strlen($ps)){
                if($ps[$lp+1] != '/'){
                    $delim = '';
                }       
            }
        }
                
        if (strpos($ps,'index.php/') !== false) {
        		$url .= 'index.php/';
        }
        
        $url .= $page.$delim;
        
        $ft = true;
        foreach($params as $key => $value)
        {
            if ($ft)
            {
                $del = '?';
                $ft = false;
            }
            else
                $del = '&';
            $url .= $del.$key.'='.$value;
        }        
    }    
    return $url;    
}

function gsom_EmailValid($email, $die = false)
{
	$valid = preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $email);	
	
	if($die){
        if (!$valid){
        	header('Location: '.gsom_BuildLink('bad-email'));						
			echo " ";			
			exit();			        	
        } else {
        	return false;
        }       
	} else {
		return $valid;
	}    
}


function gsom_Mail($subjTpl, $messageTpl, $notification = false, $opts = false)
{   
    require_once 'lib/class.phpmailer.php';
    
    global $gsom_form_vars;
    $email = $gsom_form_vars['gsom_email_field'];
    
    $subject = gsom_replaceVars($subjTpl);
    if(is_array($messageTpl)){
        $body = gsom_replaceVars($messageTpl['plain']);
        $html_body = gsom_replaceVars($messageTpl['html']);
    } else {
        $body = gsom_replaceVars($messageTpl);    
        $html_body = '';
    }
    
    if (($opts) && (is_array($opts))) {
    	
		$gsom_mail_delivery_option = $opts['gsom_mail_delivery_option'];
		$gsom_smtp_secure_conn = $opts['gsom_smtp_secure_conn'];
		    
		$gsom_smtp_hostname = trim($opts['gsom_smtp_hostname']);
		$gsom_smtp_username = trim($opts['gsom_smtp_username']);
		$gsom_smtp_password = trim($opts['gsom_smtp_password']);
		$gsom_smtp_port     = trim($opts['gsom_smtp_port']);
    	
    } else {
		$gsom_mail_delivery_option = get_option('gsom_mail_delivery_option');
		$gsom_smtp_secure_conn = get_option('gsom_smtp_secure_conn');
		    
		$gsom_smtp_hostname = get_option('gsom_smtp_hostname');
		$gsom_smtp_username = get_option('gsom_smtp_username');
		$gsom_smtp_password = get_option('gsom_smtp_password');
		$gsom_smtp_port     = get_option('gsom_smtp_port');    	
    }
    

    
    if ($notification) {
        $gsom_email_from = get_option('admin_email');
        $gsom_name_from = $gsom_form_vars['blog_name'];    
        $email = $gsom_form_vars['from_email'];
    } else {    
        $gsom_email_from = $gsom_form_vars['from_email'];
        $gsom_name_from = $gsom_form_vars['from_name'];
    }    
	
	gsom_log('gsom_Mail: Email from: '.$gsom_email_from);
	gsom_log('gsom_Mail: Name from: '.$gsom_name_from);	
	
	
	try {
		$mail = new PHPMailer(); // defaults to using php "mail()"
	    
	    switch ($gsom_mail_delivery_option) {
	    	case 'smtp':	        
	    	
				$mail->IsSMTP();// tell the class to use SMTP
				
				if (!empty($gsom_smtp_username) && !empty($gsom_smtp_password)) {
					$mail->SMTPAuth   = true;                  // enable SMTP authentication	
					$mail->Username   = $gsom_smtp_username;     // SMTP server username
					$mail->Password   = $gsom_smtp_password;            // SMTP server password					
				} 
				
				if ($gsom_smtp_secure_conn != 'off') {
					$mail->SMTPSecure = $gsom_smtp_secure_conn;
				}
				
				$mail->Port       = $gsom_smtp_port;                    // set the SMTP server port
				$mail->Host       = $gsom_smtp_hostname; // SMTP server

	
	    	break;
	    	case 'sendmail':
	    		$mail->IsSendmail();  // tell the class to use Sendmail    	
	    	break;
	    	case 'phpmail':
			
	    	break;
	    }
	    
	    
	    
		$mail->CharSet = 'utf-8';
		
		$mail->AddReplyTo($gsom_email_from, $gsom_name_from);
		
		$mail->SetFrom($gsom_email_from, $gsom_name_from);
		
		$mail->AddAddress($email);
		
		$mail->Subject = $subject;
		
	    if($html_body != '') {
	    	$mail->Body = $html_body;
	    	$mail->AltBody = $body;
	    	//$mail->MsgHTML($html_body);        
	    } else {
	    	$mail->Body = $body;	    	
	    	//$mail->Body = '<html><body><p style="color: red;">this is html body</p></body></html>';
	    	//$mail->AltBody = $body;	    	
	    }
	    
		$x = $mail->Send();	  		
	} catch (phpmailerException $e) {
		$x = false;
		gsom_log('PHPMailer error: '.$e->errorMessage());		
	}
    return $x;
}

function gsom_more_reccurences($schedules) {
    return array_merge($schedules,     
			array(
			    'weekly'		=> array('interval' => 604800, 'display' => 'Once Weekly'),
			    'fortnightly'	=> array('interval' => 1209600, 'display' => 'Once Fortnightly'),
				'5mins'			=> array('interval' => 300, 'display' => 'Every 5 minutes')
			));
}


// shortcode support
//*

    function gsom_optin_bartag_func($atts) {
        extract(shortcode_atts(array(
            'foo' => 'no foo',
            'bar' => 'default bar',
        ), $atts));

        return gsom_put_form(false);
    }
    if (function_exists('add_shortcode'))
        add_shortcode('gsom-optin', 'gsom_optin_bartag_func');
//*/

function gsom_unconfirmed_cleanup() {
    global $wpdb;
    global $gsom_table_subs;    
    $sql = $wpdb->prepare('DELETE FROM '.$gsom_table_subs.' where intStatus = '.GSOM_SS_SUBSCRIBED.' and DATE_ADD(dtTime, INTERVAL 7 DAY) <= CURDATE()');
    $result = $wpdb->query($sql);
} 

//// dashboard widget

### Function: Register Dashboard Widget

function gsom_register_dashboard_widget() {
    global $gsom_full_plugin_name;    
    wp_register_sidebar_widget('dashboard_gsom', __('G-Lock Double Opt-in Manager', 'G-Lock Double Opt-in Manager'), 'dashboard_gsom',    
        array(
        //'all_link' => 'edit.php?page='.dirname(plugin_basename(__FILE__)).'/'.__FILE__, // Example: 'index.php?page=wp-useronline/wp-useronline.php'
        //'feed_link' => 'Full URL For "RSS" link', // Example: 'index.php?page=wp-useronline/wp-useronline-rss.php'
        //'all_link' => 'hello',
        'width' => 'half', // OR 'fourth', 'third', 'half', 'full' (Default: 'half')
        'height' => 'single', // OR 'single', 'double' (Default: 'single')
        )
    );
}
 
### Function: Add Dashboard Widget
function gsom_add_dashboard_widget($widgets) {
    global $wp_registered_widgets;
    if (!isset($wp_registered_widgets['dashboard_gsom'])) {
        return $widgets;
    }
	$w1 = array_slice($widgets,0,1);
	$w2 = array_slice($widgets,1);
	return array_merge($w1,array('dashboard_gsom'),$w2);
    
    //$widgets[] = array('dashboard_gsom');
    
    //return $widgets;
}
 
### Function: Print Dashboard Widget
function dashboard_gsom($sidebar_args) {
    global $wpdb;    
    if (is_array($sidebar_args)){
        extract($sidebar_args, EXTR_SKIP);
    }
    echo $before_widget;
    echo $before_title;
    echo $widget_name;
    echo $after_title;
    
        global $gsom_plugindir;
        
        echo '<style>
                table.gsom-dboard-summary {    
                    /*border-collapse: collapse;*/
                    border: 1px solid #eeeeee;
                    
                    margin: 10px 1px 1px 1px;                    
                }
                
                table.gsom-dboard-summary td, table.gsom-dboard-summary th {
                    padding: 3px;
                    font-weight: normal;
                }                       

                table.gsom-dboard-summary thead tr {
                    background: #EBEBEB;
                    font-size: 12px;                    
                }        
                table.gsom-dboard-summary tbody td {
                    text-align: center;
                }
                p.glock_sub {
                    color:#777777;
                    font-style:italic;
                    font-family:Georgia,serif;
                    margin:-10px;                
                }
              </style>';         
  
        echo '<div>';
        echo '<div class="youhave" style="clear:both; overflow:hidden;">';        
        echo '<p class="glock_sub" style="margin: 0; float:left;">Subscription summary</p>';
        echo '<a style="line-height:140%; float:right" href="admin.php?page=gsom-subscribers">Manage subscribers</a>';
        echo '</div>';        
        
        $tod_subs = gsom_getsubscribers('today_confirmed');
        $ytd_subs = gsom_getsubscribers('ytd_confirmed');
        $total_subs = gsom_getsubscribers('total_confirmed');
        $total_unsubs = gsom_getsubscribers('total_unsubscribed');
        $total_unconf = gsom_getsubscribers('total_unconfirmed');        
        
        
        $bcsttime = get_option('gsom_last_broadcast');
        if($bcsttime){
            $df = get_option('date_format');
            $tf = get_option('time_format');            
            $lastbcst = '<p>Last broadcast was sent on <b>'.date($df.' '.$tf,$bcsttime).'</b></p>';
        } else {
            $lastbcst = '';
        }        

        
        echo '   
        <div style="overflow-y: auto;">     
        <table style="width:auto;" class="gsom-dboard-summary">
            <thead>
                <tr>
                    <th>Today subscribed</th>
                    <th>Yesterday subscribed</th>
                    <th>Total subscribers</th>
                    <th>Total unsubscribed</th>
                    <th>Total unconfirmed</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>'.$tod_subs.'</td>
                    <td>'.$ytd_subs.'</td>
                    <td style="color: green;">'.$total_subs.'</td>
                    <td style="color: red;">'.$total_unsubs.'</td>
                    <td style="color: orange;">'.$total_unconf.'</td>                    
                </tr>
            </tbody>
        </table></div>'.$lastbcst.'</div>';       
        
        //echo '<strong>Subscription Summary</strong><br />';
//        echo '<ul style="list-style-type: none; list-style-image: none; list-style-position: outside;">';
//        echo '<li style="clear: both;"><span style="display:block; float:left; width:150px;">Today subscribed</span> '.$tod_subs.'</li>';
//        echo '<li style="clear: both;"><span style="display:block; float:left; width:150px;">Total subscribers</span> '.$total_subs.'</li>';
//        echo '<li style="clear: both;"><span style="display:block; float:left; width:150px;">Total unsubscribed</span> '.$total_unsubs.'</li>';
//        echo '<li style="clear: both;"><span style="display:block; float:left; width:150px;">Total unconfirmed</span> '.$total_unconf.'</li>';
//        echo '</ul>';
//        echo '</p>';
//        echo '</div>';
//    

    echo $after_widget;
}



function gsom_getsubscribers($opt){
    global $wpdb;
    global $gsom_table_subs;
    switch($opt){
        case 'today_confirmed':
            $sql = 'select count(intId) from '.$gsom_table_subs.' where intStatus = '.GSOM_SS_CONFIRMED.' and DATE(dtTime) = CURDATE()';
        break;               
        case 'ytd_confirmed':
            $sql = 'select count(intId) from '.$gsom_table_subs.' where intStatus = '.GSOM_SS_CONFIRMED.' and DATE(dtTime) = DATE_SUB(CURDATE(),INTERVAL 1 DAY)';        
        break;         
        case 'total_unconfirmed':
            $sql = 'select count(intId) from '.$gsom_table_subs.' where intStatus = '.GSOM_SS_SUBSCRIBED;
        break;                
        case 'total_unsubscribed':
            $sql = 'select count(intId) from '.$gsom_table_subs.' where intStatus = '.GSOM_SS_UNSUBSCRIBED;
        break;        
        case 'total_confirmed':
        default:
            $sql = 'select count(intId) from '.$gsom_table_subs.' where intStatus = '.GSOM_SS_CONFIRMED;
        break;
    }
    $res = $wpdb->get_var($wpdb->prepare($sql));
    
    return $res;
}

function gsom_admin_latest_activity() {
        global $gsom_plugindir;
  
        echo '<style>
                table.gsom-dboard-summary {    
                    /*border-collapse: collapse;*/
                    border: 1px solid #eeeeee;
                    
                    margin: 10px 1px 1px 1px;
                }
                
                table.gsom-dboard-summary td, table.gsom-dboard-summary th {
                    padding: 3px;
                    font-weight: normal;
                }                       

                table.gsom-dboard-summary thead tr {
                    background: #EBEBEB;
                    font-size: 12px;                    
                }        
                table.gsom-dboard-summary tbody td {
                    text-align: center;
                }
                p.glock_sub {
                    color:#777777;
                    font-style:italic;
                    font-family:Georgia,serif;
                    margin:-10px;                
                }
              </style>'; 
  
  //      echo '<div>';
//        echo '<div class="youhave" style="clear:both; overflow:hidden;">';        
//        echo '<h4 style="margin: 0; float:left;">G-Lock Double Opt-in Manager</h4>';
//        echo '<a style="float:right" href="edit.php?page='.$gsom_plugindir.'/glsft-optin.php">Manage subscribers</a>';
//        echo '</div>';        
        
        echo '<div style="margin-top:10px;">';
        echo '<div class="youhave" style="clear:both; overflow:hidden;">';        
        echo '<p class="glock_sub" style="margin: 0; float:left; font-weight: bold;">G-Lock Double Opt-in Manager</p>';
        echo '<a style="line-height:140%; float:right" href="admin.php?page=gsom-subscribers">Manage subscribers</a>';
        echo '</div>';        
        
        $tod_subs = gsom_getsubscribers('today_confirmed');
        $ytd_subs = gsom_getsubscribers('ytd_confirmed');
        $total_subs = gsom_getsubscribers('total_confirmed');
        $total_unsubs = gsom_getsubscribers('total_unsubscribed');
        $total_unconf = gsom_getsubscribers('total_unconfirmed');        
        
        $bcsttime = get_option('gsom_last_broadcast');
        if($bcsttime){
            $df = get_option('date_format');
            $tf = get_option('time_format');            
            $lastbcst = '<p>Last broadcast was sent on <b>'.date($df.' '.$tf,$bcsttime).'</b></p>';
        } else {
            $lastbcst = '';
        }

        echo '   
        <div style="overflow-y: auto;">     
        <table style="width:auto;" class="gsom-dboard-summary">
            <thead>
                <tr>
                    <th>Today subscribed</th>
                    <th>Yesterday subscribed</th>
                    <th>Total subscribers</th>
                    <th>Total unsubscribed</th>
                    <th>Total unconfirmed</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>'.$tod_subs.'</td>
                    <td>'.$ytd_subs.'</td>
                    <td style="color: green;">'.$total_subs.'</td>
                    <td style="color: red;">'.$total_unsubs.'</td>
                    <td style="color: orange;">'.$total_unconf.'</td>                    
                </tr>
            </tbody>
        </table></div>'.$lastbcst.'        
        </div>'; 
  }
  
  function gsom_broadcast_proc(){
    // runs every 5 minutes  
    
    $send = get_option('gsom_bcst_send');
    $when = get_option('gsom_bcst_when');
    $day = get_option('gsom_bcst_day_number');
    
    gsom_log('Manual send. leaving hook.');
    
    if ($send == 'manually') {
    	return;
    }
    
			//         $gsom_last_broadcast = get_option('gsom_last_broadcast');
			//         if($gsom_last_broadcast) {
			//             if (date('Ymd',gsom_current_time_fixed('timestamp')) == date('Ymd',$gsom_last_broadcast)) {
			//                 //event was already fired today, return
			// gsom_log('event was already fired today |'.date('Ymd',gsom_current_time_fixed('timestamp')).' == '.date('Ymd',$gsom_last_broadcast));
			//                 return;
			//             }         
			//         }    
    
	gsom_log('gsom_broadcast_proc: Send option: '.$send);
	gsom_log('gsom_broadcast_proc: When option: '.$when);
	gsom_log('gsom_broadcast_proc: Day option: '.$day);
    
    if ($send == 'date') {
    	$ts_now = gsom_current_time_fixed('timestamp');
            if(trim(strtolower($when)) == 'dom'){
                // on specific day of month				
                $maxDayThisMonth = date('t',$ts_now);            
                $today = date('j',$ts_now);
                if($day > $maxDayThisMonth){
                    $day = $maxDayThisMonth;
                }
                if($today == $day) {
                	gsom_log('gsom_broadcast_proc: CALLING gsom_run_broadcast()');
                    gsom_run_broadcast();					
                }
            } else {
                // on specific day of week
                $dow_now = strtolower(date('D',$ts_now));
                $when = strtolower($when);
                gsom_log('Checking day of week match. now is '.$dow_now.' and it should be '.$when.' to start a broadcast');
                if($dow_now == $when){
                	gsom_log('gsom_broadcast_proc: CALLING gsom_run_broadcast()');
                    gsom_run_broadcast();					
                }            
            }        
    } elseif($send == 'number') {
    	gsom_log('gsom_broadcast_proc: CALLING gsom_run_broadcast()');
        gsom_run_broadcast($send);
    }
  }
  
  function gsom_fillRssLoopTemplate($tpl,$vars){
       //  matches any loop 
       $patt = '@\$rss_itemblock(.*?)/\$rss_itemblock@msi';
       
       $m = array();
       if (!preg_match($patt,$tpl,$m)) {
           return $tpl;
       }
       
       $rtpl = '';
       
       $block = $m[1];
       
       foreach($vars as $item) {
            $tmp = $block;
            foreach($item as $var => $value){
                $tmp = str_replace('$'.$var,$value,$tmp);
            }           
            $rtpl .= $tmp;
       }       
       
       $tpl = str_replace($m[0],$rtpl,$tpl);
       
       return $tpl;        
  }
  
  function gsom_getLastSubscriberTime(){
      global $wpdb;
      global $gsom_table_subs;      
      
      $sql = 'SELECT dtTime FROM '.$gsom_table_subs.' order by dtTime DESC limit 1';
      $stime = $wpdb->get_var($sql);
      if ($stime){
        $stime = mysql2date('U',$stime);
      } else {
          $stime = gsom_current_time_fixed('timestamp');
      }
      return $stime;      
  }
  
  function gsom_setBroadcastLock() {
  	  //$lock = get_option('gsom_broadcast_lock');
      //if ($lock) {
      update_option('gsom_broadcast_lock','1');
     // } else {
     //    add_option('gsom_broadcast_lock','1');
     // }   	
     // gsom_log('[gsom_setBroadcastLock] gsom_broadcast_lock is '.$lock);
  }
  
  function gsom_releaseBroadcastLock() {
  	  //$lock = get_option('gsom_broadcast_lock');
      //if ($lock) {
      update_option('gsom_broadcast_lock','0');
     // } else {
     //     add_option('gsom_broadcast_lock','0');
     // }   	
     // gsom_log('[gsom_releaseBroadcastLock] gsom_broadcast_lock is '.$lock);
  }
  
  function gsom_broadcastLocked() {
  	  $lock = get_option('gsom_broadcast_lock');
  	  gsom_log('[gsom_broadcastLocked] gsom_broadcast_lock is '.$lock);
      if($lock == '1') {
          return true;
      } else {
          return false;
      }
  }
  
   
  function gsom_run_broadcast($option = 'date'){
      global $wpdb;
      global $gsom_table_subs;      
      global $gsom_form_vars;
      
      gsom_log("\n----------------------------------- [ ".date(DATE_RFC822)." ] Running broadcast -----------------------------------\n");
      
	  if (gsom_broadcastLocked()) {
	  	gsom_log('gsom_run_broadcast: BROADCAST IS LOCKED. return.');
	  	return;
	  } else {
	  	gsom_log('gsom_run_broadcast: LOCKING BROADCAST');
	  	gsom_setBroadcastLock();
	  }
      try {
	      $df = get_option('date_format');
	      $tf = get_option('time_format');   
	      
	      $gsom_last_broadcast = get_option('gsom_last_broadcast');
	      
	      
	      if($gsom_last_broadcast != '') {
	          $newPostFrom = $gsom_last_broadcast;
	      } else {
	          $newPostFrom = gsom_getLastSubscriberTime();
			  gsom_log('gsom_run_broadcast: last subscriber time: '. date(DATE_RFC822,$newPostFrom));
	      }
		  
		  gsom_log('gsom_run_broadcast: broadcast option: '.$option);
	      
	      if($option == 'date'){                            
	        $newposts = gsom_getNewPostsfrom($newPostFrom);
	        gsom_log('Selecting the post starting from '.date(DATE_RFC822, $newPostFrom));
	  	    $newposts_num = count($newposts);
	      } elseif($option == 'number') {
	        $numOfNewPostToSend = get_option('gsom_bcst_number_of_posts');  
	        $newposts = gsom_getNewPostsfrom($newPostFrom);
	  	    $newposts_num = count($newposts);
			
	        if ($newposts_num < $numOfNewPostToSend) {
	        	gsom_log('gsom_run_broadcast: Error. you need '.$numOfNewPostToSend.' to run broadcast. You have only '.$newposts_num);
	        	gsom_releaseBroadcastLock();
	            return;
	        }        
	      }      
		  
		 		
		  gsom_log('gsom_run_broadcast: Newpost num: '.$newposts_num);
	      
		  if ($option == 'date' AND $newposts_num == 0){
	      	gsom_log('gsom_run_broadcast: Cannot run broadcast you have no new posts.');
	      	gsom_releaseBroadcastLock();
	        return;
	      }
	      
	      $lastPost = $newposts[0];
	      
	      foreach($lastPost as $key => $value) {
	        $gsom_form_vars['last_'.$key] = $value;          
	      }
	            
	      $subj = get_option('gsom_bcst_email_subj');
	      $msg_html = get_option('gsom_bcst_email_msg');
	      $msg_plain = get_option('gsom_bcst_email_msg_plain');
	      
	      // expandin rss loop template and filling rss loop vars
	      $msg_html = gsom_fillRssLoopTemplate($msg_html,$newposts);
	      $msg_plain = gsom_fillRssLoopTemplate($msg_plain,$newposts);
	      
	      $gsom_form_vars['rss_channel_title'] = $gsom_form_vars['blog_name'];
	      $gsom_form_vars['rss_channel_description'] = get_option('blogdescription');
	      $gsom_form_vars['rss_channel_link'] = get_option('home');      
		  
		  $gsom_init_fvars = $gsom_form_vars;
	      
	      $sql = 'SELECT * FROM '.$gsom_table_subs.' WHERE intStatus = '.GSOM_SS_CONFIRMED;
	      $results = $wpdb->get_results($sql,ARRAY_A);
		  
	      if ($results) {
		      foreach ($results as $row) {                       
		        // filling variables
				$gsom_form_vars = $gsom_init_fvars;
		        $email = $row['varEmail'];
		        $gsom_form_vars['gsom_email_field'] = $email;                   
		        
		        $gsom_form_vars['subscription_time'] = mysql2date($df.' '.$tf, $row['dtTime']);
		        $gsom_form_vars['subscriber_ip'] = $row['varIP'];
		        
		        $gsom_form_vars['manage_subscription_link'] = gsom_GetUnsubscribeLink($email);
		        $gsom_form_vars['confirmation_link'] = gsom_GetConfirmationLink($email);
		        $gsom_form_vars['resend_confirmation_link'] = gsom_GetResendConfirmationLink($email);    
		        $gsom_form_vars['encoded_email'] = $email;
		        $gsom_form_vars['decoded_email'] = gsom_ModBase64Encode($email);
		        
		        $customFields = $row['textCustomFields'];
		        
		        $jdform = gsom_json_decode($customFields,true);       
		       
		        foreach($jdform as $item){
		            $gsom_form_vars[$item['name']] = $item['value'];            
		        }
		        
		          $message = array('plain' => $msg_plain,
		                           'html' => $msg_html);          
		          
		          
		        // sending email          
		        if ( get_option('gsom_write_debug_log') == '1' ) {
		        	$mail_res = "DEBUG MODE: Message to $email was not sent.";
		        } else {
		        	$mail_res = gsom_Mail($subj,$message);
		        }
		        
				
				gsom_log('gsom_run_broadcast: Mail func result: '.$mail_res);
		        
		      } 
		      
		  } else {
			 gsom_log('Broadcast wasn\'t sent. There is no confirmed users in your list.');
		  }
	      
	      //*
	      
	      $gsom_last_broadcast = get_option('gsom_last_broadcast');
	      gsom_log('$gsom_last_broadcast = '.date(DATE_RFC822, $gsom_last_broadcast));
	      $t = gsom_current_time_fixed('timestamp');
	      gsom_log('current_time_fixed = '.date(DATE_RFC822, $t));
	
	  	  gsom_log('updating $gsom_last_broadcast to '.$t);
	  	  update_option('gsom_last_broadcast', $t);
	  	  
	  	  // for debug
	  	  //sleep(15);
	
	      //*/     
		  gsom_log('gsom_run_broadcast: UNLOCKING BROADCAST');
	  	  gsom_releaseBroadcastLock();      
      
	  } catch( Exception $e) {
		gsom_log('gsom_run_broadcast Exception: '.$e->getMessage());
		gsom_releaseBroadcastLock();	  	
	  }

  }
  
  
  function gsom_test_func() {
  	//gsom_log(date(DATE_RFC822,gsom_current_time_fixed('timestamp'))." event fired");
  }
  
  function gsom_create_log_table_if_ne() {
  	global $gsom_table_log;
  	global $wpdb;
  	
    if($wpdb->get_var("show tables like '".$gsom_table_log."'") != $gsom_table_log) {    
        
    	$sql =	"CREATE TABLE ".$gsom_table_log." ( 
  				 `intId` int(10) unsigned NOT NULL auto_increment, 
  				 `varMessage` varchar(255) NOT NULL, 
  				 PRIMARY KEY  (`intId`) 
				 ) CHARSET=utf8;";

        $result = $wpdb->query($sql);
    }
  }
  
function gsom_setCurrentTimeToTimeStamp( $timestamp ) {	
	//date_default_timezone_set('UTC');
	
	$dt = getdate($timestamp);	
	
	//$t =  ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );	
	
	$datetime = new DateTime();	
	$datetime->setDate($dt['year'], $dt['mon'], $dt['mday']);
	
	$utc = new DateTimeZone('UTC');
	$datetime->setTimezone($utc);
	//date_default_timezone_set('UTC');
	
	return $datetime->format('U');
}  


function gsom_current_time_fixed( $type, $gmt = 0 ) {
	$t =  ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
	switch ( $type ) {
		case 'mysql':
			return $t;
			break;
		case 'timestamp':
			return strtotime($t);
			break;
	}
}

function do_css()
{
	if (stripos($_SERVER['REQUEST_URI'],'gsombroadcast')!== false) {
    	wp_enqueue_style('thickbox');
	}
}

function do_jslibs()
{
	if (stripos($_SERVER['REQUEST_URI'],'gsombroadcast')!== false) {
	    wp_enqueue_script('editor');
	    wp_enqueue_script('thickbox');
	    add_action( 'admin_head', 'wp_tiny_mce' );
	}
}

//$ns = wp_next_scheduled('gsom_broadcast_hook');
//gsom_log('PAGE LOADED @ '.date(DATE_RFC822,gsom_current_time_fixed('timestamp')).'. next scheduled EVENT =>'.date(DATE_RFC822,$ns));

add_filter('cron_schedules', 'gsom_more_reccurences');


//------------------------------------------- tinyMCE fix

// 	function gsom_mce_options($init) {
// 		$init['cleanup'] = false;
// 		$initp['valid_elements'] = '*[*]';
// 
// 		return $init;
// 	}
// 
// add_filter( 'tiny_mce_before_init', 'gsom_mce_options',999 );

// 
// if ( ! function_exists('tadv_htmledit') ) {
// 	function tadv_htmledit($c) {
// 		$tadv_options = get_option('tadv_options');
// 
// 		if ( isset($tadv_options['fix_autop']) && $tadv_options['fix_autop'] == 1 ) {
// 			$c = preg_replace( array('/&amp;/','/&lt;/','/&gt;/'), array('&','<','>'), $c );
// 			$c = wpautop($c);
// 			$c = htmlspecialchars($c, ENT_NOQUOTES);
// 		}
// 		return $c;
// 	}
// }
// add_filter('htmledit_pre', 'tadv_htmledit', 999);
// 
// if ( ! function_exists('tmce_init') ) {
// 	function tmce_init() {
// 		global $wp_scripts;
// 		$tadv_options = get_option('tadv_options');
// 
// 		if ( ! isset($tadv_options['fix_autop']) || $tadv_options['fix_autop'] != 1 ) return;
// 
// 		$queue = $wp_scripts->queue;
// 		if ( is_array($queue) && in_array( 'autosave', $queue ) )
// 			wp_enqueue_script( 'tadv_replace', WP_PLUGIN_URL . '/tinymce-advanced/js/tadv_replace.js', array('editor'), '20080425' );
// 	}
// }
// add_action( 'admin_print_scripts', 'tmce_init' );


//-------------------------------------------













if(!defined('GSOM_INCLUDE')) { // we don't need this code if the file was included from an ajax backend    

// dashboard stats

$gsom_dashboard_stats = get_option('gsom_dashboard_stats');

    switch(strtolower($gsom_dashboard_stats)) {
        case 'abox':
            // activity box
            add_action('activity_box_end', 'gsom_admin_latest_activity');            
        break;        
        case 'widget':
            // separate widget  
            add_action('wp_dashboard_setup', 'gsom_register_dashboard_widget');
            add_filter('wp_dashboard_widgets', 'gsom_add_dashboard_widget');
        break;        
    }

    
    add_action('admin_menu', 'gsom_add_menu_item');
    add_action('init', 'gsom_init');
    add_action('wp_head', 'gsom_headMod');
    add_action('gsom_unconfirmed_cleanup_hook', 'gsom_unconfirmed_cleanup');
    add_action('gsom_broadcast_hook','gsom_broadcast_proc');
    
    
	add_action('admin_print_scripts', 'do_jslibs' );
	add_action('admin_print_styles', 'do_css' );
	
	add_action('gsom_test_func_action','gsom_test_func');
	
	add_action('admin_notices','gsom_activation_error');		
	
	include('ajaxbackend.php');	
	
    register_activation_hook(__FILE__, 'gsom_install');
    register_deactivation_hook( __FILE__, 'gsom_deactivate' );    
    
    
}


?>
