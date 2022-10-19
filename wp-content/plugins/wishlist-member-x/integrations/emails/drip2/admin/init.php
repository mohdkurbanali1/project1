<?php
$wlmapikeys = new \WishListMember\APIKey();
$wlmapikey  = wlm_or( $wlmapikeys->get( 'emails/' . $config['id'] ), array( $wlmapikeys, 'add' ), 'emails/' . $config['id'] );

$data         = $ar_data[ $config['id'] ];
$data['tags'] = array();
thirdparty_integration_data( $config['id'], $data );

