<?php
/**
 * Plugin Name: Paid Memberships Pro - Multisite Membership Add On
 * Plugin URI: https://www.paidmembershipspro.com/add-ons/pmpro-network-membership/
 * Description: Manage memberships at the network’s main site (the primary domain of the network) and provide/restrict access on subsites in the network.
 * Version: 0.4.5
 * Author: Paid Memberships Pro
 * Author URI: https://www.paidmembershipspro.com
 * Text-domain: pmpro-multisite-membership
 */

/** 
 * Get the Main DB Prefix
 *
 * Thanks, Bainternet on Stack Exchange for the code to grab the DB prefix for the current site:
 * https://wordpress.stackexchange.com/a/26467/3652
 */
function pmpro_multisite_membership_get_main_db_prefix() {
	$main_db_prefix = get_site_option( 'pmpro_multisite_membership_main_db_prefix' );

	if( empty( $main_db_prefix ) ) {
		// checking if they used this constant for backwards compatability
		if( defined( 'PMPRO_NETWORK_MAIN_DB_PREFIX' ) ) {
			$main_db_prefix = PMPRO_NETWORK_MAIN_DB_PREFIX . '_';		//when we used constants, the trailing _ wasn't included
		} else {
			global $wpdb, $current_site;
			$main_db_prefix = $wpdb->get_blog_prefix( $wpdb->get_var( $wpdb->prepare ( "SELECT blogs.blog_id FROM $wpdb->blogs blogs WHERE blogs.domain = '%s' AND blogs.path = '%s' ORDER BY blogs.blog_id ASC LIMIT 1", $current_site->domain, $current_site->path ) ) );
		}
		update_site_option( 'pmpro_multisite_membership_main_db_prefix', $main_db_prefix );
	}

	return $main_db_prefix;
}

include( 'inc/class-pmpro-manage-multisite.php' );
PMPro_Manage_Multisite::init();

/*
	Make sure this plugin loads after Paid Memberships Pro
*/
function pmpro_multisite_membership_activated_plugin() {
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace( '/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR . '/$2', __FILE__ );
	$this_plugin = plugin_basename( trim( $wp_path_to_this_file ) );

	// load plugins
	$active_plugins = get_option( 'active_plugins' );

	// where am I?
	$this_plugin_key = array_search( $this_plugin, $active_plugins );

	// move to end
	array_splice( $active_plugins, $this_plugin_key, 1 );
	$active_plugins[] = $this_plugin;

	// update option
	update_option( 'active_plugins', $active_plugins );
}
add_action( 'activated_plugin', 'pmpro_multisite_membership_activated_plugin' );

/*
	Now update wpdb tables.

	(Updated again in init to get all cases.)
*/
global $wpdb;
$wpdb->pmpro_memberships_users = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_memberships_users';
$wpdb->pmpro_membership_levels = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_levels';
$wpdb->pmpro_membership_levelmeta = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_levelmeta';
$wpdb->pmpro_membership_orders = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_orders';
$wpdb->pmpro_discount_codes = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes';
$wpdb->pmpro_discount_codes_levels = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes_levels';
$wpdb->pmpro_discount_codes_uses = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes_uses';

// get levels again
function pmpro_multisite_membership_init_get_levels() {
	global $wpdb, $membership_levels;
	if ( function_exists( 'pmpro_getAllLevels' ) ) {
		$membership_levels = pmpro_getAllLevels( true, true, true );
	}
}
add_action( 'init', 'pmpro_multisite_membership_init_get_levels', 1 );

/*
	Hide admin stuff
*/
function pmpro_multisite_membership_init() {
	// remove admin pages
	remove_action( 'admin_menu', 'pmpro_add_pages' );
	remove_action( 'admin_bar_menu', 'pmpro_admin_bar_menu' );

	// remove membership level from edit users page
	remove_action( 'show_user_profile', 'pmpro_membership_level_profile_fields' );
	remove_action( 'edit_user_profile', 'pmpro_membership_level_profile_fields' );
	remove_action( 'profile_update', 'pmpro_membership_level_profile_fields_update' );

	// update wpdb tables again
	global $wpdb;
	$wpdb->pmpro_memberships_users = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_memberships_users';
	$wpdb->pmpro_membership_levels = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_levels';
	$wpdb->pmpro_membership_levelmeta = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_levelmeta';
	$wpdb->pmpro_membership_orders = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_orders';
	$wpdb->pmpro_discount_codes = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes';
	$wpdb->pmpro_discount_codes_levels = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes_levels';
	$wpdb->pmpro_discount_codes_uses = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes_uses';
}
add_action( 'init', 'pmpro_multisite_membership_init', 15 );

/*
	Function to add links to the plugin row meta
*/
function pmpro_multisite_membership_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'pmpro-network-subsite.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/add-ons/pmpro-network-membership/' ) . '" title="' . esc_attr( __( 'View Documentation', 'pmpro-multisite-membership' ) ) . '">' . __( 'Docs', 'pmpro-multisite-membership' ) . '</a>',
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/support/' ) . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro-multisite-membership' ) ) . '">' . __( 'Support', 'pmpro-multisite-membership' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmpro_multisite_membership_plugin_row_meta', 10, 2 );
