<?php

$data = (array) $this->get_option( 'callloop_settings' );

thirdparty_integration_data(
	$config['id'],
	array( 'callloop_settings' => $data )
);
