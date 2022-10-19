<?php

add_action( 'wishlistmember_autoresponder_subscribe', array( '\WishListMember\Autoresponders\ARP', 'subscribe' ), 10, 2 );
add_action( 'wishlistmember_autoresponder_unsubscribe', array( '\WishListMember\Autoresponders\ARP', 'unsubscribe' ), 10, 2 );
