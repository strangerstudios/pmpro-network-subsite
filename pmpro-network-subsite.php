<?php
/**
 * Plugin Name: Paid Memberships Pro - Multisite Membership Add On
 * Plugin URI: https://www.paidmembershipspro.com/add-ons/pmpro-network-membership/
 * Description: Manage memberships at the network’s main site (the primary domain of the network) and provide/restrict access on subsites in the network.
 * Version: 0.5.2
 * Author: Paid Memberships Pro
 * Author URI: https://www.paidmembershipspro.com
 * Text-domain: pmpro-network-subsite
 * Domain Path: /languages
 */

/**
 * Deactivate this plugin automatically if we're not on a multisite installation.
 */
function pmpro_multisite_deactivate_self() {
	if ( is_multisite() ) {
		return;
	}

	add_action( 'admin_notices', 'pmpro_multisite_show_admin_warning' );
	deactivate_plugins( plugin_basename( __FILE__ ) );
}
add_action( 'admin_init', 'pmpro_multisite_deactivate_self' );


/**
 * Show an admin notice that the plugin has been deactivated.
 */
function pmpro_multisite_show_admin_warning() {
	if ( ! is_multisite() ) {
		?>
		<div class="error">
			<p><?php esc_html_e( 'The Paid Memberships Pro - Multisite Membership Add-On is compatible only with multisite installations. We have automatically deactivated this plugin.', 'pmpro-network-subsite' ); ?></p>
		</div>
		<?php
	}
}

// Don't run this plugin if it's not a multisite.
if ( ! is_multisite() ) {
	return;
}


// Fake that PMPro is ready since the parent site is handling almost everything.
add_filter( 'pmpro_is_ready', '__return_true' );

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

// Load text domain
function pmpro_multisite_membership_load_textdomain() {
	load_plugin_textdomain( 'pmpro-network-subsite', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'init', 'pmpro_multisite_membership_load_textdomain' );

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
$wpdb->pmpro_membership_ordermeta = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_ordermeta';
$wpdb->pmpro_discount_codes = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes';
$wpdb->pmpro_discount_codes_levels = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes_levels';
$wpdb->pmpro_discount_codes_uses = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes_uses';
$wpdb->pmpro_subscriptions = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_subscriptions';
$wpdb->pmpro_subscriptionmeta = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_subscriptionmeta';
$wpdb->pmpro_groups = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_groups';
$wpdb->pmpro_membership_levels_groups = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_levels_groups';

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
	$wpdb->pmpro_membership_ordermeta = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_ordermeta';
	$wpdb->pmpro_discount_codes = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes';
	$wpdb->pmpro_discount_codes_levels = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes_levels';
	$wpdb->pmpro_discount_codes_uses = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_discount_codes_uses';
	$wpdb->pmpro_subscriptions = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_subscriptions';
	$wpdb->pmpro_subscriptionmeta = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_subscriptionmeta';
	$wpdb->pmpro_groups = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_groups';
	$wpdb->pmpro_membership_levels_groups = pmpro_multisite_membership_get_main_db_prefix() . 'pmpro_membership_levels_groups';
}
add_action( 'init', 'pmpro_multisite_membership_init', 15 );

/**
 * Replace the global $pmpro_pages with the page ID's of the main network site.
 * @return array $pmpro_pages The array of page ID's for the main network site.
 */
function pmpro_multisite_get_parent_site_pages() {
	global $pmpro_pages;

	// Only if the constant is defined try to rewrite.
	if ( ! defined( 'PMPRO_MULTISITE_REWRITE_URLS' ) ||  ! PMPRO_MULTISITE_REWRITE_URLS ) {
		return;
	}

	// Get main site ID
	$main_site_id = pmpro_multisite_get_main_site_ID();

	foreach( $pmpro_pages as $page_slug => $page_id ) {
		if ( $page_slug == 'login' ) {
			continue;
		}
		$pmpro_pages[ $page_slug ] = get_blog_option( $main_site_id, 'pmpro_' . $page_slug . '_page_id' );
	}

}
add_action( 'init', 'pmpro_multisite_get_parent_site_pages', 20 );

