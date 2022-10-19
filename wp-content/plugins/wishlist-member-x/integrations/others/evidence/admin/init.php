<?php

$data = (array) $this->get_option( 'evidence_settings' );

if ( empty( $data['active'] ) || ! is_array( $data['active'] ) ) {
	$data['active'] = array();
}

thirdparty_integration_data(
	$config['id'],
	array( 'evidence_settings' => $data )
);
