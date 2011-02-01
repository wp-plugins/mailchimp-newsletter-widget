=== MailChimp Newsletter Widget ===
Contributors: matthewGA, matthewpoer
Donate link: http://matthewoer.wordpress.com
Tags: newsletter, mailchimp, widget
Requires at least: 2.7
Tested up to: 2.7
Stable tag: 1.0

The goal of the plug-in is to help a WordPress Site Administrator integrate a MailChimp mailing list into the WordPress site.

== Description ==

I wrote this plug-in mostly as an attempt to learn more about creating WordPress Plug-ins, but I think it is potentially useful for certain folks and so I am releasing it as an open-source, unsupported piece of software. I hope you enjoy it.

The goal of the plug-in is to help a WordPress Site Administrator integrate a MailChimp mailing list into the WordPress site. Once the plug-in is installed, you must provide a MailChimp.com APIKey, then select a list to enroll your visitors to.

The default widget is practical, but not very pretty. Basically, it includes a typical widget layout containing three input fields, one hidden field to track the page URL, and a submit button. The HTML for the widget can be customized to fit your site and WordPress theme. If you choose to do this, please don't try to change the input fields' NAME or ID attributes. It won't work if you change these.

== Installation ==

Like any other WordPress plugin, there are a few different ways to install the files. The easiest way for will be to upload the files directly to the server. 

1. Download the Plug-In if and save it to your computer. The plug-in can be found at http://wordpress.org/extend/plugins/mailchimp-newsletter-widget/
1. Unzip the plugin package. You will find documentation and two PHP files. The WPMC.php and MCAPI.class.php must both be placed on your server.
1. Using FileZilla or your favorite FTP/SFTP client, place these files in your wordpress directory at <wordpress_base>/wp-content/plugins/.
1. Now navigate to your WordPress installation and to the Plugins panel.
1. Click the Activate link to have WordPress utilize the plugin. WordPress will confirm the the plugin was activated and you will see a new menu item in the dashboard entitled MailChimp.
1. See the included PDF or ODT File for configuration insturctions.

== Changelog ==

= 1.0 =
* Initial Release


