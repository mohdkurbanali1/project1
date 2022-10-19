<?php
/*
Plugin Name: Media Cleaner Pro
Plugin URI: https://meowapps.com
Description: Clean your Media Library, many options, trash system.
Version: 5.6.3
Author: Jordy Meow
Author URI: https://meowapps.com
Text Domain: media-cleaner

Originally developed for two of my websites:
- Jordy Meow (https://offbeatjapan.org)
- Haikyo (https://haikyo.org)
*/

if ( class_exists( 'Meow_WPMC_Core' ) ) {
	function wpmc_thanks_admin_notices() {
		echo '<div class="error"><p>' . __( 'Thanks for installing the Pro version of Media Cleaner :) However, the free version is still enabled. Please disable or uninstall it.', 'media-cleaner' ) . '</p></div>';
	}
	add_action( 'admin_notices', 'wpmc_thanks_admin_notices' );
	return;
}

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	global $wpmc_version;
	global $wpmc;
	$wpmc_version = '5.6.3';

	require __DIR__ . '/admin.php';
	require __DIR__ . '/core.php';
	require __DIR__ . '/meowapps/core.php';

	wpmc_init( __FILE__ );
	$admin = new Meow_WPMC_Admin( 'wpmc', __FILE__, 'media-cleaner' );
	$wpmc = new Meow_WPMC_Core( $admin );
	new MeowAppsPro_WPMC_Core( 'wpmc', __FILE__, 'media-cleaner', $wpmc_version, $wpmc, $admin );
}

?>
