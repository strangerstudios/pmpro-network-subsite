=== Paid Memberships Pro - Multisite Membership Add On ===
Contributors: strangerstudios
Tags: paid memberships pro, pmpro, network, network membership, multisite, wpmu
Requires at least: 4.4
Tested up to: 5.8.1
Stable tag: 0.4.5

Manage memberships at the network's main site (the primary domain of the network) and provide/restrict access on subsites in the network.

== Description ==

This add on allows you to sell memberships at the main site that provide access to members-only content on a site or sites throughout the entire multisite network.

All of the membership levels and users are managed on the main network site. The subsites look to the main network site's database to mirror the membership levels available and to check a user's access.

== Installation ==

1. Install Paid Memberships Pro via Network Admin > Plugins > Add New. Do not "Network Activate" the plugin.
1. On your main network site, activate and configure Paid Memberships Pro by following the Initial Plugin Setup guide.
1. Upload the 'pmpro-network-subsite' directory to the 'wp-content/plugins/' directory of your WordPress Multisite environment.
1. For each site in your network that has member content, navigate to the site's Dashboard > Plugins. Activate Paid Memberships Pro AND the Multisite Membership Add On.
1. DO NOT activate the 'pmpro-network-subsite' plugin on the “Main” site (i.e. where people checkout) of your network.
1. Make sure that the constant PMPRO_NETWORK_MAIN_DB_PREFIX is properly defined for your main network site in your wp-config.php file. For example: define('PMPRO_NETWORK_MAIN_DB_PREFIX', 'wp');

You will now be able to create members-only content on subsites in the network. The membership levels of your main site are mirrored in each subsite.

Read full documentation at https://www.paidmembershipspro.com/add-ons/pmpro-network-membership/

== Frequently Asked Questions ==

= I found a bug in the plugin. =

Please post it in the GitHub issue tracker here: https://github.com/strangerstudios/pmpro-network-subsite/issues

= I need help installing, configuring, or customizing the plugin. =

Please visit our premium support site at http://www.paidmembershipspro.com for more documentation and our support forums.

== Changelog ==
= 0.4.5 - 2021-10-21 =
* BUG FIX: Fixed issue when activated without PMPro active.

= .4.4 - 2020-05-07 =
* BUG FIX: Fixed issues with PMPro v2.3+. Requires PMPro 2.3.2 or higher.

= .4.3 =
* ENHANCEMENT: Improving readme documentation and updating add on name.

= .4.2 =
* BUG FIX: Now hiding the Membership admin bar menu on affected subsites.

= .4.1 =
* ENHANCEMENT: Improving readme documentation and updating add on name.

= .4 =
* ENHANCEMENT: Checks first if PMPRO_NETWORK_MAIN_DB_PREFIX is already defined before defaulting to wp. This allows you to set the constant in your wp-config.php instead of in the plugin file.
* ENHANCEMENT: Now also forwarding the _pmpro_discount_codes, _pmpro_discount_codes_levels, _pmpro_discount_codes_uses, and _pmpro_membership_levelmeta tables.
* NOTE: Added Readme