/**
 * Filter the pmpro_url URLs when called on the subsite.
 * @return string $url The URL of the main site for equivalent page.
 */
function pmpro_multisite_pmpro_url( $url, $page, $querystring, $scheme ) {
	global $pmpro_pages;

	// Only if the constant is defined try to rewrite URLS.
	if ( ! defined( 'PMPRO_MULTISITE_REWRITE_URLS' ) ||  ! PMPRO_MULTISITE_REWRITE_URLS ) {
		return $url;
	}

	// Get main site URL of the network.
	$main_site_url = get_blog_option( pmpro_multisite_get_main_site_ID(), 'siteurl' );

	// Loop through $pages and generate the URL
	foreach( $pmpro_pages as $page_slug => $page_id ) {
		if ( $page_slug == 'login' ) {
			continue;
		}
		if ( $page == $page_slug && ! empty( $page_id ) ) {
			// Add query arg to the URL 
			$url = add_query_arg( 'page_id', $page_id, $main_site_url);
		}
	}
	return $url;
}
add_filter( 'pmpro_url', 'pmpro_multisite_pmpro_url', 10, 4 );


/**
 * Remove cron jobs from subsites to prevent them from running.
 * @since TBD
 */
function pmpro_multisite_remove_crons() {
	$crons = apply_filters( 'pmpro_multisite_core_crons', pmpro_get_crons() );

	foreach ( $crons as $hook => $cron ) {
		wp_clear_scheduled_hook( $hook );
	}

	// Remove the cron jobs from the main site too.
	remove_filter( 'pre_get_ready_cron_jobs', 'pmpro_handle_schedule_crons_on_cron_ready_check' );
}
add_action( 'admin_init', 'pmpro_multisite_remove_crons' );

/**
 * Reactivate PMPro cron jobs when this plugin is deactivated.
 * @since TBD
 */
function pmpro_multisite_deactivation() {
	pmpro_maybe_schedule_crons();
}
register_deactivation_hook( __FILE__, 'pmpro_multisite_deactivation' );

/**
 * Helper to get the Site ID of the stored site.
 *
 * @return void
 */
function pmpro_multisite_get_main_site_ID() {
	global $wpdb;
	if ( ! get_site_transient( 'pmpro_multisite_membership_main_site_id' ) ) {
		$prefix = pmpro_multisite_membership_get_main_db_prefix();
		$main_site_id = 0;
		
		// Loop through the sites to get the main site ID we're referencing.
		$sites = get_sites();
		foreach( $sites as $site ) {
			$bool_val = SUBDOMAIN_INSTALL;
			$siteurl = $bool_val ? $site->domain : $site->domain . $site->path;

			if ( $wpdb->get_blog_prefix( $site->blog_id ) === $prefix ) {
				$main_site_id = (int) $site->blog_id;
			}
		}

		// Set the transient here for a really long time, we clean it up anyway.
		set_site_transient( 'pmpro_multisite_membership_main_site_id', $main_site_id, YEAR_IN_SECONDS );
	} else {
		$main_site_id = get_site_transient( 'pmpro_multisite_membership_main_site_id' );
	}

	return (int) $main_site_id;
}

/*
	Function to add links to the plugin row meta
*/
function pmpro_multisite_membership_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'pmpro-network-subsite.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/add-ons/pmpro-network-membership/' ) . '" title="' . esc_attr__( 'View Documentation', 'pmpro-network-subsite' ) . '">' . esc_html__( 'Docs', 'pmpro-network-subsite' ) . '</a>',
			'<a href="' . esc_url( 'https://www.paidmembershipspro.com/support/' ) . '" title="' . esc_attr__( 'Visit Customer Support Forum', 'pmpro-network-subsite' ) . '">' . esc_html__( 'Support', 'pmpro-network-subsite' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmpro_multisite_membership_plugin_row_meta', 10, 2 );
