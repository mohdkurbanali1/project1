<?php
/**
 * WishList Member 3.1 Version Requirements Check
 *
 * @package WishListMember
 */

if ( ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
	error_reporting( 0 );
}

global $wp_version;

if ( version_compare( PHP_VERSION, WLM_MIN_PHP_VERSION, '<' ) || version_compare( $wp_version, WLM_MIN_WP_VERSION, '<' ) ) {
	// version requirements no met.
	require_once 'classes/class-wishlistmember3-requirements-not-met.php';
	new WishListMember3_Requirements_Not_Met();
	return false;
}

// version all good.
return true;
