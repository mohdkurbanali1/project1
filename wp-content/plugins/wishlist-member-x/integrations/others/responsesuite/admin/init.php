<?php

$wlmapikeys = new \WishListMember\APIKey();
$wlmapikey  = wlm_or( $wlmapikeys->get( 'others/' . $config['id'] ), array( $wlmapikeys, 'add' ), 'others/' . $config['id'] );
