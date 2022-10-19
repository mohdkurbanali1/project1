<?php

$data = new \stdClass();

// currencies
$data->currencies = array( 'USD', 'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'TWD', 'THB', 'TRY' );
foreach ( $data->currencies as &$c ) {
	$c = array(
		'value' => $c,
		'text'  => $c,
	);
}
unset( $c );

// billing periods
$data->billing_periods = array(
	array(
		'value' => 'Day',
		'text'  => 'Day(s)',
	),
	array(
		'value' => 'Month',
		'text'  => 'Month(s)',
	),
);

// card types
$data->card_types = array(
	'Visa'        => 'Visa',
	'MasterCard'  => 'MasterCard',
	'Discover'    => 'Discover',
	'Amex'        => 'American Express',
	'Diners Club' => 'Diners Club',
	'JCB'         => 'JCB',
);

// thank you url
$data->anetarbthankyou = wlm_trim( $this->get_option( 'anetarbthankyou' ) );
if ( ! $data->anetarbthankyou ) {
	$this->save_option( 'anetarbthankyou', $data->anetarbthankyou = $this->make_reg_url() );
}
$data->anetarbthankyou_url = $wpm_scregister . $data->anetarbthankyou;

// settings
$data->anetarbsettings = (array) $this->get_option( 'anetarbsettings' );

// form settings
$x             = $this->get_option( 'authnet_arb_formsettings' );
$formsettings  = array_diff( is_array( $x ) ? $x : array(), array( '' ) );
$form_defaults = array(
	'formheading'          => 'Register for %level',
	'formheadingrecur'     => 'Subscribe to %level',
	'formbuttonlabel'      => 'Pay',
	'formbuttonlabelrecur' => 'Pay',
	'supportemail'         => get_option( 'admin_email' ),
);

$data->authnet_arb_formsettings = wp_parse_args( $formsettings, $form_defaults );

// subscriptions
$x                          = $this->get_option( 'anetarbsubscriptions' );
$data->anetarbsubscriptions = is_array( $x ) ? $x : array();

thirdparty_integration_data( $config['id'], $data );
