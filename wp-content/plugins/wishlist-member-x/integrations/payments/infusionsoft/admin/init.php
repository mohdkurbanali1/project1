<?php
$data = new \stdClass();

$data->ismachine = wlm_trim( $this->get_option( 'ismachine' ) );
$data->isapikey  = wlm_trim( $this->get_option( 'isapikey' ) );

$data->isthankyou = $this->get_option( 'isthankyou' );
if ( ! $data->isthankyou ) {
	$this->save_option( 'isthankyou', $data->isthankyou = $this->make_reg_url() );
}
$data->isthankyou_url = $wpm_scregister . $data->isthankyou;

if ( isset( wlm_get_data()['isenable_log'] ) ) {
	$this->save_option( 'isenable_log', (int) wlm_get_data()['isenable_log'] );
}
$data->isenable_log = (bool) $this->get_option( 'isenable_log' );

$tags = array( 'istags_add_app', 'istags_add_rem', 'istags_remove_app', 'istags_remove_rem', 'istags_cancelled_app', 'istags_cancelled_rem', 'istagspp_add_app', 'istagspp_add_rem', 'istagspp_remove_app', 'istagspp_remove_rem' );

foreach ( $tags as $_tag ) {
	$x = $this->get_option( $_tag );
	if ( $x ) {
		$x = wlm_maybe_unserialize( $x );
	} else {
		$x = array();
	}
	$data->$_tag = $x;
}

thirdparty_integration_data( $config['id'], $data );

