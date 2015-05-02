=== SAR One Click Security ===
Contributors: samuelaguilera
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AV35DGUR2BCLS
Tags: security, protection, hardening, firewall, htaccess, spam, comments, bots, registration, login, woocommerce
Requires at least: 3.9.2
Tested up to: 4.2.1
Stable tag: 1.1
License: GPL3

Adds some extra security to your WordPress with only one click.

== Description ==

There's a lot of WordPress security plugins with many many options and pages to setup. And that is fine if you know what to do.
But most of the times, you don't need so much or simply you're not sure about what to set or not.

This plugin adds some extra security to your WordPress with only one click. **No options page, just activate it!**

= Features =

Like many other security plugins SAR One Click Security adds well known .htaccess rules, but only the ones probed to be safe to use in almost any type of site (including WooCommerce stores), to protect your WordPress from common attacks. This allows you to have a safer WordPress without worries about what protection you should be using.

* Turn off ServerSignature directive, that may leak information about your web server.
* Turn off directoy listing, avoiding bad configured hostings to leak your files.
* Blocks public access (from web) to following files that may leak information about your WordPress install: .htacces, license.txt, readme.html, wp-config.php, wp-config-sample.php
* Blocks access to wp-login.php to dummy bots trying to register in WordPress sites that have registration disabled (when they try to register are redirected to wp-login.php by WordPress.
* Blocks requests looking for timthumb.php, reducing server load caused by bots trying to find it. (*)
* Blocks TRACE and TRACK request methods, preventing XST attacks.
* Blocks direct posting to wp-comments-post.php (most spammers do this) and access with blank User Agent, reducing spam comments a lot and also server load.
* Blocks direct access to PHP files in wp-content directory (this includes subdirectories like plugins or themes). Protecting you from a huge number of 0day exploits.
* Blocks direct POST to wp-login.php and access with blank User Agent, preventing most brute-force attacks and reducing server load.

(*) If your theme uses TimThumb, you can disable that blocking rule, check FAQ before installing the plugin to see how.

= Requirements =

* WordPress 3.9.2 or higher. (Works with WordPress network/multisite installation).
* Apache2 web server

It has been tested in many servers including large providers like HostGator, Godaddy and 1&1 with optimal results, and it will work fine in any decent hosting service (that allows you to set options from .htaccess files).

Anyway, if you get any problem after activating the plugin, check FAQ for instructions on how to manually uninstall it. Or maybe check it before install the plugin if you're not sure about your hosting provider policy about .htacces

= Usage =

To apply above mentioned security rules simply install and activate the plugin, no options page, no user setup!

If you need to remove the security rules for some reason, simply deactivate the plugin. If you want to add them again, activate the plugin again, that easy ;)

And remember, **if your theme uses TimThumb, check FAQ before installing the plugin**.

= Known issues =

There are some plugins that uses direct access to .php files in the wp-conteng/plugins/ directory. That, in my honest opinion, is bad practice and should be avoided.

If you use some of these plugins, you can't use this.

Some plugins detected with this behaviour:

* Google Doc Embedder
* Yet Another Related Posts Plugin (YARPP)
 	
== Installation ==

* Extract the zip file and just drop the contents in the <code>wp-content/plugins/</code> directory of your WordPress installation (or install it directly from your dashboard) and then activate it from Plugins page.

== Frequently Asked Questions ==

= Can I use this plugin together with Wordfence Security or any other security plugin? =

If you use a plugin like Wordfence Security, or any other security plugin that gives you similar functionality (these that writes rules to .htaccess), you should not be using this plugin or another security plugin. **Using more than one security plugin at once can give you unexpected results**.

Anyway, SAR One Click Security is a pretty friendly plugin, it adds his security rules without interfering in any other existing content in your .htacces file. In fact I'm using SAR One Click Security + All In One WP Security & Firewall in some sites that I manage.

So technically you can do it if you know what you're doing, but if you do you're at your own risk. No support for problems due to the use of another security plugin together with this one.

= I already have some custom rules in my .htaccess, will the plugin remove them? =

The plugin doesn't touch any of the current content of your .htaccess file, it only adds **his own rules** when you activate it, and removes **his own rules** when you deactivate it.

= I'm not sure of what server is running my hosting, can I install this to try? =

Yes. If you install this plugin in another server rather than Apache (nginx, IIS, etc...) the plugin only will show a notice in your WordPress admin dashboard, no modifications will be made.

= My theme uses TimThumb script, can I use this plugin? =

Yes. But **you must** add the following line to your wp-config.php file **BEFORE** activating the plugin.

define('SAR_ALLOW_TIMTHUMB', '');

That will allow you to use all features of the plugin excerpt for the TimThumb blocking rule.

If you activated the plugin before inserting the above line in your wp-config.php file, simply deactivate/activate the plugin to allow access for timthumb.php and thumb.php (another file name used for TimThumb).

And if you want to turn off TimThumb support, simply remove the previous mentioned line and deactivate/activate the plugin.

= After activating the plugin I get an error 500 page, what can I do? =

If you get an error 500 page after activating the plugin this means that your hosting provider doesn't allow you to set some (or any) settings from your .htaccess

You can manually uninstall plugin's .htacces rules by open your favorite FTP client and removing all content between **# BEGIN SAR One Click Security** and **# END SAR One Click Security** in your .htaccess file located in the root directory of your WordPress installation.
And doing the same in the .htaccess file located in the wp-content dir (or deleting the file if no more content on it).

== Changelog ==

= 1.1 =

* Added support for themes using timthumb.php, check FAQ before installing the plugin to see how.
* Added blocking of access to wp-login.php with blank User Agent and direct posting of credentials
* Improved code that handles .htaccess at wp-content
* Greatly improved some .htaccess rules 

= 1.0.6 =

* Added translation support.
* Added spanish (es_ES) translation.
* Added routine for future upgrades.
* Added support for existing .htacces in wp-content before plugin activation.

= 1.0.1 =

* Added a check to see if server running the plugin is Apache, if not don't do anything, to avoid creating useless files in not supported servers.
* Also added an admin notice to show to users that installed the plugin in a not supported server.

= 1.0 =

* First release.

== Upgrade notice ==

= 1.1 =

* Recommended upgrade! See changelog.

= 1.0.1 =

* Minor improvement for people that install it in a not supported server.
