<?php
$data = (array) $this->get_option( 'elearncommerce_settings' );

thirdparty_integration_data(
	$config['id'],
	array(
		'elearncommerce_settings' => $data,
	)
);

