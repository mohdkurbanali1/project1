<?php

$xproducts = wc_get_products( array( 'limit' => -1 ) );
$products  = array();
foreach ( $xproducts as $product ) {
	$products[ $product->id ] = array(
		'id'     => $product->id,
		'value'  => $product->id,
		'name'   => $product->name,
		'text'   => $product->name,
		'sku'    => $product->sku,
		'status' => $product->status,
	);
}
unset( $xproducts );

// initial values
if ( ! is_array( $this->get_option( 'woocommerce_settings' ) ) ) {
	$this->add_option( 'woocommerce_settings', array() );
	$this->add_option( 'woocommerce_products', array() );
	$this->add_option( 'woocommerce_eot_cancel', array() );
	$this->add_option( 'woocommerce_scrcancel', array() );
}

thirdparty_integration_data(
	$config['id'],
	array(
		'woocommerce_settings'   => (array) ( wlm_or( $this->get_option( 'woocommerce_settings' ), array() ) ),
		'woocommerce_products'   => (array) ( wlm_or( array_diff( $this->get_option( 'woocommerce_products' ), array( null, '', false ) ), array() ) ),
		'woocommerce_eot_cancel' => (array) ( wlm_or( $this->get_option( 'woocommerce_eot_cancel' ), array() ) ),
		'woocommerce_scrcancel'  => (array) ( wlm_or( $this->get_option( 'woocommerce_scrcancel' ), array() ) ),
		'products'               => $products,
	)
);
