<?php

$data = new \stdClass();

// currencies
$data->currencies = array( 'USD', 'AED', 'ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'HKD', 'ILS', 'INR', 'JPY', 'LTL', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'RON', 'RUB', 'SEK', 'SGD', 'TRY', 'ZAR' );
foreach ( $data->currencies as &$c ) {
	$c = array(
		'value' => $c,
		'text'  => $c,
	);
}
unset( $c );

// rebill interval
$data->rebill_interval = array();
for ( $i = 1; $i <= 30; $i++ ) {
	$data->rebill_interval[] = array(
		'value' => $i,
		'text'  => $i,
	);
}

// billing periods
$data->rebill_interval_type = array(
	array(
		'value' => '1',
		'text'  => 'Day(s)',
	),
	array(
		'value' => '2',
		'text'  => 'Week(s)',
	),
	array(
		'value' => '3',
		'text'  => 'Month(s)',
	),
	array(
		'value' => '4',
		'text'  => 'Year(s)',
	),
);

// thank you url
$data->ewaythankyou = wlm_trim( $this->get_option( 'ewaythankyou' ) );
if ( ! $data->ewaythankyou ) {
	$this->save_option( 'ewaythankyou', $data->ewaythankyou = $this->make_reg_url() );
}
$data->ewaythankyou_url = $wpm_scregister . $data->ewaythankyou;

// settings
$data->ewaysettings = (array) $this->get_option( 'ewaysettings' );

// form settings
$form_defaults = array(
	'formheading'      => 'Register for %level',
	'buttonlabel'      => 'Join %level',
	'panelbuttonlabel' => 'Pay',
	'supportemail'     => get_option( 'admin_email' ),
);

$data->ewaysettings = wp_parse_args( $data->ewaysettings, $form_defaults );

thirdparty_integration_data( $config['id'], $data );
