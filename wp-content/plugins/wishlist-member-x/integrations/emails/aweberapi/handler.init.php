<?php

$class_name = '\WishListMember\Autoresponders\AweberAPI';

add_action( 'wishlistmember_api_queue', array( $class_name, 'process_queue' ) );

add_action( 'wishlistmember_autoresponder_subscribe', array( $class_name, 'subscribe' ), 10, 2 );
add_action( 'wishlistmember_autoresponder_unsubscribe', array( $class_name, 'unsubscribe' ), 10, 2 );
