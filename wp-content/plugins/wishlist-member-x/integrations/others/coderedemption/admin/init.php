<?php
// get settings
@require_once __DIR__ . '/../handler.php';
$coderedemption_settings = \WishListMember\Integrations\Others\CodeRedemption::get_settings( true );

// add our data to js
thirdparty_integration_data(
	$config['id'],
	array(
		'coderedemption_settings' => $coderedemption_settings,
	)
);
