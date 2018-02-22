<?php

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );
/**
 *
 */
class PMPro_Manage_Multisite {
	/**
	 * Description
	 *
	 * @return type
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'multisite_membership_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'select_site_load_scripts' ) );
		add_action( 'wp_ajax_select_site_get_results', array( __CLASS__, 'select_site_ajax' ) );
		add_action( 'wp_before_admin_bar_render', array( __CLASS__, 'remove_adminbar' ), 999 );
	}

	/**
	 * [select_site_load_scripts description]
	 *
	 * @return [type] [description]
	 */
	public static function remove_adminbar() {
		global $wp_admin_bar;
		$id = 'paid-memberships-pro';
		$wp_admin_bar->remove_menu( $id );
	}

	/**
	 * [select_site_load_scripts description]
	 *
	 * @return [type] [description]
	 */
	public static function select_site_load_scripts() {
		$screen = get_current_screen();
		if ( 'toplevel_page_pmpro-membership' === $screen->id || 'memberships_page_pmpro-multisite-membership' === $screen->id ) {
			wp_enqueue_style( 'multisite-membership', plugin_dir_url( __FILE__ ) . 'css/multisite-membership.css' );
		}
		wp_enqueue_script( 'select-site', plugin_dir_url( __FILE__ ) . 'js/select-site.js', array( 'jquery' ) );
		wp_localize_script(
			'select-site', 'select_site_vars', array(
				'select_site_nonce' => wp_create_nonce( 'select-site-nonce' ),
			)
		);
	}

	/**
	 * [multisite_membership_page description]
	 *
	 * @return [type] [description]
	 */
	public static function multisite_membership_page() {
		global $wpdb;
		self::membership_header();
		?>
		<div class="wrap">
		<h2><?php _e( 'PMPro Multisite Membership', 'pmpro-multisite-membership' ); ?></h2>
		<p>You have activated the <strong>Multisite Membership Add On</strong> on this site, which means that you will be using PMPro settings from another site in your Network to control site access.</p>

		<p>In order to finish setting up the Multisite Membership Add On, you'll need to check that you have the proper prefix for the site controlling the settings in wp-config.php. Select the site which you will use as the Main site and click the button to get the prefix.</p>
		<form id="select-site-form" action="" method="POST">
			<div><strong><label>Select PMPro Domain</strong>
				<?php echo self::render_sites_dropdown(); ?></label>
				<input type="submit" name="select-site-submit" id="select_site_submit" class="button-primary" value="<?php _e( 'Get Site Prefix', 'pmpro-multisite-membership' ); ?>"/>
				<img src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>" class="waiting" id="select_site_loading" style="display:none;"/>
			</div>
		</form>
		<div id="select_site_results"></div>

		<p>You'll know that you have your prefix defined correctly when the value above and the value below match. Currently your prefix is:</p>
		<h4><?php echo '<pre>define( \'PMPRO_NETWORK_MAIN_DB_PREFIX\', \'' . PMPRO_NETWORK_MAIN_DB_PREFIX . '\' );</pre>'; ?></h4>
		<?php
		$terms = get_terms(
			array(
				'taxonomy' => 'category',
				'hide_empty' => false,
			)
		);
		echo '<pre>';
		print_r( $terms );
		echo '<pre>';
		?>
		</div>
		<?php
	}

	/**
	 * [select_site_ajax description]
	 *
	 * @return [type] [description]
	 */
	public static function select_site_ajax() {
		global $wpdb;
		$array = array();
		$array = $_POST;
		$site = intval( $array['site'] );
		$info = get_blog_details( $site );
		echo '<h4>Add this code to your wp-config.php</h4>';

		switch_to_blog( $site );

		echo '<h4><pre>define( \'PMPRO_NETWORK_MAIN_DB_PREFIX\', \'' . substr( $wpdb->prefix, 0, -1 ) . '\' );</pre></h4>';
		restore_current_blog();

		echo '<h4>Membership is controlled here <a href="' . $info->siteurl . '/wp-admin/admin.php?page=pmpro-membershiplevels">' . $info->siteurl . '/wp-admin/admin.php?page=pmpro-membershiplevels</a></h4>';

		die();
	}

	/**
	 * [get_sites description]
	 *
	 * @return [type] [description]
	 */
	public static function get_sites() {
		$sites = get_sites();
		return $sites;
	}

	public static function pluck_sites() {
		$args = array(
			'orderby' => 'id',
			'order' => 'DESC',
		);
		$sites = get_sites( $args );
		$site_list = wp_list_pluck( $sites, 'domain', 'blog_id' );
		return $site_list;
	}

	/**
	 * Render sites dropdown.
	 *
	 * Allows the content to be overriden without having to rewrite the wrapper.
	 */
	public static function render_sites_dropdown() {
		$sites = self::get_sites();
		?>
		<label>
		<select class="site-dropdown-select" name="sitevalue">
			<?php
			foreach ( $sites as $site ) {
				$bool_val = SUBDOMAIN_INSTALL;
				$siteurl = $bool_val ? $site->domain : $site->domain . $site->path;
				printf(
					'<option value="%s" %s>%s</option>',
					$site->blog_id,
					selected( $site->blog_id, $site->blog_id, false ),
					$siteurl
				);

			}
			?>
			</select>
			</label>
		<?php
	}

	/**
	 * [multisite_membership_menu description]
	 *
	 * @return [type] [description]
	 */
	public static function multisite_membership_menu() {
		add_menu_page( __( 'Memberships', 'pmpro-multisite-membership' ), __( 'Memberships', 'pmpro-multisite-membership' ), 'manage_options', 'pmpro-multisite-membership.php', array( 'PMPro_Manage_Multisite', 'multisite_membership_page' ), 'dashicons-groups' );
	}

	/**
	 * [multisite_membership description]
	 *
	 * @return [type] [description]
	 */
	public static function multisite_membership() {
		echo '<div class="wrap admin">';
		echo '<h2>' . __FUNCTION__ . '</h2>';
		PMPro_Manage_Multisite::membership_header();
	}

	/**
	 * [membership_header description]
	 *
	 * @return [type] [description]
	 */
	public static function membership_header() {
		echo '<div class="wrap pmpro_admin pmpro-admin-header addon">';
		?>
		<div class="pmpro_banner">
		<a class="pmpro_logo" title="Paid Memberships Pro - Membership Plugin for WordPress" target="_blank" href="<?php echo pmpro_https_filter( 'http://www.paidmembershipspro.com' ); ?>"><img src="<?php echo PMPRO_URL; ?>/images/Paid-Memberships-Pro.png" width="350" height="75" border="0" alt="Paid Memberships Pro(c) - All Rights Reserved" /></a>
		<div class="pmpro_meta"><span class="pmpro_tag pmpro_tag-grey">v<?php echo PMPRO_VERSION; ?></span> | <a target="_blank" class="pmpro_tag pmpro_tag-tag-blue" href="<?php echo pmpro_https_filter( 'http://www.paidmembershipspro.com' ); ?>"><?php _e( 'Plugin Support', 'pmpro-multisite-membership' ); ?></a> | <a target="_blank" class="pmpro_tag pmpro_tag-blue" href="http://www.paidmembershipspro.com/forums/"><?php _e( 'User Forum', 'pmpro-multisite-membership' ); ?></a></div>
		<br style="clear:both;" />
	<?php
	echo '</div>';
	}
}

