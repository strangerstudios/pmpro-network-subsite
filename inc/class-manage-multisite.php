<?php

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );
/**
 *
 */
class Manage_Multisite {
	/**
	 * Description
	 *
	 * @return type
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'pmpro_multisite_membership_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'select_site_load_scripts' ) );
		add_action( 'wp_ajax_select_site_get_results', array( __CLASS__, 'select_site_ajax' ) );
	}

	/**
	 * [admin_css_bottom description]
	 *
	 * @return [type] [description]
	 */
	public static function admin_css_bottom() {
		// url( 'http://first.pmp-ms.rox/wp-content/plugins/paid-memberships-pro/images/Paid-Memberships-Pro_watermark.png' );
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
	 * [pmpro_multisite_membership_page description]
	 *
	 * @return [type] [description]
	 */
	public static function pmpro_multisite_membership_page() {
		global $wpdb;
		self::pmpro_membership_header();
		?>
		<div class="wrap">
			<h2><?php esc_attr_e( 'PMPro Multisite Membership', 'selectsite' ); ?></h2>
			<p>You have activated the Multisite Membership Add On on this site, which means that you will be using PMPro settings from another site in your Network to control site access.</p>
			<p>In order to finish setting up the Multisite Membership Add On, you'll need to check that you have the proper prefix for the site controlling the settings in wp-config.php.</p>
			<form id="select-site-form" action="" method="POST">
				<div><strong><lqbel>Select PMPro Domain</strong>
					<?php echo self::render_sites_dropdown(); ?></lqbel>
					<input type="submit" name="select-site-submit" id="select_site_submit" class="button-primary" value="<?php esc_attr_e( 'Get Site Prefix', 'selectsite' ); ?>"/>
					<img src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>" class="waiting" id="select_site_loading" style="display:none;"/>
				</div>
			</form>
			<div id="select_site_results"></div>


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
					printf(
						'<option value="%s" %s>%s</option>',
						$site->blog_id,
						selected( $site->blog_id, $site->blog_id, false ),
						$site->domain
					);
				}
				?>
			</select>
		</label>
	<?php
	}

	/**
	 * [pmpro_multisite_membership_menu description]
	 *
	 * @return [type] [description]
	 */
	public static function pmpro_multisite_membership_menu() {
		add_menu_page( __( 'Memberships', 'paid-memberships-pro' ), __( 'Memberships', 'paid-memberships-pro' ), 'manage_options', 'pmpro-multisite-membership.php', array( 'Manage_Multisite', 'pmpro_multisite_membership_page' ), 'dashicons-groups' );
	}

	/**
	 * [pmpro_multisite_membership description]
	 *
	 * @return [type] [description]
	 */
	public static function pmpro_multisite_membership() {
		echo '<div class="wrap pmpro_admin">';
		echo '<h2>' . __FUNCTION__ . '</h2>';
		Manage_Multisite::pmpro_membership_header();
	}

	/**
	 * [pmpro_membership_header description]
	 *
	 * @return [type] [description]
	 */
	public static function pmpro_membership_header() {
		echo '<div class="wrap pmpro_admin pmpro-admin-header addon">';
	?>
		<div class="pmpro_banner">
			<a class="pmpro_logo" title="Paid Memberships Pro - Membership Plugin for WordPress" target="_blank" href="<?php echo pmpro_https_filter( 'http://www.paidmembershipspro.com' ); ?>"><img src="<?php echo PMPRO_URL; ?>/images/Paid-Memberships-Pro.png" width="350" height="75" border="0" alt="Paid Memberships Pro(c) - All Rights Reserved" /></a>
			<div class="pmpro_meta"><span class="pmpro_tag-grey">v<?php echo PMPRO_VERSION; ?></span><a target="_blank" class="pmpro_tag-blue" href="<?php echo pmpro_https_filter( 'http://www.paidmembershipspro.com' ); ?>"><?php _e( 'Plugin Support', 'paid-memberships-pro' ); ?></a><a target="_blank" class="pmpro_tag-blue" href="http://www.paidmembershipspro.com/forums/"><?php _e( 'User Forum', 'paid-memberships-pro' ); ?></a></div>
			<br style="clear:both;" />
	<?php
		echo '</div>';
	}
}
