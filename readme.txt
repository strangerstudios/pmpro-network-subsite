=== Paid Memberships Pro Network Subsite Helper ===
Contributors: strangerstudios
Tags: paid memberships pro, pmpro, network sites, wpmu
Requires at least: 3.0
Tested up to: 3.6.1
Stable tag: .4

For sites running WordPress Multsite, allows you to manage memberships at the “Main” Network site and provide/restrict access on other Network Subsites.

== Description ==

With the Paid Memberships Pro plugin and this plugin activated, new users will be able to choose a site name and title at checkout. A site will be created for them after registering. If they cancel their membership or have it removed, the site will be deactivated. If they sign up for a membership again, the site will be reactivated.

== Installation ==

1. DO NOT “network activate” Paid Memberships Pro, PMPro Network Subsite, or any PMPro add ons. You should always activate the plugin on the individual site.
1. Make sure you have properly configured Network Sites on your WP install.
1. Make sure you have the Paid Memberships Pro plugin installed, activated, and setup on the main site where users will checkout for membership.
1. Upload the ‘pmpro-network-subsite’ directory to the ‘/wp-content/plugins/’ directory of your site.
1. Edit the PMPRO_NETWORK_MAIN_DB_PREFIX definition at the top of pmpro-network-subsite-helper.php if your main site uses a different DB prefix.
1. DO NOT activate the pmpro-network-subsite plugin on the “Main” site (i.e. where people checkout) of your network.
1. DO Activate the plugin through the ‘Plugins’ menu in WordPress on each subsite that should mirror memberships for the main site.
1. Paid Memberships Pro must be active and configured on the “Main” Network site.
1. Paid Memberships Pro must be activate but does not need to be configured on the other Network Subsites.

== Frequently Asked Questions ==

= I found a bug in the plugin. =

Please post it in the GitHub issue tracker here: https://github.com/strangerstudios/pmpro-network-subsite/issues

= I need help installing, configuring, or customizing the plugin. =

Please visit our premium support site at http://www.paidmembershipspro.com for more documentation and our support forums.

== Changelog ==
= .4 =
* ENHANCEMENT: Checks first if PMPRO_NETWORK_MAIN_DB_PREFIX is already defined before defaulting to wp. This allows you to set the constant in your wp-config.php instead of in the plugin file.
* ENHANCEMENT: Now also forwarding the _pmpro_discount_codes, _pmpro_discount_codes_levels, _pmpro_discount_codes_uses, and _pmpro_membership_levelmeta tables.
* NOTE: Added Readme