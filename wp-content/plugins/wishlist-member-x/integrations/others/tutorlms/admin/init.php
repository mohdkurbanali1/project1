<?php
$data = (array) $this->get_option( 'tutorlms_settings' );

thirdparty_integration_data(
	$config['id'],
	array(
		'tutorlms_settings' => $data,
	)
);

