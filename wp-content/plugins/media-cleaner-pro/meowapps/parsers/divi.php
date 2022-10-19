<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_divi', 10, 2 );

function wpmc_scan_html_divi( $html, $id ) {
	global $wpmc;
	$posts_images_urls = array();
	$galleries_images_et = array();

	// Single Image
	preg_match_all( "/src=\"((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(jpe?g|gif|png|ico|tif?f|bmp))\"/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
		foreach ( $res[1] as $url ) {
			if ( !preg_match('/(elegantthemesimages\.com)|(elegantthemes\.com)/', $url ) )
				array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Background Image
	preg_match_all( "/background_image=\"((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(jpe?g|gif|png|ico|tif?f|bmp))\"/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
		foreach ( $res[1] as $url ) {
			if ( !preg_match('/(elegantthemesimages\.com)|(elegantthemes\.com)/', $url ) )
				array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Modules with URL (like the Person module)
	preg_match_all( "/url=\"((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(jpe?g|gif|png|ico|tif?f|bmp))\"/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $url ) {
			if ( !preg_match('/(elegantthemesimages\.com)|(elegantthemes\.com)/', $url ) )
				array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Galleries
	preg_match_all( "/gallery_ids=\"([0-9,]+)/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $r ) {
			$ids = explode( ',', $r );
			$galleries_images_et = array_merge( $galleries_images_et, $ids );
		}
	}

	$wpmc->add_reference_url( $posts_images_urls, 'CONTENT (URL)' );
	$wpmc->add_reference_id( $galleries_images_et, 'PAGE BUILDER (ID)' );
}

?>