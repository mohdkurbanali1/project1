<?php
$data = (array) $this->get_option( 'wpcourseware_settings' );

thirdparty_integration_data(
	$config['id'],
	array(
		'wpcourseware_settings' => $data,
	)
);

