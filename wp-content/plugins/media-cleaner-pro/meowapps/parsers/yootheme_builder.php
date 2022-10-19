<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_yootheme_builder', 10, 2 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_yootheme_builder', 10, 1 );

function wpmc_scan_html_yootheme_builder( $html, $id ) {
}

function wpmc_scan_postmeta_yootheme_builder( $id ) {
}

?>