<?php
/* Other Integration : eLearnCommerce */
require_once 'admin/init.php';

$the_posts = new WP_Query(
	array(
		'post_type' => 'courses',
		'nopaging'  => true,
	)
);
$courses   = array();
if ( count( $the_posts->posts ) ) {
	foreach ( $the_posts->posts as $key => $c ) {
		$courses[ $c->ID ] = array(
			'id'    => $c->ID,
			'title' => $c->post_title,
		);
	}
}

$level_actions = array(
	'add'    => 'Added',
	'cancel' => 'Cancelled',
	'remove' => 'Removed',
	'rereg'  => 'Re-Registered',
);

$all_tabs         = array(
	// 'settings' => 'Settings',
	'level'  => 'Membership Level Actions',
	'course' => 'Course Actions',
);
$active_tab       = 'level';
$api_not_required = array();
?>
<div class="row">
	<div class="col plugin-status pt-2">
		<div class="text-warning"><p><em></em></p></div>
	</div>
</div>


<?php
echo '<ul class="nav nav-tabs">';
foreach ( $all_tabs as $k => $v ) {
	$active       = $active_tab === $k ? 'active' : '';
	$api_required = in_array( $k, $api_not_required, true ) ? '' : 'api-required';
	printf( '<li class="%s %s nav-item"><a class="nav-link" data-toggle="tab" href="#%s_%s">%s</a></li>', esc_attr( $active ), esc_attr( $api_required ), esc_url( $config['id'] ), esc_url( $k ), esc_html( $v ) );
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
?>
