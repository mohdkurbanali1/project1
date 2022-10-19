<?php
/**
 * This script handles the loading of CSS and JS files for WishList Member
 *
 * @package WishListMember/Helpers
 */

error_reporting( 0 );

define( 'PLUGIN_DIR', dirname( __DIR__ ) );

$wlm_build_number = '8261';

if ( preg_match( implode( '', array( '/{', 'GLOBALREV}/' ) ), $wlm_build_number ) ) {
	$wlm_build_number = '';
}

$if_none_match = filter_input( INPUT_SERVER, 'HTTP_IF_NONE_MATCH' );
$request_uri   = filter_input( INPUT_SERVER, 'REQUEST_URI' );
if ( is_null( $request_uri ) ) {
	$request_uri = '/';
}

if ( ! is_null( $if_none_match ) && stripslashes( $if_none_match ) === md5( $wlm_build_number . $request_uri ) ) {
	http_response_code( 304 );
	exit;
}

$styles  = array(
	array(
		'/assets/css/wordpress-overrides.css',
		'/assets/css/bootstrap.min.css',
		'/assets/css/animate.min.css',
		'/assets/css/select2.min.css',
		'/assets/css/select2-bootstrap.min.css',
		'/assets/css/toggle-switch-px.css',
		'/assets/css/daterangepicker.css',
		'/assets/css/jquery.minicolors.css',
		'/assets/css/source-sans.css',
		'/ui/stylesheets/main.css',
	),
);
$scripts = array(
	array(
		'/assets/js/jquery.min.js',
		'/assets/js/jquery-ui.min.js',
		'/assets/js/underscore-min.js',
		'/assets/js/backbone-min.js',
		'/assets/js/underscore-settings.js',
	),
	array(
		'/assets/js/popper.min.js',
		'/assets/js/bootstrap.min.js',
		'/assets/js/select2.min.js',
		'/assets/js/moment.min.js',
		'/assets/js/daterangepicker.js',
		'/assets/js/jquery.minicolors.min.js',
		'/assets/js/clipboard.min.js',
		'/assets/js/wlm.js',
		'/assets/js/main.js',
	),
);

if ( empty( $asset_type ) || ! in_array( $asset_type, array( 'css', 'js' ), true ) ) {
	return;
}

$asset_index = (int) $asset_index;

$output = '';

// Combine Files.
switch ( $asset_type ) {
	case 'css':
		$fs = $styles;
		break;
	default:
		$fs = $scripts;
}
$fs = (array) $fs[ $asset_index ];
foreach ( $fs as $f ) {
	if ( file_exists( PLUGIN_DIR . $f ) ) {
		$output .= '/* [' . $f . "] */\n";
		$output .= file_get_contents( PLUGIN_DIR . $f );
	}
	$output .= "\n";
}

if ( 'js' === $asset_type && 0 === $asset_index ) {
	// we use $ for jQuery.
	$output .= 'var $ = jQuery.noConflict();';
}
$output = trim( $output );

// Content Type.
$ct = 'css' === $asset_type ? 'text/css' : 'application/javascript';
header( 'Content-type: ' . $ct . '; charset=UTF-8' );

if ( ! $output ) {
	exit;
}

// caching headers.
$seconds_to_cache = 3153600; // one year.
$ts               = gmdate( 'D, d M Y H:i:s', time() + $seconds_to_cache ) . ' GMT';
if ( $wlm_build_number ) {
	header( 'Etag: ' . md5( $wlm_build_number . $request_uri ) );
}
header( 'Expires: ' . $ts );
header( 'Pragma: cache' );
header( 'Cache-Control: public, max-age=' . $seconds_to_cache );

$output = "/* WishList Member */\n" . $output;
