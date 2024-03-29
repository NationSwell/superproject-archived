=== VaultPress ===
Contributors: automattic, apokalyptik, briancolinger, josephscott, shaunandrews, xknown, thingalon
Tags: security, malware, virus, archive, back up, back ups, backup, backups, scanning, restore, wordpress backup, site backup, website backup
Requires at least: 2.9.2
Tested up to: 4.1
Stable tag: 1.7.0
License: GPLv2

VaultPress is a subscription service offering realtime backup, automated security scanning, and support from WordPress experts.

== Description ==

[VaultPress](http://vaultpress.com/?utm_source=plugin-readme&utm_medium=description&utm_campaign=1.0) is a real-time backup and security scanning service designed and built by [Automattic](http://automattic.com/), the same company that operates 25+ million sites on WordPress.com.

The VaultPress plugin provides the required functionality to backup and synchronize every post, comment, media file, revision and dashboard settings on our servers. To start safeguarding your site, you need to sign up for a VaultPress subscription.

[wpvideo TxdSIdpO]

For more information, check out [VaultPress.com](http://vaultpress.com/).

== Installation ==

1. Search for VaultPress in the WordPress.org plugin directory and click install. Or, upload the files to your `wp-content/vaultpress/` folder.
2. Visit `wp-admin/plugins.php` and activate the VaultPress plugin.
3. Head to `wp-admin/admin.php?page=vaultpress` and enter your site&rsquo;s registration key. You can purchase your registration key at [VaultPress.com](http://vaultpress.com/plugin/?utm_source=plugin-readme&utm_medium=installation&utm_campaign=1.0)

You can find more detailed instructions at [http://vaultpress.com/](http://help.vaultpress.com/install-vaultpress/?utm_source=plugin-readme&utm_medium=description&utm_campaign=1.0)

== Frequently Asked Questions ==

View our full list of FAQs at [http://help.vaultpress.com/faq/](http://help.vaultpress.com/faq/?utm_source=plugin-readme&utm_medium=faq&utm_campaign=1.0)

= What’s included in each VaultPress plan? =

All plans include Daily or Realtime Backups, Downloadable Archives for Restoring, Vitality Statistics, and the Activity Log.

The Lite plan provides Daily Backups, a 30-day backup archive and automated restores.

The Basic plan provides Realtime Backups to protect your changes as they happen and support services.

The Premium plan provides priority recovery and support services, along with site migration assistance. The Premium plan provides automated security scanning of Core, Theme, and Plugin files.

Update-to-date pricing and features can always be found on the [Plans &amp; Pricing](http://vaultpress.com/plugin/?utm_source=plugin-readme&utm_medium=installation&utm_campaign=1.0) page.

= How many sites can I protect with VaultPress? =

A VaultPress subscription is for a single WordPress site. You can purchase additional subscriptions for each of your WordPress sites, and manage them all with in one place.

= Does VaultPress work with WordPress 3.0 Multisite installs? =

Yes, VaultPress supports Multisite installs. Each site will require its own subscription.

== Changelog ==
= 1.7.0 - 9 Dec 2014 =
* Added an option to disable calls to php_uname, as some hosts don't allow them.

= 1.6.9 - 24 Dec 2014 =
* Tested against WordPress 4.1

= 1.6.8 - 12 Dec 2014 =
* Bugfix: Fall back on HTTP when updating firewall via HTTPS fails. Still warn the user about the security implications.

= 1.6.7 - 1 Dec 2014 =
* Security: More efficient format for internal firewall IPs.

= 1.6.6 - 14 Nov 2014 =
* Security: Fetch service IP updates via HTTPS.
* Feature: Don't send backup notifications while mass-deleting spam.

= 1.6.5 - 4 Sep 2014 =
* Security: Hotfix for the Slider Revolution plugin.

= 1.6.4 - 3 Sep 2014 =
* Bumping the "Tested up to" tag to 4.0

= 1.6.3 - 30 Jul 2014 =
* Bugfix: Make sure existing empty key and secret options are always strings.  This fixes an error when run with HHVM.
* Bugfix: Detect if the plugin has been installed on localhost and show an error.
* CSS Fix: Stop the "Register" button from bouncing around when clicked.

= 1.6.2 - 10 Jul 2014 =
* Feature: Instantly register for a VaultPress trial via Jetpack.
* Bugfix: Make sure the key and secret options are always strings.  This fixes an error when run with HHVM.

= 1.6.1 - 1 Jul 2014 =
* Security: Add a new security hotfix.

= 1.6 - 27 Jun 2014 =
* Bugfix: Better handling for Multisite table prefixes.
* Bugfix: Do not use the deprecated wpdb::escape() method.

= 1.5.9 - 16 Jun 2014 =
* Feature: If available, use command line md5sum and sha1sum to get checksums for large files.

= 1.5.8 - 3 Jun 2014 =
* Security: Add a new security hotfix.

= 1.5.7 - 11 Apr 2014 =
* Bugfix: Avoid PHP 5.4 warnings due to invalid constructor names.
* Security: Add a new security hotfix.

= 1.5.6 - 1 Apr 2014 =
* Bugfix: Avoid PHP 5.4 warnings.
* Bugfix: Some servers with restrictive security filters make database restores fail.
* Feature: Add a new restore method to VaultPress_Database.

= 1.5.2 - 26 Dec 2013 =
* Bugfix: Adding less greedy patterns for cache directories.

= 1.5.1 - 16 Dec 2013 =
* Feature: Adding file exclusion patterns to avoid backing up cache and backup directories.

= 1.5 - 11 Dec 2013 =
* Bugfix: Don't show admin notices on the about page.

= 1.4.9 - 10 Oct 2013 =
* Bugfix: Clean up PHP5 strict warnings.

= 1.4.8 - 15 Jul 2013 =
* Feature: Include styles and images with the plugin instead of loading them externally.

= 1.4.7 - 2 Jul 2013 =
* Bugfix: Some servers have SSL configuration problems, which breaks the plugin when SSL verification is enforced.

= 1.4.6 - 26 Jun 2013 =
* Bugfix: PHP 5.4 notices
* Feature: Add the possibility to ignore frequent updates on some postmeta keys.

= 1.3.9 =
* Feature: Request decoding (base64/rot13)
* Feature: Response encoding (base64/rot13)

= 1.3.8 =
* Bugfix: Validate IPv4-mapped IPv6 addresses in the internal firewall.
* Bugfix: Fix hooks not being properly added under certain circumstances.

= 1.3.7 =
* Bugfix: Protect against infinite loop due to a PHP bug.
* Bugfix: Encode remote ping requests.

= 1.0 =
* First public release!
