<?php

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );
/**
 *
 */
class PMPro_Manage_Multisite {
	/**
	 * Run on init to setup our hooks and filters.
	 *
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'wp_before_admin_bar_render', array( __CLASS__, 'remove_admin_bar' ), 999 );
	}

	/**
	 * Add menu page linking to settings for this add on.
	 *
	 */
	public static function add_admin_menu() {

		add_menu_page( esc_html__( 'Settings', 'pmpro-network-subsite' ), esc_html__( 'Memberships', 'pmpro-network-subsite' ), 'manage_options', 'pmpro-network-subsite', array( __CLASS__, 'settings_page' ), 'dashicons-groups' );

		// Add submenu advanced settings page.
		add_submenu_page( 'pmpro-network-subsite', 'Settings', 'Settings', 'read', 'pmpro-network-subsite',  array( __CLASS__, 'settings_page' ) ); //Add this so we can have a menu slug for the main menu link
		add_submenu_page( 'pmpro-network-subsite', esc_html__( 'Advanced Settings', 'pmpro-multisite-membership' ), esc_html__( 'Advanced Settings', 'pmpro-multisite-membership' ), 'manage_options', 'pmpro-advancedsettings', 'pmpro_advancedsettings' );

		// Only load the styling when we're on one of our admin pages.
		if ( ! empty( $_REQUEST['page'] ) && ( $_REQUEST['page'] == 'pmpro-network-subsite' || $_REQUEST['page'] == 'pmpro-advancedsettings' ) ) {
			?>
			<style>
			.pmpro_admin .nav-tab-wrapper, .pmpro_admin .subsubsub {display:none;}
			.pmpro_admin_section-checkout-settings {display:none;}
			.pmpro_admin-pmpro-advancedsettings hr {display:none;}
			.pmpro-nav-primary, .pmpro-nav-secondary {display:none;}
			</style>
			<?php
		}
	}
	/**
	 * Remove the admin bar on subsites
	 *
	 */
	public static function remove_admin_bar() {
		global $wp_admin_bar;
		$id = 'paid-memberships-pro';
		$wp_admin_bar->remove_menu( $id );
	}

	/**
	 * Render the settings page.
	 *
	 */
	public static function settings_page() {
		global $wpdb;

		// Process the form.
		if( isset( $_POST['main_db_prefix'] ) && check_admin_referer( 'pmpro_multisite_membership_settings', 'pmpro_multisite_membership_settings_nonce' ) ) {
			$main_db_prefix = sanitize_text_field( $_POST['main_db_prefix'] );
			update_site_option( 'pmpro_multisite_membership_main_db_prefix', $main_db_prefix );
			delete_site_transient( 'pmpro_multisite_membership_main_site_id' ); // Clear the transient on save.
			?>
			<div class="notice notice-success is-dismissible">
		        <p><?php esc_html_e( 'The source site has been updated. Make sure that PMPro IS active on that site and the Multisite Membership Add On IS NOT active there.', 'pmpro-network-subsite' ); ?></p>
		    </div>
			<?php
		}

		if( defined( 'PMPRO_DIR' ) ) { require_once( PMPRO_DIR . '/adminpages/admin_header.php' ); }

		// Show the form.
		?>
		<div class="wrap">
		<h2><?php esc_html_e( 'PMPro Multisite Membership Settings', 'pmpro-network-subsite' ); ?></h2>
		<p><?php printf( esc_html__( 'You have activated the %s on this site, which means that you will be using PMPro settings from another site in your Network to control site access.', 'pmpro-network-subsite' ), '<strong>' . __( 'Multisite Membership Add On', 'pmpro-network-subsite' ) . '</strong>' );?></p>

		<p><?php esc_html_e( 'Select the site you would like to get PMPro level data from and click Update.', 'pmpro-network-subsite' );?></p>

		<form id="select-site-form" action="" method="POST">
			<div>
				<?php wp_nonce_field( 'pmpro_multisite_membership_settings', 'pmpro_multisite_membership_settings_nonce' ); ?>
				<select class="site-dropdown-select" name="main_db_prefix" id="main_db_prefix">
				<?php
					$sites = get_sites();
					foreach ( $sites as $site ) {
						$bool_val = SUBDOMAIN_INSTALL;
						$siteurl = $bool_val ? $site->domain : $site->domain . $site->path;
						printf(
							'<option value="%s" %s>%s</option>',
							$wpdb->get_blog_prefix($site->blog_id),
							selected( $wpdb->get_blog_prefix($site->blog_id), pmpro_multisite_membership_get_main_db_prefix(), false ),
							$siteurl
						);
					}
				?>	
				</select>
				<input type="submit" name="select-site-submit" id="select_site_submit" class="button-primary" value="<?php esc_attr_e( 'Update', 'pmpro-network-subsite' ); ?>"/>
			</div>
		</form>
		</div>
		<style>
			.error { display:none }
		</style>
		<?php

		if( defined( 'PMPRO_DIR' ) ) { require_once( PMPRO_DIR . '/adminpages/admin_footer.php' ); }
	}
}