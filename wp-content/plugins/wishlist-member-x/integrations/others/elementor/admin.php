<?php
$go = include_once 'admin/init.php';
if ( false === $go ) {
	return;
}

$all_tabs         = array(
	'tutorial' => 'Tutorial',
);
$active_tab       = '';
$api_not_required = array( 'tutorial' );

echo '<ul class="nav nav-tabs">';
foreach ( $all_tabs as $k => $v ) {
	$active       = $active_tab === $k ? 'active' : '';
	$api_required = in_array( $k, $api_not_required, true ) ? '' : 'api-required';
	printf( '<li class="%s %s nav-item"><a class="nav-link" data-toggle="tab" href="#%s_%s">%s</a></li>', esc_attr( $active ), esc_attr( $api_required ), esc_attr( $config['id'] ), esc_attr( $k ), esc_attr( $v ) );
}
echo '</ul>';
printf( '<p>Configuration is not required within WishList Member for this integration. The integration is either enabled or disabled.</p>' );
printf( '<p>While the integration is enabled, WishList Member will add a setting within Elementor related to the display of Elementor Sections and Inner Sections.</p>' );
printf( '<p>The setting is available in the Advanced Options tab of all Sections and Inner Sections within Elementor.</p>' );
