<?php

// get settings
$webhooks_settings = $this->get_option( 'webhooks_settings' );
// make sure settings is array
if ( ! is_array( $webhooks_settings ) ) {
	$webhooks_settings = array(
		'outgoing' => array(),
		'incoming' => array(),
	);
	$this->add_option( 'webhooks_settings', $webhooks_settings );
}

$webhooks_settings = array_merge(
	array(
		'outgoing' => array(),
		'incoming' => array(),
	),
	$webhooks_settings
);

// add our data to js
thirdparty_integration_data(
	$config['id'],
	array(
		'webhooks_settings' => $webhooks_settings,
	)
);
