<?php
/**
 * WishList Member Version Requirements Check
 *
 * @package WishListMember\Classes
 */

/**
 * Requirements not met Class
 */
class WishListMember3_Requirements_Not_Met {

	/**
	 * Plugin File
	 *
	 * @var string
	 */
	public $plugin_file = '';
	/**
	 * Update URL
	 *
	 * @var string
	 */
	public $update_url = '';
	/**
	 * License Key
	 *
	 * @var [type]
	 */
	public $license_key = '';
	/**
	 * Previous version
	 *
	 * @var [type]
	 */
	public $previous_version = '';
	/**
	 * Message
	 *
	 * @var [type]
	 */
	public $message = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wp_version, $wpdb;

		// grab plugin file.
		$this->plugin_file = WLM_PLUGIN_DIR . '/wpm.php';

		// grab license key.
		$this->license_key = wlm_trim( $wpdb->get_var( "SELECT `option_value` FROM `{$wpdb->prefix}wlm_options` WHERE `option_name` = 'LicenseKey'" ) );

		// grab previous version.
		$this->previous_version = wlm_trim( $wpdb->get_var( "SELECT `option_value` FROM `{$wpdb->prefix}wlm_options` WHERE `option_name` = 'CurrentVersion'" ) );

		// knowledgebase links.
		$php_kb_link = 'https://help.wishlistproducts.com/knowledge-base/required-php-version-for-wishlist-member/';
		$wp_kb_link  = 'https://help.wishlistproducts.com/knowledge-base/required-wordpress-version/';

		// check php and wp version compatibility.
		$php = version_compare( PHP_VERSION, WLM_MIN_PHP_VERSION, '<' );
		$wp  = version_compare( $wp_version, WLM_MIN_WP_VERSION, '<' );

		if ( $php && $wp ) {
			$this->message = sprintf(
				'<p><strong>%s</strong></p><p>%s</p>',
				// Translators: 1: WishList Member version number, 2: PHP version number, 3: WordPress version number.
				sprintf( __( 'WishList Member %1$s requires PHP %2$s or higher and WordPress %3$s or higher in order to function.', 'wishlist-member' ), WLM_PLUGIN_VERSION, WLM_MIN_PHP_VERSION, WLM_MIN_WP_VERSION ),
				// Translators: 1: WishList Member version number.
				sprintf( __( 'It appears you are currently running a <a href="%2$s" target="_blank">lower version of PHP</a> and a <a href="%3$s" target="_blank">lower version of WordPress</a> so WishList Member is currently not functioning. Please upgrade PHP on your hosting account and upgrade WordPress on this site to enable WishList Member %1$s to function.', 'wishlist-member' ), WLM_PLUGIN_VERSION, $php_kb_link, $wp_kb_link )
			);
		} elseif ( $php ) {
			$this->message = sprintf(
				'<p><strong>%s</strong></p><p>%s</p>',
				// Translators: 1: WishList Member version number, 2: PHP version number.
				sprintf( __( 'WishList Member %1$s requires PHP %2$s or higher in order to function.', 'wishlist-member' ), WLM_PLUGIN_VERSION, WLM_MIN_PHP_VERSION ),
				// Translators: 1: WishList Member version number, 2: PHP version number.
				sprintf( __( 'It appears you are currently running a <a href="%3$s" target="_blank">lower version of PHP</a> so WishList Member is currently not functioning. You will need to upgrade PHP on your hosting account to %2$s or higher in order to enable WishList Member %1$s to function.', 'wishlist-member' ), WLM_PLUGIN_VERSION, WLM_MIN_PHP_VERSION, $php_kb_link )
			);
		} elseif ( $wp ) {
			$this->message = sprintf(
				'<p><strong>%s</strong></p><p>%s</p>',
				// Translators: 1: WishList Member version number, 2: WordPress version number.
				sprintf( __( 'WishList Member %1$s requires WordPress %2$s or higher in order to function.', 'wishlist-member' ), WLM_PLUGIN_VERSION, WLM_MIN_WP_VERSION ),
				// Translators: 1: WishList Member version number, 2: WordPress version number.
				sprintf( __( 'It appears you are currently running a <a href="%3$s" target="_blank">lower version of WordPress</a> so WishList Member is currently not functioning. You will need to upgrade WordPress on your site to %2$s or higher in order to enable WishList Member %1$s to function.', 'wishlist-member' ), WLM_PLUGIN_VERSION, WLM_MIN_WP_VERSION, $wp_kb_link )
			);
		}

		$this->message .= '<p>' . wp_kses_data( 'Note: You can <a href="___wlm3updateurl___">click here</a> to roll back to your previous version of WishList Member in the meantime.', 'wishlist-member' ) . '</p>';

		// Menu.
		add_action( 'admin_menu', array( $this, 'menu' ) );

		// Admin Notice.
		add_action( 'admin_notices', array( $this, 'notice' ) );

		// Rollback.
		$action = trim( sanitize_text_field( wp_unslash( isset( wlm_get_data()['action'] ) ? wlm_get_data()['action'] : '' ) ) );
		$plugin = trim( sanitize_text_field( wp_unslash( isset( wlm_get_data()['plugin'] ) ? wlm_get_data()['plugin'] : '' ) ) );
		if ( $action && $plugin && 'upgrade-plugin' === $action && $this->plugin_file === $plugin ) {
			add_filter( 'site_transient_update_plugins', array( $this, 'update_plugin_transient' ) );
		}
	}

	/**
	 * Menu
	 */
	public function menu() {
		// generate update url for later use.
		$this->update_url = wp_nonce_url( 'update.php?action=upgrade-plugin&plugin=' . $this->plugin_file, 'upgrade-plugin_' . $this->plugin_file );

		// add menu page.
		add_menu_page( 'WishList Member', 'WishList Member', 'manage_options', 'WishListMember', array( $this, 'page' ), plugins_url( '', __FILE__ ) . '/ui/images/WishListMember-logomark-16px-wp.svg', '2.01' );
	}

	/**
	 * Menu page
	 */
	public function page() {
		printf( '<div class="wrap"><h1>WishList Member</h1></div>' );
	}

	/**
	 * Admin Notice
	 */
	public function notice() {
		// only display to admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		printf( '<div class="notice notice-error">%s</div>', wp_kses_post( str_replace( '___wlm3updateurl___', $this->update_url, $this->message ) ) );
	}

	/**
	 * Rollback hook - we change the update_plugins transient based on our needs
	 *
	 * @param object $transient Plugins update transient data.
	 */
	public function update_plugin_transient( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient           = new \stdClass();
			$transient->response = array();
		}

		// no license key - abort.
		if ( ! $this->license_key ) {
			return $transient;
		}

		// no previous version - abort.
		if ( ! $this->previous_version ) {
			return $transient;
		}

		// version format not valid - abort.
		if ( ! preg_match( '/\d+.\d+.\d+/', $this->previous_version ) ) {
			return $transient;
		}

		// previous version is greater than 3.1 - abort.
		if ( version_compare( $this->previous_version, '3.1.0', '>=' ) ) {
			return $transient;
		}

		// inject our download URL.
		if ( ! isset( $transient->response[ $this->plugin_file ] ) ) {
			$transient->response[ $this->plugin_file ] = new \stdClass();
		}

		$url = 'http://wishlistproducts.com/download/' . $this->license_key . '/==' . base64_encode( pack( 'i', WLM_SKU ) );
		$url = add_query_arg( 'version', $this->previous_version, $url );
		$transient->response[ $this->plugin_file ]->package = $url;
		unset( $transient->response[ $this->plugin_file ]->new_version );

		// return modified transient.
		return $transient;
	}
}
