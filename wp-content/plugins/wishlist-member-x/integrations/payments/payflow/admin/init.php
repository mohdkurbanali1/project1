<?php
$data = new \stdClass();

$data->payflowsettings = $this->get_option( 'payflowsettings' );
if ( ! is_array( $data->payflowsettings ) ) {
	$this->save_option(
		'payflowsettings',
		$data->payflowsettings = array(
			'live'         => array(),
			'sandbox'      => array(),
			'sandbox_mode' => 0,
		)
	);
}

$data->payflowthankyou = $this->get_option( 'payflowthankyou' );
if ( ! $data->payflowthankyou ) {
	$this->save_option( 'payflowthankyou', $data->payflowthankyou = $this->make_reg_url() );
}

$data->paypalpayflowproducts = $this->get_option( 'paypalpayflowproducts' );
if ( ! $data->paypalpayflowproducts ) {
	$data->paypalpayflowproducts = array();
}

$data->payflowthankyou_url = $wpm_scregister . $data->payflowthankyou;

thirdparty_integration_data( $config['id'], $data );
