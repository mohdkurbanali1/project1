<?php
/**
 * Anonymous usage tracking
 *
 * @package WishListMember
 */

namespace WishListMember\Features\Anonymous_Usage_Tracking;

defined( 'ABSPATH' ) || die();

$anonymous_usage_tracking = wishlistmember_instance()->get_option( 'anonymous_usage_tracking' );
if ( false === $anonymous_usage_tracking ) {
	add_action( 'admin_notices', __NAMESPACE__ . '\show_anonymous_usage_tracking_optin' );
	add_action( 'wishlistmember_pre_admin_screen', __NAMESPACE__ . '\show_anonymous_usage_tracking_optin' );
	/**
	 * Show opt-in message for anonymous data tracking
	 *
	 * @wp-hook admin_notices
	 * @wp-hook wishlistmember_pre_admin_screen
	 */
	function show_anonymous_usage_tracking_optin() {
		$base_url = wlm_or( wp_get_referer(), wlm_arrval( $_SERVER, 'REQUEST_URI' ) );
		$url_yes  = wp_nonce_url( add_query_arg( 'wlm-anonymous-usage-tracking', 'yes', $base_url ), 'wlm-anonymous-usage-tracking-yes' );
		$url_no   = wp_nonce_url( add_query_arg( 'wlm-anonymous-usage-tracking', 'no', $base_url ), 'wlm-anonymous-usage-tracking-no' );
		echo wp_kses_post(
			sprintf(
				'<div class="mx-0 my-3 notice notice-info"><p><strong>%s</strong></p><p>%s</p><p><a href="%s" class="button button-secondary">%s</a> <a href="%s" class="button button-primary">%s</a></p></div>',
				__( 'Build a better WishList Member', 'wishlist-member ' ),
				__( 'Get improved features and faster fixes by sharing non-sensitive data via usage tracking that shows us how WishList Member is used. No personal data is tracked or stored.', 'wishlist-member' ),
				$url_no,
				__( 'No thanks', 'wishlist-member' ),
				$url_yes,
				__( 'Yes, count me in.', 'wishlist-member' )
			)
		);
	}

	add_action( 'admin_init', __NAMESPACE__ . '\save_anonymous_data_setting' );
	/**
	 * Save anonymous usage tracking from opt-in message
	 *
	 * @wp-hook admin_init
	 */
	function save_anonymous_data_setting() {
		$setting = wlm_get_data()['wlm-anonymous-usage-tracking'];
		if ( ! in_array( $setting, array( 'yes', 'no' ), true ) ) {
			return;
		}
		check_admin_referer( 'wlm-anonymous-usage-tracking-' . $setting );
		remove_action( 'admin_notices', __NAMESPACE__ . '\show_anonymous_usage_tracking_optin' );
		remove_action( 'wishlistmember_pre_admin_screen', __NAMESPACE__ . '\show_anonymous_usage_tracking_optin' );
		wishlistmember_instance()->save_option( 'anonymous_usage_tracking', $setting );
	}
}

add_action( 'wishlistmember_post_admin_screen', __NAMESPACE__ . '\show_settings_screen', 10, 2 );
/**
 * Insert settings under Advanced Settings > Miscellaneous
 *
 * @param  string $wl Screen being displayed
 * @param  string $base Base path to ui/admin_screens/
 */
function show_settings_screen( $wl, $base ) {
	if ( 'advanced_settings/miscellaneous' !== $wl ) {
		return;
	}
	require __DIR__ . '/settings-view.php';
}

add_action( 'wishlistmember_license_key_validated', __NAMESPACE__ . '\send_anonymous_usage_tracking' );
/**
 * Send anonymous usage tracking to WishList Products servers
 */
function send_anonymous_usage_tracking( $license_key ) {
	if ( 'yes' !== wishlistmember_instance()->get_option( 'anonymous_usage_tracking' ) ) {
		// opted out. end.
		return;
	}

	$levels            = wishlistmember_instance()->get_option( 'wpm_levels' );
	$payperposts       = wishlistmember_instance()->get_pay_per_posts();
	$email_providers   = wishlistmember_instance()->get_option( 'active_email_integrations' );
	$other_providers   = wishlistmember_instance()->get_option( 'active_other_integrations' );
	$payment_providers = wishlistmember_instance()->get_option( 'ActiveShoppingCarts' );
	if ( is_array( $payment_providers ) ) {
		$payment_providers = array_map(
			/**
			 * Cleanup payment provider names
			 *
			 * @return string
			 */
			function( $value ) {
				return str_replace( array( 'integration.shoppingcart.', '.php' ), '', $value );
			},
			$payment_providers
		);
	}

	$sys_info = new \WishListMember\System_Info();
	/**
	 * Callback for array_map to simplify $sys_info values
	 *
	 * @return string
	 */
	$simplify = function( $value ) {
		return $value['value'];
	};

	$data = array(
		'key'                   => $license_key, // used only for validation and not stored.
		'sku'                   => WLM_SKU,
		'version'               => WLM_PLUGIN_VERSION,
		'timestamp'             => time(),
		'number_of_levels'      => is_array( $levels ) ? count( $levels ) : 0,
		'number_of_payperposts' => is_array( $payperposts ) ? count( $payperposts ) : 0,
		'payment_providers'     => $payment_providers,
		'email_providers'       => is_array( $email_providers ) ? $email_providers : array(),
		'other_providers'       => is_array( $other_providers ) ? $other_providers : array(),
		'active_plugins'        => array_map( $simplify, $sys_info->info['plugins'] ),
		'active_theme'          => array_map( $simplify, $sys_info->info['theme'] ),
		'server'                => array_map( $simplify, $sys_info->info['server'] ),
		'wordpress'             => array_map( $simplify, $sys_info->info['wordpress'] ),
	);
	
	unset( $data['wordpress']['admin_email'] );
	unset( $data['wordpress']['home_url'] );

	// Send data to WishList Products.
	wp_remote_post(
		'https://api.wishlistproducts.com/wlm_anon_usage_tracking.php',
		array(
			'body'     => $data,
			'blocking' => false,
		)
	);
}
