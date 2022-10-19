<?php
/**
 * Error reporting.
 *
 * @package WishListMember
 */

if ( ! function_exists( 'wlm_is_unpackaged' ) ) {
	/**
	 * Checks if WishList Member is unpackaged.
	 *
	 * This works because the packaging script automatically
	 * replaces all instances of "{ GLOBALREV }" (without the spaces)
	 * with the build number.
	 *
	 * @return boolean
	 */
	function wlm_is_unpackaged() {
		return '8261' === ( '{' ) . 'GLOBALREV}';
	}
}

// we want as much error messages as possible to be logged and displayed for developers.
if ( wlm_is_unpackaged() && false ) {
	defined( 'WP_DEBUG' ) || define( 'WP_DEBUG', true );
	defined( 'WP_DEBUG_DISPLAY' ) || defined( 'WP_DEBUG_DISPLAY', true );
	defined( 'WP_DISABLE_FATAL_ERROR_HANDLER' ) || defined( 'WP_DISABLE_FATAL_ERROR_HANDLER', true );
	defined( 'WLM_ERROR_REPORTING' ) || define( 'WLM_ERROR_REPORTING', E_ALL ); // devs must see all.
}

// set wlmdebug cookie to value of wlmdebug query.
if ( isset( wlm_get_data()['wlmdebug'] ) ) { // phpcs:ignore
	setcookie( 'wlmdebug', $wlmdebug = (int) wlm_get_data()['wlmdebug'] ); // phpcs:ignore
}

// set error reporting to value of wlmdebug cookie.
if ( ! defined( 'WLM_ERROR_REPORTING' ) && ! empty( $wlmdebug ) ) {
	define( 'WLM_ERROR_REPORTING', $wlmdebug + 0 );
}

// show warnings and above if WP_DEBUG is on.
if ( ! defined( 'WLM_ERROR_REPORTING' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	// delete debug cookie.
	headers_sent() || setcookie( 'wlmdebug', '' );

	// prepare error reporting value.
	$error_reporting = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_WARNING;
	// add E_RECOVERABLE_ERROR to error reporting if available.
	defined( 'E_RECOVERABLE_ERROR' ) && $error_reporting |= E_RECOVERABLE_ERROR;
	// add E_DEPRECATED to error reporting if available.
	defined( 'E_DEPRECATED' ) && $error_reporting |= E_DEPRECATED;
	define( 'WLM_ERROR_REPORTING', $error_reporting );
}

// set error reporting to values of WLM_ERROR_REPORTING.
defined( 'WLM_ERROR_REPORTING' ) && error_reporting( WLM_ERROR_REPORTING ); // phpcs:ignore
