<?php
/* Payment Integration : Infusionsoft */
require_once 'admin/init.php';

$all_tabs = array(
	'settings' => 'Settings',
	'products' => 'Products',
	'cron'     => 'Cron Job',
	'tutorial' => 'Tutorial',

);
$active_tab       = 'settings';
$api_not_required = array( 'settings', 'tutorial' );
echo '<p><a href="http://wlplink.com/go/wlmis/29it" target="_blank">Learn more about our deeper integration with Infusionsoft</a></p>';
echo '<ul class="nav nav-tabs">';
foreach ( $all_tabs as $k => $v ) {
	$active       = $active_tab === $k ? 'active' : '';
	$api_required = in_array( $k, $api_not_required, true ) ? '' : 'api-required';
	printf( '<li class="%s %s nav-item"><a class="nav-link" data-toggle="tab" href="#%s_%s">%s</a></li>', esc_attr( $active ), esc_attr( $api_required ), esc_attr( $config['id'] ), esc_attr( $k ), esc_attr( $v ) );
}
echo '</ul>';
echo '<div class="tab-content">';
foreach ( $all_tabs as $k => $v ) {
	$active       = $active_tab === $k ? 'active in' : '';
	$api_required = in_array( $k, $api_not_required, true ) ? '' : 'api-required';
	printf( '<div id="%s_%s" class="tab-pane %s %s">', esc_attr( $config['id'] ), esc_attr( $k ), esc_attr( $api_required ), esc_attr( $active ) );
	include_once 'admin/tabs/' . $k . '.php';
	echo '</div>';
}
echo '</div>';

wlm_print_script( plugin_dir_url( __FILE__ ) . 'assets/admin.js' );
wlm_print_style( plugin_dir_url( __FILE__ ) . 'assets/admin.css' );
