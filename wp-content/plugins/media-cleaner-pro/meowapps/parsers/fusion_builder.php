<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_fusionbuilder', 10, 2 );

function wpmc_scan_html_fusionbuilder( $html, $id ) {
	global $wpmc;
	$galleries_images_fb = array();
	preg_match_all( "/image_ids=\"([0-9,]+)/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $r ) {
			$ids = explode( ',', $r );
			$galleries_images_fb = array_merge( $galleries_images_fb, $ids );
		}
	}
	$wpmc->add_reference_id( $galleries_images_fb, 'PAGE BUILDER GALLERY (ID)' );
}

?>