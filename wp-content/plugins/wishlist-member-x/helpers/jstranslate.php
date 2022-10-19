<?php
/**
 * Generates helpers/jslang.php
 * For internal use only.

 * @package WishListMember/Helpers
 */

$jsfiles = array();

/**
 * Recursively searches $path for .js files
 *
 * @param string $path Path to search for.
 */
function findjs( $path = '' ) {
	global $jsfiles;
	$jsfiles = array_merge( $jsfiles, glob( $path . '*.js' ) );
	$folders = glob( $path . '*', GLOB_ONLYDIR | GLOB_MARK );
	foreach ( $folders as $folder ) {
		findjs( $folder );
	}
}

findjs();

$strings = array();

while ( $file = array_shift( $jsfiles ) ) {
	$content = file_get_contents( $file );
	preg_match_all( "#wlm.translate\s*\(\s*('.+?(?<!\\\\)')#", $content, $matches );
	if ( $matches[1] ) {
		foreach ( $matches[1] as $match ) {
			$strings[] = substr( $match, 1, -1 );
		}
	}
	preg_match_all( '#wlm.translate\s*\(\s*(".+?(?<!\\\\)")#', $content, $matches );
	if ( $matches[1] ) {
		foreach ( $matches[1] as $match ) {
			$strings[] = substr( $match, 1, -1 );
		}
	}
}

$strings = array_unique( $strings );

$output = '';

$output .= "<?php\nwp_localize_script( 'wishlistmember3-combined-scripts', 'wlm3l10n', array(\n";
while ( $string = array_shift( $strings ) ) {
	$output .= sprintf( "\t'%s' => esc_html__( '%s', 'wishlist-member' ),\n", $string, $string );
}
$output .= ") );\n";
file_put_contents( __DIR__ . '/jslang.php', $output );

// menu.json.
$output   = "<?php\n";
$wlm_menu = file_get_contents( __DIR__ . '/../ui/menu.json' );
if ( preg_match_all( '/(["\'])(name|title)\1\s*:\s*(["\'])(.+?)\3/', $wlm_menu, $matches ) ) {
	foreach ( array_unique( $matches[4] ) as $text ) {
		$output .= sprintf( "__( '%s', 'wishlist-member' );\n", addslashes( $text ) );
	}
}
file_put_contents( __DIR__ . '/menulang.php', $output );

