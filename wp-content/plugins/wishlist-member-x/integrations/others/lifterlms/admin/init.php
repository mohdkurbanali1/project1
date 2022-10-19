<?php
$data = (array) $this->get_option( 'lifterlms_settings' );

thirdparty_integration_data(
	$config['id'],
	array(
		'lifterlms_settings' => $data,
	)
);
