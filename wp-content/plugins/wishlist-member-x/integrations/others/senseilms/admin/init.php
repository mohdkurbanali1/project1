<?php
$data = (array) $this->get_option( 'senseilms_settings' );

thirdparty_integration_data(
	$config['id'],
	array(
		'senseilms_settings' => $data,
	)
);
