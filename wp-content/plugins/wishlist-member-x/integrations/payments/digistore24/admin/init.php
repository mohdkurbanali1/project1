<?php

$wlmapikeys = new \WishListMember\APIKey();
$wlmapikey  = wlm_or( $wlmapikeys->get( 'payments/' . $config['id'] ), array( $wlmapikeys, 'add' ), 'payments/' . $config['id'] );
