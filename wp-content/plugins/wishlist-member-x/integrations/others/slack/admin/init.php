<?php

$data = (array) $this->get_option( 'slack_settings' );

if ( empty( $data['added'] ) || ! is_array( $data['added'] ) ) {
	$data['added'] = array( 'active' => array() );
}
if ( empty( $data['removed'] ) || ! is_array( $data['removed'] ) ) {
	$data['removed'] = array( 'active' => array() );
}
if ( empty( $data['cancelled'] ) || ! is_array( $data['cancelled'] ) ) {
	$data['cancelled'] = array( 'active' => array() );
}

thirdparty_integration_data(
	$config['id'],
	array( 'slack_settings' => $data )
);
