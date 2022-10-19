<?php

$class_name = '\WishListMember\Autoresponders\SendLane';

add_action( 'wishlistmember_user_registered', array( $class_name, 'NewUserTagsHookQueue' ), 99, 2 );
add_action( 'wishlistmember_add_user_levels', array( $class_name, 'AddUserTagsHookQueue' ), 10, 3 );
add_action( 'wishlistmember_pre_remove_user_levels', array( $class_name, 'RemoveUserTagsHookQueue' ), 99, 2 );
add_action( 'wishlistmember_cancel_user_levels', array( $class_name, 'CancelUserTagsHookQueue' ), 99, 2 );
add_action( 'wishlistmember_uncancel_user_levels', array( $class_name, 'ReregUserTagsHookQueue' ), 99, 2 );
add_action( 'delete_user', array( $class_name, 'DeleteUserHookQueue' ), 9, 1 );

add_action( 'wishlistmember_api_queue', array( $class_name, 'ProcessQueue' ) );
