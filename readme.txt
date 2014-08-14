=== SAR One Click Security ===
Contributors: samuelaguilera
Tags: security, protection, htaccess, spam, comments, bots, registration, login, woocommerce
Requires at least: 3.9.2
Tested up to: 3.9.2
Stable tag: 1.0
License: GPL3

Adds some extra security to your WordPress with only one click. No options page, just activate it!

== Description ==

There's a lot of WordPress security plugins with many many options and pages to setup. And that is fine if you know what to do.
But most of the times, you don't need so much or simply you're not sure about what to set or not.

This plugin adds some extra security to your WordPress with only one click. **No options page, just activate it!**

Like many other security plugins SAR One Click Security uses well known .htaccess rules, but only the ones probed to be safe to use in almost any type of site (including WooCommerce stores), to protect your WordPress from common attacks. This allows you to have a safer WordPress without worries about what protection you should be using.

It has been tested in many servers including large providers like HostGator and Godaddy with optimal results, and it will work fine in any decent hosting service (that allows you to set options from .htaccess files).

Anyway, if you get any problem after activating the plugin, check FAQ for instructions on how to manually uninstall it. Or maybe check it before install the plugin if you're not sure about your hosting provider policy about .htacces

= Features =

* Turn off ServerSignature directive, that may leak information about your web server.
* Turn off directoy listing, avoiding bad configured hostings to leak your files.
* Blocks access to following files, thay may leak information about your WordPress install: .htacces, license.txt, readme.html, wp-confing.php, wp-config-sample.php
* Stops dummy bots trying to register in WordPress sites that have registration disabled.
* Blocks requests looking for timthumb.php (this means that you can't use a theme with TimThumb and this plugin together).
* Blocks TRACE and TRACK request methods.
* Blocks direct posting to wp-comments-post.php (most spammers do this).
* Blocks direct access to PHP files in wp-content directory (this includes subdirectories like plugins or themes). Protecting you from a huge number of 0day exploits.

= Requirements =

* WordPress 3.9.2 or higher.
* Apache2 web server
* A theme that doesn't use Timthumb script (any decent recent theme does not use it).

= Usage =

Simply install and activate, no options page.
 	
== Installation ==

* Extract the zip file and just drop the contents in the <code>wp-content/plugins/</code> directory of your WordPress installation (or install it directly from your dashboard) and then activate it from Plugins page.

== Frequently Asked Questions ==

= After activating the plugin I get an error 500 page, what can I do? =

If you get an error 500 page after activating the plugin this means that your hosting provider doesn't allow you to set some (or any) settings from your .htaccess

You can manually uninstall plugin's .htacces rules by open your favorite FTP client and removing all content between # BEGIN SAR One Click Security and # END SAR One Click Security in your .htaccess file located in the root directory of your WordPress installation.
And doing the same in the .htaccess file located in the wp-content dir (or deleting the file if no more content on it).

== Changelog ==

= 1.0 =

* First release.
